<?php
/**
 * Çeviri tablolarını ekle
 */

$host = '5.2.85.141';
$dbname = 'ahmetkes_agency';
$username = 'ahmetkes_sezer';
$password = 'Szr4569*-';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Page Translations
    $pdo->exec("DROP TABLE IF EXISTS page_translations");
    $pdo->exec("CREATE TABLE page_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(255),
        slug VARCHAR(255),
        content LONGTEXT,
        excerpt TEXT,
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_page_lang (page_id, language_code),
        INDEX idx_page_id (page_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "page_translations [OK]\n";
    
    // Destination Translations
    $pdo->exec("DROP TABLE IF EXISTS destination_translations");
    $pdo->exec("CREATE TABLE destination_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        destination_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(255),
        slug VARCHAR(255),
        description TEXT,
        content LONGTEXT,
        meta_title VARCHAR(255),
        meta_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_dest_lang (destination_id, language_code),
        INDEX idx_destination_id (destination_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "destination_translations [OK]\n";
    
    // Tour Translations
    $pdo->exec("DROP TABLE IF EXISTS tour_translations");
    $pdo->exec("CREATE TABLE tour_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tour_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(255),
        slug VARCHAR(255),
        description TEXT,
        content LONGTEXT,
        highlights JSON,
        included JSON,
        excluded JSON,
        itinerary JSON,
        meta_title VARCHAR(255),
        meta_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_tour_lang (tour_id, language_code),
        INDEX idx_tour_id (tour_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "tour_translations [OK]\n";
    
    // Section Translations
    $pdo->exec("DROP TABLE IF EXISTS section_translations");
    $pdo->exec("CREATE TABLE section_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        section_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(255),
        subtitle VARCHAR(255),
        content LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_section_lang (section_id, language_code),
        INDEX idx_section_id (section_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "section_translations [OK]\n";
    
    // Menu Item Translations
    $pdo->exec("DROP TABLE IF EXISTS menu_item_translations");
    $pdo->exec("CREATE TABLE menu_item_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        menu_item_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(100),
        url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_menu_item_lang (menu_item_id, language_code),
        INDEX idx_menu_item_id (menu_item_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "menu_item_translations [OK]\n";
    
    // Slider Translations
    $pdo->exec("DROP TABLE IF EXISTS slider_translations");
    $pdo->exec("CREATE TABLE slider_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slider_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(255),
        subtitle TEXT,
        button_text VARCHAR(100),
        button2_text VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_slider_lang (slider_id, language_code),
        INDEX idx_slider_id (slider_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "slider_translations [OK]\n";
    
    // Testimonial Translations
    $pdo->exec("DROP TABLE IF EXISTS testimonial_translations");
    $pdo->exec("CREATE TABLE testimonial_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        testimonial_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        content TEXT,
        customer_title VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_testimonial_lang (testimonial_id, language_code),
        INDEX idx_testimonial_id (testimonial_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "testimonial_translations [OK]\n";
    
    // Feature Translations
    $pdo->exec("DROP TABLE IF EXISTS feature_translations");
    $pdo->exec("CREATE TABLE feature_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        feature_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(255),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_feature_lang (feature_id, language_code),
        INDEX idx_feature_id (feature_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "feature_translations [OK]\n";
    
    // Blog Post Translations
    $pdo->exec("DROP TABLE IF EXISTS blog_post_translations");
    $pdo->exec("CREATE TABLE blog_post_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        title VARCHAR(255),
        slug VARCHAR(255),
        excerpt TEXT,
        content LONGTEXT,
        meta_title VARCHAR(255),
        meta_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_post_lang (post_id, language_code),
        INDEX idx_post_id (post_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "blog_post_translations [OK]\n";
    
    // FAQ Translations
    $pdo->exec("DROP TABLE IF EXISTS faq_translations");
    $pdo->exec("CREATE TABLE faq_translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        faq_id INT UNSIGNED NOT NULL,
        language_code VARCHAR(5) NOT NULL,
        question TEXT,
        answer TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_faq_lang (faq_id, language_code),
        INDEX idx_faq_id (faq_id),
        INDEX idx_language_code (language_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "faq_translations [OK]\n";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Tablo sayısını kontrol et
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "\n=== Çeviri tabloları eklendi! ===\n";
    echo "Toplam " . count($tables) . " tablo mevcut.\n";
    
} catch (PDOException $e) {
    echo "[HATA] " . $e->getMessage() . "\n";
}
