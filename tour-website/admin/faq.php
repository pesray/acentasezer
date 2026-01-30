<?php
/**
 * SSS Yönetimi
 */

$pageTitle = 'SSS Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

$categories = $db->query("SELECT * FROM faq_categories ORDER BY sort_order")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    
    $translations = $_POST['translations'] ?? [];
    $defaultQuestion = $translations[$defaultLang['code']]['question'] ?? '';
    $defaultAnswer = $translations[$defaultLang['code']]['answer'] ?? '';
    
    if (empty($defaultQuestion) || empty($defaultAnswer)) {
        setFlashMessage('error', 'Soru ve cevap gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            if ($id) {
                $stmt = $db->prepare("UPDATE faqs SET category_id = ?, question = ?, answer = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$categoryId, $defaultQuestion, $defaultAnswer, $isActive, $sortOrder, $id]);
                $faqId = $id;
            } else {
                $stmt = $db->prepare("INSERT INTO faqs (category_id, question, answer, is_active, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$categoryId, $defaultQuestion, $defaultAnswer, $isActive, $sortOrder]);
                $faqId = $db->lastInsertId();
            }
            
            foreach ($translations as $langCode => $trans) {
                if ($langCode === $defaultLang['code']) continue;
                if (empty($trans['question'])) continue;
                
                $stmt = $db->prepare("INSERT INTO faq_translations (faq_id, language_code, question, answer) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE question = VALUES(question), answer = VALUES(answer)");
                $stmt->execute([$faqId, $langCode, $trans['question'], $trans['answer'] ?? '']);
            }
            
            $db->commit();
            setFlashMessage('success', $id ? 'SSS güncellendi.' : 'SSS eklendi.');
            header('Location: ' . ADMIN_URL . '/faq.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM faqs WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'SSS silindi.');
    header('Location: ' . ADMIN_URL . '/faq.php');
    exit;
}

$editData = null;
$editTranslations = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM faq_translations WHERE faq_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) { $editTranslations[$row['language_code']] = $row; }
}

$faqs = $db->query("SELECT f.*, fc.name as category_name FROM faqs f LEFT JOIN faq_categories fc ON f.category_id = fc.id ORDER BY f.category_id, f.sort_order")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">SSS Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni SSS</a>
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
                                <label class="form-label">Soru</label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][question]" class="form-control" value="<?= e($trans['question'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cevap</label>
                                <textarea name="translations[<?= $lang['code'] ?>][answer]" class="form-control" rows="5"><?= e($trans['answer'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">SSS Ayarları</h6></div>
                <div class="card-body">
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
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                <a href="<?= ADMIN_URL ?>/faq.php" class="btn btn-outline-secondary">İptal</a>
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
                    <th>Soru</th>
                    <th>Kategori</th>
                    <th width="80">Durum</th>
                    <th width="120">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faqs as $faq): ?>
                <tr>
                    <td><?= (int)$faq['sort_order'] ?></td>
                    <td><?= e(mb_substr($faq['question'], 0, 80)) ?>...</td>
                    <td><?= e($faq['category_name'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= $faq['is_active'] ? 'success' : 'secondary' ?>"><?= $faq['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $faq['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="?action=delete&id=<?= $faq['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
