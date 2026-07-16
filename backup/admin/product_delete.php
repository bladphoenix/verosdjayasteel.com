<?php
// admin/product_delete.php — hapus produk (POST only)
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}
if (!csrf_check()) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Sesi tidak valid (CSRF). Coba lagi.'];
    header('Location: products.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

        if ($row && !empty($row['image_path'])) {
            $img = __DIR__ . '/../' . $row['image_path'];
            if (is_file($img)) { @unlink($img); }
        }
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Produk berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Gagal menghapus produk: ' . $e->getMessage()];
    }
}

header('Location: products.php');
exit;
