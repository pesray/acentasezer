<?php
/**
 * Sunline teması — ortak header (tüm ön yüz sayfaları)
 *
 * Sayfaların header'dan önce tanımlayabileceği değişkenler:
 *  $seoTitle (tam başlık) | $pageTitle (kısa başlık, sonuna site adı eklenir)
 *  $metaDescription, $metaKeywords, $metaRobots, $canonicalUrl, $alternateUrls (dil => url)
 *  $ogImage, $ogType, $bodyClass, $preloadImage, $extraCss, $pageSchema (footer'da basılır)
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}
require_once INCLUDES_PATH . 'sunline/core.php';

$currentLang = getCurrentLang();
$languages = getActiveLanguages();
// Dil düğmeleri sırası: TR, EN, DE, RU, sonra diğerleri
$slLangOrder = ['tr' => 1, 'en' => 2, 'de' => 3, 'ru' => 4];
$slLangs = $languages;
usort($slLangs, function ($a, $b) use ($slLangOrder) {
    $x = ($slLangOrder[$a['code']] ?? 9) <=> ($slLangOrder[$b['code']] ?? 9);
    return $x !== 0 ? $x : ((int)$a['sort_order'] <=> (int)$b['sort_order']);
});
$siteName = getSetting('site_name', 'Sunline VIP Transfer');
$slGlobal = sl_section('global');
$slTheme = sl_theme_config();
$slLogo = sl_logo_urls();

// Başlık & açıklama
if (!empty($seoTitle)) {
    $slTitle = $seoTitle;
} elseif (!empty($pageTitle)) {
    $slTitle = $pageTitle . ' · ' . $siteName;
} else {
    $slTitle = $siteName;
}
$slDesc = isset($metaDescription) && trim((string)$metaDescription) !== '' ? $metaDescription : getSetting('site_description', '');
$slDesc = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$slDesc)));
if (mb_strlen($slDesc) > 300) $slDesc = mb_substr($slDesc, 0, 297) . '…';

// Kanonik & hreflang
$slCanonical = !empty($canonicalUrl) ? $canonicalUrl : langUrl(getPathWithoutLang());
$slAlts = [];
if (!empty($alternateUrls) && is_array($alternateUrls)) {
    $slAlts = $alternateUrls;
} else {
    foreach ($languages as $slHL) {
        $slAlts[$slHL['code']] = getAlternateLanguageUrl($slHL['code']);
    }
}
$slIsHome = sl_is_home();
$slXDefault = $slIsHome ? SITE_URL . '/' : ($slAlts['en'] ?? ($slAlts[sl_default_lang_code()] ?? $slCanonical));
$slRobots = !empty($metaRobots) ? $metaRobots : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

// Paylaşım görseli
if (empty($ogImage)) {
    $defOg = getSetting('default_og_image', '');
    $ogImage = $defOg !== '' ? UPLOADS_URL . $defOg : $slLogo['png'];
}
$ogImage = sl_abs_url($ogImage);
$slOgLoc = ['tr' => 'tr_TR', 'en' => 'en_US', 'de' => 'de_DE', 'ru' => 'ru_RU', 'ar' => 'ar_AR'];

// Navigasyon
$slNav = is_array($slGlobal['nav'] ?? null) ? $slGlobal['nav'] : [];
$slReqUrl = strtok(SITE_URL . (BASE_PATH && strpos($_SERVER['REQUEST_URI'] ?? '', BASE_PATH) === 0 ? substr($_SERVER['REQUEST_URI'], strlen(BASE_PATH)) : ($_SERVER['REQUEST_URI'] ?? '')), '?');

$slGtm = trim((string)getSetting('google_tag_manager', ''));
$slGa = trim((string)getSetting('google_analytics', ''));
$slFont = 'https://fonts.googleapis.com/css2?family=Zilla+Slab:wght@600;700&family=Hanken+Grotesk:wght@400;500;600;700;800';
if ($currentLang === 'ru') $slFont .= '&family=Roboto+Slab:wght@600;700&family=Manrope:wght@400;500;600;700;800';
$slFont .= '&display=swap';
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($slTitle) ?></title>
<meta name="description" content="<?= e($slDesc) ?>">
<?php if (!empty($metaKeywords)): ?>
<meta name="keywords" content="<?= e($metaKeywords) ?>">
<?php endif; ?>
<meta name="robots" content="<?= e($slRobots) ?>">
<link rel="canonical" href="<?= e($slCanonical) ?>">
<?php foreach ($slAlts as $slCode => $slHref): if (!$slHref) continue; ?>
<link rel="alternate" hreflang="<?= e($slCode) ?>" href="<?= e($slHref) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= e($slXDefault) ?>">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
<meta property="og:title" content="<?= e($slTitle) ?>">
<meta property="og:description" content="<?= e($slDesc) ?>">
<meta property="og:url" content="<?= e($slCanonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:locale" content="<?= e($slOgLoc[$currentLang] ?? $currentLang) ?>">
<?php foreach ($languages as $slHL): if ($slHL['code'] === $currentLang) continue; ?>
<meta property="og:locale:alternate" content="<?= e($slOgLoc[$slHL['code']] ?? $slHL['code']) ?>">
<?php endforeach; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($slTitle) ?>">
<meta name="twitter:description" content="<?= e($slDesc) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php if ($slTw = trim((string)getSetting('twitter_username', ''))): ?>
<meta name="twitter:site" content="@<?= e(ltrim($slTw, '@')) ?>">
<?php endif; ?>
<?php if ($slFb = trim((string)getSetting('facebook_app_id', ''))): ?>
<meta property="fb:app_id" content="<?= e($slFb) ?>">
<?php endif; ?>
<?php if ($slV = trim((string)getSetting('google_site_verification', ''))): ?>
<meta name="google-site-verification" content="<?= e($slV) ?>">
<?php endif; ?>
<?php if ($slV = trim((string)getSetting('yandex_verification', ''))): ?>
<meta name="yandex-verification" content="<?= e($slV) ?>">
<?php endif; ?>
<?php if ($slV = trim((string)getSetting('bing_verification', ''))): ?>
<meta name="msvalidate.01" content="<?= e($slV) ?>">
<?php endif; ?>
<?= sl_theme_meta_tags() ?>
<?= sl_theme_boot_script() ?>
<link rel="icon" href="<?= ASSETS_URL ?>img/favicon.png">
<link rel="apple-touch-icon" href="<?= ASSETS_URL ?>img/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link href="<?= e($slFont) ?>" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= ASSETS_URL ?>css/sunline.css?v=<?= SL_ASSET_VER ?>" rel="stylesheet">
<?= sl_theme_style_tag() ?>
<?php if (!empty($preloadImage)): ?>
<link rel="preload" as="image" href="<?= e($preloadImage) ?>" fetchpriority="high">
<?php endif; ?>
<?php if ($slGtm !== ''): ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= e($slGtm) ?>');</script>
<?php elseif ($slGa !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($slGa) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($slGa) ?>');</script>
<?php endif; ?>
<?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body class="<?= e($bodyClass ?? 'sl-page') ?>">
<?php if ($slGtm !== ''): ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= e($slGtm) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
<a class="skip" href="#main"><?= $currentLang === 'tr' ? 'İçeriğe geç' : ($currentLang === 'de' ? 'Zum Inhalt' : ($currentLang === 'ru' ? 'К содержимому' : 'Skip to content')) ?></a>

<?php $slPromo = is_array($slGlobal['promo'] ?? null) ? array_filter($slGlobal['promo'], function ($slP) { return trim((string)($slP['t'] ?? '')) !== '' || trim((string)($slP['b'] ?? '')) !== ''; }) : []; ?>
<?php if ($slPromo): ?>
<div class="promo" id="promo" role="region" aria-label="<?= e($siteName) ?>">
  <div class="promo-track" id="promoTrack">
    <?php for ($slRep = 0; $slRep < 2; $slRep++): foreach ($slPromo as $slP): ?>
    <span<?= $slRep ? ' aria-hidden="true"' : '' ?>><i class="bi bi-check-circle-fill"></i><?php if (trim((string)($slP['b'] ?? '')) !== ''): ?><b><?= e($slP['b']) ?></b><?php endif; ?><?= e($slP['t'] ?? '') ?></span>
    <?php endforeach; endfor; ?>
  </div>
  <button class="promo-x" type="button" aria-label="<?= sl_e('close') ?>">×</button>
</div>
<script>try{if(sessionStorage.getItem('sl-promo')){document.getElementById('promo').classList.add('is-hidden');}}catch(e){}</script>
<?php endif; ?>

<nav class="navbar navbar-expand-lg hdr" aria-label="<?= sl_e('menu') ?>">
  <div class="container">
    <a class="navbar-brand logo" href="<?= e(langUrl('')) ?>" aria-label="<?= e($siteName) ?>">
      <img class="lg-l" src="<?= e($slLogo['light']) ?>" alt="<?= e($siteName) ?> Antalya" width="129" height="52">
      <img class="lg-d" src="<?= e($slLogo['dark']) ?>" alt="<?= e($siteName) ?> Antalya" width="129" height="52">
    </a>
    <?php $slShowToggle = $slTheme['mode'] === 'auto' && $slTheme['toggle']; ?>
    <?php if ($slShowToggle): ?>
    <button type="button" class="thm d-lg-none ms-auto me-2" aria-label="<?= $currentLang === 'tr' ? 'Açık / koyu tema' : ($currentLang === 'de' ? 'Hell / dunkel' : ($currentLang === 'ru' ? 'Светлая / тёмная тема' : 'Light / dark theme')) ?>"><i class="bi bi-moon-stars i-moon"></i><i class="bi bi-sun i-sun"></i></button>
    <?php endif; ?>
    <button class="navbar-toggler<?= $slShowToggle ? '' : ' ms-auto' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#nv" aria-controls="nv" aria-expanded="false" aria-label="<?= sl_e('menu') ?>"><i class="bi bi-list" style="font-size:1.5rem"></i></button>
    <div class="collapse navbar-collapse" id="nv">
      <ul class="navbar-nav mx-auto">
        <?php foreach ($slNav as $slItem): if (trim((string)($slItem['l'] ?? '')) === '') continue; $slHref = sl_link($slItem['u'] ?? ''); if ($slHref === '') continue; ?>
        <li class="nav-item"><a class="nav-link<?= ($slHref === $slReqUrl) ? ' active' : '' ?>" href="<?= e($slHref) ?>"<?= ($slHref === $slReqUrl) ? ' aria-current="page"' : '' ?>><?= e($slItem['l']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex flex-wrap gap-2 align-items-center py-2 py-lg-0">
        <?php if (count($languages) > 1): ?>
        <div class="lang" role="navigation" aria-label="Language">
          <?php foreach ($slLangs as $slHL): $slCode = $slHL['code']; ?>
          <a class="<?= $slCode === $currentLang ? 'on' : '' ?>" href="<?= e($slAlts[$slCode] ?? langUrl('', $slCode)) ?>" hreflang="<?= e($slCode) ?>" lang="<?= e($slCode) ?>"<?= $slCode === $currentLang ? ' aria-current="true"' : '' ?> title="<?= e($slHL['native_name']) ?>"><?= e(strtoupper($slCode)) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($slShowToggle): ?>
        <button type="button" class="thm d-none d-lg-inline-flex" aria-label="<?= $currentLang === 'tr' ? 'Açık / koyu tema' : ($currentLang === 'de' ? 'Hell / dunkel' : ($currentLang === 'ru' ? 'Светлая / тёмная тема' : 'Light / dark theme')) ?>"><i class="bi bi-moon-stars i-moon"></i><i class="bi bi-sun i-sun"></i></button>
        <?php endif; ?>
        <?php if (trim((string)($slGlobal['cta_label'] ?? '')) !== ''): ?>
        <a href="<?= e(sl_link($slGlobal['cta_url'] ?? '#bolgeler')) ?>" class="btn btn-cta btn-sm"><?= e($slGlobal['cta_label']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<main id="main">
