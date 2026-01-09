<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TextBlock extends Model
{
    use BelongsToTenant;

    protected $fillable = ['key', 'title', 'content', 'type', 'order', 'tenant_id'];

    /**
     * Get block content by key. Automatically scoped to current tenant.
     */
    public static function get($key, $default = '')
    {
        $tenant = app()->has('currentTenant') ? app('currentTenant') : null;
        $cacheKey = $tenant
            ? "tenant_{$tenant->id}_text_block_{$key}"
            : "text_block_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $block = static::where('key', $key)->first();

            return $block ? $block->content : $default;
        });
    }

    /**
     * Save or update a block. Automatically scoped to current tenant.
     */
    public static function set($key, $content, $title = null, $type = 'text')
    {
        $tenant = app()->has('currentTenant') ? app('currentTenant') : null;

        $block = static::updateOrCreate(
            ['key' => $key, 'tenant_id' => $tenant?->id],
            [
                'title' => $title ?? $key,
                'content' => $content,
                'type' => $type,
            ]
        );

        // Clear cache
        $cacheKey = $tenant
            ? "tenant_{$tenant->id}_text_block_{$key}"
            : "text_block_{$key}";
        Cache::forget($cacheKey);

        return $block;
    }

    /**
     * Clear cache after saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($block) {
            $tenant = app()->has('currentTenant') ? app('currentTenant') : null;
            $cacheKey = $tenant
                ? "tenant_{$tenant->id}_text_block_{$block->key}"
                : "text_block_{$block->key}";
            Cache::forget($cacheKey);
        });
    }
}
