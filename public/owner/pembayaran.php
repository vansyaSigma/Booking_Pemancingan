<?php
$pageTitle = 'Verifikasi Pembayaran';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

$ownerId = $_SESSION['user']['id'];

// Transisi status yang diizinkan — dipakai OLEH HANDLER (validasi) dan OLEH VIEW (tombol),
// jadi tampilan dan aturan server tidak mungkin berbeda.
// Mencegah crafted POST mengubah status yang tidak valid (mis. 'selesai' dibalik jadi 'dikonfirmasi').
$allowed = [
    'menunggu_pembayaran' => ['terima', 'tolak', 'batal'],
    'dikonfirmasi'        => ['lunas', 'selesai', 'batal'],
    'lunas'               => ['selesai', 'batal'],
    'selesai'             => [],
    'dibatalkan'          => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    // Transisi status yang diizinkan — didefinisikan di atas (dipakai handler & view).
    // Pastikan booking milik kolam owner ini
    $stmt = $pdo->prepare('SELECT b.*, p.status AS status_bayar FROM bookings b
        JOIN kolam k ON k.id = b.kolam_id
        LEFT JOIN payments p ON p.booking_id = b.id
        WHERE b.id = ? AND k.owner_id = ?');
    $stmt->execute([$bookingId, $ownerId]);
    $b = $stmt->fetch();

    if (!$b) {
        redirect('/Booking_Pemancingan/public/owner/pembayaran.php?err=' . urlencode('Booking tidak ditemukan.'));
    }
    if (!in_array($aksi, $allowed[$b['status']] ?? [], true)) {
        redirect('/Booking_Pemancingan/public/owner/pembayaran.php?err='
            . urlencode('Aksi "' . $aksi . '" tidak bisa dipakai untuk booking berstatus "' . str_replace('_', ' ', $b['status']) . '".'));
    }
    // Verifikasi bayar hanya boleh saat payment masih menunggu
    if (in_array($aksi, ['terima', 'tolak'], true) && $b['status_bayar'] !== 'menunggu') {
        redirect('/Booking_Pemancingan/public/owner/pembayaran.php?err=' . urlencode('Pembayaran ini sudah diproses sebelumnya.'));
    }

    // Semua perubahan status dilakukan dalam satu transaksi supaya tidak pernah ada
// payment "diterima" yang booking-nya masih "menunggu_pembayaran" (atau sebaliknya).
$pdo->beginTransaction();
try {
    if ($aksi === 'terima') {
        $pdo->prepare("UPDATE payments SET status='diterima' WHERE booking_id=?")->execute([$bookingId]);
        $pdo->prepare("UPDATE bookings SET status='dikonfirmasi' WHERE id=?")->execute([$bookingId]);
    } elseif ($aksi === 'tolak') {
        $pdo->prepare("UPDATE payments SET status='ditolak' WHERE booking_id=?")->execute([$bookingId]);
    } elseif ($aksi === 'lunas') {
        // Pelunasan dicatat penuh (bukan cuma ganti status) supaya SUM(nominal) di dashboard benar.
        // Upsert: kalau belum ada baris payment, tetap dibuat.
        // bukti_file dibersihkan: kalau tidak, badge "Lunas" tampil di samping screenshot DP 50%
        // seolah-olah itu bukti pelunasan. Pelunasan dicatat manual oleh pemilik, tanpa bukti baru.
        $pdo->prepare("INSERT INTO payments (booking_id, jenis, nominal, bukti_file, status) VALUES (?, 'lunas', ?, NULL, 'diterima')
            ON DUPLICATE KEY UPDATE jenis='lunas', nominal=VALUES(nominal), bukti_file=NULL, status='diterima', paid_at=NOW()")
            ->execute([$bookingId, $b['total_harga']]);
        $pdo->prepare("UPDATE bookings SET status='lunas' WHERE id=?")->execute([$bookingId]);
    } elseif ($aksi === 'selesai') {
        $pdo->prepare("UPDATE bookings SET status='selesai' WHERE id=?")->execute([$bookingId]);
    } elseif ($aksi === 'batal') {
        $pdo->prepare("UPDATE bookings SET status='dibatalkan' WHERE id=?")->execute([$bookingId]);
        // Payment ikut dibuang. Kalau dibiarkan 'diterima', uang booking yang dibatalkan
        // tetap terhitung sebagai pendapatan di dashboard pemilik.
        $pdo->prepare("UPDATE payments SET status='ditolak' WHERE booking_id=? AND status <> 'ditolak'")->execute([$bookingId]);
    }
    $pdo->commit();
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    redirect('/Booking_Pemancingan/public/owner/pembayaran.php?err=' . urlencode('Perubahan gagal disimpan. Coba lagi.'));
}
    redirect('/Booking_Pemancingan/public/owner/pembayaran.php?ok=' . urlencode('Booking ' . $b['kode_booking'] . ' diperbarui.'));
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
    <?php if ($msg = $_GET['ok'] ?? ''): ?><p class="badge badge-hijau" style="display:block;margin-bottom:1rem"><?= e($msg) ?></p><?php endif; ?>
    <?php if ($msgErr = $_GET['err'] ?? ''): ?><p class="badge badge-merah" style="display:block;margin-bottom:1rem"><?= e($msgErr) ?></p><?php endif; ?>
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
            <?php
            $aksiBoleh = $allowed[$r['status_booking']] ?? [];
            // Verifikasi bayar hanya tampil saat payment masih 'menunggu'
            if ($r['status_bayar'] !== 'menunggu') {
                $aksiBoleh = array_values(array_diff($aksiBoleh, ['terima', 'tolak']));
            }
            $labelAksi = [
                'terima'  => ['Terima', 'btn-outline'],
                'tolak'   => ['Tolak', 'btn-danger'],
                'lunas'   => ['Tandai lunas', 'btn-outline'],
                'selesai' => ['Selesai', 'btn-outline'],
                'batal'   => ['Batal', 'btn-danger'],
            ];
            ?>
            <?php if (!$aksiBoleh): ?>—<?php endif; ?>
            <?php foreach ($aksiBoleh as $a):
              [$teksAksi, $kelasAksi] = $labelAksi[$a]; ?>
              <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $r['id'] ?>"><input type="hidden" name="aksi" value="<?= $a ?>"><button class="btn <?= $kelasAksi ?> btn-sm"><?= $teksAksi ?></button></form>
            <?php endforeach; ?>
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

