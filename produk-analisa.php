<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';
error_reporting(0);

if ($levelLogin === "kurir" || $levelLogin === "kasir") {
  echo "<script>document.location.href = 'bo';</script>";
}

// Filters
$filterPeriode = isset($_GET['periode']) ? $_GET['periode'] : 'bulan';
$goal = isset($_GET['goal']) ? $_GET['goal'] : 'balanced'; // balanced | omzet | margin | stok
$kategoriId = isset($_GET['kategori_id']) ? intval($_GET['kategori_id']) : 0;
$supplier = isset($_GET['supplier']) ? trim($_GET['supplier']) : '';
$minMargin = isset($_GET['min_margin']) ? floatval($_GET['min_margin']) : 0;
$minQty = isset($_GET['min_qty']) ? floatval($_GET['min_qty']) : 0;

// Date calculations
$today = date('Y-m-d');
$startOfMonth = date('Y-m-01');
$endOfMonth = date('Y-m-t');
$startOfYear = date('Y-01-01');

switch ($filterPeriode) {
  case 'hari':
    $startDate = $today;
    $endDate = $today;
    $periodLabel = 'Hari Ini';
    break;
  case 'minggu':
    $startDate = date('Y-m-d', strtotime('monday this week'));
    $endDate = date('Y-m-d', strtotime('sunday this week'));
    $periodLabel = 'Minggu Ini';
    break;
  case 'bulan':
    $startDate = $startOfMonth;
    $endDate = $endOfMonth;
    $periodLabel = 'Bulan ' . date('F Y');
    break;
  case 'tahun':
    $startDate = $startOfYear;
    $endDate = date('Y-12-31');
    $periodLabel = 'Tahun ' . date('Y');
    break;
  case 'custom':
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : $startOfMonth;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : $today;
    $periodLabel = date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
    break;
  default:
    $startDate = $startOfMonth;
    $endDate = $endOfMonth;
    $periodLabel = 'Bulan ' . date('F Y');
}

$daysRange = max(1, (int)((strtotime($endDate) - strtotime($startDate)) / 86400) + 1);

// Dropdown data
$kategoriList = query("SELECT kategori_id, kategori_nama FROM kategori WHERE kategori_status = '1' AND kategori_cabang = $sessionCabang ORDER BY kategori_nama");

// quick summary (small query, no heavy group by)
$summary = query("
  SELECT
    COUNT(DISTINCT p.barang_id) AS total_produk_terjual,
    COALESCE(SUM(p.barang_qty * p.keranjang_harga), 0) AS omzet,
    COALESCE(SUM(p.barang_qty_keranjang * p.keranjang_harga_beli), 0) AS hpp
  FROM penjualan p
  WHERE p.penjualan_cabang = $sessionCabang
    AND p.penjualan_date BETWEEN '$startDate' AND '$endDate'
");
$totalProdukTerjual = intval($summary[0]['total_produk_terjual'] ?? 0);
$omzet = floatval($summary[0]['omzet'] ?? 0);
$hpp = floatval($summary[0]['hpp'] ?? 0);
$laba = $omzet - $hpp;
$margin = $omzet > 0 ? ($laba / $omzet) * 100 : 0;

?>

<style>
  .filter-card {
    background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);
    border-radius: 15px;
  }

  .period-btn {
    border-radius: 20px;
    padding: 8px 20px;
    margin: 2px;
  }

  .period-btn.active {
    background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
    border-color: transparent;
    color: white;
  }

  .kpi-card {
    border-radius: 14px;
    overflow: hidden;
    border: none;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
  }

  .kpi-card .kpi-header {
    background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
    color: #fff;
    padding: 14px 16px;
    font-weight: 600;
  }

  .kpi-card .kpi-body {
    padding: 16px;
    background: #fff;
  }

  .kpi-value {
    font-size: 1.4rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.25rem;
  }

  .kpi-sub {
    color: #64748b;
    font-size: 0.9rem;
  }

  .promo-hint {
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
  }

  .promo-hint b {
    color: #fff;
  }

  .badge-score {
    background: #0ea5e9;
    color: #fff;
  }

  .badge-score-strong { background: #22c55e; color: #fff; }
  .badge-score-mid { background: #f59e0b; color: #111827; }
  .badge-score-low { background: #ef4444; color: #fff; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-7">
          <h1><i class="fas fa-bullhorn"></i> Analisa Produk untuk Iklan & Promo</h1>
        </div>
        <div class="col-sm-5">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="bo">Home</a></li>
            <li class="breadcrumb-item"><a href="laporan-produk">Laporan Produk</a></li>
            <li class="breadcrumb-item active">Analisa Promo</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card filter-card mb-4">
        <div class="card-body">
          <form method="GET" action="" class="row align-items-end">
            <div class="col-md-12 mb-3">
              <label class="font-weight-bold">Filter Periode:</label>
              <div class="btn-group flex-wrap" role="group">
                <a href="?periode=hari&goal=<?= urlencode($goal) ?>" class="btn btn-outline-primary period-btn <?= $filterPeriode == 'hari' ? 'active' : '' ?>">Hari Ini</a>
                <a href="?periode=minggu&goal=<?= urlencode($goal) ?>" class="btn btn-outline-primary period-btn <?= $filterPeriode == 'minggu' ? 'active' : '' ?>">Minggu Ini</a>
                <a href="?periode=bulan&goal=<?= urlencode($goal) ?>" class="btn btn-outline-primary period-btn <?= $filterPeriode == 'bulan' ? 'active' : '' ?>">Bulan Ini</a>
                <a href="?periode=tahun&goal=<?= urlencode($goal) ?>" class="btn btn-outline-primary period-btn <?= $filterPeriode == 'tahun' ? 'active' : '' ?>">Tahun Ini</a>
              </div>
            </div>

            <div class="col-md-3">
              <label>Dari Tanggal:</label>
              <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
            </div>
            <div class="col-md-3">
              <label>Sampai Tanggal:</label>
              <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
            </div>
            <div class="col-md-3">
              <label>Tujuan Promo:</label>
              <select name="goal" class="form-control">
                <option value="balanced" <?= $goal == 'balanced' ? 'selected' : '' ?>>Seimbang (omzet + margin + stok)</option>
                <option value="omzet" <?= $goal == 'omzet' ? 'selected' : '' ?>>Naikkan Omzet</option>
                <option value="margin" <?= $goal == 'margin' ? 'selected' : '' ?>>Naikkan Margin</option>
                <option value="stok" <?= $goal == 'stok' ? 'selected' : '' ?>>Habiskan Stok (clearance)</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Kategori:</label>
              <select name="kategori_id" class="form-control select2bs4">
                <option value="0">-- Semua Kategori --</option>
                <?php foreach ($kategoriList as $k) : ?>
                  <option value="<?= $k['kategori_id'] ?>" <?= $kategoriId == $k['kategori_id'] ? 'selected' : '' ?>>
                    <?= $k['kategori_nama'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3 mt-3">
              <label>Kode Supplier (opsional):</label>
              <input type="text" name="supplier" class="form-control" value="<?= htmlspecialchars($supplier) ?>" placeholder="mis. SUP-001">
            </div>
            <div class="col-md-3 mt-3">
              <label>Min Margin %:</label>
              <input type="number" step="0.1" name="min_margin" class="form-control" value="<?= $minMargin ?>" placeholder="0">
            </div>
            <div class="col-md-3 mt-3">
              <label>Min Terjual (PCS):</label>
              <input type="number" step="1" name="min_qty" class="form-control" value="<?= $minQty ?>" placeholder="0">
            </div>
            <div class="col-md-3 mt-3">
              <label>Aksi</label>
              <div class="input-group">
                <input type="hidden" name="periode" value="custom">
                <div class="input-group-append w-100">
                  <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Terapkan</button>
                </div>
              </div>
            </div>
          </form>

          <div class="mt-3 d-flex flex-wrap" style="gap:8px;">
            <span class="badge badge-primary" style="font-size: 1rem;"><i class="fas fa-calendar"></i> <?= $periodLabel ?></span>
            <span class="badge badge-info" style="font-size: 1rem;"><i class="fas fa-clock"></i> <?= $daysRange ?> hari</span>
            <span class="badge badge-secondary" style="font-size: 1rem;"><i class="fas fa-store"></i> Cabang: <?= $sessionCabang ?></span>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <div class="kpi-card mb-3">
            <div class="kpi-header"><i class="fas fa-box"></i> Produk Terjual</div>
            <div class="kpi-body">
              <div class="kpi-value"><?= number_format($totalProdukTerjual, 0, ',', '.') ?></div>
              <div class="kpi-sub">Jumlah SKU yang ada transaksi</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="kpi-card mb-3">
            <div class="kpi-header"><i class="fas fa-coins"></i> Omzet</div>
            <div class="kpi-body">
              <div class="kpi-value">Rp <?= number_format($omzet, 0, ',', '.') ?></div>
              <div class="kpi-sub">Total penjualan (range dipilih)</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="kpi-card mb-3">
            <div class="kpi-header"><i class="fas fa-chart-line"></i> Laba Kotor</div>
            <div class="kpi-body">
              <div class="kpi-value">Rp <?= number_format($laba, 0, ',', '.') ?></div>
              <div class="kpi-sub">Omzet - HPP</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="kpi-card mb-3">
            <div class="kpi-header"><i class="fas fa-percent"></i> Margin</div>
            <div class="kpi-body">
              <div class="kpi-value"><?= number_format($margin, 2, ',', '.') ?>%</div>
              <div class="kpi-sub">Margin rata-rata (kotor)</div>
            </div>
          </div>
        </div>
      </div>

      <div class="promo-hint mb-4">
        <div class="d-flex flex-wrap justify-content-between" style="gap:10px;">
          <div>
            <b>Rekomendasi Iklan/Promo:</b> urutkan berdasarkan <b>Promo Score</b>. Gunakan “Tujuan Promo” untuk fokus (omzet, margin, atau habiskan stok).
          </div>
          <div>
            <button class="btn btn-success btn-sm" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-table"></i> Rekomendasi Produk untuk Promo</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="produkAnalisaTable" class="table table-hover table-bordered" style="width:100%;">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Produk</th>
                  <th>Kategori</th>
                  <th>Supplier</th>
                  <th>Terjual (PCS)</th>
                  <th>Omzet</th>
                  <th>HPP</th>
                  <th>Laba</th>
                  <th>Margin %</th>
                  <th>Stok</th>
                  <th>Velocity (PCS/Hari)</th>
                  <th>Days of Stock</th>
                  <th>Promo Score</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include '_footer.php'; ?>

<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>

<script>
  $(function() {
    $('.select2bs4').select2({ theme: 'bootstrap4' });

    const ajaxUrl = `produk-analisa-data.php?cabang=<?= (int)$sessionCabang ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&goal=<?= urlencode($goal) ?>&kategori_id=<?= (int)$kategoriId ?>&supplier=<?= urlencode($supplier) ?>&min_margin=<?= urlencode((string)$minMargin) ?>&min_qty=<?= urlencode((string)$minQty) ?>`;

    const table = $('#produkAnalisaTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: ajaxUrl,
      pageLength: 25,
      order: [[12, "desc"]],
      columnDefs: [
        { targets: 0, orderable: false, searchable: false }
      ]
    });

    table.on('draw.dt', function() {
      var info = table.page.info();
      table.column(0, { page: 'applied' }).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1 + info.start;
      });
    });
  });

  function exportToExcel() {
    let table = document.getElementById('produkAnalisaTable');
    let html = table.outerHTML;
    let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    let downloadLink = document.createElement('a');
    downloadLink.href = url;
    downloadLink.download = 'produk_analisa_promo_<?= date('Y-m-d') ?>.xls';
    downloadLink.click();
  }
</script>

