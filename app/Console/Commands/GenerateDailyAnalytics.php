<?php

namespace App\Console\Commands;

use App\Services\AnalyticsExportService;
use App\Services\StoreHoursService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateDailyAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:generate-daily 
                            {--date= : Specific date to generate analytics for (Y-m-d format)}
                            {--staff-id= : Staff member ID who initiated the export}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate end-of-day analytics and export customer data to CSV';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsExportService $analyticsService, StoreHoursService $storeHoursService)
    {
        $this->info('🏪 Cafe Gervacios | Pastry & Coffee - Daily Analytics Export');
        $this->line('');

        // Get date parameter
        $dateInput = $this->option('date');
        $staffId = $this->option('staff-id');
        
        if ($dateInput) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $dateInput);
            } catch (\Exception $e) {
                $this->error("Invalid date format. Please use Y-m-d format (e.g., 2025-10-09)");
                return 1;
            }
        } else {
            $date = now()->subDay(); // Yesterday by default
        }

        $this->info("📅 Generating analytics for: {$date->format('l, F j, Y')}");
        $this->line('');

        // Check if store hours are configured
        $restaurantName = $storeHoursService->getRestaurantName();
        $this->info("🏪 Restaurant: {$restaurantName}");
        $this->line('');

        try {
            // Generate analytics and export
            $this->info('📊 Generating analytics data...');
            $result = $analyticsService->generateEndOfDayReport($date, $staffId);
            
            $analytics = $result['analytics'];
            
            // Display summary
            $this->line('');
            $this->info('📈 ANALYTICS SUMMARY:');
            $this->line('─────────────────────');
            $this->line("Total Customers: {$analytics['total_customers']}");
            
            if (!empty($analytics['group_sizes_data']['most_common_size'])) {
                $this->line("Most Common Party Size: {$analytics['group_sizes_data']['most_common_size']}");
            }
            
            if (!empty($analytics['peak_hours']['peak_hour'])) {
                $peakHour = sprintf('%02d:00', $analytics['peak_hours']['peak_hour']);
                $this->line("Peak Hour: {$peakHour} ({$analytics['peak_hours']['peak_customers']} customers)");
            }
            
            if (!empty($analytics['waiting_times']['average_wait_time'])) {
                $avgWait = round($analytics['waiting_times']['average_wait_time'], 1);
                $this->line("Average Wait Time: {$avgWait} minutes");
            }
            
            $this->line('');
            $this->info('💡 INSIGHTS:');
            $this->line($analytics['insights']);
            
            $this->line('');
            $this->info('🎯 RECOMMENDATIONS:');
            $this->line($analytics['recommendations']);
            
            // CSV export info
            $this->line('');
            $this->info('📄 CSV EXPORT:');
            $this->line("Filename: {$result['csv_filename']}");
            $this->line("Location: storage/app/exports/{$result['csv_filename']}");
            $this->line("Customers exported: {$analytics['total_customers']}");
            
            // Priority breakdown
            if (!empty($analytics['priority_breakdown'])) {
                $this->line('');
                $this->info('⭐ PRIORITY BREAKDOWN:');
                foreach ($analytics['priority_breakdown'] as $type => $data) {
                    if ($data['claimed'] > 0) {
                        $this->line("{$type}: {$data['verified']}/{$data['claimed']} verified");
                    }
                }
            }
            
            // Staff performance
            if (!empty($analytics['staff_performance'])) {
                $this->line('');
                $this->info('👥 STAFF PERFORMANCE:');
                foreach ($analytics['staff_performance'] as $staffId => $performance) {
                    $this->line("Staff {$staffId}: {$performance['verifications_processed']} verifications processed");
                }
            }
            
            $this->line('');
            $this->info('✅ Daily analytics export completed successfully!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error generating analytics: " . $e->getMessage());
            return 1;
        }
    }
}