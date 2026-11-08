<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SettingsController extends Controller
{
    public function index()
    {
        // Ambil setting min_saldo_mengendap
        $minSaldo = SystemSetting::where('key', 'min_saldo_mengendap')->first();
        
        // Jika belum ada, set default value untuk tampilan form
        if (!$minSaldo) {
            $minSaldo = new SystemSetting([
                'key' => 'min_saldo_mengendap',
                'value' => '10000',
                'description' => 'Batas saldo minimum yang tidak bisa ditarik oleh nasabah'
            ]);
        }

        return view('admin.settings.index', compact('minSaldo'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'min_saldo_mengendap' => 'required|numeric|min:0'
        ]);

        $setting = SystemSetting::firstOrCreate(
            ['key' => 'min_saldo_mengendap'],
            ['description' => 'Batas saldo minimum yang tidak bisa ditarik oleh nasabah']
        );

        $setting->value = $request->min_saldo_mengendap;
        $setting->save();

        \App\Models\AuditLog::record(
            'UPDATE_PENGATURAN',
            'Pengaturan Sistem',
            'Memperbarui batas saldo minimum mengendap menjadi Rp ' . number_format((float) $request->min_saldo_mengendap, 0, ',', '.'),
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Administrator'
        );

        return back()->with('success', 'Konfigurasi batas saldo minimum berhasil diperbarui!');
    }
}
