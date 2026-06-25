<?php
session_start();

require '../functions.php';

requireAdminAccess();

$user = $_SESSION['user'];
$categories = getCategory();
$csrfToken = csrfToken();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoxKado - Kelola Kategori</title>
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
                            <i class="fa fa-user-shield me-1"></i>
                            <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item text-danger" href="logout.php"><i
                                        class="fa fa-sign-out-alt me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="d-flex">
        <div class="sidebar">
            <a href="dashboard.php"><i class="fa fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="category.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i
                    class="fa fa-tags me-2"></i> Kategori</a>
            <a href="product.php"><i class="fa fa-gift me-2"></i> Produk</a>
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
                    <h2 class="fw-bold text-dark mb-0">Kelola Kategori</h2>
                    <p class="text-muted small mb-0">Kelompokkan produk kado agar pembeli lebih mudah menjelajah.</p>
                </div>
                <button class="btn fw-bold px-3 text-white" style="background-color: #ff74a4;" data-bs-toggle="modal"
                    data-bs-target="#addCategoryModal">
                    <i class="fa fa-plus-circle me-1"></i> Tambah Kategori
                </button>
            </div>

            <div class="card p-4 bg-white">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="10%" class="text-center">No</th>
                                <th class="text-start">Nama Kategori</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php $no = 1; ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-secondary"><?= $no++ ?></td>
                                        <td class="text-start fw-bold text-dark"><?= htmlspecialchars($category['name']) ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-danger btn-sm px-3 fw-bold"
                                                onclick="deleteCategory(<?= $category['id'] ?>)">
                                                <i class="fa fa-trash-alt me-1"></i> Haps
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="fa fa-folder-open fa-2x d-block mb-2 text-black-50"></i>
                                        Tidak ada kategori tersedia.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #ff94c4; color: white;">
                    <h5 class="modal-title fw-bold" id="addCategoryModalLabel"><i class="fa fa-tags me-2"></i>Tambah
                        Kategori Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="categoryForm">
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-bold">Nama Kategori</label>
                            <input type="text" class="form-control" id="name"
                                placeholder="Misal: Buket, Kotak Kayu, Acrylic" autocomplete="off" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn text-white fw-bold"
                                style="background-color: #ff74a4;">Simpan</button>
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
            try {
                return JSON.parse(response);
            } catch (error) {
                return null;
            }
        }

        function showAlert(icon, title, message) {
            Swal.fire({
                icon: icon,
                title: title,
                text: message,
                confirmButtonColor: '#ff94c4'
            });
        }

        // Penyelarasan warna konfirmasi sukses ke pink #ff94c4
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

        $(document).ready(function () {
            $("#categoryForm").submit(function (e) {
                e.preventDefault();
                var name = $("#name").val();

                $.ajax({
                    url: '../functions.php',
                    type: 'POST',
                    data: {
                        action: 'newCategory',
                        name: name,
                        csrf_token: csrfToken
                    },
                    success: function (response) {
                        var data = parseJsonResponse(response);
                        if (!data) {
                            showAlert('error', 'Error', 'Format respons tidak valid.');
                            return;
                        }
                        if (data.success) {
                            showSuccessAndReload('Berhasil', data.message);
                        } else {
                            showAlert('error', 'Gagal', data.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        showAlert('error', 'Terjadi Kesalahan', error);
                    }
                });
            });
        });

        function deleteCategory(categoryId) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Menghapus kategori ini dapat memengaruhi produk yang terikat di dalamnya!',
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
                            action: 'deleteCategory',
                            id: categoryId,
                            csrf_token: csrfToken
                        },
                        success: function (response) {
                            var data = parseJsonResponse(response);
                            if (!data) {
                                showAlert('error', 'Terjadi Kesalahan', 'Format respons tidak valid.');
                                return;
                            }
                            if (data.success) {
                                showSuccessAndReload('Kategori Dihapus', data.message);
                            } else {
                                showAlert('error', 'Terjadi Kesalahan', data.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            showAlert('error', 'Oops...', 'Terjadi kesalahan saat menghapus kategori.');
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>