<?php
// T-29: upload bukti pembayaran (server-side) — mendukung kirim ulang setelah ditolak
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';

$user = $_SESSION['user'] ?? null;
if (!$user) { redirect('/Booking_Pemancingan/public/login.php'); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/Booking_Pemancingan/public/riwayat.php'); }
csrf_verify();

$kode = $_POST['kode'] ?? '';
if (!$kode) { exit('Kode booking tidak ada.'); }

$stmt = $pdo->prepare('SELECT id, total_harga FROM bookings WHERE kode_booking = ? AND user_id = ? AND status = ?');
$stmt->execute([$kode, $user['id'], 'menunggu_pembayaran']);
$booking = $stmt->fetch();
if (!$booking) { exit('Booking tidak ditemukan atau sudah dibayar.'); }

// Nominal tidak lagi diterima dari form — dihitung dari jenis pembayaran (DP 50% / Lunas 100%)
$total = (float)$booking['total_harga'];
$jenis = $_POST['jenis'] ?? '';
if (!in_array($jenis, ['dp', 'lunas'], true)) {
    http_response_code(400);
    exit('Pilih jumlah pembayaran: DP 50% atau Lunas.');
}
$nominal = $jenis === 'dp' ? (int)round($total * 0.5) : (int)round($total);

if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
    exit('Upload gagal. Coba lagi.');
}
$file = $_FILES['bukti'];
$maxBytes = 5 * 1024 * 1024;
if ($file['size'] > $maxBytes) { exit('Ukuran file maksimal 5MB.'); }

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!in_array($mime, ['image/jpeg', 'image/png'], true)) { exit('Format harus JPG atau PNG.'); }

$ext = $mime === 'image/png' ? 'png' : 'jpg';
$namaBaru = 'bukti_' . $booking['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$tujuan = __DIR__ . '/uploads/' . $namaBaru;
if (!move_uploaded_file($file['tmp_name'], $tujuan)) { exit('Gagal menyimpan file.'); }

// Ambil nama file bukti SEBELUM upsert — setelah upsert, kolom sudah berisi nama baru.
$stmtLama = $pdo->prepare('SELECT bukti_file FROM payments WHERE booking_id = ?');
$stmtLama->execute([$booking['id']]);
$fileLama = $stmtLama->fetchColumn();

// Upsert: satu booking hanya punya satu baris payment (booking_id UNIQUE).
// Kirim ulang setelah "ditolak" akan memperbarui baris yang sama.
$stmt = $pdo->prepare("INSERT INTO payments (booking_id, jenis, nominal, bukti_file, status) VALUES (?, ?, ?, ?, 'menunggu')
    ON DUPLICATE KEY UPDATE jenis = VALUES(jenis), nominal = VALUES(nominal), bukti_file = VALUES(bukti_file), status = 'menunggu', paid_at = NOW()");
$stmt->execute([$booking['id'], $jenis, $nominal, $namaBaru]);

// Hapus file bukti lama supaya tidak menumpuk di uploads/ saat bukti dikirim ulang
if ($fileLama && $fileLama !== $namaBaru && basename($fileLama) === $fileLama) {
    @unlink(__DIR__ . '/uploads/' . $fileLama);
}

redirect('/Booking_Pemancingan/public/riwayat.php');