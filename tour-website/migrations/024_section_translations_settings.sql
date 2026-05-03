-- Migration 024: section_translations tablosuna settings sütunu ekle
-- Tarih: 2026-05-03
-- Açıklama: Admin panelde her dil için section ayarları (buton metinleri, form label'ları vb.)
--           section_translations.settings JSON sütununda saklanacak.

ALTER TABLE `section_translations`
ADD COLUMN `settings` JSON DEFAULT NULL AFTER `content`;

-- Rollback:
-- ALTER TABLE `section_translations` DROP COLUMN `settings`;
