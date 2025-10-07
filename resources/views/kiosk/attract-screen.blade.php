<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Gervacio's - Digital Seating</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>
    tailwind.config = {
      theme: {
          extend: {
              colors: {
                primary: '#09121E',
                'primary-dark': '#061018',
                secondary: '#2c3e50',
                accent: '#f39c12',
                neutral: '#f5f7fa',
                'neutral-dark': '#e3e8ef',
                'cream-button': '#EEEDE7'
              },
              fontFamily: {
                  'inter': ['Inter', 'sans-serif']
              }
          }
      }
    }
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        /* Smooth animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes gentleFloat {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-8px);
            }
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }
            100% {
                background-position: 200% center;
            }
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .animate-float {
            animation: gentleFloat 3s ease-in-out infinite;
        }

        .animate-scale-in {
            animation: scaleIn 0.6s ease-out forwards;
        }

        .shimmer-effect {
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(255,255,255,0.1) 50%, 
                transparent 100%);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }

        /* Modal animations */
        .modal-overlay {
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-content {
            animation: modalSlideUp 0.4s ease-out;
        }

        /* Delay animations */
        .delay-100 { animation-delay: 0.1s; opacity: 0; }
        .delay-200 { animation-delay: 0.2s; opacity: 0; }
        .delay-300 { animation-delay: 0.3s; opacity: 0; }
        .delay-400 { animation-delay: 0.4s; opacity: 0; }
        .delay-500 { animation-delay: 0.5s; opacity: 0; }

        /* Hide modal by default */
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body class="font-inter">
    <!-- Background Image with Blue Blur -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
         style="background-image: url('/images/restaurant-interior.jpg'); filter: blur(3px) brightness(0.4);">
        <!-- Blue Brand Color Overlay -->
        <div class="absolute inset-0" style="background-color: #09121E; opacity: 0.7;"></div>
    </div>

    <!-- Clickable Overlay to Trigger Modal -->
    <div onclick="showTermsModal()" class="absolute inset-0 z-5 cursor-pointer"></div>

    <!-- Header with Logo and Time -->
    <header class="absolute top-0 left-0 right-0 z-20 p-6 pointer-events-none">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <div class="animate-fade-in delay-100">
                <img src="/images/gervacios-logo.png" alt="Gervacio's Logo" class="h-32 w-auto drop-shadow-2xl filter brightness-110">
            </div>

            <!-- Date and Time -->
            <div class="text-right text-white animate-fade-in delay-200">
                <div id="current-time" class="text-2xl font-bold"></div>
                <div id="current-date" class="text-sm opacity-80"></div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="h-full w-full flex flex-col items-center justify-center relative z-10 px-8 pointer-events-none">
        <div class="text-center max-w-5xl space-y-12">
            
            <!-- Priority Access Banner -->
            <div class="animate-fade-in delay-300">
                <div class="inline-flex items-center bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-8 py-4 space-x-6">
                    <div class="flex items-center space-x-6">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mb-2 shadow-lg border-2 border-white/30">
                                <i class="fa-solid fa-person-cane text-2xl text-white"></i>
                            </div>
                            <span class="text-white text-xs font-medium">Seniors</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-full flex items-center justify-center mb-2 shadow-lg border-2 border-white/30">
                                <i class="fa-solid fa-wheelchair text-2xl text-white"></i>
                            </div>
                            <span class="text-white text-xs font-medium">PWD</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-full flex items-center justify-center mb-2 shadow-lg border-2 border-white/30">
                                <i class="fa-solid fa-baby text-2xl text-white"></i>
                            </div>
                            <span class="text-white text-xs font-medium">Pregnant</span>
                        </div>
                    </div>
                    <div class="border-l border-white/30 pl-6">
                        <div class="text-white font-bold text-lg">Priority Access</div>
                        <div class="text-white/80 text-sm">We care for everyone</div>
                    </div>
                </div>
            </div>

            <!-- Main Heading -->
            <div class="animate-fade-in delay-400">
                <h2 class="text-7xl lg:text-9xl font-bold text-white leading-none mb-4">
                    Reserve Your
                </h2>
                <h2 class="text-7xl lg:text-9xl font-bold text-cream-button leading-none">
                    Seat Here
                </h2>
            </div>

            <!-- CTA Button -->
            <div class="animate-scale-in delay-500">
                <button onclick="showTermsModal()" 
                        class="group bg-cream-button hover:bg-white text-primary font-bold py-5 px-16 rounded-xl transition-all duration-300 flex items-center justify-center gap-4 text-2xl shadow-2xl hover:shadow-3xl transform hover:scale-105 mx-auto animate-float cursor-pointer pointer-events-auto">
                    <i class="fa-solid fa-chair text-3xl group-hover:rotate-12 transition-transform duration-300"></i>
                    <span>Tap to Get a Seat</span>
                    <i class="fa-solid fa-arrow-right text-2xl group-hover:translate-x-2 transition-transform duration-300"></i>
                </button>
            </div>

            <!-- Footer Text -->
            <div class="animate-fade-in delay-500">
                <p class="text-white/90 text-xl leading-relaxed max-w-3xl mx-auto">
                    Register your party → Get queue number → Wait for notification → Be seated
                </p>
            </div>

            <!-- Stats Row -->
            <div class="animate-fade-in delay-500">
                <div class="flex justify-center items-center space-x-12 text-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium">Available Now</span>
                    </div>
                    <div class="h-8 w-px bg-white/30"></div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-clock text-cream-button"></i>
                        <span class="text-sm font-medium">Average Wait: 15-20 mins</span>
                    </div>
                    <div class="h-8 w-px bg-white/30"></div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-users text-cream-button"></i>
                        <span class="text-sm font-medium">8 Parties Waiting</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Terms & Conditions Modal -->
    <div id="termsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Modal Overlay -->
        <div class="modal-overlay absolute inset-0 bg-black/70 backdrop-blur-md"></div>

        <!-- Modal Container -->
        <div
            class="modal-content relative bg-white rounded-3xl shadow-2xl max-w-3xl w-full mx-4 max-h-[85vh] flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-primary px-8 py-5">
                <h2 class="text-white text-2xl font-bold text-center">Terms & Conditions & Data Privacy Consent</h2>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto px-8 py-6 space-y-6">
                <!-- Introduction -->
                <div class="text-center">
                    <p class="text-gray-700 text-base">
                        By proceeding, you agree to our <span class="text-red-600 font-semibold">Terms &
                            Conditions</span> and Data Privacy Policy.
                    </p>
                </div>

                <!-- Use of Information Section -->
                <div class="space-y-3">
                    <h3 class="text-gray-900 text-xl font-bold">Use of Information</h3>
                    <p class="text-gray-600 text-base leading-relaxed">
                        We collect your name, contact number, and group details solely for seat assignment and queue
                        management. This information is processed securely and used exclusively for providing our
                        services to you.
                    </p>
                </div>

                <!-- Priority Guests Section -->
                <div class="space-y-3">
                    <h3 class="text-gray-900 text-xl font-bold">Priority Guests</h3>
                    <p class="text-gray-600 text-base leading-relaxed">
                        If your group includes a Senior Citizen, PWD, or Pregnant Guest, verification may be required
                        for fairness. We ensure equal treatment and prioritize accessibility for all our guests.
                    </p>
                </div>

                <!-- Consent Checkbox -->
                <div class="pt-4">
                    <label class="flex items-start space-x-3 cursor-pointer group">
                        <input type="checkbox" id="consentCheckbox" onchange="toggleAcceptButton()"
                            class="mt-1 w-5 h-5 rounded border-2 border-gray-400 text-primary focus:ring-2 focus:ring-primary cursor-pointer">
                        <span class="text-gray-800 text-base font-medium group-hover:text-gray-900 select-none">
                            I agree to the Terms & Conditions and Data Privacy Consent
                        </span>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 border-t border-gray-200 flex items-center justify-center space-x-4">
                <!-- Decline Button -->
                <button onclick="declineTerms()"
                    class="flex-1 max-w-xs px-8 py-4 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-semibold text-lg rounded-2xl transition-all duration-200 hover:shadow-md">
                    Decline
                </button>

                <!-- Accept Button -->
                <button id="acceptBtn" onclick="acceptTerms()" disabled
                    class="flex-1 max-w-xs px-8 py-4 bg-primary text-white font-semibold text-lg rounded-2xl transition-all duration-200 opacity-50 cursor-not-allowed">
                    Accept & Continue
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal Functions
        function showTermsModal() {
            const modal = document.getElementById('termsModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Initialize session timeout when T&C modal is shown
            if (typeof initializeTimeoutOnTermsModal === 'function') {
                initializeTimeoutOnTermsModal();
            }
        }

        function closeTermsModal() {
            const modal = document.getElementById('termsModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            
            // Reset checkbox and button
            document.getElementById('consentCheckbox').checked = false;
            toggleAcceptButton();
            
            // Destroy session timeout when T&C modal is closed
            if (typeof destroyTimeoutOnTermsModalClose === 'function') {
                destroyTimeoutOnTermsModalClose();
            }
        }

        function toggleAcceptButton() {
            const checkbox = document.getElementById('consentCheckbox');
            const acceptBtn = document.getElementById('acceptBtn');

            if (checkbox.checked) {
                acceptBtn.disabled = false;
                acceptBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                acceptBtn.classList.add('hover:bg-primary-dark', 'hover:shadow-md', 'cursor-pointer');
            } else {
                acceptBtn.disabled = true;
                acceptBtn.classList.add('opacity-50', 'cursor-not-allowed');
                acceptBtn.classList.remove('hover:bg-primary-dark', 'hover:shadow-md', 'cursor-pointer');
            }
        }

        function acceptTerms() {
            const acceptBtn = document.getElementById('acceptBtn');
            
            // Show processing state
            acceptBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            acceptBtn.disabled = true;
            
            // Call backend API to log acceptance
            fetch('{{ route("kiosk.terms.accept") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Terms accepted and logged. Redirecting to registration...');
                    window.location.href = data.redirect_to;
                } else {
                    alert('Failed to record consent. Please try again.');
                    acceptBtn.innerHTML = 'Accept & Continue';
                    acceptBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                acceptBtn.innerHTML = 'Accept & Continue';
                acceptBtn.disabled = false;
            });
        }

        function declineTerms() {
            // Call backend API to log declination
            fetch('{{ route("kiosk.terms.decline") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Terms declined and logged.');
                closeTermsModal();
            })
            .catch(error => {
                console.error('Error logging declination:', error);
                closeTermsModal();
            });
        }

        // Time and Date Update
        function updateTimeAndDate() {
            const now = new Date();
            const timeElement = document.getElementById('current-time');
            const dateElement = document.getElementById('current-date');
            
            if (timeElement && dateElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
                dateElement.textContent = now.toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    month: 'short', 
                    day: 'numeric',
                    year: 'numeric'
                });
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateTimeAndDate();
            setInterval(updateTimeAndDate, 1000);
        });

        // Auto-refresh every 2 minutes (disabled when session timeout is active)
        let autoRefreshTimeout;
        function startAutoRefresh() {
            autoRefreshTimeout = setTimeout(() => {
                // Only refresh if session timeout is not active
                if (!window.sessionTimeout || !window.sessionTimeout.isWarningShown) {
                    location.reload();
                } else {
                    // If session timeout is active, restart auto-refresh
                    startAutoRefresh();
                }
            }, 120000);
        }
        
        // Start auto-refresh
        startAutoRefresh();
    </script>
    
    <!-- Session Timeout Modal Manager -->
    <script src="{{ asset('js/session-timeout-modal.js') }}"></script>
</body>
</html>