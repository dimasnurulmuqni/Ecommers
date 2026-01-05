-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Des 2025 pada 06.01
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecomerce2_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
('CAT694e0310a0aa4', 'Jas Pria'),
('CAT694bc8bf5ea73', 'Kaos Pria'),
('CAT694bc8ca9e911', 'Kaos Wanita'),
('CAT694bc8d5ea27a', 'Kemeja Pria'),
('CAT694bc8d028759', 'Kemeja Wanita');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `order_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `shipping_method` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `upload` varchar(255) NOT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_amount`, `status`, `shipping_address`, `shipping_method`, `payment_method`, `note`, `upload`, `tracking_number`, `rejection_reason`, `shipping_cost`) VALUES
('ORD694d5705c97c5', 'USER694bc73058bde', '2025-12-25 16:23:49', 100000.00, 'shipped', 'Ucok Siregar, Sumedang, Sumedang, 12323, Telp: 082127602881', 'jne-regular', 'transfer', 'Warna Hitam', 'uploads/proof/proof_694d5705c0f7d.jpg', '120832183783', NULL, 25000.00),
('ORD694dfada3a847', 'USER694bc73058bde', '2025-12-26 04:02:50', 92000.00, 'shipped', 'Ucok Siregar, zzx, Sumedang, 12323, Telp: 082127602881', 'jnt-regular', 'transfer', '', 'uploads/proof/proof_694dfada37636.jpg', '7678678676', NULL, 22000.00),
('ORD694e0cc38f1ae', 'USER694bc73058bde', '2025-12-26 05:19:15', 180000.00, 'pending', 'VILVIN, Test Alamat Lengkap, Sumedang, 12345, Telp: 082127602881', 'jne-regular', 'transfer', 'Test', 'uploads/proof/proof_694e0cc38a556.jpg', NULL, NULL, 25000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price_at_order` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price_at_order`, `quantity`) VALUES
(4, 'ORD694d5705c97c5', 'PROD694bd6a9a7cff', 'Kaos Pria Tulisan One Life Sablon\\/Gambar DTF Besar', 25000.00, 3),
(5, 'ORD694dfada3a847', 'PROD694deecc5ed1f', 'Atasan Knit Wanita Korean Top Baju Knit Wanita Lengan Panjang Basic Long Sleeve', 45000.00, 1),
(6, 'ORD694dfada3a847', 'PROD694bd6a9a7cff', 'Kaos Pria Tulisan One Life Sablon\\/Gambar DTF Besar', 25000.00, 1),
(7, 'ORD694e0cc38f1ae', 'PROD694bd6a9a7cff', 'Kaos Pria Tulisan One Life Sablon\\/Gambar DTF Besar', 25000.00, 3),
(8, 'ORD694e0cc38f1ae', 'PROD694e02ee28d38', 'Kaos Wanita Polos Lengan Panjang Oval Terbaru - Kaos Wanita Adem Lengan Panjang', 35000.00, 1),
(9, 'ORD694e0cc38f1ae', 'PROD694deecc5ed1f', 'Atasan Knit Wanita Korean Top Baju Knit Wanita Lengan Panjang Basic Long Sleeve', 45000.00, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `price`, `stock`, `description`) VALUES
('PROD694bd6a9a7cff', 'Kaos Pria Tulisan One Life Sablon\\/Gambar DTF Besar', 'CAT694bc8bf5ea73', 25000.00, 2, 'Harap Dibaca Size Chart & Deskripsi Untuk Menghindari Kesalahan Ya Kak ,Terimakasih :)  Size M ~ Kisaran Berat Badan 45Kg - 55kg\r\nSize L ~ Kisaran Berat Badan 55kg - 65kg Size XL ~ Kisaran Berat Badan 65kg - 75kg \r\nJumbo Size Jumbo \\/ XXL ~ 75kg keatas'),
('PROD694deecc5ed1f', 'Atasan Knit Wanita Korean Top Baju Knit Wanita Lengan Panjang Basic Long Sleeve', 'CAT694bc8ca9e911', 45000.00, 98, 'Panduan Ukuran\r\nStandar : Fit to M, BB maks 45 Kg, Panjang Baju 56 cm, Panjang Lengan 56 cm, LD sebelum-Sesudah Melar 82-90 cm\r\n\r\nUpsize : Fit to L, BB 46-52 Kg, Panjang Baju 58 cm, Panjang Lengan 58 cm, Lingkar Dada Sebelum-Sesudah Melar 90-100 cm\r\n\r\nExtra : Fit to XL, BB 53-59 Kg, Panjang Baju 60 cm, Panjang Lengan 60 cm, Lingkar Dada Sebelum-Sesudah Melar 100-110 cm'),
('PROD694e02ee28d38', 'Kaos Wanita Polos Lengan Panjang Oval Terbaru - Kaos Wanita Adem Lengan Panjang', 'CAT694bc8ca9e911', 35000.00, 19, 'Tersedia 3 Size(Ukuran) M-L DAN XL\r\n\r\n\r\n SIZE ; M - Lingkar Dada 43 CM ( 86 cm Fit To 96 cm Seluruh Lingkaran Didada) -Panjang Baju 58 Cm (Maksimal BB 45 Kg)\r\n\r\n\r\n SIZE ;  L  -  Lingkar Dada 45 CM  (90 cm Fit To 100 cm Seluruh Lingkaran Didada) -Panjang Baju 60 Cm (Maksimal BB 50 Kg)\r\n\r\n\r\n SIZE ; XL - Lingkar Dada 47 CM  ( 94 cm Fit To 104 cm Seluruh Lingkaran Didada) -Panjang Baju 62 Cm (Maksimal BB 58 Kg)\r\n\r\n SIZE ; XXL - Lingkar Dada 49 CM  ( 98 cm Fit To 108 cm Seluruh Lingkaran Didada) -Panjang Baju 64 Cm (Maksimal BB 65 Kg)');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_main`) VALUES
(3, 'PROD694bd6a9a7cff', 'uploads/products/prod_694bd6a999704_1766577833.jpg', 1),
(4, 'PROD694bd6a9a7cff', 'uploads/products/prod_694bd6a9a592f_1766577833.jpg', 0),
(5, 'PROD694deecc5ed1f', 'uploads/products/prod_694deecc5bb2d_1766715084.jpg', 1),
(6, 'PROD694deecc5ed1f', 'uploads/products/prod_694deecc5d704_1766715084.jpg', 0),
(7, 'PROD694deecc5ed1f', 'uploads/products/prod_694deecc5de82_1766715084.jpg', 0),
(8, 'PROD694e02ee28d38', 'uploads/products/prod_694e02ee2627f_1766720238.jpg', 1),
(9, 'PROD694e02ee28d38', 'uploads/products/prod_694e02ee26ead_1766720238.jpg', 0),
(10, 'PROD694e02ee28d38', 'uploads/products/prod_694e02ee27739_1766720238.jpg', 0),
(11, 'PROD694e02ee28d38', 'uploads/products/prod_694e02ee27f5a_1766720238.jpg', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'consumer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
('USER694bc6c3a4fc1', 'admin', '$2y$10$4VP6eiDlsXx4Uuk4gSly6uJvVcK7iv8/nF2slfpP6F3fMPVeqmIli', 'admin'),
('USER694bc73058bde', 'user', '$2y$10$6zF9v0KEF25JxwSbY7UwyO/0GaIHZhiYTOTJZQ4Y30gPHTOJ/ysi.', 'consumer'),
('USER694e0b9de6202', 'Test', '$2y$10$.q7sWAogTKMnDVQXuXOCiO2FYhmfEHK5RCcP3VnfMNapr.xfo9JU2', 'consumer');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_user` (`user_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_item_order` (`order_id`),
  ADD KEY `fk_order_item_product` (`product_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_category` (`category_id`);

--
-- Indeks untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_image_product` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_image_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
