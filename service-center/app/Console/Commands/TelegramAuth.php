<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use danog\MadelineProto\API;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use danog\MadelineProto\Settings\Logger as LoggerSettings;

class TelegramAuth extends Command
{
    protected $signature = 'telegram:auth';
    protected $description = 'Авторізація Telegram для розсилок';

    public function handle()
    {
        $this->info('🔐 Авторізація Telegram для модуля розсилок');
        $this->info('');
        
        // Перевірка налаштувань
        if (!config('services.telegram.api_id') || !config('services.telegram.api_hash')) {
            $this->error('❌ Не налаштовано TELEGRAM_API_ID або TELEGRAM_API_HASH в .env файлі');
            $this->info('');
            $this->info('Отримати API credentials можна тут: https://my.telegram.org/apps');
            return 1;
        }
        
        try {
            // Створюємо об'єкт налаштувань
            $settings = new Settings;
            
            // Налаштування додатку
            $appInfo = new AppInfo;
            $appInfo->setApiId((int) config('services.telegram.api_id'));
            $appInfo->setApiHash(config('services.telegram.api_hash'));
            $settings->setAppInfo($appInfo);
            
            // Налаштування логування
            // ВАЖНО: використовуємо Logger::FILE_LOGGER з danog\MadelineProto\Logger
            $logger = new LoggerSettings;
            $logger->setType(Logger::FILE_LOGGER); // Константа int з danog\MadelineProto\Logger
            $logger->setExtra(storage_path('logs/telegram.log'));
            $logger->setLevel(Logger::ERROR);
            $settings->setLogger($logger);
            
            $this->info('📱 Ініціалізація MadelineProto...');
            
            $telegram = new API(
                storage_path('app/telegram_session.madeline'),
                $settings
            );
            
            $this->info('✅ Запуск процесу авторізації...');
            $this->info('');
            
            // Запуск інтерактивної авторізації
            $telegram->start();
            
            // Перевірка авторізації
            $me = $telegram->getSelf();
            
            $this->info('');
            $this->info('✅ Авторізація успішна!');
            $this->info('👤 Авторизовано як: ' . ($me['first_name'] ?? 'Unknown'));
            $this->info('📞 Телефон: ' . ($me['phone'] ?? 'Unknown'));
            $this->info('');
            $this->info('🎉 Тепер ви можете використовувати модуль розсилок!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('');
            $this->error('❌ Помилка авторізації: ' . $e->getMessage());
            $this->error('');
            $this->info('💡 Спробуйте:');
            $this->info('  - Перевірити правильність API_ID та API_HASH');
            $this->info('  - Видалити файл ' . storage_path('app/telegram_session.madeline'));
            $this->info('  - Запустити команду знову');
            
            return 1;
        }
    }
}