/**
 * Session Timeout Modal Management for Kiosk System
 * Shows a modal overlay when user is inactive during transaction
 */

class SessionTimeoutModal {
    constructor(options = {}) {
        this.timeoutDuration = options.timeoutDuration || 60000; // 60 seconds default
        this.warningDuration = options.warningDuration || 10000; // 10 seconds warning
        this.resetEvents = options.resetEvents || [
            'mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'
        ];

        this.lastActivity = Date.now();
        this.warningTimer = null;
        this.timeoutTimer = null;
        this.isModalShown = false;
        this.modalElement = null;

        this.init();
    }

    init() {
        // Create bound event handler
        this.activityHandler = () => this.resetTimeout();

        // Add event listeners to reset timeout on user activity
        this.resetEvents.forEach(event => {
            document.addEventListener(event, this.activityHandler, true);
        });

        // Create modal HTML
        this.createModal();

        // Start the timeout timer
        this.startTimeoutTimer();
    }

    createModal() {
        // Create modal HTML
        const modalHTML = `
            <div id="sessionTimeoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-8" style="display: none;">
                <!-- Modal Overlay -->
                <div class="absolute inset-0 bg-black/70 backdrop-blur-md"></div>
                
                <!-- Modal Container -->
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 overflow-hidden">
                    <!-- Modal Header -->
                    <div style="background-color: #111827;" class="px-8 py-6 flex items-center justify-center">
                        <h2 class="text-white text-2xl font-bold">Session Timeout</h2>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-10 py-12 flex flex-col items-center text-center space-y-8">
                        <!-- Timeout Icon with Countdown Circle -->
                        <div class="relative">
                            <!-- Countdown Circle SVG -->
                            <div class="countdown-circle" style="width: 160px; height: 160px; position: relative;">
                                <svg width="160" height="160" style="transform: rotate(-90deg);">
                                    <circle class="circle-bg" cx="80" cy="80" r="72" fill="none" stroke="#e5e7eb" stroke-width="8"></circle>
                                    <circle id="progressCircle" class="circle-progress" cx="80" cy="80" r="72" fill="none" stroke="#ef4444" stroke-width="8" stroke-linecap="round" style="transition: stroke-dashoffset 1s linear;"></circle>
                                </svg>
                                
                                <!-- Icon in Center -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-20 h-20 bg-amber-500 rounded-full flex items-center justify-center shadow-lg" style="animation: timeoutPulse 2s ease-in-out infinite;">
                                        <i class="fas fa-clock text-white text-4xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Countdown Timer -->
                        <div class="space-y-2">
                            <div class="text-6xl font-bold text-gray-900" id="countdownTimer">10</div>
                            <p class="text-gray-500 text-lg">seconds remaining</p>
                        </div>

                        <!-- Timeout Message -->
                        <div class="space-y-4 max-w-md">
                            <h3 class="text-gray-900 text-2xl font-bold">
                                Your session has expired due to inactivity
                            </h3>
                            <p class="text-gray-600 text-lg leading-relaxed">
                                You will be automatically returned to the start screen shortly.
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-10 py-8 border-t-2 border-gray-200 flex justify-center space-x-4">
                        <!-- Continue Button -->
                        <button 
                            id="continueSessionBtn"
                            class="px-16 py-5 bg-green-500 hover:bg-green-600 text-white font-bold text-xl rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105 flex items-center space-x-3"
                        >
                            <i class="fas fa-play text-xl"></i>
                            <span>Continue Session</span>
                        </button>

                        <!-- Go Back Button -->
                        <button 
                            id="returnToStartBtn"
                            style="background-color: #111827;"
                            class="px-16 py-5 hover:bg-gray-800 text-white font-bold text-xl rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center space-x-3"
                        >
                            <i class="fas fa-home text-xl"></i>
                            <span>Return to Start</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modalElement = document.getElementById('sessionTimeoutModal');

        // Add CSS animations
        this.addModalStyles();

        // Add event listeners to modal buttons
        document.getElementById('continueSessionBtn').addEventListener('click', () => this.continueSession());
        document.getElementById('returnToStartBtn').addEventListener('click', () => this.returnToStart());

        // Initialize countdown circle
        this.initCountdownCircle();
    }

    addModalStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes timeoutPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            @keyframes modalFadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes modalSlideUp {
                from {
                    opacity: 0;
                    transform: translateY(50px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            #sessionTimeoutModal {
                animation: modalFadeIn 0.3s ease-out;
            }
            #sessionTimeoutModal .relative {
                animation: modalSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
        `;
        document.head.appendChild(style);
    }

    initCountdownCircle() {
        const circle = document.getElementById('progressCircle');
        const radius = 72;
        const circumference = 2 * Math.PI * radius;
        circle.style.strokeDasharray = circumference;
        circle.style.strokeDashoffset = 0;
    }

    startTimeoutTimer() {
        this.timeoutTimer = setTimeout(() => {
            this.showModal();
        }, this.timeoutDuration - this.warningDuration);
    }

    showModal() {
        if (this.isModalShown) return;

        this.isModalShown = true;

        // Show modal
        this.modalElement.style.display = 'flex';

        // Start countdown
        this.startCountdown();
    }

    startCountdown() {
        let remainingSeconds = this.warningDuration / 1000;
        const circle = document.getElementById('progressCircle');
        const radius = 72;
        const circumference = 2 * Math.PI * radius;

        const updateCountdown = () => {
            // Update timer text
            document.getElementById('countdownTimer').textContent = remainingSeconds;

            // Update circle progress
            const progress = remainingSeconds / (this.warningDuration / 1000);
            const offset = circumference * (1 - progress);
            circle.style.strokeDashoffset = offset;

            // Change color as time runs out
            if (remainingSeconds <= 3) {
                circle.style.stroke = '#dc2626'; // Red
            } else if (remainingSeconds <= 5) {
                circle.style.stroke = '#f59e0b'; // Amber
            } else {
                circle.style.stroke = '#ef4444'; // Default red
            }

            remainingSeconds--;

            if (remainingSeconds < 0) {
                this.returnToStart();
            } else {
                setTimeout(updateCountdown, 1000);
            }
        };

        updateCountdown();
    }

    continueSession() {
        // Hide modal
        this.modalElement.style.display = 'none';
        this.isModalShown = false;

        // Reset timeout
        this.resetTimeout();
    }

    returnToStart() {
        // Clear any stored session data
        sessionStorage.clear();

        // Redirect to attract screen
        window.location.href = '/';
    }

    resetTimeout() {
        this.lastActivity = Date.now();

        // Clear existing timers
        if (this.timeoutTimer) {
            clearTimeout(this.timeoutTimer);
        }
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }

        this.isModalShown = false;

        // Restart timeout timer
        this.startTimeoutTimer();
    }

    destroy() {
        // Remove event listeners
        if (this.activityHandler) {
            this.resetEvents.forEach(event => {
                document.removeEventListener(event, this.activityHandler, true);
            });
        }

        // Remove modal from DOM
        if (this.modalElement) {
            this.modalElement.remove();
        }

        // Clear timers
        if (this.timeoutTimer) {
            clearTimeout(this.timeoutTimer);
        }
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }
    }

    // Manual methods for testing
    triggerModal() {
        this.showModal();
    }

    extendSession() {
        this.resetTimeout();
    }
}

// Global instance for easy access
let sessionTimeoutModal = null;

// Initialize session timeout modal when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Check if we're on the session timeout page (don't initialize there)
    if (window.location.pathname === '/kiosk/session-timeout') {
        return;
    }

    // Check if we're on attract screen - only initialize if T&C modal is showing
    if (window.location.pathname === '/') {
        // Only initialize timeout if Terms & Conditions modal is active
        if (!isTermsModalActive()) {
            console.log('Attract screen detected - session timeout disabled unless T&C modal is active');
            return;
        }
    }

    console.log('Initializing session timeout modal...');

    // Initialize with default settings
    sessionTimeoutModal = new SessionTimeoutModal({
        timeoutDuration: 50000, // 50 seconds total timeout (20s until modal + 30s warning)
        warningDuration: 30000, // 30 seconds warning countdown
    });

    // Make it globally accessible for debugging
    window.sessionTimeoutModal = sessionTimeoutModal;

    console.log('Session timeout modal initialized successfully');
});

// Function to check if Terms & Conditions modal is active
function isTermsModalActive() {
    const termsModal = document.getElementById('termsModal');
    return termsModal && termsModal.style.display !== 'none' && termsModal.style.display !== '';
}

// Function to initialize session timeout when T&C modal becomes active
function initializeTimeoutOnTermsModal() {
    if (window.location.pathname === '/' && !sessionTimeoutModal) {
        console.log('T&C modal activated - initializing session timeout');

        sessionTimeoutModal = new SessionTimeoutModal({
            timeoutDuration: 50000, // 50 seconds total timeout (20s until modal + 30s warning)
            warningDuration: 30000, // 30 seconds warning countdown
        });

        window.sessionTimeoutModal = sessionTimeoutModal;
    }
}

// Function to destroy session timeout when T&C modal is closed
function destroyTimeoutOnTermsModalClose() {
    if (window.location.pathname === '/' && sessionTimeoutModal) {
        console.log('T&C modal closed - destroying session timeout');

        sessionTimeoutModal.destroy();
        sessionTimeoutModal = null;
        window.sessionTimeoutModal = null;
    }
}

// Utility functions for other scripts to use
window.SessionTimeoutModalUtils = {
    reset: function () {
        if (sessionTimeoutModal) {
            sessionTimeoutModal.resetTimeout();
        }
    },

    extend: function () {
        if (sessionTimeoutModal) {
            sessionTimeoutModal.extendSession();
        }
    },

    trigger: function () {
        if (sessionTimeoutModal) {
            sessionTimeoutModal.triggerModal();
        }
    },

    destroy: function () {
        if (sessionTimeoutModal) {
            sessionTimeoutModal.destroy();
            sessionTimeoutModal = null;
        }
    }
};
