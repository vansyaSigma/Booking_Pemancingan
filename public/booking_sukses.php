<?php
$pageTitle = 'Booking Berhasil';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../includes/header.php';
$kode = $_GET['kode'] ?? 'BK-DUMMY-0000';
$kolam = $_GET['kolam'] ?? 'Kolam Surya Gemilang';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$jam = $_GET['jam'] ?? '08:00';
$total = $_GET['total'] ?? 0;
?>
<div class="form-card" style="max-width:36rem">
  <h1>Booking berhasil!</h1>
  <div class="ticket" style="margin:1rem 0 1.5rem">
    <p><strong>Kode booking:</strong> <span class="kode"><?= e($kode) ?></span></p>
    <p><strong>Kolam:</strong> <?= e($kolam) ?></p>
    <p><strong>Tanggal:</strong> <?= e($tanggal) ?></p>
    <p><strong>Jam:</strong> <span class="mono"><?= e($jam) ?></span></p>
    <p><strong>Total:</strong> <span class="mono"><?= format_rupiah($total) ?></span></p>
  </div>
  <p style="margin-bottom:1rem">Simpan kode booking ini, lalu kabari pemilik supaya slotmu dikonfirmasi.</p>
  <a class="btn btn-primary btn-block" href="wa_notif.php?kode=<?= e($kode) ?>" target="_blank">Notifikasi pemilik lewat WhatsApp</a>
  <p style="margin-top:1.5rem"><a href="semua-kolam.php">&larr; Cari kolam lain</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
