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
      <div class="col-lg-7">
        <div class="hero-text" data-aos="fade-up" data-aos-delay="100">
          <h1 class="hero-title"><?= e($section['title']) ?></h1>
          <p class="hero-subtitle"><?= e($section['subtitle']) ?></p>
          <div class="hero-buttons">
            <a href="<?= e($settings['button1_url'] ?? '#') ?>" class="btn btn-primary me-3"><?= e($settings['button1_text'] ?? 'Start Exploring') ?></a>
            <a href="<?= e($settings['button2_url'] ?? '#') ?>" class="btn btn-outline"><?= e($settings['button2_text'] ?? 'Browse Tours') ?></a>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="booking-form-wrapper" data-aos="fade-left" data-aos-delay="200">
          <div class="booking-form">
            <h3 class="form-title"><?= e($settings['form_title'] ?? 'Plan Your Adventure') ?></h3>
            <form action="<?= SITE_URL ?>/turlar" method="get">
              <div class="form-group mb-3">
                <label for="destination"><?= e($settings['label_destination'] ?? 'Destination') ?></label>
                <select name="destination" id="destination" class="form-select" required="">
                  <option value=""><?= e($settings['placeholder_destination'] ?? 'Choose your destination') ?></option>
                  <?php foreach (getFeaturedDestinations(10) as $dest): ?>
                  <option value="<?= e($dest['slug']) ?>"><?= e($dest['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="checkin"><?= e($settings['label_departure'] ?? 'Departure Date') ?></label>
                    <input type="date" name="checkin" id="checkin" class="form-control" required="">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="checkout"><?= e($settings['label_return'] ?? 'Return Date') ?></label>
                    <input type="date" name="checkout" id="checkout" class="form-control" required="">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="adults"><?= e($settings['label_adults'] ?? 'Adults') ?></label>
                    <select name="adults" id="adults" class="form-select" required="">
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4+</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-3">
                    <label for="children"><?= e($settings['label_children'] ?? 'Children') ?></label>
                    <select name="children" id="children" class="form-select">
                      <option value="0">0</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3+</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group mb-3">
                <label for="tour-type"><?= e($settings['label_tour_type'] ?? 'Tour Type') ?></label>
                <select name="tour_type" id="tour-type" class="form-select" required="">
                  <option value=""><?= e($settings['placeholder_tour_type'] ?? 'Select tour type') ?></option>
                  <option value="adventure">Adventure</option>
                  <option value="cultural">Cultural</option>
                  <option value="relaxation">Relaxation</option>
                  <option value="family">Family</option>
                  <option value="luxury">Luxury</option>
                </select>
              </div>

              <button type="submit" class="btn btn-primary w-100"><?= e($settings['form_button_text'] ?? 'Find Your Perfect Trip') ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

</section>
