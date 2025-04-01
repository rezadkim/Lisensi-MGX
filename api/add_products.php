<?php
header('Content-Type: application/json');
require '../config.php';
require 'middleware.php';

// Clear any previous output
ob_start();

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Get input
    $name = $_POST['name'] ?? '';
    
    if (empty($name)) {
        throw new Exception('Product name cannot be empty', 400);
    }

    // Insert product
    $stmt = $conn->prepare("INSERT INTO products (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    // Clear any unexpected output
    ob_clean();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Produk berhasil ditambahkan',
        'product_id' => $conn->lastInsertId()
    ]);

} catch (Exception $e) {
    ob_clean();
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>