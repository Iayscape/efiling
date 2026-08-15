-- =========================================================================
-- Migrasi DB Production yang SUDAH ADA ISINYA (aman, tidak menghapus data)
-- Jalankan file ini via phpMyAdmin (tab SQL) pada database hosting Anda.
-- Pastikan Anda sudah upload file terbaru (assets/img/logos/*) ke hosting
-- SEBELUM menjalankan migrasi ini.
-- =========================================================================

-- 1) Hapus constraint UNIQUE pada kolom kode (agar KB & SB bisa punya
--    baris cetak + online dengan kode sama)
-- Jika baris ini error "key does not exist", cek nama index asli di
-- phpMyAdmin > tabel media > Struktur > Indeks, lalu ganti "kode" di
-- bawah ini dengan nama index yang benar.
ALTER TABLE media DROP INDEX kode;

-- 2) Ubah baris KB & SB yang lama (gabungan cetak_online) menjadi baris
--    KHUSUS CETAK saja (id & data lama tetap, surat/kwitansi lama tetap valid)
UPDATE media SET nama = 'Koran Barito', tipe = 'cetak',
  logo_path = 'assets/img/logos/koran-barito-cetak.png'
  WHERE kode = 'KB' AND tipe = 'cetak_online';

UPDATE media SET nama = 'Sinar Barito', tipe = 'cetak',
  logo_path = 'assets/img/logos/sinar-barito-cetak.png'
  WHERE kode = 'SB' AND tipe = 'cetak_online';

-- 3) Tambah baris BARU untuk versi online KB & SB (id baru, tidak
--    mengganggu data surat/kwitansi lama)
INSERT INTO media (kode, nama, tipe, perusahaan, alamat_redaksi, telp, email, facebook, twitter, akta_notaris, ahu_number, npwp, signatory_name, signatory_title, accent_color, logo_path)
SELECT 'KB', 'onlinekoranbarito.com', 'online', perusahaan, alamat_redaksi, telp, email, facebook, twitter, akta_notaris, ahu_number, npwp, signatory_name, signatory_title, accent_color, 'assets/img/logos/koran-barito-online.jpg'
FROM media WHERE kode = 'KB' AND tipe = 'cetak' LIMIT 1;

INSERT INTO media (kode, nama, tipe, perusahaan, alamat_redaksi, telp, email, facebook, twitter, akta_notaris, ahu_number, npwp, signatory_name, signatory_title, accent_color, logo_path)
SELECT 'SB', 'onlinesinarbarito.com', 'online', perusahaan, alamat_redaksi, telp, email, facebook, twitter, akta_notaris, ahu_number, npwp, signatory_name, signatory_title, accent_color, 'assets/img/logos/sinar-barito-online.jpg'
FROM media WHERE kode = 'SB' AND tipe = 'cetak' LIMIT 1;

-- 4) Pasang logo resmi untuk 5 media yang lain (data & id tidak berubah)
UPDATE media SET logo_path = 'assets/img/logos/suluh-banua-cetak.png' WHERE kode = 'MSB';
UPDATE media SET logo_path = 'assets/img/logos/suluh-banua-online.png' WHERE kode = 'SBN';
UPDATE media SET logo_path = 'assets/img/logos/banta-news.jpg' WHERE kode = 'BN';
UPDATE media SET logo_path = 'assets/img/logos/barito-bersinar.jpg' WHERE kode = 'BBN';
UPDATE media SET logo_path = 'assets/img/logos/selidah-nusantara.jpg' WHERE kode = 'SN';

-- 5) Pasang logo resmi + tagline outlet di homepage (ganti dari foto stok /
--    tagline kosong). Menggunakan LIKE agar cocok walau nama sedikit beda.
UPDATE outlets SET tagline = 'Jembatan Aspirasi', logo_path = 'assets/img/logos/koran-barito-online.jpg' WHERE nama LIKE '%Koran Barito%';
UPDATE outlets SET tagline = 'Pemersatu Banua', logo_path = 'assets/img/logos/sinar-barito-online.jpg' WHERE nama LIKE '%Sinar Barito%';
UPDATE outlets SET tagline = 'Sarabakawa', logo_path = 'assets/img/logos/suluh-banua-online.png' WHERE nama LIKE '%Suluh Banua%';
UPDATE outlets SET tagline = 'Tergores', logo_path = 'assets/img/logos/banta-news.jpg' WHERE nama LIKE '%Banta News%';
UPDATE outlets SET tagline = 'Dunia Dalam Genggaman', logo_path = 'assets/img/logos/barito-bersinar.jpg' WHERE nama LIKE '%Barito Bersinar%';
UPDATE outlets SET tagline = 'Bahalap', logo_path = 'assets/img/logos/selidah-nusantara.jpg' WHERE nama LIKE '%Selidah Nusantara%';

-- 6) Cek hasil
SELECT id, kode, nama, tipe, logo_path FROM media ORDER BY id;
SELECT id, nama, logo_path FROM outlets ORDER BY urutan;
