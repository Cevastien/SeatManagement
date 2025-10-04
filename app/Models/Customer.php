<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'party_size',
        'contact_number',
        'queue_number',
        'priority_type',
        'is_group',
        'has_priority_member',
        'id_verification_status',
        'id_verification_data',
        'status',
        'estimated_wait_minutes',
        'registered_at',
        'called_at',
        'seated_at',
        'completed_at',
        'table_id',
        'is_table_requested',
        'special_requests',
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'has_priority_member' => 'boolean',
        'is_table_requested' => 'boolean',
        'id_verification_data' => 'array',
        'registered_at' => 'datetime',
        'called_at' => 'datetime',
        'seated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($customer) {
            // Calculate realistic wait time on creation
            if (!$customer->estimated_wait_minutes) {
                $customer->estimated_wait_minutes = self::calculateEstimatedWaitTime(
                    $customer->party_size,
                    $customer->priority_type ?? 'normal'
                );
            }
        });

        // After a new customer is created with 'waiting' status, reassign all queue numbers
        // This ensures priority customers jump ahead of regular customers
        static::created(function ($customer) {
            if ($customer->status === 'waiting') {
                // Reassign queue numbers to reflect priority ordering
                self::reassignQueueNumbers();
                
                // Refresh this customer instance to get the updated queue number
                $customer->refresh();
            }
        });
    }

    /**
     * Get the table assigned to this customer
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get all queue events for this customer
     */
    public function queueEvents(): HasMany
    {
        return $this->hasMany(QueueEvent::class);
    }

    /**
     * Get all ID verifications for this customer
     */
    public function idVerifications(): HasMany
    {
        return $this->hasMany(IdVerification::class);
    }

    /**
     * Get the latest ID verification for this customer
     */
    public function latestIdVerification()
    {
        return $this->hasOne(IdVerification::class)->latest();
    }

    /**
     * Scope for customers waiting in queue
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope for customers called but not seated
     */
    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }

    /**
     * Scope for customers currently seated
     */
    public function scopeSeated($query)
    {
        return $query->where('status', 'seated');
    }

    /**
     * Scope for completed customers
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for priority customers
     */
    public function scopePriority($query)
    {
        return $query->whereIn('priority_type', ['senior', 'pwd', 'pregnant']);
    }

    /**
     * Scope for group customers
     */
    public function scopeGroup($query)
    {
        return $query->where('is_group', true);
    }

    /**
     * Scope for today's customers
     */
    public function scopeToday($query)
    {
        return $query->whereDate('registered_at', Carbon::today());
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority_type) {
            'senior' => 'Senior Citizen',
            'pwd' => 'PWD',
            'pregnant' => 'Pregnant',
            'group' => 'Group',
            'normal' => 'Regular',
            default => 'Regular'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'waiting' => 'Waiting',
            'called' => 'Called',
            'seated' => 'Seated',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            default => 'Unknown'
        };
    }

    /**
     * Get wait time in minutes
     */
    public function getWaitTimeAttribute(): int
    {
        if (!$this->called_at) {
            return $this->registered_at->diffInMinutes(now());
        }
        
        return $this->registered_at->diffInMinutes($this->called_at);
    }

    /**
     * Get seating duration in minutes
     */
    public function getSeatingDurationAttribute(): int
    {
        if (!$this->seated_at) {
            return 0;
        }

        $endTime = $this->completed_at ?? now();
        return $this->seated_at->diffInMinutes($endTime);
    }

    /**
     * Check if customer has priority
     */
    public function hasPriority(): bool
    {
        return in_array($this->priority_type, ['senior', 'pwd', 'pregnant']);
    }




    /**
     * Check if customer is verified
     */
    public function isVerified(): bool
    {
        return $this->id_verification_status === 'verified';
    }

    /**
     * Mark customer as called
     */
    public function markAsCalled(): void
    {
        $this->update([
            'status' => 'called',
            'called_at' => now()
        ]);

        $this->queueEvents()->create([
            'event_type' => 'called',
            'event_time' => now(),
            'staff_id' => auth()->id()
        ]);
    }

    /**
     * Mark customer as seated
     */
    public function markAsSeated(int $tableId): void
    {
        $this->update([
            'status' => 'seated',
            'seated_at' => now(),
            'table_id' => $tableId
        ]);

        $this->queueEvents()->create([
            'event_type' => 'seated',
            'event_time' => now(),
            'staff_id' => auth()->id()
        ]);

        // Reassign queue numbers to match current positions
        static::reassignQueueNumbers();

        // Update wait times for remaining customers
        $estimator = new \App\Services\QueueEstimator();
        $estimator->updateAllWaitTimes();
    }

    /**
     * Mark customer as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        $this->queueEvents()->create([
            'event_type' => 'completed',
            'event_time' => now(),
            'staff_id' => auth()->id()
        ]);
    }

    /**
     * Mark customer as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled'
        ]);

        $this->queueEvents()->create([
            'event_type' => 'cancelled',
            'event_time' => now(),
            'staff_id' => auth()->id()
        ]);
        
        // Reassign queue numbers to match current positions
        static::reassignQueueNumbers();
        
        // Update wait times for remaining customers
        $estimator = new \App\Services\QueueEstimator();
        $estimator->updateAllWaitTimes();
    }

    /**
     * Mark customer as no show
     */
    public function markAsNoShow(): void
    {
        $this->update([
            'status' => 'no_show'
        ]);

        $this->queueEvents()->create([
            'event_type' => 'no_show',
            'event_time' => now(),
            'staff_id' => auth()->id()
        ]);
        
        // Reassign queue numbers to match current positions
        static::reassignQueueNumbers();
        
        // Update wait times for remaining customers
        $estimator = new \App\Services\QueueEstimator();
        $estimator->updateAllWaitTimes();
    }

    /**
     * Get next queue number
     */
    public static function getNextQueueNumber(): int
    {
        $lastCustomer = static::today()->orderBy('queue_number', 'desc')->first();
        return ($lastCustomer ? $lastCustomer->queue_number : 0) + 1;
    }

    /**
     * Reassign queue numbers to match current positions
     * This ensures queue numbers always align with actual positions
     * Priority customers (senior, pwd, pregnant) are always placed before regular customers
     */
    public static function reassignQueueNumbers(): void
    {
        // Get all waiting customers sorted by priority tier, then by registration time
        $waitingCustomers = static::where('status', 'waiting')
            ->orderByRaw("CASE 
                WHEN priority_type IN ('senior', 'pwd', 'pregnant') THEN 1
                WHEN priority_type = 'group' THEN 2
                WHEN priority_type = 'normal' THEN 3
                ELSE 4 
            END")
            ->orderBy('registered_at', 'asc')
            ->get();

        // Reassign queue numbers sequentially (1, 2, 3, ...)
        // Priority customers will have lower numbers (1-N), regular customers will be (N+1 onwards)
        foreach ($waitingCustomers as $index => $customer) {
            $newQueueNumber = $index + 1;
            if ($customer->queue_number != $newQueueNumber) {
                $customer->update(['queue_number' => $newQueueNumber]);
            }
        }
    }

    /**
     * Get formatted queue number with priority prefix (P001, R001 format)
     */
    public function getFormattedQueueNumberAttribute(): string
    {
        $prefix = match($this->priority_type) {
            'senior' => 'P',
            'pwd' => 'P',
            'pregnant' => 'P',
            'group' => 'G',
            'normal' => 'R',
            default => 'R'
        };
        
        // Zero-pad to 3 digits (P001, P002, R001, etc.)
        return $prefix . str_pad($this->queue_number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate realistic estimated wait time using dynamic queue estimator
     */
    public static function calculateEstimatedWaitTime(int $partySize, string $priorityType = 'normal'): int
    {
        $estimator = new \App\Services\QueueEstimator();
        return $estimator->calculateWaitTime($partySize, $priorityType);
    }

    /**
     * Format wait time for user-friendly display using queue estimator
     */
    public static function formatWaitTime(int $minutes): string
    {
        $estimator = new \App\Services\QueueEstimator();
        return $estimator->formatWaitTime($minutes);
    }

    /**
     * Get formatted wait time attribute
     */
    public function getFormattedWaitTimeAttribute(): string
    {
        if ($this->estimated_wait_minutes) {
            return self::formatWaitTime($this->estimated_wait_minutes);
        }
        
        // Calculate on the fly if not set
        $waitTime = self::calculateEstimatedWaitTime($this->party_size, $this->priority_type);
        return self::formatWaitTime($waitTime);
    }
}