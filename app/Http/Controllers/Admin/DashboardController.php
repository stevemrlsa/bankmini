<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Officer;
use App\Models\Saver;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Data Stat Cards
        $totalPegawai = Officer::count();
        $totalNasabah = Saver::count();
        $totalNasabahAktif = Saver::where(function($q) {
            $q->where('status', 'active')
              ->orWhere('status', 'Aktif')
              ->orWhere(function($sub) {
                  $sub->whereNull('status')->whereNotNull('account_number');
              });
        })->count();
        $totalSaldo = Saver::sum('saldo') ?? 0;
        
        // Transaksi Hari Ini (Deposit, Withdrawal, Vault_Transfer dll)
        $transaksiHariIni = Transaction::whereDate('created_at', today())
            ->orWhereDate('transaction_date', today())
            ->count();

        // 2. Transaksi Terbaru (5 terakhir)
        $latestTransactions = Transaction::with('saver')
            ->latest()
            ->take(5)
            ->get();

        // 3. Distribusi Pegawai
        $countAdmin = User::whereIn('role', ['Administrator', 'Admin'])->count();
        $countTeller = User::where('role', 'Teller')->count();

        return view('admin.dashboard', compact(
            'totalPegawai',
            'totalNasabah',
            'totalNasabahAktif',
            'totalSaldo',
            'transaksiHariIni',
            'latestTransactions',
            'countAdmin',
            'countTeller'
        ));
    }
}
