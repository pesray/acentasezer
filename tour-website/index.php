<?php
/**
 * Ana Router / Ana Sayfa
 * Dil ön eki destekli URL yapısı: /tr/transferler, /en/transfers
 * Tüm slug'lar admin panelden yönetilir (page_settings tablosu)
 */

require_once __DIR__ . '/config/config.php';

// Dil kodunu ve path'i al
$lang = getCurrentLang();
$path = getPathWithoutLang();

// Dil ön eki yoksa varsayılan dile yönlendir
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$fullPath = parse_url($requestUri, PHP_URL_PATH);
// Base path'i çıkar
if (BASE_PATH && strpos($fullPath, BASE_PATH) === 0) {
    $fullPath = substr($fullPath, strlen(BASE_PATH));
}
$fullPath = trim($fullPath, '/');

// Admin panel - dil ön eki olmadan çalışsın
if ($fullPath === 'admin' || strpos($fullPath, 'admin/') === 0) {
    $adminFile = ROOT_PATH . $fullPath . (substr($fullPath, -4) === '.php' ? '' : '.php');
    if ($fullPath === 'admin') {
        $adminFile = ROOT_PATH . 'admin/index.php';
    }
    if (file_exists($adminFile)) {
        require_once $adminFile;
        exit;
    }
}

// Dil ön eki yoksa ve admin değilse
$urlLang = extractLangFromUrl();
if ($urlLang === null && !empty($fullPath)) {
    // Tarayıcı diline göre yönlendir
    $browserLang = detectBrowserLanguage();
    header('Location: ' . langUrl($fullPath, $browserLang));
    exit;
}

// Boş path (sadece site URL'si) - tarayıcı diline göre yönlendir
if (empty($fullPath)) {
    $browserLang = detectBrowserLanguage();
    header('Location: ' . langUrl('', $browserLang));
    exit;
}

// URL'de dil var - kullanıcı tercihini cookie'ye kaydet
if ($urlLang) {
    saveLanguagePreference($urlLang);
}

// Veritabanı bağlantısı
$db = getDB();

// Sayfa slug'larını cache'li olarak al (page_settings tablosundan)
function getPageSlugs($lang) {
    static $cache = [];
    if (isset($cache[$lang])) {
        return $cache[$lang];
    }
    
    $slugs = [];
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT ps.page_key, pst.slug 
            FROM page_settings ps
            LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
            WHERE pst.slug IS NOT NULL AND pst.slug != ''
        ");
        $stmt->execute([$lang]);
        
        while ($row = $stmt->fetch()) {
            $slugs[$row['page_key']] = $row['slug'];
        }
    } catch (Exception $e) {}
    
    $cache[$lang] = $slugs;
    return $slugs;
}

// Sayfa slug'larını al
$pageSlugs = getPageSlugs($lang);

// Listeleme sayfaları için dinamik route eşleştirme
$listingPages = [
    'destinations' => 'pages/destinations.php',
    'tours' => 'pages/tours.php',
    'blog' => 'pages/blog.php',
    'gallery' => 'pages/gallery.php',
    'faq' => 'pages/faq.php',
    'contact' => 'pages/contact.php',
    'booking' => 'pages/booking.php',
    'search' => 'pages/search.php',
];

// Mevcut path'in hangi sayfaya ait olduğunu bul
foreach ($listingPages as $pageKey => $pageFile) {
    $pageSlug = $pageSlugs[$pageKey] ?? null;
    if ($pageSlug && $path === $pageSlug) {
        require_once ROOT_PATH . $pageFile;
        exit;
    }
}

// Detay sayfaları: /liste-slug/detay-slug formatı
// Transfer detay: /transferler/side-transfer
$destinationsSlug = $pageSlugs['destinations'] ?? null;
if ($destinationsSlug && preg_match('#^' . preg_quote($destinationsSlug, '#') . '/([a-z0-9-]+)$#i', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require_once ROOT_PATH . 'pages/destination-detail.php';
    exit;
}

// Tur detay: /turlar/kapadokya-turu
$toursSlug = $pageSlugs['tours'] ?? null;
if ($toursSlug && preg_match('#^' . preg_quote($toursSlug, '#') . '/([a-z0-9-]+)$#i', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require_once ROOT_PATH . 'pages/tour-detail.php';
    exit;
}

// Blog detay: /blog/makale-slug
$blogSlug = $pageSlugs['blog'] ?? 'blog';
if (preg_match('#^' . preg_quote($blogSlug, '#') . '/([a-z0-9-]+)$#i', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require_once ROOT_PATH . 'pages/blog-detail.php';
    exit;
}

// Ana sayfa (boş path - sadece dil kodu)
if (empty($path)) {
    require_once INCLUDES_PATH . 'sections.php';
    
    $pageTitle = getSetting('site_name', 'Tour');
    $metaDescription = getSetting('site_description', '');
    $bodyClass = 'index-page';
    
    $sections = getPageSections(1);
    
    require_once INCLUDES_PATH . 'header.php';
    
    foreach ($sections as $section) {
        renderSection($section);
    }
    
    require_once INCLUDES_PATH . 'footer.php';
    exit;
}

// Genel sayfa (slug)
$_GET['slug'] = $path;
if (file_exists(ROOT_PATH . 'pages/page.php')) {
    require_once ROOT_PATH . 'pages/page.php';
    exit;
}

// 404
http_response_code(404);
if (file_exists(ROOT_PATH . 'pages/404.php')) {
    require_once ROOT_PATH . 'pages/404.php';
} else {
    echo '404 - Sayfa Bulunamadı';
}
