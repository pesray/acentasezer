-- =====================================================================
-- Migration 028: SEO — işletme bilgileri (Google İşletme Profili ile birebir NAP + sosyal hesaplar)
-- Tarih: 2026-11-16 · Sahip: Site Yönetimi
--
-- Tekrar çalıştırmaya dayanıklı: var olan ayarlara dokunmaz (INSERT IGNORE, setting_key benzersiz).
-- Şema değişikliği YOK. Sonradan Admin > Site Ayarları'ndan düzenlenebilir.
--  · Sosyal Medya: Facebook, Instagram, Google İşletme Profili (footer ikonları + JSON-LD sameAs)
--  · İletişim: şirket ticari unvanı (JSON-LD legalName)
--  · Adres: Google İşletme Profili'ndeki adres ("Side, 07600 Manavgat/Antalya").
--    Yalnız adres alanında hâlâ şirket unvanı yazıyorsa değiştirilir; elle girilmiş başka adres korunur.
-- =====================================================================

SET NAMES utf8mb4;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
('social_facebook', 'https://www.facebook.com/sunlinevip', 'text', 'social'),
('social_instagram', 'https://www.instagram.com/sunlineviptransfer/', 'text', 'social'),
('social_google_business', 'https://www.google.com/search?kgmid=/g/11nbnq8dkc', 'text', 'social'),
('company_legal_name', 'MANAVGAT MES TURİZM TİCARET LİMİTED ŞİRKETİ', 'text', 'contact');

UPDATE `settings`
SET `setting_value` = 'Side, 07600 Manavgat/Antalya'
WHERE `setting_key` = 'contact_address'
  AND `setting_value` = 'MANAVGAT MES TURİZM TİCARET LİMİTED ŞİRKETİ';
