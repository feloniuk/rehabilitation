<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MasterTelegramBotNotificationService
{
    private string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram_bot.token');
    }

    private function findMasterChatId(string $phone): ?string
    {
        // Нормализация телефона
        $normalizedPhone = $this->normalizePhone($phone);
        $fullPhone = '+38' . $normalizedPhone;

        try {
            // Получаем список updates от бота
            $updatesResponse = Http::get("https://api.telegram.org/bot{$this->botToken}/getUpdates");

            if (!$updatesResponse->successful()) {
                Log::error('Failed to get Telegram updates', [
                    'response' => $updatesResponse->body()
                ]);
                return null;
            }

            $updates = $updatesResponse->json()['result'] ?? [];

            // Детальное логирование updates
            Log::info('Telegram Updates Debug', [
                'updates_count' => count($updates),
                'first_update' => $updates[0] ?? null
            ]);

            // Проходим по всем updates и ищем чат
            foreach ($updates as $update) {
                // Различные способы найти chat_id
                $chatId = null;

                // Проверка через contact
                if (isset($update['message']['contact']['phone_number'])) {
                    $contactPhone = $this->normalizePhone($update['message']['contact']['phone_number']);
                    if ($contactPhone === $normalizedPhone) {
                        $chatId = $update['message']['chat']['id'];
                    }
                }

                // Проверка через from
                if (!$chatId && isset($update['message']['from']['phone_number'])) {
                    $fromPhone = $this->normalizePhone($update['message']['from']['phone_number']);
                    if ($fromPhone === $normalizedPhone) {
                        $chatId = $update['message']['chat']['id'];
                    }
                }

                // Проверка напрямую через chat
                if (!$chatId && isset($update['message']['chat'])) {
                    $chatId = $update['message']['chat']['id'];
                }

                // Если нашли chat_id
                if ($chatId) {
                    Log::info('Found chat_id for master', [
                        'phone' => $normalizedPhone,
                        'chat_id' => $chatId
                    ]);
                    return $chatId;
                }
            }

            // Попытка прямой отправки с номером телефона
            try {
                $directSendResponse = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                    'chat_id' => $fullPhone,
                    'text' => 'Тестове повідомлення для перевірки з\'єднання'
                ]);

                if ($directSendResponse->successful()) {
                    Log::info('Direct send successful', [
                        'phone' => $fullPhone
                    ]);
                    return $fullPhone;
                }
            } catch (\Exception $directSendException) {
                Log::error('Direct send error', [
                    'error' => $directSendException->getMessage()
                ]);
            }

            Log::warning('No chat_id found for master', [
                'phone' => $normalizedPhone
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Critical error in findMasterChatId', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function sendMasterNotification(Appointment $appointment)
    {
        try {
            $masterPhone = $this->normalizePhone($appointment->master->phone);
            $chatId = $this->findMasterChatId($masterPhone);

            if (!$chatId) {
                Log::error('Cannot send notification - no chat_id', [
                    'master' => $appointment->master->name,
                    'phone' => $masterPhone
                ]);
                return false;
            }

            $message = $this->formatNewAppointmentMessage($appointment);

            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown' // Важно для распознавания ссылок
            ]);

            if ($response->successful()) {
                Log::info('Master notification sent successfully', [
                    'master' => $appointment->master->name,
                    'chat_id' => $chatId
                ]);
                return true;
            } else {
                Log::error('Failed to send master notification', [
                    'response' => $response->body(),
                    'chat_id' => $chatId,
                    'master' => $appointment->master->name
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Master notification error', [
                'message' => $e->getMessage(),
                'master' => $appointment->master->name
            ]);
            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        // Удаляем все нецифровые символы
        $normalized = preg_replace('/[^0-9]/', '', $phone);
        
        // Убираем leading '+' или '38'
        $normalized = preg_replace('/^(\+38|38)/', '', $normalized);
        
        // Оставляем только последние 10 цифр
        $normalized = substr($normalized, -10);
        
        return $normalized;
    }

    private function formatNewAppointmentMessage(Appointment $appointment): string
    {
        // Пытаемся найти username клиента в Telegram
        $clientUsername = $this->findClientTelegramUsername($appointment->client);

        // Формируем имя клиента (с ссылкой или без)
        $clientNameFormatted = $clientUsername 
            ? "[{$appointment->client->name}](tg://user?id={$clientUsername})" 
            : $appointment->client->name;

        return sprintf(
            "🆕 Нова реєстрація 

👤 Клієнт: %s
📱 Телефон: %s
💆 Послуга: %s
📅 Дата: %s
🕰 Час: %s

Деталі в адмін-панелі.",
            $clientNameFormatted,
            $appointment->client->phone,
            $appointment->service->name,
            $appointment->appointment_date->format('d.m.Y'),
            substr($appointment->appointment_time, 0, 5)
        );
    }

    private function findClientTelegramUsername(User $client): ?string
    {
        try {
            // Получаем updates от бота
            $updatesResponse = Http::get("https://api.telegram.org/bot{$this->botToken}/getUpdates");

            if (!$updatesResponse->successful()) {
                Log::warning('Failed to get Telegram updates for client', [
                    'client_id' => $client->id,
                    'client_name' => $client->name
                ]);
                return null;
            }

            $updates = $updatesResponse->json()['result'] ?? [];

            // Нормализуем номер телефона клиента
            $normalizedClientPhone = $this->normalizePhone($client->phone);

            // Проходим по всем updates и ищем matching
            foreach ($updates as $update) {
                // Проверка через контакт
                if (isset($update['message']['contact']['phone_number'])) {
                    $contactPhone = $this->normalizePhone($update['message']['contact']['phone_number']);
                    if ($contactPhone === $normalizedClientPhone) {
                        return $update['message']['from']['id'] ?? null;
                    }
                }

                // Проверка через from
                if (isset($update['message']['from']['phone_number'])) {
                    $fromPhone = $this->normalizePhone($update['message']['from']['phone_number']);
                    if ($fromPhone === $normalizedClientPhone) {
                        return $update['message']['from']['id'] ?? null;
                    }
                }
            }

            Log::info('No Telegram user found for client', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'phone' => $normalizedClientPhone
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Error finding client Telegram username', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

}