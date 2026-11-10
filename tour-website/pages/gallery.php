<?php
/**
 * Galeri (Admin > Galeri kayıtları)
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$items = [];
try {
    $items = getDB()->query("SELECT * FROM gallery ORDER BY sort_order, id")->fetchAll();
} catch (Exception $e) {}

$title = sl_t('gallery_t');
$url = sl_page_url('gallery');
$seoTitle = $title . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = sl_t('seo_home_d');
$canonicalUrl = $url;
$bodyClass = 'sl-gallery';
if (!$items) $metaRobots = 'noindex, follow';
$pageSchema = [getBreadcrumbSchema([['name' => $title, 'url' => $url]])];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $title, 'crumbs' => [[$title, $url]]]);
?>
<section class="sec">
  <div class="container">
    <div class="row g-3">
      <?php foreach ($items as $it): if (empty($it['image'])) continue; ?>
      <div class="col-6 col-md-4 col-lg-3">
        <a class="gst d-block" style="aspect-ratio:1/1" href="<?= e(getMediaUrl($it['image'])) ?>" target="_blank" rel="noopener">
          <img src="<?= e(getMediaUrl(!empty($it['thumbnail']) ? $it['thumbnail'] : $it['image'])) ?>" alt="<?= e($it['title'] ?? $title) ?>" loading="lazy" decoding="async" width="400" height="400">
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
