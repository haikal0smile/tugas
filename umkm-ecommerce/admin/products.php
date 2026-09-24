<?php
$pageTitle = 'Produk & Stok';
$__active = 'products';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$products = $db->query(
    'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC'
)->fetchAll();
?>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
  <a href="<?= BASE_URL ?>/admin/product-form.php" class="btn btn-primary">+ Tambah Produk</a>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
      <?php if (empty($products)): ?>
        <tr><td colspan="6">Belum ada produk.</td></tr>
      <?php endif; ?>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= sanitize($p['name']) ?></td>
          <td><?= sanitize($p['category_name'] ?? '-') ?></td>
          <td><?= formatRupiah($p['price']) ?></td>
          <td>
            <form action="<?= BASE_URL ?>/process/adjust-stock.php" method="post" style="display:flex;gap:6px;align-items:center;">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <span class="<?= $p['stock'] <= $p['low_stock_threshold'] ? 'product-stock low' : '' ?>"><?= $p['stock'] ?></span>
              <input type="number" name="quantity" min="1" value="1" style="width:56px;padding:4px;border:1px solid var(--border);border-radius:3px;">
              <button type="submit" name="type" value="in" class="btn btn-secondary btn-sm" title="Tambah stok (restock)">+ Masuk</button>
              <button type="submit" name="type" value="out" class="btn btn-danger btn-sm" title="Kurangi stok manual">&minus; Keluar</button>
            </form>
          </td>
          <td><?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
          <td><a href="<?= BASE_URL ?>/admin/product-form.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Edit</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
