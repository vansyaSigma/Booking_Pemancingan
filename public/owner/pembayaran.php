<?php
$pageTitle = 'Verifikasi Pembayaran';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

$ownerId = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    // Pastikan booking milik kolam owner ini
    $stmt = $pdo->prepare('SELECT b.* FROM bookings b JOIN kolam k ON k.id = b.kolam_id WHERE b.id = ? AND k.owner_id = ?');
    $stmt->execute([$bookingId, $ownerId]);
    $b = $stmt->fetch();
    if ($b) {
        if ($aksi === 'terima') {
            $pdo->prepare("UPDATE payments SET status='diterima' WHERE booking_id=?")->execute([$bookingId]);
            $pdo->prepare("UPDATE bookings SET status='dikonfirmasi' WHERE id=?")->execute([$bookingId]);
        } elseif ($aksi === 'tolak') {
            $pdo->prepare("UPDATE payments SET status='ditolak' WHERE booking_id=?")->execute([$bookingId]);
        } elseif ($aksi === 'lunas') {
            $pdo->prepare("UPDATE payments SET status='diterima' WHERE booking_id=?")->execute([$bookingId]);
            $pdo->prepare("UPDATE bookings SET status='lunas' WHERE id=?")->execute([$bookingId]);
        } elseif ($aksi === 'selesai') {
            $pdo->prepare("UPDATE bookings SET status='selesai' WHERE id=?")->execute([$bookingId]);
        } elseif ($aksi === 'batal') {
            $pdo->prepare("UPDATE bookings SET status='dibatalkan' WHERE id=?")->execute([$bookingId]);
        }
    }
    redirect('/Booking_Pemancingan/public/owner/pembayaran.php');
}

$stmt = $pdo->prepare('SELECT b.id, b.kode_booking, b.tanggal, b.total_harga, b.status AS status_booking, u.nama, u.no_wa, p.nominal, p.jenis, p.bukti_file, p.status AS status_bayar FROM bookings b JOIN kolam k ON k.id = b.kolam_id JOIN users u ON u.id = b.user_id LEFT JOIN payments p ON p.booking_id = b.id WHERE k.owner_id = ? ORDER BY b.created_at DESC');
$stmt->execute([$ownerId]);
$rows = $stmt->fetchAll();
?>
<div class="owner-shell">
  <aside class="owner-side">
    <strong style="color:#fff;display:block;margin-bottom:1rem">KolamKita · Pemilik</strong>
    <a href="dashboard.php">Dashboard</a>
    <a href="kolam.php">Kolam</a>
    <a href="jadwal.php">Jadwal</a>
    <a href="pembayaran.php" class="active">Pembayaran</a>
  </aside>
  <main class="owner-main">
<h1>Verifikasi pembayaran</h1>
    <div class="table-wrap">
    <table>
      <thead><tr><th>Kode booking</th><th>Pemancing</th><th>Tanggal</th><th>Total</th><th>Bukti</th><th>Status bayar</th><th>Status booking</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td class="mono"><?= e($r['kode_booking']) ?></td>
          <td><?= e($r['nama']) ?><br><small class="mono"><?= e($r['no_wa']) ?></small></td>
          <td><?= e($r['tanggal']) ?></td>
          <td class="mono">
            <?php
            $total = (float)$r['total_harga'];
            $nominal = $r['nominal'] !== null ? (float)$r['nominal'] : null;
            $sisa = ($nominal !== null) ? max(0, $total - $nominal) : $total;
            echo format_rupiah($total);
            if ($nominal !== null) {
                echo '<small class="cell-note">dikirim ' . format_rupiah($nominal) . '</small>';
                if ($sisa > 0) {
                    echo '<small class="cell-note cell-note--warn">sisa ' . format_rupiah($sisa) . '</small>';
                }
            } else {
                echo '<small class="cell-note">belum ada pembayaran</small>';
            }
            ?>
          </td>
          <td>
            <?php if ($r['bukti_file']): ?>
              <a href="/Booking_Pemancingan/public/uploads/<?= e($r['bukti_file']) ?>" target="_blank">Lihat bukti</a>
              <?php if ($r['jenis'] === 'dp'): ?><br><small class="badge badge-kuning">DP</small><?php elseif ($r['jenis'] === 'lunas'): ?><br><small class="badge badge-hijau">Lunas</small><?php endif; ?>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td><?php $sb = $r['status_bayar'] ?? 'belum'; echo '<span class="badge ' . ($sb==='diterima' ? 'badge-hijau' : ($sb==='ditolak' ? 'badge-merah' : 'badge-kuning')) . '">' . $sb . '</span>'; ?></td>
          <td><span class="badge badge-biru"><?= e(str_replace('_', ' ', $r['status_booking'])) ?></span></td>
          <td>
            <?php if ($r['status_bayar'] === 'menunggu'): ?>
              <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $r['id'] ?>"><input type="hidden" name="aksi" value="terima"><button class="btn btn-outline btn-sm">Terima</button></form>
              <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $r['id'] ?>"><input type="hidden" name="aksi" value="tolak"><button class="btn btn-danger btn-sm">Tolak</button></form>
            <?php elseif ($r['status_booking'] === 'dikonfirmasi'): ?>
              <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $r['id'] ?>"><input type="hidden" name="aksi" value="lunas"><button class="btn btn-outline btn-sm">Tandai lunas</button></form>
              <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $r['id'] ?>"><input type="hidden" name="aksi" value="selesai"><button class="btn btn-outline btn-sm">Selesai</button></form>
              <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $r['id'] ?>"><input type="hidden" name="aksi" value="batal"><button class="btn btn-danger btn-sm">Batal</button></form>
            <?php elseif ($r['status_booking'] === 'menunggu_pembayaran'): ?>
              <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $r['id'] ?>"><input type="hidden" name="aksi" value="batal"><button class="btn btn-danger btn-sm">Batal</button></form>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
</table>
    </div>
    <p class="hint" style="margin-top:1rem">Terima → status booking jadi dikonfirmasi. Lunas → status booking jadi lunas. Batal → status dibatalkan (slot kembali kosong).</p>
  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

