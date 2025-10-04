<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KioskUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Kiosk Device',
            'email' => 'kiosk@seatmanagement.com',
            'password' => Hash::make('kiosk123'),
            'role' => 'kiosk',
            'email_verified_at' => now(),
        ]);
    }
}
