<?php
/**
 * Dosya Yükleme API
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Auth kontrolü
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Yetkisiz erişim']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek']);
    exit;
}

// post_max_size aşılınca PHP bütün alanları boşaltır (anahtar da gelmez) → doğru mesajı ver
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    echo json_encode(['success' => false, 'error' => 'Dosya çok büyük (sunucu limiti)']);
    exit;
}

// CSRF: başka bir siteden admin oturumu kullanılarak gizlice dosya yüklenmesin
if (!validateCSRFToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.']);
    exit;
}

if (!isset($_FILES['file']) || !is_array($_FILES['file']) || is_array($_FILES['file']['name'])) {
    echo json_encode(['success' => false, 'error' => 'Dosya bulunamadı']);
    exit;
}

$file = $_FILES['file'];

// Klasör yalnız bu listeden olabilir (../ ile uploads dışına yazılmasın); bilinmeyen → general
$allowedFolders = ['general', 'hero', 'tours', 'destinations', 'blog', 'testimonials'];
$folder = $_POST['folder'] ?? 'general';
if (!is_string($folder) || !in_array($folder, $allowedFolders, true)) {
    $folder = 'general';
}

// Hata kontrolü
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'Dosya çok büyük (php.ini limiti)',
        UPLOAD_ERR_FORM_SIZE => 'Dosya çok büyük (form limiti)',
        UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi',
        UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi',
        UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı',
        UPLOAD_ERR_CANT_WRITE => 'Dosya yazılamadı',
    ];
    echo json_encode(['success' => false, 'error' => $errors[$file['error']] ?? 'Bilinmeyen hata']);
    exit;
}

// Dosya boyutu kontrolü (50MB)
$maxSize = 50 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'Dosya 50MB\'dan büyük olamaz']);
    exit;
}

// MIME type kontrolü (dosyanın içeriğine bakılır, adına değil) → [tür, kaydedilecek uzantı]
$allowedTypes = [
    'image/jpeg' => ['image', 'jpg'],
    'image/png' => ['image', 'png'],
    'image/gif' => ['image', 'gif'],
    'image/webp' => ['image', 'webp'],
    'image/svg+xml' => ['image', 'svg'],
    'video/mp4' => ['video', 'mp4'],
    'video/webm' => ['video', 'webm'],
    'video/ogg' => ['video', 'ogv'],
    'application/pdf' => ['document', 'pdf'],
    'application/msword' => ['document', 'doc'],
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['document', 'docx'],
];

$mimeType = mime_content_type($file['tmp_name']);
if (!isset($allowedTypes[$mimeType])) {
    echo json_encode(['success' => false, 'error' => 'Desteklenmeyen dosya formatı: ' . $mimeType]);
    exit;
}

$fileType = $allowedTypes[$mimeType][0];

// Dosya adı oluştur — uzantı İÇERİKTEN (MIME) gelir, kullanıcının verdiği addan değil.
// Böylece resim gibi görünen "kotu.php" sunucuya .php olarak yazılamaz (kod çalıştırma açığı).
$extension = $allowedTypes[$mimeType][1];
$filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

// Klasör oluştur
$uploadDir = UPLOADS_PATH . $folder . '/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filePath = $folder . '/' . $filename;
$fullPath = UPLOADS_PATH . $filePath;

// Dosyayı taşı
if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
    echo json_encode(['success' => false, 'error' => 'Dosya kaydedilemedi']);
    exit;
}

// Görsel boyutlarını al
$width = null;
$height = null;
if ($fileType === 'image') {
    $imageInfo = getimagesize($fullPath);
    if ($imageInfo) {
        $width = $imageInfo[0];
        $height = $imageInfo[1];
    }
}

// Veritabanına kaydet
try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO media (filename, original_name, file_path, file_type, mime_type, file_size, width, height, folder, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $filename,
        $file['name'],
        $filePath,
        $fileType,
        $mimeType,
        $file['size'],
        $width,
        $height,
        $folder,
        $_SESSION['admin_id'] ?? null
    ]);
    
    $mediaId = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'media' => [
            'id' => $mediaId,
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_path' => $filePath,
            'file_type' => $fileType,
            'url' => UPLOADS_URL . $filePath,
            'width' => $width,
            'height' => $height
        ]
    ]);
} catch (Exception $e) {
    // Dosyayı sil
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
    echo json_encode(['success' => false, 'error' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
