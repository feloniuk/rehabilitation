<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use danog\MadelineProto\Settings\Logger;
use Illuminate\Support\Facades\Log;

/**
 * Сервіс для відправки повідомлень через Telegram (userbot)
 * Використовує MadelineProto для роботи як звичайний користувач
 */
class TelegramNotificationService
{
    private ?API $telegram = null;
    private bool $isConfigured = false;

    public function __construct()
    {
        // Перевіряємо чи налаштовано Telegram
        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');

        if (!$apiId || !$apiHash) {
            Log::warning('Telegram API credentials not configured');
            $this->isConfigured = false;
            return;
        }

        try {
            // Створюємо об'єкт налаштувань
            $settings = new Settings;
            
            // Налаштування додатку
            $appInfo = new AppInfo;
            $appInfo->setApiId((int) $apiId);
            $appInfo->setApiHash($apiHash);
            $settings->setAppInfo($appInfo);
            
            // Налаштування логування
            $logger = new Logger;
            $logger->setType(Logger::FILE_LOGGER);
            $logger->setExtra(storage_path('logs/telegram.log'));
            $logger->setLevel(Logger::ERROR);
            $settings->setLogger($logger);

            // Ініціалізація API
            $this->telegram = new API(
                storage_path('app/telegram_session.madeline'),
                $settings
            );
            
            $this->telegram->start();
            $this->isConfigured = true;
        } catch (\Exception $e) {
            Log::error('Telegram initialization error: ' . $e->getMessage());
            $this->isConfigured = false;
        }
    }

    /**
     * Перевірка чи налаштовано Telegram
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Відправити повідомлення одному користувачу
     */
    public function sendMessage(string $phone, string $message): bool
    {
        if (!$this->isConfigured) {
            Log::warning('Telegram not configured, cannot send message');
            return false;
        }

        try {
            // Нормалізація номера телефону
            $phone = $this->normalizePhone($phone);

            // Відправка повідомлення
            $this->telegram->messages->sendMessage([
                'peer' => $phone,
                'message' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage(), [
                'phone' => $phone,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Відправити нагадування про запис
     */
    public function sendAppointmentNotification(
        Appointment $appointment,
        NotificationTemplate $template
    ): NotificationLog {
        $message = $template->render($appointment);
        $phone = $appointment->client->phone;

        // Створюємо лог
        $log = NotificationLog::create([
            'appointment_id' => $appointment->id,
            'template_id' => $template->id,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        if (!$this->isConfigured) {
            $log->markAsFailed('Telegram не налаштовано. Додайте TELEGRAM_API_ID та TELEGRAM_API_HASH в .env');
            return $log;
        }

        // Відправляємо повідомлення
        $success = $this->sendMessage($phone, $message);

        if ($success) {
            $log->markAsSent();
        } else {
            $log->markAsFailed('Не вдалося відправити повідомлення в Telegram');
        }

        return $log;
    }

    /**
     * Масова розсилка для кількох записів
     */
    public function sendBulkNotifications(
        array $appointmentIds,
        NotificationTemplate $template
    ): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'logs' => [],
        ];

        if (!$this->isConfigured) {
            return [
                'success' => 0,
                'failed' => count($appointmentIds),
                'logs' => [],
                'error' => 'Telegram не налаштовано'
            ];
        }

        $appointments = Appointment::with(['client', 'master', 'service'])
            ->whereIn('id', $appointmentIds)
            ->get();

        foreach ($appointments as $appointment) {
            $log = $this->sendAppointmentNotification($appointment, $template);
            $results['logs'][] = $log;

            if ($log->status === 'sent') {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            // Затримка між повідомленнями (антиспам)
            sleep(2);
        }

        return $results;
    }

    /**
     * Нормалізація номера телефону для Telegram
     */
    private function normalizePhone(string $phone): string
    {
        // Видаляємо всі символи крім цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Додаємо + якщо немає
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Перевірка активності сесії
     */
    public function isAuthorized(): bool
    {
        if (!$this->isConfigured) {
            return false;
        }

        try {
            $this->telegram->getSelf();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}