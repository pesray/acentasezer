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
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Admin Panel</title>
    
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
            --header-height: 60px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-section {
            color: rgba(255,255,255,0.4);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1rem 1.5rem 0.5rem;
        }
        
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar-nav .nav-link i {
            font-size: 1rem;
            width: 1.25rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .top-navbar {
            height: var(--header-height);
            background: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .content-wrapper {
            padding: 1.5rem;
        }
        
        /* Cards */
        .stat-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
        }
        
        .stat-card .card-body {
            padding: 1.25rem;
        }
        
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Tables */
        .table-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
        }
        
        /* Language tabs */
        .lang-tabs .nav-link {
            border-radius: 0;
            border: 1px solid #dee2e6;
            margin-right: -1px;
        }
        
        .lang-tabs .nav-link.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar">
    <a href="<?= ADMIN_URL ?>" class="sidebar-brand">
        <i class="bi bi-globe2 me-2"></i> Tour Admin
    </a>
    
    <div class="sidebar-nav">
        <div class="nav-section">Ana</div>
        <a href="<?= ADMIN_URL ?>/index.php" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        
        <div class="nav-section">İçerik Yönetimi</div>
        <a href="<?= ADMIN_URL ?>/homepage.php" class="nav-link <?= $currentPage === 'homepage' ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i> Anasayfa
        </a>
        <a href="<?= ADMIN_URL ?>/pages.php" class="nav-link <?= $currentPage === 'pages' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i> Sayfalar
        </a>
        <a href="<?= ADMIN_URL ?>/sections.php" class="nav-link <?= $currentPage === 'sections' ? 'active' : '' ?>">
            <i class="bi bi-layout-text-window"></i> Section'lar
        </a>
        <a href="<?= ADMIN_URL ?>/menus.php" class="nav-link <?= $currentPage === 'menus' ? 'active' : '' ?>">
            <i class="bi bi-list"></i> Menüler
        </a>
        <a href="<?= ADMIN_URL ?>/sliders.php" class="nav-link <?= $currentPage === 'sliders' ? 'active' : '' ?>">
            <i class="bi bi-images"></i> Slider
        </a>
        
        <div class="nav-section">Tur Yönetimi</div>
        <a href="<?= ADMIN_URL ?>/tours.php" class="nav-link <?= $currentPage === 'tours' ? 'active' : '' ?>">
            <i class="bi bi-compass"></i> Turlar
        </a>
        <a href="<?= ADMIN_URL ?>/destinations.php" class="nav-link <?= $currentPage === 'destinations' ? 'active' : '' ?>">
            <i class="bi bi-geo-alt"></i> Transferler
        </a>
        <a href="<?= ADMIN_URL ?>/vehicles.php" class="nav-link <?= $currentPage === 'vehicles' ? 'active' : '' ?>">
            <i class="bi bi-car-front"></i> Araçlar
        </a>
        <a href="<?= ADMIN_URL ?>/bookings.php" class="nav-link <?= $currentPage === 'bookings' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i> Rezervasyonlar
        </a>
        
        <div class="nav-section">Blog & Medya</div>
        <a href="<?= ADMIN_URL ?>/media.php" class="nav-link <?= $currentPage === 'media' ? 'active' : '' ?>">
            <i class="bi bi-collection"></i> Medya Yönetimi
        </a>
        <a href="<?= ADMIN_URL ?>/blog.php" class="nav-link <?= $currentPage === 'blog' ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i> Blog Yazıları
        </a>
        <a href="<?= ADMIN_URL ?>/gallery.php" class="nav-link <?= $currentPage === 'gallery' ? 'active' : '' ?>">
            <i class="bi bi-image"></i> Galeri
        </a>
        <a href="<?= ADMIN_URL ?>/testimonials.php" class="nav-link <?= $currentPage === 'testimonials' ? 'active' : '' ?>">
            <i class="bi bi-chat-quote"></i> Yorumlar
        </a>
        
        <div class="nav-section">Diğer</div>
        <a href="<?= ADMIN_URL ?>/faq.php" class="nav-link <?= $currentPage === 'faq' ? 'active' : '' ?>">
            <i class="bi bi-question-circle"></i> SSS
        </a>
        <a href="<?= ADMIN_URL ?>/contacts.php" class="nav-link <?= $currentPage === 'contacts' ? 'active' : '' ?>">
            <i class="bi bi-envelope"></i> Mesajlar
        </a>
        
        <div class="nav-section">Ayarlar</div>
        <a href="<?= ADMIN_URL ?>/languages.php" class="nav-link <?= $currentPage === 'languages' ? 'active' : '' ?>">
            <i class="bi bi-translate"></i> Diller
        </a>
        <a href="<?= ADMIN_URL ?>/settings.php" class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> Site Ayarları
        </a>
        <a href="<?= ADMIN_URL ?>/users.php" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Kullanıcılar
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <button class="btn btn-link d-md-none" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
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
