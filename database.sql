-- ==========================================================================
--  Veros Djaya Steel — Database Schema & Seed Data
--  Tema: Jual Kandang Ayam Baja Galvanis
--
--  Cara pakai:
--    1. Buat database (mis. verosdjayasteel) di cPanel / phpMyAdmin.
--    2. Sesuaikan kredensial di admin/config/db.php.
--    3. Import file ini melalui phpMyAdmin > Import, atau:
--         mysql -u USER -p NAMA_DB < database.sql
--    4. Buat akun admin: buka admin/setup.php di browser (set dulu $ADMIN_PASS
--       di dalam file), lalu HAPUS admin/setup.php. Tidak ada password default.
-- ==========================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- --------------------------------------------------------------------------
-- Tabel: users (akun admin)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `nama`       VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tidak ada akun default (demi keamanan). Buat akun admin lewat admin/setup.php.

-- --------------------------------------------------------------------------
-- Tabel: categories (kategori kandang / produk)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL,
  `slug`        VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
  ('Kandang Ayam Petelur', 'kandang-ayam-petelur', 'Kandang baterai untuk ayam petelur (layer) dengan sistem koleksi telur otomatis.'),
  ('Kandang Ayam Broiler', 'kandang-ayam-broiler', 'Kandang panggung & baterai untuk ayam pedaging (broiler).'),
  ('Kandang Ayam Kampung', 'kandang-ayam-kampung', 'Kandang umbaran & postal untuk ayam kampung dan ayam bangkok.'),
  ('Kandang DOC / Anakan', 'kandang-doc-anakan', 'Kandang box brooder untuk anak ayam (DOC) lengkap penghangat.'),
  ('Aksesoris & Sparepart', 'aksesoris-sparepart', 'Tempat pakan, nipple minum, egg tray, dan sparepart kandang.')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------------------------
-- Tabel: products (produk kandang ayam)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(150) NOT NULL,
  `slug`         VARCHAR(180) NOT NULL UNIQUE,
  `category_id`  INT DEFAULT NULL,
  `price`        DECIMAL(12,2) DEFAULT 0.00,
  `unit`         VARCHAR(30) DEFAULT 'unit',
  `short_desc`   VARCHAR(300) DEFAULT NULL,
  `description`  MEDIUMTEXT DEFAULT NULL,
  `specs`        TEXT DEFAULT NULL,
  `material`     VARCHAR(120) DEFAULT NULL,
  `dimensions`   VARCHAR(120) DEFAULT NULL,
  `capacity`     VARCHAR(120) DEFAULT NULL,
  `image_path`   VARCHAR(255) DEFAULT NULL,
  `is_featured`  TINYINT(1) DEFAULT 0,
  `stock_status` ENUM('ready','preorder','habis') DEFAULT 'ready',
  `views`        INT DEFAULT 0,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`is_featured`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products`
  (`name`, `slug`, `category_id`, `price`, `unit`, `short_desc`, `description`, `specs`, `material`, `dimensions`, `capacity`, `is_featured`, `stock_status`) VALUES
  ('Kandang Baterai Ayam Petelur 4 Pintu', 'kandang-baterai-ayam-petelur-4-pintu', 1, 850000, 'set',
   'Kandang baterai galvanis 4 pintu, kemiringan lantai presisi untuk telur menggelinding otomatis ke talang.',
   '<p>Kandang baterai <strong>Veros Djaya Steel</strong> dirancang khusus untuk peternak ayam petelur skala kecil hingga besar. Terbuat dari kawat baja galvanis anti karat dengan lapisan seng tebal, tahan cuaca dan awet bertahun-tahun.</p><p>Desain lantai miring 7&deg; membuat telur langsung menggelinding ke talang penampung sehingga mengurangi telur pecah dan mempermudah panen. Tersedia tempat pakan memanjang dan jalur nipple minum.</p><ul><li>Kawat galvanis anti karat</li><li>Lantai miring anti telur pecah</li><li>Mudah dibongkar-pasang (knockdown)</li></ul>',
   'Kapasitas 8 ekor per unit;Sistem koleksi telur otomatis;Talang pakan galvanis;Knockdown / bongkar pasang',
   'Kawat baja galvanis Ø 2,5 mm', 'P 100 x L 40 x T 40 cm (per pintu)', '8 ekor ayam petelur', 1, 'ready'),

  ('Kandang Baterai Petelur 3 Susun (Battery Cage)', 'kandang-baterai-petelur-3-susun', 1, 2400000, 'set',
   'Kandang baterai bertingkat 3 susun hemat lahan, kapasitas besar, cocok untuk skala komersial.',
   '<p>Kandang baterai 3 susun (battery cage) memaksimalkan kapasitas ayam petelur pada lahan terbatas. Struktur rangka baja galvanis kokoh menopang 3 tingkat tanpa goyang.</p><p>Setiap tingkat memiliki talang telur, talang pakan, dan saluran kotoran terpisah untuk menjaga kebersihan kandang.</p>',
   'Kapasitas s/d 96 ekor;3 tingkat susun;Talang kotoran terpisah;Rangka baja galvanis',
   'Rangka baja galvanis + kawat Ø 2,5 mm', 'P 195 x L 70 x T 165 cm', 'Hingga 96 ekor', 1, 'ready'),

  ('Kandang Panggung Broiler Galvanis', 'kandang-panggung-broiler-galvanis', 2, 1250000, 'unit',
   'Kandang panggung untuk ayam broiler dengan sirkulasi udara maksimal dan lantai slat mudah dibersihkan.',
   '<p>Kandang panggung broiler Veros Djaya Steel menjaga ayam pedaging tetap kering dan sehat. Lantai slat galvanis memisahkan kotoran dari ayam sehingga menekan risiko penyakit.</p><p>Sirkulasi udara yang baik mempercepat pertumbuhan dan menekan angka kematian.</p>',
   'Lantai slat galvanis;Sirkulasi udara maksimal;Kaki panggung anti rayap;Mudah dibersihkan',
   'Baja galvanis + kawat las', 'P 200 x L 100 x T 120 cm', '25-30 ekor broiler', 1, 'ready'),

  ('Kandang Umbaran Ayam Kampung Knockdown', 'kandang-umbaran-ayam-kampung-knockdown', 3, 975000, 'unit',
   'Kandang umbaran semi-terbuka untuk ayam kampung/bangkok, sistem knockdown mudah dipindah.',
   '<p>Kandang umbaran ideal untuk ayam kampung dan ayam bangkok yang butuh ruang gerak. Rangka galvanis ringan namun kuat, mudah dibongkar pasang saat perlu dipindah.</p>',
   'Sistem knockdown;Rangka galvanis ringan;Cocok ayam kampung & bangkok;Ada pintu perawatan',
   'Baja galvanis ringan', 'P 150 x L 100 x T 150 cm', '10-15 ekor ayam kampung', 0, 'ready'),

  ('Box Brooder Penghangat DOC Anak Ayam', 'box-brooder-penghangat-doc-anak-ayam', 4, 650000, 'unit',
   'Kandang box brooder untuk DOC lengkap dudukan lampu penghangat, menjaga suhu anak ayam stabil.',
   '<p>Box brooder Veros dirancang menjaga suhu ideal untuk anak ayam (DOC) pada masa kritis. Dinding rapat menahan panas, lantai kawat halus aman untuk kaki DOC.</p>',
   'Dudukan lampu penghangat;Lantai kawat halus aman DOC;Dinding penahan panas;Nampan kotoran laci',
   'Kawat galvanis halus + rangka', 'P 120 x L 60 x T 45 cm', '50-70 ekor DOC', 0, 'ready'),

  ('Tempat Pakan Ayam Gantung Galvanis 5kg', 'tempat-pakan-ayam-gantung-galvanis-5kg', 5, 85000, 'pcs',
   'Tempat pakan gantung anti tumpah kapasitas 5kg, bahan galvanis awet.',
   '<p>Tempat pakan gantung galvanis kapasitas 5 kg, desain anti tumpah dan anti berak. Cocok untuk semua jenis kandang ayam.</p>',
   'Kapasitas 5 kg;Anti tumpah;Bahan galvanis;Gantungan kuat',
   'Plat galvanis', 'Ø 33 x T 36 cm', '5 kg pakan', 0, 'ready'),

  ('Nipple Drinker Set Minum Otomatis', 'nipple-drinker-set-minum-otomatis', 5, 12000, 'pcs',
   'Nipple minum otomatis stainless, higienis, hemat air untuk kandang baterai.',
   '<p>Nipple drinker stainless steel memberi air bersih tanpa tumpah. Sistem tetes otomatis menjaga air tetap higienis dan menghemat penggunaan air.</p>',
   'Stainless steel;Sistem tetes otomatis;Higienis & hemat air;Mudah dipasang',
   'Stainless steel 304', 'Standar drat 3/8"', '1 nipple / 8-10 ekor', 0, 'ready'),

  ('Kandang Baterai Petelur 2 Susun', 'kandang-baterai-petelur-2-susun', 1, 1650000, 'set',
   'Kandang baterai 2 susun untuk peternak menengah, hemat lahan dengan kapasitas besar.',
   '<p>Kandang baterai 2 susun cocok untuk peternak menengah. Kapasitas besar dengan tetap hemat lahan, dilengkapi sistem koleksi telur dan talang kotoran.</p>',
   'Kapasitas s/d 64 ekor;2 tingkat susun;Sistem koleksi telur;Rangka galvanis kokoh',
   'Rangka baja galvanis + kawat Ø 2,5 mm', 'P 195 x L 70 x T 115 cm', 'Hingga 64 ekor', 0, 'preorder')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------------------------
-- Tabel: settings (pengaturan situs — kontak, identitas, dsb.)
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(60) PRIMARY KEY,
  `setting_value` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
  ('company_name', 'Veros Djaya Steel'),
  ('tagline', 'Spesialis Kandang Ayam Baja Galvanis Berkualitas'),
  ('whatsapp', '62859191749378'),
  ('telegram', '+6287821381136'),
  ('email', 'info@verosdjayasteel.com'),
  ('phone', '0859-1917-49378'),
  ('address', 'Jl. Industri Baja No. 1, Indonesia'),
  ('hero_title', 'Kandang Ayam Baja Galvanis Kuat, Awet & Anti Karat'),
  ('hero_subtitle', 'Produsen kandang ayam petelur, broiler, kampung, dan box DOC. Bahan galvanis berkualitas, harga pabrik, siap kirim seluruh Indonesia.'),
  ('about_text', 'Veros Djaya Steel adalah produsen kandang ayam berbahan baja galvanis anti karat. Berpengalaman melayani peternak skala rumahan hingga komersial di seluruh Indonesia, kami mengutamakan kualitas material, ketepatan ukuran, dan daya tahan produk.'),
  ('working_hours', 'Senin - Sabtu, 08.00 - 17.00 WIB')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

SET foreign_key_checks = 1;
