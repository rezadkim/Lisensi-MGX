<?php
session_start();
require '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$page_title = "Edit Produk";

// Ambil data produk jika ada parameter id
$product = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error_message'] = "Produk tidak ditemukan!";
        header('Location: product.php');
        exit;
    }
}

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    
    try {
        $stmt = $conn->prepare("UPDATE products SET name = :name WHERE id = :id");
        $stmt->execute([
            'name' => $name,
            'id' => $id
        ]);
        
        $_SESSION['success_message'] = "Nama produk berhasil diperbarui!";
        header('Location: product.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Gagal memperbarui produk: " . $e->getMessage();
    }
}

require '../komponen/header.php';
require '../komponen/navbar.php';
?>

<!-- Content -->
<div class="layout-page">
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4">Edit Nama Produk</h4>

      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger mb-4">
          <?= $_SESSION['error_message'] ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">
          <form method="POST" action="edit_product.php">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            
            <div class="mb-3">
              <label for="name" class="form-label">Nama Produk</label>
              <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            
            <button type="submit" name="update_product" class="btn btn-primary">Simpan Perubahan</button>
            <a href="product.php" class="btn btn-secondary">Kembali</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Content -->

<?php
require '../komponen/footer.php';
?>