<?php

$conn = mysqli_connect("localhost", "root", "", "boxkado");

function requireAdminAccess($respondAsJson = false)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $user = $_SESSION['user'] ?? null;
    $role = $user['role'] ?? null;

    if ($user && in_array($role, ['admin', 'owner'], true)) {
        return true;
    }

    if ($respondAsJson) {
        echo json_encode([
            'success' => false,
            'message' => 'Akses ditolak.'
        ]);
        exit;
    }

    header('Location: index.php');
    exit;
}

function csrfToken()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($respondAsJson = false)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = $_POST['csrf_token'] ?? '';

    if ($sessionToken !== '' && $requestToken !== '' && hash_equals($sessionToken, $requestToken)) {
        return true;
    }

    if ($respondAsJson) {
        echo json_encode([
            'success' => false,
            'message' => 'Token permintaan tidak valid.'
        ]);
        exit;
    }

    header('Location: index.php');
    exit;
}

function getClientIp()
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function getLoginThrottleFile($username)
{
    $key = strtolower(trim($username)) . '|' . getClientIp();
    return rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'boxkado_login_' . md5($key) . '.json';
}

function isLoginLocked($username)
{
    $filePath = getLoginThrottleFile($username);

    if (!file_exists($filePath)) {
        return [false, 0];
    }

    $data = json_decode((string) file_get_contents($filePath), true);
    $lockedUntil = (int) ($data['locked_until'] ?? 0);
    $now = time();

    if ($lockedUntil > $now) {
        return [true, $lockedUntil - $now];
    }

    return [false, 0];
}

function registerLoginFailure($username)
{
    $filePath = getLoginThrottleFile($username);
    $now = time();
    $windowSeconds = 15 * 60;
    $maxAttempts = 5;

    $data = [
        'attempts' => 0,
        'first_attempt_at' => $now,
        'locked_until' => 0,
    ];

    if (file_exists($filePath)) {
        $existing = json_decode((string) file_get_contents($filePath), true);
        if (is_array($existing)) {
            $data = array_merge($data, $existing);
        }
    }

    if (($now - (int) $data['first_attempt_at']) > $windowSeconds) {
        $data['attempts'] = 0;
        $data['first_attempt_at'] = $now;
    }

    $data['attempts']++;
    if ($data['attempts'] >= $maxAttempts) {
        $data['locked_until'] = $now + $windowSeconds;
    }

    file_put_contents($filePath, json_encode($data), LOCK_EX);
}

function clearLoginFailures($username)
{
    $filePath = getLoginThrottleFile($username);

    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

function isValidImageUpload($file, &$errorMessage)
{
    $maxFileSize = 2 * 1024 * 1024;
    $allowedMimeTypes = ['image/jpeg', 'image/png'];
    $allowedExtensions = ['jpg', 'jpeg', 'png'];

    if (!isset($file['tmp_name'], $file['name'], $file['size']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Gagal membaca file gambar.';
        return false;
    }

    if ($file['size'] > $maxFileSize) {
        $errorMessage = 'Ukuran gambar maksimal 2 MB.';
        return false;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        $errorMessage = 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan.';
        return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        $errorMessage = 'File harus berupa gambar JPG atau PNG yang valid.';
        return false;
    }

    if (@getimagesize($file['tmp_name']) === false) {
        $errorMessage = 'File gambar tidak valid.';
        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    register();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    login();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'newCategory') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    newCategory();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteCategory') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    $id = $_POST['id'];
    deleteCategory($id);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addProduct') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    addProduct();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'editproduct') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    global $conn;

    $id = (int) $_POST['id'];
    $name = $_POST['productName'];
    $desc = $_POST['productDesc'];
    $price = $_POST['productPrice'];
    $color = $_POST['productColor'];
    $size = $_POST['productSize'];
    $categoryId = (int) $_POST['productCategory'];
    $status = $_POST['productStatus'];

    $image = isset($_FILES['productImage']) ? $_FILES['productImage'] : null;

    echo editProduct($id, $name, $desc, $price, $color, $size, $categoryId, $status, $image);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteproduct') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    $id = $_POST['id'];
    deleteproduct($id);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addToCart') {
    addToCart();
}

function register()
{
    global $conn;

    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Harap isi semua kolom.'
        ]);
        exit;
    }

    $query = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 's', $username);
    mysqli_stmt_execute($query);
    mysqli_stmt_store_result($query);

    if (mysqli_stmt_num_rows($query) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username sudah digunakan.'
        ]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertQuery = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
    mysqli_stmt_bind_param($insertQuery, 'ss', $username, $hashedPassword);

    if (mysqli_stmt_execute($insertQuery)) {
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

    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Harap isi semua kolom.'
        ]);
        exit;
    }

    [$isLocked, $remainingSeconds] = isLoginLocked($username);
    if ($isLocked) {
        echo json_encode([
            'success' => false,
            'message' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($remainingSeconds / 60) . ' menit.'
        ]);
        exit;
    }

    $query = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 's', $username);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            session_start();
            session_regenerate_id(true);
            $_SESSION['user'] = $user;
            clearLoginFailures($username);

            $safeUser = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil.',
                'user' => $safeUser
            ]);
        } else {
            registerLoginFailure($username);
            echo json_encode([
                'success' => false,
                'message' => 'Username / Password salah.'
            ]);
        }
    } else {
        registerLoginFailure($username);
        echo json_encode([
            'success' => false,
            'message' => 'Username / Password salah.'
        ]);
    }
}

function newCategory()
{
    global $conn;

    $name = trim($_POST['name']);

    if (empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Harap isi semua kolom.'
        ]);
        exit;
    }

    $query = mysqli_prepare($conn, "SELECT id FROM category WHERE name = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 's', $name);
    mysqli_stmt_execute($query);
    mysqli_stmt_store_result($query);

    if (mysqli_stmt_num_rows($query) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Kategori sudah ada.'
        ]);
        exit;
    }

    $insertQuery = mysqli_prepare($conn, "INSERT INTO category (name) VALUES (?)");
    mysqli_stmt_bind_param($insertQuery, 's', $name);

    if (mysqli_stmt_execute($insertQuery)) {
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

    $query = mysqli_prepare($conn, "SELECT id, name FROM category");
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    return $result;
}

function deleteCategory($id)
{
    global $conn;

    $id = (int) $id;

    $query = mysqli_prepare($conn, "SELECT id FROM category WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 'i', $id);
    mysqli_stmt_execute($query);
    mysqli_stmt_store_result($query);

    if (mysqli_stmt_num_rows($query) > 0) {
        $deleteQuery = mysqli_prepare($conn, "DELETE FROM category WHERE id = ?");
        mysqli_stmt_bind_param($deleteQuery, 'i', $id);
        $result = mysqli_stmt_execute($deleteQuery);

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

    $productName = mysqli_real_escape_string($conn, trim($_POST['productName']));
    $productAbout = mysqli_real_escape_string($conn, trim($_POST['productAbout']));
    $productColor = mysqli_real_escape_string($conn, trim($_POST['productColor']));
    $productSize = mysqli_real_escape_string($conn, trim($_POST['productSize']));
    $productCategoryId = (int) $_POST['productCategory'];
    $productPrice = (float) $_POST['productPrice'];
    $productStatus = mysqli_real_escape_string($conn, trim($_POST['productStatus']));
    $productImage = $_FILES['productImage'];

    $validateCategory = mysqli_prepare($conn, "SELECT id FROM category WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($validateCategory, 'i', $productCategoryId);
    mysqli_stmt_execute($validateCategory);
    mysqli_stmt_store_result($validateCategory);

    if (mysqli_stmt_num_rows($validateCategory) == 0) {
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

    $imageError = '';

    if (!isValidImageUpload($productImage, $imageError)) {
        echo json_encode([
            'success' => false,
            'message' => $imageError
        ]);
        exit;
    }

    $imageName = time() . '-' . basename($productImage['name']);
    $imageTmpName = $productImage['tmp_name'];
    $imagePath = "assets/uploads/" . $imageName;

    if (move_uploaded_file($imageTmpName, $imagePath)) {
        $query = mysqli_prepare($conn, "INSERT INTO product (name, about, color, size, category_id, price, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($query, 'ssssidss', $productName, $productAbout, $productColor, $productSize, $productCategoryId, $productPrice, $productStatus, $imageName);

        if (mysqli_stmt_execute($query)) {
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

    $query = "SELECT product.*, category.name AS category
              FROM product
              LEFT JOIN category ON product.category_id = category.id";
    $result = mysqli_query($conn, $query);

    return $result;
}

function getProductLimit()
{
    global $conn;

    $query = "SELECT product.*, category.name AS category
              FROM product
              LEFT JOIN category ON product.category_id = category.id
              LIMIT 12";
    $result = mysqli_query($conn, $query);

    return $result;
}

function editProduct($id, $name, $desc, $price, $color, $size, $categoryId, $status, $image)
{
    global $conn;

    $name = mysqli_real_escape_string($conn, trim($name));
    $desc = mysqli_real_escape_string($conn, trim($desc));
    $price = (float) $price;
    $color = mysqli_real_escape_string($conn, trim($color));
    $size = mysqli_real_escape_string($conn, trim($size));
    $status = mysqli_real_escape_string($conn, trim($status));

    if ($id <= 0 || $categoryId <= 0) {
        return json_encode(['success' => false, 'message' => 'Data produk atau kategori tidak valid.']);
    }

    $checkCategoryQuery = mysqli_prepare($conn, "SELECT id FROM category WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($checkCategoryQuery, 'i', $categoryId);
    mysqli_stmt_execute($checkCategoryQuery);
    mysqli_stmt_store_result($checkCategoryQuery);
    if (mysqli_stmt_num_rows($checkCategoryQuery) === 0) {
        return json_encode(['success' => false, 'message' => 'Kategori tidak ditemukan.']);
    }

    // Ambil gambar lama
    $query = mysqli_prepare($conn, "SELECT image FROM product WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 'i', $id);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if (!$row) {
        return json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
    }
    $oldImage = $row['image'];

    $imageQuery = '';
    $imageName = '';
    if ($image && $image['name'] != '') {
        $imageError = '';
        if (!isValidImageUpload($image, $imageError)) {
            return json_encode(['success' => false, 'message' => $imageError]);
        }

        // Tentukan direktori target untuk gambar
        $targetDir = 'assets/uploads/';
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
    if ($imageQuery !== '') {
        $query = mysqli_prepare($conn, "UPDATE product SET name = ?, about = ?, price = ?, color = ?, size = ?, category_id = ?, status = ?, image = ? WHERE id = ?");
        mysqli_stmt_bind_param($query, 'ssdssissi', $name, $desc, $price, $color, $size, $categoryId, $status, $imageName, $id);
    } else {
        $query = mysqli_prepare($conn, "UPDATE product SET name = ?, about = ?, price = ?, color = ?, size = ?, category_id = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($query, 'ssdssisi', $name, $desc, $price, $color, $size, $categoryId, $status, $id);
    }

    $result = mysqli_stmt_execute($query);

    if (!$result) {
        return json_encode(['success' => false, 'message' => 'Gagal memperbarui produk: ' . mysqli_error($conn)]);
    }

    return json_encode(['success' => true, 'message' => 'Produk berhasil diperbarui.']);
}

function deleteProduct($id)
{
    global $conn;

    $id = (int) $id;

    $query = mysqli_prepare($conn, "SELECT image FROM product WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 'i', $id);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if ($row) {
        $image = $row['image'];
        $imagePath = 'assets/uploads/' . $image;

        $query = mysqli_prepare($conn, "DELETE FROM product WHERE id = ?");
        mysqli_stmt_bind_param($query, 'i', $id);
        $result = mysqli_stmt_execute($query);

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

function addToCart()
{
    global $conn;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $user = $_SESSION['user'] ?? null;

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Anda harus login untuk menambahkan produk ke keranjang.']);
        exit;
    }

    $userId = (int) $user['id'];
    $productId = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data produk tidak valid.']);
        exit;
    }

    $checkQuery = mysqli_prepare($conn, "SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ? LIMIT 1");
    mysqli_stmt_bind_param($checkQuery, 'ii', $userId, $productId);
    mysqli_stmt_execute($checkQuery);
    $result = mysqli_stmt_get_result($checkQuery);
    $cartItem = mysqli_fetch_assoc($result);

    if ($cartItem) {
        $newQuantity = $cartItem['quantity'] + $quantity;
        $updateQuery = mysqli_prepare($conn, "UPDATE carts SET quantity = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateQuery, 'ii', $newQuantity, $cartItem['id']);

        if (mysqli_stmt_execute($updateQuery)) {
            echo json_encode([
                'success' => true,
                'message' => 'Jumlah produk di keranjang berhasil ditambahkan.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal memperbarui keranjang.',
            ]);
        }
    } else {
        $insertQuery = mysqli_prepare($conn, "INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insertQuery, 'iii', $userId, $productId, $quantity);

        if (mysqli_stmt_execute($insertQuery)) {
            echo json_encode([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menambahkan produk ke keranjang.',
            ]);
        }
    }
    exit;
}