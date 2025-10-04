<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PriorityVerification extends Model
{
    protected $fillable = [
        'customer_name',
        'priority_type',
        'status',
        'pin',
        'requested_at',
        'verified_at',
        'verified_by',
        'timeout_at',
        'timeout_notified',
        'rejection_reason'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'verified_at' => 'datetime',
        'timeout_at' => 'datetime',
        'timeout_notified' => 'boolean'
    ];

    /**
     * Check if verification is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if verification is verified
     */
    public function isVerified()
    {
        return $this->status === 'verified';
    }

    /**
     * Check if verification is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get priority type display name
     */
    public function getPriorityDisplayAttribute()
    {
        return match($this->priority_type) {
            'senior' => 'Senior Citizen',
            'pwd' => 'PWD',
            'pregnant' => 'Pregnant',
            default => 'Regular'
        };
    }

    /**
     * Generate queue number based on priority type
     */
    public function getQueueNumberAttribute()
    {
        if (!$this->pin) {
            return null;
        }

        $prefix = match($this->priority_type) {
            'senior' => 'S',
            'pwd' => 'P',
            'pregnant' => 'W',
            default => 'R'
        };

        return $prefix . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Mark verification as verified
     */
    public function markAsVerified($verifiedBy = null, $pin = null)
    {
        $this->update([
            'status' => 'verified',
            'pin' => $pin,
            'verified_at' => now(),
            'verified_by' => $verifiedBy
        ]);
    }

    /**
     * Mark verification as rejected
     */
    public function markAsRejected($rejectedBy = null, $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $rejectedBy
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
     * Scope for recent verifications
     */
    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('requested_at', '>', now()->subMinutes($minutes));
    }
}