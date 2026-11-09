@extends('layouts.app')

@section('title', 'Buat Pengajuan Penarikan — Bank Mini')
@section('header_title', 'Pengajuan Penarikan')
@section('page-title', 'Form Pengajuan Penarikan Dana Besar')
@section('page-subtitle', 'Pendaftaran pengajuan penarikan dana besar minimal H-1 sebelum pengambilan uang tunai')

@push('styles')
<style>
    .form-stage-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 24px;
        box-shadow: 0 16px 36px rgba(22, 35, 63, 0.06);
        padding: 32px;
    }

    .field-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #1C2536;
        margin-bottom: 6px;
    }

    .rupiah-prefix {
        background: #F1F5F9;
        font-weight: 700;
        color: #334155;
        border-color: rgba(22, 35, 63, 0.12);
    }
</style>
@endpush

@section('content')

    @php
        $indexRoute = request()->routeIs('teller.*') ? 'teller.withdrawal-requests.index' : 'admin.withdrawal-requests.index';
        $storeRoute = request()->routeIs('teller.*') ? 'teller.withdrawal-requests.store' : 'admin.withdrawal-requests.store';
    @endphp

    <div class="mb-3">
        <a href="{{ route($indexRoute) }}" class="btn btn-sm btn-link text-decoration-none text-muted p-0 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengajuan Penarikan
        </a>
    </div>

    <!-- Flash Alert -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="row g-4 justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="form-stage-card">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(180, 83, 9, 0.12); color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                        <i class="bi bi-calendar2-plus-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Reservasi Penarikan Tunai &gt; Rp 1.000.000</h5>
                        <p class="text-muted small mb-0">Wajib diajukan minimal H-1 dan disetujui Teller serta Administrator</p>
                    </div>
                </div>

                <form action="{{ route($storeRoute) }}" method="POST" id="formWithdrawalBooking">
                    @csrf

                    <!-- 1. Input Nomor Rekening -->
                    <div class="mb-3">
                        <label class="field-label" for="account_number">Nomor Rekening / NIS Nasabah <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0">
                                <i class="bi bi-credit-card-2-front-fill"></i>
                            </span>
                            <input
                                type="text"
                                name="account_number"
                                id="account_number"
                                class="form-control border-start-0 @error('account_number') is-invalid @enderror"
                                placeholder="Masukkan No. Rekening atau NIS siswa"
                                value="{{ old('account_number', request('account_number')) }}"
                                required
                                autocomplete="off"
                            >
                            <button type="button" class="btn btn-outline-secondary px-3" id="btnCheckAccount">
                                <i class="bi bi-search me-1"></i> Periksa
                            </button>
                        </div>
                        <small class="text-muted" id="accountCheckHelper">Ketik nomor rekening lalu klik Periksa untuk mengecek saldo.</small>
                        @error('account_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Info Siswa & Saldo Card -->
                    <div id="saverInfoBox" class="p-3 rounded-4 mb-4 border d-none" style="background: #F8FAFC;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold text-dark fs-6" id="dispStudentName">-</div>
                                <div class="text-muted small" id="dispStudentMeta">-</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">Saldo Tersimpan:</div>
                                <div class="fw-bold text-success fs-5" id="dispStudentSaldo">Rp 0</div>
                                <small class="text-primary fw-medium" id="dispMaxWithdrawable" style="font-size: 0.76rem;">Maksimal ditarik: Rp 0</small>
                            </div>
                        </div>
                        <div id="dispBlockedWarning" class="alert alert-danger py-2 px-3 mt-2 mb-0 small d-none">
                            <i class="bi bi-lock-fill me-1"></i> <strong>PERINGATAN:</strong> Rekening ini sedang DIBLOKIR. Pengajuan tidak dapat diproses.
                        </div>
                    </div>

                    <!-- 2. Nominal Penarikan -->
                    <div class="mb-3">
                        <label class="field-label" for="amount">Nominal Penarikan yang Diajukan (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text rupiah-prefix">Rp</span>
                            <input
                                type="text"
                                name="amount"
                                id="amount"
                                class="form-control fw-bold fs-5 text-danger @error('amount') is-invalid @enderror"
                                placeholder="Contoh: 1.500.000"
                                value="{{ old('amount', request('amount')) }}"
                                required
                            >
                        </div>
                        <small class="text-muted">
                            Nominal penarikan wajib <strong>di atas Rp 1.000.000</strong>. Saldo rekening wajib menyisakan saldo mengendap minimal <strong>Rp {{ number_format($batasMinSaldo, 0, ',', '.') }}</strong>.
                        </small>
                        @error('amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 3. Tanggal Janjian Penarikan (Minimal H-1 / Mulai Besok) -->
                    <div class="mb-3">
                        <label class="field-label" for="scheduled_date">Tanggal Pengambilan Uang Tunai di Loket <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            name="scheduled_date"
                            id="scheduled_date"
                            min="{{ $minDate }}"
                            class="form-control @error('scheduled_date') is-invalid @enderror"
                            value="{{ old('scheduled_date', $minDate) }}"
                            required
                        >
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Sesuai SOP, janjian wajib diajukan <strong>minimal H-1</strong> (tanggal paling cepat adalah besok: <strong>{{ \Carbon\Carbon::parse($minDate)->translatedFormat('d F Y') }}</strong>).
                        </small>
                        @error('scheduled_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 4. Keperluan Penarikan -->
                    <div class="mb-3">
                        <label class="field-label" for="purpose">Keperluan Penarikan Tunai <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="purpose"
                            id="purpose"
                            class="form-control @error('purpose') is-invalid @enderror"
                            placeholder="Contoh: Pembayaran biaya daftar ulang / studi banding / keperluan mendesak keluarga"
                            value="{{ old('purpose') }}"
                            required
                        >
                        @error('purpose')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 5. Catatan Tambahan (Opsional) -->
                    <div class="mb-4">
                        <label class="field-label" for="notes">Catatan Tambahan (Opsional)</label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="2"
                            class="form-control"
                            placeholder="Catatan tambahan dari nasabah atau petugas..."
                        >{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <a href="{{ route($indexRoute) }}" class="btn btn-light rounded-pill px-4 text-muted fw-semibold">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm" id="btnSubmitBooking" style="background: #111827;">
                            <i class="bi bi-calendar-check-fill me-1"></i> Ajukan Penarikan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Panduan SOP -->
        <div class="col-12 col-lg-4">
            <div class="p-4 rounded-4 bg-white border shadow-sm">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-shield-check text-success me-2"></i> SOP Penarikan Dana Besar
                </h6>
                <ul class="list-unstyled small text-secondary mb-0" style="line-height: 1.8;">
                    <li class="mb-2">
                        <strong>1. Batas Reguler Harian:</strong><br>
                        Penarikan tunai harian s.d. <strong>Rp 1.000.000</strong> dapat langsung dilayani di loket teller tanpa perlu janjian terlebih dahulu.
                    </li>
                    <li class="mb-2">
                        <strong>2. Penarikan &gt; Rp 1.000.000:</strong><br>
                        Wajib mengajukan janjian minimal <strong>H-1</strong> sebelum tanggal pengambilan uang.
                    </li>
                    <li class="mb-2">
                        <strong>3. Otorisasi Ganda:</strong><br>
                        Pengajuan dana besar harus diverifikasi dan disetujui oleh pihak <strong>Teller dan Administrator</strong> untuk mempersiapkan dana tunai fisik di brankas utama.
                    </li>
                    <li class="mb-2">
                        <strong>4. Saldo Mengendap Rp 10.000:</strong><br>
                        Berapapun nominal yang ditarik, rekening wajib tetap menyisakan saldo minimal <strong>Rp {{ number_format($batasMinSaldo, 0, ',', '.') }}</strong>.
                    </li>
                    <li>
                        <strong>5. Pengambilan Fisik:</strong><br>
                        Nasabah hadir ke loket pada tanggal janjian dengan membawa buku tabungan / kartu identitas siswa.
                    </li>
                </ul>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const accInput = document.getElementById('account_number');
    const btnCheck = document.getElementById('btnCheckAccount');
    const helper   = document.getElementById('accountCheckHelper');
    const infoBox  = document.getElementById('saverInfoBox');
    const dispName = document.getElementById('dispStudentName');
    const dispMeta = document.getElementById('dispStudentMeta');
    const dispSaldo= document.getElementById('dispStudentSaldo');
    const dispMax  = document.getElementById('dispMaxWithdrawable');
    const dispBlocked = document.getElementById('dispBlockedWarning');
    const btnSubmit= document.getElementById('btnSubmitBooking');
    const amountInput = document.getElementById('amount');

    // Format Ribuan Otomatis
    amountInput.addEventListener('input', function (e) {
        let val = this.value.replace(/[^0-9]/g, '');
        if (val) {
            this.value = Number(val).toLocaleString('id-ID');
        }
    });

    async function checkAccount() {
        const val = accInput.value.trim();
        if (!val) {
            helper.textContent = 'Silakan masukkan nomor rekening atau NIS nasabah.';
            helper.className = 'text-danger small';
            return;
        }

        helper.innerHTML = '<span class="text-primary"><span class="spinner-border spinner-border-sm me-1"></span> Memeriksa data nasabah...</span>';

        try {
            const res = await fetch(`{{ route('teller.search-saver') }}?q=${encodeURIComponent(val)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (res.ok && data.success && data.data) {
                const s = data.data;
                dispName.textContent = s.student_name || s.name;
                dispMeta.textContent = `No. Rek: ${s.account_number || '-'} · NIS: ${s.student_id || '-'} · Kelas: ${s.grade_class || '-'}`;
                dispSaldo.textContent = s.formatted_saldo || ('Rp ' + Number(s.saldo || 0).toLocaleString('id-ID'));
                dispMax.textContent = `Maksimal ditarik: ${s.formatted_max_withdraw || 'Rp 0'}`;

                infoBox.classList.remove('d-none');
                helper.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i> Data nasabah valid.</span>';

                if (s.is_blocked) {
                    dispBlocked.classList.remove('d-none');
                    btnSubmit.setAttribute('disabled', 'disabled');
                } else {
                    dispBlocked.classList.add('d-none');
                    btnSubmit.removeAttribute('disabled');
                }
            } else {
                infoBox.classList.add('d-none');
                helper.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i> ${data.message || 'Nomor rekening tidak ditemukan.'}</span>`;
            }
        } catch (e) {
            helper.innerHTML = '<span class="text-danger">Gagal memeriksa data nasabah. Periksa koneksi internet.</span>';
        }
    }

    btnCheck.addEventListener('click', checkAccount);
    accInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            checkAccount();
        }
    });

    // Jalankan auto-check jika nilai awal terisi
    if (accInput.value.trim()) {
        checkAccount();
    }
});
</script>
@endpush

