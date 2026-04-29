<?php
/**
 * Admin Panel Header
 */

require_once __DIR__ . '/auth.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$adminName   = $_SESSION['admin_name'] ?? 'Admin';

// Site ayarları (logo + isim dinamik)
$siteName    = function_exists('getSetting') ? getSetting('site_name', 'Admin Panel') : 'Admin Panel';
$siteLogo    = function_exists('getSetting') ? getSetting('site_logo', '') : '';
$logoUrl     = '';
if ($siteLogo && defined('UPLOADS_URL')) {
    $logoUrl = UPLOADS_URL . ltrim($siteLogo, '/');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e($siteName) ?> Yönetim Paneli</title>
    <script>
        window.ADMIN_URL = '<?= ADMIN_URL ?>';
        // Tema flash'ını önlemek için render öncesi uygula
        (function() {
            try {
                var t = localStorage.getItem('adminTheme') || 'light';
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 264px;
            --sidebar-mini: 76px;
            --header-height: 64px;
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: rgba(79,70,229,.08);
            --sidebar-bg: #ffffff;
            --sidebar-border: #e9ecef;
            --text-base: #1f2937;
            --text-muted: #6b7280;
            --text-soft: #9ca3af;
            --hover-bg: #f3f4f6;
            --active-bg: #eef2ff;
            --active-text: #4f46e5;
            --section-color: #9ca3af;
            --shadow-sm: 0 1px 2px rgba(0,0,0,.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,.06);
            --shadow-lg: 0 10px 30px rgba(0,0,0,.08);
            --radius: 10px;
        }

        /* ─── Dark Mode Variables ─────────────────────────── */
        [data-bs-theme="dark"] {
            --primary: #818cf8;
            --primary-dark: #6366f1;
            --primary-light: rgba(129,140,248,.15);
            --sidebar-bg: #111827;
            --sidebar-border: #1f2937;
            --text-base: #e5e7eb;
            --text-muted: #9ca3af;
            --text-soft: #6b7280;
            --hover-bg: #1f2937;
            --active-bg: rgba(129,140,248,.15);
            --active-text: #a5b4fc;
            --section-color: #6b7280;
            --shadow-sm: 0 1px 2px rgba(0,0,0,.4);
            --shadow-md: 0 4px 12px rgba(0,0,0,.4);
            --shadow-lg: 0 10px 30px rgba(0,0,0,.5);
        }
        [data-bs-theme="dark"] body { background-color: #0b1220; }
        [data-bs-theme="dark"] .top-navbar { background: var(--sidebar-bg); }
        [data-bs-theme="dark"] .submenu-flyout { background: var(--sidebar-bg); }
        [data-bs-theme="dark"] .submenu-flyout::before { border-right-color: var(--sidebar-bg); }
        [data-bs-theme="dark"] .stat-card,
        [data-bs-theme="dark"] .table-card,
        [data-bs-theme="dark"] .card { background-color: var(--sidebar-bg); color: var(--text-base); }
        [data-bs-theme="dark"] .card .card-header { background-color: var(--sidebar-bg) !important; border-bottom-color: var(--sidebar-border); color: var(--text-base); }
        [data-bs-theme="dark"] .table { color: var(--text-base); --bs-table-bg: transparent; --bs-table-color: var(--text-base); --bs-table-border-color: var(--sidebar-border); --bs-table-hover-bg: var(--hover-bg); --bs-table-hover-color: var(--text-base); --bs-table-striped-bg: rgba(255,255,255,.03); }
        [data-bs-theme="dark"] .table-light,
        [data-bs-theme="dark"] thead.table-light th,
        [data-bs-theme="dark"] .table > :not(caption) > * > * { background-color: transparent; color: var(--text-base); border-color: var(--sidebar-border); }
        [data-bs-theme="dark"] thead th { background-color: rgba(255,255,255,.03) !important; color: var(--text-muted); }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select { background-color: #0f172a; border-color: var(--sidebar-border); color: var(--text-base); }
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus { background-color: #0f172a; color: var(--text-base); border-color: var(--primary); box-shadow: 0 0 0 .2rem var(--primary-light); }
        [data-bs-theme="dark"] .form-control::placeholder { color: var(--text-soft); }
        [data-bs-theme="dark"] .input-group-text { background-color: var(--hover-bg); border-color: var(--sidebar-border); color: var(--text-muted); }
        [data-bs-theme="dark"] .modal-content { background-color: var(--sidebar-bg); color: var(--text-base); border-color: var(--sidebar-border); }
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer { border-color: var(--sidebar-border); }
        [data-bs-theme="dark"] .dropdown-menu { background-color: var(--sidebar-bg); border-color: var(--sidebar-border); }
        [data-bs-theme="dark"] .dropdown-item { color: var(--text-base); }
        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus { background-color: var(--hover-bg); color: var(--text-base); }
        [data-bs-theme="dark"] .dropdown-divider { border-top-color: var(--sidebar-border); }
        [data-bs-theme="dark"] hr { border-color: var(--sidebar-border); }
        [data-bs-theme="dark"] .text-muted { color: var(--text-muted) !important; }
        [data-bs-theme="dark"] .text-dark { color: var(--text-base) !important; }
        [data-bs-theme="dark"] .bg-light { background-color: var(--hover-bg) !important; color: var(--text-base); }
        [data-bs-theme="dark"] .bg-white { background-color: var(--sidebar-bg) !important; }
        [data-bs-theme="dark"] .border { border-color: var(--sidebar-border) !important; }
        [data-bs-theme="dark"] .nav-tabs { border-bottom-color: var(--sidebar-border); }
        [data-bs-theme="dark"] .nav-tabs .nav-link { color: var(--text-muted); border-color: transparent; }
        [data-bs-theme="dark"] .nav-tabs .nav-link.active { background-color: var(--sidebar-bg); color: var(--primary); border-color: var(--sidebar-border) var(--sidebar-border) var(--sidebar-bg); }
        [data-bs-theme="dark"] .pagination .page-link { background-color: var(--sidebar-bg); border-color: var(--sidebar-border); color: var(--text-base); }
        [data-bs-theme="dark"] .pagination .page-item.active .page-link { background-color: var(--primary); border-color: var(--primary); }
        [data-bs-theme="dark"] .alert-info { background-color: rgba(13,202,240,.1); border-color: rgba(13,202,240,.3); color: #6edff6; }
        [data-bs-theme="dark"] .alert-success { background-color: rgba(25,135,84,.1); border-color: rgba(25,135,84,.3); color: #75b798; }
        [data-bs-theme="dark"] .alert-warning { background-color: rgba(255,193,7,.1); border-color: rgba(255,193,7,.3); color: #ffda6a; }
        [data-bs-theme="dark"] .alert-danger { background-color: rgba(220,53,69,.1); border-color: rgba(220,53,69,.3); color: #ea868f; }
        [data-bs-theme="dark"] .badge.bg-light { background-color: var(--hover-bg) !important; color: var(--text-base) !important; }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection { background-color: #0f172a; border-color: var(--sidebar-border); color: var(--text-base); }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered { color: var(--text-base) !important; }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown { background-color: var(--sidebar-bg); border-color: var(--sidebar-border); }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted { background-color: var(--primary) !important; }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate { color: var(--text-muted); }
        [data-bs-theme="dark"] .btn-close { filter: invert(1) brightness(2); }
        [data-bs-theme="dark"] .modal-header .btn-close { filter: brightness(0) invert(1); }

        * { box-sizing: border-box; }

        body {
            background-color: #f7f8fb;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-base);
            margin: 0;
            transition: background-color .25s ease;
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,.25); }

        /* ─── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: width .28s cubic-bezier(.4,0,.2,1);
        }

        /* ─── Sidebar Brand ─────────────────────────────────── */
        .sidebar-brand {
            height: var(--header-height);
            display: flex; align-items: center;
            padding: 0 1.25rem;
            color: var(--text-base);
            text-decoration: none;
            border-bottom: 1px solid var(--sidebar-border);
            gap: .65rem;
            flex-shrink: 0;
            white-space: nowrap;
            overflow: hidden;
            transition: padding .28s;
        }
        .sidebar-brand:hover { color: var(--primary); }
        .sidebar-brand-logo {
            width: 36px; height: 36px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(79,70,229,.3);
            overflow: hidden;
        }
        .sidebar-brand-logo img { width: 100%; height: 100%; object-fit: cover; }
        .sidebar-brand-text {
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.1;
            transition: opacity .2s;
        }
        .sidebar-brand-text small {
            display: block;
            font-weight: 500;
            font-size: .68rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-top: 2px;
        }

        /* ─── Sidebar Nav ─────────────────────────────────── */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .75rem .75rem 1.5rem;
        }

        .nav-section {
            color: var(--section-color);
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem .75rem .4rem;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity .2s;
        }
        .nav-section:first-child { padding-top: .25rem; }

        .sidebar-nav .nav-link {
            color: var(--text-muted);
            padding: .65rem .75rem;
            margin-bottom: 2px;
            border-radius: var(--radius);
            display: flex; align-items: center;
            gap: .8rem;
            font-size: .92rem;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            transition: background .18s, color .18s, padding .28s;
            position: relative;
        }
        .sidebar-nav .nav-link i.nav-link-icon {
            font-size: 1.1rem;
            width: 22px; height: 22px;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: color .18s;
        }
        .sidebar-nav .nav-link:hover {
            background: var(--hover-bg);
            color: var(--text-base);
        }
        .sidebar-nav .nav-link.active {
            background: var(--active-bg);
            color: var(--active-text);
            font-weight: 600;
        }
        .sidebar-nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: -3px; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 22px;
            background: var(--primary);
            border-radius: 0 3px 3px 0;
        }
        .sidebar-nav .nav-link.active i.nav-link-icon { color: var(--primary); }
        .sidebar-chevron {
            margin-left: auto;
            font-size: .7rem !important;
            transition: transform .2s, opacity .2s;
        }
        .sidebar-nav .nav-link[aria-expanded="true"] .sidebar-chevron { transform: rotate(180deg); }

        .nav-link-text { transition: opacity .2s; flex: 1; }
        .nav-link-badge {
            background: var(--primary);
            color: #fff;
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
        }

        /* ─── Submenu (Collapse) ─────────────────────────────── */
        .nav-item-group { position: relative; }
        .nav-submenu {
            margin: 2px 0 6px;
            padding-left: 0;
            list-style: none;
        }
        .nav-submenu .nav-link {
            padding-left: 2.6rem;
            font-size: .87rem;
            font-weight: 500;
        }
        .nav-submenu .nav-link i.nav-link-icon { font-size: .95rem; }

        /* ─── Flyout (Collapsed mode hover menu) ─────────────── */
        .submenu-flyout {
            display: none;
            position: fixed;
            left: var(--sidebar-mini);
            background: #fff;
            min-width: 220px;
            border: 1px solid var(--sidebar-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 1100;
            padding: .35rem;
            margin-left: 12px;
        }
        /* Görünmez köprü — sidebar ile flyout arası boşluk üzerinden mouse geçişi sağlar */
        .submenu-flyout::after {
            content: '';
            position: absolute;
            left: -14px; top: 0;
            width: 14px; height: 100%;
        }
        .submenu-flyout::before {
            content: '';
            position: absolute;
            left: -8px; top: 14px;
            border: 6px solid transparent;
            border-right-color: #fff;
            z-index: 1;
        }
        .submenu-flyout .flyout-header {
            color: var(--text-muted);
            font-size: .7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .8px;
            padding: .55rem .75rem .35rem;
            border-bottom: 1px solid var(--sidebar-border);
            margin-bottom: .25rem;
        }
        .submenu-flyout .flyout-item {
            display: flex; align-items: center; gap: .55rem;
            color: var(--text-muted);
            padding: .55rem .7rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: .87rem; font-weight: 500;
            transition: background .15s, color .15s;
        }
        .submenu-flyout .flyout-item i { width: 18px; flex-shrink: 0; }
        .submenu-flyout .flyout-item:hover {
            background: var(--hover-bg);
            color: var(--text-base);
        }
        .submenu-flyout .flyout-item.active {
            background: var(--active-bg);
            color: var(--active-text);
            font-weight: 600;
        }

        /* ─── Sidebar Footer ─────────────────────────────────── */
        .sidebar-footer {
            padding: .75rem;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }
        .sidebar-user {
            display: flex; align-items: center;
            gap: .65rem;
            padding: .55rem .65rem;
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text-base);
            transition: background .18s;
        }
        .sidebar-user:hover { background: var(--hover-bg); color: var(--text-base); }
        .sidebar-user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem;
            flex-shrink: 0;
        }
        .sidebar-user-info {
            line-height: 1.2;
            overflow: hidden;
            transition: opacity .2s;
        }
        .sidebar-user-info .name {
            font-weight: 600; font-size: .85rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-user-info .role {
            font-size: .7rem; color: var(--text-muted);
        }

        /* ─── Collapsed State ─────────────────────────────────── */
        .sidebar.collapsed { width: var(--sidebar-mini); }
        .sidebar.collapsed .sidebar-brand {
            justify-content: center;
            padding: 0;
        }
        .sidebar.collapsed .sidebar-brand-text,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-link-text,
        .sidebar.collapsed .sidebar-chevron,
        .sidebar.collapsed .nav-link-badge,
        .sidebar.collapsed .sidebar-user-info {
            display: none !important;
        }
        .sidebar.collapsed .sidebar-nav { padding-left: .5rem; padding-right: .5rem; }
        .sidebar.collapsed .sidebar-nav .nav-link {
            justify-content: center !important;
            align-items: center;
            padding: .65rem 0 !important;
            gap: 0 !important;
            width: 100%;
        }
        .sidebar.collapsed .sidebar-nav .nav-link i.nav-link-icon {
            margin: 0 auto;
            width: 22px;
        }
        .sidebar.collapsed .sidebar-nav .nav-link.active::before { left: -2px; }
        .sidebar.collapsed .sidebar-nav .nav-submenu,
        .sidebar.collapsed .collapse { display: none !important; }
        .sidebar.collapsed .sidebar-user { justify-content: center; padding: .55rem 0; }
        .sidebar.collapsed .nav-item-group.flyout-open > .submenu-flyout { display: block; }
        .sidebar.collapsed .nav-section {
            padding-top: 0; padding-bottom: 0; height: 0;
        }
        .sidebar:not(.collapsed) .submenu-flyout { display: none !important; }

        /* ─── Main Content ─────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left .28s cubic-bezier(.4,0,.2,1);
        }
        .main-content.sidebar-collapsed { margin-left: var(--sidebar-mini); }

        /* ─── Top Navbar ─────────────────────────────────── */
        .top-navbar {
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky; top: 0;
            z-index: 999;
        }
        .top-navbar-left { display: flex; align-items: center; gap: .75rem; }
        .top-navbar-right { display: flex; align-items: center; gap: .65rem; }

        #sidebarToggle {
            background: transparent;
            border: 1px solid var(--sidebar-border);
            border-radius: 8px;
            width: 38px; height: 38px;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--text-muted);
            font-size: 1.05rem;
            transition: background .18s, color .18s, border-color .18s;
            cursor: pointer;
        }
        #sidebarToggle:hover {
            background: var(--hover-bg);
            color: var(--primary);
            border-color: var(--primary);
        }

        .topbar-btn {
            background: transparent;
            border: 1px solid var(--sidebar-border);
            border-radius: 8px;
            padding: .45rem .85rem;
            display: inline-flex; align-items: center;
            gap: .4rem;
            color: var(--text-base);
            text-decoration: none;
            font-size: .85rem; font-weight: 500;
            transition: background .18s, border-color .18s, color .18s;
        }
        .topbar-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }
        .topbar-btn i { font-size: .95rem; }

        .topbar-icon-btn {
            background: transparent;
            border: 1px solid var(--sidebar-border);
            border-radius: 8px;
            width: 38px; height: 38px;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--text-muted);
            font-size: 1rem;
            position: relative;
            transition: all .18s;
            cursor: pointer;
        }
        .topbar-icon-btn:hover {
            color: var(--primary);
            border-color: var(--primary);
            background: var(--primary-light);
        }
        .topbar-icon-btn .badge-dot {
            position: absolute;
            top: 6px; right: 7px;
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .top-user-dropdown .dropdown-toggle {
            display: flex; align-items: center; gap: .55rem;
            background: transparent;
            border: 1px solid var(--sidebar-border);
            border-radius: 8px;
            padding: .3rem .65rem .3rem .35rem;
            color: var(--text-base);
            text-decoration: none;
            transition: all .18s;
        }
        .top-user-dropdown .dropdown-toggle:hover {
            background: var(--hover-bg);
            border-color: var(--primary);
        }
        .top-user-dropdown .dropdown-toggle::after { display: none; }
        .top-user-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .78rem;
        }
        .top-user-name {
            font-size: .85rem; font-weight: 500;
            line-height: 1.1;
        }

        /* ─── Cards ─────────────────────────────────── */
        .content-wrapper { padding: 1.5rem; }
        .stat-card { border: none; border-radius: var(--radius); box-shadow: var(--shadow-sm); }
        .stat-card .card-body { padding: 1.25rem; }
        .stat-card .stat-icon {
            width: 48px; height: 48px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .table-card { border: none; border-radius: var(--radius); box-shadow: var(--shadow-sm); }

        /* ─── Language tabs ─────────────────────────────────── */
        .lang-tabs .nav-link { border-radius: 0; border: 1px solid #dee2e6; margin-right: -1px; }
        .lang-tabs .nav-link.active { background-color: var(--primary); border-color: var(--primary); color: #fff; }

        /* ─── Mobile Overlay ─────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 1030;
            opacity: 0;
            transition: opacity .25s;
        }
        .sidebar-overlay.show { display: block; opacity: 1; }

        /* ─── Responsive ─────────────────────────────────── */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .3s, width .28s;
                box-shadow: var(--shadow-lg);
            }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .top-user-name { display: none; }
        }

        @media (max-width: 575px) {
            .topbar-btn span { display: none; }
            .content-wrapper { padding: 1rem; }
        }
    </style>
</head>
<body>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<nav class="sidebar" id="mainSidebar">
    <a href="<?= ADMIN_URL ?>" class="sidebar-brand" title="<?= e($siteName) ?>">
        <span class="sidebar-brand-logo">
            <?php if ($logoUrl): ?>
                <img src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>">
            <?php else: ?>
                <i class="bi bi-globe2"></i>
            <?php endif; ?>
        </span>
        <span class="sidebar-brand-text">
            <?= e($siteName) ?>
            <small>Yönetim Paneli</small>
        </span>
    </a>

    <div class="sidebar-nav">
        <div class="nav-section">Genel</div>
        <a href="<?= ADMIN_URL ?>/index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" title="Dashboard">
            <i class="bi bi-grid-1x2 nav-link-icon"></i>
            <span class="nav-link-text">Dashboard</span>
        </a>

        <div class="nav-section">Tur Yönetimi</div>

        <!-- Rezervasyonlar (flyout destekli) -->
        <div class="nav-item-group">
            <a href="#bookingsSubmenu" class="nav-link <?= $currentPage === 'bookings' ? 'active' : '' ?>"
               data-bs-toggle="collapse" role="button" aria-expanded="<?= $currentPage === 'bookings' ? 'true' : 'false' ?>" title="Rezervasyonlar">
                <i class="bi bi-calendar2-check nav-link-icon"></i>
                <span class="nav-link-text">Rezervasyonlar</span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </a>
            <div class="collapse <?= $currentPage === 'bookings' ? 'show' : '' ?>" id="bookingsSubmenu">
                <div class="nav-submenu">
                    <a href="<?= ADMIN_URL ?>/bookings.php?view=all" class="nav-link <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'all') ? 'active' : '' ?>">
                        <i class="bi bi-list-ul nav-link-icon"></i>
                        <span class="nav-link-text">Tüm Rezervasyonlar</span>
                    </a>
                    <a href="<?= ADMIN_URL ?>/bookings.php?view=arrival" class="nav-link <?= ($currentPage === 'bookings' && ($_GET['view'] ?? 'arrival') === 'arrival') ? 'active' : '' ?>">
                        <i class="bi bi-airplane-engines nav-link-icon"></i>
                        <span class="nav-link-text">Geliş</span>
                    </a>
                    <a href="<?= ADMIN_URL ?>/bookings.php?view=return" class="nav-link <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'return') ? 'active' : '' ?>">
                        <i class="bi bi-airplane nav-link-icon"></i>
                        <span class="nav-link-text">Dönüş</span>
                    </a>
                </div>
            </div>
            <!-- Collapsed flyout -->
            <div class="submenu-flyout">
                <div class="flyout-header">Rezervasyonlar</div>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=all" class="flyout-item <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'all') ? 'active' : '' ?>">
                    <i class="bi bi-list-ul"></i> Tüm Rezervasyonlar
                </a>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=arrival" class="flyout-item <?= ($currentPage === 'bookings' && ($_GET['view'] ?? 'arrival') === 'arrival') ? 'active' : '' ?>">
                    <i class="bi bi-airplane-engines"></i> Geliş
                </a>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=return" class="flyout-item <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'return') ? 'active' : '' ?>">
                    <i class="bi bi-airplane"></i> Dönüş
                </a>
            </div>
        </div>

        <a href="<?= ADMIN_URL ?>/tours.php" class="nav-link <?= $currentPage === 'tours' ? 'active' : '' ?>" title="Turlar">
            <i class="bi bi-compass nav-link-icon"></i>
            <span class="nav-link-text">Turlar</span>
        </a>
        <a href="<?= ADMIN_URL ?>/destinations.php" class="nav-link <?= $currentPage === 'destinations' ? 'active' : '' ?>" title="Transferler">
            <i class="bi bi-geo-alt nav-link-icon"></i>
            <span class="nav-link-text">Transferler</span>
        </a>
        <a href="<?= ADMIN_URL ?>/vehicles.php" class="nav-link <?= $currentPage === 'vehicles' ? 'active' : '' ?>" title="Araçlar">
            <i class="bi bi-car-front nav-link-icon"></i>
            <span class="nav-link-text">Araçlar</span>
        </a>
        <a href="<?= ADMIN_URL ?>/hotels.php" class="nav-link <?= $currentPage === 'hotels' ? 'active' : '' ?>" title="Oteller">
            <i class="bi bi-building nav-link-icon"></i>
            <span class="nav-link-text">Oteller</span>
        </a>
        <a href="<?= ADMIN_URL ?>/outsource_partners.php" class="nav-link <?= $currentPage === 'outsource_partners' ? 'active' : '' ?>" title="Dış Partnerler">
            <i class="bi bi-people nav-link-icon"></i>
            <span class="nav-link-text">Dış Partnerler</span>
        </a>

        <div class="nav-section">İçerik</div>
        <a href="<?= ADMIN_URL ?>/homepage.php" class="nav-link <?= $currentPage === 'homepage' ? 'active' : '' ?>" title="Anasayfa">
            <i class="bi bi-house-door nav-link-icon"></i>
            <span class="nav-link-text">Anasayfa</span>
        </a>
        <a href="<?= ADMIN_URL ?>/sections.php" class="nav-link <?= $currentPage === 'sections' ? 'active' : '' ?>" title="Section'lar">
            <i class="bi bi-layout-text-window nav-link-icon"></i>
            <span class="nav-link-text">Section'lar</span>
        </a>
        <a href="<?= ADMIN_URL ?>/menus.php" class="nav-link <?= $currentPage === 'menus' ? 'active' : '' ?>" title="Menüler">
            <i class="bi bi-list-task nav-link-icon"></i>
            <span class="nav-link-text">Menüler</span>
        </a>
        <a href="<?= ADMIN_URL ?>/sliders.php" class="nav-link <?= $currentPage === 'sliders' ? 'active' : '' ?>" title="Slider">
            <i class="bi bi-images nav-link-icon"></i>
            <span class="nav-link-text">Slider</span>
        </a>
        <a href="<?= ADMIN_URL ?>/media.php" class="nav-link <?= $currentPage === 'media' ? 'active' : '' ?>" title="Medya">
            <i class="bi bi-collection nav-link-icon"></i>
            <span class="nav-link-text">Medya Kütüphanesi</span>
        </a>

        <div class="nav-section">Sistem</div>
        <a href="<?= ADMIN_URL ?>/languages.php" class="nav-link <?= $currentPage === 'languages' ? 'active' : '' ?>" title="Diller">
            <i class="bi bi-translate nav-link-icon"></i>
            <span class="nav-link-text">Diller</span>
        </a>
        <a href="<?= ADMIN_URL ?>/settings.php" class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" title="Site Ayarları">
            <i class="bi bi-gear nav-link-icon"></i>
            <span class="nav-link-text">Site Ayarları</span>
        </a>
        <a href="<?= ADMIN_URL ?>/users.php" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" title="Kullanıcılar">
            <i class="bi bi-person-badge nav-link-icon"></i>
            <span class="nav-link-text">Kullanıcılar</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <a href="<?= ADMIN_URL ?>/profile.php" class="sidebar-user" title="<?= e($adminName) ?>">
            <span class="sidebar-user-avatar"><?= e(mb_strtoupper(mb_substr($adminName, 0, 1))) ?></span>
            <div class="sidebar-user-info">
                <div class="name"><?= e($adminName) ?></div>
                <div class="role">Yönetici</div>
            </div>
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="top-navbar-left">
            <button type="button" id="sidebarToggle" title="Menüyü Aç/Kapat" aria-label="Menüyü Aç/Kapat">
                <i class="bi bi-list"></i>
            </button>
            <?php if (isset($pageTitle)): ?>
            <div class="d-none d-md-block">
                <div class="fw-semibold" style="font-size:.95rem;line-height:1.1;"><?= e($pageTitle) ?></div>
                <div style="font-size:.7rem;color:var(--text-muted);"><?= date('d F Y, l') ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="top-navbar-right">
            <a href="<?= defined('SITE_URL') ? SITE_URL : '/' ?>" target="_blank" class="topbar-btn" title="Siteyi yeni sekmede aç">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>Siteyi Görüntüle</span>
            </a>
            <button type="button" id="themeToggle" class="topbar-icon-btn" title="Tema değiştir" aria-label="Tema değiştir">
                <i class="bi bi-moon-stars" id="themeIconDark"></i>
                <i class="bi bi-sun" id="themeIconLight" style="display:none;"></i>
            </button>
            <div class="dropdown top-user-dropdown">
                <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="top-user-avatar"><?= e(mb_strtoupper(mb_substr($adminName, 0, 1))) ?></span>
                    <span class="top-user-name d-none d-md-inline"><?= e($adminName) ?></span>
                    <i class="bi bi-chevron-down small ms-1 text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:10px;border:1px solid var(--sidebar-border);">
                    <li class="px-3 py-2">
                        <div class="fw-semibold small"><?= e($adminName) ?></div>
                        <div class="text-muted" style="font-size:.72rem;">Yönetici</div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item small" href="<?= ADMIN_URL ?>/profile.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item small" href="<?= ADMIN_URL ?>/settings.php"><i class="bi bi-gear me-2"></i>Ayarlar</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item small text-danger" href="<?= ADMIN_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content-wrapper">
        <?php
        $flash = function_exists('getFlashMessage') ? getFlashMessage() : null;
        if ($flash):
        ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
