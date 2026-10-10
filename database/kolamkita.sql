-- KolamKita schema + seed data (XAMPP / MariaDB)
CREATE DATABASE IF NOT EXISTS kolamkita CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE kolamkita;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS slots;
DROP TABLE IF EXISTS kolam;
DROP TABLE IF EXISTS users;

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
  FOREIGN KEY (owner_id) REFERENCES users(id),
  INDEX idx_owner (owner_id),
  INDEX idx_aktif (is_aktif)
);

CREATE TABLE slots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kolam_id INT NOT NULL,
  tanggal DATE NOT NULL,
  jam TIME NOT NULL,
  status ENUM('buka','tutup') DEFAULT 'buka',
  FOREIGN KEY (kolam_id) REFERENCES kolam(id),
  UNIQUE KEY uniq_slot (kolam_id, tanggal, jam),
  INDEX idx_slot (kolam_id, tanggal)
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
  INDEX idx_user (user_id),
  INDEX idx_kolam_slot_tgl (kolam_id, slot_id, tanggal),
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

-- Seed users (password semua: password123)
INSERT INTO users (nama, email, password_hash, no_wa, role) VALUES
('Bu Sari', 'sari@kolamkita.id', '$2y$10$gEsY4BWTp5DNirfjdJrZaeZ.03mssFmZ/SeoAySMMTitxMuiNGC/m', '081234567890', 'pemilik'),
('Rian', 'rian@example.com', '$2y$10$gEsY4BWTp5DNirfjdJrZaeZ.03mssFmZ/SeoAySMMTitxMuiNGC/m', '081111111111', 'pemancing'),
('Ayu', 'ayu@example.com', '$2y$10$gEsY4BWTp5DNirfjdJrZaeZ.03mssFmZ/SeoAySMMTitxMuiNGC/m', '082222222222', 'pemancing');

-- Seed kolam
INSERT INTO kolam (owner_id, nama, deskripsi, alamat, harga_per_slot, jam_buka, jam_tutup, jenis_ikan) VALUES
(1, 'Kolam Surya Gemilang', 'Kolam harian ikan mas & nila, fasilitas lengkap, mushola, kantin.', 'Cipayung, Jakarta Timur', 75000, '06:00', '17:00', 'Mas, Nila, Patin'),
(1, 'Kolam Danau Biru', 'Kolam harian & galatama, view danau, parkir luas.', 'Depok, Jawa Barat', 65000, '06:00', '18:00', 'Lele, Mas, Gurame'),
(1, 'Pemancingan Pak Hadi', 'Kolam sederhana, cocok pemula, siap galatama tiap Sabtu.', 'Bekasi, Jawa Barat', 50000, '06:00', '17:00', 'Nila, Lele');

-- Seed slots: 7 hari ke depan, jam 06:00-17:00 untuk kolam 1 & 2, 06:00-16:00 untuk kolam 3
INSERT INTO slots (kolam_id, tanggal, jam)
SELECT 1, DATE_ADD(CURDATE(), INTERVAL d DAY), TIME(CONCAT(h, ':00:00'))
FROM (SELECT 0 d UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) dd
CROSS JOIN (SELECT 6 h UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17) hh;

INSERT INTO slots (kolam_id, tanggal, jam)
SELECT 2, DATE_ADD(CURDATE(), INTERVAL d DAY), TIME(CONCAT(h, ':00:00'))
FROM (SELECT 0 d UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) dd
CROSS JOIN (SELECT 6 h UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17) hh;

INSERT INTO slots (kolam_id, tanggal, jam, status)
SELECT 3, DATE_ADD(CURDATE(), INTERVAL d DAY), TIME(CONCAT(h, ':00:00')), IF(h=13,'tutup','buka')
FROM (SELECT 0 d UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6) dd
CROSS JOIN (SELECT 6 h UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16) hh;
