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
            background: linear-gradient(135deg, #f5f7fa 0%, #e8edf2 100%);
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
    <div class="flex-shrink-0" style="background-color: #111827;">
        <div class="p-8 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <h1 class="text-2xl font-semibold text-white" id="headerTitle">Preparing Your Receipt</h1>
                        <span class="px-3 py-1 text-white text-xs font-semibold rounded-full"
                            style="background-color: #374151;" id="stepIndicator">Step 3 of 3</span>
                    </div>
                    <p class="text-gray-300 text-sm" id="headerDesc">Please wait while we prepare your queue receipt</p>
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
    <div class="flex-1 flex items-center justify-center px-8 py-4 overflow-hidden">
        <div class="w-full max-w-5xl mx-auto">

            <!-- Two Column Layout: Printer Left, Success & Decision Right -->
            <div class="grid grid-cols-2 gap-8 items-center">

                <!-- Left: Printer Animation & Receipt -->
                <div class="flex items-center justify-center">
                    <div class="relative" style="width: 320px; height: 480px;">
                <!-- Printer Box -->
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-72 h-32 rounded-t-3xl shadow-2xl"
                            style="background-color: #111827;">
                <div
                                class="absolute top-4 left-4 right-4 h-12 bg-gray-800 rounded-lg flex items-center justify-center">
                        <div class="w-16 h-2 bg-green-500 rounded-full pulse-slow"></div>
                    </div>
                    <div
                                class="absolute bottom-4 left-4 right-4 h-14 bg-black bg-opacity-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-print text-white text-3xl pulse-slow"></i>
                    </div>
                </div>

                <!-- Paper Slot -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 w-60 h-3 bg-black rounded-b-lg"
                            style="top: 128px;"></div>

                <!-- Animated Receipt -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 overflow-hidden"
                            style="top: 132px; height: 340px;">
                    <div class="receipt-slide">
                                <div class="w-60 bg-white shadow-2xl"
                                    style="font-family: 'Courier New', monospace; font-size: 10px; line-height: 1.4;">
                                    <!-- Header -->
                                    <div class="p-2.5 text-center border-b border-dashed border-gray-400">
                                        <div class="text-xs font-bold mb-1">GERVACIOS</div>
                                        <div style="font-size: 8px;">COFFEE & EATERY</div>
                                </div>

                                    <!-- Restaurant Info -->
                                    <div class="px-2.5 py-2 text-center border-b border-dashed border-gray-400"
                                        style="font-size: 8px;">
                                        <div class="font-bold mb-0.5">CAFÉ GERVACIOS</div>
                                        <div>123 Coffee Street, Davao City</div>
                                        <div>Tel: (02) 8123-4567</div>
                            </div>

                                    <!-- Queue Number -->
                                    <div class="p-2.5 text-center border-b border-dashed border-gray-400">
                                        <div style="font-size: 9px;" class="font-bold mb-1">QUEUE RECEIPT</div>
                                        <div class="text-5xl font-bold my-1" id="receiptQueueNumber">#{{ $customer->queue_number }}</div>
                            </div>

                                    <!-- Customer Details -->
                                    <div class="px-2.5 py-2 border-b border-dashed border-gray-400"
                                        style="font-size: 9px;">
                                        <div class="flex justify-between mb-1">
                                    <span class="font-semibold">Name:</span>
                                    <span id="receiptName">{{ $customer->name }}</span>
                                </div>
                                        <div class="flex justify-between mb-1">
                                    <span class="font-semibold">Party Size:</span>
                                    <span id="receiptPartySize">{{ $customer->party_size }} pax</span>
                                </div>
                                        <div class="flex justify-between mb-1">
                                    <span class="font-semibold">Priority:</span>
                                    <span id="receiptPriority">{{ $customer->priority_type === 'normal' ? 'Regular' : ucfirst($customer->priority_type) }}</span>
                                </div>
                                        <div class="flex justify-between mb-1">
                                    <span class="font-semibold">Time:</span>
                                            <span id="receiptTime">12 Sept 2025 - 10:45 AM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Est. Wait:</span>
                                            <span id="receiptWaitTime">~20 mins</span>
                                </div>
                            </div>

                                    <!-- Instructions -->
                                    <div class="px-2.5 py-2 border-b border-dashed border-gray-400"
                                        style="font-size: 8px;">
                                        <div class="mb-1">• Please stay nearby when your number is called.</div>
                                        <div class="mb-1">• Grace period: 5 minutes after your number is called.</div>
                                        <div>• Missed slots will be marked as skipped.</div>
                            </div>

                                    <!-- Footer -->
                                    <div class="px-2.5 py-2 text-center" style="font-size: 9px;">
                                        <div class="font-bold mb-1">Thank you for visiting Café Gervacios!</div>
                                        <div style="font-size: 8px;">Enjoy your coffee while you wait.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Success Message & Print Decision -->
                <div class="flex flex-col items-center justify-center space-y-6">

                    <!-- Success Message -->
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-3">
                            <i class="fas fa-check text-green-600 text-4xl"></i>
            </div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Registration Successful!</h2>
                        <p class="text-lg text-gray-600 mb-4">Your queue number is ready</p>

                        <!-- Status Indicator -->
                        <div
                            class="inline-flex items-center space-x-3 px-6 py-3 bg-white rounded-full shadow-lg border-2 border-gray-200">
                            <div class="w-3 h-3 rounded-full animate-pulse" style="background-color: #111827;"></div>
                            <span class="text-base font-semibold text-gray-700" id="statusText">Ready to print...</span>
                        </div>
            </div>
            
                    <!-- Print Decision Card -->
                    <div class="bg-white border-3 border-gray-300 rounded-2xl shadow-xl p-8 w-full"
                        id="customerPrintDecision">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 text-center">Print Your Receipt?</h3>
                        <p class="text-base text-gray-600 mb-6 text-center">Would you like a physical copy?</p>
                        <div class="grid grid-cols-2 gap-4">
                            <button onclick="customerChoosePrint(true)"
                                class="px-8 py-5 text-white rounded-xl hover:opacity-90 font-bold text-lg shadow-lg transition-all"
                                style="background-color: #111827;">
                                <i class="fas fa-print mr-2"></i>Yes, Print
                        </button>
                            <button onclick="customerChoosePrint(false)"
                                class="px-8 py-5 bg-gray-600 text-white rounded-xl hover:bg-gray-700 font-bold text-lg shadow-lg transition-all">
                                <i class="fas fa-times mr-2"></i>No Thanks
                        </button>
                    </div>
                        <p class="text-sm text-gray-500 mt-4 text-center">View your number on screen anytime</p>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Bottom Footer - Only shown when needed -->
    <div class="bg-white px-8 py-4 flex-shrink-0 border-t-2 border-gray-200 hidden" id="footerContainer">
        <div class="text-center" id="footerMessage">
            <!-- Footer will be populated by JavaScript when needed -->
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
    <div class="bg-white border-t-2 border-gray-200 px-8 py-4 flex-shrink-0 hidden" id="successBottomNav">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Register Another Button -->
            <button onclick="handleNewTransaction()"
                class="px-16 py-5 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-bold text-xl rounded-xl transition flex items-center space-x-3">
                <i class="fas fa-plus text-2xl"></i>
                <span>Yes, Register Another</span>
            </button>

            <!-- Finish Button -->
            <button onclick="handleFinish()"
                class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl shadow-lg transition flex items-center space-x-3"
                style="background-color: #111827;">
                <span>No, I'm Done</span>
                <i class="fas fa-check text-2xl"></i>
            </button>
        </div>
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
                        statusText.textContent = 'Print complete!';
                        setTimeout(() => {
                            showSuccessState();
                        }, 1500);
                    }, 2000);
                    }, 2000);
                } else {
                statusText.textContent = 'Digital receipt ready';
                setTimeout(() => {
                    showSuccessState();
                }, 1500);
            }
        }


        // Show success state
        function showSuccessState() {
            // Get the main content area (printing state)
            const mainContent = document.querySelector('.flex-1.flex.items-center.justify-center.px-8.py-4.overflow-hidden');
            
            if (mainContent) {
                mainContent.classList.add('fade-out');
            }

            setTimeout(() => {
                // Hide main content, show success
                if (mainContent) {
                    mainContent.classList.add('hidden');
                }
                successState.classList.remove('hidden');
                successState.classList.add('fade-in');

                // Show bottom navigation
                const successBottomNav = document.getElementById('successBottomNav');
                successBottomNav.classList.remove('hidden');

                // Update header
                headerTitle.textContent = 'Registration Complete';
                
                // Update step indicator and description
                const headerDesc = document.getElementById('headerDesc');
                if (headerDesc) headerDesc.textContent = 'Your registration is complete';

                // Start auto-redirect countdown (30 seconds of inactivity)
                startAutoRedirect();
            }, 500);
        }

        function startAutoRedirect() {
            let countdown = 30;
            
            // Show footer container and populate message
            const footerContainer = document.getElementById('footerContainer');
            footerContainer.classList.remove('hidden');
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

        // Update step indicators based on customer type
        document.addEventListener('DOMContentLoaded', function() {
            const stepIndicator = document.getElementById('stepIndicator');
            if (stepIndicator) {
                // Check if customer is priority (not normal)
                const isPriority = customer.priority_type && customer.priority_type !== 'normal';
                
                if (isPriority) {
                    stepIndicator.textContent = 'Step 4 of 4';
                } else {
                    stepIndicator.textContent = 'Step 3 of 3';
                }
            }
        });
    </script>
</body>

</html>
