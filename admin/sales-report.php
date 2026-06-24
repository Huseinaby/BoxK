<?php
session_start();
require '../functions.php';

// Proteksi Ketat Khusus Owner
requireOwnerAccess();

$user = $_SESSION['user'];

// 1. Ambil Parameter Filter Tanggal (Default: Sebulan Terakhir jika kosong)
$tgl_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($conn, $_GET['tgl_mulai']) : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($conn, $_GET['tgl_selesai']) : date('Y-m-d');

// Kondisi query berdasarkan filter tanggal (Asumsi di tabel orders ada kolom created_at atau tanggal)
// Jika di tabel orders kamu kolomnya bernama 'tanggal', ganti created_at di bawah menjadi tanggal
$whereClause = "WHERE o.status = 'selesai' AND DATE(o.created_at) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";

// 2. Query Ringkasan Angka (Total Pendapatan & Total Pesanan)
$qSummary = mysqli_query($conn, "
    SELECT 
        SUM(grand_total) as total_pendapatan, 
        COUNT(id) as total_pesanan 
    FROM orders o 
    $whereClause
");
$summary = mysqli_fetch_assoc($qSummary);

// 3. Query Total Produk Terjual
$qQty = mysqli_query($conn, "
    SELECT SUM(od.quantity) as total_terjual 
    FROM order_details od
    JOIN orders o ON od.order_id = o.id
    $whereClause
");
$qtyRow = mysqli_fetch_assoc($qQty);

// 4. Query Daftar Transaksi Selesai (Tabel utama)
$querySales = mysqli_query($conn, "
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $whereClause
    ORDER BY o.id DESC
");

// 5. Query Produk Terlaris (Top 5)
$queryTopProducts = mysqli_query($conn, "
    SELECT p.name, SUM(od.quantity) as total_qty, SUM(od.price * od.quantity) as total_duit
    FROM order_details od
    JOIN product p ON od.product_id = p.id
    JOIN orders o ON od.order_id = o.id
    $whereClause
    GROUP BY od.product_id
    ORDER BY total_qty DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BoxKado - Laporan Penjualan</title>
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
    }

    .navbar-brand,
    .nav-link {
      color: white !important;
      font-weight: bold;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      background-color: rgb(155, 69, 108);
      position: fixed;
      color: white;
      display: flex !important;
      flex-direction: column;
      justify-content: space-between;
      padding-bottom: 20px;
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
    }

    .card {
      border: none;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.05);
      border-radius: 12px;
    }

    .logout-box {
      padding: 0 15px;
    }

    .btn-logout-custom {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: rgba(255, 255, 255, 0.1);
      color: #ffb3c6 !important;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      font-weight: bold;
      padding: 10px 15px !important;
      transition: 0.3s;
      text-decoration: none;
    }

    .btn-logout-custom:hover {
      background-color: #dc3545 !important;
      color: white !important;
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

    /* ==========================================================================
   CSS KHUSUS UNTUK CETAK DOKUMEN (PRINT MODE)
   ========================================================================== */
    @media print {

      /* 1. Sembunyikan elemen web yang tidak diperlukan dalam dokumen fisik */
      .navbar,
      .sidebar,
      .btn,
      form,
      .text-muted.small {
        display: none !important;
      }

      /* 2. Lebarkan area konten utama agar memenuhi kertas (A4/F4) */
      .content {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
      }

      body {
        background-color: #fff !important;
        color: #000 !important;
        font-size: 12pt;
      }

      /* 3. Hilangkan efek shadow dan border melengkung Bootstrap agar tampak formal */
      .card {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin-bottom: 20px !important;
      }

      /* 4. Atur tata letak grid ringkasan (KPI Cards) agar tetap berjejer ke samping */
      .row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 15px !important;
      }

      .col-md-4 {
        width: 33.33% !important;
        border: 1px solid #ddd !important;
        padding: 15px !important;
        border-radius: 6px !important;
      }

      /* 5. Modifikasi tabel agar terlihat seperti laporan formal standar */
      .table {
        width: 100% !important;
        border-collapse: collapse !important;
      }

      .table th,
      .table td {
        border: 1px solid #000 !important;
        /* Beri garis hitam tipis pada tabel */
        padding: 8px !important;
      }

      .table-light {
        background-color: #f2f2f2 !important;
        -webkit-print-color-adjust: exact;
        /* Memaksa browser mencetak warna latar header */
        print-color-adjust: exact;
      }

      /* 6. Atur layout bagian bawah (Tabel Rincian & Top Products) agar rapi */
      .col-lg-8 {
        width: 65% !important;
      }

      .col-lg-4 {
        width: 35% !important;
      }

      /* Beri garis pembatas tipis pada list produk terlaris saat dicetak */
      .list-group-item {
        border-bottom: 1px solid #ddd !important;
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
      <div class="menu-grup pt-3">
        <a href="dashboard.php"><i class="fa fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="category.php"><i class="fa fa-tags me-2"></i> Kategori</a>
        <a href="product.php"><i class="fa fa-gift me-2"></i> Produk</a>
        <a href="orders.php"><i class="fa fa-shopping-cart me-2"></i> Pesanan</a>
        <a href="sales-report.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i
            class="fa fa-chart-line me-2"></i> Laporan Penjualan</a>
        <?php if ($user['role'] === 'owner'): ?>
          <a href="admin-manage.php"><i class="fa fa-user-gear me-2"></i> Kelola Admin</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="content container mb-5">
      <div class="mt-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 class="fw-bold text-dark mb-0">Laporan Penjualan</h2>
          <p class="text-muted small mb-0">Analisis omzet bisnis BoxKado berdasarkan periode transaksi.</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary fw-bold shadow-sm"><i
            class="fa fa-print me-1"></i> Cetak Laporan</button>
      </div>

      <div class="card p-4 bg-white shadow-sm mb-4">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label small fw-bold text-muted">Dari Tanggal</label>
            <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold text-muted">Sampai Tanggal</label>
            <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai ?>">
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn text-white w-100 fw-bold py-2 shadow-sm"
              style="background-color: #ff74a4;">
              <i class="fa fa-filter me-1"></i> Filter Laporan
            </button>
          </div>
        </form>
      </div>

      <div class="row mb-4">
        <div class="col-md-4 mb-3">
          <div class="card p-4 bg-white shadow-sm border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted small fw-bold d-block mb-1">TOTAL PENDAPATAN</span>
                <h3 class="fw-bold text-success mb-0">Rp
                  <?= number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.') ?>
                </h3>
              </div>
              <i class="fa fa-money-bill-wave fa-2x text-muted opacity-50"></i>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card p-4 bg-white shadow-sm border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted small fw-bold d-block mb-1">PESANAN SELESAI</span>
                <h3 class="fw-bold text-primary mb-0">
                  <?= $summary['total_pesanan'] ?> Transaksi
                </h3>
              </div>
              <i class="fa fa-circle-check fa-2x text-muted opacity-50"></i>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card p-4 bg-white shadow-sm border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted small fw-bold d-block mb-1">PRODUK TERJUAL</span>
                <h3 class="fw-bold text-warning mb-0">
                  <?= $qtyRow['total_terjual'] ?? 0 ?> Item
                </h3>
              </div>
              <i class="fa fa-box-open fa-2x text-muted opacity-50"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-8 mb-4">
          <div class="card p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fa fa-list-check me-2 text-secondary"></i>Rincian Penjualan
              Selesai</h5>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3">Invoice</th>
                    <th>Pelanggan</th>
                    <th class="text-center">Metode</th>
                    <th class="text-end">Total Akhir</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (mysqli_num_rows($querySales) == 0): ?>
                    <tr>
                      <td colspan="4" class="text-center text-muted py-4">Tidak ada data transaksi pada periode ini.</td>
                    </tr>
                  <?php endif; ?>
                  <?php while ($row = mysqli_fetch_assoc($querySales)): ?>
                    <tr>
                      <td class="ps-3 fw-bold text-dark">#
                        <?= $row['invoice_number'] ?>
                      </td>
                      <td class="small text-muted"><i class="fa fa-user me-1 text-black-50"></i>
                        <?= htmlspecialchars($row['username']) ?>
                      </td>
                      <td class="text-center"><span class="badge bg-secondary">
                          <?= ucfirst($row['shipping_method']) ?>
                        </span></td>
                      <td class="text-end fw-bold text-success">Rp
                        <?= number_format($row['grand_total'], 0, ',', '.') ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-4 mb-4">
          <div class="card p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fa fa-crown me-2 text-warning"></i>5 Produk Terlaris</h5>
            <ul class="list-group list-group-flush">
              <?php if (mysqli_num_rows($queryTopProducts) == 0): ?>
                <li class="list-group-item text-center text-muted border-0 py-3">Belum ada data barang terjual.</li>
              <?php endif; ?>
              <?php $rank = 1;
              while ($top = mysqli_fetch_assoc($queryTopProducts)): ?>
                <li class="list-group-item px-0 py-3 border-bottom d-flex justify-content-between align-items-start">
                  <div class="me-auto">
                    <div class="fw-bold text-dark small"><span class="badge bg-light text-dark border me-1">
                        <?= $rank++ ?>
                      </span>
                      <?= htmlspecialchars($top['name']) ?>
                    </div>
                    <span class="text-muted text-xs" style="font-size: 12px;">Omzet: Rp
                      <?= number_format($top['total_duit'], 0, ',', '.') ?>
                    </span>
                  </div>
                  <span class="badge rounded-pill px-3 py-1 bg-warning text-dark fw-bold">
                    <?= $top['total_qty'] ?> Pcs
                  </span>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>