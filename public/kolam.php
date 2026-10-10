<?php
$pageTitle = 'Detail Kolam';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM kolam WHERE id = ? AND is_aktif = 1');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); exit('Kolam tidak ditemukan.'); }
$kolam = [
    'nama' => $row['nama'], 'alamat' => $row['alamat'], 'harga' => $row['harga_per_slot'],
    'deskripsi' => $row['deskripsi'] ?? '', 'ikan' => $row['jenis_ikan'] ?? '',
];
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
?>
<section class="section container">
  <div class="grid detail-grid">
    <div>
      <img src="/Booking_Pemancingan/public/assets/img/kolam-<?= $id ?>.jpg" alt="<?= e($kolam['nama']) ?>" style="border-radius:var(--radius); height:280px; object-fit:cover; width:100%" onerror="this.style.display='none'">
      <h1 style="margin-top:1rem"><?= e($kolam['nama']) ?></h1>
      <p class="alamat"><?= e($kolam['alamat']) ?></p>
      <p><?= e($kolam['deskripsi']) ?></p>
      <p>
        <strong>Ikan:</strong>
        <?php foreach (array_filter(array_map('trim', explode(',', $kolam['ikan']))) as $ikan): ?>
          <span class="chip-ikan"><?= e($ikan) ?></span>
        <?php endforeach; ?>
      </p>
      <p class="harga mono"><?= format_rupiah($kolam['harga']) ?> / orang</p>

      <h2 style="margin-top:2rem">Pilih tanggal & jam</h2>
      <input type="date" id="tanggal" value="<?= e($tanggal) ?>">
      <input type="hidden" id="kolam-id" value="<?= $id ?>">
      <div id="slot-grid"></div>
      <div class="float-legend" style="margin-top:.5rem">
        <span><span class="dot" style="background:#fff;border:1px solid #ccc"></span>Kosong</span>
        <span><span class="dot" style="background:#8A9994"></span>Sudah dipesan</span>
        <span><span class="dot" style="background:transparent;border:1px dashed #ccc"></span>Ditutup</span>
      </div>
    </div>
    <aside>
      <div class="card" style="padding:1.25rem; position:sticky; top:1rem">
        <h3>Ringkasan booking</h3>
        <p><strong>Kolam:</strong> <?= e($kolam['nama']) ?></p>
        <p><strong>Tanggal:</strong> <?= e($tanggal) ?></p>
        <p><strong>Jam:</strong> <span id="slot-label">Pilih jam dulu</span></p>
        <p><strong>Harga/orang:</strong> <span class="mono"><?= format_rupiah($kolam['harga']) ?></span></p>
        <input type="hidden" id="slot-terpilih" value="">
        <input type="hidden" id="harga" value="<?= $kolam['harga'] ?>">
        <a class="btn btn-primary btn-block" id="btn-booking" href="#" onclick="return mulaiBooking(event)">Booking sekarang</a>
      </div>
    </aside>
  </div>
</section>
<script>
function mulaiBooking(e){
  const slot = document.getElementById('slot-terpilih').value;
  if (!slot) { alert('Pilih jam yang kosong dulu.'); e.preventDefault(); return false; }
  window.location = `booking.php?kolam=<?= $id ?>&slot=${encodeURIComponent(slot)}&tanggal=${document.getElementById('tanggal').value}`;
  return false;
}
document.addEventListener('DOMContentLoaded', () => {
  renderSlotGrid(<?= $id ?>, '<?= e($tanggal) ?>', 'slot-grid');
  // highlight selected slot label
  const obs = new MutationObserver(() => {
    const sel = document.querySelector('#slot-grid .float-slot.selected .jam');
    document.getElementById('slot-label').textContent = sel ? sel.textContent : 'Pilih jam dulu';
  });
  obs.observe(document.getElementById('slot-grid'), {childList:true, subtree:true, attributes:true});
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
