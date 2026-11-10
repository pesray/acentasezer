<?php
/**
 * Turlar listesi (veriler Tur Yönetimi > Turlar ekranından gelir)
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
        WHERE ps.page_key = 'tours' LIMIT 1
    ");
    $stmt->execute([$lang]);
    $ps = $stmt->fetch();
} catch (Exception $e) {}

$title = !empty($ps['page_title']) ? $ps['page_title'] : sl_t('tours_t');
$sub = !empty($ps['page_subtitle']) ? $ps['page_subtitle'] : '';
$bg = !empty($ps['background_image']) ? getMediaUrl($ps['background_image']) : '';
$prefix = getPageSlugByKey('tours');
$url = langUrl($prefix);

$tours = [];
try {
    $stmt = $db->prepare("
        SELECT t.*, COALESCE(tt.title, t.title) AS title, COALESCE(tt.slug, t.slug) AS slug,
               COALESCE(tt.description, t.description) AS description
        FROM tours t
        LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
        WHERE t.status = 'published'
        ORDER BY t.is_featured DESC, t.sort_order
    ");
    $stmt->execute([$lang]);
    $tours = $stmt->fetchAll();
} catch (Exception $e) {}

// En düşük araç fiyatı (önce bu dil, yoksa herhangi bir dil)
$minPrices = [];
try {
    foreach ($db->query("SELECT tour_id, language_code, MIN(price) AS p, MAX(currency) AS c FROM tour_vehicles WHERE price > 0 GROUP BY tour_id, language_code")->fetchAll() as $r) {
        $tid = (int)$r['tour_id'];
        if ($r['language_code'] === $lang || !isset($minPrices[$tid])) $minPrices[$tid] = ['p' => (float)$r['p'], 'c' => $r['c']];
    }
} catch (Exception $e) {}

$seoTitle = $title . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = $sub !== '' ? $sub : sl_t('seo_home_d');
$canonicalUrl = $url;
$bodyClass = 'sl-tours';
$pageSchema = [getBreadcrumbSchema([['name' => $title, 'url' => $url]])];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $title, 'sub' => $sub, 'crumbs' => [[$title, $url]], 'img' => $bg, 'eyebrow' => sl_t('tours_e')]);
?>
<section class="sec">
  <div class="container">
    <div class="rc-grid">
      <?php foreach ($tours as $tour):
        $img = !empty($tour['image']) ? getMediaUrl($tour['image']) : sl_default_base()['hero']['image'];
        $tUrl = langUrl($prefix . '/' . $tour['slug']);
        $mp = $minPrices[(int)$tour['id']] ?? ((float)$tour['price'] > 0 ? ['p' => (float)$tour['price'], 'c' => $tour['currency']] : null);
      ?>
      <article class="rc">
        <a class="rc-media" href="<?= e($tUrl) ?>">
          <img src="<?= e($img) ?>" alt="<?= e($tour['title']) ?>" loading="lazy" decoding="async" width="640" height="420">
          <div class="rc-ov"><h2 class="h3 m-0" style="color:#fff;font-size:1.7rem;font-weight:700"><?= e($tour['title']) ?></h2></div>
        </a>
        <div class="rc-body">
          <?php if (!empty($tour['description'])): ?><p style="color:var(--cmuted);font-size:.92rem;line-height:1.6"><?= e(mb_strimwidth(strip_tags($tour['description']), 0, 140, '…')) ?></p><?php endif; ?>
          <div class="tour-meta">
            <?php if (!empty($tour['duration_days'])): ?><span><i class="bi bi-clock"></i><?= (int)$tour['duration_days'] ?> <?= $lang === 'tr' ? 'gün' : ($lang === 'de' ? 'Tag(e)' : ($lang === 'ru' ? 'дн.' : 'day(s)')) ?></span><?php endif; ?>
            <?php if ($mp): ?><span><i class="bi bi-tag"></i><?= e(sl_money($mp['p'], $mp['c'])) ?></span><?php endif; ?>
          </div>
          <a class="btn btn-cta rc-book" href="<?= e($tUrl) ?>"><?= sl_e('details') ?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
