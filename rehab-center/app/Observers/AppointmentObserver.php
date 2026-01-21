<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\AppointmentAuditLog;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $this->logAction($appointment, 'created', null, $appointment->toArray());
    }

    public function updated(Appointment $appointment): void
    {
        $oldValues = $appointment->getOriginal();
        $newValues = $appointment->getChanges();

        // Не логувати якщо змінився тільки updated_at
        if (count($newValues) === 1 && isset($newValues['updated_at'])) {
            return;
        }

        $this->logAction($appointment, 'updated', $oldValues, $newValues);
    }

    public function deleting(Appointment $appointment): void
    {
        // Зберігаємо повні дані запису перед видаленням
        $fullData = $appointment->load(['client', 'master', 'service'])->toArray();

        $this->logAction($appointment, 'deleted', $fullData, null);

        // Додатково логуємо в Laravel лог для відстеження
        Log::warning('Appointment being deleted', [
            'appointment_id' => $appointment->id,
            'client_id' => $appointment->client_id,
            'client_name' => $appointment->client->name ?? 'N/A',
            'client_phone' => $appointment->client->phone ?? 'N/A',
            'master_id' => $appointment->master_id,
            'master_name' => $appointment->master->name ?? 'N/A',
            'service_name' => $appointment->service->name ?? 'N/A',
            'date' => $appointment->appointment_date,
            'time' => $appointment->appointment_time,
            'deleted_by_user_id' => auth()->id(),
            'deleted_by_user_type' => $this->getUserType(),
            'ip' => request()->ip(),
        ]);
    }

    public function restored(Appointment $appointment): void
    {
        $this->logAction($appointment, 'restored', null, $appointment->toArray());
    }

    private function logAction(Appointment $appointment, string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            AppointmentAuditLog::create([
                'appointment_id' => $appointment->id,
                'action' => $action,
                'user_id' => auth()->id(),
                'user_type' => $this->getUserType(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create appointment audit log', [
                'appointment_id' => $appointment->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getUserType(): ?string
    {
        $user = auth()->user();

        if (! $user) {
            // Перевіряємо чи це консольна команда
            if (app()->runningInConsole()) {
                return 'system';
            }

            return null;
        }

        return $user->role ?? 'unknown';
    }
}
