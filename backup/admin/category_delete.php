<?php
// admin/category_delete.php — hapus kategori (POST only). Produk di dalamnya tidak dihapus
// (kolom category_id di-set NULL oleh foreign key ON DELETE SET NULL).
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categories.php');
    exit;
}
if (!csrf_check()) {
    $_SESSION['flash'] = ['type'=>'danger','msg'=>'Sesi tidak valid (CSRF). Coba lagi.'];
    header('Location: categories.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id) {
    try {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Kategori berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type'=>'danger','msg'=>'Gagal menghapus kategori: ' . $e->getMessage()];
    }
}

header('Location: categories.php');
exit;
