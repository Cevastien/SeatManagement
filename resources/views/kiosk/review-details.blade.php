<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Your Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            overflow: hidden;
            height: 100vh;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
        }
    </style>
</head>

<body class="font-inter h-screen flex flex-col">
    <!-- Header -->
    <div class="flex-shrink-0" style="background-color: #09121E;">
        <div class="px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <div class="flex items-center space-x-3 mb-0.5">
                        <h1 class="text-xl font-semibold text-white">Review Your Details</h1>
                        <span class="px-2.5 py-0.5 text-white text-xs font-semibold rounded-full"
                            style="background-color: rgba(255, 255, 255, 0.15);">Step 2 of 4</span>
                    </div>
                    <p class="text-gray-300 text-xs">Please confirm your information before proceeding</p>
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right">
                    <p class="text-white text-xl font-bold" id="time">3:24 PM</p>
                    <p class="text-gray-300 text-xs" id="date">Sep 16, 2025</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content - NO SCROLL, Single View -->
    <div class="flex-1 flex items-center justify-center px-8 py-4 bg-gray-100 overflow-hidden">
        <div class="w-full max-w-6xl mx-auto grid grid-cols-2 gap-6 h-full items-center">

            <!-- LEFT COLUMN: User Details -->
            <div
                class="bg-white border-2 border-gray-200 rounded-2xl shadow-lg p-5 h-full flex flex-col justify-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full"
                        style="background-color: rgba(9, 18, 30, 0.1);">
                        <i class="fas fa-clipboard-check text-2xl" style="color: #09121E;"></i>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-800 text-center mb-1">Your Information</h2>
                <p class="text-sm text-gray-600 text-center mb-5">Review and edit if needed</p>

                <div class="space-y-4">
                    <!-- Name -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                                style="background-color: #09121E;">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Name/Nickname</p>
                                <p class="text-base font-bold text-gray-800" id="reviewName">{{ $customer->name ?? 'Guest' }}</p>
                            </div>
                        </div>
                        <button onclick="editField('name')" class="hover:opacity-80 transition p-2"
                            style="color: #09121E;">
                            <i class="fas fa-edit text-base"></i>
                        </button>
                    </div>

                    <!-- Party Size -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                                style="background-color: #09121E;">
                                <i class="fas fa-users text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Party Size</p>
                                <p class="text-base font-bold text-gray-800" id="reviewPartySize">{{ $customer->party_size ?? '1' }} {{ ($customer->party_size ?? 1) == 1 ? 'Guest' : 'Guests' }}</p>
                            </div>
                        </div>
                        <button onclick="editField('party')" class="hover:opacity-80 transition p-2"
                            style="color: #09121E;">
                            <i class="fas fa-edit text-base"></i>
                        </button>
                    </div>

                    <!-- Contact Number -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                                style="background-color: #09121E;">
                                <i class="fas fa-phone text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Contact Number</p>
                                <p class="text-base font-bold text-gray-800" id="reviewContact">{{ $customer->contact_number ?? 'Not provided' }}</p>
                            </div>
                        </div>
                        <button onclick="editField('contact')" class="hover:opacity-80 transition p-2"
                            style="color: #09121E;">
                            <i class="fas fa-edit text-base"></i>
                        </button>
                    </div>

                    <!-- Priority Status -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                                style="background-color: #09121E;">
                                <i class="fas fa-star text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Priority Status</p>
                                @if($customer->id_verification_status === 'skipped_priority')
                                    <div class="flex items-center space-x-2">
                                        <p class="text-base font-bold text-gray-800">Regular Guest</p>
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">Priority Skipped</span>
                                    </div>
                                @elseif($customer->has_priority_member ?? false)
                                    <div class="flex items-center space-x-2">
                                        <p class="text-base font-bold text-gray-800">{{ ucfirst($customer->priority_type ?? 'Priority') }} Guest</p>
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                            @if($customer->id_verification_status === 'verified')
                                                ✓ Verified
                                            @else
                                                Priority
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <p class="text-base font-bold text-gray-800">Regular Guest</p>
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">Standard</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <button onclick="editField('priority')" class="hover:opacity-80 transition p-2"
                            style="color: #09121E;">
                            <i class="fas fa-edit text-base"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Queue Information -->
            <div class="bg-white border-2 border-gray-200 rounded-2xl shadow-lg p-6 h-full flex flex-col justify-center"
                x-data="queueUpdater({{ $customer->id }})">
                <div class="text-center">
                    <!-- Queue Number - Large & Prominent -->
                    <div class="mb-6">
                        <p class="text-sm font-medium mb-2" style="color: #09121E;">Your Queue Number</p>
                        <p class="text-7xl font-bold mb-2" style="color: #09121E;">#{{ $customer->queue_number }}</p>
                    </div>

                    <!-- Divider -->
                    <div class="border-t-2 border-gray-200 my-6"></div>

                    <!-- Customers Ahead & Wait Time - Side by Side -->
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Customers Ahead -->
                        <div class="customers-ahead-section">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users text-3xl mb-2" style="color: #09121E;"></i>
                                <p class="text-xs text-gray-600 mb-1">Customers Ahead</p>
                                <p class="text-4xl font-bold mb-2" style="color: #09121E;" x-text="customersAhead">
                                    {{ $queueInfo['customers_ahead'] ?? 0 }}
                                </p>
                                <p class="text-xs text-gray-500" x-show="customersAhead > 0">
                                    <span x-text="customersAhead"></span>
                                    <span x-text="customersAhead === 1 ? 'person' : 'people'"></span> waiting
                                </p>
                                <p class="text-xs text-green-600 font-semibold" x-show="customersAhead === 0">
                                    You're next!
                                </p>
                            </div>
                        </div>

                        <!-- Wait Time -->
                        <div class="wait-time-section">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-clock text-3xl mb-2" style="color: #09121E;"></i>
                                <p class="text-xs text-gray-600 mb-1">Estimated Wait</p>
                                <p class="text-4xl font-bold mb-2" style="color: #09121E;" x-text="waitTimeFormatted">
                                    {{ $formattedWait }}
                                </p>
                                <p class="text-xs text-gray-500">minutes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t-2 border-gray-200 my-6"></div>

                    <!-- Last Updated -->
                    <div class="text-center">
                        <p class="text-xs text-gray-400">
                            <i class="fas fa-sync-alt mr-1"></i>
                            Last updated: <span x-text="lastUpdated">{{ now()->format('g:i A') }}</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Updates automatically</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bg-white px-8 py-4 flex-shrink-0 shadow-lg">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Back Button -->
            <button onclick="goBack()"
                class="px-12 py-4 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-semibold text-lg rounded-xl transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                <i class="fas fa-arrow-left text-lg"></i>
                <span>Go Back</span>
            </button>

            <!-- Continue Button -->
            <button type="button" onclick="confirmAndPrint()"
                class="px-12 py-4 text-white font-semibold text-lg rounded-xl shadow-xl transition-all duration-200 flex items-center space-x-2 hover:shadow-2xl hover:scale-105"
                style="background-color: #09121E;">
                <span>Confirm & Print Receipt</span>
                <i class="fas fa-check text-lg"></i>
            </button>
        </div>
    </div>

    <script>
        // Update date and time
        function updateDateTime() {
            const now = new Date();

            const timeOptions = {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };
            const timeFormatted = now.toLocaleString('en-US', timeOptions);
            document.getElementById('time').textContent = timeFormatted;

            const dateOptions = {
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            };
            const dateFormatted = now.toLocaleString('en-US', dateOptions);
            document.getElementById('date').textContent = dateFormatted;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Alpine.js for dynamic queue updates
        function queueUpdater(customerId) {
            return {
                customersAhead: {{ $queueInfo['customers_ahead'] ?? 0 }},
                totalWaiting: {{ $queueInfo['total_waiting'] ?? 0 }},
                position: {{ $queueInfo['position'] ?? 1 }},
                waitTimeFormatted: '{{ $formattedWait }}',
                lastUpdated: '{{ now()->format('g:i A') }}',
                
                init() {
                    // Update every 10 seconds for real-time feel
                    this.updateQueue();
                    setInterval(() => this.updateQueue(), 10000);
                },
                
                async updateQueue() {
                    try {
                        const response = await fetch(`/api/customer/${customerId}/current-wait`);
                        const data = await response.json();
                        
                        if (data.status === 'waiting') {
                            this.customersAhead = data.customers_ahead;
                            this.totalWaiting = data.total_waiting;
                            this.position = data.position;
                            this.waitTimeFormatted = data.formatted;
                        } else {
                            // Customer was called/seated
                            this.waitTimeFormatted = data.message;
                            this.customersAhead = 0;
                        }
                        
                        this.lastUpdated = new Date().toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit'
                        });
                        
                    } catch (error) {
                        console.error('Error updating queue:', error);
                    }
                }
            }
        }

        // Clean up interval when page unloads
        window.addEventListener('beforeunload', function() {
            // Alpine.js handles cleanup automatically
        });

        function goBack() {
            // Go back to registration with edit mode to preserve existing data
            window.location.href = "{{ route('kiosk.registration') }}?edit=all";
        }

        function closeScreen() {
            if (confirm('Are you sure you want to cancel your registration?')) {
                window.location.href = "{{ route('kiosk.attract') }}";
            }
        }

        function editField(field) {
            // Redirect back to registration with edit parameter to preserve all existing data
            window.location.href = "{{ route('kiosk.registration') }}?edit=" + field;
        }

        function confirmAndPrint() {
            // Show loading state
            const confirmBtn = document.querySelector('button[onclick="confirmAndPrint()"]');
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            confirmBtn.disabled = true;

            // Submit the final registration
            fetch('{{ route("kiosk.registration.confirm") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    confirm: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to receipt/printing page with customer data
                    window.location.href = data.redirect_url;
                } else {
                    alert('Error confirming registration: ' + (data.message || 'Please try again'));
                    confirmBtn.innerHTML = '<span>Confirm & Print Receipt</span><i class="fas fa-check ml-4"></i>';
                    confirmBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                confirmBtn.innerHTML = '<span>Confirm & Print Receipt</span><i class="fas fa-check ml-4"></i>';
                confirmBtn.disabled = false;
            });
        }
    </script>

    <!-- Session Timeout Modal Manager -->
    <script src="{{ asset('js/session-timeout-modal.js') }}"></script>
</body>

</html>
