<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TellerShift;
use App\Services\MutationReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MutationReportController extends Controller
{
    public function __construct(
        private readonly MutationReportService $reportService
    ) {}

    /**
     * Menampilkan halaman laporan mutasi nasabah yang ditangani oleh Teller.
     */
    public function index(Request $request)
    {
        $context = $this->resolveFilterContext($request);
        $tsExpr = $this->reportService->effectiveTimestampExpression();

        $transactions = (clone $context['query'])
            ->orderByRaw("{$tsExpr} desc, id desc")
            ->paginate(20)
            ->withQueryString();

        return view('teller.reports.mutations', array_merge($context, [
            'transactions' => $transactions,
        ]));
    }

    /**
     * Mencetak / mengunduh laporan mutasi nasabah oleh Teller dalam format PDF resmi.
     */
    public function exportPdf(Request $request)
    {
        $context = $this->resolveFilterContext($request);
        $tsExpr = $this->reportService->effectiveTimestampExpression();

        $transactions = (clone $context['query'])
            ->orderByRaw("{$tsExpr} asc, id asc")
            ->get();

        $teller = $context['teller'];
        $tellerDisplay = $teller->officer_name ?: $teller->username;
        $scopeLabel = $context['scope'] === 'shift'
            ? 'Shift Bersama (Saya & Rekan Shift)'
            : 'Hanya Transaksi Saya';

        AuditLog::record(
            'EXPORT_PDF',
            'Laporan Mutasi Teller',
            "Mencetak PDF mutasi nasabah periode {$context['periodData']['label']} ({$context['typeLabel']}, Cakupan: {$scopeLabel}, {$context['summary']['total_count']} transaksi).",
            $teller->id,
            $tellerDisplay
        );

        $printedAt = Carbon::now('Asia/Jakarta');

        $pdf = Pdf::loadView('teller.reports.mutations_pdf', [
            'teller'              => $teller,
            'tellerDisplay'       => $tellerDisplay,
            'periodData'          => $context['periodData'],
            'normalizedType'      => $context['normalizedType'],
            'typeLabel'           => $context['typeLabel'],
            'scope'               => $context['scope'],
            'scopeLabel'          => $scopeLabel,
            'participatingTellers'=> $context['participatingTellers'],
            'search'              => $context['search'],
            'transactions'        => $transactions,
            'summary'             => $context['summary'],
            'printedAt'           => $printedAt,
        ])->setPaper('a4', 'landscape');

        $safeUser = Str::slug($teller->username ?: $tellerDisplay, '_');
        $safePeriod = Str::slug($context['periodData']['period'] . '_' . ($context['periodData']['start_date'] ?: $printedAt->toDateString()), '_');
        $filename = "Laporan_Mutasi_Teller_{$safeUser}_{$safePeriod}.pdf";

        if ($request->query('mode') === 'download' || $request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Mengumpulkan parameter filter, query mutasi, dan ringkasan statistik secara terpusat.
     */
    private function resolveFilterContext(Request $request): array
    {
        $teller = Auth::user();
        $periodData = $this->reportService->resolveTellerPeriod(
            $request->query('period', 'today'),
            $request->query('start_date'),
            $request->query('end_date')
        );
        $normalizedType = $this->reportService->normalizeType($request->query('type', 'all'));
        $typeLabel = $this->reportService->getTypeLabel($normalizedType);
        $scope = $request->query('scope', 'mine');
        $search = $request->query('search');

        $built = $this->reportService->buildTellerMutationData(
            $teller->id,
            $periodData,
            $normalizedType,
            $scope,
            $search
        );

        $summary = $this->reportService->summarizeQuery($built['query']);
        $activeShift = TellerShift::getActiveOpenShift();

        return [
            'teller'               => $teller,
            'periodData'           => $periodData,
            'normalizedType'       => $normalizedType,
            'typeLabel'            => $typeLabel,
            'scope'                => $built['scope'],
            'search'               => $search,
            'query'                => $built['query'],
            'summary'              => $summary,
            'matchedShiftsCount'   => $built['matched_shifts_count'],
            'participatingTellers' => $built['participating_tellers'],
            'activeShift'          => $activeShift,
        ];
    }
}

