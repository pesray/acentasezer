-- Migration: section_translations'a dile özgü settings JSON kolonu
-- Sahip: Site Yönetimi
-- Amaç: Anasayfa section'larında her dil için ayrı settings (buton metni, form etiketleri, stats vb.)
--       saklanabilsin. Base `sections.settings` artık sadece dil-bağımsız fallback olarak kullanılır.
-- Frontend: getPageSections() COALESCE(st.settings, s.settings) zaten hazır.

-- UP
ALTER TABLE `section_translations`
    ADD COLUMN `settings` JSON NULL AFTER `content`;

-- DOWN (rollback)
-- ALTER TABLE `section_translations` DROP COLUMN `settings`;
