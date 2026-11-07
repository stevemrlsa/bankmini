<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\AccountingJournal;
use App\Models\TellerShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; 

class TransactionController extends Controller
{
    public function dashboard()
    {
        $tellerId = auth()->id();
        $today = now()->startOfDay();

        $activeShift = TellerShift::where('user_id', $tellerId)
            ->where('status', 'Open')
            ->first();

        $shiftStart = $activeShift ? $activeShift->opened_at : $today;
        $saldoAwal = $activeShift ? (float) $activeShift->opening_balance : 0;

        // Total setoran oleh teller ini sejak shift dibuka / hari ini
        $totalSetoran = Transaction::where('user_id', $tellerId)
            ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $shiftStart)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        // Total penarikan oleh teller ini sejak shift dibuka / hari ini
        $totalPenarikan = Transaction::where('user_id', $tellerId)
            ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $shiftStart)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        // Jumlah transaksi oleh teller ini hari ini
        $jumlahTransaksi = Transaction::where('user_id', $tellerId)
            ->whereDate('created_at', '>=', $today)
            ->count();

        // Saldo kas fisik di laci = Modal Kas Laci (Opening + TopUp) + Setoran - Penarikan
        $saldoKas = $saldoAwal + $totalSetoran - $totalPenarikan;

        // Transaksi terbaru oleh teller ini
        $latestTransactions = Transaction::with('saver')
            ->where('user_id', $tellerId)
            ->latest()
            ->take(5)
            ->get();

        return view('teller.dashboard', compact(
            'activeShift',
            'saldoAwal',
            'totalSetoran',
            'totalPenarikan',
            'jumlahTransaksi',
            'saldoKas',
            'latestTransactions'
        ));
    }

    public function createSetoran()
    {
        return view('teller.setoran');
    }

    public function store(Request $request)
    {
        return $this->storeSetoran($request);
    }

    public function storeSetoran(Request $request)
    {
        // 1. Validasi Input Pendaftaran Rekening Baru (Bila membawa student_id atau initial_deposit)
        if ($request->has('student_id') || $request->has('initial_deposit')) {
            $request->validate([
                'student_id'      => 'required|string|max:20|unique:savers,student_id',
                'student_name'    => 'required|string|max:100',
                'grade_class'     => 'required|string|max:50',
                'initial_deposit' => 'required|numeric|min:10000',
                'security_pin'    => 'required|digits:6',
            ], [
                'initial_deposit.min' => 'Setoran awal pembukaan rekening minimal Rp 10.000.',
                'student_id.unique'   => 'NIS siswa ini sudah terdaftar sebagai nasabah.',
            ]);

            try {
                DB::transaction(function () use ($request) {
                    // 2. Generate No Rekening otomatis (Tahun + 4 digit urutan)
                    $accountNumber = date('Y') . str_pad(Saver::count() + 1, 4, '0', STR_PAD_LEFT);

                    // 3. Buat Data Saver / Nasabah Baru
                    $saver = Saver::create([
                        'account_number' => $accountNumber,
                        'student_id'     => $request->student_id,
                        'student_name'   => $request->student_name,
                        'grade_class'    => $request->grade_class,
                        'security_pin'   => Hash::make($request->security_pin),
                        'saldo'          => $request->initial_deposit,
                        'status'         => 'active',
                    ]);

                    // 4. Catat Transaksi Setoran Awal
                    $transaction = Transaction::create([
                        'saver_id'         => $saver->id,
                        'user_id'          => auth()->id(),
                        'transaction_date' => now(),
                        'transaction_type' => 'Deposit',
                        'amount'           => $request->initial_deposit,
                        'status'           => 'Approved',
                        'notes'            => 'Setoran awal pembukaan rekening baru.',
                    ]);

                    // 5. Catat Jurnal Akuntansi Double-Entry (Kas Debit, Tabungan Siswa Kredit)
                    AccountingJournal::insert([
                        [
                            'transaction_id' => $transaction->id,
                            'account_code'   => '101', // Kode Akun Kas Teller
                            'position'       => 'Debit',
                            'amount'         => $request->initial_deposit,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ],
                        [
                            'transaction_id' => $transaction->id,
                            'account_code'   => '201', // Kode Akun Tabungan Siswa
                            'position'       => 'Credit',
                            'amount'         => $request->initial_deposit,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]
                    ]);
                });

                return redirect()->back()->with('success', 'Rekening berhasil diaktivasi dan setoran awal berhasil dicatat!');

            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
            }
        }

        $request->validate([
            'account_number' => 'required|string',
            'amount'         => 'required|integer|min:1000',
        ]);

        try {
            DB::transaction(function () use ($request, &$successMessage) {
                // Try finding by account_number in savers
                $saver = Saver::where('account_number', $request->account_number)->lockForUpdate()->first();
                $successMessage = 'Setoran tunai berhasil. Saldo dan jurnal telah diupdate secara real-time!';

                if (!$saver) {
                    // Try finding by student_id in members
                    $member = \App\Models\Member::where('student_id', $request->account_number)->first();
                    
                    if (!$member) {
                        throw new \Exception('Nomor rekening atau NIS siswa tidak ditemukan di sistem.');
                    }
                    
                    if ($member->status === 'inactive') {
                        throw new \Exception('Rekening siswa berstatus Belum Aktif (Inactive). Silakan hubungi Administrator untuk aktivasi dan pembuatan PIN terlebih dahulu.');
                    }
                    
                    if ($request->amount < 10000) {
                        throw new \Exception('Setoran awal untuk aktivasi buku tabungan minimal Rp 10.000.');
                    }
                    
                    // Generate account number
                    $schoolyear = date('Y');
                    $lastSaver = Saver::where('account_number', 'like', $schoolyear . '%')->orderBy('account_number', 'desc')->first();
                    $nexPlace = $lastSaver ? (int) substr($lastSaver->account_number, -4) + 1 : 1;
                    $accountNumber = $schoolyear . str_pad($nexPlace, 4, '0', STR_PAD_LEFT);
                    
                    // Migrate / clone Member into Saver
                    $saver = Saver::create([
                        'account_number' => $accountNumber,
                        'student_id'     => $member->student_id,
                        'student_name'   => $member->name,
                        'grade_class'    => $member->graduation_year ? ('Angkatan ' . $member->graduation_year) : '-',
                        'security_pin'   => $member->security_pin ?? Hash::make('123456'),
                        'saldo'          => 0,
                    ]);

                    $successMessage = 'Aktivasi rekening dan migrasi ke Penabung berhasil! Nomor Rekening: ' . $accountNumber . '. Setoran awal telah dibukukan.';
                }

                // 1. Simpan Transaksi (Langsung aktif, tidak ada 'Pending')
                $transaction = Transaction::create([
                    'saver_id'         => $saver->id,
                    'user_id'          => auth()->id(), 
                    'transaction_date' => now(),
                    'transaction_type' => 'Deposit',
                    'amount'           => $request->amount,
                    'status'           => 'Approved',
                ]);
                // 2. Update saldo nasabah (Seketika)
                $saver->saldo += $request->amount;
                $saver->save();

                // 3. Jurnal Double-Entry (Seketika: Debit Kas 101, Kredit Tabungan 201)
                DB::table('accounting_journals')->insert([
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '101', // Debit Kas
                        'position'       => 'Debit',
                        'amount'         => $request->amount,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ],
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '201', // Kredit Tabungan
                        'position'       => 'Credit',
                        'amount'         => $request->amount,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                ]);

                // Flash receipt data for immediate printing
                session()->flash('receipt', [
                    'id'             => $transaction->id,
                    'type'           => 'Setoran Tunai (Deposit)',
                    'account_number' => $saver->account_number,
                    'student_name'   => $saver->student_name,
                    'amount'         => $request->amount,
                    'current_balance'=> $saver->saldo,
                    'teller_name'    => auth()->user()->officer_name ?? auth()->user()->username,
                    'date'           => now()->format('d/m/Y H:i:s'),
                ]);
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage ?? 'Setoran tunai berhasil diproses dan saldo telah diperbarui.',
                    'data'    => session('receipt')
                ], 200);
            }

            return back()->with('success', $successMessage ?? 'Setoran tunai berhasil diproses dan jurnal telah tercatat.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                $firstError = collect($e->errors())->flatten()->first() ?? 'Validasi setoran gagal.';
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors'  => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->with('error', $e->getMessage())->onlyInput('account_number', 'amount');
        }
    }

    public function createPenarikan()
    {
        $activeShift = TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->first();

        $uangFisikDiLaci = 0;
        if ($activeShift) {
            $totalSetoran = Transaction::where('user_id', auth()->id())
                ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
                ->where('created_at', '>=', $activeShift->opened_at)
                ->where('status', '!=', 'Failed')
                ->sum('amount');

            $totalPenarikan = Transaction::where('user_id', auth()->id())
                ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
                ->where('created_at', '>=', $activeShift->opened_at)
                ->where('status', '!=', 'Failed')
                ->sum('amount');

            $uangFisikDiLaci = (float) $activeShift->opening_balance + $totalSetoran - $totalPenarikan;
        }

        return view('teller.penarikan', compact('activeShift', 'uangFisikDiLaci'));
    }

    public function storePenarikan(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|exists:savers,account_number',
            'amount'         => 'required|integer|min:1000',
            'security_pin'   => 'required|digits:6', 
        ]);

        // 1. Get the Teller's active shift
        $activeShift = \App\Models\TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->first();

        if (!$activeShift) {
            $shiftError = 'Akses Ditolak: Anda belum membuka laci kasir (Shift).';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $shiftError
                ], 422);
            }
            return back()->with('error', $shiftError);
        }

        // 2. Calculate cash flow since the shift opened
        $totalSetoran = \App\Models\Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->sum('amount');

        $totalPenarikan = \App\Models\Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->sum('amount');

        // 3. Calculate actual physical cash
        $uangFisikDiLaci = $activeShift->opening_balance + $totalSetoran - $totalPenarikan;

        // 4. Block transaction if physical cash is insufficient
        if ($request->amount > $uangFisikDiLaci) {
            $insufficientError = 'Transaksi Ditolak: Uang tunai fisik di laci tidak mencukupi untuk penarikan ini. Sisa kas Anda: Rp ' . number_format($uangFisikDiLaci, 0, ',', '.');
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $insufficientError
                ], 422);
            }
            return back()->with('error', $insufficientError);
        }

        try {
            DB::transaction(function () use ($request) {
                // 1. Ambil data dengan lock dalam transaksi
                $saver = Saver::where('account_number', $request->account_number)->lockForUpdate()->firstOrFail();

                // 2. Cek PIN
                if (!Hash::check($request->security_pin, $saver->security_pin)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'security_pin' => 'PIN keamanan yang dimasukkan salah.'
                    ]);
                }

                // 3. Cek sisa saldo
                $settingMinSaldo = \App\Models\SystemSetting::where('key', 'min_saldo_mengendap')->first();
                $batasMinSaldo = $settingMinSaldo ? (int)$settingMinSaldo->value : 10000;

                if (($saver->saldo - $request->amount) < $batasMinSaldo) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'amount' => 'Penarikan ditolak! Sisa saldo setelah penarikan minimal Rp ' . number_format($batasMinSaldo,0,',','.')
                    ]);
                }

                // 4. Simpan Transaksi 
                $transaction = Transaction::create([
                    'saver_id'      => $saver->id,
                    'user_id'          => auth()->id(),
                    'transaction_date' => now(),
                    'transaction_type' => 'Withdrawal',
                    'amount'           => $request->amount,
                    'status'           => 'Approved',
                ]);

                // 5. Potong saldo nasabah (Seketika)
                $saver->saldo -= $request->amount;
                $saver->save();

                // 6. Jurnal Double-Entry (Seketika)
                DB::table('accounting_journals')->insert([
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '201', // Debit Tabungan
                        'position'       => 'Debit',
                        'amount'         => $request->amount,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ],
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '101', // Kredit Kas
                        'position'       => 'Credit',
                        'amount'         => $request->amount,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                ]);

                // Flash receipt data for immediate printing
                session()->flash('receipt', [
                    'id'             => $transaction->id,
                    'type'           => 'Penarikan Tunai (Withdrawal)',
                    'account_number' => $saver->account_number,
                    'student_name'   => $saver->student_name,
                    'amount'         => $request->amount,
                    'current_balance'=> $saver->saldo,
                    'teller_name'    => auth()->user()->officer_name ?? auth()->user()->username,
                    'date'           => now()->format('d/m/Y H:i:s'),
                ]);
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penarikan tunai berhasil diproses. Saldo nasabah telah dipotong.',
                    'data'    => session('receipt')
                ], 200);
            }

            return back()->with('success', 'Penarikan tunai berhasil diproses. Saldo nasabah dipotong dan jurnal tercatat.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                $firstError = collect($e->errors())->flatten()->first() ?? 'Validasi penarikan gagal.';
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors'  => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Teller Reset PIN untuk nasabah yang lupa PIN
     */
    public function resetPin(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|exists:savers,account_number',
        ]);

        $saver = Saver::where('account_number', $request->account_number)->firstOrFail();
        $saver->update([
            'security_pin' => Hash::make('123456')
        ]);

        return back()->with('success', 'PIN untuk nasabah ' . $saver->student_name . ' (' . $saver->account_number . ') berhasil direset menjadi 123456.');
    }

    /**
     * Search Saver or Member for QR Scan & Auto-Fill AJAX
     */
    public function searchSaver(Request $request)
    {
        $query = trim($request->input('q') ?? $request->input('account_number') ?? $request->input('student_id') ?? $request->input('nis') ?? '');

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter pencarian tidak boleh kosong.',
                'data'    => null
            ], 422);
        }

        // 1. Cari di tabel savers berdasarkan Nomor Rekening atau NIS (student_id)
        $saver = Saver::where('account_number', $query)
            ->orWhere('student_id', $query)
            ->first();

        if ($saver) {
            return response()->json([
                'success' => true,
                'message' => 'Data nasabah berhasil ditemukan',
                'data'    => [
                    'id'              => $saver->id,
                    'account_number'  => $saver->account_number,
                    'student_id'      => $saver->student_id,
                    'nis'             => $saver->student_id,
                    'student_name'    => $saver->student_name,
                    'name'            => $saver->student_name,
                    'grade_class'     => $saver->grade_class ?? '-',
                    'saldo'           => (float) $saver->saldo,
                    'balance'         => (float) $saver->saldo,
                    'current_balance' => (float) $saver->saldo,
                    'formatted_saldo' => 'Rp ' . number_format($saver->saldo, 0, ',', '.'),
                    'is_member_only'  => false,
                    'status'          => 'active'
                ]
            ], 200);
        }

        // 2. Jika tidak ada di savers, cek di tabel members (Calon nasabah / Aktivasi setoran awal)
        $member = \App\Models\Member::where('student_id', $query)->first();
        if ($member) {
            return response()->json([
                'success' => true,
                'message' => 'Data calon nasabah (Anggota) ditemukan. Silakan lakukan Setoran Awal untuk aktivasi buku tabungan.',
                'data'    => [
                    'id'              => null,
                    'account_number'  => $member->student_id,
                    'student_id'      => $member->student_id,
                    'nis'             => $member->student_id,
                    'student_name'    => $member->name,
                    'name'            => $member->name,
                    'grade_class'     => $member->graduation_year ? ('Angkatan ' . $member->graduation_year) : '-',
                    'saldo'           => 0,
                    'balance'         => 0,
                    'current_balance' => 0,
                    'formatted_saldo' => 'Rp 0 (Rekening Baru)',
                    'is_member_only'  => true,
                    'status'          => $member->status
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data nasabah dengan Nomor Rekening / NIS "' . $query . '" tidak ditemukan.',
            'data'    => null
        ], 404);
    }
}