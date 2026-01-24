<?php

namespace App\Models;

use App\Helpers\PhoneHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'telegram_username', 'telegram_chat_id', 'role', 'description',
        'photo', 'work_schedule', 'is_active', 'rating',
        'experience_years', 'clients_count', 'certificates_count', 'specialty',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'work_schedule' => 'array',
        'is_active' => 'boolean',
        'experience_years' => 'integer',
        'clients_count' => 'integer',
        'certificates_count' => 'integer',
    ];

    /**
     * Нормалізує телефон при збереженні
     */
    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = PhoneHelper::normalize($value);
    }

    /**
     * Знаходить користувача за телефоном (з нормалізацією)
     */
    public static function findByPhone(string $phone): ?self
    {
        return self::where('phone', PhoneHelper::normalize($phone))->first();
    }

    // Relationship methods
    public function masterServices()
    {
        return $this->hasMany(MasterService::class, 'master_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'master_services', 'master_id', 'service_id')
            ->withPivot('price', 'duration');
    }

    public function clientAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id');
    }

    public function masterAppointments()
    {
        return $this->hasMany(Appointment::class, 'master_id');
    }

    public function blockedPeriods()
    {
        return $this->hasMany(MasterBlockedPeriod::class, 'master_id');
    }

    public function activeBlockedPeriods()
    {
        return $this->hasMany(MasterBlockedPeriod::class, 'master_id')
            ->where('end_date', '>=', now()->toDateString());
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isMaster()
    {
        return $this->role === 'master';
    }

    public function isClient()
    {
        return $this->role === 'client';
    }

    public function getWorkingDays()
    {
        if (! $this->work_schedule) {
            return [];
        }

        return collect($this->work_schedule)
            ->filter(fn ($day) => $day['is_working'] ?? false)
            ->keys()
            ->toArray();
    }

    public function isWorkingOnDay($dayName)
    {
        return $this->work_schedule[$dayName]['is_working'] ?? false;
    }

    public function getWorkingHours($dayName)
    {
        if (! $this->isWorkingOnDay($dayName)) {
            return null;
        }

        return [
            'start' => $this->work_schedule[$dayName]['start'] ?? '09:00',
            'end' => $this->work_schedule[$dayName]['end'] ?? '17:00',
        ];
    }

    public function isBlockedOn(\Carbon\Carbon $date): bool
    {
        return $this->blockedPeriods()
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->exists();
    }

    public function getBlockedPeriodOn(\Carbon\Carbon $date): ?MasterBlockedPeriod
    {
        return $this->blockedPeriods()
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->first();
    }
}
