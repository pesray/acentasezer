<?php
/**
 * Turlar listesi (veriler Tur Yönetimi > Turlar ekranından gelir)
 * Fiyat / araç yok: transfer kartı görünümünde kart, "Rezervasyon" → WhatsApp.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/tours.php';
require_once INCLUDES_PATH . 'schema.php';

$lang = sl_lang();
$db = getDB();

$ps = null;
try {
    $stmt = $db->prepare("
        SELECT ps.background_image, pst.title AS page_title, pst.subtitle AS page_subtitle
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'tours' LIMIT 1
    ");
    $stmt->execute([$lang]);
    $ps = $stmt->fetch();
} catch (Exception $e) {}

$title = !empty($ps['page_title']) ? $ps['page_title'] : sl_t('tours_t');
$sub = !empty($ps['page_subtitle']) ? $ps['page_subtitle'] : '';
$bg = !empty($ps['background_image']) ? getMediaUrl($ps['background_image']) : '';
$url = sl_page_url('tours');

$tours = sl_tours();

$seoTitle = $title . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = $sub !== '' ? $sub : sl_t('seo_home_d');
$canonicalUrl = $url;
if ($bg !== '') $ogImage = $bg;
$bodyClass = 'sl-tours';
$extraCss = sl_tour_css();
$pageSchema = [getBreadcrumbSchema([['name' => $title, 'url' => $url]])];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $title, 'sub' => $sub, 'crumbs' => [[$title, $url]], 'img' => $bg, 'eyebrow' => sl_t('tours_e')]);
?>
<section class="sec">
  <div class="container">
    <?php if ($tours): ?>
    <div class="rc-grid">
      <?php foreach ($tours as $t) sl_tour_card($t); ?>
    </div>
    <?php else: ?>
    <div class="tour-empty">
      <p class="lead-in"><?= e(sl_tour_t('empty')) ?></p>
      <?php if ($wa = sl_wa_link(sl_tour_t('wa_info'))): ?><a class="btn btn-wa mt-2" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2" aria-hidden="true"></i>WhatsApp</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
