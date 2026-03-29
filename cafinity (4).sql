-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2026 at 02:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cafinity`
--

-- --------------------------------------------------------

--
-- Table structure for table `coffee`
--

CREATE TABLE `coffee` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(6,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `rating` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffee`
--

INSERT INTO `coffee` (`id`, `name`, `price`, `image`, `rating`) VALUES
(1, 'Espresso', 120.00, 'coffee1.jpg', 5),
(2, 'Cappuccino', 150.00, 'coffee2.jpg', 5),
(3, 'Latte', 160.00, 'coffee3.jpg', 5),
(4, 'Mocha', 170.00, 'coffee8.jpg', 5),
(5, 'Espresso', 120.00, 'coffee1.jpg', 5),
(6, 'Cappuccino', 150.00, 'coffee2.jpg', 4),
(7, 'Latte', 160.00, 'coffee3.jpg', 5),
(8, 'Americano', 130.00, 'coffee4.jpg', 4),
(9, 'Flat White', 155.00, 'coffee5.jpg', 4),
(10, 'Macchiato', 140.00, 'coffee6.jpg', 3),
(11, 'Cortado', 145.00, 'coffee7.jpg', 4),
(13, 'Cold Brew', 160.00, 'coffee9.jpg', 5),
(14, 'Irish Coffee', 180.00, 'coffee10.jpg', 5);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coffee_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hidden` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `coffee_id`, `quantity`, `status`, `created_at`, `hidden`) VALUES
(9, 9, 13, 1, 'Approved', '2026-02-15 03:41:17', 0),
(10, 9, 4, 1, 'Approved', '2026-02-15 04:14:23', 0),
(11, 9, 8, 1, 'Pending', '2026-02-15 04:31:12', 0),
(12, 10, 8, 2, 'Pending', '2026-03-03 15:12:20', 0),
(13, 15, 11, 10, 'Cancelled', '2026-03-27 23:51:56', 0),
(14, 15, 11, 1, 'Cancelled', '2026-03-27 23:56:55', 0),
(15, 15, 2, 1, 'pending', '2026-03-28 00:02:12', 0),
(16, 15, 8, 1, 'pending', '2026-03-28 00:09:38', 0),
(17, 15, 3, 1, 'pending', '2026-03-28 00:14:32', 0),
(18, 15, 1, 1, 'pending', '2026-03-28 00:19:19', 0),
(19, 15, 9, 1, 'pending', '2026-03-28 00:21:49', 0),
(20, 15, 9, 1, 'pending', '2026-03-28 00:27:08', 0),
(21, 15, 2, 1, 'pending', '2026-03-28 00:32:53', 0),
(22, 15, 2, 1, 'Paid', '2026-03-28 00:41:46', 0),
(23, 15, 11, 10, 'Paid', '2026-03-28 00:42:46', 0),
(24, 15, 3, 1, 'pending', '2026-03-28 00:54:49', 0),
(25, 15, 2, 10, 'pending', '2026-03-28 00:54:50', 0),
(26, 15, 1, 1, 'pending', '2026-03-28 00:54:50', 0),
(27, 15, 2, 4, 'pending', '2026-03-28 00:57:19', 0),
(28, 15, 3, 1, 'pending', '2026-03-28 00:57:19', 0),
(29, 15, 1, 1, 'Paid', '2026-03-28 00:57:19', 0),
(30, 15, 3, 1, 'pending', '2026-03-28 01:01:32', 0),
(31, 15, 2, 1, 'pending', '2026-03-28 01:01:32', 0),
(32, 15, 1, 1, 'pending', '2026-03-28 01:01:32', 0),
(33, 15, 4, 1, 'pending', '2026-03-28 01:01:32', 0),
(34, 15, 8, 1, 'pending', '2026-03-28 01:01:32', 0),
(35, 15, 9, 1, 'Paid', '2026-03-28 01:01:33', 0),
(36, 16, 11, 2, 'Paid', '2026-03-28 01:03:43', 0),
(37, 16, 13, 1, 'Paid', '2026-03-28 01:03:43', 0),
(38, 16, 8, 1, 'Paid', '2026-03-28 01:08:24', 0),
(39, 16, 9, 2, 'Paid', '2026-03-28 01:08:24', 0),
(40, 16, 11, 1, 'Paid', '2026-03-28 01:10:29', 0),
(41, 16, 10, 1, 'Paid', '2026-03-28 01:10:29', 0);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Unpaid','Paid','Refunded') DEFAULT 'Unpaid',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer',
  `profile_image` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `profile_image`) VALUES
(6, 'admin', '$2y$10$N8kd6UPtyjW4ocPr47dyOuzuBnewQrseDNOev3KFSMgEmAv.8W75W', 'admin', 'default.png'),
(9, 'clark', '$2y$10$MYCWXeMmGUlK40uck4OMN.OUFE/GNEfN4jzL7rxKcSHW1qv6Q3TFW', 'customer', 'user_9.jpg'),
(10, 'clarkgarnica45@gmail.com', '$2y$10$FQEuN6ZLAM1GKxYgKkk28uqdALuRwC293wtIJBfX4z4CJxSiYZYL6', 'customer', 'default.png'),
(11, 'laklak', '$2y$10$B4IjuZLDhMG1NLuPn/PIhOnlY3MOb86gnxXQTtT8j1qzXPDZ4TJ5G', 'user', 'default.png'),
(12, 'mine', '$2y$10$0Q6RV63G9vSavwOr.iYlA.Z0BqU39i6WacZhQJD1Dqju0KT.LfJLW', 'user', 'default.png'),
(13, 'jedo', '$2y$10$mPE2xq4Zt7TzCUaZyNymIu485sOcpU6U4O7O806SGe8GNE6wpBQgm', 'user', 'default.png'),
(15, 'arlene', '$2y$10$cin4TvU1ummGk9o5wP6ylObMXEYS512PqlD1.3Q1PTzKftAOW0jom', 'customer', 'default.png'),
(16, 'clarky', '$2y$10$Zfi/5KIeEWhx8YIB4iXyTesVnqx1CHCs/.co/wfCekgWqPpfnwpWa', 'customer', 'default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coffee`
--
ALTER TABLE `coffee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coffee_id` (`coffee_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coffee`
--
ALTER TABLE `coffee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coffee_id`) REFERENCES `coffee` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
