<?php
/**
 * Testimonials Section Template
 */
$section = $GLOBALS['current_section'];
$settings = $GLOBALS['section_settings'];
$limit = $settings['limit'] ?? 6;
$testimonials = getTestimonials($limit);
?>

<section id="testimonials-home" class="testimonials-home section">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2><?= e($section['title']) ?></h2>
    <div><span>What Our Customers</span> <span class="description-title">Are Saying</span></div>
  </div><!-- End Section Title -->

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="swiper init-swiper">
      <script type="application/json" class="swiper-config">
        {
          "loop": true,
          "speed": 600,
          "autoplay": {
            "delay": 5000
          },
          "slidesPerView": "auto",
          "pagination": {
            "el": ".swiper-pagination",
            "type": "bullets",
            "clickable": true
          },
          "breakpoints": {
            "320": {
              "slidesPerView": 1,
              "spaceBetween": 40
            },
            "1200": {
              "slidesPerView": 3,
              "spaceBetween": 1
            }
          }
        }
      </script>
      <div class="swiper-wrapper">

        <?php foreach ($testimonials as $testimonial): ?>
        <div class="swiper-slide">
          <div class="testimonial-item">
            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span><?= e($testimonial['content']) ?></span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
            <img src="<?= $testimonial['customer_image'] ? UPLOADS_URL . e($testimonial['customer_image']) : ASSETS_URL . 'img/person/person-m-9.webp' ?>" class="testimonial-img" alt="<?= e($testimonial['customer_name']) ?>" loading="lazy">
            <h3><?= e($testimonial['customer_name']) ?></h3>
            <h4><?= e($testimonial['customer_title']) ?></h4>
          </div>
        </div>
        <?php endforeach; ?>

      </div>
      <div class="swiper-pagination"></div>
    </div>

  </div>

</section>
