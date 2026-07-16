<?php
// admin/index.php — Dashboard
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$stat = [
    'products'   => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'categories' => (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'featured'   => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn(),
    'views'      => (int)$pdo->query("SELECT COALESCE(SUM(views),0) FROM products")->fetchColumn(),
];

$recent = $pdo->query(
    "SELECT p.*, c.name AS category_name FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     ORDER BY p.created_at DESC LIMIT 6"
)->fetchAll();

$days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$today = $days[(int)date('w')] . ', ' . date('d') . ' ' . $months[(int)date('m')] . ' ' . date('Y');
?>

<div class="page-head">
  <div>
    <h2><?php echo icon('grid'); ?> Selamat datang, <?php echo e($adminName); ?>!</h2>
    <div class="sub"><?php echo $today; ?> — kelola produk kandang ayam Anda di sini.</div>
  </div>
  <a href="product_form.php" class="btn btn-accent"><?php echo icon('box'); ?> Tambah Produk</a>
</div>

<div class="stats">
  <div class="stat"><div class="stat__ic blue"><?php echo icon('box'); ?></div><div><div class="stat__val"><?php echo $stat['products']; ?></div><div class="stat__lbl">Total Produk</div></div></div>
  <div class="stat"><div class="stat__ic amber"><?php echo icon('grid'); ?></div><div><div class="stat__val"><?php echo $stat['categories']; ?></div><div class="stat__lbl">Kategori</div></div></div>
  <div class="stat"><div class="stat__ic green"><?php echo icon('star'); ?></div><div><div class="stat__val"><?php echo $stat['featured']; ?></div><div class="stat__lbl">Produk Unggulan</div></div></div>
  <div class="stat"><div class="stat__ic slate"><?php echo icon('award'); ?></div><div><div class="stat__val"><?php echo number_format($stat['views'], 0, ',', '.'); ?></div><div class="stat__lbl">Total Dilihat</div></div></div>
</div>

<div class="card">
  <div class="card__head">
    <span><?php echo icon('box'); ?> Produk Terbaru</span>
    <a href="products.php" class="btn btn-light btn-sm">Kelola Semua <?php echo icon('arrow-right'); ?></a>
  </div>
  <div class="card__body" style="padding:0;">
    <?php if ($recent): ?>
      <div class="table-wrap">
        <table class="tbl">
          <thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Status</th><th style="text-align:right;">Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($recent as $p): $b = stock_badge($p['stock_status']); ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:12px;">
                    <img class="tbl__thumb" src="<?php echo product_image($p['image_path'], '../'); ?>" alt="">
                    <div>
                      <div class="tbl__name"><?php echo e($p['name']); ?></div>
                      <div class="tbl__sub"><?php echo icon('award'); ?> <?php echo (int)$p['views']; ?>x dilihat</div>
                    </div>
                  </div>
                </td>
                <td><?php echo e($p['category_name'] ?? '—'); ?></td>
                <td style="font-weight:700;color:var(--primary);"><?php echo e(rupiah($p['price'])); ?></td>
                <td><span class="pill pill-<?php echo e($p['stock_status']); ?>"><?php echo e($b['label']); ?></span></td>
                <td>
                  <div class="actions" style="justify-content:flex-end;">
                    <a class="ib edit" href="product_form.php?id=<?php echo (int)$p['id']; ?>" title="Edit"><?php echo icon('tools'); ?></a>
                    <a class="ib" href="../product.php?slug=<?php echo e($p['slug']); ?>" target="_blank" title="Lihat"><?php echo icon('arrow-right'); ?></a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><?php echo icon('box'); ?><p>Belum ada produk. <a href="product_form.php" style="color:var(--primary);font-weight:600;">Tambah produk pertama</a>.</p></div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
