-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2026 at 05:03 AM
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
-- Database: `vehicle_service_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `mechanics`
--

CREATE TABLE `mechanics` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `status` enum('Available','Busy','Inactive') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mechanics`
--

INSERT INTO `mechanics` (`id`, `fullname`, `email`, `phone`, `specialization`, `status`, `created_at`) VALUES
(1, 'Juan', 'juan@example.com', '09123456789', 'Engine Repair', 'Available', '2026-08-10 04:44:18'),
(2, 'Pedro Santos', 'pedro@example.com', '09234567890', 'Brake and Suspension', 'Available', '2026-08-10 04:44:18'),
(3, 'Mark Reyes', 'mark@example.com', '09345678901', 'Electrical', 'Available', '2026-08-10 04:44:18'),
(4, 'Jay', 'jay@gmail.com', '+639451250831', 'General', 'Available', '2026-08-18 01:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','GCash','Bank Transfer','Card') NOT NULL DEFAULT 'Cash',
  `status` enum('Pending','Paid','Cancelled') NOT NULL DEFAULT 'Pending',
  `reference_number` varchar(100) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `reservation_id`, `amount`, `payment_method`, `status`, `reference_number`, `paid_at`, `created_at`) VALUES
(1, 8, 2000.00, 'Cash', 'Paid', '', '2026-08-20 10:25:33', '2026-08-20 02:24:51'),
(2, 9, 2000.00, 'Cash', 'Paid', '', '2026-08-21 10:58:34', '2026-08-21 02:58:32');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Approved','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `mechanic_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `vehicle_id`, `service_type`, `appointment_date`, `appointment_time`, `remarks`, `status`, `mechanic_id`, `created_at`) VALUES
(1, 6, 5, 'Oil Change', '2027-04-16', '19:41:00', 'haktog', 'Completed', NULL, '2026-08-12 18:36:12'),
(3, 5, 8, 'General Maintenance', '2026-09-10', '14:34:00', 'just general maintenance', 'Completed', NULL, '2026-08-13 06:34:49'),
(4, 5, 9, 'General Maintenance', '2026-10-09', '19:03:00', 'kasi inom ng inom ng coke kaya kaya kailangan magpageneral check up para makita kung anong problema ng aking system', 'Cancelled', NULL, '2026-08-13 11:04:01'),
(5, 5, 9, 'Oil Change', '2026-10-09', '08:00:00', 'need oil change mabaho na siya', 'Completed', 2, '2026-08-16 15:51:15'),
(6, 5, 9, 'General Maintenance', '2026-10-09', '09:29:00', '', 'Completed', 4, '2026-08-18 01:29:12'),
(7, 5, 9, 'General Maintenance', '2026-10-09', '22:33:00', '', 'Completed', 4, '2026-08-18 02:33:53'),
(8, 5, 9, 'Engine Check', '2026-08-20', '08:00:00', 'may sira ang engine', 'Completed', 1, '2026-08-19 10:22:43'),
(9, 5, 8, 'Engine Check', '2026-10-20', '08:30:00', '', 'Completed', 4, '2026-08-19 10:27:19');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_duration` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `description`, `price`, `estimated_duration`, `status`, `created_at`) VALUES
(1, 'General Maintenance', 'General inspection and preventive vehicle maintenance.', 1500.00, 120, 'Active', '2026-08-20 01:51:59'),
(2, 'Oil Change', 'Engine oil and oil filter replacement.', 1200.00, 60, 'Active', '2026-08-20 01:51:59'),
(3, 'Brake Service', 'Brake inspection, cleaning, adjustment, and repair.', 1800.00, 120, 'Active', '2026-08-20 01:51:59'),
(4, 'Engine Check', 'Engine inspection and diagnostic checking.', 2000.00, 120, 'Active', '2026-08-20 01:51:59'),
(5, 'Battery Service', 'Battery testing, charging, and terminal inspection.', 800.00, 45, 'Active', '2026-08-20 01:51:59'),
(6, 'Tire Service', 'Tire inspection, rotation, and basic tire service.', 1000.00, 60, 'Active', '2026-08-20 01:51:59'),
(7, 'Air Conditioning Service', 'Vehicle air-conditioning inspection and maintenance.', 1800.00, 120, 'Inactive', '2026-08-20 01:51:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','staff') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Kindred Jeb Guinto', 'kindredjebgold@gmail.com', '$2y$10$eSV1mdoFxI/JwhGJuTay0OorVEG8qMNPahrsODRsyTT6S.LX06uQ6', 'staff', '2026-08-02 12:01:32'),
(5, 'Patrice S.F. Guinto', 'kindredjeb@gmail.com', '$2y$10$EGRBapkqzLjwutz8BMFd..TSOKzfiVE.sgvh.3lHD9FerxiyOrDfO', 'customer', '2026-08-10 06:03:03'),
(6, 'karl', 'karl@gmail.com', '$2y$10$q9unYfE/Hl0NztZpZv9JOeVv9rsID4yHg53bbFL79Q6uZFYuWB.ia', 'customer', '2026-08-12 03:03:59');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` year(4) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `brand`, `model`, `year`, `plate_number`, `color`, `created_at`) VALUES
(5, 6, 'toyota', 'inova', '2001', 'nbx 9268', 'black', '2026-08-12 18:32:13'),
(8, 5, 'toyota', 'inova', '2026', 'nbx 9268', 'black', '2026-08-13 03:24:14'),
(9, 5, 'Suzuki', 'Celerio', '2013', 'fpj 0516', 'white', '2026-08-13 11:01:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mechanics`
--
ALTER TABLE `mechanics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reservation_payment` (`reservation_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `fk_reservation_mechanic` (`mechanic_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mechanics`
--
ALTER TABLE `mechanics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservation_mechanic` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
