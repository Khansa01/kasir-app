<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
define('DB_NAME', 'kasir_db');

define('APP_NAME', 'KasirKu');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function generateKode() {
    return 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

function clean($str) {
    return htmlspecialchars(strip_tags(trim($str)));
}