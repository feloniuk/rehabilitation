<?php

namespace App\Console\Commands;

use App\Helpers\PhoneHelper;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Console\Command;

class NormalizePhones extends Command
{
    protected $signature = 'phones:normalize
                            {--dry-run : Показати зміни без збереження}';

    protected $description = 'Нормалізує телефонні номери клієнтів до формату +380XXXXXXXXX. Не об\'єднує дублікати — для злиття використовуйте phones:merge.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('=== Нормалізація телефонних номерів ===');

        if ($isDryRun) {
            $this->warn('Режим dry-run: зміни НЕ будуть збережені');
        }

        $this->newLine();

        $normalizedCount = 0;
        $collisionCount = 0;
        $collisions = [];

        User::whereNotNull('phone')->chunk(100, function ($users) use (
            $isDryRun, &$normalizedCount, &$collisionCount, &$collisions
        ) {
            foreach ($users as $user) {
                $normalized = PhoneHelper::normalize($user->phone);

                if ($normalized === $user->phone) {
                    continue;
                }

                // Перевіряємо колізію: чи існує вже інший клієнт з таким нормалізованим номером
                $existing = User::where('phone', $normalized)
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existing) {
                    $collisionCount++;
                    $collisions[] = [
                        $user->id,
                        $user->phone,
                        $existing->id,
                        $existing->phone,
                        'Колізія — обробіть вручну: phones:merge',
                    ];

                    continue;
                }

                $this->line("  #{$user->id} {$user->phone} → {$normalized}");

                if (! $isDryRun) {
                    $user->phone = $normalized;
                    $user->saveQuietly();
                    $normalizedCount++;
                } else {
                    $normalizedCount++;
                }
            }
        });

        // Нормалізуємо телефони в логах повідомлень (без колізій, бо це не унікальне поле)
        if (! $isDryRun) {
            NotificationLog::whereNotNull('phone')->chunk(100, function ($logs) {
                foreach ($logs as $log) {
                    $normalized = PhoneHelper::normalize($log->phone);
                    if ($log->phone !== $normalized) {
                        $log->phone = $normalized;
                        $log->saveQuietly();
                    }
                }
            });
        }

        $this->newLine();
        $this->info("Нормалізовано: {$normalizedCount}");

        if ($collisionCount > 0) {
            $this->warn("Колізій (пропущено, потребують ручного злиття): {$collisionCount}");
            $this->newLine();
            $this->table(
                ['ID дубл.', 'Тел. дубл.', 'ID існуючого', 'Тел. існуючого', 'Дія'],
                $collisions
            );
            $this->newLine();
            $this->comment('Для злиття запустіть: php artisan phones:merge {primary_id} {duplicate_id}');
        }

        if ($isDryRun) {
            $this->newLine();
            $this->comment('Dry-run завершено. Запустіть без --dry-run для збереження.');
        }

        return self::SUCCESS;
    }
}
