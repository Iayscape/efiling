-- =========================================================================
-- Migrasi: Master Data Rubrik/Item (2026-08-16)
-- Jalankan via phpMyAdmin (tab SQL) pada database hosting yang sudah ada
-- isinya. Aman — tidak mengubah data surat/kwitansi yang sudah ada.
-- =========================================================================

CREATE TABLE IF NOT EXISTS rubrik (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_rubrik VARCHAR(150) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Otomatis isi master Rubrik dari nama rubrik/item yang sudah pernah dipakai
-- di surat & kwitansi Anda sebelumnya, supaya tidak perlu ketik ulang manual.
INSERT INTO rubrik (nama_rubrik)
SELECT DISTINCT nama FROM (
  SELECT TRIM(nama_rubrik) AS nama FROM surat_penawaran_items
  UNION
  SELECT TRIM(nama_item) AS nama FROM kwitansi_items
) AS gabungan
WHERE nama <> '' AND nama NOT IN (SELECT nama_rubrik FROM rubrik);

-- Cek hasil
SELECT * FROM rubrik ORDER BY nama_rubrik;
