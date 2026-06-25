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
    <title>BoxKado - Kelola Produk</title>
    <link rel="icon" href="../assets/images/boxkado-icon.png" type="image/gif" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #ff94c4;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            position: sticky !important;
  top: 0;
  z-index: 1030 !important;
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
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
        }

        .product-img-wrapper {
            height: 200px;
            overflow: hidden;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .product-card-hover:hover .product-img-wrapper img {
            transform: scale(1.05);
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
                            <i class="fa fa-user-shield me-1"></i> <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="d-flex">
        <div class="sidebar">
            <a href="dashboard.php"><i class="fa fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="category.php"><i class="fa fa-tags me-2"></i> Kategori</a>
            <a href="product.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i class="fa fa-gift me-2"></i> Produk</a>
            <a href="orders.php"><i class="fa fa-shopping-cart me-2"></i> Pesanan</a>            
            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'owner'): ?>
                <a href="sales-report.php"><i class="fa fa-chart-line me-2"></i> Laporan Penjualan</a>
                <a href="shop-setting.php"><i class="fa fa-store me-2"></i> Kelola Toko</a>
                <a href="admin-manage.php"><i class="fa fa-user-gear me-2"></i> Kelola Admin</a>
            <?php endif; ?>
        </div>

        <div class="content container">
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <div>
                    <h2 class="fw-bold text-dark mb-0">Kelola Produk</h2>
                    <p class="text-muted small mb-0">Atur katalog kado, harga, spesifikasi varian, beserta sisa stok barang.</p>
                </div>
                <button class="btn fw-bold px-3 text-white" style="background-color: #ff74a4;"
                    data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fa fa-plus-circle me-1"></i> Tambah Produk
                </button>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa fa-filter"></i></span>
                        <select id="categoryFilter" class="form-select border-start-0 ps-0">
                            <option value="all">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 ms-auto">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari kado impian...">
                    </div>
                </div>
            </div>

            <div class="row" id="productContainer">
                <?php foreach ($products as $product): ?>
                        <div class="col-md-3 mb-4 product-card" data-category="<?= (int) $product['category_id'] ?>"
                            data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                            <div class="card h-100 product-card-hover" style="cursor: pointer;" data-bs-toggle="modal"
                                data-bs-target="#productModal<?= $product['id'] ?>">
                                <div class="product-img-wrapper">
                                    <img src="../assets/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                </div>
                                <div class="card-body p-3">
                                    <h5 class="card-title text-dark fw-bold mb-1 small"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text mb-2 text-danger fw-bold fs-5">
                                        Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'danger' ?> px-2 py-1 small">
                                            <?= ucfirst($product['status']) ?>
                                        </span>
                                        <small class="text-muted fw-bold">Stok: <?= (int) $product['stock'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="productModal<?= $product['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold text-dark"><?= htmlspecialchars($product['name']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="../assets/uploads/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded border mb-3 w-100" style="max-height: 250px; object-fit: cover;">
                                        <table class="table table-sm table-borderless small mb-0">
                                            <tr><td width="30%" class="text-muted">Deskripsi</td><td>: <?= nl2br(htmlspecialchars($product['about'])) ?></td></tr>
                                            <tr><td class="text-muted">Pilihan Warna</td><td>: <?= htmlspecialchars($product['color']) ?></td></tr>
                                            <tr><td class="text-muted">Dimensi Ukuran</td><td>: <?= htmlspecialchars($product['size']) ?></td></tr>
                                            <tr><td class="text-muted">Kategori</td><td>: <span class="badge bg-secondary"><?= htmlspecialchars($product['category'] ?? '-') ?></span></td></tr>
                                            <tr><td class="text-muted">Harga Jual</td><td class="text-danger fw-bold">: Rp <?= number_format($product['price'], 0, ',', '.') ?></td></tr>
                                            <tr><td class="text-muted">Sisa Inventori</td><td>: <strong><?= (int) $product['stock'] ?> pcs</strong></td></tr>
                                            <tr><td class="text-muted">Status Rilis</td><td>: <span class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'danger' ?>"><?= ucfirst($product['status']) ?></span></td></tr>
                                        </table>
                                    </div>
                                    <div class="modal-footer bg-light border-0">
                                        <button class="btn btn-warning fw-bold px-3 text-dark" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $product['id'] ?>">
                                            <i class="fa fa-edit me-1"></i> Ubah Data
                                        </button>
                                        <button class="btn btn-danger fw-bold px-3" onclick="deleteProduct(<?= $product['id'] ?>)">
                                            <i class="fa fa-trash-alt me-1"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editProductModal<?= $product['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header" style="background-color: #ff94c4; color: white;">
                                        <h5 class="modal-title fw-bold"><i class="fa fa-edit me-2"></i>Ubah Varian Produk</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form id="editProductForm<?= $product['id'] ?>" data-id="<?= $product['id'] ?>">
                                            <input type="hidden" name="editProductId" value="<?= $product['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Nama Produk</label>
                                                <input type="text" name="editName" class="form-control editName" value="<?= htmlspecialchars($product['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Tentang Produk</label>
                                                <textarea name="editAbout" class="form-control editAbout" rows="2" required><?= htmlspecialchars($product['about']) ?></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small fw-bold">Harga (Rp)</label>
                                                    <input type="number" name="editPrice" class="form-control editPrice" value="<?= $product['price'] ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small fw-bold">Jumlah Stok</label>
                                                    <input type="number" name="editStock" class="form-control editStock" value="<?= (int) $product['stock'] ?>" min="0" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small fw-bold">Warna</label>
                                                    <input type="text" name="editColor" class="form-control editColor" value="<?= htmlspecialchars($product['color']) ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small fw-bold">Ukuran</label>
                                                    <input type="text" name="editSize" class="form-control editSize" value="<?= htmlspecialchars($product['size']) ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small fw-bold">Kategori</label>
                                                    <select name="editCategory" class="form-select editCategory">
                                                        <?php foreach ($categories as $category): ?>
                                                                <option value="<?= (int) $category['id'] ?>" <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($category['name']) ?>
                                                                </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label small fw-bold">Status</label>
                                                    <select name="editStatus" class="form-select editStatus">
                                                        <option value="tersedia" <?= $product['status'] == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                                        <option value="habis" <?= $product['status'] == 'habis' ? 'selected' : '' ?>>Habis</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Gambar Produk</label>
                                                <input type="file" name="editImage" class="form-control editImage">
                                                <small class="text-muted mt-1 d-block">* Biarkan kosong jika tidak ingin memperbarui visual gambar.</small>
                                            </div>
                                            <div class="d-flex justify-content-end gap-2 mt-4">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn text-white fw-bold" style="background-color: #ff74a4;">Simpan Perubahan</button>
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

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #ff94c4; color: white;">
                    <h5 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i>Tambah Komoditas Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Produk</label>
                            <input type="text" class="form-control" id="productName" placeholder="Masukkan nama item kado" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tentang Produk</label>
                            <textarea class="form-control" id="productAbout" rows="2" placeholder="Deskripsikan keunikan produk kado..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Warna</label>
                                <input type="text" class="form-control" id="productColor" placeholder="Pink, Soft Blue, Putih">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Ukuran</label>
                                <input type="text" class="form-control" id="productSize" placeholder="20x20, Large, Medium">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Kategori</label>
                                <select class="form-select" id="productCategory">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                            <option value="<?= (int) $category['id'] ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Harga Jual</label>
                                <input type="number" class="form-control" id="productPrice" placeholder="Nominal angka rupiah">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Jumlah Stok Awal</label>
                                <input type="number" class="form-control" id="productStock" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Status Rilis</label>
                                <select class="form-select" id="productStatus">
                                    <option value="tersedia">Tersedia</option>
                                    <option value="habis">Habis</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Gambar Produk</label>
                            <input type="file" class="form-control" id="productImage">
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn text-white fw-bold" style="background-color: #ff74a4;">Simpan Komoditas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var csrfToken = <?= json_encode($csrfToken) ?>;

        function parseJsonResponse(response) {
            try { return JSON.parse(response); } catch (e) { return null; }
        }

        function showSuccessAndReload(title, message) {
            Swal.fire({ icon: 'success', title: title, text: message, confirmButtonColor: '#ff94c4' }).then(function() { location.reload(); });
        }

        function showError(message, title) {
            Swal.fire({ icon: 'error', title: title || 'Oops...', text: message, confirmButtonColor: '#ff94c4' });
        }

        function closeModal(modalId) {
            var modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            var modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) modalInstance.hide();
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
            formData.append('productStock', $form.find('.editStock').val());
            formData.append('productColor', $form.find('.editColor').val());
            formData.append('productSize', $form.find('.editSize').val());
            formData.append('productCategory', $form.find('.editCategory').val());
            formData.append('productStatus', $form.find('.editStatus').val());
            formData.append('csrf_token', csrfToken);

            if (fileToUpload) { formData.append('productImage', fileToUpload); }
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
                var productStock = $('#productStock').val();
                var productStatus = $('#productStatus').val();
                var productImage = $('#productImage')[0].files[0];

                if (!productName || !productAbout || !productColor || !productSize || !productCategory || !productPrice || !productStock || !productStatus || !productImage) {
                    showError('Semua kolom wajib diisi lengkap!');
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
                formData.append('productStock', productStock);
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
                        if (data && data.success) { showSuccessAndReload('Berhasil', data.message); } 
                        else { showError(data ? data.message : 'Format respons tidak valid.', 'Gagal'); }
                    },
                    error: function () { showError('Terjadi kesalahan, silakan coba lagi.'); }
                });
            });

            $(document).on('submit', "form[id^='editProductForm']", function (e) {
                e.preventDefault();

                var productName = $(this).find('.editName').val();
                var productDesc = $(this).find('.editAbout').val();
                var productPrice = $(this).find('.editPrice').val();
                var productStock = $(this).find('.editStock').val();
                var productCategory = $(this).find('.editCategory').val();
                var productStatus = $(this).find('.editStatus').val();

                if (!productName || !productDesc || !productPrice || !productStock || !productCategory || !productStatus) {
                    showError('Semua kolom modifikasi wajib diisi!');
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
                        if (data && data.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, confirmButtonColor: '#ff94c4' }).then(function () {
                                closeModal(modalId);
                                location.reload();
                            });
                        } else { showError(data ? data.message : 'Format respons tidak valid.'); }
                    },
                    error: function () { showError('Terjadi kesalahan sistem, silakan coba lagi.'); }
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

                if (matchCategory && matchSearch) { product.style.display = "block"; } 
                else { product.style.display = "none"; }
            });
        }

        function deleteProduct(productId) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Item kado ini akan dihapus permanen dari sistem katalog!',
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
                        data: { action: 'deleteproduct', id: productId, csrf_token: csrfToken },
                        success: function (response) {
                            var data = parseJsonResponse(response);
                            if (data && data.success) { showSuccessAndReload('Produk Dihapus', data.message); } 
                            else { showError(data ? data.message : 'Format respons tidak valid.', 'Error'); }
                        },
                        error: function () { showError('Terjadi kesalahan saat menghapus produk.'); }
                    });
                }
            });
        }
    </script>
</body>

</html>