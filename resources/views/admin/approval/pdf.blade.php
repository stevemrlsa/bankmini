<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Rekonsiliasi & Penutupan Kas - Bank Mini</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .header h2 {
            margin: 0;
            font-size: 17px;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h3 {
            margin: 3px 0;
            font-size: 13px;
            color: #1e3a8a;
            font-weight: 600;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
            color: #64748b;
        }
        .meta-box {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }
        .meta-box td {
            padding: 5px 8px;
            font-size: 11px;
            vertical-align: top;
        }
        .meta-label {
            width: 25%;
            color: #64748b;
            font-weight: 600;
        }
        .meta-colon {
            width: 2%;
            text-align: center;
        }
        .meta-val {
            width: 73%;
            color: #0f172a;
            font-weight: 500;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 10px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .table-data th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .table-data td {
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }
        .signature-space {
            height: 65px;
        }
        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 10px;
            color: #334155;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="header">
        <h2>BANK MINI SMK NEGERI 1 BANTUL</h2>
        <h3>BERITA ACARA REKONSILIASI & PENUTUPAN KAS TELLER</h3>
        <p>Jl. Parangtritis No.KM.11, Sabdodadi, Kec. Bantul, Kabupaten Bantul, D.I. Yogyakarta</p>
        <p>Layanan Kas Terintegrasi & Perbankan Siswa (SimPel)</p>
    </div>

    <!-- INFORMASI UMUM LAPORAN -->
    <table class="meta-box">
        <tr>
            <td class="meta-label">Nomor Berita Acara</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">BA-KAS/{{ \Carbon\Carbon::parse($report->report_date)->format('Ymd') }}/#{{ str_pad($report->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Pelaporan Kas</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Petugas Teller</td>
            <td class="meta-colon">:</td>
            <td class="meta-val"><strong>{{ $report->teller->officer_name ?? $report->teller->username ?? 'Teller Loket' }}</strong> (Username: {{ $report->teller->username ?? '-' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Administrator Pemeriksa</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ $report->admin->officer_name ?? $report->admin->username ?? 'Menunggu Otorisasi Admin' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Status Otorisasi Kas</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">
                @if($report->status === 'approved')
                    <span class="badge badge-success">DISETUJUI &amp; DIPINDAHKAN KE BRANKAS (VAULT TRANSFER)</span>
                @elseif($report->status === 'rejected')
                    <span class="badge badge-danger">DITOLAK OLEH ADMINISTRATOR</span>
                @else
                    <span class="badge badge-warning">MENUNGGU VERIFIKASI (PENDING)</span>
                @endif
            </td>
        </tr>
        @if($report->approval_at)
        <tr>
            <td class="meta-label">Waktu Otorisasi Selesai</td>
            <td class="meta-colon">:</td>
            <td class="meta-val">{{ \Carbon\Carbon::parse($report->approval_at)->translatedFormat('d F Y, H:i:s') }} WIB</td>
        </tr>
        @endif
    </table>

    <!-- RINCIAN REKONSILIASI KAS LACI -->
    <div class="section-title">I. Rincian Posisi Kas &amp; Rekonsiliasi Fisik</div>
    @php
        $selisih = ($report->closing_balance_physical ?? 0) - ($report->closing_balance_system ?? 0);
    @endphp
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="55%">Uraian Komponen Kas</th>
                <th width="40%" class="text-right">Nominal (Rupiah)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td><strong>Kas Awal (Modal Buka Laci Kasir)</strong></td>
                <td class="text-right fw-bold">Rp {{ number_format($report->opening_balance ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Total Penerimaan Setoran Tunai (Cash-In)</td>
                <td class="text-right" style="color: #15803d;">+ Rp {{ number_format($report->total_deposits ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Total Pengeluaran Penarikan Tunai (Cash-Out)</td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($report->total_withdrawals ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td class="text-center">4</td>
                <td><strong>Saldo Kas Akhir Menurut Sistem (Awal + Masuk - Keluar)</strong></td>
                <td class="text-right fw-bold" style="font-size: 12px;">Rp {{ number_format($report->closing_balance_system ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f0fdf4;">
                <td class="text-center">5</td>
                <td><strong>Hasil Perhitungan Uang Fisik Nyata di Laci Kasir</strong></td>
                <td class="text-right fw-bold" style="font-size: 12px; color: #1e3a8a;">Rp {{ number_format($report->closing_balance_physical ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: {{ $selisih === 0 ? '#f8fafc' : ($selisih > 0 ? '#fefce8' : '#fef2f2') }};">
                <td class="text-center">6</td>
                <td><strong>Selisih Kas (Fisik Laci - Sistem)</strong></td>
                <td class="text-right fw-bold" style="font-size: 12px;">
                    @if($selisih === 0)
                        <span style="color: #15803d;">Rp 0 (SEIMBANG / MATCH)</span>
                    @elseif($selisih > 0)
                        <span style="color: #b45309;">+ Rp {{ number_format($selisih, 0, ',', '.') }} (LEBIH)</span>
                    @else
                        <span style="color: #b91c1c;">- Rp {{ number_format(abs($selisih), 0, ',', '.') }} (KURANG)</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- CATATAN / REKONSILIASI -->
    <div class="section-title">II. Catatan &amp; Keterangan Rekonsiliasi</div>
    <div class="notes-box">
        <strong>Catatan Teller / Admin:</strong><br>
        {{ $report->notes ? $report->notes : 'Tidak ada catatan khusus. Perhitungan fisik dan sistem telah dicocokkan sesuai SOP penutupan kasir Bank Mini.' }}
    </div>

    <!-- TANDA TANGAN LEGALITAS -->
    <table class="signature-table">
        <tr>
            <td>
                Bantul, {{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('d F Y') }}<br>
                <strong>Petugas Teller,</strong>
                <div class="signature-space"></div>
                <strong><u>{{ $report->teller->officer_name ?? $report->teller->username ?? 'Teller' }}</u></strong><br>
                <span>NIP/ID: {{ $report->teller->username ?? '-' }}</span>
            </td>
            <td>
                Mengetahui &amp; Memeriksa,<br>
                <strong>Administrator Bank Mini,</strong>
                <div class="signature-space"></div>
                <strong><u>{{ $report->admin->officer_name ?? $report->admin->username ?? '( ...................................... )' }}</u></strong><br>
                <span>NIP/ID: {{ $report->admin->username ?? '-' }}</span>
            </td>
            <td>
                Menyetujui Otorisasi,<br>
                <strong>Kepala / Pengelola Bank Mini,</strong>
                <div class="signature-space"></div>
                <strong><u>Drs. H. Pimpinan Sekolah</u></strong><br>
                <span>SMK Negeri 1 Bantul</span>
            </td>
        </tr>
    </table>

</body>
</html>

