<?php

$conn = mysqli_connect("localhost", "root", "", "boxkado");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    register();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    login();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'newCategory') {
    newCategory();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteCategory') {
    $id = $_POST['id'];
    deleteCategory($id);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addProduct') {
    addProduct();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'editproduct') {
    global $conn;

    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['productName']);
    $desc = mysqli_real_escape_string($conn, $_POST['productDesc']);
    $price = mysqli_real_escape_string($conn, $_POST['productPrice']);
    $color = mysqli_real_escape_string($conn, $_POST['productColor']);
    $size = mysqli_real_escape_string($conn, $_POST['productSize']);
    $category = mysqli_real_escape_string($conn, $_POST['productCategory']);
    $status = mysqli_real_escape_string($conn, $_POST['productStatus']);

    $image = isset($_FILES['productImage']) ? $_FILES['productImage'] : null;

    echo editProduct($id, $name, $desc, $price, $color, $size, $category, $status, $image);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteproduct') {
    $id = $_POST['id'];
    deleteproduct($id);
}

function register()
{
    global $conn;

    $username = mysqli_real_escape_string($conn, strtolower($_POST['username']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Harap isi semua kolom.'
        ]);
        exit;
    }

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username sudah digunakan.'
        ]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertQuery = "INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword')";

    if (mysqli_query($conn, $insertQuery)) {
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan saat registrasi.'
        ]);
    }
}

function login()
{
    global $conn;

    $username = mysqli_real_escape_string($conn, strtolower($_POST['username']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Harap isi semua kolom.'
        ]);
        exit;
    }

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user;

            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil.',
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Username / Password salah.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Akun tidak ditemukan.'
        ]);
    }
}

function newCategory()
{
    global $conn;

    $name = $_POST['name'];

    if (empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Harap isi semua kolom.'
        ]);
        exit;
    }

    $query = "SELECT * FROM category WHERE name = '$name'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Kategori sudah ada.'
        ]);
        exit;
    }

    $insertQuery = "INSERT INTO category (name) VALUES ('$name')";

    if (mysqli_query($conn, $insertQuery)) {
        echo json_encode([
            'success' => true,
            'message' => 'Berhasil menambahkan kategori baru.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan.'
        ]);
    }
}

function getCategory()
{
    global $conn;

    $query = "SELECT * FROM category";
    $result = mysqli_query($conn, $query);

    return $result;
}

function deleteCategory($id)
{
    global $conn;

    $query = "SELECT * FROM category WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $query = "DELETE FROM category WHERE id = $id";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus kategori.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Kategori tidak ditemukan.']);
    }
}

function addProduct()
{
    global $conn;

    $productName = mysqli_real_escape_string($conn, $_POST['productName']);
    $productAbout = mysqli_real_escape_string($conn, $_POST['productAbout']);
    $productColor = mysqli_real_escape_string($conn, $_POST['productColor']);
    $productSize = mysqli_real_escape_string($conn, $_POST['productSize']);
    $productCategoryId = (int) $_POST['productCategory'];
    $productPrice = mysqli_real_escape_string($conn, $_POST['productPrice']);
    $productStatus = mysqli_real_escape_string($conn, $_POST['productStatus']);
    $productImage = $_FILES['productImage'];

    $validateCategory = "SELECT  * FROM category WHERE id = $productCategoryId";
    $resultCategory = mysqli_query($conn, $validateCategory);

    if (mysqli_num_rows($resultCategory) == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Kategori tidak valid.'
        ]);
        exit;
    }

    if (
        empty($productName) || empty($productAbout) || empty($productColor) ||
        empty($productSize) || empty($productCategoryId) || empty($productPrice) ||
        empty($productStatus) || empty($productImage['name'])
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Semua kolom harus diisi.'
        ]);
        exit;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $imageExtension = strtolower(pathinfo($productImage['name'], PATHINFO_EXTENSION));

    if (!in_array($imageExtension, $allowedExtensions)) {
        echo json_encode([
            'success' => false,
            'message' => 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan.'
        ]);
        exit;
    }

    $imageName = time() . '-' . basename($productImage['name']);
    $imageTmpName = $productImage['tmp_name'];
    $imagePath = "assets/uploads/" . $imageName;

    if (move_uploaded_file($imageTmpName, $imagePath)) {
        $query = "INSERT INTO product (name, about, color, size, category_id, price, status, image) 
                  VALUES ('$productName', '$productAbout', '$productColor', '$productSize', $productCategoryId, '$productPrice', '$productStatus', '$imageName')";

        if (mysqli_query($conn, $query)) {
            echo json_encode([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan produk ke database.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengupload gambar.'
        ]);
    }
}

function getProduct()
{
    global $conn;

    $query = "SELECT * FROM product";
    $result = mysqli_query($conn, $query);

    return $result;
}

function getProductLimit()
{
    global $conn;

    $query = "SELECT * FROM product LIMIT 12";
    $result = mysqli_query($conn, $query);

    return $result;
}

function editProduct($id, $name, $desc, $price, $color, $size, $category, $status, $image)
{
    global $conn;

    // Ambil gambar lama
    $query = "SELECT image FROM product WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $oldImage = $row['image'];

    $imageQuery = '';
    if ($image && $image['name'] != '') {
        $allowedExtensions = ['jpeg', 'jpg', 'png'];
        $imageExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        // Validasi ekstensi gambar
        if (!in_array($imageExtension, $allowedExtensions)) {
            return json_encode(['success' => false, 'message' => 'Hanya gambar JPEG, JPG, dan PNG diperbolehkan.']);
        }

        // Tentukan direktori target untuk gambar
        $targetDir = '../assets/uploads/';
        $oldImagePath = $targetDir . $oldImage;

        // Hapus gambar lama jika ada
        if (file_exists($oldImagePath) && !empty($oldImage)) {
            unlink($oldImagePath);
        }

        // Tentukan nama gambar baru
        $imageName = uniqid() . '_' . basename($image['name']);
        $targetFile = $targetDir . $imageName;

        // Pindahkan gambar ke direktori tujuan
        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            $imageQuery = ", image = '$imageName'";
        } else {
            return json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar.']);
        }
    }

    // Query untuk memperbarui produk
    $query = "UPDATE product 
              SET name = '$name', 
                  about = '$desc', 
                  price = '$price', 
                  color = '$color', 
                  size = '$size', 
                  category_id = $category, 
                  status = '$status'
                  $imageQuery 
              WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    return json_encode(['success' => $result, 'message' => $result ? 'Produk berhasil diperbarui.' : 'Gagal memperbarui produk.']);
}

function deleteProduct($id)
{
    global $conn;

    $query = "SELECT image FROM product WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $image = $row['image'];
        $imagePath = '../assets/uploads/' . $image;

        $query = "DELETE FROM product WHERE id = $id";
        $result = mysqli_query($conn, $query);

        if ($result) {
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus produk.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
    }
}
