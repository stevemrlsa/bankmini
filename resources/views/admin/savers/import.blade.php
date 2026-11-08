@extends('layouts.app')

@section('title', 'Import Data Anggota — Bank Mini')
@section('header_title', 'Data Anggota')
@section('page-title', 'Unggah Data Siswa Baru (Bulk Onboarding)')
@section('page-subtitle', 'Pendaftaran massal nasabah siswa menggunakan dokumen spreadsheet Excel')

@push('styles')
<style>
    .import-hero-card {
        border-radius: 22px;
        padding: 26px 30px;
        background: linear-gradient(120deg, #16233F 0%, #26355C 55%, #3B5BA5 100%);
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 16px 36px rgba(22, 35, 63, 0.16);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(22, 35, 63, 0.06);
        padding: 28px;
        height: 100%;
    }

    .dropzone-box {
        border: 2px dashed #CBD5E1;
        border-radius: 18px;
        padding: 36px 20px;
        text-align: center;
        background: #F8FAFC;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .dropzone-box:hover, .dropzone-box.dragover {
        border-color: #3B5BA5;
        background: rgba(59, 91, 165, 0.04);
        transform: translateY(-2px);
    }

    .dropzone-icon {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: rgba(59, 91, 165, 0.1);
        color: #3B5BA5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 14px;
    }

    .step-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #16233F;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 10px;
    }

    .btn-navy {
        background: linear-gradient(135deg, #16233F, #26355C);
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 0.92rem;
        box-shadow: 0 10px 20px rgba(22, 35, 63, 0.2);
        transition: all 0.2s ease;
    }

    .btn-navy:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(22, 35, 63, 0.28);
    }

    .btn-template {
        background: #F1F5F9;
        color: #1E293B;
        border: 1px solid #CBD5E1;
        padding: 11px 20px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-template:hover {
        background: #E2E8F0;
        color: #0F172A;
    }
</style>
@endpush

@section('content')

    <!-- Flash Alert -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <!-- Tombol Navigasi Kembali -->
    <div class="mb-3">
        <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-link text-decoration-none text-muted p-0 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Data Anggota
        </a>
    </div>

    <!-- Banner Informasi Onboarding Massal -->
    <div class="import-hero-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3 class="fw-bold mb-1">Pendaftaran Massal Rekening Siswa Baru</h3>
                <p class="mb-0 text-white-50" style="font-size: 0.9rem;">
                    Impor ribuan data siswa sekolah secara instan menggunakan format file Excel (.xlsx / .csv).
                </p>
            </div>
            <a href="{{ route('admin.members.template') }}" class="btn-template bg-white text-dark border-0 shadow-sm">
                <i class="bi bi-file-earmark-arrow-down-fill text-success fs-5"></i> Unduh Template Excel (.xlsx)
            </a>
        </div>
    </div>

    @if(!($canImport ?? true))
        <div class="alert alert-warning border-0 rounded-4 p-4 mb-4 shadow-sm" style="background: #FFFBEB; border-left: 6px solid #F59E0B !important;">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle p-2 bg-warning text-dark fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-dark mb-1">Import Siswa Baru Terkunci (Wajib Kenaikan Kelas Dahulu)</h5>
                    <p class="text-secondary small mb-3" style="line-height: 1.6;">
                        Sistem mendeteksi data siswa yang belum melalui proses <strong>Kenaikan Kelas Massal</strong> untuk Tahun Ajaran {{ $currentYear ?? date('Y') }}.<br>
                        Sebelum mengimpor data rombel siswa baru, seluruh siswa lama wajib dinaikkan kelasnya terlebih dahulu (X &rarr; XI, XI &rarr; XII, XII &rarr; Lulus/Alumni) agar data kelas dan saldo tabungan tidak saling bertabrakan.
                    </p>
                    <a href="{{ route('admin.savers.index') }}" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-3 shadow-sm">
                        <i class="bi bi-mortarboard-fill me-1"></i> Buka Data Nasabah &amp; Lakukan Naik Kelas Massal
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Kiri: Formulir Upload -->
        <div class="col-12 col-lg-7">
            <div class="glass-card">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i> Unggah File Excel Siswa
                </h5>
                <p class="text-muted small mb-4">
                    Pastikan susunan kolom pada spreadsheet Anda sudah sesuai dengan format template yang disediakan.
                </p>

                <form action="{{ route('admin.members.import') }}" method="POST" enctype="multipart/form-data" id="formImportExcel">
                    @csrf

                    <!-- Area Dropzone -->
                    <div class="dropzone-box mb-4 {{ !($canImport ?? true) ? 'opacity-50' : '' }}" id="dropzoneArea" @if($canImport ?? true) onclick="document.getElementById('excelFileInput').click();" @endif style="{{ !($canImport ?? true) ? 'cursor: not-allowed; background: #f1f5f9;' : '' }}">
                        <div class="dropzone-icon">
                            <i class="bi {{ !($canImport ?? true) ? 'bi-lock-fill text-secondary' : 'bi-file-earmark-spreadsheet-fill' }}"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" id="fileLabel">
                            {{ !($canImport ?? true) ? 'Import Terkunci — Harap Naikkan Kelas Siswa Dahulu' : 'Pilih File .xlsx atau Tarik ke Sini' }}
                        </h6>
                        <p class="text-muted small mb-2" id="fileSubLabel">
                            {{ !($canImport ?? true) ? 'Tombol unggah akan aktif otomatis setelah kenaikan kelas massal diproses.' : 'Mendukung format Microsoft Excel (.xlsx, .xls) atau CSV (Maksimal 10MB)' }}
                        </p>
                        @if($canImport ?? true)
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-semibold mt-2">
                                Pilih Dokumen
                            </button>
                        @endif
                        <input
                            type="file"
                            id="excelFileInput"
                            name="excel_file"
                            class="d-none"
                            accept=".xlsx, .xls, .csv"
                            required
                            {{ !($canImport ?? true) ? 'disabled' : '' }}
                        >
                    </div>

                    <!-- Ringkasan File Terpilih -->
                    <div id="fileInfoCard" class="p-3 rounded-3 bg-light border d-none mb-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-file-earmark-check-fill text-success fs-3"></i>
                                <div>
                                    <strong class="d-block text-dark small" id="selectedFileName">-</strong>
                                    <small class="text-muted" id="selectedFileSize">-</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" id="btnRemoveFile">
                                <i class="bi bi-trash-fill me-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.members.index') }}" class="btn btn-light px-4 py-2 rounded-3 text-muted fw-semibold" style="font-size:0.9rem;">
                            Batal
                        </a>
                        <button type="submit" class="btn-navy" id="btnSubmitImport" {{ !($canImport ?? true) ? 'disabled' : '' }}>
                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Data Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Panduan Struktur Format Kolom -->
        <div class="col-12 col-lg-5">
            <div class="glass-card">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-info-circle-fill text-info me-2"></i> Panduan Kolom Spreadsheet
                </h5>
                <p class="text-muted small mb-3">
                    File Excel wajib memiliki urutan 4 kolom berikut mulai dari baris ke-2:
                </p>

                <div class="table-responsive rounded-3 border mb-3">
                    <table class="table table-sm table-striped mb-0" style="font-size: 0.8rem;">
                        <thead class="table-dark">
                            <tr>
                                <th>Kolom</th>
                                <th>Nama Header</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>A (0)</strong></td>
                                <td>Nama Siswa</td>
                                <td>Nama lengkap calon nasabah</td>
                            </tr>
                            <tr>
                                <td><strong>B (1)</strong></td>
                                <td>NIS</td>
                                <td>Nomor Induk Siswa (Wajib Unik)</td>
                            </tr>
                            <tr>
                                <td><strong>C (2)</strong></td>
                                <td>Kelas</td>
                                <td>Rombongan belajar (cth: X RPL 1)</td>
                            </tr>
                            <tr>
                                <td><strong>D (3)</strong></td>
                                <td>Jenis Kelamin</td>
                                <td>Huruf <strong>L</strong> atau <strong>P</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 rounded-3" style="background: #EFF6FF; border: 1px solid #BFDBFE;">
                    <h6 class="fw-bold text-primary mb-2" style="font-size: 0.82rem;">
                        <i class="bi bi-shield-lock-fill me-1"></i> Kebijakan Akun Baru:
                    </h6>
                    <ul class="text-secondary small mb-0 ps-3" style="font-size: 0.78rem;">
                        <li><strong>Nomor Rekening:</strong> Dibiarkan kosong (NULL) hingga siswa melakukan Setoran Awal di loket teller.</li>
                        <li><strong>Status Akun:</strong> Otomatis diatur menjadi <code>Nonaktif (inactive)</code>.</li>
                        <li><strong>PIN Transaksi & Password:</strong> Diberikan nilai standar bawaan <code>123456</code>.</li>
                        <li><strong>Saldo Awal:</strong> Ditetapkan Rp 0.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput     = document.getElementById('excelFileInput');
    const dropzone      = document.getElementById('dropzoneArea');
    const fileInfoCard  = document.getElementById('fileInfoCard');
    const fileNameEl    = document.getElementById('selectedFileName');
    const fileSizeEl    = document.getElementById('selectedFileSize');
    const btnRemoveFile = document.getElementById('btnRemoveFile');
    const btnSubmit     = document.getElementById('btnSubmitImport');
    const formImport    = document.getElementById('formImportExcel');

    // Drag and Drop Events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            updateFileInfo();
        }
    });

    fileInput.addEventListener('change', updateFileInfo);

    function updateFileInfo() {
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            fileNameEl.textContent = file.name;
            fileSizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            fileInfoCard.classList.remove('d-none');
            dropzone.classList.add('d-none');
        }
    }

    btnRemoveFile.addEventListener('click', function () {
        fileInput.value = '';
        fileInfoCard.classList.add('d-none');
        dropzone.classList.remove('d-none');
    });

    formImport.addEventListener('submit', function () {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses Unggahan...';
    });
});
</script>
@endpush
