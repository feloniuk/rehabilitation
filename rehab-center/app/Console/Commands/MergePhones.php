<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergePhones extends Command
{
    protected $signature = 'phones:merge
                            {primary : ID головного клієнта (залишається)}
                            {duplicate : ID дублікату (буде soft deleted)}
                            {--force : Виконати без підтвердження}';

    protected $description = 'Переносить записи від дублікату до головного клієнта та soft-delete\'ить дублікат. Всі операції йдуть через модель — Observer логує аудит.';

    public function handle(): int
    {
        $primaryId = (int) $this->argument('primary');
        $duplicateId = (int) $this->argument('duplicate');

        if ($primaryId === $duplicateId) {
            $this->error('primary та duplicate не можуть бути однаковими.');

            return self::FAILURE;
        }

        $primary = User::find($primaryId);
        $duplicate = User::find($duplicateId);

        if (! $primary) {
            $this->error("Клієнт з ID {$primaryId} не знайдений.");

            return self::FAILURE;
        }

        if (! $duplicate) {
            $this->error("Клієнт з ID {$duplicateId} не знайдений.");

            return self::FAILURE;
        }

        $appointmentsToMove = Appointment::where('client_id', $duplicateId)->get();

        $this->info('=== Злиття клієнтів ===');
        $this->newLine();
        $this->line("Головний:   #{$primary->id} {$primary->name} ({$primary->phone})");
        $this->line("Дублікат:   #{$duplicate->id} {$duplicate->name} ({$duplicate->phone})");
        $this->line("Записів для переносу: {$appointmentsToMove->count()}");
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Продовжити? Дублікат буде soft-deleted.')) {
            $this->info('Скасовано.');

            return self::SUCCESS;
        }

        DB::beginTransaction();

        try {
            // Переносимо записи ІТЕРАТИВНО через модель (щоб спрацював Observer і залишився аудит)
            $movedCount = 0;
            foreach ($appointmentsToMove as $appointment) {
                $appointment->update(['client_id' => $primaryId]);
                $movedCount++;
                $this->line("  Перенесено запис #{$appointment->id} ({$appointment->appointment_date->format('d.m.Y')} {$appointment->appointment_time})");
            }

            // Переносимо telegram якщо є у дублікату і немає у головного
            if (! empty($duplicate->telegram_username) && empty($primary->telegram_username)) {
                $primary->telegram_username = $duplicate->telegram_username;
                $primary->save();
                $this->line('  Перенесено telegram_username');
            }

            // Переносимо опис якщо є у дублікату і немає у головного
            if (! empty($duplicate->description) && empty($primary->description)) {
                $primary->description = $duplicate->description;
                $primary->save();
                $this->line('  Перенесено description');
            }

            // Переносимо логи повідомлень
            NotificationLog::where('phone', $duplicate->phone)
                ->update(['phone' => $primary->phone]);

            // Тільки soft delete — НІКОЛИ force delete
            $duplicate->delete();
            $this->line("  Soft deleted дублікат #{$duplicate->id}");

            DB::commit();

            $this->newLine();
            $this->info("Готово! Перенесено записів: {$movedCount}. Дублікат #{$duplicate->id} soft deleted.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Помилка: {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
