<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Priority Management Kiosk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        @keyframes pulse-ring {

            0%,
            100% {
                transform: scale(0.95);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        .notification-pulse {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        .customer-list {
            height: calc(100vh - 420px);
        }
    </style>
</head>

<body class="font-inter h-screen flex flex-col bg-gray-50" x-data="priorityApp()"
    @new-priority-request.window="handleNewRequest($event.detail)">

    <!-- Header -->
    <header class="bg-[#111827] shadow-lg flex-shrink-0">
        <div class="px-8 py-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-white mb-2">Priority Management System</h1>
                <p class="text-gray-300 text-sm font-medium">Staff Kiosk Interface</p>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right">
                    <p class="text-3xl font-bold text-white" x-text="currentTime"></p>
                    <p class="text-xs text-gray-300 font-medium uppercase tracking-wider">Current Time</p>
                </div>
                <div x-show="pendingVerifications.length > 0" class="notification-pulse">
                    <div class="bg-yellow-500 text-white px-5 py-3 rounded-xl font-bold shadow-lg">
                        <i class="fas fa-bell mr-2"></i>
                        <span x-text="pendingVerifications.length"></span> Pending
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Top Navigation -->
    <nav class="bg-white shadow-md flex-shrink-0 border-b-2 border-gray-200">
        <div class="flex items-center justify-center space-x-3 p-5">
            <button @click="setNav('Dashboard')"
                :class="activeNav === 'Dashboard' ? 'bg-[#111827] text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'"
                class="flex flex-col items-center justify-center p-3 rounded-xl font-semibold text-base transition-all duration-200 min-w-[130px] h-[75px] border-2 border-transparent">
                <i class="fas fa-home text-2xl mb-2"></i>
                <span>Dashboard</span>
            </button>
            <button @click="setNav('Table Occupancy')"
                :class="activeNav === 'Table Occupancy' ? 'bg-[#111827] text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'"
                class="flex flex-col items-center justify-center p-3 rounded-xl font-semibold text-base transition-all duration-200 min-w-[130px] h-[75px] border-2 border-transparent">
                <i class="fas fa-chair text-2xl mb-2"></i>
                <span>Tables</span>
            </button>
            <button @click="setNav('Auto Table')"
                :class="activeNav === 'Auto Table' ? 'bg-[#111827] text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'"
                class="flex flex-col items-center justify-center p-3 rounded-xl font-semibold text-base transition-all duration-200 min-w-[130px] h-[75px] border-2 border-transparent">
                <i class="fas fa-robot text-2xl mb-2"></i>
                <span>Auto Table</span>
            </button>
            <button @click="setNav('Priority')"
                :class="activeNav === 'Priority' ? 'bg-[#111827] text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'"
                class="flex flex-col items-center justify-center p-3 rounded-xl font-semibold text-base transition-all duration-200 min-w-[130px] h-[75px] border-2 border-transparent">
                <i class="fas fa-users text-2xl mb-2"></i>
                <span>Priority</span>
            </button>
            <button @click="setNav('Analytics')"
                :class="activeNav === 'Analytics' ? 'bg-[#111827] text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'"
                class="flex flex-col items-center justify-center p-3 rounded-xl font-semibold text-base transition-all duration-200 min-w-[130px] h-[75px] border-2 border-transparent">
                <i class="fas fa-chart-bar text-2xl mb-2"></i>
                <span>Analytics</span>
            </button>
            <button @click="setNav('Settings')"
                :class="activeNav === 'Settings' ? 'bg-[#111827] text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-300'"
                class="flex flex-col items-center justify-center p-3 rounded-xl font-semibold text-base transition-all duration-200 min-w-[130px] h-[75px] border-2 border-transparent">
                <i class="fas fa-cog text-2xl mb-2"></i>
                <span>Settings</span>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-hidden">
        <div class="h-full flex flex-col space-y-6">

            <!-- Stats Bar -->
            <div class="bg-white rounded-2xl p-8 shadow-lg flex-shrink-0 border border-gray-200">
                <div class="grid grid-cols-4 gap-8">
                    <div class="text-center border-r-2 border-gray-200 last:border-r-0">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-yellow-100 mb-4">
                            <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                        </div>
                        <p class="text-5xl font-black text-yellow-600 mb-2" x-text="pendingVerifications.length"></p>
                        <p class="text-sm text-gray-600 font-semibold uppercase tracking-wide">Awaiting Verification</p>
                    </div>
                    <div class="text-center border-r-2 border-gray-200 last:border-r-0">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-green-100 mb-4">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        <p class="text-5xl font-black text-green-600 mb-2" x-text="verifiedCustomers.length"></p>
                        <p class="text-sm text-gray-600 font-semibold uppercase tracking-wide">Verified Today</p>
                    </div>
                    <div class="text-center border-r-2 border-gray-200 last:border-r-0">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-red-100 mb-4">
                            <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                        </div>
                        <p class="text-5xl font-black text-red-600 mb-2" x-text="rejectedCustomers.length"></p>
                        <p class="text-sm text-gray-600 font-semibold uppercase tracking-wide">Rejected Today</p>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-blue-100 mb-4">
                            <i class="fas fa-user-clock text-blue-600 text-2xl"></i>
                        </div>
                        <p class="text-xl font-bold text-gray-800 mb-2 truncate px-2"
                            x-text="nextVerification?.name || 'None'"></p>
                        <p class="text-sm text-gray-600 font-semibold uppercase tracking-wide">Next in Queue</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex-shrink-0" x-show="selectedVerification">
                <!-- For Pregnant Customers -->
                <div x-show="selectedVerification?.priorityType === 'Pregnant'" class="grid grid-cols-2 gap-5">
                    <button @click="verifyPriority()"
                        class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-green-600 hover:bg-green-700 text-white shadow-lg hover:shadow-xl">
                        <i class="fas fa-check mr-2"></i>Verify & Approve
                    </button>
                    <button @click="rejectPriority()"
                        class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-red-600 hover:bg-red-700 text-white shadow-lg hover:shadow-xl">
                        <i class="fas fa-times mr-2"></i>Reject Priority
                    </button>
                </div>

                <!-- For Other Customers -->
                <div x-show="selectedVerification?.priorityType !== 'Pregnant'" class="grid grid-cols-3 gap-5">
                    <button x-show="selectedVerification && !selectedVerification.idNumber"
                        @click="openVerificationModal()"
                        class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-blue-600 hover:bg-blue-700 text-white shadow-lg hover:shadow-xl">
                        <i class="fas fa-id-card mr-2"></i>Input ID Number
                    </button>

                    <template x-if="selectedVerification && selectedVerification.idNumber">
                        <button @click="verifyPriority()"
                            class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-green-600 hover:bg-green-700 text-white shadow-lg hover:shadow-xl">
                            <i class="fas fa-check mr-2"></i>Verify & Approve
                        </button>
                    </template>

                    <template x-if="selectedVerification && selectedVerification.idNumber">
                        <button @click="rejectPriority()"
                            class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-red-600 hover:bg-red-700 text-white shadow-lg hover:shadow-xl">
                            <i class="fas fa-times mr-2"></i>Reject Priority
                        </button>
                    </template>

                    <template x-if="selectedVerification && selectedVerification.idNumber">
                        <button @click="openVerificationModal()"
                            class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-blue-600 hover:bg-blue-700 text-white shadow-lg hover:shadow-xl">
                            <i class="fas fa-edit mr-2"></i>Edit ID Number
                        </button>
                    </template>
                </div>
            </div>

            <!-- Customer List -->
            <div class="flex-1 bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col border border-gray-200">
                <div class="bg-gray-50 px-8 py-5 border-b-2 border-gray-200">
                    <h3 class="text-lg font-black text-[#111827] uppercase tracking-wide">Priority Verification Queue
                    </h3>
                </div>

                <div class="customer-list overflow-y-auto p-5 space-y-4">

                    <!-- Pending Verifications -->
                    <template x-for="verification in pendingVerifications" :key="verification.id">
                        <div @click="selectVerification(verification)"
                            :class="selectedVerification?.id === verification.id ? 'border-[#111827] bg-gray-50 shadow-xl' : 'border-gray-200 hover:border-gray-400 hover:shadow-lg'"
                            class="bg-white rounded-xl p-5 border-2 cursor-pointer transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-5">
                                    <div class="bg-[#111827] text-white rounded-xl px-4 py-3 shadow-md">
                                        <p class="text-2xl font-black" x-text="'#' + verification.queueNumber"></p>
                                    </div>
                                    <div>
                                        <p class="text-lg font-bold text-gray-900 mb-2" x-text="verification.name"></p>
                                        <div class="flex items-center space-x-3 mb-2">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm font-semibold border border-gray-300">
                                                <i class="fas fa-users mr-2 text-gray-500"></i>
                                                <span x-text="verification.partySize"></span>
                                            </span>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold border border-blue-300">
                                                <i class="fas fa-star mr-2 text-blue-500"></i>
                                                <span x-text="verification.priorityType"></span>
                                            </span>
                                        </div>
                                        <p class="text-sm text-blue-600 font-bold" x-show="verification.idNumber">
                                            <i class="fas fa-id-card mr-1"></i>
                                            ID: <span x-text="verification.idNumber"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span x-show="!verification.idNumber"
                                        class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide bg-yellow-100 text-yellow-800 border border-yellow-300">PENDING
                                        ID CHECK</span>
                                    <span x-show="verification.idNumber"
                                        class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide bg-blue-100 text-blue-800 border border-blue-300">READY
                                        FOR VERIFICATION</span>
                                    <p class="text-xs text-gray-500 mt-3 font-medium">Visit #<span
                                            x-text="verification.visitCount"></span></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Verified Customers -->
                    <template x-for="customer in verifiedCustomers" :key="customer.id">
                        <div
                            class="bg-white rounded-xl p-5 border-2 border-green-200 hover:shadow-lg transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-5">
                                    <div class="bg-green-600 text-white rounded-xl px-4 py-3 shadow-md">
                                        <p class="text-2xl font-black" x-text="'#' + customer.queueNumber"></p>
                                    </div>
                                    <div>
                                        <p class="text-lg font-bold text-gray-900 mb-2" x-text="customer.name"></p>
                                        <p class="text-sm text-gray-600 font-medium"
                                            x-text="customer.partySize + ' people • ' + customer.priorityType"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide bg-green-100 text-green-800 border border-green-300">VERIFIED</span>
                                    <p class="text-xs text-gray-500 mt-3 font-medium"
                                        x-text="customer.verifiedAt + ' • ' + customer.verifiedBy"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Rejected Customers -->
                    <template x-for="customer in rejectedCustomers" :key="customer.id">
                        <div
                            class="bg-white rounded-xl p-5 border-2 border-red-200 hover:shadow-lg transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-5">
                                    <div class="bg-red-600 text-white rounded-xl px-4 py-3 shadow-md">
                                        <p class="text-2xl font-black" x-text="'#' + customer.queueNumber"></p>
                                    </div>
                                    <div>
                                        <p class="text-lg font-bold text-gray-900 mb-2" x-text="customer.name"></p>
                                        <p class="text-sm text-gray-600 font-medium"
                                            x-text="customer.partySize + ' people • ' + customer.priorityType"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide bg-red-100 text-red-800 border border-red-300">REJECTED</span>
                                    <p class="text-xs text-gray-500 mt-3 font-medium" x-text="customer.rejectionReason">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t-2 border-gray-200 px-8 py-5 flex-shrink-0 shadow-lg">
        <div class="flex items-center justify-between">
            <button onclick="goBack()"
                class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 shadow-md hover:shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i>
                <span>Back</span>
            </button>
            <div class="text-sm text-gray-600 font-medium">
                Staff: <span class="font-bold text-[#111827]">Staff-001</span> • <span
                    class="text-green-600 font-bold">● System Active</span>
            </div>
        </div>
    </footer>

    <!-- NEW REQUEST NOTIFICATION MODAL -->
    <div x-show="showNewRequestAlert" x-transition
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50"
        @click.self="dismissNewRequest()">
        <div class="bg-white rounded-2xl p-10 max-w-md w-full mx-4 shadow-2xl border-4 border-yellow-400">
            <div class="text-center mb-8">
                <i class="fas fa-bell text-7xl text-yellow-500 notification-pulse mb-5"></i>
                <h3 class="text-3xl font-black text-[#111827]">New Priority Request!</h3>
            </div>

            <div class="bg-yellow-50 rounded-xl p-6 mb-8 border-2 border-yellow-200" x-show="newRequestData">
                <p class="text-2xl font-bold text-gray-900 mb-5" x-text="newRequestData?.name"></p>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-600 mb-2 font-medium">Queue Number:</p>
                        <p class="font-black text-xl text-[#111827]" x-text="'#' + newRequestData?.queueNumber"></p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-600 mb-2 font-medium">Party Size:</p>
                        <p class="font-black text-xl text-gray-800" x-text="newRequestData?.partySize + ' people'"></p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-600 mb-2 font-medium">Priority Type:</p>
                        <p class="font-black text-xl text-blue-600" x-text="newRequestData?.priorityType"></p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-600 mb-2 font-medium">Visit Count:</p>
                        <p class="font-black text-xl text-gray-800" x-text="'Visit #' + newRequestData?.visitCount"></p>
                    </div>
                </div>
            </div>

            <div class="flex space-x-4">
                <button @click="goToPriorityScreen()"
                    class="flex-1 px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-[#111827] hover:bg-gray-800 text-white shadow-lg hover:shadow-xl">
                    Go to Priority Screen
                </button>
                <button @click="dismissNewRequest()"
                    class="flex-1 px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 shadow-lg hover:shadow-xl">
                    Dismiss
                </button>
            </div>
        </div>
    </div>

    <!-- ID INPUT MODAL -->
    <div x-show="showVerificationModal" x-transition
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-10 max-w-lg w-full mx-4 shadow-2xl border-2 border-gray-200">
            <h3 class="text-3xl font-black text-[#111827] mb-8">Verify Priority Customer</h3>

            <!-- ID Number Input -->
            <div class="mb-8" x-show="selectedVerification?.priorityType !== 'Pregnant'">
                <label class="block text-lg font-bold text-gray-700 mb-4">ID Number (Required)</label>
                <input type="text" x-model="idNumber" placeholder="Enter ID number from physical ID"
                    class="w-full border-2 border-gray-300 rounded-xl px-6 py-5 text-xl focus:border-[#111827] focus:ring-4 focus:ring-gray-200 focus:outline-none transition-all">
                <p class="text-sm text-gray-500 mt-4 font-medium">Please enter the ID number after physically checking
                    the customer's ID</p>
            </div>

            <!-- Pregnant Customer Notice -->
            <div class="mb-8 bg-pink-50 border-2 border-pink-300 rounded-xl p-8"
                x-show="selectedVerification?.priorityType === 'Pregnant'">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-pink-500 rounded-xl flex items-center justify-center mr-5">
                        <i class="fas fa-heart text-white text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-pink-800 mb-2">Pregnant Customer</p>
                        <p class="text-sm text-pink-600 font-medium">No ID verification required. Physical observation
                            is sufficient.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-5 mb-5">
                <button @click="verifyPriority()"
                    :disabled="selectedVerification?.priorityType !== 'Pregnant' && !idNumber"
                    :class="(selectedVerification?.priorityType !== 'Pregnant' && !idNumber) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                    class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-green-600 text-white shadow-lg flex items-center justify-center">
                    <i class="fas fa-check mr-2"></i>
                    <span>Verify & Approve</span>
                </button>
                <button @click="rejectPriority()"
                    :disabled="selectedVerification?.priorityType !== 'Pregnant' && !idNumber"
                    :class="(selectedVerification?.priorityType !== 'Pregnant' && !idNumber) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-700'"
                    class="px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-red-600 text-white shadow-lg flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>
                    <span>Reject Priority</span>
                </button>
            </div>

            <!-- Cancel Button -->
            <button @click="closeVerificationModal()"
                class="w-full px-8 py-4 font-bold text-lg rounded-xl transition-all duration-200 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-800 shadow-lg">
                Cancel
            </button>
        </div>
    </div>

    <script>
        function priorityApp() {
            return {
                activeNav: 'Priority',
                showVerificationModal: false,
                showNewRequestAlert: false,
                newRequestData: null,
                selectedVerification: null,
                idNumber: '',
                currentTime: '',
                pendingVerifications: [],
                verifiedCustomers: [],
                rejectedCustomers: [],

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    this.loadData();
                    setInterval(() => this.loadData(), 5000);

                    setInterval(() => {
                        this.refreshCSRFToken();
                    }, 10 * 60 * 1000);
                },

                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                get nextVerification() {
                    return this.pendingVerifications[0] || null;
                },

                async loadData() {
                    try {
                        const response = await fetch('/api/verification/pending', {
                            credentials: 'same-origin'
                        });

                        if (response.status === 419) {
                            const newToken = await this.refreshCSRFToken();
                            if (newToken) {
                                setTimeout(() => this.loadData(), 500);
                                return;
                            } else {
                                window.location.reload();
                                return;
                            }
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.pendingVerifications = data.pending_verifications.map(v => ({
                                id: v.id,
                                queueNumber: '#' + v.queue_number,
                                name: v.customer_name,
                                partySize: v.party_size,
                                priorityType: v.priority_display,
                                waitTime: v.time_elapsed,
                                status: 'Awaiting ID Check',
                                idRequired: v.priority_type !== 'pregnant',
                                visitCount: v.visit_count,
                                visitType: 'regular',
                                idNumber: v.id_number || '',
                                contactNumber: v.contact_number || '',
                                registeredAt: v.registered_at,
                                estimatedWaitMinutes: v.estimated_wait_minutes || 0
                            }));
                        }
                    } catch (error) {
                        console.error('Error loading data:', error);
                    }

                    try {
                        const today = new Date().toISOString().split('T')[0];
                        const response = await fetch(`/api/verification/completed?date=${today}`, {
                            credentials: 'same-origin'
                        });

                        if (response.status === 419) {
                            const newToken = await this.refreshCSRFToken();
                            if (newToken) {
                                setTimeout(() => this.loadData(), 500);
                                return;
                            }
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.verifiedCustomers = (data.verified || []).map(c => ({
                                id: c.id,
                                queueNumber: '#' + c.id,
                                name: c.customer_name,
                                partySize: 1,
                                priorityType: c.priority_display,
                                status: 'Verified',
                                verifiedAt: c.verified_at,
                                verifiedBy: c.verified_by,
                                idNumber: c.id_number || ''
                            }));

                            this.rejectedCustomers = (data.rejected || []).map(c => ({
                                id: c.id,
                                queueNumber: '#' + c.id,
                                name: c.customer_name,
                                partySize: 1,
                                priorityType: c.priority_display,
                                status: 'Rejected',
                                rejectedAt: c.rejected_at,
                                rejectionReason: c.rejection_reason,
                                idNumber: c.id_number || ''
                            }));
                        }
                    } catch (error) {
                        console.error('Error loading completed verifications:', error);
                    }
                },

                setNav(navItem) {
                    this.activeNav = navItem;
                },

                selectVerification(verification) {
                    this.selectedVerification = verification;
                    this.idNumber = verification.idNumber || '';

                    if (verification.priorityType === 'Pregnant') {
                        setTimeout(() => this.openVerificationModal(), 100);
                    }
                },

                openVerificationModal() {
                    if (this.selectedVerification) {
                        this.showVerificationModal = true;
                        this.idNumber = this.selectedVerification.idNumber || '';
                    } else {
                        alert('Please select a customer first');
                    }
                },

                closeVerificationModal() {
                    this.showVerificationModal = false;
                    this.idNumber = '';
                },

                async verifyPriority() {
                    if (!this.selectedVerification) return;

                    const isPregnant = this.selectedVerification.priorityType === 'Pregnant';
                    const hasIdNumber = this.idNumber && this.idNumber.trim() !== '';

                    if (isPregnant || hasIdNumber) {
                        try {
                            const response = await fetch('/api/verification/complete', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    verification_id: this.selectedVerification.id,
                                    verified_by: 'Staff-001',
                                    id_number: isPregnant ? 'PREGNANT_NO_ID' : this.idNumber
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                const customerName = this.selectedVerification.name;
                                this.verifiedCustomers.push(this.selectedVerification);
                                this.pendingVerifications = this.pendingVerifications.filter(v => v.id !== this.selectedVerification.id);
                                this.closeVerificationModal();
                                this.selectedVerification = null;
                                alert('Priority status verified for ' + customerName + '!');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    }
                },

                async rejectPriority() {
                    if (!this.selectedVerification) return;

                    if (!confirm(`Reject verification for ${this.selectedVerification.name}?`)) return;

                    try {
                        const response = await fetch('/api/verification/reject', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                verification_id: this.selectedVerification.id,
                                rejected_by: 'Staff-001',
                                reason: 'Verification failed'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.rejectedCustomers.push(this.selectedVerification);
                            this.pendingVerifications = this.pendingVerifications.filter(v => v.id !== this.selectedVerification.id);
                            this.closeVerificationModal();
                            this.selectedVerification = null;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },

                handleNewRequest(data) {
                    this.newRequestData = data;
                    this.showNewRequestAlert = true;
                },

                dismissNewRequest() {
                    this.showNewRequestAlert = false;
                    this.newRequestData = null;
                },

                goToPriorityScreen() {
                    this.showNewRequestAlert = false;
                    this.newRequestData = null;
                },

                async refreshCSRFToken() {
                    try {
                        const response = await fetch('/api/csrf-token');
                        const data = await response.json();
                        if (data.csrf_token) {
                            document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.csrf_token);
                            return data.csrf_token;
                        }
                    } catch (error) {
                        console.error('Error refreshing CSRF token:', error);
                    }
                    return null;
                }
            }
        }

        function goBack() {
            window.location.href = "/";
        }
    </script>
</body>

</html>