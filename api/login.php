<?php
header('Content-Type: application/json');
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username dan password harus diisi']);
        exit;
    }

    // Cari admin berdasarkan username
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Verifikasi password (gunakan password_verify jika menggunakan hashing)
        if (md5($password) === $admin['password']) {
            // Generate token acak
            $token = bin2hex(random_bytes(32)); // Token 64 karakter

            // Simpan token ke database
            $stmt = $conn->prepare("UPDATE admins SET token = :token WHERE id = :id");
            $stmt->execute(['token' => $token, 'id' => $admin['id']]);

            echo json_encode(['status' => 'success', 'token' => $token]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Password salah']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>