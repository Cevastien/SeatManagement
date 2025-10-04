<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ERDSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert sample staff
        DB::table('staff')->insert([
            [
                'name' => 'John Doe',
                'role' => 'Host',
                'email' => 'john@restaurant.com',
                'password' => bcrypt('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maria Garcia',
                'role' => 'Server',
                'email' => 'maria@restaurant.com',
                'password' => bcrypt('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert sample priority types
        DB::table('priority_type')->insert([
            [
                'description' => 'Senior Citizen',
                'requires_id' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'PWD (Person with Disability)',
                'requires_id' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Pregnant',
                'requires_id' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Regular Customer',
                'requires_id' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert sample tables
        DB::table('tables')->insert([
            [
                'number' => '1',
                'capacity' => 4,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => '2',
                'capacity' => 2,
                'status' => 'occupied',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => '3',
                'capacity' => 6,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'number' => '4',
                'capacity' => 4,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Note: Sample customer, queue, and assignment data would be inserted here
        // For now, we'll just populate the reference tables

        // Insert analytics record
        DB::table('analytics_logs')->insert([
            [
                'id' => 1,
                'date' => today(),
                'total_walkins' => 150,
                'total_groups' => 25,
                'total_priority' => 45,
                'avg_wait_time' => 18.5,
                'avg_occupancy_duration' => 45.2,
                'peak_hours' => json_encode(['12:00-13:00', '18:00-20:00']),
                'no_shows' => 5,
                'cancelled' => 8,
                'efficiency_score' => 85.5,
                'hourly_breakdown' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
