<?php
session_start();
// Sesuaikan arah require core db kamu, karena file ini di dalam folder admin/
require '../functions.php';

// Proteksi halaman admin
requireAdminAccess();

$user = $_SESSION['user'];

// Ambil semua data pesanan dari yang paling baru masuk, gabungkan dengan username pembeli
$query = mysqli_query($conn, "
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BoxKado - Kelola Pesanan</title>
  <link rel="icon" href="../assets/images/boxkado-icon.png" type="image/gif" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        <a href="sales-report.php"><i class="fa fa-chart-line me-2"></i> Laporan Penjualan</a><a
          href="shop-setting.php"><i class="fa fa-store me-2"></i> Kelola Toko</a>
        <a href="admin-manage.php"><i class="fa fa-user-gear me-2"></i> Kelola Admin</a>
      <?php endif; ?>
    </div>

    <div class="content container">
      <div class="mt-4 mb-4">
        <h2 class="fw-bold text-dark mb-0">Kelola Pesanan Masuk</h2>
        <p class="text-muted small">Pantau log transaksi invoice, validasi resi, dan kelola alur pengiriman kado
          pelanggan.</p>
      </div>

      <div class="card border-0 shadow-sm p-4 bg-white">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">No. Invoice</th>
                <th>Pelanggan</th>
                <th>Tanggal Transaksi</th>
                <th>Metode</th>
                <th>Total Akhir</th>
                <th class="text-center">Status</th>
                <th class="text-center">Bukti Bayar</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                  <td class="fw-bold text-secondary ps-3">
                    <?= htmlspecialchars($row['invoice_number']) ?>
                  </td>
                  <td class="fw-bold text-dark">
                    <?= htmlspecialchars($row['username']) ?>
                  </td>
                  <td>
                    <small class="text-muted">
                      <i
                        class="fa fa-calendar-alt me-1 text-black-50"></i><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>
                    </small>
                  </td>
                  <td>
                    <span class="badge bg-secondary px-2 py-1 small">
                      <?= ucfirst($row['shipping_method']) ?>
                    </span>
                  </td>
                  <td class="fw-bold text-danger">
                    Rp <?= number_format($row['grand_total'], 0, ',', '.') ?>
                  </td>
                  <td class="text-center">
                    <?php
                    $badgeColor = 'secondary';
                    if ($row['status'] === 'pending')
                      $badgeColor = 'warning text-dark';
                    elseif ($row['status'] === 'proses')
                      $badgeColor = 'primary';
                    elseif ($row['status'] === 'selesai')
                      $badgeColor = 'success';
                    elseif ($row['status'] === 'dibatalkan')
                      $badgeColor = 'danger';
                    ?>
                    <span class="badge px-3 py-2 fw-bold rounded-pill bg-<?= $badgeColor ?>">
                      <?= ucfirst($row['status']) ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <?php if (empty($row['bukti_pembayaran'])): ?>
                      <span class="text-muted small"><i><i class="fa fa-minus-circle me-1"></i>Belum dikirim</i></span>
                    <?php else: ?>
                      <span class="text-success small fw-bold"><i class="fa fa-check-circle me-1"></i> Ada Bukti</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="order-detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-dark px-3 fw-bold shadow-sm">
                      <i class="fa fa-sliders-h me-1"></i> Proses
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>

</html>