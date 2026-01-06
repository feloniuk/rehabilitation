<?php

namespace App\Console\Commands;

use App\Helpers\PhoneHelper;
use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizePhones extends Command
{
    protected $signature = 'phones:normalize
                            {--dry-run : Показати зміни без збереження}
                            {--force : Виконати без підтвердження}';

    protected $description = 'Нормалізує телефонні номери та об\'єднує дублікати клієнтів';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('=== Нормалізація телефонних номерів ===');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('Режим dry-run: зміни НЕ будуть збережені');
            $this->newLine();
        }

        // 1. Показуємо поточний стан
        $this->showCurrentState();

        // 2. Знаходимо дублікати
        $duplicates = $this->findDuplicates();

        if ($duplicates->isEmpty()) {
            $this->info('Дублікатів не знайдено!');
        } else {
            $this->warn("Знайдено {$duplicates->count()} груп дублікатів:");
            $this->newLine();

            foreach ($duplicates as $normalizedPhone => $users) {
                $this->line("Телефон: {$normalizedPhone}");
                foreach ($users as $user) {
                    $appointmentsCount = Appointment::where('client_id', $user->id)->count();
                    $this->line("  - ID: {$user->id}, Ім'я: {$user->name}, Тел: {$user->phone}, Записів: {$appointmentsCount}");
                }
                $this->newLine();
            }
        }

        // 3. Запитуємо підтвердження
        if (! $isDryRun && ! $this->option('force')) {
            if (! $this->confirm('Продовжити нормалізацію та об\'єднання дублікатів?')) {
                $this->info('Операцію скасовано.');

                return self::SUCCESS;
            }
        }

        if ($isDryRun) {
            $this->info('Dry-run завершено. Запустіть без --dry-run для застосування змін.');

            return self::SUCCESS;
        }

        // 4. Виконуємо нормалізацію
        DB::beginTransaction();

        try {
            // Об'єднуємо дублікати
            $mergedCount = $this->mergeDuplicates($duplicates);

            // Нормалізуємо всі телефони
            $normalizedCount = $this->normalizeAllPhones();

            DB::commit();

            $this->newLine();
            $this->info('Готово!');
            $this->info("- Об'єднано дублікатів: {$mergedCount}");
            $this->info("- Нормалізовано номерів: {$normalizedCount}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Помилка: {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function showCurrentState(): void
    {
        $users = User::whereNotNull('phone')->get();

        $stats = [
            'total' => $users->count(),
            'already_normalized' => 0,
            'needs_normalization' => 0,
        ];

        foreach ($users as $user) {
            $normalized = PhoneHelper::normalize($user->phone);
            if ($user->phone === $normalized) {
                $stats['already_normalized']++;
            } else {
                $stats['needs_normalization']++;
            }
        }

        $this->table(
            ['Метрика', 'Кількість'],
            [
                ['Всього користувачів з телефоном', $stats['total']],
                ['Вже нормалізовані', $stats['already_normalized']],
                ['Потребують нормалізації', $stats['needs_normalization']],
            ]
        );
        $this->newLine();
    }

    private function findDuplicates(): \Illuminate\Support\Collection
    {
        $users = User::where('role', 'client')
            ->whereNotNull('phone')
            ->get();

        // Групуємо по нормалізованому телефону
        $grouped = $users->groupBy(function ($user) {
            return PhoneHelper::normalize($user->phone);
        });

        // Залишаємо тільки групи з більш ніж одним користувачем
        return $grouped->filter(function ($group) {
            return $group->count() > 1;
        });
    }

    private function mergeDuplicates(\Illuminate\Support\Collection $duplicates): int
    {
        $mergedCount = 0;

        foreach ($duplicates as $normalizedPhone => $users) {
            // Сортуємо: той хто має більше записів - головний
            $sorted = $users->sortByDesc(function ($user) {
                return Appointment::where('client_id', $user->id)->count();
            });

            $primary = $sorted->first();
            $duplicatesToMerge = $sorted->slice(1);

            foreach ($duplicatesToMerge as $duplicate) {
                $this->line("Об'єднуємо #{$duplicate->id} ({$duplicate->phone}) → #{$primary->id} ({$primary->phone})");

                // Переносимо записи
                $movedAppointments = Appointment::where('client_id', $duplicate->id)
                    ->update(['client_id' => $primary->id]);

                // Переносимо логи повідомлень
                NotificationLog::where('phone', $duplicate->phone)
                    ->update(['phone' => $normalizedPhone]);

                // Зберігаємо telegram якщо є
                if (! empty($duplicate->telegram_username) && empty($primary->telegram_username)) {
                    $primary->telegram_username = $duplicate->telegram_username;
                    $primary->save();
                }

                // Зберігаємо опис якщо є
                if (! empty($duplicate->description) && empty($primary->description)) {
                    $primary->description = $duplicate->description;
                    $primary->save();
                }

                $this->line("  - Перенесено записів: {$movedAppointments}");

                // Видаляємо дублікат
                $duplicate->delete();
                $mergedCount++;
            }
        }

        return $mergedCount;
    }

    private function normalizeAllPhones(): int
    {
        $count = 0;

        User::whereNotNull('phone')->chunk(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                $normalized = PhoneHelper::normalize($user->phone);
                if ($user->phone !== $normalized) {
                    $user->phone = $normalized;
                    $user->saveQuietly(); // Без events щоб уникнути рекурсії
                    $count++;
                }
            }
        });

        // Нормалізуємо телефони в логах повідомлень
        NotificationLog::whereNotNull('phone')->chunk(100, function ($logs) {
            foreach ($logs as $log) {
                $normalized = PhoneHelper::normalize($log->phone);
                if ($log->phone !== $normalized) {
                    $log->phone = $normalized;
                    $log->saveQuietly();
                }
            }
        });

        return $count;
    }
}
