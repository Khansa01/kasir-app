<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . 'auth/login.php');
    exit;
}
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'KasirKu' ?> — KasirKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>assets/style.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <i data-lucide="receipt"></i>
            </div>
            <span class="brand-name">KasirKu</span>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= $base_url ?>pages/dashboard.php"
               class="nav-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" class="nav-icon"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="<?= $base_url ?>pages/transaksi.php"
               class="nav-item <?= $current_page === 'transaksi' ? 'active' : '' ?>">
                <i data-lucide="shopping-cart" class="nav-icon"></i>
                <span class="nav-label">Transaksi</span>
            </a>
            <?php if ($role === 'admin'): ?>
            <a href="<?= $base_url ?>pages/produk.php"
               class="nav-item <?= $current_page === 'produk' ? 'active' : '' ?>">
                <i data-lucide="package" class="nav-icon"></i>
                <span class="nav-label">Produk</span>
            </a>
            <a href="<?= $base_url ?>pages/laporan.php"
               class="nav-item <?= $current_page === 'laporan' ? 'active' : '' ?>">
                <i data-lucide="bar-chart-2" class="nav-icon"></i>
                <span class="nav-label">Laporan</span>
            </a>
            <a href="<?= $base_url ?>pages/pengguna.php"
               class="nav-item <?= $current_page === 'pengguna' ? 'active' : '' ?>">
                <i data-lucide="users" class="nav-icon"></i>
                <span class="nav-label">Pengguna</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?></div>
                <div class="user-texts">
                    <div class="user-name"><?= clean($_SESSION['user_nama']) ?></div>
                    <div class="user-role"><?= ucfirst($role) ?></div>
                </div>
            </div>
            <a href="<?= $base_url ?>auth/logout.php" class="btn-logout" title="Keluar">
                <i data-lucide="log-out"></i>
            </a>
        </div>
    </aside>

    <!-- Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i data-lucide="chevron-left" id="toggleIcon"></i>
    </button>

    <main class="main-content" id="mainContent">
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <span>KasirKu</span>
                    <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--muted)"></i>
                    <span><?= $page_title ?? '' ?></span>
                </div>
                <h1 class="page-title"><?= $page_title ?? '' ?></h1>
            </div>
            <div class="page-meta"><?= date('l, d F Y') ?></div>
        </div>
        <div class="page-body">

<script>
function toggleSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const icon     = document.getElementById('toggleIcon');
    const body     = document.body;
    const toggle   = document.getElementById('sidebarToggle');

    sidebar.classList.toggle('collapsed');
    body.classList.toggle('sidebar-collapsed');

    const isCollapsed = sidebar.classList.contains('collapsed');
    icon.setAttribute('data-lucide', isCollapsed ? 'chevron-right' : 'chevron-left');
    toggle.style.left = isCollapsed ? '44px' : 'calc(220px - 16px)';
    lucide.createIcons();

    localStorage.setItem('sidebar', isCollapsed ? 'collapsed' : 'open');
}

window.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    if (localStorage.getItem('sidebar') === 'collapsed') {
        document.getElementById('sidebar').classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
        document.getElementById('toggleIcon').setAttribute('data-lucide', 'chevron-right');
        document.getElementById('sidebarToggle').style.left = '44px';
        lucide.createIcons();
    }
});
</script>