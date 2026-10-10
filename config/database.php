<?php
// config/database.php — Koneksi PDO ke MySQL/MariaDB (XAMPP)
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
