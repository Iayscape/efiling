-- =========================================================================
-- Skema Database E-Filing Barito Media Group
-- Import file ini melalui phpMyAdmin (tab Import) pada database yang sudah
-- Anda buat, ATAU biarkan install.php melakukannya otomatis untuk Anda.
-- =========================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  theme_id VARCHAR(50) DEFAULT 'default',
  theme_vars TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  identifier VARCHAR(255) NOT NULL UNIQUE,
  attempts INT NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS instansi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  jabatan_penerima VARCHAR(255) NULL,
  cq VARCHAR(255) NULL,
  lokasi VARCHAR(100) DEFAULT 'Tempat',
  keterangan TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(10) NOT NULL UNIQUE,
  nama VARCHAR(150) NOT NULL,
  tipe ENUM('cetak','online','cetak_online') NOT NULL DEFAULT 'online',
  perusahaan VARCHAR(200) NOT NULL,
  alamat_redaksi TEXT NULL,
  telp VARCHAR(50) NULL,
  email VARCHAR(150) NULL,
  facebook VARCHAR(100) NULL,
  twitter VARCHAR(100) NULL,
  akta_notaris VARCHAR(255) NULL,
  ahu_number VARCHAR(150) NULL,
  npwp VARCHAR(100) NULL,
  signatory_name VARCHAR(150) NOT NULL,
  signatory_title VARCHAR(150) NOT NULL DEFAULT 'Pemimpin Perusahaan',
  logo_path VARCHAR(255) NULL,
  signature_path VARCHAR(255) NULL,
  stempel_path VARCHAR(255) NULL,
  accent_color VARCHAR(10) NOT NULL DEFAULT '#1d4ed8',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jenis_penawaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(150) NOT NULL,
  template_hal TEXT NULL,
  template_body TEXT NULL,
  template_pembayaran TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS doc_counters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  media_id INT NOT NULL,
  doc_type ENUM('penawaran','kwitansi') NOT NULL,
  last_number INT NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_media_doctype (media_id, doc_type),
  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS surat_penawaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_surat VARCHAR(80) NOT NULL UNIQUE,
  media_id INT NOT NULL,
  jenis_id INT NOT NULL,
  instansi_id INT NOT NULL,
  tanggal DATE NOT NULL,
  bulan TINYINT NOT NULL,
  bulan_label VARCHAR(50) NOT NULL,
  tahun SMALLINT NOT NULL,
  hal TEXT NULL,
  body TEXT NULL,
  catatan TEXT NULL,
  status ENUM('draft','terkirim') NOT NULL DEFAULT 'draft',
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (media_id) REFERENCES media(id),
  FOREIGN KEY (jenis_id) REFERENCES jenis_penawaran(id),
  FOREIGN KEY (instansi_id) REFERENCES instansi(id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS surat_penawaran_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  surat_id INT NOT NULL,
  nama_rubrik VARCHAR(255) NOT NULL,
  keterangan VARCHAR(255) NULL,
  harga_bulan DECIMAL(15,2) NOT NULL DEFAULT 0,
  harga_tahun DECIMAL(15,2) NULL,
  urutan INT NOT NULL DEFAULT 0,
  FOREIGN KEY (surat_id) REFERENCES surat_penawaran(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kwitansi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomor_kwitansi VARCHAR(80) NOT NULL UNIQUE,
  surat_id INT NULL,
  media_id INT NOT NULL,
  jenis_id INT NULL,
  instansi_id INT NOT NULL,
  tanggal DATE NOT NULL,
  bulan TINYINT NOT NULL,
  bulan_label VARCHAR(50) NOT NULL,
  tahun SMALLINT NOT NULL,
  diterima_dari VARCHAR(255) NULL,
  untuk_pembayaran TEXT NULL,
  jumlah DECIMAL(15,2) NOT NULL DEFAULT 0,
  terbilang VARCHAR(255) NULL,
  gunakan_ttd_stempel TINYINT(1) NOT NULL DEFAULT 0,
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (surat_id) REFERENCES surat_penawaran(id) ON DELETE SET NULL,
  FOREIGN KEY (media_id) REFERENCES media(id),
  FOREIGN KEY (jenis_id) REFERENCES jenis_penawaran(id) ON DELETE SET NULL,
  FOREIGN KEY (instansi_id) REFERENCES instansi(id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kwitansi_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kwitansi_id INT NOT NULL,
  nama_item VARCHAR(255) NOT NULL,
  harga DECIMAL(15,2) NOT NULL DEFAULT 0,
  urutan INT NOT NULL DEFAULT 0,
  FOREIGN KEY (kwitansi_id) REFERENCES kwitansi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  description TEXT NULL,
  ip_address VARCHAR(64) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS outlets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  url VARCHAR(255) NOT NULL,
  deskripsi VARCHAR(255) NULL,
  tagline VARCHAR(150) NULL,
  accent_color VARCHAR(10) NOT NULL DEFAULT '#1d4ed8',
  logo_path VARCHAR(255) NULL,
  urutan INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
-- SEED DATA
-- =========================================================================

INSERT INTO media (kode, nama, tipe, perusahaan, alamat_redaksi, telp, email, facebook, twitter, akta_notaris, ahu_number, npwp, signatory_name, signatory_title, accent_color) VALUES
('KB','Koran Barito & onlinekoranbarito.com','cetak_online','PT. Barito Media Jaya','Jl. A. Yani RT. 01 No. 03 Tamiang Layang, Kabupaten Barito Timur, Provinsi Kalimantan Tengah','0853 4871 2495','koranbarito@gmail.com','Koran Barito','@koranbarito','Linda Kenari, SH., MH. No. 95 Tahun 2023','AHU-0034075.AH.01.02. Tahun 2023','03.142.889.9-714.000','M. JAYA A.S.','Pemimpin Perusahaan','#b91c1c'),
('BBN','Barito Bersinar (baritobersinar.news)','online','PT. Barito Media Jaya','Jl. A. Yani RT. 01 No. 03 Tamiang Layang, Kabupaten Barito Timur, Provinsi Kalimantan Tengah','0853 4871 2495','koranbarito@gmail.com','Koran Barito','@koranbarito','Linda Kenari, SH., MH. No. 95 Tahun 2023','AHU-0034075.AH.01.02. Tahun 2023','03.142.889.9-714.000','M. JAYA A.S.','Pemimpin Perusahaan','#b45309'),
('SB','Sinar Barito & onlinesinarbarito.com','cetak_online','PT. Media Jaya Bersinar','Jl. Trans Kalimantan Komp Batola Residence Blok H Site III No. 17 RT. 12 Desa Sungai Lumbah, Kecamatan Alalak, Kabupaten Barito Kuala, Provinsi Kalimantan Selatan','0853 4871 2495','sinarbarito@gmail.com','sinarbarito','@sinarbarito','Muhammad Ikhwan, SH., M.Kn. No. 09 Tanggal 07 Februari 2025','AHU-0010210.AH.01.01. Tahun 2025','1091031211539745','EKA SRI TANJUNG JAYA','Pemimpin Perusahaan','#1d4ed8'),
('SN','Selidah Nusantara (selidahnusantara.id)','online','PT. Media Jaya Bersinar','Jl. Trans Kalimantan Komp Batola Residence Blok H Site III No. 17 RT. 12 Desa Sungai Lumbah, Kecamatan Alalak, Kabupaten Barito Kuala, Provinsi Kalimantan Selatan','0853 4871 2495','sinarbarito@gmail.com','sinarbarito','@sinarbarito','Muhammad Ikhwan, SH., M.Kn. No. 09 Tanggal 07 Februari 2025','AHU-0010210.AH.01.01. Tahun 2025','1091031211539745','EKA SRI TANJUNG JAYA','Pemimpin Perusahaan','#0e7490'),
('MSB','Suluh Banua (Majalah)','cetak','PT. Suluh Nusantara Jaya','Jl. Trans Kalimantan Desa Sungai Lumbah RT. 011, Kecamatan Alalak, Kabupaten Barito Kuala, Provinsi Kalimantan Selatan','0831 5394 2438','suluhbanua@gmail.com',NULL,NULL,'Muhammad Ikhwan, SH., M.Kn. No. 08 Tanggal 07 Februari 2025','AHU-0010122.AH.01.01. Tahun 2025','1091 0312 1154 0546','M. AQMAR SHARAYA','Pemimpin Perusahaan','#15803d'),
('SBN','suluhbanua.news','online','PT. Suluh Nusantara Jaya','Jl. Trans Kalimantan Desa Sungai Lumbah RT. 011, Kecamatan Alalak, Kabupaten Barito Kuala, Provinsi Kalimantan Selatan','0831 5394 2438','suluhbanua@gmail.com',NULL,NULL,'Muhammad Ikhwan, SH., M.Kn. No. 08 Tanggal 07 Februari 2025','AHU-0010122.AH.01.01. Tahun 2025','1091 0312 1154 0546','M. AQMAR SHARAYA','Pemimpin Perusahaan','#0f766e'),
('BN','Banta News (bantanews.com)','online','PT. Suluh Nusantara Jaya','Jl. Trans Kalimantan Desa Sungai Lumbah RT. 011, Kecamatan Alalak, Kabupaten Barito Kuala, Provinsi Kalimantan Selatan','0831 5394 2438','suluhbanua@gmail.com',NULL,NULL,'Muhammad Ikhwan, SH., M.Kn. No. 08 Tanggal 07 Februari 2025','AHU-0010122.AH.01.01. Tahun 2025','1091 0312 1154 0546','M. AQMAR SHARAYA','Pemimpin Perusahaan','#7c3aed');

INSERT INTO jenis_penawaran (kode, nama, template_hal, template_body, template_pembayaran) VALUES
('Adv','Advertorial','Penawaran Publikasi Advertorial Kegiatan - {instansi} Bulan {bulan} {tahun}','Roda pemerintahan dan pembangunan di {instansi} terus berjalan. Banyak perubahan yang terjadi sehingga taraf kehidupan masyarakat semakin meningkat. Pembangunan terus dipacu oleh pihak pemerintah setempat dengan tujuan akhir untuk mensejahterakan masyarakat.\n\nSebagai media penyampai informasi, pendidikan, sosial, budaya, dan hiburan, kami menyampaikan penawaran kerja sama publikasi advertorial kegiatan {instansi} yang akan dimuat pada {media} Bulan {bulan} {tahun}.\n\nDemikian penawaran ini kami sampaikan dengan harapan dapat terjalin kerja sama yang baik. Kami berharap penawaran ini dapat dikabulkan.','BIAYA PUBLIKASI ADVERTORIAL KEGIATAN {INSTANSI} U.B {BULAN} {TAHUN}'),
('Ikn','Iklan','Penawaran Publikasi Iklan Bulan {bulan} {tahun}','Sebagai media penyampai informasi, pendidikan, sosial, budaya, dan hiburan, kami menyampaikan penawaran pemuatan iklan pada {media} Bulan {bulan} {tahun}.\n\nDemikian penawaran ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.','BIAYA PUBLIKASI IKLAN KEGIATAN {INSTANSI} U.B {BULAN} {TAHUN}'),
('BM','Berlangganan Majalah','Penawaran Kerja Sama Berlangganan Majalah Bulan {bulan} {tahun}','Kami sangat bangga dan menyampaikan penghargaan setinggi-tingginya atas kepercayaan yang besar terhadap {media} sebagai bacaan setia.\n\nSebagai media modern penyampai informasi, pendidikan, sosial, budaya, dan hiburan kami menyampaikan penawaran kerja sama berlangganan {media} Bulan {bulan} {tahun}.','BIAYA BERLANGGANAN MAJALAH {MEDIA} U.B {BULAN} {TAHUN}'),
('LF','Lensa Foto','Penawaran Publikasi Lensa Foto Kegiatan - {instansi} Bulan {bulan} {tahun}','Sebagai media penyampai informasi, pendidikan, sosial, budaya, dan hiburan, kami menyampaikan penawaran publikasi lensa foto kegiatan {instansi} yang akan dimuat pada {media} Bulan {bulan} {tahun}.\n\nDemikian penawaran ini kami sampaikan dengan harapan dapat terjalin kerja sama yang baik.','BIAYA PUBLIKASI LENSA FOTO KEGIATAN {INSTANSI} U.B {BULAN} {TAHUN}');

INSERT INTO outlets (nama, url, deskripsi, tagline, accent_color, logo_path, urutan) VALUES
('Koran Barito','https://onlinekoranbarito.com','Tabloid & portal berita Kalimantan Tengah','Jembatan Aspirasi','#b91c1c','assets/img/outlets/koran-barito.jpg',1),
('Sinar Barito','https://onlinesinarbarito.com','Tabloid & portal berita Kalimantan Selatan','Pemersatu Banua','#1d4ed8','assets/img/outlets/sinar-barito.jpg',2),
('Suluh Banua','https://suluhbanua.news','Majalah & portal berita','Sarabakawa','#15803d','assets/img/outlets/suluh-banua.png',3),
('Banta News','https://bantanews.com','Portal berita terkini','Tergores','#7c3aed','assets/img/outlets/banta-news.jpg',4),
('Barito Bersinar','https://baritobersinar.news','Portal berita regional','Dunia Dalam Genggaman','#b45309','assets/img/outlets/barito-bersinar.jpg',5),
('Selidah Nusantara','https://selidahnusantara.id','Portal berita nasional','Bahalap','#0e7490','assets/img/outlets/selidah-nusantara.jpg',6);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_tagline','Grup Media Terpercaya di Kalimantan'),
('site_group_name','Barito Media Group');
