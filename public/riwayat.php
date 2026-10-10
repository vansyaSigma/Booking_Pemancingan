<?php
$pageTitle = 'Riwayat Booking';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
if (!($_SESSION['user'] ?? null)) { redirect('/Booking_Pemancingan/public/login.php?err=' . urlencode('Silakan masuk dulu.')); }

$stmt = $pdo->prepare('SELECT b.*, k.nama AS nama_kolam, s.jam, p.status AS bayar_status, p.nominal, p.jenis AS bayar_jenis FROM bookings b JOIN kolam k ON k.id = b.kolam_id JOIN slots s ON s.id = b.slot_id LEFT JOIN payments p ON p.booking_id = b.id WHERE b.user_id = ? ORDER BY b.tanggal DESC, s.jam DESC');
$stmt->execute([$_SESSION['user']['id']]);
$rows = $stmt->fetchAll();
?>
<section class="section container">
  <h1>Riwayat booking saya</h1>
  <?php if (!$rows): ?>
    <p style="margin-top:1.5rem;color:#5f726b">Belum ada booking. <a href="semua-kolam.php">Cari kolam dulu</a>.</p>
  <?php else: ?>
  <div class="table-wrap" style="margin-top:1.5rem">
    <table>
      <thead><tr><th>Kode</th><th>Kolam</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th><th>Pembayaran</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td class="mono"><?= e($r['kode_booking']) ?></td>
          <td><?= e($r['nama_kolam']) ?></td>
          <td><?= e($r['tanggal']) ?></td>
          <td class="mono"><?= e(substr($r['jam'], 0, 5)) ?></td>
          <td class="mono">
            <?php
            $total = (float)$r['total_harga'];
            $nominal = $r['nominal'] !== null ? (float)$r['nominal'] : null;
            echo format_rupiah($total);
            if ($r['bayar_status'] === 'diterima' && $nominal !== null) {
                $sisa = max(0, $total - $nominal);
                if ($sisa > 0) {
                    echo '<small class="cell-note">sisa ' . format_rupiah($sisa) . '</small>';
                } else {
                    echo '<small class="cell-note">lunas</small>';
                }
            } elseif ($nominal !== null) {
                echo '<small class="cell-note">' . format_rupiah($nominal) . ' menunggu verifikasi</small>';
            } else {
                echo '<small class="cell-note">belum ada pembayaran</small>';
            }
            ?>
          </td>
          <td><?php $s = $r['status']; echo '<span class="badge ' . ($s==='selesai'||$s==='lunas' ? 'badge-hijau' : ($s==='dibatalkan' ? 'badge-merah' : ($s==='dikonfirmasi' ? 'badge-biru' : 'badge-kuning'))) . '">' . str_replace('_', ' ', $s) . '</span>'; ?></td>
          <td><?php $bs = $r['bayar_status'] ?? 'belum'; echo '<span class="badge ' . ($bs==='diterima'||$bs==='lunas' ? 'badge-hijau' : ($bs==='ditolak' ? 'badge-merah' : 'badge-kuning')) . '">' . str_replace('_', ' ', $bs) . '</span>'; ?></td>
<td>
            <?php $bs = $r['bayar_status'] ?? null; ?>
            <?php if ($r['status'] === 'menunggu_pembayaran' && $bs !== 'diterima'): ?>
              <form method="POST" action="upload_bukti.php" enctype="multipart/form-data" class="pay-form">
                <?= csrf_field() ?>
                <input type="hidden" name="kode" value="<?= e($r['kode_booking']) ?>">
                <?php $total = (float)$r['total_harga']; $dp = (int) round($total * 0.5); ?>
                <label class="pay-form__row">
                  <span>Bayar</span>
                  <select name="jenis" required>
                    <option value="" selected disabled>Pilih…</option>
                    <option value="dp">DP 50% &mdash; <?= format_rupiah($dp) ?></option>
                    <option value="lunas">Lunas &mdash; <?= format_rupiah($total) ?></option>
                  </select>
                </label>
                <label class="pay-form__row">
                  <span>Bukti</span>
                  <input type="file" name="bukti" accept="image/jpeg,image/png" required>
                </label>
                <button class="btn btn-outline btn-sm" type="submit"><?= $bs === 'ditolak' ? 'Kirim ulang bukti' : 'Kirim bukti' ?></button>
                <small class="pay-form__hint">DP kirim setengah dari tagihan (<?= format_rupiah($dp) ?>), lunas kirim semua (<?= format_rupiah($total) ?>). JPG/PNG, maks 5MB.</small>
                <?php if ($bs === 'ditolak'): ?><small class="pay-form__warn">Bukti sebelumnya ditolak — kirim ulang.</small><?php endif; ?>
              </form>
            <?php else: ?>
              <a href="booking_sMADAL.php?kode=<?= e($r['kode_booking']) ?>">Detail</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

