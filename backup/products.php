<?php
// products.php — daftar semua produk + filter kategori & pencarian
require_once __DIR__ . '/admin/config/db.php';
require_once __DIR__ . '/inc/functions.php';

$settings = get_settings($pdo);

$catSlug = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';

$categories = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c ORDER BY c.id ASC"
)->fetchAll();

// Kategori aktif
$activeCat = null;
if ($catSlug) {
    foreach ($categories as $c) {
        if ($c['slug'] === $catSlug) { $activeCat = $c; break; }
    }
}

// Query produk dengan filter aman (prepared)
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
if ($activeCat) {
    $sql .= " AND p.category_id = ?";
    $params[] = $activeCat['id'];
}
if ($q !== '') {
    $sql .= " AND (p.name LIKE ? OR p.short_desc LIKE ?)";
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$sql .= " ORDER BY p.is_featured DESC, p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = ($activeCat ? $activeCat['name'] : 'Semua Produk') . ' — ' . $settings['company_name'];
$pageDesc  = 'Katalog ' . ($activeCat ? $activeCat['name'] : 'kandang ayam galvanis') . ' dari ' . $settings['company_name'] . '. ' . $settings['tagline'];
$activeNav = 'products';
require __DIR__ . '/inc/header.php';
?>

<div class="container">
  <nav class="breadcrumb">
    <a href="index.php">Beranda</a><span>/</span>
    <a href="products.php">Produk</a>
    <?php if ($activeCat): ?><span>/</span><?php echo e($activeCat['name']); ?><?php endif; ?>
  </nav>
</div>

<section class="section" style="padding-top:12px;">
  <div class="container">
    <div class="section-head" style="text-align:left; max-width:100%; margin-bottom:26px;">
      <span class="eyebrow">Katalog Produk</span>
      <h2><?php echo e($activeCat ? $activeCat['name'] : 'Semua Produk Kandang Ayam'); ?></h2>
      <?php if ($activeCat && !empty($activeCat['description'])): ?>
        <p><?php echo e($activeCat['description']); ?></p>
      <?php else: ?>
        <p>Temukan kandang ayam galvanis berkualitas sesuai kebutuhan peternakan Anda.</p>
      <?php endif; ?>
    </div>

    <!-- Search -->
    <form method="get" action="products.php" style="display:flex; gap:10px; max-width:520px; margin-bottom:22px;">
      <?php if ($catSlug): ?><input type="hidden" name="kategori" value="<?php echo e($catSlug); ?>"><?php endif; ?>
      <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari produk kandang..."
             style="flex:1; padding:12px 16px; border:1px solid var(--border); border-radius:9px; font-size:15px; font-family:inherit;">
      <button type="submit" class="btn btn-primary">Cari</button>
    </form>

    <!-- Filter chips -->
    <div class="cat-chips" style="justify-content:flex-start; margin-bottom:30px;">
      <a href="products.php" class="<?php echo !$catSlug ? 'active' : ''; ?>">Semua</a>
      <?php foreach ($categories as $cat): ?>
        <a href="products.php?kategori=<?php echo e($cat['slug']); ?>" class="<?php echo $catSlug === $cat['slug'] ? 'active' : ''; ?>">
          <?php echo e($cat['name']); ?> (<?php echo (int)$cat['product_count']; ?>)
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($products): ?>
      <p style="color:var(--muted); font-size:14px; margin-bottom:18px;">Menampilkan <strong><?php echo count($products); ?></strong> produk<?php echo $q !== '' ? ' untuk "' . e($q) . '"' : ''; ?>.</p>
      <div class="product-grid">
        <?php foreach ($products as $p):
          $badge = stock_badge($p['stock_status']);
          $waText = 'Halo ' . $settings['company_name'] . ', saya tertarik dengan produk *' . $p['name'] . '*. Apakah tersedia?';
        ?>
          <div class="card">
            <div class="card__media">
              <a href="product.php?slug=<?php echo e($p['slug']); ?>">
                <img src="<?php echo product_image($p['image_path']); ?>" alt="<?php echo e($p['name']); ?>" loading="lazy">
              </a>
              <?php if (!empty($p['category_name'])): ?><span class="card__cat"><?php echo e($p['category_name']); ?></span><?php endif; ?>
              <span class="badge <?php echo $badge['class']; ?>"><?php echo e($badge['label']); ?></span>
            </div>
            <div class="card__body">
              <h3 class="card__title"><a href="product.php?slug=<?php echo e($p['slug']); ?>"><?php echo e($p['name']); ?></a></h3>
              <p class="card__desc"><?php echo e($p['short_desc']); ?></p>
              <div class="card__price"><?php echo e(rupiah($p['price'])); ?> <?php if ((float)$p['price'] > 0): ?><small>/ <?php echo e($p['unit']); ?></small><?php endif; ?></div>
              <div class="card__actions">
                <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="btn btn-outline">Detail</a>
                <a href="<?php echo e(wa_link($settings['whatsapp'], $waText)); ?>" target="_blank" rel="noopener" class="btn btn-wa"><?php echo icon('whatsapp'); ?> Pesan</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">
        <div class="ic"><?php echo icon('box'); ?></div>
        <p>Tidak ada produk yang cocok<?php echo $q !== '' ? ' dengan pencarian "' . e($q) . '"' : ''; ?>.</p>
        <p style="margin-top:14px;"><a href="products.php" class="btn btn-outline">Lihat Semua Produk</a></p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
