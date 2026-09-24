<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDB();
$user = currentUser();

$stmt = $db->prepare(
    'SELECT c.quantity, p.id AS product_id, p.name, p.price, p.stock
     FROM cart c JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ?'
);
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    redirect('/cart.php');
}

$total = 0;
foreach ($items as $it) $total += $it['price'] * $it['quantity'];

$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap section">
  <h2>Checkout</h2>

  <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:28px;align-items:start;">
    <div class="form-panel wide" style="margin:0;">
      <form method="post" action="<?= BASE_URL ?>/process/place-order.php">
        <div class="field">
          <label for="shipping_address">Alamat Pengiriman</label>
          <textarea id="shipping_address" name="shipping_address" required><?= sanitize($user['address'] ?? '') ?></textarea>
        </div>
        <div class="field">
          <label for="phone">Nomor HP Penerima</label>
          <input type="text" id="phone" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" required>
        </div>

        <div class="field">
          <label>Metode Pengiriman</label>
          <div class="radio-group">
            <label><input type="radio" name="delivery_method" value="delivery" checked> Diantar Kurir</label>
            <label><input type="radio" name="delivery_method" value="pickup"> Ambil Sendiri</label>
          </div>
        </div>

        <div class="field">
          <label>Metode Pembayaran</label>
          <div class="radio-group">
            <label><input type="radio" name="payment_method" value="cod" checked> Bayar di Tempat (COD)</label>
            <label><input type="radio" name="payment_method" value="transfer"> Transfer Bank</label>
          </div>
        </div>

        <div class="field">
          <label for="notes">Catatan (opsional)</label>
          <textarea id="notes" name="notes" placeholder="Contoh: titip di satpam, dsb."></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Buat Pesanan</button>
      </form>
    </div>

    <div class="cart-summary">
      <h3 style="margin-top:0;">Ringkasan Pesanan</h3>
      <?php foreach ($items as $it): ?>
        <div class="summary-row">
          <span><?= sanitize($it['name']) ?> &times;<?= $it['quantity'] ?></span>
          <span><?= formatRupiah($it['price'] * $it['quantity']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="summary-row total"><span>Total</span><span><?= formatRupiah($total) ?></span></div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
