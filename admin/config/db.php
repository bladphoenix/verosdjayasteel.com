<?php
// admin/config/db.php — koneksi database (PDO)
// Sesuaikan nilai di bawah dengan kredensial database Anda di cPanel/hosting.
//
// KEAMANAN (produksi): JANGAN pakai user 'root' tanpa password. Buat user MySQL
// khusus untuk database ini dengan hak akses terbatas + password yang kuat.
// Nilai 'root'/'' di bawah hanya memudahkan uji coba lokal (XAMPP/Laragon).

$host    = 'localhost';
$db      = 'rsudiska_verosdjayasteel.com';        // nama database
$user    = 'rsudiska_verosdjayasteel';                    // GANTI: user database khusus (jangan root di produksi)
$pass    = 'sipaloma123';                        // GANTI: password database yang kuat
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    die('Koneksi database gagal. Periksa konfigurasi di admin/config/db.php.');
}
