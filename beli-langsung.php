<?php
include '_header.php';
include '_nav.php';
include '_sidebar.php';

$userId = $_SESSION['user_id'];
$tipeHarga = base64_decode($_GET['customer']);
if ($tipeHarga == 1) {
  $nameTipeHarga = "Member Retail";
} elseif ($tipeHarga == 2) {
  $nameTipeHarga = "Grosir";
} else {
  $nameTipeHarga = "Umum";
}

if ($levelLogin === "kurir") {
  echo "
      <script>
        document.location.href = 'bo';
      </script>
    ";
}


if ($dataTokoLogin['toko_status'] < 1) {
  echo "
      <script>
        alert('Status Toko Tidak Aktif Jadi Anda Tidak Bisa melakukan Transaksi !!');
        document.location.href = 'bo';
      </script>
    ";
}

// Insert Ke keranjang Scan Barcode
if (isset($_POST["inputbarcode"])) {
  // var_dump($_POST);

  // cek apakah data berhasil di tambahkan atau tidak
  if (tambahKeranjangBarcode($_POST) > 0) {
    echo "
      <script>
        document.location.href = '';
      </script>
    ";
  }
}

error_reporting(0);
// Insert Ke keranjang
$inv = $_POST["penjualan_invoice2"];
if (isset($_POST["updateStock"])) {
  // var_dump($_POST);
  $sql = mysqli_query($conn, "SELECT * FROM invoice WHERE penjualan_invoice='$inv' && invoice_cabang = '$sessionCabang' ") or die(mysqli_error($conn));

  $hasilquery = mysqli_num_rows($sql);

  if ($hasilquery == 0) {
    // cek apakah data berhasil di tambahkan atau tidak
    if (updateStock($_POST) > 0) {
      echo "
          <script>
            document.location.href = 'invoice?no=" . $inv . "';
          </script>
        ";
    } else {
      echo "
          <script>
            alert('Transaksi Gagal !!');
          </script>
        ";
    }
  } else {
    echo "
        <script>
          document.location.href = 'invoice?no=" . $inv . "';
        </script>
      ";
  }
}

if (isset($_POST["updateStockDraft"])) {
  // var_dump($_POST);
  $sql = mysqli_query($conn, "SELECT * FROM invoice WHERE penjualan_invoice='$inv' && invoice_cabang = '$sessionCabang' ") or die(mysqli_error($conn));

  $hasilquery = mysqli_num_rows($sql);

  if ($hasilquery == 0) {
    // cek apakah data berhasil di tambahkan atau tidak
    if (updateStockDraft($_POST) > 0) {
      echo "
          <script>
            document.location.href = '';
            alert('Transaksi Berhasil Dipending !!');
          </script>
        ";
    } else {
      echo "
          <script>
            alert('Transaksi Gagal !!');
          </script>
        ";
    }
  } else {
    echo "
        <script>
          document.location.href = '';
          alert('Transaksi Berhasil dipending !!');
        </script>
      ";
  }
}

if (isset($_POST["updateSn"])) {
  if (updateSn($_POST) > 0) {
    echo "
        <script>
          document.location.href = '';
        </script>
      ";
  } else {
    echo "
        <script>
          alert('Data Gagal edit');
        </script>
      ";
  }
}

if (isset($_POST["updateQtyPenjualan"])) {
  if (updateQTYHarga($_POST) > 0) {
    echo "
        <script>
          document.location.href = '';
        </script>
      ";
  } else {
    echo "
        <script>
          alert('Data Gagal edit');
        </script>
      ";
  }
}

?>


<style>
  .container {
    --uib-size: 40px;
    --uib-color: black;
    --uib-speed: 2s;
    --uib-bg-opacity: 0;
    height: var(--uib-size);
    width: var(--uib-size);
    transform-origin: center;
    animation: rotate var(--uib-speed) linear infinite;
    will-change: transform;
    overflow: visible;
  }

  .car {
    fill: none;
    stroke: var(--uib-color);
    stroke-dasharray: 1, 200;
    stroke-dashoffset: 0;
    stroke-linecap: round;
    animation: stretch calc(var(--uib-speed) * 0.75) ease-in-out infinite;
    will-change: stroke-dasharray, stroke-dashoffset;
    transition: stroke 0.5s ease;
  }

  .track {
    fill: none;
    stroke: var(--uib-color);
    opacity: var(--uib-bg-opacity);
    transition: stroke 0.5s ease;
  }

  @keyframes rotate {
    100% {
      transform: rotate(360deg);
    }
  }

  @keyframes stretch {
    0% {
      stroke-dasharray: 0, 150;
      stroke-dashoffset: 0;
    }

    50% {
      stroke-dasharray: 75, 150;
      stroke-dashoffset: -25;
    }

    100% {
      stroke-dashoffset: -100;
    }
  }
</style>



<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-8">
          <h1>Transaksi Kasir <b style="color: #007bff; ">Customer <?= $nameTipeHarga; ?></b></h1>
          <div class="btn-cash-piutang">
            <?php
            // Ambil data dari URL Untuk memberikan kondisi transaksi Cash atau Piutang
            if (empty(abs((int)base64_decode($_GET['r'])))) {
              $r = 0;
            } else {
              $r = abs((int)base64_decode($_GET['r']));
            }
            ?>

            <?php if ($r == 1) : ?>
              <a href="beli-langsung?customer=<?= $_GET['customer']; ?>" class="btn btn-default">Cash</a>
              <a href="beli-langsung?customer=<?= $_GET['customer']; ?>&r=MQ==" class="btn btn-primary">Piutang</a>
            <?php else : ?>
              <a href="beli-langsung?customer=<?= $_GET['customer']; ?>" class="btn btn-primary">Cash</a>
              <a href="beli-langsung?customer=<?= $_GET['customer']; ?>&r=MQ==" class="btn btn-default">Piutang</a>
            <?php endif; ?>
            <a class="btn btn-danger" data-toggle="modal" href='#modal-id-draft' data-backdrop="static">Pending</a>
            <!-- <a class="btn btn-info" href="beli-langsung-transfer?customer=<?= $_GET['customer']; ?>" data-backdrop="static">Transfer</a> -->
            <div class="modal fade" id="modal-id-draft">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Data Transaksi Pending</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  </div>
                  <div class="modal-body">
                    <?php
                    $draft = query("SELECT * FROM invoice WHERE invoice_draft = 1 && invoice_kasir = $userId && invoice_cabang = $sessionCabang ORDER BY invoice_id DESC");
                    ?>
                    <div class="table-auto">
                      <table id="example7" class="table table-bordered table-striped">
                        <thead>
                          <tr>
                            <th style="width: 5px;">No.</th>
                            <th>Invoice</th>
                            <th style="width: 40% !important;">Tanggal</th>
                            <th>Customer</th>
                            <th class="text-center">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>

                          <?php $i = 1; ?>
                          <?php foreach ($draft as $row) : ?>
                            <tr>
                              <td><?= $i; ?></td>
                              <td><?= $row['penjualan_invoice']; ?></td>
                              <td><?= tanggal_indo($row['invoice_tgl']); ?></td>
                              <td>
                                <?php
                                $customer_id_draft = $row['invoice_customer'];
                                $namaCustomerDraft = mysqli_query($conn, "SELECT customer_nama FROM customer WHERE customer_id = $customer_id_draft");
                                $namaCustomerDraft = mysqli_fetch_array($namaCustomerDraft);
                                $customer_nama_draft = $namaCustomerDraft['customer_nama'];

                                if ($customer_id_draft < 1) {
                                  echo "Customer Umum";
                                } else {
                                  echo $customer_nama_draft;
                                }
                                ?>
                              </td>
                              <td class="orderan-online-button">
                                <a href="beli-langsung-draft?customer=<?= base64_encode($row['invoice_customer_category']); ?>&r=<?= base64_encode($row['invoice_piutang']); ?>&invoice=<?= base64_encode($row['penjualan_invoice']); ?>" title="Edit Data">
                                  <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-edit"></i>
                                  </button>
                                </a>
                                <a href="beli-langsung-draft-delete?invoice=<?= $row['penjualan_invoice']; ?>&customer=<?= $_GET['customer']; ?>&cabang=<?= $sessionCabang; ?>" onclick="return confirm('Yakin dihapus ?')" title="Delete Data">
                                  <button class="btn btn-danger" type="submit">
                                    <i class="fa fa-trash"></i>
                                  </button>
                                </a>
                              </td>
                            </tr>
                            <?php $i++; ?>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="bo">Home</a></li>
            <li class="breadcrumb-item active">Barang</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>


  <section class="content">
    <?php
    $userId = $_SESSION['user_id'];
    $keranjang = query("SELECT * FROM keranjang WHERE keranjang_id_kasir = $userId && keranjang_tipe_customer = $tipeHarga && keranjang_cabang = $sessionCabang ORDER BY keranjang_id DESC");

    $countInvoice = mysqli_query($conn, "select * from invoice where invoice_cabang = " . $sessionCabang . " ");
    $countInvoice = mysqli_num_rows($countInvoice);
    if ($countInvoice < 1) {
      $jmlPenjualan1 = 0;
    } else {
      $penjualan = query("SELECT * FROM invoice WHERE invoice_cabang = $sessionCabang ORDER BY invoice_id DESC lIMIT 1")[0];
      $jmlPenjualan1 = $penjualan['penjualan_invoice_count'];
    }
    $jmlPenjualan1 = $jmlPenjualan1 + 1;
    ?>
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-8 col-lg-8">
              <div class="card-invoice">
                <span>No. Invoice: </span>
                <?php
                $today = date("Ymdis");
                $di = $today . $jmlPenjualan1 . $userId ;
                ?>
                <input type="text" name="invoicing" id="invoicing" value="<?= $di  ?>">
              </div>
            </div>
            <div class="col-md-4 col-lg-4">
              <div class="cari-barang-parent">
                <div class="row">
                  <div class="col-10">
                    <form action="" method="post">
                      <input type="hidden" name="keranjang_id_kasir" value="<?= $userId; ?>">
                      <input type="hidden" name="keranjang_cabang" value="<?= $sessionCabang; ?>">
                      <input type="hidden" name="tipe_harga" value="<?= $tipeHarga; ?>">
                      <input type="text" class="form-control" autofocus="" name="inputbarcode" placeholder="Barcode / Kode Barang" required="">
                    </form>
                  </div>
                  <div class="col-2">
                    <a class="btn btn-primary" title="Cari Produk" data-toggle="modal" id="cari-barang" href='#modal-id'>
                      <i class="fa fa-search"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- /.card-header -->
        <div class="card-body">
          <div class="table-auto">
            <table id="" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th style="width: 6%;">No.</th>
                  <th>Nama</th>
                  <th>Harga</th>
                  <th>Satuan</th>
                  <th style="text-align: center;">QTY</th>
                  <th>No. SN</th>
                  <th style="width: 20%;">Sub Total</th>
                  <th style="text-align: center; width: 10%;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i          = 1;
                $total_beli = 0;
                $total      = 0;
                ?>
                <?php
                foreach ($keranjang as $row) :

                  $bik = $row['barang_id'];
                  $stockParent = mysqli_query($conn, "select barang_stock, satuan_isi_1, satuan_isi_2, satuan_isi_3 from barang where barang_id = '" . $bik . "'");
                  $brg = mysqli_fetch_array($stockParent);
                  $tb_brg       = $brg['barang_stock'];

                  // $sub_total_beli = ($row['keranjang_harga_beli'] * $row['keranjang_qty_view']) * $row['keranjang_konversi_isi'];
                  $sub_total_beli = $row['keranjang_harga_beli'] * $row['keranjang_qty'];
                  $sub_total      = $row['keranjang_harga'] * $row['keranjang_qty_view'];

                  if ($row['keranjang_id_kasir'] === $_SESSION['user_id']) {
                    $total_beli += $sub_total_beli;
                    $total += $sub_total;
                ?>
                    <tr>
                      <td><?= $i; ?></td>
                      <td><?= $row['keranjang_nama'] ?></td>
                      <td>Rp. <?= number_format($row['keranjang_harga'], 0, ',', '.'); ?></td>
                      <td>
                        <?php
                        $satuan = $row['keranjang_satuan'];
                        $dataSatuan = mysqli_query($conn, "select satuan_nama from satuan where satuan_id = " . $satuan . " ");
                        $dataSatuan = mysqli_fetch_array($dataSatuan);
                        $dataSatuan = $dataSatuan['satuan_nama'];
                        echo $dataSatuan;
                        ?>
                      </td>
                      <td style="text-align: center;"><?= $row['keranjang_qty_view']; ?></td>
                      
                      <td>
                        <?php
                        if ($row['keranjang_barang_option_sn'] < 1) {
                          $sn = "Non-SN";
                        } else {
                          $sn = $row['keranjang_sn'];
                          if ($row['keranjang_sn'] == null) {
                            echo '
                                <span class="keranjang-right">
                                  <button class=" btn-success" name="" class="keranjang-pembelian"    id="keranjang_sn" data-id="' . $row['keranjang_id'] . '">
                                    <i class="fa fa-edit"></i>
                                  </button> 
                                </span>';
                          } elseif ($row['keranjang_sn'] === "0") {
                            echo '
                                <span class="keranjang-right">
                                  <button class=" btn-success" name="" class="keranjang-pembelian"    id="keranjang_sn" data-id="' . $row['keranjang_id'] . '">
                                    <i class="fa fa-edit"></i>
                                  </button> 
                                </span>';
                          }
                        }
                        echo $sn;
                        ?>
                      </td>
                      
                      <td>Rp. <?= number_format($sub_total, 0, ',', '.'); ?></td>
                      <td class="orderan-online-button">
                        <a href="#!" title="Edit Data">
                          <button class="btn btn-primary" name="" class="keranjang-pembelian" id="keranjang-qty" data-id="<?= $row['keranjang_id']; ?>">
                            <i class="fa fa-pencil"></i>
                          </button>
                        </a>
                        <a href="beli-langsung-delete?id=<?= $row['keranjang_id']; ?>&customer=<?= $_GET['customer']; ?>&r=<?= $r; ?>" title="Delete Data" onclick="return confirm('Yakin dihapus ?')">
                          <button class="btn btn-danger" type="submit" name="hapus">
                            <i class="fa fa-trash-o"></i>
                          </button>
                        </a>
                      </td>
                    </tr>
                    <?php $i++; ?>
                  <?php } ?>
                <?php endforeach; ?>
            </table>
          </div>

          <div class="btn-transaksi">
            <form role="form" action="" id="form-main" method="POST">
              <div class="row">
                <div class="col-md-6 col-lg-7">
                  <div class="filter-customer">
                    <div class="form-group">
                      <label>Tipe Customer</label>
                      <select class="form-control select2bs4 pilihan-marketplace" name="tipe_customer" id="tipe_customer">
                        <option value="0" <?= $tipeHarga == 0 ? 'selected' : null ?>>Umum</option>
                        <option value="1" <?= $tipeHarga == 1 ? 'selected' : null ?>>Member Retail</option>
                        <option value="2" <?= $tipeHarga == 2 ? 'selected' : null ?>>Grosir</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Customer <b style="color: #007bff; "><?= $nameTipeHarga; ?></b></label>
                      <select class="form-control select2bs4 pilihan-marketplace" required="" name="invoice_customer">
                        <!-- <option selected="selected" value="">Pilih Customer</option> -->

                        <?php if ($r != 1 && $tipeHarga < 2) { ?>
                          <option value="0">Umum</option>
                        <?php } ?>

                        <?php
                        $customer = query("SELECT * FROM customer WHERE customer_cabang = $sessionCabang && customer_status = 1 && customer_category = $tipeHarga ORDER BY customer_id DESC ");
                        ?>
                       <?php foreach ($customer as $ctr) : ?>
  <?php if ($ctr['customer_id'] > 1 && $ctr['customer_nama'] !== "Customer Umum") { ?>
    <option value="<?= $ctr['customer_id'] ?>">
      <?= $ctr['customer_nama'] ?> 
      <?php if (!empty($ctr['customer_kartu'])): ?>
        (<?= $ctr['customer_kartu'] ?>)
      <?php endif; ?>
    </option>
  <?php } ?>
<?php endforeach; ?>

                      </select>
                      <small>
                        <a href="customer-add">Tambah Customer <i class="fa fa-plus"></i></a>
                      </small>
                    </div>

                    <!-- View Jika Select Dari Marketplace -->
                    <span id="beli-langsung-marketplace"></span>

                    <div class="form-group">
                      <label>Tipe Pembayaran</label>
                      <select class="form-control" required="" name="invoice_tipe_transaksi" id="payment-type">
                        <option selected="selected" value="0">Cash</option>
                        <option value="1">Transfer</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Kurir</label>
                      <select class="form-control select2bs4" required="" name="invoice_kurir">
                        <?php if ($dataTokoLogin['toko_ongkir'] > 0) { ?>
                          <option selected="selected" value="">-- Pilih Kurir --</option>
                        <?php } ?>
                        <option value="0">Tanpa Kurir</option>
                        <?php
                        $kurir = query("SELECT * FROM user WHERE user_level = 'kurir' && user_cabang = $sessionCabang && user_status = '1' ORDER BY user_id DESC ");
                        ?>
                        <?php foreach ($kurir as $row) : ?>
                          <option value="<?= $row['user_id']; ?>">
                            <?= $row['user_nama']; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- kondisi jika memilih piutang -->
                    <?php if ($r == 1) : ?>
                      <div class="form-group">
                        <label style="color: red;">Jatuh Tempo</label>
                        <input type="date" name="invoice_piutang_jatuh_tempo" class="form-control" required="" value="<?= date("Y-m-d"); ?>">
                      </div>
                    <?php else : ?>
                      <input type="hidden" name="invoice_piutang_jatuh_tempo" value="0">
                    <?php endif; ?>

                  </div>
                </div>
                <div class="col-md-6 col-lg-5">
                  <div class="invoice-table">
                    <table class="table">
                      <tr>
                        <td style="width: 110px;"><b>Total</b></td>
                        <td class="table-nominal">
                          <!-- Rp. <?php echo number_format($total, 0, ',', '.'); ?> -->
                          <span>Rp. </span>
                          <span>
                            <input type="text" name="invoice_total" id="angka2" class="a2" value="<?= $total; ?>" onkeyup="return isNumberKey(event)" size="10" readonly>
                          </span>

                        </td>
                      </tr>

                      <!-- Ongkir Dinamis untuk Inputan -->
                      <tr class="ongkir-dinamis none">
                        <td>Ongkir</td>
                        <td class="table-nominal tn">
                          <span>Rp.</span>
                          <span class="ongkir-beli-langsung">
                            <input type="number" name="invoice_ongkir" id="" class="b2 ongkir-dinamis-input" autocomplete="off" onkeyup="hitung2();" onkeyup="return isNumberKey(event)" onkeypress="return hanyaAngka1(event)">
                            <i class="fa fa-close fa-ongkir-dinamis"></i>
                          </span>
                        </td>
                      </tr>
                      <tr class="ongkir-dinamis none">
                        <td>Diskon</td>
                        <td class="table-nominal tn">
                          <span>Rp.</span>
                          <span>
                            <input type="number" name="invoice_diskon" id="" class="f2 ongkir-dinamis-diskon" autocomplete="off" onkeyup="hitung6();" onkeyup="return isNumberKey(event)" onkeypress="return hanyaAngka1(event)" size="10">
                          </span>
                        </td>
                      </tr>

                      <tr class="ongkir-dinamis none">
                        <td><b>Sub Total</b></td>

                        <td class="table-nominal c2parent">
                          <span>Rp. </span>
                          <span>
                            <input type="text" name="invoice_sub_total" class="c2" value="<?= $total; ?>" readonly>
                          </span>
                        </td>

                        <td class="table-nominal g2parent" style="display: none;">
                          <span>Rp. </span>
                          <span>
                            <input type="text" name="invoice_sub_total" class="g2" value="<?= $total; ?>" readonly>
                          </span>
                        </td>
                      </tr>

                      <tr class="ongkir-dinamis none">
                        <td>
                          <b style="color: red;">
                            <?php
                            // kondisi jika memilih piutang
                            if ($r == 1) {
                              echo "DP";
                            } else {
                              echo "Bayar";
                            }
                            ?>
                          </b>
                        </td>

                        <td class="table-nominal tn d2parent">
                          <span>Rp.</span>
                          <span class="">
                            <input type="number" name="angka1" id="angka1" class="d2 ongkir-dinamis-bayar" autocomplete="off" onkeyup="hitung3();" onkeyup="return isNumberKey(event)" onkeypress="return hanyaAngka1(event)" size="10">
                          </span>
                        </td>

                        <td class="table-nominal tn h2parent" style="display: none;">
                          <span>Rp.</span>
                          <span class="">
                            <input type="number" name="angka1" id="angka1" class="h22 ongkir-dinamis-bayar" autocomplete="off" onkeyup="hitung7();" onkeyup="return isNumberKey(event)" onkeypress="return hanyaAngka1(event)" size="10">
                          </span>
                        </td>
                      </tr>

                      <tr class="ongkir-dinamis none">
                        <td>
                          <?php
                          // kondisi jika memilih piutang
                          if ($r == 1) {
                            echo "Sisa Piutang";
                          } else {
                            echo "Kembali";
                          }
                          ?>
                        </td>
                        <td class="table-nominal">
                          <span>Rp.</span>
                          <span>
                            <input type="text" name="hasil" id="hasil" class="e2" readonly size="10" disabled>
                          </span>
                        </td>
                      </tr>
                      <!-- End Ongkir Dinamis untuk Inputan -->

                      <!-- Ongkir Statis untuk Inputan -->
                      <tr class="ongkir-statis">
                        <td>Ongkir</td>
                        <td class="table-nominal tn">
                          <span>Rp.</span>
                          <span class="ongkir-beli-langsung">
                            <input type="number" value="<?= $dataTokoLogin['toko_ongkir']; ?>" name="invoice_ongkir" id="" class="b2 ongkir-statis-input" readonly>
                            <i class="fa fa-close fa-ongkir-statis"></i>
                          </span>
                        </td>
                      </tr>
                      <tr class="ongkir-statis">
                        <td>Diskon</td>
                        <td class="table-nominal tn">
                          <span>Rp.</span>
                          <span>
                            <input type="number" name="invoice_diskon" id="" class="f21 ongkir-statis-diskon" value="0" required="" autocomplete="off" onkeyup="hitung5();" onkeyup="return isNumberKey(event)" onkeypress="return hanyaAngka1(event)" size="10">
                          </span>
                        </td>
                      </tr>
                      <tr class="ongkir-statis">
                        <td><b>Sub Total</b></td>
                        <td class="table-nominal">
                          <span>Rp. </span>
                          <span>
                            <?php
                            $subTotal = $total + $dataTokoLogin['toko_ongkir'];
                            ?>
                            <input type="hidden" name="" class="g21" value="<?= $subTotal; ?>" readonly>
                            <input type="text" name="invoice_sub_total" class="c21" value="<?= $subTotal; ?>" readonly>
                          </span>

                        </td>
                      </tr>
                      <tr class="ongkir-statis">
                        <td>
                          <b style="color: red;">
                            <?php
                            // kondisi jika memilih piutang
                            if ($r == 1) {
                              echo "DP";
                            } else {
                              echo "Bayar";
                            }
                            ?>
                          </b>
                        </td>
                        <td class="table-nominal tn">
                          <span>Rp.</span>
                          <span>
                            <input type="number" name="angka1" id="angka1" class="d21 ongkir-statis-bayar" autocomplete="off" onkeyup="hitung4();" onkeyup="return isNumberKey(event)" onkeypress="return hanyaAngka1(event)" size="10">
                          </span>
                        </td>
                      </tr>
                      <tr class="ongkir-statis">
                        <td>
                          <?php
                          // kondisi jika memilih piutang
                          if ($r == 1) {
                            echo "Sisa Piutang";
                          } else {
                            echo "Kembali";
                          }
                          ?>
                        </td>
                        <td class="table-nominal">
                          <span>Rp.</span>
                          <span>
                            <input type="text" name="hasil" id="hasil" class="e21" readonly size="10" disabled>
                          </span>
                        </td>
                      </tr>
                      <!-- End Ongkir Statis untuk Inputan -->


                      <tr>
                        <td></td>
                        <td>

                          <?php foreach ($keranjang as $stk => $value) : ?>
                            <?php if ($value['keranjang_id_kasir'] === $userId) { ?>
                              <!-- <input type="hidden" name="barang_ids[]" value="<?= $value['barang_id']; ?>">
                              <input type="hidden" min="1" name="keranjang_qty[]" value="<?= $value['keranjang_qty']; ?>">
                              <input type="hidden" min="1" name="keranjang_qty_view[]" value="<?= $value['keranjang_qty_view']; ?>">
                              <input type="hidden" name="keranjang_konversi_isi[]" value="<?= $value['keranjang_konversi_isi']; ?>">
                              <input type="hidden" name="keranjang_satuan[]" value="<?= $value['keranjang_satuan']; ?>">
                              <input type="hidden" name="keranjang_harga_beli[]" value="<?= $value['keranjang_harga_beli']; ?>">
                              <input type="hidden" name="keranjang_harga[]" value="<?= $value['keranjang_harga']; ?>">
                              <input type="hidden" name="keranjang_harga_parent[]" value="<?= $value['keranjang_harga_parent']; ?>">
                              <input type="hidden" name="keranjang_harga_edit[]" value="<?= $value['keranjang_harga_edit']; ?>">
                              <input type="hidden" name="keranjang_id_kasir[]" value="<?= $value['keranjang_id_kasir']; ?>">

                              <input type="hidden" name="penjualan_invoice[]" value="<?= $di; ?>">
                              <input type="hidden" name="penjualan_date[]" value="<?= date("Y-m-d") ?>">

                              <input type="hidden" name="keranjang_barang_option_sn[]" value="<?= $value['keranjang_barang_option_sn']; ?>">
                              <input type="hidden" name="keranjang_barang_sn_id[]" value="<?= $value['keranjang_barang_sn_id']; ?>">
                              <input type="hidden" name="keranjang_sn[]" value="<?= $value['keranjang_sn']; ?>">
                              <input type="hidden" name="invoice_customer_category2[]" value="<?= $tipeHarga; ?>">
                              <input type="hidden" name="keranjang_nama[]" value="<?= $value['keranjang_nama']; ?>">
                              <input type="hidden" name="barang_kode_slug[]" value="<?= $value['barang_kode_slug']; ?>">
                              <input type="hidden" name="keranjang_id_cek[]" value="<?= $value['keranjang_id_cek']; ?>">
                              <input type="hidden" name="penjualan_cabang[]" value="<?= $sessionCabang; ?>"> -->
                              <input type="hidden" name="barang_ids[<?= $stk ?>]" value="<?= $value['barang_id']; ?>">
                              <input type="hidden" min="1" name="keranjang_qty[<?= $stk ?>]" value="<?= $value['keranjang_qty']; ?>">
                              <input type="hidden" min="1" name="keranjang_qty_view[<?= $stk ?>]" value="<?= $value['keranjang_qty_view']; ?>">
                              <input type="hidden" name="keranjang_konversi_isi[<?= $stk ?>]" value="<?= $value['keranjang_konversi_isi']; ?>">
                              <input type="hidden" name="keranjang_satuan[<?= $stk ?>]" value="<?= $value['keranjang_satuan']; ?>">
                              <input type="hidden" name="keranjang_harga_beli[<?= $stk ?>]" value="<?= $value['keranjang_harga_beli']; ?>">
                              <input type="hidden" name="keranjang_harga[<?= $stk ?>]" value="<?= $value['keranjang_harga']; ?>">
                              <input type="hidden" name="keranjang_harga_parent[<?= $stk ?>]" value="<?= $value['keranjang_harga_parent']; ?>">
                              <input type="hidden" name="keranjang_harga_edit[<?= $stk ?>]" value="<?= $value['keranjang_harga_edit']; ?>">
                              <input type="hidden" name="keranjang_id_kasir[<?= $stk ?>]" value="<?= $value['keranjang_id_kasir']; ?>">

                              <input type="hidden" name="penjualan_invoice[<?= $stk ?>]" value="<?= $di; ?>">
                              <input type="hidden" name="penjualan_date[<?= $stk ?>]" value="<?= date("Y-m-d") ?>">

                              <input type="hidden" name="keranjang_barang_option_sn[<?= $stk ?>]" value="<?= $value['keranjang_barang_option_sn']; ?>">
                              <input type="hidden" name="keranjang_barang_sn_id[<?= $stk ?>]" value="<?= $value['keranjang_barang_sn_id']; ?>">
                              <input type="hidden" name="keranjang_sn[<?= $stk ?>]" value="<?= $value['keranjang_sn']; ?>">
                              <input type="hidden" name="invoice_customer_category2[<?= $stk ?>]" value="<?= $tipeHarga; ?>">
                              <input type="hidden" name="keranjang_nama[<?= $stk ?>]" value="<?= $value['keranjang_nama']; ?>">
                              <input type="hidden" name="barang_kode_slug[<?= $stk ?>]" value="<?= $value['barang_kode_slug']; ?>">
                              <input type="hidden" name="keranjang_id_cek[<?= $stk ?>]" value="<?= $value['keranjang_id_cek']; ?>">
                              <input type="hidden" name="penjualan_cabang[<?= $stk ?>]" value="<?= $sessionCabang; ?>">
                              <input type="hidden" name="items[<?= $stk ?>]" class="items" value='{"id":"<?= $value['barang_id']; ?>","name":"<?= $value['keranjang_nama']; ?>","quantity":"<?= $value['keranjang_qty_view']; ?>","price":"<?= $value['keranjang_harga']; ?>"}'>
                            <?php } ?>
                          <?php endforeach; ?>
                          <input type="hidden" name="penjualan_invoice2" value="<?= $di; ?>">
                          <input type="hidden" name="invoice_customer_category" value="<?= $tipeHarga; ?>">
                          <input type="hidden" name="kik" value="<?= $userId; ?>">
                          <input type="hidden" name="penjualan_invoice_count" value="<?= $jmlPenjualan1; ?>">
                          <input type="hidden" name="invoice_piutang" value="<?= $r; ?>">
                          <input type="hidden" name="invoice_piutang_lunas" value="0">
                          <input type="hidden" name="invoice_cabang" value="<?= $sessionCabang; ?>">
                          <input type="hidden" name="invoice_total_beli" value="<?= $total_beli; ?>">
                        </td>
                      </tr>
                    </table>
                  </div>
                  <div class="payment">
                    <?php
                    $idKasirKeranjang = $_SESSION['user_id'];
                    $dataSn = mysqli_query($conn, "select * from keranjang where keranjang_barang_option_sn > 0 && keranjang_sn != null && keranjang_cabang = $sessionCabang && keranjang_id_kasir = $idKasirKeranjang");
                    $jmlDataSn = mysqli_num_rows($dataSn);
                    ?>
                    <?php if ($jmlDataSn < 1) { ?>
                      <button class="btn btn-danger" type="submit" name="updateStockDraft">Transaksi Pending <i class="fa fa-file-o"></i></button>
                      <button class="btn btn-primary updateStok" type="submit" name="updateStock">Simpan Payment <i class="fa fa-shopping-cart"></i></button>
                    <?php } else { ?>
                      <a href="#!" class="btn btn-default jmlDataSn" type="" name="">Transaksi Pending <i class="fa fa-file-o"></i></a>
                      <a href="#!" class="btn btn-default jmlDataSn" type="" name="">Simpan Payment <i class="fa fa-shopping-cart"></i></a>
                    <?php } ?>

                    <button type="button" id="create-midtrans" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" style="display: none">
                      Buat Pesanan
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Midtrans</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <div class="d-none">
                              <svg
                                class="container"
                                viewBox="0 0 40 40"
                                height="40"
                                width="40">
                                <circle
                                  class="track"
                                  cx="20"
                                  cy="20"
                                  r="17.5"
                                  pathlength="100"
                                  stroke-width="5px"
                                  fill="none" />
                                <circle
                                  class="car"
                                  cx="20"
                                  cy="20"
                                  r="17.5"
                                  pathlength="100"
                                  stroke-width="5px"
                                  fill="none" />
                              </svg>
                            </div>
                            <div id="loaders-midtrans" class="text-center bg-light d-flex justify-content-center align-items-center rounded" style="width:100%;min-height:500px;">
                              <iframe id="snap-midtrans" src="" width="100%" height="500px"></iframe>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button class="btn btn-primary" type="button" id="see-invoice">Lihat Invoice <i class="fa fa-shopping-cart"></i></button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        <!-- /.card-body -->
      </div>
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
</section>
<!-- /.content -->
</div>
</div>


<div class="modal fade" id="modal-id" data-backdrop="static">
  <div class="modal-dialog modal-lg-pop-up">
    <div class="modal-content">
      <div class="modal-body">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Data barang Keseluruhan</h3>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <div class="table-auto">
              <table id="example1" class="table table-bordered table-striped" style="width: 100%;">
                <thead>
                  <tr>
                    <th style="width: 5%;">No.</th>
                    <th>Kode Barang</th>
                    <th>Nama</th>
                    <th>
                      <?php
                      echo "Harga <b style='color: #007bff;'>" . $nameTipeHarga . "</b>";
                      ?>
                    </th>
                    <th>Stock</th>
                    <th style="text-align: center;">Aksi</th>
                  </tr>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
          </div>
          <!-- /.card-body -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<!-- Modal Update SN -->
<div class="modal fade" id="modal-id-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form role="form" id="form-edit-no-sn" method="POST" action="">
        <div class="modal-header">
          <h4 class="modal-title">No. SN Produk</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <div class="modal-body" id="data-keranjang-no-sn">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary" name="updateSn">Edit Data</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Modal Update QTY Penjualan -->
<div class="modal fade" id="modal-id-2">
  <div class="modal-dialog">
    <div class="modal-content">

      <form role="form" id="form-edit-qty" method="POST" action="">
        <div class="modal-header">
          <h4 class="modal-title">Edit Produk</h4>
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <div class="modal-body" id="data-keranjang-qty">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-primary" name="updateQtyPenjualan">Edit Data</button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    var table = $('#example1').DataTable({
      "processing": true,
      "serverSide": true,

      <?php if ($tipeHarga == 1) : ?> "ajax": "beli-langsung-search-data-grosir-1.php?cabang=<?= $sessionCabang; ?>",
      <?php elseif ($tipeHarga == 2) : ?> "ajax": "beli-langsung-search-data-grosir-2.php?cabang=<?= $sessionCabang; ?>",
      <?php else : ?> "ajax": "beli-langsung-search-data.php?cabang=<?= $sessionCabang; ?>",
      <?php endif; ?>

      "columnDefs": [{
          "targets": 3,
          "render": $.fn.dataTable.render.number('.', '', '', 'Rp. ')

        },
        {
          "targets": -1,
          "data": null,
          "defaultContent": `<center>

                      <button class='btn btn-primary tblInsert' title="Tambah Keranjang">
                         <i class="fa fa-shopping-cart"></i> Pilih
                      </button>

                  </center>`
        }
      ]
    });

    table.on('draw.dt', function() {
      var info = table.page.info();
      table.column(0, {
        search: 'applied',
        order: 'applied',
        page: 'applied'
      }).nodes().each(function(cell, i) {
        cell.innerHTML = i + 1 + info.start;
      });
    });

    $('#example1 tbody').on('click', '.tblInsert', function() {
      var data = table.row($(this).parents('tr')).data();
      var data0 = data[0];
      var data0 = btoa(data0);
      window.location.href = "beli-langsung-add?id=" + data0 + "&customer=<?= $_GET['customer']; ?>&r=<?= $r; ?>";
    });

  });
</script>

<?php include '_footer.php'; ?>

<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<script>
  $(function() {
    $("#example1").DataTable();
  });
  $(function() {
    $("#example7").DataTable();
  });
</script>
<script>
  function hanyaAngka(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))

      return false;
    return true;
  }

  function hanyaAngka1(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))

      return false;
    return true;
  }
</script>
<script>
  function hitung2() {
    var txtFirstNumberValue = document.querySelector('.a2').value;
    var txtSecondNumberValue = document.querySelector('.b2').value;
    var result = parseInt(txtFirstNumberValue) + parseInt(txtSecondNumberValue);
    if (!isNaN(result)) {
      document.querySelector('.c2').value = result;
    }
  }

  function hitung3() {
    var a = $(".d2").val();
    var b = $(".c2").val();
    c = a - b;
    $(".e2").val(c);
  }

  function hitung7() {
    var a = $(".h22").val();
    var b = $(".g2").val();
    c = a - b;
    $(".e2").val(c);
  }

  // Diskon
  function hitung6() {
    document.querySelector(".g2parent").style.display = "block";
    document.querySelector(".c2parent").style.display = "none";
    document.querySelector(".h2parent").style.display = "block";
    document.querySelector(".d2parent").style.display = "none";
    var a = $(".c2").val();
    var b = $(".f2").val();
    c = a - b;
    $(".g2").val(c);
  }

  // =================================== Statis ================================== //
  // Sub Total - Bayar = kembalian
  function hitung4() {
    var a = $(".d21").val();
    var b = $(".c21").val();
    c = a - b;
    $(".e21").val(c);
  }

  // Diskon
  function hitung5() {
    var a = $(".g21").val();
    var b = $(".f21").val();
    c = a - b;
    $(".c21").val(c);
  }
  // =================================== End Statis ================================== //

  function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
      return false;
    return true;
  }
</script>
<script>
  $(function() {

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
  });
</script>

<script>
  $(document).ready(function() {

    $(".pilihan-marketplace").change(function() {
      $(this).find("option:selected").each(function() {
        var optionValue = $(this).attr("value");
        if (optionValue) {
          $(".box1").not("." + optionValue).hide();
          $("." + optionValue).show();
        } else {
          $(".box1").hide();
        }
      });
    }).change();

    // Memanggil Pop Up Data Produk SN dan Non SN
    $(document).on('click', '#keranjang_sn', function(e) {
      e.preventDefault();
      $("#modal-id-1").modal('show');
      $.post('beli-langsung-sn.php', {
          id: $(this).attr('data-id')
        },
        function(html) {
          $("#data-keranjang-no-sn").html(html);
        }
      );
    });


    // Memanggil Pop Up Data Edit QTY
    $(document).on('click', '#keranjang-qty', function(e) {
      e.preventDefault();
      $("#modal-id-2").modal('show');
      $.post('beli-langsung-edit-qty.php?customer=<?= $tipeHarga; ?>', {
          id: $(this).attr('data-id')
        },
        function(html) {
          $("#data-keranjang-qty").html(html);
        }
      );
    });

    // Memanggil Pop Up Data Edit Harga
    $(document).on('click', '#keranjang-harga', function(e) {
      e.preventDefault();
      $("#modal-id-2").modal('show');
      $.post('beli-langsung-edit-harga.php?customer=<?= $tipeHarga; ?>', {
          id: $(this).attr('data-id')
        },
        function(html) {
          $("#data-keranjang-harga").html(html);
        }
      );
    });

    $(".jmlDataSn").click(function() {
      alert("Anda Tidak Bisa Melanjutkan Transaksi Karena data No. SN Masih Ada yang Kosong !!");
    });

    // View Hidden Ongkir
    $(".fa-ongkir-statis").click(function() {
      $(".ongkir-statis").addClass("none");
      $(".ongkir-statis-input").attr("name", "");
      $(".ongkir-dinamis-input").attr("name", "invoice_ongkir");

      $(".ongkir-statis-diskon").attr("name", "");
      $(".ongkir-dinamis-diskon").attr("name", "invoice_diskon");

      $(".ongkir-statis-bayar").attr("name", "");
      $(".ongkir-dinamis-bayar").attr("name", "angka1");

      // $(".ongkir-dinamis-bayar").attr("required", true);
      $(".ongkir-statis-bayar").removeAttr("required");
      $(".ongkir-statis-diskon").removeAttr("required");
      $(".ongkir-dinamis-diskon").attr("required", true);
      $(".ongkir-dinamis").removeClass("none");
    });

    $(".fa-ongkir-dinamis").click(function() {
      $(".ongkir-dinamis").addClass("none");
      $(".ongkir-dinamis-input").attr("name", "");
      $(".ongkir-statis-input").attr("name", "invoice_ongkir");

      $(".ongkir-dinamis-diskon").attr("name", "");
      $(".ongkir-statis-diskon").attr("name", "invoice_diskon");

      $(".ongkir-dinamis-bayar").attr("name", "");
      $(".ongkir-statis-bayar").attr("name", "angka1");

      // $(".ongkir-dinamis-bayar").removeAttr("required");
      $(".ongkir-dinamis-diskon").removeAttr("required");
      $(".ongkir-statis-diskon").attr("required", true);
      $(".ongkir-statis-bayar").attr("required", true);
      $(".ongkir-statis").removeClass("none");
    });
  });

  // load halaman di pilihan select jenis usaha
  $('#beli-langsung-marketplace').load('beli-langsung-marketplace.php');
</script>

</body>

<script>
  $(document).ready(function() {
    $('#see-invoice').click(function() {
      window.location.href = `invoice?no=${$("[name=invoicing]").val()}`;
    })

    $('#payment-type').change(function() {
      console.log('hgello');
      if (this.value == 1) {
        $('#create-midtrans').prop('disabled', false).show();
        $(".updateStok").prop('disabled', true).hide();
      } else {
        $('.updateStok').prop('disabled', false).show();
        $("#create-midtrans").prop('disabled', true).hide();

      }
    })

    $('#create-midtrans').on('click', function(e) {
      e.preventDefault();
      const type = $("#payment-type").val()
      if (type === "1") {
        let formData = {};
        const items = [];
        $(".items").each(function() {
          items.push(JSON.parse($(this).val()));
        });

        let gross_amount = 0;
        items.forEach(element => {
          gross_amount += element.price * element.quantity;
        });

        const dataStok = {};
        $.each($("#form-main").serializeArray(), function(_, {
          name,
          value
        }) {
          // Skip fields with name starting with "items["
          if (name.startsWith("items[")) {
            return; // Skip this iteration
          }

          const [key, index] = name.split("[");
          if (index) {
            dataStok[key] = dataStok[key] || {};
            dataStok[key][index.replace("]", "")] = value;
          } else {
            dataStok[key] = value;
          }
        });

        const data = {
          transaction: {
            invoice: $("[name=invoicing]").val(),
            gross_amount: gross_amount
          },
          customer: $("[name=invoice_customer]").val(),
          items: items,
          updateStok: dataStok
        };

        $.ajax({
          type: 'POST',
          url: 'https://api.numartmagelang.com/api/midtrans/payment/create',
          data: JSON.stringify(data),
          contentType: 'application/json',
          beforeSend: function(request) {

            $('#loaders-midtrans').html(`
                                <div class="d-flex justify-content-center align-items-center" style="width:100%;min-height:500px;">
                                  <svg
                                    class="container"
                                    viewBox="0 0 40 40"
                                    height="40"
                                    width="40">
                                    <circle
                                      class="track"
                                      cx="20"
                                      cy="20"
                                      r="17.5"
                                      pathlength="100"
                                      stroke-width="5px"
                                      fill="none" />
                                    <circle
                                      class="car"
                                      cx="20"
                                      cy="20"
                                      r="17.5"
                                      pathlength="100"
                                      stroke-width="5px"
                                      fill="none" />
                                  </svg>
                                </div>
                              `)
            $('[name="updateStock"]').prop('disabled', true);
            $('#see-invoice').prop('disabled', true);
          },
          success: function(response) {
            // $('#loaders-midtrans').html(`<iframe id="snap-midtrans" src="${response?.data?.redirect_url}" width="100%" height="500px"></iframe>`);
            const invoiceId = $('#invoicing').val();
            const invoiceUrl = `invoice?no=${invoiceId}`;
            const newTab = window.open(invoiceUrl, '_blank');
            if (response?.data?.redirect_url) {
              newTab.location.href = response.data.redirect_url;
            }
            newTab.focus();
            window.location.href = invoiceUrl;

            $('[name="updateStock"]').prop('disabled', false);
            $('#see-invoice').prop('disabled', false);
          },
          error: function(response) {
            $('[name="updateStock"]').prop('disabled', true);
            alert("Error: " + response.responseJSON.message);
          }
        });
      }
    });
  });
</script>

</html>

<script>
  // Aksi Select Status
  function myFunction() {
    var x = document.getElementById("mySelect").value;
    if (x === "1") {
      document.location.href = "beli-langsung?customer=<?= base64_encode(1); ?>";

    } else if (x === "2") {
      document.location.href = "beli-langsung?customer=<?= base64_encode(2); ?>";

    } else {
      document.location.href = "beli-langsung?customer=<?= base64_encode(0); ?>";
    }
  }

  // Change Customer
  $(function() {
    // bind change event to select
    $('#tipe_customer').on('change', function() {
      var url = $(this).val(); // get selected value
      url = btoa(url)
      if (url) { // require a URL
        document.location.href = "beli-langsung?customer=" + url; // redirect
      }
      return false;
    });
  });
</script>