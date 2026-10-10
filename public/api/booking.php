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
// Tanggal harus benar-benar ada di kalender (tolak "2026-02-31") dan tidak boleh di masa lalu
if ($tanggal) {
    $dTanggal = DateTime::createFromFormat('!Y-m-d', $tanggal);
    if (!$dTanggal || $dTanggal->format('Y-m-d') !== $tanggal) {
        $errors[] = 'Tanggal booking tidak valid.';
    } elseif ($tanggal < date('Y-m-d')) {
        $errors[] = 'Tanggal booking sudah lewat.';
    }
}
if ($jumlah < 1) $errors[] = 'Jumlah orang minimal 1.';
if ($jumlah > 20) $errors[] = 'Maksimal 20 orang per booking. Untuk lebih dari itu, hubungi pemilik kolam.';
if ($errors) {
    http_response_code(400);
    exit('<h1>Booking gagal</h1><ul><li>' . implode('</li><li>', array_map('e', $errors)) . '</li></ul><a href="javascript:history.back()">Kembali</a>');
}

try {
    $pdo->beginTransaction();

    // Ambil kolam + harga + jam operasional
    $stmt = $pdo->prepare('SELECT id, nama, harga_per_slot, jam_buka, jam_tutup FROM kolam WHERE id = ? AND is_aktif = 1');
    $stmt->execute([$kolamId]);
    $kolam = $stmt->fetch();
    if (!$kolam) { throw new Exception('Kolam tidak ditemukan.'); }

    // Ambil slot
    $stmt = $pdo->prepare('SELECT id, status, jam FROM slots WHERE kolam_id = ? AND tanggal = ? AND jam = ?');
    $stmt->execute([$kolamId, $tanggal, $slotJam . ':00']);
    $slot = $stmt->fetch();
    if (!$slot) { throw new Exception('Slot tidak tersedia.'); }
    if ($slot['status'] === 'tutup') { throw new Exception('Slot ditutup oleh pemilik.'); }

    // Penjaga lapis dua: jam slot harus benar-benar di dalam jam operasional kolam,
    // dan tidak boleh sudah lewat (untuk hari ini).
    $menitSlot = jam_menit(substr($slot['jam'], 0, 5));
    if ($menitSlot === null
        || $menitSlot < jam_menit($kolam['jam_buka'])
        || $menitSlot >= jam_menit($kolam['jam_tutup'])) {
        throw new Exception('Jam tersebut di luar jam operasional kolam.');
    }
    if ($tanggal === date('Y-m-d') && $menitSlot <= (int)date('G') * 60 + (int)date('i')) {
        throw new Exception('Jam tersebut sudah lewat.');
    }

    // Cek booking aktif untuk slot ini (idempoten + anti double booking)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE slot_id = ? AND tanggal = ? AND status <> 'dibatalkan'");
    $stmt->execute([$slot['id'], $tanggal]);
    if ((int)$stmt->fetchColumn() > 0) { throw new Exception('Slot ini sudah dipesan. Pilih jam lain.'); }

    // Batas 10 booking aktif per akun
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status IN ('menunggu_pembayaran','dikonfirmasi')");
    $stmt->execute([$user['id']]);
    if ((int)$stmt->fetchColumn() >= 10) { throw new Exception('Kamu sudah punya 10 booking aktif. Selesaikan dulu yang ada.'); }

$total = (float)$kolam['harga_per_slot'] * $jumlah;
$kode = generate_kode_booking();

    $stmt = $pdo->prepare("INSERT INTO bookings (kode_booking, user_id, kolam_id, slot_id, tanggal, jumlah_orang, total_harga, status, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, 'menunggu_pembayaran', ?)");
    $stmt->execute([$kode, $user['id'], $kolamId, $slot['id'], $tanggal, $jumlah, $total, $catatan]);

    $pdo->commit();

    redirect('../booking_sukses.php?kode=' . urlencode($kode));
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
