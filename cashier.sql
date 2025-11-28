-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 08:16 AM
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
(52, 1, 'admin', 'Login berhasil', '::1', '2025-11-21 08:23:22'),
(53, 2, 'Falih', 'Akun diupdate: Username: palihnges → Falih; Role: Kasir → Administrator; ', NULL, '2025-11-21 13:55:57'),
(54, 5, 'KSK', 'Akun diupdate: Username: ngising → KSK; Nama: Ngising Mampet → PT. Kalimantan Sawit Kusuma; ', NULL, '2025-11-21 13:56:32'),
(55, 6, 'Viola', 'Akun dibuat: Viola (Role: Administrator)', NULL, '2025-11-21 13:56:50'),
(56, 1, 'admin', 'Hapus Kas Masuk #10', '::1', '2025-11-21 13:57:13'),
(57, 1, 'admin', 'Hapus Kas Masuk #9', '::1', '2025-11-21 13:57:15'),
(58, 1, 'admin', 'Hapus Kas Keluar #13', '::1', '2025-11-21 13:57:19'),
(59, 1, 'admin', 'Hapus Kas Keluar #12', '::1', '2025-11-21 13:57:21'),
(60, 1, 'admin', 'Hapus Kas Keluar #11', '::1', '2025-11-21 13:57:22'),
(61, 1, 'admin', 'Login berhasil', '::1', '2025-11-24 13:56:19'),
(62, 6, 'Viola', 'Login berhasil', '::1', '2025-11-24 14:01:33'),
(63, 1, 'admin', 'Login berhasil', '::1', '2025-11-24 14:01:47'),
(64, 1, 'admin', 'Login berhasil', '::1', '2025-11-24 14:02:27'),
(65, 1, 'admin', 'Login berhasil', '::1', '2025-11-24 14:04:17'),
(66, 1, 'admin', 'Kas Masuk #005/KT-KSK/XI/2025: Rp. 1.000.000', '::1', '2025-11-24 14:05:22'),
(67, 6, 'Viola', 'Hapus Kas Masuk #14', '::1', '2025-11-24 14:06:22'),
(68, 6, 'Viola', 'Kas Masuk #006/KT-KSK/XI/2025: Rp. 10.000.000', '::1', '2025-11-24 16:13:43'),
(69, 6, 'Viola', 'Update Kas Masuk #15: Rp. 10.000.000', '::1', '2025-11-24 16:14:42'),
(70, 6, 'Viola', 'Kas Keluar #004/KK-KSK/XI/2025: Rp. 50.000', '::1', '2025-11-24 16:18:49'),
(71, 6, 'Viola', 'Update Kas Masuk #15: Rp. 10.000.000', '::1', '2025-11-24 16:19:17'),
(72, 1, 'admin', 'Login berhasil', '::1', '2025-11-25 09:00:45'),
(73, 1, 'admin', 'Login berhasil', '::1', '2025-11-25 09:01:05'),
(74, 5, 'KSK', 'Login berhasil', '::1', '2025-11-25 09:04:13'),
(75, 6, 'Viola', 'Login berhasil', '::1', '2025-11-25 09:45:44'),
(76, 6, 'Viola', 'Kas Masuk #007/KT-KSK/XI/2025: Rp. 1.000', '::1', '2025-11-25 09:47:25'),
(77, 6, 'Viola', 'Kas Keluar #005/KK-KSK/XI/2025: Rp. 500', '::1', '2025-11-25 09:47:41'),
(78, 6, 'Viola', 'Kas Masuk #008/KT-KSK/XI/2025: Rp. 500.000.000', '::1', '2025-11-25 10:57:07'),
(79, 6, 'Viola', 'Kas Keluar #006/KK-KSK/XI/2025: Rp. 1.000', '::1', '2025-11-25 10:57:32'),
(80, 6, 'Viola', 'Kas Masuk #009/KT-KSK/XI/2025: Rp. 15.000', '::1', '2025-11-25 10:57:47'),
(81, 1, 'admin', 'Update Kas Masuk #19: Rp. 500.000.000', '::1', '2025-11-25 11:27:03'),
(82, 1, 'admin', 'Login berhasil', '::1', '2025-11-25 13:25:58'),
(83, 1, 'admin', 'Login berhasil', '::1', '2025-11-25 13:28:13'),
(84, 6, 'Viola', 'Login berhasil', '::1', '2025-11-25 14:27:11'),
(85, 1, 'admin', 'Login berhasil', '::1', '2025-11-25 15:05:59'),
(86, 6, 'Viola', 'Login berhasil', '::1', '2025-11-25 15:33:17'),
(87, 1, 'admin', 'Login berhasil', '::1', '2025-11-26 08:22:20'),
(88, 1, 'admin', 'Login berhasil', '::1', '2025-11-26 08:40:31'),
(89, 6, 'Viola', 'Login berhasil', '::1', '2025-11-26 09:10:34'),
(90, 5, 'KSK', 'Login berhasil', '::1', '2025-11-26 09:49:12'),
(91, 1, 'admin', 'Kas Keluar #007/KK-KSK/XI/2025: Rp. 150.000', '::1', '2025-11-26 09:51:43'),
(92, 1, 'admin', 'Kas Keluar #008/KK-KSK/XI/2025: Rp. 2.400.000', '::1', '2025-11-26 09:51:54'),
(93, 1, 'admin', 'Kas Keluar #009/KK-KSK/XI/2025: Rp. 2.500.000', '::1', '2025-11-26 09:52:12'),
(94, 1, 'admin', 'Kas Keluar #010/KK-KSK/XI/2025: Rp. 200.000', '::1', '2025-11-26 09:52:53'),
(95, 1, 'admin', 'Kas Keluar #011/KK-KSK/XI/2025: Rp. 2.400.000', '::1', '2025-11-26 09:53:07'),
(96, 1, 'admin', 'Kas Keluar #012/KK-KSK/XI/2025: Rp. 1.500.000', '::1', '2025-11-26 09:53:21'),
(97, 6, 'Viola', 'Kas Masuk #010/KT-KSK/XI/2025: Rp. 234', '::1', '2025-11-26 09:53:26'),
(98, 1, 'admin', 'Kas Keluar #013/KK-KSK/XI/2025: Rp. 120.000', '::1', '2025-11-26 09:53:30'),
(99, 6, 'Viola', 'Kas Masuk #011/KT-KSK/XI/2025: Rp. 12.345.678', '::1', '2025-11-26 09:53:36'),
(100, 1, 'admin', 'Kas Keluar #014/KK-KSK/XI/2025: Rp. 2.500.000', '::1', '2025-11-26 09:54:19'),
(101, 1, 'admin', 'Kas Keluar #015/KK-KSK/XI/2025: Rp. 2.500.000', '::1', '2025-11-26 09:54:30'),
(102, 1, 'admin', 'Kas Keluar #016/KK-KSK/XI/2025: Rp. 2.500.000', '::1', '2025-11-26 09:54:39'),
(103, 1, 'admin', 'Kas Keluar #017/KK-KSK/XI/2025: Rp. 2.000.000', '::1', '2025-11-26 09:54:50'),
(104, 1, 'admin', 'Kas Keluar #018/KK-KSK/XI/2025: Rp. 2.500.000', '::1', '2025-11-26 09:55:14'),
(105, 1, 'admin', 'Kas Keluar #019/KK-KSK/XI/2025: Rp. 1.350.000', '::1', '2025-11-26 09:55:24'),
(106, 1, 'admin', 'Kas Keluar #020/KK-KSK/XI/2025: Rp. 1.250.000', '::1', '2025-11-26 09:55:32'),
(107, 1, 'admin', 'Kas Keluar #021/KK-KSK/XI/2025: Rp. 5.000.000', '::1', '2025-11-26 09:55:42'),
(108, 2, 'Falih', 'Akun diupdate: Role: Administrator → Kasir; ', NULL, '2025-11-26 10:43:11'),
(109, 5, 'KSK', 'Akun diupdate: Role: Kasir → Administrator; ', NULL, '2025-11-26 10:43:18'),
(110, 6, 'Viola', 'Login berhasil', '::1', '2025-11-26 11:09:42'),
(111, 5, 'KSK', 'Kas Masuk #012/KT-KSK/XI/2025: Rp. 3.400.000', '::1', '2025-11-26 11:20:55'),
(112, 6, 'Viola', 'Login berhasil', '::1', '2025-11-26 11:21:24'),
(113, 6, 'Viola', 'Kas Masuk #013/KT-KSK/XI/2025: Rp. 30.000.000', '::1', '2025-11-26 11:24:32'),
(114, 5, 'KSK', 'Login berhasil', '::1', '2025-11-26 13:08:49'),
(115, 5, 'KSK', 'Login berhasil', '::1', '2025-11-26 14:05:35');

-- --------------------------------------------------------

--
-- Table structure for table `konfigurasi`
--

CREATE TABLE `konfigurasi` (
  `id` int(11) NOT NULL,
  `nama_perusahaan` varchar(200) DEFAULT 'PT. MITRA SAUDARA LESTARI - MSL',
  `kode_perusahaan` varchar(10) DEFAULT 'KSK',
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

INSERT INTO `konfigurasi` (`id`, `nama_perusahaan`, `kode_perusahaan`, `alamat`, `kota`, `telepon`, `email`, `ttd_jabatan_1`, `ttd_jabatan_2`, `ttd_jabatan_3`, `ttd_jabatan_4`, `updated_at`) VALUES
(1, 'PT. Kalimantan Sawit Kusuma', 'KSK', 'Jl. W.R Supratman No. 42 Pontianak', 'Kota Pontianak', '0778-123456', 'info@ksk.com', 'Finance Dept Head', 'Finance Sub Dept Head', 'Finance Div Head', 'Cashier', '2025-11-18 08:27:32');

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
(1, 'KT-KSK', 2025, 11, 13, '2025-11-26 04:24:32'),
(2, 'KK-KSK', 2025, 11, 21, '2025-11-26 02:55:42'),
(3, 'KAS-KSK', 2025, 11, 3, '2025-11-25 02:09:00'),
(4, 'STOK-KSK', 2025, 11, 3, '2025-11-26 02:29:31');

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
  `tanggal_opname` datetime DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_opname`
--

INSERT INTO `stok_opname` (`id`, `nomor_surat`, `user_id`, `username`, `subtotal_fisik`, `bon_sementara`, `uang_rusak`, `materai`, `lainnya`, `keterangan_lainnya`, `fisik_total`, `saldo_sistem`, `selisih`, `tanggal_opname`, `is_approved`, `approved_by`, `approved_at`) VALUES
(2, '002/KAS-KSK/XI/2025', 6, 'Viola', 3500000.00, 0.00, 0.00, 0.00, 0.00, NULL, 3500000.00, 9950000.00, 6450000.00, '2025-11-24 16:19:58', 0, NULL, NULL),
(5, '002/STOK-KSK/XI/2025', 1, 'admin', 360000.00, 0.00, 0.00, 0.00, 0.00, NULL, 360000.00, 509964500.00, 509604500.00, '2025-11-26 09:25:32', 0, NULL, NULL),
(6, '003/STOK-KSK/XI/2025', 1, 'admin', 5514500.00, 0.00, 0.00, 0.00, 0.00, NULL, 5514500.00, 509964500.00, 504450000.00, '2025-11-26 09:29:31', 0, NULL, NULL);

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
(12, 2, 1, 'Seratus Ribuan Kertas', 'Lembar', 10, 100000.00),
(13, 2, 2, 'Lima Puluh Ribuan Kertas', 'Lembar', 50, 50000.00),
(14, 2, 3, 'Dua Puluh Ribuan Kertas', 'Lembar', 0, 20000.00),
(15, 2, 4, 'Sepuluh Ribuan Kertas', 'Lembar', 0, 10000.00),
(16, 2, 5, 'Lima Ribuan Kertas', 'Lembar', 0, 5000.00),
(17, 2, 6, 'Dua Ribuan Kertas', 'Lembar', 0, 2000.00),
(18, 2, 7, 'Satu Ribuan Kertas', 'Lembar', 0, 1000.00),
(19, 2, 8, 'Satu Ribuan Logam', 'Keping', 0, 1000.00),
(20, 2, 9, 'Lima Ratusan Logam', 'Keping', 0, 500.00),
(21, 2, 10, 'Dua Ratusan Logam', 'Keping', 0, 200.00),
(22, 2, 11, 'Satu Ratusan Logam', 'Keping', 0, 100.00),
(45, 5, 1, 'Seratus Ribuan Kertas', 'Lembar', 2, 100000.00),
(46, 5, 2, 'Lima Puluh Ribuan Kertas', 'Lembar', 1, 50000.00),
(47, 5, 3, 'Dua Puluh Ribuan Kertas', 'Lembar', 2, 20000.00),
(48, 5, 4, 'Sepuluh Ribuan Kertas', 'Lembar', 4, 10000.00),
(49, 5, 5, 'Lima Ribuan Kertas', 'Lembar', 6, 5000.00),
(50, 5, 6, 'Dua Ribuan Kertas', 'Lembar', 0, 2000.00),
(51, 5, 7, 'Satu Ribuan Kertas', 'Lembar', 0, 1000.00),
(52, 5, 8, 'Satu Ribuan Logam', 'Keping', 0, 1000.00),
(53, 5, 9, 'Lima Ratusan Logam', 'Keping', 0, 500.00),
(54, 5, 10, 'Dua Ratusan Logam', 'Keping', 0, 200.00),
(55, 5, 11, 'Satu Ratusan Logam', 'Keping', 0, 100.00),
(56, 6, 1, 'Seratus Ribuan Kertas', 'Lembar', 6, 100000.00),
(57, 6, 2, 'Lima Puluh Ribuan Kertas', 'Lembar', 12, 50000.00),
(58, 6, 3, 'Dua Puluh Ribuan Kertas', 'Lembar', 56, 20000.00),
(59, 6, 4, 'Sepuluh Ribuan Kertas', 'Lembar', 124, 10000.00),
(60, 6, 5, 'Lima Ribuan Kertas', 'Lembar', 198, 5000.00),
(61, 6, 6, 'Dua Ribuan Kertas', 'Lembar', 40, 2000.00),
(62, 6, 7, 'Satu Ribuan Kertas', 'Lembar', 37, 1000.00),
(63, 6, 8, 'Satu Ribuan Logam', 'Keping', 69, 1000.00),
(64, 6, 9, 'Lima Ratusan Logam', 'Keping', 1557, 500.00),
(65, 6, 10, 'Dua Ratusan Logam', 'Keping', 0, 200.00),
(66, 6, 11, 'Satu Ratusan Logam', 'Keping', 0, 100.00);

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
  `tanggal_transaksi` datetime DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `nomor_surat`, `user_id`, `username`, `jenis_transaksi`, `nominal`, `keterangan`, `tanggal_transaksi`, `is_approved`, `approved_by`, `approved_at`) VALUES
(15, '006/KT-KSK/XI/2025', 6, 'Viola', 'kas_terima', 10000000.00, 'curi uang', '2025-11-24 16:13:43', 0, NULL, NULL),
(16, '004/KK-KSK/XI/2025', 6, 'Viola', 'kas_keluar', 50000.00, 'balikin uang curi', '2025-11-24 16:18:49', 0, NULL, NULL),
(17, '007/KT-KSK/XI/2025', 6, 'Viola', 'kas_terima', 1000.00, 'gaji', '2025-11-25 09:47:25', 0, NULL, NULL),
(18, '005/KK-KSK/XI/2025', 6, 'Viola', 'kas_keluar', 500.00, 'permen', '2025-11-25 09:47:41', 0, NULL, NULL),
(19, '008/KT-KSK/XI/2025', 6, 'Viola', 'kas_terima', 500000000.00, 'terima uang kembali', '2025-11-25 10:57:07', 0, NULL, NULL),
(20, '006/KK-KSK/XI/2025', 6, 'Viola', 'kas_keluar', 1000.00, 'jajan gorengan', '2025-11-25 10:57:32', 0, NULL, NULL),
(21, '009/KT-KSK/XI/2025', 6, 'Viola', 'kas_terima', 15000.00, 'lunch', '2025-11-25 10:57:47', 0, NULL, NULL),
(22, '007/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 150000.00, 'Upah &amp; Gaji Karyawan KHL Divisi VIII R.I Oktober 2025  (Sahman)', '2025-11-26 09:51:43', 0, NULL, NULL),
(23, '008/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2400000.00, 'bayar pajak', '2025-11-26 09:51:54', 0, NULL, NULL),
(24, '009/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2500000.00, 'Bayar uang cuti tahunan karyawan gol.5 (Health Div Staf(Bidan)) an Kartika Susan', '2025-11-26 09:52:12', 0, NULL, NULL),
(25, '010/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 200000.00, 'bayar uang', '2025-11-26 09:52:53', 0, NULL, NULL),
(26, '011/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2400000.00, 'uang ksk', '2025-11-26 09:53:07', 0, NULL, NULL),
(27, '012/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 1500000.00, 'uang viola', '2025-11-26 09:53:21', 0, NULL, NULL),
(28, '010/KT-KSK/XI/2025', 6, 'Viola', 'kas_terima', 234.00, 'hy123', '2025-11-26 09:53:26', 0, NULL, NULL),
(29, '013/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 120000.00, 'uang andre', '2025-11-26 09:53:30', 0, NULL, NULL),
(30, '011/KT-KSK/XI/2025', 6, 'Viola', 'kas_terima', 12345678.00, '2345', '2025-11-26 09:53:36', 0, NULL, NULL),
(31, '014/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2500000.00, 'uang falih', '2025-11-26 09:54:19', 0, NULL, NULL),
(32, '015/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2500000.00, 'uang salwa', '2025-11-26 09:54:30', 0, NULL, NULL),
(33, '016/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2500000.00, 'uang meitin', '2025-11-26 09:54:39', 0, NULL, NULL),
(34, '017/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2000000.00, 'uang fsl', '2025-11-26 09:54:50', 0, NULL, NULL),
(35, '018/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 2500000.00, 'Bayar uang cuti tahunan karyawan gol.5 (Health Div Staf(Bidan))', '2025-11-26 09:55:14', 0, NULL, NULL),
(36, '019/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 1350000.00, 'apa ni', '2025-11-26 09:55:24', 0, NULL, NULL),
(37, '020/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 1250000.00, 'nda tau lagi', '2025-11-26 09:55:32', 0, NULL, NULL),
(38, '021/KK-KSK/XI/2025', 1, 'admin', 'kas_keluar', 5000000.00, 'ntah apa ini', '2025-11-26 09:55:42', 0, NULL, NULL),
(39, '012/KT-KSK/XI/2025', 5, 'KSK', 'kas_terima', 3400000.00, 'setoran', '2025-11-26 11:20:55', 0, NULL, NULL),
(40, '013/KT-KSK/XI/2025', 6, 'Viola', 'kas_terima', 30000000.00, 'terima uang curian', '2025-11-26 11:24:32', 0, NULL, NULL);

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
(2, 'Falih', '$2y$10$kbIC8PlzCYekG7S2gJxsDOpXUYeWQRAcofv43ar9zUsIpO0tjmBUm', 'Falih Pangestu', 'Kasir', '2025-11-17 00:51:50'),
(5, 'KSK', '$2y$10$281CCVc5d9BcfroIrGed9Ol4s6jq/URvkykVTuiM8Xi.nk6XEQQ9S', 'PT. Kalimantan Sawit Kusuma', 'Administrator', '2025-11-17 22:14:44'),
(6, 'Viola', '$2y$10$duvkzx89PGsuD2T86I0vruvepUZMtLLKrXsui7NPbTiABMJNEXLqm', 'Viola Salwa Firnanda', 'Administrator', '2025-11-21 00:56:49');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `konfigurasi`
--
ALTER TABLE `konfigurasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nomor_surat`
--
ALTER TABLE `nomor_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stok_opname`
--
ALTER TABLE `stok_opname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stok_opname_detail`
--
ALTER TABLE `stok_opname_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
