<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'max_capacity',
        'status',
        'current_customer_id',
        'occupied_at',
        'estimated_departure',
        'is_vip',
    ];

    protected $casts = [
        'occupied_at' => 'datetime',
        'estimated_departure' => 'datetime',
        'is_vip' => 'boolean',
    ];

    /**
     * Get the customer currently seated at this table
     */
    public function currentCustomer()
    {
        return $this->belongsTo(Customer::class, 'current_customer_id');
    }

    /**
     * Check if table is available for a given party size
     */
    public function canAccommodate(int $partySize): bool
    {
        return $this->status === 'available' && $this->max_capacity >= $partySize;
    }

    /**
     * Assign customer to this table
     */
    public function assignCustomer(Customer $customer): bool
    {
        if (!$this->canAccommodate($customer->party_size)) {
            return false;
        }

        $this->update([
            'status' => 'occupied',
            'current_customer_id' => $customer->id,
            'occupied_at' => Carbon::now(),
            'estimated_departure' => $this->calculateEstimatedDeparture($customer->party_size),
        ]);

        // Update customer record
        $customer->update([
            'assigned_table_id' => $this->id,
            'table_assigned_at' => Carbon::now(),
            'estimated_departure' => $this->estimated_departure,
            'status' => 'seated',
        ]);

        return true;
    }

    /**
     * Free up the table when customer leaves
     */
    public function freeTable(): void
    {
        $customer = $this->currentCustomer;
        if ($customer) {
            $customer->update([
                'assigned_table_id' => null,
                'table_assigned_at' => null,
                'estimated_departure' => null,
                'status' => 'completed',
            ]);
        }

        $this->update([
            'status' => 'available',
            'current_customer_id' => null,
            'occupied_at' => null,
            'estimated_departure' => null,
        ]);
    }

    /**
     * Calculate estimated departure time based on party size
     */
    private function calculateEstimatedDeparture(int $partySize): Carbon
    {
        $averageDiningTimes = [
            1 => 30,  // Solo diners
            2 => 35,  // Couples
            3 => 45,  // Small groups
            4 => 45,  // Standard groups
            5 => 60,  // Large groups
            6 => 60,  // Large groups
            8 => 90,  // VIP table
        ];

        $averageTime = $averageDiningTimes[$partySize] ?? 45;
        return Carbon::now()->addMinutes($averageTime);
    }

    /**
     * Get minutes until table will be free
     */
    public function getMinutesUntilFree(): int
    {
        if ($this->status !== 'occupied' || !$this->estimated_departure) {
            return 0;
        }

        return max(0, $this->estimated_departure->diffInMinutes(Carbon::now()));
    }

    /**
     * Check if table will be free soon (within next 15 minutes)
     */
    public function willBeFreeSoon(): bool
    {
        return $this->getMinutesUntilFree() <= 15;
    }
}