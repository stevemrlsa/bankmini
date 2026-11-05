<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admin123'), 
                'officer_name' => 'Administrator Utama',
                'role' => 'Administrator',
                'status' => 'Aktif'
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin_pusat'],
            [
                'password' => Hash::make('admin123'), 
                'officer_name' => 'Budi Administrator',
                'role' => 'Administrator',
                'status' => 'Aktif'
            ]
        );

        User::updateOrCreate(
            ['username' => 'teller_satu'],
            [
                'password' => Hash::make('teller123'),
                'officer_name' => 'Siti Teller',
                'role' => 'Teller',
                'status' => 'Aktif'
            ]
        );

        User::updateOrCreate(
            ['username' => 'spv_utama'],
            [
                'password' => Hash::make('spv123'),
                'officer_name' => 'Bapak Kepala Bank Mini (Admin)',
                'role' => 'Administrator',
                'status' => 'Aktif'
            ]
        );
    }
}