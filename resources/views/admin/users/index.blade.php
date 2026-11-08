@extends('layouts.app')

@section('header_title', 'Manajemen Pegawai')
@section('title', 'Manajemen Pegawai')

@push('styles')
<style>
    /* ===== Toolbar Card ===== */
    .toolbar-card {
        background: var(--white-glass-strong, rgba(255, 255, 255, 0.85));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 18px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
        box-shadow: 0 8px 24px rgba(22, 35, 63, 0.05);
    }

    .btn-primary-navy {
        background: linear-gradient(135deg, #16233F, #0E1830);
        border: none;
        color: #fff;
        font-size: 0.86rem;
        font-weight: 500;
        padding: 10px 22px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 10px 20px rgba(22, 35, 63, 0.22);
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .btn-primary-navy:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 24px rgba(22, 35, 63, 0.28);
    }

    /* ===== Tabel Pegawai ===== */
    .table-premium-wrap {
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(22, 35, 63, 0.07);
        box-shadow: 0 10px 26px rgba(22, 35, 63, 0.05);
    }

    table.table-premium {
        margin: 0;
        font-size: 0.88rem;
    }

    table.table-premium thead th {
        background: #F8FAFC;
        color: #475569;
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        padding: 15px 20px;
        white-space: nowrap;
    }

    table.table-premium tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border-top: 1px solid rgba(22, 35, 63, 0.06);
        color: #1E293B;
    }

    table.table-premium tbody tr:hover {
        background: rgba(59, 91, 165, 0.035);
    }

    .role-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 5px 14px;
        border-radius: 20px;
        display: inline-block;
    }

    .role-badge.admin, .role-badge.administrator {
        background: rgba(22, 35, 63, 0.1);
        color: #16233F;
        border: 1px solid rgba(22, 35, 63, 0.15);
    }

    .role-badge.teller {
        background: rgba(20, 184, 166, 0.12);
        color: #0F766E;
        border: 1px solid rgba(20, 184, 166, 0.25);
    }

    .action-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid rgba(22, 35, 63, 0.12);
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 0.9rem;
        transition: all 0.16s ease;
        padding: 0;
    }

    .action-btn:hover {
        background: #F1F5F9;
        color: #0F172A;
        transform: translateY(-1px);
    }

    .action-btn.danger:hover {
        background: #FEE2E2;
        color: #DC2626;
        border-color: #FCA5A5;
    }

    .action-btn.warning:hover {
        background: #FEF3C7;
        color: #D97706;
        border-color: #FCD34D;
    }

    .action-btn.success:hover {
        background: #DCFCE7;
        color: #16A34A;
        border-color: #86EFAC;
    }
</style>
@endpush

@section('content')
<div class="mb-4">
    {{-- Toolbar: Pencarian dan Tombol Tambah --}}
    <div class="toolbar-card">
        <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 460px;">
            <div class="input-group">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Cari nama, username, atau role pegawai..." 
                       value="{{ request('search') }}"
                       style="border-radius: 12px 0 0 12px;">
                <button type="submit" class="btn btn-primary px-3 d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #16233F, #0E1830); border: none; border-radius: 0 12px 12px 0;" title="Cari Pegawai">
                    <i class="bi bi-search"></i>
                    <span>Cari</span>
                </button>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Pencarian">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            @endif
        </form>

        <div>
            <button type="button" class="btn-primary-navy" data-bs-toggle="modal" data-bs-target="#modalTambahPegawai">
                <i class="bi bi-plus-lg"></i> Tambah Pegawai
            </button>
        </div>
    </div>

    {{-- Tabel Pegawai --}}
    <div class="table-premium-wrap">
        <div class="table-responsive">
            <table class="table table-premium align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th style="width: 170px;">Username</th>
                        <th>Nama Lengkap</th>
                        <th style="width: 150px;">Role</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 170px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $item)
                    <tr>
                        <td class="text-muted fw-semibold">{{ $users->firstItem() ? $users->firstItem() + $index : $index + 1 }}</td>
                        <td>
                            <code class="fw-bold fs-6">{{ $item->username }}</code>
                        </td>
                        <td>
                            {{-- REMOVE AVATAR BUG: Hanya menampilkan nama dalam bentuk teks polos --}}
                            <span class="fw-semibold text-dark">{{ $item->officer_name ?? $item->name }}</span>
                        </td>
                        <td>
                            @php
                                $roleClass = 'admin';
                                $r = strtolower($item->role ?? '');
                                if($r === 'teller') $roleClass = 'teller';
                            @endphp
                            <span class="role-badge {{ $roleClass }}">{{ $item->role ?? 'Administrator' }}</span>
                        </td>
                        <td>
                            @if($item->status === 'Aktif')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                    <i class="bi bi-dash-circle-fill me-1"></i> Terblokir
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1 align-items-center">
                                {{-- ROLE-BASED AUTHORIZATION: Edit Button HANYA untuk Administrator / Admin Utama --}}
                                @if(in_array(auth()->user()->role, ['Administrator', 'Admin Utama', 'admin_utama']))
                                    <button type="button" 
                                            class="action-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditPegawai{{ $item->id }}" 
                                            title="Edit Data Pegawai">
                                        <i class="bi bi-pencil-fill text-primary"></i>
                                    </button>
                                @endif

                                {{-- Toggle Status Akun (Aktif / Buka Blokir) --}}
                                <form action="{{ route('admin.users.toggle-status', $item->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin {{ $item->status === 'Aktif' ? 'memblokir' : 'membuka blokir' }} akun pegawai {{ $item->officer_name }}?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="action-btn {{ $item->status === 'Aktif' ? 'warning' : 'success' }}" 
                                            title="{{ $item->status === 'Aktif' ? 'Blokir Akun Pegawai' : 'Buka Blokir Akun Pegawai' }}">
                                        <i class="bi {{ $item->status === 'Aktif' ? 'bi-lock-fill text-warning' : 'bi-unlock-fill text-success' }}"></i>
                                    </button>
                                </form>

                                {{-- ROLE-BASED AUTHORIZATION: Delete Button HANYA untuk Administrator / Admin Utama --}}
                                @if(in_array(auth()->user()->role, ['Administrator', 'Admin Utama', 'admin_utama']))
                                    <form action="{{ route('admin.users.destroy', $item->id) }}" 
                                          method="POST" 
                                          class="d-inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pegawai {{ $item->officer_name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn danger" title="Hapus Akun Pegawai">
                                            <i class="bi bi-trash-fill text-danger"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-person-x display-6 text-muted mb-2 d-block"></i>
                            <span class="fw-semibold">Tidak ada data pegawai yang cocok dengan pencarian.</span>
                            <p class="small text-muted mb-0 mt-1">Coba gunakan kata kunci nama atau role lainnya.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top flex-wrap gap-2">
            <small class="text-muted">
                Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari total {{ $users->total() }} pegawai
            </small>
            <div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ======================================================== --}}
{{-- MODAL AREA (Ditempatkan di luar card/tabel untuk z-index) --}}
{{-- ======================================================== --}}

<!-- ===== Modal Tambah Pegawai ===== -->
<div class="modal fade" id="modalTambahPegawai" tabindex="-1" aria-labelledby="modalTambahPegawaiLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle p-2 text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="modalTambahPegawaiLabel">Tambah Pegawai Baru</h5>
                        <p class="text-muted small mb-0">Buat akun akses untuk Administrator atau Teller</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-body py-4 px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Lengkap Pegawai</label>
                        <input type="text" name="officer_name" class="form-control" placeholder="Contoh: Ahmad Fauzi" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Username Sistem</label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: ahmad.fauzi" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Alamat Email Pegawai</label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: ahmad.fauzi@bankmini.sch.id" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nomor WhatsApp</label>
                        <input type="text" name="whatsapp_number" class="form-control" placeholder="Contoh: 08123456789" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Role Akses</label>
                        <select name="role" class="form-select" style="border-radius: 12px;" required>
                            <option value="" selected disabled>Pilih role akses</option>
                            <option value="Administrator">Administrator (Admin Utama)</option>
                            <option value="Teller">Teller</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark">Kata Sandi Awal</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" style="border-radius: 12px;" required>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #16233F, #0E1830); border: none;">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Pegawai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== Modal Edit Pegawai ===== -->
@foreach($users as $item)
<div class="modal fade" id="modalEditPegawai{{ $item->id }}" tabindex="-1" aria-labelledby="modalEditPegawaiLabel{{ $item->id }}" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle p-2 text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="modalEditPegawaiLabel{{ $item->id }}">Edit Data Pegawai</h5>
                        <p class="text-muted small mb-0">{{ $item->officer_name }} ({{ $item->username }})</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $item->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body py-4 px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Lengkap Pegawai</label>
                        <input type="text" name="officer_name" class="form-control" value="{{ $item->officer_name }}" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Username Sistem</label>
                        <input type="text" name="username" class="form-control" value="{{ $item->username }}" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Alamat Email Pegawai</label>
                        <input type="email" name="email" class="form-control" value="{{ $item->email }}" placeholder="Contoh: ahmad.fauzi@bankmini.sch.id" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nomor WhatsApp</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="{{ $item->whatsapp_number }}" placeholder="Contoh: 08123456789" style="border-radius: 12px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Role Akses</label>
                        <select name="role" class="form-select" style="border-radius: 12px;" required>
                            <option value="Administrator" {{ $item->role === 'Administrator' ? 'selected' : '' }}>Administrator (Admin Utama)</option>
                            <option value="Teller" {{ $item->role === 'Teller' ? 'selected' : '' }}>Teller</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark">Kata Sandi Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah sandi" style="border-radius: 12px;">
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #16233F, #0E1830); border: none;">
                        <i class="bi bi-save-fill me-1"></i> Perbarui Pegawai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    // Safeguard backdrop dismissal for Bootstrap 5
    document.addEventListener('DOMContentLoaded', function () {
        const userModals = document.querySelectorAll('.modal');
        userModals.forEach(function (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.body.style.removeProperty('overflow');
            });
        });
    });
</script>
@endsection