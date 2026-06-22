<?php
session_start();
// Sesuaikan arah require core db kamu, karena file ini di dalam folder admin/
require '../functions.php';

// Proteksi halaman admin (Sesuaikan variabel session admin kamu)
$user = $_SESSION['user'] ?? null;
if (!$user) { // atau jika ada pengecekan role: || $user['role'] !== 'admin'
  header('Location: index.php');
  exit;
}

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
  <title>Kelola Pesanan - BoxKado Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
  <style>
    /* Samakan warna dengan screenshot dashboard kamu */
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
      fw-bold;
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
      <div>
        <?= htmlspecialchars($user['username']) ?> <i class="fa fa-caret-down"></i>
      </div>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold text-dark">Kelola Pesanan Masuk</h2>
        </div>

        <div class="card border-0 shadow-sm p-4">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>No. Invoice</th>
                  <th>Pelanggan</th>
                  <th>Tanggal</th>
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
                    <td class="fw-bold text-secondary">
                      <?= htmlspecialchars($row['invoice_number']) ?>
                    </td>
                    <td>
                      <?= htmlspecialchars($row['username']) ?>
                    </td>
                    <td><small>
                        <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>
                      </small></td>
                    <td><span class="badge bg-secondary">
                        <?= ucfirst($row['shipping_method']) ?>
                      </span></td>
                    <td class="fw-bold text-danger">Rp
                      <?= number_format($row['grand_total'], 0, ',', '.') ?>
                    </td>
                    <td class="text-center">
                      <span class="badge px-3 py-2 badge-<?= $row['status'] ?>">
                        <?= ucfirst($row['status']) ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <?php if (empty($row['bukti_pembayaran'])): ?>
                        <span class="text-muted small"><i>Belum dikirim</i></span>
                      <?php else: ?>
                        <span class="text-success small fw-bold"><i class="fa fa-check-circle"></i> Ada Bukti</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <a href="order-detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-dark px-3 fw-bold">
                        <i class="fa fa-cog me-1"></i> Proses
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
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>