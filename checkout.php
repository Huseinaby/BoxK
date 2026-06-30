<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

// Proteksi halaman: Jika belum login, tendang ke login
if (!$user) {
  header('Location: admin/index.php');
  exit;
}

// Ambil data keranjang user untuk ringkasan pesanan
$carts = getCartItems();

// Jika keranjang kosong, tidak boleh ke checkout, kembalikan ke cart.php
if (empty($carts)) {
  header('Location: cart.php');
  exit;
}

$totalHargaBarang = 0;
// Default awal diantar, ongkir 10k rata
$ongkosKirimDefault = 10000;
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Checkout - BoxKado</title>

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

    .product-checkout-img {
      width: 50px;
      height: 50px;
      object-fit: cover;
    }

    /* Efek transisi halus saat form alamat disembunyikan/muncul */
    #sectionAlamat {
      transition: all 0.3s ease;
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
  <div class="cream_section layout_padding mb-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12 mb-4">
          <h1 class="cream_taital">Checkout Pesanan</h1>
        </div>
      </div>

      <form id="formCheckout" method="POST" action="process-checkout.php">
        <div class="row">

          <div class="col-lg-7 mb-4">

            <div class="card shadow-sm border-0 mb-4">
              <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: #ff74a4;"><i class="fa fa-truck me-2"></i>Metode Pengiriman</h5>
                <hr>

                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-check p-3 border rounded">
                      <input class="form-check-input ms-1" type="radio" name="shipping_method" id="shipDeliver"
                        value="diantar" checked onchange="toggleShipping(this.value)">
                      <label class="form-check-label ms-3 fw-bold" for="shipDeliver">
                        Diantar
                        <small class="d-block text-muted fw-normal mt-1">Diantar langsung ke alamatmu (+Rp
                          10.000)</small>
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check p-3 border rounded">
                      <input class="form-check-input ms-1" type="radio" name="shipping_method" id="shipPickup"
                        value="diambil" onchange="toggleShipping(this.value)">
                      <label class="form-check-label ms-3 fw-bold" for="shipPickup">
                        Diambil Sendiri
                        <small class="d-block text-muted fw-normal mt-1">Ambil langsung ke toko offline BoxKado</small>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card shadow-sm border-0 mb-4" id="sectionAlamat">
              <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: #ff74a4;"><i class="fa fa-map-marker me-2"></i>Alamat Pengiriman
                </h5>
                <hr>

                <div class="row g-3">
                  <div class="col-md-12">
                    <label class="form-label font-weight-bold">Nama Penerima</label>
                    <input type="text" name="nama_penerima" id="inputNama" class="form-control" required
                      placeholder="Contoh: Muhammad Abu Husein">
                  </div>

                  <div class="col-md-12">
                    <label class="form-label font-weight-bold">Nomor Telepon / WhatsApp</label>
                    <input type="tel" name="telepon" id="inputTelp" class="form-control" required
                      placeholder="Contoh: 08123456789">
                  </div>

                  <div class="col-md-12">
                    <label class="form-label font-weight-bold">Alamat Lengkap</label>
                    <textarea name="alamat_lengkap" id="inputAlamat" class="form-control" rows="3" required
                      placeholder="Nama jalan, nomor rumah, RT/RW, Kelurahan, Kecamatan"></textarea>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label font-weight-bold">Catatan Kado (Opsional)</label>
                    <input type="text" name="catatan" class="form-control"
                      placeholder="Contoh: Tulis ucapan: Happy Graduation!">
                  </div>
                </div>
              </div>
            </div>

            <div class="card shadow-sm border-0">
              <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: #ff74a4;"><i class="fa fa-credit-card me-2"></i>Metode Pembayaran
                </h5>
                <hr>

                <div class="form-check p-3 border rounded mb-2">
                  <input class="form-check-input ms-1" type="radio" name="payment_method" id="payBank"
                    value="transfer_bank" checked>
                  <label class="form-check-label ms-3 fw-bold" for="payBank">
                    Transfer Bank (Manual)
                    <small class="d-block text-muted fw-normal mt-1">Transfer ke Rekening
                      BoxKado</small>
                  </label>
                </div>

                <div class="form-check p-3 border rounded mb-2">
                  <input class="form-check-input ms-1" type="radio" name="payment_method" id="payDigital"
                    value="dompet_digital">
                  <label class="form-check-label ms-3 fw-bold" for="payDigital">
                    QRIS
                    <small class="d-block text-muted fw-normal mt-1">Scan QRIS BoxKado saat
                      konfirmasi.</small>
                  </label>
                </div>
              </div>
            </div>

          </div>

          <div class="col-lg-5">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
              <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="fa fa-shopping-bag me-2"></i>Ringkasan Pesanan</h5>
                <hr>

                <div class="overflow-auto mb-3" style="max-height: 240px;">
                  <?php foreach ($carts as $cart): ?>
                    <?php
                    $subtotal = $cart['price'] * $cart['quantity'];
                    $totalHargaBarang += $subtotal;
                    ?>
                    <div class="d-flex align-items-center mb-3">
                      <img src="assets/uploads/<?= htmlspecialchars($cart['image']) ?>"
                        class="rounded border product-checkout-img me-3">
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-truncate" style="max-width: 180px;">
                          <?= htmlspecialchars($cart['name']) ?>
                        </h6>
                        <small class="text-muted"><?= $cart['quantity'] ?>x @ Rp
                          <?= number_format($cart['price'], 0, ',', '.') ?></small>
                      </div>
                      <span class="fw-bold text-secondary text-end small">
                        Rp <?= number_format($subtotal, 0, ',', '.') ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Total Harga Barang</span>
                  <span>Rp <?= number_format($totalHargaBarang, 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                  <span class="text-muted">Ongkos Kirim</span>
                  <span id="txtOngkir">Rp <?= number_format($ongkosKirimDefault, 0, ',', '.') ?></span>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-4">
                  <span class="h5 fw-bold">Total Pembayaran</span>
                  <span class="h5 fw-bold text-danger" id="txtTotalBayar">Rp
                    <?= number_format($totalHargaBarang + $ongkosKirimDefault, 0, ',', '.') ?></span>
                </div>

                <button type="submit" class="btn btn-block w-100 text-white fw-bold py-3"
                  style="background-color: #ff74a4; font-size: 1.1rem;">
                  <i class="fa fa-check-circle me-2"></i>Buat Pesanan Sekarang
                </button>
              </div>
            </div>
          </div>

        </div>
      </form>

    </div>
  </div>

  <div class="copyright_section mt-auto">
    <div class="container">
      <p class="copyright_text">2025 All Rights Reserved. &copy;2025 BoxKado</p>
    </div>
  </div>

  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Menyimpan nilai dasar harga barang dari PHP ke JS
    const totalHargaBarang = <?= $totalHargaBarang ?>;

    function toggleShipping(method) {
      const sectionAlamat = document.getElementById('sectionAlamat');
      const txtOngkir = document.getElementById('txtOngkir');
      const txtTotalBayar = document.getElementById('txtTotalBayar');

      const inputs = [
        document.getElementById('inputNama'),
        document.getElementById('inputTelp'),
        document.getElementById('inputAlamat')
      ];

      if (method === 'diantar') {
        // 1. Munculkan kembali form alamat
        sectionAlamat.style.display = 'block';

        // 2. Wajibkan kembali pengisian form (required)
        inputs.forEach(input => input.required = true);

        // 3. Set Ongkir 10k & Hitung Total Baru
        let totalBayar = totalHargaBarang + 10000;
        txtOngkir.innerText = "Rp 10.000";
        txtTotalBayar.innerText = "Rp " + totalBayar.toLocaleString('id-ID');

      } else if (method === 'diambil') {
        // 1. Sembunyikan form alamat
        sectionAlamat.style.display = 'none';

        // 2. Matikan required agar form bisa di-submit walau kosong
        inputs.forEach(input => input.required = false);

        // 3. Set Ongkir 0 & Hitung Total Baru
        txtOngkir.innerText = "-";
        txtTotalBayar.innerText = "Rp " + totalHargaBarang.toLocaleString('id-ID');
      }
    }
  </script>

</body>

</html>