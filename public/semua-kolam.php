<?php
$pageTitle = 'Semua kolam';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$ikanDicari = trim($_GET['ikan'] ?? '');

// Escape wildcard LIKE. Tanpa ini, mengetik "%" atau "_" di kotak pencarian
// diperlakukan sebagai wildcard dan bisa membuat semua kolam ikut terbawa.
$like = static function (string $teks): string {
    return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $teks) . '%';
};

$sql = "SELECT * FROM kolam WHERE is_aktif = 1";
$params = [];
if ($q !== '') { $sql .= " AND (nama LIKE ? OR alamat LIKE ?)"; $params[] = $like($q); $params[] = $like($q); }
if ($ikanDicari !== '') { $sql .= " AND jenis_ikan LIKE ?"; $params[] = $like($ikanDicari); }
$sql .= " ORDER BY nama";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Hitung slot kosong hari ini per kolam untuk status chip
$slotStmt = $pdo->prepare("SELECT COUNT(*) FROM slots s LEFT JOIN bookings b ON b.slot_id = s.id AND b.tanggal = s.tanggal AND b.status <> 'dibatalkan' WHERE s.kolam_id = ? AND s.tanggal = CURDATE() AND s.status = 'buka' AND b.id IS NULL");
$kolamList = [];
foreach ($rows as $r) {
    $slotStmt->execute([$r['id']]);
    $kosong = (int)$slotStmt->fetchColumn();
    $kolamList[] = [
        'id' => $r['id'], 'nama' => $r['nama'], 'alamat' => $r['alamat'],
        'harga' => $r['harga_per_slot'], 'ikan' => $r['jenis_ikan'] ?? '',
        'foto' => $r['foto'] ?? '',
        'status' => $kosong > 3 ? 'kosong' : ($kosong > 0 ? 'sedikit' : 'penuh'),
    ];
}

$rekomendasi = [];
$lain = $kolamList;
if ($ikanDicari !== '') {
    $rekomendasi = array_filter($kolamList, fn($k) => stripos($k['ikan'], $ikanDicari) !== false);
    $lain = array_diff_key($kolamList, $rekomendasi);
}
?>
<section class="page-head">
  <div class="container">
    <h1>Semua kolam</h1>
    <p class="lead">Cari kolam, cek ikan yang tersedia, lalu pilih jam kosongnya.</p>
    <form class="search-bar search-light" method="GET" action="semua-kolam.php">
      <input type="text" name="q" placeholder="Cari nama kolam atau area…" value="<?= e($_GET['q'] ?? '') ?>">
      <input type="text" name="ikan" placeholder="Cek ikan: Mas, Nila, Patin…" value="<?= e($ikanDicari) ?>">
      <input type="date" name="tanggal" value="<?= e($_GET['tanggal'] ?? date('Y-m-d')) ?>">
      <button class="btn btn-primary" type="submit">Cari kolam</button>
    </form>
    <p class="mono page-head__meta">
      <?= count($kolamList) ?> kolam ditemukan<?= $ikanDicari !== '' ? ' untuk ikan "' . e($ikanDicari) . '"' : '' ?>
    </p>
  </div>
</section>

<?php if ($ikanDicari !== ''): ?>
<section class="section container">
  <h2>Rekomendasi untuk ikan: <?= e($ikanDicari) ?></h2>
  <?php if (count($rekomendasi) > 0): ?>
  <div class="grid">
    <?php foreach ($rekomendasi as $k): ?>
      <article class="card">
        <?php $fotoSrc = $k['foto'] ? "/Booking_Pemancingan/public/uploads/" . basename($k['foto']) : "/Booking_Pemancingan/public/assets/img/kolam-" . $k['id'] . ".jpg"; ?>
        <img src="<?= e($fotoSrc) ?>" alt="<?= e($k['nama']) ?>" loading="lazy" onerror="this.style.display='none'">
        <div class="card-body">
          <h3><?= e($k['nama']) ?></h3>
          <p class="alamat"><?= e($k['alamat']) ?></p>
          <p>
            <?php foreach (array_filter(array_map('trim', explode(',', $k['ikan']))) as $ikan): ?>
              <span class="chip-ikan"><?= e($ikan) ?></span>
            <?php endforeach; ?>
          </p>
          <p class="harga"><?= format_rupiah($k['harga']) ?> / orang</p>
          <a class="btn btn-outline" href="kolam.php?id=<?= $k['id'] ?>">Lihat slot</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <p style="color:#5f726b">Belum ada kolam dengan ikan "<?= e($ikanDicari) ?>". Coba nama ikan lain.</p>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($ikanDicari === '' || count($lain) > 0): ?>
<section class="section container">
  <h2><?= $ikanDicari !== '' ? 'Kolam lainnya' : 'Kolam di sekitarmu' ?></h2>
  <?php if (count(($ikanDicari !== '' ? $lain : $kolamList)) === 0): ?>
    <p style="color:#5f726b">Tidak ada kolam yang cocok. <a href="semua-kolam.php">Hapus filter</a>.</p>
  <?php else: ?>
  <div class="grid">
    <?php foreach (($ikanDicari !== '' ? $lain : $kolamList) as $k): ?>
      <article class="card">
        <?php $fotoSrc = $k['foto'] ? "/Booking_Pemancingan/public/uploads/" . basename($k['foto']) : "/Booking_Pemancingan/public/assets/img/kolam-" . $k['id'] . ".jpg"; ?>
        <img src="<?= e($fotoSrc) ?>" alt="<?= e($k['nama']) ?>" loading="lazy" onerror="this.style.display='none'">
        <div class="card-body">
          <h3><?= e($k['nama']) ?></h3>
          <p class="alamat"><?= e($k['alamat']) ?></p>
          <p>
            <span class="status-dot" style="background:<?= $k['status'] === 'kosong' ? '#2F855A' : ($k['status'] === 'sedikit' ? '#B7791F' : '#D9401F') ?>"></span>
            <?= $k['status'] === 'kosong' ? 'Slot kosong' : ($k['status'] === 'sedikit' ? 'Sisa sedikit slot' : 'Penuh hari ini') ?>
          </p>
          <p>
            <?php foreach (array_filter(array_map('trim', explode(',', $k['ikan']))) as $ikan): ?>
              <span class="chip-ikan"><?= e($ikan) ?></span>
            <?php endforeach; ?>
          </p>
          <p class="harga"><?= format_rupiah($k['harga']) ?> / orang</p>
          <a class="btn btn-outline" href="kolam.php?id=<?= $k['id'] ?>">Lihat slot</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>