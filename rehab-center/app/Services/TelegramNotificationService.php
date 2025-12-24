<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use danog\MadelineProto\API;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use danog\MadelineProto\Settings\Logger as LoggerSettings;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private ?API $telegram = null;

    private bool $isConfigured = false;

    public function __construct()
    {
        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');

        if (! $apiId || ! $apiHash) {
            Log::warning('Telegram API credentials not configured.');

            return;
        }

        try {
            // Очистка старих сесійних файлів
            $sessionPath = storage_path('app/telegram_session.madeline');
            $lockPath = $sessionPath.'.lock';

            if (file_exists($lockPath)) {
                unlink($lockPath);
            }

            $settings = new Settings;

            // Налаштування додатку
            $appInfo = new AppInfo;
            $appInfo->setApiId((int) $apiId);
            $appInfo->setApiHash($apiHash);
            $settings->setAppInfo($appInfo);

            // Розширені налаштування логування
            $logger = new LoggerSettings;
            $logger->setType(Logger::FILE_LOGGER);
            $logger->setExtra(storage_path('logs/telegram_detailed.log'));
            $logger->setLevel(Logger::NOTICE);
            $settings->setLogger($logger);

            // Додаткові налаштування
            // $settings->setAllowUpdates(false);

            $this->telegram = new API($sessionPath, $settings);

            // Примусова перевірка сесії
            $this->telegram->start();

            $this->isConfigured = true;

        } catch (\Exception $e) {
            Log::error('Telegram initialization error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    public function isAuthorized(): bool
    {
        if (! $this->isConfigured) {
            return false;
        }

        try {
            $this->telegram->getSelf();

            return true;
        } catch (\Exception $e) {
            Log::warning('Telegram session not authorized: '.$e->getMessage());

            return false;
        }
    }

    public function sendMessage(string $phone, string $message): bool
    {
        if (! $this->isConfigured || ! $this->isAuthorized()) {
            Log::warning('Telegram not configured or not authorized.');

            return false;
        }

        try {
            // Нормалізація телефону
            $phone = $this->normalizePhone($phone);
            $fullPhone = '+'.$phone;

            // Логування всіх спроб
            Log::info('Attempting to send message', [
                'phone' => $fullPhone,
                'message_length' => strlen($message),
            ]);

            // Крок 1: Спроба надіслати безпосередньо
            try {
                $result = $this->telegram->messages->sendMessage([
                    'peer' => $fullPhone,
                    'message' => $message,
                    'random_id' => random_int(0, PHP_INT_MAX),
                ]);

                Log::info('Message sent successfully', [
                    'phone' => $fullPhone,
                    'method' => 'direct',
                ]);

                return true;
            } catch (\Exception $e) {
                Log::warning('Direct send failed, attempting peer resolution', [
                    'phone' => $fullPhone,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                ]);
            }

            // Крок 2: Резолв телефону та спроба додати в контакти
            try {
                $resolvedData = $this->telegram->contacts->resolvePhone(
                    phone: $fullPhone,
                    floodWaitLimit: 10
                );

                if (empty($resolvedData['users'])) {
                    Log::error('No user found during resolve', [
                        'phone' => $fullPhone,
                    ]);

                    return false;
                }

                $user = $resolvedData['users'][0];
                $userId = $user['id'];
                $accessHash = $user['access_hash'] ?? 0;

                Log::info('User resolved successfully', [
                    'phone' => $fullPhone,
                    'user_id' => $userId,
                ]);

                // Крок 3: Спроба додати користувача в контакти (це допоможе MadelineProto зберегти peer)
                try {
                    $this->telegram->contacts->addContact([
                        'id' => [
                            '_' => 'inputUser',
                            'user_id' => $userId,
                            'access_hash' => $accessHash,
                        ],
                        'first_name' => $user['first_name'] ?? 'Contact',
                        'last_name' => $user['last_name'] ?? '',
                        'phone' => $fullPhone,
                    ]);

                    Log::info('Contact added/updated in userbot', [
                        'phone' => $fullPhone,
                        'user_id' => $userId,
                    ]);
                } catch (\Exception $addContactError) {
                    Log::warning('Failed to add contact (non-critical)', [
                        'phone' => $fullPhone,
                        'error' => $addContactError->getMessage(),
                    ]);
                    // Це не критична помилка - продовжуємо спробу надіслати
                }

                // Крок 4: Спроба надіслати з user_id після додання в контакти
                try {
                    $result = $this->telegram->messages->sendMessage([
                        'peer' => [
                            '_' => 'inputPeerUser',
                            'user_id' => $userId,
                            'access_hash' => $accessHash,
                        ],
                        'message' => $message,
                        'random_id' => random_int(0, PHP_INT_MAX),
                    ]);

                    Log::info('Message sent after contact resolution', [
                        'phone' => $fullPhone,
                        'user_id' => $userId,
                        'method' => 'after_contact_add',
                    ]);

                    return true;
                } catch (\Exception $sendError) {
                    Log::error('Failed to send after contact resolution', [
                        'phone' => $fullPhone,
                        'user_id' => $userId,
                        'error' => $sendError->getMessage(),
                        'error_class' => get_class($sendError),
                    ]);

                    // Крок 5: Остання спроба - спробувати через importContacts
                    return $this->sendViaImportContacts($phone, $message, $user);
                }
            } catch (\Exception $resolveError) {
                Log::error('Failed to resolve phone', [
                    'phone' => $fullPhone,
                    'error' => $resolveError->getMessage(),
                    'error_class' => get_class($resolveError),
                ]);

                return false;
            }
        } catch (\Exception $globalException) {
            Log::error('Global Telegram error', [
                'phone' => $phone,
                'error' => $globalException->getMessage(),
                'error_class' => get_class($globalException),
                'trace' => $globalException->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Спроба надіслати через importContacts (останній вихід)
     */
    private function sendViaImportContacts(string $phone, string $message, array $user): bool
    {
        try {
            $fullPhone = '+'.$phone;

            // Імпортуємо контакт
            $importResult = $this->telegram->contacts->importContacts([
                'contacts' => [
                    [
                        '_' => 'inputPhoneContact',
                        'client_id' => random_int(0, PHP_INT_MAX),
                        'phone' => $fullPhone,
                        'first_name' => $user['first_name'] ?? 'Contact',
                        'last_name' => $user['last_name'] ?? '',
                    ],
                ],
            ]);

            Log::info('Contact imported', [
                'phone' => $fullPhone,
                'user_id' => $user['id'],
            ]);

            // Після імпорту спробуємо надіслати знову
            $result = $this->telegram->messages->sendMessage([
                'peer' => [
                    '_' => 'inputPeerUser',
                    'user_id' => $user['id'],
                    'access_hash' => $user['access_hash'] ?? 0,
                ],
                'message' => $message,
                'random_id' => random_int(0, PHP_INT_MAX),
            ]);

            Log::info('Message sent after import contacts', [
                'phone' => $fullPhone,
                'user_id' => $user['id'],
                'method' => 'after_import',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Final attempt failed - importContacts', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            return false;
        }
    }

    private function findUserPeer(string $phone): ?array
    {
        try {
            // Спроба резолву через contacts.resolvePhone
            try {
                $resolvedPeer = $this->telegram->contacts->resolvePhone(
                    phone: $phone,
                    floodWaitLimit: 10,
                    queueId: null,
                    cancellation: null
                );

                Log::info('Resolved via contacts', [
                    'phone' => $phone,
                    'peer' => $resolvedPeer,
                ]);

                return $resolvedPeer;
            } catch (\Exception $contactResolveError) {
                Log::warning('Contact resolve failed', [
                    'phone' => $phone,
                    'error' => $contactResolveError->getMessage(),
                    'error_class' => get_class($contactResolveError),
                ]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Peer finding global error', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            return null;
        }
    }

    public function sendAppointmentNotification(Appointment $appointment, NotificationTemplate $template): NotificationLog
    {
        $message = $template->render($appointment);
        $phone = $this->normalizePhone($appointment->client->phone);

        $log = NotificationLog::create([
            'appointment_id' => $appointment->id,
            'template_id' => $template->id,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        if (! $this->isConfigured) {
            $log->markAsFailed('Telegram не налаштовано');

            return $log;
        }

        if (! $this->isAuthorized()) {
            $log->markAsFailed('Telegram userbot не авторизований');

            return $log;
        }

        $success = $this->sendMessage($phone, $message);

        if ($success) {
            $log->markAsSent();

            $appointment->update([
                'telegram_notification_sent' => true,
            ]);
        } else {
            $log->markAsFailed('Не вдалося надіслати повідомлення');
        }

        return $log;
    }

    public function sendBulkNotifications(array $appointmentIds, NotificationTemplate $template): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'logs' => [],
        ];

        if (! $this->isConfigured) {
            return [
                'success' => 0,
                'failed' => count($appointmentIds),
                'logs' => [],
                'error' => 'Telegram не налаштовано',
            ];
        }

        if (! $this->isAuthorized()) {
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
                $appointment->update(['telegram_notification_sent' => true]);
                $results['success']++;
            } else {
                $results['failed']++;
            }

            // Антиспам затримка
            sleep(2);
        }

        return $results;
    }

    private function normalizePhone(string $phone): string
    {
        // Видаляємо всі нецифрові символи
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        // Додаємо код країни, якщо відсутній
        if (strpos($normalized, '38') !== 0 && strlen($normalized) == 10) {
            $normalized = '38'.$normalized;
        }

        return $normalized;
    }
}
