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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $dari = $_POST['dari'] ?? date('Y-m-d');
    $sampai = $_POST['sampai'] ?? $dari;
    $checked = $_POST['slot'] ?? [];
    $bukaSet = [];
    foreach ($checked as $v) {
        [$tgl, $jam] = explode('|', $v);
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

$dari = $_GET['dari'] ?? date('Y-m-d');
$sampai = $_GET['sampai'] ?? date('Y-m-d', strtotime('+7 days'));
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
      <?php
      $stmt = $pdo->prepare('SELECT tanggal, jam, status FROM slots WHERE kolam_id = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal, jam');
      $stmt->execute([$kolamId, $dari, $sampai]);
      $rows = $stmt->fetchAll();
      $byDate = [];
      foreach ($rows as $r) { $byDate[$r['tanggal']][] = $r; }
      ?>
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
