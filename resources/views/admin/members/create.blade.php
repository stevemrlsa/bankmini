@extends('layouts.app')

@section('title', 'Tambah Anggota Baru — Bank Mini')
@section('header_title', 'Data Anggota')
@section('page-title', 'Tambah Anggota Baru')

@section('content')
<div class="card-glass" style="max-width: 600px; margin: 0 auto;">
    <h5 class="mb-4">Tambah Anggota (Siswa)</h5>

    <form action="{{ route('admin.members.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label class="form-label">NIS (Nomor Induk Siswa)</label>
            <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id') }}" required>
            @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Siswa</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Angkatan Keluar (Tahun)</label>
            <input type="text" name="graduation_year" class="form-control @error('graduation_year') is-invalid @enderror" value="{{ old('graduation_year') }}" placeholder="Contoh: 2026" required>
            @error('graduation_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <hr class="my-4">
        <h6 class="mb-3 text-muted">Data Akun Login (Portal Nasabah)</h6>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Anggota</button>
        </div>
    </form>
</div>
@endsection
