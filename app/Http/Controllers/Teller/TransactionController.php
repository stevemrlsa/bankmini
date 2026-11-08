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
                $saver = null;
                DB::transaction(function () use ($request, &$saver) {
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

                \App\Models\AuditLog::record(
                    'AKTIVASI_REKENING',
                    'Aktivasi Rekening',
                    "Aktivasi rekening baru {$saver?->student_name} (Rek: {$saver?->account_number}) dengan setoran awal Rp " . number_format($request->initial_deposit, 0, ',', '.') . " berhasil.",
                    auth()->id(),
                    auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
                );

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
            $saver = null;
            $successMessage = null;
            DB::transaction(function () use ($request, &$successMessage, &$saver) {
                // Try finding by account_number in savers
                $saver = Saver::where('account_number', $request->account_number)->lockForUpdate()->first();
                $successMessage = 'Setoran tunai berhasil. Saldo dan jurnal telah diupdate secara real-time!';

                if ($saver) {
                    $statusLower = strtolower(trim((string) ($saver->status ?? '')));
                    if ($statusLower === 'blocked' || $statusLower === 'blokir') {
                        throw new \Exception('Transaksi Ditolak: Rekening nasabah ini sedang DIBLOKIR oleh Administrator. Transaksi setoran tidak dapat diproses.');
                    }
                    if ($statusLower !== 'active' && $statusLower !== 'aktif') {
                        throw new \Exception('Transaksi Ditolak: Rekening nasabah berstatus Belum Aktif (' . ($saver->status ?: 'Inactive') . '). Silakan lakukan aktivasi rekening terlebih dahulu di menu Aktivasi Nasabah.');
                    }
                }

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
                    'date'           => now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB',
                ]);
            });

            // Record Audit Log Setoran Tunai
            \App\Models\AuditLog::record(
                'SETORAN_TUNAI',
                'Setoran Tunai',
                "Setoran tunai sebesar Rp " . number_format($request->amount, 0, ',', '.') . " pada rekening {$saver->account_number} ({$saver->student_name}) berhasil.",
                auth()->id(),
                auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage ?? 'Setoran tunai berhasil diproses dan saldo telah diperbarui.',
                    'data'    => session('receipt')
                ], 200);
            }

            return back()->with('success', $successMessage ?? 'Setoran tunai berhasil diproses dan jurnal telah tercatat.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Validasi setoran gagal.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors'  => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->with('error', $firstError)->withInput();
        } catch (\Throwable $e) {
            $friendlyError = $this->formatFriendlyError($e, 'Setoran gagal diproses. Silakan periksa kembali nomor rekening dan nominal setoran.');
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyError
                ], 422);
            }
            return back()->with('error', $friendlyError)->onlyInput('account_number', 'amount');
        }
    }

    /**
     * Format pesan error agar ramah pengguna dan tidak pernah menampilkan SQLSTATE mentah ke Teller
     */
    private function formatFriendlyError(\Throwable $e, string $defaultMessage = 'Transaksi gagal diproses. Silakan periksa kembali data input dan saldo rekening nasabah.'): string
    {
        $msg = $e->getMessage();

        if ($e instanceof \Illuminate\Database\QueryException || str_contains($msg, 'SQLSTATE')) {
            \Illuminate\Support\Facades\Log::error('Database Query Error on Teller Transaction: ' . $msg);

            if (str_contains(strtolower($msg), 'check_saldo') || str_contains($msg, '3819') || str_contains(strtolower($msg), 'saldo')) {
                return 'Penarikan ditolak! Sisa saldo minimal yang wajib mengendap di rekening adalah Rp 10.000.';
            }

            return $defaultMessage;
        }

        return $msg ?: $defaultMessage;
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

            $uangFisikDiLaci = max(0, (float) $activeShift->opening_balance + (float) $totalSetoran - (float) $totalPenarikan);
        }

        $prefilledRequest = null;
        if (request()->filled('request_id')) {
            $prefilledRequest = \App\Models\WithdrawalRequest::with('saver')->where('status', 'approved')->find(request('request_id'));
        }

        return view('teller.penarikan', compact('activeShift', 'uangFisikDiLaci', 'prefilledRequest'));
    }

    public function storePenarikan(Request $request)
    {
        // Bersihkan titik ribuan jika ada
        if ($request->has('amount')) {
            $cleanAmount = preg_replace('/[^0-9]/', '', (string) $request->input('amount'));
            $request->merge(['amount' => $cleanAmount !== '' ? (float) $cleanAmount : null]);
        }

        $request->validate([
            'account_number' => 'required|string|exists:savers,account_number',
            'amount'         => 'required|numeric|min:1000',
            'security_pin'   => 'required|digits:6', 
        ], [
            'account_number.required' => 'Nomor rekening nasabah wajib diisi.',
            'account_number.exists'   => 'Nomor rekening nasabah tidak ditemukan.',
            'amount.required'         => 'Nominal penarikan wajib diisi.',
            'amount.min'              => 'Nominal penarikan tunai minimal Rp 1.000.',
            'amount.numeric'          => 'Nominal penarikan harus berupa angka.',
            'security_pin.required'   => 'PIN keamanan nasabah wajib dimasukkan.',
            'security_pin.digits'     => 'PIN keamanan harus terdiri dari 6 digit angka.',
        ]);

        // 1. Get the Teller's active shift
        $activeShift = \App\Models\TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->first();

        if (!$activeShift) {
            $shiftError = 'Penarikan ditolak! Anda belum membuka shift / laci kasir.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $shiftError
                ], 422);
            }
            return back()->withInput()->with('error', $shiftError);
        }

        // 2. Calculate cash flow since the shift opened
        $totalSetoran = \App\Models\Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        $totalPenarikan = \App\Models\Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        // 3. Calculate actual physical cash in drawer
        $uangFisikDiLaci = max(0, (float) $activeShift->opening_balance + (float) $totalSetoran - (float) $totalPenarikan);

        // 4. Block transaction if physical cash is insufficient
        if ($uangFisikDiLaci <= 0 || (float) $request->amount > $uangFisikDiLaci) {
            $insufficientError = 'Penarikan ditolak! Saldo kas di laci tidak mencukupi (Tersedia di laci: Rp ' . number_format($uangFisikDiLaci, 0, ',', '.') . '). Silakan tambah kas laci terlebih dahulu.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $insufficientError
                ], 422);
            }
            return back()->withInput()->with('error', $insufficientError);
        }

        try {
            $saver = null;
            $withdrawalReq = null;
            DB::transaction(function () use ($request, $activeShift, &$saver, &$withdrawalReq) {
                // Validasi ulang saldo kas laci di dalam transaksi
                $setoranShift = Transaction::where('user_id', auth()->id())
                    ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
                    ->where('created_at', '>=', $activeShift->opened_at)
                    ->where('status', '!=', 'Failed')
                    ->sum('amount');

                $penarikanShift = Transaction::where('user_id', auth()->id())
                    ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
                    ->where('created_at', '>=', $activeShift->opened_at)
                    ->where('status', '!=', 'Failed')
                    ->sum('amount');

                $kasLaciTersedia = max(0, (float) $activeShift->opening_balance + (float) $setoranShift - (float) $penarikanShift);
                if ($kasLaciTersedia <= 0 || (float) $request->amount > $kasLaciTersedia) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'amount' => 'Penarikan ditolak! Saldo kas di laci tidak mencukupi (Tersedia di laci: Rp ' . number_format($kasLaciTersedia, 0, ',', '.') . '). Silakan tambah kas laci terlebih dahulu.'
                    ]);
                }

                // 1. Ambil data dengan lock dalam transaksi
                $saver = Saver::where('account_number', $request->account_number)->lockForUpdate()->firstOrFail();

                // 2. Cek status keaktifan rekening
                $statusLower = strtolower(trim((string) ($saver->status ?? '')));
                if ($statusLower === 'blocked' || $statusLower === 'blokir') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'account_number' => 'Penarikan ditolak! Rekening nasabah ini sedang diblokir oleh Administrator.'
                    ]);
                }
                if ($statusLower !== 'active' && $statusLower !== 'aktif') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'account_number' => 'Penarikan ditolak! Rekening nasabah belum aktif. Silakan lakukan aktivasi rekening terlebih dahulu.'
                    ]);
                }

                // 3. Cek PIN
                if (!Hash::check($request->security_pin, $saver->security_pin)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'security_pin' => 'PIN keamanan 6-digit yang dimasukkan salah.'
                    ]);
                }

                // 4. Cek sisa saldo mengendap minimal (wajib minimal Rp 10.000)
                $batasMinSaldo = 10000;
                try {
                    $settingMinSaldo = \App\Models\SystemSetting::where('key', 'min_saldo_mengendap')->first();
                    if ($settingMinSaldo && (int) $settingMinSaldo->value >= 10000) {
                        $batasMinSaldo = (int) $settingMinSaldo->value;
                    }
                } catch (\Throwable $e) {
                    $batasMinSaldo = 10000;
                }

                $maxBisaDitarik = max(0, (float) $saver->saldo - $batasMinSaldo);

                if (((float) $saver->saldo - (float) $request->amount) < $batasMinSaldo) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'amount' => 'Penarikan ditolak! Sisa saldo minimal yang harus mengendap di rekening adalah Rp ' . number_format($batasMinSaldo, 0, ',', '.') . '. Maksimal saldo yang dapat ditarik saat ini adalah Rp ' . number_format($maxBisaDitarik, 0, ',', '.') . '.'
                    ]);
                }

                // 5. Aturan Penarikan Tunai: Reguler (<= Rp 1.000.000/hari) vs Janjian Dana Besar (> Rp 1.000.000)
                $nominalTarik = (float) $request->amount;

                if ($nominalTarik > 1000000) {
                    // Penarikan di atas Rp 1.000.000: wajib ada pengajuan janjian yang sudah disetujui
                    try {
                        if ($request->filled('withdrawal_request_id')) {
                            $withdrawalReq = \App\Models\WithdrawalRequest::where('id', $request->withdrawal_request_id)
                                ->where('saver_id', $saver->id)
                                ->where('status', 'approved')
                                ->first();
                        }

                        if (!$withdrawalReq) {
                            $withdrawalReq = \App\Models\WithdrawalRequest::where('saver_id', $saver->id)
                                ->where('status', 'approved')
                                ->where('amount', '>=', $nominalTarik)
                                ->latest()
                                ->first();
                        }
                    } catch (\Throwable $e) {
                        $withdrawalReq = null;
                    }

                    if (!$withdrawalReq) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'amount' => 'Penarikan ditolak! Penarikan di atas Rp 1.000.000 wajib membuat Janjian Penarikan Dana Besar minimal H-1 dan telah disetujui oleh Teller serta Administrator.'
                        ]);
                    }
                } else {
                    // Penarikan reguler (<= Rp 1.000.000): Batas maksimal akumulasi reguler Rp 1.000.000 per nasabah per hari
                    $maxRegulerPerHari = 1000000;

                    $disbursedRequestTxIds = [];
                    try {
                        $disbursedRequestTxIds = \App\Models\WithdrawalRequest::where('saver_id', $saver->id)
                            ->whereNotNull('transaction_id')
                            ->pluck('transaction_id')
                            ->toArray();
                    } catch (\Throwable $e) {
                        $disbursedRequestTxIds = [];
                    }

                    $todayRegularQuery = Transaction::where('saver_id', $saver->id)
                        ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
                        ->whereDate('created_at', today())
                        ->where('status', '!=', 'Failed')
                        ->where('amount', '<=', $maxRegulerPerHari);

                    if (!empty($disbursedRequestTxIds)) {
                        $todayRegularQuery->whereNotIn('id', $disbursedRequestTxIds);
                    }

                    $todayWithdrawn = (float) $todayRegularQuery->sum('amount');

                    if (($todayWithdrawn + $nominalTarik) > $maxRegulerPerHari) {
                        $sisaKuota = max(0, $maxRegulerPerHari - $todayWithdrawn);
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'amount' => 'Penarikan ditolak! Batas penarikan reguler adalah Rp 1.000.000 per hari. Sisa kuota penarikan harian nasabah hari ini adalah Rp ' . number_format($sisaKuota, 0, ',', '.') . '.'
                        ]);
                    }
                }

                // 6. Simpan Transaksi Penarikan (hanya kolom yang ada pada tabel transactions)
                $transaction = Transaction::create([
                    'saver_id'         => $saver->id,
                    'user_id'          => auth()->id(),
                    'transaction_date' => now(),
                    'transaction_type' => 'Withdrawal',
                    'amount'           => $nominalTarik,
                    'status'           => 'Approved',
                ]);

                // 7. Jika menggunakan Janjian Penarikan Dana Besar, tandai sebagai Selesai / Completed
                if ($withdrawalReq) {
                    $withdrawalReq->update([
                        'status'         => 'completed',
                        'transaction_id' => $transaction->id,
                        'disbursed_by'   => auth()->id(),
                        'disbursed_at'   => now(),
                    ]);
                }

                // 8. Potong saldo nasabah (Seketika)
                $saver->saldo -= $nominalTarik;
                $saver->save();

                // 9. Jurnal Double-Entry (Seketika)
                DB::table('accounting_journals')->insert([
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '201', // Debit Tabungan
                        'position'       => 'Debit',
                        'amount'         => $nominalTarik,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ],
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '101', // Kredit Kas
                        'position'       => 'Credit',
                        'amount'         => $nominalTarik,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                ]);

                // Flash receipt data for immediate printing
                session()->flash('receipt', [
                    'id'             => $transaction->id,
                    'type'           => $withdrawalReq ? 'Penarikan Dana Besar (Janjian H-1)' : 'Penarikan Tunai (Withdrawal)',
                    'account_number' => $saver->account_number,
                    'student_name'   => $saver->student_name,
                    'amount'         => $nominalTarik,
                    'current_balance'=> $saver->saldo,
                    'teller_name'    => auth()->user()->officer_name ?? auth()->user()->username,
                    'date'           => now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB',
                ]);
            });

            // Record Audit Log Penarikan Tunai
            $auditDesc = $withdrawalReq 
                ? "Pencairan Janjian Penarikan Dana Besar ({$withdrawalReq->request_code}) sebesar Rp " . number_format($request->amount, 0, ',', '.') . " pada rekening {$saver->account_number} ({$saver->student_name}) berhasil."
                : "Penarikan tunai reguler sebesar Rp " . number_format($request->amount, 0, ',', '.') . " pada rekening {$saver->account_number} ({$saver->student_name}) berhasil.";

            \App\Models\AuditLog::record(
                'PENARIKAN_TUNAI',
                'Penarikan Tunai',
                $auditDesc,
                auth()->id(),
                auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penarikan tunai berhasil diproses. Saldo nasabah telah dipotong.',
                    'data'    => session('receipt')
                ], 200);
            }

            return back()->with('success', 'Penarikan tunai berhasil diproses. Saldo nasabah dipotong dan jurnal tercatat.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Penarikan ditolak karena tidak memenuhi syarat transaksi.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors'  => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->with('error', $firstError)->withInput();
        } catch (\Throwable $e) {
            $friendlyError = $this->formatFriendlyError($e);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyError
                ], 422);
            }
            return back()->with('error', $friendlyError)->withInput();
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

        \App\Models\AuditLog::record(
            'RESET_PIN_NASABAH',
            'Keamanan Nasabah',
            "Mereset PIN keamanan nasabah {$saver->student_name} (Rek: {$saver->account_number}) ke default 123456.",
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
        );

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
            $statusLower = strtolower(trim((string) ($saver->status ?? '')));
            $isBlocked = ($statusLower === 'blocked' || $statusLower === 'blokir');
            $isInactive = ($statusLower !== 'active' && $statusLower !== 'aktif');
            $statusMessage = 'Data nasabah berhasil ditemukan';
            if ($isBlocked) {
                $statusMessage = 'PERINGATAN: Rekening nasabah ini sedang DIBLOKIR oleh Administrator!';
            } elseif ($isInactive) {
                $statusMessage = 'PERINGATAN: Rekening nasabah ini berstatus Belum Aktif (' . ($saver->status ?: 'Inactive') . '). Transaksi hanya dapat diproses setelah aktivasi di menu Aktivasi Nasabah.';
            }

            $batasMinSaldo = 10000;
            try {
                $settingMinSaldo = \App\Models\SystemSetting::where('key', 'min_saldo_mengendap')->first();
                if ($settingMinSaldo && (int) $settingMinSaldo->value >= 10000) {
                    $batasMinSaldo = (int) $settingMinSaldo->value;
                }
            } catch (\Throwable $e) {
                $batasMinSaldo = 10000;
            }
            $maxBisaDitarik = max(0, (float)$saver->saldo - $batasMinSaldo);

            $disbursedRequestTxIds = [];
            try {
                $disbursedRequestTxIds = \App\Models\WithdrawalRequest::where('saver_id', $saver->id)
                    ->whereNotNull('transaction_id')
                    ->pluck('transaction_id')
                    ->toArray();
            } catch (\Throwable $e) {
                $disbursedRequestTxIds = [];
            }

            $todayRegularQuery = Transaction::where('saver_id', $saver->id)
                ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
                ->whereDate('created_at', today())
                ->where('status', '!=', 'Failed')
                ->where('amount', '<=', 1000000);

            if (!empty($disbursedRequestTxIds)) {
                $todayRegularQuery->whereNotIn('id', $disbursedRequestTxIds);
            }

            $todayWithdrawn = (float) $todayRegularQuery->sum('amount');
            $remainingDailyQuota = max(0, 1000000 - $todayWithdrawn);

            $approvedRequest = null;
            if ($request->filled('request_id')) {
                $approvedRequest = \App\Models\WithdrawalRequest::where('id', $request->request_id)
                    ->where('saver_id', $saver->id)
                    ->where('status', 'approved')
                    ->first();
            }
            if (!$approvedRequest) {
                $approvedRequest = \App\Models\WithdrawalRequest::where('saver_id', $saver->id)
                    ->where('status', 'approved')
                    ->latest()
                    ->first();
            }

            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'data'    => [
                    'id'                     => $saver->id,
                    'account_number'         => $saver->account_number,
                    'student_id'             => $saver->student_id,
                    'nis'                    => $saver->student_id,
                    'student_name'           => $saver->student_name,
                    'name'                   => $saver->student_name,
                    'grade_class'            => $saver->grade_class ?? '-',
                    'saldo'                  => (float) $saver->saldo,
                    'balance'                => (float) $saver->saldo,
                    'current_balance'        => (float) $saver->saldo,
                    'formatted_saldo'        => 'Rp ' . number_format($saver->saldo, 0, ',', '.'),
                    'is_member_only'         => false,
                    'status'                 => $saver->status ?? 'active',
                    'is_blocked'             => $isBlocked,
                    'is_inactive'            => $isInactive,
                    'min_retained_balance'   => $batasMinSaldo,
                    'max_withdrawable'       => $maxBisaDitarik,
                    'formatted_max_withdraw' => 'Rp ' . number_format($maxBisaDitarik, 0, ',', '.'),
                    'today_withdrawn'        => $todayWithdrawn,
                    'remaining_daily_quota'  => $remainingDailyQuota,
                    'approved_request'       => $approvedRequest ? [
                        'id'             => $approvedRequest->id,
                        'request_code'   => $approvedRequest->request_code,
                        'amount'         => (float) $approvedRequest->amount,
                        'formatted_amount'=> 'Rp ' . number_format($approvedRequest->amount, 0, ',', '.'),
                        'purpose'        => $approvedRequest->purpose,
                        'scheduled_date' => \Carbon\Carbon::parse($approvedRequest->scheduled_date)->format('d/m/Y'),
                    ] : null,
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