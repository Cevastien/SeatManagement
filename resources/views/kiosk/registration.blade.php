<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Guest Waitlist - Kiosk</title>
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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }

        .input-field {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(17, 24, 39, 0.12);
        }

        .input-field:hover:not(:focus) {
            border-color: #6b7280;
        }

        .input-field.valid {
            border-color: #10b981;
            background-color: #f0fdf4;
        }

        .input-field.error {
            border-color: #ef4444;
            background-color: #fef2f2;
            animation: shake 0.5s;
        }

        .counter-btn {
            transition: all 0.2s ease;
            user-select: none;
        }

        .counter-btn:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .counter-btn:active:not(:disabled) {
            transform: scale(0.95);
        }

        .counter-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .radio-option {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .radio-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #9ca3af;
        }

        input[type="radio"]:checked + .radio-option {
            background: #111827;
            border-color: #111827;
            color: white;
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(17, 24, 39, 0.25);
        }

        input[type="radio"]:checked + .radio-option * {
            color: white;
        }

        .priority-section {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .priority-section.show {
            max-height: 500px;
            opacity: 1;
            margin-top: 2rem;
        }

        .priority-section.hide {
            max-height: 0;
            opacity: 0;
            margin-top: 0;
            overflow: hidden;
        }

        .contact-prefix {
            pointer-events: none;
            user-select: none;
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
                        <h1 class="text-2xl font-semibold text-white">Guest Information</h1>
                        <span class="px-3 py-1 text-white text-xs font-semibold rounded-full"
                            style="background-color: #374151;">
                            Step 1 of 4
                        </span>
                    </div>
                    <p class="text-gray-300 text-sm">Please provide your party details</p>
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
    <div class="flex-1 flex items-start justify-center px-8 py-4 bg-gray-100" style="overflow-y: auto;">
        <div class="w-full max-w-4xl mx-auto" style="margin-top: 1rem;">
            <form id="registrationForm" class="space-y-8">

                <!-- Name/Nickname -->
                <div>
                    <label class="block">
                        <div class="flex items-center space-x-2 mb-3">
                            <i class="fas fa-user text-gray-700 text-lg"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Name or Nickname</h3>
                            <span class="text-red-500 text-base">*</span>
                        </div>
                        <input type="text" id="name" name="name" placeholder="Enter your name"
                            class="input-field w-full px-5 py-4 border-3 border-gray-300 rounded-xl text-lg bg-white focus:border-gray-900 focus:outline-none"
                            oninput="handleNameInput()">
                        <p class="text-xs text-gray-600 mt-2">
                            Representative's name for priority guests
                        </p>
                    </label>
                </div>

                <!-- Party Size -->
                <div>
                    <div class="flex items-center space-x-2 mb-3">
                        <i class="fas fa-users text-gray-700 text-lg"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Party Size</h3>
                        <span class="text-red-500 text-base">*</span>
                    </div>

                    <div class="flex items-center justify-center space-x-6 my-4">
                        <button type="button" onclick="decrementPartySize()"
                            class="counter-btn w-20 h-20 bg-gray-100 border-3 border-gray-300 rounded-xl flex items-center justify-center">
                            <span class="text-4xl text-gray-700 font-light">−</span>
                        </button>
                        <input type="text" id="party_size" value="1" readonly
                            class="w-28 text-center px-4 py-3 border-3 border-gray-400 rounded-xl text-4xl font-bold bg-gray-50 cursor-default">
                        <button type="button" onclick="incrementPartySize()"
                            class="counter-btn w-20 h-20 bg-gray-900 text-white rounded-xl flex items-center justify-center">
                            <span class="text-4xl font-light">+</span>
                        </button>
                    </div>

                    <p class="text-xs text-gray-600 text-center" id="partySizeHelpText">
                        Maximum 50 guests. For larger groups, contact staff.
                    </p>
                </div>

                <!-- Contact Number -->
                <div>
                    <div class="flex items-center space-x-2 mb-3">
                        <i class="fas fa-phone text-gray-700 text-lg"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Contact Number</h3>
                        <span class="text-gray-500 text-xs">(Optional)</span>
                    </div>

                    <div class="relative">
                        <div
                            class="contact-prefix absolute left-5 top-1/2 transform -translate-y-1/2 text-lg text-gray-500 font-semibold">
                            09
                        </div>
                        <input type="text" id="contact" name="contact" placeholder="XX XXX XXXX"
                            class="input-field w-full px-5 py-4 pl-16 border-3 border-gray-300 rounded-xl text-lg bg-white focus:border-gray-900 focus:outline-none"
                            oninput="handleContactInput()" onkeypress="handleContactInput()"
                            onkeydown="preventExcessInput(event)" onpaste="setTimeout(handleContactInput, 0)"
                            maxlength="9" pattern="[0-9]*" inputmode="numeric">
                    </div>
                    <p class="text-xs text-gray-600 mt-2">
                        We'll notify you when your table is ready
                    </p>
                </div>

                <!-- Priority Check -->
                <div id="prioritySection" class="priority-section hide">
                    <div>
                        <div class="flex items-center space-x-2 mb-3">
                            <i class="fas fa-star text-gray-700 text-lg"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Priority Guest?</h3>
                            <span class="text-red-500 text-base">*</span>
                        </div>

                        <p class="text-sm text-gray-600 mb-4">
                            Does your party include a Senior Citizen (60+), PWD, or Pregnant Guest?
                        </p>

                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative">
                                <input type="radio" name="is_priority" value="1" class="sr-only"
                                    onchange="showPriorityModal()">
                                <div class="radio-option bg-white border-3 border-gray-300 rounded-xl p-5 text-center">
                                    <i class="fas fa-check-circle text-2xl mb-2 text-gray-400"></i>
                                    <p class="font-semibold text-base text-gray-700">Yes</p>
                                </div>
                            </label>

                            <label class="relative">
                                <input type="radio" name="is_priority" value="0" class="sr-only" checked>
                                <div class="radio-option bg-white border-3 border-gray-300 rounded-xl p-5 text-center">
                                    <i class="fas fa-times-circle text-2xl mb-2 text-gray-400"></i>
                                    <p class="font-semibold text-base text-gray-700">No</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bg-white px-8 py-6 flex-shrink-0 shadow-lg">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <button onclick="goBack()"
                class="px-16 py-6 bg-white hover:bg-gray-50 border-3 border-gray-400 text-gray-800 font-semibold text-xl rounded-2xl transition-all duration-200 flex items-center space-x-3 shadow-md hover:shadow-lg">
                <i class="fas fa-arrow-left text-2xl"></i>
                <span>Go Back</span>
            </button>

            <button type="button" id="continueBtn" onclick="submitForm()" style="background-color: #111827;"
                class="px-20 py-6 hover:bg-black text-white font-semibold text-xl rounded-2xl shadow-xl transition-all duration-200 flex items-center space-x-3 hover:shadow-2xl hover:scale-105">
                <span>Continue</span>
                <i class="fas fa-arrow-right text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Priority Type Modal -->
    <div id="priorityModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        style="display: none; z-index: 1000;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 p-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6 text-center">Priority Guest Type</h2>

            <div class="space-y-4 mb-8">
                <label
                    class="flex items-start space-x-4 p-5 border-2 border-gray-200 rounded-xl hover:border-primary hover:bg-gray-50 cursor-pointer transition">
                    <input type="radio" name="priority_type" value="senior" class="mt-1"
                        onchange="selectPriorityType(this)">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <i class="fas fa-user text-2xl text-gray-700"></i>
                            <h3 class="font-semibold text-lg text-gray-900">Senior Citizens (60+)</h3>
                        </div>
                        <p class="text-sm text-gray-600">A valid ID is preferred. If unavailable, please approach our
                            staff for assistance.</p>
                    </div>
                </label>

                <label
                    class="flex items-start space-x-4 p-5 border-2 border-gray-200 rounded-xl hover:border-primary hover:bg-gray-50 cursor-pointer transition">
                    <input type="radio" name="priority_type" value="pwd" class="mt-1"
                        onchange="selectPriorityType(this)">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <i class="fas fa-wheelchair text-2xl text-gray-700"></i>
                            <h3 class="font-semibold text-lg text-gray-900">Persons with Disabilities (PWD)</h3>
                        </div>
                        <p class="text-sm text-gray-600">A valid ID is preferred. If unavailable, please approach our
                            staff for assistance.</p>
                    </div>
                </label>

                <label
                    class="flex items-start space-x-4 p-5 border-2 border-gray-200 rounded-xl hover:border-primary hover:bg-gray-50 cursor-pointer transition">
                    <input type="radio" name="priority_type" value="pregnant" class="mt-1"
                        onchange="selectPriorityType(this)">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <i class="fas fa-user-plus text-2xl text-gray-700"></i>
                            <h3 class="font-semibold text-lg text-gray-900">Pregnant Guests</h3>
                        </div>
                        <p class="text-sm text-gray-600">No ID required; kindly inform our staff for priority access.
                        </p>
                    </div>
                </label>
            </div>

            <div class="flex space-x-4">
                <button onclick="cancelPriorityModal()"
                    class="flex-1 px-8 py-4 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-semibold text-lg rounded-xl transition">
                    Cancel
                </button>
                <button onclick="confirmPriorityType()" id="continueModalBtn" disabled
                    class="flex-1 px-8 py-4 text-white font-semibold text-lg rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-90"
                    style="background-color: #101825;">
                    Continue
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="display: none; z-index: 2000;">
        <div class="bg-white rounded-xl p-8 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <span class="text-lg font-semibold">Saving to database...</span>
        </div>
    </div>

    <script>
        // Settings object for dynamic configuration
        let settings = {
            party_size_min: 1,
            party_size_max: 50,
            restaurant_name: 'GERVACIOS RESTAURANT & LOUNGE'
        };

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
        }

        // Load settings from API
        async function loadSettings() {
            try {
                const response = await fetch('/api/settings/public', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    settings = {
                        party_size_min: data.settings.party_size_min || 1,
                        party_size_max: data.settings.party_size_max || 50,
                        restaurant_name: data.settings.restaurant_name || 'GERVACIOS RESTAURANT & LOUNGE'
                    };
                    
                    // Update the UI with dynamic limits
                    updatePartySizeUI();
                }
            } catch (error) {
                console.error('Failed to load settings:', error);
                // Use default values if API fails
            }
        }

        // Update party size UI with dynamic limits
        function updatePartySizeUI() {
            const partySizeInput = document.getElementById('party_size');
            if (partySizeInput) {
                partySizeInput.min = settings.party_size_min;
                partySizeInput.max = settings.party_size_max;
            }
            
            // Update the help text
            const helpText = document.getElementById('partySizeHelpText');
            if (helpText) {
                helpText.textContent = `You may enter between ${settings.party_size_min} and ${settings.party_size_max} people. For groups larger than ${settings.party_size_max}, please approach our staff for assistance.`;
            }
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        let selectedPriorityType = null;
        
        // Initialize form with existing data if editing
        document.addEventListener('DOMContentLoaded', function() {
            // Load settings first
            loadSettings();

            @if($editField && $existingData)
                console.log('Editing mode - existing data:', @json($existingData));
                
                // Set selected priority type if it exists
                @if($existingData && isset($existingData['priority_type']) && $existingData['priority_type'] !== 'normal')
                    selectedPriorityType = '{{ $existingData['priority_type'] }}';
                    console.log('Selected priority type:', selectedPriorityType);
                @endif
                
                // Show priority section if name is already entered (when editing)
                if ('{{ $existingData['name'] ?? '' }}'.trim().length > 0) {
                    showPrioritySection();
                    console.log('Priority section shown because name is set');
                }
            @endif

            // Ensure contact field is properly limited on page load
            const contactField = document.getElementById('contact');
            if (contactField) {
                handleContactInput();
                
                // Set up continuous monitoring to prevent any bypassing of the 9-digit limit
                setInterval(() => {
                    if (contactField.value.length > 9) {
                        contactField.value = contactField.value.substring(0, 9);
                        console.warn('Contact field automatically trimmed to 9 digits');
                    }
                }, 100); // Check every 100ms
            }

            // Check name field on page load to show priority section if name is already entered
            const nameField = document.getElementById('name');
            if (nameField) {
                handleNameInput();
            }

            // Add error clearing event listeners
            setupErrorClearingListeners();
        });

        // Setup event listeners to clear errors when user starts correcting
        function setupErrorClearingListeners() {
            // Name field error clearing
            const nameField = document.getElementById('name');
            if (nameField) {
                nameField.addEventListener('input', function() {
                    if (this.value.trim()) {
                        clearFieldError('name');
                    }
                });
            }

            // Party size field error clearing
            const partySizeField = document.getElementById('party_size');
            if (partySizeField) {
                partySizeField.addEventListener('input', function() {
                    if (parseInt(this.value) >= 1) {
                        clearFieldError('party_size');
                    }
                });
            }

            // Contact field error clearing
            const contactField = document.getElementById('contact');
            if (contactField) {
                contactField.addEventListener('input', function() {
                    const value = this.value.trim();
                    if (value.length >= 1 && value.length <= 9 && /^[0-9]+$/.test(value)) {
                        clearFieldError('contact');
                    }
                });
            }

            // Priority radio buttons error clearing
            document.querySelectorAll('input[name="is_priority"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    clearPrioritySectionError();
                });
            });

            // Priority type selection error clearing
            document.querySelectorAll('input[name="priority_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    clearFieldError('priority_type');
                });
            });
        }

        // Handle name input - show priority section when name is entered
        function handleNameInput() {
            const nameInput = document.getElementById('name');
            const prioritySection = document.getElementById('prioritySection');
            
            if (nameInput.value.trim().length > 0) {
                showPrioritySection();
                console.log('Priority section shown because name is entered');
            } else {
                hidePrioritySection();
                console.log('Priority section hidden because name is empty');
            }
        }

        // Handle contact number input with Philippine format validation
        function handleContactInput() {
            const contactInput = document.getElementById('contact');
            let value = contactInput.value.replace(/\D/g, ''); // Remove non-digits
            
            console.log('Contact input length:', value.length, 'Value:', value);
            
            // If user typed 11 digits starting with 09, strip the first 2 characters
            if (value.startsWith('09') && value.length === 11) {
                value = value.substring(2);
                console.log('Stripped 09 prefix, remaining:', value);
            }
            
            // Force limit to maximum 9 digits - cut off anything beyond that
            if (value.length > 9) {
                console.warn('Contact number exceeds 9 digits, trimming...');
                value = value.substring(0, 9);
                console.warn('Contact number limited to 9 digits. Extra characters removed.');
            }
            
            contactInput.value = value;
            console.log('Final contact input length:', contactInput.value.length);
        }

        // Additional validation function to prevent typing beyond 9 characters
        function preventExcessInput(event) {
            const input = event.target;
            if (input.value.length >= 9 && event.key !== 'Backspace' && event.key !== 'Delete' && event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' && event.key !== 'Tab') {
                event.preventDefault();
                console.warn('Maximum 9 digits allowed for contact number');
            }
        }

        // Show priority type modal when "Yes" is selected
        function showPriorityModal() {
            document.getElementById('priorityModal').style.display = 'flex';
        }

        // Cancel priority modal
        function cancelPriorityModal() {
            document.getElementById('priorityModal').style.display = 'none';
            // Uncheck the "Yes" radio button
            document.querySelector('input[name="is_priority"][value="1"]').checked = false;
            selectedPriorityType = null;
            
            // Scroll back to show the form details after modal closes
            setTimeout(() => {
                const formContainer = document.querySelector('.w-full.max-w-4xl');
                if (formContainer) {
                    formContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start',
                        inline: 'nearest'
                    });
                }
            }, 100); // Small delay to ensure modal is fully closed
        }

        // Handle priority type selection
        function selectPriorityType(radio) {
            selectedPriorityType = radio.value;
            document.getElementById('continueModalBtn').disabled = false;
            document.getElementById('continueModalBtn').classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
        }

        // Confirm priority type selection
        function confirmPriorityType() {
            if (!selectedPriorityType) {
                alert('Please select a priority type');
                return;
            }
            document.getElementById('priorityModal').style.display = 'none';
            
            // Scroll back to show the form details after modal closes
            setTimeout(() => {
                const formContainer = document.querySelector('.w-full.max-w-4xl');
                if (formContainer) {
                    formContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start',
                        inline: 'nearest'
                    });
                }
            }, 100); // Small delay to ensure modal is fully closed
        }

        function goBack() {
            window.location.href = "/";
        }

        // Party size controls
        function incrementPartySize() {
            const input = document.getElementById('party_size');
            const currentValue = parseInt(input.value);
            if (currentValue < settings.party_size_max) {
                input.value = currentValue + 1;
                clearFieldError('party_size'); // Clear any party size errors
                showPrioritySection(); // Show priority section when party size is set
            } else {
                alert(`Party size limit reached!\n\nYou may only enter between ${settings.party_size_min} and ${settings.party_size_max} people. For groups larger than ${settings.party_size_max}, please approach our staff for assistance.`);
            }
        }

        function decrementPartySize() {
            const input = document.getElementById('party_size');
            const currentValue = parseInt(input.value);
            if (currentValue > settings.party_size_min) {
                input.value = currentValue - 1;
                clearFieldError('party_size'); // Clear any party size errors
                showPrioritySection(); // Show priority section when party size is set
            }
        }

        // Validate party size input
        function validatePartySize() {
            const input = document.getElementById('party_size');
            let value = parseInt(input.value) || settings.party_size_min;
            
            // Ensure value is within bounds
            if (value < settings.party_size_min) {
                value = settings.party_size_min;
            } else if (value > settings.party_size_max) {
                value = settings.party_size_max;
            }
            
            input.value = value;
        }

        // Prevent invalid input for party size
        function preventInvalidPartySizeInput(event) {
            // Allow: backspace, delete, tab, escape, enter, arrow keys
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(event.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (event.keyCode === 65 && event.ctrlKey === true) ||
                (event.keyCode === 67 && event.ctrlKey === true) ||
                (event.keyCode === 86 && event.ctrlKey === true) ||
                (event.keyCode === 88 && event.ctrlKey === true)) {
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
                event.preventDefault();
            }
        }

        // Show priority section when party size is entered
        function showPrioritySection() {
            const prioritySection = document.getElementById('prioritySection');
            
            // Check if name is entered before showing priority section
            const nameField = document.getElementById('name');
            if (!nameField.value.trim()) {
                console.log('Priority section not shown because name is empty');
                return;
            }
            
            prioritySection.style.maxHeight = '400px';
            prioritySection.style.opacity = '1';
            prioritySection.style.marginTop = '1.5rem';
            prioritySection.style.overflow = 'visible';
            
            // Scroll to show the priority section smoothly
            setTimeout(() => {
                prioritySection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest',
                    inline: 'nearest'
                });
            }, 300);
            
            console.log('Priority section shown');
        }

        function hidePrioritySection() {
            const prioritySection = document.getElementById('prioritySection');
            prioritySection.style.maxHeight = '0';
            prioritySection.style.opacity = '0';
            prioritySection.style.marginTop = '0';
            prioritySection.style.overflow = 'hidden';
            
            console.log('Priority section hidden');
        }


        // Enhanced validation error display functions
        function showFieldError(fieldId, errorTitle, errorMessage) {
            const field = document.getElementById(fieldId);
            const container = field.closest('div').parentElement;
            
            // Add error styling to field
            field.classList.add('border-red-500', 'bg-red-50', 'error-shake');
            field.classList.remove('border-gray-200');
            
            // Remove existing error message if any
            const existingError = container.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Create error message element
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message flex items-start space-x-2 mt-3 text-red-600';
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-circle text-red-500 text-sm mt-0.5 flex-shrink-0"></i>
                <div>
                    <p class="font-semibold text-sm">${errorTitle}</p>
                    <p class="text-sm">${errorMessage}</p>
                </div>
            `;
            
            // Insert error message after the field
            container.appendChild(errorDiv);
            
            // Scroll to the error field
            setTimeout(() => {
                field.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center',
                    inline: 'nearest'
                });
            }, 100);
        }

        function clearFieldError(fieldId) {
            const field = document.getElementById(fieldId);
            const container = field.closest('div').parentElement;
            
            // Remove error styling from field
            field.classList.remove('border-red-500', 'bg-red-50', 'error-shake');
            field.classList.add('border-gray-200');
            
            // Remove error message
            const existingError = container.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
        }

        function showPrioritySectionError() {
            const prioritySection = document.getElementById('prioritySection');
            
            // Add error styling to the priority section
            prioritySection.classList.add('border-red-500', 'bg-red-50');
            
            // Remove existing error message if any
            const existingError = prioritySection.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Create error message element
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message flex items-start space-x-2 mt-3 text-red-600';
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-circle text-red-500 text-sm mt-0.5 flex-shrink-0"></i>
                <div>
                    <p class="font-semibold text-sm">Priority Question Required</p>
                    <p class="text-sm">Please answer whether your party includes a priority guest.</p>
                </div>
            `;
            
            // Insert error message at the end of priority section
            prioritySection.appendChild(errorDiv);
            
            // Scroll to the priority section
            setTimeout(() => {
                prioritySection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center',
                    inline: 'nearest'
                });
            }, 100);
        }

        function clearPrioritySectionError() {
            const prioritySection = document.getElementById('prioritySection');
            
            // Remove error styling from priority section
            prioritySection.classList.remove('border-red-500', 'bg-red-50');
            
            // Remove error message
            const existingError = prioritySection.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
        }

        function showPriorityTypeError() {
            // Create modal error overlay
            const errorOverlay = document.createElement('div');
            errorOverlay.className = 'fixed inset-0 bg-red-500 bg-opacity-20 flex items-center justify-center';
            errorOverlay.style.zIndex = '1001';
            errorOverlay.id = 'priorityTypeErrorOverlay';
            
            errorOverlay.innerHTML = `
                <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-2xl border-2 border-red-500">
                    <div class="flex items-center space-x-3 mb-4">
                        <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                        <h3 class="text-lg font-bold text-red-800">Selection Required</h3>
                    </div>
                    <p class="text-gray-700 mb-4">Please select a priority guest type to continue.</p>
                    <button onclick="document.getElementById('priorityTypeErrorOverlay').remove()" 
                        class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-lg transition">
                        OK
                    </button>
                </div>
            `;
            
            document.body.appendChild(errorOverlay);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                const overlay = document.getElementById('priorityTypeErrorOverlay');
                if (overlay) {
                    overlay.remove();
                }
            }, 3000);
        }

        // Enhanced form validation function
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const partySize = parseInt(document.getElementById('party_size').value);
            const contactInput = document.getElementById('contact').value.trim();
            const isPriority = document.querySelector('input[name="is_priority"]:checked');
            
            let hasErrors = false;
            
            // Clear all previous errors
            clearFieldError('name');
            clearFieldError('party_size');
            clearFieldError('contact');
            clearPrioritySectionError();
            
            // Validate name
            if (!name) {
                showFieldError('name', 'Name Required', 'Please enter your name or nickname.');
                hasErrors = true;
            }
            
            // Validate party size
            if (!partySize || partySize < settings.party_size_min || partySize > settings.party_size_max) {
                showFieldError('party_size', 'Invalid Party Size', `Party size must be between ${settings.party_size_min} and ${settings.party_size_max} people.`);
                hasErrors = true;
            }
            
            // Validate contact (if provided)
            if (contactInput) {
                if (!/^[0-9]{9}$/.test(contactInput)) {
                    showFieldError('contact', 'Invalid Contact Number', 'Please enter a valid 9-digit mobile number (without 09).');
                    hasErrors = true;
                }
            }
            
            // Validate priority question
            if (!isPriority) {
                showPrioritySectionError();
                hasErrors = true;
            }
            
            // Validate priority type if "Yes" is selected
            if (isPriority && isPriority.value === '1' && !selectedPriorityType) {
                showPriorityTypeError();
                hasErrors = true;
            }
            
            return !hasErrors;
        }

        // Enhanced submit form function
        function submitForm() {
            const name = document.getElementById('name').value.trim();
            const partySize = document.getElementById('party_size').value;
            const contactInput = document.getElementById('contact').value.trim();
            const prioritySection = document.getElementById('prioritySection');
            const isPriority = document.querySelector('input[name="is_priority"]:checked');

            // Clear all previous errors
            clearFieldError('name');
            clearFieldError('contact');
            clearPrioritySectionError();

            let hasErrors = false;

            // Client-side validation with detailed error messages
            if (!name) {
                showFieldError('name', 'Name is required', 'Please enter your name or a nickname');
                hasErrors = true;
            }

            // Validate party size
            if (!partySize || parseInt(partySize) < 1) {
                showFieldError('party_size', 'Party size must be at least 1', 'Use the + button to increase party size');
                hasErrors = true;
            }

            // Validate contact number if entered
            if (contactInput.length > 0) {
                // Ensure contact number is not more than 9 digits (since 09 is visual prefix)
                if (contactInput.length > 9) {
                    showFieldError('contact', 'Contact number too long', 'Enter maximum 9 digits (e.g., 17 123 4567). Full number will be 0917 123 4567');
                    hasErrors = true;
                } else {
                    // Check if user typed complete number (9 digits) or partial number (1-8 digits)
                    const isCompleteNumber = contactInput.length === 9;
                    const isPartialNumber = contactInput.length >= 1 && contactInput.length <= 8;
                    const isNumbersOnly = /^[0-9]+$/.test(contactInput);
                    
                    if (!isNumbersOnly || (!isCompleteNumber && !isPartialNumber)) {
                        showFieldError('contact', 'Invalid phone number format', 'Enter 9 digits (e.g., 17 123 4567). Full number will be 0917 123 4567');
                        hasErrors = true;
                    }
                }
            }

            // Priority question is required if name is entered (priority section is visible)
            if (name.length > 0 && !isPriority) {
                showPrioritySectionError();
                hasErrors = true;
            }

            // If priority is yes but no type selected
            if (isPriority && isPriority.value === '1' && !selectedPriorityType) {
                showFieldError('priority_type', 'Priority type required', 'Please select a priority type (Senior, PWD, or Pregnant)');
                hasErrors = true;
            }

            // If there are validation errors, stop here
            if (hasErrors) {
                console.log('❌ Validation errors found, stopping submission');
                return;
            }

            // Check for duplicate contact if contact number is provided
            if (contactInput) {
                checkDuplicateContact(contactInput).then(duplicateCheck => {
                    if (duplicateCheck.is_duplicate) {
                        console.log('⚠️ DUPLICATE FOUND! Showing modal...', duplicateCheck);
                        
                        // Show duplicate contact modal
                        showDuplicateContactModal(
                            duplicateCheck,
                            (duplicateData) => {
                                // Continue anyway - proceed with form submission
                                console.log('✅ User chose to continue anyway');
                                proceedWithFormSubmission(name, partySize, contactInput, isPriority);
                            },
                            () => {
                                // Change number - clear contact field and focus on it
                                console.log('🔄 User chose to change number');
                                document.getElementById('contact').value = '';
                                document.getElementById('contact').focus();
                            }
                        );
                    } else {
                        console.log('✅ No duplicate found, proceeding with registration');
                        proceedWithFormSubmission(name, partySize, contactInput, isPriority);
                    }
                }).catch(error => {
                    console.error('❌ Error checking duplicate contact:', error);
                    console.log('⚠️ Continuing with form submission due to error');
                    proceedWithFormSubmission(name, partySize, contactInput, isPriority);
                });
            } else {
                console.log('ℹ️ No contact number provided, skipping duplicate check');
                proceedWithFormSubmission(name, partySize, contactInput, isPriority);
            }
        }

        function proceedWithFormSubmission(name, partySize, contactInput, isPriority) {
            // Show loading
            showLoading();

            // Prepare form data
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('name', name);
            formData.append('party_size', partySize);
            formData.append('contact', contactInput); // Send just the digits, server will add 09
            formData.append('is_priority', isPriority ? isPriority.value : '0');
            if (selectedPriorityType) {
                formData.append('priority_type', selectedPriorityType);
            }

            // Debug logging
            console.log('Form data being sent:');
            console.log('name:', name);
            console.log('party_size:', partySize);
            console.log('contact:', contactInput);
            console.log('is_priority:', isPriority ? isPriority.value : '0');
            console.log('priority_type:', selectedPriorityType);
            console.log('isPriority element:', isPriority);

            // Submit to database
            fetch('{{ route("kiosk.registration.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                // Debug logging
                console.log('Server response:', data);
                
                if (data.success) {
                    if (data.is_priority) {
                        // Priority users: Redirect to ID scanner for verification
                        console.log('Redirecting to ID scanner for priority verification:', data.debug_session);
                        window.location.href = data.redirect_to;
                    } else {
                        // Non-priority users: Redirect to review screen
                        console.log('Redirecting to review screen with session data:', data.debug_session);
                        window.location.href = data.redirect_to;
                    }
                    
                } else {
                    if (data.errors) {
                        let errorMessage = 'Please fix the following errors:\n\n';
                        for (const [field, messages] of Object.entries(data.errors)) {
                            errorMessage += `• ${messages[0]}\n`;
                        }
                        showIncompleteModal(errorMessage);
                    } else {
                        showIncompleteModal(data.message || 'Registration failed. Please try again.');
                    }
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                alert('An error occurred. Please try again or contact staff for assistance.');
            });
        }


        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('continueBtn').disabled = true;
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('continueBtn').disabled = false;
        }

        // Incomplete Information Modal Functions
        function showIncompleteModal(message = 'Some required fields are missing.') {
            document.getElementById('incompleteMessage').textContent = message;
            const modal = document.getElementById('incompleteModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeIncompleteModal() {
            const modal = document.getElementById('incompleteModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('incompleteModal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeIncompleteModal();
                }
            });

            // Close modal with ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeIncompleteModal();
                }
            });
        });
    </script>

    <!-- Incomplete Information Modal -->
    <div id="incompleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-8">
        <!-- Modal Overlay -->
        <div class="modal-overlay absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <!-- Modal Container -->
        <div class="modal-content relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 p-8 overflow-hidden">
            <!-- Modal Header -->
            <div style="background-color: #111827;" class="px-8 py-6 flex items-center justify-between">
                <h2 class="text-white text-2xl font-bold">Incomplete Information</h2>
                <button onclick="closeIncompleteModal()" class="text-white hover:text-gray-300 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-10 py-12 flex flex-col items-center text-center space-y-6">
                <!-- Error Icon -->
                <div class="error-icon w-20 h-20 bg-red-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-exclamation text-white text-4xl"></i>
                </div>

                <!-- Error Title -->
                <h3 class="text-gray-900 text-2xl font-bold" id="incompleteMessage">
                    Some required fields are missing.
                </h3>

                <!-- Error Message -->
                <p class="text-gray-600 text-lg leading-relaxed max-w-md">
                    Please complete all fields marked with <span class="text-red-500 font-bold">*</span> before continuing.
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="px-10 py-8 border-t-2 border-gray-200 flex justify-center">
                <!-- OK Button -->
                <button 
                    onclick="closeIncompleteModal()"
                    class="px-20 py-5 bg-green-500 hover:bg-green-600 text-white font-bold text-xl rounded-full transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-105"
                >
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Duplicate Contact Modal -->
    @include('components.duplicate-contact-modal')

    <!-- Session Timeout Modal Manager -->
    <script src="{{ asset('js/session-timeout-modal.js') }}"></script>

    <script>
        // Duplicate Contact Check Functions
        async function checkDuplicateContact(contactNumber) {
            try {
                console.log('🌐 Making API call to check duplicate contact:', contactNumber);
                
                const response = await fetch('/kiosk/check-duplicate-contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        contact: contactNumber
                    })
                });

                console.log('📡 API Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status}`);
                }

                const data = await response.json();
                console.log('📊 API Response data:', data);
                return data;
            } catch (error) {
                console.error('❌ Error checking duplicate contact:', error);
                return { is_duplicate: false };
            }
        }

        // Show Duplicate Contact Modal
        function showDuplicateContactModal(duplicateData, onContinue, onChange) {
            if (window.duplicateContactModal) {
                // Add the entered contact number to the duplicate data for display
                const contactInput = document.getElementById('contact').value.trim();
                if (contactInput) {
                    duplicateData.enteredContact = contactInput.startsWith('09') ? contactInput : '09' + contactInput;
                }
                
                window.duplicateContactModal.show(duplicateData, onContinue, onChange);
            } else {
                console.error('Duplicate contact modal not initialized');
            }
        }

        // Duplicate contact checking function
        async function checkDuplicateContact(contactNumber) {
            try {
                console.log('🔍 Checking for duplicate contact:', contactNumber);
                
                const response = await fetch('/api/check-duplicate-contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        contact: contactNumber
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('📞 Duplicate check response:', data);
                
                return data;
            } catch (error) {
                console.error('❌ Error checking duplicate contact:', error);
                throw error;
            }
        }

        // Show duplicate contact modal
        function showDuplicateContactModal(duplicateData, onContinue, onChangeNumber) {
            // Create modal overlay
            const modalOverlay = document.createElement('div');
            modalOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center';
            modalOverlay.style.zIndex = '2000';
            modalOverlay.id = 'duplicateContactModal';

            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.className = 'bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6';
            modalContent.innerHTML = `
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                    <h3 class="text-xl font-bold text-gray-900">Duplicate Contact Number</h3>
                </div>
                
                <p class="text-gray-700 mb-4">
                    This contact number <span class="font-semibold text-red-600">09${duplicateData.contact}</span> 
                    is already in the queue at position <span class="font-bold text-blue-600">#${duplicateData.queue_number}</span>.
                </p>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="font-semibold text-gray-600">Estimated Wait:</span>
                            <span class="text-blue-600 ml-2">${duplicateData.estimated_wait_time || 'N/A'}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-600">Total in Queue:</span>
                            <span class="text-blue-600 ml-2">${duplicateData.total_in_queue || 'N/A'}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button id="changeNumberBtn" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Change Number
                    </button>
                    <button id="continueAnywayBtn" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Continue Anyway
                    </button>
                </div>
            `;

            modalOverlay.appendChild(modalContent);
            document.body.appendChild(modalOverlay);

            // Add event listeners
            document.getElementById('changeNumberBtn').addEventListener('click', () => {
                document.body.removeChild(modalOverlay);
                onChangeNumber();
            });

            document.getElementById('continueAnywayBtn').addEventListener('click', () => {
                document.body.removeChild(modalOverlay);
                onContinue(duplicateData);
            });

            // Close modal when clicking outside
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    document.body.removeChild(modalOverlay);
                }
            });
        }
    </script>
</body>

</html>
