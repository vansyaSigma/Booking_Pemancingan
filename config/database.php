<?php
// config/database.php — Koneksi PDO ke MySQL/MariaDB (XAMPP)

// Jangan tampilkan error ke Pengunjung saat di-hosting (bocorkan path absolut & stack trace).
// Gate memakai SERVER_ADDR (alamat socket hasil koneksi) BUKAN SERVER_NAME: di Apache,
// SERVER_NAME diturunkan dari header "Host" bila UseCanonicalName Off (nilai default),
// sehingga siapa pun bisa mengaktifkan display_errors hanya dengan mengirim "Host: localhost".
// Secara lokal (127.0.0.1 / ::1) error tetap tampil supaya mudah saat ngoding.
$isLokal = in_array($_SERVER['SERVER_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
if (!$isLokal) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

$DB_HOST = 'localhost';
$DB_NAME = 'kolamkita';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default kosong
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // Endpoint API memakai JSON — jangan kirim HTML di tengah respons JSON
    $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false
        || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false;
    http_response_code(500);
    if ($isApi) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Layanan sedang bermasalah. Coba lagi sebentar.']);
    } else {
        exit('Koneksi database gagal. Pastikan MySQL di XAMPP berjalan dan database "kolamkita" sudah di-import.');
    }
    exit;
}
