<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;
use App\Models\Saver;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestController extends Controller
{
    /**
     * Tampilkan daftar pengajuan / janjian penarikan dana besar (> Rp 1.000.000)
     */
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['saver', 'tellerApprover', 'adminApprover', 'disbursedBy', 'createdBy']);

        // Filter berdasarkan status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter pencarian nama siswa, NIS, atau nomor rekening
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', '%' . $search . '%')
                  ->orWhereHas('saver', function ($sq) use ($search) {
                      $sq->where('student_name', 'like', '%' . $search . '%')
                         ->orWhere('student_id', 'like', '%' . $search . '%')
                         ->orWhere('account_number', 'like', '%' . $search . '%');
                  });
            });
        }

        // Urutkan berdasarkan tanggal janji temu terdekat, kemudian created_at terbaru
        $requests = $query->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $countPending   = WithdrawalRequest::whereIn('status', ['pending', 'approved_teller'])->count();
        $countApproved  = WithdrawalRequest::where('status', 'approved')->count();
        $countCompleted = WithdrawalRequest::where('status', 'completed')->count();

        return view('withdrawal_requests.index', compact(
            'requests',
            'countPending',
            'countApproved',
            'countCompleted'
        ));
    }

    /**
     * Tampilkan formulir pembuatan janjian penarikan dana besar (> Rp 1.000.000)
     */
    public function create()
    {
        // Tanggal minimal penarikan adalah besok (H-1)
        $minDate = Carbon::tomorrow()->format('Y-m-d');
        
        $settingMinSaldo = SystemSetting::where('key', 'min_saldo_mengendap')->first();
        $batasMinSaldo = $settingMinSaldo ? (int)$settingMinSaldo->value : 10000;

        return view('withdrawal_requests.create', compact('minDate', 'batasMinSaldo'));
    }

    /**
     * Simpan pengajuan janjian penarikan dana besar baru
     */
    public function store(Request $request)
    {
        // 1. Bersihkan pemisah ribuan rupiah jika diinput berformat ribuan
        $rawAmount = str_replace('.', '', (string) $request->input('amount', ''));
        $request->merge(['amount' => $rawAmount]);

        $minDate = Carbon::tomorrow()->format('Y-m-d');

        $request->validate([
            'account_number' => 'required|string|exists:savers,account_number',
            'amount'         => 'required|numeric|min:1000001',
            'scheduled_date' => 'required|date|after_or_equal:' . $minDate,
            'purpose'        => 'required|string|max:255',
            'notes'          => 'nullable|string|max:500',
        ], [
            'account_number.required'     => 'Nomor rekening nasabah wajib diisi.',
            'account_number.exists'       => 'Nomor rekening tidak ditemukan di sistem.',
            'amount.required'             => 'Nominal penarikan wajib diisi.',
            'amount.numeric'              => 'Nominal penarikan harus berupa angka.',
            'amount.min'                  => 'Janjian Penarikan hanya berlaku untuk nominal di atas Rp 1.000.000. Untuk penarikan Rp 1.000.000 atau kurang dapat ditarik langsung di loket teller tanpa perlu janjian.',
            'scheduled_date.required'     => 'Tanggal janjian penarikan wajib dipilih.',
            'scheduled_date.after_or_equal'=> 'Janjian penarikan wajib dibuat minimal H-1 (tanggal penarikan minimal mulai besok, ' . Carbon::tomorrow()->translatedFormat('d M Y') . ').',
            'purpose.required'            => 'Tujuan / keperluan penarikan wajib diisi.',
        ]);

        $saver = Saver::where('account_number', $request->account_number)->firstOrFail();

        // 2. Validasi status rekening
        if ($saver->status === 'blocked') {
            return back()->withInput()->with('error', 'Pengajuan Ditolak: Rekening nasabah ini sedang DIBLOKIR oleh Administrator.');
        }

        // 3. Validasi kecukupan saldo & saldo mengendap
        $settingMinSaldo = SystemSetting::where('key', 'min_saldo_mengendap')->first();
        $batasMinSaldo = $settingMinSaldo ? (int)$settingMinSaldo->value : 10000;

        $nominalDiminta = (float) $request->amount;
        $maxBisaDitarik = max(0, (float)$saver->saldo - $batasMinSaldo);

        if (($saver->saldo - $nominalDiminta) < $batasMinSaldo) {
            return back()->withInput()->with('error', "Saldo tidak mencukupi! Saldo nasabah saat ini Rp " . number_format($saver->saldo, 0, ',', '.') . ". Dengan aturan saldo mengendap minimal Rp " . number_format($batasMinSaldo, 0, ',', '.') . ", maka nominal maksimal yang dapat ditarik adalah Rp " . number_format($maxBisaDitarik, 0, ',', '.') . ".");
        }

        // 4. Generate kode booking unik (WD-YYYYMMDD-XXXX)
        $datePrefix = date('Ymd');
        $countToday = WithdrawalRequest::whereDate('created_at', today())->count();
        $requestCode = 'WD-' . $datePrefix . '-' . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        // Tentukan initial approval sesuai role pembuat
        $userRole = Auth::user()?->role;
        $tellerId = null;
        $tellerAt = null;
        $adminId  = null;
        $adminAt  = null;
        $status   = 'pending';

        if (in_array($userRole, ['Teller'])) {
            $tellerId = Auth::id();
            $tellerAt = now();
            $status   = 'approved_teller'; // Menunggu persetujuan Admin
        } elseif (in_array($userRole, ['Administrator', 'Admin', 'Admin Utama', 'admin_utama', 'Supervisor'])) {
            $adminId  = Auth::id();
            $adminAt  = now();
            $status   = 'approved'; // Admin memiliki otorisasi penuh
        }

        $withdrawalRequest = WithdrawalRequest::create([
            'request_code'       => $requestCode,
            'saver_id'           => $saver->id,
            'amount'             => $nominalDiminta,
            'scheduled_date'     => $request->scheduled_date,
            'purpose'            => $request->purpose,
            'notes'              => $request->notes,
            'status'             => $status,
            'teller_approved_by' => $tellerId,
            'teller_approved_at' => $tellerAt,
            'admin_approved_by'  => $adminId,
            'admin_approved_at'  => $adminAt,
            'created_by'         => Auth::id(),
        ]);

        AuditLog::record(
            'BUAT_JANJIAN_PENARIKAN',
            'Janjian Penarikan',
            "Membuat Janjian Penarikan Dana Besar {$requestCode} sebesar Rp " . number_format($nominalDiminta, 0, ',', '.') . " untuk nasabah {$saver->student_name} (No. Rek: {$saver->account_number}) pada tanggal " . Carbon::parse($request->scheduled_date)->translatedFormat('d M Y'),
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Petugas'
        );

        $redirectRoute = in_array($userRole, ['Teller']) ? 'teller.withdrawal-requests.index' : 'admin.withdrawal-requests.index';

        return redirect()->route($redirectRoute)
            ->with('success', "Janjian Penarikan Dana Besar ({$requestCode}) berhasil diajukan untuk tanggal " . Carbon::parse($request->scheduled_date)->translatedFormat('d M Y') . "!");
    }

    /**
     * Persetujuan oleh Teller
     */
    public function approveTeller($id)
    {
        $withdrawal = WithdrawalRequest::with('saver')->findOrFail($id);

        if ($withdrawal->status === 'completed') {
            return back()->with('info', 'Pengajuan ini sudah selesai dicairkan.');
        }

        $newStatus = ($withdrawal->admin_approved_by !== null) ? 'approved' : 'approved_teller';

        $withdrawal->update([
            'teller_approved_by' => Auth::id(),
            'teller_approved_at' => now(),
            'status'             => $newStatus,
        ]);

        AuditLog::record(
            'APPROVE_TELLER_JANJIAN',
            'Janjian Penarikan',
            "Teller menyetujui janjian penarikan {$withdrawal->request_code} (Rp " . number_format($withdrawal->amount, 0, ',', '.') . ") nasabah {$withdrawal->saver->student_name}",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Teller'
        );

        return back()->with('success', "Persetujuan Teller berhasil dicatat untuk pengajuan {$withdrawal->request_code}.");
    }

    /**
     * Persetujuan Otorisasi Penuh oleh Administrator
     */
    public function approveAdmin($id)
    {
        $withdrawal = WithdrawalRequest::with('saver')->findOrFail($id);

        if ($withdrawal->status === 'completed') {
            return back()->with('info', 'Pengajuan ini sudah selesai dicairkan.');
        }

        $withdrawal->update([
            'admin_approved_by' => Auth::id(),
            'admin_approved_at' => now(),
            'status'            => 'approved', // Status menjadi 'approved' (siap dicairkan pada hari H)
        ]);

        AuditLog::record(
            'APPROVE_ADMIN_JANJIAN',
            'Janjian Penarikan',
            "Administrator menyetujui janjian penarikan {$withdrawal->request_code} (Rp " . number_format($withdrawal->amount, 0, ',', '.') . ") nasabah {$withdrawal->saver->student_name}",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Administrator'
        );

        return back()->with('success', "Pengajuan penarikan dana besar {$withdrawal->request_code} telah DISETUJUI oleh Administrator dan siap dicairkan pada tanggal janjian.");
    }

    /**
     * Tolak Pengajuan Janjian Penarikan
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $withdrawal = WithdrawalRequest::with('saver')->findOrFail($id);

        $withdrawal->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        AuditLog::record(
            'TOLAK_JANJIAN_PENARIKAN',
            'Janjian Penarikan',
            "Menolak janjian penarikan {$withdrawal->request_code} nasabah {$withdrawal->saver->student_name}. Alasan: {$request->rejection_reason}",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Petugas'
        );

        return back()->with('success', "Pengajuan penarikan {$withdrawal->request_code} telah ditolak.");
    }

    /**
     * Batalkan Pengajuan Janjian Penarikan
     */
    public function cancel($id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if ($withdrawal->status === 'completed') {
            return back()->with('error', 'Pengajuan yang sudah dicairkan tidak dapat dibatalkan.');
        }

        $withdrawal->update([
            'status' => 'cancelled',
        ]);

        AuditLog::record(
            'BATAL_JANJIAN_PENARIKAN',
            'Janjian Penarikan',
            "Membatalkan janjian penarikan {$withdrawal->request_code}",
            Auth::id(),
            Auth::user()?->officer_name ?? Auth::user()?->username ?? 'Petugas'
        );

        return back()->with('success', "Janjian penarikan {$withdrawal->request_code} berhasil dibatalkan.");
    }
}

