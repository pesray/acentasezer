<?php
/**
 * Dil Yönetimi
 */

$pageTitle = 'Dil Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $nativeName = trim($_POST['native_name'] ?? '');
    $flag = trim($_POST['flag'] ?? '');
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isRtl = isset($_POST['is_rtl']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    if (empty($code) || empty($name) || empty($nativeName)) {
        setFlashMessage('error', 'Dil kodu, adı ve yerel adı gereklidir.');
    } else {
        try {
            if ($id) {
                // Güncelle
                $stmt = $db->prepare("
                    UPDATE languages SET 
                        code = ?, name = ?, native_name = ?, flag = ?,
                        is_default = ?, is_active = ?, is_rtl = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([$code, $name, $nativeName, $flag, $isDefault, $isActive, $isRtl, $sortOrder, $id]);
                
                // Varsayılan dil değiştiyse diğerlerini güncelle
                if ($isDefault) {
                    $db->prepare("UPDATE languages SET is_default = 0 WHERE id != ?")->execute([$id]);
                }
                
                setFlashMessage('success', 'Dil güncellendi.');
            } else {
                // Ekle
                $stmt = $db->prepare("
                    INSERT INTO languages (code, name, native_name, flag, is_default, is_active, is_rtl, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$code, $name, $nativeName, $flag, $isDefault, $isActive, $isRtl, $sortOrder]);
                
                if ($isDefault) {
                    $newId = $db->lastInsertId();
                    $db->prepare("UPDATE languages SET is_default = 0 WHERE id != ?")->execute([$newId]);
                }
                
                setFlashMessage('success', 'Dil eklendi.');
            }
            header('Location: ' . ADMIN_URL . '/languages.php');
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

// Silme işlemi
if ($action === 'delete' && $id) {
    try {
        // Varsayılan dil silinemez
        $lang = $db->prepare("SELECT is_default FROM languages WHERE id = ?")->execute([$id]);
        $lang = $db->prepare("SELECT is_default FROM languages WHERE id = ?");
        $lang->execute([$id]);
        $langData = $lang->fetch();
        
        if ($langData && $langData['is_default']) {
            setFlashMessage('error', 'Varsayılan dil silinemez.');
        } else {
            $db->prepare("DELETE FROM languages WHERE id = ?")->execute([$id]);
            setFlashMessage('success', 'Dil silindi.');
        }
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/languages.php');
    exit;
}

// Düzenleme için veri al
$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM languages WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

// Tüm dilleri al
$languages = $db->query("SELECT * FROM languages ORDER BY sort_order")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Dil Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Yeni Dil Ekle
    </a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- Dil Formu -->
<div class="card">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><?= $action === 'edit' ? 'Dil Düzenle' : 'Yeni Dil Ekle' ?></h6>
    </div>
    <div class="card-body">
        <form method="post" action="?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Dil Kodu <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="<?= e($editData['code'] ?? '') ?>" 
                               placeholder="tr, en, de..." maxlength="5" required>
                        <small class="text-muted">ISO 639-1 kodu (örn: tr, en, de, fr)</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bayrak Emoji</label>
                        <input type="text" name="flag" class="form-control" value="<?= e($editData['flag'] ?? '') ?>" 
                               placeholder="🇹🇷" maxlength="10">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Dil Adı (İngilizce) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($editData['name'] ?? '') ?>" 
                               placeholder="Turkish" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Yerel Adı <span class="text-danger">*</span></label>
                        <input type="text" name="native_name" class="form-control" value="<?= e($editData['native_name'] ?? '') ?>" 
                               placeholder="Türkçe" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1"
                           <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Aktif</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="is_default" class="form-check-input" id="is_default" value="1"
                           <?= ($editData['is_default'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_default">Varsayılan Dil</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="is_rtl" class="form-check-input" id="is_rtl" value="1"
                           <?= ($editData['is_rtl'] ?? 0) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_rtl">Sağdan Sola (RTL)</label>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Kaydet
                </button>
                <a href="<?= ADMIN_URL ?>/languages.php" class="btn btn-outline-secondary">İptal</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- Dil Listesi -->
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="60">Sıra</th>
                        <th width="80">Bayrak</th>
                        <th>Kod</th>
                        <th>Dil Adı</th>
                        <th>Yerel Adı</th>
                        <th width="100">Varsayılan</th>
                        <th width="80">Durum</th>
                        <th width="120">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($languages as $lang): ?>
                    <tr>
                        <td><?= (int)$lang['sort_order'] ?></td>
                        <td class="fs-4"><?= e($lang['flag']) ?></td>
                        <td><code><?= e($lang['code']) ?></code></td>
                        <td><?= e($lang['name']) ?></td>
                        <td><?= e($lang['native_name']) ?></td>
                        <td>
                            <?php if ($lang['is_default']): ?>
                            <span class="badge bg-primary">Varsayılan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($lang['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?= $lang['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if (!$lang['is_default']): ?>
                            <a href="?action=delete&id=<?= $lang['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
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
