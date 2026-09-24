<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/cart.php');

$cartId = (int)($_POST['cart_id'] ?? 0);

$db = getDB();
$stmt = $db->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
$stmt->execute([$cartId, $_SESSION['user_id']]);

$_SESSION['flash'] = ['type' => 'info', 'message' => 'Barang dihapus dari keranjang.'];
redirect('/cart.php');
