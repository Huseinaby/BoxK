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

// 3. Ambil data keranjang user langsung dari database (menggunakan fungsi getCartItems Anda)
$carts = getCartItems();

if (empty($carts)) {
  header('Location: cart.php');
  exit;
}

// 4. Tangkap dan Amankan Data Form Input
$shipping_method = $_POST['shipping_method'] ?? 'diantar';
$payment_method = $_POST['payment_method'] ?? 'transfer_bank';
$catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

// Inisialisasi variabel alamat (nullable jika diambil sendiri)
$nama_penerima = null;
$telepon = null;
$alamat_lengkap = null;
$shipping_cost = 0; // Default diambil = 0

if ($shipping_method === 'diantar') {
  $nama_penerima = mysqli_real_escape_string($conn, $_POST['nama_penerima'] ?? '');
  $telepon = mysqli_real_escape_string($conn, $_POST['telepon'] ?? '');
  $alamat_lengkap = mysqli_real_escape_string($conn, $_POST['alamat_lengkap'] ?? '');
  $shipping_cost = 10000; // Ongkir jika diantar pegawai
}

// 5. Hitung Total Harga Barang dari Database secara Valid
$total_items_price = 0;
foreach ($carts as $cart) {
  $total_items_price += ($cart['price'] * $cart['quantity']);
}
$grand_total = $total_items_price + $shipping_cost;

// 6. Generate Nomor Invoice Unik (Format: INV - TAHUNBULANTANGGAL - JAMMENITDETIK)
$invoice_number = "INV-" . date('Ymd') . "-" . date('His') . rand(10, 99);

// 7. Mulai Simpan ke Database Menggunakan Prepared Statement
// Langkah A: Insert ke tabel `orders`
$queryOrder = mysqli_prepare($conn, "INSERT INTO orders (user_id, invoice_number, shipping_method, nama_penerima, telepon, alamat_lengkap, catatan, total_items_price, shipping_cost, grand_total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

mysqli_stmt_bind_param($queryOrder, 'issssssiiis', $userId, $invoice_number, $shipping_method, $nama_penerima, $telepon, $alamat_lengkap, $catatan, $total_items_price, $shipping_cost, $grand_total, $payment_method);

if (mysqli_stmt_execute($queryOrder)) {
  // Ambil ID pesanan yang baru saja dimasukkan
  $orderId = mysqli_insert_id($conn);

  // Langkah B: Insert item-item keranjang ke tabel `order_details`
  $queryDetail = mysqli_prepare($conn, "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

  foreach ($carts as $cart) {
    $productId = (int) $cart['product_id'];
    $quantity = (int) $cart['quantity'];
    $price = (int) $cart['price'];

    mysqli_stmt_bind_param($queryDetail, 'iiii', $orderId, $productId, $quantity, $price);
    mysqli_stmt_execute($queryDetail);
  }

  // Langkah C: Kosongkan keranjang belanja milik user karena transaksi sudah berhasil dibuat
  $queryClearCart = mysqli_prepare($conn, "DELETE FROM carts WHERE user_id = ?");
  mysqli_stmt_bind_param($queryClearCart, 'i', $userId);
  mysqli_stmt_execute($queryClearCart);

  // Langkah D: Alihkan ke halaman Invoice sukses/instruksi pembayaran, sertakan ID pesanan di URL
  header("Location: invoice.php?id=" . $orderId);
  exit;

} else {
  // Jika gagal insert ke orders
  echo "Terjadi kesalahan sistem saat memproses pesanan Anda: " . mysqli_error($conn);
  exit;
}