<?php
$pageTitle = 'Form Produk';
$__active = 'products';
require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$product = null;
if ($id) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}
$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>

<div class="form-panel wide" style="margin:0;">
  <h2><?= $product ? 'Edit Produk' : 'Tambah Produk' ?></h2>
  <p class="form-sub">Stok awal akan menjadi acuan sinkronisasi inventaris saat pesanan masuk.</p>

  <form action="<?= BASE_URL ?>/process/save-product.php" method="post" enctype="multipart/form-data">
    <?php if ($product): ?><input type="hidden" name="id" value="<?= $product['id'] ?>"><?php endif; ?>

    <div class="field">
      <label>Nama Produk</label>
      <input type="text" name="name" value="<?= sanitize($product['name'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label>Kategori</label>
      <select name="category_id">
        <option value="">- Pilih Kategori -</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (($product['category_id'] ?? null) == $c['id']) ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label>Deskripsi</label>
      <textarea name="description"><?= sanitize($product['description'] ?? '') ?></textarea>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div class="field">
        <label>Harga (Rp)</label>
        <input type="number" name="price" min="0" step="100" value="<?= $product['price'] ?? '' ?>" required>
      </div>
      <div class="field">
        <label>Stok <?= $product ? 'Saat Ini' : 'Awal' ?></label>
        <input type="number" name="stock" min="0" value="<?= $product['stock'] ?? 0 ?>" required <?= $product ? 'readonly style="background:var(--bg-alt);"' : '' ?>>
        <?php if ($product): ?><p class="field-hint">Gunakan menu "Produk &amp; Stok" untuk menambah/mengurangi stok agar riwayat tercatat.</p><?php endif; ?>
      </div>
    </div>

    <div class="field">
      <label>Batas Stok Menipis</label>
      <input type="number" name="low_stock_threshold" min="0" value="<?= $product['low_stock_threshold'] ?? 5 ?>">
      <p class="field-hint">Notifikasi otomatis muncul saat stok mencapai atau di bawah angka ini.</p>
    </div>

    <div class="field">
      <label for="image">Foto Produk (opsional)</label>
      <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
      <?php if (!empty($product['image'])): ?>
        <p class="field-hint">Foto saat ini: <?= sanitize($product['image']) ?>. Pilih file baru jika ingin menggantinya.</p>
      <?php else: ?>
        <p class="field-hint">Pilih file gambar JPG, PNG, WEBP, atau GIF.</p>
      <?php endif; ?>
    </div>

    <div class="field">
      <label><input type="checkbox" name="is_active" value="1" <?= (!isset($product) || $product['is_active']) ? 'checked' : '' ?> style="width:auto;"> Tampilkan di toko</label>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Simpan Produk</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
