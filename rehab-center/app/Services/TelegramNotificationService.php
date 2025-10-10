<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use danog\MadelineProto\API;
use danog\MadelineProto\Logger;

/**
 * Сервіс для відправки повідомлень через Telegram (userbot)
 * Використовує MadelineProto для роботи як звичайний користувач
 */
class TelegramNotificationService
{
    private API $telegram;

    public function __construct()
    {
        // Ініціалізація MadelineProto
        $settings = [
            'app_info' => [
                'api_id' => config('services.telegram.api_id'),
                'api_hash' => config('services.telegram.api_hash'),
            ],
            'logger' => [
                'logger' => Logger::FILE_LOGGER,
                'logger_param' => storage_path('logs/telegram.log'),
                'logger_level' => Logger::NOTICE,
            ],
        ];

        $this->telegram = new API(storage_path('app/telegram_session.madeline'), $settings);
        $this->telegram->start();
    }

    /**
     * Відправити повідомлення одному користувачу
     */
    public function sendMessage(string $phone, string $message): bool
    {
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
            \Log::error('Telegram send error: ' . $e->getMessage(), [
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
        try {
            $this->telegram->get_self();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}