-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 29 Nis 2026, 13:22:04
-- Sunucu sürümü: 8.0.45-cll-lve
-- PHP Sürümü: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `ahmetkes_agency`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int UNSIGNED NOT NULL,
  `post_id` int UNSIGNED NOT NULL,
  `parent_id` int UNSIGNED DEFAULT NULL,
  `author_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `author_id` int UNSIGNED DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `view_count` int DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blog_post_translations`
--

CREATE TABLE `blog_post_translations` (
  `id` int UNSIGNED NOT NULL,
  `post_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `booking_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_type` enum('tour','transfer') COLLATE utf8mb4_unicode_ci DEFAULT 'tour',
  `booking_status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `booking_direction` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'outbound',
  `tour_id` int DEFAULT NULL,
  `destination_id` int DEFAULT NULL,
  `vehicle_id` int DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_location` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `return_time` time DEFAULT NULL,
  `flight_date` date DEFAULT NULL,
  `flight_time` time DEFAULT NULL,
  `flight_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hotel_address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_transfer` tinyint(1) DEFAULT '0',
  `return_flight_date` date DEFAULT NULL,
  `return_flight_time` time DEFAULT NULL,
  `return_flight_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_pickup_time` time DEFAULT NULL,
  `return_hotel_address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adults` int DEFAULT '1',
  `children` int DEFAULT '0',
  `child_seat` int DEFAULT '0',
  `total_price` decimal(10,2) DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'TRY',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `is_outsourced` tinyint(1) NOT NULL DEFAULT '0',
  `outsource_price` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `outsource_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outsource_pickup_time` time DEFAULT NULL,
  `outsource_partner_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `bookings`
--

INSERT INTO `bookings` (`id`, `booking_number`, `booking_type`, `booking_status`, `booking_direction`, `tour_id`, `destination_id`, `vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `pickup_location`, `pickup_date`, `pickup_time`, `return_time`, `flight_date`, `flight_time`, `flight_number`, `hotel_address`, `return_transfer`, `return_flight_date`, `return_flight_time`, `return_flight_number`, `return_pickup_time`, `return_hotel_address`, `adults`, `children`, `child_seat`, `total_price`, `currency`, `notes`, `admin_notes`, `is_completed`, `is_outsourced`, `outsource_price`, `created_at`, `updated_at`, `outsource_name`, `outsource_pickup_time`, `outsource_partner_id`) VALUES
(37, 'TRF-20260428-0191', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'FİKRET GEMRİKLİ', '', '+49017680515901', NULL, NULL, '07:30:00', NULL, '2026-05-02', '18:25:00', 'XQ147', 'Delphin Be Grand', 0, NULL, NULL, NULL, NULL, NULL, 4, 2, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-28 20:50:28', '2026-04-28 20:53:30', NULL, NULL, NULL),
(38, 'TRF-20260428-1764', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'FİKRET GEMRİKLİ', '', '+49017680515901', NULL, NULL, '07:30:00', NULL, '2026-05-09', '10:05:00', 'XQ146', 'Delphin Be Grand', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-28 20:50:28', '2026-04-29 02:21:26', NULL, NULL, NULL),
(39, 'TRF-20260428-1622', 'transfer', 'confirmed', 'outbound', NULL, 17, 1, 'INES KAUFMANN', '', '+491629148108', NULL, NULL, NULL, NULL, '2026-05-14', '06:15:00', 'XQ247', 'Aska Just In Beach', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-28 21:05:48', '2026-04-28 23:56:19', NULL, NULL, NULL),
(48, 'TRF-20260429-7273', 'transfer', 'confirmed', 'return', NULL, 7, 3, 'KANDSORRA WOLFGANG', '', '+4917672883149', NULL, NULL, '17:30:00', NULL, '2026-04-29', '20:55:00', 'XQ236', 'Lilyum Hotel', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-29 09:51:57', '2026-04-29 10:02:26', NULL, NULL, NULL),
(49, 'TRF-20260429-7985', 'transfer', 'confirmed', 'return', NULL, 10, 1, 'EDVIN TISK', '', '+4748653342', NULL, NULL, '09:00:00', NULL, '2026-04-28', '12:00:00', NULL, 'Lago Hotel', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, '2026-04-29 09:59:09', '2026-04-29 12:51:17', NULL, NULL, NULL),
(50, 'TRF-20260429-4802', 'transfer', 'confirmed', 'outbound', NULL, 18, 1, 'KYANAT SHAH', '', '+447464218024', NULL, NULL, NULL, NULL, '2026-04-29', '16:25:00', 'LS1239', 'Eftalia Splash', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-29 10:17:31', '2026-04-29 10:17:31', NULL, NULL, NULL),
(51, 'TRF-20260429-0437', 'transfer', 'confirmed', 'return', NULL, 18, 1, 'KYANAT SHAH', '', '+447464218024', NULL, NULL, '17:30:00', NULL, '2026-05-03', NULL, 'LS1120', 'Eftalia Splash', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-29 10:17:31', '2026-04-29 10:17:31', NULL, NULL, NULL),
(52, 'TRF-20260429-6978', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'ANNA JAKUZSEW', '', '+48513095789', NULL, NULL, NULL, NULL, '2026-04-29', '17:55:00', 'ENT7055', 'Delphin Palace', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-29 10:33:02', '2026-04-29 10:33:02', NULL, NULL, NULL),
(53, 'TRF-20260429-0746', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'ANNA JAKUZSEW', '', '+48513095789', NULL, NULL, '11:00:00', NULL, '2026-05-06', '13:50:00', 'ENT7056', 'Delphin Palace', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-29 10:33:02', '2026-04-29 11:01:28', NULL, NULL, NULL),
(54, 'TRF-20260429-2347', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'PATRICIA HIBBERT', '', '+44756534817', NULL, NULL, '17:30:00', NULL, '2026-04-29', '22:30:00', 'EZY2856', 'Port River Hotel', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-29 10:46:13', '2026-04-29 10:57:50', NULL, NULL, NULL),
(55, 'TRF-20260429-9867', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'EMILY HENZLER', '', '', NULL, NULL, NULL, NULL, '2026-01-24', '19:00:00', 'XQ141', 'Grand Park Lara', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 1, 15.00, '2026-04-29 11:44:43', '2026-04-29 12:55:31', NULL, NULL, NULL),
(56, 'TRF-20260429-5173', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'EMILY HENZLER', '', '', NULL, NULL, '07:30:00', NULL, '2026-01-27', '10:45:00', 'XQ140', 'Grand Park Lara', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 1, 15.00, '2026-04-29 11:44:43', '2026-04-29 12:49:29', NULL, '07:30:00', NULL),
(57, 'TRF-20260429-1746', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'ANJA LANGENBACH', '', '+49 176 34139393', NULL, NULL, NULL, NULL, '2026-02-04', '19:10:00', 'XQ115', 'Aydınbey Kings Palace', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, '2026-04-29 11:50:45', '2026-04-29 11:56:29', NULL, NULL, NULL),
(58, 'TRF-20260429-4915', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'ANJA LANGENBACH', '', '+49 176 34139393', NULL, NULL, '07:00:00', NULL, '2026-02-11', '11:00:00', 'XQ114', 'Aydınbey Kings Palace', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 39.98, 'EUR', NULL, NULL, 1, 0, NULL, '2026-04-29 11:50:45', '2026-04-29 11:56:30', NULL, NULL, NULL),
(59, 'TRF-20260429-7223', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'JENS KUHLMANN', '', '+49 173 6520273', NULL, NULL, NULL, NULL, '2026-02-05', '19:05:00', 'XQ233', 'The Sense Deluxe', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, '2026-04-29 11:55:02', '2026-04-29 11:55:48', NULL, NULL, NULL),
(60, 'TRF-20260429-5059', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'JENS KUHLMANN', '', '+49 173 6520273', NULL, NULL, '19:00:00', NULL, '2026-02-12', '23:00:00', 'PC5009', 'The Sense Deluxe', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 1, 25.00, '2026-04-29 11:55:02', '2026-04-29 11:56:19', NULL, '19:00:00', NULL),
(61, 'TRF-20260429-0925', 'transfer', 'confirmed', 'outbound', NULL, 12, 1, 'ROMAN BILY', '', '+44 7394 764702', NULL, NULL, NULL, NULL, '2026-04-22', '20:00:00', 'XQ593', 'Kleopatra Royal Palm', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 75.00, 'EUR', NULL, NULL, 0, 0, NULL, '2026-04-29 12:03:27', '2026-04-29 12:03:27', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `booking_alert_translations`
--

CREATE TABLE `booking_alert_translations` (
  `id` int NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `icon` varchar(100) DEFAULT 'bi-exclamation-triangle',
  `color` varchar(20) DEFAULT 'warning',
  `message` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `booking_alert_translations`
--

INSERT INTO `booking_alert_translations` (`id`, `language_code`, `icon`, `color`, `message`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'de', 'bi-flag-fill', 'warning', 'Tek yön Transferin Bedelenin Tamamını , Gidiş Dönüş Transfer Bedelinin Yarısını Araç Şoförüne Nakit Ödeyelim', 1, '2026-01-29 23:25:40', '2026-01-29 23:31:28'),
(2, 'tr', 'bi-flag-fill', 'warning', 'Tek yön Transferin Bedelenin Tamamını , Gidiş Dönüş Transfer Bedelinin Yarısını Araç Şoförüne Nakit Ödeyelim', 1, '2026-01-29 23:25:40', '2026-01-29 23:31:28'),
(3, 'en', 'bi-flag-fill', 'warning', 'Tek yön Transferin Bedelenin Tamamını , Gidiş Dönüş Transfer Bedelinin Yarısını Araç Şoförüne Nakit Ödeyelim', 1, '2026-01-29 23:25:40', '2026-01-29 23:31:28');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `booking_passengers`
--

CREATE TABLE `booking_passengers` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `booking_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passenger_type` enum('adult','child') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'adult',
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `booking_passengers`
--

INSERT INTO `booking_passengers` (`id`, `booking_id`, `booking_number`, `passenger_type`, `full_name`, `sort_order`, `created_at`) VALUES
(31, 37, 'TRF-20260428-0191', 'adult', 'FİKRET GEMRİKLİ', 0, '2026-04-28 17:53:30'),
(32, 37, 'TRF-20260428-0191', 'adult', 'HOUDA GEMRİKLİ', 1, '2026-04-28 17:53:30'),
(33, 37, 'TRF-20260428-0191', 'adult', 'NAJAH MORADG', 2, '2026-04-28 17:53:30'),
(34, 37, 'TRF-20260428-0191', 'adult', 'MARYAM MORADG', 3, '2026-04-28 17:53:30'),
(35, 37, 'TRF-20260428-0191', 'child', 'TUANA GEMRİKLİ', 0, '2026-04-28 17:53:30'),
(36, 37, 'TRF-20260428-0191', 'child', 'MELİNAY GEMRİKLİ', 1, '2026-04-28 17:53:30'),
(39, 39, 'TRF-20260428-1622', 'adult', 'INES KAUFMANN', 0, '2026-04-28 18:05:48'),
(49, 38, 'TRF-20260428-1764', 'adult', 'FİKRET GEMRİKLİ', 0, '2026-04-28 23:21:26'),
(50, 38, 'TRF-20260428-1764', 'adult', 'HOUDA GEMRİKLİ', 1, '2026-04-28 23:21:26'),
(51, 48, 'TRF-20260429-7273', 'adult', 'KANDSORRA WOLFGANG', 0, '2026-04-29 06:51:57'),
(52, 48, 'TRF-20260429-7273', 'adult', 'KRISTUN UJEN', 1, '2026-04-29 06:51:57'),
(53, 48, 'TRF-20260429-7273', 'adult', 'MICHAEL UJEN', 2, '2026-04-29 06:51:57'),
(54, 48, 'TRF-20260429-7273', 'adult', 'MANUELE UJEN', 3, '2026-04-29 06:51:57'),
(55, 48, 'TRF-20260429-7273', 'adult', 'MARCO BOHLEN', 4, '2026-04-29 06:51:57'),
(56, 48, 'TRF-20260429-7273', 'adult', 'NICOLAI LENNERT', 5, '2026-04-29 06:51:57'),
(57, 48, 'TRF-20260429-7273', 'adult', 'MALENA KNABLE', 6, '2026-04-29 06:51:57'),
(58, 48, 'TRF-20260429-7273', 'adult', 'MARKUS GERHARDS', 7, '2026-04-29 06:51:57'),
(61, 50, 'TRF-20260429-4802', 'adult', 'KYANAT SHAH', 0, '2026-04-29 07:17:31'),
(62, 50, 'TRF-20260429-4802', 'adult', 'MUSHRAF BUKHARI', 1, '2026-04-29 07:17:31'),
(63, 50, 'TRF-20260429-4802', 'adult', 'ALEEZA BUKHARI', 2, '2026-04-29 07:17:31'),
(64, 50, 'TRF-20260429-4802', 'adult', 'ADAM BUKHARI', 3, '2026-04-29 07:17:31'),
(65, 51, 'TRF-20260429-0437', 'adult', 'KYANAT SHAH', 0, '2026-04-29 07:17:31'),
(66, 51, 'TRF-20260429-0437', 'adult', 'MUSHRAF BUKHARI', 1, '2026-04-29 07:17:31'),
(67, 51, 'TRF-20260429-0437', 'adult', 'ALEEZA BUKHARI', 2, '2026-04-29 07:17:31'),
(68, 51, 'TRF-20260429-0437', 'adult', 'ADAM BUKHARI', 3, '2026-04-29 07:17:31'),
(69, 52, 'TRF-20260429-6978', 'adult', 'ANNA JAKUZSEW', 0, '2026-04-29 07:33:02'),
(70, 52, 'TRF-20260429-6978', 'adult', 'ALEX JAKUZSEW', 1, '2026-04-29 07:33:02'),
(71, 53, 'TRF-20260429-0746', 'adult', 'ANNA JAKUZSEW', 0, '2026-04-29 07:33:02'),
(72, 53, 'TRF-20260429-0746', 'adult', 'ALEX JAKUZSEW', 1, '2026-04-29 07:33:02'),
(73, 54, 'TRF-20260429-2347', 'adult', 'PATRICIA HIBBERT', 0, '2026-04-29 07:46:13'),
(74, 54, 'TRF-20260429-2347', 'adult', 'KARINA KARTILL', 1, '2026-04-29 07:46:13'),
(75, 55, 'TRF-20260429-9867', 'adult', 'EMILY HENZLER', 0, '2026-04-29 08:44:43'),
(76, 56, 'TRF-20260429-5173', 'adult', 'EMILY HENZLER', 0, '2026-04-29 08:44:43'),
(77, 57, 'TRF-20260429-1746', 'adult', 'ANJA LANGENBACH', 0, '2026-04-29 08:50:45'),
(78, 57, 'TRF-20260429-1746', 'adult', 'MICHAEL OHM', 1, '2026-04-29 08:50:45'),
(79, 58, 'TRF-20260429-4915', 'adult', 'ANJA LANGENBACH', 0, '2026-04-29 08:50:45'),
(80, 58, 'TRF-20260429-4915', 'adult', 'MICHAEL OHM', 1, '2026-04-29 08:50:45'),
(81, 59, 'TRF-20260429-7223', 'adult', 'JENS KUHLMANN', 0, '2026-04-29 08:55:02'),
(82, 59, 'TRF-20260429-7223', 'adult', 'CHAHWIWAN SEEHAKAN KUHLMANN', 1, '2026-04-29 08:55:02'),
(85, 60, 'TRF-20260429-5059', 'adult', 'JENS KUHLMANN', 0, '2026-04-29 08:56:19'),
(86, 60, 'TRF-20260429-5059', 'adult', 'CHAHWIWAN SEEHAKAN KUHLMANN', 1, '2026-04-29 08:56:19'),
(87, 61, 'TRF-20260429-0925', 'adult', 'ROMAN BILY', 0, '2026-04-29 09:03:27'),
(88, 61, 'TRF-20260429-0925', 'adult', 'MARTIN LACKO', 1, '2026-04-29 09:03:27'),
(91, 49, 'TRF-20260429-7985', 'adult', 'EDVIN TISK', 0, '2026-04-29 09:51:17'),
(92, 49, 'TRF-20260429-7985', 'adult', 'ANA TISK', 1, '2026-04-29 09:51:17');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `contacts`
--

CREATE TABLE `contacts` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `destinations`
--

CREATE TABLE `destinations` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `continent` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starting_price` decimal(10,2) DEFAULT NULL,
  `badge` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT '0.0',
  `review_count` int DEFAULT '0',
  `tour_count` int DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `destinations`
--

INSERT INTO `destinations` (`id`, `title`, `slug`, `description`, `content`, `featured_image`, `gallery`, `location`, `country`, `continent`, `starting_price`, `badge`, `image`, `rating`, `review_count`, `tour_count`, `is_featured`, `meta_title`, `meta_description`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Side Transfer', 'side-transfer', '', '', 'assets/img/travel/destination-3.webp', NULL, 'Antalya / Manavgat / Side', 'Maldives', 'Asia', NULL, '', 'general/1769548145_0b146a91be92ee71.webp', 4.9, 412, 22, 1, '', '', 'published', 1, '2026-01-21 18:27:13', '2026-04-27 18:57:27'),
(2, 'Kumköy Transfer', 'kumkoy-transfer', '', '', 'assets/img/travel/destination-7.webp', NULL, 'Antalya / Manavgat / Kumköy', 'Nepal', 'Asia', NULL, '', 'general/1769548779_8597a2056acace9b.webp', 4.8, 180, 16, 1, 'Kumköy Transfer', '', 'published', 2, '2026-01-21 18:27:13', '2026-04-27 18:57:32'),
(6, 'Çolaklı Transfer', 'colakli-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Çolaklı', NULL, NULL, NULL, '', 'general/1769548794_76d96fe1935ff2f1.webp', 0.0, 0, 0, 1, 'Çolaklı Transfer', '', 'published', 3, '2026-01-27 20:44:19', '2026-04-27 18:57:40'),
(7, 'Evrenseki Transfer', 'evrenseki-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Evrenseki', NULL, NULL, NULL, '', 'general/1769353749_a7a5d089718ac8d5.webp', 0.0, 0, 0, 1, 'Evrenseki Transfer', '', 'published', 4, '2026-01-27 20:50:19', '2026-04-27 18:57:50'),
(8, 'Sorgun Transfer', 'sorgun-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Sorgun', NULL, NULL, NULL, '', 'general/1769551034_e479d68bf6e127ce.webp', 0.0, 0, 0, 1, 'Sorgun Transfer', '', 'published', 5, '2026-01-27 21:57:21', '2026-04-27 18:57:56'),
(10, 'Antalya Havalimanı (AYT)', 'antalya-havalimani-ayt', '', '', NULL, NULL, 'Antalya Airport', NULL, NULL, NULL, '', 'general/1771021316_3280340d44294aef.png', 0.0, 0, 0, 1, 'Antalya Havalimanı (AYT)', '', 'published', 6, '2026-02-13 22:22:17', '2026-04-26 07:22:47'),
(11, 'Kızılağaç Transfer', 'kizilagac-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Kızılağaç', NULL, NULL, NULL, '', 'general/1771187936_7f98bb9d64c2ddc5.jpeg', 0.0, 0, 0, 1, 'Kızılağaç Transfer', '', 'published', 7, '2026-02-15 20:39:07', '2026-04-27 18:58:09'),
(12, 'Alanya Transfer', 'alanya-transfer', '', '', NULL, NULL, 'Antalya / Alanya', NULL, NULL, NULL, '', 'general/1769551064_43aaee0999f03861.webp', 0.0, 0, 0, 0, 'Alanya Transfer', '', 'published', 8, '2026-04-26 08:03:34', '2026-04-28 23:38:59'),
(13, 'Kundu Transfer', 'kundu-transfer', '', '', NULL, NULL, 'Antalya / Kundu', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kundu Transfer', '', 'published', 9, '2026-04-26 11:48:36', '2026-04-26 11:48:36'),
(14, 'Lara Transfer', 'lara-transfer', '', '', NULL, NULL, 'Antalya / Lara', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Lara Transfer', '', 'published', 10, '2026-04-26 11:50:40', '2026-04-26 12:02:06'),
(15, 'Kızılot Transfer', 'kizilot-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Kızılot', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kızılot Transfer', '', 'published', 11, '2026-04-27 18:57:12', '2026-04-27 18:58:24'),
(16, 'Okurcalar Transfer', 'okurcalar-transfer', '', '', NULL, NULL, 'Antalya / Alanya / Okurcalar', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Okurcalar Transfer', '', 'published', 12, '2026-04-27 18:59:40', '2026-04-27 19:00:03'),
(17, 'Avsallar Transfer', 'avsallar-transfer', '', '', NULL, NULL, 'Antalya / Alanya / Avsallar', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Avsallar Transfer', '', 'published', 13, '2026-04-28 18:02:59', '2026-04-28 18:02:59'),
(18, 'Türkler Transfer', 'turkler-transfer', '', '', NULL, NULL, 'Antalya / Alanya / Türkler', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Türkler Transfer', '', 'published', 14, '2026-04-29 07:04:46', '2026-04-29 07:04:46');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `destination_translations`
--

CREATE TABLE `destination_translations` (
  `id` int UNSIGNED NOT NULL,
  `destination_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `destination_translations`
--

INSERT INTO `destination_translations` (`id`, `destination_id`, `language_code`, `title`, `slug`, `from_location`, `to_location`, `description`, `content`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 1, 'en', 'Side Transfer', 'side-transfer', '', '', '', '', '', '', '2026-01-27 20:27:59', '2026-02-01 20:48:42'),
(3, 1, 'de', 'Side Transfer', 'side-transfer', '', '', '', '', '', '', '2026-01-27 20:28:32', '2026-02-01 20:48:41'),
(15, 2, 'de', 'Kumköy Transfer', 'kumkoy-transfer', '', '', '', '', 'Kumköy Transfer', '', '2026-01-27 20:36:22', '2026-04-26 07:21:21'),
(16, 2, 'en', 'Kumköy Transfer', 'kumkoy-transfer', '', '', '', '', 'Kumköy Transfer', '', '2026-01-27 20:36:22', '2026-04-26 07:21:21'),
(21, 5, 'de', 'Evrenseki Transfer', 'evrenseki-transfer', NULL, NULL, '', '', 'Evrenseki Transfer', '', '2026-01-27 20:43:49', '2026-01-27 20:43:49'),
(22, 5, 'en', 'Evrenseki Transfer', 'evrenseki-transfer', NULL, NULL, '', '', 'Evrenseki Transfer', '', '2026-01-27 20:43:49', '2026-01-27 20:43:49'),
(23, 6, 'de', 'Çolaklı Transfer', 'colakli-transfer', '', '', '', '', 'Çolaklı Transfer', '', '2026-01-27 20:44:19', '2026-04-26 07:35:09'),
(24, 6, 'en', 'Çolaklı Transfer', 'colakli-transfer', '', '', '', '', 'Çolaklı Transfer', '', '2026-01-27 20:44:20', '2026-04-26 07:35:09'),
(27, 7, 'de', 'Evrenseki Transfer', 'evrenseki-transfer', '', '', '', '', 'Evrenseki Transfer', '', '2026-01-27 20:50:20', '2026-04-26 07:37:07'),
(28, 7, 'en', 'Evrenseki Transfer', 'evrenseki-transfer', '', '', '', '', 'Evrenseki Transfer', '', '2026-01-27 20:50:20', '2026-04-26 07:37:07'),
(35, 8, 'de', 'Sorgun Transfer', 'sorgun-transfer', '(AYT) Antalya Hava Alanı DE', 'Sorgun', '', '', 'Sorgun Transfer', '', '2026-01-27 21:57:21', '2026-01-29 22:58:28'),
(36, 8, 'en', 'Sorgun Transfer', 'sorgun-transfer', '(AYT) Antalya Hava Alanı EN', 'Sorgun', '', '', 'Sorgun Transfer', '', '2026-01-27 21:57:21', '2026-01-29 22:58:28'),
(50, 8, 'tr', 'Sorgun Transfer', 'sorgun-transfer', '', '', '', '', 'Sorgun Transfer', '', '2026-01-29 23:00:33', '2026-04-26 07:42:03'),
(53, 1, 'tr', 'Side Transfer', 'side-transfer', '', '', '', '', '', '', '2026-02-01 20:48:41', '2026-02-01 20:48:41'),
(55, 10, 'de', 'Antalya Flughafen (AYT)', 'antalya-flughafen-ayt', '', '', '', '', 'Antalya Flughafen (AYT)', '', '2026-02-13 22:22:17', '2026-02-13 22:22:17'),
(56, 10, 'tr', 'Antalya Havalimanı (AYT)', 'antalya-havalimani-ayt', '', '', '', '', 'Antalya Havalimanı (AYT)', '', '2026-02-13 22:22:17', '2026-02-13 22:22:17'),
(57, 10, 'en', 'Antalya Airport (AYT)', 'antalya-airport-ayt', '', '', '', '', 'Antalya Airport (AYT)', '', '2026-02-13 22:22:17', '2026-02-13 22:22:17'),
(61, 11, 'de', 'Kızılağaç DE Transfer', 'kizilagac-de-transfer', '', '', '', '', 'Kızılağaç DE Transfer', '', '2026-02-15 20:39:07', '2026-02-15 20:39:07'),
(62, 11, 'tr', 'Kızılağaç Transfer', 'kizilagac-transfer', '', '', '', '', 'Kızılağaç Transfer', '', '2026-02-15 20:39:07', '2026-02-15 20:39:07'),
(63, 11, 'en', 'Kızılağaç Transfer', 'kizilagac-transfer', '', '', '', '', 'Kızılağaç Transfer', '', '2026-02-15 20:39:07', '2026-02-15 20:39:07'),
(71, 2, 'tr', 'Kumköy Transfer', 'kumkoy-transfer', '', '', '', '', 'Kumköy Transfer', '', '2026-04-26 07:21:21', '2026-04-26 07:21:21'),
(122, 6, 'tr', 'Çolaklı Transfer', 'colakli-transfer', '', '', '', '', 'Çolaklı Transfer', '', '2026-04-26 07:35:09', '2026-04-26 07:35:09'),
(137, 7, 'tr', 'Evrenseki Transfer', 'evrenseki-transfer', '', '', '', '', 'Evrenseki Transfer', '', '2026-04-26 07:37:07', '2026-04-26 07:37:07'),
(178, 12, 'de', 'Alanya Transfer', 'alanya-transfer', '', '', '', '', 'Alanya Transfer', '', '2026-04-26 08:03:34', '2026-04-26 08:03:34'),
(179, 12, 'tr', 'Alanya Transfer', 'alanya-transfer', '', '', '', '', 'Alanya Transfer', '', '2026-04-26 08:03:34', '2026-04-26 08:03:34'),
(180, 12, 'en', 'Alanya Transfer', 'alanya-transfer', '', '', '', '', 'Alanya Transfer', '', '2026-04-26 08:03:34', '2026-04-26 08:03:34'),
(208, 13, 'de', 'Kundu Transfer', 'kundu-transfer', '', '', '', '', 'Kundu Transfer', '', '2026-04-26 11:48:36', '2026-04-26 11:48:36'),
(209, 13, 'tr', 'Kundu Transfer', 'kundu-transfer', '', '', '', '', 'Kundu Transfer', '', '2026-04-26 11:48:36', '2026-04-26 11:48:36'),
(210, 13, 'en', 'Kundu Transfer', 'kundu-transfer', '', '', '', '', 'Kundu Transfer', '', '2026-04-26 11:48:36', '2026-04-26 11:48:36'),
(211, 14, 'de', 'Lara Transfer', 'lara-transfer', '', '', '', '', 'Lara Transfer', '', '2026-04-26 11:50:40', '2026-04-26 11:50:40'),
(212, 14, 'tr', 'Lara Transfer', 'lara-transfer', '', '', '', '', 'Lara Transfer', '', '2026-04-26 11:50:40', '2026-04-26 11:50:40'),
(213, 14, 'en', 'Lara Transfer', 'lara-transfer', '', '', '', '', 'Lara Transfer', '', '2026-04-26 11:50:40', '2026-04-26 11:50:40'),
(274, 15, 'de', 'Kızılot Transfer', 'kizilot-transfer', 'ANTALYA AIRPORT (AYT)', 'KIZILOT', '', '', 'Kızılot Transfer', '', '2026-04-27 18:57:12', '2026-04-29 08:38:24'),
(275, 15, 'tr', 'Kızılot Transfer', 'kizilot-transfer', '', '', '', '', 'Kızılot Transfer', '', '2026-04-27 18:57:12', '2026-04-27 18:57:12'),
(299, 16, 'de', 'Okurcalar Transfer', 'okurcalar-transfer', '', '', '', '', 'Okurcalar Transfer', '', '2026-04-27 18:59:40', '2026-04-27 18:59:40'),
(300, 16, 'tr', 'Okurcalar Transfer', 'okurcalar-transfer', '', '', '', '', 'Okurcalar Transfer', '', '2026-04-27 18:59:40', '2026-04-27 18:59:40'),
(303, 17, 'tr', 'Avsallar Transfer', 'avsallar-transfer', '', '', '', '', 'Avsallar Transfer', '', '2026-04-28 18:02:59', '2026-04-28 18:02:59'),
(307, 18, 'tr', 'Türkler Transfer', 'turkler-transfer', '', '', '', '', 'Türkler Transfer', '', '2026-04-29 07:04:46', '2026-04-29 07:04:46');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `destination_vehicles`
--

CREATE TABLE `destination_vehicles` (
  `id` int NOT NULL,
  `destination_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `language_code` varchar(5) NOT NULL DEFAULT 'tr',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) NOT NULL DEFAULT 'TRY',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `destination_vehicles`
--

INSERT INTO `destination_vehicles` (`id`, `destination_id`, `vehicle_id`, `language_code`, `price`, `currency`, `created_at`) VALUES
(316, 10, 2, 'de', 45.00, 'EUR', '2026-04-26 07:52:31'),
(317, 10, 3, 'de', 65.00, 'EUR', '2026-04-26 07:52:31'),
(318, 10, 1, 'de', 40.00, 'EUR', '2026-04-26 07:52:31'),
(319, 10, 2, 'tr', 2400.00, 'TRY', '2026-04-26 07:52:31'),
(320, 10, 3, 'tr', 3500.00, 'TRY', '2026-04-26 07:52:31'),
(321, 10, 1, 'tr', 2200.00, 'TRY', '2026-04-26 07:52:31'),
(322, 10, 2, 'en', 55.00, 'USD', '2026-04-26 07:52:31'),
(323, 10, 3, 'en', 80.00, 'USD', '2026-04-26 07:52:31'),
(324, 10, 1, 'en', 50.00, 'USD', '2026-04-26 07:52:31'),
(613, 1, 2, 'de', 50.00, 'EUR', '2026-04-27 18:57:27'),
(614, 1, 3, 'de', 65.00, 'EUR', '2026-04-27 18:57:27'),
(615, 1, 1, 'de', 40.00, 'EUR', '2026-04-27 18:57:27'),
(616, 1, 2, 'tr', 2700.00, 'TRY', '2026-04-27 18:57:27'),
(617, 1, 3, 'tr', 3500.00, 'TRY', '2026-04-27 18:57:27'),
(618, 1, 1, 'tr', 2200.00, 'TRY', '2026-04-27 18:57:27'),
(619, 1, 2, 'en', 60.00, 'USD', '2026-04-27 18:57:27'),
(620, 1, 3, 'en', 80.00, 'USD', '2026-04-27 18:57:27'),
(621, 1, 1, 'en', 50.00, 'USD', '2026-04-27 18:57:27'),
(622, 2, 2, 'de', 50.00, 'EUR', '2026-04-27 18:57:32'),
(623, 2, 3, 'de', 65.00, 'EUR', '2026-04-27 18:57:32'),
(624, 2, 1, 'de', 40.00, 'EUR', '2026-04-27 18:57:32'),
(625, 2, 2, 'tr', 2700.00, 'TRY', '2026-04-27 18:57:32'),
(626, 2, 3, 'tr', 3500.00, 'TRY', '2026-04-27 18:57:32'),
(627, 2, 1, 'tr', 2200.00, 'TRY', '2026-04-27 18:57:32'),
(628, 2, 2, 'en', 60.00, 'USD', '2026-04-27 18:57:32'),
(629, 2, 3, 'en', 80.00, 'USD', '2026-04-27 18:57:32'),
(630, 2, 1, 'en', 50.00, 'USD', '2026-04-27 18:57:32'),
(640, 6, 2, 'de', 50.00, 'EUR', '2026-04-27 18:57:43'),
(641, 6, 3, 'de', 65.00, 'EUR', '2026-04-27 18:57:43'),
(642, 6, 1, 'de', 40.00, 'EUR', '2026-04-27 18:57:43'),
(643, 6, 2, 'tr', 2700.00, 'EUR', '2026-04-27 18:57:43'),
(644, 6, 3, 'tr', 3500.00, 'EUR', '2026-04-27 18:57:43'),
(645, 6, 1, 'tr', 2200.00, 'EUR', '2026-04-27 18:57:43'),
(646, 6, 2, 'en', 60.00, 'USD', '2026-04-27 18:57:43'),
(647, 6, 3, 'en', 80.00, 'USD', '2026-04-27 18:57:43'),
(648, 6, 1, 'en', 50.00, 'USD', '2026-04-27 18:57:43'),
(649, 7, 2, 'de', 50.00, 'EUR', '2026-04-27 18:57:50'),
(650, 7, 3, 'de', 65.00, 'EUR', '2026-04-27 18:57:50'),
(651, 7, 1, 'de', 40.00, 'EUR', '2026-04-27 18:57:50'),
(652, 7, 2, 'tr', 2700.00, 'TRY', '2026-04-27 18:57:50'),
(653, 7, 3, 'tr', 3500.00, 'TRY', '2026-04-27 18:57:50'),
(654, 7, 1, 'tr', 2200.00, 'TRY', '2026-04-27 18:57:50'),
(655, 7, 2, 'en', 60.00, 'USD', '2026-04-27 18:57:50'),
(656, 7, 3, 'en', 80.00, 'USD', '2026-04-27 18:57:50'),
(657, 7, 1, 'en', 50.00, 'USD', '2026-04-27 18:57:50'),
(658, 8, 2, 'de', 50.00, 'EUR', '2026-04-27 18:57:56'),
(659, 8, 3, 'de', 65.00, 'EUR', '2026-04-27 18:57:56'),
(660, 8, 1, 'de', 40.00, 'EUR', '2026-04-27 18:57:56'),
(661, 8, 2, 'tr', 2700.00, 'TRY', '2026-04-27 18:57:56'),
(662, 8, 3, 'tr', 3500.00, 'TRY', '2026-04-27 18:57:56'),
(663, 8, 1, 'tr', 2200.00, 'TRY', '2026-04-27 18:57:56'),
(664, 8, 2, 'en', 60.00, 'USD', '2026-04-27 18:57:56'),
(665, 8, 3, 'en', 80.00, 'USD', '2026-04-27 18:57:56'),
(666, 8, 1, 'en', 50.00, 'USD', '2026-04-27 18:57:56'),
(667, 11, 2, 'de', 60.00, 'EUR', '2026-04-27 18:58:09'),
(668, 11, 3, 'de', 75.00, 'EUR', '2026-04-27 18:58:09'),
(669, 11, 1, 'de', 50.00, 'EUR', '2026-04-27 18:58:09'),
(670, 11, 2, 'tr', 3200.00, 'TRY', '2026-04-27 18:58:09'),
(671, 11, 3, 'tr', 4200.00, 'TRY', '2026-04-27 18:58:09'),
(672, 11, 1, 'tr', 2700.00, 'TRY', '2026-04-27 18:58:09'),
(673, 11, 2, 'en', 70.00, 'USD', '2026-04-27 18:58:09'),
(674, 11, 3, 'en', 85.00, 'USD', '2026-04-27 18:58:09'),
(675, 11, 1, 'en', 60.00, 'USD', '2026-04-27 18:58:09'),
(677, 12, 2, 'de', 85.00, 'EUR', '2026-04-28 23:38:59'),
(678, 12, 3, 'de', 95.00, 'EUR', '2026-04-28 23:38:59'),
(679, 12, 1, 'de', 75.00, 'EUR', '2026-04-28 23:38:59'),
(680, 12, 2, 'tr', 4500.00, 'TRY', '2026-04-28 23:38:59'),
(681, 12, 3, 'tr', 5100.00, 'TRY', '2026-04-28 23:38:59'),
(682, 12, 1, 'tr', 4000.00, 'TRY', '2026-04-28 23:38:59'),
(683, 12, 2, 'en', 95.00, 'EUR', '2026-04-28 23:38:59'),
(684, 12, 3, 'en', 105.00, 'EUR', '2026-04-28 23:38:59'),
(685, 12, 1, 'en', 85.00, 'EUR', '2026-04-28 23:38:59'),
(688, 13, 3, 'de', 45.00, 'EUR', '2026-04-29 08:41:33'),
(689, 13, 1, 'de', 25.00, 'EUR', '2026-04-29 08:41:33'),
(690, 13, 2, 'de', 35.00, 'EUR', '2026-04-29 08:41:33'),
(691, 13, 3, 'tr', 2500.00, 'TRY', '2026-04-29 08:41:33'),
(692, 13, 1, 'tr', 1400.00, 'TRY', '2026-04-29 08:41:33'),
(693, 13, 2, 'tr', 1900.00, 'TRY', '2026-04-29 08:41:33'),
(694, 13, 3, 'en', 55.00, 'USD', '2026-04-29 08:41:33'),
(695, 13, 1, 'en', 35.00, 'USD', '2026-04-29 08:41:33'),
(696, 13, 2, 'en', 45.00, 'USD', '2026-04-29 08:41:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `faqs`
--

CREATE TABLE `faqs` (
  `id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `faq_categories`
--

CREATE TABLE `faq_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `faq_translations`
--

CREATE TABLE `faq_translations` (
  `id` int UNSIGNED NOT NULL,
  `faq_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `features`
--

CREATE TABLE `features` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `features`
--

INSERT INTO `features` (`id`, `title`, `description`, `icon`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Local Experts', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium totam.', 'bi-people-fill', 1, 1, '2026-01-21 18:27:13'),
(2, 'Safe & Secure', 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum.', 'bi-shield-check', 2, 1, '2026-01-21 18:27:13'),
(3, 'Best Prices', 'Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet consectetur adipisci velit.', 'bi-cash', 3, 1, '2026-01-21 18:27:13'),
(4, '24/7 Support', 'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam nisi.', 'bi-headset', 4, 1, '2026-01-21 18:27:13'),
(5, 'Global Destinations', 'Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae.', 'bi-geo-alt-fill', 5, 1, '2026-01-21 18:27:13'),
(6, 'Premium Experience', 'Excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim.', 'bi-star-fill', 6, 1, '2026-01-21 18:27:13');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `feature_translations`
--

CREATE TABLE `feature_translations` (
  `id` int UNSIGNED NOT NULL,
  `feature_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `gallery`
--

CREATE TABLE `gallery` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `gallery_categories`
--

CREATE TABLE `gallery_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `hotels`
--

CREATE TABLE `hotels` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance_km` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `address`, `phone`, `distance_km`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Sunis Kumkoy Beach', NULL, NULL, 72.00, 1, 0, '2026-04-27 17:14:33', '2026-04-28 20:30:45'),
(2, 'Sunis Evren Beach', NULL, NULL, 70.00, 1, 0, '2026-04-27 17:16:34', '2026-04-28 20:30:49'),
(3, 'Sunis Elita Beach', 'Kızılağaç', NULL, 85.00, 1, 0, '2026-04-27 17:16:51', NULL),
(4, 'Riolavitas Resort', 'Sorgun', NULL, 74.00, 1, 0, '2026-04-27 17:17:10', NULL),
(5, 'Royal Atlantis Icon', 'Gündoğdu', NULL, 70.00, 1, 0, '2026-04-27 22:17:01', '2026-04-29 08:39:13'),
(7, 'Aska Just In Beach', NULL, NULL, NULL, 1, 0, '2026-04-28 18:05:44', NULL),
(8, 'Lilyum Hotel', NULL, NULL, NULL, 1, 0, '2026-04-28 18:47:00', NULL),
(9, 'Delphin Be Grand', NULL, NULL, NULL, 1, 0, '2026-04-28 23:21:24', NULL),
(10, 'Lago Hotel', NULL, NULL, NULL, 1, 0, '2026-04-29 06:58:23', NULL),
(11, 'Eftalia Splash', NULL, NULL, NULL, 1, 0, '2026-04-29 07:14:02', NULL),
(12, 'Delphin Palace', NULL, NULL, NULL, 1, 0, '2026-04-29 07:31:22', NULL),
(13, 'Port River Hotel', NULL, NULL, NULL, 1, 0, '2026-04-29 07:42:09', NULL),
(14, 'Grand Park Lara', NULL, NULL, NULL, 1, 0, '2026-04-29 08:44:02', NULL),
(15, 'Aydınbey Kings Palace', NULL, NULL, NULL, 1, 0, '2026-04-29 08:49:45', NULL),
(16, 'The Sense Deluxe', NULL, NULL, NULL, 1, 0, '2026-04-29 08:53:25', NULL),
(17, 'Kleopatra Royal Palm', NULL, NULL, NULL, 1, 0, '2026-04-29 09:03:05', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `languages`
--

CREATE TABLE `languages` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `is_rtl` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `languages`
--

INSERT INTO `languages` (`id`, `code`, `name`, `native_name`, `flag`, `is_default`, `is_active`, `is_rtl`, `sort_order`, `created_at`) VALUES
(1, 'tr', 'Turkish', 'Türkçe', '🇹🇷', 1, 1, 0, 1, '2026-01-17 22:22:32'),
(2, 'en', 'English', 'English', '🇬🇧', 0, 1, 0, 2, '2026-01-17 22:22:32'),
(3, 'de', 'German', 'Deutsch', 'DE', 0, 1, 0, 0, '2026-01-27 19:35:16');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `media`
--

CREATE TABLE `media` (
  `id` int NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL DEFAULT '0',
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `folder` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `uploaded_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `media`
--

INSERT INTO `media` (`id`, `filename`, `original_name`, `file_path`, `file_type`, `mime_type`, `file_size`, `width`, `height`, `alt_text`, `title`, `folder`, `uploaded_by`, `created_at`) VALUES
(1, '1769353749_a7a5d089718ac8d5.webp', 'destination-10.webp', 'general/1769353749_a7a5d089718ac8d5.webp', 'image', 'image/webp', 137648, 1024, 683, NULL, NULL, 'general', 1, '2026-01-25 15:09:08'),
(2, '1769354074_ac9df981f7f01633.mp4', 'video-2.mp4', 'general/1769354074_ac9df981f7f01633.mp4', 'video', 'video/mp4', 6234342, NULL, NULL, NULL, NULL, 'general', 1, '2026-01-25 15:14:32'),
(3, '1769542314_f74c434ba115caef.png', 'volkswagen.png', 'general/1769542314_f74c434ba115caef.png', 'image', 'image/png', 505296, 1302, 622, NULL, NULL, 'general', 1, '2026-01-27 19:31:54'),
(4, '1769543525_f883265a7fcb7bdd.png', 'c2.png', 'general/1769543525_f883265a7fcb7bdd.png', 'image', 'image/png', 87000, 317, 190, NULL, NULL, 'general', 1, '2026-01-27 19:52:05'),
(5, '1769548145_0b146a91be92ee71.webp', 'tour-22.webp', 'general/1769548145_0b146a91be92ee71.webp', 'image', 'image/webp', 121238, 1024, 683, NULL, NULL, 'general', 1, '2026-01-27 21:09:04'),
(6, '1769548779_8597a2056acace9b.webp', 'destination-2.webp', 'general/1769548779_8597a2056acace9b.webp', 'image', 'image/webp', 87720, 1024, 683, NULL, NULL, 'general', 1, '2026-01-27 21:19:39'),
(7, '1769548794_76d96fe1935ff2f1.webp', 'destination-4.webp', 'general/1769548794_76d96fe1935ff2f1.webp', 'image', 'image/webp', 108696, 1024, 683, NULL, NULL, 'general', 1, '2026-01-27 21:19:53'),
(8, '1769551034_e479d68bf6e127ce.webp', 'destination-1.webp', 'general/1769551034_e479d68bf6e127ce.webp', 'image', 'image/webp', 180132, 1024, 683, NULL, NULL, 'general', 1, '2026-01-27 21:57:13'),
(9, '1769551064_43aaee0999f03861.webp', 'destination-5.webp', 'general/1769551064_43aaee0999f03861.webp', 'image', 'image/webp', 93248, 1024, 683, NULL, NULL, 'general', 1, '2026-01-27 21:57:44'),
(10, '1769727428_3a80dfbcf2594f1d.png', 'minibus-emoji-clipart-xl.png', 'general/1769727428_3a80dfbcf2594f1d.png', 'image', 'image/png', 14470, 1920, 1920, NULL, NULL, 'general', 1, '2026-01-29 22:57:05'),
(11, '1771021316_3280340d44294aef.png', 'Ekran görüntüsü 2026-02-14 012150.png', 'general/1771021316_3280340d44294aef.png', 'image', 'image/png', 373768, 844, 357, NULL, NULL, 'general', 2, '2026-02-13 22:21:52'),
(20, '1777419485_706f9d0d48995f5c.png', '826-8261315_sprinter-mercedes-benz-sprinter-png.png', 'general/1777419485_706f9d0d48995f5c.png', 'image', 'image/png', 95276, 706, 317, NULL, NULL, 'general', 2, '2026-04-28 23:38:05'),
(21, '1777446325_d560d4fe2e513797.jpg', 'images (1).jpg', 'general/1777446325_d560d4fe2e513797.jpg', 'image', 'image/jpeg', 17692, 315, 190, NULL, NULL, 'general', 2, '2026-04-29 07:05:25');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `menus`
--

CREATE TABLE `menus` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'header',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `menus`
--

INSERT INTO `menus` (`id`, `name`, `slug`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Ana Menü', 'ana-menu', 'header', '2026-01-17 22:22:32', '2026-01-17 22:22:32'),
(2, 'Footer Menü', 'footer-menu', 'footer', '2026-01-17 22:22:32', '2026-01-17 22:22:32');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int UNSIGNED NOT NULL,
  `menu_id` int UNSIGNED NOT NULL,
  `parent_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` enum('_self','_blank') COLLATE utf8mb4_unicode_ci DEFAULT '_self',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `title`, `url`, `target`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Ana Sayfa', '/', '_self', NULL, 1, 1, '2026-01-17 22:22:32', '2026-01-17 22:22:32'),
(2, 1, NULL, 'Turlar', '/turlar', '_self', NULL, 2, 1, '2026-01-17 22:22:32', '2026-01-17 22:22:32'),
(3, 1, NULL, 'Transferler', '/transferler', '_self', NULL, 3, 1, '2026-01-17 22:22:32', '2026-01-27 21:13:13'),
(5, 1, NULL, 'İletişim', '/iletisim', '_self', NULL, 5, 1, '2026-01-17 22:22:32', '2026-01-17 22:22:32'),
(6, 2, NULL, 'Anasayfa', '/', '_self', NULL, 1, 1, '2026-04-28 23:50:29', '2026-04-28 23:50:29'),
(7, 2, NULL, 'Anasayfa', '/', '_self', NULL, 1, 1, '2026-04-28 23:50:32', '2026-04-28 23:50:32'),
(8, 2, NULL, 'Anasayfa', '/', '_self', NULL, 1, 1, '2026-04-28 23:50:33', '2026-04-28 23:50:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `menu_item_translations`
--

CREATE TABLE `menu_item_translations` (
  `id` int UNSIGNED NOT NULL,
  `menu_item_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `menu_item_translations`
--

INSERT INTO `menu_item_translations` (`id`, `menu_item_id`, `language_code`, `title`, `url`, `created_at`, `updated_at`) VALUES
(1, 3, 'de', 'Transfers', '/transfers', '2026-01-27 21:13:13', '2026-01-27 21:13:13'),
(2, 3, 'en', 'Transfers', '/transfers', '2026-01-27 21:13:13', '2026-01-27 21:13:13'),
(5, 2, 'de', 'turlar-de', 'turlar-de', '2026-02-01 19:28:24', '2026-02-01 19:28:24'),
(7, 2, 'en', 'tours', 'tours', '2026-02-01 21:52:53', '2026-02-01 21:52:53'),
(8, 1, 'de', 'De Home', '/', '2026-02-01 22:56:34', '2026-02-01 22:56:34'),
(9, 1, 'en', 'Home', '/', '2026-02-01 22:56:34', '2026-02-01 22:56:34'),
(12, 6, 'de', 'Home Page', '/', '2026-04-28 23:50:29', '2026-04-28 23:50:29'),
(13, 6, 'en', 'Home Page', '/', '2026-04-28 23:50:29', '2026-04-28 23:50:29'),
(14, 7, 'de', 'Home Page', '/', '2026-04-28 23:50:32', '2026-04-28 23:50:32'),
(15, 7, 'en', 'Home Page', '/', '2026-04-28 23:50:32', '2026-04-28 23:50:32'),
(16, 8, 'de', 'Home Page', '/', '2026-04-28 23:50:33', '2026-04-28 23:50:33'),
(17, 8, 'en', 'Home Page', '/', '2026-04-28 23:50:33', '2026-04-28 23:50:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `outsource_partners`
--

CREATE TABLE `outsource_partners` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `outsource_partners`
--

INSERT INTO `outsource_partners` (`id`, `name`, `phone`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(10, 'Sezer BOZ', '+90 534 244 97 48', NULL, 1, '2026-04-28 23:06:09', NULL),
(11, 'Ali Seydi Tuluk', '5320645407', NULL, 1, '2026-04-29 09:53:47', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `pages`
--

CREATE TABLE `pages` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `is_homepage` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `author_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `excerpt`, `featured_image`, `template`, `meta_title`, `meta_description`, `meta_keywords`, `status`, `is_homepage`, `sort_order`, `author_id`, `created_at`, `updated_at`) VALUES
(1, 'Ana Sayfa', 'ana-sayfa', NULL, NULL, NULL, 'default', 'Ana Sayfa', '', '', 'published', 1, 1, NULL, '2026-01-17 22:22:32', '2026-01-25 15:14:39'),
(2, 'Hakkımızda', 'hakkimizda', NULL, NULL, NULL, 'default', NULL, NULL, NULL, 'published', 0, 2, NULL, '2026-01-17 22:22:32', '2026-01-17 22:22:32'),
(3, 'İletişim', 'iletisim', NULL, NULL, NULL, 'default', NULL, NULL, NULL, 'published', 0, 3, NULL, '2026-01-17 22:22:32', '2026-01-17 22:22:32');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `page_settings`
--

CREATE TABLE `page_settings` (
  `id` int NOT NULL,
  `page_key` varchar(50) NOT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `features_visible` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `page_settings`
--

INSERT INTO `page_settings` (`id`, `page_key`, `background_image`, `created_at`, `updated_at`, `features_visible`) VALUES
(1, 'destinations', 'general/1769548145_0b146a91be92ee71.webp', '2026-01-27 21:06:50', '2026-01-29 22:41:03', 1),
(56, 'destination_detail', NULL, '2026-01-29 20:30:02', '2026-01-29 20:30:02', 1),
(217, 'tours', 'general/1769548145_0b146a91be92ee71.webp', '2026-02-01 17:55:17', '2026-02-01 19:27:39', 1),
(218, 'tour_detail', NULL, '2026-02-01 17:55:17', '2026-02-01 17:55:17', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `page_setting_translations`
--

CREATE TABLE `page_setting_translations` (
  `id` int NOT NULL,
  `page_setting_id` int NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `subtitle` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `page_setting_translations`
--

INSERT INTO `page_setting_translations` (`id`, `page_setting_id`, `language_code`, `title`, `slug`, `subtitle`) VALUES
(1, 1, 'de', 'Transfers', 'transfers', ''),
(2, 1, 'tr', 'Transferler', 'transferler', ''),
(3, 1, 'en', 'Transfers', 'transfers', ''),
(7, 217, 'de', 'Turlar de', 'turlar-de', 'açıklama de'),
(8, 217, 'tr', 'Turlar', 'turlar', 'turlar'),
(9, 217, 'en', 'tours', 'tours', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `page_translations`
--

CREATE TABLE `page_translations` (
  `id` int UNSIGNED NOT NULL,
  `page_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sections`
--

CREATE TABLE `sections` (
  `id` int UNSIGNED NOT NULL,
  `page_id` int UNSIGNED DEFAULT NULL,
  `section_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'custom',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `settings` json DEFAULT NULL,
  `background_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `sections`
--

INSERT INTO `sections` (`id`, `page_id`, `section_key`, `section_type`, `title`, `subtitle`, `content`, `settings`, `background_image`, `background_video`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 1, 'hero', 'hero', 'Slider Ana Başlık', 'Slider Alt Başlık', '', '{\"form_title\": \"Tatili Planla\", \"button1_url\": \"#\", \"button2_url\": \"#\", \"button1_text\": \"Tura Başla\", \"button2_text\": \"Turları Keşfet\", \"label_adults\": \"Yetişkin \", \"label_return\": \"Dönüş Tarihi\", \"label_children\": \"Çocuk\", \"label_departure\": \"Gidiş Tarihi\", \"label_tour_type\": \"Tur Tipi\", \"form_button_text\": \"Buton Metni Kaydet\", \"label_destination\": \"Hedef\", \"show_booking_form\": \"1\", \"placeholder_tour_type\": \"Tur Tipini Seç\", \"placeholder_destination\": \"Hedefini Seç\"}', '', 'general/1769354074_ac9df981f7f01633.mp4', 1, 1, '2026-01-21 18:27:13', '2026-01-27 16:47:57'),
(3, 1, 'why_us', 'why_us', 'Ana Başlık', '', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>', '{\"image\": \"general/1769353749_a7a5d089718ac8d5.webp\", \"stats\": [{\"label\": \"Happy Travelers\", \"number\": \"1200\"}, {\"label\": \"Countries Covered\", \"number\": \"85\"}, {\"label\": \"Years Experience\", \"number\": \"15\"}], \"experience_text\": \"Years of Excellence\", \"experience_badge\": \"15+\"}', '', '', 2, 0, '2026-01-21 18:27:13', '2026-02-01 15:55:15'),
(4, 1, 'featured_destinations', 'destinations', 'Featured Destinations', '', '', '{\"limit\": \"4\", \"show_featured_only\": \"1\"}', '', '', 3, 1, '2026-01-21 18:27:13', '2026-01-25 15:14:40'),
(5, 1, 'featured_tours', 'tours', 'Featured Tours', '', '', '{\"limit\": \"6\", \"view_all_url\": \"/turlar\", \"show_view_all\": \"1\", \"show_featured_only\": \"1\"}', '', '', 4, 1, '2026-01-21 18:27:13', '2026-01-25 15:14:40'),
(6, 1, 'testimonials', 'testimonials', 'Testimonials', '', '', '{\"limit\": \"5\", \"autoplay_delay\": \"5000\"}', '', '', 5, 0, '2026-01-21 18:27:13', '2026-02-13 21:47:18'),
(7, 1, 'cta', 'cta', 'Discover Your Next Adventure', 'Limited Time Offer', 'Unlock incredible destinations with our specially curated travel packages. From exotic beaches to mountain peaks, your perfect getaway awaits.', '{\"image\": \"general/1769353749_a7a5d089718ac8d5.webp\", \"phone\": \"+1 (555) 123-456\", \"button1_url\": \"/destinasyonlar\", \"button2_url\": \"/turlar\", \"button1_text\": \"Explore Now\", \"button2_text\": \"View Deals\", \"contact_label\": \"Need help choosing?\"}', '', '', 6, 0, '2026-01-21 18:27:13', '2026-02-13 21:47:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `section_translations`
--

CREATE TABLE `section_translations` (
  `id` int UNSIGNED NOT NULL,
  `section_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int UNSIGNED NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('text','textarea','boolean','number','json','image') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `setting_group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'SunLine Vip Transfer', 'text', 'general', '2026-01-17 22:22:32', '2026-04-28 21:09:47'),
(2, 'site_description', 'Tur ve Seyahat Firması', 'textarea', 'general', '2026-01-17 22:22:32', '2026-01-17 22:22:32'),
(3, 'contact_email', 'contact@sunlineviptransfer.com', 'text', 'contact', '2026-01-17 22:22:32', '2026-04-27 18:18:52'),
(4, 'contact_phone', '+905069874707', 'text', 'contact', '2026-01-17 22:22:32', '2026-04-27 18:11:35'),
(5, 'contact_address', 'Antalya, Türkiye', 'textarea', 'contact', '2026-01-17 22:22:32', '2026-04-27 18:09:19'),
(6, 'site_logo', 'site_logo_1777313602.png', 'image', 'general', '2026-04-27 18:02:09', '2026-04-27 18:13:22'),
(7, 'site_favicon', '', 'image', 'general', '2026-04-27 18:02:09', '2026-04-27 18:02:09');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sliders`
--

CREATE TABLE `sliders` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button2_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button2_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `overlay_color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_position` enum('left','center','right') COLLATE utf8mb4_unicode_ci DEFAULT 'left',
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'home',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `slider_translations`
--

CREATE TABLE `slider_translations` (
  `id` int UNSIGNED NOT NULL,
  `slider_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` text COLLATE utf8mb4_unicode_ci,
  `button_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button2_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `terms_translations`
--

CREATE TABLE `terms_translations` (
  `id` int NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `checkbox_text` varchar(255) DEFAULT NULL,
  `content` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `terms_translations`
--

INSERT INTO `terms_translations` (`id`, `language_code`, `title`, `checkbox_text`, `content`, `created_at`, `updated_at`) VALUES
(1, 'de', '', '', '', '2026-01-29 23:21:56', '2026-01-29 23:21:56'),
(2, 'tr', 'TR Sözleşme', 'TR Sözleşme checkbox metin', 'TR Sözleşme içerik', '2026-01-29 23:21:56', '2026-01-29 23:21:56'),
(3, 'en', '', '', '', '2026-01-29 23:21:57', '2026-01-29 23:21:57');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int UNSIGNED NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint DEFAULT '5',
  `tour_id` int UNSIGNED DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_approved` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `testimonials`
--

INSERT INTO `testimonials` (`id`, `customer_name`, `customer_title`, `customer_image`, `content`, `rating`, `tour_id`, `is_featured`, `is_approved`, `sort_order`, `created_at`) VALUES
(1, 'Saul Goodman', 'Ceo & Founder', 'assets/img/person/person-m-9.webp', 'Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et.', 5, NULL, 1, 1, 1, '2026-01-21 18:27:13'),
(2, 'Sara Wilsson', 'Designer', 'assets/img/person/person-f-5.webp', 'Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid malis quorum velit fore eram velit sunt aliqua noster fugiat.', 5, NULL, 1, 1, 2, '2026-01-21 18:27:13'),
(3, 'Jena Karlis', 'Store Owner', 'assets/img/person/person-f-12.webp', 'Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute.', 5, NULL, 1, 1, 3, '2026-01-21 18:27:13'),
(4, 'Matt Brandon', 'Freelancer', 'assets/img/person/person-m-12.webp', 'Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat dolor enim duis veniam ipsum anim magna sunt elit fore.', 4, NULL, 1, 1, 4, '2026-01-21 18:27:13'),
(5, 'John Larson', 'Entrepreneur', 'assets/img/person/person-m-13.webp', 'Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam sunt culpa nulla illum cillum fugiat legam esse.', 5, NULL, 1, 1, 5, '2026-01-21 18:27:13');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `testimonial_translations`
--

CREATE TABLE `testimonial_translations` (
  `id` int UNSIGNED NOT NULL,
  `testimonial_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `customer_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tours`
--

CREATE TABLE `tours` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `destination_id` int UNSIGNED DEFAULT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `duration_days` int DEFAULT '1',
  `duration_nights` int DEFAULT '0',
  `group_size_min` int DEFAULT '1',
  `group_size_max` int DEFAULT '10',
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `highlights` json DEFAULT NULL,
  `included` json DEFAULT NULL,
  `excluded` json DEFAULT NULL,
  `itinerary` json DEFAULT NULL,
  `badge` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `difficulty_level` enum('easy','moderate','challenging','extreme') COLLATE utf8mb4_unicode_ci DEFAULT 'moderate',
  `rating` decimal(2,1) DEFAULT '0.0',
  `review_count` int DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_bestseller` tinyint(1) DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `tours`
--

INSERT INTO `tours` (`id`, `title`, `slug`, `description`, `content`, `image`, `featured_image`, `gallery`, `destination_id`, `category_id`, `duration_days`, `duration_nights`, `group_size_min`, `group_size_max`, `price`, `sale_price`, `currency`, `highlights`, `included`, `excluded`, `itinerary`, `badge`, `difficulty_level`, `rating`, `review_count`, `is_featured`, `is_bestseller`, `meta_title`, `meta_description`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(9, 'Land Of Legends', 'land-of-legends', '', '', 'general/1769551064_43aaee0999f03861.webp', NULL, NULL, NULL, NULL, 1, 0, 1, 10, 0.00, NULL, 'USD', NULL, NULL, NULL, NULL, NULL, 'moderate', 0.0, 0, 0, 0, NULL, NULL, 'published', 0, '2026-02-01 19:11:02', '2026-02-01 19:11:02');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tour_categories`
--

CREATE TABLE `tour_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tour_translations`
--

CREATE TABLE `tour_translations` (
  `id` int UNSIGNED NOT NULL,
  `tour_id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `highlights` json DEFAULT NULL,
  `included` json DEFAULT NULL,
  `excluded` json DEFAULT NULL,
  `itinerary` json DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `tour_translations`
--

INSERT INTO `tour_translations` (`id`, `tour_id`, `language_code`, `title`, `slug`, `description`, `content`, `highlights`, `included`, `excluded`, `itinerary`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(7, 9, 'de', 'Land Of Legends', 'land-of-legends', '', '', NULL, NULL, NULL, NULL, '', '', '2026-02-01 19:11:02', '2026-02-01 19:11:02'),
(8, 9, 'en', 'Land Of Legends', 'land-of-legends', '', '', NULL, NULL, NULL, NULL, '', '', '2026-02-01 19:11:02', '2026-02-01 19:11:02'),
(9, 9, 'RU', 'Land Of Legends', 'Land Of Legends', '', '', NULL, NULL, NULL, NULL, '', '', '2026-02-01 19:11:02', '2026-02-01 19:11:02');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tour_vehicles`
--

CREATE TABLE `tour_vehicles` (
  `id` int NOT NULL,
  `tour_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `language_code` varchar(5) NOT NULL DEFAULT 'tr',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `tour_vehicles`
--

INSERT INTO `tour_vehicles` (`id`, `tour_id`, `vehicle_id`, `language_code`, `price`, `currency`, `created_at`) VALUES
(13, 9, 1, 'de', 0.00, 'EUR', '2026-04-29 07:27:53'),
(14, 9, 2, 'de', 0.00, 'EUR', '2026-04-29 07:27:53'),
(15, 9, 3, 'de', 0.00, 'EUR', '2026-04-29 07:27:53'),
(16, 9, 1, 'tr', 0.00, 'EUR', '2026-04-29 07:27:53'),
(17, 9, 3, 'tr', 0.00, 'EUR', '2026-04-29 07:27:53'),
(18, 9, 1, 'en', 0.00, 'EUR', '2026-04-29 07:27:53'),
(19, 9, 3, 'en', 0.00, 'EUR', '2026-04-29 07:27:53');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `transfer_detail_translations`
--

CREATE TABLE `transfer_detail_translations` (
  `id` int NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `available_vehicles` varchar(255) DEFAULT NULL,
  `choose_vehicle` varchar(255) DEFAULT NULL,
  `passengers` varchar(100) DEFAULT NULL,
  `luggage` varchar(100) DEFAULT NULL,
  `book_now` varchar(100) DEFAULT NULL,
  `transfer_features` varchar(255) DEFAULT NULL,
  `what_we_offer` varchar(255) DEFAULT NULL,
  `safe_travel` varchar(255) DEFAULT NULL,
  `safe_travel_desc` text,
  `punctual` varchar(255) DEFAULT NULL,
  `punctual_desc` text,
  `professional_drivers` varchar(255) DEFAULT NULL,
  `professional_drivers_desc` text,
  `support_24_7` varchar(255) DEFAULT NULL,
  `support_24_7_desc` text,
  `make_reservation` varchar(255) DEFAULT NULL,
  `fill_form` varchar(255) DEFAULT NULL,
  `pickup_date` varchar(100) DEFAULT NULL,
  `pickup_time` varchar(100) DEFAULT NULL,
  `person` varchar(100) DEFAULT NULL,
  `vehicle_type` varchar(100) DEFAULT NULL,
  `select_vehicle` varchar(100) DEFAULT NULL,
  `pickup_address` varchar(255) DEFAULT NULL,
  `pickup_address_placeholder` varchar(255) DEFAULT NULL,
  `notes` varchar(100) DEFAULT NULL,
  `notes_placeholder` varchar(255) DEFAULT NULL,
  `continue_booking` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `transfer_route` varchar(255) DEFAULT NULL,
  `gallery` varchar(100) DEFAULT NULL,
  `gallery_desc` varchar(255) DEFAULT NULL,
  `ready_to_book` varchar(255) DEFAULT NULL,
  `contact_us_help` text,
  `contact_us` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `change_vehicle` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `flight_date` varchar(255) DEFAULT NULL,
  `flight_time` varchar(255) DEFAULT NULL,
  `flight_number` varchar(255) DEFAULT NULL,
  `adults_count` varchar(255) DEFAULT NULL,
  `child_seat` varchar(255) DEFAULT NULL,
  `yes_no` varchar(255) DEFAULT NULL,
  `hotel_address` varchar(255) DEFAULT NULL,
  `return_transfer` varchar(255) DEFAULT NULL,
  `return_flight_date` varchar(255) DEFAULT NULL,
  `return_flight_time` varchar(255) DEFAULT NULL,
  `return_flight_number` varchar(255) DEFAULT NULL,
  `return_pickup_time` varchar(255) DEFAULT NULL,
  `return_hotel_address` varchar(255) DEFAULT NULL,
  `children_count` varchar(255) DEFAULT NULL,
  `transfer_info_title` varchar(255) DEFAULT NULL,
  `total_price` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `transfer_detail_translations`
--

INSERT INTO `transfer_detail_translations` (`id`, `language_code`, `available_vehicles`, `choose_vehicle`, `passengers`, `luggage`, `book_now`, `transfer_features`, `what_we_offer`, `safe_travel`, `safe_travel_desc`, `punctual`, `punctual_desc`, `professional_drivers`, `professional_drivers_desc`, `support_24_7`, `support_24_7_desc`, `make_reservation`, `fill_form`, `pickup_date`, `pickup_time`, `person`, `vehicle_type`, `select_vehicle`, `pickup_address`, `pickup_address_placeholder`, `notes`, `notes_placeholder`, `continue_booking`, `location`, `transfer_route`, `gallery`, `gallery_desc`, `ready_to_book`, `contact_us_help`, `contact_us`, `created_at`, `updated_at`, `change_vehicle`, `full_name`, `email`, `phone`, `flight_date`, `flight_time`, `flight_number`, `adults_count`, `child_seat`, `yes_no`, `hotel_address`, `return_transfer`, `return_flight_date`, `return_flight_time`, `return_flight_number`, `return_pickup_time`, `return_hotel_address`, `children_count`, `transfer_info_title`, `total_price`) VALUES
(1, 'de', '', 'De Araç Seçin', 'De Yolcu', 'De Bagaj', 'De Rez Yap', 'DE Transfer Özellikleri', 'de Özellikler Alt Başlık', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'de Rezervasyon Yap', 'deForm Alt Başlık', 'de Alış Tarihi', 'de Alış Saati', 'de Kişi', 'de Araç Tipi', 'de Araç Seçin', 'de Alış Adresi', 'deAlış Adresi placeholder', 'de Notlar', 'de Notlar place holder', 'de Devam Et', 'de Konum', 'de Transfer Güzergahı', 'de Galeri', 'de Galeri Açıklaması', 'de Rezervasyon Hazır mısınız?', 'de İletişim Yardım Metni', 'de İletişime Geç', '2026-01-29 20:37:59', '2026-01-29 20:46:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'tr', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2026-01-29 20:37:59', '2026-01-29 20:46:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'en', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2026-01-29 20:37:59', '2026-01-29 20:37:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `transfer_features`
--

CREATE TABLE `transfer_features` (
  `id` int NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT 'bi-check-circle',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `transfer_features`
--

INSERT INTO `transfer_features` (`id`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(9, 'bi-compass', 0, 1, '2026-01-29 22:41:03', '2026-01-29 22:41:03'),
(10, 'bi-map', 1, 1, '2026-01-29 22:41:03', '2026-01-29 22:41:03'),
(11, 'bi-airplane', 2, 1, '2026-01-29 22:41:04', '2026-01-29 22:41:04'),
(12, 'bi-hospital', 3, 1, '2026-01-29 22:41:04', '2026-01-29 22:41:04');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `transfer_feature_translations`
--

CREATE TABLE `transfer_feature_translations` (
  `id` int NOT NULL,
  `feature_id` int NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `transfer_feature_translations`
--

INSERT INTO `transfer_feature_translations` (`id`, `feature_id`, `language_code`, `title`, `description`) VALUES
(25, 9, 'de', 'De Özellik', 'de açıklama'),
(26, 9, 'tr', 'Başlık', 'açıklama'),
(27, 9, 'en', 'En Başlık', 'en açıklama'),
(28, 10, 'de', 'De Başlık 2', 'de açıklama2'),
(29, 10, 'tr', 'TR Başlık 2', 'tr açıklama2'),
(30, 10, 'en', 'EN Başlık 2', 'en açıklama2'),
(31, 11, 'de', 'Başlık 3 de', 'açıklama 3 de'),
(32, 11, 'tr', 'Başlık 3 tr', 'açıklama 3 tr'),
(33, 11, 'en', 'Başlık 3 en', 'açıklama 3 en'),
(34, 12, 'de', 'başlık 4 de', 'açıklama 4 de'),
(35, 12, 'tr', 'başlık 4 tr', 'açıklama 4 tr'),
(36, 12, 'en', 'başlık 4 en', 'açıklama 4 en');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `translations`
--

CREATE TABLE `translations` (
  `id` int UNSIGNED NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trans_group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trans_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trans_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','editor','author') COLLATE utf8mb4_unicode_ci DEFAULT 'editor',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `avatar`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$TE/2AViqJiIZHyIwRQ1XjeP87NcWnj5XoI1wo3S4pmDxa/D7TVao2', 'Administrator', NULL, 'admin', 1, '2026-02-13 19:37:56', '2026-01-17 22:22:32', '2026-02-13 19:37:56'),
(2, 'sezer', 'sezerbz@gmail.com', '$2y$10$0uc7urfsSX3OcTqGIFgnj.DaMAlSfpsExjlKFprdbQgaGBxHoyRi6', 'Sezer Boz', NULL, 'admin', 1, '2026-04-29 10:16:39', '2026-02-13 22:09:08', '2026-04-29 10:16:39');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `capacity` int NOT NULL DEFAULT '4',
  `luggage_capacity` int DEFAULT '2',
  `child_seat_capacity` int DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `services` text,
  `description` text,
  `price_per_km` decimal(10,2) DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `vehicles`
--

INSERT INTO `vehicles` (`id`, `brand`, `model`, `capacity`, `luggage_capacity`, `child_seat_capacity`, `image`, `services`, `description`, `price_per_km`, `base_price`, `is_featured`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Transporter', '.', 6, 2, 1, 'general/1777446325_d560d4fe2e513797.jpg', '[\"33\",\"34\",\"35\",\"36\",\"37\",\"38\",\"39\",\"40\",\"41\",\"42\",\"43\"]', '', NULL, NULL, 0, 1, 0, '2026-01-27 19:32:02', '2026-04-29 07:24:57'),
(2, 'Vip Mercedes Vito - Maybach', 'Vito', 6, 2, 2, 'general/1769543525_f883265a7fcb7bdd.png', '[\"33\",\"34\",\"35\",\"36\",\"37\",\"38\",\"39\",\"40\",\"41\",\"42\",\"43\"]', '', NULL, NULL, 0, 1, 0, '2026-01-27 19:52:11', '2026-04-26 07:03:34'),
(3, 'Sprinter/Crafter', '.', 17, 17, 4, 'general/1777419485_706f9d0d48995f5c.png', '[\"33\",\"34\",\"35\",\"36\",\"37\",\"38\",\"39\",\"40\",\"41\",\"42\",\"43\"]', '', NULL, NULL, 0, 1, 0, '2026-01-29 22:57:11', '2026-04-29 07:25:14');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `vehicle_services`
--

CREATE TABLE `vehicle_services` (
  `id` int NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT 'bi-check-circle',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `vehicle_services`
--

INSERT INTO `vehicle_services` (`id`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(33, 'bi-wifi', 0, 1, '2026-02-01 22:39:23', '2026-02-01 22:39:23'),
(34, 'bi-snow', 1, 1, '2026-02-01 22:39:23', '2026-02-01 22:39:23'),
(35, 'bi-droplet', 2, 1, '2026-02-01 22:39:24', '2026-02-01 22:39:24'),
(36, 'bi-plug', 3, 1, '2026-02-01 22:39:24', '2026-02-01 22:39:24'),
(37, 'bi-tv', 4, 1, '2026-02-01 22:39:24', '2026-02-01 22:39:24'),
(38, 'bi-person-arms-up', 5, 1, '2026-02-01 22:39:24', '2026-02-01 22:39:24'),
(39, 'bi-star', 6, 1, '2026-02-01 22:39:24', '2026-02-01 22:39:24'),
(40, 'bi-cup-straw', 7, 1, '2026-02-01 22:39:25', '2026-02-01 22:39:25'),
(41, 'bi-newspaper', 8, 1, '2026-02-01 22:39:25', '2026-02-01 22:39:25'),
(42, 'bi-person-badge', 9, 1, '2026-02-01 22:39:25', '2026-02-01 22:39:25'),
(43, 'bi-cash', 10, 1, '2026-02-01 22:39:25', '2026-02-01 22:39:25');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `vehicle_service_translations`
--

CREATE TABLE `vehicle_service_translations` (
  `id` int NOT NULL,
  `service_id` int NOT NULL,
  `language_code` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `vehicle_service_translations`
--

INSERT INTO `vehicle_service_translations` (`id`, `service_id`, `language_code`, `name`) VALUES
(66, 33, 'tr', 'Wi-Fi'),
(67, 33, 'en', 'Wi-Fi'),
(68, 34, 'tr', 'Klima'),
(69, 34, 'en', 'Air Conditioning'),
(70, 34, 'de', 'de klima'),
(71, 35, 'tr', 'Su İkramı'),
(72, 35, 'en', 'Water'),
(73, 35, 'de', 'de su'),
(74, 36, 'tr', 'Şarj Soketi'),
(75, 36, 'en', 'Charger'),
(76, 37, 'tr', 'TV/Ekran'),
(77, 37, 'en', 'TV/Screen'),
(78, 38, 'tr', 'Çocuk Koltuğu'),
(79, 38, 'en', 'Child Seat'),
(80, 39, 'tr', 'Deri Koltuk'),
(81, 39, 'en', 'Leather Seats'),
(82, 40, 'tr', 'Minibar'),
(83, 40, 'en', 'Minibar'),
(84, 41, 'tr', 'Gazete/Dergi'),
(85, 41, 'en', 'Newspaper'),
(86, 42, 'tr', 'Karşılama Hizmeti'),
(87, 42, 'en', 'Meet & Greet'),
(88, 43, 'tr', 'Araçta Ödeme'),
(89, 43, 'en', 'Payment in Vehicle');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `blog_post_translations`
--
ALTER TABLE `blog_post_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_lang` (`post_id`,`language_code`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_number` (`booking_number`),
  ADD KEY `idx_booking_status` (`booking_status`),
  ADD KEY `idx_booking_type` (`booking_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_tour_id` (`tour_id`),
  ADD KEY `idx_destination_id` (`destination_id`),
  ADD KEY `idx_vehicle_id` (`vehicle_id`);

--
-- Tablo için indeksler `booking_alert_translations`
--
ALTER TABLE `booking_alert_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `language_code` (`language_code`);

--
-- Tablo için indeksler `booking_passengers`
--
ALTER TABLE `booking_passengers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Tablo için indeksler `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `destination_translations`
--
ALTER TABLE `destination_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dest_lang` (`destination_id`,`language_code`),
  ADD KEY `idx_destination_id` (`destination_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `destination_vehicles`
--
ALTER TABLE `destination_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dest_vehicle_lang` (`destination_id`,`vehicle_id`,`language_code`);

--
-- Tablo için indeksler `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `faq_categories`
--
ALTER TABLE `faq_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `faq_translations`
--
ALTER TABLE `faq_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_faq_lang` (`faq_id`,`language_code`),
  ADD KEY `idx_faq_id` (`faq_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `features`
--
ALTER TABLE `features`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `feature_translations`
--
ALTER TABLE `feature_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_feature_lang` (`feature_id`,`language_code`),
  ADD KEY `idx_feature_id` (`feature_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `gallery_categories`
--
ALTER TABLE `gallery_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Tablo için indeksler `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_file_type` (`file_type`),
  ADD KEY `idx_folder` (`folder`),
  ADD KEY `idx_created` (`created_at`);

--
-- Tablo için indeksler `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_menu_item_lang` (`menu_item_id`,`language_code`),
  ADD KEY `idx_menu_item_id` (`menu_item_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `outsource_partners`
--
ALTER TABLE `outsource_partners`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `page_settings`
--
ALTER TABLE `page_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_key` (`page_key`);

--
-- Tablo için indeksler `page_setting_translations`
--
ALTER TABLE `page_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_page_lang` (`page_setting_id`,`language_code`);

--
-- Tablo için indeksler `page_translations`
--
ALTER TABLE `page_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_page_lang` (`page_id`,`language_code`),
  ADD KEY `idx_page_id` (`page_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `section_translations`
--
ALTER TABLE `section_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_lang` (`section_id`,`language_code`),
  ADD KEY `idx_section_id` (`section_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Tablo için indeksler `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `slider_translations`
--
ALTER TABLE `slider_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slider_lang` (`slider_id`,`language_code`),
  ADD KEY `idx_slider_id` (`slider_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `terms_translations`
--
ALTER TABLE `terms_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `language_code` (`language_code`);

--
-- Tablo için indeksler `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `testimonial_translations`
--
ALTER TABLE `testimonial_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_testimonial_lang` (`testimonial_id`,`language_code`),
  ADD KEY `idx_testimonial_id` (`testimonial_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `tour_categories`
--
ALTER TABLE `tour_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `tour_translations`
--
ALTER TABLE `tour_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tour_lang` (`tour_id`,`language_code`),
  ADD KEY `idx_tour_id` (`tour_id`),
  ADD KEY `idx_language_code` (`language_code`);

--
-- Tablo için indeksler `tour_vehicles`
--
ALTER TABLE `tour_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tour_vehicle_lang` (`tour_id`,`vehicle_id`,`language_code`),
  ADD KEY `idx_tour_id` (`tour_id`),
  ADD KEY `idx_vehicle_id` (`vehicle_id`);

--
-- Tablo için indeksler `transfer_detail_translations`
--
ALTER TABLE `transfer_detail_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `language_code` (`language_code`);

--
-- Tablo için indeksler `transfer_features`
--
ALTER TABLE `transfer_features`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `transfer_feature_translations`
--
ALTER TABLE `transfer_feature_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_feature_lang` (`feature_id`,`language_code`);

--
-- Tablo için indeksler `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_translation` (`language_code`,`trans_group`,`trans_key`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `vehicle_services`
--
ALTER TABLE `vehicle_services`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `vehicle_service_translations`
--
ALTER TABLE `vehicle_service_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_service_lang` (`service_id`,`language_code`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `blog_post_translations`
--
ALTER TABLE `blog_post_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- Tablo için AUTO_INCREMENT değeri `booking_alert_translations`
--
ALTER TABLE `booking_alert_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `booking_passengers`
--
ALTER TABLE `booking_passengers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- Tablo için AUTO_INCREMENT değeri `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Tablo için AUTO_INCREMENT değeri `destination_translations`
--
ALTER TABLE `destination_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- Tablo için AUTO_INCREMENT değeri `destination_vehicles`
--
ALTER TABLE `destination_vehicles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=697;

--
-- Tablo için AUTO_INCREMENT değeri `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `faq_categories`
--
ALTER TABLE `faq_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `faq_translations`
--
ALTER TABLE `faq_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `features`
--
ALTER TABLE `features`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `feature_translations`
--
ALTER TABLE `feature_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `gallery_categories`
--
ALTER TABLE `gallery_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Tablo için AUTO_INCREMENT değeri `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `outsource_partners`
--
ALTER TABLE `outsource_partners`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Tablo için AUTO_INCREMENT değeri `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `page_settings`
--
ALTER TABLE `page_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=351;

--
-- Tablo için AUTO_INCREMENT değeri `page_setting_translations`
--
ALTER TABLE `page_setting_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `page_translations`
--
ALTER TABLE `page_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `section_translations`
--
ALTER TABLE `section_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `slider_translations`
--
ALTER TABLE `slider_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `terms_translations`
--
ALTER TABLE `terms_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `testimonial_translations`
--
ALTER TABLE `testimonial_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `tour_categories`
--
ALTER TABLE `tour_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tour_translations`
--
ALTER TABLE `tour_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Tablo için AUTO_INCREMENT değeri `tour_vehicles`
--
ALTER TABLE `tour_vehicles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tablo için AUTO_INCREMENT değeri `transfer_detail_translations`
--
ALTER TABLE `transfer_detail_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `transfer_features`
--
ALTER TABLE `transfer_features`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `transfer_feature_translations`
--
ALTER TABLE `transfer_feature_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Tablo için AUTO_INCREMENT değeri `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `vehicle_services`
--
ALTER TABLE `vehicle_services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Tablo için AUTO_INCREMENT değeri `vehicle_service_translations`
--
ALTER TABLE `vehicle_service_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `booking_passengers`
--
ALTER TABLE `booking_passengers`
  ADD CONSTRAINT `booking_passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `page_setting_translations`
--
ALTER TABLE `page_setting_translations`
  ADD CONSTRAINT `page_setting_translations_ibfk_1` FOREIGN KEY (`page_setting_id`) REFERENCES `page_settings` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `transfer_feature_translations`
--
ALTER TABLE `transfer_feature_translations`
  ADD CONSTRAINT `transfer_feature_translations_ibfk_1` FOREIGN KEY (`feature_id`) REFERENCES `transfer_features` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `vehicle_service_translations`
--
ALTER TABLE `vehicle_service_translations`
  ADD CONSTRAINT `vehicle_service_translations_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `vehicle_services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
