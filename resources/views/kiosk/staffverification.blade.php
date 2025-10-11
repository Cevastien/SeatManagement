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
    <div class="flex-1 flex items-center justify-center overflow-y-auto px-8 py-8" id="initialScreen">
        <div class="w-full max-w-4xl my-auto">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    
                    <!-- Icon -->
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full mb-6 float-animation shadow-lg" style="background-color: #111827;">
                        <i class="fas fa-id-card text-white text-7xl"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="text-4xl font-bold text-gray-900 mb-8">ID Verification Required</h2>

                    <!-- Call Staff Button -->
                    <button id="callStaffBtn" onclick="callStaff()" 
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
    <div class="flex-1 flex items-center justify-center overflow-y-auto px-8 py-8 hidden" id="callingScreen">
        <div class="w-full max-w-4xl my-auto">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-6 shadow-lg">
                        <i class="fas fa-bell text-gray-900 text-7xl spin-animation"></i>
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
    <div class="flex-1 flex items-center justify-center overflow-y-auto px-8 py-8 hidden" id="waitingScreen">
        <div class="w-full max-w-4xl my-auto">
            <div class="bg-white rounded-xl shadow-lg p-12">
                <div class="flex flex-col items-center text-center">
                    <!-- Animated Bell Icon -->
                    <div class="inline-flex items-center justify-center w-40 h-40 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-6 shadow-lg relative">
                        <i class="fas fa-bell text-gray-900 text-7xl spin-animation"></i>
                        <!-- Pulse rings -->
                        <div class="absolute inset-0 rounded-full border-4 border-gray-400 animate-ping opacity-75"></div>
                    </div>
                    
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Staff Notified</h2>
                    <p class="text-xl text-gray-600 mb-8">Your request has been sent</p>
                    
                    <!-- What to Expect Section -->
                    <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-6 max-w-2xl w-full">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">What to Expect:</h3>
                        <div class="space-y-3 text-left">
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-check-circle text-gray-900 text-lg mt-1"></i>
                                <p class="text-gray-700">A staff member will arrive in <strong>2-3 minutes</strong></p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-id-card text-gray-900 text-lg mt-1"></i>
                                <p class="text-gray-700">Please have your <strong>ID ready</strong> for verification</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-user-check text-gray-900 text-lg mt-1"></i>
                                <p class="text-gray-700">Staff will verify your priority status</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estimated Time -->
                    <div class="flex items-center space-x-3 text-gray-600">
                        <i class="fas fa-clock text-2xl"></i>
                        <div class="text-left">
                            <p class="text-sm text-gray-500">Estimated arrival</p>
                            <p class="text-lg font-bold" id="estimatedArrival">2-3 minutes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCREEN 5: Verification Failed Screen (Hidden) -->
    <div class="flex-1 flex items-center justify-center overflow-y-auto px-8 py-8 hidden" id="failedScreen">
        <div class="w-full max-w-4xl my-auto">
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
    <div class="flex-1 flex items-center justify-center overflow-y-auto px-8 py-8 hidden" id="completeScreen">
        <div class="w-full max-w-4xl my-auto">
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

    <!-- Skip Priority Modal -->
    <div id="skipPriorityModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full mx-4 p-8">
            <!-- Modal Header -->
            <div class="text-center mb-6">
                <!-- Warning Icon -->
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 bg-yellow-100">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl"></i>
                </div>
                
                <!-- Modal Title -->
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Skip Priority Verification?</h2>
            </div>
            
            <!-- Modal Content -->
            <div class="space-y-4 mb-8">
                <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #111827;">
                            <i class="fas fa-info-circle text-white text-xl"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-lg font-bold text-gray-900 mb-2">What happens next?</p>
                            <p class="text-gray-700 leading-relaxed">
                                You will be registered as a <strong>regular customer</strong> without priority status. 
                                You can still call staff later if needed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="flex space-x-4">
                <button onclick="hideSkipPriorityModal()" 
                    class="flex-1 px-8 py-4 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-bold text-lg rounded-xl transition">
                    Cancel
                </button>
                <button onclick="confirmSkipPriority()" 
                    class="flex-1 px-8 py-4 text-white font-bold text-lg rounded-xl transition hover:opacity-90"
                    style="background-color: #111827;">
                    Continue as Regular
                </button>
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

            <!-- Skip Priority Button -->
            <button type="button" onclick="showSkipPriorityModal()" style="background-color: #111827;"
                class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl shadow-lg transition flex items-center space-x-3">
                <span>Skip Priority</span>
                <i class="fas fa-arrow-right text-2xl"></i>
            </button>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const customerName = urlParams.get('name') || 'Guest';
        const priorityType = urlParams.get('priority_type') || 'senior';
        const partySize = urlParams.get('party_size') || '1';
        
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
                // Disable button to prevent double submission
                document.getElementById('callStaffBtn').disabled = true;
                
                // Get fresh CSRF token
                const tokenResponse = await fetch('/api/csrf-token', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const tokenData = await tokenResponse.json();
                
                const response = await fetch('/api/customer/request-verification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': tokenData.csrf_token
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        customer_name: customerName,
                        priority_type: priorityType,
                        party_size: parseInt(partySize)
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
                // Re-enable button
                document.getElementById('callStaffBtn').disabled = false;
                alert('Error calling staff. Please try again.');
                showScreen('initialScreen');
                hasCalledStaff = false;
            }
        }

        // Removed laggy loading overlay functions for better performance

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
                                window.location.href = '{{ route("kiosk.review-details") }}?name=' + encodeURIComponent(customerName) + '&priority_type=' + priorityType + '&party_size=' + partySize + '&verified=true';
                            }, 3000);
                        } else if (data.verification.status === 'rejected' || data.verification.status === 'failed') {
                            clearInterval(statusCheckInterval);
                            showScreen('failedScreen');
                            document.getElementById('headerTitle').textContent = 'Verification Issue';
                            document.getElementById('headerDescription').textContent = 'Unable to verify priority status';
                            document.getElementById('bottomNav').style.display = 'flex';
                            
                            // Update bottom navigation for failed verification with same spacing as registration
                            updateFailedNavigation();
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }, 2000);
        }


        function showSkipPriorityModal() {
            document.getElementById('skipPriorityModal').classList.remove('hidden');
        }

        function hideSkipPriorityModal() {
            document.getElementById('skipPriorityModal').classList.add('hidden');
        }

        function confirmSkipPriority() {
            if (statusCheckInterval) clearInterval(statusCheckInterval);
            window.location.href = '{{ route("kiosk.review-details") }}?skip_priority=1';
        }

        function goBack() {
            if (confirm('Go back?\n\nYour registration will be cancelled.')) {
                if (statusCheckInterval) clearInterval(statusCheckInterval);
                window.location.href = '{{ route("kiosk.registration") }}?edit=all';
            }
        }

        function updateFailedNavigation() {
            const bottomNav = document.getElementById('bottomNav');
            bottomNav.innerHTML = `
                <div class="flex items-center justify-between max-w-6xl mx-auto">
                    <!-- Try Again Button -->
                    <button onclick="retryVerification()"
                        class="px-16 py-5 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-bold text-xl rounded-xl transition flex items-center space-x-3">
                        <i class="fas fa-redo text-xl"></i>
                        <span>Try Again</span>
                    </button>

                    <!-- Continue as Regular Button -->
                    <button type="button" onclick="continueAsRegular()" style="background-color: #111827;"
                        class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl shadow-lg transition flex items-center space-x-3">
                        <span>Continue as Regular</span>
                        <i class="fas fa-arrow-right text-xl"></i>
                    </button>
                </div>
            `;
        }

        function retryVerification() {
            // Reset the verification process
            hasCalledStaff = false;
            document.getElementById('callStaffBtn').disabled = false;
            showScreen('initialScreen');
            document.getElementById('headerTitle').textContent = 'Priority Verification';
            document.getElementById('headerDescription').textContent = 'Staff assistance required for ID verification';
        }

        function continueAsRegular() {
            if (confirm('Continue as a regular customer without priority status?')) {
                window.location.href = '{{ route("kiosk.review-details") }}?name=' + encodeURIComponent(customerName) + '&priority_type=normal&party_size=' + partySize + '&skip_priority=1';
            }
        }

        window.addEventListener('beforeunload', () => {
            if (statusCheckInterval) clearInterval(statusCheckInterval);
        });
    </script>

    <!-- Removed laggy loading overlay for better performance -->

    <!-- Session Timeout Modal Manager -->
    <script src="{{ asset('js/session-timeout-modal.js') }}"></script>
</body>
</html>