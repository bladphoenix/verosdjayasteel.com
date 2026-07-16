<?php
// admin/setup.php — bootstrap akun admin (aman). Jalankan SEKALI, lalu HAPUS file ini.
// Aturan keamanan:
//   - Hanya bisa dijalankan bila BELUM ada akun admin (tabel users kosong),
//     ATAU bila Anda sudah login sebagai admin (untuk reset password).
//   - Tidak pernah menampilkan password di layar.
require_once __DIR__ . '/includes/session_boot.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../inc/functions.php';

// --- Ubah kredensial di sini SEBELUM menjalankan ---
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'admin123';   // WAJIB ganti ke password kuat milik Anda!
$ADMIN_NAME = 'Administrator';
// ----------------------------------------------------

$userCount   = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$loggedIn    = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$allowed     = ($userCount === 0) || $loggedIn;

$done = false; $err = '';

if (!$allowed) {
    http_response_code(403);
} elseif ($ADMIN_PASS === 'veros2024') {
    $err = 'Demi keamanan, ganti dulu nilai $ADMIN_PASS di dalam file ini sebelum menjalankan setup.';
} else {
    try {
        $hash = password_hash($ADMIN_PASS, PASSWORD_DEFAULT);
        $pdo->prepare(
            "INSERT INTO users (username, password, nama) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE password = VALUES(password), nama = VALUES(nama)"
        )->execute([$ADMIN_USER, $hash, $ADMIN_NAME]);
        $done = true;
    } catch (PDOException $e) {
        $err = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="robots" content="noindex,nofollow"><title>Setup Admin</title>
<style>body{font-family:system-ui,sans-serif;max-width:560px;margin:60px auto;padding:0 20px;line-height:1.6}
.box{border:1px solid #ddd;border-radius:12px;padding:24px}.ok{color:#16a34a}.err{color:#dc2626}
code{background:#f1f5f9;padding:2px 6px;border-radius:4px}</style></head><body>
<div class="box">
<h2>Setup Akun Admin — Veros Djaya Steel</h2>
<?php if (!$allowed): ?>
  <p class="err">⛔ Akses ditolak. Akun admin sudah ada.</p>
  <p>Untuk mereset password, login dulu di <a href="login.php">halaman login</a>, lalu buka kembali halaman ini.
     Jika Anda lupa password, reset hash lewat phpMyAdmin atau hapus baris di tabel <code>users</code> lalu jalankan ulang setup ini.</p>
<?php elseif ($done): ?>
  <p class="ok">✅ Akun admin berhasil dibuat / diperbarui.</p>
  <p><b>Username:</b> <code><?php echo htmlspecialchars($ADMIN_USER); ?></code><br>
     <b>Password:</b> sesuai yang Anda set di file ini.</p>
  <p class="err"><b>PENTING:</b> HAPUS file <code>admin/setup.php</code> sekarang juga.</p>
  <p><a href="login.php">→ Menuju halaman login</a></p>
<?php else: ?>
  <p class="err">❌ <?php echo htmlspecialchars($err ?: 'Gagal.'); ?></p>
  <p>Pastikan database sudah diimport (<code>database.sql</code>) dan konfigurasi <code>admin/config/db.php</code> benar.</p>
<?php endif; ?>
</div></body></html>
