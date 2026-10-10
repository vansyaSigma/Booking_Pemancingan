<footer>
  <div class="container">KolamKita — Booking kolam pemancingan tanpa ribet &copy; <?= date('Y') ?></div>
</footer>
<?php $jsVer = @filemtime(__DIR__ . '/../public/assets/js/main.js') ?: '1'; ?>
<script src="/Booking_Pemancingan/public/assets/js/main.js?v=<?= $jsVer ?>"></script>
</body>
</html>
