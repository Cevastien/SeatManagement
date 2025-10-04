<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registrations - GervaciosSys Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">GervaciosSys Admin</h1>
                        <p class="text-gray-600">Customer Registration Management</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.customers.today') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Today's Customers</a>
                        <a href="{{ route('admin.queue.status') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Queue Status</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">All Customer Registrations</h2>
                        <p class="text-sm text-gray-600 mt-1">Total: {{ $customers->total() }} customers</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Party Size</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($customers as $customer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($customer->priority_type !== 'normal') bg-red-100 text-red-800 @else bg-gray-100 text-gray-800 @endif">
                                            {{ $customer->formatted_queue_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $customer->party_size }} 
                                            @if($customer->party_size > 1) people @else person @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $customer->contact_number ?: 'Not provided' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($customer->priority_type === 'senior') bg-purple-100 text-purple-800
                                            @elseif($customer->priority_type === 'pwd') bg-blue-100 text-blue-800
                                            @elseif($customer->priority_type === 'pregnant') bg-pink-100 text-pink-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $customer->priority_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($customer->status === 'waiting') bg-yellow-100 text-yellow-800
                                            @elseif($customer->status === 'called') bg-blue-100 text-blue-800
                                            @elseif($customer->status === 'seated') bg-green-100 text-green-800
                                            @elseif($customer->status === 'completed') bg-gray-100 text-gray-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $customer->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->registered_at->format('M j, Y g:i A') }}
                                        <div class="text-xs text-gray-400">
                                            {{ $customer->registered_at->diffForHumans() }}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="text-gray-500">
                                            <p class="text-lg font-medium">No customers found</p>
                                            <p class="text-sm">Customer registrations will appear here</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($customers->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $customers->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
