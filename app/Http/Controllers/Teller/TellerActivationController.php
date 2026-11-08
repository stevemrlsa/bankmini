<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Saver;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TellerActivationController extends Controller
{
    /**
     * Display a listing of inactive students waiting for One-Stop Activation.
     */
    public function index(Request $request)
    {
        // Sinkronisasi awal jika tabel savers belum memiliki data dari members
        if (Saver::whereIn('status', ['inactive', 'Belum Aktif', 'Nonaktif', 'pending'])->count() === 0) {
            $inactiveMembers = Member::whereIn('status', ['inactive', 'Belum Aktif'])->get();
            if ($inactiveMembers->isNotEmpty()) {
                $defaultHash = Hash::make('123456');
                foreach ($inactiveMembers as $m) {
                    Saver::firstOrCreate(
                        ['student_id' => $m->student_id],
                        [
                            'student_name' => $m->name,
                            'grade_class'  => $m->grade_class ?: ($m->graduation_year ? ('Angkatan ' . $m->graduation_year) : '-'),
                            'security_pin' => $m->security_pin ?? $defaultHash,
                            'password'     => $defaultHash,
                            'status'       => 'inactive',
                            'saldo'        => 0,
                        ]
                    );
                }
            }
        }

        // Base query: calon nasabah belum aktif (kecuali siswa yang sudah lulus / alumni)
        $baseQuery = Saver::whereIn('status', ['inactive', 'Belum Aktif', 'Nonaktif', 'pending'])
            ->where(function ($q) {
                $q->whereNull('grade_class')
                  ->orWhere('grade_class', 'not like', 'Lulus%');
            });

        $totalWaitingCount = (clone $baseQuery)->count();

        // Daftar kelas unik yang terurut rapi
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

        // Jika sedang mencari nama/NIS, cari di SELURUH kelas (mengabaikan opsi kelas di sebelah kirinya)
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('student_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('student_id', 'like', '%' . $searchTerm . '%');
            });
            // Opsi pencarian mengabaikan pilihan kelas
            $currentClass = null;
            $prevClass = null;
            $nextClass = null;
        } else {
            // Mode Tampilan Per Kelas (1 tabel = 1 kelas dengan tombol Kelas Sebelumnya & Selanjutnya)
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

        $members = $query->orderBy('student_id', 'asc')->get();

        return view('teller.activations.index', compact(
            'members',
            'classes',
            'currentClass',
            'prevClass',
            'nextClass',
            'currentClassIndex',
            'totalClasses',
            'totalWaitingCount'
        ));
    }

    /**
     * Perform One-Stop Activation for student by Teller:
     * - Create/Update User login account (username, email, whatsapp_number, password)
     * - Activate Saver record with generated unique Account Number
     * - Book initial Deposit Transaction (min Rp 10.000)
     * - Update Saver balance
     * - Record double-entry Accounting Journals (Kas 101 Debit, Tabungan 201 Credit)
     */
    public function activate(Request $request, $id)
    {
        // 1. Locate Saver or Member record
        $saver = Saver::where('id', $id)
            ->orWhere('student_id', $id)
            ->first();

        $member = null;
        if (!$saver) {
            $member = Member::where('student_id', $id)->orWhere('user_id', $id)->first();
            if ($member) {
                $saver = Saver::where('student_id', $member->student_id)->first();
            }
        } else {
            $member = Member::where('student_id', $saver->student_id)->first();
        }

        if (!$saver && !$member) {
            return redirect()->back()->with('error', 'Data calon nasabah tidak ditemukan di sistem.');
        }

        $studentId   = $saver ? $saver->student_id : $member->student_id;
        $studentName = $saver ? $saver->student_name : $member->name;
        $gradeClass  = $saver ? $saver->grade_class : ($member->grade_class ?? '-');

        // Check if an existing User already exists for this student
        $existingUser = null;
        if ($saver && !empty($saver->user_id)) {
            $existingUser = User::find($saver->user_id);
        }
        if (!$existingUser && $member && $member->user_id) {
            $existingUser = User::find($member->user_id);
        }
        if (!$existingUser) {
            $existingUser = User::where('username', 'siswa_' . $studentId)->first();
        }

        // Normalize input fields
        $request->merge([
            'username'        => trim((string) $request->input('username')),
            'email'           => strtolower(trim((string) $request->input('email'))),
            'whatsapp_number' => preg_replace('/\s+/', '', trim((string) $request->input('whatsapp_number'))),
        ]);

        $usernameRules = [
            'required',
            'string',
            'max:255',
            Rule::unique('users', 'username')->ignore($existingUser?->id),
        ];
        if (Schema::hasColumn('savers', 'username')) {
            $usernameRules[] = Rule::unique('savers', 'username')->ignore($saver?->id);
        }

        $emailRules = [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($existingUser?->id),
        ];
        if (Schema::hasColumn('savers', 'email')) {
            $emailRules[] = Rule::unique('savers', 'email')->ignore($saver?->id);
        }

        $waRules = [
            'required',
            'string',
            'min:9',
            'max:20',
            Rule::unique('users', 'whatsapp_number')->ignore($existingUser?->id),
        ];
        if (Schema::hasColumn('savers', 'whatsapp_number')) {
            $waRules[] = Rule::unique('savers', 'whatsapp_number')->ignore($saver?->id);
        }

        // 2. Strict Validation with custom error messages
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'username'        => $usernameRules,
            'email'           => $emailRules,
            'whatsapp_number' => $waRules,
            'amount'          => ['required', 'numeric', 'min:10000'],
            'active_modal_id' => ['nullable'],
        ], [
            'username.required'        => 'Username wajib diisi.',
            'username.unique'          => 'Username "' . $request->input('username') . '" sudah digunakan oleh akun lain. Silakan ubah dengan username yang berbeda!',
            'email.required'           => 'Alamat email wajib diisi.',
            'email.email'              => 'Format alamat email tidak valid.',
            'email.unique'             => 'Alamat email "' . $request->input('email') . '" sudah terdaftar pada akun lain. Silakan gunakan alamat email yang berbeda!',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.min'      => 'Nomor WhatsApp tidak valid (minimal 9 digit).',
            'whatsapp_number.unique'   => 'Nomor WhatsApp "' . $request->input('whatsapp_number') . '" sudah digunakan oleh nasabah/akun lain. Wajib menggunakan nomor WhatsApp yang berbeda!',
            'amount.required'          => 'Nominal setoran awal wajib diisi.',
            'amount.numeric'           => 'Nominal setoran awal harus berupa angka.',
            'amount.min'               => 'Setoran awal minimal adalah Rp 10.000.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Aktivasi Gagal: ' . $validator->errors()->first());
        }

        $validated = $validator->validated();

        $initialAmount = (float) $validated['amount'];
        $tellerUserId  = auth()->id() ?? User::where('role', 'Teller')->value('id') ?? 1;

        try {
            DB::transaction(function () use (
                $validated,
                $studentId,
                $studentName,
                $gradeClass,
                $initialAmount,
                $tellerUserId,
                $existingUser,
                &$saver,
                &$member,
                &$accountNumber,
                &$transaction
            ) {
                // a. Find or create the User account for this student
                if ($existingUser) {
                    $user = $existingUser;
                    $user->update([
                        'username'        => $validated['username'],
                        'email'           => $validated['email'],
                        'whatsapp_number' => $validated['whatsapp_number'],
                        'password'        => Hash::make('123456'),
                        'officer_name'    => $studentName,
                        'role'            => 'Saver',
                        'status'          => 'Aktif',
                    ]);
                } else {
                    $user = new User();
                    // Jika data member sudah punya user_id dan belum ada di tabel users, gunakan user_id tersebut
                    if ($member && !empty($member->user_id) && !User::where('id', $member->user_id)->exists()) {
                        $user->id = $member->user_id;
                    }
                    $user->username        = $validated['username'];
                    $user->email           = $validated['email'];
                    $user->whatsapp_number = $validated['whatsapp_number'];
                    $user->password        = Hash::make('123456');
                    $user->officer_name    = $studentName;
                    $user->role            = 'Saver';
                    $user->status          = 'Aktif';
                    $user->save();
                }

                // b. Generate unique Account Number if not already assigned
                $accountNumber = $saver?->account_number ?: ($member?->account_number ?? null);
                if (!$accountNumber) {
                    $schoolYear = date('Y');
                    $lastSaver = Saver::where('account_number', 'like', $schoolYear . '%')
                        ->orderBy('account_number', 'desc')
                        ->first();
                    $nextSequence = $lastSaver ? ((int) substr($lastSaver->account_number, -4) + 1) : 1;
                    $accountNumber = $schoolYear . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
                }

                // c. Update or create active Saver record
                if ($saver) {
                    $saver->status         = 'active';
                    $saver->account_number = $accountNumber;
                    $saver->security_pin   = $saver->security_pin ?: Hash::make('123456');
                    $saver->password       = $saver->password ?: Hash::make('123456');
                    if (Schema::hasColumn('savers', 'user_id')) {
                        $saver->user_id = $user->id;
                    }
                    if (Schema::hasColumn('savers', 'username')) {
                        $saver->username = $validated['username'];
                    }
                    if (Schema::hasColumn('savers', 'email')) {
                        $saver->email = $validated['email'];
                    }
                    if (Schema::hasColumn('savers', 'whatsapp_number')) {
                        $saver->whatsapp_number = $validated['whatsapp_number'];
                    }
                    $saver->save();
                } else {
                    $saverData = [
                        'student_id'     => $studentId,
                        'student_name'   => $studentName,
                        'grade_class'    => $gradeClass,
                        'account_number' => $accountNumber,
                        'security_pin'   => Hash::make('123456'),
                        'password'       => Hash::make('123456'),
                        'status'         => 'active',
                        'saldo'          => 0,
                    ];
                    if (Schema::hasColumn('savers', 'user_id')) {
                        $saverData['user_id'] = $user->id;
                    }
                    if (Schema::hasColumn('savers', 'username')) {
                        $saverData['username'] = $validated['username'];
                    }
                    if (Schema::hasColumn('savers', 'email')) {
                        $saverData['email'] = $validated['email'];
                    }
                    if (Schema::hasColumn('savers', 'whatsapp_number')) {
                        $saverData['whatsapp_number'] = $validated['whatsapp_number'];
                    }
                    $saver = Saver::create($saverData);
                }

                // Also synchronize Member record safely without PRIMARY KEY collision
                if (Schema::hasTable('members')) {
                    $existingMemberRecord = DB::table('members')->where('student_id', $studentId)->first();
                    if ($existingMemberRecord) {
                        $updateData = [
                            'name'            => $studentName,
                            'grade_class'     => $gradeClass,
                            'graduation_year' => $existingMemberRecord->graduation_year ?: date('Y'),
                            'account_number'  => $accountNumber,
                            'status'          => 'active',
                            'updated_at'      => now(),
                        ];
                        // Update data profil anggota tanpa mengubah user_id primary key agar tidak terjadi SQLSTATE 1062
                        DB::table('members')->where('student_id', $studentId)->update($updateData);
                    } else {
                        $targetUserId = $user->id;
                        if (DB::table('members')->where('user_id', $targetUserId)->exists()) {
                            $maxUserId = (int) DB::table('members')->max('user_id');
                            $targetUserId = max($maxUserId + 1, $user->id + 1);
                        }
                        DB::table('members')->insert([
                            'user_id'         => $targetUserId,
                            'student_id'      => $studentId,
                            'name'            => $studentName,
                            'grade_class'     => $gradeClass,
                            'graduation_year' => date('Y'),
                            'account_number'  => $accountNumber,
                            'status'          => 'active',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }

                // d. Create new Transaction (Setoran Awal / Deposit)
                $transactionData = [
                    'saver_id'         => $saver->id,
                    'user_id'          => $tellerUserId,
                    'transaction_type' => 'Deposit',
                    'amount'           => $initialAmount,
                    'status'           => 'Approved',
                    'transaction_date' => now(),
                ];
                if (Schema::hasColumn('transactions', 'description')) {
                    $transactionData['description'] = 'Setoran Awal Aktivasi Rekening Siswa';
                }
                if (Schema::hasColumn('transactions', 'notes')) {
                    $transactionData['notes'] = 'Setoran Awal Aktivasi Rekening Siswa';
                }

                $transaction = Transaction::create($transactionData);

                // e. Update Saver balance
                $saver->saldo = ($saver->saldo ?? 0) + $initialAmount;
                $saver->save();

                // f. Real-time Double-Entry Accounting: Debit Kas 101, Kredit Tabungan 201
                if (Schema::hasTable('accounting_journals')) {
                    DB::table('accounting_journals')->insert([
                        [
                            'transaction_id' => $transaction->id,
                            'account_code'   => '101', // Kas Utama (Debit)
                            'position'       => 'Debit',
                            'amount'         => $initialAmount,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ],
                        [
                            'transaction_id' => $transaction->id,
                            'account_code'   => '201', // Simpanan/Tabungan Nasabah (Credit)
                            'position'       => 'Credit',
                            'amount'         => $initialAmount,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ],
                    ]);
                }

                // Flash receipt data for immediate print receipt in Teller UI
                session()->flash('receipt', [
                    'id'             => $transaction->id,
                    'type'           => 'Setoran Awal (Aktivasi Rekening)',
                    'account_number' => $accountNumber,
                    'student_name'   => $studentName,
                    'amount'         => $initialAmount,
                    'date'           => now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB',
                ]);
            });

            // Record Audit Log
            \App\Models\AuditLog::record(
                'AKTIVASI_REKENING',
                'Aktivasi Rekening',
                "Aktivasi rekening nasabah {$studentName} (NIS: {$studentId}, Rek: {$accountNumber}) dengan setoran awal Rp " . number_format($initialAmount, 0, ',', '.') . " berhasil dilakukan.",
                auth()->id(),
                auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
            );

            return redirect()->back()->with(
                'success',
                "Aktivasi Rekening {$studentName} berhasil! No. Rekening: {$accountNumber}, Setoran Awal: Rp " . number_format($initialAmount, 0, ',', '.') . " telah dibukukan."
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses aktivasi: ' . $e->getMessage());
        }
    }
}
