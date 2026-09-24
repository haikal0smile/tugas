<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/index.php');

if (!isLoggedIn()) {
    $_SESSION['flash'] = ['type' => 'info', 'message' => 'Silakan masuk terlebih dahulu untuk menambahkan ke keranjang.'];
    redirect('/login.php');
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

$db = getDB();
$stmt = $db->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Produk tidak ditemukan.'];
    redirect('/index.php');
}

if ($quantity > $product['stock']) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Jumlah melebihi stok yang tersedia.'];
    redirect('/product-detail.php?id=' . $productId);
}

$existing = $db->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id = ?');
$existing->execute([$_SESSION['user_id'], $productId]);
$row = $existing->fetch();

if ($row) {
    $newQty = min($product['stock'], $row['quantity'] + $quantity);
    $update = $db->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
    $update->execute([$newQty, $row['id']]);
} else {
    $insert = $db->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
    $insert->execute([$_SESSION['user_id'], $productId, $quantity]);
}

$_SESSION['flash'] = ['type' => 'success', 'message' => sanitize($product['name']) . ' ditambahkan ke keranjang.'];
redirect('/cart.php');
