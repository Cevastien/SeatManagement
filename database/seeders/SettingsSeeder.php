<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Party Size Settings
        Setting::set(
            'party_size_min',
            1,
            'integer',
            'queue',
            'Minimum party size allowed for registration',
            true // Public - can be accessed from frontend
        );

        Setting::set(
            'party_size_max',
            50,
            'integer',
            'queue',
            'Maximum party size allowed for registration',
            true // Public - can be accessed from frontend
        );

        // Queue Settings
        Setting::set(
            'avg_dining_duration',
            60,
            'integer',
            'queue',
            'Average dining duration in minutes for wait time calculation'
        );

        Setting::set(
            'table_suggestion_time_window',
            15,
            'integer',
            'queue',
            'Time window in minutes for table suggestions'
        );

        Setting::set(
            'grace_period_minutes',
            5,
            'integer',
            'queue',
            'Grace period in minutes after customer is called',
            true
        );

        // Table Settings
        Setting::set(
            'max_table_capacity',
            12,
            'integer',
            'table',
            'Maximum capacity for any single table'
        );

        Setting::set(
            'table_cleaning_duration',
            5,
            'integer',
            'table',
            'Duration in minutes for table cleaning'
        );

        // System Settings
        Setting::set(
            'verification_timeout_minutes',
            5,
            'integer',
            'verification',
            'Timeout in minutes for priority verification'
        );

        Setting::set(
            'session_timeout_minutes',
            30,
            'integer',
            'system',
            'Session timeout in minutes for kiosk'
        );

        // Display Settings
        Setting::set(
            'restaurant_name',
            'GERVACIOS RESTAURANT & LOUNGE',
            'string',
            'display',
            'Restaurant name displayed on receipts and screens',
            true
        );

        Setting::set(
            'restaurant_address',
            '123 Coffee Street, Davao City',
            'string',
            'display',
            'Restaurant address displayed on receipts',
            true
        );

        Setting::set(
            'restaurant_phone',
            '(02) 8123-4567',
            'string',
            'display',
            'Restaurant phone number displayed on receipts',
            true
        );

        // Notification Settings
        Setting::set(
            'enable_sms_notifications',
            true,
            'boolean',
            'notification',
            'Enable SMS notifications for customers'
        );

        Setting::set(
            'sms_provider',
            'twilio',
            'string',
            'notification',
            'SMS service provider'
        );

        $this->command->info('Settings seeded successfully!');
    }
}