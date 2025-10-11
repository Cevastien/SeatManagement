<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ArchiveDailyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:daily-data {--days=1 : Number of days to archive (default: 1 for yesterday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive customer data from previous days to keep database focused on current day operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysToArchive = $this->option('days');
        $archiveDate = Carbon::now()->subDays($daysToArchive)->startOfDay();
        
        $this->info("🗄️  Starting daily data archiving for: {$archiveDate->format('Y-m-d')}");
        Log::info('Daily data archiving started', ['archive_date' => $archiveDate->format('Y-m-d')]);

        try {
            // Archive customers from the specified day
            $customersArchived = $this->archiveCustomers($archiveDate);
            
            // Archive related queue events
            $queueEventsArchived = $this->archiveQueueEvents($archiveDate);
            
            // Archive priority verifications
            $priorityVerificationsArchived = $this->archivePriorityVerifications($archiveDate);
            
            // Archive activity logs
            $activityLogsArchived = $this->archiveActivityLogs($archiveDate);
            
            // Archive analytics data
            $analyticsArchived = $this->archiveAnalyticsData($archiveDate);
            
            // Archive table assignments
            $tableAssignmentsArchived = $this->archiveTableAssignments($archiveDate);
            
            // Archive staff sessions
            $staffSessionsArchived = $this->archiveStaffSessions($archiveDate);

            $totalArchived = $customersArchived + $queueEventsArchived + $priorityVerificationsArchived + 
                           $activityLogsArchived + $analyticsArchived + $tableAssignmentsArchived + $staffSessionsArchived;

            $this->info("✅ Archiving completed successfully!");
            $this->info("📊 Summary:");
            $this->info("   • Customers archived: {$customersArchived}");
            $this->info("   • Queue events archived: {$queueEventsArchived}");
            $this->info("   • Priority verifications archived: {$priorityVerificationsArchived}");
            $this->info("   • Activity logs archived: {$activityLogsArchived}");
            $this->info("   • Analytics data archived: {$analyticsArchived}");
            $this->info("   • Table assignments archived: {$tableAssignmentsArchived}");
            $this->info("   • Staff sessions archived: {$staffSessionsArchived}");
            $this->info("   • Total records archived: {$totalArchived}");

            Log::info('Daily data archiving completed successfully', [
                'archive_date' => $archiveDate->format('Y-m-d'),
                'customers_archived' => $customersArchived,
                'queue_events_archived' => $queueEventsArchived,
                'priority_verifications_archived' => $priorityVerificationsArchived,
                'activity_logs_archived' => $activityLogsArchived,
                'analytics_archived' => $analyticsArchived,
                'table_assignments_archived' => $tableAssignmentsArchived,
                'staff_sessions_archived' => $staffSessionsArchived,
                'total_archived' => $totalArchived
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Archiving failed: " . $e->getMessage());
            Log::error('Daily data archiving failed', [
                'error' => $e->getMessage(),
                'archive_date' => $archiveDate->format('Y-m-d')
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Archive customers from the specified date
     */
    private function archiveCustomers($archiveDate)
    {
        $this->info("📋 Archiving customers from {$archiveDate->format('Y-m-d')}...");
        
        // Get customers to archive (based on actual business operation date)
        $customersToArchive = DB::table('queue_customers')
            ->whereDate('registered_at', $archiveDate->format('Y-m-d'))
            ->get();

        if ($customersToArchive->isEmpty()) {
            $this->info("   No customers found for {$archiveDate->format('Y-m-d')}");
            return 0;
        }

        // Create archive table if it doesn't exist
        $this->createArchiveTable('customers_archive');

        // Insert into archive table
        foreach ($customersToArchive as $customer) {
            DB::table('queue_customers_archive')->insert((array) $customer);
        }

        // Delete from main table
        $deletedCount = DB::table('queue_customers')
            ->whereDate('registered_at', $archiveDate->format('Y-m-d'))
            ->delete();

        $this->info("   ✅ Archived {$deletedCount} customers");
        return $deletedCount;
    }

    /**
     * Archive queue events from the specified date
     */
    private function archiveQueueEvents($archiveDate)
    {
        $this->info("📋 Archiving queue events from {$archiveDate->format('Y-m-d')}...");
        
        // Get queue events to archive (for customers from the archive date)
        $queueEventsToArchive = DB::table('customer_events')
            ->whereIn('customer_id', function($query) use ($archiveDate) {
                $query->select('id')
                    ->from('queue_customers_archive')
                    ->whereDate('registered_at', $archiveDate->format('Y-m-d'));
            })
            ->orWhereDate('event_time', $archiveDate->format('Y-m-d'))
            ->get();

        if ($queueEventsToArchive->isEmpty()) {
            $this->info("   No queue events found for {$archiveDate->format('Y-m-d')}");
            return 0;
        }

        // Create archive table if it doesn't exist
        $this->createArchiveTable('queue_events_archive');

        // Insert into archive table
        foreach ($queueEventsToArchive as $event) {
            DB::table('customer_events_archive')->insert((array) $event);
        }

        // Delete from main table
        $deletedCount = DB::table('customer_events')
            ->whereIn('customer_id', function($query) use ($archiveDate) {
                $query->select('id')
                    ->from('queue_customers_archive')
                    ->whereDate('registered_at', $archiveDate->format('Y-m-d'));
            })
            ->orWhereDate('event_time', $archiveDate->format('Y-m-d'))
            ->delete();

        $this->info("   ✅ Archived {$deletedCount} queue events");
        return $deletedCount;
    }

    /**
     * Archive priority verifications from the specified date
     */
    private function archivePriorityVerifications($archiveDate)
    {
        $this->info("📋 Archiving priority verifications from {$archiveDate->format('Y-m-d')}...");
        
        if (!DB::getSchemaBuilder()->hasTable('priority_verifications')) {
            $this->info("   Priority verifications table doesn't exist, skipping...");
            return 0;
        }

        $verificationsToArchive = DB::table('verifications')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->get();

        if ($verificationsToArchive->isEmpty()) {
            $this->info("   No priority verifications found for {$archiveDate->format('Y-m-d')}");
            return 0;
        }

        // Create archive table if it doesn't exist
        $this->createArchiveTable('priority_verifications_archive');

        // Insert into archive table
        foreach ($verificationsToArchive as $verification) {
            DB::table('verifications_archive')->insert((array) $verification);
        }

        // Delete from main table
        $deletedCount = DB::table('verifications')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->delete();

        $this->info("   ✅ Archived {$deletedCount} priority verifications");
        return $deletedCount;
    }

    /**
     * Archive activity logs from the specified date
     */
    private function archiveActivityLogs($archiveDate)
    {
        $this->info("📋 Archiving activity logs from {$archiveDate->format('Y-m-d')}...");
        
        if (!DB::getSchemaBuilder()->hasTable('activity_logs')) {
            $this->info("   Activity logs table doesn't exist, skipping...");
            return 0;
        }

        $logsToArchive = DB::table('activity_logs')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->get();

        if ($logsToArchive->isEmpty()) {
            $this->info("   No activity logs found for {$archiveDate->format('Y-m-d')}");
            return 0;
        }

        // Create archive table if it doesn't exist
        $this->createArchiveTable('activity_logs_archive');

        // Insert into archive table
        foreach ($logsToArchive as $log) {
            DB::table('activity_logs_archive')->insert((array) $log);
        }

        // Delete from main table
        $deletedCount = DB::table('activity_logs')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->delete();

        $this->info("   ✅ Archived {$deletedCount} activity logs");
        return $deletedCount;
    }

    /**
     * Archive analytics data from the specified date
     */
    private function archiveAnalyticsData($archiveDate)
    {
        $this->info("📋 Archiving analytics data from {$archiveDate->format('Y-m-d')}...");
        
        if (!DB::getSchemaBuilder()->hasTable('analytics_data')) {
            $this->info("   Analytics data table doesn't exist, skipping...");
            return 0;
        }

        $analyticsToArchive = DB::table('analytics_data')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->get();

        if ($analyticsToArchive->isEmpty()) {
            $this->info("   No analytics data found for {$archiveDate->format('Y-m-d')}");
            return 0;
        }

        // Create archive table if it doesn't exist
        $this->createArchiveTable('analytics_data_archive');

        // Insert into archive table
        foreach ($analyticsToArchive as $analytics) {
            DB::table('analytics_data_archive')->insert((array) $analytics);
        }

        // Delete from main table
        $deletedCount = DB::table('analytics_data')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->delete();

        $this->info("   ✅ Archived {$deletedCount} analytics data records");
        return $deletedCount;
    }

    /**
     * Archive table assignments from the specified date
     */
    private function archiveTableAssignments($archiveDate)
    {
        $this->info("📋 Archiving table assignments from {$archiveDate->format('Y-m-d')}...");
        
        if (!DB::getSchemaBuilder()->hasTable('table_assignments')) {
            $this->info("   Table assignments table doesn't exist, skipping...");
            return 0;
        }

        $assignmentsToArchive = DB::table('table_assignments')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->get();

        if ($assignmentsToArchive->isEmpty()) {
            $this->info("   No table assignments found for {$archiveDate->format('Y-m-d')}");
            return 0;
        }

        // Create archive table if it doesn't exist
        $this->createArchiveTable('table_assignments_archive');

        // Insert into archive table
        foreach ($assignmentsToArchive as $assignment) {
            DB::table('table_assignments_archive')->insert((array) $assignment);
        }

        // Delete from main table
        $deletedCount = DB::table('table_assignments')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->delete();

        $this->info("   ✅ Archived {$deletedCount} table assignments");
        return $deletedCount;
    }

    /**
     * Archive staff sessions from the specified date
     */
    private function archiveStaffSessions($archiveDate)
    {
        $this->info("📋 Archiving staff sessions from {$archiveDate->format('Y-m-d')}...");
        
        if (!DB::getSchemaBuilder()->hasTable('staff_sessions')) {
            $this->info("   Staff sessions table doesn't exist, skipping...");
            return 0;
        }

        $sessionsToArchive = DB::table('staff_sessions')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->get();

        if ($sessionsToArchive->isEmpty()) {
            $this->info("   No staff sessions found for {$archiveDate->format('Y-m-d')}");
            return 0;
        }

        // Create archive table if it doesn't exist
        $this->createArchiveTable('staff_sessions_archive');

        // Insert into archive table
        foreach ($sessionsToArchive as $session) {
            DB::table('staff_sessions_archive')->insert((array) $session);
        }

        // Delete from main table
        $deletedCount = DB::table('staff_sessions')
            ->whereDate('created_at', $archiveDate->format('Y-m-d'))
            ->delete();

        $this->info("   ✅ Archived {$deletedCount} staff sessions");
        return $deletedCount;
    }

    /**
     * Create archive table with the same structure as the main table
     */
    private function createArchiveTable($tableName)
    {
        if (!DB::getSchemaBuilder()->hasTable($tableName)) {
            $mainTableName = str_replace('_archive', '', $tableName);
            
            // Get the structure of the main table
            $columns = DB::select("SHOW CREATE TABLE {$mainTableName}");
            $createStatement = $columns[0]->{'Create Table'};
            
            // Modify the CREATE statement for the archive table
            $archiveCreateStatement = str_replace(
                "CREATE TABLE `{$mainTableName}`",
                "CREATE TABLE `{$tableName}`",
                $createStatement
            );
            
            // Add archive-specific columns
            $archiveCreateStatement = str_replace(
                'ENGINE=InnoDB',
                'ENGINE=InnoDB COMMENT="Archived data from daily cleanup"',
                $archiveCreateStatement
            );
            
            DB::statement($archiveCreateStatement);
            $this->info("   📁 Created archive table: {$tableName}");
        }
    }
}