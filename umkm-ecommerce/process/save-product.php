<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/admin/products.php');

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$categoryId = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
$description = trim($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$threshold = (int)($_POST['low_stock_threshold'] ?? 5);
$image = trim($_POST['image'] ?? '');
$isActive = isset($_POST['is_active']) ? 1 : 0;

if ($name === '' || $price < 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nama dan harga produk wajib diisi dengan benar.'];
    redirect('/admin/product-form.php' . ($id ? '?id=' . $id : ''));
}

$db = getDB();

if ($id) {
    // Update tanpa mengubah stok langsung (stok diatur lewat menu Produk & Stok agar riwayat tercatat)
    $stmt = $db->prepare(
        'UPDATE products SET category_id=?, name=?, description=?, price=?, low_stock_threshold=?, image=?, is_active=? WHERE id=?'
    );
    $stmt->execute([$categoryId, $name, $description, $price, $threshold, $image ?: null, $isActive, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk berhasil diperbarui.'];
} else {
    $stmt = $db->prepare(
        'INSERT INTO products (category_id, name, description, price, stock, low_stock_threshold, image, is_active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$categoryId, $name, $description, $price, $stock, $threshold, $image ?: null, $isActive]);
    $newId = $db->lastInsertId();
    if ($stock > 0) {
        adjustStock($db, $newId, $stock, 'in', 'Stok awal', 'Produk baru ditambahkan');
    }
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk baru berhasil ditambahkan.'];
}

redirect('/admin/products.php');
