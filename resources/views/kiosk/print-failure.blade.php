<x-kiosk-layout>
    <div class="h-full w-full flex items-center justify-center p-8">
        <div class="w-full max-w-4xl">
            <!-- Print Failure Screen -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
                <!-- Warning Icon -->
                <div class="mb-8">
                    <div class="w-24 h-24 mx-auto bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-4xl"></i>
                    </div>
                </div>

                <!-- Error Message -->
                <h1 class="text-4xl font-bold text-red-900 mb-4">Receipt Printing Failed</h1>
                <p class="text-xl text-gray-600 mb-8">
                    We encountered an issue with the receipt printer. Your queue number has been saved and staff has been notified.
                </p>

                <!-- Queue Information -->
                <div class="bg-red-50 border-2 border-red-200 rounded-2xl shadow-xl p-6 mb-8">
                    <div class="text-center">
                        <p class="text-sm text-red-800 font-medium mb-2">Your Queue Number</p>
                        <p class="text-5xl font-bold text-red-900 mb-4">#{{ $customer->queue_number }}</p>
                        
                        <!-- QR Code -->
                        <div class="bg-white p-4 rounded-lg border-2 border-red-300 mx-auto w-fit">
                            <div class="w-32 h-32 bg-gray-200 rounded-lg flex items-center justify-center">
                                <!-- QR Code using Google Charts API -->
                                <img src="https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl={{ urlencode("Queue #{$customer->queue_number} - {$customer->name} - " . now()->format('Y-m-d H:i:s')) }}" 
                                     alt="QR Code for Queue #{{ $customer->queue_number }}" 
                                     class="w-full h-full rounded-lg">
                            </div>
                        </div>
                        
                        <p class="text-xs text-red-700 mt-4">
                            Scan this QR code to track your queue position
                        </p>
                    </div>
                </div>

                <!-- Digital Receipt Information -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl shadow-xl p-6 mb-8">
                    <h3 class="text-xl font-semibold text-blue-900 mb-4">Your Digital Receipt</h3>
                    <div class="text-left space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-blue-800">Name:</span>
                            <span class="text-sm text-blue-900">{{ $customer->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-blue-800">Party Size:</span>
                            <span class="text-sm text-blue-900">{{ $customer->party_size }} pax</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-blue-800">Priority:</span>
                            <span class="text-sm text-blue-900">{{ $customer->priority_type === 'normal' ? 'Regular' : ucfirst($customer->priority_type) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-blue-800">Time:</span>
                            <span class="text-sm text-blue-900">{{ now()->format('d M Y - g:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-blue-800">Est. Wait:</span>
                            <span class="text-sm text-blue-900">{{ $customer->estimated_wait_minutes }} min</span>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-2xl shadow-xl p-6 mb-8">
                    <h3 class="text-xl font-semibold text-yellow-900 mb-2">What's Next?</h3>
                    <p class="text-lg text-yellow-800 mb-2">
                        • Your queue number is <strong>#{{ $customer->queue_number }}</strong>
                    </p>
                    <p class="text-lg text-yellow-800 mb-2">
                        • Staff has been notified of the printing issue
                    </p>
                    <p class="text-lg text-yellow-800">
                        • You can track your position using the QR code above
                    </p>
                </div>

                <!-- Action Button -->
                <button onclick="window.location.href='{{ route('kiosk.attract') }}'" 
                   class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all inline-flex items-center justify-center">
                    <i class="fas fa-check mr-3"></i>
                    Done
                </button>
            </div>
        </div>
    </div>
</x-kiosk-layout>
