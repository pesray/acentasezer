    </div><!-- /content-wrapper -->
</div><!-- /main-content -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-tr-TR.min.js"></script>
<!-- Admin JS (AJAX & Toast) -->
<script src="<?= ADMIN_URL ?>/assets/js/admin.js"></script>

<script>
$(document).ready(function() {
    // Sidebar toggle for mobile
    $('#sidebarToggle').on('click', function() {
        $('.sidebar').toggleClass('show');
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
