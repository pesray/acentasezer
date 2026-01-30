<?php
/**
 * Transfer Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Aktif dilleri al
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

// Image kolonu yoksa ekle
try {
    $checkCol = $db->query("SHOW COLUMNS FROM destinations LIKE 'image'")->fetch();
    if (!$checkCol) {
        $db->query("ALTER TABLE destinations ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER badge");
    }
} catch (Exception $e) {}

// destination_vehicles tablosunu oluştur
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS destination_vehicles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            destination_id INT NOT NULL,
            vehicle_id INT NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_dest_vehicle (destination_id, vehicle_id),
            FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) {}

// page_settings tablosunu oluştur (sayfa ayarları için)
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS page_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_key VARCHAR(50) NOT NULL UNIQUE,
            background_image VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS page_setting_translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_setting_id INT NOT NULL,
            language_code VARCHAR(5) NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            slug VARCHAR(255) DEFAULT NULL,
            subtitle TEXT DEFAULT NULL,
            UNIQUE KEY unique_page_lang (page_setting_id, language_code),
            FOREIGN KEY (page_setting_id) REFERENCES page_settings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Slug kolonu yoksa ekle
    try {
        $db->query("ALTER TABLE page_setting_translations ADD COLUMN slug VARCHAR(255) DEFAULT NULL AFTER title");
    } catch (Exception $e) {}
    
    // features_visible kolonu yoksa ekle
    try {
        $db->query("ALTER TABLE page_settings ADD COLUMN features_visible TINYINT(1) DEFAULT 1");
    } catch (Exception $e) {}
    
    // Destinasyonlar sayfası için varsayılan kayıt
    $db->query("INSERT IGNORE INTO page_settings (page_key) VALUES ('destinations')");
    $db->query("INSERT IGNORE INTO page_settings (page_key) VALUES ('destination_detail')");
} catch (Exception $e) {}

// Transfer detay sayfası çevirileri için tablo
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS transfer_detail_translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            language_code VARCHAR(5) NOT NULL UNIQUE,
            available_vehicles VARCHAR(255) DEFAULT NULL,
            choose_vehicle VARCHAR(255) DEFAULT NULL,
            passengers VARCHAR(100) DEFAULT NULL,
            luggage VARCHAR(100) DEFAULT NULL,
            book_now VARCHAR(100) DEFAULT NULL,
            transfer_features VARCHAR(255) DEFAULT NULL,
            what_we_offer VARCHAR(255) DEFAULT NULL,
            vehicle_type VARCHAR(100) DEFAULT NULL,
            continue_booking VARCHAR(255) DEFAULT NULL,
            change_vehicle VARCHAR(100) DEFAULT NULL,
            full_name VARCHAR(100) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            phone VARCHAR(100) DEFAULT NULL,
            flight_date VARCHAR(100) DEFAULT NULL,
            flight_time VARCHAR(100) DEFAULT NULL,
            flight_number VARCHAR(100) DEFAULT NULL,
            adults_count VARCHAR(100) DEFAULT NULL,
            child_seat VARCHAR(100) DEFAULT NULL,
            yes_no VARCHAR(255) DEFAULT NULL,
            hotel_address VARCHAR(255) DEFAULT NULL,
            notes VARCHAR(100) DEFAULT NULL,
            location VARCHAR(100) DEFAULT NULL,
            transfer_route VARCHAR(255) DEFAULT NULL,
            gallery VARCHAR(100) DEFAULT NULL,
            gallery_desc VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Yeni alanları ekle (mevcut tabloya)
    $newColumns = [
        'change_vehicle', 'full_name', 'email', 'phone', 
        'flight_date', 'flight_time', 'flight_number',
        'adults_count', 'children_count', 'child_seat', 'yes_no', 'hotel_address',
        'return_transfer', 'return_flight_date', 'return_flight_time',
        'return_flight_number', 'return_pickup_time', 'return_hotel_address',
        'transfer_info_title', 'total_price'
    ];
    foreach ($newColumns as $col) {
        try {
            $db->query("ALTER TABLE transfer_detail_translations ADD COLUMN {$col} VARCHAR(255) DEFAULT NULL");
        } catch (Exception $e) {}
    }
} catch (Exception $e) {}

// destination_translations tablosuna from_location ve to_location ekle
try {
    $db->query("ALTER TABLE destination_translations ADD COLUMN from_location VARCHAR(255) DEFAULT NULL AFTER slug");
} catch (Exception $e) {}
try {
    $db->query("ALTER TABLE destination_translations ADD COLUMN to_location VARCHAR(255) DEFAULT NULL AFTER from_location");
} catch (Exception $e) {}

// Transfer özellikleri için dinamik tablo (icon, başlık, açıklama)
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS transfer_features (
            id INT AUTO_INCREMENT PRIMARY KEY,
            icon VARCHAR(100) NOT NULL DEFAULT 'bi-check-circle',
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS transfer_feature_translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            feature_id INT NOT NULL,
            language_code VARCHAR(5) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            UNIQUE KEY unique_feature_lang (feature_id, language_code),
            FOREIGN KEY (feature_id) REFERENCES transfer_features(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) {}

// Sözleşme çevirileri için tablo
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS terms_translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            language_code VARCHAR(5) NOT NULL UNIQUE,
            title VARCHAR(255) DEFAULT NULL,
            checkbox_text VARCHAR(255) DEFAULT NULL,
            content TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) {}

// Uyarı mesajı çevirileri için tablo
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS booking_alert_translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            language_code VARCHAR(5) NOT NULL UNIQUE,
            icon VARCHAR(100) DEFAULT 'bi-exclamation-triangle',
            color VARCHAR(20) DEFAULT 'warning',
            message TEXT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) {}

// Sayfa ayarları kaydetme (Transfer Sayfası)
if ($action === 'page_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pageKey = 'destinations';
        $backgroundImage = trim($_POST['page_background_image'] ?? '');
        $pageTranslations = $_POST['page_translations'] ?? [];
        
        // Sayfa ayarını al veya oluştur
        $stmt = $db->prepare("SELECT id FROM page_settings WHERE page_key = ?");
        $stmt->execute([$pageKey]);
        $pageSettingId = $stmt->fetchColumn();
        
        if (!$pageSettingId) {
            $stmt = $db->prepare("INSERT INTO page_settings (page_key, background_image) VALUES (?, ?)");
            $stmt->execute([$pageKey, $backgroundImage]);
            $pageSettingId = $db->lastInsertId();
        } else {
            $stmt = $db->prepare("UPDATE page_settings SET background_image = ? WHERE id = ?");
            $stmt->execute([$backgroundImage, $pageSettingId]);
        }
        
        // Çevirileri kaydet
        foreach ($pageTranslations as $langCode => $trans) {
            $title = trim($trans['title'] ?? '');
            $slug = trim($trans['slug'] ?? '');
            $subtitle = trim($trans['subtitle'] ?? '');
            
            $stmt = $db->prepare("
                INSERT INTO page_setting_translations (page_setting_id, language_code, title, slug, subtitle)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), subtitle = VALUES(subtitle)
            ");
            $stmt->execute([$pageSettingId, $langCode, $title, $slug, $subtitle]);
        }
        
        setFlashMessage('success', 'Sayfa ayarları kaydedildi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

// Transfer Detay çevirilerini kaydetme
if ($action === 'detail_translations' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $detailTranslations = $_POST['detail_translations'] ?? [];
        
        foreach ($detailTranslations as $langCode => $trans) {
            $fields = [
                'available_vehicles', 'choose_vehicle', 'passengers', 'luggage', 'book_now',
                'transfer_features', 'what_we_offer', 'make_reservation', 'fill_form',
                'pickup_date', 'pickup_time', 'person', 'vehicle_type', 'select_vehicle',
                'pickup_address', 'pickup_address_placeholder', 'notes', 'notes_placeholder',
                'continue_booking', 'location', 'transfer_route', 'gallery', 'gallery_desc',
                'ready_to_book', 'contact_us_help', 'contact_us'
            ];
            
            $values = ['language_code' => $langCode];
            foreach ($fields as $field) {
                $values[$field] = trim($trans[$field] ?? '');
            }
            
            $columns = implode(', ', array_keys($values));
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $updates = implode(', ', array_map(fn($f) => "$f = VALUES($f)", $fields));
            
            $stmt = $db->prepare("
                INSERT INTO transfer_detail_translations ($columns)
                VALUES ($placeholders)
                ON DUPLICATE KEY UPDATE $updates
            ");
            $stmt->execute(array_values($values));
        }
        
        setFlashMessage('success', 'Transfer detay çevirileri kaydedildi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

// Transfer özelliklerini kaydetme
if ($action === 'save_features' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        $featuresVisible = isset($_POST['features_visible']) ? 1 : 0;
        $features = $_POST['features'] ?? [];
        $featureTranslations = $_POST['feature_translations'] ?? [];
        
        // Görünürlük ayarını page_settings'e kaydet
        $stmt = $db->prepare("SELECT id FROM page_settings WHERE page_key = ?");
        $stmt->execute(['destinations']);
        $ps = $stmt->fetch();
        
        if ($ps) {
            $stmt = $db->prepare("UPDATE page_settings SET features_visible = ? WHERE id = ?");
            $stmt->execute([$featuresVisible, $ps['id']]);
        } else {
            $stmt = $db->prepare("INSERT INTO page_settings (page_key, features_visible) VALUES (?, ?)");
            $stmt->execute(['destinations', $featuresVisible]);
        }
        
        // Mevcut özellikleri sil ve yeniden oluştur
        $db->query("DELETE FROM transfer_features");
        
        foreach ($features as $index => $feature) {
            $icon = trim($feature['icon'] ?? 'bi-check-circle');
            $sortOrder = (int)($feature['sort_order'] ?? $index);
            $isActive = isset($feature['is_active']) ? 1 : 1; // Varsayılan aktif
            
            $stmt = $db->prepare("INSERT INTO transfer_features (icon, sort_order, is_active) VALUES (?, ?, ?)");
            $stmt->execute([$icon, $sortOrder, $isActive]);
            $featureId = $db->lastInsertId();
            
            // Çevirileri kaydet
            if (isset($featureTranslations[$index])) {
                foreach ($featureTranslations[$index] as $langCode => $trans) {
                    $title = trim($trans['title'] ?? '');
                    $description = trim($trans['description'] ?? '');
                    
                    if (!empty($title)) {
                        $stmt = $db->prepare("
                            INSERT INTO transfer_feature_translations (feature_id, language_code, title, description)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([$featureId, $langCode, $title, $description]);
                    }
                }
            }
        }
        
        // Özellikler bölümü başlık çevirilerini de kaydet
        $detailTranslations = $_POST['detail_translations'] ?? [];
        foreach ($detailTranslations as $langCode => $fields) {
            $columns = ['language_code'];
            $values = [$langCode];
            $updates = [];
            
            foreach ($fields as $key => $value) {
                $columns[] = $key;
                $values[] = trim($value);
                $updates[] = "$key = VALUES($key)";
            }
            
            if (count($columns) > 1) {
                $placeholders = array_fill(0, count($columns), '?');
                $stmt = $db->prepare("
                    INSERT INTO transfer_detail_translations (" . implode(', ', $columns) . ")
                    VALUES (" . implode(', ', $placeholders) . ")
                    ON DUPLICATE KEY UPDATE " . implode(', ', $updates) . "
                ");
                $stmt->execute($values);
            }
        }
        
        $db->commit();
        setFlashMessage('success', 'Transfer özellikleri kaydedildi.');
    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

// Sözleşme kaydetme
if ($action === 'save_terms' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $termsTranslations = $_POST['terms_translations'] ?? [];
        
        foreach ($termsTranslations as $langCode => $fields) {
            $title = trim($fields['title'] ?? '');
            $checkboxText = trim($fields['checkbox_text'] ?? '');
            $content = trim($fields['content'] ?? '');
            
            $stmt = $db->prepare("
                INSERT INTO terms_translations (language_code, title, checkbox_text, content)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    title = VALUES(title), 
                    checkbox_text = VALUES(checkbox_text), 
                    content = VALUES(content)
            ");
            $stmt->execute([$langCode, $title, $checkboxText, $content]);
        }
        
        setFlashMessage('success', 'Sözleşme metinleri kaydedildi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

// Uyarı mesajı kaydetme
if ($action === 'save_alert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $alertTranslations = $_POST['alert_translations'] ?? [];
        $alertIcon = trim($_POST['alert_icon'] ?? 'bi-exclamation-triangle');
        $alertColor = trim($_POST['alert_color'] ?? 'warning');
        $alertActive = isset($_POST['alert_active']) ? 1 : 0;
        
        foreach ($alertTranslations as $langCode => $fields) {
            $message = trim($fields['message'] ?? '');
            
            $stmt = $db->prepare("
                INSERT INTO booking_alert_translations (language_code, icon, color, message, is_active)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    icon = VALUES(icon), 
                    color = VALUES(color), 
                    message = VALUES(message),
                    is_active = VALUES(is_active)
            ");
            $stmt->execute([$langCode, $alertIcon, $alertColor, $message, $alertActive]);
        }
        
        setFlashMessage('success', 'Uyarı mesajı kaydedildi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

// Silme işlemi - header'dan önce
if ($action === 'delete' && $id) {
    try {
        $db->prepare("DELETE FROM destinations WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Transfer silindi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

// Form işlemleri - header'dan önce
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location'] ?? '');
    $startingPrice = !empty($_POST['starting_price']) ? (float)$_POST['starting_price'] : null;
    $badge = trim($_POST['badge'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    $translations = $_POST['translations'] ?? [];
    $defaultTitle = $translations[$defaultLang['code']]['title'] ?? '';
    $defaultSlug = $translations[$defaultLang['code']]['slug'] ?? '';
    
    if (empty($defaultTitle) || empty($defaultSlug)) {
        setFlashMessage('error', 'Varsayılan dil için başlık ve slug gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            if ($id) {
                $stmt = $db->prepare("
                    UPDATE destinations SET 
                        title = ?, slug = ?, description = ?, content = ?, location = ?,
                        starting_price = ?, badge = ?, image = ?,
                        is_featured = ?, meta_title = ?, meta_description = ?, status = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['description'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $location, $startingPrice, $badge, $image, $isFeatured,
                    $translations[$defaultLang['code']]['meta_title'] ?? '',
                    $translations[$defaultLang['code']]['meta_description'] ?? '',
                    $status, $sortOrder, $id
                ]);
                $destId = $id;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO destinations (title, slug, description, content, location, starting_price, badge, image, is_featured, meta_title, meta_description, status, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['description'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $location, $startingPrice, $badge, $image, $isFeatured,
                    $translations[$defaultLang['code']]['meta_title'] ?? '',
                    $translations[$defaultLang['code']]['meta_description'] ?? '',
                    $status, $sortOrder
                ]);
                $destId = $db->lastInsertId();
            }
            
            // Çevirileri kaydet (varsayılan dil dahil tüm diller için)
            foreach ($translations as $langCode => $trans) {
                if (empty($trans['title'])) continue;
                
                $stmt = $db->prepare("
                    INSERT INTO destination_translations (destination_id, language_code, title, slug, from_location, to_location, description, content, meta_title, meta_description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        title = VALUES(title), slug = VALUES(slug), from_location = VALUES(from_location), to_location = VALUES(to_location),
                        description = VALUES(description), content = VALUES(content), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description)
                ");
                $stmt->execute([
                    $destId, $langCode, $trans['title'], $trans['slug'] ?? '',
                    $trans['from_location'] ?? '', $trans['to_location'] ?? '',
                    $trans['description'] ?? '', $trans['content'] ?? '',
                    $trans['meta_title'] ?? '', $trans['meta_description'] ?? ''
                ]);
            }
            
            // Araç fiyatlarını kaydet (dil bazlı)
            $db->prepare("DELETE FROM destination_vehicles WHERE destination_id = ?")->execute([$destId]);
            
            $vehiclePrices = $_POST['vehicle_prices'] ?? [];
            $vehicleCurrencies = $_POST['vehicle_currencies'] ?? [];
            
            foreach ($vehiclePrices as $langCode => $prices) {
                $currency = $vehicleCurrencies[$langCode] ?? 'TRY';
                foreach ($prices as $vehicleId => $price) {
                    if (!empty($price) && $price > 0) {
                        $stmt = $db->prepare("INSERT INTO destination_vehicles (destination_id, vehicle_id, language_code, price, currency) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$destId, $vehicleId, $langCode, (float)$price, $currency]);
                    }
                }
            }
            
            $db->commit();
            setFlashMessage('success', $id ? 'Transfer güncellendi.' : 'Transfer eklendi.');
            header('Location: ' . ADMIN_URL . '/destinations.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

// Header'ı dahil et
$pageTitle = 'Transfer Yönetimi';
require_once __DIR__ . '/includes/header.php';

// Düzenleme için veri al
$editData = null;
$editTranslations = [];
$editVehiclePrices = [];
$editVehicleCurrencies = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM destination_translations WHERE destination_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $editTranslations[$row['language_code']] = $row;
    }
    
    // Araç fiyatlarını ve para birimlerini al (dil bazlı)
    $stmt = $db->prepare("SELECT vehicle_id, language_code, price, currency FROM destination_vehicles WHERE destination_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $editVehiclePrices[$row['language_code']][$row['vehicle_id']] = $row['price'];
        $editVehicleCurrencies[$row['language_code']] = $row['currency'];
    }
}

// Tüm destinasyonları al
$destinations = $db->query("SELECT * FROM destinations ORDER BY sort_order, title")->fetchAll();

// Tüm aktif araçları al
$vehicles = $db->query("SELECT * FROM vehicles WHERE is_active = 1 ORDER BY sort_order, brand, model")->fetchAll();

// Yeni destinasyon için bir sonraki sıralama değerini hesapla
$nextSortOrder = 1;
if ($action === 'add') {
    $maxSort = $db->query("SELECT MAX(sort_order) as max_sort FROM destinations")->fetch();
    $nextSortOrder = ($maxSort['max_sort'] ?? 0) + 1;
}

// Sayfa ayarlarını al
$pageSettings = null;
$pageSettingTranslations = [];
$stmt = $db->prepare("SELECT * FROM page_settings WHERE page_key = ?");
$stmt->execute(['destinations']);
$pageSettings = $stmt->fetch();

if ($pageSettings) {
    $stmt = $db->prepare("SELECT * FROM page_setting_translations WHERE page_setting_id = ?");
    $stmt->execute([$pageSettings['id']]);
    while ($row = $stmt->fetch()) {
        $pageSettingTranslations[$row['language_code']] = $row;
    }
}

// Transfer detay çevirilerini al
$detailTranslations = [];
$detailRows = $db->query("SELECT * FROM transfer_detail_translations")->fetchAll();
foreach ($detailRows as $row) {
    $detailTranslations[$row['language_code']] = $row;
}

// Transfer özelliklerini al
$transferFeatures = [];
$featureRows = $db->query("SELECT * FROM transfer_features ORDER BY sort_order")->fetchAll();
foreach ($featureRows as $feature) {
    $featureId = $feature['id'];
    $transferFeatures[$featureId] = [
        'id' => $featureId,
        'icon' => $feature['icon'],
        'sort_order' => $feature['sort_order'],
        'translations' => []
    ];
    
    $stmt = $db->prepare("SELECT * FROM transfer_feature_translations WHERE feature_id = ?");
    $stmt->execute([$featureId]);
    while ($trans = $stmt->fetch()) {
        $transferFeatures[$featureId]['translations'][$trans['language_code']] = $trans;
    }
}

// Sözleşme çevirilerini al
$termsTranslations = [];
try {
    $termsRows = $db->query("SELECT * FROM terms_translations")->fetchAll();
    foreach ($termsRows as $row) {
        $termsTranslations[$row['language_code']] = $row;
    }
} catch (Exception $e) {}

// Uyarı mesajı çevirilerini al
$alertTranslations = [];
$alertSettings = ['icon' => 'bi-exclamation-triangle', 'color' => 'warning', 'is_active' => 1];
try {
    $alertRows = $db->query("SELECT * FROM booking_alert_translations")->fetchAll();
    foreach ($alertRows as $row) {
        $alertTranslations[$row['language_code']] = $row;
        // İlk satırdan genel ayarları al
        if (empty($alertSettings['icon']) || $alertSettings['icon'] === 'bi-exclamation-triangle') {
            $alertSettings['icon'] = $row['icon'];
            $alertSettings['color'] = $row['color'];
            $alertSettings['is_active'] = $row['is_active'];
        }
    }
} catch (Exception $e) {}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Transfer Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <div>
        <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#pageSettingsModal">
            <i class="bi bi-gear me-1"></i> Sayfa Ayarları
        </button>
        <a href="?action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Yeni Transfer
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<form method="post" action="?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white py-2">
                    <ul class="nav nav-tabs lang-tabs card-header-tabs" role="tablist">
                        <?php foreach ($languages as $i => $lang): ?>
                        <li class="nav-item">
                            <button type="button" class="nav-link <?= $i === 0 ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#lang-<?= $lang['code'] ?>">
                                <?= e($lang['flag']) ?> <?= e($lang['native_name']) ?>
                            </button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <?php foreach ($languages as $i => $lang): 
                            $trans = $editTranslations[$lang['code']] ?? [];
                            $isDefault = $lang['is_default'];
                            if ($isDefault && $editData) {
                                $trans = [
                                    'title' => $editData['title'],
                                    'slug' => $editData['slug'],
                                    'description' => $editData['description'],
                                    'content' => $editData['content'],
                                    'meta_title' => $editData['meta_title'],
                                    'meta_description' => $editData['meta_description']
                                ];
                            }
                        ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="lang-<?= $lang['code'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Başlık <?php if ($isDefault): ?><span class="text-danger">*</span><?php endif; ?></label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][title]" 
                                       class="form-control title-input" data-lang="<?= $lang['code'] ?>"
                                       value="<?= e($trans['title'] ?? '') ?>" <?= $isDefault ? 'required' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug <?php if ($isDefault): ?><span class="text-danger">*</span><?php endif; ?></label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][slug]" 
                                       class="form-control slug-input" id="slug-<?= $lang['code'] ?>"
                                       value="<?= e($trans['slug'] ?? '') ?>" <?= $isDefault ? 'required' : '' ?>>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="bi bi-geo-alt text-success me-1"></i>Nereden</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][from_location]" 
                                           class="form-control" value="<?= e($trans['from_location'] ?? '') ?>" 
                                           placeholder="Örn: Antalya Havalimanı (AYT)">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Nereye</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][to_location]" 
                                           class="form-control" value="<?= e($trans['to_location'] ?? '') ?>" 
                                           placeholder="Örn: Side">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kısa Açıklama</label>
                                <textarea name="translations[<?= $lang['code'] ?>][description]" class="form-control" rows="3"><?= e($trans['description'] ?? '') ?></textarea>
                            </div>
                            
                            <?php 
                            // Para birimleri listesi (istenen sırada)
                            $currencies = [
                                'EUR' => '€ EUR (Euro)',
                                'USD' => '$ USD (Amerikan Doları)',
                                'TRY' => '₺ TRY (Türk Lirası)',
                                'GBP' => '£ GBP (İngiliz Sterlini)',
                            ];
                            // Bu dil için kayıtlı para birimini al
                            $savedCurrency = $editVehicleCurrencies[$lang['code']] ?? 'EUR';
                            ?>
                            <div class="card bg-light mb-3">
                                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-car-front me-2"></i>Araç Fiyatlandırması</h6>
                                    <select name="vehicle_currencies[<?= $lang['code'] ?>]" class="form-select form-select-sm" style="width: auto;">
                                        <?php foreach ($currencies as $code => $label): ?>
                                        <option value="<?= $code ?>" <?= $savedCurrency === $code ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($vehicles)): ?>
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-info-circle me-1"></i> Henüz araç eklenmemiş. 
                                        <a href="<?= ADMIN_URL ?>/vehicles.php?action=add">Araç ekle</a>
                                    </div>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="30"></th>
                                                    <th>Araç</th>
                                                    <th>Kapasite</th>
                                                    <th width="130">Fiyat</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($vehicles as $vehicle): 
                                                    $isSelected = isset($editVehiclePrices[$lang['code']][$vehicle['id']]);
                                                    $vehiclePrice = $editVehiclePrices[$lang['code']][$vehicle['id']] ?? '';
                                                ?>
                                                <tr class="vehicle-row-<?= $lang['code'] ?>-<?= $vehicle['id'] ?>">
                                                    <td>
                                                        <input type="checkbox" class="form-check-input vehicle-checkbox" 
                                                               data-lang="<?= $lang['code'] ?>" data-vehicle="<?= $vehicle['id'] ?>"
                                                               <?= $isSelected ? 'checked' : '' ?>>
                                                    </td>
                                                    <td class="vehicle-row-clickable" style="cursor: pointer;">
                                                        <?php if (!empty($vehicle['image'])): ?>
                                                        <img src="<?= e(getMediaUrl($vehicle['image'])) ?>" alt="" class="rounded me-2" style="width: 40px; height: 28px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <strong><?= e($vehicle['brand']) ?> <?= e($vehicle['model']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <i class="bi bi-people"></i> <?= (int)$vehicle['capacity'] ?>
                                                            <i class="bi bi-briefcase ms-1"></i> <?= (int)$vehicle['luggage_capacity'] ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="vehicle_prices[<?= $lang['code'] ?>][<?= $vehicle['id'] ?>]" 
                                                               class="form-control form-control-sm vehicle-price" 
                                                               id="price-<?= $lang['code'] ?>-<?= $vehicle['id'] ?>"
                                                               step="0.01" min="0"
                                                               value="<?= $vehiclePrice ?>" 
                                                               placeholder="0.00"
                                                               <?= !$isSelected ? 'disabled' : '' ?>>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="bi bi-info-circle me-1"></i> Seçili araçlar bu transfer için aktif olacaktır.
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Başlık</label>
                                        <input type="text" name="translations[<?= $lang['code'] ?>][meta_title]" 
                                               class="form-control meta-title-input" id="meta-title-<?= $lang['code'] ?>"
                                               value="<?= e($trans['meta_title'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Açıklama</label>
                                        <textarea name="translations[<?= $lang['code'] ?>][meta_description]" class="form-control" rows="2"><?= e($trans['meta_description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Transfer Ayarları</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Görsel</label>
                        <div class="input-group">
                            <input type="text" name="image" id="dest_image" class="form-control" value="<?= e($editData['image'] ?? '') ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('dest_image', 'image')">
                                <i class="bi bi-folder2-open"></i> Seç
                            </button>
                        </div>
                        <?php if (!empty($editData['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= e(getMediaUrl($editData['image'])) ?>" alt="Önizleme" class="img-thumbnail" style="max-height: 120px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <?php $currentStatus = $editData['status'] ?? 'published'; ?>
                            <option value="published" <?= $currentStatus === 'published' ? 'selected' : '' ?>>Yayında</option>
                            <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Taslak</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konum</label>
                        <input type="text" name="location" class="form-control" value="<?= e($editData['location'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rozet</label>
                        <input type="text" name="badge" class="form-control" value="<?= e($editData['badge'] ?? '') ?>" placeholder="Popüler, Yeni...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? $nextSortOrder) ?>">
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1"
                               <?= ($editData['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-1"></i> Kaydet
                </button>
                <a href="<?= ADMIN_URL ?>/destinations.php" class="btn btn-outline-secondary">İptal</a>
            </div>
        </div>
    </div>
</form>

<?php else: ?>
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="60">Sıra</th>
                        <th>Başlık</th>
                        <th>Konum</th>
                        <th>Araçlar</th>
                        <th width="80">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($destinations as $dest): 
                        // Bu destinasyon için araç sayısını al
                        $vehicleCountStmt = $db->prepare("SELECT COUNT(*) FROM destination_vehicles WHERE destination_id = ?");
                        $vehicleCountStmt->execute([$dest['id']]);
                        $vehicleCount = $vehicleCountStmt->fetchColumn();
                    ?>
                    <tr>
                        <td><?= (int)$dest['sort_order'] ?></td>
                        <td>
                            <strong><?= e($dest['title']) ?></strong>
                            <?php if ($dest['is_featured']): ?>
                            <span class="badge bg-warning text-dark ms-1">Öne Çıkan</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($dest['location'] ?? '-') ?></td>
                        <td>
                            <?php if ($vehicleCount > 0): ?>
                            <span class="badge bg-info"><i class="bi bi-car-front me-1"></i><?= $vehicleCount ?> araç</span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($dest['status'] === 'published'): ?>
                            <span class="badge bg-success">Yayında</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Taslak</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?= $dest['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?action=delete&id=<?= $dest['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Türkçe karakterleri İngilizce'ye çevir
    function turkishToEnglish(str) {
        const charMap = {
            'ç': 'c', 'Ç': 'C',
            'ğ': 'g', 'Ğ': 'G',
            'ı': 'i', 'I': 'I',
            'İ': 'I', 'i': 'i',
            'ö': 'o', 'Ö': 'O',
            'ş': 's', 'Ş': 'S',
            'ü': 'u', 'Ü': 'U',
            'ä': 'a', 'Ä': 'A',
            'ß': 'ss',
            'é': 'e', 'è': 'e', 'ê': 'e', 'ë': 'e',
            'à': 'a', 'â': 'a', 'á': 'a',
            'ù': 'u', 'û': 'u', 'ú': 'u',
            'î': 'i', 'ï': 'i', 'í': 'i',
            'ô': 'o', 'ó': 'o', 'ò': 'o',
            'ñ': 'n'
        };
        return str.split('').map(char => charMap[char] || char).join('');
    }
    
    // Slug oluştur
    function createSlug(str) {
        return turkishToEnglish(str)
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
    
    // Başlık değiştiğinde Slug ve Meta Başlık otomatik doldur
    document.querySelectorAll('.title-input').forEach(function(titleInput) {
        titleInput.addEventListener('input', function() {
            const lang = this.dataset.lang;
            const slugInput = document.getElementById('slug-' + lang);
            const metaTitleInput = document.getElementById('meta-title-' + lang);
            const title = this.value;
            
            // Slug alanı boşsa veya daha önce otomatik oluşturulduysa güncelle
            if (slugInput && (slugInput.value === '' || slugInput.dataset.autoGenerated === 'true')) {
                slugInput.value = createSlug(title);
                slugInput.dataset.autoGenerated = 'true';
            }
            
            // Meta Başlık alanı boşsa veya daha önce otomatik oluşturulduysa güncelle
            if (metaTitleInput && (metaTitleInput.value === '' || metaTitleInput.dataset.autoGenerated === 'true')) {
                metaTitleInput.value = title;
                metaTitleInput.dataset.autoGenerated = 'true';
            }
        });
    });
    
    // Slug manuel değiştirilirse otomatik güncellemeyi durdur
    document.querySelectorAll('.slug-input').forEach(function(slugInput) {
        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    });
    
    // Meta Başlık manuel değiştirilirse otomatik güncellemeyi durdur
    document.querySelectorAll('.meta-title-input').forEach(function(metaTitleInput) {
        metaTitleInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    });
    
    // Checkbox değişikliğinde fiyat alanını aktif/pasif yap
    document.querySelectorAll('.vehicle-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const lang = this.dataset.lang;
            const vehicleId = this.dataset.vehicle;
            const priceInput = document.getElementById('price-' + lang + '-' + vehicleId);
            
            if (this.checked) {
                priceInput.disabled = false;
                priceInput.focus();
            } else {
                priceInput.disabled = true;
                priceInput.value = '';
            }
        });
    });
    
    // Araç satırına (görsel ve marka/model) tıklayınca checkbox'u toggle et
    document.querySelectorAll('.vehicle-row-clickable').forEach(function(cell) {
        cell.addEventListener('click', function() {
            const checkbox = this.closest('tr').querySelector('.vehicle-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });
    
    // Sayfa Ayarları Modal - Başlıktan slug otomatik oluştur
    document.querySelectorAll('.page-title-input').forEach(function(titleInput) {
        titleInput.addEventListener('input', function() {
            const lang = this.dataset.lang;
            const slugInput = document.getElementById('page-slug-' + lang);
            const title = this.value;
            
            if (slugInput && (slugInput.value === '' || slugInput.dataset.autoGenerated === 'true')) {
                slugInput.value = createSlug(title);
                slugInput.dataset.autoGenerated = 'true';
            }
        });
    });
    
    // Sayfa slug manuel değiştirilirse otomatik güncellemeyi durdur
    document.querySelectorAll('.page-slug-input').forEach(function(slugInput) {
        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    });
});
</script>

<!-- Sayfa Ayarları Modal -->
<div class="modal fade" id="pageSettingsModal" tabindex="-1" aria-labelledby="pageSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageSettingsModalLabel">
                    <i class="bi bi-gear me-2"></i>Sayfa Ayarları
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <!-- Ana Tab Menüsü -->
                <ul class="nav nav-pills mb-4" id="settingsMainTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-page-settings" data-bs-toggle="pill" data-bs-target="#pane-page-settings" type="button">
                            <i class="bi bi-list-ul me-1"></i> Transfer Sayfası
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-detail-settings" data-bs-toggle="pill" data-bs-target="#pane-detail-settings" type="button">
                            <i class="bi bi-file-text me-1"></i> Transfer Detay
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-features-settings" data-bs-toggle="pill" data-bs-target="#pane-features-settings" type="button">
                            <i class="bi bi-star me-1"></i> Transfer Özellikleri
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-terms-settings" data-bs-toggle="pill" data-bs-target="#pane-terms-settings" type="button">
                            <i class="bi bi-file-earmark-text me-1"></i> Sözleşme
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-alert-settings" data-bs-toggle="pill" data-bs-target="#pane-alert-settings" type="button">
                            <i class="bi bi-exclamation-triangle me-1"></i> Uyarı
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Tab 1: Transfer Sayfası -->
                    <div class="tab-pane fade show active" id="pane-page-settings" role="tabpanel">
                        <form method="post" action="?action=page_settings" id="pageSettingsForm">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Sayfa Arka Plan Görseli</label>
                                <div class="input-group">
                                    <input type="text" name="page_background_image" id="page_bg_image" class="form-control" 
                                           value="<?= e($pageSettings['background_image'] ?? '') ?>" placeholder="Görsel seçin...">
                                    <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('page_bg_image', 'image')">
                                        <i class="bi bi-folder2-open"></i> Seç
                                    </button>
                                </div>
                                <?php if (!empty($pageSettings['background_image'])): ?>
                                <div class="mt-2">
                                    <img src="<?= e(getMediaUrl($pageSettings['background_image'])) ?>" alt="Önizleme" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <?php endif; ?>
                                <small class="text-muted">Boş bırakılırsa varsayılan görsel kullanılır.</small>
                            </div>
                            
                            <hr>
                            
                            <h6 class="fw-bold mb-3"><i class="bi bi-translate me-2"></i>Dil Bazlı Ayarlar</h6>
                            
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach ($languages as $i => $lang): ?>
                                <li class="nav-item">
                                    <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" type="button" 
                                            data-bs-toggle="tab" data-bs-target="#page-lang-<?= $lang['code'] ?>">
                                        <?= strtoupper($lang['code']) ?> <?= e($lang['name']) ?>
                                    </button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div class="tab-content border border-top-0 rounded-bottom p-3">
                                <?php foreach ($languages as $i => $lang): 
                                    $pageTrans = $pageSettingTranslations[$lang['code']] ?? [];
                                ?>
                                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="page-lang-<?= $lang['code'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Sayfa Başlığı</label>
                                        <input type="text" name="page_translations[<?= $lang['code'] ?>][title]" 
                                               class="form-control page-title-input" data-lang="<?= $lang['code'] ?>"
                                               value="<?= e($pageTrans['title'] ?? '') ?>" placeholder="Örn: Transferler, Transfers, Transfers...">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Slug (URL)</label>
                                        <input type="text" name="page_translations[<?= $lang['code'] ?>][slug]" 
                                               class="form-control page-slug-input" id="page-slug-<?= $lang['code'] ?>"
                                               value="<?= e($pageTrans['slug'] ?? '') ?>" placeholder="Örn: transferler, transfers, transfers...">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sayfa Alt Başlığı / Açıklaması</label>
                                        <textarea name="page_translations[<?= $lang['code'] ?>][subtitle]" class="form-control" rows="2" 
                                                  placeholder="Sayfa başlığının altında görünecek kısa açıklama..."><?= e($pageTrans['subtitle'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Transfer Sayfası Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab 2: Transfer Detay Çevirileri -->
                    <div class="tab-pane fade" id="pane-detail-settings" role="tabpanel">
                        <form method="post" action="?action=detail_translations" id="detailTranslationsForm">
                            <p class="text-muted mb-4">
                                <i class="bi bi-info-circle me-1"></i>
                                Transfer detay sayfasında görünen tüm metinleri buradan yönetebilirsiniz.
                            </p>
                            
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach ($languages as $i => $lang): ?>
                                <li class="nav-item">
                                    <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" type="button" 
                                            data-bs-toggle="tab" data-bs-target="#detail-lang-<?= $lang['code'] ?>">
                                        <?= strtoupper($lang['code']) ?> <?= e($lang['name']) ?>
                                    </button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div class="tab-content border border-top-0 rounded-bottom p-3" style="max-height: 500px; overflow-y: auto;">
                                <?php foreach ($languages as $i => $lang): 
                                    $dt = $detailTranslations[$lang['code']] ?? [];
                                ?>
                                <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="detail-lang-<?= $lang['code'] ?>">
                                    
                                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-car-front me-1"></i> Araçlar Bölümü</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mevcut Araçlar</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][available_vehicles]" class="form-control" 
                                                   value="<?= e($dt['available_vehicles'] ?? '') ?>" placeholder="Mevcut Araçlar / Available Vehicles">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Araç Seçin Alt Başlık</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][choose_vehicle]" class="form-control" 
                                                   value="<?= e($dt['choose_vehicle'] ?? '') ?>" placeholder="Size uygun aracı seçin">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Yolcu</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][passengers]" class="form-control" 
                                                   value="<?= e($dt['passengers'] ?? '') ?>" placeholder="Yolcu / Passengers">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Bagaj</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][luggage]" class="form-control" 
                                                   value="<?= e($dt['luggage'] ?? '') ?>" placeholder="Bagaj / Luggage">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Rezervasyon Yap</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][book_now]" class="form-control" 
                                                   value="<?= e($dt['book_now'] ?? '') ?>" placeholder="Rezervasyon Yap / Book Now">
                                        </div>
                                    </div>
                                    
                                    
                                    <hr>
                                    
                                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-calendar-check me-1"></i> Rezervasyon Formu</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Transfer Bilgileri Başlığı</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][transfer_info_title]" class="form-control" 
                                                   value="<?= e($dt['transfer_info_title'] ?? '') ?>" placeholder="Transfer Information">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Araç Tipi</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][vehicle_type]" class="form-control" 
                                                   value="<?= e($dt['vehicle_type'] ?? '') ?>" placeholder="Araç Tipi / Vehicle Type">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Devam Et Butonu</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][continue_booking]" class="form-control" 
                                                   value="<?= e($dt['continue_booking'] ?? '') ?>" placeholder="Rezervasyona Devam Et">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Araç Değiştir</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][change_vehicle]" class="form-control" 
                                                   value="<?= e($dt['change_vehicle'] ?? '') ?>" placeholder="Araç Değiştir">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Toplam Tutar</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][total_price]" class="form-control" 
                                                   value="<?= e($dt['total_price'] ?? '') ?>" placeholder="Toplam Tutar">
                                        </div>
                                    </div>
                                    
                                    <p class="text-muted small mb-2"><i class="bi bi-person me-1"></i> Kişisel Bilgiler</p>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Ad Soyad</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][full_name]" class="form-control" 
                                                   value="<?= e($dt['full_name'] ?? '') ?>" placeholder="Ad Soyad / Full Name">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">E-posta</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][email]" class="form-control" 
                                                   value="<?= e($dt['email'] ?? '') ?>" placeholder="E-posta / Email">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Telefon</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][phone]" class="form-control" 
                                                   value="<?= e($dt['phone'] ?? '') ?>" placeholder="Telefon / Phone">
                                        </div>
                                    </div>
                                    
                                    <p class="text-muted small mb-2"><i class="bi bi-airplane me-1"></i> Uçuş Bilgileri</p>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Uçuş İniş Tarihi</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][flight_date]" class="form-control" 
                                                   value="<?= e($dt['flight_date'] ?? '') ?>" placeholder="Uçuş İniş Tarihi">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Uçuş İniş Saati</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][flight_time]" class="form-control" 
                                                   value="<?= e($dt['flight_time'] ?? '') ?>" placeholder="Uçuş İniş Saati">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Uçuş Numarası</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][flight_number]" class="form-control" 
                                                   value="<?= e($dt['flight_number'] ?? '') ?>" placeholder="Uçuş Numarası">
                                        </div>
                                    </div>
                                    
                                    <p class="text-muted small mb-2"><i class="bi bi-people me-1"></i> Yolcu Bilgileri</p>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Yetişkin Sayısı</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][adults_count]" class="form-control" 
                                                   value="<?= e($dt['adults_count'] ?? '') ?>" placeholder="Yetişkin Sayısı">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Çocuk Sayısı</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][children_count]" class="form-control" 
                                                   value="<?= e($dt['children_count'] ?? '') ?>" placeholder="Çocuk Sayısı">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Çocuk Koltuğu</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][child_seat]" class="form-control" 
                                                   value="<?= e($dt['child_seat'] ?? '') ?>" placeholder="Çocuk Koltuğu">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Hayır / Evet</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][yes_no]" class="form-control" 
                                                   value="<?= e($dt['yes_no'] ?? '') ?>" placeholder="Hayır,Evet (1),Evet (2)">
                                        </div>
                                    </div>
                                    
                                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i> Adres ve Notlar</p>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Varış Otel/Adres</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][hotel_address]" class="form-control" 
                                                   value="<?= e($dt['hotel_address'] ?? '') ?>" placeholder="Varış Otel Adı / Adresi">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Notlar</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][notes]" class="form-control" 
                                                   value="<?= e($dt['notes'] ?? '') ?>" placeholder="Notlar">
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-arrow-repeat me-1"></i> Dönüş Transferi</h6>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Dönüş Transferi İstiyorum (Checkbox Metni)</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][return_transfer]" class="form-control" 
                                                   value="<?= e($dt['return_transfer'] ?? '') ?>" placeholder="Dönüş Transferi İstiyorum">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Dönüş Uçuş Tarihi</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][return_flight_date]" class="form-control" 
                                                   value="<?= e($dt['return_flight_date'] ?? '') ?>" placeholder="Dönüş Uçuş Tarihi">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Dönüş Uçuş Saati</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][return_flight_time]" class="form-control" 
                                                   value="<?= e($dt['return_flight_time'] ?? '') ?>" placeholder="Dönüş Uçuş Saati">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Dönüş Uçuş Numarası</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][return_flight_number]" class="form-control" 
                                                   value="<?= e($dt['return_flight_number'] ?? '') ?>" placeholder="Dönüş Uçuş Numarası">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Dönüş Alınış Saati</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][return_pickup_time]" class="form-control" 
                                                   value="<?= e($dt['return_pickup_time'] ?? '') ?>" placeholder="Dönüş Alınış Saati">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Dönüş İçin Alınacak Otel/Adres</label>
                                            <input type="text" name="detail_translations[<?= $lang['code'] ?>][return_hotel_address]" class="form-control" 
                                                   value="<?= e($dt['return_hotel_address'] ?? '') ?>" placeholder="Dönüş İçin Alınacak Otel / Adres">
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Transfer Detay Çevirilerini Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab 3: Transfer Özellikleri -->
                    <div class="tab-pane fade" id="pane-features-settings" role="tabpanel">
                        <form method="post" action="?action=save_features" id="featuresForm">
                            <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <i class="bi bi-eye me-2"></i>
                                    <strong>Özellikler Bölümü Görünürlüğü</strong>
                                    <p class="text-muted mb-0 small">Bu bölümü sitede göstermek veya gizlemek için kullanın.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="features_visible" id="features_visible" value="1"
                                           <?= ($pageSettings['features_visible'] ?? 1) ? 'checked' : '' ?> style="width: 3em; height: 1.5em;">
                                    <label class="form-check-label" for="features_visible"></label>
                                </div>
                            </div>
                            
                            <!-- Özellikler Bölümü Başlıkları - Dil Desteği -->
                            <div class="card mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-translate me-2"></i>Özellikler Bölümü Başlıkları</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <?php foreach ($languages as $li => $lang): ?>
                                        <li class="nav-item">
                                            <button class="nav-link <?= $li === 0 ? 'active' : '' ?>" type="button" 
                                                    data-bs-toggle="tab" data-bs-target="#features-title-lang-<?= $lang['code'] ?>">
                                                <?= strtoupper($lang['code']) ?> <?= $lang['name'] ?>
                                            </button>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="tab-content border border-top-0 rounded-bottom p-3">
                                        <?php foreach ($languages as $li => $lang): 
                                            $dt = $detailTranslations[$lang['code']] ?? [];
                                        ?>
                                        <div class="tab-pane fade <?= $li === 0 ? 'show active' : '' ?>" id="features-title-lang-<?= $lang['code'] ?>">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Transfer Özellikleri Başlık</label>
                                                    <input type="text" name="detail_translations[<?= $lang['code'] ?>][transfer_features]" class="form-control" 
                                                           value="<?= e($dt['transfer_features'] ?? '') ?>" placeholder="Transfer Özellikleri">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Özellikler Alt Başlık</label>
                                                    <input type="text" name="detail_translations[<?= $lang['code'] ?>][what_we_offer]" class="form-control" 
                                                           value="<?= e($dt['what_we_offer'] ?? '') ?>" placeholder="Size sunduğumuz hizmetler">
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-card-checklist me-2"></i>Özellik Kartları</h6>
                            <p class="text-muted mb-4">
                                <i class="bi bi-info-circle me-1"></i>
                                Transfer detay sayfasında gösterilecek özellik kartlarını buradan yönetebilirsiniz.
                            </p>
                            
                            <div id="features-container">
                                <?php 
                                $featureIndex = 0;
                                foreach ($transferFeatures as $feature): 
                                ?>
                                <div class="feature-item card mb-3" data-index="<?= $featureIndex ?>">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <span class="fw-bold"><i class="bi bi-grip-vertical me-2 text-muted"></i>Özellik #<?= $featureIndex + 1 ?></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Icon</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi <?= e($feature['icon']) ?>"></i></span>
                                                    <input type="text" name="features[<?= $featureIndex ?>][icon]" class="form-control icon-input" 
                                                           value="<?= e($feature['icon']) ?>" placeholder="bi-check-circle">
                                                    <button type="button" class="btn btn-outline-secondary icon-picker-btn" data-index="<?= $featureIndex ?>">
                                                        <i class="bi bi-grid-3x3-gap"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted">Bootstrap Icons kullanın (örn: bi-shield-check)</small>
                                            </div>
                                            <div class="col-md-8">
                                                <ul class="nav nav-tabs nav-tabs-sm" role="tablist">
                                                    <?php foreach ($languages as $li => $lang): ?>
                                                    <li class="nav-item">
                                                        <button class="nav-link <?= $li === 0 ? 'active' : '' ?> py-1 px-2" type="button" 
                                                                data-bs-toggle="tab" data-bs-target="#feature-<?= $featureIndex ?>-lang-<?= $lang['code'] ?>">
                                                            <?= strtoupper($lang['code']) ?>
                                                        </button>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <div class="tab-content border border-top-0 rounded-bottom p-2">
                                                    <?php foreach ($languages as $li => $lang): 
                                                        $ft = $feature['translations'][$lang['code']] ?? [];
                                                    ?>
                                                    <div class="tab-pane fade <?= $li === 0 ? 'show active' : '' ?>" id="feature-<?= $featureIndex ?>-lang-<?= $lang['code'] ?>">
                                                        <input type="text" name="feature_translations[<?= $featureIndex ?>][<?= $lang['code'] ?>][title]" 
                                                               class="form-control form-control-sm mb-2" value="<?= e($ft['title'] ?? '') ?>" placeholder="Başlık (<?= strtoupper($lang['code']) ?>)">
                                                        <textarea name="feature_translations[<?= $featureIndex ?>][<?= $lang['code'] ?>][description]" 
                                                                  class="form-control form-control-sm" rows="2" placeholder="Açıklama (<?= strtoupper($lang['code']) ?>)"><?= e($ft['description'] ?? '') ?></textarea>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                $featureIndex++;
                                endforeach; 
                                ?>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary mb-4" id="add-feature-btn">
                                <i class="bi bi-plus-lg me-1"></i> Yeni Özellik Ekle
                            </button>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Özellikleri Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab 4: Sözleşme -->
                    <div class="tab-pane fade" id="pane-terms-settings" role="tabpanel">
                        <form method="post" action="?action=save_terms" id="termsForm">
                            <p class="text-muted mb-4">
                                <i class="bi bi-info-circle me-1"></i>
                                Rezervasyon formunda gösterilecek sözleşme/kullanım koşulları metnini buradan yönetebilirsiniz.
                            </p>
                            
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach ($languages as $li => $lang): ?>
                                <li class="nav-item">
                                    <button class="nav-link <?= $li === 0 ? 'active' : '' ?>" type="button" 
                                            data-bs-toggle="tab" data-bs-target="#terms-lang-<?= $lang['code'] ?>">
                                        <?= strtoupper($lang['code']) ?> <?= $lang['name'] ?>
                                    </button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="tab-content border border-top-0 rounded-bottom p-3">
                                <?php foreach ($languages as $li => $lang): 
                                    $termsTrans = $termsTranslations[$lang['code']] ?? [];
                                ?>
                                <div class="tab-pane fade <?= $li === 0 ? 'show active' : '' ?>" id="terms-lang-<?= $lang['code'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Sözleşme Başlığı</label>
                                        <input type="text" name="terms_translations[<?= $lang['code'] ?>][title]" class="form-control" 
                                               value="<?= e($termsTrans['title'] ?? '') ?>" placeholder="Örn: Kullanım Koşulları">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Checkbox Metni</label>
                                        <input type="text" name="terms_translations[<?= $lang['code'] ?>][checkbox_text]" class="form-control" 
                                               value="<?= e($termsTrans['checkbox_text'] ?? '') ?>" placeholder="Örn: Sözleşme şartlarını okudum ve kabul ediyorum">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sözleşme İçeriği</label>
                                        <textarea name="terms_translations[<?= $lang['code'] ?>][content]" class="form-control" rows="10" 
                                                  placeholder="Sözleşme metnini buraya yazın..."><?= e($termsTrans['content'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Sözleşme Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab 5: Uyarı -->
                    <div class="tab-pane fade" id="pane-alert-settings" role="tabpanel">
                        <form method="post" action="?action=save_alert" id="alertForm">
                            <p class="text-muted mb-4">
                                <i class="bi bi-info-circle me-1"></i>
                                Rezervasyon formunda notlar alanının altında gösterilecek uyarı mesajını buradan yönetebilirsiniz.
                            </p>
                            
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Icon</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi <?= e($alertSettings['icon']) ?>" id="alert-icon-preview"></i></span>
                                        <input type="text" name="alert_icon" id="alert-icon-input" class="form-control" 
                                               value="<?= e($alertSettings['icon']) ?>" placeholder="bi-exclamation-triangle">
                                        <button type="button" class="btn btn-outline-secondary" onclick="openIconPicker('alert-icon-input', 'alert-icon-preview')">
                                            <i class="bi bi-grid"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Bootstrap Icons kullanın</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Renk</label>
                                    <select name="alert_color" class="form-select">
                                        <option value="warning" <?= ($alertSettings['color'] ?? '') === 'warning' ? 'selected' : '' ?>>⚠️ Sarı (Warning)</option>
                                        <option value="danger" <?= ($alertSettings['color'] ?? '') === 'danger' ? 'selected' : '' ?>>🔴 Kırmızı (Danger)</option>
                                        <option value="info" <?= ($alertSettings['color'] ?? '') === 'info' ? 'selected' : '' ?>>🔵 Mavi (Info)</option>
                                        <option value="success" <?= ($alertSettings['color'] ?? '') === 'success' ? 'selected' : '' ?>>🟢 Yeşil (Success)</option>
                                        <option value="primary" <?= ($alertSettings['color'] ?? '') === 'primary' ? 'selected' : '' ?>>🔷 Primary</option>
                                        <option value="secondary" <?= ($alertSettings['color'] ?? '') === 'secondary' ? 'selected' : '' ?>>⚫ Gri (Secondary)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Durum</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="alert_active" id="alert_active" value="1"
                                               <?= ($alertSettings['is_active'] ?? 1) ? 'checked' : '' ?> style="width: 3em; height: 1.5em;">
                                        <label class="form-check-label" for="alert_active">Aktif</label>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="fw-bold mb-3"><i class="bi bi-translate me-2"></i>Dil Bazlı Mesajlar</h6>
                            
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach ($languages as $li => $lang): ?>
                                <li class="nav-item">
                                    <button class="nav-link <?= $li === 0 ? 'active' : '' ?>" type="button" 
                                            data-bs-toggle="tab" data-bs-target="#alert-lang-<?= $lang['code'] ?>">
                                        <?= strtoupper($lang['code']) ?> <?= $lang['name'] ?>
                                    </button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="tab-content border border-top-0 rounded-bottom p-3">
                                <?php foreach ($languages as $li => $lang): 
                                    $alertTrans = $alertTranslations[$lang['code']] ?? [];
                                ?>
                                <div class="tab-pane fade <?= $li === 0 ? 'show active' : '' ?>" id="alert-lang-<?= $lang['code'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Uyarı Mesajı</label>
                                        <textarea name="alert_translations[<?= $lang['code'] ?>][message]" class="form-control" rows="3" 
                                                  placeholder="Örn: Rezervasyon sonrası 24 saat içinde onay e-postası gönderilecektir."><?= e($alertTrans['message'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i> Uyarı Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Icon Picker Modal -->
<div class="modal fade" id="iconPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Icon Seçin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="iconSearchInput" placeholder="Icon ara...">
                <div class="icon-grid" style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 10px; max-height: 400px; overflow-y: auto;">
                    <?php
                    $popularIcons = [
                        // Uyarı ve Bilgi İconları
                        'bi-exclamation-triangle', 'bi-exclamation-triangle-fill', 'bi-exclamation-circle', 'bi-exclamation-circle-fill',
                        'bi-exclamation-diamond', 'bi-exclamation-diamond-fill', 'bi-exclamation-octagon', 'bi-exclamation-octagon-fill',
                        'bi-info-circle', 'bi-info-circle-fill', 'bi-info-square', 'bi-info-square-fill',
                        'bi-question-circle', 'bi-question-circle-fill', 'bi-question-diamond', 'bi-question-square',
                        'bi-bell', 'bi-bell-fill', 'bi-bell-slash', 'bi-megaphone', 'bi-megaphone-fill',
                        'bi-broadcast', 'bi-broadcast-pin', 'bi-lightbulb', 'bi-lightbulb-fill',
                        // Onay ve İptal
                        'bi-check', 'bi-check2', 'bi-check-circle', 'bi-check-circle-fill', 'bi-check-square', 'bi-check-lg',
                        'bi-x', 'bi-x-circle', 'bi-x-circle-fill', 'bi-x-square', 'bi-x-lg',
                        'bi-slash-circle', 'bi-ban', 'bi-dash-circle', 'bi-plus-circle',
                        // Güvenlik ve Koruma
                        'bi-shield', 'bi-shield-check', 'bi-shield-fill-check', 'bi-shield-exclamation', 'bi-shield-lock',
                        'bi-lock', 'bi-lock-fill', 'bi-unlock', 'bi-key', 'bi-key-fill',
                        // Transfer ve Ulaşım
                        'bi-car-front', 'bi-car-front-fill', 'bi-truck', 'bi-truck-front', 'bi-bus-front', 'bi-bus-front-fill',
                        'bi-taxi-front', 'bi-taxi-front-fill', 'bi-airplane', 'bi-airplane-fill',
                        'bi-geo-alt', 'bi-geo-alt-fill', 'bi-geo', 'bi-pin-map', 'bi-pin-map-fill',
                        'bi-compass', 'bi-compass-fill', 'bi-signpost', 'bi-signpost-fill', 'bi-map', 'bi-map-fill',
                        'bi-globe', 'bi-globe2', 'bi-pin-angle', 'bi-pin-angle-fill',
                        // Zaman ve Takvim
                        'bi-clock', 'bi-clock-fill', 'bi-clock-history', 'bi-stopwatch', 'bi-stopwatch-fill',
                        'bi-calendar', 'bi-calendar-check', 'bi-calendar-check-fill', 'bi-calendar-event', 'bi-calendar-date',
                        'bi-hourglass', 'bi-hourglass-split', 'bi-alarm', 'bi-alarm-fill',
                        // İletişim
                        'bi-telephone', 'bi-telephone-fill', 'bi-phone', 'bi-phone-fill',
                        'bi-envelope', 'bi-envelope-fill', 'bi-envelope-check', 'bi-envelope-exclamation',
                        'bi-chat', 'bi-chat-fill', 'bi-chat-dots', 'bi-chat-dots-fill', 'bi-headset',
                        'bi-whatsapp', 'bi-messenger', 'bi-telegram',
                        // Kişi ve Kullanıcı
                        'bi-person', 'bi-person-fill', 'bi-person-check', 'bi-person-check-fill',
                        'bi-people', 'bi-people-fill', 'bi-person-badge', 'bi-person-badge-fill',
                        // Para ve Ödeme
                        'bi-credit-card', 'bi-credit-card-fill', 'bi-wallet', 'bi-wallet-fill',
                        'bi-cash', 'bi-cash-stack', 'bi-currency-dollar', 'bi-currency-euro',
                        'bi-receipt', 'bi-receipt-cutoff', 'bi-bag-check', 'bi-bag-check-fill',
                        // Yıldız ve Değerlendirme
                        'bi-star', 'bi-star-fill', 'bi-star-half', 'bi-stars',
                        'bi-heart', 'bi-heart-fill', 'bi-suit-heart', 'bi-suit-heart-fill',
                        'bi-award', 'bi-award-fill', 'bi-trophy', 'bi-trophy-fill',
                        'bi-hand-thumbs-up', 'bi-hand-thumbs-up-fill', 'bi-hand-thumbs-down',
                        // Diğer Faydalı İconlar
                        'bi-lightning', 'bi-lightning-fill', 'bi-speedometer2', 'bi-wifi',
                        'bi-luggage', 'bi-luggage-fill', 'bi-suitcase', 'bi-suitcase-fill',
                        'bi-building', 'bi-house', 'bi-house-fill', 'bi-hospital', 'bi-shop',
                        'bi-sun', 'bi-moon', 'bi-cloud', 'bi-umbrella', 'bi-snow',
                        'bi-cup-hot', 'bi-cup-hot-fill', 'bi-gift', 'bi-gift-fill',
                        'bi-camera', 'bi-camera-fill', 'bi-image', 'bi-image-fill',
                        'bi-clipboard-check', 'bi-file-earmark-check', 'bi-patch-check', 'bi-patch-check-fill',
                        'bi-emoji-smile', 'bi-emoji-smile-fill', 'bi-flag', 'bi-flag-fill'
                    ];
                    foreach ($popularIcons as $icon):
                    ?>
                    <button type="button" class="btn btn-outline-secondary icon-select-btn p-2" data-icon="<?= $icon ?>" title="<?= $icon ?>">
                        <i class="bi <?= $icon ?>" style="font-size: 1.5rem;"></i>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let featureIndex = <?= $featureIndex ?>;
    const languages = <?= json_encode(array_column($languages, 'code')) ?>;
    
    // Yeni özellik ekle
    document.getElementById('add-feature-btn').addEventListener('click', function() {
        const container = document.getElementById('features-container');
        const langTabs = languages.map((lang, i) => `
            <li class="nav-item">
                <button class="nav-link ${i === 0 ? 'active' : ''} py-1 px-2" type="button" 
                        data-bs-toggle="tab" data-bs-target="#feature-${featureIndex}-lang-${lang}">
                    ${lang.toUpperCase()}
                </button>
            </li>
        `).join('');
        
        const langPanes = languages.map((lang, i) => `
            <div class="tab-pane fade ${i === 0 ? 'show active' : ''}" id="feature-${featureIndex}-lang-${lang}">
                <input type="text" name="feature_translations[${featureIndex}][${lang}][title]" 
                       class="form-control form-control-sm mb-2" placeholder="Başlık (${lang.toUpperCase()})">
                <textarea name="feature_translations[${featureIndex}][${lang}][description]" 
                          class="form-control form-control-sm" rows="2" placeholder="Açıklama (${lang.toUpperCase()})"></textarea>
            </div>
        `).join('');
        
        const html = `
            <div class="feature-item card mb-3" data-index="${featureIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold"><i class="bi bi-grip-vertical me-2 text-muted"></i>Özellik #${featureIndex + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-feature-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Icon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                                <input type="text" name="features[${featureIndex}][icon]" class="form-control icon-input" 
                                       value="bi-check-circle" placeholder="bi-check-circle">
                                <button type="button" class="btn btn-outline-secondary icon-picker-btn" data-index="${featureIndex}">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                </button>
                            </div>
                            <small class="text-muted">Bootstrap Icons kullanın</small>
                        </div>
                        <div class="col-md-8">
                            <ul class="nav nav-tabs nav-tabs-sm" role="tablist">${langTabs}</ul>
                            <div class="tab-content border border-top-0 rounded-bottom p-2">${langPanes}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        featureIndex++;
    });
    
    // Özellik sil
    document.getElementById('features-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-feature-btn')) {
            e.target.closest('.feature-item').remove();
        }
    });
    
    // Icon picker
    let currentIconInput = null;
    let currentIconPreview = null;
    
    // Global openIconPicker function for alert tab
    window.openIconPicker = function(inputId, previewId) {
        currentIconInput = document.getElementById(inputId);
        currentIconPreview = document.getElementById(previewId);
        new bootstrap.Modal(document.getElementById('iconPickerModal')).show();
    };
    
    document.getElementById('features-container').addEventListener('click', function(e) {
        if (e.target.closest('.icon-picker-btn')) {
            currentIconInput = e.target.closest('.input-group').querySelector('.icon-input');
            currentIconPreview = e.target.closest('.input-group').querySelector('.input-group-text i');
            new bootstrap.Modal(document.getElementById('iconPickerModal')).show();
        }
    });
    
    document.querySelectorAll('.icon-select-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.dataset.icon;
            if (currentIconInput) {
                currentIconInput.value = icon;
            }
            if (currentIconPreview) {
                currentIconPreview.className = 'bi ' + icon;
            } else if (currentIconInput) {
                const preview = currentIconInput.closest('.input-group').querySelector('.input-group-text i');
                if (preview) preview.className = 'bi ' + icon;
            }
            bootstrap.Modal.getInstance(document.getElementById('iconPickerModal')).hide();
        });
    });
    
    // Icon input değiştiğinde önizlemeyi güncelle
    document.getElementById('features-container').addEventListener('input', function(e) {
        if (e.target.classList.contains('icon-input')) {
            const icon = e.target.value;
            e.target.closest('.input-group').querySelector('.input-group-text i').className = 'bi ' + icon;
        }
    });
    
    // Icon arama
    document.getElementById('iconSearchInput').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('.icon-select-btn').forEach(btn => {
            const icon = btn.dataset.icon.toLowerCase();
            btn.style.display = icon.includes(search) ? '' : 'none';
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
