# Task List: KolamKita — Web Booking Pemancingan

- **Versi:** 1.0
- **Tanggal:** 08 Oktober 2026
- **Dependensi:** `.agents/2-TECH-SPEC.md`
- **Scope:** UI/halaman dulu (tanpa DB), lalu DB & logic
- **Total:** 34 task | Estimasi ± 48 jam

## Status Legend
`Todo` | `In Progress` | `Done`

---

## Modul 1: Setup & Fondasi UI

### T-01: Setup struktur folder project
- **Deskripsi:** Buat seluruh struktur folder sesuai Tech Spec (`config/`, `public/`, `public/owner/`, `public/api/`, `public/assets/css|js/`, `public/uploads/`, `includes/`, `database/`). Buat file placeholder kosong untuk tiap file PHP agar struktur terlihat.
- **Modul:** Setup
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** -
- **Tanggal:** 2026-10-08
- **Estimasi:** 0.5 jam
- **File yang diubah:** struktur folder baru seluruh project

### T-02: Boilerplate config (siap nanti dipakai logic)
- **Deskripsi:** `config/database.php` (koneksi PDO ke MySQL `kolamkita`, mode exception + `ERRMODE_EXCEPTION`) dan `config/helpers.php` (fungsi `e()` escape, `format_rupiah()`, `redirect()`, `generate_kode_booking()` — definisi dulu, dipakai fase logic).
- **Modul:** Setup
- **Prioritas:** Mid
- **Status:** Done
- **Dependensi:** T-01
- **Tanggal:** 2026-10-08
- **Estimasi:** 0.5 jam
- **File yang diubah:** `config/database.php`, `config/helpers.php`

### T-03: Design system CSS + layout dasar
- **Deskripsi:** `assets/css/style.css`: variabel warna (hijau kolam/teal sebagai primary, netral), typography, komponen `.card`, `.badge-status` (kuning/biru/hijau/merah), `.btn` (primary/outline/danger), form input, table, grid slot (`.slot kosong/.penuh/.tutup`), responsive mobile-first. Belum perlu dark mode.
- **Modul:** Fondasi UI
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-01
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/assets/css/style.css`

### T-04: Komponen header & footer bersama
- **Deskripsi:** `includes/header.php` (logo KolamKita, nav: Kolam, Riwayat, Login/Dashboard, Logout — kondisi `$_SESSION['user']`) + `includes/footer.php`. Siap di-include semua halaman.
- **Modul:** Fondasi UI
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-03
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `includes/header.php`, `includes/footer.php`

### T-05: JavaScript dasar (`main.js`)
- **Deskripsi:** `assets/js/main.js`: helper fetch JSON, render grid jam di halaman kolam (dummy data placeholder untuk sekarang), toggle mobile nav, fungsi format rupiah di client. Logic API nyata menyusul.
- **Modul:** Fondasi UI
- **Prioritas:** Mid
- **Status:** Done
- **Dependensi:** T-03
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/assets/js/main.js`

---

## Modul 2: Halaman Publik (UI)

### T-06: Halaman beranda / katalog kolam
- **Deskripsi:** `public/index.php` — hero singkat, form pencarian (kata kunci + rentang harga), grid card kolam (foto, nama, harga/slot, alamat, tombol "Lihat Slot"). Data pakai array dummy dulu, siap diganti query DB nanti.
- **Modul:** Halaman Publik
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-04
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/index.php`

### T-07: Halaman detail kolam + grid pilih slot
- **Deskripsi:** `public/kolam.php` — detail kolam (foto besar, deskripsi, fasilitas, alamat, harga), input tanggal (default hari ini), grid jam `06:00–17:00` dengan class `kosong/penuh/tutup` + legend warna, ringkasan slot terpilih + total harga + tombol "Booking Sekarang" (arahkan ke `booking.php`). Slot dari data dummy via JS.
- **Modul:** Halaman Publik
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-05, T-06
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/kolam.php`, `public/assets/js/main.js`

### T-08: Halaman login
- **Deskripsi:** `public/login.php` — form email + password, pesan error (kredensial salah / blokir bertahap placeholder), link ke register. Styling card centered. Logic auth menyusul (Modul 5).
- **Modul:** Halaman Publik
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-04
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/login.php`

### T-09: Halaman register
- **Deskripsi:** `public/register.php` — form nama, email, no. WhatsApp, password + konfirmasi password; hint aturan (min. 8 karakter). Validasi client-side sederhana dulu, server-side menyusul.
- **Modul:** Halaman Publik
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-08
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/register.php`

### T-10: Halaman logout (placeholder)
- **Deskripsi:** `public/logout.php` — render struktur + komentar proses hapus sesi (diisi saat fase logic).
- **Modul:** Halaman Publik
- **Prioritas:** Low
- **Status:** Done
- **Dependensi:** T-08
- **Tanggal:** 2026-10-08
- **Estimasi:** 0.2 jam
- **File yang diubah:** `public/logout.php`

---

## Modul 3: Halaman Pemancing (UI)

### T-11: Halaman form booking
- **Deskripsi:** `public/booking.php` — ringkasan pilihan (kolam, tanggal, slot jam, harga), form jumlah orang + nama + no. WhatsApp + catatan, total harga otomatis (JS), tombol "Booking Sekarang". Dibuka dari `kolam.php?kolam=X&slot=Y`. Logic submit & validasi menyusul.
- **Modul:** Booking (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-07, T-08
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/booking.php`, `public/assets/js/main.js`

### T-12: Halaman sukses booking + tombol WhatsApp
- **Deskripsi:** State sukses `booking.php` / `booking_sukses.php` — tampilkan kode booking (`BK-YYYYMMDD-XXX`), ringkasan booking, dua tombol: "Konfirmasi ke WhatsApp Saya" dan "Notifikasi Pemilik" (link `wa.me/62...?text=...` — format pesan: kode booking, kolam, tanggal, jam, total).
- **Modul:** Booking (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-11
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/booking.php` (state sukses) / `public/booking_sukses.php`

### T-13: Halaman riwayat booking + upload bukti
- **Deskripsi:** `public/riwayat.php` — tabel booking saya (kode, kolam, tanggal, jam, total, badge status, badge pembayaran), aksi: lihat detail, upload bukti bayar (form upload jpg/png ≤ 5MB + preview). Data dummy dulu, query DB menyusul.
- **Modul:** Booking (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-04, T-11
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/riwayat.php`

### T-14: Endpoint API ketersediaan (struktur JSON)
- **Deskripsi:** `public/api/ketersediaan.php` — terima `?kolam=X&tanggal=Y`, return JSON `[{jam, status}]` dari data dummy. Struktur API disiapkan sekarang, query DB + join booking aktif menyusul di Modul 5.
- **Modul:** Booking (UI)
- **Prioritas:** Mid
- **Status:** Done
- **Dependensi:** T-07
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/api/ketersediaan.php`

### T-15: Endpoint API booking (struktur POST)
- **Deskripsi:** `public/api/booking.php` — terima POST JSON (kolam, slot, tanggal, jumlah_orang, catatan), validasi field + return JSON kode booking dummy. Atomic check DB menyusul.
- **Modul:** Booking (UI)
- **Prioritas:** Mid
- **Status:** Done
- **Dependensi:** T-11, T-14
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/api/booking.php`

---

## Modul 4: Halaman Pemilik (UI)

### T-16: Layout dashboard pemilik + proteksi route placeholder
- **Deskripsi:** `public/owner/dashboard.php` dengan struktur shell: sidebar/topnav khusus pemilik (Dashboard, Kolam, Jadwal, Pembayaran) + `includes/auth.php` placeholder cek role (`$_SESSION['role'] === 'pemilik'` → logika penuh menyusul). Semua halaman `/owner/*` pakai shell ini.
- **Modul:** Dashboard Pemilik (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-04, T-08
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/owner/dashboard.php`, `includes/auth.php`

### T-17: Dashboard: tabel booking masuk + ringkasan pendapatan
- **Deskripsi:** Isi `dashboard.php` — kartu ringkasan (pendapatan hari ini/minggu ini, jumlah booking, jumlah batal), filter tanggal + status, tabel booking masuk (kode, pemancing, kolam, slot, total, badge status, dropdown aksi: konfirmasi/tandai bayar/selesai/batal). Data dummy dulu.
- **Modul:** Dashboard Pemilik (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-16
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/owner/dashboard.php`

### T-18: Halaman CRUD kolam pemilik
- **Deskripsi:** `public/owner/kolam.php` — daftar kolam milik sendiri + tombol tambah; form tambah/edit (nama, deskripsi, alamat, harga/slot, jam buka/tutup, upload foto, status aktif). Validasi client-side (harga > 0). Proses simpan ke DB menyusul.
- **Modul:** Dashboard Pemilik (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-16
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/owner/kolam.php`

### T-19: Halaman manajemen slot jadwal
- **Deskripsi:** `public/owner/jadwal.php` — pilih kolam + rentang tanggal, generator rentang jam dari `jam_buka`→`jam_tutup`, checklist buka/tutup slot per tanggal, tombol simpan. Belum persist ke DB.
- **Modul:** Dashboard Pemilik (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-16, T-18
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/owner/jadwal.php`

### T-20: Halaman verifikasi pembayaran
- **Deskripsi:** `public/owner/pembayaran.php` — daftar booking `menunggu_pembayaran` + menunggu verifikasi bukti, preview bukti bayar (modal/lightbox), tombol Terima/Tolak, info nominal DP vs total. Logic update status menyusul.
- **Modul:** Dashboard Pemilik (UI)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-16, T-17
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/owner/pembayaran.php`

---

## Modul 5: Setup DB & Logic/Backend

### T-21: Skema database + seed data
- **Deskripsi:** `database/kolamkita.sql` — buat 5 tabel (`users`, `kolam`, `slots`, `bookings`, `payments`) sesuai Tech Spec + index & unique key anti double booking + seed: 1 pemilik, 2 pemancing, 2 kolam, slot 7 hari ke depan. Instruksi import di README.
- **Modul:** Database
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-02
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `database/kolamkita.sql`, `README.md`

### T-22: Logic registrasi & login (server-side)
- **Deskripsi:** Proses POST di `register.php` + `login.php` — validasi email unik/format, `password_hash`/`password_verify`, `session_start`, `session_regenerate_id`, redirect per role. Blokir bertahap: 5x→5 menit, 10x→10 menit, 15x→15 menit (counter per email+IP di DB/session).
- **Modul:** Autentikasi
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-21, T-08, T-09
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/login.php`, `public/register.php`, `public/logout.php`, `config/helpers.php`

### T-23: RBAC penuh (`includes/auth.php`)
- **Deskripsi:** Fungsi cek sesi + role: halaman pemancing wajib login, `/owner/*` wajib role `pemilik`, selain itu redirect `/login.php` / 403. Terapkan ke semua halaman.
- **Modul:** Autentikasi
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-22, T-16
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `includes/auth.php`, semua halaman

### T-24: Katalog & pencarian dari DB
- **Deskripsi:** `index.php` query kolam `is_aktif=1` + filter kata kunci/harga (PDO prepared); `kolam.php` load detail kolam dari DB. Ganti data dummy.
- **Modul:** Katalog
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-21, T-06, T-07
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/index.php`, `public/kolam.php`

### T-25: API ketersediaan dari DB
- **Deskripsi:** `api/ketersediaan.php` query `slots` per kolam+tanggal → cek booking aktif → JSON status `kosong/penuh/tutup`. Ganti dummy di JS.
- **Modul:** Katalog
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-21, T-14
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/api/ketersediaan.php`, `public/assets/js/main.js`

### T-26: Logic booking atomic + kode booking
- **Deskripsi:** `api/booking.php` — transaksi DB: re-check slot kosong → hitung total → insert `bookings` (kode `BK-YYYYMMDD-XXX`, status `menunggu_pembayaran`) → commit; tangkap bentrok unique index → tolak. Batasi maks 10 booking aktif per user.
- **Modul:** Booking
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-21, T-15, T-25
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/api/booking.php`, `public/booking.php`

### T-27: Notifikasi WhatsApp dari data booking
- **Deskripsi:** `wa_konfirmasi.php` & `wa_notif.php` — generate `https://wa.me/62...?text=` berisi kode booking, kolam, tanggal, jam, jumlah orang, total, status. Tautkan tombol di halaman sukses & dashboard.
- **Modul:** Booking
- **Prioritas:** Mid
- **Status:** Done
- **Dependensi:** T-26, T-12
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `public/wa_konfirmasi.php`, `public/wa_notif.php`, `public/booking.php`

### T-28: Riwayat booking dari DB + detail
- **Deskripsi:** `riwayat.php` query booking milik user (join kolam, slot, payments), badge status akurat, link detail. Booking `dibatalkan` tidak mengunci slot.
- **Modul:** Booking
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-21, T-13, T-23
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/riwayat.php`

### T-29: Upload bukti bayar (server-side)
- **Deskripsi:** `upload_bukti.php` — validasi jpg/png, maks **5MB**, MIME check `finfo`, rename file random, simpan ke `uploads/`, insert `payments` (status `menunggu`). `.htaccess` larang eksekusi PHP di `uploads/`.
- **Modul:** Pembayaran
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-28, T-21
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/upload_bukti.php`, `public/uploads/.htaccess`

### T-30: CRUD kolam + jadwal slot (server-side)
- **Deskripsi:** Proses POST di `owner/kolam.php` (insert/update/soft-delete `is_aktif`, upload foto kolam, validasi harga>0) dan `owner/jadwal.php` (generate/buka/tutup slot per tanggal; slot punya booking aktif butuh konfirmasi batal dulu).
- **Modul:** Dashboard Pemilik
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-21, T-18, T-19, T-23
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/owner/kolam.php`, `public/owner/jadwal.php`

### T-31: Verifikasi pembayaran + transisi status booking
- **Deskripsi:** `owner/pembayaran.php` — terima/tolak `payments` → update `bookings.status` (`menunggu_pembayaran`→`dikonfirmasi`/`lunas`→`selesai`/`dibatalkan`); aksi batal → slot kembali kosong. Hanya pemilik kolam terkait.
- **Modul:** Pembayaran
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-29, T-17, T-23
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `public/owner/pembayaran.php`, `public/owner/dashboard.php`

### T-32: Booking masuk + ringkasan pendapatan (DB)
- **Deskripsi:** `owner/dashboard.php` query booking semua kolam milik pemilik (join users/kolam) + filter; agregasi `SUM(nominal payments diterima)` harian/mingguan, jumlah booking & batal (batal tidak dihitung pendapatan).
- **Modul:** Dashboard Pemilik
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-31, T-17, T-23
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/owner/dashboard.php`

### T-33: Keamanan menyeluruh (CSRF, XSS, hardening)
- **Deskripsi:** Token CSRF semua form POST, `htmlspecialchars()` semua output, cookie sesi `httponly`+`samesite`, rate-limit login, review RBAC semua route.
- **Modul:** Keamanan
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-22, T-23, T-26, T-31
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** seluruh file PHP

### T-34: E2E test manual + README akhir
- **Deskripsi:** Uji alur lengkap: daftar → login → cek slot → booking → upload bukti → verifikasi → riwayat → pendapatan; uji anti double booking (2 request bersamaan), blokir login 5/10/15 menit, upload >5MB ditolak. Tulis README (setup XAMPP, import DB, akun seed).
- **Modul:** Testing
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-01..T-33 (semua)
- **Tanggal:** 2026-10-08
- **Estimasi:** 2 jam
- **File yang diubah:** `README.md`, catatan bug fix

### T-35: Fitur ikan pada katalog & rekomendasi pencarian ikan (UI)
- **Deskripsi:** Tambah kolom dummy `jenis_ikan` per kolam di `index.php` + tampilkan **chip ikan** di tiap card; tambah input teks bebas "Cek ikan" di hero; saat user mencari nama ikan tampilkan bagian "Rekomendasi untuk ikan: X" berisi kolam yang cocok. Tambah chip ikan juga di `kolam.php`.
- **Modul:** Katalog
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-06, T-07
- **Tanggal:** 2026-10-08
- **Estimasi:** 1.5 jam
- **File yang diubah:** `public/index.php`, `public/kolam.php`

### T-36: Form jenis ikan di kelola kolam (owner UI)
- **Deskripsi:** Tambah input teks "Jenis ikan (Mas, Nila, Patin — pisahkan koma)" di form `owner/kolam.php`; tampilkan chip ikan di tabel daftar kolam.
- **Modul:** Dashboard Pemilik
- **Prioritas:** Mid
- **Status:** Done
- **Dependensi:** T-18
- **Tanggal:** 2026-10-08
- **Estimasi:** 0.5 jam
- **File yang diubah:** `public/owner/kolam.php`

### T-37: Kolom `jenis_ikan` + filter ikan dari DB
- **Deskripsi:** Tambah `jenis_ikan VARCHAR(255)` ke tabel `kolam` (DB) + seed; `index.php` / `kolam.php` query ikan dari DB; `api` / filter `WHERE jenis_ikan LIKE %ikan%` saat pencarian. Integrasikan ke T-24/T-21.
- **Modul:** Database
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** T-21, T-24, T-35
- **Tanggal:** 2026-10-08
- **Estimasi:** 1 jam
- **File yang diubah:** `database/kolamkita.sql`, `public/index.php`, `public/kolam.php`

---

## Revisi Pasca-Implementasi

- **Booking tanpa login (tamu):** FR-08 diubah — `booking.php` & `api/booking.php` tidak mewajibkan sesi; data tamu disimpan di `bookings.guest_nama` / `guest_wa` dengan `user_id` NULL. Batas 10 booking aktif dihitung per nomor WhatsApp untuk tamu.
- **Tombol WhatsApp disederhanakan:** hanya "Notifikasi pemilik lewat WhatsApp". File `public/wa_konfirmasi.php` dihapus (tidak ada lagi konfirmasi ke WA pemancing).
- **Session cookie:** `httponly` + `samesite` diset sebelum `session_start()` di `includes/header.php` dan `includes/auth.php` (menghindari warning `ini_set()`).

### Revisi 2 — Perbaikan dari code review (08 Oktober 2026)

| # | Masalah | Perbaikan | File |
|---|---------|-----------|------|
| 1 | Booking gagal setelah pembatalan (UNIQUE index menahan baris `dibatalkan`) | Generated column `slot_aktif` (NULL bila dibatalkan) + UNIQUE 4 kolom | `database/kolamkita.sql` |
| 2 | Akun terkunci permanen setelah blokir login kedaluwarsa (`failed_attempts` tak di-reset) | Reset counter saat `locked_until` sudah lewat | `public/login.php` |
| 3 | Bukti bayar tidak bisa dikirim ulang setelah DITOLAK | Form tampil lagi + `INSERT ... ON DUPLICATE KEY UPDATE`, plus pilihan DP/Lunas & nominal | `public/riwayat.php`, `public/upload_bukti.php` |
| 4 | `uploads/.htaccess` `php_flag engine off` berisiko HTTP 500 di hosting tertentu | Ganti ke `<FilesMatch> Require all denied` | `public/uploads/.htaccess` |
| 5 | Pesan SQLException mentah tampil ke user | Mapping `23000/1062` → "Slot ini sudah dipesan. Pilih jam lain." | `public/api/booking.php` |
| 6 | Hapus kolam via GET tanpa CSRF | POST + `csrf_verify()` + konfirmasi client | `public/owner/kolam.php` |
| 7 | `wa_notif.php` bisa dipakai enumerate kode booking | Link hanya valid 1 jam (`created_at >= NOW() - INTERVAL 1 HOUR`) | `public/wa_notif.php` |
| 8 | Chip ikan rusak jika disimpan tanpa spasi setelah koma | Normalisasi `jenis_ikan` saat simpan + filter nilai kosong saat render | `public/owner/kolam.php`, `public/index.php`, `public/kolam.php` |
| 9 | WhatsApp tidak terisi untuk user login | Prefill dari `users.no_wa` | `public/booking.php` |
| 10 | Error DB dikirim sebagai HTML di endpoint JSON | `config/database.php` mendeteksi `/api/` dan mengirim JSON | `config/database.php` |
| 11 | Rule CSS `.float-slot.water` tak terpakai | Dihapus | `public/assets/css/style.css` |

### Revisi 3 — Fitur blokir login dihapus total

| # | Perubahan | Detail |
|---|-----------|--------|
| 1 | Logika blokir login 5/10/15 menit dihapus | `public/login.php` kini hanya cek `password_verify()`; gagal → pesan tunggal "Email atau password salah." |
| 2 | Kolom `users.failed_attempts` & `users.locked_until` di-DROP | `database/kolamkita.sql` + `ALTER TABLE` di DB aktif |
| 3 | Hint blokir di form login dihapus | `public/login.php` |
| 4 | Dokumen disinkronkan | PRD (FR-02 + NFR Keamanan), Tech Spec (alur login, Business Rules, Keamanan), README checklist |

**Catatan:** brute-force protection tidak ada di level aplikasi pada V1. Saat deploy ke server publik, pasang `fail2ban` atau rate-limit di Apache/nginx.

### Revisi 4 — Booking wajib akun (kembali ke konsep awal)

| # | Perubahan | Detail |
|---|-----------|--------|
| 1 | Konsep booking tamu dibatalkan | `bookings.user_id` kembali `INT NOT NULL`; kolom `guest_nama` & `guest_wa` di-DROP (kolom `slot_aktif` tetap) |
| 2 | `booking.php` wajib login | Tanpa sesi → redirect `login.php?...&next=<REQUEST_URI>` |
| 3 | `login.php` dukung `?next=` | Setelah login sukses kembali ke form booking; `next` tervalidasi hanya path internal |
| 4 | Nama & WhatsApp readonly dari akun | Ditambah gaya `input[readonly]`; identitas pemancing diserver dari `users`, bukan dari POST |
| 5 | `api/booking.php` kembali 401 tanpa sesi | Batas 10 booking aktif hanya per `user_id`; INSERT tanpa kolom guest |
| 6 | Booking tamu yang sudah ada dihapus | `BK-20261010-2334` (Haikal) dibersihkan sebelum ALTER |
| 7 | Dokumen disinkronkan | PRD (FR-08 + NFR usability), Tech Spec (skema, route, alur booking) |

**Efek samping yang bagus:** `owner/dashboard.php` & `owner/pembayaran.php` kembali andal — `JOIN users` (INNER JOIN) kini selalu punya baris karena semua booking punya `user_id`, sehingga booking tidak lagi hilang dari dashboard pemilik.

### Revisi 5 — Halaman introduction + katalog dipisah

| # | Perubahan | File |
|---|-----------|------|
| 1 | `index.php` ditulis ulang sebagai halaman introduction | `public/index.php` |
| 2 | Logika katalog dipindah ke file baru `semua-kolam.php` (query + filter + rekomendasi ikan) | `public/semua-kolam.php` |
| 3 | Nav "Kolam" mengarah ke katalog; brand tetap ke landing | `includes/header.php` |
| 4 | Link "Kembali / Cari kolam dulu / fallback login" diarahkan ke semua-kolam.php | `booking.php`, `booking_sukses.php`, `riwayat.php`, `login.php` |
| 5 | Komponen CSS baru: `.hero-actions`, `.btn-ghost`, `.steps`/`.step`/`.step-num`, `.feature-grid`/`.feature-card`, `.cta-band`, `.preview-list`, `.page-head` | `public/assets/css/style.css` |
| 6 | Dokumen disinkronkan | PRD (FR-05 + FR-16 baru), Tech Spec (route + struktur folder) |

### Revisi 6 - Nominal DP dikunci 50% (input nominal dihapus)

| # | Perubahan | File |
|---|-----------|------|
| 1 | Input nominal manual dihapus dari form bukti bayar | `public/riwayat.php` |
| 2 | Select "Bayar" dengan opsi **DP 50%** / **Lunas** (nominal rupiah dihitung per baris), default "Pilih…" (disabled) | `public/riwayat.php` |
| 3 | Nominal diturunkan server-side dari `jenis`; parameter `nominal` dari POST diabaikan | `public/upload_bukti.php` |
| 4 | Nilai `jenis` di luar DP/Lunas ditolak dengan HTTP 400 | `public/upload_bukti.php` |
| 5 | Nama file bukti lama dihapus saat bukti dikirim ulang | `public/upload_bukti.php` |
| 6 | Tampilan nominal ke pemilik & sisa tagihan ke pemancing | `public/owner/pembayaran.php`, `public/riwayat.php` |
| 7 | Dokumen disinkronkan | PRD (FR-10), Tech Spec (alur pembayaran + business rules) |

**Alasan:** `step="1000"` dengan `min="1"` hanya menerima 1/1001/2001/... sehingga nominal "5000" ditolak oleh browser dengan pesan "nilai terdekat 4001 dan 5001". Karena DP sekarang tetap 50%, nominal tidak perlu diketik sama sekali - masalah validasi HTML5 hilang dan manipulasi dari sisi klien mustahil.

---

## Ringkasan
| Modul | Task | Jumlah |
|-------|------|--------|
| 1. Setup & Fondasi UI | T-01 – T-05 | 5 |
| 2. Halaman Publik (UI) | T-06 – T-10 | 5 |
| 3. Halaman Pemancing (UI) | T-11 – T-15 | 5 |
| 4. Halaman Pemilik (UI) | T-16 – T-20 | 5 |
| 5. DB & Logic/Backend | T-21 – T-34 | 14 |
| 6. Fitur Ikan (Tambahan) | T-35 – T-37 | 3 |
| **Total** | | **37** |

**Urutan eksekusi:** UI dulu (T-01 → T-20) → DB & logic (T-21 → T-34)
















