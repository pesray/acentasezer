<?php
/**
 * İletişim Mesajları Yönetimi
 */

$pageTitle = 'İletişim Mesajları';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Okundu olarak işaretle
if ($action === 'read' && $id) {
    $db->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?")->execute([$id]);
    header('Location: ' . ADMIN_URL . '/contacts.php?id=' . $id);
    exit;
}

// Sil
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM contacts WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Mesaj silindi.');
    header('Location: ' . ADMIN_URL . '/contacts.php');
    exit;
}

// Mesaj detayı
$message = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();
    
    if ($message && !$message['is_read']) {
        $db->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?")->execute([$id]);
        $message['is_read'] = 1;
    }
}

// Tüm mesajlar
$contacts = $db->query("SELECT * FROM contacts ORDER BY is_read ASC, created_at DESC")->fetchAll();
$unreadCount = $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        İletişim Mesajları
        <?php if ($unreadCount > 0): ?>
        <span class="badge bg-danger"><?= $unreadCount ?> yeni</span>
        <?php endif; ?>
    </h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Mesajlar</h6></div>
            <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                <?php foreach ($contacts as $c): ?>
                <a href="?id=<?= $c['id'] ?>" class="list-group-item list-group-item-action <?= $id == $c['id'] ? 'active' : '' ?> <?= !$c['is_read'] ? 'fw-bold' : '' ?>">
                    <div class="d-flex justify-content-between">
                        <span>
                            <?php if (!$c['is_read']): ?><i class="bi bi-circle-fill text-primary me-1" style="font-size: 8px;"></i><?php endif; ?>
                            <?= e($c['name']) ?>
                        </span>
                        <small class="text-muted"><?= date('d.m', strtotime($c['created_at'])) ?></small>
                    </div>
                    <small class="<?= $id == $c['id'] ? 'text-white-50' : 'text-muted' ?>"><?= e(mb_substr($c['subject'] ?? $c['message'], 0, 40)) ?>...</small>
                </a>
                <?php endforeach; ?>
                <?php if (empty($contacts)): ?>
                <div class="list-group-item text-center text-muted py-4">Henüz mesaj yok</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php if ($message): ?>
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><?= e($message['subject'] ?: 'Konu belirtilmemiş') ?></h6>
                <a href="?action=delete&id=<?= $message['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"><i class="bi bi-trash"></i> Sil</a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Gönderen:</strong> <?= e($message['name']) ?><br>
                        <strong>E-posta:</strong> <a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a><br>
                        <?php if ($message['phone']): ?>
                        <strong>Telefon:</strong> <a href="tel:<?= e($message['phone']) ?>"><?= e($message['phone']) ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <strong>Tarih:</strong> <?= date('d.m.Y H:i', strtotime($message['created_at'])) ?><br>
                        <strong>IP:</strong> <?= e($message['ip_address']) ?>
                    </div>
                </div>
                <hr>
                <div class="message-content">
                    <?= nl2br(e($message['message'])) ?>
                </div>
                <hr>
                <a href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e($message['subject']) ?>" class="btn btn-primary">
                    <i class="bi bi-reply me-1"></i> Yanıtla
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-envelope-open fs-1 mb-3 d-block"></i>
                Görüntülemek için bir mesaj seçin
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
