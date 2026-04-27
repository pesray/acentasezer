<?php
/**
 * Otel Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$hotels = $db->query("SELECT * FROM hotels ORDER BY name ASC")->fetchAll();

$pageTitle = 'Oteller';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <div id="ajaxToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold" id="ajaxToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Başlık -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-building me-2"></i>Oteller</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#hotelModal" onclick="openAdd()">
        <i class="bi bi-plus-lg me-1"></i>Yeni Otel Ekle
    </button>
</div>

<!-- Tablo -->
<div class="card table-card">
    <div class="card-body">
        <?php if (empty($hotels)): ?>
        <div class="text-center py-5">
            <i class="bi bi-building display-1 text-muted"></i>
            <p class="text-muted mt-3">Henüz otel eklenmemiş.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#hotelModal" onclick="openAdd()">
                <i class="bi bi-plus-lg me-1"></i> İlk Oteli Ekle
            </button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table id="hotelsTable" class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="80">Otel</th>
                        <th>Otel Adı</th>
                        <th>Adres</th>
                        <th width="160">Telefon</th>
                        <th width="120">Mesafe</th>
                        <th width="90">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hotels as $h): ?>
                    <tr>
                        <td>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:60px;height:40px;">
                                <i class="bi bi-building fs-5 text-primary"></i>
                            </div>
                        </td>
                        <td>
                            <strong><?= e($h['name']) ?></strong>
                            <?php if (!$h['is_active']): ?>
                            <span class="badge bg-secondary ms-1">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?= e($h['address'] ?? '-') ?></td>
                        <td>
                            <?php if ($h['phone']): ?>
                            <i class="bi bi-telephone me-1 text-muted"></i><?= e($h['phone']) ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($h['distance_km'] !== null): ?>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-signpost me-1"></i><?= number_format((float)$h['distance_km'], 1, ',', '.') ?> km
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($h['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="openEdit(<?= htmlspecialchars(json_encode($h), ENT_QUOTES) ?>)" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-<?= $h['is_active'] ? 'warning' : 'success' ?>" onclick="toggleActive(<?= $h['id'] ?>)" title="<?= $h['is_active'] ? 'Pasife Al' : 'Aktife Al' ?>">
                                    <i class="bi bi-<?= $h['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteHotel(<?= $h['id'] ?>)" title="Sil">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="hotelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;">
                <h5 class="modal-title" id="hotelModalTitle"><i class="bi bi-building me-2"></i>Otel Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0) invert(1);"></button>
            </div>
            <form id="hotelForm">
                <input type="hidden" name="id" id="hotel-id">
                <input type="hidden" name="action" id="hotel-action" value="create">

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Otel Adı *</label>
                            <input type="text" name="name" id="hotel-name" class="form-control" required placeholder="Örn: Hilton Antalya">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Telefon</label>
                            <input type="text" name="phone" id="hotel-phone" class="form-control" placeholder="+90 242 000 00 00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adres</label>
                        <textarea name="address" id="hotel-address" class="form-control" rows="2" placeholder="Otel adresi..."></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mesafe (km)</label>
                            <div class="input-group">
                                <input type="number" name="distance_km" id="hotel-distance" class="form-control" min="0" step="0.1" placeholder="0.0">
                                <span class="input-group-text">km</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="hotel-active" value="1" checked>
                                <label class="form-check-label fw-semibold" for="hotel-active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" id="hotel-save-btn">
                        <i class="bi bi-check-lg me-1"></i>Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const apiUrl    = window.ADMIN_URL + '/api/handler.php?entity=hotels';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, ok) {
    const t = document.getElementById('ajaxToast');
    t.className = 'toast align-items-center border-0 text-white ' + (ok ? 'bg-success' : 'bg-danger');
    document.getElementById('ajaxToastBody').textContent = msg;
    new bootstrap.Toast(t, {delay: 3500}).show();
}


function openAdd() {
    document.getElementById('hotelModalTitle').innerHTML = '<i class="bi bi-building me-2"></i>Yeni Otel Ekle';
    document.getElementById('hotel-action').value = 'create';
    document.getElementById('hotel-id').value = '';
    document.getElementById('hotel-name').value = '';
    document.getElementById('hotel-phone').value = '';
    document.getElementById('hotel-address').value = '';
    document.getElementById('hotel-distance').value = '';
    document.getElementById('hotel-sort').value = '0';
    document.getElementById('hotel-active').checked = true;
}

function openEdit(h) {
    document.getElementById('hotelModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Otel Düzenle';
    document.getElementById('hotel-action').value = 'update';
    document.getElementById('hotel-id').value = h.id;
    document.getElementById('hotel-name').value = h.name || '';
    document.getElementById('hotel-phone').value = h.phone || '';
    document.getElementById('hotel-address').value = h.address || '';
    document.getElementById('hotel-distance').value = h.distance_km !== null ? h.distance_km : '';
    document.getElementById('hotel-active').checked = h.is_active == 1;
    new bootstrap.Modal(document.getElementById('hotelModal')).show();
}

document.getElementById('hotelForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd  = new FormData(this);
    fd.append('csrf_token', csrfToken);
    var btn = document.getElementById('hotel-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor...';
    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast(d.message, d.success);
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('hotelModal')).hide();
                setTimeout(function() { location.reload(); }, 400);
            }
        })
        .catch(function() { showToast('Bir hata oluştu.', false); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
        });
});

function toggleActive(id) {
    var fd = new FormData();
    fd.append('action', 'toggle_active');
    fd.append('id', id);
    fd.append('csrf_token', csrfToken);
    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) { showToast(d.message, d.success); if (d.success) setTimeout(function() { location.reload(); }, 300); })
        .catch(function() { showToast('Bir hata oluştu.', false); });
}

function deleteHotel(id) {
    if (!confirm('Bu oteli silmek istediğinizden emin misiniz?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('csrf_token', csrfToken);
    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) { showToast(d.message, d.success); if (d.success) setTimeout(function() { location.reload(); }, 300); })
        .catch(function() { showToast('Bir hata oluştu.', false); });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
