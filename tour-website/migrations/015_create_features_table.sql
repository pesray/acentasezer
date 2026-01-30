-- Migration: 015_create_features_table
-- Özellikler/Neden Biz tablosu

CREATE TABLE IF NOT EXISTS `features` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(50),
    `image` VARCHAR(255),
    `section` VARCHAR(50) DEFAULT 'why_us',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_section` (`section`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan özellikler
INSERT INTO `features` (`title`, `description`, `icon`, `section`, `sort_order`) VALUES
('Local Experts', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium totam.', 'bi-people-fill', 'why_us', 1),
('Safe & Secure', 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum.', 'bi-shield-check', 'why_us', 2),
('Best Prices', 'Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet consectetur adipisci velit.', 'bi-cash', 'why_us', 3),
('24/7 Support', 'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam nisi.', 'bi-headset', 'why_us', 4),
('Global Destinations', 'Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae.', 'bi-geo-alt-fill', 'why_us', 5),
('Premium Experience', 'Excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim.', 'bi-star-fill', 'why_us', 6);
