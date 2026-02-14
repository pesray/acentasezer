<?php
/**
 * Rezervasyon Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$view = $_GET['view'] ?? 'arrival';
if (!in_array($view, ['arrival', 'return'])) $view = 'arrival';

$directionFilter = $view === 'return' ? 'return' : 'outbound';
$viewTitle = $view === 'return' ? 'Dönüş Rezervasyonları' : 'Geliş Rezervasyonları';

$statusLabels = [
    'pending' => ['Onay Bekliyor', 'warning', 'bi-hourglass-split'],
    'confirmed' => ['Onaylandı', 'success', 'bi-check-circle'],
    'cancelled' => ['İptal Edildi', 'danger', 'bi-x-circle']
];

$typeLabels = [
    'tour' => ['Tur', 'primary'],
    'transfer' => ['Transfer', 'info']
];

// Onay bekleyen rezervasyonlar (filtreye göre)
$pendingStmt = $db->prepare("
    SELECT b.*, 
           t.title as tour_title,
           d.title as destination_title,
           CONCAT(v.brand, ' ', v.model) as vehicle_name
    FROM bookings b 
    LEFT JOIN tours t ON b.tour_id = t.id 
    LEFT JOIN destinations d ON b.destination_id = d.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.booking_status = 'pending' AND COALESCE(b.booking_direction, 'outbound') = ?
    ORDER BY b.created_at DESC
");
$pendingStmt->execute([$directionFilter]);
$pendingBookings = $pendingStmt->fetchAll();

// Tüm rezervasyonlar (filtreye göre)
$allStmt = $db->prepare("
    SELECT b.*, 
           t.title as tour_title,
           d.title as destination_title,
           CONCAT(v.brand, ' ', v.model) as vehicle_name
    FROM bookings b 
    LEFT JOIN tours t ON b.tour_id = t.id 
    LEFT JOIN destinations d ON b.destination_id = d.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE COALESCE(b.booking_direction, 'outbound') = ?
    ORDER BY b.created_at DESC
");
$allStmt->execute([$directionFilter]);
$allBookings = $allStmt->fetchAll();

// Transferler ve araçları (yeni rezervasyon modalı için)
$destinations = $db->query("
    SELECT d.id, COALESCE(dt.title, d.title) as title
    FROM destinations d
    LEFT JOIN destination_translations dt ON d.id = dt.destination_id AND dt.language_code = 'tr'
    WHERE d.status = 'published'
    ORDER BY d.sort_order, d.title
")->fetchAll();

$destinationVehicles = [];
$dvStmt = $db->query("
    SELECT dv.destination_id, dv.vehicle_id, dv.price, dv.currency,
           CONCAT(v.brand, ' ', v.model) as vehicle_name, v.capacity
    FROM destination_vehicles dv
    JOIN vehicles v ON dv.vehicle_id = v.id
    WHERE dv.language_code = 'tr' AND v.is_active = 1
    ORDER BY v.sort_order, dv.price ASC
");
foreach ($dvStmt->fetchAll() as $dv) {
    $destinationVehicles[$dv['destination_id']][] = [
        'vehicle_id' => (int)$dv['vehicle_id'],
        'vehicle_name' => $dv['vehicle_name'],
        'capacity' => (int)$dv['capacity'],
        'price' => (float)$dv['price'],
        'currency' => $dv['currency'],
    ];
}

$pageTitle = $viewTitle;
require_once __DIR__ . '/includes/header.php';
?>

<style>
.pending-card {
    border-left: 4px solid #ffc107;
    transition: all 0.2s;
    cursor: pointer;
}
.pending-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.pending-card .booking-number {
    font-weight: 700;
    color: #4e73df;
}
.pending-card .booking-meta {
    font-size: 0.85rem;
    color: #858796;
}
.pending-card .booking-customer {
    font-weight: 600;
}
.pending-count-badge {
    background: #ffc107;
    color: #000;
    font-size: 1rem;
    padding: 0.35em 0.65em;
}
.booking-detail-label {
    font-weight: 600;
    color: #5a5c69;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.booking-detail-value {
    font-size: 1rem;
    margin-bottom: 0.75rem;
}
.modal-booking .modal-header {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: #fff;
}
.modal-booking .modal-header .btn-close {
    filter: brightness(0) invert(1);
}
.status-select-group .btn {
    font-size: 0.85rem;
    padding: 0.35rem 0.75rem;
}
#bookingModal .modal-body {
    max-height: 75vh;
    overflow-y: auto;
}
</style>

<!-- Toast Bildirimleri -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <div id="ajaxToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold" id="ajaxToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-<?= $view === 'return' ? 'box-arrow-up-right' : 'box-arrow-in-down-right' ?> me-2"></i><?= $viewTitle ?>
    </h1>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split me-1"></i> <?= count($pendingBookings) ?> Bekleyen</span>
        <span class="badge bg-secondary fs-6"><i class="bi bi-list me-1"></i> <?= count($allBookings) ?> Toplam</span>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBookingModal">
            <i class="bi bi-plus-lg me-1"></i>Yeni Rezervasyon
        </button>
    </div>
</div>

<!-- Onay Bekleyen Rezervasyonlar -->
<?php if (!empty($pendingBookings)): ?>
<div class="mb-5">
    <h5 class="mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle text-warning"></i>
        Onay Bekleyen Rezervasyonlar
        <span class="badge pending-count-badge"><?= count($pendingBookings) ?></span>
    </h5>
    <div class="row g-2">
        <?php foreach ($pendingBookings as $pb): ?>
        <div class="col-lg-4 col-xl-3">
            <div class="card pending-card h-100" onclick="openBookingModal(<?= $pb['id'] ?>)">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="booking-number">#<?= e($pb['booking_number']) ?></span>
                        <?php if ((float)$pb['total_price'] > 0): ?>
                            <span class="badge bg-success font-weight-bold fs-6"><?= number_format((float)$pb['total_price'], 0, ',', '.') ?> <?= e($pb['currency'] ?? 'TRY') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="booking-customer mb-1">
                        <i class="bi bi-person me-1"></i><?= e($pb['customer_name']) ?>
                    </div>
                    <div class="booking-phone mb-1">
                        <?php if ($pb['customer_phone']): ?>
                       <strong><i class="bi bi-telephone me-1"></i><?= e($pb['customer_phone']) ?></strong>
                        <?php endif; ?>
                    </div>
                    <hr class="my-2">
                        <?php 
                        $dateField = $pb['pickup_date'] ?: $pb['flight_date'];
                        if ($dateField): ?>
                            <div>
                                <strong><i class="bi bi-airplane me-1"></i>: <?= date('d.m.Y', strtotime($dateField)) ?></strong>
                                <?php if ($pb['flight_time']): ?><strong class="badge bg-primary text-white font-weight-bold fs-6">
                                    <?= e(date('H:i', strtotime($pb['flight_time']))) ?></strong>
                                <?php endif; ?>                               
                                
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($pb['booking_type'] === 'tour' && $pb['tour_title']): ?>
                        <div><i class="bi bi-compass me-1"></i><strong>Tur:</strong> <?= e($pb['tour_title']) ?></div>
                        <?php elseif ($pb['destination_title']): ?>
                        <div>
                            <strong><i class="bi bi-geo-alt me-1"></i>: <?= e($pb['hotel_address']) ?></strong>
                            <?php if ($view === 'return' && $pb['pickup_time']): ?>
                                <strong class="badge bg-success text-white fw-bold fs-6">
                                    <?= e(date('H:i', strtotime($pb['pickup_time']))) ?>
                                </strong>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($pb['vehicle_name'] && trim($pb['vehicle_name'])): ?>
                        <div><strong><i class="bi bi-car-front me-1"></i>: <?= e($pb['vehicle_name']) ?></strong></div>
                        <?php endif; ?>
                        
                        <div><strong><i class="bi bi-people me-1"></i>: <?= (int)$pb['adults'] ?> Yetişkin<?= (int)$pb['children'] > 0 ? ', ' . (int)$pb['children'] . ' Çocuk' : '' ?></strong></div>
                   
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('d.m.Y H:i', strtotime($pb['created_at'])) ?></small>
                    </div>
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

<!-- Tüm Rezervasyonlar Listesi -->
<div class="card table-card">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Tüm Rezervasyonlar</h6>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="input-group input-group-sm" style="width:180px;">
                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    <input type="date" id="filter-date" class="form-control" value="">
                </div>
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
        <table id="bookingsTable" class="table table-hover datatable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Müşteri</th>
                    <th><?= $view === 'return' ? 'Alış Otel Adı' : 'Varış Otel Adı' ?></th>
                    <th>Uçuş</th>
                    <?php if ($view === 'return'): ?>
                    <th>Alış Saati</th>
                    <?php endif; ?>
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
                    $itemName = $b['booking_type'] === 'tour' ? ($b['tour_title'] ?? '-') : ($b['destination_title'] ?? '-');
                ?>
                <tr>
                    <td><strong class="text-primary"><?= e($b['booking_number']) ?></strong></td>
                    <td>
                        <?= e($b['customer_name']) ?><br />
                      <strong><?= e($b['customer_phone']) ?></strong>
                    </td>
                    <td><?= e(trim($b['hotel_address'] ?? '') ?: '-') ?></td>
                    <td>
                        <?= $dateField ? date('d.m.Y', strtotime($dateField)) : '-' ?>
                        <?php if ( $b['flight_time']): ?>
                            <strong><?= e(date('H:i', strtotime( $b['flight_time']))) ?></strong>
                        <?php endif; ?>
                    </td>
                    <?php if ($view === 'return'): ?>
                    <td>
                        <?php if ($b['pickup_time']): ?>
                            <strong><?= e(date('H:i', strtotime($b['pickup_time']))) ?></strong>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td><?= e(trim($b['vehicle_name'] ?? '') ?: '-') ?></td>
                    
                    <td><?= (int)$b['adults'] ?>Y <?= (int)$b['children'] > 0 ? '+ ' . (int)$b['children'] . 'Ç' : '' ?></td>
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
    </div>
</div>

<!-- Rezervasyon Detay / Düzenleme Modalı -->
<div class="modal fade modal-booking" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Rezervasyon Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookingEditForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="modal-booking-id" value="">
                
                <div class="modal-body">
                    <div id="modal-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Yükleniyor...</p>
                    </div>
                    
                    <div id="modal-content" style="display:none;">
                        <!-- Üst Bilgi Kartı -->
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
                        
                        <!-- Durum Seçimi -->
                        <div class="mb-4">
                            <label class="booking-detail-label d-block mb-2">Rezervasyon Durumu</label>
                            <div class="status-select-group btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="booking_status" id="status-pending" value="pending">
                                <label class="btn btn-outline-warning" for="status-pending"><i class="bi bi-hourglass-split me-1"></i> Bekliyor</label>
                                
                                <input type="radio" class="btn-check" name="booking_status" id="status-confirmed" value="confirmed">
                                <label class="btn btn-outline-success" for="status-confirmed"><i class="bi bi-check-circle me-1"></i> Onayla</label>
                                
                                <input type="radio" class="btn-check" name="booking_status" id="status-cancelled" value="cancelled">
                                <label class="btn btn-outline-danger" for="status-cancelled"><i class="bi bi-x-circle me-1"></i> İptal</label>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Müşteri Bilgileri -->
                        <h6 class="mb-3"><i class="bi bi-person me-2"></i>Müşteri Bilgileri</h6>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Ad Soyad *</label>
                                <input type="text" name="customer_name" id="modal-customer-name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">E-posta *</label>
                                <input type="email" name="customer_email" id="modal-customer-email" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefon</label>
                                <input type="text" name="customer_phone" id="modal-customer-phone" class="form-control">
                            </div>
                        </div>
                        
                        <!-- Tur/Transfer Bilgileri -->
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
                        
                        <!-- Tur Alanları (pickup) -->
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
                        
                        <!-- Transfer Alanları (flight) -->
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
                                    <input type="text" name="flight_number" id="modal-flight-number" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">

                            <?php if($view ==='return'): ?>
                                 <div class="col-9">
                                    <label class="form-label">Otel Adresi</label>
                                    <input type="text" name="hotel_address" id="modal-hotel-address" class="form-control">
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Alış Saati</label>
                                    <input type="time" name="pickup_time" id="modal-return-pickup-time" class="form-control">
                                </div>
                            <?php else: ?>

                                <div class="col-12">
                                    <label class="form-label">Otel Adresi</label>
                                    <input type="text" name="hotel_address" id="modal-hotel-address" class="form-control">
                                </div>

                            <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Yolcu Bilgileri -->
                        <h6 class="mb-3"><i class="bi bi-people me-2"></i>Yolcu Bilgileri</h6>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Yetişkin Sayısı</label>
                                <input type="number" name="adults" id="modal-adults" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Çocuk Sayısı</label>
                                <input type="number" name="children" id="modal-children" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Çocuk Koltuğu</label>
                                <input type="number" name="child_seat" id="modal-child-seat" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        
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
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Yeni Rezervasyon Ekleme Modalı -->
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
                    
                    <!-- Transfer & Araç Seçimi -->
                    <h6 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Transfer & Araç Seçimi</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Transfer *</label>
                            <select name="destination_id" id="add-destination" class="form-select" required>
                                <option value="">-- Transfer Seçin --</option>
                                <?php foreach ($destinations as $dest): ?>
                                <option value="<?= $dest['id'] ?>"><?= e($dest['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Araç *</label>
                            <select name="vehicle_id" id="add-vehicle" class="form-select" required disabled>
                                <option value="">-- Önce transfer seçin --</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Müşteri Bilgileri -->
                    <h6 class="mb-3"><i class="bi bi-person me-2"></i>Müşteri Bilgileri</h6>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" name="customer_name" class="form-control" required>
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
                    
                    <!-- Uçuş Bilgileri -->
                    <h6 class="mb-3"><i class="bi bi-airplane me-2"></i>Uçuş Bilgileri</h6>
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
                            <input type="text" name="flight_number" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <?php if ($view === 'return'): ?>
                        <div class="col-md-9">
                            <label class="form-label">Otel Adresi</label>
                            <input type="text" name="hotel_address" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Alış Saati</label>
                            <input type="time" name="pickup_time" class="form-control">
                        </div>
                        <?php else: ?>
                        <div class="col-md-12">
                            <label class="form-label">Otel Adresi</label>
                            <input type="text" name="hotel_address" class="form-control">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Yolcu Bilgileri -->
                    <h6 class="mb-3"><i class="bi bi-people me-2"></i>Yolcu Bilgileri</h6>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Yetişkin Sayısı</label>
                            <input type="number" name="adults" class="form-control" min="1" value="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Çocuk Sayısı</label>
                            <input type="number" name="children" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Çocuk Koltuğu</label>
                            <input type="number" name="child_seat" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    
                    <!-- Fiyat -->
                    <h6 class="mb-3"><i class="bi bi-cash me-2"></i>Fiyat Bilgileri</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Toplam Tutar</label>
                            <input type="number" name="total_price" id="add-total-price" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Para Birimi</label>
                            <select name="currency" id="add-currency" class="form-select">
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
                    <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i> Rezervasyon Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tüm rezervasyon verileri (JSON olarak sayfaya göm)
const bookingsData = <?= json_encode(array_map(function($b) {
    return [
        'id' => $b['id'],
        'booking_number' => $b['booking_number'],
        'booking_type' => $b['booking_type'],
        'booking_status' => $b['booking_status'],
        'customer_name' => $b['customer_name'],
        'customer_email' => $b['customer_email'],
        'customer_phone' => $b['customer_phone'],
        'tour_title' => $b['tour_title'],
        'destination_title' => $b['destination_title'],
        'vehicle_name' => trim($b['vehicle_name'] ?? ''),
        'pickup_location' => $b['pickup_location'],
        'pickup_date' => $b['pickup_date'],
        'pickup_time' => $b['pickup_time'],
        'return_time' => $b['return_time'],
        'flight_date' => $b['flight_date'],
        'flight_time' => $b['flight_time'],
        'flight_number' => $b['flight_number'],
        'hotel_address' => $b['hotel_address'],
        'adults' => (int)$b['adults'],
        'children' => (int)$b['children'],
        'child_seat' => (int)$b['child_seat'],
        'total_price' => (float)$b['total_price'],
        'currency' => $b['currency'],
        'notes' => $b['notes'],
        'admin_notes' => $b['admin_notes'],
        'created_at' => $b['created_at'],
    ];
}, $allBookings), JSON_UNESCAPED_UNICODE) ?>;

// Transfer -> Araç verileri
const destinationVehicles = <?= json_encode($destinationVehicles, JSON_UNESCAPED_UNICODE) ?>;

// Yeni rezervasyon: transfer seçilince araçları yükle
(function() {
    const destSelect = document.getElementById('add-destination');
    const vehicleSelect = document.getElementById('add-vehicle');
    const priceInput = document.getElementById('add-total-price');
    const currencySelect = document.getElementById('add-currency');

    destSelect.addEventListener('change', function() {
        const destId = this.value;
        vehicleSelect.innerHTML = '';
        priceInput.value = '';

        if (!destId || !destinationVehicles[destId]) {
            vehicleSelect.innerHTML = '<option value="">-- Önce transfer seçin --</option>';
            vehicleSelect.disabled = true;
            return;
        }

        vehicleSelect.disabled = false;
        vehicleSelect.innerHTML = '<option value="">-- Araç Seçin --</option>';
        destinationVehicles[destId].forEach(function(v) {
            const opt = document.createElement('option');
            opt.value = v.vehicle_id;
            opt.textContent = v.vehicle_name + ' (' + v.capacity + ' kişi) - ' + new Intl.NumberFormat('tr-TR').format(v.price) + ' ' + v.currency;
            opt.dataset.price = v.price;
            opt.dataset.currency = v.currency;
            vehicleSelect.appendChild(opt);
        });
    });

    vehicleSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.dataset.price) {
            priceInput.value = selected.dataset.price;
            currencySelect.value = selected.dataset.currency || 'TRY';
        } else {
            priceInput.value = '';
        }
    });
})();

// Rezervasyon verisini ID ile bul
function findBooking(id) {
    return bookingsData.find(b => b.id == id);
}

// Modal aç
function openBookingModal(id) {
    const booking = findBooking(id);
    if (!booking) return;
    
    const modal = document.getElementById('bookingModal');
    const bsModal = new bootstrap.Modal(modal);
    
    // Loading gizle, content göster
    document.getElementById('modal-loading').style.display = 'none';
    document.getElementById('modal-content').style.display = 'block';
    
    // ID
    document.getElementById('modal-booking-id').value = booking.id;
    
    // Üst bilgi
    document.getElementById('modal-booking-number').textContent = '#' + booking.booking_number;
    document.getElementById('modal-booking-type').innerHTML = booking.booking_type === 'tour' 
        ? '<span class="badge bg-primary fs-6">Tur</span>' 
        : '<span class="badge bg-info fs-6">Transfer</span>';
    document.getElementById('modal-created-at').textContent = booking.created_at 
        ? new Date(booking.created_at).toLocaleString('tr-TR') : '-';
    
    // Durum
    const statusRadio = document.getElementById('status-' + booking.booking_status);
    if (statusRadio) statusRadio.checked = true;
    
    // Müşteri bilgileri
    document.getElementById('modal-customer-name').value = booking.customer_name || '';
    document.getElementById('modal-customer-email').value = booking.customer_email || '';
    document.getElementById('modal-customer-phone').value = booking.customer_phone || '';
    
    // Tur/Transfer adı
    const itemName = booking.booking_type === 'tour' ? (booking.tour_title || '-') : (booking.destination_title || '-');
    document.getElementById('modal-item-name').value = itemName;
    document.getElementById('modal-vehicle-name').value = booking.vehicle_name || '-';
    
    // Tur / Transfer alanları göster/gizle
    if (booking.booking_type === 'tour') {
        document.getElementById('modal-tour-fields').style.display = 'block';
        document.getElementById('modal-transfer-fields').style.display = 'none';
    } else {
        document.getElementById('modal-tour-fields').style.display = 'none';
        document.getElementById('modal-transfer-fields').style.display = 'block';
    }
    
    // Tur alanları
    document.getElementById('modal-pickup-location').value = booking.pickup_location || '';
    document.getElementById('modal-pickup-date').value = booking.pickup_date || '';
    document.getElementById('modal-pickup-time').value = booking.pickup_time || '';
    document.getElementById('modal-return-time').value = booking.return_time || '';
    
    // Transfer alanları
    document.getElementById('modal-flight-date').value = booking.flight_date || '';
    document.getElementById('modal-flight-time').value = booking.flight_time || '';
    document.getElementById('modal-flight-number').value = booking.flight_number || '';
    document.getElementById('modal-hotel-address').value = booking.hotel_address || '';
    
    // Dönüş alış saati
    const returnPickupEl = document.getElementById('modal-return-pickup-time');
    if (returnPickupEl) returnPickupEl.value = booking.pickup_time || '';
    
    // Yolcu
    document.getElementById('modal-adults').value = booking.adults || 1;
    document.getElementById('modal-children').value = booking.children || 0;
    document.getElementById('modal-child-seat').value = booking.child_seat || 0;
    
    // Fiyat
    document.getElementById('modal-total-price').value = booking.total_price || 0;
    document.getElementById('modal-currency').value = booking.currency || 'TRY';
    
    // Notlar
    document.getElementById('modal-notes').value = booking.notes || '';
    document.getElementById('modal-admin-notes').value = booking.admin_notes || '';
    
    bsModal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// Tablo Filtreleme (jQuery footer'dan sonra yükleniyor)
$(document).ready(function() {
    const table = $('#bookingsTable').DataTable();
    const dateColIdx = 3; // Uçuş sütunu
    const statusColIdx = <?= $view === 'return' ? 8 : 7 ?>; // Durum sütunu

    // Custom search - tarih filtresi
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'bookingsTable') return true;
        const filterDate = $('#filter-date').val();
        if (!filterDate) return true;
        const cellText = data[dateColIdx] || '';
        const parts = cellText.trim().match(/(\d{2})\.(\d{2})\.(\d{4})/);
        if (!parts) return false;
        const cellDate = parts[3] + '-' + parts[2] + '-' + parts[1];
        return cellDate === filterDate;
    });

    // Custom search - durum filtresi
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'bookingsTable') return true;
        const filterStatus = $('#filter-status').val();
        if (!filterStatus) return true;
        const cellText = (data[statusColIdx] || '').trim();
        return cellText.indexOf(filterStatus) !== -1;
    });

    // Filtre değişince tabloyu yeniden çiz
    $('#filter-date, #filter-status').on('change', function() {
        table.draw();
    });

    // Temizle butonu
    $('#filter-clear').on('click', function() {
        $('#filter-date').val('');
        $('#filter-status').val('');
        table.draw();
    });

    // Sayfa yüklenince bugünün filtresiyle çiz
    table.draw();
});

// === AJAX CRUD Fonksiyonları ===
const currentView = '<?= $view ?>';
const apiUrl = window.ADMIN_URL + '/api/handler.php?entity=bookings';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Toast göster
function showToast(message, success) {
    const toast = document.getElementById('ajaxToast');
    const body = document.getElementById('ajaxToastBody');
    toast.className = 'toast align-items-center border-0 text-white ' + (success ? 'bg-success' : 'bg-danger');
    body.textContent = message;
    new bootstrap.Toast(toast, { delay: 3000 }).show();
}

// AJAX form gönder
function ajaxSubmit(form, onSuccess) {
    const formData = new FormData(form);
    formData.append('csrf_token', csrfToken);
    const submitBtn = form.querySelector('[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Kaydediliyor...';
    }

    fetch(apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success);
        if (data.success && onSuccess) onSuccess(data);
    })
    .catch(() => showToast('Bir hata oluştu.', false))
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Düzenleme formu AJAX submit
document.getElementById('bookingEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    ajaxSubmit(this, function() {
        bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
        setTimeout(() => location.reload(), 200);
    });
});

// Yeni rezervasyon formu AJAX submit
document.getElementById('addBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    formData.append('booking_direction', currentView === 'return' ? 'return' : 'outbound');
    formData.append('csrf_token', csrfToken);

    const submitBtn = form.querySelector('[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Kaydediliyor...';

    fetch(apiUrl, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addBookingModal')).hide();
            setTimeout(() => location.reload(), 200);
        }
    })
    .catch(() => showToast('Bir hata oluştu.', false))
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Silme AJAX
function deleteBooking(id) {
    if (!confirm('Bu rezervasyonu silmek istediğinizden emin misiniz?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    formData.append('csrf_token', csrfToken);

    fetch(apiUrl, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success);
        if (data.success) {
            setTimeout(() => location.reload(), 200);
        }
    })
    .catch(() => showToast('Bir hata oluştu.', false));
}

// Hızlı durum güncelle (onay bekleyen kartlardan)
function quickStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'quick_status');
    formData.append('id', id);
    formData.append('status', status);
    formData.append('csrf_token', csrfToken);

    fetch(apiUrl, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        showToast(data.message, data.success);
        if (data.success) {
            setTimeout(() => location.reload(), 200);
        }
    })
    .catch(() => showToast('Bir hata oluştu.', false));
}
</script>
