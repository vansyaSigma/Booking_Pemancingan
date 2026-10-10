<?php
$pageTitle = 'Kelola Jadwal';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

$ownerId = $_SESSION['user']['id'];
$kolamId = (int)($_GET['kolam'] ?? 0);

if ($kolamId === 0) {
    $stmt = $pdo->prepare('SELECT id, nama, jam_buka, jam_tutup FROM kolam WHERE owner_id = ? AND is_aktif = 1');
    $stmt->execute([$ownerId]);
    $kolamIds = $stmt->fetchAll();
    if ($kolamIds) { redirect('/Booking_Pemancingan/public/owner/jadwal.php?kolam=' . $kolamIds[0]['id']); }
    echo 'Belum ada kolam. <a href="kolam.php">Tambah kolam dulu</a>.'; require_once __DIR__ . '/../../includes/footer.php'; exit;
}

$stmt = $pdo->prepare('SELECT * FROM kolam WHERE id = ? AND owner_id = ?');
$stmt->execute([$kolamId, $ownerId]);
$kolam = $stmt->fetch();
if (!$kolam) { exit('Kolam tidak ditemukan.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'generate') {
    csrf_verify();
    // Pakai ensure_slot_horizon: memperpanjang dari slot terakhir yang sudah ada,
    // bukan selalu dari hari ini (dulu tidak akan pernah melewati today+13).
    $jumlah = ensure_slot_horizon($pdo, $kolamId, 21);
    $stmtLast = $pdo->prepare('SELECT MAX(tanggal) FROM slots WHERE kolam_id = ?');
    $stmtLast->execute([$kolamId]);
    redirect('/Booking_Pemancingan/public/owner/jadwal.php?kolam=' . $kolamId
        . '&ok=' . urlencode($jumlah > 0
            ? $jumlah . ' slot baru dibuat. Jadwal tersedia sampai ' . $stmtLast->fetchColumn() . '.'
            : 'Slot sudah cukup. Jadwal tetap tersedia sampai ' . $stmtLast->fetchColumn() . '.'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $dari = $_POST['dari'] ?? date('Y-m-d');
    $sampai = $_POST['sampai'] ?? $dari;

    // Validasi rentang tanggal: format Y-m-d dan maksimal 62 hari (cegah query berat)
    $fmt = 'Y-m-d';
    $dObj = DateTime::createFromFormat($fmt, $dari);
    $sObj = DateTime::createFromFormat($fmt, $sampai);
    if (!$dObj || !$sObj || $dObj->format($fmt) !== $dari || $sObj->format($fmt) !== $sampai) {
        redirect('/Booking_Pemancingan/public/owner/jadwal.php?kolam=' . $kolamId . '&err=' . urlencode('Format tanggal harus YYYY-MM-DD.'));
    }
    if ($sObj < $dObj) { [$dObj, $sObj] = [$sObj, $dObj]; [$dari, $sampai] = [$dObj->format($fmt), $sObj->format($fmt)]; }
    if (($sObj->diff($dObj)->days) > 62) {
        redirect('/Booking_Pemancingan/public/owner/jadwal.php?kolam=' . $kolamId . '&err=' . urlencode('Rentang maksimal 62 hari sekaligus.'));
    }
    // Batas bawah: jangan sampai jauh ke belakang (mencegah penulisan ulang riwayat)
    $batasBawah = date($fmt, strtotime('-30 day'));
    if ($dObj->format($fmt) < $batasBawah) {
        $dObj = new DateTime($batasBawah);
        $dari = $batasBawah;
        if ($sObj < $dObj) { [$dObj, $sObj] = [$sObj, $dObj]; [$dari, $sampai] = [$dObj->format($fmt), $sObj->format($fmt)]; }
    }

    $checked = $_POST['slot'] ?? [];
    $bukaSet = [];
    foreach ($checked as $v) {
        if (!is_string($v) || substr_count($v, '|') !== 1) { continue; }  // buang nilai tak valid
        [$tgl, $jam] = explode('|', $v);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) || !preg_match('/^\d{2}:\d{2}$/', $jam)) { continue; }
        $bukaSet[$tgl . ' ' . $jam . ':00'] = true;
    }
    $stmt = $pdo->prepare('SELECT id, tanggal, jam FROM slots WHERE kolam_id = ? AND tanggal BETWEEN ? AND ?');
    $stmt->execute([$kolamId, $dari, $sampai]);
    foreach ($stmt->fetchAll() as $s) {
        $key = $s['tanggal'] . ' ' . $s['jam'];
        if (isset($bukaSet[$key])) {
            $pdo->prepare("UPDATE slots SET status='buka' WHERE id=?")->execute([$s['id']]);
        } else {
            // hanya tutup jika tidak ada booking aktif
            $cek = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE slot_id = ? AND tanggal = ? AND status <> 'dibatalkan'");
            $cek->execute([$s['id'], $s['tanggal']]);
            if ((int)$cek->fetchColumn() === 0) {
                $pdo->prepare("UPDATE slots SET status='tutup' WHERE id=?")->execute([$s['id']]);
            }
        }
    }
    redirect('/Booking_Pemancingan/public/owner/jadwal.php?kolam=' . $kolamId);
}

// Tampilan (GET) — validasi sama supaya rentang freezer tidak membebani query
$fmt = 'Y-m-d';
$dari = $_GET['dari'] ?? date($fmt);
$sampai = $_GET['sampai'] ?? date($fmt, strtotime('+7 days'));
$dObj = DateTime::createFromFormat($fmt, $dari);
$sObj = DateTime::createFromFormat($fmt, $sampai);
if (!$dObj || !$sObj || $dObj->format($fmt) !== $dari || $sObj->format($fmt) !== $sampai) {
    [$dari, $sampai] = [date($fmt), date($fmt, strtotime('+7 days'))];
} elseif ($sObj < $dObj) {
    [$dObj, $sObj] = [$sObj, $dObj];
    [$dari, $sampai] = [$dObj->format($fmt), $sObj->format($fmt)];
} elseif ($sObj->diff($dObj)->days > 62) {
    $sampai = $dObj->modify('+62 days')->format($fmt);
}
// Slot pada rentang terpilih (dibuat sebelum template agar bisa dipakai untuk pesan)
$stmtSlot = $pdo->prepare('SELECT tanggal, jam, status FROM slots WHERE kolam_id = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal, jam');
$stmtSlot->execute([$kolamId, $dari, $sampai]);
$rows = $stmtSlot->fetchAll();
$byDate = [];
foreach ($rows as $r) { $byDate[$r['tanggal']][] = $r; }

// Info horizon: kapan jadwal kolam ini habis
$stmtHorizon = $pdo->prepare('SELECT COUNT(*) AS jml, MAX(tanggal) AS terakhir FROM slots WHERE kolam_id = ?');
$stmtHorizon->execute([$kolamId]);
$infoHorizon = $stmtHorizon->fetch();
$totalSlotKolam = (int)$infoHorizon['jml'];
$slotTerakhir = $infoHorizon['terakhir'];
$hariTersisa = $slotTerakhir
    ? (int)date_diff(new DateTime(date('Y-m-d')), new DateTime($slotTerakhir))->format('%a%r%s')
    : 0;
$labelGenerate = ($totalSlotKolam === 0) ? 'Buat jadwal 21 hari' : 'Perpanjang jadwal 21 hari';
?>
<div class="owner-shell">
  <aside class="owner-side">
    <strong style="color:#fff;display:block;margin-bottom:1rem">KolamKita · Pemilik</strong>
    <a href="dashboard.php">Dashboard</a>
    <a href="kolam.php">Kolam</a>
    <a href="jadwal.php" class="active">Jadwal</a>
    <a href="pembayaran.php">Pembayaran</a>
  </aside>
  <main class="owner-main">
    <h1>Manajemen slot jadwal — <?= e($kolam['nama']) ?></h1>
    <?php if ($msgOk = $_GET['ok'] ?? ''): ?><p class="badge badge-hijau" style="display:block;margin:1rem 0"><?= e($msgOk) ?></p><?php endif; ?>
    <?php if ($msgErr = $_GET['err'] ?? ''): ?><p class="badge badge-merah" style="display:block;margin:1rem 0"><?= e($msgErr) ?></p><?php endif; ?>
    <div class="jadwal-horizon">
      <p class="hint">
        <?php if ($totalSlotKolam === 0): ?>
          Kolam ini belum punya slot, jadi belum bisa dibooking.
        <?php elseif ($hariTersisa <= 7): ?>
          <strong>Jadwal hampir habis</strong> — hanya tersisa <?= $hariTersisa ?> hari lagi (sampai <?= e($slotTerakhir) ?>).
        <?php else: ?>
          Jadwal tersedia sampai <strong><?= e($slotTerakhir) ?></strong> (<?= $hariTersisa ?> hari lagi).
        <?php endif; ?>
        Jam dibuat otomatis dari jam buka/tutup kolam, dan diperpanjang otomatis saat pemancing membuka halaman kolam.
      </p>
      <form method="POST" style="margin-top:.75rem">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="generate">
        <button class="btn btn-primary" type="submit"><?= e($labelGenerate) ?></button>
      </form>
    </div>
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;margin:1.5rem 0">
      <input type="hidden" name="kolam" value="<?= $kolamId ?>">
      <input type="date" name="dari" value="<?= e($dari) ?>">
      <input type="date" name="sampai" value="<?= e($sampai) ?>">
      <button class="btn btn-outline" type="submit">Tampilkan</button>
    </form>

    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="dari" value="<?= e($dari) ?>">
      <input type="hidden" name="sampai" value="<?= e($sampai) ?>">
      <?php foreach ($byDate as $tgl => $slots): ?>
        <h3 style="margin-top:1.25rem"><?= e($tgl) ?></h3>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
          <?php foreach ($slots as $s): ?>
            <label class="slot-check">
              <input type="checkbox" name="slot[]" value="<?= e($tgl) ?>|<?= e(substr($s['jam'], 0, 5)) ?>" <?= $s['status'] === 'buka' ? 'checked' : '' ?>>
              <?= e(substr($s['jam'], 0, 5)) ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <button class="btn btn-primary" type="submit" style="margin-top:1.5rem">Simpan jadwal</button>
    </form>
    <p class="hint" style="margin-top:1rem">Centang jam yang dibuka. Slot yang sudah punya booking aktif tidak bisa ditutup — batalkan bookingnya dulu.</p>
  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
