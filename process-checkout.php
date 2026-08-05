<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

// 1. Proteksi Halaman
if (!$user) {
  header('Location: admin/index.php');
  exit;
}

// 2. Pastikan diakses melalui metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: checkout.php');
  exit;
}

$userId = (int) $user['id'];

// 3. Ambil data keranjang user langsung dari database
$carts = getCartItems();

if (empty($carts)) {
  header('Location: cart.php');
  exit;
}

// 4. Tangkap dan Amankan Data Form Input
$shipping_method = $_POST['shipping_method'] ?? 'diantar';
$payment_method = $_POST['payment_method'] ?? 'transfer_bank';
$catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');// 4. Tangkap dan Amankan Data Form Input
$shipping_method = $_POST['shipping_method'] ?? 'diantar';
$payment_method = $_POST['payment_method'] ?? 'transfer_bank';
$catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

// Validasi nilai yang diperbolehkan
$allowedShipping = ['diantar', 'diambil'];
$allowedPayment = [
  'transfer_bank',
  'dompet_digital',
  'bayar_ditempat'
];

if (!in_array($shipping_method, $allowedShipping, true)) {
  $_SESSION['error'] = 'Metode pengiriman tidak valid.';
  header('Location: checkout.php');
  exit;
}

if (!in_array($payment_method, $allowedPayment, true)) {
  $_SESSION['error'] = 'Metode pembayaran tidak valid.';
  header('Location: checkout.php');
  exit;
}

// Bayar di tempat hanya boleh untuk pengambilan
if (
  $shipping_method !== 'diambil' &&
  $payment_method === 'bayar_ditempat'
) {
  $_SESSION['error'] = 'Bayar di tempat hanya tersedia untuk metode pengambilan.';
  header('Location: checkout.php');
  exit;
}

$nama_penerima = null;
$telepon = null;
$alamat_lengkap = null;
$shipping_cost = 0;

if ($shipping_method === 'diantar') {
  $nama_penerima = mysqli_real_escape_string($conn, $_POST['nama_penerima'] ?? '');
  $telepon = mysqli_real_escape_string($conn, $_POST['telepon'] ?? '');
  $alamat_lengkap = mysqli_real_escape_string($conn, $_POST['alamat_lengkap'] ?? '');
  $shipping_cost = 10000;
}

// 5. Hitung Total Harga Barang dari Database secara Valid
$total_items_price = 0;
foreach ($carts as $cart) {
  $total_items_price += ($cart['price'] * $cart['quantity']);
}
$grand_total = $total_items_price + $shipping_cost;

// 6. Generate Nomor Invoice Unik
$invoice_number = "INV-" . date('Ymd') . "-" . date('His') . rand(10, 99);

mysqli_begin_transaction($conn);

// 7. Mulai Simpan ke Database Menggunakan Prepared Statement
// Langkah A: Insert ke tabel `orders`
$queryOrder = mysqli_prepare($conn, "INSERT INTO orders (user_id, invoice_number, shipping_method, nama_penerima, telepon, alamat_lengkap, catatan, total_items_price, shipping_cost, grand_total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

mysqli_stmt_bind_param($queryOrder, 'issssssiiis', $userId, $invoice_number, $shipping_method, $nama_penerima, $telepon, $alamat_lengkap, $catatan, $total_items_price, $shipping_cost, $grand_total, $payment_method);

if (!mysqli_stmt_execute($queryOrder)) {
  mysqli_rollback($conn);
  echo "Terjadi kesalahan sistem saat memproses pesanan Anda: " . mysqli_error($conn);
  exit;
}

// Ambil ID pesanan yang baru saja dimasukkan
$orderId = mysqli_insert_id($conn);

// Langkah B: Insert item-item keranjang ke tabel `order_details` DAN POTONG STOK VARIAN
$queryDetail = mysqli_prepare($conn, "INSERT INTO order_details (order_id, variant_id, quantity, price) VALUES (?, ?, ?, ?)");

// Siapkan query untuk mengurangi stok varian
$queryUpdateStock = mysqli_prepare($conn, "UPDATE product_variants SET stock = stock - ? WHERE id = ?");

foreach ($carts as $cart) {
  $variantId = (int) $cart['variant_id'];
  $productId = (int) $cart['product_id'];
  $quantity = (int) $cart['quantity'];
  $price = (int) $cart['price'];
  $stock = (int) $cart['stock'];

  if ($variantId <= 0) {
    mysqli_rollback($conn);
    echo "Varian produk tidak valid pada salah satu item keranjang.";
    exit;
  }

  if ($quantity > $stock) {
    mysqli_rollback($conn);
    echo "Stok varian untuk salah satu item tidak mencukupi.";
    exit;
  }

  // 1. Simpan rincian ke order_details
  mysqli_stmt_bind_param($queryDetail, 'iiii', $orderId, $variantId, $quantity, $price);
  if (!mysqli_stmt_execute($queryDetail)) {
    mysqli_rollback($conn);
    echo "Gagal menyimpan detail pesanan.";
    exit;
  }

  // 2. JALANKAN PENGURANGAN STOK VARIAN DI SINI
  mysqli_stmt_bind_param($queryUpdateStock, 'ii', $quantity, $variantId);
  if (!mysqli_stmt_execute($queryUpdateStock)) {
    mysqli_rollback($conn);
    echo "Gagal memperbarui stok varian.";
    exit;
  }
}

// Langkah C: Kosongkan keranjang belanja milik user karena transaksi sudah berhasil dibuat
$queryClearCart = mysqli_prepare($conn, "DELETE FROM carts WHERE user_id = ?");
mysqli_stmt_bind_param($queryClearCart, 'i', $userId);
mysqli_stmt_execute($queryClearCart);

mysqli_commit($conn);

// Langkah D: Alihkan ke halaman Invoice sukses/instruksi pembayaran, sertakan ID pesanan di URL
header("Location: invoice.php?id=" . $orderId);
exit;