-- Migration: 006_create_destinations_table
-- Destinasyonlar tablosu

CREATE TABLE IF NOT EXISTS `destinations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `content` LONGTEXT,
    `featured_image` VARCHAR(255),
    `gallery` JSON,
    `location` VARCHAR(255),
    `country` VARCHAR(100),
    `continent` VARCHAR(50),
    `latitude` DECIMAL(10, 8),
    `longitude` DECIMAL(11, 8),
    `tour_count` INT DEFAULT 0,
    `rating` DECIMAL(2, 1) DEFAULT 0,
    `review_count` INT DEFAULT 0,
    `starting_price` DECIMAL(10, 2),
    `badge` VARCHAR(50),
    `is_featured` TINYINT(1) DEFAULT 0,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `status` ENUM('published', 'draft', 'trash') DEFAULT 'draft',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek destinasyonlar
INSERT INTO `destinations` (`title`, `slug`, `description`, `location`, `country`, `continent`, `tour_count`, `rating`, `review_count`, `starting_price`, `badge`, `is_featured`, `status`) VALUES
('Tropical Paradise', 'tropical-paradise', 'Pristine beaches, crystal-clear waters, and luxury overwater villas await in this tropical paradise destination.', 'Maldives', 'Maldives', 'Asia', 22, 4.9, 412, 2150.00, 'Popular Choice', 1, 'published'),
('Mountain Adventure', 'mountain-adventure', 'Breathtaking Himalayan peaks and ancient Buddhist temples create an unforgettable spiritual journey.', 'Nepal', 'Nepal', 'Asia', 16, 4.8, 280, 1420.00, 'Best Value', 1, 'published'),
('Cultural Heritage', 'cultural-heritage', 'Discover ancient civilizations, colorful markets, and archaeological wonders in the heart of South America.', 'Peru', 'Peru', 'South America', 9, 4.7, 195, 980.00, NULL, 1, 'published'),
('Safari Experience', 'safari-experience', 'Witness the Big Five and experience the great migration in Africa''s most spectacular wildlife reserves.', 'Kenya', 'Kenya', 'Africa', 11, 4.9, 320, 2750.00, 'Limited Spots', 1, 'published');
