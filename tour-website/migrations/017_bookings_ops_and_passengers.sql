-- Migration 017: Rezervasyon operasyonel alanları + yolcu tablosu
-- Çalıştırma tarihi: 2026-04-25

-- 1) bookings tablosuna operasyonel kolonlar ekle
ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS is_completed    TINYINT(1)     NOT NULL DEFAULT 0   AFTER admin_notes,
    ADD COLUMN IF NOT EXISTS is_outsourced   TINYINT(1)     NOT NULL DEFAULT 0   AFTER is_completed,
    ADD COLUMN IF NOT EXISTS outsource_price DECIMAL(10,2)  NULL     DEFAULT NULL AFTER is_outsourced;

-- 2) Yolcu bilgileri tablosu
CREATE TABLE IF NOT EXISTS booking_passengers (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    booking_id      INT          NOT NULL,
    booking_number  VARCHAR(50)  NOT NULL,
    passenger_type  ENUM('adult','child') NOT NULL DEFAULT 'adult',
    full_name       VARCHAR(255) NOT NULL,
    sort_order      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
