<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\AccountingJournal;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminCustomerController extends Controller
{
    /**
     * Display a listing of active students (customers).
     * Filtered by status 'active', default sorted by NIS ascending.
     */
    public function index(Request $request)
    {
        // 0. Ensure existing savers with account numbers default to 'active' if status was null
        if (Schema::hasColumn('savers', 'status')) {
            Saver::whereNull('status')->whereNotNull('account_number')->update(['status' => 'active']);
            $query = Saver::whereIn('status', ['active', 'blocked'])
                ->where(function ($q) {
                    $q->whereNull('grade_class')
                      ->orWhere('grade_class', 'not like', 'Lulus%');
                });

            // Filter status rekening (Semua, Aktif, Diblokir)
            if ($request->filled('status') && in_array($request->status, ['active', 'blocked'])) {
                $query->where('status', $request->status);
            }
        } else {
            $query = Saver::query();
        }

        // 2. Search by Student Name or NIS (student_id) - Mengabaikan filter kelas saat mencari
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('student_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('student_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('account_number', 'like', '%' . $searchTerm . '%');
            });
        } elseif ($request->filled('class')) {
            // 3. Filter by Class (grade_class) - hanya aktif jika tidak sedang mencari nama/NIS
            $query->where('grade_class', $request->class);
        }

        // 4. Default sort by NIS (student_id) ascending
        $savers = $query->orderBy('student_id', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Distinct classes for filter dropdown
        $classesQuery = Saver::whereNotNull('grade_class')
            ->where('grade_class', 'not like', 'Lulus%');
        if (Schema::hasColumn('savers', 'status')) {
            $classesQuery->where('status', 'active');
        }
        $classes = $classesQuery->distinct()
            ->orderBy('grade_class', 'asc')
            ->pluck('grade_class');

        return view('admin.savers.index', compact('savers', 'classes'));
    }

    /**
     * Halaman khusus untuk menampilkan Data Siswa yang Sudah Lulus (Kelas 3 / XII Alumni)
     * Lengkap dengan saldo tersimpan, riwayat transaksi, dan fitur Aktifkan Kembali.
     */
    public function alumniIndex(Request $request)
    {
        $baseQuery = Saver::where(function ($q) {
            $q->where('grade_class', 'like', 'Lulus%');
            if (Schema::hasColumn('savers', 'status')) {
                $q->orWhere('status', 'graduated');
            }
        });

        $totalAlumniCount = (clone $baseQuery)->count();
        $totalAlumniSaldo = (clone $baseQuery)->sum('saldo');

        $classes = (clone $baseQuery)
            ->whereNotNull('grade_class')
            ->where('grade_class', '!=', '')
            ->distinct()
            ->pluck('grade_class')
            ->sort(function ($a, $b) {
                return strnatcasecmp($a, $b);
            })
            ->values();

        $query = clone $baseQuery;
        $currentClass = null;
        $prevClass = null;
        $nextClass = null;
        $currentClassIndex = 0;
        $totalClasses = $classes->count();

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('student_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('student_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('account_number', 'like', '%' . $searchTerm . '%');
            });
            // Pencarian di seluruh kelas alumni, mengabaikan filter kelas
            $currentClass = null;
            $prevClass = null;
            $nextClass = null;
            $currentClassIndex = 0;
        } else {
            if ($classes->isNotEmpty()) {
                $requestedClass = $request->query('class');
                if ($requestedClass && $classes->contains($requestedClass)) {
                    $currentClass = $requestedClass;
                } else {
                    $currentClass = $classes->first();
                }

                $idx = $classes->search($currentClass);
                if ($idx !== false) {
                    $currentClassIndex = $idx + 1;
                    $prevClass = $idx > 0 ? $classes->get($idx - 1) : null;
                    $nextClass = $idx < ($totalClasses - 1) ? $classes->get($idx + 1) : null;
                }

                $query->where('grade_class', $currentClass);
            }
        }

        $alumni = $query->orderBy('student_id', 'asc')->get();

        return view('admin.savers.alumni', compact(
            'alumni',
            'classes',
            'currentClass',
            'prevClass',
            'nextClass',
            'currentClassIndex',
            'totalClasses',
            'totalAlumniCount',
            'totalAlumniSaldo'
        ));
    }

    /**
     * Aktifkan kembali akun siswa yang sudah lulus / nonaktif beserta seluruh saldo dan riwayat transaksinya.
     */
    public function reactivate(Request $request, $id)
    {
        $saver = Saver::findOrFail($id);

        $newClass = trim((string) $request->input('grade_class', ''));
        if ($newClass === '') {
            // Jika tidak diisi kelas baru, hilangkan awalan 'Lulus YYYY ' atau set ke XII
            $cleaned = preg_replace('/^Lulus\s+\d{4}\s*/i', 'XII ', (string) $saver->grade_class);
            $newClass = trim($cleaned) ?: 'XII';
        }

        $updateData = [
            'grade_class' => $newClass,
        ];
        if (Schema::hasColumn('savers', 'status')) {
            $updateData['status'] = 'active';
        }
        if (empty($saver->account_number)) {
            $schoolYear = date('Y');
            $lastSaver = Saver::where('account_number', 'like', $schoolYear . '%')
                ->orderBy('account_number', 'desc')
                ->first();
            $nextSequence = $lastSaver ? ((int) substr($lastSaver->account_number, -4) + 1) : 1;
            $updateData['account_number'] = $schoolYear . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        }

        $saver->update($updateData);

        if (class_exists(Member::class)) {
            Member::where('student_id', $saver->student_id)->update([
                'status'      => 'active',
                'grade_class' => $newClass,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Berhasil! Rekening nasabah ' . $saver->student_name . ' telah diaktifkan kembali ke kelas ' . $newClass . ' beserta saldo Rp ' . number_format($saver->saldo ?? 0, 0, ',', '.') . ' dan seluruh riwayat transaksinya.');
    }

    /**
     * Hitung kenaikan tingkat kelas siswa:
     * - X (10) -> XI (11)
     * - XI (11) -> XII (12)
     * - XII (12) -> Lulus YYYY
     * - Angka 1-5 -> +1 (Kelas 2 -> Kelas 3, Kelas 4 -> Kelas 5, dst)
     * - Angka 6 -> Lulus YYYY
     */
    protected function calculatePromotedGrade(?string $grade): array
    {
        $grade = trim($grade ?? '');
        if ($grade === '' || str_starts_with(strtolower($grade), 'lulus')) {
            return ['new_grade' => $grade, 'graduated' => false];
        }

        $currentYear = date('Y');

        // 1. Jenjang XII atau 12 (Kelulusan)
        if (preg_match('/^(.*?\b)(XII|12)(\b.*)$/i', $grade, $m)) {
            $suffix = trim($m[3]);
            $newGrade = 'Lulus ' . $currentYear . ($suffix !== '' ? ' ' . $suffix : '');
            return ['new_grade' => trim($newGrade), 'graduated' => true];
        }

        // 2. Jenjang XI atau 11 -> Naik ke XII / 12
        if (preg_match('/^(.*?\b)(XI|11)(\b.*)$/i', $grade, $m)) {
            $prefix = $m[1];
            $target = (strtoupper($m[2]) === 'XI') ? 'XII' : '12';
            $suffix = $m[3];
            return ['new_grade' => $prefix . $target . $suffix, 'graduated' => false];
        }

        // 3. Jenjang X atau 10 -> Naik ke XI / 11
        if (preg_match('/^(.*?\b)(X|10)(\b.*)$/i', $grade, $m)) {
            $prefix = $m[1];
            $target = (strtoupper($m[2]) === 'X') ? 'XI' : '11';
            $suffix = $m[3];
            return ['new_grade' => $prefix . $target . $suffix, 'graduated' => false];
        }

        // 4. Pola kelas angka tunggal 1 s.d. 5 (misal 'Kelas 1', 'Kelas 2', 'Kelas 4')
        if (preg_match('/^(.*?\b)([1-5])(\b.*)$/i', $grade, $m)) {
            $prefix = $m[1];
            $target = (int)$m[2] + 1;
            $suffix = $m[3];
            return ['new_grade' => $prefix . $target . $suffix, 'graduated' => false];
        }

        // 5. Pola kelas 6 -> Kelulusan
        if (preg_match('/^(.*?\b)(6)(\b.*)$/i', $grade, $m)) {
            $suffix = trim($m[3]);
            $newGrade = 'Lulus ' . $currentYear . ($suffix !== '' ? ' ' . $suffix : '');
            return ['new_grade' => trim($newGrade), 'graduated' => true];
        }

        return ['new_grade' => $grade, 'graduated' => false];
    }

    /**
     * Kenaikan kelas massal untuk SELURUH siswa (baik nasabah aktif maupun nonaktif, serta data anggota).
     */
    public function bulkPromote(Request $request)
    {
        $promotedCount = 0;
        $graduatedCount = 0;

        DB::transaction(function () use (&$promotedCount, &$graduatedCount) {
            // 1. Ambil SELURUH siswa di tabel savers (baik aktif maupun nonaktif)
            $allSavers = Saver::all();
            foreach ($allSavers as $student) {
                $calc = $this->calculatePromotedGrade($student->grade_class);
                if ($calc['new_grade'] !== $student->grade_class) {
                    $updateData = ['grade_class' => $calc['new_grade']];
                    if ($calc['graduated']) {
                        $graduatedCount++;
                        if (Schema::hasColumn('savers', 'status')) {
                            $updateData['status'] = 'inactive';
                        }
                    } else {
                        $promotedCount++;
                    }
                    $student->update($updateData);
                }
            }

            // 2. Ambil SELURUH data anggota di tabel members (baik aktif maupun nonaktif) agar data sekolah selalu sinkron
            if (class_exists(Member::class)) {
                $allMembers = Member::all();
                foreach ($allMembers as $member) {
                    $calc = $this->calculatePromotedGrade($member->grade_class);
                    if ($calc['new_grade'] !== $member->grade_class) {
                        $updateData = ['grade_class' => $calc['new_grade']];
                        if ($calc['graduated']) {
                            if (Schema::hasColumn('members', 'status')) {
                                $updateData['status'] = 'inactive';
                            }
                            if (Schema::hasColumn('members', 'graduation_year')) {
                                $updateData['graduation_year'] = date('Y');
                            }
                        }
                        $member->update($updateData);
                    }
                }
            }
        });

        // Simpan waktu dan tahun pelaksanaan kenaikan kelas ke SystemSetting
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'last_class_promotion_year'],
            ['value' => date('Y'), 'description' => 'Tahun terakhir eksekusi kenaikan kelas massal']
        );
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'last_class_promotion_at'],
            ['value' => now()->toDateTimeString(), 'description' => 'Waktu terakhir eksekusi kenaikan kelas massal']
        );

        \App\Models\AuditLog::record(
            'NAIK_KELAS_MASSAL',
            'Kenaikan Kelas',
            "Memproses kenaikan kelas massal untuk {$promotedCount} siswa dan {$graduatedCount} siswa kelulusan/alumni (Tahun Ajaran " . date('Y') . ").",
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Administrator'
        );

        $message = "Kenaikan kelas massal berhasil diproses untuk seluruh siswa (aktif maupun nonaktif). {$promotedCount} data siswa berhasil naik kelas";
        if ($graduatedCount > 0) {
            $message .= ", dan {$graduatedCount} siswa kelas akhir dialihkan ke status kelulusan (alumni).";
        } else {
            $message .= ".";
        }

        return redirect()->route('admin.savers.index')
            ->with('success', $message);
    }

    /**
     * Blokir rekening tabungan siswa (mencegah penarikan dan setoran).
     */
    public function blockAccount(Request $request, $id)
    {
        $saver = Saver::findOrFail($id);
        $saver->update(['status' => 'blocked']);

        // Sinkronkan status akun user siswa jika ada
        if (class_exists(User::class)) {
            User::where('username', 'siswa_' . $saver->student_id)->update(['status' => 'Blokir']);
        }

        $reason = $request->input('reason', 'Pemblokiran rekening oleh Administrator');

        \App\Models\AuditLog::record(
            'BLOKIR_REKENING',
            'Data Nasabah',
            "Memblokir rekening nasabah: {$saver->student_name} (No. Rek: {$saver->account_number}, NIS: {$saver->student_id}). Alasan: {$reason}",
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Administrator'
        );

        return redirect()->back()
            ->with('success', 'Rekening nasabah ' . $saver->student_name . ' (' . ($saver->account_number ?? $saver->student_id) . ') berhasil DIBLOKIR. Transaksi setor dan tarik tunai dinonaktifkan.');
    }

    /**
     * Buka blokir rekening tabungan siswa.
     */
    public function unblockAccount($id)
    {
        $saver = Saver::findOrFail($id);
        $saver->update(['status' => 'active']);

        // Buka blokir akun user siswa jika ada
        if (class_exists(User::class)) {
            User::where('username', 'siswa_' . $saver->student_id)->update(['status' => 'Aktif']);
        }

        \App\Models\AuditLog::record(
            'BUKA_BLOKIR_REKENING',
            'Data Nasabah',
            "Membuka blokir rekening nasabah: {$saver->student_name} (No. Rek: {$saver->account_number}, NIS: {$saver->student_id}).",
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Administrator'
        );

        return redirect()->back()
            ->with('success', 'Blokir rekening nasabah ' . $saver->student_name . ' (' . ($saver->account_number ?? $saver->student_id) . ') berhasil DIBUKA. Rekening kembali aktif.');
    }

    /**
     * Deactivate a student's account (e.g. for graduates or moved students).
     */
    public function deactivateAccount($id)
    {
        $saver = Saver::findOrFail($id);
        if (Schema::hasColumn('savers', 'status')) {
            $saver->update([
                'status' => 'inactive'
            ]);
        }

        return redirect()->route('admin.savers.index')
            ->with('success', 'Akun nasabah ' . $saver->student_name . ' (' . ($saver->account_number ?? $saver->student_id) . ') berhasil dinonaktifkan.');
    }

    /**
     * Store a newly created saver / initial deposit.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (Setoran awal minimal Rp 10.000)
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

    public function storeSetoran(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Update student details.
     */
    public function update(Request $request, $id)
    {
        $saver = Saver::findOrFail($id);

        $request->validate([
            'student_id'   => 'required|string|unique:savers,student_id,' . $id,
            'student_name' => 'required|string|max:255',
            'grade_class'  => 'required|string|max:50',
        ]);

        $saver->update([
            'student_id'   => $request->student_id,
            'student_name' => $request->student_name,
            'grade_class'  => $request->grade_class,
        ]);

        if ($request->filled('security_pin')) {
            $saver->update([
                'security_pin' => Hash::make($request->security_pin)
            ]);
        }

        return redirect()->route('admin.savers.index')
            ->with('success', 'Data nasabah ' . $saver->student_name . ' berhasil diperbarui.');
    }

    /**
     * Display mutation/transaction history for a specific customer.
     */
    public function history($id)
    {
        $saver = Saver::findOrFail($id);
        $transactions = Transaction::where('saver_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.savers.history', compact('saver', 'transactions'));
    }

    /**
     * Reset security PIN for customer.
     */
    public function resetPin($id)
    {
        $saver = Saver::findOrFail($id);
        $saver->update([
            'security_pin' => Hash::make('123456')
        ]);

        return redirect()->route('admin.savers.index')
            ->with('success', 'PIN untuk nasabah ' . $saver->student_name . ' berhasil direset menjadi 123456.');
    }

    /**
     * Reset application password for customer (Plan B emergency route).
     */
    public function resetPassword(Request $request, $id)
    {
        $saver = Saver::findOrFail($id);

        // Update password on Saver model if column exists
        $saver->update([
            'password' => Hash::make('123456')
        ]);

        // Find associated user (via Member or username convention)
        $member = Member::where('student_id', $saver->student_id)->first();
        $user = $member?->user ?? User::where('username', 'siswa_' . $saver->student_id)->first();

        if ($user) {
            $user->update([
                'password' => Hash::make('123456')
            ]);
        }

        return redirect()->back()
            ->with('success', 'Password nasabah berhasil direset ke default (123456).');
    }

    /**
     * Soft delete saver record.
     */
    public function destroy($id)
    {
        $saver = Saver::findOrFail($id);
        $saverName = $saver->student_name;
        $saver->delete();

        return redirect()->route('admin.savers.index')
            ->with('success', 'Data nasabah ' . $saverName . ' berhasil dihapus.');
    }

    /**
     * Show Excel import form.
     */
    public function importForm()
    {
        $currentYear = date('Y');
        $lastPromotionYear = \App\Models\SystemSetting::where('key', 'last_class_promotion_year')->value('value');
        $lastPromotionAt = \App\Models\SystemSetting::where('key', 'last_class_promotion_at')->value('value');

        // Cek apakah ada data siswa di sistem yang perlu dinaikkan kelasnya
        $hasExistingStudents = Saver::whereNotNull('grade_class')
            ->where('grade_class', '!=', '')
            ->where('grade_class', 'not like', 'Lulus%')
            ->exists();

        // Boleh impor jika belum ada siswa sama sekali (setup awal baru), ATAU sudah melakukan kenaikan kelas tahun ini
        $canImport = (!$hasExistingStudents) || ($lastPromotionYear == $currentYear);

        return view('admin.savers.import', compact(
            'canImport',
            'currentYear',
            'lastPromotionYear',
            'lastPromotionAt',
            'hasExistingStudents'
        ));
    }

    /**
     * Download Excel template.
     */
    public function downloadTemplate()
    {
        try {
            if (class_exists(Spreadsheet::class)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Data Siswa');

                // Header & Sample Data
                $sheet->fromArray([
                    ['Nama Siswa', 'NIS', 'Kelas', 'Jenis Kelamin (L/P)'],
                    ['Ahmad Fauzi', '20261001', 'X RPL 1', 'L'],
                    ['Siti Nurhaliza', '20261002', 'X AKL 2', 'P'],
                    ['Budi Santoso', '20261003', 'XI DKV 1', 'L'],
                ], null, 'A1');

                // Styling
                $sheet->getStyle('A1:D1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '16233F'],
                    ],
                ]);
                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $writer = new Xlsx($spreadsheet);
                return response()->streamDownload(function () use ($writer) {
                    $writer->save('php://output');
                }, 'template_onboarding_siswa.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
            }
        } catch (\Throwable $e) {
            // Fallback to CSV below
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_onboarding_siswa.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, ['Nama Siswa', 'NIS', 'Kelas', 'Jenis Kelamin (L/P)']);
            fputcsv($file, ['Ahmad Fauzi', '20261001', 'X RPL 1', 'L']);
            fputcsv($file, ['Siti Nurhaliza', '20261002', 'X AKL 2', 'P']);
            fputcsv($file, ['Budi Santoso', '20261003', 'XI DKV 1', 'L']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Execute Excel/CSV import.
     */
    public function import(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        // Validasi: Wajib melakukan Kenaikan Kelas Siswa terlebih dahulu sebelum import siswa baru
        $currentYear = date('Y');
        $lastPromotionYear = \App\Models\SystemSetting::where('key', 'last_class_promotion_year')->value('value');
        $hasExistingStudents = Saver::whereNotNull('grade_class')
            ->where('grade_class', '!=', '')
            ->where('grade_class', 'not like', 'Lulus%')
            ->exists();

        if ($hasExistingStudents && $lastPromotionYear != $currentYear) {
            return back()->with('error', 'Aksi Ditolak: Anda wajib melakukan "Kenaikan Kelas Siswa" terlebih dahulu di menu Data Nasabah sebelum dapat mengimpor data siswa baru untuk tahun ajaran ' . $currentYear . '.');
        }

        try {
            if (!$request->hasFile('excel_file')) {
                return back()->with('error', 'Silakan pilih file Excel (.xlsx / .xls / .csv) terlebih dahulu.');
            }

            $file = $request->file('excel_file');
            if (!$file->isValid()) {
                return back()->with('error', 'File yang diunggah tidak valid atau melebihi batas upload server (upload_max_filesize).');
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt'])) {
                return back()->with('error', 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv).');
            }

            if ($file->getSize() > 10 * 1024 * 1024) {
                return back()->with('error', 'Ukuran file tidak boleh melebihi 10MB.');
            }

            $realPath = $file->getRealPath();
            $rows = [];

            if (in_array($extension, ['csv', 'txt'])) {
                if (($handle = fopen($realPath, 'r')) !== false) {
                    $firstLine = fgets($handle);
                    rewind($handle);
                    $separator = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                    while (($data = fgetcsv($handle, 2000, $separator)) !== false) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            } else {
                if (!class_exists(\ZipArchive::class) && $extension === 'xlsx') {
                    return back()->with('error', 'Ekstensi PHP "zip" belum aktif di cPanel hosting Anda (diperlukan untuk membaca file .xlsx). Silakan aktifkan modul "zip" di cPanel (Select PHP Version -> Extensions) atau simpan file sebagai format .CSV lalu unggah kembali.');
                }

                if (class_exists(IOFactory::class)) {
                    $spreadsheet = IOFactory::load($realPath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray(null, true, true, false);
                } else {
                    return back()->with('error', 'Library PhpSpreadsheet belum tersedia untuk membaca format Excel. Silakan gunakan format .CSV terlebih dahulu.');
                }
            }

            if (empty($rows)) {
                return back()->with('error', 'File yang diunggah kosong atau tidak memiliki data.');
            }

            $hasSaverPasswordCol = Schema::hasColumn('savers', 'password');
            $hasSaverGenderCol   = Schema::hasColumn('savers', 'gender');
            $hasSaverStatusCol   = Schema::hasColumn('savers', 'status');
            $hasSaverSaldoCol    = Schema::hasColumn('savers', 'saldo');
            $hasUserStatusCol    = Schema::hasColumn('users', 'status');
            $hasMembersTable     = Schema::hasTable('members');
            $hasMemberStatusCol  = $hasMembersTable && Schema::hasColumn('members', 'status');

            // Optimasi performa kritis: Hash '123456' CUKUP 1 KALI saja di luar loop!
            // Menghindari timeout Bcrypt CPU di hosting (mengubah proses ribuan hash menjadi 1 hash instan).
            $defaultHash = Hash::make('123456');

            $importedCount = 0;
            $isHeader = true;

            DB::transaction(function () use (
                $rows, 
                &$importedCount, 
                &$isHeader,
                $hasSaverPasswordCol,
                $hasSaverGenderCol,
                $hasSaverStatusCol,
                $hasSaverSaldoCol,
                $hasUserStatusCol,
                $hasMembersTable,
                $hasMemberStatusCol,
                $defaultHash
            ) {
                foreach ($rows as $row) {
                    if (empty($row) || !is_array($row)) {
                        continue;
                    }

                    $col0 = isset($row[0]) ? trim((string)$row[0]) : '';
                    $col1 = isset($row[1]) ? trim((string)$row[1]) : '';

                    if ($isHeader) {
                        $isHeader = false;
                        if (stripos($col0, 'nama') !== false || stripos($col1, 'nis') !== false) {
                            continue;
                        }
                    }

                    if (empty($col0) || empty($col1)) {
                        continue;
                    }

                    $studentName = $col0;
                    $studentId   = $col1;
                    $gradeClass  = isset($row[2]) && !empty(trim((string)$row[2])) ? trim((string)$row[2]) : '-';
                    $rawGender   = isset($row[3]) ? strtoupper(trim((string)$row[3])) : '';
                    $gender      = in_array($rawGender, ['L', 'P', 'LAKI-LAKI', 'PEREMPUAN']) ? substr($rawGender, 0, 1) : null;

                    $saverData = [
                        'student_name'   => $studentName,
                        'grade_class'    => $gradeClass,
                        'security_pin'   => $defaultHash,
                    ];
                    if ($hasSaverSaldoCol) {
                        $saverData['saldo'] = 0;
                    }
                    if ($hasSaverPasswordCol) {
                        $saverData['password'] = $defaultHash;
                    }
                    if ($hasSaverGenderCol) {
                        $saverData['gender'] = $gender;
                    }
                    if ($hasSaverStatusCol) {
                        $saverData['status'] = 'inactive';
                    }

                    try {
                        $saverData['account_number'] = null;
                        Saver::updateOrCreate(['student_id' => $studentId], $saverData);
                    } catch (\Throwable $saverEx) {
                        if (stripos($saverEx->getMessage(), 'account_number') !== false) {
                            $saverData['account_number'] = 'DRAFT-' . $studentId;
                            Saver::updateOrCreate(['student_id' => $studentId], $saverData);
                        } else {
                            throw $saverEx;
                        }
                    }

                    $userAttributes = [
                        'password'     => $defaultHash,
                        'officer_name' => $studentName,
                        'role'         => 'Saver',
                    ];
                    if ($hasUserStatusCol) {
                        $userAttributes['status'] = 'Aktif';
                    }

                    $user = User::firstOrCreate(
                        ['username' => 'siswa_' . $studentId],
                        $userAttributes
                    );

                    if ($hasMembersTable) {
                        $memberData = [
                            'user_id'         => $user->id,
                            'name'            => $studentName,
                            'grade_class'     => $gradeClass,
                            'graduation_year' => date('Y'),
                        ];
                        if ($hasMemberStatusCol) {
                            $memberData['status'] = 'inactive';
                        }

                        Member::updateOrCreate(['student_id' => $studentId], $memberData);
                    }

                    $importedCount++;
                }
            });

            if ($importedCount === 0) {
                return back()->with('error', 'Tidak ada data siswa yang berhasil diimpor. Pastikan file sesuai template.');
            }

            return redirect()->route('admin.members.index')
                ->with('success', "Berhasil mengimpor {$importedCount} data siswa baru ke Data Anggota! Silakan lakukan aktivasi rekening.");
        } catch (\Throwable $e) {
            try {
                Log::error('Import data siswa gagal: ' . $e->getMessage(), [
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]);
            } catch (\Throwable $logEx) {
                // Ignore log write failure
            }

            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
