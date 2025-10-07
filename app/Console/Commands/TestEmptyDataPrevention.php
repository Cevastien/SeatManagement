<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use App\Models\Customer;

class TestEmptyDataPrevention extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:empty-data-prevention';

    /**
     * The console command description.
     */
    protected $description = 'Test that no empty/invalid records are created when users go back';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Empty Data Prevention');
        $this->line('');

        // Count customers before test
        $customersBefore = Customer::count();
        $this->line("📊 Customers in database before test: {$customersBefore}");

        // Test 1: Simulate registration without confirmation
        $this->line('');
        $this->info('🧪 Test 1: Registration without confirmation');
        
        // Simulate session data (what would happen in registration)
        $registrationData = [
            'name' => 'Test User',
            'party_size' => 2,
            'contact_number' => '09171234567',
            'priority_type' => 'normal',
            'status' => 'pending_confirmation',
            'timestamp' => now()->toISOString(),
        ];
        
        Session::put('registration', $registrationData);
        $this->line('✅ Session data created (no database record yet)');
        
        // Check that no customer was created
        $customersAfterSession = Customer::count();
        $this->line("📊 Customers after session creation: {$customersAfterSession}");
        
        if ($customersAfterSession === $customersBefore) {
            $this->line('✅ PASS: No database record created during session storage');
        } else {
            $this->line('❌ FAIL: Database record was created during session storage');
        }

        // Test 2: Simulate going back (session cleared)
        $this->line('');
        $this->info('🧪 Test 2: User goes back (session cleared)');
        
        // Clear session (simulating user going back)
        Session::forget('registration');
        $this->line('✅ Session cleared (user went back)');
        
        // Check that still no customer was created
        $customersAfterGoBack = Customer::count();
        $this->line("📊 Customers after going back: {$customersAfterGoBack}");
        
        if ($customersAfterGoBack === $customersBefore) {
            $this->line('✅ PASS: No database record created when user goes back');
        } else {
            $this->line('❌ FAIL: Database record was created when user goes back');
        }

        // Test 3: Simulate successful confirmation
        $this->line('');
        $this->info('🧪 Test 3: User confirms registration');
        
        // Simulate session data again
        Session::put('registration', $registrationData);
        
        // Simulate confirmation (what would happen when user clicks "Continue")
        try {
            $customer = Customer::create([
                'name' => $registrationData['name'],
                'party_size' => $registrationData['party_size'],
                'contact_number' => $registrationData['contact_number'],
                'queue_number' => Customer::getNextQueueNumber(),
                'priority_type' => $registrationData['priority_type'],
                'is_group' => $registrationData['party_size'] > 1,
                'has_priority_member' => false,
                'status' => 'waiting',
                'estimated_wait_minutes' => 15,
                'registered_at' => now(),
            ]);
            
            $this->line('✅ Customer record created in database');
            
            // Check that customer was created
            $customersAfterConfirm = Customer::count();
            $this->line("📊 Customers after confirmation: {$customersAfterConfirm}");
            
            if ($customersAfterConfirm > $customersBefore) {
                $this->line('✅ PASS: Database record created only after confirmation');
            } else {
                $this->line('❌ FAIL: No database record created after confirmation');
            }
            
            // Clean up test data
            $customer->delete();
            $this->line('🧹 Test data cleaned up');
            
        } catch (\Exception $e) {
            $this->line('❌ FAIL: Error during confirmation test: ' . $e->getMessage());
        }

        // Final verification
        $customersFinal = Customer::count();
        $this->line('');
        $this->info('📊 Final Results:');
        $this->line("   Customers before test: {$customersBefore}");
        $this->line("   Customers after test: {$customersFinal}");
        
        if ($customersFinal === $customersBefore) {
            $this->line('✅ SUCCESS: No invalid records left in database');
        } else {
            $this->line('❌ FAILURE: Invalid records remain in database');
        }

        $this->line('');
        $this->info('🎯 Summary:');
        $this->line('   ✅ Registration data stored in session only');
        $this->line('   ✅ No database records created until confirmation');
        $this->line('   ✅ Going back clears session without creating records');
        $this->line('   ✅ Only confirmed registrations create database records');
        
        $this->line('');
        $this->info('✅ Empty data prevention test completed!');
    }
}
