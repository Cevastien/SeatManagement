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

    protected $table = 'customers';

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
        'registration_confirmed_at',
        'id_verified_at',
        'priority_applied_at',
        'called_at',
        'seated_at',
        'completed_at',
        'table_id',
        'is_table_requested',
        'special_requests',
        'assigned_table_id',
        'table_assigned_at',
        'estimated_departure',
        'last_updated_at',
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'has_priority_member' => 'boolean',
        'is_table_requested' => 'boolean',
        'id_verification_data' => 'array',
        'registered_at' => 'datetime',
        'registration_confirmed_at' => 'datetime',
        'id_verified_at' => 'datetime',
        'priority_applied_at' => 'datetime',
        'called_at' => 'datetime',
        'seated_at' => 'datetime',
        'completed_at' => 'datetime',
        'table_assigned_at' => 'datetime',
        'estimated_departure' => 'datetime',
        'last_updated_at' => 'datetime',
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
        return $this->belongsTo(Table::class, 'table_id');
    }

    /**
     * Get all queue events for this customer
     */
    public function queueEvents(): HasMany
    {
        return $this->hasMany(QueueEvent::class, 'queue_customer_id');
    }

    // Note: ID verification functionality has been consolidated into PriorityVerification

    /**
     * Get all priority verifications for this customer
     */
    public function priorityVerifications(): HasMany
    {
        return $this->hasMany(PriorityVerification::class, 'queue_customer_id');
    }

    /**
     * Get the latest priority verification for this customer
     */
    public function latestPriorityVerification()
    {
        return $this->hasOne(PriorityVerification::class)->latest();
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
        // Handle group with priority member
        if ($this->is_group && $this->has_priority_member) {
            return match($this->priority_type) {
                'senior' => 'Group (Senior Citizen)',
                'pwd' => 'Group (PWD)',
                'pregnant' => 'Group (Pregnant)',
                default => 'Group'
            };
        }
        
        // Handle individual customers
        return match($this->priority_type) {
            'senior' => 'Senior Citizen',
            'pwd' => 'PWD',
            'pregnant' => 'Pregnant',
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
     * Mark registration as confirmed
     */
    public function markRegistrationConfirmed(): void
    {
        $this->update([
            'registration_confirmed_at' => now(),
            'last_updated_at' => now()
        ]);

        $this->queueEvents()->create([
            'event_type' => 'registration_confirmed',
            'event_time' => now(),
            'notes' => 'Registration confirmed and customer added to queue'
        ]);
    }

    /**
     * Mark ID verification as completed
     */
    public function markIdVerified(string $verifiedBy = null, array $verificationData = null): void
    {
        $this->update([
            'id_verification_status' => 'verified',
            'id_verified_at' => now(),
            'id_verification_data' => $verificationData,
            'last_updated_at' => now()
        ]);

        $this->queueEvents()->create([
            'event_type' => 'id_verified',
            'event_time' => now(),
            'staff_id' => auth()->id(),
            'notes' => 'ID verification completed',
            'metadata' => [
                'verified_by' => $verifiedBy,
                'verification_data' => $verificationData
            ]
        ]);
    }

    /**
     * Mark priority status as applied
     */
    public function markPriorityApplied(string $priorityType, bool $hasPriorityMember = true): void
    {
        $this->update([
            'priority_type' => $priorityType,
            'has_priority_member' => $hasPriorityMember,
            'priority_applied_at' => now(),
            'last_updated_at' => now()
        ]);

        $this->queueEvents()->create([
            'event_type' => 'priority_applied',
            'event_time' => now(),
            'notes' => "Priority status applied: {$priorityType}",
            'metadata' => [
                'priority_type' => $priorityType,
                'has_priority_member' => $hasPriorityMember
            ]
        ]);
    }

    /**
     * Update last updated timestamp
     */
    public function touchLastUpdated(): void
    {
        $this->update(['last_updated_at' => now()]);
    }

    /**
     * Mark customer as called
     */
    public function markAsCalled(): void
    {
        $this->update([
            'status' => 'called',
            'called_at' => now(),
            'last_updated_at' => now()
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
            'table_id' => $tableId,
            'last_updated_at' => now()
        ]);

        $this->queueEvents()->create([
            'event_type' => 'seated',
            'event_time' => now(),
            'staff_id' => auth()->id()
        ]);

        // Reassign queue numbers to match current positions
        static::reassignQueueNumbers();

        // Update wait times for remaining customers using SmartQueueEstimator
        $this->updateAllCustomerWaitTimes();
    }

    /**
     * Mark customer as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'last_updated_at' => now()
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
            'status' => 'cancelled',
            'last_updated_at' => now()
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
            'status' => 'no_show',
            'last_updated_at' => now()
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
     * Get next queue number with priority system
     */
    public static function getNextQueueNumber($priorityType = 'normal'): int
    {
        // Priority order: pregnant (highest) > pwd > senior > normal (lowest)
        $priorityOrder = [
            'pregnant' => 1,
            'pwd' => 2, 
            'senior' => 3,
            'normal' => 4
        ];
        
        $currentPriority = $priorityOrder[$priorityType] ?? 4;
        
        // Get the last customer with same or higher priority
        $lastCustomer = static::today()
            ->where(function($query) use ($priorityOrder, $currentPriority) {
                foreach ($priorityOrder as $type => $order) {
                    if ($order <= $currentPriority) {
                        $query->orWhere('priority_type', $type);
                    }
                }
            })
            ->orderBy('queue_number', 'desc')
            ->first();
            
        $nextQueueNumber = ($lastCustomer ? $lastCustomer->queue_number : 0) + 1;
        
        // Check if this queue number already exists (safety check)
        $existingCustomer = static::today()->where('queue_number', $nextQueueNumber)->first();
        if ($existingCustomer) {
            // If duplicate found, get the highest queue number and add 1
            $highestQueueNumber = static::today()->max('queue_number') ?? 0;
            $nextQueueNumber = $highestQueueNumber + 1;
        }
        
        return $nextQueueNumber;
    }

    /**
     * Reassign queue numbers to match current positions
     * This ensures queue numbers always align with actual positions
     * Priority customers (pregnant > pwd > senior) are always placed before regular customers
     */
    public static function reassignQueueNumbers(): void
    {
        // Get all waiting customers sorted by priority tier, then by registration time
        // Using parameterized query to prevent SQL injection
        $waitingCustomers = static::where('status', 'waiting')
            ->orderByRaw("CASE 
                WHEN priority_type = ? THEN 1  -- pregnant (highest priority)
                WHEN priority_type = ? THEN 2  -- pwd
                WHEN priority_type = ? THEN 3  -- senior
                ELSE 4                          -- normal
            END", ['pregnant', 'pwd', 'senior'])
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
        // Priority customers get 'P' prefix, normal customers get 'R' prefix
        $prefix = $this->hasPriority() ? 'P' : 'R';
        
        // Zero-pad to 3 digits (P001, P002, R001, etc.)
        return $prefix . str_pad($this->queue_number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate realistic estimated wait time using smart queue estimator
     */
    public static function calculateEstimatedWaitTime(int $partySize, string $priorityType = 'normal'): int
    {
        // Create a temporary customer instance to calculate wait time
        $tempCustomer = new static([
            'party_size' => $partySize,
            'priority_type' => $priorityType,
            'status' => 'waiting',
            'created_at' => now(),
        ]);
        
        $estimator = new \App\Services\SmartQueueEstimator();
        $result = $estimator->calculateWaitTime($tempCustomer);
        return $result['wait_minutes'];
    }

    /**
     * Format wait time for user-friendly display
     */
    public static function formatWaitTime(int $minutes): string
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
    
    /**
     * Update wait times for all waiting customers using SmartQueueEstimator
     */
    public function updateAllCustomerWaitTimes(): void
    {
        try {
            $waitingCustomers = static::where('status', 'waiting')->get();
            $smartEstimator = new \App\Services\SmartQueueEstimator();
            
            foreach ($waitingCustomers as $customer) {
                $result = $smartEstimator->calculateWaitTime($customer);
                $customer->update(['estimated_wait_minutes' => $result['wait_minutes']]);
            }
            
            \Log::info('Updated wait times for ' . $waitingCustomers->count() . ' waiting customers');
        } catch (\Exception $e) {
            \Log::error('Failed to update customer wait times: ' . $e->getMessage());
        }
    }
}