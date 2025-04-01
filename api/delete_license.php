<?php
header('Content-Type: application/json');
require '../config.php';
require 'middleware.php'; // Sertakan middleware

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Hapus lisensi dari database
    $stmt = $conn->prepare("DELETE FROM licenses WHERE id = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Lisensi berhasil dihapus']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>