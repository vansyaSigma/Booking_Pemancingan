<?php
$pageTitle = 'Booking Berhasil';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
if (!($_SESSION['user'] ?? null)) { redirect('/Booking_Pemancingan/public/login.php?err=' . urlencode('Silakan masuk dulu.')); }

// Data booking diambil dari database, bukan dari parameter URL, supaya tidak bisa dipalsukan.
$kode = trim($_GET['kode'] ?? '');
$booking = null;
if ($kode !== '') {
    $stmt = $pdo->prepare('SELECT b.*, k.nama AS nama_kolam, s.jam FROM bookings b JOIN kolam k ON k.id = b.kolam_id JOIN slots s ON s.id = b.slot_id WHERE b.kode_booking = ? AND b.user_id = ?');
    $stmt->execute([$kode, $_SESSION['user']['id']]);
    $booking = $stmt->fetch();
}
if (!$booking) {
    http_response_code(404);
    echo '<section class="section container"><h1>Booking tidak ditemukan</h1>'
        . '<p>Kode booking tidak ada, atau booking tersebut bukan milik akun yang sedang login.</p>'
        . '<p><a href="riwayat.php">Lihat riwayat booking saya</a></p></section>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$dp = (int)round($booking['total_harga'] / 2);
?>
<div class="form-card" style="max-width:36rem">
  <h1>Booking berhasil!</h1>
  <div class="ticket" style="margin:1rem 0 1.5rem">
    <p><strong>Kode booking:</strong> <span class="kode"><?= e($booking['kode_booking']) ?></span></p>
    <p><strong>Kolam:</strong> <?= e($booking['nama_kolam']) ?></p>
    <p><strong>Tanggal:</strong> <?= e($booking['tanggal']) ?></p>
    <p><strong>Jam:</strong> <span class="mono"><?= e(substr($booking['jam'], 0, 5)) ?></span></p>
    <p><strong>Jumlah orang:</strong> <?= (int)$booking['jumlah_orang'] ?></p>
    <p><strong>Total:</strong> <span class="mono"><?= format_rupiah($booking['total_harga']) ?></span></p>
    <?php $bs = $booking['status']; ?>
    <p><strong>Status:</strong> <span class="badge <?= $bs === 'selesai' || $bs === 'lunas' ? 'badge-hijau' : ($bs === 'dibatalkan' ? 'badge-merah' : ($bs === 'dikonfirmasi' ? 'badge-biru' : 'badge-kuning')) ?>"><?= e(str_replace('_', ' ', $bs)) ?></span></p>
    <?php if (trim($booking['catatan']) !== ''): ?>
      <p><strong>Catatan:</strong> <?= e($booking['catatan']) ?></p>
    <?php endif; ?>
  </div>
  <p style="margin-bottom:1rem">Simpan kode booking ini, lalu kabari pemilik supaya slotmu dikonfirmasi.</p>
  <?php if ($booking['status'] === 'menunggu_pembayaran'): ?>
    <p style="margin-bottom:1rem">DP <strong><?= format_rupiah($dp) ?></strong> atau lunas <strong><?= format_rupiah($booking['total_harga']) ?></strong>. Kirim bukti di halaman riwayat.</p>
  <?php endif; ?>
  <a class="btn btn-primary btn-block" href="wa_notif.php?kode=<?= urlencode($booking['kode_booking']) ?>" target="_blank" rel="noopener">Notifikasi pemilik lewat WhatsApp</a>
  <p style="margin-top:1.5rem"><a href="riwayat.php">&larr; Ke riwayat booking</a> &middot; <a href="semua-kolam.php">Cari kolam lain</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>