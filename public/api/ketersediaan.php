<?php
// T-25: endpoint JSON dari DB — slot + cek booking aktif
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

$kolam = $_GET['kolam'] ?? null;
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
if (!$kolam) { http_response_code(400); echo json_encode(['error' => 'Parameter kolam wajib diisi']); exit; }

$stmt = $pdo->prepare("
    SELECT s.jam, s.status AS slot_status, b.id AS booking_id
    FROM slots s
    LEFT JOIN bookings b ON b.slot_id = s.id AND b.tanggal = s.tanggal AND b.status <> 'dibatalkan'
    WHERE s.kolam_id = ? AND s.tanggal = ?
    ORDER BY s.jam
");
$stmt->execute([(int)$kolam, $tanggal]);
$rows = $stmt->fetchAll();

$slots = [];
foreach ($rows as $r) {
    if ($r['slot_status'] === 'tutup') {
        $status = 'tutup';
    } elseif ($r['booking_id']) {
        $status = 'penuh';
    } else {
        $status = 'kosong';
    }
    $slots[] = ['jam' => substr($r['jam'], 0, 5), 'status' => $status];
}

echo json_encode(['kolam' => (int)$kolam, 'tanggal' => $tanggal, 'slots' => $slots]);
