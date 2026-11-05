<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekening Koran — {{ $saver->student_name }} ({{ $saver->account_number }})</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-smkn1bantul.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            padding: 30px 15px;
        }

        .statement-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #E2E8F0;
        }

        /* Kop Surat Resmi */
        .kop-surat {
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 2.5px solid #16233F;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .kop-logo {
            width: 78px;
            height: 78px;
            object-fit: contain;
        }

        .kop-text h4 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #16233F;
            margin: 0;
            letter-spacing: -0.01em;
            text-transform: uppercase;
        }

        .kop-text h5 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1E3A8A;
            margin: 2px 0 4px;
        }

        .kop-text p {
            font-size: 0.78rem;
            color: #64748B;
            margin: 0;
            line-height: 1.4;
        }

        /* Statement Title */
        .statement-title-wrap {
            text-align: center;
            margin-bottom: 26px;
        }

        .statement-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #16233F;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .statement-subtitle {
            font-size: 0.82rem;
            color: #64748B;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }

        .info-item {
            display: flex;
        }

        .info-label {
            width: 140px;
            color: #64748B;
            font-weight: 500;
        }

        .info-val {
            font-weight: 700;
            color: #0F172A;
            flex-grow: 1;
        }

        /* Summary Cards */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .summary-box {
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
        }

        .summary-box span {
            font-size: 0.74rem;
            color: #64748B;
            display: block;
            margin-bottom: 2px;
        }

        .summary-box strong {
            font-size: 1.05rem;
            font-weight: 800;
        }

        /* Table */
        .statement-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
            margin-bottom: 30px;
        }

        .statement-table th {
            background-color: #16233F;
            color: #FFFFFF;
            padding: 10px 12px;
            font-weight: 600;
            text-align: left;
            border: 1px solid #16233F;
        }

        .statement-table td {
            padding: 9px 12px;
            border: 1px solid #E2E8F0;
            color: #1E293B;
        }

        .statement-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        /* Signatures */
        .signature-block {
            display: flex;
            justify-content: space-between;
            margin-top: 36px;
            font-size: 0.85rem;
        }

        .sig-col {
            text-align: center;
            width: 240px;
        }

        .sig-space {
            height: 70px;
        }

        .sig-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0F172A;
        }

        .sig-role {
            font-size: 0.78rem;
            color: #64748B;
        }

        /* Action Bar (Screen Only) */
        .action-bar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .table-responsive-statement {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 650px) {
            body {
                padding: 15px 10px;
            }

            .statement-wrapper {
                padding: 20px 15px;
                border-radius: 14px;
            }

            .kop-surat {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .kop-logo {
                width: 58px;
                height: 58px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                padding: 14px;
                gap: 8px;
            }

            .info-label {
                width: 125px;
                font-size: 0.78rem;
            }

            .info-val {
                font-size: 0.82rem;
            }

            .summary-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .statement-table {
                min-width: 560px;
            }

            .signature-block {
                flex-direction: column;
                align-items: center;
                gap: 30px;
            }

            .sig-col {
                width: 100%;
                max-width: 260px;
            }
        }

        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }

            .action-bar {
                display: none !important;
            }

            .statement-wrapper {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }

            .statement-table th {
                background-color: #16233F !important;
                color: #FFFFFF !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Navigasi Tombol Aksi (Hanya Tampil di Layar) -->
    <div class="action-bar">
        <a href="{{ route('saver.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="statement-wrapper">

        <!-- KOP SURAT RESMI -->
        <div class="kop-surat">
            <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul" class="kop-logo">
            <div class="kop-text">
                <h4>SMK Negeri 1 Bantul — Layanan Kas Terpadu</h4>
                <h5>BANK MINI SMKN 1 BANTUL (SimPel)</h5>
                <p>Jl. Parangtritis Km 11, Sabdodadi, Bantul, D.I. Yogyakarta 55702 • Telp: (0274) 367156<br>
                Sistem Buku Tabungan Digital & Pembukuan Kas Terintegrasi Double-Entry</p>
            </div>
        </div>

        <!-- JUDUL REKENING KORAN -->
        <div class="statement-title-wrap">
            <div class="statement-title">Rekening Koran / Laporan Mutasi Tabungan Siswa</div>
            <div class="statement-subtitle">Simpanan Pelajar (SimPel) — Dicetak pada: {{ now()->translatedFormat('l, d F Y H:i') }} WIB</div>
        </div>

        <!-- INFORMASI NASABAH -->
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nama Siswa:</span>
                <span class="info-val">{{ $saver->student_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nomor Rekening:</span>
                <span class="info-val font-monospace">{{ $saver->account_number }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">NIS (No. Induk):</span>
                <span class="info-val">{{ $saver->student_id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Kelas / Jurusan:</span>
                <span class="info-val">{{ $saver->grade_class ?? 'Siswa Aktif' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status Rekening:</span>
                <span class="info-val text-success">● AKTIF (Verified)</span>
            </div>
            <div class="info-item">
                <span class="info-label">Mata Uang:</span>
                <span class="info-val">IDR (Rupiah Indonesia)</span>
            </div>
        </div>

        <!-- RINGKASAN AKUMULASI -->
        <div class="summary-row">
            <div class="summary-box" style="border-left: 4px solid #10B981;">
                <span>Total Setoran Masuk (Kredit)</span>
                <strong class="text-success">+ Rp {{ number_format($totalDeposits, 0, ',', '.') }}</strong>
            </div>
            <div class="summary-box" style="border-left: 4px solid #EF4444;">
                <span>Total Penarikan Keluar (Debet)</span>
                <strong class="text-danger">- Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}</strong>
            </div>
            <div class="summary-box" style="border-left: 4px solid #16233F; background: #F1F5F9;">
                <span>Saldo Akhir Tabungan</span>
                <strong style="color: #16233F;">Rp {{ number_format($saver->saldo, 0, ',', '.') }}</strong>
            </div>
        </div>

        <!-- TABEL MUTASI DETAIL -->
        <div class="table-responsive-statement">
            <table class="statement-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th style="width: 140px;">Tanggal & Waktu</th>
                        <th>Keterangan / Uraian Transaksi</th>
                        <th style="width: 110px; text-align: right;">Penarikan (Dr)</th>
                        <th style="width: 110px; text-align: right;">Setoran (Cr)</th>
                        <th style="width: 120px; text-align: right;">Saldo Akhir</th>
                        <th style="width: 90px; text-align: center;">Kasir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $index => $trx)
                        @php
                            $isDeposit = in_array($trx->transaction_type, ['Deposit', 'deposit', 'setor', 'setoran']);
                            $tellerName = $trx->user->officer_name ?? $trx->user->username ?? 'Teller';
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($trx->transaction_date ?? $trx->created_at)->translatedFormat('d/m/Y H:i') }}</td>
                            <td>
                                <strong>{{ $isDeposit ? 'Setoran Tunai' : 'Penarikan Kas' }}</strong>
                                @if($trx->notes)
                                    <div style="font-size: 0.74rem; color: #64748B;">{{ $trx->notes }}</div>
                                @endif
                            </td>
                            <td style="text-align: right; color: #DC2626;">
                                {{ !$isDeposit ? 'Rp ' . number_format($trx->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right; color: #059669;">
                                {{ $isDeposit ? 'Rp ' . number_format($trx->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right; font-weight: 700;">
                                Rp {{ number_format($trx->running_balance ?? $saver->saldo, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; font-size: 0.78rem;">{{ $tellerName }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: #64748B;">
                                Belum ada catatan mutasi transaksi pada rekening ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- KOLOM TANDA TANGAN -->
        <div class="signature-block">
            <div class="sig-col">
                <div>Mengetahui,</div>
                <div class="text-muted small">Nasabah Siswa / Wali Murid</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $saver->student_name }}</div>
                <div class="sig-role">NIS: {{ $saver->student_id }}</div>
            </div>

            <div class="sig-col">
                <div>Bantul, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="text-muted small">Petugas Bank Mini SMKN 1 Bantul</div>
                <div class="sig-space"></div>
                <div class="sig-name">Pengelola Bank Mini</div>
                <div class="sig-role">SMK Negeri 1 Bantul</div>
            </div>
        </div>

    </div>

</body>
</html>

