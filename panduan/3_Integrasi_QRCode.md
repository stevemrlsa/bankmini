# Panduan Integrasi Kamera / Scanner QR Code

Bagian ini bernilai bobot evaluasi cukup besar (10%). Teller dituntut agar bisa melakukan pemindaian profil instan menggunakan kamera (Scanner/Webcam).

Kita akan menggunakan _library_ **HTML5-QRCode** yang ringan dan berjalan murni di peramban (JavaScript/Frontend), tanpa butuh driver alat scanner khusus.

## Langkah 1: Siapkan View Pencarian / Form Transaksi Teller
Tentukan halaman mana di dashboard Teller yang akan kamu pakai untuk memindai. Biasanya fitur ini dipasang pada halaman **Setoran** (`teller/setoran.blade.php`), halaman **Penarikan** (`teller/penarikan.blade.php`), atau halaman khusus **Dashboard / Cari Nasabah**.

Buka file tampilan (misal `teller/dashboard.blade.php`) dan tambahkan elemen UI untuk kamera ini.

```html
<!-- Tambahkan ini di tempat kamu ingin memunculkan kamera -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-qrcode"></i> Scan QR Rekening Nasabah
    </div>
    <div class="card-body text-center">
        <!-- Kotak reader kamera -->
        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
        
        <!-- Pesan hasil scan -->
        <p id="qr-reader-results" class="mt-3 font-weight-bold text-success"></p>
        
        <!-- Form pencarian rahasia yang otomatis dikirim (opsional) -->
        <form id="auto-search-form" action="{{ url('/api/search-customer') }}" method="GET" style="display:none;">
            <input type="text" name="q" id="scanned-account">
        </form>
    </div>
</div>
```

---

## Langkah 2: Sertakan Script Library HTML5-QRCode
Letakkan kode ini di bagian paling bawah halaman (sebelum tanda `</body>` atau di bagian `@section('scripts')` dari *layout* utama kamu). 
Kita memanggil pustaka JS langsung lewat CDN secara online (Pastikan komputer ujian terkoneksi internet minimal 2Mbps sesuai dokumen).

```html
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    // Fungsi ini akan dieksekusi jika kamera berhasil membaca suatu QR Code
    function onScanSuccess(decodedText, decodedResult) {
        // Tampilkan teks di UI
        document.getElementById('qr-reader-results').innerText = "Berhasil! Memuat Rekening: " + decodedText;

        // TUGAS KAMU: Tentukan mau diapakan teks ini.
        // Opsi A: Langsung masukkan teks nomor rekening ke dalam input teks "Nomor Rekening" di form setoran/penarikan.
        // document.getElementById('input_account_number').value = decodedText;

        // Opsi B: Lakukan submit form otomatis ke server
        // document.getElementById('scanned-account').value = decodedText;
        // document.getElementById('auto-search-form').submit();

        // Opsi C: Panggil AJAX API searchCustomer dan render hasilnya di modal.
        
        // Hentikan scan agar tidak berulang kali trigger
        html5QrcodeScanner.clear();
    }

    function onScanFailure(error) {
        // Abaikan peringatan gagal secara diam-diam (kamera terus berupaya membaca)
        // console.warn(`Code scan error = ${error}`);
    }

    // Menginisialisasi Kamera Scanner
    let html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader", 
        { fps: 10, qrbox: {width: 250, height: 250} }, 
        /* verbose= */ false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>
```

Saat halaman dibuka, browser akan meminta _permission_ kamera. Jika diizinkan, video akan muncul di layar dan Teller cukup menghadapkan QR code (lewat HP atau kertas) ke layar komputer.

---

## Langkah 3: Pembuatan Generator QR Code Nasabah (Dari Sisi Admin)
QR Code yang di-_scan_ harusnya berisi **Nomor Rekening (Account Number)** milik nasabah. Agar Nasabah punya QR Code, Admin harus bisa mencetak/menyediakan QR Code tersebut.

1. Buka terminal proyek dan install library QR Code generator:
   ```bash
   composer require simplesoftwareio/simple-qrcode
   ```
2. Di halaman daftar Nasabah Admin (`admin/customers/index.blade.php`), tambahkan sebuah tombol di kolom aksi yang berfungsi untuk memunculkan modal/halaman cetak kartu.
3. Untuk me-_render_ QR code di blade, kamu cukup menulis (contoh):
   ```blade
   <div class="text-center">
       {!! QrCode::size(200)->generate($customer->account_number) !!}
       <p>Nomor Rekening: {{ $customer->account_number }}</p>
   </div>
   ```

Selesai! Sekarang nasabah yang terdaftar bisa memiliki kartu identitas berisi QR Code dari nomor rekening mereka, dan Teller memiliki alat canggih (kamera) di antarmuka aplikasinya untuk memindai kartu tersebut dan segera melayani setoran maupun penarikan.
