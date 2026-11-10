<?php
/**
 * 404 — Sayfa bulunamadı
 */

if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config/config.php';
}
require_once INCLUDES_PATH . 'sunline/core.php';

if (!headers_sent()) http_response_code(404);
$pageTitle = '404 · ' . sl_t('nf_t');
$seoTitle = $pageTitle;
$metaDescription = sl_t('nf_s');
$metaRobots = 'noindex, follow';
$bodyClass = 'sl-404';

require_once INCLUDES_PATH . 'header.php';
?>
<section class="nf">
  <div class="container">
    <b>404</b>
    <h1 class="sec-t mt-3"><?= sl_e('nf_t') ?></h1>
    <p class="sec-s mx-auto mb-4"><?= sl_e('nf_s') ?></p>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
      <a class="btn btn-cta" href="<?= e(langUrl('')) ?>"><i class="bi bi-house me-2"></i><?= sl_e('nf_btn') ?></a>
      <a class="btn btn-ghost" href="<?= e(sl_page_url('destinations')) ?>"><?= sl_e('all_transfers') ?></a>
    </div>
    <div class="chip-links justify-content-center">
      <?php foreach (array_slice(sl_bookable_destinations(true), 0, 10) as $d): ?><a href="<?= e($d['url']) ?>"><?= e($d['title']) ?></a><?php endforeach; ?>
    </div>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
