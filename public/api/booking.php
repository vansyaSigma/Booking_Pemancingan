<?php
// public/api/booking.php — T-26: booking atomic via DB
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
$user = $_SESSION['user'] ?? null;
if (!$user) {
    http_response_code(401);
    exit('Silakan masuk terlebih dahulu. <a href="/Booking_Pemancingan/public/login.php">Masuk</a>');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
csrf_verify();

$kolamId = (int)($_POST['kolam'] ?? 0);
$slotJam = $_POST['slot'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';
$jumlah = (int)($_POST['jumlah_orang'] ?? 0);
$catatan = trim($_POST['catatan'] ?? '');

$errors = [];
if (!$kolamId || !$slotJam || !$tanggal) $errors[] = 'Data slot tidak lengkap.';
if ($jumlah < 1) $errors[] = 'Jumlah orang minimal 1.';
if ($errors) {
    http_response_code(400);
    exit('<h1>Booking gagal</h1><ul><li>' . implode('</li><li>', array_map('e', $errors)) . '</li></ul><a href="javascript:history.back()">Kembali</a>');
}

try {
    $pdo->beginTransaction();

    // Ambil kolam + harga
    $stmt = $pdo->prepare('SELECT id, nama, harga_per_slot FROM kolam WHERE id = ? AND is_aktif = 1');
    $stmt->execute([$kolamId]);
    $kolam = $stmt->fetch();
    if (!$kolam) { throw new Exception('Kolam tidak ditemukan.'); }

    // Ambil slot
    $stmt = $pdo->prepare('SELECT id, status FROM slots WHERE kolam_id = ? AND tanggal = ? AND jam = ?');
    $stmt->execute([$kolamId, $tanggal, $slotJam . ':00']);
    $slot = $stmt->fetch();
    if (!$slot) { throw new Exception('Slot tidak tersedia.'); }
    if ($slot['status'] === 'tutup') { throw new Exception('Slot ditutup oleh pemilik.'); }

    // Cek booking aktif untuk slot ini (idempoten + anti double booking)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE slot_id = ? AND tanggal = ? AND status <> 'dibatalkan'");
    $stmt->execute([$slot['id'], $tanggal]);
    if ((int)$stmt->fetchColumn() > 0) { throw new Exception('Slot ini sudah dipesan. Pilih jam lain.'); }

    // Batas 10 booking aktif per akun
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status IN ('menunggu_pembayaran','dikonfirmasi')");
    $stmt->execute([$user['id']]);
    if ((int)$stmt->fetchColumn() >= 10) { throw new Exception('Kamu sudah punya 10 booking aktif. Selesaikan dulu yang ada.'); }

    $total = (float)$kolam['harga_per_slot'] * $jumlah;
    $kode = 'BK-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));

    $stmt = $pdo->prepare("INSERT INTO bookings (kode_booking, user_id, kolam_id, slot_id, tanggal, jumlah_orang, total_harga, status, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu_pembayaran', ?)");
    $stmt->execute([$kode, $user['id'], $kolamId, $slot['id'], $tanggal, $jumlah, $total, $catatan]);

    $pdo->commit();

    redirect('../booking_sukses.php?kode=' . urlencode($kode) . '&kolam=' . urlencode($kolam['nama']) . '&tanggal=' . urlencode($tanggal) . '&jam=' . urlencode($slotJam) . '&total=' . (int)$total);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    $isDuplicate = $e->getCode() === '23000' || str_contains($e->getMessage(), '1062');
    $pesan = $isDuplicate
        ? 'Slot ini sudah dipesan orang lain. Pilih jam lain.'
        : 'Booking gagal disimpan. Coba lagi sebentar.';
    http_response_code($isDuplicate ? 409 : 500);
    exit('<h1>Booking gagal</h1><p>' . e($pesan) . '</p><a href="javascript:history.back()">Kembali</a>');
} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(409);
    exit('<h1>Booking gagal</h1><p>' . e($e->getMessage()) . '</p><a href="javascript:history.back()">Kembali</a>');
}
