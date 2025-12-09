<?php 
  include '_header.php';
  include '_nav.php';
  include '_sidebar.php'; 
?>
<?php  
  if ( $levelLogin === "kurir") {
    echo "
      <script>
        document.location.href = 'bo';
      </script>
    ";
  }  
?>
<?php  
// ambil data di URL
$id = abs((int)$_GET['id']);


// query data mahasiswa berdasarkan id
$customer = query("SELECT * FROM customer WHERE customer_id = $id ")[0];

// cek apakah tombol submit sudah ditekan atau belum
if( isset($_POST["submit"]) ){
  // var_dump($_POST);

  // cek apakah data berhasil di tambahkan atau tidak
  if( editCustomer($_POST) > 0 ) {
    echo "
      <script>
        document.location.href = 'customer';
      </script>
    ";
  } else {
    echo "
      <script>
        alert('Data GAGAL Ditambahkan');
      </script>
    ";
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
            <h1>Edit Data Customer</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="bo">Home</a></li>
              <li class="breadcrumb-item active">Data Customer</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Data Customer</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              <form role="form" action="" method="post">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      <input type="hidden" name="customer_id" value="<?= $customer['customer_id']; ?>">
                        <div class="form-group">
                          <label for="customer_nama">Nama Lengkap</label>
                          <input type="text" name="customer_nama" class="form-control" id="customer_nama" value="<?= $customer['customer_nama']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_tlpn">No. WhatsApp</label>
                            <input type="text" name="customer_tlpn" class="form-control" id="customer_tlpn" value="<?= $customer['customer_tlpn']; ?>" required onkeypress="return hanyaAngka(event)">
                        </div>
                         <div class="form-group">
                          <label for="customer_email">Email (Tidak Wajib)</label>
                          <input type="email" name="customer_email" class="form-control" id="customer_email" value="<?= $customer['customer_email']; ?>">
                        </div>
                        <div class="form-group ">
                          <label for="customer_category">Kategori</label>
                          <div class="">
                              <?php  
                                  if ( $customer['customer_category'] == 1 ) {
                                    $customer_category = "Member Retail";
                                  } elseif ( $customer['customer_category'] == 2 ) {
                                    $customer_category = "Grosir";
                                  } else {
                                    $customer_category = "Umum";
                                  }
                                ?>
                              <select name="customer_category" required="" class="form-control ">
                                  <option value="<?= $customer['customer_category']; ?>"><?= $customer_category; ?></option>

                                  <?php if ( $customer['customer_category'] == 1 ) : ?>
                                    <option value="0">Umum</option>
                                    <option value="2">Grosir</option>
                                  <?php elseif ( $customer['customer_category'] == 2 ) : ?> 
                                    <option value="0">Umum</option>
                                    <option value="1">Member Retail</option>
                                  <?php else : ?>
                                    <option value="1">Member Retail</option>
                                    <option value="2">Grosir</option>
                                  <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-6">
                       <div class="form-group">
                            <label for="customer_alamat">Alamat</label>
                            <textarea name="customer_alamat" id="customer_alamat" class="form-control" required="required" placeholder="Alamat Lengkap" style="height:123px;"><?= $customer['customer_alamat']; ?></textarea>
                        </div>
                        <div class="form-group ">
                          <label for="customer_status">Status</label>
                          <div class="">
                                <?php  
                                  if ( $customer['customer_status'] === "1" ) {
                                    $status = "Active";
                                  } else {
                                    $status = "Not Active";
                                  }
                                ?>
                                  <select name="customer_status" required="" class="form-control ">
                                    <option value="<?= $customer['customer_status']; ?>"><?= $status; ?></option>
                                    <?php  
                                      if ( $customer['customer_status'] === "1" ) {
                                        echo '
                                          <option value="0">Not Active</option>
                                        ';
                                      } else {
                                        echo '
                                          <option value="1">Active</option>
                                        ';
                                      }
                                    ?>
                                  </select>
                              </div>
                        </div>
                         <div class="form-group">
                            <label for="customer_kartu">No Kartu</label>
                            <input type="text" name="customer_kartu" class="form-control" id="customer_kartu" value="<?= $customer['customer_kartu']; ?>" >
                        </div>
                        
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer text-right">
                  <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
          </div>
        </div>
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