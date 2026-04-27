<?php
/**
 * Dışarıya Verilen Kişi / Firma Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$partners = $db->query("SELECT * FROM outsource_partners ORDER BY name ASC")->fetchAll();

$pageTitle = 'Dış Partnerler';
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
    <h1 class="h3 mb-0"><i class="bi bi-people me-2"></i>Dış Partnerler</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#partnerModal" onclick="openAdd()">
        <i class="bi bi-plus-lg me-1"></i>Yeni Partner Ekle
    </button>
</div>

<!-- Tablo -->
<div class="card table-card">
    <div class="card-body">
        <?php if (empty($partners)): ?>
        <div class="text-center py-5">
            <i class="bi bi-people display-1 text-muted"></i>
            <p class="text-muted mt-3">Henüz partner eklenmemiş.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#partnerModal" onclick="openAdd()">
                <i class="bi bi-plus-lg me-1"></i>İlk Partneri Ekle
            </button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table id="partnersTable" class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Ad Soyad / Firma</th>
                        <th width="180">Telefon</th>
                        <th>Notlar</th>
                        <th width="90">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partners as $p): ?>
                    <tr>
                        <td>
                            <strong><?= e($p['name']) ?></strong>
                            <?php if (!$p['is_active']): ?>
                            <span class="badge bg-secondary ms-1">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['phone']): ?>
                            <a href="tel:<?= e($p['phone']) ?>" class="text-decoration-none">
                                <i class="bi bi-telephone me-1 text-muted"></i><?= e($p['phone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?= e($p['notes'] ?? '-') ?></td>
                        <td>
                            <?php if ($p['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)"
                                        title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-<?= $p['is_active'] ? 'warning' : 'success' ?>"
                                        onclick="toggleActive(<?= $p['id'] ?>)"
                                        title="<?= $p['is_active'] ? 'Pasife Al' : 'Aktife Al' ?>">
                                    <i class="bi bi-<?= $p['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="deletePartner(<?= $p['id'] ?>)"
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

<!-- Modal -->
<div class="modal fade" id="partnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#f6c23e,#d4a017);color:#fff;">
                <h5 class="modal-title" id="partnerModalTitle"><i class="bi bi-person-plus me-2"></i>Partner Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0) invert(1);"></button>
            </div>
            <form id="partnerForm">
                <input type="hidden" name="id" id="partner-id">
                <input type="hidden" name="action" id="partner-action" value="create">

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Ad Soyad / Firma <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="partner-name" class="form-control" required placeholder="Ahmet Yılmaz / ABC Turizm">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Telefon</label>
                            <input type="text" name="phone" id="partner-phone" class="form-control" placeholder="+90 5XX XXX XX XX">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notlar <small class="text-muted fw-normal">(isteğe bağlı)</small></label>
                        <textarea name="notes" id="partner-notes" class="form-control" rows="2" placeholder="Araç tipi, çalışma bölgesi vb..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning fw-bold" id="partner-save-btn">
                        <i class="bi bi-check-lg me-1"></i>Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const apiUrl    = window.ADMIN_URL + '/api/handler.php?entity=outsource_partners';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, ok) {
    const t = document.getElementById('ajaxToast');
    t.className = 'toast align-items-center border-0 text-white ' + (ok ? 'bg-success' : 'bg-danger');
    document.getElementById('ajaxToastBody').textContent = msg;
    new bootstrap.Toast(t, {delay: 3500}).show();
}

function openAdd() {
    document.getElementById('partnerModalTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Yeni Partner Ekle';
    document.getElementById('partner-action').value = 'create';
    document.getElementById('partner-id').value     = '';
    document.getElementById('partner-name').value   = '';
    document.getElementById('partner-phone').value  = '';
    document.getElementById('partner-notes').value  = '';
}

function openEdit(p) {
    document.getElementById('partnerModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Partner Düzenle';
    document.getElementById('partner-action').value = 'update';
    document.getElementById('partner-id').value     = p.id;
    document.getElementById('partner-name').value   = p.name   || '';
    document.getElementById('partner-phone').value  = p.phone  || '';
    document.getElementById('partner-notes').value  = p.notes  || '';
    new bootstrap.Modal(document.getElementById('partnerModal')).show();
}

document.getElementById('partnerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd  = new FormData(this);
    fd.append('csrf_token', csrfToken);
    var btn = document.getElementById('partner-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor...';
    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast(d.message, d.success);
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('partnerModal')).hide();
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
        .then(function(d) {
            showToast(d.message, d.success);
            if (d.success) setTimeout(function() { location.reload(); }, 300);
        })
        .catch(function() { showToast('Bir hata oluştu.', false); });
}

function deletePartner(id) {
    if (!confirm('Bu partneri silmek istediğinizden emin misiniz?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('csrf_token', csrfToken);
    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast(d.message, d.success);
            if (d.success) setTimeout(function() { location.reload(); }, 300);
        })
        .catch(function() { showToast('Bir hata oluştu.', false); });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
