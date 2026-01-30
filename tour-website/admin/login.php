<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/includes/auth.php';

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } elseif (adminLogin($username, $password)) {
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    } else {
        $error = 'Geçersiz kullanıcı adı veya şifre.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
            padding: 2.5rem;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo i {
            font-size: 3rem;
            color: #4e73df;
        }
        .login-logo h1 {
            font-size: 1.5rem;
            color: #333;
            margin-top: 0.5rem;
        }
        .form-floating > label {
            color: #6c757d;
        }
        .btn-login {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #224abe 0%, #1a3a8f 100%);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-logo">
        <i class="bi bi-globe2"></i>
        <h1>Tour Admin Panel</h1>
    </div>
    
    <?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="username" name="username" placeholder="Kullanıcı Adı" required autofocus>
            <label for="username"><i class="bi bi-person me-2"></i>Kullanıcı Adı veya E-posta</label>
        </div>
        
        <div class="form-floating mb-4">
            <input type="password" class="form-control" id="password" name="password" placeholder="Şifre" required>
            <label for="password"><i class="bi bi-lock me-2"></i>Şifre</label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-login w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
        </button>
    </form>
    
    <div class="text-center mt-4">
        <a href="<?= SITE_URL ?>" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Siteye Dön
        </a>
    </div>
</div>

</body>
</html>
