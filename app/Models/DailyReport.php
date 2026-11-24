<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    public const DENOMINATIONS = [100000, 50000, 20000, 10000, 5000, 2000, 1000, 500];

    protected $fillable = [
        'teller_id',
        'report_date',
        'opening_balance',
        'total_deposits',
        'total_withdrawals',
        'closing_balance_system',
        'closing_balance_physical',
        'cash_denominations',
        'status',
        'admin_id',
        'approval_at',
        'notes',
    ];

    protected $casts = [
        'cash_denominations' => 'array',
    ];

    /**
     * Normalisasi input rincian pecahan uang fisik di laci kasir (500 s/d 100.000).
     */
    public static function normalizeDenominations($rawDenominations): array
    {
        $counts = [];
        $total = 0;
        $hasAny = false;
        $parts = [];

        $input = is_array($rawDenominations) ? $rawDenominations : [];

        foreach (self::DENOMINATIONS as $denom) {
            $key = (string) $denom;
            $rawVal = $input[$key] ?? ($input[$denom] ?? null);
            if ($rawVal !== null && $rawVal !== '') {
                $hasAny = true;
            }
            $qty = max(0, (int) $rawVal);
            $counts[$key] = $qty;
            $subtotal = $denom * $qty;
            $total += $subtotal;

            if ($qty > 0) {
                $parts[] = 'Rp ' . number_format($denom, 0, ',', '.') . ' x ' . $qty . ' = Rp ' . number_format($subtotal, 0, ',', '.');
            }
        }

        return [
            'counts'       => $counts,
            'total'        => $total,
            'has_any'      => $hasAny,
            'non_zero'     => array_filter($counts, fn ($q) => $q > 0),
            'summary_text' => count($parts) > 0
                ? ('Pecahan Fisik: ' . implode(', ', $parts))
                : '',
        ];
    }

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
