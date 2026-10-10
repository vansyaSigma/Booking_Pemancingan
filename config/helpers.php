<?php
// config/helpers.php — Fungsi umum

// Timezone aplikasi. WAJIB sama dengan zona waktu MySQL, kalau tidak date('Y-m-d')
// dan CURDATE() bisa berbeda sehari (XAMPP default Europe/Berlin, DB Asia/Jakarta).
date_default_timezone_set('Asia/Jakarta');

function e($str): string
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

function format_rupiah($angka): string
{
    return 'Rp' . number_format((float) $angka, 0, ',', '.');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function generate_kode_booking(): string
{
    return 'BK-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid (CSRF). Muat ulang halaman lalu coba lagi.');
    }
}

/**
 * Ganti token CSRF dengan yang baru. Dipanggil saat privilege berubah (login/logout)
 * supaya token anonim tidak lagi berlaku setelah user naik menjadi pemilik.
 */
function csrf_rotate(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Validasi format jam. Menerima "HH:MM" maupun "HH:MM:SS" — kolom TIME di MySQL
 * selalu dikembalikan sebagai "HH:MM:SS", sedangkan input form memakai "HH:MM".
 */
function is_valid_jam(string $jam): bool
{
    return (bool) preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:\d{2})?$/', trim($jam));
}

/** Hitung menit dari "HH:MM" atau "HH:MM:SS", atau null bila formatnya tidak valid. */
function jam_menit(?string $jam): ?int
{
    if ($jam === null || !is_valid_jam($jam)) {
        return null;
    }
    [$h, $m] = explode(':', trim($jam));
    return ((int) $h) * 60 + (int) $m;
}

/**
 * Generate slot jam untuk sebuah kolam, mulai $dari selama $days hari,
 * dari jam_buka sampai jam_tutup (jam_tutup tidak ikut dibuat sebagai slot).
 *
 * $dari dipakai untuk MEMPERPANJANG ke masa depan. Kalau selalu mulai dari
 * "hari ini", horizon tidak akan pernah melewati today+$days-1.
 *
 * Idempotent — aman dipanggil berulang karena memakai INSERT IGNORE
 * (ada UNIQUE KEY uniq_slot pada kolam_id + tanggal + jam).
 *
 * @return int jumlah slot yang benar-benar baru dibuat
 */
function generate_slot(PDO $pdo, int $kolamId, string $jamBuka, string $jamTutup, int $days = 14, ?string $dari = null): int
{
    $menitBuka = jam_menit($jamBuka);
    $menitTutup = jam_menit($jamTutup);
    // strtotime() terlalu longgar: "12:00x" dan "25:00" bisa jadi timestamp nyeleneh.
    if ($menitBuka === null || $menitTutup === null || $menitTutup <= $menitBuka || $days < 1) {
        return 0;
    }

    $mulai = ($dari !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) ? $dari : date('Y-m-d');
    $tglMulai = strtotime($mulai . ' 00:00:00');
    if ($tglMulai === false) {
        return 0;
    }

    $stmt = $pdo->prepare('INSERT IGNORE INTO slots (kolam_id, tanggal, jam) VALUES (?, ?, ?)');
    $jumlah = 0;
    for ($d = 0; $d < $days; $d++) {
        $tanggal = date('Y-m-d', strtotime("+$d day", $tglMulai));
        for ($m = $menitBuka; $m < $menitTutup; $m += 60) {
            $stmt->execute([$kolamId, $tanggal, sprintf('%02d:%02d:00', intdiv($m, 60), $m % 60)]);
            $jumlah += $stmt->rowCount(); // hanya slot yang benar-benar baru, bukan duplikat
        }
    }
    return $jumlah;
}

/**
 * Pastikan horizon slot kolam minimal $days hari ke depan.
 *
 * Dipanggil secara lazy dari endpoint ketersediaan: kalau slot sudah hampir habis,
 * slot setelah tanggal terakhir yang sudah ada yang dibuat. Returns jumlah slot baru.
 */
function ensure_slot_horizon(PDO $pdo, int $kolamId, int $days = 14): int
{
    $stmt = $pdo->prepare('SELECT MAX(tanggal) FROM slots WHERE kolam_id = ?');
    $stmt->execute([$kolamId]);
    $terakhir = $stmt->fetchColumn();
    $target = date('Y-m-d', strtotime('+' . ($days - 1) . ' day'));

    if ($terakhir && $terakhir >= $target) {
        return 0; // horizon masih aman, tidak perlu menulis apa pun
    }

    // Lanjut dari besoknya slot terakhir, atau hari ini kalau kolam belum punya slot
    $mulai = $terakhir ? date('Y-m-d', strtotime($terakhir . ' +1 day')) : date('Y-m-d');
    if ($mulai < date('Y-m-d')) {
        $mulai = date('Y-m-d');
    }

    $stmtKolam = $pdo->prepare('SELECT jam_buka, jam_tutup FROM kolam WHERE id = ?');
    $stmtKolam->execute([$kolamId]);
    $kolam = $stmtKolam->fetch();
    if (!$kolam) {
        return 0;
    }
    return generate_slot($pdo, $kolamId, $kolam['jam_buka'], $kolam['jam_tutup'], $days, $mulai);
}
