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

        .product-img-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-card-hover:hover .product-img-wrapper img,
        .product-card-hover:hover .product-img-wrapper video {
            transform: scale(1.05);
        }

        .product-img-wrapper video {
            transition: transform .3s;
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
            <a href="category.php"><i class="fa fa-tags me-2"></i> Kategori</a>
            <a href="product.php" style="background-color: rgba(0,0,0,0.1); font-weight: bold;"><i
                    class="fa fa-gift me-2"></i> Produk</a>
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
                    <p class="text-muted small mb-0">Atur katalog kado, harga, spesifikasi varian, beserta sisa stok
                        barang.</p>
                </div>
                <button class="btn fw-bold px-3 text-white" style="background-color: #ff74a4;" data-bs-toggle="modal"
                    data-bs-target="#addProductModal">
                    <i class="fa fa-plus-circle me-1"></i> Tambah Produk
                </button>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i
                                class="fa fa-filter"></i></span>
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
                        <span class="input-group-text bg-white border-end-0 text-muted"><i
                                class="fa fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                            placeholder="Cari kado impian...">
                    </div>
                </div>
            </div>

            <div class="row" id="productContainer">
                <?php foreach ($products as $product): ?>
                    <?php
                    $productMedia = $product['media'] ?? '';
                    $totalStock = (int) ($product['total_stock'] ?? 0);

                    $isVideo = false;

                    if (!empty($productMedia)) {
                        $extension = strtolower(pathinfo($productMedia, PATHINFO_EXTENSION));
                        $isVideo = in_array($extension, ['mp4', 'webm']);
                    }
                    ?>
                    <div class="col-md-3 mb-4 product-card" data-category="<?= (int) $product['category_id'] ?>"
                        data-name="<?= strtolower(htmlspecialchars($product['name'])) ?>">
                        <div class="card h-100 product-card-hover" style="cursor: pointer;" data-bs-toggle="modal"
                            data-bs-target="#productModal<?= $product['id'] ?>">
                            <div class="product-img-wrapper">
                                <?php if (!empty($productMedia)): ?>

                                    <?php if ($isVideo): ?>
                                        <video class="w-100 h-100" style="object-fit: cover;" muted autoplay loop playsinline>
                                            <source src="../assets/uploads/<?= htmlspecialchars($productMedia) ?>">
                                        </video>
                                    <?php else: ?>
                                        <img src="../assets/uploads/<?= htmlspecialchars($productMedia) ?>"
                                            alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php endif; ?>

                                <?php else: ?>
                                    <img src="../assets/images/banner.png" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-3">
                                <h5 class="card-title text-dark fw-bold mb-1 small">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h5>
                                <p class="card-text mb-2 text-danger fw-bold fs-5">
                                    Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span
                                        class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'danger' ?> px-2 py-1 small">
                                        <?= ucfirst($product['status']) ?>
                                    </span>
                                    <small class="text-muted fw-bold">Stok: <?= $totalStock ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="productModal<?= $product['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold text-dark"><?= htmlspecialchars($product['name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php if (!empty($productMedia)): ?>

                                        <?php if ($isVideo): ?>

                                            <video class="img-fluid rounded border mb-3 w-100"
                                                style="max-height:250px;object-fit:cover;" controls>
                                                <source src="../assets/uploads/<?= htmlspecialchars($productMedia) ?>">
                                            </video>

                                        <?php else: ?>

                                            <img src="../assets/uploads/<?= htmlspecialchars($productMedia) ?>"
                                                class="img-fluid rounded border mb-3 w-100"
                                                style="max-height:250px;object-fit:cover;">

                                        <?php endif; ?>

                                    <?php else: ?>

                                        <img src="../assets/images/banner.png" class="img-fluid rounded border mb-3 w-100"
                                            style="max-height:250px;object-fit:cover;">

                                    <?php endif; ?>
                                    <table class="table table-sm table-borderless small mb-0">
                                        <tr>
                                            <td width="30%" class="text-muted">Deskripsi</td>
                                            <td>: <?= nl2br(htmlspecialchars($product['about'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Dimensi Ukuran</td>
                                            <td>: <?= htmlspecialchars($product['size']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Kategori</td>
                                            <td>: <span
                                                    class="badge bg-secondary"><?= htmlspecialchars($product['category'] ?? '-') ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Harga Jual</td>
                                            <td class="text-danger fw-bold">: Rp
                                                <?= number_format($product['price'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status Rilis</td>
                                            <td>: <span
                                                    class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'danger' ?>"><?= ucfirst($product['status']) ?></span>
                                            </td>
                                        </tr>
                                    </table>

                                    <hr>
                                    <h6 class="fw-bold mb-3">Varian Produk</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Warna</th>
                                                    <th>Stok</th>
                                                    <th>Gambar</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($product['variants'] as $variant): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($variant['color']) ?></td>
                                                        <td><?= (int) $variant['stock'] ?> pcs</td>
                                                        <td>
                                                            <?php if (!empty($variant['image'])): ?>
                                                                <img src="../assets/uploads/<?= htmlspecialchars($variant['image']) ?>"
                                                                    style="width:54px;height:54px;object-fit:cover;"
                                                                    class="rounded border">
                                                            <?php else: ?>
                                                                <span class="text-muted small">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <button class="btn btn-sm btn-outline-primary fw-bold"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editVariantModal<?= (int) $variant['id'] ?>">
                                                                <i class="fa fa-edit me-1"></i> Ubah
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-0">
                                    <button class="btn btn-warning fw-bold px-3 text-dark" data-bs-toggle="modal"
                                        data-bs-target="#editProductModal<?= $product['id'] ?>">
                                        <i class="fa fa-edit me-1"></i> Ubah Data
                                    </button>
                                    <button class="btn btn-success fw-bold px-3" data-bs-toggle="modal"
                                        data-bs-target="#addVariantModal<?= $product['id'] ?>">
                                        <i class="fa fa-layer-group me-1"></i> Tambah Varian
                                    </button>
                                    <button class="btn btn-danger fw-bold px-3"
                                        onclick="deleteProduct(<?= $product['id'] ?>)">
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
                                    <h5 class="modal-title fw-bold"><i class="fa fa-edit me-2"></i>Ubah Produk</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <form id="editProductForm<?= $product['id'] ?>" data-id="<?= $product['id'] ?>">
                                        <input type="hidden" name="editProductId" value="<?= $product['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Nama Produk</label>
                                            <input type="text" name="editName" class="form-control editName"
                                                value="<?= htmlspecialchars($product['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Tentang Produk</label>
                                            <textarea name="editAbout" class="form-control editAbout" rows="2"
                                                required><?= htmlspecialchars($product['about']) ?></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold">Harga (Rp)</label>
                                                <input type="number" name="editPrice" class="form-control editPrice"
                                                    value="<?= $product['price'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold">Ukuran</label>
                                                <input type="text" name="editSize" class="form-control editSize"
                                                    value="<?= htmlspecialchars($product['size']) ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold">Kategori</label>
                                                <select name="editCategory" class="form-select editCategory">
                                                    <?php foreach ($categories as $category): ?>
                                                        <?php $selected = ((int) $product['category_id'] === (int) $category['id']) ? 'selected' : ''; ?>
                                                        <option value="<?= (int) $category['id'] ?>" <?= $selected ?>>
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
                                            <label class="form-label small fw-bold">
                                                Media Produk
                                            </label>
                                            <input type="file" name="productMedia" class="form-control"
                                                accept="image/*,video/*">

                                            <small class="text-muted">
                                                Kosongkan jika tidak ingin mengganti media.
                                            </small>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mt-4">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn text-white fw-bold"
                                                style="background-color: #ff74a4;">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="addVariantModal<?= $product['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header" style="background-color: #ff94c4; color: white;">
                                    <h5 class="modal-title fw-bold"><i class="fa fa-layer-group me-2"></i>Tambah Varian Baru
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <form id="addVariantForm<?= $product['id'] ?>" data-product-id="<?= $product['id'] ?>">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Warna Varian</label>
                                            <input type="text" name="variantColor" class="form-control"
                                                placeholder="Pink, Soft Blue, Putih" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Stok Varian</label>
                                            <input type="number" name="variantStock" class="form-control" min="0" value="0"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">
                                                Gambar Varian
                                            </label>

                                            <input type="file" name="variantImage" class="form-control" accept="image/*">
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mt-4">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn text-white fw-bold"
                                                style="background-color: #ff74a4;">Simpan Varian</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($product['variants'] as $variant): ?>
                        <div class="modal fade" id="editVariantModal<?= (int) $variant['id'] ?>" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header" style="background-color: #ff94c4; color: white;">
                                        <h5 class="modal-title fw-bold"><i class="fa fa-pen-to-square me-2"></i>Ubah Varian</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form id="editVariantForm<?= (int) $variant['id'] ?>"
                                            data-variant-id="<?= (int) $variant['id'] ?>">
                                            <input type="hidden" name="variant_id" value="<?= (int) $variant['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Warna Varian</label>
                                                <input type="text" name="variantColor" class="form-control"
                                                    value="<?= htmlspecialchars($variant['color']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Stok Varian</label>
                                                <input type="number" name="variantStock" class="form-control" min="0"
                                                    value="<?= (int) $variant['stock'] ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Ganti Gambar Varian</label>
                                                <input type="file" name="variantImage" class="form-control" accept="image/*">
                                                <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                                            </div>
                                            <div class="d-flex justify-content-end gap-2 mt-4">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn text-white fw-bold"
                                                    style="background-color: #ff74a4;">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background-color: #ff94c4; color: white;">
                    <h5 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i>Tambah Produk Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Produk</label>
                            <input type="text" class="form-control" id="productName"
                                placeholder="Masukkan nama item kado" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tentang Produk</label>
                            <textarea class="form-control" id="productAbout" rows="2"
                                placeholder="Deskripsikan keunikan produk kado..."></textarea>
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
                                <input type="number" class="form-control" id="productPrice"
                                    placeholder="Nominal angka rupiah">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Ukuran Produk</label>
                                <input type="text" class="form-control" id="productSize"
                                    placeholder="20x20, Large, Medium">
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
                            <label class="form-label small fw-bold">
                                Media Produk
                            </label>

                            <input type="file" class="form-control" id="productMedia" accept="image/*,video/*">

                            <small class="text-muted">
                                Upload satu gambar atau satu video produk.
                            </small>
                        </div>
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Varian Pertama</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Warna</label>
                                <input type="text" class="form-control" id="variantColor"
                                    placeholder="Pink, Soft Blue, Putih">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Stok Varian</label>
                                <input type="number" class="form-control" id="variantStock" min="0" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                Gambar Varian
                            </label>

                            <input type="file" class="form-control" id="variantImage" accept="image/*">
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn text-white fw-bold"
                                style="background-color: #ff74a4;">Simpan Produk</button>
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
            Swal.fire({ icon: 'success', title: title, text: message, confirmButtonColor: '#ff94c4' }).then(function () { location.reload(); });
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
            var formData = new FormData();

            formData.append('action', 'editproduct');
            formData.append('id', $form.data('id'));
            formData.append('productName', $form.find('.editName').val());
            formData.append('productDesc', $form.find('.editAbout').val());
            formData.append('productPrice', $form.find('.editPrice').val());
            formData.append('productSize', $form.find('.editSize').val());
            formData.append('productCategory', $form.find('.editCategory').val());
            formData.append('productStatus', $form.find('.editStatus').val());

            var mediaInput = $form.find('input[name="productMedia"]')[0];

            if (mediaInput && mediaInput.files.length > 0) {
                formData.append('productMedia', mediaInput.files[0]);
            }

            formData.append('csrf_token', csrfToken);

            return formData;
        }

        function buildAddProductFormData() {

            var formData = new FormData();

            formData.append('action', 'addProduct');
            formData.append('productName', $('#productName').val());
            formData.append('productAbout', $('#productAbout').val());
            formData.append('productSize', $('#productSize').val());
            formData.append('productCategory', $('#productCategory').val());
            formData.append('productPrice', $('#productPrice').val());
            formData.append('productStatus', $('#productStatus').val());

            formData.append('variantColor', $('#variantColor').val());
            formData.append('variantStock', $('#variantStock').val());

            var productMedia = document.getElementById('productMedia');

            if (productMedia && productMedia.files.length > 0) {
                formData.append('productMedia', productMedia.files[0]);
            }

            var variantImage = document.getElementById('variantImage');

            if (variantImage && variantImage.files.length > 0) {
                formData.append('variantImage', variantImage.files[0]);
            }

            formData.append('csrf_token', csrfToken);

            return formData;
        }

        $(document).ready(function () {
            $(document).on('submit', "form[id^='editProductForm']", function (e) {
                e.preventDefault();

                var productName = $(this).find('.editName').val();
                var productDesc = $(this).find('.editAbout').val();
                var productPrice = $(this).find('.editPrice').val();
                var productCategory = $(this).find('.editCategory').val();
                var productStatus = $(this).find('.editStatus').val();

                if (!productName || !productDesc || !productPrice || !productCategory || !productStatus) {
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

            $(document).on('submit', "form[id^='addVariantForm']", function (e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'addVariant');
                formData.append('csrf_token', csrfToken);

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
                                location.reload();
                            });
                        } else {
                            showError(data ? data.message : 'Format respons tidak valid.');
                        }
                    },
                    error: function () { showError('Terjadi kesalahan sistem, silakan coba lagi.'); }
                });
            });

            $(document).on('submit', "form[id^='editVariantForm']", function (e) {
                e.preventDefault();

                var formData = new FormData(this);
                formData.append('action', 'editVariant');
                formData.append('csrf_token', csrfToken);

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
                                location.reload();
                            });
                        } else {
                            showError(data ? data.message : 'Format respons tidak valid.');
                        }
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

        $("#addProductForm").submit(function (e) {
            e.preventDefault();

            var productName = $('#productName').val();
            var productAbout = $('#productAbout').val();
            var productSize = $('#productSize').val();
            var productCategory = $('#productCategory').val();
            var productPrice = $('#productPrice').val();
            var productStatus = $('#productStatus').val();
            var variantColor = $('#variantColor').val();
            var variantStock = $('#variantStock').val();
            var productMedia = $('#productMedia')[0].files[0];
            var variantImage = $('#variantImage')[0].files[0];

            if (
                !productName ||
                !productAbout ||
                !productSize ||
                !productCategory ||
                !productPrice ||
                !productStatus ||
                !variantColor ||
                variantStock === '' ||
                !productMedia ||
                !variantImage
            ) {
                showError('Semua kolom wajib diisi.');
                return;
            }

            var formData = buildAddProductFormData();
            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengunggah...');

            $.ajax({
                url: '../functions.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                timeout: 120000, // 2 menit, sesuaikan dengan max_execution_time hosting
                success: function (response) {
                    var data = parseJsonResponse(response);
                    if (data && data.success) { showSuccessAndReload('Berhasil', data.message); }
                    else { showError(data ? data.message : 'Format respons tidak valid.', 'Gagal'); }
                },
                error: function (xhr, status) {
                    if (status === 'timeout') {
                        showError('Upload memakan waktu terlalu lama. Coba gunakan file yang lebih kecil.');
                    } else {
                        showError('Terjadi kesalahan, silakan coba lagi.');
                    }
                },
                complete: function () {
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    </script>
</body>

</html>