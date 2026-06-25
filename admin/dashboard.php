<?php
session_start();

require '../functions.php';

requireAdminAccess();

$user = $_SESSION['user'];

// --- QUERY STATISTIK UNTUK ISI DASHBOARD ---

// 1. Hitung Total Pendapatan Sah (Hanya dari pesanan yang berstatus 'selesai')
$qRevenue = mysqli_query($conn, "SELECT SUM(grand_total) AS total_sales FROM orders WHERE status = 'selesai'");
$dataRevenue = mysqli_fetch_assoc($qRevenue);
$totalRevenue = $dataRevenue['total_sales'] ?? 0;

// 2. Hitung Pesanan Masuk Menunggu Tindakan (Status 'pending')
$qPending = mysqli_query($conn, "SELECT COUNT(*) AS total_pending FROM orders WHERE status = 'pending'");
$dataPending = mysqli_fetch_assoc($qPending);
$totalPending = $dataPending['total_pending'] ?? 0;

// 3. Hitung Total Ragam Produk Kado
$qProducts = mysqli_query($conn, "SELECT COUNT(*) AS total_products FROM product");
$dataProducts = mysqli_fetch_assoc($qProducts);
$totalProducts = $dataProducts['total_products'] ?? 0;

// 4. Hitung Total Pelanggan Terdaftar
$qUsers = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM users WHERE role = 'pelanggan'");
$dataUsers = mysqli_fetch_assoc($qUsers);
$totalUsers = $dataUsers['total_users'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoxKado - Dashboard Admin</title>
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

        /* Variasi Warna Tema Dashboard */
        .card-revenue {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
        }

        .card-pending {
            background: linear-gradient(135deg, #f9d423, #ff4e50);
            color: white;
        }

        .card-products {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }

        .card-users {
            background: linear-gradient(135deg, #ff758c, #ff7eb3);
            color: white;
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
            <a class="navbar-brand" href="">BoxKado</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-shield me-1"></i>
                            <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item text-danger" href="logout.php"><i
                                        class="fa fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="d-flex">
        <div class="sidebar">
            <a href="dashboard.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i
                    class="fa fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="category.php"><i class="fa fa-tags me-2"></i> Kategori</a>
            <a href="product.php"><i class="fa fa-gift me-2"></i> Produk</a>
            <a href="orders.php"><i class="fa fa-shopping-cart me-2"></i> Pesanan</a>
            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'owner'): ?>
                <a href="sales-report.php"><i class="fa fa-chart-line me-2"></i> Laporan Penjualan</a>
                <a href="shop-setting.php"><i class="fa fa-store me-2"></i> Kelola Toko</a>
                <a href="admin-manage.php"><i class="fa fa-user-gear me-2"></i> Kelola Admin</a>
            <?php endif; ?>
        </div>
        <div class="content container">
            <div class="mb-4">
                <h2 class="mt-4 fw-bold text-dark mb-1">Selamat Datang, Admin!</h2>
                <p class="text-muted small">Berikut adalah ringkasan operasional toko kado Anda hari ini.</p>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card card-revenue p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold text-uppercase mb-1 small">Total Pendapatan</h6>
                                <h3 class="fw-bold mb-0">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3>
                            </div>
                            <div class="fs-1 text-white-50"><i class="fa fa-money-bill-wave"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card card-pending p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold text-uppercase mb-1 small">Pesanan Pending</h6>
                                <h3 class="fw-bold mb-0"><?= $totalPending ?> <span
                                        class="fs-6 fw-normal">pesanan</span></h3>
                            </div>
                            <div class="fs-1 text-white-50"><i class="fa fa-clock"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card card-products p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold text-uppercase mb-1 small">Ragam Produk</h6>
                                <h3 class="fw-bold mb-0"><?= $totalProducts ?> <span class="fs-6 fw-normal">item</span>
                                </h3>
                            </div>
                            <div class="fs-1 text-white-50"><i class="fa fa-box-open"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card card-users p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 fw-bold text-uppercase mb-1 small">Total Pelanggan</h6>
                                <h3 class="fw-bold mb-0"><?= $totalUsers ?> <span class="fs-6 fw-normal">akun</span>
                                </h3>
                            </div>
                            <div class="fs-1 text-white-50"><i class="fa fa-users"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-4 bg-white border-0 shadow-sm mt-3">
                <h5 class="fw-bold text-secondary mb-2"><i class="fa fa-info-circle text-primary me-2"></i>Panduan Cepat
                    Admin</h5>
                <p class="text-muted mb-0 small">
                    Periksa menu <a href="orders.php" class="fw-bold text-decoration-none"
                        style="color: #ff74a4;">Pesanan</a> secara berkala jika indikator <strong>Pesanan
                        Pending</strong> di atas bernilai lebih dari 0. Segera lakukan verifikasi berkas bukti transfer
                    gambar resi untuk memperbarui stok barang secara otomatis.
                </p>
            </div>

        </div>
    </div>
</body>

</html>