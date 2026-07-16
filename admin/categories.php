<?php
// admin/categories.php — daftar kategori
$page_title = 'Kategori';
require_once __DIR__ . '/includes/header.php';

$categories = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c ORDER BY c.name ASC"
)->fetchAll();
?>

<div class="page-head">
  <div><h2><?php echo icon('grid'); ?> Kelola Kategori</h2>
  <div class="sub"><?php echo count($categories); ?> kategori produk.</div></div>
  <a href="category_form.php" class="btn btn-accent"><?php echo icon('grid'); ?> Tambah Kategori</a>
</div>

<div class="card">
  <div class="card__body" style="padding:0;">
    <?php if ($categories): ?>
      <div class="table-wrap">
        <table class="tbl">
          <thead><tr><th>Nama Kategori</th><th>Slug</th><th>Jumlah Produk</th><th style="text-align:right;">Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($categories as $c): ?>
              <tr>
                <td>
                  <div class="tbl__name"><?php echo e($c['name']); ?></div>
                  <?php if (!empty($c['description'])): ?><div class="tbl__sub"><?php echo e(mb_strimwidth($c['description'], 0, 70, '…')); ?></div><?php endif; ?>
                </td>
                <td><code style="font-size:12.5px;color:var(--muted);"><?php echo e($c['slug']); ?></code></td>
                <td><span class="pill pill-muted"><?php echo (int)$c['product_count']; ?> produk</span></td>
                <td>
                  <div class="actions" style="justify-content:flex-end;">
                    <a class="ib" href="../products.php?kategori=<?php echo e($c['slug']); ?>" target="_blank" title="Lihat di website"><?php echo icon('arrow-right'); ?></a>
                    <a class="ib edit" href="category_form.php?id=<?php echo (int)$c['id']; ?>" title="Edit"><?php echo icon('tools'); ?></a>
                    <form method="post" action="category_delete.php" data-confirm="Hapus kategori &quot;<?php echo e($c['name']); ?>&quot;? Produk di dalamnya tidak terhapus, hanya kehilangan kategori." style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
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
      <div class="empty"><?php echo icon('grid'); ?><p>Belum ada kategori. <a href="category_form.php" style="color:var(--primary);font-weight:600;">Tambah kategori</a>.</p></div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
