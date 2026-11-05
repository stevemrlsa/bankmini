# Panduan Modul Konfigurasi Sistem Global (Settings)

Sesuai tuntutan soal (Poin III.1.a.1), Administrator harus bisa mengatur batas saldo mengendap minimum (Rp10.000) dan parameter sistem lainnya secara dinamis (tidak di-_hardcode_).

Berikut adalah cara membuat fitur konfigurasi sistem sederhana namun skalabel.

## Langkah 1: Buat Model dan Migration
Buka terminal dan jalankan:
```bash
php artisan make:model SystemSetting -m
```

Buka file migration (`database/migrations/xxx_create_system_settings_table.php`) dan isi schema tabelnya:
```php
public function up(): void
{
    Schema::create('system_settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique(); // Nama kunci (misal: min_saldo)
        $table->string('value'); // Nilainya (misal: 10000)
        $table->string('description')->nullable(); // Penjelasan setting
        $table->timestamps();
    });
}
```
Lalu jalankan `php artisan migrate`.

Di file **`app/Models/SystemSetting.php`**, tambahkan `$fillable`:
```php
protected $fillable = ['key', 'value', 'description'];
```

---

## Langkah 2: Buat Data Awal (Seeder)
Buat seeder untuk mengisi data batas saldo 10.000 ke database.
```bash
php artisan make:seeder SystemSettingSeeder
```

Buka `database/seeders/SystemSettingSeeder.php`:
```php
use App\Models\SystemSetting;

public function run(): void
{
    SystemSetting::create([
        'key' => 'min_saldo_mengendap',
        'value' => '10000',
        'description' => 'Batas saldo minimum yang tidak bisa ditarik oleh nasabah'
    ]);
}
```
Jangan lupa jalankan: `php artisan db:seed --class=SystemSettingSeeder`.

---

## Langkah 3: Panggil Settingan di Controller Teller
Sekarang kita ganti angka statis 10000 menjadi angka dinamis dari database. 
Buka file **`app/Http/Controllers/Teller/TransactionController.php`**.

Di bagian fungsi `storePenarikan`, cari logika saldo ini:
```php
// SEBELUMNYA:
if (($customer->saldo - $request->amount) < 10000) {
    throw new \Exception('Penarikan ditolak! Sisa saldo setelah penarikan minimal Rp 10.000.');
}
```

Ubah menjadi:
```php
// PERBAIKAN:
// Ambil settingan dari database, jika tidak ada, gunakan default 10000
$settingMinSaldo = \App\Models\SystemSetting::where('key', 'min_saldo_mengendap')->first();
$batasMinSaldo = $settingMinSaldo ? (int)$settingMinSaldo->value : 10000;

if (($customer->saldo - $request->amount) < $batasMinSaldo) {
    throw new \Exception('Penarikan ditolak! Sisa saldo setelah penarikan minimal Rp ' . number_format($batasMinSaldo, 0, ',', '.'));
}
```
Sekarang logika penarikanmu sudah mengikuti settingan dinamis!

---

## Langkah 4 (Tugas Kamu): Buat UI untuk Administrator
Agar Admin benar-benar bisa mengatur nilai tersebut secara global sesuai soal, kamu perlu:
1. Buat Controller baru `SettingsController` untuk Admin.
2. Buat fungsi `index()` untuk memunculkan form ubah "Batas Saldo Mengendap". Form ini memanggil data dari tabel `system_settings`.
3. Buat fungsi `update()` untuk menyimpan angka baru yang diinputkan Admin (menyimpan kembali ke tabel `system_settings`).
4. Buat halaman _view_ (Blade) yang berisi form sederhana.
5. Daftarkan _routes_-nya di dalam grup middleware `Administrator` (di dalam file `routes/web.php`). 

Karena kamu sudah bisa membuat CRUD untuk tabel user dan customer, fitur ini sangat mirip namun jauh lebih sederhana karena hanya mengubah isi field `value` pada baris `key` = `min_saldo_mengendap`. Selamat mencoba!
