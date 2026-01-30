<?php
/**
 * Footer Include
 * Tüm sayfalarda kullanılacak ortak footer
 */

$footerMenuItems = getMenuItems('footer-menu');
$siteName = getSetting('site_name', 'Tour');
$contactAddress = getSetting('contact_address', '');
$contactPhone = getSetting('contact_phone', '');
$contactEmail = getSetting('contact_email', '');
$socialFacebook = getSetting('social_facebook', '');
$socialTwitter = getSetting('social_twitter', '');
$socialInstagram = getSetting('social_instagram', '');
$socialLinkedin = getSetting('social_linkedin', '');
$socialYoutube = getSetting('social_youtube', '');
?>

  </main>

  <footer id="footer" class="footer position-relative dark-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="<?= SITE_URL ?>/" class="d-flex align-items-center">
            <span class="sitename"><?= e($siteName) ?></span>
          </a>
          <div class="footer-contact pt-3">
            <?php if ($contactAddress): ?>
            <p><?= nl2br(e($contactAddress)) ?></p>
            <?php endif; ?>
            <?php if ($contactPhone): ?>
            <p class="mt-3"><strong><?= __('phone', 'general') ?>:</strong> <span><?= e($contactPhone) ?></span></p>
            <?php endif; ?>
            <?php if ($contactEmail): ?>
            <p><strong><?= __('email', 'general') ?>:</strong> <span><?= e($contactEmail) ?></span></p>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4><?= __('footer_links_title', 'footer') ?></h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/"><?= __('menu_home', 'header') ?></a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/hakkimizda"><?= __('menu_about', 'header') ?></a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/turlar"><?= __('menu_tours', 'header') ?></a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/iletisim"><?= __('menu_contact', 'header') ?></a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4><?= __('footer_services_title', 'footer') ?></h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/destinasyonlar"><?= __('menu_destinations', 'header') ?></a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/galeri"><?= __('menu_gallery', 'header') ?></a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/blog"><?= __('menu_blog', 'header') ?></a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="<?= SITE_URL ?>/sss"><?= __('faq', 'general') ?></a></li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-12">
          <h4><?= __('footer_follow_title', 'footer') ?></h4>
          <p><?= __('footer_follow_text', 'footer') ?></p>
          <div class="social-links d-flex">
            <?php if ($socialTwitter): ?>
            <a href="<?= e($socialTwitter) ?>" target="_blank"><i class="bi bi-twitter-x"></i></a>
            <?php endif; ?>
            <?php if ($socialFacebook): ?>
            <a href="<?= e($socialFacebook) ?>" target="_blank"><i class="bi bi-facebook"></i></a>
            <?php endif; ?>
            <?php if ($socialInstagram): ?>
            <a href="<?= e($socialInstagram) ?>" target="_blank"><i class="bi bi-instagram"></i></a>
            <?php endif; ?>
            <?php if ($socialLinkedin): ?>
            <a href="<?= e($socialLinkedin) ?>" target="_blank"><i class="bi bi-linkedin"></i></a>
            <?php endif; ?>
            <?php if ($socialYoutube): ?>
            <a href="<?= e($socialYoutube) ?>" target="_blank"><i class="bi bi-youtube"></i></a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p><?= __('footer_copyright', 'footer') ?> <strong class="px-1 sitename"><?= e($siteName) ?></strong></p>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="<?= ASSETS_URL ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= ASSETS_URL ?>vendor/php-email-form/validate.js"></script>
  <script src="<?= ASSETS_URL ?>vendor/aos/aos.js"></script>
  <script src="<?= ASSETS_URL ?>vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="<?= ASSETS_URL ?>vendor/swiper/swiper-bundle.min.js"></script>
  <script src="<?= ASSETS_URL ?>vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="<?= ASSETS_URL ?>vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="<?= ASSETS_URL ?>vendor/glightbox/js/glightbox.min.js"></script>

  <!-- Main JS File -->
  <script src="<?= ASSETS_URL ?>js/main.js"></script>

  <?php if (isset($extraJs)): ?>
  <?= $extraJs ?>
  <?php endif; ?>

</body>

</html>
