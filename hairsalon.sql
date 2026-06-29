-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Jún 29. 12:11
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `hairsalon`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `salon_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('booked','cancelled','completed') DEFAULT 'booked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `coupon_id` int(11) DEFAULT NULL,
  `final_price` int(11) DEFAULT NULL,
  `reminder_sent` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `salon_id`, `service_id`, `appointment_date`, `appointment_time`, `status`, `created_at`, `coupon_id`, `final_price`, `reminder_sent`) VALUES
(1, 16, 2, 1, '2026-01-15', '09:00:00', 'completed', '2026-01-13 15:19:47', NULL, NULL, 0),
(2, 16, 2, 2, '2026-01-15', '10:00:00', 'completed', '2026-01-13 16:03:30', NULL, NULL, 0),
(3, 19, 3, 4, '2026-01-19', '17:00:00', 'completed', '2026-01-15 09:29:26', NULL, NULL, 0),
(4, 16, 3, 3, '2026-01-20', '10:00:00', 'completed', '2026-01-16 17:00:40', NULL, NULL, 0),
(5, 16, 2, 2, '2026-01-22', '08:00:00', 'completed', '2026-01-18 10:27:02', NULL, NULL, 0),
(6, 1, 2, 1, '2026-01-22', '12:00:00', 'completed', '2026-01-18 10:29:49', NULL, NULL, 0),
(7, 10, 4, 8, '2026-01-19', '09:00:00', 'completed', '2026-01-18 10:45:52', NULL, NULL, 0),
(9, 19, 4, 9, '2026-01-19', '10:00:00', 'completed', '2026-01-18 17:52:31', NULL, NULL, 0),
(10, 10, 4, 8, '2026-01-19', '11:30:00', 'completed', '2026-01-18 18:03:11', NULL, NULL, 0),
(11, 1, 4, 8, '2026-01-19', '10:30:00', 'completed', '2026-01-18 18:18:12', NULL, NULL, 0),
(12, 21, 4, 8, '2026-01-19', '12:00:00', 'completed', '2026-01-18 18:39:23', NULL, NULL, 0),
(13, 22, 4, 8, '2026-01-19', '13:00:00', 'completed', '2026-01-18 18:55:06', NULL, NULL, 0),
(14, 16, 4, 8, '2026-01-18', '19:58:00', 'completed', '2026-01-18 18:59:05', NULL, NULL, 0),
(15, 10, 2, 2, '2026-02-02', '09:00:00', 'completed', '2026-01-30 14:44:29', NULL, NULL, 0),
(16, 10, 3, 3, '2026-02-04', '11:00:00', 'completed', '2026-01-30 14:58:23', NULL, NULL, 0),
(18, 1, 3, 7, '2026-02-02', '12:00:00', 'completed', '2026-01-30 15:16:59', NULL, NULL, 0),
(19, 22, 3, 3, '2026-02-02', '13:30:00', 'completed', '2026-01-30 15:30:11', NULL, NULL, 0),
(20, 19, 3, 4, '2026-01-30', '17:00:00', 'completed', '2026-01-30 15:36:52', NULL, NULL, 0),
(22, 16, 3, 3, '2026-02-02', '17:30:00', 'completed', '2026-02-02 15:34:37', NULL, NULL, 0),
(23, 1, 3, 3, '2026-02-04', '13:00:00', 'completed', '2026-02-02 15:51:35', NULL, NULL, 0),
(24, 19, 3, 4, '2026-02-04', '15:00:00', 'cancelled', '2026-02-02 15:54:36', NULL, NULL, 0),
(25, 10, 4, 8, '2026-02-05', '09:30:00', 'completed', '2026-02-02 17:48:11', NULL, NULL, 0),
(26, 1, 4, 8, '2026-02-05', '10:00:00', 'cancelled', '2026-02-02 17:48:54', NULL, NULL, 0),
(27, 1, 4, 8, '2026-02-05', '12:00:00', 'cancelled', '2026-02-02 18:26:03', NULL, NULL, 0),
(28, 10, 3, 3, '2026-02-05', '10:00:00', 'completed', '2026-02-02 20:08:19', NULL, NULL, 0),
(29, 10, 3, 3, '2026-02-04', '12:00:00', 'cancelled', '2026-02-02 20:09:55', NULL, NULL, 0),
(30, 19, 3, 4, '2026-02-06', '14:00:00', 'completed', '2026-02-03 08:51:06', NULL, NULL, 0),
(31, 19, 4, 9, '2026-02-09', '11:00:00', 'completed', '2026-02-03 08:59:20', NULL, NULL, 0),
(32, NULL, 4, 9, '2026-02-10', '09:00:00', 'cancelled', '2026-02-03 09:08:35', NULL, NULL, 0),
(33, NULL, 4, 8, '2026-02-11', '10:00:00', 'cancelled', '2026-02-03 09:25:49', NULL, NULL, 0),
(34, 10, 4, 8, '2026-02-11', '10:00:00', 'completed', '2026-02-03 09:37:01', NULL, NULL, 0),
(39, NULL, 2, 1, '2026-02-06', '10:00:00', 'cancelled', '2026-02-03 11:48:51', NULL, NULL, 0),
(41, 1, 4, 11, '2026-02-05', '12:00:00', 'completed', '2026-02-03 12:27:43', NULL, NULL, 0),
(42, 19, 4, 9, '2026-02-11', '09:00:00', 'completed', '2026-02-04 08:38:13', NULL, NULL, 0),
(43, 19, 4, 11, '2026-02-09', '15:00:00', 'completed', '2026-02-04 08:39:35', NULL, NULL, 0),
(44, NULL, 2, 2, '2026-02-06', '14:00:00', 'cancelled', '2026-02-04 08:51:53', NULL, NULL, 0),
(46, NULL, 2, 1, '2026-02-05', '14:00:00', 'cancelled', '2026-02-04 09:11:56', NULL, NULL, 0),
(47, 32, 2, 1, '2026-02-05', '15:00:00', 'completed', '2026-02-04 09:56:55', NULL, NULL, 0),
(48, 10, 4, 8, '2026-02-06', '16:00:00', 'completed', '2026-02-04 10:12:07', NULL, NULL, 0),
(49, 10, 4, 8, '2026-02-06', '09:30:00', 'completed', '2026-02-04 10:19:43', NULL, NULL, 0),
(50, 10, 4, 8, '2026-02-18', '14:00:00', 'completed', '2026-02-04 10:33:06', NULL, NULL, 0),
(51, 32, 4, 8, '2026-02-16', '11:00:00', 'cancelled', '2026-02-04 10:52:01', NULL, NULL, 0),
(56, 1, 4, 11, '2026-02-24', '13:00:00', 'completed', '2026-02-04 18:58:52', NULL, NULL, 0),
(58, 10, 2, 1, '2026-02-25', '15:00:00', 'completed', '2026-02-05 14:39:18', NULL, NULL, 0),
(60, 32, 4, 8, '2026-02-13', '13:30:00', 'completed', '2026-02-05 17:28:58', NULL, NULL, 0),
(61, 32, 4, 8, '2026-02-13', '14:30:00', 'completed', '2026-02-05 17:31:30', NULL, NULL, 0),
(63, NULL, 3, 3, '2026-02-18', '14:00:00', 'cancelled', '2026-02-07 10:14:17', NULL, NULL, 0),
(64, 32, 3, 3, '2026-02-18', '14:00:00', 'completed', '2026-02-07 10:16:16', NULL, NULL, 0),
(65, 1, 3, 3, '2026-02-09', '18:00:00', 'completed', '2026-02-07 10:21:03', NULL, NULL, 0),
(66, 33, 4, 8, '2026-02-13', '15:00:00', 'completed', '2026-02-07 10:36:49', NULL, NULL, 0),
(67, 19, 4, 9, '2026-02-13', '15:30:00', 'completed', '2026-02-07 11:30:09', NULL, NULL, 0),
(69, 33, 3, 3, '2026-02-18', '10:00:00', 'completed', '2026-02-08 12:43:49', NULL, NULL, 0),
(71, 1, 4, 11, '2026-02-23', '14:00:00', 'completed', '2026-02-08 13:38:41', NULL, NULL, 0),
(72, 32, 4, 8, '2026-05-11', '12:00:00', 'completed', '2026-05-06 12:34:55', NULL, NULL, 0),
(73, 28, 4, 8, '2026-05-14', '09:30:00', 'completed', '2026-05-12 08:32:29', NULL, NULL, 0),
(74, 28, 3, 3, '2026-05-13', '10:00:00', 'completed', '2026-05-12 08:46:45', NULL, NULL, 0),
(75, 10, 4, 11, '2026-05-13', '09:00:00', 'completed', '2026-05-12 16:05:23', 2, 2000, 0),
(76, 33, 4, 11, '2026-05-13', '11:00:00', 'completed', '2026-05-12 16:17:23', NULL, 2000, 0),
(77, 33, 4, 8, '2026-05-13', '12:00:00', 'completed', '2026-05-12 16:23:24', 3, 640, 0),
(78, 28, 3, 3, '2026-05-14', '11:00:00', 'completed', '2026-05-13 16:35:04', 4, 720, 1),
(79, 28, 4, 8, '2026-05-16', '12:30:00', 'completed', '2026-05-16 09:55:13', NULL, 800, 0),
(80, 28, 4, 8, '2026-05-19', '09:00:00', 'completed', '2026-05-16 10:09:40', 5, 640, 0),
(81, 32, 4, 8, '2026-05-19', '10:00:00', 'completed', '2026-05-16 10:17:29', NULL, NULL, 0),
(82, 16, 4, 8, '2026-05-20', '08:00:00', 'completed', '2026-05-20 09:44:16', NULL, NULL, 0),
(83, 16, 4, 8, '2026-05-20', '13:30:00', 'completed', '2026-05-20 10:21:33', NULL, NULL, 0),
(84, 16, 4, 8, '2026-05-20', '15:00:00', 'completed', '2026-05-20 11:09:43', 8, 640, 0),
(85, 28, 3, 3, '2026-05-21', '10:00:00', 'completed', '2026-05-20 11:24:18', NULL, 900, 0),
(86, 28, 3, 3, '2026-05-21', '11:00:00', 'completed', '2026-05-20 11:24:29', NULL, 900, 0),
(87, 28, 3, 3, '2026-05-21', '13:30:00', 'completed', '2026-05-20 11:24:47', NULL, 900, 0),
(88, 28, 4, 8, '2026-05-21', '11:00:00', 'completed', '2026-05-20 11:35:37', NULL, 800, 0),
(89, 28, 4, 8, '2026-05-21', '12:00:00', 'completed', '2026-05-20 11:35:56', NULL, 800, 0),
(91, 38, 4, 8, '2026-05-29', '12:00:00', 'completed', '2026-05-28 13:10:21', NULL, 800, 0),
(92, 38, 3, 3, '2026-05-29', '11:00:00', 'completed', '2026-05-28 13:11:15', NULL, 900, 0),
(94, 38, 4, 8, '2026-05-29', '15:00:00', 'completed', '2026-05-28 13:19:15', NULL, 800, 0),
(95, 38, 3, 3, '2026-05-29', '16:00:00', 'completed', '2026-05-28 13:20:40', NULL, 900, 0),
(96, 38, 3, 3, '2026-05-29', '09:00:00', 'completed', '2026-05-28 14:13:23', NULL, NULL, 0),
(97, 38, 4, 8, '2026-06-01', '09:30:00', 'completed', '2026-05-30 09:50:39', NULL, 800, 0),
(98, 38, 3, 4, '2026-06-03', '12:00:00', 'completed', '2026-05-30 20:42:40', NULL, NULL, 0),
(100, 38, 4, 9, '2026-06-01', '10:00:00', 'completed', '2026-05-31 15:44:18', NULL, NULL, 0),
(101, 16, 4, 8, '2026-06-03', '09:00:00', 'completed', '2026-06-03 13:02:41', NULL, NULL, 0),
(102, 38, 4, 8, '2026-06-03', '15:30:00', 'completed', '2026-06-03 13:18:45', 9, 640, 0),
(103, 21, 3, 3, '2026-06-03', '17:00:00', 'completed', '2026-06-03 14:10:38', NULL, NULL, 0),
(104, 21, 3, 3, '2026-06-04', '16:30:00', 'completed', '2026-06-03 14:14:43', NULL, 900, 1),
(105, 21, 3, 3, '2026-06-04', '13:00:00', 'completed', '2026-06-03 14:35:11', NULL, 900, 1),
(106, 21, 3, 3, '2026-06-04', '14:00:00', 'completed', '2026-06-03 14:38:01', NULL, NULL, 1),
(107, 16, 3, 5, '2026-06-04', '13:30:00', 'completed', '2026-06-03 15:00:49', NULL, NULL, 1),
(108, 21, 4, 8, '2026-06-06', '09:00:00', 'completed', '2026-06-04 12:13:19', NULL, 800, 0),
(109, 16, 4, 8, '2026-06-05', '15:00:00', 'completed', '2026-06-05 12:25:25', 10, 640, 0),
(110, 16, 2, 1, '2026-06-08', '09:00:00', 'completed', '2026-06-05 12:36:00', NULL, NULL, 0),
(112, 28, 3, 3, '2026-06-08', '13:30:00', 'completed', '2026-06-05 13:17:05', NULL, NULL, 0),
(113, 32, 3, 7, '2026-06-08', '10:30:00', 'completed', '2026-06-05 13:17:46', NULL, NULL, 0),
(114, 28, 3, 3, '2026-06-08', '11:30:00', 'completed', '2026-06-05 13:28:54', NULL, NULL, 0),
(115, NULL, 4, 8, '2026-06-08', '11:00:00', 'cancelled', '2026-06-06 11:25:06', NULL, NULL, 0),
(116, 28, 4, 8, '2026-06-08', '10:00:00', 'completed', '2026-06-07 15:39:12', NULL, 800, 0),
(117, NULL, 4, 8, '2026-06-08', '09:00:00', 'cancelled', '2026-06-07 18:11:43', NULL, NULL, 0),
(118, 16, 4, 8, '2026-06-08', '10:30:00', 'completed', '2026-06-07 18:17:24', NULL, NULL, 0),
(119, 16, 4, 8, '2026-06-08', '12:00:00', 'completed', '2026-06-07 19:41:28', NULL, NULL, 0),
(120, 16, 4, 8, '2026-06-08', '14:30:00', 'completed', '2026-06-07 20:25:32', NULL, NULL, 0),
(121, 41, 4, 8, '2026-06-09', '12:00:00', 'completed', '2026-06-08 18:03:58', NULL, 800, 0),
(122, 41, 4, 8, '2026-06-09', '13:00:00', 'cancelled', '2026-06-08 18:24:28', NULL, NULL, 0),
(123, 16, 4, 8, '2026-06-09', '14:30:00', 'completed', '2026-06-08 18:32:02', NULL, 800, 0),
(124, 32, 3, 5, '2026-06-09', '10:00:00', 'booked', '2026-06-08 18:43:21', NULL, NULL, 0),
(125, 16, 3, 7, '2026-06-09', '11:00:00', 'booked', '2026-06-08 18:44:02', NULL, NULL, 0),
(127, NULL, 4, 8, '2026-06-11', '09:00:00', 'cancelled', '2026-06-10 16:02:17', NULL, NULL, 0),
(128, 16, 4, 8, '2026-06-12', '10:00:00', 'completed', '2026-06-11 18:01:26', NULL, 800, 0),
(129, 16, 4, 14, '2026-06-15', '09:00:00', 'completed', '2026-06-13 08:24:06', NULL, 2500, 0),
(130, 16, 4, 8, '2026-06-15', '10:00:00', 'cancelled', '2026-06-13 09:06:28', NULL, NULL, 0),
(131, 16, 4, 8, '2026-06-15', '10:00:00', 'cancelled', '2026-06-13 09:11:10', NULL, NULL, 0),
(132, 42, 4, 8, '2026-06-15', '10:00:00', 'completed', '2026-06-13 09:52:32', NULL, 800, 0),
(133, 42, 4, 8, '2026-06-15', '08:00:00', 'cancelled', '2026-06-13 09:53:03', NULL, NULL, 0),
(134, 16, 4, 8, '2026-06-16', '12:30:00', 'completed', '2026-06-15 09:52:25', NULL, 800, 0),
(135, 16, 4, 8, '2026-06-16', '15:00:00', 'cancelled', '2026-06-15 11:15:46', NULL, NULL, 0),
(136, 16, 4, 8, '2026-06-16', '14:30:00', 'completed', '2026-06-15 11:16:18', NULL, 800, 0),
(137, NULL, 4, 8, '2026-06-16', '15:00:00', 'cancelled', '2026-06-15 11:19:10', NULL, NULL, 0),
(138, 33, 4, 11, '2026-06-16', '11:00:00', 'completed', '2026-06-15 11:20:41', 13, 1600, 0),
(139, 16, 4, 8, '2026-06-16', '12:00:00', 'completed', '2026-06-15 11:23:34', 14, 640, 0),
(140, 42, 4, 8, '2026-06-17', '14:00:00', 'completed', '2026-06-15 12:17:52', NULL, NULL, 0),
(141, NULL, 4, 8, '2026-06-19', '09:00:00', 'cancelled', '2026-06-18 07:20:45', NULL, NULL, 0),
(142, 42, 4, 8, '2026-06-18', '14:00:00', 'cancelled', '2026-06-18 07:22:55', NULL, NULL, 0),
(143, 42, 4, 8, '2026-06-18', '15:00:00', 'cancelled', '2026-06-18 07:44:23', NULL, NULL, 0),
(144, 42, 3, 3, '2026-06-18', '13:00:00', 'completed', '2026-06-18 07:47:20', NULL, 900, 0),
(145, 42, 3, 3, '2026-06-18', '13:30:00', 'completed', '2026-06-18 07:47:34', NULL, 900, 0),
(146, 42, 3, 3, '2026-06-18', '14:00:00', 'completed', '2026-06-18 07:47:44', NULL, 900, 0),
(147, 42, 3, 3, '2026-06-18', '14:30:00', 'completed', '2026-06-18 07:47:54', NULL, 900, 0),
(148, 42, 3, 3, '2026-06-18', '15:00:00', 'completed', '2026-06-18 07:53:38', NULL, 900, 0),
(149, 16, 2, 1, '2026-06-18', '11:00:00', 'completed', '2026-06-18 08:04:04', NULL, 1000, 0),
(150, 16, 2, 1, '2026-06-18', '15:00:00', 'completed', '2026-06-18 08:05:40', NULL, 1000, 0),
(151, 16, 4, 8, '2026-06-18', '12:00:00', 'completed', '2026-06-18 09:08:02', NULL, 800, 0),
(152, 16, 4, 8, '2026-06-18', '13:00:00', 'completed', '2026-06-18 09:11:49', NULL, 800, 0),
(153, 42, 3, 3, '2026-06-18', '16:00:00', 'completed', '2026-06-18 09:13:59', NULL, 900, 0),
(154, 42, 3, 3, '2026-06-18', '16:30:00', 'completed', '2026-06-18 09:14:08', NULL, 900, 0),
(155, 42, 3, 3, '2026-06-18', '17:00:00', 'booked', '2026-06-18 09:14:19', NULL, NULL, 0),
(156, 42, 3, 3, '2026-06-18', '17:30:00', 'booked', '2026-06-18 09:14:37', NULL, NULL, 0),
(157, 38, 4, 8, '2026-06-18', '14:30:00', 'completed', '2026-06-18 09:17:51', NULL, 800, 0),
(158, 19, 4, 9, '2026-06-18', '13:30:00', 'completed', '2026-06-18 09:27:03', NULL, 1300, 0),
(160, 19, 3, 6, '2026-06-19', '10:00:00', 'completed', '2026-06-18 11:07:22', 22, 640, 0),
(161, 19, 3, 6, '2026-06-19', '12:30:00', 'booked', '2026-06-18 11:40:39', NULL, NULL, 0),
(163, NULL, 4, 8, '2026-06-19', '10:00:00', 'cancelled', '2026-06-18 12:13:31', NULL, NULL, 0),
(171, 19, 4, 11, '2026-06-20', '09:00:00', 'completed', '2026-06-18 13:56:09', NULL, 2000, 0),
(176, 19, 4, 11, '2026-06-20', '11:00:00', 'completed', '2026-06-19 08:24:04', NULL, 2000, 0),
(177, 19, 4, 11, '2026-06-20', '12:00:00', 'completed', '2026-06-19 08:30:11', NULL, NULL, 0),
(179, 42, 4, 8, '2026-06-22', '09:00:00', 'completed', '2026-06-21 12:18:04', NULL, 800, 0),
(180, 42, 4, 8, '2026-06-23', '10:00:00', 'completed', '2026-06-21 12:19:18', NULL, NULL, 0),
(181, 38, 4, 8, '2026-06-22', '15:00:00', 'completed', '2026-06-21 18:42:22', NULL, NULL, 0),
(182, 19, 4, 14, '2026-06-22', '11:00:00', 'completed', '2026-06-21 19:15:35', 23, 2000, 0),
(184, 38, 4, 8, '2026-06-23', '12:00:00', 'completed', '2026-06-22 13:57:13', NULL, NULL, 0),
(185, 38, 4, 9, '2026-06-23', '13:00:00', 'completed', '2026-06-23 09:50:26', NULL, NULL, 0),
(186, 38, 4, 8, '2026-06-27', '10:00:00', 'completed', '2026-06-23 09:50:59', NULL, NULL, 0),
(188, 42, 4, 8, '2026-06-26', '15:00:00', 'cancelled', '2026-06-26 09:33:10', NULL, NULL, 0),
(189, 42, 4, 8, '2026-06-26', '14:00:00', 'completed', '2026-06-26 09:35:49', NULL, NULL, 0),
(190, 42, 3, 4, '2026-06-26', '16:00:00', 'cancelled', '2026-06-26 09:36:39', NULL, NULL, 0),
(191, 42, 3, 4, '2026-06-26', '16:00:00', 'cancelled', '2026-06-26 09:47:06', NULL, NULL, 0),
(192, 42, 3, 7, '2026-06-27', '08:00:00', 'cancelled', '2026-06-26 09:47:44', NULL, NULL, 0),
(193, 38, 4, 8, '2026-06-27', '12:00:00', 'booked', '2026-06-26 10:07:15', NULL, NULL, 0),
(194, 38, 4, 8, '2026-06-27', '11:00:00', 'booked', '2026-06-26 10:29:34', NULL, NULL, 0),
(195, 42, 4, 8, '2026-06-26', '15:00:00', 'completed', '2026-06-26 10:45:02', NULL, NULL, 0),
(196, 42, 4, 8, '2026-06-29', '15:00:00', 'completed', '2026-06-28 19:43:10', NULL, NULL, 0),
(197, 16, 4, 8, '2026-06-29', '09:30:00', 'booked', '2026-06-28 19:44:25', NULL, NULL, 0),
(198, 38, 4, 8, '2026-06-29', '08:00:00', 'booked', '2026-06-28 19:47:42', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `discount_amount` int(11) NOT NULL,
  `discount_type` enum('percent','fixed') DEFAULT 'percent',
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `user_id`, `code`, `discount_amount`, `discount_type`, `is_used`, `created_at`, `used_at`) VALUES
(1, 28, '2AD22D51', 20, 'percent', 1, '2026-05-12 08:47:36', '2026-05-12 17:37:45'),
(2, 10, '66A15DBD', 20, 'percent', 1, '2026-05-12 16:06:28', '2026-05-12 18:10:25'),
(3, 10, 'E9777D93', 20, 'percent', 1, '2026-05-12 16:11:05', '2026-05-12 18:24:01'),
(4, 33, 'CE18368F', 20, 'percent', 1, '2026-05-12 16:18:45', '2026-05-13 18:50:14'),
(5, 28, 'A9EA8315', 20, 'percent', 1, '2026-05-16 10:04:52', '2026-05-16 12:11:17'),
(8, 28, 'E0441B98', 20, 'percent', 1, '2026-05-20 11:36:18', '2026-05-20 13:38:14'),
(9, 38, 'FDF445AS', 20, 'percent', 1, '2026-05-30 15:18:16', '2026-06-03 15:19:08'),
(10, 38, '34DDF30F', 20, 'percent', 1, '2026-05-30 15:27:25', '2026-06-05 14:26:39'),
(11, 38, '0168711A', 20, 'percent', 1, '2026-05-30 15:27:27', '2026-06-21 20:52:54'),
(12, 21, '981589A5', 20, 'percent', 0, '2026-06-04 12:14:56', NULL),
(13, 16, 'F3CF2AF5', 20, 'percent', 1, '2026-06-12 14:01:02', '2026-06-15 13:21:25'),
(14, 33, '87551A0A', 20, 'percent', 1, '2026-06-15 11:22:43', '2026-06-15 13:24:19'),
(19, 16, 'E41FA38F', 20, 'percent', 0, '2026-06-18 09:11:13', NULL),
(20, 42, 'AC7AF2DE', 20, 'percent', 1, '2026-06-18 09:14:54', '2026-06-21 14:11:14'),
(21, 42, '533D1A20', 20, 'percent', 1, '2026-06-21 12:18:24', '2026-06-21 15:31:03'),
(22, 19, '6CBB4CDA', 20, 'percent', 1, '2026-06-21 18:46:21', '2026-06-21 21:14:46'),
(23, 19, '40AF022D', 20, 'percent', 1, '2026-06-21 18:46:26', '2026-06-22 13:05:41'),
(24, 38, '07284CCC', 20, 'percent', 0, '2026-06-22 15:06:00', NULL),
(25, 42, '2565605F', 20, 'percent', 0, '2026-06-26 09:57:41', NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `salon_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `user_id`, `salon_id`, `created_at`) VALUES
(3, 32, 2, '2026-02-07 14:27:14'),
(6, 10, 4, '2026-02-08 13:14:29'),
(8, 38, 4, '2026-05-29 14:36:25'),
(9, 16, 4, '2026-06-03 13:02:04'),
(10, 42, 4, '2026-06-18 07:08:06');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `hair_recommendations`
--

CREATE TABLE `hair_recommendations` (
  `id` int(11) NOT NULL,
  `face_shape` varchar(50) DEFAULT NULL,
  `style_name` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `gender` enum('ferfi','no') DEFAULT 'ferfi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `hair_recommendations`
--

INSERT INTO `hair_recommendations` (`id`, `face_shape`, `style_name`, `image_url`, `description`, `gender`) VALUES
(1, 'szogletes', 'Klasszikus Old Money', 'old_money.jpg', 'A magasság segít megnyújtani az arcot és lágyítja az erős állkapcsot.', 'ferfi'),
(2, 'szogletes', 'Oldalt elválasztott (Side Part)', 'side_part.jpg', 'Klasszikus választás, ami ellensúlyozza a szögletes vonalakat.', 'ferfi'),
(3, 'szogletes', 'Oldalt elválasztott (Side Part)', 'side_part_2.jpg', 'Klasszikus választás, ami ellensúlyozza a szögletes vonalakat.', 'ferfi'),
(4, 'szogletes', 'Texturált Crop', 'textured_crop.jpg', 'Rövid, rendezett fazon, amely az erős csontozatot emeli ki anélkül, hogy túl nyers lenne.', 'ferfi'),
(5, 'szogletes', '30/70 Oldalra fésült', 'old_money2.jpg', 'Klasszikus, elegáns megjelenés a mindennapokra.', 'ferfi'),
(6, 'szogletes', 'Pompadour', 'pompadour.jpg', 'A klasszikus, dúsan hátrafésült stílus.', 'ferfi'),
(7, 'ovalis', 'Modern Quiff', 'quiff.jpg', 'Kiemeli az arc arányos szerkezetét és természetes egyensúlyát.', 'ferfi'),
(8, 'ovalis', 'Modern Quiff', 'quiff2.jpg', 'Kiemeli az arc arányos szerkezetét és természetes egyensúlyát.', 'ferfi'),
(9, 'ovalis', 'Hosszabb hullámos (Long Waves)', 'long_waves.jpg', 'Lágy esése tökéletesen követi az arc természetes vonalát.', 'ferfi'),
(10, 'ovalis', 'Hosszabb hullámos (Long Waves)', 'long_waves_2.jpg', 'Lágy esése tökéletesen követi az arc természetes vonalát.', 'ferfi'),
(11, 'ovalis', 'Buzz Cut', 'buzz_cut.jpg', 'Mivel az ovális arcforma a legarányosabb, ez a teljesen rövid fazon is kiválóan áll neki.', 'ferfi'),
(12, 'ovalis', 'Buzz Cut', 'buzz_cut_2.jpg', 'Mivel az ovális arcforma a legarányosabb, ez a teljesen rövid fazon is kiválóan áll neki.', 'ferfi'),
(13, 'kerek', 'Magas Fade + Undercut', 'undercut3.jpg', 'Az oldalt rövid, felül hosszú fazon optikailag nyújtja és vékonyítja az arcot.', 'ferfi'),
(14, 'kerek', 'Magas Fade + Undercut', 'undercut2.jpg', 'Az oldalt rövid, felül hosszú fazon optikailag nyújtja és vékonyítja az arcot.', 'ferfi'),
(15, 'kerek', 'Aszimmetrikus frufru', 'fringe.jpg', 'Megtöri az arc kerekded formáját és élesebb szögeket zár be.', 'ferfi'),
(16, 'kerek', 'Aszimmetrikus frufru', 'fringe2.jpg', 'Megtöri az arc kerekded formáját és élesebb szögeket zár be.', 'ferfi'),
(17, 'kerek', 'Tüskés frizura', 'spiky_hair.jpg', 'A szálkás, vertikális texturáltság éleket és dinamikát kölcsönöz a frizurának, kompenzálva az arc csontozatának lágyságát.', 'ferfi'),
(18, 'kerek', 'Tüskés frizura', 'spiky_hair2.jpg', 'A szálkás, vertikális texturáltság éleket és dinamikát kölcsönöz a frizurának, kompenzálva az arc csontozatának lágyságát.', 'ferfi'),
(19, 'hosszukas', 'Középhosszú réteges', 'mid_layered.jpg', 'Tömeget ad az oldalsó részeken, így vizuálisan szélesíti az arcot.', 'ferfi'),
(20, 'hosszukas', 'Középhosszú réteges', 'mid_layered2.jpg', 'Tömeget ad az oldalsó részeken, így vizuálisan szélesíti az arcot.', 'ferfi'),
(21, 'hosszukas', 'Classic Scissors Cut', 'classic_scissors.jpg', 'Hagyományos, ollóval vágott stílus, ami nem nyújtja tovább a fejtetőt.', 'ferfi'),
(22, 'hosszukas', 'Classic Scissors Cut', 'classic_scissors2.jpg', 'Hagyományos, ollóval vágott stílus, ami nem nyújtja tovább a fejtetőt.', 'ferfi'),
(23, 'hosszukas', 'Slicked Back (Hátrafésült)', 'slicked_back.jpg', 'Az oldalsó dúsabb textúra kiegyensúlyozza az arc hosszúságát.', 'ferfi'),
(24, 'hosszukas', 'Slicked Back (Hátrafésült)', 'slicked_back2.jpg', 'Az oldalsó dúsabb textúra kiegyensúlyozza az arc hosszúságát.', 'ferfi'),
(25, 'szogletes', 'Puha frufru (Soft Fringe)', 'w_soft_fringe.jpg', 'Lágyítja az állkapocs és a homlok szögletes vonásait.', 'no'),
(26, 'szogletes', 'Puha frufru (Soft Fringe)', 'w_soft_fringe2.jpg', 'Lágyítja az állkapocs és a homlok szögletes vonásait.', 'no'),
(27, 'szogletes', 'Hosszú réteges hullámok', 'w_long_waves.jpg', 'A rétegek finomítják az éles kontúrokat és keretet adnak az arcnak.', 'no'),
(28, 'szogletes', 'Hosszú réteges hullámok', 'w_long_waves2.jpg', 'A rétegek finomítják az éles kontúrokat és keretet adnak az arcnak.', 'no'),
(29, 'szogletes', 'A-Vonalú Bob', 'aline_bob.jpg', 'Hátul rövidebb, elöl hosszabb fazon, amely eltereli a hangsúlyt a markáns állról.', 'no'),
(30, 'szogletes', 'A-Vonalú Bob', 'aline_bob2.jpg', 'Hátul rövidebb, elöl hosszabb fazon, amely eltereli a hangsúlyt a markáns állról.', 'no'),
(31, 'ovalis', 'Oldalra fésült frufru', 'w_frufru.jpg', 'Gyönyörű, klasszikus női hajstílus, ami kiemeli az arányos arcot.', 'no'),
(32, 'ovalis', 'Oldalra fésült frufru', 'w_frufru2.jpg', 'Gyönyörű, klasszikus női hajstílus, ami kiemeli az arányos arcot.', 'no'),
(33, 'ovalis', 'Blunt Bob (Egyenes Bob)', 'blunt_bob.jpg', 'Modern, tűéles vágás, amely az ovális formával tiszta eleganciát sugároz.', 'no'),
(34, 'ovalis', 'Blunt Bob (Egyenes Bob)', 'blunt_bob2.jpg', 'Modern, tűéles vágás, amely az ovális formával tiszta eleganciát sugároz.', 'no'),
(35, 'ovalis', 'Hosszú, egyenes lépcsőzetes', 'w_long.jpg', 'A fokozatosan vágott tincsek dinamikát adnak a hajnak.', 'no'),
(36, 'ovalis', 'Hosszú, egyenes lépcsőzetes', 'w_long2.jpg', 'A fokozatosan vágott tincsek dinamikát adnak a hajnak.', 'no'),
(37, 'kerek', 'Hosszú, vállig érő réteges frizura', 'w_shoulder.jpg', 'Nyújtja a nyakat és az arcot, finomítja a kerek formát.', 'no'),
(38, 'kerek', 'Hosszú, vállig érő réteges frizura', 'w_shoulder2.jpg', 'Nyújtja a nyakat és az arcot, finomítja a kerek formát.', 'no'),
(39, 'kerek', 'Oldalra fésült hosszú frufru', 'w_long_bangs.jpg', 'Az aszimmetria optikailag megtöri az arc körvonalát.', 'no'),
(40, 'kerek', 'Oldalra fésült hosszú frufru', 'w_long_bangs2.jpg', 'Az aszimmetria optikailag megtöri az arc körvonalát.', 'no'),
(41, 'kerek', 'Magas, laza konty (Messy Bun)', 'konty.jpg', 'A fejtetőn elhelyezett volumen optikailag nyújtja a teljes arcszerkezetet.', 'no'),
(42, 'kerek', 'Magas, laza konty (Messy Bun)', 'konty2.jpg', 'A fejtetőn elhelyezett volumen optikailag nyújtja a teljes arcszerkezetet.', 'no'),
(43, 'hosszukas', 'Dús, texturált hullámok', 'w_texture_waves.jpg', 'Oldalirányú volument ad, ami szélesíti az arc összképét.', 'no'),
(44, 'hosszukas', 'Dús, texturált hullámok', 'w_texture_waves2.jpg', 'Oldalirányú volument ad, ami szélesíti az arc összképét.', 'no'),
(45, 'hosszukas', 'Teljes, egyenes frufru', 'w_egyenes_frufru.jpg', 'Eltakarja a homlok egy részét, így vizuálisan lerövidíti az arcot.', 'no'),
(46, 'hosszukas', 'Teljes, egyenes frufru', 'w_egyenes_frufru2.jpg', 'Eltakarja a homlok egy részét, így vizuálisan lerövidíti az arcot.', 'no'),
(47, 'hosszukas', 'Oldalra fésült réteges frizura', 'oldalra.jpg', 'Az arccsont környékén elhelyezett rétegek szélesítik az arcot.', 'no'),
(48, 'hosszukas', 'Oldalra fésült réteges frizura', 'oldalra2.jpg', 'Az arccsont környékén elhelyezett rétegek szélesítik az arcot.', 'no');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `salon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `reviews`
--

INSERT INTO `reviews` (`review_id`, `salon_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(2, 3, 16, 5, 'Nagyon profi munka!', '2026-02-02 17:18:26'),
(3, 3, 19, 4, 'Profi!', '2026-02-02 17:21:13'),
(4, 4, 10, 5, 'Legjobb szalon.', '2026-02-02 17:31:43'),
(5, 2, 10, 1, 'Amator munka.', '2026-02-02 17:35:36'),
(6, 4, 1, 5, 'Legjobb szalon!!!', '2026-05-06 12:41:34'),
(7, 3, 28, 4, 'A fodrasz nagyon kedves volt csak ajanlani tudom.', '2026-05-16 09:53:51'),
(8, 2, 16, 2, '', '2026-06-03 12:55:18'),
(9, 3, 41, 4, 'Megvagyok elegedve.', '2026-06-07 16:07:08');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `salons`
--

CREATE TABLE `salons` (
  `salon_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `image_url` varchar(255) DEFAULT 'default_salon.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `salons`
--

INSERT INTO `salons` (`salon_id`, `owner_id`, `name`, `address`, `city`, `phone`, `active`, `image_url`) VALUES
(2, 15, 'Vogue Hair Studio', 'Nagy utca 24', 'Szabadka', '016 454 758', 1, 'salon1.jpg'),
(3, 18, 'Flow', 'Petőfi Sándor utca 35', 'Szabadka', '011 258 584', 1, 'salon2.jpg'),
(4, 20, 'Aura Hair', 'Gyuri utca 6', 'Szabadka', '011 548 6487', 1, 'salon3.jpg'),
(5, 25, 'MinimalHair', 'Kis utca 25', 'Szabadka', '848484', 0, 'default_salon.jpg');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `salon_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `points` int(10) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `services`
--

INSERT INTO `services` (`service_id`, `salon_id`, `name`, `price`, `duration`, `points`) VALUES
(1, 2, 'Férfi hajvágás', 1000, 30, 0),
(2, 2, 'Borotvákozás', 500, 15, 0),
(3, 3, 'Férfi hajvágás', 900, 30, 80),
(4, 3, 'Női hajvágás', 1200, 45, 0),
(5, 3, 'Női hajvágás+hajfestés', 2000, 60, 0),
(6, 3, 'Hajfestés', 800, 30, 0),
(7, 3, 'Hajmosás', 500, 15, 0),
(8, 4, 'Férfi hajvágás', 800, 30, 20),
(9, 4, 'Női hajvágás', 1300, 45, 20),
(11, 4, 'Hajápolás', 2000, 30, 100),
(14, 4, 'Női hajvágás+hajfestés', 2500, 60, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','owner','admin') DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT NULL,
  `mobilenumber` varchar(35) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `activation_token` varchar(64) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `total_points` int(10) UNSIGNED DEFAULT 0,
  `is_blocked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`user_id`, `email`, `username`, `password`, `role`, `profile_pic`, `mobilenumber`, `created_at`, `activation_token`, `is_active`, `reset_token`, `reset_expires_at`, `total_points`, `is_blocked`) VALUES
(1, 'dzokica@gmail.com', 'Dzokica', '$2y$12$hN.2HGsp5IKK7s1itJWkLuFrbgWV4CpIHBhkjNVlBttkfa1PN6I1C', 'user', NULL, NULL, '2025-12-01 17:22:25', '', 1, NULL, NULL, 0, 0),
(3, 'blans@gmail.com', 'blans', '$2y$12$CWvKvT9nIRCCe9.DH7POKulGBmuwUT6iJELZOuszPi2iXkocqpXsS', 'user', NULL, NULL, '2025-12-01 17:33:15', '41cf10d44492c2141d8cea01fb9993e6', 0, NULL, NULL, 0, 0),
(10, 'istvan@gmail.com', 'Istvan', '$2y$12$nwCknSgw/k5hl9MGh4Mj8eUOd2sjsR1ZLRfPW0GWiSI3Wn8RpVB76', 'user', NULL, NULL, '2025-12-03 11:45:31', '', 1, NULL, NULL, 0, 0),
(15, 'bela@gmail.com', 'Bela', '$2y$12$cQ1AQT7tIQhDEY03qd7tc.1l3OBXkkajVbuWOesVsXfF0Sdo5Kc.q', 'owner', NULL, '2841963', '2026-01-08 15:37:55', '', 1, NULL, NULL, 0, 0),
(16, 'marci@gmail.com', 'Marci', '$2y$12$omDfI9e9QwOxZ.ESCKr4SOlrQLIUGGtv9NE/SQXNQCHk025P5h0TW', 'user', 'avatar_16_1780418675.png', '854-555', '2026-01-08 15:44:39', '', 1, NULL, NULL, 20, 0),
(17, 'benjamin@gmail.com', 'Benjamin', '$2y$12$XL98S8CC.E5l43QSgkt9WenRiNOPBNvAEgQsyQRsmF2VQnVlG5rry', 'admin', NULL, '1887474', '2026-01-08 15:48:36', '', 1, NULL, NULL, 0, 0),
(18, 'maria@gmail.com', 'Maria', '$2y$12$/fHj65vUqxScwaP5knppKunqZ9iS5QQ.WCMOqDBHusouKQnmmJs9q', 'owner', NULL, '6493735', '2026-01-14 16:37:58', '', 1, NULL, NULL, 0, 0),
(19, 'beata@gmail.com', 'Beata', '$2y$12$9rbCOu77cH3n1TnRxgh9UemzkzHSxcHFmVQwiHWPrVtT.RP.V6Ui6', 'user', NULL, '91933332', '2026-01-15 09:25:51', '', 1, NULL, NULL, 60, 0),
(20, 'blanka@gmail.com', 'Blanka', '$2y$12$4jZ387Y7z4vjWkqdAH7sQ.JiJmjS3Smpzwyag99C1xH1GJbp7ctli', 'owner', 'avatar_20_1781511685.png', '9835923', '2026-01-16 13:01:10', '', 1, NULL, NULL, 0, 0),
(21, 'tamas@gmail.com', 'Tamas', '$2y$12$kKcBq4p8PlVGzYhgLREaAeKzEgufizw/QxvpLUkeGnlt433lehJs2', 'user', 'avatar_21_1780495127.png', '262626', '2026-01-18 18:37:40', '', 1, NULL, NULL, 80, 0),
(22, 'pityu@gmail.com', 'pityu', '$2y$10$2AditGiYX4EKZ6UqLMwpGe1DF3eEeLWV/a6CdPKfadRojB.STXili', 'user', NULL, NULL, '2026-01-18 18:53:38', '', 1, NULL, NULL, 0, 0),
(25, 'joker@gmail.com', 'Andras', '$2y$12$LzzQaP2TP0mBQ9jrghIgE.UplsXQqhbrj3i0y/3LEfC9Uj4E7IvXe', 'owner', NULL, NULL, '2026-02-02 16:11:25', '', 1, NULL, NULL, 0, 0),
(28, 'john@gmail.com', 'John', '$2y$10$OiOJfcxKwJlXONdys3JLaey979b7JnYpv8ZhaA3IO4FtGsgP9iJHC', 'user', 'avatar_28_1781342345.jpg', '1414825', '2026-02-03 12:39:37', '', 1, NULL, NULL, 20, 0),
(29, 'blanka@office.com', 'Blanka office', '$2y$10$mzU3W9S/JV38Hz0NB9mPteJslFTIs13Aerpy2sE0mF2TlErlPmWqO', 'user', NULL, NULL, '2026-02-03 14:26:12', '7d12e9abdfd47b575552b59904144515', 0, NULL, NULL, 0, 0),
(31, 'szonyiblanka2005@gmail.com', 'Blans office', '$2y$10$nsY9znNcuNJo3rNg2cugYef8f3bSZMezQUolM0pgQOIb525iaO12.', 'user', NULL, NULL, '2026-02-03 14:28:13', '695e281ff7a535d97835c33f6d9fd31f', 0, NULL, NULL, 0, 0),
(32, 'berci@gmail.com', 'Berci', '$2y$10$78qKUaJYkHlmAOko3cBcFuIpqMTmqchmOG2F6wV6PhP3Oax0s7TQ2', 'user', 'avatar_32_1780495425.png', '848414884', '2026-02-04 08:42:53', '', 1, NULL, NULL, 0, 0),
(33, 'bertold@gmail.com', 'Bertold', '$2y$10$7E/akLChMwA4l2.IsM57oO76HkZ0Z8QBxK2LQf3M0a274Me.XcxMO', 'user', NULL, NULL, '2026-02-07 10:12:37', '', 1, NULL, NULL, 0, 0),
(34, 'lecso@gmail.com', 'Lecso', '$2y$12$2kBO0b4e8w8N65SEgHt5EuFVAqtnJD7Y5csMiuQnIwm.Xl7HAgMbu', 'user', NULL, NULL, '2026-05-27 12:00:10', '', 1, NULL, NULL, 0, 0),
(37, 'miska@gmail.com', 'Miska Janos', '$2y$12$lDCf6v9fDfyPMws/PD1PJeV9QO7fyhcKhF9BdDHYY8c2C./t.MgQ2', 'user', NULL, NULL, '2026-05-28 12:31:11', '', 1, NULL, NULL, 0, 0),
(38, 'felix@gmail.com', 'Felix', '$2y$12$LxfWArUjFVxaX80tXXC.4uFGhbluzMAgg6J1xYYxUE0bynvNPYuWm', 'user', 'avatar_38_1780413243.png', '5805544777', '2026-05-28 13:06:49', '', 1, NULL, NULL, 40, 0),
(41, 'james@gmail.com', 'James', '$2y$12$aI2japik.tK3xDEfYbtHXeihUsMS/P1xyfv17yNX1vlF.eKJ/uYlO', 'user', NULL, NULL, '2026-06-07 15:52:34', '', 1, '41a546870d65a02a4cd830f807bebf9b113bc8b813aa407c7ba712f79a5afa6c', '2026-06-12 20:54:42', 20, 0),
(42, 'george@gmail.com', 'George', '$2y$12$aHfrlmmMU0nlAsP5gfgF0.Lw9grXfwqLEYodACMVwoFCGwAjxtpYy', 'user', 'avatar_42_1781344411.jpg', '012 528 777', '2026-06-12 17:20:53', '', 1, NULL, NULL, 40, 0),
(43, 'elizabet@gmail.com', 'Elizabet', '$2y$12$XVTFTanlxIjnPo5RtoGOrOKYY1vqF3QTxR.P1Gil/kTiVlrl3gIlS', 'owner', NULL, NULL, '2026-06-15 14:36:07', '7ac99a615e63eb1d30fe1de08bc198b0', 1, NULL, NULL, 0, 0),
(46, 'mate@gmail.com', 'Mate', '$2y$12$4gIirZ.41GnaN4Fh2qDlce.UagmDIw/uQ6A2Lg0XBLrRD43j6pKi.', 'user', NULL, '353535', '2026-06-17 18:56:14', '', 1, NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_favored_styles`
--

CREATE TABLE `user_favored_styles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `recommendation_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `user_favored_styles`
--

INSERT INTO `user_favored_styles` (`id`, `user_id`, `appointment_id`, `recommendation_id`, `saved_at`) VALUES
(5, 42, NULL, 7, '2026-06-15 12:17:36'),
(6, 38, NULL, 1, '2026-06-22 13:58:38'),
(8, 38, NULL, 1, '2026-06-26 10:29:03'),
(9, 38, NULL, 1, '2026-06-26 10:30:03'),
(10, 42, 195, 9, '2026-06-26 10:47:17'),
(11, 38, 198, 1, '2026-06-28 19:48:18');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `visitor_logs`
--

CREATE TABLE `visitor_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device_type` enum('mobile','tablet','pc') NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `login_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `visitor_logs`
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
(42, 37, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-28 12:31:11'),
(43, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-28 13:06:49'),
(44, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-28 13:11:04'),
(45, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-28 14:06:13'),
(46, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:25:25'),
(47, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:25:30'),
(48, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:25:44'),
(49, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:25:47'),
(50, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:25:57'),
(51, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:26:00'),
(52, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:44:48'),
(53, 34, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:44:51'),
(54, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:46:01'),
(55, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 11:56:56'),
(56, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 12:04:41'),
(57, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 12:06:27'),
(58, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 12:51:11'),
(59, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 12:51:50'),
(60, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 13:00:14'),
(61, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 13:35:59'),
(62, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-05-29 14:33:47'),
(63, 38, '192.168.1.9', 'pc', 'Ismeretlen', '2026-05-30 08:17:51'),
(64, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 08:49:12'),
(65, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 09:25:27'),
(66, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 09:51:34'),
(67, 38, '192.168.1.9', 'pc', 'Ismeretlen', '2026-05-30 10:25:56'),
(68, 38, '192.168.1.9', 'pc', 'Ismeretlen', '2026-05-30 11:32:48'),
(69, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 12:01:40'),
(70, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 12:01:49'),
(71, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 12:04:01'),
(72, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 12:05:02'),
(73, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 12:15:17'),
(74, 38, '192.168.1.6', 'pc', 'Ismeretlen', '2026-05-30 12:19:06'),
(75, 38, '192.168.0.102', 'pc', 'Ismeretlen', '2026-05-30 13:23:44'),
(76, 38, '192.168.0.102', 'pc', 'Ismeretlen', '2026-05-30 14:58:50'),
(77, 38, '192.168.0.102', 'pc', 'Ismeretlen', '2026-05-30 15:19:03'),
(78, 38, '192.168.0.102', 'pc', 'Ismeretlen', '2026-05-30 15:28:40'),
(79, 38, '192.168.0.102', 'pc', 'Ismeretlen', '2026-05-30 15:49:51'),
(80, 38, '192.168.0.101', 'pc', 'Ismeretlen', '2026-05-31 14:19:54'),
(81, 38, '192.168.0.101', 'pc', 'Ismeretlen', '2026-05-31 14:56:28'),
(82, 38, '192.168.0.101', 'pc', 'Ismeretlen', '2026-05-31 15:22:06'),
(83, 38, '192.168.0.101', 'pc', 'Ismeretlen', '2026-05-31 15:39:22'),
(84, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-03 13:01:37'),
(85, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-03 13:10:17'),
(86, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-03 13:19:46'),
(87, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-03 13:20:44'),
(88, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-03 13:25:39'),
(89, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-03 13:27:51'),
(90, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-03 13:34:01'),
(91, 38, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-03 15:17:07'),
(92, 21, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-03 15:25:53'),
(93, 21, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-03 15:55:10'),
(94, 20, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-03 15:55:30'),
(95, 20, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-03 16:14:33'),
(96, 21, '192.168.0.101', 'pc', 'Ismeretlen', '2026-06-04 12:10:57'),
(97, 21, '192.168.0.101', 'pc', 'Ismeretlen', '2026-06-04 12:12:41'),
(98, 21, '192.168.0.101', 'pc', 'Ismeretlen', '2026-06-04 12:14:36'),
(99, 20, '192.168.0.101', 'pc', 'Ismeretlen', '2026-06-04 12:21:12'),
(100, 16, '192.168.0.101', 'pc', 'Ismeretlen', '2026-06-04 13:06:39'),
(101, 20, '192.168.0.101', 'pc', 'Ismeretlen', '2026-06-04 13:35:40'),
(102, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-05 12:35:30'),
(103, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-05 13:31:30'),
(104, 28, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-05 13:50:29'),
(105, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-05 13:51:51'),
(106, 20, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-05 14:21:28'),
(107, 20, '192.168.0.102', 'pc', 'Ismeretlen', '2026-06-06 14:45:00'),
(108, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-07 19:20:44'),
(109, 20, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-07 19:23:44'),
(110, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-07 19:41:12'),
(111, 20, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-07 19:41:44'),
(112, 16, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-07 19:45:43'),
(113, 20, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-07 19:48:38'),
(114, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-08 18:47:49'),
(115, 18, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-08 18:49:03'),
(116, 18, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-08 18:50:25'),
(117, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-17 18:56:14'),
(118, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-17 19:00:35'),
(119, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-17 19:03:59'),
(120, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-17 19:07:54'),
(121, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-17 19:14:56'),
(122, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-17 19:17:36'),
(123, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:46'),
(124, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:46'),
(125, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:46'),
(126, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:47'),
(127, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:47'),
(128, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:48'),
(129, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:48'),
(130, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:48'),
(131, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:48'),
(132, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:48'),
(133, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(134, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(135, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(136, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(137, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(138, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(139, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(140, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:49'),
(141, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:50'),
(142, 46, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:43:50'),
(143, 42, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 06:44:50'),
(144, 42, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 07:02:55'),
(145, 42, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 07:24:00'),
(146, 42, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 07:25:19'),
(147, 38, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 09:16:28'),
(148, 19, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 09:23:14'),
(149, 19, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-18 12:42:09'),
(150, 42, '192.168.0.102', 'pc', 'Ismeretlen', '2026-06-21 11:29:03'),
(151, 20, '192.168.0.102', 'pc', 'Ismeretlen', '2026-06-21 11:30:40'),
(152, 42, '192.168.0.102', 'pc', 'Ismeretlen', '2026-06-21 11:59:56'),
(153, 20, '192.168.0.102', 'pc', 'Ismeretlen', '2026-06-21 12:11:10'),
(154, 42, '192.168.0.102', 'pc', 'Ismeretlen', '2026-06-21 12:14:25'),
(155, 20, '192.168.0.102', 'pc', 'Ismeretlen', '2026-06-21 12:19:32'),
(156, 20, '192.168.0.100', 'pc', 'Ismeretlen', '2026-06-21 18:44:44'),
(157, 20, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-22 11:04:56'),
(158, 42, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-22 11:10:48'),
(159, 19, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-22 11:19:27'),
(160, 42, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-22 11:57:46'),
(161, 20, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-22 13:09:43'),
(162, 20, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-22 13:59:35'),
(163, 42, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-26 09:09:13'),
(164, 20, '192.168.1.9', 'pc', 'Ismeretlen', '2026-06-26 09:49:26'),
(165, 42, '192.168.0.103', 'pc', 'Ismeretlen', '2026-06-28 19:30:07'),
(166, 16, '192.168.0.103', 'pc', 'Ismeretlen', '2026-06-28 19:38:06'),
(167, 42, '192.168.0.103', 'pc', 'Ismeretlen', '2026-06-28 19:42:52'),
(168, 16, '192.168.0.103', 'pc', 'Ismeretlen', '2026-06-28 19:43:56'),
(169, 20, '192.168.0.103', 'pc', 'Ismeretlen', '2026-06-28 19:44:47'),
(170, 42, '192.168.1.12', 'pc', 'Ismeretlen', '2026-06-29 08:52:40');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `working_hours`
--

CREATE TABLE `working_hours` (
  `working_hour_id` int(11) NOT NULL,
  `salon_id` int(11) NOT NULL,
  `day_of_week` int(11) NOT NULL COMMENT '1: Hétfő, 7: Vasárnap',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_closed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `working_hours`
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
(21, 4, 7, '00:00:00', '00:00:00', 1);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `salon_id` (`salon_id`),
  ADD KEY `service_id` (`service_id`);

--
-- A tábla indexei `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`salon_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- A tábla indexei `hair_recommendations`
--
ALTER TABLE `hair_recommendations`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_user_salon` (`user_id`,`salon_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- A tábla indexei `salons`
--
ALTER TABLE `salons`
  ADD PRIMARY KEY (`salon_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- A tábla indexei `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- A tábla indexei `user_favored_styles`
--
ALTER TABLE `user_favored_styles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_appointment_style` (`appointment_id`,`recommendation_id`),
  ADD KEY `recommendation_id` (`recommendation_id`),
  ADD KEY `user_id_fk_idx` (`user_id`);

--
-- A tábla indexei `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `working_hours`
--
ALTER TABLE `working_hours`
  ADD PRIMARY KEY (`working_hour_id`),
  ADD KEY `salon_id` (`salon_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT a táblához `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT a táblához `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT a táblához `hair_recommendations`
--
ALTER TABLE `hair_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT a táblához `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT a táblához `salons`
--
ALTER TABLE `salons`
  MODIFY `salon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT a táblához `user_favored_styles`
--
ALTER TABLE `user_favored_styles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT a táblához `visitor_logs`
--
ALTER TABLE `visitor_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT a táblához `working_hours`
--
ALTER TABLE `working_hours`
  MODIFY `working_hour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Megkötések a táblához `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`salon_id`) REFERENCES `salons` (`salon_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `user_favored_styles`
--
ALTER TABLE `user_favored_styles`
  ADD CONSTRAINT `user_favored_styles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favored_styles_ibfk_2` FOREIGN KEY (`recommendation_id`) REFERENCES `hair_recommendations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favored_styles_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD CONSTRAINT `visitor_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
