<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use danog\MadelineProto\API;
use danog\MadelineProto\Logger;

class TelegramAuth extends Command
{
    protected $signature = 'telegram:auth';
    protected $description = 'Авторизація Telegram для розсилок';

    public function handle()
    {
        $this->info('🔐 Авторизація Telegram для модуля розсилок');
        $this->info('');

        // Перевірка налаштувань
        if (!config('services.telegram.api_id') || !config('services.telegram.api_hash')) {
            $this->error('❌ Не налаштовано TELEGRAM_API_ID або TELEGRAM_API_HASH в .env файлі');
            $this->info('');
            $this->info('Отримати API credentials можна тут: https://my.telegram.org/apps');
            return 1;
        }

        try {
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

            $this->info('📱 Ініціалізація MadelineProto...');
            
            $telegram = new API(storage_path('app/telegram_session.madeline'), $settings);
            
            $this->info('✅ Запуск процесу авторизації...');
            $this->info('');
            
            // Запуск інтерактивної авторизації
            $telegram->start();
            
            // Перевірка авторизації
            $me = $telegram->get_self();
            
            $this->info('');
            $this->info('✅ Авторизація успішна!');
            $this->info('👤 Авторизовано як: ' . ($me['first_name'] ?? 'Unknown'));
            $this->info('📞 Телефон: ' . ($me['phone'] ?? 'Unknown'));
            $this->info('');
            $this->info('🎉 Тепер ви можете використовувати модуль розсилок!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('');
            $this->error('❌ Помилка авторизації: ' . $e->getMessage());
            $this->error('');
            $this->info('💡 Спробуйте:');
            $this->info('  - Перевірити правильність API_ID та API_HASH');
            $this->info('  - Видалити файл ' . storage_path('app/telegram_session.madeline'));
            $this->info('  - Запустити команду знову');
            
            return 1;
        }
    }
}