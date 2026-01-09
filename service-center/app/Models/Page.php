<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['slug', 'title', 'content', 'is_active', 'tenant_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function findBySlug($slug)
    {
        // GlobalScope будет применен автоматически через BelongsToTenant trait
        return static::where('slug', $slug)->where('is_active', true)->firstOrFail();
    }
}
