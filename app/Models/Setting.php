<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key with caching
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            // Parse value based on type
            return match($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value
            };
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string', string $category = 'general', string $description = null, bool $isPublic = false)
    {
        // Clear cache
        Cache::forget("setting_{$key}");
        
        // Convert value to string for storage
        $stringValue = match($type) {
            'json' => json_encode($value),
            'boolean' => $value ? 'true' : 'false',
            default => (string) $value
        };
        
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'category' => $category,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );
    }

    /**
     * Get all public settings for frontend
     */
    public static function getPublicSettings()
    {
        return Cache::remember('public_settings', 3600, function () {
            return static::where('is_public', true)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => self::get($setting->key)];
                });
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }
        Cache::forget('public_settings');
    }

    /**
     * Get party size limits
     */
    public static function getPartySizeLimits()
    {
        return [
            'min' => self::get('party_size_min', 1),
            'max' => self::get('party_size_max', 50),
        ];
    }

    /**
     * Get queue settings
     */
    public static function getQueueSettings()
    {
        return [
            'avg_dining_duration' => self::get('avg_dining_duration', 60),
            'table_suggestion_time_window' => self::get('table_suggestion_time_window', 15),
            'grace_period_minutes' => self::get('grace_period_minutes', 5),
        ];
    }

    /**
     * Get table settings
     */
    public static function getTableSettings()
    {
        return [
            'max_table_capacity' => self::get('max_table_capacity', 12),
            'table_cleaning_duration' => self::get('table_cleaning_duration', 5),
        ];
    }

    /**
     * Boot method to clear cache when model is updated
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget('public_settings');
        });
        
        static::deleted(function ($setting) {
            Cache::forget("setting_{$setting->key}");
            Cache::forget('public_settings');
        });
    }
}