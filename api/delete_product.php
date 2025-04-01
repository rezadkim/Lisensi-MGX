<?php
header('Content-Type: application/json');
require '../config.php';
require 'middleware.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
        
        // First check if product is used in licenses
        // $check = $conn->prepare("SELECT COUNT(*) FROM licenses WHERE product_id = :id");
        // $check->execute(['id' => $id]);
        // $count = $check->fetchColumn();
        
        // if ($count > 0) {
        //     echo json_encode([
        //         'status' => 'error',
        //         'message' => 'Produk tidak bisa dihapus karena masih digunakan',
        //         'license_count' => $count
        //     ]);
        //     exit;
        // }
        
        // If not used, proceed with deletion
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus produk',
            'error' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
}
?>