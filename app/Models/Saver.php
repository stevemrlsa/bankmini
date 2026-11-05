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
    protected $hidden = [
        'security_pin',
        'password',
    ];

    protected $fillable = [
        'account_number',
        'student_id',
        'student_name',
        'grade_class',
        'gender',
        'username',
        'email',
        'whatsapp_number',
        'security_pin',
        'password',
        'status',
        'saldo',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'saver_id');
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