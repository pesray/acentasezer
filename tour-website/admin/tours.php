<?php
/**
 * Tur Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Aktif dilleri al
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

// Tablolar migration ile oluşturuldu - tour_vehicles, page_settings

// Araçları al
$vehicles = $db->query("SELECT * FROM vehicles WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

// Sayfa ayarları işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_page_settings'])) {
    $bgImage = $_POST['background_image'] ?? '';
    $featuresVisible = isset($_POST['features_visible']) ? 1 : 0;
    
    // Sayfa ayarlarını güncelle
    $pageSettingId = $db->query("SELECT id FROM page_settings WHERE page_key = 'tours'")->fetchColumn();
    if ($pageSettingId) {
        $stmt = $db->prepare("UPDATE page_settings SET background_image = ?, features_visible = ? WHERE id = ?");
        $stmt->execute([$bgImage, $featuresVisible, $pageSettingId]);
        
        // Çevirileri kaydet
        foreach ($_POST['page_translations'] ?? [] as $langCode => $trans) {
            $stmt = $db->prepare("
                INSERT INTO page_setting_translations (page_setting_id, language_code, title, slug, subtitle)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), subtitle = VALUES(subtitle)
            ");
            $stmt->execute([$pageSettingId, $langCode, $trans['title'] ?? '', $trans['slug'] ?? '', $trans['subtitle'] ?? '']);
        }
        
        setFlashMessage('success', 'Sayfa ayarları kaydedildi.');
    }
    header('Location: ' . ADMIN_URL . '/tours.php');
    exit;
}

// Form işlemleri (Tur ekleme/düzenleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_page_settings'])) {
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $image = $_POST['image'] ?? '';
    
    // Çeviriler
    $translations = $_POST['translations'] ?? [];
    
    // Varsayılan dil kontrolü
    $defaultTitle = $translations[$defaultLang['code']]['title'] ?? '';
    $defaultSlug = $translations[$defaultLang['code']]['slug'] ?? '';
    
    if (empty($defaultTitle) || empty($defaultSlug)) {
        setFlashMessage('error', 'Varsayılan dil için başlık ve slug gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            if ($id) {
                // Güncelle
                $stmt = $db->prepare("
                    UPDATE tours SET 
                        title = ?, slug = ?, description = ?, content = ?,
                        image = ?, is_featured = ?, status = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug, 
                    $translations[$defaultLang['code']]['description'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $image, $isFeatured, $status, $sortOrder, $id
                ]);
                $tourId = $id;
            } else {
                // Ekle
                $stmt = $db->prepare("
                    INSERT INTO tours (title, slug, description, content, image, is_featured, status, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['description'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $image, $isFeatured, $status, $sortOrder
                ]);
                $tourId = $db->lastInsertId();
            }
            
            // Çevirileri kaydet
            foreach ($translations as $langCode => $trans) {
                if ($langCode === $defaultLang['code']) continue;
                if (empty($trans['title'])) continue;
                
                $stmt = $db->prepare("
                    INSERT INTO tour_translations (tour_id, language_code, title, slug, description, content, meta_title, meta_description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        title = VALUES(title), slug = VALUES(slug), description = VALUES(description),
                        content = VALUES(content), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description)
                ");
                $stmt->execute([
                    $tourId, $langCode, $trans['title'], $trans['slug'] ?? '',
                    $trans['description'] ?? '', $trans['content'] ?? '',
                    $trans['meta_title'] ?? '', $trans['meta_description'] ?? ''
                ]);
            }
            
            // Araçları kaydet (fiyat olmadan, sadece seçim)
            // Önce mevcut araçları sil
            $db->prepare("DELETE FROM tour_vehicles WHERE tour_id = ?")->execute([$tourId]);
            
            $selectedVehicles = $_POST['selected_vehicles'] ?? [];
            
            // Format: selected_vehicles[lang_code][] = vehicle_id
            foreach ($selectedVehicles as $langCode => $vehicleIds) {
                foreach ($vehicleIds as $vehicleId) {
                    $vehicleId = (int)$vehicleId;
                    if ($vehicleId > 0) {
                        $stmt = $db->prepare("
                            INSERT INTO tour_vehicles (tour_id, vehicle_id, language_code, price, currency)
                            VALUES (?, ?, ?, 0, 'EUR')
                        ");
                        $stmt->execute([$tourId, $vehicleId, $langCode]);
                    }
                }
            }
            
            $db->commit();
            setFlashMessage('success', $id ? 'Tur güncellendi.' : 'Tur eklendi.');
            header('Location: ' . ADMIN_URL . '/tours.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

// Silme işlemi
if ($action === 'delete' && $id) {
    try {
        $db->prepare("DELETE FROM tours WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Tur silindi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/tours.php');
    exit;
}

// Düzenleme için veri al
$editData = null;
$editTranslations = [];
$editVehiclePrices = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    // Çevirileri al
    $stmt = $db->prepare("SELECT * FROM tour_translations WHERE tour_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $editTranslations[$row['language_code']] = $row;
    }
    
    // Araç fiyatlarını al - Format: [vehicle_id][lang_code] = ['price' => x, 'currency' => y]
    $stmt = $db->prepare("SELECT * FROM tour_vehicles WHERE tour_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $editVehiclePrices[$row['vehicle_id']][$row['language_code']] = [
            'price' => $row['price'],
            'currency' => $row['currency']
        ];
    }
}

// Sayfa ayarlarını al
$pageSettings = $db->query("SELECT * FROM page_settings WHERE page_key = 'tours'")->fetch();
$pageSettingTranslations = [];
if ($pageSettings) {
    $stmt = $db->prepare("SELECT * FROM page_setting_translations WHERE page_setting_id = ?");
    $stmt->execute([$pageSettings['id']]);
    while ($row = $stmt->fetch()) {
        $pageSettingTranslations[$row['language_code']] = $row;
    }
}

// Tüm turları al
$tours = $db->query("
    SELECT t.*
    FROM tours t
    ORDER BY t.sort_order, t.created_at DESC
")->fetchAll();

$pageTitle = 'Tur Yönetimi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Tur Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <div>
        <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#pageSettingsModal">
            <i class="bi bi-gear me-1"></i> Sayfa Ayarları
        </button>
        <a href="?action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Yeni Tur Ekle
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- Tur Formu - destinations.php ile birebir aynı yapı -->
<form method="post" action="?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <!-- Dil Tabları -->
            <div class="card mb-4">
                <div class="card-header bg-white py-2">
                    <ul class="nav nav-tabs lang-tabs card-header-tabs" role="tablist">
                        <?php foreach ($languages as $i => $lang): ?>
                        <li class="nav-item">
                            <button type="button" class="nav-link <?= $i === 0 ? 'active' : '' ?>" 
                                    data-bs-toggle="tab" data-bs-target="#lang-<?= $lang['code'] ?>">
                                <?= e($lang['flag']) ?> <?= e($lang['native_name']) ?>
                                <?php if ($lang['is_default']): ?><span class="text-danger">*</span><?php endif; ?>
                            </button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <?php foreach ($languages as $i => $lang): 
                            $trans = $editTranslations[$lang['code']] ?? [];
                            $isDefault = $lang['is_default'];
                            if ($isDefault && $editData) {
                                $trans = [
                                    'title' => $editData['title'],
                                    'slug' => $editData['slug'],
                                    'description' => $editData['description'],
                                    'meta_title' => $editData['meta_title'] ?? '',
                                    'meta_description' => $editData['meta_description'] ?? ''
                                ];
                            }
                        ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="lang-<?= $lang['code'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Başlık <?php if ($isDefault): ?><span class="text-danger">*</span><?php endif; ?></label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][title]" 
                                       class="form-control title-input" data-lang="<?= $lang['code'] ?>"
                                       value="<?= e($trans['title'] ?? '') ?>" <?= $isDefault ? 'required' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug <?php if ($isDefault): ?><span class="text-danger">*</span><?php endif; ?></label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][slug]" 
                                       class="form-control slug-input" id="slug-<?= $lang['code'] ?>"
                                       value="<?= e($trans['slug'] ?? '') ?>" <?= $isDefault ? 'required' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kısa Açıklama</label>
                                <textarea name="translations[<?= $lang['code'] ?>][description]" class="form-control" rows="3"><?= e($trans['description'] ?? '') ?></textarea>
                            </div>
                            
                            <?php 
                            // Para birimleri listesi
                            $currencies = [
                                'EUR' => '€ EUR (Euro)',
                                'USD' => '$ USD (Amerikan Doları)',
                                'TRY' => '₺ TRY (Türk Lirası)',
                                'GBP' => '£ GBP (İngiliz Sterlini)',
                            ];
                            // Bu dil için kayıtlı para birimini al
                            $savedCurrency = 'EUR';
                            foreach ($editVehiclePrices as $vid => $langPrices) {
                                if (isset($langPrices[$lang['code']]['currency'])) {
                                    $savedCurrency = $langPrices[$lang['code']]['currency'];
                                    break;
                                }
                            }
                            ?>
                            <div class="card bg-light mb-3">
                                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-car-front me-2"></i>Araç Seçimi</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($vehicles)): ?>
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-info-circle me-1"></i> Henüz araç eklenmemiş. 
                                        <a href="<?= ADMIN_URL ?>/vehicles.php?action=add">Araç ekle</a>
                                    </div>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="30"></th>
                                                    <th>Araç</th>
                                                    <th>Kapasite</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($vehicles as $vehicle): 
                                                    $isSelected = isset($editVehiclePrices[$vehicle['id']][$lang['code']]);
                                                    $vehiclePrice = $editVehiclePrices[$vehicle['id']][$lang['code']]['price'] ?? '';
                                                ?>
                                                <tr class="vehicle-row-<?= $lang['code'] ?>-<?= $vehicle['id'] ?>">
                                                    <td>
                                                        <input type="checkbox" class="form-check-input vehicle-checkbox" 
                                                               name="selected_vehicles[<?= $lang['code'] ?>][]" 
                                                               value="<?= $vehicle['id'] ?>"
                                                               data-lang="<?= $lang['code'] ?>" data-vehicle="<?= $vehicle['id'] ?>"
                                                               <?= $isSelected ? 'checked' : '' ?>>
                                                    </td>
                                                    <td class="vehicle-row-clickable" style="cursor: pointer;">
                                                        <?php if (!empty($vehicle['image'])): ?>
                                                        <img src="<?= e(getMediaUrl($vehicle['image'])) ?>" alt="" class="rounded me-2" style="width: 40px; height: 28px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <strong><?= e($vehicle['brand']) ?> <?= e($vehicle['model']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <i class="bi bi-people"></i> <?= (int)$vehicle['capacity'] ?>
                                                            <i class="bi bi-briefcase ms-1"></i> <?= (int)$vehicle['luggage_capacity'] ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="bi bi-info-circle me-1"></i> Seçili araçlar bu tur için aktif olacaktır.
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Başlık</label>
                                        <input type="text" name="translations[<?= $lang['code'] ?>][meta_title]" 
                                               class="form-control" value="<?= e($trans['meta_title'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Açıklama</label>
                                        <textarea name="translations[<?= $lang['code'] ?>][meta_description]" class="form-control" rows="2"><?= e($trans['meta_description'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Tur Ayarları</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Görsel</label>
                        <div class="input-group">
                            <input type="text" name="image" id="tour_image" class="form-control" value="<?= e($editData['image'] ?? '') ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('tour_image', 'image')">
                                <i class="bi bi-folder2-open"></i> Seç
                            </button>
                        </div>
                        <?php if (!empty($editData['image'])): ?>
                        <div class="mt-2">
                              <img src="<?= e(getMediaUrl($editData['image'])) ?>" alt="Önizleme" class="img-thumbnail" style="max-height: 120px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <?php $currentStatus = $editData['status'] ?? 'published'; ?>
                            <option value="published" <?= $currentStatus === 'published' ? 'selected' : '' ?>>Yayında</option>
                            <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Taslak</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                    
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1"
                               <?= ($editData['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Öne Çıkan Tur</label>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-1"></i> Kaydet
                </button>
                <a href="<?= ADMIN_URL ?>/tours.php" class="btn btn-outline-secondary">İptal</a>
            </div>
        </div>
    </div>
</form>

<?php else: ?>

<!-- Tur Listesi -->
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="60">Sıra</th>
                        <th width="80">Görsel</th>
                        <th>Başlık</th>
                        <th width="80">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tours as $tour): ?>
                    <tr>
                        <td><?= (int)$tour['sort_order'] ?></td>
                        <td>
                            <?php if (!empty($tour['image'])): ?>
                            <img src="<?= e(getMediaUrl($tour['image'])) ?>" class="rounded" style="width:60px;height:40px;object-fit:cover;">
                            <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:60px;height:40px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($tour['title']) ?></strong>
                            <?php if ($tour['is_featured']): ?>
                            <span class="badge bg-warning text-dark ms-1">Öne Çıkan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($tour['status'] === 'published'): ?>
                            <span class="badge bg-success">Yayında</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Taslak</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    data-delete data-entity="tours" data-id="<?= $tour['id'] ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sayfa Ayarları Modal -->
<div class="modal fade" id="pageSettingsModal" tabindex="-1" aria-labelledby="pageSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageSettingsModalLabel">
                    <i class="bi bi-gear me-2"></i>Tur Sayfası Ayarları
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="pageSettingsForm">
                    <input type="hidden" name="save_page_settings" value="1">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Sayfa Arka Plan Görseli</label>
                        <div class="input-group">
                            <input type="text" name="background_image" id="page_bg_image" class="form-control" 
                                   value="<?= e($pageSettings['background_image'] ?? '') ?>" placeholder="Görsel seçin...">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('page_bg_image', 'image')">
                                <i class="bi bi-folder2-open"></i> Seç
                            </button>
                        </div>
                        <?php if (!empty($pageSettings['background_image'])): ?>
                        <div class="mt-2">
                            <img src="<?= SITE_URL ?>/<?= e($pageSettings['background_image']) ?>" alt="Önizleme" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                        <?php endif; ?>
                        <small class="text-muted">Boş bırakılırsa varsayılan görsel kullanılır.</small>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="features_visible" class="form-check-input" id="features_visible" value="1"
                                   <?= ($pageSettings['features_visible'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="features_visible">Özellikler bölümünü göster</label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="fw-bold mb-3"><i class="bi bi-translate me-2"></i>Dil Bazlı Ayarlar</h6>
                    
                    <ul class="nav nav-tabs" role="tablist">
                        <?php foreach ($languages as $i => $lang): ?>
                        <li class="nav-item">
                            <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" type="button" 
                                    data-bs-toggle="tab" data-bs-target="#page-lang-<?= $lang['code'] ?>">
                                <?= e($lang['flag']) ?> <?= e($lang['native_name']) ?>
                            </button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="tab-content border border-top-0 rounded-bottom p-3">
                        <?php foreach ($languages as $i => $lang): 
                            $pageTrans = $pageSettingTranslations[$lang['code']] ?? [];
                        ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="page-lang-<?= $lang['code'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Sayfa Başlığı</label>
                                <input type="text" name="page_translations[<?= $lang['code'] ?>][title]" class="form-control" 
                                       value="<?= e($pageTrans['title'] ?? '') ?>" placeholder="Turlar">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">URL Slug</label>
                                <input type="text" name="page_translations[<?= $lang['code'] ?>][slug]" class="form-control" 
                                       value="<?= e($pageTrans['slug'] ?? '') ?>" placeholder="turlar">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sayfa Alt Başlığı / Açıklaması</label>
                                <textarea name="page_translations[<?= $lang['code'] ?>][subtitle]" class="form-control" rows="2" 
                                          placeholder="En iyi turlarımızı keşfedin..."><?= e($pageTrans['subtitle'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Türkçe karakter çevrimi ve slug oluşturma
function generateSlug(text) {
    const turkishMap = {
        'ç': 'c', 'Ç': 'c', 'ğ': 'g', 'Ğ': 'g', 'ı': 'i', 'I': 'i', 'İ': 'i',
        'ö': 'o', 'Ö': 'o', 'ş': 's', 'Ş': 's', 'ü': 'u', 'Ü': 'u',
        'ä': 'a', 'Ä': 'a', 'ß': 'ss', 'é': 'e', 'è': 'e', 'ê': 'e', 'ë': 'e',
        'à': 'a', 'â': 'a', 'î': 'i', 'ï': 'i', 'ô': 'o', 'û': 'u', 'ù': 'u',
        'ñ': 'n', 'Ñ': 'n', 'ý': 'y', 'ÿ': 'y', 'æ': 'ae', 'œ': 'oe'
    };
    
    let slug = text.toLowerCase();
    
    // Türkçe ve özel karakterleri çevir
    for (let key in turkishMap) {
        slug = slug.split(key).join(turkishMap[key]);
    }
    
    // Alfanumerik olmayan karakterleri tire ile değiştir
    slug = slug.replace(/[^a-z0-9]+/g, '-');
    
    // Baştaki ve sondaki tireleri kaldır
    slug = slug.replace(/^-+|-+$/g, '');
    
    return slug;
}

// Başlık alanlarına event listener ekle
document.querySelectorAll('input[name*="[title]"]').forEach(function(titleInput) {
    titleInput.addEventListener('input', function() {
        // Aynı dil için slug alanını bul
        const langMatch = this.name.match(/\[([a-z]{2})\]/);
        if (langMatch) {
            const langCode = langMatch[1];
            const slugInput = document.querySelector('input[name="translations[' + langCode + '][slug]"]');
            if (slugInput && !slugInput.dataset.manualEdit) {
                slugInput.value = generateSlug(this.value);
            }
        }
    });
});

// Slug alanı manuel düzenlenirse otomatik doldurmayı durdur
document.querySelectorAll('input[name*="[slug]"]').forEach(function(slugInput) {
    slugInput.addEventListener('input', function() {
        this.dataset.manualEdit = 'true';
    });
});

// Araç checkbox kontrolü
document.querySelectorAll('.vehicle-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const lang = this.dataset.lang;
        const vehicleId = this.dataset.vehicle;
        const priceInput = document.getElementById('price-' + lang + '-' + vehicleId);
        
        if (priceInput) {
            priceInput.disabled = !this.checked;
            if (!this.checked) {
                priceInput.value = '';
            } else {
                priceInput.focus();
            }
        }
    });
});

// Araç satırına tıklayınca checkbox'ı toggle et
document.querySelectorAll('.vehicle-row-clickable').forEach(function(row) {
    row.addEventListener('click', function() {
        const checkbox = this.closest('tr').querySelector('.vehicle-checkbox');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
