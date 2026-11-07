<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use App\Models\Member;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminMemberController extends Controller
{
    /**
     * Display a listing of inactive students (raw onboarding data from Excel).
     * Single source of truth: Saver model with status 'inactive', sorted by NIS ascending.
     */
    public function index(Request $request)
    {
        // 1. Query inactive students directly from Saver model
        $query = Saver::where('status', 'inactive');

        // 2. Search by Student Name or NIS (student_id)
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('student_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('student_id', 'like', '%' . $searchTerm . '%');
            });
        }

        // 3. Filter by Class (grade_class)
        if ($request->filled('class')) {
            $selectedClass = trim($request->class);
            $query->where('grade_class', $selectedClass);
        }

        // 4. Default sort by NIS (student_id) ascending
        $members = $query->orderBy('student_id', 'asc')->get();

        // 5. Distinct classes for filter dropdown
        $classes = Saver::where('status', 'inactive')
            ->whereNotNull('grade_class')
            ->where('grade_class', '!=', '')
            ->distinct()
            ->pluck('grade_class')
            ->sort()
            ->values();

        return view('admin.member-data', compact('members', 'classes'));
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
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel compatibility
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

            // 1. Ekstraksi baris data dari file (mendukung CSV native dan Excel via PhpSpreadsheet)
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

            // 2. Loop & simpan data ke database
            $importedCount = 0;
            $isHeader = true;

            DB::transaction(function () use ($rows, &$importedCount, &$isHeader) {
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

                    // A. Saver record
                    $saverData = [
                        'account_number' => null,
                        'student_name'   => $studentName,
                        'grade_class'    => $gradeClass,
                        'security_pin'   => Hash::make('123456'),
                        'password'       => Hash::make('123456'),
                        'saldo'          => 0,
                    ];
                    if (Schema::hasColumn('savers', 'gender')) {
                        $saverData['gender'] = $gender;
                    }
                    if (Schema::hasColumn('savers', 'status')) {
                        $saverData['status'] = 'inactive';
                    }

                    Saver::updateOrCreate(['student_id' => $studentId], $saverData);

                    // B. User record
                    $user = User::firstOrCreate(
                        ['username' => 'siswa_' . $studentId],
                        [
                            'password'     => Hash::make('123456'),
                            'officer_name' => $studentName,
                            'role'         => 'Saver',
                            'status'       => 'Aktif',
                        ]
                    );

                    // C. Member record
                    $memberData = [
                        'user_id'         => $user->id,
                        'name'            => $studentName,
                        'grade_class'     => $gradeClass,
                        'graduation_year' => date('Y'),
                    ];
                    if (Schema::hasColumn('members', 'status')) {
                        $memberData['status'] = 'inactive';
                    }

                    Member::updateOrCreate(['student_id' => $studentId], $memberData);

                    $importedCount++;
                }
            });

            if ($importedCount === 0) {
                return back()->with('error', 'Tidak ada data siswa yang berhasil diimpor. Pastikan format kolom: Kolom A = Nama Siswa, Kolom B = NIS, Kolom C = Kelas, Kolom D = Jenis Kelamin (L/P).');
            }

            return redirect()->route('admin.members.index')
                ->with('success', "Berhasil mengimpor {$importedCount} data siswa baru ke Data Anggota! Silakan lakukan aktivasi rekening.");
        } catch (\Throwable $e) {
            Log::error('Import data siswa gagal: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Activate a student's account:
     * - Status -> active
     * - Auto-generate unique account_number (if null)
     * - Set security_pin & password to Hash::make('123456') (default)
     * - Set initial balance (saldo) to Rp 10.000
     * - Create initial Deposit transaction record to maintain accounting balance
     * - Record double-entry accounting journals (Kas 101 Debit, Tabungan 201 Credit)
     * - Redirects back to admin.members.index
     */
    public function activate(Request $request, $id)
    {
        // 1. Locate Saver or Member
        $saver = Saver::where('id', $id)
            ->orWhere('student_id', $id)
            ->first();

        $member = null;
        if (!$saver) {
            $member = Member::where('user_id', $id)
                ->orWhere('student_id', $id)
                ->first();
            if ($member) {
                $saver = Saver::where('student_id', $member->student_id)->first();
            }
        } else {
            $member = Member::where('student_id', $saver->student_id)
                ->orWhere('user_id', $id)
                ->first();
        }

        if (!$saver && !$member) {
            abort(404, 'Data calon siswa tidak ditemukan.');
        }

        // 2. Determine default or custom PIN (supports testing compatibility)
        $rawPin = $request->filled('security_pin') ? $request->security_pin : '123456';
        $hashedPin = Hash::make($rawPin);
        $hashedPassword = Hash::make('123456');

        // 3. Generate unique Account Number if not already assigned
        $accountNumber = $saver ? $saver->account_number : ($member ? $member->account_number : null);
        if (!$accountNumber) {
            $schoolYear = date('Y');
            $lastSaver = Saver::where('account_number', 'like', $schoolYear . '%')
                ->orderBy('account_number', 'desc')
                ->first();
            $nextSequence = $lastSaver ? ((int) substr($lastSaver->account_number, -4) + 1) : 1;
            $accountNumber = $schoolYear . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        }

        $initialBalance = 10000;

        // 4. Update or create active Saver record with initial balance
        if ($saver) {
            $saver->update([
                'status'         => 'active',
                'account_number' => $accountNumber,
                'security_pin'   => $hashedPin,
                'password'       => $hashedPassword,
                'saldo'          => ($saver->saldo && $saver->saldo > 0) ? $saver->saldo : $initialBalance,
            ]);
        } else {
            $saver = Saver::create([
                'student_id'     => $member->student_id,
                'student_name'   => $member->name,
                'grade_class'    => $member->grade_class ?: ($member->graduation_year ? ('Angkatan ' . $member->graduation_year) : '-'),
                'account_number' => $accountNumber,
                'security_pin'   => $hashedPin,
                'password'       => $hashedPassword,
                'status'         => 'active',
                'saldo'          => $initialBalance,
            ]);
        }

        // 5. Automatically create initial Deposit transaction record to maintain accounting balance
        $adminUserId = auth()->id() ?? User::whereIn('role', ['Administrator', 'Admin'])->value('id') ?? User::first()?->id ?? 1;

        $hasInitialDeposit = Transaction::where('saver_id', $saver->id)
            ->where('transaction_type', 'Deposit')
            ->exists();

        if (!$hasInitialDeposit) {
            $transactionData = [
                'saver_id'         => $saver->id,
                'user_id'          => $adminUserId,
                'transaction_type' => 'Deposit',
                'amount'           => $initialBalance,
                'status'           => 'Approved',
                'transaction_date' => now(),
            ];

            if (Schema::hasColumn('transactions', 'description')) {
                $transactionData['description'] = 'Setoran Awal Otomatis (Aktivasi Rekening)';
            }
            if (Schema::hasColumn('transactions', 'notes')) {
                $transactionData['notes'] = 'Setoran Awal Otomatis (Aktivasi Rekening)';
            }

            $transaction = Transaction::create($transactionData);

            // Double-entry accounting: Debit Kas 101, Kredit Tabungan 201
            if (Schema::hasTable('accounting_journals')) {
                DB::table('accounting_journals')->insert([
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '101', // Debit Kas
                        'position'       => 'Debit',
                        'amount'         => $initialBalance,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ],
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '201', // Kredit Tabungan
                        'position'       => 'Credit',
                        'amount'         => $initialBalance,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ],
                ]);
            }
        }

        // 6. Synchronize to Member & User if exists
        if ($member) {
            $memberUpdate = [
                'status'       => 'active',
                'security_pin' => $hashedPin,
            ];
            if (Schema::hasColumn('members', 'account_number')) {
                $memberUpdate['account_number'] = $accountNumber;
            }
            $member->update($memberUpdate);

            if ($member->user) {
                $member->user->update([
                    'password' => $hashedPassword,
                    'status'   => 'Aktif',
                ]);
            }
        }

        $studentName = $saver->student_name ?? ($member ? $member->name : 'Siswa');
        $studentId = $saver->student_id ?? ($member ? $member->student_id : '-');

        return redirect()->route('admin.members.index')
            ->with('success', 'Rekening siswa ' . $studentName . ' (NIS: ' . $studentId . ') berhasil diaktifkan dengan saldo awal Rp 10.000! Nomor Rekening: ' . $accountNumber . ' | PIN Default: ' . $rawPin . '. Siswa kini tercatat sebagai nasabah aktif.');
    }

    public function edit($id)
    {
        $saver = Saver::where('id', $id)->orWhere('student_id', $id)->first();
        $member = Member::where('user_id', $id)
            ->orWhere('student_id', $saver ? $saver->student_id : $id)
            ->first();

        if (!$member && $saver) {
            $member = (object)[
                'id' => $saver->id,
                'user_id' => $saver->id,
                'student_id' => $saver->student_id,
                'name' => $saver->student_name,
                'grade_class' => $saver->grade_class,
                'graduation_year' => date('Y'),
                'status' => $saver->status,
            ];
        }

        return view('admin.members.edit', compact('member', 'saver'));
    }

    public function update(Request $request, $id)
    {
        $saver = Saver::where('id', $id)->orWhere('student_id', $id)->first();
        $member = Member::where('user_id', $id)->orWhere('student_id', $id)->first();

        $request->validate([
            'name'        => 'required|string|max:255',
            'student_id'  => 'required|string|max:50',
            'grade_class' => 'nullable|string|max:50',
        ]);

        if ($saver) {
            $saver->update([
                'student_name' => $request->name,
                'student_id'   => $request->student_id,
                'grade_class'  => $request->grade_class ?? $saver->grade_class,
            ]);
        }

        if ($member) {
            $member->update([
                'name'        => $request->name,
                'student_id'  => $request->student_id,
                'grade_class' => $request->grade_class ?? $member->grade_class,
            ]);
            if ($member->user) {
                $member->user->update(['officer_name' => $request->name]);
            }
        }

        return redirect()->route('admin.members.index')
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $saver = Saver::where('id', $id)->orWhere('student_id', $id)->first();
        $studentName = $saver ? $saver->student_name : 'Siswa';

        if ($saver) {
            $saver->delete();
        }

        $member = Member::where('user_id', $id)->orWhere('student_id', $saver ? $saver->student_id : $id)->first();
        if ($member) {
            $studentName = $member->name;
            if ($member->user) {
                $member->user->delete();
            }
            $member->delete();
        }

        return redirect()->route('admin.members.index')
            ->with('success', 'Data anggota ' . $studentName . ' berhasil dihapus.');
    }

    /**
     * Reset application password for member/customer (Plan B emergency route).
     */
    public function resetPassword(Request $request, $id)
    {
        $saver = Saver::where('id', $id)->orWhere('student_id', $id)->first();
        $member = Member::where('user_id', $id)->orWhere('student_id', $id)->first();

        if ($saver) {
            $saver->update([
                'password' => Hash::make('123456'),
            ]);
        }

        $user = $member?->user ?? ($saver ? (Member::where('student_id', $saver->student_id)->first()?->user ?? User::where('username', 'siswa_' . $saver->student_id)->first()) : User::find($id));

        if ($user) {
            $user->update([
                'password' => Hash::make('123456'),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Password nasabah berhasil direset ke default (123456).');
    }
}
