<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, BelongsToTenant;

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