<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';
error_reporting(0);

if ($levelLogin === "kurir") {
    echo "<script>document.location.href = 'bo';</script>";
}

// Date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$filterProvinsi = isset($_GET['provinsi']) ? $_GET['provinsi'] : '';
$filterKabupaten = isset($_GET['kabupaten']) ? $_GET['kabupaten'] : '';

// Build where conditions
$whereArea = "";
if (!empty($filterProvinsi)) {
    $filterProvinsi = mysqli_real_escape_string($conn, $filterProvinsi);
    $whereArea .= " AND c.alamat_provinsi = '$filterProvinsi'";
}
if (!empty($filterKabupaten)) {
    $filterKabupaten = mysqli_real_escape_string($conn, $filterKabupaten);
    $whereArea .= " AND c.alamat_kabupaten = '$filterKabupaten'";
}

// Get statistics by province
$provinsiStats = query("SELECT 
                            c.alamat_provinsi,
                            COUNT(DISTINCT c.customer_id) as total_customer,
                            COALESCE(SUM(i.invoice_sub_total), 0) as total_revenue,
                            COUNT(DISTINCT i.invoice_id) as total_transaksi
                        FROM customer c
                        LEFT JOIN invoice i ON c.customer_id = i.invoice_customer 
                            AND i.invoice_date BETWEEN '$startDate' AND '$endDate'
                            AND i.invoice_cabang = $sessionCabang
                        WHERE c.customer_cabang = $sessionCabang 
                            AND c.customer_id > 1 
                            AND c.alamat_provinsi IS NOT NULL 
                            AND c.alamat_provinsi != ''
                        GROUP BY c.alamat_provinsi
                        ORDER BY total_revenue DESC");

// Get statistics by kabupaten
$kabupatenStats = query("SELECT 
                            c.alamat_provinsi,
                            c.alamat_kabupaten,
                            COUNT(DISTINCT c.customer_id) as total_customer,
                            COALESCE(SUM(i.invoice_sub_total), 0) as total_revenue,
                            COUNT(DISTINCT i.invoice_id) as total_transaksi
                         FROM customer c
                         LEFT JOIN invoice i ON c.customer_id = i.invoice_customer 
                            AND i.invoice_date BETWEEN '$startDate' AND '$endDate'
                            AND i.invoice_cabang = $sessionCabang
                         WHERE c.customer_cabang = $sessionCabang 
                            AND c.customer_id > 1 
                            AND c.alamat_kabupaten IS NOT NULL 
                            AND c.alamat_kabupaten != ''
                            $whereArea
                         GROUP BY c.alamat_kabupaten
                         ORDER BY total_revenue DESC");

// Get statistics by kecamatan (if kabupaten selected)
$kecamatanStats = [];
if (!empty($filterKabupaten)) {
    $kecamatanStats = query("SELECT 
                                c.alamat_kecamatan,
                                COUNT(DISTINCT c.customer_id) as total_customer,
                                COALESCE(SUM(i.invoice_sub_total), 0) as total_revenue,
                                COUNT(DISTINCT i.invoice_id) as total_transaksi
                             FROM customer c
                             LEFT JOIN invoice i ON c.customer_id = i.invoice_customer 
                                AND i.invoice_date BETWEEN '$startDate' AND '$endDate'
                                AND i.invoice_cabang = $sessionCabang
                             WHERE c.customer_cabang = $sessionCabang 
                                AND c.customer_id > 1 
                                AND c.alamat_kecamatan IS NOT NULL 
                                AND c.alamat_kecamatan != ''
                                AND c.alamat_kabupaten = '$filterKabupaten'
                             GROUP BY c.alamat_kecamatan
                             ORDER BY total_revenue DESC");
}

// Get customer list for selected area
$customerList = [];
if (!empty($filterKabupaten) || !empty($filterProvinsi)) {
    $customerList = query("SELECT 
                              c.*,
                              COALESCE(SUM(i.invoice_sub_total), 0) as total_belanja,
                              COUNT(DISTINCT i.invoice_id) as total_transaksi
                           FROM customer c
                           LEFT JOIN invoice i ON c.customer_id = i.invoice_customer 
                              AND i.invoice_date BETWEEN '$startDate' AND '$endDate'
                              AND i.invoice_cabang = $sessionCabang
                           WHERE c.customer_cabang = $sessionCabang 
                              AND c.customer_id > 1 
                              $whereArea
                           GROUP BY c.customer_id
                           ORDER BY total_belanja DESC");
}

// Get unique provinces for filter
$provinces = query("SELECT DISTINCT alamat_provinsi FROM customer 
                    WHERE customer_cabang = $sessionCabang 
                    AND alamat_provinsi IS NOT NULL 
                    AND alamat_provinsi != '' 
                    ORDER BY alamat_provinsi");

// Total stats
$totalWithArea = array_sum(array_column($provinsiStats, 'total_customer'));
$totalRevenue = array_sum(array_column($provinsiStats, 'total_revenue'));
$totalCustomers = query("SELECT COUNT(*) as total FROM customer WHERE customer_cabang = $sessionCabang AND customer_id > 1")[0]['total'];
$customersNoArea = $totalCustomers - $totalWithArea;
?>

<style>
    .area-card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .area-card:hover {
        transform: translateY(-3px);
    }
    .area-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border-radius: 15px 15px 0 0;
    }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
    }
    .stat-card.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .stat-card.orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-card.yellow {
        background: linear-gradient(135deg, #f12711 0%, #f5af19 100%);
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
    }
    .area-table th {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    .progress-area {
        height: 10px;
        border-radius: 5px;
    }
    .area-item {
        padding: 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background 0.2s;
    }
    .area-item:hover {
        background: #f8f9fa;
    }
    .area-item.active {
        background: #e3f2fd;
        border-left: 4px solid #2196F3;
    }
    .map-placeholder {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 15px;
        padding: 50px;
        text-align: center;
    }
    .breadcrumb-area {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .breadcrumb-area a {
        color: #667eea;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-map-marked-alt"></i> Area Tracking Customer</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="bo">Home</a></li>
                        <li class="breadcrumb-item"><a href="customer-management">Customer Management</a></li>
                        <li class="breadcrumb-item active">Area Tracking</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter -->
            <div class="card area-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-3">
                            <label>Periode:</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Sampai:</label>
                            <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Provinsi:</label>
                            <select name="provinsi" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Semua Provinsi --</option>
                                <?php foreach ($provinces as $prov) : ?>
                                <option value="<?= $prov['alamat_provinsi'] ?>" <?= $filterProvinsi == $prov['alamat_provinsi'] ? 'selected' : '' ?>>
                                    <?= $prov['alamat_provinsi'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-value"><?= count($provinsiStats) ?></div>
                        <div>Provinsi</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card green">
                        <div class="stat-value"><?= count($kabupatenStats) ?></div>
                        <div>Kabupaten/Kota</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card orange">
                        <div class="stat-value"><?= number_format($totalWithArea) ?></div>
                        <div>Customer (Ada Alamat)</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-card yellow">
                        <div class="stat-value"><?= number_format($customersNoArea) ?></div>
                        <div>Customer (Tanpa Alamat)</div>
                    </div>
                </div>
            </div>

            <!-- Area Breadcrumb -->
            <?php if (!empty($filterProvinsi) || !empty($filterKabupaten)) : ?>
            <div class="breadcrumb-area">
                <a href="customer-area-tracking?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">
                    <i class="fas fa-globe-asia"></i> Semua
                </a>
                <?php if (!empty($filterProvinsi)) : ?>
                    <i class="fas fa-chevron-right mx-2"></i>
                    <a href="customer-area-tracking?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&provinsi=<?= urlencode($filterProvinsi) ?>">
                        <?= $filterProvinsi ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($filterKabupaten)) : ?>
                    <i class="fas fa-chevron-right mx-2"></i>
                    <span><?= $filterKabupaten ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Province/Kabupaten Stats -->
                <div class="col-lg-<?= !empty($filterKabupaten) ? '4' : '6' ?> mb-4">
                    <div class="card area-card">
                        <div class="area-header card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-map"></i> 
                                <?= empty($filterProvinsi) ? 'Per Provinsi' : 'Per Kabupaten/Kota' ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php 
                            $statsToShow = empty($filterProvinsi) ? $provinsiStats : $kabupatenStats;
                            $maxRevenue = !empty($statsToShow) ? max(array_column($statsToShow, 'total_revenue')) : 1;
                            ?>
                            <?php foreach ($statsToShow as $stat) : 
                                $areaName = empty($filterProvinsi) ? $stat['alamat_provinsi'] : $stat['alamat_kabupaten'];
                                $percentage = $maxRevenue > 0 ? ($stat['total_revenue'] / $maxRevenue) * 100 : 0;
                                
                                // Build URL
                                if (empty($filterProvinsi)) {
                                    $url = "?start_date=$startDate&end_date=$endDate&provinsi=" . urlencode($stat['alamat_provinsi']);
                                } else {
                                    $url = "?start_date=$startDate&end_date=$endDate&provinsi=" . urlencode($filterProvinsi) . "&kabupaten=" . urlencode($stat['alamat_kabupaten']);
                                }
                                
                                $isActive = ($filterKabupaten == $stat['alamat_kabupaten']);
                            ?>
                            <a href="<?= $url ?>" class="text-decoration-none">
                                <div class="area-item <?= $isActive ? 'active' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="text-dark"><?= $areaName ?: 'Tidak diketahui' ?></strong>
                                        <span class="badge badge-primary"><?= $stat['total_customer'] ?> customer</span>
                                    </div>
                                    <div class="progress progress-area mb-2">
                                        <div class="progress-bar" style="width: <?= $percentage ?>%; background: linear-gradient(90deg, #4facfe, #00f2fe);"></div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-money-bill"></i> Rp <?= number_format($stat['total_revenue'], 0, ',', '.') ?>
                                        | <i class="fas fa-shopping-cart"></i> <?= $stat['total_transaksi'] ?> transaksi
                                    </small>
                                </div>
                            </a>
                            <?php endforeach; ?>
                            
                            <?php if (empty($statsToShow)) : ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-map fa-3x mb-3"></i>
                                <p>Belum ada data area</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Kecamatan Stats (if kabupaten selected) -->
                <?php if (!empty($filterKabupaten)) : ?>
                <div class="col-lg-4 mb-4">
                    <div class="card area-card">
                        <div class="area-header card-header">
                            <h5 class="mb-0"><i class="fas fa-map-pin"></i> Per Kecamatan</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php 
                            $maxKecRevenue = !empty($kecamatanStats) ? max(array_column($kecamatanStats, 'total_revenue')) : 1;
                            foreach ($kecamatanStats as $kec) : 
                                $kecPercentage = $maxKecRevenue > 0 ? ($kec['total_revenue'] / $maxKecRevenue) * 100 : 0;
                            ?>
                            <div class="area-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong><?= $kec['alamat_kecamatan'] ?: 'Tidak diketahui' ?></strong>
                                    <span class="badge badge-info"><?= $kec['total_customer'] ?></span>
                                </div>
                                <div class="progress progress-area mb-2">
                                    <div class="progress-bar bg-success" style="width: <?= $kecPercentage ?>%;"></div>
                                </div>
                                <small class="text-muted">Rp <?= number_format($kec['total_revenue'], 0, ',', '.') ?></small>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($kecamatanStats)) : ?>
                            <div class="text-center text-muted py-4">
                                <p>Tidak ada data kecamatan</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Customer List or Chart -->
                <div class="col-lg-<?= !empty($filterKabupaten) ? '4' : '6' ?> mb-4">
                    <?php if (!empty($customerList)) : ?>
                    <div class="card area-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Customer di Area Ini</h5>
                        </div>
                        <div class="card-body p-0">
                            <div style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Customer</th>
                                            <th>Belanja</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customerList as $cust) : ?>
                                        <tr>
                                            <td>
                                                <strong><?= $cust['customer_nama'] ?></strong><br>
                                                <small class="text-muted">
                                                    <?= $cust['alamat_kecamatan'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                Rp <?= number_format($cust['total_belanja'], 0, ',', '.') ?><br>
                                                <small class="text-muted"><?= $cust['total_transaksi'] ?>x</small>
                                            </td>
                                            <td>
                                                <a href="customer-zoom?id=<?= $cust['customer_id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="card area-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Distribusi Revenue</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($kabupatenStats)) : ?>
                            <canvas id="areaChart" style="height: 300px;"></canvas>
                            <?php else : ?>
                            <div class="map-placeholder">
                                <i class="fas fa-map fa-4x text-muted mb-3"></i>
                                <h5>Pilih area untuk melihat detail</h5>
                                <p class="text-muted">Klik pada provinsi atau kabupaten di sebelah kiri</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card area-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-table"></i> Data Lengkap Per Area</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="areaTable" class="table table-hover area-table mb-0">
                            <thead>
                                <tr>
                                    <th>Provinsi</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Jumlah Customer</th>
                                    <th>Total Transaksi</th>
                                    <th>Total Revenue</th>
                                    <th>Rata-rata/Customer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kabupatenStats as $kab) : 
                                    $avgPerCustomer = $kab['total_customer'] > 0 ? $kab['total_revenue'] / $kab['total_customer'] : 0;
                                ?>
                                <tr>
                                    <td><?= $kab['alamat_provinsi'] ?></td>
                                    <td><strong><?= $kab['alamat_kabupaten'] ?></strong></td>
                                    <td><?= $kab['total_customer'] ?></td>
                                    <td><?= $kab['total_transaksi'] ?></td>
                                    <td>Rp <?= number_format($kab['total_revenue'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($avgPerCustomer, 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include '_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>

<script>
$(function() {
    $('#areaTable').DataTable({
        "order": [[4, "desc"]]
    });
});

<?php if (!empty($kabupatenStats) && empty($customerList)) : ?>
// Area Chart
const ctx = document.getElementById('areaChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($kabupatenStats, 'alamat_kabupaten')) ?>,
        datasets: [{
            data: <?= json_encode(array_map('intval', array_column($kabupatenStats, 'total_revenue'))) ?>,
            backgroundColor: [
                '#4facfe', '#00f2fe', '#667eea', '#764ba2', '#f093fb',
                '#f5576c', '#fa709a', '#fee140', '#11998e', '#38ef7d'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 10,
                    usePointStyle: true,
                    font: { size: 11 }
                }
            }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>

