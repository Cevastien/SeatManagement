<?php

namespace App\Services;

use App\Models\Table;
use App\Models\Customer;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TableService
{
    /**
     * Get tables that will be available soon for a specific party size
     */
    public function getAvailableSoonTables(int $partySize, int $timeWindowMinutes = 15): Collection
    {
        Log::info("Finding tables available soon for party size: {$partySize}, time window: {$timeWindowMinutes} minutes");

        // Update expected free times for occupied tables first
        $this->updateExpectedFreeTimes();

        $availableTables = Table::canAccommodate($partySize)
            ->availableSoon($timeWindowMinutes)
            ->orderBy('expected_free_at', 'asc')
            ->orderBy('capacity', 'asc') // Prefer smaller tables that still fit
            ->get();

        // Filter out tables that are already reserved by other customers
        $availableTables = $availableTables->filter(function ($table) {
            if ($table->status === 'reserved' && $table->reserved_by_customer_id) {
                // Check if the reserved customer is still waiting
                $reservedCustomer = $table->reservedByCustomer;
                return !$reservedCustomer || $reservedCustomer->status !== 'waiting';
            }
            return true;
        });

        Log::info("Found {$availableTables->count()} tables available soon for party size {$partySize}");
        
        return $availableTables;
    }

    /**
     * Update expected free times for all occupied tables
     */
    public function updateExpectedFreeTimes(): void
    {
        $occupiedTables = Table::where('status', 'occupied')
            ->whereNull('expected_free_at')
            ->with('currentCustomer')
            ->get();

        foreach ($occupiedTables as $table) {
            $expectedFreeAt = $table->calculateExpectedFreeTime();
            if ($expectedFreeAt) {
                $table->update(['expected_free_at' => $expectedFreeAt]);
                Log::info("Updated expected free time for table {$table->name}: {$expectedFreeAt}");
            }
        }
    }

    /**
     * Reserve a table for a customer
     */
    public function reserveTable(Table $table, Customer $customer): bool
    {
        try {
            // Check if table is still available for reservation
            if (!$this->canReserveTable($table, $customer->party_size)) {
                Log::warning("Cannot reserve table {$table->name} - no longer available");
                return false;
            }

            $expectedFreeAt = $this->getExpectedFreeAtForTable($table);
            
            $table->markAsReserved($customer, $expectedFreeAt);
            
            // Update customer record
            $customer->update([
                'table_id' => $table->id,
                'is_table_requested' => true,
            ]);

            Log::info("Table {$table->name} reserved for customer {$customer->name} (ID: {$customer->id})");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to reserve table {$table->name} for customer {$customer->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a table can be reserved
     */
    public function canReserveTable(Table $table, int $partySize): bool
    {
        // Check capacity
        if ($table->capacity < $partySize) {
            return false;
        }

        // Check if table is available or will be available soon
        if ($table->status === 'vacant') {
            return true;
        }

        if ($table->status === 'occupied' && $table->expected_free_at) {
            $minutesUntilFree = now()->diffInMinutes($table->expected_free_at, false);
            return $minutesUntilFree >= 0 && $minutesUntilFree <= 15; // 15 minute window
        }

        return false;
    }

    /**
     * Get expected free time for a table
     */
    public function getExpectedFreeAtForTable(Table $table): ?Carbon
    {
        if ($table->status === 'vacant') {
            return now();
        }

        if ($table->status === 'occupied') {
            return $table->expected_free_at ?: $table->calculateExpectedFreeTime();
        }

        return null;
    }

    /**
     * Assign a table to a customer (when they are seated)
     */
    public function assignTable(Table $table, Customer $customer): bool
    {
        try {
            $table->markAsOccupied($customer);
            
            // Update customer record
            $customer->update([
                'table_id' => $table->id,
                'seated_at' => now(),
                'status' => 'seated',
            ]);

            Log::info("Table {$table->name} assigned to customer {$customer->name} (ID: {$customer->id})");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to assign table {$table->name} to customer {$customer->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Free a table (when customer leaves)
     */
    public function freeTable(Table $table): bool
    {
        try {
            $customer = $table->currentCustomer;
            
            $table->markAsVacant();
            
            // Update customer record if exists
            if ($customer) {
                $customer->update([
                    'completed_at' => now(),
                    'status' => 'completed',
                ]);
            }

            Log::info("Table {$table->name} freed");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to free table {$table->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get best table suggestions for a customer
     */
    public function getTableSuggestions(Customer $customer, int $maxSuggestions = 3): Collection
    {
        $partySize = $customer->party_size;
        $timeWindow = Setting::get('table_suggestion_time_window', 15);
        
        $suggestions = $this->getAvailableSoonTables($partySize, $timeWindow)
            ->take($maxSuggestions);

        // Add suggestion metadata
        return $suggestions->map(function ($table) {
            return [
                'table' => $table,
                'is_available_now' => $table->status === 'vacant',
                'minutes_until_free' => $table->minutes_until_free,
                'formatted_time_until_free' => $table->formatted_time_until_free,
                'suggestion_reason' => $this->getSuggestionReason($table),
            ];
        });
    }

    /**
     * Get reason for table suggestion
     */
    private function getSuggestionReason(Table $table): string
    {
        if ($table->status === 'vacant') {
            return 'Available now';
        }

        if ($table->status === 'occupied' && $table->expected_free_at) {
            $minutes = $table->minutes_until_free;
            if ($minutes <= 5) {
                return 'Finishing soon';
            } elseif ($minutes <= 10) {
                return 'Almost done';
            } else {
                return 'Will be ready shortly';
            }
        }

        return 'Available soon';
    }

    /**
     * Get table statistics for dashboard
     */
    public function getTableStatistics(): array
    {
        $totalTables = Table::count();
        $vacantTables = Table::available()->count();
        $occupiedTables = Table::byStatus('occupied')->count();
        $reservedTables = Table::byStatus('reserved')->count();
        $cleaningTables = Table::byStatus('cleaning')->count();
        $outOfServiceTables = Table::byStatus('out_of_service')->count();

        return [
            'total' => $totalTables,
            'vacant' => $vacantTables,
            'occupied' => $occupiedTables,
            'reserved' => $reservedTables,
            'cleaning' => $cleaningTables,
            'out_of_service' => $outOfServiceTables,
            'utilization_rate' => $totalTables > 0 ? round((($occupiedTables + $reservedTables) / $totalTables) * 100, 1) : 0,
        ];
    }

    /**
     * Process table status updates (scheduled job)
     */
    public function processTableStatusUpdates(): void
    {
        Log::info('Processing table status updates...');

        // Free tables where customers have completed dining
        $occupiedTables = Table::where('status', 'occupied')
            ->with('currentCustomer')
            ->get();

        foreach ($occupiedTables as $table) {
            $customer = $table->currentCustomer;
            
            if ($customer && $customer->status === 'completed') {
                $this->freeTable($table);
                Log::info("Auto-freed table {$table->name} - customer completed dining");
            }
        }

        // Check for expired reservations
        $expiredReservations = Table::where('status', 'reserved')
            ->where('expected_free_at', '<', now())
            ->with('reservedByCustomer')
            ->get();

        foreach ($expiredReservations as $table) {
            $customer = $table->reservedByCustomer;
            
            if ($customer && $customer->status === 'waiting') {
                // Table is ready but customer hasn't been called
                Log::warning("Table {$table->name} reservation expired - customer {$customer->name} still waiting");
                // TODO: Send notification to staff
            }
        }

        Log::info('Table status updates completed');
    }

    /**
     * Create sample tables for testing
     */
    public function createSampleTables(): void
    {
        $tables = [
            ['name' => 'T1', 'capacity' => 2, 'location' => 'Main Dining'],
            ['name' => 'T2', 'capacity' => 4, 'location' => 'Main Dining'],
            ['name' => 'T3', 'capacity' => 6, 'location' => 'Main Dining'],
            ['name' => 'T4', 'capacity' => 2, 'location' => 'Patio'],
            ['name' => 'T5', 'capacity' => 8, 'location' => 'Private Room'],
            ['name' => 'T6', 'capacity' => 4, 'location' => 'Patio'],
            ['name' => 'T7', 'capacity' => 2, 'location' => 'Main Dining'],
            ['name' => 'T8', 'capacity' => 6, 'location' => 'Main Dining'],
            ['name' => 'T9', 'capacity' => 4, 'location' => 'Patio'],
            ['name' => 'T10', 'capacity' => 2, 'location' => 'Main Dining'],
        ];

        foreach ($tables as $tableData) {
            Table::firstOrCreate(
                ['name' => $tableData['name']],
                $tableData
            );
        }

        Log::info('Sample tables created successfully');
    }
}
