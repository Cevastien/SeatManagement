<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Priority PIN Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Priority Verification Dashboard</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage customer ID verifications and PIN generation</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Active PINs</p>
                            <p class="text-2xl font-bold text-blue-600" id="activePINsCount">0</p>
                        </div>
                        <button onclick="loadRecentPINs()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pending Verifications -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Pending Verifications</h2>
                    </div>
                    <div class="p-6">
                        <div id="pendingVerifications" class="space-y-3">
                            <p class="text-gray-500 text-center">No pending verifications</p>
                        </div>
                    </div>
                </div>

                <!-- Recent PINs -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Recent Verifications</h2>
                    </div>
                    <div class="p-6">
                        <div id="recentPINs" class="space-y-3">
                            <p class="text-gray-500 text-center">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PIN Generation Modal -->
    <div id="pinModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-key text-blue-600 mr-3"></i>
                    Generate PIN
                </h2>
                <button onclick="closePINModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Customer Details -->
            <div class="bg-gray-100 rounded-xl p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-600 font-semibold">Customer Details</p>
                    <span id="modalPriorityTag" class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Priority</span>
                </div>
                <p class="text-xl font-bold text-gray-900 mb-1" id="modalCustomerName">Loading...</p>
                <div class="flex items-center text-gray-700 text-sm">
                    <span id="modalPriorityType">Senior Citizen</span>
                </div>
            </div>

            <!-- Generated PIN Display -->
            <div class="bg-gray-50 rounded-xl p-8 text-center mb-6" id="pinDisplaySection">
                <p class="text-sm text-gray-500 mb-2" id="pinLabel">Verification PIN</p>
                <div class="text-6xl font-bold text-gray-900 mb-2" id="modalGeneratedPIN">0000</div>
                <p class="text-sm text-gray-500" id="pinDescription">Customer will be automatically verified</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between space-x-4">
                <button onclick="closePINModal()" class="flex-1 px-4 py-3 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 font-semibold rounded-lg transition">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button onclick="confirmPIN()" class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                    <i class="fas fa-check mr-2"></i> Confirm & Generate PIN
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentVerificationId = null;
        let currentCustomerData = null;

        // Load recent PINs
        async function loadRecentPINs() {
            try {
                const response = await fetch('/api/staff/pending-verifications');
                const data = await response.json();
                
                if (data.success) {
                    displayPendingVerifications(data.pending_verifications);
                }
            } catch (error) {
                console.error('Error loading recent PINs:', error);
            }
        }

        // Display pending verifications
        function displayPendingVerifications(verifications) {
            const container = document.getElementById('pendingVerifications');
            
            if (verifications.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center">No pending verifications</p>';
                return;
            }

            container.innerHTML = verifications.map(verification => `
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer" 
                     onclick="triggerPINModal(${verification.id}, '${verification.customer_name}', '${verification.priority_type}')">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900">${verification.customer_name}</h3>
                            <p class="text-sm text-gray-600">${verification.priority_display}</p>
                            <p class="text-xs text-gray-500">Requested at ${verification.requested_at}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                            <p class="text-xs text-gray-500 mt-1">${verification.time_elapsed}m ago</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Trigger PIN modal
        function triggerPINModal(verificationId, customerName, priorityType) {
            currentVerificationId = verificationId;
            currentCustomerData = {
                name: customerName,
                priority_type: priorityType || 'senior'
            };

            // Update modal content
            document.getElementById('modalCustomerName').textContent = customerName;
            document.getElementById('modalGeneratedPIN').textContent = '0000';
            document.getElementById('modalPriorityType').textContent = getPriorityDisplay(priorityType);

            // Update modal title and messaging for pregnant customers
            if (priorityType === 'pregnant') {
                document.querySelector('#pinModal h2').innerHTML = '<i class="fas fa-heart text-pink-600 mr-3"></i>Priority Assistance';
                document.getElementById('pinLabel').textContent = 'Priority Status';
                document.getElementById('modalGeneratedPIN').textContent = '✓';
                document.getElementById('modalGeneratedPIN').className = 'text-6xl font-bold text-green-600 mb-2';
                document.getElementById('pinDescription').textContent = 'Customer will be assisted with priority seating';
            } else {
                document.querySelector('#pinModal h2').innerHTML = '<i class="fas fa-key text-blue-600 mr-3"></i>Generate PIN';
                document.getElementById('pinLabel').textContent = 'Verification PIN';
                document.getElementById('modalGeneratedPIN').textContent = '0000';
                document.getElementById('modalGeneratedPIN').className = 'text-6xl font-bold text-gray-900 mb-2';
                document.getElementById('pinDescription').textContent = 'Customer will be automatically verified';
            }

            // Show modal
            document.getElementById('pinModal').classList.remove('hidden');
        }

        // Helper function to get priority display name
        function getPriorityDisplay(priorityType) {
            switch(priorityType) {
                case 'senior': return 'Senior Citizen';
                case 'pwd': return 'PWD';
                case 'pregnant': return 'Pregnant';
                default: return 'Regular';
            }
        }

        // Close PIN modal
        function closePINModal() {
            document.getElementById('pinModal').classList.add('hidden');
            currentVerificationId = null;
            currentCustomerData = null;
        }

        // Confirm PIN generation
        async function confirmPIN() {
            if (!currentVerificationId) {
                alert('No verification selected');
                return;
            }

            try {
                const response = await fetch('/api/staff/verify-and-generate-pin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        verification_id: currentVerificationId,
                        verified_by: 'Staff Member'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    if (currentCustomerData.priority_type === 'pregnant') {
                        alert(`Priority assistance provided! ${currentCustomerData.name} has been assisted with priority seating.`);
                    } else {
                        alert(`Verification complete! ${currentCustomerData.name} has been verified with PIN ${data.verification.pin}.`);
                    }
                    closePINModal();
                    loadRecentPINs();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error confirming PIN:', error);
                alert('Failed to confirm verification. Please try again.');
            }
        }

        // Check for pending requests every 2 seconds
        async function checkPendingRequests() {
            try {
                const response = await fetch('/api/staff/pending-verifications');
                const data = await response.json();
                
                if (data.success && data.has_pending) {
                    displayPendingVerifications(data.pending_verifications);
                    
                    // Auto-open modal for first pending request
                    if (data.pending_verifications.length > 0 && !document.getElementById('pinModal').classList.contains('hidden')) {
                        const latestRequest = data.pending_verifications[0];
                        triggerPINModal(latestRequest.id, latestRequest.customer_name, latestRequest.priority_type);
                    }
                }
            } catch (error) {
                console.error('Error checking pending requests:', error);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadRecentPINs();
            setInterval(checkPendingRequests, 2000); // Check every 2 seconds
        });
    </script>
</body>
</html>
