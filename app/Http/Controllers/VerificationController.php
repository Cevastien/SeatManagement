<?php

namespace App\Http\Controllers;

use App\Models\PriorityVerification;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    /**
     * Customer requests verification
     */
    public function requestVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'priority_type' => 'required|in:senior,pwd,pregnant',
            'party_size' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if there's already a pending verification for this customer
            $existingVerification = PriorityVerification::where('customer_name', $request->customer_name)
                ->where('status', 'pending')
                ->first();

            if ($existingVerification) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verification request already exists',
                    'verification' => [
                        'id' => $existingVerification->id,
                        'status' => $existingVerification->status,
                        'requested_at' => $existingVerification->requested_at->toISOString()
                    ]
                ]);
            }

            // Create new verification request
            $verification = PriorityVerification::create([
                'customer_name' => $request->customer_name,
                'priority_type' => $request->priority_type,
                'party_size' => $request->party_size ?? 1, // Include party size from request
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            Log::info('Priority verification requested', [
                'verification_id' => $verification->id,
                'customer_name' => $request->customer_name,
                'priority_type' => $request->priority_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification request submitted successfully',
                'verification' => [
                    'id' => $verification->id,
                    'status' => $verification->status,
                    'requested_at' => $verification->requested_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to request verification', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit verification request. Please try again.'
            ], 500);
        }
    }

    /**
     * Check verification status
     */
    public function checkVerificationStatus($id)
    {
        try {
            $verification = PriorityVerification::find($id);

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'verification' => [
                    'id' => $verification->id,
                    'customer_name' => $verification->customer_name,
                    'priority_type' => $verification->priority_type,
                    'status' => $verification->status,
                    'requested_at' => $verification->requested_at->toISOString(),
                    'verified_at' => $verification->verified_at?->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check verification status', [
                'verification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check verification status'
            ], 500);
        }
    }

    /**
     * Get pending verifications (for staff dashboard)
     */
    public function getPendingVerifications()
    {
        try {
            $pendingVerifications = PriorityVerification::where('status', 'pending')
                ->orderBy('requested_at', 'asc')
                ->get()
                ->map(function ($verification) {
                    // Get the complete customer data from customers table
                    $customer = Customer::where('name', $verification->customer_name)
                        ->where('priority_type', $verification->priority_type)
                        ->where('status', 'waiting')
                        ->latest()
                        ->first();
                    
                    return [
                        'id' => $verification->id,
                        'customer_name' => $verification->customer_name,
                        'priority_type' => $verification->priority_type,
                        'priority_display' => $verification->priority_display,
                        'status' => $verification->status,
                        'requested_at' => $verification->requested_at->format('M d, Y h:i A'),
                        'time_elapsed' => now()->diffInMinutes($verification->requested_at),
                        // Add real customer data from registration
                        'party_size' => $customer ? $customer->party_size : 1,
                        'queue_number' => $customer ? $customer->queue_number : $verification->id,
                        'contact_number' => $customer ? $customer->contact_number : '',
                        'registered_at' => $customer ? $customer->registered_at->format('M d, Y h:i A') : $verification->requested_at->format('M d, Y h:i A'),
                        'estimated_wait_minutes' => $customer ? $customer->estimated_wait_minutes : 0,
                        'visit_count' => $customer ? ($customer->created_at->isToday() ? 1 : 2) : 1 // Simple visit count logic
                    ];
                });

            return response()->json([
                'success' => true,
                'has_pending' => $pendingVerifications->count() > 0,
                'pending_verifications' => $pendingVerifications,
                'count' => $pendingVerifications->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get pending verifications', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load pending verifications'
            ], 500);
        }
    }


    /**
     * Reject verification (for staff)
     */
    public function rejectVerification(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'verification_id' => 'required|integer|exists:verifications,id',
            'rejected_by' => 'required|string',
            'reason' => 'nullable|string|max:500',
            'id_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = PriorityVerification::find($request->json('verification_id'));

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found'
                ], 404);
            }

            if ($verification->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification has already been processed'
                ], 400);
            }

            // Mark as rejected
            $verification->update([
                'status' => 'rejected',
                'verified_at' => now(),
                'verified_by' => $request->json('rejected_by'),
                'rejection_reason' => $request->json('reason') ?? 'ID verification failed',
                'id_number' => $request->json('id_number')
            ]);

            // Update the customer record if it exists
            $customer = Customer::where('name', $verification->customer_name)
                ->where('priority_type', $verification->priority_type)
                ->where('status', 'waiting')
                ->latest()
                ->first();

            if ($customer) {
                $customer->update([
                    'id_verification_status' => 'rejected',
                    'id_verification_data' => [
                        'rejected_by' => $request->json('rejected_by'),
                        'rejected_at' => now()->toISOString(),
                        'reason' => $request->json('reason') ?? 'ID verification failed',
                        'verification_id' => $verification->id,
                        'id_number' => $request->json('id_number')
                    ]
                ]);

                Log::info('Customer record updated with rejection', [
                    'customer_id' => $customer->id,
                    'verification_id' => $verification->id
                ]);
            }

            Log::info('Priority verification rejected', [
                'verification_id' => $verification->id,
                'customer_name' => $verification->customer_name,
                'rejected_by' => $request->json('rejected_by'),
                'reason' => $request->json('reason'),
                'id_number' => $request->json('id_number')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification rejected successfully',
                'verification' => [
                    'id' => $verification->id,
                    'customer_name' => $verification->customer_name,
                    'priority_type' => $verification->priority_type,
                    'priority_display' => $verification->priority_display,
                    'status' => 'rejected',
                    'rejected_at' => $verification->verified_at->toISOString(),
                    'rejected_by' => $verification->verified_by,
                    'reason' => $verification->rejection_reason,
                    'id_number' => $verification->id_number
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject verification', [
                'verification_id' => $request->json('verification_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject verification. Please try again.'
            ], 500);
        }
    }

    /**
     * Complete verification (approve priority status)
     */
    public function completeVerification(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'verification_id' => 'required|integer|exists:verifications,id',
            'verified_by' => 'required|string|max:255',
            'id_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = PriorityVerification::findOrFail($request->json('verification_id'));

            if ($verification->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification is not pending'
                ], 400);
            }

            // Mark as verified
            $verification->markAsVerified($request->json('verified_by'), $request->json('id_number'));

            // Update customer priority status if needed
            if ($verification->queue_customer_id && !empty($verification->queue_customer_id)) {
                $customer = Customer::find($verification->queue_customer_id);
                if ($customer) {
                    $customer->update([
                        'priority_type' => $verification->priority_type,
                        'priority_applied_at' => now(),
                        'has_priority_member' => true,
                    ]);
                    
                    Log::info('Customer priority updated', [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'priority_type' => $customer->priority_type
                    ]);
                } else {
                    Log::warning('Customer not found for verification', [
                        'verification_id' => $verification->id,
                        'queue_customer_id' => $verification->queue_customer_id,
                        'customer_name' => $verification->customer_name
                    ]);
                }
            } else {
                Log::info('Verification completed without customer update (no queue_customer_id)', [
                    'verification_id' => $verification->id,
                    'customer_name' => $verification->customer_name,
                    'reason' => 'queue_customer_id is empty or null'
                ]);
            }

            Log::info('Priority verification completed', [
                'verification_id' => $verification->id,
                'verified_by' => $request->json('verified_by'),
                'priority_type' => $verification->priority_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Priority status verified successfully',
                'verification' => [
                    'id' => $verification->id,
                    'status' => $verification->status,
                    'priority_type' => $verification->priority_type,
                    'verified_at' => $verification->verified_at->toISOString(),
                    'verified_by' => $verification->verified_by
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to complete verification', [
                'verification_id' => $request->json('verification_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete verification. Please try again.'
            ], 500);
        }
    }

    /**
     * Get completed verifications for a specific date
     */
    public function getCompletedVerifications(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            
            $verified = PriorityVerification::where('status', 'verified')
                ->whereDate('verified_at', $date)
                ->orderBy('verified_at', 'desc')
                ->get()
                ->map(function ($verification) {
                    return [
                        'id' => $verification->id,
                        'customer_name' => $verification->customer_name,
                        'priority_type' => $verification->priority_type,
                        'priority_display' => $verification->priority_display,
                        'status' => $verification->status,
                        'verified_at' => $verification->verified_at->format('h:i A'),
                        'verified_by' => $verification->verified_by,
                        'id_number' => $verification->id_number ?? ''
                    ];
                });

            $rejected = PriorityVerification::where('status', 'rejected')
                ->whereDate('verified_at', $date)
                ->orderBy('verified_at', 'desc')
                ->get()
                ->map(function ($verification) {
                    return [
                        'id' => $verification->id,
                        'customer_name' => $verification->customer_name,
                        'priority_type' => $verification->priority_type,
                        'priority_display' => $verification->priority_display,
                        'status' => $verification->status,
                        'rejected_at' => $verification->verified_at->format('h:i A'),
                        'rejected_by' => $verification->verified_by,
                        'rejection_reason' => $verification->rejection_reason,
                        'id_number' => $verification->id_number ?? ''
                    ];
                });

            return response()->json([
                'success' => true,
                'verified' => $verified,
                'rejected' => $rejected,
                'date' => $date
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get completed verifications', [
                'error' => $e->getMessage(),
                'date' => $request->get('date')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load completed verifications'
            ], 500);
        }
    }
}

