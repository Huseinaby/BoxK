<?php
session_start();
require '../functions.php';

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

// 3. Proses Ganti Status & Potong Stok (Saat Form di-Submit Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $statusBaru = $_POST['status'];

  // Ambil status lama untuk pengecekan agar tidak memotong stok dua kali
  $qCheck = mysqli_query($conn, "SELECT status FROM orders WHERE id = $orderId");
  $currentOrder = mysqli_fetch_assoc($qCheck);
  $statusLama = $currentOrder['status'];

  // JIKA status berubah menjadi 'proses' (pembayaran disetujui), potong stok barang
  if ($statusBaru === 'proses' && $statusLama === 'pending') {
    // Ambil item produk dalam pesanan ini
    $qItems = mysqli_query($conn, "SELECT product_id, quantity FROM order_details WHERE order_id = $orderId");
    while ($item = mysqli_fetch_assoc($qItems)) {
      $pId = $item['product_id'];
      $qty = $item['quantity'];
      // Jalankan query pengurangan stok
      mysqli_query($conn, "UPDATE product SET stock = stock - $qty WHERE id = $pId");
    }
  }

  // JIKA status berubah dari 'proses' di-cancel ke 'dibatalkan', kembalikan stok barang
  if ($statusBaru === 'dibatalkan' && $statusLama === 'proses') {
    $qItems = mysqli_query($conn, "SELECT product_id, quantity FROM order_details WHERE order_id = $orderId");
    while ($item = mysqli_fetch_assoc($qItems)) {
      $pId = $item['product_id'];
      $qty = $item['quantity'];
      mysqli_query($conn, "UPDATE product SET stock = stock + $qty WHERE id = $pId");
    }
  }

  // Update status induk pesanan
  $updateStatus = mysqli_query($conn, "UPDATE orders SET status = '$statusBaru' WHERE id = $orderId");

  if ($updateStatus) {
    echo "<script>alert('Status pesanan berhasil diperbarui!'); window.location.href='order-detail.php?id=$orderId';</script>";
    exit;
  }
}

// 4. Ambil Data Induk Pesanan
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
  <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
  <style>
    .navbar-top {
      background-color: #ff74a4;
      color: white;
    }

    .sidebar {
      background-color: #924068;
      min-height: 100vh;
      color: white;
    }

    .sidebar a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      display: block;
      padding: 10px 15px;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: rgba(0, 0, 0, 0.1);
      color: white;
    }

    .badge-pending {
      background-color: #ffc107;
      color: #000;
    }

    .badge-proses {
      background-color: #0d6efd;
      color: #fff;
    }

    .badge-selesai {
      background-color: #198754;
      color: #fff;
    }

    .badge-dibatalkan {
      background-color: #dc3545;
      color: #fff;
    }
  </style>
</head>

<body>

  <div class="container-fluid p-0">
    <div class="navbar-top p-3 d-flex justify-content-between align-items-center">
      <h4 class="mb-0 fw-bold">BoxKado Admin</h4>
      <div><?= htmlspecialchars($user['username']) ?></div>
    </div>

    <div class="row g-0">
      <div class="col-md-2 sidebar p-2">
        <a href="dashboard.php"><i class="fa fa-dashboard me-2"></i> Dashboard</a>
        <a href="category.php"><i class="fa fa-tags me-2"></i> Kategori</a>
        <a href="product.php"><i class="fa fa-gift me-2"></i> Produk</a>
        <a href="orders.php" class="active"><i class="fa fa-shopping-cart me-2"></i> Pesanan</a>
        <a href="logout.php" class="text-danger mt-5"><i class="fa fa-sign-out me-2"></i> Logout</a>
      </div>

      <div class="col-md-10 p-4 bg-light">
        <div class="mb-4">
          <a href="orders.php" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left"></i> Kembali ke
            Daftar</a>
          <h2 class="fw-bold text-dark mt-2">Detail Pesanan: <?= $order['invoice_number'] ?></h2>
        </div>

        <div class="row">
          <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm p-4 mb-4">
              <h5 class="fw-bold mb-3 text-secondary"><i class="fa fa-info-circle me-2"></i>Informasi Pelanggan</h5>
              <table class="table table-borderless sm-table">
                <tr>
                  <td width="35%">Nama Akun</td>
                  <td>: <?= htmlspecialchars($order['username']) ?></td>
                </tr>
                <tr>
                  <td>Metode Operasional</td>
                  <td>: <strong><?= ucfirst($order['shipping_method']) ?></strong></td>
                </tr>
                <?php if ($order['shipping_method'] === 'diantar'): ?>
                  <tr>
                    <td>Nama Penerima</td>
                    <td>: <?= htmlspecialchars($order['nama_penerima']) ?></td>
                  </tr>
                  <tr>
                    <td>No. HP Penerima</td>
                    <td>: <?= htmlspecialchars($order['telepon']) ?></td>
                  </tr>
                  <tr>
                    <td>Alamat Pengiriman</td>
                    <td>: <?= nl2br(htmlspecialchars($order['alamat_lengkap'])) ?></td>
                  </tr>
                <?php endif; ?>
                <tr>
                  <td>Catatan Kado</td>
                  <td>: <span class="text-muted">"<?= htmlspecialchars($order['catatan']) ?: '-' ?>"</span></td>
                </tr>
              </table>
            </div>

            <div class="card border-0 shadow-sm p-4">
              <h5 class="fw-bold mb-3 text-secondary"><i class="fa fa-gift me-2"></i>Item yang Dibeli</h5>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Produk</th>
                      <th class="text-center">Harga</th>
                      <th class="text-center">Qty</th>
                      <th class="text-end">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($item = mysqli_fetch_assoc($queryDetails)): ?>
                      <tr>
                        <td>
                          <div class="d-flex align-items-center">
                            <img src="../assets/uploads/<?= htmlspecialchars($item['product_image']) ?>"
                              class="rounded border me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <span class="small fw-bold"><?= htmlspecialchars($item['product_name']) ?></span>
                          </div>
                        </td>
                        <td class="text-center small">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                        <td class="text-center small"><?= $item['quantity'] ?></td>
                        <td class="text-end small fw-bold">Rp
                          <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                  <tfoot class="table-light small">
                    <tr>
                      <td colspan="3" class="text-end text-muted">Total Barang:</td>
                      <td class="text-end">Rp <?= number_format($order['total_items_price'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                      <td colspan="3" class="text-end text-muted">Ongkir:</td>
                      <td class="text-end">Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?></td>
                    </tr>
                    <tr class="fw-bold text-danger">
                      <td colspan="3" class="text-end">Total Akhir:</td>
                      <td class="text-end">Rp <?= number_format($order['grand_total'], 0, ',', '.') ?></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 mb-4">
              <h5 class="fw-bold mb-3 text-secondary"><i class="fa fa-refresh me-2"></i>Aksi Status Pesanan</h5>
              <div class="mb-3">
                <label class="small text-muted d-block mb-1">Status Saat Ini:</label>
                <span class="badge fs-6 px-3 py-2 badge-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
              </div>

              <form method="POST">
                <div class="mb-3">
                  <label class="form-label small fw-bold">Ubah Status Menjadi:</label>
                  <select name="status" class="form-select">
                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending (Belum
                      Diperiksa)</option>
                    <option value="proses" <?= $order['status'] === 'proses' ? 'selected' : '' ?>>Proses (Bayar Valid &
                      Potong Stok)</option>
                    <option value="selesai" <?= $order['status'] === 'selesai' ? 'selected' : '' ?>>Selesai (Kado
                      Sampai/Diambil)</option>
                    <option value="dibatalkan" <?= $order['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan
                    </option>
                  </select>
                </div>
                <button type="submit" name="update_status" class="btn text-white w-100 fw-bold"
                  style="background-color: #ff74a4;">
                  <i class="fa fa-save me-1"></i> Perbarui Status
                </button>
              </form>
            </div>

            <div class="card border-0 shadow-sm p-4 text-center">
              <h5 class="fw-bold mb-3 text-secondary text-start"><i class="fa fa-file-image-o me-2"></i>Bukti Pembayaran
              </h5>
              <?php if (empty($order['bukti_pembayaran'])): ?>
                <div class="alert alert-warning py-4 mb-0">
                  <i class="fa fa-exclamation-triangle fa-2x mb-2 d-block text-warning"></i>
                  <span class="small fw-bold">Pembeli belum mengunggah bukti transfer resi.</span>
                </div>
              <?php else: ?>
                <p class="small text-muted text-start mb-2">Klik gambar resi di bawah untuk memperbesar:</p>
                <a href="../assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                  <img src="../assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>"
                    class="img-fluid rounded border shadow-sm" style="max-height: 350px; object-fit: contain;">
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>