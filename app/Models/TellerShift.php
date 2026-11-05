<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TellerShift extends Model
{
    use HasFactory;

    protected $table = 'teller_shifts';

    protected $fillable = [
        'user_id',
        'opening_balance',
        'closing_balance',
        'system_balance',
        'difference',
        'notes',
        'status',
        'approved_by',
        'opened_at',
        'closed_at',
        'approved_at',
    ];

    protected $casts = [
        'opening_balance' => 'integer',
        'closing_balance' => 'integer',
        'system_balance'  => 'integer',
        'difference'      => 'integer',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
        'approved_at'     => 'datetime',
    ];

    /**
     * Relasi ke model User (Pegawai / Teller).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke model User (Admin yang menyetujui shift).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
