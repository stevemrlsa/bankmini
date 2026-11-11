<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mutasi Nasabah Teller - Bank Mini SMKN 1 Bantul</title>
    @include('partials.mutation_pdf_styles')
</head>
<body>

    <div class="header">
        <h2>BANK MINI SMKN 1 BANTUL</h2>
        <p>Sistem Informasi E-Teller Perbankan Sekolah Terpadu</p>
        <p class="doc-title">LAPORAN MUTASI TRANSAKSI NASABAH (PETUGAS TELLER)</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Petugas Teller</td>
            <td class="meta-val"><strong>{{ $tellerDisplay }}</strong> ({{ $teller->username }})</td>
            <td class="meta-label">Periode Laporan</td>
            <td class="meta-val"><strong>{{ $periodData['label'] }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Cakupan Data</td>
            <td class="meta-val">{{ $scopeLabel }}</td>
            <td class="meta-label">Filter Jenis</td>
            <td class="meta-val">{{ $typeLabel }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kata Kunci Cari</td>
            <td class="meta-val">{{ !empty(trim((string) $search)) ? $search : 'Semua Nasabah' }}</td>
            <td class="meta-label">Waktu Cetak</td>
            <td class="meta-val">{{ $printedAt->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
        @if($scope === 'shift' && $participatingTellers->count() > 1)
        <tr>
            <td class="meta-label">Rekan Shift</td>
            <td class="meta-val" colspan="3">
                {{ $participatingTellers->map(fn ($u) => $u->officer_name ?: $u->username)->implode(', ') }}
            </td>
        </tr>
        @endif
    </table>

    <table class="summary-table">
        <tr>
            <td style="width: 22%;">
                <div class="summary-title">Total Transaksi</div>
                <div class="summary-value">{{ number_format($summary['total_count'], 0, ',', '.') }} Mutasi</div>
            </td>
            <td style="width: 26%;">
                <div class="summary-title">Total Setoran Masuk (Cash-In)</div>
                <div class="summary-value text-success">+ Rp {{ number_format($summary['total_deposits'], 0, ',', '.') }}</div>
            </td>
            <td style="width: 26%;">
                <div class="summary-title">Total Penarikan Keluar (Cash-Out)</div>
                <div class="summary-value text-danger">- Rp {{ number_format($summary['total_withdrawals'], 0, ',', '.') }}</div>
            </td>
            <td style="width: 26%;">
                <div class="summary-title">Selisih Bersih Mutasi</div>
                <div class="summary-value {{ $summary['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $summary['net_change'] >= 0 ? '+ ' : '- ' }}Rp {{ number_format(abs($summary['net_change']), 0, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 26px;" class="text-center">No</th>
                <th style="width: 98px;">Waktu Transaksi</th>
                <th style="width: 66px;">Kode Trx</th>
                <th style="width: 82px;">No. Rekening</th>
                <th>Nama Nasabah</th>
                <th style="width: 72px;">Kelas</th>
                <th style="width: 76px;" class="text-center">Jenis Mutasi</th>
                <th style="width: 95px;" class="text-end">Nominal (Rp)</th>
                <th style="width: 95px;">Petugas Teller</th>
                <th style="width: 58px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
                @php
                    $isDep = \App\Services\MutationReportService::isDeposit($trx->transaction_type);
                    $txTime = \Carbon\Carbon::parse($trx->transaction_date ?? $trx->created_at)->timezone('Asia/Jakarta');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $txTime->format('d/m/Y H:i') }} WIB</td>
                    <td class="fw-bold">#TRX-{{ $trx->id }}</td>
                    <td>{{ $trx->saver->account_number ?? '-' }}</td>
                    <td>
                        <strong>{{ $trx->saver->student_name ?? 'Nasabah Terhapus' }}</strong>
                        @if($trx->saver?->student_id)
                            <br><span class="text-muted" style="font-size: 9px;">NIS: {{ $trx->saver->student_id }}</span>
                        @endif
                    </td>
                    <td>{{ $trx->saver->grade_class ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $isDep ? 'badge-deposit' : 'badge-withdrawal' }}">
                            {{ $isDep ? 'Setoran' : 'Penarikan' }}
                        </span>
                    </td>
                    <td class="text-end fw-bold {{ $isDep ? 'text-success' : 'text-danger' }}">
                        {{ $isDep ? '+ ' : '- ' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                    <td>{{ $trx->user->officer_name ?? $trx->user->username ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge badge-status">{{ $trx->status ?? 'Approved' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding: 18px;">
                        Tidak ada transaksi mutasi nasabah pada periode dan filter yang dipilih.
                    </td>
                </tr>
            @endforelse

            @if($transactions->isNotEmpty())
                <tr class="total-row">
                    <td colspan="7" class="text-end">REKAPITULASI TOTAL PERIODE ({{ $summary['total_count'] }} TRANSAKSI)</td>
                    <td class="text-end" colspan="3">
                        <span class="text-success">Setor: +Rp {{ number_format($summary['total_deposits'], 0, ',', '.') }}</span>
                        &nbsp;|&nbsp;
                        <span class="text-danger">Tarik: -Rp {{ number_format($summary['total_withdrawals'], 0, ',', '.') }}</span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    <strong>Administrator / Kepala Bank Mini</strong>
                    <br><br><br><br>
                    ( _________________________________ )
                </td>
                <td>
                    Bantul, {{ $printedAt->translatedFormat('d F Y') }}<br>
                    <strong>Petugas Teller Bertugas</strong>
                    <br><br><br><br>
                    <strong>( {{ $tellerDisplay }} )</strong>
                </td>
            </tr>
        </table>

        <div class="system-note">
            Dokumen ini dicetak secara resmi melalui Sistem Informasi Bank Mini SMKN 1 Bantul pada {{ $printedAt->translatedFormat('d F Y H:i:s') }} WIB.
        </div>
    </div>

</body>
</html>

