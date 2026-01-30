<?php
/**
 * Turlar Listesi
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$pageTitle = __('menu_tours', 'header');
$metaDescription = __('tours_meta_desc', 'seo');
$bodyClass = 'tours-page';

$lang = getCurrentLang();
$db = getDB();

// Filtreler
$categorySlug = $_GET['category'] ?? '';
$destinationSlug = $_GET['destination'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

// Turları getir
$sql = "SELECT t.*, COALESCE(tt.title, t.title) as title, COALESCE(tt.slug, t.slug) as slug, 
        COALESCE(tt.description, t.description) as description
        FROM tours t
        LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
        WHERE t.status = 'published'";
$params = [$lang];

if ($categorySlug) {
    $sql .= " AND t.category_id = (SELECT id FROM tour_categories WHERE slug = ?)";
    $params[] = $categorySlug;
}

$sql .= " ORDER BY t.is_featured DESC, t.sort_order";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$tours = $stmt->fetchAll();

// Kategorileri getir
$categories = $db->query("SELECT * FROM tour_categories ORDER BY sort_order")->fetchAll();

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= ASSETS_URL ?>img/page-title-bg.webp);">
    <div class="container position-relative">
        <h1><?= __('menu_tours', 'header') ?></h1>
        <p><?= __('discover_tours', 'general') ?></p>
    </div>
</div>

<section class="tours-listing section">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h5><?= __('filter', 'general') ?></h5>
                    <form method="get">
                        <div class="mb-3">
                            <label class="form-label"><?= __('category', 'general') ?></label>
                            <select name="category" class="form-select">
                                <option value=""><?= __('all', 'general') ?></option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?= __('apply_filter', 'general') ?></button>
                    </form>
                </div>
            </div>
            
            <!-- Tours Grid -->
            <div class="col-lg-9">
                <div class="row gy-4">
                    <?php foreach ($tours as $tour): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="tour-card">
                            <div class="tour-image">
                                <img src="<?= $tour['featured_image'] ? UPLOADS_URL . e($tour['featured_image']) : ASSETS_URL . 'img/travel/tour-1.webp' ?>" alt="<?= e($tour['title']) ?>" class="img-fluid">
                                <?php if ($tour['badge']): ?>
                                <div class="tour-badge"><?= e($tour['badge']) ?></div>
                                <?php endif; ?>
                                <div class="tour-price"><?= $tour['currency'] ?><?= number_format($tour['sale_price'] ?? $tour['price'], 0) ?></div>
                            </div>
                            <div class="tour-content">
                                <h4><?= e($tour['title']) ?></h4>
                                <div class="tour-meta">
                                    <span><i class="bi bi-clock"></i> <?= (int)$tour['duration_days'] ?> <?= __('days', 'general') ?></span>
                                    <span><i class="bi bi-people"></i> <?= __('max', 'general') ?> <?= (int)$tour['group_size_max'] ?></span>
                                </div>
                                <p><?= e(mb_substr($tour['description'], 0, 80)) ?>...</p>
                                <div class="tour-action">
                                    <a href="<?= SITE_URL ?>/tur/<?= e($tour['slug']) ?>" class="btn-book"><?= __('view_details', 'general') ?></a>
                                    <div class="tour-rating">
                                        <i class="bi bi-star-fill"></i>
                                        <span><?= number_format($tour['rating'], 1) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($tours)): ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted"><?= __('no_tours_found', 'general') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
