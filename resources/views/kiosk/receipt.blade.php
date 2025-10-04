<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Printing Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        @keyframes printSlide {
            0% {
                transform: translateY(100%);
                opacity: 0;
            }

            50% {
                transform: translateY(0);
                opacity: 1;
            }

            100% {
                transform: translateY(-20%);
                opacity: 1;
            }
        }

        .receipt-slide {
            animation: printSlide 3s ease-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .pulse-slow {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes checkmark {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 0;
            }

            50% {
                transform: scale(1.2) rotate(10deg);
            }

            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        .checkmark-animate {
            animation: checkmark 0.6s ease-out forwards;
        }

        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in forwards;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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
                        <h1 class="text-xl font-semibold text-white" id="headerTitle">Preparing Your Receipt</h1>
                        <span class="px-2.5 py-0.5 text-white text-xs font-semibold rounded-full"
                            style="background-color: rgba(255, 255, 255, 0.15);">Step 3 of 4</span>
                    </div>
                    <p class="text-gray-300 text-xs" id="headerDesc">Please wait while we prepare your queue receipt</p>
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

    <!-- Main Content - Printing State (NO SCROLL, Single View) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden bg-gray-100" id="printingState">
        <div class="w-full max-w-6xl mx-auto px-8">
            <div class="grid grid-cols-2 gap-8 items-center">
                
                <!-- LEFT COLUMN: Status & Actions -->
                <div class="flex flex-col items-center justify-center">
                    <!-- Success Checkmark -->
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-6">
                        <i class="fas fa-check text-green-600 text-4xl"></i>
                    </div>

                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Registration Successful!</h2>
                    <p class="text-lg text-gray-600 mb-8">Your queue number is ready</p>

                    <!-- Status Indicator -->
                    <div class="flex items-center justify-center space-x-3 px-6 py-3 bg-white rounded-full shadow-lg mb-8">
                        <div class="w-2.5 h-2.5 rounded-full animate-pulse" style="background-color: #09121E;"></div>
                        <span class="text-base font-semibold text-gray-700" id="statusText">Preparing receipt...</span>
                    </div>

                    <!-- Customer Print Decision -->
                    <div id="customerPrintDecision" class="hidden bg-white border-2 border-gray-200 rounded-2xl shadow-xl p-6 w-full">
                        <div class="text-center">
                            <h3 class="text-xl font-bold text-gray-800 mb-3">Print Your Receipt?</h3>
                            <p class="text-sm text-gray-600 mb-5">Would you like a physical copy?</p>
                            <div class="flex space-x-4 justify-center">
                                <button onclick="customerChoosePrint(true)" 
                                    class="flex-1 px-6 py-3 text-white rounded-xl hover:opacity-90 font-semibold text-base shadow-lg transition-all"
                                    style="background-color: #09121E;">
                                    <i class="fas fa-print mr-2"></i>Yes, Print
                                </button>
                                <button onclick="customerChoosePrint(false)" 
                                    class="flex-1 px-6 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 font-semibold text-base shadow-lg transition-all">
                                    <i class="fas fa-times mr-2"></i>No Thanks
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">View your number on screen anytime</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Printer Animation & Receipt Preview -->
                <div class="flex flex-col items-center justify-center">
                    <!-- Printer Frame -->
                    <div class="relative w-full max-w-sm" style="height: 380px;">
                        <!-- Printer Box -->
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-64 h-32 rounded-t-2xl shadow-2xl" style="background-color: #09121E;">
                            <!-- Printer Top Display -->
                            <div class="absolute top-3 left-3 right-3 h-12 bg-gray-800 rounded-lg flex items-center justify-center">
                                <div class="w-12 h-1.5 bg-green-500 rounded-full pulse-slow"></div>
                            </div>
                            <!-- Printer Icon Display -->
                            <div class="absolute bottom-3 left-3 right-3 h-14 bg-black bg-opacity-50 rounded-lg flex items-center justify-center">
                                <i class="fas fa-print text-white text-2xl pulse-slow"></i>
                            </div>
                        </div>

                        <!-- Paper Slot -->
                        <div class="absolute top-32 left-1/2 transform -translate-x-1/2 w-52 h-3 bg-black rounded-b-lg overflow-hidden">
                            <div class="w-full h-full bg-gradient-to-b from-gray-700 to-black"></div>
                        </div>

                        <!-- Animated Receipt -->
                        <div class="absolute top-36 left-1/2 transform -translate-x-1/2 overflow-hidden" style="height: 200px;">
                            <div class="receipt-slide">
                                <!-- Compact Receipt Design -->
                                <div class="w-52 bg-white shadow-2xl text-xs" style="font-family: 'Courier New', monospace;">
                                    <div class="p-3 text-center border-b border-dashed border-gray-400">
                                        <p class="font-bold text-sm mb-1">GERVACIOS</p>
                                        <p class="text-xs">COFFEE & EATERY</p>
                                    </div>

                                    <div class="p-3 text-center border-b border-dashed border-gray-400">
                                        <p class="text-xs font-bold mb-1">QUEUE RECEIPT</p>
                                        <p class="text-4xl font-bold" id="receiptQueueNumber">#{{ $customer->queue_number }}</p>
                                    </div>

                                    <div class="px-3 py-2 text-xs space-y-0.5">
                                        <div class="flex justify-between">
                                            <span class="font-semibold">Name:</span>
                                            <span id="receiptName">{{ $customer->name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-semibold">Party:</span>
                                            <span id="receiptPartySize">{{ $customer->party_size }} pax</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-semibold">Priority:</span>
                                            <span id="receiptPriority">{{ $customer->priority_type === 'normal' ? 'Regular' : ucfirst($customer->priority_type) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-semibold">Wait:</span>
                                            <span id="receiptWaitTime">25-30 mins</span>
                                        </div>
                                    </div>

                                    <div class="px-3 pb-2 text-xs text-center border-t border-dashed border-gray-400 pt-2">
                                        <p class="font-bold">Thank you!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success State (Hidden initially, NO SCROLL) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden bg-gray-100 hidden" id="successState">
        <div class="flex flex-col items-center text-center">
            <!-- Large Success Checkmark -->
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 mb-6 checkmark-animate">
                <i class="fas fa-check text-green-600 text-5xl"></i>
            </div>

            <h2 class="text-4xl font-bold text-gray-800 mb-3">Registration Complete!</h2>
            <p class="text-xl text-gray-600 mb-6">Your queue number is ready</p>

            <div class="bg-white border-2 border-gray-200 rounded-xl px-10 py-6 mb-8 shadow-lg">
                <p class="text-lg text-gray-700 mb-2">Your Queue Number</p>
                <p class="font-bold text-5xl text-gray-900" style="color: #09121E;" id="successQueueNumber">#{{ request('queue', '001') }}</p>
            </div>

            <!-- Single Line Question -->
            <p class="text-2xl font-semibold text-gray-700 mb-2">Register another party?</p>
        </div>
    </div>

    <!-- Bottom Navigation Bar (Success State) -->
    <div class="bg-white px-8 py-4 flex-shrink-0 shadow-lg hidden" id="successBottomNav">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Register Another Button -->
            <button onclick="handleNewTransaction()"
                class="px-12 py-4 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-semibold text-lg rounded-xl transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg">
                <i class="fas fa-plus text-lg"></i>
                <span>Yes, Register Another</span>
            </button>

            <!-- Finish Button -->
            <button onclick="handleFinish()"
                class="px-12 py-4 text-white font-semibold text-lg rounded-xl shadow-xl transition-all duration-200 flex items-center space-x-2 hover:shadow-2xl hover:scale-105"
                style="background-color: #09121E;">
                <span>No, I'm Done</span>
                <i class="fas fa-check text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Footer - Auto redirect message -->
    <div class="text-center py-3 text-sm text-gray-600 bg-white" id="footerMessage">
        <p>Please wait...</p>
    </div>

    <script>
        // Get customer data from route parameter
        const customer = @json($customer);
        const queueNumber = customer.queue_number;
        const customerName = customer.name;
        const partySize = customer.party_size;
        const priority = customer.priority_type;
        const customerId = customer.id;

        // Update receipt with actual data
        document.addEventListener('DOMContentLoaded', function() {
            // Update receipt data
            document.getElementById('receiptQueueNumber').textContent = '#' + customer.queue_number;
            document.getElementById('receiptName').textContent = customer.name;
            document.getElementById('receiptPartySize').textContent = customer.party_size + ' pax';
            document.getElementById('receiptPriority').textContent = customer.priority_type === 'normal' ? 'Regular' : customer.priority_type.charAt(0).toUpperCase() + customer.priority_type.slice(1);
            // Wait time will be updated dynamically by updateReceiptWaitTime()
            
            // Update success state queue number
            document.getElementById('successQueueNumber').textContent = '#' + customer.queue_number;
        });

        // Update date and time
        function updateDateTime() {
            const now = new Date();

            const timeOptions = {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };
            const timeFormatted = now.toLocaleString('en-US', timeOptions);

            const dateOptions = {
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            };
            const dateFormatted = now.toLocaleString('en-US', dateOptions);

            // Update header time and date
            const timeElement = document.getElementById('time');
            const dateElement = document.getElementById('date');
            
            if (timeElement) {
                timeElement.textContent = timeFormatted;
            }
            
            if (dateElement) {
                dateElement.textContent = dateFormatted;
            }

            // Update receipt time if element exists
            const receiptTimeElement = document.getElementById('receiptTime');
            if (receiptTimeElement) {
                receiptTimeElement.textContent = now.toLocaleDateString('en-US', { 
                    day: 'numeric', 
                    month: 'short', 
                    year: 'numeric' 
                }) + ' - ' + now.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit', 
                    hour12: true 
                });
            }
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Dynamic wait time updates for receipt
        function updateReceiptWaitTime() {
            if (!customerId) return;
            
            fetch(`/api/customer/${customerId}/current-wait`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const waitTimeElement = document.getElementById('receiptWaitTime');
                    if (data.status === 'waiting') {
                        waitTimeElement.textContent = data.formatted || 'Calculating...';
                    } else {
                        waitTimeElement.textContent = data.message || 'Seated/Completed';
                    }
                } else {
                    document.getElementById('receiptWaitTime').textContent = 'Calculating...';
                }
            })
            .catch(error => {
                console.error('Error fetching wait time:', error);
                document.getElementById('receiptWaitTime').textContent = 'Calculating...';
            });
        }

        // Update wait time on page load and periodically
        updateReceiptWaitTime();
        setInterval(updateReceiptWaitTime, 30000); // Update every 30 seconds

        let autoRedirectInterval;

        // Printing and success logic
        const printingState = document.getElementById('printingState');
        const successState = document.getElementById('successState');
        const statusText = document.getElementById('statusText');
        const headerTitle = document.getElementById('headerTitle');
        const footerMessage = document.getElementById('footerMessage');

        // Show customer print decision after 2 seconds
        setTimeout(() => {
            statusText.textContent = 'Ready to print...';
            const customerDecision = document.getElementById('customerPrintDecision');
            customerDecision.classList.remove('hidden');
        }, 2000);

        // Customer chooses whether to print or not
        function customerChoosePrint(wantsToPrint) {
            const customerDecision = document.getElementById('customerPrintDecision');
            const statusText = document.getElementById('statusText');
            
            customerDecision.classList.add('hidden');
            
            if (wantsToPrint) {
                statusText.textContent = 'Printing receipt...';
                
                // Simulate printing process
                setTimeout(() => {
                    statusText.textContent = 'Cutting paper...';
                    setTimeout(() => {
                        showSuccessState();
                    }, 2000);
                }, 2000);
            } else {
                statusText.textContent = 'Printing skipped - Digital receipt displayed';
                
                // Show success state after brief delay
                setTimeout(() => {
                    showSuccessState();
                }, 1500);
            }
        }

        // Show success state
        function showSuccessState() {
            // Fade out printing state
            printingState.classList.add('fade-out');

            setTimeout(() => {
                // Hide printing, show success
                printingState.classList.add('hidden');
                successState.classList.remove('hidden');
                successState.classList.add('fade-in');

                // Show bottom navigation
                const successBottomNav = document.getElementById('successBottomNav');
                successBottomNav.classList.remove('hidden');

                // Update header
                headerTitle.textContent = 'Registration Complete';
                
                // Update step indicator and description
                const stepIndicator = document.querySelector('.px-3.py-1.text-white.text-xs.font-semibold.rounded-full');
                const description = document.querySelector('.text-gray-300.text-sm');
                if (stepIndicator) stepIndicator.textContent = 'Step 4 of 4';
                if (description) description.textContent = 'Your registration is complete';

                // Start auto-redirect countdown (30 seconds of inactivity)
                startAutoRedirect();
            }, 500);
        }

        function startAutoRedirect() {
            let countdown = 30;
            footerMessage.innerHTML = `<p>Auto-returning to home in <span class="font-bold">${countdown}</span> seconds...</p>`;

            autoRedirectInterval = setInterval(() => {
                countdown--;
                footerMessage.innerHTML = `<p>Auto-returning to home in <span class="font-bold">${countdown}</span> seconds...</p>`;

                if (countdown === 0) {
                    clearInterval(autoRedirectInterval);
                    window.location.href = "{{ route('kiosk.attract') }}";
                }
            }, 1000);
        }

        function handleNewTransaction() {
            clearInterval(autoRedirectInterval);
            window.location.href = "{{ route('kiosk.registration') }}";
        }

        function handleFinish() {
            clearInterval(autoRedirectInterval);
            window.location.href = "{{ route('kiosk.attract') }}";
        }
    </script>
</body>

</html>
