<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\AppointmentAuditLog;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $appointment->load(['client', 'master', 'service']);

        $newValues = $this->formatAppointmentData($appointment);

        $this->logAction($appointment, 'created', null, $newValues);
    }

    public function updated(Appointment $appointment): void
    {
        $changes = $appointment->getChanges();

        // Не логувати якщо змінився тільки updated_at
        if (count($changes) === 1 && isset($changes['updated_at'])) {
            return;
        }

        $appointment->load(['client', 'master', 'service']);

        $oldValues = $this->getOldValuesWithRelations($appointment);
        $newValues = $this->formatAppointmentData($appointment);

        // Логуємо тільки змінені поля
        $logOldValues = [];
        $logNewValues = [];
        foreach (array_keys($changes) as $field) {
            if ($field !== 'updated_at') {
                $logOldValues[$field] = $oldValues[$field] ?? null;
                $logNewValues[$field] = $newValues[$field] ?? null;
            }
        }

        $this->logAction($appointment, 'updated', $logOldValues, $logNewValues);
    }

    public function deleting(Appointment $appointment): void
    {
        // Зберігаємо ПОВНІ дані запису перед видаленням
        $appointment->load(['client', 'master', 'service']);

        $fullData = $this->formatAppointmentData($appointment);

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
        $appointment->load(['client', 'master', 'service']);

        $newValues = $this->formatAppointmentData($appointment);

        $this->logAction($appointment, 'restored', null, $newValues);
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

    /**
     * Форматує дані запису з повною інформацією про зв'язки
     */
    private function formatAppointmentData(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'client_id' => $appointment->client_id,
            'client' => [
                'id' => $appointment->client?->id,
                'name' => $appointment->client?->name,
                'phone' => $appointment->client?->phone,
                'email' => $appointment->client?->email,
            ],
            'master_id' => $appointment->master_id,
            'master' => [
                'id' => $appointment->master?->id,
                'name' => $appointment->master?->name,
                'phone' => $appointment->master?->phone,
            ],
            'service_id' => $appointment->service_id,
            'service' => [
                'id' => $appointment->service?->id,
                'name' => $appointment->service?->name,
                'duration' => $appointment->service?->duration,
            ],
            'appointment_date' => $appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time,
            'duration' => $appointment->duration,
            'price' => $appointment->price,
            'status' => $appointment->status,
            'notes' => $appointment->notes,
            'is_confirmed' => $appointment->is_confirmed,
            'telegram_notification_sent' => $appointment->telegram_notification_sent,
            'created_at' => $appointment->created_at?->toIso8601String(),
            'updated_at' => $appointment->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Отримує старі значення з інформацією про зв'язки
     */
    private function getOldValuesWithRelations(Appointment $appointment): array
    {
        $original = $appointment->getOriginal();

        return [
            'id' => $original['id'] ?? $appointment->id,
            'client_id' => $original['client_id'] ?? $appointment->client_id,
            'client' => [
                'id' => $appointment->client?->id,
                'name' => $appointment->client?->name,
                'phone' => $appointment->client?->phone,
                'email' => $appointment->client?->email,
            ],
            'master_id' => $original['master_id'] ?? $appointment->master_id,
            'master' => [
                'id' => $appointment->master?->id,
                'name' => $appointment->master?->name,
                'phone' => $appointment->master?->phone,
            ],
            'service_id' => $original['service_id'] ?? $appointment->service_id,
            'service' => [
                'id' => $appointment->service?->id,
                'name' => $appointment->service?->name,
                'duration' => $appointment->service?->duration,
            ],
            'appointment_date' => $original['appointment_date'] ?? $appointment->appointment_date?->format('Y-m-d'),
            'appointment_time' => $original['appointment_time'] ?? $appointment->appointment_time,
            'duration' => $original['duration'] ?? $appointment->duration,
            'price' => $original['price'] ?? $appointment->price,
            'status' => $original['status'] ?? $appointment->status,
            'notes' => $original['notes'] ?? $appointment->notes,
            'is_confirmed' => $original['is_confirmed'] ?? $appointment->is_confirmed,
            'telegram_notification_sent' => $original['telegram_notification_sent'] ?? $appointment->telegram_notification_sent,
            'created_at' => $original['created_at'] ?? $appointment->created_at?->toIso8601String(),
            'updated_at' => $original['updated_at'] ?? $appointment->updated_at?->toIso8601String(),
        ];
    }
}
