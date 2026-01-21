<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentAuditLog extends Model
{
    protected $fillable = [
        'appointment_id',
        'action',
        'user_id',
        'user_type',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Створено',
            'updated' => 'Оновлено',
            'deleted' => 'Видалено',
            'restored' => 'Відновлено',
            default => $this->action,
        };
    }

    public function getUserTypeLabelAttribute(): string
    {
        return match ($this->user_type) {
            'admin' => 'Адміністратор',
            'master' => 'Майстер',
            'client' => 'Клієнт',
            'system' => 'Система',
            default => $this->user_type ?? 'Невідомо',
        };
    }
}
