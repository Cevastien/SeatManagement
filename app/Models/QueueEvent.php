<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'event_type',
        'event_time',
        'staff_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the customer this event belongs to
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the staff member who triggered this event
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Scope for specific event types
     */
    public function scopeRegistered($query)
    {
        return $query->where('event_type', 'registered');
    }

    public function scopeCalled($query)
    {
        return $query->where('event_type', 'called');
    }

    public function scopeSeated($query)
    {
        return $query->where('event_type', 'seated');
    }

    public function scopeCompleted($query)
    {
        return $query->where('event_type', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('event_type', 'cancelled');
    }

    public function scopeNoShow($query)
    {
        return $query->where('event_type', 'no_show');
    }

    public function scopePriorityApplied($query)
    {
        return $query->where('event_type', 'priority_applied');
    }

    /**
     * Scope for today's events
     */
    public function scopeToday($query)
    {
        return $query->whereDate('event_time', today());
    }

    /**
     * Get event type label
     */
    public function getEventTypeLabelAttribute(): string
    {
        return match($this->event_type) {
            'registered' => 'Registered',
            'called' => 'Called',
            'seated' => 'Seated',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'hold' => 'Hold',
            'priority_applied' => 'Priority Applied',
            default => 'Unknown'
        };
    }

    /**
     * Get event icon
     */
    public function getEventIconAttribute(): string
    {
        return match($this->event_type) {
            'registered' => '📝',
            'called' => '📞',
            'seated' => '🪑',
            'completed' => '✅',
            'cancelled' => '❌',
            'no_show' => '⏰',
            'hold' => '⏸️',
            'priority_applied' => '⭐',
            default => '❓'
        };
    }

    /**
     * Get event color class
     */
    public function getEventColorClassAttribute(): string
    {
        return match($this->event_type) {
            'registered' => 'bg-blue-100 text-blue-800',
            'called' => 'bg-yellow-100 text-yellow-800',
            'seated' => 'bg-green-100 text-green-800',
            'completed' => 'bg-emerald-100 text-emerald-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'no_show' => 'bg-gray-100 text-gray-800',
            'hold' => 'bg-orange-100 text-orange-800',
            'priority_applied' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Create a new queue event
     */
    public static function createEvent(
        int $customerId,
        string $eventType,
        int $staffId = null,
        string $notes = null,
        array $metadata = null
    ): self {
        return static::create([
            'customer_id' => $customerId,
            'event_type' => $eventType,
            'event_time' => now(),
            'staff_id' => $staffId,
            'notes' => $notes,
            'metadata' => $metadata
        ]);
    }

    /**
     * Get events for a specific customer
     */
    public static function getCustomerEvents(int $customerId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('customer_id', $customerId)
            ->orderBy('event_time', 'asc')
            ->get();
    }

    /**
     * Get today's event summary
     */
    public static function getTodaySummary(): array
    {
        $events = static::today()->get();
        
        return [
            'total_events' => $events->count(),
            'registered' => $events->where('event_type', 'registered')->count(),
            'called' => $events->where('event_type', 'called')->count(),
            'seated' => $events->where('event_type', 'seated')->count(),
            'completed' => $events->where('event_type', 'completed')->count(),
            'cancelled' => $events->where('event_type', 'cancelled')->count(),
            'no_show' => $events->where('event_type', 'no_show')->count(),
            'priority_applied' => $events->where('event_type', 'priority_applied')->count(),
        ];
    }

    /**
     * Get hourly event breakdown for today
     */
    public static function getHourlyBreakdown(): array
    {
        $events = static::today()
            ->selectRaw('HOUR(event_time) as hour, event_type, COUNT(*) as count')
            ->groupBy('hour', 'event_type')
            ->orderBy('hour')
            ->get();

        $breakdown = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $breakdown[$hour] = [
                'hour' => sprintf('%02d:00', $hour),
                'registered' => 0,
                'called' => 0,
                'seated' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'no_show' => 0,
            ];
        }

        foreach ($events as $event) {
            $breakdown[$event->hour][$event->event_type] = $event->count;
        }

        return array_values($breakdown);
    }
}