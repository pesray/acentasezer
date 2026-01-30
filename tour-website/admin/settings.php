<?php
/**
 * Site Ayarları
 */

$pageTitle = 'Site Ayarları';
require_once __DIR__ . '/includes/header.php';
requireAdmin();

$db = getDB();

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
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
    'social' => 'Sosyal Medya',
    'seo' => 'SEO Ayarları'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Site Ayarları</h1>
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
                        <label class="form-label"><?= e(ucwords(str_replace('_', ' ', $setting['setting_key']))) ?></label>
                        <?php if ($setting['setting_type'] === 'textarea'): ?>
                        <textarea name="settings[<?= e($setting['setting_key']) ?>]" class="form-control" rows="3"><?= e($setting['setting_value']) ?></textarea>
                        <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                        <select name="settings[<?= e($setting['setting_key']) ?>]" class="form-select">
                            <option value="1" <?= $setting['setting_value'] ? 'selected' : '' ?>>Evet</option>
                            <option value="0" <?= !$setting['setting_value'] ? 'selected' : '' ?>>Hayır</option>
                        </select>
                        <?php elseif ($setting['setting_type'] === 'image'): ?>
                        <input type="text" name="settings[<?= e($setting['setting_key']) ?>]" class="form-control" 
                               value="<?= e($setting['setting_value']) ?>" placeholder="Görsel yolu">
                        <?php if ($setting['setting_value']): ?>
                        <img src="<?= UPLOADS_URL . e($setting['setting_value']) ?>" alt="" class="mt-2" style="max-height: 50px;">
                        <?php endif; ?>
                        <?php else: ?>
                        <input type="text" name="settings[<?= e($setting['setting_key']) ?>]" class="form-control" 
                               value="<?= e($setting['setting_value']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg me-1"></i> Ayarları Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
