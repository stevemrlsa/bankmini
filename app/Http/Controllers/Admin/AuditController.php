<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditController extends Controller
{
    public function index()
    {
        $journals = DB::table('accounting_journals')->orderBy('created_at', 'desc')->get();

        $totalDebet = DB::table('accounting_journals')->where('position', 'Debit')->sum('amount');
        $totalKredit = DB::table('accounting_journals')->where('position', 'Credit')->sum('amount');

        $isBalance = ($totalDebet === $totalKredit);

        if (!$isBalance) {
            Log::warning('Anomali Jurnal Terdeteksi: Debet dan Kredit tidak balance!');
        }

        return view('admin.audit.index', compact('journals', 'totalDebet', 'totalKredit', 'isBalance'));
    }
}
