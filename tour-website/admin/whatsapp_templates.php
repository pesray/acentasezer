<?php
/**
 * WhatsApp Şablonları Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

// Şablonları çek
$templates = $db->query("SELECT * FROM whatsapp_templates ORDER BY title ASC")->fetchAll();

$pageTitle = 'WhatsApp Şablonları';
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
    <h1 class="h3 mb-0"><i class="bi bi-whatsapp me-2 text-success"></i>WhatsApp Şablonları</h1>
</div>

<!-- Tablo / Liste -->
<div class="card table-card">
    <div class="card-body">
        <?php if (empty($templates)): ?>
        <div class="text-center py-5">
            <i class="bi bi-whatsapp display-1 text-muted"></i>
            <p class="text-muted mt-3">Herhangi bir şablon bulunamadı.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="80">Şablon</th>
                        <th>Şablon Adı (Key)</th>
                        <th>İçerik Önizleme</th>
                        <th width="120">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $t): ?>
                    <tr>
                        <td>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-success" style="width:60px;height:40px;">
                                <i class="bi bi-chat-text fs-5"></i>
                            </div>
                        </td>
                        <td>
                            <strong><?= e($t['title']) ?></strong><br>
                            <small class="text-muted"><?= e($t['template_key']) ?></small>
                        </td>
                        <td>
                            <div class="text-muted text-truncate" style="max-width: 500px; white-space: pre-line;">
                                <?= e(mb_substr($t['content'], 0, 150)) ?><?= mb_strlen($t['content']) > 150 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)" title="Düzenle">
                                <i class="bi bi-pencil me-1"></i> Düzenle
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================== ŞABLON DÜZENLEME MODALı ===================== -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-whatsapp me-2"></i>Şablon Düzenle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="templateForm" method="POST">
                <input type="hidden" name="id" id="modal-template-id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Sol Kolon: Textarea Edit -->
                        <div class="col-lg-7 mb-3">
                            <label class="form-label fw-bold" id="modal-template-title">Şablon İçeriği</label>
                            <textarea name="content" id="modal-template-content" class="form-control" rows="15" style="font-family: monospace; font-size: 14px;" required></textarea>
                            <div class="form-text text-muted mt-2">
                                <i class="bi bi-info-circle me-1"></i> WhatsApp kalın yazım kuralları geçerlidir (Örn: *kalın*).
                            </div>
                        </div>
                        
                        <!-- Sağ Kolon: Parametreler Listesi (Kullanıcı Bilgilendirme) -->
                        <div class="col-lg-5 mb-3">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3 text-success"><i class="bi bi-info-square me-2"></i>Kullanılabilir Parametreler</h6>
                                    <p class="small text-muted mb-3">Aşağıdaki etiketleri şablon içinde kopyalayıp yapıştırarak dinamik rezervasyon bilgilerini mesaj içeriğine çekebilirsiniz:</p>
                                    
                                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered bg-white text-dark small mb-0">
                                            <thead>
                                                <tr class="table-light">
                                                    <th>Parametre</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>{ROUTE}</code></td>
                                                    <td>Transfer güzergahı (Örn: Havalimanı → Otel)</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{FLIGHT_DATE}</code></td>
                                                    <td>Uçuş / Transfer Tarihi</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{FLIGHT_TIME}</code></td>
                                                    <td>Uçuş saati (Arrival için iniş saati)</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{PICKUP_DATE}</code></td>
                                                    <td>Alınış Tarihi</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{PICKUP_TIME}</code></td>
                                                    <td>Otelden alınış saati (Depar için geçerli)</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{FLIGHT_NO}</code></td>
                                                    <td>Uçuş Numarası / Kodu</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{HOTEL}</code></td>
                                                    <td>Otel veya alınış adresi</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td><code>{PICKUP_HOTEL}</code></td>
                                                    <td>Alınış Oteli (Ara transfer şablonlarında)</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td><code>{DESTINATION_HOTEL}</code></td>
                                                    <td>Varış Oteli (Ara transfer şablonlarında)</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{PAX}</code></td>
                                                    <td>Kişi sayısı açıklaması (Yetişkin + Çocuk)</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{PASSENGERS}</code></td>
                                                    <td>Tüm yolcuların ad/soyad listesi (Alt alta)</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{PRICE}</code></td>
                                                    <td>Müşteriden tahsil edilecek toplam tutar</td>
                                                </tr>
                                                <tr>
                                                    <td><code>{NOTES}</code></td>
                                                    <td>Müşterinin ilettiği özel not</td>
                                                </tr>
                                                <tr class="table-info">
                                                    <td><code>{PARTNER_NAME}</code></td>
                                                    <td>Dış Partner adı (Sadece dış partner şablonunda)</td>
                                                </tr>
                                                <tr class="table-info">
                                                    <td><code>{PARTNER_HAKEDIS}</code></td>
                                                    <td>Dış Partner Hakediş tutarı (Sadece dış partner şablonunda)</td>
                                                </tr>
                                                <tr class="table-warning">
                                                    <td><code>{DRIVER_NAME}</code></td>
                                                    <td>Şöför adı (Sadece şöför şablonunda)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success" id="btn-save-template">
                        <i class="bi bi-save me-1"></i> Şablonu Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = '
<script>
const csrfToken = document.querySelector(\'meta[name="csrf-token"]\').content;
var templateModal = new bootstrap.Modal(document.getElementById("templateModal"));

function showToast(message, success) {
    var toastEl = document.getElementById("ajaxToast");
    var toastBody = document.getElementById("ajaxToastBody");
    toastEl.classList.remove("bg-success", "bg-danger", "text-white");
    if (success) {
        toastEl.classList.add("bg-success", "text-white");
    } else {
        toastEl.classList.add("bg-danger", "text-white");
    }
    toastBody.textContent = message;
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
}

function openEdit(template) {
    document.getElementById("modal-template-id").value = template.id;
    document.getElementById("modal-template-title").textContent = template.title + " İçeriği";
    document.getElementById("modal-template-content").value = template.content;
    templateModal.show();
}

document.getElementById("templateForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    var btn = document.getElementById("btn-save-template");
    btn.disabled = true;
    btn.innerHTML = "<span class=\"spinner-border spinner-border-sm me-1\"></span> Kaydediliyor...";
    
    var fd = new FormData(this);
    fd.append("action", "update");
    fd.append("entity", "whatsapp_templates");
    fd.append("csrf_token", csrfToken);
    
    fetch(window.ADMIN_URL + "/api/handler.php", {
        method: "POST",
        body: fd
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerHTML = "<i class=\"bi bi-save me-1\"></i> Şablonu Kaydet";
        
        if (d.success) {
            showToast(d.message, true);
            templateModal.hide();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(d.message, false);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = "<i class=\"bi bi-save me-1\"></i> Şablonu Kaydet";
        showToast("Bir hata oluştu.", false);
    });
});
</script>
';
require_once __DIR__ . '/includes/footer.php';
?>
