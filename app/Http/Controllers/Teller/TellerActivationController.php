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
        // 1. Fetch inactive students from Saver model
        $query = Saver::whereIn('status', ['inactive', 'Belum Aktif', 'Nonaktif', 'pending']);

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

        // If no records in Saver, check Member table for inactive members
        if ($members->isEmpty() && !$request->filled('search') && !$request->filled('class')) {
            $inactiveMembers = Member::whereIn('status', ['inactive', 'Belum Aktif'])->get();
            if ($inactiveMembers->isNotEmpty()) {
                foreach ($inactiveMembers as $m) {
                    Saver::firstOrCreate(
                        ['student_id' => $m->student_id],
                        [
                            'student_name' => $m->name,
                            'grade_class'  => $m->grade_class ?: ($m->graduation_year ? ('Angkatan ' . $m->graduation_year) : '-'),
                            'security_pin' => $m->security_pin ?? Hash::make('123456'),
                            'password'     => Hash::make('123456'),
                            'status'       => 'inactive',
                            'saldo'        => 0,
                        ]
                    );
                }
                $members = Saver::whereIn('status', ['inactive', 'Belum Aktif', 'Nonaktif', 'pending'])
                    ->orderBy('student_id', 'asc')
                    ->get();
            }
        }

        // 5. Distinct classes for filter dropdown
        $classes = Saver::whereIn('status', ['inactive', 'Belum Aktif', 'pending'])
            ->whereNotNull('grade_class')
            ->where('grade_class', '!=', '')
            ->distinct()
            ->pluck('grade_class')
            ->sort()
            ->values();

        return view('teller.activations.index', compact('members', 'classes'));
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
        if ($member && $member->user_id) {
            $existingUser = User::find($member->user_id);
        }
        if (!$existingUser) {
            $existingUser = User::where('username', 'siswa_' . $studentId)->first();
        }

        // 2. Strict Validation with custom error messages
        $validated = $request->validate([
            'username'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($existingUser?->id),
            ],
            'email'           => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($existingUser?->id),
            ],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'amount'          => ['required', 'numeric', 'min:10000'],
            'active_modal_id' => ['nullable'],
        ], [
            'username.required'        => 'Username wajib diisi.',
            'username.unique'          => 'Username sudah digunakan oleh akun lain.',
            'email.required'           => 'Alamat email wajib diisi.',
            'email.email'              => 'Format alamat email tidak valid.',
            'email.unique'             => 'Alamat email sudah terdaftar di sistem.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'amount.required'          => 'Nominal setoran awal wajib diisi.',
            'amount.numeric'           => 'Nominal setoran awal harus berupa angka.',
            'amount.min'               => 'Setoran awal minimal adalah Rp 10.000.',
        ]);

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
                    $user = User::create([
                        'username'        => $validated['username'],
                        'email'           => $validated['email'],
                        'whatsapp_number' => $validated['whatsapp_number'],
                        'password'        => Hash::make('123456'),
                        'officer_name'    => $studentName,
                        'role'            => 'Saver',
                        'status'          => 'Aktif',
                    ]);
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
                    $saver = Saver::create($saverData);
                }

                // Also synchronize Member record
                Member::updateOrCreate(
                    ['student_id' => $studentId],
                    [
                        'user_id'         => $user->id,
                        'name'            => $studentName,
                        'grade_class'     => $gradeClass,
                        'graduation_year' => date('Y'),
                        'account_number'  => $accountNumber,
                        'status'          => 'active',
                    ]
                );

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
                    'date'           => now()->format('d/m/Y H:i'),
                ]);
            });

            return redirect()->back()->with(
                'success',
                "Aktivasi Rekening {$studentName} berhasil! No. Rekening: {$accountNumber}, Setoran Awal: Rp " . number_format($initialAmount, 0, ',', '.') . " telah dibukukan."
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses aktivasi: ' . $e->getMessage());
        }
    }
}
