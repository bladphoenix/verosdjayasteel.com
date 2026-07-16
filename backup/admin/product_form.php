<?php
// admin/product_form.php — tambah / edit produk (dengan upload gambar)
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../inc/functions.php';

/** Kompres & resize gambar (hemat ukuran). Kembalikan true bila sukses. */
function compressImage($src, $dest, $mime, $maxW = 1400, $quality = 82) {
    switch ($mime) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($src); break;
        case 'image/png':  $img = @imagecreatefrompng($src);  break;
        case 'image/webp': $img = @imagecreatefromwebp($src); break;
        default: return copy($src, $dest);
    }
    if (!$img) return copy($src, $dest);
    $w = imagesx($img); $h = imagesy($img);
    $nw = $w > $maxW ? $maxW : $w;
    $nh = (int) round($h * ($nw / $w));
    $dst = imagecreatetruecolor($nw, $nh);
    if ($mime === 'image/png') {
        imagealphablending($dst, false); imagesavealpha($dst, true);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, imagecolorallocatealpha($dst, 255,255,255,127));
    }
    imagecopyresampled($dst, $img, 0,0,0,0, $nw,$nh, $w,$h);
    imagedestroy($img);
    switch ($mime) {
        case 'image/jpeg': $ok = imagejpeg($dst, $dest, $quality); break;
        case 'image/png':  $ok = imagepng($dst, $dest, 8);         break;
        case 'image/webp': $ok = imagewebp($dst, $dest, $quality); break;
        default: $ok = false;
    }
    imagedestroy($dst);
    return $ok;
}

/** Pastikan slug unik. */
function unique_slug(PDO $pdo, string $slug, int $excludeId = 0): string {
    $slug = $slug !== '' ? $slug : 'produk';
    $base = $slug; $i = 2;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1");
        $stmt->execute([$slug, $excludeId]);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// Nilai default
$p = [
    'name' => '', 'slug' => '', 'category_id' => '', 'price' => '', 'unit' => 'unit',
    'short_desc' => '', 'description' => '', 'specs' => '', 'material' => '',
    'dimensions' => '', 'capacity' => '', 'image_path' => '', 'is_featured' => 0,
    'stock_status' => 'ready',
];
$old_image = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { $_SESSION['flash'] = ['type'=>'danger','msg'=>'Produk tidak ditemukan.']; header('Location: products.php'); exit; }
    $p = $row;
    $old_image = $row['image_path'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check()) {
    $error = 'Sesi tidak valid (CSRF). Muat ulang halaman lalu coba lagi.';
  } else {
    $p['name']         = trim($_POST['name'] ?? '');
    $p['slug']         = trim($_POST['slug'] ?? '');
    $p['category_id']  = ($_POST['category_id'] ?? '') !== '' ? (int)$_POST['category_id'] : null;
    $p['price']        = ($_POST['price'] ?? '') !== '' ? (float)str_replace(',', '', $_POST['price']) : 0;
    $p['unit']         = trim($_POST['unit'] ?? 'unit') ?: 'unit';
    $p['short_desc']   = trim($_POST['short_desc'] ?? '');
    $p['description']  = trim($_POST['description'] ?? '');
    // specs: 1 baris = 1 poin, disimpan dipisah ';'
    $specLines         = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $_POST['specs'] ?? '')));
    $p['specs']        = implode(';', $specLines);
    $p['material']     = trim($_POST['material'] ?? '');
    $p['dimensions']   = trim($_POST['dimensions'] ?? '');
    $p['capacity']     = trim($_POST['capacity'] ?? '');
    $p['is_featured']  = isset($_POST['is_featured']) ? 1 : 0;
    $p['stock_status'] = in_array($_POST['stock_status'] ?? '', ['ready','preorder','habis'], true) ? $_POST['stock_status'] : 'ready';

    if ($p['slug'] === '') { $p['slug'] = slugify($p['name']); }
    else { $p['slug'] = slugify($p['slug']); }

    $image_path = $old_image;

    // Upload gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
        $mime = function_exists('mime_content_type') ? mime_content_type($_FILES['image']['tmp_name']) : $_FILES['image']['type'];
        if (!isset($allowed[$mime])) {
            $error = 'Format gambar tidak didukung (gunakan JPG, PNG, WebP, atau GIF).';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 5MB.';
        } else {
            $dir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
            $fname = time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            $target = $dir . $fname;
            $ok = ($mime === 'image/gif')
                ? move_uploaded_file($_FILES['image']['tmp_name'], $target)
                : compressImage($_FILES['image']['tmp_name'], $target, $mime);
            if ($ok) {
                $image_path = 'assets/uploads/' . $fname;
                // hapus gambar lama
                if ($old_image && $old_image !== $image_path && is_file(__DIR__ . '/../' . $old_image)) {
                    @unlink(__DIR__ . '/../' . $old_image);
                }
            } else {
                $error = 'Gagal mengunggah gambar.';
            }
        }
    }

    if ($p['name'] === '') {
        $error = $error ?: 'Nama produk harus diisi.';
    }

    if ($error === '') {
        $p['slug'] = unique_slug($pdo, $p['slug'], $id);
        $p['image_path'] = $image_path;
        try {
            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE products SET name=?, slug=?, category_id=?, price=?, unit=?, short_desc=?, description=?, specs=?, material=?, dimensions=?, capacity=?, image_path=?, is_featured=?, stock_status=? WHERE id=?"
                );
                $stmt->execute([$p['name'],$p['slug'],$p['category_id'],$p['price'],$p['unit'],$p['short_desc'],$p['description'],$p['specs'],$p['material'],$p['dimensions'],$p['capacity'],$p['image_path'],$p['is_featured'],$p['stock_status'],$id]);
                $_SESSION['flash'] = ['type'=>'success','msg'=>'Produk berhasil diperbarui.'];
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO products (name,slug,category_id,price,unit,short_desc,description,specs,material,dimensions,capacity,image_path,is_featured,stock_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
                );
                $stmt->execute([$p['name'],$p['slug'],$p['category_id'],$p['price'],$p['unit'],$p['short_desc'],$p['description'],$p['specs'],$p['material'],$p['dimensions'],$p['capacity'],$p['image_path'],$p['is_featured'],$p['stock_status']]);
                $_SESSION['flash'] = ['type'=>'success','msg'=>'Produk baru berhasil ditambahkan.'];
            }
            header('Location: products.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
  }
}

// specs -> tampilan textarea (1 baris per poin)
$specsText = str_replace(';', "\n", (string)$p['specs']);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$page_title = $id ? 'Edit Produk' : 'Tambah Produk';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div><h2><?php echo icon('box'); ?> <?php echo $id ? 'Edit Produk' : 'Tambah Produk'; ?></h2>
  <div class="sub"><?php echo $id ? 'Perbarui informasi produk.' : 'Isi detail produk kandang ayam baru.'; ?></div></div>
  <a href="products.php" class="btn btn-light"><?php echo icon('chevron'); ?> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo icon('shield'); ?> <?php echo e($error); ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
  <div class="form-grid">
    <!-- Kolom kiri -->
    <div>
      <div class="card" style="margin-bottom:20px;">
        <div class="card__head"><span><?php echo icon('box'); ?> Informasi Produk</span></div>
        <div class="card__body">
          <div class="field">
            <label for="name">Nama Produk *</label>
            <input class="input" type="text" id="name" name="name" data-slug-source value="<?php echo e($p['name']); ?>" placeholder="mis. Kandang Baterai Ayam Petelur 4 Pintu" required>
          </div>
          <div class="field">
            <label for="slug">Slug URL</label>
            <input class="input" type="text" id="slug" name="slug" data-slug-target value="<?php echo e($p['slug']); ?>" placeholder="otomatis dari nama" style="font-family:monospace;font-size:13px;">
            <div class="hint">URL: /product.php?slug=<b><?php echo e($p['slug'] ?: 'nama-produk'); ?></b>. Kosongkan untuk otomatis.</div>
          </div>
          <div class="field">
            <label for="short_desc">Deskripsi Singkat</label>
            <textarea class="textarea" id="short_desc" name="short_desc" style="min-height:70px;" placeholder="Ringkasan 1-2 kalimat untuk kartu produk."><?php echo e($p['short_desc']); ?></textarea>
          </div>
          <div class="field">
            <label for="description">Deskripsi Lengkap</label>
            <textarea class="textarea tall" id="description" name="description" placeholder="Deskripsi detail. Boleh pakai tag HTML sederhana: &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;&lt;li&gt;."><?php echo e($p['description']); ?></textarea>
            <div class="hint">Mendukung HTML sederhana (paragraf, tebal, daftar).</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card__head"><span><?php echo icon('tools'); ?> Spesifikasi</span></div>
        <div class="card__body">
          <div class="row-2">
            <div class="field"><label for="material">Material</label><input class="input" type="text" id="material" name="material" value="<?php echo e($p['material']); ?>" placeholder="Kawat baja galvanis Ø 2,5 mm"></div>
            <div class="field"><label for="dimensions">Dimensi</label><input class="input" type="text" id="dimensions" name="dimensions" value="<?php echo e($p['dimensions']); ?>" placeholder="P 100 x L 40 x T 40 cm"></div>
          </div>
          <div class="field"><label for="capacity">Kapasitas</label><input class="input" type="text" id="capacity" name="capacity" value="<?php echo e($p['capacity']); ?>" placeholder="8 ekor ayam petelur"></div>
          <div class="field">
            <label for="specs">Poin Keunggulan</label>
            <textarea class="textarea" id="specs" name="specs" placeholder="Satu poin per baris, mis:&#10;Anti karat&#10;Knockdown / bongkar pasang&#10;Lantai miring anti telur pecah"><?php echo e($specsText); ?></textarea>
            <div class="hint">Satu baris = satu poin (tampil sebagai daftar bercentang di halaman produk).</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Kolom kanan -->
    <div>
      <div class="card" style="margin-bottom:20px;">
        <div class="card__body">
          <button type="submit" class="btn btn-primary btn-block" style="padding:13px;"><?php echo icon('check'); ?> <?php echo $id ? 'Simpan Perubahan' : 'Simpan Produk'; ?></button>
        </div>
      </div>

      <div class="card" style="margin-bottom:20px;">
        <div class="card__head"><span><?php echo icon('settings'); ?> Pengaturan</span></div>
        <div class="card__body">
          <div class="field">
            <label for="category_id">Kategori</label>
            <select class="select" id="category_id" name="category_id">
              <option value="">— Tanpa kategori —</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?php echo (int)$c['id']; ?>" <?php echo (int)$p['category_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row-2">
            <div class="field"><label for="price">Harga (Rp)</label><input class="input" type="number" step="1" min="0" id="price" name="price" value="<?php echo e((string)($p['price'] !== '' ? (int)$p['price'] : '')); ?>" placeholder="0 = Hubungi Kami"></div>
            <div class="field"><label for="unit">Satuan</label><input class="input" type="text" id="unit" name="unit" value="<?php echo e($p['unit']); ?>" placeholder="unit / set / pcs"></div>
          </div>
          <div class="field">
            <label for="stock_status">Status Stok</label>
            <select class="select" id="stock_status" name="stock_status">
              <option value="ready" <?php echo $p['stock_status']==='ready'?'selected':''; ?>>Ready Stock</option>
              <option value="preorder" <?php echo $p['stock_status']==='preorder'?'selected':''; ?>>Pre-Order</option>
              <option value="habis" <?php echo $p['stock_status']==='habis'?'selected':''; ?>>Stok Habis</option>
            </select>
          </div>
          <label class="check"><input type="checkbox" name="is_featured" value="1" <?php echo $p['is_featured']?'checked':''; ?>> <?php echo icon('star'); ?> Tampilkan sebagai Produk Unggulan</label>
        </div>
      </div>

      <div class="card">
        <div class="card__head"><span><?php echo icon('box'); ?> Gambar Produk</span></div>
        <div class="card__body">
          <img class="img-preview" data-image-preview src="<?php echo product_image($p['image_path'], '../'); ?>" alt="Preview">
          <input class="input" type="file" name="image" accept="image/*" data-image-input>
          <div class="hint"><?php echo $p['image_path'] ? 'Unggah gambar baru untuk mengganti.' : 'JPG, PNG, WebP, GIF — maks 5MB.'; ?></div>
        </div>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
