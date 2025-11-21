-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 07:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cashier`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `username`, `action`, `ip_address`, `timestamp`) VALUES
(1, 2, 'palihnges', 'Login berhasil', '::1', '2025-11-17 07:53:38'),
(2, 2, 'palihnges', 'Login berhasil', '::1', '2025-11-17 07:54:01'),
(3, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 07:55:30'),
(4, 2, 'palihnges', 'Login berhasil', '::1', '2025-11-17 07:58:49'),
(5, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 08:13:48'),
(6, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 08:13:54'),
(7, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 08:17:10'),
(8, 2, 'palihnges', 'Login berhasil', '::1', '2025-11-17 08:17:22'),
(9, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 08:20:05'),
(10, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 08:25:17'),
(11, 2, 'palihnges', 'Login berhasil', '::1', '2025-11-17 08:25:27'),
(12, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 09:44:01'),
(13, 1, 'admin', 'Login berhasil', '::1', '2025-11-17 09:44:39'),
(14, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 01:40:25'),
(15, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 05:01:35'),
(16, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 05:04:15'),
(17, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 05:05:37'),
(18, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 05:06:26'),
(19, 5, 'ngising', 'Akun dibuat: ngising (Role: Kasir)', NULL, '2025-11-18 11:14:44'),
(20, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 07:40:42'),
(21, 5, 'ngising', 'Login berhasil', '::1', '2025-11-18 08:25:45'),
(22, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 08:26:13'),
(23, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 10:16:36'),
(24, 5, 'ngising', 'Login berhasil', '::1', '2025-11-18 10:16:54'),
(25, 1, 'admin', 'Login berhasil', '::1', '2025-11-19 02:09:53'),
(26, 1, 'admin', 'Login berhasil', '::1', '2025-11-19 06:56:35'),
(27, 1, 'admin', 'Kas Masuk: Rp. 1.900.000 - wassapguys12', '::1', '2025-11-19 07:05:42'),
(28, 1, 'admin', 'Kas Masuk: Rp. 19.000.001 - wassapguys122', '::1', '2025-11-19 07:14:08'),
(29, 1, 'admin', 'Kas Masuk: Rp. 1.900.000.123 - wassapguys1221', '::1', '2025-11-19 07:22:30'),
(30, 1, 'admin', 'Login berhasil', '::1', '2025-11-19 10:11:23'),
(31, 5, 'ngising', 'Login berhasil', '::1', '2025-11-19 10:13:57'),
(32, 5, 'ngising', 'Login berhasil', '::1', '2025-11-20 01:39:16'),
(33, 1, 'admin', 'Login berhasil', '::1', '2025-11-20 01:39:29'),
(34, 1, 'admin', 'Kas Masuk: Rp. 1.999.022.134 - wassapmamen', '::1', '2025-11-20 02:01:15'),
(35, 1, 'admin', 'Hapus Kas Masuk #6', '::1', '2025-11-20 08:12:07'),
(36, 1, 'admin', 'Hapus Kas Masuk #5', '::1', '2025-11-20 08:12:11'),
(37, 1, 'admin', 'Hapus Kas Masuk #4', '::1', '2025-11-20 08:12:13'),
(38, 1, 'admin', 'Hapus Kas Masuk #3', '::1', '2025-11-20 08:12:15'),
(39, 1, 'admin', 'Hapus Kas Masuk #2', '::1', '2025-11-20 08:12:17'),
(40, 1, 'admin', 'Kas Masuk #001/KT-MSL/XI/2025: Rp. 19.000.001', '::1', '2025-11-20 08:12:38'),
(41, 1, 'admin', 'Hapus Kas Masuk #1', '::1', '2025-11-20 08:21:14'),
(42, 5, 'ngising', 'Login berhasil', '::1', '2025-11-20 08:23:55'),
(43, 1, 'admin', 'Login berhasil', '::1', '2025-11-20 08:24:15'),
(44, 1, 'admin', 'Kas Keluar #002/KK-MSL/XI/2025: Rp. 19.000.000', '::1', '2025-11-20 12:58:29'),
(45, 1, 'admin', 'Hapus Kas Masuk #7', '::1', '2025-11-20 13:34:06'),
(46, 1, 'admin', 'Kas Masuk #003/KT-MSL/XI/2025: Rp. 1.999.022.134', '::1', '2025-11-20 13:35:08'),
(47, 1, 'admin', 'Kas Masuk #004/KT-MSL/XI/2025: Rp. 19.000.001', '::1', '2025-11-20 13:35:32'),
(48, 1, 'admin', 'Hapus Kas Keluar #8', '::1', '2025-11-20 13:42:22'),
(49, 1, 'admin', 'Kas Keluar #001/KK-MSL/XI/2025: Rp. 1.900.000', '::1', '2025-11-20 13:42:26'),
(50, 1, 'admin', 'Kas Keluar #002/KK-MSL/XI/2025: Rp. 190.000', '::1', '2025-11-20 13:43:31'),
(51, 1, 'admin', 'Kas Keluar #003/KK-MSL/XI/2025: Rp. 190.000', '::1', '2025-11-20 13:45:56'),
(52, 1, 'admin', 'Login berhasil', '::1', '2025-11-21 08:23:22');

-- --------------------------------------------------------

--
-- Table structure for table `konfigurasi`
--

CREATE TABLE `konfigurasi` (
  `id` int(11) NOT NULL,
  `nama_perusahaan` varchar(200) DEFAULT 'PT. MITRA SAUDARA LESTARI - MSL',
  `alamat` text DEFAULT 'Jl. W.R Supratman No. 42 Pontianak, Kalimantan Barat 78122',
  `kota` varchar(100) DEFAULT 'Sungai Buluh',
  `telepon` varchar(20) DEFAULT '0778-123456',
  `email` varchar(100) DEFAULT 'info@msl.com',
  `ttd_jabatan_1` varchar(100) DEFAULT 'Finance Dept Head',
  `ttd_jabatan_2` varchar(100) DEFAULT 'Finance Sub Dept Head',
  `ttd_jabatan_3` varchar(100) DEFAULT 'Finance Div Head',
  `ttd_jabatan_4` varchar(100) DEFAULT 'Cashier',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konfigurasi`
--

INSERT INTO `konfigurasi` (`id`, `nama_perusahaan`, `alamat`, `kota`, `telepon`, `email`, `ttd_jabatan_1`, `ttd_jabatan_2`, `ttd_jabatan_3`, `ttd_jabatan_4`, `updated_at`) VALUES
(1, 'PT. Kalimantan Sawit Kusuma', 'Jl. W.R Supratman No. 42 Pontianak', 'Kota Pontianak', '0778-123456', 'info@ksk.com', 'Finance Dept Head', 'Finance Sub Dept Head', 'Finance Div Head', 'Cashier', '2025-11-18 08:27:32');

-- --------------------------------------------------------

--
-- Table structure for table `nomor_surat`
--

CREATE TABLE `nomor_surat` (
  `id` int(11) NOT NULL,
  `jenis_dokumen` varchar(20) NOT NULL DEFAULT 'KT-KSK',
  `tahun` int(4) NOT NULL,
  `bulan` int(2) NOT NULL,
  `counter` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nomor_surat`
--

INSERT INTO `nomor_surat` (`id`, `jenis_dokumen`, `tahun`, `bulan`, `counter`, `updated_at`) VALUES
(1, 'KT-KSK', 2025, 11, 4, '2025-11-21 05:57:23'),
(2, 'KK-KSK', 2025, 11, 3, '2025-11-21 05:57:23'),
(3, 'KAS-KSK', 2025, 11, 1, '2025-11-21 05:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `nomor_surat_backup`
--

CREATE TABLE `nomor_surat_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `tahun` int(4) NOT NULL,
  `bulan` int(2) NOT NULL,
  `counter` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nomor_surat_backup`
--

INSERT INTO `nomor_surat_backup` (`id`, `tahun`, `bulan`, `counter`, `updated_at`) VALUES
(1, 2025, 11, 2, '2025-11-20 05:58:29');

-- --------------------------------------------------------

--
-- Table structure for table `stok_opname`
--

CREATE TABLE `stok_opname` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `subtotal_fisik` decimal(15,2) DEFAULT 0.00,
  `bon_sementara` decimal(15,2) DEFAULT 0.00,
  `uang_rusak` decimal(15,2) DEFAULT 0.00,
  `materai` decimal(15,2) DEFAULT 0.00,
  `lainnya` decimal(15,2) DEFAULT 0.00,
  `keterangan_lainnya` text DEFAULT NULL,
  `fisik_total` decimal(15,2) NOT NULL,
  `saldo_sistem` decimal(15,2) NOT NULL,
  `selisih` decimal(15,2) NOT NULL,
  `tanggal_opname` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_opname`
--

INSERT INTO `stok_opname` (`id`, `nomor_surat`, `user_id`, `username`, `subtotal_fisik`, `bon_sementara`, `uang_rusak`, `materai`, `lainnya`, `keterangan_lainnya`, `fisik_total`, `saldo_sistem`, `selisih`, `tanggal_opname`) VALUES
(1, '001/KAS-KSK/XI/2025', 1, 'admin', 40400000.00, 0.00, 0.00, 0.00, 0.00, NULL, 40400000.00, 2015742135.00, -1975342135.00, '2025-11-21 09:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `stok_opname_detail`
--

CREATE TABLE `stok_opname_detail` (
  `id` int(11) NOT NULL,
  `stok_opname_id` int(11) NOT NULL,
  `no_urut` int(11) NOT NULL,
  `uraian` varchar(100) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `nilai` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_opname_detail`
--

INSERT INTO `stok_opname_detail` (`id`, `stok_opname_id`, `no_urut`, `uraian`, `satuan`, `jumlah`, `nilai`) VALUES
(1, 1, 1, 'Seratus Ribuan Kertas', 'Lembar', 120, 100000.00),
(2, 1, 2, 'Lima Puluh Ribuan Kertas', 'Lembar', 430, 50000.00),
(3, 1, 3, 'Dua Puluh Ribuan Kertas', 'Lembar', 120, 20000.00),
(4, 1, 4, 'Sepuluh Ribuan Kertas', 'Lembar', 450, 10000.00),
(5, 1, 5, 'Lima Ribuan Kertas', 'Lembar', 0, 5000.00),
(6, 1, 6, 'Dua Ribuan Kertas', 'Lembar', 0, 2000.00),
(7, 1, 7, 'Satu Ribuan Kertas', 'Lembar', 0, 1000.00),
(8, 1, 8, 'Satu Ribuan Logam', 'Keping', 0, 1000.00),
(9, 1, 9, 'Lima Ratusan Logam', 'Keping', 0, 500.00),
(10, 1, 10, 'Dua Ratusan Logam', 'Keping', 0, 200.00),
(11, 1, 11, 'Satu Ratusan Logam', 'Keping', 0, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `jenis_transaksi` enum('kas_terima','kas_keluar') NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `nomor_surat`, `user_id`, `username`, `jenis_transaksi`, `nominal`, `keterangan`, `tanggal_transaksi`) VALUES
(9, '003/KT-KSK/XI/2025', 1, 'admin', 'kas_terima', 1999022134.00, 'wassapguys12', '2025-11-20 13:35:08'),
(10, '004/KT-KSK/XI/2025', 1, 'admin', 'kas_terima', 19000001.00, 'wassapmamen', '2025-11-20 13:35:32'),
(11, '001/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 1900000.00, 'wassapguys1221', '2025-11-20 13:42:26'),
(12, '002/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 190000.00, 'wassapmamen', '2025-11-20 13:43:31'),
(13, '003/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 190000.00, 'wassapguys12', '2025-11-20 13:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('Kasir','Administrator') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$upcANdBwwfO7IZWJ0BvFve74wJQewUJ0aJ1Ykgd03UdlPocWzd.XW', 'Andreanoe Meitin', 'Administrator', '2025-11-17 02:51:52'),
(2, 'palihnges', '$2y$10$5KYqmbmP/3XEnYQsS/9q2OzpOLgAuI2ilpcyR5SoWk6J05XyfWG6O', 'Falih Pangestu', 'Kasir', '2025-11-17 00:51:50'),
(5, 'ngising', '$2y$10$AXCuteFGWCj3wH7kZYOV4OU1cX567gK1VPNbprWw31aa60YhyzodG', 'Ngising Mampet', 'Kasir', '2025-11-17 22:14:44');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_users_after_delete` AFTER DELETE ON `users` FOR EACH ROW BEGIN
    INSERT INTO audit_log (user_id, username, action, ip_address, timestamp)
    VALUES (OLD.id, OLD.username, CONCAT('Akun dihapus: ', OLD.username, ' (Role: ', OLD.role, ')'), NULL, NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_users_after_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO audit_log (user_id, username, action, ip_address, timestamp)
    VALUES (NEW.id, NEW.username, CONCAT('Akun dibuat: ', NEW.username, ' (Role: ', NEW.role, ')'), NULL, NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_users_after_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    DECLARE changed_fields VARCHAR(255) DEFAULT '';
    
    IF OLD.username <> NEW.username THEN
        SET changed_fields = CONCAT(changed_fields, 'Username: ', OLD.username, ' → ', NEW.username, '; ');
    END IF;
    
    IF OLD.nama_lengkap <> NEW.nama_lengkap THEN
        SET changed_fields = CONCAT(changed_fields, 'Nama: ', OLD.nama_lengkap, ' → ', NEW.nama_lengkap, '; ');
    END IF;
    
    IF OLD.role <> NEW.role THEN
        SET changed_fields = CONCAT(changed_fields, 'Role: ', OLD.role, ' → ', NEW.role, '; ');
    END IF;
    
    IF changed_fields <> '' THEN
        INSERT INTO audit_log (user_id, username, action, ip_address, timestamp)
        VALUES (NEW.id, NEW.username, CONCAT('Akun diupdate: ', changed_fields), NULL, NOW());
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `konfigurasi`
--
ALTER TABLE `konfigurasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nomor_surat`
--
ALTER TABLE `nomor_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nomor` (`jenis_dokumen`,`tahun`,`bulan`);

--
-- Indexes for table `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_nomor_surat` (`nomor_surat`);

--
-- Indexes for table `stok_opname_detail`
--
ALTER TABLE `stok_opname_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stok_opname_id` (`stok_opname_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_nomor_surat` (`nomor_surat`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `konfigurasi`
--
ALTER TABLE `konfigurasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nomor_surat`
--
ALTER TABLE `nomor_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stok_opname`
--
ALTER TABLE `stok_opname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stok_opname_detail`
--
ALTER TABLE `stok_opname_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD CONSTRAINT `stok_opname_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `stok_opname_detail`
--
ALTER TABLE `stok_opname_detail`
  ADD CONSTRAINT `stok_opname_detail_ibfk_1` FOREIGN KEY (`stok_opname_id`) REFERENCES `stok_opname` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
