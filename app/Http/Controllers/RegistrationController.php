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
     * Store registration data in session only (no database save until final confirmation)
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
            'priority_type' => 'required_if:is_priority,1|nullable|in:senior,pwd,pregnant',
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

        // STRICT: Additional duplicate contact check during registration (today only)
        if ($request->contact) {
            $formattedContact = str_starts_with($request->contact, '09') ? $request->contact : '09' . $request->contact;
            $existingCustomer = Customer::where('contact_number', $formattedContact)
                ->where('status', 'waiting')
                ->whereDate('registered_at', today()) // Only check today's customers
                ->first();
            
            if ($existingCustomer) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'contact' => ['This contact number is already registered today. Please use a different contact number.']
                    ]
                ], 422);
            }
        }

        try {
            // Determine priority type first
            $priorityType = 'normal';
            if ($request->is_priority == '1' && $request->priority_type) {
                $priorityType = $request->priority_type;
            }
            
            // Determine if it's a group
            $isGroup = $request->party_size > 1;
            $hasPriorityMember = $request->is_priority == '1';
            
            // Format contact number
            $contactNumber = $request->contact ? 
                (str_starts_with($request->contact, '09') ? $request->contact : '09' . $request->contact) : null;
            
            // Store data in session only (no database save yet)
            $registrationData = [
                'name' => trim($request->name),
                'party_size' => $request->party_size,
                'contact_number' => $contactNumber,
                'priority_type' => $priorityType,
                'is_group' => $isGroup,
                'has_priority_member' => $hasPriorityMember,
                'is_priority' => $request->is_priority,
                'priority_type_selected' => $request->priority_type,
                'timestamp' => now()->toISOString(),
                'status' => 'pending_confirmation', // Not saved to database yet
            ];
            
            // Store in session
            session(['registration' => $registrationData]);
            
            // Generate a dynamic queue number for display (using the proper method)
            $nextQueueNumber = Customer::getNextQueueNumber();
            $tempQueueNumber = str_pad($nextQueueNumber, 3, '0', STR_PAD_LEFT);
            $registrationData['temp_queue_number'] = $tempQueueNumber;
            session(['registration' => $registrationData]);
            
            // Calculate estimated wait time for display
            $estimator = new QueueEstimator();
            $estimatedWaitTime = $estimator->calculateWaitTime(
                $request->party_size,
                $priorityType
            );
            
            $registrationData['estimated_wait_minutes'] = $estimatedWaitTime;
            session(['registration' => $registrationData]);
            
            // Store updated registration data in session
            session(['registration' => $registrationData]);

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
                    $redirectUrl = route('kiosk.staffverification') . '?name=' . urlencode($request->name) . '&priority_type=' . $priorityType;
                }
                $isPriority = true;
            } else {
                // Non-priority users go to review
                $redirectUrl = route('kiosk.review-details');
                $isPriority = false;
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration data saved!',
                'data' => [
                    'temp_queue_number' => $tempQueueNumber,
                    'estimated_wait_time' => $estimatedWaitTime,
                    'formatted_wait_time' => $estimator->formatWaitTime($estimatedWaitTime),
                    'priority_type' => $priorityType,
                ],
                'redirect_to' => $redirectUrl,
                'is_priority' => $isPriority,
                'debug_session' => session('registration')
            ]);

        } catch (\Exception $e) {
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
     * Confirm registration and save to database (final step)
     */
    public function confirm(Request $request)
    {
        try {
            // Get registration data from session
            $registrationData = session('registration');
            
            Log::info('Confirm method called', [
                'registration_data' => $registrationData,
                'session_id' => session()->getId()
            ]);
            
            if (!$registrationData) {
                Log::warning('No registration data found for confirmation');
                
                return response()->json([
                    'success' => false,
                    'message' => 'No registration data found. Please start over.'
                ], 400);
            }
            
            // Check if already confirmed
            if ($registrationData['status'] === 'confirmed') {
                Log::info('Registration already confirmed, redirecting to receipt', [
                    'customer_id' => $registrationData['customer_id']
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Registration already confirmed!',
                    'data' => [
                        'customer_id' => $registrationData['customer_id'],
                        'queue_number' => $registrationData['queue_number'],
                        'estimated_wait_time' => $registrationData['estimated_wait_time'],
                        'formatted_wait_time' => (new QueueEstimator())->formatWaitTime($registrationData['estimated_wait_time']),
                        'priority_type' => $registrationData['priority_type'],
                    ],
                    'redirect_to' => route('kiosk.receipt', $registrationData['customer_id'])
                ]);
            }
            
            if ($registrationData['status'] !== 'pending_confirmation') {
                Log::warning('Invalid session status for confirmation', [
                    'status' => $registrationData['status'] ?? 'not_set'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid registration status. Please start over.'
                ], 400);
            }

            DB::beginTransaction();

            // Generate final queue number
            $queueNumber = Customer::getNextQueueNumber();
            
            // Create customer record in database
            $customer = Customer::create([
                'name' => $registrationData['name'],
                'party_size' => $registrationData['party_size'],
                'contact_number' => $registrationData['contact_number'],
                'queue_number' => $queueNumber,
                'priority_type' => $registrationData['priority_type'],
                'is_group' => $registrationData['is_group'],
                'has_priority_member' => $registrationData['has_priority_member'],
                'status' => 'waiting',
                'estimated_wait_minutes' => $registrationData['estimated_wait_minutes'],
                'registered_at' => now(),
            ]);
            
            Log::info('Customer created successfully', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'queue_number' => $customer->queue_number
            ]);
            
            // Ensure customer was created successfully
            if (!$customer || !$customer->id) {
                throw new \Exception('Failed to create customer record');
            }

            // Calculate actual wait time now that customer is in database
            $estimator = new QueueEstimator();
            $actualWaitTime = $estimator->calculateWaitTime(
                $customer->party_size,
                $customer->priority_type,
                $customer->id
            );
            
            // Update with correct wait time
            $customer->update(['estimated_wait_minutes' => $actualWaitTime]);

            // Create initial queue event
            QueueEvent::create([
                'customer_id' => $customer->id,
                'event_type' => 'registered',
                'event_time' => now(),
                'notes' => 'Customer registered via kiosk',
            ]);

            // Handle priority verification if needed
            if ($registrationData['has_priority_member'] && $registrationData['priority_type'] !== 'normal') {
                if ($registrationData['priority_type'] === 'pregnant') {
                    // Create verification request for pregnant customers
                    \App\Models\PriorityVerification::create([
                        'customer_name' => $registrationData['name'],
                        'priority_type' => $registrationData['priority_type'],
                        'status' => 'pending',
                        'requested_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Update session with final customer data
            session([
                'registration' => [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'party_size' => $customer->party_size,
                    'contact_number' => $customer->contact_number,
                    'queue_number' => $customer->queue_number,
                    'priority_type' => $customer->priority_type,
                    'is_priority' => $customer->has_priority_member,
                    'estimated_wait_time' => $actualWaitTime,
                    'status' => 'confirmed', // Mark as confirmed
                    'id_verified' => false,
                ]
            ]);

            $responseData = [
                'success' => true,
                'message' => 'Registration confirmed!',
                'data' => [
                    'customer_id' => $customer->id,
                    'queue_number' => $customer->formatted_queue_number,
                    'estimated_wait_time' => $actualWaitTime,
                    'formatted_wait_time' => $estimator->formatWaitTime($actualWaitTime),
                    'priority_type' => $customer->priority_type,
                ],
                'redirect_to' => route('kiosk.receipt', $customer->id)
            ];
            
            Log::info('Sending confirmation response', [
                'customer_id' => $customer->id,
                'redirect_url' => $responseData['redirect_to']
            ]);
            
            return response()->json($responseData);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Registration confirmation failed', [
                'error' => $e->getMessage(),
                'session_data' => session('registration'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration confirmation failed. Please try again or contact staff for assistance.'
            ], 500);
        }
    }

    /**
     * Cancel registration and clear session data
     */
    public function cancel(Request $request)
    {
        try {
            // Clear registration data from session
            session()->forget('registration');
            
            return response()->json([
                'success' => true,
                'message' => 'Registration cancelled',
                'redirect_to' => route('kiosk.registration')
            ]);

        } catch (\Exception $e) {
            Log::error('Registration cancellation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel registration'
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
        
        // Check if customer already exists in database (created during verification)
        $existingCustomer = null;
        if (isset($registrationData['customer_id']) && $registrationData['customer_id']) {
            $existingCustomer = Customer::find($registrationData['customer_id']);
        }
        
        if ($existingCustomer) {
            // Customer exists in database - use real data
            $customer = $existingCustomer;
            
            // Sync session with database values
            $registrationData['id_verified'] = $customer->id_verification_status === 'verified';
            $registrationData['id_verification_status'] = $customer->id_verification_status ?? 'pending';
            $registrationData['priority_type'] = $customer->priority_type;
            $registrationData['status'] = 'pending_confirmation'; // Still needs final confirmation
            session(['registration' => $registrationData]);
            
            Log::info('Review Details - Using existing customer from database', [
                'customer_id' => $customer->id,
                'id_verified' => $customer->id_verification_status === 'verified',
                'priority_type' => $customer->priority_type
            ]);
        } else {
            // Customer doesn't exist yet - create mock object
            if (!isset($registrationData['status']) || $registrationData['status'] !== 'pending_confirmation') {
                return redirect()->route('kiosk.registration')->with('error', 'Invalid registration state. Please start over.');
            }
            
            $customer = (object) [
                'id' => null,
                'name' => $registrationData['name'],
                'party_size' => $registrationData['party_size'],
                'contact_number' => $registrationData['contact_number'],
                'queue_number' => $registrationData['temp_queue_number'] ?? '001',
                'priority_type' => $registrationData['priority_type'],
                'has_priority_member' => $registrationData['has_priority_member'],
                'estimated_wait_minutes' => $registrationData['estimated_wait_minutes'],
                'id_verified' => $registrationData['id_verified'] ?? false,
                'id_verification_status' => $registrationData['id_verification_status'] ?? 'pending',
            ];
        }
        
        // Check if user skipped priority and update session data
        if (request()->get('skip_priority') && $customer->priority_type !== 'normal') {
            // Update session data to regular status
            $registrationData['priority_type'] = 'normal';
            $registrationData['has_priority_member'] = false;
            $registrationData['is_priority'] = '0';
            $registrationData['id_verified'] = false;
            session(['registration' => $registrationData]);
            
            // Update customer object for display
            $customer->priority_type = 'normal';
            $customer->has_priority_member = false;
        }

        // Handle pregnant customers
        if ($customer->priority_type === 'pregnant' && (!isset($customer->id_verification_status) || $customer->id_verification_status !== 'verified')) {
            $registrationData['id_verified'] = true;
            $registrationData['id_verification_status'] = 'verified';
            session(['registration' => $registrationData]);
            
            if (is_object($customer) && !($customer instanceof Customer)) {
                $customer->id_verified = true;
                $customer->id_verification_status = 'verified';
            }
        }

        // Calculate wait time
        $estimator = new QueueEstimator();
        $formattedWait = $estimator->formatWaitTime($customer->estimated_wait_minutes);
        
        $queueInfo = [
            'customers_ahead' => $existingCustomer ? $estimator->getQueuePosition($customer->id)['position'] : rand(1, 5),
            'wait_time_formatted' => $formattedWait,
            'estimated_table_time' => now()->addMinutes($customer->estimated_wait_minutes)->format('g:i A'),
        ];
        
        Log::info('Review Details - Final customer status', [
            'has_customer_id' => isset($customer->id) && $customer->id,
            'id_verified' => $customer->id_verified ?? ($customer->id_verification_status === 'verified'),
            'id_verification_status' => $customer->id_verification_status ?? 'unknown',
            'priority_type' => $customer->priority_type
        ]);
        
        return view('kiosk.review-details', [
            'customer' => $customer,
            'queueInfo' => $queueInfo,
            'formattedWait' => $formattedWait,
            'isNewCustomer' => !$existingCustomer,
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

            // Check for existing customer with this contact number (STRICT - no duplicates allowed, today only)
            $existingCustomer = Customer::where('contact_number', $formattedContact)
                ->where('status', 'waiting')
                ->whereDate('registered_at', today()) // Only check today's customers
                ->first();

            Log::info('🔍 Database query result (STRICT - today only)', [
                'contact_number' => $formattedContact,
                'found_customer' => $existingCustomer ? $existingCustomer->id : null,
                'customer_status' => $existingCustomer ? $existingCustomer->status : null,
                'enforcement' => 'STRICT - No duplicates allowed (today only)',
                'check_date' => today()->toDateString()
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

    /**
     * Update review details from the review screen
     */
    public function updateReviewDetails(Request $request)
    {
        try {
            $field = $request->input('field');
            $value = $request->input('value');
            
            // Get customer from session
            $registrationData = session('registration');
            if (!$registrationData || !isset($registrationData['customer_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer session not found'
                ], 400);
            }
            
            $customerId = $registrationData['customer_id'];
            
            $customer = Customer::findOrFail($customerId);
            $requiresVerification = false;
            $redirectUrl = null;
            
            switch ($field) {
                case 'name':
                    $customer->name = $value;
                    break;
                    
                case 'party':
                    $customer->party_size = (int)$value;
                    break;
                    
                case 'contact':
                    $customer->contact_number = $value;
                    break;
                    
                case 'priority':
                    $oldPriority = $customer->priority_type;
                    $customer->priority_type = $value;
                    
                    // If changing to a priority type, require verification (except for pregnant)
                    if ($value !== 'normal' && $oldPriority === 'normal') {
                        $customer->has_priority_member = true;
                        if ($value === 'pregnant') {
                            // Pregnant customers don't need ID verification
                            $customer->id_verification_status = 'verified';
                            $requiresVerification = false;
                        } else {
                            // Senior and PWD customers need ID verification
                            $requiresVerification = true;
                            $customer->id_verification_status = 'pending';
                            $redirectUrl = route('kiosk.staffverification', [
                                'name' => $customer->name,
                                'priority_type' => $value
                            ]);
                        }
                    } else if ($value === 'normal' && $oldPriority !== 'normal') {
                        // If changing from priority to normal, update accordingly
                        $customer->has_priority_member = false;
                        $customer->id_verification_status = 'skipped_priority';
                    } else if ($value !== 'normal' && $oldPriority !== 'normal') {
                        // If changing from one priority type to another
                        $customer->has_priority_member = true;
                        if ($value === 'pregnant') {
                            // Pregnant customers don't need ID verification
                            $customer->id_verification_status = 'verified';
                            $requiresVerification = false;
                        } else {
                            // Senior and PWD customers need ID verification
                            $requiresVerification = true;
                            $customer->id_verification_status = 'pending';
                            $redirectUrl = route('kiosk.staffverification', [
                                'name' => $customer->name,
                                'priority_type' => $value
                            ]);
                        }
                    }
                    break;
            }
            
            $customer->save();
            
            // Update session data
            $registration = session('registration', []);
            $registration[$field] = $value;
            session(['registration' => $registration]);
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($field) . ' updated successfully',
                'requires_verification' => $requiresVerification,
                'redirect_url' => $redirectUrl
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update review details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update details. Please try again.'
            ], 500);
        }
    }

    /**
     * Check verification status for priority customers
     */
    public function checkVerificationStatus(Request $request)
    {
        try {
            $registrationData = session('registration');
            
            if (!$registrationData) {
                return response()->json([
                    'success' => false,
                    'id_verified' => false,
                    'id_verification_status' => 'pending'
                ]);
            }
            
            $customerName = $registrationData['name'];
            $priorityType = $registrationData['priority_type'] ?? 'normal';
            
            // Check if customer exists in database and is verified
            $customerId = $registrationData['customer_id'] ?? null;
            if ($customerId) {
                $customer = \App\Models\Customer::find($customerId);
                if ($customer && $customer->id_verification_status === 'verified') {
                    // Update session with database status
                    $registrationData['id_verified'] = true;
                    $registrationData['id_verification_status'] = 'verified';
                    session(['registration' => $registrationData]);
                    
                    Log::info('Session updated with database verification status', [
                        'customer_id' => $customerId,
                        'verification_status' => 'verified'
                    ]);
                }
            }
            
            // Also check PriorityVerification table for session-only customers
            if ($priorityType !== 'normal' && $priorityType !== 'pregnant') {
                $verification = \App\Models\PriorityVerification::where('customer_name', $customerName)
                    ->where('priority_type', $priorityType)
                    ->where('status', 'verified')
                    ->latest()
                    ->first();
                
                if ($verification) {
                    // Update session with verification status
                    $registrationData['id_verified'] = true;
                    $registrationData['id_verification_status'] = 'verified';
                    session(['registration' => $registrationData]);
                    
                    Log::info('Session updated with PriorityVerification status', [
                        'customer_name' => $customerName,
                        'priority_type' => $priorityType,
                        'verification_id' => $verification->id,
                        'verification_status' => 'verified'
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'id_verified' => $registrationData['id_verified'] ?? false,
                'id_verification_status' => $registrationData['id_verification_status'] ?? 'pending',
                'priority_type' => $registrationData['priority_type'] ?? 'normal'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to check verification status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'id_verified' => false,
                'id_verification_status' => 'pending'
            ], 500);
        }
    }

    /**
     * Update verification status in session
     */
    public function updateVerificationSession(Request $request)
    {
        try {
            $registrationData = session('registration');
            
            if (!$registrationData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No registration data found'
                ], 400);
            }
            
            // Update session with verified status
            $registrationData['id_verified'] = true;
            $registrationData['id_verification_status'] = 'verified';
            
            session(['registration' => $registrationData]);
            
            Log::info('Session updated with verification status', [
                'priority_type' => $request->priority_type,
                'verified' => true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Session updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update verification session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update session'
            ], 500);
        }
    }

}
