<?php

namespace App\Livewire\Kiosk;

use App\Models\Customer;
use App\Models\QueueEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RegistrationStepper extends Component
{
    public int $currentStep = 1;
    public array $steps = [
        1 => 'Personal Information',
        2 => 'Party Details', 
        3 => 'Contact Information',
        4 => 'Priority Check',
        5 => 'Special Requests',
        6 => 'Confirmation'
    ];

    // Form data
    public string $name = '';
    public string $lastName = '';
    public int $partySize = 1;
    public string $contactNumber = '';
    public string $priorityType = '';
    public string $specialRequests = '';

    protected function rules()
    {
        return match($this->currentStep) {
            1 => [
                'name' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
            ],
            2 => [
                'partySize' => 'required|integer|min:1|max:' . \App\Models\Setting::get('party_size_max', 50),
            ],
            3 => [
                'contactNumber' => 'nullable|string|max:20',
            ],
            4 => [
                'priorityType' => 'required|in:yes,no',
            ],
            5 => [
                'specialRequests' => 'nullable|string|max:500',
            ],
            default => []
        };
    }

    public function nextStep()
    {
        $this->validate();
        
        if ($this->currentStep < count($this->steps)) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep($step)
    {
        if ($step >= 1 && $step <= count($this->steps)) {
            $this->currentStep = $step;
        }
    }

    public function incrementPartySize()
    {
        $maxPartySize = \App\Models\Setting::get('party_size_max', 50);
        if ($this->partySize < $maxPartySize) {
            $this->partySize++;
        }
    }

    public function decrementPartySize()
    {
        if ($this->partySize > 1) {
            $this->partySize--;
        }
    }

    public function submit()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Generate queue number
            $queueNumber = Customer::getNextQueueNumber();
            
            // Calculate estimated wait time
            $estimatedWaitTime = Customer::calculateEstimatedWaitTime($this->partySize);
            
            // Determine if it's a group and priority type
            $isGroup = $this->partySize >= 2;
            $priorityType = $this->priorityType === 'yes' ? 'priority' : 'normal';
            
            // Create customer
            $customer = Customer::create([
                'name' => $this->name . ' ' . $this->lastName,
                'party_size' => $this->partySize,
                'contact_number' => $this->contactNumber,
                'queue_number' => $queueNumber,
                'priority_type' => $priorityType,
                'is_group' => $isGroup,
                'status' => 'waiting',
                'estimated_wait_minutes' => $estimatedWaitTime,
                'registered_at' => now(),
                'special_requests' => $this->specialRequests,
            ]);

            // Create queue event
            QueueEvent::createEvent($customer->id, 'registered');

            DB::commit();

            // Store customer ID in session for next steps
            session(['kiosk_customer_id' => $customer->id]);

            // Redirect to confirmation
            return redirect()->route('kiosk.confirmation', ['queueNumber' => $customer->queue_number]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => [
                    'name' => $this->name,
                    'lastName' => $this->lastName,
                    'partySize' => $this->partySize,
                    'contactNumber' => $this->contactNumber,
                ]
            ]);
            session()->flash('error', 'Failed to register. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.kiosk.registration-stepper')
            ->layout('components.layouts.kiosk');
    }
}
