<?php
$pageTitle = 'Dashboard';
$__active = 'dashboard';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

$totalOrders = $db->query('SELECT COUNT(*) c FROM orders')->fetch()['c'];
$pendingOrders = $db->query("SELECT COUNT(*) c FROM orders WHERE status = 'pending'")->fetch()['c'];
$revenue = $db->query("SELECT COALESCE(SUM(total_amount),0) t FROM orders WHERE status != 'cancelled'")->fetch()['t'];
$lowStock = $db->query('SELECT COUNT(*) c FROM products WHERE stock <= low_stock_threshold')->fetch()['c'];

$recentOrders = $db->query(
    'SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 8'
)->fetchAll();
?>

<div class="stat-cards">
  <div class="stat-card"><span class="num"><?= $totalOrders ?></span><span class="lbl">Total Pesanan</span></div>
  <div class="stat-card"><span class="num"><?= $pendingOrders ?></span><span class="lbl">Menunggu Konfirmasi</span></div>
  <div class="stat-card"><span class="num"><?= formatRupiah($revenue) ?></span><span class="lbl">Total Pendapatan</span></div>
  <div class="stat-card"><span class="num"><?= $lowStock ?></span><span class="lbl">Produk Stok Menipis</span></div>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>Kode</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Tanggal</th><th></th></tr>
    </thead>
    <tbody>
      <?php if (empty($recentOrders)): ?>
        <tr><td colspan="6">Belum ada pesanan.</td></tr>
      <?php endif; ?>
      <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td><?= sanitize($o['order_code']) ?></td>
          <td><?= sanitize($o['customer_name']) ?></td>
          <td><?= formatRupiah($o['total_amount']) ?></td>
          <td><span class="badge <?= statusBadgeClass($o['status']) ?>"><?= statusLabel($o['status']) ?></span></td>
          <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
          <td><a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= $o['id'] ?>" class="btn btn-secondary btn-sm">Detail</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
