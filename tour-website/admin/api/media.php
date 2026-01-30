<?php
/**
 * Medya API
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Yetkisiz erişim']);
    exit;
}

$db = getDB();

// GET - Medya detayı veya liste
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Tek medya detayı
    if (isset($_GET['id'])) {
        $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $media = $stmt->fetch();
        
        if ($media) {
            $media['file_size_formatted'] = formatFileSize($media['file_size']);
            echo json_encode(['success' => true, 'media' => $media]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Medya bulunamadı']);
        }
        exit;
    }
    
    // Medya listesi (modal için)
    $type = $_GET['type'] ?? 'all';
    $folder = $_GET['folder'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = ($page - 1) * $limit;
    
    $where = [];
    $params = [];
    
    if ($type !== 'all') {
        $where[] = "file_type = ?";
        $params[] = $type;
    }
    
    if ($folder !== 'all') {
        $where[] = "folder = ?";
        $params[] = $folder;
    }
    
    if ($search) {
        $where[] = "(original_name LIKE ? OR title LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Toplam sayı
    $countStmt = $db->prepare("SELECT COUNT(*) FROM media $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    // Liste
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $db->prepare("SELECT * FROM media $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute($params);
    $mediaList = $stmt->fetchAll();
    
    // URL ekle
    foreach ($mediaList as &$media) {
        $media['url'] = UPLOADS_URL . $media['file_path'];
        $media['file_size_formatted'] = formatFileSize($media['file_size']);
    }
    
    echo json_encode([
        'success' => true,
        'media' => $mediaList,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
    exit;
}

// POST - Medya güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID gerekli']);
        exit;
    }
    
    $title = $_POST['title'] ?? '';
    $altText = $_POST['alt_text'] ?? '';
    
    $stmt = $db->prepare("UPDATE media SET title = ?, alt_text = ? WHERE id = ?");
    $stmt->execute([$title, $altText, $id]);
    
    echo json_encode(['success' => true]);
    exit;
}

// DELETE - Medya sil
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID gerekli']);
        exit;
    }
    
    $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch();
    
    if ($media) {
        $filePath = UPLOADS_PATH . $media['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $db->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Medya bulunamadı']);
    }
    exit;
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}
