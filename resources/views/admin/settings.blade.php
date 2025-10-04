<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">
                <i class="fas fa-cog mr-3"></i>System Settings
            </h1>

            <!-- Party Size Settings -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Party Size Limits</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Party Size</label>
                        <input type="number" id="party_size_min" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" max="100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Party Size</label>
                        <input type="number" id="party_size_max" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" max="100">
                    </div>
                </div>
            </div>

            <!-- Queue Settings -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Queue Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Average Dining Duration (minutes)</label>
                        <input type="number" id="avg_dining_duration" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="10" max="300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Table Suggestion Time Window (minutes)</label>
                        <input type="number" id="table_suggestion_time_window" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="5" max="60">
                    </div>
                </div>
            </div>

            <!-- Restaurant Information -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Restaurant Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant Name</label>
                        <input type="text" id="restaurant_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant Address</label>
                        <input type="text" id="restaurant_address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant Phone</label>
                        <input type="text" id="restaurant_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end space-x-4">
                <button onclick="resetSettings()" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                    <i class="fas fa-undo mr-2"></i>Reset
                </button>
                <button onclick="saveSettings()" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </div>

            <!-- Status Message -->
            <div id="statusMessage" class="mt-4 hidden"></div>
        </div>
    </div>

    <script>
        // Load current settings
        async function loadCurrentSettings() {
            try {
                const response = await fetch('/api/settings/public');
                const data = await response.json();
                
                if (data.success) {
                    // Populate form fields
                    document.getElementById('party_size_min').value = data.settings.party_size_min || 1;
                    document.getElementById('party_size_max').value = data.settings.party_size_max || 50;
                    document.getElementById('avg_dining_duration').value = data.queue_settings.avg_dining_duration || 60;
                    document.getElementById('table_suggestion_time_window').value = data.queue_settings.table_suggestion_time_window || 15;
                    document.getElementById('restaurant_name').value = data.settings.restaurant_name || '';
                    document.getElementById('restaurant_address').value = data.settings.restaurant_address || '';
                    document.getElementById('restaurant_phone').value = data.settings.restaurant_phone || '';
                }
            } catch (error) {
                console.error('Failed to load settings:', error);
                showStatus('Failed to load current settings', 'error');
            }
        }

        // Save settings
        async function saveSettings() {
            const settings = {
                party_size_min: parseInt(document.getElementById('party_size_min').value),
                party_size_max: parseInt(document.getElementById('party_size_max').value),
                avg_dining_duration: parseInt(document.getElementById('avg_dining_duration').value),
                table_suggestion_time_window: parseInt(document.getElementById('table_suggestion_time_window').value),
                restaurant_name: document.getElementById('restaurant_name').value,
                restaurant_address: document.getElementById('restaurant_address').value,
                restaurant_phone: document.getElementById('restaurant_phone').value,
            };

            // Basic validation
            if (settings.party_size_min >= settings.party_size_max) {
                showStatus('Minimum party size must be less than maximum party size', 'error');
                return;
            }

            try {
                const response = await fetch('/api/admin/settings/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify(settings)
                });

                const data = await response.json();
                if (data.success) {
                    showStatus('Settings saved successfully!', 'success');
                } else {
                    showStatus('Failed to save settings: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Failed to save settings:', error);
                showStatus('Failed to save settings', 'error');
            }
        }

        // Reset settings to defaults
        function resetSettings() {
            document.getElementById('party_size_min').value = 1;
            document.getElementById('party_size_max').value = 50;
            document.getElementById('avg_dining_duration').value = 60;
            document.getElementById('table_suggestion_time_window').value = 15;
            document.getElementById('restaurant_name').value = 'GERVACIOS RESTAURANT & LOUNGE';
            document.getElementById('restaurant_address').value = '123 Coffee Street, Davao City';
            document.getElementById('restaurant_phone').value = '(02) 8123-4567';
        }

        // Show status message
        function showStatus(message, type) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.className = `mt-4 p-4 rounded-md ${type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            statusDiv.textContent = message;
            statusDiv.classList.remove('hidden');
            
            setTimeout(() => {
                statusDiv.classList.add('hidden');
            }, 5000);
        }

        // Load settings when page loads
        document.addEventListener('DOMContentLoaded', loadCurrentSettings);
    </script>
</body>
</html>
