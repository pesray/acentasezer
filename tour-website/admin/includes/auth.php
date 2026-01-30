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
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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
