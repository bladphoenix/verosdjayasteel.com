<?php
// index.php — Beranda Veros Djaya Steel
require_once __DIR__ . '/admin/config/db.php';
require_once __DIR__ . '/inc/functions.php';

$settings = get_settings($pdo);

// Kategori + jumlah produk
$categories = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c ORDER BY c.id ASC"
)->fetchAll();

// Produk unggulan
$featured = $pdo->query(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.is_featured = 1 ORDER BY p.created_at DESC LIMIT 8"
)->fetchAll();

// Lengkapi hingga kelipatan 4 dengan produk terbaru lain agar baris grid penuh
// (tidak ada kolom longgar). Minimal 4 kartu bila produk tersedia.
$target = count($featured) === 0 ? 4 : (int)ceil(count($featured) / 4) * 4;
if (count($featured) < $target) {
    $need = $target - count($featured);
    $excludeIds = array_column($featured, 'id');
    $notIn = $excludeIds ? ' WHERE p.id NOT IN (' . implode(',', array_fill(0, count($excludeIds), '?')) . ')' : '';
    $stmt = $pdo->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p LEFT JOIN categories c ON p.category_id = c.id"
        . $notIn . " ORDER BY p.created_at DESC LIMIT " . (int)$need
    );
    $stmt->execute($excludeIds);
    $featured = array_merge($featured, $stmt->fetchAll());
}

$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

$pageTitle = $settings['company_name'] . ' — ' . $settings['tagline'];
$activeNav = 'home';
require __DIR__ . '/inc/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero__copy">
      <span class="hero__badge"><span class="dot"></span> Produsen &amp; Distributor Kandang Ayam</span>
      <h1><?php echo e($settings['hero_title']); ?></h1>
      <p class="lead"><?php echo e($settings['hero_subtitle']); ?></p>
      <div class="hero__actions">
        <a href="products.php" class="btn btn-accent btn-lg"><?php echo icon('grid'); ?> Lihat Katalog Produk</a>
        <a href="<?php echo e(wa_link($settings['whatsapp'], 'Halo ' . $settings['company_name'] . ', saya ingin konsultasi kebutuhan kandang ayam.')); ?>" target="_blank" rel="noopener" class="btn btn-wa btn-lg"><?php echo icon('whatsapp'); ?> Konsultasi Gratis</a>
      </div>
      <div class="hero__stats">
        <div><div class="num"><?php echo $totalProducts; ?>+</div><div class="lbl">Model Kandang</div></div>
        <div><div class="num">100%</div><div class="lbl">Baja Galvanis</div></div>
        <div><div class="num">34</div><div class="lbl">Provinsi Terjangkau</div></div>
      </div>
    </div>
    <div class="hero__visual">
      <img src="<?php echo product_image($featured[0]['image_path'] ?? null); ?>" alt="Kandang ayam galvanis Veros Djaya Steel" loading="eager">
      <div class="cap"><?php echo icon('shield'); ?> Garansi kualitas material &amp; ketepatan ukuran</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features section--tight">
  <div class="container">
    <div class="grid">
      <div class="feature"><span class="ic"><?php echo icon('shield'); ?></span><div><h4>Anti Karat</h4><p>Baja galvanis berlapis seng, tahan cuaca &amp; awet bertahun-tahun.</p></div></div>
      <div class="feature"><span class="ic"><?php echo icon('award'); ?></span><div><h4>Kualitas Pabrik</h4><p>Produksi presisi, ukuran akurat, harga langsung dari produsen.</p></div></div>
      <div class="feature"><span class="ic"><?php echo icon('truck'); ?></span><div><h4>Kirim Se-Indonesia</h4><p>Pengiriman aman ke seluruh provinsi, packing kayu rapi.</p></div></div>
      <div class="feature"><span class="ic"><?php echo icon('tools'); ?></span><div><h4>Knockdown</h4><p>Desain bongkar-pasang, mudah dipindah &amp; dirakit sendiri.</p></div></div>
    </div>
  </div>
</section>

<!-- KATEGORI -->
<section class="section" id="kategori">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Kategori Produk</span>
      <h2>Pilih Jenis Kandang Sesuai Kebutuhan</h2>
      <p>Dari kandang baterai petelur hingga box brooder DOC — semua tersedia dalam bahan galvanis berkualitas.</p>
    </div>
    <div class="cat-chips">
      <a href="products.php" class="active">Semua Produk</a>
      <?php foreach ($categories as $cat): ?>
        <a href="products.php?kategori=<?php echo e($cat['slug']); ?>"><?php echo e($cat['name']); ?> (<?php echo (int)$cat['product_count']; ?>)</a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PRODUK UNGGULAN -->
<section class="section" style="padding-top:0;" id="produk">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Katalog</span>
      <h2>Produk Unggulan Kami</h2>
      <p>Kandang ayam pilihan yang paling banyak diminati peternak.</p>
    </div>

    <?php if ($featured): ?>
      <div class="product-grid">
        <?php foreach ($featured as $p):
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
      <div style="text-align:center; margin-top:40px;">
        <a href="products.php" class="btn btn-primary btn-lg">Lihat Semua Produk <?php echo icon('arrow-right'); ?></a>
      </div>
    <?php else: ?>
      <div class="empty"><div class="ic"><?php echo icon('box'); ?></div><p>Belum ada produk. Tambahkan lewat panel admin.</p></div>
    <?php endif; ?>
  </div>
</section>

<!-- TENTANG -->
<section class="section" id="tentang" style="background:var(--surface);">
  <div class="container">
    <div class="about">
      <div class="about__img">
        <img src="<?php echo product_image($featured[1]['image_path'] ?? ($featured[0]['image_path'] ?? null)); ?>" alt="Tentang <?php echo e($settings['company_name']); ?>">
      </div>
      <div>
        <span class="eyebrow" style="color:var(--accent-dark);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;font-size:13px;">Tentang Kami</span>
        <h2><?php echo e($settings['company_name']); ?></h2>
        <p><?php echo e($settings['about_text']); ?></p>
        <ul>
          <li><span class="ck"><?php echo icon('check'); ?></span> Material baja galvanis anti karat pilihan</li>
          <li><span class="ck"><?php echo icon('check'); ?></span> Ukuran presisi &amp; konstruksi kokoh</li>
          <li><span class="ck"><?php echo icon('check'); ?></span> Harga langsung dari produsen</li>
          <li><span class="ck"><?php echo icon('check'); ?></span> Melayani grosir &amp; eceran, kirim se-Indonesia</li>
        </ul>
        <div style="margin-top:26px; display:flex; gap:12px; flex-wrap:wrap;">
          <a href="<?php echo e(wa_link($settings['whatsapp'], 'Halo, saya ingin tahu lebih lanjut tentang ' . $settings['company_name'])); ?>" target="_blank" rel="noopener" class="btn btn-wa"><?php echo icon('whatsapp'); ?> WhatsApp</a>
          <a href="<?php echo e(tg_link($settings['telegram'])); ?>" target="_blank" rel="noopener" class="btn btn-tg"><?php echo icon('telegram'); ?> Telegram</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section--tight">
  <div class="container">
    <div class="cta-banner">
      <h2>Butuh Kandang Custom atau Order Grosir?</h2>
      <p>Konsultasikan kebutuhan kandang ayam Anda. Tim kami siap membantu memilih model &amp; ukuran yang tepat, plus penawaran harga terbaik.</p>
      <div class="btns">
        <a href="<?php echo e(wa_link($settings['whatsapp'], 'Halo ' . $settings['company_name'] . ', saya ingin konsultasi / order kandang ayam.')); ?>" target="_blank" rel="noopener" class="btn btn-wa btn-lg"><?php echo icon('whatsapp'); ?> Chat WhatsApp</a>
        <a href="<?php echo e(tg_link($settings['telegram'])); ?>" target="_blank" rel="noopener" class="btn btn-tg btn-lg"><?php echo icon('telegram'); ?> Chat Telegram</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
