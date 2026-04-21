-- =====================================================
-- Query Poin Customer
-- Poin = FLOOR(total_transaksi / 100000)
-- 1 poin per Rp 100.000 total transaksi
-- =====================================================

-- 1. POIN UNTUK SATU CUSTOMER (ganti 123 dengan customer_id)
-- Setara dengan kode PHP Anda:
--   $qpoin = query("SELECT sum(invoice_total) as total_transaksi ...")[0];
--   $poin = floor($qpoin['total_transaksi'] / 100000);
SELECT 
    i.invoice_customer AS customer_id,
    c.customer_nama,
    COALESCE(SUM(i.invoice_total), 0) AS total_transaksi,
    FLOOR(COALESCE(SUM(i.invoice_total), 0) / 100000) AS poin
FROM invoice i
LEFT JOIN customer c ON c.customer_id = i.invoice_customer
WHERE i.invoice_customer = 123   -- ganti 123 dengan ID customer
GROUP BY i.invoice_customer, c.customer_nama;


-- 2. CUSTOMER YANG ADA POIN SAJA: NAMA, NO KARTU, TOTAL POIN, ALAMAT (cabang 1)
SELECT 
    c.customer_nama AS NAMA,
    c.customer_kartu AS NO_KARTU,
    FLOOR(COALESCE(SUM(i.invoice_total), 0) / 100000) AS TOTAL_POIN,
    c.customer_alamat AS ALAMAT
FROM customer c
INNER JOIN invoice i ON i.invoice_customer = c.customer_id
WHERE c.customer_cabang = 1
GROUP BY c.customer_id, c.customer_nama, c.customer_kartu, c.customer_alamat
HAVING TOTAL_POIN > 0
ORDER BY TOTAL_POIN DESC;


-- 2b. POIN SEMUA CUSTOMER (termasuk yang poin 0, untuk laporan / dashboard)
SELECT 
    c.customer_id,
    c.customer_nama,
    c.customer_tlpn,
    COALESCE(SUM(i.invoice_total), 0) AS total_transaksi,
    FLOOR(COALESCE(SUM(i.invoice_total), 0) / 100000) AS poin
FROM customer c
LEFT JOIN invoice i ON i.invoice_customer = c.customer_id
GROUP BY c.customer_id, c.customer_nama, c.customer_tlpn
ORDER BY poin DESC, total_transaksi DESC;


-- 3. HANYA TOTAL & POIN (untuk dipanggil dari PHP dengan $id)
-- Contoh: SELECT ... WHERE invoice_customer = $id
SELECT 
    COALESCE(SUM(invoice_total), 0) AS total_transaksi,
    FLOOR(COALESCE(SUM(invoice_total), 0) / 100000) AS poin
FROM invoice
WHERE invoice_customer = 123;   -- ganti 123 dengan variabel $id
