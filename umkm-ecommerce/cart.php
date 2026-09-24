<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDB();
$stmt = $db->prepare(
    'SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.image, p.stock
     FROM cart c JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ? ORDER BY c.created_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $it) {
    $total += $it['price'] * $it['quantity'];
}

$pageTitle = 'Keranjang Belanja';
require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap section">
  <h2>Keranjang Belanja</h2>

  <?php if (empty($items)): ?>
    <div class="empty-state">
      <h3>Keranjang Anda masih kosong</h3>
      <p><a href="<?= BASE_URL ?>/index.php" class="btn btn-primary" style="margin-top:12px;">Mulai Belanja</a></p>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:28px;align-items:start;">
      <div class="table-wrap">
        <?php foreach ($items as $it): ?>
          <div class="cart-row">
            <div class="cart-thumb">
              <?php if (!empty($it['image'])): ?>
                <img src="<?= BASE_URL ?>/assets/img/<?= sanitize($it['image']) ?>" alt="" onerror="this.parentElement.textContent='No foto'">
              <?php else: ?>No foto<?php endif; ?>
            </div>
            <div>
              <strong><?= sanitize($it['name']) ?></strong><br>
              <span style="color:var(--ink-soft);font-size:.85rem;"><?= formatRupiah($it['price']) ?> / unit</span>
            </div>
            <form class="qty-form" action="<?= BASE_URL ?>/process/update-cart.php" method="post">
              <input type="hidden" name="cart_id" value="<?= $it['cart_id'] ?>">
              <input type="number" name="quantity" value="<?= $it['quantity'] ?>" min="1" max="<?= $it['stock'] ?>">
              <button type="submit" class="btn btn-ghost btn-sm" style="border-color:var(--border);color:var(--ink-soft);">Update</button>
            </form>
            <strong><?= formatRupiah($it['price'] * $it['quantity']) ?></strong>
            <form action="<?= BASE_URL ?>/process/remove-cart.php" method="post" onsubmit="return confirm('Hapus barang ini dari keranjang?');">
              <input type="hidden" name="cart_id" value="<?= $it['cart_id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="cart-summary">
        <div class="summary-row"><span>Subtotal</span><span><?= formatRupiah($total) ?></span></div>
        <div class="summary-row"><span>Ongkir</span><span>Dihitung saat checkout</span></div>
        <div class="summary-row total"><span>Total</span><span><?= formatRupiah($total) ?></span></div>
        <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-block" style="margin-top:16px;">Lanjut ke Checkout</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
