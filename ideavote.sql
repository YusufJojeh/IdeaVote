-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 24 أغسطس 2025 الساعة 11:30
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ideavote`
--

-- --------------------------------------------------------

--
-- بنية الجدول `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `admin_id`, `action`, `table_name`, `record_id`, `old_data`, `new_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'user_created', 'users', 2, '{}', '{\"username\": \"ahmed\"}', '192.168.1.1', NULL, '2025-08-11 21:42:10'),
(2, 1, 'user_created', 'users', 2, '{}', '{\"username\": \"ahmed\"}', '192.168.1.1', NULL, '2025-08-12 00:35:38'),
(3, 1, 'user_created', 'users', 2, '{}', '{\"username\": \"ahmed\"}', '192.168.1.1', NULL, '2025-08-12 00:39:56'),
(4, 1, 'logout', 'users', 1, '{\"username\":\"admin\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:02:45'),
(7, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:47:50'),
(8, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:47:57'),
(9, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:47:58'),
(10, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:48:03'),
(11, 1, 'login_blocked', 'users', 0, '{\"username\":\"admin@ideavote.com\",\"reason\":\"rate_limit\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:48:07'),
(12, 1, 'login_blocked', 'users', 0, '{\"username\":\"admin@ideavote.com\",\"reason\":\"rate_limit\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:48:10'),
(13, 1, 'login_blocked', 'users', 0, '{\"username\":\"admin@ideavote.com\",\"reason\":\"rate_limit\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 15:53:12'),
(14, 1, 'login_failed', 'users', 0, '{\"username\":\"ahmed@example.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:04:32'),
(15, 1, 'login_failed', 'users', 0, '{\"username\":\"ahmed@example.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:04:42'),
(16, 1, 'login_failed', 'users', 0, '{\"username\":\"ahmed@example.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:10:43'),
(17, 1, 'login_failed', 'users', 0, '{\"username\":\"ahmed@example.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:11:03'),
(18, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:11:42'),
(19, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:11:48'),
(20, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:13:02'),
(21, 1, 'login_failed', 'users', 0, '{\"username\":\"admin@ideavote.com\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:13:10'),
(22, 1, 'logout', 'users', 1, '{\"username\":\"admin\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 20:26:50'),
(23, 2, 'logout', 'users', 2, '{\"username\":\"ahmed\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 22:29:07'),
(24, 1, 'logout', 'users', 1, '{\"username\":\"admin\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0', '2025-08-12 23:25:39'),
(25, 1, 'logout', 'users', 1, '{\"username\":\"admin\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-17 19:19:47'),
(26, 2, 'logout', 'users', 2, '{\"username\":\"ahmed\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-17 19:53:38'),
(27, 1, 'logout', 'users', 1, '{\"username\":\"admin\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 09:27:50');

-- --------------------------------------------------------

--
-- بنية الجدول `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `idea_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `bookmarks`
--

INSERT INTO `bookmarks` (`id`, `user_id`, `idea_id`, `created_at`) VALUES
(1, 2, 3, '2025-08-11 21:42:10'),
(2, 4, 2, '2025-08-11 21:42:10'),
(3, 3, 2, '2025-08-11 21:42:10'),
(5, 4, 3, '2025-08-11 21:42:10'),
(9, 2, 2, '2025-08-11 21:42:10'),
(10, 3, 3, '2025-08-11 21:42:10'),
(18, 4, 1, '2025-08-12 00:35:38'),
(31, 3, 1, '2025-08-12 00:39:56'),
(40, 1, 2, '2025-08-17 18:28:41'),
(41, 1, 1, '2025-08-17 18:33:43');

-- --------------------------------------------------------

--
-- بنية الجدول `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `categories`
--

INSERT INTO `categories` (`id`, `name_en`, `name_ar`, `description`, `created_at`) VALUES
(1, 'Technology', 'تكنولوجيا', NULL, '2025-08-11 21:39:11'),
(2, 'Education', 'تعليم', NULL, '2025-08-11 21:39:11'),
(3, 'Environment', 'البيئة', NULL, '2025-08-11 21:39:11'),
(4, 'Health', 'الصحة', NULL, '2025-08-11 21:39:11'),
(5, 'Transportation', 'النقل', NULL, '2025-08-11 21:39:11');

-- --------------------------------------------------------

--
-- بنية الجدول `category_follows`
--

CREATE TABLE `category_follows` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `idea_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `idea_id`, `comment`, `created_at`) VALUES
(1, 3, 3, 'Well thought out!', '2025-08-11 21:39:12'),
(2, 2, 2, 'Well thought out!', '2025-08-11 21:39:12'),
(3, 3, 3, 'I love this idea!', '2025-08-11 21:39:12'),
(4, 3, 1, 'Well thought out!', '2025-08-11 21:39:12'),
(5, 2, 3, 'This is exactly what we need!', '2025-08-11 21:39:12'),
(6, 3, 1, 'Well thought out!', '2025-08-11 21:39:12'),
(7, 2, 3, 'Interesting concept.', '2025-08-11 21:39:12'),
(8, 3, 2, 'I love this idea!', '2025-08-11 21:39:12'),
(9, 4, 2, 'Great idea!', '2025-08-11 21:39:12'),
(10, 2, 1, 'Interesting concept.', '2025-08-11 21:39:12'),
(11, 2, 1, 'Great idea!', '2025-08-11 21:39:12'),
(12, 2, 2, 'This is exactly what we need!', '2025-08-11 21:39:12'),
(13, 3, 2, 'I love this idea!', '2025-08-11 21:39:12'),
(14, 4, 3, 'Well thought out!', '2025-08-11 21:39:12'),
(15, 4, 2, 'I love this idea!', '2025-08-11 21:39:12'),
(16, 4, 1, 'Great idea!', '2025-08-11 21:41:08'),
(17, 2, 1, 'Great idea!', '2025-08-11 21:41:08'),
(18, 4, 2, 'Interesting concept.', '2025-08-11 21:41:08'),
(19, 3, 2, 'This is exactly what we need!', '2025-08-11 21:41:08'),
(20, 3, 3, 'Well thought out!', '2025-08-11 21:41:08'),
(21, 2, 1, 'Well thought out!', '2025-08-11 21:41:08'),
(22, 4, 2, 'Interesting concept.', '2025-08-11 21:41:08'),
(23, 4, 2, 'I love this idea!', '2025-08-11 21:41:08'),
(24, 3, 2, 'Interesting concept.', '2025-08-11 21:41:08'),
(25, 3, 1, 'Well thought out!', '2025-08-11 21:41:08'),
(26, 4, 1, 'Interesting concept.', '2025-08-11 21:41:08'),
(27, 3, 2, 'Well thought out!', '2025-08-11 21:41:08'),
(28, 2, 3, 'This is exactly what we need!', '2025-08-11 21:41:08'),
(29, 3, 3, 'This is exactly what we need!', '2025-08-11 21:41:08'),
(30, 3, 3, 'This is exactly what we need!', '2025-08-11 21:41:08'),
(31, 4, 3, 'This is exactly what we need!', '2025-08-11 21:41:25'),
(32, 4, 2, 'Great idea!', '2025-08-11 21:41:25'),
(33, 2, 1, 'Interesting concept.', '2025-08-11 21:41:25'),
(34, 3, 3, 'Well thought out!', '2025-08-11 21:41:25'),
(35, 3, 2, 'Well thought out!', '2025-08-11 21:41:25'),
(36, 3, 3, 'Well thought out!', '2025-08-11 21:41:25'),
(37, 3, 1, 'Well thought out!', '2025-08-11 21:41:25'),
(38, 4, 2, 'Interesting concept.', '2025-08-11 21:41:25'),
(39, 2, 3, 'Well thought out!', '2025-08-11 21:41:25'),
(40, 3, 2, 'Well thought out!', '2025-08-11 21:41:25'),
(41, 4, 2, 'Well thought out!', '2025-08-11 21:41:25'),
(42, 3, 1, 'Great idea!', '2025-08-11 21:41:25'),
(43, 4, 2, 'Well thought out!', '2025-08-11 21:41:25'),
(44, 2, 3, 'Well thought out!', '2025-08-11 21:41:25'),
(45, 3, 3, 'Interesting concept.', '2025-08-11 21:41:25'),
(46, 4, 1, 'I love this idea!', '2025-08-11 21:42:10'),
(47, 2, 1, 'This is exactly what we need!', '2025-08-11 21:42:10'),
(48, 4, 1, 'Great idea!', '2025-08-11 21:42:10'),
(49, 2, 1, 'Well thought out!', '2025-08-11 21:42:10'),
(50, 4, 3, 'Well thought out!', '2025-08-11 21:42:10'),
(51, 2, 2, 'This is exactly what we need!', '2025-08-11 21:42:10'),
(52, 3, 1, 'Well thought out!', '2025-08-11 21:42:10'),
(53, 4, 1, 'Well thought out!', '2025-08-11 21:42:10'),
(54, 3, 2, 'This is exactly what we need!', '2025-08-11 21:42:10'),
(55, 3, 1, 'Well thought out!', '2025-08-11 21:42:10'),
(56, 3, 2, 'I love this idea!', '2025-08-11 21:42:10'),
(57, 2, 2, 'I love this idea!', '2025-08-11 21:42:10'),
(58, 2, 2, 'This is exactly what we need!', '2025-08-11 21:42:10'),
(59, 3, 3, 'Interesting concept.', '2025-08-11 21:42:10'),
(60, 2, 2, 'This is exactly what we need!', '2025-08-11 21:42:10'),
(61, 4, 2, 'This is exactly what we need!', '2025-08-12 00:35:38'),
(62, 4, 3, 'Interesting concept.', '2025-08-12 00:35:38'),
(63, 3, 3, 'Interesting concept.', '2025-08-12 00:35:38'),
(64, 2, 2, 'This is exactly what we need!', '2025-08-12 00:35:38'),
(65, 2, 2, 'Great idea!', '2025-08-12 00:35:38'),
(66, 3, 3, 'Great idea!', '2025-08-12 00:35:38'),
(67, 2, 3, 'I love this idea!', '2025-08-12 00:35:38'),
(68, 3, 1, 'This is exactly what we need!', '2025-08-12 00:35:38'),
(69, 4, 1, 'I love this idea!', '2025-08-12 00:35:38'),
(70, 3, 2, 'I love this idea!', '2025-08-12 00:35:38'),
(71, 4, 1, 'Well thought out!', '2025-08-12 00:35:38'),
(72, 3, 3, 'Great idea!', '2025-08-12 00:35:38'),
(73, 3, 3, 'I love this idea!', '2025-08-12 00:35:38'),
(74, 4, 2, 'I love this idea!', '2025-08-12 00:35:38'),
(75, 3, 2, 'Great idea!', '2025-08-12 00:35:38'),
(76, 3, 1, 'Great idea!', '2025-08-12 00:39:56'),
(77, 4, 3, 'Great idea!', '2025-08-12 00:39:56'),
(78, 4, 3, 'Well thought out!', '2025-08-12 00:39:56'),
(79, 2, 3, 'Interesting concept.', '2025-08-12 00:39:56'),
(80, 2, 2, 'This is exactly what we need!', '2025-08-12 00:39:56'),
(81, 2, 3, 'This is exactly what we need!', '2025-08-12 00:39:56'),
(82, 3, 2, 'Great idea!', '2025-08-12 00:39:56'),
(83, 4, 2, 'Well thought out!', '2025-08-12 00:39:56'),
(84, 4, 3, 'This is exactly what we need!', '2025-08-12 00:39:56'),
(85, 2, 1, 'This is exactly what we need!', '2025-08-12 00:39:56'),
(86, 2, 3, 'Well thought out!', '2025-08-12 00:39:56'),
(87, 2, 1, 'I love this idea!', '2025-08-12 00:39:56'),
(88, 3, 3, 'Great idea!', '2025-08-12 00:39:56'),
(89, 2, 3, 'Interesting concept.', '2025-08-12 00:39:56'),
(90, 2, 1, 'I love this idea!', '2025-08-12 00:39:56');

-- --------------------------------------------------------

--
-- بنية الجدول `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(1, 3, 2, '2025-08-11 21:41:25'),
(4, 3, 4, '2025-08-11 21:41:25'),
(5, 2, 3, '2025-08-11 21:41:25'),
(6, 4, 2, '2025-08-11 21:41:25'),
(10, 4, 3, '2025-08-11 21:42:10'),
(11, 2, 4, '2025-08-11 21:42:10');

-- --------------------------------------------------------

--
-- بنية الجدول `ideas`
--

CREATE TABLE `ideas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `is_approved` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `votes_count` int(11) DEFAULT 0,
  `bookmarks_count` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `trending_score` decimal(10,4) DEFAULT 0.0000,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `ideas`
--

INSERT INTO `ideas` (`id`, `user_id`, `category_id`, `title`, `slug`, `description`, `is_public`, `is_approved`, `is_featured`, `image_url`, `votes_count`, `bookmarks_count`, `views_count`, `trending_score`, `tags`, `created_at`) VALUES
(1, 2, 5, 'Smart City Traffic Management System', 'smart-city-traffic-management-system', 'AI-powered traffic management system using real-time data to optimize traffic flow and reduce congestion.', 1, 1, 0, NULL, 3, 3, 70, 162.7000, '[\"smart-city\",\"ai\",\"transportation\"]', '2025-08-11 21:39:12'),
(2, 2, 2, 'Virtual Reality Education Platform', 'virtual-reality-education-platform', 'Comprehensive VR platform for immersive learning experiences across all subjects.', 1, 1, 1, NULL, 1, 4, 12, 174.7000, '[\"education\",\"vr\",\"technology\"]', '2025-08-11 21:39:12'),
(3, 4, 3, 'Renewable Energy Microgrid Network', 'renewable-energy-microgrid-network', 'Decentralized renewable energy system connecting solar, wind, and battery storage.', 1, 1, 0, NULL, 2, 3, 24, 176.0000, '[\"renewable-energy\",\"sustainability\"]', '2025-08-11 21:39:12');

-- --------------------------------------------------------

--
-- بنية الجدول `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `username` varchar(190) DEFAULT NULL,
  `occurred_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip`, `username`, `occurred_at`) VALUES
(1, 0x7f000001, 'ahmed@example.com', '2025-08-12 18:27:37'),
(2, 0x7f000001, 'admin@ideavote.com', '2025-08-12 18:46:54'),
(3, 0x7f000001, 'admin@ideavote.com', '2025-08-12 18:47:50'),
(4, 0x7f000001, 'admin@ideavote.com', '2025-08-12 18:47:56'),
(5, 0x7f000001, 'admin@ideavote.com', '2025-08-12 18:47:58'),
(6, 0x7f000001, 'admin@ideavote.com', '2025-08-12 18:48:03'),
(7, 0x7f000001, 'ahmed@example.com', '2025-08-12 23:04:32'),
(8, 0x7f000001, 'ahmed@example.com', '2025-08-12 23:04:42'),
(9, 0x7f000001, 'ahmed@example.com', '2025-08-12 23:10:43'),
(10, 0x7f000001, 'ahmed@example.com', '2025-08-12 23:11:03'),
(11, 0x7f000001, 'admin@ideavote.com', '2025-08-12 23:11:42'),
(12, 0x7f000001, 'admin@ideavote.com', '2025-08-12 23:11:48'),
(13, 0x7f000001, 'admin@ideavote.com', '2025-08-12 23:13:02'),
(14, 0x7f000001, 'admin@ideavote.com', '2025-08-12 23:13:10');

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('vote','comment','follow','mention','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `created_at`) VALUES
(1, 4, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-11 21:42:10'),
(2, 4, 'follow', 'New Follower', 'Someone started following you', NULL, 0, '2025-08-11 21:42:10'),
(3, 3, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-11 21:42:10'),
(4, 4, 'follow', 'New Follower', 'Someone started following you', NULL, 0, '2025-08-11 21:42:10'),
(5, 2, 'system', 'Welcome', 'Welcome to IdeaVote!', NULL, 0, '2025-08-11 21:42:10'),
(6, 4, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-11 21:42:10'),
(7, 3, 'system', 'Welcome', 'Welcome to IdeaVote!', NULL, 0, '2025-08-11 21:42:10'),
(8, 4, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-11 21:42:10'),
(9, 4, 'follow', 'New Follower', 'Someone started following you', NULL, 0, '2025-08-12 00:35:38'),
(10, 2, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-12 00:35:38'),
(11, 2, 'follow', 'New Follower', 'Someone started following you', NULL, 0, '2025-08-12 00:35:38'),
(12, 2, 'comment', 'New Comment', 'Someone commented on your idea', NULL, 0, '2025-08-12 00:35:38'),
(13, 2, 'follow', 'New Follower', 'Someone started following you', NULL, 0, '2025-08-12 00:35:38'),
(14, 4, 'system', 'Welcome', 'Welcome to IdeaVote!', NULL, 0, '2025-08-12 00:35:38'),
(15, 4, 'comment', 'New Comment', 'Someone commented on your idea', NULL, 0, '2025-08-12 00:35:38'),
(16, 2, 'system', 'Welcome', 'Welcome to IdeaVote!', NULL, 0, '2025-08-12 00:35:38'),
(17, 2, '', 'New Smart Reaction', 'Someone reacted with 🧠 to your idea', '{\"idea_id\":1,\"reaction_type\":\"brain\",\"reactor_id\":1}', 0, '2025-08-12 00:35:58'),
(18, 2, '', 'New Smart Reaction', 'Someone reacted with 🧠 to your idea', '{\"idea_id\":1,\"reaction_type\":\"brain\",\"reactor_id\":1}', 0, '2025-08-12 00:36:01'),
(19, 4, 'system', 'Welcome', 'Welcome to IdeaVote!', NULL, 0, '2025-08-12 00:39:56'),
(20, 3, 'system', 'Welcome', 'Welcome to IdeaVote!', NULL, 0, '2025-08-12 00:39:56'),
(21, 3, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-12 00:39:56'),
(22, 2, 'comment', 'New Comment', 'Someone commented on your idea', NULL, 0, '2025-08-12 00:39:56'),
(23, 4, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-12 00:39:56'),
(24, 4, 'follow', 'New Follower', 'Someone started following you', NULL, 0, '2025-08-12 00:39:56'),
(25, 2, 'vote', 'New Vote', 'Someone voted on your idea', NULL, 0, '2025-08-12 00:39:56'),
(26, 3, 'system', 'Welcome', 'Welcome to IdeaVote!', NULL, 0, '2025-08-12 00:39:56');

-- --------------------------------------------------------

--
-- بنية الجدول `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varbinary(32) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `idea_id` int(11) NOT NULL,
  `reaction_type` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `reactions`
--

INSERT INTO `reactions` (`id`, `user_id`, `idea_id`, `reaction_type`, `created_at`) VALUES
(1, 2, 3, '🎉', '2025-08-17 19:27:15'),
(2, 4, 3, 'love', '2025-08-11 21:39:12'),
(3, 4, 2, 'laugh', '2025-08-11 21:39:12'),
(4, 4, 1, 'laugh', '2025-08-11 21:39:12'),
(5, 3, 1, 'love', '2025-08-11 21:39:12'),
(7, 3, 3, 'love', '2025-08-11 21:39:12'),
(8, 2, 1, 'wow', '2025-08-11 21:39:12'),
(9, 2, 2, 'like', '2025-08-11 21:39:12'),
(12, 3, 2, 'wow', '2025-08-11 21:39:12'),
(167, 1, 1, '❤️', '2025-08-17 19:19:13');

-- --------------------------------------------------------

--
-- بنية الجدول `reported_content`
--

CREATE TABLE `reported_content` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `content_type` enum('idea','comment','user') NOT NULL,
  `content_id` int(11) NOT NULL,
  `reason` enum('spam','inappropriate','harassment','copyright','other') NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `saved_filters`
--

CREATE TABLE `saved_filters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `filter_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`filter_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `trending_ideas`
-- (See below for the actual view)
--
CREATE TABLE `trending_ideas` (
`id` int(11)
,`user_id` int(11)
,`category_id` int(11)
,`title` varchar(255)
,`slug` varchar(255)
,`description` text
,`is_public` tinyint(1)
,`is_approved` tinyint(1)
,`is_featured` tinyint(1)
,`image_url` varchar(255)
,`votes_count` int(11)
,`views_count` int(11)
,`trending_score` decimal(10,4)
,`tags` longtext
,`created_at` timestamp
,`calculated_score` decimal(24,1)
);

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `bio` text DEFAULT '',
  `image_url` varchar(255) DEFAULT NULL,
  `language` enum('en','ar') DEFAULT 'en',
  `theme` enum('light','dark','auto') DEFAULT 'auto',
  `email_notifications` tinyint(1) DEFAULT 1,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_admin`, `bio`, `image_url`, `language`, `theme`, `email_notifications`, `avatar`, `created_at`) VALUES
(1, 'admin', 'admin@ideavote.com', '$2y$10$ScgXYrdKaF7OgKT4NjaN/uzNTuh1sZ6r48O7F5RS3vwEZI.J4Qq1S', 1, 'Platform administrator', NULL, 'en', 'light', 1, NULL, '2025-08-11 21:39:11'),
(2, 'ahmed', 'ahmed@example.com', '$2y$10$S7UxdH.8ARdygvTWADVpju3648JjNqqjjZkGCvGEa.w3Cox21U0ei', 0, 'Technology enthusiast', 'uploads/users/689bb8b1db187_1755035825.png', 'en', 'dark', 1, NULL, '2025-08-11 21:39:11'),
(3, 'sarah', 'sarah@example.com', '$2y$10$ScgXYrdKaF7OgKT4NjaN/uzNTuh1sZ6r48O7F5RS3vwEZI.J4Qq1S', 0, 'Environmental activist', NULL, 'en', 'auto', 1, NULL, '2025-08-11 21:39:11'),
(4, 'mohammed', 'mohammed@example.com', '$2y$10$ScgXYrdKaF7OgKT4NjaN/uzNTuh1sZ6r48O7F5RS3vwEZI.J4Qq1S', 0, 'Education reformer', NULL, 'en', 'auto', 1, NULL, '2025-08-11 21:39:12');

-- --------------------------------------------------------

--
-- بنية الجدول `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `idea_id` int(11) NOT NULL,
  `vote_type` enum('like','dislike') NOT NULL DEFAULT 'like',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `votes`
--

INSERT INTO `votes` (`id`, `user_id`, `idea_id`, `vote_type`, `created_at`) VALUES
(1, 2, 1, 'like', '2025-08-11 21:39:12'),
(2, 3, 2, 'dislike', '2025-08-11 21:39:12'),
(4, 3, 3, 'dislike', '2025-08-11 21:39:12'),
(5, 3, 1, 'dislike', '2025-08-11 21:39:12'),
(10, 4, 1, 'like', '2025-08-11 21:39:12'),
(11, 2, 2, 'like', '2025-08-11 21:39:12'),
(12, 4, 2, 'dislike', '2025-08-11 21:39:12'),
(16, 4, 3, 'like', '2025-08-11 21:39:12'),
(38, 2, 3, 'like', '2025-08-11 21:41:08'),
(81, 1, 1, 'like', '2025-08-11 21:59:28');

-- --------------------------------------------------------

--
-- بنية الجدول `webhook_events`
--

CREATE TABLE `webhook_events` (
  `id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `processed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `webhook_events`
--

INSERT INTO `webhook_events` (`id`, `event_type`, `payload`, `processed`, `created_at`, `processed_at`) VALUES
(1, 'idea.created', '{\"idea_id\": 1, \"user_id\": 2, \"title\": \"Smart City Traffic Management System\"}', 0, '2025-08-11 21:42:10', NULL),
(2, 'idea.created', '{\"idea_id\": 1, \"user_id\": 2, \"title\": \"Smart City Traffic Management System\"}', 0, '2025-08-12 00:35:38', NULL),
(3, 'idea.created', '{\"idea_id\": 1, \"user_id\": 2, \"title\": \"Smart City Traffic Management System\"}', 0, '2025-08-12 00:39:56', NULL);

-- --------------------------------------------------------

--
-- Structure for view `trending_ideas`
--
DROP TABLE IF EXISTS `trending_ideas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `trending_ideas`  AS SELECT `i`.`id` AS `id`, `i`.`user_id` AS `user_id`, `i`.`category_id` AS `category_id`, `i`.`title` AS `title`, `i`.`slug` AS `slug`, `i`.`description` AS `description`, `i`.`is_public` AS `is_public`, `i`.`is_approved` AS `is_approved`, `i`.`is_featured` AS `is_featured`, `i`.`image_url` AS `image_url`, `i`.`votes_count` AS `votes_count`, `i`.`views_count` AS `views_count`, `i`.`trending_score` AS `trending_score`, `i`.`tags` AS `tags`, `i`.`created_at` AS `created_at`, `i`.`votes_count`* 10 + `i`.`views_count` * 0.1 + count(`c`.`id`) * 5 + timestampdiff(HOUR,`i`.`created_at`,current_timestamp()) * -0.1 AS `calculated_score` FROM (`ideas` `i` left join `comments` `c` on(`i`.`id` = `c`.`idea_id`)) WHERE `i`.`is_approved` = 1 GROUP BY `i`.`id` ORDER BY `i`.`votes_count`* 10 + `i`.`views_count` * 0.1 + count(`c`.`id`) * 5 + timestampdiff(HOUR,`i`.`created_at`,current_timestamp()) * -0.1 DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_action` (`admin_id`,`action`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`user_id`,`idea_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_idea` (`idea_id`),
  ADD KEY `idx_bookmarks_user_idea` (`user_id`,`idea_id`),
  ADD KEY `idx_bookmarks_idea` (`idea_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name_en` (`name_en`),
  ADD UNIQUE KEY `unique_name_ar` (`name_ar`);

--
-- Indexes for table `category_follows`
--
ALTER TABLE `category_follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category_follow` (`user_id`,`category_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_category` (`category_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_idea_id` (`idea_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`following_id`),
  ADD KEY `idx_follower` (`follower_id`),
  ADD KEY `idx_following` (`following_id`);

--
-- Indexes for table `ideas`
--
ALTER TABLE `ideas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_featured` (`is_featured`);
ALTER TABLE `ideas` ADD FULLTEXT KEY `title` (`title`,`description`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_occurred_at` (`occurred_at`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_ip` (`ip`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_token_hash` (`token_hash`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_idea_type` (`idea_id`,`reaction_type`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `reported_content`
--
ALTER TABLE `reported_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_content` (`content_type`,`content_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reporter` (`reporter_id`);

--
-- Indexes for table `saved_filters`
--
ALTER TABLE `saved_filters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session` (`session_id`),
  ADD KEY `idx_user_active` (`user_id`,`is_active`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`user_id`,`idea_id`),
  ADD KEY `idx_idea_id` (`idea_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `webhook_events`
--
ALTER TABLE `webhook_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_processed` (`processed`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `category_follows`
--
ALTER TABLE `category_follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `ideas`
--
ALTER TABLE `ideas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `reported_content`
--
ALTER TABLE `reported_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_filters`
--
ALTER TABLE `saved_filters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `webhook_events`
--
ALTER TABLE `webhook_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `category_follows`
--
ALTER TABLE `category_follows`
  ADD CONSTRAINT `category_follows_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_follows_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `ideas`
--
ALTER TABLE `ideas`
  ADD CONSTRAINT `ideas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ideas_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `reported_content`
--
ALTER TABLE `reported_content`
  ADD CONSTRAINT `reported_content_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reported_content_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `saved_filters`
--
ALTER TABLE `saved_filters`
  ADD CONSTRAINT `saved_filters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
