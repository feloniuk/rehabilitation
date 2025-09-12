<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterService extends Model
{
    use HasFactory;

    protected $fillable = ['master_id', 'service_id', 'price', 'duration'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getDuration()
    {
        return $this->duration ?? $this->service->duration;
    }
}