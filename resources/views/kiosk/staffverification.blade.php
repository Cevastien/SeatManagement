<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priority Verification - Staff Assistance Required</title>
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

        @keyframes pulse-slow {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }

        .pulse-slow {
            animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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
                        <h1 class="text-xl font-semibold text-white" id="headerTitle">Priority Verification</h1>
                        <span class="px-2.5 py-0.5 text-white text-xs font-semibold rounded-full"
                            style="background-color: rgba(255, 255, 255, 0.15);" id="stepIndicator">Step 1.5 of 4</span>
                    </div>
                    <p class="text-gray-300 text-xs" id="headerDescription">Staff assistance required for ID verification</p>
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

    <!-- SCREEN 1: Initial Call Staff Screen -->
    <div class="flex-1 flex items-center justify-center overflow-hidden" id="initialScreen">
        <div class="flex flex-col items-center">
            <!-- ID Icon with Float Animation -->
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100 mb-6 float-animation">
                <i class="fas fa-id-card text-amber-600 text-5xl"></i>
            </div>

            <h2 class="text-4xl font-bold text-gray-800 mb-3 text-center">ID Verification Required</h2>
            <p class="text-xl text-gray-600 mb-12 text-center">A staff member will verify your ID for priority access</p>

            <!-- Call Staff Button -->
            <button onclick="callStaff()" 
                class="px-16 py-6 text-white rounded-xl hover:bg-gray-800 font-bold text-2xl shadow-2xl transition-all duration-200 transform hover:scale-105 mb-8"
                style="background-color: #09121E;">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-bell text-3xl pulse-slow"></i>
                    <span>Call Staff for Verification</span>
                </div>
            </button>

            <!-- Simple Instructions -->
            <div class="bg-gray-50 border-2 border-gray-200 rounded-xl px-8 py-4 mb-4">
                <p class="text-lg text-gray-700 text-center">
                    Please have your <strong>Senior Citizen ID, PWD ID, or Government ID</strong> ready
                </p>
            </div>

            <!-- Estimated Time -->
            <p class="text-sm text-gray-500 text-center">
                <i class="fas fa-clock mr-2"></i>Staff will arrive within <strong>2-3 minutes</strong>
            </p>
        </div>
    </div>

    <!-- SCREEN 2: Calling Staff Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden hidden" id="callingScreen">
        <div class="flex flex-col items-center">
            <!-- Spinning Icon -->
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-blue-100 mb-6">
                <i class="fas fa-bell text-blue-600 text-5xl spin-animation"></i>
            </div>

            <h2 class="text-4xl font-bold text-gray-800 mb-3 text-center">Calling Staff...</h2>
            <p class="text-xl text-gray-600 mb-8 text-center">Please wait while we notify our team</p>

            <!-- Loading Bar -->
            <div class="w-96 h-3 bg-gray-200 rounded-full overflow-hidden mb-8">
                <div class="h-full rounded-full pulse-slow" style="background-color: #09121E; width: 60%;"></div>
            </div>

            <p class="text-sm text-gray-500 text-center">This will only take a moment...</p>
        </div>
    </div>

    <!-- SCREEN 3: Waiting for Staff Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden hidden" id="waitingScreen">
        <div class="flex flex-col items-center">
            <!-- Clock Icon with Pulse -->
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 mb-6">
                <i class="fas fa-clock text-green-600 text-5xl pulse-slow"></i>
            </div>

            <h2 class="text-4xl font-bold text-gray-800 mb-3 text-center">Waiting for Staff Verification</h2>
            <p class="text-xl text-gray-600 mb-8 text-center">A staff member will be with you shortly</p>

            <!-- Staff Notified Card -->
            <div class="bg-green-50 border-2 border-green-200 rounded-xl px-12 py-6 mb-8">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                    <div>
                        <p class="text-lg font-bold text-green-800">Staff Notified Successfully</p>
                        <p class="text-sm text-green-700">Please have your ID ready</p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-gray-50 border-2 border-gray-200 rounded-xl px-8 py-4">
                <p class="text-base text-gray-700 text-center">
                    <i class="fas fa-info-circle mr-2" style="color: #09121E;"></i>
                    Estimated arrival: <strong>2-3 minutes</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- SCREEN 4: Verification Complete Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden hidden" id="completeScreen">
        <div class="flex flex-col items-center">
            <!-- Success Checkmark -->
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 mb-6">
                <i class="fas fa-check text-green-600 text-5xl"></i>
            </div>

            <h2 class="text-4xl font-bold text-gray-800 mb-3 text-center">Verification Complete!</h2>
            <p class="text-xl text-gray-600 mb-12 text-center">Your priority status has been confirmed</p>

            <!-- Success Card -->
            <div class="bg-white border-2 border-gray-200 rounded-xl px-12 py-6 shadow-lg mb-8">
                <div class="text-center">
                    <p class="text-lg text-gray-700 mb-2">You are now registered as:</p>
                    <p class="text-2xl font-bold text-gray-900">Senior Citizen Priority Guest</p>
                </div>
            </div>

            <!-- Redirecting Message -->
            <p class="text-base text-gray-600 text-center">
                <i class="fas fa-spinner spin-animation mr-2"></i>
                Redirecting to next step...
            </p>
        </div>
    </div>

    <!-- Loading/Processing Overlay -->
    <div id="processingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-md mx-4 text-center">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Calling Staff...</h3>
            <p class="text-gray-600">Please wait for a staff member to assist you</p>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bg-white px-8 py-5 flex-shrink-0 shadow-lg" id="bottomNav">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Go Back Button -->
            <button onclick="goBack()"
                class="px-16 py-5 bg-white hover:bg-gray-50 border-3 border-gray-400 text-gray-800 font-semibold text-xl rounded-2xl transition-all duration-200 flex items-center space-x-3 shadow-md hover:shadow-lg">
                <i class="fas fa-arrow-left text-2xl"></i>
                <span>Go Back</span>
            </button>

            <!-- Skip Priority Button -->
            <button onclick="skipPriority()" 
                class="px-20 py-5 text-white font-semibold text-xl rounded-2xl shadow-xl transition-all duration-200 flex items-center space-x-3 hover:shadow-2xl hover:scale-105"
                style="background-color: #09121E;">
                <span>Skip Priority</span>
                <i class="fas fa-arrow-right text-2xl"></i>
            </button>
        </div>
    </div>

    <script>
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const customerName = urlParams.get('name') || 'Guest';
        const priorityType = urlParams.get('priority_type') || 'senior';
        
        let verificationId = null;
        let statusCheckInterval = null;
        let hasCalledStaff = false;

        // Update time and date
        function updateDateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            const dateStr = now.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            
            document.getElementById('time').textContent = timeStr;
            document.getElementById('date').textContent = dateStr;
        }
        
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Show screen by ID and hide others
        function showScreen(screenId) {
            const screens = ['initialScreen', 'callingScreen', 'waitingScreen', 'completeScreen'];
            screens.forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            document.getElementById(screenId).classList.remove('hidden');
        }

        // Call staff for assistance
        async function callStaff() {
            if (hasCalledStaff) {
                alert('Staff has already been notified.\n\nPlease wait for a team member to assist you.');
                return;
            }

            hasCalledStaff = true;

            // Show calling screen
            showScreen('callingScreen');
            document.getElementById('headerTitle').textContent = 'Calling Staff';
            document.getElementById('headerDescription').textContent = 'Notifying team member...';

            try {
                // Send verification request to API
                const response = await fetch('/api/customer/request-verification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        customer_name: customerName,
                        priority_type: priorityType
                    })
                });

                const data = await response.json();

                if (data.success) {
                    verificationId = data.verification.id;

                    // Move to waiting screen after 2 seconds
                    setTimeout(() => {
                        showScreen('waitingScreen');
                        document.getElementById('headerTitle').textContent = 'Waiting for Verification';
                        document.getElementById('headerDescription').textContent = 'Staff member is on the way';

                        // Start checking verification status
                        startStatusCheck();
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to call staff');
                }
                
            } catch (error) {
                console.error('Error calling staff:', error);
                alert('There was an error calling staff. Please try again or approach the counter directly.');
                showScreen('initialScreen');
                hasCalledStaff = false;
            }
        }

        // Start checking verification status
        function startStatusCheck() {
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }

            statusCheckInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/api/customer/verification-status/${verificationId}`);
                    const data = await response.json();

                    if (data.success && data.verification.status === 'verified') {
                        console.log('✓ Verification complete:', data.verification);
                        
                        // Stop checking
                        clearInterval(statusCheckInterval);
                        
                        // Show complete screen
                        showScreen('completeScreen');
                        document.getElementById('headerTitle').textContent = 'Verification Complete';
                        document.getElementById('headerDescription').textContent = 'Priority status confirmed';
                        document.getElementById('stepIndicator').textContent = 'Step 2 of 4';

                        // Hide bottom navigation on complete screen
                        document.getElementById('bottomNav').style.display = 'none';
                        
                        // Redirect to review details after 3 seconds
                        setTimeout(() => {
                            window.location.href = '{{ route("kiosk.review-details") }}';
                        }, 3000);
                    }
                } catch (error) {
                    console.error('Error checking verification status:', error);
                }
            }, 2000); // Check every 2 seconds
        }

        // Skip priority and continue as regular customer
        function skipPriority() {
            if (confirm('Are you sure you want to skip priority verification?\n\nYou will be registered as a regular customer.')) {
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                }
                alert('Continuing as regular customer...');
            }
        }

        // Go back to registration
        function goBack() {
            if (confirm('Are you sure you want to go back?\n\nYour current registration will be cancelled.')) {
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                }
                alert('Returning to registration...');
            }
        }

        // Clean up interval on page unload
        window.addEventListener('beforeunload', function () {
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
        });
    </script>
</body>

</html>

