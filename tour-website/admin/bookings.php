<?php
/**
 * Rezervasyon Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$defaultView = defined('BOOKINGS_AS_DASHBOARD') ? 'daily' : 'arrival';
$view = $_GET['view'] ?? $defaultView;
if (!in_array($view, ['arrival', 'return', 'all', 'daily', 'inter_transfer'])) $view = $defaultView;

$viewTitleMap = [
    'return'         => 'Depar Rezervasyonları',
    'all'            => 'Tüm Rezervasyonlar',
    'daily'          => 'Günlük Görünüm',
    'inter_transfer' => 'Ara Transferler'
];
$viewTitle = $viewTitleMap[$view] ?? 'Arrival Rezervasyonları';

$statusLabels = [
    'pending'   => ['Onay Bekliyor', 'warning', 'bi-hourglass-split'],
    'confirmed' => ['Onaylandı',     'success', 'bi-check-circle'],
    'cancelled' => ['İptal Edildi',  'danger',  'bi-x-circle'],
];

$directionLabels = [
    'outbound'       => ['Arrival', 'primary'],
    'return'         => ['Depar', 'info'],
    'inter_transfer' => ['Ara Transfer', 'danger']
];

// WHERE koşulu view'a göre
if ($view === 'return') {
    $dirWhere = "AND COALESCE(b.booking_direction,'outbound') = 'return'";
} elseif ($view === 'all' || $view === 'daily') {
    $dirWhere = '';
} elseif ($view === 'inter_transfer') {
    $dirWhere = "AND 1=0"; // Standart bookings tablosundan çekme
} else {
    $dirWhere = "AND COALESCE(b.booking_direction,'outbound') = 'outbound'";
}

// Onay bekleyen rezervasyonlar
$pendingBookings = $db->query("
    SELECT b.*,
           t.title AS tour_title,
           d.title AS destination_title,
           CONCAT(v.brand,' ',v.model) AS vehicle_name,
           COALESCE(v.capacity, 20) AS vehicle_capacity
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    LEFT JOIN destinations d ON b.destination_id = d.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.booking_status = 'pending' AND b.is_deleted = 0 $dirWhere
    ORDER BY b.created_at DESC
")->fetchAll();

// Tüm rezervasyonlar
$allBookings = $db->query("
    SELECT b.*,
           t.title AS tour_title,
           d.title AS destination_title,
           CONCAT(v.brand,' ',v.model) AS vehicle_name,
           COALESCE(v.capacity, 20) AS vehicle_capacity
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    LEFT JOIN destinations d ON b.destination_id = d.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.is_deleted = 0 $dirWhere
    ORDER BY b.created_at DESC
")->fetchAll();

// Ara Transferler (inter_hotel_transfers) verilerini ekle
if ($view === 'inter_transfer' || $view === 'all' || $view === 'daily') {
    try {
        $interList = $db->query("
            SELECT b.id, b.booking_number, 'transfer' AS booking_type, b.booking_status, 'inter_transfer' AS booking_direction,
                   b.destination_id, b.vehicle_id, b.customer_name, b.customer_email, b.customer_phone,
                   b.pickup_hotel_name AS pickup_location, b.pickup_date, b.pickup_time,
                   b.destination_hotel_name AS hotel_address,
                   NULL AS flight_date, NULL AS flight_time, NULL AS flight_number,
                   b.adults, b.children, b.child_seat, b.booster, b.total_price, b.currency, b.notes, b.admin_notes,
                   b.is_completed, b.is_outsourced, b.outsource_price, b.outsource_currency, b.created_at, b.updated_at,
                   b.outsource_name, b.outsource_pickup_time, b.outsource_partner_id, b.customer_id,
                   1 AS is_inter_transfer,
                   d.title AS destination_title,
                   CONCAT(v.brand,' ',v.model) AS vehicle_name,
                   COALESCE(v.capacity, 20) AS vehicle_capacity
            FROM inter_hotel_transfers b
            LEFT JOIN destinations d ON b.destination_id = d.id
            LEFT JOIN vehicles v ON b.vehicle_id = v.id
            WHERE b.is_deleted = 0
            ORDER BY b.created_at DESC
        ")->fetchAll();

        if (!empty($interList)) {
            $allBookings = array_merge($allBookings, $interList);
            usort($allBookings, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }
    } catch (Exception $e) {}
}

// Havalimanları (yeni rezervasyon & düzenleme modalı için)
$airports = [];
try {
    $airports = $db->query("SELECT * FROM airports WHERE is_deleted = 0 ORDER BY is_default DESC, name ASC")->fetchAll();
} catch (Exception $e) {}

$defaultAirportId = 0;
foreach ($airports as $ap) {
    if (!empty($ap['is_default'])) {
        $defaultAirportId = (int)$ap['id'];
        break;
    }
}
if (!$defaultAirportId && !empty($airports)) {
    $defaultAirportId = (int)$airports[0]['id'];
}

// Transferler (yeni rezervasyon modalı için)
$destinations = $db->query("
    SELECT d.id, COALESCE(dt.title, d.title) AS title
    FROM destinations d
    LEFT JOIN destination_translations dt ON d.id = dt.destination_id AND dt.language_code = 'tr'
    WHERE d.status = 'published'
    ORDER BY d.sort_order, d.title
")->fetchAll();

// Tüm aktif araçlar (transfere bağlı değil)
$allVehicles = $db->query("
    SELECT id, CONCAT(brand,' ',model) AS vehicle_name, capacity
    FROM vehicles
    WHERE is_active = 1
    ORDER BY sort_order, brand, model
")->fetchAll();

// Aktif oteller (otel adresi select için)
$hotelOptions = $db->query("
    SELECT name, address, distance_km
    FROM hotels
    WHERE is_active = 1
    ORDER BY name ASC
")->fetchAll();

// Listede "Bölge" sütunu: otel adı gösterilmez, yalnız bölge (hotels.address).
// Kayıt "OTEL — BÖLGE" ise bölge oradan; yalnız otel adı yazılmışsa Oteller listesinden bulunur; yoksa boş.
function rzBolgeAnahtar($s) {
    $s = str_replace(['İ', 'I', 'ı'], 'i', trim((string)$s));
    $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
    return preg_replace('/\s+/u', ' ', $s);
}
function rzBolge($adres, $otelBolge) {
    $adres = trim((string)$adres);
    if ($adres === '') return '';
    if (strpos($adres, '—') !== false) {
        $parcalar = explode('—', $adres);
        return trim(end($parcalar));
    }
    $k = rzBolgeAnahtar($adres);
    return isset($otelBolge[$k]) ? (string)$otelBolge[$k] : '';
}
$otelBolge = [];
foreach ($db->query("SELECT name, address FROM hotels WHERE address IS NOT NULL AND TRIM(address) <> ''")->fetchAll() as $ob) {
    $k = rzBolgeAnahtar($ob['name']);
    if ($k === '') continue;
    $bolgeAd = trim($ob['address']);
    if (array_key_exists($k, $otelBolge) && $otelBolge[$k] !== $bolgeAd) {
        $otelBolge[$k] = null; // aynı adlı otel farklı bölgelerde → belirsiz, boş göster
    } elseif (!array_key_exists($k, $otelBolge)) {
        $otelBolge[$k] = $bolgeAd;
    }
}

// Tüm Rezervasyonlar: arrival-depar çiftlerini eşleştir
$tripGroups = [];
if ($view === 'all') {
    foreach ($allBookings as $b) {
        $dir  = $b['booking_direction'] ?? 'outbound';
        $slot = ($dir === 'return') ? 'ret' : 'out';
        $key  = strtolower(trim($b['customer_name'] ?? '')) . '|'
              . (int)($b['destination_id'] ?? 0) . '|'
              . (int)($b['vehicle_id'] ?? 0) . '|'
              . date('Y-m-d', strtotime($b['created_at']));
        if (!isset($tripGroups[$key])) {
            $tripGroups[$key] = ['out' => null, 'ret' => null];
        }
        if ($tripGroups[$key][$slot] === null) {
            $tripGroups[$key][$slot] = $b;
        } else {
            // Aynı yönde ikinci rezervasyon: bağımsız satır
            $tripGroups[$key . '|' . $b['id']] = [
                'out' => $slot === 'out' ? $b : null,
                'ret' => $slot === 'ret' ? $b : null,
            ];
        }
    }
}

if (!isset($pageTitle)) $pageTitle = $viewTitle;
require_once __DIR__ . '/includes/header.php';
?>

<style>
.pending-card {
    border-left: 4px solid #ffc107;
    transition: all .2s;
    cursor: pointer;
}
.pending-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,.1);
    transform: translateY(-2px);
}
.pending-card .booking-number  { font-weight:700; color:#4e73df; }
.pending-card .booking-meta    { font-size:.85rem; color:#858796; }
.pending-card .booking-customer{ font-weight:600; }
.pending-count-badge { background:#ffc107; color:#000; font-size:1rem; padding:.35em .65em; }
.booking-detail-label { font-weight:600; color:#5a5c69; font-size:.85rem; text-transform:uppercase; letter-spacing:.5px; }
.booking-detail-value { font-size:1rem; margin-bottom:.75rem; }
.modal-booking .modal-header { background:linear-gradient(135deg,#4e73df,#224abe); color:#fff; }
.modal-booking .modal-header .btn-close { filter:brightness(0) invert(1); }
.status-select-group .btn { font-size:.85rem; padding:.35rem .75rem; }
#bookingModal .modal-body { max-height:75vh; overflow-y:auto; }

/* Mobilde Rezervasyon Ekle/Düzenle Modallarını Tam Ekran Yap ve Butonları Alta Yasla */
@media (max-width: 575.98px) {
    .modal-booking.modal {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .modal-booking .modal-dialog {
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        height: 100vh !important;
        max-height: 100vh !important;
    }
    .modal-booking .modal-content {
        height: 100% !important;
        max-height: 100% !important;
        border-radius: 0 !important;
        border: none !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .modal-booking .modal-body {
        flex: 1 1 auto !important;
        max-height: calc(100vh - 125px) !important;
        overflow-y: auto !important;
    }
    .modal-booking .modal-footer {
        margin-top: auto !important;
        border-radius: 0 !important;
        background-color: var(--bs-modal-bg, #fff) !important;
        padding: 0.75rem 1rem !important;
        border-top: 1px solid #dee2e6 !important;
    }
}
.return-section { background:#f0f7ff; border:1px solid #b8d4f0; border-radius:.5rem; padding:1rem 1.25rem; }
.view-tab.active { font-weight:600; }

/* Ops cell compact */
.ops-price-input { width: 60px !important; font-size: .72rem !important; padding: 1px 4px !important; height: auto !important; }
.ops-name-display { font-size: .72rem !important; line-height: 1.3; }

/* Passenger steppers */
.pax-stepper { width: 130px; }
.pax-stepper .form-control { max-width: 44px; min-width: 44px; text-align: center; padding: 0; font-weight: 600; }
.pax-stepper .btn { padding: 0.375rem 0.5rem; }
.passenger-name-group .passenger-name-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .4rem; }
.passenger-name-group .passenger-name-row input { flex: 1; }
.passenger-name-group h6 { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #858796; margin-bottom: .5rem; }

.row-completed td {
    background-color: #00ff3c86 !important; /* Daha belirgin yeşil */
    transition: background-color 0.3s ease;
}
.row-completed:hover td {
    background-color: #00ff3c9b !important;
}

.customer-autocomplete-wrap {
    position: relative;
}
.customer-autocomplete-wrap .form-control[readonly],
#modal-customer-email[readonly],
#add-customer-email[readonly] {
    background-color: #fff !important;
    opacity: 1;
}
.customer-autocomplete-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    max-height: 200px;
    overflow-y: auto;
    margin-top: 2px;
}
.customer-autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f1f1f1;
    font-size: 0.9rem;
    text-align: left;
}
.customer-autocomplete-item:last-child {
    border-bottom: none;
}
.customer-autocomplete-item:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}
</style>

<!-- Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <div id="ajaxToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold" id="ajaxToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Başlık + View Seçimi -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">
        <i class="bi bi-calendar-check me-2"></i><?= $viewTitle ?>
    </h1>
    <div class="d-flex gap-2 align-items-center">
        <?php if (count($pendingBookings) > 0): ?>
        <span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split me-1"></i><?= count($pendingBookings) ?> Bekleyen</span>
        <?php endif; ?>
        <span class="badge bg-secondary fs-6"><i class="bi bi-list me-1"></i><?= count($allBookings) ?> Toplam</span>
        <button type="button" class="btn btn-info text-white btn-sm" data-bs-toggle="modal" data-bs-target="#addInterTransferModal">
            <i class="bi bi-arrows-expand me-1"></i>+ Ara Transfer
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBookingModal">
            <i class="bi bi-plus-lg me-1"></i>Yeni Rezervasyon
        </button>
    </div>
</div>

<!-- View Tabları -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'daily' ? 'active' : '' ?>" href="?view=daily">
            <i class="bi bi-calendar-day me-1"></i>Günlük Görünüm
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'all' ? 'active' : '' ?>" href="?view=all">
            <i class="bi bi-list-ul me-1"></i>Tüm Rezervasyonlar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'arrival' ? 'active' : '' ?>" href="?view=arrival">
            <i class="bi bi-box-arrow-in-down-right me-1"></i>Arrival Rezervasyonları
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'return' ? 'active' : '' ?>" href="?view=return">
            <i class="bi bi-box-arrow-up-right me-1"></i>Depar Rezervasyonları
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'inter_transfer' ? 'active' : '' ?>" href="?view=inter_transfer">
            <i class="bi bi-arrows-expand me-1"></i>Ara Transferler
        </a>
    </li>
</ul>

<!-- Onay Bekleyenler -->
<?php if (!empty($pendingBookings)): ?>
<div class="mb-5">
    <h5 class="mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle text-warning"></i>
        Onay Bekleyen Rezervasyonlar
        <span class="badge pending-count-badge"><?= count($pendingBookings) ?></span>
    </h5>
    <div class="row g-2">
        <?php foreach ($pendingBookings as $pb):
            $dir      = $pb['booking_direction'] ?? 'outbound';
            $dirLabel = $directionLabels[$dir] ?? ['Arrival','primary'];
            $dateField = $pb['pickup_date'] ?: $pb['flight_date'];
        ?>
        <div class="col-lg-4 col-xl-3">
            <div class="card pending-card h-100" onclick="openBookingModal(<?= $pb['id'] ?>)">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="booking-number">#<?= e($pb['booking_number']) ?></span>
                        <div class="d-flex gap-1">
                            <?php if ($view === 'all'): ?>
                            <span class="badge bg-<?= $dirLabel[1] ?>"><?= $dirLabel[0] ?></span>
                            <?php endif; ?>
                            <?php if ((float)$pb['total_price'] > 0): ?>
                            <span class="badge bg-success fs-6"><?= number_format((float)$pb['total_price'], 0, ',', '.') ?> <?= e($pb['currency'] ?? 'TRY') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="booking-customer mb-1"><i class="bi bi-person me-1"></i><?= e($pb['customer_name']) ?></div>

                    <hr class="my-2">
                    <?php if ($dateField): ?>
                    <div>
                        <strong><i class="bi bi-airplane me-1"></i><?= date('d.m.Y', strtotime($dateField)) ?></strong>
                        <?php if ($pb['flight_time']): ?>
                        <span class="badge bg-primary fs-6"><?= date('H:i', strtotime($pb['flight_time'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($pb['hotel_address']): ?>
                    <div>
                        <strong><i class="bi bi-geo-alt me-1"></i><?= e($pb['hotel_address']) ?></strong>
                        <?php if (($dir === 'return' || $view === 'return') && $pb['pickup_time']): ?>
                        <span class="badge bg-success fs-6"><?= date('H:i', strtotime($pb['pickup_time'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (trim($pb['vehicle_name'] ?? '')): ?>
                    <div><strong><i class="bi bi-car-front me-1"></i><?= e($pb['vehicle_name']) ?></strong></div>
                    <?php endif; ?>
                    <div><strong><i class="bi bi-people me-1"></i><?= (int)$pb['adults'] ?> Yetişkin<?= (int)$pb['children'] > 0 ? ', '.(int)$pb['children'].' Çocuk' : '' ?></strong></div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-success btn-sm flex-fill" onclick="event.stopPropagation(); quickStatus(<?= $pb['id'] ?>, 'confirmed')">
                            <i class="bi bi-check-lg me-1"></i>Onayla
                        </button>
                        <button type="button" class="btn btn-danger btn-sm flex-fill" onclick="event.stopPropagation(); quickStatus(<?= $pb['id'] ?>, 'cancelled')">
                            <i class="bi bi-x-lg me-1"></i>İptal
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Rezervasyonlar Tablosu -->
<div class="card table-card">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i><?= $viewTitle ?></h6>

            <!-- Tarih navigasyonu (ortalanmış) -->
            <div class="d-flex align-items-center gap-1">
                <button type="button" id="date-prev" class="btn btn-sm btn-outline-primary" title="Önceki Gün">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="input-group input-group-sm" style="width:170px;">
                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    <input type="date" id="filter-date" class="form-control">
                </div>
                <button type="button" id="date-next" class="btn btn-sm btn-outline-primary" title="Sonraki Gün">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <button type="button" id="date-clear" class="btn btn-sm btn-outline-secondary" title="Tarihi Temizle">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <select id="filter-status" class="form-select form-select-sm" style="width:160px;">
                    <option value="yapilmadi" <?= $view === 'daily' ? 'selected' : '' ?>>Yapılmadı</option>
                    <option value="is_yapildi">İş Yapıldı</option>
                    <option value="disariya_verildi">Dışarıya Verildi</option>
                    <option value="" <?= $view !== 'daily' ? 'selected' : '' ?>>Tüm Durumlar</option>
                </select>
                <button type="button" id="filter-clear" class="btn btn-sm btn-outline-secondary" title="Filtreleri Temizle">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">

    <style>

/* Sütun ve metin sığma ayarları (mobilde yatay kaymayı önler)
   word-break: break-word kaldırıldı (2026-11-28): kelimeleri harf harf bölüyordu ("08:5 5", "Tut ar").
   Metin yalnız boşluklardan alt satıra geçer; tek kelime hücreye sığmazsa overflow-wrap böler. */
#bookingsTable th, #bookingsTable td {
    white-space: normal;
    overflow-wrap: break-word;
}

/* Tablo hazır olana kadar ham satırlar gösterilmez (telefonda ilk saniyelerdeki karışık görüntü).
   DataTables tabloyu .dataTables_wrapper içine alınca bu kurallar kendiliğinden devre dışı kalır. */
.table-responsive > #bookingsTable:not(.rz-goster) > thead,
.table-responsive > #bookingsTable:not(.rz-goster) > tbody { display: none; }
.rz-yukleniyor { display: none; }
.table-responsive > #bookingsTable:not(.rz-goster) ~ .rz-yukleniyor {
    display: flex; align-items: center; justify-content: center; gap: .6rem;
    padding: 2.5rem 1rem; color: var(--text-muted); font-size: .9rem;
}

/* Tarih kolonu asla alt satıra kaymasın */
#bookingsTable th:nth-child(2), #bookingsTable td:nth-child(2),
#bookingsTable td.text-nowrap {
    white-space: nowrap !important;
    word-break: normal !important;
}

/* Otel Adı kolonu daraltılsın ve alt satıra kaysın */
#bookingsTable th:nth-child(8), #bookingsTable td:nth-child(8) {
    max-width: 170px !important;
    white-space: normal !important;
    overflow-wrap: break-word !important;
}

@media (max-width: 991.98px) {
    #bookingsTable th, #bookingsTable td {
        padding: .5rem .3rem;
    }
}

/* Otel Adı: masaüstünde tam ad, mobilde bölge veya tam ad sığdırma */
.hotel-short { display: none; }
.hotel-full { display: inline; }
@media (max-width: 991.98px) {
    .hotel-short { display: inline; }
    .hotel-full { display: none; }
    /* Detay satırında tam otel adı görünsün ve alt satıra geçsin */
    .dtr-details .hotel-short { display: none; }
    .dtr-details .hotel-full { display: inline; white-space: normal !important; overflow-wrap: break-word; }
}

table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-info-full {
    width: 100% !important;
    flex: 1 1 100% !important;
    white-space: normal !important;
    overflow-wrap: break-word;
}
@media (min-width: 768px) {
    /* Özel masaüstü gizleme kuralları iptal edildi, tüm sütunlar görünür */
}

    /* Custom DTR Toggle Button (Mobile Child Row Açma Butonu) */
    table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control::before, 
    table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control::before {
        content: "\F282" !important; /* chevron-down */
        font-family: "bootstrap-icons" !important;
        background-color: var(--primary) !important;
        color: white !important;
        border: none !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
        width: 24px !important;
        height: 24px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
        line-height: 1 !important;
        margin-top: -12px !important;
    }
    table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control::before, 
    table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.dtr-control::before {
        content: "\F286" !important; /* chevron-up */
        background-color: #dc3545 !important;
    }

    /* Child Row (Aşağı açılan satır / Kart) Tasarımı */
    table.dataTable.dtr-inline.collapsed > tbody > tr.child > td {
        padding: 0 !important;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details {
        display: flex !important;
        flex-wrap: wrap !important;
        padding: 12px 15px !important;
        margin: 0 !important;
        list-style: none !important;
        width: 100% !important;
        background-color: rgba(var(--bs-light-rgb), 0.5); /* Hafif arka plan */
        border-radius: 0 0 8px 8px;
        box-shadow: inset 0 3px 6px rgba(0,0,0,0.02);
    }
    [data-bs-theme="dark"] table.dataTable > tbody > tr.child ul.custom-dtr-details {
        background-color: rgba(0,0,0,0.15);
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li {
        width: 100% !important;
        border-bottom: 1px dashed var(--bs-border-color) !important;
        padding: 8px 0 !important;
        display: flex !important;
        flex-direction: column !important;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li:last-child {
        border-bottom: none !important;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li > .dtr-title {
        font-weight: 700;
        color: var(--bs-primary);
        font-size: 0.70rem;
        text-transform: uppercase;
        margin-bottom: 4px;
        letter-spacing: 0.5px;
        display: block;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li > .dtr-data {
        font-size: 0.85rem;
        color: var(--bs-body-color);
        display: block;
    }
    
    /* Tutar alanını büyüt ve belirginleştir */
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-price-cell > .dtr-data {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--bs-success);
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-price-cell > .dtr-title {
        color: var(--bs-success);
        font-size: 0.75rem;
    }
    
    /* Yön, Tarih, Saat gibi bilgi sütunlarını yan yana diz (2x2 grid mantığı) */
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-info-cell {
        flex: 1 1 45% !important; 
        width: auto !important;
        min-width: 0 !important;
        padding-right: 10px !important;
    }
    
    /* İş Durumu (Checkboxes) - Tam genişlik, içerik yan yana */
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-status-cell {
        flex: 1 1 100% !important;
        width: 100% !important;
        border-bottom: none !important;
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color) !important;
        padding: 12px !important;
        margin-top: 10px !important;
        border-radius: 8px;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-status-cell > .dtr-title {
        color: var(--bs-secondary);
        border-bottom: 1px solid var(--bs-border-color);
        padding-bottom: 4px;
        margin-bottom: 10px;
        width: 100%;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-status-cell .ops-cell {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-status-cell .form-check {
        margin-bottom: 0 !important;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-status-cell .form-check-input {
        margin-top: 0;
        width: 1.2rem;
        height: 1.2rem;
        cursor: pointer;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-status-cell .form-check-label {
        font-size: 0.90rem;
        cursor: pointer;
    }

    /* Durum ve İşlem - %48 yan yana ve merkezlenmiş */
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-action-cell {
        flex: 1 1 45% !important; 
        width: auto !important;
        min-width: 0 !important; /* İçeriğin flex'i patlatmasını önle */
        border-bottom: none !important;
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color) !important;
        padding: 12px !important;
        margin-top: 10px !important;
        border-radius: 8px;
        margin-right: 10px !important;
        align-items: center !important;
        text-align: center;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-action-cell:last-child {
        margin-right: 0 !important;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-action-cell > .dtr-title {
        color: var(--bs-secondary);
        border-bottom: 1px solid var(--bs-border-color);
        padding-bottom: 4px;
        margin-bottom: 10px;
        width: 100%;
        text-align: center;
    }
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-action-cell > .dtr-data {
        display: flex;
        justify-content: center;
        width: 100%;
    }
    
    /* Aksiyon butonlarını genişlet (Mobilde rahat basılması için) */
    table.dataTable > tbody > tr.child ul.custom-dtr-details > li.dtr-action-cell .btn {
        padding: 8px 14px !important;
        font-size: 1.1rem !important; /* İkonları netleştir ve büyüt */
    }



    /* Tablet/Desktop için Custom Scroll (Mobilde DataTables Responsive çalışsın diye genişlik zorlamaz) */
    /* BU KISIM KALDIRILDI - BOOTSTRAP TABLE-RESPONSIVE KULLANILACAK */

/* Sayfa filter satırı mobilde stack */
@media (max-width: 767.98px) {
    .table-card .card-header > .d-flex { flex-direction: column; align-items: stretch !important; gap: .5rem; }
    .table-card .card-header .d-flex.gap-2 { flex-wrap: wrap; }
    .table-card .card-header #filter-status { flex: 1; min-width: 130px; }
}
    </style>

    <?php $viewClass = 'view-' . $view; ?>
    <div class="<?= $viewClass ?>">
    <div class="table-responsive">

    <?php if ($view === 'all' || $view === 'daily'): ?>
    <!-- Günlük Görünüm: her rezervasyon ayrı satırda -->
    <table id="bookingsTable" class="table table-hover" width="100%">
        <thead>
            <tr>
                <th class="all">Yön</th>
                <th class="min-tablet">Tarih</th>
                <th class="all">Alış Saati</th>
                <th class="all">Saat</th>
                <th class="min-tablet">Uçuş Kodu</th>
                <th class="min-tablet">Müşteri</th>
                <th class="min-tablet">Kişi</th>
                <th class="all">Bölge</th>
                <th class="min-tablet">Araç</th>
                <th class="min-tablet">Tutar</th>
                <th class="min-tablet">İş Durumu</th>
                <th class="min-tablet">Durum</th>
                <th class="min-tablet">İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allBookings as $b):
                $isInter  = !empty($b['is_inter_transfer']) || ($b['booking_direction'] ?? '') === 'inter_transfer';
                $dir      = $b['booking_direction'] ?? 'outbound';
                $dirLabel = $isInter ? ['Ara Transfer', 'danger'] : ($directionLabels[$dir] ?? ['Arrival','primary']);
                $dirIcon  = $isInter ? 'arrows-expand' : ($dir === 'return' ? 'box-arrow-up-right' : 'box-arrow-in-down-right');
                $dateField = ($b['flight_date'] ?? null) ?: (($b['pickup_date'] ?? null) ?: null);
                $bVParam   = $isInter ? 'out_id=' . $b['id'] : (($dir === 'return') ? 'ret_id=' . $b['id'] : 'out_id=' . $b['id']);
            ?>
            <tr class="<?= (!empty($b['is_completed']) ? 'row-completed' : '') ?>">
                <td>
                    <span class="badge bg-<?= $dirLabel[1] ?>">
                        <i class="bi bi-<?= $dirIcon ?> me-1"></i><?= $dirLabel[0] ?>
                    </span>
                </td>
                <td data-order="<?= $dateField ? date('Y-m-d', strtotime($dateField)) : '' ?>" class="text-nowrap"><?= $dateField ? date('d.m.Y', strtotime($dateField)) : '-' ?></td>
                <td>
                    <?php if (($dir === 'return' || $isInter) && !empty($b['pickup_time'])): ?>
                        <strong><?= date('H:i', strtotime($b['pickup_time'])) ?></strong>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <?php if ($isInter): ?>
                    <?php
                        $pickupFull = trim($b['pickup_location'] ?? '');
                        $pickupShort = $pickupFull;
                        if ($pickupFull && strpos($pickupFull, '—') !== false) {
                            $parts = explode('—', $pickupFull);
                            $pickupShort = trim(end($parts));
                        } elseif ($pickupFull && strpos($pickupFull, '-') !== false) {
                            $parts = explode('-', $pickupFull);
                            $pickupShort = trim(end($parts));
                        }
                    ?>
                    <td class="align-middle">
                        <div style="font-size:0.68rem;color:#6c757d;text-transform:uppercase;letter-spacing:.3px;line-height:1;margin-bottom:3px;"><i class="bi bi-building-up me-1"></i>Alınış Oteli</div>
                        <div>
                            <span class="hotel-full fw-semibold" style="font-size:0.85rem;color:#0c5460;"><?= e($pickupFull ?: '-') ?></span>
                            <span class="hotel-short fw-semibold" style="font-size:0.85rem;color:#0c5460;"><?= e($pickupShort ?: '-') ?></span>
                        </div>
                    </td>
                    <td class="align-middle text-muted">-</td>
                <?php else: ?>
                    <td>
                        <?php if (!empty($b['flight_time'])): ?>
                            <strong><?= date('H:i', strtotime($b['flight_time'])) ?></strong>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($b['flight_number'])): ?>
                            <strong><?= e($b['flight_number']) ?></strong>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                <?php endif; ?>
                <td>
                    <?= e($b['customer_name']) ?>
                </td>
                <td><?= (int)$b['adults'] ?>Y<?= (int)$b['children'] > 0 ? ' +'.(int)$b['children'].'Ç' : '' ?></td>
                <?php
                    $hotelFull = trim($b['hotel_address'] ?? '');
                    $hotelShort = $hotelFull;
                    if ($hotelFull && strpos($hotelFull, '—') !== false) {
                        $parts = explode('—', $hotelFull);
                        $hotelShort = trim(end($parts));
                    } elseif ($hotelFull && strpos($hotelFull, '-') !== false) {
                        $parts = explode('-', $hotelFull);
                        $hotelShort = trim(end($parts));
                    }
                ?>
                <?php if ($isInter): /* ara transfer: varış oteli olduğu gibi */ ?>
                <td data-search="<?= e($hotelFull) ?>">
                    <span class="hotel-full"><?= e($hotelFull ?: '-') ?></span>
                    <span class="hotel-short"><?= e($hotelShort ?: '-') ?></span>
                </td>
                <?php else: $bolge = rzBolge($hotelFull, $otelBolge); /* yalnız bölge; otel adı yalnız aramada */ ?>
                <td data-search="<?= e(trim($bolge . ' ' . $hotelFull)) ?>"><?= e($bolge) ?></td>
                <?php endif; ?>
                <td><?= e(trim($b['vehicle_name'] ?? '') ?: '-') ?></td>
                <td>
                    <?php if ((float)$b['total_price'] > 0): ?>
                        <strong><?= number_format((float)$b['total_price'], 0, ',', '.') ?></strong>
                        <small class="text-muted"><?= e($b['currency'] ?? 'TRY') ?></small>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td>
                    <div class="ops-cell">
                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input ops-check" id="comp-<?= $b['id'] ?>"
                                   data-id="<?= $b['id'] ?>" data-field="is_completed"
                                   <?= !empty($b['is_completed']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="comp-<?= $b['id'] ?>">İş yapıldı</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input ops-check ops-out-check" id="out-<?= $b['id'] ?>"
                                   data-id="<?= $b['id'] ?>" data-field="is_outsourced"
                                   data-outsource-name="<?= e($b['outsource_name'] ?? '') ?>"
                                   data-outsource-partner-id="<?= (int)($b['outsource_partner_id'] ?? 0) ?>"
                                   <?= !empty($b['is_outsourced']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="out-<?= $b['id'] ?>">Dışarıya verildi</label>
                        </div>
                        <!-- daily: fiyat/partner gizli (ileride açılabilir) -->
                        <div class="ops-price-wrap mt-1" style="display:none;">
                            <input type="number" class="form-control form-control-sm ops-price-input"
                                   data-id="<?= $b['id'] ?>"
                                   value="<?= e($b['outsource_price'] ?? '') ?>"
                                   placeholder="Tutar..." min="0" step="0.01" style="width:90px;">
                            <?php if (!empty($b['outsource_name'])): ?>
                            <small class="ops-name-display text-muted d-block mt-1"><?= e($b['outsource_name']) ?></small>
                            <?php else: ?>
                            <small class="ops-name-display text-muted d-block mt-1" style="display:none!important;"></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="visually-hidden"><?= $statusLabels[$b['booking_status']][0] ?></span>
                    <div class="d-flex gap-1 align-items-center flex-wrap">
                        <button type="button"
                                class="btn btn-outline-<?= $dir === 'return' ? 'info' : 'primary' ?>"
                                style="padding:2px 6px;"
                                onclick="openOpsStatus(<?= $b['id'] ?>)"
                                title="<?= $statusLabels[$b['booking_status']][0] ?>">
                            <i class="bi bi-<?= $dirIcon ?>"></i>
                        </button>
                        <div class="btn-group btn-group-sm">
                            <a href="voucher.php?<?= $bVParam ?>&lang=tr" target="_blank" class="btn btn-outline-success" style="padding:2px 6px;" title="Voucher (TR)">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" style="padding:2px 4px;" data-bs-toggle="dropdown">
                                <span class="visually-hidden">Dil Seç</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=tr" target="_blank">🇹🇷 Türkçe</a></li>
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=en" target="_blank">🇬🇧 English</a></li>
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=de" target="_blank">🇩🇪 Deutsch</a></li>
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=ru" target="_blank">🇷🇺 Русский</a></li>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="openBookingModal(<?= $b['id'] ?>)" title="Detay / Düzenle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteBooking(<?= $b['id'] ?>)" title="Sil">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php else: ?>
    <!-- Arrival / Depar view: günlük görünüm ile aynı sütunlar -->
    <table id="bookingsTable" class="table table-hover" width="100%">
        <thead>
            <tr>
                <th class="all">Yön</th>
                <th class="min-tablet">Tarih</th>
                <?php if ($view !== 'arrival'): ?>
                <th class="all">Alış Saati</th>
                <?php endif; ?>
                <?php if ($view === 'inter_transfer'): ?>
                <th class="all">Alınış Oteli</th>
                <?php else: ?>
                <th class="all">Saat</th>
                <th class="min-tablet">Uçuş Kodu</th>
                <?php endif; ?>
                <th class="min-tablet">Müşteri</th>
                <th class="min-tablet">Kişi</th>
                <th class="all"><?= $view === 'inter_transfer' ? 'Varış Oteli' : 'Bölge' ?></th>
                <th class="min-tablet">Araç</th>
                <th class="min-tablet">Tutar</th>
                <th class="min-tablet">İş Durumu</th>
                <th class="min-tablet">Durum</th>
                <th class="min-tablet">İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allBookings as $b):
                $isInter  = !empty($b['is_inter_transfer']) || ($b['booking_direction'] ?? '') === 'inter_transfer';
                $dir      = $b['booking_direction'] ?? 'outbound';
                $dirLabel = $isInter ? ['Ara Transfer', 'danger'] : ($directionLabels[$dir] ?? ['Arrival','primary']);
                $dirIcon  = $isInter ? 'arrows-expand' : ($dir === 'return' ? 'box-arrow-up-right' : 'box-arrow-in-down-right');
                $dateField = ($b['flight_date'] ?? null) ?: (($b['pickup_date'] ?? null) ?: null);
                $bVParam   = ($dir === 'return') ? 'ret_id=' . $b['id'] : 'out_id=' . $b['id'];
            ?>
            <tr class="<?= (!empty($b['is_completed']) ? 'row-completed' : '') ?>">
                <td>
                    <span class="badge bg-<?= $dirLabel[1] ?>">
                        <i class="bi bi-<?= $dirIcon ?> me-1"></i><?= $dirLabel[0] ?>
                    </span>
                </td>
                <td data-order="<?= $dateField ? date('Y-m-d', strtotime($dateField)) : '' ?>" class="text-nowrap"><?= $dateField ? date('d.m.Y', strtotime($dateField)) : '-' ?></td>
                <?php if ($view !== 'arrival'): ?>
                <td>
                    <?php if (!empty($b['pickup_time'])): ?>
                        <strong><?= date('H:i', strtotime($b['pickup_time'])) ?></strong>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <?php endif; ?>
                <?php if ($view === 'inter_transfer'): ?>
                    <?php
                        $pickupFull = trim($b['pickup_location'] ?? '');
                        $pickupShort = $pickupFull;
                        if ($pickupFull && strpos($pickupFull, '—') !== false) {
                            $parts = explode('—', $pickupFull);
                            $pickupShort = trim(end($parts));
                        } elseif ($pickupFull && strpos($pickupFull, '-') !== false) {
                            $parts = explode('-', $pickupFull);
                            $pickupShort = trim(end($parts));
                        }
                    ?>
                    <td>
                        <span class="hotel-full"><?= e($pickupFull ?: '-') ?></span>
                        <span class="hotel-short"><?= e($pickupShort ?: '-') ?></span>
                    </td>
                <?php else: ?>
                    <?php if ($isInter): ?>
                        <?php
                            $pickupFull = trim($b['pickup_location'] ?? '');
                            $pickupShort = $pickupFull;
                            if ($pickupFull && strpos($pickupFull, '—') !== false) {
                                $parts = explode('—', $pickupFull);
                                $pickupShort = trim(end($parts));
                            } elseif ($pickupFull && strpos($pickupFull, '-') !== false) {
                                $parts = explode('-', $pickupFull);
                                $pickupShort = trim(end($parts));
                            }
                        ?>
                        <td class="align-middle">
                            <div style="font-size:0.68rem;color:#6c757d;text-transform:uppercase;letter-spacing:.3px;line-height:1;margin-bottom:3px;"><i class="bi bi-building-up me-1"></i>Alınış Oteli</div>
                            <div>
                                <span class="hotel-full fw-semibold" style="font-size:0.85rem;color:#0c5460;"><?= e($pickupFull ?: '-') ?></span>
                                <span class="hotel-short fw-semibold" style="font-size:0.85rem;color:#0c5460;"><?= e($pickupShort ?: '-') ?></span>
                            </div>
                        </td>
                        <td class="align-middle text-muted">-</td>
                    <?php else: ?>
                        <td>
                            <?php if (!empty($b['flight_time'])): ?>
                                <strong><?= date('H:i', strtotime($b['flight_time'])) ?></strong>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($b['flight_number'])): ?>
                                <strong><?= e($b['flight_number']) ?></strong>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                    <?php endif; ?>
                <?php endif; ?>
                <td>
                    <?= e($b['customer_name']) ?>
                </td>
                <td><?= (int)$b['adults'] ?>Y<?= (int)$b['children'] > 0 ? ' +'.(int)$b['children'].'Ç' : '' ?></td>
                <?php
                    $hotelFull = trim($b['hotel_address'] ?? '');
                    $hotelShort = $hotelFull;
                    if ($hotelFull && strpos($hotelFull, '—') !== false) {
                        $parts = explode('—', $hotelFull);
                        $hotelShort = trim(end($parts));
                    } elseif ($hotelFull && strpos($hotelFull, '-') !== false) {
                        $parts = explode('-', $hotelFull);
                        $hotelShort = trim(end($parts));
                    }
                ?>
                <?php if ($isInter): /* ara transfer: varış oteli olduğu gibi */ ?>
                <td data-search="<?= e($hotelFull) ?>">
                    <span class="hotel-full"><?= e($hotelFull ?: '-') ?></span>
                    <span class="hotel-short"><?= e($hotelShort ?: '-') ?></span>
                </td>
                <?php else: $bolge = rzBolge($hotelFull, $otelBolge); /* yalnız bölge; otel adı yalnız aramada */ ?>
                <td data-search="<?= e(trim($bolge . ' ' . $hotelFull)) ?>"><?= e($bolge) ?></td>
                <?php endif; ?>
                <td><?= e(trim($b['vehicle_name'] ?? '') ?: '-') ?></td>
                <td>
                    <?php if ((float)$b['total_price'] > 0): ?>
                        <strong><?= number_format((float)$b['total_price'], 0, ',', '.') ?></strong>
                        <small class="text-muted"><?= e($b['currency'] ?? 'TRY') ?></small>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td>
                    <div class="ops-cell">
                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input ops-check" id="comp-<?= $b['id'] ?>"
                                   data-id="<?= $b['id'] ?>" data-field="is_completed"
                                   <?= !empty($b['is_completed']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="comp-<?= $b['id'] ?>">İş yapıldı</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input ops-check ops-out-check" id="out-<?= $b['id'] ?>"
                                   data-id="<?= $b['id'] ?>" data-field="is_outsourced"
                                   data-outsource-name="<?= e($b['outsource_name'] ?? '') ?>"
                                   data-outsource-partner-id="<?= (int)($b['outsource_partner_id'] ?? 0) ?>"
                                   <?= !empty($b['is_outsourced']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="out-<?= $b['id'] ?>">Dışarıya verildi</label>
                        </div>
                        <div class="ops-price-wrap mt-1" style="display:none;">
                            <input type="number" class="form-control form-control-sm ops-price-input"
                                   data-id="<?= $b['id'] ?>"
                                   value="<?= e($b['outsource_price'] ?? '') ?>"
                                   placeholder="Tutar..." min="0" step="0.01" style="width:90px;">
                            <?php if (!empty($b['outsource_name'])): ?>
                            <small class="ops-name-display text-muted d-block mt-1"><?= e($b['outsource_name']) ?></small>
                            <?php else: ?>
                            <small class="ops-name-display text-muted d-block mt-1" style="display:none!important;"></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="visually-hidden"><?= $statusLabels[$b['booking_status']][0] ?></span>
                    <div class="d-flex gap-1 align-items-center flex-wrap">
                        <button type="button"
                                class="btn btn-outline-<?= $dir === 'return' ? 'info' : 'primary' ?>"
                                style="padding:2px 6px;"
                                onclick="openOpsStatus(<?= $b['id'] ?>)"
                                title="<?= $statusLabels[$b['booking_status']][0] ?>">
                            <i class="bi bi-<?= $dirIcon ?>"></i>
                        </button>
                        <div class="btn-group btn-group-sm">
                            <a href="voucher.php?<?= $bVParam ?>&lang=tr" target="_blank" class="btn btn-outline-success" style="padding:2px 6px;" title="Voucher (TR)">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" style="padding:2px 4px;" data-bs-toggle="dropdown">
                                <span class="visually-hidden">Dil Seç</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=tr" target="_blank">🇹🇷 Türkçe</a></li>
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=en" target="_blank">🇬🇧 English</a></li>
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=de" target="_blank">🇩🇪 Deutsch</a></li>
                                <li><a class="dropdown-item small" href="voucher.php?<?= $bVParam ?>&lang=ru" target="_blank">🇷🇺 Русский</a></li>
                            </ul>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="openBookingModal(<?= $b['id'] ?>)" title="Detay / Düzenle">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteBooking(<?= $b['id'] ?>)" title="Sil">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Tablo hazırlanırken görünür; DataTables hazır olunca CSS ile kendiliğinden gizlenir -->
    <div class="rz-yukleniyor" role="status"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span>Rezervasyonlar yükleniyor…</div>
    <script>
    /* Güvenlik: tablo 12 sn içinde hazırlanamazsa (ör. internet kütüphanesi yüklenemedi) satırları ham haliyle göster */
    setTimeout(function () {
        var t = document.getElementById('bookingsTable');
        if (t && t.parentNode && t.parentNode.classList && t.parentNode.classList.contains('table-responsive')) t.classList.add('rz-goster');
    }, 12000);
    </script>

    </div><!-- /.table-responsive -->
    </div><!-- /.view-X -->

    </div>
</div>

<!-- ===================== DETAY / DÜZENLEME MODALı ===================== -->
<div class="modal fade modal-booking" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Rezervasyon Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookingEditForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="modal-booking-id">
                <input type="hidden" name="outbound_id" id="modal-outbound-id" value="">
                <input type="hidden" name="return_id" id="modal-return-id" value="">
                <input type="hidden" name="customer_id" id="modal-customer-id" value="">

                <div class="modal-body">
                    <!-- Üst bilgi kartları -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <div class="text-muted small">Rezervasyon No</div>
                                    <div class="fw-bold fs-5 text-primary" id="modal-booking-number"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <div class="text-muted small">Tür</div>
                                    <div id="modal-booking-type" class="fw-bold fs-5"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-3">
                                    <div class="text-muted small">Oluşturulma</div>
                                    <div class="fw-bold" id="modal-created-at"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Durum -->
                    <div class="mb-4">
                        <label class="booking-detail-label d-block mb-2">Rezervasyon Durumu</label>
                        <div class="status-select-group btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="booking_status" id="status-pending" value="pending">
                            <label class="btn btn-outline-warning" for="status-pending"><i class="bi bi-hourglass-split me-1"></i>Bekliyor</label>
                            <input type="radio" class="btn-check" name="booking_status" id="status-confirmed" value="confirmed">
                            <label class="btn btn-outline-success" for="status-confirmed"><i class="bi bi-check-circle me-1"></i>Onayla</label>
                            <input type="radio" class="btn-check" name="booking_status" id="status-cancelled" value="cancelled">
                            <label class="btn btn-outline-danger" for="status-cancelled"><i class="bi bi-x-circle me-1"></i>İptal</label>
                        </div>
                    </div>

                    <hr>

                    <!-- Müşteri -->
                    <h6 class="mb-3"><i class="bi bi-person me-2"></i>Müşteri Bilgileri</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 customer-autocomplete-wrap">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" name="customer_name" id="modal-customer-name" class="form-control" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');" required>
                            <div class="customer-autocomplete-list list-group shadow" style="display: none;"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="customer_email" id="modal-customer-email" class="form-control" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');">
                        </div>
                        <div class="col-md-4 customer-autocomplete-wrap">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="customer_phone" id="modal-customer-phone" class="form-control" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');">
                            <div class="customer-autocomplete-list list-group shadow" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Transfer & Araç -->
                    <h6 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Havalimanı, Transfer & Araç Seçimi</h6>
                    <div class="row mb-4 g-2">
                        <div class="col-md-4">
                            <label class="form-label">Havalimanı</label>
                            <select name="airport_id" id="modal-airport" class="form-select airport-select">
                                <option value="">-- Havalimanı Seçin --</option>
                                <?php foreach ($airports as $ap): ?>
                                <option value="<?= $ap['id'] ?>" <?= $ap['is_default'] ? 'selected' : '' ?>><?= e($ap['name']) ?><?= $ap['code'] ? ' (' . e($ap['code']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transfer</label>
                            <select name="destination_id" id="modal-destination" class="form-select destination-select">
                                <option value="">-- Transfer Seçin --</option>
                                <?php foreach ($destinations as $dest): ?>
                                <option value="<?= $dest['id'] ?>"><?= e($dest['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Araç</label>
                            <select name="vehicle_id" id="modal-vehicle" class="form-select">
                                <option value="">-- Araç Seçin --</option>
                                <?php foreach ($allVehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= e($v['vehicle_name']) ?> (<?= (int)$v['capacity'] ?> kişi)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tur alanları -->
                    <div id="modal-tour-fields" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Alınış Yeri</label>
                                <input type="text" name="pickup_location" id="modal-pickup-location" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Alınış Tarihi</label>
                                <input type="date" name="pickup_date" id="modal-pickup-date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Alınış Saati</label>
                                <input type="time" name="pickup_time" id="modal-pickup-time" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Depar Saati</label>
                                <input type="time" name="return_time" id="modal-return-time" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Transfer alanları -->
                    <div id="modal-transfer-fields" style="display:none;">
                        <!-- Arrival toggle -->
                        <div class="form-check form-switch mb-3" id="modal-outbound-toggle-wrap">
                            <input class="form-check-input" type="checkbox" id="modal-has-outbound" name="has_outbound" value="1">
                            <label class="form-check-label fw-bold" for="modal-has-outbound">
                                <i class="bi bi-box-arrow-in-down-right me-1 text-primary"></i>Arrival Transferi Ekle
                            </label>
                        </div>

                        <!-- ARRIVAL alanları -->
                        <div id="modal-outbound-section">
                            <h6 class="mb-3 text-primary"><i class="bi bi-box-arrow-in-down-right me-2"></i>Arrival Uçuş Bilgileri</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Uçuş Tarihi</label>
                                    <input type="date" name="flight_date" id="modal-flight-date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Uçuş Saati</label>
                                    <input type="time" name="flight_time" id="modal-flight-time" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Uçuş Numarası</label>
                                    <input type="text" name="flight_number" id="modal-flight-number" class="form-control flight-number-upper" style="text-transform:uppercase;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-7">
                                    <label class="form-label">Otel / Adres</label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <select name="hotel_address" id="modal-hotel-address" class="form-select hotel-select-edit">
                                                <option value=""></option>
                                                <?php foreach ($hotelOptions as $ho): ?>
                                                <option value="<?= e($ho['name'] . ($ho['address'] ? ' — ' . $ho['address'] : '')) ?>">
                                                    <?= e($ho['name']) ?><?= $ho['distance_km'] !== null ? ' (' . number_format((float)$ho['distance_km'], 0) . ' km)' : '' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-quick-hotel flex-shrink-0"
                                                data-target="modal-hotel-address" title="Hızlı Otel Ekle" style="height:38px;">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Arrival Fiyatı</label>
                                    <div class="input-group">
                                        <select name="currency" id="modal-currency" class="form-select" style="max-width:90px;">
                                            <option value="TRY">₺ TRY</option>
                                            <option value="EUR">€ EUR</option>
                                            <option value="USD">$ USD</option>
                                            <option value="GBP">£ GBP</option>
                                        </select>
                                        <input type="number" name="total_price" id="modal-total-price" class="form-control" min="0" step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>

                        <!-- Depar toggle -->
                        <div class="form-check form-switch mb-3" id="modal-return-toggle-wrap">
                            <input class="form-check-input" type="checkbox" id="modal-has-return" name="has_return" value="1">
                            <label class="form-check-label fw-bold" for="modal-has-return">
                                <i class="bi bi-box-arrow-up-right me-1 text-info"></i>Depar Transferi Ekle
                            </label>
                        </div>

                        <!-- DEPAR alanları -->
                        <div id="modal-return-section" class="return-section mb-4">
                            <h6 class="mb-3 text-info"><i class="bi bi-box-arrow-up-right me-2"></i>Depar Uçuş Bilgileri</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Depar Uçuş Tarihi</label>
                                    <input type="date" name="return_flight_date" id="modal-return-flight-date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Depar Uçuş Saati</label>
                                    <input type="time" name="return_flight_time" id="modal-return-flight-time" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Depar Uçuş Numarası</label>
                                    <input type="text" name="return_flight_number" id="modal-return-flight-number" class="form-control flight-number-upper" style="text-transform:uppercase;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-9">
                                    <label class="form-label">Otel / Adres</label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <select name="return_hotel_address" id="modal-return-hotel-address" class="form-select hotel-select-edit">
                                                <option value=""></option>
                                                <?php foreach ($hotelOptions as $ho): ?>
                                                <option value="<?= e($ho['name'] . ($ho['address'] ? ' — ' . $ho['address'] : '')) ?>">
                                                    <?= e($ho['name']) ?><?= $ho['distance_km'] !== null ? ' (' . number_format((float)$ho['distance_km'], 0) . ' km)' : '' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-quick-hotel flex-shrink-0"
                                                data-target="modal-return-hotel-address" title="Hızlı Otel Ekle" style="height:38px;">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Otelden Alış Saati</label>
                                    <input type="time" name="return_pickup_time" id="modal-return-pickup-time" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Depar Fiyatı</label>
                                    <div class="input-group">
                                        <select name="return_currency" id="modal-return-currency" class="form-select" style="max-width:90px;">
                                            <option value="TRY">₺ TRY</option>
                                            <option value="EUR">€ EUR</option>
                                            <option value="USD">$ USD</option>
                                            <option value="GBP">£ GBP</option>
                                        </select>
                                        <input type="number" name="return_total_price" id="modal-return-price" class="form-control" min="0" step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Yolcu -->
                    <h6 class="mb-3"><i class="bi bi-people me-2"></i>Yolcu Bilgileri</h6>
                    <input type="hidden" id="edit-pax-capacity" value="20">
                    <div class="d-flex flex-wrap gap-4 mb-3">
                        <div>
                            <label class="form-label d-block small fw-semibold">Yetişkin</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="modal-adults"><i class="bi bi-dash"></i></button>
                                <input type="number" name="adults" id="modal-adults" class="form-control" value="1" min="1" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="modal-adults"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label d-block small fw-semibold">Çocuk</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="modal-children"><i class="bi bi-dash"></i></button>
                                <input type="number" name="children" id="modal-children" class="form-control" value="0" min="0" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="modal-children"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label d-block small fw-semibold">Çocuk Koltuğu</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="modal-child-seat"><i class="bi bi-dash"></i></button>
                                <input type="number" name="child_seat" id="modal-child-seat" class="form-control" value="0" min="0" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="modal-child-seat"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label d-block small fw-semibold">Yükseltici</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="modal-booster"><i class="bi bi-dash"></i></button>
                                <input type="number" name="booster" id="modal-booster" class="form-control" value="0" min="0" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="modal-booster"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="edit-passenger-names" class="mb-4"></div>


                    <!-- Notlar -->
                    <h6 class="mb-3"><i class="bi bi-chat-text me-2"></i>Notlar</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Müşteri Notu</label>
                            <textarea name="notes" id="modal-notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admin Notu <small class="text-muted">(müşteri görmez)</small></label>
                            <textarea name="admin_notes" id="modal-admin-notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== YENİ ARA TRANSFER MODALı ===================== -->
<div class="modal fade" id="addInterTransferModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-arrows-expand me-2"></i>Yeni Ara Transfer Ekle (Otelden Otele)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addInterTransferForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="modal-body" style="max-height:75vh;overflow-y:auto;">
                    
                    <!-- Transfer & Araç Seçimi -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body py-2 px-3">
                            <h6 class="card-title fw-semibold text-muted mb-2 small"><i class="bi bi-geo-alt me-1"></i>Transfer & Araç Seçimi</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Transfer Güzergahı</label>
                                    <select name="destination_id" class="form-select form-select-sm inter-destination-select">
                                        <option value="">-- Transfer Seçin --</option>
                                        <?php foreach ($destinations as $d): ?>
                                            <option value="<?= $d['id'] ?>"><?= e($d['title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Araç</label>
                                    <select name="vehicle_id" class="form-select form-select-sm">
                                        <option value="">-- Araç Seçin --</option>
                                        <?php foreach ($allVehicles as $v): ?>
                                            <option value="<?= $v['id'] ?>" data-capacity="<?= $v['capacity'] ?>"><?= e($v['vehicle_name']) ?> (Max <?= $v['capacity'] ?> Kişi)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Müşteri Bilgileri -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body py-2 px-3">
                            <h6 class="card-title fw-semibold text-muted mb-2 small"><i class="bi bi-person me-1"></i>Müşteri Bilgileri</h6>
                            <input type="hidden" name="customer_id" id="inter-customer-id">
                            <div class="row g-2">
                                <div class="col-md-4 customer-autocomplete-wrap">
                                    <label class="form-label small mb-1">Ad Soyad *</label>
                                    <input type="text" name="customer_name" id="inter-customer-name" class="form-control form-control-sm" required placeholder="Müşteri ad soyad" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');">
                                    <div class="customer-autocomplete-list list-group shadow" style="display: none;"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">E-posta</label>
                                    <input type="email" name="customer_email" id="inter-customer-email" class="form-control form-control-sm" placeholder="E-posta adresi" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');">
                                </div>
                                <div class="col-md-4 customer-autocomplete-wrap">
                                    <label class="form-label small mb-1">Telefon</label>
                                    <input type="text" name="customer_phone" id="inter-customer-phone" class="form-control form-control-sm" placeholder="+90 5XX XXX XX XX" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');">
                                    <div class="customer-autocomplete-list list-group shadow" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alınış Bilgileri (Otel / Adres) -->
                    <div class="card mb-3 border-info border-opacity-25">
                        <div class="card-body py-2 px-3">
                            <h6 class="card-title fw-semibold text-info mb-2 small"><i class="bi bi-building-up me-1"></i>Alınış Oteli / Noktası Bilgileri</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Alınış Oteli / Adres</label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <select name="pickup_hotel_name" id="inter-pickup-hotel-select" class="form-select hotel-select-inter">
                                                <option value=""></option>
                                                <?php foreach ($hotelOptions as $ho): ?>
                                                <option value="<?= e($ho['name'] . ($ho['address'] ? ' — ' . $ho['address'] : '')) ?>">
                                                    <?= e($ho['name']) ?><?= $ho['distance_km'] !== null ? ' (' . number_format((float)$ho['distance_km'], 0) . ' km)' : '' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-quick-hotel flex-shrink-0"
                                                data-target="inter-pickup-hotel-select" title="Hızlı Otel Ekle" style="height:38px;">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Alınış Tarihi</label>
                                    <input type="date" name="pickup_date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Alınış Saati</label>
                                    <input type="time" name="pickup_time" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Varış Bilgileri (Otel / Adres) -->
                    <div class="card mb-3 border-primary border-opacity-25">
                        <div class="card-body py-2 px-3">
                            <h6 class="card-title fw-semibold text-primary mb-2 small"><i class="bi bi-building-down me-1"></i>Varış Oteli / Noktası Bilgileri</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Varış Oteli / Adres</label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <select name="destination_hotel_name" id="inter-dest-hotel-select" class="form-select hotel-select-inter">
                                                <option value=""></option>
                                                <?php foreach ($hotelOptions as $ho): ?>
                                                <option value="<?= e($ho['name'] . ($ho['address'] ? ' — ' . $ho['address'] : '')) ?>">
                                                    <?= e($ho['name']) ?><?= $ho['distance_km'] !== null ? ' (' . number_format((float)$ho['distance_km'], 0) . ' km)' : '' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-quick-hotel flex-shrink-0"
                                                data-target="inter-dest-hotel-select" title="Hızlı Otel Ekle" style="height:38px;">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Transfer Ücreti</label>
                                    <div class="input-group input-group-sm">
                                        <select name="currency" class="form-select form-select-sm" style="max-width:85px;">
                                            <option value="EUR">€ EUR</option>
                                            <option value="USD">$ USD</option>
                                            <option value="TRY">₺ TRY</option>
                                            <option value="GBP">£ GBP</option>
                                        </select>
                                        <input type="number" step="0.01" name="total_price" class="form-control form-control-sm" placeholder="0.00" value="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Yolcu Bilgileri (Steppers & İsimler) -->
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body py-2 px-3">
                            <h6 class="card-title fw-semibold text-muted mb-2 small"><i class="bi bi-people me-1"></i>Yolcu Bilgileri</h6>
                            <div class="row g-3 align-items-center mb-2">
                                <div class="col-auto">
                                    <label class="form-label small mb-1 d-block">Yetişkin</label>
                                    <div class="input-group input-group-sm" style="width:110px;">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="minus" data-target="inter-adults-input">-</button>
                                        <input type="number" name="adults" id="inter-adults-input" class="form-control text-center px-1" value="1" min="1" max="50">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="plus" data-target="inter-adults-input">+</button>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small mb-1 d-block">Çocuk</label>
                                    <div class="input-group input-group-sm" style="width:110px;">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="minus" data-target="inter-children-input">-</button>
                                        <input type="number" name="children" id="inter-children-input" class="form-control text-center px-1" value="0" min="0" max="20">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="plus" data-target="inter-children-input">+</button>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small mb-1 d-block">Çocuk Koltuğu</label>
                                    <div class="input-group input-group-sm" style="width:110px;">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="minus" data-target="inter-seat-input">-</button>
                                        <input type="number" name="child_seat" id="inter-seat-input" class="form-control text-center px-1" value="0" min="0" max="10">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="plus" data-target="inter-seat-input">+</button>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small mb-1 d-block">Yükseltici</label>
                                    <div class="input-group input-group-sm" style="width:110px;">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="minus" data-target="inter-booster-input">-</button>
                                        <input type="number" name="booster" id="inter-booster-input" class="form-control text-center px-1" value="0" min="0" max="10">
                                        <button type="button" class="btn btn-outline-secondary inter-pax-step-btn" data-action="plus" data-target="inter-booster-input">+</button>
                                    </div>
                                </div>
                            </div>
                            <div id="inter-passenger-names-container">
                                <!-- Yolcu isim alanları -->
                            </div>
                        </div>
                    </div>

                    <!-- Notlar -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Müşteri Notu</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Özel istekler..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Yönetici Notu <small class="text-muted">(iç not)</small></label>
                            <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="İç notlar..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-info text-white btn-sm" id="save-inter-transfer-btn">
                        <i class="bi bi-check-lg me-1"></i>Ara Transfer Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== YENİ REZERVASYON MODALı ===================== -->
<div class="modal fade modal-booking" id="addBookingModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Yeni Rezervasyon Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBookingForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="customer_id" id="add-customer-id" value="">

                <div class="modal-body" style="max-height:75vh;overflow-y:auto;">

                    <!-- Transfer & Araç -->
                    <h6 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Havalimanı, Transfer & Araç Seçimi</h6>
                    <div class="row mb-4 g-2">
                        <div class="col-md-4">
                            <label class="form-label">Havalimanı</label>
                            <select name="airport_id" id="add-airport" class="form-select airport-select">
                                <option value="">-- Havalimanı Seçin --</option>
                                <?php foreach ($airports as $ap): ?>
                                <option value="<?= $ap['id'] ?>" <?= $ap['is_default'] ? 'selected' : '' ?>><?= e($ap['name']) ?><?= $ap['code'] ? ' (' . e($ap['code']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transfer</label>
                            <select name="destination_id" id="add-destination" class="form-select destination-select">
                                <option value="">-- Transfer Seçin --</option>
                                <?php foreach ($destinations as $dest): ?>
                                <option value="<?= $dest['id'] ?>"><?= e($dest['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Araç</label>
                            <select name="vehicle_id" id="add-vehicle" class="form-select">
                                <option value="">-- Araç Seçin --</option>
                                <?php foreach ($allVehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= e($v['vehicle_name']) ?> (<?= (int)$v['capacity'] ?> kişi)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- Müşteri -->
                    <h6 class="mb-3"><i class="bi bi-person me-2"></i>Müşteri Bilgileri</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 customer-autocomplete-wrap">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" name="customer_name" id="add-customer-name" class="form-control" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');" required>
                            <div class="customer-autocomplete-list list-group shadow" style="display: none;"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="customer_email" id="add-customer-email" class="form-control" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');">
                        </div>
                        <div class="col-md-4 customer-autocomplete-wrap">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="customer_phone" id="add-customer-phone" class="form-control" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" onblur="this.setAttribute('readonly', 'readonly');">
                            <div class="customer-autocomplete-list list-group shadow" style="display: none;"></div>
                        </div>
                    </div>

                    <hr>

                    <!-- Arrival toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="add-has-outbound" name="has_outbound" value="1"
                            <?= $view !== 'return' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="add-has-outbound">
                            <i class="bi bi-box-arrow-in-down-right me-1 text-primary"></i>Arrival Transferi Ekle
                        </label>
                    </div>

                    <!-- ARRIVAL alanları -->
                    <div id="outbound-section" style="<?= $view !== 'return' ? '' : 'display:none;' ?>">
                        <h6 class="mb-3 text-primary"><i class="bi bi-box-arrow-in-down-right me-2"></i>Arrival Uçuş Bilgileri</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Uçuş Tarihi</label>
                                <input type="date" name="flight_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Uçuş Saati</label>
                                <input type="time" name="flight_time" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Uçuş Numarası</label>
                                <input type="text" name="flight_number" class="form-control flight-number-upper" style="text-transform:uppercase;">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <label class="form-label">Otel / Adres</label>
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <select name="hotel_address" id="add-hotel-address" class="form-select hotel-select">
                                            <option value=""></option>
                                            <?php foreach ($hotelOptions as $ho): ?>
                                            <option value="<?= e($ho['name'] . ($ho['address'] ? ' — ' . $ho['address'] : '')) ?>">
                                                <?= e($ho['name']) ?><?= $ho['distance_km'] !== null ? ' (' . number_format((float)$ho['distance_km'], 0) . ' km)' : '' ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-quick-hotel flex-shrink-0"
                                            data-target="add-hotel-address" title="Hızlı Otel Ekle" style="height:38px;">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Arrival Fiyatı</label>
                                <div class="input-group">
                                    <select name="currency" id="add-currency" class="form-select" style="max-width:90px;">
                                        <option value="TRY">₺ TRY</option>
                                        <option value="EUR" selected>€ EUR</option>
                                        <option value="USD">$ USD</option>
                                        <option value="GBP">£ GBP</option>
                                    </select>
                                    <input type="number" name="total_price" id="add-total-price" class="form-control" min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>

                    <!-- Depar toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="add-has-return" name="has_return" value="1"
                            <?= $view === 'return' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="add-has-return">
                            <i class="bi bi-box-arrow-up-right me-1 text-info"></i>Depar Transferi Ekle
                        </label>
                    </div>

                    <!-- DEPAR alanları -->
                    <div id="return-section" class="return-section mb-4" style="<?= $view === 'return' ? '' : 'display:none;' ?>">
                        <h6 class="mb-3 text-info"><i class="bi bi-box-arrow-up-right me-2"></i>Depar Uçuş Bilgileri</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Depar Uçuş Tarihi</label>
                                <input type="date" name="return_flight_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Depar Uçuş Saati</label>
                                <input type="time" name="return_flight_time" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Depar Uçuş Numarası</label>
                                <input type="text" name="return_flight_number" class="form-control flight-number-upper" style="text-transform:uppercase;">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-9">
                                <label class="form-label">Otel / Adres</label>
                                <div class="d-flex gap-2">
                                    <div class="flex-grow-1">
                                        <select name="return_hotel_address" id="add-return-hotel-address" class="form-select hotel-select">
                                            <option value=""></option>
                                            <?php foreach ($hotelOptions as $ho): ?>
                                            <option value="<?= e($ho['name'] . ($ho['address'] ? ' — ' . $ho['address'] : '')) ?>">
                                                <?= e($ho['name']) ?><?= $ho['distance_km'] !== null ? ' (' . number_format((float)$ho['distance_km'], 0) . ' km)' : '' ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-quick-hotel flex-shrink-0"
                                            data-target="add-return-hotel-address" title="Hızlı Otel Ekle" style="height:38px;">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Otelden Alış Saati</label>
                                <input type="time" name="return_pickup_time" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Depar Fiyatı</label>
                                <div class="input-group">
                                    <select name="return_currency" id="add-return-currency" class="form-select" style="max-width:90px;">
                                        <option value="TRY">₺ TRY</option>
                                        <option value="EUR" selected>€ EUR</option>
                                        <option value="USD">$ USD</option>
                                        <option value="GBP">£ GBP</option>
                                    </select>
                                    <input type="number" name="return_total_price" id="add-return-price" class="form-control" min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Yolcu -->
                    <h6 class="mb-3"><i class="bi bi-people me-2"></i>Yolcu Bilgileri</h6>
                    <input type="hidden" id="add-pax-capacity" value="20">
                    <div class="d-flex flex-wrap gap-4 mb-3">
                        <div>
                            <label class="form-label d-block small fw-semibold">Yetişkin</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="add-adults"><i class="bi bi-dash"></i></button>
                                <input type="number" name="adults" id="add-adults" class="form-control" value="1" min="1" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="add-adults"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label d-block small fw-semibold">Çocuk</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="add-children"><i class="bi bi-dash"></i></button>
                                <input type="number" name="children" id="add-children" class="form-control" value="0" min="0" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="add-children"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label d-block small fw-semibold">Çocuk Koltuğu</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="add-child-seat"><i class="bi bi-dash"></i></button>
                                <input type="number" name="child_seat" id="add-child-seat" class="form-control" value="0" min="0" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="add-child-seat"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label d-block small fw-semibold">Yükseltici</label>
                            <div class="pax-stepper input-group">
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="minus" data-target="add-booster"><i class="bi bi-dash"></i></button>
                                <input type="number" name="booster" id="add-booster" class="form-control" value="0" min="0" readonly>
                                <button type="button" class="btn btn-outline-secondary pax-btn" data-op="plus" data-target="add-booster"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="add-passenger-names" class="mb-4"></div>

                    <!-- Notlar -->
                    <h6 class="mb-3"><i class="bi bi-chat-text me-2"></i>Notlar</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Müşteri Notu</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admin Notu <small class="text-muted">(müşteri görmez)</small></label>
                            <textarea name="admin_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Rezervasyon Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== DIŞARIYA VERME MODALı ===================== -->
<div class="modal fade" id="outsourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#f6c23e,#d4a017);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-person-check me-2"></i>Dışarıya Verildi</h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="outsource-booking-id">

                <!-- Rezervasyon özeti -->
                <div class="rounded p-3 mb-4" style="background:#fffbf0;border:1px solid #f6c23e;">
                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Rezervasyon No</div>
                            <div class="fw-bold text-primary" id="ob-number">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Müşteri</div>
                            <div class="fw-semibold" id="ob-customer">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Tarih</div>
                            <div class="fw-semibold" id="ob-date">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Saat</div>
                            <div class="fw-semibold" id="ob-time">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Otel / Adres</div>
                            <div class="fw-semibold" id="ob-hotel">—</div>
                        </div>
                        <div class="col-6 col-md-3" id="ob-pickup-wrap" style="display:none;">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Otelden Alış Saati</div>
                            <div class="fw-bold" style="color:#1c4b56;" id="ob-pickup-time">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Araç</div>
                            <div class="fw-semibold" id="ob-vehicle">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Kişi</div>
                            <div class="fw-semibold" id="ob-pax">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Uçuş No</div>
                            <div class="fw-semibold" id="ob-flight">—</div>
                        </div>
                    </div>
                    <div class="pt-2 mt-1" style="border-top:1px dashed #f6c23e;">
                        <span class="text-muted" style="font-size:.75rem;">Rezervasyon Tutarı</span>
                        <span id="ob-price" class="fw-bold ms-2" style="font-size:1.5rem;color:#1c4b56;letter-spacing:.5px;">—</span>
                    </div>
                </div>

                <!-- Inputlar: mobilde alt alta, desktop tek satır -->
                <div class="row g-2 align-items-end">
                    <!-- Ad Soyad / Firma -->
                    <div class="col-12 col-md" style="min-width:200px; max-width:260px;">
                        <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Ad Soyad / Firma</label>
                        <div class="d-flex gap-1">
                            <div class="flex-grow-1" style="min-width: 0;">
                                <select id="outsource-name-input" class="form-select form-select-sm">
                                    <option value=""></option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm flex-shrink-0" id="btn-add-partner" title="Yeni kişi/firma ekle">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Otelden Alış Saati (sadece depar) -->
                    <div class="col-6 col-md-auto" id="outsource-pickup-wrap" style="display:none;">
                        <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Alış Saati</label>
                        <input type="time" id="outsource-pickup-input" class="form-control form-control-sm" style="width:90px;">
                    </div>
                    <!-- Tutar + Para Birimi -->
                    <div class="col-6 col-md-auto">
                        <label class="form-label fw-semibold mb-1" style="font-size:.82rem;">Tutar</label>
                        <div class="input-group input-group-sm flex-nowrap" style="width:150px;">
                            <select id="outsource-currency-input" class="form-select" style="max-width:75px; padding-left:0.4rem; padding-right:1.2rem;">
                                <option value="TRY">₺ TRY</option>
                                <option value="EUR" selected>€ EUR</option>
                                <option value="USD">$ USD</option>
                                <option value="GBP">£ GBP</option>
                            </select>
                            <input type="number" id="outsource-price-input" class="form-control" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <!-- WP Mesaj At ve Tahsilat Yok -->
                    <div class="col-12 col-md-auto d-flex flex-wrap" id="outsource-wa-wrap">
                        <div class="d-flex align-items-center  border rounded px-2" style="height:32px;background:#fff5f5;border-color:#dc3545 !important;white-space:nowrap;width:fit-content;">
                            <div class="form-check form-switch mb-0" style="padding-left:2.2em;min-height:auto;">
                                <input class="form-check-input mt-0" type="checkbox" id="outsource-no-collection" style="cursor:pointer;width:1.8em;height:.9em;">
                            </div>
                            <label for="outsource-no-collection" class="fw-bold mb-0" style="cursor:pointer;color:#dc3545;font-size:.8rem;line-height:1;">
                                Tahsilat Yok
                            </label>
                        </div>
                        <div class="d-flex align-items-center border rounded px-2" style="height:32px;background:#f6fff8;border-color:#25d366 !important;white-space:nowrap;width:fit-content;">
                            <div class="form-check form-switch mb-0" style="padding-left:2.2em;min-height:auto;">
                                <input class="form-check-input mt-0" type="checkbox" id="outsource-wa-toggle" style="cursor:pointer;width:1.8em;height:.9em;">
                            </div>
                            <label for="outsource-wa-toggle" class="fw-bold mb-0" style="cursor:pointer;color:#25d366;font-size:.8rem;line-height:1;">
                                <i class="bi bi-whatsapp"></i> WP Mesaj At
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-warning fw-bold" id="outsource-save-btn">
                    <i class="bi bi-check-lg me-1"></i>Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== HIZLI OTEL EKLEME MODALı ===================== -->
<div class="modal fade" id="quickHotelModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-building-add me-2"></i>Hızlı Otel Ekle</h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="quick-hotel-target">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Otel Adı <span class="text-danger">*</span></label>
                    <input type="text" id="quick-hotel-name" class="form-control" placeholder="Otel adı...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Adres <small class="text-muted fw-normal">(isteğe bağlı)</small></label>
                    <input type="text" id="quick-hotel-address" class="form-control" placeholder="Mahalle, bölge, ilçe...">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mesafe (km) <small class="text-muted fw-normal">(isteğe bağlı)</small></label>
                        <input type="number" id="quick-hotel-distance" class="form-control" min="0" step="0.1" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telefon <small class="text-muted fw-normal">(isteğe bağlı)</small></label>
                        <input type="text" id="quick-hotel-phone" class="form-control" placeholder="+90...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-success fw-bold" id="quick-hotel-save-btn">
                    <i class="bi bi-plus-lg me-1"></i>Ekle ve Seç
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== HIZLI PARTNER EKLEME MODALı ===================== -->
<div class="modal fade" id="quickPartnerModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#f6c23e,#d4a017);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Yeni Kişi / Firma</h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ad Soyad / Firma <span class="text-danger">*</span></label>
                    <input type="text" id="quick-partner-name" class="form-control" placeholder="Ad soyad veya firma...">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Telefon <small class="text-muted fw-normal">(isteğe bağlı)</small></label>
                    <input type="text" id="quick-partner-phone" class="form-control" placeholder="+90...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-warning fw-bold btn-sm" id="quick-partner-save-btn">
                    <i class="bi bi-plus-lg me-1"></i>Ekle ve Seç
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== ŞÖFÖR MODAL ===================== -->
<div class="modal fade" id="driverSelectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>İşi Yapan Şöförü Seç</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="driver-modal-booking-id" value="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Şöför *</label>
                    <select id="driver-modal-select" class="form-select" required>
                        <option value="">-- Şöför Seç --</option>
                    </select>
                </div>
                <!-- Tahsilat Yok ve WP Mesaj At (Dışarıya Verildi penceresiyle aynı) -->
                <div class="d-flex flex-wrap gap-2">
                    <div class="d-flex align-items-center border rounded px-2" style="height:32px;background:#fff5f5;border-color:#dc3545 !important;white-space:nowrap;width:fit-content;">
                        <div class="form-check form-switch mb-0" style="padding-left:2.2em;min-height:auto;">
                            <input class="form-check-input mt-0" type="checkbox" id="driver-no-collection" style="cursor:pointer;width:1.8em;height:.9em;">
                        </div>
                        <label for="driver-no-collection" class="fw-bold mb-0" style="cursor:pointer;color:#dc3545;font-size:.8rem;line-height:1;">
                            Tahsilat Yok
                        </label>
                    </div>
                    <div class="d-flex align-items-center border rounded px-2" style="height:32px;background:#f6fff8;border-color:#25d366 !important;white-space:nowrap;width:fit-content;">
                        <div class="form-check form-switch mb-0" style="padding-left:2.2em;min-height:auto;">
                            <input class="form-check-input mt-0" type="checkbox" id="driver-wa-toggle" style="cursor:pointer;width:1.8em;height:.9em;" checked>
                        </div>
                        <label for="driver-wa-toggle" class="fw-bold mb-0" style="cursor:pointer;color:#25d366;font-size:.8rem;line-height:1;">
                            <i class="bi bi-whatsapp"></i> WP Mesaj At
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary btn-sm" id="driver-modal-save-btn">
                    <i class="bi bi-check-lg me-1"></i>Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== İŞ DURUMU MODALı ===================== -->
<div class="modal fade" id="opsStatusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-clipboard-check me-2"></i>İş Durumu</h5>
                <button type="button" class="btn-close" style="filter:brightness(0) invert(1);" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Rezervasyon Bilgileri -->
                <div class="rounded p-3 mb-4" style="background:#f0f7ff;border:1px solid #b8d4f0;">
                    <div class="row g-2" id="ops-booking-info">
                        <div class="col-12 text-center py-2"><span class="spinner-border spinner-border-sm"></span></div>
                    </div>
                </div>
                <!-- İş Durumu -->
                <div id="ops-status-body">
                    <div class="text-center py-3"><span class="spinner-border spinner-border-sm"></span></div>
                </div>
                <!-- Şöför Seçimi -->
                <div id="ops-driver-section" class="mt-4 pt-4" style="border-top:1px dashed #b8d4f0; display:none;">
                    <h6 class="fw-bold mb-3"><i class="bi bi-person-badge me-2 text-primary"></i>Şöför Ata</h6>
                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-md-6">
                            <select id="ops-modal-driver" class="form-select" data-booking-id="">
                                <option value="">-- Şöför Seç --</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex flex-wrap gap-2">
                            <!-- Tahsilat Yok (Dışarıya Verildi penceresiyle aynı) -->
                            <div class="d-flex align-items-center border rounded px-2" style="height:38px;background:#fff5f5;border-color:#dc3545 !important;white-space:nowrap;width:fit-content;">
                                <div class="form-check form-switch mb-0" style="padding-left:2.2em;min-height:auto;">
                                    <input class="form-check-input mt-0" type="checkbox" id="ops-modal-no-collection" style="cursor:pointer;width:1.8em;height:.9em;">
                                </div>
                                <label for="ops-modal-no-collection" class="fw-bold mb-0" style="cursor:pointer;color:#dc3545;font-size:.8rem;line-height:1;">
                                    Tahsilat Yok
                                </label>
                            </div>
                            <!-- WP Mesaj At Toggle -->
                            <div class="d-flex align-items-center border rounded px-2" style="height:38px;background:#f6fff8;border-color:#25d366 !important;white-space:nowrap;width:fit-content;">
                                <div class="form-check form-switch mb-0" style="padding-left:2.2em;min-height:auto;">
                                    <input class="form-check-input mt-0" type="checkbox" id="ops-modal-driver-wa-toggle" style="cursor:pointer;width:1.8em;height:.9em;" checked>
                                </div>
                                <label for="ops-modal-driver-wa-toggle" class="fw-bold mb-0" style="cursor:pointer;color:#25d366;font-size:.8rem;line-height:1;">
                                    <i class="bi bi-whatsapp"></i> WP Mesaj At
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="ops-modal-driver-save">
                        <i class="bi bi-check-lg me-1"></i>Kaydet
                    </button>
                    <span class="ops-modal-driver-status ms-2 small" style="display:none;"></span>
                </div>
            </div>
        </div>
    </div>
<!-- ===================== SİLME ONAY MODALı ===================== -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Rezervasyon Sil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="delete-modal-body">
                <!-- JS ile doldurulacak -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>İptal
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="confirm-delete-btn">
                    <i class="bi bi-trash me-1"></i>Seçilenleri Sil
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$dirWhereJs = str_replace('b.booking_direction', 'b2.booking_direction', $dirWhere);
$jsBookings = $db->query("
    SELECT b.*,
           t.title AS tour_title,
           d.title AS destination_title,
           CONCAT(v.brand,' ',v.model) AS vehicle_name,
           COALESCE(v.capacity, 20) AS vehicle_capacity
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    LEFT JOIN destinations d ON b.destination_id = d.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.is_deleted = 0 AND ( (1=1 $dirWhere) OR b.id IN (
        SELECT COALESCE(b2.related_booking_id, 0)
        FROM bookings b2
        WHERE b2.is_deleted = 0 $dirWhereJs
    ) )
    ORDER BY b.created_at DESC
")->fetchAll();

try {
    $jsInter = $db->query("
        SELECT b.id, b.booking_number, 'transfer' AS booking_type, b.booking_status, 'inter_transfer' AS booking_direction,
               b.destination_id, b.vehicle_id, b.customer_name, b.customer_email, b.customer_phone,
               b.pickup_hotel_name AS pickup_location, b.pickup_date, b.pickup_time,
               b.destination_hotel_name AS hotel_address,
               NULL AS flight_date, NULL AS flight_time, NULL AS flight_number, NULL AS return_time,
               b.adults, b.children, b.child_seat, b.booster, b.total_price, b.currency, b.notes, b.admin_notes,
               b.is_completed, b.is_outsourced, b.outsource_price, b.outsource_currency, b.created_at, b.updated_at,
               b.outsource_name, b.outsource_pickup_time, b.outsource_partner_id, b.customer_id,
               1 AS is_inter_transfer,
               d.title AS destination_title,
               CONCAT(v.brand,' ',v.model) AS vehicle_name,
               COALESCE(v.capacity, 20) AS vehicle_capacity
        FROM inter_hotel_transfers b
        LEFT JOIN destinations d ON b.destination_id = d.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.is_deleted = 0
        ORDER BY b.created_at DESC
    ")->fetchAll();
    if (!empty($jsInter)) {
        $jsBookings = array_merge($jsBookings, $jsInter);
    }
} catch (Exception $e) {}
?>

<script>
const bookingsData = <?= json_encode(array_map(function($b) {
    return [
        'id'                => $b['id'],
        'customer_id'       => (int)($b['customer_id'] ?? 0),
        'booking_number'    => $b['booking_number'],
        'booking_type'      => $b['booking_type'],
        'booking_direction' => $b['booking_direction'] ?? 'outbound',
        'booking_status'    => $b['booking_status'],
        'customer_name'     => $b['customer_name'],
        'customer_email'    => $b['customer_email'],
        'customer_phone'    => $b['customer_phone'],
        'tour_title'        => $b['tour_title'] ?? null,
        'destination_title' => $b['destination_title'] ?? null,
        'airport_id'        => (int)($b['airport_id'] ?? 0),
        'destination_id'    => (int)($b['destination_id'] ?? 0),
        'vehicle_id'        => (int)($b['vehicle_id'] ?? 0),
        'vehicle_name'      => trim($b['vehicle_name'] ?? ''),
        'pickup_location'   => $b['pickup_location'] ?? null,
        'pickup_date'       => $b['pickup_date'] ?? null,
        'pickup_time'       => $b['pickup_time'] ?? null,
        'return_time'       => $b['return_time'] ?? null,
        'flight_date'       => $b['flight_date'] ?? null,
        'flight_time'       => $b['flight_time'] ?? null,
        'flight_number'     => $b['flight_number'] ?? null,
        'hotel_address'     => $b['hotel_address'] ?? null,
        'adults'            => (int)($b['adults'] ?? 1),
        'children'          => (int)($b['children'] ?? 0),
        'child_seat'        => (int)($b['child_seat'] ?? 0),
        'booster'           => (int)($b['booster'] ?? 0),
        'vehicle_capacity'  => (int)($b['vehicle_capacity'] ?? 20),
        'total_price'       => (float)($b['total_price'] ?? 0),
        'currency'          => $b['currency'] ?? 'EUR',
        'notes'                => $b['notes'] ?? null,
        'admin_notes'          => $b['admin_notes'] ?? null,
        'outsource_partner_id' => (int)($b['outsource_partner_id'] ?? 0),
        'outsource_name'       => $b['outsource_name'] ?? '',
        'created_at'           => $b['created_at'] ?? null,
        'related_booking_id'   => (int)($b['related_booking_id'] ?? 0),
        'is_inter_transfer'    => (int)($b['is_inter_transfer'] ?? 0),
    ];
}, $jsBookings), JSON_UNESCAPED_UNICODE) ?>;

const defaultAirportId = <?= (int)$defaultAirportId ?>;

const currentView = '<?= $view ?>';
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
    var table = window.bookingsTable = $('#bookingsTable').DataTable({
        // Türkçe metinler sayfada (assets/js/dt-tr.js); yoksa eskisi gibi internetten
        language: window.DT_TR || {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
        },
        pageLength: 25,
        // İlk sıralama doğrudan tarih (aşağıdaki order([[1,'asc']]) ile aynı). Dil metinleri artık sayfada olduğu için
        // tablo hemen çiziliyor; bu satır olmasa ilk çizim "Yön"e göre sıralanır ve aynı günün kayıtlarının sırası değişirdi.
        // Eskisi gibi: aynı tarihteki kayıtlar sunucudan geldiği sırada (en yeni kayıt üstte) kalır.
        order: (currentView === 'all' || currentView === 'daily' || currentView === 'arrival' || currentView === 'return') ? [[1, 'asc']] : [[0, 'asc']],
        autoWidth: false,
        responsive: {
            // Pencere 1280 px'ten darsa (telefon dik/yan, tablet) satırlar telefon düzeninde: 4 ana sütun + dokununca açılan ayrıntı.
            // Varsayılan ayarda 481–1279 px arası 13 sütunun hepsi zorla gösteriliyordu; sığmayınca tablo yana kayıyordu.
            // 1280 px ve üzeri (masaüstü) eskisi gibi 13 sütun.
            breakpoints: [
                { name: 'desktop',  width: Infinity },
                { name: 'tablet-l', width: 1281 },
                { name: 'tablet-p', width: 1280 },
                { name: 'mobile-l', width: 1279 },
                { name: 'mobile-p', width: 320 }
            ],
            details: {
                renderer: function (api, rowIdx, columns) {
                    var data = $.map(columns, function (col, i) {
                        var title = col.title ? col.title.replace(/(<([^>]+)>)/gi, "").trim() : '';
                        var extraClass = '';
                        if (title === 'İş Durumu') {
                            extraClass = ' dtr-status-cell';
                        } else if (title.includes('Durum') || title.includes('İşlem')) {
                            extraClass = ' dtr-action-cell';
                        } else if (title.includes('Otel') || title.includes('Adı') || title.includes('Bölge')) {
                            extraClass = ' dtr-info-full';
                        } else if (title.includes('Yön') || title.includes('Tarih') || title.includes('Saat') || title.includes('Müşteri') || title.includes('Kişi')) {
                            extraClass = ' dtr-info-cell';
                        } else if (title.includes('Tutar')) {
                            extraClass = ' dtr-price-cell';
                        }
                        return '<li class="' + extraClass + '" data-dtr-index="'+col.columnIndex+'">'+
                                 '<span class="dtr-title">'+col.title+'</span> '+
                                 '<span class="dtr-data">'+col.data+'</span>'+
                               '</li>';
                    }).join('');
                    return data ? $('<ul class="dtr-details custom-dtr-details"/>').append(data) : false;
                }
            }
        }
    });

    // Accordion: bir satır açılınca diğer açık satırları kapat
    $('#bookingsTable tbody').on('click', 'td.dtr-control', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        // Diğer açık child row'ları kapat
        table.rows('.parent').every(function() {
            if (this.index() !== row.index()) {
                this.child.hide();
                $(this.node()).removeClass('parent');
            }
        });
    });

    // Tüm görünümlerde tarihe göre artan sıra
    if (currentView === 'all' || currentView === 'daily' || currentView === 'arrival' || currentView === 'return') {
        table.order([[1, 'asc']]).draw();
    }
    // all/daily/return: Yön(0) Tarih(1) Saat(2) AlışSaati(3) Müşteri(4) Kişi(5) Otel(6) Araç(7) Tutar(8) İşDurumu(9) Durum(10) İşlem(11)
    // arrival:      Yön(0) Tarih(1) Saat(2) Müşteri(3) Kişi(4) Otel(5) Araç(6) Tutar(7) İşDurumu(8) Durum(9) İşlem(10)
    const dateColIdxs  = [1];
    const statusColIdx = currentView === 'arrival' ? 10 : 11;

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'bookingsTable') return true;
        const fd = $('#filter-date').val();
        if (fd) {
            var matched = false;
            for (var i = 0; i < dateColIdxs.length; i++) {
                var m = (data[dateColIdxs[i]] || '').match(/(\d{2})\.(\d{2})\.(\d{4})/);
                if (m) {
                    var rowDate = m[3]+'-'+m[2]+'-'+m[1];
                    if (currentView === 'daily') {
                        if (rowDate === fd) { matched = true; break; }
                    } else {
                        if (rowDate >= fd) { matched = true; break; }
                    }
                }
            }
            if (!matched) return false;
        }
        const fs = $('#filter-status').val();
        if (fs) {
            var rowNode = settings.aoData[dataIndex].nTr;
            var isCompleted = $(rowNode).find('input[data-field="is_completed"]').is(':checked');
            var isOutsourced = $(rowNode).find('input[data-field="is_outsourced"]').is(':checked');
            if (fs === 'yapilmadi') {
                if (isCompleted || isOutsourced) return false;
            } else if (fs === 'is_yapildi') {
                if (!isCompleted) return false;
            } else if (fs === 'disariya_verildi') {
                if (!isOutsourced) return false;
            }
        }
        return true;
    });

    // Tüm sekmelerde bugünü default olarak seç
    var today = new Date();
    var todayStr = today.getFullYear() + '-'
        + String(today.getMonth() + 1).padStart(2, '0') + '-'
        + String(today.getDate()).padStart(2, '0');
    $('#filter-date').val(todayStr);
    table.draw();

    $('#filter-date, #filter-status').on('change', function() { table.draw(); });

    // Önceki / Sonraki gün butonları
    function shiftDate(days) {
        var cur = $('#filter-date').val();
        var d = cur ? new Date(cur + 'T00:00:00') : new Date();
        d.setDate(d.getDate() + days);
        var newVal = d.getFullYear() + '-'
            + String(d.getMonth() + 1).padStart(2, '0') + '-'
            + String(d.getDate()).padStart(2, '0');
        $('#filter-date').val(newVal).trigger('change');
    }
    $('#date-prev').on('click', function() { shiftDate(-1); });
    $('#date-next').on('click', function() { shiftDate(1); });
    $('#date-clear').on('click', function() {
        $('#filter-date').val('').trigger('change');
    });

    $('#filter-clear').on('click', function() {
        <?php if ($view === 'daily'): ?>
        $('#filter-status').val('yapilmadi');
        <?php else: ?>
        $('#filter-status').val('');
        <?php endif; ?>
        table.draw();
    });
    table.draw();

    // ─── Otel Select2 (Yeni rezervasyon modalı) ───────────────────────────────
    $('.hotel-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Otel seçin veya yazın...',
        allowClear: true,
        tags: true,
        dropdownParent: $('#addBookingModal'),
        createTag: function(params) {
            return { id: params.term, text: params.term, newTag: true };
        }
    });

    // ─── Ara transfer modalı select2'ları ─────────────────────────────────────
    $('.hotel-select-inter').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Otel seçin veya yazın...',
        allowClear: true,
        tags: true,
        dropdownParent: $('#addInterTransferModal'),
        createTag: function(params) {
            return { id: params.term, text: params.term, newTag: true };
        }
    });

    // ─── Düzenleme modalı select2'ları ────────────────────────────────────────
    $('.hotel-select-edit').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Otel seçin veya yazın...',
        allowClear: true,
        tags: true,
        dropdownParent: $('#bookingModal'),
        createTag: function(params) {
            return { id: params.term, text: params.term, newTag: true };
        }
    });

    $('.inter-destination-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Transfer Seçin --',
        allowClear: true,
        dropdownParent: $('#addInterTransferModal')
    });

    $('#add-airport, #add-destination').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#addBookingModal')
    });

    $('#modal-airport, #modal-destination, #modal-vehicle').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#bookingModal')
    });
});

// ─── Uçuş numarası inputlarını otomatik büyük harf yap ──────────────────────
document.addEventListener('input', function(e) {
    if (e.target && e.target.classList && e.target.classList.contains('flight-number-upper')) {
        var pos = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        try { e.target.setSelectionRange(pos, pos); } catch (err) {}
    }
});

// ─── Arrival toggle ────────────────────────────────────────────────────────────
document.getElementById('add-has-outbound').addEventListener('change', function() {
    document.getElementById('outbound-section').style.display = this.checked ? 'block' : 'none';
    // En az biri seçili olmalı
    if (!this.checked && !document.getElementById('add-has-return').checked) {
        document.getElementById('add-has-return').checked = true;
        document.getElementById('add-has-return').dispatchEvent(new Event('change'));
    }
});

// ─── Depar toggle ────────────────────────────────────────────────────────────
document.getElementById('add-has-return').addEventListener('change', function() {
    document.getElementById('return-section').style.display = this.checked ? 'block' : 'none';
    // Depar açılınca otel adresini otomatik kopyala
    if (this.checked) {
        var gelisVal = $('#add-hotel-address').val();
        if (gelisVal && !$('#add-return-hotel-address').val()) {
            $('#add-return-hotel-address').val(gelisVal).trigger('change');
        }
    }
    // En az biri seçili olmalı
    if (!this.checked && !document.getElementById('add-has-outbound').checked) {
        document.getElementById('add-has-outbound').checked = true;
        document.getElementById('add-has-outbound').dispatchEvent(new Event('change'));
    }
});

// ─── Müşteri adı → 1. yolcu ismi ──────────────────────────────────────────────
document.getElementById('add-customer-name').addEventListener('input', function() {
    var firstPax = document.querySelector('#add-passenger-names input[name="passenger_adult_name[]"]');
    if (firstPax) firstPax.value = this.value;
});

// ─── Müşteri Otomatik Tamamlama (Autocomplete) ────────────────────────────────
function initCustomerAutocomplete(inputId, phoneId, emailId, hiddenId) {
    const nameInput = document.getElementById(inputId);
    const phoneInput = document.getElementById(phoneId);
    const emailInput = document.getElementById(emailId);
    const hiddenInput = document.getElementById(hiddenId);

    if (!nameInput || !phoneInput) return;

    let isSelecting = false;

    function hideAllLists() {
        document.querySelectorAll('.customer-autocomplete-list').forEach(l => {
            l.style.display = 'none';
            l.innerHTML = '';
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function selectCustomer(cust) {
        isSelecting = true;
        nameInput.value = cust.name || '';
        phoneInput.value = cust.phone || '';
        if (emailInput) emailInput.value = cust.email || '';
        hiddenInput.value = cust.id;
        
        // Tetikleyici olayları çalıştır (ad-customer-name gibi input dinleyicileri için)
        nameInput.dispatchEvent(new Event('input'));
        
        // Yarış durumunu önlemek için debounce sıfırlama zamanlayıcısını temizle
        if (typeof debounceTimer !== 'undefined') {
            clearTimeout(debounceTimer);
        }
        
        isSelecting = false;
        hideAllLists();
    }

    function handleInput(e, inputEl) {
        if (isSelecting) return;

        const query = inputEl.value.trim();
        const listEl = inputEl.parentNode.querySelector('.customer-autocomplete-list');
        
        if (query.length < 2) {
            if (listEl) {
                listEl.style.display = 'none';
                listEl.innerHTML = '';
            }
            return;
        }

        hiddenInput.value = '';

        const fd = new FormData();
        fd.append('action', 'search_customers');
        fd.append('q', query);
        fd.append('csrf_token', csrfToken);

        fetch(apiUrl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data && res.data.customers && res.data.customers.length > 0) {
                    listEl.innerHTML = '';
                    res.data.customers.forEach(cust => {
                        const item = document.createElement('div');
                        item.className = 'customer-autocomplete-item';
                        
                        let details = [];
                        if (cust.phone) details.push(cust.phone);
                        if (cust.email) details.push(cust.email);
                        const detailStr = details.length > 0 ? ` (${details.join(' - ')})` : '';
                        
                        item.innerHTML = `<strong>${escapeHtml(cust.name)}</strong>${escapeHtml(detailStr)}`;
                        
                        item.addEventListener('click', function() {
                            selectCustomer(cust);
                        });
                        listEl.appendChild(item);
                    });
                    listEl.style.display = 'block';
                } else {
                    listEl.style.display = 'none';
                    listEl.innerHTML = '';
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    let debounceTimer;
    function debounceInput(e, inputEl) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => handleInput(e, inputEl), 250);
    }

    nameInput.addEventListener('input', function(e) {
        debounceInput(e, this);
    });

    phoneInput.addEventListener('input', function(e) {
        debounceInput(e, this);
    });

    document.addEventListener('click', function(e) {
        if (!nameInput.contains(e.target) && !phoneInput.contains(e.target) && !e.target.closest('.customer-autocomplete-list')) {
            hideAllLists();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideAllLists();
        }
    });
}

initCustomerAutocomplete('add-customer-name', 'add-customer-phone', 'add-customer-email', 'add-customer-id');
initCustomerAutocomplete('modal-customer-name', 'modal-customer-phone', 'modal-customer-email', 'modal-customer-id');
initCustomerAutocomplete('inter-customer-name', 'inter-customer-phone', 'inter-customer-email', 'inter-customer-id');

// ─── Ara Transfer Müşteri adı → 1. yolcu ismi ─────────────────────────────
document.getElementById('inter-customer-name')?.addEventListener('input', function() {
    var firstPax = document.querySelector('#inter-passenger-names-container input[name="passenger_adult_name[]"]');
    if (firstPax) firstPax.value = this.value;
});

// ─── Arrival otel adresi → depar otel adresi (depar açıksa) ────────────────────
$('#add-hotel-address').on('change', function() {
    if (document.getElementById('add-has-return').checked) {
        $('#add-return-hotel-address').val($(this).val()).trigger('change');
    }
});

// Edit modal Arrival toggle
document.getElementById('modal-has-outbound').addEventListener('change', function() {
    document.getElementById('modal-outbound-section').style.display = this.checked ? 'block' : 'none';
    if (!this.checked && !document.getElementById('modal-has-return').checked) {
        document.getElementById('modal-has-return').checked = true;
        document.getElementById('modal-has-return').dispatchEvent(new Event('change'));
    }
});

// Edit modal Depar toggle
document.getElementById('modal-has-return').addEventListener('change', function() {
    document.getElementById('modal-return-section').style.display = this.checked ? 'block' : 'none';
    if (this.checked) {
        var gelisVal = $('#modal-hotel-address').val();
        if (gelisVal && !$('#modal-return-hotel-address').val()) {
            $('#modal-return-hotel-address').val(gelisVal).trigger('change');
        }
    }
    if (!this.checked && !document.getElementById('modal-has-outbound').checked) {
        document.getElementById('modal-has-outbound').checked = true;
        document.getElementById('modal-has-outbound').dispatchEvent(new Event('change'));
    }
});

// Arrival otel adresi → depar otel adresi (depar açıksa) edit modal için
$('#modal-hotel-address').on('change', function() {
    if (document.getElementById('modal-has-return').checked) {
        $('#modal-return-hotel-address').val($(this).val()).trigger('change');
    }
});

// ─── Detay Modalı ─────────────────────────────────────────────────────────────
function openBookingModal(id) {
    const b = bookingsData.find(x => x.id == id);
    if (!b) return;

    if (b.is_inter_transfer === 1) {
        openEditInterTransferModal(id);
        return;
    }

    document.getElementById('modal-booking-id').value = b.id;
    document.getElementById('modal-booking-number').textContent = '#' + b.booking_number;
    document.getElementById('modal-booking-type').innerHTML = b.booking_type === 'tour'
        ? '<span class="badge bg-primary fs-6">Tur</span>'
        : '<span class="badge bg-info fs-6">Transfer</span>';
    document.getElementById('modal-created-at').textContent =
        b.created_at ? new Date(b.created_at).toLocaleString('tr-TR') : '-';

    const statusEl = document.getElementById('status-' + b.booking_status);
    if (statusEl) statusEl.checked = true;

    document.getElementById('modal-customer-id').value    = b.customer_id    || '';
    document.getElementById('modal-customer-name').value  = b.customer_name  || '';
    document.getElementById('modal-customer-email').value = b.customer_email || '';
    document.getElementById('modal-customer-phone').value = b.customer_phone || '';

    // Airport, Destination & Vehicle select'leri
    $('#modal-airport').val(b.airport_id || defaultAirportId).trigger('change');
    $('#modal-destination').val(b.destination_id || '').trigger('change');
    $('#modal-vehicle').val(b.vehicle_id || '').trigger('change');

    if (b.booking_type === 'tour') {
        document.getElementById('modal-tour-fields').style.display     = 'block';
        document.getElementById('modal-transfer-fields').style.display = 'none';
        document.getElementById('modal-pickup-location').value = b.pickup_location || '';
        document.getElementById('modal-pickup-date').value     = b.pickup_date     || '';
        document.getElementById('modal-pickup-time').value     = b.pickup_time     || '';
        document.getElementById('modal-return-time').value     = b.return_time     || '';

        document.getElementById('modal-total-price').value = b.total_price || 0;
        document.getElementById('modal-currency').value    = b.currency   || 'TRY';
    } else {
        document.getElementById('modal-tour-fields').style.display     = 'none';
        document.getElementById('modal-transfer-fields').style.display = 'block';

        let outboundBooking = null;
        let returnBooking = null;

        if (b.booking_direction === 'return') {
            returnBooking = b;
            if (b.related_booking_id) {
                outboundBooking = bookingsData.find(x => x.id == b.related_booking_id);
            }
        } else {
            outboundBooking = b;
            if (b.related_booking_id) {
                returnBooking = bookingsData.find(x => x.id == b.related_booking_id);
            }
        }

        document.getElementById('modal-outbound-id').value = outboundBooking ? outboundBooking.id : '';
        document.getElementById('modal-return-id').value   = returnBooking ? returnBooking.id : '';

        const hasOutbound = !!outboundBooking;
        const hasReturn   = !!returnBooking;

        document.getElementById('modal-has-outbound').checked = hasOutbound;
        document.getElementById('modal-has-return').checked   = hasReturn;

        document.getElementById('modal-outbound-section').style.display = hasOutbound ? 'block' : 'none';
        document.getElementById('modal-return-section').style.display   = hasReturn ? 'block' : 'none';

        if (outboundBooking) {
            document.getElementById('modal-flight-date').value   = outboundBooking.flight_date   || '';
            document.getElementById('modal-flight-time').value   = outboundBooking.flight_time   ? outboundBooking.flight_time.substring(0, 5) : '';
            document.getElementById('modal-flight-number').value = outboundBooking.flight_number || '';
            $('#modal-hotel-address').val(outboundBooking.hotel_address || '').trigger('change');
            document.getElementById('modal-total-price').value   = outboundBooking.total_price   || 0;
            document.getElementById('modal-currency').value      = outboundBooking.currency      || 'TRY';
        } else {
            document.getElementById('modal-flight-date').value   = '';
            document.getElementById('modal-flight-time').value   = '';
            document.getElementById('modal-flight-number').value = '';
            $('#modal-hotel-address').val('').trigger('change');
            document.getElementById('modal-total-price').value   = '';
            document.getElementById('modal-currency').value      = b.currency || 'EUR';
        }

        if (returnBooking) {
            document.getElementById('modal-return-flight-date').value   = returnBooking.flight_date   || '';
            document.getElementById('modal-return-flight-time').value   = returnBooking.flight_time   ? returnBooking.flight_time.substring(0, 5) : '';
            document.getElementById('modal-return-flight-number').value = returnBooking.flight_number || '';
            $('#modal-return-hotel-address').val(returnBooking.hotel_address || '').trigger('change');
            document.getElementById('modal-return-pickup-time').value   = returnBooking.pickup_time   ? returnBooking.pickup_time.substring(0, 5) : '';
            document.getElementById('modal-return-price').value         = returnBooking.total_price   || 0;
            document.getElementById('modal-return-currency').value      = returnBooking.currency      || 'TRY';
        } else {
            document.getElementById('modal-return-flight-date').value   = '';
            document.getElementById('modal-return-flight-time').value   = '';
            document.getElementById('modal-return-flight-number').value = '';
            $('#modal-return-hotel-address').val('').trigger('change');
            document.getElementById('modal-return-pickup-time').value   = '';
            document.getElementById('modal-return-price').value         = '';
            document.getElementById('modal-return-currency').value      = b.currency || 'EUR';
        }
    }

    // Kapasite limiti
    document.getElementById('edit-pax-capacity').value = b.vehicle_capacity || 20;

    // Stepper değerleri
    document.getElementById('modal-adults').value     = b.adults     || 1;
    document.getElementById('modal-children').value   = b.children   || 0;
    document.getElementById('modal-child-seat').value = b.child_seat || 0;
    document.getElementById('modal-booster').value    = b.booster    || 0;

    document.getElementById('modal-notes').value       = b.notes       || '';
    document.getElementById('modal-admin-notes').value = b.admin_notes || '';

    // Önce isim alanlarını sayıma göre çiz, sonra DB'den yolcuları yükle
    renderPassengerNames('edit', b.adults || 1, b.children || 0, []);
    loadPassengers(b.id);

    new bootstrap.Modal(document.getElementById('bookingModal')).show();
}

// ─── AJAX ─────────────────────────────────────────────────────────────────────
const apiUrl    = window.ADMIN_URL + '/api/handler.php?entity=bookings';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

<?php
$wpTemplatesRaw = $db->query("SELECT template_key, content FROM whatsapp_templates")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
const wpTemplates = <?php echo json_encode($wpTemplatesRaw ?: []); ?>;
const AIRPORT_NAME = <?php echo json_encode(getSetting('airport_name', 'ANTALYA AİRPORT AYT')); ?>;

const defaultWpTemplates = {
    'outsource_inter_transfer': "*ARA TRANSFER DETAYI*\n{ROUTE}\n*Tarih/Saat:* {PICKUP_DATE} - {PICKUP_TIME}\n*Alınış Oteli:* {PICKUP_HOTEL}\n*Varış Oteli:* {DESTINATION_HOTEL}\n*Kişi:* {PAX}\n\n*TAHSİLAT:* {PRICE}\n*HAKEDİŞ:* {PARTNER_HAKEDIS}\n\n*Yolcular:*\n{PASSENGERS}\n\n*Not:* {NOTES}",
    'driver_inter_transfer': "*SÜRÜCÜ ARA TRANSFER GÖREVİ*\nŞöför: {DRIVER_NAME}\n-----------------------------\n{ROUTE}\n*Alınış Tarihi / Saati:* {PICKUP_DATE} - {PICKUP_TIME}\n*Alınış Oteli:* {PICKUP_HOTEL}\n*Varış Oteli:* {DESTINATION_HOTEL}\n*Kişi:* {PAX}\n\n*TAHSİLAT:* {PRICE}\n\n*Yolcular:*\n{PASSENGERS}\n\n*Not:* {NOTES}"
};

function parseWhatsAppTemplate(templateKey, placeholders) {
    var raw = wpTemplates[templateKey] || defaultWpTemplates[templateKey] || '';
    if (!raw) return '';
    
    var msg = raw;
    for (var key in placeholders) {
        if (placeholders.hasOwnProperty(key)) {
            var val = placeholders[key] !== null && placeholders[key] !== undefined ? placeholders[key] : '';
            msg = msg.split(key).join(val);
        }
    }
    return msg;
}

function showToast(msg, ok) {
    const t = document.getElementById('ajaxToast');
    const b = document.getElementById('ajaxToastBody');
    t.className = 'toast align-items-center border-0 text-white ' + (ok ? 'bg-success' : 'bg-danger');
    b.textContent = msg;
    new bootstrap.Toast(t, {delay: 3500}).show();
}

function ajaxSubmit(form, onSuccess) {
    const fd  = new FormData(form);
    fd.append('csrf_token', csrfToken);
    const btn  = form.querySelector('[type="submit"]');
    const orig = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Kaydediliyor...'; }
    fetch(apiUrl, {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => { showToast(d.message, d.success); if (d.success && onSuccess) onSuccess(d); })
        .catch(() => showToast('Bir hata oluştu.', false))
        .finally(() => { if (btn) { btn.disabled = false; btn.innerHTML = orig; } });
}

document.getElementById('bookingEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    ajaxSubmit(this, function() {
        bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
        setTimeout(() => location.reload(), 200);
    });
});

document.getElementById('addBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    ajaxSubmit(this, function() {
        bootstrap.Modal.getInstance(document.getElementById('addBookingModal')).hide();
        setTimeout(() => location.reload(), 200);
    });
});

// ─── Ara Transfer Form Submission & Steppers & Edit ───────────────────────────
document.getElementById('addInterTransferForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('save-inter-transfer-btn');
    if (btn) btn.disabled = true;
    const fd = new FormData(this);
    const act = fd.get('action') || 'create';
    fetch(window.ADMIN_URL + '/api/handler.php?entity=inter_transfers&action=' + act, {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(d => {
        showToast(d.message, d.success);
        if (d.success) {
            var modalEl = document.getElementById('addInterTransferModal');
            if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
            setTimeout(() => location.reload(), 200);
        } else {
            if (btn) btn.disabled = false;
        }
    })
    .catch(() => {
        showToast('Bir hata oluştu.', false);
        if (btn) btn.disabled = false;
    });
});

function renderInterPassengerInputs() {
    const adultsInput = document.getElementById('inter-adults-input');
    const childrenInput = document.getElementById('inter-children-input');
    if (!adultsInput || !childrenInput) return;
    const adults = parseInt(adultsInput.value) || 1;
    const children = parseInt(childrenInput.value) || 0;
    const container = document.getElementById('inter-passenger-names-container');
    if (!container) return;

    const custName = document.querySelector('#addInterTransferForm input[name="customer_name"]')?.value || '';

    const existingAdults = Array.from(container.querySelectorAll('input[name="passenger_adult_name[]"]')).map(i => i.value);
    const existingChildren = Array.from(container.querySelectorAll('input[name="passenger_child_name[]"]')).map(i => i.value);

    let html = '';
    if (adults > 0) {
        html += '<div class="fw-semibold text-muted mb-1" style="font-size:.75rem;">YETİŞKİN YOLCULAR</div>';
        for (let i = 1; i <= adults; i++) {
            let val = existingAdults[i - 1] !== undefined ? existingAdults[i - 1] : '';
            if (i === 1 && !val && custName) val = custName;
            html += '<div class="mb-1">'
                + '<input type="text" name="passenger_adult_name[]" class="form-control form-control-sm" value="' + val.replace(/"/g, '&quot;') + '" placeholder="' + i + '. yetişkin adı soyadı (isteğe bağlı)">'
                + '</div>';
        }
    }
    if (children > 0) {
        html += '<div class="fw-semibold text-muted mt-2 mb-1" style="font-size:.75rem;">ÇOCUK YOLCULAR</div>';
        for (let i = 1; i <= children; i++) {
            let val = existingChildren[i - 1] !== undefined ? existingChildren[i - 1] : '';
            html += '<div class="mb-1">'
                + '<input type="text" name="passenger_child_name[]" class="form-control form-control-sm" value="' + val.replace(/"/g, '&quot;') + '" placeholder="' + i + '. çocuk adı soyadı (isteğe bağlı)">'
                + '</div>';
        }
    }
    container.innerHTML = html;
}

// Müşteri adı yazıldığında 1. yetişkin yolcuya otomatik kopyala
document.addEventListener('input', function(e) {
    if (e.target && e.target.name === 'customer_name' && e.target.closest('#addInterTransferForm')) {
        const firstPax = document.querySelector('#inter-passenger-names-container input[name="passenger_adult_name[]"]');
        if (firstPax) {
            firstPax.value = e.target.value;
        }
    }
});

function openEditInterTransferModal(id) {
    const b = bookingsData.find(x => x.id == id);
    if (!b) return;

    const modalEl = document.getElementById('addInterTransferModal');
    if (!modalEl) return;

    modalEl.querySelector('.modal-title').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Ara Transfer Düzenle (#' + b.booking_number + ')';
    const form = document.getElementById('addInterTransferForm');

    let actInp = form.querySelector('input[name="action"]');
    if (!actInp) {
        actInp = document.createElement('input');
        actInp.type = 'hidden';
        actInp.name = 'action';
        form.appendChild(actInp);
    }
    actInp.value = 'update';

    let idInp = form.querySelector('input[name="id"]');
    if (!idInp) {
        idInp = document.createElement('input');
        idInp.type = 'hidden';
        idInp.name = 'id';
        form.appendChild(idInp);
    }
    idInp.value = b.id;

    const saveBtn = document.getElementById('save-inter-transfer-btn');
    if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Ara Transfer Güncelle';

    if (form.querySelector('select[name="destination_id"]')) {
        $(form.querySelector('select[name="destination_id"]')).val(b.destination_id || '').trigger('change');
    }
    if (form.querySelector('select[name="vehicle_id"]')) {
        $(form.querySelector('select[name="vehicle_id"]')).val(b.vehicle_id || '').trigger('change');
    }
    if (form.querySelector('input[name="customer_name"]')) form.querySelector('input[name="customer_name"]').value = b.customer_name || '';
    if (form.querySelector('input[name="customer_email"]')) form.querySelector('input[name="customer_email"]').value = b.customer_email || '';
    if (form.querySelector('input[name="customer_phone"]')) form.querySelector('input[name="customer_phone"]').value = b.customer_phone || '';

    $('#inter-pickup-hotel-select').val(b.pickup_location || '').trigger('change');
    $('#inter-dest-hotel-select').val(b.hotel_address || '').trigger('change');

    if (form.querySelector('input[name="pickup_date"]')) form.querySelector('input[name="pickup_date"]').value = b.pickup_date || '';
    if (form.querySelector('input[name="pickup_time"]')) form.querySelector('input[name="pickup_time"]').value = b.pickup_time ? b.pickup_time.substring(0, 5) : '';
    if (form.querySelector('input[name="total_price"]')) form.querySelector('input[name="total_price"]').value = b.total_price || '0.00';
    if (form.querySelector('select[name="currency"]')) form.querySelector('select[name="currency"]').value = b.currency || 'EUR';

    if (document.getElementById('inter-adults-input')) document.getElementById('inter-adults-input').value = b.adults || 1;
    if (document.getElementById('inter-children-input')) document.getElementById('inter-children-input').value = b.children || 0;
    if (document.getElementById('inter-seat-input')) document.getElementById('inter-seat-input').value = b.child_seat || 0;
    if (document.getElementById('inter-booster-input')) document.getElementById('inter-booster-input').value = b.booster || 0;

    renderInterPassengerInputs();

    if (form.querySelector('textarea[name="notes"]')) form.querySelector('textarea[name="notes"]').value = b.notes || '';
    if (form.querySelector('textarea[name="admin_notes"]')) form.querySelector('textarea[name="admin_notes"]').value = b.admin_notes || '';

    const fdPax = new FormData();
    fdPax.append('action', 'get_passengers');
    fdPax.append('id', b.id);
    fdPax.append('csrf_token', csrfToken);

    fetch(window.ADMIN_URL + '/api/handler.php?entity=inter_transfers', {method:'POST', body:fdPax})
        .then(r => r.json())
        .then(d => {
            if (d.success && d.data && d.data.passengers) {
                const adults = d.data.passengers.filter(p => p.passenger_type === 'adult');
                const children = d.data.passengers.filter(p => p.passenger_type === 'child');
                const adultInps = document.querySelectorAll('#inter-passenger-names-container input[name="passenger_adult_name[]"]');
                const childInps = document.querySelectorAll('#inter-passenger-names-container input[name="passenger_child_name[]"]');
                adults.forEach((p, idx) => {
                    if (adultInps[idx]) adultInps[idx].value = p.full_name || '';
                });
                children.forEach((p, idx) => {
                    if (childInps[idx]) childInps[idx].value = p.full_name || '';
                });
            }
        }).catch(e => {});

    new bootstrap.Modal(modalEl).show();
}

document.addEventListener('click', function(e) {
    const stepBtn = e.target.closest('.inter-pax-step-btn');
    if (stepBtn) {
        const targetId = stepBtn.getAttribute('data-target');
        const action = stepBtn.getAttribute('data-action');
        const input = document.getElementById(targetId);
        if (input) {
            let val = parseInt(input.value) || 0;
            const min = parseInt(input.getAttribute('min')) || 0;
            const max = parseInt(input.getAttribute('max')) || 99;
            if (action === 'plus' && val < max) val++;
            if (action === 'minus' && val > min) val--;
            input.value = val;
            renderInterPassengerInputs();
        }
    }
});

document.getElementById('inter-adults-input')?.addEventListener('input', renderInterPassengerInputs);
document.getElementById('inter-children-input')?.addEventListener('input', renderInterPassengerInputs);
document.getElementById('addInterTransferModal')?.addEventListener('show.bs.modal', function(e) {
    // Sadece yeni ekle butonundan tetiklendiyse sıfırla
    if (e.relatedTarget) {
        this.querySelector('.modal-title').innerHTML = '<i class="bi bi-arrows-expand me-2"></i>Yeni Ara Transfer Ekle (Otelden Otele)';
        const form = document.getElementById('addInterTransferForm');
        form.reset();
        form.querySelector('input[name="action"]').value = 'create';
        const idInp = form.querySelector('input[name="id"]');
        if (idInp) idInp.value = '';
        const saveBtn = document.getElementById('save-inter-transfer-btn');
        if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Ara Transfer Oluştur';
    }
});
document.getElementById('addInterTransferModal')?.addEventListener('shown.bs.modal', function() {
    renderInterPassengerInputs();
});

$(document).on('change', '#inter-pickup-hotel-select', function() {
    var txt = $(this).find('option:selected').text();
    if ($(this).val()) {
        $('#inter-pickup-hotel-name').val(txt);
    }
});
$(document).on('change', '#inter-dest-hotel-select', function() {
    var txt = $(this).find('option:selected').text();
    if ($(this).val()) {
        $('#inter-dest-hotel-name').val(txt);
    }
});

function performDelete(ids) {
    if (!ids || ids.length === 0) return;
    const b = bookingsData.find(x => x.id == ids[0]);
    const isInter = b && (b.is_inter_transfer === 1 || b.booking_direction === 'inter_transfer');
    const targetDeleteApiUrl = isInter
        ? (window.ADMIN_URL + '/api/handler.php?entity=inter_transfers')
        : apiUrl;

    const fd = new FormData();
    fd.append('action', 'delete');
    ids.forEach(id => fd.append('ids[]', id));
    fd.append('csrf_token', csrfToken);
    fetch(targetDeleteApiUrl, {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            if (d.success) setTimeout(() => location.reload(), 200);
        })
        .catch(() => showToast('Bir hata oluştu.', false));
}

// ─── Rezervasyon detay kartı HTML'i oluştur ─────────────────────────────────
function buildBookingDetailCard(b, opts) {
    opts = opts || {};
    const isInter = b.is_inter_transfer === 1 || b.booking_direction === 'inter_transfer';
    const dirLabel = isInter ? 'Ara Transfer' : (b.booking_direction === 'return' ? 'Depar' : 'Arrival');
    const dirBadge = isInter ? 'info' : (b.booking_direction === 'return' ? 'info' : 'primary');
    
    const rawDate = isInter ? (b.pickup_date || b.flight_date) : b.flight_date;
    const dateStr = rawDate ? new Date(rawDate).toLocaleDateString('tr-TR') : '-';
    const timeStr = isInter ? (b.pickup_time ? b.pickup_time.substring(0, 5) : '') : (b.flight_time || '');
    const flightNo = b.flight_number || '-';
    const hotel = b.hotel_address || '-';
    const pickupHotel = b.pickup_location || '-';
    const price = b.total_price ? parseFloat(b.total_price).toFixed(2) + ' ' + (b.currency || 'EUR') : '-';
    const dest = b.destination_title || '-';

    let html = '<div class="card mb-2 border-' + dirBadge + '">';
    
    // Checkbox header (ilişkili booking'ler için)
    if (opts.checkbox) {
        html += '<div class="card-header bg-' + dirBadge + ' bg-opacity-10 py-2 d-flex align-items-center">';
        html += '<div class="form-check mb-0">';
        html += '<input class="form-check-input del-booking-check" type="checkbox" id="del-check-' + b.id + '" value="' + b.id + '" checked>';
        html += '<label class="form-check-label fw-bold" for="del-check-' + b.id + '">';
        html += '<span class="badge bg-' + dirBadge + ' me-1">' + dirLabel + '</span> #' + b.booking_number;
        html += '</label>';
        html += '</div>';
        html += '</div>';
    } else {
        html += '<div class="card-header bg-' + dirBadge + ' bg-opacity-10 py-2">';
        html += '<span class="badge bg-' + dirBadge + ' me-1">' + dirLabel + '</span>';
        html += '<strong>#' + b.booking_number + '</strong>';
        html += '</div>';
    }

    html += '<div class="card-body py-2 px-3 small">';
    html += '<div class="row g-1">';
    html += '<div class="col-6"><i class="bi bi-person me-1 text-muted"></i>' + (b.customer_name || '-') + '</div>';
    html += '<div class="col-6"><i class="bi bi-geo-alt me-1 text-muted"></i>' + dest + '</div>';
    html += '<div class="col-6"><i class="bi bi-calendar me-1 text-muted"></i>' + dateStr + (timeStr ? ' ' + timeStr : '') + '</div>';
    
    if (isInter) {
        html += '<div class="col-6"><i class="bi bi-building-up me-1 text-muted"></i>Alınış: ' + pickupHotel + '</div>';
        html += '<div class="col-6"><i class="bi bi-building-down me-1 text-muted"></i>Varış: ' + hotel + '</div>';
    } else {
        html += '<div class="col-6"><i class="bi bi-airplane me-1 text-muted"></i>' + flightNo + '</div>';
        if (b.booking_direction === 'return' && b.pickup_time) {
            html += '<div class="col-6"><i class="bi bi-clock me-1 text-muted"></i>Alış: ' + b.pickup_time.substring(0, 5) + '</div>';
        }
        html += '<div class="col-6"><i class="bi bi-building me-1 text-muted"></i>' + hotel + '</div>';
    }
    
    html += '<div class="col-6"><i class="bi bi-currency-exchange me-1 text-muted"></i><strong>' + price + '</strong></div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    
    return html;
}

function deleteBooking(id) {
    const b = bookingsData.find(x => x.id == id);
    if (!b) return;

    const modalBody = document.getElementById('delete-modal-body');
    const confirmBtn = document.getElementById('confirm-delete-btn');
    let html = '';

    // İlişkili booking var mı?
    const hasRelated = b.related_booking_id && b.related_booking_id > 0;
    const related = hasRelated ? bookingsData.find(x => x.id == b.related_booking_id) : null;

    if (hasRelated && related) {
        // ── Birleşik (Arrival + Depar) ──
        const outbound = b.booking_direction === 'return' ? related : b;
        const returnLeg = b.booking_direction === 'return' ? b : related;

        html += '<p class="mb-2"><i class="bi bi-link-45deg me-1"></i>Bu rezervasyon ilişkili bir <strong>arrival-depar</strong> kaydıdır. Silmek istediğiniz bacakları seçin:</p>';
        html += buildBookingDetailCard(outbound, {checkbox: true});
        html += buildBookingDetailCard(returnLeg, {checkbox: true});
        html += '<div class="alert alert-warning py-2 mb-0 mt-2 small">';
        html += '<i class="bi bi-info-circle me-1"></i>En az bir bacak seçilmelidir. Sadece tek yönü silerseniz, diğer bacak bağımsız bir rezervasyon olarak kalır.';
        html += '</div>';
    } else {
        // ── Tek yönlü rezervasyon ──
        html += '<p class="mb-2">Bu rezervasyonu silmek istediğinizden emin misiniz?</p>';
        html += buildBookingDetailCard(b, {checkbox: false});
        // Tek bir gizli checkbox ekle (confirm-delete-btn JS'inde kullanılacak)
        html += '<input type="hidden" class="del-booking-check" value="' + b.id + '" data-always="1">';
    }

    modalBody.innerHTML = html;
    
    // Checkbox durumuna göre buton aktif/pasif
    function updateDeleteBtn() {
        var checks = modalBody.querySelectorAll('.del-booking-check');
        var anyChecked = false;
        checks.forEach(function(el) {
            if (el.type === 'hidden' || el.checked) anyChecked = true;
        });
        confirmBtn.disabled = !anyChecked;
    }
    updateDeleteBtn();
    
    // Checkbox değişikliğini dinle (event delegation)
    modalBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('del-booking-check')) {
            updateDeleteBtn();
        }
    });

    var delModal = document.getElementById('deleteBookingModal');
    document.body.appendChild(delModal);
    new bootstrap.Modal(delModal).show();
}

// Silme Onay Modal Event Listener
document.getElementById('confirm-delete-btn').addEventListener('click', function() {
    const checks = document.querySelectorAll('.del-booking-check');
    const ids = [];

    checks.forEach(function(el) {
        // Hidden input (tek yönlü) veya checked checkbox (birleşik)
        if (el.type === 'hidden' || el.checked) {
            ids.push(el.value);
        }
    });

    if (ids.length === 0) {
        showToast('Lütfen silinecek en az bir rezervasyon seçin.', false);
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('deleteBookingModal')).hide();
    performDelete(ids);
});

function quickStatus(id, status) {
    const fd = new FormData();
    fd.append('action', 'quick_status');
    fd.append('id', id);
    fd.append('status', status);
    fd.append('csrf_token', csrfToken);
    fetch(apiUrl, {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => { showToast(d.message, d.success); if (d.success) setTimeout(() => location.reload(), 200); })
        .catch(() => showToast('Bir hata oluştu.', false));
}

// ─── Passenger Steppers & Names ───────────────────────────────────────────────

// Yolcu isimlerini (mevcut veya boş) render eder
// mode: 'edit' | 'add'
// existingPassengers: [{passenger_type:'adult'|'child', full_name:''}] array
function renderPassengerNames(mode, adults, children, existingPassengers) {
    const container = document.getElementById(mode + '-passenger-names');
    if (!container) return;

    const adultPassengers  = existingPassengers.filter(p => p.passenger_type === 'adult');
    const childPassengers  = existingPassengers.filter(p => p.passenger_type === 'child');

    let html = '<div class="passenger-name-group">';

    if (adults > 0) {
        html += '<h6>Yetişkin Yolcular</h6>';
        for (let i = 0; i < adults; i++) {
            const val = (adultPassengers[i] && adultPassengers[i].full_name) ? adultPassengers[i].full_name : '';
            html += `<div class="passenger-name-row">
                <span class="badge bg-primary rounded-pill" style="min-width:24px;">${i+1}</span>
                <input type="text" name="passenger_adult_name[]" class="form-control form-control-sm"
                       placeholder="${i+1}. yetişkin adı soyadı (isteğe bağlı)" value="${val.replace(/"/g, '&quot;')}">
            </div>`;
        }
    }

    if (children > 0) {
        html += '<h6 class="mt-3">Çocuk Yolcular</h6>';
        for (let i = 0; i < children; i++) {
            const val = (childPassengers[i] && childPassengers[i].full_name) ? childPassengers[i].full_name : '';
            html += `<div class="passenger-name-row">
                <span class="badge bg-info rounded-pill" style="min-width:24px;">${i+1}</span>
                <input type="text" name="passenger_child_name[]" class="form-control form-control-sm"
                       placeholder="${i+1}. çocuk adı soyadı (isteğe bağlı)" value="${val.replace(/"/g, '&quot;')}">
            </div>`;
        }
    }

    html += '</div>';
    container.innerHTML = (adults + children > 0) ? html : '';
}

// Yolcuları DB'den yükle (edit modal için)
function loadPassengers(bookingId) {
    const fd = new FormData();
    fd.append('action', 'get_passengers');
    fd.append('id', bookingId);
    fd.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
    fetch(window.ADMIN_URL + '/api/handler.php?entity=bookings', {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const adults   = parseInt(document.getElementById('modal-adults').value)   || 0;
            const children = parseInt(document.getElementById('modal-children').value) || 0;
            renderPassengerNames('edit', adults, children, (d.data && d.data.passengers) ? d.data.passengers : []);
        })
        .catch(() => {});
}

// Stepper buton tıklamaları (event delegation)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.pax-btn');
    if (!btn) return;

    const targetId = btn.dataset.target;
    const input    = document.getElementById(targetId);
    if (!input) return;

    const isEdit   = targetId.startsWith('modal-');
    const mode     = isEdit ? 'edit' : 'add';
    const capInput = document.getElementById(mode === 'edit' ? 'edit-pax-capacity' : 'add-pax-capacity');
    const capacity = capInput ? (parseInt(capInput.value) || 50) : 50;

    const adultsInput   = document.getElementById(isEdit ? 'modal-adults'     : 'add-adults');
    const childrenInput = document.getElementById(isEdit ? 'modal-children'   : 'add-children');
    const seatInput     = document.getElementById(isEdit ? 'modal-child-seat' : 'add-child-seat');
    const boosterInput  = document.getElementById(isEdit ? 'modal-booster'    : 'add-booster');

    let adults   = parseInt(adultsInput.value)   || 0;
    let children = parseInt(childrenInput.value) || 0;
    let seats    = parseInt(seatInput.value)     || 0;

    const op = btn.dataset.op;

    if (input === adultsInput) {
        if (op === 'plus')  adults = Math.min(adults + 1, capacity - children);
        if (op === 'minus') adults = Math.max(adults - 1, 1);
        adultsInput.value = adults;
    } else if (input === childrenInput) {
        if (op === 'plus')  children = Math.min(children + 1, capacity - adults);
        if (op === 'minus') children = Math.max(children - 1, 0);
        childrenInput.value = children;
    } else if (input === seatInput) {
        if (op === 'plus')  seats = Math.min(seats + 1, capacity);
        if (op === 'minus') seats = Math.max(seats - 1, 0);
        seatInput.value = seats;
    } else if (input === boosterInput) {
        let boosters = parseInt(boosterInput.value) || 0;
        if (op === 'plus')  boosters = Math.min(boosters + 1, capacity);
        if (op === 'minus') boosters = Math.max(boosters - 1, 0);
        boosterInput.value = boosters;
    }

    // İsim alanlarını güncelle (mevcut değerleri koru)
    const existingNames = collectPassengerNames(mode);
    renderPassengerNames(mode, parseInt(adultsInput.value), parseInt(childrenInput.value), existingNames);
});

// Mevcut isim inputlarındaki değerleri topla
function collectPassengerNames(mode) {
    const container = document.getElementById(mode + '-passenger-names');
    if (!container) return [];
    const result = [];
    container.querySelectorAll('input[name="passenger_adult_name[]"]').forEach(inp => {
        result.push({passenger_type: 'adult', full_name: inp.value});
    });
    container.querySelectorAll('input[name="passenger_child_name[]"]').forEach(inp => {
        result.push({passenger_type: 'child', full_name: inp.value});
    });
    return result;
}

// Add modal sıfırla
document.getElementById('addBookingModal').addEventListener('show.bs.modal', function() {
    document.getElementById('add-customer-id').value = '';
    document.getElementById('add-adults').value     = 1;
    document.getElementById('add-children').value   = 0;
    document.getElementById('add-child-seat').value = 0;
    document.getElementById('add-booster').value    = 0;
    document.getElementById('add-pax-capacity').value = 20;
    renderPassengerNames('add', 1, 0, []);
});

// ─── Operasyonel Alan Güncellemeleri ──────────────────────────────────────────
function updateOps(id, field, value) {
    const fd = new FormData();
    fd.append('action', 'update_ops');
    fd.append('id', id);
    fd.append('field', field);
    fd.append('value', value);
    fd.append('csrf_token', csrfToken);
    fetch(apiUrl, {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            if (d.success && typeof table !== 'undefined') {
                table.draw();
            }
        })
        .catch(() => showToast('Bir hata oluştu.', false));
}

// Ops checkbox değişimi
var outsourceSaved            = false;
var outsourceChkRef           = null;
var outsourceModalCurrentName = '';
var outsourceModalCurrentPid  = 0;
var completedSaved            = false;
var completedChkRef           = null;

document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('ops-check')) return;
    const id    = e.target.dataset.id;
    const field = e.target.dataset.field;

    if (field === 'is_completed') {
        if (e.target.checked) {
            // Şöför modal'ını aç
            completedChkRef = e.target;
            completedSaved = false;
            document.getElementById('driver-modal-booking-id').value = id;
            openDriverModal(id);
        } else {
            // Şöförü kaldır ve is_completed'ı 0 yap
            const fd = new FormData();
            fd.append('action', 'set_driver');
            fd.append('booking_id', id);
            fd.append('driver_id', 0);
            fd.append('csrf_token', csrfToken);
            fetch(apiUrl, {method:'POST', body:fd})
                .then(r => r.json())
                .then(d => {
                    // Şoför/tahsilat silinemediyse iş yapıldıyı da kaldırma (yarım kalmasın)
                    if (!d || !d.success) throw new Error((d && d.message) || 'Şoför kaldırılamadı.');
                    // is_completed'ı 0 yap
                    const fdStatus = new FormData();
                    fdStatus.append('action', 'update_ops');
                    fdStatus.append('id', id);
                    fdStatus.append('field', 'is_completed');
                    fdStatus.append('value', 0);
                    fdStatus.append('csrf_token', csrfToken);
                    return fetch(apiUrl, {method:'POST', body:fdStatus}).then(r => r.json());
                })
                .then(d => {
                    showToast(d.message || 'İş yapıldı kaldırıldı', d.success);
                    if (d.success) {
                        const tr = e.target.closest('tr');
                        if (tr) tr.classList.remove('row-completed');
                        if (typeof table !== 'undefined') table.draw();
                    } else {
                        e.target.checked = true; // kaldırılamadı → işaret geri gelsin
                    }
                })
                .catch(err => {
                    e.target.checked = true; // hiçbir şey değişmedi → işaret geri gelsin
                    showToast((err && err.message) ? err.message : 'Bir hata oluştu.', false);
                });
        }
        return;
    }

    if (field === 'is_outsourced') {
        if (e.target.checked) {
            // Modal aç, mevcut değerleri doldur
            outsourceSaved  = false;
            outsourceChkRef = e.target;
            document.getElementById('outsource-booking-id').value = id;
            outsourceModalCurrentName = e.target.dataset.outsourceName    || '';
            outsourceModalCurrentPid  = parseInt(e.target.dataset.outsourcePartnerId) || 0;
            const priceInp = e.target.closest('.ops-cell').querySelector('.ops-price-input');
            document.getElementById('outsource-price-input').value = priceInp ? priceInp.value : '';
            // Para birimini booking'den al
            var bDataCur = bookingsData.find(function(x) { return x.id == id; });
            var curSel = document.getElementById('outsource-currency-input');
            if (curSel && bDataCur) curSel.value = bDataCur.currency || 'EUR';

            // Rezervasyon özeti doldur
            var bData = bookingsData.find(function(x) { return x.id == id; });
            if (bData) {
                document.getElementById('ob-number').textContent  = '#' + bData.booking_number;
                document.getElementById('ob-customer').textContent = bData.customer_name || '—';
                document.getElementById('ob-hotel').textContent   = bData.hotel_address || '—';
                document.getElementById('ob-vehicle').textContent = bData.vehicle_name  || '—';
                document.getElementById('ob-flight').textContent  = bData.flight_number || '—';

                var adults = (bData.adults || 1);
                var children = (bData.children || 0);
                var paxStr = adults + ' Yetişkin' + (children > 0 ? ' + ' + children + ' Çocuk' : '');
                document.getElementById('ob-pax').textContent = paxStr;

                var rawDate = bData.flight_date || bData.pickup_date || '';
                if (rawDate) {
                    var d2 = new Date(rawDate);
                    rawDate = d2.toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric'});
                }
                document.getElementById('ob-date').textContent = rawDate || '—';

                var rawTime = bData.flight_time || '';
                if (rawTime) rawTime = rawTime.substring(0, 5);
                document.getElementById('ob-time').textContent = rawTime || '—';

                var priceVal = bData.total_price > 0
                    ? (parseFloat(bData.total_price).toLocaleString('tr-TR', {minimumFractionDigits:2}) + ' ' + (bData.currency || 'EUR'))
                    : '—';
                document.getElementById('ob-price').textContent = priceVal;

                // Depar rezervasyonu: alış saati alanlarını göster
                var isReturn = bData.booking_direction === 'return';
                document.getElementById('ob-pickup-wrap').style.display      = isReturn ? '' : 'none';
                document.getElementById('outsource-pickup-wrap').style.display = isReturn ? '' : 'none';
                if (isReturn) {
                    var pickupVal = bData.pickup_time ? bData.pickup_time.substring(0, 5) : '';
                    document.getElementById('ob-pickup-time').textContent      = pickupVal || '—';
                    document.getElementById('outsource-pickup-input').value    = pickupVal;
                } else {
                    document.getElementById('outsource-pickup-input').value = '';
                }
            }

            new bootstrap.Modal(document.getElementById('outsourceModal')).show();
        } else {
            // Temizle
            const cell = e.target.closest('.ops-cell');
            const wrap = cell.querySelector('.ops-price-wrap');
            const priceInp = cell.querySelector('.ops-price-input');
            const nameDisp = cell.querySelector('.ops-name-display');
            if (wrap) wrap.style.display = 'none';
            if (priceInp) priceInp.value = '';
            if (nameDisp) { nameDisp.textContent = ''; nameDisp.style.display = 'none'; }
            e.target.dataset.outsourceName = '';
            
            // Dışarıya verilmediyse iş de yapılmamıştır, 'is_completed' checkbox'ını da kaldır
            const tr = e.target.closest('tr');
            if (tr) {
                const completedChk = tr.querySelector('.ops-check[data-field="is_completed"]');
                if (completedChk && completedChk.checked) {
                    completedChk.checked = false;
                }
            }

            var fdClear = new FormData();
            fdClear.append('action', 'clear_outsource');
            fdClear.append('id', id);
            fdClear.append('csrf_token', csrfToken);
            fetch(apiUrl, {method:'POST', body:fdClear})
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    showToast(d.message, d.success);
                    if (d.success) {
                        if (tr) tr.classList.remove('row-completed');
                        if (typeof table !== 'undefined') table.draw();
                    }
                })
                .catch(function() { showToast('Bir hata oluştu.', false); });
        }
        return;
    }

    updateOps(id, field, e.target.checked ? 1 : 0);
});

// Modal iptal → checkbox'ı geri al
document.getElementById('outsourceModal').addEventListener('hide.bs.modal', function() {
    if (!outsourceSaved && outsourceChkRef) {
        outsourceChkRef.checked = false;
    }
    outsourceChkRef = null;
});

// Dışarıya verme kaydet
document.getElementById('outsource-save-btn').addEventListener('click', function() {
    const id         = document.getElementById('outsource-booking-id').value;
    const selVal     = $('#outsource-name-input').val() || '';
    const isNumeric  = /^\d+$/.test(selVal.trim());
    const partnerId  = isNumeric ? selVal.trim() : '';
    const name       = isNumeric
        ? ($('#outsource-name-input option:selected').data('name') || selVal).trim()
        : selVal.trim();
    const price      = document.getElementById('outsource-price-input').value;
    const currency   = document.getElementById('outsource-currency-input').value;
    const pickupWrap = document.getElementById('outsource-pickup-wrap');
    const pickupTime = (pickupWrap && pickupWrap.style.display !== 'none')
                       ? document.getElementById('outsource-pickup-input').value
                       : '';
    const btn   = this;

    const fd = new FormData();
    fd.append('action', 'save_outsource');
    fd.append('id', id);
    fd.append('outsource_name', name);
    fd.append('outsource_partner_id', partnerId);
    fd.append('outsource_price', price);
    fd.append('outsource_currency', currency);
    if (pickupTime) fd.append('outsource_pickup_time', pickupTime);
    fd.append('csrf_token', csrfToken);

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor...';

    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast(d.message, d.success);
            if (d.success) {
                outsourceSaved = true;
                // Tablodaki price input ve name display'i güncelle
                const priceInp = document.querySelector('.ops-price-input[data-id="' + id + '"]');
                if (priceInp) {
                    priceInp.value = price;
                    const wrap = priceInp.closest('.ops-price-wrap');
                    if (wrap && currentView !== 'daily') wrap.style.display = '';
                    const nameDisp = wrap ? wrap.querySelector('.ops-name-display') : null;
                    if (nameDisp) {
                        nameDisp.textContent = name;
                        nameDisp.style.removeProperty('display');
                    }
                }
                // Checkbox data attribute güncelle
                const chk = document.querySelector('.ops-check[data-field="is_outsourced"][data-id="' + id + '"]');
                if (chk) {
                    chk.dataset.outsourceName      = name;
                    chk.dataset.outsourcePartnerId = partnerId;
                }

                // WhatsApp mesajı (toggle açık + telefon varsa)
                const waToggle = document.getElementById('outsource-wa-toggle');
                const noCollectionToggle = document.getElementById('outsource-no-collection');
                const waPhone  = ($('#outsource-name-input').find('option:selected').data('phone') || '').toString().trim();
                if (waToggle.checked && waPhone) {
                    var outCurrency = document.getElementById('outsource-currency-input').value || 'EUR';
                    sendOutsourceWhatsApp(id, waPhone, name, price, pickupTime, outCurrency, noCollectionToggle.checked);
                }

                // is_completed checkbox'unu işaretle (DB'de save_outsource zaten 1 yaptı)
                document.querySelectorAll('.ops-check[data-field="is_completed"][data-id="' + id + '"]').forEach(function(chk) {
                    chk.checked = true;
                    const tr = chk.closest('tr');
                    if (tr) tr.classList.add('row-completed');
                });
                if (typeof table !== 'undefined') table.draw();

                bootstrap.Modal.getInstance(document.getElementById('outsourceModal')).hide();
            }
        })
        .catch(function() { showToast('Bir hata oluştu.', false); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
        });
});

// Fiyat inputu blur'da kaydet
document.addEventListener('blur', function(e) {
    if (!e.target.classList.contains('ops-price-input')) return;
    updateOps(e.target.dataset.id, 'outsource_price', e.target.value);
}, true);

// ─── Dışarıya Verilen Partnere WhatsApp Mesajı ────────────────────────────────
function sendOutsourceWhatsApp(bookingId, partnerPhone, partnerName, partnerPrice, pickupTimeOverride, outsourceCurrency, noCollection) {
    var bData = bookingsData.find(function(x) { return x.id == bookingId; });
    if (!bData) return;

    var isInter = bData.is_inter_transfer === 1 || bData.booking_direction === 'inter_transfer';
    var targetPaxApiUrl = isInter
        ? (window.ADMIN_URL + '/api/handler.php?entity=inter_transfers')
        : apiUrl;

    // Yolcuları çek
    var fdp = new FormData();
    fdp.append('action', 'get_passengers');
    fdp.append('id', bookingId);
    fdp.append('csrf_token', csrfToken);

    fetch(targetPaxApiUrl, {method:'POST', body:fdp})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var passengers = (d.success && d.data && d.data.passengers) ? d.data.passengers : [];
            var msg = buildOutsourceMessage(bData, partnerPrice, pickupTimeOverride, passengers, outsourceCurrency, noCollection, partnerName);
            var waNumber = (partnerPhone || '').replace(/[^0-9]/g, '');
            if (waNumber.length === 10 && waNumber.startsWith('5')) {
                waNumber = '90' + waNumber;
            } else if (waNumber.length === 11 && waNumber.startsWith('05')) {
                waNumber = '90' + waNumber.substring(1);
            }
            if (!waNumber) return;
            var url = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg);
            window.open(url, '_blank');
        })
        .catch(function() {});
}

function buildOutsourceMessage(b, partnerPrice, pickupTimeOverride, passengers, outsourceCurrency, noCollection, partnerName) {
    var isInter = b.is_inter_transfer === 1 || b.booking_direction === 'inter_transfer';
    var AIRPORT  = AIRPORT_NAME;
    var hotel    = b.hotel_address || '-';
    var pickupHotel = b.pickup_location || '-';
    var isReturn = b.booking_direction === 'return';
    var adults   = b.adults || 0;
    var children = b.children || 0;
    var childSeat = b.child_seat || 0;
    var booster   = parseInt(b.booster) || 0;

    var paxStr = adults + ' Yetişkin' + (children > 0 ? ' + ' + children + ' Çocuk' : '');
    var paxExtra = [];
    if (childSeat > 0) paxExtra.push(childSeat + ' Çocuk Koltuğu');
    if (booster > 0)   paxExtra.push(booster + ' Yükseltici');
    if (paxExtra.length) paxStr += ' (' + paxExtra.join(', ') + ')';
    var price  = b.total_price > 0
        ? (parseFloat(b.total_price).toLocaleString('tr-TR', {minimumFractionDigits:0}) + ' ' + (b.currency || 'EUR'))
        : '-';
    
    if (noCollection) {
        price = 'YOK';
    }
    var hakCur = outsourceCurrency || b.currency || 'EUR';
    var hak    = (partnerPrice && parseFloat(partnerPrice) > 0)
        ? (parseFloat(partnerPrice).toLocaleString('tr-TR', {minimumFractionDigits:0}) + ' ' + hakCur)
        : '-';

    function fmtDate(d) {
        if (!d) return '';
        var dt = new Date(d);
        return dt.toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric'});
    }
    function fmtTime(t) { return t ? t.substring(0, 5) : ''; }

    var route = isInter
        ? (pickupHotel + ' ➔ ' + hotel)
        : (isReturn ? (hotel + ' ➔ ' + AIRPORT) : (AIRPORT + ' ➔ ' + hotel));

    var flightDateFormatted = isInter ? '' : fmtDate(b.flight_date);
    var flightTimeFormatted = isInter ? '' : fmtTime(b.flight_time);
    var pickupTimeFormatted = isInter
        ? (pickupTimeOverride || (b.pickup_time ? b.pickup_time.substring(0,5) : ''))
        : (isReturn ? (pickupTimeOverride || (b.pickup_time ? b.pickup_time.substring(0,5) : '')) : '');
    var pickupDateFormatted = isInter ? fmtDate(b.pickup_date || b.flight_date) : (isReturn ? fmtDate(b.flight_date) : '');

    var passengerLines = [];
    passengers.forEach(function(p) {
        if (p.full_name) {
            var tag = p.passenger_type === 'child' ? ' (Çocuk)' : '';
            passengerLines.push('• ' + p.full_name + tag);
        }
    });
    var passengersStr = passengerLines.join('\n');

    var placeholders = {
        '{ROUTE}': route,
        '{FLIGHT_DATE}': flightDateFormatted,
        '{FLIGHT_TIME}': flightTimeFormatted,
        '{PICKUP_DATE}': pickupDateFormatted,
        '{PICKUP_TIME}': pickupTimeFormatted,
        '{FLIGHT_NO}': b.flight_number || '-',
        '{HOTEL}': hotel,
        '{PICKUP_HOTEL}': pickupHotel,
        '{DESTINATION_HOTEL}': hotel,
        '{PAX}': paxStr,
        '{PASSENGERS}': passengersStr,
        '{PRICE}': price,
        '{NOTES}': b.notes ? b.notes.trim() : '-',
        '{PARTNER_NAME}': partnerName || '-',
        '{PARTNER_HAKEDIS}': hak
    };

    var templateKey = isInter ? 'outsource_inter_transfer' : 'outsource_transfer';
    return parseWhatsAppTemplate(templateKey, placeholders);
}

// ─── İş Durumu Modalı ─────────────────────────────────────────────────────────
function openOpsStatus(id) {
    const body    = document.getElementById('ops-status-body');
    const infoBox = document.getElementById('ops-booking-info');
    body.innerHTML    = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm"></span></div>';
    infoBox.innerHTML = '<div class="col-12 text-center py-2"><span class="spinner-border spinner-border-sm"></span></div>';
    
    // WP toggle'ı varsayılan açık yap
    const waToggle = document.getElementById('ops-modal-driver-wa-toggle');
    if (waToggle) waToggle.checked = true;
    // Önceki işten kalan "Kaydedildi" yazısını ve Tahsilat Yok anahtarını sıfırla
    const opsDurumYazi = document.querySelector('.ops-modal-driver-status');
    if (opsDurumYazi) { opsDurumYazi.style.display = 'none'; opsDurumYazi.textContent = ''; }
    const opsTahsilatYokAc = document.getElementById('ops-modal-no-collection');
    if (opsTahsilatYokAc) opsTahsilatYokAc.checked = false;

    new bootstrap.Modal(document.getElementById('opsStatusModal')).show();

    // Rezervasyon bilgilerini bookingsData'dan hemen doldur
    var bData = bookingsData.find(function(x) { return x.id == id; });
    var targetOpsApiUrl = (bData && bData.is_inter_transfer) 
        ? (window.ADMIN_URL + '/api/handler.php?entity=inter_transfers') 
        : apiUrl;

    if (bData) {
        var rawDate = bData.flight_date || bData.pickup_date || '';
        if (rawDate) {
            var d2 = new Date(rawDate);
            rawDate = d2.toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric'});
        }
        var rawTime = bData.flight_time ? bData.flight_time.substring(0, 5) : '—';
        var paxStr  = (bData.adults || 1) + ' Yetişkin' + ((bData.children || 0) > 0 ? ' + ' + bData.children + ' Çocuk' : '');
        var paxExtra2 = [];
        if (bData.child_seat > 0) paxExtra2.push(bData.child_seat + ' Çocuk Koltuğu');
        if (parseInt(bData.booster) > 0) paxExtra2.push(parseInt(bData.booster) + ' Yükseltici');
        if (paxExtra2.length) paxStr += ' (' + paxExtra2.join(', ') + ')';
        var priceVal = bData.total_price > 0
            ? (parseFloat(bData.total_price).toLocaleString('tr-TR', {minimumFractionDigits:2}) + ' ' + (bData.currency || 'EUR'))
            : '—';
        var isInter  = bData.is_inter_transfer === 1;
        var isReturn = bData.booking_direction === 'return';
        var dirLabel = isInter ? 'Ara Transfer' : (isReturn ? 'Depar' : 'Arrival');
        var dirBadge = isInter ? 'info' : (isReturn ? 'info' : 'primary');
        var dirIcon  = isInter ? 'arrows-expand' : (isReturn ? 'box-arrow-up-right' : 'box-arrow-in-down-right');
        var dirBadgeHtml = '<span class="badge bg-' + dirBadge + ' text-white px-2 py-1" style="font-size:.85rem !important;font-weight:600;display:inline-flex;align-items:center;">'
            + '<i class="bi bi-' + dirIcon + ' me-1"></i>' + dirLabel
            + '</span>';

        function infoCell(label, value, bold) {
            return '<div class="col-6 col-md-3">'
                + '<div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">' + label + '</div>'
                + '<div class="' + (bold ? 'fw-bold' : 'fw-semibold') + '">' + (value || '—') + '</div>'
                + '</div>';
        }

        var infoHtml = infoCell('Müşteri', bData.customer_name, true)
            + infoCell('Tarih', rawDate || '—');

        if (isInter) {
            infoHtml += infoCell('Alınış Saati', bData.pickup_time ? bData.pickup_time.substring(0, 5) : '—', true)
                + infoCell('Transfer Güzergahı', bData.destination_title || '—')
                + infoCell('Alınış Oteli', bData.pickup_location)
                + infoCell('Varış Oteli', bData.hotel_address);
        } else {
            infoHtml += infoCell('Uçuş Saati', rawTime)
                + infoCell('Uçuş No', bData.flight_number);

            if (isReturn && bData.pickup_time) {
                infoHtml += infoCell('Alış Saati', '<span style="color:#1c4b56;">' + bData.pickup_time.substring(0, 5) + '</span>', true);
            }
            infoHtml += infoCell('Otel / Adres', bData.hotel_address);
        }

        infoHtml += infoCell('Araç', bData.vehicle_name)
            + infoCell('Kişi', paxStr);

        // Yolcu Listesi + Müşteri Notu + Rezervasyon Tutarı & Yön yan yana
        infoHtml += '<div class="col-12 mt-2 pt-2" style="border-top:1px dashed #b8d4f0;">'
            + '<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">'
            +   '<div id="ops-modal-passengers-container" style="display:none;flex:1 1 auto;min-width:180px;"></div>'
            +   '<div id="ops-modal-notes-container"' + (bData.notes && bData.notes.trim() ? '' : ' style="display:none;"') + ' style="flex:1 1 auto;min-width:180px;">'
            +     '<div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;"><i class="bi bi-chat-left-text me-1"></i>Müşteri Notu</div>'
            +     '<div class="fw-semibold" style="white-space:pre-wrap;">' + (bData.notes || '') + '</div>'
            +   '</div>'
            +   '<div class="d-flex align-items-center gap-4 ms-auto flex-nowrap" style="flex-shrink:0;">'
            +     '<div>'
            +       '<div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Rezervasyon Tutarı</div>'
            +       '<div class="fw-bold" style="font-size:1.35rem;color:#1c4b56;line-height:1.2;white-space:nowrap;">' + priceVal + '</div>'
            +     '</div>'
            +     '<div class="text-end">'
            +       '<div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Yön</div>'
            +       '<div style="white-space:nowrap;">' + dirBadgeHtml + '</div>'
            +     '</div>'
            +   '</div>'
            + '</div>'
            + '</div>';

        infoBox.innerHTML = infoHtml;

        // Yolcu listesini hemen çek (ops'tan bağımsız)
        var fdPax2 = new FormData();
        fdPax2.append('action', 'get_passengers');
        fdPax2.append('id', id);
        fdPax2.append('csrf_token', csrfToken);
        fetch(targetOpsApiUrl, {method:'POST', body:fdPax2})
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success && d.data && d.data.passengers && d.data.passengers.length > 0) {
                    var pCont = document.getElementById('ops-modal-passengers-container');
                    if (pCont) {
                        var pNames = d.data.passengers.map(function(p) {
                            return p.full_name ? (p.full_name + (p.passenger_type === 'child' ? ' <small class="text-muted">(Çocuk)</small>' : '')) : '';
                        }).filter(function(n) { return n !== ''; });
                        if (pNames.length > 0) {
                            pCont.style.display = 'block';
                            pCont.innerHTML = '<div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">Yolcu Listesi</div>'
                                + '<div class="fw-semibold" style="font-size:.85rem;line-height:1.4;">' + pNames.join('<br>') + '</div>';
                        }
                    }
                }
            }).catch(function(){});
    }

    // Operasyonel durumu AJAX ile çek
    const fd = new FormData();
    fd.append('action', 'get_ops');
    fd.append('id', id);
    fd.append('csrf_token', csrfToken);

    fetch(targetOpsApiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) { body.innerHTML = '<p class="text-danger mb-0">Veri alınamadı.</p>'; return; }
            const ops = d.data;
            // Ops verilerini window'a kaydet (şöför seçimi için)
            if (!window.bookingOpsData) window.bookingOpsData = {};
            window.bookingOpsData[id] = ops;
            const yesNo = function(val, yesColor, noColor) {
                return val
                    ? '<span class="badge bg-' + yesColor + '">Evet</span>'
                    : '<span class="badge bg-' + noColor + '">Hayır</span>';
            };

            var html = '<h6 class="fw-bold mb-3"><i class="bi bi-clipboard-check me-2 text-success"></i>Operasyon Durumu</h6>'
                + '<div class="d-flex flex-column gap-3">'
                + '<div class="d-flex justify-content-between align-items-center">'
                + '<span><i class="bi bi-check-circle text-success me-1"></i><strong>İş Yapıldı</strong></span>'
                + yesNo(ops.is_completed, 'success', 'secondary')
                + '</div>'
                + '<div class="d-flex justify-content-between align-items-center">'
                + '<span><i class="bi bi-people text-warning me-1"></i><strong>Dışarıya Verildi</strong></span>'
                + yesNo(ops.is_outsourced, 'warning text-dark', 'secondary')
                + '</div>'
                // Tahsilat satırı yalnız iş bir şoföre verilmişse (değer kayıtlıysa) görünür
                + '<div id="ops-tahsilat-satir" class="justify-content-between align-items-center" style="display:' + (rzTahsilatVarMi(ops.tahsilat) ? 'flex' : 'none') + ';">'
                + '<span><i class="bi bi-cash-coin text-success me-1"></i><strong>Tahsilat</strong></span>'
                + '<span id="ops-tahsilat-durum">' + rzTahsilatDurumHtml(ops.tahsilat) + '</span>'
                + '</div>';

            if (ops.is_outsourced == 1) {
                html += '<div class="rounded p-3 mt-1" style="background:#fffbf0;border:1px solid #f6c23e;">'
                    + '<div class="row g-2 align-items-end">';

                // 1) Kime
                var partnerHtml = ops.outsource_name || '<em class="text-muted">—</em>';
                if (ops.outsource_phone) {
                    partnerHtml += '<br><small class="text-muted"><i class="bi bi-telephone me-1"></i>' + ops.outsource_phone + '</small>';
                }
                html += '<div class="col-sm-4"><div class="text-muted" style="font-size:.72rem;text-transform:uppercase;">Kime</div>'
                    + '<div class="fw-semibold">' + partnerHtml + '</div></div>';

                // 2) Verilen Tutar (editable + currency + kaydet butonu)
                var curCurrency = ops.outsource_currency || 'TRY';
                var curOpts = ['TRY','EUR','USD','GBP'];
                var curSelectHtml = '<select id="ops-modal-currency" class="form-select form-select-sm" style="width:70px;font-size:.8rem;">';
                curOpts.forEach(function(c) {
                    curSelectHtml += '<option value="' + c + '"' + (c === curCurrency ? ' selected' : '') + '>' + c + '</option>';
                });
                curSelectHtml += '</select>';
                html += '<div class="col-sm-5"><div class="text-muted" style="font-size:.72rem;text-transform:uppercase;">Verilen Tutar</div>'
                    + '<div class="d-flex align-items-center gap-1 mt-1">'
                    + '<input type="number" id="ops-modal-price" class="form-control form-control-sm fw-bold text-danger" '
                    + 'value="' + (ops.outsource_price ? parseFloat(ops.outsource_price).toFixed(2) : '') + '" '
                    + 'placeholder="0.00" min="0" step="0.01" style="width:90px;font-size:1rem;" '
                    + 'data-booking-id="' + ops.id + '">'
                    + curSelectHtml
                    + '<button type="button" id="ops-modal-price-save" class="btn btn-outline-primary btn-sm" style="font-size:.75rem;padding:2px 8px;" title="Kaydet">'
                    + '<i class="bi bi-check-lg"></i>'
                    + '</button>'
                    + '<span class="ops-modal-price-status" style="font-size:.7rem;"></span>'
                    + '</div></div>';

                // 3) WhatsApp Mesaj At
                var hasPhone = !!(ops.outsource_phone && ops.outsource_phone.trim());
                html += '<div class="col-sm-3 text-sm-end"><div class="text-muted d-none d-sm-block" style="font-size:.72rem;text-transform:uppercase;">&nbsp;</div>'
                    + '<button type="button" id="ops-modal-wa-btn" class="btn btn-success btn-sm mt-1" '
                    + (hasPhone ? '' : 'disabled ')
                    + 'data-booking-id="' + ops.id + '" '
                    + 'data-partner-phone="' + (ops.outsource_phone || '') + '" '
                    + 'data-partner-name="' + (ops.outsource_name || '') + '" '
                    + 'data-partner-price="' + (ops.outsource_price || '') + '" '
                    + 'data-pickup-time="' + (ops.outsource_pickup_time || '') + '">'
                    + '<i class="bi bi-whatsapp me-1"></i>WP Mesaj At'
                    + '</button>'
                    + (hasPhone ? '' : '<br><small class="text-muted" style="font-size:.68rem;">Telefon kaydı yok</small>')
                    + '</div>';

                html += '</div></div>';
            }

            html += '</div>';
            body.innerHTML = html;

            // Şöför ata bölümünü: dışarıya verilmediyse göster, verildiyse gizle
            var driverSection = document.getElementById('ops-driver-section');
            if (driverSection) {
                driverSection.style.display = ops.is_outsourced ? 'none' : '';
            }

            // Verilen Tutar kaydet butonu handler
            var priceInput = document.getElementById('ops-modal-price');
            var currencySelect = document.getElementById('ops-modal-currency');
            var saveBtn = document.getElementById('ops-modal-price-save');
            if (saveBtn && priceInput) {
                saveBtn.addEventListener('click', function() {
                    var statusEl = saveBtn.parentElement.querySelector('.ops-modal-price-status');
                    var newPrice = priceInput.value;
                    var newCurrency = currencySelect ? currencySelect.value : 'TRY';
                    var bId = priceInput.dataset.bookingId;

                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:.7rem;height:.7rem;"></span>';
                    statusEl.textContent = '';

                    var fd = new FormData();
                    fd.append('action', 'update_outsource_pricing');
                    fd.append('id', bId);
                    fd.append('outsource_price', newPrice);
                    fd.append('outsource_currency', newCurrency);
                    fd.append('csrf_token', csrfToken);

                    fetch(apiUrl, {method:'POST', body:fd})
                        .then(function(r) { return r.json(); })
                        .then(function(d) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
                            if (d.success) {
                                statusEl.textContent = '\u2713 Kaydedildi';
                                statusEl.className = 'ops-modal-price-status text-success';
                            } else {
                                statusEl.textContent = '\u2717 ' + (d.message || 'Hata');
                                statusEl.className = 'ops-modal-price-status text-danger';
                            }
                            var tableInp = document.querySelector('.ops-price-input[data-id="' + bId + '"]');
                            if (tableInp) tableInp.value = newPrice;
                            var waBtn = document.getElementById('ops-modal-wa-btn');
                            if (waBtn) waBtn.dataset.partnerPrice = newPrice;
                        })
                        .catch(function() {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
                            statusEl.textContent = '\u2717 Bağlantı hatası';
                            statusEl.className = 'ops-modal-price-status text-danger';
                        });
                });
            }

            // WhatsApp buton handler
            var waBtn = document.getElementById('ops-modal-wa-btn');
            if (waBtn) {
                waBtn.addEventListener('click', function() {
                    var bId = this.dataset.bookingId;
                    var phone = this.dataset.partnerPhone;
                    var name = this.dataset.partnerName;
                    var price = this.dataset.partnerPrice;
                    var pickup = this.dataset.pickupTime;
                    var outCur = currencySelect ? currencySelect.value : 'EUR';
                    sendOutsourceWhatsApp(bId, phone, name, price, pickup ? pickup.substring(0,5) : '', outCur);
                });
            }
        })
        .catch(function(err) { body.innerHTML = '<p class="text-danger mb-0">Hata: ' + (err.message || 'Bağlantı hatası') + '</p>'; console.error('Ops Error:', err); })
        .finally(function() {
            // Tahsilat seçimini bu işin kayıtlı değerine getir (yoksa boş)
            var opsKayit = (window.bookingOpsData && window.bookingOpsData[id]) ? window.bookingOpsData[id] : null;
            var opsTahsilatYok = document.getElementById('ops-modal-no-collection');
            if (opsTahsilatYok) opsTahsilatYok.checked = !!(opsKayit && (opsKayit.tahsilat === 0 || opsKayit.tahsilat === '0'));
            // Şöför listesini yükle
            var fdDrivers = new FormData();
            fdDrivers.append('action', 'list_drivers');
            fdDrivers.append('csrf_token', csrfToken);
            fetch(apiUrl, {method:'POST', body:fdDrivers})
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success && d.data && d.data.drivers) {
                        var $sel = $('#ops-modal-driver');
                        var sel = document.getElementById('ops-modal-driver');
                        var curDriverId = 0;
                        // Mevcut şöförü kontrol et
                        var bData = bookingsData.find(function(x) { return x.id == id; });
                        if (bData && window.bookingOpsData && window.bookingOpsData[id]) {
                            curDriverId = window.bookingOpsData[id].driver_id || 0;
                        }

                        // Select2'yi destroy et ve yeniden oluştur
                        if ($sel.hasClass('select2-hidden-accessible')) {
                            $sel.select2('destroy');
                        }

                        sel.innerHTML = '<option value=""></option>';
                        (d.data.drivers || []).forEach(function(dr) {
                            var txt = dr.name + ' ' + dr.surname;
                            if (dr.plate) txt += ' • ' + dr.plate;
                            if (dr.brand) txt += ' (' + dr.brand + ' ' + dr.model + ')';
                            var opt = new Option(txt, dr.id, false, dr.id == curDriverId);
                            opt.dataset.phone = dr.phone || '';
                            opt.dataset.name = dr.name + ' ' + dr.surname;
                            $sel.append(opt);
                        });

                        // Select2 initialize et
                        $sel.select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Şöför seçin...',
                            allowClear: true,
                            dropdownParent: $('#opsStatusModal')
                        });

                        if (curDriverId > 0) {
                            $sel.val(curDriverId).trigger('change');
                        }

                        sel.dataset.bookingId = id;
                    }
                }).catch(function(){});

        });
}

// ─── Outsource Partner Select2 ───────────────────────────────────────────────
var partnerApiUrl = window.ADMIN_URL + '/api/handler.php?entity=outsource_partners';

// Partner select'i değişince WP toggle durumu (telefon yoksa devre dışı)
function updateWaToggleState() {
    var $sel    = $('#outsource-name-input');
    var phone   = ($sel.find('option:selected').data('phone') || '').toString().trim();
    var toggle  = document.getElementById('outsource-wa-toggle');
    var hint    = document.getElementById('outsource-wa-hint');
    if (phone) {
        if (toggle) {
            toggle.disabled = false;
            toggle.checked  = true;
        }
        if (hint) hint.style.display = 'none';
    } else {
        if (toggle) {
            toggle.disabled = true;
            toggle.checked  = false;
        }
        if (hint) hint.style.display = '';
    }
}

function initPartnerSelect(currentPid, currentName) {
    var $sel = $('#outsource-name-input');
    if ($sel.hasClass('select2-hidden-accessible')) {
        $sel.select2('destroy');
    }
    $sel.empty().append('<option value=""></option>');

    var fd = new FormData();
    fd.append('action', 'list');
    fd.append('csrf_token', csrfToken);
    fetch(partnerApiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) return;
            (d.data.partners || []).forEach(function(p) {
                var selected = currentPid ? (p.id == currentPid) : false;
                var opt = new Option(p.name, p.id, false, selected);
                opt.dataset.name  = p.name;
                opt.dataset.phone = p.phone || '';
                $sel.append(opt);
            });
            // Eğer ID eşleşmedi ama isim varsa (eski kayıt / elle yazılmış) tag olarak ekle
            if (!currentPid && currentName) {
                var tagOpt = new Option(currentName, currentName, true, true);
                $sel.append(tagOpt);
            }
            $sel.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Kişi veya firma seçin...',
                allowClear: true,
                tags: true,
                dropdownParent: $('#outsourceModal'),
                createTag: function(params) {
                    return {id: params.term, text: params.term, newTag: true};
                }
            });
            $sel.off('change.waToggle').on('change.waToggle', updateWaToggleState);
            updateWaToggleState();
        })
        .catch(function() {});
}

// Outsource modal açılınca partner listesini yükle (mevcut değeri seç)
document.getElementById('outsourceModal').addEventListener('show.bs.modal', function() {
    initPartnerSelect(outsourceModalCurrentPid, outsourceModalCurrentName);
});

// + butonu
document.getElementById('btn-add-partner').addEventListener('click', function() {
    document.getElementById('quick-partner-name').value  = '';
    document.getElementById('quick-partner-phone').value = '';
    new bootstrap.Modal(document.getElementById('quickPartnerModal')).show();
    setTimeout(function() { document.getElementById('quick-partner-name').focus(); }, 400);
});

// Partner kaydet
document.getElementById('quick-partner-save-btn').addEventListener('click', function() {
    var name  = document.getElementById('quick-partner-name').value.trim();
    var phone = document.getElementById('quick-partner-phone').value.trim();
    if (!name) {
        document.getElementById('quick-partner-name').classList.add('is-invalid');
        document.getElementById('quick-partner-name').focus();
        return;
    }
    document.getElementById('quick-partner-name').classList.remove('is-invalid');

    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Ekleniyor...';

    var fd = new FormData();
    fd.append('action', 'create');
    fd.append('name', name);
    fd.append('phone', phone);
    fd.append('csrf_token', csrfToken);

    fetch(partnerApiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) { showToast(d.message, false); return; }
            showToast('Partner eklendi: ' + name, true);
            bootstrap.Modal.getInstance(document.getElementById('quickPartnerModal')).hide();
            initPartnerSelect(d.data.id, name);
        })
        .catch(function() { showToast('Bir hata oluştu.', false); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Ekle ve Seç';
        });
});

// ─── Hızlı Otel Ekleme ────────────────────────────────────────────────────────
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-quick-hotel');
    if (!btn) return;
    var targetId = btn.dataset.target;
    document.getElementById('quick-hotel-target').value = targetId || '';
    document.getElementById('quick-hotel-name').value     = '';
    document.getElementById('quick-hotel-address').value  = '';
    document.getElementById('quick-hotel-distance').value = '';
    document.getElementById('quick-hotel-phone').value    = '';
    new bootstrap.Modal(document.getElementById('quickHotelModal')).show();
    setTimeout(function() { document.getElementById('quick-hotel-name').focus(); }, 400);
});

document.getElementById('quick-hotel-save-btn').addEventListener('click', function() {
    var name     = document.getElementById('quick-hotel-name').value.trim();
    var address  = document.getElementById('quick-hotel-address').value.trim();
    var distance = document.getElementById('quick-hotel-distance').value;
    var phone    = document.getElementById('quick-hotel-phone').value.trim();
    var targetId = document.getElementById('quick-hotel-target').value;

    if (!name) {
        document.getElementById('quick-hotel-name').focus();
        document.getElementById('quick-hotel-name').classList.add('is-invalid');
        return;
    }
    document.getElementById('quick-hotel-name').classList.remove('is-invalid');

    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Ekleniyor...';

    var fd = new FormData();
    fd.append('action', 'create');
    fd.append('name', name);
    fd.append('address', address);
    fd.append('distance_km', distance);
    fd.append('phone', phone);
    fd.append('is_active', '1');
    fd.append('csrf_token', csrfToken);

    fetch(window.ADMIN_URL + '/api/handler.php?entity=hotels', {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) { showToast(d.message, false); return; }
            showToast('Otel eklendi: ' + name, true);

            // Oluşturulan otel değerini al (ad — adres formatı)
            var optVal  = name + (address ? ' — ' + address : '');
            var optText = name + (distance ? ' (' + Math.round(parseFloat(distance)) + ' km)' : '');

            // Tüm hotel-select'lere (add + edit + inter) yeni option ekle ve hedef select'te seç
            document.querySelectorAll('.hotel-select, .hotel-select-edit, .hotel-select-inter').forEach(function(sel) {
                var opt = new Option(optText, optVal, false, sel.id === targetId);
                $(sel).append(opt);
                if (sel.id === targetId) {
                    $(sel).val(optVal).trigger('change');
                }
            });

            bootstrap.Modal.getInstance(document.getElementById('quickHotelModal')).hide();
        })
        .catch(function() { showToast('Bir hata oluştu.', false); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Ekle ve Seç';
        });
});

// ─── Şöför Kaydet Handler ─────────────────────────────────────────────────────
document.getElementById('ops-modal-driver-save').addEventListener('click', function() {
    var sel = document.getElementById('ops-modal-driver');
    var bookingId = parseInt(sel.dataset.bookingId || 0);
    var driverId = parseInt(sel.value || 0);

    if (!bookingId) return;

    var bData = bookingsData.find(function(x) { return x.id == bookingId; });
    var targetDriverApiUrl = (bData && bData.is_inter_transfer)
        ? (window.ADMIN_URL + '/api/handler.php?entity=inter_transfers')
        : apiUrl;

    var btn = this;
    var statusEl = document.querySelector('.ops-modal-driver-status');
    var tahsilat = rzTahsilatDegeri('ops-modal-no-collection');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem;"></span>Kaydediliyor...';
    statusEl.style.display = 'none';

    var fd = new FormData();
    fd.append('action', bData && bData.is_inter_transfer ? 'assign_driver' : 'set_driver');
    fd.append(bData && bData.is_inter_transfer ? 'id' : 'booking_id', bookingId);
    fd.append('driver_id', driverId);
    fd.append('tahsilat', driverId > 0 ? tahsilat : '');
    fd.append('csrf_token', csrfToken);

    fetch(targetDriverApiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
            if (d.success) {
                statusEl.textContent = '✓ Kaydedildi';
                statusEl.className = 'ops-modal-driver-status ms-2 small text-success';
                statusEl.style.display = 'inline';

                // Açık pencerenin tahsilat bilgisini güncelle
                var tYeni = driverId > 0 ? parseInt(tahsilat, 10) : null;
                if (window.bookingOpsData && window.bookingOpsData[bookingId]) {
                    window.bookingOpsData[bookingId].tahsilat = tYeni;
                    window.bookingOpsData[bookingId].driver_id = driverId;
                }
                var opsDurum = document.getElementById('ops-tahsilat-durum');
                if (opsDurum) opsDurum.innerHTML = rzTahsilatDurumHtml(tYeni);
                var opsSatir = document.getElementById('ops-tahsilat-satir');
                if (opsSatir) opsSatir.style.display = rzTahsilatVarMi(tYeni) ? 'flex' : 'none';

                // WhatsApp Mesajı Gönder (Eğer check edilmişse)
                const waToggle = document.getElementById('ops-modal-driver-wa-toggle');
                if (waToggle && waToggle.checked) {
                    const selectedOption = sel.options[sel.selectedIndex];
                    const driverPhone = selectedOption ? selectedOption.dataset.phone : '';
                    const driverName = selectedOption ? selectedOption.dataset.name : '';
                    if (driverPhone) {
                        sendDriverWhatsApp(bookingId, driverPhone, driverName, driverId > 0 && tahsilat === '0');
                    }
                }
            } else {
                statusEl.textContent = '✗ ' + (d.message || 'Hata');
                statusEl.className = 'ops-modal-driver-status ms-2 small text-danger';
                statusEl.style.display = 'inline';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
            statusEl.textContent = '✗ Bağlantı hatası';
            statusEl.className = 'ops-modal-driver-status ms-2 small text-danger';
            statusEl.style.display = 'inline';
        });
});

// ─── Şöför Seçim Modal (İş Yapıldı Checkbox) ─────────────────────────────────
// ─── Şöför WhatsApp Mesajı Gönder ───────────────────────────────────────────
function sendDriverWhatsApp(bookingId, driverPhone, driverName, noCollection) {
    var bData = bookingsData.find(function(x) { return x.id == bookingId; });
    if (!bData) return;

    var isInter = bData.is_inter_transfer === 1 || bData.booking_direction === 'inter_transfer';
    var targetPaxApiUrl = isInter
        ? (window.ADMIN_URL + '/api/handler.php?entity=inter_transfers')
        : apiUrl;

    var fdp = new FormData();
    fdp.append('action', 'get_passengers');
    fdp.append('id', bookingId);
    fdp.append('csrf_token', csrfToken);

    fetch(targetPaxApiUrl, {method:'POST', body:fdp})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var passengers = (d.success && d.data && d.data.passengers) ? d.data.passengers : [];
            var msg = buildDriverMessage(bData, driverName, passengers, noCollection);
            var waNumber = (driverPhone || '').replace(/[^0-9]/g, '');
            if (waNumber.length === 10 && waNumber.startsWith('5')) {
                waNumber = '90' + waNumber;
            } else if (waNumber.length === 11 && waNumber.startsWith('05')) {
                waNumber = '90' + waNumber.substring(1);
            }
            if (!waNumber) return;
            var url = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg);
            window.open(url, '_blank');
        })
        .catch(function() {});
}

function buildDriverMessage(b, driverName, passengers, noCollection) {
    var isInter = b.is_inter_transfer === 1 || b.booking_direction === 'inter_transfer';
    var AIRPORT  = AIRPORT_NAME;
    var hotel    = b.hotel_address || '-';
    var pickupHotel = b.pickup_location || '-';
    var isReturn = b.booking_direction === 'return';
    var adults   = b.adults || 0;
    var children = b.children || 0;
    var childSeat = b.child_seat || 0;
    var booster   = parseInt(b.booster) || 0;

    var paxStr = adults + ' Yetişkin' + (children > 0 ? ' + ' + children + ' Çocuk' : '');
    var paxExtra = [];
    if (childSeat > 0) paxExtra.push(childSeat + ' Çocuk Koltuğu');
    if (booster > 0)   paxExtra.push(booster + ' Yükseltici');
    if (paxExtra.length) paxStr += ' (' + paxExtra.join(', ') + ')';
    var price  = b.total_price > 0
        ? (parseFloat(b.total_price).toLocaleString('tr-TR', {minimumFractionDigits:0}) + ' ' + (b.currency || 'EUR'))
        : '-';

    if (noCollection) {
        price = 'YOK'; // Dışarıya Verildi mesajındaki gibi: tahsilat yoksa tutar yerine YOK
    }

    function fmtDate(d) {
        if (!d) return '';
        var dt = new Date(d);
        return dt.toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric'});
    }
    function fmtTime(t) { return t ? t.substring(0, 5) : ''; }

    var route = isInter
        ? (pickupHotel + ' ➔ ' + hotel)
        : (isReturn ? (hotel + ' ➔ ' + AIRPORT) : (AIRPORT + ' ➔ ' + hotel));

    var flightDateFormatted = isInter ? '' : fmtDate(b.flight_date);
    var flightTimeFormatted = isInter ? '' : fmtTime(b.flight_time);
    var pickupTimeFormatted = isInter
        ? (b.pickup_time ? b.pickup_time.substring(0,5) : '')
        : (isReturn ? (b.pickup_time ? b.pickup_time.substring(0,5) : '') : '');
    var pickupDateFormatted = isInter ? fmtDate(b.pickup_date || b.flight_date) : (isReturn ? fmtDate(b.flight_date) : '');

    var passengerLines = [];
    passengers.forEach(function(p) {
        if (p.full_name) {
            var tag = p.passenger_type === 'child' ? ' (Çocuk)' : '';
            passengerLines.push('• ' + p.full_name + tag);
        }
    });
    var passengersStr = passengerLines.join('\n');

    var placeholders = {
        '{ROUTE}': route,
        '{FLIGHT_DATE}': flightDateFormatted,
        '{FLIGHT_TIME}': flightTimeFormatted,
        '{PICKUP_DATE}': pickupDateFormatted,
        '{PICKUP_TIME}': pickupTimeFormatted,
        '{FLIGHT_NO}': b.flight_number || '-',
        '{HOTEL}': hotel,
        '{PICKUP_HOTEL}': pickupHotel,
        '{DESTINATION_HOTEL}': hotel,
        '{PAX}': paxStr,
        '{PASSENGERS}': passengersStr,
        '{PRICE}': price,
        '{NOTES}': b.notes ? b.notes.trim() : '-',
        '{DRIVER_NAME}': driverName || '-'
    };

    var templateKey = isInter ? 'driver_inter_transfer' : 'driver_transfer';
    return parseWhatsAppTemplate(templateKey, placeholders);
}

// Tahsilat kayıtlı mı (1 = var, 0 = yok); null/boş = iş şoföre verilmemiş
function rzTahsilatVarMi(val) {
    return val === 1 || val === 0 || val === '1' || val === '0';
}
// İş Durumu penceresinde "Tahsilat: Var / Yok"
function rzTahsilatDurumHtml(val) {
    if (val === 1 || val === '1') return '<span class="badge bg-success">Var</span>';
    if (val === 0 || val === '0') return '<span class="badge bg-danger">Yok</span>';
    return '<span class="badge bg-light text-muted border">Seçilmedi</span>';
}
// "Tahsilat Yok" anahtarı (Dışarıya Verildi penceresindeki gibi): açık → 0 (yok), kapalı → 1 (var)
function rzTahsilatDegeri(anahtarId) {
    var a = document.getElementById(anahtarId);
    return (a && a.checked) ? '0' : '1';
}

function openDriverModal(bookingId) {
    const $sel = $('#driver-modal-select');
    var tahsilatYok = document.getElementById('driver-no-collection');
    if (tahsilatYok) tahsilatYok.checked = false;

    // Select2'yi yoksa initialize et
    if (!$sel.hasClass('select2-hidden-accessible')) {
        $sel.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Şöför seçin...',
            allowClear: true,
            dropdownParent: $('#driverSelectModal')
        });
    }

    $sel.empty().append('<option value=""></option>');

    const fd = new FormData();
    fd.append('action', 'list_drivers');
    fd.append('csrf_token', csrfToken);

    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success && d.data && d.data.drivers) {
                (d.data.drivers || []).forEach(function(dr) {
                    var txt = dr.name + ' ' + dr.surname;
                    if (dr.plate) txt += ' • ' + dr.plate;
                    if (dr.brand) txt += ' (' + dr.brand + ' ' + dr.model + ')';
                    const opt = new Option(txt, dr.id);
                    opt.dataset.phone = dr.phone || '';
                    opt.dataset.name = dr.name + ' ' + dr.surname;
                    $sel.append(opt);
                });
            }
            $sel.trigger('change');
            
            // WP Toggle'ı varsayılan açık yap
            const waToggle = document.getElementById('driver-wa-toggle');
            if (waToggle) waToggle.checked = true;

            new bootstrap.Modal(document.getElementById('driverSelectModal')).show();
        })
        .catch(function() {
            new bootstrap.Modal(document.getElementById('driverSelectModal')).show();
        });
}

// Şöför seçim modal kaydet
document.getElementById('driver-modal-save-btn').addEventListener('click', function() {
    const sel = document.getElementById('driver-modal-select');
    const bookingId = parseInt(document.getElementById('driver-modal-booking-id').value || 0);
    const driverId = parseInt(sel.value || 0);
    const tahsilat = rzTahsilatDegeri('driver-no-collection');
    // Satırı şimdi al: pencere kapanınca (hide olayı) completedChkRef sıfırlanıyor
    const kayitSatiri = completedChkRef ? completedChkRef.closest('tr') : null;

    if (!bookingId) return;

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem;"></span>Kaydediliyor...';

    const fd = new FormData();
    fd.append('action', 'set_driver');
    fd.append('booking_id', bookingId);
    fd.append('driver_id', driverId);
    fd.append('tahsilat', driverId > 0 ? tahsilat : '');
    fd.append('csrf_token', csrfToken);

    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                completedSaved = true;

                // is_completed status'unu da kaydet
                const fdStatus = new FormData();
                fdStatus.append('action', 'update_ops');
                fdStatus.append('id', bookingId);
                fdStatus.append('field', 'is_completed');
                fdStatus.append('value', 1);
                fdStatus.append('csrf_token', csrfToken);

                // İş yapıldı kaydedilemezse şoförü geri al (yarım kalmasın: "iş yapılmadı ama şoför + tahsilat var")
                const yarimKalmasin = function(mesaj) {
                    const fdGeri = new FormData();
                    fdGeri.append('action', 'set_driver');
                    fdGeri.append('booking_id', bookingId);
                    fdGeri.append('driver_id', 0);
                    fdGeri.append('csrf_token', csrfToken);
                    fetch(apiUrl, {method:'POST', body:fdGeri}).catch(function() {});
                    completedSaved = false; // pencere kapanınca "İş yapıldı" işareti geri alınır
                    bootstrap.Modal.getInstance(document.getElementById('driverSelectModal')).hide();
                    showToast(mesaj, false);
                };

                fetch(apiUrl, {method:'POST', body:fdStatus})
                    .then(function(r) { return r.json(); })
                    .then(function(d2) {
                        if (!d2 || !d2.success) {
                            yarimKalmasin('İş yapıldı kaydedilemedi' + (d2 && d2.message ? ': ' + d2.message : '') + '. Tekrar deneyin.');
                            return;
                        }
                        bootstrap.Modal.getInstance(document.getElementById('driverSelectModal')).hide();
                        showToast('Şöför kaydedildi ve iş yapıldı olarak işaretlendi.', true);
                        if (kayitSatiri) kayitSatiri.classList.add('row-completed');
                        if (typeof table !== 'undefined') table.draw();

                        // WhatsApp Mesajı Gönder (Eğer check edilmişse, durum güncellendikten sonra açılır)
                        const waToggle = document.getElementById('driver-wa-toggle');
                        if (waToggle && waToggle.checked) {
                            const selectedOption = sel.options[sel.selectedIndex];
                            const driverPhone = selectedOption ? selectedOption.dataset.phone : '';
                            const driverName = selectedOption ? selectedOption.dataset.name : '';
                            if (driverPhone) {
                                sendDriverWhatsApp(bookingId, driverPhone, driverName, driverId > 0 && tahsilat === '0');
                            }
                        }
                    })
                    .catch(function() {
                        yarimKalmasin('İş yapıldı kaydedilemedi (bağlantı hatası). Tekrar deneyin.');
                    });
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
                showToast(d.message || 'Hata oluştu', false);
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
            showToast('Bir hata oluştu.', false);
        });
});

// Modal iptal → checkbox'ı geri al ve save butonunu sıfırla
document.getElementById('driverSelectModal').addEventListener('hide.bs.modal', function() {
    if (!completedSaved && completedChkRef) {
        completedChkRef.checked = false;
    }
    completedChkRef = null;
    
    // Buton durumunu sıfırla
    const btn = document.getElementById('driver-modal-save-btn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
    }
});
</script>
