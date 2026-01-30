<?php
/**
 * 404 Sayfa Bulunamadı
 */

if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

$pageTitle = '404 - ' . __('page_not_found', 'general');
$bodyClass = 'error-page';

require_once INCLUDES_PATH . 'header.php';
?>

<section class="error-404 section">
    <div class="container text-center py-5">
        <h1 class="display-1 fw-bold text-primary">404</h1>
        <h2><?= __('page_not_found', 'general') ?></h2>
        <p class="lead text-muted mb-4"><?= __('page_not_found_desc', 'general') ?></p>
        <a href="<?= SITE_URL ?>" class="btn btn-primary btn-lg">
            <i class="bi bi-house me-2"></i> <?= __('back_to_home', 'general') ?>
        </a>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
