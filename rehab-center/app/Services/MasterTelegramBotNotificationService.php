<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MasterTelegramBotNotificationService
{
    private ?string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram_bot.token');
    }

    /**
     * Отправляет уведомление мастеру о новой записи
     */
    public function sendMasterNotification(Appointment $appointment): bool
    {
        try {
            $master = $appointment->master;

            // Сначала проверяем, есть ли chat_id в БД
            if (! $master->telegram_chat_id) {
                Log::error('Master has no telegram_chat_id configured', [
                    'master_id' => $master->id,
                    'master_name' => $master->name,
                    'phone' => $master->phone,
                ]);

                return false;
            }

            $message = $this->formatNewAppointmentMessage($appointment);

            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $master->telegram_chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info('Master notification sent successfully', [
                    'master_id' => $master->id,
                    'master_name' => $master->name,
                    'appointment_id' => $appointment->id,
                    'chat_id' => $master->telegram_chat_id,
                ]);

                return true;
            }

            Log::error('Failed to send master notification - API error', [
                'master_id' => $master->id,
                'master_name' => $master->name,
                'appointment_id' => $appointment->id,
                'chat_id' => $master->telegram_chat_id,
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Master notification error', [
                'appointment_id' => $appointment->id,
                'master_id' => $appointment->master_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Сохраняет chat_id мастера из Telegram webhook
     * Вызывается, когда мастер отправляет /start боту с контактом
     */
    public function saveMasterChatId(string $phone, string $chatId): bool
    {
        try {
            $normalizedPhone = PhoneHelper::normalize($phone);
            $master = User::where('role', 'master')
                ->where('phone', $normalizedPhone)
                ->first();

            if (! $master) {
                Log::warning('Master not found for phone number', [
                    'phone' => $normalizedPhone,
                    'chat_id' => $chatId,
                ]);

                return false;
            }

            $master->update(['telegram_chat_id' => $chatId]);

            Log::info('Master chat_id saved', [
                'master_id' => $master->id,
                'master_name' => $master->name,
                'chat_id' => $chatId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error saving master chat_id', [
                'phone' => $phone,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Форматирует сообщение о новой записи
     */
    private function formatNewAppointmentMessage(Appointment $appointment): string
    {
        $clientName = $appointment->client->name;

        // Если у клиента есть telegram_username из нашей БД, добавляем линк
        if ($appointment->client->telegram_username) {
            $clientName = "[{$clientName}](https://t.me/{$appointment->client->telegram_username})";
        }

        return sprintf(
            "🆕 Нова реєстрація\n\n".
            "👤 Клієнт: %s\n".
            "📱 Телефон: %s\n".
            "💆 Послуга: %s\n".
            "📅 Дата: %s\n".
            "🕰 Час: %s\n\n".
            'Деталі в адмін-панелі.',
            $clientName,
            $appointment->client->phone,
            $appointment->service->name,
            $appointment->appointment_date->format('d.m.Y'),
            substr($appointment->appointment_time, 0, 5)
        );
    }
}
