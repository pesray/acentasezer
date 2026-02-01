<?php
/**
 * Turlar Listesi
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$lang = getCurrentLang();
$db = getDB();

// Sayfa ayarlarını çek
$pageSettings = null;
try {
    $stmt = $db->prepare("
        SELECT ps.*, pst.title as page_title, pst.subtitle as page_subtitle, pst.slug as page_slug
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'tours'
    ");
    $stmt->execute([$lang]);
    $pageSettings = $stmt->fetch();
} catch (Exception $e) {}

// Sayfa başlığı
$pageTitle = !empty($pageSettings['page_title']) ? $pageSettings['page_title'] : __('menu_tours', 'header');
$pageSubtitle = !empty($pageSettings['page_subtitle']) ? $pageSettings['page_subtitle'] : __('discover_tours', 'general');
$pageBgImage = !empty($pageSettings['background_image']) ? getMediaUrl($pageSettings['background_image']) : ASSETS_URL . 'img/page-title-bg.webp';

// Tur detay sayfası için dil bazlı prefix
$tourDetailPrefix = 'tours';
if (!empty($pageSettings['page_slug'])) {
    $tourDetailPrefix = $pageSettings['page_slug'];
}

$bodyClass = 'tours-page';

// Turları getir
$stmt = $db->prepare("
    SELECT t.*, COALESCE(tt.title, t.title) as title, COALESCE(tt.slug, t.slug) as slug, 
           COALESCE(tt.description, t.description) as description
    FROM tours t
    LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
    WHERE t.status = 'published'
    ORDER BY t.is_featured DESC, t.sort_order
");
$stmt->execute([$lang]);
$tours = $stmt->fetchAll();

require_once INCLUDES_PATH . 'header.php';
?>

<!-- Page Title -->
<div class="page-title dark-background" data-aos="fade" style="background-image: url(<?= e($pageBgImage) ?>);">
    <div class="container position-relative">
        <h1><?= e($pageTitle) ?></h1>
        <p><?= e($pageSubtitle) ?></p>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="<?= langUrl('') ?>"><?= __('menu_home', 'header') ?></a></li>
                <li class="current"><?= e($pageTitle) ?></li>
            </ol>
        </nav>
    </div>
</div><!-- End Page Title -->

<!-- Tours Section -->
<section id="tours" class="tours section">
    <div class="container" data-aos="fade-up" data-aos-delay="100">
        
        <!-- Tour Grid -->
        <div class="row" data-aos="fade-up" data-aos-delay="200">
            <div class="col-12">
                <div class="row">
                    <?php foreach ($tours as $tour): 
                        $imageUrl = !empty($tour['image']) ? getMediaUrl($tour['image']) : ASSETS_URL . 'img/travel/tour-1.webp';
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="tour-card">
                            <div class="tour-image">
                                <img src="<?= e($imageUrl) ?>" alt="<?= e($tour['title']) ?>" class="img-fluid" loading="lazy">
                            </div>
                            <div class="tour-content">
                                <h4><?= e($tour['title']) ?></h4>
                                <?php if (!empty($tour['description'])): ?>
                                <p><?= e(mb_substr($tour['description'], 0, 100)) ?><?= mb_strlen($tour['description']) > 100 ? '...' : '' ?></p>
                                <?php endif; ?>
                                <a href="<?= langUrl($tourDetailPrefix . '/' . $tour['slug']) ?>" class="btn btn-outline-primary"><?= __('view_tour', 'general') ?></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($tours)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-compass display-1 text-muted"></i>
                        <h4 class="mt-3"><?= __('no_tours_found', 'general') ?></h4>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section><!-- /Tours Section -->

<style>
/* Tour Card Styles */
.tours .tour-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
}

.tours .tour-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.tours .tour-card .tour-image {
    position: relative;
    overflow: hidden;
    height: 220px;
}

.tours .tour-card .tour-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.tours .tour-card:hover .tour-image img {
    transform: scale(1.1);
}

.tours .tour-card .tour-content {
    padding: 20px;
}

.tours .tour-card .tour-content h4 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--heading-color);
}

.tours .tour-card .tour-content p {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}

.tours .tour-card .tour-content .btn {
    width: 100%;
}
</style>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
