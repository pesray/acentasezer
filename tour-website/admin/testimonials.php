<?php
/**
 * Testimonial (Müşteri Yorumları) Yönetimi
 */

$pageTitle = 'Müşteri Yorumları';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerImage = trim($_POST['customer_image'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isApproved = isset($_POST['is_approved']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    $translations = $_POST['translations'] ?? [];
    $defaultContent = $translations[$defaultLang['code']]['content'] ?? '';
    $defaultTitle = $translations[$defaultLang['code']]['customer_title'] ?? '';
    
    if (empty($customerName) || empty($defaultContent)) {
        setFlashMessage('error', 'Müşteri adı ve yorum içeriği gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            if ($id) {
                $stmt = $db->prepare("UPDATE testimonials SET customer_name = ?, customer_title = ?, customer_image = ?, content = ?, rating = ?, is_featured = ?, is_approved = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$customerName, $defaultTitle, $customerImage, $defaultContent, $rating, $isFeatured, $isApproved, $sortOrder, $id]);
                $testId = $id;
            } else {
                $stmt = $db->prepare("INSERT INTO testimonials (customer_name, customer_title, customer_image, content, rating, is_featured, is_approved, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$customerName, $defaultTitle, $customerImage, $defaultContent, $rating, $isFeatured, $isApproved, $sortOrder]);
                $testId = $db->lastInsertId();
            }
            
            foreach ($translations as $langCode => $trans) {
                if ($langCode === $defaultLang['code']) continue;
                if (empty($trans['content'])) continue;
                
                $stmt = $db->prepare("INSERT INTO testimonial_translations (testimonial_id, language_code, content, customer_title) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content = VALUES(content), customer_title = VALUES(customer_title)");
                $stmt->execute([$testId, $langCode, $trans['content'], $trans['customer_title'] ?? '']);
            }
            
            $db->commit();
            setFlashMessage('success', $id ? 'Yorum güncellendi.' : 'Yorum eklendi.');
            header('Location: ' . ADMIN_URL . '/testimonials.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Yorum silindi.');
    header('Location: ' . ADMIN_URL . '/testimonials.php');
    exit;
}

$editData = null;
$editTranslations = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM testimonial_translations WHERE testimonial_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) { $editTranslations[$row['language_code']] = $row; }
}

$testimonials = $db->query("SELECT * FROM testimonials ORDER BY sort_order, created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Müşteri Yorumları</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni Yorum</a>
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
                        <li class="nav-item"><button type="button" class="nav-link <?= $i === 0 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#lang-<?= $lang['code'] ?>"><?= e($lang['flag']) ?> <?= e($lang['native_name']) ?></button></li>
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
                                <label class="form-label">Yorum İçeriği</label>
                                <textarea name="translations[<?= $lang['code'] ?>][content]" class="form-control" rows="4"><?= e($trans['content'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Müşteri Ünvanı</label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][customer_title]" class="form-control" value="<?= e($trans['customer_title'] ?? '') ?>" placeholder="CEO, Designer...">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Yorum Ayarları</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Müşteri Adı <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control" value="<?= e($editData['customer_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Müşteri Fotoğrafı</label>
                        <input type="text" name="customer_image" class="form-control" value="<?= e($editData['customer_image'] ?? '') ?>" placeholder="img/person.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Puan</label>
                        <select name="rating" class="form-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= ($editData['rating'] ?? 5) == $i ? 'selected' : '' ?>><?= $i ?> Yıldız</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_approved" class="form-check-input" id="is_approved" value="1" <?= ($editData['is_approved'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_approved">Onaylı</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?= ($editData['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                <a href="<?= ADMIN_URL ?>/testimonials.php" class="btn btn-outline-secondary">İptal</a>
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
                    <th>Müşteri</th>
                    <th>Yorum</th>
                    <th width="80">Puan</th>
                    <th width="80">Durum</th>
                    <th width="120">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td><?= (int)$t['sort_order'] ?></td>
                    <td>
                        <strong><?= e($t['customer_name']) ?></strong>
                        <?php if ($t['is_featured']): ?><span class="badge bg-warning text-dark ms-1">Öne Çıkan</span><?php endif; ?>
                        <small class="d-block text-muted"><?= e($t['customer_title']) ?></small>
                    </td>
                    <td><?= e(mb_substr($t['content'], 0, 80)) ?>...</td>
                    <td><?php for ($i = 0; $i < $t['rating']; $i++) echo '<i class="bi bi-star-fill text-warning"></i>'; ?></td>
                    <td><span class="badge bg-<?= $t['is_approved'] ? 'success' : 'secondary' ?>"><?= $t['is_approved'] ? 'Onaylı' : 'Bekliyor' ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="?action=delete&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
