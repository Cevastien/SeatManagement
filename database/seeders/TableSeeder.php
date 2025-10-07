<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Table;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create tables for different party sizes
        
        // Solo tables (1-2 people) - 8 tables
        for ($i = 1; $i <= 8; $i++) {
            Table::create([
                'table_number' => "S{$i}",
                'max_capacity' => 2,
                'status' => 'available',
                'is_vip' => false,
            ]);
        }
        
        // Standard tables (3-4 people) - 6 tables
        for ($i = 1; $i <= 6; $i++) {
            Table::create([
                'table_number' => "T{$i}",
                'max_capacity' => 4,
                'status' => 'available',
                'is_vip' => false,
            ]);
        }
        
        // Large tables (5-6 people) - 4 tables
        for ($i = 1; $i <= 4; $i++) {
            Table::create([
                'table_number' => "L{$i}",
                'max_capacity' => 6,
                'status' => 'available',
                'is_vip' => false,
            ]);
        }
        
        // VIP table (8 people) - 1 table
        Table::create([
            'table_number' => "VIP",
            'max_capacity' => 8,
            'status' => 'available',
            'is_vip' => true,
        ]);
        
        // Optionally occupy some tables for testing
        if (config('app.env') === 'local') {
            // Occupy 2 solo tables for testing
            $soloTable1 = Table::where('table_number', 'S1')->first();
            $soloTable1->update([
                'status' => 'occupied',
                'occupied_at' => now()->subMinutes(20), // Occupied 20 minutes ago
                'estimated_departure' => now()->addMinutes(10), // Will be free in 10 minutes
            ]);
            
            $soloTable2 = Table::where('table_number', 'S2')->first();
            $soloTable2->update([
                'status' => 'occupied',
                'occupied_at' => now()->subMinutes(5), // Occupied 5 minutes ago
                'estimated_departure' => now()->addMinutes(25), // Will be free in 25 minutes
            ]);
            
            // Occupy 1 standard table for testing
            $standardTable1 = Table::where('table_number', 'T1')->first();
            $standardTable1->update([
                'status' => 'occupied',
                'occupied_at' => now()->subMinutes(35), // Occupied 35 minutes ago
                'estimated_departure' => now()->addMinutes(10), // Will be free in 10 minutes
            ]);
        }
    }
}
