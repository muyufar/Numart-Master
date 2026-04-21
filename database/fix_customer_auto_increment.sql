-- =====================================================
-- SQL Script untuk Mengubah customer_id menjadi AUTO_INCREMENT
-- Problem: Error #1062 - Duplicate entry untuk PRIMARY KEY
-- Solution: Perbaiki duplikat dulu, baru ubah jadi AUTO_INCREMENT
-- =====================================================

-- LANGKAH 1: CEK APAKAH ADA NILAI DUPLIKAT
-- Jalankan query ini terlebih dahulu untuk mengecek
SELECT customer_id, COUNT(*) as jumlah 
FROM customer 
GROUP BY customer_id 
HAVING COUNT(*) > 1;

-- Jika ada hasil, berarti ada customer_id yang duplikat
-- Catat ID yang duplikat untuk diperbaiki


-- LANGKAH 2: PERBAIKI NILAI DUPLIKAT (WAJIB DILAKUKAN!)
-- Metode Otomatis: Script untuk memperbaiki semua duplikat

-- A. Lihat detail data duplikat
SELECT customer_id, customer_nama, customer_tlpn, COUNT(*) as jumlah 
FROM customer 
GROUP BY customer_id 
HAVING COUNT(*) > 1
ORDER BY customer_id;

-- B. Cara mencari nilai maksimum yang ada:
SELECT MAX(customer_id) as max_id FROM customer;

-- C. PERBAIKI DUPLIKAT SECARA OTOMATIS
-- Script ini akan memberi ID baru untuk semua record duplikat kecuali yang pertama

-- Buat tabel temporary untuk menyimpan ID yang akan diupdate
CREATE TEMPORARY TABLE IF NOT EXISTS temp_duplicate_fix (
    old_id INT,
    new_id INT,
    row_num INT,
    PRIMARY KEY (old_id, row_num)
);

-- Isi tabel temporary dengan mapping ID lama ke ID baru
SET @new_id = (SELECT COALESCE(MAX(customer_id), 0) + 1 FROM customer);
SET @row_num = 0;

-- Insert semua record duplikat yang perlu diupdate (kecuali yang pertama)
INSERT INTO temp_duplicate_fix (old_id, new_id, row_num)
SELECT 
    t1.customer_id as old_id,
    (@new_id := @new_id + (@row_num := @row_num + 1) - 1) as new_id,
    @row_num as row_num
FROM (
    SELECT customer_id, 
           ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY customer_create, customer_nama) as rn
    FROM customer
    HAVING rn > 1
) t1;

-- ATAU gunakan cara manual untuk MySQL versi lama yang tidak support ROW_NUMBER():
-- Hapus tabel temporary jika sudah dibuat
DROP TEMPORARY TABLE IF EXISTS temp_duplicate_fix;

-- Cara Manual: Update satu per satu
-- Contoh: Jika ada duplikat di customer_id = 12340
-- Cari tau dulu ada berapa duplikat:
SELECT 
    customer_id, 
    customer_nama, 
    customer_tlpn, 
    customer_create,
    COUNT(*) OVER (PARTITION BY customer_id) as total_duplikat
FROM customer 
WHERE customer_id = 12340;

-- Kemudian update manual (ganti 12340 dengan ID yang duplikat):
-- UPDATE customer SET customer_id = (SELECT MAX(customer_id) + 1 FROM (SELECT * FROM customer) as temp) 
-- WHERE customer_id = 12340 
-- AND customer_nama = 'nama_yang_duplikat'  -- sesuaikan dengan kondisi unik
-- LIMIT 1;


-- LANGKAH 3: PASTIKAN TIDAK ADA NULL di customer_id
UPDATE customer SET customer_id = 0 WHERE customer_id IS NULL OR customer_id = '';


-- LANGKAH 4: CEK LAGI, PASTIKAN TIDAK ADA DUPLIKAT
SELECT customer_id, COUNT(*) as jumlah 
FROM customer 
GROUP BY customer_id 
HAVING COUNT(*) > 1;
-- Hasil harus kosong (0 rows)


-- LANGKAH 5: BACKUP! (PENTING!)
-- Buat backup tabel customer sebelum melanjutkan
CREATE TABLE customer_backup_20260121 AS SELECT * FROM customer;


-- LANGKAH 6: PERBAIKAN DUPLIKAT CARA MUDAH
-- Gunakan query ini untuk memperbaiki duplikat secara otomatis

-- Tambah kolom temporary untuk menyimpan ID asli
ALTER TABLE customer ADD COLUMN temp_old_id INT NULL;
UPDATE customer SET temp_old_id = customer_id;

-- Buat ID baru yang unique untuk semua record
SET @new_id = 0;
UPDATE customer 
SET customer_id = (@new_id := @new_id + 1)
ORDER BY temp_old_id, customer_create, customer_nama;

-- Sekarang semua customer_id sudah unique (1, 2, 3, 4, ...)


-- LANGKAH 7: DROP PRIMARY KEY YANG LAMA (jika ada)
-- Cek dulu nama constraint PRIMARY KEY
SHOW KEYS FROM customer WHERE Key_name = 'PRIMARY';

-- Drop PRIMARY KEY (jika sudah ada)
ALTER TABLE customer DROP PRIMARY KEY;


-- LANGKAH 8: UBAH KOLOM MENJADI AUTO_INCREMENT
-- Sekarang tidak akan error karena sudah tidak ada duplikat
ALTER TABLE customer 
MODIFY COLUMN customer_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

-- Hapus kolom temporary
ALTER TABLE customer DROP COLUMN temp_old_id;


-- LANGKAH 9: SET AUTO_INCREMENT VALUE
-- Set nilai auto_increment mulai dari ID tertinggi + 1
SET @max_id = (SELECT MAX(customer_id) FROM customer);
SET @sql = CONCAT('ALTER TABLE customer AUTO_INCREMENT = ', @max_id + 1);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- LANGKAH 10: VERIFIKASI
-- Cek struktur tabel sudah benar
DESCRIBE customer;

-- Cek nilai AUTO_INCREMENT
SHOW TABLE STATUS LIKE 'customer';


-- LANGKAH 11: TEST INSERT
-- Coba insert data baru tanpa customer_id (akan otomatis)
-- INSERT INTO customer (customer_nama, customer_kartu, customer_tlpn, customer_alamat, customer_create, customer_status, customer_category, customer_cabang)
-- VALUES ('Test Customer', '', '08123456789', 'Test Alamat', NOW(), '1', 'REGULER', 0);

-- Cek hasil insert
-- SELECT * FROM customer ORDER BY customer_id DESC LIMIT 1;


-- =====================================================
-- ALTERNATIF: Jika masih error, gunakan cara manual ini
-- =====================================================

-- CARA ALTERNATIF (Jika cara di atas masih error):
-- 1. Export semua data customer
-- SELECT * FROM customer INTO OUTFILE '/tmp/customer_backup.csv'
--    FIELDS TERMINATED BY ',' ENCLOSED BY '"'
--    LINES TERMINATED BY '\n';

-- 2. Drop tabel customer
-- DROP TABLE customer;

-- 3. Buat ulang tabel dengan AUTO_INCREMENT
-- CREATE TABLE customer (
--     customer_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     customer_nama VARCHAR(255) NOT NULL,
--     customer_kartu VARCHAR(50),
--     customer_tlpn VARCHAR(20),
--     customer_email VARCHAR(255),
--     customer_alamat TEXT,
--     customer_create DATETIME,
--     customer_status ENUM('0','1') DEFAULT '1',
--     customer_category VARCHAR(50),
--     customer_cabang INT DEFAULT 0,
--     alamat_dusun VARCHAR(100),
--     alamat_desa VARCHAR(100),
--     alamat_kecamatan VARCHAR(100),
--     alamat_kabupaten VARCHAR(100),
--     alamat_provinsi VARCHAR(100),
--     alamat_kode_provinsi VARCHAR(10),
--     alamat_kode_kabupaten VARCHAR(10),
--     alamat_kode_kecamatan VARCHAR(15),
--     alamat_kode_desa VARCHAR(20),
--     customer_birthday DATE NULL DEFAULT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Import kembali data (gunakan phpMyAdmin atau LOAD DATA INFILE)


-- =====================================================
-- CATATAN PENTING
-- =====================================================
-- 1. SELALU BACKUP DATABASE SEBELUM MELAKUKAN PERUBAHAN STRUKTUR!
-- 2. Jalankan query satu per satu, jangan langsung semua
-- 3. Cek hasil setiap langkah sebelum lanjut ke langkah berikutnya
-- 4. Jika ada error, jangan panik - Anda sudah punya backup
-- 5. Pastikan tidak ada aplikasi yang sedang mengakses database saat ALTER TABLE
