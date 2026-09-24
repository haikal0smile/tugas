<?php
require_once __DIR__ . '/../config/config.php';
$__user = currentUser();
$__cartCount = $__user ? cartCount($__user['id']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap header-inner">
    <a href="<?= BASE_URL ?>/index.php" class="brand"><?= SITE_NAME ?></a>
    <nav class="main-nav">
      <a href="<?= BASE_URL ?>/index.php">Beranda</a>
      <a href="<?= BASE_URL ?>/index.php#produk">Produk</a>
      <a href="<?= BASE_URL ?>/index.php#kontak">Kontak</a>
      <?php if ($__user): ?>
        <a href="<?= BASE_URL ?>/my-orders.php">Pesanan Saya</a>
      <?php endif; ?>
    </nav>
    <div class="header-actions">
      <a href="<?= BASE_URL ?>/cart.php" class="icon-link" aria-label="Keranjang">
        Keranjang
        <?php if ($__cartCount > 0): ?><span class="pill"><?= $__cartCount ?></span><?php endif; ?>
      </a>
      <?php if ($__user): ?>
        <div class="user-menu">
          <span class="user-name"><?= sanitize($__user['name']) ?></span>
          <?php if ($__user['role'] === 'admin'): ?>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-ghost btn-sm">Dashboard Admin</a>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/logout.php" class="btn btn-ghost btn-sm">Keluar</a>
        </div>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-ghost btn-sm">Masuk</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="wrap">
    <div class="flash flash-<?= sanitize($_SESSION['flash']['type']) ?>">
      <?= sanitize($_SESSION['flash']['message']) ?>
    </div>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<main>
