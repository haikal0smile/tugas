<?php
require_once __DIR__ . '/config/config.php';

$db = getDB();

$categories = $db->query('SELECT * FROM categories ORDER BY id')->fetchAll();

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = trim($_GET['q'] ?? '');

$sql = 'SELECT * FROM products WHERE is_active = 1';
$params = [];
if ($categoryId > 0) {
    $sql .= ' AND category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $sql .= ' AND name LIKE ?';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Beranda';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="wrap hero-inner">
    <div>
      <p class="hero-eyebrow">Belanja langsung dari pelaku usaha lokal</p>
      <h1>Produk UMKM pilihan, dari dapur &amp; workshop ke rumah Anda</h1>
      <p class="lead">Setiap pesanan tersambung langsung ke stok gudang dan pembukuan kami, jadi barang yang Anda lihat di sini benar-benar tersedia.</p>
      <div class="hero-actions">
        <a href="#produk" class="btn btn-primary">Lihat Produk</a>
        <a href="#kontak" class="btn btn-ghost">Hubungi Kami</a>
      </div>
    </div>
    <div class="hero-art">
      <h3>Kenapa belanja di sini?</h3>
      <div class="hero-stats">
        <div class="hero-stat"><span class="num"><?= count($products) ?>+</span><span class="lbl">Produk aktif</span></div>
        <div class="hero-stat"><span class="num">100%</span><span class="lbl">Stok tersinkron</span></div>
        <div class="hero-stat"><span class="num">COD</span><span class="lbl">&amp; Transfer</span></div>
        <div class="hero-stat"><span class="num">1x24j</span><span class="lbl">Pesanan diproses</span></div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="produk">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>Katalog Produk</h2>
        <p>Semua stok yang tampil sudah sesuai jumlah barang di gudang.</p>
      </div>
    </div>

    <form class="search-box" method="get" action="<?= BASE_URL ?>/index.php#produk">
      <?php if ($categoryId): ?><input type="hidden" name="category" value="<?= (int)$categoryId ?>"><?php endif; ?>
      <input type="text" name="q" placeholder="Cari produk..." value="<?= sanitize($search) ?>">
      <button type="submit" class="btn btn-secondary">Cari</button>
    </form>

    <div class="filter-bar">
      <a href="<?= BASE_URL ?>/index.php#produk" class="<?= $categoryId === 0 ? 'active' : '' ?>">Semua</a>
      <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>/index.php?category=<?= $cat['id'] ?>#produk"
           class="<?= $categoryId === (int)$cat['id'] ? 'active' : '' ?>">
          <?= sanitize($cat['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($products)): ?>
      <div class="empty-state">
        <h3>Produk tidak ditemukan</h3>
        <p>Coba ubah kata kunci atau pilih kategori lain.</p>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($products as $p): ?>
          <div class="product-card">
            <div class="product-thumb">
              <?php if (!empty($p['image'])): ?>
                <img src="<?= BASE_URL ?>/assets/img/<?= sanitize($p['image']) ?>" alt="<?= sanitize($p['name']) ?>" onerror="this.parentElement.textContent='Foto belum ada'">
              <?php else: ?>
                Foto belum ada
              <?php endif; ?>
            </div>
            <div class="product-body">
              <span class="product-name"><a href="<?= BASE_URL ?>/product-detail.php?id=<?= $p['id'] ?>"><?= sanitize($p['name']) ?></a></span>
              <span class="product-price"><?= formatRupiah($p['price']) ?></span>
              <span class="product-stock <?= $p['stock'] <= $p['low_stock_threshold'] ? 'low' : '' ?>">
                <?= $p['stock'] > 0 ? ('Stok: ' . $p['stock']) : 'Stok habis' ?>
              </span>
            </div>
            <div class="product-actions">
              <a href="<?= BASE_URL ?>/product-detail.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-block btn-sm">Lihat Detail</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
