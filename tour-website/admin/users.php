<?php
$pageTitle = 'Kullanıcılar';
require_once __DIR__ . '/includes/header.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'editor';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($username) || empty($email)) {
        setFlashMessage('error', 'Kullanıcı adı ve e-posta gereklidir.');
    } else {
        try {
            if ($id) {
                $sql = "UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, is_active = ?";
                $params = [$username, $email, $fullName, $role, $isActive];
                if (!empty($password)) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= " WHERE id = ?";
                $params[] = $id;
                $db->prepare($sql)->execute($params);
            } else {
                if (empty($password)) {
                    setFlashMessage('error', 'Şifre gereklidir.');
                } else {
                    $db->prepare("INSERT INTO users (username, email, full_name, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)")
                       ->execute([$username, $email, $fullName, password_hash($password, PASSWORD_DEFAULT), $role, $isActive]);
                }
            }
            setFlashMessage('success', 'Kullanıcı kaydedildi.');
            header('Location: ' . ADMIN_URL . '/users.php');
            exit;
        } catch (Exception $e) {
            setFlashMessage('error', 'Hata: ' . $e->getMessage());
        }
    }
}

if ($action === 'delete' && $id && $id != $_SESSION['admin_id']) {
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Kullanıcı silindi.');
    header('Location: ' . ADMIN_URL . '/users.php');
    exit;
}

$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Kullanıcılar</h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni Kullanıcı</a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="row"><div class="col-lg-6">
<div class="card">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><?= $editData ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı' ?></h6></div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Kullanıcı Adı <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" value="<?= e($editData['username'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-posta <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($editData['email'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ad Soyad</label>
                <input type="text" name="full_name" class="form-control" value="<?= e($editData['full_name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Şifre <?= $editData ? '(boş bırakırsanız değişmez)' : '<span class="text-danger">*</span>' ?></label>
                <input type="password" name="password" class="form-control" <?= $editData ? '' : 'required' ?>>
            </div>
            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select name="role" class="form-select">
                    <option value="author" <?= ($editData['role'] ?? '') === 'author' ? 'selected' : '' ?>>Yazar</option>
                    <option value="editor" <?= ($editData['role'] ?? 'editor') === 'editor' ? 'selected' : '' ?>>Editör</option>
                    <option value="admin" <?= ($editData['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" <?= ($editData['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Aktif</label>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
            <a href="<?= ADMIN_URL ?>/users.php" class="btn btn-outline-secondary">İptal</a>
        </form>
    </div>
</div>
</div></div>
<?php else: ?>
<div class="card table-card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead><tr><th>Kullanıcı</th><th>E-posta</th><th>Rol</th><th>Son Giriş</th><th width="80">Durum</th><th width="120">İşlem</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= e($u['username']) ?></strong><small class="d-block text-muted"><?= e($u['full_name']) ?></small></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'editor' ? 'primary' : 'secondary') ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['last_login'] ? date('d.m.Y H:i', strtotime($u['last_login'])) : '-' ?></td>
                    <td><span class="badge bg-<?= $u['is_active'] ? 'success' : 'secondary' ?>"><?= $u['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-delete data-entity="users" data-id="<?= $u['id'] ?>"><i class="bi bi-trash"></i></button>
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
