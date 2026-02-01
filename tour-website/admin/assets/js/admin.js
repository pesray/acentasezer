/**
 * Admin Panel - Ortak AJAX ve UI fonksiyonları
 */

const AdminAPI = {
    baseUrl: window.ADMIN_URL || '/admin',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    
    // AJAX POST isteği
    async post(entity, action, data = {}) {
        const formData = new FormData();
        formData.append('entity', entity);
        formData.append('action', action);
        formData.append('csrf_token', this.csrfToken);
        
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }
        
        try {
            const response = await fetch(`${this.baseUrl}/api/handler.php`, {
                method: 'POST',
                body: formData
            });
            
            if (response.status === 401) {
                Toast.error('Oturum süresi dolmuş. Sayfa yenileniyor...');
                setTimeout(() => window.location.reload(), 1500);
                return null;
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            Toast.error('Bir hata oluştu. Lütfen tekrar deneyin.');
            return null;
        }
    },
    
    // Silme işlemi (onay ile)
    async delete(entity, id, confirmText = 'Bu kaydı silmek istediğinizden emin misiniz?') {
        if (!confirm(confirmText)) return false;
        
        const result = await this.post(entity, 'delete', { id });
        
        if (result?.success) {
            Toast.success(result.message);
            return true;
        } else if (result) {
            Toast.error(result.message);
        }
        return false;
    },
    
    // Durum değiştirme
    async toggleStatus(entity, id, status) {
        const result = await this.post(entity, 'toggle_status', { id, status });
        
        if (result?.success) {
            Toast.success(result.message);
            return true;
        } else if (result) {
            Toast.error(result.message);
        }
        return false;
    },
    
    // Öne çıkarma toggle
    async toggleFeatured(entity, id, featured) {
        const result = await this.post(entity, 'toggle_featured', { id, featured: featured ? 1 : 0 });
        
        if (result?.success) {
            Toast.success(result.message);
            return true;
        } else if (result) {
            Toast.error(result.message);
        }
        return false;
    }
};

// Toast Bildirimleri
const Toast = {
    container: null,
    
    init() {
        if (this.container) return;
        
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(this.container);
    },
    
    show(message, type = 'info', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast-item toast-${type}`;
        toast.style.cssText = `
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
            min-width: 280px;
            max-width: 400px;
        `;
        
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        toast.style.background = colors[type] || colors.info;
        if (type === 'warning') toast.style.color = '#333';
        
        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };
        
        toast.innerHTML = `
            <i class="bi ${icons[type] || icons.info}"></i>
            <span>${message}</span>
        `;
        
        this.container.appendChild(toast);
        
        // Auto remove
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error', 5000); },
    warning(message) { this.show(message, 'warning'); },
    info(message) { this.show(message, 'info'); }
};

// CSS animasyonları ekle
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    .table-row-removing {
        animation: rowFadeOut 0.3s ease forwards;
    }
    @keyframes rowFadeOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(-20px); height: 0; padding: 0; }
    }
`;
document.head.appendChild(style);

// Silme butonlarını otomatik bağla
document.addEventListener('DOMContentLoaded', function() {
    // Data attribute ile silme butonları
    document.querySelectorAll('[data-delete]').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const entity = this.dataset.entity;
            const id = this.dataset.id;
            const customAction = this.dataset.action || 'delete';
            const row = this.closest('tr');
            
            if (!confirm('Bu kaydı silmek istediğinizden emin misiniz?')) return;
            
            const result = await AdminAPI.post(entity, customAction, { id });
            
            if (result?.success) {
                Toast.success(result.message);
                if (row) {
                    row.classList.add('table-row-removing');
                    setTimeout(() => row.remove(), 300);
                }
            } else if (result) {
                Toast.error(result.message);
            }
        });
    });
    
    // Durum toggle butonları
    document.querySelectorAll('[data-toggle-status]').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const entity = this.dataset.entity;
            const id = this.dataset.id;
            const newStatus = this.dataset.toggleStatus;
            
            const success = await AdminAPI.toggleStatus(entity, id, newStatus);
            
            if (success) {
                // Badge'i güncelle
                const badge = this.closest('tr')?.querySelector('.badge');
                if (badge) {
                    if (newStatus === 'published') {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Yayında';
                    } else {
                        badge.className = 'badge bg-secondary';
                        badge.textContent = 'Taslak';
                    }
                }
            }
        });
    });
});

// Global erişim
window.AdminAPI = AdminAPI;
window.Toast = Toast;
