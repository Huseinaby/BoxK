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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/product.css">
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

                        <?php if ($user): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="cart.php">Keranjang</a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <ul class="navbar-nav ml-lg-auto align-items-lg-center">
                        <?php if ($user): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold text-dark" href="#" id="userDropdown"
                                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-user-circle me-1"></i> <?= htmlspecialchars($user['username']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 animate fade-In"
                                    aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item py-2" href="orders.php"><i class="fa fa-shopping-bag me-2"
                                                style="color: #ff74a4;"></i>Pesanan Saya</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item py-2 text-danger" href="admin/logout.php"><i
                                                class="fa fa-sign-out me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link fw-bold" href="admin/index.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bold" href="admin/register.php">Daftar</a>
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
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-card"
                            data-category="<?= htmlspecialchars($product['category']) ?>"
                            data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                            <div class="card h-100 product-inner-card">

                                <!-- Container Gambar -->
                                <div class="product-img-wrapper">
                                    <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>"
                                        class="product-image" alt="<?= htmlspecialchars($product['name']) ?>">
                                </div>

                                <div class="card-body d-flex flex-column p-3">
                                    <!-- Badge Kategori di Atas Judul -->
                                    <div class="mb-2">
                                        <span class="badge category-badge">
                                            <i class="fa fa-tag me-1"></i><?= htmlspecialchars($product['category']) ?>
                                        </span>
                                    </div>

                                    <!-- Judul Produk -->
                                    <h5 class="card-title text-truncate-2 mb-1"
                                        title="<?= htmlspecialchars($product['name']) ?>">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h5>

                                    <!-- Harga Produk -->
                                    <div class="price mb-2">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </div>

                                    <!-- Tombol Detail -->
                                    <button class="btn btn-detail w-100 mt-auto py-2" data-bs-toggle="modal"
                                        data-bs-target="#productModal<?= $product['id'] ?>">
                                        <i class="fa fa-eye me-2"></i>Lihat Detail
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div class="modal fade" id="productModal<?= $product['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold text-dark">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-4">
                                            <div class="col-md-5">
                                                <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>"
                                                    class="modal-product-image"
                                                    alt="<?= htmlspecialchars($product['name']) ?>">
                                            </div>
                                            <div class="col-md-7">
                                                <span class="badge category-badge mb-3">
                                                    <i
                                                        class="fa fa-tag me-1"></i><?= htmlspecialchars($product['category'] ?? '-') ?>
                                                </span>
                                                <p class="mb-2"><strong>Harga:</strong> Rp
                                                    <?= number_format($product['price'], 0, ',', '.') ?>
                                                </p>
                                                <p class="mb-2"><strong>Warna:</strong>
                                                    <?= htmlspecialchars($product['color'] ?? '-') ?>
                                                </p>
                                                <p class="mb-2"><strong>Ukuran:</strong>
                                                    <?= htmlspecialchars($product['size'] ?? '-') ?>
                                                </p>
                                                <p class="mb-2"><strong>Stok:</strong> <?= (int) ($product['stock'] ?? 0) ?>
                                                </p>
                                                <p class="mb-0 text-muted">
                                                    <?= nl2br(htmlspecialchars($product['about'] ?? '-')) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="emptyState" class="text-center py-5 my-4 d-none" style="min-height: 220px;">
                    <div class="d-inline-flex flex-column align-items-center justify-content-center px-4 py-5 rounded-4"
                        style="background: #fff8fb; border: 1px dashed #f4b8cc; max-width: 500px; margin: 0 auto;">
                        <i class="fa fa-search" style="font-size: 40px; color: #ff74a4;"></i>
                        <h5 class="mt-3 mb-2 text-dark">Produk tidak ditemukan</h5>
                        <p class="mb-0 text-muted">Coba ubah kata kunci atau pilih kategori lain.</p>
                    </div>
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
            var visibleCount = 0;
            var emptyState = document.getElementById('emptyState');

            products.forEach(function (product) {
                var productCategory = product.getAttribute('data-category').toLowerCase();
                var productName = product.getAttribute('data-name');

                var matchCategory = selectedCategory === "all" || productCategory === selectedCategory;
                var matchSearch = searchText === "" || productName.includes(searchText);
                var isVisible = matchCategory && matchSearch;

                product.style.display = isVisible ? "block" : "none";
                if (isVisible) visibleCount++;
            });

            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCount > 0);
            }
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