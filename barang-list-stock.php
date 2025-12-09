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

	<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Data Stock Barang</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="bo">Home</a></li>
              <li class="breadcrumb-item active">Barang</li>
            </ol>
          </div>
           <a href="barang" class="btn btn-primary">Kembali</a>
        </div>
      </div><!-- /.container-fluid -->
    </section>


    <?php  
     $qu = "SELECT * FROM barang WHERE barang_cabang = $sessionCabang ORDER BY barang_id DESC";
    $data = query($qu);
    	// $data = query("SELECT * FROM barang ORDER BY barang_id DESC");
    ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-12">

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Data stock barang Keseluruhan</h3>
            </div>
            
            
            <!-- /.card-header -->
            <div class="card-body">
              <div class="table-auto">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th style="width: 6%;">No.</th>
                    <th style="width: 13%;">Kode Barang</th>
                    <th>Nama</th>
                    <th>Total Penjualan</th>
                    <th>Kode Suplier</th>
                    <th>Stock Gudang</th>
                    <th>Stock Dukun</th>
                    <th>Stock PP Srumbung</th>
                    <th>Stock Pakis</th>
                    <th>Stock Tegalrejo</th>
                  </tr>
                  </thead>
                  <tbody>

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
  </div>
</div>


<script>
$(document).ready(function(){
    var table = $('#example1').DataTable({ 
        "processing": true,
        "serverSide": true,
        "ajax": "barang-data-list-stock.php?cabang=<?= $sessionCabang; ?>",
        "columns": [
            { "data": 0 }, // No
            { "data": 1 }, // Kode Barang
            { "data": 2 }, // Nama
            { "data": 3 },
            { "data": 4 }, // Nama
            { "data": 5 }, // Stock Gudang
            { "data": 6 }, // Stock Dukun
            { "data": 7 },
            { "data": 8 },
            { "data": 9 }// Stock PP Srumbung
        ],
        "columnDefs": [{
            "targets": [3, 4, 5],
            "className": 'text-center'
        }]
    });

    table.on('draw.dt', function () {
        var info = table.page.info();
        table.column(0, { search: 'applied', order: 'applied', page: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + info.start;
        });
    });
});
  </script>
  
<script>
// Toast function
function showToast(message, type) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336'; // Green for success, red for error
    toast.className = "toast show";
    setTimeout(() => { 
        toast.className = toast.className.replace("show", ""); 
        if (type === 'success') {
            location.reload(); // Refresh halaman jika sukses
        }
    }, 3000);
}

// Import button click event
document.getElementById('importButton').addEventListener('click', () => {
    const fileInput = document.getElementById('excelFileInput');
    fileInput.click(); // Trigger file input dialog
});

// Handle file selection
document.getElementById('excelFileInput').addEventListener('change', (event) => {
    const formData = new FormData(document.getElementById('importForm'));

    // Nonaktifkan tombol untuk mencegah pemrosesan ulang
    const importButton = document.getElementById('importButton');
    importButton.disabled = true;

    fetch('import/import-barang.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json()) // Pastikan response adalah JSON
    .then(data => {
        console.log(data);  // Debugging response dari server
        if (data.success) {
            showToast(data.message, 'success'); // Tampilkan toast sukses
        } else {
            showToast(data.message, 'error'); // Tampilkan toast error
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat mengimpor data.', 'error');
    })
    .finally(() => {
        // Aktifkan kembali tombol setelah request selesai
        importButton.disabled = false;
    });
});
</script>

<style>
    .toast {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 5px;
        padding: 10px;
        position: fixed;
        z-index: 1;
        left: 50%;
        bottom: 30px;
        font-size: 17px;
    }
    .toast.show {
        visibility: visible;
        animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }
    @keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
    }
    @keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
    }
</style>

<?php include '_footer.php'; ?>

<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<script>
  $(function () {
    $("#example1").DataTable();
  });

  $(".delete-data").click(function(){
    alert("Data tidak bisa dihapus karena masih ada di data Invoice");
  });
</script>
</body>
</html>