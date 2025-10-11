<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Cafe Gervacios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .animate-fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-chart-bar text-2xl text-gray-700"></i>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
                            <p class="text-sm text-gray-600">Cafe Gervacios | Pastry & Coffee</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="exportTodayData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-download"></i>
                            <span>Export Today</span>
                        </button>
                        <button onclick="refreshData()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Customers Today -->
                <div class="bg-white rounded-xl shadow-sm p-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Customers</p>
                            <p class="text-3xl font-bold text-gray-900" id="totalCustomers">0</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center">
                        <span class="text-sm text-gray-500" id="customerChange">+0%</span>
                        <span class="text-sm text-gray-500 ml-2">vs yesterday</span>
                    </div>
                </div>

                <!-- Average Wait Time -->
                <div class="bg-white rounded-xl shadow-sm p-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Avg Wait Time</p>
                            <p class="text-3xl font-bold text-gray-900" id="avgWaitTime">0</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center">
                        <span class="text-sm text-gray-500" id="waitTimeChange">+0%</span>
                        <span class="text-sm text-gray-500 ml-2">vs yesterday</span>
                    </div>
                </div>

                <!-- Table Utilization -->
                <div class="bg-white rounded-xl shadow-sm p-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Table Utilization</p>
                            <p class="text-3xl font-bold text-gray-900" id="tableUtilization">0%</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-table text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center">
                        <span class="text-sm text-gray-500" id="utilizationChange">+0%</span>
                        <span class="text-sm text-gray-500 ml-2">vs yesterday</span>
                    </div>
                </div>

                <!-- Priority Customers -->
                <div class="bg-white rounded-xl shadow-sm p-6 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Priority Customers</p>
                            <p class="text-3xl font-bold text-gray-900" id="priorityCustomers">0</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-star text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center">
                        <span class="text-sm text-gray-500" id="priorityPercentage">0%</span>
                        <span class="text-sm text-gray-500 ml-2">of total</span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Hourly Distribution Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Hourly Customer Distribution</h3>
                    <canvas id="hourlyChart" width="400" height="200"></canvas>
                </div>

                <!-- Priority Breakdown Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Priority Customer Breakdown</h3>
                    <canvas id="priorityChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Group Sizes and Peak Hours -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Group Sizes Distribution -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Party Size Distribution</h3>
                    <div id="groupSizesChart" class="space-y-2">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Peak Hours Analysis -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Peak Hours Analysis</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Busiest Hour</p>
                                <p class="text-sm text-gray-600" id="peakHour">Loading...</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-gray-900" id="peakCustomers">0</p>
                                <p class="text-sm text-gray-600">customers</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-600" id="morningPeak">0</p>
                                <p class="text-sm text-gray-600">Morning</p>
                            </div>
                            <div class="p-3 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600" id="afternoonPeak">0</p>
                                <p class="text-sm text-gray-600">Afternoon</p>
                            </div>
                            <div class="p-3 bg-purple-50 rounded-lg">
                                <p class="text-2xl font-bold text-purple-600" id="eveningPeak">0</p>
                                <p class="text-sm text-gray-600">Evening</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights and Recommendations -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Today's Insights -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Insights</h3>
                    <div class="space-y-3" id="insightsList">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-lightbulb text-yellow-500 text-lg mt-1"></i>
                            <p class="text-gray-700">Loading insights...</p>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendations</h3>
                    <div class="space-y-3" id="recommendationsList">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-arrow-right text-blue-500 text-lg mt-1"></i>
                            <p class="text-gray-700">Loading recommendations...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize charts
        let hourlyChart, priorityChart;

        // Load analytics data
        async function loadAnalyticsData() {
            try {
                const response = await fetch('/api/analytics/today');
                const data = await response.json();
                
                if (data.success) {
                    updateSummaryCards(data.analytics);
                    updateCharts(data.analytics);
                    updateInsights(data.analytics);
                    updateGroupSizes(data.analytics);
                    updatePeakHours(data.analytics);
                }
            } catch (error) {
                console.error('Failed to load analytics:', error);
            }
        }

        // Update summary cards
        function updateSummaryCards(analytics) {
            document.getElementById('totalCustomers').textContent = analytics.total_customers || 0;
            document.getElementById('avgWaitTime').textContent = Math.round(analytics.avg_wait_time || 0) + ' min';
            document.getElementById('tableUtilization').textContent = Math.round(analytics.table_utilization || 0) + '%';
            
            const priorityTotal = Object.values(analytics.priority_breakdown || {}).reduce((sum, p) => sum + (p.verified || 0), 0);
            document.getElementById('priorityCustomers').textContent = priorityTotal;
            document.getElementById('priorityPercentage').textContent = analytics.total_customers > 0 
                ? Math.round((priorityTotal / analytics.total_customers) * 100) + '%' 
                : '0%';
        }

        // Update charts
        function updateCharts(analytics) {
            // Hourly Chart
            const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
            const hourlyData = analytics.hourly_distribution || {};
            
            if (hourlyChart) hourlyChart.destroy();
            hourlyChart = new Chart(hourlyCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => i + ':00'),
                    datasets: [{
                        label: 'Customers',
                        data: Array.from({length: 24}, (_, i) => hourlyData[i]?.registrations || 0),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Priority Chart
            const priorityCtx = document.getElementById('priorityChart').getContext('2d');
            const priorityData = analytics.priority_breakdown || {};
            
            if (priorityChart) priorityChart.destroy();
            priorityChart = new Chart(priorityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Senior', 'PWD', 'Pregnant', 'Regular'],
                    datasets: [{
                        data: [
                            priorityData.senior?.verified || 0,
                            priorityData.pwd?.verified || 0,
                            priorityData.pregnant?.verified || 0,
                            (analytics.total_customers || 0) - Object.values(priorityData).reduce((sum, p) => sum + (p.verified || 0), 0)
                        ],
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(59, 130, 246)',
                            'rgb(168, 85, 247)',
                            'rgb(156, 163, 175)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Update insights
        function updateInsights(analytics) {
            const insightsList = document.getElementById('insightsList');
            const recommendationsList = document.getElementById('recommendationsList');
            
            // Simple insights based on data
            const insights = [];
            const recommendations = [];

            if (analytics.total_customers > 0) {
                insights.push(`${analytics.total_customers} customers served today`);
                
                if (analytics.avg_wait_time > 30) {
                    insights.push(`Average wait time is ${Math.round(analytics.avg_wait_time)} minutes`);
                    recommendations.push('Consider adding more staff during peak hours');
                }
                
                const priorityTotal = Object.values(analytics.priority_breakdown || {}).reduce((sum, p) => sum + (p.verified || 0), 0);
                if (priorityTotal > 0) {
                    const percentage = Math.round((priorityTotal / analytics.total_customers) * 100);
                    insights.push(`${percentage}% of customers had priority status`);
                }
            }

            insightsList.innerHTML = insights.map(insight => 
                `<div class="flex items-start space-x-3">
                    <i class="fas fa-lightbulb text-yellow-500 text-lg mt-1"></i>
                    <p class="text-gray-700">${insight}</p>
                </div>`
            ).join('');

            recommendationsList.innerHTML = recommendations.map(rec => 
                `<div class="flex items-start space-x-3">
                    <i class="fas fa-arrow-right text-blue-500 text-lg mt-1"></i>
                    <p class="text-gray-700">${rec}</p>
                </div>`
            ).join('');
        }

        // Update group sizes
        function updateGroupSizes(analytics) {
            const groupSizesChart = document.getElementById('groupSizesChart');
            const groupSizes = analytics.group_sizes_data?.distribution || {};
            
            groupSizesChart.innerHTML = Object.entries(groupSizes).map(([size, count]) => {
                if (count === 0) return '';
                const percentage = analytics.total_customers > 0 ? Math.round((count / analytics.total_customers) * 100) : 0;
                return `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="font-medium">${size} person${size !== '1' ? 's' : ''}</span>
                        <div class="flex items-center space-x-3">
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                            </div>
                            <span class="text-sm font-medium w-8 text-right">${count}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Update peak hours
        function updatePeakHours(analytics) {
            const peakHours = analytics.peak_hours || {};
            document.getElementById('peakHour').textContent = peakHours.peak_hour ? `${peakHours.peak_hour}:00` : 'N/A';
            document.getElementById('peakCustomers').textContent = peakHours.peak_customers || 0;
            
            // Calculate morning, afternoon, evening peaks
            const hourlyData = analytics.hourly_distribution || {};
            const morning = Array.from({length: 6}, (_, i) => hourlyData[i]?.registrations || 0).reduce((a, b) => a + b, 0);
            const afternoon = Array.from({length: 6}, (_, i) => hourlyData[i + 6]?.registrations || 0).reduce((a, b) => a + b, 0);
            const evening = Array.from({length: 6}, (_, i) => hourlyData[i + 12]?.registrations || 0).reduce((a, b) => a + b, 0);
            
            document.getElementById('morningPeak').textContent = morning;
            document.getElementById('afternoonPeak').textContent = afternoon;
            document.getElementById('eveningPeak').textContent = evening;
        }

        // Export today's data
        function exportTodayData() {
            window.location.href = '/staff/analytics/export/today';
        }

        // Refresh data
        function refreshData() {
            loadAnalyticsData();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAnalyticsData();
            
            // Auto-refresh every 5 minutes
            setInterval(loadAnalyticsData, 300000);
        });
    </script>
</body>
</html>
