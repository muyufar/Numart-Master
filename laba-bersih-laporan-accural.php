<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';
include 'aksi/koneksi.php';

if ($levelLogin != "admin" && $levelLogin != "super admin") {
  echo "<script>document.location.href = 'bo';</script>";
  exit;
}

$listCabang = query("SELECT * FROM toko");

$tanggal_awal = $_POST['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_POST['tanggal_akhir'] ?? date('Y-m-t');
$cabang = $_POST['cabang'] ?? $_SESSION['user_cabang'];

function rupiah($angka)
{
  return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Ambil data toko
$toko = query("SELECT * FROM toko WHERE toko_cabang = '$cabang' ")[0];

/* -------------------------------------------
   0. PERSEDIAAN BARANG
   
   CABANG 0 (PUSAT):
   - Persediaan Awal = Total Pembelian (invoice_pembelian)
   - Persediaan Akhir = Pembelian - HPP - Transfer Stock ke cabang lain
   
   CABANG LAIN (1,2,3,dst):
   - Persediaan Awal = Total Transfer Stock yang diterima dari Cabang 0
   - Persediaan Akhir = Transfer Stock - HPP (penjualan)
------------------------------------------- */

if ($cabang == 0) {
  // CABANG 0: Persediaan dari pembelian
  $q_persediaan_barang = mysqli_query($conn, "
    SELECT COALESCE(SUM(invoice_total), 0) AS total
    FROM invoice_pembelian
    WHERE invoice_pembelian_cabang = '$cabang'
    AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
  ");
  $persediaan_awal = mysqli_fetch_assoc($q_persediaan_barang)['total'] ?? 0;
  $persediaan_label = "Total Pembelian Barang";
} else {
  // CABANG LAIN: Persediaan dari transfer stock yang diterima dari cabang 0
  $q_transfer_masuk = mysqli_query($conn, "
    SELECT COALESCE(SUM(tpk_qty * b.barang_harga_beli), 0) AS total
    FROM transfer_produk_keluar tpk
    JOIN barang b ON tpk.tpk_barang_id = b.barang_id
    WHERE tpk.tpk_pengirim_cabang = 0
    AND tpk.tpk_penerima_cabang = '$cabang'
    AND tpk.tpk_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
  ");
  $persediaan_awal = mysqli_fetch_assoc($q_transfer_masuk)['total'] ?? 0;
  $persediaan_label = "Transfer Stock dari Pusat";
}

/* -------------------------------------------
   1. PENJUALAN
   CATATAN: DP Kredit dihitung sebagai bagian dari Penjualan Cash
   - Penjualan Cash = penjualan cash biasa + DP dari penjualan kredit
   - Penjualan Kredit = total penjualan kredit - DP yang sudah dibayar
------------------------------------------- */
$q_penjualan = mysqli_query($conn, "
  SELECT 
    SUM(CASE WHEN invoice_piutang = 0 THEN invoice_sub_total ELSE 0 END) AS total_cash_biasa,
    SUM(CASE WHEN invoice_piutang = 1 THEN invoice_sub_total ELSE 0 END) AS total_kredit_penuh,
    SUM(CASE WHEN invoice_piutang = 1 THEN invoice_piutang_dp ELSE 0 END) AS total_dp,
    SUM(invoice_sub_total) AS total_penjualan
  FROM invoice
  WHERE invoice_cabang = '$cabang'
  AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$penjualan = mysqli_fetch_assoc($q_penjualan);
$total_cash_biasa = $penjualan['total_cash_biasa'] ?? 0;
$total_kredit_penuh = $penjualan['total_kredit_penuh'] ?? 0;
$total_dp = $penjualan['total_dp'] ?? 0;
$total_penjualan = $penjualan['total_penjualan'] ?? 0;

// Hitung penjualan cash (cash biasa + DP) dan penjualan kredit (kredit - DP)
$total_cash = $total_cash_biasa + $total_dp;
$total_kredit = $total_kredit_penuh - $total_dp;

/* -------------------------------------------
   3. HPP
------------------------------------------- */
$q_hpp = mysqli_query($conn, "
  SELECT SUM(invoice_total_beli) AS total_hpp
  FROM invoice
  WHERE invoice_cabang = '$cabang'
  AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$hpp = mysqli_fetch_assoc($q_hpp)['total_hpp'] ?? 0;

// Persediaan Akhir akan dihitung setelah total_transfer_stok tersedia (untuk cabang 0)

/* -------------------------------------------
   4. Pendapatan Lain-lain (laba.tipe = 0)
   CATATAN: Menggunakan l.date (tanggal transaksi dilakukan), BUKAN created_at (tanggal dibuat)
------------------------------------------- */
$q_pendapatan_lain = mysqli_query($conn, "
  SELECT 
    COALESCE(lk.name, 'Tanpa Kategori') AS kategori_nama,
    SUM(CAST(REPLACE(REPLACE(l.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) AS total 
  FROM laba l
  LEFT JOIN laba_kategori lk ON CAST(l.kategori AS UNSIGNED) = lk.id
  WHERE l.tipe = 0
  AND l.cabang = '$cabang'
  AND l.date >= '$tanggal_awal 00:00:00'
  AND l.date <= '$tanggal_akhir 23:59:59'
  GROUP BY lk.name
  ORDER BY lk.name
");
$pendapatan_lain = [];
$total_pendapatan_lain = 0;
while ($row = mysqli_fetch_assoc($q_pendapatan_lain)) {
  $pendapatan_lain[] = $row;
  $total_pendapatan_lain += $row['total'];
}

/* -------------------------------------------
   5. Pengeluaran / Beban Operasi (laba.tipe = 1)
   CATATAN: 
   - Menggunakan l.date (tanggal transaksi dilakukan), BUKAN created_at (tanggal dibuat)
   - Hanya kategori dengan label 'beban' yang masuk ke Beban Operasi
------------------------------------------- */
$q_pengeluaran = mysqli_query($conn, "
  SELECT 
    COALESCE(lk.name, 'Tanpa Kategori') AS kategori_nama,
    SUM(CAST(REPLACE(REPLACE(l.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) AS total 
  FROM laba l
  LEFT JOIN laba_kategori lk ON CAST(l.kategori AS UNSIGNED) = lk.id
  WHERE l.tipe = 1
  AND l.cabang = '$cabang'
  AND l.date >= '$tanggal_awal 00:00:00'
  AND l.date <= '$tanggal_akhir 23:59:59'
  AND lk.kategori = 'beban'
  GROUP BY lk.name
  ORDER BY lk.name
");
$pengeluaran = [];
$total_pengeluaran = 0;
while ($row = mysqli_fetch_assoc($q_pengeluaran)) {
  $pengeluaran[] = $row;
  $total_pengeluaran += $row['total'];
}

/* -------------------------------------------
   6. Sharing Profit (khusus cabang 0)
   CATATAN: Hanya menghitung beban operasi (kategori 'beban')
------------------------------------------- */
$sharing_profit = 0;
$sharing_detail = [];

if ($cabang == 0) {
  // Cabang 1 → 45%
  $q_laba_cbg1 = mysqli_query($conn, "
    SELECT 
      (SUM(invoice_sub_total) 
       - SUM(invoice_total_beli)
       - COALESCE((
          SELECT SUM(CAST(REPLACE(REPLACE(l2.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) 
          FROM laba l2 
          LEFT JOIN laba_kategori lk2 ON CAST(l2.kategori AS UNSIGNED) = lk2.id
          WHERE l2.tipe = 1 
          AND l2.cabang = 1 
          AND l2.date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
          AND lk2.kategori = 'beban'
        ),0)
      ) AS laba_bersih_cabang1
    FROM invoice
    WHERE invoice_cabang = 1
    AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
  ");
  $laba_cbg1 = mysqli_fetch_assoc($q_laba_cbg1)['laba_bersih_cabang1'] ?? 0;
  $sharing_cbg1 = $laba_cbg1 * 0.45;
  $sharing_profit += $sharing_cbg1;
  $sharing_detail[] = ['nama' => 'Sharing Profit NUMART DUKUN (45%)', 'nilai' => $sharing_cbg1];

  // Cabang 3 → 50%
  $q_laba_cbg3 = mysqli_query($conn, "
    SELECT 
      (SUM(invoice_sub_total) 
       - SUM(invoice_total_beli)
       - COALESCE((
          SELECT SUM(CAST(REPLACE(REPLACE(l2.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) 
          FROM laba l2 
          LEFT JOIN laba_kategori lk2 ON CAST(l2.kategori AS UNSIGNED) = lk2.id
          WHERE l2.tipe = 1 
          AND l2.cabang = 3 
          AND l2.date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
          AND lk2.kategori = 'beban'
        ),0)
      ) AS laba_bersih_cabang3
    FROM invoice
    WHERE invoice_cabang = 3
    AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
  ");
  $laba_cbg3 = mysqli_fetch_assoc($q_laba_cbg3)['laba_bersih_cabang3'] ?? 0;
  $sharing_cbg3 = $laba_cbg3 * 0.50;
  $sharing_profit += $sharing_cbg3;
  $sharing_detail[] = ['nama' => 'Sharing Profit PONDOK SRUMBUNG (50%)', 'nilai' => $sharing_cbg3];
}

/* -------------------------------------------
   7. Pendapatan Lain (Bagi Hasil dari Cabang)
   CATATAN: 
   - 45% dari laba bersih Numart Dukun, 50% dari laba bersih Pondok Srumbung
   - 30% dari laba bersih Numart Tren Pakis, 45% dari laba bersih Numart Tegalrejo
   - Laba Bersih = Total Pendapatan - HPP - Total Beban Operasi (hanya kategori 'beban')
------------------------------------------- */
$pendapatan_lain_bagi_hasil = 0;
$pendapatan_lain_detail = [];

if ($cabang == 0) {
  // Hitung laba bersih Numart Dukun (Cabang 1)
  $q_laba_bersih_cbg1 = mysqli_query($conn, "
    SELECT 
      (SUM(invoice_sub_total) 
       - SUM(invoice_total_beli)
       - COALESCE((
          SELECT SUM(CAST(REPLACE(REPLACE(l2.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) 
          FROM laba l2 
          LEFT JOIN laba_kategori lk2 ON CAST(l2.kategori AS UNSIGNED) = lk2.id
          WHERE l2.tipe = 1 
          AND l2.cabang = 1 
          AND l2.date >= '$tanggal_awal 00:00:00'
          AND l2.date <= '$tanggal_akhir 23:59:59'
          AND lk2.kategori = 'beban'
        ),0)
      ) AS laba_bersih_cabang1
    FROM invoice
    WHERE invoice_cabang = 1
    AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
  ");
  $laba_bersih_cbg1 = mysqli_fetch_assoc($q_laba_bersih_cbg1)['laba_bersih_cabang1'] ?? 0;
  $bagi_hasil_cbg1 = $laba_bersih_cbg1 * 0.45;
  $pendapatan_lain_bagi_hasil += $bagi_hasil_cbg1;
  $pendapatan_lain_detail[] = [
    'nama' => 'Bagi Hasil Numart Dukun (45%)',
    'nilai' => $bagi_hasil_cbg1
  ];

  // Hitung laba bersih Numart Pondok Srumbung (Cabang 3)
  $q_laba_bersih_cbg3 = mysqli_query($conn, "
    SELECT 
      (SUM(invoice_sub_total) 
       - SUM(invoice_total_beli)
       - COALESCE((
          SELECT SUM(CAST(REPLACE(REPLACE(l2.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) 
          FROM laba l2 
          LEFT JOIN laba_kategori lk2 ON CAST(l2.kategori AS UNSIGNED) = lk2.id
          WHERE l2.tipe = 1 
          AND l2.cabang = 3 
          AND l2.date >= '$tanggal_awal 00:00:00'
          AND l2.date <= '$tanggal_akhir 23:59:59'
          AND lk2.kategori = 'beban'
        ),0)
      ) AS laba_bersih_cabang3
    FROM invoice
    WHERE invoice_cabang = 3
    AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
  ");
  $laba_bersih_cbg3 = mysqli_fetch_assoc($q_laba_bersih_cbg3)['laba_bersih_cabang3'] ?? 0;
  $bagi_hasil_cbg3 = $laba_bersih_cbg3 * 0.50;
  $pendapatan_lain_bagi_hasil += $bagi_hasil_cbg3;
  $pendapatan_lain_detail[] = [
    'nama' => 'Bagi Hasil Numart Pondok Srumbung (50%)',
    'nilai' => $bagi_hasil_cbg3
  ];

  // Hitung laba bersih Numart Tren Pakis (Cabang 2)
  $q_laba_bersih_cbg2 = mysqli_query($conn, "
    SELECT 
      (SUM(invoice_sub_total) 
       - SUM(invoice_total_beli)
       - COALESCE((
          SELECT SUM(CAST(REPLACE(REPLACE(l2.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) 
          FROM laba l2 
          LEFT JOIN laba_kategori lk2 ON CAST(l2.kategori AS UNSIGNED) = lk2.id
          WHERE l2.tipe = 1 
          AND l2.cabang = 2 
          AND l2.date >= '$tanggal_awal 00:00:00'
          AND l2.date <= '$tanggal_akhir 23:59:59'
          AND lk2.kategori = 'beban'
        ),0)
      ) AS laba_bersih_cabang2
    FROM invoice
    WHERE invoice_cabang = 2
    AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
  ");
  $laba_bersih_cbg2 = mysqli_fetch_assoc($q_laba_bersih_cbg2)['laba_bersih_cabang2'] ?? 0;
  $bagi_hasil_cbg2 = $laba_bersih_cbg2 * 0.30;
  $pendapatan_lain_bagi_hasil += $bagi_hasil_cbg2;
  $pendapatan_lain_detail[] = [
    'nama' => 'Bagi Hasil Numart Tren Pondok Pakis (30%)',
    'nilai' => $bagi_hasil_cbg2
  ];

    // Hitung laba bersih Numart Tegalrejo (Cabang 5)
  $q_laba_bersih_cbg5 = mysqli_query($conn, "
  SELECT 
    (SUM(invoice_sub_total) 
     - SUM(invoice_total_beli)
     - COALESCE((
        SELECT SUM(CAST(REPLACE(REPLACE(l2.jumlah, '.', ''), ',', '') AS DECIMAL(18,2))) 
        FROM laba l2 
          LEFT JOIN laba_kategori lk2 ON CAST(l2.kategori AS UNSIGNED) = lk2.id
        WHERE l2.tipe = 1 
        AND l2.cabang = 5 
        AND l2.date >= '$tanggal_awal 00:00:00'
        AND l2.date <= '$tanggal_akhir 23:59:59'
          AND lk2.kategori = 'beban'
      ),0)
    ) AS laba_bersih_cabang5
  FROM invoice
  WHERE invoice_cabang = 5
  AND invoice_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$laba_bersih_cbg5 = mysqli_fetch_assoc($q_laba_bersih_cbg5)['laba_bersih_cabang5'] ?? 0;
$bagi_hasil_cbg5 = $laba_bersih_cbg5 * 0.45;
$pendapatan_lain_bagi_hasil += $bagi_hasil_cbg5;
$pendapatan_lain_detail[] = [
  'nama' => 'Bagi Hasil Numart Tegalrejo (45%)',
  'nilai' => $bagi_hasil_cbg5
];
}

/* -------------------------------------------
   8. Hitung Total Laba
------------------------------------------- */
$total_pendapatan = $total_cash + $total_kredit + $total_pendapatan_lain + $sharing_profit;
$laba_kotor = $total_penjualan - $hpp;
$laba_operasi = $laba_kotor - $total_pengeluaran; // Laba Operasi = Laba Kotor - Beban Operasi
$beban_lain = 0; // Beban Lain (bisa ditambahkan nanti jika diperlukan)
$laba_bersih = $laba_operasi + $pendapatan_lain_bagi_hasil - $beban_lain;
// Note: DP sudah termasuk dalam total_cash, jadi tidak perlu ditambahkan lagi
$persentase = $hpp > 0 ? round(($laba_bersih / $hpp) * 100, 2) : 0;

/* ========================================================
   9. LAPORAN NERACA
   CATATAN: Hanya menampilkan akun sesuai cabang yang dipilih
======================================================== */
// Escape input untuk keamanan
$cabang_escaped = mysqli_real_escape_string($conn, $cabang);
$tanggal_awal_escaped = mysqli_real_escape_string($conn, $tanggal_awal);
$tanggal_akhir_escaped = mysqli_real_escape_string($conn, $tanggal_akhir);

// Ambil kategori dari laba_kategori sesuai cabang yang dipilih
$q_kategori_neraca = mysqli_query($conn, "
  SELECT
    lk.id,
    lk.name,
    lk.kode_akun,
    lk.kategori,
    lk.tipe_akun,
    COALESCE(lk.saldo, 0) AS saldo_awal
  FROM laba_kategori lk
  WHERE lk.cabang = '$cabang_escaped'
  ORDER BY lk.kategori, lk.name
");

// Check for query errors
if (!$q_kategori_neraca) {
  die("Query error: " . mysqli_error($conn));
}

$neraca = [
  'aktiva' => [],
  'pasiva' => [],
  'modal' => []
];

$total_aktiva = 0;
$total_pasiva = 0;
$total_modal = 0;

$jumlah_kategori_ditemukan = 0;

while ($row_kat = mysqli_fetch_assoc($q_kategori_neraca)) {
  $kat_id = mysqli_real_escape_string($conn, $row_kat['id']);
  $kategori_raw = trim($row_kat['kategori'] ?? '');

  // Gunakan data langsung dari database (case-insensitive)
  $kategori_raw_lower = strtolower(trim($kategori_raw));

  // Cek langsung apakah kategori adalah aktiva, pasiva, atau modal
  $kategori = null;
  if (in_array($kategori_raw_lower, ['aktiva', 'pasiva', 'modal'])) {
    $kategori = $kategori_raw_lower;
  }

  // Skip jika bukan kategori neraca
  if (!$kategori) {
    continue;
  }

  $jumlah_kategori_ditemukan++;
  $tipe_akun = $row_kat['tipe_akun'] ?? '';
  $saldo_awal = floatval($row_kat['saldo_awal'] ?? 0);

  // Hitung mutasi dari tabel laba dalam periode
  $q_mutasi = mysqli_query($conn, "
    SELECT 
      COALESCE(SUM(CASE 
        WHEN l.tipe = 0 AND l.jumlah IS NOT NULL AND l.jumlah != '' AND l.jumlah != '0'
        THEN CAST(REPLACE(REPLACE(REPLACE(l.jumlah, '.', ''), ',', ''), ' ', '') AS DECIMAL(18,2)) 
        ELSE 0 
      END), 0) AS total_masuk,
      COALESCE(SUM(CASE 
        WHEN l.tipe = 1 AND l.jumlah IS NOT NULL AND l.jumlah != '' AND l.jumlah != '0'
        THEN CAST(REPLACE(REPLACE(REPLACE(l.jumlah, '.', ''), ',', ''), ' ', '') AS DECIMAL(18,2)) 
        ELSE 0 
      END), 0) AS total_keluar
    FROM laba l
    WHERE CAST(l.kategori AS UNSIGNED) = $kat_id
    AND l.cabang = '$cabang_escaped'
    AND l.date >= '$tanggal_awal_escaped 00:00:00'
    AND l.date <= '$tanggal_akhir_escaped 23:59:59'
  ");

  if (!$q_mutasi) {
    error_log("Error query mutasi untuk kategori $kat_id: " . mysqli_error($conn));
    continue;
  }

  $mutasi = mysqli_fetch_assoc($q_mutasi);
  $total_masuk = floatval($mutasi['total_masuk'] ?? 0);
  $total_keluar = floatval($mutasi['total_keluar'] ?? 0);

  // Hitung saldo akhir berdasarkan konsep akuntansi sederhana
  $saldo_akhir = 0;

  if ($kategori == 'aktiva') {
    // Aktiva: normal saldo DEBIT
    if ($tipe_akun == 'debit') {
      $saldo_akhir = $saldo_awal + $total_masuk - $total_keluar;
    } else {
      $saldo_akhir = $saldo_awal - $total_masuk + $total_keluar;
    }
  } else if ($kategori == 'pasiva') {
    // Pasiva: normal saldo KREDIT
    if ($tipe_akun == 'kredit') {
      $saldo_akhir = $saldo_awal - $total_masuk + $total_keluar;
    } else {
      $saldo_akhir = $saldo_awal + $total_masuk - $total_keluar;
    }
  } else if ($kategori == 'modal') {
    // Modal: normal saldo KREDIT
    if ($tipe_akun == 'kredit') {
      $saldo_akhir = $saldo_awal + $total_masuk - $total_keluar;
    } else {
      $saldo_akhir = $saldo_awal - $total_masuk + $total_keluar;
    }
  }

  $kode_akun = !empty($row_kat['kode_akun']) ? trim($row_kat['kode_akun']) : '-';

  // Extract prefix untuk grouping
  $prefix_group = '-';
  if ($kode_akun != '-' && preg_match('/^(\d+)-(\d{3})/', $kode_akun, $matches)) {
    $prefix_group = $matches[1] . '-' . $matches[2];
  } elseif ($kode_akun != '-' && preg_match('/^(\d+)-/', $kode_akun, $matches)) {
    $prefix_group = $matches[1] . '-';
  }

  $data_neraca = [
    'id' => $row_kat['id'],
    'kode_akun' => $kode_akun,
    'prefix_group' => $prefix_group,
    'name' => !empty($row_kat['name']) ? $row_kat['name'] : '-',
    'tipe_akun' => !empty($tipe_akun) ? $tipe_akun : '-',
    'saldo_awal' => $saldo_awal,
    'total_masuk' => $total_masuk,
    'total_keluar' => $total_keluar,
    'saldo_akhir' => $saldo_akhir
  ];

  // Masukkan ke array neraca hanya jika saldo akhir != 0
  if ($saldo_akhir != 0) {
    if ($kategori == 'aktiva') {
      $neraca['aktiva'][] = $data_neraca;
      $total_aktiva += $saldo_akhir;
    } else if ($kategori == 'pasiva') {
      $neraca['pasiva'][] = $data_neraca;
      $total_pasiva += $saldo_akhir;
    } else if ($kategori == 'modal') {
      $neraca['modal'][] = $data_neraca;
      $total_modal += $saldo_akhir;
    }
  }
}

// Total Pasiva & Modal
$total_pasiva_modal = $total_pasiva + $total_modal;

/* ========================================================
   7. TAMBAHKAN LABA BERSIH KE KAS TUNAI (SEBELUM GROUPING)
======================================================== */
// Cari akun kas tunai di aktiva (bisa dengan kode 1-100, 1-101, 1-1100, atau nama mengandung "Kas Tunai")
$kas_tunai_ditemukan = false;
$kas_tunai_index = -1;

if (!empty($neraca['aktiva'])) {
  foreach ($neraca['aktiva'] as $index => $akt) {
    $kode_akun = strtolower(trim($akt['kode_akun'] ?? ''));
    $nama_akun = strtolower(trim($akt['name'] ?? ''));
    
    // Cek apakah ini akun kas tunai
    if (
      preg_match('/^1-(100|101|1100)/', $kode_akun) || 
      strpos($nama_akun, 'kas tunai') !== false ||
      (strpos($nama_akun, 'kas') !== false && strpos($nama_akun, 'bank') === false)
    ) {
      $kas_tunai_ditemukan = true;
      $kas_tunai_index = $index;
      break;
    }
  }
}

// Tambahkan laba bersih ke kas tunai
if ($laba_bersih != 0) {
  if ($kas_tunai_ditemukan && $kas_tunai_index >= 0) {
    // Jika kas tunai sudah ada, tambahkan laba bersih ke saldo akhir
    $neraca['aktiva'][$kas_tunai_index]['saldo_akhir'] += $laba_bersih;
    $neraca['aktiva'][$kas_tunai_index]['total_masuk'] += $laba_bersih;
    
    // Update total aktiva
    $total_aktiva += $laba_bersih;
  } else {
    // Jika kas tunai belum ada, tambahkan sebagai item baru
    $kas_tunai_baru = [
      'id' => 'kas_tunai_laba_bersih',
      'kode_akun' => '1-1100',
      'prefix_group' => '1-110',
      'name' => 'Kas Tunai (dari Laba Bersih)',
      'tipe_akun' => 'debit',
      'saldo_awal' => 0,
      'total_masuk' => $laba_bersih,
      'total_keluar' => 0,
      'saldo_akhir' => $laba_bersih
    ];
    
    // Tambahkan ke array aktiva
    $neraca['aktiva'][] = $kas_tunai_baru;
    $total_aktiva += $laba_bersih;
  }
}

// Grouping berdasarkan prefix kode akun
function groupByPrefix($items)
{
  $grouped = [];
  foreach ($items as $item) {
    $prefix = $item['prefix_group'];
    if (!isset($grouped[$prefix])) {
      $grouped[$prefix] = [
        'name' => $prefix,
        'items' => [],
        'total' => 0
      ];
    }
    $grouped[$prefix]['items'][] = $item;
    $grouped[$prefix]['total'] += $item['saldo_akhir'];
  }
  return $grouped;
}

// Group aktiva, pasiva, dan modal berdasarkan prefix kode akun
$aktiva_grouped = !empty($neraca['aktiva']) ? groupByPrefix($neraca['aktiva']) : [];
$pasiva_grouped = !empty($neraca['pasiva']) ? groupByPrefix($neraca['pasiva']) : [];
$modal_grouped = !empty($neraca['modal']) ? groupByPrefix($neraca['modal']) : [];

// Hitung total harta lancar dan tetap berdasarkan prefix
$total_harta_lancar = 0;
$total_harta_tetap = 0;

foreach ($aktiva_grouped as $prefix => $group) {
  if (preg_match('/^1-(100|101|102|110|200|201|202)/', $prefix)) {
    $total_harta_lancar += $group['total'];
  } elseif (preg_match('/^1-(107|108|109)/', $prefix)) {
    $total_harta_tetap += $group['total'];
  }
}

/* ========================================================
   8. TOTAL TRANSFER STOK (Cabang Utama)
======================================================== */
$total_transfer_stok = 0;
$transfer_detail = [];

if ($cabang == 0) {
  $q_transfer = mysqli_query($conn, "
    SELECT 
      tpk_penerima_cabang,
      COALESCE(SUM(tpk_qty * b.barang_harga_beli), 0) AS total_transfer
    FROM transfer_produk_keluar tpk
    JOIN barang b ON tpk.tpk_barang_id = b.barang_id
    WHERE tpk_penerima_cabang != 0
      AND tpk.tpk_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    GROUP BY tpk_penerima_cabang
  ");

  while ($row = mysqli_fetch_assoc($q_transfer)) {
    $total_transfer_stok += $row['total_transfer'];
    $nama_cabang = '';
    if ($row['tpk_penerima_cabang'] == 1) {
      $nama_cabang = 'NUMART DUKUN';
    } elseif ($row['tpk_penerima_cabang'] == 2) {
      $nama_cabang = 'NUMART TREN PAKIS';
    } elseif ($row['tpk_penerima_cabang'] == 3) {
      $nama_cabang = 'NUMART PONDOK SRUMBUNG';
    } elseif ($row['tpk_penerima_cabang'] == 4) {
      $nama_cabang = 'BAQNU PCNU';
    } elseif ($row['tpk_penerima_cabang'] == 5) {
      $nama_cabang = 'NUMART TEGALREJO';
    } else {
      $nama_cabang = 'Cabang ' . $row['tpk_penerima_cabang'];
    }
    $transfer_detail[] = [
      'nama' => 'Transfer Stok ke ' . $nama_cabang,
      'nilai' => $row['total_transfer']
    ];
  }
}

/* ========================================================
   9. HITUNG PERSEDIAAN AKHIR
======================================================== */
if ($cabang == 0) {
  // CABANG 0: Persediaan Akhir = Pembelian - HPP - Transfer ke cabang lain
  $persediaan_akhir = $persediaan_awal - $hpp - $total_transfer_stok;
} else {
  // CABANG LAIN: Persediaan Akhir = Transfer dari Pusat - HPP
  $persediaan_akhir = $persediaan_awal - $hpp;
}

// Tambahkan persediaan akhir ke total aktiva (masuk ke Harta Lancar)
if ($persediaan_akhir > 0) {
  $total_aktiva += $persediaan_akhir;
  $total_harta_lancar += $persediaan_akhir;
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Laporan Laba Bersih Accural Basis</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- FILTER -->
      <div class="card card-default">
        <div class="card-header">
          <h3 class="card-title">Filter Data</h3>
        </div>
        <form method="POST">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <label>Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control" value="<?= $tanggal_awal ?>">
              </div>
              <div class="col-md-3">
                <label>Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control" value="<?= $tanggal_akhir ?>">
              </div>
              <div class="col-md-3">
                <label>Cabang</label>
                <select name="cabang" class="form-control" <?= $levelLogin == 'super admin' ? '' : 'disabled' ?>>
                  <?php foreach ($listCabang as $cab) : ?>
                    <option value="<?= $cab['toko_cabang'] ?>" <?= $cab['toko_cabang'] == $cabang ? 'selected' : '' ?>>
                      <?= $cab['toko_nama'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label>&nbsp;</label><br>
                <button class="btn btn-primary"><i class="fa fa-filter"></i> Tampilkan</button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- LAPORAN -->
      <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title">Periode <?= date('d M Y', strtotime($tanggal_awal)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?></h3>
          <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm" onclick="exportExcel()">
              <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button type="button" class="btn btn-danger btn-sm ml-1" onclick="exportPDF()">
              <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button type="button" class="btn btn-info btn-sm ml-1" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
        </div>
        </div>
        <div class="card-body" id="laporan-content">

          <!-- Persediaan Awal Barang -->
          <table class="table table-bordered">
            <thead>
              <tr>
                <th colspan="2" class="bg-secondary text-white">
                  <i class="fas fa-boxes"></i> PERSEDIAAN AWAL BARANG
                </th>
              </tr>
            </thead>
            <tbody>
              <tr class="table-warning">
                <td>
                  <b><?= $persediaan_label ?></b> 
                  (<?= date('d/m/Y', strtotime($tanggal_awal)) ?> - <?= date('d/m/Y', strtotime($tanggal_akhir)) ?>)
                </td>
                <td class="text-right"><b><?= rupiah($persediaan_awal) ?></b></td>
              </tr>
            </tbody>
          </table>

          <!-- Laporan Laba Rugi dalam format yang lebih rapi -->
          <table class="table table-bordered">
            <thead>
              <tr>
                <th colspan="2" class="bg-primary text-white">1. PENDAPATAN</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>a. Penjualan Cash</td>
                <td class="text-right"><?= rupiah($total_cash) ?></td>
              </tr>
              <tr>
                <td>b. Penjualan Kredit</td>
                <td class="text-right"><?= rupiah($total_kredit) ?></td>
              </tr>
              <tr>
                <td>c. Total Penjualan</td>
                <td class="text-right"><?= rupiah($total_penjualan) ?></td>
              </tr>
              <tr class="table-info">
                <td><b>Total Penjualan</b></td>
                <td class="text-right"><b><?= rupiah($total_penjualan) ?></b></td>
              </tr>
            </tbody>
          </table>



          <!-- HPP -->
          <table class="table table-bordered">
            <thead>
              <tr>
                <th colspan="2" class="bg-primary text-white"><?= $cabang == 0 && !empty($transfer_detail) ? '2. HPP' : '2. HPP' ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>a. Harga Pokok Penjualan</td>
                <td class="text-right"><?= rupiah($hpp) ?></td>
              </tr>
              <tr class="table-info">
                <td><b>Laba Kotor</b></td>
                <td class="text-right"><b><?= rupiah($laba_kotor) ?></b></td>
              </tr>
              <tr>
                <td>PRESENTASE</td>
                <td class="text-right"><?= $total_penjualan > 0 ? round(($laba_kotor / $total_penjualan) * 100, 2) : 0 ?>%</td>
              </tr>
            </tbody>
          </table>

          <!-- Pengeluaran -->
          <table class="table table-bordered">
            <thead>
              <tr>
                <th colspan="2" class="bg-primary text-white"><?= $cabang == 0 && !empty($transfer_detail) ? '3. BEBAN OPERASI' : '3. BEBAN OPERASI' ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
              $pengeluaran_counter = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'];
              $counter_index = 0;
              foreach ($pengeluaran as $p) : ?>
                <tr>
                  <td><?= $pengeluaran_counter[$counter_index] ?? '' ?>. <?= $p['kategori_nama'] ?></td>
                  <td class="text-right"><?= rupiah($p['total']) ?></td>
                </tr>
                <?php $counter_index++; ?>
              <?php endforeach; ?>
              <tr class="table-info">
                <td><b>Total Biaya Pengeluaran</b></td>
                <td class="text-right"><b><?= rupiah($total_pengeluaran) ?></b></td>
              </tr>
            </tbody>
          </table>

          <!-- Laba Bersih -->
          <table class="table table-bordered mt-3">
            <thead>
              <tr class="bg-success">
                <th colspan="2" class="text-white">
                  <?php
                  $section_number = 4;
                  if ($cabang == 0 && !empty($transfer_detail)) {
                    $section_number = 4;
                  }
                  echo $section_number . '. Laba Bersih';
                  ?>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Laba Kotor</td>
                <td class="text-right"><?= rupiah($laba_kotor) ?></td>
              </tr>
              <tr>
                <td>Total Biaya Pengeluaran</td>
                <td class="text-right"><?= rupiah($total_pengeluaran) ?></td>
              </tr>
              <tr>
                <td><b>Laba Bersih</b></td>
                <td class="text-right">
                  <?php
                  // Laba Bersih = Laba Kotor - Total Pengeluaran
                  $laba_bersih_section = $laba_kotor - $total_pengeluaran;
                  // Persentase Keuntungan = (Laba Bersih / HPP) * 100
                  $persentase_section = $hpp > 0 ? round(($laba_bersih_section / $hpp) * 100, 2) : 0;
                  ?>
                  <b class="<?= $laba_bersih_section >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= rupiah($laba_bersih_section) ?>
                  </b>
                </td>
              </tr>
              <?php if ($hpp > 0) : ?>
                <tr>
                  <td>Persentase Keuntungan</td>
                  <td class="text-right">
                    <span class="<?= $persentase_section >= 0 ? 'text-success' : 'text-danger' ?>">
                      <b><?= $persentase_section ?>%</b>
                    </span>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>

          <!-- Persediaan Akhir Barang (untuk cabang selain 0) -->
          <?php if ($cabang != 0) : ?>
          <table class="table table-bordered mt-3">
            <thead>
              <tr>
                <th colspan="2" class="bg-secondary text-white">
                  <i class="fas fa-boxes"></i> PERSEDIAAN AKHIR BARANG
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= $persediaan_label ?></td>
                <td class="text-right"><?= rupiah($persediaan_awal) ?></td>
              </tr>
              <tr>
                <td>HPP / Penjualan (<?= date('d/m/Y', strtotime($tanggal_awal)) ?> - <?= date('d/m/Y', strtotime($tanggal_akhir)) ?>)</td>
                <td class="text-right">(<?= rupiah($hpp) ?>)</td>
              </tr>
              <tr class="table-warning">
                <td><b>Persediaan Akhir Barang</b></td>
                <td class="text-right">
                  <b class="<?= $persediaan_akhir >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= rupiah($persediaan_akhir) ?>
                  </b>
                </td>
              </tr>
            </tbody>
          </table>
          <?php endif; ?>

          <!-- Pendapatan Lain (Bagi Hasil) -->
          <?php if ($cabang == 0 && !empty($pendapatan_lain_detail)) : ?>
            <table class="table table-bordered mt-3">
              <thead>
                <tr class="bg-primary">
                  <th colspan="2" class="text-white">
                    <?php
                    $section_number_pendapatan = 5;
                    if ($cabang == 0 && !empty($transfer_detail)) {
                      $section_number_pendapatan = 5;
                    }
                    echo $section_number_pendapatan . '. Pendapatan Lain';
                    ?>
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendapatan_lain_detail as $p) : ?>
                  <tr>
                    <td><?= $p['nama'] ?></td>
                    <td class="text-right"><?= rupiah($p['nilai']) ?></td>
                  </tr>
                <?php endforeach; ?>
                <tr class="table-info">
                  <td><b>Total Pendapatan Lain</b></td>
                  <td class="text-right"><b><?= rupiah($pendapatan_lain_bagi_hasil) ?></b></td>
                </tr>
              </tbody>
            </table>
          <?php endif; ?>

          <!-- Laba Rugi (Ringkasan) -->
          <?php
          $section_number_ringkasan = 6;
          if ($cabang == 0 && !empty($transfer_detail)) {
            $section_number_ringkasan = 6;
          }
          if ($cabang == 0 && !empty($pendapatan_lain_detail)) {
            $section_number_ringkasan = 6;
            if (!empty($transfer_detail)) {
              $section_number_ringkasan = 6;
            }
          }
          ?>
          <table class="table table-bordered mt-3">
            <thead>
              <tr>
                <th colspan="2" class="bg-primary text-white"><?= $section_number_ringkasan ?>. LABA RUGI (Ringkasan)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Total Pendapatan</td>
                <td class="text-right"><?= rupiah($total_pendapatan) ?></td>
              </tr>
              <tr>
                <td>Total HPP</td>
                <td class="text-right"><?= rupiah($hpp) ?></td>
              </tr>
              <tr>
                <td>Laba Kotor</td>
                <td class="text-right"><?= rupiah($laba_kotor) ?></td>
              </tr>
              <tr>
                <td>Beban Operasi</td>
                <td class="text-right"><?= rupiah($total_pengeluaran) ?></td>
              </tr>
              <tr class="table-success">
                <td><b>Laba Operasi</b></td>
                <td class="text-right">
                  <b class="<?= $laba_operasi >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= rupiah($laba_operasi) ?>
                  </b>
                </td>
              </tr>
              <tr>
                <td>Pendapatan Lain</td>
                <td class="text-right"><?= rupiah($pendapatan_lain_bagi_hasil) ?></td>
              </tr>
              <?php if ($beban_lain > 0) : ?>
                <tr>
                  <td>Beban Lain</td>
                  <td class="text-right"><?= rupiah($beban_lain) ?></td>
                </tr>
              <?php endif; ?>
              <tr class="table-success">
                <td><b>Laba Bersih</b></td>
                <td class="text-right">
                  <b class="<?= $laba_bersih >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= rupiah($laba_bersih) ?>
                  </b>
                </td>
              </tr>
            </tbody>
          </table>

          <!--Transfer Stock-->
          <?php if ($cabang == 0 && !empty($transfer_detail)) : ?>
            <table class="table table-bordered mt-3">
              <thead>
                <tr>
                  <th colspan="2" class="bg-primary text-white">
                    <?php
                    $section_number_transfer = 6;
                    if (!empty($pendapatan_lain_detail)) {
                      $section_number_transfer = 7;
                    }
                    echo $section_number_transfer . '. Total Transfer Stok (Dikirim oleh Cabang)';
                    ?>
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($transfer_detail as $t) : ?>
                  <tr>
                    <td><?= $t['nama'] ?></td>
                    <td class="text-right"><?= rupiah($t['nilai']) ?></td>
                  </tr>
                <?php endforeach; ?>
                <tr class="table-info">
                  <td><b>Total Transfer Stok</b></td>
                  <td class="text-right"><b><?= rupiah($total_transfer_stok) ?></b></td>
                </tr>
              </tbody>
            </table>
          
            <!-- Persediaan Akhir Barang (Cabang 0 - setelah transfer) -->
            <table class="table table-bordered mt-3">
              <thead>
                <tr>
                  <th colspan="2" class="bg-secondary text-white">
                    <i class="fas fa-boxes"></i> PERSEDIAAN AKHIR BARANG
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?= $persediaan_label ?></td>
                  <td class="text-right"><?= rupiah($persediaan_awal) ?></td>
                </tr>
                <tr>
                  <td>HPP / Penjualan</td>
                  <td class="text-right">(<?= rupiah($hpp) ?>)</td>
                </tr>
                <tr>
                  <td>Transfer Stok ke Cabang Lain</td>
                  <td class="text-right">(<?= rupiah($total_transfer_stok) ?>)</td>
                </tr>
                <tr class="table-warning">
                  <td><b>Persediaan Akhir Barang</b></td>
                  <td class="text-right">
                    <b class="<?= $persediaan_akhir >= 0 ? 'text-success' : 'text-danger' ?>">
                      <?= rupiah($persediaan_akhir) ?>
                    </b>
                  </td>
                </tr>
              </tbody>
            </table>
          <?php endif; ?>

        </div>
      </div>

      <!-- Laporan Neraca -->
      <div class="card card-success mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h3 class="card-title mb-0">Laporan Neraca</h3>
          <small>Periode <?= date('d M Y', strtotime($tanggal_awal)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?></small>
        </div>
          <div class="card-tools">
            <button type="button" class="btn btn-light btn-sm" onclick="exportNeracaExcel()">
              <i class="fas fa-file-excel text-success"></i> Excel
            </button>
            <button type="button" class="btn btn-light btn-sm ml-1" onclick="exportNeracaPDF()">
              <i class="fas fa-file-pdf text-danger"></i> PDF
            </button>
          </div>
        </div>
        <div class="card-body" id="neraca-content">

          <?php if ($jumlah_kategori_ditemukan == 0) : ?>
            <div class="alert alert-warning">
              <strong>Peringatan:</strong> Tidak ada kategori neraca yang ditemukan di database.<br>
              <small>
                Pastikan tabel <code>laba_kategori</code> memiliki data dengan field <code>kategori</code> berisi nilai: <strong>aktiva</strong>, <strong>pasiva</strong>, atau <strong>modal</strong>.
              </small>
            </div>
          <?php endif; ?>

          <div class="row">
            <!-- AKTIVA -->
            <div class="col-md-6">
              <table class="table table-bordered">
                <thead>
                  <tr class="bg-info">
                    <th colspan="3"><strong>AKTIVA (Harta)</strong></th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($aktiva_grouped)) : ?>
                    <?php foreach ($aktiva_grouped as $prefix => $group) : ?>
                      <?php if ($group['total'] != 0) : ?>
                        <tr>
                          <td colspan="3" class="bg-light"><strong><?= htmlspecialchars($prefix) ?></strong></td>
                        </tr>

                        <?php foreach ($group['items'] as $akt) : ?>
                          <?php if ($akt['saldo_akhir'] != 0) : ?>
                            <tr>
                              <td style="width: 20%;"><?= htmlspecialchars($akt['kode_akun']) ?></td>
                              <td style="padding-left: 20px;"><?= htmlspecialchars($akt['name']) ?></td>
                              <td style="width: 30%; text-align: right;"><?= rupiah($akt['saldo_akhir']) ?></td>
                            </tr>
                          <?php endif; ?>
                        <?php endforeach; ?>

                        <tr class="bg-light">
                          <td colspan="2" class="text-right"><strong>Total <?= htmlspecialchars($prefix) ?></strong></td>
                          <td class="text-right"><strong><?= rupiah($group['total']) ?></strong></td>
                        </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Total Harta Lancar -->
                    <?php if ($total_harta_lancar != 0) : ?>
                      <tr>
                        <td colspan="2"><strong>Total Harta Lancar</strong></td>
                        <td class="text-right"><strong><?= rupiah($total_harta_lancar) ?></strong></td>
                      </tr>
                    <?php endif; ?>

                    <!-- Total Harta Tetap -->
                    <?php if ($total_harta_tetap != 0) : ?>
                      <tr>
                        <td colspan="2"><strong>Total Harta Tetap</strong></td>
                        <td class="text-right"><strong><?= rupiah($total_harta_tetap) ?></strong></td>
                      </tr>
                    <?php endif; ?>

                  <?php elseif (!empty($neraca['aktiva'])) : ?>
                    <!-- Fallback jika tidak ada grouping - tampilkan semua data aktiva -->
                    <?php foreach ($neraca['aktiva'] as $akt) : ?>
                      <?php if ($akt['saldo_akhir'] != 0) : ?>
                        <tr>
                          <td style="width: 20%;"><?= htmlspecialchars($akt['kode_akun']) ?></td>
                          <td style="padding-left: 20px;"><?= htmlspecialchars($akt['name']) ?></td>
                          <td style="width: 30%; text-align: right;"><?= rupiah($akt['saldo_akhir']) ?></td>
                        </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted">
                        Tidak ada data aktiva
                      </td>
                    </tr>
                  <?php endif; ?>

                  <!-- Persediaan Barang Harian -->
                  <?php if ($persediaan_akhir > 0) : ?>
                    <tr>
                      <td style="width: 20%;">1-103</td>
                      <td style="padding-left: 20px;"><strong>Persediaan Barang</strong></td>
                      <td style="width: 30%; text-align: right;"><?= rupiah($persediaan_akhir) ?></td>
                    </tr>
                  <?php endif; ?>

                  <tr class="bg-info">
                    <td colspan="2"><strong>TOTAL HARTA</strong></td>
                    <td class="text-right"><strong><?= rupiah($total_aktiva) ?></strong></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- PASIVA & MODAL -->
            <div class="col-md-6">
              <table class="table table-bordered">
                <thead>
                  <tr class="bg-warning">
                    <th colspan="3"><strong>KEWAJIBAN DAN MODAL</strong></th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Pasiva -->
                  <?php if (!empty($pasiva_grouped) || !empty($neraca['pasiva'])) : ?>
                    <tr>
                      <td colspan="3" class="bg-light"><strong>Kewajiban</strong></td>
                    </tr>

                    <?php if (!empty($pasiva_grouped)) : ?>
                      <?php foreach ($pasiva_grouped as $prefix => $group) : ?>
                        <?php if ($group['total'] != 0) : ?>
                          <tr>
                            <td colspan="3" class="bg-light"><strong><?= htmlspecialchars($prefix) ?></strong></td>
                          </tr>

                          <?php foreach ($group['items'] as $pas) : ?>
                            <?php if ($pas['saldo_akhir'] != 0) : ?>
                              <tr>
                                <td style="width: 20%;"><?= htmlspecialchars($pas['kode_akun']) ?></td>
                                <td style="padding-left: 20px;"><?= htmlspecialchars($pas['name']) ?></td>
                                <td style="width: 30%; text-align: right;"><?= rupiah($pas['saldo_akhir']) ?></td>
                              </tr>
                            <?php endif; ?>
                          <?php endforeach; ?>

                          <tr class="bg-light">
                            <td colspan="2" class="text-right"><strong>Total <?= htmlspecialchars($prefix) ?></strong></td>
                            <td class="text-right"><strong><?= rupiah($group['total']) ?></strong></td>
                          </tr>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    <?php else : ?>
                      <?php foreach ($neraca['pasiva'] as $pas) : ?>
                        <?php if ($pas['saldo_akhir'] != 0) : ?>
                          <tr>
                            <td><?= htmlspecialchars($pas['kode_akun']) ?></td>
                            <td><?= htmlspecialchars($pas['name']) ?></td>
                            <td class="text-right"><?= rupiah($pas['saldo_akhir']) ?></td>
                          </tr>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    <?php endif; ?>

                    <tr>
                      <td colspan="2" class="text-right"><strong>Total Kewajiban</strong></td>
                      <td class="text-right"><strong><?= rupiah($total_pasiva) ?></strong></td>
                    </tr>
                  <?php else : ?>
                    <tr>
                      <td colspan="3" class="bg-light"><strong>Kewajiban</strong></td>
                    </tr>
                    <tr>
                      <td colspan="3" class="text-center text-muted">Tidak ada data kewajiban</td>
                    </tr>
                    <tr>
                      <td colspan="2" class="text-right"><strong>Total Kewajiban</strong></td>
                      <td class="text-right"><strong><?= rupiah($total_pasiva) ?></strong></td>
                    </tr>
                  <?php endif; ?>

                  <!-- Modal -->
                  <tr>
                    <td colspan="3" class="bg-light"><strong>Modal</strong></td>
                  </tr>

                  <!-- Laba Rugi dari laporan laba rugi -->
                  <tr>
                    <td></td>
                    <td>Laba Rugi</td>
                    <td class="text-right"><?= rupiah($laba_bersih) ?></td>
                  </tr>

                  <?php
                  // Inisialisasi total modal dengan laba rugi
                  $total_modal_dengan_laba = $total_modal + $laba_bersih;
                  ?>

                  <?php if (!empty($modal_grouped) || !empty($neraca['modal'])) : ?>
                    <?php if (!empty($modal_grouped)) : ?>
                      <?php foreach ($modal_grouped as $prefix => $group) : ?>
                        <?php if ($group['total'] != 0) : ?>
                          <tr>
                            <td colspan="3" class="bg-light"><strong><?= htmlspecialchars($prefix) ?></strong></td>
                          </tr>

                          <?php foreach ($group['items'] as $mod) : ?>
                            <?php if ($mod['saldo_akhir'] != 0) : ?>
                              <tr>
                                <td style="width: 20%;"><?= htmlspecialchars($mod['kode_akun']) ?></td>
                                <td style="padding-left: 20px;"><?= htmlspecialchars($mod['name']) ?></td>
                                <td style="width: 30%; text-align: right;"><?= rupiah($mod['saldo_akhir']) ?></td>
                              </tr>
                            <?php endif; ?>
                          <?php endforeach; ?>

                          <tr class="bg-light">
                            <td colspan="2" class="text-right"><strong>Total <?= htmlspecialchars($prefix) ?></strong></td>
                            <td class="text-right"><strong><?= rupiah($group['total']) ?></strong></td>
                          </tr>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    <?php else : ?>
                      <?php foreach ($neraca['modal'] as $mod) : ?>
                        <?php if ($mod['saldo_akhir'] != 0) : ?>
                          <tr>
                            <td><?= htmlspecialchars($mod['kode_akun']) ?></td>
                            <td><?= htmlspecialchars($mod['name']) ?></td>
                            <td class="text-right"><?= rupiah($mod['saldo_akhir']) ?></td>
                          </tr>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    <?php endif; ?>

                    <tr>
                      <td colspan="2" class="text-right"><strong>Total Modal</strong></td>
                      <td class="text-right"><strong><?= rupiah($total_modal_dengan_laba) ?></strong></td>
                    </tr>
                  <?php else : ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted">Tidak ada data modal</td>
                    </tr>
                    <tr>
                      <td colspan="2" class="text-right"><strong>Total Modal</strong></td>
                      <td class="text-right"><strong><?= rupiah($total_modal_dengan_laba) ?></strong></td>
                    </tr>
                  <?php endif; ?>

                  <?php
                  // Update total pasiva & modal dengan laba rugi
                  $total_pasiva_modal_dengan_laba = $total_pasiva + $total_modal_dengan_laba;
                  ?>
                  <tr class="bg-warning">
                    <td colspan="2"><strong>TOTAL KEWAJIBAN DAN MODAL</strong></td>
                    <td class="text-right"><strong><?= rupiah($total_pasiva_modal_dengan_laba) ?></strong></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Catatan Keseimbangan -->
          <div class="alert alert-info mt-3">
            <strong>Keseimbangan Neraca:</strong><br>
            Total Harta: <strong><?= rupiah($total_aktiva) ?></strong><br>
            Total Kewajiban dan Modal: <strong><?= rupiah($total_pasiva_modal_dengan_laba) ?></strong><br>
            <?php
            $selisih = abs($total_aktiva - $total_pasiva_modal_dengan_laba);
            if ($selisih < 0.01) : ?>
              <span class="text-success"><strong>✓ Neraca Seimbang</strong></span>
            <?php else : ?>
              <span class="text-danger">
                <strong>⚠ Selisih: <?= rupiah($selisih) ?></strong>
              </span>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </section>
</div>

<style>
@media print {
  .content-header, .card-default, .card-tools, .main-sidebar, .main-header, .main-footer, .breadcrumb, .no-print {
    display: none !important;
  }
  .content-wrapper {
    margin-left: 0 !important;
    padding: 0 !important;
  }
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  body {
    font-size: 12px;
  }
  .table {
    font-size: 11px;
  }
}
</style>

<script>
// Export to Excel using table2excel approach
function exportExcel() {
  try {
    const toko = '<?= addslashes($toko['toko_nama'] ?? 'Laporan') ?>';
    const periode = '<?= date('d-m-Y', strtotime($tanggal_awal)) ?>_sd_<?= date('d-m-Y', strtotime($tanggal_akhir)) ?>';
    const filename = 'Laporan_Laba_Rugi_' + toko.replace(/\s+/g, '_') + '_' + periode;
    
    // Build HTML table for export
    let html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
    html += '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
    html += '<x:Name>Laba Rugi</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
    html += '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>';
    
    // Header
    html += '<table border="1">';
    html += '<tr><td colspan="2" style="font-size:16pt;font-weight:bold;text-align:center;">LAPORAN LABA RUGI</td></tr>';
    html += '<tr><td colspan="2" style="font-size:14pt;text-align:center;">' + toko + '</td></tr>';
    html += '<tr><td colspan="2" style="text-align:center;">Periode: <?= date('d M Y', strtotime($tanggal_awal)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?></td></tr>';
    html += '<tr><td colspan="2"></td></tr>';
    
    // Get all tables
    const tables = document.querySelectorAll('#laporan-content table');
    tables.forEach(table => {
      const rows = table.querySelectorAll('tr');
      rows.forEach(row => {
        html += '<tr>';
        const cells = row.querySelectorAll('th, td');
        cells.forEach(cell => {
          const colspan = cell.getAttribute('colspan') || 1;
          const text = cell.innerText.trim().replace(/\n/g, ' ');
          const isBold = cell.querySelector('b, strong') || cell.tagName === 'TH';
          const style = isBold ? 'font-weight:bold;' : '';
          html += '<td colspan="' + colspan + '" style="' + style + '">' + text + '</td>';
        });
        html += '</tr>';
      });
      html += '<tr><td colspan="2"></td></tr>';
    });
    
    html += '</table></body></html>';
    
    // Download
    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
      icon: 'success',
      title: 'Export Berhasil',
      text: 'File Excel telah didownload',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (err) {
    console.error('Excel Error:', err);
    Swal.fire({
      icon: 'error',
      title: 'Gagal Export',
      text: 'Terjadi kesalahan: ' + err.message
    });
  }
}

// Export Neraca to Excel
function exportNeracaExcel() {
  try {
    const toko = '<?= addslashes($toko['toko_nama'] ?? 'Laporan') ?>';
    const periode = '<?= date('d-m-Y', strtotime($tanggal_awal)) ?>_sd_<?= date('d-m-Y', strtotime($tanggal_akhir)) ?>';
    const filename = 'Neraca_' + toko.replace(/\s+/g, '_') + '_' + periode;
    
    let html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
    html += '<head><meta charset="UTF-8"></head><body>';
    html += '<table border="1">';
    html += '<tr><td colspan="3" style="font-size:16pt;font-weight:bold;text-align:center;">LAPORAN NERACA</td></tr>';
    html += '<tr><td colspan="3" style="font-size:14pt;text-align:center;">' + toko + '</td></tr>';
    html += '<tr><td colspan="3" style="text-align:center;">Periode: <?= date('d M Y', strtotime($tanggal_awal)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?></td></tr>';
    html += '<tr><td colspan="3"></td></tr>';
    
    const tables = document.querySelectorAll('#neraca-content table');
    tables.forEach(table => {
      const rows = table.querySelectorAll('tr');
      rows.forEach(row => {
        html += '<tr>';
        const cells = row.querySelectorAll('th, td');
        cells.forEach(cell => {
          const colspan = cell.getAttribute('colspan') || 1;
          const text = cell.innerText.trim().replace(/\n/g, ' ');
          const isBold = cell.querySelector('b, strong') || cell.tagName === 'TH';
          const style = isBold ? 'font-weight:bold;' : '';
          html += '<td colspan="' + colspan + '" style="' + style + '">' + text + '</td>';
        });
        html += '</tr>';
      });
      html += '<tr><td colspan="3"></td></tr>';
    });
    
    html += '</table></body></html>';
    
    const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
      icon: 'success',
      title: 'Export Berhasil',
      text: 'File Excel Neraca telah didownload',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (err) {
    console.error('Excel Error:', err);
    Swal.fire({ icon: 'error', title: 'Gagal Export', text: err.message });
  }
}

// Export to PDF - Open print dialog
function exportPDF() {
  // Create printable version
  const toko = '<?= addslashes($toko['toko_nama'] ?? 'Laporan') ?>';
  const content = document.getElementById('laporan-content').innerHTML;
  
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Laporan Laba Rugi - ${toko}</title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
      <style>
        body { font-size: 12px; padding: 20px; }
        .table { font-size: 11px; }
        .table-warning { background-color: #fff3cd !important; }
        .table-info { background-color: #d1ecf1 !important; }
        .table-success { background-color: #d4edda !important; }
        .bg-secondary { background-color: #6c757d !important; color: white; }
        .bg-primary { background-color: #007bff !important; color: white; }
        .bg-success { background-color: #28a745 !important; color: white; }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        h2 { margin-bottom: 5px; }
        @media print {
          .table-warning, .table-info, .table-success, .bg-secondary, .bg-primary, .bg-success {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }
        }
      </style>
    </head>
    <body>
      <div class="text-center mb-3">
        <h2>LAPORAN LABA RUGI</h2>
        <h4>${toko}</h4>
        <p>Periode: <?= date('d M Y', strtotime($tanggal_awal)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?></p>
      </div>
      ${content}
      <script>
        window.onload = function() {
          window.print();
          setTimeout(function() { window.close(); }, 500);
        };
      <\/script>
    </body>
    </html>
  `);
  printWindow.document.close();
}

// Export Neraca to PDF
function exportNeracaPDF() {
  const toko = '<?= addslashes($toko['toko_nama'] ?? 'Laporan') ?>';
  const content = document.getElementById('neraca-content').innerHTML;
  
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Laporan Neraca - ${toko}</title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
      <style>
        body { font-size: 11px; padding: 15px; }
        .table { font-size: 10px; }
        .bg-info { background-color: #17a2b8 !important; color: white; }
        .bg-warning { background-color: #ffc107 !important; }
        .bg-light { background-color: #f8f9fa !important; }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        @media print {
          .bg-info, .bg-warning, .bg-light {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
          }
        }
      </style>
    </head>
    <body>
      <div class="text-center mb-3">
        <h3>LAPORAN NERACA</h3>
        <h5>${toko}</h5>
        <p>Periode: <?= date('d M Y', strtotime($tanggal_awal)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?></p>
      </div>
      ${content}
      <script>
        window.onload = function() {
          window.print();
          setTimeout(function() { window.close(); }, 500);
        };
      <\/script>
    </body>
    </html>
  `);
  printWindow.document.close();
}
</script>

<?php include '_footerlaporan.php' ?>
<?php include '_footer.php'; ?>