<?php
/**
 * Tur Yönetimi
 */

$pageTitle = 'Tur Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Aktif dilleri al
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

// Kategorileri al
$categories = $db->query("SELECT * FROM tour_categories ORDER BY sort_order")->fetchAll();

// Destinasyonları al
$destinations = $db->query("SELECT id, title FROM destinations WHERE status = 'published' ORDER BY title")->fetchAll();

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $durationDays = (int)($_POST['duration_days'] ?? 0);
    $durationNights = (int)($_POST['duration_nights'] ?? 0);
    $groupSizeMin = (int)($_POST['group_size_min'] ?? 1);
    $groupSizeMax = (int)($_POST['group_size_max'] ?? 10);
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $currency = $_POST['currency'] ?? 'USD';
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $destinationId = !empty($_POST['destination_id']) ? (int)$_POST['destination_id'] : null;
    $badge = trim($_POST['badge'] ?? '');
    $difficultyLevel = $_POST['difficulty_level'] ?? 'moderate';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
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
                        destination_id = ?, category_id = ?, duration_days = ?, duration_nights = ?,
                        group_size_min = ?, group_size_max = ?, price = ?, sale_price = ?,
                        currency = ?, badge = ?, difficulty_level = ?, is_featured = ?,
                        is_bestseller = ?, status = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug, 
                    $translations[$defaultLang['code']]['description'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $destinationId, $categoryId, $durationDays, $durationNights,
                    $groupSizeMin, $groupSizeMax, $price, $salePrice,
                    $currency, $badge, $difficultyLevel, $isFeatured,
                    $isBestseller, $status, $sortOrder, $id
                ]);
                $tourId = $id;
            } else {
                // Ekle
                $stmt = $db->prepare("
                    INSERT INTO tours (title, slug, description, content, destination_id, category_id,
                        duration_days, duration_nights, group_size_min, group_size_max, price, sale_price,
                        currency, badge, difficulty_level, is_featured, is_bestseller, status, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['description'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $destinationId, $categoryId, $durationDays, $durationNights,
                    $groupSizeMin, $groupSizeMax, $price, $salePrice,
                    $currency, $badge, $difficultyLevel, $isFeatured,
                    $isBestseller, $status, $sortOrder
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
}

// Tüm turları al
$tours = $db->query("
    SELECT t.*, tc.name as category_name, d.title as destination_name
    FROM tours t
    LEFT JOIN tour_categories tc ON t.category_id = tc.id
    LEFT JOIN destinations d ON t.destination_id = d.id
    ORDER BY t.sort_order, t.created_at DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Tur Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Yeni Tur Ekle
    </a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- Tur Formu -->
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
                            // Varsayılan dil için ana tablodan al
                            if ($isDefault && $editData) {
                                $trans = [
                                    'title' => $editData['title'],
                                    'slug' => $editData['slug'],
                                    'description' => $editData['description'],
                                    'content' => $editData['content'],
                                    'meta_title' => $editData['meta_title'],
                                    'meta_description' => $editData['meta_description']
                                ];
                            }
                        ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="lang-<?= $lang['code'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Başlık <?php if ($isDefault): ?><span class="text-danger">*</span><?php endif; ?></label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][title]" class="form-control" 
                                       value="<?= e($trans['title'] ?? '') ?>" <?= $isDefault ? 'required' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug (URL) <?php if ($isDefault): ?><span class="text-danger">*</span><?php endif; ?></label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][slug]" class="form-control" 
                                       value="<?= e($trans['slug'] ?? '') ?>" <?= $isDefault ? 'required' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kısa Açıklama</label>
                                <textarea name="translations[<?= $lang['code'] ?>][description]" class="form-control" rows="3"><?= e($trans['description'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">İçerik</label>
                                <textarea name="translations[<?= $lang['code'] ?>][content]" class="form-control summernote"><?= e($trans['content'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Başlık (SEO)</label>
                                        <input type="text" name="translations[<?= $lang['code'] ?>][meta_title]" class="form-control" 
                                               value="<?= e($trans['meta_title'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Açıklama (SEO)</label>
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
            <!-- Tur Ayarları -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Tur Ayarları</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($editData['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Taslak</option>
                            <option value="published" <?= ($editData['status'] ?? '') === 'published' ? 'selected' : '' ?>>Yayında</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Seçiniz</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($editData['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Destinasyon</label>
                        <select name="destination_id" class="form-select select2">
                            <option value="">Seçiniz</option>
                            <?php foreach ($destinations as $dest): ?>
                            <option value="<?= $dest['id'] ?>" <?= ($editData['destination_id'] ?? '') == $dest['id'] ? 'selected' : '' ?>>
                                <?= e($dest['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Süre (Gün)</label>
                                <input type="number" name="duration_days" class="form-control" 
                                       value="<?= (int)($editData['duration_days'] ?? 1) ?>" min="1">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Süre (Gece)</label>
                                <input type="number" name="duration_nights" class="form-control" 
                                       value="<?= (int)($editData['duration_nights'] ?? 0) ?>" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Min Kişi</label>
                                <input type="number" name="group_size_min" class="form-control" 
                                       value="<?= (int)($editData['group_size_min'] ?? 1) ?>" min="1">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Max Kişi</label>
                                <input type="number" name="group_size_max" class="form-control" 
                                       value="<?= (int)($editData['group_size_max'] ?? 10) ?>" min="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-8">
                            <div class="mb-3">
                                <label class="form-label">Fiyat</label>
                                <input type="number" name="price" class="form-control" step="0.01"
                                       value="<?= (float)($editData['price'] ?? 0) ?>" min="0" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-3">
                                <label class="form-label">Para Birimi</label>
                                <select name="currency" class="form-select">
                                    <option value="USD" <?= ($editData['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="EUR" <?= ($editData['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                    <option value="TRY" <?= ($editData['currency'] ?? '') === 'TRY' ? 'selected' : '' ?>>TRY</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">İndirimli Fiyat</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01"
                               value="<?= $editData['sale_price'] ?? '' ?>" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rozet</label>
                        <input type="text" name="badge" class="form-control" 
                               value="<?= e($editData['badge'] ?? '') ?>" placeholder="Örn: Popüler, Yeni">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Zorluk Seviyesi</label>
                        <select name="difficulty_level" class="form-select">
                            <option value="easy" <?= ($editData['difficulty_level'] ?? '') === 'easy' ? 'selected' : '' ?>>Kolay</option>
                            <option value="moderate" <?= ($editData['difficulty_level'] ?? 'moderate') === 'moderate' ? 'selected' : '' ?>>Orta</option>
                            <option value="challenging" <?= ($editData['difficulty_level'] ?? '') === 'challenging' ? 'selected' : '' ?>>Zor</option>
                            <option value="extreme" <?= ($editData['difficulty_level'] ?? '') === 'extreme' ? 'selected' : '' ?>>Ekstrem</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" 
                               value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                    
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1"
                               <?= ($editData['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_bestseller" class="form-check-input" id="is_bestseller" value="1"
                               <?= ($editData['is_bestseller'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_bestseller">Çok Satan</label>
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
                        <th>Başlık</th>
                        <th>Kategori</th>
                        <th>Destinasyon</th>
                        <th>Süre</th>
                        <th>Fiyat</th>
                        <th width="80">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tours as $tour): ?>
                    <tr>
                        <td><?= (int)$tour['sort_order'] ?></td>
                        <td>
                            <strong><?= e($tour['title']) ?></strong>
                            <?php if ($tour['is_featured']): ?>
                            <span class="badge bg-warning text-dark ms-1">Öne Çıkan</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($tour['category_name'] ?? '-') ?></td>
                        <td><?= e($tour['destination_name'] ?? '-') ?></td>
                        <td><?= (int)$tour['duration_days'] ?> Gün</td>
                        <td>
                            <?php if ($tour['sale_price']): ?>
                            <del class="text-muted"><?= $tour['currency'] ?><?= number_format($tour['price'], 0) ?></del>
                            <strong class="text-success"><?= $tour['currency'] ?><?= number_format($tour['sale_price'], 0) ?></strong>
                            <?php else: ?>
                            <strong><?= $tour['currency'] ?><?= number_format($tour['price'], 0) ?></strong>
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
                            <a href="?action=delete&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
