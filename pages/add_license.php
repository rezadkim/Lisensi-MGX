<?php
session_start();
require '../config.php'; // Sesuaikan path ke config.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Ambil daftar produk dari database
$products = $conn->query("SELECT * FROM products")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $device_code = $_POST['device_code'];
    $is_trial = isset($_POST['is_trial']) ? 1 : 0;
    $duration = $_POST['duration']; // Bulanan, Mingguan, Harian
    $product_id = $_POST['product_id']; // Produk yang dipilih

    // Ambil nama produk berdasarkan product_id
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = :product_id");
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch();
    $product_name = $product['name'];

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
    $stmt = $conn->prepare("INSERT INTO licenses (license_key, name, expiration_date, device_code, is_trial, product_id, product_name) VALUES (:license_key, :name, :expiration_date, :device_code, :is_trial, :product_id, :product_name)");
    $stmt->execute([
        'license_key' => $license_key,
        'name' => $name,
        'expiration_date' => $expiration_date->format('Y-m-d H:i:s'),
        'device_code' => $device_code,
        'is_trial' => $is_trial,
        'product_id' => $product_id,
        'product_name' => $product_name
    ]);

    $_SESSION['success_message'] = "Lisensi berhasil ditambahkan!";
    header('Location: add_license.php'); // Redirect ke halaman ini setelah berhasil
    exit;
}

$page_title = "Tambah Lisensi Baru"; // Judul halaman
require '../komponen/header.php'; // Include header
require '../komponen/navbar.php'; // Include navbar
?>

<!-- Content -->
<div class="layout-page">
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4">Tambah Lisensi Baru</h4>

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
              <label for="name" class="form-label">Nama</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
              <label for="device_code" class="form-label">Device Code</label>
              <input type="text" class="form-control" id="device_code" name="device_code" required>
            </div>

            <div class="mb-3">
              <label for="duration" class="form-label">Jangka Waktu</label>
              <select class="form-select" id="duration" name="duration" required>
                <option value="Bulanan">Bulanan +1</option>
                <option value="Mingguan">Mingguan +1</option>
                <option value="Harian">Harian +</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="product_id" class="form-label">Produk</label>
              <select class="form-select" id="product_id" name="product_id" required>
                <?php foreach ($products as $product): ?>
                  <option value="<?= $product['id'] ?>"><?= $product['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_trial" name="is_trial">
                <label class="form-check-label" for="is_trial">Trial</label>
              </div>
            </div>

            <div class="mb-3">
              <label for="expiration_date" class="form-label">Expiration Date</label>
              <input type="text" class="form-control" id="expiration_date" name="expiration_date" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
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
    defaultDate: "today", // Set tanggal default ke hari ini
  });

  // Ambil elemen checkbox trial dan dropdown duration
  const isTrialCheckbox = document.getElementById('is_trial');
  const durationSelect = document.getElementById('duration');

  // Tambahkan event listener untuk checkbox trial
  isTrialCheckbox.addEventListener('change', function() {
    if (this.checked) {
      // Jika trial dicentang, nonaktifkan dropdown duration
      durationSelect.disabled = true;

      // Set expired date 24 jam dari sekarang
      const now = new Date();
      now.setHours(now.getHours() + 24);
      expirationDateInput.setDate(now);
    } else {
      // Jika trial tidak dicentang, aktifkan dropdown duration
      durationSelect.disabled = false;

      // Set expired date ke hari ini
      expirationDateInput.setDate('today');
    }
  });
</script>

<?php
require '../komponen/footer.php'; // Include footer
?>