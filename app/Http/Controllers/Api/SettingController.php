<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StoreHoursService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    protected StoreHoursService $storeHoursService;

    public function __construct(StoreHoursService $storeHoursService)
    {
        $this->storeHoursService = $storeHoursService;
    }

    /**
     * Check if store is currently open
     */
    public function isStoreOpen(): JsonResponse
    {
        $isOpen = $this->storeHoursService->isStoreOpen();
        $statusMessage = $this->storeHoursService->getStoreStatusMessage();
        
        return response()->json([
            'success' => true,
            'is_open' => $isOpen,
            'status_message' => $statusMessage,
            'time_until_open' => $this->storeHoursService->getTimeUntilOpen(),
            'time_until_close' => $this->storeHoursService->getTimeUntilClose(),
        ]);
    }

    /**
     * Get store hours for all days of the week
     */
    public function getStoreHours(): JsonResponse
    {
        $hours = $this->storeHoursService->getAllStoreHours();
        
        return response()->json([
            'success' => true,
            'hours' => $hours,
        ]);
    }

    /**
     * Get today's store hours
     */
    public function getTodayHours(): JsonResponse
    {
        $today = now()->format('l'); // monday, tuesday, etc.
        $hours = $this->storeHoursService->getStoreHours($today);
        
        return response()->json([
            'success' => true,
            'day' => ucfirst($today),
            'hours' => $hours,
        ]);
    }

    /**
     * Get public settings for frontend
     */
    public function getPublicSettings(): JsonResponse
    {
        $settings = [
            'restaurant_name' => $this->storeHoursService->getRestaurantName(),
            'is_open' => $this->storeHoursService->isStoreOpen(),
            'status_message' => $this->storeHoursService->getStoreStatusMessage(),
            'today_hours' => $this->storeHoursService->getStoreHours(now()->format('l')),
            'weekly_hours' => $this->storeHoursService->getAllStoreHours(),
        ];

        // Add store hours for each day
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            $dayHours = $this->storeHoursService->getStoreHours($day);
            $settings["{$day}_open"] = $dayHours['open'] ?? null;
            $settings["{$day}_close"] = $dayHours['close'] ?? null;
        }

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    /**
     * Check if registration should be blocked
     */
    public function shouldBlockRegistration(): JsonResponse
    {
        $shouldBlock = $this->storeHoursService->shouldBlockRegistration();
        
        return response()->json([
            'success' => true,
            'block_registration' => $shouldBlock,
            'reason' => $shouldBlock ? 'Store is currently closed' : null,
        ]);
    }
}
