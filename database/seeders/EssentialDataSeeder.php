<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EssentialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🌱 Seeding essential data for queue management system...\n";

        // 1. Create staff users
        $this->createStaffUsers();
        
        // 2. Create restaurant tables
        $this->createRestaurantTables();
        
        // 3. Create system settings
        $this->createSystemSettings();
        
        // 4. Create queue positions for existing customers
        $this->createQueuePositions();
        
        // 5. Create table reservations
        $this->createTableReservations();
        
        // 6. Create staff login sessions
        $this->createStaffLoginSessions();
        
        // 7. Create system activity logs
        $this->createSystemActivityLogs();
        
        // 8. Create daily analytics
        $this->createDailyAnalytics();
        
        // 9. Create ID verifications
        $this->createIdVerifications();
        
        // 10. Create priority reviews
        $this->createPriorityReviews();
        
        // 11. Fix encrypted pregnant customer names
        $this->fixEncryptedPregnantNames();

        echo "✅ Essential data seeding completed!\n";
    }

    private function createStaffUsers()
    {
        echo "👥 Creating staff users...\n";
        
        $existingCount = DB::table('staff_users')->count();
        if ($existingCount > 0) {
            echo "ℹ️ Staff users already exist ({$existingCount} records)\n";
            return;
        }
        
        $staffUsers = [
            [
                'email' => 'admin@restaurant.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'manager@restaurant.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'manager',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'host@restaurant.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'host',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('staff_users')->insert($staffUsers);
        echo "✅ Created " . count($staffUsers) . " staff users\n";
    }

    private function createRestaurantTables()
    {
        echo "🪑 Creating restaurant tables...\n";
        
        $existingCount = DB::table('restaurant_tables')->count();
        if ($existingCount > 0) {
            echo "ℹ️ Restaurant tables already exist ({$existingCount} records)\n";
            return;
        }
        
        $tables = [
            [
                'number' => 'T01',
                'capacity' => 2,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => 'T02',
                'capacity' => 4,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => 'T03',
                'capacity' => 6,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => 'VIP01',
                'capacity' => 8,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('restaurant_tables')->insert($tables);
        echo "✅ Created " . count($tables) . " restaurant tables\n";
    }

    private function createSystemSettings()
    {
        echo "⚙️ Creating system settings...\n";
        
        $settings = [
            [
                'key' => 'party_size_min',
                'value' => '1',
                'type' => 'integer',
                'category' => 'queue',
                'description' => 'Minimum party size allowed',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'party_size_max',
                'value' => '20',
                'type' => 'integer',
                'category' => 'queue',
                'description' => 'Maximum party size allowed',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'avg_dining_duration',
                'value' => '60',
                'type' => 'integer',
                'category' => 'queue',
                'description' => 'Average dining duration in minutes',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'table_cleaning_duration',
                'value' => '5',
                'type' => 'integer',
                'category' => 'queue',
                'description' => 'Table cleaning duration in minutes',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('settings')->insert($settings);
        echo "✅ Created " . count($settings) . " system settings\n";
    }

    private function createQueuePositions()
    {
        echo "📍 Creating queue positions...\n";
        
        $customers = DB::table('queue_customers')->where('status', 'waiting')->get();
        $positions = [];
        
        foreach ($customers as $index => $customer) {
            $positions[] = [
                'customer_id' => $customer->id,
                'table_id' => null,
                'queue_number' => $index + 1,
                'party_size' => $customer->party_size,
                'priority_type' => $customer->priority_type ?? 'normal',
                'priority_verified' => $customer->priority_type ? 1 : 0,
                'status' => 'waiting',
                'registration_time' => $customer->registered_at,
                'estimated_wait' => $customer->estimated_wait_minutes,
                'people_ahead' => $index,
                'seated_time' => null,
                'completed_time' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($positions)) {
            DB::table('queue_positions')->insert($positions);
            echo "✅ Created " . count($positions) . " queue positions\n";
        } else {
            echo "ℹ️ No waiting customers to create queue positions for\n";
        }
    }

    private function createTableReservations()
    {
        echo "🍽️ Creating table reservations...\n";
        
        $seatedCustomers = DB::table('queue_customers')
            ->where('status', 'seated')
            ->whereNotNull('table_id')
            ->get();
        
        $reservations = [];
        
        foreach ($seatedCustomers as $customer) {
            $reservations[] = [
                'table_id' => $customer->table_id,
                'queue_id' => $customer->id,
                'assigned_at' => $customer->seated_at ?? now(),
                'status' => 'active',
                'notes' => 'Table assigned to customer',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($reservations)) {
            DB::table('table_reservations')->insert($reservations);
            echo "✅ Created " . count($reservations) . " table reservations\n";
        } else {
            echo "ℹ️ No seated customers to create table reservations for\n";
        }
    }

    private function createStaffLoginSessions()
    {
        echo "🔐 Creating staff login sessions...\n";
        
        $staffUsers = DB::table('staff_users')->get();
        $sessions = [];
        
        foreach ($staffUsers as $staff) {
            $sessions[] = [
                'staff_id' => $staff->id,
                'login_time' => now()->subHours(rand(1, 8)),
                'logout_time' => null,
                'ip_address' => '127.0.0.1',
                'is_active' => true,
                'created_at' => now(),
            ];
        }

        DB::table('staff_login_sessions')->insert($sessions);
        echo "✅ Created " . count($sessions) . " staff login sessions\n";
    }

    private function createSystemActivityLogs()
    {
        echo "📊 Creating system activity logs...\n";
        
        $activities = [
            [
                'staff_id' => null,
                'action_type' => 'system',
                'details' => 'Customer registration system initialized',
                'ip_address' => '127.0.0.1',
                'timestamp' => now()->subDays(1),
                'created_at' => now(),
            ],
            [
                'staff_id' => 1,
                'action_type' => 'queue_update',
                'details' => 'Queue positions updated - 25 customers in queue',
                'ip_address' => '127.0.0.1',
                'timestamp' => now()->subHours(2),
                'created_at' => now(),
            ],
            [
                'staff_id' => 1,
                'action_type' => 'verification',
                'details' => 'Priority verification completed for pregnant customer',
                'ip_address' => '127.0.0.1',
                'timestamp' => now()->subHours(1),
                'created_at' => now(),
            ]
        ];

        DB::table('system_activity_logs')->insert($activities);
        echo "✅ Created " . count($activities) . " system activity logs\n";
    }

    private function createDailyAnalytics()
    {
        echo "📈 Creating daily analytics...\n";
        
        $analytics = [
            [
                'date' => now()->toDateString(),
                'total_customers' => 25,
                'avg_wait_time' => 15,
                'table_utilization' => 75.50,
                'peak_hours' => json_encode(['12:00', '13:00', '19:00']),
                'revenue_data' => json_encode([
                    'total_revenue' => 2500.00,
                    'avg_per_customer' => 100.00,
                    'peak_hour_revenue' => 800.00
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('daily_analytics')->insert($analytics);
        echo "✅ Created " . count($analytics) . " daily analytics records\n";
    }

    private function createIdVerifications()
    {
        echo "🆔 Creating ID verifications...\n";
        
        $priorityCustomers = DB::table('queue_customers')
            ->whereIn('priority_type', ['senior', 'pwd'])
            ->where('id_verification_status', 'verified')
            ->get();
        
        $verifications = [];
        
        foreach ($priorityCustomers as $customer) {
            $verifications[] = [
                'customer_id' => $customer->id,
                'verification_method' => 'manual',
                'name' => $customer->name,
                'birthdate' => $customer->birthdate,
                'id_type' => 'Senior Citizen ID',
                'id_number' => 'SC' . rand(100000, 999999),
                'verification_result' => 'verified',
                'confidence_score' => 0.9500,
                'field_confidence' => json_encode([
                    'name' => 0.95,
                    'birthdate' => 0.90,
                    'id_number' => 0.98
                ]),
                'raw_extracted_data' => json_encode([
                    'name' => $customer->name,
                    'birthdate' => $customer->birthdate
                ]),
                'processing_details' => json_encode([
                    'method' => 'manual_verification',
                    'staff_member' => 'Admin User'
                ]),
                'verification_source' => 'staff_manual',
                'processing_time_ms' => 5000,
                'attempt_number' => 1,
                'error_message' => null,
                'error_details' => null,
                'is_manual_override' => true,
                'requires_review' => false,
                'review_notes' => null,
                'verified_at' => $customer->id_verified_at ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($verifications)) {
            DB::table('id_verifications')->insert($verifications);
            echo "✅ Created " . count($verifications) . " ID verifications\n";
        } else {
            echo "ℹ️ No priority customers to create ID verifications for\n";
        }
    }

    private function createPriorityReviews()
    {
        echo "⭐ Creating priority reviews...\n";
        
        $priorityVerifications = DB::table('priority_verification_requests')
            ->where('status', 'verified')
            ->get();
        
        $reviews = [];
        
        foreach ($priorityVerifications as $verification) {
            $customer = DB::table('queue_customers')
                ->where('name', $verification->customer_name)
                ->where('priority_type', $verification->priority_type)
                ->first();
            
            if ($customer) {
                $reviews[] = [
                    'queue_id' => $customer->id,
                    'review_type' => $verification->priority_type,
                    'status' => 'verified',
                    'verified_by' => 1, // Staff user ID
                    'verification_time' => $verification->verified_at,
                    'notes' => 'Priority status verified and approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($reviews)) {
            DB::table('priority_reviews')->insert($reviews);
            echo "✅ Created " . count($reviews) . " priority reviews\n";
        } else {
            echo "ℹ️ No verified priority requests to create reviews for\n";
        }
    }

    private function fixEncryptedPregnantNames()
    {
        echo "🔓 Fixing encrypted pregnant customer names...\n";
        
        $encryptedRequests = DB::table('priority_verification_requests')
            ->where('priority_type', 'pregnant')
            ->where('customer_name', 'like', 'eyJ%')
            ->get();
        
        $fixed = 0;
        
        foreach ($encryptedRequests as $request) {
            try {
                // Try to decrypt the name
                $decryptedName = decrypt($request->customer_name);
                
                // Update the record with decrypted name
                DB::table('priority_verification_requests')
                    ->where('id', $request->id)
                    ->update(['customer_name' => $decryptedName]);
                
                $fixed++;
                echo "✅ Fixed encrypted name: {$decryptedName}\n";
                
            } catch (Exception $e) {
                echo "⚠️ Could not decrypt name for request ID {$request->id}: {$e->getMessage()}\n";
            }
        }
        
        echo "✅ Fixed {$fixed} encrypted pregnant customer names\n";
    }
}