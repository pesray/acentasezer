<?php
/**
 * Sunline teması — ortak footer
 * Anahtar kelime şeridi, footer, WhatsApp düğmesi, rezervasyon sihirbazı, JSON-LD
 */

require_once INCLUDES_PATH . 'sunline/core.php';

$slGlobalF = sl_section('global');
$slLogoF = sl_logo_urls();
$slSiteName = getSetting('site_name', 'Sunline VIP Transfer');
$slAddress = trim((string)getSetting('contact_address', ''));
$slPhone = trim((string)getSetting('contact_phone', ''));
$slEmail = trim((string)getSetting('contact_email', ''));
$slWa = sl_wa_link(sl_t('wa_hello'));
$slKw = sl_keywords();
$slPop = array_slice(sl_bookable_destinations(true), 0, 8);
if (!$slPop) $slPop = array_slice(sl_bookable_destinations(false), 0, 8);
$slSocial = [];
foreach (['google_business' => 'bi-google', 'instagram' => 'bi-instagram', 'facebook' => 'bi-facebook', 'youtube' => 'bi-youtube', 'twitter' => 'bi-twitter-x', 'linkedin' => 'bi-linkedin'] as $slNet => $slIco) {
    $slUrl = trim((string)getSetting('social_' . $slNet, ''));
    if ($slUrl !== '') $slSocial[] = [$slUrl, $slIco, $slNet === 'google_business' ? 'Google' : ucfirst($slNet)];
}
$slOlF = sl_olcum();
?>
</main>

<?php if ($slKw): ?>
<div class="kw" aria-label="Transfer">
  <div class="promo-track" style="animation-duration:<?= max(40, count($slKw) * 4) ?>s">
    <?php for ($slR = 0; $slR < 2; $slR++): foreach ($slKw as $slK): ?>
    <span<?= $slR ? ' aria-hidden="true"' : '' ?>><?php if ($slK['u'] !== ''): ?><a href="<?= e($slK['u']) ?>"<?= $slR ? ' tabindex="-1"' : '' ?>><?= e($slK['t']) ?></a><?php else: ?><?= e($slK['t']) ?><?php endif; ?></span>
    <?php endforeach; endfor; ?>
  </div>
</div>
<?php endif; ?>

<footer class="ftr" id="iletisim">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <a class="logo mb-3" href="<?= e(langUrl('')) ?>" aria-label="<?= e($slSiteName) ?>">
          <img class="lg-l" src="<?= e($slLogoF['light']) ?>" alt="<?= e($slSiteName) ?> Antalya" style="height:64px;width:auto" width="158" height="64" loading="lazy">
          <img class="lg-d" src="<?= e($slLogoF['dark']) ?>" alt="<?= e($slSiteName) ?> Antalya" style="height:64px;width:auto" width="158" height="64" loading="lazy">
        </a>
        <?php if (trim((string)($slGlobalF['footer_about'] ?? '')) !== ''): ?>
        <p><?= e($slGlobalF['footer_about']) ?></p>
        <?php endif; ?>
        <?php if ($slSocial): ?>
        <div class="d-flex gap-2 mt-3">
          <?php foreach ($slSocial as $slS): ?>
          <a class="soc" href="<?= e($slS[0]) ?>" target="_blank" rel="noopener" aria-label="<?= e($slS[2]) ?>"><i class="bi <?= e($slS[1]) ?>"></i></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="col-6 col-lg-2">
        <h6><?= sl_e('f_quick') ?></h6>
        <ul>
          <?php foreach ((array)($slGlobalF['footer_links'] ?? []) as $slL): if (trim((string)($slL['l'] ?? '')) === '') continue; $slH = sl_link($slL['u'] ?? ''); if ($slH === '') continue; ?>
          <li><a href="<?= e($slH) ?>"><?= e($slL['l']) ?></a></li>
          <?php endforeach; ?>
          <li><a href="<?= e(sl_page_url('destinations')) ?>"><?= sl_e('all_transfers') ?></a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <h6><?= sl_e('f_pop') ?></h6>
        <ul>
          <?php foreach ($slPop as $slD): ?>
          <li><a href="<?= e($slD['url']) ?>"><?= e($slD['place']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-lg-3">
        <h6><?= sl_e('f_contact') ?></h6>
        <ul>
          <?php if ($slAddress !== ''): ?><li><a href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($slAddress)) ?>" target="_blank" rel="noopener"><i class="bi bi-geo-alt me-2"></i><?= e($slAddress) ?></a></li><?php endif; ?>
          <?php if ($slPhone !== ''): ?><li><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $slPhone)) ?>"><i class="bi bi-telephone me-2"></i><?= e($slPhone) ?></a></li><?php endif; ?>
          <?php if ($slWa !== ''): ?><li><a href="<?= e($slWa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a></li><?php endif; ?>
          <?php if ($slEmail !== ''): ?><li><a href="mailto:<?= e($slEmail) ?>"><i class="bi bi-envelope me-2"></i><?= e($slEmail) ?></a></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="copy">© <?= date('Y') ?> <?= e(mb_strtoupper($slSiteName, 'UTF-8')) ?> — <?= sl_e('rights') ?><?php if ($slOlF['any']): ?> · <button type="button" class="ck-open" data-ck-open><?= sl_e('ck_set') ?></button><?php endif; ?></div>
  </div>
</footer>

<?php if ($slWa !== ''): ?>
<a class="wa" href="<?= e($slWa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
<?php endif; ?>

<?php sl_partial('booking-modal'); ?>

<?php if ($slOlF['any']): $slKv = sl_page_url_by_base('kvkk'); ?>
<div class="ck" id="slCk" role="dialog" aria-label="<?= sl_e('ck_title') ?>" data-ym="<?= e($slOlF['ym']) ?>" hidden>
  <p><?= sl_e('ck_text') ?><?php if ($slKv !== ''): ?> <a href="<?= e($slKv) ?>"><?= sl_e('ck_more') ?></a><?php endif; ?></p>
  <div class="ck-b">
    <button type="button" class="btn btn-ghost btn-sm" data-ck="denied"><?= sl_e('ck_no') ?></button>
    <button type="button" class="btn btn-cta btn-sm" data-ck="granted"><?= sl_e('ck_ok') ?></button>
  </div>
</div>
<?php endif; ?>

<script>window.SL=<?= sl_json(sl_booking_payload()) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?= ASSETS_URL ?>js/sunline.js?v=<?= SL_ASSET_VER ?>" defer></script>
<?php if ($slOlF['any']): ?>
<script src="<?= ASSETS_URL ?>js/sl-olcum.js?v=<?= SL_ASSET_VER ?>" defer></script>
<?php endif; ?>
<?php if (isset($extraJs)) echo $extraJs; ?>
<?php
require_once INCLUDES_PATH . 'schema.php';
echo renderSchema(getOrganizationSchema());
if (sl_is_home()) echo renderSchema(getWebsiteSchema());
if (isset($pageSchema) && is_array($pageSchema)) {
    if (isset($pageSchema['@context'])) {
        echo renderSchema($pageSchema);
    } else {
        foreach ($pageSchema as $slSchemaItem) {
            if (!empty($slSchemaItem)) echo renderSchema($slSchemaItem);
        }
    }
}
?>
</body>
</html>
