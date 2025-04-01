<?php
session_start();
require '../config.php'; // Sesuaikan path ke config.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Hapus lisensi jika ada parameter id
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM licenses WHERE id = :id");
    $stmt->execute(['id' => $delete_id]);

    $_SESSION['success_message'] = "Lisensi berhasil dihapus!";
    header('Location: list_licenses.php'); // Redirect ke halaman ini setelah berhasil
    exit;
}

// Ambil semua lisensi dari database
$stmt = $conn->query("SELECT * FROM licenses");
$licenses = $stmt->fetchAll();

$page_title = "Daftar Lisensi"; // Judul halaman
require '../komponen/header.php'; // Include header
require '../komponen/navbar.php'; // Include navbar
?>

<!-- Content -->
<div class="layout-page">
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4">Daftar Lisensi</h4>

      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-4">
          <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>License Key</th>
                  <th>Nama</th>
                  <th>Expiration Date</th>
                  <th>Device Code</th>
                  <th>Trial</th>
                  <th>Produk</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($licenses as $license): ?>
                  <tr>
                    <td><?= $license['id'] ?></td>
                    <td><?= $license['license_key'] ?></td>
                    <td><?= $license['name'] ?></td>
                    <td><?= $license['expiration_date'] ?></td>
                    <td><?= $license['device_code'] ?></td>
                    <td><?= $license['is_trial'] ? 'Ya' : 'Tidak' ?></td>
                    <td><?= $license['product_name'] ?></td>
                    <td>
                      <a href="modify_license.php?id=<?= $license['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                      <a href="list_licenses.php?delete_id=<?= $license['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus lisensi ini?')">Hapus</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
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