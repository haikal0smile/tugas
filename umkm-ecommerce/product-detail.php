<?php
require_once __DIR__ . '/config/config.php';

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ? AND p.is_active = 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Produk tidak ditemukan';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="wrap section"><div class="empty-state"><h3>Produk tidak ditemukan</h3><p><a href="' . BASE_URL . '/index.php">Kembali ke katalog</a></p></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap section">
  <p class="breadcrumb"><a href="<?= BASE_URL ?>/index.php">Beranda</a> / <?= sanitize($product['name']) ?></p>

  <div class="product-detail">
    <div class="thumb">
      <?php if (!empty($product['image'])): ?>
        <img src="<?= BASE_URL ?>/assets/img/<?= sanitize($product['image']) ?>" alt="<?= sanitize($product['name']) ?>" onerror="this.parentElement.textContent='Foto belum ada'">
      <?php else: ?>
        Foto belum ada
      <?php endif; ?>
    </div>
    <div>
      <?php if ($product['category_name']): ?><p class="product-cat"><?= sanitize($product['category_name']) ?></p><?php endif; ?>
      <h1><?= sanitize($product['name']) ?></h1>
      <p style="font-size:1.4rem;font-weight:700;color:var(--forest);"><?= formatRupiah($product['price']) ?></p>
      <p class="product-stock <?= $product['stock'] <= $product['low_stock_threshold'] ? 'low' : '' ?>">
        <?= $product['stock'] > 0 ? ('Stok tersedia: ' . $product['stock'] . ' unit') : 'Stok habis' ?>
      </p>
      <p><?= nl2br(sanitize($product['description'])) ?></p>

      <?php if ($product['stock'] > 0): ?>
        <form action="<?= BASE_URL ?>/process/add-to-cart.php" method="post">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <div class="field" style="max-width:160px;">
            <label>Jumlah</label>
            <div class="qty-stepper">
              <button type="button" data-step="-1">&minus;</button>
              <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
              <button type="button" data-step="1">&plus;</button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Tambah ke Keranjang</button>
        </form>
      <?php else: ?>
        <button class="btn" disabled>Stok Habis</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
