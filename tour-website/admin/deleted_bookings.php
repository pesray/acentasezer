<?php
/**
 * Silinen Rezervasyonlar (Trash Box)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();

// Silinmiş tüm rezervasyonları çek
$deletedBookings = $db->query("
    SELECT b.*,
           t.title AS tour_title,
           d.title AS destination_title,
           CONCAT(v.brand,' ',v.model) AS vehicle_name,
           COALESCE(v.capacity, 20) AS vehicle_capacity
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    LEFT JOIN destinations d ON b.destination_id = d.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.is_deleted = 1
    ORDER BY b.updated_at DESC
")->fetchAll();

$pageTitle = 'Silinen Rezervasyonlar';
$currentPage = 'deleted_bookings';
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
    <h1 class="h3 mb-0 text-danger"><i class="bi bi-trash me-2"></i>Silinen Rezervasyonlar</h1>
    <div>
        <button type="button" class="btn btn-outline-success btn-sm d-none" id="bulk-restore-btn" onclick="bulkRestore()">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Seçilenleri Geri Yükle
        </button>
    </div>
</div>

<!-- Tablo -->
<div class="card table-card border-danger-subtle">
    <div class="card-body">
        <?php if (empty($deletedBookings)): ?>
        <div class="text-center py-5">
            <i class="bi bi-trash-fill display-1 text-muted" style="opacity:0.3;"></i>
            <p class="text-muted mt-3">Çöp kutusu boş. Silinmiş rezervasyon bulunamadı.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table id="deletedBookingsTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="check-all">
                        </th>
                        <th width="160">Yön / Rez. Kodu</th>
                        <th>Müşteri Bilgileri</th>
                        <th>Güzergah / Tarih</th>
                        <th>Araç / Fiyat</th>
                        <th width="200">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deletedBookings as $b): 
                        $dirLabel = ($b['booking_direction'] === 'return') ? 'Depar' : 'Arrival';
                        $dirClass = ($b['booking_direction'] === 'return') ? 'info' : 'primary';
                        $dateStr  = $b['flight_date'] ? date('d.m.Y', strtotime($b['flight_date'])) : '-';
                        $timeStr  = $b['flight_time'] ? substr($b['flight_time'], 0, 5) : '-';
                        $priceStr = $b['total_price'] > 0 
                            ? (number_format((float)$b['total_price'], 0, ',', '.') . ' ' . $b['currency']) 
                            : '—';
                    ?>
                    <tr data-id="<?= $b['id'] ?>">
                        <td>
                            <input type="checkbox" class="form-check-input item-check" value="<?= $b['id'] ?>" onclick="toggleBulkButton()">
                        </td>
                        <td>
                            <span class="badge bg-<?= $dirClass ?> px-3 py-2 fs-7 fw-bold d-inline-block text-white" style="min-width:70px;text-align:center;">
                                <?= $dirLabel ?>
                            </span><br>
                            <small class="text-muted fw-semibold mt-1 d-inline-block"><?= e($b['booking_number']) ?></small>
                        </td>
                        <td>
                            <strong><?= e($b['customer_name']) ?></strong><br>
                            <small class="text-muted">
                                <?php if ($b['customer_phone']): ?>
                                    <i class="bi bi-telephone me-1"></i><?= e($b['customer_phone']) ?>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <span class="fw-bold text-dark"><?= e($b['destination_title'] ?: 'Belirtilmemiş') ?></span><br>
                            <span class="text-muted fs-7"><i class="bi bi-calendar-event me-1"></i><?= $dateStr ?> - <?= $timeStr ?></span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><i class="bi bi-car-front me-1"></i><?= e($b['vehicle_name']) ?></span><br>
                            <strong class="text-success mt-1 d-inline-block"><?= $priceStr ?></strong>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="openDetails(<?= $b['id'] ?>)" title="Detayları Gör">
                                    <i class="bi bi-eye me-1"></i> Detay
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="restoreBooking(<?= $b['id'] ?>, this)" title="Geri Yükle">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Geri Yükle
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

<!-- ===================== REZERVASYON DETAY MODALı ===================== -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Rezervasyon Detayı</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Üst Özet Kartı -->
                <div class="rounded p-3 mb-4" style="background:#f0f7ff;border:1px solid #b8d4f0;">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small" style="font-size: .68rem;">REZERVASYON KODU</span>
                            <strong class="fs-6 text-dark" id="detail-booking-number">-</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small" style="font-size: .68rem;">YÖN</span>
                            <span class="badge" id="detail-direction">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small" style="font-size: .68rem;">OLUŞTURULMA TARİHİ</span>
                            <span class="text-dark fw-bold" id="detail-created-at">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small" style="font-size: .68rem;">SİLİNME TARİHİ</span>
                            <span class="text-danger fw-bold" id="detail-deleted-at">-</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Sol Kolon: Transfer Bilgileri -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-airplane-engines me-2"></i>Transfer Detayları</h6>
                        <table class="table table-sm table-borderless mb-0" style="font-size: .85rem;">
                            <tr>
                                <th width="120" class="text-muted fw-normal">Güzergah:</th>
                                <td id="detail-route" class="fw-bold text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Transfer Tarihi:</th>
                                <td id="detail-date" class="text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Uçuş Saati:</th>
                                <td id="detail-time" class="text-dark"></td>
                            </tr>
                            <tr id="detail-pickup-row">
                                <th class="text-muted text-primary fw-normal">Alınış Saati:</th>
                                <td id="detail-pickup" class="text-primary fw-bold"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Uçuş Numarası:</th>
                                <td id="detail-flight-number" class="text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Otel / Adres:</th>
                                <td id="detail-hotel" class="text-dark"></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Sağ Kolon: Müşteri & Ücret Bilgileri -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-person me-2"></i>Müşteri & Ücret</h6>
                        <table class="table table-sm table-borderless mb-0" style="font-size: .85rem;">
                            <tr>
                                <th width="120" class="text-muted fw-normal">Ad Soyad:</th>
                                <td id="detail-customer-name" class="fw-bold text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Telefon:</th>
                                <td id="detail-customer-phone" class="text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">E-posta:</th>
                                <td id="detail-customer-email" class="text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Araç Tipi:</th>
                                <td id="detail-vehicle" class="text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Kişi Sayısı:</th>
                                <td id="detail-pax" class="text-dark"></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-success fw-normal">Toplam Tutar:</th>
                                <td id="detail-price" class="text-success fw-bold fs-6"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Alt Satır: Yolcular & Notlar -->
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary mb-2 border-bottom pb-2"><i class="bi bi-people me-2"></i>Yolcu İsimleri</h6>
                        <div id="detail-passengers" class="p-2 bg-light rounded text-dark fs-7" style="min-height:80px; max-height: 120px; overflow-y: auto;">
                            <span class="text-muted">Yolcu bilgisi yükleniyor...</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-primary mb-2 border-bottom pb-2"><i class="bi bi-chat-left-text me-2"></i>Rezervasyon Notu</h6>
                        <div id="detail-notes" class="p-2 bg-light rounded text-muted fs-7" style="min-height:80px; max-height: 120px; overflow-y: auto; white-space: pre-line;">
                            -
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-success btn-sm" id="detail-modal-restore-btn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Rezervasyonu Geri Yükle
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extraJs = '
<script>
const apiUrl = window.ADMIN_URL + "/api/handler.php?entity=bookings";
const csrfToken = document.querySelector(\'meta[name="csrf-token"]\').content;
const deletedBookings = ' . json_encode($deletedBookings ?: []) . ';
const detailModal = new bootstrap.Modal(document.getElementById("detailModal"));
let currentDetailId = null;

function showToast(message, success) {
    const toastEl = document.getElementById("ajaxToast");
    const toastBody = document.getElementById("ajaxToastBody");
    toastEl.className = "toast align-items-center border-0 text-white " + (success ? "bg-success" : "bg-danger");
    toastBody.textContent = message;
    const toast = new bootstrap.Toast(toastEl, {delay: 3500});
    toast.show();
}

// Checkbox Tümü Seçimi
const checkAll = document.getElementById("check-all");
if (checkAll) {
    checkAll.addEventListener("change", function() {
        document.querySelectorAll(".item-check").forEach(chk => {
            chk.checked = this.checked;
        });
        toggleBulkButton();
    });
}

function toggleBulkButton() {
    const selected = document.querySelectorAll(".item-check:checked").length;
    const btn = document.getElementById("bulk-restore-btn");
    if (selected > 0) {
        btn.classList.remove("d-none");
    } else {
        btn.classList.add("d-none");
    }
}

// Detay Modalını Aç
function openDetails(id) {
    const b = deletedBookings.find(x => x.id == id);
    if (!b) return;

    currentDetailId = id;
    
    // Değerleri doldur
    document.getElementById("detail-booking-number").textContent = b.booking_number;
    
    const dirEl = document.getElementById("detail-direction");
    const isReturn = b.booking_direction === "return";
    dirEl.textContent = isReturn ? "Depar" : "Arrival";
    dirEl.className = "badge bg-" + (isReturn ? "info" : "primary") + " px-2 py-1";

    function fmtDateTime(str) {
        if (!str) return "-";
        const dt = new Date(str);
        return dt.toLocaleDateString("tr-TR") + " " + dt.toLocaleTimeString("tr-TR", {hour: "2-digit", minute:"2-digit"});
    }
    
    document.getElementById("detail-created-at").textContent = fmtDateTime(b.created_at);
    document.getElementById("detail-deleted-at").textContent = fmtDateTime(b.updated_at); // silinince güncelleniyor
    
    // Detay tablosu
    document.getElementById("detail-route").textContent = b.destination_title || "Belirtilmemiş";
    document.getElementById("detail-date").textContent = b.flight_date ? new Date(b.flight_date).toLocaleDateString("tr-TR") : "-";
    document.getElementById("detail-time").textContent = b.flight_time ? b.flight_time.substring(0, 5) : "-";
    
    const pickupRow = document.getElementById("detail-pickup-row");
    if (isReturn && b.pickup_time) {
        pickupRow.style.removeProperty("display");
        document.getElementById("detail-pickup").textContent = b.pickup_time.substring(0, 5);
    } else {
        pickupRow.style.display = "none";
    }

    document.getElementById("detail-flight-number").textContent = b.flight_number || "-";
    document.getElementById("detail-hotel").textContent = b.hotel_address || "-";
    
    // Müşteri & Ücret
    document.getElementById("detail-customer-name").textContent = b.customer_name;
    document.getElementById("detail-customer-phone").textContent = b.customer_phone || "-";
    document.getElementById("detail-customer-email").textContent = b.customer_email || "-";
    document.getElementById("detail-vehicle").textContent = b.vehicle_name || "-";
    
    let pax = (b.adults || 1) + " Yetişkin";
    if (b.children > 0) pax += " + " + b.children + " Çocuk";
    let paxEx = [];
    if (b.child_seat > 0) paxEx.push(b.child_seat + " Çocuk Koltuğu");
    if (parseInt(b.booster) > 0) paxEx.push(parseInt(b.booster) + " Yükseltici");
    if (paxEx.length) pax += " (" + paxEx.join(", ") + ")";
    document.getElementById("detail-pax").textContent = pax;
    
    const priceStr = b.total_price > 0 ? (parseFloat(b.total_price).toLocaleString("tr-TR", {minimumFractionDigits: 0}) + " " + b.currency) : "—";
    document.getElementById("detail-price").textContent = priceStr;
    
    document.getElementById("detail-notes").textContent = b.notes ? b.notes.trim() : "-";
    
    // Yolcuları AJAX ile çek
    const passEl = document.getElementById("detail-passengers");
    passEl.innerHTML = "<div class=\"text-center py-2\"><span class=\"spinner-border spinner-border-sm text-primary\"></span></div>";
    
    const fd = new FormData();
    fd.append("action", "get_passengers");
    fd.append("id", id);
    fd.append("csrf_token", csrfToken);

    fetch(apiUrl, { method: "POST", body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data && res.data.passengers && res.data.passengers.length > 0) {
                passEl.innerHTML = "";
                const ul = document.createElement("ul");
                ul.className = "mb-0 ps-3";
                res.data.passengers.forEach(p => {
                    const li = document.createElement("li");
                    const typeLabel = p.passenger_type === "child" ? " (Çocuk)" : "";
                    li.innerHTML = `<strong>${p.full_name}</strong>${typeLabel}`;
                    ul.appendChild(li);
                });
                passEl.appendChild(ul);
            } else {
                passEl.innerHTML = "<span class=\"text-muted\">Yolcu bilgisi yok.</span>";
            }
        })
        .catch(() => {
            passEl.innerHTML = "<span class=\"text-danger\">Yolcu bilgisi yüklenemedi.</span>";
        });

    detailModal.show();
}

// Modal içindeki geri yükleme butonu
document.getElementById("detail-modal-restore-btn").addEventListener("click", function() {
    if (!currentDetailId) return;
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = "<span class=\"spinner-border spinner-border-sm me-1\"></span>Geri Yükleniyor...";
    
    const fd = new FormData();
    fd.append("action", "restore");
    fd.append("id", currentDetailId);
    fd.append("csrf_token", csrfToken);

    fetch(apiUrl, { method: "POST", body: fd })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            btn.disabled = false;
            btn.innerHTML = "<i class=\"bi bi-arrow-counterclockwise me-1\"></i>Rezervasyonu Geri Yükle";
            if (d.success) {
                detailModal.hide();
                // Tablodaki satırı kaldır
                const tr = document.querySelector("tr[data-id=\"" + currentDetailId + "\"]");
                if (tr) {
                    tr.style.transition = "all 0.4s ease";
                    tr.style.opacity = 0;
                    setTimeout(() => {
                        tr.remove();
                        if (document.querySelectorAll(".item-check").length === 0) {
                            window.location.reload();
                        }
                    }, 400);
                }
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = "<i class=\"bi bi-arrow-counterclockwise me-1\"></i>Rezervasyonu Geri Yükle";
            showToast("Bağlantı hatası oluştu.", false);
        });
});

// Tekli Geri Yükleme
function restoreBooking(id, btn) {
    if (!confirm("Bu rezervasyonu geri yüklemek istiyor musunuz?")) return;

    btn.disabled = true;
    btn.innerHTML = "<span class=\"spinner-border spinner-border-sm\"></span>";

    const fd = new FormData();
    fd.append("action", "restore");
    fd.append("id", id);
    fd.append("csrf_token", csrfToken);

    fetch(apiUrl, { method: "POST", body: fd })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            if (d.success) {
                const tr = btn.closest("tr");
                tr.style.transition = "all 0.4s ease";
                tr.style.opacity = 0;
                setTimeout(() => {
                    tr.remove();
                    if (document.querySelectorAll(".item-check").length === 0) {
                        window.location.reload();
                    }
                }, 400);
            } else {
                btn.disabled = false;
                btn.innerHTML = "<i class=\"bi bi-arrow-counterclockwise me-1\"></i> Geri Yükle";
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = "<i class=\"bi bi-arrow-counterclockwise me-1\"></i> Geri Yükle";
            showToast("Bağlantı hatası oluştu.", false);
        });
}

// Çoklu Geri Yükleme
function bulkRestore() {
    const checkedBoxes = document.querySelectorAll(".item-check:checked");
    if (checkedBoxes.length === 0) return;

    if (!confirm("Seçilen " + checkedBoxes.length + " rezervasyonu geri yüklemek istiyor musunuz?")) return;

    const btn = document.getElementById("bulk-restore-btn");
    btn.disabled = true;
    btn.innerHTML = "<span class=\"spinner-border spinner-border-sm me-1\"></span>Geri Yükleniyor...";

    const fd = new FormData();
    fd.append("action", "restore");
    fd.append("csrf_token", csrfToken);
    checkedBoxes.forEach(chk => {
        fd.append("ids[]", chk.value);
    });

    fetch(apiUrl, { method: "POST", body: fd })
        .then(r => r.json())
        .then(d => {
            showToast(d.message, d.success);
            if (d.success) {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                btn.disabled = false;
                btn.innerHTML = "<i class=\"bi bi-arrow-counterclockwise me-1\"></i> Seçilenleri Geri Yükle";
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = "<i class=\"bi bi-arrow-counterclockwise me-1\"></i> Seçilenleri Geri Yükle";
            showToast("Bağlantı hatası oluştu.", false);
        });
}
</script>
';

require_once __DIR__ . '/includes/footer.php';
?>
