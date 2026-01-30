<?php
/**
 * Medya Yönetimi
 */

$pageTitle = 'Medya Yönetimi';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Medya tablosunu oluştur (yoksa)
$db->exec("
    CREATE TABLE IF NOT EXISTS media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        file_size INT NOT NULL DEFAULT 0,
        width INT DEFAULT NULL,
        height INT DEFAULT NULL,
        alt_text VARCHAR(255) DEFAULT NULL,
        title VARCHAR(255) DEFAULT NULL,
        folder VARCHAR(100) DEFAULT 'general',
        uploaded_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_file_type (file_type),
        INDEX idx_folder (folder),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch();
    
    if ($media) {
        $filePath = UPLOADS_PATH . $media['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $db->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Dosya silindi.');
    }
    header('Location: ' . ADMIN_URL . '/media.php');
    exit;
}

// Filtreleme
$type = $_GET['type'] ?? 'all';
$folder = $_GET['folder'] ?? 'all';
$search = $_GET['search'] ?? '';

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
    $where[] = "(original_name LIKE ? OR title LIKE ? OR alt_text LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Medya listesi
$stmt = $db->prepare("SELECT * FROM media $whereClause ORDER BY created_at DESC");
$stmt->execute($params);
$mediaList = $stmt->fetchAll();

// Klasörler
$folders = $db->query("SELECT DISTINCT folder FROM media ORDER BY folder")->fetchAll(PDO::FETCH_COLUMN);

// İstatistikler
$stats = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN file_type = 'image' THEN 1 ELSE 0 END) as images,
        SUM(CASE WHEN file_type = 'video' THEN 1 ELSE 0 END) as videos,
        SUM(CASE WHEN file_type = 'document' THEN 1 ELSE 0 END) as documents,
        SUM(file_size) as total_size
    FROM media
")->fetch();
?>

<style>
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}
.media-item {
    position: relative;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.2s;
    cursor: pointer;
}
.media-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.media-item.selected {
    ring: 3px solid #3b82f6;
    box-shadow: 0 0 0 3px #3b82f6;
}
.media-thumb {
    aspect-ratio: 1;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.media-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.media-thumb .file-icon {
    font-size: 48px;
    color: #94a3b8;
}
.media-thumb video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.media-info {
    padding: 12px;
}
.media-info .filename {
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.media-info .meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 4px;
}
.media-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: none;
}
.media-item:hover .media-actions {
    display: flex;
    gap: 4px;
}
.media-actions .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(4px);
}
.upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 48px;
    text-align: center;
    background: #f8fafc;
    transition: all 0.2s;
    cursor: pointer;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: #3b82f6;
    background: #eff6ff;
}
.upload-zone .upload-icon {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 16px;
}
.upload-zone.dragover .upload-icon {
    color: #3b82f6;
}
.filter-tabs .nav-link {
    color: #64748b;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
}
.filter-tabs .nav-link.active {
    background: #3b82f6;
    color: white;
}
.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f1f5f9;
    border-radius: 8px;
    font-size: 13px;
    color: #475569;
}
.progress-item {
    background: #fff;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6, #6366f1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <i class="bi bi-collection text-white" style="font-size: 24px;"></i>
        </div>
        <div>
            <h1 class="h4 mb-0 fw-bold">Medya Yönetimi</h1>
            <small class="text-muted">Görsel ve video dosyalarınızı yönetin</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload me-2"></i>Dosya Yükle
        </button>
    </div>
</div>

<!-- İstatistikler -->
<div class="d-flex gap-3 mb-4 flex-wrap">
    <div class="stat-badge">
        <i class="bi bi-files"></i>
        <span><?= number_format($stats['total'] ?? 0) ?> Dosya</span>
    </div>
    <div class="stat-badge">
        <i class="bi bi-image text-success"></i>
        <span><?= number_format($stats['images'] ?? 0) ?> Görsel</span>
    </div>
    <div class="stat-badge">
        <i class="bi bi-film text-primary"></i>
        <span><?= number_format($stats['videos'] ?? 0) ?> Video</span>
    </div>
    <div class="stat-badge">
        <i class="bi bi-hdd"></i>
        <span><?= formatFileSize($stats['total_size'] ?? 0) ?></span>
    </div>
</div>

<!-- Filtreler -->
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <ul class="nav filter-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?= $type === 'all' ? 'active' : '' ?>" href="?type=all">Tümü</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $type === 'image' ? 'active' : '' ?>" href="?type=image">
                            <i class="bi bi-image me-1"></i>Görseller
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $type === 'video' ? 'active' : '' ?>" href="?type=video">
                            <i class="bi bi-film me-1"></i>Videolar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $type === 'document' ? 'active' : '' ?>" href="?type=document">
                            <i class="bi bi-file-earmark me-1"></i>Belgeler
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-auto">
                <select class="form-select form-select-sm" onchange="location.href='?type=<?= $type ?>&folder='+this.value">
                    <option value="all">Tüm Klasörler</option>
                    <?php foreach ($folders as $f): ?>
                    <option value="<?= e($f) ?>" <?= $folder === $f ? 'selected' : '' ?>><?= e(ucfirst($f)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <form class="d-flex gap-2">
                    <input type="hidden" name="type" value="<?= e($type) ?>">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Dosya ara..." value="<?= e($search) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Medya Grid -->
<?php if (empty($mediaList)): ?>
<div class="text-center py-5">
    <i class="bi bi-inbox text-muted" style="font-size: 64px;"></i>
    <p class="text-muted mt-3">Henüz dosya yüklenmemiş</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-cloud-upload me-2"></i>İlk Dosyayı Yükle
    </button>
</div>
<?php else: ?>
<div class="media-grid">
    <?php foreach ($mediaList as $media): ?>
    <div class="media-item" data-id="<?= $media['id'] ?>" data-path="<?= e($media['file_path']) ?>" onclick="showMediaDetail(<?= $media['id'] ?>)">
        <div class="media-actions">
            <a href="<?= UPLOADS_URL . e($media['file_path']) ?>" target="_blank" class="btn btn-sm" title="Görüntüle" onclick="event.stopPropagation()">
                <i class="bi bi-eye"></i>
            </a>
            <a href="?delete=<?= $media['id'] ?>" class="btn btn-sm text-danger btn-delete" title="Sil" onclick="event.stopPropagation()">
                <i class="bi bi-trash"></i>
            </a>
        </div>
        <div class="media-thumb">
            <?php if ($media['file_type'] === 'image'): ?>
            <img src="<?= UPLOADS_URL . e($media['file_path']) ?>" alt="<?= e($media['alt_text'] ?? $media['original_name']) ?>" loading="lazy">
            <?php elseif ($media['file_type'] === 'video'): ?>
            <video muted>
                <source src="<?= UPLOADS_URL . e($media['file_path']) ?>" type="<?= e($media['mime_type']) ?>">
            </video>
            <i class="bi bi-play-circle-fill position-absolute" style="font-size: 32px; color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.5);"></i>
            <?php else: ?>
            <i class="bi bi-file-earmark file-icon"></i>
            <?php endif; ?>
        </div>
        <div class="media-info">
            <div class="filename" title="<?= e($media['original_name']) ?>"><?= e($media['original_name']) ?></div>
            <div class="meta">
                <?= formatFileSize($media['file_size']) ?>
                <?php if ($media['width']): ?>
                • <?= $media['width'] ?>x<?= $media['height'] ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cloud-upload me-2 text-primary"></i>Dosya Yükle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="upload-zone" id="uploadZone">
                    <i class="bi bi-cloud-arrow-up upload-icon"></i>
                    <h5>Dosyaları buraya sürükleyin</h5>
                    <p class="text-muted mb-3">veya</p>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-folder2-open me-2"></i>Dosya Seç
                    </button>
                    <input type="file" id="fileInput" multiple accept="image/*,video/*,.pdf,.doc,.docx" style="display: none;">
                    <p class="text-muted mt-3 mb-0 small">
                        Desteklenen formatlar: JPG, PNG, GIF, WEBP, MP4, WEBM, PDF, DOC<br>
                        Maksimum dosya boyutu: 50MB
                    </p>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Klasör</label>
                        <select id="uploadFolder" class="form-select">
                            <option value="general">Genel</option>
                            <option value="hero">Hero / Slider</option>
                            <option value="tours">Turlar</option>
                            <option value="destinations">Destinasyonlar</option>
                            <option value="blog">Blog</option>
                            <option value="testimonials">Yorumlar</option>
                        </select>
                    </div>
                </div>
                
                <div id="uploadProgress" class="mt-4" style="display: none;">
                    <h6 class="mb-3">Yükleniyor...</h6>
                    <div id="progressList"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Detail Modal -->
<div class="modal fade" id="mediaDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Dosya Detayları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div id="mediaPreview" class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="min-height: 300px;"></div>
                    </div>
                    <div class="col-md-6">
                        <form id="mediaEditForm">
                            <input type="hidden" id="mediaId" name="id">
                            <div class="mb-3">
                                <label class="form-label">Dosya Adı</label>
                                <input type="text" id="mediaFilename" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" id="mediaTitle" name="title" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alt Metin (SEO)</label>
                                <input type="text" id="mediaAlt" name="alt_text" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dosya URL</label>
                                <div class="input-group">
                                    <input type="text" id="mediaUrl" class="form-control" readonly>
                                    <button type="button" class="btn btn-outline-primary" onclick="copyMediaUrl()">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">Boyut</label>
                                    <input type="text" id="mediaSize" class="form-control" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Boyutlar</label>
                                    <input type="text" id="mediaDimensions" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Kaydet
                                </button>
                                <a href="#" id="mediaDownload" class="btn btn-outline-secondary" download>
                                    <i class="bi bi-download me-1"></i>İndir
                                </a>
                                <button type="button" class="btn btn-outline-danger ms-auto" onclick="deleteMedia()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const uploadProgress = document.getElementById('uploadProgress');
const progressList = document.getElementById('progressList');

// Drag & Drop
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', () => {
    handleFiles(fileInput.files);
});

function handleFiles(files) {
    if (files.length === 0) return;
    
    uploadProgress.style.display = 'block';
    progressList.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        uploadFile(file, index);
    });
}

function uploadFile(file, index) {
    const folder = document.getElementById('uploadFolder').value;
    const formData = new FormData();
    formData.append('file', file);
    formData.append('folder', folder);
    
    const progressItem = document.createElement('div');
    progressItem.className = 'progress-item';
    progressItem.innerHTML = `
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-file-earmark text-muted"></i>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-medium">${file.name}</span>
                    <span class="small text-muted progress-percent">0%</span>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
            </div>
            <span class="upload-status"></span>
        </div>
    `;
    progressList.appendChild(progressItem);
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= ADMIN_URL ?>/api/upload.php');
    
    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressItem.querySelector('.progress-bar').style.width = percent + '%';
            progressItem.querySelector('.progress-percent').textContent = percent + '%';
        }
    };
    
    xhr.onload = () => {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                progressItem.querySelector('.upload-status').innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                progressItem.querySelector('.progress-bar').classList.add('bg-success');
            } else {
                progressItem.querySelector('.upload-status').innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                progressItem.querySelector('.progress-bar').classList.add('bg-danger');
            }
        }
    };
    
    xhr.onerror = () => {
        progressItem.querySelector('.upload-status').innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
    };
    
    xhr.send(formData);
}

// Media Detail
let currentMedia = null;

function showMediaDetail(id) {
    fetch('<?= ADMIN_URL ?>/api/media.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                currentMedia = data.media;
                document.getElementById('mediaId').value = data.media.id;
                document.getElementById('mediaFilename').value = data.media.original_name;
                document.getElementById('mediaTitle').value = data.media.title || '';
                document.getElementById('mediaAlt').value = data.media.alt_text || '';
                document.getElementById('mediaUrl').value = '<?= UPLOADS_URL ?>' + data.media.file_path;
                document.getElementById('mediaSize').value = data.media.file_size_formatted;
                document.getElementById('mediaDimensions').value = data.media.width ? data.media.width + 'x' + data.media.height : '-';
                document.getElementById('mediaDownload').href = '<?= UPLOADS_URL ?>' + data.media.file_path;
                
                const preview = document.getElementById('mediaPreview');
                if (data.media.file_type === 'image') {
                    preview.innerHTML = `<img src="<?= UPLOADS_URL ?>${data.media.file_path}" class="img-fluid rounded" style="max-height: 400px;">`;
                } else if (data.media.file_type === 'video') {
                    preview.innerHTML = `<video controls class="w-100 rounded"><source src="<?= UPLOADS_URL ?>${data.media.file_path}"></video>`;
                } else {
                    preview.innerHTML = `<i class="bi bi-file-earmark" style="font-size: 64px;"></i>`;
                }
                
                new bootstrap.Modal(document.getElementById('mediaDetailModal')).show();
            }
        });
}

function copyMediaUrl() {
    const url = document.getElementById('mediaUrl').value;
    navigator.clipboard.writeText(url);
    alert('URL kopyalandı!');
}

function deleteMedia() {
    if (currentMedia && confirm('Bu dosyayı silmek istediğinize emin misiniz?')) {
        location.href = '?delete=' + currentMedia.id;
    }
}

// Media Edit Form
document.getElementById('mediaEditForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?= ADMIN_URL ?>/api/media.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('mediaDetailModal')).hide();
            location.reload();
        }
    });
});

// Refresh after upload
document.getElementById('uploadModal').addEventListener('hidden.bs.modal', function() {
    if (progressList.children.length > 0) {
        location.reload();
    }
});
</script>

<?php 
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

require_once __DIR__ . '/includes/footer.php'; 
?>
