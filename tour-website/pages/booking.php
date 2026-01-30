<?php
/**
 * Rezervasyon Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$pageTitle = __('booking', 'general');
$bodyClass = 'booking-page';

$tourSlug = $_GET['tour'] ?? '';
$tour = null;
$success = false;
$error = '';

if ($tourSlug) {
    $tour = getTourBySlug($tourSlug);
}

// Form gönderimi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tourId = (int)($_POST['tour_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $adults = (int)($_POST['adults'] ?? 1);
    $children = (int)($_POST['children'] ?? 0);
    $departureDate = $_POST['departure_date'] ?? '';
    $specialRequests = trim($_POST['special_requests'] ?? '');
    
    if (empty($name) || empty($email) || empty($tourId)) {
        $error = __('required_fields', 'general');
    } else {
        try {
            $db = getDB();
            
            // Tur fiyatını al
            $stmt = $db->prepare("SELECT price, sale_price, currency FROM tours WHERE id = ?");
            $stmt->execute([$tourId]);
            $tourData = $stmt->fetch();
            
            $pricePerPerson = $tourData['sale_price'] ?? $tourData['price'];
            $totalPrice = $pricePerPerson * ($adults + ($children * 0.5));
            
            // Rezervasyon numarası oluştur
            $bookingNumber = 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -4));
            
            $stmt = $db->prepare("
                INSERT INTO bookings (booking_number, tour_id, customer_name, customer_email, customer_phone, adults, children, departure_date, special_requests, total_price, currency, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $bookingNumber, $tourId, $name, $email, $phone, $adults, $children,
                $departureDate ?: null, $specialRequests, $totalPrice, $tourData['currency'],
                $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            $success = true;
            $_SESSION['booking_number'] = $bookingNumber;
        } catch (Exception $e) {
            $error = __('error_occurred', 'general');
        }
    }
}

// Turları getir
$lang = getCurrentLang();
$db = getDB();
$stmt = $db->prepare("
    SELECT t.id, COALESCE(tt.title, t.title) as title, t.price, t.sale_price, t.currency
    FROM tours t
    LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
    WHERE t.status = 'published'
    ORDER BY t.title
");
$stmt->execute([$lang]);
$tours = $stmt->fetchAll();

require_once INCLUDES_PATH . 'header.php';
?>

<div class="page-title dark-background" style="background-image: url(<?= ASSETS_URL ?>img/page-title-bg.webp);">
    <div class="container position-relative">
        <h1><?= __('booking', 'general') ?></h1>
    </div>
</div>

<section class="booking section">
    <div class="container">
        <?php if ($success): ?>
        <div class="text-center py-5">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            <h2 class="mt-3"><?= __('booking_success', 'general') ?></h2>
            <p class="lead"><?= __('booking_number', 'general') ?>: <strong><?= e($_SESSION['booking_number']) ?></strong></p>
            <p><?= __('booking_confirmation_email', 'general') ?></p>
            <a href="<?= SITE_URL ?>" class="btn btn-primary mt-3"><?= __('back_to_home', 'general') ?></a>
        </div>
        <?php else: ?>
        
        <div class="row">
            <div class="col-lg-8">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><?= __('booking_form', 'general') ?></h5></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label"><?= __('select_tour', 'general') ?> <span class="text-danger">*</span></label>
                                <select name="tour_id" class="form-select" required>
                                    <option value=""><?= __('choose', 'general') ?></option>
                                    <?php foreach ($tours as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= ($tour && $tour['id'] == $t['id']) ? 'selected' : '' ?>>
                                        <?= e($t['title']) ?> - <?= $t['currency'] ?><?= number_format($t['sale_price'] ?? $t['price'], 0) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('your_name', 'general') ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('your_email', 'general') ?> <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('your_phone', 'general') ?></label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('departure_date', 'general') ?></label>
                                    <input type="date" name="departure_date" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('adults', 'general') ?></label>
                                    <select name="adults" class="form-select">
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= __('children', 'general') ?></label>
                                    <select name="children" class="form-select">
                                        <?php for ($i = 0; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><?= __('special_requests', 'general') ?></label>
                                <textarea name="special_requests" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-calendar-check me-2"></i> <?= __('submit_booking', 'general') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><?= __('need_help', 'general') ?></h5></div>
                    <div class="card-body">
                        <p><i class="bi bi-telephone me-2"></i> <?= e(getSetting('contact_phone')) ?></p>
                        <p><i class="bi bi-envelope me-2"></i> <?= e(getSetting('contact_email')) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
