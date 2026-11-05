<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'teller_id',
        'report_date',
        'opening_balance',
        'total_deposits',
        'total_withdrawals',
        'closing_balance_system',
        'closing_balance_physical',
        'status',
        'admin_id',
        'approval_at',
        'notes',
    ];

    /**
     * Relasi ke pembuat laporan (Teller)
     */
    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    /**
     * Relasi ke pemeriksa laporan (Administrator)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
