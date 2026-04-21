-- =====================================================
-- SOLUSI SIMPLE: Fix Duplikat dan Ubah ke AUTO_INCREMENT
-- File ini sudah siap pakai, tinggal copy-paste dan jalankan
-- =====================================================

-- ⚠️ PENTING: BACKUP DULU! ⚠️
CREATE TABLE customer_backup_sebelum_auto_increment AS SELECT * FROM customer;


-- ========== CEK DUPLIKAT (Opsional, untuk melihat masalahnya) ==========
SELECT customer_id, customer_nama, customer_tlpn, COUNT(*) as jumlah_duplikat
FROM customer 
GROUP BY customer_id 
HAVING COUNT(*) > 1
ORDER BY customer_id;


-- ========== PERBAIKI DUPLIKAT SECARA OTOMATIS ==========
-- Cara 1: Buat semua ID jadi unique dengan re-numbering

-- Tambah kolom sementara untuk backup ID lama
ALTER TABLE customer ADD COLUMN backup_old_id INT NULL;
UPDATE customer SET backup_old_id = customer_id;

-- Drop PRIMARY KEY dulu (jika ada)
-- Jika error "can't DROP 'PRIMARY'; check that column/key exists", skip saja
ALTER TABLE customer DROP PRIMARY KEY;

-- Re-number semua customer_id jadi urut 1, 2, 3, dst (tidak ada duplikat)
SET @row_number = 0;
UPDATE customer 
SET customer_id = (@row_number := @row_number + 1)
ORDER BY backup_old_id, customer_create, customer_nama;


-- ========== UBAH JADI AUTO_INCREMENT ==========
-- Sekarang bisa langsung karena sudah tidak ada duplikat
ALTER TABLE customer 
MODIFY COLUMN customer_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY;


-- ========== SET AUTO_INCREMENT VALUE ==========
-- Mulai dari ID tertinggi + 1
SET @max = (SELECT MAX(customer_id) FROM customer);
SET @sql = CONCAT('ALTER TABLE customer AUTO_INCREMENT = ', @max + 1);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ========== HAPUS KOLOM BACKUP (Opsional) ==========
-- Jika sudah yakin berhasil, hapus kolom backup
-- ALTER TABLE customer DROP COLUMN backup_old_id;


-- ========== VERIFIKASI ==========
-- Cek struktur tabel
DESCRIBE customer;

-- Cek tidak ada duplikat lagi
SELECT customer_id, COUNT(*) as jumlah 
FROM customer 
GROUP BY customer_id 
HAVING COUNT(*) > 1;
-- Harus kosong (0 rows)

-- Cek AUTO_INCREMENT value
SHOW TABLE STATUS LIKE 'customer';


-- ========== TEST INSERT ==========
-- Test insert data baru (customer_id otomatis)
-- INSERT INTO customer (customer_nama, customer_kartu, customer_tlpn, customer_alamat, customer_create, customer_status, customer_category, customer_cabang)
-- VALUES ('Test Auto Increment', '', '08123456789', 'Test Alamat', NOW(), '1', 'REGULER', 0);

-- Lihat hasil
-- SELECT * FROM customer ORDER BY customer_id DESC LIMIT 5;


-- ✅ SELESAI!
-- Sekarang tabel customer sudah menggunakan AUTO_INCREMENT
-- Data lama masih ada di tabel: customer_backup_sebelum_auto_increment
