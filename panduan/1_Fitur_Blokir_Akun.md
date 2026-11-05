# Panduan Pembuatan Fitur Blokir Akun Pengguna

Fitur ini merupakan syarat kelulusan dari Soal UKK (Poin III.1.a.1) yang mengharuskan Administrator memiliki wewenang untuk **memblokir** akun internal sistem. Saat ini sistemmu hanya memiliki fitur Tambah, Edit, dan Hapus. 

Berikut adalah langkah-langkah lengkap untuk mengerjakannya:

## Langkah 1: Tambahkan Kolom Status di Database
Kamu tidak perlu menghapus database lama, cukup buat migration baru untuk menambahkan satu kolom ke tabel `users`.

Buka terminal dan jalankan:
```bash
php artisan make:migration add_status_to_users_table
```

Buka file migration yang baru saja terbuat (lokasinya di `database/migrations/xxx_add_status_to_users_table.php`), lalu isi dengan kode berikut:
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Menambahkan kolom status dengan nilai default 'Aktif'
        $table->enum('status', ['Aktif', 'Blokir'])->default('Aktif')->after('role');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
```
Lalu jalankan `php artisan migrate`.

---

## Langkah 2: Daftarkan Kolom Status di Model
Buka file **`app/Models/User.php`** dan tambahkan `'status'` ke dalam array `$fillable`.

```php
protected $fillable = [
    'username',
    'password',
    'officer_name',
    'role',
    'status', // <--- Tambahkan ini
];
```

---

## Langkah 3: Update Controller Admin
Buka file **`app/Http/Controllers/Admin/UserController.php`**. 
Kamu perlu menambahkan logika agar Admin bisa memblokir atau mengaktifkan kembali akun. Cara termudah adalah membuat satu fungsi baru khusus untuk mengubah (toggle) status.

Tambahkan _method_ berikut di dalam `UserController.php`:
```php
public function toggleStatus(string $id)
{
    $user = User::findOrFail($id);
    
    // Cegah admin memblokir dirinya sendiri
    if (auth()->id() === $user->id) {
        return back()->with('error', 'Anda tidak dapat memblokir akun Anda sendiri!');
    }

    // Toggle status
    $user->status = ($user->status === 'Aktif') ? 'Blokir' : 'Aktif';
    $user->save();

    return back()->with('success', 'Status akun berhasil diubah menjadi ' . $user->status . '!');
}
```

Lalu daftarkan _route_ barunya di **`routes/web.php`** (di bawah route admin/users yang lain):
```php
Route::patch('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
```

---

## Langkah 4: Tampilkan Tombol di View HTML
Buka file **`resources/views/admin/users/index.blade.php`**.
1. Di bagian header tabel (`<thead>`), tambahkan kolom baru **"Status"**.
2. Di bagian perulangan tabel (`<tbody>`), cetak statusnya:
   ```blade
   <td>
       @if($user->status === 'Aktif')
           <span class="badge bg-success">Aktif</span>
       @else
           <span class="badge bg-danger">Blokir</span>
       @endif
   </td>
   ```
3. Di kolom "Aksi" (Tombol Edit/Hapus), tambahkan form untuk memblokir:
   ```blade
   <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="d-inline">
       @csrf
       @method('PATCH')
       <button type="submit" class="btn btn-sm btn-{{ $user->status === 'Aktif' ? 'warning' : 'success' }}">
           {{ $user->status === 'Aktif' ? 'Blokir' : 'Aktifkan' }}
       </button>
   </form>
   ```

---

## Langkah 5: Kunci Pintu Masuk (Proses Login)
Langkah terakhir dan paling penting: menolak user yang diblokir saat mereka mencoba login.
Buka file **`app/Http/Controllers/AuthController.php`**, cari metode `login()`:

```php
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // +++ TAMBAHKAN KODE INI +++
            if (Auth::user()->status === 'Blokir') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'username' => 'Akun Anda sedang diblokir oleh Administrator.',
                ])->onlyInput('username');
            }
            // +++++++++++++++++++++++++
            
            $userRole = Auth::user()->role; 
            // ... (kode switch role tetap sama)
```
Selesai! Sekarang fitur blokir akun berjalan dengan sempurna.
