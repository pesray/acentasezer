-- =============================================================================
-- MIGRATION: 2026-08-06 (BÜTÜNSEL GÜNCELLEME)
-- SunLine VIP Transfer - Ara Transferler (Otelden Otele Transfer) Tablosu ve Kısıtlamalar
-- =============================================================================

-- 1. Ara Transferler (inter_hotel_transfers) Tablosu
CREATE TABLE IF NOT EXISTS `inter_hotel_transfers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_number` VARCHAR(30) UNIQUE NOT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `destination_id` INT DEFAULT NULL,
  `vehicle_id` INT DEFAULT NULL,
  `pickup_hotel_id` INT DEFAULT NULL,
  `pickup_hotel_name` VARCHAR(500) DEFAULT NULL,
  `pickup_date` DATE DEFAULT NULL,
  `pickup_time` TIME DEFAULT NULL,
  `destination_hotel_id` INT DEFAULT NULL,
  `destination_hotel_name` VARCHAR(500) DEFAULT NULL,
  `adults` INT DEFAULT 1,
  `children` INT DEFAULT 0,
  `child_seat` INT DEFAULT 0,
  `total_price` DECIMAL(10,2) DEFAULT 0.00,
  `currency` VARCHAR(10) DEFAULT 'EUR',
  `notes` TEXT DEFAULT NULL,
  `admin_notes` TEXT DEFAULT NULL,
  `booking_status` ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `is_outsourced` TINYINT(1) NOT NULL DEFAULT 0,
  `outsource_partner_id` INT DEFAULT NULL,
  `outsource_name` VARCHAR(255) DEFAULT NULL,
  `outsource_price` DECIMAL(10,2) DEFAULT NULL,
  `outsource_currency` VARCHAR(10) DEFAULT NULL,
  `outsource_pickup_time` TIME DEFAULT NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_inter_booking_number` (`booking_number`),
  INDEX `idx_inter_customer_id` (`customer_id`),
  INDEX `idx_inter_pickup_date` (`pickup_date`),
  INDEX `idx_inter_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Foreign Key Kısıtlamalarını Kaldırıp booking_id'yi Boş Bırakılabilir (NULL) Yapma
-- (Bu sayede Ara Transfer yolcu eklerken booking_id boş kalsa dahi 1452 hatası oluşmaz)
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `booking_passengers` MODIFY COLUMN `booking_id` INT NULL;
ALTER TABLE `booking_drivers` MODIFY COLUMN `booking_id` INT NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- 3. Yolcular ve Şöförler Tablosuna inter_transfer_id Ekleme (Eğer henüz eklenmediyse)
ALTER TABLE `booking_passengers` ADD COLUMN IF NOT EXISTS `inter_transfer_id` INT DEFAULT NULL AFTER `booking_id`;
ALTER TABLE `booking_passengers` ADD INDEX IF NOT EXISTS `idx_bp_inter_transfer_id` (`inter_transfer_id`);

ALTER TABLE `booking_drivers` ADD COLUMN IF NOT EXISTS `inter_transfer_id` INT DEFAULT NULL AFTER `booking_id`;
ALTER TABLE `booking_drivers` ADD INDEX IF NOT EXISTS `idx_bd_inter_transfer_id` (`inter_transfer_id`);

-- 4. Ara Transfer Özel WhatsApp Şablonları
INSERT INTO `whatsapp_templates` (`template_key`, `title`, `content`) VALUES 
('outsource_inter_transfer', 'Dış Partner Ara Transfer Bilgisi', '*ARA TRANSFER DETAYI*\n{ROUTE}\n*Tarih/Saat:* {PICKUP_DATE} - {PICKUP_TIME}\n*Alınış Oteli:* {PICKUP_HOTEL}\n*Varış Oteli:* {DESTINATION_HOTEL}\n*Kişi:* {PAX}\n\n*TAHSİLAT:* {PRICE}\n*HAKEDİŞ:* {PARTNER_HAKEDIS}\n\n*Yolcular:*\n{PASSENGERS}\n\n*Not:* {NOTES}'),
('driver_inter_transfer', 'Şöför Ara Transfer Görevi', '*SÜRÜCÜ ARA TRANSFER GÖREVİ*\nŞöför: {DRIVER_NAME}\n-----------------------------\n{ROUTE}\n*Alınış Tarihi / Saati:* {PICKUP_DATE} - {PICKUP_TIME}\n*Alınış Oteli:* {PICKUP_HOTEL}\n*Varış Oteli:* {DESTINATION_HOTEL}\n*Kişi:* {PAX}\n\n*TAHSİLAT:* {PRICE}\n\n*Yolcular:*\n{PASSENGERS}\n\n*Not:* {NOTES}')
ON DUPLICATE KEY UPDATE `content` = VALUES(`content`);

