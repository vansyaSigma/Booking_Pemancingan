<?php
$pageTitle = 'Masuk';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
// Tujuan setelah login (mis. kembali ke form booking). Hanya path internal yang diizinkan.
$next = $_GET['next'] ?? ($_POST['next'] ?? '');
if (!is_string($next) || strpos($next, '/Booking_Pemancingan/public/') !== 0 || strpos($next, '//') === 0) {
    $next = '';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
          session_regenerate_id(true);
          // Ganti token CSRF: token yang dibuat saat masih anonim tidak boleh tetap
          // berlaku setelah user naik privilege menjadi pemilik.
          csrf_rotate();
          $_SESSION['user'] = ['id' => $user['id'], 'nama' => $user['nama'], 'email' => $user['email'], 'role' => $user['role']];
        if ($next !== '' && !str_contains($next, '/owner/')) {
            redirect($next);
        }
        redirect($user['role'] === 'pemilik'
            ? '/Booking_Pemancingan/public/owner/dashboard.php'
            : '/Booking_Pemancingan/public/semua-kolam.php');
    }

    $error = 'Email atau password salah.';
}
?>
<div class="form-card">
  <h1>Masuk</h1>
  <p style="color:#5f726b;margin-bottom:1.5rem">Belum punya akun? <a href="register.php">Daftar di sini</a>.</p>
  <?php if ($error): ?>
    <p class="badge badge-merah" style="display:block;margin-bottom:1rem"><?= e($error) ?></p>
  <?php endif; ?>
  <?php if ($err = $_GET['err'] ?? ''): ?>
    <p class="badge badge-merah" style="display:block;margin-bottom:1rem"><?= e($err) ?></p>
  <?php endif; ?>
  <form method="POST" action="login.php">
    <?= csrf_field() ?>
    <?php if ($next !== ''): ?><input type="hidden" name="next" value="<?= e($next) ?>"><?php endif; ?>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <button class="btn btn-primary btn-block" type="submit">Masuk</button>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
