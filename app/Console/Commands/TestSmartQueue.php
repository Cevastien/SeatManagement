<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmartQueueEstimator;
use App\Models\Customer;

class TestSmartQueue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'queue:test-smart';

    /**
     * The console command description.
     */
    protected $description = 'Test the smart queue estimation logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Smart Queue Estimation Logic');
        $this->line('');

        $estimator = new SmartQueueEstimator();

        // Test different scenarios
        $testCases = [
            ['party_size' => 1, 'priority_type' => 'normal', 'description' => 'Solo customer (regular)'],
            ['party_size' => 2, 'priority_type' => 'normal', 'description' => 'Couple (regular)'],
            ['party_size' => 4, 'priority_type' => 'normal', 'description' => 'Standard group (regular)'],
            ['party_size' => 6, 'priority_type' => 'normal', 'description' => 'Large group (regular)'],
            ['party_size' => 2, 'priority_type' => 'senior', 'description' => 'Senior couple (priority)'],
            ['party_size' => 4, 'priority_type' => 'pwd', 'description' => 'PWD group (priority)'],
        ];

        foreach ($testCases as $testCase) {
            // Create a temporary customer for testing
            $tempCustomer = new Customer([
                'party_size' => $testCase['party_size'],
                'priority_type' => $testCase['priority_type'],
                'status' => 'waiting',
                'created_at' => now(),
            ]);

            $result = $estimator->calculateWaitTime($tempCustomer);

            $this->line("📋 {$testCase['description']}");
            $this->line("   Wait Time: {$result['wait_formatted']}");
            $this->line("   Customers Ahead: {$result['customers_ahead']}");
            if ($result['estimated_table_time']) {
                $this->line("   Next Table Available: {$result['estimated_table_time']}");
            }
            $this->line('');
        }

        // Test queue stats
        $this->info('📊 Queue Statistics:');
        $stats = $estimator->getQueueStats();
        
        foreach ($stats as $capacity => $stat) {
            $this->line("   Tables for {$capacity} people:");
            $this->line("     Total: {$stat['total_tables']}");
            $this->line("     Occupied: {$stat['occupied_tables']}");
            $this->line("     Available: {$stat['available_tables']}");
            $this->line("     Waiting: {$stat['waiting_customers']}");
            $this->line('');
        }

        $this->info('✅ Smart queue estimation test completed!');
    }
}
