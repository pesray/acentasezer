<?php
/**
 * Müşteri Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

$customers = $db->query("SELECT * FROM customers ORDER BY name ASC")->fetchAll();

$pageTitle = 'Müşteriler';
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
    <h1 class="h3 mb-0"><i class="bi bi-person-lines-fill me-2"></i>Müşteriler</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="openAdd()">
        <i class="bi bi-plus-lg me-1"></i>Yeni Müşteri Ekle
    </button>
</div>

<!-- Tablo -->
<div class="card table-card">
    <div class="card-body">
        <?php if (empty($customers)): ?>
        <div class="text-center py-5">
            <i class="bi bi-person-lines-fill display-1 text-muted"></i>
            <p class="text-muted mt-3">Henüz müşteri eklenmemiş.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="openAdd()">
                <i class="bi bi-plus-lg me-1"></i>İlk Müşteriyi Ekle
            </button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table id="customersTable" class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th width="180">Telefon</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <strong><?= e($c['name']) ?></strong>
                        </td>
                        <td>
                            <?php if ($c['email']): ?>
                            <a href="mailto:<?= e($c['email']) ?>" class="text-decoration-none">
                                <i class="bi bi-envelope me-1 text-muted"></i><?= e($c['email']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c['phone']): 
                                $phoneClean = preg_replace('/[^0-9]/', '', $c['phone']);
                                if (strlen($phoneClean) === 10 && strpos($phoneClean, '5') === 0) {
                                    $phoneClean = '90' . $phoneClean;
                                } elseif (strlen($phoneClean) === 11 && strpos($phoneClean, '05') === 0) {
                                    $phoneClean = '90' . substr($phoneClean, 1);
                                }
                            ?>
                            <a href="https://wa.me/<?= $phoneClean ?>" target="_blank" class="text-decoration-none text-success" title="WhatsApp ile Mesaj Gönder">
                                <i class="bi bi-whatsapp me-1"></i><?= e($c['phone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)"
                                        title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="deleteCustomer(<?= $c['id'] ?>)"
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
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#4f46e5,#3730a3);color:#fff;">
                <h5 class="modal-title" id="customerModalTitle"><i class="bi bi-person-plus me-2"></i>Müşteri Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:brightness(0) invert(1);"></button>
            </div>
            <form id="customerForm">
                <input type="hidden" name="id" id="customer-id">
                <input type="hidden" name="action" id="customer-action" value="create">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ad Soyad <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="customer-name" class="form-control" required placeholder="Ad Soyad">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">E-posta</label>
                        <input type="email" name="email" id="customer-email" class="form-control" placeholder="E-posta adresi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Telefon</label>
                        <input type="text" name="phone" id="customer-phone" class="form-control" placeholder="+90 5XX XXX XX XX">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="customer-save-btn">
                        <i class="bi bi-check-lg me-1"></i>Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const apiUrl    = window.ADMIN_URL + '/api/handler.php?entity=customers';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, ok) {
    const t = document.getElementById('ajaxToast');
    t.className = 'toast align-items-center border-0 text-white ' + (ok ? 'bg-success' : 'bg-danger');
    document.getElementById('ajaxToastBody').textContent = msg;
    new bootstrap.Toast(t, {delay: 3500}).show();
}

function openAdd() {
    document.getElementById('customerModalTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Yeni Müşteri Ekle';
    document.getElementById('customer-action').value = 'create';
    document.getElementById('customer-id').value     = '';
    document.getElementById('customer-name').value   = '';
    document.getElementById('customer-email').value  = '';
    document.getElementById('customer-phone').value  = '';
}

function openEdit(c) {
    document.getElementById('customerModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Müşteri Düzenle';
    document.getElementById('customer-action').value = 'update';
    document.getElementById('customer-id').value     = c.id;
    document.getElementById('customer-name').value   = c.name   || '';
    document.getElementById('customer-email').value  = c.email  || '';
    document.getElementById('customer-phone').value  = c.phone  || '';
    new bootstrap.Modal(document.getElementById('customerModal')).show();
}

document.getElementById('customerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd  = new FormData(this);
    fd.append('csrf_token', csrfToken);
    var btn = document.getElementById('customer-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor...';
    fetch(apiUrl, {method:'POST', body:fd})
        .then(function(r) { return r.json(); })
        .then(function(d) {
            showToast(d.message, d.success);
            if (d.success) {
                bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
                setTimeout(function() { location.reload(); }, 400);
            }
        })
        .catch(function() { showToast('Bir hata oluştu.', false); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
        });
});

function deleteCustomer(id) {
    if (!confirm('Bu müşteriyi silmek istediğinizden emin misiniz?\nMüşteri silindiğinde geçmiş rezervasyonlarındaki müşteri kaydı ilişkisi silinecektir fakat rezervasyonlar silinmeyecektir.')) return;
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
