-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 25, 2026 at 07:33 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `boxkado`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(2, 'keramik');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `shipping_method` enum('diantar','diambil') NOT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text,
  `catatan` text,
  `total_items_price` int NOT NULL,
  `shipping_cost` int NOT NULL,
  `grand_total` int NOT NULL,
  `payment_method` enum('transfer_bank','dompet_digital') NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status` enum('pending','proses','selesai','dibatalkan') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `invoice_number`, `shipping_method`, `nama_penerima`, `telepon`, `alamat_lengkap`, `catatan`, `total_items_price`, `shipping_cost`, `grand_total`, `payment_method`, `bukti_pembayaran`, `status`, `created_at`) VALUES
(1, 4, 'INV-20260619-16181670', 'diantar', 'abu hsuein', '087816', 'jl ampera raya', 'tidak ada', 790, 10000, 10790, 'transfer_bank', NULL, 'dibatalkan', '2026-06-19 16:18:16'),
(2, 4, 'INV-20260619-16392288', 'diambil', NULL, NULL, NULL, '', 230, 0, 230, 'dompet_digital', 'PAY-2-1782036536.png', 'selesai', '2026-06-19 16:39:22'),
(3, 4, 'INV-20260624-09193023', 'diambil', NULL, NULL, NULL, '', 40430, 0, 40430, 'dompet_digital', 'PAY-3-1782292783.jpg', 'selesai', '2026-06-24 09:19:30'),
(4, 4, 'INV-20260624-09273420', 'diambil', NULL, NULL, NULL, '', 330, 0, 330, 'transfer_bank', NULL, 'pending', '2026-06-24 09:27:34'),
(5, 4, 'INV-20260624-10032281', 'diambil', NULL, NULL, NULL, '', 100, 0, 100, 'dompet_digital', NULL, 'pending', '2026-06-24 10:03:22');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 8, 3, 230),
(2, 1, 7, 1, 100),
(3, 2, 8, 1, 230),
(4, 3, 9, 2, 20000),
(5, 3, 7, 2, 100),
(6, 3, 8, 1, 230),
(7, 4, 8, 1, 230),
(8, 4, 7, 1, 100),
(9, 5, 7, 1, 100);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `price` double DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `about` text,
  `status` enum('habis','tersedia') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `category_id`, `name`, `color`, `size`, `price`, `stock`, `image`, `about`, `status`) VALUES
(7, 2, 'Kotak Besar', 'Merah', '16', 100, 15, '69feef7640db0_id-11134207-82251-mgins64u1q159e.jpg', 'Kotak yang besar', 'tersedia'),
(8, 2, 'Kotak Kecil', 'biru', 'kecil', 230, 8, '1781862127-project cu (7).png', 'kotak kedua', 'tersedia'),
(9, 2, 'Kotak 3', 'Kuning', '15x23', 20000, 0, '1782132266-6a392e2a53c2d.jpg', 'Kotak ke 3', 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `shop_banks`
--

CREATE TABLE `shop_banks` (
  `id` int NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shop_banks`
--

INSERT INTO `shop_banks` (`id`, `bank_name`, `account_number`, `account_name`, `created_at`) VALUES
(1, 'BCA', '1234567890', 'Muhammad Abu Husein', '2026-06-24 09:53:45'),
(2, 'Mandiri', '9876543210', 'Muhammad Abu Husein', '2026-06-24 09:53:45'),
(3, 'BNI', '021231113331', 'Husein', '2026-06-24 09:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `shop_identities`
--

CREATE TABLE `shop_identities` (
  `id` int NOT NULL,
  `shop_name` varchar(100) NOT NULL DEFAULT 'BoxKado',
  `whatsapp` varchar(20) DEFAULT NULL,
  `address` text,
  `qris_image` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shop_identities`
--

INSERT INTO `shop_identities` (`id`, `shop_name`, `whatsapp`, `address`, `qris_image`, `updated_at`) VALUES
(1, 'BoxKado BJM', '081234567890', 'Alamat Toko', 'qris_1782295414.jpg', '2026-06-24 10:03:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('owner','admin','pelanggan') NOT NULL DEFAULT 'pelanggan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'husein', '$2y$10$0qcDQSCfzfzUYVhWSZi8k.ZOCWxtEY3ZWPP36UjFP7Fnb9jNzyIIW', 'owner'),
(3, 'admin', '$2y$10$.TGYUah2hOL2/TZiXIj6DeA3idYlZAVguT8t1czZv3eQMvJy6NjYi', 'admin'),
(4, 'abu', '$2y$10$qsRNFABIvSgGmUHBgBXW1.Tbp6P.pIoSkkrfGuSD/v/v30jboCj7a', 'pelanggan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `category_product` (`category_id`);

--
-- Indexes for table `shop_banks`
--
ALTER TABLE `shop_banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop_identities`
--
ALTER TABLE `shop_identities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `shop_banks`
--
ALTER TABLE `shop_banks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shop_identities`
--
ALTER TABLE `shop_identities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `category_product` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


ALTER TABLE orders
ADD COLUMN courier VARCHAR(50) NULL AFTER shipping_cost,
ADD COLUMN tracking_number VARCHAR(100) NULL AFTER courier;

ALTER TABLE orders
MODIFY COLUMN status ENUM(
    'pending',
    'proses',
    'diantar',
    'selesai',
    'dibatalkan'
) DEFAULT 'pending';

CREATE TABLE product_variants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  color VARCHAR(50) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_variant_product
    FOREIGN KEY (product_id)
    REFERENCES product(id)
    ON DELETE CASCADE
);

CREATE TABLE product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  variant_id INT NOT NULL,
  image VARCHAR(255) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_image_variant
    FOREIGN KEY (variant_id)
    REFERENCES product_variants(id)
    ON DELETE CASCADE
);

ALTER TABLE carts
ADD COLUMN variant_id INT NULL AFTER product_id,
ADD CONSTRAINT fk_cart_variant
  FOREIGN KEY (variant_id)
  REFERENCES product_variants(id)
  ON DELETE SET NULL;

ALTER TABLE order_details
ADD COLUMN variant_id INT NULL AFTER product_id,
ADD CONSTRAINT fk_orderdetail_variant
FOREIGN KEY (variant_id)
REFERENCES product_variants(id);

INSERT INTO product_variants (product_id, color, stock)
SELECT id, color, stock
FROM product;

INSERT INTO product_images (variant_id, image, is_primary)
SELECT pv.id, p.image, TRUE
FROM product p
JOIN product_variants pv ON pv.product_id = p.id;

UPDATE order_details od
JOIN product_variants pv ON od.product_id = pv.product_id
SET od.variant_id = pv.id;

ALTER TABLE order_details
MODIFY variant_id INT NOT NULL;

ALTER TABLE order_details
DROP COLUMN product_id;

ALTER TABLE product
DROP COLUMN color,
DROP COLUMN stock,
DROP COLUMN image;