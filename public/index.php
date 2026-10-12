<?php
$pageTitle = null; // judul default dari header
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$user = $_SESSION['user'] ?? null;

// Angka nyata dari DB untuk baris mono di hero
$jmlKolam = (int)$pdo->query("SELECT COUNT(*) FROM kolam WHERE is_aktif = 1")->fetchColumn();
$stmtSlot = $pdo->query("SELECT COUNT(*) FROM slots s
    LEFT JOIN bookings b ON b.slot_id = s.id AND b.tanggal = s.tanggal AND b.status <> 'dibatalkan'
    WHERE s.tanggal = CURDATE() AND s.status = 'buka' AND b.id IS NULL");
$slotKosong = (int)$stmtSlot->fetchColumn();

// Preview 3 kolam untuk section CTA
$stmtPrev = $pdo->prepare("SELECT id, nama, alamat, harga_per_slot FROM kolam WHERE is_aktif = 1 ORDER BY nama LIMIT 3");
$stmtPrev->execute();
$preview = $stmtPrev->fetchAll();
?>
<section class="hero">
  <div class="container">
    <p class="mono hero__eyebrow">Booking kolam pemancingan</p>
    <h1>Cek slot. Datang. Mancing. Pulang.</h1>
    <p class="lead">Cek kolam mana yang masih kosong hari ini, pesan jamnya, lalu kabari pemicangnya lewat WhatsApp — tanpa chat yang menumpuk.</p>
    <div class="hero-actions">
      <?php if ($user): ?>
        <a class="btn btn-primary" href="semua-kolam.php">Lihat kolam terdekat</a>
        <a class="btn btn-outline btn-ghost" href="<?= $_user['role'] === 'pemilik' ? 'owner/dashboard.php' : 'riwayat.php' ?>"><?= $_user['role'] === 'pemilik' ? 'Buka dashboard' : 'Riwayat booking' ?></a>
      <?php else: ?>
        <a class="btn btn-primary" href="semua-kolam.php">Lihat kolam terdekat</a>
        <a class="btn btn-outline btn-ghost" href="register.php">Daftar akun</a>
      <?php endif; ?>
    </div>
    <div class="float-row" aria-hidden="true">
      <?php foreach (['06:00','07:00','08:00','09:00','10:00','11:00','12:00'] as $i => $jam): ?>
        <span class="float-slot <?= $i === 2 ? 'taken' : ($i === 5 ? 'closed' : '') ?>">
          <span class="bobber"></span><span class="jam"><?= $jam ?></span>
        </span>
      <?php endforeach; ?>
    </div>
    <p class="mono hero__meta"><?= $jmlKolam ?> kolam aktif · <?= $slotKosong ?> slot kosong hari ini</p>
  </div>
</section>

<section class="section container">
  <h2>Tiga langkah, dua menit</h2>
  <ol class="steps">
    <li class="step">
      <span class="step-num mono">01</span>
      <h3>Cari kolam &amp; cek ikan</h3>
      <p>Cari berdasarkan nama atau area, lalu saring ikan yang kamu mau — Mas, Nila, Patin, atau apa pun yang ada di kolam itu.</p>
    </li>
    <li class="step">
      <span class="step-num mono">02</span>
      <h3>Pilih jam kosong</h3>
      <p>Setiap jam punya pelampungnya sendiri. Yang mengambang berarti kosong; yang tenggelam sudah dipesan orang lain.</p>
    </li>
    <li class="step">
      <span class="step-num mono">03</span>
      <h3>Booking &amp; kirim bukti</h3>
      <p>Dapat kode booking, transfer, lalu unggah bukti. Pemilik kolam mengonfirmasi lewat WhatsApp.</p>
    </li>
  </ol>
</section>

<section class="section section--tight container">
  <h2>Yang bikin beda dari chat biasa</h2>
  <div class="feature-grid">
    <div class="feature-card">
      <span class="feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 15c2-1 4-1 6 0s4 1 6 0 4-1 4-1"/><path d="M4 19c2-1 4-1 6 0s4 1 6 0 4-1 4-1"/><path d="M12 3v9"/><circle cx="12" cy="4.5" r="1.5"/></svg>
      </span>
      <h3>Slot real-time</h3>
      <p>Ketersediaan per jam diambil langsung dari database, bukan catatan di chat yang sudah basi.</p>
    </div>
    <div class="feature-card">
      <span class="feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
      </span>
      <h3>Anti double booking</h3>
      <p>Slot terkunci begitu dibooking. Tidak ada lagi dua orang datang membawa kode yang sama.</p>
    </div>
    <div class="feature-card">
      <span class="feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h4"/></svg>
      </span>
      <h3>Bukti bayar tercatat</h3>
      <p>Foto transfer diunggah ke booking, lalu pemilik menerima atau menolak dengan satu klik.</p>
    </div>
    <div class="feature-card">
      <span class="feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 11.5a8.5 8.5 0 0 1-11.9 7.8L3 21l1.8-6.1A8.5 8.5 0 1 1 21 11.5z"/><path d="M8.5 10.5h7M8.5 14h4"/></svg>
      </span>
      <h3>Konfirmasi WhatsApp</h3>
      <p>Kode booking, kolam, jam, dan total langsung terkirim ke nomor pemilik lewat WhatsApp.</p>
    </div>
  </div>
</section>

<section class="cta-band">
  <div class="container">
    <h2>Mulai mancing hari ini</h2>
    <p>Buat akun sekali, lalu semua bookingmu tersimpan di satu tempat — lengkap dengan riwayat dan bukti pembayarannya.</p>
    <div class="hero-actions">
      <?php if ($user): ?>
        <a class="btn btn-primary" href="semua-kolam.php">Cari kolam sekarang</a>
      <?php else: ?>
        <a class="btn btn-primary" href="register.php">Daftar akun</a>
        <a class="btn btn-outline btn-ghost" href="semua-kolam.php">Lihat semua kolam</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($preview): ?>
<section class="section container">
  <h2>Kolam yang bisa kamu coba</h2>
  <ul class="preview-list">
    <?php foreach ($preview as $p): ?>
      <li class="preview-row">
        <span class="preview-name"><?= e($p['nama']) ?></span>
        <span class="preview-alamat"><?= e($p['alamat']) ?></span>
        <span class="preview-harga mono"><?= format_rupiah($p['harga_per_slot']) ?> / orang</span>
        <a class="preview-link" href="kolam.php?id=<?= $p['id'] ?>">Lihat slot →</a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
