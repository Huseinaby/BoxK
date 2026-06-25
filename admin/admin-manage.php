<?php
session_start();
require '../functions.php';

// Proteksi Khusus Owner
requireOwnerAccess();

$user = $_SESSION['user'];

// Variabel bantu untuk menampung pesan SweetAlert2 dari PHP ke JavaScript
$swalScript = '';

// 1. Proses Tambah Admin Baru
if (isset($_POST['add_admin'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];

  // Cek apakah username sudah dipakai
  $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
  if (mysqli_num_rows($checkUser) > 0) {
    $swalScript = "
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Username sudah terdaftar!',
                confirmButtonColor: '#ff94c4'
            }).then(function() {
                window.location.href = 'admin-manage.php';
            });
        ";
  } else {
    // Hash password demi keamanan
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert sebagai role admin
    $queryInsert = mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$passwordHash', 'admin')");

    if ($queryInsert) {
      $swalScript = "
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Akun Admin berhasil ditambahkan!',
                    confirmButtonColor: '#ff94c4'
                }).then(function() {
                    window.location.href = 'admin-manage.php';
                });
            ";
    }
  }
}

// 2. Proses Hapus Admin
if (isset($_GET['delete_id'])) {
  $deleteId = (int) $_GET['delete_id'];

  // Pastikan tidak menghapus akun sendiri atau owner lain lewat URL query iseng
  $checkRole = mysqli_query($conn, "SELECT role FROM users WHERE id = $deleteId");
  $userToDelete = mysqli_fetch_assoc($checkRole);

  if ($userToDelete && $userToDelete['role'] === 'admin') {
    mysqli_query($conn, "DELETE FROM users WHERE id = $deleteId");
    $swalScript = "
            Swal.fire({
                icon: 'success',
                title: 'Dihapus!',
                text: 'Akun Admin berhasil dihapus!',
                confirmButtonColor: '#ff94c4'
            }).then(function() {
                window.location.href = 'admin-manage.php';
            });
        ";
  }
}

// 3. Ambil semua data user yang rolenya 'admin'
$queryAdmin = mysqli_query($conn, "SELECT id, username FROM users WHERE role = 'admin' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BoxKado - Kelola Admin</title>
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
    .nav-link {
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
              <i class="fa fa-user-shield me-1"></i>
              <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
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
      <a href="orders.php"><i class="fa fa-shopping-cart me-2"></i> Pesanan</a>
      <a href="sales-report.php"><i class="fa fa-chart-line me-2"></i> Laporan Penjualan</a>
      <a href="shop-setting.php"><i class="fa fa-store me-2"></i> Kelola Toko</a>
      <a href="admin-manage.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i
          class="fa fa-user-gear me-2"></i> Kelola Admin</a>

    </div>

    <div class="content container">
      <div class="mt-4 mb-4">
        <h2 class="fw-bold text-dark mb-0">Kelola Akun Admin</h2>
        <p class="text-muted small">Registrasikan staf admin baru atau cabut hak akses operasional toko di bawah ini.
        </p>
      </div>

      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="card p-4 bg-white shadow-sm">
            <h5 class="fw-bold text-dark mb-3"><i class="fa fa-user-plus me-2 text-secondary"></i>Tambah Admin</h5>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username baru" required
                  autocomplete="off">
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
              </div>
              <button type="submit" name="add_admin" class="btn text-white w-100 fw-bold shadow-sm"
                style="background-color: #ff74a4;">
                <i class="fa fa-plus me-1"></i> Daftarkan Admin
              </button>
            </form>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="card p-4 bg-white shadow-sm">
            <h5 class="fw-bold text-dark mb-3"><i class="fa fa-users me-2 text-secondary"></i>Daftar Staf Aktif</h5>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3">No</th>
                    <th>Nama Staf Admin</th>
                    <th>Tanggal Dibuat</th>
                    <th class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (mysqli_num_rows($queryAdmin) == 0): ?>
                    <tr>
                      <td colspan="4" class="text-center text-muted py-3">Belum ada akun admin yang dibuat.</td>
                    </tr>
                  <?php endif; ?>
                  <?php $no = 1;
                  while ($row = mysqli_fetch_assoc($queryAdmin)): ?>
                    <tr>
                      <td class="ps-3 text-muted">
                        <?= $no++; ?>
                      </td>
                      <td class="fw-bold text-dark"><i class="fa fa-user-shield me-2 text-black-50"></i>
                        <?= htmlspecialchars($row['username']) ?>
                      </td>
                      <td class="small text-muted">
                        <?= date('d M Y', strtotime($row['created_at'] ?? date('Y-m-d'))) ?>
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger px-3 fw-bold btn-delete"
                          data-href="admin-manage.php?delete_id=<?= $row['id'] ?>">
                          <i class="fa fa-trash-can me-1"></i> Hapus
                        </button>
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
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Logika untuk Pop-up Konfirmasi Hapus
    $('.btn-delete').on('click', function (e) {
      e.preventDefault();
      const href = $(this).attr('data-href');

      Swal.fire({
        title: 'Apakah kamu yakin?',
        text: "Hak akses staf admin ini akan dicabut secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545', // Warna merah untuk konfirmasi hapus
        cancelButtonColor: '#6c757d',  // Warna abu-abu untuk batal
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true // Membuat tombol batal di kiri dan hapus di kanan
      }).then((result) => {
        if (result.isConfirmed) {
          // Jika ditekan Ya, arahkan ke link penghapusan
          window.location.href = href;
        }
      });
    });

    // Panggilan otomatis alert sukses/gagal dari PHP sebelumnya
    <?php if (!empty($swalScript))
      echo $swalScript; ?>
  </script>
</body>

</html>