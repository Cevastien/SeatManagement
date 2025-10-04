<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Get all public settings for frontend
     */
    public function getPublicSettings(): JsonResponse
    {
        try {
            $settings = Setting::getPublicSettings();
            
            return response()->json([
                'success' => true,
                'settings' => $settings,
                'party_size_limits' => Setting::getPartySizeLimits(),
                'queue_settings' => Setting::getQueueSettings(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update settings (admin only)
     */
    public function updateSettings(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $request->validate([
                'party_size_min' => 'required|integer|min:1|max:100',
                'party_size_max' => 'required|integer|min:1|max:100',
                'avg_dining_duration' => 'required|integer|min:10|max:300',
                'table_suggestion_time_window' => 'required|integer|min:5|max:60',
                'restaurant_name' => 'required|string|max:255',
                'restaurant_address' => 'required|string|max:255',
                'restaurant_phone' => 'required|string|max:50',
            ]);

            if ($request->party_size_min >= $request->party_size_max) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum party size must be less than maximum party size'
                ], 400);
            }

            // Update settings
            Setting::set('party_size_min', $request->party_size_min, 'integer', 'queue', 'Minimum party size allowed for registration', true);
            Setting::set('party_size_max', $request->party_size_max, 'integer', 'queue', 'Maximum party size allowed for registration', true);
            Setting::set('avg_dining_duration', $request->avg_dining_duration, 'integer', 'queue', 'Average dining duration in minutes for wait time calculation');
            Setting::set('table_suggestion_time_window', $request->table_suggestion_time_window, 'integer', 'queue', 'Time window in minutes for table suggestions');
            Setting::set('restaurant_name', $request->restaurant_name, 'string', 'display', 'Restaurant name displayed on receipts and screens', true);
            Setting::set('restaurant_address', $request->restaurant_address, 'string', 'display', 'Restaurant address displayed on receipts', true);
            Setting::set('restaurant_phone', $request->restaurant_phone, 'string', 'display', 'Restaurant phone number displayed on receipts', true);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}