<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match ($setting->setting_type) {
            'boolean' => filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->setting_value,
            'json' => json_decode($setting->setting_value, true),
            default => $setting->setting_value
        };
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value, string $type = 'string', string $description = null, bool $isPublic = false)
    {
        $processedValue = match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value
        };

        return static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $processedValue,
                'setting_type' => $type,
                'description' => $description,
                'is_public' => $isPublic
            ]
        );
    }

    /**
     * Get all public settings
     */
    public static function getPublicSettings()
    {
        return static::where('is_public', true)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->setting_key => static::getValue($setting->setting_key)];
            });
    }
}