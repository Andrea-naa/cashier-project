-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 09:29 AM
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
(22, 1, 'admin', 'Login berhasil', '::1', '2025-11-18 08:26:13');

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
-- Table structure for table `stok_opname`
--

CREATE TABLE `stok_opname` (
  `id` int(11) NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `jenis_transaksi` enum('kas_terima','kas_keluar') NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `konfigurasi`
--
ALTER TABLE `konfigurasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stok_opname`
--
ALTER TABLE `stok_opname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stok_opname_detail`
--
ALTER TABLE `stok_opname_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
