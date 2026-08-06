-- =============================================================================
-- MIGRATION: 2026-08-06
-- SunLine VIP Transfer - Havalimanları (Airports) Tablosu ve Varsayılan Yapı
-- =============================================================================

-- 1. Havalimanları Tablosu
CREATE TABLE IF NOT EXISTS `airports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `district` VARCHAR(100) DEFAULT NULL,
  `code` VARCHAR(20) DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_airports_is_default` (`is_default`),
  INDEX `idx_airports_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bookings tablosuna airport_id kolonu ekleme
ALTER TABLE `bookings` ADD `airport_id` INT DEFAULT NULL AFTER `destination_id`;

-- 3. Varsayılan Havalimanı Kaydı (Antalya Airport AYT)
INSERT INTO `airports` (`name`, `city`, `district`, `code`, `is_default`) 
VALUES ('ANTALYA AİRPORT AYT', 'Antalya', 'Muratpaşa', 'AYT', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
