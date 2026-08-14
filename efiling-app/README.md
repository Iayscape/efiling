# E-Filing Barito Media Group

Aplikasi arsip digital (e-filing) untuk mengelola Surat Penawaran &amp; Kwitansi
seluruh media di bawah Barito Media Group. Dibangun murni dengan **PHP native +
HTML + CSS + JavaScript + MySQL** — tanpa Node.js, tanpa build step, tanpa
Composer install di server. Tinggal upload ke hosting.

> **Penting:** folder ini TIDAK menyertakan `config.php` (hanya
> `config.sample.php` sebagai contoh). Ini disengaja agar begitu Anda upload
> & buka websitenya, sistem otomatis membawa Anda ke `install.php` untuk
> membuat `config.php` sesuai data database Anda sendiri. Jangan pernah
> membuat file `config.php` secara manual sebelum upload.

## 1. Cara Deploy ke Hosting (cPanel/shared hosting)

1. Login ke **cPanel** Anda, buka menu **MySQL Databases**, buat:
   - 1 database (misal `namauser_efiling`)
   - 1 user database + password
   - Hubungkan (assign) user tersebut ke database dengan privilege **ALL**
2. Buka **File Manager**, arahkan ke folder domain/subdomain yang ingin
   dipakai untuk e-filing (disarankan pakai subdomain, misal
   `arsip.domainanda.com`, agar path menjadi root `/`).
3. Upload **seluruh isi folder `efiling-app/`** (bukan foldernya, isinya) ke
   root domain/subdomain tersebut. Bisa lewat upload ZIP lalu extract di File
   Manager.
4. Buka `https://arsip.domainanda.com/` di browser. Karena `config.php` belum
   ada, otomatis diarahkan ke halaman **install.php**.
5. Isi form instalasi: host database (biasanya `localhost`), nama database,
   user, password (yang dibuat di langkah 1), lalu isi nama, email &amp;
   password akun admin pertama Anda. Klik **Pasang Aplikasi**.
6. Selesai! Anda otomatis bisa login di halaman `/login.php`.

Tidak perlu menjalankan `composer install` — folder `vendor/` (Dompdf &amp;
PhpWord untuk membuat PDF/Word) sudah disertakan lengkap.

## 2. Struktur Data yang Sudah Disiapkan

Saat instalasi, sistem otomatis mengisi:
- 7 kode media: **KB** (Koran Barito & onlinekoranbarito.com), **BBN**
  (Barito Bersinar), **SB** (Sinar Barito & onlinesinarbarito.com), **SN**
  (Selidah Nusantara), **MSB** (Majalah Suluh Banua), **SBN**
  (suluhbanua.news), **BN** (Banta News) — lengkap dengan data legalitas
  (alamat redaksi, akta notaris, AHU, NPWP, penandatangan) sesuai berkas yang
  Anda lampirkan sebelumnya.
- 4 jenis penawaran: Advertorial (Adv), Iklan (Ikn), Berlangganan Majalah
  (BM), Lensa Foto (LF) — semua bisa diubah/ditambah di menu **Jenis
  Penawaran**.
- 6 outlet untuk homepage.

Semua data ini bisa diubah kapan saja lewat menu admin (Instansi, Media,
Jenis Penawaran).

## 3. Fitur Utama

- **Homepage company profile** — mengarahkan pengunjung ke 6 website media &
  tombol Login e-filing.
- **Surat Penawaran otomatis membuat Kwitansi** — saat menyimpan surat
  penawaran, kwitansi dengan nominal & keterangan yang sama otomatis
  dibuat/diperbarui.
- **Penomoran otomatis**: `NNN/KodeJenis/Red/KodeMedia/BulanRomawi/Tahun`
  untuk surat, dan `NNN/KW/KodeMedia/Red/BulanRomawi/Tahun` untuk kwitansi.
  Nomor berjalan per media, tidak reset tahun.
- **Arsip Digital** — filter berdasarkan instansi, media, tahun, jenis
  dokumen, kata kunci; unduh satu atau banyak dokumen sekaligus dalam ZIP
  terorganisir per Instansi/Media/Tahun/Bulan/Jenis.
- **Export PDF & Word** (pilih salah satu atau keduanya).
- **Personalisasi tampilan** — 100+ pilihan palet warna (Hitam, Putih, Biru,
  Kuning, Merah) di menu Personalisasi Tampilan, tersimpan per akun.
- **Multi-user & role** — Admin (akses penuh) dan Staff (input/edit/unduh
  dokumen, tanpa akses kelola pengguna & media).
- **Keamanan**: password di-hash (bcrypt), proteksi CSRF di semua form,
  proteksi brute-force login (kunci 15 menit setelah 5x salah), prepared
  statement di semua query, header keamanan (CSP, X-Frame-Options, dst),
  folder sensitif (`includes/`, `storage/`, `vendor/`, `sql/`) diblokir dari
  akses langsung browser via `.htaccess`.

## 4. Setelah Live

- Tambahkan tanda tangan & stempel digital (opsional) di menu **Media &
  Legalitas** per media — nanti tersedia sebagai centang "Gunakan tanda
  tangan & stempel otomatis" saat membuat/mengubah kwitansi.
- Tambah pengguna staff di menu **Pengguna** (khusus admin).
- Backup rutin database lewat phpMyAdmin (Export).

## 5. Struktur Folder

```
index.php, login.php, logout.php, install.php   -> halaman publik
admin/                                           -> semua halaman internal (wajib login)
includes/                                        -> koneksi DB, helper, keamanan (tidak bisa diakses browser)
assets/                                          -> css & js
vendor/                                          -> library Dompdf & PhpWord (siap pakai)
storage/uploads/                                 -> logo/ttd/stempel (tidak bisa diakses browser)
sql/schema.sql                                   -> struktur database
```
