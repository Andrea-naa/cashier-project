-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 03:53 AM
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
(1, 'admin', '$2y$10$upcANdBwwfO7IZWJ0BvFve74wJQewUJ0aJ1Ykgd03UdlPocWzd.XW', 'Andreanoe Meitin', '', '2025-11-17 02:51:52');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `konfigurasi`
--
ALTER TABLE `konfigurasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
