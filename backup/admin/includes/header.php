<?php
// admin/includes/header.php — kerangka admin (auth + sidebar + topbar).
// Wajib require dari halaman admin yang terproteksi. Set $page_title sebelum include.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../../inc/functions.php';

$page_title = $page_title ?? 'Dashboard';
$current = basename($_SERVER['PHP_SELF']);
$settings = get_settings($pdo);
$company  = $settings['company_name'];
$adminName = $_SESSION['admin_username'] ?? 'admin';

function nav_active(array $files, string $current): string {
    return in_array($current, $files, true) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($page_title); ?> — Admin <?php echo e($company); ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%230d2137'/><text x='50' y='70' font-family='Arial' font-weight='900' font-size='54' fill='%23f59e0b' text-anchor='middle'>V</text></svg>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css?v=6">
</head>
<body>
<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
      <span class="sidebar__logo">V</span>
      <div><strong><?php echo e($company); ?></strong><small>Panel Admin</small></div>
    </div>
    <nav class="sidebar__nav">
      <div class="label">Menu Utama</div>
      <a href="index.php" class="<?php echo nav_active(['index.php'], $current); ?>"><?php echo icon('grid'); ?> Dashboard</a>
      <a href="products.php" class="<?php echo nav_active(['products.php','product_form.php'], $current); ?>"><?php echo icon('box'); ?> Produk</a>
      <a href="categories.php" class="<?php echo nav_active(['categories.php','category_form.php'], $current); ?>"><?php echo icon('grid'); ?> Kategori</a>
      <div class="label">Situs</div>
      <a href="settings.php" class="<?php echo nav_active(['settings.php'], $current); ?>"><?php echo icon('settings'); ?> Pengaturan</a>
      <a href="../index.php" target="_blank"><?php echo icon('arrow-right'); ?> Lihat Website</a>
      <div class="label">Akun</div>
      <a href="logout.php"><?php echo icon('arrow-right'); ?> Logout</a>
    </nav>
    <div class="sidebar__foot">&copy; <?php echo date('Y'); ?> <?php echo e($company); ?></div>
  </aside>
  <div class="backdrop" id="backdrop"></div>

  <!-- Main -->
  <div class="main">
    <header class="topbar">
      <div class="topbar__left">
        <button class="hamburger" id="hamburger" aria-label="Menu"><?php echo icon('menu'); ?></button>
        <h1><?php echo e($page_title); ?></h1>
      </div>
      <div class="topbar__right">
        <a href="../index.php" target="_blank" class="btn btn-light btn-sm"><?php echo icon('arrow-right'); ?> Website</a>
        <div class="user">
          <span class="av"><?php echo e(strtoupper(substr($adminName, 0, 1))); ?></span>
          <span><?php echo e($adminName); ?></span>
        </div>
      </div>
    </header>
    <main class="content">
<?php
// Tampilkan flash message bila ada
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $cls = $f['type'] === 'success' ? 'alert-success' : ($f['type'] === 'danger' ? 'alert-danger' : 'alert-info');
    $ic  = $f['type'] === 'danger' ? 'shield' : 'check';
    echo '<div class="alert ' . $cls . '">' . icon($ic) . ' ' . e($f['msg']) . '</div>';
}
?>
