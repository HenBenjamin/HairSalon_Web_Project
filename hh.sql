-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2026 at 04:10 PM
-- Server version: 8.0.42-0ubuntu0.20.04.1
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hh`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `salon_id` int NOT NULL,
  `service_id` int NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('booked','cancelled','completed') COLLATE utf8mb4_general_ci DEFAULT 'booked',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `salon_id`, `service_id`, `appointment_date`, `appointment_time`, `status`, `created_at`) VALUES
(1, 16, 2, 1, '2026-01-15', '09:00:00', 'completed', '2026-01-13 15:19:47'),
(2, 16, 2, 2, '2026-01-15', '10:00:00', 'completed', '2026-01-13 16:03:30'),
(3, 19, 3, 4, '2026-01-19', '17:00:00', 'completed', '2026-01-15 09:29:26'),
(4, 16, 3, 3, '2026-01-20', '10:00:00', 'completed', '2026-01-16 17:00:40'),
(5, 16, 2, 2, '2026-01-22', '08:00:00', 'completed', '2026-01-18 10:27:02'),
(6, 1, 2, 1, '2026-01-22', '12:00:00', 'completed', '2026-01-18 10:29:49'),
(7, 10, 4, 8, '2026-01-19', '09:00:00', 'completed', '2026-01-18 10:45:52'),
(9, 19, 4, 9, '2026-01-19', '10:00:00', 'completed', '2026-01-18 17:52:31'),
(10, 10, 4, 8, '2026-01-19', '11:30:00', 'completed', '2026-01-18 18:03:11'),
(11, 1, 4, 8, '2026-01-19', '10:30:00', 'completed', '2026-01-18 18:18:12'),
(12, 21, 4, 8, '2026-01-19', '12:00:00', 'completed', '2026-01-18 18:39:23'),
(13, 22, 4, 8, '2026-01-19', '13:00:00', 'completed', '2026-01-18 18:55:06'),
(14, 16, 4, 8, '2026-01-18', '19:58:00', 'completed', '2026-01-18 18:59:05'),
(15, 10, 2, 2, '2026-02-02', '09:00:00', 'completed', '2026-01-30 14:44:29'),
(16, 10, 3, 3, '2026-02-04', '11:00:00', 'completed', '2026-01-30 14:58:23'),
(18, 1, 3, 7, '2026-02-02', '12:00:00', 'completed', '2026-01-30 15:16:59'),
(19, 22, 3, 3, '2026-02-02', '13:30:00', 'completed', '2026-01-30 15:30:11'),
(20, 19, 3, 4, '2026-01-30', '17:00:00', 'completed', '2026-01-30 15:36:52'),
(22, 16, 3, 3, '2026-02-02', '17:30:00', 'completed', '2026-02-02 15:34:37'),
(23, 1, 3, 3, '2026-02-04', '13:00:00', 'completed', '2026-02-02 15:51:35'),
(24, 19, 3, 4, '2026-02-04', '15:00:00', 'cancelled', '2026-02-02 15:54:36'),
(25, 10, 4, 8, '2026-02-05', '09:30:00', 'completed', '2026-02-02 17:48:11'),
(26, 1, 4, 8, '2026-02-05', '10:00:00', 'cancelled', '2026-02-02 17:48:54'),
(27, 1, 4, 8, '2026-02-05', '12:00:00', 'cancelled', '2026-02-02 18:26:03'),
(28, 10, 3, 3, '2026-02-05', '10:00:00', 'completed', '2026-02-02 20:08:19'),
(29, 10, 3, 3, '2026-02-04', '12:00:00', 'cancelled', '2026-02-02 20:09:55'),
(30, 19, 3, 4, '2026-02-06', '14:00:00', 'completed', '2026-02-03 08:51:06'),
(31, 19, 4, 9, '2026-02-09', '11:00:00', 'completed', '2026-02-03 08:59:20'),
(32, NULL, 4, 9, '2026-02-10', '09:00:00', 'cancelled', '2026-02-03 09:08:35'),
(33, NULL, 4, 8, '2026-02-11', '10:00:00', 'cancelled', '2026-02-03 09:25:49'),
(39, NULL, 2, 1, '2026-02-06', '10:00:00', 'cancelled', '2026-02-03 11:48:51'),
(41, 1, 4, 11, '2026-02-05', '12:00:00', 'completed', '2026-02-03 12:27:43'),
(42, 19, 4, 9, '2026-02-11', '09:00:00', 'booked', '2026-02-04 08:38:13'),
(43, NULL, 4, 11, '2026-02-09', '15:00:00', 'cancelled', '2026-02-04 08:39:35'),
(44, NULL, 2, 2, '2026-02-06', '14:00:00', 'cancelled', '2026-02-04 08:51:53'),
(46, NULL, 2, 1, '2026-02-05', '14:00:00', 'cancelled', '2026-02-04 09:11:56'),
(47, 32, 2, 1, '2026-02-05', '15:00:00', 'completed', '2026-02-04 09:56:55'),
(48, 10, 4, 8, '2026-02-06', '16:00:00', 'completed', '2026-02-04 10:12:07'),
(49, 10, 4, 8, '2026-02-06', '09:30:00', 'completed', '2026-02-04 10:19:43'),
(50, 10, 4, 8, '2026-02-18', '14:00:00', 'booked', '2026-02-04 10:33:06'),
(51, 32, 4, 8, '2026-02-16', '11:00:00', 'cancelled', '2026-02-04 10:52:01'),
(56, 1, 4, 11, '2026-02-24', '13:00:00', 'booked', '2026-02-04 18:58:52'),
(58, 10, 2, 1, '2026-02-25', '15:00:00', 'booked', '2026-02-05 14:39:18'),
(60, 32, 4, 8, '2026-02-13', '13:30:00', 'booked', '2026-02-05 17:28:58'),
(61, 32, 4, 8, '2026-02-13', '14:30:00', 'booked', '2026-02-05 17:31:30'),
(63, NULL, 3, 3, '2026-02-18', '14:00:00', 'cancelled', '2026-02-07 10:14:17'),
(64, 32, 3, 3, '2026-02-18', '14:00:00', 'booked', '2026-02-07 10:16:16'),
(65, 1, 3, 3, '2026-02-09', '18:00:00', 'booked', '2026-02-07 10:21:03'),
(66, 33, 4, 8, '2026-02-13', '15:00:00', 'booked', '2026-02-07 10:36:49'),
(67, 19, 4, 9, '2026-02-13', '15:30:00', 'booked', '2026-02-07 11:30:09'),
(69, 33, 3, 3, '2026-02-18', '10:00:00', 'booked', '2026-02-08 12:43:49'),
(71, 1, 4, 11, '2026-02-23', '14:00:00', 'booked', '2026-02-08 13:38:41'),
(72, 32, 4, 8, '2026-02-09', '12:00:00', 'completed', '2026-02-09 10:19:56'),
(74, 10, 4, 8, '2026-02-19', '13:30:00', 'booked', '2026-02-09 13:30:46'),
(75, 10, 4, 14, '2026-02-20', '11:30:00', 'booked', '2026-02-09 13:35:04');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int NOT NULL,
  `user_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `user_id`, `salon_id`, `created_at`) VALUES
(3, 32, 2, '2026-02-07 14:27:14'),
(6, 10, 4, '2026-02-08 13:14:29');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `gallery_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `user_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`gallery_id`, `salon_id`, `user_id`, `image_path`, `uploaded_at`) VALUES
(3, 2, 32, 'img_1770488699_6987837bf2649.jpg', '2026-02-07 18:24:59'),
(4, 2, 32, 'img_1770489482_6987868a76cb4.jpg', '2026-02-07 18:38:02'),
(5, 4, 33, 'img_1770557447_69889007eb103.jpg', '2026-02-08 13:30:47');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `salon_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(2, 3, 16, 5, 'Nagyon profi munka!', '2026-02-02 17:18:26'),
(3, 3, 19, 4, 'Profi!', '2026-02-02 17:21:13'),
(4, 4, 10, 5, 'Legjobb szalon.', '2026-02-02 17:31:43'),
(5, 2, 10, 1, 'Amator munka.', '2026-02-02 17:35:36'),
(6, 4, 19, 5, 'Le a kalappal!', '2026-02-09 10:35:12');

-- --------------------------------------------------------

--
-- Table structure for table `salons`
--

CREATE TABLE `salons` (
  `salon_id` int NOT NULL,
  `owner_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default_salon.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salons`
--

INSERT INTO `salons` (`salon_id`, `owner_id`, `name`, `address`, `city`, `phone`, `active`, `image_url`) VALUES
(2, 15, 'Nindzsa', 'Gellert utca 24', 'Szabadka', '016 454 758', 1, 'salon1.jpg'),
(3, 18, 'Flow', 'Petőfi Sándor utca 35', 'Szabadka', '011 2584 584', 1, 'salon2.jpg'),
(4, 20, 'Lily Style', 'Gyuri utca 6', 'Szabadka', '011 548 6487', 1, 'salon3.jpg'),
(5, 25, 'joker', 'sandor 25', 'subotica', '848484', 0, 'default_salon.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `price` int NOT NULL,
  `duration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `salon_id`, `name`, `price`, `duration`) VALUES
(1, 2, 'Férfi hajvágás', 1000, 30),
(2, 2, 'Borotvákozás', 500, 15),
(3, 3, 'Férfi hajvágás', 900, 30),
(4, 3, 'Női hajvágás', 1200, 30),
(5, 3, 'Női hajvágás+hajfestés', 2000, 60),
(6, 3, 'Hajfestés', 800, 30),
(7, 3, 'Hajmosás', 500, 15),
(8, 4, 'Férfi hajvágás', 800, 30),
(9, 4, 'Női hajvágás', 1300, 30),
(11, 4, 'Kutyanyírás', 2000, 60),
(14, 4, 'Hajfestés', 700, 30);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('user','owner','admin') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activation_token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `reset_token` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `username`, `password`, `role`, `created_at`, `activation_token`, `is_active`, `reset_token`, `reset_expires_at`) VALUES
(1, 'dzokica@gmail.com', 'dzokica', '$2y$12$hN.2HGsp5IKK7s1itJWkLuFrbgWV4CpIHBhkjNVlBttkfa1PN6I1C', 'user', '2025-12-01 17:22:25', '', 1, NULL, NULL),
(3, 'blans@gmail.com', 'blans', '$2y$12$CWvKvT9nIRCCe9.DH7POKulGBmuwUT6iJELZOuszPi2iXkocqpXsS', 'user', '2025-12-01 17:33:15', '41cf10d44492c2141d8cea01fb9993e6', 0, NULL, NULL),
(10, 'istvan@gmail.com', 'istvan', '$2y$12$nwCknSgw/k5hl9MGh4Mj8eUOd2sjsR1ZLRfPW0GWiSI3Wn8RpVB76', 'user', '2025-12-03 11:45:31', '', 1, NULL, NULL),
(15, 'bela@gmail.com', 'bela', '$2y$12$cQ1AQT7tIQhDEY03qd7tc.1l3OBXkkajVbuWOesVsXfF0Sdo5Kc.q', 'owner', '2026-01-08 15:37:55', '', 1, NULL, NULL),
(16, 'marci@gmail.com', 'marci', '$2y$12$omDfI9e9QwOxZ.ESCKr4SOlrQLIUGGtv9NE/SQXNQCHk025P5h0TW', 'user', '2026-01-08 15:44:39', '', 1, NULL, NULL),
(17, 'benjamin@gmail.com', 'benjamin', '$2y$12$XL98S8CC.E5l43QSgkt9WenRiNOPBNvAEgQsyQRsmF2VQnVlG5rry', 'admin', '2026-01-08 15:48:36', '', 1, NULL, NULL),
(18, 'maria@gmail.com', 'maria', '$2y$12$/fHj65vUqxScwaP5knppKunqZ9iS5QQ.WCMOqDBHusouKQnmmJs9q', 'owner', '2026-01-14 16:37:58', '', 1, NULL, NULL),
(19, 'beata@gmail.com', 'beata', '$2y$12$9rbCOu77cH3n1TnRxgh9UemzkzHSxcHFmVQwiHWPrVtT.RP.V6Ui6', 'user', '2026-01-15 09:25:51', '', 1, NULL, NULL),
(20, 'blanka@gmail.com', 'blanka', '$2y$12$4jZ387Y7z4vjWkqdAH7sQ.JiJmjS3Smpzwyag99C1xH1GJbp7ctli', 'owner', '2026-01-16 13:01:10', '', 1, NULL, NULL),
(21, 'tamas@gmail.com', 'tamas', '$2y$12$u3iVzbGx1M.Jfkgn7I.CZOhThSmBVh0seHgprLDigMz8r4SU4B9rS', 'user', '2026-01-18 18:37:40', '', 1, NULL, NULL),
(22, 'pityu@gmail.com', 'pityu', '$2y$10$2AditGiYX4EKZ6UqLMwpGe1DF3eEeLWV/a6CdPKfadRojB.STXili', 'user', '2026-01-18 18:53:38', '', 1, NULL, NULL),
(25, 'joker@gmail.com', 'joker', '$2y$12$LzzQaP2TP0mBQ9jrghIgE.UplsXQqhbrj3i0y/3LEfC9Uj4E7IvXe', 'owner', '2026-02-02 16:11:25', '', 1, NULL, NULL),
(28, 'john@gmail.com', 'john', '$2y$10$OiOJfcxKwJlXONdys3JLaey979b7JnYpv8ZhaA3IO4FtGsgP9iJHC', 'user', '2026-02-03 12:39:37', '', 1, NULL, NULL),
(29, 'blanka@office.com', 'Blanka office', '$2y$10$mzU3W9S/JV38Hz0NB9mPteJslFTIs13Aerpy2sE0mF2TlErlPmWqO', 'user', '2026-02-03 14:26:12', '7d12e9abdfd47b575552b59904144515', 0, NULL, NULL),
(32, 'berci@gmail.com', 'berci', '$2y$10$78qKUaJYkHlmAOko3cBcFuIpqMTmqchmOG2F6wV6PhP3Oax0s7TQ2', 'user', '2026-02-04 08:42:53', '', 1, NULL, NULL),
(33, 'bertold@gmail.com', 'Bertold', '$2y$10$7E/akLChMwA4l2.IsM57oO76HkZ0Z8QBxK2LQf3M0a274Me.XcxMO', 'user', '2026-02-07 10:12:37', '', 1, NULL, NULL),
(36, 'laszlo@gmail.com', 'Laszlo', '$2y$10$UMnGHlB.3QZwqrgK5lzhHere93saD.CmNtZhsEW1o8i3Yq3.i4Bzu', 'user', '2026-02-09 09:59:11', '', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `visitor_logs`
--

CREATE TABLE `visitor_logs` (
  `log_id` int NOT NULL,
  `user_id` int NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device_type` enum('mobile','tablet','pc') NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `login_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `visitor_logs`
--

INSERT INTO `visitor_logs` (`log_id`, `user_id`, `ip_address`, `device_type`, `city`, `login_time`) VALUES
(1, 32, '85.222.187.174', 'pc', 'TesztVaros', '2026-02-04 13:36:28'),
(2, 19, '85.222.187.174', 'pc', 'Subotica', '2026-02-04 13:42:03'),
(3, 20, '85.222.187.174', 'pc', 'Subotica', '2026-02-04 13:57:10'),
(4, 19, '85.222.187.174', 'pc', 'Subotica', '2026-02-04 13:58:25'),
(5, 32, '85.222.187.174', 'pc', 'Subotica', '2026-02-04 14:04:15'),
(6, 32, '85.222.187.174', 'mobile', 'Subotica', '2026-02-04 14:24:23'),
(7, 1, '85.222.187.174', 'pc', 'Subotica', '2026-02-04 20:08:08'),
(8, 1, '85.222.187.174', 'pc', 'Subotica', '2026-02-04 20:08:38'),
(9, 32, '85.222.187.174', 'pc', 'Subotica', '2026-02-05 09:23:24'),
(10, 10, '85.222.187.174', 'pc', 'Subotica', '2026-02-05 14:35:28'),
(11, 10, '85.222.187.174', 'pc', 'Subotica', '2026-02-05 17:18:47'),
(12, 32, '85.222.187.174', 'pc', 'Subotica', '2026-02-05 17:23:13'),
(13, 32, '85.222.187.174', 'pc', 'Subotica', '2026-02-05 17:28:44'),
(14, 32, '85.222.187.174', 'pc', 'Subotica', '2026-02-05 17:31:11'),
(15, 19, '178.223.65.4', 'pc', 'Novi Sad', '2026-02-06 19:14:21'),
(16, 1, '109.93.191.70', 'pc', 'Belgrade', '2026-02-07 10:18:46'),
(17, 19, '185.26.174.212', 'pc', 'Belgrade', '2026-02-07 11:07:34'),
(18, 19, '185.26.174.212', 'pc', 'Belgrade', '2026-02-07 11:43:06'),
(19, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 14:26:23'),
(20, 16, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 14:30:28'),
(21, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 14:54:57'),
(22, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 15:10:01'),
(23, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 15:16:24'),
(24, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 16:56:20'),
(25, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 17:06:49'),
(26, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 17:11:35'),
(27, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 17:36:42'),
(28, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 17:50:33'),
(29, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 17:52:51'),
(30, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 17:56:04'),
(31, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 18:06:52'),
(32, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 18:11:29'),
(33, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 18:19:31'),
(34, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 18:24:44'),
(35, 32, '109.92.193.178', 'pc', 'Belgrade', '2026-02-07 18:36:03'),
(36, 1, '178.223.65.4', 'pc', 'Novi Sad', '2026-02-08 13:16:57'),
(37, 1, '178.223.65.4', 'pc', 'Novi Sad', '2026-02-08 13:40:54'),
(38, 1, '178.223.65.4', 'pc', 'Novi Sad', '2026-02-08 13:41:06'),
(39, 10, '85.222.187.174', 'pc', 'Subotica', '2026-02-09 13:29:22'),
(40, 10, '85.222.187.174', 'pc', 'Subotica', '2026-02-09 13:48:03');

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

CREATE TABLE `waiting_list` (
  `wait_id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waiting_list`
--

INSERT INTO `waiting_list` (`wait_id`, `appointment_id`, `user_id`, `joined_at`) VALUES
(1, 25, 19, '2026-02-02 18:06:34'),
(2, 26, 16, '2026-02-02 18:11:52'),
(3, 25, 16, '2026-02-02 18:20:04'),
(4, 27, 16, '2026-02-02 18:26:28'),
(5, 27, 10, '2026-02-02 18:30:52'),
(6, 29, 16, '2026-02-02 20:10:18'),
(7, 30, 16, '2026-02-03 08:51:59'),
(8, 31, 16, '2026-02-03 08:59:55');

-- --------------------------------------------------------

--
-- Table structure for table `working_hours`
--

CREATE TABLE `working_hours` (
  `working_hour_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `day_of_week` int NOT NULL COMMENT '1: Hétfő, 7: Vasárnap',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_closed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `working_hours`
--

INSERT INTO `working_hours` (`working_hour_id`, `salon_id`, `day_of_week`, `start_time`, `end_time`, `is_closed`) VALUES
(1, 2, 1, '08:00:00', '16:00:00', 0),
(2, 2, 2, '08:00:00', '16:00:00', 0),
(3, 2, 3, '08:00:00', '16:00:00', 0),
(4, 2, 4, '08:00:00', '16:00:00', 0),
(5, 2, 5, '08:00:00', '16:00:00', 0),
(6, 2, 6, '08:00:00', '16:00:00', 1),
(7, 2, 7, '08:00:00', '16:00:00', 1),
(8, 3, 1, '09:00:00', '18:00:00', 0),
(9, 3, 2, '09:00:00', '18:00:00', 0),
(10, 3, 3, '09:00:00', '18:00:00', 0),
(11, 3, 4, '09:00:00', '18:00:00', 0),
(12, 3, 5, '09:00:00', '18:00:00', 0),
(13, 3, 6, '08:00:00', '12:00:00', 0),
(14, 3, 7, '08:00:00', '16:00:00', 1),
(15, 4, 1, '08:00:00', '16:00:00', 0),
(16, 4, 2, '08:00:00', '16:00:00', 0),
(17, 4, 3, '08:00:00', '16:00:00', 0),
(18, 4, 4, '08:00:00', '16:00:00', 0),
(19, 4, 5, '08:00:00', '16:00:00', 0),
(20, 4, 6, '09:00:00', '13:00:00', 0),
(21, 4, 7, '08:00:00', '16:00:00', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`salon_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`gallery_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_user_salon` (`user_id`,`salon_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `salons`
--
ALTER TABLE `salons`
  ADD PRIMARY KEY (`salon_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD PRIMARY KEY (`wait_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `working_hours`
--
ALTER TABLE `working_hours`
  ADD PRIMARY KEY (`working_hour_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `gallery_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salons`
--
ALTER TABLE `salons`
  MODIFY `salon_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `waiting_list`
--
ALTER TABLE `waiting_list`
  MODIFY `wait_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `working_hours`
--
ALTER TABLE `working_hours`
  MODIFY `working_hour_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gallery_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `salons`
--
ALTER TABLE `salons`
  ADD CONSTRAINT `salons_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`) ON DELETE CASCADE;

--
-- Constraints for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD CONSTRAINT `visitor_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD CONSTRAINT `waiting_list_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `waiting_list_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `working_hours`
--
ALTER TABLE `working_hours`
  ADD CONSTRAINT `working_hours_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
