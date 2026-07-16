<?php
// admin/includes/session_boot.php — mulai sesi dengan cookie yang diperketat.
// Dipakai oleh auth.php, login.php, logout.php, dan setup.php.
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
           || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,   // aktif otomatis saat HTTPS
        'samesite' => 'Lax',     // blokir pengiriman cookie pada POST lintas-situs (mitigasi CSRF)
    ]);
    session_start();
}
