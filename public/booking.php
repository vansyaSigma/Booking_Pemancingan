<?php
$pageTitle = 'Booking';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

// Booking wajib punya akun. Simpan tujuan supaya setelah login user lanjut ke form yang sama.
if (!($_SESSION['user'] ?? null)) {
    $next = $_SERVER['REQUEST_URI'] ?? '/Booking_Pemancingan/public/index.php';
    redirect('/Booking_Pemancingan/public/login.php?err=' . urlencode('Masuk dulu untuk lanjut booking.') . '&next=' . urlencode($next));
}

$kolamId = (int)($_GET['kolam'] ?? 0);
$slot = $_GET['slot'] ?? '';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$stmt = $pdo->prepare('SELECT nama, harga_per_slot FROM kolam WHERE id = ? AND is_aktif = 1');
$stmt->execute([$kolamId]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); exit('Kolam tidak ditemukan.'); }
$harga = $row['harga_per_slot'];
$namaKolam = $row['nama'];
$stmtWa = $pdo->prepare('SELECT no_wa FROM users WHERE id = ?');
$stmtWa->execute([$_SESSION['user']['id']]);
$waTersimpan = (string)$stmtWa->fetchColumn();
if (!$slot) { exit('Slot belum dipilih. <a href="semua-kolam.php">Kembali</a>'); }
?>
<div class="form-card" style="max-width:36rem">
  <h1>Form booking</h1>
  <p style="color:#5f726b">Masuk sebagai <strong><?= e($_SESSION['user']['nama']) ?></strong>. Data pemancing diambil dari akun.</p>
  <div class="ticket" style="margin-bottom:1.5rem">
    <p><strong>Kolam:</strong> <?= e($namaKolam) ?></p>
    <p><strong>Tanggal:</strong> <?= e($tanggal) ?></p>
    <p><strong>Jam:</strong> <span class="mono"><?= e($slot) ?></span></p>
    <p><strong>Harga/orang:</strong> <span class="mono"><?= format_rupiah($harga) ?></span></p>
  </div>
  <form method="POST" action="api/booking.php" onsubmit="return this.reportValidity()">
    <?= csrf_field() ?>
    <input type="hidden" id="harga" value="<?= $harga ?>">
    <input type="hidden" name="kolam" value="<?= $kolamId ?>">
    <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
    <input type="hidden" name="slot" value="<?= e($slot) ?>">
    <label for="nama">Nama pemancing</label>
    <input type="text" id="nama" name="nama" value="<?= e($_SESSION['user']['nama']) ?>" readonly>
    <label for="no_wa">Nomor WhatsApp</label>
    <input type="text" id="no_wa" name="no_wa" value="<?= e($waTersimpan) ?>" readonly>
    <p class="hint">Nama dan WhatsApp mengikuti akunmu.</p>
    <label for="jumlah_orang">Jumlah orang</label>
    <input type="number" id="jumlah_orang" name="jumlah_orang" min="1" max="20" value="1" required>
    <p class="hint">Maksimal 20 orang per booking.</p>
    <label for="catatan">Catatan (opsional)</label>
    <textarea id="catatan" name="catatan" rows="3"></textarea>
    <p style="font-size:1.1rem"><strong>Total:</strong> <span id="total-harga" class="mono"><?= format_rupiah($harga) ?></span></p>
    <button class="btn btn-primary btn-block" type="submit">Booking sekarang</button>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
