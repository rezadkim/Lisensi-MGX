<?php
session_start();
require '../config.php'; // Sesuaikan path ke config.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    // Simpan produk baru ke database
    $stmt = $conn->prepare("INSERT INTO products (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    $_SESSION['success_message'] = "Produk berhasil ditambahkan!";
    header('Location: add_product.php'); // Redirect ke halaman ini setelah berhasil
    exit;
}

$page_title = "Tambah Produk Baru"; // Judul halaman
require '../komponen/header.php'; // Include header
require '../komponen/navbar.php'; // Include navbar
?>

<!-- Content -->
<div class="layout-page">
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4">Tambah Produk Baru</h4>

      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-4">
          <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label for="name" class="form-label">Nama Produk</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </form>
        </div>
      </div>

      <div class="mt-3">
        <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
      </div>
    </div>
  </div>
</div>
<!--/ Content -->

<?php
require '../komponen/footer.php'; // Include footer
?>