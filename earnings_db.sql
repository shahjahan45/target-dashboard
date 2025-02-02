-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2025 at 08:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `earnings_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `actual_data`
--

CREATE TABLE `actual_data` (
  `id` int(11) NOT NULL,
  `month_year` date DEFAULT NULL,
  `actual_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `actual_data`
--

INSERT INTO `actual_data` (`id`, `month_year`, `actual_amount`, `created_at`) VALUES
(7, '2025-01-01', 5740.00, '2025-02-02 06:49:09'),
(8, '2025-01-01', 3810.00, '2025-02-02 06:49:18'),
(9, '2025-01-01', 6491.00, '2025-02-02 06:49:26'),
(10, '2025-01-01', 14748.00, '2025-02-02 06:49:34'),
(11, '2025-01-01', 15607.00, '2025-02-02 06:49:43'),
(12, '2025-01-01', 14090.00, '2025-02-02 06:49:52'),
(13, '2025-01-01', 8287.00, '2025-02-02 06:50:01'),
(14, '2025-01-01', 6418.00, '2025-02-02 06:50:09'),
(15, '2025-01-01', 3038.00, '2025-02-02 06:50:17'),
(16, '2025-01-01', 6259.00, '2025-02-02 06:50:25'),
(17, '2025-01-01', 7051.00, '2025-02-02 06:50:31'),
(18, '2025-01-01', 19711.00, '2025-02-02 06:50:41'),
(19, '2025-01-01', 530.00, '2025-02-02 06:50:53'),
(20, '2025-01-01', 4260.00, '2025-02-02 06:51:04'),
(21, '2025-01-01', 3097.00, '2025-02-02 06:51:13'),
(22, '2025-01-01', 6070.00, '2025-02-02 06:51:34'),
(23, '2025-01-01', 6270.00, '2025-02-02 06:51:47'),
(24, '2025-01-01', 6250.00, '2025-02-02 06:51:56'),
(25, '2025-01-01', 4843.00, '2025-02-02 06:52:04'),
(26, '2025-01-01', 4102.00, '2025-02-02 06:52:15'),
(27, '2025-01-01', 2560.00, '2025-02-02 06:52:24'),
(28, '2025-01-01', 22111.00, '2025-02-02 06:52:36'),
(29, '2025-01-01', 1909.00, '2025-02-02 06:52:45'),
(30, '2025-01-01', 7179.00, '2025-02-02 06:52:54'),
(31, '2025-01-01', 3279.00, '2025-02-02 06:53:07'),
(32, '2025-01-01', 6939.00, '2025-02-02 06:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `earnings`
--

CREATE TABLE `earnings` (
  `id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `month` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `earnings`
--

INSERT INTO `earnings` (`id`, `entry_date`, `month`, `amount`) VALUES
(1, '2025-02-01', 'April', 15000.00),
(2, '2025-02-01', 'February', 15000.00),
(3, '2025-02-01', 'February', 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `monthly_targets`
--

CREATE TABLE `monthly_targets` (
  `id` int(11) NOT NULL,
  `month_year` date DEFAULT NULL,
  `target_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monthly_targets`
--

INSERT INTO `monthly_targets` (`id`, `month_year`, `target_amount`, `created_at`) VALUES
(1, '2025-01-01', 283278.00, '2025-02-01 10:54:08'),
(2, '2025-02-01', 250000.00, '2025-02-01 14:09:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(3, 'admin1', '$2y$10$yourHashedPasswordHere', '2025-02-01 13:59:19'),
(5, 'admin', '$2y$10$wKK./O1VGhrD.C0JvLnNm.RregHWGZcqicIwJFKQAuF5kGqi.FGT6', '2025-02-01 14:08:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actual_data`
--
ALTER TABLE `actual_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `month_year` (`month_year`);

--
-- Indexes for table `earnings`
--
ALTER TABLE `earnings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monthly_targets`
--
ALTER TABLE `monthly_targets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `month_year` (`month_year`);

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
-- AUTO_INCREMENT for table `actual_data`
--
ALTER TABLE `actual_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `earnings`
--
ALTER TABLE `earnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `monthly_targets`
--
ALTER TABLE `monthly_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actual_data`
--
ALTER TABLE `actual_data`
  ADD CONSTRAINT `actual_data_ibfk_1` FOREIGN KEY (`month_year`) REFERENCES `monthly_targets` (`month_year`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
