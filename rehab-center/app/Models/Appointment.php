<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'master_id', 'service_id', 'appointment_date',
        'appointment_time', 'duration', 'price', 'status', 'notes', 'telegram_notification_sent',
        'is_confirmed',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'price' => 'decimal:2',
        'telegram_notification_sent' => 'boolean',
        'is_confirmed' => 'boolean',
        'duration' => 'integer', // ВАЖНО: приводим к integer
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getStartDateTime()
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d').' '.$this->appointment_time);
    }

    public function getEndDateTime()
    {
        // ИСПРАВЛЕНИЕ: явно приводим duration к integer
        return $this->getStartDateTime()->addMinutes((int) $this->duration);
    }

    public function isUpcoming()
    {
        return $this->getStartDateTime()->isFuture();
    }

    public function canBeCancelled()
    {
        return $this->status === 'scheduled' && $this->getStartDateTime()->diffInHours(now()) > 24;
    }
}
