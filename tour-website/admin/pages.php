<?php
/**
 * Sayfa Yönetimi
 */

$pageTitle = 'Sayfa Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

$templates = [
    'default' => 'Varsayılan',
    'home' => 'Ana Sayfa',
    'about' => 'Hakkımızda',
    'contact' => 'İletişim',
    'faq' => 'SSS',
    'full-width' => 'Tam Genişlik'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $template = $_POST['template'] ?? 'default';
    $isHomepage = isset($_POST['is_homepage']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $authorId = $_SESSION['admin_id'];
    
    $translations = $_POST['translations'] ?? [];
    $defaultTitle = $translations[$defaultLang['code']]['title'] ?? '';
    $defaultSlug = $translations[$defaultLang['code']]['slug'] ?? '';
    
    if (empty($defaultTitle) || empty($defaultSlug)) {
        setFlashMessage('error', 'Başlık ve slug gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            if ($id) {
                $stmt = $db->prepare("
                    UPDATE pages SET title = ?, slug = ?, content = ?, excerpt = ?, template = ?,
                        meta_title = ?, meta_description = ?, meta_keywords = ?, status = ?, 
                        is_homepage = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $translations[$defaultLang['code']]['excerpt'] ?? '',
                    $template,
                    $translations[$defaultLang['code']]['meta_title'] ?? '',
                    $translations[$defaultLang['code']]['meta_description'] ?? '',
                    $translations[$defaultLang['code']]['meta_keywords'] ?? '',
                    $status, $isHomepage, $sortOrder, $id
                ]);
                $pageId = $id;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO pages (title, slug, content, excerpt, template, meta_title, meta_description, meta_keywords, status, is_homepage, sort_order, author_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $translations[$defaultLang['code']]['excerpt'] ?? '',
                    $template,
                    $translations[$defaultLang['code']]['meta_title'] ?? '',
                    $translations[$defaultLang['code']]['meta_description'] ?? '',
                    $translations[$defaultLang['code']]['meta_keywords'] ?? '',
                    $status, $isHomepage, $sortOrder, $authorId
                ]);
                $pageId = $db->lastInsertId();
            }
            
            if ($isHomepage) {
                $db->prepare("UPDATE pages SET is_homepage = 0 WHERE id != ?")->execute([$pageId]);
            }
            
            foreach ($translations as $langCode => $trans) {
                if ($langCode === $defaultLang['code']) continue;
                if (empty($trans['title'])) continue;
                
                $stmt = $db->prepare("
                    INSERT INTO page_translations (page_id, language_code, title, slug, content, excerpt, meta_title, meta_description, meta_keywords)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        title = VALUES(title), slug = VALUES(slug), content = VALUES(content),
                        excerpt = VALUES(excerpt), meta_title = VALUES(meta_title), 
                        meta_description = VALUES(meta_description), meta_keywords = VALUES(meta_keywords)
                ");
                $stmt->execute([
                    $pageId, $langCode, $trans['title'], $trans['slug'] ?? '',
                    $trans['content'] ?? '', $trans['excerpt'] ?? '',
                    $trans['meta_title'] ?? '', $trans['meta_description'] ?? '', $trans['meta_keywords'] ?? ''
                ]);
            }
            
            $db->commit();
            setFlashMessage('success', $id ? 'Sayfa güncellendi.' : 'Sayfa eklendi.');
            header('Location: ' . ADMIN_URL . '/pages.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $db->prepare("DELETE FROM pages WHERE id = ? AND is_homepage = 0")->execute([$id]);
        setFlashMessage('success', 'Sayfa silindi.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: ' . ADMIN_URL . '/pages.php');
    exit;
}

$editData = null;
$editTranslations = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM page_translations WHERE page_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) {
        $editTranslations[$row['language_code']] = $row;
    }
}

$pages = $db->query("SELECT * FROM pages ORDER BY sort_order, title")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Sayfa Yönetimi</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni Sayfa</a>
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
                            <button type="button" class="nav-link <?= $i === 0 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#lang-<?= $lang['code'] ?>">
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
                            if ($lang['is_default'] && $editData) {
                                $trans = $editData;
                            }
                        ?>
                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="lang-<?= $lang['code'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][title]" class="form-control" value="<?= e($trans['title'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="translations[<?= $lang['code'] ?>][slug]" class="form-control" value="<?= e($trans['slug'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Özet</label>
                                <textarea name="translations[<?= $lang['code'] ?>][excerpt]" class="form-control" rows="2"><?= e($trans['excerpt'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">İçerik</label>
                                <textarea name="translations[<?= $lang['code'] ?>][content]" class="form-control summernote"><?= e($trans['content'] ?? '') ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Meta Başlık</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][meta_title]" class="form-control" value="<?= e($trans['meta_title'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Meta Açıklama</label>
                                    <textarea name="translations[<?= $lang['code'] ?>][meta_description]" class="form-control" rows="2"><?= e($trans['meta_description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][meta_keywords]" class="form-control" value="<?= e($trans['meta_keywords'] ?? '') ?>">
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
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Sayfa Ayarları</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($editData['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Taslak</option>
                            <option value="published" <?= ($editData['status'] ?? '') === 'published' ? 'selected' : '' ?>>Yayında</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şablon</label>
                        <select name="template" class="form-select">
                            <?php foreach ($templates as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($editData['template'] ?? 'default') === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($editData['sort_order'] ?? 0) ?>">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_homepage" class="form-check-input" id="is_homepage" value="1" <?= ($editData['is_homepage'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_homepage">Ana Sayfa Olarak Ayarla</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                <a href="<?= ADMIN_URL ?>/pages.php" class="btn btn-outline-secondary">İptal</a>
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
                    <th>Slug</th>
                    <th>Şablon</th>
                    <th width="80">Durum</th>
                    <th width="120">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                <tr>
                    <td><?= (int)$page['sort_order'] ?></td>
                    <td>
                        <strong><?= e($page['title']) ?></strong>
                        <?php if ($page['is_homepage']): ?><span class="badge bg-primary ms-1">Ana Sayfa</span><?php endif; ?>
                    </td>
                    <td><code>/<?= e($page['slug']) ?></code></td>
                    <td><?= e($templates[$page['template']] ?? $page['template']) ?></td>
                    <td>
                        <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : 'secondary' ?>"><?= $page['status'] === 'published' ? 'Yayında' : 'Taslak' ?></span>
                    </td>
                    <td>
                        <a href="?action=edit&id=<?= $page['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if (!$page['is_homepage']): ?>
                        <a href="?action=delete&id=<?= $page['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
