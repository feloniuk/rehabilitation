<?php

namespace App\Services;

use App\Helpers\PhoneHelper;
use App\Models\User;
use danog\MadelineProto\API;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use danog\MadelineProto\Settings\Logger as LoggerSettings;
use Illuminate\Support\Facades\Log;

class TelegramMasterChatIdResolverService
{
    private ?API $telegram = null;

    private bool $isConfigured = false;

    public function __construct()
    {
        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');

        if (! $apiId || ! $apiHash) {
            Log::warning('Telegram API credentials not configured for resolver.');

            return;
        }

        try {
            // Очистка старых файлов блокировки
            $sessionPath = storage_path('app/telegram_session.madeline');
            $lockPath = $sessionPath.'.lock';

            if (file_exists($lockPath)) {
                unlink($lockPath);
            }

            $settings = new Settings;

            $appInfo = new AppInfo;
            $appInfo->setApiId((int) $apiId);
            $appInfo->setApiHash($apiHash);
            $settings->setAppInfo($appInfo);

            $logger = new LoggerSettings;
            $logger->setType(Logger::FILE_LOGGER);
            $logger->setExtra(storage_path('logs/telegram_chat_id_resolver.log'));
            $logger->setLevel(Logger::NOTICE);
            $settings->setLogger($logger);

            $this->telegram = new API($sessionPath, $settings);
            $this->telegram->start();
            $this->isConfigured = true;

        } catch (\Exception $e) {
            Log::error('Telegram resolver initialization error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Пытается найти и сохранить chat_id мастера по номеру телефона
     */
    public function resolveMasterChatId(User $master): ?string
    {
        if (! $this->isConfigured) {
            Log::warning('Telegram resolver not configured');

            return null;
        }

        try {
            $normalizedPhone = PhoneHelper::normalize($master->phone);

            Log::info('Attempting to resolve master chat_id', [
                'master_id' => $master->id,
                'master_name' => $master->name,
                'phone' => $normalizedPhone,
            ]);

            // Пытаемся найти пользователя через resolve контакта
            $chatId = $this->findUserChatIdByPhone($normalizedPhone);

            if ($chatId) {
                // Сохраняем найденный chat_id в БД
                $master->update(['telegram_chat_id' => $chatId]);

                Log::info('Master chat_id resolved and saved', [
                    'master_id' => $master->id,
                    'master_name' => $master->name,
                    'chat_id' => $chatId,
                ]);

                return $chatId;
            }

            Log::warning('Could not resolve master chat_id', [
                'master_id' => $master->id,
                'master_name' => $master->name,
                'phone' => $normalizedPhone,
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Error resolving master chat_id', [
                'master_id' => $master->id,
                'master_name' => $master->name,
                'phone' => $master->phone,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Находит chat_id пользователя по номеру телефона
     */
    private function findUserChatIdByPhone(string $phone): ?string
    {
        try {
            // Нормализуем номер в формат для Telegram
            $fullPhone = '+'.$phone;

            Log::info('Searching for user by phone', [
                'phone' => $fullPhone,
            ]);

            // Пытаемся резолвить контакт
            $resolved = $this->telegram->contacts->resolvePhone(
                phone: $fullPhone,
                floodWaitLimit: 10,
            );

            if (! isset($resolved['users']) || empty($resolved['users'])) {
                Log::warning('No users found for phone', ['phone' => $fullPhone]);

                return null;
            }

            $user = $resolved['users'][0];
            $chatId = (string) $user['id'];

            Log::info('User resolved successfully', [
                'phone' => $fullPhone,
                'user_id' => $chatId,
                'username' => $user['username'] ?? 'N/A',
            ]);

            return $chatId;

        } catch (\Exception $e) {
            Log::warning('Failed to resolve phone contact', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            return null;
        }
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }
}
