-- Migration: 009_create_testimonials_table
-- Müşteri yorumları tablosu

CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_title` VARCHAR(100),
    `customer_image` VARCHAR(255),
    `content` TEXT NOT NULL,
    `rating` TINYINT DEFAULT 5,
    `tour_id` INT UNSIGNED,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_approved` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_approved` (`is_approved`),
    FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek yorumlar
INSERT INTO `testimonials` (`customer_name`, `customer_title`, `customer_image`, `content`, `rating`, `is_featured`, `is_approved`) VALUES
('Saul Goodman', 'CEO & Founder', 'assets/img/person/person-m-9.webp', 'Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et.', 5, 1, 1),
('Sara Wilsson', 'Designer', 'assets/img/person/person-f-7.webp', 'Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster.', 5, 1, 1),
('Jena Karlis', 'Store Owner', 'assets/img/person/person-f-8.webp', 'Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem.', 5, 1, 1),
('Matt Brandon', 'Freelancer', 'assets/img/person/person-m-6.webp', 'Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam.', 5, 1, 1),
('John Larson', 'Entrepreneur', 'assets/img/person/person-m-2.webp', 'Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam enim culpa labore duis sunt.', 5, 1, 1);
