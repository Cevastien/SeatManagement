<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DailyAnalytics extends Model
{
    protected $table = 'daily_analytics';
    
    protected $fillable = [
        'date',
        'total_customers',
        'avg_wait_time',
        'table_utilization',
        'peak_hours',
        'group_sizes_data',
        'priority_breakdown',
        'hourly_distribution',
        'staff_performance',
        'export_status',
        'insights',
        'recommendations',
    ];

    protected $casts = [
        'date' => 'date',
        'peak_hours' => 'array',
        'group_sizes_data' => 'array',
        'priority_breakdown' => 'array',
        'hourly_distribution' => 'array',
        'staff_performance' => 'array',
    ];

    /**
     * Get the staff member who exported this data
     */
    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'exported_by');
    }

    /**
     * Generate today's analytics
     */
    public static function generateToday(): self
    {
        $today = today();
        $customers = Customer::whereDate('created_at', $today)->get();
        $events = QueueEvent::whereDate('event_time', $today)->get();
        $verifications = PriorityVerification::whereDate('created_at', $today)->get();
        
        $analytics = [
            'date' => $today,
            'total_customers' => $customers->count(),
            'avg_wait_time' => self::calculateAverageWaitTime($customers, $events),
            'table_utilization' => self::calculateTableUtilization($customers),
            'peak_hours' => self::identifyPeakHours($events),
            'group_sizes_data' => self::analyzeGroupSizes($customers),
            'priority_breakdown' => self::analyzePriorityBreakdown($verifications),
            'hourly_distribution' => self::analyzeHourlyDistribution($events),
            'staff_performance' => self::analyzeStaffPerformance($verifications, $events),
            'export_status' => 'pending',
            'insights' => self::generateInsights($customers, $events, $verifications),
            'recommendations' => self::generateRecommendations($customers, $events, $verifications),
        ];
        
        return static::updateOrCreate(
            ['date' => $today],
            $analytics
        );
    }

    /**
     * Calculate average wait time
     */
    private static function calculateAverageWaitTime($customers, $events): float
    {
        $waitTimes = [];
        
        foreach ($customers as $customer) {
            $registeredAt = $events->where('customer_id', $customer->id)
                ->where('event_type', 'registered')
                ->first()?->event_time;
            $seatedAt = $events->where('customer_id', $customer->id)
                ->where('event_type', 'seated')
                ->first()?->event_time;
                
            if ($registeredAt && $seatedAt) {
                $waitTimes[] = $registeredAt->diffInMinutes($seatedAt);
            }
        }
        
        return count($waitTimes) > 0 ? round(array_sum($waitTimes) / count($waitTimes), 1) : 0;
    }

    /**
     * Calculate table utilization
     */
    private static function calculateTableUtilization($customers): float
    {
        $totalTables = Table::count();
        $occupiedTables = Table::occupied()->count();
        
        return $totalTables > 0 ? round(($occupiedTables / $totalTables) * 100, 1) : 0;
    }

    /**
     * Identify peak hours
     */
    private static function identifyPeakHours($events): array
    {
        $hourlyRegistrations = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyRegistrations[$hour] = $events->where('event_type', 'registered')
                ->filter(function($event) use ($hour) {
                    return $event->event_time->hour == $hour;
                })->count();
        }
        
        $sortedHours = collect($hourlyRegistrations)->sortDesc();
        
        return [
            'peak_hour' => $sortedHours->keys()->first(),
            'peak_customers' => $sortedHours->values()->first(),
            'hourly_distribution' => $hourlyRegistrations
        ];
    }

    /**
     * Analyze group sizes
     */
    private static function analyzeGroupSizes($customers): array
    {
        $groupSizes = $customers->groupBy('party_size');
        $distribution = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $distribution[$i] = $groupSizes->get($i, collect())->count();
        }
        
        $total = $customers->count();
        $mostCommon = $groupSizes->sortDesc()->keys()->first();
        $average = $total > 0 ? round($customers->avg('party_size'), 1) : 0;
        
        return [
            'distribution' => $distribution,
            'most_common_size' => $mostCommon,
            'average_size' => $average,
            'total_groups' => $total
        ];
    }

    /**
     * Analyze priority breakdown
     */
    private static function analyzePriorityBreakdown($verifications): array
    {
        $priorityTypes = ['senior', 'pwd', 'pregnant'];
        $breakdown = [];
        
        foreach ($priorityTypes as $type) {
            $breakdown[$type] = [
                'claimed' => $verifications->where('priority_type', $type)->count(),
                'verified' => $verifications->where('priority_type', $type)->where('status', 'verified')->count(),
                'rejected' => $verifications->where('priority_type', $type)->where('status', 'rejected')->count()
            ];
        }
        
        return $breakdown;
    }

    /**
     * Analyze hourly distribution
     */
    private static function analyzeHourlyDistribution($events): array
    {
        $hourlyData = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyData[$hour] = [
                'hour' => sprintf('%02d:00', $hour),
                'registrations' => $events->where('event_type', 'registered')
                    ->filter(function($event) use ($hour) {
                        return $event->event_time->hour == $hour;
                    })->count(),
                'total_events' => $events->filter(function($event) use ($hour) {
                    return $event->event_time->hour == $hour;
                })->count()
            ];
        }
        
        return $hourlyData;
    }

    /**
     * Analyze staff performance
     */
    private static function analyzeStaffPerformance($verifications, $events): array
    {
        $staffStats = [];
        
        // Staff verification stats
        $verificationStaff = $verifications->whereNotNull('verified_by')
            ->groupBy('verified_by');
            
        foreach ($verificationStaff as $staffId => $staffVerifications) {
            $staffStats[$staffId] = [
                'verifications_processed' => $staffVerifications->count(),
                'verified' => $staffVerifications->where('status', 'verified')->count(),
                'rejected' => $staffVerifications->where('status', 'rejected')->count(),
                'verification_rate' => $staffVerifications->count() > 0 
                    ? round(($staffVerifications->where('status', 'verified')->count() / $staffVerifications->count()) * 100, 1)
                    : 0
            ];
        }
        
        return $staffStats;
    }

    /**
     * Generate insights
     */
    private static function generateInsights($customers, $events, $verifications): string
    {
        $totalCustomers = $customers->count();
        $priorityCustomers = $verifications->where('status', 'verified')->count();
        $averageWaitTime = self::calculateAverageWaitTime($customers, $events);
        
        $insights = [];
        
        if ($totalCustomers > 0) {
            $insights[] = "Served {$totalCustomers} customers today";
        }
        
        if ($priorityCustomers > 0) {
            $priorityPercentage = round(($priorityCustomers / $totalCustomers) * 100, 1);
            $insights[] = "{$priorityPercentage}% of customers had priority status";
        }
        
        if ($averageWaitTime > 0) {
            $insights[] = "Average wait time was {$averageWaitTime} minutes";
        }
        
        $peakHour = self::identifyPeakHours($events)['peak_hour'];
        if ($peakHour !== null) {
            $insights[] = "Peak hour was " . sprintf('%02d:00', $peakHour);
        }
        
        return implode('. ', $insights) . '.';
    }

    /**
     * Generate recommendations
     */
    private static function generateRecommendations($customers, $events, $verifications): string
    {
        $recommendations = [];
        
        $averageWaitTime = self::calculateAverageWaitTime($customers, $events);
        if ($averageWaitTime > 30) {
            $recommendations[] = "Consider adding more staff during peak hours to reduce wait times";
        }
        
        $peakHour = self::identifyPeakHours($events)['peak_hour'];
        if ($peakHour !== null) {
            $recommendations[] = "Schedule extra staff around " . sprintf('%02d:00', $peakHour) . " for peak demand";
        }
        
        $priorityRejectionRate = $verifications->where('status', 'rejected')->count() / max($verifications->count(), 1);
        if ($priorityRejectionRate > 0.2) {
            $recommendations[] = "High priority rejection rate - review verification process";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "Operations running smoothly - maintain current staffing levels";
        }
        
        return implode('. ', $recommendations) . '.';
    }

    /**
     * Export to CSV
     */
    public function exportToCSV(): string
    {
        $filename = "analytics_export_{$this->date->format('Y-m-d')}.csv";
        $filepath = storage_path("app/exports/{$filename}");
        
        // Ensure exports directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // CSV headers
        fputcsv($file, [
            'Date',
            'Total Customers',
            'Average Wait Time',
            'Table Utilization',
            'Peak Hour',
            'Most Common Party Size',
            'Priority Customers',
            'Insights',
            'Recommendations'
        ]);
        
        // Data row
        fputcsv($file, [
            $this->date->format('Y-m-d'),
            $this->total_customers,
            $this->avg_wait_time,
            $this->table_utilization,
            $this->peak_hours['peak_hour'] ?? 'N/A',
            $this->group_sizes_data['most_common_size'] ?? 'N/A',
            array_sum(array_column($this->priority_breakdown, 'verified')),
            $this->insights,
            $this->recommendations
        ]);
        
        fclose($file);
        
        return $filename;
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for recent analytics
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    /**
     * Get analytics summary for dashboard
     */
    public static function getDashboardSummary(): array
    {
        $today = static::where('date', today())->first();
        $yesterday = static::where('date', today()->subDay())->first();
        $thisWeek = static::where('date', '>=', today()->startOfWeek())->get();
        
        return [
            'today' => $today ? [
                'customers' => $today->total_customers,
                'wait_time' => $today->avg_wait_time,
                'utilization' => $today->table_utilization,
                'peak_hour' => $today->peak_hours['peak_hour'] ?? null,
            ] : null,
            'yesterday' => $yesterday ? [
                'customers' => $yesterday->total_customers,
                'wait_time' => $yesterday->avg_wait_time,
                'utilization' => $yesterday->table_utilization,
            ] : null,
            'week_average' => $thisWeek->count() > 0 ? [
                'customers' => round($thisWeek->avg('total_customers'), 1),
                'wait_time' => round($thisWeek->avg('avg_wait_time'), 1),
                'utilization' => round($thisWeek->avg('table_utilization'), 1),
            ] : null,
        ];
    }
}
