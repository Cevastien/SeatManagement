<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review Your Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            overflow: hidden;
            height: 100vh;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
        }
    </style>
</head>

<body class="h-screen flex flex-col bg-gray-100 overflow-hidden" style="font-family: 'Inter', sans-serif;" x-data="reviewApp()">
    
    <!-- Header -->
    <div class="flex-shrink-0" style="background-color: #111827;">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <h1 class="text-2xl font-bold text-white">Review Your Details</h1>
                        <span class="px-3 py-1 text-white text-xs font-semibold rounded-full"
                            style="background-color: #374151;" id="stepIndicator">Step 2 of 3</span>
                    </div>
                    <p class="text-gray-300 text-sm">Please confirm your information before proceeding</p>
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right">
                    <p class="text-white text-2xl font-bold" id="time">3:24 PM</p>
                    <p class="text-gray-300 text-sm" id="date">Sep 16, 2025</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center px-8 py-8 overflow-y-auto">
        <div class="w-full max-w-6xl">
            <div class="bg-white rounded-xl shadow-lg p-10">
                
                <!-- Title -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Please Review Your Details</h2>
                    <p class="text-sm text-gray-600">Confirm your information before generating your queue number</p>
                </div>

                <div class="flex gap-10">
                    <!-- Left Side: Queue Number Display -->
                    <div class="flex-shrink-0">
                        <div class="text-center bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-2xl px-12 py-8 shadow-md">
                            <p class="text-xs text-gray-500 uppercase tracking-widest font-semibold mb-3">Queue Number</p>
                            <div class="inline-block rounded-2xl px-10 py-6 mb-4 shadow-lg" style="background-color: #111827;">
                                <p class="text-7xl font-black text-white" x-text="'#' + queueNumber">#{{ $customer->queue_number ?? '001' }}</p>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-300">
                                <div class="flex items-center justify-center space-x-2 mb-2">
                                    <i class="fas fa-users text-gray-600 text-sm"></i>
                                    <p class="text-sm text-gray-700 font-medium" x-text="partySize + ' guests'">
                                        {{ $customer->party_size ?? 1 }} guests
                                    </p>
                                </div>
                                <div class="flex items-center justify-center space-x-2">
                                    <i class="fas fa-clock text-gray-600 text-sm"></i>
                                    <p class="text-sm text-gray-700 font-medium" x-text="'~' + waitTimeFormatted">
                                        ~{{ $queueInfo['wait_time_formatted'] ?? '20' }} mins
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="inline-block px-4 py-2 rounded-full text-xs font-bold shadow-sm" 
                                      :class="priorityType !== 'normal' ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-gray-200 text-gray-700 border border-gray-300'"
                                      x-text="priorityStatus">
                                    {{ $customer->priority_type === 'normal' ? 'Regular' : ucfirst($customer->priority_type) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Details List -->
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-center py-5 px-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #111827;">
                                    <i class="fas fa-users text-white text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Number of Guests</span>
                            </div>
                            <span class="text-gray-900 font-bold text-lg" x-text="partySize + ' Guests'">{{ $customer->party_size ?? 1 }} Guests</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-5 px-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #111827;">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Full Name</span>
                            </div>
                            <span class="text-gray-900 font-bold text-lg" x-text="customerName">{{ $customer->name ?? 'Guest' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-5 px-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #111827;">
                                    <i class="fas fa-phone text-white text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Contact Number</span>
                            </div>
                            <span class="text-gray-900 font-bold text-lg" x-text="contactNumber || 'Not provided'">{{ $customer->contact_number ?? 'Not provided' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-5 px-4 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #111827;">
                                    <i class="fas fa-star text-white text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium">Priority Status</span>
                            </div>
                            <span class="text-gray-900 font-bold text-lg" x-text="priorityStatus">{{ $customer->priority_type === 'normal' ? 'Regular' : ucfirst($customer->priority_type) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bg-white border-t-2 border-gray-200 px-8 py-4 flex-shrink-0">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Back Button -->
            <button onclick="editDetails()"
                class="px-16 py-5 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-bold text-xl rounded-xl transition flex items-center space-x-3">
                <i class="fas fa-edit text-2xl"></i>
                <span>Edit Details</span>
            </button>

            <!-- Continue Button -->
            <button type="button" @click="confirmAndPrint()" style="background-color: #111827;"
                class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl shadow-lg transition flex items-center space-x-3">
                <span>Continue</span>
                <i class="fas fa-arrow-right text-2xl"></i>
            </button>
        </div>
    </div>





    <script>
        const customerData = @json($customer ?? []);
        const queueData = @json($queueInfo ?? []);
        
        function reviewApp() {
            return {
                customerName: customerData.name || 'Guest',
                partySize: customerData.party_size || 1,
                contactNumber: customerData.contact_number || '',
                priorityType: customerData.priority_type || 'normal',
                priorityStatus: customerData.priority_type === 'normal' ? 'Regular' : (customerData.priority_type?.charAt(0).toUpperCase() + customerData.priority_type?.slice(1) || 'Regular'),
                queueNumber: customerData.queue_number || '001',
                customersAhead: queueData.customers_ahead || 0,
                waitTimeFormatted: queueData.wait_time_formatted || '20',
                
                async confirmAndPrint() {
                    try {
                        if (this.priorityType !== 'normal' && this.priorityType !== 'regular' && this.priorityType !== 'pregnant') {
                            const checkResponse = await fetch('{{ route("kiosk.check-verification-status") }}', {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            });
                            const checkData = await checkResponse.json();
                            if (!checkData.id_verified || checkData.id_verification_status !== 'verified') {
                                window.location.href = '{{ route("kiosk.staffverification") }}?name=' + encodeURIComponent(this.customerName) + '&priority_type=' + this.priorityType;
                                return;
                            }
                        }
                        const response = await fetch('{{ route("kiosk.registration.confirm") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ confirm: true })
                        });
                        const data = await response.json();
                        if (data.success && data.redirect_to) {
                            window.location.href = data.redirect_to;
                        } else {
                            alert('Error: ' + (data.message || 'Please try again'));
                        }
                    } catch (error) {
                        alert('An error occurred. Please try again.');
                    }
                },
                
                init() {
                    this.updateQueue();
                    setInterval(() => this.updateQueue(), 10000);
                },
                
                async updateQueue() {
                    try {
                        const response = await fetch('{{ route("api.queue.update") }}', {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.customersAhead = data.customers_ahead || 0;
                            this.waitTimeFormatted = data.wait_time_formatted || '20';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }
            }
        }

        function updateDateTime() {
            const now = new Date();
            document.getElementById('time').textContent = now.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            document.getElementById('date').textContent = now.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        function editDetails() {
            window.location.href = "{{ route('kiosk.registration') }}?edit=1";
        }

        // Update step indicators based on customer type
        document.addEventListener('DOMContentLoaded', function() {
            const stepIndicator = document.getElementById('stepIndicator');
            if (stepIndicator) {
                const isPriority = customerData.priority_type && customerData.priority_type !== 'normal';
                if (isPriority) {
                    stepIndicator.textContent = 'Step 3 of 4';
                } else {
                    stepIndicator.textContent = 'Step 2 of 3';
                }
            }
        });
    </script>

    <!-- Session Timeout Modal Manager -->
    <script src="{{ asset('js/session-timeout-modal.js') }}"></script>
</body>

</html>