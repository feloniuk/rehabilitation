<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'content', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function findBySlug($slug)
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }
}