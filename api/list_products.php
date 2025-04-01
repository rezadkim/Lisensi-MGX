<?php
header('Content-Type: application/json');
require '../config.php';
require 'middleware.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {

        // Get all products (using FETCH_ASSOC to avoid numeric indexes)
        $stmt = $conn->query("SELECT id, name FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Proper response format with all products
        echo json_encode([
            'status' => 'success',
            'data' => $products,  // This contains all your products
            'count' => count($products)
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengambil data produk',
            'error' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Method tidak diizinkan. Gunakan GET.'
    ]);
}
?>