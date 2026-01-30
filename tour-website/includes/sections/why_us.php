<?php
/**
 * Why Us Section Template
 */
$section = $GLOBALS['current_section'];
$settings = $GLOBALS['section_settings'];
?>

<section id="why-us" class="why-us section">

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <!-- About Us Content -->
    <div class="row align-items-center">
      <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
        <div class="content">
          <h3><?= e($section['title']) ?></h3>
          <?= $section['content'] ?>
          <div class="stats-row">
            <?php 
            $stats = $settings['stats'] ?? [
                ['number' => 1200, 'label' => 'Happy Travelers'],
                ['number' => 85, 'label' => 'Countries Covered'],
                ['number' => 15, 'label' => 'Years Experience']
            ];
            foreach ($stats as $stat): ?>
            <div class="stat-item">
              <span data-purecounter-start="0" data-purecounter-end="<?= (int)$stat['number'] ?>" data-purecounter-duration="2" class="purecounter">0</span>
              <div class="stat-label"><?= e($stat['label']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
        <div class="about-image">
          <?php $whyUsImage = $settings['image'] ?? 'img/travel/showcase-8.webp'; ?>
          <img src="<?= e(getMediaUrl($whyUsImage)) ?>" alt="Travel Experience" class="img-fluid rounded-4">
          <div class="experience-badge">
            <div class="experience-number"><?= e($settings['experience_badge'] ?? '15+') ?></div>
            <div class="experience-text"><?= e($settings['experience_text'] ?? 'Years of Excellence') ?></div>
          </div>
        </div>
      </div>
    </div><!-- End About Us Content -->

  </div>

</section>
