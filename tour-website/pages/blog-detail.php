<?php
/**
 * Blog Detay Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$slug = $_GET['slug'] ?? '';
$lang = getCurrentLang();
$db = getDB();

$stmt = $db->prepare("
    SELECT bp.*, COALESCE(bpt.title, bp.title) as title, COALESCE(bpt.slug, bp.slug) as slug,
           COALESCE(bpt.content, bp.content) as content, COALESCE(bpt.excerpt, bp.excerpt) as excerpt,
           COALESCE(bpt.meta_title, bp.meta_title) as meta_title,
           COALESCE(bpt.meta_description, bp.meta_description) as meta_description,
           u.full_name as author_name
    FROM blog_posts bp
    LEFT JOIN blog_post_translations bpt ON bp.id = bpt.post_id AND bpt.language_code = ?
    LEFT JOIN users u ON bp.author_id = u.id
    WHERE (bpt.slug = ? OR bp.slug = ?) AND bp.status = 'published'
");
$stmt->execute([$lang, $slug, $slug]);
$post = $stmt->fetch();

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

// View count artır
$db->prepare("UPDATE blog_posts SET view_count = view_count + 1 WHERE id = ?")->execute([$post['id']]);

$pageTitle = $post['meta_title'] ?: $post['title'];
$metaDescription = $post['meta_description'] ?: $post['excerpt'];
$bodyClass = 'blog-details-page';

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= $post['featured_image'] ? UPLOADS_URL . e($post['featured_image']) : ASSETS_URL . 'img/page-title-bg.webp' ?>);">
    <div class="container position-relative">
        <h1><?= e($post['title']) ?></h1>
        <div class="post-meta">
            <span><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($post['published_at'])) ?></span>
            <span><i class="bi bi-person"></i> <?= e($post['author_name']) ?></span>
            <span><i class="bi bi-eye"></i> <?= number_format($post['view_count']) ?></span>
        </div>
    </div>
</div>

<section class="blog-details section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <article class="blog-post">
                    <?php if ($post['featured_image']): ?>
                    <img src="<?= UPLOADS_URL . e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>" class="img-fluid mb-4 rounded">
                    <?php endif; ?>
                    
                    <div class="post-content">
                        <?= $post['content'] ?>
                    </div>
                </article>
                
                <div class="post-navigation mt-4 pt-4 border-top">
                    <a href="<?= SITE_URL ?>/blog" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i> <?= __('back_to_blog', 'general') ?>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="sidebar">
                    <div class="sidebar-widget">
                        <h4><?= __('recent_posts', 'general') ?></h4>
                        <?php
                        $recentStmt = $db->prepare("
                            SELECT bp.id, COALESCE(bpt.title, bp.title) as title, COALESCE(bpt.slug, bp.slug) as slug, bp.published_at
                            FROM blog_posts bp
                            LEFT JOIN blog_post_translations bpt ON bp.id = bpt.post_id AND bpt.language_code = ?
                            WHERE bp.status = 'published' AND bp.id != ?
                            ORDER BY bp.published_at DESC LIMIT 5
                        ");
                        $recentStmt->execute([$lang, $post['id']]);
                        $recentPosts = $recentStmt->fetchAll();
                        ?>
                        <ul class="list-unstyled">
                            <?php foreach ($recentPosts as $rp): ?>
                            <li class="mb-2">
                                <a href="<?= SITE_URL ?>/blog/<?= e($rp['slug']) ?>"><?= e($rp['title']) ?></a>
                                <small class="d-block text-muted"><?= date('d M Y', strtotime($rp['published_at'])) ?></small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
