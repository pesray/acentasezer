<?php
/**
 * Admin Dashboard — Tüm rezervasyonların listelendiği yönetim ekranı
 * (bookings.php ile aynı içeriği gösterir, sadece tarih filtresi bugüne kilitlenmez
 *  ve varsayılan tab "Tüm Rezervasyonlar" olur)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
define('BOOKINGS_AS_DASHBOARD', true);

require_once __DIR__ . '/bookings.php';
