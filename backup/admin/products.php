<?php
// admin/products.php — daftar produk (list + aksi)
$page_title = 'Produk';
require_once __DIR__ . '/includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$catFilter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$sql = "SELECT p.*, c.name AS category_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
if ($q !== '') { $sql .= " AND p.name LIKE ?"; $params[] = '%' . $q . '%'; }
if ($catFilter) { $sql .= " AND p.category_id = ?"; $params[] = $catFilter; }
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="page-head">
  <div>
    <h2><?php echo icon('box'); ?> Kelola Produk</h2>
    <div class="sub">Total <?php echo count($products); ?> produk dalam katalog.</div>
  </div>
  <a href="product_form.php" class="btn btn-accent"><?php echo icon('box'); ?> Tambah Produk</a>
</div>

<div class="card" style="margin-bottom:18px;">
  <div class="card__body" style="padding:14px 18px;">
    <form method="get" action="products.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <input class="input" style="max-width:280px;" type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari nama produk...">
      <select class="select" style="max-width:230px;" name="cat">
        <option value="0">Semua kategori</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo $catFilter === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary" type="submit"><?php echo icon('grid'); ?> Filter</button>
      <?php if ($q !== '' || $catFilter): ?><a class="btn btn-light" href="products.php">Reset</a><?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card__body" style="padding:0;">
    <?php if ($products): ?>
      <div class="table-wrap">
        <table class="tbl">
          <thead>
            <tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Unggulan</th><th style="text-align:right;">Aksi</th></tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): $b = stock_badge($p['stock_status']); ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:12px;">
                    <img class="tbl__thumb" src="<?php echo product_image($p['image_path'], '../'); ?>" alt="">
                    <div>
                      <div class="tbl__name"><?php echo e($p['name']); ?></div>
                      <div class="tbl__sub"><?php echo e($p['dimensions'] ?: ('/' . $p['slug'])); ?></div>
                    </div>
                  </div>
                </td>
                <td><?php echo e($p['category_name'] ?? '—'); ?></td>
                <td style="font-weight:700;color:var(--primary);white-space:nowrap;"><?php echo e(rupiah($p['price'])); ?></td>
                <td><span class="pill pill-<?php echo e($p['stock_status']); ?>"><?php echo e($b['label']); ?></span></td>
                <td>
                  <?php if ($p['is_featured']): ?>
                    <span class="pill pill-feat"><?php echo icon('star'); ?> Ya</span>
                  <?php else: ?>
                    <span class="pill pill-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="actions" style="justify-content:flex-end;">
                    <a class="ib" href="../product.php?slug=<?php echo e($p['slug']); ?>" target="_blank" title="Lihat di website"><?php echo icon('arrow-right'); ?></a>
                    <a class="ib edit" href="product_form.php?id=<?php echo (int)$p['id']; ?>" title="Edit"><?php echo icon('tools'); ?></a>
                    <form method="post" action="product_delete.php" data-confirm="Hapus produk &quot;<?php echo e($p['name']); ?>&quot;? Tindakan ini tidak bisa dibatalkan." style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                      <button class="ib del" type="submit" title="Hapus"><?php echo icon('shield'); ?></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><?php echo icon('box'); ?><p>Tidak ada produk<?php echo $q !== '' ? ' untuk "' . e($q) . '"' : ''; ?>. <a href="product_form.php" style="color:var(--primary);font-weight:600;">Tambah produk</a>.</p></div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
