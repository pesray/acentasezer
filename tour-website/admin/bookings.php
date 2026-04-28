<?php
/**
 * Rezervasyon Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$defaultView = defined('BOOKINGS_AS_DASHBOARD') ? 'all' : 'arrival';
$view = $_GET['view'] ?? $defaultView;
if (!in_array($view, ['arrival', 'return', 'all'])) $view = $defaultView;

$viewTitleMap = ['return' => 'Dönüş Rezervasyonları', 'all' => 'Tüm Rezervasyonlar'];
$viewTitle = $viewTitleMap[$view] ?? 'Geliş Rezervasyonları';

$statusLabels = [
    'pending'   => ['Onay Bekliyor', 'warning', 'bi-hourglass-split'],
    'confirmed' => ['Onaylandı',     'success', 'bi-check-circle'],
    'cancelled' => ['İptal Edildi',  'danger',  'bi-x-circle'],
];

$directionLabels = [
    'outbound' => ['Geliş', 'primary'],
    'return'   => ['Dönüş', 'info'],
];

// WHERE koşulu view'a göre
if ($view === 'return') {
    $dirWhere = "AND COALESCE(b.booking_direction,'outbound') = 'return'";
} elseif ($view === 'all') {
    $dirWhere = '';
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
    WHERE b.booking_status = 'pending' $dirWhere
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
    WHERE 1=1 $dirWhere
    ORDER BY b.created_at DESC
")->fetchAll();

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

// Tüm Rezervasyonlar: geliş-dönüş çiftlerini eşleştir
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
        <span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split me-1"></i><?= count($pendingBookings) ?> Bekleyen</span>
        <span class="badge bg-secondary fs-6"><i class="bi bi-list me-1"></i><?= count($allBookings) ?> Toplam</span>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBookingModal">
            <i class="bi bi-plus-lg me-1"></i>Yeni Rezervasyon
        </button>
    </div>
</div>

<!-- View Tabları -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'all' ? 'active' : '' ?>" href="?view=all">
            <i class="bi bi-list-ul me-1"></i>Tüm Rezervasyonlar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'arrival' ? 'active' : '' ?>" href="?view=arrival">
            <i class="bi bi-box-arrow-in-down-right me-1"></i>Geliş Rezervasyonları
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link view-tab <?= $view === 'return' ? 'active' : '' ?>" href="?view=return">
            <i class="bi bi-box-arrow-up-right me-1"></i>Dönüş Rezervasyonları
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
            $dirLabel = $directionLabels[$dir] ?? ['Geliş','primary'];
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
                    <?php if ($pb['customer_phone']): ?>
                    <div class="mb-1"><strong><i class="bi bi-telephone me-1"></i><?= e($pb['customer_phone']) ?></strong></div>
                    <?php endif; ?>
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
            </div>

            <div class="d-flex gap-2 align-items-center">
                <select id="filter-status" class="form-select form-select-sm" style="width:160px;">
                    <option value="">Tüm Durumlar</option>
                    <option value="Onay Bekliyor">Onay Bekliyor</option>
                    <option value="Onaylandı">Onaylandı</option>
                    <option value="İptal Edildi">İptal Edildi</option>
                </select>
                <button type="button" id="filter-clear" class="btn btn-sm btn-outline-secondary" title="Filtreleri Temizle">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">

    <style>
#bookingsTable {
    zoom: 0.95;
}
    </style>

    <?php if ($view === 'all'): ?>
    <!-- Tüm Rezervasyonlar: geliş+dönüş çifti tek satırda -->
    <table id="bookingsTable" class="table table-hover datatable">
        <thead>
            <tr>
                <th>Yön</th>
                <th>Geliş Tarihi</th>
                <th>Geliş Saati</th>
                <th>Gidiş Tarihi</th>
                <th>Gidiş Saati</th>
                <th>Alış Saati</th>
                <th>Müşteri</th>
                <th>Kişi</th>
                <th>Otel Adı</th>
                <th>Araç</th>
                <th>Tutar</th>
                <th>Geliş Durumu</th>
                <th>Dönüş Durumu</th>
                <th>Durum</th>
                <th >İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tripGroups as $trip):
                $out  = $trip['out'];
                $ret  = $trip['ret'];
                $base = $out ?? $ret;
                if (!$base) continue;
            ?>
            <tr>
                <td>
                    <?php if ($out): ?>
                    <span class="badge bg-primary"><i class="bi bi-box-arrow-in-down-right me-1"></i>Geliş</span>
                    <?php endif; ?>
                    <?php if ($ret): ?>
                    <span class="badge bg-info"><i class="bi bi-box-arrow-up-right me-1"></i>Dönüş</span>
                    <?php endif; ?>
                </td>
                <td><?= ($out && $out['flight_date']) ? date('d.m.Y', strtotime($out['flight_date'])) : '-' ?></td>
                <td>
                    <?php if ($out && $out['flight_time']): ?>
                        <strong><?= date('H:i', strtotime($out['flight_time'])) ?></strong>
                        <?php if ($out['flight_number']): ?><br><strong class="text-dark">(<?= e($out['flight_number']) ?>)</strong><?php endif; ?>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td><?= ($ret && $ret['flight_date']) ? date('d.m.Y', strtotime($ret['flight_date'])) : '-' ?></td>
                <td>
                    <?php if ($ret && $ret['flight_time']): ?>
                        <strong><?= date('H:i', strtotime($ret['flight_time'])) ?></strong>
                        <?php if ($ret['flight_number']): ?><br><strong class="text-dark">(<?= e($ret['flight_number']) ?>)</strong><?php endif; ?>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td>
                    <?= ($ret && $ret['pickup_time']) ? '<strong>'.date('H:i', strtotime($ret['pickup_time'])).'</strong>' : '-' ?>
                </td>
                <td>
                    <?= e($base['customer_name']) ?><br>
                    <strong><?= e($base['customer_phone'] ?? '') ?></strong>
                </td>
                <td><?= (int)$base['adults'] ?>Y<?= (int)$base['children'] > 0 ? ' +'.(int)$base['children'].'Ç' : '' ?></td>
                <td><?= e(trim($base['hotel_address'] ?? '') ?: '-') ?></td>
                <td><?= e(trim($base['vehicle_name'] ?? '') ?: '-') ?></td>
                <td>
                    <?php if ($out && (float)$out['total_price'] > 0): ?>
                        <small class="text-muted">G:</small> <strong><?= number_format((float)$out['total_price'], 0, ',', '.') ?></strong> <small><?= e($out['currency'] ?? 'TRY') ?></small>
                    <?php endif; ?>
                    <?php if ($ret && (float)$ret['total_price'] > 0): ?>
                        <?php if ($out && (float)$out['total_price'] > 0): ?><br><?php endif; ?>
                        <small class="text-muted">D:</small> <strong><?= number_format((float)$ret['total_price'], 0, ',', '.') ?></strong> <small><?= e($ret['currency'] ?? 'TRY') ?></small>
                    <?php endif; ?>
                    <?php if (!($out && (float)$out['total_price'] > 0) && !($ret && (float)$ret['total_price'] > 0)): ?>-<?php endif; ?>
                </td>
                <!-- Geliş Durumu -->
                <td><?php if ($out): ?>
                    <div class="ops-cell">
                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input ops-check" id="comp-<?= $out['id'] ?>"
                                   data-id="<?= $out['id'] ?>" data-field="is_completed"
                                   <?= !empty($out['is_completed']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="comp-<?= $out['id'] ?>">İş yapıldı</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input ops-check ops-out-check" id="out-<?= $out['id'] ?>"
                                   data-id="<?= $out['id'] ?>" data-field="is_outsourced"
                                   data-outsource-name="<?= e($out['outsource_name'] ?? '') ?>"
                                   data-outsource-partner-id="<?= (int)($out['outsource_partner_id'] ?? 0) ?>"
                                   <?= !empty($out['is_outsourced']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="out-<?= $out['id'] ?>">Dışarıya verildi</label>
                        </div>
                        <div class="ops-price-wrap mt-1" <?= !empty($out['is_outsourced']) ? '' : 'style="display:none;"' ?>>
                            <input type="number" class="form-control form-control-sm ops-price-input"
                                   data-id="<?= $out['id'] ?>"
                                   value="<?= e($out['outsource_price'] ?? '') ?>"
                                   placeholder="Tutar..." min="0" step="0.01" style="width:90px;">
                            <?php if (!empty($out['outsource_name'])): ?>
                            <small class="ops-name-display text-muted d-block mt-1"><?= e($out['outsource_name']) ?></small>
                            <?php else: ?>
                            <small class="ops-name-display text-muted d-block mt-1" style="display:none!important;"></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>-<?php endif; ?></td>
                <!-- Dönüş Durumu -->
                <td><?php if ($ret): ?>
                    <div class="ops-cell">
                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input ops-check" id="comp-<?= $ret['id'] ?>"
                                   data-id="<?= $ret['id'] ?>" data-field="is_completed"
                                   <?= !empty($ret['is_completed']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="comp-<?= $ret['id'] ?>">İş yapıldı</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input ops-check ops-out-check" id="out-<?= $ret['id'] ?>"
                                   data-id="<?= $ret['id'] ?>" data-field="is_outsourced"
                                   data-outsource-name="<?= e($ret['outsource_name'] ?? '') ?>"
                                   <?= !empty($ret['is_outsourced']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="out-<?= $ret['id'] ?>">Dışarıya verildi</label>
                        </div>
                        <div class="ops-price-wrap mt-1" <?= !empty($ret['is_outsourced']) ? '' : 'style="display:none;"' ?>>
                            <input type="number" class="form-control form-control-sm ops-price-input"
                                   data-id="<?= $ret['id'] ?>"
                                   value="<?= e($ret['outsource_price'] ?? '') ?>"
                                   placeholder="Tutar..." min="0" step="0.01" style="width:90px;">
                            <?php if (!empty($ret['outsource_name'])): ?>
                            <small class="ops-name-display text-muted d-block mt-1"><?= e($ret['outsource_name']) ?></small>
                            <?php else: ?>
                            <small class="ops-name-display text-muted d-block mt-1" style="display:none!important;"></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>-<?php endif; ?></td>
                <td>
                    <div class="d-flex flex-column gap-1" style="width:fit-content;">
                    <?php if ($out): ?>
                    <button type="button" class="btn btn-outline-primary" style="padding:2px 6px;" onclick="openOpsStatus(<?= $out['id'] ?>)" title="Geliş İş Durumu">
                        <i class="bi bi-box-arrow-in-down-right"></i>
                    </button>
                    <?php endif; ?>
                    <?php if ($ret): ?>
                    <button type="button" class="btn btn-outline-info" style="padding:2px 6px;" onclick="openOpsStatus(<?= $ret['id'] ?>)" title="Dönüş İş Durumu">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </button>
                    <?php endif; ?>
                    </div>
                </td>
                <td>
                    <?php
                        $vParts = [];
                        if ($out) $vParts[] = 'out_id=' . $out['id'];
                        if ($ret) $vParts[] = 'ret_id=' . $ret['id'];
                        $vParams = implode('&', $vParts);
                    ?>
                    <div class="d-flex flex-column gap-1">
                    <?php if ($out): ?>
                        <div class="btn-group" style="width:fit-content;">
                            <button type="button" class="btn btn-outline-primary" style="padding:2px 6px;" onclick="openBookingModal(<?= $out['id'] ?>)" title="Geliş Detayı">
                                <i class="bi bi-box-arrow-in-down-right"></i>
                            </button>

                            <button type="button" class="btn btn-outline-danger" style="padding:2px 6px;" onclick="deleteBooking(<?= $out['id'] ?>)" title="Geliş Sil">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if ($ret): ?>
                        <div class="btn-group" style="width:fit-content;">
                            <button type="button" class="btn btn-outline-info" style="padding:2px 6px;" onclick="openBookingModal(<?= $ret['id'] ?>)" title="Dönüş Detayı">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </button>

                            <button type="button" class="btn btn-outline-danger" style="padding:2px 6px;" onclick="deleteBooking(<?= $ret['id'] ?>)" title="Dönüş Sil">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    <!-- Voucher (her zaman) -->
                    <div class="btn-group" style="width:fit-content;">
                        <a href="voucher.php?<?= $vParams ?>&lang=tr" target="_blank" class="btn btn-outline-success" style="padding:2px 6px;" title="Voucher (TR)">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                        <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" style="padding:2px 4px;" data-bs-toggle="dropdown">
                            <span class="visually-hidden">Dil Seç</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item small" href="voucher.php?<?= $vParams ?>&lang=tr" target="_blank">🇹🇷 Türkçe</a></li>
                            <li><a class="dropdown-item small" href="voucher.php?<?= $vParams ?>&lang=en" target="_blank">🇬🇧 English</a></li>
                            <li><a class="dropdown-item small" href="voucher.php?<?= $vParams ?>&lang=de" target="_blank">🇩🇪 Deutsch</a></li>
                            <li><a class="dropdown-item small" href="voucher.php?<?= $vParams ?>&lang=ru" target="_blank">🇷🇺 Русский</a></li>
                        </ul>
                    </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php else: ?>
    <!-- Geliş / Dönüş view: eski yapı -->
    <table id="bookingsTable" class="table table-hover datatable">
        <thead>
            <tr>
                <th>No</th>
                <th>Müşteri</th>
                <th><?= $view === 'return' ? 'Alış Otel Adı' : 'Varış Otel Adı' ?></th>
                <th>Uçuş</th>
                <?php if ($view === 'return'): ?><th>Alış Saati</th><?php endif; ?>
                <th>Araç</th>
                <th>Kişi</th>
                <th>Tutar</th>
                <th width="110">Durum</th>
                <th width="100">İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allBookings as $b):
                $dateField = $b['pickup_date'] ?: $b['flight_date'];
            ?>
            <tr>
                <td><strong class="text-primary"><?= e($b['booking_number']) ?></strong></td>
                <td>
                    <?= e($b['customer_name']) ?><br>
                    <strong><?= e($b['customer_phone'] ?? '') ?></strong>
                </td>
                <td><?= e(trim($b['hotel_address'] ?? '') ?: '-') ?></td>
                <td>
                    <?= $dateField ? date('d.m.Y', strtotime($dateField)) : '-' ?>
                    <?php if ($b['flight_time']): ?>
                        <strong> <?= date('H:i', strtotime($b['flight_time'])) ?></strong>
                    <?php endif; ?>
                </td>
                <?php if ($view === 'return'): ?>
                <td>
                    <?php if ($b['pickup_time']): ?>
                        <strong><?= date('H:i', strtotime($b['pickup_time'])) ?></strong>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <?php endif; ?>
                <td><?= e(trim($b['vehicle_name'] ?? '') ?: '-') ?></td>
                <td><?= (int)$b['adults'] ?>Y<?= (int)$b['children'] > 0 ? ' +'.(int)$b['children'].'Ç' : '' ?></td>
                <td>
                    <?php if ((float)$b['total_price'] > 0): ?>
                        <strong><?= number_format((float)$b['total_price'], 0, ',', '.') ?></strong>
                        <small class="text-muted"><?= e($b['currency'] ?? 'TRY') ?></small>
                    <?php else: ?>-<?php endif; ?>
                </td>
                <td>
                    <span class="badge bg-<?= $statusLabels[$b['booking_status']][1] ?>">
                        <i class="bi <?= $statusLabels[$b['booking_status']][2] ?> me-1"></i><?= $statusLabels[$b['booking_status']][0] ?>
                    </span>
                </td>
                <td>
                    <?php
                        $bDir = $b['booking_direction'] ?? 'outbound';
                        $bVParam = ($bDir === 'return') ? 'ret_id=' . $b['id'] : 'out_id=' . $b['id'];
                    ?>
                    <div class="d-flex flex-column gap-1 align-items-start">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="openBookingModal(<?= $b['id'] ?>)" title="Detay / Düzenle">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="openOpsStatus(<?= $b['id'] ?>)" title="İş Durumu">
                                <i class="bi bi-clipboard-check"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteBooking(<?= $b['id'] ?>)" title="Sil">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="voucher.php?<?= $bVParam ?>&lang=tr" target="_blank" class="btn btn-outline-success" title="Voucher (TR)">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
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
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

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
                        <div class="col-md-4">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" name="customer_name" id="modal-customer-name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="customer_email" id="modal-customer-email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="customer_phone" id="modal-customer-phone" class="form-control">
                        </div>
                    </div>

                    <!-- Rezervasyon detayları -->
                    <h6 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Rezervasyon Detayları</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tur / Transfer</label>
                            <input type="text" id="modal-item-name" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Araç</label>
                            <input type="text" id="modal-vehicle-name" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Tur alanları -->
                    <div id="modal-tour-fields">
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
                                <label class="form-label">Dönüş Saati</label>
                                <input type="time" name="return_time" id="modal-return-time" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Transfer alanları -->
                    <div id="modal-transfer-fields" style="display:none;">
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
                            <div class="col-md-9">
                                <label class="form-label">Otel Adresi</label>
                                <input type="text" name="hotel_address" id="modal-hotel-address" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Otelden Alış Saati</label>
                                <input type="time" name="pickup_time" id="modal-pickup-time-transfer" class="form-control">
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
                    </div>
                    <div id="edit-passenger-names" class="mb-4"></div>

                    <!-- Fiyat -->
                    <h6 class="mb-3"><i class="bi bi-cash me-2"></i>Fiyat Bilgileri</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Toplam Tutar</label>
                            <input type="number" name="total_price" id="modal-total-price" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Para Birimi</label>
                            <select name="currency" id="modal-currency" class="form-select">
                                <option value="TRY">₺ TRY</option>
                                <option value="EUR">€ EUR</option>
                                <option value="USD">$ USD</option>
                                <option value="GBP">£ GBP</option>
                            </select>
                        </div>
                    </div>

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

                <div class="modal-body" style="max-height:75vh;overflow-y:auto;">

                    <!-- Transfer & Araç -->
                    <h6 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Transfer & Araç Seçimi</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Transfer</label>
                            <select name="destination_id" id="add-destination" class="form-select">
                                <option value="">-- Transfer Seçin --</option>
                                <?php foreach ($destinations as $dest): ?>
                                <option value="<?= $dest['id'] ?>"><?= e($dest['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-4">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" name="customer_name" id="add-customer-name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="customer_email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="customer_phone" class="form-control">
                        </div>
                    </div>

                    <hr>

                    <!-- Geliş toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="add-has-outbound" name="has_outbound" value="1"
                            <?= $view !== 'return' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="add-has-outbound">
                            <i class="bi bi-box-arrow-in-down-right me-1 text-primary"></i>Geliş Transferi Ekle
                        </label>
                    </div>

                    <!-- GELİŞ alanları -->
                    <div id="outbound-section" style="<?= $view !== 'return' ? '' : 'display:none;' ?>">
                        <h6 class="mb-3 text-primary"><i class="bi bi-box-arrow-in-down-right me-2"></i>Geliş Uçuş Bilgileri</h6>
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
                                <label class="form-label">Geliş Fiyatı</label>
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

                    <!-- Dönüş toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="add-has-return" name="has_return" value="1"
                            <?= $view === 'return' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="add-has-return">
                            <i class="bi bi-box-arrow-up-right me-1 text-info"></i>Dönüş Transferi Ekle
                        </label>
                    </div>

                    <!-- DÖNÜŞ alanları -->
                    <div id="return-section" class="return-section mb-4" style="<?= $view === 'return' ? '' : 'display:none;' ?>">
                        <h6 class="mb-3 text-info"><i class="bi bi-box-arrow-up-right me-2"></i>Dönüş Uçuş Bilgileri</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Dönüş Uçuş Tarihi</label>
                                <input type="date" name="return_flight_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dönüş Uçuş Saati</label>
                                <input type="time" name="return_flight_time" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dönüş Uçuş Numarası</label>
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
                                <label class="form-label">Dönüş Fiyatı</label>
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

                <!-- Inputlar yan yana -->
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ad Soyad / Firma</label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <select id="outsource-name-input" class="form-select">
                                    <option value=""></option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-outline-success flex-shrink-0" id="btn-add-partner" title="Yeni kişi/firma ekle" style="height:38px;">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3" id="outsource-pickup-wrap" style="display:none;">
                        <label class="form-label fw-semibold">Otelden Alış Saati</label>
                        <input type="time" id="outsource-pickup-input" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tutar</label>
                        <input type="number" id="outsource-price-input" class="form-control" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-3" id="outsource-wa-wrap">
                        <label class="form-label fw-semibold d-block">&nbsp;</label>
                        <div class="d-flex align-items-center gap-2 border rounded px-2" style="height:38px;background:#f6fff8;border-color:#25d366 !important;white-space:nowrap;">
                            <div class="form-check form-switch mb-0" style="padding-left:2.2em;min-height:auto;">
                                <input class="form-check-input mt-0" type="checkbox" id="outsource-wa-toggle" style="cursor:pointer;width:2em;height:1em;">
                            </div>
                            <label for="outsource-wa-toggle" class="fw-bold mb-0" style="cursor:pointer;color:#25d366;font-size:.85rem;line-height:1;">
                                <i class="bi bi-whatsapp"></i> WP Mesaj At
                            </label>
                            <span class="small text-muted ms-1" id="outsource-wa-hint" style="font-size:.65rem;display:none;">(numara yok)</span>
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
            </div>
        </div>
    </div>
</div>

<script>
const bookingsData = <?= json_encode(array_map(function($b) {
    return [
        'id'                => $b['id'],
        'booking_number'    => $b['booking_number'],
        'booking_type'      => $b['booking_type'],
        'booking_direction' => $b['booking_direction'] ?? 'outbound',
        'booking_status'    => $b['booking_status'],
        'customer_name'     => $b['customer_name'],
        'customer_email'    => $b['customer_email'],
        'customer_phone'    => $b['customer_phone'],
        'tour_title'        => $b['tour_title'],
        'destination_title' => $b['destination_title'],
        'vehicle_name'      => trim($b['vehicle_name'] ?? ''),
        'pickup_location'   => $b['pickup_location'],
        'pickup_date'       => $b['pickup_date'],
        'pickup_time'       => $b['pickup_time'],
        'return_time'       => $b['return_time'],
        'flight_date'       => $b['flight_date'],
        'flight_time'       => $b['flight_time'],
        'flight_number'     => $b['flight_number'],
        'hotel_address'     => $b['hotel_address'],
        'adults'            => (int)$b['adults'],
        'children'          => (int)$b['children'],
        'child_seat'        => (int)$b['child_seat'],
        'vehicle_capacity'  => (int)($b['vehicle_capacity'] ?? 20),
        'total_price'       => (float)$b['total_price'],
        'currency'          => $b['currency'],
        'notes'                => $b['notes'],
        'admin_notes'          => $b['admin_notes'],
        'outsource_partner_id' => (int)($b['outsource_partner_id'] ?? 0),
        'outsource_name'       => $b['outsource_name'] ?? '',
        'created_at'           => $b['created_at'],
    ];
}, $allBookings), JSON_UNESCAPED_UNICODE) ?>;

const currentView = '<?= $view ?>';
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
    const table = $('#bookingsTable').DataTable();
    // all:     Yön(0) GelişTarihi(1) GelişSaati(2) GidişTarihi(3) GidişSaati(4) AlışSaati(5) Müşteri(6) Kişi(7) Otel(8) Tutar(9) Durum(10)
    // arrival: No(0) Müşteri(1) Otel(2) Uçuş(3) Araç(4) Kişi(5) Tutar(6) Durum(7)
    // return:  No(0) Müşteri(1) Otel(2) Uçuş(3) AlışSaati(4) Araç(5) Kişi(6) Tutar(7) Durum(8)
    // all: GelişTarihi(1) + GidişTarihi(3) — herhangi biri eşleşirse geçer
    // arrival/return: tek tarih sütunu (3)
    const dateColIdxs  = currentView === 'all' ? [1, 3] : [3];
    const statusColIdx = currentView === 'all' ? 11 : (currentView === 'return' ? 8 : 7);

    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (settings.nTable.id !== 'bookingsTable') return true;
        const fd = $('#filter-date').val();
        if (fd) {
            var matched = false;
            for (var i = 0; i < dateColIdxs.length; i++) {
                var m = (data[dateColIdxs[i]] || '').match(/(\d{2})\.(\d{2})\.(\d{4})/);
                if (m && (m[3]+'-'+m[2]+'-'+m[1]) === fd) { matched = true; break; }
            }
            if (!matched) return false;
        }
        const fs = $('#filter-status').val();
        if (fs && (data[statusColIdx] || '').indexOf(fs) === -1) return false;
        return true;
    });

    // Bugünü default olarak set et (dashboard'dan açılırsa atla)
    <?php if (defined('BOOKINGS_AS_DASHBOARD')): ?>window.AUTO_FILTER_TODAY = false;<?php endif; ?>
    if (window.AUTO_FILTER_TODAY !== false) {
        var today = new Date();
        var todayStr = today.getFullYear() + '-'
            + String(today.getMonth() + 1).padStart(2, '0') + '-'
            + String(today.getDate()).padStart(2, '0');
        $('#filter-date').val(todayStr);
    }

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

    $('#filter-clear').on('click', function() {
        $('#filter-date').val('');
        $('#filter-status').val('');
        table.draw();
    });
    table.draw();

    // ─── Otel Select2 ─────────────────────────────────────────────────────────
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
});

// ─── Uçuş numarası inputlarını otomatik büyük harf yap ──────────────────────
document.addEventListener('input', function(e) {
    if (e.target && e.target.classList && e.target.classList.contains('flight-number-upper')) {
        var pos = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        try { e.target.setSelectionRange(pos, pos); } catch (err) {}
    }
});

// ─── Geliş toggle ────────────────────────────────────────────────────────────
document.getElementById('add-has-outbound').addEventListener('change', function() {
    document.getElementById('outbound-section').style.display = this.checked ? 'block' : 'none';
    // En az biri seçili olmalı
    if (!this.checked && !document.getElementById('add-has-return').checked) {
        document.getElementById('add-has-return').checked = true;
        document.getElementById('add-has-return').dispatchEvent(new Event('change'));
    }
});

// ─── Dönüş toggle ────────────────────────────────────────────────────────────
document.getElementById('add-has-return').addEventListener('change', function() {
    document.getElementById('return-section').style.display = this.checked ? 'block' : 'none';
    // Dönüş açılınca otel adresini otomatik kopyala
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

// ─── Geliş otel adresi → dönüş otel adresi (dönüş açıksa) ────────────────────
$('#add-hotel-address').on('change', function() {
    if (document.getElementById('add-has-return').checked) {
        $('#add-return-hotel-address').val($(this).val()).trigger('change');
    }
});

// ─── Detay Modalı ─────────────────────────────────────────────────────────────
function openBookingModal(id) {
    const b = bookingsData.find(x => x.id == id);
    if (!b) return;

    document.getElementById('modal-booking-id').value = b.id;
    document.getElementById('modal-booking-number').textContent = '#' + b.booking_number;
    document.getElementById('modal-booking-type').innerHTML = b.booking_type === 'tour'
        ? '<span class="badge bg-primary fs-6">Tur</span>'
        : '<span class="badge bg-info fs-6">Transfer</span>';
    document.getElementById('modal-created-at').textContent =
        b.created_at ? new Date(b.created_at).toLocaleString('tr-TR') : '-';

    const statusEl = document.getElementById('status-' + b.booking_status);
    if (statusEl) statusEl.checked = true;

    document.getElementById('modal-customer-name').value  = b.customer_name  || '';
    document.getElementById('modal-customer-email').value = b.customer_email || '';
    document.getElementById('modal-customer-phone').value = b.customer_phone || '';

    const itemName = b.booking_type === 'tour' ? (b.tour_title || '-') : (b.destination_title || '-');
    document.getElementById('modal-item-name').value    = itemName;
    document.getElementById('modal-vehicle-name').value = b.vehicle_name || '-';

    if (b.booking_type === 'tour') {
        document.getElementById('modal-tour-fields').style.display     = 'block';
        document.getElementById('modal-transfer-fields').style.display = 'none';
        document.getElementById('modal-pickup-location').value = b.pickup_location || '';
        document.getElementById('modal-pickup-date').value     = b.pickup_date     || '';
        document.getElementById('modal-pickup-time').value     = b.pickup_time     || '';
        document.getElementById('modal-return-time').value     = b.return_time     || '';
    } else {
        document.getElementById('modal-tour-fields').style.display     = 'none';
        document.getElementById('modal-transfer-fields').style.display = 'block';
        document.getElementById('modal-flight-date').value          = b.flight_date   || '';
        document.getElementById('modal-flight-time').value          = b.flight_time   || '';
        document.getElementById('modal-flight-number').value        = b.flight_number || '';
        document.getElementById('modal-hotel-address').value        = b.hotel_address || '';
        document.getElementById('modal-pickup-time-transfer').value = b.pickup_time   || '';
    }

    // Kapasite limiti
    document.getElementById('edit-pax-capacity').value = b.vehicle_capacity || 20;

    // Stepper değerleri
    document.getElementById('modal-adults').value     = b.adults     || 1;
    document.getElementById('modal-children').value   = b.children   || 0;
    document.getElementById('modal-child-seat').value = b.child_seat || 0;

    document.getElementById('modal-total-price').value = b.total_price || 0;
    document.getElementById('modal-currency').value    = b.currency   || 'TRY';
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

function deleteBooking(id) {
    if (!confirm('Bu rezervasyonu silmek istediğinizden emin misiniz?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('csrf_token', csrfToken);
    fetch(apiUrl, {method:'POST', body:fd})
        .then(r => r.json())
        .then(d => { showToast(d.message, d.success); if (d.success) setTimeout(() => location.reload(), 200); })
        .catch(() => showToast('Bir hata oluştu.', false));
}

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
        if (op === 'minus') { children = Math.max(children - 1, 0); seats = Math.min(seats, children); }
        childrenInput.value = children;
        seatInput.value     = seats;
    } else if (input === seatInput) {
        if (op === 'plus')  seats = Math.min(seats + 1, children);
        if (op === 'minus') seats = Math.max(seats - 1, 0);
        seatInput.value = seats;
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
    document.getElementById('add-adults').value     = 1;
    document.getElementById('add-children').value   = 0;
    document.getElementById('add-child-seat').value = 0;
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
        .then(d => showToast(d.message, d.success))
        .catch(() => showToast('Bir hata oluştu.', false));
}

// Ops checkbox değişimi
var outsourceSaved            = false;
var outsourceChkRef           = null;
var outsourceModalCurrentName = '';
var outsourceModalCurrentPid  = 0;

document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('ops-check')) return;
    const id    = e.target.dataset.id;
    const field = e.target.dataset.field;

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

                // Dönüş rezervasyonu: alış saati alanlarını göster
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
            var fdClear = new FormData();
            fdClear.append('action', 'clear_outsource');
            fdClear.append('id', id);
            fdClear.append('csrf_token', csrfToken);
            fetch(apiUrl, {method:'POST', body:fdClear})
                .then(function(r) { return r.json(); })
                .then(function(d) { showToast(d.message, d.success); })
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
                    if (wrap) wrap.style.display = '';
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
                const waPhone  = ($('#outsource-name-input').find('option:selected').data('phone') || '').toString().trim();
                if (waToggle.checked && waPhone) {
                    sendOutsourceWhatsApp(id, waPhone, name, price, pickupTime);
                }

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
function sendOutsourceWhatsApp(bookingId, partnerPhone, partnerName, partnerPrice, pickupTimeOverride) {
    var bData = bookingsData.find(function(x) { return x.id == bookingId; });
    if (!bData) return;

    // Yolcuları çek
    var fdp = new FormData();
    fdp.append('action', 'get_passengers');
    fdp.append('id', bookingId);
    fdp.append('csrf_token', csrfToken);

    fetch(apiUrl, {method:'POST', body:fdp})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var passengers = (d.success && d.data && d.data.passengers) ? d.data.passengers : [];
            var msg = buildOutsourceMessage(bData, partnerPrice, pickupTimeOverride, passengers);
            var waNumber = (partnerPhone || '').replace(/[^0-9]/g, '');
            if (!waNumber) return;
            var url = 'https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg);
            window.open(url, '_blank');
        })
        .catch(function() {});
}

function buildOutsourceMessage(b, partnerPrice, pickupTimeOverride, passengers) {
    var AIRPORT  = 'ANTALYA AİRPORT AYT';
    var hotel    = b.hotel_address || '-';
    var isReturn = b.booking_direction === 'return';
    var adults   = b.adults || 0;
    var children = b.children || 0;

    var paxStr = adults + ' Yetişkin' + (children > 0 ? ' + ' + children + ' Çocuk' : '');
    var price  = b.total_price > 0
        ? (parseFloat(b.total_price).toLocaleString('tr-TR', {minimumFractionDigits:0}) + ' ' + (b.currency || 'EUR'))
        : '-';
    var hak    = (partnerPrice && parseFloat(partnerPrice) > 0)
        ? (parseFloat(partnerPrice).toLocaleString('tr-TR', {minimumFractionDigits:0}) + ' ' + (b.currency || 'EUR'))
        : '-';

    function fmtDate(d) {
        if (!d) return '';
        var dt = new Date(d);
        return dt.toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric'});
    }
    function fmtTime(t) { return t ? t.substring(0, 5) : ''; }

    var lines = [];
    if (isReturn) {
        lines.push(hotel + ' --> ' + AIRPORT);
        var pickupTime = pickupTimeOverride || (b.pickup_time ? b.pickup_time.substring(0,5) : '');
        var pickupDate = fmtDate(b.flight_date);
        lines.push('*OTELDEN ALINIŞ: ' + pickupDate + ' - ' + pickupTime + '*');
        lines.push('Uçuş: ' + fmtDate(b.flight_date) + ' - ' + fmtTime(b.flight_time));
        lines.push('Kişi: ' + paxStr);
    } else {
        lines.push(AIRPORT + ' --> ' + hotel);
        lines.push(fmtDate(b.flight_date) + ' - ' + fmtTime(b.flight_time));
        lines.push('Kişi: ' + paxStr);
    }
    if (b.flight_number) {
        lines.push('Uçuş No: ' + b.flight_number);
    }

    lines.push('');
    lines.push('*TAHSİLAT: ' + price + '*');
    lines.push('HAKEDİŞ: ' + hak);

    if (passengers && passengers.length > 0) {
        lines.push('');
        lines.push('*YOLCULAR*');
        passengers.forEach(function(p) {
            if (p.full_name) {
                var tag = p.passenger_type === 'child' ? ' (Çocuk)' : '';
                lines.push('• ' + p.full_name + tag);
            }
        });
    }

    return lines.join('\n');
}

// ─── İş Durumu Modalı ─────────────────────────────────────────────────────────
function openOpsStatus(id) {
    const body    = document.getElementById('ops-status-body');
    const infoBox = document.getElementById('ops-booking-info');
    body.innerHTML    = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm"></span></div>';
    infoBox.innerHTML = '<div class="col-12 text-center py-2"><span class="spinner-border spinner-border-sm"></span></div>';
    new bootstrap.Modal(document.getElementById('opsStatusModal')).show();

    // Rezervasyon bilgilerini bookingsData'dan hemen doldur
    var bData = bookingsData.find(function(x) { return x.id == id; });
    if (bData) {
        var rawDate = bData.flight_date || bData.pickup_date || '';
        if (rawDate) {
            var d2 = new Date(rawDate);
            rawDate = d2.toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric'});
        }
        var rawTime = bData.flight_time ? bData.flight_time.substring(0, 5) : '—';
        var paxStr  = (bData.adults || 1) + ' Yetişkin' + ((bData.children || 0) > 0 ? ' + ' + bData.children + ' Çocuk' : '');
        var priceVal = bData.total_price > 0
            ? (parseFloat(bData.total_price).toLocaleString('tr-TR', {minimumFractionDigits:2}) + ' ' + (bData.currency || 'EUR'))
            : '—';
        var isReturn = bData.booking_direction === 'return';

        function infoCell(label, value, bold) {
            return '<div class="col-6 col-md-3">'
                + '<div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;">' + label + '</div>'
                + '<div class="' + (bold ? 'fw-bold' : 'fw-semibold') + '">' + (value || '—') + '</div>'
                + '</div>';
        }

        var infoHtml = infoCell('Rezervasyon No', '<span class="text-primary">#' + bData.booking_number + '</span>', true)
            + infoCell('Müşteri', bData.customer_name)
            + infoCell('Tarih', rawDate || '—')
            + infoCell('Uçuş Saati', rawTime);

        if (isReturn && bData.pickup_time) {
            infoHtml += infoCell('Alış Saati', '<span style="color:#1c4b56;">' + bData.pickup_time.substring(0, 5) + '</span>', true);
        }

        infoHtml += infoCell('Otel / Adres', bData.hotel_address)
            + infoCell('Araç', bData.vehicle_name)
            + infoCell('Kişi', paxStr)
            + infoCell('Uçuş No', bData.flight_number);

        infoHtml += '<div class="col-12 mt-2 pt-2" style="border-top:1px dashed #b8d4f0;">'
            + '<span class="text-muted" style="font-size:.75rem;">Rezervasyon Tutarı</span>'
            + '<span class="fw-bold ms-2" style="font-size:1.4rem;color:#1c4b56;">' + priceVal + '</span>'
            + '</div>';

        infoBox.innerHTML = infoHtml;
    }

    // Operasyonel durumu AJAX ile çek
    const fd = new FormData();
    fd.append('action', 'get_ops');
    fd.append('id', id);
    fd.append('csrf_token', csrfToken);

    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) { body.innerHTML = '<p class="text-danger mb-0">Veri alınamadı.</p>'; return; }
            const ops = d.data;
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
                + '</div>';

            if (ops.is_outsourced == 1) {
                html += '<div class="rounded p-3 mt-1" style="background:#fffbf0;border:1px solid #f6c23e;">'
                    + '<div class="row g-2">';

                var partnerHtml = ops.outsource_name || '<em class="text-muted">—</em>';
                if (ops.outsource_phone) {
                    partnerHtml += '<br><small class="text-muted"><i class="bi bi-telephone me-1"></i>' + ops.outsource_phone + '</small>';
                }
                html += '<div class="col-6"><div class="text-muted" style="font-size:.72rem;text-transform:uppercase;">Kime</div>'
                    + '<div class="fw-semibold">' + partnerHtml + '</div></div>';

                if (ops.outsource_pickup_time) {
                    html += '<div class="col-6"><div class="text-muted" style="font-size:.72rem;text-transform:uppercase;">Alış Saati</div>'
                        + '<div class="fw-bold">' + ops.outsource_pickup_time.substring(0, 5) + '</div></div>';
                }

                if (ops.outsource_price) {
                    html += '<div class="col-12 mt-1 pt-1" style="border-top:1px dashed #f6c23e;">'
                        + '<span class="text-muted" style="font-size:.75rem;">Verilen Tutar</span>'
                        + '<span class="fw-bold ms-2 text-danger fs-5">' + parseFloat(ops.outsource_price).toFixed(2) + '</span>'
                        + '</div>';
                }

                html += '</div></div>';
            }

            html += '</div>';
            body.innerHTML = html;
        })
        .catch(function() { body.innerHTML = '<p class="text-danger mb-0">Bağlantı hatası.</p>'; });
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
        toggle.disabled = false;
        toggle.checked  = true;
        hint.style.display = 'none';
    } else {
        toggle.disabled = true;
        toggle.checked  = false;
        hint.style.display = '';
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

            // Tüm hotel-select'lere yeni option ekle ve hedef select'te seç
            document.querySelectorAll('.hotel-select').forEach(function(sel) {
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
</script>
