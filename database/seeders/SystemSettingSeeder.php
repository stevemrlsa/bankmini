<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'min_saldo_mengendap'],
            [
                'value' => '10000',
                'description' => 'Batas saldo minimum yang tidak bisa ditarik oleh nasabah'
            ]
        );
    }
}
