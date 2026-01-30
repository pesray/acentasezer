<?php
/**
 * Slider Yönetimi
 */

$pageTitle = 'Slider Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image = trim($_POST['image'] ?? '');
    $video = trim($_POST['video'] ?? '');
    $overlayColor = trim($_POST['overlay_color'] ?? 'rgba(0,0,0,0.5)');
    $textPosition = $_POST['text_position'] ?? 'left';
    $location = $_POST['location'] ?? 'home';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    $translations = $_POST['translations'] ?? [];
    $defaultTitle = $translations[$defaultLang['code']]['title'] ?? '';
    
    try {
        $db->beginTransaction();
        
        if ($id) {
            $stmt = $db->prepare("
                UPDATE sliders SET title = ?, subtitle = ?, image = ?, video = ?,
                    button_text = ?, button_url = ?, button2_text = ?, button2_url = ?,
                    overlay_color = ?, text_position = ?, location = ?, is_active = ?, sort_order = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $defaultTitle,
                $translations[$defaultLang['code']]['subtitle'] ?? '',
                $image, $video,
                $translations[$defaultLang['code']]['button_text'] ?? '',
                $translations[$defaultLang['code']]['button_url'] ?? '',
                $translations[$defaultLang['code']]['button2_text'] ?? '',
                $translations[$defaultLang['code']]['button2_url'] ?? '',
                $overlayColor, $textPosition, $location, $isActive, $sortOrder, $id
            ]);
            $sliderId = $id;
        } else {
            $stmt = $db->prepare("
                INSERT INTO sliders (title, subtitle, image, video, button_text, button_url, button2_text, button2_url, overlay_color, text_position, location, is_active, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $defaultTitle,
                $translations[$defaultLang['code']]['subtitle'] ?? '',
                $image, $video,
                $translations[$defaultLang['code']]['button_text'] ?? '',
                $translations[$defaultLang['code']]['button_url'] ?? '',
                $translations[$defaultLang['code']]['button2_text'] ?? '',
                $translations[$defaultLang['code']]['button2_url'] ?? '',
                $overlayColor, $textPosition, $location, $isActive, $sortOrder
            ]);
            $sliderId = $db->lastInsertId();
        }
        
        foreach ($translations as $langCode => $trans) {
            if ($langCode === $defaultLang['code']) continue;
            if (empty($trans['title'])) continue;
            
            $stmt = $db->prepare("
                INSERT INTO slider_translations (slider_id, language_code, title, subtitle, button_text, button_url, button2_text, button2_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    title = VALUES(title), subtitle = VALUES(subtitle),
                    button_text = VALUES(button_text), button_url = VALUES(button_url),
                    button2_text = VALUES(button2_text), button2_url = VALUES(button2_url)
            ");
            $stmt->execute([
                $sliderId, $langCode, $trans['title'], $trans['subtitle'] ?? '',
                $trans['button_text'] ?? '', $trans['button_url'] ?? '',
                $trans['button2_text'] ?? '', $trans['button2_url'] ?? ''
            ]);
        }
        
        $db->commit();
        setFlashMessage('success', $id ? 'Slider güncellendi.' : 'Slider eklendi.');
        header('Location: ' . ADMIN_URL . '/sliders.php');
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
}

if ($action === 'delete' && $id) {
    try {
        $db->prepare("DELETE FROM sliders WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Slider silindi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/sliders.php');
    exit;
}

$editData = null;
$editTranslations = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM sliders WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM slider_translations WHERE slider_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $editTranslations[$row['language_code']] = $row;
    }
}

$sliders = $db->query("SELECT * FROM sliders ORDER BY location, sort_order")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Slider Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni Slider</a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<form method="post" action="?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white py-2">
                    <ul class="nav nav-tabs lang-tabs card-header-tabs" role="tablist">
                        <?php foreach ($languages as $i => $lang): ?>
                        <li class="nav-item">
                            <button type="button" class="nav-link <?= $i === 0 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#lang-<?= $lang['code'] ?>"><?= e($lang['flag']) ?> <?= e($lang['native_name']) ?></button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <?php foreach ($languages as $i => $lang): 
                            $trans = $editTranslations[$lang['code']] ?? [];
                            if ($lang['is_default'] && $editData) { $trans = $editData; }
                        ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="lang-<?= $lang['code'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][title]" class="form-control" value="<?= e($trans['title'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alt Başlık</label>
                                <textarea name="translations[<?= $lang['code'] ?>][subtitle]" class="form-control" rows="2"><?= e($trans['subtitle'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Buton 1 Metni</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][button_text]" class="form-control" value="<?= e($trans['button_text'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Buton 1 URL</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][button_url]" class="form-control" value="<?= e($trans['button_url'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Buton 2 Metni</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][button2_text]" class="form-control" value="<?= e($trans['button2_text'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Buton 2 URL</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][button2_url]" class="form-control" value="<?= e($trans['button2_url'] ?? '') ?>">
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
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Slider Ayarları</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Görsel</label>
                        <input type="text" name="image" class="form-control" value="<?= e($editData['image'] ?? '') ?>" placeholder="img/slider.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Video</label>
                        <input type="text" name="video" class="form-control" value="<?= e($editData['video'] ?? '') ?>" placeholder="img/travel/video.mp4">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konum</label>
                        <select name="location" class="form-select">
                            <option value="home" <?= ($editData['location'] ?? 'home') === 'home' ? 'selected' : '' ?>>Ana Sayfa</option>
                            <option value="tours" <?= ($editData['location'] ?? '') === 'tours' ? 'selected' : '' ?>>Turlar</option>
                            <option value="destinations" <?= ($editData['location'] ?? '') === 'destinations' ? 'selected' : '' ?>>Destinasyonlar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metin Pozisyonu</label>
                        <select name="text_position" class="form-select">
                            <option value="left" <?= ($editData['text_position'] ?? 'left') === 'left' ? 'selected' : '' ?>>Sol</option>
                            <option value="center" <?= ($editData['text_position'] ?? '') === 'center' ? 'selected' : '' ?>>Orta</option>
                            <option value="right" <?= ($editData['text_position'] ?? '') === 'right' ? 'selected' : '' ?>>Sağ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                <a href="<?= ADMIN_URL ?>/sliders.php" class="btn btn-outline-secondary">İptal</a>
            </div>
        </div>
    </div>
</form>
<?php else: ?>
<div class="card table-card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th width="60">Sıra</th>
                    <th>Başlık</th>
                    <th>Konum</th>
                    <th width="80">Durum</th>
                    <th width="120">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sliders as $slider): ?>
                <tr>
                    <td><?= (int)$slider['sort_order'] ?></td>
                    <td><?= e($slider['title'] ?: '(Başlıksız)') ?></td>
                    <td><?= e(ucfirst($slider['location'])) ?></td>
                    <td><span class="badge bg-<?= $slider['is_active'] ? 'success' : 'secondary' ?>"><?= $slider['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $slider['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="?action=delete&id=<?= $slider['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
