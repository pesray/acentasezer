<?php
/**
 * Medya Seçici Modal Bileşeni
 * Kullanım: <?php include 'includes/media-picker.php'; ?>
 * JS: openMediaPicker(inputId, type) - type: 'image', 'video', 'all'
 */
?>

<!-- Media Picker Modal -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-collection me-2 text-primary"></i>Medya Seç
                    </h5>
                    <ul class="nav nav-pills" id="mediaPickerTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-tab="library">Kütüphane</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-tab="upload">Yükle</button>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Library Tab -->
                <div id="mediaLibraryTab">
                    <div class="d-flex gap-3 mb-3">
                        <div class="btn-group" id="mediaTypeFilter">
                            <button type="button" class="btn btn-outline-secondary btn-sm active" data-type="all">Tümü</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-type="image">Görseller</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-type="video">Videolar</button>
                        </div>
                        <input type="text" class="form-control form-control-sm" id="mediaSearchInput" placeholder="Ara..." style="max-width: 200px;">
                    </div>
                    
                    <div id="mediaPickerGrid" class="media-picker-grid">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2">Yükleniyor...</p>
                        </div>
                    </div>
                    
                    <div id="mediaPickerPagination" class="d-flex justify-content-center mt-3"></div>
                </div>
                
                <!-- Upload Tab -->
                <div id="mediaUploadTab" style="display: none;">
                    <div class="media-upload-zone" id="pickerUploadZone">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <h5>Dosyaları buraya sürükleyin</h5>
                        <p class="text-muted mb-3">veya</p>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('pickerFileInput').click()">
                            <i class="bi bi-folder2-open me-2"></i>Dosya Seç
                        </button>
                        <input type="file" id="pickerFileInput" multiple accept="image/*,video/*" style="display: none;">
                    </div>
                    <div id="pickerUploadProgress" class="mt-3" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="mediaPickerSelect" disabled>
                    <i class="bi bi-check-lg me-1"></i>Seç
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.media-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    max-height: 400px;
    overflow-y: auto;
    padding: 4px;
}
.media-picker-item {
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    position: relative;
    border: 3px solid transparent;
    transition: all 0.2s;
    background: #f1f5f9;
}
.media-picker-item:hover {
    border-color: #94a3b8;
}
.media-picker-item.selected {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}
.media-picker-item img,
.media-picker-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.media-picker-item .check-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    background: #3b82f6;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}
.media-picker-item.selected .check-icon {
    display: flex;
}
.media-picker-item .video-icon {
    position: absolute;
    bottom: 8px;
    left: 8px;
    background: rgba(0,0,0,0.6);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
}
.media-upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 48px;
    text-align: center;
    background: #f8fafc;
    transition: all 0.2s;
}
.media-upload-zone:hover,
.media-upload-zone.dragover {
    border-color: #3b82f6;
    background: #eff6ff;
}
.media-upload-zone i {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 16px;
    display: block;
}
#mediaPickerTabs .nav-link {
    padding: 6px 16px;
    border-radius: 6px;
    color: #64748b;
    font-size: 14px;
}
#mediaPickerTabs .nav-link.active {
    background: #e2e8f0;
    color: #1e293b;
}
</style>

<script>
let mediaPickerCallback = null;
let mediaPickerType = 'all';
let selectedMedia = null;
let mediaPickerPage = 1;

function openMediaPicker(inputId, type = 'all') {
    mediaPickerType = type;
    selectedMedia = null;
    mediaPickerPage = 1;
    
    // Callback ayarla
    mediaPickerCallback = (media) => {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = media.file_path;
            // Preview varsa güncelle
            const preview = document.getElementById(inputId + '_preview');
            if (preview) {
                if (media.file_type === 'image') {
                    preview.innerHTML = `<img src="${media.url}" class="img-thumbnail" style="max-height: 100px;">`;
                } else if (media.file_type === 'video') {
                    preview.innerHTML = `<video src="${media.url}" class="img-thumbnail" style="max-height: 100px;" muted></video>`;
                }
            }
        }
    };
    
    // Type filter ayarla
    document.querySelectorAll('#mediaTypeFilter button').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.type === type || (type === 'all' && btn.dataset.type === 'all'));
        if (type !== 'all') {
            btn.style.display = btn.dataset.type === type || btn.dataset.type === 'all' ? '' : 'none';
        } else {
            btn.style.display = '';
        }
    });
    
    // Modal aç
    const modal = new bootstrap.Modal(document.getElementById('mediaPickerModal'));
    modal.show();
    
    // Medyaları yükle
    loadMediaPickerItems();
}

function loadMediaPickerItems() {
    const grid = document.getElementById('mediaPickerGrid');
    const search = document.getElementById('mediaSearchInput').value;
    const activeType = document.querySelector('#mediaTypeFilter button.active')?.dataset.type || 'all';
    
    grid.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    
    fetch(`<?= ADMIN_URL ?>/api/media.php?type=${activeType}&search=${encodeURIComponent(search)}&page=${mediaPickerPage}&limit=50`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.media.length > 0) {
                grid.innerHTML = data.media.map(media => `
                    <div class="media-picker-item" data-id="${media.id}" data-media='${JSON.stringify(media).replace(/'/g, "&#39;")}' onclick="selectMediaItem(this)">
                        ${media.file_type === 'image' 
                            ? `<img src="${media.url}" alt="${media.original_name}" loading="lazy">`
                            : `<video src="${media.url}" muted></video><span class="video-icon"><i class="bi bi-play-fill"></i></span>`
                        }
                        <span class="check-icon"><i class="bi bi-check"></i></span>
                    </div>
                `).join('');
                
                // Pagination
                if (data.pages > 1) {
                    let pagination = '';
                    for (let i = 1; i <= data.pages; i++) {
                        pagination += `<button class="btn btn-sm ${i === data.page ? 'btn-primary' : 'btn-outline-secondary'}" onclick="mediaPickerPage=${i};loadMediaPickerItems()">${i}</button>`;
                    }
                    document.getElementById('mediaPickerPagination').innerHTML = pagination;
                } else {
                    document.getElementById('mediaPickerPagination').innerHTML = '';
                }
            } else {
                grid.innerHTML = `
                    <div class="text-center py-5 col-span-full" style="grid-column: 1/-1;">
                        <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                        <p class="text-muted mt-2">Medya bulunamadı</p>
                        <button class="btn btn-sm btn-primary" onclick="switchToUploadTab()">Dosya Yükle</button>
                    </div>
                `;
            }
        });
}

function selectMediaItem(element) {
    document.querySelectorAll('.media-picker-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    selectedMedia = JSON.parse(element.dataset.media);
    document.getElementById('mediaPickerSelect').disabled = false;
}

// Tab switching
document.querySelectorAll('#mediaPickerTabs button').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#mediaPickerTabs button').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const tab = this.dataset.tab;
        document.getElementById('mediaLibraryTab').style.display = tab === 'library' ? '' : 'none';
        document.getElementById('mediaUploadTab').style.display = tab === 'upload' ? '' : 'none';
    });
});

function switchToUploadTab() {
    document.querySelector('#mediaPickerTabs button[data-tab="upload"]').click();
}

// Type filter
document.querySelectorAll('#mediaTypeFilter button').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#mediaTypeFilter button').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        mediaPickerPage = 1;
        loadMediaPickerItems();
    });
});

// Search
let searchTimeout;
document.getElementById('mediaSearchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        mediaPickerPage = 1;
        loadMediaPickerItems();
    }, 300);
});

// Select button
document.getElementById('mediaPickerSelect').addEventListener('click', function() {
    if (selectedMedia && mediaPickerCallback) {
        mediaPickerCallback(selectedMedia);
        bootstrap.Modal.getInstance(document.getElementById('mediaPickerModal')).hide();
    }
});

// Upload in picker
const pickerUploadZone = document.getElementById('pickerUploadZone');
const pickerFileInput = document.getElementById('pickerFileInput');

pickerUploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    pickerUploadZone.classList.add('dragover');
});

pickerUploadZone.addEventListener('dragleave', () => {
    pickerUploadZone.classList.remove('dragover');
});

pickerUploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    pickerUploadZone.classList.remove('dragover');
    handlePickerUpload(e.dataTransfer.files);
});

pickerFileInput.addEventListener('change', () => {
    handlePickerUpload(pickerFileInput.files);
});

function handlePickerUpload(files) {
    if (files.length === 0) return;
    
    const progressDiv = document.getElementById('pickerUploadProgress');
    progressDiv.style.display = 'block';
    progressDiv.innerHTML = '';
    
    let completed = 0;
    
    Array.from(files).forEach(file => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('folder', 'general');
        
        const item = document.createElement('div');
        item.className = 'progress-item d-flex align-items-center gap-2 mb-2';
        item.innerHTML = `
            <span class="small flex-grow-1">${file.name}</span>
            <div class="spinner-border spinner-border-sm text-primary"></div>
        `;
        progressDiv.appendChild(item);
        
        fetch('<?= ADMIN_URL ?>/api/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            completed++;
            if (data.success) {
                item.innerHTML = `
                    <span class="small flex-grow-1">${file.name}</span>
                    <i class="bi bi-check-circle-fill text-success"></i>
                `;
            } else {
                item.innerHTML = `
                    <span class="small flex-grow-1">${file.name}</span>
                    <i class="bi bi-x-circle-fill text-danger"></i>
                `;
            }
            
            if (completed === files.length) {
                setTimeout(() => {
                    document.querySelector('#mediaPickerTabs button[data-tab="library"]').click();
                    loadMediaPickerItems();
                }, 500);
            }
        });
    });
}
</script>
