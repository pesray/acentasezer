<?php
/**
 * Galeri Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$pageTitle = __('menu_gallery', 'header');
$bodyClass = 'gallery-page';

$db = getDB();

$categories = $db->query("SELECT * FROM gallery_categories ORDER BY sort_order")->fetchAll();
$gallery = $db->query("SELECT g.*, gc.slug as category_slug FROM gallery g LEFT JOIN gallery_categories gc ON g.category_id = gc.id ORDER BY g.sort_order")->fetchAll();

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= ASSETS_URL ?>img/page-title-bg.webp);">
    <div class="container position-relative">
        <h1><?= __('menu_gallery', 'header') ?></h1>
    </div>
</div>

<section class="gallery section">
    <div class="container">
        <!-- Filter -->
        <div class="gallery-filters text-center mb-4">
            <ul class="list-inline">
                <li class="list-inline-item"><button class="btn btn-outline-primary active" data-filter="*"><?= __('all', 'general') ?></button></li>
                <?php foreach ($categories as $cat): ?>
                <li class="list-inline-item"><button class="btn btn-outline-primary" data-filter=".<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></button></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- Gallery Grid -->
        <div class="row gallery-container gy-4">
            <?php foreach ($gallery as $item): ?>
            <div class="col-lg-3 col-md-4 col-6 gallery-item <?= e($item['category_slug'] ?? '') ?>">
                <a href="<?= UPLOADS_URL . e($item['image']) ?>" class="glightbox" data-gallery="gallery">
                    <img src="<?= UPLOADS_URL . e($item['thumbnail'] ?: $item['image']) ?>" alt="<?= e($item['title']) ?>" class="img-fluid">
                    <?php if ($item['title']): ?>
                    <div class="gallery-overlay">
                        <span><?= e($item['title']) ?></span>
                    </div>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php 
$extraJs = '<script>
$(document).ready(function() {
    $(".gallery-filters button").on("click", function() {
        $(".gallery-filters button").removeClass("active");
        $(this).addClass("active");
        var filter = $(this).data("filter");
        if (filter === "*") {
            $(".gallery-item").show();
        } else {
            $(".gallery-item").hide();
            $(filter).show();
        }
    });
});
</script>';
require_once INCLUDES_PATH . 'footer.php'; 
?>
