<?php
// T-27: link WhatsApp notifikasi booking masuk ke pemilik kolam
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';

$kode = $_GET['kode'] ?? '';
$user = $_SESSION['user'] ?? null;
if (!$user) { redirect('/Booking_Pemancingan/public/login.php'); }

// Hanya pemancing yang booking-nya ATAU pemilik kolam yang boleh membuka link notifikasi ini.
$stmt = $pdo->prepare('SELECT b.*, k.nama AS nama_kolam, s.jam, p.no_wa AS wa_pemilik
    FROM bookings b
    JOIN kolam k ON k.id = b.kolam_id
    JOIN slots s ON s.id = b.slot_id
    JOIN users p ON p.id = k.owner_id
    WHERE b.kode_booking = ?
      AND b.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
      AND (b.user_id = ? OR k.owner_id = ?)');
$stmt->execute([$kode, $user['id'], $user['id']]);
$b = $stmt->fetch();
if (!$b) {
    http_response_code(404);
    exit('Booking tidak ditemukan, link sudah kedaluwarsa, atau bukan booking kamu.');
}

$msg = "Booking baru masuk: {$b['kode_booking']} di {$b['nama_kolam']} tanggal {$b['tanggal']} jam " . substr($b['jam'], 0, 5) . '. Total: Rp' . number_format($b['total_harga'], 0, ',', '.') . '. Mohon dicek di dashboard.';
$wa = preg_replace('/[^0-9]/', '', $b['wa_pemilik']);
if (substr($wa, 0, 1) === '0') { $wa = '62' . substr($wa, 1); }
redirect('https://wa.me/' . $wa . '?text=' . rawurlencode($msg));
