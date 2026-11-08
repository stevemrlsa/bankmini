<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\DailyReportService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reports:auto-close-shifts', function () {
    $this->info('Memeriksa shift teller untuk penutupan otomatis jam 16:30...');
    $count = DailyReportService::autoClosePendingShifts();
    $this->info("Selesai! {$count} shift teller berhasil diproses dan ditutup otomatis.");
})->purpose('Otomatis tutup shift teller dan kirim laporan harian pada pukul 16:30');

// Jadwalkan otomatisasi setiap hari pada pukul 16:30 WIB
Schedule::command('reports:auto-close-shifts')
    ->dailyAt('16:30')
    ->timezone('Asia/Jakarta');
