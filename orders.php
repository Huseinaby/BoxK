<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

// Proteksi halaman: Jika belum login, tendang ke login
if (!$user) {
  header('Location: admin/index.php');
  exit;
}

$userId = (int) $user['id'];

// Ambil semua data pesanan milik user ini dari yang terbaru
$query = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($query, 'i', $userId);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Riwayat Pesanan - BoxKado</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <link rel="icon" href="assets/images/boxkado-icon.png" type="image/gif" />
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css">
  <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    .header_section {
      position: relative;
      z-index: 9999 !important;
    }

    .dropdown-menu {
      z-index: 10000 !important;
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

    .badge-diantar {
      background-color: #0dcaf0;
      color: #000;
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
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="product.php">Produk</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="aboutme.php">Tentang Kami</a>
            </li>

            <?php if ($user): ?>
              <li class="nav-item">
                <a class="nav-link" href="cart.php">Keranjang</a>
              </li>
            <?php endif; ?>
          </ul>

          <ul class="navbar-nav ml-lg-auto align-items-lg-center">
            <?php if ($user): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle fw-bold text-dark" href="#" id="userDropdown" role="button"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-user-circle me-1"></i> <?= htmlspecialchars($user['username']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 animate fade-In"
                  aria-labelledby="userDropdown">
                  <li><a class="dropdown-item py-2" href="orders.php"><i class="fa fa-shopping-bag me-2"
                        style="color: #ff74a4;"></i>Pesanan Saya</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item py-2 text-danger" href="admin/logout.php"><i
                        class="fa fa-sign-out me-2"></i>Logout</a></li>
                </ul>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link fw-bold" href="admin/index.php">Login</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold" href="admin/register.php">Daftar</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </nav>
    </div>
  </div>
  <!-- End Header -->

  <!-- Main Content -->
  <div class="cream_section layout_padding mb-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12 mb-4">
          <h1 class="cream_taital">Pesanan Saya</h1>
          <p class="text-center text-muted">Pantau status pembayaran dan pengiriman kado kamu di sini</p>
        </div>
      </div>

      <div class="card shadow-sm border-0 bg-white p-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>No. Invoice</th>
                  <th>Tanggal</th>
                  <th>Metode</th>
                  <th>Total Pembayaran</th>
                  <th class="text-center">Status Pesanan</th>
                  <th class="text-center">Bukti Bayar</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                  <tr>
                    <td class="fw-bold text-secondary">
                      <?= htmlspecialchars($row['invoice_number']) ?>
                    </td>
                    <td>
                      <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
                    </td>
                    <td>
                      <small class="d-block fw-bold">
                        <?= ucfirst(htmlspecialchars($row['shipping_method'])) ?>
                      </small>
                      <small class="text-muted">
                        <?php
                        switch ($row['payment_method']) {

                          case 'transfer_bank':
                            echo 'Transfer Bank';
                            break;

                          case 'dompet_digital':
                            echo 'QRIS';
                            break;

                          case 'bayar_ditempat':
                            echo 'Bayar di Tempat';
                            break;
                        }
                        ?>
                      </small>
                    </td>
                    <td class="fw-bold text-danger">Rp
                      <?= number_format($row['grand_total'], 0, ',', '.') ?>
                    </td>
                    <td class="text-center">
                      <span class="badge px-3 py-2 badge-<?= htmlspecialchars($row['status']) ?>">
                        <?= ucfirst(htmlspecialchars($row['status'])) ?>
                      </span>
                    </td>
                    <<td class="text-center">
                      <?php if ($row['payment_method'] === 'bayar_ditempat'): ?>
                        <span class="badge bg-info">
                          Bayar di Tempat
                        </span>
                      <?php elseif (empty($row['bukti_pembayaran'])): ?>
                        <span class="text-muted small">
                          <i>Belum Upload</i>
                        </span>
                      <?php else: ?>
                        <span class="text-success small fw-bold">
                          <i class="fa fa-check"></i>
                          Sudah di-upload
                        </span>
                      <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <!-- Tombol dinamis mengarah ke invoice masing-masing -->
                        <a href="invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm text-white px-3 fw-bold"
                          style="background-color: #ff74a4;">
                          <i class="fa fa-eye me-1"></i> Detail
                        </a>
                      </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center py-5">
            <i class="fa fa-shopping-bag fa-4x text-muted mb-3 d-block"></i>
            <h5>Kamu belum pernah melakukan pemesanan.</h5>
            <a href="product.php" class="btn text-white fw-bold mt-3 px-4" style="background-color: #ff74a4;">Mulai
              Belanja Kado</a>
          </div>
        <?php endif; ?>
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