<?php
// =========================================================================
// SALIN file ini menjadi "config.php" (di folder yang sama), lalu isi
// dengan data database yang Anda buat di phpMyAdmin / cPanel hosting Anda.
// Setelah config.php dibuat, buka website Anda -> otomatis ke halaman
// install.php untuk membuat tabel & akun admin pertama.
// =========================================================================

// Data koneksi database (dari cPanel > MySQL Databases / phpMyAdmin)
define('DB_HOST', 'localhost');
define('DB_NAME', 'namadatabase_anda');
define('DB_USER', 'userdatabase_anda');
define('DB_PASS', 'passworddatabase_anda');

// Nama aplikasi yang tampil di judul halaman & kop dokumen
define('APP_NAME', 'Barito Media Group - E-Filing');

// Ubah menjadi 'production' setelah website live (mematikan pesan error detail)
define('APP_ENV', 'production');
