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
     * Get store hours for a specific day
     * @param string $day monday, tuesday, wednesday, etc.
     */
    public static function getStoreHours(string $day): ?array
    {
        $day = strtolower($day);
        $openKey = "{$day}_open";
        $closeKey = "{$day}_close";
        
        $open = self::get($openKey);
        $close = self::get($closeKey);
        
        // If both are null or '00:00', store is closed
        if ($open === null && $close === null) {
            return null;
        }
        
        // Check for closed indicator (00:00 for both open and close)
        if ($open === '00:00' && $close === '00:00') {
            return null;
        }
        
        return [
            'open' => $open,
            'close' => $close,
            'is_closed' => ($open === null || $close === null || ($open === '00:00' && $close === '00:00')),
        ];
    }

    /**
     * Check if store is currently open
     */
    public static function isStoreOpen(): bool
    {
        $now = now();
        $day = strtolower($now->format('l')); // monday, tuesday, etc.
        $currentTime = $now->format('H:i');
        
        $hours = self::getStoreHours($day);
        
        // Closed all day
        if ($hours === null || $hours['is_closed']) {
            return false;
        }
        
        return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
    }

    /**
     * Get today's store hours
     */
    public static function getTodayHours(): ?array
    {
        $today = strtolower(now()->format('l'));
        return self::getStoreHours($today);
    }

    /**
     * Get all store hours for the week
     */
    public static function getWeeklyHours(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $schedule = [];
        
        foreach ($days as $day) {
            $schedule[$day] = self::getStoreHours($day);
        }
        
        return $schedule;
    }

    /**
     * Update store hours for a specific day
     */
    public static function updateStoreHours(string $day, ?string $openTime, ?string $closeTime): bool
    {
        try {
            $day = strtolower($day);
            $openKey = "{$day}_open";
            $closeKey = "{$day}_close";
            
            self::set(
                $openKey, 
                $openTime, 
                'time', 
                'hours', 
                ucfirst($day) . ' opening time'
            );
            
            self::set(
                $closeKey, 
                $closeTime, 
                'time', 
                'hours', 
                ucfirst($day) . ' closing time'
            );
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get restaurant name
     */
    public static function getRestaurantName(): string
    {
        return self::get('restaurant_name', 'Restaurant');
    }

    /**
     * Get restaurant information
     */
    public static function getRestaurantInfo(): array
    {
        return [
            'name' => self::getRestaurantName(),
            'address' => self::get('restaurant_address', ''),
            'phone' => self::get('restaurant_phone', ''),
        ];
    }

    /**
     * Check if registration should be blocked (store closed)
     */
    public static function shouldBlockRegistration(): bool
    {
        return !self::isStoreOpen();
    }

    /**
     * Get store status message for display
     */
    public static function getStoreStatusMessage(): string
    {
        if (self::isStoreOpen()) {
            return "We're currently open!";
        }
        
        // Get next open day
        $weeklyHours = self::getWeeklyHours();
        $today = strtolower(now()->format('l'));
        
        // Find next open day
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $currentDayIndex = array_search($today, $days);
        
        for ($i = 1; $i <= 7; $i++) {
            $nextDayIndex = ($currentDayIndex + $i) % 7;
            $nextDay = $days[$nextDayIndex];
            $nextDayHours = $weeklyHours[$nextDay];
            
            if ($nextDayHours && !$nextDayHours['is_closed']) {
                $nextOpen = now()->addDays($i)->copy()->setTimeFromTimeString($nextDayHours['open']);
                return "We're currently closed. Opens {$nextOpen->diffForHumans()}";
            }
        }
        
        return "We're currently closed. Please check back later.";
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