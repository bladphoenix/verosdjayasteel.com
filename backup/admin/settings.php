<?php
// admin/settings.php — pengaturan situs (kontak, identitas, hero)
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../inc/functions.php';

// Field yang bisa diedit
$fields = [
    'company_name'  => 'Nama Perusahaan',
    'tagline'       => 'Tagline / Slogan',
    'whatsapp'      => 'Nomor WhatsApp (format: 6287821381136)',
    'telegram'      => 'Telegram (mis. +6287821381136 atau @username)',
    'phone'         => 'Telepon (tampilan)',
    'email'         => 'Email',
    'address'       => 'Alamat',
    'working_hours' => 'Jam Operasional',
    'hero_title'    => 'Judul Hero (Beranda)',
    'hero_subtitle' => 'Subjudul Hero (Beranda)',
    'about_text'    => 'Teks Tentang Kami',
];
$textareas = ['hero_subtitle', 'about_text', 'address'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check()) {
    $error = 'Sesi tidak valid (CSRF). Muat ulang halaman lalu coba lagi.';
  } else {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        foreach ($fields as $key => $label) {
            $val = trim($_POST[$key] ?? '');
            if ($key === 'whatsapp') { $val = preg_replace('/[^0-9]/', '', $val); }
            $stmt->execute([$key, $val]);
        }
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Pengaturan berhasil disimpan.'];
        header('Location: settings.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Gagal menyimpan: ' . $e->getMessage();
    }
  }
}

$settings = get_settings($pdo);

$page_title = 'Pengaturan';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div><h2><?php echo icon('settings'); ?> Pengaturan Situs</h2>
  <div class="sub">Identitas &amp; kontak yang tampil di seluruh halaman website.</div></div>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?php echo icon('shield'); ?> <?php echo e($error); ?></div><?php endif; ?>

<div class="alert alert-info"><?php echo icon('check'); ?> Nomor WhatsApp &amp; Telegram di sini otomatis dipakai untuk semua tombol "Pesan" &amp; "Chat" di website.</div>

<form method="post">
  <?php echo csrf_field(); ?>
  <div class="form-grid">
    <div class="card">
      <div class="card__head"><span><?php echo icon('box'); ?> Identitas &amp; Konten</span></div>
      <div class="card__body">
        <?php foreach (['company_name','tagline','hero_title','hero_subtitle','about_text'] as $key): ?>
          <div class="field">
            <label for="<?php echo $key; ?>"><?php echo e($fields[$key]); ?></label>
            <?php if (in_array($key, $textareas, true)): ?>
              <textarea class="textarea" id="<?php echo $key; ?>" name="<?php echo $key; ?>"><?php echo e($settings[$key] ?? ''); ?></textarea>
            <?php else: ?>
              <input class="input" type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo e($settings[$key] ?? ''); ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="card__head"><span><?php echo icon('phone'); ?> Kontak</span></div>
      <div class="card__body">
        <?php foreach (['whatsapp','telegram','phone','email','address','working_hours'] as $key): ?>
          <div class="field">
            <label for="<?php echo $key; ?>"><?php echo e($fields[$key]); ?></label>
            <?php if (in_array($key, $textareas, true)): ?>
              <textarea class="textarea" id="<?php echo $key; ?>" name="<?php echo $key; ?>" style="min-height:70px;"><?php echo e($settings[$key] ?? ''); ?></textarea>
            <?php else: ?>
              <input class="input" type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo e($settings[$key] ?? ''); ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary btn-block" style="padding:12px;margin-top:6px;"><?php echo icon('check'); ?> Simpan Pengaturan</button>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
