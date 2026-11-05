@extends('layouts.app')

@section('title', 'Edit Anggota — Bank Mini')
@section('header_title', 'Data Anggota')
@section('page-title', 'Edit Anggota')

@section('content')
<div class="card-glass" style="max-width: 600px; margin: 0 auto;">
    <h5 class="mb-4">Edit Data Anggota (Siswa)</h5>

    <form action="{{ route('admin.members.update', $member->user_id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label class="form-label">NIS (Nomor Induk Siswa)</label>
            <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $member->student_id) }}" required>
            @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Siswa</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $member->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Angkatan (Tahun)</label>
            <input type="text" name="graduation_year" class="form-control @error('graduation_year') is-invalid @enderror" value="{{ old('graduation_year', $member->graduation_year) }}" required>
            @error('graduation_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Status Rekening</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="inactive" {{ old('status', $member->status) === 'inactive' ? 'selected' : '' }}>Rekening Tidak Aktif (Inactive)</option>
                <option value="active" {{ old('status', $member->status) === 'active' ? 'selected' : '' }}>Rekening Aktif (Active)</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Ganti 6-Digit PIN Keamanan (Kosongkan jika tidak diubah)</label>
            <input type="password" name="security_pin" class="form-control @error('security_pin') is-invalid @enderror" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="6 digit angka baru">
            @error('security_pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
