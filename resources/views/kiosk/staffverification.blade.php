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

<body class="font-inter bg-white h-screen flex flex-col">
    <!-- Header -->
    <div class="flex-shrink-0" style="background-color: #111827;">
        <div class="p-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <h1 class="text-2xl font-bold text-white">Priority Verification</h1>
                        <span class="px-3 py-1 text-white text-xs font-semibold rounded-full"
                            style="background-color: #374151;">Step 1.5 of 4</span>
                    </div>
                    <p class="text-gray-300 text-sm">Staff assistance required for ID verification</p>
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
        <div class="w-full max-w-4xl">
            <!-- ID Verification Icon -->
            <div class="flex justify-center mb-6">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-100">
                    <i class="fas fa-id-card text-amber-600 text-5xl"></i>
                </div>
            </div>

            <h2 class="text-4xl font-bold text-gray-800 text-center mb-3">ID Verification Required</h2>
            <p class="text-xl text-gray-600 text-center mb-4" id="priorityMessage">
                @php
                    $priorityType = request()->get('priority_type', 'senior');
                    $name = request()->get('name', 'Guest');
                    $displayType = match($priorityType) {
                        'senior' => 'Senior Citizen',
                        'pwd' => 'Person with Disability (PWD)',
                        default => 'Priority Customer',
                    };
                @endphp
                Hello <strong>{{ $name }}</strong>, you've registered as a <strong>{{ $displayType }}</strong>
            </p>
            <p class="text-lg text-gray-500 text-center mb-10">A staff member will verify your ID to complete your priority registration</p>

            <!-- Information Card -->
            <div class="bg-white border-2 border-gray-200 rounded-2xl shadow-xl p-8 mb-8">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-primary text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-secondary mb-3">What You Need to Do:</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                                <span class="text-lg">Have your valid ID ready (Senior Citizen ID, PWD ID, or government-issued ID)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                                <span class="text-lg">Press the button below to call a staff member</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                                <span class="text-lg">Show your ID to the staff member when they arrive</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Call Staff Button -->
            <div class="mb-8">
                <button onclick="callStaff()" 
                    class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-6 px-8 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg">
                    <div class="flex items-center justify-center space-x-4">
                        <i class="fas fa-bell text-3xl pulse-slow"></i>
                        <span class="text-2xl">Call Staff for ID Verification</span>
                    </div>
                </button>
            </div>

            <!-- Alternative Options -->
            <div class="bg-white border-2 border-gray-200 rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-bold text-secondary mb-4 text-center">Other Options</h3>
                <div class="grid grid-cols-1 gap-4">
                    <!-- Skip Priority -->
                    <button onclick="skipPriority()" 
                        class="w-full bg-white hover:bg-gray-50 text-gray-700 font-semibold py-4 px-6 rounded-xl border-2 border-gray-300 transition duration-200">
                        <div class="flex items-center justify-center space-x-3">
                            <i class="fas fa-forward text-xl"></i>
                            <span class="text-lg">Continue as Regular Customer</span>
                        </div>
                    </button>
                    
                    <!-- Go Back -->
                    <button onclick="goBack()" 
                        class="w-full bg-white hover:bg-gray-50 text-gray-700 font-semibold py-4 px-6 rounded-xl border-2 border-gray-300 transition duration-200">
                        <div class="flex items-center justify-center space-x-3">
                            <i class="fas fa-arrow-left text-xl"></i>
                            <span class="text-lg">Go Back to Registration</span>
                        </div>
                    </button>
                </div>
            </div>
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

        // Call staff for assistance
        async function callStaff() {
            if (hasCalledStaff) {
                alert('⏳ Staff has already been notified.\n\nPlease wait for a team member to assist you.');
                return;
            }

            const overlay = document.getElementById('processingOverlay');
            const overlayTitle = overlay.querySelector('h3');
            const overlayText = overlay.querySelector('p');
            
            overlay.classList.remove('hidden');
            overlayTitle.textContent = 'Calling Staff...';
            overlayText.textContent = 'Please wait for a staff member to assist you';

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
                    hasCalledStaff = true;

                    console.log('✓ Staff notification sent:', {
                        verification_id: verificationId,
                        customer_name: customerName,
                        priority_type: priorityType
                    });

                    // Update overlay to show waiting state
                    overlayTitle.textContent = '⏳ Waiting for Staff Verification';
                    overlayText.textContent = 'A staff member will be with you shortly. Please have your ID ready.';

                    // Start checking verification status
                    startStatusCheck();
                } else {
                    throw new Error(data.message || 'Failed to call staff');
                }
                
            } catch (error) {
                console.error('Error calling staff:', error);
                alert('⚠️ There was an error calling staff. Please try again or approach the counter directly.');
                overlay.classList.add('hidden');
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
                        
                        // Show success message
                        const overlay = document.getElementById('processingOverlay');
                        const overlayTitle = overlay.querySelector('h3');
                        const overlayText = overlay.querySelector('p');
                        
                        overlayTitle.textContent = '✓ Verification Complete!';
                        overlayText.textContent = 'Redirecting to next step...';
                        
                        // Redirect to review details after a short delay
                        setTimeout(() => {
                            window.location.href = '{{ route("kiosk.review-details") }}';
                        }, 1500);
                    }
                } catch (error) {
                    console.error('Error checking verification status:', error);
                }
            }, 2000); // Check every 2 seconds
        }

        // Skip priority and continue as regular customer
        function skipPriority() {
            if (confirm('Are you sure you want to continue as a regular customer?\n\nYou will lose priority queue benefits.')) {
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                }
                window.location.href = '{{ route("kiosk.review-details") }}?skip_priority=1';
            }
        }

        // Go back to registration
        function goBack() {
            if (confirm('Are you sure you want to go back?\n\nYour current registration will be cancelled.')) {
                if (statusCheckInterval) {
                    clearInterval(statusCheckInterval);
                }
                window.location.href = '{{ route("kiosk.registration") }}';
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

