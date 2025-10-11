<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriorityVerification extends Model
{
    protected $table = 'verifications';
    
    protected $fillable = [
        'queue_customer_id',
        'customer_name',
        'priority_type',  // Only: senior, pwd, pregnant
        'party_size',
        'status',         // pending, verified, rejected
        'requested_at',
        'verified_at',
        'verified_by',    // Staff member name/ID
        'rejected_by',    // Staff member who rejected
        'rejection_reason',
        'id_number',      // Optional: ID card number for verification
        'rejected_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Automatically decrypt customer name when accessing
     */
    public function getCustomerNameAttribute($value)
    {
        // If the value looks like encrypted data (starts with eyJ), try to decrypt it
        if (is_string($value) && str_starts_with($value, 'eyJ')) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                // If decryption fails, return the original value
                return $value;
            }
        }
        
        // If it's already plain text, return as is
        return $value;
    }

    /**
     * Get the customer that owns this verification
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'queue_customer_id');
    }

    /**
     * Check if verification is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if verification is verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if verification is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get priority type display name
     */
    public function getPriorityDisplayAttribute(): string
    {
        return match($this->priority_type) {
            'senior' => 'Senior Citizen',
            'pwd' => 'PWD',
            'pregnant' => 'Pregnant',
            default => 'Regular'
        };
    }

    /**
     * Mark verification as verified by staff
     * Note: Even pregnant customers can be rejected by staff
     */
    public function markAsVerified(string $verifiedBy, ?string $idNumber = null): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
            'id_number' => $idNumber,
        ]);
    }

    /**
     * Mark verification as rejected by staff
     */
    public function markAsRejected(string $rejectedBy, ?string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $rejectedBy,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Scope for pending verifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for verified verifications
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope for rejected verifications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for recent verifications
     */
    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('requested_at', '>', now()->subMinutes($minutes));
    }

    /**
     * Scope for specific priority type
     */
    public function scopeByPriorityType($query, string $priorityType)
    {
        return $query->where('priority_type', $priorityType);
    }

    /**
     * Scope for today's verifications
     */
    public function scopeToday($query)
    {
        return $query->whereDate('requested_at', today());
    }

    /**
     * Get verification statistics for dashboard
     */
    public static function getStats(): array
    {
        $today = today();
        
        return [
            'pending' => static::where('status', 'pending')->count(),
            'verified_today' => static::where('status', 'verified')
                ->whereDate('verified_at', $today)->count(),
            'rejected_today' => static::where('status', 'rejected')
                ->whereDate('rejected_at', $today)->count(),
            'total_today' => static::whereDate('requested_at', $today)->count(),
            'breakdown_by_priority' => static::whereDate('requested_at', $today)
                ->selectRaw('priority_type, status, COUNT(*) as count')
                ->groupBy('priority_type', 'status')
                ->get()
                ->groupBy('priority_type')
        ];
    }

    /**
     * Create a new priority verification request
     */
    public static function createRequest(
        int $customerId,
        string $customerName,
        string $priorityType,
        int $partySize,
        ?string $idNumber = null
    ): self {
        return static::create([
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'priority_type' => $priorityType,
            'party_size' => $partySize,
            'status' => 'pending',
            'requested_at' => now(),
            'id_number' => $idNumber,
        ]);
    }

    /**
     * Get verification duration (time from request to resolution)
     */
    public function getVerificationDurationAttribute(): ?int
    {
        if (!$this->requested_at) {
            return null;
        }

        $endTime = $this->verified_at ?? $this->rejected_at ?? now();
        return $this->requested_at->diffInMinutes($endTime);
    }

    /**
     * Check if verification is taking too long (more than 5 minutes)
     */
    public function isTakingTooLong(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        return $this->requested_at->diffInMinutes(now()) > 5;
    }
}