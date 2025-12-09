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

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function handleGet()
{
    global $conn;

    // Check if cabang column exists
    $cabang_column_exists = false;
    $check_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
    $column_result = mysqli_query($conn, $check_column);
    if ($column_result && mysqli_num_rows($column_result) > 0) {
        $cabang_column_exists = true;
    }

    // Get cabang from query parameter (explicit filter)
    // If cabang is provided in GET, use it (even if it's 0)
    // If not provided, check if we should use session cabang
    $cabang = null;
    if (isset($_GET['cabang']) && $_GET['cabang'] !== '') {
        // Explicit filter from user
        $cabang = (int)$_GET['cabang'];
    } else {
        // No explicit filter, use session cabang as default (if exists)
        $session_cabang = isset($_SESSION['user_cabang']) ? (int)$_SESSION['user_cabang'] : null;
        $cabang = $session_cabang;
    }

    // Get single item by ID
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $query = "SELECT * FROM laba_kategori WHERE id = $id";
        if ($cabang !== null && $cabang_column_exists) {
            // Include categories for this cabang OR global categories (cabang IS NULL) OR default cabang (cabang = 0)
            // Cabang 0 (PCNU) dianggap sebagai cabang default yang bisa digunakan semua cabang
            $query .= " AND (cabang = $cabang OR cabang IS NULL OR cabang = 0)";
        }
        $result = mysqli_query($conn, $query);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'success' => true,
                'message' => 'success',
                'data' => $row
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        }
        return;
    }

    // Get all categories filtered by cabang
    $query = "SELECT * FROM laba_kategori WHERE 1=1";

    // Filter by cabang if provided and column exists
    // IMPORTANT: Only filter if cabang is explicitly provided in GET parameter
    // If no cabang parameter, show all accounts (no filter)
    if (isset($_GET['cabang']) && $_GET['cabang'] !== '' && $cabang_column_exists) {
        $cabang_filter = (int)$_GET['cabang'];
        // Check if cabang is 0 (PCNU/Default) or specific cabang
        if ($cabang_filter == 0) {
            // If filter is cabang 0, show only cabang 0
            $query .= " AND cabang = 0";
        } else {
            // If filter is specific cabang, show ONLY that cabang (not including cabang 0 or NULL)
            // This ensures that when user selects a specific branch, they only see accounts from that branch
            $query .= " AND cabang = $cabang_filter";
        }
    } else if ($cabang !== null && $cabang_column_exists) {
        // Fallback: Use session cabang if no explicit filter (for backward compatibility)
        // But only if cabang is not 0 (to avoid showing all accounts)
        if ($cabang != 0) {
            $query .= " AND cabang = $cabang";
        }
    }
    
    // Filter by kategori if provided
    if (isset($_GET['kategori']) && $_GET['kategori'] !== '') {
        $kategori = mysqli_real_escape_string($conn, $_GET['kategori']);
        $query .= " AND kategori = '$kategori'";
    }
    
    // Filter by tipe_akun if provided
    if (isset($_GET['tipe_akun']) && $_GET['tipe_akun'] !== '') {
        $tipe_akun = mysqli_real_escape_string($conn, $_GET['tipe_akun']);
        $query .= " AND tipe_akun = '$tipe_akun'";
    }
    
    // Search by name or kode_akun if provided
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $search = mysqli_real_escape_string($conn, $_GET['search']);
        $query .= " AND (name LIKE '%$search%' OR kode_akun LIKE '%$search%')";
    }

    $query .= " ORDER BY kategori, name";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengambil data: ' . mysqli_error($conn)
        ]);
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

function handlePost()
{
    global $conn;

    // Get JSON data
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);

    if ($data === null || empty($data)) {
        $data = $_POST;
    }

    // Validate required fields
    $errors = [];
    if (!isset($data['name']) || trim($data['name']) === '') {
        $errors[] = 'Nama kategori harus diisi';
    }
    if (!isset($data['kategori']) || trim($data['kategori']) === '') {
        $errors[] = 'Kategori harus diisi';
    }
    if (!isset($data['tipe_akun']) || trim($data['tipe_akun']) === '') {
        $errors[] = 'Tipe akun harus diisi';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $errors
        ]);
        exit;
    }

    $name = mysqli_real_escape_string($conn, trim($data['name']));
    $kode_akun = isset($data['kode_akun']) ? mysqli_real_escape_string($conn, trim($data['kode_akun'])) : '';
    $kategori = mysqli_real_escape_string($conn, trim($data['kategori']));
    $tipe_akun = mysqli_real_escape_string($conn, trim($data['tipe_akun']));
    $saldo = isset($data['saldo']) ? floatval($data['saldo']) : 0;

    // Check if cabang column exists
    $cabang_column_exists = false;
    $check_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
    $column_result = mysqli_query($conn, $check_column);
    if ($column_result && mysqli_num_rows($column_result) > 0) {
        $cabang_column_exists = true;
    }

    // Get cabang from data or session
    $cabang = null;
    if (isset($data['cabang']) && $data['cabang'] !== '' && $data['cabang'] !== null) {
        $cabang = (int)$data['cabang'];
    } else if (isset($_SESSION['user_cabang'])) {
        $cabang = (int)$_SESSION['user_cabang'];
    }

    // Check if name already exists for this cabang
    $check_query = "SELECT id FROM laba_kategori WHERE name = '$name'";
    if ($cabang !== null && $cabang_column_exists) {
        $check_query .= " AND (cabang = $cabang OR cabang IS NULL)";
    }
    $check_result = mysqli_query($conn, $check_query);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nama kategori sudah ada untuk cabang ini'
        ]);
        exit;
    }

    // Build query with cabang (only if column exists)
    $query = "INSERT INTO laba_kategori (name, kode_akun, kategori, tipe_akun, saldo";
    $values = "VALUES ('$name', '$kode_akun', '$kategori', '$tipe_akun', $saldo";

    if ($cabang !== null && $cabang_column_exists) {
        $query .= ", cabang";
        $values .= ", $cabang";
    }

    $query .= ") " . $values . ")";

    if (mysqli_query($conn, $query)) {
        $id = mysqli_insert_id($conn);
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => ['id' => $id]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan data: ' . mysqli_error($conn)
        ]);
    }
}

function handlePut()
{
    global $conn;

    // Get JSON data
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);

    if ($data === null || empty($data)) {
        $data = $_POST;
    }

    // Validate required fields
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID harus diisi']);
        exit;
    }

    $errors = [];
    if (!isset($data['name']) || trim($data['name']) === '') {
        $errors[] = 'Nama kategori harus diisi';
    }
    if (!isset($data['kategori']) || trim($data['kategori']) === '') {
        $errors[] = 'Kategori harus diisi';
    }
    if (!isset($data['tipe_akun']) || trim($data['tipe_akun']) === '') {
        $errors[] = 'Tipe akun harus diisi';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $errors
        ]);
        exit;
    }

    $id = (int)$data['id'];
    $name = mysqli_real_escape_string($conn, trim($data['name']));
    $kode_akun = isset($data['kode_akun']) ? mysqli_real_escape_string($conn, trim($data['kode_akun'])) : '';
    $kategori = mysqli_real_escape_string($conn, trim($data['kategori']));
    $tipe_akun = mysqli_real_escape_string($conn, trim($data['tipe_akun']));
    $saldo = isset($data['saldo']) ? floatval($data['saldo']) : 0;

    // Check if cabang column exists
    $cabang_column_exists = false;
    $check_column = "SHOW COLUMNS FROM laba_kategori LIKE 'cabang'";
    $column_result = mysqli_query($conn, $check_column);
    if ($column_result && mysqli_num_rows($column_result) > 0) {
        $cabang_column_exists = true;
    }

    // Get cabang from data or session
    $cabang = null;
    if (isset($data['cabang']) && $data['cabang'] !== '' && $data['cabang'] !== null) {
        $cabang = (int)$data['cabang'];
    } else if (isset($_SESSION['user_cabang'])) {
        $cabang = (int)$_SESSION['user_cabang'];
    }

    // Check if name already exists (excluding current record) for this cabang
    $check_query = "SELECT id FROM laba_kategori WHERE name = '$name' AND id != $id";
    if ($cabang !== null && $cabang_column_exists) {
        $check_query .= " AND (cabang = $cabang OR cabang IS NULL)";
    }
    $check_result = mysqli_query($conn, $check_query);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nama kategori sudah ada untuk cabang ini'
        ]);
        exit;
    }

    // Build update query
    $query = "UPDATE laba_kategori 
              SET name = '$name', 
                  kode_akun = '$kode_akun', 
                  kategori = '$kategori', 
                  tipe_akun = '$tipe_akun', 
                  saldo = $saldo";

    if ($cabang !== null && $cabang_column_exists) {
        $query .= ", cabang = $cabang";
    }

    $query .= " WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil diupdate'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengupdate data: ' . mysqli_error($conn)
        ]);
    }
}

function handleDelete()
{
    global $conn;

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit;
    }

    // Check if category is being used
    $check_query = "SELECT COUNT(*) as count FROM laba WHERE kategori = '$id'";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);

    if ($check_row['count'] > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Kategori tidak dapat dihapus karena masih digunakan'
        ]);
        exit;
    }

    $query = "DELETE FROM laba_kategori WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus data: ' . mysqli_error($conn)
        ]);
    }
}
