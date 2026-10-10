# Tech Spec: KolamKita — Web Booking Pemancingan

- **Versi:** 1.0
- **Tanggal:** 08 Oktober 2026
- **Dependensi:** `.agents/1-PRD.md` (sudah dibaca)
- **Target:** Tasking → Implementasi

---

## 1. Tech Stack & Arsitektur

### Tech Stack
| Layer | Technology | Version |
|-------|------------|---------|
| Frontend | HTML5 + CSS3 + JavaScript (vanilla) | - |
| Styling | Tailwind via CDN / CSS custom (disesuaikan) | 3.x |
| State | Session PHP + localStorage minimal | - |
| Backend | PHP native (tanpa framework) | 8.2 (bundel XAMPP) |
| Database | MySQL / MariaDB | 10.4+ (XAMPP) |
| ORM | PDO (prepared statement) | - |
| Auth | Session-based + password_hash (bcrypt) | - |
| Hosting | XAMPP lokal (Apache + MySQL) | - |
| Caching | Tidak perlu (skala V1 kecil) | - |

### Arsitektur Sistem
```
Browser (HTML/CSS/JS)
        │
        ▼
   PHP (Apache)  ──►  Upload folder (foto kolam / bukti bayar)
        │
        ▼
   MySQL/MariaDB (PDO prepared statement)
```
- **Pola:** Monolith server-rendered — PHP mencetak HTML, JS vanilla untuk interaksi (fetch API untuk ketersediaan slot & validasi form).
- **Konfirmasi WhatsApp:** generate link `https://wa.me/62xxxx?text=...` (tanpa API berbayar).

### Struktur Folder
```
Booking_Pemancingan/
├── config/
│   ├── database.php          # Koneksi PDO
│   └── helpers.php           # Fungsi umum (auth, format rupiah, sanitasi)
├── public/
│   ├── index.php             # Halaman introduction (landing)
│   ├── semua-kolam.php       # Katalog kolam + pencarian/filter
│   ├── kolam.php             # Detail kolam + ketersediaan slot
│   ├── booking.php           # Form booking
│   ├── login.php / register.php / logout.php
│   ├── riwayat.php           # Riwayat booking pemancing
│   ├── owner/
│   │   ├── dashboard.php     # Booking masuk + ringkasan pendapatan
│   │   ├── kolam.php         # CRUD kolam
│   │   ├── jadwal.php        # Manajemen slot
│   │   └── pembayaran.php    # Konfirmasi DP/lunas
│   ├── api/
│   │   ├── ketersediaan.php  # GET slot per kolam+tanggal (JSON)
│   │   └── booking.php       # POST booking (atomic check)
│   ├── assets/
│   │   ├── css/style.css
│   │   └── js/main.js
│   └── uploads/              # Foto kolam & bukti bayar
├── includes/
│   ├── header.php / footer.php
│   └── auth.php              # Cek sesi + RBAC
├── database/
│   └── kolamkita.sql          # Schema + seed data
└── README.md
```

### Justifikasi
- **PHP native + HTML/CSS/JS:** sesuai matakuliah PBW, tanpa dependency build tool, langsung jalan di XAMPP.
- **MySQL/MariaDB:** relasional cocok untuk relasi kolam → slot → booking → pembayaran; tersedia default di XAMPP.
- **PDO prepared statement:** proteksi SQL injection bawaan PHP.
- **Session-based auth:** paling sederhana untuk monolith tanpa JWT.
- **XAMPP lokal:** zero-cost, mudah dipresentasikan, deploy shared hosting tinggal upload.

---

## 2. Database Design

### Ringkasan Database
| Item | Detail |
|------|--------|
| Database | MySQL / MariaDB (XAMPP) |
| ORM/Driver | PDO (prepared statement) |
| Pendekatan | Relasional |
| Tools Migrasi | File SQL manual (`database/kolamkita.sql`) + phpMyAdmin |

### Entity Overview
| Entity | Key Fields | Relasi |
|--------|-----------|--------|
| `users` | id, nama, email, password_hash, no_wa, role (`pemancing`/`pemilik`/`admin`), created_at | → `kolam` (1:N, pemilik) |
| `kolam` | id, owner_id, nama, deskripsi, alamat, harga_per_slot, jam_buka, jam_tutup, foto, is_aktif, **jenis_ikan (teks "Mas, Nila, Patin")** | ← `users`, → `slots`, → `bookings` |
| `slots` | id, kolam_id, tanggal, jam (`06:00`, `07:00`...), status (`buka`/`tutup`) | ← `kolam`, → `bookings` |
| `bookings` | id, kode_booking (unik), user_id, kolam_id, slot_id, tanggal, jumlah_orang, total_harga, status, catatan, created_at | ← `users`, `kolam`, `slot`; → `payments` (1:1) |
| `payments` | id, booking_id, jenis (`dp`/`lunas`), nominal, bukti_file, status (`menunggu`/`diterima`/`ditolak`), paid_at | ← `bookings` |

### Index Strategy
- `bookings.slot_id + bookings.tanggal` — cek ketersediaan slot (fast lookup)
- **UNIQUE index `bookings(kolam_id, slot_id, tanggal)`** — jaring pengaman anti double booking di level DB (dengan penanganan status batal saat implementasi)
- `bookings.kode_booking` — UNIQUE, lookup dari WhatsApp
- `bookings.user_id` — riwayat booking per user
- `kolam.owner_id` — daftar kolam per pemilik
- `slots(kolam_id, tanggal)` — load ketersediaan per tanggal
- `users.email` — UNIQUE, login
- `kolam.is_aktif` — filter katalog
- Filter ikan pakai `LIKE %nama_ikan%` di kolom `jenis_ikan` (skala kecil, cukup tanpa full-text index)

### Data Flow
1. **User daftar** → baris di `users` (role pemancing) → login → sesi aktif.
2. **Pemilik buka slot** → baris di `slots` per tanggal+jam → tampil di halaman ketersediaan.
3. **Pemancing booking** → cek slot kosong (query `bookings` aktif) → insert `bookings` (status `menunggu_pembayaran`) → relasi slot terpakai.
4. **Booking dibuat** → link WhatsApp ke pemancing & pemilik.
5. **Transfer DP/lunas** → pemancing upload bukti → insert `payments` (status `menunggu`) → pemilik verifikasi → `payments.status = diterima` + `bookings.status` → `dikonfirmasi` / `lunas`.
6. **Booking dibatalkan** → `bookings.status = dibatalkan` → slot otomatis kosong kembali.
7. **Dashboard pendapatan** → agregasi `payments` (status `diterima`) per rentang tanggal.

### Contoh Schema Ringkas (SQL)
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  no_wa VARCHAR(20) NOT NULL,
  role ENUM('pemancing','pemilik','admin') DEFAULT 'pemancing',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kolam (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  nama VARCHAR(120) NOT NULL,
  deskripsi TEXT,
  alamat VARCHAR(255),
  harga_per_slot DECIMAL(12,2) NOT NULL,
  jam_buka TIME, jam_tutup TIME,
  foto VARCHAR(255),
  is_aktif TINYINT(1) DEFAULT 1,
  jenis_ikan VARCHAR(255),
  FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE slots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kolam_id INT NOT NULL,
  tanggal DATE NOT NULL,
  jam TIME NOT NULL,
  status ENUM('buka','tutup') DEFAULT 'buka',
  FOREIGN KEY (kolam_id) REFERENCES kolam(id),
  UNIQUE KEY uniq_slot (kolam_id, tanggal, jam)
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_booking VARCHAR(20) NOT NULL UNIQUE,
  user_id INT NOT NULL, kolam_id INT NOT NULL, slot_id INT NOT NULL,
  tanggal DATE NOT NULL, jumlah_orang INT NOT NULL,
  total_harga DECIMAL(12,2) NOT NULL,
  status ENUM('menunggu_pembayaran','dikonfirmasi','lunas','selesai','dibatalkan') DEFAULT 'menunggu_pembayaran',
  catatan TEXT,
  slot_aktif VARCHAR(3) GENERATED ALWAYS AS (IF(status = 'dibatalkan', NULL, 'A')) STORED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (kolam_id) REFERENCES kolam(id),
  FOREIGN KEY (slot_id) REFERENCES slots(id),
  UNIQUE KEY uniq_active_slot (kolam_id, slot_id, tanggal, slot_aktif)
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL UNIQUE,
  jenis ENUM('dp','lunas') NOT NULL,
  nominal DECIMAL(12,2) NOT NULL,
  bukti_file VARCHAR(255),
  status ENUM('menunggu','diterima','ditolak') DEFAULT 'menunggu',
  paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id)
);
```
> Catatan: unique index `uniq_active_slot` menyertakan kolom generated `slot_aktif VARCHAR(3) AS (IF(status='dibatalkan', NULL, 'A')) STORED`. Booking aktif mengunci slot (nilai 'A'), sedangkan booking `dibatalkan` bernilai NULL sehingga **tidak bentrok** dan slot bisa dibooking ulang.

---

## 3. Interface Design

> Stack: **PHP monolith (server-rendered)** → tabel Halaman/Route + Aksi + Auth.

### Halaman Publik & Pemancing
| Method | Path / Action | Description | Auth |
|--------|---------------|-------------|------|
| GET | `/public/index.php` | Halaman introduction/landing: hero + 2 CTA, cara kerja, fitur, CTA daftar, preview kolam | No |
| GET | `/public/semua-kolam.php` | Katalog kolam + pencarian/filter (kata kunci, **ikan**), rekomendasi ikan, grid card | No |
| GET | `/public/kolam.php?id=X` | Detail kolam + pilih tanggal → daftar slot (JSON fetch `/api/ketersediaan.php`) | No |
| GET/POST | `/public/register.php` | Form daftar (nama, email, WA, password) | No |
| GET/POST | `/public/login.php` | Form login → redirect sesuai role | No |
| GET | `/public/logout.php` | Hapus sesi | Yes |
| GET/POST | `/public/booking.php?kolam=X&slot=Y` | Form booking (nama, WhatsApp, jumlah orang, catatan) → submit POST → hasil: kode booking + tombol notifikasi WA | Yes (pemancing) |
| GET | `/public/riwayat.php` | Daftar booking saya + status pembayaran + detail | Yes (pemancing) |
| POST | `/public/upload_bukti.php` | Upload foto bukti transfer (jpg/png ≤ 5MB) | Yes (pemancing) |
| GET | `/public/api/ketersediaan.php?kolam=X&tanggal=YYYY-MM-DD` | JSON daftar slot + status (kosong/penuh/tutup) | No |
| POST | `/public/api/booking.php` | Submit booking (atomic check) → kode booking | Yes (pemancing) |

### Halaman Pemilik (`/public/owner/`)
| Method | Path / Action | Description | Auth |
|--------|---------------|-------------|------|
| GET | `/owner/dashboard.php` | Booking masuk (filter tanggal/status) + ringkasan pendapatan harian/mingguan | Yes (pemilik) |
| GET/POST | `/owner/kolam.php` | CRUD kolam milik sendiri (nama, harga, foto, jam operasional) | Yes (pemilik) |
| GET/POST | `/owner/jadwal.php` | Buka/tutup slot per tanggal (generate rentang jam) | Yes (pemilik) |
| POST | `/owner/pembayaran.php` | Verifikasi bukti bayar → terima/tolak; ubah status booking (konfirmasi/selesai/batal) | Yes (pemilik) |

### Halaman Auth & Notifikasi
| Method | Path / Action | Description | Auth |
|--------|---------------|-------------|------|
| POST | `/includes/auth.php` | Cek sesi + RBAC, redirect `/login.php` jika belum login, 403 jika role salah | - |
| GET | `/public/wa_notif.php?kode=BK-...` | Generate link `wa.me` ke pemilik (booking masuk) | No |

### Komponen UI yang Diulang
- **Header:** logo "KolamKita", nav (Kolam, Riwayat, Login/Dashboard, Logout)
- **Card kolam:** foto, nama, harga/slot, alamat, tombol "Lihat Slot"
- **Pilih slot:** grid jam (`06:00–17:00`) — hijau kosong, merah penuh, abu ditutup
- **Form booking:** ringkasan kolam + slot terpilih + total harga + tombol "Booking Sekarang"
- **Halaman sukses booking:** kode booking + tombol "Konfirmasi via WhatsApp"
- **Tabel dashboard pemilik:** booking masuk + dropdown aksi status
- **Badge status:** `menunggu_pembayaran` (kuning), `dikonfirmasi` (biru), `lunas`/`selesai` (hijau), `dibatalkan` (merah)

---

## 4. Alur Logika & Business Rules

> Pola alur (monolith server-rendered): **User → Halaman PHP → Proses/Query (PDO) → Redirect/Render HTML**

### Alur Registrasi & Login (FR-01, FR-02, FR-03)
1. User isi form `register.php` → POST ke server.
2. Validasi server-side: email unik, format valid, password ≥ 8 karakter, no. WA valid.
3. `password_hash()` (bcrypt) → insert ke `users` (role `pemancing`) → redirect `login.php`.
4. Login: cocokkan email + verifikasi hash → `session_start()` → simpan `user_id`, `role`.
5. Redirect berdasarkan role: `pemancing` → beranda, `pemilik` → `owner/dashboard.php`.
6. Gagal login → pesan "Email atau password salah." tanpa penguncian akun (V1 tidak memakai counter percobaan).

### Alur Pencarian & Cek Ketersediaan (FR-05, FR-06)
1. User buka beranda → query kolam `is_aktif = 1` + filter (kata kunci, harga).
2. Klik kolam → `kolam.php?tanggal=...` → JS fetch `api/ketersediaan.php?kolam=X&tanggal=Y`.
3. Server ambil semua `slots` kolam pada tanggal → cek booking aktif per slot.
4. Return JSON: `[{jam, status: 'kosong'|'penuh'|'tutup'}]` → grid jam dirender JS.

### Alur Booking (FR-08, FR-09) — inti anti double booking
1. User pilih slot kosong → `booking.php`. **Wajib login:** bila belum punya sesi, redirect ke `login.php?err=…&next=<REQUEST_URI>`; setelah login sukses, `login.php` mengarahkan balik ke form booking yang sama (`next` hanya diterima jika path internal diawali `/Booking_Pemancingan/public/`).
2. Form menampilkan nama & WhatsApp **readonly** dari akun, plus jumlah orang dan catatan.
3. Submit POST → **dalam satu transaksi DB**:
   - Cek lagi slot masih kosong (booking status ≠ `dibatalkan`).
   - Hitung `total_harga = jumlah_orang × harga_per_slot`.
   - Batas 10 booking aktif per `user_id`.
   - Insert `bookings` dengan kode unik `BK-YYYYMMDD-XXX`, status `menunggu_pembayaran`.
   - Jika slot bentrok → tolak dengan pesan "Slot ini sudah dipesan. Pilih jam lain."
4. Halaman sukses: kode booking (mono) + satu tombol **"Notifikasi pemilik lewat WhatsApp"** (link `wa.me` ke nomor pemilik; isi: kode, kolam, tanggal, jam, total).

### Alur Pembayaran DP/Lunas (FR-10, FR-11)
1. Pemancing memilih jumlah bayar (**DP 50%** atau **Lunas 100%**) lalu transfer manual ke rekening pemilik → upload bukti di `riwayat.php`. Nominal **dihitung server** dari `total_harga` booking (tidak diterima dari POST) → insert `payments` (status `menunggu`).
2. Pemilik buka `owner/pembayaran.php` → lihat bukti → **Terima** → `payments.status = diterima` + `bookings.status` → `dikonfirmasi` (DP) / `lunas` (pelunasan).
3. Pemilik bisa **Konfirmasi / Selesai / Tolak / Batal** booking → transisi status berikut:
   `menunggu_pembayaran` → `dikonfirmasi` → `selesai` / `dibatalkan`.
4. Booking `dibatalkan` → slot otomatis kembali kosong untuk user lain.

### Alur Dashboard & Pendapatan (FR-13, FR-14)
1. Pemilik login → `owner/dashboard.php` → query booking semua kolam miliknya (join kolam + user).
2. Filter tanggal/status → tabel booking + aksi dropdown (POST aksi per baris).
3. Ringkasan: `SUM(nominal)` dari `payments` (status `diterima`) per hari/minggu + jumlah booking & batal.

### Business Rules (dari PRD)
- Password minimal 8 karakter; tidak ada pemblokiran akun atas percobaan login gagal pada V1 (brute-force protection disiapkan saat deployment publik).
- Pemancing hanya lihat booking miliknya; pemilik hanya kelola kolam miliknya (RBAC).
- Slot yang sudah dipesan booking aktif tidak bisa dipilih lagi (dicek saat render & saat submit, atomic).
- Harga kolam wajib > 0; kolam nonaktif tidak tampil di katalog.
- Booking dibatalkan → slot kembali kosong; pembatalan tidak dihitung pendapatan.
- Status pembayaran hanya diubah oleh pemilik kolam terkait.
- Upload bukti bayar: jpg/png, maks **5MB**; nominal = 50% (DP) atau 100% (Lunas) dari total tagihan, dihitung di server.
- Satu booking = satu kolam + satu slot.
- Semua pesan WhatsApp wajib memuat kode booking + jumlah tagihan.

---

## 5. Keamanan, Performa, & Deployment

### Keamanan
- **Password:** `password_hash()` bcrypt, tidak pernah simpan plain text; minimal 8 karakter.

- **Sesi:** `session_start()` dengan cookie `httponly` + `samesite=Lax`; `session_regenerate_id()` setelah login; logout hapus sesi total.
- **SQL injection:** semua query via PDO prepared statement (tidak ada string concatenation).
- **XSS:** escape output dengan `htmlspecialchars()` saat render.
- **CSRF:** token di setiap form POST (booking, upload, aksi pemilik).
- **RBAC:** `includes/auth.php` cek role per halaman — pemancing tidak bisa akses `/owner/*`, sebaliknya.
- **Upload bukti bayar:** hanya jpg/png, maks **5MB**, nama file diganti (random) + cek ekstensi & MIME via `finfo`.
- **File storage:** folder `uploads/` tidak boleh mengeksekusi PHP (`.htaccess` disable handler).
- **Rate limit sederhana:** booking dibatasi maksimal 10 booking aktif per akun untuk cegah spam.
- **Brute-force login:** tidak ada penguncian akun di level aplikasi pada V1 — pasang `fail2ban` / rate-limit di Apache atau nginx bila dideploy ke server publik.
- **HTTPS:** wajib saat deploy produksi; lokal cukup HTTP.

### Performa
- **Index DB:** `bookings(kolam_id, slot_id, tanggal)`, `slots(kolam_id, tanggal)`, `kode_booking` UNIQUE → query ketersediaan & riwayat cepat.
- **Halaman ketersediaan** dimuat via fetch JSON per tanggal (tidak render ulang seluruh halaman).
- **Gambar kolam:** kompresi sebelum simpan (maks lebar ~1200px), lazy loading `<img loading="lazy">`.
- **Target:** muat halaman < 2 detik, API JSON < 500ms, akurasi slot saat 100 concurrent user (dijamin atomic check + unique index).
- **Tanpa cache layer** untuk V1 (skala 100 booking/hari tidak butuh Redis).

### Deployment
- **Lokal (dev):** XAMPP — Apache + MariaDB aktif, import `database/kolamkita.sql` via phpMyAdmin, taruh project di `C:\xampp\htdocs\Booking_Pemancingan`.
- **Presentasi/demo:** XAMPP yang sama, bisa dibuka dari LAN (IP lokal) untuk demo.
- **Produksi (nanti):** shared hosting murah — upload folder via FileZilla, buat DB di cPanel, set kredensial di `config/database.php`, arahkan domain.
- **Backup:** export DB berkala via phpMyAdmin (`kolamkita_backup.sql`).

### Development Setup
```bash
# 1. Pastikan Apache + MySQL menyala di XAMPP Control Panel
# 2. Import schema
#    Buka http://localhost/phpmyadmin → Import → pilih database/kolamkita.sql
# 3. Cek config DB
#    config/database.php → host: localhost, user: root, pass: (kosong), db: kolamkita
# 4. Buka aplikasi
#    http://localhost/Booking_Pemancingan/public/index.php
```
- Tidak ada `npm install` / build step — cukup edit file, refresh browser.
- Seed data: 1 pemilik + 2 pemancing + 2 kolam + slot contoh 7 hari ke depan.
