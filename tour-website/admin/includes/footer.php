    </div><!-- /content-wrapper -->
</div><!-- /main-content -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-tr-TR.min.js"></script>
<!-- Admin JS (AJAX & Toast) -->
<script src="<?= ADMIN_URL ?>/assets/js/admin.js"></script>

<script>
$(document).ready(function() {
    // ── Tema (Dark / Light) ──────────────────────────────────
    var themeBtn   = document.getElementById('themeToggle');
    var iconDark   = document.getElementById('themeIconDark');
    var iconLight  = document.getElementById('themeIconLight');

    function applyThemeIcon(theme) {
        if (!iconDark || !iconLight) return;
        if (theme === 'dark') {
            iconDark.style.display = 'none';
            iconLight.style.display = '';
        } else {
            iconDark.style.display = '';
            iconLight.style.display = 'none';
        }
    }
    var currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
    applyThemeIcon(currentTheme);

    if (themeBtn) {
        themeBtn.addEventListener('click', function() {
            var t = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', t);
            try { localStorage.setItem('adminTheme', t); } catch (e) {}
            applyThemeIcon(t);
        });
    }

    // ── Sidebar collapse toggle ──────────────────────────────
    var sidebar     = document.getElementById('mainSidebar');
    var mainContent = document.getElementById('mainContent');
    var overlay     = document.getElementById('sidebarOverlay');
    var isMobile    = function() { return window.matchMedia('(max-width: 991.98px)').matches; };

    // Restore saved state (sadece desktop'ta)
    if (!isMobile() && localStorage.getItem('sidebarCollapsed') === '1') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
    }

    // Event delegation — bookings.php gibi geç yüklenen sayfalarda da çalışır
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('#sidebarToggle');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        if (isMobile()) {
            var open = sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show', open);
            return;
        }
        var collapsed = sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('sidebar-collapsed', collapsed);
        try { localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); } catch(err) {}
    });

    // Mobile: overlay tıklayınca kapat
    if (overlay) overlay.addEventListener('click', closeMobileSidebar);

    // Mobile: link tıklayınca otomatik kapat (collapse içindekiler hariç)
    document.querySelectorAll('.sidebar-nav .nav-link[href]').forEach(function(link) {
        link.addEventListener('click', function() {
            if (isMobile() && !this.hasAttribute('data-bs-toggle')) {
                closeMobileSidebar();
            }
        });
    });

    // Window resize: mobile→desktop geçince mobile state'i temizle
    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (!isMobile()) {
                sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
            }
        }, 150);
    });

    // Flyout vertical positioning + hover bridge (collapsed mode)
    document.querySelectorAll('.nav-item-group').forEach(function(group) {
        var flyout = group.querySelector('.submenu-flyout');
        if (!flyout) return;
        var closeTimer = null;

        function open() {
            if (!sidebar.classList.contains('collapsed') || isMobile()) return;
            clearTimeout(closeTimer);
            // Diğer tüm açık flyout'ları kapat
            document.querySelectorAll('.nav-item-group.flyout-open').forEach(function(g) {
                if (g !== group) g.classList.remove('flyout-open');
            });
            var rect = group.getBoundingClientRect();
            flyout.style.top = rect.top + 'px';
            group.classList.add('flyout-open');
        }
        function scheduleClose() {
            clearTimeout(closeTimer);
            closeTimer = setTimeout(function() {
                group.classList.remove('flyout-open');
            }, 180);
        }
        function cancelClose() {
            clearTimeout(closeTimer);
        }

        group.addEventListener('mouseenter', open);
        group.addEventListener('mouseleave', scheduleClose);
        flyout.addEventListener('mouseenter', cancelClose);
        flyout.addEventListener('mouseleave', scheduleClose);
    });

    // Sidebar genişleyince tüm flyout'ları kapat
    document.addEventListener('click', function(e) {
        if (e.target.closest('#sidebarToggle')) {
            document.querySelectorAll('.nav-item-group.flyout-open').forEach(function(g) {
                g.classList.remove('flyout-open');
            });
        }
    });

    // ESC ile mobile sidebar kapansın
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            closeMobileSidebar();
        }
    });
    
    // DataTables default config
    if ($.fn.DataTable) {
        $.extend($.fn.dataTable.defaults, {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
            },
            pageLength: 25,
            responsive: true
        });
    }
    
    // Select2 default config
    if ($.fn.select2) {
        $.fn.select2.defaults.set('theme', 'bootstrap-5');
        $.fn.select2.defaults.set('width', '100%');
    }
    
    // Summernote default config
    $('.summernote').summernote({
        height: 300,
        lang: 'tr-TR',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
    
    // Initialize DataTables
    $('.datatable').DataTable();
    
    // Initialize Select2
    $('.select2').select2();
    
    // Confirm delete
    $(document).on('click', '.btn-delete', function(e) {
        if (!confirm('Bu öğeyi silmek istediğinizden emin misiniz?')) {
            e.preventDefault();
        }
    });
    
    // Auto-generate slug
    $('input[name="title"]').on('blur', function() {
        var slugField = $('input[name="slug"]');
        if (slugField.val() === '') {
            var title = $(this).val();
            var slug = title.toLowerCase()
                .replace(/ı/g, 'i').replace(/ğ/g, 'g').replace(/ü/g, 'u')
                .replace(/ş/g, 's').replace(/ö/g, 'o').replace(/ç/g, 'c')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugField.val(slug);
        }
    });
});
</script>

<?php if (isset($extraJs)): ?>
<?= $extraJs ?>
<?php endif; ?>

<?php include __DIR__ . '/media-picker.php'; ?>

</body>
</html>
