-- Migration 025: Temel UI çevirilerini ekle (TR, EN, DE)
-- Tarih: 2026-05-03
-- Açıklama: Header, footer ve genel çeviriler

INSERT INTO `translations` (`language_code`, `trans_group`, `trans_key`, `trans_value`) VALUES
-- ========== TÜRKÇE ==========
-- Header
('tr', 'header', 'menu_home', 'Ana Sayfa'),
('tr', 'header', 'menu_about', 'Hakkımızda'),
('tr', 'header', 'menu_destinations', 'Transferler'),
('tr', 'header', 'menu_tours', 'Turlar'),
('tr', 'header', 'menu_gallery', 'Galeri'),
('tr', 'header', 'menu_blog', 'Blog'),
('tr', 'header', 'menu_contact', 'İletişim'),
('tr', 'header', 'btn_get_started', 'Başlayın'),

-- Footer
('tr', 'footer', 'footer_links_title', 'Hızlı Linkler'),
('tr', 'footer', 'footer_services_title', 'Hizmetlerimiz'),
('tr', 'footer', 'footer_follow_title', 'Bizi Takip Edin'),
('tr', 'footer', 'footer_follow_text', 'Sosyal medya hesaplarımızdan bizi takip edin.'),
('tr', 'footer', 'footer_copyright', '© 2025 Tüm hakları saklıdır.'),

-- Genel
('tr', 'general', 'phone', 'Telefon'),
('tr', 'general', 'email', 'E-posta'),
('tr', 'general', 'read_more', 'Devamını Oku'),
('tr', 'general', 'book_now', 'Şimdi Rezervasyon Yap'),
('tr', 'general', 'view_details', 'Detayları Gör'),
('tr', 'general', 'explore_now', 'Keşfet'),
('tr', 'general', 'from', 'den başlayan'),
('tr', 'general', 'starting_from', 'Başlayan fiyat'),
('tr', 'general', 'packages', 'Paket'),
('tr', 'general', 'tours', 'Tur'),
('tr', 'general', 'days', 'Gün'),
('tr', 'general', 'nights', 'Gece'),
('tr', 'general', 'person', 'Kişi'),
('tr', 'general', 'faq', 'SSS'),
('tr', 'general', 'subscribe', 'Abone Ol'),
('tr', 'general', 'send_message', 'Mesaj Gönder'),

-- ========== İNGİLİZCE ==========
-- Header
('en', 'header', 'menu_home', 'Home'),
('en', 'header', 'menu_about', 'About'),
('en', 'header', 'menu_destinations', 'Transfers'),
('en', 'header', 'menu_tours', 'Tours'),
('en', 'header', 'menu_gallery', 'Gallery'),
('en', 'header', 'menu_blog', 'Blog'),
('en', 'header', 'menu_contact', 'Contact'),
('en', 'header', 'btn_get_started', 'Get Started'),

-- Footer
('en', 'footer', 'footer_links_title', 'Quick Links'),
('en', 'footer', 'footer_services_title', 'Our Services'),
('en', 'footer', 'footer_follow_title', 'Follow Us'),
('en', 'footer', 'footer_follow_text', 'Follow us on social media for updates.'),
('en', 'footer', 'footer_copyright', '© 2025 All rights reserved.'),

-- Genel
('en', 'general', 'phone', 'Phone'),
('en', 'general', 'email', 'Email'),
('en', 'general', 'read_more', 'Read More'),
('en', 'general', 'book_now', 'Book Now'),
('en', 'general', 'view_details', 'View Details'),
('en', 'general', 'explore_now', 'Explore Now'),
('en', 'general', 'from', 'from'),
('en', 'general', 'starting_from', 'Starting from'),
('en', 'general', 'packages', 'Packages'),
('en', 'general', 'tours', 'Tours'),
('en', 'general', 'days', 'Days'),
('en', 'general', 'nights', 'Nights'),
('en', 'general', 'person', 'Person'),
('en', 'general', 'faq', 'FAQ'),
('en', 'general', 'subscribe', 'Subscribe'),
('en', 'general', 'send_message', 'Send Message'),

-- ========== ALMANCA ==========
-- Header
('de', 'header', 'menu_home', 'Startseite'),
('de', 'header', 'menu_about', 'Über uns'),
('de', 'header', 'menu_destinations', 'Transfers'),
('de', 'header', 'menu_tours', 'Touren'),
('de', 'header', 'menu_gallery', 'Galerie'),
('de', 'header', 'menu_blog', 'Blog'),
('de', 'header', 'menu_contact', 'Kontakt'),
('de', 'header', 'btn_get_started', 'Loslegen'),

-- Footer
('de', 'footer', 'footer_links_title', 'Schnelllinks'),
('de', 'footer', 'footer_services_title', 'Unsere Dienste'),
('de', 'footer', 'footer_follow_title', 'Folgen Sie uns'),
('de', 'footer', 'footer_follow_text', 'Folgen Sie uns in den sozialen Medien.'),
('de', 'footer', 'footer_copyright', '© 2025 Alle Rechte vorbehalten.'),

-- Genel
('de', 'general', 'phone', 'Telefon'),
('de', 'general', 'email', 'E-Mail'),
('de', 'general', 'read_more', 'Weiterlesen'),
('de', 'general', 'book_now', 'Jetzt buchen'),
('de', 'general', 'view_details', 'Details anzeigen'),
('de', 'general', 'explore_now', 'Entdecken'),
('de', 'general', 'from', 'ab'),
('de', 'general', 'starting_from', 'Ab'),
('de', 'general', 'packages', 'Pakete'),
('de', 'general', 'tours', 'Touren'),
('de', 'general', 'days', 'Tage'),
('de', 'general', 'nights', 'Nächte'),
('de', 'general', 'person', 'Person'),
('de', 'general', 'faq', 'FAQ'),
('de', 'general', 'subscribe', 'Abonnieren'),
('de', 'general', 'send_message', 'Nachricht senden')

ON DUPLICATE KEY UPDATE trans_value = VALUES(trans_value);
