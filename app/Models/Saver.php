<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Saver extends Authenticatable
{
    use HasFactory, HasApiTokens, SoftDeletes;
    protected $table = 'savers';

    public const CATEGORIES = [
        'student'   => 'Siswa',
        'teacher'   => 'Guru / Pendidik',
        'staff'     => 'Staf & Karyawan',
        'community' => 'Kantin & Warga Sekolah',
    ];

    public const NON_STUDENT_KEYWORDS = [
        'guru',
        'pendidik',
        'staf',
        'staff',
        'karyawan',
        'tu',
        'tata usaha',
        'kantin',
        'warga sekolah',
        'umum',
        'non-siswa',
        'non siswa',
        'pegawai',
        'satpam',
        'keamanan',
        'kebersihan',
        'koperasi',
    ];

    protected $hidden = [
        'security_pin',
        'password',
    ];

    protected $fillable = [
        'account_number',
        'student_id',
        'student_name',
        'grade_class',
        'category',
        'gender',
        'username',
        'email',
        'whatsapp_number',
        'security_pin',
        'password',
        'status',
        'saldo',
    ];

    /**
     * Menentukan apakah nasabah merupakan Non-Siswa (Guru, Staf, Ibu Kantin, Warga Sekolah).
     */
    public function isNonStudent(): bool
    {
        $cat = strtolower(trim((string) ($this->category ?? '')));
        if (in_array($cat, ['teacher', 'staff', 'community', 'non_student', 'guru', 'staf', 'warga'], true)) {
            return true;
        }

        return self::isNonStudentGrade($this->grade_class)
            || preg_match('/^(GR|ST|WS|NS)-/i', (string) $this->student_id) === 1;
    }

    /**
     * Cek apakah nama kelas/unit/jabatan merupakan kategori non-siswa.
     */
    public static function isNonStudentGrade(?string $gradeClass): bool
    {
        $clean = strtolower(trim((string) $gradeClass));
        if ($clean === '') {
            return false;
        }

        foreach (self::NON_STUDENT_KEYWORDS as $kw) {
            if (str_contains($clean, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Label kategori nasabah untuk tampilan UI.
     */
    public function getCategoryLabelAttribute(): string
    {
        $cat = strtolower(trim((string) ($this->category ?? '')));
        if (isset(self::CATEGORIES[$cat])) {
            return self::CATEGORIES[$cat];
        }

        $grade = strtolower(trim((string) $this->grade_class));
        if (str_contains($grade, 'guru') || str_contains($grade, 'pendidik')) {
            return self::CATEGORIES['teacher'];
        }
        if (str_contains($grade, 'staf') || str_contains($grade, 'karyawan') || str_contains($grade, 'tu')) {
            return self::CATEGORIES['staff'];
        }
        if ($this->isNonStudent()) {
            return self::CATEGORIES['community'];
        }

        return self::CATEGORIES['student'];
    }

    /**
     * Default label Kelas/Unit/Jabatan untuk nasabah non-siswa.
     */
    public static function defaultGradeForCategory(string $category, ?string $customGrade = null): string
    {
        $trimmed = trim((string) $customGrade);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return match (strtolower(trim($category))) {
            'teacher', 'guru'     => 'Guru / Pendidik',
            'staff', 'staf'       => 'Staf & Karyawan',
            'community', 'warga'  => 'Warga Sekolah / Kantin',
            default               => '-',
        };
    }

    /**
     * Generate kode identitas unik otomatis untuk nasabah non-siswa jika NIP/NIK tidak diisi.
     */
    public static function generateNonStudentId(string $category = 'community'): string
    {
        $prefix = match (strtolower(trim($category))) {
            'teacher', 'guru'    => 'GR',
            'staff', 'staf'      => 'ST',
            'community', 'warga' => 'WS',
            default              => 'NS',
        };

        $year = date('Y');
        $seq = 1;
        do {
            $candidate = sprintf('%s-%s%03d', $prefix, $year, $seq);
            $existsInSavers = self::withTrashed()->where('student_id', $candidate)->exists();
            $existsInMembers = class_exists(Member::class)
                && \Illuminate\Support\Facades\Schema::hasTable('members')
                && Member::where('student_id', $candidate)->exists();
            $seq++;
        } while ($existsInSavers || $existsInMembers);

        return $candidate;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'saver_id');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'saver_id');
    }

    public function getNameAttribute()
    {
        return $this->student_name;
    }

    public function getUserIdAttribute()
    {
        return $this->id;
    }
}