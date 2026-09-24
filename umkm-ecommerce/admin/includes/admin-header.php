<?php
require_once __DIR__ . '/../../config/config.php';
requireAdmin();
$__admin = currentUser();
$__unread = unreadNotificationCount();
$__active = $__active ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?>Admin <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="brand"><?= SITE_NAME ?> · Admin</a>
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $__active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/orders.php" class="<?= $__active === 'orders' ? 'active' : '' ?>">Pesanan</a>
    <a href="<?= BASE_URL ?>/admin/products.php" class="<?= $__active === 'products' ? 'active' : '' ?>">Produk &amp; Stok</a>
    <a href="<?= BASE_URL ?>/admin/finance.php" class="<?= $__active === 'finance' ? 'active' : '' ?>">Keuangan</a>
    <a href="<?= BASE_URL ?>/index.php">Lihat Toko</a>
    <a href="<?= BASE_URL ?>/logout.php">Keluar</a>
  </aside>
  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <h2 style="margin:0;"><?= isset($pageTitle) ? sanitize($pageTitle) : 'Dashboard' ?></h2>
      </div>
      <div style="display:flex;align-items:center;gap:16px;">
        <div class="notif-bell" id="notifBell" data-base="<?= BASE_URL ?>">
          Notifikasi
          <span class="notif-dot" id="notifDot" style="display:<?= $__unread > 0 ? 'inline-block' : 'none' ?>;"><?= $__unread ?></span>
          <div class="notif-panel" id="notifPanel">
            <div id="notifList"><div class="notif-empty">Memuat notifikasi...</div></div>
          </div>
        </div>
        <span style="font-size:.9rem;color:var(--ink-soft);"><?= sanitize($__admin['name']) ?></span>
      </div>
    </div>
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="flash flash-<?= sanitize($_SESSION['flash']['type']) ?>"><?= sanitize($_SESSION['flash']['message']) ?></div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
