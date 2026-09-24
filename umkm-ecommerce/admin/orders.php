<?php
$pageTitle = 'Pesanan';
$__active = 'orders';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$status = $_GET['status'] ?? '';
$validStatus = ['pending','confirmed','processing','shipped','completed','cancelled'];

$sql = 'SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id';
$params = [];
if (in_array($status, $validStatus, true)) {
    $sql .= ' WHERE o.status = ?';
    $params[] = $status;
}
$sql .= ' ORDER BY o.created_at DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="filter-bar">
  <a href="<?= BASE_URL ?>/admin/orders.php" class="<?= $status === '' ? 'active' : '' ?>">Semua</a>
  <?php foreach ($validStatus as $s): ?>
    <a href="<?= BASE_URL ?>/admin/orders.php?status=<?= $s ?>" class="<?= $status === $s ? 'active' : '' ?>"><?= statusLabel($s) ?></a>
  <?php endforeach; ?>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>Kode</th><th>Pelanggan</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th><th></th></tr>
    </thead>
    <tbody>
      <?php if (empty($orders)): ?>
        <tr><td colspan="7">Tidak ada pesanan.</td></tr>
      <?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= sanitize($o['order_code']) ?></td>
          <td><?= sanitize($o['customer_name']) ?></td>
          <td><?= formatRupiah($o['total_amount']) ?></td>
          <td><?= strtoupper($o['payment_method']) ?></td>
          <td><span class="badge <?= statusBadgeClass($o['status']) ?>"><?= statusLabel($o['status']) ?></span></td>
          <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
          <td><a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">Kelola</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
