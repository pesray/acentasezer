-- Migration: 016_create_languages_table
-- Çoklu dil desteği tabloları

CREATE TABLE IF NOT EXISTS `languages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(5) NOT NULL UNIQUE,
    `name` VARCHAR(50) NOT NULL,
    `native_name` VARCHAR(50) NOT NULL,
    `flag` VARCHAR(10),
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_rtl` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan diller
INSERT INTO `languages` (`code`, `name`, `native_name`, `flag`, `is_default`, `is_active`, `sort_order`) VALUES
('tr', 'Turkish', 'Türkçe', '🇹🇷', 1, 1, 1),
('en', 'English', 'English', '🇬🇧', 0, 1, 2),
('de', 'German', 'Deutsch', '🇩🇪', 0, 0, 3),
('ru', 'Russian', 'Русский', '🇷🇺', 0, 0, 4),
('ar', 'Arabic', 'العربية', '🇸🇦', 0, 0, 5);

-- Sayfa çevirileri tablosu
CREATE TABLE IF NOT EXISTS `page_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `page_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `content` LONGTEXT,
    `excerpt` TEXT,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `meta_keywords` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_page_lang` (`page_id`, `language_code`),
    UNIQUE KEY `unique_slug_lang` (`slug`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Destinasyon çevirileri
CREATE TABLE IF NOT EXISTS `destination_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `destination_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `content` LONGTEXT,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_dest_lang` (`destination_id`, `language_code`),
    UNIQUE KEY `unique_slug_lang` (`slug`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tur çevirileri
CREATE TABLE IF NOT EXISTS `tour_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tour_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `content` LONGTEXT,
    `highlights` JSON,
    `included` JSON,
    `excluded` JSON,
    `itinerary` JSON,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tour_lang` (`tour_id`, `language_code`),
    UNIQUE KEY `unique_slug_lang` (`slug`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Section çevirileri
CREATE TABLE IF NOT EXISTS `section_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `section_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255),
    `subtitle` TEXT,
    `content` LONGTEXT,
    `settings` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_section_lang` (`section_id`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menü öğesi çevirileri
CREATE TABLE IF NOT EXISTS `menu_item_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `menu_item_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_menu_item_lang` (`menu_item_id`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Slider çevirileri
CREATE TABLE IF NOT EXISTS `slider_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slider_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255),
    `subtitle` TEXT,
    `button_text` VARCHAR(100),
    `button_url` VARCHAR(255),
    `button2_text` VARCHAR(100),
    `button2_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_slider_lang` (`slider_id`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`slider_id`) REFERENCES `sliders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonial çevirileri
CREATE TABLE IF NOT EXISTS `testimonial_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `testimonial_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `content` TEXT NOT NULL,
    `customer_title` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_testimonial_lang` (`testimonial_id`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`testimonial_id`) REFERENCES `testimonials`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feature çevirileri
CREATE TABLE IF NOT EXISTS `feature_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `feature_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_feature_lang` (`feature_id`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`feature_id`) REFERENCES `features`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog yazısı çevirileri
CREATE TABLE IF NOT EXISTS `blog_post_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` TEXT,
    `content` LONGTEXT,
    `meta_title` VARCHAR(255),
    `meta_description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_post_lang` (`post_id`, `language_code`),
    UNIQUE KEY `unique_slug_lang` (`slug`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FAQ çevirileri
CREATE TABLE IF NOT EXISTS `faq_translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `faq_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_faq_lang` (`faq_id`, `language_code`),
    INDEX `idx_language` (`language_code`),
    FOREIGN KEY (`faq_id`) REFERENCES `faqs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Genel çeviri tablosu (header, footer, butonlar, etiketler vb.)
CREATE TABLE IF NOT EXISTS `translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `translation_key` VARCHAR(100) NOT NULL,
    `language_code` VARCHAR(5) NOT NULL,
    `translation_value` TEXT NOT NULL,
    `group_name` VARCHAR(50) DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_key_lang` (`translation_key`, `language_code`),
    INDEX `idx_group` (`group_name`),
    INDEX `idx_language` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan çeviriler (Türkçe)
INSERT INTO `translations` (`translation_key`, `language_code`, `translation_value`, `group_name`) VALUES
-- Header
('menu_home', 'tr', 'Ana Sayfa', 'header'),
('menu_about', 'tr', 'Hakkımızda', 'header'),
('menu_destinations', 'tr', 'Destinasyonlar', 'header'),
('menu_tours', 'tr', 'Turlar', 'header'),
('menu_gallery', 'tr', 'Galeri', 'header'),
('menu_blog', 'tr', 'Blog', 'header'),
('menu_contact', 'tr', 'İletişim', 'header'),
('btn_get_started', 'tr', 'Başla', 'header'),

-- Footer
('footer_about_title', 'tr', 'Hakkımızda', 'footer'),
('footer_links_title', 'tr', 'Hızlı Linkler', 'footer'),
('footer_services_title', 'tr', 'Hizmetlerimiz', 'footer'),
('footer_follow_title', 'tr', 'Bizi Takip Edin', 'footer'),
('footer_newsletter_title', 'tr', 'Bültenimize Katılın', 'footer'),
('footer_newsletter_text', 'tr', 'En son haberler ve fırsatlar için abone olun!', 'footer'),
('footer_copyright', 'tr', '© 2025 Tüm hakları saklıdır.', 'footer'),

-- Genel
('read_more', 'tr', 'Devamını Oku', 'general'),
('book_now', 'tr', 'Şimdi Rezervasyon Yap', 'general'),
('view_details', 'tr', 'Detayları Gör', 'general'),
('explore_now', 'tr', 'Keşfet', 'general'),
('subscribe', 'tr', 'Abone Ol', 'general'),
('send_message', 'tr', 'Mesaj Gönder', 'general'),
('days', 'tr', 'Gün', 'general'),
('nights', 'tr', 'Gece', 'general'),
('person', 'tr', 'Kişi', 'general'),
('from', 'tr', 'den başlayan', 'general'),

-- İngilizce çeviriler
('menu_home', 'en', 'Home', 'header'),
('menu_about', 'en', 'About', 'header'),
('menu_destinations', 'en', 'Destinations', 'header'),
('menu_tours', 'en', 'Tours', 'header'),
('menu_gallery', 'en', 'Gallery', 'header'),
('menu_blog', 'en', 'Blog', 'header'),
('menu_contact', 'en', 'Contact', 'header'),
('btn_get_started', 'en', 'Get Started', 'header'),
('footer_about_title', 'en', 'About Us', 'footer'),
('footer_links_title', 'en', 'Quick Links', 'footer'),
('footer_services_title', 'en', 'Our Services', 'footer'),
('footer_follow_title', 'en', 'Follow Us', 'footer'),
('footer_newsletter_title', 'en', 'Join Our Newsletter', 'footer'),
('footer_newsletter_text', 'en', 'Subscribe for latest news and offers!', 'footer'),
('footer_copyright', 'en', '© 2025 All Rights Reserved.', 'footer'),
('read_more', 'en', 'Read More', 'general'),
('book_now', 'en', 'Book Now', 'general'),
('view_details', 'en', 'View Details', 'general'),
('explore_now', 'en', 'Explore Now', 'general'),
('subscribe', 'en', 'Subscribe', 'general'),
('send_message', 'en', 'Send Message', 'general'),
('days', 'en', 'Days', 'general'),
('nights', 'en', 'Nights', 'general'),
('person', 'en', 'Person', 'general'),
('from', 'en', 'from', 'general');
