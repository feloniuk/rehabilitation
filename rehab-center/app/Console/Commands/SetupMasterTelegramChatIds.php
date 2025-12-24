<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetupMasterTelegramChatIds extends Command
{
    protected $signature = 'telegram:setup-master-chat-ids {--interactive}';

    protected $description = 'Setup telegram chat_ids for masters';

    public function handle(): int
    {
        $this->info('=== Setup Master Telegram Chat IDs ===');
        $this->newLine();

        $masters = User::where('role', 'master')
            ->whereNull('telegram_chat_id')
            ->get();

        if ($masters->isEmpty()) {
            $this->info('✓ Все мастера имеют telegram_chat_id!');
            return self::SUCCESS;
        }

        $this->warn("Найдено {$masters->count()} мастеров без telegram_chat_id:\n");

        foreach ($masters as $master) {
            $this->line("  • ID: {$master->id}, Имя: {$master->name}, Телефон: {$master->phone}");
        }

        $this->newLine();

        if ($this->option('interactive')) {
            $this->handleInteractive($masters);
        } else {
            $this->showInstructions($masters);
        }

        return self::SUCCESS;
    }

    private function showInstructions(mixed $masters): void
    {
        $this->info('Инструкции для заполнения telegram_chat_id:');
        $this->newLine();
        $this->line('1. Каждый мастер должен авторизоваться в Telegram боте');
        $this->line('2. Отправить команду /start с контактом (+38XXXXXXXXXXX)');
        $this->line('3. chat_id будет сохранен автоматически');
        $this->newLine();

        if ($this->confirm('Хотите вручную добавить chat_id для какого-то мастера?')) {
            $this->handleInteractive($masters);
        } else {
            $this->line('Или запустите эту команду с флагом:');
            $this->line('  php artisan telegram:setup-master-chat-ids --interactive');
        }
    }

    private function handleInteractive(mixed $masters): void
    {
        foreach ($masters as $master) {
            $this->newLine();
            $this->line("Мастер: {$master->name} ({$master->phone})");

            $chatId = $this->ask('Введите telegram chat_id (или пропустите)');

            if (empty($chatId)) {
                $this->line('  → Пропущено');
                continue;
            }

            if (!is_numeric($chatId)) {
                $this->error('  → Ошибка: chat_id должен быть числом');
                continue;
            }

            $master->update(['telegram_chat_id' => $chatId]);
            $this->info("  ✓ Сохранено: {$chatId}");
        }

        $this->newLine();
        $this->info('Готово!');
    }
}
