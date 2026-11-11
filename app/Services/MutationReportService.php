<?php

namespace App\Services;

use App\Models\TellerShift;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MutationReportService
{
    public const DEPOSIT_TYPES = ['Deposit', 'deposit', 'setor', 'setoran'];
    public const WITHDRAWAL_TYPES = ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'];
    public const EXCLUDED_STATUSES = ['Failed', 'Rejected', 'Cancelled'];

    /**
     * Ekspresi timestamp efektif transaksi agar konsisten di MySQL & SQLite.
     */
    public function effectiveTimestampExpression(): string
    {
        return 'COALESCE(transaction_date, created_at)';
    }

    /**
     * Menentukan apakah tipe transaksi merupakan setoran (kredit tabungan siswa).
     */
    public static function isDeposit(?string $type): bool
    {
        return in_array(strtolower(trim((string) $type)), ['deposit', 'setor', 'setoran'], true);
    }

    /**
     * Menentukan apakah tipe transaksi merupakan penarikan (debet tabungan siswa).
     */
    public static function isWithdrawal(?string $type): bool
    {
        return in_array(strtolower(trim((string) $type)), ['withdrawal', 'tarik', 'penarikan'], true);
    }

    /**
     * Menormalisasi filter jenis transaksi menjadi: all, deposit, atau withdrawal.
     */
    public function normalizeType(?string $type): string
    {
        $clean = strtolower(trim((string) $type));
        if ($clean === 'deposit') {
            return 'deposit';
        }
        if ($clean === 'withdrawal') {
            return 'withdrawal';
        }

        return 'all';
    }

    /**
     * Label human-readable untuk jenis mutasi pada tampilan web & PDF.
     */
    public function getTypeLabel(string $normalizedType): string
    {
        return match ($normalizedType) {
            'deposit'    => 'Setoran Tunai (Deposit)',
            'withdrawal' => 'Penarikan Tunai (Withdrawal)',
            default      => 'Semua Mutasi (Setoran & Penarikan)',
        };
    }

    /**
     * Resolusi periode preset untuk laporan mutasi Teller (Hari Ini, Kemarin, Bulan Ini, Kustom).
     */
    public function resolveTellerPeriod(?string $period, ?string $startDate, ?string $endDate): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $normalizedPeriod = in_array($period, ['today', 'yesterday', 'this_month', 'custom'], true)
            ? $period
            : 'today';

        if ($normalizedPeriod === 'yesterday') {
            $day = $now->copy()->subDay();

            return [
                'period'     => 'yesterday',
                'label'      => 'Kemarin (' . $day->translatedFormat('d F Y') . ')',
                'start_date' => $day->toDateString(),
                'end_date'   => $day->toDateString(),
                'start_at'   => $day->copy()->startOfDay(),
                'end_at'     => $day->copy()->endOfDay(),
            ];
        }

        if ($normalizedPeriod === 'this_month') {
            return [
                'period'     => 'this_month',
                'label'      => 'Bulan Ini (' . $now->translatedFormat('F Y') . ')',
                'start_date' => $now->copy()->startOfMonth()->toDateString(),
                'end_date'   => $now->copy()->endOfMonth()->toDateString(),
                'start_at'   => $now->copy()->startOfMonth()->startOfDay(),
                'end_at'     => $now->copy()->endOfMonth()->endOfDay(),
            ];
        }

        if ($normalizedPeriod === 'custom') {
            $parsedStart = $this->parseDateSafe($startDate);
            $parsedEnd = $this->parseDateSafe($endDate);

            if (!$parsedStart && !$parsedEnd) {
                $parsedStart = $now->copy();
                $parsedEnd = $now->copy();
            } elseif ($parsedStart && !$parsedEnd) {
                $parsedEnd = $parsedStart->copy();
            } elseif (!$parsedStart && $parsedEnd) {
                $parsedStart = $parsedEnd->copy();
            }

            if ($parsedStart->gt($parsedEnd)) {
                [$parsedStart, $parsedEnd] = [$parsedEnd, $parsedStart];
            }

            $label = $parsedStart->isSameDay($parsedEnd)
                ? $parsedStart->translatedFormat('d F Y')
                : $parsedStart->translatedFormat('d M Y') . ' s/d ' . $parsedEnd->translatedFormat('d M Y');

            return [
                'period'     => 'custom',
                'label'      => $label,
                'start_date' => $parsedStart->toDateString(),
                'end_date'   => $parsedEnd->toDateString(),
                'start_at'   => $parsedStart->copy()->startOfDay(),
                'end_at'     => $parsedEnd->copy()->endOfDay(),
            ];
        }

        return [
            'period'     => 'today',
            'label'      => 'Hari Ini (' . $now->translatedFormat('d F Y') . ')',
            'start_date' => $now->toDateString(),
            'end_date'   => $now->toDateString(),
            'start_at'   => $now->copy()->startOfDay(),
            'end_at'     => $now->copy()->endOfDay(),
        ];
    }

    /**
     * Resolusi periode preset untuk laporan mutasi per nasabah oleh Admin.
     */
    public function resolveCustomerPeriod(?string $period, ?string $startDate, ?string $endDate): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $normalizedPeriod = in_array($period, ['all', 'this_month', 'last_3_months', 'custom'], true)
            ? $period
            : 'all';

        if ($normalizedPeriod === 'this_month') {
            return [
                'period'     => 'this_month',
                'label'      => 'Bulan Ini (' . $now->translatedFormat('F Y') . ')',
                'start_date' => $now->copy()->startOfMonth()->toDateString(),
                'end_date'   => $now->copy()->endOfMonth()->toDateString(),
                'start_at'   => $now->copy()->startOfMonth()->startOfDay(),
                'end_at'     => $now->copy()->endOfMonth()->endOfDay(),
            ];
        }

        if ($normalizedPeriod === 'last_3_months') {
            $start = $now->copy()->subMonthsNoOverflow(2)->startOfMonth()->startOfDay();
            $end = $now->copy()->endOfMonth()->endOfDay();

            return [
                'period'     => 'last_3_months',
                'label'      => '3 Bulan Terakhir (' . $start->translatedFormat('M Y') . ' – ' . $now->translatedFormat('M Y') . ')',
                'start_date' => $start->toDateString(),
                'end_date'   => $end->toDateString(),
                'start_at'   => $start,
                'end_at'     => $end,
            ];
        }

        if ($normalizedPeriod === 'custom') {
            $parsedStart = $this->parseDateSafe($startDate);
            $parsedEnd = $this->parseDateSafe($endDate);

            if ($parsedStart || $parsedEnd) {
                $parsedStart = $parsedStart ?? $parsedEnd->copy();
                $parsedEnd = $parsedEnd ?? $parsedStart->copy();

                if ($parsedStart->gt($parsedEnd)) {
                    [$parsedStart, $parsedEnd] = [$parsedEnd, $parsedStart];
                }

                $label = $parsedStart->isSameDay($parsedEnd)
                    ? $parsedStart->translatedFormat('d F Y')
                    : $parsedStart->translatedFormat('d M Y') . ' s/d ' . $parsedEnd->translatedFormat('d M Y');

                return [
                    'period'     => 'custom',
                    'label'      => $label,
                    'start_date' => $parsedStart->toDateString(),
                    'end_date'   => $parsedEnd->toDateString(),
                    'start_at'   => $parsedStart->copy()->startOfDay(),
                    'end_at'     => $parsedEnd->copy()->endOfDay(),
                ];
            }
        }

        return [
            'period'     => 'all',
            'label'      => 'Semua Riwayat Transaksi',
            'start_date' => '',
            'end_date'   => '',
            'start_at'   => null,
            'end_at'     => null,
        ];
    }

    /**
     * Membangun query mutasi nasabah untuk Teller lengkap dengan dukungan Shared Shift.
     */
    public function buildTellerMutationData(
        int $tellerId,
        array $periodData,
        string $normalizedType,
        string $scope = 'mine',
        ?string $search = null
    ): array {
        $normalizedScope = ($scope === 'shift') ? 'shift' : 'mine';
        $tsExpr = $this->effectiveTimestampExpression();

        $query = Transaction::with(['saver', 'user']);
        $this->applyBaseCustomerMutationFilters($query, $normalizedType);
        $this->applyDateRange($query, $periodData['start_at'], $periodData['end_at']);

        $participatingTellers = collect();
        $matchedShiftsCount = 0;

        if ($normalizedScope === 'shift') {
            $matchedShifts = $this->findParticipatingShifts($tellerId, $periodData['start_at'], $periodData['end_at']);
            $matchedShiftsCount = $matchedShifts->count();

            $participantIds = collect([$tellerId]);
            foreach ($matchedShifts as $shift) {
                $participantIds = $participantIds->merge($shift->getParticipatingUserIds());
            }
            $participantIds = $participantIds->unique()->values()->all();
            $participatingTellers = User::whereIn('id', $participantIds)->get(['id', 'officer_name', 'username']);

            $query->where(function ($scopeQ) use ($tellerId, $matchedShifts, $tsExpr) {
                $scopeQ->where('user_id', $tellerId);

                foreach ($matchedShifts as $shift) {
                    $userIds = $shift->getParticipatingUserIds();
                    if (count($userIds) <= 1) {
                        continue;
                    }

                    $shiftStart = ($shift->opened_at ?? $shift->created_at)
                        ->timezone('Asia/Jakarta')
                        ->format('Y-m-d H:i:s');
                    $shiftEndCarbon = $shift->closed_at
                        ?? ($shift->status === 'Open' ? Carbon::now('Asia/Jakarta') : ($shift->updated_at ?? $shift->created_at));
                    $shiftEnd = $shiftEndCarbon
                        ->timezone('Asia/Jakarta')
                        ->format('Y-m-d H:i:s');

                    $scopeQ->orWhere(function ($shiftQuery) use ($userIds, $tsExpr, $shiftStart, $shiftEnd) {
                        $shiftQuery->whereIn('user_id', $userIds)
                            ->whereRaw("{$tsExpr} BETWEEN ? AND ?", [$shiftStart, $shiftEnd]);
                    });
                }
            });
        } else {
            $query->where('user_id', $tellerId);
        }

        $trimmedSearch = trim((string) $search);
        if ($trimmedSearch !== '') {
            $query->whereHas('saver', function ($saverQ) use ($trimmedSearch) {
                $saverQ->withTrashed()->where(function ($inner) use ($trimmedSearch) {
                    $inner->where('student_name', 'like', "%{$trimmedSearch}%")
                        ->orWhere('student_id', 'like', "%{$trimmedSearch}%")
                        ->orWhere('account_number', 'like', "%{$trimmedSearch}%");
                });
            });
        }

        return [
            'query'                 => $query,
            'scope'                 => $normalizedScope,
            'matched_shifts_count'  => $matchedShiftsCount,
            'participating_tellers' => $participatingTellers,
        ];
    }

    /**
     * Membangun query mutasi transaksi untuk satu nasabah tertentu (Admin).
     */
    public function buildCustomerMutationQuery(int $saverId, array $periodData, string $normalizedType): Builder
    {
        $query = Transaction::with('user')->where('saver_id', $saverId);
        $this->applyBaseCustomerMutationFilters($query, $normalizedType, false);
        $this->applyDateRange($query, $periodData['start_at'], $periodData['end_at']);

        return $query;
    }

    /**
     * Menghitung ringkasan agregat (jumlah transaksi, setoran, penarikan, selisih) via SQL.
     */
    public function summarizeQuery(Builder $query): array
    {
        $depositPlaceholders = implode(',', array_fill(0, count(self::DEPOSIT_TYPES), '?'));
        $withdrawPlaceholders = implode(',', array_fill(0, count(self::WITHDRAWAL_TYPES), '?'));
        $bindings = array_merge(self::DEPOSIT_TYPES, self::WITHDRAWAL_TYPES);

        $row = (clone $query)
            ->setEagerLoads([])
            ->reorder()
            ->toBase()
            ->selectRaw(
                "COUNT(*) as total_count,
                 COALESCE(SUM(CASE WHEN transaction_type IN ({$depositPlaceholders}) THEN amount ELSE 0 END), 0) as total_deposits,
                 COALESCE(SUM(CASE WHEN transaction_type IN ({$withdrawPlaceholders}) THEN amount ELSE 0 END), 0) as total_withdrawals",
                $bindings
            )
            ->first();

        $totalDeposits = (float) ($row->total_deposits ?? 0);
        $totalWithdrawals = (float) ($row->total_withdrawals ?? 0);

        return [
            'total_count'       => (int) ($row->total_count ?? 0),
            'total_deposits'    => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'net_change'        => $totalDeposits - $totalWithdrawals,
        ];
    }

    /**
     * Menghitung saldo awal periode agar saldo berjalan di PDF selalu sinkron dengan saldo rekening terkini.
     */
    public function calculateOpeningBalanceForCustomer(int $saverId, float $currentSaverSaldo, ?Carbon $startAt): float
    {
        $query = Transaction::where('saver_id', $saverId);
        $this->applyBaseCustomerMutationFilters($query, 'all', false);

        if ($startAt !== null) {
            $tsExpr = $this->effectiveTimestampExpression();
            $query->whereRaw("{$tsExpr} >= ?", [$startAt->format('Y-m-d H:i:s')]);
        }

        $summaryFromStartToNow = $this->summarizeQuery($query);

        return $currentSaverSaldo - $summaryFromStartToNow['net_change'];
    }

    /**
     * Menyisipkan saldo berjalan (running_balance) pada koleksi transaksi terurut kronologis ASC.
     */
    public function attachRunningBalance(Collection $transactions, float $openingBalance): array
    {
        $runningBalance = $openingBalance;

        foreach ($transactions as $trx) {
            if (self::isDeposit($trx->transaction_type)) {
                $runningBalance += (float) $trx->amount;
            } elseif (self::isWithdrawal($trx->transaction_type)) {
                $runningBalance -= (float) $trx->amount;
            }
            $trx->running_balance = $runningBalance;
        }

        return [
            'transactions'    => $transactions,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
        ];
    }

    /**
     * Menerapkan filter standar mutasi nasabah (exclude gagal/batal & mutasi internal brankas).
     */
    private function applyBaseCustomerMutationFilters(
        Builder $query,
        string $normalizedType,
        bool $requireSaverId = true
    ): void {
        if ($requireSaverId) {
            $query->whereNotNull('saver_id');
        }

        $query->where(function ($statusQ) {
            $statusQ->whereNull('status')
                ->orWhereNotIn('status', self::EXCLUDED_STATUSES);
        });

        if ($normalizedType === 'deposit') {
            $query->whereIn('transaction_type', self::DEPOSIT_TYPES);
        } elseif ($normalizedType === 'withdrawal') {
            $query->whereIn('transaction_type', self::WITHDRAWAL_TYPES);
        } else {
            $query->whereIn(
                'transaction_type',
                array_merge(self::DEPOSIT_TYPES, self::WITHDRAWAL_TYPES)
            );
        }
    }

    /**
     * Menerapkan batas waktu awal dan akhir ke query menggunakan timestamp efektif.
     */
    private function applyDateRange(Builder $query, ?Carbon $startAt, ?Carbon $endAt): void
    {
        $tsExpr = $this->effectiveTimestampExpression();

        if ($startAt && $endAt) {
            $query->whereRaw("{$tsExpr} BETWEEN ? AND ?", [
                $startAt->format('Y-m-d H:i:s'),
                $endAt->format('Y-m-d H:i:s'),
            ]);
        } elseif ($startAt) {
            $query->whereRaw("{$tsExpr} >= ?", [$startAt->format('Y-m-d H:i:s')]);
        } elseif ($endAt) {
            $query->whereRaw("{$tsExpr} <= ?", [$endAt->format('Y-m-d H:i:s')]);
        }
    }

    /**
     * Mencari daftar TellerShift dalam rentang periode yang diikuti oleh teller tertentu.
     */
    private function findParticipatingShifts(int $tellerId, Carbon $startAt, Carbon $endAt): Collection
    {
        $startStr = $startAt->format('Y-m-d H:i:s');
        $endStr = $endAt->format('Y-m-d H:i:s');

        $candidateShifts = TellerShift::whereRaw('COALESCE(opened_at, created_at) <= ?', [$endStr])
            ->where(function ($q) use ($startStr) {
                $q->whereNull('closed_at')
                    ->orWhere('closed_at', '>=', $startStr);
            })
            ->get();

        return $candidateShifts->filter(function (TellerShift $shift) use ($tellerId) {
            return in_array($tellerId, $shift->getParticipatingUserIds(), true);
        })->values();
    }

    /**
     * Parsing tanggal input secara aman tanpa melempar exception pada string korup.
     */
    private function parseDateSafe(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse(trim($value), 'Asia/Jakarta');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
