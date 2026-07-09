-- Migration: 2026-07-08_migrations
-- SEO Ayarları için yeni parametreler ekleme

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
('default_og_image', '', 'image', 'seo'),
('twitter_username', '', 'text', 'seo'),
('facebook_app_id', '', 'text', 'seo'),
('google_tag_manager', '', 'text', 'seo');
