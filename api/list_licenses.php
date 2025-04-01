<?php
header('Content-Type: application/json');
require '../config.php';
require 'middleware.php'; // Sertakan middleware

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Ambil semua lisensi dari database
    $stmt = $conn->query("SELECT * FROM licenses");
    $licenses = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $licenses]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>