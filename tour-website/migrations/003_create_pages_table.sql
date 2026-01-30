-- Migration: 003_create_pages_table
-- Sayfalar tablosu (SEO dostu slug yapısı)

CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` LONGTEXT,
    `excerpt` TEXT,
    `featured_image` VARCHAR(255),
    `template` VARCHAR(50) DEFAULT 'default',
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `meta_keywords` VARCHAR(255),
    `og_image` VARCHAR(255),
    `status` ENUM('published', 'draft', 'trash') DEFAULT 'draft',
    `is_homepage` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `author_id` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan sayfalar
INSERT INTO `pages` (`title`, `slug`, `template`, `status`, `is_homepage`, `sort_order`) VALUES
('Ana Sayfa', 'anasayfa', 'home', 'published', 1, 1),
('Hakkımızda', 'hakkimizda', 'about', 'published', 0, 2),
('İletişim', 'iletisim', 'contact', 'published', 0, 3),
('Gizlilik Politikası', 'gizlilik-politikasi', 'default', 'published', 0, 4),
('Kullanım Şartları', 'kullanim-sartlari', 'default', 'published', 0, 5),
('SSS', 'sikca-sorulan-sorular', 'faq', 'published', 0, 6);
