<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Priority Verification - Staff Assistance Required</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin-animation {
            animation: spin 2s linear infinite;
        }
    </style>
</head>
<body class="h-screen flex flex-col bg-gray-100 overflow-hidden">
    
    <!-- Header -->
    <div class="flex-shrink-0" style="background-color: #111827;">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <h1 class="text-2xl font-bold text-white" id="headerTitle">Priority Verification</h1>
                        <span class="px-3 py-1 text-white text-xs font-semibold rounded-full"
                            style="background-color: #374151;" id="stepIndicator">Step 2 of 4</span>
                    </div>
                    <p class="text-gray-300 text-sm" id="headerDescription">Staff assistance required for ID verification</p>
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

    <!-- SCREEN 1: Initial Call Staff Screen -->
    <div class="flex-1 flex items-center justify-center overflow-hidden px-8 py-8" id="initialScreen">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    
                    <!-- Icon -->
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full mb-6 float-animation shadow-lg" style="background-color: #111827;">
                        <i class="fas fa-id-card text-white text-7xl"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="text-4xl font-bold text-gray-900 mb-8">ID Verification Required</h2>

                    <!-- Call Staff Button -->
                    <button onclick="callStaff()" 
                        class="px-12 py-6 text-white rounded-xl hover:bg-gray-800 font-bold text-2xl shadow-lg transition-all duration-200 mb-8"
                        style="background-color: #111827;">
                        <div class="flex items-center space-x-4">
                            <i class="fas fa-bell text-3xl"></i>
                            <span>Call Staff for Verification</span>
                        </div>
                    </button>

                    <!-- Instructions -->
                    <div class="space-y-4">
                        <p class="text-lg text-gray-700">Please have your <strong>Senior Citizen ID, PWD ID, or Government ID</strong> ready</p>
                        
                        <div class="flex items-center justify-center space-x-3 text-gray-700">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #111827;">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <p class="text-lg">Staff arrives in <strong>2-3 minutes</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCREEN 2: Calling Staff Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden px-8 py-8 hidden" id="callingScreen">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 mb-6 shadow-lg">
                        <i class="fas fa-bell text-blue-600 text-7xl spin-animation"></i>
                    </div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Calling Staff...</h2>
                    <p class="text-xl text-gray-600 mb-8">Please wait</p>
                    <div class="w-96 h-4 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="background-color: #111827; width: 60%; animation: pulse 2s ease-in-out infinite;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCREEN 3: Waiting for Staff Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden px-8 py-8 hidden" id="waitingScreen">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full bg-gradient-to-br from-green-100 to-green-200 mb-6 shadow-lg">
                        <i class="fas fa-clock text-green-600 text-7xl"></i>
                    </div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Waiting for Staff</h2>
                    <p class="text-xl text-green-600 font-semibold mb-8">Staff has been notified</p>

                    <div class="flex items-center space-x-3 text-gray-700">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #111827;">
                            <i class="fas fa-info-circle text-white"></i>
                        </div>
                        <p class="text-lg">Arrives in <strong>2-3 minutes</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCREEN 5: Verification Failed Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden px-8 py-8 hidden" id="failedScreen">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full mb-6 shadow-lg" style="background-color: #dc2626;">
                        <i class="fas fa-exclamation-triangle text-white text-7xl"></i>
                    </div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Unable to Verify</h2>
                    <p class="text-xl text-gray-600 mb-8">We couldn't complete the priority verification</p>
                    
                    <div class="bg-red-50 border-2 border-red-200 rounded-xl px-10 py-6 mb-8 max-w-2xl w-full">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #dc2626;">
                                <i class="fas fa-info-circle text-white text-xl"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-lg font-bold text-red-900 mb-2">What happens next?</p>
                                <p class="text-red-800">You can continue as a regular customer or visit our service desk for assistance.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCREEN 4: Verification Complete Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-hidden px-8 py-8 hidden" id="completeScreen">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full bg-gradient-to-br from-green-100 to-green-200 mb-6 shadow-lg">
                        <i class="fas fa-check text-green-600 text-7xl"></i>
                    </div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Verification Complete!</h2>
                    <p class="text-xl text-gray-600 mb-8">Priority status confirmed</p>
                    
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl px-10 py-6 mb-8 max-w-2xl w-full">
                        <p class="text-gray-700 mb-2">You are registered as:</p>
                        <p class="text-3xl font-bold text-gray-900" id="priorityDisplay">Senior Citizen Priority</p>
                    </div>

                    <div class="flex items-center space-x-3 text-gray-600">
                        <i class="fas fa-spinner spin-animation text-2xl" style="color: #111827;"></i>
                        <p class="text-lg font-medium">Redirecting...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bg-white border-t-2 border-gray-200 px-8 py-4 flex-shrink-0" id="bottomNav">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Back Button -->
            <button onclick="goBack()"
                class="px-16 py-5 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-bold text-xl rounded-xl transition flex items-center space-x-3">
                <i class="fas fa-arrow-left text-2xl"></i>
                <span>Go Back</span>
            </button>

            <!-- Continue Button -->
            <button type="button" onclick="skipPriority()" style="background-color: #111827;"
                class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl shadow-lg transition flex items-center space-x-3">
                <span>Continue</span>
                <i class="fas fa-arrow-right text-2xl"></i>
            </button>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const customerName = urlParams.get('name') || 'Guest';
        const priorityType = urlParams.get('priority_type') || 'senior';
        
        let verificationId = null;
        let statusCheckInterval = null;
        let hasCalledStaff = false;

        function updateDateTime() {
            const now = new Date();
            document.getElementById('time').textContent = now.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            document.getElementById('date').textContent = now.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        }
        
        updateDateTime();
        setInterval(updateDateTime, 1000);

        function showScreen(screenId) {
            ['initialScreen', 'callingScreen', 'waitingScreen', 'completeScreen', 'failedScreen'].forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            document.getElementById(screenId).classList.remove('hidden');
        }

        async function callStaff() {
            if (hasCalledStaff) {
                alert('Staff has already been notified. Please wait for assistance.');
                return;
            }
            hasCalledStaff = true;
            showScreen('callingScreen');
            document.getElementById('headerTitle').textContent = 'Calling Staff';
            document.getElementById('headerDescription').textContent = 'Notifying team member...';

            try {
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
                    setTimeout(() => {
                        showScreen('waitingScreen');
                        document.getElementById('headerTitle').textContent = 'Waiting for Verification';
                        document.getElementById('headerDescription').textContent = 'Staff member is on the way';
                        startStatusCheck();
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to call staff');
                }
            } catch (error) {
                alert('Error calling staff. Please try again.');
                showScreen('initialScreen');
                hasCalledStaff = false;
            }
        }

        function startStatusCheck() {
            if (statusCheckInterval) clearInterval(statusCheckInterval);
            statusCheckInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/api/customer/verification-status/${verificationId}`);
                    const data = await response.json();
                    if (data.success) {
                        if (data.verification.status === 'verified') {
                            clearInterval(statusCheckInterval);
                            showScreen('completeScreen');
                            document.getElementById('headerTitle').textContent = 'Verification Complete';
                            document.getElementById('headerDescription').textContent = 'Priority status confirmed';
                            document.getElementById('bottomNav').style.display = 'none';
                            
                            // Update priority display based on actual priority type
                            const priorityDisplay = document.getElementById('priorityDisplay');
                            switch(priorityType) {
                                case 'senior':
                                    priorityDisplay.textContent = 'Senior Citizen Priority';
                                    break;
                                case 'pwd':
                                    priorityDisplay.textContent = 'PWD Priority';
                                    break;
                                case 'pregnant':
                                    priorityDisplay.textContent = 'Pregnant Priority';
                                    break;
                                default:
                                    priorityDisplay.textContent = 'Priority Customer';
                            }
                            
                            setTimeout(() => {
                                window.location.href = '{{ route("kiosk.review-details") }}';
                            }, 3000);
                        } else if (data.verification.status === 'rejected' || data.verification.status === 'failed') {
                            clearInterval(statusCheckInterval);
                            showScreen('failedScreen');
                            document.getElementById('headerTitle').textContent = 'Verification Issue';
                            document.getElementById('headerDescription').textContent = 'Unable to verify priority status';
                            document.getElementById('bottomNav').style.display = 'flex';
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }, 2000);
        }


        function skipPriority() {
            if (confirm('Continue as regular customer?\n\nYou will be registered without priority status.')) {
                if (statusCheckInterval) clearInterval(statusCheckInterval);
                window.location.href = '{{ route("kiosk.review-details") }}?skip_priority=1';
            }
        }

        function goBack() {
            if (confirm('Go back?\n\nYour registration will be cancelled.')) {
                if (statusCheckInterval) clearInterval(statusCheckInterval);
                window.location.href = '{{ route("kiosk.registration") }}?edit=all';
            }
        }

        window.addEventListener('beforeunload', () => {
            if (statusCheckInterval) clearInterval(statusCheckInterval);
        });
    </script>

    <!-- Session Timeout Modal Manager -->
    <script src="{{ asset('js/session-timeout-modal.js') }}"></script>
</body>
</html>