-- Migration: 005_create_sections_table
-- Sayfa section'ları tablosu (dinamik içerik yönetimi)

CREATE TABLE IF NOT EXISTS `sections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `page_id` INT UNSIGNED,
    `section_key` VARCHAR(100) NOT NULL,
    `section_type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255),
    `subtitle` TEXT,
    `content` LONGTEXT,
    `settings` JSON,
    `background_image` VARCHAR(255),
    `background_video` VARCHAR(255),
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_page_id` (`page_id`),
    INDEX `idx_section_key` (`section_key`),
    FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hero section için varsayılan
INSERT INTO `sections` (`page_id`, `section_key`, `section_type`, `title`, `subtitle`, `content`, `settings`, `sort_order`) VALUES
(1, 'hero', 'hero', 'Discover Your Perfect Journey', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', NULL, 
'{"button_text": "Start Exploring", "button_url": "#", "button2_text": "Browse Tours", "button2_url": "/turlar", "form_title": "Plan Your Adventure"}', 1),

(1, 'why_us', 'why_us', 'Explore the World with Confidence', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', NULL,
'{"stats": [{"value": 1200, "label": "Happy Travelers"}, {"value": 85, "label": "Countries Covered"}, {"value": 15, "label": "Years Experience"}]}', 2),

(1, 'featured_destinations', 'destinations', 'Featured Destinations', 'Check Our Featured Destinations', NULL, '{"limit": 4}', 3),

(1, 'featured_tours', 'tours', 'Featured Tours', 'Check Our Featured Tours', NULL, '{"limit": 6}', 4),

(1, 'testimonials', 'testimonials', 'Testimonials', 'What Our Customers Are Saying', NULL, '{"limit": 6}', 5);
