<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Menambahkan User Tambahan Pegawai
        $users = [
            ['username' => 'admin_sistem', 'officer_name' => 'Ahmad Admin', 'role' => 'Administrator'],
            ['username' => 'teller_dua', 'officer_name' => 'Rini Teller', 'role' => 'Teller'],
            ['username' => 'teller_tiga', 'officer_name' => 'Bowo Teller', 'role' => 'Teller'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['username' => $u['username']],
                [
                    'password' => Hash::make('password123'),
                    'officer_name' => $u['officer_name'],
                    'role' => $u['role'],
                    'status' => 'Aktif'
                ]
            );
        }

        // Data Anggota Siswa Baru (Members)
        // 1. Belum Aktif (Perlu aktivasi dan set PIN oleh Admin)
        $userStudent1 = User::firstOrCreate(
            ['username' => 'siswa_budi'],
            [
                'password' => Hash::make('password123'),
                'officer_name' => 'Budi Pratama',
                'role' => 'Saver',
                'status' => 'Aktif'
            ]
        );
        \App\Models\Member::firstOrCreate(
            ['user_id' => $userStudent1->id],
            [
                'student_id' => '2026001',
                'name' => 'Budi Pratama',
                'graduation_year' => '2027',
                'status' => 'inactive',
            ]
        );

        // 2. Sudah Aktif (Sudah aktivasi PIN 123456, siap setoran awal >= 10.000 di Teller)
        $userStudent2 = User::firstOrCreate(
            ['username' => 'siswa_siti'],
            [
                'password' => Hash::make('password123'),
                'officer_name' => 'Siti Aisyah',
                'role' => 'Saver',
                'status' => 'Aktif'
            ]
        );
        \App\Models\Member::firstOrCreate(
            ['user_id' => $userStudent2->id],
            [
                'student_id' => '2026002',
                'name' => 'Siti Aisyah',
                'graduation_year' => '2027',
                'security_pin' => Hash::make('123456'),
                'status' => 'active',
            ]
        );

        // 2. Menambahkan Saver (Nasabah Siswa) - Minimal 5
        $savers = [
            ['student_id' => '20240101', 'name' => 'Ahmad Rizki', 'class' => 'Kelas 1'],
            ['student_id' => '20240102', 'name' => 'Budi Santoso', 'class' => 'Kelas 2'],
            ['student_id' => '20240103', 'name' => 'Citra Lestari', 'class' => 'Kelas 3'],
            ['student_id' => '20240104', 'name' => 'Dewi Sartika', 'class' => 'Kelas 4'],
            ['student_id' => '20240105', 'name' => 'Eko Prasetyo', 'class' => 'Kelas 5'],
            ['student_id' => '20240106', 'name' => 'Fahri Hamzah', 'class' => 'Kelas 6'],
        ];

        $year = date('Y');
        $saverRecords = [];
        $idx = 1;
        foreach ($savers as $c) {
            $accountNumber = $year . str_pad($idx, 4, '0', STR_PAD_LEFT);
            $saver = Saver::firstOrCreate(
                ['student_id' => $c['student_id']],
                [
                    'account_number' => $accountNumber,
                    'student_name' => $c['name'],
                    'grade_class' => $c['class'],
                    'security_pin' => Hash::make('123456'), // Default PIN
                    'saldo' => 0
                ]
            );
            $saverRecords[] = $saver;
            $idx++;
        }

        // 3. Menambahkan Dummy Transaksi (Setoran & Penarikan) agar laporan terisi
        $teller = User::where('role', 'Teller')->first();

        foreach ($saverRecords as $saver) {
            // Berikan 2 setoran dan 1 penarikan untuk tiap nasabah
            
            // Transaksi 1: Setoran Awal (Rp 50.000)
            Transaction::create([
                'saver_id' => $saver->id,
                'user_id' => $teller->id, // Diproses oleh teller
                'transaction_date' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
                'transaction_type' => 'Deposit',
                'amount' => 50000,
                'status' => 'Approved',
                'created_at' => Carbon::now()->subDays(2),
            ]);
            $saver->saldo += 50000;
            
            // Transaksi 2: Setoran Kedua (Rp 25.000)
            Transaction::create([
                'saver_id' => $saver->id,
                'user_id' => $teller->id,
                'transaction_date' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s'),
                'transaction_type' => 'Deposit',
                'amount' => 25000,
                'status' => 'Approved',
                'created_at' => Carbon::now()->subDays(1),
            ]);
            $saver->saldo += 25000;

            // Transaksi 3: Penarikan (Rp 15.000)
            Transaction::create([
                'saver_id' => $saver->id,
                'user_id' => $teller->id,
                'transaction_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'transaction_type' => 'Withdrawal',
                'amount' => 15000,
                'status' => 'Approved',
                'created_at' => Carbon::now(),
            ]);
            $saver->saldo -= 15000;

            $saver->save();
        }
    }
}
