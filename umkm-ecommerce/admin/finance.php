<?php
$pageTitle = 'Keuangan';
$__active = 'finance';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

$income = $db->query("SELECT COALESCE(SUM(amount),0) t FROM finance_records WHERE type='income'")->fetch()['t'];
$expense = $db->query("SELECT COALESCE(SUM(amount),0) t FROM finance_records WHERE type='expense'")->fetch()['t'];
$net = $income - $expense;

$records = $db->query(
    'SELECT f.*, o.order_code FROM finance_records f LEFT JOIN orders o ON o.id = f.order_id
     ORDER BY f.created_at DESC LIMIT 100'
)->fetchAll();
?>

<p class="form-sub">Setiap transaksi penjualan &amp; pembatalan pesanan tercatat otomatis di sini, tersinkron dengan modul Pesanan dan Inventaris.</p>

<div class="stat-cards">
  <div class="stat-card"><span class="num"><?= formatRupiah($income) ?></span><span class="lbl">Total Pemasukan</span></div>
  <div class="stat-card"><span class="num"><?= formatRupiah($expense) ?></span><span class="lbl">Total Pengurangan (Pembatalan)</span></div>
  <div class="stat-card"><span class="num"><?= formatRupiah($net) ?></span><span class="lbl">Pendapatan Bersih</span></div>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Tanggal</th><th>Pesanan</th><th>Tipe</th><th>Jumlah</th><th>Keterangan</th></tr></thead>
    <tbody>
      <?php if (empty($records)): ?>
        <tr><td colspan="5">Belum ada catatan keuangan.</td></tr>
      <?php endif; ?>
      <?php foreach ($records as $r): ?>
        <tr>
          <td><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></td>
          <td><?= sanitize($r['order_code'] ?? '-') ?></td>
          <td><span class="badge <?= $r['type'] === 'income' ? 'badge-completed' : 'badge-cancelled' ?>"><?= $r['type'] === 'income' ? 'Pemasukan' : 'Pengurangan' ?></span></td>
          <td><?= formatRupiah($r['amount']) ?></td>
          <td><?= sanitize($r['description']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
