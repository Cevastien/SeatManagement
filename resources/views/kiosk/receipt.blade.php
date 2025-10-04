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

<body class="font-inter bg-white h-screen flex flex-col">
    <!-- Header -->
    <div class="flex-shrink-0" style="background-color: #111827;">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <h1 class="text-2xl font-bold text-white" id="headerTitle">Preparing Your Receipt</h1>
                        <span class="px-3 py-1 text-white text-xs font-semibold rounded-full"
                            style="background-color: #374151;">Step 3 of 4</span>
                    </div>
                    <p class="text-gray-300 text-sm">Please wait while we prepare your queue receipt</p>
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

    <!-- Main Content - Printing State -->
    <div class="flex-1 flex items-center justify-center overflow-hidden" id="printingState">
        <div class="flex flex-col items-center">
            <!-- Success Checkmark -->
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 mb-8">
                <i class="fas fa-check text-green-600 text-5xl"></i>
            </div>

            <h2 class="text-4xl font-bold text-gray-800 mb-3">Registration Successful!</h2>
            <p class="text-xl text-gray-600 mb-12">Your queue number is being printed...</p>

            <!-- Printer Frame -->
            <div class="relative w-full max-w-md mb-8" style="height: 500px;">
                <!-- Printer Box -->
                <div
                    class="absolute top-0 left-1/2 transform -translate-x-1/2 w-80 h-48 bg-gray-800 rounded-t-2xl shadow-2xl">
                    <!-- Printer Top -->
                    <div
                        class="absolute top-4 left-4 right-4 h-16 bg-gray-700 rounded-lg flex items-center justify-center">
                        <div class="w-16 h-2 bg-green-500 rounded-full pulse-slow"></div>
                    </div>
                    <!-- Printer Display -->
                    <div
                        class="absolute bottom-4 left-4 right-4 h-20 bg-gray-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-print text-white text-3xl pulse-slow"></i>
                    </div>
                </div>

                <!-- Paper Slot -->
                <div
                    class="absolute top-48 left-1/2 transform -translate-x-1/2 w-64 h-4 bg-gray-900 rounded-b-lg overflow-hidden">
                    <div class="w-full h-full bg-gradient-to-b from-gray-700 to-gray-900"></div>
                </div>

                <!-- Animated Receipt -->
                <div class="absolute top-52 left-1/2 transform -translate-x-1/2 overflow-hidden" style="height: 250px;">
                    <div class="receipt-slide">
                        <!-- Receipt Design -->
                        <div class="w-64 bg-white shadow-2xl" style="font-family: 'Courier New', monospace;">
                            <div class="p-4 text-center border-b-2 border-dashed border-gray-400">
                                <div class="mb-2">
                                    <p
                                        style="font-family: 'Inter', sans-serif; font-weight: 300; font-size: 14px; letter-spacing: 2px;">
                                        GERVACIOS</p>
                                    <p style="font-family: 'Inter', sans-serif; font-size: 9px; letter-spacing: 1px;">
                                        COFFEE & EATERY</p>
                                </div>
                                <p class="text-xs font-bold mt-2">CAFÉ GERVACIOS</p>
                                <p class="text-xs">123 Coffee Street, Davao City</p>
                                <p class="text-xs">Tel: (02) 8123-4567</p>
                            </div>

                            <div class="p-4 text-center border-b-2 border-dashed border-gray-400">
                                <p class="text-sm font-bold mb-2">QUEUE RECEIPT</p>
                                <p class="text-5xl font-bold" id="receiptQueueNumber">#{{ $customer->queue_number }}</p>
                            </div>

                            <div class="px-4 py-3 text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span class="font-semibold">Name:</span>
                                    <span id="receiptName">{{ $customer->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Party Size:</span>
                                    <span id="receiptPartySize">{{ $customer->party_size }} pax</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Priority:</span>
                                    <span id="receiptPriority">{{ $customer->priority_type === 'normal' ? 'Regular' : ucfirst($customer->priority_type) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Time:</span>
                                    <span id="receiptTime">{{ now()->format('d M Y - g:i A') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Est. Wait:</span>
                                    <span id="receiptWaitTime">Updating...</span>
                                </div>
                            </div>

                            <div class="px-4 pb-3 text-xs border-t-2 border-dashed border-gray-400 pt-3 space-y-2">
                                <p>• Please stay nearby when your number is called.</p>
                                <p>• Grace period: 5 minutes after your number is called.</p>
                                <p>• Missed slots will be marked as skipped.</p>
                            </div>

                            <div class="px-4 pb-4 text-center border-t-2 border-dashed border-gray-400 pt-3">
                                <p class="text-xs font-bold">Thank you for visiting Café Gervacios!</p>
                                <p class="text-xs mt-1">Enjoy your coffee while you wait.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Print Decision -->
            <div class="flex items-center justify-center space-x-3 px-8 py-4 bg-gray-100 rounded-full">
                <div class="w-3 h-3 bg-gray-700 rounded-full animate-pulse"></div>
                <span class="text-xl font-semibold text-gray-700" id="statusText">Preparing receipt...</span>
            </div>
            
            <!-- Customer Print Decision Modal -->
            <div id="customerPrintDecision" class="hidden mt-8 bg-white border-2 border-gray-200 rounded-2xl shadow-xl p-8">
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-secondary mb-4">Print Your Receipt?</h3>
                    <p class="text-lg text-gray-600 mb-6">Would you like to print your queue receipt?</p>
                    <div class="flex space-x-6 justify-center">
                        <button onclick="customerChoosePrint(true)" 
                            class="px-8 py-4 bg-primary text-white rounded-xl hover:bg-primary-dark font-bold text-lg shadow-lg hover:shadow-xl transition-all">
                            <i class="fas fa-print mr-3"></i>Yes, Print Receipt
                        </button>
                        <button onclick="customerChoosePrint(false)" 
                            class="px-8 py-4 bg-gray-600 text-white rounded-xl hover:bg-gray-700 font-bold text-lg shadow-lg hover:shadow-xl transition-all">
                            <i class="fas fa-times mr-3"></i>No, Skip Printing
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">You can always view your queue number on the screen</p>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Success State (Hidden initially) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden hidden" id="successState">
        <div class="flex flex-col items-center text-center">
            <!-- Large Success Checkmark -->
            <div
                class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-green-100 mb-8 checkmark-animate">
                <i class="fas fa-check text-green-600 text-6xl"></i>
            </div>

            <h2 class="text-5xl font-bold text-gray-800 mb-4">Registration Complete!</h2>
            <p class="text-2xl text-gray-600 mb-8">Your queue number is ready</p>

            <div class="bg-gray-50 border-2 border-gray-200 rounded-xl px-12 py-8 mb-12">
                <p class="text-xl text-gray-700">Your queue number: <span
                        class="font-bold text-4xl text-gray-900" id="successQueueNumber">#{{ request('queue', '001') }}</span></p>
            </div>

            <!-- Action Buttons -->
            <div class="w-full max-w-2xl">
                <p class="text-2xl font-semibold text-gray-700 mb-6">Would you like to register another party?</p>
            </div>
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
            <button onclick="handleFinish()" style="background-color: #111827;"
                class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl shadow-lg transition flex items-center space-x-3">
                <span>No, I'm Done</span>
                <i class="fas fa-check text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Footer - Auto redirect message -->
    <div class="text-center pb-6 text-base text-gray-600" id="footerMessage">
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
            document.getElementById('time').textContent = timeFormatted;

            const dateOptions = {
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            };
            const dateFormatted = now.toLocaleString('en-US', dateOptions);
            document.getElementById('date').textContent = dateFormatted;

            // Update receipt time
            document.getElementById('receiptTime').textContent = now.toLocaleDateString('en-US', { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric' 
            }) + ' - ' + now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit', 
                hour12: true 
            });
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
