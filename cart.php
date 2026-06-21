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

    /* Menghilangkan tombol panah bawaan browser di input number */
    .custom-qty-input::-webkit-outer-spin-button,
    .custom-qty-input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    .custom-qty-input {
      -moz-appearance: textfield;
      border-left: none !important;
      border-right: none !important;
      font-weight: 500;
    }

    /* Mempercantik interaksi tombol minus/plus */
    .input-group .btn-outline-secondary {
      border-color: #dee2e6;
      color: #6c757d;
      transition: all 0.2s ease;
    }

    .input-group .btn-outline-secondary:hover {
      background-color: #ff74a4 !important;
      border-color: #ff74a4 !important;
      color: white !important;
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
            <li class="nav-item">
              <a class="nav-link" href="orders.php">Pesanan Saya</a>
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

                    <td width="160">
                      <div class="input-group input-group-sm justify-content-center">
                        <button type="button" class="btn btn-outline-secondary px-2 py-1"
                          onclick="changeQty(this, -1, <?= $cartId ?>)">
                          <i class="fa fa-minus"></i>
                        </button>

                        <input type="number" class="form-control text-center custom-qty-input bg-white"
                          value="<?= $cart['quantity'] ?>" min="1" readonly style="max-width: 60px;">

                        <button type="button" class="btn btn-outline-secondary px-2 py-1"
                          onclick="changeQty(this, 1, <?= $cartId ?>)">
                          <i class="fa fa-plus"></i>
                        </button>
                      </div>
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
    // Menyimpan instance modal secara global agar bisa ditutup lewat fungsi apa saja
    let deleteModalInstance = null;

    function deleteCartItem(cartId) {
      // 1. Inisialisasi dan tampilkan Bootstrap 5 Modal Konfirmasi
      const deleteModalElement = document.getElementById('deleteConfirmModal');
      deleteModalInstance = new bootstrap.Modal(deleteModalElement);
      deleteModalInstance.show();

      // 2. Ikat aksi klik tombol "Ya, Hapus" secara dinamis tepat saat modal muncul
      const btnDoDelete = document.getElementById('btnDoDelete');

      // Bersihkan event listener lama agar tidak terjadi penumpukan (double post)
      btnDoDelete.replaceWith(btnDoDelete.cloneNode(true));

      // Ambil kembali elemen tombol yang baru setelah di-clone
      const freshBtnDoDelete = document.getElementById('btnDoDelete');

      // Tambahkan event klik baru
      freshBtnDoDelete.addEventListener('click', function () {
        // Bungkus data cart_id ke dalam FormData
        const formData = new FormData();
        formData.append('action', 'deleteCartItem');
        formData.append('cart_id', cartId);

        // Kirim data ke backend via POST ke functions.php
        fetch('functions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Sembunyikan modal sebelum reload halaman
              if (deleteModalInstance) {
                deleteModalInstance.hide();
              }
              window.location.reload();
            } else {
              alert(data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem saat menghapus produk.');
          });
      });
    }

    function updateCartQuantity(cartId, newQty) {
      if (newQty < 1) return;

      const formData = new FormData();
      formData.append('action', 'updateCartQty');
      formData.append('cart_id', cartId);
      formData.append('quantity', newQty);

      fetch('functions.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.reload();
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan sistem saat memperbarui jumlah.');
        });
    }

    function changeQty(button, change, cartId) {
      // Mencari input terdekat di dalam grupnya
      const input = button.parentElement.querySelector('.custom-qty-input');
      let currentVal = parseInt(input.value) || 1;

      let newVal = currentVal + change;

      // Batasi agar jumlah tidak boleh kurang dari 1
      if (newVal >= 1) {
        input.value = newVal;
        // Panggil fungsi utama update ke database
        updateCartQuantity(cartId, newVal);
      }
    }
  </script>

  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="deleteConfirmModalLabel">Hapus Produk?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4">
          <div class="text-danger mb-3" style="font-size: 4.5rem; line-height: 1; font-weight: 300;">&times;</div>
          <p class="fs-5 mb-0">Apakah Anda yakin ingin menghapus produk ini dari keranjang?</p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
          <button type="button" id="btnDoDelete" class="btn btn-danger px-4">Ya, Hapus</button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>