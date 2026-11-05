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
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SaversImport;
use App\Exports\SaversTemplateExport;

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
            $query = Saver::where('status', 'active');
        } else {
            $query = Saver::query();
        }

        // 2. Search by Student Name or NIS (student_id)
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('student_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('student_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('account_number', 'like', '%' . $searchTerm . '%');
            });
        }

        // 3. Filter by Class (grade_class)
        if ($request->filled('class')) {
            $query->where('grade_class', $request->class);
        }

        // 4. Default sort by NIS (student_id) ascending
        $savers = $query->orderBy('student_id', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Distinct classes for filter dropdown
        $classesQuery = Saver::whereNotNull('grade_class');
        if (Schema::hasColumn('savers', 'status')) {
            $classesQuery->where('status', 'active');
        }
        $classes = $classesQuery->distinct()
            ->orderBy('grade_class', 'asc')
            ->pluck('grade_class');

        return view('admin.savers.index', compact('savers', 'classes'));
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
        return view('admin.savers.import');
    }

    /**
     * Download Excel template.
     */
    public function downloadTemplate()
    {
        if (class_exists(Excel::class)) {
            return Excel::download(new SaversTemplateExport, 'template_onboarding_siswa.xlsx');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_onboarding_siswa.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama Siswa', 'NIS', 'Kelas', 'Jenis Kelamin (L/P)']);
            fputcsv($file, ['Ahmad Fauzi', '20261001', 'X RPL 1', 'L']);
            fputcsv($file, ['Siti Nurhaliza', '20261002', 'X AKL 2', 'P']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Execute Excel import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excel_file.required' => 'Silakan pilih file Excel (.xlsx / .xls / .csv) terlebih dahulu.',
            'excel_file.mimes'    => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'excel_file.max'      => 'Ukuran file tidak boleh melebihi 10MB.',
        ]);

        try {
            if (class_exists(Excel::class)) {
                Excel::import(new SaversImport, $request->file('excel_file'));
            } else {
                $path = $request->file('excel_file')->getRealPath();
                $file = fopen($path, 'r');
                $isHeader = true;
                while (($row = fgetcsv($file, 1000, ',')) !== false) {
                    if ($isHeader) { $isHeader = false; continue; }
                    if (empty($row[0]) || empty($row[1])) continue;
                    $studentId = trim((string)$row[1]);
                    $studentName = trim((string)$row[0]);
                    $gradeClass = trim((string)($row[2] ?? '-'));

                    Saver::updateOrCreate(
                        ['student_id' => $studentId],
                        [
                            'account_number' => null,
                            'student_name'   => $studentName,
                            'grade_class'    => $gradeClass,
                            'gender'         => isset($row[3]) ? strtoupper(trim((string)$row[3])) : null,
                            'security_pin'   => Hash::make('123456'),
                            'password'       => Hash::make('123456'),
                            'status'         => 'inactive',
                            'saldo'          => 0,
                        ]
                    );

                    // Sync to User and Member for Data Anggota
                    $user = \App\Models\User::firstOrCreate(
                        ['username' => 'siswa_' . $studentId],
                        [
                            'password'     => Hash::make('123456'),
                            'officer_name' => $studentName,
                            'role'         => 'Saver',
                            'status'       => 'Aktif'
                        ]
                    );

                    \App\Models\Member::updateOrCreate(
                        ['student_id' => $studentId],
                        [
                            'user_id'         => $user->id,
                            'name'            => $studentName,
                            'grade_class'     => $gradeClass,
                            'graduation_year' => date('Y'),
                            'status'          => 'inactive',
                        ]
                    );
                }
                fclose($file);
            }

            return redirect()->route('admin.members.index')
                ->with('success', 'Data siswa baru berhasil diimpor ke Data Anggota! Silakan lakukan aktivasi rekening.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
