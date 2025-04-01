<?php
header('Content-Type: application/json');
require '../config.php';
require 'middleware.php'; // Sertakan middleware

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $device_code = $_POST['device_code'];
    $is_trial = isset($_POST['is_trial']) ? 1 : 0;
    $duration = $_POST['duration']; // Bulanan, Mingguan, Harian
    $product_id = $_POST['product_id'];

    // Generate license key (20 karakter acak)
    $license_key = bin2hex(random_bytes(10));

    // Hitung expiration date
    $expiration_date = new DateTime();
    if ($is_trial) {
        $expiration_date->modify('+24 hours'); // Jika trial, expired 24 jam
    } else {
        switch ($duration) {
            case 'Bulanan':
                $expiration_date->modify('+1 month');
                break;
            case 'Mingguan':
                $expiration_date->modify('+1 week');
                break;
            case 'Harian':
                $expiration_date->modify('+1 day');
                break;
        }
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO licenses (license_key, name, expiration_date, device_code, is_trial, product_id) VALUES (:license_key, :name, :expiration_date, :device_code, :is_trial, :product_id)");
    $stmt->execute([
        'license_key' => $license_key,
        'name' => $name,
        'expiration_date' => $expiration_date->format('Y-m-d H:i:s'),
        'device_code' => $device_code,
        'is_trial' => $is_trial,
        'product_id' => $product_id
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Lisensi berhasil ditambahkan']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>