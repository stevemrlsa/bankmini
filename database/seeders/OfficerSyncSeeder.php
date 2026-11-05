<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Officer;

class OfficerSyncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffUsers = User::whereIn('role', ['Administrator', 'Admin', 'Teller', 'Supervisor'])->get();

        foreach ($staffUsers as $user) {
            Officer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'             => null,
                    'officer_name'    => $user->officer_name ?? $user->username,
                    'whatsapp_number' => $user->whatsapp_number ?? null,
                    'address'         => null,
                    'status'          => $user->status ?? 'Aktif',
                ]
            );
        }
    }
}

