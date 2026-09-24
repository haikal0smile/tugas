<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/admin/orders.php');

$orderId = (int)($_POST['order_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$validStatus = ['pending','confirmed','processing','shipped','completed','cancelled'];

if (!in_array($newStatus, $validStatus, true)) {
    redirect('/admin/order-detail.php?id=' . $orderId);
}

$db = getDB();

try {
    $db->beginTransaction();

    $stmt = $db->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) throw new Exception('Pesanan tidak ditemukan.');

    // Jika pesanan dibatalkan, kembalikan stok (sinkronisasi Inventaris)
    if ($newStatus === 'cancelled' && $order['status'] !== 'cancelled') {
        $itemsStmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $itemsStmt->execute([$orderId]);
        foreach ($itemsStmt->fetchAll() as $it) {
            adjustStock($db, $it['product_id'], $it['quantity'], 'in', $order['order_code'], 'Pengembalian stok - pesanan dibatalkan');
        }
        // Catat sebagai pengurang pendapatan pada modul Keuangan
        $expense = $db->prepare('INSERT INTO finance_records (order_id, type, amount, description) VALUES (?, "expense", ?, ?)');
        $expense->execute([$orderId, $order['total_amount'], 'Pembatalan pesanan ' . $order['order_code']]);
    }

    $update = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $update->execute([$newStatus, $orderId]);

    createNotification(
        'order_status',
        $orderId,
        'Pesanan ' . $order['order_code'] . ' diperbarui menjadi "' . statusLabel($newStatus) . '".'
    );

    $db->commit();
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status pesanan berhasil diperbarui.'];

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
}

redirect('/admin/order-detail.php?id=' . $orderId);
