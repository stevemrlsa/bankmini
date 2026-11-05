@extends('layouts.app')

@section('title', 'Penutupan Kas Harian — Bank Mini')
@section('header_title', 'Penutupan Kas')
@section('page-title', 'Penutupan Kas Harian')
@section('page-subtitle', 'Cocokkan uang fisik dengan catatan sistem')

@push('styles')
<style>
    .closing-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 22px;
        align-items: start;
    }

    @media (max-width: 991.98px) {
        .closing-layout {
            grid-template-columns: 1fr;
        }
    }

    /* ===== Ringkasan Sistem ===== */
    .system-summary-card {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 22px;
        padding: 26px 28px;
        box-shadow: 0 10px 26px rgba(22, 35, 63, 0.06);
        margin-bottom: 20px;
    }

    .system-summary-card h3 {
        font-size: 1.02rem;
        font-weight: 600;
        color: #1C2536;
        margin-bottom: 4px;
    }

    .system-summary-card p.desc {
        font-size: 0.8rem;
        color: #808C9E;
        margin-bottom: 22px;
    }

    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 0;
        border-bottom: 1px solid rgba(22, 35, 63, 0.06);
    }

    .summary-row:last-child { border-bottom: none; padding-bottom: 0; }
    .summary-row:first-child { padding-top: 0; }

    .summary-row .label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.86rem;
        color: #5C6579;
    }

    .summary-row .label i {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        color: #fff;
    }

    .summary-row .label i.i-open { background: linear-gradient(135deg, #16233F, #3B5BA5); }
    .summary-row .label i.i-in { background: linear-gradient(135deg, #0F766E, #14B8A6); }
    .summary-row .label i.i-out { background: linear-gradient(135deg, #B45309, #F59E0B); }

    .summary-row .value {
        font-size: 0.92rem;
        font-weight: 600;
        color: #1C2536;
    }

    .summary-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 16px;
        padding: 16px 18px;
        border-radius: 15px;
        background: linear-gradient(135deg, rgba(22,35,63,0.05), rgba(59,91,165,0.06));
    }

    .summary-total .label {
        font-size: 0.86rem;
        font-weight: 500;
        color: #1C2536;
    }

    .summary-total .value {
        font-size: 1.15rem;
        font-weight: 700;
        color: #16233F;
    }

    /* ===== Form Kas Fisik (Glass) ===== */
    .closing-form-card {
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(16px) saturate(160%);
        -webkit-backdrop-filter: blur(16px) saturate(160%);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 24px;
        box-shadow: 0 18px 42px rgba(22, 35, 63, 0.1), inset 0 1px 0 rgba(255,255,255,0.5);
        padding: 28px 26px;
        position: sticky;
        top: 96px;
    }

    .closing-form-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #16233F;
        margin-bottom: 4px;
    }

    .closing-form-card p.desc {
        font-size: 0.79rem;
        color: #808C9E;
        margin-bottom: 22px;
    }

    .field-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: #1C2536;
        margin-bottom: 7px;
        display: block;
    }

    .field-wrap { position: relative; display: flex; align-items: center; margin-bottom: 18px; }

    .field-wrap .field-prefix {
        position: absolute;
        left: 16px;
        color: #1C2536;
        font-weight: 500;
        font-size: 0.88rem;
    }

    .field-input {
        width: 100%;
        padding: 12px 16px 12px 40px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.94rem;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(22, 35, 63, 0.12);
        border-radius: 13px;
        color: #1C2536;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .field-input:focus {
        border-color: #3B5BA5;
        box-shadow: 0 0 0 4px rgba(59, 91, 165, 0.12);
    }

    /* ===== Hasil Selisih ===== */
    .diff-result {
        border-radius: 15px;
        padding: 16px 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .diff-result.balanced { background: rgba(22, 163, 74, 0.1); border: 1px solid rgba(22, 163, 74, 0.18); }
    .diff-result.over { background: rgba(59, 91, 165, 0.09); border: 1px solid rgba(59, 91, 165, 0.16); }
    .diff-result.short { background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.16); }

    .diff-result .diff-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1rem; flex-shrink: 0;
    }

    .diff-result.balanced .diff-icon { background: #15803D; }
    .diff-result.over .diff-icon { background: #3B5BA5; }
    .diff-result.short .diff-icon { background: #B91C1C; }

    .diff-result strong { display: block; font-size: 0.88rem; }
    .diff-result.balanced strong { color: #14532D; }
    .diff-result.over strong { color: #1E3A6E; }
    .diff-result.short strong { color: #7F1D1D; }

    .diff-result span { font-size: 0.76rem; }
    .diff-result.balanced span { color: #166534; }
    .diff-result.over span { color: #2C4A82; }
    .diff-result.short span { color: #991B1B; }

    .btn-submit-navy {
        width: 100%;
        border: none;
        padding: 13px;
        border-radius: 13px;
        background: linear-gradient(135deg, #16233F 0%, #0E1830 100%);
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 0.92rem;
        box-shadow: 0 12px 24px rgba(22, 35, 63, 0.26);
        transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
    }

    .btn-submit-navy:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(22, 35, 63, 0.32);
        filter: brightness(1.08);
        color: #fff;
    }

    .btn-submit-navy:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }
</style>
@endpush

@section('content')

    @php
        $saldoAwal = $saldoAwal ?? 500000;
        $totalSetoran = $totalSetoran ?? 875000;
        $totalPenarikan = $totalPenarikan ?? 240000;
        $saldoSistem = $saldoAwal + $totalSetoran - $totalPenarikan;
    @endphp

    <div class="closing-layout">

        <!-- Kolom kiri: ringkasan sistem -->
        <div>
            <div class="system-summary-card">
                <h3>Ringkasan Kas Sistem</h3>
                <p class="desc">Perhitungan otomatis berdasarkan seluruh transaksi loket kamu hari ini.</p>

                <div class="summary-row">
                    <div class="label"><i class="bi bi-wallet2 i-open"></i> Saldo Kas Awal</div>
                    <div class="value">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</div>
                </div>
                <div class="summary-row">
                    <div class="label"><i class="bi bi-arrow-down-circle-fill i-in"></i> Total Setoran Masuk</div>
                    <div class="value">+Rp {{ number_format($totalSetoran, 0, ',', '.') }}</div>
                </div>
                <div class="summary-row">
                    <div class="label"><i class="bi bi-arrow-up-circle-fill i-out"></i> Total Penarikan Keluar</div>
                    <div class="value">-Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</div>
                </div>

                <div class="summary-total">
                    <span class="label">Saldo Kas Menurut Sistem</span>
                    <span class="value">Rp {{ number_format($saldoSistem, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="card-solid p-4">
                <p style="font-size:0.86rem; font-weight:600; color:#1C2536; margin-bottom:10px;">Catatan Penutupan</p>
                <p style="font-size:0.82rem; color:#808C9E; margin:0;">
                    Hitung seluruh uang fisik di laci kasir (uang kertas dan koin), lalu masukkan totalnya pada form di samping.
                    Sistem akan otomatis menghitung selisih antara jumlah fisik dan catatan sistem. Selisih akan tercatat pada
                    laporan audit dan ditinjau oleh Administrator.
                </p>
            </div>
        </div>

        <!-- Kolom kanan: form kas fisik -->
        <div class="closing-form-card">
            <h3>Input Uang Fisik</h3>
            <p class="desc">Masukkan total uang tunai hasil hitungan manual kamu.</p>

            @if(session('success'))
            <div class="diff-result balanced mb-3">
                <div class="diff-icon"><i class="bi bi-check-lg"></i></div>
                <div>
                    <strong>Laporan tersimpan</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('teller.report.store') }}">
                @csrf
                <input type="hidden" name="saldo_sistem" value="{{ $saldoSistem }}">

                <label class="field-label" for="uang_fisik">Total Uang Fisik</label>
                <div class="field-wrap">
                    <span class="field-prefix">Rp</span>
                    <input
                        type="number"
                        id="uang_fisik"
                        name="uang_fisik"
                        min="0"
                        step="500"
                        class="field-input"
                        style="padding-left:40px;"
                        placeholder="0"
                        value="{{ old('uang_fisik') }}"
                        required
                    >
                </div>

                <div id="diffPreview"></div>

                <button type="submit" class="btn-submit-navy">
                    <i class="bi bi-lock-fill"></i> Kunci &amp; Kirim Laporan
                </button>
            </form>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    const saldoSistem = {{ $saldoSistem }};
    const inputFisik = document.getElementById('uang_fisik');
    const diffPreview = document.getElementById('diffPreview');

    function formatRupiah(angka) {
        return 'Rp ' + Math.abs(angka).toLocaleString('id-ID');
    }

    inputFisik?.addEventListener('input', function () {
        const nilai = parseInt(this.value || 0, 10);
        const selisih = nilai - saldoSistem;

        let html = '';
        if (isNaN(nilai) || this.value === '') {
            diffPreview.innerHTML = '';
            return;
        }

        if (selisih === 0) {
            html = `<div class="diff-result balanced">
                        <div class="diff-icon"><i class="bi bi-check-lg"></i></div>
                        <div><strong>Kas Seimbang</strong><span>Jumlah fisik sesuai dengan catatan sistem.</span></div>
                    </div>`;
        } else if (selisih > 0) {
            html = `<div class="diff-result over">
                        <div class="diff-icon"><i class="bi bi-arrow-up-short"></i></div>
                        <div><strong>Kelebihan ${formatRupiah(selisih)}</strong><span>Uang fisik lebih banyak dari catatan sistem.</span></div>
                    </div>`;
        } else {
            html = `<div class="diff-result short">
                        <div class="diff-icon"><i class="bi bi-arrow-down-short"></i></div>
                        <div><strong>Kekurangan ${formatRupiah(selisih)}</strong><span>Uang fisik lebih sedikit dari catatan sistem.</span></div>
                    </div>`;
        }
        diffPreview.innerHTML = html;
    });
</script>
@endpush
