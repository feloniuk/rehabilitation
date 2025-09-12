<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'duration', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function masters()
    {
        return $this->belongsToMany(User::class, 'master_services', 'service_id', 'master_id')
                    ->withPivot('price', 'duration');
    }

    public function masterServices()
    {
        return $this->hasMany(MasterService::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}