# Design: KolamKita — Web Booking Pemancingan

- **Versi:** 1.0
- **Tanggal:** 08 Oktober 2026
- **Dependensi:** `.agents/1-PRD.md`, `.agents/2-TECH-SPEC.md`, `.agents/3-TASKS.md`
- **Acuan implementasi:** T-03 (design system CSS), T-06 – T-20 (halaman)

---

## 1. Subject & Arah

**Subjek:** KolamKita — web booking kolam pemancingan harian/galatama di Indonesia.
**Audiens:** pemancing akhir pekan (HP-first, buka dari chat WhatsApp) + pemilik kolam usia ~40-an (pemula teknis).
**Satu tugas halaman:** lihat slot kosong hari ini dan booking dalam < 2 menit.

**Register copy:** bahasa Indonesia, verb pendek, sentence case, tanpa jargon sistem.
- Navigasi & aksi: "Cek slot", "Pilih jam", "Booking sekarang", "Kirim bukti bayar", "Terima pembayaran".
- Orang mengontrol "bukti bayar", bukan "upload file"; sistem tidak meminta maaf dan tidak error vague — "Foto bukti lebih dari 5MB. Kecilkan dulu lalu coba lagi."
- Halaman kosong = ajakan bertindak: "Belum ada booking. Cari kolam untuk mulai."

---

## 2. Palet — "fajar di kolam"

Bukan cream/terracotta, bukan near-black + acid green. Warna berasal dari air kolam pagi hari.

| Nama | Hex | Peran |
|------|-----|-------|
| Kolam Dalam | `#072B27` | hero, footer, teks di atas terang, sidebar owner |
| Air Siang | `#116E5E` | primary — link, aksi sekunder, status konfirmasi |
| Kabut Pagi | `#E9F0EB` | background konten (dingin, bukan cream) |
| Ujung Pelampung | `#D9401F` | **satu-satunya aksen** — CTA utama, status float, focus ring |
| Busa | `#FFFFFF` | kartu, permukaan form |
| Tinta | `#13211E` | teks utama |

Aturan aksen: merah `#D9401F` hanya untuk CTA utama, ujung pelampung, dan focus ring — tidak untuk dekorasi.

Badge status (dipakai di PRD/Tech Spec):
- `menunggu_pembayaran` → kuning `#B7791F` di background `#FDF6E3`
- `dikonfirmasi` → Air Siang `#116E5E` di `#E3F0EC`
- `lunas` / `selesai` → hijau `#2F855A` di `#E6F4EA`
- `dibatalkan` → merah `#D9401F` di `#FBEAE6`

---

## 3. Tipografi

| Peran | Font | Penggunaan |
|-------|------|------------|
| Display | **Bricolage Grotesque** 800 | headline hero, judul halaman; sedikit condensed & aneh — bukan serif default |
| Body | **Plus Jakarta Sans** 400/500/600 | teks, nav, form, tombol — sans rancangan Indonesia, kontekstual |
| Utility/data | **IBM Plex Mono** 500 | kode booking `BK-20261008-001`, jam slot, harga rupiah, tabel owner → rasa karcis/scoreboard |

Load via Google Fonts:
```
https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap
```

Skala type (rem, mobile-first):
- Display hero: `clamp(2.5rem, 8vw, 4.5rem)`, line-height 1.05, letter-spacing -0.02em
- H2 judul bagian: `clamp(1.5rem, 4vw, 2rem)`, weight 800
- Body: `1rem` / 1.6
- Small/label: `0.8125rem`, weight 600, uppercase hanya untuk label kecil (eyebrow), letter-spacing 0.06em
- Data mono: `0.9375rem`

---

## 4. Layout & Wireframe

### Landing / Introduction (`index.php`)
```
[KOLAMKITA   Kolam · Riwayat · Masuk]
~~~~~~~~~~~~~~~~~~~~~~~ Kolam Dalam ~~~~~~~~~~~~~~~~~~~~~~~
 BOOKING KOLAM PEMANCINGAN            ← eyebrow, mono, muted
 Cek slot. Datang. Mancing.                   ← display 800
 Cek kolam mana yang masih kosong hari ini…  ← lead
 [Lihat kolam terdekat] [Daftar akun]         ← hero-actions, 2 CTA
  o—o—◉—o—o—o—o     ← baris pelampung (sengaja 1 penuh, 1 ditutup)
  3 kolam aktif · 34 slot kosong hari ini     ← mono, angka nyata dari DB
────────────── Kabut Pagi ──────────────
 TIGA LANGKAH, DUA MENIT
 [01 Cari kolam & cek ikan] [02 Pilih jam kosong] [03 Booking & kirim bukti]
      ↑ nomor mono merah — sah karena isinya urutan proses
────────────── Kabut Pagi ──────────────
 YANG BIKIN BEDA DARI CHAT BIASA
 [icon] [icon] [icon] [icon]   ← feature-card, grid 4→2→1
~~~~~~~~~~ Kolam Dalam (cta-band) ~~~~~~~~~~
 Mulai mancing hari ini  [Daftar akun] [Lihat semua kolam]
────────────── Kabut Pagi ──────────────
 KOLAM YANG BISA KAMU COBA
  Surya Gemilang · Cikarang · Rp75.000/orang      Lihat slot →
```

### Katalog (`semua-kolam.php`)
Halaman terpisah dari landing: `page-head` (judul + search bar terang + jumlah hasil) lalu grid card + section "Rekomendasi untuk ikan: X" bila filter ikan dipakai. Tidak ada hero — supaya beda jelas dari landing.

### Komponen landing (baru)
- `.hero-actions` — baris CTA, wrap; mobile tetap satu kolom penuh.
- `.btn-ghost` — outline putih transparan untuk CTA sekunder di hero/cta-band gelap.
- `.step` / `.step-num` — kartu langkah; nomor mono merah `01/02/03` (numbered markers sah karena urutan proses).
- `.feature-card` / `.feature-icon` — kartu fitur; ikon SVG stroke `currentColor`, 40px, bg `#E3F0EC`.
- `.cta-band` — pita gelap `Kolam Dalam`, `padding-block: 3.5rem`.
- `.preview-list` / `.preview-row` — daftar ringkas kolam preview; 2 kolom di mobile, 4 kolom ≥768px; harga mono.
- `.page-head` — kepala halaman katalog dengan search bar terang di atas `Kabut Pagi`.

### Detail kolam (`kolam.php`)
- Foto besar + info (deskripsi, fasilitas, alamat, harga mono).
- Input tanggal → **grid pilih jam** sebagai float (state: kosong/penuh/tutup + legend).
- Ringkasan slot terpilih (sticky bottom di mobile) → total harga → "Booking sekarang".

### Booking (`booking.php`)
- Ringkasan gaya **karcis** (kode mono, sisi sobek pakai dashed border) — subordinat dari signature float, bukan elemen kedua yang menyaingi.
- Form: jumlah orang, nama, no. WhatsApp, catatan; total otomatis.
- Halaman sukses: kode booking mono besar + dua tombol WhatsApp.

### Halaman pemancing
- `riwayat.php`: tabel/card list + badge status + form kirim bukti bayar.

### Dashboard pemilik (`owner/*`)
- Shell: sidebar/topnav **Kolam Dalam** (gelap) + konten Kabut Pagi.
- Tabel rapat, mono untuk angka & kode, badge status dari palet, aksi via dropdown.
- Kartu ringkasan pendapatan: angka mono besar + label kecil.

### Grid & breakpoint
- Mobile-first; konten utama max-width `72rem`.
- **Gutter wajib:** semua "kulit" halaman memakai token `--gutter` — `1.25rem` (20px) di mobile, `2rem` di ≥768px, dengan turunan `--pad-l`/`--pad-r` (= `max(gutter, safe-area-inset)`) untuk layar ber-notch. Diterapkan ke `.container`, `.form-card` (margin kiri/kanan), `.owner-side`, `.owner-main`. Tidak boleh ada konten yang menyentuh tepi layar.
- **Perhatikan cascade:** untuk elemen yang memakai `.container` **sekaligus** `.section`, aturan vertikal wajib pakai `padding-block` — kalau memakai shorthand `padding: X 0`, padding horizontal `.container` akan tertimpa dan konten naik ke tepi layar.
- **Verifikasi:** ukur dengan probe 360px (iframe same-origin) — target `h1`/kartu mulai di `left=20` dan `scrollWidth == clientWidth` (nol overflow). L Morales: headless Edge mengabaikan `--window-size`, jadi jangan percaya screenshot langsung; lewat iframe atau probe terukur.
- Ritme vertikal mobile (≤640px): hero `3rem 0 3.5rem`, section `2.25rem 0`, form-card `margin-top 1.5rem`, judul hero clamp min `2.25rem`.
- Kolam: 1 kolom < 640px, 2 kolom ≥ 640px, 3 kolom ≥ 1024px.
- Grid jam: min `5rem` per cell, wrap; target tap ≥ 44px; `gap` `1rem` di mobile.

---

## 5. Signature — "Pelampung Slot"

**Slot jam digambar sebagai pelampung (bobber) di atas garis air.**

| State | Visual |
|-------|--------|
| Kosong (booking bisa) | float putih berujung merah `#D9401F`, **di atas** garis air |
| Penuh (sudah dipesan) | float abu `#5F726B`, **tenggelam di bawah** garis air, tidak bisa diklik |
| Dibuat tutup pemilik | garis kosong (hanya tick), abu muda |

- Murni dari dunia subjek (pelampung mancing), membawa informasi nyata (ketersediaan).
- Dipakai konsisten di tiga tempat: hero (pratinjau live), grid pilih jam (`kolam.php`), titik status di kartu kolam.
- Implementasi CSS: pseudo-element lingkaran + translate Y untuk state tenggelam; label jam di mono.

---

## 6. Motion

Satu orkestrasi kecil, bukan efek di mana-mana:
1. **Load hero:** float baris ber-`bobbing` halus (`translateY` ±4px, 2s ease-in-out infinite, delay berurutan) — hanya di hero.
2. **Pilih jam:** transisi float naik/turun 200ms saat state berubah (kosong ↔ dipilih).
3. **Hover kartu kolam:** ripple ring halus (box-shadow scale), tanpa zoom foto.
- Semua dimatikan di `prefers-reduced-motion: reduce`.
- Tidak ada scroll-reveal; halaman terbaca penuh saat dimuat.

---

## 7. Kualitas Floor (wajib)

- Focus ring terlihat: `outline: 2px solid #D9401F; outline-offset: 2px`.
- Kontras teks lolos WCAG AA (Tinta di Kabut Pagi; Busa di Kolam Dalam).
- Target tap ≥ 44px untuk tombol, float jam, dan aksi tabel.
- Nyaman di 360px; tabel owner (riwayat, kolam, pembayaran, dashboard) dibungkus `.table-wrap` sehingga bisa di-scroll horizontal tanpa membuat halaman melebar. Konversi tabel → card list di mobile postponed ke v2.
- Ikon: SVG inline sederhana (mailto, wa, chevron) — tanpa icon font berat.

---

## 8. Self-Critique vs Default AI

- Cream + serif high-contrast + terracotta → **diganti**: kabut dingin `#E9F0EB` + grotesk aneh + merah pelampung.
- Near-black + acid green → ditolak; hero gelap di sini berasal dari air kolam fajar, bukan tren.
- Numbered markers `01/02/03` → **tidak dipakai** — daftar kolam bukan urutan proses.
- Risiko palet "hijau" terkesan eco-template → dikunci aksen merah pelampung yang hanya muncul di satu peran (CTA, ujung float, focus ring).
- Chanel rule: elemen dekoratif dipangkas — tidak ada badge angka, tidak ada gradien dekoratif, tidak ada ilustrasi stok.

---

## 10. Komponen Tambahan: Chip Ikan

- Dipakai di: card kolam (index.php), halaman detail kolam (kolam.php), sesuai hasil pencarian ikan.
- Gaya: `background: Air Siang `#116E5E`@10%` → gunakan `#E3F0EC`, `color: #116E5E`, padding `.15rem .6rem`, radius `999px`, font `Plus Jakarta Sans 500 .78rem`.
- Ukuran chip kecil; tampil inline, wrap, jarak `.35rem` horizontal.
- Teks: nama ikan saja (mis. "Mas", "Nila") — sumber dari kolom `jenis_ikan`, dipisah koma lalu tiap nama jadi satu chip.

---

## 9. Aset

- **Font:** Google Fonts (Bricolage Grotesque, Plus Jakarta Sans, IBM Plex Mono).
- **Foto kolam:** seed data pakai placeholder lokal `public/assets/img/kolam-1.jpg ... kolam-2.jpg` (bisa diganti foto asli).
- **Ikon:** SVG inline.
- **Tidak ada:** icon library, animation library, CSS framework eksternal (Tailwind opsional via CDN, default pakai CSS custom murni sesuai T-03).
