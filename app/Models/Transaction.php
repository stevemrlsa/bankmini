<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'saver_id',
        'user_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'status',
    ];

    public function saver()
    {
        return $this->belongsTo(Saver::class, 'saver_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accountingJournals()
    {
        return $this->hasMany(AccountingJournal::class, 'transaction_id');
    }
}