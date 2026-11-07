<?php

namespace App\Imports;

use App\Models\Saver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SaversImport implements ToModel, WithStartRow, SkipsEmptyRows
{
    protected static ?string $cachedHash = null;

    protected function getDefaultHash(): string
    {
        if (static::$cachedHash === null) {
            static::$cachedHash = Hash::make('123456');
        }
        return static::$cachedHash;
    }

    /**
     * Mulai membaca baris dari baris ke-2 (melewati baris header)
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Mapping data Excel ke Model Saver
     *
     * @param array $row
     * @return Model|array|null
     */
    public function model(array $row): Model|array|null
    {
        // Mendapatkan data kolom berdasarkan indeks (Column 0: Name, Column 1: NIS, Column 2: Class, Column 3: Gender)
        $studentName = isset($row[0]) ? trim((string)$row[0]) : null;
        $studentId   = isset($row[1]) ? trim((string)$row[1]) : null;
        $gradeClass  = isset($row[2]) ? trim((string)$row[2]) : '-';
        $gender      = isset($row[3]) ? strtoupper(trim((string)$row[3])) : null;

        // Validasi data minimal: Nama dan NIS tidak boleh kosong
        if (empty($studentName) || empty($studentId)) {
            return null;
        }

        $defaultHash = $this->getDefaultHash();

        // UpdateOrCreate berdasarkan NIS (student_id) untuk mencegah duplikasi di Saver
        $saver = Saver::updateOrCreate(
            ['student_id' => $studentId],
            [
                'account_number' => null, // Wajib NULL (akan digenerate saat setoran pertama)
                'student_name'   => $studentName,
                'grade_class'    => $gradeClass,
                'gender'         => $gender,
                'security_pin'   => $defaultHash, // Default PIN 123456
                'password'       => $defaultHash, // Default Password 123456
                'status'         => 'inactive',   // Wajib 'inactive'
                'saldo'          => 0,
            ]
        );

        // Pastikan User & Member juga tersinkronisasi untuk menu Data Anggota (Siswa Baru)
        $user = \App\Models\User::firstOrCreate(
            ['username' => 'siswa_' . $studentId],
            [
                'password'     => $defaultHash,
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

        return $saver;
    }
}
