<?php
/**
 * Header Include
 * Tüm sayfalarda kullanılacak ortak header
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

$currentLang = getCurrentLang();
$languages = getActiveLanguages();
$menuItems = getMenuItems('ana-menu');
$siteName = getSetting('site_name', 'Tour');
$siteLogo = getSetting('site_logo', '');
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= e($siteName) ?></title>
  <meta name="description" content="<?= isset($metaDescription) ? e($metaDescription) : e(getSetting('site_description', '')) ?>">
  <meta name="keywords" content="<?= isset($metaKeywords) ? e($metaKeywords) : e(getSetting('site_keywords', '')) ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="<?= isset($pageTitle) ? e($pageTitle) : e($siteName) ?>">
  <meta property="og:description" content="<?= isset($metaDescription) ? e($metaDescription) : e(getSetting('site_description', '')) ?>">
  <meta property="og:image" content="<?= isset($ogImage) ? e($ogImage) : SITE_URL . '/' . e(getSetting('site_logo', '')) ?>">
  <meta property="og:url" content="<?= e(SITE_URL . $_SERVER['REQUEST_URI']) ?>">
  <meta property="og:type" content="website">

  <!-- Hreflang tags for SEO -->
  <?php foreach ($languages as $lang): ?>
  <link rel="alternate" hreflang="<?= e($lang['code']) ?>" href="<?= e(getAlternateLanguageUrl($lang['code'])) ?>">
  <?php endforeach; ?>

  <!-- Favicons -->
  <link href="<?= ASSETS_URL ?>img/favicon.png" rel="icon">
  <link href="<?= ASSETS_URL ?>img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="<?= ASSETS_URL ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= ASSETS_URL ?>vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= ASSETS_URL ?>vendor/aos/aos.css" rel="stylesheet">
  <link href="<?= ASSETS_URL ?>vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="<?= ASSETS_URL ?>vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="<?= ASSETS_URL ?>css/main.css" rel="stylesheet">

  <?php if (isset($extraCss)): ?>
  <?= $extraCss ?>
  <?php endif; ?>
</head>

<body class="<?= isset($bodyClass) ? e($bodyClass) : 'index-page' ?>">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="<?= langUrl('') ?>" class="logo d-flex align-items-center me-auto me-xl-0">
        <?php if ($siteLogo): ?>
        <img src="<?= UPLOADS_URL . e($siteLogo) ?>" alt="<?= e($siteName) ?>">
        <?php else: ?>
        <h1 class="sitename"><?= e($siteName) ?></h1>
        <?php endif; ?>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <?php foreach ($menuItems as $item): ?>
          <?php 
            // URL'yi langUrl ile sar (eğer dış link değilse)
            $itemUrl = $item['url'];
            if (!preg_match('/^https?:\/\//', $itemUrl)) {
                $itemUrl = langUrl(ltrim($itemUrl, '/'));
            }
          ?>
          <?php if (empty($item['children'])): ?>
          <li><a href="<?= e($itemUrl) ?>"><?= e($item['title']) ?></a></li>
          <?php else: ?>
          <li class="dropdown">
            <a href="<?= e($itemUrl) ?>"><span><?= e($item['title']) ?></span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <?php foreach ($item['children'] as $child): ?>
              <?php 
                $childUrl = $child['url'];
                if (!preg_match('/^https?:\/\//', $childUrl)) {
                    $childUrl = langUrl(ltrim($childUrl, '/'));
                }
              ?>
              <li><a href="<?= e($childUrl) ?>"><?= e($child['title']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
          <?php endif; ?>
          <?php endforeach; ?>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <!-- Dil Seçici -->
      <div class="language-switcher dropdown me-3">
        <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <?php 
          $currentLangData = array_filter($languages, fn($l) => $l['code'] === $currentLang);
          $currentLangData = reset($currentLangData);
          echo $currentLangData ? $currentLangData['flag'] . ' ' . strtoupper($currentLangData['code']) : strtoupper($currentLang);
          ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php foreach ($languages as $lang): ?>
          <li>
            <a class="dropdown-item <?= $lang['code'] === $currentLang ? 'active' : '' ?>" 
               href="<?= e(getAlternateLanguageUrl($lang['code'])) ?>">
              <?= e($lang['flag']) ?> <?= e($lang['native_name']) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <a class="btn-getstarted" href="<?= langUrl('transferler') ?>"><?= __('btn_get_started', 'header') ?></a>

    </div>
  </header>

  <main class="main">
