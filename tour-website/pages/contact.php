<?php
/**
 * İletişim sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sunline/core.php';
require_once INCLUDES_PATH . 'schema.php';

$title = sl_t('contact_t');
$url = sl_page_url('contact');
$success = false;
$error = '';
$old = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) $old[$k] = trim((string)($_POST[$k] ?? ''));
    $spam = trim((string)($_POST['website'] ?? '')) !== '';
    if ($spam) {
        $success = true; // botlara başarılı gibi görün
    } elseif ($old['name'] === '' || $old['email'] === '' || $old['message'] === '') {
        $error = sl_t('c_err');
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = sl_t('err_email');
    } else {
        try {
            $stmt = getDB()->prepare("INSERT INTO contacts (name, email, phone, subject, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                mb_substr($old['name'], 0, 100), mb_substr($old['email'], 0, 100), mb_substr($old['phone'], 0, 20),
                mb_substr($old['subject'], 0, 200), mb_substr($old['message'], 0, 5000),
                $_SERVER['REMOTE_ADDR'] ?? '', mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
            $success = true;
            $old = array_fill_keys(array_keys($old), '');
        } catch (Exception $e) {
            $error = sl_t('err_generic');
        }
    }
}

$address = trim((string)getSetting('contact_address', ''));
$phone = trim((string)getSetting('contact_phone', ''));
$email = trim((string)getSetting('contact_email', ''));
$wa = sl_wa_link(sl_t('wa_hello'));

$seoTitle = $title . ' · ' . getSetting('site_name', 'Sunline VIP Transfer');
$metaDescription = sl_t('contact_s');
$canonicalUrl = $url;
$bodyClass = 'sl-contact';
$pageSchema = [getBreadcrumbSchema([['name' => $title, 'url' => $url]])];

require_once INCLUDES_PATH . 'header.php';
sl_partial('page-hero', ['h1' => $title, 'sub' => sl_t('contact_s'), 'crumbs' => [[$title, $url]], 'size' => 'sm']);
?>
<div class="container">
  <div class="qf">
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="cinfo">
          <?php if ($wa !== ''): ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i><span><small>WhatsApp</small><?= e($phone !== '' ? $phone : 'WhatsApp') ?></span></a><?php endif; ?>
          <?php if ($phone !== ''): ?><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><i class="bi bi-telephone"></i><span><small><?= sl_e('c_phone') ?></small><?= e($phone) ?></span></a><?php endif; ?>
          <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>"><i class="bi bi-envelope"></i><span><small><?= sl_e('c_email') ?></small><?= e($email) ?></span></a><?php endif; ?>
          <?php if ($address !== ''): ?><a href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode($address)) ?>" target="_blank" rel="noopener"><i class="bi bi-geo-alt"></i><span><small><?= sl_e('address') ?></small><?= e($address) ?></span></a><?php endif; ?>
        </div>
      </div>
      <div class="col-lg-7">
        <?php if ($success): ?>
        <div class="alert-sl ok-msg mb-3" role="status"><i class="bi bi-check-circle-fill"></i><span><?= sl_e('c_ok') ?></span></div>
        <?php elseif ($error !== ''): ?>
        <div class="alert-sl err-msg mb-3" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><span><?= e($error) ?></span></div>
        <?php endif; ?>
        <form method="post" class="fm row g-3" novalidate>
          <div class="col-sm-6"><label class="d-block" for="cName"><?= sl_e('c_name') ?> *</label><input class="form-control" id="cName" name="name" required maxlength="100" autocomplete="name" value="<?= e($old['name']) ?>"></div>
          <div class="col-sm-6"><label class="d-block" for="cEmail"><?= sl_e('c_email') ?> *</label><input class="form-control" id="cEmail" name="email" type="email" required maxlength="100" autocomplete="email" value="<?= e($old['email']) ?>"></div>
          <div class="col-sm-6"><label class="d-block" for="cPhone"><?= sl_e('c_phone') ?></label><input class="form-control" id="cPhone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="<?= e($old['phone']) ?>"></div>
          <div class="col-sm-6"><label class="d-block" for="cSubject"><?= sl_e('c_subject') ?></label><input class="form-control" id="cSubject" name="subject" maxlength="200" value="<?= e($old['subject']) ?>"></div>
          <div class="col-12"><label class="d-block" for="cMsg"><?= sl_e('c_message') ?> *</label><textarea class="form-control" id="cMsg" name="message" rows="5" required maxlength="5000"><?= e($old['message']) ?></textarea></div>
          <div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden"><label for="cWeb">Website</label><input id="cWeb" name="website" tabindex="-1" autocomplete="off"></div>
          <div class="col-12"><button type="submit" class="btn btn-cta"><i class="bi bi-send me-2"></i><?= sl_e('c_send') ?></button></div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="sec pt-5"></div>
<?php require_once INCLUDES_PATH . 'footer.php'; ?>
