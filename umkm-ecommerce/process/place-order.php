<?php
/**
 * PLACE ORDER
 * Ini adalah titik integrasi utama antar modul ERP pada Bab 6:
 *  - Inventaris : stok produk dikurangi otomatis & tersinkron (adjustStock)
 *  - Logistik   : pesanan dibuat dengan status awal "pending" untuk diproses admin
 *  - Keuangan   : setiap transaksi dicatat ke finance_records
 *  - Notifikasi : admin mendapat notifikasi pesanan baru secara real-time
 */
require_once __DIR__ . '/../config/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/cart.php');

$shippingAddress = trim($_POST['shipping_address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$deliveryMethod = in_array($_POST['delivery_method'] ?? '', ['delivery', 'pickup']) ? $_POST['delivery_method'] : 'delivery';
$paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'transfer']) ? $_POST['payment_method'] : 'cod';
$notes = trim($_POST['notes'] ?? '');

if ($shippingAddress === '' || $phone === '') {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Alamat dan nomor HP wajib diisi.'];
    redirect('/checkout.php');
}

$db = getDB();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare(
    'SELECT c.quantity, p.id AS product_id, p.name, p.price, p.stock
     FROM cart c JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ? FOR UPDATE'
);

try {
    $db->beginTransaction();

    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        throw new Exception('Keranjang kosong.');
    }

    // Validasi stok sebelum memproses (mencegah overselling)
    foreach ($items as $it) {
        if ($it['quantity'] > $it['stock']) {
            throw new Exception('Stok "' . $it['name'] . '" tidak mencukupi. Sisa stok: ' . $it['stock']);
        }
    }

    $total = 0;
    foreach ($items as $it) $total += $it['price'] * $it['quantity'];

    $orderCode = generateOrderCode();
    $insertOrder = $db->prepare(
        'INSERT INTO orders (order_code, user_id, total_amount, payment_method, delivery_method, shipping_address, phone, notes, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")'
    );
    $insertOrder->execute([$orderCode, $userId, $total, $paymentMethod, $deliveryMethod, $shippingAddress, $phone, $notes]);
    $orderId = $db->lastInsertId();

    $insertItem = $db->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    foreach ($items as $it) {
        $subtotal = $it['price'] * $it['quantity'];
        $insertItem->execute([$orderId, $it['product_id'], $it['name'], $it['price'], $it['quantity'], $subtotal]);

        // --- Sinkronisasi Inventaris: kurangi stok begitu pesanan dibuat ---
        adjustStock($db, $it['product_id'], $it['quantity'], 'out', $orderCode, 'Pesanan masuk');
    }

    // --- Sinkronisasi Keuangan: catat transaksi ---
    recordFinance($db, $orderId, $total, 'Penjualan pesanan ' . $orderCode);

    // --- Notifikasi ke admin: pesanan baru masuk ---
    createNotification(
        'new_order',
        $orderId,
        'Pesanan baru ' . $orderCode . ' dari ' . ($_SESSION['user_id'] ? currentUser()['name'] : 'pelanggan') . ' senilai ' . formatRupiah($total) . '.'
    );

    // Kosongkan keranjang setelah pesanan berhasil dibuat
    $clearCart = $db->prepare('DELETE FROM cart WHERE user_id = ?');
    $clearCart->execute([$userId]);

    $db->commit();

    redirect('/order-success.php?code=' . urlencode($orderCode));

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
    redirect('/checkout.php');
}
