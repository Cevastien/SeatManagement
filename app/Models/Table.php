<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'number',          // Table number/name
        'capacity',        // Max party size
        'status',          // available, occupied, cleaning
        'current_customer_id', // Customer currently seated
        'occupied_at',     // When table was occupied
        'is_vip',          // VIP table flag
        'location',        // Table location (window, corner, etc.)
        'notes',           // Special notes about the table
    ];

    protected $casts = [
        'occupied_at' => 'datetime',
        'is_vip' => 'boolean',
    ];

    /**
     * Get the customer currently seated at this table
     */
    public function currentCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'current_customer_id');
    }

    /**
     * Get all customers who have used this table (historical)
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'assigned_table_id');
    }

    /**
     * Check if table can accommodate party size
     */
    public function canAccommodate(int $partySize): bool
    {
        return $this->status === 'available' && $this->capacity >= $partySize;
    }

    /**
     * Check if table is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if table is occupied
     */
    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    /**
     * Check if table is being cleaned
     */
    public function isCleaning(): bool
    {
        return $this->status === 'cleaning';
    }

    /**
     * Mark table as occupied (real-time seating only)
     */
    public function occupy(Customer $customer): void
    {
        $this->update([
            'status' => 'occupied',
            'current_customer_id' => $customer->id,
            'occupied_at' => now(),
        ]);

        // Update customer record
        $customer->update([
            'assigned_table_id' => $this->id,
            'table_assigned_at' => now(),
            'status' => 'seated',
        ]);
    }

    /**
     * Mark table as available when customer leaves
     */
    public function makeAvailable(): void
    {
        $this->update([
            'status' => 'available',
            'current_customer_id' => null,
            'occupied_at' => null,
        ]);
    }

    /**
     * Mark table as cleaning
     */
    public function markForCleaning(): void
    {
        $this->update([
            'status' => 'cleaning',
        ]);
    }

    /**
     * Mark table as available after cleaning
     */
    public function markCleaningComplete(): void
    {
        $this->update([
            'status' => 'available',
        ]);
    }

    /**
     * Scope for available tables
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for occupied tables
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope for tables being cleaned
     */
    public function scopeCleaning($query)
    {
        return $query->where('status', 'cleaning');
    }

    /**
     * Scope for tables that can fit party size
     */
    public function scopeCanFit($query, int $partySize)
    {
        return $query->where('capacity', '>=', $partySize);
    }

    /**
     * Scope for VIP tables
     */
    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Scope for regular tables
     */
    public function scopeRegular($query)
    {
        return $query->where('is_vip', false);
    }

    /**
     * Get best available table for party size
     */
    public static function getBestAvailableTable(int $partySize): ?self
    {
        // First try to find exact capacity match
        $exactMatch = static::available()
            ->canFit($partySize)
            ->where('capacity', $partySize)
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        // Then find smallest available table that can fit
        return static::available()
            ->canFit($partySize)
            ->orderBy('capacity', 'asc')
            ->first();
    }

    /**
     * Get all available tables that can accommodate party size
     */
    public static function getAvailableTablesForParty(int $partySize): \Illuminate\Database\Eloquent\Collection
    {
        return static::available()
            ->canFit($partySize)
            ->orderBy('capacity', 'asc')
            ->get();
    }

    /**
     * Get table utilization statistics
     */
    public static function getUtilizationStats(): array
    {
        $totalTables = static::count();
        $occupiedTables = static::occupied()->count();
        $availableTables = static::available()->count();
        $cleaningTables = static::cleaning()->count();

        return [
            'total_tables' => $totalTables,
            'occupied' => $occupiedTables,
            'available' => $availableTables,
            'cleaning' => $cleaningTables,
            'utilization_rate' => $totalTables > 0 ? round(($occupiedTables / $totalTables) * 100, 1) : 0,
        ];
    }

    /**
     * Get table capacity distribution
     */
    public static function getCapacityDistribution(): array
    {
        return static::selectRaw('capacity, COUNT(*) as count')
            ->groupBy('capacity')
            ->orderBy('capacity')
            ->pluck('count', 'capacity')
            ->toArray();
    }

    /**
     * Get tables that have been occupied the longest
     */
    public function getLongestOccupiedTables(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::occupied()
            ->with('currentCustomer')
            ->orderBy('occupied_at', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get table status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'occupied' => 'Occupied',
            'cleaning' => 'Cleaning',
            default => 'Unknown'
        };
    }

    /**
     * Get table type label
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->is_vip ? 'VIP' : 'Regular';
    }

    /**
     * Get capacity label
     */
    public function getCapacityLabelAttribute(): string
    {
        return $this->capacity === 1 ? '1 person' : "{$this->capacity} people";
    }
}