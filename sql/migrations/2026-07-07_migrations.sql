-- =============================================================================
-- MIGRATION: 2026-07-07
-- SunLine VIP Transfer - WhatsApp Templates, Settings & Soft Delete Additions
-- =============================================================================

-- 1. WhatsApp Şablonları Tablosu ve Varsayılan Veriler
CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_key` VARCHAR(50) UNIQUE NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `whatsapp_templates` (`template_key`, `title`, `content`) VALUES 
('outsource_transfer', 'Dış Partner Transfer Bilgisi', '*TRANSFER DETAYI*\n{ROUTE}\n*Tarih/Saat:* {FLIGHT_DATE} - {FLIGHT_TIME}\n*Kişi:* {PAX}\n*Otel:* {HOTEL}\n\n*TAHSİLAT:* {PRICE}\n*HAKEDİŞ:* {PARTNER_HAKEDIS}\n\n*Yolcular:*\n{PASSENGERS}\n\n*Not:* {NOTES}'),
('driver_transfer', 'Şöför Transfer Görevi', '*SÜRÜCÜ TRANSFER GÖREVİ*\nŞöför: {DRIVER_NAME}\n-----------------------------\n{ROUTE}\n*Alınış:* {PICKUP_DATE} - {PICKUP_TIME}\n*Uçuş:* {FLIGHT_DATE} - {FLIGHT_TIME}\n*Uçuş No:* {FLIGHT_NO}\n*Kişi:* {PAX}\n*Otel:* {HOTEL}\n\n*TAHSİLAT:* {PRICE}\n\n*Yolcular:*\n{PASSENGERS}\n\n*Not:* {NOTES}')
ON DUPLICATE KEY UPDATE `content` = VALUES(`content`);

-- 2. Havalimanı Ayarı Girişi (Settings Tablosuna Veri Ekleme)
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) 
VALUES ('airport_name', 'ANTALYA AİRPORT AYT', 'text', 'general')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;

-- 3. Soft Delete (is_deleted) Alanının Bookings Tablosuna Eklenmesi
ALTER TABLE `bookings` ADD COLUMN `is_deleted` TINYINT DEFAULT 0 AFTER `customer_id`;
ALTER TABLE `bookings` ADD INDEX `idx_bookings_is_deleted` (`is_deleted`);
