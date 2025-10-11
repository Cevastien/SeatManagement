<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\QueueEvent;
use App\Models\PriorityVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AnalyticsExportService
{
    /**
     * Generate end-of-day analytics and export
     */
    public function generateEndOfDayReport(Carbon $date = null, int $staffId = null): array
    {
        $date = $date ?: now()->subDay(); // Yesterday's data by default
        
        $customers = $this->getDailyCustomers($date);
        $events = $this->getDailyEvents($date);
        $verifications = $this->getDailyVerifications($date);
        
        // Generate analytics data
        $analytics = [
            'date' => $date->format('Y-m-d'),
            'total_customers' => $customers->count(),
            'group_sizes_data' => $this->analyzeGroupSizes($customers),
            'priority_breakdown' => $this->analyzePriorityBreakdown($customers, $verifications),
            'hourly_distribution' => $this->analyzeHourlyDistribution($events),
            'staff_performance' => $this->analyzeStaffPerformance($events, $verifications),
            'waiting_times' => $this->analyzeWaitingTimes($customers, $events),
            'table_usage' => $this->analyzeTableUsage($customers),
            'peak_hours' => $this->identifyPeakHours($events),
            'insights' => $this->generateInsights($customers, $events, $verifications),
            'recommendations' => $this->generateRecommendations($customers, $events, $verifications)
        ];
        
        // Save analytics to database
        $this->saveDailyAnalytics($analytics);
        
        // Generate CSV export
        $csvFilename = $this->exportCustomersToCsv($customers, $date, $staffId);
        
        // Record export in daily_exports table
        $this->recordDailyExport($date, $csvFilename, $customers->count(), $staffId);
        
        return [
            'analytics' => $analytics,
            'csv_filename' => $csvFilename,
            'export_completed' => true
        ];
    }
    
    /**
     * Get customers for a specific date
     */
    private function getDailyCustomers(Carbon $date)
    {
        return Customer::whereDate('created_at', $date)
            ->with(['events' => function($query) use ($date) {
                $query->whereDate('event_time', $date);
            }])
            ->get();
    }
    
    /**
     * Get events for a specific date
     */
    private function getDailyEvents(Carbon $date)
    {
        return QueueEvent::whereDate('event_time', $date)->get();
    }
    
    /**
     * Get verifications for a specific date
     */
    private function getDailyVerifications(Carbon $date)
    {
        return PriorityVerification::whereDate('created_at', $date)->get();
    }
    
    /**
     * Analyze group sizes distribution
     */
    private function analyzeGroupSizes($customers): array
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
    private function analyzePriorityBreakdown($customers, $verifications): array
    {
        $priorityTypes = ['normal', 'senior', 'pwd', 'pregnant'];
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
    private function analyzeHourlyDistribution($events): array
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
    private function analyzeStaffPerformance($events, $verifications): array
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
     * Analyze waiting times
     */
    private function analyzeWaitingTimes($customers, $events): array
    {
        $waitingTimes = [];
        
        foreach ($customers as $customer) {
            $registeredAt = $customer->events->where('event_type', 'registered')->first()?->event_time;
            $seatedAt = $customer->events->where('event_type', 'seated')->first()?->event_time;
            
            if ($registeredAt && $seatedAt) {
                $waitTime = $registeredAt->diffInMinutes($seatedAt);
                $waitingTimes[] = [
                    'customer_id' => $customer->id,
                    'party_size' => $customer->party_size,
                    'priority_type' => $customer->priority_type,
                    'wait_time_minutes' => $waitTime
                ];
            }
        }
        
        $totalWaitTimes = collect($waitingTimes);
        
        return [
            'average_wait_time' => $totalWaitTimes->avg('wait_time_minutes'),
            'min_wait_time' => $totalWaitTimes->min('wait_time_minutes'),
            'max_wait_time' => $totalWaitTimes->max('wait_time_minutes'),
            'wait_by_priority' => $totalWaitTimes->groupBy('priority_type')->map(function($group) {
                return round($group->avg('wait_time_minutes'), 1);
            }),
            'wait_by_party_size' => $totalWaitTimes->groupBy('party_size')->map(function($group) {
                return round($group->avg('wait_time_minutes'), 1);
            })
        ];
    }
    
    /**
     * Analyze table usage
     */
    private function analyzeTableUsage($customers): array
    {
        $tableUsage = $customers->whereNotNull('assigned_table_id')
            ->groupBy('assigned_table_id');
            
        return [
            'tables_used' => $tableUsage->count(),
            'most_used_table' => $tableUsage->sortDesc()->keys()->first(),
            'average_customers_per_table' => $tableUsage->avg(function($group) {
                return $group->count();
            })
        ];
    }
    
    /**
     * Identify peak hours
     */
    private function identifyPeakHours($events): array
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
     * Generate AI-like insights
     */
    private function generateInsights($customers, $events, $verifications): string
    {
        $totalCustomers = $customers->count();
        $priorityCustomers = $verifications->where('status', 'verified')->count();
        $averageWaitTime = $this->analyzeWaitingTimes($customers, $events)['average_wait_time'];
        
        $insights = [];
        
        if ($totalCustomers > 0) {
            $insights[] = "Served {$totalCustomers} customers today";
        }
        
        if ($priorityCustomers > 0) {
            $priorityPercentage = round(($priorityCustomers / $totalCustomers) * 100, 1);
            $insights[] = "{$priorityPercentage}% of customers had priority status";
        }
        
        if ($averageWaitTime) {
            $insights[] = "Average wait time was {$averageWaitTime} minutes";
        }
        
        $peakHour = $this->identifyPeakHours($events)['peak_hour'];
        if ($peakHour !== null) {
            $insights[] = "Peak hour was " . sprintf('%02d:00', $peakHour);
        }
        
        return implode('. ', $insights) . '.';
    }
    
    /**
     * Generate recommendations
     */
    private function generateRecommendations($customers, $events, $verifications): string
    {
        $recommendations = [];
        
        $averageWaitTime = $this->analyzeWaitingTimes($customers, $events)['average_wait_time'];
        if ($averageWaitTime && $averageWaitTime > 30) {
            $recommendations[] = "Consider adding more staff during peak hours to reduce wait times";
        }
        
        $peakHour = $this->identifyPeakHours($events)['peak_hour'];
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
     * Save analytics to database
     */
    private function saveDailyAnalytics(array $analytics): void
    {
        DB::table('daily_analytics')->updateOrInsert(
            ['date' => $analytics['date']],
            [
                'total_customers' => $analytics['total_customers'],
                'group_sizes_data' => json_encode($analytics['group_sizes_data']),
                'priority_breakdown' => json_encode($analytics['priority_breakdown']),
                'hourly_distribution' => json_encode($analytics['hourly_distribution']),
                'staff_performance' => json_encode($analytics['staff_performance']),
                'export_status' => 'completed',
                'insights' => $analytics['insights'],
                'recommendations' => $analytics['recommendations'],
                'updated_at' => now()
            ]
        );
    }
    
    /**
     * Export customers to CSV
     */
    private function exportCustomersToCsv($customers, Carbon $date, int $staffId = null): string
    {
        $filename = "customers_export_{$date->format('Y-m-d')}.csv";
        $filepath = storage_path("app/exports/{$filename}");
        
        // Ensure exports directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // CSV headers
        fputcsv($file, [
            'Customer ID',
            'Name',
            'Party Size',
            'Contact Number',
            'Queue Number',
            'Priority Type',
            'Status',
            'Registration Time',
            'Called Time',
            'Seated Time',
            'Completed Time',
            'Wait Time (minutes)',
            'Table ID',
            'Special Requests'
        ]);
        
        // Customer data
        foreach ($customers as $customer) {
            $registeredAt = $customer->events->where('event_type', 'registered')->first()?->event_time;
            $calledAt = $customer->events->where('event_type', 'called')->first()?->event_time;
            $seatedAt = $customer->events->where('event_type', 'seated')->first()?->event_time;
            $completedAt = $customer->events->where('event_type', 'completed')->first()?->event_time;
            
            $waitTime = null;
            if ($registeredAt && $seatedAt) {
                $waitTime = $registeredAt->diffInMinutes($seatedAt);
            }
            
            fputcsv($file, [
                $customer->id,
                $customer->name,
                $customer->party_size,
                $customer->contact_number,
                $customer->queue_number,
                $customer->priority_type,
                $customer->status,
                $registeredAt?->format('Y-m-d H:i:s'),
                $calledAt?->format('Y-m-d H:i:s'),
                $seatedAt?->format('Y-m-d H:i:s'),
                $completedAt?->format('Y-m-d H:i:s'),
                $waitTime,
                $customer->assigned_table_id,
                $customer->special_requests
            ]);
        }
        
        fclose($file);
        
        return $filename;
    }
    
    /**
     * Record daily export in database
     */
    private function recordDailyExport(Carbon $date, string $filename, int $totalCustomers, int $staffId = null): void
    {
        DB::table('daily_exports')->updateOrInsert(
            ['export_date' => $date->format('Y-m-d')],
            [
                'csv_filename' => $filename,
                'total_customers' => $totalCustomers,
                'exported_by' => $staffId,
                'exported_at' => now(),
                'export_summary' => json_encode([
                    'total_customers' => $totalCustomers,
                    'export_timestamp' => now()->toISOString()
                ]),
                'updated_at' => now()
            ]
        );
    }
    
    /**
     * Get export history
     */
    public function getExportHistory(int $limit = 30): array
    {
        return DB::table('daily_exports')
            ->orderBy('export_date', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    
    /**
     * Download CSV file
     */
    public function downloadCsvFile(string $filename): ?string
    {
        $filepath = storage_path("app/exports/{$filename}");
        
        if (file_exists($filepath)) {
            return $filepath;
        }
        
        return null;
    }
}
