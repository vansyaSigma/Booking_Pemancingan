# PRD: KolamKita — Web Booking Pemancingan

- **Versi:** 1.0
- **Tanggal:** 08 Oktober 2026
- **Target:** Vibe Coding → Tech Spec → Implementasi

---

## 1. Visi & Tujuan Produk

### Visi Produk
**"KolamKita"** — platform web booking kolam pemancingan yang menggantikan chat dan telepon berantakan. Pemancing dapat melihat kolam, slot jadwal, harga, dan fasilitas secara langsung, lalu booking lewat form singkat; pemilik kolam mendapat dashboard untuk mengatur jadwal, ketersediaan, dan pembayaran dalam satu tempat. Semua konfirmasi booking diteruskan otomatis ke WhatsApp pemancing dan pemilik, sehingga transaksi tetap tercatat rapi tanpa aplikasi tambahan.

### Tujuan Utama
1. **Hilangkan double booking** — slot yang sudah dipesan otomatis terkunci di sistem; indikator: 0 kasus booking bentrok setelah 3 bulan berjalan.
2. **Pemancing bisa cek ketersediaan sendiri** — tanpa chat tanya "kosong gak?"; indikator: 70% booking masuk lewat form web, bukan WhatsApp manual.
3. **Pencatatan & pembayaran terpusat** — pemilik tidak lagi catat di buku; indikator: 100% transaksi tercatat di dashboard, status pembayaran jelas (DP/lunas/batal).
4. **Booking selesai < 2 menit** — dari pilih kolam sampai konfirmasi WhatsApp; indikator: rata-rata waktu isi form < 120 detik.

### Value Proposition
- **Booking simpel tanpa drama:** form singkat + konfirmasi WhatsApp instan — tidak perlu install aplikasi, tidak perlu chat bolak-balik.
- **Jadwal selalu akurat:** ketersediaan slot real-time per kolam & per jam, pemilik update sekali, langsung terlihat semua orang.
- **Pemilik kolam dapat kontrol penuh:** dashboard kelola kolam, slot, harga, pembayaran, dan riwayat booking — bisnis jadi tercatat dan rapi.

---

## 2. User Persona

### Persona 1: Rian (Pemancing)
- **Usia/Pekerjaan:** 28 tahun, karyawan swasta (Jakarta)
- **Level Teknis:** Menengah — pakai WhatsApp, Instagram, aplikasi e-commerce harian
- **Tujuan:** Cari kolam pemancingan terdekat yang kosong di akhir pekan, booking slot pagi tanpa ribet, tahu harga pasti sebelum berangkat.
- **Pain Points:** Chat pemilik kolam dibalas lambat; sering datang ternyata slot penuh; harga tidak jelas; DP transfer tanpa bukti catatan yang rapi.
- **Motivasi:** Semua bisa dicek & dibooking sendiri dari HP dalam 2 menit, ada konfirmasi WhatsApp resmi sebagai bukti booking.

### Persona 2: Bu Sari (Pemilik Kolam)
- **Usia/Pekerjaan:** 45 tahun, pengelola kolam pemancingan harian & galatama
- **Level Teknis:** Pemula — lancar WhatsApp, kuatir aplikasi rumit
- **Tujuan:** Kelola jadwal & ketersediaan slot, hindari double booking, catat pembayaran (DP/lunas) tanpa buku tulis, tahu pendapatan harian/mingguan.
- **Pain Points:** Chat booking menumpuk di WhatsApp dan sering ketuker; catatan buku tulis hilang/kotor; lupa slot yang sudah dipesan; susah hitung pendapatan per minggu.
- **Motivasi:** Dashboard simpel yang bisa diakses dari HP/komputer — satu tempat untuk semua jadwal, booking, dan uang masuk.

---

## 3. User Stories

Format: `Sebagai [peran], saya ingin [tindakan], agar [manfaat].`

### Modul 1: Autentikasi
1. Sebagai pemancing baru, saya ingin mendaftar dengan email & no. WhatsApp, agar bisa membuat booking di web.
2. Sebagai pemancing, saya ingin login, agar booking saya terhubung ke akun dan bisa dilacak riwayatnya.
3. Sebagai pemilik kolam, saya ingin login ke dashboard, agar bisa mengelola kolam dan jadwal milik saya.

### Modul 2: Pencarian & Ketersediaan
4. Sebagai pemancing, saya ingin melihat daftar kolam lengkap dengan harga, fasilitas, dan lokasi, agar bisa memilih kolam yang sesuai.
5. Sebagai pemancing, saya ingin melihat slot jadwal yang masih kosong per tanggal & jam, agar tidak memesan slot yang sudah penuh.
6. Sebagai pemancing, saya ingin mencari/filter kolam (harga, area, jenis kolam), agar menemukan pilihan lebih cepat.

### Modul 3: Booking
7. Sebagai pemancing, saya ingin mengisi form booking (kolam, tanggal, slot jam, jumlah orang), agar pesanan saya tercatat di sistem.
8. Sebagai pemancing, saya ingin menerima konfirmasi via WhatsApp setelah booking dibuat, agar punya bukti booking resmi.
9. Sebagai pemancing, saya ingin melihat status pembayaran (DP/lunas/batal), agar tahu booking saya sudah diproses atau belum.
10. Sebagai pemancing, saya ingin melihat riwayat booking saya, agar bisa memantau booking aktif dan yang lampau.

### Modul 4: Pengelolaan (Pemilik Kolam)
11. Sebagai pemilik kolam, saya ingin menambah/mengedit data kolam (harga, fasilitas, jam operasional), agar info yang tampil akurat.
12. Sebagai pemilik kolam, saya ingin mengatur slot jadwal & ketersediaan per tanggal, agar sistem tahu kapan kolam penuh/kosong.
13. Sebagai pemilik kolam, saya ingin melihat daftar booking masuk beserta statusnya, agar bisa konfirmasi tanpa cek chat satu-satu.
14. Sebagai pemilik kolam, saya ingin menandai status pembayaran booking, agar catatan keuangan rapi dan tahu siapa yang belum bayar.
15. Sebagai pemilik kolam, saya ingin melihat ringkasan pendapatan harian/mingguan, agar tahu performa bisnis tanpa hitung manual.

---

## 4. Functional Requirements

### Modul 1: Autentikasi

**FR-01: Registrasi Pengguna**
- **Input:** Email, password, nama, no. WhatsApp
- **Proses:** Validasi email unik & format, hash password, simpan ke DB
- **Output:** Akun terdaftar, redirect ke login
- **Aturan:** Password minimal 8 karakter; no. WhatsApp wajib format internasional/lokal valid

**FR-02: Login Pengguna**
- **Input:** Email, password
- **Proses:** Verifikasi kredensial, generate sesi/token
- **Output:** Sesi aktif, redirect ke halaman sesuai peran (dashboard pemilik / beranda pemancing)
- **Aturan:** Password minimal 8 karakter; tidak ada pemblokiran akun atas percobaan login gagal pada V1 (rate-limit breathable)

**FR-03: Hak Akses Peran (RBAC)**
- **Input:** Sesi pengguna
- **Proses:** Cek peran (pemancing / pemilik) saat akses halaman & API
- **Output:** Akses diizinkan atau halaman 403
- **Aturan:** Pemancing tidak boleh akses fitur pemilik, dan sebaliknya

### Modul 2: Katalog & Ketersediaan

**FR-04: Manajemen Data Kolam**
- **Input:** Nama kolam, deskripsi, alamat, harga per slot/jam, fasilitas, foto, jam operasional, **jenis ikan (Mas, Nila, Patin, ...)**
- **Proses:** CRUD kolam oleh pemilik
- **Output:** Kolam tampil di katalog publik
- **Aturan:** Harga wajib > 0; kolam nonaktif tidak muncul di pencarian; jenis ikan wajib diisi (dipisah koma)

**FR-05a: Pencarian Berdasarkan Ikan**
- **Input:** Nama ikan (teks bebas, mis. "Mas")
- **Proses:** Filter kolam yang jenis ikannya mengandung kata tersebut
- **Output:** Rekomendasi kolam "Kolam untuk ikan X"
- **Aturan:** Hanya kolam aktif yang ditampilkan; pencarian tidak peka huruf besar/kecil

**FR-05: Pencarian & Filter Kolam (halaman katalog `semua-kolam.php`)**
- **Input:** Kata kunci, rentang harga, area/jenis kolam, jenis ikan
- **Proses:** Query ke DB dengan filter
- **Output:** Daftar kolam yang cocok beserta harga & badge ikan
- **Aturan:** Hanya kolam aktif yang ditampilkan

**FR-06: Melihat Ketersediaan Slot**
- **Input:** ID kolam, tanggal
- **Proses:** Ambil slot jadwal kolam, cek relasi booking aktif
- **Output:** Daftar slot per jam dengan status: kosong / penuh / ditutup
- **Aturan:** Slot sudah dipesan (belum batal) tidak boleh dipilih lagi

**FR-07: Manajemen Slot Jadwal Pemilik**
- **Input:** Tanggal, rentang jam, kapasitas kolam
- **Proses:** Buka/tutup slot per tanggal
- **Output:** Slot baru langsung terlihat di halaman ketersediaan
- **Aturan:** Slot yang sudah punya booking aktif tidak bisa ditutup paksa tanpa konfirmasi pembatalan

### Modul 3: Booking & Pembayaran

**FR-08: Pembuatan Booking**
- **Input:** ID kolam, tanggal, slot jam, jumlah orang, nama, no. WhatsApp, catatan
- **Proses:** Validasi slot masih kosong, hitung total harga, simpan booking dengan status `menunggu_pembayaran`
- **Output:** Kode booking unik (mis. `BK-20261008-001`), ringkasan booking, tombol notifikasi WhatsApp ke pemilik
- **Aturan:** Pemancing **wajib punya akun** untuk booking (login ringan via email + password); booking ganda pada slot & tanggal yang sama harus ditolak (atomic check); batas 10 booking aktif per akun; nama & WhatsApp pemancing diambil dari akun (form readonly)

**FR-09: Konfirmasi via WhatsApp**
- **Input:** Data booking, no. WhatsApp pemilik kolam
- **Proses:** Generate link WhatsApp berisi template pesan (kode booking, kolam, tanggal, jam, total)
- **Output:** Tombol "Notifikasi pemilik lewat WhatsApp" menuju chat pemilik
- **Aturan:** Satu tombol notifikasi ke pemilik (V1 tidak ada tombol konfirmasi ke WA pemancing); pesan wajib memuat kode booking & jumlah tagihan

**FR-10: Pembayaran DP / Pelunasan**
- **Input:** Bukti pembayaran (upload foto), jenis pembayaran (**DP 50%** atau **Lunas 100%**)
- **Proses:** Nominal dihitung server dari total tagihan (DP = 50% × total, Lunas = 100% × total), simpan data pembayaran, perbarui status booking
- **Output:** Status booking berubah → `menunggu_konfirmasi` / `lunas`
- **Aturan:** Status hanya boleh diubah oleh pemilik kolam pemilik booking tersebut; file bukti jpg/png maks 5MB; nominal tidak diterima dari form (anti manipulasi), pilihan di luar DP/Lunas ditolak

**FR-11: Status Booking**
- **Input:** Aksi pemilik (konfirmasi / tolak / selesai) atau pembatalan
- **Proses:** Transisi status: `menunggu_pembayaran` → `dikonfirmasi` → `selesai` / `dibatalkan`
- **Output:** Status terbaru tampil di dashboard pemilik & riwayat pemancing
- **Aturan:** Booking dibatalkan → slot otomatis kembali kosong

**FR-12: Riwayat Booking Pemancing**
- **Input:** Sesi pengguna
- **Proses:** Query booking milik user, urutkan terbaru
- **Output:** Daftar booking (aktif & lampau) + detail status & pembayaran
- **Aturan:** User hanya melihat booking miliknya sendiri

### Modul 4: Dashboard Pemilik & Laporan

**FR-13: Daftar Booking Masuk**
- **Input:** Filter tanggal / status
- **Proses:** Query booking untuk semua kolam milik pemilik
- **Output:** Tabel booking + aksi konfirmasi, tandai bayar, selesai
- **Aturan:** Pemilik hanya melihat booking kolam miliknya

**FR-14: Ringkasan Pendapatan**
- **Input:** Rentang tanggal (harian/mingguan)
- **Proses:** Agregasi nominal pembayaran (DP + lunas) per periode
- **Output:** Total pendapatan, jumlah booking, jumlah batal
- **Aturan:** Booking batal tidak dihitung sebagai pendapatan

**FR-15: Notifikasi Booking Baru (WhatsApp)**
- **Input:** Booking baru terbuat
- **Proses:** Generate pesan WhatsApp ke pemilik kolam
- **Output:** Link chat WhatsApp pemilik berisi detail booking masuk
- **Aturan:** Dikirim setiap booking baru dibuat

**FR-16: Halaman Introduction (landing `index.php`)**
- **Input:** Data ringkas dari DB (jumlah kolam aktif, jumlah slot kosong hari ini, 3 kolam aktif untuk preview)
- **Proses:** Tampilkan hero (headline + 2 CTA + baris pelampung), 3 langkah cara kerja, 4 fitur produk, CTA daftar, dan preview kolam
- **Output:** Halaman pengenalan yang memperkenalkan produk sebelum masuk ke katalog
- **Aturan:** Tidak memuat section untuk pemilik kolam (registrasi pemilik mandiri di luar scope V1); CTA mengarah ke `semua-kolam.php`, `register.php`, atau dashboard/riwayat bila sudah login

---

## 5. Non-Functional Requirements

### Performa
- Waktu muat halaman < 2 detik (jaringan 4G)
- API response < 500ms untuk request normal
- Halaman ketersediaan slot tetap akurat < 1 detik saat 100 user akses bersamaan
- Booking harus atomic — tidak ada slot terjual ganda saat request bersamaan

### Keamanan
- Password di-hash (bcrypt/argon2), tidak pernah disimpan plain text
- HTTPS wajib di seluruh halaman
- Validasi input server-side (form booking, upload bukti bayar)
- Proteksi SQL injection & XSS (prepared statement, escape output)
- Role-based access control (pemancing vs pemilik) di setiap endpoint
- Upload bukti pembayaran: batasi ukuran & tipe file (jpg/png maks 5MB)
- Tidak ada penguncian akun berbasis percobaan login gagal pada V1; rate-limit (fail2ban/nginx) disiapkan bila dideploy publik

### Skalabilitas
- Target awal: 50 kolam, 1.000 pengguna terdaftar, 100 booking/hari
- Desain DB relasional yang rapi agar mudah tambah kolam baru tanpa ubah kode
- Dapat di-deploy di shared hosting/VPS murah (kebutuhan kelas & UMKM)
- Siap naik ke VPS/cloud dengan lebih banyak RAM bila trafik naik

### Usability
- Responsive: mobile, tablet, desktop (mobile-first — pemancing mayoritas dari HP)
- Bahasa Indonesia
- Alur booking maksimal 3 langkah: pilih kolam & slot → isi data → konfirmasi WhatsApp
- Booking wajib login agar riwayat tercatat; pemancing yang belum login diarahkan ke login lalu kembali ke form booking yang sama
- Halaman utama dibuka maksimal 2 klik dari URL

### Kompatibilitas
- Browser modern: Chrome, Safari (iOS), Chrome Android, Edge — versi 2 tahun terakhir
- Konfirmasi WhatsApp berfungsi di WhatsApp Web & WhatsApp mobile

### Maintainability
- Kode terstruktur (pemisahan halaman/logic/DB layer) agar mudah diperbaiki
- Komentar cukup untuk penjelasan modul inti
- Setup lokal mudah dijalankan (satu perintah install/run)

---

## 6. Out of Scope & Dependensi

### Out of Scope (Tidak Dikerjakan di V1)
- **Pembayaran online otomatis (payment gateway)** — ditunda ke v2; V1 pakai transfer manual + bukti upload
- **Marketplace multi-pemilik (registrasi pemilik mandiri)** — ditunda ke v2; V1 pemilik kolam dibuat/administrasi manual
- **Sistem rating & ulasan kolam** — ditunda ke v2
- **Aplikasi mobile native (Android/iOS)** — ditunda; V1 berbasis web responsif
- **Notifikasi push/email otomatis** — ditunda; V1 konfirmasi via link WhatsApp
- **Fitur galatama/umpan poin, member VIP, referral** — di luar V1
- **Multi-kolam dalam satu booking** — V1 satu booking = satu kolam + satu slot

### Dependensi
- **WhatsApp** — konfirmasi & notifikasi via `wa.me` link (tanpa API berbayar)
- **Database** — MySQL/SQLite untuk data user, kolam, slot, booking, pembayaran
- **Stack web** — ditentukan di Tech Spec (sesuai perkuliahan PBW & kebutuhan deploy murah)
- **Upload storage** — penyimpanan lokal server untuk foto bukti pembayaran & foto kolam
- **Hosting** — shared hosting / VPS kecil / XAMPP untuk lokal & demo
- **Runtime PHP** — jika stack PHP (sesuai matakuliah PBW), butuh PHP + web server

### Asumsi
- Pemilik punya nomor WhatsApp aktif untuk menerima notifikasi booking
- Pemancing punya WhatsApp & email aktif
- Pembayaran DP/lunas ditransfer manual ke rekening pemilik kolam, bukti diunggah ke sistem
- Jam operasional kolam diatur per kolam oleh pemilik
- Satu slot jam = kapasitas booking (1 booking per slot, kecuali diatur lain)
- Ketersediaan real-time cukup dengan pengecekan saat submit booking (tanpa websocket)
