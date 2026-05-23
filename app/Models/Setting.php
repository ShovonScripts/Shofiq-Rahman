<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key with optional default.
     * Uses cache for faster performance.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("settings.{$key}", function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();

            return $row->value ?? $default;
        });
    }

    /**
     * Set or update a setting key/value.
     * Clears cache automatically.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("settings.{$key}");
        Cache::forget('settings.all');
    }

    /**
     * Get a setting as JSON-decoded array.
     */
    public static function getJson(string $key, $default = [])
    {
        $raw = static::get($key);
        if (! $raw) {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * Set a JSON-encoded value for a key.
     */
    public static function setJson(string $key, array $value): void
    {
        static::set($key, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Get all settings as a single cached array.
     * Reduces 7+ cache reads to 1 per request.
     */
    public static function allCached(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Forget a cached key or all settings cache.
     */
    public static function forget(?string $key = null): void
    {
        if ($key) {
            Cache::forget("settings.{$key}");
        } else {
            Cache::forget('settings.all');
            $keys = static::query()->pluck('key');
            foreach ($keys as $k) {
                Cache::forget("settings.{$k}");
            }
        }
    }
}
