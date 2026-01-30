<?php
/**
 * Ana Konfigürasyon Dosyası
 */

// Hata raporlama (geliştirme için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı ayarları
require_once __DIR__ . '/database.php';

// Site sabitleri
define('SITE_NAME', 'Tour');
define('SITE_URL', 'http://localhost/transfer');
define('ADMIN_URL', SITE_URL . '/admin');

// Dizin sabitleri
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('UPLOADS_URL', SITE_URL . '/uploads/');

// Assets (template'den)
define('ASSETS_URL', SITE_URL . '/assets/');

// Varsayılan dil
define('DEFAULT_LANG', 'en');

// Base path sabiti (XAMPP için)
define('BASE_PATH', parse_url(SITE_URL, PHP_URL_PATH) ?: '');

// URL'den dil kodunu çıkar
function extractLangFromUrl() {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Base path'i çıkar
    if (BASE_PATH && strpos($path, BASE_PATH) === 0) {
        $path = substr($path, strlen(BASE_PATH));
    }
    $path = trim($path, '/');
    
    // İlk segment dil kodu mu kontrol et
    $segments = explode('/', $path);
    if (!empty($segments[0]) && strlen($segments[0]) === 2) {
        // Aktif diller arasında mı kontrol et
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT code FROM languages WHERE code = ? AND is_active = 1");
            $stmt->execute([$segments[0]]);
            if ($stmt->fetch()) {
                return $segments[0];
            }
        } catch (Exception $e) {}
    }
    return null;
}

// Aktif dili belirle (URL'den veya session'dan)
function getCurrentLang() {
    static $currentLang = null;
    
    if ($currentLang !== null) {
        return $currentLang;
    }
    
    // Önce URL'den dil kodunu al
    $urlLang = extractLangFromUrl();
    if ($urlLang) {
        $_SESSION['lang'] = $urlLang;
        $currentLang = $urlLang;
        return $currentLang;
    }
    
    // Eski ?lang= parametresi desteği (geriye uyumluluk)
    if (isset($_GET['lang'])) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    
    $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
    return $currentLang;
}

// URL'den dil ön ekini çıkarılmış path'i al
function getPathWithoutLang() {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Base path'i çıkar
    if (BASE_PATH && strpos($path, BASE_PATH) === 0) {
        $path = substr($path, strlen(BASE_PATH));
    }
    $path = trim($path, '/');
    
    $segments = explode('/', $path);
    
    // İlk segment dil kodu mu kontrol et
    if (!empty($segments[0]) && strlen($segments[0]) === 2) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT code FROM languages WHERE code = ? AND is_active = 1");
            $stmt->execute([$segments[0]]);
            if ($stmt->fetch()) {
                array_shift($segments); // Dil kodunu çıkar
                return implode('/', $segments);
            }
        } catch (Exception $e) {}
    }
    
    return $path;
}

// Dil bazlı URL oluştur
function langUrl($path = '', $langCode = null) {
    if ($langCode === null) {
        $langCode = getCurrentLang();
    }
    $path = ltrim($path, '/');
    return SITE_URL . '/' . $langCode . ($path ? '/' . $path : '');
}

// Mevcut sayfanın başka bir dildeki URL'sini al
function getAlternateLanguageUrl($targetLang) {
    $currentLang = getCurrentLang();
    $path = getPathWithoutLang();
    
    // Sayfa ayarlarından slug çevirilerini kontrol et
    try {
        $db = getDB();
        
        // Mevcut path için page_key bul
        $stmt = $db->prepare("
            SELECT ps.page_key, pst_current.slug as current_slug, pst_target.slug as target_slug
            FROM page_settings ps
            LEFT JOIN page_setting_translations pst_current ON ps.id = pst_current.page_setting_id AND pst_current.language_code = ?
            LEFT JOIN page_setting_translations pst_target ON ps.id = pst_target.page_setting_id AND pst_target.language_code = ?
        ");
        $stmt->execute([$currentLang, $targetLang]);
        
        while ($row = $stmt->fetch()) {
            $currentSlug = $row['current_slug'] ?? '';
            $targetSlug = $row['target_slug'] ?? '';
            
            // Eğer mevcut path bu sayfanın slug'ı ile eşleşiyorsa
            if ($currentSlug && $path === $currentSlug) {
                // Hedef dildeki slug'ı kullan
                $newPath = $targetSlug ?: $currentSlug;
                return langUrl($newPath, $targetLang);
            }
        }
        
        // Transfer/destinasyon detay sayfası kontrolü
        if (preg_match('#^(transfer|destinasyon|destination)/([a-z0-9-]+)$#', $path, $matches)) {
            $detailSlug = $matches[2];
            
            // Destinasyon çevirisini bul
            $stmt = $db->prepare("
                SELECT d.id, 
                       COALESCE(dt_current.slug, d.slug) as current_slug,
                       COALESCE(dt_target.slug, d.slug) as target_slug
                FROM destinations d
                LEFT JOIN destination_translations dt_current ON d.id = dt_current.destination_id AND dt_current.language_code = ?
                LEFT JOIN destination_translations dt_target ON d.id = dt_target.destination_id AND dt_target.language_code = ?
                WHERE COALESCE(dt_current.slug, d.slug) = ?
            ");
            $stmt->execute([$currentLang, $targetLang, $detailSlug]);
            $dest = $stmt->fetch();
            
            if ($dest) {
                // Hedef dildeki prefix'i al (transfer/destination)
                $prefix = ($targetLang === 'tr') ? 'transfer' : 'transfer';
                return langUrl($prefix . '/' . $dest['target_slug'], $targetLang);
            }
        }
        
    } catch (Exception $e) {}
    
    // Varsayılan: aynı path ile dil değiştir
    return langUrl($path, $targetLang);
}

// Tarayıcı dilini algıla
function detectBrowserLanguage() {
    // Accept-Language header'ını al
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (empty($acceptLang)) {
        return DEFAULT_LANG;
    }
    
    // Aktif dilleri al
    try {
        $db = getDB();
        $stmt = $db->query("SELECT code FROM languages WHERE is_active = 1");
        $activeLangs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return DEFAULT_LANG;
    }
    
    if (empty($activeLangs)) {
        return DEFAULT_LANG;
    }
    
    // Accept-Language header'ını parse et
    // Örnek: "en-US,en;q=0.9,tr;q=0.8,de;q=0.7"
    $langs = [];
    foreach (explode(',', $acceptLang) as $part) {
        $part = trim($part);
        if (strpos($part, ';') !== false) {
            list($lang, $q) = explode(';', $part);
            $q = (float) str_replace('q=', '', $q);
        } else {
            $lang = $part;
            $q = 1.0;
        }
        // Sadece dil kodunu al (en-US -> en)
        $lang = strtolower(substr($lang, 0, 2));
        $langs[$lang] = $q;
    }
    
    // Öncelik sırasına göre sırala
    arsort($langs);
    
    // Aktif dillerle eşleştir
    foreach ($langs as $lang => $q) {
        if (in_array($lang, $activeLangs)) {
            return $lang;
        }
    }
    
    return DEFAULT_LANG;
}

// Çeviri fonksiyonu
function __($key, $group = 'general') {
    static $translations = null;
    $lang = getCurrentLang();
    
    if ($translations === null) {
        $translations = [];
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT translation_key, translation_value, group_name FROM translations WHERE language_code = ?");
            $stmt->execute([$lang]);
            while ($row = $stmt->fetch()) {
                $translations[$row['group_name']][$row['translation_key']] = $row['translation_value'];
            }
        } catch (Exception $e) {
            return $key;
        }
    }
    
    return $translations[$group][$key] ?? $key;
}

// Slug oluşturma fonksiyonu
function createSlug($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(
        ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'],
        ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'],
        $text
    );
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// Güvenli çıktı
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Medya URL'sini oluştur - uploads veya assets klasöründen gelen path'leri otomatik algıla
function getMediaUrl($path) {
    if (empty($path)) return '';
    if (strpos($path, 'http') === 0) return $path; // Zaten tam URL
    if (strpos($path, 'img/') === 0 || strpos($path, 'video/') === 0) return ASSETS_URL . $path; // Assets
    return UPLOADS_URL . $path; // Uploads
}

// Aktif dilleri getir
function getActiveLanguages() {
    static $languages = null;
    if ($languages === null) {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order");
            $languages = $stmt->fetchAll();
        } catch (Exception $e) {
            $languages = [];
        }
    }
    return $languages;
}

// Varsayılan dili getir
function getDefaultLanguage() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM languages WHERE is_default = 1 LIMIT 1");
        return $stmt->fetch();
    } catch (Exception $e) {
        return ['code' => 'tr', 'name' => 'Turkish'];
    }
}

// Site ayarını getir
function getSetting($key, $default = '') {
    static $settings = null;
    if ($settings === null) {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

// Menü öğelerini getir
function getMenuItems($menuSlug, $parentId = null) {
    $lang = getCurrentLang();
    try {
        $db = getDB();
        $sql = "SELECT mi.*, COALESCE(mit.title, mi.title) as title, COALESCE(mit.url, mi.url) as url
                FROM menu_items mi
                JOIN menus m ON mi.menu_id = m.id
                LEFT JOIN menu_item_translations mit ON mi.id = mit.menu_item_id AND mit.language_code = ?
                WHERE m.slug = ? AND mi.is_active = 1";
        
        if ($parentId === null) {
            $sql .= " AND mi.parent_id IS NULL";
            $params = [$lang, $menuSlug];
        } else {
            $sql .= " AND mi.parent_id = ?";
            $params = [$lang, $menuSlug, $parentId];
        }
        
        $sql .= " ORDER BY mi.sort_order";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        // Alt menüleri ekle
        foreach ($items as &$item) {
            $item['children'] = getMenuItems($menuSlug, $item['id']);
        }
        
        return $items;
    } catch (Exception $e) {
        return [];
    }
}
