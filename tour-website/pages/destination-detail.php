<?php
/**
 * Transfer Detay Sayfası
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . 'sections.php';

$slug = $_GET['slug'] ?? '';
$destination = getDestinationBySlug($slug);

if (!$destination) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $destination['meta_title'] ?: $destination['title'];
$metaDescription = $destination['meta_description'] ?: $destination['description'];
$bodyClass = 'destination-details-page';

$lang = getCurrentLang();
$db = getDB();

// Transferler listesi için dil bazlı slug al
$transfersSlug = 'transfers'; // varsayılan
$transfersTitle = '';
try {
    $slugStmt = $db->prepare("
        SELECT pst.slug, pst.title 
        FROM page_settings ps
        LEFT JOIN page_setting_translations pst ON ps.id = pst.page_setting_id AND pst.language_code = ?
        WHERE ps.page_key = 'destinations'
    ");
    $slugStmt->execute([$lang]);
    $slugRow = $slugStmt->fetch();
    if (!empty($slugRow['slug'])) {
        $transfersSlug = $slugRow['slug'];
    }
    if (!empty($slugRow['title'])) {
        $transfersTitle = $slugRow['title'];
    }
} catch (Exception $e) {}

// Bu transfer için araç fiyatlarını getir
$vehicleStmt = $db->prepare("
    SELECT v.*, dv.price, dv.currency
    FROM destination_vehicles dv
    JOIN vehicles v ON dv.vehicle_id = v.id
    WHERE dv.destination_id = ? AND dv.language_code = ? AND v.is_active = 1
    ORDER BY v.sort_order, dv.price ASC
");
$vehicleStmt->execute([$destination['id'], $lang]);
$vehicles = $vehicleStmt->fetchAll();

// Para birimi sembolleri
$currencySymbols = [
    'TRY' => '₺',
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£'
];

// Galeri görsellerini parse et
$gallery = [];
if (!empty($destination['gallery'])) {
    $gallery = json_decode($destination['gallery'], true) ?: [];
}

// Varsayılan metinler (fallback)
$defaultTexts = [
    'tr' => [
        'home' => 'Ana Sayfa', 'transfers' => 'Transferler',
        'available_vehicles' => 'Mevcut Araçlar', 'choose_vehicle' => 'Size uygun aracı seçin',
        'passengers' => 'Yolcu', 'luggage' => 'Bagaj', 'child_seats' => 'Çocuk Koltuğu', 'book_now' => 'Rezervasyon Yap',
        'transfer_features' => 'Transfer Özellikleri', 'what_we_offer' => 'Size sunduğumuz hizmetler',
        'vehicle_type' => 'Araç Tipi', 'continue_booking' => 'Rezervasyona Devam Et', 'change_vehicle' => 'Araç Değiştir',
        'full_name' => 'Ad Soyad', 'email' => 'E-posta', 'phone' => 'Telefon',
        'flight_date' => 'Uçuş İniş Tarihi', 'flight_time' => 'Uçuş İniş Saati', 'flight_number' => 'Uçuş Numarası',
        'adults_count' => 'Yetişkin Sayısı', 'children_count' => 'Çocuk Sayısı', 'child_seat' => 'Çocuk Koltuğu',
        'hotel_address' => 'Varış Otel Adı / Adresi', 'notes' => 'Notlar',
        'location' => 'Konum', 'transfer_route' => 'Transfer güzergahı',
        'gallery' => 'Galeri', 'gallery_desc' => 'Transfer hizmetimizden görüntüler',
        'full_name_placeholder' => 'Adınız ve soyadınız', 'email_placeholder' => 'E-posta adresiniz',
        'phone_placeholder' => '+90 5XX XXX XX XX', 'flight_number_placeholder' => 'Örn: TK1234',
        'hotel_placeholder' => 'Otel adı veya tam adres', 'notes_placeholder' => 'Varsa özel isteklerinizi yazın',
        'return_transfer' => 'Dönüş Transferi İstiyorum',
        'return_flight_date' => 'Dönüş Uçuş Tarihi', 'return_flight_time' => 'Dönüş Uçuş Saati',
        'return_flight_number' => 'Dönüş Uçuş Numarası', 'return_pickup_time' => 'Dönüş Alınış Saati',
        'return_hotel_address' => 'Dönüş İçin Alınacak Otel / Adres',
        'return_hotel_placeholder' => 'Dönüşte alınacak otel adı veya adres',
        'total_price' => 'Toplam Tutar',
        'terms_checkbox' => 'Sözleşme şartlarını okudum ve kabul ediyorum',
        'terms_title' => 'Kullanım Koşulları',
        'transfer_info_title' => 'Transfer Bilgileri',
    ],
    'en' => [
        'home' => 'Home', 'transfers' => 'Transfers',
        'available_vehicles' => 'Available Vehicles', 'choose_vehicle' => 'Choose the vehicle that suits you',
        'passengers' => 'Passengers', 'luggage' => 'Luggage', 'child_seats' => 'Child Seat', 'book_now' => 'Book Now',
        'transfer_features' => 'Transfer Features', 'what_we_offer' => 'Services we offer',
        'vehicle_type' => 'Vehicle Type', 'continue_booking' => 'Continue Booking', 'change_vehicle' => 'Change Vehicle',
        'full_name' => 'Full Name', 'email' => 'Email', 'phone' => 'Phone',
        'flight_date' => 'Flight Arrival Date', 'flight_time' => 'Flight Arrival Time', 'flight_number' => 'Flight Number',
        'adults_count' => 'Number of Adults', 'children_count' => 'Number of Children', 'child_seat' => 'Child Seat',
        'hotel_address' => 'Destination Hotel / Address', 'notes' => 'Notes',
        'location' => 'Location', 'transfer_route' => 'Transfer route',
        'gallery' => 'Gallery', 'gallery_desc' => 'Images from our transfer service',
        'full_name_placeholder' => 'Your full name', 'email_placeholder' => 'Your email address',
        'phone_placeholder' => '+1 XXX XXX XXXX', 'flight_number_placeholder' => 'e.g. TK1234',
        'hotel_placeholder' => 'Hotel name or full address', 'notes_placeholder' => 'Write your special requests if any',
        'return_transfer' => 'I Want Return Transfer',
        'return_flight_date' => 'Return Flight Date', 'return_flight_time' => 'Return Flight Time',
        'return_flight_number' => 'Return Flight Number', 'return_pickup_time' => 'Return Pickup Time',
        'return_hotel_address' => 'Return Pickup Hotel / Address',
        'return_hotel_placeholder' => 'Hotel name or address for return pickup',
        'total_price' => 'Total Price',
        'terms_checkbox' => 'I have read and accept the terms and conditions',
        'terms_title' => 'Terms and Conditions',
        'transfer_info_title' => 'Transfer Information',
    ],
    'de' => [
        'home' => 'Startseite', 'transfers' => 'Transfers',
        'available_vehicles' => 'Verfügbare Fahrzeuge', 'choose_vehicle' => 'Wählen Sie das passende Fahrzeug',
        'passengers' => 'Passagiere', 'luggage' => 'Gepäck', 'child_seats' => 'Kindersitz', 'book_now' => 'Jetzt Buchen',
        'transfer_features' => 'Transfer-Funktionen', 'what_we_offer' => 'Unsere Dienstleistungen',
        'vehicle_type' => 'Fahrzeugtyp', 'continue_booking' => 'Buchung Fortsetzen', 'change_vehicle' => 'Fahrzeug Ändern',
        'full_name' => 'Vollständiger Name', 'email' => 'E-Mail', 'phone' => 'Telefon',
        'flight_date' => 'Flug Ankunftsdatum', 'flight_time' => 'Flug Ankunftszeit', 'flight_number' => 'Flugnummer',
        'adults_count' => 'Anzahl Erwachsene', 'children_count' => 'Anzahl Kinder', 'child_seat' => 'Kindersitz',
        'hotel_address' => 'Zielhotel / Adresse', 'notes' => 'Notizen',
        'location' => 'Standort', 'transfer_route' => 'Transferroute',
        'gallery' => 'Galerie', 'gallery_desc' => 'Bilder von unserem Transferservice',
        'full_name_placeholder' => 'Ihr vollständiger Name', 'email_placeholder' => 'Ihre E-Mail-Adresse',
        'phone_placeholder' => '+49 XXX XXX XXXX', 'flight_number_placeholder' => 'z.B. TK1234',
        'hotel_placeholder' => 'Hotelname oder vollständige Adresse', 'notes_placeholder' => 'Schreiben Sie Ihre besonderen Wünsche',
        'return_transfer' => 'Ich möchte Rücktransfer',
        'return_flight_date' => 'Rückflugdatum', 'return_flight_time' => 'Rückflugzeit',
        'return_flight_number' => 'Rückflugnummer', 'return_pickup_time' => 'Rück-Abholzeit',
        'return_hotel_address' => 'Rück-Abholhotel / Adresse',
        'return_hotel_placeholder' => 'Hotelname oder Adresse für Rückabholung',
        'total_price' => 'Gesamtpreis',
        'terms_checkbox' => 'Ich habe die AGB gelesen und akzeptiere sie',
        'terms_title' => 'Allgemeine Geschäftsbedingungen',
        'transfer_info_title' => 'Transferinformationen',
    ],
];

// Veritabanından çevirileri al
$dbTexts = [];
try {
    $stmt = $db->prepare("SELECT * FROM transfer_detail_translations WHERE language_code = ?");
    $stmt->execute([$lang]);
    $dbTexts = $stmt->fetch() ?: [];
} catch (Exception $e) {}

// Sözleşme çevirilerini al
$termsData = [];
try {
    $stmt = $db->prepare("SELECT * FROM terms_translations WHERE language_code = ?");
    $stmt->execute([$lang]);
    $termsData = $stmt->fetch() ?: [];
} catch (Exception $e) {}

// Uyarı mesajını al
$alertData = [];
try {
    $stmt = $db->prepare("SELECT * FROM booking_alert_translations WHERE language_code = ?");
    $stmt->execute([$lang]);
    $alertData = $stmt->fetch() ?: [];
} catch (Exception $e) {}

// Varsayılan metinleri al
$t = $defaultTexts[$lang] ?? $defaultTexts['en'];

// Veritabanındaki değerlerle üzerine yaz (boş olmayanları)
foreach ($t as $key => $value) {
    if (!empty($dbTexts[$key])) {
        $t[$key] = $dbTexts[$key];
    }
}

// Transfer özelliklerini veritabanından al
$transferFeatures = [];
$featuresVisible = true;
try {
    // Görünürlük ayarını kontrol et
    $stmt = $db->prepare("SELECT features_visible FROM page_settings WHERE page_key = ?");
    $stmt->execute(['destinations']);
    $ps = $stmt->fetch();
    $featuresVisible = ($ps['features_visible'] ?? 1) == 1;
    
    if ($featuresVisible) {
        $stmt = $db->prepare("
            SELECT tf.id, tf.icon, tft.title, tft.description
            FROM transfer_features tf
            LEFT JOIN transfer_feature_translations tft ON tf.id = tft.feature_id AND tft.language_code = ?
            WHERE tf.is_active = 1
            ORDER BY tf.sort_order
        ");
        $stmt->execute([$lang]);
        $transferFeatures = $stmt->fetchAll();
    }
} catch (Exception $e) {}

require_once INCLUDES_PATH . 'header.php';
?>

<!-- Page Title -->
<div class="page-title dark-background" data-aos="fade" style="background-image: url(<?= !empty($destination['image']) ? getMediaUrl($destination['image']) : ASSETS_URL . 'img/page-title-bg.webp' ?>);">
    <div class="container position-relative">
        <h1><?= e($destination['title']) ?></h1>
        <?php if (!empty($destination['location'])): ?>
        <p><i class="bi bi-geo-alt"></i> <?= e($destination['location']) ?></p>
        <?php endif; ?>
        <?php if (!empty($destination['description'])): ?>
        <p><?= e($destination['description']) ?></p>
        <?php endif; ?>
        <nav class="breadcrumbs">
            <ol>
                <li><a href="<?= langUrl('') ?>"><?= $t['home'] ?></a></li>
                <li><a href="<?= langUrl($transfersSlug) ?>"><?= !empty($transfersTitle) ? e($transfersTitle) : $t['transfers'] ?></a></li>
                <li class="current"><?= e($destination['title']) ?></li>
            </ol>
        </nav>
    </div>
</div><!-- End Page Title -->

<!-- Transfer Details Section -->
<section id="travel-destination-details" class="travel-destination-details section">
    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <!-- Overview Section -->
        <?php if (!empty($destination['content']) || !empty($destination['description'])): ?>
        <div class="destination-overview" data-aos="fade-up" data-aos-delay="200">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <?php if (!empty($destination['content'])): ?>
                    <?= $destination['content'] ?>
                    <?php elseif (!empty($destination['description'])): ?>
                    <p><?= nl2br(e($destination['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Vehicle Selection & Booking Section -->
        <?php 
        // Araç içi hizmetleri tek sorguda al (N+1 query problemi çözüldü)
        $availableServices = [];
        $currentLang = extractLangFromUrl() ?: (is_string($lang) ? $lang : 'tr');
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
            $serviceStmt->execute([$currentLang]);
            $allServices = $serviceStmt->fetchAll();
            
            foreach ($allServices as $svc) {
                $availableServices[$svc['id']] = [
                    'icon' => $svc['icon'],
                    'label' => $svc['name']
                ];
            }
        } catch (Exception $e) {}
        ?>
        <?php if (!empty($vehicles)): ?>
        <div id="booking-section" class="booking-section" data-aos="fade-up" data-aos-delay="300">
            <div class="section-header">
                <h2><?= e($destination['title']) ?> - <?= $t['available_vehicles'] ?></h2>
                <p><?= $t['choose_vehicle'] ?></p>
            </div>
            
            <!-- Vehicle Selection Cards -->
            <div class="vehicle-selection-list">
                <?php foreach ($vehicles as $index => $vehicle): 
                    $currencySymbol = $currencySymbols[$vehicle['currency']] ?? $vehicle['currency'];
                    $services = !empty($vehicle['services']) ? json_decode($vehicle['services'], true) : [];
                ?>
                <div class="vehicle-select-card" data-vehicle-id="<?= (int)$vehicle['id'] ?>" 
                     data-vehicle-name="<?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?>"
                     data-vehicle-price="<?= $currencySymbol ?><?= number_format($vehicle['price'], 0) ?>"
                     data-vehicle-capacity="<?= (int)$vehicle['capacity'] ?>"
                     data-child-seat-capacity="<?= (int)($vehicle['child_seat_capacity'] ?? 0) ?>"
                     data-aos="fade-up" data-aos-delay="<?= 100 + ($index * 50) ?>">
                    <div class="vehicle-select-inner">
                        <div class="vehicle-select-image">
                            <?php if (!empty($vehicle['image'])): ?>
                            <img src="<?= UPLOADS_URL . e($vehicle['image']) ?>" alt="<?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?>" loading="lazy">
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
                                    // Hem string hem int key'leri destekle
                                    $serviceKey = is_numeric($service) ? (int)$service : $service;
                                    $serviceInfo = $availableServices[$serviceKey] ?? null;
                                    if ($serviceInfo && !empty($serviceInfo['label'])):
                                ?>
                                <span class="service-tag"><i class="bi <?= e($serviceInfo['icon']) ?>"></i> <?= e($serviceInfo['label']) ?></span>
                                <?php endif; endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="vehicle-select-price">
                            <div class="price-tag"><?= $currencySymbol ?><?= number_format($vehicle['price'], 0) ?></div>
                            <button type="button" class="btn-select-vehicle">
                                <i class="bi bi-check-lg"></i> <?= $t['book_now'] ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Booking Form (Hidden by default) -->
            <div id="booking-form-wrapper" class="booking-form-wrapper" style="display: none;">
                <div class="transfer-info-header">
                    <div class="transfer-info-left">
                        <h5 class="transfer-info-title"><?= $t['transfer_info_title'] ?></h5>
                        <?php if (!empty($destination['from_location']) || !empty($destination['to_location'])): ?>
                        <div class="transfer-route-info">
                            <?php if (!empty($destination['from_location'])): ?>
                            <span class="route-point from"><i class="bi bi-geo-alt text-success"></i> <?= e($destination['from_location']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($destination['from_location']) && !empty($destination['to_location'])): ?>
                            <span class="route-arrow"><i class="bi bi-arrow-right"></i></span>
                            <?php endif; ?>
                            <?php if (!empty($destination['to_location'])): ?>
                            <span class="route-point to"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e($destination['to_location']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="transfer-info-right">
                        <div class="selected-vehicle-compact">
                            <span id="selected-vehicle-name" class="selected-name"></span>
                            <span id="selected-vehicle-price" class="selected-price"></span>
                        </div>
                        <button type="button" class="btn-change-vehicle"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                </div>
                
                <form action="<?= langUrl('booking') ?>" method="GET" class="booking-form" id="reservationForm">
                    <input type="hidden" name="transfer_id" value="<?= (int)$destination['id'] ?>">
                    <input type="hidden" name="vehicle_id" id="selected_vehicle_id" value="">
                    
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
                        
                        <!-- Row 2: Uçuş Tarihi, Saati, Numarası -->
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label"><?= $t['flight_date'] ?> *</label>
                            <input type="date" name="flight_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label"><?= $t['flight_time'] ?> *</label>
                            <input type="time" name="flight_time" class="form-control" required>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label class="form-label"><?= $t['flight_number'] ?></label>
                            <input type="text" name="flight_number" class="form-control" placeholder="<?= $t['flight_number_placeholder'] ?>">
                        </div>
                        
                        <!-- Row 3: Yetişkin Sayısı, Çocuk Sayısı, Çocuk Koltuğu -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label"><?= $t['adults_count'] ?> *</label>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn qty-minus" data-target="passengers-input"><i class="bi bi-dash"></i></button>
                                <input type="number" name="passengers" id="passengers-input" class="form-control qty-input" value="1" min="1" max="16" readonly>
                                <button type="button" class="qty-btn qty-plus" data-target="passengers-input"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6" id="children-count-wrapper" style="display: none;">
                            <label class="form-label"><?= $t['children_count'] ?></label>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn qty-minus" data-target="children-input"><i class="bi bi-dash"></i></button>
                                <input type="number" name="children" id="children-input" class="form-control qty-input" value="0" min="0" max="0" readonly>
                                <button type="button" class="qty-btn qty-plus" data-target="children-input"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6" id="child-seat-wrapper" style="display: none;">
                            <label class="form-label"><?= $t['child_seat'] ?></label>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn qty-minus" data-target="child-seat-input"><i class="bi bi-dash"></i></button>
                                <input type="number" name="child_seat" id="child-seat-input" class="form-control qty-input" value="0" min="0" max="0" readonly>
                                <button type="button" class="qty-btn qty-plus" data-target="child-seat-input"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 d-flex align-items-end">
                            <div class="form-check return-transfer-check">
                                <input type="checkbox" class="form-check-input" name="return_transfer" id="return-transfer-checkbox" value="1">
                                <label class="form-check-label" for="return-transfer-checkbox">
                                    <i class="bi bi-arrow-repeat me-1"></i> <?= $t['return_transfer'] ?>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Row 4: Otel Adresi -->
                        <div class="col-12">
                            <label class="form-label"><?= $t['hotel_address'] ?> *</label>
                            <input type="text" name="hotel_address" class="form-control" required placeholder="<?= $t['hotel_placeholder'] ?>">
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
                        
                        <!-- Row 5: Notlar -->
                        <div class="col-12">
                            <label class="form-label"><?= $t['notes'] ?></label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="<?= $t['notes_placeholder'] ?>"></textarea>
                        </div>
                        
                        <!-- Alert Message -->
                        <?php if (!empty($alertData['message']) && ($alertData['is_active'] ?? 1)): ?>
                        <div class="col-12">
                            <div class="booking-alert alert-<?= e($alertData['color'] ?? 'warning') ?>">
                                <i class="bi <?= e($alertData['icon'] ?? 'bi-exclamation-triangle') ?>"></i>
                                <span><?= e($alertData['message']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Terms & Submit Section -->
                        <div class="col-12 mt-4">
                            <div class="booking-footer">
                                <div class="booking-footer-left">
                                    <div class="total-price-section">
                                        <span class="total-label"><?= $t['total_price'] ?>:</span>
                                        <span id="total-price-display" class="total-amount">₺0</span>
                                    </div>
                                    <?php 
                                    $checkboxText = !empty($termsData['checkbox_text']) ? $termsData['checkbox_text'] : $t['terms_checkbox'];
                                    ?>
                                    <div class="terms-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input" id="terms-checkbox" required>
                                        <label class="form-check-label" for="terms-checkbox">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal"><?= e($checkboxText) ?></a>
                                        </label>
                                    </div>
                                </div>
                                <div class="booking-footer-right">
                                    <button type="submit" class="btn btn-primary btn-lg btn-submit-booking" id="submit-booking-btn" disabled>
                                        <i class="bi bi-calendar-check me-2"></i><?= $t['continue_booking'] ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Terms Modal -->
        <?php 
        $termsTitle = !empty($termsData['title']) ? $termsData['title'] : $t['terms_title'];
        $termsCheckboxText = !empty($termsData['checkbox_text']) ? $termsData['checkbox_text'] : $t['terms_checkbox'];
        $termsContent = !empty($termsData['content']) ? nl2br(e($termsData['content'])) : '<p>Sözleşme içeriği henüz eklenmemiş.</p>';
        ?>
        <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termsModalLabel"><?= e($termsTitle) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?= $termsContent ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?= $lang == 'tr' ? 'Tamam' : ($lang == 'de' ? 'OK' : 'OK') ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Vehicle Features Section -->
        <?php if (!empty($transferFeatures)): ?>
        <div class="practical-info mt-5 pt-4" data-aos="fade-up" data-aos-delay="400">
            <div class="section-header">
                <h2><?= $t['transfer_features'] ?></h2>
                <p><?= $t['what_we_offer'] ?></p>
            </div>
            <div class="row gy-4">
                <?php 
                $featureCount = count($transferFeatures);
                $colClass = $featureCount <= 3 ? 'col-lg-4' : 'col-lg-3';
                $delay = 100;
                foreach ($transferFeatures as $feature): 
                    if (empty($feature['title'])) continue;
                ?>
                <div class="<?= $colClass ?> col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi <?= e($feature['icon'] ?: 'bi-check-circle') ?>"></i>
                        </div>
                        <h4><?= e($feature['title']) ?></h4>
                        <?php if (!empty($feature['description'])): ?>
                        <p><?= e($feature['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                $delay += 100;
                endforeach; 
                ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Map Section -->
        <?php if (!empty($destination['latitude']) && !empty($destination['longitude'])): ?>
        <div class="map-section" data-aos="fade-up" data-aos-delay="600">
            <div class="section-header">
                <h2><?= $t['location'] ?></h2>
                <p><?= $t['transfer_route'] ?></p>
            </div>
            <div class="map-container">
                <div class="map-embed">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d50000!2d<?= e($destination['longitude']) ?>!3d<?= e($destination['latitude']) ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zLocation!5e0!3m2!1sen!2str" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <?php if (!empty($destination['location'])): ?>
                <div class="map-points">
                    <div class="point-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><?= e($destination['location']) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Gallery Section -->
        <?php if (!empty($gallery)): ?>
        <div class="gallery-section" data-aos="fade-up" data-aos-delay="700">
            <div class="section-header">
                <h2><?= $t['gallery'] ?></h2>
                <p><?= $t['gallery_desc'] ?></p>
            </div>
            <div class="gallery-slider swiper init-swiper">
                <script type="application/json" class="swiper-config">
                {
                    "loop": true,
                    "speed": 600,
                    "autoplay": {
                        "delay": 4000
                    },
                    "slidesPerView": 1,
                    "breakpoints": {
                        "768": {
                            "slidesPerView": 2
                        },
                        "992": {
                            "slidesPerView": 3
                        }
                    },
                    "pagination": {
                        "el": ".swiper-pagination",
                        "type": "bullets",
                        "clickable": true
                    }
                }
                </script>
                <div class="swiper-wrapper">
                    <?php foreach ($gallery as $image): ?>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <a href="<?= UPLOADS_URL . e($image) ?>" class="glightbox">
                                <img src="<?= UPLOADS_URL . e($image) ?>" alt="<?= e($destination['title']) ?>" class="img-fluid" loading="lazy">
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section><!-- /Transfer Details Section -->

<link rel="stylesheet" href="<?= ASSETS_URL ?>css/pages/destination-detail.css">


<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleCards = document.querySelectorAll('.vehicle-select-card');
    const bookingFormWrapper = document.getElementById('booking-form-wrapper');
    const selectedVehicleId = document.getElementById('selected_vehicle_id');
    const selectedVehicleName = document.getElementById('selected-vehicle-name');
    const selectedVehiclePrice = document.getElementById('selected-vehicle-price');
    const passengersInput = document.getElementById('passengers-input');
    const changeVehicleBtn = document.querySelector('.btn-change-vehicle');
    
    let currentMaxCapacity = 16;
    let currentChildSeatMax = 0;
    let currentBasePrice = '';
    let currentPriceNumeric = 0;
    let currentCurrencySymbol = '';
    
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
            const vehiclePrice = this.dataset.vehiclePrice;
            const vehicleCapacity = parseInt(this.dataset.vehicleCapacity) || 16;
            
            // Store base price for return transfer calculation
            currentBasePrice = vehiclePrice;
            // Extract numeric value and currency symbol
            const priceMatch = vehiclePrice.match(/([^\d]*)(\d[\d,.]*)/);
            if (priceMatch) {
                currentCurrencySymbol = priceMatch[1];
                currentPriceNumeric = parseFloat(priceMatch[2].replace(/,/g, ''));
            }
            
            // Update hidden input and display
            selectedVehicleId.value = vehicleId;
            selectedVehicleName.textContent = vehicleName;
            selectedVehiclePrice.textContent = vehiclePrice;
            
            // Reset return transfer checkbox and update price
            const returnCheckbox = document.getElementById('return-transfer-checkbox');
            if (returnCheckbox) {
                returnCheckbox.checked = false;
                document.getElementById('return-transfer-fields').style.display = 'none';
            }
            
            // Reset terms checkbox
            const termsCheckbox = document.getElementById('terms-checkbox');
            const submitBtn = document.getElementById('submit-booking-btn');
            if (termsCheckbox) {
                termsCheckbox.checked = false;
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            
            // Update total price display
            const totalPriceDisplay = document.getElementById('total-price-display');
            if (totalPriceDisplay) {
                totalPriceDisplay.textContent = vehiclePrice;
            }
            
            // Update max capacity for passengers
            currentMaxCapacity = vehicleCapacity;
            
            // Child seat capacity
            currentChildSeatMax = parseInt(card.dataset.childSeatCapacity) || 0;
            const childSeatWrapper = document.getElementById('child-seat-wrapper');
            const childSeatInput = document.getElementById('child-seat-input');
            const childrenCountWrapper = document.getElementById('children-count-wrapper');
            const childrenInput = document.getElementById('children-input');
            
            // Show children count and child seat fields
            if (currentMaxCapacity > 1) {
                childrenCountWrapper.style.display = 'block';
                childrenInput.value = 0;
            } else {
                childrenCountWrapper.style.display = 'none';
                childrenInput.value = 0;
            }
            
            // Show/hide child seat based on capacity
            if (currentChildSeatMax > 0) {
                childSeatWrapper.style.display = 'block';
                childSeatInput.value = 0;
            } else {
                childSeatWrapper.style.display = 'none';
                childSeatInput.value = 0;
            }
            
            // Reset passengers
            passengersInput.value = 1;
            updateMaxValues();
            
            // Show booking form
            bookingFormWrapper.style.display = 'block';
            
            // Scroll to form
            setTimeout(() => {
                bookingFormWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        });
    });
    
    // Change vehicle button
    if (changeVehicleBtn) {
        changeVehicleBtn.addEventListener('click', function() {
            vehicleCards.forEach(c => c.classList.remove('selected'));
            bookingFormWrapper.style.display = 'none';
            selectedVehicleId.value = '';
            
            // Scroll to vehicle list
            document.getElementById('booking-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    
    // Update max values based on total capacity
    // Total = adults + children + child_seats <= maxCapacity
    function updateMaxValues() {
        const childSeatInput = document.getElementById('child-seat-input');
        const childrenInput = document.getElementById('children-input');
        
        const adults = parseInt(passengersInput.value) || 1;
        const children = parseInt(childrenInput?.value) || 0;
        const childSeats = parseInt(childSeatInput?.value) || 0;
        
        const totalUsed = adults + children + childSeats;
        const remaining = currentMaxCapacity - totalUsed;
        
        // Adults max = total capacity - (children + childSeats)
        const maxAdults = currentMaxCapacity - children - childSeats;
        passengersInput.max = Math.max(1, maxAdults);
        
        // Children max = total capacity - (adults + childSeats)
        if (childrenInput) {
            const maxChildren = currentMaxCapacity - adults - childSeats;
            childrenInput.max = Math.max(0, maxChildren);
            
            // If current value exceeds new max, reduce it
            if (parseInt(childrenInput.value) > maxChildren) {
                childrenInput.value = Math.max(0, maxChildren);
            }
        }
        
        // Child seats max = min(original child seat capacity, total capacity - adults - children)
        if (childSeatInput) {
            const maxChildSeats = Math.min(currentChildSeatMax, currentMaxCapacity - adults - children);
            childSeatInput.max = Math.max(0, maxChildSeats);
            
            // If current value exceeds new max, reduce it
            if (parseInt(childSeatInput.value) > maxChildSeats) {
                childSeatInput.value = Math.max(0, maxChildSeats);
            }
        }
        
        // Also check if adults exceed new max
        if (parseInt(passengersInput.value) > maxAdults) {
            passengersInput.value = Math.max(1, maxAdults);
        }
    }
    
    // Quantity selector for all +/- buttons
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
    
    // Return Transfer Toggle
    const returnTransferCheckbox = document.getElementById('return-transfer-checkbox');
    const returnTransferFields = document.getElementById('return-transfer-fields');
    
    // Function to update price based on return transfer
    const totalPriceDisplay = document.getElementById('total-price-display');
    
    function updatePriceDisplay() {
        if (!currentPriceNumeric) return;
        
        const isReturnChecked = returnTransferCheckbox && returnTransferCheckbox.checked;
        const multiplier = isReturnChecked ? 2 : 1;
        const newPrice = currentPriceNumeric * multiplier;
        const formattedPrice = currentCurrencySymbol + newPrice.toLocaleString('tr-TR');
        
        // Update both header price and total price
        selectedVehiclePrice.textContent = formattedPrice;
        if (totalPriceDisplay) {
            totalPriceDisplay.textContent = formattedPrice;
        }
    }
    
    if (returnTransferCheckbox && returnTransferFields) {
        returnTransferCheckbox.addEventListener('change', function() {
            if (this.checked) {
                returnTransferFields.style.display = 'block';
                // Make return fields required
                returnTransferFields.querySelectorAll('input[type="date"], input[type="time"]').forEach(input => {
                    if (input.name !== 'return_flight_number') {
                        input.required = true;
                    }
                });
                returnTransferFields.querySelector('input[name="return_hotel_address"]').required = true;
            } else {
                returnTransferFields.style.display = 'none';
                // Remove required from return fields
                returnTransferFields.querySelectorAll('input').forEach(input => {
                    input.required = false;
                    input.value = '';
                });
            }
            
            // Update price display
            updatePriceDisplay();
        });
    }
    
    // Terms checkbox - enable/disable submit button
    const termsCheckbox = document.getElementById('terms-checkbox');
    const submitBtn = document.getElementById('submit-booking-btn');
    
    if (termsCheckbox && submitBtn) {
        termsCheckbox.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
        });
    }
    
    // Form validation
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            if (!selectedVehicleId.value) {
                e.preventDefault();
                alert('<?= $lang == "tr" ? "Lütfen bir araç seçin" : "Please select a vehicle" ?>');
                document.getElementById('booking-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
});
</script>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>
