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
                    'pin' => $verification->pin,
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
                    return [
                        'id' => $verification->id,
                        'customer_name' => $verification->customer_name,
                        'priority_type' => $verification->priority_type,
                        'priority_display' => $verification->priority_display,
                        'status' => $verification->status,
                        'requested_at' => $verification->requested_at->format('M d, Y h:i A'),
                        'time_elapsed' => now()->diffInMinutes($verification->requested_at)
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
     * Verify customer and generate PIN (for staff)
     */
    public function verifyAndGeneratePIN(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:priority_verifications,id',
            'verified_by' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = PriorityVerification::find($request->verification_id);

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

            // Generate PIN (4-digit random number)
            $pin = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            // Mark as verified
            $verification->markAsVerified($request->verified_by, $pin);

            // Update the customer record if it exists
            $customer = Customer::where('name', $verification->customer_name)
                ->where('priority_type', $verification->priority_type)
                ->where('status', 'waiting')
                ->latest()
                ->first();

            if ($customer) {
                $customer->update([
                    'id_verification_status' => 'verified',
                    'id_verification_data' => [
                        'verified_by' => $request->verified_by,
                        'verified_at' => now()->toISOString(),
                        'pin' => $pin,
                        'verification_id' => $verification->id
                    ]
                ]);

                Log::info('Customer record updated with verification', [
                    'customer_id' => $customer->id,
                    'verification_id' => $verification->id
                ]);
            }

            Log::info('Priority verification completed', [
                'verification_id' => $verification->id,
                'customer_name' => $verification->customer_name,
                'pin' => $pin,
                'verified_by' => $request->verified_by
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification completed successfully',
                'verification' => [
                    'id' => $verification->id,
                    'customer_name' => $verification->customer_name,
                    'priority_type' => $verification->priority_type,
                    'pin' => $pin,
                    'status' => 'verified',
                    'verified_at' => $verification->verified_at->toISOString(),
                    'verified_by' => $verification->verified_by
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to verify and generate PIN', [
                'verification_id' => $request->verification_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete verification. Please try again.'
            ], 500);
        }
    }

    /**
     * Reject verification (for staff)
     */
    public function rejectVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|integer|exists:priority_verifications,id',
            'rejected_by' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $verification = PriorityVerification::find($request->verification_id);

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
                'verified_by' => $request->rejected_by,
                'rejection_reason' => $request->reason ?? 'ID verification failed'
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
                        'rejected_by' => $request->rejected_by,
                        'rejected_at' => now()->toISOString(),
                        'reason' => $request->reason ?? 'ID verification failed',
                        'verification_id' => $verification->id
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
                'rejected_by' => $request->rejected_by,
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification rejected successfully',
                'verification' => [
                    'id' => $verification->id,
                    'customer_name' => $verification->customer_name,
                    'priority_type' => $verification->priority_type,
                    'status' => 'rejected',
                    'rejected_at' => $verification->verified_at->toISOString(),
                    'rejected_by' => $verification->verified_by,
                    'reason' => $verification->rejection_reason
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject verification', [
                'verification_id' => $request->verification_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject verification. Please try again.'
            ], 500);
        }
    }
}

