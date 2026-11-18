<?php
/**
 * Tur detay sayfası (veriler Tur Yönetimi > Turlar ekranından gelir)
 * Fiyat / araç yok; rezervasyon WhatsApp'tan (tur adıyla hazır mesaj).
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/tours.php';
require_once INCLUDES_PATH . 'schema.php';

$slug = $_GET['slug'] ?? '';
$lang = sl_lang();
$db = getDB();

$stmt = $db->prepare("
    SELECT t.*, COALESCE(NULLIF(tt.title, ''), t.title) AS title, COALESCE(NULLIF(tt.slug, ''), t.slug) AS slug,
           COALESCE(tt.description, t.description) AS description,
           COALESCE(tt.content, t.content) AS content,
           COALESCE(NULLIF(tt.meta_title, ''), t.meta_title) AS meta_title,
           COALESCE(NULLIF(tt.meta_description, ''), t.meta_description) AS meta_description
    FROM tours t
    LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
    WHERE (t.slug = ? OR tt.slug = ?) AND t.status = 'published'
    LIMIT 1
");
$stmt->execute([$lang, $slug, $slug]);
$tour = $stmt->fetch();

if (!$tour) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$listUrl = sl_page_url('tours');
$tourUrl = langUrl(getPageSlugByKey('tours') . '/' . $tour['slug']);
$listTitle = sl_t('tours_t');
$img = !empty($tour['image']) ? getMediaUrl($tour['image']) : '';
$bookUrl = sl_tour_book_url($tour['title']);
$desc = (string)$tour['description'];
$others = sl_tours((int)$tour['id']);

$seoTitle = (!empty($tour['meta_title']) ? $tour['meta_title'] : $tour['title']) . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = !empty($tour['meta_description']) ? $tour['meta_description'] : $desc;
$canonicalUrl = $tourUrl;
if ($img !== '') $ogImage = $img;
$bodyClass = 'sl-tour has-mbar';
$extraCss = sl_tour_css();
$pageSchema = [
    getBreadcrumbSchema([['name' => $listTitle, 'url' => $listUrl], ['name' => $tour['title'], 'url' => $tourUrl]]),
    getTourSchema($tour, $tourUrl),
];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $tour['title'], 'crumbs' => [[$listTitle, $listUrl], [$tour['title'], $tourUrl]], 'img' => $img, 'size' => 'sm', 'eyebrow' => sl_t('tours_e')]);
?>
<section class="sec" id="hakkinda">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-8">
        <div class="eyebrow"><?= e($tour['title']) ?></div>
        <h2 class="sec-t"><?= e(sl_tour_t('about')) ?></h2>
        <?php foreach (preg_split('/\n[ \t]*\n/', str_replace(["\r\n", "\r"], "\n", e($desc))) as $para): if (trim($para) === '') continue; ?>
        <p class="lead-in"><?= nl2br(trim($para), false) ?></p>
        <?php endforeach; ?>
        <?php if (trim((string)$tour['content']) !== ''): ?>
        <div class="prose mt-4"><?= $tour['content'] ?></div>
        <?php endif; ?>
      </div>
      <div class="col-lg-4">
        <aside class="side-card">
          <h3><?= e(sl_tour_t('book_t')) ?></h3>
          <p class="mb-0" style="color:var(--cmuted)"><?= e(sl_tour_t('book_s')) ?></p>
          <a class="btn btn-cta w-100 mt-4" href="<?= e($bookUrl) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2" aria-hidden="true"></i><?= sl_e('book') ?></a>
        </aside>
      </div>
    </div>
  </div>
</section>

<?php if ($others): ?>
<section class="sec pt-0">
  <div class="container">
    <div class="eyebrow"><?= sl_e('tours_e') ?></div>
    <h2 class="sec-t"><?= e(sl_tour_t('others')) ?></h2>
    <div class="rc-grid">
      <?php foreach (array_slice($others, 0, 6) as $o) sl_tour_card($o); ?>
    </div>
    <div class="rc-more"><a class="btn btn-ghost" href="<?= e($listUrl) ?>"><?= e(sl_tour_t('all')) ?></a></div>
  </div>
</section>
<?php endif; ?>

<div class="mbar">
  <div>
    <small><?= sl_e('tours_e') ?></small>
    <b style="font-size:1.05rem"><?= e(mb_strimwidth($tour['title'], 0, 28, '…', 'UTF-8')) ?></b>
  </div>
  <a class="btn btn-cta" href="<?= e($bookUrl) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2" aria-hidden="true"></i><?= sl_e('book') ?></a>
</div>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
