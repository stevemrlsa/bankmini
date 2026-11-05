<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Nasabah</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #28a745; color: white; padding: 10px; border: none; border-radius: 4px; width: 100%; font-size: 16px; cursor: pointer; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body>
<div class="card">
    <h2 style="text-align: center;">Portal Tabungan Siswa</h2>
    
    @if(session('error')) <div class="alert-error">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="alert-error">{{ $errors->first() }}</div> @endif

    <form action="{{ route('saver.login.submit') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nomor Rekening</label>
            <input type="text" name="account_number" placeholder="Masukkan nomor rekening..." required>
        </div>
        <div class="form-group">
            <label>PIN Keamanan (6 Digit)</label>
            <input type="password" name="security_pin" maxlength="6" placeholder="Masukkan PIN 6 digit..." required>
        </div>
        <button type="submit">Masuk ke Buku Tabungan</button>
    </form>
</div>
</body>
</html>