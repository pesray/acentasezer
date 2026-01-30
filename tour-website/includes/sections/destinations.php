<?php
/**
 * Featured Destinations Section Template
 */
$section = $GLOBALS['current_section'];
$settings = $GLOBALS['section_settings'];
$limit = $settings['limit'] ?? 4;
$destinations = getFeaturedDestinations($limit);

?>

<section id="featured-destinations" class="featured-destinations section">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2><?= e($section['title']) ?></h2>
    <div><span>Check Our</span> <span class="description-title"><?= e($section['title']) ?></span></div>
  </div><!-- End Section Title -->

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="row">

      <?php if (isset($destinations[0])): ?>
      <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="200">
        <div class="featured-destination">
          <div class="destination-overlay">
            <img src="<?= $destinations[0]['featured_image'] ? UPLOADS_URL . e($destinations[0]['featured_image']) : ASSETS_URL . 'img/travel/destination-3.webp' ?>" alt="<?= e($destinations[0]['title']) ?>" class="img-fluid">
            <div class="destination-info">
              <?php if ($destinations[0]['badge']): ?>
              <span class="destination-tag"><?= e($destinations[0]['badge']) ?></span>
              <?php endif; ?>
              <h3><?= e($destinations[0]['title']) ?></h3>
              <p class="location"><i class="bi bi-geo-alt-fill"></i> <?= e($destinations[0]['location']) ?></p>
              <p class="description"><?= e($destinations[0]['description']) ?></p>
              <div class="destination-meta">
                <div class="tours-count">
                  <i class="bi bi-collection"></i>
                  <span><?= (int)$destinations[0]['tour_count'] ?> <?= __('packages', 'general') ?></span>
                </div>
                <div class="rating">
                  <i class="bi bi-star-fill"></i>
                  <span><?= number_format($destinations[0]['rating'], 1) ?> (<?= (int)$destinations[0]['review_count'] ?>)</span>
                </div>
              </div>
              <div class="price-info">
                <span class="starting-from"><?= __('starting_from', 'general') ?></span>
                <span class="amount">$<?= number_format($destinations[0]['starting_price'], 0) ?></span>
              </div>
              <a href="<?= SITE_URL ?>/destinasyon/<?= e($destinations[0]['slug']) ?>" class="explore-btn">
                <span><?= __('explore_now', 'general') ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="col-lg-6">
        <div class="row g-3">
          <?php foreach (array_slice($destinations, 1) as $index => $dest): ?>
          <div class="col-12" data-aos="fade-left" data-aos-delay="<?= 300 + ($index * 100) ?>">
            <div class="compact-destination">
              <div class="destination-image">
                <img src="<?= $dest['featured_image'] ? UPLOADS_URL . e($dest['featured_image']) : ASSETS_URL . 'img/travel/destination-7.webp' ?>" alt="<?= e($dest['title']) ?>" class="img-fluid">
                <?php if ($dest['badge']): ?>
                <div class="badge-offer"><?= e($dest['badge']) ?></div>
                <?php endif; ?>
              </div>
              <div class="destination-details">
                <h4><?= e($dest['title']) ?></h4>
                <p class="location"><i class="bi bi-geo-alt"></i> <?= e($dest['location']) ?></p>
                <p class="brief"><?= e($dest['description']) ?></p>
                <div class="stats-row">
                  <span class="tour-count"><i class="bi bi-calendar-check"></i> <?= (int)$dest['tour_count'] ?> <?= __('tours', 'general') ?></span>
                  <span class="rating"><i class="bi bi-star-fill"></i> <?= number_format($dest['rating'], 1) ?></span>
                  <span class="price"><?= __('from', 'general') ?> $<?= number_format($dest['starting_price'], 0) ?></span>
                </div>
                <a href="<?= SITE_URL ?>/destinasyon/<?= e($dest['slug']) ?>" class="quick-link"><?= __('view_details', 'general') ?> <i class="bi bi-chevron-right"></i></a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>

</section>
