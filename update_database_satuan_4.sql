-- ============================================================
-- SCRIPT UPDATE DATABASE UNTUK MENAMBAHKAN SATUAN 4
-- ============================================================
-- Jalankan script ini di database MySQL/MariaDB Anda
-- Pastikan untuk backup database terlebih dahulu!
-- ============================================================

-- 1. Menambahkan kolom harga satuan 4 (setelah barang_harga_grosir_2_s3)
ALTER TABLE `barang` 
ADD COLUMN `barang_harga_s4` VARCHAR(255) DEFAULT '0' AFTER `barang_harga_grosir_2_s3`,
ADD COLUMN `barang_harga_grosir_1_s4` VARCHAR(255) DEFAULT '0' AFTER `barang_harga_s4`,
ADD COLUMN `barang_harga_grosir_2_s4` VARCHAR(255) DEFAULT '0' AFTER `barang_harga_grosir_1_s4`;

-- 2. Menambahkan kolom satuan_id_4 (setelah satuan_id_3)
ALTER TABLE `barang` 
ADD COLUMN `satuan_id_4` INT(11) DEFAULT NULL AFTER `satuan_id_3`;

-- 3. Menambahkan kolom satuan_isi_4 (setelah satuan_isi_3)
ALTER TABLE `barang` 
ADD COLUMN `satuan_isi_4` INT(11) DEFAULT NULL AFTER `satuan_isi_3`;

-- ============================================================
-- VERIFIKASI: Cek apakah kolom sudah berhasil ditambahkan
-- ============================================================
-- Jalankan query berikut untuk memastikan kolom sudah ada:
-- DESCRIBE barang;
-- atau
-- SHOW COLUMNS FROM barang LIKE '%s4%';
-- SHOW COLUMNS FROM barang LIKE '%satuan_id_4%';
-- SHOW COLUMNS FROM barang LIKE '%satuan_isi_4%';

-- ============================================================
-- CATATAN PENTING:
-- ============================================================
-- 1. Pastikan untuk backup database sebelum menjalankan script ini
-- 2. Script ini akan menambahkan 5 kolom baru ke tabel barang:
--    - barang_harga_s4 (harga umum satuan 4)
--    - barang_harga_grosir_1_s4 (harga retail satuan 4)
--    - barang_harga_grosir_2_s4 (harga grosir satuan 4)
--    - satuan_id_4 (ID satuan keempat)
--    - satuan_isi_4 (konversi isi satuan keempat)
-- 3. Semua kolom baru memiliki nilai default yang aman
-- 4. Setelah menjalankan script ini, restart aplikasi jika diperlukan

