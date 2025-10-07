<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Table;
use Carbon\Carbon;

class SmartQueueEstimator
{
    // Average dining times by party size (in minutes) - More realistic for cafe
    private const AVERAGE_DINING_TIMES = [
        1 => 15,  // Solo diners
        2 => 20,  // Couples
        3 => 25,  // Small groups
        4 => 25,  // Standard groups
        5 => 30,  // Large groups
        6 => 30,  // Large groups
        8 => 35,  // VIP table
    ];

    // Priority customer buffer (20% longer wait for regular customers)
    private const PRIORITY_BUFFER = 0.2;

    /**
     * Calculate smart wait time based on actual table availability
     */
    public function calculateWaitTime(Customer $customer): array
    {
        $partySize = $customer->party_size;
        $priorityType = $customer->priority_type;
        
        // Step 1: Find suitable tables for this party size
        $suitableTables = $this->getSuitableTables($partySize);
        
        // Step 2: Get current queue position for this party size
        $queuePosition = $this->getQueuePosition($customer, $partySize);
        
        // Step 3: Calculate actual wait time based on table availability
        $waitTime = $this->calculateActualWaitTime($suitableTables, $queuePosition, $partySize);
        
        // Step 4: Apply priority adjustments
        if ($priorityType === 'regular' || $priorityType === 'normal') {
            $waitTime = $waitTime * (1 + self::PRIORITY_BUFFER);
        }
        
        // Step 5: Apply maximum wait time limit and round to reasonable increments
        $waitTime = min($waitTime, 60); // Maximum 1 hour wait time
        $waitTime = ceil($waitTime / 5) * 5; // Round to 5-minute intervals
        
        // Step 6: If wait time is still too high, use fallback to simple estimator
        if ($waitTime > 45) {
            $simpleEstimator = new \App\Services\QueueEstimator();
            $simpleWaitTime = $simpleEstimator->calculateWaitTimeForNew($partySize, $priorityType);
            $waitTime = min($waitTime, $simpleWaitTime);
        }
        
        // Step 7: Business logic - if wait time exceeds 1 hour, consider cafe full
        if ($waitTime > 60) {
            $waitTime = 60; // Cap at 1 hour maximum
        }
        
        return [
            'wait_minutes' => (int) $waitTime,
            'wait_formatted' => $this->formatWaitTime($waitTime),
            'customers_ahead' => max(0, $queuePosition - 1),
            'estimated_table_time' => $this->getEstimatedTableTime($suitableTables),
            'last_updated' => Carbon::now()->format('g:i A'),
        ];
    }

    /**
     * Get tables suitable for the party size (simplified version)
     */
    private function getSuitableTables(int $partySize): array
    {
        // For now, simulate table availability based on party size
        // This can be enhanced later when proper table tracking is implemented
        
        $tableCounts = [
            1 => 8,  // Solo tables
            2 => 8,  // Solo tables can fit 2 people
            3 => 6,  // Standard tables
            4 => 6,  // Standard tables
            5 => 4,  // Large tables
            6 => 4,  // Large tables
            7 => 1,  // VIP table
            8 => 1,  // VIP table
        ];
        
        $minCapacity = $partySize > 6 ? 8 : ($partySize > 4 ? 6 : ($partySize > 2 ? 4 : 2));
        
        // Simulate some tables being occupied (about 50% occupancy - more realistic)
        $totalTables = $tableCounts[$minCapacity] ?? 4;
        $occupiedTables = max(0, floor($totalTables * 0.5));
        $availableTables = $totalTables - $occupiedTables;
        
        // Create mock table data
        $tables = [];
        for ($i = 1; $i <= $totalTables; $i++) {
            $tables[] = [
                'id' => $i,
                'table_number' => "T{$i}",
                'max_capacity' => $minCapacity,
                'status' => $i <= $occupiedTables ? 'occupied' : 'available',
                'occupied_at' => $i <= $occupiedTables ? now()->subMinutes(rand(5, 40))->format('Y-m-d H:i:s') : null,
                'estimated_departure' => $i <= $occupiedTables ? now()->addMinutes(rand(5, 30))->format('Y-m-d H:i:s') : null,
            ];
        }
        
        return $tables;
    }

    /**
     * Get queue position for customers with same party size requirements
     */
    private function getQueuePosition(Customer $customer, int $partySize): int
    {
        $minCapacity = $partySize > 6 ? 8 : $partySize;
        
        // For temporary customers (testing), return a realistic position
        if (!$customer->id) {
            $currentWaiting = Customer::where('status', 'waiting')->count();
            return $currentWaiting + 1; // Next in line
        }
        
        return Customer::where('status', 'waiting')
            ->where('party_size', '<=', $minCapacity)
            ->where('created_at', '<', $customer->created_at)
            ->count() + 1;
    }

    /**
     * Calculate actual wait time based on table availability and real activity
     */
    private function calculateActualWaitTime(array $tables, int $queuePosition, int $partySize): int
    {
        // Step 1: Check if any suitable tables are available now
        $availableTables = array_filter($tables, function($table) {
            return $table['status'] === 'available';
        });
        
        if (count($availableTables) > 0 && $queuePosition === 1) {
            return 0; // Immediate seating available
        }
        
        // Step 2: Get real table turnover data from database
        $recentCompletions = \App\Models\Customer::where('status', 'completed')
            ->where('completed_at', '>=', now()->subMinutes(60))
            ->count();
        
        $recentSeatings = \App\Models\Customer::where('status', 'seated')
            ->where('seated_at', '>=', now()->subMinutes(60))
            ->count();
        
        // Step 3: Calculate realistic table turnover rate
        $actualTurnoverRate = $recentCompletions > 0 ? $recentCompletions / 60 : 0.5; // completions per minute
        $averageDiningTime = self::AVERAGE_DINING_TIMES[$partySize] ?? 25;
        
        // Step 4: Calculate wait time based on real activity
        if ($actualTurnoverRate > 0) {
            // Use real turnover data
            $estimatedWaitTime = ($queuePosition - 1) / $actualTurnoverRate;
        } else {
            // Fallback to table-based calculation
            $tableFreeTimes = [];
            $occupiedTables = array_filter($tables, function($table) {
                return $table['status'] === 'occupied';
            });
            
            foreach ($occupiedTables as $table) {
                if ($table['occupied_at']) {
                    $occupiedAt = Carbon::parse($table['occupied_at']);
                    $tableDiningTime = self::AVERAGE_DINING_TIMES[$table['max_capacity']] ?? $averageDiningTime;
                    $estimatedDeparture = $occupiedAt->addMinutes($tableDiningTime);
                    
                    $minutesUntilFree = max(0, $estimatedDeparture->diffInMinutes(Carbon::now()));
                    $tableFreeTimes[] = $minutesUntilFree;
                }
            }
            
            sort($tableFreeTimes);
            
            if (count($tableFreeTimes) >= $queuePosition) {
                $estimatedWaitTime = $tableFreeTimes[$queuePosition - 1];
            } else {
                $tablesNeeded = $queuePosition - count($tableFreeTimes);
                $additionalWait = $tablesNeeded * $averageDiningTime;
                $estimatedWaitTime = (count($tableFreeTimes) > 0 ? $tableFreeTimes[count($tableFreeTimes) - 1] : 0) + $additionalWait;
            }
        }
        
        return (int) $estimatedWaitTime;
    }

    /**
     * Get estimated time when a table will be available
     */
    private function getEstimatedTableTime(array $tables): ?string
    {
        $occupiedTables = array_filter($tables, function($table) {
            return $table['status'] === 'occupied' && $table['occupied_at'];
        });
        
        if (empty($occupiedTables)) {
            return null;
        }
        
        $earliestFree = null;
        foreach ($occupiedTables as $table) {
            $occupiedAt = Carbon::parse($table['occupied_at']);
            $averageDiningTime = self::AVERAGE_DINING_TIMES[$table['max_capacity']] ?? 45;
            $estimatedDeparture = $occupiedAt->addMinutes($averageDiningTime);
            
            if (!$earliestFree || $estimatedDeparture->lt($earliestFree)) {
                $earliestFree = $estimatedDeparture;
            }
        }
        
        return $earliestFree ? $earliestFree->format('g:i A') : null;
    }

    /**
     * Format wait time for display
     */
    private function formatWaitTime(int $minutes): string
    {
        if ($minutes === 0) {
            return 'Available now';
        } elseif ($minutes < 60) {
            return "{$minutes} minutes";
        } else {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            
            if ($remainingMinutes === 0) {
                return $hours === 1 ? '1 hour' : "{$hours} hours";
            } else {
                $hourText = $hours === 1 ? '1 hour' : "{$hours} hours";
                return "{$hourText} {$remainingMinutes} minutes";
            }
        }
    }

    /**
     * Check if cafe is too busy to accept new customers
     */
    public function isCafeTooBusy(int $partySize = 2): bool
    {
        $testCustomer = new Customer([
            'party_size' => $partySize,
            'priority_type' => 'normal',
            'status' => 'waiting',
            'created_at' => now(),
        ]);
        
        $result = $this->calculateWaitTime($testCustomer);
        return $result['wait_minutes'] > 60; // Too busy if wait > 1 hour
    }
    
    /**
     * Get queue statistics for display (simplified version)
     */
    public function getQueueStats(): array
    {
        $stats = [];
        
        // Simulate table stats for each capacity
        $tableCapacities = [2, 4, 6, 8];
        $tableCounts = [2 => 8, 4 => 6, 6 => 4, 8 => 1]; // Solo, Standard, Large, VIP
        
        foreach ($tableCapacities as $capacity) {
            $totalTables = $tableCounts[$capacity];
            $occupiedTables = max(1, floor($totalTables * 0.3)); // 30% occupancy
            $waitingCustomers = Customer::where('status', 'waiting')
                ->where('party_size', '<=', $capacity)
                ->count();
            
            $stats[$capacity] = [
                'total_tables' => $totalTables,
                'occupied_tables' => $occupiedTables,
                'available_tables' => $totalTables - $occupiedTables,
                'waiting_customers' => $waitingCustomers,
            ];
        }
        
        return $stats;
    }
}
