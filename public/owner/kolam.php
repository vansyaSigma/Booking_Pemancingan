<?php
$pageTitle = 'Kelola Kolam';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

$ownerId = $_SESSION['user']['id'];
$pesan = '';

// AKSI: nonaktifkan (soft delete) kolam — lewat POST + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE kolam SET is_aktif = 0 WHERE id = ? AND owner_id = ?')->execute([$id, $ownerId]);
    redirect('/Booking_Pemancingan/public/owner/kolam.php');
}

// AKSI: simpan (tambah/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $jam_buka = $_POST['jam_buka'] ?? '06:00';
    $jam_tutup = $_POST['jam_tutup'] ?? '17:00';
    $jenis_ikan = trim($_POST['jenis_ikan'] ?? '');
    $jenis_ikan = str_replace(["\r", "\n"], ',', $jenis_ikan);
    $jenis_ikan = preg_replace('/\s*,\s*/', ', ', $jenis_ikan);   // normalisasi spasi di sekitar koma
    $jenis_ikan = preg_replace('/(,\s*)+/', ', ', $jenis_ikan);   // gabungkan koma beruntun
    $jenis_ikan = trim($jenis_ikan, ', ');
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if ($nama === '' || $harga <= 0 || $jenis_ikan === '') {
        $pesan = 'Nama, harga (>0), dan jenis ikan wajib diisi.';
    } else {
        $foto = null;
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['foto']['tmp_name']);
            if (in_array($mime, ['image/jpeg', 'image/png'], true) && $_FILES['foto']['size'] <= 5 * 1024 * 1024) {
                $ext = $mime === 'image/png' ? 'png' : 'jpg';
                $foto = 'kolam_' . $ownerId . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../uploads/' . $foto);
            }
        }
        if (!empty($_POST['edit_id'])) {
            $id = (int)$_POST['edit_id'];
            $sql = 'UPDATE kolam SET nama=?, deskripsi=?, alamat=?, harga_per_slot=?, jam_buka=?, jam_tutup=?, jenis_ikan=?, is_aktif=?';
            $params = [$nama, $deskripsi, $alamat, $harga, $jam_buka, $jam_tutup, $jenis_ikan, $aktif];
            if ($foto) { $sql .= ', foto=?'; $params[] = $foto; }
            $sql .= ' WHERE id=? AND owner_id=?';
            $params[] = $id; $params[] = $ownerId;
            $pdo->prepare($sql)->execute($params);
        } else {
            $pdo->prepare('INSERT INTO kolam (owner_id, nama, deskripsi, alamat, harga_per_slot, jam_buka, jam_tutup, jenis_ikan, foto, is_aktif) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$ownerId, $nama, $deskripsi, $alamat, $harga, $jam_buka, $jam_tutup, $jenis_ikan, $foto, $aktif]);
        }
        redirect('/Booking_Pemancingan/public/owner/kolam.php');
    }
}

$stmt = $pdo->prepare('SELECT * FROM kolam WHERE owner_id = ? ORDER BY id DESC');
$stmt->execute([$ownerId]);
$kolamList = $stmt->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM kolam WHERE id = ? AND owner_id = ?');
    $stmt->execute([(int)$_GET['edit'], $ownerId]);
    $edit = $stmt->fetch();
}
?>
<div class="owner-shell">
  <aside class="owner-side">
    <strong style="color:#fff;display:block;margin-bottom:1rem">KolamKita · Pemilik</strong>
    <a href="dashboard.php">Dashboard</a>
    <a href="kolam.php" class="active">Kolam</a>
    <a href="jadwal.php">Jadwal</a>
    <a href="pembayaran.php">Pembayaran</a>
  </aside>
  <main class="owner-main">
    <h1>Kolam saya</h1>
    <?php if ($pesan): ?><p class="badge badge-merah" style="display:block;margin-bottom:1rem"><?= e($pesan) ?></p><?php endif; ?>
    <div class="table-wrap">
    <table>
      <thead><tr><th>Nama</th><th>Harga</th><th>Jam buka</th><th>Jam tutup</th><th>Ikan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($kolamList as $k): ?>
        <tr>
          <td><?= e($k['nama']) ?></td>
          <td class="mono"><?= format_rupiah($k['harga_per_slot']) ?></td>
          <td class="mono"><?= e(substr($k['jam_buka'], 0, 5)) ?></td>
          <td class="mono"><?= e(substr($k['jam_tutup'], 0, 5)) ?></td>
          <td><?php foreach (explode(', ', $k['jenis_ikan'] ?? '') as $i): if (trim($i) !== ''): ?><span class="chip-ikan"><?= e(trim($i)) ?></span><?php endif; endforeach; ?></td>
          <td><span class="badge <?= $k['is_aktif'] ? 'badge-hijau' : 'badge-merah' ?>"><?= $k['is_aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td>
            <a href="?edit=<?= $k['id'] ?>">Edit</a> ·
            <form method="POST" style="display:inline" onsubmit="return confirm('Nonaktifkan kolam ini?')">
              <?= csrf_field() ?>
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= $k['id'] ?>">
              <button type="submit" style="background:none;border:none;color:#D9401F;cursor:pointer;font:inherit;padding:0">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>

    <h2 style="margin-top:2rem"><?= $edit ? 'Edit kolam' : 'Tambah kolam baru' ?></h2>
    <form method="POST" enctype="multipart/form-data" onsubmit="return cekHarga(this)" style="max-width:36rem">
      <?= csrf_field() ?>
      <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>
      <label>Nama kolam</label><input type="text" name="nama" value="<?= e($edit['nama'] ?? '') ?>" required>
      <label>Deskripsi</label><textarea name="deskripsi" rows="3"><?= e($edit['deskripsi'] ?? '') ?></textarea>
      <label>Alamat</label><input type="text" name="alamat" value="<?= e($edit['alamat'] ?? '') ?>">
      <label>Harga per orang (Rp)</label><input type="number" name="harga" min="1" value="<?= e($edit['harga_per_slot'] ?? '') ?>" required>
      <label>Jenis ikan</label><input type="text" name="jenis_ikan" placeholder="Mas, Nila, Patin (pisahkan koma)" value="<?= e($edit['jenis_ikan'] ?? '') ?>" required>
      <label>Jam buka</label><input type="time" name="jam_buka" value="<?= e(substr($edit['jam_buka'] ?? '06:00', 0, 5)) ?>">
      <label>Jam tutup</label><input type="time" name="jam_tutup" value="<?= e(substr($edit['jam_tutup'] ?? '17:00', 0, 5)) ?>">
      <label>Foto kolam</label><input type="file" name="foto" accept="image/jpeg,image/png">
      <label class="check-row"><input type="checkbox" name="aktif" <?= ($edit ? $edit['is_aktif'] : 1) ? 'checked' : '' ?>> Aktif ditampilkan</label>
      <button class="btn btn-primary" type="submit"><?= $edit ? 'Simpan perubahan' : 'Simpan kolam' ?></button>
      <?php if ($edit): ?><a class="btn btn-outline" href="kolam.php">Batal</a><?php endif; ?>
    </form>
  </main>
</div>
<script>function cekHarga(f){ if (parseInt(f.harga.value) <= 0){ alert('Harga harus lebih dari 0'); return false;} return true; }</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
