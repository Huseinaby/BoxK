<?php
session_start();
require '../functions.php';

// Proteksi halaman admin
requireAdminAccess();

// 1. Proteksi Halaman Admin
$user = $_SESSION['user'] ?? null;
if (!$user) {
  header('Location: index.php');
  exit;
}

// 2. Tangkap ID Pesanan dari URL
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($orderId <= 0) {
  header('Location: orders.php');
  exit;
}
// 3. Ambil Data Induk Pesanan
$queryOrder = mysqli_query($conn, "
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = $orderId
");
$order = mysqli_fetch_assoc($queryOrder);

if (!$order) {
  header('Location: orders.php');
  exit;
}

// 4. Proses Ganti Status (Saat Form di-Submit Admin) - Menyesuaikan Pemotongan Stok di Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $statusBaru = $_POST['status'];
  $courier = trim($_POST['courier'] ?? '');
  $trackingNumber = trim($_POST['tracking_number'] ?? '');

  // Ambil status lama dari database untuk pengecekan riwayat status
  $qCheck = mysqli_query($conn, "SELECT status FROM orders WHERE id = $orderId");
  $currentOrder = mysqli_fetch_assoc($qCheck);
  $statusLama = $currentOrder['status'];

  // LOGIKA RESTOCK: Jika pesanan dibatalkan (dari status apa pun selain dibatalkan), kembalikan stoknya
  if ($statusBaru === 'dibatalkan' && $statusLama !== 'dibatalkan') {
    $qItems = mysqli_query($conn, "SELECT product_id, quantity FROM order_details WHERE order_id = $orderId");
    while ($item = mysqli_fetch_assoc($qItems)) {
      $pId = $item['product_id'];
      $qty = $item['quantity'];

      // Kembalikan sisa inventori ke tabel product
      mysqli_query($conn, "UPDATE product SET stock = stock + $qty WHERE id = $pId");
    }
  }

  // LOGIKA RE-REDUCE (Pengecualian): Jika admin mengaktifkan kembali pesanan yang tadinya sudah dibatalkan
  if ($statusLama === 'dibatalkan' && $statusBaru !== 'dibatalkan') {
    $qItems = mysqli_query($conn, "SELECT product_id, quantity FROM order_details WHERE order_id = $orderId");
    while ($item = mysqli_fetch_assoc($qItems)) {
      $pId = $item['product_id'];
      $qty = $item['quantity'];

      // Kurangi stok kembali karena pesanan diaktifkan lagi
      mysqli_query($conn, "UPDATE product SET stock = stock - $qty WHERE id = $pId");
    }
  }

  $shippingMethod = $order['shipping_method'] ?? '';
  if ($shippingMethod === 'diantar') {
    $courierValue = mysqli_real_escape_string($conn, $courier);
    $trackingValue = mysqli_real_escape_string($conn, $trackingNumber);

    $updateStatus = mysqli_query($conn, "UPDATE orders SET status = '$statusBaru', courier = '$courierValue', tracking_number = '$trackingValue' WHERE id = $orderId");
  } else {
    $updateStatus = mysqli_query($conn, "UPDATE orders SET status = '$statusBaru' WHERE id = $orderId");
  }

  if ($updateStatus) {
    header('Location: order-detail.php?id=' . $orderId . '&notice=success');
    exit;
  }

  header('Location: order-detail.php?id=' . $orderId . '&notice=error');
  exit;
}

// 5. Ambil Data Detail Produk yang Dibeli
$queryDetails = mysqli_query($conn, "
    SELECT od.*, p.name AS product_name, p.image AS product_image 
    FROM order_details od
    JOIN product p ON od.product_id = p.id
    WHERE od.order_id = $orderId
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Pesanan #<?= $order['invoice_number'] ?> - Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }

    .navbar {
      background-color: #ff94c4;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      position: sticky !important;
      top: 0;
      z-index: 1030 !important;
    }

    .navbar-brand,
    .nav-link,
    .username {
      color: white !important;
      font-weight: bold;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      background-color: rgb(155, 69, 108);
      padding-top: 20px;
      position: fixed;
      color: white;
      transition: 0.3s;
    }

    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      font-size: 16px;
    }

    .sidebar a:hover {
      background-color: #ff74a4;
      padding-left: 25px;
      transition: 0.3s;
    }

    .content {
      margin-left: 260px;
      padding: 20px;
      transition: 0.3s;
    }

    .card {
      border: none;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.05);
      border-radius: 12px;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }

      .content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">BoxKado</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="fa fa-user-shield me-1"></i> <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-sign-out-alt me-1"></i>
                  Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="d-flex">
    <div class="sidebar">
      <a href="dashboard.php"><i class="fa fa-tachometer-alt me-2"></i> Dashboard</a>
      <a href="category.php"><i class="fa fa-tags me-2"></i> Kategori</a>
      <a href="product.php"><i class="fa fa-gift me-2"></i> Produk</a>
      <a href="orders.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i
          class="fa fa-shopping-cart me-2"></i> Pesanan</a>
      <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'owner'): ?>
        <a href="sales-report.php"><i class="fa fa-chart-line me-2"></i> Laporan Penjualan</a>
        <a href="shop-setting.php"><i class="fa fa-store me-2"></i> Kelola Toko</a>
        <a href="admin-manage.php"><i class="fa fa-user-gear me-2"></i> Kelola Admin</a>
      <?php endif; ?>
    </div>

    <div class="content container">
      <div class="mt-4 mb-4">
        <a href="orders.php" class="btn btn-sm btn-outline-secondary fw-bold px-3"><i class="fa fa-arrow-left me-1"></i>
          Kembali ke Daftar</a>
        <h2 class="fw-bold text-dark mt-3">Detail Pesanan: <?= $order['invoice_number'] ?></h2>
      </div>

      <div class="row">
        <div class="col-lg-7 mb-4">
          <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 text-secondary"><i class="fa fa-circle-info me-2"></i>Informasi Pelanggan</h5>
            <table class="table table-borderless sm-table mb-0 align-middle">
              <tr>
                <td width="35%" class="text-muted">Nama Akun</td>
                <td class="fw-bold text-dark">: <?= htmlspecialchars($order['username']) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Metode Penyerahan</td>
                <td>: <span class="badge bg-secondary px-2 py-1"><?= ucfirst($order['shipping_method']) ?></span></td>
              </tr>
              <?php if ($order['shipping_method'] === 'diantar'): ?>
                <tr>
                  <td class="text-muted">Nama Penerima</td>
                  <td class="fw-bold text-dark">: <?= htmlspecialchars($order['nama_penerima']) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">No. HP Penerima</td>
                  <td>: <a href="https://wa.me/62<?= ltrim($order['telepon'], '0') ?>" target="_blank"
                      class="text-decoration-none fw-bold" style="color: #ff74a4;"><i
                        class="fab fa-whatsapp me-1"></i><?= htmlspecialchars($order['telepon']) ?></a></td>
                </tr>
                <tr>
                  <td class="text-muted">Alamat Pengiriman</td>
                  <td class="text-dark">: <?= nl2br(htmlspecialchars($order['alamat_lengkap'])) ?></td>
                </tr>
              <?php endif; ?>
              <tr>
                <td class="text-muted">Catatan Kado</td>
                <td>: <span class="text-muted">"<?= htmlspecialchars($order['catatan']) ?: '-' ?>"</span></td>
              </tr>
            </table>
          </div>

          <div class="card border-0 shadow-sm p-4 bg-white">
            <h5 class="fw-bold mb-3 text-secondary"><i class="fa fa-gift me-2"></i>Item yang Dibeli</h5>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Produk</th>
                    <th class="text-center">Harga</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php mysqli_data_seek($queryDetails, 0); ?>
                  <?php while ($item = mysqli_fetch_assoc($queryDetails)): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <img src="../assets/uploads/<?= htmlspecialchars($item['product_image']) ?>"
                            class="rounded border me-2" style="width: 45px; height: 45px; object-fit: cover;">
                          <span class="small fw-bold text-dark"><?= htmlspecialchars($item['product_name']) ?></span>
                        </div>
                      </td>
                      <td class="text-center small text-muted">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                      <td class="text-center small fw-bold text-dark"><?= $item['quantity'] ?></td>
                      <td class="text-end small fw-bold text-dark">Rp
                        <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
                <tfoot class="table-light small">
                  <tr>
                    <td colspan="3" class="text-end text-muted">Total Barang:</td>
                    <td class="text-end fw-bold">Rp <?= number_format($order['total_items_price'], 0, ',', '.') ?></td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-end text-muted">Ongkir:</td>
                    <td class="text-end fw-bold">Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?></td>
                  </tr>
                  <tr class="fw-bold text-danger">
                    <td colspan="3" class="text-end fs-6">Total Akhir:</td>
                    <td class="text-end fs-5">Rp <?= number_format($order['grand_total'], 0, ',', '.') ?></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 text-secondary"><i class="fa fa-arrows-rotate me-2"></i>Aksi Status Pesanan</h5>
            <div class="mb-3">
              <label class="small text-muted d-block mb-1">Status Saat Ini:</label>
              <?php
              $badgeColor = 'secondary';
              if ($order['status'] === 'pending')
                $badgeColor = 'warning text-dark';
              elseif ($order['status'] === 'proses')
                $badgeColor = 'primary';
              elseif ($order['status'] === 'diantar')
                $badgeColor = 'info';
              elseif ($order['status'] === 'selesai')
                $badgeColor = 'success';
              elseif ($order['status'] === 'dibatalkan')
                $badgeColor = 'danger';
              ?>
              <span
                class="badge fs-6 px-3 py-2 rounded-pill bg-<?= $badgeColor ?>"><?= ucfirst($order['status']) ?></span>
            </div>

            <form method="POST">
              <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Ubah Status Menjadi:</label>
                <select name="status" class="form-select">
                  <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending (Belum Diperiksa)
                  </option>
                  <option value="proses" <?= $order['status'] === 'proses' ? 'selected' : '' ?>>Proses (Bayar Valid &
                    Potong Stok)</option>
                  <option value="diantar" <?= $order['status'] === 'diantar' ? 'selected' : '' ?>>Diantar (Sedang Dalam
                    Pengiriman)</option>
                  <option value="selesai" <?= $order['status'] === 'selesai' ? 'selected' : '' ?>>Selesai (Kado
                    Sampai/Diambil)</option>
                  <option value="dibatalkan" <?= $order['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
              </div>

              <?php if ($order['shipping_method'] === 'diantar'): ?>
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark">Kurir</label>
                  <input type="text" name="courier" class="form-control"
                    value="<?= htmlspecialchars($order['courier'] ?? '') ?>" placeholder="Contoh: JNE, POS, Grab">
                </div>
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark">Nomor Resi / Link Tracking</label>
                  <input type="text" name="tracking_number" class="form-control"
                    value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>"
                    placeholder="Masukkan nomor resi atau link tracking">
                  <?php
                  $trackingValue = trim((string) ($order['tracking_number'] ?? ''));
                  if (!empty($trackingValue)):
                    $trackingIsUrl = preg_match('/^https?:\/\//i', $trackingValue) === 1;
                    $trackingHost = '';
                    if ($trackingIsUrl) {
                      $parsedUrl = parse_url($trackingValue);
                      $trackingHost = $parsedUrl['host'] ?? '';
                    }
                    ?>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <button type="submit" name="update_status" class="btn text-white w-100 fw-bold py-2 shadow-sm"
                style="background-color: #ff74a4;">
                <i class="fa fa-floppy-disk me-1"></i> Perbarui Status
              </button>
            </form>
          </div>

          <div class="card border-0 shadow-sm p-4 text-center bg-white">
            <h5 class="fw-bold mb-3 text-secondary text-start"><i class="fa fa-image me-2"></i>Bukti Pembayaran</h5>
            <?php if (empty($order['bukti_pembayaran'])): ?>
              <div class="alert alert-warning py-4 mb-0 border-0 shadow-inner">
                <i class="fa fa-triangle-exclamation fa-2x mb-2 d-block text-warning"></i>
                <span class="small fw-bold">Pembeli belum mengunggah bukti transfer resi.</span>
              </div>
            <?php else: ?>
              <p class="small text-muted text-start mb-2"><i class="fa fa-magnifying-glass-plus me-1"></i>Klik gambar resi
                di bawah untuk memperbesar:</p>
              <a href="../assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                <img src="../assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>"
                  class="img-fluid rounded border shadow-sm product-card-hover"
                  style="max-height: 350px; object-fit: contain;">
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const params = new URLSearchParams(window.location.search);
    const notice = params.get('notice');

    if (notice === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Status pesanan berhasil diperbarui!',
        timer: 1800,
        showConfirmButton: false
      });
      const cleanUrl = window.location.pathname + '?id=' + params.get('id');
      window.history.replaceState({}, document.title, cleanUrl);
    } else if (notice === 'error') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Status pesanan gagal diperbarui. Silakan coba lagi.',
        timer: 2200,
        showConfirmButton: false
      });
      const cleanUrl = window.location.pathname + '?id=' + params.get('id');
      window.history.replaceState({}, document.title, cleanUrl);
    }
  </script>

</body>

</html>