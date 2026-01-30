-- Migration: 001_create_settings_table
-- Genel site ayarları tablosu

CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'textarea', 'image', 'boolean', 'json') DEFAULT 'text',
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan ayarlar
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
('site_name', 'Tour', 'text', 'general'),
('site_description', 'Discover Your Perfect Journey', 'textarea', 'general'),
('site_keywords', 'tur, seyahat, tatil, gezi', 'text', 'seo'),
('site_logo', 'assets/img/logo.png', 'image', 'general'),
('site_favicon', 'assets/img/favicon.png', 'image', 'general'),
('contact_email', 'info@example.com', 'text', 'contact'),
('contact_phone', '+90 555 123 4567', 'text', 'contact'),
('contact_address', 'İstanbul, Türkiye', 'textarea', 'contact'),
('social_facebook', '', 'text', 'social'),
('social_twitter', '', 'text', 'social'),
('social_instagram', '', 'text', 'social'),
('social_youtube', '', 'text', 'social'),
('footer_text', '© 2025 Tour. Tüm hakları saklıdır.', 'textarea', 'general'),
('google_analytics', '', 'textarea', 'seo'),
('google_maps_api', '', 'text', 'general');
