<?php
/**
 * Sıkça Sorulan Sorular
 * Kaynak: Admin > SSS kayıtları; yoksa Anasayfa & Tema > SSS bölümü
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$lang = sl_lang();
$items = [];
try {
    $stmt = getDB()->prepare("
        SELECT f.question, f.answer, ft.question AS t_q, ft.answer AS t_a
        FROM faqs f
        LEFT JOIN faq_translations ft ON f.id = ft.faq_id AND ft.language_code = ?
        WHERE f.is_active = 1
        ORDER BY f.sort_order, f.id
    ");
    $stmt->execute([$lang]);
    foreach ($stmt->fetchAll() as $r) {
        $q = trim((string)($r['t_q'] ?? '')) !== '' ? $r['t_q'] : ($lang === sl_default_lang_code() ? $r['question'] : '');
        $a = trim((string)($r['t_a'] ?? '')) !== '' ? $r['t_a'] : ($lang === sl_default_lang_code() ? $r['answer'] : '');
        if (trim((string)$q) !== '' && trim((string)$a) !== '') $items[] = ['q' => $q, 'a' => strip_tags($a)];
    }
} catch (Exception $e) {}

$homeFaq = sl_section('faq');
if (!$items) {
    $items = array_values(array_filter((array)($homeFaq['items'] ?? []), function ($i) { return trim((string)($i['q'] ?? '')) !== '' && trim((string)($i['a'] ?? '')) !== ''; }));
}

$title = sl_t('faq_t');
$url = sl_page_url('faq');
$seoTitle = (trim((string)($homeFaq['title'] ?? '')) !== '' ? $homeFaq['title'] : $title) . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = $items ? mb_substr($items[0]['q'] . ' ' . $items[0]['a'], 0, 158) : sl_t('seo_home_d');
$canonicalUrl = $url;
$bodyClass = 'sl-faq';
$pageSchema = [
    getBreadcrumbSchema([['name' => $title, 'url' => $url]]),
    getFaqSchema($items),
];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => trim((string)($homeFaq['title'] ?? '')) !== '' ? $homeFaq['title'] : $title, 'crumbs' => [[$title, $url]], 'eyebrow' => sl_t('faq_e')]);
?>
<section class="sec" id="sss">
  <div class="container" style="max-width:920px">
    <?php sl_partial('faq', ['items' => $items]); ?>
    <div class="cta-band mt-5">
      <div><h2><?= sl_e('contact_t') ?></h2><p><?= sl_e('contact_s') ?></p></div>
      <div class="acts">
        <?php if ($wa = sl_wa_link(sl_t('wa_hello'))): ?><a class="btn btn-wa" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a><?php endif; ?>
        <a class="btn btn-cta" href="<?= e(sl_page_url('destinations')) ?>"><?= sl_e('see') ?></a>
      </div>
    </div>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
