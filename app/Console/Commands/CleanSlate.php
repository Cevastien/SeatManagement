<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanSlate extends Command
{
    protected $signature = 'queue:clean-slate';
    protected $description = 'Reset all customer data for fresh testing';

    public function handle()
    {
        if (!$this->confirm('This will delete ALL customer data. Continue?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Cleaning customer data...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Truncate customer-related tables
        DB::table('customers')->truncate();
        DB::table('queue_events')->truncate();
        DB::table('priority_verifications')->truncate();
        DB::table('id_verifications')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->info('✓ All customer data cleared');
        $this->info('✓ Queue reset to 0');
        $this->info('✓ Configuration data preserved');
        $this->info('');
        $this->info('Configuration tables preserved:');
        $this->info('  - priority_type (4 priority types)');
        $this->info('  - staff (2 staff members)');
        $this->info('  - tables (4 restaurant tables)');
        $this->info('  - users (test user)');
        $this->info('');
        $this->info('Ready for fresh testing!');
    }
}
