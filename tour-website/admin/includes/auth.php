<?php
/**
 * Admin Authentication Helper
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

/**
 * Kullanıcı girişi yap
 */
function adminLogin($username, $password) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_name'] = $user['full_name'];
            
            // Son giriş zamanını güncelle
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Kullanıcı çıkışı
 */
function adminLogout() {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_role']);
    unset($_SESSION['admin_name']);
    session_destroy();
}

/**
 * Giriş kontrolü
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Yetki kontrolü
 */
function hasPermission($requiredRole = 'editor') {
    if (!isAdminLoggedIn()) return false;
    
    $roles = ['author' => 1, 'editor' => 2, 'admin' => 3];
    $userRole = $_SESSION['admin_role'] ?? 'author';
    
    return ($roles[$userRole] ?? 0) >= ($roles[$requiredRole] ?? 0);
}

/**
 * Giriş gerektir
 */
function requireLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Admin rolü gerektir
 */
function requireAdmin() {
    requireLogin();
    if (!hasPermission('admin')) {
        header('Location: ' . ADMIN_URL . '/index.php?error=permission');
        exit;
    }
}

/**
 * CSRF token oluştur
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token doğrula
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formlara eklenen gizli güvenlik anahtarı alanı: <?= csrfField() ?>
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCSRFToken()) . '">';
}

/**
 * Silme bağlantılarına eklenen güvenlik anahtarı: href="?action=delete&id=5&<?= csrfQuery() ?>"
 */
function csrfQuery() {
    return 'csrf_token=' . urlencode(generateCSRFToken());
}

/**
 * Sayfa koruması (CSRF) — admin sayfasının en başında, header.php'den ÖNCE çağrılır.
 * Bu sayfaya gelen her form gönderimi (POST) ve her silme bağlantısı
 * (?action=delete, ?action=delete_item, ?delete=ID) geçerli güvenlik anahtarı taşımalı.
 * Taşımıyorsa hiçbir şey kaydedilmez / silinmez; uyarıyla sayfaya geri dönülür.
 * Amaç: başka bir siteden, admin'in açık oturumu kullanılarak gizlice gönderilen istekleri durdurmak.
 */
function csrfProtect() {
    requireLogin();

    $isPost   = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    $isDelete = in_array($_GET['action'] ?? '', ['delete', 'delete_item'], true) || isset($_GET['delete']);
    if (!$isPost && !$isDelete) {
        return;
    }

    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? '';
    if (validateCSRFToken($token)) {
        return;
    }

    if ($isPost && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // post_max_size aşılınca PHP bütün alanları boşaltır; anahtar da gelmez
        setFlashMessage('error', 'Gönderilen veri çok büyük (dosya boyutu sınırı aşıldı), hiçbir şey kaydedilmedi.');
    } else {
        setFlashMessage('error', 'Güvenlik doğrulaması başarısız oldu, işlem yapılmadı. Sayfayı yenileyip tekrar deneyin.');
    }

    // Geri dönüş: form → aynı ekran (ör. düzenleme), silme bağlantısı → sayfanın listesi
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $page   = basename($caller[0]['file'] ?? 'index.php');
    $query  = $_SERVER['QUERY_STRING'] ?? '';
    $url    = ADMIN_URL . '/' . $page . (($isPost && $query !== '' && !$isDelete) ? '?' . $query : '');

    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo '<script>location.replace(' . json_encode($url) . ');</script>';
    }
    exit;
}

/**
 * Flash mesaj ayarla
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash mesaj al ve temizle
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
