<?php
/**
 * Section Yönetimi
 */

$pageTitle = 'Section Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$pageId = $_GET['page_id'] ?? null;

// Aktif dilleri al
$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

// Sayfaları al
$pages = $db->query("SELECT id, title FROM pages ORDER BY sort_order")->fetchAll();

// Section tipleri
$sectionTypes = [
    'hero' => 'Hero / Slider',
    'why_us' => 'Neden Biz',
    'destinations' => 'Destinasyonlar',
    'tours' => 'Turlar',
    'testimonials' => 'Müşteri Yorumları',
    'cta' => 'Call to Action',
    'gallery' => 'Galeri',
    'blog' => 'Blog Yazıları',
    'faq' => 'SSS',
    'contact' => 'İletişim Formu',
    'custom' => 'Özel İçerik'
];

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sectionPageId = (int)($_POST['page_id'] ?? 0);
    $sectionKey = trim($_POST['section_key'] ?? '');
    $sectionType = $_POST['section_type'] ?? 'custom';
    $backgroundImage = trim($_POST['background_image'] ?? '');
    $backgroundVideo = trim($_POST['background_video'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $settings = $_POST['settings'] ?? '{}';
    
    // Çeviriler
    $translations = $_POST['translations'] ?? [];
    $defaultTitle = $translations[$defaultLang['code']]['title'] ?? '';
    
    if (empty($sectionKey)) {
        setFlashMessage('error', 'Section anahtarı gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            if ($id) {
                $stmt = $db->prepare("
                    UPDATE sections SET 
                        page_id = ?, section_key = ?, section_type = ?, title = ?, subtitle = ?,
                        content = ?, settings = ?, background_image = ?, background_video = ?,
                        sort_order = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $sectionPageId, $sectionKey, $sectionType, $defaultTitle,
                    $translations[$defaultLang['code']]['subtitle'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $settings, $backgroundImage, $backgroundVideo, $sortOrder, $isActive, $id
                ]);
                $sectionId = $id;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO sections (page_id, section_key, section_type, title, subtitle, content, settings, background_image, background_video, sort_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $sectionPageId, $sectionKey, $sectionType, $defaultTitle,
                    $translations[$defaultLang['code']]['subtitle'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $settings, $backgroundImage, $backgroundVideo, $sortOrder, $isActive
                ]);
                $sectionId = $db->lastInsertId();
            }
            
            // Çevirileri kaydet
            foreach ($translations as $langCode => $trans) {
                if ($langCode === $defaultLang['code']) continue;
                if (empty($trans['title'])) continue;
                
                $stmt = $db->prepare("
                    INSERT INTO section_translations (section_id, language_code, title, subtitle, content, settings)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        title = VALUES(title), subtitle = VALUES(subtitle), content = VALUES(content), settings = VALUES(settings)
                ");
                $stmt->execute([
                    $sectionId, $langCode, $trans['title'], $trans['subtitle'] ?? '',
                    $trans['content'] ?? '', $trans['settings'] ?? null
                ]);
            }
            
            $db->commit();
            setFlashMessage('success', $id ? 'Section güncellendi.' : 'Section eklendi.');
            header('Location: ' . ADMIN_URL . '/sections.php');
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
        $db->prepare("DELETE FROM sections WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Section silindi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/sections.php');
    exit;
}

// Düzenleme için veri al
$editData = null;
$editTranslations = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM sections WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM section_translations WHERE section_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $editTranslations[$row['language_code']] = $row;
    }
}

// Tüm section'ları al
$sql = "SELECT s.*, p.title as page_title FROM sections s LEFT JOIN pages p ON s.page_id = p.id";
if ($pageId) {
    $sql .= " WHERE s.page_id = " . (int)$pageId;
}
$sql .= " ORDER BY s.page_id, s.sort_order";
$sections = $db->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Section Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <div class="d-flex gap-2">
        <select class="form-select" onchange="location.href='?page_id='+this.value">
            <option value="">Tüm Sayfalar</option>
            <?php foreach ($pages as $page): ?>
            <option value="<?= $page['id'] ?>" <?= $pageId == $page['id'] ? 'selected' : '' ?>><?= e($page['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <a href="?action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Yeni Section
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- Section Formu -->
<form method="post" action="?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>">
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
                                    'subtitle' => $editData['subtitle'],
                                    'content' => $editData['content']
                                ];
                            }
                        ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="lang-<?= $lang['code'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][title]" class="form-control" 
                                       value="<?= e($trans['title'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alt Başlık</label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][subtitle]" class="form-control" 
                                       value="<?= e($trans['subtitle'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">İçerik</label>
                                <textarea name="translations[<?= $lang['code'] ?>][content]" class="form-control summernote"><?= e($trans['content'] ?? '') ?></textarea>
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
                    <h6 class="mb-0 fw-bold">Section Ayarları</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Sayfa <span class="text-danger">*</span></label>
                        <select name="page_id" class="form-select" required>
                            <?php foreach ($pages as $page): ?>
                            <option value="<?= $page['id'] ?>" <?= ($editData['page_id'] ?? '') == $page['id'] ? 'selected' : '' ?>>
                                <?= e($page['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Section Anahtarı <span class="text-danger">*</span></label>
                        <input type="text" name="section_key" class="form-control" 
                               value="<?= e($editData['section_key'] ?? '') ?>" required placeholder="hero, why_us, tours...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Section Tipi</label>
                        <select name="section_type" class="form-select">
                            <?php foreach ($sectionTypes as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($editData['section_type'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Arka Plan Görsel</label>
                        <input type="text" name="background_image" class="form-control" 
                               value="<?= e($editData['background_image'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Arka Plan Video</label>
                        <input type="text" name="background_video" class="form-control" 
                               value="<?= e($editData['background_video'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ayarlar (JSON)</label>
                        <textarea name="settings" class="form-control" rows="4"><?= e($editData['settings'] ?? '{}') ?></textarea>
                        <small class="text-muted">Örn: {"limit": 6, "show_button": true}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" 
                               value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1"
                               <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif (Görünür)</label>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-1"></i> Kaydet
                </button>
                <a href="<?= ADMIN_URL ?>/sections.php" class="btn btn-outline-secondary">İptal</a>
            </div>
        </div>
    </div>
</form>

<?php else: ?>
<!-- Section Listesi -->
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="60">Sıra</th>
                        <th>Sayfa</th>
                        <th>Anahtar</th>
                        <th>Tip</th>
                        <th>Başlık</th>
                        <th width="80">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sections as $section): ?>
                    <tr>
                        <td><?= (int)$section['sort_order'] ?></td>
                        <td><?= e($section['page_title'] ?? '-') ?></td>
                        <td><code><?= e($section['section_key']) ?></code></td>
                        <td><?= e($sectionTypes[$section['section_type']] ?? $section['section_type']) ?></td>
                        <td><?= e($section['title'] ?? '-') ?></td>
                        <td>
                            <?php if ($section['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?= $section['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?action=delete&id=<?= $section['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
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
