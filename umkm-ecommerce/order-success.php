<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$code = $_GET['code'] ?? '';
$db = getDB();
$stmt = $db->prepare('SELECT * FROM orders WHERE order_code = ? AND user_id = ?');
$stmt->execute([$code, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) redirect('/index.php');

$pageTitle = 'Pesanan Berhasil';
require_once __DIR__ . '/includes/header.php';
?>

<div class="wrap section">
  <div class="empty-state">
    <h3>Pesanan Berhasil Dibuat 🎉</h3>
    <p>Kode pesanan Anda: <strong><?= sanitize($order['order_code']) ?></strong></p>
    <p>Total: <strong><?= formatRupiah($order['total_amount']) ?></strong> &middot; Status: <span class="badge <?= statusBadgeClass($order['status']) ?>"><?= statusLabel($order['status']) ?></span></p>
    <p>Kami akan menghubungi Anda melalui nomor HP yang didaftarkan untuk konfirmasi lebih lanjut.</p>
    <a href="<?= BASE_URL ?>/my-orders.php" class="btn btn-primary" style="margin-top:12px;">Lihat Pesanan Saya</a>
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-ghost" style="margin-top:12px;border-color:var(--border);color:var(--ink-soft);">Kembali Belanja</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
