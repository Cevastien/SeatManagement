<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\QueueEvent;
use App\Services\QueueEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Show the registration form (Step 1 of kiosk process)
     */
    public function show(Request $request)
    {
        $editField = $request->get('edit');
        $existingData = [];
        
        // If editing, get existing data from session
        if ($editField && session('registration')) {
            $existingData = session('registration');
        }
        
        return view('kiosk.registration', compact('editField', 'existingData'));
    }

    /**
     * Store registration data and proceed to next step
     */
    public function store(Request $request)
    {
        // Validate the form data
        // Get dynamic party size limits from settings
        $partySizeLimits = \App\Models\Setting::getPartySizeLimits();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2',
            'party_size' => "required|integer|min:{$partySizeLimits['min']}|max:{$partySizeLimits['max']}",
            'contact' => 'nullable|string|regex:/^[0-9]{1,11}$/',
            'is_priority' => 'required|in:0,1',
            'priority_type' => 'required_if:is_priority,1|in:senior,pwd,pregnant',
        ], [
            'name.required' => 'Please enter your name.',
            'name.min' => 'Name must be at least 2 characters.',
            'party_size.required' => 'Please specify party size.',
            'party_size.min' => "Party size must be at least {$partySizeLimits['min']}.",
            'party_size.max' => "Party size cannot exceed {$partySizeLimits['max']} people.",
            'contact.regex' => 'Please enter only numbers for the mobile number.',
            'is_priority.required' => 'Please answer the priority check question.',
            'priority_type.required_if' => 'Please select a priority type.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate queue number
            $queueNumber = Customer::getNextQueueNumber();
            
            // Determine priority type first
            $priorityType = 'normal';
            if ($request->is_priority == '1' && $request->priority_type) {
                $priorityType = $request->priority_type;
            }
            
            // Determine if it's a group
            $isGroup = $request->party_size > 1;
            $hasPriorityMember = $request->is_priority == '1';
            
            // Create customer record FIRST with temporary wait time
            $customer = Customer::create([
                'name' => trim($request->name),
                'party_size' => $request->party_size,
                'contact_number' => $request->contact ? 
                    (str_starts_with($request->contact, '09') ? $request->contact : '09' . $request->contact) : null,
                'queue_number' => $queueNumber,
                'priority_type' => $priorityType,
                'is_group' => $isGroup,
                'has_priority_member' => $hasPriorityMember,
                'status' => 'waiting',
                'estimated_wait_minutes' => 0, // Temporary value
                'registered_at' => now(),
            ]);

            // NOW calculate their actual wait time (they're in the database)
            $estimator = new QueueEstimator();
            $estimatedWaitTime = $estimator->calculateWaitTime(
                $customer->party_size,
                $customer->priority_type,
                $customer->id
            );
            
            // Update with correct wait time
            $customer->update(['estimated_wait_minutes' => $estimatedWaitTime]);

            // Create initial queue event
            QueueEvent::create([
                'customer_id' => $customer->id,
                'event_type' => 'registered',
                'event_time' => now(),
                'notes' => 'Customer registered via kiosk',
            ]);

            DB::commit();

            // Store registration data in session for review
            session([
                'registration' => [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'party_size' => $customer->party_size,
                    'contact_number' => $customer->contact_number,
                    'queue_number' => $customer->queue_number,
                    'priority_type' => $priorityType,
                    'is_priority' => $hasPriorityMember,
                    'estimated_wait_time' => $estimatedWaitTime,
                    'id_verified' => false,
                ]
            ]);

            // Determine redirect based on priority status
            if ($hasPriorityMember && $priorityType !== 'normal') {
                if ($priorityType === 'pregnant') {
                    // Create verification request for pregnant customers (for staff dashboard)
                    \App\Models\PriorityVerification::create([
                        'customer_name' => $request->name,
                        'priority_type' => $priorityType,
                        'status' => 'pending',
                        'requested_at' => now(),
                    ]);
                    
                    // Pregnant customers go to review details like other priority customers
                    // They will get automatic verification without ID check
                    $redirectUrl = route('kiosk.review-details');
                } else {
                    // Senior/PWD customers need ID verification
                    $redirectUrl = route('kiosk.id-scanner') . '?name=' . urlencode($request->name) . '&priority_type=' . $priorityType;
                }
                $isPriority = true;
            } else {
                // Non-priority users go to review
                $redirectUrl = route('kiosk.review-details');
                $isPriority = false;
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'data' => [
                    'customer_id' => $customer->id,
                    'queue_number' => $customer->formatted_queue_number,
                    'estimated_wait_time' => $estimatedWaitTime,
                    'formatted_wait_time' => $estimator->formatWaitTime($estimatedWaitTime),
                    'priority_type' => $priorityType,
                ],
                'redirect_to' => $redirectUrl,
                'is_priority' => $isPriority,
                'debug_session' => session('registration')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again or contact staff for assistance.'
            ], 500);
        }
    }

    /**
     * Show review details page (Step 2 of kiosk process)
     */
    public function reviewDetails()
    {
        // Check if registration data exists in session
        if (!session('registration')) {
            return redirect()->route('kiosk.registration')->with('error', 'No registration data found. Please start over.');
        }

        $registrationData = session('registration');
        $customer = Customer::find($registrationData['customer_id']);
        
        if (!$customer) {
            return redirect()->route('kiosk.registration')->with('error', 'Customer not found. Please start over.');
        }

        // Check if user skipped priority and update database dynamically
        if (request()->get('skip_priority') && $customer->priority_type !== 'normal') {
            try {
                DB::beginTransaction();
                
                // Update customer to regular status
                $customer->update([
                    'priority_type' => 'normal',
                    'has_priority_member' => false,
                    'id_verification_status' => 'skipped_priority'
                ]);

                // Update session data
                $registrationData['priority_type'] = 'normal';
                $registrationData['is_priority'] = false;
                $registrationData['id_verified'] = false;
                session(['registration' => $registrationData]);

                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to update customer priority status', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Handle pregnant customers - they get automatic verification
        if ($customer->priority_type === 'pregnant') {
            // Find the pending verification request for this customer
            $verification = \App\Models\PriorityVerification::where('customer_name', $customer->name)
                ->where('priority_type', 'pregnant')
                ->where('status', 'pending')
                ->latest()
                ->first();
                
            if ($verification) {
                // Automatically verify pregnant customers
                $verification->update([
                    'status' => 'verified',
                    'pin' => null, // No PIN needed for pregnant customers
                    'verified_at' => now(),
                    'verified_by' => 'Automatic (Pregnant Priority)',
                ]);
                
                // Update customer status to verified
                $customer->update([
                    'id_verification_status' => 'verified',
                    'id_verification_data' => [
                        'verified_by' => 'Automatic (Pregnant Priority)',
                        'verified_at' => now()->toISOString(),
                        'verification_type' => 'pregnant_priority'
                    ]
                ]);
            }
        }

        // Calculate actual wait time based on current queue
        $estimator = new QueueEstimator();
        $queueInfo = $estimator->getQueuePosition($customer->id);
        $formattedWait = $estimator->formatWaitTime($customer->estimated_wait_minutes);
        
        return view('kiosk.review-details', [
            'customer' => $customer->fresh(), // Get fresh data from database
            'queueInfo' => $queueInfo,
            'formattedWait' => $formattedWait,
            'isNewCustomer' => true, // Flag for frontend to use customer ID API
        ]);
    }

    /**
     * Handle ID verification for priority customers
     */
    public function verifyId(Request $request)
    {
        // Validate ID verification data
        $validator = Validator::make($request->all(), [
            'id_data' => 'required|json',
            'verification_status' => 'required|in:verified,pending,failed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $registrationData = session('registration');
            
            if (!$registrationData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No registration data found. Please start over.'
                ], 400);
            }

            // Update customer with ID verification data
            $customer = Customer::find($registrationData['customer_id']);
            if ($customer) {
                $customer->update([
                    'id_verification_status' => $request->verification_status,
                    'id_verification_data' => $request->id_data,
                ]);

                // Update session data
                $registrationData['id_verified'] = $request->verification_status === 'verified';
                session(['registration' => $registrationData]);
            }

            return response()->json([
                'success' => true,
                'message' => 'ID verification completed',
                'verified' => $request->verification_status === 'verified',
                'redirect_to' => route('kiosk.review-details')
            ]);

        } catch (\Exception $e) {
            Log::error('ID verification failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ID verification failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Confirm final registration and generate receipt
     */
    public function confirm(Request $request)
    {
        try {
            $registrationData = session('registration');
            
            if (!$registrationData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No registration data found. Please start over.'
                ], 400);
            }

            // Update customer status to confirmed
            $customer = Customer::find($registrationData['customer_id']);
            if ($customer) {
                $customer->update([
                    'status' => 'waiting',
                ]);

                // Create confirmation event
                QueueEvent::create([
                    'customer_id' => $customer->id,
                    'event_type' => 'completed',
                    'event_time' => now(),
                    'notes' => 'Registration confirmed and receipt generated',
                ]);
            }

            // Store customer ID in session for receipt page
            session(['confirmed_customer_id' => $customer->id]);

            // Clear registration session data
            session()->forget('registration');

            // Generate receipt URL with customer data
            $estimator = new QueueEstimator();
            $formattedWaitTime = $estimator->formatWaitTime($registrationData['estimated_wait_time']);
            $receiptUrl = route('kiosk.receipt', $customer->id);

            return response()->json([
                'success' => true,
                'message' => 'Registration confirmed successfully',
                'redirect_url' => $receiptUrl,
                'customer_data' => [
                    'name' => $registrationData['name'],
                    'queue_number' => $registrationData['queue_number'],
                    'estimated_wait_time' => $registrationData['estimated_wait_time'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Registration confirmation failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'registration_data' => session('registration'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Confirmation failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Check for duplicate contact numbers
     */
    public function checkDuplicateContact(Request $request)
    {
        try {
            Log::info('🔍 Duplicate contact check started', [
                'request_data' => $request->all(),
                'ip' => $request->ip()
            ]);

            $validator = Validator::make($request->all(), [
                'contact' => 'required|string|regex:/^[0-9]{1,11}$/'
            ]);

            if ($validator->fails()) {
                Log::warning('❌ Invalid contact format', ['errors' => $validator->errors()]);
                return response()->json([
                    'is_duplicate' => false,
                    'message' => 'Invalid contact number format'
                ], 400);
            }

            $contact = $request->input('contact');
            $formattedContact = str_starts_with($contact, '09') ? $contact : '09' . $contact;
            
            Log::info('📞 Checking for contact number', [
                'input_contact' => $contact,
                'formatted_contact' => $formattedContact
            ]);

            // Check for existing customer with this contact number
            $existingCustomer = Customer::where('contact_number', $formattedContact)
                ->where('status', 'waiting')
                ->first();

            Log::info('🔍 Database query result', [
                'contact_number' => $formattedContact,
                'found_customer' => $existingCustomer ? $existingCustomer->id : null,
                'customer_status' => $existingCustomer ? $existingCustomer->status : null
            ]);

            if ($existingCustomer) {
                Log::info('⚠️ Duplicate customer found', [
                    'customer_id' => $existingCustomer->id,
                    'customer_name' => $existingCustomer->name,
                    'contact_number' => $existingCustomer->contact_number
                ]);

                // Calculate queue position and wait time using dynamic estimator
                $estimator = new QueueEstimator();
                $queuePosition = $estimator->getQueuePosition($existingCustomer->id);
                $estimatedWaitTime = $estimator->calculateWaitTime($existingCustomer->party_size, $existingCustomer->priority_type);

                return response()->json([
                    'is_duplicate' => true,
                    'customer' => [
                        'id' => $existingCustomer->id,
                        'name' => $existingCustomer->name,
                        'contact_number' => $existingCustomer->contact_number,
                        'created_at' => $existingCustomer->created_at,
                        'registered_at' => $existingCustomer->registered_at
                    ],
                    'queue_position' => $queuePosition['position'],
                    'estimated_wait_minutes' => $estimatedWaitTime,
                    'formatted_wait_time' => $estimator->formatWaitTime($estimatedWaitTime),
                    'queue_info' => [
                        'total_active_customers' => $queuePosition['total_in_queue'],
                        'priority_customers_ahead' => $queuePosition['priority_ahead'],
                        'normal_customers_ahead' => $queuePosition['normal_ahead']
                    ],
                    'message' => 'This contact number is already in the queue'
                ]);
            }

            Log::info('✅ No duplicate found - contact number is available');
            return response()->json([
                'is_duplicate' => false,
                'message' => 'Contact number is available'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking duplicate contact: ' . $e->getMessage());
            return response()->json([
                'is_duplicate' => false,
                'message' => 'Error checking contact number'
            ], 500);
        }
    }

    /**
     * Update wait times for all waiting customers when queue changes
     */
    public function updateAllWaitTimes()
    {
        try {
            $estimator = new QueueEstimator();
            $result = $estimator->updateAllWaitTimes();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 
                    'Wait times updated successfully' : 
                    'Failed to update wait times',
                'updated_count' => $result['updated_count'] ?? 0,
                'stats' => $result['stats'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update all wait times: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wait times'
            ], 500);
        }
    }
    
    /**
     * Get current queue statistics
     */
    public function getQueueStats()
    {
        try {
            $estimator = new QueueEstimator();
            $stats = $estimator->getQueueStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get queue stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get queue statistics'
            ], 500);
        }
    }

}
