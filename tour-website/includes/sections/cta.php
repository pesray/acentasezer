<?php
/**
 * Call To Action Section Template
 */
$section = $GLOBALS['current_section'];
$settings = $GLOBALS['section_settings'];
$ctaImage = $settings['image'] ?? 'img/travel/showcase-3.webp';
?>

<section id="call-to-action" class="call-to-action section light-background">

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="hero-content" data-aos="zoom-in" data-aos-delay="200">
      <div class="content-wrapper">
        <div class="badge-wrapper">
          <span class="promo-badge"><?= e($section['subtitle'] ?? 'Limited Time Offer') ?></span>
        </div>
        <h2><?= e($section['title']) ?></h2>
        <?= $section['content'] ?>

        <div class="action-section">
          <div class="main-actions">
            <a href="<?= e($settings['button1_url'] ?? '/destinasyonlar') ?>" class="btn btn-explore">
              <i class="<?= e($settings['button1_icon'] ?? 'bi bi-compass') ?>"></i>
              <?= e($settings['button1_text'] ?? 'Explore Now') ?>
            </a>
            <a href="<?= e($settings['button2_url'] ?? '/turlar') ?>" class="btn btn-deals">
              <i class="<?= e($settings['button2_icon'] ?? 'bi bi-percent') ?>"></i>
              <?= e($settings['button2_text'] ?? 'View Deals') ?>
            </a>
          </div>

          <div class="quick-contact">
            <span class="contact-label"><?= e($settings['contact_label'] ?? 'Need help choosing?') ?></span>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $settings['phone'] ?? '+1555123456')) ?>" class="contact-link">
              <i class="bi bi-telephone"></i>
              <?= e($settings['phone'] ?? '+1 (555) 123-456') ?>
            </a>
          </div>
        </div>
      </div>

      <div class="visual-element">
        <img src="<?= e(getMediaUrl($ctaImage)) ?>" alt="Travel Adventure" class="hero-image" loading="lazy">
        <div class="image-overlay">
          <?php 
          $ctaStats = $settings['stats'] ?? [
              ['number' => '500+', 'label' => 'Destinations'],
              ['number' => '10K+', 'label' => 'Happy Travelers']
          ];
          foreach ($ctaStats as $stat): ?>
          <div class="stat-item">
            <span class="stat-number"><?= e($stat['number']) ?></span>
            <span class="stat-label"><?= e($stat['label']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>

</section>
