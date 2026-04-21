-- =====================================================
-- QUERY STOCK OPNAME - SIAP EXPORT KE EXCEL
-- Tanggal: 18 Februari 2026 Jam 06:00
-- =====================================================

-- ========== GANTI PARAMETER DI BAWAH INI ==========
-- Ganti '2026-02-18 06:00:00' dengan tanggal yang diinginkan
-- Ganti 5 dengan cabang yang diinginkan (0=Gudang, 1=Dukun, 2=Pakis, 3=PP Srumbung, 5=Tegalrejo)


-- ========== QUERY UTAMA (LANGSUNG COPY-PASTE KE phpMyAdmin) ==========

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
    
FROM (SELECT @row_number := 0) AS init
CROSS JOIN barang b
LEFT JOIN satuan s ON b.barang_satuan_id = s.satuan_id

-- Hitung total penjualan SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_terjual
    FROM penjualan
    WHERE penjualan_date > '2026-02-18 06:00:00'  -- ⭐ GANTI TANGGAL DI SINI
      AND penjualan_cabang = 5  -- ⭐ GANTI CABANG DI SINI
    GROUP BY barang_id
) penjualan_setelah ON b.barang_id = penjualan_setelah.barang_id

-- Hitung total pembelian SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        SUM(barang_qty) AS total_beli
    FROM pembelian
    WHERE pembelian_date > '2026-02-18 06:00:00'  -- ⭐ GANTI TANGGAL DI SINI
      AND pembelian_cabang = 5  -- ⭐ GANTI CABANG DI SINI
    GROUP BY barang_id
) pembelian_setelah ON b.barang_id = pembelian_setelah.barang_id

WHERE b.barang_status = '1'
  AND b.barang_cabang = 5  -- ⭐ GANTI CABANG DI SINI
  AND (
      b.barang_stock + 
      COALESCE(penjualan_setelah.total_terjual, 0) - 
      COALESCE(pembelian_setelah.total_beli, 0)
  ) > 0  -- Hanya tampilkan barang yang stock-nya di atas 0
ORDER BY b.barang_kode;


-- ========== CARA PENGGUNAAN ==========
-- 
-- 1. UBAH PARAMETER:
--    - Ganti '2026-02-18 06:00:00' dengan tanggal stock opname
--    - Ganti angka 5 dengan ID cabang:
--      * 0 = Gudang
--      * 1 = Dukun
--      * 2 = Pakis
--      * 3 = PP Srumbung
--      * 5 = Tegalrejo
--
-- 2. COPY seluruh query SELECT di atas (mulai dari SELECT sampai ORDER BY)
--
-- 3. PASTE di phpMyAdmin tab SQL
--
-- 4. Klik "Go" untuk melihat hasilnya
--
-- 5. Klik "Export" untuk download ke Excel
--
-- CATATAN: Tanggal dan cabang sudah hardcoded di dalam query,
--          jadi tidak perlu SET variable terpisah
--
-- =====================================================


-- ========== ALTERNATIF: QUERY UNTUK SEMUA CABANG ==========

SELECT 
    @row_number := @row_number + 1 AS 'No',
    b.barang_kode AS 'Kode/Barcode',
    b.barang_nama AS 'Nama Produk',
    s.satuan_nama AS 'Satuan',
    CASE b.barang_cabang
        WHEN 0 THEN 'Gudang'
        WHEN 1 THEN 'Dukun'
        WHEN 2 THEN 'Pakis'
        WHEN 3 THEN 'PP Srumbung'
        WHEN 5 THEN 'Tegalrejo'
        ELSE 'Unknown'
    END AS 'Cabang',
    
    -- Hitung stock pada tanggal cutoff
    (
        b.barang_stock + 
        COALESCE(penjualan_setelah.total_terjual, 0) - 
        COALESCE(pembelian_setelah.total_beli, 0)
    ) AS 'Stock Sistem',
    
    '' AS 'Stock Fisik',
    '' AS 'Selisih',
    '' AS 'Keterangan'
    
FROM (SELECT @row_number := 0) AS init
CROSS JOIN barang b
LEFT JOIN satuan s ON b.barang_satuan_id = s.satuan_id

-- Hitung total penjualan SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        penjualan_cabang,
        SUM(barang_qty) AS total_terjual
    FROM penjualan
    WHERE penjualan_date > '2026-02-18 06:00:00'  -- ⭐ GANTI TANGGAL DI SINI
    GROUP BY barang_id, penjualan_cabang
) penjualan_setelah ON b.barang_id = penjualan_setelah.barang_id 
                     AND b.barang_cabang = penjualan_setelah.penjualan_cabang

-- Hitung total pembelian SETELAH tanggal cutoff
LEFT JOIN (
    SELECT 
        barang_id,
        pembelian_cabang,
        SUM(barang_qty) AS total_beli
    FROM pembelian
    WHERE pembelian_date > '2026-02-18 06:00:00'  -- ⭐ GANTI TANGGAL DI SINI
    GROUP BY barang_id, pembelian_cabang
) pembelian_setelah ON b.barang_id = pembelian_setelah.barang_id
                     AND b.barang_cabang = pembelian_setelah.pembelian_cabang

WHERE b.barang_status = '1'
  AND (
      b.barang_stock + 
      COALESCE(penjualan_setelah.total_terjual, 0) - 
      COALESCE(pembelian_setelah.total_beli, 0)
  ) > 0
ORDER BY b.barang_cabang, b.barang_kode;
