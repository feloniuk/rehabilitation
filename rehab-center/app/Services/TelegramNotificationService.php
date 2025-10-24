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
 * Сервіс для відправки повідомлень у Telegram через MadelineProto (userbot)
 */
class TelegramNotificationService
{
    private ?API $telegram = null;
    private bool $isConfigured = true;

    public function __construct()
    {
        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');

        if (!$apiId || !$apiHash) {
            Log::warning('Telegram API credentials not configured.');
            return;
        }

        try {
            $settings = new Settings;

            // Налаштування додатку
            $appInfo = new AppInfo;
            $appInfo->setApiId((int)$apiId);
            $appInfo->setApiHash($apiHash);
            $settings->setAppInfo($appInfo);

            // Налаштування логування
            $logger = new Logger;
            $logger->setType(Logger::FILE_LOGGER);
            $logger->setExtra(storage_path('logs/telegram.log'));
            $logger->setLevel(Logger::ERROR);
            $settings->setLogger($logger);

            // Ініціалізація сесії (без повторного start)
            $this->telegram = new API(storage_path('app/telegram_session.madeline'), $settings);
            $this->telegram->async(false);

            $this->isConfigured = true;
        } catch (\Exception $e) {
            Log::error('Telegram initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Перевіряє чи налаштовано Telegram
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
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
            Log::warning('Telegram session not authorized: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Відправлення повідомлення одному користувачу
     */
    public function sendMessage(string $phone, string $message): bool
    {
        if (!$this->isConfigured || !$this->isAuthorized()) {
            Log::warning('Telegram not configured or not authorized.');
            return false;
        }

        try {
            // Нормалізуємо телефон (без +)
            $phone = $this->normalizePhone($phone);

            // Отримуємо peer за телефоном
            $peer = $this->telegram->contacts->resolvePhone($phone);

            // Відправляємо повідомлення
            $this->telegram->messages->sendMessage([
                'peer' => $peer,
                'message' => $message,
            ]);

            return true;
        } catch (\danog\MadelineProto\RPCErrorException $e) {
            Log::error('Telegram RPC error: ' . $e->getMessage(), [
                'phone' => $phone,
                'error_code' => $e->rpc ?? null,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage(), [
                'phone' => $phone,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Відправлення повідомлення про запис
     */
    public function sendAppointmentNotification(Appointment $appointment, NotificationTemplate $template): NotificationLog
    {
        $message = $template->render($appointment);
        $phone = $appointment->client->phone;

        $log = NotificationLog::create([
            'appointment_id' => $appointment->id,
            'template_id' => $template->id,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        if (!$this->isConfigured) {
            $log->markAsFailed('Telegram не налаштовано. Додайте TELEGRAM_API_ID і TELEGRAM_API_HASH у .env');
            return $log;
        }

        if (!$this->isAuthorized()) {
            $log->markAsFailed('Telegram userbot не авторизований. Запустіть авторизацію вручну.');
            return $log;
        }

        $success = $this->sendMessage($phone, $message);

        if ($success) {
            $log->markAsSent();
        } else {
            $log->markAsFailed('Не вдалося відправити повідомлення в Telegram');
        }

        return $log;
    }

    /**
     * Масова розсилка
     */
    public function sendBulkNotifications(array $appointmentIds, NotificationTemplate $template): array
    {
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
                'error' => 'Telegram не налаштовано',
            ];
        }

        if (!$this->isAuthorized()) {
            return [
                'success' => 0,
                'failed' => count($appointmentIds),
                'logs' => [],
                'error' => 'Telegram userbot не авторизований',
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

            // Антиспам затримка
            sleep(2);
        }

        return $results;
    }

    /**
     * Нормалізація номера телефону (без +)
     */
    private function normalizePhone(string $phone): string
    {
        return ltrim(preg_replace('/[^0-9]/', '', $phone), '+');
    }
}
