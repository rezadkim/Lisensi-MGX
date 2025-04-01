<?php
header('Content-Type: application/json');
require '../config.php';
require 'middleware.php';

// Verify admin token
// verifyAdminToken();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID lisensi diperlukan']);
        exit;
    }

    try {
        $id = $_POST['id'];
        $name = isset($_POST['name']) ? $_POST['name'] : null;
        $device_code = isset($_POST['device_code']) ? $_POST['device_code'] : null;
        $is_trial = isset($_POST['is_trial']) ? 1 : 0;
        $expiration_date = isset($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

        // Build the update query dynamically based on provided fields
        $updates = [];
        $params = [':id' => $id];
        
        if ($name !== null) {
            $updates[] = 'name = :name';
            $params[':name'] = $name;
        }
        if ($device_code !== null) {
            $updates[] = 'device_code = :device_code';
            $params[':device_code'] = $device_code;
        }
        if ($expiration_date !== null) {
            $updates[] = 'expiration_date = :expiration_date';
            $params[':expiration_date'] = $expiration_date;
        }
        if ($product_id !== null) {
            $updates[] = 'product_id = :product_id';
            $params[':product_id'] = $product_id;
        }
        
        // Always update is_trial
        $updates[] = 'is_trial = :is_trial';
        $params[':is_trial'] = $is_trial;

        if (empty($updates)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diubah']);
            exit;
        }

        $sql = "UPDATE licenses SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Lisensi berhasil diupdate']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>