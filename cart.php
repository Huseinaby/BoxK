<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

// Proteksi halaman: Jika belum login, tendang ke halaman login
if (!$user) {
  header('Location: admin/index.php');
  exit;
}

// Ambil data keranjang user (menggunakan fungsi getCartItems yang sudah kita buat)
$carts = getCartItems();

$total = 0;
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Keranjang - BoxKado</title>

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
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

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

            <li class="nav-item active">
              <a class="nav-link" href="cart.php">Keranjang</a>
            </li>
          </ul>

          <ul class="navbar-nav ml-lg-auto align-items-lg-center">
            <?php if ($user): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                  <?= htmlspecialchars($user['username']) ?>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="admin/logout.php">
                      Logout
                    </a>
                  </li>
                </ul>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link" href="admin/index.php">Login</a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="admin/register.php">Daftar</a>
              </li>
            <?php endif; ?>
          </ul>

        </div>
      </nav>
    </div>
  </div>
  <div class="cream_section layout_padding mb-5">
    <div class="container">

      <div class="row">
        <div class="col-md-12">
          <h1 class="cream_taital">Keranjang Belanja</h1>
        </div>
      </div>

      <div class="cream_section_2 mt-4">

        <?php if (empty($carts)): ?>

          <div class="alert alert-info text-center py-5">
            <p class="mb-3">Keranjang belanja Anda masih kosong.</p>
            <a href="product.php" class="btn text-white px-4" style="background-color: #ff74a4;">Mulai Belanja</a>
          </div>

        <?php else: ?>

          <div class="table-responsive">

            <table class="table table-bordered align-middle text-center">

              <thead class="table-light">
                <tr>
                  <th>Gambar</th>
                  <th>Produk</th>
                  <th>Harga</th>
                  <th>Jumlah</th>
                  <th>Subtotal</th>
                  <th>Aksi</th>
                </tr>
              </thead>

              <tbody>

                <?php foreach ($carts as $cart): ?>

                  <?php
                  $cartId = isset($cart['cart_id']) ? (int) $cart['cart_id'] : 0; // Menggunakan alias cart_id dari JOIN
                  $subtotal = $cart['price'] * $cart['quantity'];
                  $total += $subtotal;
                  ?>

                  <tr>
                    <td width="100">
                      <img src="assets/uploads/<?= htmlspecialchars($cart['image']) ?>" class="img-fluid rounded"
                        alt="Produk">
                    </td>

                    <td class="text-start"><?= htmlspecialchars($cart['name']) ?></td>

                    <td class="text-end">
                      Rp <?= number_format($cart['price'], 0, ',', '.') ?>
                    </td>

                    <td width="130">
                      <input type="number" class="form-control text-center form-control-sm" value="<?= $cart['quantity'] ?>"
                        min="1" onchange="updateCartQuantity(<?= $cartId ?>, this.value)">
                    </td>

                    <td class="text-end fw-bold" style="color: #ff74a4;">
                      Rp <?= number_format($subtotal, 0, ',', '.') ?>
                    </td>

                    <td>
                      <button type="button" onclick="deleteCartItem(<?= $cartId ?>)" class="btn btn-danger btn-sm">
                        Hapus
                      </button>
                    </td>
                  </tr>

                <?php endforeach; ?>

              </tbody>

              <tfoot>
                <tr class="table-light fw-bold">
                  <th colspan="4" class="text-end">Total Bayar</th>
                  <th class="text-end" style="color: #ff74a4;">
                    Rp <?= number_format($total, 0, ',', '.') ?>
                  </th>
                  <th></th>
                </tr>
              </tfoot>

            </table>

          </div>

          <div class="text-end mt-4">
            <a href="checkout.php" class="btn text-white fw-bold px-4 py-2" style="background-color:#ff74a4;">
              Lanjut ke Checkout <i class="fa fa-arrow-right ms-1"></i>
            </a>
          </div>

        <?php endif; ?>

      </div>
    </div>
  </div>

  <div class="copyright_section mt-auto">
    <div class="container">
      <p class="copyright_text">
        2025 All Rights Reserved. &copy;2025 BoxKado
      </p>
    </div>
  </div>

  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/jquery-3.0.0.min.js"></script>
  <script src="assets/js/plugin.js"></script>
  <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
  <script src="assets/js/custom.js"></script>

  <script>
    function updateCartQuantity(cartId, newQty) {
      if (newQty < 1) return;
      console.log("Update cart ID " + cartId + " menjadi " + newQty);
      // Logika AJAX update kuantitas database dipasang di sini nanti
    }

    function deleteCartItem(cartId) {
      if (confirm("Apakah Anda yakin ingin menghapus produk ini dari keranjang?")) {
        console.log("Hapus cart ID " + cartId);
        // Logika AJAX hapus data database dipasang di sini nanti
      }
    }
  </script>

</body>

</html>