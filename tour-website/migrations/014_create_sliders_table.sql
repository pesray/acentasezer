-- Migration: 014_create_sliders_table
-- Slider/Banner tablosu

CREATE TABLE IF NOT EXISTS `sliders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255),
    `subtitle` TEXT,
    `image` VARCHAR(255),
    `video` VARCHAR(255),
    `button_text` VARCHAR(100),
    `button_url` VARCHAR(255),
    `button2_text` VARCHAR(100),
    `button2_url` VARCHAR(255),
    `overlay_color` VARCHAR(20) DEFAULT 'rgba(0,0,0,0.5)',
    `text_position` ENUM('left', 'center', 'right') DEFAULT 'left',
    `location` VARCHAR(50) DEFAULT 'home',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `start_date` DATE,
    `end_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_location` (`location`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan slider
INSERT INTO `sliders` (`title`, `subtitle`, `video`, `button_text`, `button_url`, `button2_text`, `button2_url`, `location`, `is_active`, `sort_order`) VALUES
('Discover Your Perfect Journey', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'assets/img/travel/video-2.mp4', 'Start Exploring', '#', 'Browse Tours', '/turlar', 'home', 1, 1);
