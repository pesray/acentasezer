<?php
/**
 * Site Ayarları
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireAdmin();

$db = getDB();

// Form işlemleri — header.php'den önce, redirect çalışsın
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            // Resim yükleme kontrolü
            if (
                isset($_FILES['settings_files']['error'][$key]) &&
                $_FILES['settings_files']['error'][$key] === UPLOAD_ERR_OK
            ) {
                $tmpName  = $_FILES['settings_files']['tmp_name'][$key];
                $origName = $_FILES['settings_files']['name'][$key];
                $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                if (in_array($ext, $allowed) && is_uploaded_file($tmpName)) {
                    $filename = $key . '_' . time() . '.' . $ext;
                    if (!is_dir(UPLOADS_PATH)) mkdir(UPLOADS_PATH, 0755, true);
                    if (move_uploaded_file($tmpName, UPLOADS_PATH . $filename)) {
                        $value = $filename;
                    }
                }
            }

            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([trim($value), $key]);
        }
        setFlashMessage('success', 'Ayarlar kaydedildi.');
        header('Location: ' . ADMIN_URL . '/settings.php');
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', 'Hata: ' . $e->getMessage());
    }
}

// Ayarları grupla
$settings = [];
$stmt = $db->query("SELECT * FROM settings ORDER BY setting_group, id");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_group']][] = $row;
}

$groupNames = [
    'general' => 'Genel Ayarlar',
    'contact' => 'İletişim Bilgileri',
    'social'  => 'Sosyal Medya',
    'seo'     => 'SEO Ayarları',
];

$pageTitle = 'Site Ayarları';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-gear me-2"></i>Site Ayarları</h1>
</div>

<form method="post" action="" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <?php foreach ($settings as $group => $items): ?>
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><?= e($groupNames[$group] ?? ucfirst($group)) ?></h6>
                </div>
                <div class="card-body">
                    <?php foreach ($items as $setting): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?= e(ucwords(str_replace('_', ' ', $setting['setting_key']))) ?></label>

                        <?php if ($setting['setting_type'] === 'textarea'): ?>
                            <textarea name="settings[<?= e($setting['setting_key']) ?>]" class="form-control" rows="3"><?= e($setting['setting_value']) ?></textarea>

                        <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                            <select name="settings[<?= e($setting['setting_key']) ?>]" class="form-select">
                                <option value="1" <?= $setting['setting_value'] ? 'selected' : '' ?>>Evet</option>
                                <option value="0" <?= !$setting['setting_value'] ? 'selected' : '' ?>>Hayır</option>
                            </select>

                        <?php elseif ($setting['setting_type'] === 'image'): ?>
                            <?php if ($setting['setting_value']): ?>
                            <div class="mb-2 p-2 bg-light rounded d-inline-block">
                                <img src="<?= UPLOADS_URL . e($setting['setting_value']) ?>"
                                     alt="<?= e($setting['setting_key']) ?>"
                                     style="max-height:64px; max-width:240px; object-fit:contain;">
                            </div>
                            <div class="mb-2">
                                <small class="text-muted"><i class="bi bi-file-image me-1"></i><?= e($setting['setting_value']) ?></small>
                            </div>
                            <?php endif; ?>
                            <input type="file"
                                   name="settings_files[<?= e($setting['setting_key']) ?>]"
                                   class="form-control"
                                   accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp">
                            <input type="hidden" name="settings[<?= e($setting['setting_key']) ?>]" value="<?= e($setting['setting_value']) ?>">
                            <small class="text-muted">JPG, PNG, SVG, WebP — Seçim yapılmazsa mevcut görsel korunur.</small>

                        <?php else: ?>
                            <input type="text"
                                   name="settings[<?= e($setting['setting_key']) ?>]"
                                   class="form-control"
                                   value="<?= e($setting['setting_value']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top:80px;">
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-1"></i>Ayarları Kaydet
                        </button>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Görsel alanları için yeni dosya seçilmezse mevcut görsel korunur.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
