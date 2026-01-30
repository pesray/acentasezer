-- Migration: 010_create_blog_table
-- Blog tabloları

CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` TEXT,
    `content` LONGTEXT,
    `featured_image` VARCHAR(255),
    `category_id` INT UNSIGNED,
    `author_id` INT UNSIGNED,
    `tags` JSON,
    `view_count` INT DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `allow_comments` TINYINT(1) DEFAULT 1,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `status` ENUM('published', 'draft', 'trash') DEFAULT 'draft',
    `published_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_category` (`category_id`),
    INDEX `idx_author` (`author_id`),
    FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `author_name` VARCHAR(100) NOT NULL,
    `author_email` VARCHAR(100) NOT NULL,
    `author_website` VARCHAR(255),
    `content` TEXT NOT NULL,
    `is_approved` TINYINT(1) DEFAULT 0,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_post_id` (`post_id`),
    INDEX `idx_approved` (`is_approved`),
    FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `blog_comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog kategorileri
INSERT INTO `blog_categories` (`name`, `slug`, `sort_order`) VALUES
('Seyahat İpuçları', 'seyahat-ipuclari', 1),
('Destinasyonlar', 'destinasyonlar', 2),
('Macera', 'macera', 3),
('Yemek & Kültür', 'yemek-kultur', 4);
