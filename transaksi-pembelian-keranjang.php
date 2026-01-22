<?php 
  error_reporting(0);
  include '_header-artibut.php';

  // Ambil data berdasarkan tipe cash atau hutang
  $r = $_GET['r'];
?>

	<?php  
      $userId = $_SESSION['user_id'];
      $keranjang = query("SELECT * FROM keranjang_pembelian WHERE keranjang_id_kasir = $userId && keranjang_cabang = $sessionCabang ORDER BY keranjang_id ASC");

      $pembelian = mysqli_query($conn,"select * from invoice_pembelian");
      $jmlPembelian = mysqli_num_rows($pembelian);
      $jmlPembelian1 = $jmlPembelian + 1;
    ?>
    <?php  
        $today = date("Ymd");
        $di = $today.$jmlPembelian1;
    ?>

    <!-- Mencari Nilai no Invoice -->
    <?php  
        $userLogin = $_SESSION['user_id'];
        $invoiceNumber = mysqli_query($conn, "select invoice_pembelian_number_id, invoice_pembelian_number_input, invoice_pembelian_number_delete from invoice_pembelian_number where invoice_pembelian_number_parent = ".$di." && invoice_pembelian_number_user = ".$userLogin." && invoice_pembelian_cabang = ".$sessionCabang." ");
        $inParent = mysqli_fetch_array($invoiceNumber);
        $inId     = $inParent['invoice_pembelian_number_id'];
        $in       = $inParent['invoice_pembelian_number_input'];
        $inDelete = $inParent['invoice_pembelian_number_delete'];
        
        if ( $in == null ) {
          $in = 0;
        } else {
          $in = $in;
        }
    ?>
    <!-- End Mencari Nilai no Invoice -->
   
      <div class="card">
        <div class="card-header">
           <div class="row">
              <div class="col-md-8 col-lg-8">
                <div class="card-invoice">
                 <span>No. Invoice: </span>
                  <input type="" value="<?= $in; ?>" readonly="" style="border: 1px solid #eaeaea;">

                <?php if ( $in == null ) { ?>
                  <span class="" name="" data-toggle="modal" data-target="#modal-tambah-invoice">
                        <i class="fa fa-pencil" style="color: green; cursor: pointer;"></i>
                  </span>
                <?php } ?>

                <?php if ( $in != null ) { ?>
                  <span class="" name="" id="invoice_edit" data-id="<?= $inId; ?>">
                        <i class="fa fa-edit" style="color: blue; cursor: pointer;"></i>
                  </span>
                <?php } ?>

                 </div>
                </div>
                <div class="col-md-4 col-lg-4">
                  <div class="cari-barang-parent">
                    <div class="row">
                        <div class="col-10">
                            <form action="" method="post">
                                <input type="hidden" name="keranjang_id_kasir" value="<?= $_SESSION['user_id']; ?>">
                                <input type="hidden" name="keranjang_cabang" value="<?= $sessionCabang; ?>">
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

			     <div class="card-body">
              <div class="table-auto">
                <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th style="width: 6%;">No.</th>
                  <th>Nama</th>
                  <th>Harga Beli</th>
                  <th style="text-align: center;">QTY</th>
                  <th style="width: 20%;">Sub Total</th>
                  <th style="text-align: center;">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                  $i=1; 
                  $total = 0;
                ?>
                <?php 
                  foreach($keranjang as $row) : 

                  $bik = $row['barang_id'];
                  $stockParent = mysqli_query( $conn, "select barang_stock, barang_harga_beli from barang where barang_id = '".$bik."'");
                  $brg = mysqli_fetch_array($stockParent); 
                  $tb_brg = $brg['barang_stock'];
                      if ($row['keranjang_harga'] == 0) {
          mysqli_query($conn, "UPDATE keranjang_pembelian SET keranjang_harga = {$brg['barang_harga_beli']} WHERE keranjang_id = {$row['keranjang_id']}");
          $row['keranjang_harga'] = $brg['barang_harga_beli']; // Update nilai lokal
      }
                 $sub_total = $row['keranjang_harga'] * $row['keranjang_qty'];
        
                  if ( $row['keranjang_id_kasir'] === $_SESSION['user_id'] ) {
                  $total += $sub_total;
                ?>
               <tr>
    <td><?= $i; ?></td>
    <td><?= $row['keranjang_nama']; ?></td>
    <td>
        Rp. <?= number_format($row['keranjang_harga'], 0, ',', '.'); ?>
    </td>
    <td style="text-align: center; width: 11%;">
        <form role="form" action="" method="post">
            <input type="hidden" name="keranjang_id" value="<?= $row['keranjang_id']; ?>">
            <input type="number" min="1" name="keranjang_qty" value="<?= $row['keranjang_qty']; ?>" style="text-align: center; width: 60%;">
            <button class="btn btn-primary" type="submit" name="update">
                <i class="fa fa-refresh"></i>
            </button>
        </form>
    </td>
    <td>Rp. <?= number_format($sub_total, 0, ',', '.'); ?></td>
    <td style="text-align: center; width: 6%;">
        <a href="transaksi-pembelian-delete?id=<?= $row['keranjang_id']; ?>&r=<?= $r; ?>" title="Delete Data" onclick="return confirm('Yakin dihapus ?')">
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
                <form role="form" action="transaksi-pembelian.php" method="POST">
                  <div class="row">
                    <div class="col-md-6 col-lg-7">
                        <div class="filter-customer">
                          
                          <div class="form-group">
                            <label>Supplier</label>
                            <select class="form-control select2bs4" required="" name="invoice_supplier">
                              <option selected="selected" value="">-- Pilih Supplier --</option>
                              <?php  
                                $supplier = query("SELECT * FROM supplier WHERE supplier_cabang = $sessionCabang && supplier_status = '1' ORDER BY supplier_id DESC ");
                              ?>
                              <?php foreach ( $supplier as $ctr ) : ?>
                                <option value="<?= $ctr['supplier_id'] ?>">
                                	<?= $ctr['supplier_nama']; ?> - <?= $ctr['supplier_company']; ?>	
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <small>
                              <a href="supplier-add">Tambah Supplier <i class="fa fa-plus"></i></a>
                            </small>
                          </div>

                          <!-- kondisi jika memilih hutang -->
                          <?php if ( $r == 1 ) : ?>
                          <div class="form-group">
                              <label style="color: red;">Jatuh Tempo</label>
                              <input type="date" name="invoice_hutang_jatuh_tempo" class="form-control" raquired value="<?= date("Y-m-d"); ?>">
                          </div>
                         <?php else : ?>
                            <input type="hidden" name="invoice_hutang_jatuh_tempo" value="0">
                         <?php endif; ?>
                      </div>
                    </div>
                    <div class="col-md-6 col-lg-5">
                      <div class="invoice-table">
                        <table class="table">
                          <tr>
                              <td><b>Total</b></td>
                              <td class="table-nominal">
                                 <!-- Rp. <?php echo number_format($total, 0, ',', '.'); ?> -->
                                 <span>Rp. </span>
                                 <span>
                                    <input type="text" name="invoice_total" id="angka2" class="b2" onkeyup="hitung2();" value="<?= $total; ?>" onkeyup="return isNumberKey(event)" size="10" readonly>
                                 </span>
                                 
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <b style="color: red;">
                                      <?php  
                                        // kondisi jika memilih hutang
                                        if ( $r == 1 ) {
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
                                   <input type="text" name="angka1" id="angka1" class="a2" autocomplete="off" onkeyup="hitung2();" required="" onkeyup="return isNumberKey(event)" onkeypress="return hanyaAngka1(event)" size="10">
                                 </span>
                              </td>
                          </tr>
                          <tr>
                              <td>
                                  <?php  
                                    // kondisi jika memilih hutang
                                    if ( $r == 1 ) {
                                        echo "Sisa Hutang";
                                    } else {
                                        echo "Kembali";
                                    }
                                  ?>  
                              </td>
                              <td class="table-nominal">
                                <span>Rp.</span>
                                 <span>
                                  <input type="text" name="hasil" id="hasil" class="c2" readonly size="10" disabled>
                                </span>
                              </td>
                          </tr>
                          <tr>
                              <td></td>
                              <td>

                                <?php 
                                    foreach ($keranjang as $stk) : 
                                    if ( $stk['keranjang_id_kasir'] === $_SESSION['user_id'] ) {
                                ?>
                                  <input type="hidden" name="barang_ids[]" value="<?= $stk['barang_id']; ?>">
                                  <input type="hidden" min="1" name="keranjang_qty[]" value="<?= $stk['keranjang_qty'] ?>"> 
                                  <input type="hidden" name="keranjang_id_kasir[]" value="<?= $_SESSION['user_id']; ?>">

                                  <input type="hidden" name="kik" value="<?= $_SESSION['user_id']; ?>
                                  ">
                                  <input type="hidden" name="pembelian_invoice[]" value="<?= $in; ?>">
                                  <input type="hidden" name="pembelian_invoice_parent[]" value="<?= $inDelete; ?>">
                                  <input type="hidden" name="pembelian_date[]" value="<?= date("Y-m-d") ?>">
                                  <input type="hidden" name="barang_harga_beli[]" value="<?= $stk['keranjang_harga']; ?>">
                                  <input type="hidden" name="pembelian_cabang[]" value="<?= $sessionCabang; ?>">
                                <?php } ?>
                                <?php endforeach; ?>  
                                <input type="hidden" name="pembelian_invoice2" value="<?= $in; ?>">
                                <input type="hidden" name="invoice_pembelian_number_delete" value="<?= $inDelete; ?>">
                                <input type="hidden" name="pembelian_invoice_parent2" value="<?= $inDelete; ?>">
                                <input type="hidden" name="invoice_hutang" value="<?= $r; ?>">
                                <input type="hidden" name="invoice_hutang_lunas" value="0">
                                <input type="hidden" name="invoice_pembelian_cabang" value="<?= $sessionCabang; ?>">
                                <div class="payment">
                                  <?php  
                                  	 $idKasir = $_SESSION['user_id'];
                                  	 $keranjang = mysqli_query($conn,"select keranjang_harga from keranjang_pembelian where keranjang_harga < 1 && keranjang_id_kasir = ".$idKasir." && keranjang_cabang = ".$sessionCabang." ");
    								                  $jmlKeranjang = mysqli_num_rows($keranjang);
                                  ?>

                                <?php if ( $in != null ) { ?>
                                  <?php if ( $jmlKeranjang < 1 ) { ?>
                                  <button class="btn btn-primary" type="submit" name="updateStock">Simpan Pembelian <i class="fa fa-shopping-cart"></i></button>
                                  <?php } ?>

                                  <?php if ( $jmlKeranjang > 0 ) { ?>
                                  <a class="btn btn-default btn-disabled" type="" name="">Simpan Pembelian<i class="fa fa-shopping-cart"></i></a>
                                  <?php } ?>
                                <?php } ?>

                                <?php if ( $in == null ) { ?>
                                  <a class="btn btn-default" type="" name="" disabled>Simpan Pembelian <i class="fa fa-shopping-cart"></i></a>
                                <?php } ?>

                                </div>
                              </td>
                          </tr>
                        </table>
                      </div>
                    </div>
                  </div>
               </form>
              </div>
            </div>
          </div>

        

<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<script>
  $(function () {
    $("#example1").DataTable();
  });
</script>

<script>
  $(function () {

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
  });

  $(document).ready(function(){
  	 $('.btn-disabled').click(function(){
	  	alert("Harga Beli Masih ada yang bernilai kosong (Rp.0) !! Segera Update Harga Pembelian Barang per Produk ..");
	  });
  });
</script>

