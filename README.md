<div align="center">

  <img src="public/images/logo-smkn1bantul.png" alt="Logo SMKN 1 Bantul" width="100" style="margin-bottom: 12px;"/>

  # 🏦 Bank Mini SMKN 1 Bantul
  ### *Sistem Informasi E-Teller & Layanan Perbankan Mini Sekolah Terintegrasi*

  [![Laravel Version](https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![PHP Version](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![Flutter](https://img.shields.io/badge/Flutter-3.41-02569B?style=for-the-badge&logo=flutter&logoColor=white)](https://flutter.dev)
  [![Database](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
  [![CI/CD](https://img.shields.io/badge/GitHub_Actions-Automated_FTP_Deploy-2088FF?style=for-the-badge&logo=github-actions&logoColor=white)](https://github.com/features/actions)
  [![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

  <p align="center">
    <b>🌐 Live Production Web:</b> <a href="https://minibank.my.id">https://minibank.my.id</a><br/>
    <b>📱 Unduh Aplikasi Nasabah (APK):</b> <a href="https://minibank.my.id/download-apk">Unduh BankMini-Nasabah.apk</a>
  </p>

</div>

---

## 📌 Tentang Proyek

**Bank Mini SMKN 1 Bantul** adalah platform perbankan mini sekolah komprehensif yang dirancang untuk mendukung operasional loket keuangan sekolah secara akuntabel, transparan, dan *real-time*. Sistem ini mengintegrasikan **Web Portal Petugas (Administrator & Teller)** berbasis Laravel dengan **Aplikasi Mobile Nasabah (Siswa & Warga Sekolah)** berbasis Flutter Android melalui RESTful API Sanctum berstandar industri.

Proyek ini dikembangkan secara terstruktur mengikuti spesifikasi standar **Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak**, prinsip pembukuan akuntansi perbankan *double-entry*, serta proteksi fisik laci teller untuk menjamin ketiadaan selisih kas.

---

## 🚀 Fitur Unggulan Sistem

### 👑 1. Portal Administrator
- **Dashboard Statistik Finansial:** Pantau total saldo beredar, perputaran kas harian, ringkasan saldo per kelas, dan status laci operasional secara *real-time*.
- **Manajemen Data Anggota & Nasabah:**
  - Import data anggota siswa massal dari format Excel / Spreadsheet.
  - Dukungan nasabah **Siswa** maupun **Non-Siswa** (Guru, Staf/Karyawan TU, Warga Sekolah/Kantin) dengan auto-generate nomor rekening dan ID unik (`GR-`, `ST-`, `WS-`).
  - Fitur **Kenaikan Kelas Massal** otomatis (Siswa kelas X $\rightarrow$ XI, XI $\rightarrow$ XII, XII $\rightarrow$ Alumni Lulus) dengan proteksi pengecualian nasabah non-siswa.
- **Approval Kas Harian & Vault Transfer (Brankas):**
  - Verifikasi laporan penutupan kas teller harian.
  - Pengecekan rekonsiliasi uang fisik (termasuk validasi lembaran/koin 8 pecahan uang).
  - Otorisasi pemindahan saldo fisik laci kasir ke **Brankas Utama (Vault)** secara otomatis.
- **Jurnal Akuntansi Double-Entry Otomatis:**
  - Setiap transaksi mencatat Debit & Kredit presisi pada Kode Akun:
    - `101` - Kas Laci Teller
    - `102` - Brankas Utama (Vault)
    - `201` - Tabungan Nasabah
- **Audit Log & Laporan Mutasi Komprehensif:**
  - Seluruh aktivitas pengguna (*login, transaksi, approval, reset kredensial*) tercatat permanen pada sistem audit trail.
  - Cetak dan unduh Laporan Mutasi Nasabah format PDF resmi berlogo sekolah.

---

### 💼 2. Portal Loket Teller
- **Sistem Shift Kasir & Laci Kas Bersama (Multi-Teller):**
  - Wajib input modal awal (*Opening Balance*) sebelum loket dapat melayani transaksi.
  - Sinkronisasi saldo kas fisik laci bersama secara *real-time* antar sesama teller.
- **One-Stop Aktivasi Rekening:**
  - Pendaftaran akun, pembuatan nomor rekening otomatis, aktivasi PIN 6-digit, serta setoran pertama (minimal Rp 10.000) diselesaikan dalam satu alur cepat.
  - Form khusus aktivasi nasabah non-siswa (Guru / Pegawai / Kantin).
- **Setoran & Penarikan Tunai Cepat:**
  - Input cepat menggunakan **Scan QR Code Kamera** kartu identitas siswa atau ketik nomor rekening.
  - Validasi ketat penarikan:
    - Saldo mengendap minimal Rp 10.000.
    - Otorisasi PIN rahasia 6-digit nasabah.
    - **Proteksi Kas Fisik Laci:** Penarikan ditolak otomatis oleh sistem jika uang fisik di laci loket teller tidak mencukupi (mencegah defisit kas).
- **Cetak Struk Transaksi Resmi:**
  - Cetak struk instan pasca-transaksi dengan layout presisi, font proporsional, dan terpusat di tengah kertas (*thermal* maupun A4/Letter).
- **Penutupan Kas & Perhitungan 8 Pecahan Uang Tunai:**
  - Rekonsiliasi otomatis: $\text{Modal Awal} + \text{Total Setoran} - \text{Total Penarikan} = \text{Target Saldo Sistem}$.
  - Formulir rincian uang fisik laci kasir berdasarkan 8 pecahan mata uang rupiah:
    - `Rp 100.000` | `Rp 50.000` | `Rp 20.000` | `Rp 10.000`
    - `Rp 5.000` | `Rp 2.000` | `Rp 1.000` | `Rp 500`
  - Validasi selisih kas fisik dan catatan pertanggungjawaban sebelum dikirim ke Admin.
- **Cetak Laporan Mutasi Teller (PDF):**
  - Cetak mutasi transaksi harian atau bulanan dengan pilihan lingkup tugas (hanya transaksi teller bersangkutan atau seluruh loket).

---

### 📱 3. Aplikasi Mobile Nasabah (Flutter Android)
- **Desain Modern & Responsif:** Tampilan antarmuka intuitif dengan skema warna elegan (Navy & Teal).
- **Kartu Identitas Digital:** Dilengkapi QR Code dinamis untuk memudahkan pemindaian kartu di loket teller sekolah.
- **Pemantauan Saldo & Mutasi Real-Time:** Menampilkan saldo aktif dan riwayat transaksi kredit/debit lengkap.
- **Cetak / Ekspor Mutasi Rekening:** Fitur filter periode mutasi (Hari ini, 7 hari, 30 hari, bulan ini) serta ekspor cetak dokumen mutasi langsung dari smartphone.
- **Keamanan Akun:**
  - Autentikasi token Sanctum aman.
  - Layanan ubah kata sandi & ubah PIN transaksi 6-digit dengan proteksi OTP.

---

## 🛠️ Tech Stack & Arsitektur

| Komponen | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 11 / 12 (PHP 8.3) | Arsitektur MVC, Eloquent ORM, Database Transactions & Locking |
| **Frontend Web** | Blade, Bootstrap 5.3, Bootstrap Icons | Desain Glassmorphism, CSS Variables, Responsive Sidebar Layout |
| **Mobile Application**| Flutter 3.41 (Dart 3.11) | State Management, HTTP REST API, Pinput, Shared Preferences |
| **Database Engine** | MySQL / MariaDB (InnoDB) | Foreign Keys Cascade, Row-Level Locking (`lockForUpdate`) |
| **Authentication** | Session Guard & Laravel Sanctum | Pemisahan otentikasi Web Petugas dan Mobile API Nasabah |
| **Dokumen & Cetak** | DomPDF & Native Print CSS | Kompilasi PDF server-side & layout struk presisi di peramban |
| **Deployment / CI-CD**| GitHub Actions + FTP Deploy | Otomatis kompilasi Vite aset, sync hosting DomaiNesia, migrate & cache clear |

---

## 🏗️ Struktur Skema Akun Akuntansi (Double-Entry)

Sistem menerapkan kaidah perbankan di mana tabungan nasabah merupakan kewajiban (*liabilitas*) bagi bank mini:

```
+-----------------------------------------------------------------------------+
| Transaksi Setoran Tunai Rp 50.000:                                          |
|   (Debit)  101 - Kas Laci Teller               Rp 50.000                     |
|   (Kredit) 201 - Tabungan Nasabah                            Rp 50.000      |
+-----------------------------------------------------------------------------+
| Transaksi Penarikan Tunai Rp 20.000:                                        |
|   (Debit)  201 - Tabungan Nasabah              Rp 20.000                     |
|   (Kredit) 101 - Kas Laci Teller                             Rp 20.000      |
+-----------------------------------------------------------------------------+
| Approval Tutup Kas & Pemindahan ke Brankas Rp 30.000:                       |
|   (Debit)  102 - Brankas Utama (Vault)         Rp 30.000                     |
|   (Kredit) 101 - Kas Laci Teller                             Rp 30.000      |
+-----------------------------------------------------------------------------+
```

---

## 💻 Panduan Instalasi Lokal (Local Setup)

### Prasyarat:
* **PHP** $\ge$ 8.2 (Disarankan PHP 8.3 dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `zip`, `intl`)
* **Composer** $\ge$ 2.x
* **Node.js** $\ge$ 20.x & **npm**
* **MySQL / MariaDB** (melalui Laragon / XAMPP)
* **Flutter SDK** $\ge$ 3.x *(Opsional, untuk kompilasi mobile app)*

---

### 1. Menjalankan Web Backend (Laravel)

```bash
# 1. Clone repositori
git clone https://github.com/stevemrlsa/bankmini.git
cd bankmini

# 2. Install dependensi Composer & Node
composer install
npm install

# 3. Konfigurasi Environment File
cp .env.example .env
php artisan key:generate

# 4. Atur kredensial database di file .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=bankmini
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Jalankan migrasi dan seeder data demo
php artisan migrate:fresh --seed

# 6. Buat tautan storage publik (untuk logo & unduhan APK)
php artisan storage:link

# 7. Kompilasi aset Vite & jalankan server lokal
npm run build
php artisan serve
```

Akses portal web pada peramban: `http://localhost:8000` atau melalui virtual host Laragon `http://bankmini.test`.

---

### 2. Menjalankan Aplikasi Mobile (Flutter)

```bash
# Buka direktori aplikasi mobile
cd /path/to/mobile_nasabah_bankmini/bankmini_nasabah

# Unduh paket Flutter
flutter pub get

# Jalankan pengujian analisis kode
flutter analyze

# Jalankan aplikasi pada emulator atau HP fisik
flutter run

# Build berkas APK rilis final
flutter build apk --release
```
*Berkas hasil build APK berada di: `build/app/outputs/flutter-apk/app-release.apk`.*

---

## 🔑 Kredensial Akun Pengujian (Demo Accounts)

Untuk mempermudah demonstrasi dan penilaian, gunakan akun bawaan yang telah disiapkan oleh database seeder:

| Peran (Role) | Username / ID | Password | Akses & Hak Istimewa |
| :--- | :--- | :--- | :--- |
| **Administrator Utama** | `admin` | `admin123` | Akses penuh dashboard, approval kas, jurnal akuntansi, manajemen pegawai & nasabah. |
| **Administrator Pusat** | `admin_pusat` | `admin123` | Hak penuh manajemen dan audit finansial. |
| **Loket Teller** | `teller_satu` | `teller123` | Buka shift kas, transaksi setor/tarik, cetak struk, dan penutupan kas harian. |
| **Teller Operasional** | `teller_dua` | `password123` | Layanan loket kasir & aktivasi rekening baru. |
| **Nasabah (Siswa Aktif)** | `20240101` / NIS | PIN: `123456` | Login aplikasi mobile, cek saldo, mutasi, kartu QR digital. |

---

## 🧪 Pengujian Otomatis (Automated Testing)

Aplikasi dilengkapi unit dan feature testing lengkap untuk menjamin kestabilan logika perbankan:

```bash
# Menjalankan seluruh rangkaian test
php artisan test

# Menguji fitur revisi internal (Pecahan uang, nasabah non-siswa, struk cetak, logout)
php artisan test --filter=InternalReviewRevisionsTest

# Menguji generasi laporan PDF mutasi nasabah & teller
php artisan test --filter=MutationReportPdfTest
```

---

## 👨‍💻 Pengembang Proyek

* **Institusi:** SMK Negeri 1 Bantul (Yogyakarta)
* **Jurusan:** Rekayasa Perangkat Lunak (RPL)
* **Pengembang:** Fadhilah Marele Saputro ([@stevemrlsa](https://github.com/stevemrlsa))
* **Tahun Operasional:** 2026

---

## 📄 Lisensi

Proyek ini didistribusikan di bawah lisensi terbuka [MIT License](LICENSE). Seluruh kode dapat dipelajari, dikembangkan, dan dimanfaatkan untuk kemajuan pendidikan kejuruan teknologi informasi.
