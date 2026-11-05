<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Umum Akuntansi - Bank Mini</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 12px;
            color: #1e3a8a;
        }
        .header p {
            margin: 2px 0;
            font-size: 9px;
            color: #64748b;
        }
        .info-bar {
            margin-bottom: 12px;
            font-size: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }
        .signature-space { height: 60px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>BANK MINI SMK NEGERI 1 BANTUL</h2>
        <h3>BUKU JURNAL UMUM AKUNTANSI (DOUBLE-ENTRY GENERAL LEDGER)</h3>
        <p>Jl. Parangtritis No.KM.11, Sabdodadi, Kec. Bantul, Kabupaten Bantul, D.I. Yogyakarta</p>
    </div>

    <div class="info-bar">
        <table width="100%">
            <tr>
                <td><strong>Periode Jurnal:</strong> {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
                <td class="text-right"><strong>Status Keseimbangan Neraca:</strong> 
                    @if($isBalanced)
                        <span class="badge badge-success">BALANCE (SEIMBANG)</span>
                    @else
                        <span class="badge badge-danger">TIDAK BALANCE</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="14%">Tanggal &amp; Waktu</th>
                <th width="12%">No. Transaksi</th>
                <th width="8%">Kode Akun</th>
                <th width="28%">Nama Akun &amp; Keterangan</th>
                <th width="17%" class="text-right">Debet (Rp)</th>
                <th width="17%" class="text-right">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $index => $j)
                @php
                    $isDebit = ($j->position === 'Debit');
                    $accName = $accountNames[$j->account_code] ?? 'Transaksi Operasional';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($j->created_at)->format('d/m/Y H:i') }}</td>
                    <td>TRX-#{{ str_pad($j->transaction_id ?? $j->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-center font-monospace fw-bold">{{ $j->account_code }}</td>
                    <td style="{{ $isDebit ? '' : 'padding-left: 20px;' }}">
                        {{ $accName }}
                        @if($j->student_name)
                            <div style="font-size: 8px; color: #64748b;">Nasabah: {{ $j->student_name }}</div>
                        @endif
                    </td>
                    <td class="text-right fw-bold">
                        {{ $isDebit ? 'Rp ' . number_format($j->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right fw-bold">
                        {{ !$isDebit ? 'Rp ' . number_format($j->amount, 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Tidak ada data transaksi jurnal akuntansi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($journals->count() > 0)
            <tfoot>
                <tr class="fw-bold" style="background-color: #f1f5f9;">
                    <td colspan="5" class="text-right">TOTAL NERACA JURNAL UMUM:</td>
                    <td class="text-right" style="color: #1e3a8a;">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #15803d;">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Administrator Sistem Akuntansi,</strong>
                <div class="signature-space"></div>
                <strong><u>{{ auth()->user()->officer_name ?? auth()->user()->username ?? 'Administrator' }}</u></strong><br>
                <span>NIP/ID: {{ auth()->user()->username ?? '-' }}</span>
            </td>
            <td>
                Bantul, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Kepala Bank Mini SMK Negeri 1 Bantul,</strong>
                <div class="signature-space"></div>
                <strong><u>Drs. H. Pimpinan Sekolah</u></strong><br>
                <span>Pengelola Resmi Perbankan Sekolah</span>
            </td>
        </tr>
    </table>

</body>
</html>

