<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/admin/products.php');

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$type = in_array($_POST['type'] ?? '', ['in', 'out']) ? $_POST['type'] : 'in';

$db = getDB();

try {
    $db->beginTransaction();
    adjustStock($db, $productId, $quantity, $type, 'Manual', $type === 'in' ? 'Restock manual oleh admin' : 'Koreksi/pengurangan stok manual oleh admin');
    $db->commit();
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Stok berhasil diperbarui dan disinkronkan.'];
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
}

redirect('/admin/products.php');
