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
    protected function getStatsData(): array
    {
        $totalPegawai = User::whereIn('role', ['Administrator', 'Admin', 'Admin Utama', 'admin_utama', 'Teller', 'Supervisor'])->count();
        $totalNasabah = Saver::count();
        $totalNasabahAktif = Saver::whereIn('status', ['active', 'Aktif'])
            ->where(function ($q) {
                $q->whereNull('grade_class')
                  ->orWhere('grade_class', 'not like', 'Lulus%');
            })
            ->count();

        $totalSaldo = (float) (Saver::sum('saldo') ?? 0);

        $transaksiHariIni = Transaction::where('transaction_type', '!=', 'Vault_Transfer')
            ->where(function ($q) {
                $q->whereDate('created_at', today())
                  ->orWhereDate('transaction_date', today());
            })->count();

        $countAdmin = User::whereIn('role', ['Administrator', 'Admin', 'Admin Utama', 'admin_utama', 'Supervisor'])->count();
        $countTeller = User::where('role', 'Teller')->count();

        return compact(
            'totalPegawai',
            'totalNasabah',
            'totalNasabahAktif',
            'totalSaldo',
            'transaksiHariIni',
            'countAdmin',
            'countTeller'
        );
    }

    public function index()
    {
        $stats = $this->getStatsData();

        // Transaksi Terbaru (5 terakhir nasabah)
        $latestTransactions = Transaction::with(['saver', 'user'])
            ->where('transaction_type', '!=', 'Vault_Transfer')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', array_merge($stats, [
            'latestTransactions' => $latestTransactions,
        ]));
    }

    /**
     * Endpoint JSON Real-Time untuk memperbarui Stat Cards & Transaksi Terbaru di Dashboard Admin secara otomatis
     */
    public function realtimeStats()
    {
        $stats = $this->getStatsData();
        $latestTransactions = Transaction::with(['saver'])
            ->where('transaction_type', '!=', 'Vault_Transfer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $isDeposit = in_array(strtolower($tx->transaction_type), ['deposit', 'setor', 'setoran']);
                return [
                    'id'           => $tx->id,
                    'student_name' => $tx->saver->student_name ?? $tx->saver->name ?? 'Nasabah',
                    'grade_class'  => $tx->saver->grade_class ?? $tx->saver->student_id ?? '-',
                    'is_deposit'   => $isDeposit,
                    'type_label'   => $isDeposit ? 'Setoran tunai' : 'Penarikan tunai',
                    'time'         => $tx->created_at ? $tx->created_at->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-',
                    'amount_fmt'   => ($isDeposit ? '+Rp ' : '-Rp ') . number_format($tx->amount, 0, ',', '.'),
                ];
            });

        return response()->json([
            'totalPegawai'      => number_format($stats['totalPegawai']),
            'totalNasabah'      => number_format($stats['totalNasabah']),
            'totalNasabahAktif' => number_format($stats['totalNasabahAktif']),
            'totalSaldo'        => 'Rp ' . number_format($stats['totalSaldo'], 0, ',', '.'),
            'transaksiHariIni'  => number_format($stats['transaksiHariIni']),
            'countAdmin'        => number_format($stats['countAdmin']),
            'countTeller'       => number_format($stats['countTeller']),
            'latestTransactions'=> $latestTransactions,
        ]);
    }

    /**
     * Halaman Ringkasan Total Saldo Tersimpan (Rekap per Kelas, Nasabah Aktif vs Alumni, & Top Penabung)
     */
    public function balanceSummary(Request $request)
    {
        $totalSaldoAll = (float) (Saver::sum('saldo') ?? 0);

        $activeQuery = Saver::whereIn('status', ['active', 'Aktif'])
            ->where(function ($q) {
                $q->whereNull('grade_class')
                  ->orWhere('grade_class', 'not like', 'Lulus%');
            });

        $saldoNasabahAktif = (float) ((clone $activeQuery)->sum('saldo') ?? 0);
        $jumlahNasabahAktif = (clone $activeQuery)->count();

        $alumniQuery = Saver::where(function ($q) {
            $q->where('grade_class', 'like', 'Lulus%')
              ->orWhere('status', 'graduated');
        });
        $saldoAlumni = (float) ((clone $alumniQuery)->sum('saldo') ?? 0);
        $jumlahAlumni = (clone $alumniQuery)->count();

        // Rekapitulasi Saldo per Kelas
        $classRecap = Saver::selectRaw('COALESCE(NULLIF(grade_class, ""), "Tanpa Kelas") as class_name, COUNT(*) as total_students, SUM(CASE WHEN status IN ("active", "Aktif") THEN 1 ELSE 0 END) as active_students, SUM(saldo) as total_balance')
            ->where(function ($q) {
                $q->where('saldo', '>', 0)
                  ->orWhereIn('status', ['active', 'Aktif']);
            })
            ->groupBy('class_name')
            ->orderByDesc('total_balance')
            ->get();

        // 10 Penabung dengan Saldo Tertinggi
        $topSavers = Saver::where('saldo', '>', 0)
            ->orderByDesc('saldo')
            ->take(10)
            ->get();

        return view('admin.balance-summary', compact(
            'totalSaldoAll',
            'saldoNasabahAktif',
            'jumlahNasabahAktif',
            'saldoAlumni',
            'jumlahAlumni',
            'classRecap',
            'topSavers'
        ));
    }

    /**
     * Halaman Riwayat Seluruh Transaksi (Tujuan klik Card Transaksi Hari Ini di Dashboard Admin)
     */
    public function transactions(Request $request)
    {
        $query = Transaction::with(['saver', 'user'])
            ->where('transaction_type', '!=', 'Vault_Transfer');

        // Filter Tanggal (Default: menampilkan semua jika tidak difilter, atau sesuai parameter date)
        if ($request->filled('date')) {
            $date = $request->date;
            $query->where(function ($q) use ($date) {
                $q->whereDate('created_at', $date)
                  ->orWhereDate('transaction_date', $date);
            });
        }

        // Filter Jenis Transaksi
        if ($request->filled('type')) {
            if ($request->type === 'deposit') {
                $query->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran']);
            } elseif ($request->type === 'withdrawal') {
                $query->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan']);
            }
        }

        // Filter Pencarian Nama / NIS / No. Rekening
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('saver', function ($q) use ($search) {
                $q->where('student_name', 'like', '%' . $search . '%')
                  ->orWhere('student_id', 'like', '%' . $search . '%')
                  ->orWhere('account_number', 'like', '%' . $search . '%');
            });
        }

        $totalSetoranFiltered = (clone $query)->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])->sum('amount');
        $totalPenarikanFiltered = (clone $query)->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])->sum('amount');
        $totalCountFiltered = (clone $query)->count();

        $transactions = $query->latest()->paginate(20)->withQueryString();

        return view('admin.transactions', compact(
            'transactions',
            'totalSetoranFiltered',
            'totalPenarikanFiltered',
            'totalCountFiltered'
        ));
    }
}
