<?php
/**
 * Full Migration - Tüm tabloları oluşturur
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
    
    // 1. Settings tablosu
    $pdo->exec("DROP TABLE IF EXISTS settings");
    $pdo->exec("CREATE TABLE settings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_type ENUM('text', 'textarea', 'boolean', 'number', 'json', 'image') DEFAULT 'text',
        setting_group VARCHAR(50) DEFAULT 'general',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "1. settings [OK]\n";
    
    // 2. Users tablosu
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("CREATE TABLE users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        avatar VARCHAR(255),
        role ENUM('admin', 'editor', 'author') DEFAULT 'editor',
        is_active TINYINT(1) DEFAULT 1,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "2. users [OK]\n";
    
    // 3. Pages tablosu
    $pdo->exec("DROP TABLE IF EXISTS pages");
    $pdo->exec("CREATE TABLE pages (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT,
        excerpt TEXT,
        featured_image VARCHAR(255),
        template VARCHAR(50) DEFAULT 'default',
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        status ENUM('draft', 'published') DEFAULT 'draft',
        is_homepage TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        author_id INT UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "3. pages [OK]\n";
    
    // 4. Menus tablosu
    $pdo->exec("DROP TABLE IF EXISTS menus");
    $pdo->exec("CREATE TABLE menus (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        location VARCHAR(50) DEFAULT 'header',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "4. menus [OK]\n";
    
    // 5. Menu Items tablosu
    $pdo->exec("DROP TABLE IF EXISTS menu_items");
    $pdo->exec("CREATE TABLE menu_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        menu_id INT UNSIGNED NOT NULL,
        parent_id INT UNSIGNED DEFAULT NULL,
        title VARCHAR(100) NOT NULL,
        url VARCHAR(255),
        target ENUM('_self', '_blank') DEFAULT '_self',
        icon VARCHAR(50),
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "5. menu_items [OK]\n";
    
    // 6. Sections tablosu
    $pdo->exec("DROP TABLE IF EXISTS sections");
    $pdo->exec("CREATE TABLE sections (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page_id INT UNSIGNED,
        section_key VARCHAR(100) NOT NULL,
        section_type VARCHAR(50) DEFAULT 'custom',
        title VARCHAR(255),
        subtitle VARCHAR(255),
        content LONGTEXT,
        settings JSON,
        background_image VARCHAR(255),
        background_video VARCHAR(255),
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "6. sections [OK]\n";
    
    // 7. Destinations tablosu
    $pdo->exec("DROP TABLE IF EXISTS destinations");
    $pdo->exec("CREATE TABLE destinations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        content LONGTEXT,
        featured_image VARCHAR(255),
        gallery JSON,
        location VARCHAR(255),
        country VARCHAR(100),
        continent VARCHAR(50),
        starting_price DECIMAL(10,2),
        badge VARCHAR(50),
        rating DECIMAL(2,1) DEFAULT 0,
        review_count INT DEFAULT 0,
        tour_count INT DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        meta_title VARCHAR(255),
        meta_description TEXT,
        status ENUM('draft', 'published') DEFAULT 'draft',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "7. destinations [OK]\n";
    
    // 8. Tour Categories tablosu
    $pdo->exec("DROP TABLE IF EXISTS tour_categories");
    $pdo->exec("CREATE TABLE tour_categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50),
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "8. tour_categories [OK]\n";
    
    // 9. Tours tablosu
    $pdo->exec("DROP TABLE IF EXISTS tours");
    $pdo->exec("CREATE TABLE tours (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        content LONGTEXT,
        featured_image VARCHAR(255),
        gallery JSON,
        destination_id INT UNSIGNED,
        category_id INT UNSIGNED,
        duration_days INT DEFAULT 1,
        duration_nights INT DEFAULT 0,
        group_size_min INT DEFAULT 1,
        group_size_max INT DEFAULT 10,
        price DECIMAL(10,2) NOT NULL,
        sale_price DECIMAL(10,2),
        currency VARCHAR(3) DEFAULT 'USD',
        highlights JSON,
        included JSON,
        excluded JSON,
        itinerary JSON,
        badge VARCHAR(50),
        difficulty_level ENUM('easy', 'moderate', 'challenging', 'extreme') DEFAULT 'moderate',
        rating DECIMAL(2,1) DEFAULT 0,
        review_count INT DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        is_bestseller TINYINT(1) DEFAULT 0,
        meta_title VARCHAR(255),
        meta_description TEXT,
        status ENUM('draft', 'published') DEFAULT 'draft',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "9. tours [OK]\n";
    
    // 10. Bookings tablosu
    $pdo->exec("DROP TABLE IF EXISTS bookings");
    $pdo->exec("CREATE TABLE bookings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        booking_number VARCHAR(20) NOT NULL UNIQUE,
        tour_id INT UNSIGNED,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(20),
        adults INT DEFAULT 1,
        children INT DEFAULT 0,
        departure_date DATE,
        special_requests TEXT,
        total_price DECIMAL(10,2),
        currency VARCHAR(3) DEFAULT 'USD',
        booking_status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
        payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "10. bookings [OK]\n";
    
    // 11. Testimonials tablosu
    $pdo->exec("DROP TABLE IF EXISTS testimonials");
    $pdo->exec("CREATE TABLE testimonials (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        customer_title VARCHAR(100),
        customer_image VARCHAR(255),
        content TEXT NOT NULL,
        rating TINYINT DEFAULT 5,
        tour_id INT UNSIGNED,
        is_featured TINYINT(1) DEFAULT 0,
        is_approved TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "11. testimonials [OK]\n";
    
    // 12. Blog Categories tablosu
    $pdo->exec("DROP TABLE IF EXISTS blog_categories");
    $pdo->exec("CREATE TABLE blog_categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "12. blog_categories [OK]\n";
    
    // 13. Blog Posts tablosu
    $pdo->exec("DROP TABLE IF EXISTS blog_posts");
    $pdo->exec("CREATE TABLE blog_posts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        excerpt TEXT,
        content LONGTEXT,
        featured_image VARCHAR(255),
        category_id INT UNSIGNED,
        author_id INT UNSIGNED,
        is_featured TINYINT(1) DEFAULT 0,
        view_count INT DEFAULT 0,
        meta_title VARCHAR(255),
        meta_description TEXT,
        status ENUM('draft', 'published') DEFAULT 'draft',
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "13. blog_posts [OK]\n";
    
    // 14. Blog Comments tablosu
    $pdo->exec("DROP TABLE IF EXISTS blog_comments");
    $pdo->exec("CREATE TABLE blog_comments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id INT UNSIGNED NOT NULL,
        parent_id INT UNSIGNED,
        author_name VARCHAR(100) NOT NULL,
        author_email VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        is_approved TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "14. blog_comments [OK]\n";
    
    // 15. Gallery Categories tablosu
    $pdo->exec("DROP TABLE IF EXISTS gallery_categories");
    $pdo->exec("CREATE TABLE gallery_categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "15. gallery_categories [OK]\n";
    
    // 16. Gallery tablosu
    $pdo->exec("DROP TABLE IF EXISTS gallery");
    $pdo->exec("CREATE TABLE gallery (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        image VARCHAR(255) NOT NULL,
        thumbnail VARCHAR(255),
        category_id INT UNSIGNED,
        is_featured TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "16. gallery [OK]\n";
    
    // 17. Contacts tablosu
    $pdo->exec("DROP TABLE IF EXISTS contacts");
    $pdo->exec("CREATE TABLE contacts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(255),
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "17. contacts [OK]\n";
    
    // 18. FAQ Categories tablosu
    $pdo->exec("DROP TABLE IF EXISTS faq_categories");
    $pdo->exec("CREATE TABLE faq_categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "18. faq_categories [OK]\n";
    
    // 19. FAQs tablosu
    $pdo->exec("DROP TABLE IF EXISTS faqs");
    $pdo->exec("CREATE TABLE faqs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT UNSIGNED,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "19. faqs [OK]\n";
    
    // 20. Sliders tablosu
    $pdo->exec("DROP TABLE IF EXISTS sliders");
    $pdo->exec("CREATE TABLE sliders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        subtitle TEXT,
        image VARCHAR(255),
        video VARCHAR(255),
        button_text VARCHAR(100),
        button_url VARCHAR(255),
        button2_text VARCHAR(100),
        button2_url VARCHAR(255),
        overlay_color VARCHAR(50),
        text_position ENUM('left', 'center', 'right') DEFAULT 'left',
        location VARCHAR(50) DEFAULT 'home',
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "20. sliders [OK]\n";
    
    // 21. Features tablosu
    $pdo->exec("DROP TABLE IF EXISTS features");
    $pdo->exec("CREATE TABLE features (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(50),
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "21. features [OK]\n";
    
    // 22. Languages tablosu
    $pdo->exec("DROP TABLE IF EXISTS languages");
    $pdo->exec("CREATE TABLE languages (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(5) NOT NULL UNIQUE,
        name VARCHAR(50) NOT NULL,
        native_name VARCHAR(50) NOT NULL,
        flag VARCHAR(10),
        is_default TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        is_rtl TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "22. languages [OK]\n";
    
    // 23. Translations tablosu
    $pdo->exec("DROP TABLE IF EXISTS translations");
    $pdo->exec("CREATE TABLE translations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        language_code VARCHAR(5) NOT NULL,
        trans_group VARCHAR(50) NOT NULL,
        trans_key VARCHAR(100) NOT NULL,
        trans_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_translation (language_code, trans_group, trans_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "23. translations [OK]\n";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Varsayılan veriler
    echo "\n--- Varsayılan veriler ekleniyor ---\n";
    
    // Admin kullanıcı
    $pdo->exec("INSERT INTO users (username, email, password, full_name, role) VALUES 
        ('admin', 'admin@example.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'Administrator', 'admin')");
    echo "Admin kullanıcı eklendi\n";
    
    // Diller
    $pdo->exec("INSERT INTO languages (code, name, native_name, flag, is_default, sort_order) VALUES 
        ('tr', 'Turkish', 'Türkçe', '🇹🇷', 1, 1),
        ('en', 'English', 'English', '🇬🇧', 0, 2)");
    echo "Diller eklendi\n";
    
    // Menüler
    $pdo->exec("INSERT INTO menus (name, slug, location) VALUES 
        ('Ana Menü', 'ana-menu', 'header'),
        ('Footer Menü', 'footer-menu', 'footer')");
    echo "Menüler eklendi\n";
    
    // Menü öğeleri
    $pdo->exec("INSERT INTO menu_items (menu_id, title, url, sort_order) VALUES 
        (1, 'Ana Sayfa', '/', 1),
        (1, 'Turlar', '/turlar', 2),
        (1, 'Destinasyonlar', '/destinasyonlar', 3),
        (1, 'Blog', '/blog', 4),
        (1, 'İletişim', '/iletisim', 5)");
    echo "Menü öğeleri eklendi\n";
    
    // Sayfalar
    $pdo->exec("INSERT INTO pages (title, slug, status, is_homepage, sort_order) VALUES 
        ('Ana Sayfa', 'ana-sayfa', 'published', 1, 1),
        ('Hakkımızda', 'hakkimizda', 'published', 0, 2),
        ('İletişim', 'iletisim', 'published', 0, 3)");
    echo "Sayfalar eklendi\n";
    
    // Ayarlar
    $pdo->exec("INSERT INTO settings (setting_key, setting_value, setting_type, setting_group) VALUES 
        ('site_name', 'Tour', 'text', 'general'),
        ('site_description', 'Tur ve Seyahat Firması', 'textarea', 'general'),
        ('contact_email', 'info@example.com', 'text', 'contact'),
        ('contact_phone', '+90 555 123 4567', 'text', 'contact'),
        ('contact_address', 'İstanbul, Türkiye', 'textarea', 'contact')");
    echo "Ayarlar eklendi\n";
    
    echo "\n=== TÜM TABLOLAR OLUŞTURULDU! ===\n";
    
    // Tablo sayısını kontrol et
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "\nToplam " . count($tables) . " tablo mevcut.\n";
    
} catch (PDOException $e) {
    echo "[HATA] " . $e->getMessage() . "\n";
}
