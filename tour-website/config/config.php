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

// Aktif dil kodlarını cache'li olarak al (performans için)
function getLanguageCodes() {
    static $codes = null;
    if ($codes === null) {
        try {
            $db = getDB();
            $codes = $db->query("SELECT code FROM languages WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $codes = ['tr', 'en', 'de']; // Fallback
        }
    }
    return $codes;
}

// Tarayıcı dilini algıla (Accept-Language header)
function detectBrowserLanguage() {
    // Önce cookie'den kullanıcı tercihini kontrol et
    if (isset($_COOKIE['user_lang'])) {
        $cookieLang = $_COOKIE['user_lang'];
        $activeLangs = getLanguageCodes();
        if (in_array($cookieLang, $activeLangs)) {
            return $cookieLang;
        }
    }
    
    // Accept-Language header'ını al
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (empty($acceptLang)) {
        return DEFAULT_LANG;
    }
    
    // Aktif dilleri al
    $activeLangs = getLanguageCodes();
    
    // Accept-Language'ı parse et (örn: "en-US,en;q=0.9,tr;q=0.8,de;q=0.7")
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
        // Dil kodunun ilk 2 karakterini al (en-US -> en)
        $lang = strtolower(substr($lang, 0, 2));
        $langs[$lang] = $q;
    }
    
    // Q değerine göre sırala (yüksekten düşüğe)
    arsort($langs);
    
    // Aktif dillerden eşleşeni bul
    foreach ($langs as $lang => $q) {
        if (in_array($lang, $activeLangs)) {
            return $lang;
        }
    }
    
    // Eşleşme yoksa varsayılan dil
    return DEFAULT_LANG;
}

// Kullanıcı dil tercihini cookie'ye kaydet
function saveLanguagePreference($lang) {
    // 1 yıl geçerli cookie
    setcookie('user_lang', $lang, [
        'expires' => time() + (365 * 24 * 60 * 60),
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// URL'den dil kodunu çıkar (cache kullanır)
function extractLangFromUrl() {
    static $result = null;
    if ($result !== null) {
        return $result ?: null;
    }
    
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
        $langCodes = getLanguageCodes();
        if (in_array($segments[0], $langCodes)) {
            $result = $segments[0];
            return $result;
        }
    }
    $result = false; // null yerine false kullan (cache için)
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

// URL'den dil ön ekini çıkarılmış path'i al (cache kullanır)
function getPathWithoutLang() {
    static $result = null;
    if ($result !== null) {
        return $result;
    }
    
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
        $langCodes = getLanguageCodes();
        if (in_array($segments[0], $langCodes)) {
            array_shift($segments); // Dil kodunu çıkar
            $result = implode('/', $segments);
            return $result;
        }
    }
    
    $result = $path;
    return $result;
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
    
    // Boş path = ana sayfa
    if (empty($path)) {
        return langUrl('', $targetLang);
    }
    
    try {
        $db = getDB();
        
        // Tüm sayfa slug'larını al (mevcut ve hedef dil için)
        $stmt = $db->prepare("
            SELECT ps.page_key, pst_current.slug as current_slug, pst_target.slug as target_slug
            FROM page_settings ps
            LEFT JOIN page_setting_translations pst_current ON ps.id = pst_current.page_setting_id AND pst_current.language_code = ?
            LEFT JOIN page_setting_translations pst_target ON ps.id = pst_target.page_setting_id AND pst_target.language_code = ?
        ");
        $stmt->execute([$currentLang, $targetLang]);
        
        $pageSlugs = [];
        while ($row = $stmt->fetch()) {
            $pageSlugs[$row['page_key']] = [
                'current' => $row['current_slug'] ?? '',
                'target' => $row['target_slug'] ?? ''
            ];
        }
        
        // 1. Listeleme sayfası kontrolü (transferler, turlar, vb.)
        foreach ($pageSlugs as $pageKey => $slugs) {
            if (!empty($slugs['current']) && $path === $slugs['current']) {
                $newPath = $slugs['target'] ?: $slugs['current'];
                return langUrl($newPath, $targetLang);
            }
        }
        
        // 2. Detay sayfası kontrolü: /liste-slug/detay-slug formatı
        // Transfer detay sayfası
        $destSlugCurrent = $pageSlugs['destinations']['current'] ?? '';
        $destSlugTarget = $pageSlugs['destinations']['target'] ?? '';
        if ($destSlugCurrent && preg_match('#^' . preg_quote($destSlugCurrent, '#') . '/([a-z0-9-]+)$#i', $path, $matches)) {
            $detailSlug = $matches[1];
            
            // Destinasyon çevirisini bul
            $stmt = $db->prepare("
                SELECT d.id, 
                       COALESCE(dt_target.slug, d.slug) as target_slug
                FROM destinations d
                LEFT JOIN destination_translations dt_current ON d.id = dt_current.destination_id AND dt_current.language_code = ?
                LEFT JOIN destination_translations dt_target ON d.id = dt_target.destination_id AND dt_target.language_code = ?
                WHERE COALESCE(dt_current.slug, d.slug) = ? OR d.slug = ?
            ");
            $stmt->execute([$currentLang, $targetLang, $detailSlug, $detailSlug]);
            $dest = $stmt->fetch();
            
            if ($dest) {
                $prefix = $destSlugTarget ?: $destSlugCurrent;
                return langUrl($prefix . '/' . $dest['target_slug'], $targetLang);
            }
        }
        
        // Tur detay sayfası
        $tourSlugCurrent = $pageSlugs['tours']['current'] ?? '';
        $tourSlugTarget = $pageSlugs['tours']['target'] ?? '';
        if ($tourSlugCurrent && preg_match('#^' . preg_quote($tourSlugCurrent, '#') . '/([a-z0-9-]+)$#i', $path, $matches)) {
            $detailSlug = $matches[1];
            
            // Tur çevirisini bul
            $stmt = $db->prepare("
                SELECT t.id, 
                       COALESCE(tt_target.slug, t.slug) as target_slug
                FROM tours t
                LEFT JOIN tour_translations tt_current ON t.id = tt_current.tour_id AND tt_current.language_code = ?
                LEFT JOIN tour_translations tt_target ON t.id = tt_target.tour_id AND tt_target.language_code = ?
                WHERE COALESCE(tt_current.slug, t.slug) = ? OR t.slug = ?
            ");
            $stmt->execute([$currentLang, $targetLang, $detailSlug, $detailSlug]);
            $tour = $stmt->fetch();
            
            if ($tour) {
                $prefix = $tourSlugTarget ?: $tourSlugCurrent;
                return langUrl($prefix . '/' . $tour['target_slug'], $targetLang);
            }
        }
        
        // Blog detay sayfası
        $blogSlugCurrent = $pageSlugs['blog']['current'] ?? 'blog';
        $blogSlugTarget = $pageSlugs['blog']['target'] ?? 'blog';
        if ($blogSlugCurrent && preg_match('#^' . preg_quote($blogSlugCurrent, '#') . '/([a-z0-9-]+)$#i', $path, $matches)) {
            $detailSlug = $matches[1];
            // Blog için çeviri varsa kullan, yoksa aynı slug
            $prefix = $blogSlugTarget ?: $blogSlugCurrent;
            return langUrl($prefix . '/' . $detailSlug, $targetLang);
        }
        
    } catch (Exception $e) {}
    
    // Varsayılan: aynı path ile dil değiştir
    return langUrl($path, $targetLang);
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
    static $cache = [];
    $lang = getCurrentLang();
    $cacheKey = $menuSlug . '_' . $lang . '_' . ($parentId ?? 'root');
    
    // Cache'den döndür
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    
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
        
        $cache[$cacheKey] = $items;
        return $items;
    } catch (Exception $e) {
        return [];
    }
}
