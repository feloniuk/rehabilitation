<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TextBlock extends Model
{
    protected $fillable = ['key', 'title', 'content', 'type', 'order'];

    /**
     * Отримати контент блоку за ключем
     */
    public static function get($key, $default = '')
    {
        return Cache::remember("text_block_{$key}", 3600, function () use ($key, $default) {
            $block = static::where('key', $key)->first();
            return $block ? $block->content : $default;
        });
    }

    /**
     * Зберегти або оновити блок
     */
    public static function set($key, $content, $title = null, $type = 'text')
    {
        $block = static::updateOrCreate(
            ['key' => $key],
            [
                'title' => $title ?? $key,
                'content' => $content,
                'type' => $type
            ]
        );
        
        Cache::forget("text_block_{$key}");
        return $block;
    }

    /**
     * Очистити кеш після збереження
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function ($block) {
            Cache::forget("text_block_{$block->key}");
        });
    }
}