<?php
$pageTitle = 'Dashboard Pemilik';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

$ownerId = $_SESSION['user']['id'];
$filterTgl = $_GET['tanggal'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$pendapatan = $pdo->prepare("SELECT COALESCE(SUM(p.nominal),0) FROM payments p JOIN bookings b ON b.id = p.booking_id JOIN kolam k ON k.id = b.kolam_id WHERE k.owner_id = ? AND p.status = 'diterima' AND p.paid_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$pendapatan->execute([$ownerId]);
$totalMinggu = $pendapatan->fetchColumn();

$stmtJml = $pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN kolam k ON k.id = b.kolam_id WHERE k.owner_id = ?"); $stmtJml->execute([$ownerId]); $jmlBooking = $stmtJml->fetchColumn();
$stmtBatal = $pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN kolam k ON k.id = b.kolam_id WHERE k.owner_id = ? AND b.status = 'dibatalkan'"); $stmtBatal->execute([$ownerId]); $jmlBatal = $stmtBatal->fetchColumn();

$sql = 'SELECT b.kode_booking, b.tanggal, b.total_harga, b.status, u.nama, u.no_wa, k.nama AS nama_kolam, s.jam FROM bookings b JOIN kolam k ON k.id = b.kolam_id JOIN users u ON u.id = b.user_id JOIN slots s ON s.id = b.slot_id WHERE k.owner_id = ?';
$params = [$ownerId];
if ($filterTgl) { $sql .= ' AND b.tanggal = ?'; $params[] = $filterTgl; }
if ($filterStatus) { $sql .= ' AND b.status = ?'; $params[] = $filterStatus; }
$sql .= ' ORDER BY b.tanggal DESC, s.jam DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $bookingRows = $stmt->fetchAll();
?>
<div class="owner-shell">
  <aside class="owner-side">
    <strong style="color:#fff;display:block;margin-bottom:1rem">KolamKita · Pemilik</strong>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="kolam.php">Kolam</a>
    <a href="jadwal.php">Jadwal</a>
    <a href="pembayaran.php">Pembayaran</a>
  </aside>
  <main class="owner-main">
    <h1>Dashboard</h1>
    <div class="grid stat-grid" style="margin:1.5rem 0">
      <div class="stat-card"><div class="angka"><?= format_rupiah($totalMinggu) ?></div><div class="label">Pendapatan minggu ini</div></div>
      <div class="stat-card"><div class="angka"><?= (int)$jmlBooking ?></div><div class="label">Booking masuk</div></div>
      <div class="stat-card"><div class="angka"><?= (int)$jmlBatal ?></div><div class="label">Booking batal</div></div>
    </div>
    <h2>Booking masuk</h2>
    <form method="GET" class="search-bar search-light">
      <input type="date" name="tanggal" value="<?= e($filterTgl) ?>">
      <select name="status">
        <option value="">Semua status</option>
        <option value="menunggu_pembayaran" <?= $filterStatus==='menunggu_pembayaran'?'selected':'' ?>>menunggu pembayaran</option>
        <option value="dikonfirmasi" <?= $filterStatus==='dikonfirmasi'?'selected':'' ?>>dikonfirmasi</option>
        <option value="lunas" <?= $filterStatus==='lunas'?'selected':'' ?>>lunas</option>
        <option value="selesai" <?= $filterStatus==='selesai'?'selected':'' ?>>selesai</option>
        <option value="dibatalkan" <?= $filterStatus==='dibatalkan'?'selected':'' ?>>dibatalkan</option>
      </select>
      <button class="btn btn-outline" type="submit">Filter</button>
    </form>
    <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Pemancing</th><th>Kolam</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($bookingRows as $r): ?>
        <tr>
          <td class="mono"><?= e($r['kode_booking']) ?></td>
          <td><?= e($r['nama']) ?><br><small class="mono"><?= e($r['no_wa']) ?></small></td>
          <td><?= e($r['nama_kolam']) ?></td>
          <td><?= e($r['tanggal']) ?></td>
          <td class="mono"><?= e(substr($r['jam'], 0, 5)) ?></td>
          <td class="mono"><?= format_rupiah($r['total_harga']) ?></td>
          <td><span class="badge <?= $r['status']==='selesai'||$r['status']==='lunas' ? 'badge-hijau' : ($r['status']==='dibatalkan' ? 'badge-merah' : ($r['status']==='dikonfirmasi' ? 'badge-biru' : 'badge-kuning')) ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$bookingRows): ?><tr><td colspan="7" style="text-align:center;color:#5f726b">Belum ada booking.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
