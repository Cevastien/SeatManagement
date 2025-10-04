<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\PriorityVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVerificationTimeouts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Check for pending verifications that have exceeded 5 minutes
     * and automatically convert them to regular queue
     */
    public function handle(): void
    {
        try {
            // Find all pending verifications that are older than 5 minutes
            $timedOutVerifications = PriorityVerification::where('status', 'pending')
                ->where('requested_at', '<', now()->subMinutes(5))
                ->get();

            foreach ($timedOutVerifications as $verification) {
                Log::warning('Verification timeout detected', [
                    'verification_id' => $verification->id,
                    'customer_name' => $verification->customer_name,
                    'priority_type' => $verification->priority_type,
                    'requested_at' => $verification->requested_at,
                    'elapsed_minutes' => $verification->requested_at->diffInMinutes(now())
                ]);

                // Mark verification as timeout
                $verification->update([
                    'status' => 'rejected',
                    'timeout_at' => now(),
                    'timeout_notified' => true,
                    'rejection_reason' => 'Verification timeout - No staff response within 5 minutes'
                ]);

                // Find and convert the customer to regular queue
                $customer = Customer::where('name', $verification->customer_name)
                    ->whereIn('priority_type', ['senior', 'pwd'])
                    ->where('status', 'waiting')
                    ->latest()
                    ->first();

                if ($customer) {
                    $oldPriorityType = $customer->priority_type;
                    
                    // Convert to regular queue
                    $customer->update([
                        'priority_type' => 'normal',
                        'has_priority_member' => false,
                        'id_verification_status' => 'timeout'
                    ]);

                    // Reassign queue numbers (priority customers will be moved down)
                    Customer::reassignQueueNumbers();

                    Log::info('Customer converted to regular queue due to timeout', [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'old_priority_type' => $oldPriorityType,
                        'new_priority_type' => 'normal',
                        'new_queue_number' => $customer->fresh()->queue_number
                    ]);
                } else {
                    Log::warning('Customer not found for timeout conversion', [
                        'verification_id' => $verification->id,
                        'customer_name' => $verification->customer_name
                    ]);
                }
            }

            if ($timedOutVerifications->count() > 0) {
                Log::info('Verification timeout processing complete', [
                    'processed_count' => $timedOutVerifications->count()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to process verification timeouts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
