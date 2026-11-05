@extends('layouts.app')

@section('title', 'Pengaturan Sistem — Bank Mini')
@section('header_title', 'Pengaturan Sistem')
@section('page-title', 'Konfigurasi Sistem')
@section('page-subtitle', 'Pengaturan parameter global Bank Mini Sekolah')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 20px;
        border: 1px solid rgba(22, 35, 63, 0.08);
        box-shadow: 0 14px 34px rgba(22, 35, 63, 0.06);
        padding: 32px 36px;
        position: relative;
        overflow: hidden;
    }

    .glass-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(90deg, #16233F, #3B5BA5, #0F766E);
    }

    .form-label-premium {
        font-size: 0.88rem;
        font-weight: 600;
        color: #1C2536;
        margin-bottom: 8px;
    }

    .form-control-premium {
        border-radius: 12px;
        border: 1px solid rgba(22, 35, 63, 0.12);
        padding: 12px 16px;
        font-size: 0.95rem;
        font-family: 'Poppins', sans-serif;
        background: #fff;
        transition: all 0.2s ease;
    }

    .form-control-premium:focus {
        border-color: #3B5BA5;
        box-shadow: 0 0 0 4px rgba(59, 91, 165, 0.1);
        outline: none;
    }

    .input-group-text-premium {
        background: #F7F9F9;
        border: 1px solid rgba(22, 35, 63, 0.12);
        border-right: none;
        color: #5C6579;
        font-weight: 500;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .btn-primary-navy {
        background: linear-gradient(135deg, #16233F, #0E1830);
        border: none;
        color: #fff;
        font-size: 0.9rem;
        font-weight: 500;
        padding: 12px 28px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 10px 20px rgba(22, 35, 63, 0.15);
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .btn-primary-navy:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 14px 24px rgba(22, 35, 63, 0.22);
    }

    .setting-desc {
        font-size: 0.82rem;
        color: #808C9E;
        margin-top: 6px;
        line-height: 1.5;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-7 col-md-9 mx-auto">
        <div class="glass-card mt-4">
            <h4 class="mb-4 text-dark font-weight-bold"><i class="bi bi-gear-fill me-2 text-primary"></i> Pengaturan Saldo</h4>
            
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="form-label-premium">Batas Saldo Mengendap Minimum</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-premium">Rp</span>
                        <input type="number" 
                               name="min_saldo_mengendap" 
                               class="form-control form-control-premium" 
                               value="{{ $minSaldo->value ?? 10000 }}" 
                               min="0" step="5000" required>
                    </div>
                    <div class="setting-desc">
                        {{ $minSaldo->description ?? 'Batas saldo minimum yang tidak bisa ditarik oleh nasabah.' }} 
                        Jika nasabah menarik uang dan menyisakan saldo di bawah angka ini, sistem akan otomatis menolak transaksi penarikan tersebut.
                    </div>
                </div>

                <hr class="my-4" style="border-color: rgba(0,0,0,0.05);">

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn-primary-navy">
                        <i class="bi bi-save2-fill"></i> Simpan Konfigurasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
