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
        $message = $this->formatNewAppointmentMessage($appointment);

        return $this->sendMessage($appointment, $message);
    }

    /**
     * Отправляет уведомление мастеру об отмене записи
     */
    public function sendCancellationNotification(Appointment $appointment): bool
    {
        $message = $this->formatCancelledAppointmentMessage($appointment);

        return $this->sendMessage($appointment, $message);
    }

    /**
     * Отправляет сообщение мастеру
     */
    private function sendMessage(Appointment $appointment, string $message): bool
    {
        try {
            $master = $appointment->master;

            $notificationLog = MasterNotificationLog::create([
                'appointment_id' => $appointment->id,
                'master_id' => $master->id,
                'phone' => $master->phone,
                'status' => 'pending',
                'message' => $message,
            ]);

            $chatId = $master->telegram_chat_id;
            $resolutionSource = 'database';

            if (! $chatId) {
                Log::info('Master has no chat_id, attempting to resolve by phone', [
                    'master_id' => $master->id,
                    'phone' => $master->phone,
                ]);

                $resolver = new TelegramMasterChatIdResolverService;
                $chatId = $resolver->resolveMasterChatId($master);
                $resolutionSource = 'resolver';
            }

            if (! $chatId) {
                $errorMsg = 'Could not determine telegram_chat_id for master';
                Log::error($errorMsg, ['master_id' => $master->id]);
                $notificationLog->markAsFailed($errorMsg);

                return false;
            }

            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info('Master notification sent', [
                    'master_id' => $master->id,
                    'appointment_id' => $appointment->id,
                ]);
                $notificationLog->markAsSent($chatId, $resolutionSource);

                return true;
            }

            $errorMsg = 'Telegram API error: '.$response->body();
            Log::error($errorMsg, ['master_id' => $master->id]);
            $notificationLog->markAsFailed($errorMsg);

            return false;

        } catch (\Exception $e) {
            Log::error('Master notification error', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($notificationLog)) {
                $notificationLog->markAsFailed($e->getMessage());
            }

            return false;
        }
    }

    /**
     * Сохраняет chat_id мастера из Telegram webhook
     */
    public function saveMasterChatId(string $phone, string $chatId): bool
    {
        try {
            $normalizedPhone = PhoneHelper::normalize($phone);
            $master = User::where('role', 'master')
                ->where('phone', $normalizedPhone)
                ->first();

            if (! $master) {
                Log::warning('Master not found for phone number', ['phone' => $normalizedPhone]);

                return false;
            }

            $master->update(['telegram_chat_id' => $chatId]);
            Log::info('Master chat_id saved from webhook', [
                'master_id' => $master->id,
                'chat_id' => $chatId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error saving master chat_id', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function formatNewAppointmentMessage(Appointment $appointment): string
    {
        $clientName = $this->formatClientName($appointment);

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

    private function formatCancelledAppointmentMessage(Appointment $appointment): string
    {
        $clientName = $this->formatClientName($appointment);

        return sprintf(
            "❌ Скасування запису\n\n".
            "👤 Клієнт: %s\n".
            "📱 Телефон: %s\n".
            "💆 Послуга: %s\n".
            "📅 Дата: %s\n".
            "🕰 Час: %s\n\n".
            'Запис було скасовано.',
            $clientName,
            $appointment->client->phone,
            $appointment->service->name,
            $appointment->appointment_date->format('d.m.Y'),
            substr($appointment->appointment_time, 0, 5)
        );
    }

    private function formatClientName(Appointment $appointment): string
    {
        $clientName = $appointment->client->name;

        if ($appointment->client->telegram_username) {
            $clientName = "[{$clientName}](https://t.me/{$appointment->client->telegram_username})";
        }

        return $clientName;
    }
}
