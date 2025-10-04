/**
 * Session Timeout Management for Kiosk System
 * Handles automatic session timeout detection and redirection
 */

class SessionTimeoutManager {
    constructor(options = {}) {
        this.timeoutDuration = options.timeoutDuration || 60000; // 60 seconds default
        this.warningDuration = options.warningDuration || 10000; // 10 seconds warning
        this.warningCallback = options.warningCallback || null;
        this.timeoutCallback = options.timeoutCallback || null;
        this.resetEvents = options.resetEvents || [
            'mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'
        ];

        this.lastActivity = Date.now();
        this.warningTimer = null;
        this.timeoutTimer = null;
        this.isWarningShown = false;

        this.init();
    }

    init() {
        // Create bound event handler
        this.activityHandler = () => this.resetTimeout();

        // Add event listeners to reset timeout on user activity
        this.resetEvents.forEach(event => {
            document.addEventListener(event, this.activityHandler, true);
        });

        // Start the timeout timer
        this.startTimeoutTimer();
    }

    startTimeoutTimer() {
        this.timeoutTimer = setTimeout(() => {
            this.showWarning();
        }, this.timeoutDuration - this.warningDuration);
    }

    showWarning() {
        if (this.isWarningShown) return;

        this.isWarningShown = true;

        // Debug logging
        console.log('Session timeout warning triggered at:', new Date().toLocaleTimeString());

        // Call warning callback if provided
        if (this.warningCallback) {
            this.warningCallback();
        } else {
            // Default warning behavior - redirect to session timeout page
            this.redirectToTimeoutPage();
        }

        // Set final timeout
        this.warningTimer = setTimeout(() => {
            this.handleTimeout();
        }, this.warningDuration);
    }

    redirectToTimeoutPage() {
        // Store current page for potential return
        sessionStorage.setItem('timeoutReturnUrl', window.location.href);

        // Redirect to session timeout page
        window.location.href = '/kiosk/session-timeout';
    }

    handleTimeout() {
        // Call timeout callback if provided
        if (this.timeoutCallback) {
            this.timeoutCallback();
        } else {
            // Default timeout behavior - redirect to attract screen
            window.location.href = '/';
        }
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

        this.isWarningShown = false;

        // Debug logging
        console.log('Session timeout reset due to user activity at:', new Date().toLocaleTimeString());

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

        // Clear timers
        if (this.timeoutTimer) {
            clearTimeout(this.timeoutTimer);
        }
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }
    }

    // Manual methods for testing or special cases
    triggerWarning() {
        this.showWarning();
    }

    extendSession() {
        this.resetTimeout();
    }
}

// Global instance for easy access
let sessionTimeout = null;

// Initialize session timeout manager when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Check if we're on the session timeout page
    if (window.location.pathname === '/kiosk/session-timeout') {
        console.log('Session timeout manager not initialized - on timeout page');
        return; // Don't initialize timeout manager on the timeout page itself
    }

    console.log('Initializing session timeout manager...');

    // Initialize with default settings
    sessionTimeout = new SessionTimeoutManager({
        timeoutDuration: 30000, // 30 seconds for testing (change back to 60000 for production)
        warningDuration: 10000, // 10 seconds warning
        // Remove custom warningCallback to use default redirect behavior
    });

    // Make it globally accessible for debugging
    window.sessionTimeout = sessionTimeout;

    console.log('Session timeout manager initialized successfully');
    console.log('Monitoring events:', sessionTimeout.resetEvents);
});

// Utility functions for other scripts to use
window.SessionTimeoutUtils = {
    reset: function () {
        if (sessionTimeout) {
            sessionTimeout.resetTimeout();
        }
    },

    extend: function () {
        if (sessionTimeout) {
            sessionTimeout.extendSession();
        }
    },

    trigger: function () {
        if (sessionTimeout) {
            sessionTimeout.triggerWarning();
        }
    },

    destroy: function () {
        if (sessionTimeout) {
            sessionTimeout.destroy();
            sessionTimeout = null;
        }
    }
};
