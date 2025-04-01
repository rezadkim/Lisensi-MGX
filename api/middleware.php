<?php
header('Content-Type: application/json');
require '../config.php';

// Ambil token dari parameter
if (!isset($_GET['token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan']);
    exit;
}

$token = $_GET['token'];

// Cari admin berdasarkan token
$stmt = $conn->prepare("SELECT * FROM admins WHERE token = :token");
$stmt->execute(['token' => $token]);
$admin = $stmt->fetch();

if (!$admin) {
    echo json_encode(['status' => 'error', 'message' => 'Token tidak valid']);
    exit;
}

// Simpan data admin untuk digunakan di API
$admin_id = $admin['id'];
$username = $admin['username'];
?>