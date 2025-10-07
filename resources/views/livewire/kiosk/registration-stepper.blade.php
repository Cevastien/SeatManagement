<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Waitlist - Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e293b',
                        'primary-dark': '#0f172a',
                        secondary: '#2c3e50',
                        accent: '#f39c12',
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
    </style>
</head>
<body class="font-inter bg-gray-200">
    <div class="h-screen w-screen flex overflow-hidden">
        <!-- Left Side - Dark Background -->
        <div class="w-2/5 bg-gradient-to-br from-primary via-primary-dark to-gray-900 relative flex flex-col justify-between p-12">
            <!-- Logo and Back Button -->
            <div>
                <div class="flex items-center mb-8">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mr-4 shadow-lg">
                        <i class="fas fa-utensils text-primary text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-white text-3xl font-bold tracking-wide">GERVACIOS</h1>
                        <p class="text-gray-300 text-sm">RESTAURANT & LOUNGE</p>
                    </div>
                </div>
                
                <button 
                    onclick="window.location.href='/'"
                    class="w-14 h-14 rounded-full border-2 border-white flex items-center justify-center hover:bg-white hover:text-primary transition-all group"
                >
                    <i class="fas fa-arrow-left text-white group-hover:text-primary text-xl"></i>
                </button>
            </div>

            <!-- Decorative Elements -->
            <div class="space-y-6">
                <div class="flex items-center space-x-4 text-white opacity-80">
                    <div class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm opacity-70">Average Wait Time</p>
                        <p class="text-2xl font-bold">15-20 mins</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 text-white opacity-80">
                    <div class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm opacity-70">Current Waitlist</p>
                        <p class="text-2xl font-bold">8 Parties</p>
                    </div>
                </div>
            </div>

            <!-- Date Time -->
            <div class="text-white">
                <p class="text-lg font-medium" id="currentDateTime">{{ now()->format('M d, Y | g:i A') }}</p>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="w-3/5 bg-gray-100 flex items-center justify-center p-8">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-10 h-full flex flex-col justify-center">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-primary mb-2">Guest Information</h2>
                    <p class="text-gray-500 text-base">Please fill in your details to join the waitlist</p>
                </div>

                <form id="waitlistForm" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Name/Nickname -->
                        <div>
                            <label class="block text-base font-semibold text-primary mb-3">
                                Name/Nickname <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                wire:model="name"
                                placeholder="Enter your name"
                                required
                                class="w-full px-5 py-4 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition text-lg"
                            >
                            <p class="text-xs text-gray-500 mt-2">Enter your name (or representative's name for priority guests).</p>
                            @error('name') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <label class="block text-base font-semibold text-primary mb-3">
                                Contact Number (Optional)
                            </label>
                            <input 
                                type="tel" 
                                wire:model="contactNumber"
                                placeholder="Enter your phone number"
                                class="w-full px-5 py-4 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition text-lg"
                            >
                            <p class="text-xs text-gray-500 mt-2">We'll use this to notify you when your turn is ready.</p>
                            @error('contactNumber') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Party Size -->
                    <div>
                        <label class="block text-base font-semibold text-primary mb-3">
                            Party Size <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-6">
                            <button 
                                type="button"
                                onclick="decrementPartySize()"
                                class="w-16 h-16 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-2xl font-bold text-primary transition shadow-sm hover:shadow-md"
                            >
                                -
                            </button>
                            <div class="flex-1 max-w-xs h-16 rounded-xl bg-neutral border-2 border-gray-200 flex items-center justify-center">
                                <input 
                                    type="number" 
                                    id="partySizeInput"
                                    min="1" 
                                    max="50" 
                                    value="{{ $partySize }}"
                                    onchange="validatePartySize()"
                                    onkeydown="preventInvalidInput(event)"
                                    class="text-4xl font-bold text-primary text-center bg-transparent border-none outline-none w-full"
                                    style="font-size: 2.5rem; font-weight: bold;"
                                />
                            </div>
                            <button 
                                type="button"
                                onclick="incrementPartySize()"
                                class="w-16 h-16 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-2xl font-bold text-primary transition shadow-sm hover:shadow-md"
                            >
                                +
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 text-center">You may enter 1-50 people</p>
                        @error('partySize') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Priority Check Question -->
                    <div>
                        <label class="block text-base font-semibold text-primary mb-3">
                            Priority Check Question <span class="text-red-500">*</span>
                        </label>
                        <p class="text-base text-gray-600 mb-4">Does your group include a Senior, PWD, or Pregnant Guest?</p>
                        <div class="grid grid-cols-2 gap-4">
                            <button 
                                type="button"
                                id="priorityYes"
                                onclick="selectPriority('yes')"
                                class="py-5 px-8 rounded-xl border-2 border-gray-200 bg-white hover:border-primary hover:bg-primary hover:text-white transition font-bold text-lg shadow-sm hover:shadow-md"
                            >
                                Yes
                            </button>
                            <button 
                                type="button"
                                id="priorityNo"
                                onclick="selectPriority('no')"
                                class="py-5 px-8 rounded-xl border-2 border-gray-200 bg-white hover:border-primary hover:bg-primary hover:text-white transition font-bold text-lg shadow-sm hover:shadow-md"
                            >
                                No
                            </button>
                        </div>
                        @error('priorityType') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="button"
                        id="joinWaitlistBtn"
                        onclick="checkForTableSuggestions()"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-5 rounded-xl transition-all shadow-lg hover:shadow-xl text-xl mt-6"
                    >
                        Join Waitlist
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Suggestion Modal -->
    <div id="tableSuggestionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200" style="background-color: #111827;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Table May Be Available Soon</h3>
                            <p class="text-gray-300">We found a table that might be ready for you!</p>
                        </div>
                    </div>
                    <button onclick="closeTableSuggestionModal()" class="text-white hover:text-gray-300 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="bg-white border-2 border-gray-200 rounded-xl shadow-lg p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-info-circle text-primary text-xl"></i>
                        <div>
                            <p class="text-secondary font-semibold">Great news!</p>
                            <p class="text-gray-700" id="suggestionMessage">
                                Table T5 will be available in about 15 minutes. Would you like to reserve it?
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Table Selection Panel -->
                <div id="tableSelectionPanel" class="hidden">
                    <h4 class="text-lg font-semibold text-secondary mb-4">Select Your Preferred Table:</h4>
                    <div class="grid grid-cols-2 gap-4 mb-6" id="availableTables">
                        <!-- Tables will be populated here -->
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button 
                        id="requestTableBtn"
                        onclick="showTableSelection()"
                        class="flex-1 bg-primary hover:bg-primary-dark text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl text-lg"
                    >
                        <i class="fas fa-table mr-2"></i>Yes, Request Table
                    </button>
                    <button 
                        onclick="skipTableSuggestion()"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl text-lg"
                    >
                        <i class="fas fa-clock mr-2"></i>No, I'll Wait
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40 flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-primary mx-auto mb-4"></div>
            <p class="text-lg font-semibold text-primary">Processing...</p>
        </div>
    </div>

    <script>
        let partySize = 2;
        let selectedPriority = null;
        let settings = {
            party_size_min: 1,
            party_size_max: 50,
            restaurant_name: 'GERVACIOS RESTAURANT & LOUNGE'
        };

        // Update Date and Time
        function updateDateTime() {
            const now = new Date();
            const options = { 
                month: 'short', 
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };
            const formatted = now.toLocaleDateString('en-US', options).replace(',', ' |');
            document.getElementById('currentDateTime').textContent = formatted;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

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
            const partySizeInput = document.getElementById('partySizeInput');
            if (partySizeInput) {
                partySizeInput.min = settings.party_size_min;
                partySizeInput.max = settings.party_size_max;
            }
            
            // Update the help text
            const helpText = document.querySelector('.text-xs.text-gray-500.mt-2.text-center');
            if (helpText) {
                helpText.textContent = `You may enter ${settings.party_size_min}-${settings.party_size_max} people`;
            }
        }

        // Load settings when page loads
        loadSettings();

        // Party Size Functions
        function incrementPartySize() {
            const input = document.getElementById('partySizeInput');
            let currentValue = parseInt(input.value) || settings.party_size_min;
            
            if (currentValue < settings.party_size_max) {
                currentValue++;
                input.value = currentValue;
                partySize = currentValue;
            }
        }

        function decrementPartySize() {
            const input = document.getElementById('partySizeInput');
            let currentValue = parseInt(input.value) || settings.party_size_min;
            
            if (currentValue > settings.party_size_min) {
                currentValue--;
                input.value = currentValue;
                partySize = currentValue;
            }
        }

        function validatePartySize() {
            const input = document.getElementById('partySizeInput');
            let value = parseInt(input.value) || settings.party_size_min;
            
            // Ensure value is within bounds
            if (value < settings.party_size_min) {
                value = settings.party_size_min;
            } else if (value > settings.party_size_max) {
                value = settings.party_size_max;
            }
            
            input.value = value;
            partySize = value;
        }

        function preventInvalidInput(event) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(event.keyCode) !== -1 ||
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

        // Priority Selection
        function selectPriority(choice) {
            selectedPriority = choice;
            const yesBtn = document.getElementById('priorityYes');
            const noBtn = document.getElementById('priorityNo');

            if (choice === 'yes') {
                yesBtn.classList.add('bg-primary', 'text-white', 'border-primary');
                yesBtn.classList.remove('bg-white', 'border-gray-200');
                noBtn.classList.remove('bg-primary', 'text-white', 'border-primary');
                noBtn.classList.add('bg-white', 'border-gray-200');
            } else {
                noBtn.classList.add('bg-primary', 'text-white', 'border-primary');
                noBtn.classList.remove('bg-white', 'border-gray-200');
                yesBtn.classList.remove('bg-primary', 'text-white', 'border-primary');
                yesBtn.classList.add('bg-white', 'border-gray-200');
            }
        }

        // Table Suggestion Functions
        function checkForTableSuggestions() {
            if (selectedPriority === null) {
                alert('Please answer the priority check question.');
                return;
            }

            const name = document.querySelector('input[type="text"]').value;
            if (!name.trim()) {
                alert('Please enter your name.');
                return;
            }

            const partySizeInput = document.getElementById('partySizeInput');
            const partySizeValue = parseInt(partySizeInput.value) || 1;
            
            // Validate party size
            if (partySizeValue < settings.party_size_min || partySizeValue > settings.party_size_max) {
                alert(`Party size must be between ${settings.party_size_min} and ${settings.party_size_max} people.`);
                return;
            }

            showLoadingOverlay();

            // First, get table suggestions
            fetch(`/api/kiosk/table-suggestions?party_size=${partySizeValue}&time_window=15`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoadingOverlay();
                
                if (data.success && data.has_suggestions) {
                    // Store form data for later use
                    const partySizeInput = document.getElementById('partySizeInput');
                    const partySizeValue = parseInt(partySizeInput.value) || 1;
                    
                    // Validate party size before submission
                    if (partySizeValue < settings.party_size_min || partySizeValue > settings.party_size_max) {
                        alert(`Party size must be between ${settings.party_size_min} and ${settings.party_size_max} people.`);
                        return;
                    }

                    window.formData = {
                        name: name,
                        partySize: partySizeValue,
                        contactNumber: document.querySelector('input[type="tel"]').value,
                priority: selectedPriority,
                timestamp: new Date().toISOString()
            };

                    // Show table suggestion modal
                    showTableSuggestionModal(data.suggestions, data.best_suggestion);
                } else {
                    // No table suggestions, proceed with normal registration
                    submitRegistration();
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Error checking table suggestions:', error);
                // Fallback to normal registration
                submitRegistration();
            });
        }

        function showTableSuggestionModal(suggestions, bestSuggestion) {
            const modal = document.getElementById('tableSuggestionModal');
            const messageEl = document.getElementById('suggestionMessage');
            
            if (bestSuggestion) {
                const timeText = bestSuggestion.is_available_now ? 'now' : bestSuggestion.formatted_time_until_free;
                messageEl.textContent = `Table ${bestSuggestion.name} will be available ${timeText}. Would you like to reserve it?`;
            }
            
            // Store suggestions for table selection
            window.tableSuggestions = suggestions;
            
            modal.classList.remove('hidden');
        }

        function closeTableSuggestionModal() {
            document.getElementById('tableSuggestionModal').classList.add('hidden');
            document.getElementById('tableSelectionPanel').classList.add('hidden');
        }

        function showTableSelection() {
            const panel = document.getElementById('tableSelectionPanel');
            const tablesContainer = document.getElementById('availableTables');
            
            // Clear previous tables
            tablesContainer.innerHTML = '';
            
            // Populate available tables
            window.tableSuggestions.forEach(table => {
                const tableCard = document.createElement('div');
                tableCard.className = `p-4 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md ${
                    table.is_available_now ? 'border-green-300 bg-green-50' : 'border-yellow-300 bg-yellow-50'
                }`;
                
                tableCard.innerHTML = `
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary mb-2">${table.name}</div>
                        <div class="text-sm text-gray-600 mb-2">Capacity: ${table.capacity} people</div>
                        <div class="text-sm font-semibold ${table.is_available_now ? 'text-green-600' : 'text-yellow-600'}">
                            ${table.is_available_now ? 'Available Now' : table.formatted_time_until_free}
                        </div>
                        <button 
                            onclick="reserveTable(${table.id})"
                            class="mt-3 w-full bg-primary hover:bg-primary-dark text-white font-bold py-2 px-4 rounded-lg transition text-sm"
                        >
                            Reserve
                        </button>
                    </div>
                `;
                
                tablesContainer.appendChild(tableCard);
            });
            
            panel.classList.remove('hidden');
        }

        function reserveTable(tableId) {
            showLoadingOverlay();
            
            // First create the customer
            fetch('/kiosk/registration', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(window.formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.customer_id) {
                    // Now reserve the table
                    return fetch(`/api/kiosk/reserve-table/${tableId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ customer_id: data.customer_id })
                    });
                } else {
                    throw new Error('Failed to create customer');
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoadingOverlay();
                closeTableSuggestionModal();
                
                if (data.success) {
                    alert(`Table ${data.table.name} reserved successfully! You'll be notified when it's ready.`);
                    window.location.href = `/kiosk/receipt/${data.customer.id}`;
                } else {
                    alert('Failed to reserve table. Please try again.');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Error reserving table:', error);
                alert('An error occurred. Please try again.');
            });
        }

        function skipTableSuggestion() {
            closeTableSuggestionModal();
            submitRegistration();
        }

        function submitRegistration() {
            showLoadingOverlay();
            
            fetch('/kiosk/registration', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    name: document.querySelector('input[type="text"]').value,
                    partySize: parseInt(document.getElementById('partySizeInput').value) || 1,
                    contactNumber: document.querySelector('input[type="tel"]').value,
                    priority: selectedPriority,
                    timestamp: new Date().toISOString()
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoadingOverlay();
                
                if (data.success) {
            alert('Thank you! You have been added to the waitlist.\nWe will notify you when your table is ready.');
                    window.location.href = `/kiosk/receipt/${data.customer_id}`;
                } else {
                    alert('Failed to join waitlist. Please try again.');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                console.error('Error submitting registration:', error);
                alert('An error occurred. Please try again.');
            });
        }

        function showLoadingOverlay() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        }

        function hideLoadingOverlay() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        }

        // Form Submission (legacy - kept for compatibility)
        document.getElementById('waitlistForm').addEventListener('submit', function(e) {
            e.preventDefault();
            checkForTableSuggestions();
        });
    </script>
</body>
</html>
