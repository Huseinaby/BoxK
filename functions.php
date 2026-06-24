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
    echo editProduct();
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

    // Tangkap data dari $_POST
    $name = htmlspecialchars($_POST['productName']);
    $about = htmlspecialchars($_POST['productAbout']);
    $color = htmlspecialchars($_POST['productColor']);
    $size = htmlspecialchars($_POST['productSize']);
    $category_id = (int) $_POST['productCategory'];
    $price = (int) $_POST['productPrice'];
    $stock = (int) $_POST['productStock']; // <-- TAMBAHKAN INI
    $status = htmlspecialchars($_POST['productStatus']);

    // --- Proses Upload Gambar (Logika gambar kamu yang sudah ada) ---
    $imageName = $_FILES['productImage']['name'];
    $tmpName = $_FILES['productImage']['tmp_name'];
    $ext = pathinfo($imageName, PATHINFO_EXTENSION);
    $newImageName = time() . '-' . uniqid() . '.' . $ext;
    $targetPath = "assets/uploads/" . $newImageName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        // UPDATE QUERY: Tambahkan kolom stock dan valuenya
        $query = "INSERT INTO product (category_id, name, color, size, price, stock, image, about, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssiisss', $category_id, $name, $color, $size, $price, $stock, $newImageName, $about, $status);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah gambar produk.']);
    }
    exit;
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

function editproduct()
{
    global $conn;

    $id = (int) $_POST['id'];
    $name = htmlspecialchars($_POST['productName']);
    $about = htmlspecialchars($_POST['productDesc']);
    $price = (int) $_POST['productPrice'];
    $stock = (int) $_POST['productStock']; // <-- TAMBAHKAN INI
    $color = htmlspecialchars($_POST['productColor']);
    $size = htmlspecialchars($_POST['productSize']);
    $category_id = (int) $_POST['productCategory'];
    $status = htmlspecialchars($_POST['productStatus']);

    // Cek apakah admin mengupload gambar baru
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === 0) {
        // JIKA GANTI GAMBAR
        $imageName = $_FILES['productImage']['name'];
        $tmpName = $_FILES['productImage']['tmp_name'];
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = time() . '-' . uniqid() . '.' . $ext;

        if (move_uploaded_file($tmpName, "assets/uploads/" . $newImageName)) {
            // UPDATE QUERY dengan Gambar & Stok Baru
            $query = "UPDATE product SET category_id = ?, name = ?, color = ?, size = ?, price = ?, stock = ?, image = ?, about = ?, status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'isssiisssi', $category_id, $name, $color, $size, $price, $stock, $newImageName, $about, $status, $id);
        }
    } else {
        // JIKA TIDAK GANTI GAMBAR (Hanya update data teks dan stok saja)
        $query = "UPDATE product SET category_id = ?, name = ?, color = ?, size = ?, price = ?, stock = ?, about = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssiissi', $category_id, $name, $color, $size, $price, $stock, $about, $status, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Produk berhasil diperbarui!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data produk.']);
    }
    exit;
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
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
        exit;
    }

    $userId = (int) $user['id'];
    $productId = (int) $_POST['product_id'];

    // 1. Ambil info stok asli produk saat ini dari database
    $qProduct = mysqli_prepare($conn, "SELECT stock, name FROM product WHERE id = ?");
    mysqli_stmt_bind_param($qProduct, 'i', $productId);
    mysqli_stmt_execute($qProduct);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($qProduct));

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
        exit;
    }

    // Jika stok produk di database sudah 0
    if ((int) $product['stock'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Maaf, stok untuk ' . htmlspecialchars($product['name']) . ' sudah habis!']);
        exit;
    }

    // 2. Cek apakah produk ini sudah ada di keranjang user
    $qCart = mysqli_prepare($conn, "SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($qCart, 'ii', $userId, $productId);
    mysqli_stmt_execute($qCart);
    $cartItem = mysqli_fetch_assoc(mysqli_stmt_get_result($qCart));

    if ($cartItem) {
        $currentQtyInCart = (int) $cartItem['quantity'];
        $newQtyPrediction = $currentQtyInCart + 1;

        // VALIDASI: Jika penambahan (+1) melampaui sisa stok di database, gagalkan!
        if ($newQtyPrediction > (int) $product['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menambah! Jumlah di keranjangmu sudah mencapai batas maksimal stok yang tersedia (' . $product['stock'] . ' pcs).'
            ]);
            exit;
        }

        // Jika aman, update quantity +1
        $updateCart = mysqli_prepare($conn, "UPDATE carts SET quantity = quantity + 1 WHERE id = ?");
        mysqli_stmt_bind_param($updateCart, 'i', $cartItem['id']);
        mysqli_stmt_execute($updateCart);

    } else {
        // Jika belum ada di keranjang, langsung insert quantity = 1
        $insertCart = mysqli_prepare($conn, "INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
        mysqli_stmt_bind_param($insertCart, 'ii', $userId, $productId);
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

    $query = "SELECT carts.id AS cart_id, carts.quantity, product.id AS product_id, 
                     product.name, product.price, product.image, product.status 
              FROM carts 
              JOIN product ON carts.product_id = product.id 
              WHERE carts.user_id = $userId 
              ORDER BY carts.created_at DESC";

    $result = mysqli_query($conn, $query);
    $items = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
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