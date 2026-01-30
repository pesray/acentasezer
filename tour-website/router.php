<?php
/**
 * PHP Built-in Server Router
 * Kullanım: php -S localhost:8080 router.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Gerçek dosya varsa direkt sun
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    // CSS, JS, resim dosyaları için
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'ico' => 'image/x-icon',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
    ];
    
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile(__DIR__ . $path);
        return true;
    }
    
    // PHP dosyaları
    if ($ext === 'php') {
        return false; // PHP'nin kendi işlemesine bırak
    }
    
    return false;
}

// Assets için template klasörüne yönlendir
if (strpos($path, '/assets/') === 0) {
    $templatePath = dirname(__DIR__) . '/tour-template' . $path;
    if (file_exists($templatePath)) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'map' => 'application/json',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($templatePath);
        return true;
    }
}

// Diğer tüm istekleri index.php'ye yönlendir
require_once __DIR__ . '/index.php';
