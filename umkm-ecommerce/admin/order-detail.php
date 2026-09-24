<?php
$pageTitle = 'Detail Pesanan';
$__active = 'orders';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo '<div class="empty-state"><h3>Pesanan tidak ditemukan</h3></div>';
    require_once __DIR__ . '/includes/admin-footer.php';
    exit;
}

$itemsStmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$statusFlow = ['pending','confirmed','processing','shipped','completed'];
?>

<p class="breadcrumb"><a href="<?= BASE_URL ?>/admin/orders.php">Pesanan</a> / <?= sanitize($order['order_code']) ?></p>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:24px;align-items:start;">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= sanitize($it['product_name']) ?></td>
            <td><?= formatRupiah($it['price']) ?></td>
            <td><?= $it['quantity'] ?></td>
            <td><?= formatRupiah($it['subtotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="3" style="text-align:right;font-weight:700;">Total</td><td style="font-weight:700;color:var(--forest);"><?= formatRupiah($order['total_amount']) ?></td></tr>
      </tfoot>
    </table>
  </div>

  <div class="cart-summary">
    <p><strong>Pelanggan:</strong> <?= sanitize($order['customer_name']) ?><br>
    <span style="color:var(--ink-soft);font-size:.85rem;"><?= sanitize($order['customer_email']) ?></span></p>
    <p><strong>Telepon:</strong> <?= sanitize($order['phone']) ?></p>
    <p><strong>Alamat:</strong><br><?= nl2br(sanitize($order['shipping_address'])) ?></p>
    <p><strong>Pengiriman:</strong> <?= $order['delivery_method'] === 'delivery' ? 'Diantar Kurir' : 'Ambil Sendiri' ?></p>
    <p><strong>Pembayaran:</strong> <?= strtoupper($order['payment_method']) ?></p>
    <?php if ($order['notes']): ?><p><strong>Catatan:</strong> <?= sanitize($order['notes']) ?></p><?php endif; ?>
    <p><strong>Status saat ini:</strong> <span class="badge <?= statusBadgeClass($order['status']) ?>"><?= statusLabel($order['status']) ?></span></p>

    <?php if (!in_array($order['status'], ['completed','cancelled'], true)): ?>
      <form action="<?= BASE_URL ?>/process/update-order-status.php" method="post" style="margin-top:14px;">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <div class="field">
          <label>Ubah Status (Modul Logistik)</label>
          <select name="status">
            <?php foreach ($statusFlow as $s): ?>
              <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= statusLabel($s) ?></option>
            <?php endforeach; ?>
            <option value="cancelled">Batalkan Pesanan</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Simpan Status</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
