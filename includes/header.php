<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
require_once __DIR__ . '/../config/helpers.php';
$_user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' — KolamKita' : 'KolamKita — Booking Kolam Pemancingan' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap">
  <?php // cache-busting:?v= disetiap file berubah => browser selalu ambil versi terbaru
  $cssVer = @filemtime(__DIR__ . '/../public/assets/css/style.css') ?: '1'; ?>
  <link rel="stylesheet" href="/Booking_Pemancingan/public/assets/css/style.css?v=<?= $cssVer ?>">
</head>
<body>
<header class="site-header">
  <div class="container">
    <a class="brand" href="/Booking_Pemancingan/public/index.php">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="10" r="7" fill="#fff"/><path d="M12 3v-3" stroke="#D9401F" stroke-width="2"/></svg>
      KolamKita
    </a>
    <button class="nav-toggle" onclick="document.querySelector('.nav').classList.toggle('open')">Menu</button>
    <nav class="nav">
      <a href="/Booking_Pemancingan/public/semua-kolam.php">Kolam</a>
      <?php if ($_user && $_user['role'] === 'pemancing'): ?>
        <a href="/Booking_Pemancingan/public/riwayat.php">Riwayat</a>
      <?php elseif ($_user && $_user['role'] === 'pemilik'): ?>
        <a href="/Booking_Pemancingan/public/owner/dashboard.php">Dashboard</a>
      <?php endif; ?>
      <?php if ($_user): ?>
        <a href="/Booking_Pemancingan/public/logout.php">Keluar (<?= e($_user['nama']) ?>)</a>
      <?php else: ?>
        <a href="/Booking_Pemancingan/public/login.php">Masuk</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
