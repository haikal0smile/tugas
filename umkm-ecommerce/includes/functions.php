<?php
/**
 * Fungsi-fungsi bantuan yang dipakai di seluruh aplikasi.
 */

function sanitize($str) {
    return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatRupiah($amount) {
    return 'Rp' . number_format((float)$amount, 0, ',', '.');
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('/login.php');
    }
}

function currentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function cartCount($userId) {
    if (!$userId) return 0;
    $db = getDB();
    $stmt = $db->prepare('SELECT COALESCE(SUM(quantity),0) AS total FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int)$stmt->fetch()['total'];
}

function unreadNotificationCount() {
    $db = getDB();
    $stmt = $db->query('SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0');
    return (int)$stmt->fetch()['c'];
}

/**
 * Membuat notifikasi baru (dipakai saat ada pesanan masuk, stok menipis, dll)
 */
function createNotification($type, $referenceId, $message) {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO notifications (type, reference_id, message) VALUES (?, ?, ?)');
    $stmt->execute([$type, $referenceId, $message]);
}

/**
 * Modul Inventaris: menyesuaikan stok produk secara otomatis dan tersinkron.
 * $type: 'out' saat ada penjualan/pesanan, 'in' saat restock manual oleh admin.
 * Mencatat jejak perubahan ke stock_history dan memicu notifikasi stok menipis.
 */
function adjustStock(PDO $db, $productId, $qty, $type, $reference = null, $notes = null) {
    $stmt = $db->prepare('SELECT stock, low_stock_threshold, name FROM products WHERE id = ? FOR UPDATE');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        throw new Exception('Produk tidak ditemukan saat sinkronisasi stok.');
    }

    $newStock = $type === 'out'
        ? $product['stock'] - $qty
        : $product['stock'] + $qty;

    if ($newStock < 0) {
        throw new Exception('Stok tidak mencukupi untuk produk: ' . $product['name']);
    }

    $update = $db->prepare('UPDATE products SET stock = ? WHERE id = ?');
    $update->execute([$newStock, $productId]);

    $history = $db->prepare(
        'INSERT INTO stock_history (product_id, change_type, quantity, stock_after, reference, notes)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $history->execute([$productId, $type, $qty, $newStock, $reference, $notes]);

    // Notifikasi otomatis jika stok menipis setelah pengurangan
    if ($type === 'out' && $newStock <= $product['low_stock_threshold']) {
        createNotification(
            'low_stock',
            $productId,
            'Stok menipis: "' . $product['name'] . '" tersisa ' . $newStock . ' unit.'
        );
    }

    return $newStock;
}

/**
 * Modul Keuangan: mencatat transaksi keuangan tersinkron dari sebuah pesanan.
 */
function recordFinance(PDO $db, $orderId, $amount, $description) {
    $stmt = $db->prepare(
        'INSERT INTO finance_records (order_id, type, amount, description) VALUES (?, "income", ?, ?)'
    );
    $stmt->execute([$orderId, $amount, $description]);
}

function generateOrderCode() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

function statusLabel($status) {
    $map = [
        'pending'    => 'Menunggu Konfirmasi',
        'confirmed'  => 'Dikonfirmasi',
        'processing' => 'Diproses',
        'shipped'    => 'Dikirim',
        'completed'  => 'Selesai',
        'cancelled'  => 'Dibatalkan',
    ];
    return $map[$status] ?? $status;
}

function statusBadgeClass($status) {
    $map = [
        'pending'    => 'badge-pending',
        'confirmed'  => 'badge-confirmed',
        'processing' => 'badge-processing',
        'shipped'    => 'badge-shipped',
        'completed'  => 'badge-completed',
        'cancelled'  => 'badge-cancelled',
    ];
    return $map[$status] ?? '';
}
