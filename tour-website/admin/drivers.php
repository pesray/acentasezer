<?php
/**
 * Şöför Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$drivers = $db->query("
    SELECT d.*, v.brand, v.model
    FROM drivers d
    LEFT JOIN vehicles v ON d.vehicle_id = v.id
    ORDER BY d.name, d.surname ASC
")->fetchAll();

$pageTitle = 'Şöförler';
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
    <h1 class="h3 mb-0"><i class="bi bi-person-badge me-2"></i>Şöförler</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#driverModal" onclick="openAdd()">
        <i class="bi bi-plus-lg me-1"></i>Yeni Şöför Ekle
    </button>
</div>

<!-- Tablo -->
<div class="card table-card">
    <div class="card-body">
        <?php if (empty($drivers)): ?>
        <div class="text-center py-5">
            <i class="bi bi-person-badge display-1 text-muted"></i>
            <p class="text-muted mt-3">Henüz şöför eklenmemiş.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#driverModal" onclick="openAdd()">
                <i class="bi bi-plus-lg me-1"></i> İlk Şöförü Ekle
            </button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table id="driversTable" class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Şöför Adı</th>
                        <th>Telefon</th>
                        <th>Araç</th>
                        <th width="100">Plaka</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drivers as $d): ?>
                    <tr>
                        <td>
                            <strong><?= e($d['name']) ?> <?= e($d['surname']) ?></strong>
                        </td>
                        <td>
                            <?php if ($d['phone']): ?>
                            <i class="bi bi-telephone me-1 text-muted"></i><?= e($d['phone']) ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['vehicle_id']): ?>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-car-front me-1"></i><?= e($d['brand']) ?> <?= e($d['model']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">Araç yok</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['plate']): ?>
                            <code><?= e($d['plate']) ?></code>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="openEdit(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteDriver(<?= $d['id'] ?>)" title="Sil">
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
<div class="modal fade" id="driverModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;">
                <h5 class="modal-title" id="driverModalTitle"><i class="bi bi-person-badge me-2"></i>Şöför Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0) invert(1);"></button>
            </div>
            <form id="driverForm">
                <input type="hidden" name="id" id="driver-id">
                <input type="hidden" name="action" id="driver-action" value="create">

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ad *</label>
                            <input type="text" name="name" id="driver-name" class="form-control" required placeholder="Örn: Ahmet">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Soyad *</label>
                            <input type="text" name="surname" id="driver-surname" class="form-control" required placeholder="Örn: Çelik">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Telefon</label>
                        <input type="tel" name="phone" id="driver-phone" class="form-control" placeholder="+90 555 123 45 67">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Araç *</label>
                            <select name="vehicle_id" id="driver-vehicle" class="form-select" required>
                                <option value="">-- Araç Seç --</option>
                                <?php
                                $vehicles = $db->query("SELECT id, brand, model FROM vehicles ORDER BY brand, model")->fetchAll();
                                foreach ($vehicles as $v):
                                ?>
                                <option value="<?= $v['id'] ?>"><?= e($v['brand']) ?> <?= e($v['model']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Plaka</label>
                            <input type="text" name="plate" id="driver-plate" class="form-control" placeholder="Örn: 34 ABC 1234" maxlength="20">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" id="driver-save-btn">
                        <i class="bi bi-check-lg me-1"></i>Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const apiUrl    = window.ADMIN_URL + '/api/handler.php?entity=drivers';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, ok) {
    const t = document.getElementById('ajaxToast');
    t.className = 'toast align-items-center border-0 text-white ' + (ok ? 'bg-success' : 'bg-danger');
    document.getElementById('ajaxToastBody').textContent = msg;
    new bootstrap.Toast(t, {delay: 3500}).show();
}

function openAdd() {
    document.getElementById('driverModalTitle').innerHTML = '<i class="bi bi-person-badge me-2"></i>Yeni Şöför Ekle';
    document.getElementById('driver-action').value = 'create';
    document.getElementById('driver-id').value = '';
    document.getElementById('driver-name').value = '';
    document.getElementById('driver-surname').value = '';
    document.getElementById('driver-phone').value = '';
    document.getElementById('driver-vehicle').value = '';
    document.getElementById('driver-plate').value = '';
}

function openEdit(d) {
    document.getElementById('driverModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Şöför Düzenle';
    document.getElementById('driver-action').value = 'update';
    document.getElementById('driver-id').value = d.id;
    document.getElementById('driver-name').value = d.name || '';
    document.getElementById('driver-surname').value = d.surname || '';
    document.getElementById('driver-phone').value = d.phone || '';
    document.getElementById('driver-vehicle').value = d.vehicle_id || '';
    document.getElementById('driver-plate').value = d.plate || '';
    new bootstrap.Modal(document.getElementById('driverModal')).show();
}

document.getElementById('driverForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd  = new FormData(this);
    fd.append('csrf_token', csrfToken);
    var btn = document.getElementById('driver-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor...';
    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast(d.message, d.success);
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('driverModal')).hide();
                setTimeout(function() { location.reload(); }, 400);
            }
        })
        .catch(function() { showToast('Bir hata oluştu.', false); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
        });
});

function deleteDriver(id) {
    if (!confirm('Bu şöförü silmek istediğinizden emin misiniz?')) return;
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
