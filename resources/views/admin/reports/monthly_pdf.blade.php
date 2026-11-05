<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Bulanan - Bank Mini Sekolah</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #16233F;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            color: #16233F;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
            color: #555;
        }
        .summary-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            text-align: center;
        }
        .summary-title {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: #0F172A;
            margin-top: 3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th, .data-table td {
            border: 1px solid #CBD5E1;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #16233F;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #F8FAFC;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #15803D; }
        .text-danger { color: #B91C1C; }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border: none;
        }
        .signature-table td {
            border: none;
            text-align: center;
            width: 50%;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>BANK MINI SEKOLAH</h2>
        <p>Sistem Informasi E-Teller Perbankan Sekolah Terpadu</p>
        <p><strong>LAPORAN REKAPITULASI TRANSAKSI BULANAN</strong></p>
        <p>Periode: Bulan {{ $monthName }} {{ $year }}</p>
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-title">Total Setoran (Cash-In)</div>
                <div class="summary-value text-success">+ Rp {{ number_format($totalDeposits, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-title">Total Penarikan (Cash-Out)</div>
                <div class="summary-value text-danger">- Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-title">Net Pertumbuhan Tabungan</div>
                <div class="summary-value">Rp {{ number_format($netSavings, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-title">Pemindahan Kas ke Brankas</div>
                <div class="summary-value">Rp {{ number_format($totalVault, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 110px;">Waktu Transaksi</th>
                <th style="width: 80px;">No. Rekening</th>
                <th>Nama Nasabah</th>
                <th style="width: 80px;">Jenis</th>
                <th style="width: 90px;" class="text-end">Nominal</th>
                <th style="width: 100px;">Petugas/Teller</th>
                <th style="width: 60px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y H:i') }}</td>
                <td>{{ $trx->saver ? $trx->saver->account_number : 'BRANKAS' }}</td>
                <td>{{ $trx->saver ? $trx->saver->student_name : 'Kas Pusat Bank Mini' }}</td>
                <td>
                    @if($trx->transaction_type === 'Deposit')
                        Setoran
                    @elseif($trx->transaction_type === 'Withdrawal')
                        Penarikan
                    @else
                        Vault Transfer
                    @endif
                </td>
                <td class="text-end {{ $trx->transaction_type === 'Deposit' ? 'text-success' : ($trx->transaction_type === 'Withdrawal' ? 'text-danger' : '') }}">
                    Rp {{ number_format($trx->amount, 0, ',', '.') }}
                </td>
                <td>{{ $trx->user->officer_name ?? $trx->user->username ?? '-' }}</td>
                <td class="text-center">{{ $trx->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 15px; color: #888;">Tidak ada catatan transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    <br><br>
                    Petugas Teller Pelaksana,
                    <br><br><br><br><br>
                    ( _____________________________ )
                </td>
                <td>
                    Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}<br>
                    Mengetahui,<br>
                    <strong>Administrator / Pimpinan Bank Mini</strong>
                    <br><br><br><br>
                    ( _____________________________ )
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
