-- 030: Ölçüm ayarları + 17 Kasım eksikleri (2026-11-17)
-- Tekrar çalıştırmak güvenli: her satır yalnız eksikse ekler / yalnız eski değerdeyse değiştirir.
-- SIRA: önce kod deploy edilmeli (çerez onayı kutusu yeni kodda), sonra bu SQL.

SET NAMES utf8mb4;

-- 1) Site Ayarları > SEO'ya 3 ölçüm alanı (admin yalnız var olan satırı gösterip güncelliyor)
--    Google Analytics kimliği Sezer'in GA4 mülkünden ("SunLine", akış: https://sunlineviptransfer.com/)
--    Google Ads etiketi: hesap 665-760-1421 ("SunLine VIP Transfer", GA4'e bağlı)
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, setting_group) VALUES
('google_analytics', 'G-YHE7HMXZN2', 'text', 'seo'),
('google_ads_id', 'AW-18581344123', 'text', 'seo'),
('yandex_metrika', '', 'text', 'seo');

-- 2) Evrenseki (7) ve Sorgun (8) Rusça adresi boştu → RU sayfaları açılmıyordu
UPDATE destination_translations SET slug = 'evrenseki-transfer' WHERE destination_id = 7 AND language_code = 'ru' AND (slug IS NULL OR slug = '');
UPDATE destination_translations SET slug = 'sorgun-transfer' WHERE destination_id = 8 AND language_code = 'ru' AND (slug IS NULL OR slug = '');

-- 3) Anasayfa "Misafirlerimiz" fotoğrafı 17 Kasım veri kaybında silinen dosyayı gösteriyordu → aynı fotoğrafın yeniden yüklenmiş hali
UPDATE sections SET settings = REPLACE(settings, 'general/1794326724_ec7e7808dc5e7b90.webp', 'general/1794927007_84b80eb1bc04be03.webp')
WHERE id = 12 AND section_key = 'sun_guests' AND settings LIKE '%general/1794326724_ec7e7808dc5e7b90.webp%';

-- 4) Gizlilik / KVKK sayfasına "Çerezler" bölümü (4 dil; çerez onayı kutusundaki "Ayrıntılar" bu sayfaya gider)
UPDATE page_translations SET content = CONCAT(content, '<h2>Çerezler</h2><p>Sitemiz, çalışması için gerekli tercihleri (dil, tema, çerez tercihi) tarayıcınızda saklar. Açık rızanızla ayrıca ziyaret istatistikleri ve reklam ölçümü için Google Analytics, Google Ads ve Yandex Metrika çerezleri kullanılır; bu veriler Google ve Yandex tarafından yurt dışındaki sunucularda işlenebilir. İzin vermezseniz bu çerezler kullanılmaz. Tercihinizi sayfanın altındaki "Çerez ayarları" bağlantısından istediğiniz zaman değiştirebilirsiniz.</p>')
WHERE page_id = 4 AND language_code = 'tr' AND slug = 'kvkk' AND content NOT LIKE '%<h2>Çerezler</h2>%';

UPDATE page_translations SET content = CONCAT(content, '<h2>Cookies</h2><p>Our website stores settings it needs to work (language, theme, cookie choice) in your browser. With your consent, Google Analytics, Google Ads and Yandex Metrica cookies are also used for visit statistics and ad measurement; this data may be processed by Google and Yandex on servers outside Türkiye. If you do not consent, these cookies are not used. You can change your choice at any time via the "Cookie settings" link at the bottom of the page.</p>')
WHERE page_id = 4 AND language_code = 'en' AND slug = 'privacy-policy' AND content NOT LIKE '%<h2>Cookies</h2>%';

UPDATE page_translations SET content = CONCAT(content, '<h2>Cookies</h2><p>Unsere Website speichert für ihren Betrieb notwendige Einstellungen (Sprache, Design, Cookie-Auswahl) in Ihrem Browser. Mit Ihrer Einwilligung werden zusätzlich Cookies von Google Analytics, Google Ads und Yandex Metrica für Besuchsstatistiken und Werbemessung verwendet; diese Daten können von Google und Yandex auf Servern außerhalb der Türkei verarbeitet werden. Ohne Einwilligung werden diese Cookies nicht verwendet. Ihre Auswahl können Sie jederzeit über den Link „Cookie-Einstellungen“ unten auf der Seite ändern.</p>')
WHERE page_id = 4 AND language_code = 'de' AND slug = 'datenschutz' AND content NOT LIKE '%<h2>Cookies</h2>%';

UPDATE page_translations SET content = CONCAT(content, '<h2>Файлы cookie</h2><p>Наш сайт сохраняет в вашем браузере настройки, необходимые для его работы (язык, тема, выбор cookie). С вашего согласия также используются cookie Google Analytics, Google Ads и Яндекс Метрики для статистики посещений и оценки рекламы; эти данные могут обрабатываться Google и Яндексом на серверах за пределами Турции. Без согласия эти cookie не используются. Изменить выбор можно в любое время по ссылке «Настройки cookie» внизу страницы.</p>')
WHERE page_id = 4 AND language_code = 'ru' AND slug = 'politika-konfidencialnosti' AND content NOT LIKE '%<h2>Файлы cookie</h2>%';
