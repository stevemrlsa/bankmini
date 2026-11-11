<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekening Koran Nasabah - {{ $saver->student_name }}</title>
    @include('partials.mutation_pdf_styles')
</head>
<body>

    <div class="header">
        <h2>BANK MINI SMKN 1 BANTUL</h2>
        <p>Sistem Informasi E-Teller Perbankan Sekolah Terpadu</p>
        <p class="doc-title">LAPORAN MUTASI REKENING NASABAH (REKENING KORAN)</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Nasabah</td>
            <td class="meta-val"><strong>{{ $saver->student_name }}</strong></td>
            <td class="meta-label">No. Rekening</td>
            <td class="meta-val"><strong>{{ $saver->account_number ?? 'Belum Ada Rekening' }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">NIS Siswa</td>
            <td class="meta-val">{{ $saver->student_id }}</td>
            <td class="meta-label">Status Rekening</td>
            <td class="meta-val">
                @if($saver->trashed())
                    Diarsipkan (Nonaktif)
                @elseif($saver->status === 'blocked')
                    Diblokir
                @elseif(in_array(strtolower((string) $saver->status), ['active', 'aktif'], true))
                    Aktif
                @else
                    {{ ucfirst((string) ($saver->status ?? 'Aktif')) }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="meta-label">Kelas / Angkatan</td>
            <td class="meta-val">{{ $saver->grade_class ?? '-' }}</td>
            <td class="meta-label">Periode Mutasi</td>
            <td class="meta-val"><strong>{{ $periodData['label'] }}</strong> ({{ $typeLabel }})</td>
        </tr>
        <tr>
            <td class="meta-label">Saldo Terkini</td>
            <td class="meta-val"><strong class="text-success">Rp {{ number_format($saver->saldo ?? 0, 0, ',', '.') }}</strong></td>
            <td class="meta-label">Dicetak Oleh</td>
            <td class="meta-val">{{ $adminName }} — {{ $printedAt->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            @if($showRunningBalance)
                <td style="width: 19%;">
                    <div class="summary-title">Saldo Awal Periode</div>
                    <div class="summary-value">Rp {{ number_format($openingBalance, 0, ',', '.') }}</div>
                </td>
            @endif
            <td style="width: {{ $showRunningBalance ? '17%' : '25%' }};">
                <div class="summary-title">Frekuensi Mutasi</div>
                <div class="summary-value">{{ number_format($summary['total_count'], 0, ',', '.') }} Trx</div>
            </td>
            <td style="width: {{ $showRunningBalance ? '22%' : '25%' }};">
                <div class="summary-title">Total Kredit (Setoran)</div>
                <div class="summary-value text-success">+ Rp {{ number_format($summary['total_deposits'], 0, ',', '.') }}</div>
            </td>
            <td style="width: {{ $showRunningBalance ? '22%' : '25%' }};">
                <div class="summary-title">Total Debet (Penarikan)</div>
                <div class="summary-value text-danger">- Rp {{ number_format($summary['total_withdrawals'], 0, ',', '.') }}</div>
            </td>
            @if($showRunningBalance)
                <td style="width: 20%;">
                    <div class="summary-title">Saldo Akhir Periode</div>
                    <div class="summary-value text-success">Rp {{ number_format($closingBalance, 0, ',', '.') }}</div>
                </td>
            @else
                <td style="width: 25%;">
                    <div class="summary-title">Selisih Mutasi Filter</div>
                    <div class="summary-value">Rp {{ number_format(abs($summary['net_change']), 0, ',', '.') }}</div>
                </td>
            @endif
        </tr>
    </table>

    @if(!$showRunningBalance)
        <div class="note-box">
            <strong>Catatan:</strong> Filter jenis mutasi spesifik (<em>{{ $typeLabel }}</em>) sedang aktif, sehingga kolom Saldo Berjalan tidak ditampilkan agar tidak membingungkan secara akuntansi. Pilih <em>Semua Mutasi</em> untuk melihat kolom Saldo Berjalan Rekening Koran lengkap.
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 26px;" class="text-center">No</th>
                <th style="width: 98px;">Tanggal &amp; Waktu</th>
                <th style="width: 68px;">Kode Trx</th>
                <th>Keterangan / Jenis Mutasi</th>
                <th style="width: 105px;">Petugas Teller</th>
                <th style="width: 95px;" class="text-end">Debet (Keluar)</th>
                <th style="width: 95px;" class="text-end">Kredit (Masuk)</th>
                @if($showRunningBalance)
                    <th style="width: 105px;" class="text-end">Saldo Berjalan</th>
                @endif
                <th style="width: 58px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @if($showRunningBalance && ($periodData['period'] !== 'all' || abs($openingBalance) > 0.001))
                <tr class="opening-row">
                    <td class="text-center">-</td>
                    <td>{{ $periodData['start_at'] ? $periodData['start_at']->format('d/m/Y H:i') : 'Awal' }}</td>
                    <td>-</td>
                    <td colspan="2">SALDO AWAL PERIODE MUTASI</td>
                    <td class="text-end">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end">Rp {{ number_format($openingBalance, 0, ',', '.') }}</td>
                    <td class="text-center">-</td>
                </tr>
            @endif

            @forelse($transactions as $index => $trx)
                @php
                    $isDep = \App\Services\MutationReportService::isDeposit($trx->transaction_type);
                    $txTime = \Carbon\Carbon::parse($trx->transaction_date ?? $trx->created_at)->timezone('Asia/Jakarta');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $txTime->format('d/m/Y H:i') }} WIB</td>
                    <td class="fw-bold">#TRX-{{ $trx->id }}</td>
                    <td>
                        <span class="badge {{ $isDep ? 'badge-deposit' : 'badge-withdrawal' }}">
                            {{ $isDep ? 'Setoran Tunai' : 'Penarikan Tunai' }}
                        </span>
                    </td>
                    <td>{{ $trx->user->officer_name ?? $trx->user->username ?? '-' }}</td>
                    <td class="text-end {{ !$isDep ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ !$isDep ? 'Rp ' . number_format($trx->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-end {{ $isDep ? 'text-success fw-bold' : 'text-muted' }}">
                        {{ $isDep ? 'Rp ' . number_format($trx->amount, 0, ',', '.') : '-' }}
                    </td>
                    @if($showRunningBalance)
                        <td class="text-end fw-bold">
                            Rp {{ number_format($trx->running_balance ?? 0, 0, ',', '.') }}
                        </td>
                    @endif
                    <td class="text-center">
                        <span class="badge badge-status">{{ $trx->status ?? 'Approved' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showRunningBalance ? 9 : 8 }}" class="text-center text-muted" style="padding: 18px;">
                        Tidak ada catatan mutasi transaksi nasabah pada periode dan filter yang dipilih.
                    </td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="5" class="text-end">TOTAL MUTASI PERIODE INI</td>
                <td class="text-end text-danger">Rp {{ number_format($summary['total_withdrawals'], 0, ',', '.') }}</td>
                <td class="text-end text-success">Rp {{ number_format($summary['total_deposits'], 0, ',', '.') }}</td>
                @if($showRunningBalance)
                    <td class="text-end">Rp {{ number_format($closingBalance, 0, ',', '.') }}</td>
                @endif
                <td class="text-center">-</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    Nasabah / Pemilik Rekening,<br>
                    <strong>Siswa SMKN 1 Bantul</strong>
                    <br><br><br><br>
                    <strong>( {{ $saver->student_name }} )</strong>
                </td>
                <td>
                    Bantul, {{ $printedAt->translatedFormat('d F Y') }}<br>
                    <strong>Administrator Bank Mini SMKN 1 Bantul</strong>
                    <br><br><br><br>
                    <strong>( {{ $adminName }} )</strong>
                </td>
            </tr>
        </table>

        <div class="system-note">
            Dokumen Rekening Koran ini dicetak secara sah melalui Sistem Informasi Bank Mini SMKN 1 Bantul pada {{ $printedAt->translatedFormat('d F Y H:i:s') }} WIB.
        </div>
    </div>

</body>
</html>

