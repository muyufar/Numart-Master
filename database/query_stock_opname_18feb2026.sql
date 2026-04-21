-- =====================================================
-- QUERY STOCK OPNAME PER TANGGAL 18 FEBRUARI 2026 JAM 06:00
-- Query ini menghitung stock sistem pada tanggal tertentu dengan mundur dari stock sekarang
-- Rumus: Stock 18 Feb 06:00 = Stock Sekarang + Penjualan Setelah 18 Feb - Pembelian Setelah 18 Feb
-- =====================================================

-- PARAMETER UTAMA
SET @tanggal_cutoff = '2026-02-18 06:00:00';  -- Tanggal dan waktu stock opname
SET @cabang = 0;  -- Ganti dengan ID cabang (0=Gudang, 1=Dukun, 2=Pakis, 3=PP Srumbung, 5=Tegalrejo)


-- ========== CARA 1: STOCK SISTEM PADA 18 FEB 2026 JAM 06:00 (REKOMENDASI) ==========
-- Menghitung stock mundur dari stock sekarang dengan memperhitungkan transaksi setelah cutoff

SELECT 
    ROW_NUMBER() OVER (ORDER BY b.barang_kode) AS No,
    b.barang_kode AS 'Kode/Barcode',
    b.barang_nama AS 'Nama Produk',
    s.satuan_nama AS 'Satuan',
    
    -- Hitung stock pada tanggal cutoff
    (
        b.barang_stock + 
        COALESCE(penjualan_setelah.total_terjual, 0) - 
        COALESCE(pembelian_setelah.total_beli, 0)
    ) AS 'Stock Sistem',
    
    0 AS 'Stock Fisik'
    
FROM barang b
LEFT JOIN satuan s ON b.barang_satuan_id = s.satuan_id

-- Hitung total penjualan SETELAH tanggal cutoff (untuk dikembalikan ke stock)
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_terjual
    FROM penjualan
    WHERE penjualan_date > @tanggal_cutoff
      AND penjualan_cabang = @cabang
    GROUP BY barang_id
) penjualan_setelah ON b.barang_id = penjualan_setelah.barang_id

-- Hitung total pembelian SETELAH tanggal cutoff (untuk dikurangi dari stock)
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_beli
    FROM pembelian
    WHERE pembelian_date > @tanggal_cutoff
      AND pembelian_cabang = @cabang
    GROUP BY barang_id
) pembelian_setelah ON b.barang_id = pembelian_setelah.barang_id

WHERE b.barang_status = '1'
  AND b.barang_cabang = @cabang
  AND (
      b.barang_stock + 
      COALESCE(penjualan_setelah.total_terjual, 0) - 
      COALESCE(pembelian_setelah.total_beli, 0)
  ) > 0  -- Hanya tampilkan barang yang stock-nya di atas 0
ORDER BY b.barang_kode;


-- ========== CARA 2: EXPORT KE EXCEL/CSV (SIAP STOCK OPNAME) ==========
-- Query siap export dengan kolom Stock Fisik, Selisih, dan Keterangan

SELECT 
    @row_number := @row_number + 1 AS 'No',
    b.barang_kode AS 'Kode/Barcode',
    b.barang_nama AS 'Nama Produk',
    s.satuan_nama AS 'Satuan',
    
    -- Hitung stock pada tanggal cutoff
    (
        b.barang_stock + 
        COALESCE(penjualan_setelah.total_terjual, 0) - 
        COALESCE(pembelian_setelah.total_beli, 0)
    ) AS 'Stock Sistem',
    
    '' AS 'Stock Fisik',
    '' AS 'Selisih',
    '' AS 'Keterangan'
    
FROM barang b
CROSS JOIN (SELECT @row_number := 0) AS t
LEFT JOIN satuan s ON b.barang_satuan_id = s.satuan_id

-- Hitung total penjualan SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_terjual
    FROM penjualan
    WHERE penjualan_date > @tanggal_cutoff
      AND penjualan_cabang = @cabang
    GROUP BY barang_id
) penjualan_setelah ON b.barang_id = penjualan_setelah.barang_id

-- Hitung total pembelian SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_beli
    FROM pembelian
    WHERE pembelian_date > @tanggal_cutoff
      AND pembelian_cabang = @cabang
    GROUP BY barang_id
) pembelian_setelah ON b.barang_id = pembelian_setelah.barang_id

WHERE b.barang_status = '1'
  AND b.barang_cabang = @cabang
  AND (
      b.barang_stock + 
      COALESCE(penjualan_setelah.total_terjual, 0) - 
      COALESCE(pembelian_setelah.total_beli, 0)
  ) > 0  -- Hanya tampilkan barang yang stock-nya di atas 0
ORDER BY b.barang_kode;


-- ========== CARA 3: DENGAN DETAIL TRANSAKSI (UNTUK DEBUGGING) ==========
-- Query ini menampilkan detail transaksi untuk verifikasi

SELECT 
    ROW_NUMBER() OVER (ORDER BY b.barang_kode) AS No,
    b.barang_kode AS 'Kode/Barcode',
    b.barang_nama AS 'Nama Produk',
    s.satuan_nama AS 'Satuan',
    b.barang_stock AS 'Stock Sekarang',
    COALESCE(penjualan_setelah.total_terjual, 0) AS 'Terjual Setelah 18 Feb',
    COALESCE(pembelian_setelah.total_beli, 0) AS 'Dibeli Setelah 18 Feb',
    (
        b.barang_stock + 
        COALESCE(penjualan_setelah.total_terjual, 0) - 
        COALESCE(pembelian_setelah.total_beli, 0)
    ) AS 'Stock Pada 18 Feb 06:00',
    0 AS 'Stock Fisik'
    
FROM barang b
LEFT JOIN satuan s ON b.barang_satuan_id = s.satuan_id

-- Hitung total penjualan SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_terjual
    FROM penjualan
    WHERE penjualan_date > @tanggal_cutoff
      AND penjualan_cabang = @cabang
    GROUP BY barang_id
) penjualan_setelah ON b.barang_id = penjualan_setelah.barang_id

-- Hitung total pembelian SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_beli
    FROM pembelian
    WHERE pembelian_date > @tanggal_cutoff
      AND pembelian_cabang = @cabang
    GROUP BY barang_id
) pembelian_setelah ON b.barang_id = pembelian_setelah.barang_id

WHERE b.barang_status = '1'
  AND b.barang_cabang = @cabang
  AND (
      b.barang_stock + 
      COALESCE(penjualan_setelah.total_terjual, 0) - 
      COALESCE(pembelian_setelah.total_beli, 0)
  ) > 0
ORDER BY b.barang_kode;


-- ========== CARA 4: DENGAN VALUASI STOCK ==========
-- Menampilkan harga beli dan total nilai stock

SELECT 
    ROW_NUMBER() OVER (ORDER BY b.barang_kode) AS No,
    b.barang_kode AS 'Kode/Barcode',
    b.barang_nama AS 'Nama Produk',
    k.kategori_nama AS 'Kategori',
    s.satuan_nama AS 'Satuan',
    
    -- Hitung stock pada tanggal cutoff
    (
        b.barang_stock + 
        COALESCE(penjualan_setelah.total_terjual, 0) - 
        COALESCE(pembelian_setelah.total_beli, 0)
    ) AS 'Stock Sistem',
    
    0 AS 'Stock Fisik',
    b.barang_harga_beli AS 'Harga Beli',
    (
        (b.barang_stock + 
         COALESCE(penjualan_setelah.total_terjual, 0) - 
         COALESCE(pembelian_setelah.total_beli, 0)) * 
        b.barang_harga_beli
    ) AS 'Total Nilai Stock'
    
FROM barang b
LEFT JOIN satuan s ON b.barang_satuan_id = s.satuan_id
LEFT JOIN kategori k ON b.kategori_id = k.kategori_id

-- Hitung total penjualan SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_terjual
    FROM penjualan
    WHERE penjualan_date > @tanggal_cutoff
      AND penjualan_cabang = @cabang
    GROUP BY barang_id
) penjualan_setelah ON b.barang_id = penjualan_setelah.barang_id

-- Hitung total pembelian SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_beli
    FROM pembelian
    WHERE pembelian_date > @tanggal_cutoff
      AND pembelian_cabang = @cabang
    GROUP BY barang_id
) pembelian_setelah ON b.barang_id = pembelian_setelah.barang_id

WHERE b.barang_status = '1'
  AND b.barang_cabang = @cabang
  AND (
      b.barang_stock + 
      COALESCE(penjualan_setelah.total_terjual, 0) - 
      COALESCE(pembelian_setelah.total_beli, 0)
  ) > 0
ORDER BY b.barang_kode;


-- ========== CARA 5: CEK TRANSAKSI SETELAH 18 FEB 2026 ==========
-- Query untuk melihat transaksi apa saja yang terjadi setelah tanggal cutoff

-- Cek Penjualan setelah 18 Feb 2026 06:00
SELECT 
    'PENJUALAN' AS Jenis,
    p.penjualan_date AS Tanggal,
    b.barang_kode AS Kode,
    b.barang_nama AS 'Nama Barang',
    p.barang_qty AS Qty,
    p.penjualan_invoice AS Invoice
FROM penjualan p
INNER JOIN barang b ON p.barang_id = b.barang_id
WHERE p.penjualan_date > @tanggal_cutoff
  AND p.penjualan_cabang = @cabang
ORDER BY p.penjualan_date DESC;

-- Cek Pembelian setelah 18 Feb 2026 06:00
SELECT 
    'PEMBELIAN' AS Jenis,
    pm.pembelian_date AS Tanggal,
    b.barang_kode AS Kode,
    b.barang_nama AS 'Nama Barang',
    pm.barang_qty AS Qty,
    pm.pembelian_invoice AS Invoice
FROM pembelian pm
INNER JOIN barang b ON pm.barang_id = b.barang_id
WHERE pm.pembelian_date > @tanggal_cutoff
  AND pm.pembelian_cabang = @cabang
ORDER BY pm.pembelian_date DESC;


-- ========== REKOMENDASI PENGGUNAAN ==========
-- 
-- Untuk STOCK OPNAME tanggal 18 Februari 2026 pukul 06:00:
--
-- LANGKAH 1: Set parameter di atas (sudah diset)
--   SET @tanggal_cutoff = '2026-02-18 06:00:00';
--   SET @cabang = 0;
--
-- LANGKAH 2: Gunakan salah satu query berikut:
--   - CARA 1: Query utama untuk stock opname (REKOMENDASI)
--   - CARA 2: Export ke Excel dengan kolom siap diisi
--   - CARA 3: Debugging - lihat detail perhitungan
--   - CARA 4: Dengan valuasi harga (untuk laporan keuangan)
--   - CARA 5: Cek transaksi setelah tanggal cutoff
--
-- CARA KERJA QUERY:
-- Stock pada 18 Feb 06:00 = Stock Sekarang + Penjualan Setelah 18 Feb - Pembelian Setelah 18 Feb
--
-- Contoh:
-- - Stock sekarang: 100 pcs
-- - Terjual setelah 18 Feb: 20 pcs
-- - Dibeli setelah 18 Feb: 10 pcs
-- - Stock pada 18 Feb = 100 + 20 - 10 = 110 pcs
--
-- TIPS:
-- 1. Jalankan CARA 5 dulu untuk verifikasi ada transaksi apa saja setelah 18 Feb
-- 2. Gunakan CARA 3 untuk debugging jika ada yang aneh
-- 3. Gunakan CARA 2 untuk export ke Excel (paling praktis)
-- 4. Untuk ganti cabang, ubah nilai @cabang di atas
-- 5. Untuk ganti tanggal, ubah nilai @tanggal_cutoff di atas
--
-- =====================================================
