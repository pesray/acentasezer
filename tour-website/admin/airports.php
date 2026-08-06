<?php
/**
 * Havalimanları Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

// Tablo yoksa otomatik oluştur
$db->exec("
    CREATE TABLE IF NOT EXISTS `airports` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(255) NOT NULL,
      `city` VARCHAR(100) DEFAULT NULL,
      `district` VARCHAR(100) DEFAULT NULL,
      `code` VARCHAR(20) DEFAULT NULL,
      `is_default` TINYINT(1) NOT NULL DEFAULT 0,
      `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_airports_is_default` (`is_default`),
      INDEX `idx_airports_is_deleted` (`is_deleted`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Varsayılan kayıt yoksa Antalya Airport ekle
$chkCount = (int)$db->query("SELECT COUNT(*) FROM airports WHERE is_deleted = 0")->fetchColumn();
if ($chkCount === 0) {
    $airportSetting = getSetting('airport_name', 'ANTALYA AİRPORT AYT');
    $db->prepare("INSERT INTO airports (name, city, district, code, is_default, is_deleted) VALUES (?, 'Antalya', 'Muratpaşa', 'AYT', 1, 0)")
       ->execute([$airportSetting]);
}

$airports = $db->query("SELECT * FROM airports WHERE is_deleted = 0 ORDER BY is_default DESC, name ASC")->fetchAll();

$pageTitle = 'Havalimanları';
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
    <h1 class="h3 mb-0"><i class="bi bi-airplane me-2 text-primary"></i>Havalimanları</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#airportModal" onclick="openAdd()">
        <i class="bi bi-plus-lg me-1"></i>Yeni Havalimanı Ekle
    </button>
</div>

<!-- Tablo -->
<div class="card table-card">
    <div class="card-body">
        <?php if (empty($airports)): ?>
        <div class="text-center py-5">
            <i class="bi bi-airplane display-1 text-muted"></i>
            <p class="text-muted mt-3">Henüz havalimanı eklenmemiş.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#airportModal" onclick="openAdd()">
                <i class="bi bi-plus-lg me-1"></i>İlk Havalimanını Ekle
            </button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table id="airportsTable" class="table table-hover datatable" width="100%">
                <thead>
                    <tr>
                        <th>Havalimanı Adı</th>
                        <th>İl / İlçe</th>
                        <th width="120">IATA Kodu</th>
                        <th width="160">Varsayılan</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($airports as $a): ?>
                    <tr>
                        <td>
                            <strong class="fs-6"><?= e($a['name']) ?></strong>
                            <?php if ($a['is_default']): ?>
                            <span class="badge bg-success ms-2"><i class="bi bi-star-fill me-1"></i>Varsayılan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= e(trim(($a['city'] ?? '') . ($a['district'] ? ' / ' . $a['district'] : '')) ?: '-') ?>
                        </td>
                        <td>
                            <?php if (!empty($a['code'])): ?>
                            <span class="badge bg-light text-dark border font-monospace"><?= e($a['code']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input default-toggle-radio" type="radio" name="default_airport_radio"
                                       id="def-<?= $a['id'] ?>"
                                       <?= $a['is_default'] ? 'checked' : '' ?>
                                       onchange="setDefault(<?= $a['id'] ?>)">
                                <label class="form-check-label small fw-semibold" for="def-<?= $a['id'] ?>">
                                    <?= $a['is_default'] ? '<span class="text-success fw-bold">Varsayılan Seçili</span>' : '<span class="text-muted">Varsayılan Yap</span>' ?>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>)"
                                        title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="deleteAirport(<?= $a['id'] ?>)"
                                        title="Sil">
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

<!-- ===================== HAVALİMANI MODAL (EKLE / DÜZENLE) ===================== -->
<div class="modal fade" id="airportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-airplane me-2"></i>Yeni Havalimanı Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="airportForm" method="POST">
                <input type="hidden" name="entity" value="airports">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="airportId" value="">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Havalimanı Adı *</label>
                        <input type="text" name="name" id="airportName" class="form-control" placeholder="Örn: ANTALYA AİRPORT AYT" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">İl</label>
                            <input type="text" name="city" id="airportCity" class="form-control" placeholder="Örn: Antalya">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">İlçe</label>
                            <input type="text" name="district" id="airportDistrict" class="form-control" placeholder="Örn: Muratpaşa">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">IATA Kodu <small class="text-muted">(İsteğe bağlı)</small></label>
                        <input type="text" name="code" id="airportCode" class="form-control text-uppercase" placeholder="Örn: AYT" maxlength="10">
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_default" id="airportIsDefault" value="1">
                        <label class="form-check-label fw-semibold" for="airportIsDefault">
                            <i class="bi bi-star-fill text-warning me-1"></i>Varsayılan Havalimanı Olsun
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="bi bi-check-lg me-1"></i>Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const handlerUrl = '<?= ADMIN_URL ?>/api/handler.php';

function showToast(msg, ok) {
    const t = document.getElementById('ajaxToast');
    const b = document.getElementById('ajaxToastBody');
    t.className = 'toast align-items-center border-0 text-white ' + (ok ? 'bg-success' : 'bg-danger');
    b.textContent = msg;
    new bootstrap.Toast(t, {delay: 3000}).show();
}

function openAdd() {
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-airplane me-2"></i>Yeni Havalimanı Ekle';
    document.getElementById('formAction').value = 'create';
    document.getElementById('airportId').value = '';
    document.getElementById('airportForm').reset();
}

function openEdit(data) {
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Havalimanı Düzenle';
    document.getElementById('formAction').value = 'update';
    document.getElementById('airportId').value = data.id;
    document.getElementById('airportName').value = data.name || '';
    document.getElementById('airportCity').value = data.city || '';
    document.getElementById('airportDistrict').value = data.district || '';
    document.getElementById('airportCode').value = data.code || '';
    document.getElementById('airportIsDefault').checked = (data.is_default == 1);
    
    new bootstrap.Modal(document.getElementById('airportModal')).show();
}

function setDefault(id) {
    const fd = new FormData();
    fd.append('entity', 'airports');
    fd.append('action', 'set_default');
    fd.append('id', id);
    fd.append('csrf_token', '<?= generateCSRFToken() ?>');

    fetch(handlerUrl, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            if (d.success) setTimeout(() => location.reload(), 300);
        })
        .catch(() => showToast('Bir hata oluştu.', false));
}

function deleteAirport(id) {
    if (!confirm('Bu havalimanını silmek istediğinizden emin misiniz?')) return;
    
    const fd = new FormData();
    fd.append('entity', 'airports');
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('csrf_token', '<?= generateCSRFToken() ?>');

    fetch(handlerUrl, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            if (d.success) setTimeout(() => location.reload(), 300);
        })
        .catch(() => showToast('Bir hata oluştu.', false));
}

// Form Submit
document.getElementById('airportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor...';

    const fd = new FormData(this);
    fetch(handlerUrl, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('airportModal')).hide();
                setTimeout(() => location.reload(), 300);
            }
        })
        .catch(() => showToast('Bir hata oluştu.', false))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
        });
});

$(document).ready(function() {
    if ($.fn.DataTable && $('#airportsTable').length && !$.fn.DataTable.isDataTable('#airportsTable')) {
        $('#airportsTable').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' },
            pageLength: 25,
            autoWidth: false,
            responsive: true
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
