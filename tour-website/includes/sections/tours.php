<?php
/**
 * Featured Tours Section Template
 */
$section = $GLOBALS['current_section'];
$settings = $GLOBALS['section_settings'];
$limit = $settings['limit'] ?? 6;
$tours = getFeaturedTours($limit);

// Dinamik URL prefix al (hardcoded /tur/ ve /turlar yerine)
$lang = getCurrentLang();
$tourPrefix = 'tours';
try {
    $db = getDB();
    $prefixStmt = $db->prepare("
        SELECT pst.slug 
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'tours'
    ");
    $prefixStmt->execute([$lang]);
    $prefixRow = $prefixStmt->fetch();
    if (!empty($prefixRow['slug'])) {
        $tourPrefix = $prefixRow['slug'];
    }
} catch (Exception $e) {}
?>

<section id="featured-tours" class="featured-tours section">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2><?= e($section['title']) ?></h2>
    <div><span>Check Our</span> <span class="description-title"><?= e($section['title']) ?></span></div>
  </div><!-- End Section Title -->

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="row gy-4">
      <?php foreach ($tours as $index => $tour): ?>
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= 200 + ($index * 100) ?>">
        <div class="tour-card">
          <div class="tour-image">
            <img src="<?= $tour['featured_image'] ? UPLOADS_URL . e($tour['featured_image']) : ASSETS_URL . 'img/travel/tour-1.webp' ?>" alt="<?= e($tour['title']) ?>" class="img-fluid" loading="lazy">
            <?php if ($tour['badge']): ?>
            <div class="tour-badge"><?= e($tour['badge']) ?></div>
            <?php endif; ?>
            <div class="tour-price"><?= $tour['currency'] ?><?= number_format($tour['sale_price'] ?? $tour['price'], 0) ?></div>
          </div>
          <div class="tour-content">
            <h4><?= e($tour['title']) ?></h4>
            <div class="tour-meta">
              <span class="duration"><i class="bi bi-clock"></i> <?= (int)$tour['duration_days'] ?> <?= __('days', 'general') ?></span>
              <span class="group-size"><i class="bi bi-people"></i> <?= __('max', 'general') ?> <?= (int)$tour['group_size_max'] ?></span>
            </div>
            <p><?= e(mb_substr($tour['description'], 0, 100)) ?>...</p>
            
            <?php 
            $highlights = json_decode($tour['highlights'], true);
            if (!empty($highlights)): 
            ?>
            <div class="tour-highlights">
              <?php foreach (array_slice($highlights, 0, 3) as $highlight): ?>
              <span><?= e($highlight) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="tour-action">
              <a href="<?= langUrl($tourPrefix . '/' . $tour['slug']) ?>" class="btn-book"><?= __('book_now', 'general') ?></a>
              <div class="tour-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <?php if ($i <= floor($tour['rating'])): ?>
                  <i class="bi bi-star-fill"></i>
                  <?php elseif ($i - 0.5 <= $tour['rating']): ?>
                  <i class="bi bi-star-half"></i>
                  <?php else: ?>
                  <i class="bi bi-star"></i>
                  <?php endif; ?>
                <?php endfor; ?>
                <span><?= number_format($tour['rating'], 1) ?> (<?= (int)$tour['review_count'] ?>)</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="500">
      <a href="<?= langUrl($tourPrefix) ?>" class="btn-view-all"><?= __('view_all_tours', 'general') ?></a>
    </div>

  </div>

</section>
