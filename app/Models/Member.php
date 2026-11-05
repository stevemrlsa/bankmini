<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'student_id',
        'account_number',
        'name',
        'grade_class',
        'graduation_year',
        'security_pin',
        'status',
    ];

    public function getClassDisplayNameAttribute()
    {
        return $this->grade_class ?: ($this->graduation_year ? ('Angkatan ' . $this->graduation_year) : '-');
    }

    // Alias for student_name compatibility
    public function getStudentNameAttribute()
    {
        return $this->attributes['name'] ?? null;
    }

    public function setStudentNameAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
