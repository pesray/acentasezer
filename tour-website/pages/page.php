<?php
/**
 * Genel Sayfa Şablonu
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$slug = $_GET['slug'] ?? '';
$page = getPageBySlug($slug);

if (!$page) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $page['meta_title'] ?: $page['title'];
$metaDescription = $page['meta_description'] ?: $page['excerpt'];
$metaKeywords = $page['meta_keywords'] ?? '';
$bodyClass = 'page-' . $page['template'];

require_once INCLUDES_PATH . 'header.php';

// Sayfa section'larını al
$sections = getPageSections($page['id']);
?>

<!-- Page Title -->
<div class="page-title dark-background" style="background-image: url(<?= ASSETS_URL ?>img/page-title-bg.webp);">
    <div class="container position-relative">
        <h1><?= e($page['title']) ?></h1>
        <?php if ($page['excerpt']): ?>
        <p><?= e($page['excerpt']) ?></p>
        <?php endif; ?>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="<?= SITE_URL ?>"><?= __('menu_home', 'header') ?></a></li>
                <li class="current"><?= e($page['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Page Content -->
<section class="page-content section">
    <div class="container">
        <?php if ($page['content']): ?>
        <div class="content-wrapper">
            <?= $page['content'] ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Section'ları render et
foreach ($sections as $section) {
    renderSection($section);
}

require_once INCLUDES_PATH . 'footer.php';
?>
