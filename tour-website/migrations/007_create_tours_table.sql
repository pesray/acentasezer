-- Migration: 007_create_tours_table
-- Turlar tablosu

CREATE TABLE IF NOT EXISTS `tour_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `icon` VARCHAR(50),
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tours` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `content` LONGTEXT,
    `featured_image` VARCHAR(255),
    `gallery` JSON,
    `destination_id` INT UNSIGNED,
    `category_id` INT UNSIGNED,
    `duration_days` INT,
    `duration_nights` INT,
    `group_size_min` INT DEFAULT 1,
    `group_size_max` INT DEFAULT 10,
    `price` DECIMAL(10, 2) NOT NULL,
    `sale_price` DECIMAL(10, 2),
    `currency` VARCHAR(3) DEFAULT 'USD',
    `rating` DECIMAL(2, 1) DEFAULT 0,
    `review_count` INT DEFAULT 0,
    `badge` VARCHAR(50),
    `highlights` JSON,
    `included` JSON,
    `excluded` JSON,
    `itinerary` JSON,
    `departure_dates` JSON,
    `meeting_point` VARCHAR(255),
    `difficulty_level` ENUM('easy', 'moderate', 'challenging', 'extreme') DEFAULT 'moderate',
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_bestseller` TINYINT(1) DEFAULT 0,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `status` ENUM('published', 'draft', 'trash') DEFAULT 'draft',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_destination` (`destination_id`),
    INDEX `idx_category` (`category_id`),
    FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`category_id`) REFERENCES `tour_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tur kategorileri
INSERT INTO `tour_categories` (`name`, `slug`, `icon`, `sort_order`) VALUES
('Macera', 'macera', 'bi-lightning', 1),
('Kültür', 'kultur', 'bi-building', 2),
('Plaj & Deniz', 'plaj-deniz', 'bi-water', 3),
('Safari', 'safari', 'bi-binoculars', 4),
('Lüks', 'luks', 'bi-gem', 5),
('Aile', 'aile', 'bi-people', 6);

-- Örnek turlar
INSERT INTO `tours` (`title`, `slug`, `description`, `destination_id`, `category_id`, `duration_days`, `group_size_max`, `price`, `rating`, `review_count`, `badge`, `highlights`, `is_featured`, `status`) VALUES
('Serene Beach Retreat', 'serene-beach-retreat', 'Mauris ipsum neque, cursus ac ipsum at, iaculis facilisis ligula.', 1, 3, 8, 6, 2150.00, 4.8, 95, 'Top Rated', '["Maldives", "Seychelles", "Bora Bora"]', 1, 'published'),
('Arctic Wilderness Expedition', 'arctic-wilderness-expedition', 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices.', NULL, 1, 10, 8, 5700.00, 4.6, 55, 'Only 3 Spots!', '["Greenland", "Iceland", "Norway"]', 1, 'published'),
('Sahara Desert Discovery', 'sahara-desert-discovery', 'Pellentesque euismod tincidunt turpis ac tristique.', NULL, 1, 5, 10, 1400.00, 4.9, 72, 'Newly Added', '["Morocco", "Egypt", "Dubai"]', 1, 'published'),
('Mediterranean Coastal Cruise', 'mediterranean-coastal-cruise', 'Nullam lacinia justo eget ex sodales, vel finibus orci aliquet.', NULL, 3, 9, 15, 1980.00, 4.7, 110, 'Popular Choice', '["Greece", "Croatia", "Italy"]', 1, 'published'),
('Amazon Rainforest Trek', 'amazon-rainforest-trek', 'Quisque dictum felis eu tortor mollis, quis tincidunt arcu pharetra.', NULL, 1, 12, 10, 2650.00, 4.5, 88, 'Eco-Friendly', '["Brazil", "Ecuador", "Peru"]', 1, 'published'),
('Patagonian Peaks & Glaciers', 'patagonian-peaks-glaciers', 'Vivamus eget semper neque. Ut porttitor mi at odio egestas.', NULL, 1, 14, 10, 3950.00, 4.9, 60, 'Adventure Seekers', '["Argentina", "Chile", "Ushuaia"]', 1, 'published');
