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
                    colors: {
                        primary: '#6366f1',
                        'primary-dark': '#4f46e5',
                        secondary: '#2c3e50',
                        accent: '#f59e0b',
                        neutral: '#f5f7fa',
                        'neutral-dark': '#e3e8ef'
                    },
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
        }
    </style>
</head>

<body class="font-inter bg-white h-screen flex flex-col">
    <!-- Header -->
    <div class="flex-shrink-0" style="background-color: #111827;">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <h1 class="text-2xl font-bold text-white">Review Your Details</h1>
                        <span class="px-3 py-1 text-white text-xs font-semibold rounded-full"
                            style="background-color: #374151;">Step 2 of 4</span>
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
    <div class="flex-1 flex items-center justify-center p-8 overflow-auto">
        <div class="w-full max-w-3xl">
            <!-- Info Icon -->
            <div class="flex justify-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100">
                    <i class="fas fa-clipboard-check text-primary text-4xl"></i>
                </div>
            </div>

            <h2 class="text-3xl font-bold text-gray-800 text-center mb-3">Please Review Your Information</h2>
            <p class="text-lg text-gray-600 text-center mb-10">Make sure everything is correct before confirming</p>

            <!-- Details Card -->
            <div class="bg-white border-2 border-gray-200 rounded-2xl shadow-xl p-8 mb-8">
                <div class="space-y-6">
                    <!-- Name -->
                    <div class="flex items-start justify-between pb-6 border-b border-gray-300">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-gray-800">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Name/Nickname</p>
                                <p class="text-2xl font-bold text-gray-800" id="reviewName">{{ $customer->name ?? 'Guest' }}</p>
                            </div>
                        </div>
                        <button onclick="editField('name')" class="text-primary hover:text-primary-dark transition">
                            <i class="fas fa-edit text-xl"></i>
                        </button>
                    </div>

                    <!-- Party Size -->
                    <div class="flex items-start justify-between pb-6 border-b border-gray-300">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-gray-800">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Party Size</p>
                                <p class="text-2xl font-bold text-gray-800" id="reviewPartySize">{{ $customer->party_size ?? '1' }} {{ ($customer->party_size ?? 1) == 1 ? 'Guest' : 'Guests' }}</p>
                            </div>
                        </div>
                        <button onclick="editField('party')" class="text-primary hover:text-primary-dark transition">
                            <i class="fas fa-edit text-xl"></i>
                        </button>
                    </div>

                    <!-- Contact Number -->
                    <div class="flex items-start justify-between pb-6 border-b border-gray-300">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-gray-800">
                                <i class="fas fa-phone text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Contact Number</p>
                                <p class="text-2xl font-bold text-gray-800" id="reviewContact">{{ $customer->contact_number ?? 'Not provided' }}</p>
                            </div>
                        </div>
                        <button onclick="editField('contact')" class="text-primary hover:text-primary-dark transition">
                            <i class="fas fa-edit text-xl"></i>
                        </button>
                    </div>

                    <!-- Priority Status -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-gray-800">
                                <i class="fas fa-star text-white"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Priority Status</p>
                                @if($customer->id_verification_status === 'skipped_priority')
                                    <div class="flex items-center space-x-2">
                                        <p class="text-2xl font-bold text-gray-800">Regular Guest</p>
                                        <span class="px-3 py-1 bg-amber-100 text-amber-700 text-sm font-semibold rounded-full">Priority Skipped</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Converted to regular customer - no priority assistance</p>
                                @elseif($customer->has_priority_member ?? false)
                                    <div class="flex items-center space-x-2">
                                        <p class="text-2xl font-bold text-gray-800">{{ ucfirst($customer->priority_type ?? 'Priority') }} Guest</p>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-semibold rounded-full">
                                            @if($customer->id_verification_status === 'verified')
                                                ✓ Verified
                                            @else
                                                Priority
                                            @endif
                                        </span>
                                    </div>
                                    @if($customer->priority_type === 'pregnant')
                                        <p class="text-sm text-gray-600 mt-1">Pregnancy priority confirmed - no ID verification needed</p>
                                    @else
                                        <p class="text-sm text-gray-600 mt-1">Priority assistance available - ID verification required</p>
                                    @endif
                                @else
                                    <div class="flex items-center space-x-2">
                                        <p class="text-2xl font-bold text-gray-800">Regular Guest</p>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-semibold rounded-full">Standard</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">No priority assistance required</p>
                                @endif
                            </div>
                        </div>
                        <button onclick="editField('priority')" class="text-primary hover:text-primary-dark transition">
                            <i class="fas fa-edit text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Queue Information -->
            <div class="bg-white border-2 border-gray-200 rounded-2xl shadow-xl p-6 mb-8" x-data="queueUpdater({{ $customer->id }})">
                <div class="text-center">
                    <!-- Queue Number -->
                    <div class="mb-6">
                        <p class="text-sm text-secondary font-medium">Your Queue Number</p>
                        <p class="text-5xl font-bold text-primary">#{{ $customer->queue_number }}</p>
                    </div>
                    
                    <!-- Customers Ahead (Dynamic) -->
                    <div class="customers-ahead-section mb-6">
                        <div class="flex items-center justify-center gap-3">
                            <i class="fas fa-users text-2xl text-primary"></i>
                            <div>
                                <p class="text-sm text-gray-600">Customers ahead of you</p>
                                <p class="text-3xl font-bold text-primary" x-text="customersAhead">
                                    {{ $queueInfo['customers_ahead'] ?? 0 }}
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2" x-show="customersAhead > 0">
                            <span x-text="customersAhead"></span> 
                            <span x-text="customersAhead === 1 ? 'person is' : 'people are'"></span> 
                            waiting before you
                        </p>
                        <p class="text-xs text-green-600 mt-2 font-semibold" x-show="customersAhead === 0">
                            🎉 You're next in line!
                        </p>
                    </div>
                    
                    <!-- Wait Time (Dynamic) -->
                    <div class="wait-time-section">
                        <div class="flex items-center justify-center gap-3">
                            <i class="fas fa-clock text-2xl text-primary"></i>
                            <div>
                                <p class="text-sm text-gray-600">Estimated Wait Time</p>
                                <p class="text-2xl font-bold text-primary" x-text="waitTimeFormatted">
                                    {{ $formattedWait }}
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Updates automatically when tables are freed
                        </p>
                    </div>
                    
                    <!-- Last Updated -->
                    <div class="text-center mt-4">
                        <p class="text-xs text-gray-400">
                            Last updated: <span x-text="lastUpdated">{{ now()->format('g:i A') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bg-white border-t-2 border-gray-200 px-8 py-4 flex-shrink-0">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Back Button -->
            <button onclick="goBack()"
                class="px-16 py-5 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-bold text-xl rounded-xl transition flex items-center space-x-3">
                <i class="fas fa-arrow-left text-2xl"></i>
                <span>Go Back</span>
            </button>

            <!-- Continue Button -->
            <button type="button" onclick="confirmAndPrint()" style="background-color: #111827;"
                class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl shadow-lg transition flex items-center space-x-3">
                <span>Confirm & Print Receipt</span>
                <i class="fas fa-check text-2xl"></i>
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
