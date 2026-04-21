-- =====================================================
-- PERBAIKAN RELASI DATA SETELAH CUSTOMER_ID BERUBAH
-- Script ini untuk update semua tabel yang terkait dengan customer_id
-- =====================================================

-- ⚠️ CATATAN PENTING ⚠️
-- Script ini diasumsikan sudah ada kolom backup_old_id di tabel customer
-- Kolom backup_old_id berisi customer_id LAMA sebelum di-renumber
-- Jika belum ada, JANGAN jalankan script ini!


-- ========== LANGKAH 1: VERIFIKASI KOLOM BACKUP ADA ==========
-- Cek apakah kolom backup_old_id ada di tabel customer
SELECT COUNT(*) as ada_backup 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'customer' 
  AND COLUMN_NAME = 'backup_old_id';
-- Harus return 1

-- Cek jumlah data di backup
SELECT COUNT(*) as total_dengan_backup 
FROM customer 
WHERE backup_old_id IS NOT NULL;


-- ========== LANGKAH 2: BACKUP TABEL TERKAIT ==========
-- Backup semua tabel yang akan diupdate

-- Backup tabel invoice (PALING PENTING!)
CREATE TABLE IF NOT EXISTS invoice_backup_before_fix AS SELECT * FROM invoice;

-- Backup tabel lain yang mungkin ada
CREATE TABLE IF NOT EXISTS wa_blast_recipients_backup_before_fix AS 
SELECT * FROM wa_blast_recipients WHERE 1=0;  -- struktur saja, data nanti kalau ada

CREATE TABLE IF NOT EXISTS customer_tag_relations_backup_before_fix AS 
SELECT * FROM customer_tag_relations WHERE 1=0;

CREATE TABLE IF NOT EXISTS customer_notes_backup_before_fix AS 
SELECT * FROM customer_notes WHERE 1=0;

-- Isi backup jika tabel ada datanya
INSERT IGNORE INTO wa_blast_recipients_backup_before_fix SELECT * FROM wa_blast_recipients;
INSERT IGNORE INTO customer_tag_relations_backup_before_fix SELECT * FROM customer_tag_relations;
INSERT IGNORE INTO customer_notes_backup_before_fix SELECT * FROM customer_notes;


-- ========== LANGKAH 3: CEK DATA YANG AKAN DIUPDATE ==========
-- Lihat berapa invoice yang perlu diupdate
SELECT 
    COUNT(*) as total_invoice_perlu_update,
    COUNT(DISTINCT i.invoice_customer) as jumlah_customer_unik
FROM invoice i
INNER JOIN customer c ON i.invoice_customer = c.backup_old_id
WHERE i.invoice_customer != c.customer_id;

-- Lihat contoh data yang akan berubah
SELECT 
    i.invoice_id,
    i.penjualan_invoice,
    i.invoice_customer as customer_id_lama,
    c.customer_id as customer_id_baru,
    c.customer_nama
FROM invoice i
INNER JOIN customer c ON i.invoice_customer = c.backup_old_id
WHERE i.invoice_customer != c.customer_id
LIMIT 10;


-- ========== LANGKAH 4: UPDATE TABEL INVOICE ==========
-- Update invoice_customer dengan customer_id yang baru
-- Menggunakan JOIN dengan mapping dari backup_old_id

UPDATE invoice i
INNER JOIN customer c ON i.invoice_customer = c.backup_old_id
SET i.invoice_customer = c.customer_id
WHERE i.invoice_customer != c.customer_id;

-- Cek hasil update
SELECT 
    CONCAT('Total invoice berhasil diupdate: ', ROW_COUNT()) as hasil;


-- ========== LANGKAH 5: UPDATE TABEL LAIN (jika ada) ==========

-- Update wa_blast_recipients (jika tabel ada)
UPDATE wa_blast_recipients w
INNER JOIN customer c ON w.customer_id = c.backup_old_id
SET w.customer_id = c.customer_id
WHERE w.customer_id != c.customer_id
  AND EXISTS (SELECT 1 FROM information_schema.TABLES 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'wa_blast_recipients');

-- Update customer_tag_relations (jika tabel ada)
UPDATE customer_tag_relations ctr
INNER JOIN customer c ON ctr.customer_id = c.backup_old_id
SET ctr.customer_id = c.customer_id
WHERE ctr.customer_id != c.customer_id
  AND EXISTS (SELECT 1 FROM information_schema.TABLES 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'customer_tag_relations');

-- Update customer_notes (jika tabel ada)
UPDATE customer_notes cn
INNER JOIN customer c ON cn.customer_id = c.backup_old_id
SET cn.customer_id = c.customer_id
WHERE cn.customer_id != c.customer_id
  AND EXISTS (SELECT 1 FROM information_schema.TABLES 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'customer_notes');


-- ========== LANGKAH 6: VERIFIKASI HASIL ==========

-- Verifikasi invoice sudah sesuai
SELECT 
    'Invoice' as tabel,
    COUNT(*) as total_records,
    COUNT(DISTINCT i.invoice_customer) as jumlah_customer,
    SUM(CASE WHEN c.customer_id IS NULL THEN 1 ELSE 0 END) as customer_tidak_ditemukan
FROM invoice i
LEFT JOIN customer c ON i.invoice_customer = c.customer_id;

-- Cek apakah ada invoice yang customer_id-nya tidak ada di tabel customer
SELECT 
    i.invoice_id,
    i.penjualan_invoice,
    i.invoice_customer,
    i.invoice_tgl
FROM invoice i
LEFT JOIN customer c ON i.invoice_customer = c.customer_id
WHERE c.customer_id IS NULL
  AND i.invoice_customer > 0  -- exclude customer umum/0
LIMIT 20;


-- ========== LANGKAH 7: TEST DATA PENJUALAN ==========
-- Cek apakah data penjualan sudah normal kembali

-- Test query seperti di penjualan-data.php
SELECT 
    i.invoice_id, 
    i.penjualan_invoice,
    i.invoice_tgl, 
    i.invoice_sub_total, 
    i.invoice_customer,
    c.customer_id,
    c.customer_nama,
    u.user_nama
FROM invoice i
LEFT JOIN user u ON i.invoice_kasir = u.user_id
LEFT JOIN customer c ON i.invoice_customer = c.customer_id
ORDER BY i.invoice_tgl DESC
LIMIT 10;


-- ========== LANGKAH 8: HAPUS KOLOM BACKUP (OPSIONAL) ==========
-- HANYA jalankan ini jika SUDAH YAKIN 100% semua data benar!
-- Simpan dulu kolom ini untuk jaga-jaga

-- ALTER TABLE customer DROP COLUMN backup_old_id;


-- ========== INFO BACKUP ==========
-- Jika ada masalah, restore dari backup:
-- 1. DROP TABLE invoice;
-- 2. CREATE TABLE invoice AS SELECT * FROM invoice_backup_before_fix;
-- 3. Ulangi untuk tabel lain


-- ✅ SELESAI!
-- Sekarang relasi antara invoice dan customer sudah diperbaiki
-- Data penjualan seharusnya sudah normal kembali
