<?php
/**
 * Araç Yönetimi
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Dilleri al
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY is_default DESC, name")->fetchAll();
$defaultLang = null;
foreach ($languages as $lang) {
    if ($lang['is_default']) {
        $defaultLang = $lang;
        break;
    }
}
if (!$defaultLang && !empty($languages)) {
    $defaultLang = $languages[0];
}

// Vehicles tablosunu oluştur
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS vehicles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            brand VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            capacity INT NOT NULL DEFAULT 4,
            luggage_capacity INT DEFAULT 2,
            child_seat_capacity INT DEFAULT 0,
            image VARCHAR(255),
            services TEXT,
            description TEXT,
            price_per_km DECIMAL(10,2),
            base_price DECIMAL(10,2),
            is_featured TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Çocuk koltuğu kapasitesi alanını ekle (mevcut tabloya)
    try {
        $db->query("ALTER TABLE vehicles ADD COLUMN child_seat_capacity INT DEFAULT 0 AFTER luggage_capacity");
    } catch (Exception $e) {}
} catch (Exception $e) {}

// Araç hizmetleri tablosu
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS vehicle_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            icon VARCHAR(100) NOT NULL DEFAULT 'bi-check-circle',
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS vehicle_service_translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_id INT NOT NULL,
            language_code VARCHAR(5) NOT NULL,
            name VARCHAR(255) NOT NULL,
            UNIQUE KEY unique_service_lang (service_id, language_code),
            FOREIGN KEY (service_id) REFERENCES vehicle_services(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Exception $e) {}

// Hizmetleri kaydetme
if ($action === 'save_services' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        $services = $_POST['services'] ?? [];
        
        // Mevcut hizmetleri sil ve yeniden oluştur
        $db->query("DELETE FROM vehicle_services");
        
        foreach ($services as $index => $service) {
            $icon = trim($service['icon'] ?? 'bi-check-circle');
            $names = $service['names'] ?? [];
            
            // En az bir isim girilmiş olmalı
            $hasName = false;
            foreach ($names as $name) {
                if (!empty(trim($name))) {
                    $hasName = true;
                    break;
                }
            }
            
            if ($hasName) {
                $stmt = $db->prepare("INSERT INTO vehicle_services (icon, sort_order) VALUES (?, ?)");
                $stmt->execute([$icon, $index]);
                $serviceId = $db->lastInsertId();
                
                foreach ($names as $langCode => $name) {
                    $name = trim($name);
                    if (!empty($name)) {
                        $stmt = $db->prepare("
                            INSERT INTO vehicle_service_translations (service_id, language_code, name)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$serviceId, $langCode, $name]);
                    }
                }
            }
        }
        
        $db->commit();
        setFlashMessage('success', 'Araç hizmetleri kaydedildi.');
    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/vehicles.php');
    exit;
}

// Silme işlemi
if ($action === 'delete' && $id) {
    try {
        $db->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Araç silindi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/vehicles.php');
    exit;
}

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 4);
    $luggageCapacity = (int)($_POST['luggage_capacity'] ?? 2);
    $childSeatCapacity = (int)($_POST['child_seat_capacity'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $services = isset($_POST['services']) ? json_encode($_POST['services']) : '[]';
    $description = trim($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    if (empty($brand) || empty($model)) {
        setFlashMessage('error', 'Marka ve model alanları zorunludur.');
    } else {
        try {
            if ($id) {
                $stmt = $db->prepare("
                    UPDATE vehicles SET 
                        brand = ?, model = ?, capacity = ?, luggage_capacity = ?, child_seat_capacity = ?,
                        image = ?, services = ?, description = ?,
                        is_featured = ?, is_active = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $brand, $model, $capacity, $luggageCapacity, $childSeatCapacity,
                    $image, $services, $description,
                    $isFeatured, $isActive, $sortOrder, $id
                ]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO vehicles (brand, model, capacity, luggage_capacity, child_seat_capacity, image, services, description, is_featured, is_active, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $brand, $model, $capacity, $luggageCapacity, $childSeatCapacity,
                    $image, $services, $description,
                    $isFeatured, $isActive, $sortOrder
                ]);
            }
            
            setFlashMessage('success', $id ? 'Araç güncellendi.' : 'Araç eklendi.');
            header('Location: ' . ADMIN_URL . '/vehicles.php');
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

// Header'ı dahil et
$pageTitle = 'Araç Yönetimi';
require_once __DIR__ . '/includes/header.php';

// Düzenleme için veri al
$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

// Tüm araçları al
$vehicles = $db->query("SELECT * FROM vehicles ORDER BY sort_order, brand, model")->fetchAll();

// Araç içi hizmetleri veritabanından al
$availableServices = [];
try {
    $serviceRows = $db->query("SELECT * FROM vehicle_services WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
    foreach ($serviceRows as $service) {
        $serviceId = $service['id'];
        $availableServices[$serviceId] = [
            'id' => $serviceId,
            'icon' => $service['icon'],
            'translations' => []
        ];
        
        $stmt = $db->prepare("SELECT * FROM vehicle_service_translations WHERE service_id = ?");
        $stmt->execute([$serviceId]);
        while ($trans = $stmt->fetch()) {
            $availableServices[$serviceId]['translations'][$trans['language_code']] = $trans['name'];
        }
        
        // Varsayılan dildeki ismi label olarak kullan
        $availableServices[$serviceId]['label'] = $availableServices[$serviceId]['translations'][$defaultLang['code']] ?? 'Hizmet #' . $serviceId;
    }
} catch (Exception $e) {}

// Eğer hiç hizmet yoksa varsayılanları ekle
if (empty($availableServices)) {
    $defaultServices = [
        ['icon' => 'bi-wifi', 'names' => ['tr' => 'Wi-Fi', 'en' => 'Wi-Fi']],
        ['icon' => 'bi-snow', 'names' => ['tr' => 'Klima', 'en' => 'Air Conditioning']],
        ['icon' => 'bi-droplet', 'names' => ['tr' => 'Su İkramı', 'en' => 'Water']],
        ['icon' => 'bi-plug', 'names' => ['tr' => 'Şarj Soketi', 'en' => 'Charger']],
        ['icon' => 'bi-tv', 'names' => ['tr' => 'TV/Ekran', 'en' => 'TV/Screen']],
        ['icon' => 'bi-person-arms-up', 'names' => ['tr' => 'Çocuk Koltuğu', 'en' => 'Child Seat']],
        ['icon' => 'bi-star', 'names' => ['tr' => 'Deri Koltuk', 'en' => 'Leather Seats']],
        ['icon' => 'bi-cup-straw', 'names' => ['tr' => 'Minibar', 'en' => 'Minibar']],
        ['icon' => 'bi-newspaper', 'names' => ['tr' => 'Gazete/Dergi', 'en' => 'Newspaper']],
        ['icon' => 'bi-person-badge', 'names' => ['tr' => 'Karşılama Hizmeti', 'en' => 'Meet & Greet']],
    ];
    
    try {
        foreach ($defaultServices as $index => $ds) {
            $stmt = $db->prepare("INSERT INTO vehicle_services (icon, sort_order) VALUES (?, ?)");
            $stmt->execute([$ds['icon'], $index]);
            $newId = $db->lastInsertId();
            
            foreach ($ds['names'] as $langCode => $name) {
                $stmt = $db->prepare("INSERT INTO vehicle_service_translations (service_id, language_code, name) VALUES (?, ?, ?)");
                $stmt->execute([$newId, $langCode, $name]);
            }
            
            $availableServices[$newId] = [
                'id' => $newId,
                'icon' => $ds['icon'],
                'label' => $ds['names'][$defaultLang['code']] ?? $ds['names']['tr'],
                'translations' => $ds['names']
            ];
        }
    } catch (Exception $e) {}
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Araç Yönetimi</h1>
    <div>
        <?php if ($action === 'list'): ?>
        <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#servicesModal">
            <i class="bi bi-gear me-1"></i> Hizmetleri Yönet
        </button>
        <a href="?action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Yeni Araç
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<form method="post" action="?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Araç Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Marka <span class="text-danger">*</span></label>
                            <input type="text" name="brand" class="form-control" value="<?= e($editData['brand'] ?? '') ?>" required placeholder="Mercedes-Benz, BMW, Audi...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control" value="<?= e($editData['model'] ?? '') ?>" required placeholder="Vito, E-Class, A6...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Yolcu Kapasitesi</label>
                            <input type="number" name="capacity" class="form-control" value="<?= (int)($editData['capacity'] ?? 4) ?>" min="1" max="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bagaj Kapasitesi</label>
                            <input type="number" name="luggage_capacity" class="form-control" value="<?= (int)($editData['luggage_capacity'] ?? 2) ?>" min="0" max="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Çocuk Koltuğu</label>
                            <input type="number" name="child_seat_capacity" class="form-control" value="<?= (int)($editData['child_seat_capacity'] ?? 0) ?>" min="0" max="10">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sıralama</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Açıklama</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Araç hakkında kısa bilgi..."><?= e($editData['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-gear me-2"></i>Araç İçi Hizmetler</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $selectedServices = json_decode($editData['services'] ?? '[]', true) ?: [];
                    // String key'leri int'e çevir (eski verilerle uyumluluk)
                    $selectedServices = array_map(function($s) { return is_numeric($s) ? (int)$s : $s; }, $selectedServices);
                    ?>
                    <?php if (empty($availableServices)): ?>
                    <div class="text-muted">
                        <i class="bi bi-info-circle me-1"></i> Henüz hizmet tanımlanmamış. 
                        <a href="<?= ADMIN_URL ?>/vehicles.php" class="text-primary">Hizmetleri Yönet</a> butonundan ekleyebilirsiniz.
                    </div>
                    <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($availableServices as $serviceId => $service): ?>
                        <div class="col-md-4 col-6">
                            <div class="form-check">
                                <input type="checkbox" name="services[]" value="<?= (int)$serviceId ?>" 
                                       class="form-check-input" id="service_<?= $serviceId ?>"
                                       <?= in_array((int)$serviceId, $selectedServices) || in_array((string)$serviceId, $selectedServices) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="service_<?= $serviceId ?>">
                                    <i class="bi <?= e($service['icon']) ?> me-1"></i> <?= e($service['label']) ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Araç Görseli</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Görsel</label>
                        <div class="input-group">
                            <input type="text" name="image" id="vehicle_image" class="form-control" value="<?= e($editData['image'] ?? '') ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="openMediaPicker('vehicle_image', 'image')">
                                <i class="bi bi-folder2-open"></i> Seç
                            </button>
                        </div>
                        <?php if (!empty($editData['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= e(getMediaUrl($editData['image'])) ?>" alt="Önizleme" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Durum</h6>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1"
                               <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1"
                               <?= ($editData['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-1"></i> Kaydet
                </button>
                <a href="<?= ADMIN_URL ?>/vehicles.php" class="btn btn-outline-secondary">İptal</a>
            </div>
        </div>
    </div>
</form>

<?php else: ?>
<div class="card table-card">
    <div class="card-body">
        <?php if (empty($vehicles)): ?>
        <div class="text-center py-5">
            <i class="bi bi-car-front display-1 text-muted"></i>
            <p class="text-muted mt-3">Henüz araç eklenmemiş.</p>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> İlk Aracı Ekle
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="60">Sıra</th>
                        <th width="80">Görsel</th>
                        <th>Araç</th>
                        <th>Kapasite</th>
                        <th>Hizmetler</th>
                        <th width="80">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?= (int)$vehicle['sort_order'] ?></td>
                        <td>
                            <?php if (!empty($vehicle['image'])): ?>
                            <img src="<?= e(getMediaUrl($vehicle['image'])) ?>" alt="<?= e($vehicle['brand']) ?>" class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 40px;">
                                <i class="bi bi-car-front text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($vehicle['brand']) ?> <?= e($vehicle['model']) ?></strong>
                            <?php if ($vehicle['is_featured']): ?>
                            <span class="badge bg-warning text-dark ms-1">Öne Çıkan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="bi bi-people me-1"></i><?= (int)$vehicle['capacity'] ?>
                            <i class="bi bi-briefcase ms-2 me-1"></i><?= (int)$vehicle['luggage_capacity'] ?>
                        </td>
                        <td>
                            <?php 
                            $services = json_decode($vehicle['services'] ?? '[]', true) ?: [];
                            $serviceCount = count($services);
                            if ($serviceCount > 0): ?>
                            <span class="badge bg-info"><?= $serviceCount ?> hizmet</span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($vehicle['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?= $vehicle['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?action=delete&id=<?= $vehicle['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Hizmetleri Yönet Modal -->
<div class="modal fade" id="servicesModal" tabindex="-1" aria-labelledby="servicesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="servicesModalLabel">
                    <i class="bi bi-gear me-2"></i>Araç İçi Hizmetleri Yönet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="?action=save_services">
                <div class="modal-body">
                    <p class="text-muted mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Araçlara eklenebilecek hizmetleri buradan yönetebilirsiniz. Her hizmet için icon ve dil bazlı isimler tanımlayabilirsiniz.
                    </p>
                    
                    <div id="services-container">
                        <?php 
                        $serviceIndex = 0;
                        foreach ($availableServices as $serviceId => $service): 
                        ?>
                        <div class="service-item card mb-3" data-index="<?= $serviceIndex ?>">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold"><i class="bi bi-grip-vertical me-2 text-muted"></i>Hizmet #<?= $serviceIndex + 1 ?></span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-service-btn">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Icon</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi <?= e($service['icon']) ?>"></i></span>
                                            <input type="text" name="services[<?= $serviceIndex ?>][icon]" class="form-control icon-input" 
                                                   value="<?= e($service['icon']) ?>" placeholder="bi-check-circle">
                                            <button type="button" class="btn btn-outline-secondary icon-picker-btn" data-index="<?= $serviceIndex ?>">
                                                <i class="bi bi-grid-3x3-gap"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label">Hizmet Adı (Dil Bazlı)</label>
                                        <div class="row g-2">
                                            <?php foreach ($languages as $lang): ?>
                                            <div class="col-md-4">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><?= strtoupper($lang['code']) ?></span>
                                                    <input type="text" name="services[<?= $serviceIndex ?>][names][<?= $lang['code'] ?>]" 
                                                           class="form-control" 
                                                           value="<?= e($service['translations'][$lang['code']] ?? '') ?>"
                                                           placeholder="<?= e($lang['name']) ?>">
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                        $serviceIndex++;
                        endforeach; 
                        ?>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary" id="add-service-btn">
                        <i class="bi bi-plus-lg me-1"></i> Yeni Hizmet Ekle
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Hizmetleri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Icon Picker Modal -->
<div class="modal fade" id="iconPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-grid-3x3-gap me-2"></i>Icon Seç</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="iconSearch" placeholder="Icon ara...">
                <div class="icon-grid" style="max-height: 400px; overflow-y: auto;">
                    <?php
                    $icons = [
                        'bi-wifi', 'bi-snow', 'bi-droplet', 'bi-plug', 'bi-tv', 'bi-person-arms-up',
                        'bi-star', 'bi-star-fill', 'bi-cup-straw', 'bi-newspaper', 'bi-person-badge',
                        'bi-check-circle', 'bi-check-circle-fill', 'bi-shield-check', 'bi-award',
                        'bi-lightning', 'bi-lightning-fill', 'bi-battery-charging', 'bi-usb-plug',
                        'bi-music-note', 'bi-music-note-beamed', 'bi-headphones', 'bi-speaker',
                        'bi-phone', 'bi-telephone', 'bi-sim', 'bi-router', 'bi-broadcast',
                        'bi-car-front', 'bi-car-front-fill', 'bi-truck', 'bi-bus-front',
                        'bi-airplane', 'bi-train-front', 'bi-bicycle', 'bi-scooter',
                        'bi-geo-alt', 'bi-geo-alt-fill', 'bi-map', 'bi-compass', 'bi-signpost',
                        'bi-clock', 'bi-clock-fill', 'bi-alarm', 'bi-stopwatch', 'bi-hourglass',
                        'bi-calendar', 'bi-calendar-check', 'bi-calendar-event', 'bi-calendar-heart',
                        'bi-heart', 'bi-heart-fill', 'bi-suit-heart', 'bi-emoji-smile',
                        'bi-hand-thumbs-up', 'bi-hand-thumbs-up-fill', 'bi-trophy', 'bi-trophy-fill',
                        'bi-gift', 'bi-gift-fill', 'bi-box', 'bi-bag', 'bi-bag-check', 'bi-basket',
                        'bi-cart', 'bi-cart-check', 'bi-credit-card', 'bi-wallet', 'bi-cash',
                        'bi-currency-dollar', 'bi-currency-euro', 'bi-coin', 'bi-piggy-bank',
                        'bi-house', 'bi-house-fill', 'bi-building', 'bi-buildings', 'bi-shop',
                        'bi-door-open', 'bi-key', 'bi-lock', 'bi-unlock', 'bi-shield-lock',
                        'bi-person', 'bi-person-fill', 'bi-people', 'bi-people-fill', 'bi-person-check',
                        'bi-briefcase', 'bi-briefcase-fill', 'bi-suitcase', 'bi-luggage',
                        'bi-cup-hot', 'bi-cup-hot-fill', 'bi-cup', 'bi-egg-fried',
                        'bi-thermometer', 'bi-thermometer-half', 'bi-fan', 'bi-wind',
                        'bi-sun', 'bi-moon', 'bi-cloud', 'bi-umbrella', 'bi-snow2',
                        'bi-camera', 'bi-camera-fill', 'bi-image', 'bi-images', 'bi-film',
                        'bi-book', 'bi-journal', 'bi-file-text', 'bi-file-earmark-text',
                        'bi-envelope', 'bi-envelope-fill', 'bi-chat', 'bi-chat-dots', 'bi-megaphone',
                        'bi-bell', 'bi-bell-fill', 'bi-volume-up', 'bi-volume-mute',
                        'bi-eye', 'bi-eye-fill', 'bi-binoculars', 'bi-search',
                        'bi-tools', 'bi-wrench', 'bi-hammer', 'bi-screwdriver', 'bi-gear', 'bi-gear-fill',
                        'bi-bandaid', 'bi-capsule', 'bi-hospital', 'bi-activity',
                        'bi-recycle', 'bi-tree', 'bi-flower1', 'bi-flower2', 'bi-bug',
                        'bi-globe', 'bi-globe2', 'bi-translate', 'bi-flag', 'bi-pin-map'
                    ];
                    foreach ($icons as $icon):
                    ?>
                    <button type="button" class="btn btn-outline-secondary icon-option m-1" data-icon="<?= $icon ?>">
                        <i class="bi <?= $icon ?>"></i>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let serviceIndex = <?= $serviceIndex ?>;
    let currentIconTarget = null;
    
    // Yeni hizmet ekle
    document.getElementById('add-service-btn')?.addEventListener('click', function() {
        const container = document.getElementById('services-container');
        const langInputs = <?= json_encode(array_map(function($l) { return ['code' => $l['code'], 'name' => $l['name']]; }, $languages)) ?>;
        
        let langHtml = '';
        langInputs.forEach(lang => {
            langHtml += `
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">${lang.code.toUpperCase()}</span>
                        <input type="text" name="services[${serviceIndex}][names][${lang.code}]" 
                               class="form-control" placeholder="${lang.name}">
                    </div>
                </div>
            `;
        });
        
        const html = `
            <div class="service-item card mb-3" data-index="${serviceIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold"><i class="bi bi-grip-vertical me-2 text-muted"></i>Hizmet #${serviceIndex + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-service-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Icon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                                <input type="text" name="services[${serviceIndex}][icon]" class="form-control icon-input" 
                                       value="bi-check-circle" placeholder="bi-check-circle">
                                <button type="button" class="btn btn-outline-secondary icon-picker-btn" data-index="${serviceIndex}">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Hizmet Adı (Dil Bazlı)</label>
                            <div class="row g-2">${langHtml}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        serviceIndex++;
    });
    
    // Hizmet sil
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-service-btn')) {
            e.target.closest('.service-item').remove();
        }
    });
    
    // Icon picker aç
    document.addEventListener('click', function(e) {
        if (e.target.closest('.icon-picker-btn')) {
            const btn = e.target.closest('.icon-picker-btn');
            currentIconTarget = btn.closest('.input-group').querySelector('.icon-input');
            const iconPickerModal = new bootstrap.Modal(document.getElementById('iconPickerModal'));
            iconPickerModal.show();
        }
    });
    
    // Icon seç
    document.querySelectorAll('.icon-option').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.dataset.icon;
            if (currentIconTarget) {
                currentIconTarget.value = icon;
                const preview = currentIconTarget.closest('.input-group').querySelector('.input-group-text i');
                if (preview) {
                    preview.className = 'bi ' + icon;
                }
            }
            bootstrap.Modal.getInstance(document.getElementById('iconPickerModal')).hide();
        });
    });
    
    // Icon arama
    document.getElementById('iconSearch')?.addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('.icon-option').forEach(btn => {
            const icon = btn.dataset.icon.toLowerCase();
            btn.style.display = icon.includes(search) ? '' : 'none';
        });
    });
    
    // Icon input değiştiğinde preview güncelle
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('icon-input')) {
            const preview = e.target.closest('.input-group').querySelector('.input-group-text i');
            if (preview) {
                preview.className = 'bi ' + e.target.value;
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
