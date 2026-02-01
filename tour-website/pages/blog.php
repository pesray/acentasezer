<?php
/**
 * Blog Listesi
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$pageTitle = __('menu_blog', 'header');
$bodyClass = 'blog-page';

$lang = getCurrentLang();
$db = getDB();

$stmt = $db->prepare("
    SELECT bp.*, COALESCE(bpt.title, bp.title) as title, COALESCE(bpt.slug, bp.slug) as slug,
           COALESCE(bpt.excerpt, bp.excerpt) as excerpt, u.full_name as author_name
    FROM blog_posts bp
    LEFT JOIN blog_post_translations bpt ON bp.id = bpt.post_id AND bpt.language_code = ?
    LEFT JOIN users u ON bp.author_id = u.id
    WHERE bp.status = 'published'
    ORDER BY bp.published_at DESC
");
$stmt->execute([$lang]);
$posts = $stmt->fetchAll();

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= ASSETS_URL ?>img/page-title-bg.webp);">
    <div class="container position-relative">
        <h1><?= __('menu_blog', 'header') ?></h1>
    </div>
</div>

<section class="blog-listing section">
    <div class="container">
        <div class="row gy-4">
            <?php foreach ($posts as $post): ?>
            <div class="col-lg-4 col-md-6">
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="<?= $post['featured_image'] ? UPLOADS_URL . e($post['featured_image']) : ASSETS_URL . 'img/blog/blog-1.webp' ?>" alt="<?= e($post['title']) ?>" class="img-fluid" loading="lazy">
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($post['published_at'])) ?></span>
                            <span><i class="bi bi-person"></i> <?= e($post['author_name']) ?></span>
                        </div>
                        <h4><a href="<?= SITE_URL ?>/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h4>
                        <p><?= e(mb_substr($post['excerpt'], 0, 120)) ?>...</p>
                        <a href="<?= SITE_URL ?>/blog/<?= e($post['slug']) ?>" class="read-more"><?= __('read_more', 'general') ?> <i class="bi bi-arrow-right"></i></a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
