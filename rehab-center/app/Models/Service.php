<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'duration', 'photo', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
    ];

    public function masters()
    {
        return $this->belongsToMany(User::class, 'master_services', 'service_id', 'master_id')
                    ->withPivot('price', 'duration');
    }

    public function faqs()
    {
        return $this->hasMany(ServiceFaq::class)->orderBy('order');
    }

    public function masterServices()
    {
        return $this->hasMany(MasterService::class);
    }

    /**
     * Отримати masterServices тільки від активних мастерів
     */
    public function activeMasterServices()
    {
        return $this->hasMany(MasterService::class)
            ->whereHas('master', function($query) {
                $query->where('role', 'master')
                      ->where('is_active', true);
            });
    }

    /**
     * Отримати діапазон цін від активних мастерів
     */
    public function getActivePriceRangeAttribute(): array
    {
        $prices = MasterService::where('service_id', $this->id)
            ->join('users', 'master_services.master_id', '=', 'users.id')
            ->where('users.role', 'master')
            ->where('users.is_active', true)
            ->pluck('master_services.price');

        if ($prices->isEmpty()) {
            return ['min' => null, 'max' => null];
        }

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    
    /**
     * Отримати URL фото
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
    }
}