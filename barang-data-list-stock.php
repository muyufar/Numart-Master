<?php 
include 'aksi/koneksi.php';
$cabang = $_GET['cabang'];

// Database connection info 
$dbDetails = array( 
    'host' => $servername, 
    'user' => $username, 
    'pass' => $password, 
    'db'   => $db
); 

// DB table to use 
$table = <<<EOT
 (
    SELECT 
      a.barang_kode,
      a.barang_nama,
      SUM(a.barang_terjual) AS barang_terjual,
      a.kode_suplier,

      SUM(CASE WHEN a.barang_cabang = 0 THEN a.barang_stock ELSE 0 END) AS stockGudang,
      SUM(CASE WHEN a.barang_cabang = 1 THEN a.barang_stock ELSE 0 END) AS stockDukun,
      SUM(CASE WHEN a.barang_cabang = 3 THEN a.barang_stock ELSE 0 END) AS stockPPSrumbung,
      SUM(CASE WHEN a.barang_cabang = 2 THEN a.barang_stock ELSE 0 END) AS stockPakis,
      SUM(CASE WHEN a.barang_cabang = 5 THEN a.barang_stock ELSE 0 END) AS stockTegalrejo

    FROM barang a
    WHERE a.barang_status = '1'
    GROUP BY a.barang_kode
    ORDER BY a.barang_id ASC
 ) temp
EOT;

// Table's primary key 
$primaryKey = 'barang_kode'; // karena kita group by kode

// Array of database columns which should be read and sent back to DataTables.
$columns = array( 
    array('db' => 'barang_kode',           'dt' => 0), // No. (akan diisi otomatis via JS)
    array('db' => 'barang_kode',           'dt' => 1), // Kode Barang
    array('db' => 'barang_nama',           'dt' => 2), // Nama
    array('db' => 'barang_terjual',        'dt' => 3), // penjualan
    array('db' => 'kode_suplier',           'dt' => 4), // suplier
    array('db' => 'stockGudang',           'dt' => 5), // Stock Gudang
    array('db' => 'stockDukun',            'dt' => 6), // Stock Dukun
    array('db' => 'stockPPSrumbung',       'dt' => 7),
    array('db' => 'stockPakis',             'dt' => 8),
    array('db' => 'stockTegalrejo',             'dt' =>9),// Stock PP Srumbung
); 

// Include SQL query processing class 
require 'aksi/ssp.php'; 

// Output data as json format 
echo json_encode(
    SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
);