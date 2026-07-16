<?php
// product.php — halaman detail produk
require_once __DIR__ . '/admin/config/db.php';
require_once __DIR__ . '/inc/functions.php';

$settings = get_settings($pdo);

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.slug = ? LIMIT 1"
);
$stmt->execute([$slug]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    $pageTitle = 'Produk tidak ditemukan — ' . $settings['company_name'];
    require __DIR__ . '/inc/header.php';
    echo '<div class="container"><div class="empty" style="padding:100px 20px;"><div class="ic">' . icon('box') . '</div><h2 style="color:var(--steel-800);margin-bottom:8px;">Produk tidak ditemukan</h2><p>Maaf, produk yang Anda cari tidak tersedia.</p><p style="margin-top:18px;"><a href="products.php" class="btn btn-primary">Kembali ke Katalog</a></p></div></div>';
    require __DIR__ . '/inc/footer.php';
    exit;
}

// Tambah view (best-effort)
try {
    $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$p['id']]);
} catch (PDOException $e) { /* abaikan */ }

// Produk terkait
$related = [];
if (!empty($p['category_id'])) {
    $rs = $pdo->prepare(
        "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.category_id = ? AND p.id <> ? ORDER BY p.is_featured DESC, RAND() LIMIT 4"
    );
    $rs->execute([$p['category_id'], $p['id']]);
    $related = $rs->fetchAll();
}

$badge   = stock_badge($p['stock_status']);
$waText  = 'Halo ' . $settings['company_name'] . ', saya tertarik memesan produk *' . $p['name'] . '*';
if ((float)$p['price'] > 0) { $waText .= ' (' . rupiah($p['price']) . '/' . $p['unit'] . ')'; }
$waText .= '. Mohon info ketersediaan & pengiriman.';
$waLink  = wa_link($settings['whatsapp'], $waText);
$tgLink  = tg_link($settings['telegram']);

// Pecah specs (dipisah ;)
$specItems = array_filter(array_map('trim', explode(';', (string)$p['specs'])));

$pageTitle = $p['name'] . ' — ' . $settings['company_name'];
$pageDesc  = $p['short_desc'] ?: $settings['tagline'];
$activeNav = 'products';
require __DIR__ . '/inc/header.php';
?>

<div class="container">
  <nav class="breadcrumb">
    <a href="index.php">Beranda</a><span>/</span>
    <a href="products.php">Produk</a>
    <?php if (!empty($p['category_name'])): ?><span>/</span><a href="products.php?kategori=<?php echo e($p['category_slug']); ?>"><?php echo e($p['category_name']); ?></a><?php endif; ?>
    <span>/</span><?php echo e($p['name']); ?>
  </nav>
</div>

<section class="section" style="padding-top:8px;">
  <div class="container">
    <div class="pd">
      <!-- Media -->
      <div class="pd__media">
        <img src="<?php echo product_image($p['image_path']); ?>" alt="<?php echo e($p['name']); ?>">
      </div>

      <!-- Info -->
      <div class="pd__info">
        <?php if (!empty($p['category_name'])): ?><div class="pd__cat"><?php echo e($p['category_name']); ?></div><?php endif; ?>
        <h1><?php echo e($p['name']); ?></h1>
        <span class="badge <?php echo $badge['class']; ?>" style="position:static; display:inline-block;"><?php echo e($badge['label']); ?></span>

        <div class="pd__price" style="margin-top:16px;">
          <?php echo e(rupiah($p['price'])); ?>
          <?php if ((float)$p['price'] > 0): ?><small>/ <?php echo e($p['unit']); ?></small><?php endif; ?>
        </div>

        <?php if (!empty($p['short_desc'])): ?><p class="pd__short"><?php echo e($p['short_desc']); ?></p><?php endif; ?>

        <!-- Spesifikasi ringkas -->
        <table class="pd__specs">
          <?php if (!empty($p['material'])): ?><tr><th>Material</th><td><?php echo e($p['material']); ?></td></tr><?php endif; ?>
          <?php if (!empty($p['dimensions'])): ?><tr><th>Dimensi</th><td><?php echo e($p['dimensions']); ?></td></tr><?php endif; ?>
          <?php if (!empty($p['capacity'])): ?><tr><th>Kapasitas</th><td><?php echo e($p['capacity']); ?></td></tr><?php endif; ?>
          <tr><th>Status</th><td><?php echo e($badge['label']); ?></td></tr>
        </table>

        <?php if ($specItems): ?>
          <ul class="spec-list">
            <?php foreach ($specItems as $item): ?>
              <li><span class="ck"><?php echo icon('check'); ?></span><?php echo e($item); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <!-- Kontak / Order -->
        <div class="pd__contact">
          <h3>Berminat dengan produk ini?</h3>
          <p>Hubungi kami langsung via WhatsApp atau Telegram untuk pemesanan, harga grosir, dan info pengiriman.</p>
          <div class="btns">
            <a href="<?php echo e($waLink); ?>" target="_blank" rel="noopener" class="btn btn-wa btn-lg"><?php echo icon('whatsapp'); ?> Pesan via WhatsApp</a>
            <a href="<?php echo e($tgLink); ?>" target="_blank" rel="noopener" class="btn btn-tg btn-lg"><?php echo icon('telegram'); ?> Chat Telegram</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Deskripsi lengkap -->
    <?php if (!empty($p['description'])): ?>
      <div class="pd__desc">
        <h2>Deskripsi Produk</h2>
        <div class="content"><?php echo $p['description']; // HTML dari admin (tepercaya) ?></div>
      </div>
    <?php endif; ?>

    <!-- Produk terkait -->
    <?php if ($related): ?>
      <div class="pd__desc">
        <h2>Produk Terkait</h2>
        <div class="product-grid">
          <?php foreach ($related as $r):
            $rBadge = stock_badge($r['stock_status']);
          ?>
            <div class="card">
              <div class="card__media">
                <a href="product.php?slug=<?php echo e($r['slug']); ?>">
                  <img src="<?php echo product_image($r['image_path']); ?>" alt="<?php echo e($r['name']); ?>" loading="lazy">
                </a>
                <span class="badge <?php echo $rBadge['class']; ?>"><?php echo e($rBadge['label']); ?></span>
              </div>
              <div class="card__body">
                <h3 class="card__title"><a href="product.php?slug=<?php echo e($r['slug']); ?>"><?php echo e($r['name']); ?></a></h3>
                <div class="card__price"><?php echo e(rupiah($r['price'])); ?> <?php if ((float)$r['price'] > 0): ?><small>/ <?php echo e($r['unit']); ?></small><?php endif; ?></div>
                <div class="card__actions">
                  <a href="product.php?slug=<?php echo e($r['slug']); ?>" class="btn btn-outline btn-block">Lihat Detail</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
