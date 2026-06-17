<?php
session_start();

$user = $_SESSION['user'] ?? null;
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
                        <li class="nav-item">
                            <a class="nav-link" href="product.php">Produk</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="aboutme.php">Tentang Kami</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ml-lg-auto align-items-lg-center">
                        <?php if ($user): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                    data-toggle="dropdown" aria-expanded="false">
                                    <?= htmlspecialchars($user['username']) ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                    <a class="dropdown-item" href="pelanggan/logout.php">Logout</a>
                                </div>
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
    <!-- about section start -->
    <div class="about_section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="about_img"><img src="assets/images/about-img.png"></div>
                </div>
                <div class="col-md-6">
                    <h1 class="about_taital">Tentang BoxKado</h1>
                    <p class="about_text">
                        BoxKado hadir untuk membantu Anda menemukan hadiah terbaik yang berkesan untuk setiap momen
                        spesial. Kami menyediakan berbagai pilihan kado unik, kreatif, dan personal yang cocok untuk
                        keluarga, teman, pasangan, hingga kolega.
                        <br><br>
                        Kami percaya bahwa setiap hadiah memiliki makna mendalam, bukan sekadar barang, tetapi juga
                        wujud perhatian dan kasih sayang. Oleh karena itu, kami menghadirkan koleksi kado yang dirancang
                        dengan penuh ketelitian, menggunakan bahan berkualitas tinggi serta kemasan eksklusif yang
                        elegan dan menarik.
                        <br><br>
                        Apakah Anda mencari hadiah ulang tahun, pernikahan, anniversary, atau hanya sekadar ingin
                        memberikan kejutan kecil yang manis? BoxKado siap membantu Anda! Dengan berbagai opsi
                        personalisasi, Anda dapat menambahkan sentuhan spesial agar hadiah lebih berkesan dan penuh
                        makna bagi penerima.
                        <br><br>
                        Kami berkomitmen untuk memberikan pengalaman belanja yang mudah, cepat, dan menyenangkan. Pilih
                        kado impian Anda, pesan dengan beberapa klik, dan biarkan kami mengurus sisanya hingga hadiah
                        istimewa Anda tiba di tangan orang terkasih.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- about section end -->

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
    <script src="assets/js/jquery-3.0.0.min.js"></script>
    <script src="assets/js/plugin.js"></script>
    <!-- sidebar -->
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <!-- javascript -->
</body>

</html>