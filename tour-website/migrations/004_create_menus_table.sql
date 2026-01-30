-- Migration: 004_create_menus_table
-- Menü yönetimi tabloları

CREATE TABLE IF NOT EXISTS `menus` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `location` VARCHAR(50) DEFAULT 'header',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `menu_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255),
    `target` ENUM('_self', '_blank') DEFAULT '_self',
    `item_type` ENUM('page', 'tour', 'destination', 'category', 'custom') DEFAULT 'custom',
    `item_id` INT UNSIGNED DEFAULT NULL,
    `icon` VARCHAR(50),
    `css_class` VARCHAR(100),
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_menu_id` (`menu_id`),
    INDEX `idx_parent_id` (`parent_id`),
    FOREIGN KEY (`menu_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan menüler
INSERT INTO `menus` (`name`, `slug`, `location`) VALUES
('Ana Menü', 'ana-menu', 'header'),
('Footer Menü', 'footer-menu', 'footer');

-- Ana menü öğeleri
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `title`, `url`, `item_type`, `sort_order`) VALUES
(1, NULL, 'Ana Sayfa', '/', 'page', 1),
(1, NULL, 'Hakkımızda', '/hakkimizda', 'page', 2),
(1, NULL, 'Destinasyonlar', '/destinasyonlar', 'custom', 3),
(1, NULL, 'Turlar', '/turlar', 'custom', 4),
(1, NULL, 'Galeri', '/galeri', 'custom', 5),
(1, NULL, 'Blog', '/blog', 'custom', 6),
(1, NULL, 'İletişim', '/iletisim', 'page', 7);
