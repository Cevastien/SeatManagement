<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Staff extends Authenticatable
{
    protected $table = 'staff';
    
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',  // admin, host, server, manager
        'is_active',
        'phone',
        'employee_id',
        'hire_date',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the password for the user (override for custom column name)
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Set the password for the user
     */
    public function setPasswordAttribute($value)
    {
        $this->password_hash = Hash::make($value);
    }

    /**
     * Check if staff member is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if staff member is manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if staff member is host
     */
    public function isHost(): bool
    {
        return $this->role === 'host';
    }

    /**
     * Check if staff member is server
     */
    public function isServer(): bool
    {
        return $this->role === 'server';
    }

    /**
     * Check if staff member can verify priority claims
     */
    public function canVerifyPriority(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'host']);
    }

    /**
     * Check if staff member can access admin functions
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Check if staff member can manage tables
     */
    public function canManageTables(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'host']);
    }

    /**
     * Check if staff member can view analytics
     */
    public function canViewAnalytics(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Check if staff member can export data
     */
    public function canExportData(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'host' => 'Host',
            'server' => 'Server',
            default => 'Staff'
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get all priority verifications processed by this staff member
     */
    public function priorityVerifications(): HasMany
    {
        return $this->hasMany(PriorityVerification::class, 'verified_by', 'id');
    }

    /**
     * Get all queue events created by this staff member
     */
    public function queueEvents(): HasMany
    {
        return $this->hasMany(QueueEvent::class, 'staff_id');
    }

    /**
     * Get daily exports created by this staff member
     */
    public function dailyExports(): HasMany
    {
        return $this->hasMany(DailyExport::class, 'exported_by');
    }

    /**
     * Scope for active staff members
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for staff by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for staff who can verify priority
     */
    public function scopeCanVerify($query)
    {
        return $query->whereIn('role', ['admin', 'manager', 'host']);
    }

    /**
     * Scope for staff who can access admin
     */
    public function scopeCanAccessAdmin($query)
    {
        return $query->whereIn('role', ['admin', 'manager']);
    }

    /**
     * Get staff performance statistics
     */
    public function getPerformanceStats(): array
    {
        $today = today();
        
        return [
            'verifications_today' => $this->priorityVerifications()
                ->whereDate('verified_at', $today)
                ->count(),
            'verifications_verified' => $this->priorityVerifications()
                ->whereDate('verified_at', $today)
                ->where('status', 'verified')
                ->count(),
            'verifications_rejected' => $this->priorityVerifications()
                ->whereDate('rejected_at', $today)
                ->where('status', 'rejected')
                ->count(),
            'queue_events_today' => $this->queueEvents()
                ->whereDate('event_time', $today)
                ->count(),
            'exports_created' => $this->dailyExports()
                ->whereDate('exported_at', $today)
                ->count(),
        ];
    }

    /**
     * Get staff member's work statistics for a date range
     */
    public function getWorkStats(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'verifications_processed' => $this->priorityVerifications()
                ->whereBetween('verified_at', [$startDate, $endDate])
                ->count(),
            'queue_events_created' => $this->queueEvents()
                ->whereBetween('event_time', [$startDate, $endDate])
                ->count(),
            'exports_created' => $this->dailyExports()
                ->whereBetween('exported_at', [$startDate, $endDate])
                ->count(),
        ];
    }

    /**
     * Create a new staff member
     */
    public static function createStaff(
        string $name,
        string $email,
        string $password,
        string $role,
        ?string $phone = null,
        ?string $employeeId = null
    ): self {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => Hash::make($password),
            'role' => $role,
            'phone' => $phone,
            'employee_id' => $employeeId,
            'hire_date' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Get all staff members with their performance stats
     */
    public static function getAllWithStats(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->withCount(['priorityVerifications as verifications_today' => function($query) {
                $query->whereDate('verified_at', today());
            }])
            ->withCount(['queueEvents as events_today' => function($query) {
                $query->whereDate('event_time', today());
            }])
            ->get();
    }
}
