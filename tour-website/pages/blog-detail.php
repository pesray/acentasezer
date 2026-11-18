<?php
/**
 * Blog yazısı detay
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$slug = $_GET['slug'] ?? '';
$lang = sl_lang();
$db = getDB();

$stmt = $db->prepare("
    SELECT bp.*, bpt.title AS t_title, bpt.slug AS t_slug, bpt.content AS t_content, bpt.excerpt AS t_excerpt,
           bpt.meta_title AS t_mt, bpt.meta_description AS t_md, bpt.id AS t_id,
           u.full_name AS author_name
    FROM blog_posts bp
    LEFT JOIN blog_post_translations bpt ON bp.id = bpt.post_id AND bpt.language_code = ?
    LEFT JOIN users u ON bp.author_id = u.id
    WHERE (bpt.slug = ? OR bp.slug = ?) AND bp.status = 'published'
    LIMIT 1
");
$stmt->execute([$lang, $slug, $slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$hasT = !empty($post['t_id']) && trim((string)$post['t_title']) !== '';
$title = $hasT ? $post['t_title'] : $post['title'];
$content = $hasT && trim((string)$post['t_content']) !== '' ? $post['t_content'] : (string)$post['content'];
$excerpt = $hasT && trim((string)$post['t_excerpt']) !== '' ? $post['t_excerpt'] : (string)$post['excerpt'];
$postSlug = $hasT && trim((string)$post['t_slug']) !== '' ? $post['t_slug'] : $post['slug'];
$blogSlug = getPageSlugByKey('blog');
$postUrl = langUrl($blogSlug . '/' . $postSlug);

if ($postSlug !== $slug) {
    header('Location: ' . $postUrl, true, 301);
    exit;
}

try {
    $db->prepare("UPDATE blog_posts SET view_count = view_count + 1 WHERE id = ?")->execute([$post['id']]);
} catch (Exception $e) {}

$blogSec = sl_section('blog');
$blogTitle = trim((string)($blogSec['title'] ?? '')) !== '' ? $blogSec['title'] : sl_t('blog_t');
$img = !empty($post['featured_image']) ? getMediaUrl($post['featured_image']) : '';
$mt = $hasT ? (string)$post['t_mt'] : (string)$post['meta_title'];
$md = $hasT ? (string)$post['t_md'] : (string)$post['meta_description'];

$seoTitle = trim($mt) !== '' ? $mt : $title . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = trim($md) !== '' ? $md : ($excerpt !== '' ? $excerpt : mb_substr(trim(strip_tags($content)), 0, 160));
$canonicalUrl = $postUrl;
$ogType = 'article';
if ($img !== '') $ogImage = $img;
$bodyClass = 'sl-post';
// Yazıdaki geniş tablolar telefonda sayfayı yana taşırmasın: tablo kendi içinde kayar
$extraCss = '<style>@media(max-width:767.98px){.sl-post .prose table{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch}}</style>';
$date = $post['published_at'] ?: $post['created_at'];
// Bu dilde çevirisi olmayan yazı: kopya içerik olmasın diye dizine eklenmez
if (!$hasT && $lang !== sl_default_lang_code()) $metaRobots = 'noindex, follow';

$pageSchema = [
    getBreadcrumbSchema([
        ['name' => $blogTitle, 'url' => sl_page_url('blog')],
        ['name' => $title, 'url' => $postUrl],
    ]),
    [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => mb_substr($title, 0, 110),
        'description' => trim(strip_tags($metaDescription)),
        'image' => $img !== '' ? sl_abs_url($img) : sl_logo_urls()['png'],
        'datePublished' => date('c', strtotime($date)),
        'dateModified' => date('c', strtotime($post['updated_at'] ?: $date)),
        'inLanguage' => $lang,
        'mainEntityOfPage' => $postUrl,
        'author' => ['@type' => 'Organization', 'name' => getSetting('site_name', 'Sunline VIP Transfer')],
        'publisher' => ['@id' => SITE_URL . '/#organization'],
    ],
];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', [
    'h1' => $title,
    'crumbs' => [[$blogTitle, sl_page_url('blog')], [$title, $postUrl]],
    'eyebrow' => date('d.m.Y', strtotime($date)),
    'img' => $img,
    'size' => 'sm',
]);
?>
<section class="page-body">
  <div class="container" style="max-width:860px">
    <?php if ($img !== ''): ?>
    <figure class="post-cover"><img src="<?= e($img) ?>" alt="<?= e($title) ?>" width="1200" height="630" fetchpriority="high"></figure>
    <?php endif; ?>
    <?php if ($excerpt !== ''): ?><p class="lead-in"><?= e($excerpt) ?></p><?php endif; ?>
    <article class="prose"><?= $content ?></article>
    <p class="mt-5"><a href="<?= e(sl_page_url('blog')) ?>"><?= sl_e('blog_back') ?></a></p>
  </div>
</section>
<section class="sec pt-0">
  <div class="container">
    <div class="cta-band">
      <div>
        <h2><?= sl_e('all_transfers') ?></h2>
        <p><?= sl_e('dest_cta_s') ?></p>
      </div>
      <div class="acts"><a class="btn btn-cta" href="<?= e(sl_page_url('destinations')) ?>"><?= sl_e('see') ?></a></div>
    </div>
  </div>
</section>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
