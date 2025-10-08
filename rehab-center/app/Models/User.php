<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'description',
        'photo', 'work_schedule', 'is_active',
        'experience_years', 'clients_count', 'certificates_count'
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
        if (!$this->work_schedule) return [];

        return collect($this->work_schedule)
            ->filter(fn($day) => $day['is_working'] ?? false)
            ->keys()
            ->toArray();
    }

    public function isWorkingOnDay($dayName)
    {
        return $this->work_schedule[$dayName]['is_working'] ?? false;
    }

    public function getWorkingHours($dayName)
    {
        if (!$this->isWorkingOnDay($dayName)) return null;

        return [
            'start' => $this->work_schedule[$dayName]['start'] ?? '09:00',
            'end' => $this->work_schedule[$dayName]['end'] ?? '17:00'
        ];
    }
}