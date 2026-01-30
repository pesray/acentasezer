<?php
/**
 * İletişim Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$pageTitle = __('menu_contact', 'header');
$bodyClass = 'contact-page';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = __('required_fields', 'general');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = __('invalid_email', 'general');
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO contacts (name, email, phone, subject, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']);
            $success = true;
        } catch (Exception $e) {
            $error = __('error_occurred', 'general');
        }
    }
}

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= ASSETS_URL ?>img/page-title-bg.webp);">
    <div class="container position-relative">
        <h1><?= __('menu_contact', 'header') ?></h1>
        <p><?= __('contact_subtitle', 'general') ?></p>
    </div>
</div>

<section class="contact section">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4">
                <div class="info-item">
                    <i class="bi bi-geo-alt"></i>
                    <h4><?= __('address', 'general') ?></h4>
                    <p><?= nl2br(e(getSetting('contact_address'))) ?></p>
                </div>
                
                <div class="info-item">
                    <i class="bi bi-telephone"></i>
                    <h4><?= __('phone', 'general') ?></h4>
                    <p><?= e(getSetting('contact_phone')) ?></p>
                </div>
                
                <div class="info-item">
                    <i class="bi bi-envelope"></i>
                    <h4><?= __('email', 'general') ?></h4>
                    <p><?= e(getSetting('contact_email')) ?></p>
                </div>
            </div>
            
            <div class="col-lg-8">
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i> <?= __('message_sent', 'general') ?>
                </div>
                <?php else: ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                
                <form method="post" class="contact-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="name" class="form-control" placeholder="<?= __('your_name', 'general') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="email" name="email" class="form-control" placeholder="<?= __('your_email', 'general') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="tel" name="phone" class="form-control" placeholder="<?= __('your_phone', 'general') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" name="subject" class="form-control" placeholder="<?= __('subject', 'general') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="6" placeholder="<?= __('your_message', 'general') ?>" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-send me-2"></i> <?= __('send_message', 'general') ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
