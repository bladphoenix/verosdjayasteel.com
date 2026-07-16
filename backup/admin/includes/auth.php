<?php
// admin/includes/auth.php — proteksi halaman admin.
require_once __DIR__ . '/session_boot.php';

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
