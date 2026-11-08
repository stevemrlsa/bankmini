@extends('layouts.app')

@section('header_title', 'Data Nasabah')
@section('page-title', 'Data Nasabah')
@section('page-subtitle', 'Master data rekening tabungan siswa aktif')

@push('styles')
<style>
    .toolbar-card {
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 20px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(22, 35, 63, 0.05);
    }

    .filter-select {
        border-radius: 12px;
        border: 1px solid rgba(22, 35, 63, 0.12);
        padding: 9px 14px;
        font-size: 0.85rem;
        font-family: 'Poppins', sans-serif;
        background: #fff;
        color: #1C2536;
        outline: none;
    }

    .filter-select:focus {
        border-color: #3B5BA5;
    }

    .btn-primary-navy {
        background: linear-gradient(135deg, #16233F, #0E1830);
        border: none;
        color: #fff;
        font-size: 0.85rem;
        font-weight: 500;
        padding: 9px 18px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 10px 20px rgba(22, 35, 63, 0.2);
        transition: transform 0.16s ease, box-shadow 0.16s ease;
        text-decoration: none;
    }

    .btn-primary-navy:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 24px rgba(22, 35, 63, 0.26);
    }

    .table-premium-wrap {
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(22, 35, 63, 0.07);
        box-shadow: 0 10px 26px rgba(22, 35, 63, 0.05);
    }

    table.table-premium {
        margin: 0;
        font-size: 0.86rem;
    }

    table.table-premium thead th {
        background: #F7F9F9;
        color: #5C6579;
        font-weight: 600;
        font-size: 0.76rem;
        border: none;
        padding: 15px 20px;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    table.table-premium tbody td {
        padding: 15px 20px;
        vertical-align: middle;
        border-top: 1px solid rgba(22, 35, 63, 0.06);
        color: #1C2536;
    }

    table.table-premium tbody tr:hover {
        background: rgba(59, 91, 165, 0.035);
    }

    .rek-number {
        font-weight: 600;
        color: #16233F;
        letter-spacing: 0.3px;
    }

    .kelas-badge {
        font-size: 0.74rem;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 20px;
        background: rgba(59, 91, 165, 0.1);
        color: #3B5BA5;
    }

    .saldo-value {
        font-weight: 600;
        color: #15803D;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        border: 1px solid rgba(22, 35, 63, 0.1);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #5C6579;
        font-size: 0.85rem;
        transition: all 0.15s ease;
        text-decoration: none;
    }

    .action-btn:hover {
        background: #F4F7F6;
        color: #16233F;
    }

    .action-btn.danger:hover {
        background: rgba(220, 38, 38, 0.08);
        color: #B91C1C;
        border-color: rgba(220, 38, 38, 0.2);
    }

    .pagination-note {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        font-size: 0.8rem;
        color: #808C9E;
    }

    .modal-content-glass {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        box-shadow: 0 24px 60px rgba(22, 35, 63, 0.25);
    }
</style>
@endpush

@section('content')

    <!-- Toolbar: Pencarian, Filter Kelas, dan Aksi Massal -->
    <div class="toolbar-card">
        <!-- Pencarian & Filter Kelas -->
        <form method="GET" action="{{ route('admin.savers.index') }}" class="d-flex gap-2 flex-wrap align-items-center mb-0">
            <div class="input-group" style="max-width: 340px;">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    style="border-radius: 12px 0 0 12px; font-size: 0.85rem; border-color: rgba(22, 35, 63, 0.12);"
                    placeholder="Cari nama atau NIS..."
                    value="{{ request('search') }}"
                >
                <button type="submit" class="btn btn-primary-navy" style="border-radius: 0 12px 12px 0; padding: 8px 16px;" title="Cari Data">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <select name="class" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                @if(isset($classes))
                    @foreach($classes as $c)
                        <option value="{{ $c }}" {{ request('class') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                @else
                    <option>Kelas 1</option>
                    <option>Kelas 2</option>
                    <option>Kelas 3</option>
                    <option>Kelas 4</option>
                    <option>Kelas 5</option>
                    <option>Kelas 6</option>
                @endif
            </select>

            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Diblokir</option>
            </select>

            @if(request('search') || request('class') || request('status'))
                <a href="{{ route('admin.savers.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 text-decoration-none" style="font-size: 0.78rem;">
                    <i class="bi bi-x-circle me-1"></i> Reset Filter
                </a>
            @endif
        </form>

        <!-- Tombol Aksi Massal -->
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @if(Route::has('admin.alumni.index'))
                <a href="{{ route('admin.alumni.index') }}" class="btn btn-outline-dark rounded-3 px-3 py-2 fw-semibold" style="font-size: 0.85rem; border-radius: 12px;">
                    <i class="bi bi-mortarboard me-1"></i> Data Siswa Lulus (Alumni)
                </a>
            @endif
            <!-- Tombol Naik Kelas Massal -->
            <button type="button" class="btn-primary-navy" style="background: linear-gradient(135deg, #1E3A8A, #3B5BA5);" data-bs-toggle="modal" data-bs-target="#modalBulkPromote">
                <i class="bi bi-mortarboard-fill"></i> Naik Kelas Massal
            </button>
        </div>
    </div>

    <!-- Tabel Data Nasabah Siswa Aktif -->
    <div class="table-premium-wrap">
        <div class="table-responsive">
            <table class="table table-premium">
                <thead>
                    <tr>
                        <th>No. Rekening</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Saldo Tabungan</th>
                        <th style="width: 210px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($savers as $item)
                    <tr style="{{ $item->status === 'blocked' ? 'background: #FEF2F2;' : '' }}">
                        <td>
                            <span class="rek-number">{{ $item->account_number ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-secondary">{{ $item->student_id }}</span>
                        </td>
                        <td>
                            <span class="fw-medium text-dark">{{ $item->student_name }}</span>
                        </td>
                        <td>
                            <span class="kelas-badge">{{ $item->grade_class ?? '-' }}</span>
                        </td>
                        <td>
                            @if($item->status === 'blocked')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill" style="font-size: 0.74rem;">
                                    <i class="bi bi-lock-fill me-1"></i>Diblokir
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.74rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="saldo-value">Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <!-- Cetak Kartu QR Siswa -->
                                <button type="button" class="action-btn text-primary" title="Cetak Kartu QR" data-bs-toggle="modal" data-bs-target="#modalQrCode{{ $item->id }}">
                                    <i class="bi bi-qr-code"></i>
                                </button>

                                <!-- Lihat Mutasi Transaksi (Eye Icon) -->
                                <a href="{{ route('admin.savers.history', $item->id) }}" class="action-btn text-info" title="Lihat Mutasi Transaksi">
                                    <i class="bi bi-eye-fill"></i>
                                </a>

                                <!-- Edit Data Siswa (Pencil Icon) -->
                                <button type="button" class="action-btn text-warning" title="Edit Data Siswa" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <!-- Blokir / Buka Blokir Rekening Siswa -->
                                @if($item->status === 'blocked')
                                    <form action="{{ route('admin.savers.unblock', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Buka blokir rekening nasabah {{ $item->student_name }}? Rekening akan aktif kembali.');">
                                        @csrf
                                        <button type="submit" class="action-btn text-success" title="Buka Blokir Rekening" style="border-color: rgba(22, 163, 74, 0.3); background: #F0FDF4;">
                                            <i class="bi bi-unlock-fill"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="action-btn text-danger" title="Blokir Rekening Nasabah" data-bs-toggle="modal" data-bs-target="#modalBlock{{ $item->id }}" style="border-color: rgba(220, 38, 38, 0.3);">
                                        <i class="bi bi-lock-fill"></i>
                                    </button>
                                @endif

                                <!-- Reset PIN ke 123456 -->
                                <form action="{{ route('admin.savers.reset-pin', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin mereset PIN nasabah ini ke 123456?');">
                                    @csrf
                                    <button type="submit" class="action-btn" title="Reset PIN ke 123456" style="color: #EA580C; border-color: rgba(234, 88, 12, 0.2);">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>

                                <!-- Reset Password ke 123456 (Plan B Darurat) -->
                                <button type="button" class="action-btn" title="Reset Password ke default (123456)" data-bs-toggle="modal" data-bs-target="#modalResetPass{{ $item->id }}" style="color: #D97706; border-color: rgba(217, 119, 6, 0.25);">
                                    <i class="bi bi-key-fill"></i>
                                </button>

                                <!-- Nonaktifkan Akun Nasabah (Stop/Slash Icon) -->
                                <form action="{{ route('admin.savers.deactivate', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menonaktifkan akun siswa ini? Status akun akan diubah menjadi nonaktif.');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn danger" title="Nonaktifkan Akun">
                                        <i class="bi bi-slash-circle-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Tidak ada data siswa aktif yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($savers, 'hasPages') && $savers->hasPages())
            <div class="pagination-note border-top">
                <span>Menampilkan {{ $savers->firstItem() }}–{{ $savers->lastItem() }} dari {{ $savers->total() }} nasabah aktif</span>
                <div>{{ $savers->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>

    <!-- Modal Konfirmasi Naik Kelas Massal -->
    <div class="modal fade" id="modalBulkPromote" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-glass">
                <div class="modal-header border-0 pb-0 justify-content-between">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-mortarboard-fill text-primary me-2"></i> Konfirmasi Kenaikan Kelas Massal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('admin.savers.bulk-promote') }}" method="POST">
                    @csrf
                    <div class="modal-body py-3">
                        <p class="text-muted small mb-3">
                            Aksi ini akan menaikkan jenjang <strong>seluruh siswa</strong> (baik nasabah aktif maupun nonaktif) secara otomatis:
                        </p>
                        <ul class="text-secondary small mb-3 ps-3">
                            <li>Kelas X (10) akan dinaikkan ke Kelas XI (11).</li>
                            <li>Kelas XI (11) akan dinaikkan ke Kelas XII (12).</li>
                            <li>Kelas XII (12) akan dialihkan ke status kelulusan dan otomatis diubah menjadi <strong>Nonaktif (alumni)</strong>.</li>
                            <li>Data nasabah nonaktif / anggota yang belum aktivasi juga otomatis ikut naik kelas.</li>
                        </ul>
                        <div class="alert alert-warning py-2 px-3 small rounded-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Pastikan proses ini dilakukan pada saat pergantian tahun ajaran baru.
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary-navy">
                            <i class="bi bi-check-circle-fill me-1"></i> Ya, Jalankan Kenaikan Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals untuk Setiap Nasabah (QR Code & Edit Data) -->
    @if(isset($savers) && count($savers) > 0)
        @foreach($savers as $item)
            <!-- Modal Cetak Kartu QR Siswa -->
            <div class="modal fade" id="modalQrCode{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content modal-content-glass text-center">
                        <div class="modal-header border-0 pb-0 justify-content-end">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body pt-0 pb-4">
                            <h5 class="fw-bold mb-1 text-dark">{{ $item->student_name }}</h5>
                            <p class="text-muted small mb-3">{{ $item->student_id }} · Kelas {{ $item->grade_class ?? '-' }}</p>
                            
                            <div class="bg-white p-3 rounded-4 d-inline-block shadow-sm mb-3 border">
                                @if(class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class))
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(190)->generate($item->account_number ?? $item->student_id) !!}
                                @else
                                    <div class="p-4 text-muted small">Kode QR Siswa</div>
                                @endif
                            </div>
                            
                            <p class="fw-bold text-dark fs-5 tracking-wide mb-1">{{ $item->account_number ?? $item->student_id }}</p>
                            <p class="text-muted small mb-0">Tunjukkan kartu ini kepada Petugas Teller</p>
                        </div>
                        <div class="modal-footer border-0 pt-0 justify-content-center">
                            <button type="button" class="btn-primary-navy w-100 justify-content-center" onclick="window.print()">
                                <i class="bi bi-printer-fill me-1"></i> Cetak Kartu
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Edit Data Siswa -->
            <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-content-glass">
                        <div class="modal-header border-0 pb-0 justify-content-between">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="bi bi-pencil-square text-warning me-2"></i> Edit Data Siswa
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <form action="{{ route('admin.savers.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body py-3">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Nama Lengkap Siswa</label>
                                    <input type="text" name="student_name" class="form-control rounded-3" value="{{ $item->student_name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Nomor Induk Siswa (NIS)</label>
                                    <input type="text" name="student_id" class="form-control rounded-3" value="{{ $item->student_id }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Kelas / Rombel</label>
                                    <input type="text" name="grade_class" class="form-control rounded-3" value="{{ $item->grade_class }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Ganti PIN Keamanan (Opsional)</label>
                                    <input type="password" name="security_pin" class="form-control rounded-3" maxlength="6" placeholder="Biarkan kosong jika tidak diubah">
                                    <small class="text-muted" style="font-size: 0.72rem;">PIN harus terdiri dari 6 digit angka rahasia.</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary-navy">
                                    <i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Reset Password Nasabah (Plan B Darurat) -->
            <div class="modal fade" id="modalResetPass{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content modal-content-glass text-center">
                        <div class="modal-header border-0 pb-0 justify-content-end">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body pt-0 pb-4 px-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 54px; height: 54px; background: rgba(217, 119, 6, 0.12); color: #D97706;">
                                <i class="bi bi-key-fill fs-3"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Reset Password?</h5>
                            <p class="text-muted small mb-3">
                                Apakah Anda yakin ingin mereset password nasabah <strong>{{ $item->student_name }}</strong> ke default: <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace">123456</span>?
                            </p>
                            <form action="{{ route('admin.savers.reset-password', $item->id) }}" method="POST">
                                @csrf
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3 fw-semibold text-dark shadow-sm">
                                        <i class="bi bi-check-circle-fill me-1"></i> Ya, Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Blokir Rekening Nasabah -->
            <div class="modal fade" id="modalBlock{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-content-glass">
                        <div class="modal-header border-0 pb-0 justify-content-between">
                            <h5 class="modal-title fw-bold text-danger">
                                <i class="bi bi-lock-fill me-2"></i> Blokir Rekening Nasabah
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <form action="{{ route('admin.savers.block', $item->id) }}" method="POST">
                            @csrf
                            <div class="modal-body py-3">
                                <div class="alert alert-danger border-0 rounded-4 p-3 small mb-3">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Pemblokiran rekening akan menonaktifkan transaksi <strong>Setoran Tunai</strong>, <strong>Penarikan Tunai</strong>, dan akses login aplikasi nasabah.
                                </div>
                                <div class="p-3 rounded-3 mb-3 border bg-light">
                                    <div class="fw-bold text-dark">{{ $item->student_name }}</div>
                                    <div class="text-muted small">No. Rekening: <strong>{{ $item->account_number ?? '-' }}</strong> · NIS: {{ $item->student_id }}</div>
                                    <div class="text-success small fw-semibold mt-1">Saldo Tersimpan: Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark">Alasan Pemblokiran (Opsional)</label>
                                    <input type="text" name="reason" class="form-control rounded-3" placeholder="Contoh: Kartu/Buku tabungan hilang, permintaan orang tua/wali">
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm">
                                    <i class="bi bi-shield-lock-fill me-1"></i> Ya, Blokir Rekening Ini
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

@endsection