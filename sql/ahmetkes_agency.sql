-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 14 Haz 2026, 23:58:07
-- Sunucu sürümü: 8.0.45-cll-lve
-- PHP Sürümü: 8.4.21

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
  `outsource_currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `outsource_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outsource_pickup_time` time DEFAULT NULL,
  `outsource_partner_id` int DEFAULT NULL,
  `customer_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `bookings`
--

INSERT INTO `bookings` (`id`, `booking_number`, `booking_type`, `booking_status`, `booking_direction`, `tour_id`, `destination_id`, `vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `pickup_location`, `pickup_date`, `pickup_time`, `return_time`, `flight_date`, `flight_time`, `flight_number`, `hotel_address`, `return_transfer`, `return_flight_date`, `return_flight_time`, `return_flight_number`, `return_pickup_time`, `return_hotel_address`, `adults`, `children`, `child_seat`, `total_price`, `currency`, `notes`, `admin_notes`, `is_completed`, `is_outsourced`, `outsource_price`, `outsource_currency`, `created_at`, `updated_at`, `outsource_name`, `outsource_pickup_time`, `outsource_partner_id`, `customer_id`) VALUES
(37, 'TRF-20260428-0191', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'FİKRET GEMRİKLİ', '', '+49017680515901', NULL, NULL, '07:30:00', NULL, '2026-05-02', '18:25:00', 'XQ147', 'Delphin Be Grand', 0, NULL, NULL, NULL, NULL, NULL, 4, 2, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-04-28 20:50:28', '2026-06-14 20:21:10', NULL, NULL, NULL, 1),
(38, 'TRF-20260428-1764', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'FİKRET GEMRİKLİ', '', '+49017680515901', NULL, NULL, '07:00:00', NULL, '2026-05-09', '10:05:00', 'XQ146', 'DELPHIN BE GRAND — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 2, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-04-28 20:50:28', '2026-06-14 20:21:10', 'Yılmaz Acar', '07:00:00', 39, 1),
(48, 'TRF-20260429-7273', 'transfer', 'confirmed', 'return', NULL, 7, 3, 'KANDSORRA WOLFGANG', '', '+4917672883149', NULL, NULL, '17:30:00', NULL, '2026-04-29', '20:55:00', 'XQ236', 'Lilyum Hotel', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-29 09:51:57', '2026-06-14 20:21:10', NULL, NULL, NULL, 2),
(49, 'TRF-20260429-7985', 'transfer', 'confirmed', 'return', NULL, 10, 1, 'EDVIN TISK', '', '+4748653342', NULL, NULL, '09:00:00', NULL, '2026-04-28', '12:00:00', NULL, 'LAGO HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-29 09:59:09', '2026-06-14 20:21:10', NULL, NULL, NULL, 3),
(50, 'TRF-20260429-4802', 'transfer', 'confirmed', 'outbound', NULL, 18, 1, 'KYANAT SHAH', '', '+447464218024', NULL, NULL, NULL, NULL, '2026-04-29', '16:25:00', 'LS1239', 'Eftalia Splash', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-29 10:17:31', '2026-06-14 20:21:11', NULL, NULL, NULL, 4),
(52, 'TRF-20260429-6978', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'ANNA JAKUZSEW', '', '+48513095789', NULL, NULL, NULL, NULL, '2026-04-29', '17:55:00', 'ENT7055', 'Delphin Palace', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-29 10:33:02', '2026-06-14 20:21:11', NULL, NULL, NULL, 5),
(54, 'TRF-20260429-2347', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'PATRICIA HIBBERT', '', '+44756534817', NULL, NULL, '17:30:00', NULL, '2026-04-29', '22:30:00', 'EZY2856', 'PORT RIVER HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-29 10:46:13', '2026-06-14 20:21:11', NULL, NULL, NULL, 6),
(55, 'TRF-20260429-9867', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'EMILY HENZLER', '', '', NULL, NULL, NULL, NULL, '2026-01-24', '19:00:00', 'XQ141', 'Grand Park Lara', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-04-29 11:44:43', '2026-06-14 20:21:11', NULL, NULL, NULL, 7),
(56, 'TRF-20260429-5173', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'EMILY HENZLER', '', '', NULL, NULL, '07:30:00', NULL, '2026-01-27', '10:45:00', 'XQ140', 'Grand Park Lara', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-04-29 11:44:43', '2026-06-14 20:21:11', NULL, '07:30:00', NULL, 7),
(57, 'TRF-20260429-1746', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'ANJA LANGENBACH', '', '+49 176 34139393', NULL, NULL, NULL, NULL, '2026-02-04', '19:10:00', 'XQ115', 'Aydınbey Kings Palace', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, '', '2026-04-29 11:50:45', '2026-06-14 20:21:11', NULL, NULL, NULL, 8),
(58, 'TRF-20260429-4915', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'ANJA LANGENBACH', '', '+49 176 34139393', NULL, NULL, '07:00:00', NULL, '2026-02-11', '11:00:00', 'XQ114', 'Aydınbey Kings Palace', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-29 11:50:45', '2026-06-14 20:21:11', NULL, NULL, NULL, 8),
(59, 'TRF-20260429-7223', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'JENS KUHLMANN', '', '+49 173 6520273', NULL, NULL, NULL, NULL, '2026-02-05', '19:05:00', 'XQ233', 'The Sense Deluxe', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, '', '2026-04-29 11:55:02', '2026-06-14 20:21:11', NULL, NULL, NULL, 9),
(60, 'TRF-20260429-5059', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'JENS KUHLMANN', '', '+49 173 6520273', NULL, NULL, '19:00:00', NULL, '2026-02-12', '23:00:00', 'PC5009', 'The Sense Deluxe', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-04-29 11:55:02', '2026-06-14 20:21:11', NULL, NULL, NULL, 9),
(61, 'TRF-20260429-0925', 'transfer', 'confirmed', 'outbound', NULL, 12, 1, 'ROMAN BILY', '', '+44 7394 764702', NULL, NULL, NULL, NULL, '2026-04-22', '20:00:00', 'XQ593', 'Kleopatra Royal Palm', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 75.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-04-29 12:03:27', '2026-06-14 20:21:11', NULL, NULL, NULL, 10),
(67, 'TRF-20260430-7210', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'DANNY HENNEKES', '', '+31 6 52675282', NULL, NULL, NULL, NULL, '2026-05-21', '18:10:00', 'XC103', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 30.00, 'EUR', '4+2 Pax', NULL, 0, 0, NULL, '', '2026-04-30 00:35:43', '2026-06-14 20:21:11', NULL, NULL, NULL, 11),
(68, 'TRF-20260430-9161', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'QUAIMA AIT EL HADJ', '', '+31 6 36032967', NULL, NULL, NULL, NULL, '2026-05-01', '18:30:00', 'XC103', 'Mirackle Resort Hotel', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', 'ÇOCUK KOLTUĞU ŞART', NULL, 1, 1, 15.00, 'EUR', '2026-04-30 00:44:42', '2026-06-14 20:21:11', NULL, NULL, NULL, 12),
(69, 'TRF-20260430-9916', 'transfer', 'confirmed', 'return', NULL, 10, 1, 'BESMIRA ZHUTA', '', '+41 76 681 42 15', NULL, NULL, '04:00:00', NULL, '2026-05-03', '07:50:00', 'PC5027', 'SIDE CROWN PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-04-30 00:59:17', '2026-06-14 20:21:11', NULL, NULL, NULL, 13),
(70, 'TRF-20260430-5242', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'DJORDY HENDRICKS', '', '+31 6 39325013', NULL, NULL, NULL, NULL, '2026-05-27', '14:10:00', 'XQ283', 'Dream Fun World', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-30 11:38:33', '2026-06-14 20:21:12', NULL, NULL, NULL, 14),
(71, 'TRF-20260430-8491', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'ANGIE STARMANS', '', '+31 6 30034315', NULL, NULL, NULL, NULL, '2026-05-02', '08:05:00', 'PC5014', 'DREAM WORLD AQUA — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', 'asdadadadasdasd', NULL, 1, 0, NULL, NULL, '2026-04-30 11:55:19', '2026-06-14 20:21:12', NULL, NULL, NULL, 15),
(72, 'TRF-20260430-6823', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'AYMAN EL BEKKAOUI', '', '+31 6 13109793', NULL, NULL, NULL, NULL, '2026-05-01', '16:30:00', 'XC101', 'Aska Lara', 0, NULL, NULL, NULL, NULL, NULL, 4, 2, 1, 30.00, 'EUR', '1 CHILD SEAT\r\nKARIŞALAMA ISMI  JADE - AVAH MEZIANE', NULL, 1, 1, 15.00, 'EUR', '2026-04-30 12:02:32', '2026-06-14 20:21:12', NULL, NULL, NULL, 16),
(73, 'TRF-20260430-6063', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'RAZİYE ERYILMAZ', '', '', NULL, NULL, NULL, NULL, '2026-04-30', '15:55:00', 'XQ525', 'SUNBERK HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, '', '2026-04-30 12:07:45', '2026-06-14 20:21:12', NULL, NULL, NULL, 17),
(76, 'TRF-20260502-5801', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'Vitali Leis', '', '+49 176 22988685', NULL, NULL, NULL, NULL, '2026-05-18', '12:30:00', 'XQ141', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-02 13:15:33', '2026-06-14 20:21:12', NULL, NULL, NULL, 18),
(77, 'TRF-20260502-2358', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'Vitali Leis', '', '+49 176 22988685', NULL, NULL, '13:00:00', NULL, '2026-05-25', '15:50:00', 'XQ142', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-02 13:15:33', '2026-06-14 20:21:12', NULL, NULL, NULL, 18),
(78, 'TRF-20260502-3505', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MUHAMMAD BILAL', '', '+44 7384 266651', NULL, NULL, NULL, NULL, '2026-05-29', '10:00:00', 'J20025', 'PALORMA HOTEL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, 'ÖDEMEYİ 14.15TE İNECEK YOLCU YAPACAK', 1, 1, NULL, 'EUR', '2026-05-02 14:23:33', '2026-06-14 20:21:12', 'Yusuf Oğuz', NULL, 73, 19),
(79, 'TRF-20260502-0089', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MUDASSIR MANSHA', '', '+44 7384 266651', NULL, NULL, NULL, NULL, '2026-05-29', '14:15:00', 'EZY2141', 'PALORMA HOTEL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-02 14:30:58', '2026-06-14 20:21:12', 'Yusuf Oğuz', NULL, 73, 20),
(80, 'TRF-20260502-2670', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'MUDASSIR MANSHA', '', '+44 7384 266651', NULL, NULL, '07:00:00', NULL, '2026-06-19', '09:35:00', 'XQ592', 'PALORMA HOTEL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 1, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-02 14:30:58', '2026-06-14 20:21:12', NULL, NULL, NULL, 20),
(83, 'TRF-20260502-3270', 'transfer', 'confirmed', 'outbound', NULL, 17, 1, 'MAKSIM OSIPOVS', '', '+371 29 746 739', NULL, NULL, NULL, NULL, '2026-05-10', '04:35:00', '4M934', 'RUBI PLATINUM SPA RESORT & SUITES — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 55.00, 'EUR', NULL, 'YAPILI GÜZEL ARAÇ GEREKİRSE VİTO\r\nKARŞILAMA İSMİ \"MAXIM AND SEXY CHIA', 1, 1, 40.00, 'EUR', '2026-05-02 14:53:20', '2026-06-14 20:21:12', 'Yüksel Bozkurt', NULL, 44, 21),
(85, 'TRF-20260502-0476', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'BJÖRN SCHMELZER', '', '+491733010065', NULL, NULL, '19:00:00', NULL, '2026-05-02', '22:40:00', 'PC5009', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-02 15:26:35', '2026-06-14 20:21:12', NULL, '19:00:00', NULL, 22),
(86, 'TRF-20260502-2062', 'transfer', 'confirmed', 'return', NULL, NULL, NULL, 'RACHELLE SUTTON', '', '+44 7749 193180', NULL, NULL, '16:30:00', NULL, '2026-05-02', '20:25:00', 'LS1810', 'ARCANUS SORGUN — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 15:31:16', '2026-06-14 20:21:12', NULL, NULL, NULL, 23),
(87, 'TRF-20260502-5072', 'transfer', 'confirmed', 'outbound', NULL, NULL, 2, 'SHANTHOSAN SIVANDAN', '', '+44 7405 279855', NULL, NULL, NULL, NULL, '2026-05-02', '23:10:00', 'XQ531', 'GRAND PARK LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-02 15:35:50', '2026-06-14 20:21:12', NULL, NULL, NULL, 24),
(89, 'TRF-20260502-6222', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'KACPER BEC', '', '+49 1575 2472622', NULL, NULL, NULL, NULL, '2026-05-03', '00:30:00', 'XQ671', 'SULTAN OF SIDE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-02 15:45:05', '2026-06-14 20:21:13', 'Beyhan Suluova', NULL, 15, 25),
(90, 'TRF-20260502-3980', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'KACPER BEC', '', '+49 1575 2472622', NULL, NULL, '03:30:00', NULL, '2026-05-09', '07:00:00', 'XQ170', 'SULTAN OF SIDE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 15:45:05', '2026-06-14 20:21:13', NULL, NULL, NULL, 25),
(91, 'TRF-20260502-9319', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'HELENA WIEBE', '', '', NULL, NULL, '17:00:00', NULL, '2026-05-04', '20:55:00', 'XQ236', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 15:49:41', '2026-06-14 20:21:13', NULL, NULL, NULL, 26),
(92, 'TRF-20260502-2019', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'VERA ALBERTI', '', '+49 1523 3618806', NULL, NULL, '18:30:00', NULL, '2026-05-03', '22:45:00', 'XC112', 'SEAMELIA BEACH — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 15:54:11', '2026-06-14 20:21:13', NULL, NULL, NULL, 27),
(93, 'TRF-20260502-1517', 'transfer', 'confirmed', 'return', NULL, 18, 1, 'KYNAAT SHAH', '', '+44 7464 218024', NULL, NULL, '16:00:00', NULL, '2026-05-03', '22:45:00', 'LS1120', 'EFTALIA SPLASH RESORT — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-02 15:58:31', '2026-06-14 20:21:13', 'Uğur Aşanel', '16:00:00', 14, 28),
(94, 'TRF-20260502-7320', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'TAHSEEN SHAH', '', '+44 7790 453487', NULL, NULL, '18:30:00', NULL, '2026-05-03', '22:45:00', 'LS1120', 'BARUT GOIA — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-02 16:01:03', '2026-06-14 20:21:13', NULL, '18:30:00', NULL, 29),
(95, 'TRF-20260502-9739', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'İLKAY KOPARAN', '', '+49 178 8082590', NULL, NULL, NULL, NULL, '2026-05-03', '00:45:00', 'XQ155', 'LIMAK LARA DELUXE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-02 16:09:26', '2026-06-14 20:21:13', NULL, NULL, NULL, 30),
(96, 'TRF-20260502-8813', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'CAN KLEISMAN', '', '+49 163 6969895', NULL, NULL, NULL, NULL, '2026-05-04', '06:25:00', 'XQ257', 'DREAM WATER WORLD — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 16:36:02', '2026-06-14 20:21:13', NULL, NULL, NULL, 31),
(97, 'TRF-20260502-4170', 'transfer', 'confirmed', 'outbound', NULL, 24, 1, 'MUSTAFA LALE', '', '05327336949', NULL, NULL, NULL, NULL, '2026-05-04', '08:50:00', 'VF3032', 'JUJU PREMIER HOTEL — BELDİBİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 2000.00, 'TRY', NULL, NULL, 1, 1, 1300.00, 'TRY', '2026-05-02 16:43:43', '2026-06-14 20:21:13', 'Önder Günal', NULL, 20, 32),
(98, 'TRF-20260502-8809', 'transfer', 'confirmed', 'return', NULL, 24, 1, 'MUSTAFA LALE', '', '05327336949', NULL, NULL, '17:15:00', NULL, '2026-05-07', '20:45:00', NULL, 'JUJU PREMIER HOTEL — BELDİBİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 2000.00, 'TRY', NULL, NULL, 1, 1, 1300.00, 'TRY', '2026-05-02 16:43:43', '2026-06-14 20:21:13', 'Önder Günal', '17:15:00', 20, 32),
(99, 'TRF-20260502-9269', 'transfer', 'confirmed', 'return', NULL, NULL, NULL, 'ISABEL SCULTZ', '', '+49 172 2492390', NULL, NULL, '05:00:00', NULL, '2026-05-04', '09:00:00', 'PC5041', 'SIDE MARE — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 17:50:28', '2026-06-14 20:21:13', NULL, NULL, NULL, 33),
(100, 'TRF-20260502-4307', 'transfer', 'confirmed', 'outbound', NULL, 25, 1, 'SERGEN ÖZMEN', '', '+49 1577 7358358', NULL, NULL, NULL, NULL, '2026-05-05', '12:10:00', 'XQ151', 'KIRMAN BELAZUR — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-02 18:01:27', '2026-06-14 20:21:13', 'Mehmet Ali Öztaş', NULL, 23, 34),
(101, 'TRF-20260502-8019', 'transfer', 'confirmed', 'return', NULL, 25, 1, 'SERGEN ÖZMEN', '', '+49 1577 7358358', NULL, NULL, '15:00:00', NULL, '2026-05-12', '18:20:00', 'XQ150', 'KIRMAN BELAZUR — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-02 18:01:27', '2026-06-14 20:21:13', NULL, '15:00:00', NULL, 34),
(102, 'TRF-20260502-5031', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'SHANAY GORDON', '', '+44 7496 345618', NULL, NULL, NULL, NULL, '2026-05-05', '21:25:00', 'BY506', 'CLUB TURAN PRINCE WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', 'içecek ve wifi bağlantısı istiyoruz', NULL, 1, 1, 1700.00, 'TRY', '2026-05-02 18:06:04', '2026-06-14 20:21:14', 'Ali Taş', NULL, 25, 35),
(103, 'TRF-20260502-3738', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'SHANAY GORDON', '', '+44 7496 345618', NULL, NULL, '18:45:00', NULL, '2026-05-12', '22:25:00', 'BY507', 'CLUB TURAN PRINCE WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-02 18:06:04', '2026-06-14 20:21:14', 'Süleyman Kıdık', '18:45:00', 47, 35),
(104, 'TRF-20260502-2702', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'OWAIS ASIF', '', '+44 7414 286708', NULL, NULL, NULL, NULL, '2026-05-07', '00:55:00', 'XQ595', 'WASHINGTON RESORT — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-02 18:18:04', '2026-06-14 20:21:14', 'Mehmet Ali Mutlu', NULL, 29, 36),
(105, 'TRF-20260502-4160', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'OWAIS ASIF', '', '+44 7414 286708', NULL, NULL, '10:00:00', NULL, '2026-05-11', '14:25:00', 'XQ594', 'WASHINGTON RESORT — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 18:18:04', '2026-06-14 20:21:14', NULL, NULL, NULL, 36),
(106, 'TRF-20260502-0050', 'transfer', 'confirmed', 'return', NULL, 16, 1, 'ANNA JAKUSZEW', '', '+48 513 095 789', NULL, NULL, '10:30:00', NULL, '2026-05-04', '15:45:00', 'XQ420', 'DELPHIN DELUXE — OKURCALAR', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-02 18:20:44', '2026-06-14 20:21:14', 'Can Küpelioğlu', '10:30:00', 18, 37),
(107, 'TRF-20260502-4799', 'transfer', 'confirmed', 'return', NULL, 17, 1, 'DOERTA KUBERSKI', '', '+49 176 44224994', NULL, NULL, '18:30:00', NULL, '2026-05-07', '23:30:00', 'XQ166', 'ASKA JUST IN BEACH — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-02 18:24:06', '2026-06-14 20:21:14', 'Hasan Gül', '18:30:00', 33, 38),
(108, 'TRF-20260502-8861', 'transfer', 'confirmed', 'return', NULL, 26, 1, 'ILONA KÖNİG', '', '+49 170 4646219', NULL, NULL, '12:30:00', NULL, '2026-05-07', '16:35:00', 'XQ140', 'TRENDY ASPENDOS BEACH — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 18:30:16', '2026-06-14 20:21:14', NULL, NULL, NULL, 39),
(109, 'TRF-20260502-1655', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'HUSSEIN EL ABED', '', '+49 1512 4166324', NULL, NULL, NULL, NULL, '2026-05-07', '14:40:00', 'PC5002', 'ROYAL HOLIDAY PALACE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-02 18:39:00', '2026-06-14 20:21:14', 'Ömer Can Tülek', NULL, 32, 40),
(110, 'TRF-20260502-0163', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'HUSSEIN EL ABED', '', '+49 1512 4166324', NULL, NULL, '13:00:00', NULL, '2026-05-13', '15:30:00', 'XC106', 'ROYAL HOLIDAY PALACE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-02 18:39:00', '2026-06-14 20:21:14', NULL, '13:00:00', NULL, 40),
(111, 'TRF-20260502-2169', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'YUSSOF ALAM', '', '+49 160 1814464', NULL, NULL, NULL, NULL, '2026-05-08', '18:15:00', 'XQ173', 'ALBA RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 18:50:43', '2026-06-14 20:21:14', NULL, NULL, NULL, 41),
(112, 'TRF-20260502-1393', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'YUSSOF ALAM', '', '+49 160 1814464', NULL, NULL, '12:45:00', NULL, '2026-05-15', '16:10:00', 'XQ670', 'ALBA RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 18:50:43', '2026-06-14 20:21:14', NULL, NULL, NULL, 41),
(113, 'TRF-20260502-7354', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'Jacquelina Bollacke', '', '+49 176 23569654', NULL, NULL, NULL, NULL, '2026-05-08', '23:20:00', 'PC5012', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 35.00, 'EUR', '1 KINDERSITZ // ÇOCUK KOLTUĞU', NULL, 1, 1, 25.00, 'EUR', '2026-05-02 18:55:56', '2026-06-14 20:21:14', 'Mehmet Akif Esoğlu', NULL, 38, 42),
(114, 'TRF-20260502-3230', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'Jacquelina Bollacke', '', '+49 176 23569654', NULL, NULL, '03:00:00', NULL, '2026-05-16', '06:00:00', 'XQ188', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 35.00, 'EUR', '1 KINDERSITZ // ÇOCUK KOLTUĞU', NULL, 1, 1, 20.00, 'EUR', '2026-05-02 18:55:56', '2026-06-14 20:21:14', 'Yusuf Şanal', '03:00:00', 59, 42),
(115, 'TRF-20260502-9921', 'transfer', 'confirmed', 'return', NULL, 12, 1, 'STEFAN BELL', '', '+44 7796 913065', NULL, NULL, '04:30:00', NULL, '2026-05-04', '10:30:00', 'XC4142', 'ARSI BLUE BEACH — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 75.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-02 18:58:59', '2026-06-14 20:21:14', 'Murat Yılmaz', '05:00:00', 17, 43),
(117, 'TRF-20260502-1128', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'DIANA RÜLANDER', '', '+49 172 3740934', NULL, NULL, NULL, NULL, '2026-05-03', '04:45:00', 'XQ251', 'SÜRAL RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 19:13:25', '2026-06-14 20:21:15', NULL, NULL, NULL, 44),
(118, 'TRF-20260502-7951', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'DIANA RÜLANDER', '', '+49 172 3740934', NULL, NULL, '16:30:00', NULL, '2026-05-10', '20:20:00', NULL, 'SÜRAL RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-02 19:13:25', '2026-06-14 20:21:15', 'ss', '16:30:00', 49, 44),
(119, 'TRF-20260502-5995', 'transfer', 'confirmed', 'outbound', NULL, 7, 3, 'NADINE RADES', '', '+49 176 24655007', NULL, NULL, NULL, NULL, '2026-05-10', '16:15:00', '4M792', 'SIDE STORY HOTEL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 70.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-02 19:22:29', '2026-06-14 20:21:15', 'Eyüp Aksu', NULL, 46, 45),
(120, 'TRF-20260502-2039', 'transfer', 'confirmed', 'return', NULL, 7, 3, 'NADINE RADES', '', '+49 176 24655007', NULL, NULL, '05:25:00', NULL, '2026-05-17', '08:25:00', '4M791', 'SIDE STORY HOTEL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 70.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-02 19:22:29', '2026-06-14 20:21:15', 'Ömer Can Tülek', '05:25:00', 32, 45),
(121, 'TRF-20260502-3224', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'GALINA STENNING', '', '', NULL, NULL, '07:00:00', NULL, '2026-05-04', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-02 22:03:14', '2026-06-14 20:21:15', NULL, NULL, NULL, 46),
(124, 'TRF-20260503-3163', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'ANNA SCHILDER', '', '+43 650 6901304', NULL, NULL, NULL, NULL, '2026-05-14', '13:55:00', 'XQ199', 'TUI MAGIC LIFE — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-03 10:45:21', '2026-06-14 20:21:15', NULL, NULL, NULL, 47),
(125, 'TRF-20260503-9785', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'ANNA SCHILDER', '', '+43 650 6901304', NULL, NULL, '09:15:00', NULL, '2026-05-20', '12:10:00', 'XQ190', 'TUI MAGIC LIFE — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 10:45:21', '2026-06-14 20:21:15', NULL, NULL, NULL, 47),
(126, 'TRF-20260503-8542', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'KIM HOEKSTRA', '', '+31 6 53878520', NULL, NULL, NULL, NULL, '2026-06-22', '11:00:00', 'HV5011', 'DREAM WORLD PALACE — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 10:51:45', '2026-06-14 20:21:15', NULL, NULL, NULL, 48),
(127, 'TRF-20260503-5721', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'KIM HOEKSTRA', '', '+31 6 53878520', NULL, NULL, '08:00:00', NULL, '2026-06-29', '12:00:00', 'HV5012', 'DREAM WORLD PALACE — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 10:51:45', '2026-06-14 20:21:15', NULL, NULL, NULL, 48),
(128, 'TRF-20260503-1818', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'STEVEN HANLEY', '', '+44 7795 466431', NULL, NULL, NULL, NULL, '2026-05-19', '00:55:00', 'XQ595', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 12:51:59', '2026-06-14 20:21:15', NULL, NULL, NULL, 49),
(129, 'TRF-20260503-8421', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'STEVEN HANLEY', '', '+44 7795 466431', NULL, NULL, '08:45:00', NULL, '2026-05-29', '12:00:00', 'XQ534', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-03 12:51:59', '2026-06-14 20:21:15', 'Yusuf Oğuz', '08:45:00', 73, 49),
(130, 'TRF-20260503-5684', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'Dylan De Noijer', '', '+31 6 18323023', NULL, NULL, NULL, NULL, '2026-05-10', '14:00:00', 'OR3045', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-03 13:37:30', '2026-06-14 20:21:15', 'Sinan Adıyamanlı', NULL, 48, 50),
(131, 'TRF-20260503-7229', 'transfer', 'confirmed', 'outbound', NULL, 25, NULL, 'NATALIJA MARINA', '', '+353 83 152 1411', NULL, NULL, NULL, NULL, '2026-05-12', '19:25:00', 'XQ533', 'CRYSTAL WATER WORLD AQUA COLLECTION — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 35.00, 'EUR', 'KARŞILAMA İSMİ ROBERT MARINA', NULL, 1, 1, 25.00, 'EUR', '2026-05-03 13:53:59', '2026-06-14 20:21:15', 'Europa Vip', NULL, 51, 51),
(132, 'TRF-20260503-4869', 'transfer', 'confirmed', 'return', NULL, 25, NULL, 'NATALIJA MARINA', '', '+353 83 152 1411', NULL, NULL, '04:15:00', NULL, '2026-05-20', '08:15:00', 'XQ532', 'CRYSTAL WATER WORLD AQUA COLLECTION — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 35.00, 'EUR', 'KARŞILAMA İSMİ ROBERT MARINA', NULL, 1, 1, 25.00, 'EUR', '2026-05-03 13:53:59', '2026-06-14 20:21:15', 'Tolgahan Güven', '04:15:00', 67, 51),
(133, 'TRF-20260503-2682', 'transfer', 'confirmed', 'outbound', NULL, 27, 1, 'MICHELLE GOROWOI', '', '+49 176 72971935', NULL, NULL, NULL, NULL, '2026-05-13', '04:15:00', '4M232', 'ADONIS HOTEL — ŞİRİNYALI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-03 14:05:50', '2026-06-14 20:21:15', 'Ömer', NULL, 54, 52),
(134, 'TRF-20260503-6610', 'transfer', 'confirmed', 'return', NULL, 27, 1, 'MICHELLE GOROWOI', '', '+49 176 72971935', NULL, NULL, '16:45:00', NULL, '2026-05-17', '19:50:00', 'XQ166', 'ADONIS HOTEL — ŞİRİNYALI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-03 14:05:50', '2026-06-14 20:21:15', NULL, '16:45:00', NULL, 52),
(135, 'TRF-20260503-1636', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'SENNA SANDERS', '', '+31 6 57475947', NULL, NULL, NULL, NULL, '2026-05-13', '04:40:00', '4M260', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-03 14:10:49', '2026-06-14 20:21:16', 'Cemil Duruk', NULL, 53, 53),
(136, 'TRF-20260503-1385', 'transfer', 'confirmed', 'outbound', NULL, 17, 1, 'STEFFI SCHAPER', '', '+49 176 50271885', NULL, NULL, NULL, NULL, '2026-05-13', '05:20:00', 'XQ237', 'ASKA JUST IN BEACH — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-03 15:09:05', '2026-06-14 20:21:16', 'Savaş Tunç', NULL, 55, 54),
(137, 'TRF-20260503-0722', 'transfer', 'confirmed', 'outbound', NULL, 17, 1, 'INES KAUFMANN', '', '+49 162 9148108', NULL, NULL, NULL, NULL, '2026-05-14', '06:15:00', 'XQ247', 'ASKA JUST IN BEACH — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 55.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-03 15:10:29', '2026-06-14 20:21:16', NULL, NULL, NULL, 55),
(138, 'TRF-20260503-8992', 'transfer', 'confirmed', 'return', NULL, 17, 1, 'STEFFI SCHAPER', '', '+49 176 50271885', NULL, NULL, '17:00:00', NULL, '2026-05-21', '21:50:00', 'XQ234', 'ASKA JUST IN BEACH — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-03 15:13:17', '2026-06-14 20:21:16', 'Mega Vip', '17:00:00', 68, 54),
(139, 'TRF-20260503-0273', 'transfer', 'confirmed', 'outbound', NULL, 18, 1, 'KIARA SWEENEY', '', '+44 7742 740212', NULL, NULL, NULL, NULL, '2026-05-14', '19:50:00', 'EZY2141', 'VIKINGEN INFINITY RESORT — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-03 15:18:28', '2026-06-14 20:21:16', NULL, NULL, NULL, 56),
(140, 'TRF-20260503-6910', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'NELLI DAKOV', '', '+49 1520 2459620', NULL, NULL, NULL, NULL, '2026-05-15', '12:20:00', 'XQ103', 'SELECTUM LUXURY RESORT — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-03 15:24:49', '2026-06-14 20:21:16', NULL, NULL, NULL, 57),
(141, 'TRF-20260503-2877', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'NELLI DAKOV', '', '+49 1520 2459620', NULL, NULL, '16:00:00', NULL, '2026-05-21', '19:05:00', '4M101', 'SELECTUM LUXURY RESORT — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 15:24:49', '2026-06-14 20:21:16', NULL, NULL, NULL, 57),
(142, 'TRF-20260503-0296', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'MASIH PAIKARJO', '', '+49 15568 645570', NULL, NULL, NULL, NULL, '2026-05-15', '19:05:00', 'FH1274', 'ALBA RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', 'KARŞILAMA İSMİ \"FRAU CANALES\"', NULL, 1, 0, NULL, NULL, '2026-05-03 15:30:21', '2026-06-14 20:21:16', NULL, NULL, NULL, 58),
(143, 'TRF-20260503-2938', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'MASIH PAIKARJO', '', '+49 15568 645570', NULL, NULL, '05:30:00', NULL, '2026-05-26', '09:55:00', NULL, 'ALBA RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 3, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 15:30:21', '2026-06-14 20:21:16', NULL, NULL, NULL, 58),
(144, 'TRF-20260503-6675', 'transfer', 'confirmed', 'outbound', NULL, 23, 1, 'VADIMS STEPANOVS', '', '+371 29 119 619', NULL, NULL, NULL, NULL, '2026-05-16', '10:20:00', 'BT7717', 'DOBEDAN WORLD KEMER — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-03 15:45:57', '2026-06-14 20:21:16', 'Turgay Hasırcı', NULL, 60, 59),
(145, 'TRF-20260503-5980', 'transfer', 'confirmed', 'outbound', NULL, 26, 1, 'Hennes weubker', '', '', NULL, NULL, NULL, NULL, '2026-05-04', '01:00:00', 'XQ285', 'DIAMOND DELUXE — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-03 21:40:37', '2026-06-14 20:21:16', 'Mustafa Bayrak', NULL, 16, 60),
(146, 'TRF-20260503-0965', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'CALLUM KALSI', '', '+44 7376 237519', NULL, NULL, NULL, NULL, '2026-05-18', '01:15:00', 'TOM202', 'TUI MAGIC LIFE MASMAVI — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 950.00, 'TRY', '2026-05-03 22:43:41', '2026-06-14 20:21:16', 'Burhan Koyuncu', NULL, 64, 61),
(147, 'TRF-20260503-3438', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'JANA VON JAN', '', '+49 177 8115059', NULL, NULL, NULL, NULL, '2026-05-26', '06:25:00', 'XQ247', 'SIDE ROYAL STYLE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 22:49:33', '2026-06-14 20:21:16', NULL, NULL, NULL, 62),
(148, 'TRF-20260503-5195', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'JANA VON JAN', '', '+49 177 8115059', NULL, NULL, '18:30:00', NULL, '2026-05-30', '22:40:00', 'XQ246', 'SIDE ROYAL STYLE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-03 22:49:33', '2026-06-14 20:21:16', NULL, NULL, NULL, 62),
(149, 'TRF-20260503-0266', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'INEZ WNEK', '', '+49 1521 4356687', NULL, NULL, NULL, NULL, '2026-05-04', '06:55:00', 'PC5004', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-03 22:58:12', '2026-06-14 20:21:17', 'Mehmet Paşa', NULL, 22, 63),
(150, 'TRF-20260504-9031', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'LUKMAN ALIZADA', '', '+44 7704 449147', NULL, NULL, NULL, NULL, '2026-05-06', '00:55:00', 'XQ595', 'DREAM WORLD PALACE — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-04 00:07:24', '2026-06-14 20:21:17', NULL, NULL, NULL, 64),
(151, 'TRF-20260504-8380', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'ZAID NANAWADI', '', '+44 7471 267039', NULL, NULL, NULL, NULL, '2026-05-07', '20:50:00', 'XC4101', 'BOSPHORUS HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-04 10:37:16', '2026-06-14 20:21:17', NULL, NULL, NULL, 65),
(152, 'TRF-20260504-5688', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'JESSICA EILERS', '', '+49 173 6563645', NULL, NULL, NULL, NULL, '2026-05-16', '16:25:00', 'PC5060', 'GRAND SEKER OTEL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 1, 40.00, 'EUR', '1 ÇOCUK KOLTUĞU', NULL, 1, 0, NULL, NULL, '2026-05-04 13:53:26', '2026-06-14 20:21:17', NULL, NULL, NULL, 66),
(153, 'TRF-20260504-9110', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'JESSICA EILERS', '', '+49 173 6563645', NULL, NULL, '03:15:00', NULL, '2026-05-22', '07:15:00', NULL, 'GRAND SEKER OTEL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 1, 40.00, 'EUR', '1 ÇOCUK KOLTUĞU', NULL, 1, 1, 25.00, 'EUR', '2026-05-04 13:53:26', '2026-06-14 20:21:17', 'Hüseyin Asar', '03:15:00', 62, 66),
(154, 'TRF-20260504-0285', 'transfer', 'confirmed', 'outbound', NULL, 28, 3, 'SILVIU FLORIAN BOC', '', '+49 177 6728414', NULL, NULL, NULL, NULL, '2026-05-18', '06:25:00', 'XQ367', 'ADORE HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 45.00, 'EUR', NULL, NULL, 1, 1, 1600.00, 'TRY', '2026-05-04 14:02:12', '2026-06-14 20:21:17', 'Emre Bayrak', NULL, 26, 67),
(155, 'TRF-20260504-3668', 'transfer', 'confirmed', 'outbound', NULL, 26, 1, 'GUIDO PESCHEL', '', '+49 176 60443492', NULL, NULL, NULL, NULL, '2026-05-18', '12:20:00', 'XQ103', 'TRENDY ASPENDOS BEACH — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-04 14:11:16', '2026-06-14 20:21:17', NULL, NULL, NULL, 68),
(156, 'TRF-20260504-6472', 'transfer', 'confirmed', 'return', NULL, 26, 1, 'GUIDO PESCHEL', '', '+49 176 60443492', NULL, NULL, '04:30:00', NULL, '2026-05-27', '08:20:00', 'XQ614', 'TRENDY ASPENDOS BEACH — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-04 14:11:16', '2026-06-14 20:21:17', NULL, NULL, NULL, 68),
(157, 'TRF-20260504-6811', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'MARCEL HÜNİNG', '', '+49 170 1719718', NULL, NULL, NULL, NULL, '2026-05-21', '08:15:00', 'XQ257', 'SEADEN SEA WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-04 14:21:32', '2026-06-14 20:21:17', NULL, NULL, NULL, 69),
(158, 'TRF-20260504-0839', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'MARCEL HÜNİNG', '', '+49 170 1719718', NULL, NULL, '19:00:00', NULL, '2026-05-28', '23:45:00', 'XQ256', 'SEADEN SEA WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-04 14:21:32', '2026-06-14 20:21:17', 'Yusuf Oğuz', '19:00:00', 73, 69),
(159, 'TRF-20260504-9021', 'transfer', 'confirmed', 'outbound', NULL, 17, 1, 'ALJONA BENZ', '', '+49 176 61569610', NULL, NULL, NULL, NULL, '2026-05-23', '07:05:00', 'PC5056', 'AQI PEGASOS ROYAL — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 55.00, 'EUR', '4 ADET SOĞUK BİRA / 4 COLD BEER', NULL, 0, 0, NULL, NULL, '2026-05-04 15:23:14', '2026-06-14 20:21:17', NULL, NULL, NULL, 70),
(160, 'TRF-20260504-1090', 'transfer', 'confirmed', 'return', NULL, 17, 1, 'ALJONA BENZ', '', '+49 176 61569610', NULL, NULL, '18:30:00', NULL, '2026-05-29', '23:10:00', '5055', 'AQI PEGASOS ROYAL — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-04 15:23:14', '2026-06-14 20:21:17', 'Uğur Kurtman', '18:30:00', 85, 70),
(161, 'TRF-20260505-5360', 'transfer', 'confirmed', 'outbound', NULL, 19, 2, 'ALPASLAN YALÇIN', '', '+33 6 36 67 06 67', NULL, NULL, NULL, NULL, '2026-05-08', '15:30:00', 'XQ127', 'ETHNO BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 35.00, 'EUR', 'MAYBACH YA DA VITO', NULL, 1, 1, 20.00, 'EUR', '2026-05-05 10:28:52', '2026-06-14 20:21:17', 'Abdülrezzak Yeşil', NULL, 34, 71),
(162, 'TRF-20260505-3113', 'transfer', 'confirmed', 'outbound', NULL, 29, 2, 'YASSİNE BOUZINE', '', '+31 6 24980321', NULL, NULL, NULL, NULL, '2026-05-07', '15:15:00', 'PC5016', 'FETHİYE MERKEZ — FETHİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 130.00, 'EUR', NULL, NULL, 1, 1, 90.00, 'EUR', '2026-05-05 10:33:52', '2026-06-14 20:21:17', 'Murat Şanlı', NULL, 28, 72),
(163, 'TRF-20260505-8984', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'ELISA PAGNOZZO', '', '', NULL, NULL, '14:00:00', NULL, '2026-05-06', NULL, NULL, 'LIMAK LARA DELUXE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-05 10:40:53', '2026-06-14 20:21:18', 'Ünal Hartamacı', '14:00:00', 27, 73),
(164, 'TRF-20260505-0043', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'JENS KUHLMANN', '', '+49 173 6520273', NULL, NULL, NULL, NULL, '2026-05-07', '15:35:00', 'XQ219', 'SIDE AMOUR HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, 'EL VALİZİ VAR BAGAJ BEKLEMEZ HIZLI ÇIKAR', 1, 0, NULL, NULL, '2026-05-05 12:32:13', '2026-06-14 20:21:18', NULL, NULL, NULL, 9),
(165, 'TRF-20260505-7359', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'ERHAN KARAKOÇ', '', '', NULL, NULL, NULL, NULL, '2026-05-11', '10:50:00', 'PC1871', 'MANAVGAT — MERKEZ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 0.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-05 12:49:25', '2026-06-14 20:21:18', NULL, NULL, NULL, 74),
(166, 'TRF-20260505-8394', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'ERHAN KARAKOÇ', '', '', NULL, NULL, '04:30:00', NULL, '2026-05-10', '07:15:00', 'PC1870', 'MANAVGAT — MERKEZ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 0.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-05 12:49:25', '2026-06-14 20:21:18', NULL, NULL, NULL, 74),
(167, 'TRF-20260505-6152', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'ADEM UĞURLU', '', '', NULL, NULL, NULL, NULL, '2026-05-30', '19:10:00', NULL, 'MANAVGAT — MERKEZ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 0.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 12:52:23', '2026-06-14 20:21:18', NULL, NULL, NULL, 75),
(168, 'TRF-20260505-1732', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'ADEM UĞURLU', '', '', NULL, NULL, '08:00:00', NULL, '2026-05-18', '11:20:00', NULL, 'MANAVGAT — MERKEZ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 0.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-05 12:52:23', '2026-06-14 20:21:18', NULL, NULL, NULL, 75),
(169, 'TRF-20260505-4686', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MICHAEL ROSENBOOM', '', '+49 176 12266226', NULL, NULL, NULL, NULL, '2026-05-22', '04:35:00', '4M428', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-05 13:10:02', '2026-06-14 20:21:18', NULL, NULL, NULL, 76),
(170, 'TRF-20260505-8794', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'MICHAEL ROSENBOOM', '', '+49 176 12266226', NULL, NULL, '16:30:00', NULL, '2026-05-29', '19:10:00', '4M527', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-05 13:10:02', '2026-06-14 20:21:18', 'Yusuf Oğuz', '16:30:00', 73, 76),
(171, 'TRF-20260505-3439', 'transfer', 'confirmed', 'outbound', NULL, 26, 1, 'ALEKSANDRA PRUCHNIEWSKA', '', '', NULL, NULL, NULL, NULL, '2026-05-23', '23:50:00', '3Z7270', 'ROYAL ATLANTIS ICON — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 1400.00, 'TRY', '2026-05-05 13:24:12', '2026-06-14 20:21:18', 'İdris Şanlı', NULL, 70, 77),
(173, 'TRF-20260505-1606', 'transfer', 'confirmed', 'outbound', NULL, 12, 1, 'ANDREAS KOZOLE', '', '+491634660361', NULL, NULL, NULL, NULL, '2026-05-26', '04:45:00', 'XQ251', 'TAÇ PREMIER HOTEL — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 75.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-05 13:26:56', '2026-06-14 20:21:18', 'Flamingo Vip', NULL, 81, 78),
(174, 'TRF-20260505-9421', 'transfer', 'confirmed', 'return', NULL, 12, 1, 'ANDREAS KOZOLE', '', '', NULL, NULL, '15:00:00', NULL, '2026-06-06', '20:20:00', 'XQ250', 'TAÇ PREMIER HOTEL — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 75.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-05 13:26:56', '2026-06-14 20:21:18', 'Murat Yılmaz', '15:00:00', 17, 79),
(175, 'TRF-20260505-8274', 'transfer', 'confirmed', 'outbound', NULL, 8, 2, 'MIKE WETERKAMP', '', '+31 6 37010275', NULL, NULL, NULL, NULL, '2026-05-25', '11:00:00', 'HV5011', 'ARCANUS SORGUN — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, 'ÖNCELİK VİTO', 1, 1, 25.00, 'EUR', '2026-05-05 14:34:02', '2026-06-14 20:21:18', 'Zeynel Temel', NULL, 79, 80),
(176, 'TRF-20260505-1286', 'transfer', 'confirmed', 'return', NULL, 8, 2, 'MIKE WETERKAMP', '', '+31 6 37010275', NULL, NULL, '07:30:00', NULL, '2026-05-30', '11:50:00', 'HV5012', 'ARCANUS SORGUN — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, 'ÖNCELİK VİTO', 0, 0, NULL, NULL, '2026-05-05 14:34:02', '2026-06-14 20:21:18', NULL, NULL, NULL, 80),
(177, 'TRF-20260505-8605', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'MICHAEL BÖHRİNGER', '', '+49 1511 0502460', NULL, NULL, NULL, NULL, '2026-05-26', '08:20:00', 'XQ113', 'THE SENSE DELUXE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 14:41:09', '2026-06-14 20:21:18', NULL, NULL, NULL, 81),
(178, 'TRF-20260505-2216', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'Anke Mühlstädt', '', '', NULL, NULL, '20:00:00', NULL, '2026-06-04', NULL, 'XQ112', 'THE SENSE DELUXE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-05 14:43:44', '2026-06-14 20:21:18', 'Yasin Kuru', '20:00:00', 36, 82),
(179, 'TRF-20260505-1778', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'MICHAEL BÖHRİNGER', '', '+49 1511 0502460', NULL, NULL, '18:00:00', NULL, '2026-06-09', '21:40:00', 'XQ118', 'THE SENSE DELUXE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-05 14:45:00', '2026-06-14 20:21:19', 'Mehmet çelik', '18:00:00', 86, 81),
(180, 'TRF-20260505-6069', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'PAUL COMBES', '', '+44 7368 923639', NULL, NULL, '17:15:00', NULL, '2026-05-06', '21:00:00', 'LS1240', 'DREAM WORLD PALACE — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-05 14:55:58', '2026-06-14 20:21:19', NULL, '17:15:00', NULL, 83),
(181, 'TRF-20260505-2678', 'transfer', 'confirmed', 'outbound', NULL, 23, NULL, 'UFUK CÖMERT', '', '+49 1520 8688845', NULL, NULL, NULL, NULL, '2026-05-28', '21:50:00', 'PC5012', 'LIMAK LIMRA HOTEL — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 15:04:53', '2026-06-14 20:21:19', NULL, NULL, NULL, 84),
(182, 'TRF-20260505-0559', 'transfer', 'confirmed', 'return', NULL, 23, NULL, 'UFUK CÖMERT', '', '+49 1520 8688845', NULL, NULL, '06:30:00', NULL, '2026-06-05', '09:30:00', NULL, 'LIMAK LIMRA HOTEL — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 50.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-05 15:04:53', '2026-06-14 20:21:19', 'Fahri Genç', '06:30:00', 92, 84),
(183, 'TRF-20260505-2523', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'MUHAMMAD WAQAS', '', '+44 7435 778035', NULL, NULL, NULL, NULL, '2026-06-03', '21:00:00', 'XQ537', 'GRANADA LUXURY BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', 'Happy Birthday, my love. Every journey is better with you.”', 'Happy Birthday, my love. Every journey is better with you.”\r\nKARŞILAMA TABELASI BU YAZACAK ÇİCEKLE KARŞILANACAK\r\nMÜMKÜNSE BGL717 İSTENİYOR', 0, 0, NULL, NULL, '2026-05-05 16:14:36', '2026-06-14 20:21:19', NULL, NULL, NULL, 85),
(184, 'TRF-20260505-5946', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'MUHAMMAD WAQAS', '', '+44 7435 778035', NULL, NULL, '18:00:00', NULL, '2026-06-10', '21:00:00', 'LS1240', 'GRANADA LUXURY BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 20.00, 'GBP', 'FLOWERS WELCOME', 'Happy Birthday, my love. Every journey is better with you.”\r\nKARŞILAMA TABELASI BU YAZACAK ÇİCEKLE KARŞILANACAK', 0, 0, NULL, NULL, '2026-05-05 16:14:36', '2026-06-14 20:21:19', NULL, NULL, NULL, 85),
(185, 'TRF-20260505-6905', 'transfer', 'confirmed', 'outbound', NULL, 23, 3, 'VLADIMIRS SOBOLEVS', '', '+44 7730 777139', NULL, NULL, NULL, NULL, '2026-05-29', '21:00:00', 'WQ537', 'SWANDOR KEMER — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 60.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-05 16:22:12', '2026-06-14 20:21:19', 'Orhan Güven', NULL, 83, 86),
(186, 'TRF-20260505-0648', 'transfer', 'confirmed', 'return', NULL, 23, 3, 'VLADIMIRS SOBOLEVS', '', '+44 7730 777139', NULL, NULL, '08:30:00', NULL, '2026-06-05', '13:05:00', 'WQ596', 'SWANDOR KEMER — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 60.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-05 16:22:12', '2026-06-14 20:21:19', NULL, '08:30:00', NULL, 86),
(187, 'TRF-20260505-3632', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'MARTA SZARPAK', '', '+48 601 508 328', NULL, NULL, NULL, NULL, '2026-05-30', '06:35:00', 'ENT7169', 'SUNLIGHT GARDEN SIDE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 16:30:26', '2026-06-14 20:21:19', NULL, NULL, NULL, 87),
(188, 'TRF-20260505-1759', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'MARTA SZARPAK', '', '+48 601 508 328', NULL, NULL, '10:00:00', NULL, '2026-06-04', '14:10:00', 'RR7106', 'SUNLIGHT GARDEN SIDE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-05 16:30:26', '2026-06-14 20:21:19', 'Yusuf Oğuz', '10:00:00', 73, 87),
(189, 'TRF-20260505-8294', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'ERIK TRIEBER', '', '+49 176 24611114', NULL, NULL, NULL, NULL, '2026-05-31', '08:05:00', 'XQ231', 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 16:44:13', '2026-06-14 20:21:19', NULL, NULL, NULL, 88),
(190, 'TRF-20260505-7730', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'ERIK TRIEBER', '', '+49 176 24611114', NULL, NULL, '19:30:00', NULL, '2026-06-09', '23:50:00', 'XQ230', 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-05 16:44:13', '2026-06-14 20:21:19', 'Ali genç', '19:30:00', 107, 88),
(191, 'TRF-20260505-5319', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'BERKAY KALAYCI', '', '+49 172 4582391', NULL, NULL, NULL, NULL, '2026-05-24', '06:55:00', 'XQ253', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-05 16:49:57', '2026-06-14 20:21:19', 'Fuat Çetin', NULL, 71, 89),
(192, 'TRF-20260505-2085', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'BERKAY KALAYCI', '', '+49 172 4582391', NULL, NULL, '21:00:00', NULL, '2026-06-03', '23:45:00', 'XQ256', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 16:49:57', '2026-06-14 20:21:19', NULL, NULL, NULL, 89),
(193, 'TRF-20260505-7822', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MOHAMMAD İSMAİL', '', '+44 7964 697233', NULL, NULL, NULL, NULL, '2026-05-21', '21:00:00', 'XQ537', 'SWANDOR TOPKAPI PALACE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 16:58:35', '2026-06-14 20:21:20', NULL, NULL, NULL, 90),
(194, 'TRF-20260505-4414', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'MOHAMMAD İSMAİL', '', '+44 7964 697233', NULL, NULL, '10:00:00', NULL, '2026-05-28', '13:05:00', 'XQ596', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 16:58:35', '2026-06-14 20:21:20', NULL, NULL, NULL, 90),
(195, 'TRF-20260505-3290', 'transfer', 'confirmed', 'outbound', NULL, 12, 1, 'WILHELMUS HALLER', '', '+44 7964 697233', NULL, NULL, NULL, NULL, '2026-06-06', '15:15:00', 'XC5021', 'GRAND UYSAL BEACH — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 75.00, 'EUR', NULL, NULL, 1, 1, 45.00, 'EUR', '2026-05-05 17:08:45', '2026-06-14 20:21:20', 'Mehmet Akif Eseoğlu', NULL, 97, 91);
INSERT INTO `bookings` (`id`, `booking_number`, `booking_type`, `booking_status`, `booking_direction`, `tour_id`, `destination_id`, `vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `pickup_location`, `pickup_date`, `pickup_time`, `return_time`, `flight_date`, `flight_time`, `flight_number`, `hotel_address`, `return_transfer`, `return_flight_date`, `return_flight_time`, `return_flight_number`, `return_pickup_time`, `return_hotel_address`, `adults`, `children`, `child_seat`, `total_price`, `currency`, `notes`, `admin_notes`, `is_completed`, `is_outsourced`, `outsource_price`, `outsource_currency`, `created_at`, `updated_at`, `outsource_name`, `outsource_pickup_time`, `outsource_partner_id`, `customer_id`) VALUES
(196, 'TRF-20260505-2054', 'transfer', 'confirmed', 'return', NULL, 12, 1, 'WILHELMUS HALLER', '', '+44 7964 697233', NULL, NULL, '01:00:00', NULL, '2026-06-13', '06:00:00', 'XC5022', 'GRAND UYSAL BEACH — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 75.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-05 17:08:45', '2026-06-14 20:21:20', NULL, NULL, NULL, 91),
(197, 'TRF-20260505-5395', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'FURKAN COŞKUN', '', '+49 175 4186314', NULL, NULL, NULL, NULL, '2026-06-05', '22:40:00', 'FH1026', 'DIAMOND PREMIUM — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 35.00, 'EUR', NULL, NULL, 1, 1, 1200.00, 'TRY', '2026-05-05 17:38:31', '2026-06-14 20:21:20', 'Arda Coşkun', NULL, 94, 92),
(198, 'TRF-20260505-8823', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'FURKAN COŞKUN', '', '+49 175 4186314', NULL, NULL, '09:00:00', NULL, '2026-06-12', '12:45:00', 'XQ158', 'DIAMOND PREMIUM — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 35.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-05 17:38:31', '2026-06-14 20:21:20', NULL, NULL, NULL, 92),
(199, 'TRF-20260505-5715', 'transfer', 'confirmed', 'outbound', NULL, 17, 1, 'AYTEN BAHTİRİ', '', '+49 172 4653276', NULL, NULL, NULL, NULL, '2026-06-07', '06:05:00', 'XQ119', 'RUBI PLATINUM SPA RESORT & SUITES — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 5, 1, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-05 18:02:40', '2026-06-14 20:21:20', 'Mustafa Bayrak', NULL, 16, 93),
(200, 'TRF-20260505-3725', 'transfer', 'confirmed', 'return', NULL, 17, 1, 'AYTEN BAHTİRİ', '', '+49 172 4653276', NULL, NULL, '15:00:00', NULL, '2026-06-17', '19:45:00', 'XQ612', 'RUBI PLATINUM SPA RESORT & SUITES — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 5, 1, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:02:40', '2026-06-14 20:21:20', NULL, NULL, NULL, 93),
(201, 'TRF-20260505-2338', 'transfer', 'confirmed', 'outbound', NULL, 25, 1, 'EDUARD WAGNER', '', '+49 160 2606939', NULL, NULL, NULL, NULL, '2026-06-09', '04:40:00', '4M102', 'DOBEDAN EXCLUSIVE BELEK — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 1200.00, 'TRY', '2026-05-05 18:19:35', '2026-06-14 20:21:20', 'Samet Şimşek', NULL, 106, 94),
(202, 'TRF-20260505-6127', 'transfer', 'confirmed', 'return', NULL, 25, 1, 'EDUARD WGNER', '', '+49 160 2606939', NULL, NULL, '16:00:00', NULL, '2026-06-18', '20:05:00', '4M101', 'DOBEDAN EXCLUSIVE BELEK — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:19:35', '2026-06-14 20:21:20', NULL, NULL, NULL, 95),
(203, 'TRF-20260505-9770', 'transfer', 'confirmed', 'outbound', NULL, 16, 1, 'DIANA LACKA', '', '+371 28 830 309', NULL, NULL, NULL, NULL, '2026-06-15', '08:50:00', 'FH728', 'JUSTINIANO CLUB PARK CONTI — OKURCALAR', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 1, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:26:11', '2026-06-14 20:21:20', NULL, NULL, NULL, 96),
(204, 'TRF-20260505-6168', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'NURIJE OSMANI', '', '+49 176 81083696', NULL, NULL, NULL, NULL, '2026-06-16', '22:45:00', 'XQ121', 'DREAM WORLD HILL — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', 'KARŞILAMA ISMI OSMANI HALJITI', 'KARŞILAMA ISMI OSMANI HALJITI', 0, 0, NULL, NULL, '2026-05-05 18:32:07', '2026-06-14 20:21:20', NULL, NULL, NULL, 97),
(205, 'TRF-20260505-8682', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'NURIJE OSMANI', '', '+49 176 81083696', NULL, NULL, '02:30:00', NULL, '2026-06-26', '06:00:00', 'XQ124', 'DREAM WORLD HILL — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', 'KARŞILAMA ISMI OSMANI HALJITI', 'KARŞILAMA ISMI OSMANI HALJITI', 0, 0, NULL, NULL, '2026-05-05 18:32:07', '2026-06-14 20:21:20', NULL, NULL, NULL, 97),
(206, 'TRF-20260505-8120', 'transfer', 'confirmed', 'outbound', NULL, 24, 1, 'ALEXANDER HORN', '', '+49 176 22855954', NULL, NULL, NULL, NULL, '2026-06-10', '04:40:00', '4M252', 'KIMEROS PARK HOLIDAY VILLAGA — GÖYNÜK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:37:44', '2026-06-14 20:21:20', NULL, NULL, NULL, 98),
(207, 'TRF-20260505-9123', 'transfer', 'confirmed', 'return', NULL, 24, 1, 'ALEXANDER HORN', '', '+49 176 22855954', NULL, NULL, '16:00:00', NULL, '2026-06-17', '20:00:00', '4M351', 'KIMEROS PARK HOLIDAY VILLAGA — GÖYNÜK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:37:44', '2026-06-14 20:21:20', NULL, NULL, NULL, 98),
(208, 'TRF-20260505-7248', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'JOSEF HAIDER', '', '+49 173 3621027', NULL, NULL, NULL, NULL, '2026-06-28', '04:30:00', 'XQ169', 'BARUT HEMERA — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:50:42', '2026-06-14 20:21:21', NULL, NULL, NULL, 99),
(209, 'TRF-20260505-3965', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'JOSEF HAIDER', '', '+49 173 3621027', NULL, NULL, '19:00:00', NULL, '2026-07-05', '23:10:00', 'FH1345', 'BARUT HEMERA — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:50:42', '2026-06-14 20:21:21', NULL, NULL, NULL, 99),
(210, 'TRF-20260505-4540', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'HAKAN BATTIG', '', '+49 162 2056195', NULL, NULL, NULL, NULL, '2026-07-01', '15:35:00', 'XQ219', 'MELAS LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:58:57', '2026-06-14 20:21:21', NULL, NULL, NULL, 100),
(211, 'TRF-20260505-1170', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'HAKAN BATTIG', '', '+49 162 2056195', NULL, NULL, '13:30:00', NULL, '2026-07-14', '16:10:00', 'XQ670', 'MELAS LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 18:58:57', '2026-06-14 20:21:21', NULL, NULL, NULL, 100),
(212, 'TRF-20260505-7070', 'transfer', 'confirmed', 'outbound', NULL, 1, 3, 'SERHAN GÜNEŞ', '', '', NULL, NULL, NULL, NULL, '2026-05-08', '21:30:00', 'VF3044', 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-05 19:04:21', '2026-06-14 20:21:21', 'Sait Uğurlu', NULL, 37, 101),
(213, 'TRF-20260505-0779', 'transfer', 'confirmed', 'return', NULL, 1, 3, 'SERHAN GÜNEŞ', '', '', NULL, NULL, '10:00:00', NULL, '2026-05-10', '13:40:00', NULL, 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-05 19:04:21', '2026-06-14 20:21:21', 'Adem Uğurlı', '10:00:00', 43, 101),
(214, 'TRF-20260505-3106', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'JULIA PETERSEN', '', '+49 174 9802753', NULL, NULL, NULL, NULL, '2026-07-13', '19:50:00', 'XQ187', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:08:51', '2026-06-14 20:21:21', NULL, NULL, NULL, 102),
(215, 'TRF-20260505-7436', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'JULIA PETERSEN', '', '+49 174 9802753', NULL, NULL, '11:15:00', NULL, '2026-07-21', '15:15:00', 'XQ282', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:08:51', '2026-06-14 20:21:21', NULL, NULL, NULL, 102),
(216, 'TRF-20260505-8335', 'transfer', 'confirmed', 'outbound', NULL, 7, 3, 'DENNIS FICEK', '', '+49 174 7047540', NULL, NULL, NULL, NULL, '2026-07-13', '18:30:00', 'XQ241', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 5, 3, 0, 60.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:17:10', '2026-06-14 20:21:21', NULL, NULL, NULL, 103),
(217, 'TRF-20260505-6955', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'DENNIS FICEK', '', '+49 174 7047540', NULL, NULL, '06:30:00', NULL, '2026-07-21', '10:05:00', 'XQ240', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:19:18', '2026-06-14 20:21:21', NULL, NULL, NULL, 103),
(218, 'TRF-20260505-6407', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'MAREK DEBOWSKI', '', '', NULL, NULL, '06:30:00', NULL, '2026-07-20', '10:05:00', 'XQ240', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 3, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:20:41', '2026-06-14 20:21:21', NULL, NULL, NULL, 104),
(219, 'TRF-20260505-8328', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'ANGELA DI MAGGIO', '', '+49 176 61142401', NULL, NULL, NULL, NULL, '2026-07-15', '12:10:00', 'XQ151', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 1, 2, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:28:32', '2026-06-14 20:21:21', NULL, NULL, NULL, 105),
(220, 'TRF-20260505-5607', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'ANGELA DI MAGGIO', '', '+49 176 61142401', NULL, NULL, '09:00:00', NULL, '2026-07-23', '13:05:00', NULL, 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 1, 2, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:28:32', '2026-06-14 20:21:21', NULL, NULL, NULL, 105),
(221, 'TRF-20260505-6949', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'STEFAN PFAFF', '', '+49 176 82168622', NULL, NULL, NULL, NULL, '2026-07-16', '12:15:00', 'XQ665', 'BLUE WATERS CLUB — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:47:24', '2026-06-14 20:21:21', NULL, NULL, NULL, 106),
(222, 'TRF-20260505-4232', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'STEFAN PFAFF', '', '+49 176 82168622', NULL, NULL, '10:00:00', NULL, '2026-07-27', '14:00:00', 'XQ660', 'BLUE WATERS CLUB — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:47:24', '2026-06-14 20:21:21', NULL, NULL, NULL, 106),
(223, 'TRF-20260505-2103', 'transfer', 'confirmed', 'outbound', NULL, 30, 1, 'BEDİRHAN YEKTİR', '', '+49 1575 5748433', NULL, NULL, NULL, NULL, '2026-07-25', '15:30:00', 'XQ127', 'LUMOS DELUXE RESORT — KARGICAK', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 2, 80.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:56:58', '2026-06-14 20:21:22', NULL, NULL, NULL, 107),
(224, 'TRF-20260505-4417', 'transfer', 'confirmed', 'return', NULL, 30, 1, 'BEDİRHAN YEKTİR', '', '+49 1575 5748433', NULL, NULL, '02:00:00', NULL, '2026-08-02', '07:20:00', 'XQ126', 'LUMOS DELUXE RESORT — KARGICAK', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 2, 80.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 19:56:58', '2026-06-14 20:21:22', NULL, NULL, NULL, 107),
(225, 'TRF-20260505-6036', 'transfer', 'confirmed', 'outbound', NULL, 17, 1, 'NEBAHAT YALÇINKAYA', '', '+49 176 97691792', NULL, NULL, NULL, NULL, '2026-08-04', '18:25:00', 'XQ147', 'ASKA JUST IN BEACH — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 20:03:39', '2026-06-14 20:21:22', NULL, NULL, NULL, 108),
(226, 'TRF-20260505-0486', 'transfer', 'confirmed', 'return', NULL, 17, 1, 'NEBAHAT YALÇINKAYA', '', '+49 176 97691792', NULL, NULL, '05:30:00', NULL, '2026-08-14', '10:05:00', 'XQ146', 'ASKA JUST IN BEACH — AVSALLAR', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 20:03:39', '2026-06-14 20:21:22', NULL, NULL, NULL, 108),
(227, 'TRF-20260505-7848', 'transfer', 'confirmed', 'outbound', NULL, 18, 1, 'ANEL CURİC', '', '+436503706560', NULL, NULL, NULL, NULL, '2026-05-14', '22:35:00', 'XQ197', 'HAYDAR PASHA PALACE — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-05 21:02:25', '2026-06-14 20:21:22', 'Antya Turizm', NULL, 57, 109),
(228, 'TRF-20260505-5989', 'transfer', 'confirmed', 'return', NULL, 18, 1, 'ANEL CURİC', '', '+436503706560', NULL, NULL, '11:30:00', NULL, '2026-05-22', '16:05:00', 'XQ196', 'HAYDAR PASHA PALACE — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-05 21:02:25', '2026-06-14 20:21:22', 'Hakan Akgül', '11:30:00', 69, 109),
(229, 'TRF-20260505-0159', 'transfer', 'confirmed', 'outbound', NULL, NULL, NULL, 'NATALIIA LUNINA', '', '+380 68 139 7304', NULL, NULL, NULL, NULL, '2026-06-06', '02:00:00', NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', 'ANTALYA OTOGARDAN ALINACAK', NULL, 1, 1, 1600.00, 'TRY', '2026-05-05 23:10:46', '2026-06-14 20:21:22', 'Europa Vip', NULL, 51, 110),
(230, 'TRF-20260505-4356', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'FERNANDO GALLARDO CAMPOS', '', '', NULL, NULL, NULL, NULL, '2026-07-18', '19:15:00', 'BT715', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:18:44', '2026-06-14 20:21:22', NULL, NULL, NULL, 111),
(232, 'TRF-20260505-8094', 'transfer', 'confirmed', 'outbound', NULL, 26, 3, 'KATI', '', '+49 171 3211491', NULL, NULL, NULL, NULL, '2026-08-11', '12:20:00', 'XQ185', 'NOVUM GARDEN — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 11, 0, 0, 60.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:23:49', '2026-06-14 20:21:22', NULL, NULL, NULL, 112),
(233, 'TRF-20260505-8888', 'transfer', 'confirmed', 'return', NULL, 26, 3, 'KATI', '', '+49 171 3211491', NULL, NULL, '07:15:00', NULL, '2026-08-20', '11:45:00', 'XC102', 'NOVUM GARDEN — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 11, 0, 0, 60.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:23:49', '2026-06-14 20:21:22', NULL, NULL, NULL, 112),
(234, 'TRF-20260505-5803', 'transfer', 'confirmed', 'outbound', NULL, 31, 1, 'CHRISTIAN HAGEN', '', '', NULL, NULL, NULL, NULL, '2026-08-12', '20:10:00', 'XQ667', 'QUEENS PARK GÖYNÜK — GÖYNÜK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 0.00, 'EUR', NULL, 'EMRE GİDEBİLİR ÜCRET SORULACAK', 0, 0, NULL, NULL, '2026-05-05 23:27:55', '2026-06-14 20:21:22', NULL, NULL, NULL, 113),
(235, 'TRF-20260505-9909', 'transfer', 'confirmed', 'return', NULL, 31, 1, 'CHRISTIAN HAGEN', '', '', NULL, NULL, '05:00:00', NULL, '2026-08-24', '09:00:00', 'XQ160', 'QUEENS PARK GÖYNÜK — GÖYNÜK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 0.00, 'EUR', NULL, 'EMRE GİDEBİLİR ÜCRET SORULACAK', 0, 0, NULL, NULL, '2026-05-05 23:27:55', '2026-06-14 20:21:22', NULL, NULL, NULL, 113),
(236, 'TRF-20260505-9436', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'SARAH ONEILL', '', '+44 7740 101686', NULL, NULL, NULL, NULL, '2026-08-14', '21:55:00', NULL, 'PALOMA GRIDA — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, 'DÖNÜŞ TARİHİ SORULACAK', 0, 0, NULL, NULL, '2026-05-05 23:33:14', '2026-06-14 20:21:22', NULL, NULL, NULL, 114),
(237, 'TRF-20260505-1398', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'CHRISTOPH HENEL', '', '+49 170 6160992', NULL, NULL, NULL, NULL, '2026-08-15', '06:05:00', '4M874', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:36:50', '2026-06-14 20:21:22', NULL, NULL, NULL, 115),
(238, 'TRF-20260505-5302', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'CHRISTOPH HENEL', '', '+49 170 6160992', NULL, NULL, '20:30:00', NULL, '2026-08-22', '23:00:00', '4M873', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:36:50', '2026-06-14 20:21:22', NULL, NULL, NULL, 115),
(239, 'TRF-20260505-7106', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'ILKAY KOPARAN', '', '+49 178 8082590', NULL, NULL, '00:00:00', NULL, '2026-05-08', '04:05:00', 'XQ156', 'LIMAK LARA DELUXE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-05 23:40:21', '2026-06-14 20:21:22', 'Mustafa Esen', '00:00:00', 31, 30),
(242, 'TRF-20260505-5318', 'transfer', 'confirmed', 'outbound', NULL, 28, 1, 'ŞENAY TURACAK', '', '+31 6 46139890', NULL, NULL, NULL, NULL, '2026-08-19', NULL, 'OR3001', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:44:01', '2026-06-14 20:21:23', NULL, NULL, NULL, 116),
(243, 'TRF-20260505-6983', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'ŞENAY TURACAK', '', '+31 6 46139890', NULL, NULL, NULL, NULL, '2026-08-25', NULL, 'OR3026', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:44:01', '2026-06-14 20:21:23', NULL, NULL, NULL, 116),
(244, 'TRF-20260505-9040', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'GINO MOLL', '', '+49 1515 9860218', NULL, NULL, NULL, NULL, '2026-08-21', '16:50:00', 'XQ615', 'SIDE PRENSES HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:47:08', '2026-06-14 20:21:23', NULL, NULL, NULL, 117),
(245, 'TRF-20260505-8142', 'transfer', 'confirmed', 'outbound', NULL, 24, NULL, 'RACHEL GILL', '', '+44 7761 308432', NULL, NULL, NULL, NULL, '2026-09-07', '23:15:00', 'TOM574', 'RIXOS SUNGATE BELDIBI — BELDIBI', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 35.00, 'GBP', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:59:33', '2026-06-14 20:21:23', NULL, NULL, NULL, 118),
(246, 'TRF-20260505-5770', 'transfer', 'confirmed', 'return', NULL, 24, NULL, 'RACHEL GILL', '', '+44 7761 308432', NULL, NULL, '20:00:00', NULL, '2026-09-15', '00:25:00', 'TOM575', 'RIXOS SUNGATE BELDIBI — BELDIBI', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 35.00, 'GBP', NULL, NULL, 0, 0, NULL, NULL, '2026-05-05 23:59:33', '2026-06-14 20:21:23', NULL, NULL, NULL, 118),
(247, 'TRF-20260506-4485', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'MARCUS HUMMEL', '', '', NULL, NULL, NULL, NULL, '2026-09-08', '16:35:00', 'PC5030', 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 0.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:03:04', '2026-06-14 20:21:23', NULL, NULL, NULL, 119),
(248, 'TRF-20260506-1478', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'MARCUS HUMMEL', '', '', NULL, NULL, NULL, NULL, '2026-08-15', NULL, NULL, 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 0.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:03:04', '2026-06-14 20:21:23', NULL, NULL, NULL, 119),
(249, 'TRF-20260506-1931', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'DANIELA RÖPCKE', '', '+49 1520 2821124', NULL, NULL, NULL, NULL, '2026-09-21', '15:20:00', 'XQ171', 'SEADEN DE MAR — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:05:29', '2026-06-14 20:21:23', NULL, NULL, NULL, 120),
(250, 'TRF-20260506-1150', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'DANIELA RÖPCKE', '', '+49 1520 2821124', NULL, NULL, '12:00:00', NULL, '2026-09-28', '16:10:00', 'XQ670', 'SEADEN DE MAR — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:05:29', '2026-06-14 20:21:23', NULL, NULL, NULL, 120),
(251, 'TRF-20260506-6085', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'MALTE EHLERT', '', '+49 176 97739555', NULL, NULL, NULL, NULL, '2026-10-02', '12:15:00', 'XQ665', 'SEADEN SEA WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:08:56', '2026-06-14 20:21:23', NULL, NULL, NULL, 121),
(252, 'TRF-20260506-4230', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'MALTE EHLERT', '', '+49 176 97739555', NULL, NULL, '14:00:00', NULL, '2026-10-10', '18:00:00', 'XQ664', 'SEADEN SEA WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:08:56', '2026-06-14 20:21:23', NULL, NULL, NULL, 121),
(253, 'TRF-20260506-3257', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'MAIK PRETZ', '', '+49 160 6475769', NULL, NULL, NULL, NULL, '2026-10-08', '05:20:00', 'XQ237', 'ROYAL TAJ MAHAL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:12:08', '2026-06-14 20:21:23', NULL, NULL, NULL, 122),
(254, 'TRF-20260506-8918', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'MAIK PRETZ', '', '+49 160 6475769', NULL, NULL, '16:00:00', NULL, '2026-10-15', '20:00:00', 'XQ238', 'ROYAL TAJ MAHAL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 00:12:08', '2026-06-14 20:21:23', NULL, NULL, NULL, 122),
(255, 'TRF-20260506-5786', 'transfer', 'confirmed', 'outbound', NULL, NULL, 1, 'IGOR OSIPCHUK', '', '+380 96 475 5923', NULL, NULL, NULL, NULL, '2026-05-11', '13:10:00', 'XQ657', 'NIRVANA DOLCE VITA — TEKİROVA', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 50.00, 'USD', NULL, NULL, 1, 1, 1600.00, 'TRY', '2026-05-06 17:05:57', '2026-06-14 20:21:23', 'Önder Günal', NULL, 20, 123),
(256, 'TRF-20260506-9996', 'transfer', 'confirmed', 'return', NULL, NULL, 1, 'IGOR OSIPCHUK', '', '+380 96 475 5923', NULL, NULL, '04:00:00', NULL, '2026-05-20', '08:30:00', 'XQ656', 'NIRVANA DOLCE VITA — TEKİROVA', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 50.00, 'USD', NULL, NULL, 1, 1, 1550.00, 'TRY', '2026-05-06 17:05:57', '2026-06-14 20:21:23', 'Veysel Çaçan', '04:00:00', 65, 123),
(257, 'TRF-20260506-2204', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'CAN KLEISMAN', '', '+49 163 6969895', NULL, NULL, '19:00:00', NULL, '2026-05-07', '23:00:00', 'PC5073', 'DREAM WATER WORLD — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-06 19:49:52', '2026-06-14 20:21:23', NULL, NULL, NULL, 31),
(258, 'TRF-20260506-0328', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'JEAN SPENCER', '', '', NULL, NULL, '07:00:00', NULL, '2026-05-07', '10:30:00', 'XC4102', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-06 19:53:16', '2026-06-14 20:21:23', NULL, NULL, NULL, 124),
(259, 'TRF-20260506-9941', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'OLGA FRANK', '', '+49 162 8704086', NULL, NULL, NULL, NULL, '2026-07-05', '18:15:00', 'XQ173', 'SIDE PRENSES HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 21:02:09', '2026-06-14 20:21:24', NULL, NULL, NULL, 125),
(260, 'TRF-20260506-6352', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'OLGA FRANK', '', '+49 162 8704086', NULL, NULL, '05:30:00', NULL, '2026-07-14', '09:00:00', 'XC146', 'SIDE PRENSES HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-06 21:02:09', '2026-06-14 20:21:24', NULL, NULL, NULL, 125),
(261, 'TRF-20260507-3077', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'JAY HEBBORN', '', '+44 7870 884232', NULL, NULL, NULL, NULL, '2026-05-07', '14:00:00', 'EZY8555', 'ADALYA ELITE LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 1400.00, 'TRY', NULL, NULL, 1, 0, NULL, NULL, '2026-05-07 09:02:38', '2026-06-14 20:21:24', NULL, NULL, NULL, 126),
(262, 'TRF-20260507-9528', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'JOSE ORLANDO LOPEZ', '', '', NULL, NULL, '09:30:00', NULL, '2026-05-08', '12:35:00', 'TK2415', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-07 10:21:31', '2026-06-14 20:21:24', NULL, NULL, NULL, 127),
(264, 'TRF-20260507-8094', 'transfer', 'confirmed', 'outbound', NULL, 24, 1, 'ANDRIAN TONNY PREDESCU', '', '+44 7565 716144', NULL, NULL, NULL, NULL, '2026-06-10', '20:40:00', 'EZY3059', 'DOSINIA LUXURY RESORT — BELDİBİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 1, 40.00, 'EUR', NULL, NULL, 1, 1, 1200.00, 'TRY', '2026-05-07 17:55:28', '2026-06-14 20:21:24', 'Ebubekir Varlık', NULL, 108, 128),
(265, 'TRF-20260507-4324', 'transfer', 'confirmed', 'return', NULL, 24, 1, 'ANDRIAN TONNY PREDESCU', '', '+44 7565 716144', NULL, NULL, '17:00:00', NULL, '2026-06-17', '21:40:00', 'EZY3060', 'DOSINIA LUXURY RESORT — BELDİBİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 1, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-07 17:55:28', '2026-06-14 20:21:24', NULL, NULL, NULL, 128),
(266, 'TRF-20260507-6100', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'QUAIMA AIT EL HADJ', '', '+31 6 36032967', NULL, NULL, '13:00:00', NULL, '2026-05-09', '15:30:00', 'XC106', 'MIRACKLE RESORT — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-07 18:03:53', '2026-06-14 20:21:24', 'Osman Demir', '13:00:00', 40, 12),
(267, 'TRF-20260507-5165', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'LAURO LAURANNE', '', '+32 476 84 81 11', NULL, NULL, NULL, NULL, '2026-08-11', '10:50:00', 'FH8013', 'LIMAK ARCADIA SPORT BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-07 18:10:18', '2026-06-14 20:21:24', NULL, NULL, NULL, 129),
(268, 'TRF-20260507-6729', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'LAURO LAURANNE', '', '+32 476 84 81 11', NULL, NULL, '17:45:00', NULL, '2026-08-18', '21:50:00', 'FH8116', 'LIMAK ARCADIA SPORT BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-07 18:10:18', '2026-06-14 20:21:24', NULL, NULL, NULL, 129),
(269, 'TRF-20260508-7185', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'INEZ WNEK', '', '+4915214356687', NULL, NULL, '18:15:00', NULL, '2026-05-08', '20:50:00', NULL, 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-08 00:40:57', '2026-06-14 20:21:24', NULL, NULL, NULL, 63),
(270, 'TRF-20260508-1576', 'transfer', 'confirmed', 'outbound', NULL, 31, 1, 'KARINA PUNCULS', '', '+47 908 73 518', NULL, NULL, NULL, NULL, '2026-06-09', '11:35:00', 'DY1260', 'MIRADA DEL MAR — GÖYNÜK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 1200.00, 'TRY', '2026-05-08 00:44:30', '2026-06-14 20:21:24', 'Murat Öndeğirmen', NULL, 104, 130),
(271, 'TRF-20260508-4114', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'VLADISLAV MASISIKI', '', '', NULL, NULL, '18:30:00', NULL, '2026-05-08', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-08 00:57:21', '2026-06-14 20:21:25', 'Kadir aydın', '18:30:00', 35, 131),
(272, 'TRF-20260508-7711', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'EBUBEKİR GÜREL', '', '+49 160 2603211', NULL, NULL, NULL, NULL, '2026-05-18', '00:45:00', 'XQ155', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 2, 25.00, 'EUR', NULL, NULL, 1, 1, 750.00, 'TRY', '2026-05-08 09:01:24', '2026-06-14 20:21:25', 'Yılmaz Acar', NULL, 39, 132),
(273, 'TRF-20260508-4810', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'EBUBEKİR GÜREL', '', '+49 160 2603211', NULL, NULL, '09:30:00', NULL, '2026-05-26', '13:05:00', 'XQ152', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 2, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-08 09:01:24', '2026-06-14 20:21:25', NULL, NULL, NULL, 132),
(274, 'TRF-20260508-1514', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'NADINE LÖFFLER', '', '+49 171 7435456', NULL, NULL, NULL, NULL, '2026-05-24', '19:05:00', 'XC131', 'DREAM WATER WORLD — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-08 09:04:51', '2026-06-14 20:21:25', 'Yusuf Oğuz', NULL, 73, 133),
(275, 'TRF-20260508-1224', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'NADINE LÖFFLER', '', '+49 171 7435456', NULL, NULL, '03:00:00', NULL, '2026-06-02', '06:40:00', 'XQ276', 'DREAM WATER WORLD — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', '!!!!DÖNÜŞ ÜCRETİ ÖDENDİ ÜCRET ALINMAYACAK !!!!', NULL, 1, 1, NULL, 'EUR', '2026-05-08 09:04:51', '2026-06-14 20:21:25', 'Mustafa Bayrak', '03:00:00', 16, 133),
(276, 'TRF-20260508-3364', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'ANGIE STARMANS', '', '+31 6 30034315', NULL, NULL, '19:30:00', NULL, '2026-05-08', '22:45:00', 'XC112', 'DREAM WORLD AQUA — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 1, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-08 09:08:45', '2026-06-14 20:21:25', 'Yasin Kuru', '19:30:00', 36, 15),
(277, 'TRF-20260508-1758', 'transfer', 'confirmed', 'outbound', NULL, 18, 1, 'LORI HASELL', '', '+44 7539 946621', NULL, NULL, NULL, NULL, '2026-05-31', '20:20:00', 'LS197', 'EFTALIA BLUE — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 2, 3, 1, 70.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-08 09:15:10', '2026-06-14 20:21:25', NULL, NULL, NULL, 134),
(279, 'TRF-20260508-1671', 'transfer', 'confirmed', 'return', NULL, 26, 1, 'EMIL GODLA', '', '+44 7442 912255', NULL, NULL, '16:00:00', NULL, '2026-05-09', '21:00:00', 'W95332', 'LIU RESORT — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-08 22:03:53', '2026-06-14 20:21:25', 'Ali Taş', '16:00:00', 25, 135),
(280, 'TRF-20260508-4970', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'ABDELWAHED ADAHRI', '', '+31 6 14532584', NULL, NULL, NULL, NULL, '2026-05-09', '14:50:00', 'XQ547', 'KLEOPATRA DREAMS BEACH — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 65.00, 'EUR', 'EL BAGAJI VAR VALİZ YOK', NULL, 1, 1, 50.00, 'EUR', '2026-05-08 22:11:10', '2026-06-14 20:21:25', 'Yahya Hamurcu', NULL, 41, 136),
(281, 'TRF-20260508-2402', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'SARKA ROHACOVA', '', '', NULL, NULL, '05:00:00', NULL, '2026-05-10', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-08 22:14:56', '2026-06-14 20:21:25', 'Yunus Emre Kozanlı', '05:00:00', 42, 137),
(282, 'TRF-20260508-4551', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'CHRISTIAN HENRIKSEN', '', '', NULL, NULL, '02:00:00', NULL, '2026-05-09', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-08 22:15:55', '2026-06-14 20:21:25', NULL, NULL, NULL, 138),
(283, 'TRF-20260508-7007', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'ZAID NANAWADI', '', '+44 7471 267039', NULL, NULL, '06:30:00', NULL, '2026-05-11', '10:30:00', 'XC4102', 'BOSPHORUS HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 35.00, 'GBP', NULL, NULL, 1, 0, NULL, NULL, '2026-05-08 22:44:10', '2026-06-14 20:21:25', NULL, NULL, NULL, 65),
(284, 'TRF-20260509-1420', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'TATIANA LAZEROVA', '', '', NULL, NULL, NULL, NULL, '2026-05-09', '13:45:00', 'SU2700', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-09 10:16:59', '2026-06-14 20:21:25', NULL, NULL, NULL, 139),
(285, 'TRF-20260509-9341', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'TUFAN YAZICI', '', '+4917628302727', NULL, NULL, NULL, NULL, '2026-05-10', '04:35:00', '4M628', 'SELECTUM LUXURY RESORT — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-09 22:30:24', '2026-06-14 20:21:25', 'Ice travel', NULL, 45, 140),
(286, 'TRF-20260509-0813', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'TUFAN YAZICI', '', '+4917628302727', NULL, NULL, '20:30:00', NULL, '2026-05-16', '23:50:00', 'XQ230', 'SELECTUM LUXURY RESORT — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-09 22:30:24', '2026-06-14 20:21:25', 'Yusuf Şanal', '20:30:00', 59, 140),
(287, 'TRF-20260509-3837', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'AXEL KERLL', '', '+49 171 2662089', NULL, NULL, NULL, NULL, '2026-05-22', '04:35:00', '4M428', 'DELPHIN DIVA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-09 22:38:46', '2026-06-14 20:21:26', NULL, NULL, NULL, 141),
(288, 'TRF-20260509-3493', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'AXEL KERLL', '', '+49 171 2662089', NULL, NULL, '16:30:00', NULL, '2026-05-31', '19:10:00', '4M427', 'DELPHIN DIVA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-09 22:38:46', '2026-06-14 20:21:26', NULL, NULL, NULL, 141),
(290, 'TRF-20260510-2187', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'FADIL BEGANAJ', '', '+49 174 3958950', NULL, NULL, NULL, NULL, '2026-07-03', '12:20:00', 'XQ103', 'MELAS RESORT — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-10 14:35:38', '2026-06-14 20:21:26', NULL, NULL, NULL, 142),
(291, 'TRF-20260510-8823', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'FADIL BEGANAJ', '', '+49 174 3958950', NULL, NULL, '12:30:00', NULL, '2026-07-10', '16:40:00', 'XQ102', 'MELAS RESORT — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-10 14:35:38', '2026-06-14 20:21:26', NULL, NULL, NULL, 142),
(292, 'TRF-20260510-4007', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'ZEESHAN SAGHIR', '', '+447904972954', NULL, NULL, '01:00:00', NULL, '2026-05-11', '03:40:00', 'PC1840', 'WASHINGTON RESORT — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-10 20:19:35', '2026-06-14 20:21:26', 'Fatih Çil', '01:00:00', 50, 143),
(293, 'TRF-20260510-5026', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'BETHANY HANCOCK', '', '+497749164267', NULL, NULL, NULL, NULL, '2026-05-13', '18:30:00', 'EZY5607', 'LARA GARDEN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', 'UÇAK 18 MAYIS 01.10DA KALKIYOR MİSAFİR 17 MAYIS 19:30 DA ALINACAK', NULL, 0, 0, NULL, NULL, '2026-05-10 20:30:31', '2026-06-14 20:21:26', NULL, NULL, NULL, 144),
(294, 'TRF-20260510-6317', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'BETHANY HANCOCK', '', '+497749164267', NULL, NULL, '19:30:00', NULL, '2026-05-17', '22:15:00', 'EZY5608', 'LARA GARDEN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-10 20:30:31', '2026-06-14 20:21:26', NULL, '19:30:00', NULL, 144),
(295, 'TRF-20260510-9965', 'transfer', 'confirmed', 'outbound', NULL, 7, 2, 'RALF RIEGER', '', '+491638641719', NULL, NULL, NULL, NULL, '2026-05-18', '04:30:00', '4M796', 'ROYAL TAJ MAHAL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', 'ÖNCELİK VİTO MAYBACH', NULL, 1, 1, 1200.00, 'TRY', '2026-05-10 20:42:07', '2026-06-14 20:21:26', 'Mehmet Zülfi Göktepe', NULL, 63, 145),
(296, 'TRF-20260510-7475', 'transfer', 'confirmed', 'return', NULL, 7, 2, 'RALF RIEGER', '', '+491638641719', NULL, NULL, '18:00:00', NULL, '2026-05-29', '22:40:00', 'XQ246', 'ROYAL TAJ MAHAL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 30.00, 'EUR', 'ÖNCELİK VİTO MAYBACH', NULL, 1, 1, 30.00, 'EUR', '2026-05-10 20:42:07', '2026-06-14 20:21:26', 'Erdal Özbey', '18:00:00', 82, 145),
(297, 'TRF-20260510-8921', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'Julia Haubenreisser', '', '+49 1520 3821075', NULL, NULL, NULL, NULL, '2026-05-24', '08:05:00', 'PC5014', 'TRENDY VERBANA BEACH — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-10 20:45:32', '2026-06-14 20:21:26', 'Yasin Kuru', NULL, 36, 146),
(298, 'TRF-20260512-8684', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'SİBEL PADURARU', '', '+4917663417319', NULL, NULL, NULL, NULL, '2026-05-12', '12:20:00', 'XQ103', 'BELEK MERKEZ — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-12 09:27:58', '2026-06-14 20:21:26', 'Süleyman Kıdık', NULL, 47, 147),
(299, 'TRF-20260512-5145', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'LUKMAN ALIZADE', '', '+447704449147', NULL, NULL, '10:30:00', NULL, '2026-05-19', '14:25:00', 'XQ594', 'DREAM WORLD PALACE — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-12 09:29:26', '2026-06-14 20:21:26', NULL, NULL, NULL, 148),
(300, 'TRF-20260512-3913', 'transfer', 'confirmed', 'return', NULL, 12, 1, 'ABDELWAHED ADAHRİ', '', '+31614532584', NULL, NULL, '01:00:00', NULL, '2026-05-13', '06:10:00', 'XQ546', 'KLEOPATRA DREAMS BEACH — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-12 09:33:10', '2026-06-14 20:21:26', 'Ilyada Yıldız', '01:00:00', 52, 149),
(301, 'TRF-20260512-0781', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'JENS KHULMANN', '', '+491736520273', NULL, NULL, '05:00:00', NULL, '2026-05-14', '07:15:00', 'XQ218', 'SIDE AMOUR HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-12 09:36:05', '2026-06-14 20:21:26', NULL, NULL, NULL, 150),
(302, 'TRF-20260512-2906', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'MARIO MIHAILA', '', '+447955458019', NULL, NULL, NULL, NULL, '2026-05-14', '18:00:00', 'XC4133', 'SUNTHALI HOTEL — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-12 09:40:16', '2026-06-14 20:21:27', NULL, NULL, NULL, 151),
(303, 'TRF-20260512-7243', 'transfer', 'confirmed', 'outbound', NULL, 28, 1, 'THOMAS TIPTHORPE', '', '+447476990932', NULL, NULL, NULL, NULL, '2026-05-14', '21:55:00', 'EZY8557', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-12 09:46:42', '2026-06-14 20:21:27', 'Ahmet Karadağlı', NULL, 58, 152),
(304, 'TRF-20260512-3242', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'JAMAL HUSSAIN', '', '+447306257303', NULL, NULL, NULL, NULL, '2026-05-29', '19:00:00', 'XQ583', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-12 09:52:07', '2026-06-14 20:21:27', NULL, NULL, NULL, 153),
(305, 'TRF-20260512-9695', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'ROSMARIE FURRER', '', '', NULL, NULL, '07:00:00', NULL, '2026-05-18', NULL, 'LX8187', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-12 12:00:07', '2026-06-14 20:21:27', NULL, NULL, NULL, 154),
(306, 'TRF-20260512-3335', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'TATIANA LAZEROVA', '', '', NULL, NULL, '10:30:00', NULL, '2026-05-22', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-12 12:02:47', '2026-06-14 20:21:27', NULL, NULL, NULL, 139),
(307, 'TRF-20260512-2680', 'transfer', 'confirmed', 'return', NULL, NULL, NULL, 'RICARDO HERKENS', '', '', NULL, NULL, '02:30:00', NULL, '2026-05-13', NULL, NULL, 'CATS GARDEN HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-12 12:04:23', '2026-06-14 20:21:27', NULL, NULL, NULL, 155),
(308, 'TRF-20260513-5443', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'ANGELİKA BÜHRİNG', '', '', NULL, NULL, NULL, NULL, '2026-05-14', '15:20:00', 'XQ171', 'SEADEN VALENTINE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-13 11:30:51', '2026-06-14 20:21:27', NULL, NULL, NULL, 156),
(309, 'TRF-20260513-4126', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'KARİM KOPONEN', '', '', NULL, NULL, '06:00:00', NULL, '2026-05-16', NULL, NULL, 'CATS GARDEN HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-13 11:31:50', '2026-06-14 20:21:27', NULL, NULL, NULL, 157),
(310, 'TRF-20260513-8616', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'ŞENİZ POLATCI', '', '+49 178 5495240', NULL, NULL, NULL, NULL, '2026-08-04', '15:20:00', 'XQ171', 'SEADEN SEA WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-13 11:38:07', '2026-06-14 20:21:27', NULL, NULL, NULL, 158),
(311, 'TRF-20260513-4555', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'ŞENİZ POLATCI', '', '+49 178 5495240', NULL, NULL, '12:00:00', NULL, '2026-08-11', '16:10:00', 'XQ670', 'SEADEN SEA WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-13 11:38:07', '2026-06-14 20:21:27', NULL, NULL, NULL, 158),
(312, 'TRF-20260513-4863', 'transfer', 'confirmed', 'return', NULL, 24, 1, 'Tatyana Kapralenko', '', '+380 97 653 8935', NULL, NULL, '21:00:00', NULL, '2026-05-13', NULL, 'FH1897', 'ALERIA BELPORT — BELDİBİ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 2000.00, 'TRY', NULL, NULL, 1, 1, 1100.00, 'TRY', '2026-05-13 11:43:04', '2026-06-14 20:21:27', 'Ahmet Kocaman', '21:00:00', 56, 159),
(313, 'TRF-20260513-2329', 'transfer', 'confirmed', 'return', NULL, 20, 1, 'Vithussan Nanthagopalan', '', '+44 7453 392227', NULL, NULL, '19:00:00', NULL, '2026-05-13', '22:40:00', 'EZY2552', 'GRAVEL SELECT HOTEL — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 2000.00, 'TRY', NULL, NULL, 1, 1, 1300.00, 'TRY', '2026-05-13 11:46:24', '2026-06-14 20:21:27', 'Ahmet Kocaman', '19:00:00', 56, 160),
(314, 'TRF-20260514-0965', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'NELLE WILSON', '', '+44 7742 454165', NULL, NULL, NULL, NULL, '2026-05-14', '19:50:00', 'WK95331', 'WASHINGTON RESORT — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-14 14:28:43', '2026-06-14 20:21:28', 'Mehmet Ali Öztaş', NULL, 23, 161),
(315, 'TRF-20260514-1124', 'transfer', 'confirmed', 'outbound', NULL, 31, 1, 'SERGEİ KUTERGİN', '', '+3725050625', NULL, NULL, NULL, NULL, '2026-05-25', '08:30:00', 'XQ497', 'KİLİKYA PALACE — GÖYNÜK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 1150.00, 'TRY', '2026-05-14 17:47:57', '2026-06-14 20:21:28', 'Sefa Bingöl', NULL, 76, 162),
(316, 'TRF-20260514-8296', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'ALEXANDRA IHLE', '', '+49 1577 9343652', NULL, NULL, NULL, NULL, '2026-06-23', NULL, 'XQ275', 'SIDE MARE — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-14 17:57:37', '2026-06-14 20:21:28', NULL, NULL, NULL, 163),
(317, 'TRF-20260514-1116', 'transfer', 'confirmed', 'outbound', NULL, NULL, NULL, 'SIMON SIMEONOV', '', '+31 6 37384270', NULL, NULL, NULL, NULL, '2026-08-11', '14:10:00', 'XQ283', NULL, 0, NULL, NULL, NULL, NULL, NULL, 11, 0, 0, 75.00, 'EUR', 'KONUMA GİDECEKLER', NULL, 0, 0, NULL, NULL, '2026-05-14 18:08:18', '2026-06-14 20:21:28', NULL, NULL, NULL, 164),
(318, 'TRF-20260514-9648', 'transfer', 'confirmed', 'return', NULL, NULL, NULL, 'SIMON SIMEONOV', '', '+31 6 37384270', NULL, NULL, '10:30:00', NULL, '2026-08-25', '15:00:00', 'XQ182', NULL, 0, NULL, NULL, NULL, NULL, NULL, 11, 0, 0, 75.00, 'EUR', 'KONUMA GİDECEKLER', NULL, 0, 0, NULL, NULL, '2026-05-14 18:08:18', '2026-06-14 20:21:28', NULL, NULL, NULL, 164),
(319, 'TRF-20260514-0175', 'transfer', 'confirmed', 'return', NULL, 1, 3, 'SUHAILA YONAN', '', 'YONAN', NULL, NULL, '18:00:00', NULL, '2026-05-17', NULL, 'PC5003', 'ARNOR DELUXE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 1500.00, 'TRY', '2026-05-14 18:20:41', '2026-06-14 20:21:28', NULL, '18:00:00', NULL, 165),
(320, 'TRF-20260515-3162', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'Dylen de noijer', '', '+31618323023', NULL, NULL, '14:30:00', NULL, '2026-05-22', '17:05:00', 'OR3046', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-15 15:03:14', '2026-06-14 20:21:28', NULL, NULL, NULL, 166),
(321, 'TRF-20260515-9243', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'KATARINA DEDIC', '', '', NULL, NULL, '04:30:00', NULL, '2026-05-21', '07:00:00', 'XC2108', 'SELECTUM LUXURY RESORT — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-15 15:09:42', '2026-06-14 20:21:28', 'Tolgahan Güven', '04:30:00', 67, 167),
(322, 'TRF-20260515-5277', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'Fırat vurucu', '', '+4522331829', NULL, NULL, NULL, NULL, '2026-05-15', '23:55:00', 'PC5026', 'WYNDHAM GARDEN LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-15 15:17:01', '2026-06-14 20:21:28', 'Yılmaz Acar', NULL, 39, 168),
(323, 'TRF-20260515-9147', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'Tabea Theilen', '', '+49 1573 2529253', NULL, NULL, NULL, NULL, '2026-07-16', '04:45:00', 'XQ251', 'SEADEN QUALITY — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-15 22:54:42', '2026-06-14 20:21:28', NULL, NULL, NULL, 169),
(324, 'TRF-20260515-7562', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'Tabea Theilen', '', '+49 1573 2529253', NULL, NULL, '16:00:00', NULL, '2026-07-25', '20:20:00', 'XQ250', 'SEADEN QUALITY — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-15 22:54:42', '2026-06-14 20:21:28', NULL, NULL, NULL, 169),
(326, 'TRF-20260516-8383', 'transfer', 'confirmed', 'outbound', NULL, 6, 3, 'SIMON STAIBER', '', '+49 160 1226969', NULL, NULL, NULL, NULL, '2026-05-20', '05:10:00', 'XQ163', 'ALBA RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 7, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-16 19:39:39', '2026-06-14 20:21:29', 'Necla Zorlu', NULL, 66, 170),
(327, 'TRF-20260516-7126', 'transfer', 'confirmed', 'return', NULL, 6, 3, 'SIMON STAIBER', '', '+49 160 1226969', NULL, NULL, '17:00:00', NULL, '2026-05-29', '21:00:00', NULL, 'ALBA RESORT — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 7, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-16 19:39:39', '2026-06-14 20:21:29', 'Erdal Özbey', '17:00:00', 82, 170),
(328, 'TRF-20260516-0966', 'transfer', 'confirmed', 'return', NULL, 19, 3, 'ARTEM KUNGURZEV', '', '+49 1525 5777794', NULL, NULL, '17:30:00', NULL, '2026-05-17', '22:00:00', NULL, 'LIMAK ARCADIA SPORT BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 7, 0, 0, 50.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-16 20:43:56', '2026-06-14 20:21:29', 'Mehmet Eseoğlu', '17:30:00', 61, 171),
(329, 'TRF-20260516-3629', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'SARAH HERWEG', '', '+49 176 82252615', NULL, NULL, NULL, NULL, '2026-05-23', '07:55:00', 'XC133', 'WATER SIDE HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-16 20:46:48', '2026-06-14 20:21:29', NULL, NULL, NULL, 172),
(330, 'TRF-20260516-2314', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'SARAH HERWEG', '', '+49 176 82252615', NULL, NULL, '18:00:00', NULL, '2026-06-06', '22:00:00', 'XQ254', 'WATER SIDE HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', 'GELİŞ ÜCRETİDE ALINACAK TOPLAM 90€', NULL, 1, 1, 1400.00, 'TRY', '2026-05-16 20:46:48', '2026-06-14 20:21:29', 'Abdullah Akgün', '18:00:00', 98, 172),
(331, 'TRF-20260516-2390', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'TIRZA VAN ROOIJEN', '', '+31 6 41557064', NULL, NULL, '04:45:00', NULL, '2026-05-18', '08:00:00', NULL, 'SELECTUM FAMILY RESORT SIDE — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 2600.00, 'TRY', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-16 20:50:44', '2026-06-14 20:21:29', 'Hüseyin Asar', '04:45:00', 62, 173),
(332, 'TRF-20260516-3103', 'transfer', 'confirmed', 'outbound', NULL, 19, 3, 'RYAN BURNS', '', '+44 7594 615695', NULL, NULL, NULL, NULL, '2026-08-21', '14:15:00', 'EZY2141', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 11, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-16 20:57:42', '2026-06-14 20:21:29', NULL, NULL, NULL, 174),
(333, 'TRF-20260516-5038', 'transfer', 'confirmed', 'return', NULL, 19, 3, 'RYAN BURNS', '', '+44 7594 615695', NULL, NULL, '12:20:00', NULL, '2026-08-28', '15:20:00', 'EZY2142', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 11, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-16 20:57:42', '2026-06-14 20:21:29', NULL, NULL, NULL, 174),
(334, 'TRF-20260516-5341', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'JULIENNE HOFFMEISTER', '', '+49 1523 3668822', NULL, NULL, NULL, NULL, '2026-06-12', '07:45:00', 'XQ369', 'ASTERIA FAMILY RESORT BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', 'KATLANIR TEKERLEKLI SANDALYE VAR GENİŞ BAGAJ ARAÇ ŞARTT', NULL, 1, 1, 25.00, 'EUR', '2026-05-16 21:00:18', '2026-06-14 20:21:29', 'Mustafa Edirneli', NULL, 112, 175),
(335, 'TRF-20260517-5201', 'transfer', 'confirmed', 'return', NULL, 28, 3, 'DOROTA KORBUS', '', '+48695205213', NULL, NULL, '14:30:00', NULL, '2026-05-18', '18:00:00', 'XQ664', 'IC SANTAI FAMILY — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 11, 0, 0, 50.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-17 21:19:14', '2026-06-14 20:21:29', 'Mehmet Eseoğlu', '14:30:00', 61, 176);
INSERT INTO `bookings` (`id`, `booking_number`, `booking_type`, `booking_status`, `booking_direction`, `tour_id`, `destination_id`, `vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `pickup_location`, `pickup_date`, `pickup_time`, `return_time`, `flight_date`, `flight_time`, `flight_number`, `hotel_address`, `return_transfer`, `return_flight_date`, `return_flight_time`, `return_flight_number`, `return_pickup_time`, `return_hotel_address`, `adults`, `children`, `child_seat`, `total_price`, `currency`, `notes`, `admin_notes`, `is_completed`, `is_outsourced`, `outsource_price`, `outsource_currency`, `created_at`, `updated_at`, `outsource_name`, `outsource_pickup_time`, `outsource_partner_id`, `customer_id`) VALUES
(336, 'TRF-20260517-5617', 'transfer', 'confirmed', 'outbound', NULL, 12, 1, 'MICHAEL KRINJEN', '', '+31627822313', NULL, NULL, NULL, NULL, '2026-06-17', '00:35:00', 'XQ281', 'KHAYA RESORT AQUA — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-17 21:28:33', '2026-06-14 20:21:29', NULL, NULL, NULL, 177),
(337, 'TRF-20260517-4452', 'transfer', 'confirmed', 'return', NULL, 12, 1, 'MICHAEL KRINJEN', '', '+31627822313', NULL, NULL, '00:30:00', NULL, '2026-06-22', '05:30:00', 'XQ282', 'KHAYA RESORT AQUA — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-17 21:28:33', '2026-06-14 20:21:29', NULL, NULL, NULL, 177),
(338, 'TRF-20260517-8580', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'SAM LARKIN', '', '+447538316274', NULL, NULL, NULL, NULL, '2026-05-25', '13:15:00', 'LS895', 'SIDE STAR ELEGANCE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-17 21:31:50', '2026-06-14 20:21:29', NULL, NULL, NULL, 178),
(339, 'TRF-20260517-4974', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'MEHMET ALİ YILDIZ', '', '+491627125810', NULL, NULL, NULL, NULL, '2026-05-18', '19:50:00', 'XQ187', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-17 21:38:57', '2026-06-14 20:21:29', NULL, NULL, NULL, 179),
(340, 'TRF-20260517-5003', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'MEHMET ALİ YILDIZ', '', '+491627125810', NULL, NULL, '11:30:00', NULL, '2026-05-24', '15:00:00', 'XQ182', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-17 21:38:57', '2026-06-14 20:21:29', 'Faruk Sevil', '11:30:00', 72, 179),
(341, 'TRF-20260517-4756', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'PETRA ANDROLEIT', '', '', NULL, NULL, NULL, NULL, '2026-05-18', '14:40:00', 'PC5002', 'ALEGRİA HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-17 21:42:33', '2026-06-14 20:21:30', NULL, NULL, NULL, 180),
(342, 'TRF-20260517-4122', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'PETRA ANDROLEIT', '', '', NULL, NULL, '10:20:00', NULL, '2026-05-28', '13:45:00', 'PC5011', 'ALEGRİA HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-17 21:42:33', '2026-06-14 20:21:30', NULL, NULL, NULL, 180),
(344, 'TRF-20260517-3220', 'transfer', 'confirmed', 'return', NULL, 1, NULL, 'KLAUS DIETER BACZYK', '', '', NULL, NULL, '18:00:00', NULL, '2026-06-19', '22:35:00', 'XC112', 'ALEGRİA HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-17 21:47:07', '2026-06-14 20:21:30', NULL, NULL, NULL, 181),
(347, 'TRF-20260518-3070', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'Luis webels', '', '+4915257893242', NULL, NULL, NULL, NULL, '2026-05-25', '08:10:00', 'XQ257', 'NOVA PARK — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-18 21:47:41', '2026-06-14 20:21:30', 'Yusuf Oğuz', NULL, 73, 182),
(348, 'TRF-20260518-5435', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'Luis webels', '', '+4915257893242', NULL, NULL, '18:30:00', NULL, '2026-06-01', '22:50:00', 'XQ134', 'NOVA PARK — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-18 21:47:41', '2026-06-14 20:21:30', NULL, NULL, NULL, 182),
(350, 'TRF-20260518-9042', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'Carole swinburne', '', '', NULL, NULL, '12:30:00', NULL, '2026-05-20', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', 'ANTALYA RIVER PARK RESIDENCE LARA GİDER', NULL, 0, 0, NULL, NULL, '2026-05-18 22:12:12', '2026-06-14 20:21:30', NULL, NULL, NULL, 183),
(351, 'TRF-20260518-5602', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'Senna sanders', '', '+31657475947', NULL, NULL, '16:30:00', NULL, '2026-05-20', '20:00:00', '4M359', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-18 22:16:23', '2026-06-14 20:21:30', NULL, NULL, NULL, 53),
(352, 'TRF-20260518-0654', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'Gülsün yaşar', '', '+31616831650', NULL, NULL, NULL, NULL, '2026-05-21', '22:30:00', 'XQ551', 'DREAM WORLD AQUA — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-18 22:29:51', '2026-06-14 20:21:30', NULL, NULL, NULL, 184),
(353, 'TRF-20260518-8610', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'Gülsün yaşar', '', '+31616831650', NULL, NULL, '07:00:00', NULL, '2026-05-28', '10:30:00', 'PC5065', 'DREAM WORLD AQUA — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-18 22:29:51', '2026-06-14 20:21:30', NULL, NULL, NULL, 184),
(354, 'TRF-20260518-0551', 'transfer', 'confirmed', 'outbound', NULL, 28, 1, 'Fahmi laouini', '', '+33761496988', NULL, NULL, NULL, NULL, '2026-05-21', '17:55:00', 'TK2426', 'NICKELODEON HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-18 23:02:28', '2026-06-14 20:21:30', NULL, NULL, NULL, 185),
(356, 'TRF-20260519-2910', 'transfer', 'confirmed', 'return', NULL, 13, NULL, 'FIRAT VURUCU', '', '+4522331829', NULL, NULL, '11:45:00', NULL, '2026-05-22', NULL, 'PC5025', 'WYNDHAM GARDEN LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-19 17:03:59', '2026-06-14 20:21:30', 'Fatih Çil', '11:45:00', 50, 168),
(357, 'TRF-20260519-4291', 'transfer', 'confirmed', 'return', NULL, 1, NULL, 'AHMET TANRIKOLU', '', '+90 542 309 88 54', NULL, NULL, '10:15:00', NULL, '2026-05-22', '13:45:00', 'WF3035', 'SIDE PRENSES HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-19 17:09:57', '2026-06-14 20:21:30', NULL, NULL, NULL, 186),
(358, 'TRF-20260519-4727', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'BLEONA AHMETI', '', '+49 1523 8422041', NULL, NULL, NULL, NULL, '2026-08-05', '05:30:00', 'XQ655', 'KAYA SIDE HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-19 17:16:56', '2026-06-14 20:21:30', NULL, NULL, NULL, 187),
(359, 'TRF-20260519-1192', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'BLEONA AHMETI', '', '+49 1523 8422041', NULL, NULL, '20:45:00', NULL, '2026-08-10', '00:45:00', 'XQ654', 'KAYA SIDE HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', 'UÇUŞ 11 AĞUSTOS 00:45TE', NULL, 0, 0, NULL, NULL, '2026-05-19 17:16:56', '2026-06-14 20:21:30', NULL, NULL, NULL, 187),
(360, 'TRF-20260519-6029', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'FABIAN DOKL', '', '+49 1512 9513587', NULL, NULL, '04:30:00', NULL, '2026-05-21', '08:00:00', '4M311', 'WASHINGTON RESORT — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 2700.00, 'TRY', NULL, NULL, 0, 0, NULL, NULL, '2026-05-19 21:19:58', '2026-06-14 20:21:30', NULL, NULL, NULL, 188),
(361, 'TRF-20260519-7962', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'JOLINA KOTHE', '', '+49 1525 1804647', NULL, NULL, '19:30:00', NULL, '2026-05-21', '23:30:00', 'XQ166', 'KAMELYA SELIN HOTEL — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-19 21:22:31', '2026-06-14 20:21:31', NULL, NULL, NULL, 189),
(362, 'TRF-20260519-1913', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MELISSA CHARMELY', '', '+44 7527 495393', NULL, NULL, NULL, NULL, '2026-06-01', '23:05:00', 'EZY2139', 'DELPHIN BE GRAND — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-19 21:24:58', '2026-06-14 20:21:31', 'Ümit Ekin', NULL, 87, 190),
(363, 'TRF-20260519-7208', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'SEVDA HUSEYNOVA', '', '+46 73 701 93 04', NULL, NULL, NULL, NULL, '2026-06-14', '15:30:00', 'PC5020', 'GRAND PARK LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 55.00, 'USD', '2 ADET ÇİÇEK BUKETİ İLE KARŞILAMA !!', NULL, 0, 0, NULL, NULL, '2026-05-19 21:26:59', '2026-06-14 20:21:31', NULL, NULL, NULL, 191),
(365, 'TRF-20260520-5243', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'Serap couper', '', '', NULL, NULL, '06:30:00', NULL, '2026-05-21', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-20 19:13:34', '2026-06-14 20:21:31', NULL, NULL, NULL, 192),
(366, 'TRF-20260521-7036', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'Elena Peitsch', '', '+49 179 4720377', NULL, NULL, NULL, NULL, '2026-06-11', '16:45:00', 'XQ161', 'HANE FAMILY RESORT — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 2, 0, 45.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-21 14:21:37', '2026-06-14 20:21:31', 'Yusuf Oğuz', NULL, 73, 193),
(367, 'TRF-20260521-7067', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'Elena Peitsch', '', '+49 179 4720377', NULL, NULL, '11:30:00', NULL, '2026-06-20', '15:35:00', 'PC5007', 'HANE FAMILY RESORT — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 2, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-21 14:21:37', '2026-06-14 20:21:31', NULL, NULL, NULL, 193),
(368, 'TRF-20260521-1904', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'OESEM AKKABAL', '', '+32 485 08 88 54', NULL, NULL, NULL, NULL, '2026-08-06', '18:30:00', 'XC103', 'THE X BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-21 14:25:42', '2026-06-14 20:21:31', NULL, NULL, NULL, 194),
(369, 'TRF-20260521-0378', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'OESEM AKKABAL', '', '+32 485 08 88 54', NULL, NULL, '12:00:00', NULL, '2026-08-26', '15:30:00', 'XC106', 'THE X BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-21 14:25:42', '2026-06-14 20:21:31', NULL, NULL, NULL, 194),
(370, 'TRF-20260522-0950', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'REXHEP ZUKA', '', '+352661264985', NULL, NULL, NULL, NULL, '2026-07-24', '10:45:00', 'LG835', 'CLUB TURAN PRINCE WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-22 01:07:28', '2026-06-14 20:21:31', NULL, NULL, NULL, 195),
(371, 'TRF-20260522-1779', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'REXHEP ZUKA', '', '+352661264985', NULL, NULL, '15:30:00', NULL, '2026-08-02', '19:25:00', 'LG834', 'CLUB TURAN PRINCE WORLD — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-22 01:07:28', '2026-06-14 20:21:31', NULL, NULL, NULL, 195),
(372, 'TRF-20260522-3383', 'transfer', 'confirmed', 'outbound', NULL, 20, 1, 'HASSAN HASSAN', '', '+964 774 911 9953', NULL, NULL, NULL, NULL, '2026-05-22', '20:30:00', 'IA209', 'Castello Hotel & Aparts — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 1500.00, 'TRY', '2026-05-22 10:36:50', '2026-06-14 20:21:31', 'Mehmet Akif Esoğlu', NULL, 38, 196),
(373, 'TRF-20260522-5181', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'YASEMİN DEMİR', '', '+49 162 2581244', NULL, NULL, '19:00:00', NULL, '2026-05-24', '22:25:00', 'PC5003', 'ETHNO BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-22 10:39:19', '2026-06-14 20:21:31', 'Yusuf Oğuz', '19:00:00', 73, 197),
(374, 'TRF-20260522-0767', 'transfer', 'confirmed', 'return', NULL, 20, 1, 'VADIM STEPANOVS', '', '+371 29 119 619', NULL, NULL, '07:20:00', NULL, '2026-05-23', '11:05:00', 'BT7718', 'DOBEDAN WORLD KEMER — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 1650.00, 'TRY', '2026-05-22 14:29:27', '2026-06-14 20:21:31', 'Mehmet Akif Esoğlu', '07:20:00', 38, 198),
(375, 'TRF-20260522-6353', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'KADRUSH SELIMI', '', '+41 76 568 54 16', NULL, NULL, NULL, NULL, '2026-05-31', '00:45:00', 'XQ123', 'LAGO HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-22 14:46:44', '2026-06-14 20:21:31', NULL, NULL, NULL, 199),
(376, 'TRF-20260522-0040', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'KADRUSH SELIMI', '', '+41 76 568 54 16', NULL, NULL, '12:30:00', NULL, '2026-06-08', '16:55:00', 'XQ122', 'LAGO HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-22 14:46:44', '2026-06-14 20:21:31', NULL, NULL, NULL, 199),
(379, 'TRF-20260523-1683', 'transfer', 'confirmed', 'outbound', NULL, 20, 1, 'IRENA GUTIN', '', '+15147991208', NULL, NULL, NULL, NULL, '2026-06-11', '16:35:00', 'TK2424', 'ÖZKAYMAK MARINA — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-23 15:36:37', '2026-06-14 20:21:32', 'Levent Mulla Çeçen', NULL, 109, 200),
(380, 'TRF-20260523-1340', 'transfer', 'confirmed', 'return', NULL, 20, 1, 'IRENA GUTIN', '', '+15147991208', NULL, NULL, '07:30:00', NULL, '2026-06-21', '11:20:00', 'TK2411', 'ÖZKAYMAK MARINA — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-23 15:36:37', '2026-06-14 20:21:32', NULL, NULL, NULL, 200),
(381, 'TRF-20260523-8725', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'Hana Ramcilovic', '', '+352 691 154 494', NULL, NULL, NULL, NULL, '2026-05-24', '22:30:00', 'XQ551', 'TRENDY LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-23 17:25:37', '2026-06-14 20:21:32', 'Aykut Ataman', NULL, 74, 201),
(382, 'TRF-20260523-1773', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'BİLAL KÖYCÜ', '', '+32 488 60 45 65', NULL, NULL, NULL, NULL, '2026-06-26', '23:55:00', 'PC5566', 'SIDE KONUM — SIDE', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 40.00, 'EUR', 'BIRAKILACAK KONUM YALI MH 1096 SK SIDE RIVIERA EVLERİ 14', NULL, 0, 0, NULL, NULL, '2026-05-23 17:28:33', '2026-06-14 20:21:32', NULL, NULL, NULL, 202),
(383, 'TRF-20260524-8987', 'transfer', 'confirmed', 'return', NULL, 28, 3, 'Silviu Florian Boc', '', '+49 177 6728414', NULL, NULL, '19:00:00', NULL, '2026-05-24', NULL, NULL, 'ADORE HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 45.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-24 12:49:30', '2026-06-14 20:21:32', 'Mehmet Akif Esoğlu', '19:00:00', 38, 67),
(384, 'TRF-20260524-1312', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'FAHMI LAOUINI', '', '+33 7 61 49 69 88', NULL, NULL, '10:00:00', NULL, '2026-05-25', '12:55:00', NULL, 'NICKELODEON HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-24 12:52:54', '2026-06-14 20:21:32', 'Buğra Tunç', '10:00:00', 77, 185),
(385, 'TRF-20260524-5794', 'transfer', 'confirmed', 'outbound', NULL, 12, 1, 'SAMIRA HAGOS', '', '+46 70 768 45 94', NULL, NULL, NULL, NULL, '2026-07-15', '22:20:00', 'PC5022', 'KARİKATÜR HOTEL — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 75.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 13:08:08', '2026-06-14 20:21:32', NULL, NULL, NULL, 203),
(386, 'TRF-20260524-8802', 'transfer', 'confirmed', 'return', NULL, 12, 1, 'SAMIRA HAGOS', '', '+46 70 768 45 94', NULL, NULL, '09:00:00', NULL, '2026-07-29', '13:35:00', 'PC5021', 'KARİKATÜR HOTEL — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 75.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 13:08:08', '2026-06-14 20:21:32', NULL, NULL, NULL, 203),
(387, 'TRF-20260524-6293', 'transfer', 'confirmed', 'outbound', NULL, 15, 1, 'DERYA FİDANCI', '', '+4917680553291', NULL, NULL, NULL, NULL, '2026-05-27', '06:05:00', 'XQ119', 'SULTAN OF DREAMS — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 13:12:43', '2026-06-14 20:21:32', NULL, NULL, NULL, 204),
(388, 'TRF-20260524-0234', 'transfer', 'confirmed', 'return', NULL, 15, 1, 'DERYA FİDANCI', '', '+4917680553291', NULL, NULL, '17:30:00', NULL, '2026-05-31', '20:50:00', 'XQ116', 'SULTAN OF DREAMS — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 13:12:43', '2026-06-14 20:21:32', NULL, NULL, NULL, 204),
(389, 'TRF-20260524-4103', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'GEORG SCHNEIDER', '', '', NULL, NULL, NULL, NULL, '2026-06-16', '22:45:00', 'XQ121', 'SIDE GOLDEN ROCK — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 13:19:47', '2026-06-14 20:21:32', NULL, NULL, NULL, 205),
(390, 'TRF-20260524-8183', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'GEORG SCHNEIDER', '', '', NULL, NULL, '02:30:00', NULL, '2026-06-25', '06:10:00', 'XQ124', 'SIDE GOLDEN ROCK — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 13:19:47', '2026-06-14 20:21:32', NULL, NULL, NULL, 205),
(391, 'TRF-20260524-0061', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'DIETMAR RESSEL', '', '+49 176 24212035', NULL, NULL, NULL, NULL, '2026-06-15', '12:20:00', 'XQ185', 'DIAMOND DELUXE — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', 'EN AZ 8 BİRA', NULL, 0, 0, NULL, NULL, '2026-05-24 14:52:03', '2026-06-14 20:21:32', NULL, NULL, NULL, 206),
(393, 'TRF-20260524-9868', 'transfer', 'confirmed', 'outbound', NULL, 28, NULL, 'DINO DJULIC', '', '+43 660 1105093', NULL, NULL, NULL, NULL, '2026-06-24', '13:15:00', 'OS887', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 2, 35.00, 'EUR', '4+2 PAX', NULL, 0, 0, NULL, NULL, '2026-05-24 14:57:03', '2026-06-14 20:21:32', NULL, NULL, NULL, 207),
(394, 'TRF-20260524-4035', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'THOMAS MEYER', '', '', NULL, NULL, '04:45:00', NULL, '2026-05-25', '07:55:00', NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-05-24 15:21:12', '2026-06-14 20:21:32', 'Ali Gür', '04:45:00', 78, 208),
(395, 'TRF-20260524-3475', 'transfer', 'confirmed', 'return', NULL, 29, NULL, 'DINO DJULIC', '', '+43 660 1105093', NULL, NULL, '11:30:00', NULL, '2026-07-01', '14:05:00', 'OS888', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 2, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 15:28:55', '2026-06-14 20:21:32', NULL, NULL, NULL, 207),
(396, 'TRF-20260524-4337', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'ALAN MAJID HASAN', '', '+964 750 455 8350', NULL, NULL, NULL, NULL, '2026-05-25', '13:35:00', 'PC4505', 'CLUB NENA HOTEL — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 50.00, 'EUR', 'KARŞILAMA İSMİ Dr Alan Hasan', NULL, 1, 1, NULL, 'EUR', '2026-05-24 15:35:42', '2026-06-14 20:21:33', 'Yusuf Oğuz', NULL, 73, 209),
(397, 'TRF-20260524-3859', 'transfer', 'confirmed', 'outbound', NULL, 20, 1, 'JESSICA NOORDMAN', '', '+31 6 15270788', NULL, NULL, NULL, NULL, '2026-06-26', '14:55:00', 'XC5021', 'FAME RESIDANCE KEMER — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 21:11:06', '2026-06-14 20:21:33', NULL, NULL, NULL, 210),
(398, 'TRF-20260524-0452', 'transfer', 'confirmed', 'return', NULL, 20, 1, 'JESSICA NOORDMAN', '', '+31 6 15270788', NULL, NULL, '03:00:00', NULL, '2026-06-30', '06:00:00', 'XC5022', 'FAME RESIDANCE KEMER — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-24 21:11:06', '2026-06-14 20:21:33', NULL, NULL, NULL, 210),
(399, 'TRF-20260525-5959', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'Patricia Matthews', '', '+353 86 842 3532', NULL, NULL, NULL, NULL, '2026-06-20', '19:25:00', 'XQ533', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-25 00:39:46', '2026-06-14 20:21:33', NULL, NULL, NULL, 211),
(400, 'TRF-20260525-9182', 'transfer', 'confirmed', 'return', NULL, 24, 1, 'AHMAD SHWEHNEH', '', '+90 537 206 18 82', NULL, NULL, '16:30:00', NULL, '2026-05-25', NULL, NULL, 'TRANSATLANTIK — GÖYNÜK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 2000.00, 'TRY', NULL, NULL, 1, 1, 1200.00, 'TRY', '2026-05-25 10:22:57', '2026-06-14 20:21:33', 'Sezgin Korkmaz', '16:30:00', 80, 212),
(401, 'TRF-20260525-9152', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'Nabil mechehrawi', '', '', NULL, NULL, NULL, NULL, '2026-06-07', '18:30:00', 'XC103', 'ACARKENT — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-25 11:40:42', '2026-06-14 20:21:33', 'Yusuf Oğuz', NULL, 73, 213),
(402, 'TRF-20260525-0684', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'PATRICK BRUDER', '', '+33 6 13 19 96 60', NULL, NULL, NULL, NULL, '2026-08-14', '14:30:00', 'XQ111', 'SUNIS KUMKÖY BEACH — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-25 17:59:41', '2026-06-14 20:21:33', NULL, NULL, NULL, 214),
(403, 'TRF-20260525-6663', 'transfer', 'confirmed', 'outbound', NULL, 7, NULL, 'Merve khalaf', '', '+49 176 31285667', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PALM WORLD — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-25 22:31:06', '2026-06-14 20:21:33', NULL, NULL, NULL, 215),
(404, 'TRF-20260525-9288', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'Merve khalaf', '', '+49 176 31285667', NULL, NULL, NULL, NULL, '2026-05-26', '12:00:00', NULL, 'PALM WORLD — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-25 22:32:16', '2026-06-14 20:21:33', NULL, NULL, NULL, 215),
(405, 'TRF-20260526-9529', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'PATRICK VILLERIUS', '', '+31 6 53577736', NULL, NULL, NULL, NULL, '2026-05-26', '15:10:00', 'XC5021', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 15.00, 'EUR', '2026-05-26 10:24:03', '2026-06-14 20:21:33', 'Can Küpelioğlu', NULL, 18, 216),
(406, 'TRF-20260526-1585', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'JOY HANA', '', '+46 72 381 78 09', NULL, NULL, '18:30:00', NULL, '2026-05-27', '21:10:00', 'D84608', 'TITANIC GOLF RESORT — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 7, 0, 1, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-26 13:35:01', '2026-06-14 20:21:33', NULL, NULL, NULL, 217),
(407, 'TRF-20260526-3408', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'MERVE KHALAF', '', '+49 176 31285667', NULL, NULL, '02:00:00', NULL, '2026-05-30', '06:00:00', 'XQ188', 'PALM WORLD — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-26 14:04:31', '2026-06-14 20:21:33', NULL, NULL, NULL, 215),
(408, 'TRF-20260526-0737', 'transfer', 'confirmed', 'outbound', NULL, 25, 1, 'VALENTIN MOCH', '', '+41 76 628 23 36', NULL, NULL, NULL, NULL, '2026-05-30', '00:45:00', 'XQ123', 'CRYSTAL PARAISO AQUA COLLECTION — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 1, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-26 15:58:06', '2026-06-14 20:21:33', NULL, NULL, NULL, 218),
(409, 'TRF-20260526-8819', 'transfer', 'confirmed', 'return', NULL, 25, 1, 'VALENTIN MOCH', '', '+41 76 628 23 36', NULL, NULL, '03:00:00', NULL, '2026-06-06', '07:25:00', 'PC5027', 'CRYSTAL PARAISO AQUA COLLECTION — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 1, 40.00, 'EUR', NULL, NULL, 1, 1, 1400.00, 'TRY', '2026-05-26 15:58:06', '2026-06-14 20:21:33', 'Mustafa Bayrak', '03:00:00', 16, 218),
(410, 'TRF-20260527-4773', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'ALI GHOULAM', '', '+4407836435124', NULL, NULL, NULL, NULL, '2026-06-19', '22:05:00', 'LS757', 'MIRACKLE RESORT — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-27 14:16:56', '2026-06-14 20:21:34', NULL, NULL, NULL, 219),
(411, 'TRF-20260527-8380', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'ALI GHOULAM', '', '+4407836435124', NULL, NULL, '20:30:00', NULL, '2026-06-26', '23:00:00', 'LS758', 'MIRACKLE RESORT — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-27 14:16:56', '2026-06-14 20:21:34', NULL, NULL, NULL, 219),
(412, 'TRF-20260527-5075', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'SAMIRA MOUKEFFES', '', '+49 177 1542206', NULL, NULL, NULL, NULL, '2026-05-29', '18:30:00', 'XQ241', 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-27 19:53:13', '2026-06-14 20:21:34', 'Yusuf Oğuz', NULL, 73, 220),
(413, 'TRF-20260527-5566', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'SAMIRA MOUKEFFES', '', '+49 177 1542206', NULL, NULL, '06:00:00', NULL, '2026-06-02', '10:05:00', 'XQ240', 'SEADEN COROLLA HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-27 19:53:13', '2026-06-14 20:21:34', NULL, NULL, NULL, 220),
(414, 'TRF-20260527-3449', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'JORDAN MANLEY', '', '+44 7368 509121', NULL, NULL, NULL, NULL, '2026-05-28', '01:05:00', 'XQ585', 'WYNDHAM GARDEN LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-27 19:57:48', '2026-06-14 20:21:34', NULL, NULL, NULL, 221),
(415, 'TRF-20260527-1405', 'transfer', 'confirmed', 'outbound', NULL, 25, 1, 'SİNAN BAK', '', '+49 176 64008810', NULL, NULL, NULL, NULL, '2026-06-18', '12:10:00', 'XQ151', 'CRYSTAL WATER WORLD AQUA COLLECTION — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-27 22:10:46', '2026-06-14 20:21:34', NULL, NULL, NULL, 222),
(417, 'TRF-20260528-4856', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'PHILLIP GARNHAM', '', '+447970266036', NULL, NULL, NULL, NULL, '2026-06-14', '15:55:00', 'XQ525', 'AKRA SORGUN — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-28 20:59:36', '2026-06-14 20:21:34', NULL, NULL, NULL, 223),
(418, 'TRF-20260528-1489', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'ÖZCAN KOCAMAN', '', '+49 176 84559364', NULL, NULL, NULL, NULL, '2026-06-04', '17:25:00', 'PC5042', 'SATURUN PALACE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 25.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-28 21:06:24', '2026-06-14 20:21:34', 'Yusuf Oğuz', NULL, 73, 224),
(419, 'TRF-20260528-2894', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'ÖZCAN KOCAMAN', '', '+49 176 84559364', NULL, NULL, '07:00:00', NULL, '2026-06-11', '09:15:00', 'PC5041', 'SATURUN PALACE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 25.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-28 21:06:24', '2026-06-14 20:21:34', NULL, NULL, NULL, 224),
(420, 'TRF-20260528-3385', 'transfer', 'confirmed', 'outbound', NULL, 16, 1, 'CEMAL YILDIRIM', '', '+49 1573 6153016', NULL, NULL, NULL, NULL, '2026-06-09', '11:55:00', 'XQ157', 'ÖZ HOTELS İNCEKUM — İNCEKUM', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-05-28 21:11:08', '2026-06-14 20:21:34', NULL, NULL, NULL, 225),
(421, 'TRF-20260528-2496', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MARILENA BOEACA', '', '+40799421240', NULL, NULL, NULL, NULL, '2026-06-04', '14:05:00', 'TK2418', 'GRAND PARK LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-28 21:13:35', '2026-06-14 20:21:34', NULL, NULL, NULL, 226),
(422, 'TRF-20260528-5482', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'VIKTOR MELENBERG', '', '+49 1575 4305782', NULL, NULL, NULL, NULL, '2026-06-03', '08:20:00', 'XQ113', 'DREAM WORLD PALACE — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-28 21:16:26', '2026-06-14 20:21:34', NULL, NULL, NULL, 227),
(423, 'TRF-20260529-8037', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'FATMA KOLUÇOLAK', '', '05554708080', NULL, NULL, NULL, NULL, '2026-05-29', '14:10:00', 'VF4006', 'MANAVGAT — MERKEZ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 0.00, 'EUR', NULL, NULL, 1, 1, 1300.00, 'TRY', '2026-05-29 09:21:07', '2026-06-14 20:21:34', 'Fatih Çil', NULL, 50, 228),
(424, 'TRF-20260529-5136', 'transfer', 'confirmed', 'return', NULL, 20, 1, 'NATALIJA PETROVA', '', '+37258394680', NULL, NULL, '19:50:00', NULL, '2026-05-29', '23:20:00', NULL, 'ÖZKAYMAK MARINA — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-29 14:13:49', '2026-06-14 20:21:35', 'Naci Çetin', '19:50:00', 84, 229),
(425, 'TRF-20260529-9942', 'transfer', 'confirmed', 'outbound', NULL, 18, 1, 'JC VAN TIENEN', '', '+31 6 38911696', NULL, NULL, NULL, NULL, '2026-06-04', '12:05:00', 'CD5145', 'EFTALIA OCEAN — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 40.00, 'EUR', '2026-05-29 15:22:09', '2026-06-14 20:21:35', 'Ensar Yamankaya', NULL, 90, 230),
(426, 'TRF-20260529-4338', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'TIFANNY NHAN', '', '+47 455 72 380', NULL, NULL, NULL, NULL, '2026-06-14', '11:00:00', 'DY1260', 'BEGONVILLE APART — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-29 15:25:11', '2026-06-14 20:21:35', NULL, NULL, NULL, 231),
(427, 'TRF-20260529-1793', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'TIFANNY NHAN', '', '+47 455 72 380', NULL, NULL, '05:30:00', NULL, '2026-06-19', '11:50:00', 'DY1261', 'BEGONVILLE APART — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-29 15:27:07', '2026-06-14 20:21:35', NULL, NULL, NULL, 231),
(428, 'TRF-20260530-1822', 'transfer', 'confirmed', 'outbound', NULL, 13, 3, 'Fatima Cheaito', '', '+46 72 918 55 41', NULL, NULL, NULL, NULL, '2026-06-04', '06:00:00', 'XQ573', 'GRAND PARK LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 7, 0, 2, 45.00, 'EUR', NULL, NULL, 1, 1, 1500.00, 'TRY', '2026-05-30 00:02:44', '2026-06-14 20:21:35', 'Bülent Şahin', NULL, 89, 232),
(429, 'TRF-20260530-4926', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'MARK MEADER', '', '+44 7904 232182', NULL, NULL, NULL, NULL, '2026-06-06', '19:10:00', 'EZY8555', 'GRANADA LUXURY BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 1, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-30 00:05:36', '2026-06-14 20:21:35', 'Aykut Ataman', NULL, 74, 233),
(430, 'TRF-20260530-9487', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'MARK MEADER', '', '+44 7904 232182', NULL, NULL, '12:00:00', NULL, '2026-06-13', '15:30:00', NULL, 'GRANADA LUXURY BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 1, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-05-30 00:05:36', '2026-06-14 20:21:35', NULL, '12:00:00', NULL, 233),
(431, 'TRF-20260530-2402', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'DANNY HENNEKES', '', '+31 6 52675282', NULL, NULL, '12:30:00', NULL, '2026-05-31', NULL, NULL, 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-30 09:23:28', '2026-06-14 20:21:35', 'Yusuf Oğuz', '12:30:00', 73, 11),
(432, 'TRF-20260530-7185', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'DEBBY VERVLOET', '', '', NULL, NULL, '06:30:00', NULL, '2026-05-31', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-30 21:55:43', '2026-06-14 20:21:35', 'Yusuf Oğuz', '06:30:00', 73, 234),
(433, 'TRF-20260531-2500', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'JULIA HAUBENREISSER', '', '+49 1520 3821075', NULL, NULL, '05:00:00', NULL, '2026-06-01', '08:20:00', 'XQ614', 'TRENDY VERBANA BEACH — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-31 19:08:38', '2026-06-14 20:21:35', 'Yusuf Oğuz', '05:00:00', 73, 146),
(434, 'TRF-20260531-1646', 'transfer', 'confirmed', 'outbound', NULL, 16, 1, 'ALEXANDER FELGER', '', '+491742347178', NULL, NULL, NULL, NULL, '2026-09-14', '04:35:00', '4M728', 'MYLOME LUXURY HOTEL — OKURCALAR', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:15:49', '2026-06-14 20:21:35', NULL, NULL, NULL, 235),
(435, 'TRF-20260531-1846', 'transfer', 'confirmed', 'return', NULL, 16, 1, 'ALEXANDER FELGER', '', '+491742347178', NULL, NULL, '16:00:00', NULL, '2026-09-24', '20:35:00', 'FH1255', 'MYLOME LUXURY HOTEL — OKURCALAR', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:15:49', '2026-06-14 20:21:35', NULL, NULL, NULL, 235),
(436, 'TRF-20260531-4927', 'transfer', 'confirmed', 'outbound', NULL, 26, 3, 'ZERIN CATIC', '', '+436602448820', NULL, NULL, NULL, NULL, '2026-07-24', '22:20:00', 'XC2103', 'ROYAL ATLANTIS BEACH — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 60.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:21:21', '2026-06-14 20:21:35', NULL, NULL, NULL, 236),
(437, 'TRF-20260531-4462', 'transfer', 'confirmed', 'return', NULL, 26, 3, 'ZERIN CATIC', '', '+436602448820', NULL, NULL, '11:00:00', NULL, '2026-07-29', '14:50:00', 'XC2104', 'ROYAL ATLANTIS BEACH — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 8, 0, 0, 60.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:21:21', '2026-06-14 20:21:35', NULL, NULL, NULL, 236),
(438, 'TRF-20260531-7002', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'STEFAN HALLAUER', '', '+4917661053238', NULL, NULL, NULL, NULL, '2026-07-01', '12:10:00', 'XQ151', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:27:22', '2026-06-14 20:21:35', NULL, NULL, NULL, 237),
(439, 'TRF-20260531-4944', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'STEFAN HALLAUER', '', '+4917661053238', NULL, NULL, '15:00:00', NULL, '2026-07-08', '18:20:00', 'XQ150', 'AYDIN BEY KINGS PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:27:22', '2026-06-14 20:21:35', NULL, NULL, NULL, 237),
(441, 'TRF-20260531-1677', 'transfer', 'confirmed', 'outbound', NULL, 8, NULL, 'Jewgeni Riedel', '', '', NULL, NULL, NULL, NULL, '2026-07-03', '21:10:00', 'XQ279', 'DIAMOND PREMIUM — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:35:23', '2026-06-14 20:21:36', NULL, NULL, NULL, 238),
(442, 'TRF-20260531-4150', 'transfer', 'confirmed', 'return', NULL, 8, NULL, 'Jewgeni Riedel', '', '', NULL, NULL, '09:30:00', NULL, '2026-07-14', '13:30:00', 'XQ278', 'DIAMOND PREMIUM — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 19:35:23', '2026-06-14 20:21:36', NULL, NULL, NULL, 238),
(443, 'TRF-20260531-2042', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'CEREN UYGUT', '', '+49 1575 1777164', NULL, NULL, NULL, NULL, '2026-08-21', '16:10:00', 'XQ145', 'SIDE ROYAL STYLE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', 'İC HATLARDAN YOLCU ALINACAK XQ7674', NULL, 0, 0, NULL, NULL, '2026-05-31 19:44:14', '2026-06-14 20:21:36', NULL, NULL, NULL, 239),
(444, 'TRF-20260531-7867', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'CEREN UYGUT', '', '+49 1575 1777164', NULL, NULL, NULL, NULL, '2026-08-29', NULL, NULL, 'SIDE ROYAL STYLE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', 'İC HATLARDAN YOLCU ALINACAK XQ7674', NULL, 0, 0, NULL, NULL, '2026-05-31 19:44:14', '2026-06-14 20:21:36', NULL, NULL, NULL, 239),
(445, 'TRF-20260531-1143', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'Ovidiu Remus Chelaru', '', '+44 7377 222480', NULL, NULL, NULL, NULL, '2026-06-14', '15:55:00', 'XQ525', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 22:21:03', '2026-06-14 20:21:36', NULL, NULL, NULL, 240),
(446, 'TRF-20260531-3721', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'BEN LEE', '', '+44 7584 320071', NULL, NULL, NULL, NULL, '2026-06-04', '14:00:00', 'EZY8555', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-05-31 22:35:56', '2026-06-14 20:21:36', NULL, NULL, NULL, 241),
(447, 'TRF-20260531-3472', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'Valdrin Shabani', '', '+41 79 220 61 28', NULL, NULL, NULL, NULL, '2026-06-05', '22:45:00', 'XQ121', 'LAGO HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-05-31 22:51:44', '2026-06-14 20:21:36', 'İbrahim Kıyıcı', NULL, 93, 242),
(448, 'TRF-20260531-8273', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'Valdrin Shabani', '', '+41 79 220 61 28', NULL, NULL, '11:00:00', NULL, '2026-06-12', '14:55:00', 'XQ120', 'LAGO HOTEL — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-05-31 22:51:44', '2026-06-14 20:21:36', 'Tolgahan Güven', '11:00:00', 67, 242),
(449, 'TRF-20260531-0803', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'LIRIDON ZUKA', '', '+352 661 357 107', NULL, NULL, NULL, NULL, '2026-07-02', '22:15:00', 'PC5012', 'JADORE DELUXE — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 23:11:10', '2026-06-14 20:21:36', NULL, NULL, NULL, 243),
(450, 'TRF-20260531-1635', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'LIRIDON ZUKA', '', '+352 661 357 107', NULL, NULL, '03:30:00', NULL, '2026-07-10', '07:30:00', '4M511', 'JADORE DELUXE — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-05-31 23:11:10', '2026-06-14 20:21:36', NULL, NULL, NULL, 243),
(451, 'TRF-20260601-6091', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'IVANA JURISIC', '', '+385 95 737 4776', NULL, NULL, NULL, NULL, '2026-06-01', '20:50:00', 'TK2442', 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-01 14:32:49', '2026-06-14 20:21:36', NULL, NULL, NULL, 244),
(452, 'TRF-20260601-6952', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'MOUNIR DRIOUECH', '', '', NULL, NULL, '09:00:00', NULL, '2026-06-07', '11:45:00', 'HV5012', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-01 14:36:35', '2026-06-14 20:21:36', NULL, NULL, NULL, 245),
(453, 'TRF-20260601-5082', 'transfer', 'confirmed', 'outbound', NULL, 28, NULL, 'TOBIAS ISENSCHMID', '', '+41 76 802 90 89', NULL, NULL, NULL, NULL, '2026-06-07', '20:50:00', 'PC5054', 'MEGASARAY CLUB BELEK — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 950.00, 'TRY', '2026-06-01 14:40:27', '2026-06-14 20:21:36', 'Mesut Yatar', NULL, 102, 246),
(454, 'TRF-20260601-7605', 'transfer', 'confirmed', 'return', NULL, 28, NULL, 'TOBIAS ISENSCHMID', '', '+41 76 802 90 89', NULL, NULL, '03:30:00', NULL, '2026-06-17', '06:10:00', 'XQ110', 'MEGASARAY CLUB BELEK — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-01 14:40:27', '2026-06-14 20:21:36', NULL, NULL, NULL, 246),
(455, 'TRF-20260601-6471', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'NAZARII VASIK', '', '', NULL, NULL, NULL, NULL, '2026-06-05', '20:30:00', 'FH1322', 'PINE BEACH — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-01 14:43:20', '2026-06-14 20:21:36', 'Yusuf Oğuz', NULL, 73, 247),
(456, 'TRF-20260601-0813', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'HERBEST BEER', '', '', NULL, NULL, NULL, NULL, '2026-06-01', '21:45:00', 'XC185', 'SIDE CROWN PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', 'KLINIK MISAFIRI ÖDEME ALINMIYOR', NULL, 0, 0, NULL, NULL, '2026-06-01 14:47:35', '2026-06-14 20:21:37', NULL, NULL, NULL, 248),
(457, 'TRF-20260601-9807', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'HERBEST BEER', '', '', NULL, NULL, '10:00:00', NULL, '2026-06-08', '14:00:00', 'XC186', 'SIDE CROWN PALACE — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', 'KLINIK MISAFIRI ÖDEME ALINMIYOR', NULL, 1, 1, 25.00, 'EUR', '2026-06-01 14:47:35', '2026-06-14 20:21:37', 'Hüseyin Asar', '10:00:00', 62, 248),
(458, 'TRF-20260601-7268', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'KLAUS DIETER BACZYK', '', '', NULL, NULL, NULL, NULL, '2026-06-05', '07:35:00', 'XC111', 'ALEGRİA HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-01 14:49:14', '2026-06-14 20:21:37', NULL, NULL, NULL, 181),
(459, 'TRF-20260601-0078', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'KLAUS DIETER BACZYK', '', '', NULL, NULL, '17:30:00', NULL, '2026-06-19', '22:35:00', 'XC112', 'ALEGRİA HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-01 14:49:14', '2026-06-14 20:21:37', NULL, NULL, NULL, 181),
(460, 'TRF-20260601-2991', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'DJORDY HENDRIKS', '', '+31 6 39325013', NULL, NULL, '05:30:00', NULL, '2026-06-05', '09:30:00', 'XC104', 'DREAM FUN WORLD — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-01 14:51:30', '2026-06-14 20:21:37', NULL, NULL, NULL, 249),
(461, 'TRF-20260601-9567', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'ARON FURER', '', '', NULL, NULL, '05:00:00', NULL, '2026-06-02', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-01 17:36:05', '2026-06-14 20:21:37', 'Mehmet çelik', '05:00:00', 86, 250),
(462, 'TRF-20260601-6467', 'transfer', 'confirmed', 'outbound', NULL, 1, 3, 'NATALIE MILDE', '', '+49 1575 4600357', NULL, NULL, NULL, NULL, '2026-06-02', '19:50:00', 'XQ187', 'HOTEL CESAR — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-06-01 17:48:12', '2026-06-14 20:21:37', 'Ali Seydi Tuluk', NULL, 11, 251),
(463, 'TRF-20260601-7361', 'transfer', 'confirmed', 'return', NULL, 1, 3, 'NATALIE MILDE', '', '+49 1575 4600357', NULL, NULL, '11:30:00', NULL, '2026-06-08', '15:30:00', 'XC106', 'HOTEL CESAR — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-01 17:48:12', '2026-06-14 20:21:37', NULL, '11:30:00', NULL, 251),
(464, 'TRF-20260601-4180', 'transfer', 'confirmed', 'return', NULL, 8, 1, 'HASAN MAJID', '', '+9647504558350', NULL, NULL, '14:30:00', NULL, '2026-06-02', '18:05:00', 'XQ7712', 'AQI PEGASOS WORLD — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 2000.00, 'TRY', 'İÇ HAT', NULL, 1, 0, NULL, NULL, '2026-06-01 22:11:56', '2026-06-14 20:21:37', NULL, NULL, NULL, 252),
(465, 'TRF-20260602-3040', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'CHRISTIAN MANZL', '', '+436763112175', NULL, NULL, NULL, NULL, '2026-06-14', '14:20:00', 'XQ135', 'BARUT HEMERA — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-02 00:29:06', '2026-06-14 20:21:37', NULL, NULL, NULL, 253),
(466, 'TRF-20260602-9830', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'CHRISTIAN MANZL', '', '+436763112175', NULL, NULL, '12:00:00', NULL, '2026-06-24', '15:35:00', 'XQ132', 'BARUT HEMERA — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-02 00:29:06', '2026-06-14 20:21:37', NULL, NULL, NULL, 253),
(467, 'TRF-20260602-0334', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'OLGA WAGNER', '', '+49 175 2074282', NULL, NULL, NULL, NULL, '2026-08-13', '20:45:00', 'XQ153', 'WYNDHAM GARDEN LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-02 11:45:48', '2026-06-14 20:21:37', NULL, NULL, NULL, 254),
(468, 'TRF-20260602-8040', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'OLGA WAGNER', '', '+49 175 2074282', NULL, NULL, '10:30:00', NULL, '2026-08-17', '12:45:00', 'XQ158', 'WYNDHAM GARDEN LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-02 11:45:48', '2026-06-14 20:21:37', NULL, NULL, NULL, 254),
(469, 'TRF-20260602-1455', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'JAMAL HUSSEIN', '', '+44 7306 257303', NULL, NULL, '06:00:00', NULL, '2026-06-05', '08:25:00', 'XQ582', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-02 11:48:48', '2026-06-14 20:21:37', NULL, NULL, NULL, 255),
(470, 'TRF-20260602-2512', 'transfer', 'confirmed', 'outbound', NULL, 14, NULL, 'BRENDON SHILTON', '', '+44 7950 916082', NULL, NULL, NULL, NULL, '2026-06-06', '20:50:00', 'LS1239', 'BELMONDO SUITES — KALEİÇİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 1, 1000.00, 'TRY', '2026-06-02 12:03:38', '2026-06-14 20:21:37', 'Oğuzhan Bedir', NULL, 100, 256),
(471, 'TRF-20260602-7557', 'transfer', 'confirmed', 'return', NULL, 14, NULL, 'BRENDON SHILTON', '', '+44 7950 916082', NULL, NULL, '17:00:00', NULL, '2026-06-12', '21:35:00', 'LS1120', 'BELMONDO SUITES — KALEİÇİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 1, 850.00, 'TRY', '2026-06-02 12:03:38', '2026-06-14 20:21:37', 'Veli Zorlu', '17:00:00', 113, 256),
(472, 'TRF-20260602-9305', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'GARY ROGERSON', '', '', NULL, NULL, NULL, NULL, '2026-06-14', '20:05:00', NULL, 'AKRA SORGUN — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', 'FROM MANCHESTER', NULL, 0, 0, NULL, NULL, '2026-06-02 12:12:07', '2026-06-14 20:21:37', NULL, NULL, NULL, 257),
(473, 'TRF-20260602-1442', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'FILIP FILIPAK', '', '', NULL, NULL, '04:45:00', NULL, '2026-06-04', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-02 12:28:37', '2026-06-14 20:21:38', 'Yusuf Oğuz', '04:45:00', 73, 258),
(474, 'TRF-20260602-6258', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'FAUIZA AHMED', '', '+44 7884 977191', NULL, NULL, NULL, NULL, '2026-06-03', '00:55:00', 'XQ595', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'GBP', NULL, NULL, 1, 1, 900.00, 'TRY', '2026-06-02 15:13:10', '2026-06-14 20:21:38', 'Berkay İpek', NULL, 88, 259),
(475, 'TRF-20260602-8627', 'transfer', 'confirmed', 'outbound', NULL, 26, 1, 'VALON VELUI', '', '+49 171 2376522', NULL, NULL, NULL, NULL, '2026-06-05', '21:45:00', 'FH1302', 'ALETRIS DELUXE — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-02 16:42:43', '2026-06-14 20:21:38', NULL, NULL, NULL, 260),
(476, 'TRF-20260602-0941', 'transfer', 'confirmed', 'return', NULL, 26, 1, 'VALON VELUI', '', '+49 171 2376522', NULL, NULL, '07:00:00', NULL, '2026-06-12', NULL, NULL, 'ALETRIS DELUXE — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-02 16:42:43', '2026-06-14 20:21:38', NULL, '07:00:00', NULL, 260),
(477, 'TRF-20260602-9413', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'AJDIN TOROMONOVIC', '', '+31 6 23257743', NULL, NULL, NULL, NULL, '2026-06-05', '12:20:00', 'XQ103', 'PALOME PERISSIA — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-02 22:15:53', '2026-06-14 20:21:38', 'Yusuf Oğuz', NULL, 73, 261),
(478, 'TRF-20260602-1646', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'BANU SHEIKH', '', '+44 7913 990857', NULL, NULL, NULL, NULL, '2026-06-05', '20:05:00', 'XQ593', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 1, 25.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-02 22:20:02', '2026-06-14 20:21:38', NULL, NULL, NULL, 262),
(479, 'TRF-20260602-7527', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'BANU SHEIKH', '', '+44 7913 990857', NULL, NULL, '10:30:00', NULL, '2026-06-13', '14:25:00', 'XQ594', 'DELPHIN IMPERIAL — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 1, 25.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-02 22:20:02', '2026-06-14 20:21:38', 'Yusuf Oğuz', '10:30:00', 73, 262),
(480, 'TRF-20260603-1987', 'transfer', 'confirmed', 'outbound', NULL, 20, 1, 'GLENN BOERA SOBA', '', '+31 6 15406152', NULL, NULL, NULL, NULL, '2026-06-09', '12:20:00', 'OR3025', 'BALMY BEACH KEMER — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 1200.00, 'TRY', '2026-06-03 09:40:55', '2026-06-14 20:21:38', 'Abdülkadir Taştan', NULL, 105, 263),
(481, 'TRF-20260603-7539', 'transfer', 'confirmed', 'return', NULL, 20, 1, 'GLENN BOERA SOBA', '', '+31 6 15406152', NULL, NULL, '15:00:00', NULL, '2026-06-16', '19:00:00', 'OR3026', 'BALMY BEACH KEMER — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-03 09:40:55', '2026-06-14 20:21:38', NULL, NULL, NULL, 263);
INSERT INTO `bookings` (`id`, `booking_number`, `booking_type`, `booking_status`, `booking_direction`, `tour_id`, `destination_id`, `vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `pickup_location`, `pickup_date`, `pickup_time`, `return_time`, `flight_date`, `flight_time`, `flight_number`, `hotel_address`, `return_transfer`, `return_flight_date`, `return_flight_time`, `return_flight_number`, `return_pickup_time`, `return_hotel_address`, `adults`, `children`, `child_seat`, `total_price`, `currency`, `notes`, `admin_notes`, `is_completed`, `is_outsourced`, `outsource_price`, `outsource_currency`, `created_at`, `updated_at`, `outsource_name`, `outsource_pickup_time`, `outsource_partner_id`, `customer_id`) VALUES
(482, 'TRF-20260603-6370', 'transfer', 'confirmed', 'outbound', NULL, 23, 1, 'MUSTAFA KAÇMAZ', '', '+49 162 6181051', NULL, NULL, NULL, NULL, '2026-06-07', '06:55:00', 'PC5004', 'MIAROSA KEMER BEACH — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 45.00, 'EUR', NULL, NULL, 1, 1, 30.00, 'EUR', '2026-06-03 09:45:12', '2026-06-14 20:21:38', 'Doğan Bulut', NULL, 99, 264),
(483, 'TRF-20260603-6172', 'transfer', 'confirmed', 'return', NULL, 23, 1, 'MUSTAFA KAÇMAZ', '', '+49 162 6181051', NULL, NULL, '04:30:00', NULL, '2026-06-18', '08:20:00', 'XQ614', 'MIAROSA KEMER BEACH — KİRİŞ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-03 09:45:12', '2026-06-14 20:21:38', NULL, NULL, NULL, 264),
(484, 'TRF-20260603-2433', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'WOLFGANG VOLZ', '', '', NULL, NULL, '17:20:00', NULL, '2026-06-04', NULL, 'XQ264', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-03 10:52:21', '2026-06-14 20:21:38', 'Enis Korkmaz', '17:20:00', 91, 265),
(485, 'TRF-20260603-5653', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'ALİ GÖKDEMİR', '', '+4917670748331', NULL, NULL, '04:30:00', NULL, '2026-08-28', NULL, NULL, 'PALOMA OCEANA — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-03 20:15:35', '2026-06-14 20:21:38', NULL, NULL, NULL, 266),
(486, 'TRF-20260603-8161', 'transfer', 'confirmed', 'outbound', NULL, 19, NULL, 'MUHAMMAD QASIM', '', '+447709291616', NULL, NULL, NULL, NULL, '2026-07-03', '21:40:00', NULL, 'GRANADA LUXURY BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-03 20:22:46', '2026-06-14 20:21:38', NULL, NULL, NULL, 267),
(487, 'TRF-20260603-2795', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'TOMASZ LOSKA', '', '+48782067268', NULL, NULL, '18:50:00', NULL, '2026-06-04', '21:35:00', 'FH769', 'SUSESI HOTEL — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-06-03 23:35:38', '2026-06-14 20:21:39', 'Yusuf Şanal', '18:50:00', 59, 268),
(488, 'TRF-20260603-6489', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'INA PAULSEN', '', '', NULL, NULL, '21:00:00', NULL, '2026-06-04', '23:35:00', NULL, 'MEGASARAY CLUB BELEK — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-03 23:38:11', '2026-06-14 20:21:39', NULL, NULL, NULL, 269),
(489, 'TRF-20260603-1630', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'ANGELIKA BÜHRING', '', '', NULL, NULL, '06:00:00', NULL, '2026-06-07', NULL, NULL, 'ALEGRİA HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-03 23:42:38', '2026-06-14 20:21:39', NULL, NULL, NULL, 270),
(490, 'TRF-20260604-2663', 'transfer', 'confirmed', 'return', NULL, 25, 1, 'MANUEL KRAUSSER', '', '+491754644895', NULL, NULL, '18:00:00', NULL, '2026-06-04', '21:35:00', 'XQ162', 'KIRMAN BELAZUR — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-04 08:16:34', '2026-06-14 20:21:39', NULL, NULL, NULL, 271),
(491, 'TRF-20260604-9620', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'JORDAN MANLEY', '', '+44 7368 509121', NULL, NULL, '04:00:00', NULL, '2026-06-06', NULL, 'XQ584', 'WYNDHAM GARDEN LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 1000.00, 'TRY', '2026-06-04 11:16:05', '2026-06-14 20:21:39', NULL, '04:00:00', NULL, 221),
(492, 'TRF-20260604-1200', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'HASSAN EL ABED', '', '+491724053495', NULL, NULL, NULL, NULL, '2026-07-05', '07:55:00', NULL, 'SERENIS HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 50.00, 'EUR', '3+3 PAX\r\n6 VALİZ 3 SIRT ÇANTASI GENİŞ BAGAJ ARAÇ MÜMKÜNSE', NULL, 0, 0, NULL, NULL, '2026-06-04 16:07:44', '2026-06-14 20:21:39', NULL, NULL, NULL, 272),
(493, 'TRF-20260604-0936', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'HASSAN EL ABED', '', '+491724053495', NULL, NULL, '18:30:00', NULL, '2026-07-11', '22:50:00', NULL, 'SERENIS HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 50.00, 'EUR', '3+3 PAX\r\n6 VALİZ 3 SIRT ÇANTASI GENİŞ BAGAJ ARAÇ MÜMKÜNSE', NULL, 0, 0, NULL, NULL, '2026-06-04 16:07:44', '2026-06-14 20:21:39', NULL, NULL, NULL, 272),
(494, 'TRF-20260604-3784', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'TOLGA IRKAYA', '', '+49 176 58878524', NULL, NULL, NULL, NULL, '2026-06-05', '04:40:00', '4M460', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 1, 25.00, 'EUR', '4 AYLIK BEBEK İÇİN PUSET YADA Ç.KOLTUĞU', NULL, 1, 1, 15.00, 'EUR', '2026-06-04 17:10:18', '2026-06-14 20:21:39', NULL, NULL, NULL, 273),
(495, 'TRF-20260604-7804', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'TOLGA IRKAYA', '', '+49 176 58878524', NULL, NULL, '16:45:00', NULL, '2026-06-10', '20:00:00', '4M359', 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 1, 25.00, 'EUR', '4 AYLIK BEBEK İÇİN PUSET YADA Ç.KOLTUĞU', NULL, 0, 0, NULL, NULL, '2026-06-04 17:10:18', '2026-06-14 20:21:39', NULL, NULL, NULL, 273),
(496, 'TRF-20260604-5971', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'EVI KEYSER', '', '+32 472 90 46 48', NULL, NULL, NULL, NULL, '2026-07-27', '07:55:00', 'FH1372', 'ROYAL SEGINUS LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-04 21:29:39', '2026-06-14 20:21:39', NULL, NULL, NULL, 274),
(497, 'TRF-20260604-8369', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'EVI KEYSER', '', '+32 472 90 46 48', NULL, NULL, '18:00:00', NULL, '2026-07-31', '20:35:00', NULL, 'ROYAL SEGINUS LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-04 21:29:39', '2026-06-14 20:21:39', NULL, NULL, NULL, 274),
(498, 'TRF-20260604-2125', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'ANISAH ABID', '', '+447306671998', NULL, NULL, '10:00:00', NULL, '2026-06-05', NULL, NULL, 'ASKA LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-04 23:48:42', '2026-06-14 20:21:39', NULL, NULL, NULL, 275),
(499, 'TRF-20260604-0116', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'İBN DAFIA MOUNIA', '', '+32484845769', NULL, NULL, NULL, NULL, '2026-06-11', '11:05:00', 'FH8013', 'TITANIC GOLF RESORT — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-04 23:53:47', '2026-06-14 20:21:39', 'Yusuf Oğuz', NULL, 73, 276),
(500, 'TRF-20260604-7144', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'İBN DAFIA MOUNIA', '', '+32484845769', NULL, NULL, NULL, NULL, '2026-06-20', NULL, NULL, 'TITANIC GOLF RESORT — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-04 23:53:47', '2026-06-14 20:21:39', NULL, NULL, NULL, 276),
(501, 'TRF-20260604-3287', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'HUSSEIN BAALBAKI', '', '+46706787844', NULL, NULL, NULL, NULL, '2026-06-05', '23:15:00', 'PC5026', 'GRAND PARK LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, 800.00, 'TRY', '2026-06-04 23:59:17', '2026-06-14 20:21:40', 'Veysel Çaçan', NULL, 65, 277),
(502, 'TRF-20260605-3857', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'TAYFUR IBAY', '', '', NULL, NULL, '08:00:00', NULL, '2026-06-06', NULL, NULL, 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-05 11:13:25', '2026-06-14 20:21:40', 'Yusuf Oğuz', '08:00:00', 73, 278),
(503, 'TRF-20260605-8249', 'transfer', 'confirmed', 'outbound', NULL, 20, 1, 'SANTI MERCIER', '', '+41766453712', NULL, NULL, NULL, NULL, '2026-06-05', '21:40:00', 'TK2432', 'CLUBMED PALMIYE KEMER — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-05 11:21:58', '2026-06-14 20:21:40', 'Osman Demir', NULL, 95, 279),
(504, 'TRF-20260605-5917', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'OZAN ÇIRAKMAN', '', '+33626058376', NULL, NULL, NULL, NULL, '2026-06-06', '12:00:00', NULL, 'CALYPTUS KIRMAN PREMIUM — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', 'TOURIST HOTEL LARA >> CALYPTUS KIRMAN \r\nOTELDEN OTELE TRANSFER', NULL, 1, 0, NULL, NULL, '2026-06-05 11:29:37', '2026-06-14 20:21:40', NULL, NULL, NULL, 280),
(505, 'TRF-20260605-7978', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'KADİR KARADAŞ', '', '+436766273557', NULL, NULL, '08:00:00', NULL, '2026-06-06', NULL, NULL, 'TUI MAGIC LIFE BELEK — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-06-05 11:36:04', '2026-06-14 20:21:40', 'Ahmet Katıl', '08:00:00', 96, 281),
(506, 'TRF-20260605-7547', 'transfer', 'confirmed', 'outbound', NULL, 28, 1, 'SHAYLEY ORD', '', '+44 7849 795069', NULL, NULL, NULL, NULL, '2026-06-16', NULL, 'EZY8561', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-05 18:42:23', '2026-06-14 20:21:40', NULL, NULL, NULL, 282),
(507, 'TRF-20260605-5594', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'SHAYLEY ORD', '', '+44 7849 795069', NULL, NULL, NULL, NULL, '0000-00-00', NULL, 'EZY8562', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-05 18:42:23', '2026-06-14 20:21:40', NULL, NULL, NULL, 282),
(508, 'TRF-20260605-4074', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'SHAYLEY ORD', '', '+44 7849 795069', NULL, NULL, NULL, NULL, '2026-06-23', NULL, 'EZY8562', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-05 18:43:57', '2026-06-14 20:21:40', NULL, NULL, NULL, 282),
(509, 'TRF-20260605-0097', 'transfer', 'confirmed', 'return', NULL, 16, 1, 'HERMIN NUREDINI', '', '+49 176 21817656', NULL, NULL, '03:00:00', NULL, '2026-06-07', '07:05:00', NULL, 'MERYAN HOTEL — OKURCALAR', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 0, 55.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-06-05 18:46:09', '2026-06-14 20:21:40', 'Mustafa Bayrak', '03:00:00', 16, 283),
(510, 'TRF-20260605-5833', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'VIKTORIA ÇEREVKO', '', '+37255605760', NULL, NULL, NULL, NULL, '2026-06-06', '08:30:00', 'XQ497', 'ORFEUS PARK HOTEL — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-05 22:34:39', '2026-06-14 20:21:40', 'Yusuf Oğuz', NULL, 73, 284),
(511, 'TRF-20260605-9016', 'transfer', 'confirmed', 'outbound', NULL, 7, 1, 'MAIK PRETZ', '', '+49 160 6475769', NULL, NULL, NULL, NULL, '2026-07-20', '06:40:00', 'PC5010', 'SIDE STORY HOTEL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-05 23:11:20', '2026-06-14 20:21:40', NULL, NULL, NULL, 122),
(512, 'TRF-20260605-5131', 'transfer', 'confirmed', 'return', NULL, 7, 1, 'MAIK PRETZ', '', '+49 160 6475769', NULL, NULL, '19:00:00', NULL, '2026-07-25', NULL, 'PC5009', 'SIDE STORY HOTEL — EVRENSEKİ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-05 23:11:20', '2026-06-14 20:21:40', NULL, NULL, NULL, 122),
(513, 'TRF-20260605-0723', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'TOMASZ LOSKA', '', '+48 782 067 268', NULL, NULL, NULL, NULL, '2026-06-06', '16:00:00', '4M886', 'KREMLIN PALACE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-05 23:19:56', '2026-06-14 20:21:40', 'Yusuf Oğuz', NULL, 73, 268),
(514, 'TRF-20260605-3970', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'TOMASZ LOSKA', '', '+48 782 067 268', NULL, NULL, '20:50:00', NULL, '2026-06-09', '23:30:00', '4M885', 'KREMLIN PALACE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-05 23:19:56', '2026-06-14 20:21:40', NULL, NULL, NULL, 268),
(515, 'TRF-20260606-9455', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'LUIDMYLA LUNINA', '', '', NULL, NULL, '15:30:00', NULL, '2026-06-13', NULL, NULL, 'ANTALYA OTOGAR — ANTALYA', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-06 15:40:04', '2026-06-14 20:21:40', NULL, NULL, NULL, 285),
(516, 'TRF-20260606-6710', 'transfer', 'confirmed', 'outbound', NULL, 16, 1, 'CEMAL INAN', '', '+491629666568', NULL, NULL, NULL, NULL, '2026-08-25', '00:45:00', 'XQ155', 'CALIDO SOL HOTEL — OKURCALAR', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 1, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-06 15:47:27', '2026-06-14 20:21:40', NULL, NULL, NULL, 286),
(517, 'TRF-20260606-3813', 'transfer', 'confirmed', 'return', NULL, 16, 1, 'CEMAL INAN', '', '+491629666568', NULL, NULL, '00:10:00', NULL, '2026-09-05', '04:05:00', 'XQ156', 'CALIDO SOL HOTEL — OKURCALAR', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 1, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-06 15:47:27', '2026-06-14 20:21:40', NULL, NULL, NULL, 286),
(518, 'TRF-20260606-2476', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'SCOTT MULLEN', '', '', NULL, NULL, NULL, NULL, '2026-06-07', '22:05:00', 'LS1845', 'SIDE VILLAGE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', 'ÖDEME ALINMIYOR', 'KLİNİK', 1, 1, 1200.00, 'TRY', '2026-06-06 15:58:45', '2026-06-14 20:21:41', 'Özgür Uçak', NULL, 101, 287),
(519, 'TRF-20260606-6221', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'SCOTT MULLEN', '', '', NULL, NULL, '19:00:00', NULL, '2026-06-14', '23:00:00', 'LS1846', 'SIDE VILLAGE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', 'ÖDEME ALINMIYOR', 'KLİNİK', 1, 1, 25.00, 'EUR', '2026-06-06 15:58:45', '2026-06-14 20:21:41', 'Abdussamed yıldız', '19:00:00', 115, 287),
(520, 'TRF-20260606-1201', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'VANESSA SARKÖZIVA', '', '+421 914 151 941', NULL, NULL, '06:00:00', NULL, '2026-06-08', '10:00:00', '4M135', 'ROYAL ALHAMBRA PALACE — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-06 16:16:15', '2026-06-14 20:21:41', NULL, NULL, NULL, 288),
(521, 'TRF-20260606-4076', 'transfer', 'confirmed', 'outbound', NULL, 28, 1, 'CALLUM KANE', '', '+447931320695', NULL, NULL, NULL, NULL, '2026-06-07', '21:25:00', 'LS3265', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 950.00, 'TRY', '2026-06-06 23:47:22', '2026-06-14 20:21:41', 'Aykut Ataman', NULL, 74, 289),
(522, 'TRF-20260607-8259', 'transfer', 'confirmed', 'outbound', NULL, 28, 1, 'Grigoriu Beatrice', '', '+40 766 107 230', NULL, NULL, NULL, NULL, '2026-06-08', '08:00:00', 'ROT7131', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-07 17:05:41', '2026-06-14 20:21:41', NULL, NULL, NULL, 290),
(523, 'TRF-20260607-3616', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'MARIO PIANA', '', '+31 6 81707939', NULL, NULL, NULL, NULL, '2026-06-16', '07:00:00', 'XQ119', 'SEADEN QUALITY — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-07 17:08:50', '2026-06-14 20:21:41', NULL, NULL, NULL, 291),
(524, 'TRF-20260607-5999', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'HARIS BEŞİROVİC', '', '', NULL, NULL, NULL, NULL, '2026-06-12', '14:25:00', 'PC5546', 'SUNBERK HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-07 17:48:08', '2026-06-14 20:21:41', NULL, NULL, NULL, 292),
(525, 'TRF-20260607-2825', 'transfer', 'confirmed', 'return', NULL, 18, 1, 'ANDREJ PRUCH', '', '+49 176 64646104', NULL, NULL, '02:00:00', NULL, '2026-06-08', '06:10:00', 'XQ124', 'LONICERA RESORT — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 65.00, 'EUR', NULL, NULL, 1, 1, 35.00, 'EUR', '2026-06-07 18:57:48', '2026-06-14 20:21:41', 'Kadir Oğuz', '02:00:00', 103, 293),
(526, 'TRF-20260607-4060', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'IVANA JURISIC', '', '+385 95 737 4776', NULL, NULL, '13:00:00', NULL, '2026-06-08', '14:55:00', NULL, 'RIXOS PREMIUM BELEK', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 1, 30.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-07 19:04:07', '2026-06-14 20:21:41', NULL, NULL, NULL, 244),
(527, 'TRF-20260607-3983', 'transfer', 'confirmed', 'return', NULL, 25, 1, 'KAZIM BİLECAN', '', '+41796386363', NULL, NULL, '05:30:00', NULL, '2026-06-08', NULL, NULL, 'ARMAS LIFE HOTEL — BOĞAZKENT', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 35.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-07 23:39:55', '2026-06-14 20:21:41', 'Berkay İpek', '05:30:00', 88, 294),
(528, 'TRF-20260608-2811', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MARK RYDER', '', '+44 7885 992212', NULL, NULL, NULL, NULL, '2026-06-15', '13:15:00', 'LS895', 'DELPHIN BE GRAND — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-08 17:48:34', '2026-06-14 20:21:41', NULL, NULL, NULL, 295),
(529, 'TRF-20260608-3884', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'MELISA PACOLLI', '', '+49 162 6779424', NULL, NULL, '19:45:00', NULL, '2026-06-12', '23:35:00', NULL, 'SEADEN QUALITY — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-08 17:55:43', '2026-06-14 20:21:41', 'Suat Tosun', '19:45:00', 114, 296),
(530, 'TRF-20260608-6758', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'SALİH KARACA', '', '+31 6 42824012', NULL, NULL, NULL, NULL, '2026-06-21', '22:10:00', 'CD5099', 'GRANADA LUXURY BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-08 18:05:33', '2026-06-14 20:21:41', NULL, NULL, NULL, 297),
(531, 'TRF-20260608-7316', 'transfer', 'confirmed', 'return', NULL, 25, 1, 'BÜŞRA KOYUNCU', '', '+49 160 3104176', NULL, NULL, '12:30:00', NULL, '2026-06-10', '14:30:00', NULL, 'ANTALYA OTOGAR — ANTALYA', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 45.00, 'EUR', 'BELEK BEACH RESORT HOTELDEN ANTALYA OTOGAR', NULL, 1, 1, 1600.00, 'TRY', '2026-06-08 18:12:43', '2026-06-14 20:21:42', 'Mustafa Esen', '12:30:00', 31, 298),
(532, 'TRF-20260608-5742', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'MARK RYDER', '', '+44 7885 992212', NULL, NULL, '11:30:00', NULL, '2026-06-25', '14:10:00', NULL, 'DELPHIN BE GRAND — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-08 18:19:38', '2026-06-14 20:21:42', NULL, NULL, NULL, 295),
(533, 'TRF-20260608-1573', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'FAUZIA AHMED', '', '+44 7884 977191', NULL, NULL, '07:30:00', NULL, '2026-06-09', '09:40:00', 'XQ592', 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 25.00, 'GBP', NULL, NULL, 1, 1, NULL, 'GBP', '2026-06-08 18:22:49', '2026-06-14 20:21:42', 'Yusuf Oğuz', '07:30:00', 73, 299),
(534, 'TRF-20260608-3115', 'transfer', 'confirmed', 'return', NULL, 19, 1, 'NAZARII VASIK', '', '+49 176 20362113', NULL, NULL, '04:30:00', NULL, '2026-06-12', '07:05:00', 'PC5005', 'PINE BEACH — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 1, 1, 20.00, 'EUR', '2026-06-08 18:26:41', '2026-06-14 20:21:42', 'Mert Yıldız', '04:30:00', 111, 300),
(535, 'TRF-20260609-3819', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'MARILENA BOECA', '', '+40 799 421 240', NULL, NULL, '19:30:00', NULL, '2026-06-09', '22:30:00', 'W43042', 'GRAND PARK LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-09 10:49:00', '2026-06-14 20:21:42', 'Yusuf Oğuz', '19:30:00', 73, 301),
(536, 'TRF-20260609-0644', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'MIDLEY BRUINTJES', '', '+31 6 43858845', NULL, NULL, NULL, NULL, '2026-09-23', '08:15:00', 'XQ257', 'FAME RESIDANCE LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-09 14:53:58', '2026-06-14 20:21:42', NULL, NULL, NULL, 302),
(537, 'TRF-20260609-2992', 'transfer', 'confirmed', 'return', NULL, 13, 1, 'MIDLEY BRUINTJES', '', '+31 6 43858845', NULL, NULL, '19:15:00', NULL, '2026-09-29', NULL, 'XQ254', 'FAME RESIDANCE LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-09 14:53:58', '2026-06-14 20:21:42', NULL, NULL, NULL, 302),
(538, 'TRF-20260609-4109', 'transfer', 'confirmed', 'outbound', NULL, 15, 1, 'FLORIN GÖSGENS', '', '+49 179 6126558', NULL, NULL, NULL, NULL, '2026-08-09', '08:05:00', 'XQ231', 'CALIDO MARIS — ÇENGER', 0, NULL, NULL, NULL, NULL, NULL, 2, 2, 1, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-09 15:07:25', '2026-06-14 20:21:42', NULL, NULL, NULL, 303),
(539, 'TRF-20260610-1206', 'transfer', 'confirmed', 'outbound', NULL, 8, 1, 'KATIE MCGAVIN', '', '+1 (623) 399-3316', NULL, NULL, NULL, NULL, '2026-06-12', '19:30:00', 'XQ599', 'SIDE MOON PALACE — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 2200.00, 'TRY', NULL, NULL, 1, 0, NULL, NULL, '2026-06-10 17:48:39', '2026-06-14 20:21:42', NULL, NULL, NULL, 304),
(540, 'TRF-20260610-1053', 'transfer', 'confirmed', 'return', NULL, 28, 1, 'CALLUM CANE', '', '+44 7931 320695', NULL, NULL, '19:30:00', NULL, '2026-06-12', '22:35:00', NULL, 'AYDIN BEY QUEEN\'S PALACE — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-10 17:51:23', '2026-06-14 20:21:42', NULL, NULL, NULL, 305),
(541, 'TRF-20260610-9805', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'AJDIN TOROMANOVIC', '', '+31 6 23257743', NULL, NULL, '03:15:00', NULL, '2026-06-13', NULL, 'XQ614', 'PALOME PERISSIA — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 4, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-10 18:00:13', '2026-06-14 20:21:42', NULL, '03:15:00', NULL, 306),
(542, 'TRF-20260610-3316', 'transfer', 'confirmed', 'outbound', NULL, 28, 3, 'VEDRAN JUKIC', '', '+385 98 315 404', NULL, NULL, NULL, NULL, '2026-06-17', '15:00:00', 'XQ521', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 17, 0, 0, 65.00, 'EUR', '8 ADULTS 9 CHILDREN', NULL, 0, 0, NULL, NULL, '2026-06-10 18:08:49', '2026-06-14 20:21:42', NULL, NULL, NULL, 307),
(543, 'TRF-20260610-3031', 'transfer', 'confirmed', 'return', NULL, 28, 3, 'VEDRAN JUKIC', '', '+385 98 315 404', NULL, NULL, '06:00:00', NULL, '2026-06-24', '09:10:00', 'XQ520', 'THE LAND OF LEGENDS KINGDOM HOTEL — KADRİYE', 0, NULL, NULL, NULL, NULL, NULL, 17, 0, 0, 65.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-10 18:08:50', '2026-06-14 20:21:42', NULL, NULL, NULL, 307),
(544, 'TRF-20260610-4944', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'SIDRA KAUSER', '', '+44 7404 876055', NULL, NULL, NULL, NULL, '2026-06-11', '23:05:00', 'LS1291', 'A HOTEL SIDE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 2200.00, 'TRY', NULL, NULL, 1, 0, NULL, NULL, '2026-06-10 18:14:18', '2026-06-14 20:21:43', NULL, NULL, NULL, 308),
(545, 'TRF-20260610-7357', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'ADRIAN AMBRUS', '', '+40 742 610 969', NULL, NULL, NULL, NULL, '2026-06-12', '14:00:00', 'RO6453', 'DREAM WATER WORLD — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-10 18:18:09', '2026-06-14 20:21:43', 'Yusuf Oğuz', NULL, 73, 309),
(546, 'TRF-20260610-4025', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'ADRIAN AMBRUS', '', '+40 742 610 969', NULL, NULL, '11:40:00', NULL, '2026-06-19', '15:40:00', 'RO6454', 'DREAM WATER WORLD — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-10 18:18:09', '2026-06-14 20:21:43', NULL, NULL, NULL, 309),
(548, 'TRF-20260610-3669', 'transfer', 'confirmed', 'return', NULL, 1, NULL, 'MUSTAFA KEMAL YILDIRIM', '', '', NULL, NULL, '05:30:00', NULL, '2026-06-11', '07:50:00', 'PC1875', 'MANAVGAT — MERKEZ', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-10 18:47:37', '2026-06-14 20:21:43', NULL, NULL, NULL, 310),
(549, 'TRF-20260611-8686', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'ALEXANDER ÇEREVKO', '', '+372 5814 7555', NULL, NULL, '19:30:00', NULL, '2026-06-12', NULL, 'FHY417', 'ORFEUS PARK HOTEL — ÇOLAKLI', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-11 09:17:00', '2026-06-14 20:21:43', NULL, NULL, NULL, 311),
(550, 'TRF-20260611-7941', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'BEGÜM CANLI', '', '', NULL, NULL, NULL, NULL, '2026-06-12', '01:25:00', 'VF4012', 'ALEGRİA HOTEL', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-11 10:01:44', '2026-06-14 20:21:43', 'Zeus vip', NULL, 110, 312),
(551, 'TRF-20260611-1247', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'MUSTAFA KEMAL YILDIRIM', '', '+90 536 717 92 82', NULL, NULL, NULL, NULL, '2026-06-12', '22:25:00', 'PC1875', 'MANAVGAT — MERKEZ', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 0.00, 'EUR', NULL, NULL, 1, 1, 25.00, 'EUR', '2026-06-11 10:03:03', '2026-06-14 20:21:43', 'Suat Tosun', NULL, 114, 313),
(552, 'TRF-20260611-2295', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'OZAN ÇIRAKMAN', '', '+33 6 26 05 83 76', NULL, NULL, '08:45:00', NULL, '2026-06-12', '13:35:00', 'XQ510', 'CALYPTUS KIRMAN PREMIUM — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-11 10:26:04', '2026-06-14 20:21:43', 'Yusuf Oğuz', '08:45:00', 73, 280),
(553, 'TRF-20260611-1640', 'transfer', 'confirmed', 'outbound', NULL, 12, 1, 'SARA ELCHIRI', '', '+4581718086', NULL, NULL, NULL, NULL, '2026-06-12', '18:55:00', 'PC5050', 'LAVINIA APART HOTEL — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 75.00, 'EUR', NULL, NULL, 1, 1, NULL, 'EUR', '2026-06-11 14:02:59', '2026-06-14 20:21:43', 'Yusuf Oğuz', NULL, 73, 314),
(554, 'TRF-20260611-8995', 'transfer', 'confirmed', 'outbound', NULL, 11, 1, 'WILLI SENG', '', '+49 163 6799412', NULL, NULL, NULL, NULL, '2026-06-28', '12:30:00', 'XQ141', 'SEADEN SEA PLANET — KIZILOT', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-11 18:10:37', '2026-06-14 20:21:43', NULL, NULL, NULL, 315),
(555, 'TRF-20260611-4553', 'transfer', 'confirmed', 'return', NULL, 11, 1, 'WILLI SENG', '', '+49 163 6799412', NULL, NULL, '12:00:00', NULL, '2026-07-09', '16:35:00', 'XQ140', 'SEADEN SEA PLANET — KIZILOT', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-11 18:10:37', '2026-06-14 20:21:43', NULL, NULL, NULL, 315),
(556, 'TRF-20260612-6592', 'transfer', 'confirmed', 'return', NULL, 20, 1, 'BİRSEN TOK', '', '+90 535 464 22 52', NULL, NULL, '05:45:00', NULL, '2026-06-15', '09:00:00', NULL, 'KEMER MERKEZ — KEMER', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', 'ARSLANBUCAK MH 235SK ANKA EVLERİ 2', NULL, 0, 0, NULL, NULL, '2026-06-12 00:03:39', '2026-06-14 20:21:43', NULL, NULL, NULL, 316),
(557, 'TRF-20260612-0393', 'transfer', 'confirmed', 'return', NULL, 16, NULL, 'CEMAL YILDIRIM', '', '+49 1573 6153016', NULL, NULL, '12:00:00', NULL, '2026-06-15', '17:05:00', 'XQ154', 'ÖZ HOTELS İNCEKUM — İNCEKUM', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-12 00:06:30', '2026-06-14 20:21:43', NULL, NULL, NULL, 225),
(558, 'TRF-20260612-3846', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'HAROON NASIR', '', '+44 7923 243956', NULL, NULL, NULL, NULL, '2026-07-21', '00:55:00', 'XQ595', 'GRAND PARK LARA — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 30.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-12 00:14:00', '2026-06-14 20:21:43', NULL, NULL, NULL, 317),
(559, 'TRF-20260612-1385', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'GERALD HELMDORF', '', '+49 1520 4976956', NULL, NULL, NULL, NULL, '2026-09-11', NULL, 'XQ277', 'SIDE MOON PALACE — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-12 00:17:44', '2026-06-14 20:21:44', NULL, NULL, NULL, 318),
(560, 'TRF-20260612-9096', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'GERALD HELMDORF', '', '+49 1520 4976956', NULL, NULL, NULL, NULL, '2026-09-25', NULL, 'XQ278', 'SIDE MOON PALACE — SORGUN', 0, NULL, NULL, NULL, NULL, NULL, 6, 0, 0, 45.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-12 00:17:44', '2026-06-14 20:21:44', NULL, NULL, NULL, 318),
(561, 'TRF-20260612-7349', 'transfer', 'confirmed', 'outbound', NULL, 15, 1, 'ZOLTAN SZABO', '', 'Zoltán Szabó', NULL, NULL, NULL, NULL, '2026-06-19', '12:45:00', 'FH646', 'SULTAN OF DREAMS — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-12 13:00:23', '2026-06-14 20:21:44', NULL, NULL, NULL, 319),
(562, 'TRF-20260612-7898', 'transfer', 'confirmed', 'return', NULL, 15, 1, 'ZOLTAN SZABO', '', 'Zoltán Szabó', NULL, NULL, '02:30:00', NULL, '2026-06-26', '06:25:00', 'FH645', 'SULTAN OF DREAMS — KIZILAĞAÇ', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 55.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-12 13:00:23', '2026-06-14 20:21:44', NULL, NULL, NULL, 319),
(563, 'TRF-20260612-1295', 'transfer', 'confirmed', 'outbound', NULL, 19, 1, 'CERRİ JONES', '', '+44 7976 640942', NULL, NULL, NULL, NULL, '2026-06-12', '23:20:00', 'XQ597', 'GRANADA LUXURY BELEK — BELEK', 0, NULL, NULL, NULL, NULL, NULL, 5, 0, 0, 35.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-12 15:11:57', '2026-06-14 20:21:44', NULL, NULL, NULL, 320),
(564, 'TRF-20260613-1437', 'transfer', 'confirmed', 'outbound', NULL, 18, 3, 'DIANA BOGDANOVA', '', '+44 7540 467409', NULL, NULL, NULL, NULL, '2026-07-07', '15:55:00', 'XQ525', 'AZURA WORLD — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 90.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-13 12:42:50', '2026-06-14 20:21:44', NULL, NULL, NULL, 321),
(565, 'TRF-20260613-7081', 'transfer', 'confirmed', 'return', NULL, 18, 3, 'DIANA BOGDANOVA', '', '+44 7540 467409', NULL, NULL, '12:00:00', NULL, '2026-07-14', '16:30:00', 'XQ526', 'AZURA WORLD — TÜRKLER', 0, NULL, NULL, NULL, NULL, NULL, 9, 0, 0, 90.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-13 12:42:50', '2026-06-14 20:21:44', NULL, NULL, NULL, 321),
(566, 'TRF-20260613-3162', 'transfer', 'confirmed', 'outbound', NULL, 6, 1, 'ARTUR WIESNER', '', '+49 176 63390749', NULL, NULL, NULL, NULL, '2026-06-17', '16:45:00', 'XQ161', 'DIAMOND DELUXE — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-13 13:04:38', '2026-06-14 20:21:44', NULL, NULL, NULL, 322),
(567, 'TRF-20260613-5318', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'ARTUR WIESNER', '', '+49 176 63390749', NULL, NULL, '05:00:00', NULL, NULL, '09:00:00', 'XQ160', 'DIAMOND DELUXE — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-13 13:04:38', '2026-06-14 20:21:44', NULL, NULL, NULL, 322),
(568, 'TRF-20260613-4510', 'transfer', 'confirmed', 'return', NULL, 6, 1, 'ARTUR WIESNER', '', '+49 176 63390749', NULL, NULL, '05:00:00', NULL, '2026-06-25', '09:00:00', 'XQ160', 'DIAMOND DELUXE — GÜNDOĞDU', 0, NULL, NULL, NULL, NULL, NULL, 2, 1, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-13 13:06:07', '2026-06-14 20:21:44', NULL, NULL, NULL, 322),
(569, 'TRF-20260613-7479', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'ASHFAQUE CHOWDHURY', '', '+447944692928', NULL, NULL, NULL, NULL, '2026-06-20', '21:35:00', 'W95331', 'CATS GARDEN HOTEL — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 70.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-13 16:53:17', '2026-06-14 20:21:44', NULL, NULL, NULL, 323),
(570, 'TRF-20260613-1563', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'NICOLE MEZNER', '', '', NULL, NULL, '03:00:00', NULL, '2026-06-14', '06:00:00', 'XQ188', 'GALA RESIDANCE — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 40.00, 'EUR', NULL, NULL, 1, 0, NULL, NULL, '2026-06-13 18:31:53', '2026-06-14 20:21:44', NULL, NULL, NULL, 324),
(571, 'TRF-20260613-0652', 'transfer', 'confirmed', 'outbound', NULL, 13, 1, 'FATMA YATOUJI', '', '+33620365890', NULL, NULL, NULL, NULL, '2026-06-13', '22:40:00', 'XQ511', 'CONCORDE DELUXE — KUNDU', 0, NULL, NULL, NULL, NULL, NULL, 3, 0, 0, 25.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-13 19:07:26', '2026-06-14 20:21:44', NULL, NULL, NULL, 325),
(572, 'TRF-20260614-8630', 'transfer', 'confirmed', 'outbound', NULL, 12, NULL, 'JAY LOVELL', '', '+44 7873 935625', NULL, NULL, NULL, NULL, '2026-06-14', '22:05:00', 'LS1845', 'COOKS CLUB ALANYA — ALANYA', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 75.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-14 12:07:26', '2026-06-14 20:21:44', NULL, NULL, NULL, 326),
(573, 'TRF-20260614-8195', 'transfer', 'confirmed', 'outbound', NULL, 2, 1, 'HASSAN FAIZAN', '', '+44 7481 801028', NULL, NULL, NULL, NULL, '2026-06-17', '23:00:00', 'LS1727', 'CASTIVAL HOTEL — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-14 12:10:40', '2026-06-14 20:21:44', NULL, NULL, NULL, 327),
(574, 'TRF-20260614-9761', 'transfer', 'confirmed', 'return', NULL, 2, 1, 'HASSAN FAIZAN', '', '+44 7481 801028', NULL, NULL, '10:00:00', NULL, '2026-06-24', '13:45:00', NULL, 'CASTIVAL HOTEL — KUMKÖY', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 40.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-14 12:10:40', '2026-06-14 20:21:45', NULL, NULL, NULL, 327),
(575, 'TRF-20260614-8053', 'transfer', 'confirmed', 'outbound', NULL, 1, 1, 'ahmet keskin', 'ahmet@netask.com.tr', '905078485254', NULL, NULL, NULL, NULL, '2026-06-14', '21:44:00', 'TEST', 'ACARKENT — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-14 20:43:29', '2026-06-14 23:55:49', NULL, NULL, NULL, 328),
(576, 'TRF-20260614-9228', 'transfer', 'confirmed', 'return', NULL, 1, 1, 'ahmet keskin', 'ahmet@netask.com.tr', '905078485254', NULL, NULL, '09:00:00', NULL, '2026-06-15', '10:00:00', 'TEST2', 'ACARKENT — SİDE', 0, NULL, NULL, NULL, NULL, NULL, 2, 0, 0, 50.00, 'EUR', NULL, NULL, 0, 0, NULL, NULL, '2026-06-14 20:43:30', '2026-06-14 20:43:30', NULL, NULL, NULL, 328);

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
-- Tablo için tablo yapısı `booking_drivers`
--

CREATE TABLE `booking_drivers` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `driver_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Tablo döküm verisi `booking_drivers`
--

INSERT INTO `booking_drivers` (`id`, `booking_id`, `driver_id`, `assigned_at`) VALUES
(13, 117, 2, '2026-05-03 10:21:56'),
(10, 69, 2, '2026-05-03 10:15:33'),
(14, 92, 1, '2026-05-03 12:02:42'),
(17, 96, 2, '2026-05-04 07:38:59'),
(18, 99, 2, '2026-05-04 07:39:15'),
(19, 121, 1, '2026-05-04 07:39:26'),
(21, 91, 1, '2026-05-04 19:11:29'),
(22, 108, 1, '2026-05-07 10:42:20'),
(23, 257, 1, '2026-05-07 14:59:29'),
(24, 151, 1, '2026-05-07 17:38:52'),
(25, 261, 2, '2026-05-07 21:32:21'),
(26, 258, 2, '2026-05-07 21:32:42'),
(27, 164, 1, '2026-05-07 21:32:59'),
(28, 71, 1, '2026-05-08 06:09:45'),
(29, 269, 1, '2026-05-08 18:27:11'),
(30, 262, 2, '2026-05-08 18:27:33'),
(31, 111, 2, '2026-05-08 18:28:51'),
(32, 282, 2, '2026-05-09 18:05:03'),
(33, 90, 1, '2026-05-09 18:05:27'),
(34, 166, 1, '2026-05-10 03:13:44'),
(35, 283, 1, '2026-05-11 17:56:42'),
(36, 307, 1, '2026-05-13 08:29:16'),
(37, 305, 2, '2026-05-17 18:48:56'),
(38, 168, 1, '2026-05-17 18:49:44'),
(39, 351, 2, '2026-05-20 12:19:28'),
(40, 464, 1, '2026-06-02 12:42:21'),
(41, 478, 2, '2026-06-05 16:59:36'),
(42, 198, 2, '2026-06-11 21:19:56'),
(43, 549, 2, '2026-06-12 14:31:50'),
(44, 539, 2, '2026-06-12 14:32:34');

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
(69, 52, 'TRF-20260429-6978', 'adult', 'ANNA JAKUZSEW', 0, '2026-04-29 07:33:02'),
(70, 52, 'TRF-20260429-6978', 'adult', 'ALEX JAKUZSEW', 1, '2026-04-29 07:33:02'),
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
(120, 68, 'TRF-20260430-9161', 'adult', 'QUAIMA AIT EL HADJ', 0, '2026-04-29 21:50:06'),
(121, 68, 'TRF-20260430-9161', 'adult', 'BİLAL AAMİ', 1, '2026-04-29 21:50:06'),
(122, 68, 'TRF-20260430-9161', 'adult', 'NOUSSAİR AAMİ', 2, '2026-04-29 21:50:06'),
(130, 70, 'TRF-20260430-5242', 'adult', 'DJORDY HENDRICKS', 0, '2026-04-30 08:38:33'),
(131, 70, 'TRF-20260430-5242', 'adult', 'JOY KERSTEN', 1, '2026-04-30 08:38:33'),
(132, 70, 'TRF-20260430-5242', 'adult', 'JAZZY HENDRICKS', 2, '2026-04-30 08:38:33'),
(137, 72, 'TRF-20260430-6823', 'adult', 'AYMAN EL BEKKAOUI', 0, '2026-04-30 09:02:32'),
(138, 72, 'TRF-20260430-6823', 'adult', 'JENNY NGUYEN', 1, '2026-04-30 09:02:32'),
(139, 72, 'TRF-20260430-6823', 'adult', 'MARINE NGUYEN', 2, '2026-04-30 09:02:32'),
(140, 72, 'TRF-20260430-6823', 'adult', 'LAMINE MEZIANE', 3, '2026-04-30 09:02:32'),
(141, 72, 'TRF-20260430-6823', 'child', 'JADE MEZIANE', 0, '2026-04-30 09:02:32'),
(142, 72, 'TRF-20260430-6823', 'child', 'AVAH MEZIANE', 1, '2026-04-30 09:02:32'),
(144, 73, 'TRF-20260430-6063', 'adult', 'RAZİYE ERYILMAZ', 0, '2026-04-30 09:07:56'),
(151, 54, 'TRF-20260429-2347', 'adult', 'PATRICIA HIBBERT', 0, '2026-04-30 18:54:20'),
(152, 54, 'TRF-20260429-2347', 'adult', 'KARINA KARTILL', 1, '2026-04-30 18:54:20'),
(153, 49, 'TRF-20260429-7985', 'adult', 'EDVIN TISK', 0, '2026-04-30 18:54:39'),
(154, 49, 'TRF-20260429-7985', 'adult', 'ANA TISK', 1, '2026-04-30 18:54:39'),
(162, 77, 'TRF-20260502-2358', 'adult', 'VITALI LEIS', 0, '2026-05-02 10:15:33'),
(163, 77, 'TRF-20260502-2358', 'adult', 'VALERJIA LEIS', 1, '2026-05-02 10:15:33'),
(164, 76, 'TRF-20260502-5801', 'adult', 'VITALI LEIS', 0, '2026-05-02 10:16:41'),
(165, 76, 'TRF-20260502-5801', 'adult', 'VALERJIA LEIS', 1, '2026-05-02 10:16:41'),
(166, 76, 'TRF-20260502-5801', 'adult', 'MAX LÜDECKE', 2, '2026-05-02 10:16:41'),
(167, 76, 'TRF-20260502-5801', 'adult', 'ALINA LÜDECKE', 3, '2026-05-02 10:16:41'),
(168, 71, 'TRF-20260430-8491', 'adult', 'ANGIE STARMANS', 0, '2026-05-02 10:22:47'),
(169, 71, 'TRF-20260430-8491', 'adult', 'JAXX LAMERS', 1, '2026-05-02 10:22:47'),
(170, 71, 'TRF-20260430-8491', 'adult', 'BEN ZENDEN', 2, '2026-05-02 10:22:47'),
(171, 71, 'TRF-20260430-8491', 'adult', 'SANDRA EYKENBOOM', 3, '2026-05-02 10:22:47'),
(179, 80, 'TRF-20260502-2670', 'adult', 'MUDASSIR MANSHA', 0, '2026-05-02 11:30:58'),
(180, 80, 'TRF-20260502-2670', 'adult', 'ZULAIKHA BIBI', 1, '2026-05-02 11:30:58'),
(181, 80, 'TRF-20260502-2670', 'adult', 'MUHAMMAD BILAL', 2, '2026-05-02 11:30:58'),
(182, 80, 'TRF-20260502-2670', 'adult', 'SAIMA BILAL', 3, '2026-05-02 11:30:58'),
(183, 80, 'TRF-20260502-2670', 'child', 'AYAT NOOR', 0, '2026-05-02 11:30:58'),
(198, 85, 'TRF-20260502-0476', 'adult', 'BJÖRN SCHMELZER', 0, '2026-05-02 12:26:35'),
(199, 85, 'TRF-20260502-0476', 'adult', 'ANDREA SCHMELZER', 1, '2026-05-02 12:26:35'),
(200, 85, 'TRF-20260502-0476', 'adult', 'BENNET SCHMELZER', 2, '2026-05-02 12:26:35'),
(201, 86, 'TRF-20260502-2062', 'adult', 'RACHELLE SUTTON', 0, '2026-05-02 12:31:16'),
(202, 86, 'TRF-20260502-2062', 'adult', 'CHRISTOPHER HADLEY', 1, '2026-05-02 12:31:16'),
(203, 86, 'TRF-20260502-2062', 'adult', 'EVAN SUTTON', 2, '2026-05-02 12:31:16'),
(216, 89, 'TRF-20260502-6222', 'adult', 'KACPER BEC', 0, '2026-05-02 12:45:05'),
(217, 89, 'TRF-20260502-6222', 'adult', 'NATHAN MARSZEWSKİ', 1, '2026-05-02 12:45:05'),
(220, 90, 'TRF-20260502-3980', 'adult', 'KACPER BEC', 0, '2026-05-02 12:47:41'),
(221, 90, 'TRF-20260502-3980', 'adult', 'NATHAN MARSZEWSKİ', 1, '2026-05-02 12:47:41'),
(222, 91, 'TRF-20260502-9319', 'adult', 'HELENA WIEBE', 0, '2026-05-02 12:49:41'),
(223, 91, 'TRF-20260502-9319', 'adult', 'JAKOB WIEBE', 1, '2026-05-02 12:49:41'),
(224, 91, 'TRF-20260502-9319', 'adult', 'BERRAK KLASSEN', 2, '2026-05-02 12:49:41'),
(227, 92, 'TRF-20260502-2019', 'adult', 'VERA ALBERTI', 0, '2026-05-02 12:54:11'),
(228, 92, 'TRF-20260502-2019', 'adult', 'ANDREI COSAREV', 1, '2026-05-02 12:54:11'),
(233, 94, 'TRF-20260502-7320', 'adult', 'TAHSEEN SHAH', 0, '2026-05-02 13:01:03'),
(234, 94, 'TRF-20260502-7320', 'adult', 'HASINA BATOOL', 1, '2026-05-02 13:01:03'),
(235, 94, 'TRF-20260502-7320', 'adult', 'EMANI SHAH', 2, '2026-05-02 13:01:03'),
(236, 94, 'TRF-20260502-7320', 'adult', 'INAYA SHAH', 3, '2026-05-02 13:01:03'),
(237, 95, 'TRF-20260502-9739', 'adult', 'İLKAY KOPARAN', 0, '2026-05-02 13:09:26'),
(238, 95, 'TRF-20260502-9739', 'adult', 'ALEX KOCH', 1, '2026-05-02 13:09:26'),
(239, 95, 'TRF-20260502-9739', 'adult', 'ELISA PAGNOZZO', 2, '2026-05-02 13:09:26'),
(240, 95, 'TRF-20260502-9739', 'adult', 'NADINE WUNSCH', 3, '2026-05-02 13:09:26'),
(245, 96, 'TRF-20260502-8813', 'adult', 'CAN KLEISMAN', 0, '2026-05-02 13:36:02'),
(246, 96, 'TRF-20260502-8813', 'adult', 'CEM KLEISMAN', 1, '2026-05-02 13:36:02'),
(247, 97, 'TRF-20260502-4170', 'adult', 'MUSTAFA LALE', 0, '2026-05-02 13:43:43'),
(248, 97, 'TRF-20260502-4170', 'adult', 'VEDAT DEMİRALP', 1, '2026-05-02 13:43:43'),
(249, 97, 'TRF-20260502-4170', 'adult', 'NEVZAT SARIKAYA', 2, '2026-05-02 13:43:43'),
(250, 97, 'TRF-20260502-4170', 'adult', 'HAYRİ GÜROĞLU', 3, '2026-05-02 13:43:43'),
(251, 98, 'TRF-20260502-8809', 'adult', 'MUSTAFA LALE', 0, '2026-05-02 13:43:43'),
(252, 98, 'TRF-20260502-8809', 'adult', 'VEDAT DEMİRALP', 1, '2026-05-02 13:43:43'),
(253, 98, 'TRF-20260502-8809', 'adult', 'NEVZAT SARIKAYA', 2, '2026-05-02 13:43:43'),
(254, 98, 'TRF-20260502-8809', 'adult', 'HAYRİ GÜROĞLU', 3, '2026-05-02 13:43:43'),
(255, 99, 'TRF-20260502-9269', 'adult', 'ISABEL SCULTZ', 0, '2026-05-02 14:50:28'),
(256, 99, 'TRF-20260502-9269', 'adult', 'JULY ROSE SCULTZ', 1, '2026-05-02 14:50:28'),
(257, 99, 'TRF-20260502-9269', 'adult', 'JAN CAPALBO BÖHME', 2, '2026-05-02 14:50:28'),
(258, 100, 'TRF-20260502-4307', 'adult', 'SERGEN ÖZMEN', 0, '2026-05-02 15:01:27'),
(259, 100, 'TRF-20260502-4307', 'adult', 'SELİN NUR GÜLER', 1, '2026-05-02 15:01:27'),
(260, 100, 'TRF-20260502-4307', 'adult', 'DAVID BOJKOVIC', 2, '2026-05-02 15:01:27'),
(261, 100, 'TRF-20260502-4307', 'adult', 'JENNIFER VANESSA MOKROS', 3, '2026-05-02 15:01:27'),
(284, 107, 'TRF-20260502-4799', 'adult', 'DOERTA KUBERSKI', 0, '2026-05-02 15:24:06'),
(289, 108, 'TRF-20260502-8861', 'adult', 'ILONA KÖNİG', 0, '2026-05-02 15:33:37'),
(290, 108, 'TRF-20260502-8861', 'adult', 'MARGARETE KÖNİG', 1, '2026-05-02 15:33:37'),
(291, 108, 'TRF-20260502-8861', 'adult', 'LISA KÖNİG', 2, '2026-05-02 15:33:37'),
(292, 108, 'TRF-20260502-8861', 'adult', 'ALINA KÖNİG', 3, '2026-05-02 15:33:37'),
(296, 110, 'TRF-20260502-0163', 'adult', 'HUSSEIN EL ABED', 0, '2026-05-02 15:39:00'),
(297, 110, 'TRF-20260502-0163', 'adult', 'ALI EL ABED', 1, '2026-05-02 15:39:00'),
(298, 110, 'TRF-20260502-0163', 'adult', 'ALI MENCHO', 2, '2026-05-02 15:39:00'),
(299, 111, 'TRF-20260502-2169', 'adult', 'YUSSOF ALAM', 0, '2026-05-02 15:50:43'),
(300, 111, 'TRF-20260502-2169', 'adult', 'JAMSHED ALAM', 1, '2026-05-02 15:50:43'),
(301, 111, 'TRF-20260502-2169', 'adult', 'HAMSA ALAM', 2, '2026-05-02 15:50:43'),
(323, 117, 'TRF-20260502-1128', 'adult', 'DIANA RÜLANDER', 0, '2026-05-02 16:13:25'),
(324, 117, 'TRF-20260502-1128', 'adult', 'PETRA HOFFMAN', 1, '2026-05-02 16:13:25'),
(325, 118, 'TRF-20260502-7951', 'adult', 'DIANA RÜLANDER', 0, '2026-05-02 16:13:25'),
(326, 118, 'TRF-20260502-7951', 'adult', 'PETRA HOFFMAN', 1, '2026-05-02 16:13:25'),
(327, 119, 'TRF-20260502-5995', 'adult', 'NADINE RADES', 0, '2026-05-02 16:22:29'),
(328, 119, 'TRF-20260502-5995', 'adult', 'SYLVIA RADES', 1, '2026-05-02 16:22:29'),
(329, 119, 'TRF-20260502-5995', 'adult', 'RALF RADES', 2, '2026-05-02 16:22:29'),
(330, 119, 'TRF-20260502-5995', 'adult', 'MARTIN STREICH', 3, '2026-05-02 16:22:29'),
(331, 119, 'TRF-20260502-5995', 'adult', 'KAROLA STREICH', 4, '2026-05-02 16:22:29'),
(332, 119, 'TRF-20260502-5995', 'adult', 'JÜRGEN STREICH', 5, '2026-05-02 16:22:29'),
(333, 119, 'TRF-20260502-5995', 'adult', 'CINDY KOWALZIK', 6, '2026-05-02 16:22:29'),
(334, 119, 'TRF-20260502-5995', 'adult', 'JULIANE KOWALZIK', 7, '2026-05-02 16:22:29'),
(343, 121, 'TRF-20260502-3224', 'adult', 'GALINA STENNING', 0, '2026-05-02 19:03:14'),
(346, 87, 'TRF-20260502-5072', 'adult', 'SHANTHOSAN SIVANDAN', 0, '2026-05-02 20:47:46'),
(347, 87, 'TRF-20260502-5072', 'adult', 'KISHOK KUGANSEN', 1, '2026-05-02 20:47:46'),
(348, 87, 'TRF-20260502-5072', 'adult', 'BAVIKESH SIVAKUMAR', 2, '2026-05-02 20:47:47'),
(349, 87, 'TRF-20260502-5072', 'adult', 'UZEIR AHMED', 3, '2026-05-02 20:47:47'),
(351, 104, 'TRF-20260502-2702', 'adult', 'OWAIS ASIF', 0, '2026-05-03 07:36:27'),
(352, 104, 'TRF-20260502-2702', 'adult', 'MADIHA RASHID', 1, '2026-05-03 07:36:27'),
(353, 104, 'TRF-20260502-2702', 'adult', 'MALIHA IRFAN', 2, '2026-05-03 07:36:27'),
(354, 104, 'TRF-20260502-2702', 'adult', 'ZEESHAN SAGHIR', 3, '2026-05-03 07:36:27'),
(359, 124, 'TRF-20260503-3163', 'adult', 'ANNA SCHILDER', 0, '2026-05-03 07:45:21'),
(360, 124, 'TRF-20260503-3163', 'adult', 'CHRISTIAN SCHILDER', 1, '2026-05-03 07:45:21'),
(361, 125, 'TRF-20260503-9785', 'adult', 'ANNA SCHILDER', 0, '2026-05-03 07:45:21'),
(362, 125, 'TRF-20260503-9785', 'adult', 'CHRISTIAN SCHILDER', 1, '2026-05-03 07:45:21'),
(363, 126, 'TRF-20260503-8542', 'adult', 'KIM HOEKSTRA', 0, '2026-05-03 07:51:45'),
(364, 126, 'TRF-20260503-8542', 'adult', 'JAIMEY TOMBAL', 1, '2026-05-03 07:51:45'),
(365, 126, 'TRF-20260503-8542', 'child', 'LEILA VAROL', 0, '2026-05-03 07:51:45'),
(366, 127, 'TRF-20260503-5721', 'adult', 'KIM HOEKSTRA', 0, '2026-05-03 07:51:45'),
(367, 127, 'TRF-20260503-5721', 'adult', 'JAIMEY TOMBAL', 1, '2026-05-03 07:51:45'),
(368, 127, 'TRF-20260503-5721', 'child', 'LEILA VAROL', 0, '2026-05-03 07:51:45'),
(375, 114, 'TRF-20260502-3230', 'adult', 'Jacquelina Bollacke', 0, '2026-05-03 08:29:12'),
(376, 114, 'TRF-20260502-3230', 'adult', 'JAAFER EL CHALL', 1, '2026-05-03 08:29:12'),
(377, 114, 'TRF-20260502-3230', 'adult', 'JUNA BOLLACKE', 2, '2026-05-03 08:29:12'),
(387, 69, 'TRF-20260430-9916', 'adult', 'BESMIRA ZHUTA', 0, '2026-05-03 08:52:43'),
(388, 69, 'TRF-20260430-9916', 'adult', 'ALBION MUSA', 1, '2026-05-03 08:52:43'),
(389, 69, 'TRF-20260430-9916', 'adult', 'LIAN MUSA', 2, '2026-05-03 08:52:43'),
(390, 69, 'TRF-20260430-9916', 'adult', 'MALIK MUSA', 3, '2026-05-03 08:52:43'),
(391, 128, 'TRF-20260503-1818', 'adult', 'STEVEN HANLEY', 0, '2026-05-03 09:51:59'),
(392, 128, 'TRF-20260503-1818', 'adult', 'STEPHANIE BROOKSBANK', 1, '2026-05-03 09:51:59'),
(393, 128, 'TRF-20260503-1818', 'adult', 'NOAH HANLEY', 2, '2026-05-03 09:51:59'),
(394, 128, 'TRF-20260503-1818', 'adult', 'MIA HANLEY', 3, '2026-05-03 09:51:59'),
(407, 93, 'TRF-20260502-1517', 'adult', 'KYNAAT SHAH', 0, '2026-05-03 10:00:16'),
(408, 93, 'TRF-20260502-1517', 'adult', 'MUSHRAF BUKHARI', 1, '2026-05-03 10:00:16'),
(409, 93, 'TRF-20260502-1517', 'adult', 'ALEEZA BUKHARI', 2, '2026-05-03 10:00:16'),
(410, 93, 'TRF-20260502-1517', 'adult', 'ADAM BUKHARI', 3, '2026-05-03 10:00:16'),
(411, 130, 'TRF-20260503-5684', 'adult', 'Dylan De Noijer', 0, '2026-05-03 10:37:30'),
(412, 130, 'TRF-20260503-5684', 'adult', 'PATRICIA MANNI', 1, '2026-05-03 10:37:30'),
(413, 130, 'TRF-20260503-5684', 'adult', 'GUIDO DE NOOJIER', 2, '2026-05-03 10:37:30'),
(414, 131, 'TRF-20260503-7229', 'adult', 'NATALIJA MARINA', 0, '2026-05-03 10:53:59'),
(415, 131, 'TRF-20260503-7229', 'adult', 'RICHARDS MARINA', 1, '2026-05-03 10:53:59'),
(416, 131, 'TRF-20260503-7229', 'adult', 'ROBERT MARINA', 2, '2026-05-03 10:53:59'),
(417, 132, 'TRF-20260503-4869', 'adult', 'NATALIJA MARINA', 0, '2026-05-03 10:53:59'),
(418, 132, 'TRF-20260503-4869', 'adult', 'RICHARDS MARINA', 1, '2026-05-03 10:53:59'),
(419, 132, 'TRF-20260503-4869', 'adult', 'ROBERT MARINA', 2, '2026-05-03 10:53:59'),
(420, 133, 'TRF-20260503-2682', 'adult', 'MICHELLE GOROWOI', 0, '2026-05-03 11:05:50'),
(421, 133, 'TRF-20260503-2682', 'adult', 'TOBIAS GÜNTER NIKLAS', 1, '2026-05-03 11:05:50'),
(422, 133, 'TRF-20260503-2682', 'adult', 'IRENE GOROWOI', 2, '2026-05-03 11:05:50'),
(426, 134, 'TRF-20260503-6610', 'adult', 'MICHELLE GOROWOI', 0, '2026-05-03 11:06:30'),
(427, 134, 'TRF-20260503-6610', 'adult', 'TOBIAS GÜNTER NIKLAS', 1, '2026-05-03 11:06:30'),
(428, 134, 'TRF-20260503-6610', 'adult', 'IRENE GOROWOI', 2, '2026-05-03 11:06:30'),
(433, 136, 'TRF-20260503-1385', 'adult', 'STEFFI SCHAPER', 0, '2026-05-03 12:09:05'),
(434, 136, 'TRF-20260503-1385', 'adult', 'UWE SCHAPER', 1, '2026-05-03 12:09:05'),
(435, 137, 'TRF-20260503-0722', 'adult', 'INES KAUFMANN', 0, '2026-05-03 12:10:29'),
(441, 140, 'TRF-20260503-6910', 'adult', 'NELLI DAKOV', 0, '2026-05-03 12:24:49'),
(442, 140, 'TRF-20260503-6910', 'adult', 'KONSTANTIN DAKOV', 1, '2026-05-03 12:24:49'),
(443, 140, 'TRF-20260503-6910', 'adult', 'VALENTINA SAIBEL', 2, '2026-05-03 12:24:49'),
(444, 140, 'TRF-20260503-6910', 'adult', 'ILJANA DAKOV', 3, '2026-05-03 12:24:49'),
(445, 141, 'TRF-20260503-2877', 'adult', 'NELLI DAKOV', 0, '2026-05-03 12:24:49'),
(446, 141, 'TRF-20260503-2877', 'adult', 'KONSTANTIN DAKOV', 1, '2026-05-03 12:24:49'),
(447, 141, 'TRF-20260503-2877', 'adult', 'VALENTINA SAIBEL', 2, '2026-05-03 12:24:49'),
(448, 141, 'TRF-20260503-2877', 'adult', 'ILJANA DAKOV', 3, '2026-05-03 12:24:49'),
(462, 106, 'TRF-20260502-0050', 'adult', 'ANNA JAKUSZEW', 0, '2026-05-03 16:23:01'),
(463, 106, 'TRF-20260502-0050', 'adult', 'ALEX JAKUSZEW', 1, '2026-05-03 16:23:01'),
(465, 145, 'TRF-20260503-5980', 'adult', 'Hennes weubker', 0, '2026-05-03 18:41:52'),
(466, 145, 'TRF-20260503-5980', 'adult', 'Rosa marlentrappe', 1, '2026-05-03 18:41:52'),
(467, 115, 'TRF-20260502-9921', 'adult', 'STEFAN BELL', 0, '2026-05-03 19:30:02'),
(468, 115, 'TRF-20260502-9921', 'adult', 'GEMMA MARSHALL', 1, '2026-05-03 19:30:02'),
(469, 115, 'TRF-20260502-9921', 'adult', 'LAILA MARSHALL', 2, '2026-05-03 19:30:02'),
(470, 115, 'TRF-20260502-9921', 'adult', 'OLLIE BELL', 3, '2026-05-03 19:30:02'),
(477, 146, 'TRF-20260503-0965', 'adult', 'CALLUM KALSI', 0, '2026-05-03 19:43:41'),
(478, 146, 'TRF-20260503-0965', 'adult', 'PARM KALSI', 1, '2026-05-03 19:43:41'),
(479, 146, 'TRF-20260503-0965', 'adult', 'CAMREN KALSI', 2, '2026-05-03 19:43:41'),
(480, 146, 'TRF-20260503-0965', 'adult', 'JAS KALSI', 3, '2026-05-03 19:43:41'),
(481, 147, 'TRF-20260503-3438', 'adult', 'JANA VON JAN', 0, '2026-05-03 19:49:33'),
(482, 147, 'TRF-20260503-3438', 'adult', 'JANA LINDIG', 1, '2026-05-03 19:49:33'),
(483, 148, 'TRF-20260503-5195', 'adult', 'JANA VON JAN', 0, '2026-05-03 19:49:33'),
(484, 148, 'TRF-20260503-5195', 'adult', 'JANA LINDIG', 1, '2026-05-03 19:49:33'),
(485, 149, 'TRF-20260503-0266', 'adult', 'INEZ WNEK', 0, '2026-05-03 19:58:12'),
(486, 149, 'TRF-20260503-0266', 'adult', 'ALEYNA YILMAZER', 1, '2026-05-03 19:58:12'),
(487, 150, 'TRF-20260504-9031', 'adult', 'LUKMAN ALIZADA', 0, '2026-05-03 21:07:24'),
(488, 150, 'TRF-20260504-9031', 'adult', 'DELBAR JANAGDOST', 1, '2026-05-03 21:07:24'),
(491, 152, 'TRF-20260504-5688', 'adult', 'JESSICA EILERS', 0, '2026-05-04 10:53:26'),
(492, 152, 'TRF-20260504-5688', 'adult', 'PATRIK EILERS', 1, '2026-05-04 10:53:26'),
(493, 152, 'TRF-20260504-5688', 'child', 'OONA EILERS', 0, '2026-05-04 10:53:26'),
(494, 153, 'TRF-20260504-9110', 'adult', 'JESSICA EILERS', 0, '2026-05-04 10:53:26'),
(495, 153, 'TRF-20260504-9110', 'adult', 'PATRIK EILERS', 1, '2026-05-04 10:53:26'),
(496, 153, 'TRF-20260504-9110', 'child', 'OONA EILERS', 0, '2026-05-04 10:53:26'),
(528, 157, 'TRF-20260504-6811', 'adult', 'MARCEL HÜNİNG', 0, '2026-05-04 11:21:32'),
(529, 157, 'TRF-20260504-6811', 'adult', 'EILEEN HÜNİNG', 1, '2026-05-04 11:21:32'),
(530, 158, 'TRF-20260504-0839', 'adult', 'MARCEL HÜNİNG', 0, '2026-05-04 11:21:32'),
(531, 158, 'TRF-20260504-0839', 'adult', 'EILEEN HÜNİNG', 1, '2026-05-04 11:21:32'),
(532, 155, 'TRF-20260504-3668', 'adult', 'GUIDO PESCHEL', 0, '2026-05-04 11:21:49'),
(533, 155, 'TRF-20260504-3668', 'adult', 'ROSI PESCHEL', 1, '2026-05-04 11:21:49'),
(536, 159, 'TRF-20260504-9021', 'adult', 'ALJONA BENZ', 0, '2026-05-04 12:23:14'),
(537, 159, 'TRF-20260504-9021', 'adult', 'ANDREAS BENZ', 1, '2026-05-04 12:23:14'),
(538, 159, 'TRF-20260504-9021', 'adult', 'ANNA NICKEL', 2, '2026-05-04 12:23:14'),
(539, 159, 'TRF-20260504-9021', 'adult', 'ALEXANDER NICKEL', 3, '2026-05-04 12:23:14'),
(544, 160, 'TRF-20260504-1090', 'adult', 'ALJONA BENZ', 0, '2026-05-04 12:24:04'),
(545, 160, 'TRF-20260504-1090', 'adult', 'ANDREAS BENZ', 1, '2026-05-04 12:24:04'),
(546, 160, 'TRF-20260504-1090', 'adult', 'ANNA NICKEL', 2, '2026-05-04 12:24:04'),
(547, 160, 'TRF-20260504-1090', 'adult', 'ALEXANDER NICKEL', 3, '2026-05-04 12:24:04'),
(548, 161, 'TRF-20260505-5360', 'adult', 'ALPASLAN YALÇIN', 0, '2026-05-05 07:28:52'),
(549, 161, 'TRF-20260505-5360', 'adult', 'HILAL SAVRAN', 1, '2026-05-05 07:28:52'),
(550, 162, 'TRF-20260505-3113', 'adult', 'YASSİNE BOUZINE', 0, '2026-05-05 07:33:52'),
(551, 162, 'TRF-20260505-3113', 'adult', 'AOUATIF AQANTOS', 1, '2026-05-05 07:33:52'),
(552, 163, 'TRF-20260505-8984', 'adult', 'ELISA PAGNOZZO', 0, '2026-05-05 07:40:53'),
(555, 164, 'TRF-20260505-0043', 'adult', 'JENS KUHLMANN', 0, '2026-05-05 09:34:34'),
(556, 164, 'TRF-20260505-0043', 'adult', 'CHAHWIWAN SEEHAKHAN', 1, '2026-05-05 09:34:34'),
(559, 166, 'TRF-20260505-8394', 'adult', 'ERHAN KARAKOÇ', 0, '2026-05-05 09:49:25'),
(560, 166, 'TRF-20260505-8394', 'adult', 'ERTUĞRUL ŞAHİN', 1, '2026-05-05 09:49:25'),
(561, 167, 'TRF-20260505-6152', 'adult', 'ADEM UĞURLU', 0, '2026-05-05 09:52:23'),
(562, 167, 'TRF-20260505-6152', 'adult', 'FATMA UĞURLU', 1, '2026-05-05 09:52:23'),
(563, 167, 'TRF-20260505-6152', 'adult', 'MİNA UĞURLU', 2, '2026-05-05 09:52:23'),
(564, 167, 'TRF-20260505-6152', 'adult', 'DEFNE UĞURLU', 3, '2026-05-05 09:52:23'),
(565, 168, 'TRF-20260505-1732', 'adult', 'ADEM UĞURLU', 0, '2026-05-05 09:52:23'),
(566, 168, 'TRF-20260505-1732', 'adult', 'FATMA UĞURLU', 1, '2026-05-05 09:52:23'),
(567, 168, 'TRF-20260505-1732', 'adult', 'MİNA UĞURLU', 2, '2026-05-05 09:52:23'),
(568, 168, 'TRF-20260505-1732', 'adult', 'DEFNE UĞURLU', 3, '2026-05-05 09:52:23'),
(569, 169, 'TRF-20260505-4686', 'adult', 'MICHAEL ROSENBOOM', 0, '2026-05-05 10:10:02'),
(570, 169, 'TRF-20260505-4686', 'adult', 'JOWITA WRONSKA GROENEWOLD', 1, '2026-05-05 10:10:02'),
(571, 170, 'TRF-20260505-8794', 'adult', 'MICHAEL ROSENBOOM', 0, '2026-05-05 10:10:02'),
(572, 170, 'TRF-20260505-8794', 'adult', 'JOWITA WRONSKA GROENEWOLD', 1, '2026-05-05 10:10:02'),
(591, 176, 'TRF-20260505-1286', 'adult', 'MIKE WETERKAMP', 0, '2026-05-05 11:34:02'),
(592, 176, 'TRF-20260505-1286', 'adult', 'THIJS HOFMAN', 1, '2026-05-05 11:34:02'),
(593, 175, 'TRF-20260505-8274', 'adult', 'MIKE WETERKAMP', 0, '2026-05-05 11:37:00'),
(594, 175, 'TRF-20260505-8274', 'adult', 'THIJS HOFMAN', 1, '2026-05-05 11:37:00'),
(595, 177, 'TRF-20260505-8605', 'adult', 'MICHAEL BÖHRİNGER', 0, '2026-05-05 11:41:09'),
(596, 177, 'TRF-20260505-8605', 'adult', 'Nicole Kissing', 1, '2026-05-05 11:41:09'),
(597, 177, 'TRF-20260505-8605', 'adult', 'Peter Mühlstädt', 2, '2026-05-05 11:41:09'),
(598, 177, 'TRF-20260505-8605', 'adult', 'Anke Mühlstädt', 3, '2026-05-05 11:41:09'),
(617, 182, 'TRF-20260505-0559', 'adult', 'UFUK CÖMERT', 0, '2026-05-05 12:06:19'),
(618, 182, 'TRF-20260505-0559', 'adult', 'BAHAR CÖMERT', 1, '2026-05-05 12:06:19'),
(619, 182, 'TRF-20260505-0559', 'child', 'NEHİR CÖMERT', 0, '2026-05-05 12:06:19'),
(620, 182, 'TRF-20260505-0559', 'child', 'FİLİZ CÖMERT', 1, '2026-05-05 12:06:19'),
(627, 185, 'TRF-20260505-6905', 'adult', 'VLADIMIRS SOBOLEVS', 0, '2026-05-05 13:22:12'),
(628, 185, 'TRF-20260505-6905', 'adult', 'ANASTASIJA SOBOLEVA', 1, '2026-05-05 13:22:12'),
(629, 185, 'TRF-20260505-6905', 'adult', 'POLINA SOBOLEVA', 2, '2026-05-05 13:22:12'),
(630, 185, 'TRF-20260505-6905', 'adult', 'NIKITA SOVOLEVS', 3, '2026-05-05 13:22:12'),
(631, 185, 'TRF-20260505-6905', 'adult', 'SOFJA SILOVA', 4, '2026-05-05 13:22:12'),
(632, 185, 'TRF-20260505-6905', 'adult', 'DANIEL LEE', 5, '2026-05-05 13:22:12'),
(633, 185, 'TRF-20260505-6905', 'adult', 'ANTONINA SILOVA', 6, '2026-05-05 13:22:12'),
(634, 185, 'TRF-20260505-6905', 'adult', 'VLADIMIRS SILOVS', 7, '2026-05-05 13:22:12'),
(635, 186, 'TRF-20260505-0648', 'adult', 'VLADIMIRS SOBOLEVS', 0, '2026-05-05 13:22:12'),
(636, 186, 'TRF-20260505-0648', 'adult', 'ANASTASIJA SOBOLEVA', 1, '2026-05-05 13:22:12'),
(637, 186, 'TRF-20260505-0648', 'adult', 'POLINA SOBOLEVA', 2, '2026-05-05 13:22:12'),
(638, 186, 'TRF-20260505-0648', 'adult', 'NIKITA SOVOLEVS', 3, '2026-05-05 13:22:12'),
(639, 186, 'TRF-20260505-0648', 'adult', 'SOFJA SILOVA', 4, '2026-05-05 13:22:12'),
(640, 186, 'TRF-20260505-0648', 'adult', 'DANIEL LEE', 5, '2026-05-05 13:22:12'),
(641, 186, 'TRF-20260505-0648', 'adult', 'ANTONINA SILOVA', 6, '2026-05-05 13:22:12'),
(642, 186, 'TRF-20260505-0648', 'adult', 'VLADIMIRS SILOVS', 7, '2026-05-05 13:22:12'),
(655, 189, 'TRF-20260505-8294', 'adult', 'ERIK TRIEBER', 0, '2026-05-05 13:44:13'),
(656, 189, 'TRF-20260505-8294', 'adult', 'MAXIMA ALEKSA KUEHNEL', 1, '2026-05-05 13:44:13'),
(657, 189, 'TRF-20260505-8294', 'child', 'LAILA MARIA KUEHNEL', 0, '2026-05-05 13:44:13'),
(658, 189, 'TRF-20260505-8294', 'child', 'LIANA ELLA KUEHNEL', 1, '2026-05-05 13:44:13'),
(659, 190, 'TRF-20260505-7730', 'adult', 'ERIK TRIEBER', 0, '2026-05-05 13:44:13'),
(660, 190, 'TRF-20260505-7730', 'adult', 'MAXIMA ALEKSA KUEHNEL', 1, '2026-05-05 13:44:13'),
(661, 190, 'TRF-20260505-7730', 'child', 'LAILA MARIA KUEHNEL', 0, '2026-05-05 13:44:13'),
(662, 190, 'TRF-20260505-7730', 'child', 'LIANA ELLA KUEHNEL', 1, '2026-05-05 13:44:13'),
(677, 193, 'TRF-20260505-7822', 'adult', 'MOHAMMAD İSMAİL', 0, '2026-05-05 13:58:35'),
(678, 193, 'TRF-20260505-7822', 'adult', 'NAYOB FAROOQ', 1, '2026-05-05 13:58:35'),
(679, 193, 'TRF-20260505-7822', 'child', 'HUZAYFA İSMAİL', 0, '2026-05-05 13:58:35'),
(680, 194, 'TRF-20260505-4414', 'adult', 'MOHAMMAD İSMAİL', 0, '2026-05-05 13:58:35'),
(681, 194, 'TRF-20260505-4414', 'adult', 'NAYOB FAROOQ', 1, '2026-05-05 13:58:35'),
(682, 194, 'TRF-20260505-4414', 'child', 'HUZAYFA İSMAİL', 0, '2026-05-05 13:58:35'),
(691, 197, 'TRF-20260505-5395', 'adult', 'FURKAN COŞKUN', 0, '2026-05-05 14:38:31'),
(692, 197, 'TRF-20260505-5395', 'adult', 'ŞEHRİNUR COŞKUN', 1, '2026-05-05 14:38:31'),
(693, 197, 'TRF-20260505-5395', 'child', 'CEYLİN COŞKUN', 0, '2026-05-05 14:38:31'),
(697, 199, 'TRF-20260505-5715', 'adult', 'AYTEN BAHTİRİ', 0, '2026-05-05 15:02:40'),
(698, 199, 'TRF-20260505-5715', 'adult', 'BEKİR BAHTİRİ', 1, '2026-05-05 15:02:40'),
(699, 199, 'TRF-20260505-5715', 'adult', 'AHMET BAHTİRİ', 2, '2026-05-05 15:02:40'),
(700, 199, 'TRF-20260505-5715', 'adult', 'DHEYWAIRE BAHTİRİ', 3, '2026-05-05 15:02:40'),
(701, 199, 'TRF-20260505-5715', 'adult', 'INAYA BAHTİRİ', 4, '2026-05-05 15:02:40'),
(702, 199, 'TRF-20260505-5715', 'child', 'ALİYA BAHTİRİ', 0, '2026-05-05 15:02:40'),
(703, 200, 'TRF-20260505-3725', 'adult', 'AYTEN BAHTİRİ', 0, '2026-05-05 15:02:40'),
(704, 200, 'TRF-20260505-3725', 'adult', 'BEKİR BAHTİRİ', 1, '2026-05-05 15:02:40'),
(705, 200, 'TRF-20260505-3725', 'adult', 'AHMET BAHTİRİ', 2, '2026-05-05 15:02:40'),
(706, 200, 'TRF-20260505-3725', 'adult', 'DHEYWAIRE BAHTİRİ', 3, '2026-05-05 15:02:40'),
(707, 200, 'TRF-20260505-3725', 'adult', 'INAYA BAHTİRİ', 4, '2026-05-05 15:02:40'),
(708, 200, 'TRF-20260505-3725', 'child', 'ALİYA BAHTİRİ', 0, '2026-05-05 15:02:40'),
(713, 202, 'TRF-20260505-6127', 'adult', 'EDUARD WGNER', 0, '2026-05-05 15:19:35'),
(714, 202, 'TRF-20260505-6127', 'adult', 'AGNES SCHUMACHER', 1, '2026-05-05 15:19:35'),
(715, 202, 'TRF-20260505-6127', 'adult', 'ALEXANDER KRAEMER', 2, '2026-05-05 15:19:35'),
(716, 202, 'TRF-20260505-6127', 'adult', 'VIKTORIA KRAEMER', 3, '2026-05-05 15:19:35'),
(717, 203, 'TRF-20260505-9770', 'adult', 'DIANA LACKA', 0, '2026-05-05 15:26:11'),
(718, 203, 'TRF-20260505-9770', 'adult', 'DMITRIS LACKIS', 1, '2026-05-05 15:26:11'),
(719, 203, 'TRF-20260505-9770', 'child', 'ANASTASIIA LACKA', 0, '2026-05-05 15:26:11'),
(720, 203, 'TRF-20260505-9770', 'child', 'ARTJOMS LACKIS', 1, '2026-05-05 15:26:11'),
(731, 207, 'TRF-20260505-9123', 'adult', 'ALEXANDER HORN', 0, '2026-05-05 15:37:44'),
(732, 207, 'TRF-20260505-9123', 'adult', 'GALINA HORN', 1, '2026-05-05 15:37:44'),
(733, 208, 'TRF-20260505-7248', 'adult', 'JOSEF HAIDER', 0, '2026-05-05 15:50:42'),
(734, 208, 'TRF-20260505-7248', 'adult', 'ALEXANDRA KLAB', 1, '2026-05-05 15:50:42'),
(735, 208, 'TRF-20260505-7248', 'adult', 'ANDREAS BLEYER', 2, '2026-05-05 15:50:42'),
(736, 208, 'TRF-20260505-7248', 'adult', 'ANDREA BLEYER', 3, '2026-05-05 15:50:42'),
(737, 209, 'TRF-20260505-3965', 'adult', 'JOSEF HAIDER', 0, '2026-05-05 15:50:42'),
(738, 209, 'TRF-20260505-3965', 'adult', 'ALEXANDRA KLAB', 1, '2026-05-05 15:50:42'),
(739, 209, 'TRF-20260505-3965', 'adult', 'ANDREAS BLEYER', 2, '2026-05-05 15:50:42'),
(740, 209, 'TRF-20260505-3965', 'adult', 'ANDREA BLEYER', 3, '2026-05-05 15:50:42'),
(741, 210, 'TRF-20260505-4540', 'adult', 'HAKAN BATTIG', 0, '2026-05-05 15:58:57'),
(742, 210, 'TRF-20260505-4540', 'adult', 'LEYLA BATTIG', 1, '2026-05-05 15:58:57'),
(743, 210, 'TRF-20260505-4540', 'child', 'RUTH BATTIG', 0, '2026-05-05 15:58:57'),
(744, 211, 'TRF-20260505-1170', 'adult', 'HAKAN BATTIG', 0, '2026-05-05 15:58:57'),
(745, 211, 'TRF-20260505-1170', 'adult', 'LEYLA BATTIG', 1, '2026-05-05 15:58:57'),
(746, 211, 'TRF-20260505-1170', 'child', 'RUTH BATTIG', 0, '2026-05-05 15:58:57'),
(772, 214, 'TRF-20260505-3106', 'adult', 'JULIA PETERSEN', 0, '2026-05-05 16:08:51'),
(773, 214, 'TRF-20260505-3106', 'adult', 'JENS PETERSEN', 1, '2026-05-05 16:08:51'),
(774, 214, 'TRF-20260505-3106', 'adult', 'LUKA PETERSEN', 2, '2026-05-05 16:08:51'),
(775, 214, 'TRF-20260505-3106', 'adult', 'AMELIE PETERSEN', 3, '2026-05-05 16:08:51'),
(776, 215, 'TRF-20260505-7436', 'adult', 'JULIA PETERSEN', 0, '2026-05-05 16:08:51'),
(777, 215, 'TRF-20260505-7436', 'adult', 'JENS PETERSEN', 1, '2026-05-05 16:08:51'),
(778, 215, 'TRF-20260505-7436', 'adult', 'LUKA PETERSEN', 2, '2026-05-05 16:08:51'),
(779, 215, 'TRF-20260505-7436', 'adult', 'AMELIE PETERSEN', 3, '2026-05-05 16:08:51'),
(796, 219, 'TRF-20260505-8328', 'adult', 'ANGELA DI MAGGIO', 0, '2026-05-05 16:28:32'),
(797, 219, 'TRF-20260505-8328', 'child', 'FABIO PILLITTU', 0, '2026-05-05 16:28:32'),
(798, 219, 'TRF-20260505-8328', 'child', 'SAMUELE PILLITTU', 1, '2026-05-05 16:28:32'),
(818, 217, 'TRF-20260505-6955', 'adult', 'DENNIS FICEK', 0, '2026-05-05 16:35:36'),
(819, 217, 'TRF-20260505-6955', 'adult', 'SANDRA DAYANA FICEK', 1, '2026-05-05 16:35:36'),
(820, 217, 'TRF-20260505-6955', 'child', 'CLEO ALEXANDRA FICEK', 0, '2026-05-05 16:35:36'),
(821, 217, 'TRF-20260505-6955', 'child', 'LANA SOFIA FICEK', 1, '2026-05-05 16:35:36'),
(822, 218, 'TRF-20260505-6407', 'adult', 'MAREK DEBOWSKI', 0, '2026-05-05 16:35:56'),
(823, 218, 'TRF-20260505-6407', 'adult', 'AGNES ALEXANDRA DEBOWSKI', 1, '2026-05-05 16:35:56'),
(824, 218, 'TRF-20260505-6407', 'adult', 'SOFIA MARGARETE FICEK', 2, '2026-05-05 16:35:56'),
(825, 218, 'TRF-20260505-6407', 'child', 'DOMINIK MAREK DEBOWSKI', 0, '2026-05-05 16:35:56'),
(826, 216, 'TRF-20260505-8335', 'adult', 'DENNIS FICEK', 0, '2026-05-05 16:40:42'),
(827, 216, 'TRF-20260505-8335', 'adult', 'SANDRA DAYANA FICEK', 1, '2026-05-05 16:40:42'),
(828, 216, 'TRF-20260505-8335', 'adult', 'MAREK DEBOWSKI', 2, '2026-05-05 16:40:42'),
(829, 216, 'TRF-20260505-8335', 'adult', 'AGNES ALXEANDRA DEBOWSKI', 3, '2026-05-05 16:40:42'),
(830, 216, 'TRF-20260505-8335', 'adult', 'SOFIA MARGARETE FICEK', 4, '2026-05-05 16:40:42'),
(831, 216, 'TRF-20260505-8335', 'child', 'CLEO ALEXANDRA FICEK', 0, '2026-05-05 16:40:42'),
(832, 216, 'TRF-20260505-8335', 'child', 'LANA SOFIA FICEK', 1, '2026-05-05 16:40:42'),
(833, 216, 'TRF-20260505-8335', 'child', 'DOMINIK MAREK DEBOWSKI', 2, '2026-05-05 16:40:42'),
(834, 221, 'TRF-20260505-6949', 'adult', 'STEFAN PFAFF', 0, '2026-05-05 16:47:24'),
(835, 221, 'TRF-20260505-6949', 'adult', 'KATHARINA PFAFF', 1, '2026-05-05 16:47:24'),
(836, 221, 'TRF-20260505-6949', 'child', 'LIAM PFAFF', 0, '2026-05-05 16:47:24'),
(837, 221, 'TRF-20260505-6949', 'child', 'MILAN PFAFF', 1, '2026-05-05 16:47:24'),
(838, 222, 'TRF-20260505-4232', 'adult', 'STEFAN PFAFF', 0, '2026-05-05 16:47:24'),
(839, 222, 'TRF-20260505-4232', 'adult', 'KATHARINA PFAFF', 1, '2026-05-05 16:47:24'),
(840, 222, 'TRF-20260505-4232', 'child', 'LIAM PFAFF', 0, '2026-05-05 16:47:24'),
(841, 222, 'TRF-20260505-4232', 'child', 'MILAN PFAFF', 1, '2026-05-05 16:47:24'),
(854, 225, 'TRF-20260505-6036', 'adult', 'NEBAHAT YALÇINKAYA', 0, '2026-05-05 17:03:39'),
(855, 225, 'TRF-20260505-6036', 'adult', 'MEMNUNE TARIM', 1, '2026-05-05 17:03:39'),
(856, 225, 'TRF-20260505-6036', 'adult', 'AYDIN TARIM', 2, '2026-05-05 17:03:39'),
(857, 225, 'TRF-20260505-6036', 'adult', 'MUAMMER YALÇINKAYA', 3, '2026-05-05 17:03:39'),
(858, 225, 'TRF-20260505-6036', 'adult', 'EDA YALÇINKAYA', 4, '2026-05-05 17:03:39'),
(859, 226, 'TRF-20260505-0486', 'adult', 'NEBAHAT YALÇINKAYA', 0, '2026-05-05 17:03:39'),
(860, 226, 'TRF-20260505-0486', 'adult', 'MEMNUNE TARIM', 1, '2026-05-05 17:03:39'),
(861, 226, 'TRF-20260505-0486', 'adult', 'AYDIN TARIM', 2, '2026-05-05 17:03:39'),
(862, 226, 'TRF-20260505-0486', 'adult', 'MUAMMER YALÇINKAYA', 3, '2026-05-05 17:03:39'),
(863, 226, 'TRF-20260505-0486', 'adult', 'EDA YALÇINKAYA', 4, '2026-05-05 17:03:39'),
(864, 227, 'TRF-20260505-7848', 'adult', 'ANEL CURİC', 0, '2026-05-05 18:02:25'),
(865, 227, 'TRF-20260505-7848', 'adult', 'VASVİJA CURİC', 1, '2026-05-05 18:02:25'),
(866, 227, 'TRF-20260505-7848', 'child', 'SELINA CURIC', 0, '2026-05-05 18:02:25'),
(873, 228, 'TRF-20260505-5989', 'adult', 'ANEL CURİC', 0, '2026-05-05 18:40:56'),
(874, 228, 'TRF-20260505-5989', 'adult', 'VASVİJA CURİC', 1, '2026-05-05 18:40:56'),
(875, 228, 'TRF-20260505-5989', 'child', 'SELINA CURIC', 0, '2026-05-05 18:40:56'),
(882, 232, 'TRF-20260505-8094', 'adult', 'KATI', 0, '2026-05-05 20:23:49'),
(883, 233, 'TRF-20260505-8888', 'adult', 'KATI', 0, '2026-05-05 20:23:49'),
(884, 234, 'TRF-20260505-5803', 'adult', 'CHRISTIAN HAGEN', 0, '2026-05-05 20:27:55'),
(885, 234, 'TRF-20260505-5803', 'adult', 'CHRISTIN HAGEN', 1, '2026-05-05 20:27:55'),
(886, 234, 'TRF-20260505-5803', 'adult', 'FABIOFIETE FINK', 2, '2026-05-05 20:27:55'),
(887, 234, 'TRF-20260505-5803', 'adult', 'FINLEYFIONA FINK', 3, '2026-05-05 20:27:55'),
(888, 235, 'TRF-20260505-9909', 'adult', 'CHRISTIAN HAGEN', 0, '2026-05-05 20:27:55'),
(889, 235, 'TRF-20260505-9909', 'adult', 'CHRISTIN HAGEN', 1, '2026-05-05 20:27:55'),
(890, 235, 'TRF-20260505-9909', 'adult', 'FABIOFIETE FINK', 2, '2026-05-05 20:27:55'),
(891, 235, 'TRF-20260505-9909', 'adult', 'FINLEYFIONA FINK', 3, '2026-05-05 20:27:55'),
(895, 236, 'TRF-20260505-9436', 'adult', 'SARAH ONEILL', 0, '2026-05-05 20:33:45'),
(896, 236, 'TRF-20260505-9436', 'adult', 'KEITH ONEILL', 1, '2026-05-05 20:33:45'),
(897, 236, 'TRF-20260505-9436', 'adult', 'CALLUM ONEILL', 2, '2026-05-05 20:33:45'),
(898, 237, 'TRF-20260505-1398', 'adult', 'CHRISTOPH HENEL', 0, '2026-05-05 20:36:50'),
(899, 237, 'TRF-20260505-1398', 'adult', 'BRIGITTE HENEL', 1, '2026-05-05 20:36:50'),
(900, 237, 'TRF-20260505-1398', 'child', 'LEONIE HENEL', 0, '2026-05-05 20:36:50'),
(901, 237, 'TRF-20260505-1398', 'child', 'DAVID HENEL', 1, '2026-05-05 20:36:50'),
(902, 238, 'TRF-20260505-5302', 'adult', 'CHRISTOPH HENEL', 0, '2026-05-05 20:36:50'),
(903, 238, 'TRF-20260505-5302', 'adult', 'BRIGITTE HENEL', 1, '2026-05-05 20:36:50'),
(904, 238, 'TRF-20260505-5302', 'child', 'LEONIE HENEL', 0, '2026-05-05 20:36:50'),
(905, 238, 'TRF-20260505-5302', 'child', 'DAVID HENEL', 1, '2026-05-05 20:36:50'),
(906, 239, 'TRF-20260505-7106', 'adult', 'ILKAY KOPARAN', 0, '2026-05-05 20:40:21'),
(907, 239, 'TRF-20260505-7106', 'adult', 'ALEX KOCH', 1, '2026-05-05 20:40:21'),
(908, 239, 'TRF-20260505-7106', 'adult', 'NADINE WUNSCH', 2, '2026-05-05 20:40:21'),
(913, 242, 'TRF-20260505-5318', 'adult', 'ŞENAY TURACAK', 0, '2026-05-05 20:44:01'),
(914, 242, 'TRF-20260505-5318', 'adult', 'AMAR BEHARI', 1, '2026-05-05 20:44:01'),
(915, 242, 'TRF-20260505-5318', 'adult', 'RAJANI BEHARI', 2, '2026-05-05 20:44:01'),
(916, 242, 'TRF-20260505-5318', 'adult', 'AMIR BEHARI', 3, '2026-05-05 20:44:01'),
(917, 243, 'TRF-20260505-6983', 'adult', 'ŞENAY TURACAK', 0, '2026-05-05 20:44:01'),
(918, 243, 'TRF-20260505-6983', 'adult', 'AMAR BEHARI', 1, '2026-05-05 20:44:01'),
(919, 243, 'TRF-20260505-6983', 'adult', 'RAJANI BEHARI', 2, '2026-05-05 20:44:01'),
(920, 243, 'TRF-20260505-6983', 'adult', 'AMIR BEHARI', 3, '2026-05-05 20:44:01'),
(921, 244, 'TRF-20260505-9040', 'adult', 'GINO MOLL', 0, '2026-05-05 20:47:08'),
(922, 244, 'TRF-20260505-9040', 'child', 'JOSHUA MOLL', 0, '2026-05-05 20:47:08'),
(923, 245, 'TRF-20260505-8142', 'adult', 'RACHEL GILL', 0, '2026-05-05 20:59:33'),
(924, 245, 'TRF-20260505-8142', 'adult', 'SAMUEL ROBSON', 1, '2026-05-05 20:59:33'),
(925, 245, 'TRF-20260505-8142', 'child', 'HALLIE ROBSON', 0, '2026-05-05 20:59:33'),
(926, 246, 'TRF-20260505-5770', 'adult', 'RACHEL GILL', 0, '2026-05-05 20:59:33'),
(927, 246, 'TRF-20260505-5770', 'adult', 'SAMUEL ROBSON', 1, '2026-05-05 20:59:33'),
(928, 246, 'TRF-20260505-5770', 'child', 'HALLIE ROBSON', 0, '2026-05-05 20:59:33'),
(929, 247, 'TRF-20260506-4485', 'adult', 'MARCUS HUMMEL', 0, '2026-05-05 21:03:04'),
(930, 247, 'TRF-20260506-4485', 'adult', 'DANA WYRZYKOWSKI', 1, '2026-05-05 21:03:04'),
(931, 247, 'TRF-20260506-4485', 'adult', 'SAN WYRZYKOWSKI', 2, '2026-05-05 21:03:04'),
(932, 247, 'TRF-20260506-4485', 'adult', 'MARTINA WYRZYKOWSKI', 3, '2026-05-05 21:03:04'),
(933, 247, 'TRF-20260506-4485', 'adult', 'MANDY MÜLLER', 4, '2026-05-05 21:03:04'),
(934, 247, 'TRF-20260506-4485', 'adult', 'SÖRG MÜLLER', 5, '2026-05-05 21:03:04'),
(935, 248, 'TRF-20260506-1478', 'adult', 'MARCUS HUMMEL', 0, '2026-05-05 21:03:04'),
(936, 248, 'TRF-20260506-1478', 'adult', 'DANA WYRZYKOWSKI', 1, '2026-05-05 21:03:04'),
(937, 248, 'TRF-20260506-1478', 'adult', 'SAN WYRZYKOWSKI', 2, '2026-05-05 21:03:04'),
(938, 248, 'TRF-20260506-1478', 'adult', 'MARTINA WYRZYKOWSKI', 3, '2026-05-05 21:03:04'),
(939, 248, 'TRF-20260506-1478', 'adult', 'MANDY MÜLLER', 4, '2026-05-05 21:03:04'),
(940, 248, 'TRF-20260506-1478', 'adult', 'SÖRG MÜLLER', 5, '2026-05-05 21:03:04'),
(941, 249, 'TRF-20260506-1931', 'adult', 'DANIELA RÖPCKE', 0, '2026-05-05 21:05:29'),
(942, 249, 'TRF-20260506-1931', 'adult', 'MARC JÖRG HOFFMANN', 1, '2026-05-05 21:05:29'),
(943, 250, 'TRF-20260506-1150', 'adult', 'DANIELA RÖPCKE', 0, '2026-05-05 21:05:29'),
(944, 250, 'TRF-20260506-1150', 'adult', 'MARC JÖRG HOFFMANN', 1, '2026-05-05 21:05:29'),
(945, 251, 'TRF-20260506-6085', 'adult', 'MALTE EHLERT', 0, '2026-05-05 21:08:56'),
(946, 251, 'TRF-20260506-6085', 'adult', 'JASMIN KROHN', 1, '2026-05-05 21:08:56'),
(947, 252, 'TRF-20260506-4230', 'adult', 'MALTE EHLERT', 0, '2026-05-05 21:08:56'),
(948, 252, 'TRF-20260506-4230', 'adult', 'JASMIN KROHN', 1, '2026-05-05 21:08:56'),
(949, 253, 'TRF-20260506-3257', 'adult', 'MAIK PRETZ', 0, '2026-05-05 21:12:08'),
(950, 253, 'TRF-20260506-3257', 'adult', 'NICOLE PRETZ', 1, '2026-05-05 21:12:08'),
(951, 253, 'TRF-20260506-3257', 'adult', 'NIKE PRETZ', 2, '2026-05-05 21:12:08'),
(952, 253, 'TRF-20260506-3257', 'adult', 'JÖRG PRETZ', 3, '2026-05-05 21:12:08'),
(953, 253, 'TRF-20260506-3257', 'adult', 'SILKE PRETZ', 4, '2026-05-05 21:12:08'),
(959, 254, 'TRF-20260506-8918', 'adult', 'MAIK PRETZ', 0, '2026-05-05 21:12:33'),
(960, 254, 'TRF-20260506-8918', 'adult', 'NICOLE PRETZ', 1, '2026-05-05 21:12:33'),
(961, 254, 'TRF-20260506-8918', 'adult', 'NIKE PRETZ', 2, '2026-05-05 21:12:33'),
(962, 254, 'TRF-20260506-8918', 'adult', 'JÖRG PRETZ', 3, '2026-05-05 21:12:33'),
(963, 254, 'TRF-20260506-8918', 'adult', 'SILKE PRETZ', 4, '2026-05-05 21:12:33'),
(967, 180, 'TRF-20260505-6069', 'adult', 'PAUL COMBES', 0, '2026-05-06 08:53:14'),
(968, 180, 'TRF-20260505-6069', 'adult', 'BONIE SEWELL', 1, '2026-05-06 08:53:14'),
(969, 180, 'TRF-20260505-6069', 'adult', 'KODIE REDFERN', 2, '2026-05-06 08:53:14'),
(970, 102, 'TRF-20260502-5031', 'adult', 'SHANAY GORDON', 0, '2026-05-06 08:54:01'),
(971, 102, 'TRF-20260502-5031', 'adult', 'AYLAH AYANLEYE GORDON', 1, '2026-05-06 08:54:01'),
(972, 255, 'TRF-20260506-5786', 'adult', 'IGOR OSIPCHUK', 0, '2026-05-06 14:05:57'),
(973, 255, 'TRF-20260506-5786', 'adult', 'TETIANA OSIPCHUK', 1, '2026-05-06 14:05:57'),
(974, 255, 'TRF-20260506-5786', 'child', 'KATERYNA OSIPCHUK', 0, '2026-05-06 14:05:57'),
(975, 256, 'TRF-20260506-9996', 'adult', 'IGOR OSIPCHUK', 0, '2026-05-06 14:05:57'),
(976, 256, 'TRF-20260506-9996', 'adult', 'TETIANA OSIPCHUK', 1, '2026-05-06 14:05:57'),
(977, 256, 'TRF-20260506-9996', 'child', 'KATERYNA OSIPCHUK', 0, '2026-05-06 14:05:57'),
(978, 257, 'TRF-20260506-2204', 'adult', 'CAN KLEISMAN', 0, '2026-05-06 16:49:52'),
(979, 257, 'TRF-20260506-2204', 'adult', 'CEM KLEISMAN', 1, '2026-05-06 16:49:52'),
(980, 258, 'TRF-20260506-0328', 'adult', 'JEAN SPENCER', 0, '2026-05-06 16:53:16'),
(985, 261, 'TRF-20260507-3077', 'adult', 'JAY HEBBORN', 0, '2026-05-07 06:02:38'),
(986, 261, 'TRF-20260507-3077', 'adult', 'REECE HEBBORN', 1, '2026-05-07 06:02:38'),
(987, 262, 'TRF-20260507-9528', 'adult', 'JOSE ORLANDO LOPEZ', 0, '2026-05-07 07:21:31'),
(988, 262, 'TRF-20260507-9528', 'adult', 'LAURA MOROSIN', 1, '2026-05-07 07:21:31'),
(992, 109, 'TRF-20260502-1655', 'adult', 'HUSSEIN EL ABED', 0, '2026-05-07 07:53:50'),
(993, 109, 'TRF-20260502-1655', 'adult', 'ALI EL ABED', 1, '2026-05-07 07:53:50'),
(994, 109, 'TRF-20260502-1655', 'adult', 'ALI MENCHO', 2, '2026-05-07 07:53:50'),
(998, 78, 'TRF-20260502-3505', 'adult', 'MUHAMMAD BILAL', 0, '2026-05-07 14:47:29'),
(999, 78, 'TRF-20260502-3505', 'adult', 'SAIMA BILAL', 1, '2026-05-07 14:47:29'),
(1000, 264, 'TRF-20260507-8094', 'adult', 'ANDRIAN TONNY PREDESCU', 0, '2026-05-07 14:55:28'),
(1001, 264, 'TRF-20260507-8094', 'adult', 'IONELA MARIELA PREDUSCU', 1, '2026-05-07 14:55:28'),
(1002, 264, 'TRF-20260507-8094', 'child', 'ADRİAN MATTEO PREDUSCU', 0, '2026-05-07 14:55:28'),
(1009, 265, 'TRF-20260507-4324', 'adult', 'ANDRIAN TONNY PREDESCU', 0, '2026-05-07 14:56:42'),
(1010, 265, 'TRF-20260507-4324', 'adult', 'IONELA MARIELA PREDUSCU', 1, '2026-05-07 14:56:42'),
(1011, 265, 'TRF-20260507-4324', 'child', 'ADRİAN MATTEO PREDUSCU', 0, '2026-05-07 14:56:42'),
(1015, 267, 'TRF-20260507-5165', 'adult', 'LAURO LAURANNE', 0, '2026-05-07 15:10:18'),
(1016, 267, 'TRF-20260507-5165', 'adult', 'SEBASTIEN MARTINET', 1, '2026-05-07 15:10:18'),
(1017, 267, 'TRF-20260507-5165', 'adult', 'ILENIA MARTINET', 2, '2026-05-07 15:10:18'),
(1018, 267, 'TRF-20260507-5165', 'adult', 'LUENZO MARTINET', 3, '2026-05-07 15:10:18'),
(1019, 268, 'TRF-20260507-6729', 'adult', 'LAURO LAURANNE', 0, '2026-05-07 15:10:18'),
(1020, 268, 'TRF-20260507-6729', 'adult', 'SEBASTIEN MARTINET', 1, '2026-05-07 15:10:18'),
(1021, 268, 'TRF-20260507-6729', 'adult', 'ILENIA MARTINET', 2, '2026-05-07 15:10:18'),
(1022, 268, 'TRF-20260507-6729', 'adult', 'LUENZO MARTINET', 3, '2026-05-07 15:10:18'),
(1023, 151, 'TRF-20260504-8380', 'adult', 'ZAID NANAWADI', 0, '2026-05-07 15:42:29'),
(1024, 151, 'TRF-20260504-8380', 'adult', 'MUHAMMED SHAIKH', 1, '2026-05-07 15:42:29'),
(1025, 269, 'TRF-20260508-7185', 'adult', 'INEZ WNEK', 0, '2026-05-07 21:40:57'),
(1026, 269, 'TRF-20260508-7185', 'adult', 'ALEYNA YILMAZER', 1, '2026-05-07 21:40:57'),
(1048, 276, 'TRF-20260508-3364', 'adult', 'ANGIE STARMANS', 0, '2026-05-08 06:08:45'),
(1049, 276, 'TRF-20260508-3364', 'adult', 'BEN ZENDEN', 1, '2026-05-08 06:08:45'),
(1050, 276, 'TRF-20260508-3364', 'adult', 'SANDRA EYKENBOOM', 2, '2026-05-08 06:08:45'),
(1051, 276, 'TRF-20260508-3364', 'child', 'JAXX LAMERS', 0, '2026-05-08 06:08:45'),
(1052, 271, 'TRF-20260508-4114', 'adult', 'VLADISLAV MASISIKI', 0, '2026-05-08 06:10:17'),
(1053, 271, 'TRF-20260508-4114', 'adult', 'ADEMIN KATUNINA', 1, '2026-05-08 06:10:17'),
(1054, 271, 'TRF-20260508-4114', 'adult', 'JELENA SMIRNOVA', 2, '2026-05-08 06:10:17'),
(1055, 277, 'TRF-20260508-1758', 'adult', 'LORI HASELL', 0, '2026-05-08 06:15:10'),
(1056, 277, 'TRF-20260508-1758', 'adult', 'CRAIG HASELL', 1, '2026-05-08 06:15:10'),
(1057, 277, 'TRF-20260508-1758', 'child', 'JAKE HASELL', 0, '2026-05-08 06:15:10'),
(1058, 277, 'TRF-20260508-1758', 'child', 'LUCIA HASELL', 1, '2026-05-08 06:15:10'),
(1059, 277, 'TRF-20260508-1758', 'child', 'VIOLET HASELL', 2, '2026-05-08 06:15:10'),
(1080, 38, 'TRF-20260428-1764', 'adult', 'FİKRET GEMRİKLİ', 0, '2026-05-08 12:00:52'),
(1081, 38, 'TRF-20260428-1764', 'adult', 'HOUDA GEMRİKLİ', 1, '2026-05-08 12:00:52'),
(1082, 38, 'TRF-20260428-1764', 'adult', 'NAJAH MORADG', 2, '2026-05-08 12:00:52'),
(1083, 38, 'TRF-20260428-1764', 'adult', 'MARYAM MORADG', 3, '2026-05-08 12:00:52'),
(1084, 38, 'TRF-20260428-1764', 'child', 'TUANA GEMRIKLI', 0, '2026-05-08 12:00:52'),
(1085, 38, 'TRF-20260428-1764', 'child', 'MELINAY GEMRIKLI', 1, '2026-05-08 12:00:52'),
(1086, 113, 'TRF-20260502-7354', 'adult', 'Jacquelina Bollacke', 0, '2026-05-08 14:25:14'),
(1087, 113, 'TRF-20260502-7354', 'adult', 'JAAFER EL CHALL', 1, '2026-05-08 14:25:14'),
(1088, 113, 'TRF-20260502-7354', 'adult', 'JUNA BOLLACKE', 2, '2026-05-08 14:25:14'),
(1089, 212, 'TRF-20260505-7070', 'adult', 'SERHAN GÜNEŞ', 0, '2026-05-08 14:46:42'),
(1090, 212, 'TRF-20260505-7070', 'adult', 'SABİT GÜNEŞ', 1, '2026-05-08 14:46:42'),
(1091, 212, 'TRF-20260505-7070', 'adult', 'RÜYA GÜNEŞ', 2, '2026-05-08 14:46:42'),
(1092, 212, 'TRF-20260505-7070', 'adult', 'KUZEY GÜNEŞ', 3, '2026-05-08 14:46:42'),
(1093, 212, 'TRF-20260505-7070', 'adult', 'EZGİ GÜNEŞ', 4, '2026-05-08 14:46:42'),
(1094, 212, 'TRF-20260505-7070', 'adult', 'KARAN GÜNEŞ', 5, '2026-05-08 14:46:42'),
(1095, 212, 'TRF-20260505-7070', 'adult', 'SEBAHAT GÜNEŞ', 6, '2026-05-08 14:46:42'),
(1096, 212, 'TRF-20260505-7070', 'adult', 'SEDAT GÜNEŞ', 7, '2026-05-08 14:46:42'),
(1097, 204, 'TRF-20260505-6168', 'adult', 'NURIJE OSMANI', 0, '2026-05-08 18:38:01'),
(1098, 204, 'TRF-20260505-6168', 'adult', 'MIRANDA OSMANI', 1, '2026-05-08 18:38:01'),
(1099, 204, 'TRF-20260505-6168', 'adult', 'REMZIJE OSMANI', 2, '2026-05-08 18:38:01'),
(1100, 204, 'TRF-20260505-6168', 'adult', 'BEDRI HALJITI', 3, '2026-05-08 18:38:01'),
(1101, 205, 'TRF-20260505-8682', 'adult', 'NURIJE OSMANI', 0, '2026-05-08 18:38:36'),
(1102, 205, 'TRF-20260505-8682', 'adult', 'MIRANDA OSMANI', 1, '2026-05-08 18:38:36'),
(1103, 205, 'TRF-20260505-8682', 'adult', 'REMZIJE OSMANI', 2, '2026-05-08 18:38:36'),
(1104, 205, 'TRF-20260505-8682', 'adult', 'BEDRI HALJITI', 3, '2026-05-08 18:38:36'),
(1105, 279, 'TRF-20260508-1671', 'adult', 'EMIL GODLA', 0, '2026-05-08 19:03:53'),
(1106, 279, 'TRF-20260508-1671', 'adult', 'MARTIN FARBAR', 1, '2026-05-08 19:03:53'),
(1107, 280, 'TRF-20260508-4970', 'adult', 'ABDELWAHED ADAHRI', 0, '2026-05-08 19:11:10'),
(1108, 280, 'TRF-20260508-4970', 'adult', 'PARSA SOLTANI', 1, '2026-05-08 19:11:10'),
(1109, 281, 'TRF-20260508-2402', 'adult', 'SARKA ROHACOVA', 0, '2026-05-08 19:14:56'),
(1110, 281, 'TRF-20260508-2402', 'adult', 'JAN ROHAC', 1, '2026-05-08 19:14:56'),
(1111, 282, 'TRF-20260508-4551', 'adult', 'CHRISTIAN HENRIKSEN', 0, '2026-05-08 19:15:55'),
(1112, 282, 'TRF-20260508-4551', 'adult', 'NADJA SOENDERGAARD', 1, '2026-05-08 19:15:55'),
(1113, 283, 'TRF-20260508-7007', 'adult', 'ZAID NANAWADI', 0, '2026-05-08 19:44:10'),
(1114, 283, 'TRF-20260508-7007', 'adult', 'MUHAMMED SHAIKH', 1, '2026-05-08 19:44:10'),
(1115, 266, 'TRF-20260507-6100', 'adult', 'QUAIMA AIT EL HADJ', 0, '2026-05-09 07:11:27'),
(1116, 266, 'TRF-20260507-6100', 'adult', 'BILAL AAMI', 1, '2026-05-09 07:11:27'),
(1117, 266, 'TRF-20260507-6100', 'adult', 'NOUSSAIR AAMI', 2, '2026-05-09 07:11:27'),
(1118, 284, 'TRF-20260509-1420', 'adult', 'TATIANA LAZEROVA', 0, '2026-05-09 07:16:59'),
(1123, 213, 'TRF-20260505-0779', 'adult', 'SERHAN GÜNEŞ', 0, '2026-05-09 12:19:17'),
(1124, 213, 'TRF-20260505-0779', 'adult', 'SABİT GÜNEŞ', 1, '2026-05-09 12:19:17'),
(1125, 213, 'TRF-20260505-0779', 'adult', 'RÜYA GÜNEŞ', 2, '2026-05-09 12:19:17'),
(1126, 213, 'TRF-20260505-0779', 'adult', 'KUZEY GÜNEŞ', 3, '2026-05-09 12:19:17'),
(1127, 213, 'TRF-20260505-0779', 'adult', 'EZGİ GÜNEŞ', 4, '2026-05-09 12:19:17'),
(1128, 213, 'TRF-20260505-0779', 'adult', 'KARAN GÜNEŞ', 5, '2026-05-09 12:19:17'),
(1129, 213, 'TRF-20260505-0779', 'adult', 'SEBAHAT GÜNEŞ', 6, '2026-05-09 12:19:17'),
(1130, 213, 'TRF-20260505-0779', 'adult', 'SEDAT GÜNEŞ', 7, '2026-05-09 12:19:17'),
(1131, 213, 'TRF-20260505-0779', 'adult', 'SEDAT KARA', 8, '2026-05-09 12:19:17'),
(1140, 272, 'TRF-20260508-7711', 'adult', 'EBUBEKİR GÜREL', 0, '2026-05-09 17:59:06'),
(1141, 272, 'TRF-20260508-7711', 'adult', 'GAMZE GÜREL', 1, '2026-05-09 17:59:06'),
(1142, 272, 'TRF-20260508-7711', 'child', 'KIYAN GÜREL', 0, '2026-05-09 17:59:06'),
(1143, 272, 'TRF-20260508-7711', 'child', 'HELİN GÜREL', 1, '2026-05-09 17:59:06'),
(1148, 273, 'TRF-20260508-4810', 'adult', 'EBUBEKİR GÜREL', 0, '2026-05-09 18:00:04'),
(1149, 273, 'TRF-20260508-4810', 'adult', 'GAMZE GÜREL', 1, '2026-05-09 18:00:04'),
(1150, 273, 'TRF-20260508-4810', 'child', 'KIYAN GÜREL', 0, '2026-05-09 18:00:04'),
(1151, 273, 'TRF-20260508-4810', 'child', 'HELİN GÜREL', 1, '2026-05-09 18:00:04'),
(1152, 83, 'TRF-20260502-3270', 'adult', 'MAKSIM OSIPOVS', 0, '2026-05-09 18:42:40'),
(1153, 83, 'TRF-20260502-3270', 'adult', 'SEJITGULIJEVA MILANA', 1, '2026-05-09 18:42:40'),
(1154, 285, 'TRF-20260509-9341', 'adult', 'TUFAN YAZICI', 0, '2026-05-09 19:30:24'),
(1155, 285, 'TRF-20260509-9341', 'adult', 'MERVE YAZICI', 1, '2026-05-09 19:30:24'),
(1156, 285, 'TRF-20260509-9341', 'child', 'MUSAB YAZICI', 0, '2026-05-09 19:30:24'),
(1157, 285, 'TRF-20260509-9341', 'child', 'ISRA YAZICI', 1, '2026-05-09 19:30:24'),
(1158, 286, 'TRF-20260509-0813', 'adult', 'TUFAN YAZICI', 0, '2026-05-09 19:30:24'),
(1159, 286, 'TRF-20260509-0813', 'adult', 'MERVE YAZICI', 1, '2026-05-09 19:30:24'),
(1160, 286, 'TRF-20260509-0813', 'child', 'MUSAB YAZICI', 0, '2026-05-09 19:30:24'),
(1161, 286, 'TRF-20260509-0813', 'child', 'ISRA YAZICI', 1, '2026-05-09 19:30:24'),
(1162, 287, 'TRF-20260509-3837', 'adult', 'AXEL KERLL', 0, '2026-05-09 19:38:46'),
(1163, 287, 'TRF-20260509-3837', 'adult', 'GABRIELE KERLL', 1, '2026-05-09 19:38:46'),
(1164, 288, 'TRF-20260509-3493', 'adult', 'AXEL KERLL', 0, '2026-05-09 19:38:46'),
(1165, 288, 'TRF-20260509-3493', 'adult', 'GABRIELE KERLL', 1, '2026-05-09 19:38:46'),
(1166, 165, 'TRF-20260505-7359', 'adult', 'ERHAN KARAKOÇ', 0, '2026-05-10 11:28:33'),
(1167, 165, 'TRF-20260505-7359', 'adult', 'ERTUĞRUL ŞAHİN', 1, '2026-05-10 11:28:33'),
(1168, 290, 'TRF-20260510-2187', 'adult', 'FADIL BEGANAJ', 0, '2026-05-10 11:35:38'),
(1169, 290, 'TRF-20260510-2187', 'adult', 'DANIJELA BEGANAJ', 1, '2026-05-10 11:35:38'),
(1170, 290, 'TRF-20260510-2187', 'child', 'THEAJULIA BEGANAJ', 0, '2026-05-10 11:35:38'),
(1171, 291, 'TRF-20260510-8823', 'adult', 'FADIL BEGANAJ', 0, '2026-05-10 11:35:38'),
(1172, 291, 'TRF-20260510-8823', 'adult', 'DANIJELA BEGANAJ', 1, '2026-05-10 11:35:38'),
(1173, 291, 'TRF-20260510-8823', 'child', 'THEAJULIA BEGANAJ', 0, '2026-05-10 11:35:38'),
(1174, 292, 'TRF-20260510-4007', 'adult', 'ZEESHAN SAGHIR', 0, '2026-05-10 17:19:35'),
(1175, 195, 'TRF-20260505-3290', 'adult', 'WILHELMUS HALLER', 0, '2026-05-10 17:21:02'),
(1176, 195, 'TRF-20260505-3290', 'adult', 'PATRICIA HALLER', 1, '2026-05-10 17:21:02'),
(1177, 195, 'TRF-20260505-3290', 'adult', 'SHAKIRA HALLER', 2, '2026-05-10 17:21:02'),
(1178, 195, 'TRF-20260505-3290', 'adult', 'MYROM REMMELTS', 3, '2026-05-10 17:21:02'),
(1183, 293, 'TRF-20260510-5026', 'adult', 'BETHANY HANCOCK', 0, '2026-05-10 17:30:31'),
(1184, 293, 'TRF-20260510-5026', 'adult', 'CONNOR HUGHES', 1, '2026-05-10 17:30:31'),
(1187, 295, 'TRF-20260510-9965', 'adult', 'RALF RIEGER', 0, '2026-05-10 17:42:07'),
(1192, 297, 'TRF-20260510-8921', 'adult', 'Julia Haubenreisser', 0, '2026-05-10 17:46:00'),
(1193, 297, 'TRF-20260510-8921', 'adult', 'Lukas Haubenreisser', 1, '2026-05-10 17:46:00'),
(1194, 297, 'TRF-20260510-8921', 'adult', 'Sven uhle', 2, '2026-05-10 17:46:00'),
(1195, 105, 'TRF-20260502-4160', 'adult', 'OWAIS ASIF', 0, '2026-05-10 17:46:39'),
(1196, 105, 'TRF-20260502-4160', 'adult', 'MADIHA RASHID', 1, '2026-05-10 17:46:39'),
(1197, 105, 'TRF-20260502-4160', 'adult', 'MALIHA IRFAN', 2, '2026-05-10 17:46:39'),
(1198, 103, 'TRF-20260502-3738', 'adult', 'SHANAY GORDON', 0, '2026-05-11 07:47:56');
INSERT INTO `booking_passengers` (`id`, `booking_id`, `booking_number`, `passenger_type`, `full_name`, `sort_order`, `created_at`) VALUES
(1199, 103, 'TRF-20260502-3738', 'adult', 'AYLAH AYANLEYE GORDON', 1, '2026-05-11 07:47:56'),
(1204, 101, 'TRF-20260502-8019', 'adult', 'SERGEN ÖZMEN', 0, '2026-05-11 12:45:38'),
(1205, 101, 'TRF-20260502-8019', 'adult', 'SELİN NUR GÜLER', 1, '2026-05-11 12:45:38'),
(1206, 101, 'TRF-20260502-8019', 'adult', 'DAVID BOJKOVIC', 2, '2026-05-11 12:45:38'),
(1207, 101, 'TRF-20260502-8019', 'adult', 'JENNIFER VANESSA MOKROS', 3, '2026-05-11 12:45:38'),
(1208, 298, 'TRF-20260512-8684', 'adult', 'SİBEL PADURARU', 0, '2026-05-12 06:27:58'),
(1209, 298, 'TRF-20260512-8684', 'adult', 'İREM AYALP', 1, '2026-05-12 06:27:58'),
(1211, 299, 'TRF-20260512-5145', 'adult', 'LUMAN ALIZADE', 0, '2026-05-12 06:29:57'),
(1212, 299, 'TRF-20260512-5145', 'adult', 'DELBAR JANAGDOST', 1, '2026-05-12 06:29:57'),
(1213, 300, 'TRF-20260512-3913', 'adult', 'ABDELWAHED ADAHRİ', 0, '2026-05-12 06:33:10'),
(1214, 300, 'TRF-20260512-3913', 'adult', 'PARSA SOLTANI', 1, '2026-05-12 06:33:10'),
(1215, 301, 'TRF-20260512-0781', 'adult', 'JENS KHULMANN', 0, '2026-05-12 06:36:05'),
(1216, 301, 'TRF-20260512-0781', 'adult', 'CHAHWIWAN SEEHAKHAN', 1, '2026-05-12 06:36:05'),
(1217, 302, 'TRF-20260512-2906', 'adult', 'MARIO MIHAILA', 0, '2026-05-12 06:40:16'),
(1218, 302, 'TRF-20260512-2906', 'adult', 'LAURA BADESCU', 1, '2026-05-12 06:40:16'),
(1227, 303, 'TRF-20260512-7243', 'adult', 'THOMAS TIPTHORPE', 0, '2026-05-12 06:47:35'),
(1228, 303, 'TRF-20260512-7243', 'adult', 'ANNE DOHERTY', 1, '2026-05-12 06:47:35'),
(1229, 303, 'TRF-20260512-7243', 'adult', 'ANNEJANE TIPTHORPE', 2, '2026-05-12 06:47:35'),
(1230, 303, 'TRF-20260512-7243', 'adult', 'HUGH TIPTHORPE', 3, '2026-05-12 06:47:35'),
(1231, 304, 'TRF-20260512-3242', 'adult', 'JAMAL HUSSAIN', 0, '2026-05-12 06:52:07'),
(1232, 304, 'TRF-20260512-3242', 'adult', 'HUMAIRA MAJID', 1, '2026-05-12 06:52:07'),
(1233, 304, 'TRF-20260512-3242', 'child', 'AYAAN JAMAL', 0, '2026-05-12 06:52:07'),
(1234, 220, 'TRF-20260505-5607', 'adult', 'ANGELA DI MAGGIO', 0, '2026-05-12 06:54:40'),
(1235, 220, 'TRF-20260505-5607', 'child', 'FABIO PILLITTU', 0, '2026-05-12 06:54:40'),
(1236, 220, 'TRF-20260505-5607', 'child', 'SAMUELE PILLITTU', 1, '2026-05-12 06:54:40'),
(1237, 187, 'TRF-20260505-3632', 'adult', 'MARTA SZARPAK', 0, '2026-05-12 06:57:37'),
(1238, 187, 'TRF-20260505-3632', 'adult', 'KAROLINA OTUSZEWSKA', 1, '2026-05-12 06:57:37'),
(1239, 187, 'TRF-20260505-3632', 'adult', 'MONIKA SOBIAK', 2, '2026-05-12 06:57:37'),
(1240, 187, 'TRF-20260505-3632', 'adult', 'MAJA SKORACKA', 3, '2026-05-12 06:57:37'),
(1241, 187, 'TRF-20260505-3632', 'adult', 'ANITA RATAJCZAK', 4, '2026-05-12 06:57:37'),
(1242, 187, 'TRF-20260505-3632', 'adult', 'AMELIA RATAJCZAK', 5, '2026-05-12 06:57:37'),
(1243, 188, 'TRF-20260505-1759', 'adult', 'MARTA SZARPAK', 0, '2026-05-12 06:58:33'),
(1244, 188, 'TRF-20260505-1759', 'adult', 'KAROLINA OTUSZEWSKA', 1, '2026-05-12 06:58:33'),
(1245, 188, 'TRF-20260505-1759', 'adult', 'MONIKA SOBIAK', 2, '2026-05-12 06:58:33'),
(1246, 188, 'TRF-20260505-1759', 'adult', 'MAJA SKORACKA', 3, '2026-05-12 06:58:33'),
(1247, 188, 'TRF-20260505-1759', 'adult', 'ANITA RATAJCZAK', 4, '2026-05-12 06:58:33'),
(1248, 188, 'TRF-20260505-1759', 'adult', 'AMELIA RATAJCZAK', 5, '2026-05-12 06:58:33'),
(1252, 307, 'TRF-20260512-2680', 'adult', 'RICARDO HERKENS', 0, '2026-05-12 09:04:23'),
(1253, 135, 'TRF-20260503-1636', 'adult', 'SENNA SANDERS', 0, '2026-05-12 16:33:07'),
(1254, 135, 'TRF-20260503-1636', 'adult', 'LEO VAN BEEK', 1, '2026-05-12 16:33:07'),
(1255, 135, 'TRF-20260503-1636', 'adult', 'LAURENZO BAKKER', 2, '2026-05-12 16:33:07'),
(1256, 135, 'TRF-20260503-1636', 'adult', 'KAY VAN BEEK', 3, '2026-05-12 16:33:07'),
(1259, 309, 'TRF-20260513-4126', 'adult', 'KARİM KOPONEN', 0, '2026-05-13 08:31:50'),
(1260, 310, 'TRF-20260513-8616', 'adult', 'ŞENİZ POLATCI', 0, '2026-05-13 08:38:07'),
(1261, 310, 'TRF-20260513-8616', 'adult', 'LEYLA BATTIG', 1, '2026-05-13 08:38:07'),
(1262, 311, 'TRF-20260513-4555', 'adult', 'ŞENİZ POLATCI', 0, '2026-05-13 08:38:07'),
(1263, 311, 'TRF-20260513-4555', 'adult', 'LEYLA BATTIG', 1, '2026-05-13 08:38:07'),
(1264, 191, 'TRF-20260505-5319', 'adult', 'BERKAY KALAYCI', 0, '2026-05-13 08:39:55'),
(1265, 191, 'TRF-20260505-5319', 'adult', 'ANDREJ MANEV', 1, '2026-05-13 08:39:55'),
(1273, 313, 'TRF-20260513-2329', 'adult', 'Vithussan Nanthagopalan', 0, '2026-05-13 12:44:36'),
(1274, 313, 'TRF-20260513-2329', 'adult', 'Avanthisha Ratnasingam', 1, '2026-05-13 12:44:36'),
(1275, 312, 'TRF-20260513-4863', 'adult', 'Tatyana Kapralenko', 0, '2026-05-13 12:44:45'),
(1276, 312, 'TRF-20260513-4863', 'adult', 'Emilia Kapralenko', 1, '2026-05-13 12:44:45'),
(1277, 312, 'TRF-20260513-4863', 'adult', 'Alena Karpishina', 2, '2026-05-13 12:44:45'),
(1278, 139, 'TRF-20260503-0273', 'adult', 'KIARA SWEENEY', 0, '2026-05-14 06:41:56'),
(1279, 139, 'TRF-20260503-0273', 'adult', 'LEO BRINDLEY', 1, '2026-05-14 06:41:56'),
(1280, 308, 'TRF-20260513-5443', 'adult', 'ANGELİKA BÜHRİNG', 0, '2026-05-14 09:36:21'),
(1281, 308, 'TRF-20260513-5443', 'adult', 'ROSALİE SCHİRM', 1, '2026-05-14 09:36:21'),
(1282, 314, 'TRF-20260514-0965', 'adult', 'NELLE WILSON', 0, '2026-05-14 11:28:43'),
(1283, 314, 'TRF-20260514-0965', 'adult', 'FEYI WILSON', 1, '2026-05-14 11:28:43'),
(1288, 142, 'TRF-20260503-0296', 'adult', 'MASIH PAIKARJO', 0, '2026-05-14 14:50:20'),
(1289, 142, 'TRF-20260503-0296', 'adult', 'KATHLEEN CANALES', 1, '2026-05-14 14:50:20'),
(1290, 142, 'TRF-20260503-0296', 'child', 'Arian Elias Canales Paikarjo', 0, '2026-05-14 14:50:20'),
(1291, 316, 'TRF-20260514-8296', 'adult', 'ALEXANDRA IHLE', 0, '2026-05-14 14:57:37'),
(1292, 316, 'TRF-20260514-8296', 'adult', 'LINDA BÖTTCHER', 1, '2026-05-14 14:57:37'),
(1293, 317, 'TRF-20260514-1116', 'adult', 'SIMON SIMEONOV', 0, '2026-05-14 15:08:18'),
(1294, 317, 'TRF-20260514-1116', 'adult', 'Naomi Vleesenbeek', 1, '2026-05-14 15:08:18'),
(1295, 317, 'TRF-20260514-1116', 'adult', 'Boaz Vleesenbeek', 2, '2026-05-14 15:08:18'),
(1296, 317, 'TRF-20260514-1116', 'adult', 'Djenna Vleesenbeek', 3, '2026-05-14 15:08:18'),
(1297, 317, 'TRF-20260514-1116', 'adult', 'Martijn den Haan', 4, '2026-05-14 15:08:18'),
(1298, 317, 'TRF-20260514-1116', 'adult', 'Astrid Hoekstra', 5, '2026-05-14 15:08:18'),
(1299, 317, 'TRF-20260514-1116', 'adult', 'Denise den Haan', 6, '2026-05-14 15:08:18'),
(1300, 317, 'TRF-20260514-1116', 'adult', 'Hugo den Haan', 7, '2026-05-14 15:08:18'),
(1301, 317, 'TRF-20260514-1116', 'adult', 'Milou den Haan', 8, '2026-05-14 15:08:18'),
(1302, 317, 'TRF-20260514-1116', 'adult', 'Mellanie Ponte', 9, '2026-05-14 15:08:18'),
(1303, 317, 'TRF-20260514-1116', 'adult', 'Gianni van Buuringen', 10, '2026-05-14 15:08:18'),
(1304, 318, 'TRF-20260514-9648', 'adult', 'SIMON SIMEONOV', 0, '2026-05-14 15:08:18'),
(1305, 318, 'TRF-20260514-9648', 'adult', 'Naomi Vleesenbeek', 1, '2026-05-14 15:08:18'),
(1306, 318, 'TRF-20260514-9648', 'adult', 'Boaz Vleesenbeek', 2, '2026-05-14 15:08:18'),
(1307, 318, 'TRF-20260514-9648', 'adult', 'Djenna Vleesenbeek', 3, '2026-05-14 15:08:18'),
(1308, 318, 'TRF-20260514-9648', 'adult', 'Martijn den Haan', 4, '2026-05-14 15:08:18'),
(1309, 318, 'TRF-20260514-9648', 'adult', 'Astrid Hoekstra', 5, '2026-05-14 15:08:18'),
(1310, 318, 'TRF-20260514-9648', 'adult', 'Denise den Haan', 6, '2026-05-14 15:08:18'),
(1311, 318, 'TRF-20260514-9648', 'adult', 'Hugo den Haan', 7, '2026-05-14 15:08:18'),
(1312, 318, 'TRF-20260514-9648', 'adult', 'Milou den Haan', 8, '2026-05-14 15:08:18'),
(1313, 318, 'TRF-20260514-9648', 'adult', 'Mellanie Ponte', 9, '2026-05-14 15:08:18'),
(1314, 318, 'TRF-20260514-9648', 'adult', 'Gianni van Buuringen', 10, '2026-05-14 15:08:18'),
(1315, 305, 'TRF-20260512-9695', 'adult', 'ROSMARIE FURRER', 0, '2026-05-14 15:15:31'),
(1316, 305, 'TRF-20260512-9695', 'adult', 'DANIEL FURRER', 1, '2026-05-14 15:15:31'),
(1335, 112, 'TRF-20260502-1393', 'adult', 'YUSSOF ALAM', 0, '2026-05-14 19:42:08'),
(1336, 112, 'TRF-20260502-1393', 'adult', 'JAMSHED ALAM', 1, '2026-05-14 19:42:08'),
(1337, 112, 'TRF-20260502-1393', 'adult', 'HAMSA ALAM', 2, '2026-05-14 19:42:08'),
(1350, 321, 'TRF-20260515-9243', 'adult', 'KATARINA DEDIC', 0, '2026-05-15 12:09:42'),
(1351, 321, 'TRF-20260515-9243', 'adult', 'De is dedic', 1, '2026-05-15 12:09:42'),
(1352, 321, 'TRF-20260515-9243', 'adult', 'Donuna dedic', 2, '2026-05-15 12:09:42'),
(1353, 321, 'TRF-20260515-9243', 'adult', 'Mila dedic', 3, '2026-05-15 12:09:42'),
(1357, 322, 'TRF-20260515-5277', 'adult', 'Fırat vurucu', 0, '2026-05-15 18:54:09'),
(1358, 322, 'TRF-20260515-5277', 'adult', 'Emir vurucu', 1, '2026-05-15 18:54:09'),
(1359, 322, 'TRF-20260515-5277', 'adult', 'Zin vurucu', 2, '2026-05-15 18:54:09'),
(1360, 144, 'TRF-20260503-6675', 'adult', 'VADIMS STEPANOVS', 0, '2026-05-15 19:40:32'),
(1361, 144, 'TRF-20260503-6675', 'adult', 'LINDA CERUKA', 1, '2026-05-15 19:40:32'),
(1362, 144, 'TRF-20260503-6675', 'adult', 'ALEKSANDRA STEPANOVA', 2, '2026-05-15 19:40:32'),
(1363, 144, 'TRF-20260503-6675', 'adult', 'VIKTORIJA STEPANOVA', 3, '2026-05-15 19:40:32'),
(1364, 323, 'TRF-20260515-9147', 'adult', 'Tabea Theilen', 0, '2026-05-15 19:54:42'),
(1365, 323, 'TRF-20260515-9147', 'adult', 'Tanja Theilen', 1, '2026-05-15 19:54:42'),
(1366, 324, 'TRF-20260515-7562', 'adult', 'Tabea Theilen', 0, '2026-05-15 19:54:42'),
(1367, 324, 'TRF-20260515-7562', 'adult', 'Tanja Theilen', 1, '2026-05-15 19:54:42'),
(1374, 294, 'TRF-20260510-6317', 'adult', 'BETHANY HANCOCK', 0, '2026-05-16 14:06:12'),
(1375, 294, 'TRF-20260510-6317', 'adult', 'CONNOR HUGHES', 1, '2026-05-16 14:06:12'),
(1408, 328, 'TRF-20260516-0966', 'adult', 'ARTEM KUNGURZEV', 0, '2026-05-16 17:43:56'),
(1409, 328, 'TRF-20260516-0966', 'adult', 'MAXIM KUNGURZEV', 1, '2026-05-16 17:43:56'),
(1410, 328, 'TRF-20260516-0966', 'adult', 'KRISTINA KUNGURZEV', 2, '2026-05-16 17:43:56'),
(1411, 328, 'TRF-20260516-0966', 'adult', 'ANTON KUNGURZEV', 3, '2026-05-16 17:43:56'),
(1412, 328, 'TRF-20260516-0966', 'adult', 'DANIEL KUNGURZEV', 4, '2026-05-16 17:43:56'),
(1413, 328, 'TRF-20260516-0966', 'adult', 'ELENA KUNGURZEV', 5, '2026-05-16 17:43:56'),
(1414, 328, 'TRF-20260516-0966', 'adult', 'EVGENIJ KUNGURZEV', 6, '2026-05-16 17:43:56'),
(1449, 319, 'TRF-20260514-0175', 'adult', 'SUHAILA YONAN', 0, '2026-05-16 18:01:11'),
(1450, 319, 'TRF-20260514-0175', 'adult', 'RAFAEL KARIM', 1, '2026-05-16 18:01:11'),
(1451, 319, 'TRF-20260514-0175', 'adult', 'EMILIS KARIM', 2, '2026-05-16 18:01:11'),
(1452, 319, 'TRF-20260514-0175', 'adult', 'MARANA DABBA', 3, '2026-05-16 18:01:11'),
(1453, 319, 'TRF-20260514-0175', 'adult', 'PIETER MEKA', 4, '2026-05-16 18:01:11'),
(1454, 319, 'TRF-20260514-0175', 'adult', 'PEDRO MEKA', 5, '2026-05-16 18:01:11'),
(1455, 319, 'TRF-20260514-0175', 'adult', 'LAVINA MEKA', 6, '2026-05-16 18:01:11'),
(1456, 319, 'TRF-20260514-0175', 'adult', 'NAVAL RAFU', 7, '2026-05-16 18:01:11'),
(1457, 319, 'TRF-20260514-0175', 'adult', 'EVETA KARIM', 8, '2026-05-16 18:01:11'),
(1461, 120, 'TRF-20260502-2039', 'adult', 'NADINE RADES', 0, '2026-05-16 18:09:32'),
(1462, 120, 'TRF-20260502-2039', 'adult', 'SYLVIA RADES', 1, '2026-05-16 18:09:32'),
(1463, 120, 'TRF-20260502-2039', 'adult', 'RALF RADES', 2, '2026-05-16 18:09:32'),
(1464, 120, 'TRF-20260502-2039', 'adult', 'MARTIN STREICH', 3, '2026-05-16 18:09:32'),
(1465, 120, 'TRF-20260502-2039', 'adult', 'KAROLA STREICH', 4, '2026-05-16 18:09:32'),
(1466, 120, 'TRF-20260502-2039', 'adult', 'JÜRGEN STREICH', 5, '2026-05-16 18:09:32'),
(1467, 120, 'TRF-20260502-2039', 'adult', 'CINDY KOWALZIK', 6, '2026-05-16 18:09:32'),
(1468, 120, 'TRF-20260502-2039', 'adult', 'JULIANE KOWALZIK', 7, '2026-05-16 18:09:32'),
(1472, 331, 'TRF-20260516-2390', 'adult', 'TIRZA VAN ROOIJEN', 0, '2026-05-17 18:02:08'),
(1473, 331, 'TRF-20260516-2390', 'adult', 'DAVEY BERNARD', 1, '2026-05-17 18:02:08'),
(1474, 331, 'TRF-20260516-2390', 'adult', 'NOMEE BERNARD', 2, '2026-05-17 18:02:08'),
(1475, 326, 'TRF-20260516-8383', 'adult', 'SIMON STAIBER', 0, '2026-05-17 18:05:47'),
(1476, 326, 'TRF-20260516-8383', 'adult', 'KATRIN STAIBER', 1, '2026-05-17 18:05:47'),
(1477, 326, 'TRF-20260516-8383', 'adult', 'MAXIMILIAN STAIBER', 2, '2026-05-17 18:05:47'),
(1478, 326, 'TRF-20260516-8383', 'adult', 'ANDREAS ANTONI', 3, '2026-05-17 18:05:47'),
(1479, 326, 'TRF-20260516-8383', 'adult', 'OLGA ANTONI', 4, '2026-05-17 18:05:47'),
(1480, 326, 'TRF-20260516-8383', 'adult', 'EMILIA ANTONI', 5, '2026-05-17 18:05:47'),
(1481, 326, 'TRF-20260516-8383', 'adult', 'MARIO ANTONI', 6, '2026-05-17 18:05:47'),
(1489, 335, 'TRF-20260517-5201', 'adult', 'DOROTA KORBUS', 0, '2026-05-17 18:19:14'),
(1490, 335, 'TRF-20260517-5201', 'adult', 'DANIEL KORBUS', 1, '2026-05-17 18:19:14'),
(1491, 335, 'TRF-20260517-5201', 'adult', 'EWA KORBUS', 2, '2026-05-17 18:19:14'),
(1492, 335, 'TRF-20260517-5201', 'adult', 'NICODEM SKRZYSZOWSKI', 3, '2026-05-17 18:19:14'),
(1493, 335, 'TRF-20260517-5201', 'adult', 'DARIUSZ SKRZYSZOWSKI', 4, '2026-05-17 18:19:14'),
(1494, 335, 'TRF-20260517-5201', 'adult', 'LILIANA KORBUS', 5, '2026-05-17 18:19:14'),
(1495, 335, 'TRF-20260517-5201', 'adult', 'KRZYSZTOF KORBUS', 6, '2026-05-17 18:19:14'),
(1496, 335, 'TRF-20260517-5201', 'adult', 'ALICJA KORBUS', 7, '2026-05-17 18:19:14'),
(1497, 335, 'TRF-20260517-5201', 'adult', 'MALGORZATA GRAFE', 8, '2026-05-17 18:19:14'),
(1498, 335, 'TRF-20260517-5201', 'adult', 'KATARZYNA TROCIAK', 9, '2026-05-17 18:19:14'),
(1499, 335, 'TRF-20260517-5201', 'adult', 'PIOTR TROCIAK', 10, '2026-05-17 18:19:14'),
(1522, 336, 'TRF-20260517-5617', 'adult', 'MICHAEL KRINJEN', 0, '2026-05-17 18:34:59'),
(1523, 336, 'TRF-20260517-5617', 'adult', 'ROMY KRONMOLLER', 1, '2026-05-17 18:34:59'),
(1524, 336, 'TRF-20260517-5617', 'adult', 'JIOVANNI KRONMELLER', 2, '2026-05-17 18:34:59'),
(1525, 336, 'TRF-20260517-5617', 'adult', 'KIM VAN KAN', 3, '2026-05-17 18:34:59'),
(1526, 337, 'TRF-20260517-4452', 'adult', 'MICHAEL KRINJEN', 0, '2026-05-17 18:35:08'),
(1527, 337, 'TRF-20260517-4452', 'adult', 'ROMY KRONMOLLER', 1, '2026-05-17 18:35:08'),
(1528, 337, 'TRF-20260517-4452', 'adult', 'JIOVANNI KRONMELLER', 2, '2026-05-17 18:35:08'),
(1529, 337, 'TRF-20260517-4452', 'adult', 'KIM VAN KAN', 3, '2026-05-17 18:35:08'),
(1530, 339, 'TRF-20260517-4974', 'adult', 'MEHMET ALİ YILDIZ', 0, '2026-05-17 18:38:57'),
(1531, 339, 'TRF-20260517-4974', 'adult', 'RESUL YILDIZ', 1, '2026-05-17 18:38:57'),
(1532, 339, 'TRF-20260517-4974', 'adult', 'ZÜLEHYA YILDIZ', 2, '2026-05-17 18:38:57'),
(1536, 341, 'TRF-20260517-4756', 'adult', 'PETRA ANDROLEIT', 0, '2026-05-17 18:42:33'),
(1540, 344, 'TRF-20260517-3220', 'adult', 'KLAUS DIETER BACZYK', 0, '2026-05-17 18:47:07'),
(1541, 344, 'TRF-20260517-3220', 'adult', 'KLAUS DIETER BACZYK', 1, '2026-05-17 18:47:07'),
(1542, 154, 'TRF-20260504-0285', 'adult', 'SILVIU FLORIAN BOC', 0, '2026-05-17 18:55:13'),
(1543, 154, 'TRF-20260504-0285', 'adult', 'ALBERT ALXENDRU ANTONICA', 1, '2026-05-17 18:55:13'),
(1544, 154, 'TRF-20260504-0285', 'adult', 'STEFANIA ANDREEA ANTONICA', 2, '2026-05-17 18:55:13'),
(1545, 154, 'TRF-20260504-0285', 'adult', 'ALEXANDRU KARIM ANTONICA', 3, '2026-05-17 18:55:13'),
(1546, 154, 'TRF-20260504-0285', 'adult', 'TIAGO GABRIEL ANTONICA', 4, '2026-05-17 18:55:13'),
(1547, 154, 'TRF-20260504-0285', 'adult', 'LUMINITA ANTONICA', 5, '2026-05-17 18:55:13'),
(1548, 154, 'TRF-20260504-0285', 'adult', 'MARIAN ANTONICA', 6, '2026-05-17 18:55:13'),
(1549, 154, 'TRF-20260504-0285', 'adult', 'CONSTANTINA DACIANA IZVERNARI', 7, '2026-05-17 18:55:13'),
(1550, 154, 'TRF-20260504-0285', 'adult', 'LUKACS ANDRAS MARIAN', 8, '2026-05-17 18:55:13'),
(1556, 138, 'TRF-20260503-8992', 'adult', 'STEFFI SCHAPER', 0, '2026-05-18 17:15:58'),
(1557, 138, 'TRF-20260503-8992', 'adult', 'UWE SCHAPER', 1, '2026-05-18 17:15:58'),
(1558, 138, 'TRF-20260503-8992', 'adult', 'INES KAUFMANN', 2, '2026-05-18 17:15:58'),
(1559, 347, 'TRF-20260518-3070', 'adult', 'Luis webels', 0, '2026-05-18 18:47:41'),
(1560, 347, 'TRF-20260518-3070', 'adult', 'Jana webels', 1, '2026-05-18 18:47:41'),
(1561, 347, 'TRF-20260518-3070', 'adult', 'Liam webels', 2, '2026-05-18 18:47:41'),
(1562, 348, 'TRF-20260518-5435', 'adult', 'Luis webels', 0, '2026-05-18 18:47:41'),
(1563, 348, 'TRF-20260518-5435', 'adult', 'Jana webels', 1, '2026-05-18 18:47:41'),
(1564, 348, 'TRF-20260518-5435', 'adult', 'Liam webels', 2, '2026-05-18 18:47:41'),
(1590, 173, 'TRF-20260505-1606', 'adult', 'ANDREAS KOZOLE', 0, '2026-05-18 19:23:24'),
(1591, 173, 'TRF-20260505-1606', 'adult', 'MONIKA KOZOLE', 1, '2026-05-18 19:23:24'),
(1592, 173, 'TRF-20260505-1606', 'adult', 'ALEXANDER SCHWENK', 2, '2026-05-18 19:23:24'),
(1593, 173, 'TRF-20260505-1606', 'adult', 'FABIAN JASHINSKI', 3, '2026-05-18 19:23:24'),
(1594, 174, 'TRF-20260505-9421', 'adult', 'ANDREAS KOZOLE', 0, '2026-05-18 19:23:43'),
(1595, 174, 'TRF-20260505-9421', 'adult', 'MONIKA KOZOLE', 1, '2026-05-18 19:23:43'),
(1596, 174, 'TRF-20260505-9421', 'adult', 'ALEXANDER SCHWENK', 2, '2026-05-18 19:23:43'),
(1597, 174, 'TRF-20260505-9421', 'adult', 'FABIAN JASHINSKI', 3, '2026-05-18 19:23:43'),
(1598, 351, 'TRF-20260518-5602', 'adult', 'Senna sanders', 0, '2026-05-18 19:24:02'),
(1599, 351, 'TRF-20260518-5602', 'adult', 'Leo van beek', 1, '2026-05-18 19:24:02'),
(1600, 351, 'TRF-20260518-5602', 'adult', 'Kay van beek', 2, '2026-05-18 19:24:02'),
(1601, 352, 'TRF-20260518-0654', 'adult', 'Gülsün yaşar', 0, '2026-05-18 19:29:51'),
(1602, 352, 'TRF-20260518-0654', 'adult', 'Ramazan sarpkaya', 1, '2026-05-18 19:29:51'),
(1603, 352, 'TRF-20260518-0654', 'adult', 'Adem sarpkaya', 2, '2026-05-18 19:29:51'),
(1604, 352, 'TRF-20260518-0654', 'adult', 'Elif sarpkaya', 3, '2026-05-18 19:29:51'),
(1616, 356, 'TRF-20260519-2910', 'adult', 'FIRAT VURUCU', 0, '2026-05-19 14:03:59'),
(1617, 356, 'TRF-20260519-2910', 'adult', 'EMİR VURUCU', 1, '2026-05-19 14:03:59'),
(1618, 356, 'TRF-20260519-2910', 'adult', 'ZİN VURUCU', 2, '2026-05-19 14:03:59'),
(1619, 357, 'TRF-20260519-4291', 'adult', 'AHMET TANRIKOLU', 0, '2026-05-19 14:09:57'),
(1620, 357, 'TRF-20260519-4291', 'adult', 'ASYA TANRIKOLU', 1, '2026-05-19 14:09:57'),
(1621, 357, 'TRF-20260519-4291', 'adult', 'DEFNE TANRIKOLU', 2, '2026-05-19 14:09:57'),
(1622, 358, 'TRF-20260519-4727', 'adult', 'BLEONA AHMETI', 0, '2026-05-19 14:16:56'),
(1623, 358, 'TRF-20260519-4727', 'adult', 'BESART DAJAKU', 1, '2026-05-19 14:16:56'),
(1624, 358, 'TRF-20260519-4727', 'adult', 'DARIS DAJAKU', 2, '2026-05-19 14:16:56'),
(1625, 358, 'TRF-20260519-4727', 'adult', 'AMARIS DAJAKU', 3, '2026-05-19 14:16:56'),
(1630, 359, 'TRF-20260519-1192', 'adult', 'BLEONA AHMETI', 0, '2026-05-19 14:18:46'),
(1631, 359, 'TRF-20260519-1192', 'adult', 'BESART DAJAKU', 1, '2026-05-19 14:18:46'),
(1632, 359, 'TRF-20260519-1192', 'adult', 'DARIS DAJAKU', 2, '2026-05-19 14:18:46'),
(1633, 359, 'TRF-20260519-1192', 'adult', 'AMARIS DAJAKU', 3, '2026-05-19 14:18:46'),
(1634, 332, 'TRF-20260516-3103', 'adult', 'RYAN BURNS', 0, '2026-05-19 14:21:19'),
(1635, 332, 'TRF-20260516-3103', 'adult', 'JEMME BURNS', 1, '2026-05-19 14:21:19'),
(1636, 332, 'TRF-20260516-3103', 'adult', 'CONNIE BURNS', 2, '2026-05-19 14:21:19'),
(1637, 332, 'TRF-20260516-3103', 'adult', 'CAROLE BURNS', 3, '2026-05-19 14:21:19'),
(1638, 332, 'TRF-20260516-3103', 'adult', 'SCOTT BURNS', 4, '2026-05-19 14:21:19'),
(1639, 332, 'TRF-20260516-3103', 'adult', 'EVIE BURNS', 5, '2026-05-19 14:21:19'),
(1640, 332, 'TRF-20260516-3103', 'adult', 'MOLLY BURNS', 6, '2026-05-19 14:21:19'),
(1641, 332, 'TRF-20260516-3103', 'adult', 'ALISON BURNS', 7, '2026-05-19 14:21:19'),
(1642, 332, 'TRF-20260516-3103', 'adult', 'GARY BURNS', 8, '2026-05-19 14:21:19'),
(1643, 332, 'TRF-20260516-3103', 'adult', 'NOAH BURNS', 9, '2026-05-19 14:21:19'),
(1644, 332, 'TRF-20260516-3103', 'adult', 'GRACE BURNS', 10, '2026-05-19 14:21:19'),
(1645, 333, 'TRF-20260516-5038', 'adult', 'RYAN BURNS', 0, '2026-05-19 14:21:42'),
(1646, 333, 'TRF-20260516-5038', 'adult', 'JEMME BURNS', 1, '2026-05-19 14:21:42'),
(1647, 333, 'TRF-20260516-5038', 'adult', 'CONNIE BURNS', 2, '2026-05-19 14:21:42'),
(1648, 333, 'TRF-20260516-5038', 'adult', 'CAROLE BURNS', 3, '2026-05-19 14:21:42'),
(1649, 333, 'TRF-20260516-5038', 'adult', 'SCOTT BURNS', 4, '2026-05-19 14:21:42'),
(1650, 333, 'TRF-20260516-5038', 'adult', 'EVIE BURNS', 5, '2026-05-19 14:21:42'),
(1651, 333, 'TRF-20260516-5038', 'adult', 'MOLLY BURNS', 6, '2026-05-19 14:21:42'),
(1652, 333, 'TRF-20260516-5038', 'adult', 'ALISON BURNS', 7, '2026-05-19 14:21:42'),
(1653, 333, 'TRF-20260516-5038', 'adult', 'GARY BURNS', 8, '2026-05-19 14:21:42'),
(1654, 333, 'TRF-20260516-5038', 'adult', 'NOAH BURNS', 9, '2026-05-19 14:21:42'),
(1655, 333, 'TRF-20260516-5038', 'adult', 'GRACE BURNS', 10, '2026-05-19 14:21:42'),
(1656, 360, 'TRF-20260519-6029', 'adult', 'FABIAN DOKL', 0, '2026-05-19 18:19:58'),
(1657, 360, 'TRF-20260519-6029', 'adult', 'JULIA LINDHORST', 1, '2026-05-19 18:19:58'),
(1658, 361, 'TRF-20260519-7962', 'adult', 'JOLINA KOTHE', 0, '2026-05-19 18:22:31'),
(1659, 361, 'TRF-20260519-7962', 'adult', 'Charlotte Herberg', 1, '2026-05-19 18:22:31'),
(1660, 361, 'TRF-20260519-7962', 'adult', 'Jolien Blümel', 2, '2026-05-19 18:22:31'),
(1669, 350, 'TRF-20260518-9042', 'adult', 'Carole swinburne', 0, '2026-05-19 18:31:30'),
(1670, 350, 'TRF-20260518-9042', 'adult', 'Michael mnulty', 1, '2026-05-19 18:31:30'),
(1679, 67, 'TRF-20260430-7210', 'adult', 'DANNY HENNEKES', 0, '2026-05-20 07:42:24'),
(1680, 67, 'TRF-20260430-7210', 'adult', 'MANDY HENNEKES', 1, '2026-05-20 07:42:24'),
(1681, 67, 'TRF-20260430-7210', 'adult', 'SEM HENNEKES', 2, '2026-05-20 07:42:24'),
(1682, 67, 'TRF-20260430-7210', 'adult', 'RAMON HOL', 3, '2026-05-20 07:42:24'),
(1683, 67, 'TRF-20260430-7210', 'adult', 'KARIN HOL', 4, '2026-05-20 07:42:24'),
(1684, 67, 'TRF-20260430-7210', 'adult', 'MEVI HOL', 5, '2026-05-20 07:42:24'),
(1685, 365, 'TRF-20260520-5243', 'adult', 'Serap couper', 0, '2026-05-20 16:13:34'),
(1686, 365, 'TRF-20260520-5243', 'adult', 'Elowen couper', 1, '2026-05-20 16:13:34'),
(1688, 306, 'TRF-20260512-3335', 'adult', 'TATIANA LAZEROVA', 0, '2026-05-21 06:22:40'),
(1689, 354, 'TRF-20260518-0551', 'adult', 'Fahmi laouini', 0, '2026-05-21 09:25:52'),
(1690, 354, 'TRF-20260518-0551', 'adult', 'Sarah laouini', 1, '2026-05-21 09:25:52'),
(1691, 354, 'TRF-20260518-0551', 'adult', 'Essya laouini', 2, '2026-05-21 09:25:52'),
(1692, 340, 'TRF-20260517-5003', 'adult', 'MEHMET ALİ YILDIZ', 0, '2026-05-21 10:59:37'),
(1693, 340, 'TRF-20260517-5003', 'adult', 'RESUL YILDIZ', 1, '2026-05-21 10:59:37'),
(1694, 340, 'TRF-20260517-5003', 'adult', 'ZÜLEHYA YILDIZ', 2, '2026-05-21 10:59:37'),
(1695, 366, 'TRF-20260521-7036', 'adult', 'Elena Peitsch', 0, '2026-05-21 11:21:37'),
(1696, 366, 'TRF-20260521-7036', 'adult', 'KATHARINA POPKOV', 1, '2026-05-21 11:21:37'),
(1697, 366, 'TRF-20260521-7036', 'adult', 'LIEBELT MARKUS', 2, '2026-05-21 11:21:37'),
(1698, 366, 'TRF-20260521-7036', 'adult', 'MARCEL PEITSCH', 3, '2026-05-21 11:21:37'),
(1699, 366, 'TRF-20260521-7036', 'child', 'CARLFRIEDRIC ROSE', 0, '2026-05-21 11:21:37'),
(1700, 366, 'TRF-20260521-7036', 'child', 'FREDERIC ROSE', 1, '2026-05-21 11:21:37'),
(1701, 367, 'TRF-20260521-7067', 'adult', 'Elena Peitsch', 0, '2026-05-21 11:21:37'),
(1702, 367, 'TRF-20260521-7067', 'adult', 'KATHARINA POPKOV', 1, '2026-05-21 11:21:37'),
(1703, 367, 'TRF-20260521-7067', 'adult', 'LIEBELT MARKUS', 2, '2026-05-21 11:21:37'),
(1704, 367, 'TRF-20260521-7067', 'adult', 'MARCEL PEITSCH', 3, '2026-05-21 11:21:37'),
(1705, 367, 'TRF-20260521-7067', 'child', 'CARLFRIEDRIC ROSE', 0, '2026-05-21 11:21:37'),
(1706, 367, 'TRF-20260521-7067', 'child', 'FREDERIC ROSE', 1, '2026-05-21 11:21:37'),
(1707, 368, 'TRF-20260521-1904', 'adult', 'OESEM AKKABAL', 0, '2026-05-21 11:25:42'),
(1708, 368, 'TRF-20260521-1904', 'adult', 'HANANE ABDESLAMI', 1, '2026-05-21 11:25:42'),
(1709, 368, 'TRF-20260521-1904', 'adult', 'JANNA AKKABAL', 2, '2026-05-21 11:25:42'),
(1710, 368, 'TRF-20260521-1904', 'adult', 'SELINE AKKABAL', 3, '2026-05-21 11:25:42'),
(1711, 368, 'TRF-20260521-1904', 'adult', 'AYDIN AKKABAL', 4, '2026-05-21 11:25:42'),
(1712, 369, 'TRF-20260521-0378', 'adult', 'OESEM AKKABAL', 0, '2026-05-21 11:25:42'),
(1713, 369, 'TRF-20260521-0378', 'adult', 'HANANE ABDESLAMI', 1, '2026-05-21 11:25:42'),
(1714, 369, 'TRF-20260521-0378', 'adult', 'JANNA AKKABAL', 2, '2026-05-21 11:25:42'),
(1715, 369, 'TRF-20260521-0378', 'adult', 'SELINE AKKABAL', 3, '2026-05-21 11:25:42'),
(1716, 369, 'TRF-20260521-0378', 'adult', 'AYDIN AKKABAL', 4, '2026-05-21 11:25:42'),
(1725, 370, 'TRF-20260522-0950', 'adult', 'REXHEP ZUKA', 0, '2026-05-21 22:07:53'),
(1726, 370, 'TRF-20260522-0950', 'adult', 'Arigona zuka', 1, '2026-05-21 22:07:53'),
(1727, 370, 'TRF-20260522-0950', 'child', 'Liam zuka', 0, '2026-05-21 22:07:53'),
(1728, 370, 'TRF-20260522-0950', 'child', 'Liana zuka', 1, '2026-05-21 22:07:53'),
(1729, 371, 'TRF-20260522-1779', 'adult', 'REXHEP ZUKA', 0, '2026-05-21 22:08:11'),
(1730, 371, 'TRF-20260522-1779', 'adult', 'Arigona zuka', 1, '2026-05-21 22:08:11'),
(1731, 371, 'TRF-20260522-1779', 'child', 'Liam zuka', 0, '2026-05-21 22:08:11'),
(1732, 371, 'TRF-20260522-1779', 'child', 'Liana zuka', 1, '2026-05-21 22:08:11'),
(1733, 320, 'TRF-20260515-3162', 'adult', 'Dylen de noijer', 0, '2026-05-22 06:37:21'),
(1734, 320, 'TRF-20260515-3162', 'adult', 'Patricia manni', 1, '2026-05-22 06:37:21'),
(1735, 320, 'TRF-20260515-3162', 'adult', 'Guido de nooijer', 2, '2026-05-22 06:37:21'),
(1740, 373, 'TRF-20260522-5181', 'adult', 'YASEMİN DEMİR', 0, '2026-05-22 07:39:19'),
(1741, 373, 'TRF-20260522-5181', 'adult', 'TUĞBA DEMİR', 1, '2026-05-22 07:39:19'),
(1742, 171, 'TRF-20260505-3439', 'adult', 'ALEKSANDRA PRUCHNIEWSKA', 0, '2026-05-22 07:51:50'),
(1743, 171, 'TRF-20260505-3439', 'adult', 'MICHAL PRUCHNIEWSKI', 1, '2026-05-22 07:51:50'),
(1744, 171, 'TRF-20260505-3439', 'adult', 'MARIOLA PRZYBYLOWICZ', 2, '2026-05-22 07:51:50'),
(1745, 171, 'TRF-20260505-3439', 'adult', 'PRZEMYSLAW PRZYBYLOWICZ', 3, '2026-05-22 07:51:50'),
(1746, 372, 'TRF-20260522-3383', 'adult', 'HASSAN HASSAN', 0, '2026-05-22 11:25:01'),
(1747, 372, 'TRF-20260522-3383', 'adult', 'RUQAYA ALASLANI', 1, '2026-05-22 11:25:01'),
(1748, 372, 'TRF-20260522-3383', 'child', 'YOSUF ALAA', 0, '2026-05-22 11:25:01'),
(1749, 372, 'TRF-20260522-3383', 'child', 'MENA ALAA', 1, '2026-05-22 11:25:01'),
(1750, 374, 'TRF-20260522-0767', 'adult', 'VADIM STEPANOVS', 0, '2026-05-22 11:29:27'),
(1751, 374, 'TRF-20260522-0767', 'adult', 'Linda Ceruka', 1, '2026-05-22 11:29:27'),
(1752, 374, 'TRF-20260522-0767', 'adult', 'Aleksandra Stepanova', 2, '2026-05-22 11:29:27'),
(1753, 374, 'TRF-20260522-0767', 'adult', 'Viktorija Stepanova', 3, '2026-05-22 11:29:27'),
(1754, 375, 'TRF-20260522-6353', 'adult', 'KADRUSH SELIMI', 0, '2026-05-22 11:46:44'),
(1755, 375, 'TRF-20260522-6353', 'adult', 'NAZIFE SELIMI', 1, '2026-05-22 11:46:44'),
(1756, 375, 'TRF-20260522-6353', 'adult', 'ERON SELIMI', 2, '2026-05-22 11:46:44'),
(1757, 376, 'TRF-20260522-0040', 'adult', 'KADRUSH SELIMI', 0, '2026-05-22 11:46:44'),
(1758, 376, 'TRF-20260522-0040', 'adult', 'NAZIFE SELIMI', 1, '2026-05-22 11:46:44'),
(1759, 376, 'TRF-20260522-0040', 'adult', 'ERON SELIMI', 2, '2026-05-22 11:46:44'),
(1760, 329, 'TRF-20260516-3629', 'adult', 'SARAH HERWEG', 0, '2026-05-23 02:54:36'),
(1761, 329, 'TRF-20260516-3629', 'adult', 'MARVIN HERWEG', 1, '2026-05-23 02:54:36'),
(1762, 329, 'TRF-20260516-3629', 'adult', 'HELMU HERWEG', 2, '2026-05-23 02:54:36'),
(1763, 329, 'TRF-20260516-3629', 'adult', 'STEVEN HERWEG', 3, '2026-05-23 02:54:36'),
(1764, 329, 'TRF-20260516-3629', 'adult', 'NICK HERWEG', 4, '2026-05-23 02:54:36'),
(1765, 329, 'TRF-20260516-3629', 'adult', 'SANDY HERWEG', 5, '2026-05-23 02:54:36'),
(1773, 380, 'TRF-20260523-1340', 'adult', 'IRENA GUTIN', 0, '2026-05-23 12:36:37'),
(1774, 380, 'TRF-20260523-1340', 'adult', 'ANATOLI GUTIN', 1, '2026-05-23 12:36:37'),
(1775, 380, 'TRF-20260523-1340', 'adult', 'OLGA USPENSKAYA', 2, '2026-05-23 12:36:37'),
(1780, 381, 'TRF-20260523-8725', 'adult', 'Hana Ramcilovic', 0, '2026-05-23 14:25:37'),
(1781, 381, 'TRF-20260523-8725', 'adult', 'ELDİN RAMILOVIC', 1, '2026-05-23 14:25:37'),
(1782, 381, 'TRF-20260523-8725', 'adult', 'NERMINA RAMILOVIC', 2, '2026-05-23 14:25:37'),
(1783, 381, 'TRF-20260523-8725', 'adult', 'ENISA MARVIC', 3, '2026-05-23 14:25:37'),
(1784, 381, 'TRF-20260523-8725', 'adult', 'ZAYAN RAMILOVIC', 4, '2026-05-23 14:25:37'),
(1795, 382, 'TRF-20260523-1773', 'adult', 'BİLAL KÖYCÜ', 0, '2026-05-23 14:30:11'),
(1796, 382, 'TRF-20260523-1773', 'adult', 'LANA KÖYCÜ', 1, '2026-05-23 14:30:11'),
(1797, 382, 'TRF-20260523-1773', 'adult', 'SID KÖYCÜ', 2, '2026-05-23 14:30:11'),
(1798, 382, 'TRF-20260523-1773', 'adult', 'AYLİN KÖYCÜ', 3, '2026-05-23 14:30:11'),
(1799, 382, 'TRF-20260523-1773', 'adult', 'ESRA KÖYCÜ', 4, '2026-05-23 14:30:11'),
(1800, 338, 'TRF-20260517-8580', 'adult', 'SAM LARKIN', 0, '2026-05-23 14:35:46'),
(1801, 338, 'TRF-20260517-8580', 'adult', 'SEGAY HUGHES', 1, '2026-05-23 14:35:46'),
(1802, 338, 'TRF-20260517-8580', 'adult', 'JACQUELINE WILKS', 2, '2026-05-23 14:35:46'),
(1803, 338, 'TRF-20260517-8580', 'adult', 'LAKISHA DE WEEVER', 3, '2026-05-23 14:35:46'),
(1804, 338, 'TRF-20260517-8580', 'adult', 'TORIA LARKIN', 4, '2026-05-23 14:35:46'),
(1805, 338, 'TRF-20260517-8580', 'adult', 'COREY LARKIN', 5, '2026-05-23 14:35:46'),
(1806, 383, 'TRF-20260524-8987', 'adult', 'Silviu Florian Boc', 0, '2026-05-24 09:49:30'),
(1807, 383, 'TRF-20260524-8987', 'adult', 'Albert Alexandru antonica', 1, '2026-05-24 09:49:30'),
(1808, 383, 'TRF-20260524-8987', 'adult', 'Ștefania Andreea Antonica', 2, '2026-05-24 09:49:30'),
(1809, 383, 'TRF-20260524-8987', 'adult', 'Karim Alexandru Antonica', 3, '2026-05-24 09:49:30'),
(1810, 383, 'TRF-20260524-8987', 'adult', 'Tiago Gabriel Antonica', 4, '2026-05-24 09:49:30'),
(1811, 383, 'TRF-20260524-8987', 'adult', 'Luminița Antonica', 5, '2026-05-24 09:49:30'),
(1812, 383, 'TRF-20260524-8987', 'adult', 'Marian Antonica', 6, '2026-05-24 09:49:30'),
(1813, 383, 'TRF-20260524-8987', 'adult', 'Constantina Daciana Izvernari', 7, '2026-05-24 09:49:30'),
(1814, 384, 'TRF-20260524-1312', 'adult', 'FAHMI LAOUINI', 0, '2026-05-24 09:52:54'),
(1815, 384, 'TRF-20260524-1312', 'adult', 'Sarah LAOUINI', 1, '2026-05-24 09:52:54'),
(1816, 384, 'TRF-20260524-1312', 'adult', 'Essya LAOUINI', 2, '2026-05-24 09:52:54'),
(1827, 389, 'TRF-20260524-4103', 'adult', 'GEORG SCHNEIDER', 0, '2026-05-24 10:19:47'),
(1828, 389, 'TRF-20260524-4103', 'adult', 'ELISABETH ROETHLIN', 1, '2026-05-24 10:19:47'),
(1829, 390, 'TRF-20260524-8183', 'adult', 'GEORG SCHNEIDER', 0, '2026-05-24 10:19:47'),
(1830, 390, 'TRF-20260524-8183', 'adult', 'ELISABETH ROETHLIN', 1, '2026-05-24 10:19:47'),
(1831, 385, 'TRF-20260524-5794', 'adult', 'SAMIRA HAGOS', 0, '2026-05-24 10:20:25'),
(1832, 385, 'TRF-20260524-5794', 'adult', 'MARIAM RAJO', 1, '2026-05-24 10:20:25'),
(1833, 386, 'TRF-20260524-8802', 'adult', 'SAMIRA HAGOS', 0, '2026-05-24 10:20:35'),
(1834, 386, 'TRF-20260524-8802', 'adult', 'MARIAM RAJO', 1, '2026-05-24 10:20:35'),
(1839, 387, 'TRF-20260524-6293', 'adult', 'DERYA FİDANCI', 0, '2026-05-24 11:48:15'),
(1840, 387, 'TRF-20260524-6293', 'adult', 'OĞUZ FİDANCI', 1, '2026-05-24 11:48:15'),
(1841, 387, 'TRF-20260524-6293', 'adult', 'SİMAY FİDANCI', 2, '2026-05-24 11:48:15'),
(1842, 387, 'TRF-20260524-6293', 'adult', 'TALAY FİDANCI', 3, '2026-05-24 11:48:15'),
(1857, 393, 'TRF-20260524-9868', 'adult', 'DINO DJULIC', 0, '2026-05-24 12:02:36'),
(1858, 393, 'TRF-20260524-9868', 'adult', 'PIA DJULIC', 1, '2026-05-24 12:02:36'),
(1859, 393, 'TRF-20260524-9868', 'adult', 'LIAS DJULIC', 2, '2026-05-24 12:02:36'),
(1860, 393, 'TRF-20260524-9868', 'adult', 'GORDAN JURIC', 3, '2026-05-24 12:02:36'),
(1861, 393, 'TRF-20260524-9868', 'adult', 'VERENA JURIC', 4, '2026-05-24 12:02:36'),
(1862, 393, 'TRF-20260524-9868', 'adult', 'SAMUEL JURIC', 5, '2026-05-24 12:02:36'),
(1863, 315, 'TRF-20260514-1124', 'adult', 'SERGEİ KUTERGİN', 0, '2026-05-24 12:12:36'),
(1864, 315, 'TRF-20260514-1124', 'adult', 'TAMARA KUTERGİNA', 1, '2026-05-24 12:12:36'),
(1865, 315, 'TRF-20260514-1124', 'adult', 'ALEXSANDR URBANOVITS', 2, '2026-05-24 12:12:36'),
(1866, 315, 'TRF-20260514-1124', 'adult', 'MARINA URBANOVITS', 3, '2026-05-24 12:12:36'),
(1867, 394, 'TRF-20260524-4035', 'adult', 'THOMAS MEYER', 0, '2026-05-24 12:21:12'),
(1868, 394, 'TRF-20260524-4035', 'adult', 'BESTE MEYER', 1, '2026-05-24 12:21:12'),
(1875, 395, 'TRF-20260524-3475', 'adult', 'DINO DJULIC', 0, '2026-05-24 12:29:50'),
(1876, 395, 'TRF-20260524-3475', 'adult', 'PIA DJULIC', 1, '2026-05-24 12:29:50'),
(1877, 395, 'TRF-20260524-3475', 'adult', 'LIAS DJULIC', 2, '2026-05-24 12:29:50'),
(1878, 395, 'TRF-20260524-3475', 'adult', 'GORDAN JURIC', 3, '2026-05-24 12:29:50'),
(1879, 395, 'TRF-20260524-3475', 'adult', 'VERENE JURIC', 4, '2026-05-24 12:29:50'),
(1880, 395, 'TRF-20260524-3475', 'adult', 'SAMUEL JURIC', 5, '2026-05-24 12:29:50'),
(1884, 396, 'TRF-20260524-4337', 'adult', 'ALAN MAJID HASAN', 0, '2026-05-24 12:37:38'),
(1885, 396, 'TRF-20260524-4337', 'adult', 'VAJIN JAMIL KHIDIR', 1, '2026-05-24 12:37:38'),
(1886, 396, 'TRF-20260524-4337', 'adult', 'NILLA ALAN MAJID', 2, '2026-05-24 12:37:38'),
(1891, 391, 'TRF-20260524-0061', 'adult', 'DIETMAR RESSEL', 0, '2026-05-24 12:39:39'),
(1892, 391, 'TRF-20260524-0061', 'adult', 'ANDRE RESSEL', 1, '2026-05-24 12:39:39'),
(1893, 391, 'TRF-20260524-0061', 'adult', 'JOSEF STOTZ', 2, '2026-05-24 12:39:39'),
(1894, 391, 'TRF-20260524-0061', 'adult', 'HELGA STOTZ', 3, '2026-05-24 12:39:39'),
(1895, 274, 'TRF-20260508-1514', 'adult', 'NADINE LÖFFLER', 0, '2026-05-24 12:57:13'),
(1896, 274, 'TRF-20260508-1514', 'adult', 'MARCUS LÖFFLER', 1, '2026-05-24 12:57:13'),
(1897, 274, 'TRF-20260508-1514', 'child', 'HANNES LÖFFLER', 0, '2026-05-24 12:57:13'),
(1898, 274, 'TRF-20260508-1514', 'child', 'MAX LÖFFLER', 1, '2026-05-24 12:57:13'),
(1915, 399, 'TRF-20260525-5959', 'adult', 'Patricia Matthews', 0, '2026-05-25 06:43:35'),
(1916, 399, 'TRF-20260525-5959', 'adult', 'Robert matthews', 1, '2026-05-25 06:43:35'),
(1917, 399, 'TRF-20260525-5959', 'adult', 'Millie matthews', 2, '2026-05-25 06:43:35'),
(1918, 399, 'TRF-20260525-5959', 'adult', 'Mason matthews', 3, '2026-05-25 06:43:35'),
(1919, 400, 'TRF-20260525-9182', 'adult', 'AHMAD SHWEHNEH', 0, '2026-05-25 07:22:57'),
(1920, 400, 'TRF-20260525-9182', 'adult', 'AYLA SHWEHNEH', 1, '2026-05-25 07:22:57'),
(1921, 400, 'TRF-20260525-9182', 'adult', 'ELİN SHWEHNEH', 2, '2026-05-25 07:22:57'),
(1922, 400, 'TRF-20260525-9182', 'adult', 'SOFİA ALLOUACHE', 3, '2026-05-25 07:22:57'),
(1925, 401, 'TRF-20260525-9152', 'adult', 'Nabil mechehrawi', 0, '2026-05-25 09:00:56'),
(1926, 401, 'TRF-20260525-9152', 'adult', 'Abir mechehrawi', 1, '2026-05-25 09:00:56'),
(1927, 156, 'TRF-20260504-6472', 'adult', 'GUIDO PESCHEL', 0, '2026-05-25 14:53:09'),
(1928, 156, 'TRF-20260504-6472', 'adult', 'ROSI PESCHEL', 1, '2026-05-25 14:53:09'),
(1929, 402, 'TRF-20260525-0684', 'adult', 'PATRICK BRUDER', 0, '2026-05-25 14:59:41'),
(1930, 402, 'TRF-20260525-0684', 'adult', 'Stéphanie Seyller', 1, '2026-05-25 14:59:41'),
(1931, 402, 'TRF-20260525-0684', 'adult', 'EVA BRUDER', 2, '2026-05-25 14:59:41'),
(1932, 402, 'TRF-20260525-0684', 'adult', 'GEORGE RIEBEIRO', 3, '2026-05-25 14:59:41'),
(1933, 402, 'TRF-20260525-0684', 'adult', 'CARINO RIEBERIO', 4, '2026-05-25 14:59:41'),
(1942, 143, 'TRF-20260503-2938', 'adult', 'MASIH PAIKARJO', 0, '2026-05-25 18:45:46'),
(1943, 143, 'TRF-20260503-2938', 'adult', 'KATHLEEN CANALES', 1, '2026-05-25 18:45:46'),
(1944, 143, 'TRF-20260503-2938', 'child', 'Arian Elias Canales Paikarjo', 0, '2026-05-25 18:45:46'),
(1945, 403, 'TRF-20260525-6663', 'adult', 'Merve khalaf', 0, '2026-05-25 19:31:06'),
(1946, 403, 'TRF-20260525-6663', 'adult', 'Mohamad khalaf', 1, '2026-05-25 19:31:06'),
(1949, 404, 'TRF-20260525-9288', 'adult', 'Merve khalaf', 0, '2026-05-25 19:32:49'),
(1950, 404, 'TRF-20260525-9288', 'adult', 'Mohamad khalaf', 1, '2026-05-25 19:32:49'),
(1951, 397, 'TRF-20260524-3859', 'adult', 'JESSICA NOORDMAN', 0, '2026-05-25 19:49:53'),
(1952, 397, 'TRF-20260524-3859', 'adult', 'PETER SJOERDSMA', 1, '2026-05-25 19:49:53'),
(1953, 397, 'TRF-20260524-3859', 'adult', 'INGE SJOERDSMA', 2, '2026-05-25 19:49:53'),
(1954, 397, 'TRF-20260524-3859', 'adult', 'LYNN SJOERDSMA', 3, '2026-05-25 19:49:53'),
(1955, 398, 'TRF-20260524-0452', 'adult', 'JESSICA NOORDMAN', 0, '2026-05-25 19:50:15'),
(1956, 398, 'TRF-20260524-0452', 'adult', 'PETER SJOERDSMA', 1, '2026-05-25 19:50:15'),
(1957, 398, 'TRF-20260524-0452', 'adult', 'INGE SJOERDSMA', 2, '2026-05-25 19:50:15'),
(1958, 398, 'TRF-20260524-0452', 'adult', 'LYNN SJOERDSMA', 3, '2026-05-25 19:50:15'),
(1959, 405, 'TRF-20260526-9529', 'adult', 'PATRICK VILLERIUS', 0, '2026-05-26 07:24:03'),
(1960, 405, 'TRF-20260526-9529', 'adult', 'Arkadiusz wieczorek', 1, '2026-05-26 07:24:03'),
(1961, 342, 'TRF-20260517-4122', 'adult', 'PETRA ANDROLEIT', 0, '2026-05-26 07:40:35'),
(1962, 275, 'TRF-20260508-1224', 'adult', 'NADINE LÖFFLER', 0, '2026-05-26 07:55:39'),
(1963, 275, 'TRF-20260508-1224', 'adult', 'MARCUS LÖFFLER', 1, '2026-05-26 07:55:39'),
(1964, 275, 'TRF-20260508-1224', 'child', 'HANNES LÖFFLER', 0, '2026-05-26 07:55:39'),
(1965, 275, 'TRF-20260508-1224', 'child', 'MAX LÖFFLER', 1, '2026-05-26 07:55:39'),
(1966, 406, 'TRF-20260526-1585', 'adult', 'JOY HANA', 0, '2026-05-26 10:35:01'),
(1967, 406, 'TRF-20260526-1585', 'adult', 'ALICE HANA', 1, '2026-05-26 10:35:01'),
(1968, 406, 'TRF-20260526-1585', 'adult', 'ELINA HANA', 2, '2026-05-26 10:35:01'),
(1969, 406, 'TRF-20260526-1585', 'adult', 'SOPHIE HANA', 3, '2026-05-26 10:35:01'),
(1970, 406, 'TRF-20260526-1585', 'adult', 'FEHMI OKTAY', 4, '2026-05-26 10:35:01'),
(1971, 406, 'TRF-20260526-1585', 'adult', 'LEMA OKTAY', 5, '2026-05-26 10:35:01'),
(1972, 406, 'TRF-20260526-1585', 'adult', 'SARA OKTAY', 6, '2026-05-26 10:35:01'),
(1991, 410, 'TRF-20260527-4773', 'adult', 'ALI GHOULAM', 0, '2026-05-27 11:18:01'),
(1992, 410, 'TRF-20260527-4773', 'adult', 'BROOKE LAING', 1, '2026-05-27 11:18:01'),
(1995, 411, 'TRF-20260527-8380', 'adult', 'ALI GHOULAM', 0, '2026-05-27 11:19:33'),
(1996, 411, 'TRF-20260527-8380', 'adult', 'BROOKE LAING', 1, '2026-05-27 11:19:33'),
(1997, 412, 'TRF-20260527-5075', 'adult', 'SAMIRA MOUKEFFES', 0, '2026-05-27 16:53:13'),
(1998, 413, 'TRF-20260527-5566', 'adult', 'SAMIRA MOUKEFFES', 0, '2026-05-27 16:53:13'),
(1999, 414, 'TRF-20260527-3449', 'adult', 'JORDAN MANLEY', 0, '2026-05-27 16:57:48'),
(2000, 414, 'TRF-20260527-3449', 'adult', 'MACI MAYO', 1, '2026-05-27 16:57:48'),
(2001, 415, 'TRF-20260527-1405', 'adult', 'SİNAN BAK', 0, '2026-05-27 19:10:46'),
(2002, 415, 'TRF-20260527-1405', 'adult', 'BETÜL BAK', 1, '2026-05-27 19:10:46'),
(2003, 415, 'TRF-20260527-1405', 'child', 'ERAY SAİD BAK', 0, '2026-05-27 19:10:46'),
(2004, 415, 'TRF-20260527-1405', 'child', 'ENSAR AZİZ', 1, '2026-05-27 19:10:46'),
(2009, 353, 'TRF-20260518-8610', 'adult', 'Gülsün yaşar', 0, '2026-05-27 19:16:25'),
(2010, 353, 'TRF-20260518-8610', 'adult', 'Ramazan sarpkaya', 1, '2026-05-27 19:16:25'),
(2011, 353, 'TRF-20260518-8610', 'adult', 'Adem sarpkaya', 2, '2026-05-27 19:16:25'),
(2012, 353, 'TRF-20260518-8610', 'adult', 'Elif sarpkaya', 3, '2026-05-27 19:16:25'),
(2017, 181, 'TRF-20260505-2678', 'adult', 'UFUK CÖMERT', 0, '2026-05-28 12:09:21'),
(2018, 181, 'TRF-20260505-2678', 'adult', 'BAHAR CÖMERT', 1, '2026-05-28 12:09:21'),
(2019, 181, 'TRF-20260505-2678', 'child', 'NEHİR CÖMERT', 0, '2026-05-28 12:09:21'),
(2020, 181, 'TRF-20260505-2678', 'child', 'FİLİZ CÖMERT', 1, '2026-05-28 12:09:21'),
(2021, 129, 'TRF-20260503-8421', 'adult', 'STEVEN HANLEY', 0, '2026-05-28 14:44:47'),
(2022, 129, 'TRF-20260503-8421', 'adult', 'STEPHANIE BROOKSBANK', 1, '2026-05-28 14:44:47'),
(2023, 129, 'TRF-20260503-8421', 'adult', 'NOAH HANLEY', 2, '2026-05-28 14:44:47'),
(2024, 129, 'TRF-20260503-8421', 'adult', 'MIA HANLEY', 3, '2026-05-28 14:44:47'),
(2025, 327, 'TRF-20260516-7126', 'adult', 'SIMON STAIBER', 0, '2026-05-28 15:12:31'),
(2026, 327, 'TRF-20260516-7126', 'adult', 'KATRIN STAIBER', 1, '2026-05-28 15:12:31'),
(2027, 327, 'TRF-20260516-7126', 'adult', 'MAXIMILLAN STAIBER', 2, '2026-05-28 15:12:31'),
(2028, 327, 'TRF-20260516-7126', 'adult', 'ANDREAS ANTONI', 3, '2026-05-28 15:12:31'),
(2029, 327, 'TRF-20260516-7126', 'adult', 'OLGA ANTONI', 4, '2026-05-28 15:12:31'),
(2030, 327, 'TRF-20260516-7126', 'adult', 'EMILIA ANTONI', 5, '2026-05-28 15:12:31'),
(2031, 327, 'TRF-20260516-7126', 'adult', 'MARIO ANTONI', 6, '2026-05-28 15:12:31'),
(2032, 388, 'TRF-20260524-0234', 'adult', 'DERYA FİDANCI', 0, '2026-05-28 17:54:10'),
(2033, 388, 'TRF-20260524-0234', 'adult', 'OĞUZ FİDANCI', 1, '2026-05-28 17:54:10'),
(2034, 388, 'TRF-20260524-0234', 'adult', 'TALAY FİDANCI', 2, '2026-05-28 17:54:10'),
(2035, 388, 'TRF-20260524-0234', 'adult', 'SİMAY FİDANCI', 3, '2026-05-28 17:54:10'),
(2041, 409, 'TRF-20260526-8819', 'adult', 'VALENTIN MOCH', 0, '2026-05-28 17:56:49'),
(2042, 409, 'TRF-20260526-8819', 'adult', 'ANASTASIA MOCH', 1, '2026-05-28 17:56:49'),
(2043, 409, 'TRF-20260526-8819', 'adult', 'DAMIAN MOCH', 2, '2026-05-28 17:56:49'),
(2044, 409, 'TRF-20260526-8819', 'adult', 'CHRISTINA HENSE', 3, '2026-05-28 17:56:49'),
(2045, 409, 'TRF-20260526-8819', 'adult', 'ARTHUR HENSE', 4, '2026-05-28 17:56:49'),
(2046, 408, 'TRF-20260526-0737', 'adult', 'VALENTIN MOCH', 0, '2026-05-28 17:56:57'),
(2047, 408, 'TRF-20260526-0737', 'adult', 'ANASTASIA MOCH', 1, '2026-05-28 17:56:57'),
(2048, 408, 'TRF-20260526-0737', 'adult', 'DAMIAN MOCH', 2, '2026-05-28 17:56:57'),
(2049, 408, 'TRF-20260526-0737', 'adult', 'CHRISTINA HENSE', 3, '2026-05-28 17:56:57'),
(2050, 408, 'TRF-20260526-0737', 'adult', 'ARTHUR HENSE', 4, '2026-05-28 17:56:57'),
(2051, 417, 'TRF-20260528-4856', 'adult', 'PHILLIP GARNHAM', 0, '2026-05-28 17:59:36'),
(2052, 417, 'TRF-20260528-4856', 'adult', 'SHERLY GARNHAM', 1, '2026-05-28 17:59:36'),
(2053, 418, 'TRF-20260528-1489', 'adult', 'ÖZCAN KOCAMAN', 0, '2026-05-28 18:06:24'),
(2054, 418, 'TRF-20260528-1489', 'adult', 'RADMILA KOCAMAN', 1, '2026-05-28 18:06:24'),
(2055, 418, 'TRF-20260528-1489', 'child', 'LIMA AVA KOCAMAN', 0, '2026-05-28 18:06:24'),
(2056, 418, 'TRF-20260528-1489', 'child', 'NISA SU KOCAMAN', 1, '2026-05-28 18:06:24'),
(2057, 419, 'TRF-20260528-2894', 'adult', 'ÖZCAN KOCAMAN', 0, '2026-05-28 18:06:24'),
(2058, 419, 'TRF-20260528-2894', 'adult', 'RADMILA KOCAMAN', 1, '2026-05-28 18:06:24'),
(2059, 419, 'TRF-20260528-2894', 'child', 'LIMA AVA KOCAMAN', 0, '2026-05-28 18:06:24'),
(2060, 419, 'TRF-20260528-2894', 'child', 'NISA SU KOCAMAN', 1, '2026-05-28 18:06:24'),
(2061, 420, 'TRF-20260528-3385', 'adult', 'CEMAL YILDIRIM', 0, '2026-05-28 18:11:08'),
(2062, 420, 'TRF-20260528-3385', 'adult', 'AYŞE YILDIRIM', 1, '2026-05-28 18:11:08'),
(2066, 422, 'TRF-20260528-5482', 'adult', 'VIKTOR MELENBERG', 0, '2026-05-28 18:16:26'),
(2067, 296, 'TRF-20260510-7475', 'adult', 'RALF RIEGER', 0, '2026-05-28 18:28:25'),
(2068, 423, 'TRF-20260529-8037', 'adult', 'FATMA KOLUÇOLAK', 0, '2026-05-29 06:21:07'),
(2069, 423, 'TRF-20260529-8037', 'adult', 'GÖKÇE KOLUÇOLAK', 1, '2026-05-29 06:21:07'),
(2070, 423, 'TRF-20260529-8037', 'adult', 'ŞÜKRAN KOLUÇOLAK', 2, '2026-05-29 06:21:07'),
(2071, 407, 'TRF-20260526-3408', 'adult', 'MERVE KHALAF', 0, '2026-05-29 08:00:17'),
(2072, 407, 'TRF-20260526-3408', 'adult', 'MOHAMAD KHALAF', 1, '2026-05-29 08:00:17'),
(2073, 79, 'TRF-20260502-0089', 'adult', 'MUDASSIR MANSHA', 0, '2026-05-29 10:40:35'),
(2074, 79, 'TRF-20260502-0089', 'adult', 'ZULAIKHA BIBI', 1, '2026-05-29 10:40:35'),
(2075, 79, 'TRF-20260502-0089', 'child', 'AYAT NOOR', 0, '2026-05-29 10:40:35'),
(2082, 424, 'TRF-20260529-5136', 'adult', 'NATALIJA PETROVA', 0, '2026-05-29 11:14:21'),
(2083, 424, 'TRF-20260529-5136', 'adult', 'VALERIJA MUGAMAE', 1, '2026-05-29 11:14:21'),
(2084, 424, 'TRF-20260529-5136', 'adult', 'MILANA MUGAMAE', 2, '2026-05-29 11:14:21'),
(2085, 424, 'TRF-20260529-5136', 'adult', 'DENISS SOKOLOV', 3, '2026-05-29 11:14:21'),
(2086, 424, 'TRF-20260529-5136', 'adult', 'MARIA SOKOLOVA', 4, '2026-05-29 11:14:21'),
(2087, 424, 'TRF-20260529-5136', 'adult', 'AMINA KAHHETELIDZE', 5, '2026-05-29 11:14:21'),
(2091, 426, 'TRF-20260529-4338', 'adult', 'TIFANNY NHAN', 0, '2026-05-29 12:25:11'),
(2092, 426, 'TRF-20260529-4338', 'adult', 'MICHELLE NHAN', 1, '2026-05-29 12:25:11'),
(2093, 426, 'TRF-20260529-4338', 'adult', 'NGOC THI LAM', 2, '2026-05-29 12:25:11'),
(2094, 426, 'TRF-20260529-4338', 'adult', 'CUONG NHAM', 3, '2026-05-29 12:25:11'),
(2099, 427, 'TRF-20260529-1793', 'adult', 'TIFANNY NHAN', 0, '2026-05-29 13:07:52'),
(2100, 427, 'TRF-20260529-1793', 'adult', 'MICHELLE NHAN', 1, '2026-05-29 13:07:52'),
(2101, 427, 'TRF-20260529-1793', 'adult', 'NGOC THI NHAN', 2, '2026-05-29 13:07:52'),
(2102, 427, 'TRF-20260529-1793', 'adult', 'CUONG NHAN', 3, '2026-05-29 13:07:52'),
(2103, 428, 'TRF-20260530-1822', 'adult', 'Fatima Cheaito', 0, '2026-05-29 21:02:44'),
(2104, 428, 'TRF-20260530-1822', 'adult', 'Hadi El Chab', 1, '2026-05-29 21:02:44'),
(2105, 428, 'TRF-20260530-1822', 'adult', 'Ali El Chab', 2, '2026-05-29 21:02:44'),
(2106, 428, 'TRF-20260530-1822', 'adult', 'Mona Cheaito', 3, '2026-05-29 21:02:44'),
(2107, 428, 'TRF-20260530-1822', 'adult', 'Nassima Cheaito', 4, '2026-05-29 21:02:44'),
(2108, 428, 'TRF-20260530-1822', 'adult', 'Youssef Baalbaki', 5, '2026-05-29 21:02:44'),
(2109, 428, 'TRF-20260530-1822', 'adult', 'Fatima Baalbaki', 6, '2026-05-29 21:02:44'),
(2118, 429, 'TRF-20260530-4926', 'adult', 'MARK MEADER', 0, '2026-05-29 21:10:25'),
(2119, 429, 'TRF-20260530-4926', 'adult', 'HOLLY MEADER', 1, '2026-05-29 21:10:25'),
(2120, 429, 'TRF-20260530-4926', 'adult', 'MAE MEADER', 2, '2026-05-29 21:10:25'),
(2121, 429, 'TRF-20260530-4926', 'adult', 'PRIMROSE MEADER', 3, '2026-05-29 21:10:25'),
(2122, 430, 'TRF-20260530-9487', 'adult', 'MARK MEADER', 0, '2026-05-29 21:10:33'),
(2123, 430, 'TRF-20260530-9487', 'adult', 'HOLLY MEADER', 1, '2026-05-29 21:10:33'),
(2124, 430, 'TRF-20260530-9487', 'adult', 'MAE MEADER', 2, '2026-05-29 21:10:33'),
(2125, 430, 'TRF-20260530-9487', 'adult', 'PRIMROSE MEADER', 3, '2026-05-29 21:10:33'),
(2132, 431, 'TRF-20260530-2402', 'adult', 'DANNY HENNEKES', 0, '2026-05-30 09:44:03'),
(2133, 431, 'TRF-20260530-2402', 'adult', 'MANDY HENNEKES', 1, '2026-05-30 09:44:03'),
(2134, 431, 'TRF-20260530-2402', 'adult', 'SEM HENNEKES', 2, '2026-05-30 09:44:03'),
(2135, 431, 'TRF-20260530-2402', 'adult', 'REMON HOL', 3, '2026-05-30 09:44:03'),
(2136, 431, 'TRF-20260530-2402', 'adult', 'KARIN HOL', 4, '2026-05-30 09:44:03'),
(2137, 431, 'TRF-20260530-2402', 'adult', 'MEVI HOL', 5, '2026-05-30 09:44:03'),
(2140, 432, 'TRF-20260530-7185', 'adult', 'DEBBY VERVLOET', 0, '2026-05-30 19:36:23'),
(2141, 432, 'TRF-20260530-7185', 'adult', 'Sergen thill', 1, '2026-05-30 19:36:23'),
(2142, 433, 'TRF-20260531-2500', 'adult', 'JULIA HAUBENREISSER', 0, '2026-05-31 16:08:38'),
(2143, 433, 'TRF-20260531-2500', 'adult', 'Lukas Haubenreisser', 1, '2026-05-31 16:08:38'),
(2144, 433, 'TRF-20260531-2500', 'adult', 'SVEN UHLE', 2, '2026-05-31 16:08:38'),
(2145, 434, 'TRF-20260531-1646', 'adult', 'ALEXANDER FELGER', 0, '2026-05-31 16:15:49'),
(2146, 434, 'TRF-20260531-1646', 'adult', 'LILI FELGER', 1, '2026-05-31 16:15:49'),
(2147, 434, 'TRF-20260531-1646', 'adult', 'ALEX TSCHASCHKOW', 2, '2026-05-31 16:15:49'),
(2148, 434, 'TRF-20260531-1646', 'adult', 'KATHARINA TSCHASCHKOW', 3, '2026-05-31 16:15:49'),
(2149, 435, 'TRF-20260531-1846', 'adult', 'ALEXANDER FELGER', 0, '2026-05-31 16:15:49'),
(2150, 435, 'TRF-20260531-1846', 'adult', 'LILI FELGER', 1, '2026-05-31 16:15:49'),
(2151, 435, 'TRF-20260531-1846', 'adult', 'ALEX TSCHASCHKOW', 2, '2026-05-31 16:15:49'),
(2152, 435, 'TRF-20260531-1846', 'adult', 'KATHARINA TSCHASCHKOW', 3, '2026-05-31 16:15:49'),
(2169, 438, 'TRF-20260531-7002', 'adult', 'STEFAN HALLAUER', 0, '2026-05-31 16:27:22'),
(2170, 438, 'TRF-20260531-7002', 'adult', 'MANTY HALLAUER', 1, '2026-05-31 16:27:22'),
(2171, 438, 'TRF-20260531-7002', 'adult', 'SOPHIE HALLAUER', 2, '2026-05-31 16:27:22'),
(2172, 439, 'TRF-20260531-4944', 'adult', 'STEFAN HALLAUER', 0, '2026-05-31 16:27:22'),
(2173, 439, 'TRF-20260531-4944', 'adult', 'MANTY HALLAUER', 1, '2026-05-31 16:27:22'),
(2174, 439, 'TRF-20260531-4944', 'adult', 'SOPHIE HALLAUER', 2, '2026-05-31 16:27:22'),
(2183, 441, 'TRF-20260531-1677', 'adult', 'Jewgeni Riedel', 0, '2026-05-31 16:35:23'),
(2184, 441, 'TRF-20260531-1677', 'adult', 'Anita Block', 1, '2026-05-31 16:35:23'),
(2185, 441, 'TRF-20260531-1677', 'adult', 'Petra Block', 2, '2026-05-31 16:35:23'),
(2186, 441, 'TRF-20260531-1677', 'adult', 'Luisa Riedel', 3, '2026-05-31 16:35:23'),
(2187, 441, 'TRF-20260531-1677', 'adult', 'Lea Riedel', 4, '2026-05-31 16:35:23'),
(2188, 442, 'TRF-20260531-4150', 'adult', 'Jewgeni Riedel', 0, '2026-05-31 16:35:23'),
(2189, 442, 'TRF-20260531-4150', 'adult', 'Anita Block', 1, '2026-05-31 16:35:23'),
(2190, 442, 'TRF-20260531-4150', 'adult', 'Petra Block', 2, '2026-05-31 16:35:23'),
(2191, 442, 'TRF-20260531-4150', 'adult', 'Luisa Riedel', 3, '2026-05-31 16:35:23'),
(2192, 442, 'TRF-20260531-4150', 'adult', 'Lea Riedel', 4, '2026-05-31 16:35:23'),
(2193, 443, 'TRF-20260531-2042', 'adult', 'CEREN UYGUT', 0, '2026-05-31 16:44:14'),
(2194, 443, 'TRF-20260531-2042', 'adult', 'SILAN UYGUT', 1, '2026-05-31 16:44:14'),
(2195, 443, 'TRF-20260531-2042', 'adult', 'ISMAIL UYGUT', 2, '2026-05-31 16:44:14'),
(2196, 443, 'TRF-20260531-2042', 'adult', 'SELVER UYGUT', 3, '2026-05-31 16:44:14'),
(2197, 444, 'TRF-20260531-7867', 'adult', 'CEREN UYGUT', 0, '2026-05-31 16:44:14'),
(2198, 444, 'TRF-20260531-7867', 'adult', 'SILAN UYGUT', 1, '2026-05-31 16:44:14'),
(2199, 444, 'TRF-20260531-7867', 'adult', 'ISMAIL UYGUT', 2, '2026-05-31 16:44:14'),
(2200, 444, 'TRF-20260531-7867', 'adult', 'SELVER UYGUT', 3, '2026-05-31 16:44:14'),
(2201, 362, 'TRF-20260519-1913', 'adult', 'MELISSA CHARMELY', 0, '2026-05-31 16:46:29'),
(2202, 362, 'TRF-20260519-1913', 'adult', 'Philip Thomas', 1, '2026-05-31 16:46:29'),
(2203, 362, 'TRF-20260519-1913', 'adult', 'Theo Thomas', 2, '2026-05-31 16:46:29'),
(2204, 362, 'TRF-20260519-1913', 'adult', 'Tate Thomas', 3, '2026-05-31 16:46:29'),
(2205, 436, 'TRF-20260531-4927', 'adult', 'ZERIN CATIC', 0, '2026-05-31 18:51:54'),
(2206, 436, 'TRF-20260531-4927', 'adult', 'BELMINA CATIC', 1, '2026-05-31 18:51:54'),
(2207, 436, 'TRF-20260531-4927', 'adult', 'HAMZA CATIC', 2, '2026-05-31 18:51:54'),
(2208, 436, 'TRF-20260531-4927', 'adult', 'SENADIN KULASIC', 3, '2026-05-31 18:51:54'),
(2209, 436, 'TRF-20260531-4927', 'adult', 'SELMA KULASIC', 4, '2026-05-31 18:51:54'),
(2210, 436, 'TRF-20260531-4927', 'adult', 'MALIK KULASIC', 5, '2026-05-31 18:51:54'),
(2211, 436, 'TRF-20260531-4927', 'adult', 'MELVIN KULASIC', 6, '2026-05-31 18:51:54'),
(2212, 436, 'TRF-20260531-4927', 'adult', 'FATIMA CATIC', 7, '2026-05-31 18:51:54'),
(2213, 437, 'TRF-20260531-4462', 'adult', 'ZERIN CATIC', 0, '2026-05-31 18:52:07'),
(2214, 437, 'TRF-20260531-4462', 'adult', 'BELMINA CATIC', 1, '2026-05-31 18:52:07');
INSERT INTO `booking_passengers` (`id`, `booking_id`, `booking_number`, `passenger_type`, `full_name`, `sort_order`, `created_at`) VALUES
(2215, 437, 'TRF-20260531-4462', 'adult', 'HAMZA CATIC', 2, '2026-05-31 18:52:07'),
(2216, 437, 'TRF-20260531-4462', 'adult', 'SENADIN KULASIC', 3, '2026-05-31 18:52:07'),
(2217, 437, 'TRF-20260531-4462', 'adult', 'SELMA KULASIC', 4, '2026-05-31 18:52:07'),
(2218, 437, 'TRF-20260531-4462', 'adult', 'MALIK KULASIC', 5, '2026-05-31 18:52:07'),
(2219, 437, 'TRF-20260531-4462', 'adult', 'MELVIN KULASIC', 6, '2026-05-31 18:52:07'),
(2220, 437, 'TRF-20260531-4462', 'adult', 'FATIMA CATIC', 7, '2026-05-31 18:52:07'),
(2224, 445, 'TRF-20260531-1143', 'adult', 'Ovidiu Remus Chelaru', 0, '2026-05-31 19:21:20'),
(2225, 445, 'TRF-20260531-1143', 'adult', 'Gabriela Simona Chelaru', 1, '2026-05-31 19:21:20'),
(2226, 445, 'TRF-20260531-1143', 'adult', 'David Andrei Chelaru', 2, '2026-05-31 19:21:20'),
(2227, 446, 'TRF-20260531-3721', 'adult', 'BEN LEE', 0, '2026-05-31 19:35:56'),
(2228, 446, 'TRF-20260531-3721', 'adult', 'NICOLE BUSHELL', 1, '2026-05-31 19:35:56'),
(2229, 446, 'TRF-20260531-3721', 'adult', 'TATA LEE', 2, '2026-05-31 19:35:56'),
(2230, 446, 'TRF-20260531-3721', 'adult', 'CHLOE LEE', 3, '2026-05-31 19:35:56'),
(2231, 447, 'TRF-20260531-3472', 'adult', 'Valdrin Shabani', 0, '2026-05-31 19:51:44'),
(2232, 447, 'TRF-20260531-3472', 'adult', 'Sheqipdona ShabaniTosuni', 1, '2026-05-31 19:51:44'),
(2233, 447, 'TRF-20260531-3472', 'adult', 'Leyla Shabani', 2, '2026-05-31 19:51:44'),
(2234, 448, 'TRF-20260531-8273', 'adult', 'Valdrin Shabani', 0, '2026-05-31 19:51:44'),
(2235, 448, 'TRF-20260531-8273', 'adult', 'Sheqipdona ShabaniTosuni', 1, '2026-05-31 19:51:44'),
(2236, 448, 'TRF-20260531-8273', 'adult', 'Leyla Shabani', 2, '2026-05-31 19:51:44'),
(2245, 330, 'TRF-20260516-2314', 'adult', 'SARAH HERWEG', 0, '2026-05-31 20:32:35'),
(2246, 330, 'TRF-20260516-2314', 'adult', 'MARVIN HERWEG', 1, '2026-05-31 20:32:35'),
(2247, 330, 'TRF-20260516-2314', 'adult', 'HELMU HERWEG', 2, '2026-05-31 20:32:35'),
(2248, 330, 'TRF-20260516-2314', 'adult', 'STEVEN HERWEG', 3, '2026-05-31 20:32:35'),
(2249, 330, 'TRF-20260516-2314', 'adult', 'NICK HERWEG', 4, '2026-05-31 20:32:35'),
(2250, 330, 'TRF-20260516-2314', 'adult', 'SANDY HERWEG', 5, '2026-05-31 20:32:35'),
(2251, 451, 'TRF-20260601-6091', 'adult', 'IVANA JURISIC', 0, '2026-06-01 11:32:49'),
(2252, 451, 'TRF-20260601-6091', 'adult', 'ROKO JURISIC', 1, '2026-06-01 11:32:49'),
(2253, 451, 'TRF-20260601-6091', 'adult', 'IZABEL JURISIC', 2, '2026-06-01 11:32:49'),
(2258, 453, 'TRF-20260601-5082', 'adult', 'TOBIAS ISENSCHMID', 0, '2026-06-01 11:40:27'),
(2259, 453, 'TRF-20260601-5082', 'adult', 'DELIA WIDMER', 1, '2026-06-01 11:40:27'),
(2260, 454, 'TRF-20260601-7605', 'adult', 'TOBIAS ISENSCHMID', 0, '2026-06-01 11:40:27'),
(2261, 454, 'TRF-20260601-7605', 'adult', 'DELIA WIDMER', 1, '2026-06-01 11:40:27'),
(2262, 455, 'TRF-20260601-6471', 'adult', 'NAZARII VASIK', 0, '2026-06-01 11:43:20'),
(2263, 455, 'TRF-20260601-6471', 'adult', 'DARIA YURCHENKO', 1, '2026-06-01 11:43:20'),
(2265, 457, 'TRF-20260601-9807', 'adult', 'HERBEST BEER', 0, '2026-06-01 11:47:35'),
(2266, 458, 'TRF-20260601-7268', 'adult', 'KLAUS DIETER BACZYK', 0, '2026-06-01 11:49:14'),
(2267, 459, 'TRF-20260601-0078', 'adult', 'KLAUS DIETER BACZYK', 0, '2026-06-01 11:49:14'),
(2268, 460, 'TRF-20260601-2991', 'adult', 'DJORDY HENDRIKS', 0, '2026-06-01 11:51:30'),
(2269, 460, 'TRF-20260601-2991', 'adult', 'JOR KERSTEN', 1, '2026-06-01 11:51:30'),
(2270, 460, 'TRF-20260601-2991', 'child', 'JAZZY HENDRIKS', 0, '2026-06-01 11:51:30'),
(2271, 461, 'TRF-20260601-9567', 'adult', 'ARON FURER', 0, '2026-06-01 14:36:05'),
(2272, 461, 'TRF-20260601-9567', 'adult', 'LIUQMILA FURER', 1, '2026-06-01 14:36:05'),
(2273, 456, 'TRF-20260601-0813', 'adult', 'HERBEST BEER', 0, '2026-06-01 14:40:10'),
(2274, 462, 'TRF-20260601-6467', 'adult', 'NATALIE MILDE', 0, '2026-06-01 14:48:12'),
(2275, 462, 'TRF-20260601-6467', 'adult', 'VITALIJ MILDE', 1, '2026-06-01 14:48:12'),
(2276, 462, 'TRF-20260601-6467', 'adult', 'LEONEL MILDE', 2, '2026-06-01 14:48:12'),
(2277, 462, 'TRF-20260601-6467', 'adult', 'MAXIM MILDE', 3, '2026-06-01 14:48:12'),
(2278, 462, 'TRF-20260601-6467', 'adult', 'VIKTORIE MILDE', 4, '2026-06-01 14:48:12'),
(2279, 462, 'TRF-20260601-6467', 'adult', 'Nadezhda Domowez', 5, '2026-06-01 14:48:12'),
(2280, 462, 'TRF-20260601-6467', 'adult', 'Vyacheslav Domowez', 6, '2026-06-01 14:48:12'),
(2281, 462, 'TRF-20260601-6467', 'adult', 'Evgeniy Domowez', 7, '2026-06-01 14:48:12'),
(2282, 462, 'TRF-20260601-6467', 'adult', 'Miroslava Domowez', 8, '2026-06-01 14:48:12'),
(2283, 463, 'TRF-20260601-7361', 'adult', 'NATALIE MILDE', 0, '2026-06-01 14:48:12'),
(2284, 463, 'TRF-20260601-7361', 'adult', 'VITALIJ MILDE', 1, '2026-06-01 14:48:12'),
(2285, 463, 'TRF-20260601-7361', 'adult', 'LEONEL MILDE', 2, '2026-06-01 14:48:12'),
(2286, 463, 'TRF-20260601-7361', 'adult', 'MAXIM MILDE', 3, '2026-06-01 14:48:12'),
(2287, 463, 'TRF-20260601-7361', 'adult', 'VIKTORIE MILDE', 4, '2026-06-01 14:48:12'),
(2288, 463, 'TRF-20260601-7361', 'adult', 'Nadezhda Domowez', 5, '2026-06-01 14:48:12'),
(2289, 463, 'TRF-20260601-7361', 'adult', 'Vyacheslav Domowez', 6, '2026-06-01 14:48:12'),
(2290, 463, 'TRF-20260601-7361', 'adult', 'Evgeniy Domowez', 7, '2026-06-01 14:48:12'),
(2291, 463, 'TRF-20260601-7361', 'adult', 'Miroslava Domowez', 8, '2026-06-01 14:48:12'),
(2292, 464, 'TRF-20260601-4180', 'adult', 'HASAN MAJID', 0, '2026-06-01 19:11:56'),
(2293, 464, 'TRF-20260601-4180', 'adult', 'VAJIN JAMIL KHIDIR', 1, '2026-06-01 19:11:56'),
(2294, 464, 'TRF-20260601-4180', 'adult', 'NILA ALAN MAJID', 2, '2026-06-01 19:11:56'),
(2299, 465, 'TRF-20260602-3040', 'adult', 'CHRISTIAN MANZL', 0, '2026-06-01 21:29:06'),
(2300, 465, 'TRF-20260602-3040', 'adult', 'CHRISTIANA MANZL', 1, '2026-06-01 21:29:06'),
(2301, 465, 'TRF-20260602-3040', 'adult', 'ANNA MANZL', 2, '2026-06-01 21:29:06'),
(2302, 466, 'TRF-20260602-9830', 'adult', 'CHRISTIAN MANZL', 0, '2026-06-01 21:29:06'),
(2303, 466, 'TRF-20260602-9830', 'adult', 'CHRISTIANA MANZL', 1, '2026-06-01 21:29:06'),
(2304, 466, 'TRF-20260602-9830', 'adult', 'ANNA MANZL', 2, '2026-06-01 21:29:06'),
(2318, 192, 'TRF-20260505-2085', 'adult', 'BERKAY KALAYCI', 0, '2026-06-02 08:50:52'),
(2319, 192, 'TRF-20260505-2085', 'adult', 'ANDREJ MANEV', 1, '2026-06-02 08:50:52'),
(2320, 192, 'TRF-20260505-2085', 'adult', 'IRMAK ZEYNEP GÜNEY', 2, '2026-06-02 08:50:52'),
(2321, 192, 'TRF-20260505-2085', 'adult', 'YASMİN MAKAUI', 3, '2026-06-02 08:50:52'),
(2322, 467, 'TRF-20260602-0334', 'adult', 'OLGA WAGNER', 0, '2026-06-02 08:51:33'),
(2323, 467, 'TRF-20260602-0334', 'adult', 'DENIS WAGNER', 1, '2026-06-02 08:51:33'),
(2324, 467, 'TRF-20260602-0334', 'adult', 'ALEXANDER WAGNER', 2, '2026-06-02 08:51:33'),
(2325, 467, 'TRF-20260602-0334', 'adult', 'VALENTIN WAGNER', 3, '2026-06-02 08:51:33'),
(2326, 467, 'TRF-20260602-0334', 'adult', 'VALERIA WAGNER', 4, '2026-06-02 08:51:33'),
(2327, 468, 'TRF-20260602-8040', 'adult', 'OLGA WAGNER', 0, '2026-06-02 08:52:05'),
(2328, 468, 'TRF-20260602-8040', 'adult', 'DENIS WAGNER', 1, '2026-06-02 08:52:05'),
(2329, 468, 'TRF-20260602-8040', 'adult', 'ALEXANDER WAGNER', 2, '2026-06-02 08:52:05'),
(2330, 468, 'TRF-20260602-8040', 'adult', 'VALENTIN WAGNER', 3, '2026-06-02 08:52:05'),
(2331, 468, 'TRF-20260602-8040', 'adult', 'VALERIA WAGNER', 4, '2026-06-02 08:52:05'),
(2332, 178, 'TRF-20260505-2216', 'adult', 'Anke Mühlstädt', 0, '2026-06-02 08:59:15'),
(2333, 178, 'TRF-20260505-2216', 'adult', 'Peter Mühlstädt', 1, '2026-06-02 08:59:15'),
(2334, 178, 'TRF-20260505-2216', 'adult', 'Gerhard Bömsch', 2, '2026-06-02 08:59:15'),
(2335, 470, 'TRF-20260602-2512', 'adult', 'BRENDON SHILTON', 0, '2026-06-02 09:03:38'),
(2336, 470, 'TRF-20260602-2512', 'adult', 'JESSICA BENNET', 1, '2026-06-02 09:03:38'),
(2339, 472, 'TRF-20260602-9305', 'adult', 'GARY ROGERSON', 0, '2026-06-02 09:12:07'),
(2340, 472, 'TRF-20260602-9305', 'adult', 'SARAH ROGERSON', 1, '2026-06-02 09:12:07'),
(2341, 473, 'TRF-20260602-1442', 'adult', 'FILIP FILIPAK', 0, '2026-06-02 09:28:37'),
(2342, 473, 'TRF-20260602-1442', 'adult', 'BARBORA VLACKOVA', 1, '2026-06-02 09:28:37'),
(2343, 449, 'TRF-20260531-0803', 'adult', 'LIRIDON ZUKA', 0, '2026-06-02 10:10:25'),
(2344, 449, 'TRF-20260531-0803', 'adult', 'BESIJANA ZUKA', 1, '2026-06-02 10:10:25'),
(2345, 449, 'TRF-20260531-0803', 'adult', 'ROI ZUKA', 2, '2026-06-02 10:10:25'),
(2346, 449, 'TRF-20260531-0803', 'adult', 'MEARA ZUKA', 3, '2026-06-02 10:10:25'),
(2347, 450, 'TRF-20260531-1635', 'adult', 'LIRIDON ZUKA', 0, '2026-06-02 10:11:06'),
(2348, 450, 'TRF-20260531-1635', 'adult', 'BESIJANA ZUKA', 1, '2026-06-02 10:11:06'),
(2349, 450, 'TRF-20260531-1635', 'adult', 'ROI ZUKA', 2, '2026-06-02 10:11:06'),
(2350, 450, 'TRF-20260531-1635', 'adult', 'MEARA ZUKA', 3, '2026-06-02 10:11:06'),
(2351, 474, 'TRF-20260602-6258', 'adult', 'FAUIZA AHMED', 0, '2026-06-02 12:13:10'),
(2352, 474, 'TRF-20260602-6258', 'adult', 'RAHIL ABEDIN', 1, '2026-06-02 12:13:10'),
(2353, 474, 'TRF-20260602-6258', 'adult', 'MUSA ABEDIN', 2, '2026-06-02 12:13:10'),
(2354, 475, 'TRF-20260602-8627', 'adult', 'VALON VELUI', 0, '2026-06-02 13:42:43'),
(2355, 475, 'TRF-20260602-8627', 'adult', 'JOANA BRÜCK', 1, '2026-06-02 13:42:43'),
(2356, 475, 'TRF-20260602-8627', 'adult', 'LORIK BRÜCK', 2, '2026-06-02 13:42:43'),
(2357, 476, 'TRF-20260602-0941', 'adult', 'VALON VELUI', 0, '2026-06-02 13:42:43'),
(2358, 476, 'TRF-20260602-0941', 'adult', 'JOANA BRÜCK', 1, '2026-06-02 13:42:43'),
(2359, 476, 'TRF-20260602-0941', 'adult', 'LORIK BRÜCK', 2, '2026-06-02 13:42:43'),
(2360, 477, 'TRF-20260602-9413', 'adult', 'AJDIN TOROMONOVIC', 0, '2026-06-02 19:15:54'),
(2361, 477, 'TRF-20260602-9413', 'adult', 'ERMINA AVDIC TOROMONOVIC', 1, '2026-06-02 19:15:54'),
(2362, 477, 'TRF-20260602-9413', 'adult', 'KENAN TOROMONOVIC', 2, '2026-06-02 19:15:54'),
(2363, 477, 'TRF-20260602-9413', 'adult', 'ALICIA GANTRICH', 3, '2026-06-02 19:15:54'),
(2364, 478, 'TRF-20260602-1646', 'adult', 'BANU SHEIKH', 0, '2026-06-02 19:20:02'),
(2365, 478, 'TRF-20260602-1646', 'adult', 'JUNAID BOBAT', 1, '2026-06-02 19:20:02'),
(2366, 478, 'TRF-20260602-1646', 'adult', 'YAHYA BOBAT', 2, '2026-06-02 19:20:02'),
(2367, 478, 'TRF-20260602-1646', 'adult', 'AYESHA BOBAT', 3, '2026-06-02 19:20:02'),
(2368, 479, 'TRF-20260602-7527', 'adult', 'BANU SHEIKH', 0, '2026-06-02 19:20:02'),
(2369, 479, 'TRF-20260602-7527', 'adult', 'JUNAID BOBAT', 1, '2026-06-02 19:20:02'),
(2370, 479, 'TRF-20260602-7527', 'adult', 'YAHYA BOBAT', 2, '2026-06-02 19:20:02'),
(2371, 479, 'TRF-20260602-7527', 'adult', 'AYESHA BOBAT', 3, '2026-06-02 19:20:02'),
(2375, 480, 'TRF-20260603-1987', 'adult', 'GLENN BOERA SOBA', 0, '2026-06-03 06:40:55'),
(2376, 480, 'TRF-20260603-1987', 'adult', 'MELISSA BOERA SOBA', 1, '2026-06-03 06:40:55'),
(2377, 481, 'TRF-20260603-7539', 'adult', 'GLENN BOERA SOBA', 0, '2026-06-03 06:40:55'),
(2378, 481, 'TRF-20260603-7539', 'adult', 'MELISSA BOERA SOBA', 1, '2026-06-03 06:40:55'),
(2379, 482, 'TRF-20260603-6370', 'adult', 'MUSTAFA KAÇMAZ', 0, '2026-06-03 06:45:12'),
(2380, 482, 'TRF-20260603-6370', 'adult', 'JACQUELINE KAÇMAZ', 1, '2026-06-03 06:45:12'),
(2381, 483, 'TRF-20260603-6172', 'adult', 'MUSTAFA KAÇMAZ', 0, '2026-06-03 06:45:12'),
(2382, 483, 'TRF-20260603-6172', 'adult', 'JACQUELINE KAÇMAZ', 1, '2026-06-03 06:45:12'),
(2387, 183, 'TRF-20260505-2523', 'adult', 'MUHAMMAD WAQAS', 0, '2026-06-03 14:32:00'),
(2388, 183, 'TRF-20260505-2523', 'adult', 'IQRA MAHMOOD', 1, '2026-06-03 14:32:00'),
(2389, 485, 'TRF-20260603-5653', 'adult', 'ALİ GÖKDEMİR', 0, '2026-06-03 17:15:35'),
(2390, 485, 'TRF-20260603-5653', 'adult', 'BİRGÜL GÖKDEMİR', 1, '2026-06-03 17:15:35'),
(2391, 485, 'TRF-20260603-5653', 'child', 'HÜSEYİN EFE GÖKDEMİR', 0, '2026-06-03 17:15:35'),
(2392, 485, 'TRF-20260603-5653', 'child', 'OZAN GÖKDEMİR', 1, '2026-06-03 17:15:35'),
(2393, 486, 'TRF-20260603-8161', 'adult', 'MUHAMMAD QASIM', 0, '2026-06-03 17:22:46'),
(2394, 486, 'TRF-20260603-8161', 'adult', 'FIRDOUS QASIM', 1, '2026-06-03 17:22:46'),
(2395, 486, 'TRF-20260603-8161', 'adult', 'MUHAMMAD AYAAN', 2, '2026-06-03 17:22:46'),
(2396, 486, 'TRF-20260603-8161', 'adult', 'FATIMA QURESHI', 3, '2026-06-03 17:22:46'),
(2397, 487, 'TRF-20260603-2795', 'adult', 'TOMASZ LOSKA', 0, '2026-06-03 20:35:38'),
(2398, 487, 'TRF-20260603-2795', 'adult', 'ANNA SZYDLOWSKA', 1, '2026-06-03 20:35:38'),
(2399, 488, 'TRF-20260603-6489', 'adult', 'INA PAULSEN', 0, '2026-06-03 20:38:11'),
(2400, 488, 'TRF-20260603-6489', 'adult', 'MAXIM PAULSEN', 1, '2026-06-03 20:38:11'),
(2401, 488, 'TRF-20260603-6489', 'adult', 'MATWEJ PAULSEN', 2, '2026-06-03 20:38:11'),
(2402, 488, 'TRF-20260603-6489', 'adult', 'ATJEMI PAULSEN', 3, '2026-06-03 20:38:11'),
(2403, 489, 'TRF-20260603-1630', 'adult', 'ANGELIKA BÜHRING', 0, '2026-06-03 20:42:38'),
(2404, 489, 'TRF-20260603-1630', 'adult', 'ROSALIE SCHIRM', 1, '2026-06-03 20:42:38'),
(2405, 421, 'TRF-20260528-2496', 'adult', 'MARILENA BOEACA', 0, '2026-06-04 03:28:02'),
(2406, 421, 'TRF-20260528-2496', 'adult', 'STEGARU ALICE', 1, '2026-06-04 03:28:02'),
(2407, 421, 'TRF-20260528-2496', 'adult', 'CHIRITA DIANA', 2, '2026-06-04 03:28:02'),
(2408, 490, 'TRF-20260604-2663', 'adult', 'MANUEL KRAUSSER', 0, '2026-06-04 05:16:34'),
(2409, 490, 'TRF-20260604-2663', 'adult', 'MANUELA KRAUSSER', 1, '2026-06-04 05:16:34'),
(2410, 490, 'TRF-20260604-2663', 'adult', 'JULIAN KRAUSSER', 2, '2026-06-04 05:16:34'),
(2411, 490, 'TRF-20260604-2663', 'adult', 'JONAS KRAUSSER', 3, '2026-06-04 05:16:34'),
(2412, 425, 'TRF-20260529-9942', 'adult', 'JC VAN TIENEN', 0, '2026-06-04 05:44:48'),
(2413, 425, 'TRF-20260529-9942', 'adult', 'JENNIFER VAN ELST', 1, '2026-06-04 05:44:48'),
(2414, 425, 'TRF-20260529-9942', 'adult', 'JAELAY AMORA VAN TIENEN', 2, '2026-06-04 05:44:48'),
(2423, 484, 'TRF-20260603-2433', 'adult', 'WOLFGANG VOLZ', 0, '2026-06-04 12:51:45'),
(2424, 484, 'TRF-20260603-2433', 'adult', 'MARIA VOLZ', 1, '2026-06-04 12:51:45'),
(2425, 484, 'TRF-20260603-2433', 'adult', 'AMELIA VOLZ', 2, '2026-06-04 12:51:45'),
(2426, 484, 'TRF-20260603-2433', 'adult', 'HEIDI VOLZ', 3, '2026-06-04 12:51:45'),
(2427, 492, 'TRF-20260604-1200', 'adult', 'HASSAN EL ABED', 0, '2026-06-04 13:07:44'),
(2428, 492, 'TRF-20260604-1200', 'adult', 'ITAF EL ABED', 1, '2026-06-04 13:07:44'),
(2429, 492, 'TRF-20260604-1200', 'adult', 'ZAHRA ANDREAE', 2, '2026-06-04 13:07:44'),
(2430, 492, 'TRF-20260604-1200', 'adult', 'RICARDA ANDREAE', 3, '2026-06-04 13:07:44'),
(2431, 492, 'TRF-20260604-1200', 'adult', 'AMIR ANDREAE', 4, '2026-06-04 13:07:44'),
(2432, 492, 'TRF-20260604-1200', 'adult', 'SOURAYA ANDREAE', 5, '2026-06-04 13:07:44'),
(2433, 493, 'TRF-20260604-0936', 'adult', 'HASSAN EL ABED', 0, '2026-06-04 13:07:44'),
(2434, 493, 'TRF-20260604-0936', 'adult', 'ITAF EL ABED', 1, '2026-06-04 13:07:44'),
(2435, 493, 'TRF-20260604-0936', 'adult', 'ZAHRA ANDREAE', 2, '2026-06-04 13:07:44'),
(2436, 493, 'TRF-20260604-0936', 'adult', 'RICARDA ANDREAE', 3, '2026-06-04 13:07:44'),
(2437, 493, 'TRF-20260604-0936', 'adult', 'AMIR ANDREAE', 4, '2026-06-04 13:07:44'),
(2438, 493, 'TRF-20260604-0936', 'adult', 'SOURAYA ANDREAE', 5, '2026-06-04 13:07:44'),
(2439, 494, 'TRF-20260604-3784', 'adult', 'TOLGA IRKAYA', 0, '2026-06-04 14:10:18'),
(2440, 494, 'TRF-20260604-3784', 'adult', 'NACIYE MINE IRKAYA', 1, '2026-06-04 14:10:18'),
(2441, 494, 'TRF-20260604-3784', 'child', 'INAYA HIRA IRKAYA', 0, '2026-06-04 14:10:18'),
(2445, 496, 'TRF-20260604-5971', 'adult', 'EVI KEYSER', 0, '2026-06-04 18:29:39'),
(2446, 496, 'TRF-20260604-5971', 'adult', 'JENS CLYNHENS', 1, '2026-06-04 18:29:39'),
(2447, 496, 'TRF-20260604-5971', 'adult', 'NOA CLYNHENS', 2, '2026-06-04 18:29:39'),
(2448, 496, 'TRF-20260604-5971', 'adult', 'LUCA VAN DE LIGT', 3, '2026-06-04 18:29:39'),
(2449, 497, 'TRF-20260604-8369', 'adult', 'EVI KEYSER', 0, '2026-06-04 18:29:39'),
(2450, 497, 'TRF-20260604-8369', 'adult', 'JENS CLYNHENS', 1, '2026-06-04 18:29:39'),
(2451, 497, 'TRF-20260604-8369', 'adult', 'NOA CLYNHENS', 2, '2026-06-04 18:29:39'),
(2452, 497, 'TRF-20260604-8369', 'adult', 'LUCA VAN DE LIGT', 3, '2026-06-04 18:29:39'),
(2453, 469, 'TRF-20260602-1455', 'adult', 'JAMAL HUSSEIN', 0, '2026-06-04 19:49:32'),
(2454, 469, 'TRF-20260602-1455', 'adult', 'HUMAIRA MAJID', 1, '2026-06-04 19:49:32'),
(2455, 469, 'TRF-20260602-1455', 'adult', 'AYAAN JAMAL', 2, '2026-06-04 19:49:32'),
(2456, 498, 'TRF-20260604-2125', 'adult', 'ANISAH ABID', 0, '2026-06-04 20:48:42'),
(2457, 498, 'TRF-20260604-2125', 'adult', 'Mohammad yusuf', 1, '2026-06-04 20:48:42'),
(2458, 498, 'TRF-20260604-2125', 'adult', 'Keyaan yusuf', 2, '2026-06-04 20:48:42'),
(2465, 501, 'TRF-20260604-3287', 'adult', 'HUSSEIN BAALBAKI', 0, '2026-06-04 21:03:58'),
(2466, 501, 'TRF-20260604-3287', 'adult', 'ZAINEB BAALBAKI', 1, '2026-06-04 21:03:58'),
(2467, 501, 'TRF-20260604-3287', 'adult', 'JANNAH BAALBAKI', 2, '2026-06-04 21:03:58'),
(2468, 501, 'TRF-20260604-3287', 'adult', 'SUKAINA BAALBAKI', 3, '2026-06-04 21:03:58'),
(2469, 502, 'TRF-20260605-3857', 'adult', 'TAYFUR IBAY', 0, '2026-06-05 08:13:25'),
(2470, 502, 'TRF-20260605-3857', 'adult', 'HELENA IBAY', 1, '2026-06-05 08:13:25'),
(2471, 503, 'TRF-20260605-8249', 'adult', 'SANTI MERCIER', 0, '2026-06-05 08:21:58'),
(2472, 503, 'TRF-20260605-8249', 'adult', 'Senthil Senthamaraikkannan', 1, '2026-06-05 08:21:58'),
(2473, 503, 'TRF-20260605-8249', 'adult', 'Meenakshi Senthamaraikkannan', 2, '2026-06-05 08:21:58'),
(2474, 503, 'TRF-20260605-8249', 'adult', 'Jagdesh Senthamaraikkannan', 3, '2026-06-05 08:21:58'),
(2480, 505, 'TRF-20260605-7978', 'adult', 'KADİR KARADAŞ', 0, '2026-06-05 08:36:38'),
(2481, 505, 'TRF-20260605-7978', 'adult', 'SABİNE KARADAŞ', 1, '2026-06-05 08:36:38'),
(2482, 504, 'TRF-20260605-5917', 'adult', 'OZAN ÇIRAKMAN', 0, '2026-06-05 08:38:23'),
(2483, 504, 'TRF-20260605-5917', 'adult', 'EDEN ÇIRAKMAN', 1, '2026-06-05 08:38:23'),
(2484, 504, 'TRF-20260605-5917', 'adult', 'AYLAN ÇIRAKMAN', 2, '2026-06-05 08:38:23'),
(2485, 229, 'TRF-20260505-0159', 'adult', 'NATALIIA LUNINA', 0, '2026-06-05 14:03:35'),
(2486, 229, 'TRF-20260505-0159', 'adult', 'LYUDMILA LUNINA', 1, '2026-06-05 14:03:35'),
(2487, 506, 'TRF-20260605-7547', 'adult', 'SHAYLEY ORD', 0, '2026-06-05 15:42:23'),
(2488, 506, 'TRF-20260605-7547', 'adult', 'LISA PRICE', 1, '2026-06-05 15:42:23'),
(2489, 506, 'TRF-20260605-7547', 'adult', 'EVERLEY ORD', 2, '2026-06-05 15:42:23'),
(2490, 506, 'TRF-20260605-7547', 'adult', 'OSCAR ORD', 3, '2026-06-05 15:42:23'),
(2491, 507, 'TRF-20260605-5594', 'adult', 'SHAYLEY ORD', 0, '2026-06-05 15:42:23'),
(2492, 507, 'TRF-20260605-5594', 'adult', 'LISA PRICE', 1, '2026-06-05 15:42:23'),
(2493, 507, 'TRF-20260605-5594', 'adult', 'EVERLEY ORD', 2, '2026-06-05 15:42:23'),
(2494, 507, 'TRF-20260605-5594', 'adult', 'OSCAR ORD', 3, '2026-06-05 15:42:23'),
(2495, 508, 'TRF-20260605-4074', 'adult', 'SHAYLEY ORD', 0, '2026-06-05 15:43:57'),
(2496, 508, 'TRF-20260605-4074', 'adult', 'LISA PRICE', 1, '2026-06-05 15:43:57'),
(2497, 508, 'TRF-20260605-4074', 'adult', 'EVERLEY ORD', 2, '2026-06-05 15:43:57'),
(2498, 508, 'TRF-20260605-4074', 'adult', 'OSCAR ORD', 3, '2026-06-05 15:43:57'),
(2499, 509, 'TRF-20260605-0097', 'adult', 'HERMIN NUREDINI', 0, '2026-06-05 15:46:09'),
(2500, 509, 'TRF-20260605-0097', 'adult', 'DJENA NUREDINI', 1, '2026-06-05 15:46:09'),
(2501, 509, 'TRF-20260605-0097', 'child', 'NAZIBE NUREDINI', 0, '2026-06-05 15:46:09'),
(2502, 509, 'TRF-20260605-0097', 'child', 'EREN NUREDINI', 1, '2026-06-05 15:46:09'),
(2503, 510, 'TRF-20260605-5833', 'adult', 'VIKTORIA ÇEREVKO', 0, '2026-06-05 19:34:39'),
(2504, 510, 'TRF-20260605-5833', 'adult', 'MAKAR ÇEREVKO', 1, '2026-06-05 19:34:39'),
(2505, 510, 'TRF-20260605-5833', 'adult', 'ALEXANDER ÇEREVKO', 2, '2026-06-05 19:34:39'),
(2506, 511, 'TRF-20260605-9016', 'adult', 'MAIK PRETZ', 0, '2026-06-05 20:11:20'),
(2507, 511, 'TRF-20260605-9016', 'adult', 'NICOLE PRETZ', 1, '2026-06-05 20:11:20'),
(2510, 513, 'TRF-20260605-0723', 'adult', 'TOMASZ LOSKA', 0, '2026-06-05 20:19:56'),
(2511, 513, 'TRF-20260605-0723', 'adult', 'ANNA Szydłowska', 1, '2026-06-05 20:19:56'),
(2512, 513, 'TRF-20260605-0723', 'child', 'ADELA LOSKA', 0, '2026-06-05 20:19:56'),
(2516, 512, 'TRF-20260605-5131', 'adult', 'MAIK PRETZ', 0, '2026-06-05 20:20:51'),
(2517, 512, 'TRF-20260605-5131', 'adult', 'NICOLE PRETZ', 1, '2026-06-05 20:20:51'),
(2518, 491, 'TRF-20260604-9620', 'adult', 'JORDAN MANLEY', 0, '2026-06-06 01:07:03'),
(2519, 491, 'TRF-20260604-9620', 'adult', 'MACI MAYO', 1, '2026-06-06 01:07:03'),
(2520, 515, 'TRF-20260606-9455', 'adult', 'LUIDMYLA LUNINA', 0, '2026-06-06 12:40:04'),
(2521, 515, 'TRF-20260606-9455', 'adult', 'NATALIIA LUNINA', 1, '2026-06-06 12:40:04'),
(2538, 230, 'TRF-20260505-4356', 'adult', 'FERNANDO GALLARDO CAMPOS', 0, '2026-06-06 12:55:10'),
(2539, 230, 'TRF-20260505-4356', 'adult', 'KAUR TOOTSI', 1, '2026-06-06 12:55:10'),
(2540, 518, 'TRF-20260606-2476', 'adult', 'SCOTT MULLEN', 0, '2026-06-06 12:58:45'),
(2541, 519, 'TRF-20260606-6221', 'adult', 'SCOTT MULLEN', 0, '2026-06-06 12:58:45'),
(2542, 516, 'TRF-20260606-6710', 'adult', 'CELAL INAN', 0, '2026-06-06 13:12:23'),
(2543, 516, 'TRF-20260606-6710', 'adult', 'ELISA INAN', 1, '2026-06-06 13:12:23'),
(2544, 516, 'TRF-20260606-6710', 'adult', 'MIKAIL INAN', 2, '2026-06-06 13:12:23'),
(2545, 516, 'TRF-20260606-6710', 'adult', 'ILYAS INAN', 3, '2026-06-06 13:12:23'),
(2546, 517, 'TRF-20260606-3813', 'adult', 'CELAL INAN', 0, '2026-06-06 13:12:36'),
(2547, 517, 'TRF-20260606-3813', 'adult', 'ELISA INAN', 1, '2026-06-06 13:12:36'),
(2548, 517, 'TRF-20260606-3813', 'adult', 'MIKAIL INAN', 2, '2026-06-06 13:12:36'),
(2549, 517, 'TRF-20260606-3813', 'adult', 'ILYAS INAN', 3, '2026-06-06 13:12:36'),
(2560, 452, 'TRF-20260601-6952', 'adult', 'MOUNIR DRIOUECH', 0, '2026-06-06 13:56:25'),
(2561, 452, 'TRF-20260601-6952', 'adult', 'KAWTAR DRIOUECH', 1, '2026-06-06 13:56:25'),
(2562, 452, 'TRF-20260601-6952', 'adult', 'NIYA DRIOUECH', 2, '2026-06-06 13:56:25'),
(2563, 452, 'TRF-20260601-6952', 'adult', 'MUSA DRIOUECH', 3, '2026-06-06 13:56:25'),
(2564, 521, 'TRF-20260606-4076', 'adult', 'CALLUM KANE', 0, '2026-06-06 20:47:22'),
(2565, 521, 'TRF-20260606-4076', 'adult', 'ALANA OXLEY', 1, '2026-06-06 20:47:22'),
(2566, 520, 'TRF-20260606-1201', 'adult', 'VANESSA SARKÖZIVA', 0, '2026-06-07 06:57:22'),
(2567, 520, 'TRF-20260606-1201', 'adult', 'PETER PAGAC I', 1, '2026-06-07 06:57:22'),
(2568, 520, 'TRF-20260606-1201', 'adult', 'PETER PAGAC II', 2, '2026-06-07 06:57:22'),
(2569, 520, 'TRF-20260606-1201', 'adult', 'PETER PAGAC III', 3, '2026-06-07 06:57:22'),
(2570, 520, 'TRF-20260606-1201', 'adult', 'PATRICK PAGAC', 4, '2026-06-07 06:57:22'),
(2571, 520, 'TRF-20260606-1201', 'adult', 'LUCIA MIERNA', 5, '2026-06-07 06:57:22'),
(2572, 522, 'TRF-20260607-8259', 'adult', 'Grigoriu Beatrice', 0, '2026-06-07 14:05:41'),
(2573, 522, 'TRF-20260607-8259', 'adult', 'Stanica Luna Maria', 1, '2026-06-07 14:05:41'),
(2574, 522, 'TRF-20260607-8259', 'adult', 'Stanica Andrei Ciprian', 2, '2026-06-07 14:05:41'),
(2575, 523, 'TRF-20260607-3616', 'adult', 'MARIO PIANA', 0, '2026-06-07 14:08:50'),
(2576, 523, 'TRF-20260607-3616', 'adult', 'USCHI LINSSEN', 1, '2026-06-07 14:08:50'),
(2579, 525, 'TRF-20260607-2825', 'adult', 'ANDREJ PRUCH', 0, '2026-06-07 15:57:48'),
(2580, 526, 'TRF-20260607-4060', 'adult', 'IVANA JURISIC', 0, '2026-06-07 16:04:07'),
(2581, 526, 'TRF-20260607-4060', 'adult', 'ROKO JURISIC', 1, '2026-06-07 16:04:07'),
(2582, 526, 'TRF-20260607-4060', 'adult', 'IZABEL JURISIC', 2, '2026-06-07 16:04:07'),
(2583, 223, 'TRF-20260505-2103', 'adult', 'BEDİRHAN YEKTİR', 0, '2026-06-07 16:16:04'),
(2584, 223, 'TRF-20260505-2103', 'adult', 'ROKSANA KRAWCZYK YEKTİR', 1, '2026-06-07 16:16:04'),
(2585, 223, 'TRF-20260505-2103', 'adult', 'MILAN KRAWCZYK', 2, '2026-06-07 16:16:04'),
(2586, 223, 'TRF-20260505-2103', 'adult', 'MEIKE KRUG', 3, '2026-06-07 16:16:04'),
(2587, 223, 'TRF-20260505-2103', 'adult', 'EMELY KRUG', 4, '2026-06-07 16:16:04'),
(2588, 223, 'TRF-20260505-2103', 'adult', 'PHILIP GRUG', 5, '2026-06-07 16:16:04'),
(2589, 224, 'TRF-20260505-4417', 'adult', 'BEDİRHAN YEKTİR', 0, '2026-06-07 16:16:14'),
(2590, 224, 'TRF-20260505-4417', 'adult', 'ROKSANA KRAWCZYK YEKTİR', 1, '2026-06-07 16:16:14'),
(2591, 224, 'TRF-20260505-4417', 'adult', 'MILAN KRAWCZYK', 2, '2026-06-07 16:16:14'),
(2592, 224, 'TRF-20260505-4417', 'adult', 'MEIKE KRUG', 3, '2026-06-07 16:16:14'),
(2593, 224, 'TRF-20260505-4417', 'adult', 'EMELY KRUG', 4, '2026-06-07 16:16:14'),
(2594, 224, 'TRF-20260505-4417', 'adult', 'PHILIP GRUG', 5, '2026-06-07 16:16:14'),
(2595, 527, 'TRF-20260607-3983', 'adult', 'KAZIM BİLECAN', 0, '2026-06-07 20:39:55'),
(2596, 527, 'TRF-20260607-3983', 'adult', 'ONANA MOLDOVANU', 1, '2026-06-07 20:39:55'),
(2600, 528, 'TRF-20260608-2811', 'adult', 'MARK RYDER', 0, '2026-06-08 14:50:51'),
(2601, 528, 'TRF-20260608-2811', 'adult', 'EMMA LAITAK', 1, '2026-06-08 14:50:51'),
(2602, 528, 'TRF-20260608-2811', 'child', 'HARPER RYDER', 0, '2026-06-08 14:50:51'),
(2603, 529, 'TRF-20260608-3884', 'adult', 'MELISA PACOLLI', 0, '2026-06-08 14:55:43'),
(2604, 529, 'TRF-20260608-3884', 'adult', 'BEJTUSH PACOLLI', 1, '2026-06-08 14:55:43'),
(2605, 529, 'TRF-20260608-3884', 'adult', 'ILKEM PACOLLI', 2, '2026-06-08 14:55:43'),
(2608, 499, 'TRF-20260604-0116', 'adult', 'İBN DAFIA MOUNIA', 0, '2026-06-08 14:59:37'),
(2609, 500, 'TRF-20260604-7144', 'adult', 'İBN DAFIA MOUNIA', 0, '2026-06-08 14:59:54'),
(2614, 179, 'TRF-20260505-1778', 'adult', 'MICHAEL BÖHRİNGER', 0, '2026-06-08 15:04:32'),
(2615, 179, 'TRF-20260505-1778', 'adult', 'NICOLE KISSING', 1, '2026-06-08 15:04:32'),
(2616, 530, 'TRF-20260608-6758', 'adult', 'SALİH KARACA', 0, '2026-06-08 15:05:33'),
(2617, 530, 'TRF-20260608-6758', 'adult', 'YASEMİN GİDEN KARACA', 1, '2026-06-08 15:05:33'),
(2618, 530, 'TRF-20260608-6758', 'child', 'ASEL KARACA', 0, '2026-06-08 15:05:33'),
(2622, 532, 'TRF-20260608-5742', 'adult', 'MARK RYDER', 0, '2026-06-08 15:19:38'),
(2623, 532, 'TRF-20260608-5742', 'adult', 'EMMA LATIAK', 1, '2026-06-08 15:19:38'),
(2624, 532, 'TRF-20260608-5742', 'child', 'HARPER RYDER', 0, '2026-06-08 15:19:38'),
(2625, 533, 'TRF-20260608-1573', 'adult', 'FAUZIA AHMED', 0, '2026-06-08 15:22:49'),
(2626, 533, 'TRF-20260608-1573', 'adult', 'RAHIL ABEDIN', 1, '2026-06-08 15:22:49'),
(2627, 533, 'TRF-20260608-1573', 'child', 'MUSA ABEDIN', 0, '2026-06-08 15:22:49'),
(2628, 534, 'TRF-20260608-3115', 'adult', 'NAZARII VASIK', 0, '2026-06-08 15:26:41'),
(2629, 534, 'TRF-20260608-3115', 'adult', 'DARIA YURCHENKO', 1, '2026-06-08 15:26:41'),
(2633, 270, 'TRF-20260508-1576', 'adult', 'KARINA PUNCULS', 0, '2026-06-08 18:10:42'),
(2634, 270, 'TRF-20260508-1576', 'adult', 'GINTS PUNCULS', 1, '2026-06-08 18:10:42'),
(2635, 201, 'TRF-20260505-2338', 'adult', 'EDUARD WGNER', 0, '2026-06-08 20:18:40'),
(2636, 201, 'TRF-20260505-2338', 'adult', 'AGNES SCHUMACHER', 1, '2026-06-08 20:18:40'),
(2637, 201, 'TRF-20260505-2338', 'adult', 'ALEXANDER KRAEMER', 2, '2026-06-08 20:18:40'),
(2638, 201, 'TRF-20260505-2338', 'adult', 'VIKTORIA KRAEMER', 3, '2026-06-08 20:18:40'),
(2639, 535, 'TRF-20260609-3819', 'adult', 'MARILENA BOECA', 0, '2026-06-09 07:49:00'),
(2640, 535, 'TRF-20260609-3819', 'adult', 'ALICE STEGARU', 1, '2026-06-09 07:49:00'),
(2641, 535, 'TRF-20260609-3819', 'adult', 'DIANA CHIRITA', 2, '2026-06-09 07:49:00'),
(2642, 531, 'TRF-20260608-7316', 'adult', 'BÜŞRA KOYUNCU', 0, '2026-06-09 09:48:13'),
(2643, 531, 'TRF-20260608-7316', 'adult', 'HAVVA KOYUNCU', 1, '2026-06-09 09:48:13'),
(2644, 531, 'TRF-20260608-7316', 'adult', 'ARDA KOYUNCU', 2, '2026-06-09 09:48:13'),
(2645, 184, 'TRF-20260505-5946', 'adult', 'MUHAMMAD WAQAS', 0, '2026-06-09 11:25:14'),
(2646, 184, 'TRF-20260505-5946', 'adult', 'IQRA MAHMOOD', 1, '2026-06-09 11:25:14'),
(2651, 536, 'TRF-20260609-0644', 'adult', 'MIDLEY BRUINTJES', 0, '2026-06-09 12:02:04'),
(2652, 536, 'TRF-20260609-0644', 'adult', 'RENALDO SANDERS', 1, '2026-06-09 12:02:04'),
(2653, 537, 'TRF-20260609-2992', 'adult', 'MIDLEY BRUINTJES', 0, '2026-06-09 12:02:31'),
(2654, 537, 'TRF-20260609-2992', 'adult', 'RENALDO SANDERS', 1, '2026-06-09 12:02:31'),
(2655, 538, 'TRF-20260609-4109', 'adult', 'FLORIN GÖSGENS', 0, '2026-06-09 12:07:25'),
(2656, 538, 'TRF-20260609-4109', 'adult', 'SIMONE GÖSGENS', 1, '2026-06-09 12:07:25'),
(2657, 538, 'TRF-20260609-4109', 'child', 'TAYLOR GÖSGENS', 0, '2026-06-09 12:07:25'),
(2658, 538, 'TRF-20260609-4109', 'child', 'NOAH GÖSGENS', 1, '2026-06-09 12:07:25'),
(2662, 514, 'TRF-20260605-3970', 'adult', 'TOMASZ LOSKA', 0, '2026-06-09 16:46:57'),
(2663, 514, 'TRF-20260605-3970', 'adult', 'ANNA Szydłowska', 1, '2026-06-09 16:46:57'),
(2664, 514, 'TRF-20260605-3970', 'child', 'ADELA LOSKA', 0, '2026-06-09 16:46:57'),
(2667, 206, 'TRF-20260505-8120', 'adult', 'ALEXANDER HORN', 0, '2026-06-09 21:11:36'),
(2668, 206, 'TRF-20260505-8120', 'adult', 'GALINA HORN', 1, '2026-06-09 21:11:36'),
(2669, 495, 'TRF-20260604-7804', 'adult', 'TOLGA IRKAYA', 0, '2026-06-10 10:00:53'),
(2670, 495, 'TRF-20260604-7804', 'adult', 'NACIYE MINE IRKAYA', 1, '2026-06-10 10:00:53'),
(2671, 495, 'TRF-20260604-7804', 'child', 'INAYA HIRA IRKAYA', 0, '2026-06-10 10:00:53'),
(2672, 539, 'TRF-20260610-1206', 'adult', 'KATIE MCGAVIN', 0, '2026-06-10 14:48:39'),
(2673, 539, 'TRF-20260610-1206', 'adult', 'JOSH RALSTON', 1, '2026-06-10 14:48:39'),
(2674, 539, 'TRF-20260610-1206', 'adult', 'LINDA MCGAVIN', 2, '2026-06-10 14:48:39'),
(2675, 539, 'TRF-20260610-1206', 'adult', 'JOHN MCGAVIN', 3, '2026-06-10 14:48:39'),
(2676, 540, 'TRF-20260610-1053', 'adult', 'CALLUM CANE', 0, '2026-06-10 14:51:23'),
(2677, 540, 'TRF-20260610-1053', 'adult', 'ALANA OXLEY', 1, '2026-06-10 14:51:23'),
(2678, 541, 'TRF-20260610-9805', 'adult', 'AJDIN TOROMANOVIC', 0, '2026-06-10 15:00:13'),
(2679, 541, 'TRF-20260610-9805', 'adult', 'Ermina avdic toromanovic', 1, '2026-06-10 15:00:13'),
(2680, 541, 'TRF-20260610-9805', 'adult', 'Kenan toromanovic', 2, '2026-06-10 15:00:13'),
(2681, 541, 'TRF-20260610-9805', 'adult', 'Alicia gantrich', 3, '2026-06-10 15:00:13'),
(2699, 543, 'TRF-20260610-3031', 'adult', 'VEDRAN JUKIC', 0, '2026-06-10 15:08:50'),
(2700, 543, 'TRF-20260610-3031', 'adult', 'Kristina Jukic', 1, '2026-06-10 15:08:50'),
(2701, 543, 'TRF-20260610-3031', 'adult', 'Dominik Jukic', 2, '2026-06-10 15:08:50'),
(2702, 543, 'TRF-20260610-3031', 'adult', 'Ema Jukic', 3, '2026-06-10 15:08:50'),
(2703, 543, 'TRF-20260610-3031', 'adult', 'Lana Jukic', 4, '2026-06-10 15:08:50'),
(2704, 543, 'TRF-20260610-3031', 'adult', 'Marko Slipcevic', 5, '2026-06-10 15:08:50'),
(2705, 543, 'TRF-20260610-3031', 'adult', 'Danijela Slipcevic', 6, '2026-06-10 15:08:50'),
(2706, 543, 'TRF-20260610-3031', 'adult', 'Dita Slipcevic', 7, '2026-06-10 15:08:50'),
(2707, 543, 'TRF-20260610-3031', 'adult', 'Lora Slipcevic', 8, '2026-06-10 15:08:50'),
(2708, 543, 'TRF-20260610-3031', 'adult', 'Ivan Bosancic', 9, '2026-06-10 15:08:50'),
(2709, 543, 'TRF-20260610-3031', 'adult', 'Ivana Bosancic', 10, '2026-06-10 15:08:50'),
(2710, 543, 'TRF-20260610-3031', 'adult', 'Arijan Bosancic', 11, '2026-06-10 15:08:50'),
(2711, 543, 'TRF-20260610-3031', 'adult', 'Nika Bosancic', 12, '2026-06-10 15:08:50'),
(2712, 543, 'TRF-20260610-3031', 'adult', 'Marko Milidrag', 13, '2026-06-10 15:08:50'),
(2713, 543, 'TRF-20260610-3031', 'adult', 'Zrinka Milidrag', 14, '2026-06-10 15:08:50'),
(2714, 543, 'TRF-20260610-3031', 'adult', 'Ivan Milidrag', 15, '2026-06-10 15:08:50'),
(2715, 543, 'TRF-20260610-3031', 'adult', 'Mika Milidrag', 16, '2026-06-10 15:08:50'),
(2716, 542, 'TRF-20260610-3316', 'adult', 'VEDRAN JUKIC', 0, '2026-06-10 15:09:40'),
(2717, 542, 'TRF-20260610-3316', 'adult', 'Kristina Jukic', 1, '2026-06-10 15:09:40'),
(2718, 542, 'TRF-20260610-3316', 'adult', 'Dominik Jukic', 2, '2026-06-10 15:09:40'),
(2719, 542, 'TRF-20260610-3316', 'adult', 'Ema Jukic', 3, '2026-06-10 15:09:40'),
(2720, 542, 'TRF-20260610-3316', 'adult', 'Lana Jukic', 4, '2026-06-10 15:09:40'),
(2721, 542, 'TRF-20260610-3316', 'adult', 'Marko Slipcevic', 5, '2026-06-10 15:09:40'),
(2722, 542, 'TRF-20260610-3316', 'adult', 'Danijela Slipcevic', 6, '2026-06-10 15:09:40'),
(2723, 542, 'TRF-20260610-3316', 'adult', 'Dita Slipcevic', 7, '2026-06-10 15:09:40'),
(2724, 542, 'TRF-20260610-3316', 'adult', 'Lora Slipcevic', 8, '2026-06-10 15:09:40'),
(2725, 542, 'TRF-20260610-3316', 'adult', 'Ivan Bosancic', 9, '2026-06-10 15:09:40'),
(2726, 542, 'TRF-20260610-3316', 'adult', 'Ivana Bosancic', 10, '2026-06-10 15:09:40'),
(2727, 542, 'TRF-20260610-3316', 'adult', 'Arijan Bosancic', 11, '2026-06-10 15:09:40'),
(2728, 542, 'TRF-20260610-3316', 'adult', 'Nika Bosancic', 12, '2026-06-10 15:09:40'),
(2729, 542, 'TRF-20260610-3316', 'adult', 'Marko Milidrag', 13, '2026-06-10 15:09:40'),
(2730, 542, 'TRF-20260610-3316', 'adult', 'Zrinka Milidrag', 14, '2026-06-10 15:09:40'),
(2731, 542, 'TRF-20260610-3316', 'adult', 'Ivan Milidrag', 15, '2026-06-10 15:09:40'),
(2732, 542, 'TRF-20260610-3316', 'adult', 'Mika Milidrag', 16, '2026-06-10 15:09:40'),
(2733, 363, 'TRF-20260519-7208', 'adult', 'SEVDA HUSEYNOVA', 0, '2026-06-10 15:11:45'),
(2734, 363, 'TRF-20260519-7208', 'adult', 'NAZAKAT HUSEYNOVA', 1, '2026-06-10 15:11:45'),
(2735, 363, 'TRF-20260519-7208', 'adult', 'SARA HUSEYNOVA', 2, '2026-06-10 15:11:45'),
(2736, 363, 'TRF-20260519-7208', 'adult', 'GÖZEL HUSEYNOVA', 3, '2026-06-10 15:11:45'),
(2737, 544, 'TRF-20260610-4944', 'adult', 'SIDRA KAUSER', 0, '2026-06-10 15:14:18'),
(2738, 545, 'TRF-20260610-7357', 'adult', 'ADRIAN AMBRUS', 0, '2026-06-10 15:18:09'),
(2739, 545, 'TRF-20260610-7357', 'adult', 'CRISTINA AMBRUS', 1, '2026-06-10 15:18:09'),
(2740, 545, 'TRF-20260610-7357', 'adult', 'NADINA AMBRUS', 2, '2026-06-10 15:18:09'),
(2741, 546, 'TRF-20260610-4025', 'adult', 'ADRIAN AMBRUS', 0, '2026-06-10 15:18:09'),
(2742, 546, 'TRF-20260610-4025', 'adult', 'CRISTINA AMBRUS', 1, '2026-06-10 15:18:09'),
(2743, 546, 'TRF-20260610-4025', 'adult', 'NADINA AMBRUS', 2, '2026-06-10 15:18:09'),
(2746, 548, 'TRF-20260610-3669', 'adult', 'MUSTAFA KEMAL YILDIRIM', 0, '2026-06-10 15:48:40'),
(2747, 549, 'TRF-20260611-8686', 'adult', 'ALEXANDER ÇEREVKO', 0, '2026-06-11 06:17:00'),
(2748, 549, 'TRF-20260611-8686', 'adult', 'MAKAR ÇEREVKO', 1, '2026-06-11 06:17:00'),
(2749, 549, 'TRF-20260611-8686', 'adult', 'VIKTORIA ÇEREVKO', 2, '2026-06-11 06:17:00'),
(2750, 379, 'TRF-20260523-1683', 'adult', 'IRENA GUTIN', 0, '2026-06-11 06:42:07'),
(2751, 379, 'TRF-20260523-1683', 'adult', 'ANATOLI GUTIN', 1, '2026-06-11 06:42:07'),
(2752, 379, 'TRF-20260523-1683', 'adult', 'OLGA USPENSKAYA', 2, '2026-06-11 06:42:07'),
(2754, 551, 'TRF-20260611-1247', 'adult', 'MUSTAFA KEMAL YILDIRIM', 0, '2026-06-11 07:03:03'),
(2755, 551, 'TRF-20260611-1247', 'adult', 'MUSTAFA KEMAL YILDIRIM', 1, '2026-06-11 07:03:03'),
(2760, 552, 'TRF-20260611-2295', 'adult', 'OZAN ÇIRAKMAN', 0, '2026-06-11 07:34:40'),
(2761, 552, 'TRF-20260611-2295', 'adult', 'EDEN ÇIRAKMAN', 1, '2026-06-11 07:34:40'),
(2762, 552, 'TRF-20260611-2295', 'adult', 'AYLAN ÇIRAKMAN', 2, '2026-06-11 07:34:40'),
(2763, 524, 'TRF-20260607-5999', 'adult', 'HARIS BEŞİROVİC', 0, '2026-06-11 07:36:53'),
(2764, 524, 'TRF-20260607-5999', 'adult', 'MERYEM BEŞİROVİC', 1, '2026-06-11 07:36:53'),
(2765, 524, 'TRF-20260607-5999', 'child', 'ZEID BEŞİROVİC', 0, '2026-06-11 07:36:53'),
(2766, 550, 'TRF-20260611-7941', 'adult', 'BEGÜM CANLI', 0, '2026-06-11 07:40:57'),
(2771, 196, 'TRF-20260505-2054', 'adult', 'WILHELMUS HALLER', 0, '2026-06-11 08:59:06'),
(2772, 196, 'TRF-20260505-2054', 'adult', 'PATRICIA HALLER', 1, '2026-06-11 08:59:06'),
(2773, 196, 'TRF-20260505-2054', 'adult', 'SHAKIRA HALLER', 2, '2026-06-11 08:59:06'),
(2774, 196, 'TRF-20260505-2054', 'adult', 'MYROM REMMELTS', 3, '2026-06-11 08:59:06'),
(2775, 334, 'TRF-20260516-5341', 'adult', 'JULIENNE HOFFMEISTER', 0, '2026-06-11 09:09:46'),
(2776, 334, 'TRF-20260516-5341', 'adult', 'KAI HOFFMEISTER', 1, '2026-06-11 09:09:46'),
(2777, 334, 'TRF-20260516-5341', 'adult', 'GENEVIEVE HOFFMAISTER', 2, '2026-06-11 09:09:46'),
(2778, 553, 'TRF-20260611-1640', 'adult', 'SARA ELCHIRI', 0, '2026-06-11 11:02:59'),
(2779, 553, 'TRF-20260611-1640', 'adult', 'ALANUD HASAN', 1, '2026-06-11 11:02:59'),
(2785, 198, 'TRF-20260505-8823', 'adult', 'FURKAN COŞKUN', 0, '2026-06-11 15:04:23'),
(2786, 198, 'TRF-20260505-8823', 'adult', 'ŞEHRİNUR COŞKUN', 1, '2026-06-11 15:04:23'),
(2787, 198, 'TRF-20260505-8823', 'child', 'CEYLİN COŞKUN', 0, '2026-06-11 15:04:23'),
(2788, 554, 'TRF-20260611-8995', 'adult', 'WILLI SENG', 0, '2026-06-11 15:10:37'),
(2789, 554, 'TRF-20260611-8995', 'adult', 'SABRINA SENG', 1, '2026-06-11 15:10:37'),
(2790, 554, 'TRF-20260611-8995', 'adult', 'ELLA SENG', 2, '2026-06-11 15:10:37'),
(2794, 555, 'TRF-20260611-4553', 'adult', 'WILLI SENG', 0, '2026-06-11 15:16:56'),
(2795, 555, 'TRF-20260611-4553', 'adult', 'SABRINA SENG', 1, '2026-06-11 15:16:56'),
(2796, 555, 'TRF-20260611-4553', 'adult', 'ELLA SENG', 2, '2026-06-11 15:16:56'),
(2797, 556, 'TRF-20260612-6592', 'adult', 'BİRSEN TOK', 0, '2026-06-11 21:03:39'),
(2798, 556, 'TRF-20260612-6592', 'adult', 'MUSTAFA TOK', 1, '2026-06-11 21:03:39'),
(2799, 557, 'TRF-20260612-0393', 'adult', 'CEMAL YILDIRIM', 0, '2026-06-11 21:06:30'),
(2800, 557, 'TRF-20260612-0393', 'adult', 'AYŞE YILDIRIM', 1, '2026-06-11 21:06:30'),
(2801, 558, 'TRF-20260612-3846', 'adult', 'HAROON NASIR', 0, '2026-06-11 21:14:00'),
(2802, 558, 'TRF-20260612-3846', 'adult', 'Samara kousar', 1, '2026-06-11 21:14:00'),
(2803, 558, 'TRF-20260612-3846', 'adult', 'Mohammed ali Nasir', 2, '2026-06-11 21:14:00'),
(2804, 558, 'TRF-20260612-3846', 'adult', 'Mohammed Adam Nasir', 3, '2026-06-11 21:14:00'),
(2805, 558, 'TRF-20260612-3846', 'adult', 'Ayza Noor Nasir', 4, '2026-06-11 21:14:00'),
(2806, 559, 'TRF-20260612-1385', 'adult', 'GERALD HELMDORF', 0, '2026-06-11 21:17:44'),
(2807, 559, 'TRF-20260612-1385', 'adult', 'Romana Marggraf', 1, '2026-06-11 21:17:44'),
(2808, 559, 'TRF-20260612-1385', 'adult', 'Thomas Kottwitz', 2, '2026-06-11 21:17:44'),
(2809, 559, 'TRF-20260612-1385', 'adult', 'Ramona Kottwitz', 3, '2026-06-11 21:17:44'),
(2810, 559, 'TRF-20260612-1385', 'adult', 'Sven Herfurth', 4, '2026-06-11 21:17:44'),
(2811, 559, 'TRF-20260612-1385', 'adult', 'Rosemarie Herfurth', 5, '2026-06-11 21:17:44'),
(2812, 560, 'TRF-20260612-9096', 'adult', 'GERALD HELMDORF', 0, '2026-06-11 21:17:44'),
(2813, 560, 'TRF-20260612-9096', 'adult', 'Romana Marggraf', 1, '2026-06-11 21:17:44'),
(2814, 560, 'TRF-20260612-9096', 'adult', 'Thomas Kottwitz', 2, '2026-06-11 21:17:44'),
(2815, 560, 'TRF-20260612-9096', 'adult', 'Ramona Kottwitz', 3, '2026-06-11 21:17:44'),
(2816, 560, 'TRF-20260612-9096', 'adult', 'Sven Herfurth', 4, '2026-06-11 21:17:44'),
(2817, 560, 'TRF-20260612-9096', 'adult', 'Rosemarie Herfurth', 5, '2026-06-11 21:17:44'),
(2818, 471, 'TRF-20260602-7557', 'adult', 'BRENDON SHILTON', 0, '2026-06-12 07:48:29'),
(2819, 471, 'TRF-20260602-7557', 'adult', 'JESSICA BENNET', 1, '2026-06-12 07:48:29'),
(2830, 561, 'TRF-20260612-7349', 'adult', 'ZOLTAN SZABO', 0, '2026-06-12 10:01:29'),
(2831, 561, 'TRF-20260612-7349', 'adult', 'KATALIN VARGA SZABO', 1, '2026-06-12 10:01:29'),
(2832, 561, 'TRF-20260612-7349', 'adult', 'ZOE SARA SZABO', 2, '2026-06-12 10:01:29'),
(2833, 561, 'TRF-20260612-7349', 'adult', 'GRETA SZABO', 3, '2026-06-12 10:01:29'),
(2834, 561, 'TRF-20260612-7349', 'adult', 'FLORA SZABO', 4, '2026-06-12 10:01:29'),
(2840, 562, 'TRF-20260612-7898', 'adult', 'ZOLTAN SZABO', 0, '2026-06-12 10:02:14'),
(2841, 562, 'TRF-20260612-7898', 'adult', 'KATALIN VARGA SZABO', 1, '2026-06-12 10:02:14'),
(2842, 562, 'TRF-20260612-7898', 'adult', 'ZOE SARA SZABO', 2, '2026-06-12 10:02:14'),
(2843, 562, 'TRF-20260612-7898', 'adult', 'GRETA SZABO', 3, '2026-06-12 10:02:14'),
(2844, 562, 'TRF-20260612-7898', 'adult', 'FLORA SZABO', 4, '2026-06-12 10:02:14'),
(2845, 563, 'TRF-20260612-1295', 'adult', 'CERRİ JONES', 0, '2026-06-12 12:11:57'),
(2846, 563, 'TRF-20260612-1295', 'adult', 'MARK PEMBERTON', 1, '2026-06-12 12:11:57'),
(2847, 563, 'TRF-20260612-1295', 'adult', 'OSCAR PEMBERTON', 2, '2026-06-12 12:11:57'),
(2848, 563, 'TRF-20260612-1295', 'adult', 'HARLEY PEMBERTON', 3, '2026-06-12 12:11:57'),
(2849, 563, 'TRF-20260612-1295', 'adult', 'MAX JONES', 4, '2026-06-12 12:11:57'),
(2850, 564, 'TRF-20260613-1437', 'adult', 'DIANA BOGDANOVA', 0, '2026-06-13 09:42:50'),
(2851, 564, 'TRF-20260613-1437', 'adult', 'Aleksands Kotovs', 1, '2026-06-13 09:42:50'),
(2852, 564, 'TRF-20260613-1437', 'adult', 'Timurs Kotovs (ребенок)', 2, '2026-06-13 09:42:50'),
(2853, 564, 'TRF-20260613-1437', 'adult', 'Zanna Bogdanova', 3, '2026-06-13 09:42:50'),
(2854, 564, 'TRF-20260613-1437', 'adult', 'Genadijs Bogdanovs', 4, '2026-06-13 09:42:50'),
(2855, 564, 'TRF-20260613-1437', 'adult', 'Artjom Potapovs', 5, '2026-06-13 09:42:50'),
(2856, 564, 'TRF-20260613-1437', 'adult', 'Natalja Pimenova', 6, '2026-06-13 09:42:50'),
(2857, 564, 'TRF-20260613-1437', 'adult', 'Dmitrijs Potapovs', 7, '2026-06-13 09:42:50'),
(2858, 564, 'TRF-20260613-1437', 'adult', 'Egija Sveckare', 8, '2026-06-13 09:42:50'),
(2859, 565, 'TRF-20260613-7081', 'adult', 'DIANA BOGDANOVA', 0, '2026-06-13 09:42:50'),
(2860, 565, 'TRF-20260613-7081', 'adult', 'Aleksands Kotovs', 1, '2026-06-13 09:42:50'),
(2861, 565, 'TRF-20260613-7081', 'adult', 'Timurs Kotovs (ребенок)', 2, '2026-06-13 09:42:50'),
(2862, 565, 'TRF-20260613-7081', 'adult', 'Zanna Bogdanova', 3, '2026-06-13 09:42:50'),
(2863, 565, 'TRF-20260613-7081', 'adult', 'Genadijs Bogdanovs', 4, '2026-06-13 09:42:50'),
(2864, 565, 'TRF-20260613-7081', 'adult', 'Artjom Potapovs', 5, '2026-06-13 09:42:50'),
(2865, 565, 'TRF-20260613-7081', 'adult', 'Natalja Pimenova', 6, '2026-06-13 09:42:50'),
(2866, 565, 'TRF-20260613-7081', 'adult', 'Dmitrijs Potapovs', 7, '2026-06-13 09:42:50'),
(2867, 565, 'TRF-20260613-7081', 'adult', 'Egija Sveckare', 8, '2026-06-13 09:42:50'),
(2871, 567, 'TRF-20260613-5318', 'adult', 'ARTUR WIESNER', 0, '2026-06-13 10:04:38'),
(2872, 567, 'TRF-20260613-5318', 'adult', 'NICOLE STIER', 1, '2026-06-13 10:04:38'),
(2873, 567, 'TRF-20260613-5318', 'child', 'LIANA WIESNER', 0, '2026-06-13 10:04:38'),
(2874, 568, 'TRF-20260613-4510', 'adult', 'ARTUR WIESNER', 0, '2026-06-13 10:06:07'),
(2875, 568, 'TRF-20260613-4510', 'adult', 'NICOLE STIER', 1, '2026-06-13 10:06:07'),
(2876, 568, 'TRF-20260613-4510', 'child', 'LIANA WIESNER', 0, '2026-06-13 10:06:07'),
(2877, 566, 'TRF-20260613-3162', 'adult', 'ARTUR WIESNER', 0, '2026-06-13 10:06:19'),
(2878, 566, 'TRF-20260613-3162', 'adult', 'NICOLE STIER', 1, '2026-06-13 10:06:19'),
(2879, 566, 'TRF-20260613-3162', 'child', 'LIANA WIESNER', 0, '2026-06-13 10:06:19'),
(2880, 569, 'TRF-20260613-7479', 'adult', 'ASHFAQUE CHOWDHURY', 0, '2026-06-13 13:53:17'),
(2881, 570, 'TRF-20260613-1563', 'adult', 'NICOLE MEZNER', 0, '2026-06-13 15:31:53'),
(2882, 571, 'TRF-20260613-0652', 'adult', 'FATMA YATOUJI', 0, '2026-06-13 16:07:26'),
(2883, 571, 'TRF-20260613-0652', 'adult', 'BELHASSEN OMAR CHADI', 1, '2026-06-13 16:07:26'),
(2884, 571, 'TRF-20260613-0652', 'adult', 'BAYA CHADI', 2, '2026-06-13 16:07:26'),
(2885, 259, 'TRF-20260506-9941', 'adult', 'OLGA FRANK', 0, '2026-06-14 09:03:05'),
(2886, 259, 'TRF-20260506-9941', 'adult', 'ISABELLE FRANK', 1, '2026-06-14 09:03:05'),
(2887, 260, 'TRF-20260506-6352', 'adult', 'OLGA FRANK', 0, '2026-06-14 09:03:28'),
(2888, 260, 'TRF-20260506-6352', 'adult', 'ISABELLE FRANK', 1, '2026-06-14 09:03:28'),
(2889, 572, 'TRF-20260614-8630', 'adult', 'JAY LOVELL', 0, '2026-06-14 09:07:26'),
(2890, 572, 'TRF-20260614-8630', 'adult', 'Valerija Isajeva', 1, '2026-06-14 09:07:26'),
(2891, 573, 'TRF-20260614-8195', 'adult', 'HASSAN FAIZAN', 0, '2026-06-14 09:10:40'),
(2892, 573, 'TRF-20260614-8195', 'adult', 'LAIBE NEDEEM', 1, '2026-06-14 09:10:40'),
(2893, 574, 'TRF-20260614-9761', 'adult', 'HASSAN FAIZAN', 0, '2026-06-14 09:10:40'),
(2894, 574, 'TRF-20260614-9761', 'adult', 'LAIBE NEDEEM', 1, '2026-06-14 09:10:40'),
(2895, 575, 'TRF-20260614-8053', 'adult', 'ahmet keskin', 0, '2026-06-14 17:43:29'),
(2896, 575, 'TRF-20260614-8053', 'adult', 'simge keskin', 1, '2026-06-14 17:43:30'),
(2897, 576, 'TRF-20260614-9228', 'adult', 'ahmet keskin', 0, '2026-06-14 17:43:30'),
(2898, 576, 'TRF-20260614-9228', 'adult', 'simge keskin', 1, '2026-06-14 17:43:30');

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
-- Tablo için tablo yapısı `customers`
--

CREATE TABLE `customers` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'FİKRET GEMRİKLİ', NULL, '+49017680515901', '2026-06-14 17:21:10', '2026-06-14 17:21:10'),
(2, 'KANDSORRA WOLFGANG', NULL, '+4917672883149', '2026-06-14 17:21:10', '2026-06-14 17:21:10'),
(3, 'EDVIN TISK', NULL, '+4748653342', '2026-06-14 17:21:10', '2026-06-14 17:21:10'),
(4, 'KYANAT SHAH', NULL, '+447464218024', '2026-06-14 17:21:10', '2026-06-14 17:21:10'),
(5, 'ANNA JAKUZSEW', NULL, '+48513095789', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(6, 'PATRICIA HIBBERT', NULL, '+44756534817', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(7, 'EMILY HENZLER', NULL, NULL, '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(8, 'ANJA LANGENBACH', NULL, '+49 176 34139393', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(9, 'JENS KUHLMANN', NULL, '+49 173 6520273', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(10, 'ROMAN BILY', NULL, '+44 7394 764702', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(11, 'DANNY HENNEKES', NULL, '+31 6 52675282', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(12, 'QUAIMA AIT EL HADJ', NULL, '+31 6 36032967', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(13, 'BESMIRA ZHUTA', NULL, '+41 76 681 42 15', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(14, 'DJORDY HENDRICKS', NULL, '+31 6 39325013', '2026-06-14 17:21:11', '2026-06-14 17:21:11'),
(15, 'ANGIE STARMANS', NULL, '+31 6 30034315', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(16, 'AYMAN EL BEKKAOUI', NULL, '+31 6 13109793', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(17, 'RAZİYE ERYILMAZ', NULL, NULL, '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(18, 'Vitali Leis', NULL, '+49 176 22988685', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(19, 'MUHAMMAD BILAL', NULL, '+44 7384 266651', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(20, 'MUDASSIR MANSHA', NULL, '+44 7384 266651', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(21, 'MAKSIM OSIPOVS', NULL, '+371 29 746 739', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(22, 'BJÖRN SCHMELZER', NULL, '+491733010065', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(23, 'RACHELLE SUTTON', NULL, '+44 7749 193180', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(24, 'SHANTHOSAN SIVANDAN', NULL, '+44 7405 279855', '2026-06-14 17:21:12', '2026-06-14 17:21:12'),
(25, 'KACPER BEC', NULL, '+49 1575 2472622', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(26, 'HELENA WIEBE', NULL, NULL, '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(27, 'VERA ALBERTI', NULL, '+49 1523 3618806', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(28, 'KYNAAT SHAH', NULL, '+44 7464 218024', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(29, 'TAHSEEN SHAH', NULL, '+44 7790 453487', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(30, 'İLKAY KOPARAN', NULL, '+49 178 8082590', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(31, 'CAN KLEISMAN', NULL, '+49 163 6969895', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(32, 'MUSTAFA LALE', NULL, '05327336949', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(33, 'ISABEL SCULTZ', NULL, '+49 172 2492390', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(34, 'SERGEN ÖZMEN', NULL, '+49 1577 7358358', '2026-06-14 17:21:13', '2026-06-14 17:21:13'),
(35, 'SHANAY GORDON', NULL, '+44 7496 345618', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(36, 'OWAIS ASIF', NULL, '+44 7414 286708', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(37, 'ANNA JAKUSZEW', NULL, '+48 513 095 789', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(38, 'DOERTA KUBERSKI', NULL, '+49 176 44224994', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(39, 'ILONA KÖNİG', NULL, '+49 170 4646219', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(40, 'HUSSEIN EL ABED', NULL, '+49 1512 4166324', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(41, 'YUSSOF ALAM', NULL, '+49 160 1814464', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(42, 'Jacquelina Bollacke', NULL, '+49 176 23569654', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(43, 'STEFAN BELL', NULL, '+44 7796 913065', '2026-06-14 17:21:14', '2026-06-14 17:21:14'),
(44, 'DIANA RÜLANDER', NULL, '+49 172 3740934', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(45, 'NADINE RADES', NULL, '+49 176 24655007', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(46, 'GALINA STENNING', NULL, NULL, '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(47, 'ANNA SCHILDER', NULL, '+43 650 6901304', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(48, 'KIM HOEKSTRA', NULL, '+31 6 53878520', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(49, 'STEVEN HANLEY', NULL, '+44 7795 466431', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(50, 'Dylan De Noijer', NULL, '+31 6 18323023', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(51, 'NATALIJA MARINA', NULL, '+353 83 152 1411', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(52, 'MICHELLE GOROWOI', NULL, '+49 176 72971935', '2026-06-14 17:21:15', '2026-06-14 17:21:15'),
(53, 'SENNA SANDERS', NULL, '+31 6 57475947', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(54, 'STEFFI SCHAPER', NULL, '+49 176 50271885', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(55, 'INES KAUFMANN', NULL, '+49 162 9148108', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(56, 'KIARA SWEENEY', NULL, '+44 7742 740212', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(57, 'NELLI DAKOV', NULL, '+49 1520 2459620', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(58, 'MASIH PAIKARJO', NULL, '+49 15568 645570', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(59, 'VADIMS STEPANOVS', NULL, '+371 29 119 619', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(60, 'Hennes weubker', NULL, NULL, '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(61, 'CALLUM KALSI', NULL, '+44 7376 237519', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(62, 'JANA VON JAN', NULL, '+49 177 8115059', '2026-06-14 17:21:16', '2026-06-14 17:21:16'),
(63, 'INEZ WNEK', NULL, '+49 1521 4356687', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(64, 'LUKMAN ALIZADA', NULL, '+44 7704 449147', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(65, 'ZAID NANAWADI', NULL, '+44 7471 267039', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(66, 'JESSICA EILERS', NULL, '+49 173 6563645', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(67, 'SILVIU FLORIAN BOC', NULL, '+49 177 6728414', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(68, 'GUIDO PESCHEL', NULL, '+49 176 60443492', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(69, 'MARCEL HÜNİNG', NULL, '+49 170 1719718', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(70, 'ALJONA BENZ', NULL, '+49 176 61569610', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(71, 'ALPASLAN YALÇIN', NULL, '+33 6 36 67 06 67', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(72, 'YASSİNE BOUZINE', NULL, '+31 6 24980321', '2026-06-14 17:21:17', '2026-06-14 17:21:17'),
(73, 'ELISA PAGNOZZO', NULL, NULL, '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(74, 'ERHAN KARAKOÇ', NULL, NULL, '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(75, 'ADEM UĞURLU', NULL, NULL, '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(76, 'MICHAEL ROSENBOOM', NULL, '+49 176 12266226', '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(77, 'ALEKSANDRA PRUCHNIEWSKA', NULL, NULL, '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(78, 'ANDREAS KOZOLE', NULL, '+491634660361', '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(79, 'ANDREAS KOZOLE', NULL, NULL, '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(80, 'MIKE WETERKAMP', NULL, '+31 6 37010275', '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(81, 'MICHAEL BÖHRİNGER', NULL, '+49 1511 0502460', '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(82, 'Anke Mühlstädt', NULL, NULL, '2026-06-14 17:21:18', '2026-06-14 17:21:18'),
(83, 'PAUL COMBES', NULL, '+44 7368 923639', '2026-06-14 17:21:19', '2026-06-14 17:21:19'),
(84, 'UFUK CÖMERT', NULL, '+49 1520 8688845', '2026-06-14 17:21:19', '2026-06-14 17:21:19'),
(85, 'MUHAMMAD WAQAS', NULL, '+44 7435 778035', '2026-06-14 17:21:19', '2026-06-14 17:21:19'),
(86, 'VLADIMIRS SOBOLEVS', NULL, '+44 7730 777139', '2026-06-14 17:21:19', '2026-06-14 17:21:19'),
(87, 'MARTA SZARPAK', NULL, '+48 601 508 328', '2026-06-14 17:21:19', '2026-06-14 17:21:19'),
(88, 'ERIK TRIEBER', NULL, '+49 176 24611114', '2026-06-14 17:21:19', '2026-06-14 17:21:19'),
(89, 'BERKAY KALAYCI', NULL, '+49 172 4582391', '2026-06-14 17:21:19', '2026-06-14 17:21:19'),
(90, 'MOHAMMAD İSMAİL', NULL, '+44 7964 697233', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(91, 'WILHELMUS HALLER', NULL, '+44 7964 697233', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(92, 'FURKAN COŞKUN', NULL, '+49 175 4186314', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(93, 'AYTEN BAHTİRİ', NULL, '+49 172 4653276', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(94, 'EDUARD WAGNER', NULL, '+49 160 2606939', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(95, 'EDUARD WGNER', NULL, '+49 160 2606939', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(96, 'DIANA LACKA', NULL, '+371 28 830 309', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(97, 'NURIJE OSMANI', NULL, '+49 176 81083696', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(98, 'ALEXANDER HORN', NULL, '+49 176 22855954', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(99, 'JOSEF HAIDER', NULL, '+49 173 3621027', '2026-06-14 17:21:20', '2026-06-14 17:21:20'),
(100, 'HAKAN BATTIG', NULL, '+49 162 2056195', '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(101, 'SERHAN GÜNEŞ', NULL, NULL, '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(102, 'JULIA PETERSEN', NULL, '+49 174 9802753', '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(103, 'DENNIS FICEK', NULL, '+49 174 7047540', '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(104, 'MAREK DEBOWSKI', NULL, NULL, '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(105, 'ANGELA DI MAGGIO', NULL, '+49 176 61142401', '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(106, 'STEFAN PFAFF', NULL, '+49 176 82168622', '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(107, 'BEDİRHAN YEKTİR', NULL, '+49 1575 5748433', '2026-06-14 17:21:21', '2026-06-14 17:21:21'),
(108, 'NEBAHAT YALÇINKAYA', NULL, '+49 176 97691792', '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(109, 'ANEL CURİC', NULL, '+436503706560', '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(110, 'NATALIIA LUNINA', NULL, '+380 68 139 7304', '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(111, 'FERNANDO GALLARDO CAMPOS', NULL, NULL, '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(112, 'KATI', NULL, '+49 171 3211491', '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(113, 'CHRISTIAN HAGEN', NULL, NULL, '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(114, 'SARAH ONEILL', NULL, '+44 7740 101686', '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(115, 'CHRISTOPH HENEL', NULL, '+49 170 6160992', '2026-06-14 17:21:22', '2026-06-14 17:21:22'),
(116, 'ŞENAY TURACAK', NULL, '+31 6 46139890', '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(117, 'GINO MOLL', NULL, '+49 1515 9860218', '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(118, 'RACHEL GILL', NULL, '+44 7761 308432', '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(119, 'MARCUS HUMMEL', NULL, NULL, '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(120, 'DANIELA RÖPCKE', NULL, '+49 1520 2821124', '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(121, 'MALTE EHLERT', NULL, '+49 176 97739555', '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(122, 'MAIK PRETZ', NULL, '+49 160 6475769', '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(123, 'IGOR OSIPCHUK', NULL, '+380 96 475 5923', '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(124, 'JEAN SPENCER', NULL, NULL, '2026-06-14 17:21:23', '2026-06-14 17:21:23'),
(125, 'OLGA FRANK', NULL, '+49 162 8704086', '2026-06-14 17:21:24', '2026-06-14 17:21:24'),
(126, 'JAY HEBBORN', NULL, '+44 7870 884232', '2026-06-14 17:21:24', '2026-06-14 17:21:24'),
(127, 'JOSE ORLANDO LOPEZ', NULL, NULL, '2026-06-14 17:21:24', '2026-06-14 17:21:24'),
(128, 'ANDRIAN TONNY PREDESCU', NULL, '+44 7565 716144', '2026-06-14 17:21:24', '2026-06-14 17:21:24'),
(129, 'LAURO LAURANNE', NULL, '+32 476 84 81 11', '2026-06-14 17:21:24', '2026-06-14 17:21:24'),
(130, 'KARINA PUNCULS', NULL, '+47 908 73 518', '2026-06-14 17:21:24', '2026-06-14 17:21:24'),
(131, 'VLADISLAV MASISIKI', NULL, NULL, '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(132, 'EBUBEKİR GÜREL', NULL, '+49 160 2603211', '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(133, 'NADINE LÖFFLER', NULL, '+49 171 7435456', '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(134, 'LORI HASELL', NULL, '+44 7539 946621', '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(135, 'EMIL GODLA', NULL, '+44 7442 912255', '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(136, 'ABDELWAHED ADAHRI', NULL, '+31 6 14532584', '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(137, 'SARKA ROHACOVA', NULL, NULL, '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(138, 'CHRISTIAN HENRIKSEN', NULL, NULL, '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(139, 'TATIANA LAZEROVA', NULL, NULL, '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(140, 'TUFAN YAZICI', NULL, '+4917628302727', '2026-06-14 17:21:25', '2026-06-14 17:21:25'),
(141, 'AXEL KERLL', NULL, '+49 171 2662089', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(142, 'FADIL BEGANAJ', NULL, '+49 174 3958950', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(143, 'ZEESHAN SAGHIR', NULL, '+447904972954', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(144, 'BETHANY HANCOCK', NULL, '+497749164267', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(145, 'RALF RIEGER', NULL, '+491638641719', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(146, 'Julia Haubenreisser', NULL, '+49 1520 3821075', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(147, 'SİBEL PADURARU', NULL, '+4917663417319', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(148, 'LUKMAN ALIZADE', NULL, '+447704449147', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(149, 'ABDELWAHED ADAHRİ', NULL, '+31614532584', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(150, 'JENS KHULMANN', NULL, '+491736520273', '2026-06-14 17:21:26', '2026-06-14 17:21:26'),
(151, 'MARIO MIHAILA', NULL, '+447955458019', '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(152, 'THOMAS TIPTHORPE', NULL, '+447476990932', '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(153, 'JAMAL HUSSAIN', NULL, '+447306257303', '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(154, 'ROSMARIE FURRER', NULL, NULL, '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(155, 'RICARDO HERKENS', NULL, NULL, '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(156, 'ANGELİKA BÜHRİNG', NULL, NULL, '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(157, 'KARİM KOPONEN', NULL, NULL, '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(158, 'ŞENİZ POLATCI', NULL, '+49 178 5495240', '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(159, 'Tatyana Kapralenko', NULL, '+380 97 653 8935', '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(160, 'Vithussan Nanthagopalan', NULL, '+44 7453 392227', '2026-06-14 17:21:27', '2026-06-14 17:21:27'),
(161, 'NELLE WILSON', NULL, '+44 7742 454165', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(162, 'SERGEİ KUTERGİN', NULL, '+3725050625', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(163, 'ALEXANDRA IHLE', NULL, '+49 1577 9343652', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(164, 'SIMON SIMEONOV', NULL, '+31 6 37384270', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(165, 'SUHAILA YONAN', NULL, 'YONAN', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(166, 'Dylen de noijer', NULL, '+31618323023', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(167, 'KATARINA DEDIC', NULL, NULL, '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(168, 'Fırat vurucu', NULL, '+4522331829', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(169, 'Tabea Theilen', NULL, '+49 1573 2529253', '2026-06-14 17:21:28', '2026-06-14 17:21:28'),
(170, 'SIMON STAIBER', NULL, '+49 160 1226969', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(171, 'ARTEM KUNGURZEV', NULL, '+49 1525 5777794', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(172, 'SARAH HERWEG', NULL, '+49 176 82252615', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(173, 'TIRZA VAN ROOIJEN', NULL, '+31 6 41557064', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(174, 'RYAN BURNS', NULL, '+44 7594 615695', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(175, 'JULIENNE HOFFMEISTER', NULL, '+49 1523 3668822', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(176, 'DOROTA KORBUS', NULL, '+48695205213', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(177, 'MICHAEL KRINJEN', NULL, '+31627822313', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(178, 'SAM LARKIN', NULL, '+447538316274', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(179, 'MEHMET ALİ YILDIZ', NULL, '+491627125810', '2026-06-14 17:21:29', '2026-06-14 17:21:29'),
(180, 'PETRA ANDROLEIT', NULL, NULL, '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(181, 'KLAUS DIETER BACZYK', NULL, NULL, '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(182, 'Luis webels', NULL, '+4915257893242', '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(183, 'Carole swinburne', NULL, NULL, '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(184, 'Gülsün yaşar', NULL, '+31616831650', '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(185, 'Fahmi laouini', NULL, '+33761496988', '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(186, 'AHMET TANRIKOLU', NULL, '+90 542 309 88 54', '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(187, 'BLEONA AHMETI', NULL, '+49 1523 8422041', '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(188, 'FABIAN DOKL', NULL, '+49 1512 9513587', '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(189, 'JOLINA KOTHE', NULL, '+49 1525 1804647', '2026-06-14 17:21:30', '2026-06-14 17:21:30'),
(190, 'MELISSA CHARMELY', NULL, '+44 7527 495393', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(191, 'SEVDA HUSEYNOVA', NULL, '+46 73 701 93 04', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(192, 'Serap couper', NULL, NULL, '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(193, 'Elena Peitsch', NULL, '+49 179 4720377', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(194, 'OESEM AKKABAL', NULL, '+32 485 08 88 54', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(195, 'REXHEP ZUKA', NULL, '+352661264985', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(196, 'HASSAN HASSAN', NULL, '+964 774 911 9953', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(197, 'YASEMİN DEMİR', NULL, '+49 162 2581244', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(198, 'VADIM STEPANOVS', NULL, '+371 29 119 619', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(199, 'KADRUSH SELIMI', NULL, '+41 76 568 54 16', '2026-06-14 17:21:31', '2026-06-14 17:21:31'),
(200, 'IRENA GUTIN', NULL, '+15147991208', '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(201, 'Hana Ramcilovic', NULL, '+352 691 154 494', '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(202, 'BİLAL KÖYCÜ', NULL, '+32 488 60 45 65', '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(203, 'SAMIRA HAGOS', NULL, '+46 70 768 45 94', '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(204, 'DERYA FİDANCI', NULL, '+4917680553291', '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(205, 'GEORG SCHNEIDER', NULL, NULL, '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(206, 'DIETMAR RESSEL', NULL, '+49 176 24212035', '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(207, 'DINO DJULIC', NULL, '+43 660 1105093', '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(208, 'THOMAS MEYER', NULL, NULL, '2026-06-14 17:21:32', '2026-06-14 17:21:32'),
(209, 'ALAN MAJID HASAN', NULL, '+964 750 455 8350', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(210, 'JESSICA NOORDMAN', NULL, '+31 6 15270788', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(211, 'Patricia Matthews', NULL, '+353 86 842 3532', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(212, 'AHMAD SHWEHNEH', NULL, '+90 537 206 18 82', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(213, 'Nabil mechehrawi', NULL, NULL, '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(214, 'PATRICK BRUDER', NULL, '+33 6 13 19 96 60', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(215, 'Merve khalaf', NULL, '+49 176 31285667', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(216, 'PATRICK VILLERIUS', NULL, '+31 6 53577736', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(217, 'JOY HANA', NULL, '+46 72 381 78 09', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(218, 'VALENTIN MOCH', NULL, '+41 76 628 23 36', '2026-06-14 17:21:33', '2026-06-14 17:21:33'),
(219, 'ALI GHOULAM', NULL, '+4407836435124', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(220, 'SAMIRA MOUKEFFES', NULL, '+49 177 1542206', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(221, 'JORDAN MANLEY', NULL, '+44 7368 509121', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(222, 'SİNAN BAK', NULL, '+49 176 64008810', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(223, 'PHILLIP GARNHAM', NULL, '+447970266036', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(224, 'ÖZCAN KOCAMAN', NULL, '+49 176 84559364', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(225, 'CEMAL YILDIRIM', NULL, '+49 1573 6153016', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(226, 'MARILENA BOEACA', NULL, '+40799421240', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(227, 'VIKTOR MELENBERG', NULL, '+49 1575 4305782', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(228, 'FATMA KOLUÇOLAK', NULL, '05554708080', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(229, 'NATALIJA PETROVA', NULL, '+37258394680', '2026-06-14 17:21:34', '2026-06-14 17:21:34'),
(230, 'JC VAN TIENEN', NULL, '+31 6 38911696', '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(231, 'TIFANNY NHAN', NULL, '+47 455 72 380', '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(232, 'Fatima Cheaito', NULL, '+46 72 918 55 41', '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(233, 'MARK MEADER', NULL, '+44 7904 232182', '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(234, 'DEBBY VERVLOET', NULL, NULL, '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(235, 'ALEXANDER FELGER', NULL, '+491742347178', '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(236, 'ZERIN CATIC', NULL, '+436602448820', '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(237, 'STEFAN HALLAUER', NULL, '+4917661053238', '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(238, 'Jewgeni Riedel', NULL, NULL, '2026-06-14 17:21:35', '2026-06-14 17:21:35'),
(239, 'CEREN UYGUT', NULL, '+49 1575 1777164', '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(240, 'Ovidiu Remus Chelaru', NULL, '+44 7377 222480', '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(241, 'BEN LEE', NULL, '+44 7584 320071', '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(242, 'Valdrin Shabani', NULL, '+41 79 220 61 28', '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(243, 'LIRIDON ZUKA', NULL, '+352 661 357 107', '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(244, 'IVANA JURISIC', NULL, '+385 95 737 4776', '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(245, 'MOUNIR DRIOUECH', NULL, NULL, '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(246, 'TOBIAS ISENSCHMID', NULL, '+41 76 802 90 89', '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(247, 'NAZARII VASIK', NULL, NULL, '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(248, 'HERBEST BEER', NULL, NULL, '2026-06-14 17:21:36', '2026-06-14 17:21:36'),
(249, 'DJORDY HENDRIKS', NULL, '+31 6 39325013', '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(250, 'ARON FURER', NULL, NULL, '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(251, 'NATALIE MILDE', NULL, '+49 1575 4600357', '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(252, 'HASAN MAJID', NULL, '+9647504558350', '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(253, 'CHRISTIAN MANZL', NULL, '+436763112175', '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(254, 'OLGA WAGNER', NULL, '+49 175 2074282', '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(255, 'JAMAL HUSSEIN', NULL, '+44 7306 257303', '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(256, 'BRENDON SHILTON', NULL, '+44 7950 916082', '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(257, 'GARY ROGERSON', NULL, NULL, '2026-06-14 17:21:37', '2026-06-14 17:21:37'),
(258, 'FILIP FILIPAK', NULL, NULL, '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(259, 'FAUIZA AHMED', NULL, '+44 7884 977191', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(260, 'VALON VELUI', NULL, '+49 171 2376522', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(261, 'AJDIN TOROMONOVIC', NULL, '+31 6 23257743', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(262, 'BANU SHEIKH', NULL, '+44 7913 990857', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(263, 'GLENN BOERA SOBA', NULL, '+31 6 15406152', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(264, 'MUSTAFA KAÇMAZ', NULL, '+49 162 6181051', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(265, 'WOLFGANG VOLZ', NULL, NULL, '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(266, 'ALİ GÖKDEMİR', NULL, '+4917670748331', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(267, 'MUHAMMAD QASIM', NULL, '+447709291616', '2026-06-14 17:21:38', '2026-06-14 17:21:38'),
(268, 'TOMASZ LOSKA', NULL, '+48782067268', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(269, 'INA PAULSEN', NULL, NULL, '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(270, 'ANGELIKA BÜHRING', NULL, NULL, '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(271, 'MANUEL KRAUSSER', NULL, '+491754644895', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(272, 'HASSAN EL ABED', NULL, '+491724053495', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(273, 'TOLGA IRKAYA', NULL, '+49 176 58878524', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(274, 'EVI KEYSER', NULL, '+32 472 90 46 48', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(275, 'ANISAH ABID', NULL, '+447306671998', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(276, 'İBN DAFIA MOUNIA', NULL, '+32484845769', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(277, 'HUSSEIN BAALBAKI', NULL, '+46706787844', '2026-06-14 17:21:39', '2026-06-14 17:21:39'),
(278, 'TAYFUR IBAY', NULL, NULL, '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(279, 'SANTI MERCIER', NULL, '+41766453712', '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(280, 'OZAN ÇIRAKMAN', NULL, '+33626058376', '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(281, 'KADİR KARADAŞ', NULL, '+436766273557', '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(282, 'SHAYLEY ORD', NULL, '+44 7849 795069', '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(283, 'HERMIN NUREDINI', NULL, '+49 176 21817656', '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(284, 'VIKTORIA ÇEREVKO', NULL, '+37255605760', '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(285, 'LUIDMYLA LUNINA', NULL, NULL, '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(286, 'CEMAL INAN', NULL, '+491629666568', '2026-06-14 17:21:40', '2026-06-14 17:21:40'),
(287, 'SCOTT MULLEN', NULL, NULL, '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(288, 'VANESSA SARKÖZIVA', NULL, '+421 914 151 941', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(289, 'CALLUM KANE', NULL, '+447931320695', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(290, 'Grigoriu Beatrice', NULL, '+40 766 107 230', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(291, 'MARIO PIANA', NULL, '+31 6 81707939', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(292, 'HARIS BEŞİROVİC', NULL, NULL, '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(293, 'ANDREJ PRUCH', NULL, '+49 176 64646104', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(294, 'KAZIM BİLECAN', NULL, '+41796386363', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(295, 'MARK RYDER', NULL, '+44 7885 992212', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(296, 'MELISA PACOLLI', NULL, '+49 162 6779424', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(297, 'SALİH KARACA', NULL, '+31 6 42824012', '2026-06-14 17:21:41', '2026-06-14 17:21:41'),
(298, 'BÜŞRA KOYUNCU', NULL, '+49 160 3104176', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(299, 'FAUZIA AHMED', NULL, '+44 7884 977191', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(300, 'NAZARII VASIK', NULL, '+49 176 20362113', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(301, 'MARILENA BOECA', NULL, '+40 799 421 240', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(302, 'MIDLEY BRUINTJES', NULL, '+31 6 43858845', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(303, 'FLORIN GÖSGENS', NULL, '+49 179 6126558', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(304, 'KATIE MCGAVIN', NULL, '+1 (623) 399-3316', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(305, 'CALLUM CANE', NULL, '+44 7931 320695', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(306, 'AJDIN TOROMANOVIC', NULL, '+31 6 23257743', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(307, 'VEDRAN JUKIC', NULL, '+385 98 315 404', '2026-06-14 17:21:42', '2026-06-14 17:21:42'),
(308, 'SIDRA KAUSER', NULL, '+44 7404 876055', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(309, 'ADRIAN AMBRUS', NULL, '+40 742 610 969', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(310, 'MUSTAFA KEMAL YILDIRIM', NULL, NULL, '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(311, 'ALEXANDER ÇEREVKO', NULL, '+372 5814 7555', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(312, 'BEGÜM CANLI', NULL, NULL, '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(313, 'MUSTAFA KEMAL YILDIRIM', NULL, '+90 536 717 92 82', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(314, 'SARA ELCHIRI', NULL, '+4581718086', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(315, 'WILLI SENG', NULL, '+49 163 6799412', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(316, 'BİRSEN TOK', NULL, '+90 535 464 22 52', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(317, 'HAROON NASIR', NULL, '+44 7923 243956', '2026-06-14 17:21:43', '2026-06-14 17:21:43'),
(318, 'GERALD HELMDORF', NULL, '+49 1520 4976956', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(319, 'ZOLTAN SZABO', NULL, 'Zoltán Szabó', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(320, 'CERRİ JONES', NULL, '+44 7976 640942', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(321, 'DIANA BOGDANOVA', NULL, '+44 7540 467409', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(322, 'ARTUR WIESNER', NULL, '+49 176 63390749', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(323, 'ASHFAQUE CHOWDHURY', NULL, '+447944692928', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(324, 'NICOLE MEZNER', NULL, NULL, '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(325, 'FATMA YATOUJI', NULL, '+33620365890', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(326, 'JAY LOVELL', NULL, '+44 7873 935625', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(327, 'HASSAN FAIZAN', NULL, '+44 7481 801028', '2026-06-14 17:21:44', '2026-06-14 17:21:44'),
(328, 'ahmet keskin', 'ahmet@netask.com.tr', '905078485254', '2026-06-14 17:43:29', '2026-06-14 17:43:29');

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
(11, 'Kızılağaç Transfer', 'kizilagac-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Kızılağaç', NULL, NULL, NULL, '', 'general/1769353749_a7a5d089718ac8d5.webp', 0.0, 0, 0, 1, 'Kızılağaç Transfer', '', 'published', 7, '2026-02-15 20:39:07', '2026-05-03 03:38:47'),
(12, 'Alanya Transfer', 'alanya-transfer', '', '', NULL, NULL, 'Antalya / Alanya', NULL, NULL, NULL, '', 'general/1769551064_43aaee0999f03861.webp', 0.0, 0, 0, 0, 'Alanya Transfer', '', 'published', 8, '2026-04-26 08:03:34', '2026-04-28 23:38:59'),
(13, 'Kundu Transfer', 'kundu-transfer', '', '', NULL, NULL, 'Antalya / Muratpaşa / Kundu', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kundu Transfer', '', 'published', 9, '2026-04-26 11:48:36', '2026-05-03 10:59:54'),
(14, 'Lara Transfer', 'lara-transfer', '', '', NULL, NULL, 'Antalya / Muratpaşa / Lara', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Lara Transfer', '', 'published', 10, '2026-04-26 11:50:40', '2026-05-03 10:59:38'),
(15, 'Kızılot Transfer', 'kizilot-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Kızılot', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kızılot Transfer', '', 'published', 11, '2026-04-27 18:57:12', '2026-04-27 18:58:24'),
(16, 'Okurcalar Transfer', 'okurcalar-transfer', '', '', NULL, NULL, 'Antalya / Alanya / Okurcalar', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Okurcalar Transfer', '', 'published', 12, '2026-04-27 18:59:40', '2026-04-27 19:00:03'),
(17, 'Avsallar Transfer', 'avsallar-transfer', '', '', NULL, NULL, 'Antalya / Alanya / Avsallar', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Avsallar Transfer', '', 'published', 13, '2026-04-28 18:02:59', '2026-04-28 18:02:59'),
(18, 'Türkler Transfer', 'turkler-transfer', '', '', NULL, NULL, 'Antalya / Alanya / Türkler', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Türkler Transfer', '', 'published', 14, '2026-04-29 07:04:46', '2026-04-29 07:04:46'),
(19, 'Belek Transfer', 'belek-transfer', '', '', NULL, NULL, 'Antalya / Serik / Belek', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Belek Transfer', '', 'published', 15, '2026-04-29 21:04:47', '2026-04-29 21:04:47'),
(20, 'Kemer Transfer', 'kemer-transfer', '', '', NULL, NULL, '', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kemer Transfer', '', 'published', 16, '2026-04-29 21:24:27', '2026-04-29 21:24:27'),
(21, 'Tekirova Transfer', 'tekirova-transfer', '', '', NULL, NULL, '', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Tekirova Transfer', '', 'published', 17, '2026-04-29 21:24:40', '2026-04-29 21:24:40'),
(22, 'Çamyuva Transfer', 'camyuva-transfer', '', '', NULL, NULL, '', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Çamyuva Transfer', '', 'published', 18, '2026-04-29 21:24:53', '2026-04-29 21:24:53'),
(23, 'Kiriş Transfer', 'kiris-transfer', '', '', NULL, NULL, '', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kiriş Transfer', '', 'published', 19, '2026-04-29 21:25:54', '2026-04-29 21:25:54'),
(24, 'Beldibi Transfer', 'beldibi-transfer', '', '', NULL, NULL, 'Antalya / Kemer / Beldibi', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Beldibi Transfer', '', 'published', 20, '2026-05-02 13:38:21', '2026-05-02 13:38:46'),
(25, 'Boğazkent Transfer', 'bogazkent-transfer', '', '', NULL, NULL, 'Antalya / Serik / Boğazkent', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Boğazkent Transfer', '', 'published', 21, '2026-05-02 13:48:10', '2026-05-02 13:48:10'),
(26, 'Gündoğdu Transfer', 'gundogdu-transfer', '', '', NULL, NULL, 'Antalya / Manavgat / Gündoğdu', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Gündoğdu Transfer', '', 'published', 22, '2026-05-02 15:32:11', '2026-05-02 15:32:38'),
(27, 'Şirinyalı Transfer', 'sirinyali-transfer', '', '', NULL, NULL, 'Antalya / Muratpaşa / Şirinyalı', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Şirinyalı Transfer', '', 'published', 23, '2026-05-03 10:59:24', '2026-05-03 10:59:24'),
(28, 'Kadriye Transfer', 'kadriye-transfer', '', '', NULL, NULL, 'Antalya / Serik / Kadriye', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kadriye Transfer', '', 'published', 24, '2026-05-04 10:59:27', '2026-05-04 10:59:27'),
(29, 'Fethiye Transfer', 'fethiye-transfer', '', '', NULL, NULL, 'Muğla / Fethiye', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Fethiye Transfer', '', 'published', 25, '2026-05-05 07:30:43', '2026-05-05 07:30:43'),
(30, 'Kargıcak Transfer', 'kargicak-transfer', '', '', NULL, NULL, 'Alanya / Gazipaşa / Kargıcak', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Kargıcak Transfer', '', 'published', 26, '2026-05-05 16:51:23', '2026-05-05 16:51:23'),
(31, 'Göynük Transfer', 'goynuk-transfer', '', '', NULL, NULL, 'Antalya / Kemer / Göynük', NULL, NULL, NULL, '', '', 0.0, 0, 0, 0, 'Göynük Transfer', '', 'published', 27, '2026-05-05 20:25:04', '2026-05-05 20:25:04');

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
(307, 18, 'tr', 'Türkler Transfer', 'turkler-transfer', '', '', '', '', 'Türkler Transfer', '', '2026-04-29 07:04:46', '2026-04-29 07:04:46'),
(333, 19, 'tr', 'Belek Transfer', 'belek-transfer', '', '', '', '', 'Belek Transfer', '', '2026-04-29 21:04:47', '2026-04-29 21:04:47'),
(334, 20, 'tr', 'Kemer Transfer', 'kemer-transfer', '', '', '', '', 'Kemer Transfer', '', '2026-04-29 21:24:27', '2026-04-29 21:24:27'),
(335, 21, 'tr', 'Tekirova Transfer', 'tekirova-transfer', '', '', '', '', 'Tekirova Transfer', '', '2026-04-29 21:24:40', '2026-04-29 21:24:40'),
(336, 22, 'tr', 'Çamyuva Transfer', 'camyuva-transfer', '', '', '', '', 'Çamyuva Transfer', '', '2026-04-29 21:24:53', '2026-04-29 21:24:53'),
(337, 23, 'tr', 'Kiriş Transfer', 'kiris-transfer', '', '', '', '', 'Kiriş Transfer', '', '2026-04-29 21:25:54', '2026-04-29 21:25:54'),
(338, 24, 'tr', 'Beldibi Transfer', 'beldibi-transfer', '', '', '', '', 'Beldibi Transfer', '', '2026-05-02 13:38:21', '2026-05-02 13:38:21'),
(340, 25, 'tr', 'Boğazkent Transfer', 'bogazkent-transfer', '', '', '', '', 'Boğazkent Transfer', '', '2026-05-02 13:48:10', '2026-05-02 13:48:10'),
(341, 26, 'tr', 'Gündoğdu Transfer', 'gundogdu-transfer', '', '', '', '', 'Gündoğdu Transfer', '', '2026-05-02 15:32:11', '2026-05-02 15:32:11'),
(346, 27, 'tr', 'Şirinyalı Transfer', 'sirinyali-transfer', '', '', '', '', 'Şirinyalı Transfer', '', '2026-05-03 10:59:24', '2026-05-03 10:59:24'),
(353, 28, 'tr', 'Kadriye Transfer', 'kadriye-transfer', '', '', '', '', 'Kadriye Transfer', '', '2026-05-04 10:59:27', '2026-05-04 10:59:27'),
(354, 29, 'tr', 'Fethiye Transfer', 'fethiye-transfer', '', '', '', '', 'Fethiye Transfer', '', '2026-05-05 07:30:43', '2026-05-05 07:30:43'),
(355, 30, 'tr', 'Kargıcak Transfer', 'kargicak-transfer', '', '', '', '', 'Kargıcak Transfer', '', '2026-05-05 16:51:23', '2026-05-05 16:51:23'),
(356, 31, 'tr', 'Göynük Transfer', 'goynuk-transfer', '', '', '', '', 'Göynük Transfer', '', '2026-05-05 20:25:04', '2026-05-05 20:25:04');

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
(677, 12, 2, 'de', 85.00, 'EUR', '2026-04-28 23:38:59'),
(678, 12, 3, 'de', 95.00, 'EUR', '2026-04-28 23:38:59'),
(679, 12, 1, 'de', 75.00, 'EUR', '2026-04-28 23:38:59'),
(680, 12, 2, 'tr', 4500.00, 'TRY', '2026-04-28 23:38:59'),
(681, 12, 3, 'tr', 5100.00, 'TRY', '2026-04-28 23:38:59'),
(682, 12, 1, 'tr', 4000.00, 'TRY', '2026-04-28 23:38:59'),
(683, 12, 2, 'en', 95.00, 'EUR', '2026-04-28 23:38:59'),
(684, 12, 3, 'en', 105.00, 'EUR', '2026-04-28 23:38:59'),
(685, 12, 1, 'en', 85.00, 'EUR', '2026-04-28 23:38:59'),
(697, 11, 3, 'de', 75.00, 'EUR', '2026-05-03 03:38:47'),
(698, 11, 1, 'de', 50.00, 'EUR', '2026-05-03 03:38:47'),
(699, 11, 2, 'de', 60.00, 'EUR', '2026-05-03 03:38:47'),
(700, 11, 3, 'tr', 4200.00, 'TRY', '2026-05-03 03:38:47'),
(701, 11, 1, 'tr', 2700.00, 'TRY', '2026-05-03 03:38:47'),
(702, 11, 2, 'tr', 3200.00, 'TRY', '2026-05-03 03:38:47'),
(703, 11, 3, 'en', 85.00, 'USD', '2026-05-03 03:38:47'),
(704, 11, 1, 'en', 60.00, 'USD', '2026-05-03 03:38:47'),
(705, 11, 2, 'en', 70.00, 'USD', '2026-05-03 03:38:47'),
(706, 13, 3, 'de', 45.00, 'EUR', '2026-05-03 10:59:54'),
(707, 13, 1, 'de', 25.00, 'EUR', '2026-05-03 10:59:54'),
(708, 13, 2, 'de', 35.00, 'EUR', '2026-05-03 10:59:54'),
(709, 13, 3, 'tr', 2500.00, 'TRY', '2026-05-03 10:59:54'),
(710, 13, 1, 'tr', 1400.00, 'TRY', '2026-05-03 10:59:54'),
(711, 13, 2, 'tr', 1900.00, 'TRY', '2026-05-03 10:59:54'),
(712, 13, 3, 'en', 55.00, 'USD', '2026-05-03 10:59:54'),
(713, 13, 1, 'en', 35.00, 'USD', '2026-05-03 10:59:54'),
(714, 13, 2, 'en', 45.00, 'USD', '2026-05-03 10:59:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `drivers`
--

CREATE TABLE `drivers` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `surname` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vehicle_id` int NOT NULL,
  `plate` varchar(20) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Tablo döküm verisi `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `surname`, `phone`, `vehicle_id`, `plate`, `created_at`, `updated_at`) VALUES
(1, 'Sezer', 'Boz', '5342449748', 1, '07BZJ990', '2026-05-02 21:43:36', '2026-05-03 08:08:27'),
(2, 'Emre', 'Boz', '5469406233', 1, '07BGL717', '2026-05-03 08:08:17', '2026-05-06 06:23:01');

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
(1, 'SUNIS KUMKÖY BEACH', 'KUMKÖY', NULL, 72.00, 1, 0, '2026-04-27 17:14:33', '2026-04-30 09:28:25'),
(2, 'SUNIS EVREN BEACH', 'EVRENSEKİ', NULL, 57.00, 1, 0, '2026-04-27 17:16:34', '2026-04-30 09:28:07'),
(3, 'SUNIS ELITA BEACH', 'KIZILAĞAÇ', NULL, 85.00, 1, 0, '2026-04-27 17:16:51', '2026-04-30 09:27:37'),
(4, 'RIO LAVITAS RESORT', 'SORGUN', NULL, 74.00, 1, 0, '2026-04-27 17:17:10', '2026-04-30 09:26:10'),
(5, 'ROYAL ATLANTIS ICON', 'GÜNDOĞDU', NULL, 53.00, 1, 0, '2026-04-27 22:17:01', '2026-04-30 09:26:56'),
(7, 'ASKA JUST IN BEACH', 'AVSALLAR', NULL, 140.00, 1, 0, '2026-04-28 18:05:44', '2026-05-02 15:24:37'),
(8, 'LILYUM HOTEL', 'EVRENSEKİ', NULL, 57.00, 1, 0, '2026-04-28 18:47:00', '2026-04-30 09:23:49'),
(9, 'DELPHIN BE GRAND', 'KUNDU', NULL, 13.00, 1, 0, '2026-04-28 23:21:24', '2026-04-30 09:20:13'),
(10, 'LAGO HOTEL', 'SORGUN', NULL, 70.00, 1, 0, '2026-04-29 06:58:23', '2026-04-30 09:23:26'),
(11, 'EFTALIA SPLASH RESORT', 'TÜRKLER', NULL, 106.00, 1, 0, '2026-04-29 07:14:02', '2026-04-30 09:21:02'),
(12, 'DELPHIN PALACE', 'KUNDU', NULL, 15.00, 1, 0, '2026-04-29 07:31:22', '2026-04-30 09:20:20'),
(13, 'PORT RIVER HOTEL', 'SORGUN', NULL, 70.00, 1, 0, '2026-04-29 07:42:09', '2026-04-30 09:25:43'),
(14, 'GRAND PARK LARA', 'KUNDU', NULL, 14.00, 1, 0, '2026-04-29 08:44:02', '2026-04-30 09:21:34'),
(15, 'AYDIN BEY KINGS PALACE', 'EVRENSEKİ', NULL, 57.00, 1, 0, '2026-04-29 08:49:45', '2026-04-30 09:20:07'),
(16, 'THE SENSE DELUXE', 'SİDE', NULL, 61.00, 1, 0, '2026-04-29 08:53:25', '2026-04-30 09:29:03'),
(17, 'KLEOPATRA ROYAL PALM', 'ALANYA', NULL, 122.00, 1, 0, '2026-04-29 09:03:05', '2026-04-30 09:23:06'),
(18, 'ETHNO BELEK', 'BELEK', NULL, 34.00, 1, 0, '2026-04-29 21:06:51', '2026-04-30 09:21:08'),
(19, 'LIMAK LIMRA HOTEL', 'KİRİŞ', NULL, 65.00, 1, 0, '2026-04-29 21:27:30', '2026-04-30 09:24:54'),
(20, 'ASKA LARA', 'KUNDU', NULL, 15.00, 1, 0, '2026-04-29 21:34:47', '2026-04-30 09:20:00'),
(21, 'MIRACKLE RESORT', 'KUNDU', NULL, 13.00, 1, 0, '2026-04-29 21:44:06', '2026-04-30 09:25:05'),
(22, 'SIDE CROWN PALACE', 'EVRENSEKİ', NULL, 57.00, 1, 0, '2026-04-29 21:58:57', '2026-04-30 09:27:08'),
(23, 'DREAM FUN WORLD', 'KUMKÖY', NULL, 60.00, 1, 0, '2026-04-30 08:37:52', '2026-04-30 09:20:27'),
(24, 'DREAM WORLD AQUA', 'KUMKÖY', NULL, 60.00, 1, 0, '2026-04-30 08:54:48', '2026-04-30 09:20:55'),
(25, 'SUNBERK HOTEL', 'SİDE', NULL, 64.00, 1, 0, '2026-04-30 09:07:30', '2026-04-30 09:27:26'),
(26, 'RIXOS PREMIUM BELEK', NULL, NULL, NULL, 1, 0, '2026-05-02 10:14:08', NULL),
(27, 'PALORMA HOTEL', 'KUNDU', NULL, NULL, 1, 0, '2026-05-02 11:22:51', NULL),
(28, 'RUBI PLATINUM SPA RESORT & SUITES', 'AVSALLAR', NULL, NULL, 1, 0, '2026-05-02 11:52:27', NULL),
(29, 'ARCANUS SORGUN', 'SORGUN', NULL, NULL, 1, 0, '2026-05-02 12:30:19', NULL),
(30, 'SÜRAL RESORT', 'ÇOLAKLI', NULL, NULL, 1, 0, '2026-05-02 12:40:54', NULL),
(31, 'SULTAN OF SIDE', 'EVRENSEKİ', NULL, NULL, 1, 0, '2026-05-02 12:43:17', NULL),
(32, 'SEAMELIA BEACH', 'EVRENSEKİ', NULL, NULL, 1, 0, '2026-05-02 12:53:57', NULL),
(33, 'BARUT GOIA', 'SİDE', NULL, NULL, 1, 0, '2026-05-02 13:00:35', NULL),
(34, 'LIMAK LARA DELUXE', 'KUNDU', NULL, NULL, 1, 0, '2026-05-02 13:08:39', NULL),
(35, 'DREAM WATER WORLD', 'KUMKÖY', NULL, NULL, 1, 0, '2026-05-02 13:35:41', NULL),
(36, 'JUJU PREMIER HOTEL', 'BELDİBİ', NULL, NULL, 1, 0, '2026-05-02 13:40:43', NULL),
(37, 'SIDE MARE', 'KUMKÖY', NULL, NULL, 1, 0, '2026-05-02 14:48:27', NULL),
(38, 'KIRMAN BELAZUR', 'BOĞAZKENT', NULL, NULL, 1, 0, '2026-05-02 14:55:59', NULL),
(39, 'CLUB TURAN PRINCE WORLD', 'KIZILAĞAÇ', NULL, NULL, 1, 0, '2026-05-02 15:04:44', NULL),
(40, 'WASHINGTON RESORT', 'KIZILAĞAÇ', NULL, NULL, 1, 0, '2026-05-02 15:09:32', NULL),
(41, 'DELPHIN DELUXE', 'OKURCALAR', NULL, NULL, 1, 0, '2026-05-02 15:19:45', NULL),
(42, 'TRENDY ASPENDOS BEACH', 'GÜNDOĞDU', NULL, NULL, 1, 0, '2026-05-02 15:29:01', NULL),
(43, 'ROYAL HOLIDAY PALACE', 'KUNDU', NULL, NULL, 1, 0, '2026-05-02 15:37:33', NULL),
(44, 'ALBA RESORT', 'ÇOLAKLI', NULL, NULL, 1, 0, '2026-05-02 15:49:15', NULL),
(45, 'ARSI BLUE BEACH', 'ALANYA', NULL, NULL, 1, 0, '2026-05-02 15:57:49', NULL),
(46, 'SIDE STORY HOTEL', 'EVRENSEKİ', NULL, NULL, 1, 0, '2026-05-02 16:19:26', NULL),
(47, 'TUI MAGIC LIFE BELEK', 'KADRİYE', NULL, NULL, 1, 0, '2026-05-03 07:44:16', '2026-06-05 08:36:19'),
(48, 'DREAM WORLD PALACE', 'ÇOLAKLI', NULL, NULL, 1, 0, '2026-05-03 07:50:05', NULL),
(49, 'AYDIN BEY QUEEN\'S PALACE', 'KADRİYE', NULL, NULL, 1, 0, '2026-05-03 09:50:15', NULL),
(50, 'DELPHIN IMPERIAL', 'KUNDU', NULL, NULL, 1, 0, '2026-05-03 10:36:18', NULL),
(51, 'CRYSTAL WATER WORLD AQUA COLLECTION', 'BOĞAZKENT', NULL, NULL, 1, 0, '2026-05-03 10:52:20', NULL),
(52, 'ADONIS HOTEL', 'ŞİRİNYALI', NULL, NULL, 1, 0, '2026-05-03 11:03:43', NULL),
(53, 'VIKINGEN INFINITY RESORT', 'TÜRKLER', NULL, 105.00, 1, 0, '2026-05-03 12:18:10', NULL),
(54, 'SELECTUM LUXURY RESORT', 'BELEK', NULL, 30.00, 1, 0, '2026-05-03 12:22:41', NULL),
(55, 'DOBEDAN WORLD KEMER', 'KİRİŞ', NULL, 66.00, 1, 0, '2026-05-03 12:44:58', NULL),
(56, 'DIAMOND DELUXE', 'GÜNDOĞDU', NULL, NULL, 1, 0, '2026-05-03 18:40:13', NULL),
(57, 'TUI MAGIC LIFE MASMAVI', 'BELEK', NULL, NULL, 1, 0, '2026-05-03 19:43:18', NULL),
(58, 'SIDE ROYAL STYLE', 'EVRENSEKİ', NULL, 57.00, 1, 0, '2026-05-03 19:48:37', NULL),
(59, 'BOSPHORUS HOTEL', 'SORGUN', NULL, NULL, 1, 0, '2026-05-04 07:36:58', NULL),
(60, 'GRAND SEKER OTEL', 'EVRENSEKİ', NULL, 57.00, 1, 0, '2026-05-04 10:51:15', NULL),
(61, 'ADORE HOTEL', 'KADRİYE', NULL, NULL, 1, 0, '2026-05-04 10:59:59', NULL),
(62, 'SEADEN SEA WORLD', 'KIZILAĞAÇ', NULL, NULL, 1, 0, '2026-05-04 11:15:52', NULL),
(63, 'AQI PEGASOS ROYAL', 'AVSALLAR', NULL, NULL, 1, 0, '2026-05-04 12:21:16', NULL),
(64, 'FETHİYE MERKEZ', 'FETHİYE', NULL, NULL, 1, 0, '2026-05-05 07:33:14', NULL),
(65, 'SIDE AMOUR HOTEL', 'SİDE', NULL, NULL, 1, 0, '2026-05-05 09:31:27', NULL),
(66, 'MANAVGAT', 'MERKEZ', NULL, NULL, 1, 0, '2026-05-05 09:47:12', NULL),
(67, 'TAÇ PREMIER HOTEL', 'ALANYA', NULL, 123.00, 1, 0, '2026-05-05 10:25:51', NULL),
(68, 'GRANADA LUXURY BELEK', 'BELEK', NULL, NULL, 1, 0, '2026-05-05 13:12:22', NULL),
(69, 'SWANDOR KEMER', 'KİRİŞ', NULL, NULL, 1, 0, '2026-05-05 13:20:08', NULL),
(70, 'SUNLIGHT GARDEN SIDE', 'SİDE', NULL, NULL, 1, 0, '2026-05-05 13:28:35', NULL),
(71, 'SEADEN COROLLA HOTEL', 'SİDE', NULL, NULL, 1, 0, '2026-05-05 13:39:30', NULL),
(72, 'SWANDOR TOPKAPI PALACE', 'KUNDU', NULL, NULL, 1, 0, '2026-05-05 13:54:21', NULL),
(73, 'THE LAND OF LEGENDS KINGDOM HOTEL', 'KADRİYE', NULL, NULL, 1, 0, '2026-05-05 13:55:36', NULL),
(74, 'GRAND UYSAL BEACH', 'ALANYA', NULL, NULL, 1, 0, '2026-05-05 14:03:23', NULL),
(75, 'DIAMOND PREMIUM', 'SORGUN', NULL, NULL, 1, 0, '2026-05-05 14:37:22', NULL),
(76, 'DOBEDAN EXCLUSIVE BELEK', 'BOĞAZKENT', NULL, NULL, 1, 0, '2026-05-05 15:15:48', NULL),
(77, 'JUSTINIANO CLUB PARK CONTI', 'OKURCALAR', NULL, NULL, 1, 0, '2026-05-05 15:25:06', NULL),
(78, 'DREAM WORLD HILL', 'KUMKÖY', NULL, NULL, 1, 0, '2026-05-05 15:28:52', NULL),
(79, 'KIMERES PARK HOLIDAY VILLAGA', 'GÖYNÜK', NULL, NULL, 1, 0, '2026-05-05 15:36:18', NULL),
(80, 'KIMEROS PARK HOLIDAY VILLAGA', 'GÖYNÜK', NULL, NULL, 1, 0, '2026-05-05 15:37:23', NULL),
(81, 'BARUT HEMERA', 'SİDE', NULL, NULL, 1, 0, '2026-05-05 15:49:19', NULL),
(82, 'MELAS LARA', 'KUNDU', NULL, NULL, 1, 0, '2026-05-05 15:57:29', NULL),
(83, 'BLUE WATERS CLUB', 'SORGUN', NULL, NULL, 1, 0, '2026-05-05 16:46:13', NULL),
(84, 'LUMOS DELUXE RESORT', 'KARGICAK', NULL, NULL, 1, 0, '2026-05-05 16:52:50', NULL),
(85, 'HAYDAR PASHA PALACE', 'TÜRKLER', NULL, NULL, 1, 0, '2026-05-05 18:00:26', NULL),
(86, 'NOVUM GARDEN', 'GÜNDOĞDU', NULL, NULL, 1, 0, '2026-05-05 20:22:14', NULL),
(87, 'QUEENS PARK GÖYNÜK', 'GÖYNÜK', NULL, NULL, 1, 0, '2026-05-05 20:26:15', NULL),
(88, 'PALOMA GRIDA', 'BELEK', NULL, NULL, 1, 0, '2026-05-05 20:30:45', NULL),
(89, 'SIDE PRENSES HOTEL', 'SİDE', NULL, NULL, 1, 0, '2026-05-05 20:46:12', NULL),
(90, 'RIXOS SUNGATE BELDIBI', 'BELDIBI', NULL, NULL, 1, 0, '2026-05-05 20:54:23', NULL),
(91, 'SEADEN DE MAR', 'KIZILAĞAÇ', NULL, NULL, 1, 0, '2026-05-05 21:04:23', NULL),
(92, 'ROYAL TAJ MAHAL', 'EVRENSEKİ', NULL, NULL, 1, 0, '2026-05-05 21:11:09', NULL),
(93, 'NIRVANA DOLCE VITA', 'TEKİROVA', NULL, NULL, 1, 0, '2026-05-06 14:04:21', NULL),
(94, 'ADALYA ELITE LARA', 'KUNDU', NULL, NULL, 1, 0, '2026-05-07 06:01:55', NULL),
(95, 'DOSINIA LUXURY RESORT', 'BELDİBİ', NULL, NULL, 1, 0, '2026-05-07 14:53:39', NULL),
(96, 'LIMAK ARCADIA SPORT BELEK', 'BELEK', NULL, NULL, 1, 0, '2026-05-07 15:07:23', NULL),
(97, 'MIRADA DEL MAR', 'GÖYNÜK', NULL, NULL, 1, 0, '2026-05-07 21:44:00', NULL),
(98, 'EFTALIA BLUE', 'TÜRKLER', NULL, NULL, 1, 0, '2026-05-08 06:12:52', NULL),
(99, 'LIU RESORT', 'GÜNDOĞDU', NULL, NULL, 1, 0, '2026-05-08 19:03:31', NULL),
(100, 'KLEOPATRA DREAMS BEACH', 'ALANYA', NULL, NULL, 1, 0, '2026-05-08 19:10:21', NULL),
(101, 'DELPHIN DIVA', 'KUNDU', NULL, NULL, 1, 0, '2026-05-09 19:37:10', NULL),
(102, 'MELAS RESORT', 'SİDE', NULL, NULL, 1, 0, '2026-05-10 11:33:47', NULL),
(103, 'LARA GARDEN', NULL, NULL, NULL, 1, 0, '2026-05-10 17:27:43', NULL),
(104, 'TRENDY VERBANA BEACH', 'EVRENSEKİ', NULL, NULL, 1, 0, '2026-05-10 17:44:04', NULL),
(105, 'BELEK MERKEZ', 'BELEK', NULL, NULL, 1, 0, '2026-05-12 06:27:46', NULL),
(106, 'SUNTHALI HOTEL', 'ÇOLAKLI', NULL, NULL, 1, 0, '2026-05-12 06:39:58', NULL),
(107, 'CATS GARDEN HOTEL', 'SİDE', NULL, NULL, 1, 0, '2026-05-12 09:04:01', NULL),
(108, 'ALEGRİA HOTEL', NULL, NULL, NULL, 1, 0, '2026-05-13 08:30:19', NULL),
(109, 'ALERIA BELPORT', 'BELDİBİ', NULL, NULL, 1, 0, '2026-05-13 08:42:44', NULL),
(110, 'GRAVEL SELECT HOTEL', 'KEMER', NULL, NULL, 1, 0, '2026-05-13 08:46:10', NULL),
(111, 'SEADEN VALENTINE', 'SİDE', NULL, NULL, 1, 0, '2026-05-14 09:36:20', NULL),
(112, 'KİLİKYA PALACE', 'GÖYNÜK', NULL, NULL, 1, 0, '2026-05-14 14:46:34', NULL),
(113, 'ARNOR DELUXE', 'SİDE', NULL, NULL, 1, 0, '2026-05-15 12:06:45', NULL),
(114, 'WYNDHAM GARDEN LARA', 'KUNDU', NULL, NULL, 1, 0, '2026-05-15 12:16:29', NULL),
(115, 'SEADEN QUALITY', 'SİDE', NULL, NULL, 1, 0, '2026-05-15 19:54:00', NULL),
(116, 'WATER SIDE HOTEL', 'SORGUN', NULL, NULL, 1, 0, '2026-05-16 17:45:27', NULL),
(117, 'SELECTUM FAMILY RESORT SIDE', 'KIZILAĞAÇ', NULL, NULL, 1, 0, '2026-05-16 17:49:57', NULL),
(118, 'ASTERIA FAMILY RESORT BELEK', 'BELEK', NULL, NULL, 1, 0, '2026-05-16 17:59:45', NULL),
(119, 'IC SANTAI FAMILY', 'KADRİYE', NULL, NULL, 1, 0, '2026-05-17 18:14:09', NULL),
(120, 'KHAYA RESORT AQUA', 'ALANYA', NULL, NULL, 1, 0, '2026-05-17 18:27:00', NULL),
(121, 'SIDE STAR ELEGANCE', 'SİDE', NULL, NULL, 1, 0, '2026-05-17 18:30:48', NULL),
(122, 'NOVA PARK', 'KUMKÖY', NULL, NULL, 1, 0, '2026-05-18 18:45:51', NULL),
(123, 'NICKELODEON HOTEL', 'KADRİYE', NULL, NULL, 1, 0, '2026-05-18 20:01:52', NULL),
(124, 'KAYA SIDE HOTEL', 'SORGUN', NULL, NULL, 1, 0, '2026-05-19 14:14:46', NULL),
(125, 'KAMELYA SELIN HOTEL', 'ÇOLAKLI', NULL, NULL, 1, 0, '2026-05-19 18:21:54', NULL),
(126, 'HANE FAMILY RESORT', 'EVRENSEKİ', NULL, NULL, 1, 0, '2026-05-21 11:18:15', NULL),
(127, 'THE X BELEK', 'BELEK', NULL, NULL, 1, 0, '2026-05-21 11:24:04', NULL),
(128, 'Castello Hotel & Aparts', 'KEMER', NULL, NULL, 1, 0, '2026-05-22 07:35:44', NULL),
(129, 'MAXX ROYAL BELEK', 'BELEK', NULL, NULL, 1, 0, '2026-05-23 12:30:54', NULL),
(130, 'ÖZKAYMAK MARINA', 'KEMER', NULL, NULL, 1, 0, '2026-05-23 12:35:31', NULL),
(131, 'TRENDY LARA', 'KUNDU', NULL, NULL, 1, 0, '2026-05-23 14:24:43', NULL),
(132, 'SIDE KONUM', 'SIDE', NULL, NULL, 1, 0, '2026-05-23 14:27:19', NULL),
(133, 'KARİKATÜR HOTEL', 'ALANYA', NULL, NULL, 1, 0, '2026-05-24 10:06:05', NULL),
(134, 'SULTAN OF DREAMS', 'KIZILAĞAÇ', NULL, NULL, 1, 0, '2026-05-24 10:11:33', NULL),
(135, 'SIDE GOLDEN ROCK', 'SİDE', NULL, NULL, 1, 0, '2026-05-24 10:18:53', NULL),
(136, 'CLUB NENA HOTEL', 'KIZILAĞAÇ', NULL, NULL, 1, 0, '2026-05-24 12:34:43', NULL),
(137, 'FAME RESIDANCE GÖYNÜK', 'GÖYNÜK', NULL, NULL, 1, 0, '2026-05-24 18:09:59', NULL),
(138, 'TRANSATLANTIK', 'GÖYNÜK', NULL, NULL, 1, 0, '2026-05-25 07:22:18', NULL),
(139, 'ACARKENT', 'SİDE', NULL, NULL, 1, 0, '2026-05-25 09:00:45', NULL),
(140, 'PALM WORLD', 'EVRENSEKİ', NULL, NULL, 1, 0, '2026-05-25 19:30:44', NULL),
(141, 'FAME RESIDANCE KEMER', 'KEMER', NULL, NULL, 1, 0, '2026-05-25 19:49:51', NULL),
(142, 'TITANIC GOLF RESORT', 'KADRİYE', NULL, NULL, 1, 0, '2026-05-26 10:31:58', NULL),
(143, 'CRYSTAL PARAISO AQUA COLLECTION', 'BOĞAZKENT', NULL, NULL, 1, 0, '2026-05-26 12:56:02', NULL),
(144, 'AKRA SORGUN', 'SORGUN', NULL, NULL, 1, 0, '2026-05-28 17:59:20', NULL),
(145, 'SATURUN PALACE', 'KUNDU', NULL, NULL, 1, 0, '2026-05-28 18:04:02', NULL),
(146, 'ÖZ HOTELS İNCEKUM', 'İNCEKUM', NULL, NULL, 1, 0, '2026-05-28 18:10:39', NULL),
(147, 'EFTALIA OCEAN', 'TÜRKLER', NULL, NULL, 1, 0, '2026-05-29 12:21:41', NULL),
(148, 'BEGONVILLE APART', 'SİDE', NULL, NULL, 1, 0, '2026-05-29 12:24:34', NULL),
(149, 'MYLOME LUXURY HOTEL', 'OKURCALAR', NULL, NULL, 1, 0, '2026-05-31 16:14:26', NULL),
(150, 'ROYAL ATLANTIS BEACH', 'GÜNDOĞDU', NULL, NULL, 1, 0, '2026-05-31 16:19:06', NULL),
(151, 'JADORE DELUXE', 'SORGUN', NULL, NULL, 1, 0, '2026-05-31 20:08:04', NULL),
(152, 'MEGASARAY CLUB BELEK', 'KADRİYE', NULL, NULL, 1, 0, '2026-06-01 11:39:36', NULL),
(153, 'PINE BEACH', 'BELEK', NULL, NULL, 1, 0, '2026-06-01 11:43:00', NULL),
(154, 'HOTEL CESAR', 'SİDE', NULL, NULL, 1, 0, '2026-06-01 14:45:35', NULL),
(155, 'AQI PEGASOS WORLD', 'SORGUN', NULL, NULL, 1, 0, '2026-06-01 19:08:51', NULL),
(156, 'BELMONDO SUITES', 'KALEİÇİ', NULL, NULL, 1, 0, '2026-06-02 09:02:11', NULL),
(157, 'ALETRIS DELUXE', 'GÜNDOĞDU', NULL, NULL, 1, 0, '2026-06-02 13:41:20', NULL),
(158, 'PALOME PERISSIA', 'SİDE', NULL, NULL, 1, 0, '2026-06-02 19:14:36', NULL),
(159, 'BALMY BEACH KEMER', 'KEMER', NULL, NULL, 1, 0, '2026-06-03 06:39:32', NULL),
(160, 'MIAROSA KEMER BEACH', 'KİRİŞ', NULL, NULL, 1, 0, '2026-06-03 06:43:37', NULL),
(161, 'PALOMA OCEANA', 'KUMKÖY', NULL, NULL, 1, 0, '2026-06-03 17:15:00', NULL),
(162, 'SUSESI HOTEL', 'BELEK', NULL, NULL, 1, 0, '2026-06-03 20:35:13', NULL),
(163, 'SERENIS HOTEL', NULL, NULL, NULL, 1, 0, '2026-06-04 13:05:23', NULL),
(164, 'ROYAL SEGINUS LARA', 'KUNDU', NULL, NULL, 1, 0, '2026-06-04 18:27:53', NULL),
(165, 'CLUBMED PALMIYE KEMER', 'KEMER', NULL, NULL, 1, 0, '2026-06-05 08:15:42', NULL),
(166, 'CALYPTUS KIRMAN PREMIUM', 'KUMKÖY', NULL, NULL, 1, 0, '2026-06-05 08:27:47', NULL),
(167, 'MERYAN HOTEL', 'OKURCALAR', NULL, NULL, 1, 0, '2026-06-05 15:45:41', NULL),
(168, 'ORFEUS PARK HOTEL', 'ÇOLAKLI', NULL, NULL, 1, 0, '2026-06-05 19:34:21', NULL),
(169, 'KREMLIN PALACE', 'KUNDU', NULL, NULL, 1, 0, '2026-06-05 20:17:34', NULL),
(170, 'ANTALYA OTOGAR', 'ANTALYA', NULL, NULL, 1, 0, '2026-06-06 12:39:40', NULL),
(171, 'CALIDO SOL HOTEL', 'OKURCALAR', NULL, NULL, 1, 0, '2026-06-06 12:43:14', NULL),
(172, 'SIDE VILLAGE', 'SİDE', NULL, NULL, 1, 0, '2026-06-06 12:57:38', NULL),
(173, 'ROYAL ALHAMBRA PALACE', 'ÇOLAKLI', NULL, NULL, 1, 0, '2026-06-06 13:14:59', NULL),
(174, 'LONICERA RESORT', 'TÜRKLER', NULL, NULL, 1, 0, '2026-06-07 15:57:40', NULL),
(175, 'ARMAS LIFE HOTEL', 'BOĞAZKENT', NULL, NULL, 1, 0, '2026-06-07 20:39:17', NULL),
(176, 'BELEK BEACH RESORT', 'BOĞAZKENT', NULL, NULL, 1, 0, '2026-06-08 15:11:57', NULL),
(177, 'FAME RESIDANCE LARA', 'KUNDU', NULL, NULL, 1, 0, '2026-06-09 11:52:49', NULL),
(178, 'CALIDO MARIS', 'ÇENGER', NULL, NULL, 1, 0, '2026-06-09 12:06:51', NULL),
(179, 'SIDE MOON PALACE', 'SORGUN', NULL, NULL, 1, 0, '2026-06-10 14:47:54', NULL),
(180, 'A HOTEL SIDE', 'SİDE', NULL, NULL, 1, 0, '2026-06-10 15:13:33', NULL),
(181, 'LAVINIA APART HOTEL', 'ALANYA', NULL, NULL, 1, 0, '2026-06-11 11:02:45', NULL),
(182, 'SEADEN SEA PLANET', 'KIZILOT', NULL, NULL, 1, 0, '2026-06-11 15:06:28', NULL),
(183, 'KEMER MERKEZ', 'KEMER', NULL, NULL, 1, 0, '2026-06-11 21:02:51', NULL),
(184, 'AZURA WORLD', 'TÜRKLER', NULL, NULL, 1, 0, '2026-06-13 09:34:03', NULL),
(185, 'GALA RESIDANCE', 'SİDE', NULL, NULL, 1, 0, '2026-06-13 15:31:45', NULL),
(186, 'CONCORDE DELUXE', 'KUNDU', NULL, NULL, 1, 0, '2026-06-13 16:07:05', NULL),
(187, 'COOKS CLUB ALANYA', 'ALANYA', NULL, NULL, 1, 0, '2026-06-14 09:07:10', NULL),
(188, 'CASTIVAL HOTEL', 'KUMKÖY', NULL, NULL, 1, 0, '2026-06-14 09:09:21', NULL);

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
(22, '1777771258_acd08717c84eb5a1.png', '826-8261315_sprinter-mercedes-benz-sprinter-png.png', 'general/1777771258_acd08717c84eb5a1.png', 'image', 'image/png', 95276, 706, 317, NULL, NULL, 'general', 2, '2026-05-03 01:20:58');

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
(10, 'Sezer BOZ', '+905342449748', NULL, 1, '2026-04-28 23:06:09', '2026-05-10 14:47:22'),
(11, 'Ali Seydi Tuluk', '5320645407', NULL, 1, '2026-04-29 09:53:47', NULL),
(12, 'Emre Boz', '5469406233', NULL, 1, '2026-04-30 12:05:52', NULL),
(14, 'Uğur Aşanel', '5068590550', NULL, 1, '2026-05-03 13:08:07', NULL),
(15, 'Beyhan Suluova', '5357910875', NULL, 1, '2026-05-03 13:14:10', NULL),
(16, 'Mustafa Bayrak', '5511703350', NULL, 1, '2026-05-03 21:43:01', NULL),
(17, 'Murat Yılmaz', '531 240 37 50', NULL, 1, '2026-05-03 22:29:34', NULL),
(18, 'Can Küpelioğlu', '5308940426', NULL, 1, '2026-05-03 22:31:22', NULL),
(20, 'Önder Günal', '+905374649374', NULL, 1, '2026-05-03 22:34:13', '2026-05-10 20:54:56'),
(22, 'Mehmet Paşa', '5525685002', NULL, 1, '2026-05-03 23:28:21', NULL),
(23, 'Mehmet Ali Öztaş', '5070228621', NULL, 1, '2026-05-05 10:26:19', NULL),
(25, 'Ali Taş', '5521509551', NULL, 1, '2026-05-05 18:44:31', NULL),
(26, 'Emre Bayrak', '543 109 72 07', NULL, 1, '2026-05-05 21:44:56', NULL),
(27, 'Ünal Hartamacı', '05323137525', NULL, 1, '2026-05-06 12:48:22', '2026-05-06 12:49:02'),
(28, 'Murat Şanlı', '5324343388', NULL, 1, '2026-05-06 20:50:54', NULL),
(29, 'Mehmet Ali Mutlu', '5308171120', NULL, 1, '2026-05-06 21:04:12', NULL),
(30, 'Hasan Gül', '5313853908', NULL, 1, '2026-05-07 15:06:18', NULL),
(31, 'Mustafa Esen', '5534523611', NULL, 1, '2026-05-07 21:41:28', NULL),
(32, 'Ömer Can Tülek', '5327990789', NULL, 1, '2026-05-08 00:33:58', NULL),
(33, 'Hasan Gül', '5313853908', NULL, 1, '2026-05-08 00:34:46', NULL),
(34, 'Abdülrezzak Yeşil', '+905548313363', NULL, 1, '2026-05-08 12:16:23', '2026-05-10 14:46:13'),
(35, 'Kadir aydın', '5413591113', NULL, 1, '2026-05-08 17:20:36', NULL),
(36, 'Yasin Kuru', '5334203199', NULL, 1, '2026-05-08 17:22:03', NULL),
(37, 'Sait Uğurlu', '5365712763', NULL, 1, '2026-05-08 17:55:10', NULL),
(38, 'Mehmet Akif Esoğlu', '5319732994', NULL, 1, '2026-05-08 21:35:43', NULL),
(39, 'Yılmaz Acar', '5435672506', NULL, 1, '2026-05-08 22:27:10', NULL),
(40, 'Osman Demir', '5449426080', NULL, 1, '2026-05-09 10:13:43', NULL),
(41, 'Yahya Hamurcu', '5378313302', NULL, 1, '2026-05-09 12:21:48', NULL),
(42, 'Yunus Emre Kozanlı', '5302249792', NULL, 1, '2026-05-09 21:33:04', NULL),
(43, 'Adem Uğurlu', '+905326043480', NULL, 1, '2026-05-09 21:37:15', '2026-05-10 14:46:34'),
(44, 'Yüksel Bozkurt', '5379125077', NULL, 1, '2026-05-09 21:56:11', NULL),
(45, 'Ice travel', '5345540707', NULL, 1, '2026-05-09 22:47:01', NULL),
(46, 'Eyüp Aksu', '5312420079', NULL, 1, '2026-05-09 23:04:14', NULL),
(47, 'Süleyman Kıdık', '+905352352607', NULL, 1, '2026-05-10 14:26:17', '2026-05-12 10:17:26'),
(48, 'Sinan Adıyamanlı', NULL, NULL, 1, '2026-05-10 14:27:59', NULL),
(50, 'Fatih Çil', '+90 543 900 93 23', NULL, 1, '2026-05-10 21:05:35', NULL),
(51, 'Europa Vip', '+905530538311', NULL, 1, '2026-05-12 19:21:58', NULL),
(52, 'Ilyada Yıldız', '+905437856955', NULL, 1, '2026-05-12 19:35:47', NULL),
(53, 'Cemil Duruk', '+905357386425', NULL, 1, '2026-05-12 23:26:06', NULL),
(54, 'Ömer', '+905318660961', NULL, 1, '2026-05-12 23:43:36', NULL),
(55, 'Savaş Tunç', '+905344919758', NULL, 1, '2026-05-12 23:44:39', NULL),
(56, 'Ahmet Kocaman', '+905459017361', NULL, 1, '2026-05-13 15:45:05', NULL),
(57, 'Antya Turizm', '+90 546 557 12 20', NULL, 1, '2026-05-14 19:06:35', NULL),
(58, 'Ahmet Karadağlı', '+905056565790', NULL, 1, '2026-05-14 21:40:46', NULL),
(59, 'Yusuf Şanal', '+905457759180', NULL, 1, '2026-05-15 22:46:44', NULL),
(60, 'Turgay Hasırcı', '+905439260964', NULL, 1, '2026-05-15 22:50:18', NULL),
(61, 'Mehmet Eseoğlu', '+90 533 404 46 07', NULL, 1, '2026-05-17 12:57:13', NULL),
(62, 'Hüseyin Asar', '+905427469714', NULL, 1, '2026-05-17 20:58:39', NULL),
(63, 'Mehmet Zülfi Göktepe', '+905372824985', NULL, 1, '2026-05-17 22:12:56', NULL),
(64, 'Burhan Koyuncu', '+905451080207', NULL, 1, '2026-05-17 22:24:12', NULL),
(65, 'Veysel Çaçan', '+905539745433', NULL, 1, '2026-05-19 21:41:28', NULL),
(66, 'Necla Zorlu', '+905523777071', NULL, 1, '2026-05-19 21:42:40', NULL),
(67, 'Tolgahan Güven', '+905436522909', NULL, 1, '2026-05-19 22:50:10', NULL),
(68, 'Mega Vip', '+905350868578', NULL, 1, '2026-05-21 14:12:05', NULL),
(69, 'Hakan Akgül', '+90 538 592 82 79', NULL, 1, '2026-05-22 10:41:55', NULL),
(70, 'İdris Şanlı', '+90 552 235 08 21', NULL, 1, '2026-05-23 20:52:13', NULL),
(71, 'Fuat Çetin', '+905387616902', NULL, 1, '2026-05-24 13:25:54', NULL),
(72, 'Faruk Sevil', '+905051525602', NULL, 1, '2026-05-24 13:27:43', NULL),
(73, 'Yusuf Oğuz', '+905372844170', NULL, 1, '2026-05-24 16:54:19', NULL),
(74, 'Aykut Ataman', '+905332169454', NULL, 1, '2026-05-24 21:04:39', NULL),
(75, 'Süleyman Tik', '+905415313529', NULL, 1, '2026-05-24 21:23:08', NULL),
(76, 'Sefa Bingöl', '+905414685321', NULL, 1, '2026-05-24 21:35:52', NULL),
(77, 'Buğra Tunç', '+905384606029', NULL, 1, '2026-05-25 10:16:07', NULL),
(78, 'Ali Gür', '+905077526537', NULL, 1, '2026-05-25 10:17:09', NULL),
(79, 'Zeynel Temel', '+905427391691', NULL, 1, '2026-05-25 10:37:40', NULL),
(80, 'Sezgin Korkmaz', '+90 540 490 71 75', NULL, 1, '2026-05-25 10:38:57', NULL),
(81, 'Flamingo Vip', '+905316673023', NULL, 1, '2026-05-25 20:07:20', NULL),
(82, 'Erdal Özbey', '+905428469606', NULL, 1, '2026-05-28 21:29:13', NULL),
(83, 'Orhan Güven', '+905416769904', NULL, 1, '2026-05-29 10:27:42', NULL),
(84, 'Naci Çetin', '+905419036507', NULL, 1, '2026-05-29 14:50:48', NULL),
(85, 'Uğur Kurtman', '+905456724623', NULL, 1, '2026-05-29 14:51:57', NULL),
(86, 'Mehmet çelik', '+90 0538 286 1883', NULL, 1, '2026-06-01 20:53:19', NULL),
(87, 'Ümit Ekin', '+905362261591', NULL, 1, '2026-06-01 22:28:43', NULL),
(88, 'Berkay İpek', '+905446217112', NULL, 1, '2026-06-02 22:37:15', NULL),
(89, 'Bülent Şahin', '+905064971433', NULL, 1, '2026-06-03 21:00:42', NULL),
(90, 'Ensar Yamankaya', '+905456155796', NULL, 1, '2026-06-04 10:11:56', NULL),
(91, 'Enis Korkmaz', '+905437973116', NULL, 1, '2026-06-04 16:39:28', NULL),
(92, 'Fahri Genç', '+905072757182', NULL, 1, '2026-06-04 21:34:58', NULL),
(93, 'İbrahim Kıyıcı', '+905466990123', NULL, 1, '2026-06-05 15:59:49', NULL),
(94, 'Arda Coşkun', '+905457759440', NULL, 1, '2026-06-05 17:49:56', NULL),
(95, 'Osman Demir', '+905448143595', NULL, 1, '2026-06-05 18:30:11', NULL),
(96, 'Ahmet Katıl', '+905433520721', NULL, 1, '2026-06-06 00:23:51', NULL),
(97, 'Mehmet Akif Eseoğlu', '+905319732924', NULL, 1, '2026-06-06 09:43:38', NULL),
(98, 'Abdullah Akgün', '+905418031710', NULL, 1, '2026-06-06 16:09:09', '2026-06-14 20:46:08'),
(99, 'Doğan Bulut', '+905458612332', NULL, 1, '2026-06-06 20:09:53', NULL),
(100, 'Oğuzhan Bedir', '+905362342345', NULL, 1, '2026-06-06 21:06:55', NULL),
(101, 'Özgür Uçak', '+905326225831', NULL, 1, '2026-06-07 19:22:59', NULL),
(102, 'Mesut Yatar', '+90 506 248 87 93', NULL, 1, '2026-06-07 19:26:41', NULL),
(103, 'Kadir Oğuz', '+905376443677', NULL, 1, '2026-06-07 23:48:08', NULL),
(104, 'Murat Öndeğirmen', '+905324463827', NULL, 1, '2026-06-08 23:27:46', NULL),
(105, 'Abdülkadir Taştan', '+905524866363', NULL, 1, '2026-06-08 23:28:53', NULL),
(106, 'Samet Şimşek', '+905426358704', NULL, 1, '2026-06-08 23:29:35', NULL),
(107, 'Ali genç', '+90 543 551 89 23', NULL, 1, '2026-06-09 17:25:50', NULL),
(108, 'Ebubekir Varlık', '+905015331366', NULL, 1, '2026-06-10 18:43:30', NULL),
(109, 'Levent Mulla Çeçen', '+905548374097', NULL, 1, '2026-06-11 13:42:16', NULL),
(110, 'Zeus vip', '+905367088026', NULL, 1, '2026-06-11 23:04:10', NULL),
(111, 'Mert Yıldız', '+905415044306', NULL, 1, '2026-06-11 23:05:34', NULL),
(112, 'Mustafa Edirneli', '+90 553 402 46 56', NULL, 1, '2026-06-11 23:07:18', NULL),
(113, 'Veli Zorlu', '+905072130874', NULL, 1, '2026-06-12 15:27:27', NULL),
(114, 'Suat Tosun', '+905354328433', NULL, 1, '2026-06-12 17:12:13', NULL),
(115, 'Abdussamed yıldız', '0532 694 15 12', NULL, 1, '2026-06-14 17:55:04', NULL);

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

--
-- Tablo döküm verisi `page_translations`
--

INSERT INTO `page_translations` (`id`, `page_id`, `language_code`, `title`, `slug`, `content`, `excerpt`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(7, 1, 'tr', 'Ana Sayfa', '/', NULL, NULL, NULL, NULL, NULL, '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(8, 1, 'de', 'Ana Sayfa', 'home-de', NULL, NULL, NULL, NULL, NULL, '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(9, 1, 'en', 'Ana Sayfa', 'home-en', NULL, NULL, NULL, NULL, NULL, '2026-05-03 03:35:27', '2026-05-03 03:35:27');

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
(5, 1, 'featured_tours', 'tours', 'Featured Tours', '', '', '{\"limit\": \"6\", \"view_all_url\": \"/turlar\", \"show_view_all\": \"1\", \"show_featured_only\": \"1\"}', '', '', 4, 1, '2026-01-21 18:27:13', '2026-05-03 03:36:04'),
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
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `section_translations`
--

INSERT INTO `section_translations` (`id`, `section_id`, `language_code`, `title`, `subtitle`, `content`, `settings`, `created_at`, `updated_at`) VALUES
(1, 2, 'tr', 'Slider Ana Başlık', 'Slider Alt Başlık', NULL, '{\"form_title\": \"Tatili Planla\", \"button1_url\": \"#\", \"button2_url\": \"#\", \"button1_text\": \"Tura Başla\", \"button2_text\": \"Turları Keşfet\", \"label_adults\": \"Yetişkin \", \"label_children\": \"Çocuk\", \"label_departure\": \"Gidiş Tarihi\", \"form_button_text\": \"Buton Metni Kaydet\", \"label_destination\": \"Hedef\", \"show_booking_form\": \"1\", \"placeholder_destination\": \"Hedefini Seç\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(2, 2, 'de', 'Slider Ana Başlık', 'Slider Alt Başlık', NULL, '{\"form_title\": \"Tatili Planla\", \"button1_url\": \"#\", \"button2_url\": \"#\", \"button1_text\": \"Tura Başla\", \"button2_text\": \"Turları Keşfet\", \"label_adults\": \"Yetişkin \", \"label_children\": \"Çocuk\", \"label_departure\": \"Gidiş Tarihi\", \"form_button_text\": \"Buton Metni Kaydet\", \"label_destination\": \"Hedef\", \"show_booking_form\": \"1\", \"placeholder_destination\": \"Hedefini Seç\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(3, 2, 'en', 'Slider Ana Başlık', 'Slider Alt Başlık', NULL, '{\"form_title\": \"Tatili Planla\", \"button1_url\": \"#\", \"button2_url\": \"#\", \"button1_text\": \"Tura Başla\", \"button2_text\": \"Turları Keşfet\", \"label_adults\": \"Yetişkin \", \"label_children\": \"Çocuk\", \"label_departure\": \"Gidiş Tarihi\", \"form_button_text\": \"Buton Metni Kaydet\", \"label_destination\": \"Hedef\", \"show_booking_form\": \"1\", \"placeholder_destination\": \"Hedefini Seç\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(4, 3, 'tr', 'Ana Başlık', NULL, '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>', '{\"stats\": [{\"label\": \"Happy Travelers\", \"number\": \"1200\"}, {\"label\": \"Countries Covered\", \"number\": \"85\"}, {\"label\": \"Years Experience\", \"number\": \"15\"}], \"experience_text\": \"Years of Excellence\", \"experience_badge\": \"15+\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(5, 3, 'de', 'Ana Başlık', NULL, '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>', '{\"stats\": [{\"label\": \"Happy Travelers\", \"number\": \"1200\"}, {\"label\": \"Countries Covered\", \"number\": \"85\"}, {\"label\": \"Years Experience\", \"number\": \"15\"}], \"experience_text\": \"Years of Excellence\", \"experience_badge\": \"15+\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(6, 3, 'en', 'Ana Başlık', NULL, '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>', '{\"stats\": [{\"label\": \"Happy Travelers\", \"number\": \"1200\"}, {\"label\": \"Countries Covered\", \"number\": \"85\"}, {\"label\": \"Years Experience\", \"number\": \"15\"}], \"experience_text\": \"Years of Excellence\", \"experience_badge\": \"15+\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(7, 4, 'tr', 'Öne Çıkan Transferler', NULL, NULL, '{\"limit\": \"6\", \"show_featured_only\": \"1\"}', '2026-05-03 03:35:27', '2026-05-03 03:36:04'),
(8, 4, 'de', 'Featured Destinations', NULL, NULL, '{\"limit\": \"4\", \"show_featured_only\": \"1\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(9, 4, 'en', 'Featured Destinations', NULL, NULL, '{\"limit\": \"4\", \"show_featured_only\": \"1\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(10, 5, 'tr', 'Featured Tours', NULL, NULL, '{\"limit\": \"6\", \"view_all_url\": \"/turlar\", \"show_view_all\": \"1\", \"show_featured_only\": \"1\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(11, 5, 'de', 'Featured Tours', NULL, NULL, '{\"limit\": \"6\", \"view_all_url\": \"/turlar\", \"show_view_all\": \"1\", \"show_featured_only\": \"1\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(12, 5, 'en', 'Featured Tours', NULL, NULL, '{\"limit\": \"6\", \"view_all_url\": \"/turlar\", \"show_view_all\": \"1\", \"show_featured_only\": \"1\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(13, 6, 'tr', 'Testimonials', NULL, NULL, '{\"limit\": \"5\", \"autoplay_delay\": \"5000\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(14, 6, 'de', 'Testimonials', NULL, NULL, '{\"limit\": \"5\", \"autoplay_delay\": \"5000\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(15, 6, 'en', 'Testimonials', NULL, NULL, '{\"limit\": \"5\", \"autoplay_delay\": \"5000\"}', '2026-05-03 03:35:27', '2026-05-03 03:35:27'),
(16, 7, 'tr', 'Discover Your Next Adventure', 'Limited Time Offer', 'Unlock incredible destinations with our specially curated travel packages. From exotic beaches to mountain peaks, your perfect getaway awaits.', '{\"phone\": \"+1 (555) 123-456\", \"button1_url\": \"/destinasyonlar\", \"button2_url\": \"/turlar\", \"button1_text\": \"Explore Now\", \"button2_text\": \"View Deals\", \"contact_label\": \"Need help choosing?\"}', '2026-05-03 03:35:28', '2026-05-03 03:35:28'),
(17, 7, 'de', 'Discover Your Next Adventure', 'Limited Time Offer', 'Unlock incredible destinations with our specially curated travel packages. From exotic beaches to mountain peaks, your perfect getaway awaits.', '{\"phone\": \"+1 (555) 123-456\", \"button1_url\": \"/destinasyonlar\", \"button2_url\": \"/turlar\", \"button1_text\": \"Explore Now\", \"button2_text\": \"View Deals\", \"contact_label\": \"Need help choosing?\"}', '2026-05-03 03:35:28', '2026-05-03 03:35:28'),
(18, 7, 'en', 'Discover Your Next Adventure', 'Limited Time Offer', 'Unlock incredible destinations with our specially curated travel packages. From exotic beaches to mountain peaks, your perfect getaway awaits.', '{\"phone\": \"+1 (555) 123-456\", \"button1_url\": \"/destinasyonlar\", \"button2_url\": \"/turlar\", \"button1_text\": \"Explore Now\", \"button2_text\": \"View Deals\", \"contact_label\": \"Need help choosing?\"}', '2026-05-03 03:35:28', '2026-05-03 03:35:28');

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

--
-- Tablo döküm verisi `translations`
--

INSERT INTO `translations` (`id`, `language_code`, `trans_group`, `trans_key`, `trans_value`, `created_at`) VALUES
(1, 'tr', 'header', 'menu_home', 'Ana Sayfa', '2026-05-03 02:22:54'),
(2, 'tr', 'header', 'menu_about', 'Hakkımızda', '2026-05-03 02:22:54'),
(3, 'tr', 'header', 'menu_destinations', 'Transferler', '2026-05-03 02:22:54'),
(4, 'tr', 'header', 'menu_tours', 'Turlar', '2026-05-03 02:22:54'),
(5, 'tr', 'header', 'menu_gallery', 'Galeri', '2026-05-03 02:22:54'),
(6, 'tr', 'header', 'menu_blog', 'Blog', '2026-05-03 02:22:54'),
(7, 'tr', 'header', 'menu_contact', 'İletişim', '2026-05-03 02:22:54'),
(8, 'tr', 'header', 'btn_get_started', 'Başlayın', '2026-05-03 02:22:54'),
(9, 'tr', 'footer', 'footer_links_title', 'Hızlı Linkler', '2026-05-03 02:22:54'),
(10, 'tr', 'footer', 'footer_services_title', 'Hizmetlerimiz', '2026-05-03 02:22:54'),
(11, 'tr', 'footer', 'footer_follow_title', 'Bizi Takip Edin', '2026-05-03 02:22:54'),
(12, 'tr', 'footer', 'footer_follow_text', 'Sosyal medya hesaplarımızdan bizi takip edin.', '2026-05-03 02:22:54'),
(13, 'tr', 'footer', 'footer_copyright', '© 2025 Tüm hakları saklıdır.', '2026-05-03 02:22:54'),
(14, 'tr', 'general', 'phone', 'Telefon', '2026-05-03 02:22:54'),
(15, 'tr', 'general', 'email', 'E-posta', '2026-05-03 02:22:54'),
(16, 'tr', 'general', 'read_more', 'Devamını Oku', '2026-05-03 02:22:54'),
(17, 'tr', 'general', 'book_now', 'Şimdi Rezervasyon Yap', '2026-05-03 02:22:54'),
(18, 'tr', 'general', 'view_details', 'Detayları Gör', '2026-05-03 02:22:54'),
(19, 'tr', 'general', 'explore_now', 'Keşfet', '2026-05-03 02:22:54'),
(20, 'tr', 'general', 'from', 'den başlayan', '2026-05-03 02:22:54'),
(21, 'tr', 'general', 'starting_from', 'Başlayan fiyat', '2026-05-03 02:22:54'),
(22, 'tr', 'general', 'packages', 'Paket', '2026-05-03 02:22:54'),
(23, 'tr', 'general', 'tours', 'Tur', '2026-05-03 02:22:54'),
(24, 'tr', 'general', 'days', 'Gün', '2026-05-03 02:22:54'),
(25, 'tr', 'general', 'nights', 'Gece', '2026-05-03 02:22:54'),
(26, 'tr', 'general', 'person', 'Kişi', '2026-05-03 02:22:54'),
(27, 'tr', 'general', 'faq', 'SSS', '2026-05-03 02:22:54'),
(28, 'tr', 'general', 'subscribe', 'Abone Ol', '2026-05-03 02:22:54'),
(29, 'tr', 'general', 'send_message', 'Mesaj Gönder', '2026-05-03 02:22:54'),
(30, 'en', 'header', 'menu_home', 'Home', '2026-05-03 02:22:54'),
(31, 'en', 'header', 'menu_about', 'About', '2026-05-03 02:22:54'),
(32, 'en', 'header', 'menu_destinations', 'Transfers', '2026-05-03 02:22:54'),
(33, 'en', 'header', 'menu_tours', 'Tours', '2026-05-03 02:22:54'),
(34, 'en', 'header', 'menu_gallery', 'Gallery', '2026-05-03 02:22:54'),
(35, 'en', 'header', 'menu_blog', 'Blog', '2026-05-03 02:22:54'),
(36, 'en', 'header', 'menu_contact', 'Contact', '2026-05-03 02:22:54'),
(37, 'en', 'header', 'btn_get_started', 'Get Started', '2026-05-03 02:22:54'),
(38, 'en', 'footer', 'footer_links_title', 'Quick Links', '2026-05-03 02:22:54'),
(39, 'en', 'footer', 'footer_services_title', 'Our Services', '2026-05-03 02:22:54'),
(40, 'en', 'footer', 'footer_follow_title', 'Follow Us', '2026-05-03 02:22:54'),
(41, 'en', 'footer', 'footer_follow_text', 'Follow us on social media for updates.', '2026-05-03 02:22:54'),
(42, 'en', 'footer', 'footer_copyright', '© 2025 All rights reserved.', '2026-05-03 02:22:54'),
(43, 'en', 'general', 'phone', 'Phone', '2026-05-03 02:22:54'),
(44, 'en', 'general', 'email', 'Email', '2026-05-03 02:22:54'),
(45, 'en', 'general', 'read_more', 'Read More', '2026-05-03 02:22:54'),
(46, 'en', 'general', 'book_now', 'Book Now', '2026-05-03 02:22:54'),
(47, 'en', 'general', 'view_details', 'View Details', '2026-05-03 02:22:54'),
(48, 'en', 'general', 'explore_now', 'Explore Now', '2026-05-03 02:22:54'),
(49, 'en', 'general', 'from', 'from', '2026-05-03 02:22:54'),
(50, 'en', 'general', 'starting_from', 'Starting from', '2026-05-03 02:22:54'),
(51, 'en', 'general', 'packages', 'Packages', '2026-05-03 02:22:54'),
(52, 'en', 'general', 'tours', 'Tours', '2026-05-03 02:22:54'),
(53, 'en', 'general', 'days', 'Days', '2026-05-03 02:22:54'),
(54, 'en', 'general', 'nights', 'Nights', '2026-05-03 02:22:54'),
(55, 'en', 'general', 'person', 'Person', '2026-05-03 02:22:54'),
(56, 'en', 'general', 'faq', 'FAQ', '2026-05-03 02:22:54'),
(57, 'en', 'general', 'subscribe', 'Subscribe', '2026-05-03 02:22:54'),
(58, 'en', 'general', 'send_message', 'Send Message', '2026-05-03 02:22:54'),
(59, 'de', 'header', 'menu_home', 'Startseite', '2026-05-03 02:22:54'),
(60, 'de', 'header', 'menu_about', 'Über uns', '2026-05-03 02:22:54'),
(61, 'de', 'header', 'menu_destinations', 'Transfers', '2026-05-03 02:22:54'),
(62, 'de', 'header', 'menu_tours', 'Touren', '2026-05-03 02:22:54'),
(63, 'de', 'header', 'menu_gallery', 'Galerie', '2026-05-03 02:22:54'),
(64, 'de', 'header', 'menu_blog', 'Blog', '2026-05-03 02:22:54'),
(65, 'de', 'header', 'menu_contact', 'Kontakt', '2026-05-03 02:22:54'),
(66, 'de', 'header', 'btn_get_started', 'Loslegen', '2026-05-03 02:22:54'),
(67, 'de', 'footer', 'footer_links_title', 'Schnelllinks', '2026-05-03 02:22:54'),
(68, 'de', 'footer', 'footer_services_title', 'Unsere Dienste', '2026-05-03 02:22:54'),
(69, 'de', 'footer', 'footer_follow_title', 'Folgen Sie uns', '2026-05-03 02:22:54'),
(70, 'de', 'footer', 'footer_follow_text', 'Folgen Sie uns in den sozialen Medien.', '2026-05-03 02:22:54'),
(71, 'de', 'footer', 'footer_copyright', '© 2025 Alle Rechte vorbehalten.', '2026-05-03 02:22:54'),
(72, 'de', 'general', 'phone', 'Telefon', '2026-05-03 02:22:54'),
(73, 'de', 'general', 'email', 'E-Mail', '2026-05-03 02:22:54'),
(74, 'de', 'general', 'read_more', 'Weiterlesen', '2026-05-03 02:22:54'),
(75, 'de', 'general', 'book_now', 'Jetzt buchen', '2026-05-03 02:22:54'),
(76, 'de', 'general', 'view_details', 'Details anzeigen', '2026-05-03 02:22:54'),
(77, 'de', 'general', 'explore_now', 'Entdecken', '2026-05-03 02:22:54'),
(78, 'de', 'general', 'from', 'ab', '2026-05-03 02:22:54'),
(79, 'de', 'general', 'starting_from', 'Ab', '2026-05-03 02:22:54'),
(80, 'de', 'general', 'packages', 'Pakete', '2026-05-03 02:22:54'),
(81, 'de', 'general', 'tours', 'Touren', '2026-05-03 02:22:54'),
(82, 'de', 'general', 'days', 'Tage', '2026-05-03 02:22:54'),
(83, 'de', 'general', 'nights', 'Nächte', '2026-05-03 02:22:54'),
(84, 'de', 'general', 'person', 'Person', '2026-05-03 02:22:54'),
(85, 'de', 'general', 'faq', 'FAQ', '2026-05-03 02:22:54'),
(86, 'de', 'general', 'subscribe', 'Abonnieren', '2026-05-03 02:22:54'),
(87, 'de', 'general', 'send_message', 'Nachricht senden', '2026-05-03 02:22:54'),
(88, 'tr', 'general', 'rezervasyon', 'rezervasyon', '2026-05-03 02:55:23'),
(89, 'de', 'general', 'rezervasyon', 'Reservierung', '2026-05-03 02:55:24'),
(90, 'en', 'general', 'rezervasyon', 'reservation', '2026-05-03 02:55:24');

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
(1, 'admin', 'admin@example.com', '$2y$10$YrXyhyMI/lDAJLPeHeWvQOg5nvvtB0UeR7.ZaoNl65JbM0BHS1cMy', 'Administrator', NULL, 'admin', 1, '2026-06-14 20:41:36', '2026-01-17 22:22:32', '2026-06-14 20:41:36'),
(2, 'sezer', 'sezerbz@gmail.com', '$2y$10$0uc7urfsSX3OcTqGIFgnj.DaMAlSfpsExjlKFprdbQgaGBxHoyRi6', 'Sezer Boz', NULL, 'admin', 1, '2026-06-14 20:16:16', '2026-02-13 22:09:08', '2026-06-14 20:16:16'),
(3, 'emre', 'emre@example.com', '$2y$10$Xy4R2jKE1l5gqHqkUTAmteiKbWCjjJ8vm8dV64bjtvHytvoBjpHBm', 'Emre Boz', NULL, 'admin', 1, '2026-06-14 14:29:22', '2026-05-03 09:25:05', '2026-06-14 14:29:22');

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
(1, 'Transporter', '', 6, 2, 1, 'general/1769542314_f74c434ba115caef.png', '[\"33\",\"34\",\"35\",\"36\",\"37\",\"38\",\"39\",\"40\",\"41\",\"42\",\"43\"]', '', NULL, NULL, 0, 1, 0, '2026-01-27 19:32:02', '2026-05-03 01:20:31'),
(2, 'Vip Mercedes Vito - Maybach', 'Vito', 6, 2, 2, 'general/1769543525_f883265a7fcb7bdd.png', '[\"33\",\"34\",\"35\",\"36\",\"37\",\"38\",\"39\",\"40\",\"41\",\"42\",\"43\"]', '', NULL, NULL, 0, 1, 0, '2026-01-27 19:52:11', '2026-04-26 07:03:34'),
(3, 'Sprinter/Crafter', '', 17, 17, 4, 'general/1777771258_acd08717c84eb5a1.png', '[\"33\",\"34\",\"35\",\"36\",\"37\",\"38\",\"39\",\"40\",\"41\",\"42\",\"43\"]', '', NULL, NULL, 0, 1, 0, '2026-01-29 22:57:11', '2026-05-03 01:21:19');

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
-- Tablo için indeksler `booking_drivers`
--
ALTER TABLE `booking_drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_driver` (`booking_id`,`driver_id`),
  ADD KEY `idx_booking_drivers_booking` (`booking_id`),
  ADD KEY `idx_booking_drivers_driver` (`driver_id`);

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
-- Tablo için indeksler `customers`
--
ALTER TABLE `customers`
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
-- Tablo için indeksler `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_drivers_vehicle` (`vehicle_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=577;

--
-- Tablo için AUTO_INCREMENT değeri `booking_alert_translations`
--
ALTER TABLE `booking_alert_translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Tablo için AUTO_INCREMENT değeri `booking_drivers`
--
ALTER TABLE `booking_drivers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Tablo için AUTO_INCREMENT değeri `booking_passengers`
--
ALTER TABLE `booking_passengers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2899;

--
-- Tablo için AUTO_INCREMENT değeri `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=329;

--
-- Tablo için AUTO_INCREMENT değeri `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Tablo için AUTO_INCREMENT değeri `destination_translations`
--
ALTER TABLE `destination_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=357;

--
-- Tablo için AUTO_INCREMENT değeri `destination_vehicles`
--
ALTER TABLE `destination_vehicles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=715;

--
-- Tablo için AUTO_INCREMENT değeri `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- Tablo için AUTO_INCREMENT değeri `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `section_translations`
--
ALTER TABLE `section_translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
