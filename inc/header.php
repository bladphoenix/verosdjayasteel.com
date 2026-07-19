<?php
// inc/header.php — topbar + header + navigasi (frontend).
// Membutuhkan: $settings (array). Opsional: $pageTitle, $pageDesc, $activeNav, $canonical.
if (!isset($settings)) { $settings = default_settings(); }
$company = $settings['company_name'];
$waNum   = $settings['whatsapp'];
$pageTitle = $pageTitle ?? ($company . ' — ' . $settings['tagline']);
$pageDesc  = $pageDesc  ?? $settings['hero_subtitle'];
$activeNav = $activeNav ?? 'home';
$waGeneral = wa_link($waNum, 'Halo ' . $company . ', saya ingin bertanya tentang produk kandang ayam.');
$tgLink    = tg_link($settings['telegram']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?></title>
  <meta name="description" content="<?php echo e($pageDesc); ?>">
  <meta name="keywords" content="kandang ayam, kandang baterai, kandang ayam petelur, kandang broiler, kandang galvanis, veros djaya steel, jual kandang ayam">
  <meta name="author" content="<?php echo e($company); ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?php echo e($pageTitle); ?>">
  <meta property="og:description" content="<?php echo e($pageDesc); ?>">
  <?php if (!empty($canonical)): ?><link rel="canonical" href="<?php echo e($canonical); ?>"><?php endif; ?>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%230d2137'/><text x='50' y='70' font-family='Arial' font-weight='900' font-size='54' fill='%23f59e0b' text-anchor='middle'>V</text></svg>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=9">
</head>
<body>

<!-- Banner: Website Dijual (untuk presentasi ke client) -->
<div class="forsale-bar" id="forsaleBar">
  <div class="container">
    <span class="forsale-bar__tag"><?php echo icon('star'); ?> DIJUAL</span>
    <span class="forsale-bar__txt">Website ini <strong>DIJUAL</strong><span class="forsale-bar__more"> — mau punya website profesional seperti ini untuk bisnis Anda?</span></span>
    <div class="forsale-bar__cta">
      <a href="<?php echo e(wa_link($waNum, 'Halo, saya tertarik memiliki website seperti ' . ($_SERVER['HTTP_HOST'] ?? 'ini') . '. Boleh info harga & fiturnya?')); ?>" target="_blank" rel="noopener" class="forsale-bar__cta-wa">
        <span class="forsale-bar__cta-txt">Pesan<span class="forsale-bar__cta-long"> Website Ini</span></span>
        <span class="forsale-bar__cta-ico forsale-bar__cta-ico--wa" aria-hidden="true"><?php echo icon('whatsapp'); ?></span>
      </a>
      <a href="<?php echo e($tgLink); ?>" target="_blank" rel="noopener" class="forsale-bar__cta-ico forsale-bar__cta-ico--tg" aria-label="Pesan via Telegram"><?php echo icon('telegram'); ?></a>
    </div>
    <button class="forsale-bar__close" id="forsaleClose" type="button" aria-label="Tutup banner">&times;</button>
  </div>
</div>

<!-- Topbar -->
<div class="topbar">
  <div class="container">
    <div class="topbar__info">
      <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $settings['phone'])); ?>"><span class="ti"><?php echo icon('phone'); ?></span> <?php echo e($settings['phone']); ?></a>
      <span><span class="ti"><?php echo icon('clock'); ?></span> <?php echo e($settings['working_hours'] ?? 'Senin - Sabtu'); ?></span>
      <span><span class="ti"><?php echo icon('map'); ?></span> <?php echo e($settings['address']); ?></span>
    </div>
    <div class="topbar__social">
      <a href="<?php echo e($waGeneral); ?>" target="_blank" rel="noopener">WhatsApp</a>
      <a href="<?php echo e($tgLink); ?>" target="_blank" rel="noopener">Telegram</a>
    </div>
  </div>
</div>

<!-- Header -->
<header class="header" id="siteHeader">
  <div class="container">
    <a href="index.php" class="brand">
      <span class="brand__logo">V</span>
      <span class="brand__name"><?php echo e($company); ?><small><?php echo e($settings['tagline']); ?></small></span>
    </a>
    <nav class="nav" id="siteNav">
      <a href="index.php" class="<?php echo $activeNav === 'home' ? 'active' : ''; ?>">Beranda</a>
      <a href="products.php" class="<?php echo $activeNav === 'products' ? 'active' : ''; ?>">Produk</a>
      <a href="index.php#kategori">Kategori</a>
      <a href="index.php#tentang">Tentang</a>
      <a href="index.php#kontak">Kontak</a>
    </nav>
    <div class="header__cta">
      <a href="products.php" class="btn btn-outline">Lihat Produk</a>
      <a href="<?php echo e($waGeneral); ?>" target="_blank" rel="noopener" class="btn btn-wa"><?php echo icon('whatsapp'); ?> Chat</a>
      <button class="nav-toggle" id="navToggle" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>
