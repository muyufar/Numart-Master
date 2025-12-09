<?php
header('Content-Type: application/json');
include '../aksi/koneksi.php';

// Cek session
session_start();
if (!isset($_SESSION['user_level'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Buat tabel jika belum ada
createTableIfNotExists();

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function createTableIfNotExists() {
    global $conn;
    
    $sql = "CREATE TABLE IF NOT EXISTS `transaksi_mapping` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `cabang` int(11) NOT NULL,
        `jenis_transaksi` varchar(50) NOT NULL,
        `akun_debit` int(11) DEFAULT NULL,
        `akun_kredit` int(11) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_cabang_jenis` (`cabang`, `jenis_transaksi`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    mysqli_query($conn, $sql);
}

function handleGet() {
    global $conn;
    
    $cabang = isset($_GET['cabang']) ? (int)$_GET['cabang'] : null;
    $jenis = isset($_GET['jenis_transaksi']) ? mysqli_real_escape_string($conn, $_GET['jenis_transaksi']) : null;
    
    $query = "SELECT tm.*, 
                     lkd.name as akun_debit_nama, lkd.kode_akun as akun_debit_kode,
                     lkk.name as akun_kredit_nama, lkk.kode_akun as akun_kredit_kode
              FROM transaksi_mapping tm
              LEFT JOIN laba_kategori lkd ON tm.akun_debit = lkd.id
              LEFT JOIN laba_kategori lkk ON tm.akun_kredit = lkk.id
              WHERE 1=1";
    
    if ($cabang !== null) {
        $query .= " AND tm.cabang = $cabang";
    }
    
    if ($jenis) {
        $query .= " AND tm.jenis_transaksi = '$jenis'";
    }
    
    $query .= " ORDER BY tm.jenis_transaksi";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Query error: ' . mysqli_error($conn)]);
        return;
    }
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'success',
        'data' => $data
    ]);
}

function handlePost() {
    global $conn;
    
    // Cek apakah ini update transaksi langsung
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update_transaksi') {
        handleUpdateTransaksi();
        return;
    }
    
    // Get form data untuk mapping jenis transaksi
    $cabang = isset($_POST['cabang']) ? (int)$_POST['cabang'] : null;
    $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : [];
    
    if ($cabang === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cabang harus diisi']);
        return;
    }
    
    if (empty($mapping)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data mapping tidak ditemukan']);
        return;
    }
    
    $successCount = 0;
    $errors = [];
    
    foreach ($mapping as $jenis => $data) {
        $jenis_escaped = mysqli_real_escape_string($conn, $jenis);
        $akun_debit = !empty($data['akun_debit']) ? (int)$data['akun_debit'] : 'NULL';
        $akun_kredit = !empty($data['akun_kredit']) ? (int)$data['akun_kredit'] : 'NULL';
        
        // Skip jika kedua akun kosong
        if ($akun_debit === 'NULL' && $akun_kredit === 'NULL') {
            // Hapus mapping jika ada
            $deleteQuery = "DELETE FROM transaksi_mapping 
                           WHERE cabang = $cabang AND jenis_transaksi = '$jenis_escaped'";
            mysqli_query($conn, $deleteQuery);
            continue;
        }
        
        // Cek apakah sudah ada
        $checkQuery = "SELECT id FROM transaksi_mapping 
                       WHERE cabang = $cabang AND jenis_transaksi = '$jenis_escaped'";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            // Update
            $updateQuery = "UPDATE transaksi_mapping 
                           SET akun_debit = $akun_debit, 
                               akun_kredit = $akun_kredit,
                               updated_at = NOW()
                           WHERE cabang = $cabang AND jenis_transaksi = '$jenis_escaped'";
            
            if (mysqli_query($conn, $updateQuery)) {
                $successCount++;
            } else {
                $errors[] = "Gagal update $jenis: " . mysqli_error($conn);
            }
        } else {
            // Insert
            $insertQuery = "INSERT INTO transaksi_mapping (cabang, jenis_transaksi, akun_debit, akun_kredit)
                           VALUES ($cabang, '$jenis_escaped', $akun_debit, $akun_kredit)";
            
            if (mysqli_query($conn, $insertQuery)) {
                $successCount++;
            } else {
                $errors[] = "Gagal insert $jenis: " . mysqli_error($conn);
            }
        }
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Beberapa mapping gagal disimpan',
            'errors' => $errors,
            'success_count' => $successCount
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Berhasil menyimpan $successCount konfigurasi mapping"
    ]);
}

function handleUpdateTransaksi() {
    global $conn;
    
    // ID adalah varchar/string (UUID), bukan integer
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : null;
    $akun_debit = isset($_POST['akun_debit']) && $_POST['akun_debit'] !== '' ? (int)$_POST['akun_debit'] : null;
    $akun_kredit = isset($_POST['akun_kredit']) && $_POST['akun_kredit'] !== '' ? (int)$_POST['akun_kredit'] : null;
    
    if (!$id || $id === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID transaksi harus diisi']);
        return;
    }
    
    // Cek dulu apakah ID ada di database
    $checkQuery = "SELECT id, akun_debit, akun_kredit, kategori FROM laba WHERE id = '$id'";
    $checkResult = mysqli_query($conn, $checkQuery);
    $existingData = mysqli_fetch_assoc($checkResult);
    
    if (!$existingData) {
        echo json_encode([
            'success' => false, 
            'message' => 'ID tidak ditemukan di database',
            'id_dicari' => $id
        ]);
        return;
    }
    
    // Update tabel laba
    $updateFields = [];
    
    if ($akun_debit !== null) {
        $updateFields[] = "akun_debit = $akun_debit";
        // Juga update kategori untuk backward compatibility
        $updateFields[] = "kategori = '$akun_debit'";
    }
    
    if ($akun_kredit !== null) {
        $updateFields[] = "akun_kredit = $akun_kredit";
    }
    
    if (empty($updateFields)) {
        echo json_encode(['success' => true, 'message' => 'Tidak ada perubahan']);
        return;
    }
    
    // ID adalah string (UUID), gunakan quotes
    $updateQuery = "UPDATE laba SET " . implode(', ', $updateFields) . " WHERE id = '$id'";
    
    if (mysqli_query($conn, $updateQuery)) {
        $affectedRows = mysqli_affected_rows($conn);
        
        // Verifikasi dengan SELECT ulang
        $verifyQuery = "SELECT id, akun_debit, akun_kredit, kategori FROM laba WHERE id = '$id'";
        $verifyResult = mysqli_query($conn, $verifyQuery);
        $newData = mysqli_fetch_assoc($verifyResult);
        
        echo json_encode([
            'success' => true,
            'message' => 'Transaksi berhasil diupdate',
            'affected_rows' => $affectedRows,
            'query' => $updateQuery,
            'data_sebelum' => $existingData,
            'data_sesudah' => $newData
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update transaksi: ' . mysqli_error($conn),
            'query' => $updateQuery
        ]);
    }
}

function handleDelete() {
    global $conn;
    
    $cabang = isset($_GET['cabang']) ? (int)$_GET['cabang'] : null;
    
    if ($cabang === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cabang harus diisi']);
        return;
    }
    
    $query = "DELETE FROM transaksi_mapping WHERE cabang = $cabang";
    
    if (mysqli_query($conn, $query)) {
        $affectedRows = mysqli_affected_rows($conn);
        echo json_encode([
            'success' => true,
            'message' => "Berhasil menghapus $affectedRows konfigurasi mapping"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus mapping: ' . mysqli_error($conn)
        ]);
    }
}

