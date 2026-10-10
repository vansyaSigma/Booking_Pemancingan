<?php
// T-25: endpoint JSON dari DB — slot + cek booking aktif
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';

$kolam = (int)($_GET['kolam'] ?? 0);
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
if (!$kolam) { http_response_code(400); echo json_encode(['error' => 'Parameter kolam wajib diisi']); exit; }

// Tanggal harus format kalender yang benar (tolak "2026-02-31", "abc", kosong)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format tanggal harus YYYY-MM-DD.']);
    exit;
}
$dObj = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$dObj || $dObj->format('Y-m-d') !== $tanggal) {
    http_response_code(400);
    echo json_encode(['error' => 'Tanggal tidak valid.']);
    exit;
}

// Lazy generation: kalau horizon slot kolam sudah mepet, lengkapi di sini.
// Tanpa ini kolam akan berhenti bisa dibooking 14 hari setelah dibuat.
if ($kolam && $tanggal >= date('Y-m-d')) {
    ensure_slot_horizon($pdo, $kolam, 14);
}

$stmt = $pdo->prepare("
    SELECT s.jam, s.status AS slot_status, b.id AS booking_id
    FROM slots s
    LEFT JOIN bookings b ON b.slot_id = s.id AND b.tanggal = s.tanggal AND b.status <> 'dibatalkan'
    WHERE s.kolam_id = ? AND s.tanggal = ?
    ORDER BY s.jam
");
$stmt->execute([$kolam, $tanggal]);
$rows = $stmt->fetchAll();

$slots = [];
$lewat = false;
foreach ($rows as $r) {
    $jam = substr($r['jam'], 0, 5);
    // Jam yang sudah berlanjur hari ini tidak bisa dipesan lagi
    $sudahLewat = ($tanggal === date('Y-m-d')) && (jam_menit($jam) <= (int) date('G') * 60 + (int) date('i'));

    if ($sudahLewat) {
        $lewat = true;
        $status = 'lewat';
    } elseif ($r['slot_status'] === 'tutup') {
        $status = 'tutup';
    } elseif ($r['booking_id']) {
        $status = 'penuh';
    } else {
        $status = 'kosong';
    }
    $slots[] = ['jam' => $jam, 'status' => $status];
}

echo json_encode([
    'kolam' => $kolam,
    'tanggal' => $tanggal,
    'slots' => $slots,
    'lewat' => $lewat,
]);