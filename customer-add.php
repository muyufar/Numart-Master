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

// cek apakah tombol submit sudah ditekan atau belum
if( isset($_POST["submit"]) ){
  // var_dump($_POST);

  // cek apakah data berhasil di tambahkan atau tidak
  if( tambahCustomer($_POST) > 0 ) {
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
            <h1>Tambah Data Customer</h1>
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
                      <input type="hidden" name="customer_cabang" value="<?= $sessionCabang; ?>">
                        <div class="form-group">
                          <label for="customer_nama">Nama Lengkap</label>
                          <input type="text" name="customer_nama" class="form-control" id="customer_nama" placeholder="Enter Nama Lengkap" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_tlpn">No. WhatsApp</label>
                            <input type="number" name="customer_tlpn" class="form-control" id="customer_tlpn" placeholder="Contoh: 081234567890" required onkeypress="return hanyaAngka(event)">
                        </div>
                         <div class="form-group">
                          <label for="customer_email">Email (Tidak Wajib)</label>
                          <input type="email" name="customer_email" class="form-control" id="customer_email" placeholder="Enter email">
                        </div>
                        <div class="form-group ">
                          <label for="customer_category">Kelas Member</label>
                          <div class="">
                              <select name="customer_category" required="" class="form-control ">
                                  <option value="">-- Pilih --</option>
                                  <option value="0">Member Umum</option>
                                  <option value="1">Member Retail</option>
                                  <option value="2">Member Grosir</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-6">
                       <div class="form-group">
                            <label for="customer_alamat">Alamat</label>
                            <textarea name="customer_alamat" id="customer_alamat" class="form-control" required="required" placeholder="Alamat Lengkap" style="height:123px;"></textarea>
                        </div>
                        <div class="form-group ">
                          <label for="customer_status">Status</label>
                          <div class="">
                              <select name="customer_status" required="" class="form-control ">
                                  <option value="">-- Status --</option>
                                  <option value="1">Active</option>
                                  <option value="0">Not Active</option>
                                </select>
                            </div>
                            
                            
                        </div>
                        <div class="form-group">
                                <label for="customer_kartu">No Kartu</label>
                                <input type="teks" name="customer_kartu" class="form-control" id="customer_kartu" placeholder="Enter No kartu">
                            </div>
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