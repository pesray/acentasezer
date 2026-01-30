<?php
/**
 * Blog Yönetimi
 */

$pageTitle = 'Blog Yazıları';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$languages = $db->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$defaultLang = array_filter($languages, fn($l) => $l['is_default']);
$defaultLang = reset($defaultLang) ?: $languages[0] ?? ['code' => 'tr'];

$categories = $db->query("SELECT * FROM blog_categories ORDER BY sort_order")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $featuredImage = trim($_POST['featured_image'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    $authorId = $_SESSION['admin_id'];
    
    $translations = $_POST['translations'] ?? [];
    $defaultTitle = $translations[$defaultLang['code']]['title'] ?? '';
    $defaultSlug = $translations[$defaultLang['code']]['slug'] ?? '';
    
    if (empty($defaultTitle) || empty($defaultSlug)) {
        setFlashMessage('error', 'Başlık ve slug gereklidir.');
    } else {
        try {
            $db->beginTransaction();
            
            $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
            
            if ($id) {
                $stmt = $db->prepare("UPDATE blog_posts SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, category_id = ?, is_featured = ?, meta_title = ?, meta_description = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['excerpt'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $featuredImage, $categoryId, $isFeatured,
                    $translations[$defaultLang['code']]['meta_title'] ?? '',
                    $translations[$defaultLang['code']]['meta_description'] ?? '',
                    $status, $id
                ]);
                $postId = $id;
            } else {
                $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category_id, author_id, is_featured, meta_title, meta_description, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $defaultTitle, $defaultSlug,
                    $translations[$defaultLang['code']]['excerpt'] ?? '',
                    $translations[$defaultLang['code']]['content'] ?? '',
                    $featuredImage, $categoryId, $authorId, $isFeatured,
                    $translations[$defaultLang['code']]['meta_title'] ?? '',
                    $translations[$defaultLang['code']]['meta_description'] ?? '',
                    $status, $publishedAt
                ]);
                $postId = $db->lastInsertId();
            }
            
            foreach ($translations as $langCode => $trans) {
                if ($langCode === $defaultLang['code']) continue;
                if (empty($trans['title'])) continue;
                
                $stmt = $db->prepare("INSERT INTO blog_post_translations (post_id, language_code, title, slug, excerpt, content, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), excerpt = VALUES(excerpt), content = VALUES(content), meta_title = VALUES(meta_title), meta_description = VALUES(meta_description)");
                $stmt->execute([$postId, $langCode, $trans['title'], $trans['slug'] ?? '', $trans['excerpt'] ?? '', $trans['content'] ?? '', $trans['meta_title'] ?? '', $trans['meta_description'] ?? '']);
            }
            
            $db->commit();
            setFlashMessage('success', $id ? 'Yazı güncellendi.' : 'Yazı eklendi.');
            header('Location: ' . ADMIN_URL . '/blog.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Yazı silindi.');
    header('Location: ' . ADMIN_URL . '/blog.php');
    exit;
}

$editData = null;
$editTranslations = [];
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM blog_post_translations WHERE post_id = ?");
    $stmt->execute([$id]);
    while ($row = $stmt->fetch()) { $editTranslations[$row['language_code']] = $row; }
}

$posts = $db->query("SELECT bp.*, bc.name as category_name, u.full_name as author_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id = bc.id LEFT JOIN users u ON bp.author_id = u.id ORDER BY bp.created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Blog Yazıları</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni Yazı</a>
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
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Başlık</label>
                                    <input type="text" name="translations[<?= $lang['code'] ?>][meta_title]" class="form-control" value="<?= e($trans['meta_title'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Meta Açıklama</label>
                                    <textarea name="translations[<?= $lang['code'] ?>][meta_description]" class="form-control" rows="2"><?= e($trans['meta_description'] ?? '') ?></textarea>
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
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Yazı Ayarları</h6></div>
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
                            <option value="<?= $cat['id'] ?>" <?= ($editData['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Öne Çıkan Görsel</label>
                        <input type="text" name="featured_image" class="form-control" value="<?= e($editData['featured_image'] ?? '') ?>">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?= ($editData['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Öne Çıkan</label>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                <a href="<?= ADMIN_URL ?>/blog.php" class="btn btn-outline-secondary">İptal</a>
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
                    <th>Başlık</th>
                    <th>Kategori</th>
                    <th>Yazar</th>
                    <th width="80">Durum</th>
                    <th width="120">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td>
                        <strong><?= e($post['title']) ?></strong>
                        <?php if ($post['is_featured']): ?><span class="badge bg-warning text-dark ms-1">Öne Çıkan</span><?php endif; ?>
                    </td>
                    <td><?= e($post['category_name'] ?? '-') ?></td>
                    <td><?= e($post['author_name'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= $post['status'] === 'published' ? 'success' : 'secondary' ?>"><?= $post['status'] === 'published' ? 'Yayında' : 'Taslak' ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="?action=delete&id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
