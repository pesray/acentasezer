<?php
/**
 * Tur Detay Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$slug = $_GET['slug'] ?? '';
$lang = getCurrentLang();
$db = getDB();

// Turu slug'a göre getir
$stmt = $db->prepare("
    SELECT t.*, COALESCE(tt.title, t.title) as title, COALESCE(tt.slug, t.slug) as slug,
           COALESCE(tt.description, t.description) as description,
           COALESCE(tt.meta_title, t.meta_title) as meta_title,
           COALESCE(tt.meta_description, t.meta_description) as meta_description
    FROM tours t
    LEFT JOIN tour_translations tt ON t.id = tt.tour_id AND tt.language_code = ?
    WHERE (t.slug = ? OR tt.slug = ?) AND t.status = 'published'
");
$stmt->execute([$lang, $slug, $slug]);
$tour = $stmt->fetch();

if (!$tour) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $tour['meta_title'] ?: $tour['title'];
$metaDescription = $tour['meta_description'] ?: $tour['description'];
$bodyClass = 'tour-details-page';

// Bu tur için araçları getir - önce mevcut dilde, yoksa herhangi bir dilde
$vehicles = [];
try {
    // Önce mevcut dilde dene
    $vehicleStmt = $db->prepare("
        SELECT v.*, tv.price, tv.currency
        FROM tour_vehicles tv
        JOIN vehicles v ON tv.vehicle_id = v.id
        WHERE tv.tour_id = ? AND tv.language_code = ? AND v.is_active = 1
        ORDER BY v.sort_order
    ");
    $vehicleStmt->execute([$tour['id'], $lang]);
    $vehicles = $vehicleStmt->fetchAll();
    
    // Mevcut dilde yoksa, herhangi bir dildeki araçları al (fallback)
    if (empty($vehicles)) {
        $vehicleStmt = $db->prepare("
            SELECT v.*, tv.price, tv.currency, tv.language_code
            FROM tour_vehicles tv
            JOIN vehicles v ON tv.vehicle_id = v.id
            WHERE tv.tour_id = ? AND v.is_active = 1
            GROUP BY v.id
            ORDER BY v.sort_order
        ");
        $vehicleStmt->execute([$tour['id']]);
        $vehicles = $vehicleStmt->fetchAll();
    }
} catch (Exception $e) {}

// Sayfa ayarlarını çek (turlar için)
$pageSettings = null;
try {
    $stmt = $db->prepare("
        SELECT ps.*, pst.slug as page_slug
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'tours'
    ");
    $stmt->execute([$lang]);
    $pageSettings = $stmt->fetch();
} catch (Exception $e) {}

$toursPrefix = !empty($pageSettings['page_slug']) ? $pageSettings['page_slug'] : 'tours';

// Para birimi sembolleri
$currencySymbols = [
    'TRY' => '₺',
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£'
];

// Araç içi hizmetleri tek sorguda al
$availableServices = [];
try {
    $serviceStmt = $db->prepare("
        SELECT vs.id, vs.icon, 
               COALESCE(vst.name, vst_tr.name, '') as name
        FROM vehicle_services vs
        LEFT JOIN vehicle_service_translations vst ON vs.id = vst.service_id AND vst.language_code = ?
        LEFT JOIN vehicle_service_translations vst_tr ON vs.id = vst_tr.service_id AND vst_tr.language_code = 'tr'
        WHERE vs.is_active = 1
        ORDER BY vs.sort_order
    ");
    $serviceStmt->execute([$lang]);
    $allServices = $serviceStmt->fetchAll();
    
    foreach ($allServices as $svc) {
        $availableServices[$svc['id']] = [
            'icon' => $svc['icon'],
            'label' => $svc['name']
        ];
    }
} catch (Exception $e) {}

// Varsayılan metinler
$defaultTexts = [
    'tr' => [
        'home' => 'Ana Sayfa', 'tours' => 'Turlar',
        'available_vehicles' => 'Mevcut Araçlar', 'choose_vehicle' => 'Bu tur için kullanılabilir araçlar',
        'passengers' => 'Yolcu', 'luggage' => 'Bagaj', 'child_seats' => 'Çocuk Koltuğu',
        'gallery' => 'Galeri', 'gallery_desc' => 'Tur görüntüleri',
        'contact_us' => 'Bizimle İletişime Geçin', 'contact_desc' => 'Bu tur hakkında bilgi almak için bizimle iletişime geçin',
        'select_vehicle' => 'Seç', 'tour_info_title' => 'Tur Bilgileri',
        'full_name' => 'Ad Soyad', 'email' => 'E-posta', 'phone' => 'Telefon',
        'pickup_location' => 'Alınış Yeri', 'pickup_date' => 'Alınış Tarihi', 
        'pickup_time' => 'Alınış Saati', 'return_time' => 'Dönüş Saati',
        'adults_count' => 'Yetişkin Sayısı', 'children_count' => 'Çocuk Sayısı', 'child_seat' => 'Çocuk Koltuğu',
        'notes' => 'Notlar', 'send_inquiry' => 'Rezervasyon Yap',
        'full_name_placeholder' => 'Adınız ve soyadınız', 'email_placeholder' => 'E-posta adresiniz',
        'phone_placeholder' => '+90 5XX XXX XX XX', 'notes_placeholder' => 'Varsa sorularınızı yazın',
        'pickup_location_placeholder' => 'Otel adı veya adres',
        'return_transfer' => 'Dönüş Transferi İstiyorum',
        'return_flight_date' => 'Dönüş Uçuş Tarihi', 'return_flight_time' => 'Dönüş Uçuş Saati',
        'return_flight_number' => 'Dönüş Uçuş Numarası', 'return_pickup_time' => 'Dönüş Alınış Saati',
        'return_hotel_address' => 'Dönüş İçin Alınacak Otel / Adres',
        'hotel_address' => 'Varış Otel Adı / Adresi', 'hotel_placeholder' => 'Otel adı veya tam adres',
        'return_hotel_placeholder' => 'Dönüşte alınacak otel adı veya adres',
        'flight_number_placeholder' => 'Örn: TK1234',
        'booking_success' => 'Rezervasyonunuz başarıyla alındı! En kısa sürede sizinle iletişime geçeceğiz.',
        'booking_error' => 'Rezervasyon gönderilirken bir hata oluştu. Lütfen tekrar deneyin.',
    ],
    'en' => [
        'home' => 'Home', 'tours' => 'Tours',
        'available_vehicles' => 'Available Vehicles', 'choose_vehicle' => 'Vehicles available for this tour',
        'passengers' => 'Passengers', 'luggage' => 'Luggage', 'child_seats' => 'Child Seat',
        'gallery' => 'Gallery', 'gallery_desc' => 'Tour images',
        'contact_us' => 'Contact Us', 'contact_desc' => 'Contact us for information about this tour',
        'select_vehicle' => 'Select', 'tour_info_title' => 'Tour Information',
        'full_name' => 'Full Name', 'email' => 'Email', 'phone' => 'Phone',
        'pickup_location' => 'Pickup Location', 'pickup_date' => 'Pickup Date', 
        'pickup_time' => 'Pickup Time', 'return_time' => 'Return Time',
        'adults_count' => 'Number of Adults', 'children_count' => 'Number of Children', 'child_seat' => 'Child Seat',
        'notes' => 'Notes', 'send_inquiry' => 'Book Now',
        'full_name_placeholder' => 'Your full name', 'email_placeholder' => 'Your email address',
        'phone_placeholder' => '+1 XXX XXX XXXX', 'notes_placeholder' => 'Write your questions if any',
        'pickup_location_placeholder' => 'Hotel name or address',
        'return_transfer' => 'I Want Return Transfer',
        'return_flight_date' => 'Return Flight Date', 'return_flight_time' => 'Return Flight Time',
        'return_flight_number' => 'Return Flight Number', 'return_pickup_time' => 'Return Pickup Time',
        'return_hotel_address' => 'Return Pickup Hotel / Address',
        'hotel_address' => 'Arrival Hotel Name / Address', 'hotel_placeholder' => 'Hotel name or full address',
        'return_hotel_placeholder' => 'Hotel name or address for return pickup',
        'flight_number_placeholder' => 'E.g: TK1234',
        'booking_success' => 'Your booking has been received! We will contact you shortly.',
        'booking_error' => 'An error occurred while sending your booking. Please try again.',
    ],
    'de' => [
        'home' => 'Startseite', 'tours' => 'Touren',
        'available_vehicles' => 'Verfügbare Fahrzeuge', 'choose_vehicle' => 'Für diese Tour verfügbare Fahrzeuge',
        'passengers' => 'Passagiere', 'luggage' => 'Gepäck', 'child_seats' => 'Kindersitz',
        'gallery' => 'Galerie', 'gallery_desc' => 'Tour-Bilder',
        'contact_us' => 'Kontaktieren Sie uns', 'contact_desc' => 'Kontaktieren Sie uns für Informationen zu dieser Tour',
        'select_vehicle' => 'Wählen', 'tour_info_title' => 'Tour-Informationen',
        'full_name' => 'Vollständiger Name', 'email' => 'E-Mail', 'phone' => 'Telefon',
        'pickup_location' => 'Abholort', 'pickup_date' => 'Abholdatum', 
        'pickup_time' => 'Abholzeit', 'return_time' => 'Rückfahrzeit',
        'adults_count' => 'Anzahl Erwachsene', 'children_count' => 'Anzahl Kinder', 'child_seat' => 'Kindersitz',
        'notes' => 'Notizen', 'send_inquiry' => 'Jetzt buchen',
        'full_name_placeholder' => 'Ihr vollständiger Name', 'email_placeholder' => 'Ihre E-Mail-Adresse',
        'phone_placeholder' => '+49 XXX XXX XXXX', 'notes_placeholder' => 'Schreiben Sie Ihre Fragen',
        'pickup_location_placeholder' => 'Hotelname oder Adresse',
        'return_transfer' => 'Rücktransfer gewünscht',
        'return_flight_date' => 'Rückflugdatum', 'return_flight_time' => 'Rückflugzeit',
        'return_flight_number' => 'Rückflugnummer', 'return_pickup_time' => 'Rück-Abholzeit',
        'return_hotel_address' => 'Rück-Abholhotel / Adresse',
        'hotel_address' => 'Ankunftshotel / Adresse', 'hotel_placeholder' => 'Hotelname oder vollständige Adresse',
        'return_hotel_placeholder' => 'Hotelname oder Adresse für die Rückabholung',
        'flight_number_placeholder' => 'z.B: TK1234',
        'booking_success' => 'Ihre Buchung wurde erfolgreich empfangen! Wir werden uns in Kürze bei Ihnen melden.',
        'booking_error' => 'Beim Senden Ihrer Buchung ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.',
    ],
];

$t = $defaultTexts[$lang] ?? $defaultTexts['en'];

require_once INCLUDES_PATH . 'header.php';
?>

<!-- Page Title -->
<div class="page-title dark-background" data-aos="fade" style="background-image: url(<?= !empty($tour['image']) ? getMediaUrl($tour['image']) : ASSETS_URL . 'img/page-title-bg.webp' ?>);">
    <div class="container position-relative">
        <h1><?= e($tour['title']) ?></h1>
        <?php if (!empty($tour['description'])): ?>
        <p><?= e($tour['description']) ?></p>
        <?php endif; ?>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="<?= langUrl('') ?>"><?= $t['home'] ?></a></li>
                <li><a href="<?= langUrl($toursPrefix) ?>"><?= $t['tours'] ?></a></li>
                <li class="current"><?= e($tour['title']) ?></li>
            </ol>
        </nav>
    </div>
</div><!-- End Page Title -->

<!-- Tour Details Section -->
<section id="tour-details" class="tour-details section">
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <!-- Overview Section -->
        <?php if (!empty($tour['content']) || !empty($tour['description'])): ?>
        <div class="tour-overview" data-aos="fade-up" data-aos-delay="200">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <?php if (!empty($tour['content'])): ?>
                    <?= $tour['content'] ?>
                    <?php elseif (!empty($tour['description'])): ?>
                    <p><?= nl2br(e($tour['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Vehicle Selection Section -->
        <?php if (!empty($vehicles)): ?>
        <div id="booking-section" class="booking-section" data-aos="fade-up" data-aos-delay="300">
            <div class="section-header">
                <h2><?= e($tour['title']) ?> - <?= $t['available_vehicles'] ?></h2>
                <p><?= $t['choose_vehicle'] ?></p>
            </div>
            
            <!-- Vehicle Selection Cards -->
            <div class="vehicle-selection-list">
                <?php foreach ($vehicles as $index => $vehicle): 
                    $services = !empty($vehicle['services']) ? json_decode($vehicle['services'], true) : [];
                ?>
                <div class="vehicle-select-card" data-vehicle-id="<?= (int)$vehicle['id'] ?>" 
                     data-vehicle-name="<?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?>"
                     data-vehicle-price-raw="<?= (float)$vehicle['price'] ?>"
                     data-vehicle-currency="<?= e($vehicle['currency'] ?? 'TRY') ?>"
                     data-vehicle-capacity="<?= (int)$vehicle['capacity'] ?>"
                     data-child-seat-capacity="<?= (int)($vehicle['child_seat_capacity'] ?? 0) ?>"
                     data-aos="fade-up" data-aos-delay="<?= 100 + ($index * 50) ?>">
                    <div class="vehicle-select-inner">
                        <div class="vehicle-select-image">
                            <?php if (!empty($vehicle['image'])): ?>
                            <img src="<?= getMediaUrl($vehicle['image']) ?>" alt="<?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?>" loading="lazy">
                            <?php else: ?>
                            <img src="<?= ASSETS_URL ?>img/travel/tour-1.webp" alt="<?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="vehicle-select-info">
                            <h4 class="vehicle-title"><?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?></h4>
                            <div class="vehicle-specs">
                                <span class="spec-item"><i class="bi bi-people-fill"></i> <?= (int)$vehicle['capacity'] ?> <?= $t['passengers'] ?></span>
                                <span class="spec-item"><i class="bi bi-briefcase-fill"></i> <?= (int)$vehicle['luggage_capacity'] ?> <?= $t['luggage'] ?></span>
                                <?php if (!empty($vehicle['child_seat_capacity']) && $vehicle['child_seat_capacity'] > 0): ?>
                                <span class="spec-item"><i class="bi bi-person-arms-up"></i> <?= (int)$vehicle['child_seat_capacity'] ?> <?= $t['child_seats'] ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($services)): ?>
                            <div class="vehicle-services-inline">
                                <?php foreach ($services as $service): 
                                    $serviceKey = is_numeric($service) ? (int)$service : $service;
                                    $serviceInfo = $availableServices[$serviceKey] ?? null;
                                    if ($serviceInfo && !empty($serviceInfo['label'])):
                                ?>
                                <span class="service-tag"><i class="bi <?= e($serviceInfo['icon']) ?>"></i> <?= e($serviceInfo['label']) ?></span>
                                <?php endif; endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="vehicle-select-action">
                            <button type="button" class="btn-select-vehicle">
                                <i class="bi bi-check-lg"></i> <?= $t['select_vehicle'] ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Inquiry Form (Hidden by default) -->
            <div id="inquiry-form-wrapper" class="booking-form-wrapper" style="display: none;">
                <div class="transfer-info-header">
                    <div class="transfer-info-left">
                        <h5 class="transfer-info-title"><?= $t['tour_info_title'] ?></h5>
                        <span class="tour-name"><?= e($tour['title']) ?></span>
                    </div>
                    <div class="transfer-info-right">
                        <div class="selected-vehicle-compact">
                            <span id="selected-vehicle-name" class="selected-name"></span>
                        </div>
                        <button type="button" class="btn-change-vehicle"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                </div>
                
                <!-- AJAX mesaj alanı -->
                <div id="booking-alert" style="display:none;"></div>
                
                <form class="booking-form" id="tourInquiryForm">
                    <input type="hidden" name="booking_type" value="tour">
                    <input type="hidden" name="tour_id" value="<?= (int)$tour['id'] ?>">
                    <input type="hidden" name="vehicle_id" id="selected_vehicle_id" value="">
                    <input type="hidden" name="vehicle_name" id="selected_vehicle_name_input" value="">
                    
                    <div class="row gy-3">
                        <!-- Row 1: Ad Soyad, E-posta, Telefon -->
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label"><?= $t['full_name'] ?> *</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="<?= $t['full_name_placeholder'] ?>">
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label"><?= $t['email'] ?> *</label>
                            <input type="email" name="email" class="form-control" required placeholder="<?= $t['email_placeholder'] ?>">
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label class="form-label"><?= $t['phone'] ?> *</label>
                            <input type="tel" name="phone" class="form-control" required placeholder="<?= $t['phone_placeholder'] ?>">
                        </div>
                        
                        <!-- Row 2: Alınış Yeri, Tarihi, Saati, Dönüş Saati -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label"><?= $t['pickup_location'] ?> *</label>
                            <input type="text" name="pickup_location" class="form-control" required placeholder="<?= $t['pickup_location_placeholder'] ?>">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label"><?= $t['pickup_date'] ?> *</label>
                            <input type="date" name="pickup_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label"><?= $t['pickup_time'] ?> *</label>
                            <input type="time" name="pickup_time" class="form-control" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label"><?= $t['return_time'] ?></label>
                            <input type="time" name="return_time" class="form-control">
                        </div>
                        
                        <!-- Row 3: Yetişkin Sayısı, Çocuk Sayısı, Çocuk Koltuğu -->
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label"><?= $t['adults_count'] ?> *</label>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn qty-minus" data-target="adults-input"><i class="bi bi-dash"></i></button>
                                <input type="number" name="adults" id="adults-input" class="form-control qty-input" value="1" min="1" max="16" readonly>
                                <button type="button" class="qty-btn qty-plus" data-target="adults-input"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label"><?= $t['children_count'] ?></label>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn qty-minus" data-target="children-input"><i class="bi bi-dash"></i></button>
                                <input type="number" name="children" id="children-input" class="form-control qty-input" value="0" min="0" max="16" readonly>
                                <button type="button" class="qty-btn qty-plus" data-target="children-input"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label"><?= $t['child_seat'] ?></label>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn qty-minus" data-target="child-seat-input"><i class="bi bi-dash"></i></button>
                                <input type="number" name="child_seat" id="child-seat-input" class="form-control qty-input" value="0" min="0" max="16" readonly>
                                <button type="button" class="qty-btn qty-plus" data-target="child-seat-input"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        
                        <!-- Varış Otel Adresi -->
                        <div class="col-12">
                            <label class="form-label"><?= $t['hotel_address'] ?> *</label>
                            <input type="text" name="hotel_address" class="form-control" required placeholder="<?= $t['hotel_placeholder'] ?>">
                        </div>
                        
                        <!-- Dönüş Transferi Checkbox -->
                        <div class="col-12">
                            <div class="form-check return-transfer-check">
                                <input type="checkbox" class="form-check-input" name="return_transfer" id="return-transfer-checkbox" value="1">
                                <label class="form-check-label" for="return-transfer-checkbox">
                                    <i class="bi bi-arrow-repeat me-1"></i> <?= $t['return_transfer'] ?>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Return Transfer Fields (Hidden by default) -->
                        <div id="return-transfer-fields" class="col-12" style="display: none;">
                            <div class="return-transfer-section">
                                <h5 class="return-section-title"><i class="bi bi-arrow-repeat me-2"></i><?= $t['return_transfer'] ?></h5>
                                <div class="row gy-3">
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label"><?= $t['return_flight_date'] ?> *</label>
                                        <input type="date" name="return_flight_date" class="form-control" min="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label"><?= $t['return_flight_time'] ?> *</label>
                                        <input type="time" name="return_flight_time" class="form-control">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label"><?= $t['return_flight_number'] ?></label>
                                        <input type="text" name="return_flight_number" class="form-control" placeholder="<?= $t['flight_number_placeholder'] ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label"><?= $t['return_pickup_time'] ?> *</label>
                                        <input type="time" name="return_pickup_time" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label"><?= $t['return_hotel_address'] ?> *</label>
                                        <input type="text" name="return_hotel_address" class="form-control" placeholder="<?= $t['return_hotel_placeholder'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notlar -->
                        <div class="col-12">
                            <label class="form-label"><?= $t['notes'] ?></label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="<?= $t['notes_placeholder'] ?>"></textarea>
                        </div>
                        
                        <!-- Submit -->
                        <div class="col-12 mt-4">
                            <div class="booking-footer">
                                <div class="booking-footer-right w-100 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg btn-submit-booking">
                                        <i class="bi bi-envelope me-2"></i><?= $t['send_inquiry'] ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>



    </div>
</section><!-- /Tour Details Section -->

<!-- destination-detail.css stillerini kullan -->
<link rel="stylesheet" href="<?= ASSETS_URL ?>css/pages/destination-detail.css">

<style>
/* Tour Details Additional Styles */
.tour-details .section-header {
    text-align: center;
    margin-bottom: 40px;
}

.tour-details .section-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--heading-color);
    margin-bottom: 10px;
}

.tour-details .section-header p {
    color: #666;
    font-size: 1.1rem;
}

.tour-overview {
    margin-bottom: 60px;
    padding: 40px;
    background: var(--surface-color);
    border-radius: 15px;
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
}

.tour-overview p {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #555;
}

.tour-name {
    font-size: 14px;
    color: #666;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleCards = document.querySelectorAll('.vehicle-select-card');
    const inquiryFormWrapper = document.getElementById('inquiry-form-wrapper');
    const selectedVehicleId = document.getElementById('selected_vehicle_id');
    const selectedVehicleName = document.getElementById('selected-vehicle-name');
    const selectedVehicleNameInput = document.getElementById('selected_vehicle_name_input');
    const adultsInput = document.getElementById('adults-input');
    const childrenInput = document.getElementById('children-input');
    const childSeatInput = document.getElementById('child-seat-input');
    const changeVehicleBtn = document.querySelector('.btn-change-vehicle');
    
    let currentMaxCapacity = 16;
    let currentChildSeatMax = 0;
    let currentPriceRaw = 0;
    let currentCurrencyCode = 'TRY';
    
    // Update max values based on total capacity
    function updateMaxValues() {
        const adults = parseInt(adultsInput?.value) || 1;
        const children = parseInt(childrenInput?.value) || 0;
        const childSeats = parseInt(childSeatInput?.value) || 0;
        
        const totalUsed = adults + children + childSeats;
        
        // Adults max
        if (adultsInput) {
            const maxAdults = currentMaxCapacity - children - childSeats;
            adultsInput.max = Math.max(1, maxAdults);
            if (parseInt(adultsInput.value) > maxAdults) {
                adultsInput.value = Math.max(1, maxAdults);
            }
        }
        
        // Children max
        if (childrenInput) {
            const maxChildren = currentMaxCapacity - adults - childSeats;
            childrenInput.max = Math.max(0, maxChildren);
            if (parseInt(childrenInput.value) > maxChildren) {
                childrenInput.value = Math.max(0, maxChildren);
            }
        }
        
        // Child seats max
        if (childSeatInput) {
            const maxChildSeats = Math.min(currentChildSeatMax, currentMaxCapacity - adults - children);
            childSeatInput.max = Math.max(0, maxChildSeats);
            if (parseInt(childSeatInput.value) > maxChildSeats) {
                childSeatInput.value = Math.max(0, maxChildSeats);
            }
        }
    }
    
    // Vehicle selection
    vehicleCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            vehicleCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked card
            this.classList.add('selected');
            
            // Get vehicle data
            const vehicleId = this.dataset.vehicleId;
            const vehicleName = this.dataset.vehicleName;
            const vehicleCapacity = parseInt(this.dataset.vehicleCapacity) || 16;
            const childSeatCapacity = parseInt(this.dataset.childSeatCapacity) || 0;
            currentPriceRaw = parseFloat(this.dataset.vehiclePriceRaw) || 0;
            currentCurrencyCode = this.dataset.vehicleCurrency || 'TRY';
            
            // Update hidden inputs and display
            selectedVehicleId.value = vehicleId;
            selectedVehicleName.textContent = vehicleName;
            if (selectedVehicleNameInput) {
                selectedVehicleNameInput.value = vehicleName;
            }
            
            // Update max capacity
            currentMaxCapacity = vehicleCapacity;
            currentChildSeatMax = childSeatCapacity;
            
            // Reset form values
            if (adultsInput) adultsInput.value = 1;
            if (childrenInput) childrenInput.value = 0;
            if (childSeatInput) childSeatInput.value = 0;
            
            updateMaxValues();
            
            // Show inquiry form
            inquiryFormWrapper.style.display = 'block';
            
            // Scroll to form
            setTimeout(() => {
                inquiryFormWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        });
    });
    
    // Change vehicle button
    if (changeVehicleBtn) {
        changeVehicleBtn.addEventListener('click', function() {
            vehicleCards.forEach(c => c.classList.remove('selected'));
            inquiryFormWrapper.style.display = 'none';
            selectedVehicleId.value = '';
            
            // Scroll to vehicle list
            document.getElementById('booking-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    
    // Return transfer checkbox toggle
    const returnCheckbox = document.getElementById('return-transfer-checkbox');
    const returnFields = document.getElementById('return-transfer-fields');
    if (returnCheckbox && returnFields) {
        returnCheckbox.addEventListener('change', function() {
            returnFields.style.display = this.checked ? 'block' : 'none';
            // Toggle required on return fields
            returnFields.querySelectorAll('input').forEach(inp => {
                if (inp.name !== 'return_flight_number') {
                    inp.required = this.checked;
                }
            });
        });
    }
    
    // Quantity selector for +/- buttons
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;
            
            let value = parseInt(input.value) || 0;
            const min = parseInt(input.min) || 0;
            const max = parseInt(input.max) || 16;
            
            if (this.classList.contains('qty-minus')) {
                if (value > min) {
                    input.value = value - 1;
                }
            } else if (this.classList.contains('qty-plus')) {
                if (value < max) {
                    input.value = value + 1;
                }
            }
            
            // Update max values after change
            updateMaxValues();
        });
    });
    
    // AJAX Form Submit
    const inquiryForm = document.getElementById('tourInquiryForm');
    const bookingAlert = document.getElementById('booking-alert');
    const API_URL = '<?= SITE_URL ?>/api/booking';
    
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Araç seçimi kontrolü
            if (!selectedVehicleId.value) {
                showAlert('warning', '<?= $lang == "tr" ? "Lütfen bir araç seçin." : ($lang == "de" ? "Bitte wählen Sie ein Fahrzeug." : "Please select a vehicle.") ?>');
                document.getElementById('booking-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            
            const submitBtn = inquiryForm.querySelector('.btn-submit-booking');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span><?= $lang == "tr" ? "Gönderiliyor..." : ($lang == "de" ? "Wird gesendet..." : "Sending...") ?>';
            
            const formData = new FormData(inquiryForm);
            const isReturnChecked = document.getElementById('return-transfer-checkbox')?.checked;
            const priceToSend = isReturnChecked ? (currentPriceRaw * 2) : currentPriceRaw;
            formData.append('total_price', priceToSend);
            formData.append('currency', currentCurrencyCode);
            
            fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let msg = data.message + '<br><strong><?= $lang == "tr" ? "Rezervasyon No" : "Booking No" ?>: ' + data.booking_number + '</strong>';
                    if (data.return_booking_number) {
                        msg += '<br><strong><?= $lang == "tr" ? "Dönüş Rezervasyon No" : "Return Booking No" ?>: ' + data.return_booking_number + '</strong>';
                    }
                    showAlert('success', msg);
                    inquiryForm.style.display = 'none';
                } else {
                    showAlert('danger', data.message || '<?= $t['booking_error'] ?>');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(err => {
                showAlert('danger', '<?= $t['booking_error'] ?>');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    function showAlert(type, message) {
        bookingAlert.style.display = 'block';
        bookingAlert.className = 'alert alert-' + type + ' d-flex align-items-center';
        const icons = { success: 'bi-check-circle-fill', danger: 'bi-exclamation-triangle-fill', warning: 'bi-exclamation-circle-fill' };
        bookingAlert.innerHTML = '<i class="bi ' + (icons[type] || 'bi-info-circle-fill') + ' me-2 fs-4"></i><div>' + message + '</div>';
        bookingAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
