<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawais';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'no_telp',
        'alamat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
