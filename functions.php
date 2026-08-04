<?php

$conn = mysqli_connect(
    "localhost",
    "boxkadom_db",
    "XUSAGxXwB4ULKCHeSGKf",
    "boxkadom_db"
);

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

function normalizeUploadedFiles($files)
{
    if (!isset($files['name'])) {
        return [];
    }

    if (!is_array($files['name'])) {
        return [$files];
    }

    $normalizedFiles = [];

    foreach ($files['name'] as $index => $name) {
        $error = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE || $name === '') {
            continue;
        }

        $normalizedFiles[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $error,
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    return $normalizedFiles;
}

function storeUploadedImageFile($file, $prefix = 'image')
{
    $errorMessage = '';

    if (!isValidImageUpload($file, $errorMessage)) {
        return [false, $errorMessage];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = $prefix . '-' . time() . '-' . uniqid() . '.' . $extension;
    $targetPath = __DIR__ . '/assets/uploads/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [false, 'Gagal menyimpan file gambar.'];
    }

    return [true, $fileName];
}

function getProductVariantsByProductId($productId)
{
    global $conn;

    $productId = (int) $productId;
    $variants = [];

    $query = mysqli_prepare(
        $conn,
        "SELECT
            id,
            product_id,
            color,
            stock,
            image
        FROM product_variants
        WHERE product_id = ?
        ORDER BY id ASC"
    );

    mysqli_stmt_bind_param($query, 'i', $productId);
    mysqli_stmt_execute($query);

    $result = mysqli_stmt_get_result($query);

    while ($row = mysqli_fetch_assoc($result)) {
        $variants[] = $row;
    }

    return $variants;
}

function getDefaultVariantByProductId($productId)
{
    $variants = getProductVariantsByProductId($productId);
    return $variants[0] ?? null;
}

function getVariantById($variantId)
{
    global $conn;

    $variantId = (int) $variantId;

    $query = mysqli_prepare($conn, "
        SELECT
            pv.id,
            pv.product_id,
            pv.color,
            pv.stock,
            pv.image,
            p.name AS product_name,
            p.price,
            p.status,
            p.category_id,
            p.media,
            category.name AS category
        FROM product_variants pv
        JOIN product p ON pv.product_id = p.id
        LEFT JOIN category ON p.category_id = category.id
        WHERE pv.id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($query, 'i', $variantId);
    mysqli_stmt_execute($query);

    $result = mysqli_stmt_get_result($query);

    return $result ? mysqli_fetch_assoc($result) : null;
}

function getProductCatalog($limit = null)
{
    global $conn;

    $limitSql = '';

    if ($limit !== null) {
        $limitSql = ' LIMIT ' . (int) $limit;
    }

    $query = "
        SELECT
            product.*,
            category.name AS category
        FROM product
        LEFT JOIN category
            ON product.category_id = category.id
        ORDER BY product.id DESC
        {$limitSql}
    ";

    $result = mysqli_query($conn, $query);

    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $variants = getProductVariantsByProductId($row['id']);

        $row['variants'] = $variants;

        $row['total_stock'] = array_sum(
            array_column($variants, 'stock')
        );

        $products[] = $row;
    }

    return $products;
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
    echo editProduct();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addVariant') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    addVariant();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'editVariant') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    editVariant();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteproduct') {
    requireAdminAccess(true);
    verifyCsrfToken(true);
    $id = $_POST['id'];
    deleteproduct($id);
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addToCart') {
    addToCart();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'updateCartQty') {
    updateCartQty();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteCartItem') {
    deleteCartItemAction();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancelOrderUser') {
    cancelOrderUserAction();
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

    $name = trim($_POST['productName'] ?? '');
    $about = trim($_POST['productAbout'] ?? '');
    $size = trim($_POST['productSize'] ?? '');
    $category_id = (int) ($_POST['productCategory'] ?? 0);
    $price = (int) ($_POST['productPrice'] ?? 0);
    $status = trim($_POST['productStatus'] ?? 'tersedia');

    $variantColor = trim($_POST['variantColor'] ?? '');
    $variantStock = (int) ($_POST['variantStock'] ?? 0);

    $productMedia = $_FILES['productMedia'] ?? null;
    $variantImage = $_FILES['variantImage'] ?? null;

    if (
        $name === '' ||
        $about === '' ||
        $size === '' ||
        $category_id <= 0 ||
        $price <= 0 ||
        $variantColor === '' ||
        $variantStock < 0
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Lengkapi seluruh data produk dengan benar.'
        ]);
        exit;
    }

    if (
        !$productMedia ||
        ($productMedia['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Media produk wajib diunggah.'
        ]);
        exit;
    }

    if (
        !$variantImage ||
        ($variantImage['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Gambar varian wajib diunggah.'
        ]);
        exit;
    }

    mysqli_begin_transaction($conn);

    // Upload media produk (gambar / video)
    $uploadProduct = storeUploadedMediaFile($productMedia, 'product');

    if (!$uploadProduct[0]) {
        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => $uploadProduct[1]
        ]);
        exit;
    }

    $productMediaName = $uploadProduct[1];

    // Upload gambar varian
    $uploadVariant = storeUploadedImageFile($variantImage, 'variant');

    if (!$uploadVariant[0]) {

        @unlink(__DIR__ . '/assets/uploads/' . $productMediaName);

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => $uploadVariant[1]
        ]);
        exit;
    }

    $variantImageName = $uploadVariant[1];

    // Simpan produk
    $query = mysqli_prepare(
        $conn,
        "INSERT INTO product
        (category_id, name, size, price, about, status, media)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $query,
        'ississs',
        $category_id,
        $name,
        $size,
        $price,
        $about,
        $status,
        $productMediaName
    );

    if (!mysqli_stmt_execute($query)) {

        @unlink(__DIR__ . '/assets/uploads/' . $productMediaName);
        @unlink(__DIR__ . '/assets/uploads/' . $variantImageName);

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan produk.'
        ]);
        exit;
    }

    $productId = mysqli_insert_id($conn);

    // Simpan varian pertama
    $variantQuery = mysqli_prepare(
        $conn,
        "INSERT INTO product_variants
        (product_id, color, stock, image)
        VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $variantQuery,
        'isis',
        $productId,
        $variantColor,
        $variantStock,
        $variantImageName
    );

    if (!mysqli_stmt_execute($variantQuery)) {

        @unlink(__DIR__ . '/assets/uploads/' . $productMediaName);
        @unlink(__DIR__ . '/assets/uploads/' . $variantImageName);

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan varian.'
        ]);
        exit;
    }

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Produk berhasil ditambahkan.'
    ]);

    exit;
}
function getProduct()
{
    return getProductCatalog();
}

function getProductLimit()
{
    return getProductCatalog(12);
}

function editproduct()
{
    global $conn;

    $id = (int) ($_POST['id'] ?? 0);
    $name = htmlspecialchars(trim($_POST['productName'] ?? ''));
    $about = htmlspecialchars(trim($_POST['productDesc'] ?? ''));
    $price = (int) ($_POST['productPrice'] ?? 0);
    $size = htmlspecialchars(trim($_POST['productSize'] ?? ''));
    $category_id = (int) ($_POST['productCategory'] ?? 0);
    $status = htmlspecialchars(trim($_POST['productStatus'] ?? ''));

    $productMedia = $_FILES['productMedia'] ?? null;

    if ($id <= 0 || $name === '' || $about === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Data produk tidak valid.'
        ]);
        exit;
    }

    // Ambil media lama
    $checkProduct = mysqli_prepare(
        $conn,
        "SELECT media FROM product WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($checkProduct, 'i', $id);
    mysqli_stmt_execute($checkProduct);

    $result = mysqli_stmt_get_result($checkProduct);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Produk tidak ditemukan.'
        ]);
        exit;
    }

    mysqli_begin_transaction($conn);

    $mediaName = $product['media'];

    // Jika upload media baru
    if (
        $productMedia &&
        ($productMedia['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
    ) {

        $uploadResult = storeUploadedMediaFile($productMedia, 'product');

        if (!$uploadResult[0]) {
            mysqli_rollback($conn);

            echo json_encode([
                'success' => false,
                'message' => $uploadResult[1]
            ]);
            exit;
        }

        $mediaName = $uploadResult[1];
    }

    // Update produk
    $query = mysqli_prepare(
        $conn,
        "UPDATE product
        SET category_id = ?, name = ?, size = ?, price = ?, about = ?, status = ?, media = ?
        WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $query,
        'ississsi',
        $category_id,
        $name,
        $size,
        $price,
        $about,
        $status,
        $mediaName,
        $id
    );

    if (!mysqli_stmt_execute($query)) {

        if (isset($uploadResult) && $uploadResult[0]) {
            @unlink(__DIR__ . '/assets/uploads/' . $mediaName);
        }

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => 'Gagal memperbarui produk.'
        ]);
        exit;
    }

    // Hapus media lama jika diganti
    if (
        isset($uploadResult) &&
        $uploadResult[0] &&
        !empty($product['media']) &&
        file_exists(__DIR__ . '/assets/uploads/' . $product['media'])
    ) {
        @unlink(__DIR__ . '/assets/uploads/' . $product['media']);
    }

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Produk berhasil diperbarui!'
    ]);
    exit;
}

function addVariant()
{
    global $conn;

    $productId = (int) ($_POST['product_id'] ?? 0);
    $color = trim($_POST['variantColor'] ?? '');
    $stock = (int) ($_POST['variantStock'] ?? 0);
    $variantImage = $_FILES['variantImage'] ?? null;

    if (
        $productId <= 0 ||
        $color === '' ||
        !$variantImage ||
        ($variantImage['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Lengkapi data varian dan gambar varian.'
        ]);
        exit;
    }

    // Pastikan produk ada
    $checkProduct = mysqli_prepare(
        $conn,
        "SELECT id FROM product WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($checkProduct, 'i', $productId);
    mysqli_stmt_execute($checkProduct);
    mysqli_stmt_store_result($checkProduct);

    if (mysqli_stmt_num_rows($checkProduct) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Produk tidak ditemukan.'
        ]);
        exit;
    }

    mysqli_begin_transaction($conn);

    // Upload gambar varian
    $uploadResult = storeUploadedImageFile($variantImage, 'variant');

    if (!$uploadResult[0]) {
        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => $uploadResult[1]
        ]);
        exit;
    }

    $imageName = $uploadResult[1];

    // Simpan varian
    $variantQuery = mysqli_prepare(
        $conn,
        "INSERT INTO product_variants
        (product_id, color, stock, image)
        VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $variantQuery,
        'isis',
        $productId,
        $color,
        $stock,
        $imageName
    );

    if (!mysqli_stmt_execute($variantQuery)) {

        @unlink(__DIR__ . '/assets/uploads/' . $imageName);

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambahkan varian.'
        ]);
        exit;
    }

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Varian berhasil ditambahkan.'
    ]);
    exit;
}
function editVariant()
{
    global $conn;

    $variantId = (int) ($_POST['variant_id'] ?? 0);
    $color = trim($_POST['variantColor'] ?? '');
    $stock = (int) ($_POST['variantStock'] ?? 0);
    $variantImage = $_FILES['variantImage'] ?? null;

    if ($variantId <= 0 || $color === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Data varian tidak valid.'
        ]);
        exit;
    }

    // Pastikan varian ada
    $checkVariant = mysqli_prepare(
        $conn,
        "SELECT image FROM product_variants WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($checkVariant, 'i', $variantId);
    mysqli_stmt_execute($checkVariant);

    $result = mysqli_stmt_get_result($checkVariant);
    $variant = mysqli_fetch_assoc($result);

    if (!$variant) {
        echo json_encode([
            'success' => false,
            'message' => 'Varian tidak ditemukan.'
        ]);
        exit;
    }

    mysqli_begin_transaction($conn);

    $imageName = $variant['image'];

    // Jika admin mengganti gambar
    if (
        $variantImage &&
        ($variantImage['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
    ) {

        $uploadResult = storeUploadedImageFile($variantImage, 'variant');

        if (!$uploadResult[0]) {
            mysqli_rollback($conn);

            echo json_encode([
                'success' => false,
                'message' => $uploadResult[1]
            ]);
            exit;
        }

        $imageName = $uploadResult[1];
    }

    // Update data varian
    $updateVariant = mysqli_prepare(
        $conn,
        "UPDATE product_variants
        SET color = ?, stock = ?, image = ?
        WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $updateVariant,
        'sisi',
        $color,
        $stock,
        $imageName,
        $variantId
    );

    if (!mysqli_stmt_execute($updateVariant)) {

        // Hapus file baru jika gagal update database
        if (
            isset($uploadResult) &&
            $uploadResult[0]
        ) {
            @unlink(__DIR__ . '/assets/uploads/' . $imageName);
        }

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => 'Gagal memperbarui varian.'
        ]);
        exit;
    }

    // Jika upload berhasil, hapus gambar lama
    if (
        isset($uploadResult) &&
        $uploadResult[0] &&
        !empty($variant['image']) &&
        file_exists(__DIR__ . '/assets/uploads/' . $variant['image'])
    ) {
        @unlink(__DIR__ . '/assets/uploads/' . $variant['image']);
    }

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Varian berhasil diperbarui.'
    ]);
    exit;
}

function deleteProduct($id)
{
    global $conn;

    $id = (int) $id;

    // Ambil media produk
    $productQuery = mysqli_prepare(
        $conn,
        "SELECT media
         FROM product
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($productQuery, 'i', $id);
    mysqli_stmt_execute($productQuery);

    $productResult = mysqli_stmt_get_result($productQuery);
    $product = mysqli_fetch_assoc($productResult);

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Produk tidak ditemukan.'
        ]);
        exit;
    }

    // Ambil semua gambar varian
    $variantQuery = mysqli_prepare(
        $conn,
        "SELECT image
         FROM product_variants
         WHERE product_id = ?"
    );

    mysqli_stmt_bind_param($variantQuery, 'i', $id);
    mysqli_stmt_execute($variantQuery);

    $variantResult = mysqli_stmt_get_result($variantQuery);

    $variantImages = [];

    while ($row = mysqli_fetch_assoc($variantResult)) {
        if (!empty($row['image'])) {
            $variantImages[] = $row['image'];
        }
    }

    mysqli_begin_transaction($conn);

    $deleteQuery = mysqli_prepare(
        $conn,
        "DELETE FROM product
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($deleteQuery, 'i', $id);

    if (!mysqli_stmt_execute($deleteQuery)) {

        mysqli_rollback($conn);

        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus produk.'
        ]);
        exit;
    }

    mysqli_commit($conn);

    // Hapus media produk
    if (!empty($product['media'])) {
        @unlink(__DIR__ . '/assets/uploads/' . $product['media']);
    }

    // Hapus gambar semua varian
    foreach (array_unique($variantImages) as $image) {
        @unlink(__DIR__ . '/assets/uploads/' . $image);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Produk berhasil dihapus.'
    ]);
    exit;
}

function addToCart()
{
    global $conn;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
        exit;
    }

    $userId = (int) $user['id'];
    $productId = (int) ($_POST['product_id'] ?? 0);
    $variantId = (int) ($_POST['variant_id'] ?? 0);

    if ($variantId > 0) {
        $qVariant = mysqli_prepare($conn, "
            SELECT pv.id, pv.product_id, pv.color, pv.stock, p.name
            FROM product_variants pv
            JOIN product p ON pv.product_id = p.id
            WHERE pv.id = ? AND p.id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($qVariant, 'ii', $variantId, $productId);
    } else {
        $qVariant = mysqli_prepare($conn, "
            SELECT pv.id, pv.product_id, pv.color, pv.stock, p.name
            FROM product_variants pv
            JOIN product p ON pv.product_id = p.id
            WHERE pv.product_id = ?
            ORDER BY pv.id ASC
            LIMIT 1
        ");
        mysqli_stmt_bind_param($qVariant, 'i', $productId);
    }

    mysqli_stmt_execute($qVariant);
    $variant = mysqli_fetch_assoc(mysqli_stmt_get_result($qVariant));

    if (!$variant) {
        echo json_encode(['success' => false, 'message' => 'Varian produk tidak ditemukan.']);
        exit;
    }

    $variantId = (int) $variant['id'];
    $productId = (int) $variant['product_id'];

    if ((int) $variant['stock'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Maaf, stok untuk warna ' . htmlspecialchars($variant['color']) . ' sudah habis!']);
        exit;
    }

    // 2. Cek apakah varian ini sudah ada di keranjang user
    $qCart = mysqli_prepare($conn, "SELECT id, quantity FROM carts WHERE user_id = ? AND variant_id = ?");
    mysqli_stmt_bind_param($qCart, 'ii', $userId, $variantId);
    mysqli_stmt_execute($qCart);
    $cartItem = mysqli_fetch_assoc(mysqli_stmt_get_result($qCart));

    if ($cartItem) {
        $currentQtyInCart = (int) $cartItem['quantity'];
        $newQtyPrediction = $currentQtyInCart + 1;

        // VALIDASI: Jika penambahan (+1) melampaui sisa stok di database, gagalkan!
        if ($newQtyPrediction > (int) $variant['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menambah! Jumlah di keranjangmu sudah mencapai batas maksimal stok warna ini (' . $variant['stock'] . ' pcs).'
            ]);
            exit;
        }

        // Jika aman, update quantity +1
        $updateCart = mysqli_prepare($conn, "UPDATE carts SET quantity = quantity + 1 WHERE id = ?");
        mysqli_stmt_bind_param($updateCart, 'i', $cartItem['id']);
        mysqli_stmt_execute($updateCart);

    } else {
        // Jika belum ada di keranjang, langsung insert quantity = 1
        $insertCart = mysqli_prepare($conn, "INSERT INTO carts (user_id, product_id, variant_id, quantity) VALUES (?, ?, ?, 1)");
        mysqli_stmt_bind_param($insertCart, 'iii', $userId, $productId, $variantId);
        mysqli_stmt_execute($insertCart);
    }

    echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang!']);
    exit;
}

// Berikan nilai default = null pada parameter $userId
function getCartItems($userId = null)
{
    global $conn;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $user = $_SESSION['user'] ?? null;

    // Jika parameter $userId tidak diisi saat fungsi dipanggil, ambil dari session login
    if ($userId === null) {
        if (!$user) {
            return [];
        }
        $userId = (int) $user['id'];
    } else {
        $userId = (int) $userId;
    }

    $query = "SELECT
                carts.id AS cart_id,
                carts.quantity,

                product.id AS product_id,
                product.name,
                product.price,
                product.status,

                pv.id AS variant_id,
                pv.color,
                pv.stock,
                pv.image

          FROM carts

          JOIN product
                ON carts.product_id = product.id

          LEFT JOIN product_variants pv
                ON pv.id = carts.variant_id

          WHERE carts.user_id = ?

          ORDER BY carts.created_at DESC";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $items = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }

    return $items;
}

function updateCartQty()
{
    global $conn;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Sesi Anda telah berakhir.']);
        exit;
    }

    $cartId = (int) $_POST['cart_id'];
    $quantity = (int) $_POST['quantity'];
    $userId = (int) $user['id'];

    if ($cartId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
        exit;
    }

    $checkQuery = mysqli_prepare($conn, "
        SELECT c.id, pv.stock, p.name, pv.color
        FROM carts c
        LEFT JOIN product_variants pv ON pv.id = COALESCE(
            c.variant_id,
            (
                SELECT pv2.id
                FROM product_variants pv2
                WHERE pv2.product_id = c.product_id
                ORDER BY pv2.id ASC
                LIMIT 1
            )
        )
        JOIN product p ON p.id = c.product_id
        WHERE c.id = ? AND c.user_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($checkQuery, 'ii', $cartId, $userId);
    mysqli_stmt_execute($checkQuery);
    $cart = mysqli_fetch_assoc(mysqli_stmt_get_result($checkQuery));

    if (!$cart) {
        echo json_encode(['success' => false, 'message' => 'Item keranjang tidak ditemukan.']);
        exit;
    }

    if ($quantity > (int) ($cart['stock'] ?? 0)) {
        echo json_encode(['success' => false, 'message' => 'Jumlah melebihi stok varian yang tersedia (' . (int) $cart['stock'] . ' pcs).']);
        exit;
    }

    // Pastikan baris keranjang tersebut memang benar milik user yang sedang login (keamanan)
    $query = mysqli_prepare($conn, "UPDATE carts SET quantity = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($query, 'iii', $quantity, $cartId, $userId);

    if (mysqli_stmt_execute($query)) {
        echo json_encode(['success' => true, 'message' => 'Jumlah berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
    }
    exit;
}

function deleteCartItemAction()
{
    global $conn;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Sesi Anda telah berakhir.']);
        exit;
    }

    $cartId = (int) $_POST['cart_id'];
    $userId = (int) $user['id'];

    if ($cartId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
        exit;
    }

    // Pastikan item keranjang yang dihapus murni milik user yang sedang login demi keamanan
    $query = mysqli_prepare($conn, "DELETE FROM carts WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($query, 'ii', $cartId, $userId);

    if (mysqli_stmt_execute($query)) {
        echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus dari keranjang.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus produk dari database.']);
    }
    exit;
}

function cancelOrderUserAction()
{
    global $conn;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Sesi Anda telah berakhir.']);
        exit;
    }

    $orderId = (int) $_POST['order_id'];
    $userId = (int) $user['id'];

    if ($orderId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID Pesanan tidak valid.']);
        exit;
    }

    // PENTING: Validasi keamanan berlapis.
    // Pesanan hanya boleh dibatalkan jika milik user yang login, statusnya masih 'pending', DAN belum ada bukti_pembayaran.
    $checkQuery = mysqli_prepare($conn, "SELECT status, bukti_pembayaran FROM orders WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($checkQuery, 'ii', $orderId, $userId);
    mysqli_stmt_execute($checkQuery);
    $result = mysqli_stmt_get_result($checkQuery);
    $order = mysqli_fetch_assoc($result);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        exit;
    }

    if ($order['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak dapat dibatalkan karena sedang diproses atau sudah selesai.']);
        exit;
    }

    if (!empty($order['bukti_pembayaran'])) {
        echo json_encode(['success' => false, 'message' => 'Pesanan tidak dapat dibatalkan karena bukti pembayaran sudah dikirim.']);
        exit;
    }

    // Jika lolos semua validasi, ubah status menjadi 'dibatalkan'
    $updateQuery = mysqli_prepare($conn, "UPDATE orders SET status = 'dibatalkan' WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($updateQuery, 'ii', $orderId, $userId);

    if (mysqli_stmt_execute($updateQuery)) {
        $itemsQuery = mysqli_prepare($conn, "SELECT variant_id, quantity FROM order_details WHERE order_id = ?");
        mysqli_stmt_bind_param($itemsQuery, 'i', $orderId);
        mysqli_stmt_execute($itemsQuery);
        $items = mysqli_stmt_get_result($itemsQuery);

        if ($items) {
            $restockQuery = mysqli_prepare($conn, "UPDATE product_variants SET stock = stock + ? WHERE id = ?");

            while ($item = mysqli_fetch_assoc($items)) {
                $variantId = (int) $item['variant_id'];
                $quantity = (int) $item['quantity'];
                mysqli_stmt_bind_param($restockQuery, 'ii', $quantity, $variantId);
                mysqli_stmt_execute($restockQuery);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status di database.']);
    }
    exit;
}


function requireOwnerAccess()
{
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit;
    }
    // Jika role bukan owner, tendang balik ke dashboard utama admin
    if ($_SESSION['user']['role'] !== 'owner') {
        header('Location: dashboard.php');
        exit;
    }
}

function getShopIdentity()
{
    global $conn;
    $query = mysqli_query($conn, "SELECT * FROM shop_identities WHERE id = 1 LIMIT 1");
    return mysqli_fetch_assoc($query);
}

function getShopBanks()
{
    global $conn;
    return mysqli_query($conn, "SELECT * FROM shop_banks ORDER BY id ASC");
}

function storeUploadedMediaFile(array $file, string $prefix = 'product')
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, 'Media gagal diunggah.'];
    }

    $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'mp4',
        'webm'
    ];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        return [false, 'Format media tidak didukung.'];
    }

    // Maksimal 20 MB
    $maxSize = 20 * 1024 * 1024;

    if ($file['size'] > $maxSize) {
        return [false, 'Ukuran media maksimal 20 MB.'];
    }

    $fileName = sprintf(
        '%s-%s.%s',
        $prefix,
        bin2hex(random_bytes(8)),
        $extension
    );

    $uploadPath = __DIR__ . '/assets/uploads/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [false, 'Gagal menyimpan media.'];
    }

    return [true, $fileName];
}