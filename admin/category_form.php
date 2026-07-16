<?php
// admin/category_form.php — tambah / edit kategori
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../inc/functions.php';

function unique_cat_slug(PDO $pdo, string $slug, int $excludeId = 0): string {
    $slug = $slug !== '' ? $slug : 'kategori';
    $base = $slug; $i = 2;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id <> ? LIMIT 1");
        $stmt->execute([$slug, $excludeId]);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$c = ['name' => '', 'slug' => '', 'description' => ''];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { $_SESSION['flash'] = ['type'=>'danger','msg'=>'Kategori tidak ditemukan.']; header('Location: categories.php'); exit; }
    $c = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $c['name']        = trim($_POST['name'] ?? '');
    $c['slug']        = slugify(trim($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : $c['name']);
    $c['description'] = trim($_POST['description'] ?? '');

    if (!csrf_check()) {
        $error = 'Sesi tidak valid (CSRF). Muat ulang halaman lalu coba lagi.';
    } elseif ($c['name'] === '') {
        $error = 'Nama kategori harus diisi.';
    } else {
        $c['slug'] = unique_cat_slug($pdo, $c['slug'], $id);
        try {
            if ($id) {
                $pdo->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?")
                    ->execute([$c['name'], $c['slug'], $c['description'], $id]);
                $_SESSION['flash'] = ['type'=>'success','msg'=>'Kategori diperbarui.'];
            } else {
                $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?,?,?)")
                    ->execute([$c['name'], $c['slug'], $c['description']]);
                $_SESSION['flash'] = ['type'=>'success','msg'=>'Kategori ditambahkan.'];
            }
            header('Location: categories.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

$page_title = $id ? 'Edit Kategori' : 'Tambah Kategori';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div><h2><?php echo icon('grid'); ?> <?php echo $id ? 'Edit Kategori' : 'Tambah Kategori'; ?></h2></div>
  <a href="categories.php" class="btn btn-light"><?php echo icon('chevron'); ?> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo icon('shield'); ?> <?php echo e($error); ?></div><?php endif; ?>

<div class="card" style="max-width:640px;">
  <div class="card__body">
    <form method="post">
      <?php echo csrf_field(); ?>
      <div class="field">
        <label for="name">Nama Kategori *</label>
        <input class="input" type="text" id="name" name="name" data-slug-source value="<?php echo e($c['name']); ?>" placeholder="mis. Kandang Ayam Petelur" required>
      </div>
      <div class="field">
        <label for="slug">Slug URL</label>
        <input class="input" type="text" id="slug" name="slug" data-slug-target value="<?php echo e($c['slug']); ?>" placeholder="otomatis dari nama" style="font-family:monospace;font-size:13px;">
        <div class="hint">Kosongkan untuk dibuat otomatis dari nama.</div>
      </div>
      <div class="field">
        <label for="description">Deskripsi</label>
        <textarea class="textarea" id="description" name="description" placeholder="Deskripsi singkat kategori (opsional)."><?php echo e($c['description']); ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary"><?php echo icon('check'); ?> <?php echo $id ? 'Simpan Perubahan' : 'Simpan Kategori'; ?></button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
