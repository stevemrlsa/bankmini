<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $table = 'withdrawal_requests';

    protected $fillable = [
        'request_code',
        'saver_id',
        'amount',
        'scheduled_date',
        'purpose',
        'notes',
        'status',
        'teller_approved_by',
        'teller_approved_at',
        'admin_approved_by',
        'admin_approved_at',
        'rejection_reason',
        'transaction_id',
        'disbursed_by',
        'disbursed_at',
        'created_by',
    ];

    protected $casts = [
        'amount'             => 'float',
        'scheduled_date'     => 'date',
        'teller_approved_at' => 'datetime',
        'admin_approved_at'  => 'datetime',
        'disbursed_at'       => 'datetime',
    ];

    public function saver()
    {
        return $this->belongsTo(Saver::class, 'saver_id')->withTrashed();
    }

    public function tellerApprover()
    {
        return $this->belongsTo(User::class, 'teller_approved_by');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function disbursedBy()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}

