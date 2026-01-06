<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ServiceFaq extends Model
{
    use BelongsToTenant;

    protected $fillable = ['service_id', 'question', 'answer', 'order', 'tenant_id'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}