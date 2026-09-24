<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/cart.php');

$cartId = (int)($_POST['cart_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

$db = getDB();
$stmt = $db->prepare(
    'SELECT c.*, p.stock FROM cart c JOIN products p ON p.id = c.product_id
     WHERE c.id = ? AND c.user_id = ?'
);
$stmt->execute([$cartId, $_SESSION['user_id']]);
$row = $stmt->fetch();

if ($row) {
    $quantity = min($quantity, $row['stock']);
    $update = $db->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
    $update->execute([$quantity, $cartId]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Jumlah barang diperbarui.'];
}

redirect('/cart.php');
