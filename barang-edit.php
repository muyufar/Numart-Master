    <?php 
  include '_header.php';
  include '_nav.php';
  include '_sidebar.php'; 
  error_reporting(0);
?>
<?php  
  if ( $levelLogin === "kasir" && $levelLogin === "kurir" ) {
    echo "
      <script>
        document.location.href = 'bo';
      </script>
    ";
  }  
?>
<?php  
// ambil data di URL
$id = abs((int)base64_decode($_GET['id']));

// query data mahasiswa berdasarkan id
$barang = query("SELECT * FROM barang WHERE barang_id = $id ")[0];

// cek apakah tombol submit sudah ditekan atau belum
if( isset($_POST["submit"]) ){
  // var_dump($_POST);

  // cek apakah data berhasil di tambahkan atau tidak
  if ($sessionCabang == 0){
      if( editBarang($_POST) > 0 ) {
        echo "
          <script>
            document.location.href = 'barang';
          </script>
        ";
      } else {
        echo "
          <script>
            alert('data gagal ditambahkan');
          </script>
        ";
      }
  } else {
      if( editBarangCabang($_POST) > 0 ) {
        echo "
          <script>
            document.location.href = 'barang';
          </script>
        ";
      } else {
        echo "
          <script>
            alert('data gagal ditambahkan');
          </script>
        ";
      }
  }
}
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Data Barang</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="bo">Home</a></li>
              <li class="breadcrumb-item active">Edit Barang</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>


    <?php $isReadOnly = ($sessionCabang != 0) ? 'readonly' : ''; ?>
    <section class="content">
      <div class="container-fluid">
        <form role="form" action="" method="post">
        <div class="row">
          <!-- left column -->
            <div class="col-md-12">
              <!-- general form elements -->
              <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Data Barang</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6 col-lg-6">
                        <input type="hidden" name="barang_id" value="<?= $barang['barang_id']; ?>">
                        <div class="form-group">
                          <label for="barang_kode">Barcode / Kode Barang</label>
                          <input type="text" name="barang_kode" class="form-control" id="barang_kode" value="<?= $barang['barang_kode']; ?>" <?= $isReadOnly; ?> required>
                        </div>
                      </div>
                      <div class="col-md-6 col-lg-6"></div>
                      <div class="col-md-6 col-lg-6">
                          <div class="form-group">
                            <label for="barang_nama">Nama Barang</label>
                            <input type="text" name="barang_nama" class="form-control" id="barang_nama" value="<?= $barang['barang_nama']; ?>" <?= $isReadOnly; ?> required>
                          </div>
                        <div class="form-group">
                            <label for="kode_suplier">Kode Suplier</label>
                            <input type="text" name="kode_suplier" class="form-control" id="kode_suplier" value="<?= $barang['kode_suplier']; ?>" <?= $isReadOnly; ?> required>
                          </div>
                          <div class="form-group">
                            <label for="barang_deskripsi">Deskripsi</label>
                            <textarea name="barang_deskripsi" id="barang_deskripsi" class="form-control" rows="5" <?= $isReadOnly; ?> required="required"><?= $barang['barang_deskripsi']; ?></textarea>
                          </div>
                          <div class="form-group ">
                              <label for="kategori_id" class="">Kategori</label>
                              <div class="">
                                <select name="kategori_id" <?= $isReadOnly; ?> required="" class="form-control ">
                                  <?php  
                                      $kategori = $barang['kategori_id'];
                                      $kategoriParent = mysqli_query( $conn, "select kategori_nama from kategori where kategori_id = ".$kategori." && kategori_status > 0 && kategori_cabang = 0 ");
                                      $kn = mysqli_fetch_array($kategoriParent); 
                                      $nKn = $kn['kategori_nama'];
                                  ?>

                                    <option value="<?= $kategori; ?>"><?= $nKn; ?></option>

                                    <?php $data = query("SELECT * FROM kategori WHERE  kategori_status > 0 && kategori_cabang = 0 ORDER BY kategori_id DESC"); ?>
                                    <?php foreach ( $data as $row ) : ?>
                                      <?php if ( $row['kategori_status'] === '1' ) { ?>
                                      <?php if ( $row['kategori_id'] !== $barang['kategori_id'] ) { ?>
                                        <option value="<?= $row['kategori_id']; ?>">
                                          <?= $row['kategori_nama']; ?> 
                                        </option>
                                      <?php } ?>
                                      <?php } ?>
                                    <?php endforeach; ?>
                                </select>
                              </div>
                          </div>
                      </div>

                      <div class="col-md-6 col-lg-6">
                          <div class="form-group ">
                            <label for="barang_option_sn">SN or Non-SN</label>
                            <div class="">
                              <?php  
                                if ( $barang['barang_option_sn'] === '1' ) {
                                  $sn = "SN";
                                } else {
                                  $sn = "Non-SN";
                                }
                              ?>
                                <select name="barang_option_sn" <?= $isReadOnly; ?> required="" id="barang_option_sn" class="form-control stock-pilihan">
                                        <option value="<?= $barang['barang_option_sn']; ?>">
                                          <?= $sn; ?>
                                        </option>
                                        <?php  
                                          if ( $barang['barang_option_sn'] === '1' ) {
                                            echo '
                                              <option >Non-SN</option>
                                            ';
                                          } else {
                                            echo '
                                              <option value="1">SN</option>
                                            ';
                                          }
                                        ?>
                                  </select>
                              </div>
                              <small style="color: red">
                                    <b>
                                        SN (Serial Number) Hanya dikhususkan Untuk Produk yang memiliki No. SN Seperti Handphone & Laptop 
                                    </b>
                                </small>
                          </div>
                          <div class="form-group">
                            <label for="barang_stock">Stock</label>
                            <input type="number" name="barang_stock" <?= $isReadOnly; ?> class="form-control" id="barang_stock" value="<?= $barang['barang_stock']; ?>" required>
                          </div>
                          
                          <div class="form-group ">
                            <label for="barang_option_konsi">Barang Titipan (Konsi)</label>
                            <div class="">
                              <?php  
                                if ( $barang['barang_konsi'] === '1' ) {
                                  $konsi = "Barang Titipan";
                                } else {
                                  $konsi = "Bukan Barang Titipan";
                                }
                              ?>
                                <select name="barang_option_konsi" <?= $isReadOnly; ?> required="" id="barang_option_konsi" class="form-control stock-pilihan">
                                    <!-- Opsi sebelumnya tetap terpilih jika tidak diubah -->
                                    <option value="1" <?= $barang['barang_konsi'] === '1' ? 'selected' : ''; ?>>Barang Titipan</option>
                                    <option value="0" <?= $barang['barang_konsi'] === '0' ? 'selected' : ''; ?>>Bukan Barang Titipan</option>
                                </select>
                            </div>
                              
                      </div>
                    </div>
                  </div>
              </div>

              <div class="card card-default">
                <div class="card-header">
                  <h3 class="card-title">Data Satuan</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                  <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-6">
                          <div class="form-group ">
                              <label for="satuan_id">Satuan 1 (Utama)</label>
                              <div class="">
                                  <select name="satuan_id" <?= $isReadOnly; ?> required="" class="form-control ">
                                  <?php  
                                    $satuan = $barang['satuan_id'];
                                    $satuanParent = mysqli_query( $conn, "select satuan_nama from satuan where satuan_id = ".$satuan." && satuan_status > 0 && satuan_cabang = 0 ");
                                    $sn = mysqli_fetch_array($satuanParent); 
                                    $nSn = $sn['satuan_nama'];
                                  ?>

                                    <option value="<?= $satuan; ?>"><?= $nSn; ?></option>

                                    <?php $data1 = query("SELECT * FROM satuan WHERE satuan_status > 0 && satuan_cabang = 0 ORDER BY satuan_id DESC"); ?>
                                    <?php foreach ( $data1 as $row ) : ?>
                                      <?php if ( $row['satuan_status'] === '1' ) { ?>
                                      <?php if ( $row['satuan_id'] !== $barang['satuan_id'] ) { ?>
                                        <option value="<?= $row['satuan_id']; ?>">
                                          <?= $row['satuan_nama']; ?> 
                                        </option>
                                      <?php } ?>
                                      <?php } ?>
                                    <?php endforeach; ?>
                                </select>
                              </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6"></div>

                        <div class="col-md-6 col-lg-6">
                          <div class="form-group ">
                              <label for="satuan_id">Satuan 2</label>
                              <div class="">
                                  <select name="satuan_id_2" <?= $isReadOnly; ?> class="form-control ">
                                  <?php  
                                    $satuan = $barang['satuan_id_2'];
                                    $satuanParent = mysqli_query( $conn, "select satuan_nama from satuan where satuan_id = ".$satuan." && satuan_status > 0 && satuan_cabang = 0 ");
                                    $sn = mysqli_fetch_array($satuanParent); 
                                    $nSn = $sn['satuan_nama'];
                                  ?>

                                    <option value="<?= $satuan; ?>"><?= $nSn; ?></option>

                                    <?php $data1 = query("SELECT * FROM satuan WHERE satuan_status > 0 && satuan_cabang = 0 ORDER BY satuan_id DESC"); ?>
                                    <?php foreach ( $data1 as $row ) : ?>
                                      <?php if ( $row['satuan_status'] === '1' ) { ?>
                                      <?php if ( $row['satuan_id'] !== $satuan ) { ?>
                                        <option value="<?= $row['satuan_id']; ?>">
                                          <?= $row['satuan_nama']; ?> 
                                        </option>
                                      <?php } ?>
                                      <?php } ?>
                                    <?php endforeach; ?>
                                </select>
                              </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                          <div class="form-group">
                            <label for="barang_nama">Isi</label>
                            <input type="number" name="satuan_isi_2" <?= $isReadOnly; ?> class="form-control" id="barang_nama" value="<?= $barang['satuan_isi_2']; ?>" placeholder="Konversi dari satuan utama">
                          </div>
                        </div>

                        <div class="col-md-6 col-lg-6">
                          <div class="form-group ">
                              <label for="satuan_id">Satuan 3</label>
                              <div class="">
                                  <select name="satuan_id_3" <?= $isReadOnly; ?> class="form-control ">
                                  <?php  
                                    $satuan = $barang['satuan_id_3'];
                                    $satuanParent = mysqli_query( $conn, "select satuan_nama from satuan where satuan_id = ".$satuan." && satuan_status > 0 && satuan_cabang = 0 ");
                                    $sn = mysqli_fetch_array($satuanParent); 
                                    $nSn = $sn['satuan_nama'];
                                  ?>

                                    <option value="<?= $satuan; ?>"><?= $nSn; ?></option>

                                    <?php $data1 = query("SELECT * FROM satuan WHERE satuan_status > 0 && satuan_cabang = 0 ORDER BY satuan_id DESC"); ?>
                                    <?php foreach ( $data1 as $row ) : ?>
                                      <?php if ( $row['satuan_status'] === '1' ) { ?>
                                      <?php if ( $row['satuan_id'] !== $satuan ) { ?>
                                        <option value="<?= $row['satuan_id']; ?>">
                                          <?= $row['satuan_nama']; ?> 
                                        </option>
                                      <?php } ?>
                                      <?php } ?>
                                    <?php endforeach; ?>
                                </select>
                              </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6">
                          <div class="form-group">
                            <label for="barang_nama">Isi</label>
                            <input type="number" name="satuan_isi_3" <?= $isReadOnly; ?> class="form-control" id="barang_nama" value="<?= $barang['satuan_isi_3']; ?>" placeholder="Konversi dari satuan utama">
                          </div>
                        </div>

                    </div>
                  </div>
                  <!-- /.card-body -->
              </div>

              <div class="card card-default">
                <div class="card-header">
                  <h3 class="card-title">Data Harga</h3>
                </div>
                  <!--<div class="card-body">-->
             <div class="col-md-6 col-lg-6">
    <div class="form-group">
        <label for="barang_harga_beli">Harga Beli</label> 
        <input 
            type="text" 
            name="barang_harga_beli" 
            class="form-control" 
            id="barang_harga" 
            <?= $isReadOnly; ?>
            placeholder="Input Harga Beli Barang" 
            value="<?= isset($barang['barang_harga_beli']) ? $barang['barang_harga_beli'] : 0; ?>" <?= $isReadOnly; ?>
            required>
    </div>
</div>

                <!-- /.card-header -->
                <!-- form start -->
                  <div class="card-body">
                    <div class="table-auto">
                      <table class="table table-bordered">
                          <thead>
                            <tr>
                                <th>Level Harga</th>
                                <th>Satuan 1</th>
                                <th>Satuan 2</th>
                                <th>Satuan 3</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                                <th>Harga Umum</th>
                                <td>
                                  <input type="text" name="barang_harga" class="form-control" id="barang_harga" placeholder="Input Harga Barang" value="<?= $barang['barang_harga']; ?>" <?= $isReadOnly; ?> required="">
                                </td>
                                <td>
                                  <input type="text" name="barang_harga_s2" class="form-control" id="barang_harga_s2" placeholder="Input Harga Barang" value="<?= $barang['barang_harga_s2']; ?>" <?= $isReadOnly; ?>>
                                </td>
                                <td>
                                  <input type="text" name="barang_harga_s3" class="form-control" id="barang_harga_s3" placeholder="Input Harga Barang" value="<?= $barang['barang_harga_s3']; ?>" <?= $isReadOnly; ?>>
                                </td>
                            </tr>
                            <!--onkeypress="return hanyaAngka(event)"-->
                            <tr>
                                <th>Harga Member Retail</th>
                                <td>
                                  <input type="text" name="barang_harga_grosir_1" class="form-control" id="barang_harga_grosir_1" placeholder="Input Harga Barang"  value="<?= $barang['barang_harga_grosir_1']; ?>" <?= $isReadOnly; ?>>
                                </td>
                                <td>
                                  <input type="text" name="barang_harga_grosir_1_s2" class="form-control" id="barang_harga_grosir_1_s2" placeholder="Input Harga Barang" value="<?= $barang['barang_harga_grosir_1_s2']; ?>" <?= $isReadOnly; ?>>
                                </td>
                                <td>
                                  <input type="text" name="barang_harga_grosir_1_s3" class="form-control" id="barang_harga_grosir_1_s3" placeholder="Input Harga Barang" value="<?= $barang['barang_harga_grosir_1_s3']; ?>" <?= $isReadOnly; ?>>
                                </td>
                            </tr>
                            <tr>
                                <th>Harga Grosir</th>
                                <td>
                                  <input type="text" name="barang_harga_grosir_2" class="form-control" id="barang_harga_grosir_2" placeholder="Input Harga Barang" value="<?= $barang['barang_harga_grosir_2']; ?>" <?= $isReadOnly; ?>>
                                </td>
                                <td>
                                  <input type="text" name="barang_harga_grosir_2_s2" class="form-control" id="barang_harga_grosir_2_s2" placeholder="Input Harga Barang" value="<?= $barang['barang_harga_grosir_2_s2']; ?>" <?= $isReadOnly; ?>>
                                </td>
                                <td>
                                  <input type="text" name="barang_harga_grosir_2_s3" class="form-control" id="barang_harga_grosir_2_s3" placeholder="Input Harga Barang" value="<?= $barang['barang_harga_grosir_2_s3']; ?>" <?= $isReadOnly; ?>>
                                </td>
                            </tr>
                          </tbody>
                      </table>    
                    </div>

                    
                    <!--<br>-->
                    <!--<div class="row">-->
                    <!--    <div class="col-md-6 col-lg-6">-->
                    <!--        <div class="form-group">-->
                    <!--          <label for="barang_harga_beli">Harga Beli</label> -->
                    <!--          <input type="text" name="barang_harga_beli" class="form-control" id="barang_harga" value="<?= $barang['barang_harga_beli']; ?>" readonly>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->
                
                  </div>
                  <!-- /.card-body -->

                  <div class="card-footer text-right">
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                  </div>
              </div>
            </div>
        </div>
        </form>
      </div>
    </section>


  </div>


<?php include '_footer.php'; ?>
<script>
    function hanyaAngka(evt) {
      var charCode = (evt.which) ? evt.which : event.keyCode
       if (charCode > 31 && (charCode < 48 || charCode > 57))
 
        return false;
      return true;
    }
</script>

