<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';
?>
<?php
if ($levelLogin === "kasir") {
  echo "
      <script>
        document.location.href = 'bo';
      </script>
    ";
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Laporan Penjualan Per Produk</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="bo">Home</a></li>
            <li class="breadcrumb-item active">Penjualan Per Produk</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>


  <section class="content">
    <div class="container-fluid">
      <div class="card card-default">
        <div class="card-header">
          <h3 class="card-title">Filter Data Berdasrkan Tanggal dan Produk</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-remove"></i></button>
          </div>
        </div>
        <!-- /.card-header -->
        <form role="form" action="" method="POST">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="tanggal_awal">Tanggal Awal</label>
                  <input type="date" name="tanggal_awal" class="form-control" id="tanggal_awal" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="tanggal_akhir">Tanggal Akhir</label>
                  <input type="date" name="tanggal_akhir" class="form-control" id="tanggal_akhir" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="tanggal_akhir">Produk</label>
                  <select class="form-control select2bs4" required="" name="barang_id">
                    <option value="semua" <?= $_POST['barang_id'] == 'semua' ? 'selected' : '' ?>>Semua</option>
                    <?php
                    $produk = query("SELECT * FROM barang WHERE barang_cabang = $sessionCabang AND barang_status = 1 ORDER BY barang_id DESC ");
                    foreach ($produk as $row) : ?>
                      <option value="<?= $row['barang_id'] ?>" <?= $_POST['barang_id'] == $row['barang_id'] ? 'selected' : '' ?>><?= $row['barang_nama'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="tanggal_akhir">Aksi</label>
                  <button type="submit" name="submit" class="btn btn-primary form-control">
                    <i class="fa fa-filter"></i> Filter
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
  </section>


  <?php if (isset($_POST["submit"])) { ?>
    <?php
    $tanggal_awal  = $_POST['tanggal_awal'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    $barang_id     = $_POST['barang_id'];

    $where = '';
    if ($barang_id != 'semua') {
      $where = "AND penjualan_barang_id = '$barang_id'";
    }

    $q =
      "SELECT
        penjualan.penjualan_date,
        penjualan.barang_id,
        penjualan.penjualan_cabang,
        count(penjualan.barang_qty_keranjang) as jumlah,
        barang.barang_id,
        barang.barang_nama
      FROM penjualan 
      JOIN barang ON penjualan.barang_id = barang.barang_id
      WHERE 
        penjualan_cabang = '$sessionCabang' 
        AND penjualan_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
        $where
      GROUP BY
        penjualan.penjualan_date,barang.barang_id";

    $queryes = $conn->query($q);
    $dataGrafik = [
      'labels' => [],
      'datasets' => []
    ];

    // Mengumpulkan semua tanggal unik
    $uniqueDates = [];
    foreach ($queryes as $row) {
      $invoiceDate = $row['penjualan_date'];
      if (!in_array($invoiceDate, $uniqueDates)) {
        $uniqueDates[] = $invoiceDate;
      }
    }

    // Mengumpulkan data per kasir dan menambahkan warna dinamis
    $datasets = [];
    $colorMapping = []; // Untuk menyimpan warna unik per kasir

    foreach ($queryes as $row) {
      $userNama = $row['barang_nama'];

      // Buat warna dinamis untuk setiap kasir hanya satu kali
      if (!isset($colorMapping[$userNama])) {
        $color = getRandomColor();
        $colorMapping[$userNama] = [
          'bg' => $color . '0.2)', // Background dengan opacity 0.2
          'border' => $color . '1)' // Border dengan opacity 1
        ];
      }

      // Jika dataset untuk kasir ini belum ada, buat dataset
      if (!isset($datasets[$userNama])) {
        $datasets[$userNama] = [
          'label' => $userNama,
          'data' => array_fill(0, count($uniqueDates), 0), // Isi awal dengan 0
          'backgroundColor' => $colorMapping[$userNama]['bg'],
          'borderColor' => $colorMapping[$userNama]['border'],
          'borderWidth' => 1,
        ];
      }

      // Mengisi data pembayaran pada tanggal yang sesuai
      $index = array_search($row['penjualan_date'], $uniqueDates); // Cari indeks dari tanggal
      if ($index !== false) {
        $datasets[$userNama]['data'][$index] = (int)$row['jumlah'];
      }
    }

    // Menambahkan tanggal unik sebagai labels
    $dataGrafik['labels'] = $uniqueDates;

    // Menambahkan dataset ke data utama
    foreach ($datasets as $dataset) {
      $dataGrafik['datasets'][] = $dataset;
    }

    $gafik = json_encode($dataGrafik);
    ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Grafik</h3>
              <div class="card-body">
                <div class="">
                  <canvas id="myChart" style="width: 100%; height: 300px;"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Laporan Per Produk</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div class="table-auto">
                <table id="laporan-per-produk" class="table table-bordered table-striped table-laporan">
                  <thead>
                    <tr>
                      <th style="width: 6%;">No.</th>
                      <th style="width: 13%;">Invoice</th>
                      <th>Tanggal</th>
                      <th>Produk</th>
                      <th>QTY Terjual</th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    $i = 1;
                    $total = 0;
                    $newQ =
                      " SELECT 
                        penjualan.penjualan_id, penjualan.penjualan_barang_id, penjualan.penjualan_invoice, penjualan.penjualan_date, penjualan.barang_id, penjualan.penjualan_cabang, penjualan.barang_qty, penjualan.barang_qty_keranjang, barang.barang_id, barang.barang_nama
                      FROM penjualan 
                      JOIN barang ON penjualan.barang_id = barang.barang_id
                      WHERE 
                        penjualan_cabang = '$sessionCabang' 
                      -- AND penjualan_barang_id = '$barang_id' 
                      AND penjualan_date BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
                      $where
                      ORDER BY penjualan_id DESC";

                    $queryPenjualan = $conn->query($newQ);
                    while ($rowProduct = mysqli_fetch_array($queryPenjualan)) {
                      $total += $rowProduct['barang_qty_keranjang'];
                    ?>
                      <tr>
                        <td><?= $i; ?></td>
                        <td><?= $rowProduct['penjualan_invoice']; ?></td>
                        <td><?= $rowProduct['penjualan_date']; ?></td>
                        <td><?= $rowProduct['barang_nama']; ?></td>
                        <td><?= $rowProduct['barang_qty_keranjang']; ?></td>
                      </tr>
                      <?php $i++; ?>
                    <?php } ?>
                    <tr>
                      <td colspan="5">
                        <b>Total <span style="color: red;">Terjual <?= mysqli_num_rows($queryPenjualan); ?>x</span> dengan Jumlah Keseluruhan <span style="color: red">QTY Terjual <?= $total; ?></span></b>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  <?php  } ?>
</div>
</div>



<?php include '_footer.php'; ?>
<script>
  $(function() {
    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
  });
</script>
<script>
  $(function() {

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
  });

  const chartData = <?php echo $gafik; ?>;


  const ctx = $('#myChart')
  const myChart = new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      },
      tooltips: {
        mode: 'index',
        intersect: false,
        callbacks: {
          label: function(tooltipItem, chart) {
            const datasetLabel = chart?.datasets[tooltipItem?.datasetIndex].label || '';
            const value = tooltipItem.yLabel;

            // Format nilai sebagai mata uang Rupiah
            const formattedValue = new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0, // Atur sesuai kebutuhan, misalnya 2 untuk dua desimal
              maximumFractionDigits: 0
            }).format(value);

            return `${datasetLabel} ${formattedValue}`;
          }
        }
      }
    }
  });
</script>
<?php include '_footerlaporan.php' ?>
</body>

</html>