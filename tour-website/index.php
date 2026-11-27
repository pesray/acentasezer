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

// API endpoints - dil ön eki olmadan çalışsın
if (strpos($fullPath, 'api/') === 0) {
    $apiFile = ROOT_PATH . $fullPath . (substr($fullPath, -4) === '.php' ? '' : '.php');
    if (file_exists($apiFile)) {
        require_once $apiFile;
        exit;
    }
}

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

// sitemap.xml - dil ön eki olmadan çalışsın
if ($fullPath === 'sitemap.xml') {
    require_once ROOT_PATH . 'sitemap.php';
    exit;
}

// robots.txt - dil ön eki olmadan çalışsın
if ($fullPath === 'robots.txt') {
    require_once ROOT_PATH . 'robots.php';
    exit;
}

// llms.txt - yapay zekâ asistanları için site özeti (dil ön eki olmadan)
if ($fullPath === 'llms.txt' || $fullPath === 'llms-full.txt') {
    $_GET['full'] = $fullPath === 'llms-full.txt' ? 1 : 0;
    require_once ROOT_PATH . 'llms.php';
    exit;
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

    // Yönetim panelinde adres girilmemiş sayfalar/diller için varsayılanlar
    foreach (defaultPageSlugs() as $key => $byLang) {
        if (empty($slugs[$key]) && !empty($byLang[$lang])) {
            $slugs[$key] = $byLang[$lang];
        }
    }

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

// Ana sayfa (boş path - sadece dil kodu) — Sunline teması
if (empty($path)) {
    require_once INCLUDES_PATH . 'sunline/home.php';
    $GLOBALS['sl_is_home'] = true;

    // Anasayfa SEO bilgileri (Admin > Anasayfa & Tema > SEO), boşsa dile göre varsayılan
    $homepageSeo = null;
    try {
        $hpStmt = $db->prepare("
            SELECT pt.meta_title, pt.meta_description, pt.meta_keywords
            FROM pages p
            JOIN page_translations pt ON p.id = pt.page_id AND pt.language_code = ?
            WHERE p.is_homepage = 1
            LIMIT 1
        ");
        $hpStmt->execute([$lang]);
        $homepageSeo = $hpStmt->fetch();
    } catch (Exception $e) {}

    $seoTitle = !empty($homepageSeo['meta_title']) ? $homepageSeo['meta_title'] : sl_t('seo_home_t');
    $metaDescription = !empty($homepageSeo['meta_description']) ? $homepageSeo['meta_description'] : sl_t('seo_home_d');
    $metaKeywords = !empty($homepageSeo['meta_keywords']) ? $homepageSeo['meta_keywords'] : '';
    $canonicalUrl = langUrl('');
    $bodyClass = 'sl-home';
    $slHeroCfg = sl_section('hero');
    // Mozaik açıksa büyük arka plan görseli kullanılmaz → ön yüklenmez
    $preloadImage = (!sl_hero_mosaic_photos($slHeroCfg) && trim((string)($slHeroCfg['image'] ?? '')) !== '') ? getMediaUrl($slHeroCfg['image']) : '';

    require_once INCLUDES_PATH . 'header.php';
    $slHome = sl_render_home();

    require_once INCLUDES_PATH . 'schema.php';
    $pageSchema = [
        getFaqSchema($slHome['faq']),
        getDestinationListSchema($slHome['cards']),
    ];
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
