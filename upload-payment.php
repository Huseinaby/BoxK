<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

if (!$user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

$orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
$userId = (int) $user['id'];

if ($orderId <= 0 || !isset($_FILES['bukti_bayar'])) {
  header('Location: index.php');
  exit;
}

$file = $_FILES['bukti_bayar'];

// Validasi Gambar
$maxFileSize = 2 * 1024 * 1024; // 2MB
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
  header("Location: invoice.php?id=" . $orderId . "&error=ext");
  exit;
}

if ($file['size'] > $maxFileSize) {
  header("Location: invoice.php?id=" . $orderId . "&error=size");
  exit;
}

// Buat folder penampung resi jika belum ada
$targetDir = "assets/uploads/payments/";
if (!file_exists($targetDir)) {
  mkdir($targetDir, 0777, true);
}

// Beri nama file unik agar tidak bertabrakan (Contoh: PAY-IDPESANAN-TIMESTAMP.png)
$newFileName = "PAY-" . $orderId . "-" . time() . "." . $extension;
$targetFile = $targetDir . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
  // Update data nama file ke database orders milik user tersebut
  $query = mysqli_prepare($conn, "UPDATE orders SET bukti_pembayaran = ? WHERE id = ? AND user_id = ?");
  mysqli_stmt_bind_param($query, 'sii', $newFileName, $orderId, $userId);

  if (mysqli_stmt_execute($query)) {
    // Berhasil, kembalikan ke halaman invoice semula
    header("Location: invoice.php?id=" . $orderId);
    exit;
  } else {
    echo "Gagal memperbarui data di database.";
  }
} else {
  echo "Gagal mengunggah file bukti pembayaran.";
}