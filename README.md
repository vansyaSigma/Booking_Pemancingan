# KolamKita — Web Booking Pemancingan

Stack: HTML + CSS + JS (vanilla) + PHP native (XAMPP) + MySQL/MariaDB.

## Setup Lokal (XAMPP)

1. Jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
2. Pindahkan/symlink project ke `C:\xampp\htdocs\Booking_Pemancingan`
   (sudah dibuat via junction: `C:\xampp\htdocs\Booking_Pemancingan` → project ini).
3. Import database:
   ```
   C:\xampp\mysql\bin\mysql.exe -u root < database\kolamkita.sql
   ```
   atau buka `http://localhost/phpmyadmin` → Import → pilih `database/kolamkita.sql`.
4. Buka browser:
   - `http://localhost/Booking_Pemancingan/public/index.php` — halaman awal (pengenalan produk)
   - `http://localhost/Booking_Pemancingan/public/semua-kolam.php` — katalog & pencarian kolam

## Akun Seed (password semua: `password123`)

| Peran | Email |
|-------|-------|
| Pemilik | `sari@kolamkita.id` |
| Pemancing | `rian@example.com`, `ayu@example.com` |

## Checklist E2E (uji manual)

1. **Login & RBAC:** login sebagai pemancing → `/public/index.php` tampil; akses `/public/owner/dashboard.php` → 403. Login sebagai `sari@kolamkita.id` → redirect dashboard pemilik; akses halaman pemancing → redirect/403.
2. **Login gagal:** password salah → pesan "Email atau password salah." Akun tidak pernah terkunci (V1 tidak memakai blokir percobaan).
3. **Katalog & ikan:** buka `/public/semua-kolam.php` → card kolam tampil; `?ikan=Mas` → section "Rekomendasi untuk ikan: Mas"; `?q=surya` → filter nama; `?q=depok` → filter alamat.
4. **Slot:** buka `kolam.php?id=1` → grid jam 06:00–17:00; untuk tanggal hari ini semua `kosong`. Ubah tanggal via date picker → grid reload (fetch `api/ketersediaan.php`).
5. **Booking (wajib login):** pilih jam → "Booking sekarang". Belum login → diarahkan ke login, dan setelah berhasil login kembali ke form booking yang sama. Nama & WhatsApp terisi readonly dari akun. Submit → halaman sukses menampilkan kode BK-... + satu tombol "Notifikasi pemilik lewat WhatsApp".
6. **Anti double booking:** booking slot & tanggal yang sama dua kali -> yang kedua ditolak "Slot ini sudah dipesan. Pilih jam lain." Setelah booking dibatalkan, slot bisa dibooking ulang (dicek).
7. **Bukti bayar:** di riwayat.php pilih **DP 50%** atau **Lunas**, lalu upload foto JPG/PNG <=5MB. Nominal dihitung server (50%/100% dari tagihan) - tidak bisa diketik manual. Kalau bukti DITOLAK, tombol "Kirim ulang bukti" muncul lagi. Format lain atau >5MB ditolak.
8. **Verifikasi pembayaran:** login pemilik → `owner/pembayaran.php` → Terima → status booking jadi `dikonfirmasi`; Tandai lunas → `lunas`; Selesai → `selesai`. Batal → status `dibatalkan`, slot kembali kosong.
9. **Dashboard pendapatan:** setelah pembayaran diterima, kartu "Pendapatan minggu ini" di `owner/dashboard.php` bertambah; booking batal tidak dihitung.
10. **Upload kolam:** `owner/kolam.php` -> tambah kolam (harga >0, jenis ikan wajib, format "Mas,Nila" dinormalisasi jadi "Mas, Nila") -> muncul di beranda; Edit; Hapus (POST + CSRF) -> nonaktif.

## Dokumen
- `.agents/1-PRD.md` — Product Requirements
- `.agents/2-TECH-SPEC.md` — Technical Specification
- `.agents/3-TASKS.md` — Task List (status)
- `.agents/4-DESIGN.md` — Design tokens & signature
