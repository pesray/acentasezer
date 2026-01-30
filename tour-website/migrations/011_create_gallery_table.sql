-- Migration: 011_create_gallery_table
-- Galeri tablosu

CREATE TABLE IF NOT EXISTS `gallery_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255),
    `description` TEXT,
    `image` VARCHAR(255) NOT NULL,
    `thumbnail` VARCHAR(255),
    `category_id` INT UNSIGNED,
    `destination_id` INT UNSIGNED,
    `tour_id` INT UNSIGNED,
    `alt_text` VARCHAR(255),
    `is_featured` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_featured` (`is_featured`),
    FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galeri kategorileri
INSERT INTO `gallery_categories` (`name`, `slug`, `sort_order`) VALUES
('Plajlar', 'plajlar', 1),
('Dağlar', 'daglar', 2),
('Şehirler', 'sehirler', 3),
('Doğa', 'doga', 4),
('Macera', 'macera', 5);
