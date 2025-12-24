<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use App\Models\Appointment;
use App\Models\MasterNotificationLog;
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
            $message = $this->formatNewAppointmentMessage($appointment);

            // Создаем лог записи сразу (статус pending)
            $notificationLog = MasterNotificationLog::create([
                'appointment_id' => $appointment->id,
                'master_id' => $master->id,
                'phone' => $master->phone,
                'status' => 'pending',
                'message' => $message,
            ]);

            // Сначала проверяем, есть ли chat_id в БД
            $chatId = $master->telegram_chat_id;
            $resolutionSource = 'database';

            // Если нет chat_id - пытаемся найти через резолвер
            if (! $chatId) {
                Log::info('Master has no chat_id, attempting to resolve by phone', [
                    'master_id' => $master->id,
                    'master_name' => $master->name,
                    'phone' => $master->phone,
                ]);

                $resolver = new TelegramMasterChatIdResolverService;
                $chatId = $resolver->resolveMasterChatId($master);
                $resolutionSource = 'resolver';
            }

            // Если все равно не нашли - логируем ошибку и выходим
            if (! $chatId) {
                $errorMsg = 'Could not determine telegram_chat_id for master';

                Log::error($errorMsg, [
                    'master_id' => $master->id,
                    'master_name' => $master->name,
                    'phone' => $master->phone,
                    'notification_log_id' => $notificationLog->id,
                ]);

                $notificationLog->markAsFailed($errorMsg);

                return false;
            }

            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info('Master notification sent successfully', [
                    'master_id' => $master->id,
                    'master_name' => $master->name,
                    'appointment_id' => $appointment->id,
                    'chat_id' => $chatId,
                    'notification_log_id' => $notificationLog->id,
                ]);

                $notificationLog->markAsSent($chatId, $resolutionSource);

                return true;
            }

            $errorMsg = 'Telegram API error: '.$response->body();

            Log::error('Failed to send master notification - API error', [
                'master_id' => $master->id,
                'master_name' => $master->name,
                'appointment_id' => $appointment->id,
                'chat_id' => $chatId,
                'response' => $response->body(),
                'notification_log_id' => $notificationLog->id,
            ]);

            $notificationLog->markAsFailed($errorMsg);

            return false;

        } catch (\Exception $e) {
            Log::error('Master notification error', [
                'appointment_id' => $appointment->id,
                'master_id' => $appointment->master_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($notificationLog)) {
                $notificationLog->markAsFailed($e->getMessage());
            }

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

            Log::info('Master chat_id saved from webhook', [
                'master_id' => $master->id,
                'master_name' => $master->name,
                'phone' => $normalizedPhone,
                'chat_id' => $chatId,
                'source' => 'bot_webhook',
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
