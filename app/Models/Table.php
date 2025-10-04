<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'status',
        'current_customer_id',
        'reserved_by_customer_id',
        'expected_free_at',
        'location',
        'notes',
    ];

    protected $casts = [
        'expected_free_at' => 'datetime',
    ];

    /**
     * Get the customer currently seated at this table
     */
    public function currentCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'current_customer_id');
    }

    /**
     * Get the customer who has reserved this table
     */
    public function reservedByCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'reserved_by_customer_id');
    }

    /**
     * Check if table is available for a specific party size
     */
    public function canAccommodate(int $partySize): bool
    {
        return $this->capacity >= $partySize && $this->status === 'vacant';
    }

    /**
     * Check if table will be available soon for a specific party size
     */
    public function willBeAvailableSoon(int $partySize, int $timeWindowMinutes = 15): bool
    {
        if ($this->capacity < $partySize) {
            return false;
        }

        if ($this->status === 'vacant') {
            return true; // Available now
        }

        if ($this->status === 'occupied' && $this->expected_free_at) {
            $minutesUntilFree = now()->diffInMinutes($this->expected_free_at, false);
            return $minutesUntilFree >= 0 && $minutesUntilFree <= $timeWindowMinutes;
        }

        return false;
    }

    /**
     * Mark table as occupied by a customer
     */
    public function markAsOccupied(Customer $customer): void
    {
        $this->update([
            'status' => 'occupied',
            'current_customer_id' => $customer->id,
            'reserved_by_customer_id' => null,
            'expected_free_at' => null,
        ]);
    }

    /**
     * Mark table as reserved by a customer
     */
    public function markAsReserved(Customer $customer, Carbon $expectedFreeAt): void
    {
        $this->update([
            'status' => 'reserved',
            'reserved_by_customer_id' => $customer->id,
            'expected_free_at' => $expectedFreeAt,
        ]);
    }

    /**
     * Mark table as vacant (free)
     */
    public function markAsVacant(): void
    {
        $this->update([
            'status' => 'vacant',
            'current_customer_id' => null,
            'reserved_by_customer_id' => null,
            'expected_free_at' => null,
        ]);
    }

    /**
     * Mark table as cleaning
     */
    public function markAsCleaning(): void
    {
        $this->update([
            'status' => 'cleaning',
        ]);
    }

    /**
     * Calculate estimated free time for occupied table
     */
    public function calculateExpectedFreeTime(): ?Carbon
    {
        if ($this->status !== 'occupied' || !$this->current_customer_id) {
            return null;
        }

        $customer = $this->currentCustomer;
        if (!$customer || !$customer->seated_at) {
            return null;
        }

        // Get average dining duration from settings (fallback to 60 minutes)
        $avgDiningDuration = \App\Models\Setting::get('avg_dining_duration', 60);
        
        return $customer->seated_at->addMinutes($avgDiningDuration);
    }

    /**
     * Scope for tables available now
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'vacant');
    }

    /**
     * Scope for tables available soon
     */
    public function scopeAvailableSoon($query, int $timeWindowMinutes = 15)
    {
        return $query->where(function ($q) use ($timeWindowMinutes) {
            $q->where('status', 'vacant')
              ->orWhere(function ($q2) use ($timeWindowMinutes) {
                  $q2->where('status', 'occupied')
                     ->whereNotNull('expected_free_at')
                     ->where('expected_free_at', '<=', now()->addMinutes($timeWindowMinutes));
              });
        });
    }

    /**
     * Scope for tables that can accommodate party size
     */
    public function scopeCanAccommodate($query, int $partySize)
    {
        return $query->where('capacity', '>=', $partySize);
    }

    /**
     * Scope for tables by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'vacant' => 'green',
            'occupied' => 'red',
            'reserved' => 'yellow',
            'cleaning' => 'orange',
            'out_of_service' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get status label for UI
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'vacant' => 'Available',
            'occupied' => 'Occupied',
            'reserved' => 'Reserved',
            'cleaning' => 'Cleaning',
            'out_of_service' => 'Out of Service',
            default => 'Unknown'
        };
    }

    /**
     * Get minutes until free (for occupied/reserved tables)
     */
    public function getMinutesUntilFreeAttribute(): ?int
    {
        if (!$this->expected_free_at) {
            return null;
        }

        $minutes = now()->diffInMinutes($this->expected_free_at, false);
        return $minutes > 0 ? $minutes : 0;
    }

    /**
     * Get formatted time until free
     */
    public function getFormattedTimeUntilFreeAttribute(): string
    {
        $minutes = $this->minutes_until_free;
        
        if ($minutes === null) {
            return 'Unknown';
        }

        if ($minutes === 0) {
            return 'Available now';
        }

        if ($minutes < 60) {
            return "about {$minutes} minutes";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($remainingMinutes === 0) {
            return "about {$hours} hour" . ($hours > 1 ? 's' : '');
        }

        return "about {$hours}h {$remainingMinutes}m";
    }
}