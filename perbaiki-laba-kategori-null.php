<?php
/**
 * Script untuk memperbaiki data laba_kategori yang memiliki kode_akun dan name null
 * 
 * Script ini akan:
 * 1. Mencari data dengan kode_akun null atau empty dan name null atau empty
 * 2. Mencoba mengidentifikasi akun berdasarkan kategori, tipe_akun, dan saldo
 * 3. Merge atau update data tersebut ke akun yang sesuai
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '_header.php';
include '_nav.php';
include '_sidebar.php';

if ($levelLogin != "admin" && $levelLogin != "super admin") {
    echo "
        <script>
            document.location.href = 'bo';
        </script>
    ";
    exit;
}

include 'aksi/koneksi.php';

// Ambil data yang memiliki kode_akun null atau empty dan name null atau empty
$query_null = "SELECT * FROM laba_kategori WHERE (kode_akun IS NULL OR kode_akun = '' OR kode_akun = '-') AND (name IS NULL OR name = '' OR name = '-') AND saldo != 0";
$result_null = mysqli_query($conn, $query_null);

$data_null = [];
if ($result_null) {
    while ($row = mysqli_fetch_assoc($result_null)) {
        $data_null[] = $row;
    }
}

// Proses perbaikan jika ada POST
$message = '';
$success_count = 0;
$error_count = 0;

if (isset($_POST['perbaiki'])) {
    $ids_to_fix = $_POST['ids'] ?? [];
    
    foreach ($ids_to_fix as $id) {
        $id = intval($id);
        
        // Ambil data yang akan diperbaiki
        $query_data = "SELECT * FROM laba_kategori WHERE id = $id";
        $result_data = mysqli_query($conn, $query_data);
        
        if ($result_data && $row = mysqli_fetch_assoc($result_data)) {
            $saldo = floatval($row['saldo']);
            $kategori = $row['kategori'];
            $tipe_akun = $row['tipe_akun'];
            $cabang = isset($row['cabang']) ? intval($row['cabang']) : 0;
            
            // Tentukan kode_akun dan name berdasarkan kategori dan saldo
            $kode_akun = '';
            $name = '';
            
            if ($kategori == 'aktiva' && $tipe_akun == 'debit') {
                // Jika saldo positif, kemungkinan Kas Tunai
                // Jika saldo negatif, mungkin ada masalah atau pembelian
                if ($saldo > 0) {
                    $kode_akun = '1-1100';
                    $name = 'Kas Tunai';
                } else {
                    // Saldo negatif, mungkin dari pembelian cash
                    $kode_akun = '1-1100';
                    $name = 'Kas Tunai';
                }
            } else if ($kategori == 'pasiva' && $tipe_akun == 'kredit') {
                // Kemungkinan Hutang Dagang
                $kode_akun = '2-1100';
                $name = 'Hutang Dagang';
            }
            
            if ($kode_akun != '' && $name != '') {
                // Cek apakah sudah ada akun dengan kode_akun yang sama untuk cabang ini
                $check_query = "SELECT id, saldo FROM laba_kategori WHERE kode_akun = '$kode_akun'";
                if (isset($row['cabang'])) {
                    $check_query .= " AND (cabang = $cabang OR cabang = 0 OR cabang IS NULL)";
                }
                $check_query .= " AND id != $id LIMIT 1";
                
                $check_result = mysqli_query($conn, $check_query);
                
                if ($check_result && mysqli_num_rows($check_result) > 0) {
                    // Merge saldo ke akun yang sudah ada
                    $existing = mysqli_fetch_assoc($check_result);
                    $existing_id = intval($existing['id']);
                    $existing_saldo = floatval($existing['saldo']);
                    $new_saldo = $existing_saldo + $saldo;
                    
                    // Update saldo akun yang sudah ada
                    $update_query = "UPDATE laba_kategori SET saldo = $new_saldo WHERE id = $existing_id";
                    if (mysqli_query($conn, $update_query)) {
                        // Hapus data yang null
                        $delete_query = "DELETE FROM laba_kategori WHERE id = $id";
                        if (mysqli_query($conn, $delete_query)) {
                            $success_count++;
                            $message .= "✓ ID $id: Saldo digabungkan ke akun $kode_akun ($name).<br>";
                        } else {
                            $error_count++;
                            $message .= "✗ ID $id: Gagal menghapus data setelah merge.<br>";
                        }
                    } else {
                        $error_count++;
                        $message .= "✗ ID $id: Gagal update saldo akun yang sudah ada.<br>";
                    }
                } else {
                    // Update data yang null dengan kode_akun dan name
                    $update_query = "UPDATE laba_kategori SET kode_akun = '$kode_akun', name = '$name' WHERE id = $id";
                    if (mysqli_query($conn, $update_query)) {
                        $success_count++;
                        $message .= "✓ ID $id: Diperbaiki menjadi $kode_akun ($name).<br>";
                    } else {
                        $error_count++;
                        $message .= "✗ ID $id: Gagal update - " . mysqli_error($conn) . "<br>";
                    }
                }
            } else {
                $error_count++;
                $message .= "✗ ID $id: Tidak dapat menentukan kode_akun dan name (Kategori: $kategori, Tipe: $tipe_akun).<br>";
            }
        }
    }
    
    if ($success_count > 0 || $error_count > 0) {
        $message = "<div class='alert alert-" . ($error_count > 0 ? "warning" : "success") . "'>" . $message . "</div>";
    }
    
    // Refresh data
    $query_null = "SELECT * FROM laba_kategori WHERE (kode_akun IS NULL OR kode_akun = '' OR kode_akun = '-') AND (name IS NULL OR name = '' OR name = '-') AND saldo != 0";
    $result_null = mysqli_query($conn, $query_null);
    
    $data_null = [];
    if ($result_null) {
        while ($row = mysqli_fetch_assoc($result_null)) {
            $data_null[] = $row;
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
                    <h1>Perbaiki Data Laba Kategori (Null)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="bo">Home</a></li>
                        <li class="breadcrumb-item"><a href="laba-kategori">Laba Kategori</a></li>
                        <li class="breadcrumb-item active">Perbaiki Data Null</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data dengan Kode Akun dan Nama Kategori Null</h3>
                </div>
                <div class="card-body">
                    <?php if (count($data_null) > 0): ?>
                        <form method="POST" action="">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;">
                                                <input type="checkbox" id="select-all">
                                            </th>
                                            <th>ID</th>
                                            <th>Kode Akun</th>
                                            <th>Nama Kategori</th>
                                            <th>Kategori</th>
                                            <th>Tipe Akun</th>
                                            <th>Cabang</th>
                                            <th>Saldo</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data_null as $row): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="ids[]" value="<?php echo $row['id']; ?>">
                                                </td>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['kode_akun'] ?: '-'; ?></td>
                                                <td><?php echo $row['name'] ?: '-'; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $row['kategori'] == 'aktiva' ? 'success' : 'danger'; ?>">
                                                        <?php echo strtoupper($row['kategori']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?php echo strtoupper($row['tipe_akun']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo isset($row['cabang']) ? $row['cabang'] : '0'; ?></td>
                                                <td>
                                                    <span class="text-<?php echo $row['saldo'] >= 0 ? 'success' : 'danger'; ?>">
                                                        Rp <?php echo number_format($row['saldo'], 0, ',', '.'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $suggested_kode = '';
                                                    $suggested_name = '';
                                                    
                                                    if ($row['kategori'] == 'aktiva' && $row['tipe_akun'] == 'debit') {
                                                        $suggested_kode = '1-1100';
                                                        $suggested_name = 'Kas Tunai';
                                                    } else if ($row['kategori'] == 'pasiva' && $row['tipe_akun'] == 'kredit') {
                                                        $suggested_kode = '2-1100';
                                                        $suggested_name = 'Hutang Dagang';
                                                    }
                                                    
                                                    if ($suggested_kode) {
                                                        echo "<small class='text-muted'>Disarankan: <strong>$suggested_kode</strong> ($suggested_name)</small>";
                                                    } else {
                                                        echo "<small class='text-danger'>Tidak dapat ditentukan</small>";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="perbaiki" class="btn btn-primary" onclick="return confirm('Yakin ingin memperbaiki data yang dipilih?')">
                                    <i class="fa fa-wrench"></i> Perbaiki Data yang Dipilih
                                </button>
                                <a href="laba-kategori.php" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> Tidak ada data dengan kode akun dan nama kategori null yang memiliki saldo.
                        </div>
                        <a href="laba-kategori.php" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali ke Laba Kategori
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Select all checkbox
    document.getElementById('select-all').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = this.checked;
        }, this);
    });
</script>

<?php include '_footer.php'; ?>
