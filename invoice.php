<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

// 1. Proteksi Halaman
if (!$user) {
  header('Location: admin/index.php');
  exit;
}

// 2. Ambil ID Pesanan dari URL
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($orderId <= 0) {
  header('Location: index.php');
  exit;
}

$userId = (int) $user['id'];

// 3. Ambil Data Induk Pesanan (Pastikan murni milik user yang sedang login)
$queryOrder = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($queryOrder, 'ii', $orderId, $userId);
mysqli_stmt_execute($queryOrder);
$resultOrder = mysqli_stmt_get_result($queryOrder);
$order = mysqli_fetch_assoc($resultOrder);

// Jika data pesanan tidak ditemukan, lempar balik ke home
if (!$order) {
  header('Location: index.php');
  exit;
}

// 4. Ambil Data Detail Item Produk yang Dibeli (Membaca JOIN ke tabel produk untuk ambil nama & gambar)
$queryItems = mysqli_prepare($conn, "
    SELECT od.*, p.name AS product_name, p.image AS product_image 
    FROM order_details od
    JOIN product p ON od.product_id = p.id
    WHERE od.order_id = ?
");
mysqli_stmt_bind_param($queryItems, 'i', $orderId);
mysqli_stmt_execute($queryItems);
$items = mysqli_stmt_get_result($queryItems);
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Invoice
    <?= htmlspecialchars($order['invoice_number']) ?> - BoxKado
  </title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <link rel="icon" href="assets/images/boxkado-icon.png" type="image/gif" />
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css">
  <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">

  <style>
    .header_section {
      position: relative;
      z-index: 9999 !important;
    }

    .dropdown-menu {
      z-index: 10000 !important;
    }

    .invoice-card {
      border-radius: 12px;
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

<body class="d-flex flex-column min-vh-100">

  <!-- Header -->
  <div class="header_section header_bg">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href=""><b>BoxKado</b></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse w-100" id="navbarSupportedContent">
          <ul class="navbar-nav flex-grow-1 justify-content-lg-center align-items-lg-center">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="product.php">Produk</a></li>
            <li class="nav-item"><a class="nav-link" href="aboutme.php">Tentang Kami</a></li>
            <li class="nav-item"><a class="nav-link" href="cart.php">Keranjang</a></li>
          </ul>
          <ul class="navbar-nav ml-lg-auto align-items-lg-center">
            <?php if ($user): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                  <?= htmlspecialchars($user['username']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="admin/logout.php">Logout</a></li>
                </ul>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </nav>
    </div>
  </div>
  <!-- End Header -->

  <!-- Invoice Content -->
  <div class="cream_section layout_padding mb-5">
    <div class="container">

      <div class="row justify-content-center">
        <div class="col-lg-9">

          <div class="alert alert-success text-center py-3 mb-4 border-0 shadow-sm">
            <h4 class="fw-bold mb-1"><i class="fa fa-check-circle me-2"></i>Pesanan Berhasil Dibuat!</h4>
            <p class="mb-0 small text-muted">Silakan selesaikan pembayaran sesuai petunjuk di bawah agar pesanan segera
              diproses.</p>
          </div>

          <div class="card invoice-card shadow-sm border-0 bg-white p-4 p-md-5">

            <!-- Baris Info Nota -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
              <div>
                <h3 class="fw-bold text-secondary mb-1">INVOICE PESANAN</h3>
                <span class="fw-bold text-muted">
                  <?= htmlspecialchars($order['invoice_number']) ?>
                </span>
              </div>
              <div class="text-md-end mt-2 mt-md-0">
                <span class="badge px-3 py-2 fs-6 badge-<?= htmlspecialchars($order['status']) ?>">
                  Status:
                  <?= ucfirst(htmlspecialchars($order['status'])) ?>
                </span>
                <div class="text-muted small mt-1">Tanggal:
                  <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                </div>
              </div>
            </div>

            <hr>

            <!-- Detail Alamat & Metode -->
            <div class="row g-4 my-2">
              <div class="col-md-6">
                <h6 class="fw-bold text-muted mb-2">Informasi Pengiriman:</h6>
                <p class="mb-1 fw-bold">
                  <?= ucfirst(htmlspecialchars($order['shipping_method'])) ?>
                </p>
                <?php if ($order['shipping_method'] === 'diantar'): ?>
                  <p class="mb-1 text-dark"><strong>Penerima:</strong>
                    <?= htmlspecialchars($order['nama_penerima']) ?> (
                    <?= htmlspecialchars($order['telepon']) ?>)
                  </p>
                  <p class="mb-0 text-muted small">
                    <?= nl2br(htmlspecialchars($order['alamat_lengkap'])) ?>
                  </p>
                <?php else: ?>
                  <p class="mb-0 text-muted small">Silakan datang langsung ke toko offline BoxKado untuk mengambil pesanan
                    Anda.</p>
                <?php endif; ?>

                <?php if (!empty($order['catatan'])): ?>
                  <div class="mt-2 p-2 bg-light rounded border-start border-3 border-pink small">
                    <strong>Catatan Kado:</strong> "
                    <?= htmlspecialchars($order['catatan']) ?>"
                  </div>
                <?php endif; ?>
              </div>

              <div class="col-md-6 text-md-end">
                <h6 class="fw-bold text-muted mb-2">Metode Pembayaran:</h6>
                <p class="mb-1 fw-bold" style="color: #ff74a4;">
                  <?= $order['payment_method'] === 'transfer_bank' ? 'Transfer Bank (Manual)' : 'Dompet Digital (QRIS / E-Wallet)' ?>
                </p>

                <!-- Panduan Bayar Dinamis Sesuai Opsi Pilihan User -->
                <div class="p-3 bg-light rounded text-start d-inline-block w-100 mt-2">
                  <?php if ($order['payment_method'] === 'transfer_bank'): ?>
                    <small class="d-block fw-bold text-dark mb-1">Silakan transfer ke rekening resmi kami:</small>
                    <span class="d-block fs-6 fw-bold text-primary">Bank BCA: 123-4567-890</span>
                    <small class="text-muted d-block">Atas Nama: BoxKado Official</small>
                  <?php else: ?>
                    <small class="d-block fw-bold text-dark mb-2 text-center">Silakan scan QRIS BoxKado:</small>
                    <div class="text-center">
                      <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=BoxKado-Payment"
                        alt="QRIS BoxKado" class="img-fluid rounded border p-2 bg-white" style="max-width: 160px;">
                    </div>
                    <small class="text-muted text-center d-block mt-2">Bisa di-scan via Dana, GoPay, OVO, ShopeePay, atau
                      MBanking</small>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Tabel Item Produk -->
            <div class="table-responsive mt-4">
              <table class="table align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Produk</th>
                    <th class="text-center">Harga Satuan</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-end">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <img src="assets/uploads/<?= htmlspecialchars($item['product_image']) ?>"
                            class="rounded border me-3" style="width: 45px; height: 45px; object-fit: cover;">
                          <span class="fw-bold text-dark text-truncate" style="max-width: 250px;">
                            <?= htmlspecialchars($item['product_name']) ?>
                          </span>
                        </div>
                      </td>
                      <td class="text-center">Rp
                        <?= number_format($item['price'], 0, ',', '.') ?>
                      </td>
                      <td class="text-center">
                        <?= $item['quantity'] ?>
                      </td>
                      <td class="text-end fw-bold">Rp
                        <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3" class="text-end text-muted border-0 pt-3">Total Harga Barang:</td>
                    <td class="text-end border-0 pt-3">Rp
                      <?= number_format($order['total_items_price'], 0, ',', '.') ?>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-end text-muted border-0 py-1">Ongkos Kirim:</td>
                    <td class="text-end border-0 py-1">
                      <?= $order['shipping_cost'] > 0 ? 'Rp ' . number_format($order['shipping_cost'], 0, ',', '.') : 'Gratis Ongkir' ?>
                    </td>
                  </tr>
                  <tr class="fs-5 fw-bold">
                    <td colspan="3" class="text-end border-0 pt-2">Total Pembayaran:</td>
                    <td class="text-end border-0 pt-2" style="color: #ff74a4;">Rp
                      <?= number_format($order['grand_total'], 0, ',', '.') ?>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <hr class="mt-4">

            <div class="mt-4 p-4 rounded bg-light">
              <?php if (empty($order['bukti_pembayaran'])): ?>
                <h5 class="fw-bold text-dark mb-2"><i class="fa fa-upload me-2" style="color: #ff74a4;"></i>Upload Bukti
                  Pembayaran</h5>
                <p class="text-muted small">Harap unggah foto atau tangkapan layar (screenshot) resi transfer/bukti bayar
                  yang sah (Format: JPG, JPEG, PNG, Maks 2MB).</p>

                <form action="upload-payment.php" method="POST" enctype="multipart/form-data" class="mt-3">
                  <input type="hidden" name="order_id" value="<?= $orderId ?>">

                  <div class="input-group">
                    <input type="file" name="bukti_bayar" class="form-control" accept="image/*" required>
                    <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #ff74a4;">
                      Kirim Bukti
                    </button>
                  </div>
                </form>
              <?php else: ?>
                <div class="row align-items-center">
                  <div class="col-md-7 mb-3 mb-md-0">
                    <h5 class="fw-bold text-success mb-1"><i class="fa fa-check-circle me-2"></i>Bukti Pembayaran Telah
                      Dikirim</h5>
                    <p class="text-muted small mb-0">Terima kasih! Bukti transfer Anda sudah terekam di sistem. Admin kami
                      akan segera melakukan verifikasi data dan mengubah status pesanan Anda.</p>
                  </div>
                  <div class="col-md-5 text-md-end">
                    <small class="d-block text-muted mb-1 font-weight-bold">Preview Bukti Anda:</small>
                    <a href="assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                      <img src="assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>"
                        class="img-fluid rounded border shadow-sm" style="max-height: 100px; object-fit: cover;">
                    </a>
                  </div>
                </div>
              <?php endif; ?>
            </div>

          </div>

          <div class="text-start mt-4">
            <a href="product.php" class="btn btn-outline-secondary px-4"><i class="fa fa-arrow-left me-2"></i>Kembali
              Belanja</a>
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- Footer -->
  <div class="copyright_section mt-auto">
    <div class="container">
      <p class="copyright_text">2025 All Rights Reserved. &copy;2025 BoxKado</p>
    </div>
  </div>

  <!-- JS -->
  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>