<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDB();
$stmt = $db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$itemsStmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');

$pageTitle = 'Pesanan Saya';
require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap section">
  <h2>Pesanan Saya</h2>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <h3>Belum ada pesanan</h3>
      <p><a href="<?= BASE_URL ?>/index.php" class="btn btn-primary" style="margin-top:12px;">Mulai Belanja</a></p>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php $itemsStmt->execute([$order['id']]); $items = $itemsStmt->fetchAll(); ?>
      <div class="form-panel wide" style="margin:0 0 20px;">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;align-items:center;">
          <div>
            <strong><?= sanitize($order['order_code']) ?></strong>
            <p style="color:var(--ink-soft);font-size:.85rem;margin:2px 0 0;"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
          </div>
          <span class="badge <?= statusBadgeClass($order['status']) ?>"><?= statusLabel($order['status']) ?></span>
        </div>
        <div class="table-wrap" style="margin-top:14px;border:none;">
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
          </table>
        </div>
        <p style="text-align:right;margin-top:10px;font-weight:700;color:var(--forest);">Total: <?= formatRupiah($order['total_amount']) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
