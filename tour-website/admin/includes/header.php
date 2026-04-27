<?php
/**
 * Admin Panel Header
 */

require_once __DIR__ . '/auth.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Admin Panel</title>
    <script>window.ADMIN_URL = '<?= ADMIN_URL ?>';</script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Summernote Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-mini: 68px;
            --header-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --sidebar-bg: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        }

        body { background-color: #f8f9fc; font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

        /* ── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.28s ease;
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.2rem; font-weight: 700;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap; overflow: hidden;
            gap: .5rem;
        }

        .sidebar-nav { padding: 1rem 0; }

        .nav-section {
            color: rgba(255,255,255,0.4); font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; padding: 1rem 1.5rem 0.5rem;
            white-space: nowrap; overflow: hidden;
            transition: opacity .2s;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
            transition: background .2s, padding .28s;
            white-space: nowrap; overflow: hidden;
        }
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); }
        .sidebar-nav .nav-link i { font-size: 1rem; width: 1.25rem; flex-shrink: 0; }

        .sidebar-nav .nav-sub-link { padding-left: 3rem !important; font-size: 0.9rem; }
        .sidebar-chevron { font-size: .7rem !important; transition: opacity .2s; }
        .nav-link-text { transition: opacity .2s; }

        /* Nav item group (for flyout) */
        .nav-item-group { position: relative; }

        /* Flyout submenu — hidden by default */
        .submenu-flyout {
            display: none;
            position: fixed;
            left: var(--sidebar-mini);
            background: var(--sidebar-bg);
            min-width: 210px;
            border-radius: 0 10px 10px 0;
            z-index: 1100;
            box-shadow: 6px 0 20px rgba(0,0,0,.3);
            overflow: hidden;
        }
        .submenu-flyout .flyout-header {
            color: rgba(255,255,255,.5); font-size: .7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .5px;
            padding: .7rem 1rem .4rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .submenu-flyout .flyout-item {
            display: flex; align-items: center; gap: .5rem;
            color: rgba(255,255,255,.85); padding: .6rem 1rem;
            text-decoration: none; font-size: .9rem;
            transition: background .15s;
        }
        .submenu-flyout .flyout-item:hover { color: #fff; background: rgba(255,255,255,.12); }
        .submenu-flyout .flyout-item.active { color: #fff; background: rgba(255,255,255,.15); font-weight: 600; }

        /* ── Collapsed state ─────────────────────────────────── */
        .sidebar.collapsed { width: var(--sidebar-mini); overflow: visible; }
        .sidebar.collapsed .nav-link-text,
        .sidebar.collapsed .sidebar-brand-text,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .sidebar-chevron { opacity: 0; width: 0; overflow: hidden; }
        .sidebar.collapsed .sidebar-nav .nav-link { justify-content: center !important; padding: .75rem 0; gap: 0; }
        .sidebar.collapsed .sidebar-nav .nav-link > span { gap: 0 !important; }
        .sidebar.collapsed .sidebar-nav .nav-link i { width: auto; font-size: 1.15rem; }
        .sidebar.collapsed .collapse { display: none !important; }
        /* Show flyout on hover when collapsed */
        .sidebar.collapsed .nav-item-group:hover .submenu-flyout { display: block; }

        /* ── Main Content ────────────────────────────────────── */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin-left .28s ease; }
        .main-content.sidebar-collapsed { margin-left: var(--sidebar-mini); }

        /* ── Top Navbar ──────────────────────────────────────── */
        .top-navbar {
            height: var(--header-height); background: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,.15);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; position: sticky; top: 0; z-index: 999;
        }
        #sidebarToggle { color: #858796; transition: color .2s; }
        #sidebarToggle:hover { color: #4e73df; }

        /* ── Cards ───────────────────────────────────────────── */
        .content-wrapper { padding: 1.5rem; }
        .stat-card { border: none; border-radius: .5rem; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.1); }
        .stat-card .card-body { padding: 1.25rem; }
        .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .table-card { border: none; border-radius: .5rem; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.1); }

        /* ── Language tabs ───────────────────────────────────── */
        .lang-tabs .nav-link { border-radius: 0; border: 1px solid #dee2e6; margin-right: -1px; }
        .lang-tabs .nav-link.active { background-color: var(--primary-color); border-color: var(--primary-color); color: #fff; }

        /* ── Responsive ──────────────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s, width .28s; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="mainSidebar">
    <a href="<?= ADMIN_URL ?>" class="sidebar-brand">
        <i class="bi bi-globe2" style="flex-shrink:0;"></i>
        <span class="sidebar-brand-text">Tour Admin</span>
    </a>

    <div class="sidebar-nav">
        <div class="nav-section">Ana</div>
        <a href="<?= ADMIN_URL ?>/index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" title="Dashboard">
            <i class="bi bi-speedometer2"></i> <span class="nav-link-text">Dashboard</span>
        </a>

        <div class="nav-section">Tur Yönetimi</div>

        <!-- Rezervasyonlar (flyout destekli) -->
        <div class="nav-item-group">
            <a href="#bookingsSubmenu" class="nav-link d-flex justify-content-between align-items-center <?= $currentPage === 'bookings' ? 'active' : '' ?>"
               data-bs-toggle="collapse" role="button" aria-expanded="<?= $currentPage === 'bookings' ? 'true' : 'false' ?>" title="Rezervasyonlar">
                <span class="d-flex align-items-center gap-3">
                    <i class="bi bi-calendar-check"></i>
                    <span class="nav-link-text">Rezervasyonlar</span>
                </span>
                <i class="bi bi-chevron-down sidebar-chevron"></i>
            </a>
            <div class="collapse <?= $currentPage === 'bookings' ? 'show' : '' ?>" id="bookingsSubmenu">
                <a href="<?= ADMIN_URL ?>/bookings.php?view=all" class="nav-link nav-sub-link <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'all') ? 'active' : '' ?>">
                    <i class="bi bi-list-ul"></i> <span class="nav-link-text">Tüm Rezervasyonlar</span>
                </a>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=arrival" class="nav-link nav-sub-link <?= ($currentPage === 'bookings' && ($_GET['view'] ?? 'arrival') === 'arrival') ? 'active' : '' ?>">
                    <i class="bi bi-box-arrow-in-down-right"></i> <span class="nav-link-text">Geliş Rezervasyonları</span>
                </a>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=return" class="nav-link nav-sub-link <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'return') ? 'active' : '' ?>">
                    <i class="bi bi-box-arrow-up-right"></i> <span class="nav-link-text">Dönüş Rezervasyonları</span>
                </a>
            </div>
            <!-- Collapsed flyout -->
            <div class="submenu-flyout">
                <div class="flyout-header">Rezervasyonlar</div>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=all" class="flyout-item <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'all') ? 'active' : '' ?>">
                    <i class="bi bi-list-ul"></i> Tüm Rezervasyonlar
                </a>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=arrival" class="flyout-item <?= ($currentPage === 'bookings' && ($_GET['view'] ?? 'arrival') === 'arrival') ? 'active' : '' ?>">
                    <i class="bi bi-box-arrow-in-down-right"></i> Geliş Rezervasyonları
                </a>
                <a href="<?= ADMIN_URL ?>/bookings.php?view=return" class="flyout-item <?= ($currentPage === 'bookings' && ($_GET['view'] ?? '') === 'return') ? 'active' : '' ?>">
                    <i class="bi bi-box-arrow-up-right"></i> Dönüş Rezervasyonları
                </a>
            </div>
        </div>

        <a href="<?= ADMIN_URL ?>/tours.php" class="nav-link <?= $currentPage === 'tours' ? 'active' : '' ?>" title="Turlar">
            <i class="bi bi-compass"></i> <span class="nav-link-text">Turlar</span>
        </a>
        <a href="<?= ADMIN_URL ?>/destinations.php" class="nav-link <?= $currentPage === 'destinations' ? 'active' : '' ?>" title="Transferler">
            <i class="bi bi-geo-alt"></i> <span class="nav-link-text">Transferler</span>
        </a>
        <a href="<?= ADMIN_URL ?>/vehicles.php" class="nav-link <?= $currentPage === 'vehicles' ? 'active' : '' ?>" title="Araçlar">
            <i class="bi bi-car-front"></i> <span class="nav-link-text">Araçlar</span>
        </a>
        <a href="<?= ADMIN_URL ?>/hotels.php" class="nav-link <?= $currentPage === 'hotels' ? 'active' : '' ?>" title="Oteller">
            <i class="bi bi-building"></i> <span class="nav-link-text">Oteller</span>
        </a>

        <div class="nav-section">İçerik Yönetimi</div>
        <a href="<?= ADMIN_URL ?>/homepage.php" class="nav-link <?= $currentPage === 'homepage' ? 'active' : '' ?>" title="Anasayfa">
            <i class="bi bi-house-door"></i> <span class="nav-link-text">Anasayfa</span>
        </a>
        <a href="<?= ADMIN_URL ?>/sections.php" class="nav-link <?= $currentPage === 'sections' ? 'active' : '' ?>" title="Section'lar">
            <i class="bi bi-layout-text-window"></i> <span class="nav-link-text">Section'lar</span>
        </a>
        <a href="<?= ADMIN_URL ?>/menus.php" class="nav-link <?= $currentPage === 'menus' ? 'active' : '' ?>" title="Menüler">
            <i class="bi bi-list"></i> <span class="nav-link-text">Menüler</span>
        </a>
        <a href="<?= ADMIN_URL ?>/sliders.php" class="nav-link <?= $currentPage === 'sliders' ? 'active' : '' ?>" title="Slider">
            <i class="bi bi-images"></i> <span class="nav-link-text">Slider</span>
        </a>

        <div class="nav-section">Blog & Medya</div>
        <a href="<?= ADMIN_URL ?>/media.php" class="nav-link <?= $currentPage === 'media' ? 'active' : '' ?>" title="Medya Yönetimi">
            <i class="bi bi-collection"></i> <span class="nav-link-text">Medya Yönetimi</span>
        </a>

        <div class="nav-section">Ayarlar</div>
        <a href="<?= ADMIN_URL ?>/languages.php" class="nav-link <?= $currentPage === 'languages' ? 'active' : '' ?>" title="Diller">
            <i class="bi bi-translate"></i> <span class="nav-link-text">Diller</span>
        </a>
        <a href="<?= ADMIN_URL ?>/settings.php" class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" title="Site Ayarları">
            <i class="bi bi-gear"></i> <span class="nav-link-text">Site Ayarları</span>
        </a>
        <a href="<?= ADMIN_URL ?>/users.php" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" title="Kullanıcılar">
            <i class="bi bi-people"></i> <span class="nav-link-text">Kullanıcılar</span>
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <button class="btn btn-link px-2" id="sidebarToggle" title="Menüyü Aç/Kapat">
            <i class="bi bi-layout-sidebar-inset fs-5"></i>
        </button>

        <div class="d-flex align-items-center gap-3">
            <a href="<?= SITE_URL ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-up-right"></i> Siteyi Görüntüle
            </a>
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle text-decoration-none text-dark" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> <?= e($adminName) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= ADMIN_URL ?>/profile.php"><i class="bi bi-person me-2"></i> Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= ADMIN_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Çıkış</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content-wrapper">
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
