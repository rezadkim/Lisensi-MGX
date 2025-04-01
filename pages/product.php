<?php
session_start();
require '../config.php'; // Sesuaikan path ke config.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Hapus produk jika ada parameter id
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Hapus produk dari database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $_SESSION['success_message'] = "Produk berhasil dihapus!";
    header('Location: product.php'); // Ubah redirect ke index.php
    exit;
}

// Ambil semua produk dari database
$products = $conn->query("SELECT * FROM products")->fetchAll();

$page_title = "Kelola Produk"; // Ubah judul halaman
require '../komponen/header.php'; // Include header
require '../komponen/navbar.php'; // Include navbar
?>

<!-- Content -->
<div class="layout-page">
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4">Kelola Produk</h4>

      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-4">
          <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <div class="mb-3">
        <a href="add_product.php" class="btn btn-primary">Tambah Produk Baru</a>
      </div>

      <div class="card">
        <div class="card-body">
          <!-- Ubah bagian tabel menjadi lebih sederhana -->
          <table class="table table-bordered">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Nama Produk</th>
                      <th>Aksi</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($products as $product): ?>
                      <tr>
                          <td><?= $product['id'] ?></td>
                          <td><?= $product['name'] ?></td>
                          <td>
                              <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                              <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
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