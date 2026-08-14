# PRD - E-Filing Barito Media Group

## Problem Statement (asli)
Membuat website e-filing/pengarsipan digital untuk grup media (Koran Barito,
Sinar Barito, Suluh Banua, Banta News, Barito Bersinar, Selidah Nusantara)
yang mengelola Surat Penawaran & Kwitansi ke berbagai instansi (DPRD,
Kominfo, dsb). Stack wajib: PHP native + HTML/CSS/JS + MySQL, siap upload ke
shared hosting cPanel tanpa instalasi/build tambahan (kecuali konfigurasi DB).
Testing oleh agent dilewati atas permintaan user (user menguji sendiri).

## Arsitektur
- Pure PHP 8 (procedural, tanpa framework), MySQL/MariaDB via PDO.
- Library pihak ketiga: Dompdf (PDF), PhpOffice/PHPWord (Word) — sudah
  di-vendor secara statis (tidak perlu composer install di server).
- Tidak ada Node/build step. CSS/JS vanilla, di-link langsung.
- Auth: session PHP native + bcrypt + CSRF token + brute-force lock 15 menit.
- Struktur folder: lihat README.md di /app/efiling-app/README.md.

## User Persona
- **Admin** (pemilik/owner, aqmarsharaya@gmail.com): kelola media, instansi,
  jenis penawaran, pengguna, seluruh dokumen.
- **Staff**: input & kelola surat/kwitansi, unduh arsip, tanpa akses data
  master media/pengguna.

## Core Requirements (statis)
1. Arsip terorganisir per Instansi > Media > Tahun > Bulan > Jenis Dokumen.
2. CRUD Instansi tujuan (tambah/kurangi bebas).
3. Mendukung 7 kode media: KB, BBN (grup PT. Barito Media Jaya), SB, SN
   (grup PT. Media Jaya Bersinar), MSB, SBN, BN (grup PT. Suluh Nusantara
   Jaya) — data legal (alamat, akta, AHU, NPWP, penandatangan) per media.
4. Tanggal surat/kwitansi default hari ini, bisa diubah via date picker.
5. CRUD rubrik/item penawaran + harga per surat (dinamis, tambah/kurang baris).
6. Surat Penawaran otomatis membuat Kwitansi terhubung (nomor, jumlah,
   terbilang, keterangan pembayaran otomatis mengikuti template jenis
   penawaran) — sinkron ulang otomatis setiap surat disimpan.
7. Penomoran otomatis: Surat = `NNN/KodeJenis/Red/KodeMedia/BulanRomawi/Tahun`,
   Kwitansi = `NNN/KW/KodeMedia/Red/BulanRomawi/Tahun`. Berjalan per media,
   mulai 001, tidak reset tahun.
8. Jenis penawaran & kode media bisa ditambah/diubah dari UI (menu Jenis
   Penawaran & Media).
9. Homepage company profile mengarahkan ke 6 website outlet + login e-filing.
10. Export/download PDF dan/atau Word, single atau multi-select (ZIP dengan
    struktur folder Instansi/Media/Tahun/Bulan/JenisDokumen).
11. Personalisasi tampilan: 100+ palet warna (Hitam/Putih/Biru/Kuning/Merah),
    tersimpan per akun.
12. Keamanan: bcrypt, CSRF, brute-force lock, prepared statements, security
    headers, folder sensitif diblokir `.htaccess`.

## Implemented (2026-08-14)
- Struktur project lengkap di `/app/efiling-app/` (siap diunduh & di-upload).
- `install.php` — wizard instalasi 1x klik (buat tabel + akun admin).
- `sql/schema.sql` — 13 tabel + seed 7 media, 4 jenis penawaran, 6 outlet.
- Auth lengkap (login/logout, roles admin/staff, brute-force lock, CSRF).
- Homepage high-end (dark editorial theme, Playfair Display + IBM Plex Sans)
  dengan 6 outlet card + tombol login.
- Dashboard admin: sidebar, stat cards, dokumen terbaru.
- Master data CRUD: Instansi, Media (+upload logo/ttd/stempel), Jenis
  Penawaran (dengan template token {instansi}/{media}/{bulan}/{tahun}),
  Pengguna (admin only).
- Surat Penawaran: form dinamis (tambah/hapus item via JS), auto-nomor,
  auto-generate & auto-sync Kwitansi (jumlah & terbilang selalu matching).
- Kwitansi manual/standalone (tanpa surat) juga didukung.
- Generator PDF (Dompdf) & Word (PHPWord) dengan kop surat & data legal per
  media, opsi tanda tangan+stempel otomatis (checkbox, default nonaktif).
- Arsip Digital: filter (instansi/media/tahun/jenis/kata kunci), multi-select,
  unduh ZIP terorganisir (PDF/Word/keduanya).
- Personalisasi tampilan: 120 palet warna generatif (24 hitam, 24 putih, 26
  biru, 24 kuning, 24 merah), tersimpan ke DB per user + cookie anti-FOUC.
- Sudah dites manual end-to-end via curl (login, CRUD, auto kwitansi+harga
  matching, PDF/Word valid, bulk ZIP, role restriction 403 untuk staff) —
  testing_agent SENGAJA DILEWATI atas permintaan eksplisit user.
- `config.php` sengaja TIDAK disertakan agar `install.php` berjalan otomatis
  saat pertama upload.

## Backlog / P1-P2 (belum dikerjakan, menunggu feedback user)
- P1: Halaman edit outlet homepage dari UI (saat ini via tabel `outlets`,
  butuh phpMyAdmin manual untuk ubah selain lewat DB).
- P1: Export rekap/laporan (misal total pendapatan per bulan/instansi).
- P2: Import data lama (dari dokumen existing) secara massal.
- P2: Notifikasi email saat surat/kwitansi baru dibuat.
- P2: Versi cetak thermal/dot-matrix untuk kwitansi.

## Next Steps untuk User
1. Download source code project ini (folder `efiling-app/`).
2. Ikuti langkah di `README.md` untuk upload & instalasi ke hosting.
3. Uji sendiri sesuai permintaan Anda (testing agent dilewati).
