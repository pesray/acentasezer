<?php
/**
 * Hero Section Template
 */
$section = $GLOBALS['current_section'];
$settings = $GLOBALS['section_settings'];
?>

<section id="travel-hero" class="travel-hero section dark-background">

  <div class="hero-background">
    <?php 
    $bgVideo = $section['background_video'] ?? '';
    $bgImage = $section['background_image'] ?? '';
    $videoUrl = getMediaUrl($bgVideo);
    $imageUrl = getMediaUrl($bgImage);
    ?>
    <?php if (!empty($videoUrl)): ?>
    <video autoplay="" muted="" loop="">
      <source src="<?= e($videoUrl) ?>" type="video/mp4">
    </video>
    <?php elseif (!empty($imageUrl)): ?>
    <img src="<?= e($imageUrl) ?>" alt="<?= e($section['title']) ?>">
    <?php else: ?>
    <video autoplay="" muted="" loop="">
      <source src="<?= ASSETS_URL ?>img/travel/video-2.mp4" type="video/mp4">
    </video>
    <?php endif; ?>
    <div class="hero-overlay"></div>
  </div>

  <div class="container position-relative">
    <div class="row align-items-center">
      <div class="<?= !empty($settings['show_booking_form']) ? 'col-lg-7' : 'col-lg-12 text-center' ?>">
        <div class="hero-text" data-aos="fade-up" data-aos-delay="100">
          <h1 class="hero-title"><?= e($section['title']) ?></h1>
          <p class="hero-subtitle"><?= e($section['subtitle']) ?></p>
          <div class="hero-buttons">
            <a href="<?= e($settings['button1_url'] ?? '#') ?>" class="btn btn-primary me-3"><?= e($settings['button1_text'] ?? 'Start Exploring') ?></a>
            <a href="<?= e($settings['button2_url'] ?? '#') ?>" class="btn btn-outline"><?= e($settings['button2_text'] ?? 'Browse Tours') ?></a>
          </div>
        </div>
      </div>

      <?php
      $showForm = !empty($settings['show_booking_form']);
      if ($showForm):
        // Destinasyon slug prefix'i al (dile göre)
        $heroLang = getCurrentLang();
        $heroDestPrefix = 'transfers';
        try {
            $heroDb = getDB();
            $heroPrefixStmt = $heroDb->prepare("
                SELECT pst.slug FROM page_settings ps
                LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
                WHERE ps.page_key = 'destinations'
            ");
            $heroPrefixStmt->execute([$heroLang]);
            $heroPrefixRow = $heroPrefixStmt->fetch();
            if (!empty($heroPrefixRow['slug'])) $heroDestPrefix = $heroPrefixRow['slug'];
        } catch (Exception $e) {}
      ?>
      <div class="col-lg-5">
        <div class="booking-form-wrapper" data-aos="fade-left" data-aos-delay="200">
          <div class="booking-form">
            <h3 class="form-title"><?= e($settings['form_title'] ?? 'Plan Your Adventure') ?></h3>
            <form id="heroTransferForm">
              <div class="form-group mb-3">
                <label for="hero-destination"><?= e($settings['label_destination'] ?? 'Destination') ?></label>
                <select name="destination" id="hero-destination" class="form-select" required="">
                  <option value=""><?= e($settings['placeholder_destination'] ?? 'Choose your destination') ?></option>
                  <?php foreach (getFeaturedDestinations(20) as $dest): ?>
                  <option value="<?= e($dest['slug']) ?>"><?= e($dest['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group mb-3">
                <label for="hero-date"><?= e($settings['label_departure'] ?? 'Transfer Date') ?></label>
                <input type="date" name="flight_date" id="hero-date" class="form-control" required="" min="<?= date('Y-m-d') ?>">
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="hero-adults"><?= e($settings['label_adults'] ?? 'Adults') ?></label>
                    <select name="adults" id="hero-adults" class="form-select" required="">
                      <?php for ($i = 1; $i <= 10; $i++): ?>
                      <option value="<?= $i ?>"><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="hero-children"><?= e($settings['label_children'] ?? 'Children') ?></label>
                    <select name="children" id="hero-children" class="form-select">
                      <?php for ($i = 0; $i <= 5; $i++): ?>
                      <option value="<?= $i ?>"><?= $i ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-car-front me-2"></i><?= e($settings['form_button_text'] ?? 'Find Your Transfer') ?>
              </button>
            </form>
            <script>
            document.getElementById('heroTransferForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var slug = document.getElementById('hero-destination').value;
                if (!slug) return;
                var url = '<?= langUrl($heroDestPrefix) ?>/' + slug;
                window.location.href = url;
            });
            </script>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

</section>
