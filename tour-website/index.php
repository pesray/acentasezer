<?php
/**
 * Ana Router / Ana Sayfa
 * Dil ön eki destekli URL yapısı: /tr/transferler, /en/transfers
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

// Dil ön eki yoksa ve admin değilse, varsayılan dile yönlendir
$urlLang = extractLangFromUrl();
if ($urlLang === null && !empty($fullPath)) {
    // Dil ön eki ekleyerek yönlendir
    header('Location: ' . langUrl($fullPath, DEFAULT_LANG));
    exit;
}

// Boş path ve dil ön eki yoksa tarayıcı diline göre yönlendir
if (empty($fullPath)) {
    // Her zaman tarayıcı dilini algıla (ana sayfaya geldiğinde)
    $browserLang = detectBrowserLanguage();
    header('Location: ' . langUrl('', $browserLang));
    exit;
}

// Veritabanı bağlantısı
$db = getDB();

// Tüm dillerdeki sayfa slug'larını al ve route'ları oluştur
$routes = [];

// Statik route'lar (varsayılan)
$defaultRoutes = [
    'turlar' => 'pages/tours.php',
    'tours' => 'pages/tours.php',
    'blog' => 'pages/blog.php',
    'galeri' => 'pages/gallery.php',
    'gallery' => 'pages/gallery.php',
    'sss' => 'pages/faq.php',
    'faq' => 'pages/faq.php',
    'iletisim' => 'pages/contact.php',
    'contact' => 'pages/contact.php',
    'rezervasyon' => 'pages/booking.php',
    'booking' => 'pages/booking.php',
    'arama' => 'pages/search.php',
    'search' => 'pages/search.php',
];
$routes = array_merge($routes, $defaultRoutes);

// Sayfa ayarlarından dinamik slug'ları al (mevcut dil için)
try {
    $stmt = $db->prepare("
        SELECT ps.page_key, pst.slug 
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
    ");
    $stmt->execute([$lang]);
    
    while ($row = $stmt->fetch()) {
        if (!empty($row['slug'])) {
            if ($row['page_key'] === 'destinations') {
                $routes[$row['slug']] = 'pages/destinations.php';
            }
        }
    }
} catch (Exception $e) {}

// Varsayılan transfer slug'larını ekle
$routes['transferler'] = 'pages/destinations.php';
$routes['transfers'] = 'pages/destinations.php';

// Dinamik route'lar (slug içerenler)
if (preg_match('#^tur/([a-z0-9-]+)$#', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require_once ROOT_PATH . 'pages/tour-detail.php';
    exit;
}

// Transfer detay sayfası için dinamik slug'ları al
$transferDetailPrefixes = ['transfer', 'destinasyon', 'destination', 'transferler', 'transfers'];
try {
    $prefixStmt = $db->prepare("
        SELECT pst.slug 
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'destinations' AND pst.slug IS NOT NULL
    ");
    $prefixStmt->execute([$lang]);
    while ($prefixRow = $prefixStmt->fetch()) {
        if (!empty($prefixRow['slug']) && !in_array($prefixRow['slug'], $transferDetailPrefixes)) {
            $transferDetailPrefixes[] = $prefixRow['slug'];
        }
    }
} catch (Exception $e) {}

$transferPrefixPattern = implode('|', array_map('preg_quote', $transferDetailPrefixes));
if (preg_match('#^(' . $transferPrefixPattern . ')/([a-z0-9-]+)$#', $path, $matches)) {
    $_GET['slug'] = $matches[2];
    require_once ROOT_PATH . 'pages/destination-detail.php';
    exit;
}

if (preg_match('#^blog/([a-z0-9-]+)$#', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require_once ROOT_PATH . 'pages/blog-detail.php';
    exit;
}

// Statik route'lar
if (isset($routes[$path])) {
    require_once ROOT_PATH . $routes[$path];
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
