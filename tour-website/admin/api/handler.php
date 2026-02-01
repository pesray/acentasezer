<?php
/**
 * Admin API Handler - Tüm AJAX isteklerini yöneten merkezi dosya
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';

// AJAX istekleri için session kontrolü
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Oturum süresi dolmuş. Lütfen yeniden giriş yapın.']);
    exit;
}

// CSRF token kontrolü (POST istekleri için)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Güvenlik doğrulaması başarısız.']);
        exit;
    }
}

$db = getDB();
$action = $_REQUEST['action'] ?? '';
$entity = $_REQUEST['entity'] ?? '';

// Response helper
function jsonResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Entity ve action bazlı işlemler
try {
    switch ($entity) {
        case 'destinations':
            require_once __DIR__ . '/destinations.php';
            break;
        case 'tours':
            require_once __DIR__ . '/tours.php';
            break;
        case 'vehicles':
            require_once __DIR__ . '/vehicles.php';
            break;
        case 'bookings':
            require_once __DIR__ . '/bookings.php';
            break;
        case 'sections':
            require_once __DIR__ . '/sections.php';
            break;
        case 'menus':
            require_once __DIR__ . '/menus.php';
            break;
        case 'sliders':
            require_once __DIR__ . '/sliders.php';
            break;
        case 'languages':
            require_once __DIR__ . '/languages.php';
            break;
        case 'users':
            require_once __DIR__ . '/users.php';
            break;
        case 'media':
            require_once __DIR__ . '/media_api.php';
            break;
        default:
            jsonResponse(false, 'Geçersiz entity: ' . $entity);
    }
} catch (Exception $e) {
    http_response_code(500);
    jsonResponse(false, 'Sunucu hatası: ' . $e->getMessage());
}
