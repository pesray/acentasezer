<?php
$pageTitle = 'Galeri';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$categories = $db->query("SELECT * FROM gallery_categories ORDER BY sort_order")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    if (empty($image)) {
        setFlashMessage('error', 'Görsel gereklidir.');
    } else {
        if ($id) {
            $db->prepare("UPDATE gallery SET title = ?, image = ?, category_id = ?, is_featured = ?, sort_order = ? WHERE id = ?")->execute([$title, $image, $categoryId, $isFeatured, $sortOrder, $id]);
        } else {
            $db->prepare("INSERT INTO gallery (title, image, category_id, is_featured, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([$title, $image, $categoryId, $isFeatured, $sortOrder]);
        }
        setFlashMessage('success', 'Görsel kaydedildi.');
        header('Location: ' . ADMIN_URL . '/gallery.php');
        exit;
    }
}

if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Görsel silindi.');
    header('Location: ' . ADMIN_URL . '/gallery.php');
    exit;
}

$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

$gallery = $db->query("SELECT g.*, gc.name as category_name FROM gallery g LEFT JOIN gallery_categories gc ON g.category_id = gc.id ORDER BY g.sort_order")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Galeri</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni Görsel</a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><?= $editData ? 'Görsel Düzenle' : 'Yeni Görsel' ?></h6></div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" name="title" class="form-control" value="<?= e($editData['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Görsel Yolu <span class="text-danger">*</span></label>
                        <input type="text" name="image" class="form-control" value="<?= e($editData['image'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Seçiniz</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($editData['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?= ($editData['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                    <a href="<?= ADMIN_URL ?>/gallery.php" class="btn btn-outline-secondary">İptal</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($gallery as $item): ?>
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="card h-100">
            <img src="<?= UPLOADS_URL . e($item['image']) ?>" class="card-img-top" alt="<?= e($item['title']) ?>" style="height: 150px; object-fit: cover;">
            <div class="card-body p-2">
                <small class="text-muted"><?= e($item['category_name'] ?? 'Kategorisiz') ?></small>
                <div class="mt-2">
                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="?action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
