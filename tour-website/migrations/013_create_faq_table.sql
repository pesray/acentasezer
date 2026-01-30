-- Migration: 013_create_faq_table
-- SSS tablosu

CREATE TABLE IF NOT EXISTS `faq_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faqs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_active` (`is_active`),
    FOREIGN KEY (`category_id`) REFERENCES `faq_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SSS kategorileri
INSERT INTO `faq_categories` (`name`, `slug`, `sort_order`) VALUES
('Genel Sorular', 'genel-sorular', 1),
('Rezervasyon', 'rezervasyon', 2),
('Ödeme', 'odeme', 3),
('İptal & İade', 'iptal-iade', 4);

-- Örnek SSS
INSERT INTO `faqs` (`category_id`, `question`, `answer`, `sort_order`) VALUES
(1, 'Turlarınız neleri kapsıyor?', 'Turlarımız genellikle konaklama, ulaşım, rehberlik hizmetleri ve belirtilen aktiviteleri kapsar. Her turun detaylı içeriği tur sayfasında belirtilmektedir.', 1),
(1, 'Grup turları mı yoksa özel turlar mı sunuyorsunuz?', 'Her iki seçeneği de sunuyoruz. Grup turlarımız ekonomik seçenekler sunarken, özel turlarımız tamamen size özel planlanır.', 2),
(2, 'Nasıl rezervasyon yapabilirim?', 'Web sitemiz üzerinden online rezervasyon yapabilir veya müşteri hizmetlerimizi arayarak telefonla rezervasyon oluşturabilirsiniz.', 1),
(2, 'Rezervasyon için ne kadar önceden başvurmalıyım?', 'Popüler turlar için en az 2-3 hafta önceden rezervasyon yapmanızı öneririz. Özel dönemlerde bu süre daha uzun olabilir.', 2),
(3, 'Hangi ödeme yöntemlerini kabul ediyorsunuz?', 'Kredi kartı, banka havalesi ve kapıda ödeme seçeneklerimiz mevcuttur.', 1),
(4, 'İptal politikanız nedir?', 'Tur başlangıcından 14 gün öncesine kadar yapılan iptallerde tam iade, 7-14 gün arası %50 iade yapılmaktadır. 7 günden az sürede iade yapılamamaktadır.', 1);
