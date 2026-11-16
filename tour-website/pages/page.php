<?php
/**
 * Genel sayfa (Admin > Sayfalar) — KVKK, iptal koşulları, hakkımızda vb.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$slug = $_GET['slug'] ?? '';
$page = getPageBySlug($slug);

if (!$page || !empty($page['is_homepage'])) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Başka dilde temel (varsayılan dil) adresle gelindiyse kopya içerik olmasın:
// o dilin çevirisi varsa çeviri adresine, yoksa varsayılan dildeki adrese kalıcı yönlendir.
$pgLang = getCurrentLang();
$pgDef = sl_default_lang_code();
if ($pgLang !== $pgDef) {
    try {
        $st = getDB()->prepare("SELECT slug FROM page_translations WHERE page_id = ? AND language_code = ? AND slug <> '' AND title <> '' LIMIT 1");
        $st->execute([(int)$page['id'], $pgLang]);
        $pgSlug = $st->fetchColumn();
        if ($pgSlug === false) {
            header('Location: ' . langUrl($page['slug'], $pgDef), true, 301);
            exit;
        }
        if ($pgSlug !== $slug) {
            header('Location: ' . langUrl($pgSlug, $pgLang), true, 301);
            exit;
        }
    } catch (Exception $e) {}
}

$title = $page['title'];
$url = langUrl($page['slug'] ?? $slug);
$seoTitle = (!empty($page['meta_title']) ? $page['meta_title'] : $title) . (empty($page['meta_title']) ? ' · ' . getSetting('site_name', 'Sunline VIP Transfer') : '');
$metaDescription = !empty($page['meta_description']) ? $page['meta_description'] : (!empty($page['excerpt']) ? $page['excerpt'] : mb_substr(trim(strip_tags((string)($page['content'] ?? ''))), 0, 160));
$metaKeywords = $page['meta_keywords'] ?? '';
$canonicalUrl = $url;
$bodyClass = 'sl-page-' . preg_replace('/[^a-z0-9-]/', '', (string)($page['template'] ?? 'default'));
$pageSchema = [getBreadcrumbSchema([['name' => $title, 'url' => $url]])];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $title, 'sub' => $page['excerpt'] ?? '', 'crumbs' => [[$title, $url]]]);
?>
<section class="page-body">
  <div class="container" style="max-width:900px">
    <?php if (!empty($page['content'])): ?>
    <article class="prose"><?= $page['content'] ?></article>
    <?php endif; ?>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
