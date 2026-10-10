<?php
$pageTitle = 'Daftar';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_wa = trim($_POST['no_wa'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || $email === '' || $no_wa === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password_hash, no_wa, role) VALUES (?, ?, ?, ?, 'pemancing')");
            $stmt->execute([$nama, $email, $hash, $no_wa]);
            redirect('/Booking_Pemancingan/public/login.php?err=' . urlencode('Pendaftaran berhasil, silakan masuk.'));
        }
    }
}
?>
<div class="form-card">
  <h1>Daftar akun</h1>
  <p style="color:#5f726b;margin-bottom:1.5rem">Sudah punya akun? <a href="login.php">Masuk di sini</a>.</p>
  <?php if ($error): ?><p class="badge badge-merah" style="display:block;margin-bottom:1rem"><?= e($error) ?></p><?php endif; ?>
  <form method="POST" action="register.php" onsubmit="return cekPassword(this)">
    <?= csrf_field() ?>
    <label for="nama">Nama lengkap</label>
    <input type="text" id="nama" name="nama" required>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>
    <label for="no_wa">Nomor WhatsApp</label>
    <input type="text" id="no_wa" name="no_wa" placeholder="08xxxx" required>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" minlength="8" required>
    <p class="hint">Minimal 8 karakter.</p>
    <label for="konfirmasi">Konfirmasi password</label>
    <input type="password" id="konfirmasi" name="konfirmasi" required>
    <button class="btn btn-primary btn-block" type="submit">Daftar</button>
  </form>
</div>
<script>
function cekPassword(f){
  if (f.password.value !== f.konfirmasi.value) { alert('Konfirmasi password tidak sama.'); return false; }
  return true;
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
