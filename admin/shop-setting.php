<?php
session_start();
require '../functions.php';

// Proteksi Ketat Khusus Owner
requireOwnerAccess();

$user = $_SESSION['user'];
$swalScript = '';

// 1. Ambil data identitas toko saat ini
$shop = getShopIdentity();
if (!$shop) {
  $shop = ['shop_name' => '', 'whatsapp' => '', 'address' => '', 'qris_image' => ''];
}

// 2. PROSES A: UPDATE PROFIL TOKO & QRIS
if (isset($_POST['save_settings'])) {
  $shop_name = mysqli_real_escape_string($conn, $_POST['shop_name']);
  $whatsapp = mysqli_real_escape_string($conn, $_POST['whatsapp']);
  $address = mysqli_real_escape_string($conn, $_POST['address']);
  $qris_image = $shop['qris_image'];

  echo '<pre>';
  print_r($_FILES);
  echo '</pre>';
  if ($_FILES['qris_image']['error'] === 0) {
    $fileName = $_FILES['qris_image']['name'];
    $fileTmp = $_FILES['qris_image']['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
      $newQrisName = 'qris_' . time() . '.' . $fileExt;
      if (move_uploaded_file($fileTmp, '../assets/uploads/' . $newQrisName)) {
        echo "MOVE BERHASIL";
      } else {
        die("MOVE GAGAL");
      }
    } else {
      $swalScript = "Swal.fire({icon:'error', title:'Gagal!', text:'Format QRIS harus JPG/PNG', confirmButtonColor:'#ff94c4'});";
    }
  }

  if (empty($swalScript)) {
    mysqli_query($conn, "UPDATE shop_identities SET shop_name='$shop_name', whatsapp='$whatsapp', address='$address', qris_image='$qris_image' WHERE id=1");
    $swalScript = "Swal.fire({icon:'success', title:'Berhasil!', text:'Profil toko berhasil diperbarui!', confirmButtonColor:'#ff94c4'}).then(function(){window.location.href='shop-setting.php';});";
  }
}

// 3. PROSES B: TAMBAH BANK BARU
if (isset($_POST['add_bank'])) {
  $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name']);
  $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
  $account_name = mysqli_real_escape_string($conn, $_POST['account_name']);

  $insertBank = mysqli_query($conn, "INSERT INTO shop_banks (bank_name, account_number, account_name) VALUES ('$bank_name', '$account_number', '$account_name')");
  if ($insertBank) {
    $swalScript = "Swal.fire({icon:'success', title:'Berhasil!', text:'Rekening bank berhasil ditambahkan!', confirmButtonColor:'#ff94c4'}).then(function(){window.location.href='shop-setting.php';});";
  }
}

// 4. PROSES C: HAPUS BANK
if (isset($_GET['delete_bank_id'])) {
  $delBankId = (int) $_GET['delete_bank_id'];
  mysqli_query($conn, "DELETE FROM shop_banks WHERE id = $delBankId");
  $swalScript = "Swal.fire({icon:'success', title:'Dihapus!', text:'Rekening bank berhasil dihapus!', confirmButtonColor:'#ff94c4'}).then(function(){window.location.href='shop-setting.php';});";
}

// 5. Ambil semua list bank dinamis
$banksResult = getShopBanks();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BoxKado - Pengaturan Toko</title>
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
    <!-- Sidebar Menu -->
    <div class="sidebar">
      <div class="menu-grup pt-3">
        <a href="dashboard.php"><i class="fa fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="category.php"><i class="fa fa-tags me-2"></i> Kategori</a>
        <a href="product.php"><i class="fa fa-gift me-2"></i> Produk</a>
        <a href="orders.php"><i class="fa fa-shopping-cart me-2"></i> Pesanan</a>
        <?php if ($user['role'] === 'owner'): ?>
          <a href="sales-report.php"><i class="fa fa-chart-line me-2"></i> Laporan Penjualan</a>
          <a href="shop-setting.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i
              class="fa fa-store me-2"></i> Kelola Toko</a>
          <a href="admin-manage.php"><i class="fa fa-user-gear me-2"></i> Kelola Admin</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Content Area -->
    <div class="content container mb-5">
      <div class="mt-4 mb-4">
        <h2 class="fw-bold text-dark mb-0">Kelola Identitas & Pembayaran Toko</h2>
        <p class="text-muted small">Atur identitas profil lapak BoxKado dan manajemen banyak rekening bank secara
          fleksibel.</p>
      </div>

      <div class="row">
        <!-- KOLOM KIRI: Identitas Toko & QRIS -->
        <div class="col-lg-6 mb-4">
          <form method="POST" enctype="multipart/form-data">
            <div class="card p-4 bg-white shadow-sm mb-4">
              <h5 class="fw-bold text-dark mb-3"><i class="fa fa-info-circle me-2 text-secondary"></i>Profil & Kontak
                Lapak</h5>
              <div class="mb-3">
                <label class="form-label small fw-bold">Nama Toko</label>
                <input type="text" name="shop_name" class="form-control"
                  value="<?= htmlspecialchars($shop['shop_name']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">No. WhatsApp Admin</label>
                <input type="text" name="whatsapp" class="form-control"
                  value="<?= htmlspecialchars($shop['whatsapp']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Alamat Toko</label>
                <textarea name="address" class="form-control" rows="2"
                  required><?= htmlspecialchars($shop['address']) ?></textarea>
              </div>
            </div>

            <div class="card p-4 bg-white shadow-sm">
              <h5 class="fw-bold text-dark mb-3"><i class="fa fa-qrcode me-2 text-secondary"></i>QRIS Central Payment
              </h5>
              <div class="text-center bg-light p-3 rounded border mb-3">
                <?php if (!empty($shop['qris_image']) && file_exists('../assets/uploads/' . $shop['qris_image'])): ?>
                  <img src="../assets/uploads/<?= $shop['qris_image'] ?>" alt="QRIS"
                    class="img-fluid rounded border bg-white p-1" style="max-height: 150px; object-fit: contain;">
                <?php else: ?>
                  <span class="d-block small text-muted fw-bold py-2">Belum ada gambar QRIS</span>
                <?php endif; ?>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Ganti File QRIS</label>
                <input type="file" name="qris_image" class="form-control">
              </div>
              <button type="submit" name="save_settings" class="btn text-white w-100 fw-bold py-2 shadow-sm"
                style="background-color: #ff74a4;">
                <i class="fa fa-floppy-disk me-1"></i> Simpan Profil Toko
              </button>
            </div>
          </form>
        </div>

        <!-- KOLOM KANAN: Manajemen Banyak Pilihan Bank (Dinamis) -->
        <div class="col-lg-6 mb-4">
          <!-- Form Tambah Rekening -->
          <div class="card p-4 bg-white shadow-sm mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fa fa-plus-circle me-2 text-success"></i>Tambah Rekening Bank
              Baru</h5>
            <form method="POST">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label small fw-bold">Nama Bank</label>
                  <input type="text" name="bank_name" class="form-control" placeholder="BCA / Mandiri" required>
                </div>
                <div class="col-md-8 mb-3">
                  <label class="form-label small fw-bold">Nomor Rekening</label>
                  <input type="text" name="account_number" class="form-control" placeholder="Masukkan nomor rekening"
                    required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Atas Nama (Nama Pemilik)</label>
                <input type="text" name="account_name" class="form-control" placeholder="Nama pemilik rekening"
                  required>
              </div>
              <button type="submit" name="add_bank" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                <i class="fa fa-circle-check me-1"></i> Tambahkan Rekening
              </button>
            </form>
          </div>

          <!-- Tabel Daftar Bank Aktif -->
          <div class="card p-4 bg-white shadow-sm">
            <h5 class="fw-bold text-dark mb-3"><i class="fa fa-credit-card me-2 text-secondary"></i>Daftar Rekening Bank
              Aktif</h5>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Bank</th>
                    <th>Detail Akun</th>
                    <th class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (mysqli_num_rows($banksResult) == 0): ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted py-3">Belum ada pilihan bank transfer.</td>
                    </tr>
                  <?php endif; ?>
                  <?php while ($b = mysqli_fetch_assoc($banksResult)): ?>
                    <tr>
                      <td><span class="badge bg-primary px-2 py-1 fs-6"><?= htmlspecialchars($b['bank_name']) ?></span>
                      </td>
                      <td>
                        <div class="fw-bold text-dark small"><?= htmlspecialchars($b['account_number']) ?></div>
                        <div class="text-muted text-xs" style="font-size: 12px;">a.n.
                          <?= htmlspecialchars($b['account_name']) ?>
                        </div>
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-bank"
                          data-href="shop-setting.php?delete_bank_id=<?= $b['id'] ?>">
                          <i class="fa fa-trash"></i>
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
    // SweetAlert2 Konfirmasi Hapus Bank
    $('.btn-delete-bank').on('click', function (e) {
      e.preventDefault();
      const href = $(this).attr('data-href');
      Swal.fire({
        title: 'Hapus rekening ini?',
        text: "Pelanggan tidak akan bisa memilih opsi bank transfer ini lagi!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) { window.location.href = href; }
      });
    });

    <?php if (!empty($swalScript))
      echo $swalScript; ?>
  </script>
</body>

</html>