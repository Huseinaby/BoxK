<?php
session_start();
require 'functions.php';

$user = $_SESSION['user'] ?? null;

$categories = getCategory();
$products = getProductLimit();
?>

<!DOCTYPE html>
<html>

<head>
    <!-- basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- mobile metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <!-- site metas -->
    <title>BoxKado</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- style css -->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- Responsive-->
    <link rel="stylesheet" href="assets/css/responsive.css">
    <!-- fevicon -->
    <link rel="icon" href="assets/images/boxkado-icon.png" type="image/gif" />
    <!-- font css -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css">
    <!-- Tweaks for older IEs-->
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
</head>

<body>
    <div class="header_section header_bg">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href=""><b>BoxKado</b></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse w-100" id="navbarSupportedContent">
                    <ul class="navbar-nav flex-grow-1 justify-content-lg-center align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="product.php">Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="aboutme.php">Tentang Kami</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">Keranjang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">Pesanan Saya</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-lg-auto align-items-lg-center">
                        <?php if ($user): ?>
                            <li class="nav-item dropdown">
                                <!-- Ubah data-toggle menjadi data-bs-toggle -->
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= htmlspecialchars($user['username']) ?>
                                </a>
                                <!-- Di Bootstrap 5, dropdown-menu-right diubah menjadi dropdown-menu-end -->
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="admin/logout.php">Logout</a></li>
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
    <!-- header section end -->
    <!-- cream sectuion start -->
    <div class="cream_section layout_padding mb-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="cream_taital">Produk BoxKado</h1>
                </div>
            </div>
            <div class="cream_section_2">
                <div class="d-flex justify-content-between mb-3">
                    <select id="categoryFilter" class="form-control" style="width: 200px;" onchange="filterProducts()">
                        <option value="all">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['name']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" id="searchInput" class="form-control" style="width: 300px;"
                        placeholder="Cari produk...">
                </div>
                <div class="row" id="productContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-3 mb-3 product-card"
                            data-category="<?= htmlspecialchars($product['category']) ?>"
                            data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                            <div class="card h-100" style="cursor: pointer;" data-bs-toggle="modal"
                                data-bs-target="#productModal<?= $product['id'] ?>">
                                <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top"
                                    alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text" style="color: #ff74a4; font-weight: bold;">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Detail Produk -->
                        <div class="modal fade" id="productModal<?= $product['id'] ?>" tabindex="-1"
                            aria-labelledby="productModalLabel<?= $product['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="productModalLabel<?= $product['id'] ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>"
                                            class="img-fluid mb-3" alt="<?= htmlspecialchars($product['name']) ?>">
                                        <p><strong>Tentang Produk:</strong>
                                            <?= nl2br(htmlspecialchars($product['about'])) ?></p>
                                        <p><strong>Warna:</strong> <?= htmlspecialchars($product['color']) ?></p>
                                        <p><strong>Ukuran:</strong> <?= htmlspecialchars($product['size']) ?></p>
                                        <p><strong>Kategori:</strong> <?= htmlspecialchars($product['category']) ?></p>
                                        <p><strong>Harga:</strong> Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                        </p>

                                        <p><strong>Stok Tersedia:</strong> <?= (int) $product['stock'] ?> pcs</p>

                                        <p><strong>Status:</strong>
                                            <span
                                                class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($product['status']) ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Tutup</button>
                                        <?php if ($product['status'] === 'tersedia'): ?>
                                            <?php if ($user): ?>
                                                <form onsubmit="addCartAjax(event, <?= $product['id'] ?>)">
                                                    <input type="hidden" name="action" value="addToCart">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    <input type="hidden" name="quantity" value=1>
                                                    <button type="submit" class="btn text-white" style="background-color: #ff74a4">
                                                        <i class="fa fa-shopping-cart"></i>Masukkan ke keranjang</button>
                                                </form>
                                            <?php else: ?>
                                                <a href="admin/index.php" class="btn btn-warning text-dark fw-bold">
                                                    <i class="fa fa-sign-in"></i> Login untuk Membeli
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn btn-danger" disabled>Stok Habis</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- cream sectuion end -->
    <!-- copyright section start -->
    <div class="copyright_section">
        <div class="container">
            <p class="copyright_text">2025 All Rights Reserved. &copy;2025 BoxKado</p>
        </div>
    </div>
    <!-- copyright section end -->
    <!-- Javascript files-->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery-3.0.0.min.js"></script>
    <script src="assets/js/plugin.js"></script>
    <!-- sidebar -->
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <!-- javascript -->
    <script>
        document.getElementById('searchInput').addEventListener('keyup', filterProducts);

        function filterProducts() {
            var selectedCategory = document.getElementById('categoryFilter').value.toLowerCase();
            var searchText = document.getElementById('searchInput').value.toLowerCase();
            var products = document.querySelectorAll('.product-card');

            products.forEach(function (product) {
                var productCategory = product.getAttribute('data-category').toLowerCase();
                var productName = product.getAttribute('data-name');

                var matchCategory = selectedCategory === "all" || productCategory === selectedCategory;
                var matchSearch = searchText === "" || productName.includes(searchText);

                if (matchCategory && matchSearch) {
                    product.style.display = "block";
                } else {
                    product.style.display = "none";
                }
            });
        }

        function addCartAjax(event, productId) {
            event.preventDefault(); // Menghentikan form agar tidak submit lewat URL browser

            const formData = new FormData();
            formData.append('action', 'addToCart');
            formData.append('product_id', productId);
            formData.append('quantity', 1);

            fetch('functions.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const toastElement = document.getElementById('successToast');

                    if (data.success) {
                        // 1. Tampilan jika BERHASIL: Set warna pink khas BoxKado (#ff74a4)
                        toastElement.style.backgroundColor = '#ff74a4';

                        document.getElementById('toastMessage').innerHTML = `<i class="fa fa-check-circle me-2"></i> ${data.message}`;

                        // Sembunyikan modal produk jika sedang terbuka
                        const modalElement = document.getElementById(`productModal${productId}`);
                        if (modalElement) {
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                        }
                    } else {
                        // 2. Tampilan jika GAGAL (Stok Habis / Belum Login): Set warna merah tegas (#dc3545)
                        toastElement.style.backgroundColor = '#dc3545';

                        document.getElementById('toastMessage').innerHTML = `<i class="fa fa-exclamation-triangle me-2"></i> ${data.message}`;
                    }

                    // 3. Inisialisasi dan memunculkan Toast kustom di layar
                    const toast = new bootstrap.Toast(toastElement, { delay: 4000 }); // Tampil selama 4 detik
                    toast.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback jika sistem crash total
                    const toastElement = document.getElementById('successToast');
                    toastElement.style.backgroundColor = '#dc3545';
                    document.getElementById('toastMessage').innerHTML = `<i class="fa fa-times-circle me-2"></i> Terjadi kesalahan sistem.`;
                    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
                    toast.show();
                });
        }
    </script>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <div id="successToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex py-1">
                <div class="toast-body fw-bold fs-6">
                    <span id="toastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
</body>

</html>