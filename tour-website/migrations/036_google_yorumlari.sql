-- =====================================================================
-- 036 — Google yorumları (Sezer, 2026-11-26)
--
-- Anasayfadaki "Müşteri Yorumları" alanı için yorum listesi.
-- Yorumlar admin > İçerik > Google Yorumları ekranından elle girilir; her yorum
-- tek tıkla gizlenip gösterilebilir. Alanın kendisi (aç/kapat, başlıklar, puan,
-- yorum sayısı, Google bağlantısı) Anasayfa & Tema > Google Yorumları kartından yönetilir
-- (sections tablosu, section_type = sun_reviews — o satırı panel kendisi oluşturur).
--
-- Yalnız yeni tablo oluşturur; mevcut hiçbir tabloya dokunmaz.
-- Tekrar çalıştırılabilir (IF NOT EXISTS). MySQL 8 + MariaDB 10.4 uyumlu.
--
-- Geri alma (gerekirse): DROP TABLE `google_reviews`;
-- =====================================================================

CREATE TABLE IF NOT EXISTS `google_reviews` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `author_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_photo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'isteğe bağlı; boşsa baş harf gösterilir',
  `rating` tinyint UNSIGNED NOT NULL DEFAULT 5,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'yorumun yazıldığı dil (tr, en, de, ru, nl ...)',
  `review_date` date DEFAULT NULL COMMENT 'yaklaşık tarih; sitede "3 ay önce" gibi gösterilir',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_google_reviews_visible` (`is_visible`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
