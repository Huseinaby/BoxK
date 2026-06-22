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
            <h2 class="mt-4">Kategori</h2>
            <button class="btn" style="background-color: #ff74a4; color: white; margin-bottom: 15px;"
                data-bs-toggle="modal" data-bs-target="#addCategoryModal">Tambah Kategori</button>
            <div class="card p-4">
                <table class="table table-bordered table-responsive text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php $no = 1;
                            ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="deleteCategory(<?= $category['id'] ?>)">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Tidak ada kategori tersedia</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="name" placeholder="Masukkan nama kategori"
                                autocomplete="off">
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

        function showAlert(icon, title, message) {
            Swal.fire({
                icon: icon,
                title: title,
                text: message,
                confirmButtonColor: '#ff94c4'
            });
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
                            showSuccessAndReload('Success', data.message);
                        } else {
                            showAlert('error', 'Error', data.message);
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
                text: 'Data ini tidak dapat dikembalikan setelah dihapus!',
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
                            console.log('Error:', error);
                            showAlert('error', 'Oops...', 'Terjadi kesalahan saat menghapus kategori.');
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>