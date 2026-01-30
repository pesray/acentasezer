<?php
/**
 * Admin Dashboard
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// İstatistikleri al
try {
    $db = getDB();
    
    // Toplam turlar
    $tourCount = $db->query("SELECT COUNT(*) FROM tours WHERE status = 'published'")->fetchColumn();
    
    // Toplam destinasyonlar
    $destCount = $db->query("SELECT COUNT(*) FROM destinations WHERE status = 'published'")->fetchColumn();
    
    // Bekleyen rezervasyonlar
    $pendingBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending'")->fetchColumn();
    
    // Okunmamış mesajlar
    $unreadMessages = $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    
    // Son rezervasyonlar
    $recentBookings = $db->query("
        SELECT b.*, t.title as tour_title 
        FROM bookings b 
        LEFT JOIN tours t ON b.tour_id = t.id 
        ORDER BY b.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Son mesajlar
    $recentMessages = $db->query("
        SELECT * FROM contacts 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
} catch (Exception $e) {
    $tourCount = $destCount = $pendingBookings = $unreadMessages = 0;
    $recentBookings = $recentMessages = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Dashboard</h1>
    <span class="text-muted"><?= date('d F Y, l') ?></span>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small mb-1">Toplam Tur</div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($tourCount) ?></div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-compass"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small mb-1">Destinasyonlar</div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($destCount) ?></div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small mb-1">Bekleyen Rezervasyon</div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($pendingBookings) ?></div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase text-muted small mb-1">Okunmamış Mesaj</div>
                        <div class="h4 mb-0 fw-bold"><?= number_format($unreadMessages) ?></div>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Son Rezervasyonlar -->
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Son Rezervasyonlar</h6>
                <a href="<?= ADMIN_URL ?>/bookings.php" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rezervasyon No</th>
                                <th>Müşteri</th>
                                <th>Tur</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBookings)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Henüz rezervasyon yok</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><strong><?= e($booking['booking_number']) ?></strong></td>
                                <td><?= e($booking['customer_name']) ?></td>
                                <td><?= e($booking['tour_title'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'completed' => 'info',
                                        'cancelled' => 'danger'
                                    ][$booking['booking_status']] ?? 'secondary';
                                    $statusText = [
                                        'pending' => 'Bekliyor',
                                        'confirmed' => 'Onaylandı',
                                        'completed' => 'Tamamlandı',
                                        'cancelled' => 'İptal'
                                    ][$booking['booking_status']] ?? $booking['booking_status'];
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($booking['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Son Mesajlar -->
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Son Mesajlar</h6>
                <a href="<?= ADMIN_URL ?>/contacts.php" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentMessages)): ?>
                <div class="text-center text-muted py-4">Henüz mesaj yok</div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentMessages as $message): ?>
                    <a href="<?= ADMIN_URL ?>/contacts.php?id=<?= $message['id'] ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">
                                    <?php if (!$message['is_read']): ?>
                                    <span class="badge bg-danger me-1">Yeni</span>
                                    <?php endif; ?>
                                    <?= e($message['name']) ?>
                                </div>
                                <small class="text-muted"><?= e(mb_substr($message['subject'] ?? $message['message'], 0, 50)) ?>...</small>
                            </div>
                            <small class="text-muted"><?= date('d.m', strtotime($message['created_at'])) ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
