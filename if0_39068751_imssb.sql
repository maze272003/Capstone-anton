-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql109.byetcluster.com
-- Generation Time: Jun 05, 2025 at 10:18 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39068751_imssb`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(3, 'Electrical and Electronics'),
(5, 'External'),
(10, 'Internal'),
(9, 'tires'),
(1, 'Wiring');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `file_name`, `file_type`) VALUES
(1, '1741778723_wheel.png', ''),
(2, '1741778784_light.jpg', ''),
(3, '1741778831_rear mirror.jpg', ''),
(4, '1741781162_nose.jpg', ''),
(5, '1742817684_me.jpg', ''),
(6, '1742817932_me.jpg', ''),
(7, '1742818101_Screenshot 2024-12-17 151535.png', ''),
(8, '1742818126_Code.png', ''),
(9, '1742818788_nose.jpg', ''),
(10, '1742965000_ME.jpg', ''),
(11, '1742965398_s-l1200.jpg', ''),
(12, '1742965484_71jnmZuUnyL._AC_SL1500_.jpg', ''),
(13, '1743120700_s-l1200.jpg', ''),
(14, '1743120858_Exam Output.png', ''),
(15, '1743154380_Page 2.pdf', ''),
(16, '1746590072_WIN_20231109_20_17_18_Pro.jpg', ''),
(17, '1747286348_Adobe Express - file (1).png', ''),
(18, '1747286627_Adobe Express - file (5).png', ''),
(19, '1747286732_Adobe Express - file.png', ''),
(20, '1747286771_Attack_1.png', ''),
(21, '1747286833_Adobe Express - file.png', ''),
(22, '1747286854_Attack_1.png', ''),
(23, '1747311564_Adobe Express - file (1).png', ''),
(24, '1749097509_494888875_1397662284715307_3402506248733688217_n.jpg', ''),
(25, '1749097540_494818735_1039619841635592_2312001772888473094_n.jpg', ''),
(26, '1749097647_494888355_1420857396032624_3911008052008362578_n.jpg', ''),
(27, '1749097880_Messenger_creation_E8EAAA53-0D1B-48EB-B3E3-B88CC64D81F5.jpeg', ''),
(28, '1749097971_Messenger_creation_3C322C08-AAF9-46E1-844D-EF0EA9AC9656.jpeg', ''),
(29, '1749098388_images.jpeg', ''),
(30, '1749098476_images (1).jpeg', ''),
(31, '1749098514_images (2).jpeg', ''),
(32, '1749098565_download.jpeg', ''),
(33, '1749098618_images (3).jpeg', ''),
(34, '1749101963_17491019227655803928630398016554.jpg', '');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verification`
--

CREATE TABLE `otp_verification` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` varchar(50) DEFAULT NULL,
  `buy_price` decimal(25,2) DEFAULT NULL,
  `sale_price` decimal(25,2) NOT NULL,
  `categorie_id` int(11) UNSIGNED NOT NULL,
  `media_id` int(11) DEFAULT 0,
  `date` datetime NOT NULL,
  `min_quantity` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `quantity`, `buy_price`, `sale_price`, `categorie_id`, `media_id`, `date`, `min_quantity`) VALUES
(1, 'sensors', '29', '200.00', '500.00', 1, 33, '2021-04-04 16:45:51', 100),
(14, 'Wheel', '24', '200.00', '220.00', 5, 1, '2025-02-13 03:45:41', 100),
(15, 'spark plug wire', '91', '150.00', '170.00', 1, 3, '2025-02-13 03:46:40', 100),
(16, 'grille', '42', '120.00', '130.00', 5, 26, '2025-02-13 07:01:33', 100),
(19, 'headlight', '47', '30.00', '50.00', 5, 25, '2025-03-24 13:08:21', 100),
(20, 'Batt6', '0', '30.00', '50.00', 3, 29, '2025-03-24 13:08:46', 100),
(21, 'front bumber', '47', '1500.00', '2000.00', 5, 24, '2025-03-26 05:56:40', 100),
(22, 'seats', '25', '400.00', '450.00', 9, 13, '2025-03-26 06:03:18', 100),
(23, 'Instrument Cluster', '23', '250.00', '300.00', 10, 28, '2025-03-26 06:04:44', 100),
(26, 'ReEngine Control Unitar Glass', '28', '200.00', '250.00', 10, 27, '2025-05-07 05:54:32', 100),
(33, 'wiring harness', '12', '13.00', '14.00', 1, 32, '2025-05-15 14:19:24', 100),
(34, 'alternator', '96', '1550.00', '2150.00', 3, 30, '2025-06-05 00:41:16', 100),
(35, 'fuses and relays', '117', '350.00', '550.00', 3, 31, '2025-06-05 00:41:54', 100),
(36, 'seat', '120', '200.00', '350.00', 9, 34, '2025-06-05 01:39:23', 100);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(25,2) NOT NULL,
  `date` date NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `qty`, `price`, `date`, `user_id`) VALUES
(12, 16, 2, '130.00', '2025-05-13', 0),
(13, 16, 3, '130.00', '2025-02-19', 0),
(14, 14, 4, '220.00', '2025-02-19', 0),
(15, 16, 3, '130.00', '2025-02-19', 0),
(16, 15, 1, '170.00', '2025-02-19', 0),
(17, 14, 3, '220.00', '2025-02-20', 0),
(18, 1, 2, '500.00', '2025-02-20', 0),
(19, 1, 2, '500.00', '2025-02-20', 0),
(20, 14, 3, '220.00', '2025-02-20', 0),
(21, 16, 3, '130.00', '2025-02-20', 0),
(22, 16, 3, '130.00', '2025-02-20', 0),
(23, 14, 2, '220.00', '2025-02-21', 0),
(24, 15, 2, '170.00', '2025-02-21', 0),
(25, 1, 1, '500.00', '2025-03-12', 0),
(26, 15, 1, '170.00', '2025-03-12', 0),
(27, 16, 2, '130.00', '2025-03-12', 0),
(28, 1, 20, '500.00', '2025-03-24', 0),
(29, 21, 1, '2000.00', '2025-03-26', 0),
(31, 14, 2, '220.00', '2025-05-07', 0),
(32, 1, 1, '500.00', '2025-05-09', 0),
(33, 15, 1, '170.00', '2025-05-09', 0),
(34, 22, 4, '450.00', '2025-05-09', 0),
(35, 23, 5, '300.00', '2025-05-09', 0),
(36, 14, 6, '220.00', '2025-05-09', 0),
(37, 16, 2, '130.00', '2025-05-09', 0),
(38, 20, 1, '50.00', '2025-06-05', 0),
(39, 34, 2, '2150.00', '2025-06-05', 0),
(40, 35, 1, '550.00', '2025-06-05', 0),
(41, 16, 2, '130.00', '2025-06-05', 0),
(42, 21, 2, '2000.00', '2025-06-05', 0),
(43, 14, 1, '220.00', '2025-06-05', 0),
(44, 19, 1, '50.00', '2025-06-05', 0),
(45, 23, 2, '300.00', '2025-06-05', 0),
(46, 26, 2, '250.00', '2025-06-05', 0),
(47, 22, 1, '450.00', '2025-06-05', 0),
(48, 35, 2, '550.00', '2025-06-05', 0),
(49, 20, 2, '50.00', '2025-06-05', 0),
(50, 34, 2, '2150.00', '2025-06-05', 0),
(51, 19, 2, '50.00', '2025-06-05', 0),
(52, 16, 2, '130.00', '2025-06-05', 0),
(53, 20, 47, '50.00', '2025-06-05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_level` int(11) NOT NULL,
  `image` varchar(255) DEFAULT 'no_image.jpg',
  `status` int(1) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `user_level`, `image`, `status`, `last_login`, `otp_code`, `otp_expiry`) VALUES
(1, 'Neil Ivan', 'Admin', '', 'd033e22ae348aeb5660fc2140aec35850c4da997', 1, 'rm0jhq131.jpg', 1, '2025-05-23 12:03:58', NULL, NULL),
(9, 'Dude Qwerty', 'Staff', '', '5d43e3169f06cf2a04a0ee870b5ac2aff3c558ff', 2, 'no_image.jpg', 1, '2025-03-13 14:53:48', NULL, NULL),
(12, 'Neil ', 'Dude', '', '615b8b65cf589b048bcb5ecfe5dea34c0512cfa6', 2, 'no_image.jpg', 1, '2025-05-23 10:47:07', NULL, NULL),
(13, 'John Michael', 'qwerty', '', '$2y$10$8F/MoGDE3a5iHsaDTwU56OtGuUGwxWr.NFnMuxfieqTO8xswB3tVa', 2, 'no_image.jpg', 1, NULL, NULL, NULL),
(14, 'John Michael test2', 'test2', '', 'b0399d2029f64d445bd131ffaa399a42d2f8e7dc', 2, 'no_image.jpg', 1, '2025-05-23 10:50:12', NULL, NULL),
(15, 'qpal', 'qpal', '', '6b2e6e1aad06363e8e352e0b1895b796c9e0a520', 2, 'no_image.jpg', 0, '2025-05-23 11:24:46', NULL, NULL),
(16, 'John Michael21212', 'qwertyuiop', 'admin4@gmail.com', 'b0399d2029f64d445bd131ffaa399a42d2f8e7dc', 1, 'no_image.jpg', 1, '2025-06-05 01:26:16', NULL, NULL),
(19, 'Dev', 'deve', 'jg.dev.au@phinmaed.com', 'b0399d2029f64d445bd131ffaa399a42d2f8e7dc', 2, 'no_image.jpg', 1, '2025-05-23 12:05:08', '627481', '2025-05-23 11:52:48'),
(21, 'paulodelavega', 'paulodelavega', 'paulodelavega@gmail.com', 'b0399d2029f64d445bd131ffaa399a42d2f8e7dc', 1, 'no_image.jpg', 1, '2025-06-05 01:38:07', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_level` int(11) NOT NULL,
  `group_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`id`, `group_name`, `group_level`, `group_status`) VALUES
(1, 'Admin', 1, 1),
(3, 'Staffs', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_level` (`user_level`);

--
-- Indexes for table `user_groups`
--
ALTER TABLE `user_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_level` (`group_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `otp_verification`
--
ALTER TABLE `otp_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_groups`
--
ALTER TABLE `user_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `FK_products` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `SK` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_user` FOREIGN KEY (`user_level`) REFERENCES `user_groups` (`group_level`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
