<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

// 1. Proteksi Halaman
if (!$user) {
  header('Location: admin/index.php');
  exit;
}

// 2. Ambil ID Pesanan dari URL
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($orderId <= 0) {
  header('Location: index.php');
  exit;
}

$userId = (int) $user['id'];

// 3. Ambil Data Induk Pesanan (Pastikan murni milik user yang sedang login)
$queryOrder = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($queryOrder, 'ii', $orderId, $userId);
mysqli_stmt_execute($queryOrder);
$resultOrder = mysqli_stmt_get_result($queryOrder);
$order = mysqli_fetch_assoc($resultOrder);

// Jika data pesanan tidak ditemukan, lempar balik ke home
if (!$order) {
  header('Location: index.php');
  exit;
}

// 4. Ambil Data Detail Item Produk yang Dibeli (Membaca JOIN ke tabel produk untuk ambil nama & gambar)
$queryItems = mysqli_prepare($conn, "
    SELECT od.*, pv.color AS variant_color, p.name AS product_name, p.price AS product_price,
           (
             SELECT pi.image
             FROM product_images pi
             WHERE pi.variant_id = pv.id
             ORDER BY pi.is_primary DESC, pi.id ASC
             LIMIT 1
           ) AS product_image
    FROM order_details od
    JOIN product_variants pv ON od.variant_id = pv.id
    JOIN product p ON pv.product_id = p.id
    WHERE od.order_id = ?
");
mysqli_stmt_bind_param($queryItems, 'i', $orderId);
mysqli_stmt_execute($queryItems);
$items = mysqli_stmt_get_result($queryItems);

// 5. Ambil Identitas Toko & Daftar Bank Dinamis dari Database
$shop = getShopIdentity();
if (!$shop) {
  $shop = ['shop_name' => 'BoxKado', 'whatsapp' => '', 'address' => '', 'qris_image' => ''];
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Invoice <?= htmlspecialchars($order['invoice_number']) ?> - <?= htmlspecialchars($shop['shop_name']) ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/responsive.css">
  <link rel="icon" href="assets/images/boxkado-icon.png" type="image/gif" />
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    .header_section {
      position: relative;
      z-index: 9999 !important;
    }

    .dropdown-menu {
      z-index: 10000 !important;
    }

    .invoice-card {
      border-radius: 12px;
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

    .border-pink {
      border-color: #ff74a4 !important;
    }

    .delivery-info-card {
      background: linear-gradient(135deg, #fff7fb 0%, #ffffff 100%);
      border: 1px solid #f4dce7;
      border-radius: 14px;
      padding: 0.9rem 1rem;
      margin-top: 0.7rem;
      box-shadow: 0 4px 12px rgba(255, 116, 164, 0.08);
    }

    .delivery-info-label {
      font-size: 0.82rem;
      font-weight: 700;
      color: #ff74a4;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      margin-bottom: 0.25rem;
    }

    .delivery-info-value {
      font-size: 1rem;
      font-weight: 700;
      color: #2f2f2f;
    }

    .copy-btn {
      border: none;
      background: #ff74a4;
      color: #fff;
      border-radius: 8px;
      padding: 0.35rem 0.7rem;
      font-size: 0.85rem;
      transition: background 0.2s ease;
    }

    .copy-btn:hover {
      background: #e95f93;
      color: #fff;
    }

    .copy-btn.success {
      background: #198754;
      color: #fff;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <div class="header_section header_bg">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php"><b><?= htmlspecialchars($shop['shop_name']) ?></b></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse w-100" id="navbarSupportedContent">
          <ul class="navbar-nav flex-grow-1 justify-content-lg-center align-items-lg-center">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="product.php">Produk</a></li>
            <li class="nav-item"><a class="nav-link" href="aboutme.php">Tentang Kami</a></li>
            <li class="nav-item"><a class="nav-link" href="cart.php">Keranjang</a></li>
            <li class="nav-item"><a class="nav-link" href="orders.php">Pesanan Saya</a></li>
          </ul>
          <ul class="navbar-nav ml-lg-auto align-items-lg-center">
            <?php if ($user): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                  <?= htmlspecialchars($user['username']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item text-danger" href="admin/logout.php">Logout</a></li>
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

      <div class="row justify-content-center">
        <div class="col-lg-9">
          <?php if ($order['status'] === 'pending' && empty($order['bukti_pembayaran'])): ?>
            <div class="alert alert-warning text-center py-3 mb-4 border-0 shadow-sm">
              <h4 class="fw-bold mb-1" style="color: #664d03;"><i class="fa fa-info-circle me-2"></i>Menunggu Pembayaran
              </h4>
              <p class="mb-0 small text-muted">Silakan lakukan transfer sesuai metode pilihan Anda dan unggah bukti bayar
                di bawah.</p>
            </div>
          <?php elseif ($order['status'] === 'pending' && !empty($order['bukti_pembayaran'])): ?>
            <div class="alert alert-info text-center py-3 mb-4 border-0 shadow-sm">
              <h4 class="fw-bold mb-1" style="color: #055160;"><i class="fa-regular fa-clock me-2"></i>Menunggu Verifikasi
                Admin</h4>
              <p class="mb-0 small text-muted">Bukti pembayaran Anda telah dikirim dan sedang diperiksa oleh tim kami.
                Mohon tunggu ya!</p>
            </div>
          <?php elseif ($order['status'] === 'proses'): ?>
            <div class="alert alert-primary text-center py-3 mb-4 border-0 shadow-sm">
              <h4 class="fw-bold mb-1"><i class="fa fa-refresh fa-spin me-2"></i>Pesanan Sedang Diproses</h4>
              <p class="mb-0 small text-muted">Pembayaran valid! Kado Anda sedang dipersiapkan atau dalam perjalanan oleh
                petugas.</p>
            </div>
          <?php elseif ($order['status'] === 'selesai'): ?>
            <div class="alert alert-success text-center py-3 mb-4 border-0 shadow-sm">
              <h4 class="fw-bold mb-1"><i class="fa fa-check-circle me-2"></i>Pesanan Selesai</h4>
              <p class="mb-0 small text-muted">Kado telah diterima/diambil. Terima kasih banyak telah memercayakan
                <?= htmlspecialchars($shop['shop_name']) ?>!
              </p>
            </div>
          <?php elseif ($order['status'] === 'dibatalkan'): ?>
            <div class="alert alert-danger text-center py-3 mb-4 border-0 shadow-sm">
              <h4 class="fw-bold mb-1"><i class="fa fa-times-circle me-2"></i>Pesanan Dibatalkan</h4>
              <p class="mb-0 small text-muted">Mohon maaf, pesanan ini telah dibatalkan oleh sistem atau admin.</p>
            </div>
          <?php endif; ?>

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
              <h3 class="fw-bold text-secondary mb-1">INVOICE PESANAN</h3>
              <span class="fw-bold text-muted">
                <?= htmlspecialchars($order['invoice_number']) ?>
              </span>
            </div>
            <div class="text-md-end mt-2 mt-md-0">
              <span class="badge px-3 py-2 fs-6 badge-<?= htmlspecialchars($order['status']) ?>">
                Status: <?= ucfirst(htmlspecialchars($order['status'])) ?>
              </span>
              <?php if ($order['shipping_method'] === 'diantar'): ?>
                <div class="delivery-info-card mt-3">
                  <div class="delivery-info-label">Kurir</div>
                  <div class="delivery-info-value"><?= htmlspecialchars($order['courier'] ?? '-') ?></div>
                  <div class="delivery-info-label mt-3">Nomor Tracking</div>
                  <div class="d-flex align-items-center justify-content-between gap-2 mt-1">
                    <div class="delivery-info-value flex-grow-1">
                      <?php
                      $trackingValue = trim((string) ($order['tracking_number'] ?? ''));
                      $trackingIsUrl = !empty($trackingValue) && preg_match('/^https?:\/\//i', $trackingValue) === 1;
                      if ($trackingIsUrl):
                        echo '<span class="text-muted">Link tracking</span>';
                      else:
                        echo htmlspecialchars($trackingValue ?: '-');
                      endif;
                      ?>
                    </div>
                    <?php if (!empty($trackingValue)): ?>
                      <button type="button" class="copy-btn"
                        data-original-label="<?= $trackingIsUrl ? 'Salin Link Tracking' : 'Salin' ?>"
                        onclick='copyTrackingValue(<?= json_encode($trackingValue, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, this)'>
                        <i class="fa fa-copy me-1"></i>
                        <span class="copy-btn-label"><?= $trackingIsUrl ? 'Salin Link Tracking' : 'Salin' ?></span>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
              <div class="text-muted small mt-1">Tanggal: <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
              </div>
            </div>
          </div>

          <hr>

          <div class="row g-4 my-2">
            <div class="col-md-6">
              <h6 class="fw-bold text-muted mb-2">Informasi Pengiriman:</h6>
              <p class="mb-1 fw-bold">
                <?= ucfirst(htmlspecialchars($order['shipping_method'])) ?>
              </p>
              <?php if ($order['shipping_method'] === 'diantar'): ?>
                <p class="mb-1 text-dark"><strong>Penerima:</strong>
                  <?= htmlspecialchars($order['nama_penerima']) ?> (<?= htmlspecialchars($order['telepon']) ?>)
                </p>
                <p class="mb-0 text-muted small">
                  <?= nl2br(htmlspecialchars($order['alamat_lengkap'])) ?>
                </p>
              <?php else: ?>
                <p class="mb-0 text-muted small">Silakan datang langsung ke toko offline
                  <strong><?= htmlspecialchars($shop['shop_name']) ?></strong> untuk mengambil pesanan Anda.
                </p>
                <?php if (!empty($shop['address'])): ?>
                  <small class="text-muted d-block mt-1"><i class="fa fa-location-dot me-1"></i> Lokasi:
                    <?= htmlspecialchars($shop['address']) ?></small>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (!empty($order['catatan'])): ?>
                <div class="mt-2 p-2 bg-light rounded border-start border-3 border-pink small">
                  <strong>Catatan Kado:</strong> "<?= htmlspecialchars($order['catatan']) ?>"
                </div>
              <?php endif; ?>
            </div>

            <div class="col-md-6 text-md-end">
              <h6 class="fw-bold text-muted mb-2">Metode Pembayaran:</h6>
              <p class="mb-1 fw-bold" style="color: #ff74a4;">
                <?= $order['payment_method'] === 'transfer_bank' ? 'Transfer Bank (Manual)' : 'Dompet Digital (QRIS / E-Wallet)' ?>
              </p>

              <div class="p-3 bg-light rounded text-start d-inline-block w-100 mt-2 border">
                <?php if ($order['payment_method'] === 'transfer_bank'): ?>
                  <small class="d-block fw-bold text-dark mb-2"><i class="fa fa-credit-card me-1 text-secondary"></i>
                    Silakan transfer ke rekening resmi kami:</small>
                  <?php
                  $banks = getShopBanks();
                  if (mysqli_num_rows($banks) == 0):
                    ?>
                    <span class="text-muted small d-block"><i>Belum ada rekening bank yang dikonfigurasi owner.</i></span>
                    <?php
                  else:
                    while ($b = mysqli_fetch_assoc($banks)):
                      ?>
                      <div class="mb-2 pb-2 border-bottom last-border-0">
                        <span class="d-block small fw-bold text-primary">Bank <?= htmlspecialchars($b['bank_name']) ?>:
                          <?= htmlspecialchars($b['account_number']) ?></span>
                        <small class="text-muted d-block">a.n. <?= htmlspecialchars($b['account_name']) ?></small>
                      </div>
                      <?php
                    endwhile;
                  endif;
                  ?>
                <?php else: ?>
                  <small class="d-block fw-bold text-dark mb-2 text-center"><i
                      class="fa fa-qrcode me-1 text-secondary"></i> Silakan scan QRIS
                    <?= htmlspecialchars($shop['shop_name']) ?>:</small>
                  <div class="text-center">
                    <?php if (!empty($shop['qris_image']) && file_exists('assets/uploads/' . $shop['qris_image'])): ?>
                      <img src="assets/uploads/<?= $shop['qris_image'] ?>" alt="QRIS Pembayaran"
                        class="img-fluid rounded border p-2 bg-white" style="max-height: 180px; object-fit: contain;">
                    <?php else: ?>
                      <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=BoxKado-Payment"
                        alt="QRIS Default" class="img-fluid rounded border p-2 bg-white" style="max-width: 150px;">
                    <?php endif; ?>
                  </div>
                  <small class="text-muted text-center d-block mt-2" style="font-size: 11px;">Mendukung QRIS M-Banking &
                    seluruh aplikasi E-Wallet lokal.</small>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="table-responsive mt-4">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Produk</th>
                  <th class="text-center">Harga Satuan</th>
                  <th class="text-center">Jumlah</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="assets/uploads/<?= htmlspecialchars($item['product_image']) ?>"
                          class="rounded border me-3" style="width: 45px; height: 45px; object-fit: cover;">
                        <div>
                          <span class="fw-bold text-dark text-truncate d-block" style="max-width: 250px;">
                            <?= htmlspecialchars($item['product_name']) ?>
                          </span>
                          <span class="text-muted small d-block">Warna:
                            <?= htmlspecialchars($item['variant_color'] ?? '-') ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-end fw-bold">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="text-end text-muted border-0 pt-3">Total Harga Barang:</td>
                  <td class="text-end border-0 pt-3">Rp <?= number_format($order['total_items_price'], 0, ',', '.') ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="3" class="text-end text-muted border-0 py-1">Ongkos Kirim:</td>
                  <td class="text-end border-0 py-1">
                    <?= $order['shipping_cost'] > 0 ? 'Rp ' . number_format($order['shipping_cost'], 0, ',', '.') : 'Gratis Ongkir' ?>
                  </td>
                </tr>
                <tr class="fs-5 fw-bold">
                  <td colspan="3" class="text-end border-0 pt-2">Total Pembayaran:</td>
                  <td class="text-end border-0 pt-2" style="color: #ff74a4;">Rp
                    <?= number_format($order['grand_total'], 0, ',', '.') ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <hr class="mt-4">

          <div class="mt-4 p-4 rounded bg-light">
            <?php if (empty($order['bukti_pembayaran'])): ?>
              <h5 class="fw-bold text-dark mb-2"><i class="fa fa-upload me-2" style="color: #ff74a4;"></i>Upload Bukti
                Pembayaran</h5>
              <p class="text-muted small">Harap unggah foto atau tangkapan layar (screenshot) resi transfer/bukti bayar
                yang sah (Format: JPG, JPEG, PNG, Maks 2MB).</p>

              <form action="upload-payment.php" method="POST" enctype="multipart/form-data" class="mt-3 mb-0">
                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                <div class="input-group">
                  <input type="file" name="bukti_bayar" class="form-control" accept="image/*" required>
                  <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #ff74a4;">
                    Kirim Bukti
                  </button>
                </div>
              </form>
            <?php else: ?>
              <div class="row align-items-center">
                <div class="col-md-7 mb-3 mb-md-0">
                  <h5 class="fw-bold text-success mb-1"><i class="fa fa-check-circle me-2"></i>Bukti Pembayaran Telah
                    Dikirim</h5>
                  <p class="text-muted small mb-0">Terima kasih! Bukti transfer Anda sudah terekam di sistem. Admin kami
                    akan segera melakukan verifikasi data dan mengubah status pesanan Anda.</p>
                </div>
                <div class="col-md-5 text-md-end">
                  <small class="d-block text-muted mb-1 font-weight-bold">Preview Bukti Anda:</small>
                  <a href="assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                    <img src="assets/uploads/payments/<?= htmlspecialchars($order['bukti_pembayaran']) ?>"
                      class="img-fluid rounded border shadow-sm" style="max-height: 100px; object-fit: cover;">
                  </a>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
              <a href="product.php" class="btn btn-outline-secondary px-4">
                <i class="fa fa-arrow-left me-2"></i>Kembali Belanja
              </a>
            </div>
            <div>
              <?php if ($order['status'] === 'pending' && empty($order['bukti_pembayaran'])): ?>
                <button type="button" class="btn btn-outline-danger fw-bold px-4" onclick="cancelOrder(<?= $orderId ?>)">
                  <i class="fa fa-times me-2"></i>Batalkan Pesanan Ini
                </button>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <div class="copyright_section mt-auto">
    <div class="container">
      <p class="copyright_text"><?= date('Y') ?> All Rights Reserved. &copy; <?= htmlspecialchars($shop['shop_name']) ?>
      </p>
    </div>
  </div>

  <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-labelledby="cancelConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="cancelConfirmModalLabel">Batalkan Pesanan?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4">
          <div class="text-danger mb-3" style="font-size: 4.5rem; line-height: 1; font-weight: 300;">&times;</div>
          <p class="fs-5 mb-1 fw-bold text-dark">Apakah Anda yakin ingin membatalkan pesanan ini?</p>
          <p class="text-muted small mb-0">Aksi ini tidak dapat dibatalkan setelah dieksekusi.</p>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Kembali</button>
          <button type="button" id="btnDoCancel" class="btn btn-danger px-4">Ya, Batalkan</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-50 start-50 translate-middle p-3" style="z-index: 1060;">
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0 shadow-lg" role="alert"
      aria-live="assertive" aria-atomic="true">
      <div class="d-flex py-2 px-1">
        <div class="toast-body fw-bold text-center w-100 fs-6">
          <?php
          if (isset($_GET['error']) && $_GET['error'] === 'ext') {
            echo '<i class="fa fa-exclamation-circle d-block mb-2 fa-2x"></i> Format Salah!<br><small class="fw-normal">Hanya file JPG, JPEG, dan PNG yang diperbolehkan.</small>';
          } elseif (isset($_GET['error']) && $_GET['error'] === 'size') {
            echo '<i class="fa fa-exclamation-circle d-block mb-2 fa-2x"></i> File Terlalu Besar!<br><small class="fw-normal">Maksimal ukuran gambar resi adalah 2 MB.</small>';
          }
          ?>
        </div>
      </div>
      <div class="text-center pb-2">
        <button type="button" class="btn btn-sm btn-light fw-bold px-3" data-bs-dismiss="toast">Oke</button>
      </div>
    </div>
  </div>

  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let cancelModalInstance = null;

    function copyTrackingValue(value, button) {
      if (!value) return;

      const textToCopy = String(value);

      const markSuccess = () => {
        if (button) {
          button.classList.add('success');
          const label = button.querySelector('.copy-btn-label');
          if (label) {
            label.textContent = 'Tersalin';
          }
          setTimeout(() => {
            button.classList.remove('success');
            if (label) {
              label.textContent = button.dataset.originalLabel || 'Salin';
            }
          }, 1500);
        }
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy).then(markSuccess).catch(() => {
          fallbackCopyText(textToCopy);
          markSuccess();
        });
      } else {
        fallbackCopyText(textToCopy);
        markSuccess();
      }
    }

    function fallbackCopyText(text) {
      const tempInput = document.createElement('textarea');
      tempInput.value = text;
      tempInput.setAttribute('readonly', '');
      tempInput.style.position = 'fixed';
      tempInput.style.top = '-9999px';
      document.body.appendChild(tempInput);
      tempInput.select();
      document.execCommand('copy');
      document.body.removeChild(tempInput);
    }

    function cancelOrder(orderId) {
      const cancelModalElement = document.getElementById('cancelConfirmModal');
      cancelModalInstance = new bootstrap.Modal(cancelModalElement);
      cancelModalInstance.show();

      const btnDoCancel = document.getElementById('btnDoCancel');
      btnDoCancel.replaceWith(btnDoCancel.cloneNode(true));
      const freshBtnDoCancel = document.getElementById('btnDoCancel');

      freshBtnDoCancel.addEventListener('click', function () {
        const formData = new FormData();
        formData.append('action', 'cancelOrderUser');
        formData.append('order_id', orderId);

        fetch('functions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              if (cancelModalInstance) {
                cancelModalInstance.hide();
              }
              window.location.reload();
            } else {
              alert(data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem saat membatalkan pesanan.');
          });
      });
    }

    document.addEventListener("DOMContentLoaded", function () {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('error')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.style.zIndex = '1055';
        document.body.appendChild(backdrop);

        const errorToastElement = document.getElementById('errorToast');
        const toast = new bootstrap.Toast(errorToastElement, {
          autohide: true,
          delay: 4000
        });
        toast.show();

        errorToastElement.addEventListener('hidden.bs.toast', function () {
          backdrop.remove();
        });

        window.history.replaceState({}, document.title, window.location.pathname + "?id=" + urlParams.get('id'));
      }
    });
  </script>
</body>

</html>