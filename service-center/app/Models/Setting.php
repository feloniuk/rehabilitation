<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['key', 'value', 'tenant_id'];

    /**
     * Get a setting value. Automatically scoped to current tenant.
     */
    public static function get($key, $default = null)
    {
        $tenant = app()->has('currentTenant') ? app('currentTenant') : null;
        $cacheKey = $tenant
            ? "tenant_{$tenant->id}_setting_{$key}"
            : "setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value. Automatically scoped to current tenant.
     */
    public static function set($key, $value)
    {
        $tenant = app()->has('currentTenant') ? app('currentTenant') : null;

        // Clear cache
        $cacheKey = $tenant
            ? "tenant_{$tenant->id}_setting_{$key}"
            : "setting_{$key}";
        Cache::forget($cacheKey);

        return static::updateOrCreate(
            ['key' => $key, 'tenant_id' => $tenant?->id],
            ['value' => $value]
        );
    }
}
