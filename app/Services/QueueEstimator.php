<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;

class QueueEstimator
{
    // Average time to serve one customer (minutes)
    const AVG_SERVICE_TIME = 5;
    
    // How many customers can be served simultaneously (number of tables/staff)
    const CONCURRENT_CAPACITY = 10;
    
    /**
     * Calculate smart wait time based on actual table availability
     */
    public function calculateWaitTime($partySize, $priorityType, $excludeCustomerId = null)
    {
        // Use smart table-based calculation if SmartQueueEstimator is available
        if (class_exists('\App\Services\SmartQueueEstimator')) {
            try {
                $smartEstimator = new \App\Services\SmartQueueEstimator();
                
                // Create a temporary customer instance for calculation
                $tempCustomer = new Customer([
                    'party_size' => $partySize,
                    'priority_type' => $priorityType,
                    'status' => 'waiting',
                    'created_at' => now(),
                ]);
                
                if ($excludeCustomerId) {
                    $tempCustomer->id = $excludeCustomerId;
                }
                
                $result = $smartEstimator->calculateWaitTime($tempCustomer);
                return $result['wait_minutes'];
            } catch (\Exception $e) {
                // Fall back to original logic if smart estimator fails
            }
        }
        
        // Original logic as fallback
        $customer = $excludeCustomerId ? Customer::find($excludeCustomerId) : null;
        
        // Get all customers waiting who registered BEFORE this customer
        $query = Customer::where('status', 'waiting');
        
        if ($customer) {
            // Count customers who registered before this customer
            $query->where('registered_at', '<', $customer->registered_at);
        } else {
            // For new customers, count all current waiting customers
            $query->where('id', '!=', $excludeCustomerId);
        }
        
        $customersAhead = $query->count();
        
        // If no one ahead, minimum wait time
        if ($customersAhead === 0) {
            return 5;
        }
        
        // Calculate wait time based on position
        $queueBatches = ceil($customersAhead / self::CONCURRENT_CAPACITY);
        $estimatedMinutes = $queueBatches * self::AVG_SERVICE_TIME;
        
        // Add party size factor (bigger groups take longer)
        if ($partySize >= 6) {
            $estimatedMinutes += 5; // Large groups need extra time
        } elseif ($partySize >= 4) {
            $estimatedMinutes += 3; // Medium groups need some extra time
        }
        
        // Apply priority advantage
        if (in_array($priorityType, ['senior', 'pwd', 'pregnant'])) {
            // Priority customers get 20% faster service
            $estimatedMinutes = ceil($estimatedMinutes * 0.8);
        }
        
        // Minimum and maximum wait times
        return max(5, min($estimatedMinutes, 120)); // 5 min to 2 hours max
    }
    
    /**
     * Format wait time for user-friendly display
     */
    public function formatWaitTime($minutes)
    {
        if ($minutes <= 5) {
            return '~5 minutes';
        } elseif ($minutes <= 15) {
            return '10-15 minutes';
        } elseif ($minutes <= 30) {
            return '20-30 minutes';
        } elseif ($minutes <= 45) {
            return '30-45 minutes';
        } elseif ($minutes <= 60) {
            return '45-60 minutes';
        } elseif ($minutes <= 90) {
            return '1-1.5 hours';
        } else {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
        }
    }
    
    /**
     * Get detailed queue statistics
     */
    public function getQueueStats()
    {
        $waitingCustomers = Customer::where('status', 'waiting')
            ->orderByRaw("CASE 
                WHEN priority_type IN ('senior', 'pwd', 'pregnant') THEN 1
                WHEN priority_type = 'normal' THEN 2
                ELSE 3 END")
            ->orderBy('registered_at', 'asc')
            ->get();
            
        $priorityCount = $waitingCustomers->whereIn('priority_type', ['senior', 'pwd', 'pregnant'])->count();
        $normalCount = $waitingCustomers->where('priority_type', 'normal')->count();
        $totalCount = $waitingCustomers->count();
        
        // Calculate average wait times
        $avgWaitPriority = $priorityCount > 0 ? 
            max(5, ceil($priorityCount / self::CONCURRENT_CAPACITY) * self::AVG_SERVICE_TIME * 0.8) : 5;
        $avgWaitNormal = $totalCount > 0 ? 
            max(5, ceil($totalCount / self::CONCURRENT_CAPACITY) * self::AVG_SERVICE_TIME) : 5;
            
        return [
            'total_waiting' => $totalCount,
            'priority_waiting' => $priorityCount,
            'normal_waiting' => $normalCount,
            'concurrent_capacity' => self::CONCURRENT_CAPACITY,
            'avg_service_time' => self::AVG_SERVICE_TIME,
            'estimated_priority_wait' => max(5, $avgWaitPriority),
            'estimated_normal_wait' => max(5, $avgWaitNormal),
            'queue_efficiency' => $totalCount > 0 ? round(($totalCount / self::CONCURRENT_CAPACITY) * 100, 1) : 0
        ];
    }
    
    /**
     * Update wait times for all waiting customers when queue changes
     */
    public function updateAllWaitTimes()
    {
        try {
            // Get all waiting customers ordered by registration time
            $waitingCustomers = Customer::where('status', 'waiting')
                ->orderBy('registered_at', 'asc')
                ->get();
            
            foreach ($waitingCustomers as $customer) {
                // Calculate wait time excluding this customer from the count
                $newWaitTime = $this->calculateWaitTime(
                    $customer->party_size,
                    $customer->priority_type,
                    $customer->id
                );
                
                $customer->update([
                    'estimated_wait_minutes' => $newWaitTime
                ]);
            }
            
            $stats = $this->getQueueStats();
            
            return [
                'success' => true,
                'updated_count' => $waitingCustomers->count(),
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to update wait times: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get queue position for a specific customer
     */
    public function getQueuePosition($customerId)
    {
        $customer = Customer::find($customerId);
        
        if (!$customer || $customer->status !== 'waiting') {
            return [
                'position' => null,
                'customers_ahead' => 0,
                'total_waiting' => 0,
                'estimated_wait_minutes' => 0,
                'formatted_wait_time' => 'Seated/Completed',
                'status' => $customer ? $customer->status : 'not_found'
            ];
        }
        
        // Count customers registered before this one who are still waiting
        $customersAhead = Customer::where('status', 'waiting')
            ->where('registered_at', '<', $customer->registered_at)
            ->count();
        
        $totalWaiting = Customer::where('status', 'waiting')->count();
        
        return [
            'position' => $customersAhead + 1,
            'customers_ahead' => $customersAhead,
            'total_waiting' => $totalWaiting,
            'estimated_wait_minutes' => $this->calculateWaitTime($customer->party_size, $customer->priority_type, $customer->id),
            'formatted_wait_time' => $this->formatWaitTime($this->calculateWaitTime($customer->party_size, $customer->priority_type, $customer->id)),
            'status' => 'waiting'
        ];
    }
    
    /**
     * Check if customer should be called based on queue position
     */
    public function shouldCallCustomer($customerId)
    {
        $position = $this->getQueuePosition($customerId);
        if (!$position) {
            return false;
        }
        
        // Call customer if they're in the top positions based on capacity
        return $position <= self::CONCURRENT_CAPACITY;
    }
    
    /**
     * Get next customers to call
     */
    public function getNextCustomersToCall()
    {
        return Customer::where('status', 'waiting')
            ->orderByRaw("CASE 
                WHEN priority_type IN ('senior', 'pwd', 'pregnant') THEN 1
                WHEN priority_type = 'normal' THEN 2
                ELSE 3 END")
            ->orderBy('registered_at', 'asc')
            ->limit(self::CONCURRENT_CAPACITY)
            ->get();
    }

    /**
     * Calculate wait time for a new customer (before they're added to database)
     * This gives an estimate based on current queue, not exact position
     */
    public function calculateWaitTimeForNew($partySize, $priorityType)
    {
        // Count all current waiting customers
        $waitingCustomers = Customer::where('status', 'waiting')->count();
        
        // New customer will be at the end (count + 1 position)
        $customersAhead = $waitingCustomers;
        
        // If no one ahead, minimum wait time
        if ($customersAhead === 0) {
            return 5;
        }
        
        // Calculate wait time based on position
        $queueBatches = ceil($customersAhead / self::CONCURRENT_CAPACITY);
        $estimatedMinutes = $queueBatches * self::AVG_SERVICE_TIME;
        
        // Add party size factor (bigger groups take longer)
        if ($partySize >= 6) {
            $estimatedMinutes += 5; // Large groups need extra time
        } elseif ($partySize >= 4) {
            $estimatedMinutes += 3; // Medium groups need some extra time
        }
        
        // Apply priority advantage
        if (in_array($priorityType, ['senior', 'pwd', 'pregnant'])) {
            // Priority customers get 20% faster service
            $estimatedMinutes = ceil($estimatedMinutes * 0.8);
        }
        
        // Minimum and maximum wait times
        return max(5, min($estimatedMinutes, 120)); // 5 min to 2 hours max
    }

    /**
     * Calculate position-based wait time for existing customers
     */
    public function calculatePositionBasedWaitTime($customersAhead, $priorityType)
    {
        // If no one ahead, minimum wait time
        if ($customersAhead === 0) {
            return 5;
        }

        // Calculate wait time based on queue position
        $queueBatches = ceil($customersAhead / self::CONCURRENT_CAPACITY);
        $estimatedMinutes = $queueBatches * self::AVG_SERVICE_TIME;

        // Apply priority advantage
        if (in_array($priorityType, ['senior', 'pwd', 'pregnant'])) {
            // Priority customers get 20% faster service
            $estimatedMinutes = ceil($estimatedMinutes * 0.8);
        }

        // Minimum and maximum wait times
        return max(5, min($estimatedMinutes, 120)); // 5 min to 2 hours max
    }
}
