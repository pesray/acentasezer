<?php
/**
 * Transfer bölgeleri listesi — tüm yayındaki bölgeler ve fiyatları
 * Başlık/alt başlık/görsel: Tur Yönetimi > Transferler > Sayfa Ayarları (boşsa varsayılan)
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$lang = sl_lang();
$db = getDB();

$ps = null;
try {
    $stmt = $db->prepare("
        SELECT ps.background_image, pst.title AS page_title, pst.subtitle AS page_subtitle
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'destinations' LIMIT 1
    ");
    $stmt->execute([$lang]);
    $ps = $stmt->fetch();
} catch (Exception $e) {}

$crumbTitle = !empty($ps['page_title']) ? $ps['page_title'] : sl_t('all_transfers');
$subtitle = !empty($ps['page_subtitle']) ? $ps['page_subtitle'] : sl_t('list_s');
$hero = sl_section('hero');
$bg = !empty($ps['background_image']) ? getMediaUrl($ps['background_image']) : (trim((string)($hero['image'] ?? '')) !== '' ? getMediaUrl($hero['image']) : sl_default_base()['hero']['image']);

$dests = sl_bookable_destinations(false);
$reg = sl_section('regions');
$used = [];
foreach ($dests as $d) if ($d['region'] !== '') $used[$d['region']] = true;
$filters = [];
foreach (sl_region_filters() as $f) {
    $k = (string)($f['k'] ?? '');
    if ($k === '' || empty($used[$k])) continue;
    $filters[] = ['k' => $k, 'l' => $f['l'][$lang] ?? ($f['l']['en'] ?? $k)];
}
$allLabel = $reg['all_label'][$lang] ?? ($reg['all_label']['en'] ?? 'All');

$faq = sl_section('faq');
$faqItems = $faq['_active'] ? array_values(array_filter((array)($faq['items'] ?? []), function ($i) { return trim((string)($i['q'] ?? '')) !== '' && trim((string)($i['a'] ?? '')) !== ''; })) : [];

$seoTitle = sl_t('seo_list_t');
$metaDescription = sl_t('seo_list_d');
$canonicalUrl = sl_page_url('destinations');
$ogImage = $bg;
$bodyClass = 'sl-list';
$pageSchema = [
    getBreadcrumbSchema([['name' => $crumbTitle, 'url' => $canonicalUrl]]),
    getDestinationListSchema($dests),
    getFaqSchema($faqItems),
];

require_once INCLUDES_PATH . 'header.php';
?>

<header class="hero hero-sm">
  <div class="hero-bg" style="background-image:url('<?= e($bg) ?>')"></div>
  <div class="container hero-in">
    <ol class="bc">
      <li><a href="<?= e(langUrl('')) ?>"><?= sl_e('home') ?></a></li>
      <li aria-current="page"><?= e($crumbTitle) ?></li>
    </ol>
    <div class="eyebrow"><?= sl_e('list_e') ?></div>
    <h1><?= sl_e('list_t') ?></h1>
    <p><?= e($subtitle) ?></p>
  </div>
</header>
<div class="container">
  <?php sl_partial('quickform'); ?>
</div>

<section class="sec" id="bolgeler">
  <div class="container">
    <div class="eyebrow"><?= e($reg['eyebrow'] ?? '') ?></div>
    <h2 class="sec-t"><?= e($reg['title'] ?? '') ?></h2>
    <?php if (trim((string)($reg['subtitle'] ?? '')) !== ''): ?><p class="sec-s"><?= e($reg['subtitle']) ?></p><?php endif; ?>
    <?php if (count($filters) > 1): ?>
    <div class="flt" role="toolbar" aria-label="<?= e($reg['title'] ?? '') ?>">
      <button type="button" class="on" data-f="all" aria-pressed="true"><?= e($allLabel) ?></button>
      <?php foreach ($filters as $f): ?><button type="button" data-f="<?= e($f['k']) ?>" aria-pressed="false"><?= e($f['l']) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="rc-grid">
      <?php foreach ($dests as $d) sl_card($d); ?>
    </div>
    <div class="chip-links mt-5">
      <?php foreach ($dests as $d): ?><a href="<?= e($d['url']) ?>"><?= e($d['title']) ?></a><?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($faqItems): ?>
<section class="sec pt-0" id="sss">
  <div class="container" style="max-width:920px">
    <div class="eyebrow"><?= e($faq['eyebrow'] ?? sl_t('faq_e')) ?></div>
    <h2 class="sec-t"><?= e($faq['title'] ?? sl_t('faq_t')) ?></h2>
    <?php sl_partial('faq', ['items' => $faqItems]); ?>
  </div>
</section>
<?php endif; ?>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
