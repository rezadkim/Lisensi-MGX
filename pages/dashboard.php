<?php
session_start();
require '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Generate token baru jika ada request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_token'])) {
    $token = bin2hex(random_bytes(32)); // Token 64 karakter
    $stmt = $conn->prepare("UPDATE admins SET token = :token WHERE id = :id");
    $stmt->execute(['token' => $token, 'id' => $_SESSION['admin_id']]);
    $_SESSION['success_message'] = "Token baru berhasil dihasilkan: <strong>$token</strong>";
}

// Ambil token admin saat ini
$stmt = $conn->prepare("SELECT token FROM admins WHERE id = :id");
$stmt->execute(['id' => $_SESSION['admin_id']]);
$admin = $stmt->fetch();
$current_token = $admin['token'];

$page_title = "Dashboard Admin"; // Judul halaman
require '../komponen/header.php'; // Include header
require '../komponen/navbar.php'; // Include navbar
?>

<!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Dashboard Admin</h4>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success mb-4">
                            <?= $_SESSION['success_message'] ?>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>

                        <p class="mb-4">Selamat datang, <strong><?= $_SESSION['username'] ?></strong>!</p>
                        <p class="mb-4">Token saat ini: <strong><?= $current_token ?></strong></p>

                        <form method="POST" class="mb-4">
                            <button type="submit" name="generate_token" class="btn btn-primary">
                            Generate Token Baru
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!--/ Content -->

<?php
require '../komponen/footer.php'; // Include footer
?>