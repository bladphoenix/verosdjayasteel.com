<?php
// inc/footer.php — footer + tombol WA mengambang + script (frontend).
// Membutuhkan: $settings (array).
if (!isset($settings)) { $settings = default_settings(); }
$company   = $settings['company_name'];
$waGeneral = wa_link($settings['whatsapp'], 'Halo ' . $company . ', saya ingin bertanya tentang produk kandang ayam.');
$tgLink    = tg_link($settings['telegram']);
?>
<!-- Footer -->
<footer class="footer" id="kontak">
  <div class="container">
    <div class="grid">
      <div>
        <div class="fbrand">
          <span class="brand__logo">V</span>
          <strong><?php echo e($company); ?></strong>
        </div>
        <p class="desc"><?php echo e($settings['about_text']); ?></p>
      </div>
      <div>
        <h4>Navigasi</h4>
        <ul>
          <li><a href="index.php">Beranda</a></li>
          <li><a href="products.php">Semua Produk</a></li>
          <li><a href="index.php#kategori">Kategori</a></li>
          <li><a href="index.php#tentang">Tentang Kami</a></li>
        </ul>
      </div>
      <div>
        <h4>Produk Unggulan</h4>
        <ul>
          <li><a href="products.php?kategori=kandang-ayam-petelur">Kandang Petelur</a></li>
          <li><a href="products.php?kategori=kandang-ayam-broiler">Kandang Broiler</a></li>
          <li><a href="products.php?kategori=kandang-ayam-kampung">Kandang Kampung</a></li>
          <li><a href="products.php?kategori=kandang-doc-anakan">Box DOC</a></li>
        </ul>
      </div>
      <div>
        <h4>Hubungi Kami</h4>
        <ul>
          <li class="contact-li"><span class="ic"><?php echo icon('whatsapp'); ?></span> <a href="<?php echo e($waGeneral); ?>" target="_blank" rel="noopener">WhatsApp: <?php echo e($settings['phone']); ?></a></li>
          <li class="contact-li"><span class="ic"><?php echo icon('telegram'); ?></span> <a href="<?php echo e($tgLink); ?>" target="_blank" rel="noopener">Telegram</a></li>
          <li class="contact-li"><span class="ic"><?php echo icon('mail'); ?></span> <a href="mailto:<?php echo e($settings['email']); ?>"><?php echo e($settings['email']); ?></a></li>
          <li class="contact-li"><span class="ic"><?php echo icon('map'); ?></span> <?php echo e($settings['address']); ?></li>
          <li class="contact-li"><span class="ic"><?php echo icon('clock'); ?></span> <?php echo e($settings['working_hours'] ?? '-'); ?></li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      &copy; <?php echo date('Y'); ?> <?php echo e($company); ?>. Seluruh hak cipta dilindungi. &middot; Spesialis Kandang Ayam Baja Galvanis.
    </div>
  </div>
</footer>

<!-- Floating WhatsApp -->
<a class="fab-wa" href="<?php echo e($waGeneral); ?>" target="_blank" rel="noopener" aria-label="Chat WhatsApp"><?php echo icon('whatsapp'); ?></a>

<script src="script.js?v=7"></script>
</body>
</html>
