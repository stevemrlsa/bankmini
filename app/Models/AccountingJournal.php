<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{
    protected $table = 'accounting_journals';

    protected $fillable = [
        'transaction_id',
        'account_code',
        'position',
        'amount',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}