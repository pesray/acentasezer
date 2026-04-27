-- Migration: 019_add_site_logo_setting
-- site_logo ve site_favicon ayarlarını ekle (yoksa)

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`)
VALUES
('site_logo',    '', 'image', 'general'),
('site_favicon', '', 'image', 'general');
