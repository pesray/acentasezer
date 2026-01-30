<?php
/**
 * Tur Detay Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$slug = $_GET['slug'] ?? '';
$tour = getTourBySlug($slug);

if (!$tour) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $tour['meta_title'] ?: $tour['title'];
$metaDescription = $tour['meta_description'] ?: $tour['description'];
$bodyClass = 'tour-details-page';

$highlights = json_decode($tour['highlights'], true) ?: [];
$included = json_decode($tour['included'], true) ?: [];
$excluded = json_decode($tour['excluded'], true) ?: [];
$itinerary = json_decode($tour['itinerary'], true) ?: [];

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= $tour['featured_image'] ? UPLOADS_URL . e($tour['featured_image']) : ASSETS_URL . 'img/page-title-bg.webp' ?>);">
    <div class="container position-relative">
        <h1><?= e($tour['title']) ?></h1>
        <div class="tour-meta-header">
            <span><i class="bi bi-clock"></i> <?= (int)$tour['duration_days'] ?> <?= __('days', 'general') ?></span>
            <span><i class="bi bi-people"></i> <?= __('max', 'general') ?> <?= (int)$tour['group_size_max'] ?> <?= __('person', 'general') ?></span>
            <span><i class="bi bi-star-fill"></i> <?= number_format($tour['rating'], 1) ?> (<?= (int)$tour['review_count'] ?>)</span>
        </div>
    </div>
</div>

<section class="tour-details section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Description -->
                <div class="tour-description mb-4">
                    <h3><?= __('description', 'general') ?></h3>
                    <?php if ($tour['content']): ?>
                    <?= $tour['content'] ?>
                    <?php else: ?>
                    <p><?= nl2br(e($tour['description'])) ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Highlights -->
                <?php if (!empty($highlights)): ?>
                <div class="tour-highlights mb-4">
                    <h3><?= __('highlights', 'general') ?></h3>
                    <ul class="list-unstyled">
                        <?php foreach ($highlights as $h): ?>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> <?= e($h) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Itinerary -->
                <?php if (!empty($itinerary)): ?>
                <div class="tour-itinerary mb-4">
                    <h3><?= __('itinerary', 'general') ?></h3>
                    <div class="accordion" id="itineraryAccordion">
                        <?php foreach ($itinerary as $i => $day): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#day<?= $i ?>">
                                    <strong><?= __('day', 'general') ?> <?= $i + 1 ?>:</strong> <?= e($day['title'] ?? '') ?>
                                </button>
                            </h2>
                            <div id="day<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#itineraryAccordion">
                                <div class="accordion-body"><?= e($day['description'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Included/Excluded -->
                <div class="row mb-4">
                    <?php if (!empty($included)): ?>
                    <div class="col-md-6">
                        <h4><?= __('included', 'general') ?></h4>
                        <ul class="list-unstyled">
                            <?php foreach ($included as $item): ?>
                            <li><i class="bi bi-check text-success me-2"></i> <?= e($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($excluded)): ?>
                    <div class="col-md-6">
                        <h4><?= __('excluded', 'general') ?></h4>
                        <ul class="list-unstyled">
                            <?php foreach ($excluded as $item): ?>
                            <li><i class="bi bi-x text-danger me-2"></i> <?= e($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="booking-card sticky-top" style="top: 100px;">
                    <div class="price-box">
                        <?php if ($tour['sale_price']): ?>
                        <del class="old-price"><?= $tour['currency'] ?><?= number_format($tour['price'], 0) ?></del>
                        <span class="current-price"><?= $tour['currency'] ?><?= number_format($tour['sale_price'], 0) ?></span>
                        <?php else: ?>
                        <span class="current-price"><?= $tour['currency'] ?><?= number_format($tour['price'], 0) ?></span>
                        <?php endif; ?>
                        <span class="per-person">/ <?= __('per_person', 'general') ?></span>
                    </div>
                    
                    <ul class="tour-info-list">
                        <li><i class="bi bi-clock"></i> <strong><?= __('duration', 'general') ?>:</strong> <?= (int)$tour['duration_days'] ?> <?= __('days', 'general') ?></li>
                        <li><i class="bi bi-people"></i> <strong><?= __('group_size', 'general') ?>:</strong> <?= (int)$tour['group_size_min'] ?>-<?= (int)$tour['group_size_max'] ?></li>
                        <li><i class="bi bi-graph-up"></i> <strong><?= __('difficulty', 'general') ?>:</strong> <?= e(ucfirst($tour['difficulty_level'])) ?></li>
                    </ul>
                    
                    <a href="<?= SITE_URL ?>/rezervasyon/<?= e($tour['slug']) ?>" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-calendar-check me-2"></i> <?= __('book_now', 'general') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
