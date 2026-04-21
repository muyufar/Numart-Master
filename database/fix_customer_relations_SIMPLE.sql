-- =====================================================
-- SOLUSI SIMPLE: Perbaiki Relasi Customer di Tabel Invoice
-- Copy-paste script ini ke phpMyAdmin dan jalankan
-- =====================================================

-- ⚠️ PENTING: Pastikan kolom backup_old_id masih ada di tabel customer!
-- Kolom ini berisi customer_id LAMA sebelum di-renumber


-- STEP 1: BACKUP TABEL INVOICE (WAJIB!)
CREATE TABLE invoice_backup_perbaikan_relasi AS SELECT * FROM invoice;


-- STEP 2: CEK DULU - Lihat contoh data yang akan berubah
SELECT 
    i.invoice_id,
    i.penjualan_invoice,
    i.invoice_customer as ID_LAMA,
    c.customer_id as ID_BARU,
    c.customer_nama,
    i.invoice_tgl
FROM invoice i
INNER JOIN customer c ON i.invoice_customer = c.backup_old_id
WHERE i.invoice_customer != c.customer_id
LIMIT 10;
-- Lihat hasilnya, pastikan mapping sudah benar


-- STEP 3: UPDATE INVOICE - Perbaiki customer_id di tabel invoice
UPDATE invoice i
INNER JOIN customer c ON i.invoice_customer = c.backup_old_id
SET i.invoice_customer = c.customer_id
WHERE i.invoice_customer != c.customer_id;

-- Lihat berapa row yang ter-update
SELECT CONCAT('Berhasil update ', ROW_COUNT(), ' invoice') as hasil;


-- STEP 4: UPDATE TABEL LAIN (jika ada)

-- Update wa_blast_recipients
UPDATE wa_blast_recipients w
INNER JOIN customer c ON w.customer_id = c.backup_old_id
SET w.customer_id = c.customer_id
WHERE w.customer_id != c.customer_id;

-- Update customer_tag_relations
UPDATE customer_tag_relations ctr
INNER JOIN customer c ON ctr.customer_id = c.backup_old_id
SET ctr.customer_id = c.customer_id
WHERE ctr.customer_id != c.customer_id;

-- Update customer_notes
UPDATE customer_notes cn
INNER JOIN customer c ON cn.customer_id = c.backup_old_id
SET cn.customer_id = c.customer_id
WHERE cn.customer_id != c.customer_id;


-- STEP 5: VERIFIKASI - Test query penjualan
SELECT 
    i.invoice_id, 
    i.penjualan_invoice,
    i.invoice_tgl, 
    c.customer_nama,
    i.invoice_sub_total
FROM invoice i
LEFT JOIN customer c ON i.invoice_customer = c.customer_id
ORDER BY i.invoice_tgl DESC
LIMIT 10;
-- Pastikan customer_nama muncul dengan benar!


-- STEP 6: CEK ADA MASALAH (harusnya 0 rows)
SELECT 
    i.invoice_id,
    i.penjualan_invoice,
    i.invoice_customer,
    'Customer tidak ditemukan!' as masalah
FROM invoice i
LEFT JOIN customer c ON i.invoice_customer = c.customer_id
WHERE c.customer_id IS NULL
  AND i.invoice_customer > 0;
-- Jika ada hasil, berarti ada invoice dengan customer_id yang tidak valid


-- ✅ SELESAI!
-- Data penjualan sekarang sudah benar
-- Backup tersimpan di tabel: invoice_backup_perbaikan_relasi


-- CATATAN:
-- Jika ingin hapus kolom backup_old_id setelah YAKIN 100% data benar:
-- ALTER TABLE customer DROP COLUMN backup_old_id;

-- Jika ada masalah, restore dari backup:
-- TRUNCATE TABLE invoice;
-- INSERT INTO invoice SELECT * FROM invoice_backup_perbaikan_relasi;
