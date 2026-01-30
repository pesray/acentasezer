<?php
/**
 * Menü Yönetimi
 */

$pageTitle = 'Menü Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$menuId = $_GET['menu_id'] ?? null;
$itemId = $_GET['item_id'] ?? null;

$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

// Menü öğesi kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_item') {
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $target = $_POST['target'] ?? '_self';
    $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $translations = $_POST['translations'] ?? [];
    
    if (empty($title)) {
        setFlashMessage('error', 'Menü öğesi başlığı gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            if ($itemId) {
                $stmt = $db->prepare("UPDATE menu_items SET title = ?, url = ?, target = ?, parent_id = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $url, $target, $parentId, $sortOrder, $isActive, $itemId]);
            } else {
                $stmt = $db->prepare("INSERT INTO menu_items (menu_id, title, url, target, parent_id, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$menuId, $title, $url, $target, $parentId, $sortOrder, $isActive]);
                $itemId = $db->lastInsertId();
            }
            
            // Çevirileri kaydet
            foreach ($translations as $langCode => $trans) {
                if (empty($trans['title'])) continue;
                $stmt = $db->prepare("
                    INSERT INTO menu_item_translations (menu_item_id, language_code, title, url)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE title = VALUES(title), url = VALUES(url)
                ");
                $stmt->execute([$itemId, $langCode, $trans['title'], $trans['url'] ?? '']);
            }
            
            $db->commit();
            setFlashMessage('success', 'Menü öğesi kaydedildi.');
            header('Location: ' . ADMIN_URL . '/menus.php?action=items&menu_id=' . $menuId);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

// Menü öğesi sil
if ($action === 'delete_item' && $itemId) {
    try {
        $stmt = $db->prepare("SELECT menu_id FROM menu_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        $db->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$itemId]);
        setFlashMessage('success', 'Menü öğesi silindi.');
        header('Location: ' . ADMIN_URL . '/menus.php?action=items&menu_id=' . $item['menu_id']);
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
}

// Menüler
$menus = $db->query("SELECT * FROM menus ORDER BY id")->fetchAll();

// Menü öğeleri
$menuItems = [];
if ($menuId) {
    $stmt = $db->prepare("SELECT * FROM menu_items WHERE menu_id = ? ORDER BY sort_order");
    $stmt->execute([$menuId]);
    $menuItems = $stmt->fetchAll();
}

// Düzenleme için veri
$editItem = null;
$editItemTranslations = [];
if ($action === 'edit_item' && $itemId) {
    $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$itemId]);
    $editItem = $stmt->fetch();
    $menuId = $editItem['menu_id'];
    
    $stmt = $db->prepare("SELECT * FROM menu_item_translations WHERE menu_item_id = ?");
    $stmt->execute([$itemId]);
    while ($row = $stmt->fetch()) {
        $editItemTranslations[$row['language_code']] = $row;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Menü Yönetimi</h1>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Menüler</h6></div>
            <div class="list-group list-group-flush">
                <?php foreach ($menus as $menu): ?>
                <a href="?action=items&menu_id=<?= $menu['id'] ?>" class="list-group-item list-group-item-action <?= $menuId == $menu['id'] ? 'active' : '' ?>">
                    <i class="bi bi-list me-2"></i> <?= e($menu['name']) ?>
                    <small class="d-block text-muted"><?= e($menu['location']) ?></small>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <?php if ($action === 'items' && $menuId): ?>
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Menü Öğeleri</h6>
                <a href="?action=add_item&menu_id=<?= $menuId ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Yeni Öğe</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Sıra</th>
                            <th>Başlık</th>
                            <th>URL</th>
                            <th width="80">Durum</th>
                            <th width="100">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menuItems as $item): ?>
                        <tr>
                            <td><?= (int)$item['sort_order'] ?></td>
                            <td>
                                <?php if ($item['parent_id']): ?><span class="text-muted">└─</span> <?php endif; ?>
                                <?= e($item['title']) ?>
                            </td>
                            <td><code><?= e($item['url']) ?></code></td>
                            <td><span class="badge bg-<?= $item['is_active'] ? 'success' : 'secondary' ?>"><?= $item['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                            <td>
                                <a href="?action=edit_item&menu_id=<?= $menuId ?>&item_id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <a href="?action=delete_item&item_id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($menuItems)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Henüz menü öğesi yok</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php elseif (($action === 'add_item' || $action === 'edit_item') && $menuId): ?>
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><?= $editItem ? 'Menü Öğesi Düzenle' : 'Yeni Menü Öğesi' ?></h6>
            </div>
            <div class="card-body">
                <form method="post" action="?action=edit_item&menu_id=<?= $menuId ?><?= $itemId ? '&item_id=' . $itemId : '' ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Başlık (Varsayılan) <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= e($editItem['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" name="url" class="form-control" value="<?= e($editItem['url'] ?? '') ?>" placeholder="/sayfa-adi">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Üst Menü</label>
                            <select name="parent_id" class="form-select">
                                <option value="">Ana Menü</option>
                                <?php foreach ($menuItems as $mi): if ($mi['id'] == $itemId) continue; ?>
                                <option value="<?= $mi['id'] ?>" <?= ($editItem['parent_id'] ?? '') == $mi['id'] ? 'selected' : '' ?>><?= e($mi['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hedef</label>
                            <select name="target" class="form-select">
                                <option value="_self" <?= ($editItem['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>Aynı Pencere</option>
                                <option value="_blank" <?= ($editItem['target'] ?? '') === '_blank' ? 'selected' : '' ?>>Yeni Pencere</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sıralama</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= (int)($editItem['sort_order'] ?? 0) ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Dil Çevirileri</h6>
                    <?php foreach ($languages as $lang): if ($lang['is_default']) continue; ?>
                    <div class="row mb-2">
                        <div class="col-md-1 pt-2"><?= e($lang['flag']) ?></div>
                        <div class="col-md-5">
                            <input type="text" name="translations[<?= $lang['code'] ?>][title]" class="form-control form-control-sm" 
                                   value="<?= e($editItemTranslations[$lang['code']]['title'] ?? '') ?>" placeholder="Başlık">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="translations[<?= $lang['code'] ?>][url]" class="form-control form-control-sm" 
                                   value="<?= e($editItemTranslations[$lang['code']]['url'] ?? '') ?>" placeholder="URL (opsiyonel)">
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                        <a href="?action=items&menu_id=<?= $menuId ?>" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-arrow-left-circle fs-1 mb-3 d-block"></i>
                Düzenlemek için sol taraftan bir menü seçin
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
