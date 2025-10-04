<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\QueueEvent;
use App\Services\QueueEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    /**
     * Get customer's current queue position
     */
    public function getPosition(Request $request, $queueNumber)
    {
        $customer = Customer::where('queue_number', $queueNumber)->orderBy('created_at', 'desc')->first();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Queue number not found'
            ], 404);
        }

        // Calculate queue position using dynamic estimator
        $estimator = new QueueEstimator();
        $queuePosition = $estimator->getQueuePosition($customer->id);
        
        $normalPosition = $queuePosition ? $queuePosition['position'] : 1;
        $priorityPosition = $customer->hasPriority() ? $queuePosition['priority_ahead'] + 1 : null;

        return response()->json([
            'success' => true,
            'data' => [
                'queue_number' => $customer->queue_number,
                'name' => $customer->name,
                'party_size' => $customer->party_size,
                'status' => $customer->status,
                'status_label' => $customer->status_label,
                'priority_type' => $customer->priority_type,
                'priority_label' => $customer->priority_label,
                'wait_time' => $customer->wait_time,
                'estimated_wait_time' => $queuePosition ? $queuePosition['estimated_wait_minutes'] : $customer->estimated_wait_minutes,
                'formatted_wait_time' => $queuePosition ? $queuePosition['formatted_wait_time'] : $customer->formatted_wait_time,
                'queue_position' => $normalPosition,
                'priority_position' => $priorityPosition,
                'is_priority' => $customer->hasPriority(),
                'is_called' => $customer->status === 'called',
                'is_seated' => $customer->status === 'seated',
                'special_requests' => $customer->special_requests,
                'registered_at' => $customer->registered_at->format('H:i'),
            ]
        ]);
    }

    /**
     * Get currently serving customer
     */
    public function getCurrentServing()
    {
        $servingCustomer = Customer::where('status', 'called')->first();
        
        if (!$servingCustomer) {
            return response()->json([
                'success' => true,
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'queue_number' => $servingCustomer->queue_number,
                'name' => $servingCustomer->name,
                'party_size' => $servingCustomer->party_size,
                'priority_type' => $servingCustomer->priority_type,
                'priority_label' => $servingCustomer->priority_label,
                'called_at' => $servingCustomer->called_at->format('H:i'),
                'special_requests' => $servingCustomer->special_requests,
            ]
        ]);
    }

    /**
     * Get queue summary for public display with dynamic calculations
     */
    public function getQueueSummary()
    {
        $estimator = new QueueEstimator();
        $stats = $estimator->getQueueStats();
        $waitingCustomers = Customer::waiting()->orderBy('priority_type', 'desc')->orderBy('registered_at')->get();
        $currentServing = Customer::where('status', 'called')->first();
        
        $summary = [
            'total_waiting' => $stats['total_waiting'],
            'normal_queue' => $stats['normal_waiting'],
            'priority_queue' => $stats['priority_waiting'],
            'current_serving' => $currentServing ? [
                'queue_number' => $currentServing->queue_number,
                'name' => $currentServing->name,
                'party_size' => $currentServing->party_size,
                'priority_label' => $currentServing->priority_label,
                'called_at' => $currentServing->called_at->format('H:i'),
            ] : null,
            'estimated_wait_time' => $stats['estimated_normal_wait'],
            'estimated_priority_wait' => $stats['estimated_priority_wait'],
            'concurrent_capacity' => $stats['concurrent_capacity'],
            'queue_efficiency' => $stats['queue_efficiency'],
            'next_customers' => $waitingCustomers->take(5)->map(function ($customer) use ($estimator) {
                $position = $estimator->getQueuePosition($customer->id);
                return [
                    'queue_number' => $customer->queue_number,
                    'name' => $customer->name,
                    'party_size' => $customer->party_size,
                    'priority_label' => $customer->priority_label,
                    'wait_time' => $customer->wait_time,
                    'estimated_wait_minutes' => $position ? $position['estimated_wait_minutes'] : $customer->estimated_wait_minutes,
                    'formatted_wait_time' => $position ? $position['formatted_wait_time'] : $customer->formatted_wait_time,
                ];
            })->toArray(),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Process OCR ID verification
     */
    public function processOCR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'image_data' => 'required|string', // Base64 encoded image
            'id_type' => 'required|in:senior_citizen,pwd',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = Customer::findOrFail($request->customer_id);
            
            // Simulate OCR processing (in real implementation, use Tesseract.js or Google Vision API)
            $ocrResult = $this->simulateOCRProcessing($request->image_data, $request->id_type);
            
            if ($ocrResult['success']) {
                // Update customer with priority
                $customer->update([
                    'priority_type' => $request->id_type,
                    'has_priority_member' => true,
                    'id_verification_status' => 'verified',
                    'id_verification_data' => $ocrResult['data'],
                ]);
                
                // Update wait time based on new priority status
                $estimator = new QueueEstimator();
                $newWaitTime = $estimator->calculateWaitTime($customer->party_size, $customer->priority_type);
                $customer->update(['estimated_wait_minutes' => $newWaitTime]);
                
                // Update wait times for all waiting customers
                $estimator->updateAllWaitTimes();

                // Create priority applied event
                QueueEvent::createEvent(
                    $customer->id,
                    'priority_applied',
                    null,
                    "Priority applied: {$request->id_type}",
                    ['priority_type' => $request->id_type, 'ocr_confidence' => $ocrResult['confidence']]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'ID verified successfully! Priority applied.',
                    'data' => [
                        'priority_type' => $customer->priority_type,
                        'priority_label' => $customer->priority_label,
                        'ocr_confidence' => $ocrResult['confidence'],
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'ID verification failed. Please try again or contact staff.',
                    'data' => [
                        'error_details' => $ocrResult['error']
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process ID verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simulate OCR processing (replace with real OCR implementation)
     */
    private function simulateOCRProcessing($imageData, $idType)
    {
        // Simulate processing delay
        usleep(500000); // 0.5 seconds
        
        // Simulate 90% success rate
        $success = rand(1, 100) <= 90;
        
        if ($success) {
            return [
                'success' => true,
                'confidence' => rand(85, 98) / 100, // 85-98% confidence
                'data' => [
                    'id_type' => $idType,
                    'extracted_name' => 'Sample Name',
                    'id_number' => '1234567890',
                    'expiry_date' => '2025-12-31',
                    'processed_at' => now()->toISOString(),
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Could not read ID clearly. Please ensure the ID is well-lit and positioned correctly.',
                'confidence' => rand(20, 75) / 100, // Low confidence
            ];
        }
    }

    /**
     * Get current wait time for an existing customer by ID
     */
    public function getCurrentWait($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $estimator = new QueueEstimator();
        
        if ($customer->status !== 'waiting') {
            return response()->json([
                'status' => $customer->status,
                'message' => ucfirst($customer->status),
                'success' => true
            ]);
        }
        
        $waitTime = $estimator->calculateWaitTime(
            $customer->party_size,
            $customer->priority_type,
            $customer->id
        );
        
        // Update customer's wait time in real-time
        $customer->update(['estimated_wait_minutes' => $waitTime]);
        
        $queueInfo = $estimator->getQueuePosition($customer->id);
        
        return response()->json([
            'status' => 'waiting',
            'minutes' => $waitTime,
            'formatted' => $estimator->formatWaitTime($waitTime),
            'position' => $queueInfo['position'],
            'customers_ahead' => $queueInfo['customers_ahead'],
            'total_waiting' => $queueInfo['total_waiting'],
            'timestamp' => now()->toIso8601String(),
            'success' => true
        ]);
    }

    /**
     * Get current wait time for a new customer (before they're in database)
     */
    public function getCurrentWaitTime(Request $request)
    {
        $partySize = $request->get('party_size', 1);
        $priorityType = $request->get('priority_type', 'normal');
        
        $estimator = new QueueEstimator();
        $waitTime = $estimator->calculateWaitTimeForNew($partySize, $priorityType);
        
        return response()->json([
            'minutes' => $waitTime,
            'formatted' => $estimator->formatWaitTime($waitTime),
            'timestamp' => now()->toIso8601String(),
            'success' => true
        ]);
    }

    /**
     * System health check
     */
    public function healthCheck()
    {
        try {
            // Check database connection
            $customerCount = Customer::count();
            $queueCount = Customer::waiting()->count();
            
            return response()->json([
                'success' => true,
                'status' => 'healthy',
                'data' => [
                    'timestamp' => now()->toISOString(),
                    'database_connected' => true,
                    'total_customers' => $customerCount,
                    'current_queue' => $queueCount,
                    'system_version' => '1.0.0',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'unhealthy',
                'message' => 'System health check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}