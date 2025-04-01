<?php
session_start();
require '../config.php'; // Sesuaikan path ke config.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Ambil daftar produk dari database
$products = $conn->query("SELECT * FROM products")->fetchAll();

// Ambil data lisensi berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM licenses WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $license = $stmt->fetch();

    if (!$license) {
        echo "Lisensi tidak ditemukan!";
        exit;
    }
} else {
    echo "ID lisensi tidak ditemukan!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $device_code = $_POST['device_code'];
    $is_trial = isset($_POST['is_trial']) ? 1 : 0;
    $expiration_date = $_POST['expiration_date'];
    $product_id = $_POST['product_id']; // Produk yang dipilih

    // Ambil nama produk berdasarkan product_id
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = :product_id");
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch();
    $product_name = $product['name'];

    // Jika trial dicentang, set expired 24 jam dari sekarang
    if ($is_trial) {
        $expiration_date = (new DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');
    }

    // Update data lisensi
    $stmt = $conn->prepare("UPDATE licenses SET name = :name, device_code = :device_code, expiration_date = :expiration_date, is_trial = :is_trial, product_id = :product_id, product_name = :product_name WHERE id = :id");
    $stmt->execute([
        'id' => $id,
        'name' => $name,
        'device_code' => $device_code,
        'expiration_date' => $expiration_date,
        'is_trial' => $is_trial,
        'product_id' => $product_id,
        'product_name' => $product_name
    ]);

    $_SESSION['success_message'] = "Lisensi berhasil diupdate!";
    header('Location: list_licenses.php'); // Redirect ke halaman daftar lisensi
    exit;
}

$page_title = "Modifikasi Lisensi"; // Judul halaman
require '../komponen/header.php'; // Include header
require '../komponen/navbar.php'; // Include navbar
?>

<!-- Content -->
<div class="layout-page">
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4">Modifikasi Lisensi</h4>

      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-4">
          <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="id" value="<?= $license['id'] ?>">
            <div class="mb-3">
              <label for="name" class="form-label">Nama</label>
              <input type="text" class="form-control" id="name" name="name" value="<?= $license['name'] ?>" required>
            </div>

            <div class="mb-3">
              <label for="device_code" class="form-label">Device Code</label>
              <input type="text" class="form-control" id="device_code" name="device_code" value="<?= $license['device_code'] ?>" required>
            </div>

            <div class="mb-3">
              <label for="product_id" class="form-label">Produk</label>
              <select class="form-select" id="product_id" name="product_id" required>
                <?php foreach ($products as $product): ?>
                  <option value="<?= $product['id'] ?>" <?= $product['id'] == $license['product_id'] ? 'selected' : '' ?>>
                    <?= $product['name'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_trial" name="is_trial" <?= $license['is_trial'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_trial">Trial</label>
              </div>
            </div>

            <div class="mb-3">
              <label for="expiration_date" class="form-label">Expiration Date</label>
              <input type="text" class="form-control" id="expiration_date" name="expiration_date" value="<?= date('Y-m-d H:i', strtotime($license['expiration_date'])) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Content -->

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  // Inisialisasi Flatpickr
  const expirationDateInput = flatpickr("#expiration_date", {
    enableTime: true, // Aktifkan waktu
    dateFormat: "Y-m-d H:i", // Format tanggal dan waktu
    minDate: "today", // Batasi tanggal minimal hari ini
    defaultDate: "<?= date('Y-m-d H:i', strtotime($license['expiration_date'])) ?>", // Set tanggal default
  });

  // Ambil elemen checkbox trial
  const isTrialCheckbox = document.getElementById('is_trial');

  // Tambahkan event listener untuk checkbox trial
  isTrialCheckbox.addEventListener('change', function() {
    if (this.checked) {
      // Jika trial dicentang, set expired date 24 jam dari sekarang
      const now = new Date();
      now.setHours(now.getHours() + 24);
      expirationDateInput.setDate(now);
    } else {
      // Jika trial tidak dicentang, set expired date ke tanggal sebelumnya
      expirationDateInput.setDate("<?= date('Y-m-d H:i', strtotime($license['expiration_date'])) ?>");
    }
  });

  // Jika trial sudah dicentang saat form dimuat, set expired date 24 jam dari sekarang
  if (isTrialCheckbox.checked) {
    const now = new Date();
    now.setHours(now.getHours() + 24);
    expirationDateInput.setDate(now);
  }
</script>

<?php
require '../komponen/footer.php'; // Include footer
?>