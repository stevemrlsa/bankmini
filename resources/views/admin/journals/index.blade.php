@extends('layouts.app')

@section('title', 'Jurnal Akuntansi — Bank Mini')
@section('header_title', 'Jurnal Akuntansi')
@section('page-title', 'Jurnal Akuntansi')
@section('page-subtitle', 'Buku jurnal umum pembukuan berpasangan (Double-Entry General Ledger) sistem perbankan sekolah')

@section('content')
<div class="container-fluid px-0">

    {{-- Kartu Ringkasan Keseimbangan Jurnal (Neraca Debet & Kredit) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #EFF6FF; color: #1E3A8A;">
                        <i class="bi bi-arrow-down-left-circle-fill fs-5 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Debet</div>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($totalDebit, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #ECFDF5; color: #047857;">
                        <i class="bi bi-arrow-up-right-circle-fill fs-5 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Kredit</div>
                        <h5 class="fw-bold text-dark mb-0">Rp {{ number_format($totalCredit, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: {{ $isBalanced ? '#DCFCE7' : '#FEE2E2' }}; color: {{ $isBalanced ? '#16A34A' : '#DC2626' }};">
                        <i class="bi {{ $isBalanced ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }} fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Keseimbangan Neraca</div>
                        @if($isBalanced)
                            <h5 class="fw-bold text-success mb-0">Balance (Seimbang)</h5>
                        @else
                            <h5 class="fw-bold text-danger mb-0">Selisih: Rp {{ number_format($diff, 0, ',', '.') }}</h5>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Baris Entri</div>
                        <h5 class="fw-bold text-dark mb-0">{{ $journals->count() }} Baris</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Action Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: #ffffff;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.journals.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-auto">
                    <span class="fw-semibold small text-dark me-1"><i class="bi bi-filter me-1"></i>Filter Jurnal:</span>
                </div>

                {{-- Tanggal Mulai --}}
                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;"><i class="bi bi-calendar-event text-muted"></i></span>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm" style="border-radius: 0 10px 10px 0;">
                    </div>
                </div>

                {{-- Tanggal Selesai --}}
                <div class="col-6 col-md-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;"><i class="bi bi-calendar-check text-muted"></i></span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm" style="border-radius: 0 10px 10px 0;">
                    </div>
                </div>

                {{-- Filter Akun --}}
                <div class="col-6 col-md-auto">
                    <select name="account" class="form-select form-select-sm rounded-pill" style="min-width: 170px;">
                        <option value="">Semua Akun</option>
                        <option value="101" {{ $account === '101' ? 'selected' : '' }}>101 - Kas Loket Teller</option>
                        <option value="102" {{ $account === '102' ? 'selected' : '' }}>102 - Brankas Utama</option>
                        <option value="201" {{ $account === '201' ? 'selected' : '' }}>201 - Simpanan Nasabah</option>
                    </select>
                </div>

                {{-- Filter Posisi --}}
                <div class="col-6 col-md-auto">
                    <select name="position" class="form-select form-select-sm rounded-pill" style="min-width: 130px;">
                        <option value="">Semua Posisi</option>
                        <option value="Debit" {{ $position === 'Debit' ? 'selected' : '' }}>Debit Saja</option>
                        <option value="Credit" {{ $position === 'Credit' ? 'selected' : '' }}>Kredit Saja</option>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold shadow-sm" style="background-color: #111827; border: none;">
                        <i class="bi bi-search me-1"></i> Tampilkan
                    </button>
                </div>

                <div class="col-auto ms-auto">
                    <a href="{{ route('admin.journals.pdf', request()->query()) }}" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold shadow-sm">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak / Unduh PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Journal Table Card --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Daftar Entri Jurnal Umum
                </h5>
                <small class="text-muted">Pencatatan double-entry setiap setoran, penarikan tabungan, dan transfer kas fisik ke brankas</small>
            </div>
            <div>
                <span class="badge {{ $isBalanced ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi {{ $isBalanced ? 'bi-shield-check' : 'bi-exclamation-octagon' }} me-1"></i>
                    Status: {{ $isBalanced ? 'Balance (Seimbang)' : 'Anomali Tidak Seimbang' }}
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead style="background-color: #F8FAFC;">
                        <tr>
                            <th class="py-3 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em;">Tanggal & Jam</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em;">No. Bukti / Transaksi</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em;">Tipe Transaksi</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em;">Kode Akun</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em;">Nama Akun Pembukuan</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold text-end" style="font-size: 0.74rem; letter-spacing: 0.05em;">Debet (Rp)</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold text-end" style="font-size: 0.74rem; letter-spacing: 0.05em;">Kredit (Rp)</th>
                            <th class="py-3 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em;">Petugas / Nasabah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $j)
                            @php
                                $accName = $accountNames[$j->account_code] ?? 'Akun Transaksi Operasional';
                                $isDebit = ($j->position === 'Debit');
                            @endphp
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                {{-- Waktu --}}
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark">
                                        {{ \Carbon\Carbon::parse($j->created_at)->translatedFormat('d M Y') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($j->created_at)->format('H:i:s') }} WIB
                                    </small>
                                </td>

                                {{-- No. Transaksi --}}
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                        TRX-#{{ str_pad($j->transaction_id ?? $j->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                {{-- Tipe Transaksi --}}
                                <td class="py-3">
                                    @if(in_array(strtolower($j->transaction_type ?? ''), ['deposit', 'setor', 'setoran']))
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-arrow-down-left me-1"></i>Setoran Tunai
                                        </span>
                                    @elseif(in_array(strtolower($j->transaction_type ?? ''), ['withdrawal', 'tarik', 'penarikan']))
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-arrow-up-right me-1"></i>Penarikan Tunai
                                        </span>
                                    @elseif($j->transaction_type === 'Vault_Transfer')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-safe me-1"></i>Transfer Brankas
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">
                                            {{ $j->transaction_type ?: 'Jurnal Otomatis' }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Kode Akun --}}
                                <td class="py-3">
                                    <span class="fw-bold font-monospace px-2 py-1 rounded" style="background-color: #F8FAFC; color: #1E3A8A; border: 1px solid #E2E8F0;">
                                        {{ $j->account_code }}
                                    </span>
                                </td>

                                {{-- Nama Akun --}}
                                <td class="py-3">
                                    <div class="fw-semibold text-dark {{ $isDebit ? '' : 'ps-3' }}">
                                        {{ $accName }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">
                                        Posisi: <span class="fw-semibold {{ $isDebit ? 'text-primary' : 'text-success' }}">{{ $j->position }}</span>
                                    </small>
                                </td>

                                {{-- Debit --}}
                                <td class="py-3 text-end">
                                    @if($isDebit)
                                        <span class="fw-bold text-dark">Rp {{ number_format($j->amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Kredit --}}
                                <td class="py-3 text-end">
                                    @if(!$isDebit)
                                        <span class="fw-bold text-dark">Rp {{ number_format($j->amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Petugas / Nasabah --}}
                                <td class="px-4 py-3">
                                    @if($j->student_name)
                                        <div class="fw-semibold text-dark">{{ $j->student_name }}</div>
                                        <small class="text-muted">Rek: {{ $j->account_number ?? '-' }}</small>
                                    @elseif($j->officer_name)
                                        <div class="fw-semibold text-dark">{{ $j->officer_name }}</div>
                                        <small class="text-muted">Petugas Bank</small>
                                    @else
                                        <span class="text-muted small">Sistem Otomatis</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="mb-3">
                                        <i class="bi bi-journal-x fs-1 text-secondary opacity-50"></i>
                                    </div>
                                    <h6 class="fw-semibold text-secondary">Belum ada catatan jurnal akuntansi</h6>
                                    <p class="small text-muted mb-0">Setiap transaksi kas masuk, kas keluar, dan penutupan kas teller akan dicatat otomatis di jurnal umum pembukuan ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($journals->count() > 0)
                        <tfoot style="background-color: #F8FAFC; border-top: 2px solid #CBD5E1;">
                            <tr class="fw-bold text-dark">
                                <td colspan="5" class="py-3 px-4 text-end text-uppercase" style="font-size: 0.78rem;">
                                    Total Saldo Neraca (Debet vs Kredit):
                                </td>
                                <td class="py-3 text-end fs-6 text-primary">
                                    Rp {{ number_format($totalDebit, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-end fs-6 text-success">
                                    Rp {{ number_format($totalCredit, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($isBalanced)
                                        <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="bi bi-check-lg"></i> Balance</span>
                                    @else
                                        <span class="badge bg-danger text-white rounded-pill px-2 py-1"><i class="bi bi-x-lg"></i> Selisih</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

