-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 06, 2026 at 07:10 AM
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
  `variant_id` int NOT NULL,
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
  `courier` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `grand_total` int NOT NULL,
  `payment_method` enum('transfer_bank','dompet_digital','bayar_ditempat') NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status` enum('pending','proses','diantar','selesai','dibatalkan') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `invoice_number`, `shipping_method`, `nama_penerima`, `telepon`, `alamat_lengkap`, `catatan`, `total_items_price`, `shipping_cost`, `courier`, `tracking_number`, `grand_total`, `payment_method`, `bukti_pembayaran`, `status`, `created_at`) VALUES
(2, 4, 'INV-20260619-16392288', 'diambil', NULL, NULL, NULL, '', 230, 0, NULL, NULL, 230, 'dompet_digital', 'PAY-2-1782036536.png', 'selesai', '2026-06-19 16:39:22'),
(3, 4, 'INV-20260624-09193023', 'diambil', NULL, NULL, NULL, '', 40430, 0, NULL, NULL, 40430, 'dompet_digital', 'PAY-3-1782292783.jpg', 'selesai', '2026-06-24 09:19:30'),
(4, 4, 'INV-20260624-09273420', 'diambil', NULL, NULL, NULL, '', 330, 0, NULL, NULL, 330, 'transfer_bank', NULL, 'pending', '2026-06-24 09:27:34'),
(5, 4, 'INV-20260624-10032281', 'diambil', NULL, NULL, NULL, '', 100, 0, NULL, NULL, 100, 'dompet_digital', NULL, 'pending', '2026-06-24 10:03:22'),
(6, 4, 'INV-20260630-04324572', 'diambil', NULL, NULL, NULL, '', 230, 0, NULL, NULL, 230, 'transfer_bank', NULL, 'pending', '2026-06-30 04:32:45'),
(7, 4, 'INV-20260630-07345113', 'diambil', NULL, NULL, NULL, '', 1000, 0, NULL, NULL, 1000, 'dompet_digital', NULL, 'selesai', '2026-06-30 07:34:51'),
(8, 4, 'INV-20260630-07564728', 'diambil', NULL, NULL, NULL, '', 1840, 0, NULL, NULL, 1840, 'transfer_bank', NULL, 'selesai', '2026-06-30 07:56:47'),
(9, 4, 'INV-20260630-07580623', 'diambil', NULL, NULL, NULL, '', 100, 0, NULL, NULL, 100, 'transfer_bank', NULL, 'selesai', '2026-06-30 07:58:06'),
(10, 4, 'INV-20260801-08195374', 'diantar', 'husein', '0878153111', 'Jl. Ampera Raya No. 21', '', 200000, 10000, 'Gosend', 'https://localhost:8080/boxkado/admin/order-detail.php?id=10', 210000, 'transfer_bank', 'PAY-10-1785572406.jpg', 'selesai', '2026-08-01 08:19:53'),
(11, 4, 'INV-20260803-10084818', 'diambil', NULL, NULL, NULL, '', 107400, 0, NULL, NULL, 107400, 'dompet_digital', 'PAY-11-1785752332.jpg', 'selesai', '2026-08-03 10:08:48'),
(12, 4, 'INV-20260805-23311972', 'diambil', NULL, NULL, NULL, '', 35800, 0, NULL, NULL, 35800, 'bayar_ditempat', NULL, 'selesai', '2026-08-05 23:31:19');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `variant_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `variant_id`, `quantity`, `price`) VALUES
(3, 2, 2, 1, 230),
(4, 3, 3, 2, 20000),
(5, 3, 1, 2, 100),
(6, 3, 2, 1, 230),
(7, 4, 2, 1, 230),
(8, 4, 1, 1, 100),
(9, 5, 1, 1, 100),
(10, 6, 2, 1, 230),
(11, 7, 1, 10, 100),
(12, 8, 2, 8, 230),
(13, 9, 1, 1, 100),
(14, 10, 2, 1, 200000),
(15, 11, 7, 1, 35800),
(16, 11, 5, 2, 35800),
(17, 12, 5, 1, 35800);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `price` double DEFAULT NULL,
  `about` text,
  `status` enum('habis','tersedia') DEFAULT 'tersedia',
  `media` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `category_id`, `name`, `size`, `price`, `about`, `status`, `media`) VALUES
(7, 2, 'Kotak Besar', '16', 1000000, 'Kotak yang besar', 'tersedia', 'product-ecf8bc195e361b95.jpg'),
(8, 2, 'Kotak Kecil', 'kecil', 200000, 'kotak kedua', 'tersedia', 'product-1ec2f23fba56968c.mp4'),
(9, 2, 'kotak biru besar dengan pita', '15x23', 20000, 'kotak biru besar dengan pita', 'habis', 'product-85f560fcd0e40b70.jpg'),
(10, 2, 'Kotak kado kecil', '16x5 cm', 35800, 'kotak kado lebih kecil', 'tersedia', 'product-8b59ff2903d78b4c.mp4');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `variant_id` int NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `variant_id`, `image`, `is_primary`, `created_at`) VALUES
(1, 1, '69feef7640db0_id-11134207-82251-mgins64u1q159e.jpg', 1, '2026-08-02 14:04:19'),
(2, 2, '1785567706-6a6d99da87725.jpg', 0, '2026-08-02 14:04:19'),
(3, 3, '1782132266-6a392e2a53c2d.jpg', 1, '2026-08-02 14:04:19'),
(4, 4, 'variant-primary-1785681139-6a6f54f36282a.jpg', 0, '2026-08-02 14:32:19'),
(5, 2, 'variant-primary-1785683436-6a6f5dece8c13.png', 1, '2026-08-02 15:10:36'),
(6, 2, 'variant-gallery-1785683436-6a6f5dece9b11.png', 0, '2026-08-02 15:10:36'),
(7, 4, 'variant-primary-1785684778-6a6f632a472e8.jpg', 0, '2026-08-02 15:32:58'),
(8, 4, 'variant-primary-1785684791-6a6f6337a2054.png', 0, '2026-08-02 15:33:11'),
(9, 4, 'variant-primary-1785684818-6a6f6352754f9.jpg', 0, '2026-08-02 15:33:38'),
(10, 4, 'variant-primary-1785685918-6a6f679e8aa06.png', 1, '2026-08-02 15:51:58');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `color` varchar(50) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `color`, `stock`, `image`, `created_at`) VALUES
(1, 7, 'Merah', 14, '69feef7640db0_id-11134207-82251-mgins64u1q159e.jpg', '2026-08-02 14:04:09'),
(2, 8, 'kuning', 19, 'variant-primary-1785683436-6a6f5dece8c13.png', '2026-08-02 14:04:09'),
(3, 9, 'Kuning', 20, '1782132266-6a392e2a53c2d.jpg', '2026-08-02 14:04:09'),
(4, 8, 'merah', 12, 'variant-1785737897-6a7032a9e3358.jpg', '2026-08-02 14:32:19'),
(5, 10, 'Pink', 7, 'variant-1785740436-6a703c94eae2c.png', '2026-08-03 07:00:36'),
(6, 10, 'Biru Tua', 5, 'variant-1785740512-6a703ce043bab.png', '2026-08-03 07:01:52'),
(7, 10, 'Hijau Muda', 1, 'variant-1785740530-6a703cf275041.png', '2026-08-03 07:02:10');

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
(1, 'BoxKado BJM', '081234567890', 'JL. Teluk Tiram Darat', 'qris_1782295414.jpg', '2026-08-05 23:56:57');

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cart_variant` (`variant_id`);

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
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_orderdetail_variant` (`variant_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `category_product` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_image_variant` (`variant_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_variant_product` (`product_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_cart_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `fk_orderdetail_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`),
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `category_product` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_image_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
