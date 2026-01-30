-- Migration: 012_create_contacts_table
-- İletişim mesajları tablosu

CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `subject` VARCHAR(255),
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_replied` TINYINT(1) DEFAULT 0,
    `replied_at` TIMESTAMP NULL,
    `replied_by` INT UNSIGNED,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_email` (`email`),
    FOREIGN KEY (`replied_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
