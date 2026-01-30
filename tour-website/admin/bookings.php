<?php
/**
 * Rezervasyon Yönetimi
 */

$pageTitle = 'Rezervasyonlar';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$statusLabels = [
    'pending' => ['Bekliyor', 'warning'],
    'confirmed' => ['Onaylandı', 'success'],
    'completed' => ['Tamamlandı', 'info'],
    'cancelled' => ['İptal', 'danger']
];

// Durum güncelle
if ($action === 'status' && $id && isset($_GET['status'])) {
    $newStatus = $_GET['status'];
    if (array_key_exists($newStatus, $statusLabels)) {
        $db->prepare("UPDATE bookings SET booking_status = ? WHERE id = ?")->execute([$newStatus, $id]);
        setFlashMessage('success', 'Rezervasyon durumu güncellendi.');
    }
    header('Location: ' . ADMIN_URL . '/bookings.php?id=' . $id);
    exit;
}

// Sil
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM bookings WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Rezervasyon silindi.');
    header('Location: ' . ADMIN_URL . '/bookings.php');
    exit;
}

// Detay
$booking = null;
if ($id) {
    $stmt = $db->prepare("SELECT b.*, t.title as tour_title FROM bookings b LEFT JOIN tours t ON b.tour_id = t.id WHERE b.id = ?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();
}

// Tüm rezervasyonlar
$bookings = $db->query("
    SELECT b.*, t.title as tour_title 
    FROM bookings b 
    LEFT JOIN tours t ON b.tour_id = t.id 
    ORDER BY b.created_at DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Rezervasyonlar</h1>
</div>

<?php if ($booking): ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Rezervasyon #<?= e($booking['booking_number']) ?></h6>
                <span class="badge bg-<?= $statusLabels[$booking['booking_status']][1] ?> fs-6">
                    <?= $statusLabels[$booking['booking_status']][0] ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Müşteri Bilgileri</h6>
                        <p><strong>Ad Soyad:</strong> <?= e($booking['customer_name']) ?></p>
                        <p><strong>E-posta:</strong> <a href="mailto:<?= e($booking['customer_email']) ?>"><?= e($booking['customer_email']) ?></a></p>
                        <p><strong>Telefon:</strong> <?= e($booking['customer_phone'] ?: '-') ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Tur Bilgileri</h6>
                        <p><strong>Tur:</strong> <?= e($booking['tour_title'] ?? '-') ?></p>
                        <p><strong>Tarih:</strong> <?= $booking['departure_date'] ? date('d.m.Y', strtotime($booking['departure_date'])) : '-' ?></p>
                        <p><strong>Kişi:</strong> <?= (int)$booking['adults'] ?> Yetişkin, <?= (int)$booking['children'] ?> Çocuk</p>
                    </div>
                </div>
                
                <?php if ($booking['special_requests']): ?>
                <hr>
                <h6 class="text-muted mb-2">Özel İstekler</h6>
                <p><?= nl2br(e($booking['special_requests'])) ?></p>
                <?php endif; ?>
                
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Toplam Tutar:</strong> <span class="fs-4 text-success"><?= $booking['currency'] ?><?= number_format($booking['total_price'], 2) ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Oluşturulma:</strong> <?= date('d.m.Y H:i', strtotime($booking['created_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="<?= ADMIN_URL ?>/bookings.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Listeye Dön</a>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Durum Değiştir</h6></div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php foreach ($statusLabels as $key => $val): ?>
                    <a href="?action=status&id=<?= $booking['id'] ?>&status=<?= $key ?>" 
                       class="btn btn-<?= $booking['booking_status'] === $key ? '' : 'outline-' ?><?= $val[1] ?>">
                        <?= $val[0] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <a href="?action=delete&id=<?= $booking['id'] ?>" class="btn btn-outline-danger w-100 btn-delete">
                    <i class="bi bi-trash me-1"></i> Rezervasyonu Sil
                </a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="card table-card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Rezervasyon No</th>
                    <th>Müşteri</th>
                    <th>Tur</th>
                    <th>Tarih</th>
                    <th>Tutar</th>
                    <th width="100">Durum</th>
                    <th width="80">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><strong><?= e($b['booking_number']) ?></strong></td>
                    <td>
                        <?= e($b['customer_name']) ?>
                        <small class="d-block text-muted"><?= e($b['customer_email']) ?></small>
                    </td>
                    <td><?= e($b['tour_title'] ?? '-') ?></td>
                    <td><?= $b['departure_date'] ? date('d.m.Y', strtotime($b['departure_date'])) : '-' ?></td>
                    <td><?= $b['currency'] ?><?= number_format($b['total_price'], 0) ?></td>
                    <td><span class="badge bg-<?= $statusLabels[$b['booking_status']][1] ?>"><?= $statusLabels[$b['booking_status']][0] ?></span></td>
                    <td><a href="?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
