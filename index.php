<?php
require 'functions.php';

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
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
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
    <div class="header_section">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href=""><b>BoxKado</b></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="product.php">Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="aboutme.php">Tentang Kami</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- banner section start -->
        <div class="banner_section layout_padding">
            <div class="container">
                <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active">01</li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="1">02</li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="2">03</li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="3">04</li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h1 class="banner_taital">BoxKado</h1>
                                    <p class="banner_text">Temukan berbagai pilihan kado unik dan istimewa untuk orang-orang tercinta. Jadikan momen spesial lebih bermakna dengan BoxKado.</p>
                                    <div class="started_text">
                                        <a href="https://wa.me/6287823885784" target="_blank" target="_blank">Pesan Sekarang</a>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="banner_img"><img style="width: 300px;" src="assets/images/banner.png"></div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h1 class="banner_taital">BoxKado</h1>
                                    <p class="banner_text">Beragam koleksi kado kreatif dan personal siap melengkapi momen spesial Anda. Pilih dan pesan dengan mudah di BoxKado.</p>
                                    <div class="started_text">
                                        <a href="https://wa.me/6287823885784" target="_blank" target="_blank">Pesan Sekarang</a>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="banner_img"><img style="width: 300px;" src="assets/images/banner.png"></div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h1 class="banner_taital">BoxKado</h1>
                                    <p class="banner_text">Butuh ide kado unik? Kami siap membantu! BoxKado menyediakan berbagai pilihan untuk semua acara spesial Anda.</p>
                                    <div class="started_text">
                                        <a href="https://wa.me/6287823885784" target="_blank" target="_blank">Pesan Sekarang</a>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="banner_img"><img style="width: 300px;" src="assets/images/banner.png"></div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h1 class="banner_taital">BoxKado</h1>
                                    <p class="banner_text">Jadikan setiap hadiah lebih spesial dengan BoxKado. Kami hadir untuk membantu Anda memberikan yang terbaik.</p>
                                    <div class="started_text">
                                        <a href="https://wa.me/6287823885784" target="_blank" target="_blank">Pesan Sekarang</a>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="banner_img"><img style="width: 300px;" src="assets/images/banner.png"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- banner section end -->
    </div>
    <!-- header section end -->
    <!-- about sectuion start -->
    <div class="about_section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="about_img"><img src="assets/images/about-img.png"></div>
                </div>
                <div class="col-md-6">
                    <h1 class="about_taital">Tentang BoxKado</h1>
                    <p class="about_text">
                        BoxKado hadir untuk membantu Anda menemukan hadiah terbaik untuk setiap momen spesial. Kami menyediakan berbagai pilihan kado unik, kreatif, dan personal yang cocok untuk orang-orang tercinta. Dengan kualitas terbaik dan kemasan eksklusif, setiap kado dari BoxKado dibuat dengan penuh perhatian dan cinta.
                    </p>
                    <div class="read_bt_1"><a href="aboutme.php">Selengkapnya</a></div>
                </div>
            </div>
        </div>
    </div>
    <!-- about sectuion end -->
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
                            <option value="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" id="searchInput" class="form-control" style="width: 300px;" placeholder="Cari produk...">
                </div>
                <div class="row" id="productContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-3 mb-3 product-card" data-category="<?= htmlspecialchars($product['category']) ?>" data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                            <div class="card h-100" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#productModal<?= $product['id'] ?>">
                                <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text" style="color: #ff74a4; font-weight: bold;">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Detail Produk -->
                        <div class="modal fade" id="productModal<?= $product['id'] ?>" tabindex="-1" aria-labelledby="productModalLabel<?= $product['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="productModalLabel<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($product['name']) ?>">
                                        <p><strong>Tentang Produk:</strong> <?= nl2br(htmlspecialchars($product['about'])) ?></p>
                                        <p><strong>Warna:</strong> <?= htmlspecialchars($product['color']) ?></p>
                                        <p><strong>Ukuran:</strong> <?= htmlspecialchars($product['size']) ?></p>
                                        <p><strong>Kategori:</strong> <?= htmlspecialchars($product['category']) ?></p>
                                        <p><strong>Harga:</strong> Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                                        <p><strong>Status:</strong>
                                            <span class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($product['status']) ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="seemore_bt"><a href="product.php">Selengkapnya</a></div>
        </div>
    </div>
    <!-- cream sectuion end -->
    <!-- contact section start -->
    <div class="contact_section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="location_text">
                        <ul>
                            <li>
                                <a href="https://wa.me/6287823885784" target="_blank">
                                    <span class="padding_left_10"><i class="fa fa-phone" aria-hidden="true"></i></span>Whatsapp : +62 878 2388 5784
                                </a>
                            </li>
                            <li>
                                <a href="https://www.instagram.com/boxkado.banjarmasin?igsh=N2N4YjN1cjk0YWg2" target="_blank">
                                    <span class="padding_left_10"><i class="fa fa-instagram" aria-hidden="true"></i></span>Instagram : @boxkado.banjarmasin
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- contact section end -->
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
    <script src="assets/js/bootstrap.bundle.min.js"></script>
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

            products.forEach(function(product) {
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
    </script>
</body>

</html>