<?php
header('Content-Type: application/json');
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $license_key = trim($_POST['license_key']);
    $device_code = trim($_POST['device_code']);

    // Validasi input
    if (empty($license_key) || empty($device_code)) {
        echo json_encode(['status' => 'error', 'message' => 'License key dan device code harus diisi']);
        exit;
    }

    // Cari lisensi berdasarkan license_key
    $stmt = $conn->prepare("SELECT * FROM licenses WHERE license_key = :license_key");
    $stmt->execute(['license_key' => $license_key]);
    $license = $stmt->fetch();

    if ($license) {
        // Periksa apakah device code sesuai
        if ($device_code !== $license['device_code']) {
            echo json_encode(['status' => 'error', 'message' => 'Device melebihi batas']);
            exit;
        }

        // Periksa apakah lisensi sudah expired
        $expiration_date = new DateTime($license['expiration_date']);
        $current_date = new DateTime();

        if ($current_date > $expiration_date) {
            echo json_encode(['status' => 'error', 'message' => 'Lisensi sudah expired']);
        } else {
            // Lisensi valid
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'id' => $license['id'],
                    'license_key' => $license['license_key'],
                    'name' => $license['name'],
                    'expiration_date' => $license['expiration_date'],
                    'device_code' => $license['device_code'],
                    'is_trial' => $license['is_trial'],
                    'product_id' => $license['product_id'],
                    'product_name' => $license['product_name']
                ]
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lisensi tidak ditemukan']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>