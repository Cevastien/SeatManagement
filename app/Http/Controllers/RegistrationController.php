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
        
        // Check if coming from verification and has session data
        if (session('registration') && isset(session('registration')['verification_id'])) {
            $existingData = session('registration');
        }
        
        return view('kiosk.registration', compact('editField', 'existingData'));
    }

    /**
     * Store registration data in session only (no database save until final confirmation)
     */
    public function store(Request $request)
    {
        // Sanitize and validate input data
        $sanitizedData = [
            'name' => strip_tags(trim($request->input('name', ''))),
            'party_size' => (int) $request->input('party_size', 1),
            'contact' => preg_replace('/[^0-9]/', '', $request->input('contact', '')),
            'is_priority' => $request->input('is_priority', '0'),
            'priority_type' => $request->input('priority_type', 'normal'),
        ];
        
        Log::info('Registration form data received', [
            'raw_contact' => $request->input('contact'),
            'sanitized_contact' => $sanitizedData['contact'],
            'all_sanitized_data' => $sanitizedData,
            'all_request_data' => $request->all(),
            'contact_field_exists' => $request->has('contact'),
            'contact_field_empty' => empty($request->input('contact'))
        ]);

        // Validate the sanitized form data
        // Get dynamic party size limits from settings
        $partySizeLimits = \App\Models\Setting::getPartySizeLimits();
        
        $validator = Validator::make($sanitizedData, [
            'name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\-\.\']+$/',
            'party_size' => "required|integer|min:{$partySizeLimits['min']}|max:{$partySizeLimits['max']}",
            'contact' => 'nullable|string|regex:/^[0-9]{9}$/',
            'is_priority' => 'required|in:0,1',
            'priority_type' => 'required|in:senior,pwd,pregnant,normal',
        ], [
            'name.required' => 'Please enter your name.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, periods, and apostrophes.',
            'party_size.required' => 'Please specify party size.',
            'party_size.min' => "Party size must be at least {$partySizeLimits['min']}.",
            'party_size.max' => "Party size cannot exceed {$partySizeLimits['max']} people.",
            'contact.regex' => 'Please enter only numbers for the mobile number.',
            'is_priority.required' => 'Please answer the priority check question.',
            'priority_type.required' => 'Please select a priority type.',
            'priority_type.in' => 'Invalid priority type selected.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // STRICT: Additional duplicate contact check during registration (today only)
        if ($sanitizedData['contact']) {
            $formattedContact = str_starts_with($sanitizedData['contact'], '09') ? $sanitizedData['contact'] : '09' . $sanitizedData['contact'];
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
            if ($sanitizedData['is_priority'] == '1' && $sanitizedData['priority_type']) {
                $priorityType = $sanitizedData['priority_type'];
            }
            
            // Determine if it's a group
            $isGroup = $sanitizedData['party_size'] > 1;
            $hasPriorityMember = $sanitizedData['is_priority'] == '1';
            
            // Format contact number
            $contactNumber = $sanitizedData['contact'] ? 
                (str_starts_with($sanitizedData['contact'], '09') ? $sanitizedData['contact'] : '09' . $sanitizedData['contact']) : null;
                
            Log::info('Contact number formatting', [
                'original_contact' => $sanitizedData['contact'],
                'formatted_contact' => $contactNumber,
                'will_be_encrypted' => $contactNumber ? 'yes' : 'no'
            ]);
            
            // Encrypt sensitive data before storing in session
            $registrationData = [
                'name' => encrypt($sanitizedData['name']), // Encrypt sensitive data
                'party_size' => $sanitizedData['party_size'],
                'contact_number' => $contactNumber ? encrypt($contactNumber) : null, // Encrypt contact
                'priority_type' => $priorityType,
                'is_group' => $isGroup,
                'has_priority_member' => $hasPriorityMember,
                'is_priority' => $sanitizedData['is_priority'],
                'priority_type_selected' => $sanitizedData['priority_type'],
                'timestamp' => now()->toISOString(),
                'status' => 'pending_confirmation', // Not saved to database yet
                'session_id' => session()->getId(), // Track session for security
            ];
            
            // Regenerate session ID for security
            session()->regenerate();
            
            // Store encrypted data in session
            session(['registration' => $registrationData]);
            
            // Generate a dynamic queue number for display (using the proper method)
            $nextQueueNumber = Customer::getNextQueueNumber($priorityType);
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
                    $redirectUrl = route('kiosk.staffverification') . '?name=' . urlencode($request->name) . '&priority_type=' . $priorityType . '&party_size=' . $sanitizedData['party_size'];
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
            
            // Check if customer already exists in database (from verification)
            $existingCustomer = null;
            if (isset($registrationData['customer_id']) && $registrationData['customer_id']) {
                $existingCustomer = Customer::find($registrationData['customer_id']);
            }
            
            // If customer exists and has priority (verified), just redirect to receipt
            if ($existingCustomer && $existingCustomer->has_priority_member && $existingCustomer->status === 'waiting') {
                Log::info('Verified customer already exists, redirecting to receipt', [
                    'customer_id' => $existingCustomer->id,
                    'queue_number' => $existingCustomer->queue_number
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Registration already confirmed!',
                    'data' => [
                        'customer_id' => $existingCustomer->id,
                        'queue_number' => $existingCustomer->queue_number,
                        'estimated_wait_time' => $existingCustomer->estimated_wait_minutes,
                        'formatted_wait_time' => (new QueueEstimator())->formatWaitTime($existingCustomer->estimated_wait_minutes),
                        'priority_type' => $existingCustomer->priority_type,
                    ],
                    'redirect_to' => route('kiosk.receipt', $existingCustomer->id)
                ]);
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

            // Check if customer already exists (from verification)
            if ($existingCustomer) {
                // Update existing customer with any new data from session
                $decryptedContact = $registrationData['contact_number'] ? decrypt($registrationData['contact_number']) : null;
                
                $existingCustomer->update([
                    'party_size' => $registrationData['party_size'],
                    'contact_number' => $decryptedContact,
                    'status' => 'waiting', // Keep as waiting status
                    'last_updated_at' => now(),
                ]);
                
                $customer = $existingCustomer;
                
                Log::info('Updated existing verified customer', [
                    'customer_id' => $customer->id,
                    'queue_number' => $customer->queue_number,
                    'status' => $customer->status
                ]);
            } else {
                // Create new customer record using the reserved queue number
                $queueNumber = $registrationData['reserved_queue_number'] ?? Customer::getNextQueueNumber($registrationData['priority_type'] ?? 'normal');
                
                Log::info('Using reserved queue number for customer creation', [
                    'reserved_queue_number' => $registrationData['reserved_queue_number'] ?? 'none',
                    'final_queue_number' => $queueNumber,
                    'priority_type' => $registrationData['priority_type']
                ]);
                
                // Decrypt sensitive data from session
                $decryptedName = decrypt($registrationData['name']);
                $decryptedContact = $registrationData['contact_number'] ? decrypt($registrationData['contact_number']) : null;
                
                $customer = Customer::create([
                    'name' => $decryptedName,
                    'party_size' => $registrationData['party_size'],
                    'contact_number' => $decryptedContact,
                    'queue_number' => $queueNumber,
                    'priority_type' => $registrationData['priority_type'],
                    'is_group' => $registrationData['is_group'],
                    'has_priority_member' => $registrationData['has_priority_member'],
                    'status' => 'waiting',
                    'estimated_wait_minutes' => $registrationData['estimated_wait_minutes'],
                    'registered_at' => now(),
                    'last_updated_at' => now(),
                ]);
                
                Log::info('Created new customer', [
                    'customer_id' => $customer->id,
                    'queue_number' => $customer->queue_number
                ]);
            }
            
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

            // Mark registration as confirmed
            $customer->markRegistrationConfirmed();

            // Create initial queue event
            QueueEvent::create([
                'queue_customer_id' => $customer->id,
                'event_type' => 'registered',
                'event_time' => now(),
                'notes' => 'Customer registered via kiosk',
            ]);

            // Handle priority verification if needed
            if ($registrationData['has_priority_member'] && $registrationData['priority_type'] !== 'normal') {
                if ($registrationData['priority_type'] === 'pregnant') {
                    // Check if verification already exists and is verified
                    $existingVerification = \App\Models\PriorityVerification::where('customer_name', $registrationData['name'])
                        ->where('priority_type', $registrationData['priority_type'])
                        ->where('status', 'verified')
                        ->latest()
                        ->first();
                    
                    if (!$existingVerification) {
                        // Only create verification request if none exists
                        \App\Models\PriorityVerification::create([
                            'queue_customer_id' => $customer->id,
                            'customer_name' => $registrationData['name'],
                            'priority_type' => $registrationData['priority_type'],
                            'status' => 'pending',
                            'requested_at' => now(),
                        ]);
                    } else {
                        // Link existing verification to new customer
                        $existingVerification->update(['queue_customer_id' => $customer->id]);
                    }
                }
                
                // Mark priority as applied
                $customer->markPriorityApplied($registrationData['priority_type'], $registrationData['has_priority_member']);
            }

            DB::commit();

            // Check if customer has verified priority verification
            $isVerified = false;
            if ($customer->priority_type !== 'normal') {
                $verification = \App\Models\PriorityVerification::where('customer_name', $customer->name)
                    ->where('priority_type', $customer->priority_type)
                    ->where('status', 'verified')
                    ->latest()
                    ->first();
                $isVerified = $verification ? true : false;
            }

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
                    'id_verified' => $isVerified,
                    'id_verification_status' => $isVerified ? 'verified' : 'pending',
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
    public function reviewDetails(Request $request)
    {
        // Check if coming from rejected verification (skip_priority=1)
        if ($request->has('skip_priority') && $request->get('skip_priority') == '1') {
            // Customer skipped priority verification - treat as regular customer
            if (!session('registration')) {
                return redirect()->route('kiosk.registration')->with('error', 'No registration data found. Please start over.');
            }
            
            $registrationData = session('registration');
            
            // Update the registration data to reflect that priority was skipped
            $registrationData['priority_type'] = 'normal';
            $registrationData['has_priority_member'] = false;
            $registrationData['is_group'] = ($registrationData['party_size'] ?? 1) > 1;
            $registrationData['status'] = 'pending_confirmation';
            
            session(['registration' => $registrationData]);
            
            // Mark any pending verification as rejected
            $customerName = $registrationData['name'] ? decrypt($registrationData['name']) : null;
            if ($customerName) {
                $pendingVerification = \App\Models\PriorityVerification::where('customer_name', $customerName)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
                    
                if ($pendingVerification) {
                    $pendingVerification->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejected_by' => 'customer_skipped',
                        'rejection_reason' => 'Customer chose to skip priority verification'
                    ]);
                    
                    Log::info('Marked pending verification as rejected due to customer skipping', [
                        'verification_id' => $pendingVerification->id,
                        'customer_name' => $customerName,
                        'priority_type' => $pendingVerification->priority_type
                    ]);
                }
            }
            
            Log::info('Customer skipped priority verification', [
                'customer_name' => $customerName,
                'party_size' => $registrationData['party_size'] ?? 1,
                'priority_type' => 'normal'
            ]);
            
            // Continue with normal flow
            $existingCustomer = null;
            if (isset($registrationData['customer_id']) && $registrationData['customer_id']) {
                $existingCustomer = Customer::find($registrationData['customer_id']);
                
                // Update existing customer record to reflect normal priority
                if ($existingCustomer && $existingCustomer->priority_type !== 'normal') {
                    $existingCustomer->update([
                        'priority_type' => 'normal',
                        'has_priority_member' => false,
                        'is_group' => ($registrationData['party_size'] ?? 1) > 1,
                        'last_updated_at' => now(),
                    ]);
                    
                    Log::info('Updated existing customer to normal priority after skipping verification', [
                        'customer_id' => $existingCustomer->id,
                        'customer_name' => $existingCustomer->name,
                        'old_priority_type' => $registrationData['priority_type'] ?? 'unknown',
                        'new_priority_type' => 'normal'
                    ]);
                }
            }
        }
        // Check if coming from verification with URL parameters
        elseif ($request->has('name') && $request->has('priority_type') && $request->has('verified')) {
            $customerName = $request->get('name');
            $priorityType = $request->get('priority_type');
            $partySize = $request->get('party_size', 1);
            
            // Find the verified customer by checking the verifications table
            $verification = \App\Models\PriorityVerification::where('customer_name', $customerName)
                ->where('priority_type', $priorityType)
                ->where('status', 'verified')
                ->latest()
                ->first();
            
            if ($verification) {
                // Find the customer record (may or may not exist yet)
                $customer = Customer::where('name', $customerName)
                    ->where('priority_type', $priorityType)
                    ->latest()
                    ->first();
            } else {
                $customer = null;
            }
            
            if ($verification) {
                if ($customer) {
                    // Customer exists in database - use real data
                    $existingCustomer = $customer;
                    
                    // Update party size if it's different from URL parameter
                    if ($customer->party_size != $partySize) {
                        $customer->update([
                            'party_size' => $partySize,
                            'is_group' => $partySize > 1,
                            'last_updated_at' => now(),
                        ]);
                        
                        Log::info('Updated customer party size from URL parameter', [
                            'customer_id' => $customer->id,
                            'old_party_size' => $customer->party_size,
                            'new_party_size' => $partySize
                        ]);
                    }
                    
                    Log::info('Found verified customer in database', [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'queue_number' => $customer->queue_number,
                        'verification_id' => $verification->id,
                        'party_size' => $customer->party_size
                    ]);
                } else {
                    // Verification exists but customer record doesn't - create it now
                    // Check if there's contact number in session data
                    $contactNumber = null;
                    $sessionData = session('registration');
                    if ($sessionData && isset($sessionData['contact_number']) && $sessionData['contact_number']) {
                        try {
                            $contactNumber = decrypt($sessionData['contact_number']);
                        } catch (Exception $e) {
                            Log::warning('Failed to decrypt contact number from session', [
                                'error' => $e->getMessage(),
                                'customer_name' => $customerName
                            ]);
                        }
                    }
                    
                    $customer = Customer::create([
                        'name' => $customerName,
                        'party_size' => $partySize,
                        'contact_number' => $contactNumber,
                        'queue_number' => Customer::getNextQueueNumber($priorityType),
                        'priority_type' => $priorityType,
                        'is_group' => $partySize > 1,
                        'has_priority_member' => true,
                        'status' => 'waiting',
                        'estimated_wait_minutes' => 20,
                        'registered_at' => now(),
                        'priority_applied_at' => now(),
                        'last_updated_at' => now(),
                    ]);
                    
                    $existingCustomer = $customer;
                    
                    Log::info('Created customer record from verified verification', [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'queue_number' => $customer->queue_number,
                        'verification_id' => $verification->id,
                        'priority_type' => $priorityType,
                        'contact_number' => $contactNumber ? 'provided' : 'not_provided'
                    ]);
                }
            } else {
                return redirect()->route('kiosk.registration')->with('error', 'Verification not found or not completed. Please start over.');
            }
        } else {
            // Check if registration data exists in session (for non-verified customers)
            if (!session('registration')) {
                return redirect()->route('kiosk.registration')->with('error', 'No registration data found. Please start over.');
            }

            $registrationData = session('registration');
            
            // Check if customer already exists in database (created during verification)
            $existingCustomer = null;
            if (isset($registrationData['customer_id']) && $registrationData['customer_id']) {
                $existingCustomer = Customer::find($registrationData['customer_id']);
            }
        }
        
        if ($existingCustomer) {
            // Customer exists in database - use real data
            $customer = $existingCustomer;
            
            // Format contact number with 09 prefix if needed
            if ($customer->contact_number) {
                $customer->contact_number = str_starts_with($customer->contact_number, '09') ? 
                    $customer->contact_number : '09' . $customer->contact_number;
            }
            
            Log::info('Existing customer contact number processing', [
                'customer_id' => $customer->id,
                'contact_number_raw' => $customer->contact_number,
                'contact_number_formatted' => $customer->contact_number
            ]);
            
            // Create session data for verified customer (if not already exists)
            if (!session('registration')) {
                $registrationData = [
                    'customer_id' => $customer->id,
                    'name' => encrypt($customer->name),
                    'party_size' => $customer->party_size ?? 1, // Default to 1 if null
                    'contact_number' => $customer->contact_number ? encrypt($customer->contact_number) : null,
                    'priority_type' => $customer->priority_type,
                    'has_priority_member' => $customer->has_priority_member,
                    'is_group' => $customer->is_group,
                    'id_verified' => $customer->id_verification_status === 'verified',
                    'id_verification_status' => $customer->id_verification_status ?? 'pending',
                    'status' => 'pending_confirmation',
                    'estimated_wait_minutes' => $customer->estimated_wait_minutes,
                ];
                session(['registration' => $registrationData]);
            } else {
                // Update existing session data with database values
                $registrationData = session('registration');
                $registrationData['customer_id'] = $customer->id;
                $registrationData['id_verified'] = $customer->id_verification_status === 'verified';
                $registrationData['id_verification_status'] = $customer->id_verification_status ?? 'pending';
                $registrationData['priority_type'] = $customer->priority_type;
                $registrationData['status'] = 'pending_confirmation';
                session(['registration' => $registrationData]);
            }
            
            Log::info('Review Details - Using existing customer from database', [
                'customer_id' => $customer->id,
                'queue_number' => $customer->queue_number,
                'id_verified' => $customer->id_verification_status === 'verified',
                'priority_type' => $customer->priority_type
            ]);
        } else {
            // Customer doesn't exist yet - create mock object
            if (!isset($registrationData['status']) || $registrationData['status'] !== 'pending_confirmation') {
                return redirect()->route('kiosk.registration')->with('error', 'Invalid registration state. Please start over.');
            }
            
            // Decrypt sensitive data from session for display
            $decryptedName = decrypt($registrationData['name']);
            $decryptedContact = '';
            
            // Safely decrypt contact number if it exists and is not null
            if (!empty($registrationData['contact_number']) && $registrationData['contact_number'] !== null) {
                try {
                    $decryptedContact = decrypt($registrationData['contact_number']);
                } catch (\Exception $e) {
                    Log::warning('Failed to decrypt contact number', [
                        'error' => $e->getMessage(),
                        'contact_encrypted' => $registrationData['contact_number']
                    ]);
                    $decryptedContact = '';
                }
            }
            
            // Check if coming from verification and missing required data
            // For verified customers, contact number is optional
            if (isset($registrationData['verification_id']) && !isset($registrationData['party_size'])) {
                // Redirect to registration to complete missing information
                return redirect()->route('kiosk.registration')->with('message', 'Please complete your registration information to continue.');
            }
            
            // Format contact number with 09 prefix if needed
            $formattedContact = $decryptedContact ? 
                (str_starts_with($decryptedContact, '09') ? $decryptedContact : '09' . $decryptedContact) : null;
            
            Log::info('Contact number processing in reviewDetails', [
                'has_contact_in_session' => !empty($registrationData['contact_number']),
                'contact_encrypted' => $registrationData['contact_number'],
                'contact_decrypted' => $decryptedContact,
                'contact_formatted' => $formattedContact
            ]);
            
            // If customer exists in database, use that customer directly
            if ($existingCustomer) {
                $customer = $existingCustomer;
                
                // Update contact number if different
                if ($formattedContact && $customer->contact_number !== $formattedContact) {
                    $customer->contact_number = $formattedContact;
                    $customer->save();
                }
            } else {
                // Customer doesn't exist yet - RESERVE the queue number to prevent timing issues
                $nextQueueNumber = Customer::getNextQueueNumber($registrationData['priority_type']);
                
                // Store the reserved queue number in session to use during confirmation
                $registrationData['reserved_queue_number'] = $nextQueueNumber;
                session(['registration' => $registrationData]);
                
                Log::info('Reserved queue number for review-details', [
                    'reserved_queue_number' => $nextQueueNumber,
                    'priority_type' => $registrationData['priority_type'],
                    'customer_name' => $decryptedName
                ]);
                
                $customer = (object) [
                    'id' => null,
                    'name' => $decryptedName,
                    'party_size' => $registrationData['party_size'],
                    'contact_number' => $formattedContact,
                    'queue_number' => $nextQueueNumber, // Use reserved queue number
                    'priority_type' => $registrationData['priority_type'],
                    'has_priority_member' => $registrationData['has_priority_member'],
                    'estimated_wait_minutes' => $registrationData['estimated_wait_minutes'],
                    'id_verified' => $registrationData['id_verified'] ?? false,
                    'id_verification_status' => $registrationData['id_verification_status'] ?? 'pending',
                ];
            }
        }
        
        // Check if user skipped priority and update session data
        if (request()->get('skip_priority') && $customer->priority_type !== 'normal') {
            // Update session data to regular status
            $registrationData['priority_type'] = 'normal';
            
            // Recalculate queue number for normal priority
            $customer->queue_number = Customer::getNextQueueNumber('normal');
            $customer->priority_type = 'normal';
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
            
            // Update customer record in database if it exists
            if (isset($customer->id) && $customer->id) {
                $customer->update([
                    'id_verification_status' => 'verified',
                    'last_updated_at' => now(),
                ]);
                
                Log::info('Updated pregnant customer verification status in database', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'verification_status' => 'verified'
                ]);
            }
            
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
            'customer_id' => $customer->id ?? 'null',
            'customer_name' => $customer->name ?? 'null',
            'queue_number' => $customer->queue_number ?? 'null',
            'priority_type' => $customer->priority_type ?? 'null',
            'id_verified' => $customer->id_verified ?? ($customer->id_verification_status === 'verified'),
            'id_verification_status' => $customer->id_verification_status ?? 'unknown',
        ]);
        
        // Queue number is already set from database - no need to override
        
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
                'contact' => 'required|string|regex:/^[0-9]{9}$/'
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
            // Handle JSON requests (for contact number updates)
            if ($request->isJson()) {
                $customerId = $request->json('customer_id');
                $contactNumber = $request->json('contact_number');
                
                if (!$customerId || !$contactNumber) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer ID and contact number are required'
                    ], 400);
                }
                
                $customer = Customer::findOrFail($customerId);
                
                // Format contact number (add 09 prefix if missing)
                $formattedContact = str_starts_with($contactNumber, '09') ? $contactNumber : '09' . $contactNumber;
                
                $customer->update([
                    'contact_number' => $formattedContact,
                    'last_updated_at' => now(),
                ]);
                
                Log::info('Contact number updated via review details', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'contact_number' => $formattedContact
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Contact number updated successfully'
                ]);
            }
            
            // Handle form requests (legacy support)
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
            
            // Also check PriorityVerification table for all priority customers
            if ($priorityType !== 'normal') {
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
