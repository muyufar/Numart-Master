<?php
/**
 * API untuk menyimpan riwayat WA Blast
 */

header('Content-Type: application/json');
include '../aksi/koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$userId = $_SESSION['user_id'] ?? 0;
$cabang = $_SESSION['user_cabang'] ?? 0;
$totalRecipients = intval($data['total_recipients'] ?? 0);
$messageTemplate = mysqli_real_escape_string($conn, $data['message_template'] ?? '');
$blastType = mysqli_real_escape_string($conn, $data['blast_type'] ?? 'manual');

if ($userId === 0) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$query = "INSERT INTO wa_blast_history (cabang, user_id, message_template, total_recipients, blast_type) 
          VALUES ($cabang, $userId, '$messageTemplate', $totalRecipients, '$blastType')";

if (mysqli_query($conn, $query)) {
    echo json_encode(['success' => true, 'message' => 'History saved']);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}


