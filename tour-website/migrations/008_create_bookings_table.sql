-- Migration: 008_create_bookings_table
-- Rezervasyonlar tablosu

CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `booking_number` VARCHAR(20) NOT NULL UNIQUE,
    `tour_id` INT UNSIGNED NOT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20),
    `adults` INT DEFAULT 1,
    `children` INT DEFAULT 0,
    `departure_date` DATE,
    `return_date` DATE,
    `special_requests` TEXT,
    `total_price` DECIMAL(10, 2),
    `currency` VARCHAR(3) DEFAULT 'USD',
    `payment_status` ENUM('pending', 'paid', 'refunded', 'cancelled') DEFAULT 'pending',
    `booking_status` ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    `notes` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_booking_number` (`booking_number`),
    INDEX `idx_tour_id` (`tour_id`),
    INDEX `idx_status` (`booking_status`),
    INDEX `idx_email` (`customer_email`),
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
