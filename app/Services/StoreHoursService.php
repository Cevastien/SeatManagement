<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreHoursService
{
    /**
     * Get store hours for a specific day
     */
    public function getStoreHours(string $day = null): array
    {
        $day = $day ?: strtolower(now()->format('l')); // monday, tuesday, etc.
        
        $openKey = "{$day}_open";
        $closeKey = "{$day}_close";
        
        $settings = DB::table('settings')
            ->whereIn('key', [$openKey, $closeKey])
            ->where('category', 'hours')
            ->pluck('value', 'key')
            ->toArray();
            
        return [
            'day' => ucfirst($day),
            'open' => $settings[$openKey] ?? null,
            'close' => $settings[$closeKey] ?? null,
            'is_closed' => is_null($settings[$openKey]) || is_null($settings[$closeKey]) || 
                           ($settings[$openKey] === '00:00' && $settings[$closeKey] === '00:00')
        ];
    }
    
    /**
     * Check if store is currently open
     */
    public function isStoreOpen(): bool
    {
        $hours = $this->getStoreHours();
        
        if ($hours['is_closed']) {
            return false;
        }
        
        $now = now();
        $openTime = Carbon::createFromFormat('H:i', $hours['open']);
        $closeTime = Carbon::createFromFormat('H:i', $hours['close']);
        
        // Handle overnight hours (e.g., open 22:00, close 02:00)
        if ($closeTime->lessThan($openTime)) {
            return $now->format('H:i') >= $hours['open'] || $now->format('H:i') <= $hours['close'];
        }
        
        return $now->format('H:i') >= $hours['open'] && $now->format('H:i') <= $hours['close'];
    }
    
    /**
     * Get time until store opens (if closed)
     */
    public function getTimeUntilOpen(): ?string
    {
        if ($this->isStoreOpen()) {
            return null; // Store is already open
        }
        
        $todayHours = $this->getStoreHours();
        
        // If today is closed, find next open day
        if ($todayHours['is_closed']) {
            for ($i = 1; $i <= 7; $i++) {
                $nextDay = now()->addDays($i);
                $dayName = strtolower($nextDay->format('l'));
                $nextDayHours = $this->getStoreHours($dayName);
                
                if (!$nextDayHours['is_closed']) {
                    $nextOpen = $nextDay->copy()->setTimeFromTimeString($nextDayHours['open']);
                    return $nextOpen->diffForHumans();
                }
            }
            return null; // Store is closed indefinitely
        }
        
        // Store opens later today
        $todayOpen = now()->copy()->setTimeFromTimeString($todayHours['open']);
        
        // If opening time has passed today, it means we're past closing time
        if ($todayOpen->isPast()) {
            return null;
        }
        
        return $todayOpen->diffForHumans();
    }
    
    /**
     * Get time until store closes (if open)
     */
    public function getTimeUntilClose(): ?string
    {
        if (!$this->isStoreOpen()) {
            return null; // Store is closed
        }
        
        $hours = $this->getStoreHours();
        $todayClose = now()->copy()->setTimeFromTimeString($hours['close']);
        
        // Handle overnight hours
        $openTime = Carbon::createFromFormat('H:i', $hours['open']);
        $closeTime = Carbon::createFromFormat('H:i', $hours['close']);
        
        if ($closeTime->lessThan($openTime)) {
            // Overnight hours - close time is tomorrow
            $todayClose->addDay();
        }
        
        return $todayClose->diffForHumans();
    }
    
    /**
     * Get store status message for display
     */
    public function getStoreStatusMessage(): string
    {
        if ($this->isStoreOpen()) {
            $timeUntilClose = $this->getTimeUntilClose();
            return "We're currently open! Closes in {$timeUntilClose}";
        }
        
        $timeUntilOpen = $this->getTimeUntilOpen();
        if ($timeUntilOpen) {
            return "We're currently closed. Opens {$timeUntilOpen}";
        }
        
        return "We're currently closed. Please check back later.";
    }
    
    /**
     * Get all store hours for the week
     */
    public function getAllStoreHours(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];
        
        foreach ($days as $day) {
            $hours[$day] = $this->getStoreHours($day);
        }
        
        return $hours;
    }
    
    /**
     * Update store hours for a specific day
     */
    public function updateStoreHours(string $day, ?string $openTime, ?string $closeTime): bool
    {
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => "{$day}_open"],
                [
                    'value' => $openTime,
                    'type' => 'time',
                    'category' => 'hours',
                    'description' => ucfirst($day) . ' opening time',
                    'is_public' => true,
                    'updated_at' => now()
                ]
            );
            
            DB::table('settings')->updateOrInsert(
                ['key' => "{$day}_close"],
                [
                    'value' => $closeTime,
                    'type' => 'time',
                    'category' => 'hours',
                    'description' => ucfirst($day) . ' closing time',
                    'is_public' => true,
                    'updated_at' => now()
                ]
            );
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get restaurant name
     */
    public function getRestaurantName(): string
    {
        return DB::table('settings')
            ->where('key', 'restaurant_name')
            ->where('category', 'general')
            ->value('value') ?: 'Restaurant';
    }
    
    /**
     * Check if registration should be blocked (store closed)
     */
    public function shouldBlockRegistration(): bool
    {
        return !$this->isStoreOpen();
    }
    
    /**
     * Get store hours display for kiosk
     */
    public function getStoreHoursDisplay(): array
    {
        $hours = $this->getAllStoreHours();
        $display = [];
        
        foreach ($hours as $day => $dayHours) {
            if ($dayHours['is_closed']) {
                $display[] = ucfirst($day) . ': Closed';
            } else {
                $display[] = ucfirst($day) . ': ' . $dayHours['open'] . ' - ' . $dayHours['close'];
            }
        }
        
        return $display;
    }
}
