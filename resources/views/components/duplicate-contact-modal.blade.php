<!DOCTYPE html>
<!-- Duplicate Contact Warning Modal -->
<div id="duplicateContactModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
    <div class="modal-overlay bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-0 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-red-50 border-b border-red-200 px-6 py-4" style="background-color: #111827;">
            <div class="flex items-center justify-center">
                <div class="warning-icon bg-red-100 rounded-full p-3 mr-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">Duplicate Contact Number</h3>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-6">
            <div class="text-center">
                <p class="text-gray-700 text-lg mb-4">
                    This contact number <span class="font-semibold text-red-600" id="duplicateContactNumber">09XXXXXXXXX</span> 
                    is already in the queue at position <span class="font-bold text-primary" id="duplicateQueuePosition">#15</span>.
                </p>
                
                <p class="text-gray-600 mb-4">
                    Would you like to continue anyway or change your contact number?
                </p>

                <!-- Queue Details (Optional) -->
                <div id="queueDetails" class="bg-white border-2 border-gray-200 rounded-xl shadow-lg p-4 mb-4 text-sm" style="display: none;">
                    <div class="grid grid-cols-2 gap-4 text-left">
                        <div>
                            <span class="font-semibold text-gray-600">Estimated Wait:</span>
                            <span id="estimatedWaitTime" class="text-primary ml-2">-</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-600">Total in Queue:</span>
                            <span id="totalInQueue" class="text-primary ml-2">-</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-600">Priority Ahead:</span>
                            <span id="priorityAhead" class="text-primary ml-2">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex justify-center space-x-4">
            <button id="changeNumberBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-xl font-semibold text-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                Change Number
            </button>
            <button id="continueAnywayBtn" class="bg-primary hover:bg-primary-dark text-white px-8 py-3 rounded-xl font-semibold text-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                Continue Anyway
            </button>
        </div>
    </div>
</div>

<style>
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

    @keyframes warningPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    .modal-overlay {
        animation: modalFadeIn 0.3s ease-out;
    }

    .modal-content {
        animation: modalSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .warning-icon {
        animation: warningPulse 2s ease-in-out infinite;
    }
</style>

<script>
// Duplicate Contact Modal Management
class DuplicateContactModal {
    constructor() {
        this.modalElement = document.getElementById('duplicateContactModal');
        this.continueBtn = document.getElementById('continueAnywayBtn');
        this.changeNumberBtn = document.getElementById('changeNumberBtn');
        this.queuePositionSpan = document.getElementById('duplicateQueuePosition');
        this.contactNumberSpan = document.getElementById('duplicateContactNumber');
        
        this.duplicateData = null;
        this.onContinueCallback = null;
        this.onChangeCallback = null;
        
        this.init();
    }

    init() {
        // Add event listeners
        this.continueBtn.addEventListener('click', () => this.handleContinue());
        this.changeNumberBtn.addEventListener('click', () => this.handleChangeNumber());
        
        // Close modal with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isVisible()) {
                this.handleChangeNumber();
            }
        });
    }

    show(duplicateData, onContinue, onChange) {
        this.duplicateData = duplicateData;
        this.onContinueCallback = onContinue;
        this.onChangeCallback = onChange;
        
        // Update queue position
        if (duplicateData && duplicateData.queue_position) {
            this.queuePositionSpan.textContent = `#${duplicateData.queue_position}`;
        }
        
        // Update contact number (use entered contact if available, otherwise use existing customer's contact)
        if (duplicateData) {
            if (duplicateData.enteredContact) {
                this.contactNumberSpan.textContent = duplicateData.enteredContact;
            } else if (duplicateData.customer && duplicateData.customer.contact_number) {
                this.contactNumberSpan.textContent = duplicateData.customer.contact_number;
            }
        }
        
        // Update dynamic queue information
        this.updateQueueDetails(duplicateData);
        
        // Show modal
        this.modalElement.style.display = 'flex';
        
        // Add body scroll lock
        document.body.style.overflow = 'hidden';
    }

    updateQueueDetails(duplicateData) {
        const queueDetails = document.getElementById('queueDetails');
        const estimatedWaitTime = document.getElementById('estimatedWaitTime');
        const totalInQueue = document.getElementById('totalInQueue');
        const priorityAhead = document.getElementById('priorityAhead');
        
        if (duplicateData) {
            // Show queue details section
            queueDetails.style.display = 'block';
            
            // Update estimated wait time with formatted display
            if (duplicateData.estimated_wait_minutes) {
                const formattedWait = this.formatWaitTime(duplicateData.estimated_wait_minutes);
                estimatedWaitTime.textContent = formattedWait;
            }
            
            // Update total in queue
            if (duplicateData.queue_info && duplicateData.queue_info.total_active_customers) {
                totalInQueue.textContent = duplicateData.queue_info.total_active_customers;
            }
            
            // Update priority customers ahead
            if (duplicateData.queue_info && duplicateData.queue_info.priority_customers_ahead !== undefined) {
                priorityAhead.textContent = duplicateData.queue_info.priority_customers_ahead;
            }
        } else {
            // Hide queue details if no data
            queueDetails.style.display = 'none';
        }
    }

    hide() {
        this.modalElement.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    isVisible() {
        return this.modalElement.style.display === 'flex';
    }

    formatWaitTime(minutes) {
        if (minutes < 1) {
            return 'Less than 1 minute';
        } else if (minutes <= 5) {
            return '≈ 5 minutes';
        } else if (minutes <= 10) {
            return '5-10 minutes';
        } else if (minutes <= 15) {
            return '10-15 minutes';
        } else if (minutes <= 30) {
            return '15-30 minutes';
        } else {
            return '30-45 minutes';
        }
    }

    handleContinue() {
        console.log('Continuing with registration - will replace existing entry');
        
        // Call the continue callback if provided
        if (this.onContinueCallback) {
            this.onContinueCallback(this.duplicateData);
        }
        
        this.hide();
    }

    handleChangeNumber() {
        console.log('Returning to form to change number');
        
        // Call the change callback if provided
        if (this.onChangeCallback) {
            this.onChangeCallback();
        }
        
        this.hide();
    }
}

// Global instance
let duplicateContactModal = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    duplicateContactModal = new DuplicateContactModal();
    window.duplicateContactModal = duplicateContactModal;
});
</script>
