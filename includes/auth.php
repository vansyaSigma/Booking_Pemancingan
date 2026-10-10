<?php
// includes/auth.php — RBAC penuh (T-23)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
require_once __DIR__ . '/../config/helpers.php';

$_user = $_SESSION['user'] ?? null;
if (!$_user) {
    redirect('/Booking_Pemancingan/public/login.php?err=' . urlencode('Silakan masuk terlebih dahulu.'));
}
if ($_user['role'] !== 'pemilik' && strpos($_SERVER['SCRIPT_NAME'], '/owner/') !== false) {
    http_response_code(403);
    exit('Akses ditolak — halaman khusus pemilik kolam.');
}
