<?php
session_start();

require '../functions.php';

requireAdminAccess();

$user = $_SESSION['user'];
$categories = getCategory();
$products = getProduct();
$csrfToken = csrfToken();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoxKado</title>
    <!-- fevicon -->
    <link rel="icon" href="../assets/images/boxkado-icon.png" type="image/gif" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #ff94c4;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand,
        .nav-link,
        .username {
            color: white !important;
            font-weight: bold;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: rgb(155, 69, 108);
            padding-top: 20px;
            position: fixed;
            color: white;
            transition: 0.3s;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .sidebar a:hover {
            background-color: #ff74a4;
            padding-left: 25px;
            transition: 0.3s;
        }

        .content {
            margin-left: 260px;
            padding: 20px;
            transition: 0.3s;
        }

        .card {
            border: none;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">BoxKado</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="d-flex">
        <div class="sidebar">
            <a href="dashboard.php">Dashboard</a>
            <a href="category.php">Kategori</a>
            <a href="product.php">Produk</a>
            <a href="orders.php">Pesanan</a>
        </div>
        <div class="content container">
            <h2 class="mt-4">Produk</h2>
            <button class="btn" style="background-color: #ff74a4; color: white; margin-bottom: 15px;"
                data-bs-toggle="modal" data-bs-target="#addProductModal">
                Tambah Produk
            </button>
            <div class="d-flex justify-content-between mb-3">
                <select id="categoryFilter" class="form-control" style="width: 200px;">
                    <option value="all">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="text" id="searchInput" class="form-control" style="width: 300px;"
                    placeholder="Cari produk...">
            </div>
            <div class="row" id="productContainer">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-3 mb-3 product-card" data-category="<?= (int) $product['category_id'] ?>"
                        data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                        <div class="card h-100" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#productModal<?= $product['id'] ?>">
                            <img src="../assets/uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top"
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
                                    <img src="../assets/uploads/<?= htmlspecialchars($product['image']) ?>"
                                        class="img-fluid mb-3" alt="<?= htmlspecialchars($product['name']) ?>">
                                    <p><strong>Tentang Produk:</strong> <?= nl2br(htmlspecialchars($product['about'])) ?>
                                    </p>
                                    <p><strong>Warna:</strong> <?= htmlspecialchars($product['color']) ?></p>
                                    <p><strong>Ukuran:</strong> <?= htmlspecialchars($product['size']) ?></p>
                                    <p><strong>Kategori:</strong> <?= htmlspecialchars($product['category'] ?? '-') ?></p>
                                    <p><strong>Harga:</strong> Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                                    <p><strong>Status:</strong>
                                        <span
                                            class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($product['status']) ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editProductModal<?= $product['id'] ?>">
                                        Edit Produk
                                    </button>
                                    <button class="btn btn-danger"
                                        onclick="deleteProduct(<?php echo $product['id']; ?>)">Hapus Produk</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit Produk -->
                    <div class="modal fade" id="editProductModal<?= $product['id'] ?>" tabindex="-1"
                        aria-labelledby="editProductModalLabel<?= $product['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Produk: <?= htmlspecialchars($product['name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editProductForm<?php echo $product['id']; ?>" data-id="<?= $product['id'] ?>">
                                        <input type="hidden" name="editProductId" value="<?= $product['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Produk</label>
                                            <input type="text" name="editName" id="editName<?= $product['id'] ?>"
                                                class="form-control editName"
                                                value="<?= htmlspecialchars($product['name']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tentang Produk</label>
                                            <input type="text" name="editAbout" id="editAbout<?= $product['id'] ?>"
                                                class="form-control editAbout"
                                                value="<?= htmlspecialchars($product['about']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Harga</label>
                                            <input type="number" name="editPrice" id="editPrice<?= $product['id'] ?>"
                                                class="form-control editPrice" value="<?= $product['price'] ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Warna</label>
                                            <input type="text" name="editColor" id="editColor<?= $product['id'] ?>"
                                                class="form-control editColor"
                                                value="<?= htmlspecialchars($product['color']) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Ukuran</label>
                                            <input type="text" name="editSize" id="editSize<?= $product['id'] ?>"
                                                class="form-control editSize"
                                                value="<?= htmlspecialchars($product['size']) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Kategori</label>
                                            <select name="editCategory" id="editCategory<?= $product['id'] ?>"
                                                class="form-control editCategory">
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= (int) $category['id'] ?>"
                                                        <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="editStatus" id="editStatus<?= $product['id'] ?>"
                                                class="form-control editStatus">
                                                <option value="tersedia" <?= $product['status'] == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                                <option value="habis" <?= $product['status'] == 'habis' ? 'selected' : '' ?>>Habis</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Gambar Produk</label>
                                            <input type="file" name="editImage" id="editImage<?= $product['id'] ?>"
                                                class="form-control editImage">
                                            <small>* Biarkan kosong jika tidak ingin mengubah gambar.</small>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary"
                                                style="background-color: #ff74a4; color: white;">Simpan Perubahan</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" id="productName" name="productName"
                                placeholder="Masukkan nama produk" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="productAbout" class="form-label">Tentang Produk</label>
                            <textarea class="form-control" id="productAbout" name="productAbout"
                                placeholder="Deskripsi produk"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="productColor" class="form-label">Warna</label>
                            <input type="text" class="form-control" id="productColor" name="productColor"
                                placeholder="Masukkan warna produk">
                        </div>
                        <div class="mb-3">
                            <label for="productSize" class="form-label">Ukuran</label>
                            <input type="text" class="form-control" id="productSize" name="productSize"
                                placeholder="Masukkan ukuran produk">
                        </div>
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Kategori</label>
                            <select class="form-control" id="productCategory" name="productCategory">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="productPrice" class="form-label">Harga</label>
                            <input type="number" class="form-control" id="productPrice" name="productPrice"
                                placeholder="Masukkan harga">
                        </div>
                        <div class="mb-3">
                            <label for="productStatus" class="form-label">Status</label>
                            <select class="form-control" id="productStatus" name="productStatus">
                                <option value="tersedia">Tersedia</option>
                                <option value="habis">Habis</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="productImage" class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" id="productImage" name="productImage">
                        </div>
                        <button type="submit" class="btn"
                            style="background-color: #ff74a4; color: white;">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add SweetAlert2 CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var csrfToken = <?= json_encode($csrfToken) ?>;

        function parseJsonResponse(response) {
            try {
                return JSON.parse(response);
            } catch (error) {
                return null;
            }
        }

        function showSuccessAndReload(title, message) {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                confirmButtonColor: '#ff94c4'
            }).then(function () {
                location.reload();
            });
        }

        function showError(message, title) {
            Swal.fire({
                icon: 'error',
                title: title || 'Oops...',
                text: message,
                confirmButtonColor: '#ff94c4'
            });
        }

        function closeModal(modalId) {
            var modalElement = document.getElementById(modalId);
            if (!modalElement) {
                return;
            }

            var modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        function buildEditProductFormData(formElement) {
            var $form = $(formElement);
            var fileInput = $form.find('.editImage')[0];
            var fileToUpload = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

            var formData = new FormData();
            formData.append('action', 'editproduct');
            formData.append('id', $form.data('id'));
            formData.append('productName', $form.find('.editName').val());
            formData.append('productDesc', $form.find('.editAbout').val());
            formData.append('productPrice', $form.find('.editPrice').val());
            formData.append('productColor', $form.find('.editColor').val());
            formData.append('productSize', $form.find('.editSize').val());
            formData.append('productCategory', $form.find('.editCategory').val());
            formData.append('productStatus', $form.find('.editStatus').val());
            formData.append('csrf_token', csrfToken);

            if (fileToUpload) {
                formData.append('productImage', fileToUpload);
            }

            return formData;
        }

        $(document).ready(function () {
            $("#addProductForm").submit(function (e) {
                e.preventDefault();

                var productName = $('#productName').val();
                var productAbout = $('#productAbout').val();
                var productColor = $('#productColor').val();
                var productSize = $('#productSize').val();
                var productCategory = $('#productCategory').val();
                var productPrice = $('#productPrice').val();
                var productStatus = $('#productStatus').val();
                var productImage = $('#productImage')[0].files[0];

                if (!productName || !productAbout || !productColor || !productSize || !productCategory || !productPrice || !productStatus || !productImage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Semua kolom harus diisi!',
                        confirmButtonColor: '#ff94c4'
                    });
                    return;
                }

                var formData = new FormData();
                formData.append('action', 'addProduct');
                formData.append('productName', productName);
                formData.append('productAbout', productAbout);
                formData.append('productColor', productColor);
                formData.append('productSize', productSize);
                formData.append('productCategory', productCategory);
                formData.append('productPrice', productPrice);
                formData.append('productStatus', productStatus);
                formData.append('productImage', productImage);
                formData.append('csrf_token', csrfToken);

                $.ajax({
                    url: '../functions.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        var data = parseJsonResponse(response);

                        if (!data) {
                            showError('Format respons tidak valid.');
                            return;
                        }

                        if (data.success) {
                            showSuccessAndReload('Berhasil', data.message);
                        } else {
                            showError(data.message, 'Gagal');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('Error:', error);
                        showError('Terjadi kesalahan, silakan coba lagi.');
                    }
                });
            });

            $(document).on('submit', "form[id^='editProductForm']", function (e) {
                e.preventDefault();

                var productName = $(this).find('.editName').val();
                var productDesc = $(this).find('.editAbout').val();
                var productPrice = $(this).find('.editPrice').val();
                var productCategory = $(this).find('.editCategory').val();
                var productStatus = $(this).find('.editStatus').val();

                if (!productName || !productDesc || !productPrice || !productCategory || !productStatus) {
                    showError('All fields are required!');
                    return;
                }

                var formData = buildEditProductFormData(this);
                var modalId = 'editProductModal' + $(this).data('id');

                $.ajax({
                    url: '../functions.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        var data = parseJsonResponse(response);

                        if (!data) {
                            showError('Format respons tidak valid.');
                            return;
                        }

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message
                            }).then(function () {
                                closeModal(modalId);
                                location.reload();
                            });
                        } else {
                            showError(data.message, 'Error');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('Error:', error);
                        showError('An error occurred. Please try again.');
                    }
                });
            });
        });

        document.getElementById('categoryFilter').addEventListener('change', filterProducts);
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

        function deleteProduct(productId) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Anda tidak akan bisa mengembalikannya!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                confirmButtonColor: '#ff94c4'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../functions.php',
                        type: 'POST',
                        data: {
                            action: 'deleteproduct',
                            id: productId,
                            csrf_token: csrfToken
                        },
                        success: function (response) {
                            var data = parseJsonResponse(response);

                            if (!data) {
                                showError('Format respons tidak valid.');
                                return;
                            }

                            if (data.success) {
                                showSuccessAndReload('Produk Dihapus', data.message);
                            } else {
                                showError(data.message, 'Error');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log('Error:', error);
                            showError('Terjadi kesalahan saat menghapus produk.');
                        }
                    });
                }
            });
        }
    </script>

</html>