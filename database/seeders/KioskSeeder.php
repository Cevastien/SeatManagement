<?php

namespace Database\Seeders;

use App\Models\Kiosk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KioskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kiosks = [
            [
                'name' => 'Main Entrance Kiosk',
                'location' => 'Roxas, Davao City',
                'description' => 'Primary kiosk at the main entrance',
                'status' => 'active',
                'ip_address' => '192.168.1.100',
            ],
            [
                'name' => 'Drive-Thru Kiosk',
                'location' => 'Roxas, Davao City',
                'description' => 'Kiosk for drive-thru customers',
                'status' => 'active',
                'ip_address' => '192.168.1.101',
            ],
            [
                'name' => 'Lobby Kiosk',
                'location' => 'Roxas, Davao City',
                'description' => 'Kiosk in the main lobby area',
                'status' => 'active',
                'ip_address' => '192.168.1.102',
            ],
            [
                'name' => 'Back Entrance Kiosk',
                'location' => 'Roxas, Davao City',
                'description' => 'Secondary kiosk at the back entrance',
                'status' => 'inactive',
                'ip_address' => '192.168.1.103',
            ],
        ];

        foreach ($kiosks as $kiosk) {
            Kiosk::create($kiosk);
        }
    }
}
