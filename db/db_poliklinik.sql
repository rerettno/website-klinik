-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Des 2024 pada 14.00
-- Versi server: 10.4.25-MariaDB
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_poliklinik`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `daftar_poli`
--

CREATE TABLE `daftar_poli` (
  `id` int(11) NOT NULL,
  `id_pasien` int(11) NOT NULL,
  `id_jadwal` int(11) UNSIGNED NOT NULL,
  `tgl_daftar` date DEFAULT NULL,
  `keluhan` text NOT NULL,
  `no_antrian` int(11) NOT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `daftar_poli`
--

INSERT INTO `daftar_poli` (`id`, `id_pasien`, `id_jadwal`, `tgl_daftar`, `keluhan`, `no_antrian`, `active`) VALUES
(59, 25, 22, '2024-12-17', 'sa', 1, 0),
(60, 33, 24, '2024-12-17', 'dd', 1, 1),
(61, 33, 24, '2024-12-17', 'iaaiaia', 2, 1),
(62, 26, 16, '2024-12-18', '', 1, 0),
(63, 26, 22, '2024-12-18', '', 1, 0),
(64, 26, 16, '2024-12-18', '', 2, 0),
(65, 26, 26, '2024-12-18', 'd', 1, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_periksa`
--

CREATE TABLE `detail_periksa` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_periksa` int(11) UNSIGNED NOT NULL,
  `id_obat` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `detail_periksa`
--

INSERT INTO `detail_periksa` (`id`, `id_periksa`, `id_obat`) VALUES
(24, 19, 4),
(25, 19, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `id` int(11) UNSIGNED NOT NULL,
  `password` varchar(255) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_hp` varchar(50) DEFAULT NULL,
  `id_poli` int(11) UNSIGNED NOT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`id`, `password`, `nip`, `nama`, `alamat`, `no_hp`, `id_poli`, `active`) VALUES
(46, '$2y$10$KTl4ztj1xULQj7km0Clz5eGrKbmh0NA0bIYLQcn0JK/QtiiJtiHWS', 'DR2412.001', 'dr.Budi Harjo', 'Semarang Barat', '0826834242', 52, 1),
(47, '$2y$10$5RM3kMIDkPw13sCgTfW4veBDYxYxfQU2CghrEEJDSZ7CmKHdHwkIu', 'DR2412.002', 'dr.Siti Abri', 'Banyumanik', '083142324323', 54, 1),
(48, '$2y$10$hij1KwiBiZ65QvPfYF8oSOi5EI1E8GiaTT8OrD0W233c2HV3Rr1LO', 'DR2412.003', 'dr.Nafitri', 'Kuala Lumpur', '084327682924', 51, 1),
(49, '$2y$10$uC/.0ng28A5HubTtLJ9qause5KJiX5yie03JdrBH9skr6EYZGyo52', 'DR2412.004', 'dr.Ehsan', 'Kampung Durian Runtuh', '087218317329', 53, 1),
(50, '$2y$10$AWSF8wEjYcfzX5Pru9Nlze2uxbn3Xh/.YqIUpnjG58RD61sia99Ii', 'DR2412.005', 'dr.Baymax', 'Syalalala', '087263817365', 50, 1),
(51, '$2y$10$SIi9ETdofu5Dxqpmf/KDcOyk0rfzTL7/JW7hpg2Z/s9MgIzZlodOS', 'DR2412.006', 'dr.Agege', 'Bali', '082848528139', 55, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_periksa`
--

CREATE TABLE `jadwal_periksa` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_dokter` int(11) UNSIGNED NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `jadwal_periksa`
--

INSERT INTO `jadwal_periksa` (`id`, `id_dokter`, `hari`, `jam_mulai`, `jam_selesai`, `active`) VALUES
(16, 46, 'Senin', '09:00:00', '11:00:00', 1),
(17, 46, 'Sabtu', '15:30:00', '17:30:00', 0),
(18, 46, 'Kamis', '14:00:00', '16:00:00', 0),
(19, 47, 'Selasa', '16:00:00', '18:00:00', 0),
(20, 47, 'Minggu', '08:00:00', '11:30:00', 1),
(21, 48, 'Kamis', '13:00:00', '15:00:00', 0),
(22, 48, 'Jumat', '09:00:00', '12:00:00', 1),
(23, 49, 'Senin', '19:00:00', '21:00:00', 0),
(24, 49, 'Kamis', '07:30:00', '11:00:00', 1),
(25, 51, 'Jumat', '08:00:00', '12:00:00', 1),
(26, 50, 'Kamis', '09:00:00', '12:00:00', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `obat`
--

CREATE TABLE `obat` (
  `id` int(11) UNSIGNED NOT NULL,
  `nama_obat` varchar(50) NOT NULL,
  `kemasan` varchar(35) NOT NULL,
  `harga` int(11) NOT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `obat`
--

INSERT INTO `obat` (`id`, `nama_obat`, `kemasan`, `harga`, `active`) VALUES
(4, 'antangin', 'botol', 1111111, 1),
(5, 'paracetamol', 'serbuk', 123456, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pasien`
--

CREATE TABLE `pasien` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `alamat` varchar(244) NOT NULL,
  `no_hp` varchar(50) NOT NULL,
  `no_rm` varchar(25) NOT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pasien`
--

INSERT INTO `pasien` (`id`, `nama`, `nik`, `alamat`, `no_hp`, `no_rm`, `active`) VALUES
(25, 'hiro', '0000000000000000', 'semarang', '021234554322', '202412-002', 1),
(26, 'retno', '1111111111111111', 'tembalang', '081358904244', '202412-003', 1),
(28, 'gita', '7777777777777777', 'ungaran', '087932442748', '202412-005', 1),
(29, 'lila', '3333333333333333', 'cepu', '087376414233', '202412-006', 1),
(30, 'tari', '4444444444444444', 'semarang', '083253226437', '202412-007', 1),
(31, 'baymax', '5555555555555555', 'yamada\r\n', '078665432456', '202412-008', 1),
(32, 'Hapsari Retno Puspitaningrum', '3375015103040002', 'Perum Bukit Permata Jaya Blok DD-01, Tembalang Semarang', '085328051707', '202412-009', 1),
(33, 'cahyo', '1222111111111111', 'semarang', '00000000000', '202412-010', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `periksa`
--

CREATE TABLE `periksa` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_daftar_poli` int(11) NOT NULL,
  `tgl_periksa` datetime NOT NULL,
  `catatan` text DEFAULT NULL,
  `biaya_periksa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `periksa`
--

INSERT INTO `periksa` (`id`, `id_daftar_poli`, `tgl_periksa`, `catatan`, `biaya_periksa`) VALUES
(18, 60, '2024-12-17 14:20:20', 'turu', 150000),
(19, 61, '2024-12-17 14:25:07', 'saa', 1384567);

-- --------------------------------------------------------

--
-- Struktur dari tabel `poli`
--

CREATE TABLE `poli` (
  `id` int(11) UNSIGNED NOT NULL,
  `nama_poli` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `poli`
--

INSERT INTO `poli` (`id`, `nama_poli`, `keterangan`, `active`) VALUES
(50, 'Poli Umum', 'Bidang Kesehatan Umum\r\n', 1),
(51, 'Poli Gigi', 'Bidang Kesehatan Gigi', 1),
(52, 'UGD', 'Bidang Unit Gawat Darurat\r\n', 1),
(53, 'Poli Syaraf', 'Bidang Kesehatan Syaraf', 1),
(54, 'Poli Ibu dan Anak', 'Bidang Kesehatan Ibu dan Anak', 1),
(55, 'Poli Hewan', 'Bidang Kesehatan Hewan', 1),
(56, 'poli klinik', 'poli kinik umum', 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `daftar_poli`
--
ALTER TABLE `daftar_poli`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `id_pasien` (`id_pasien`);

--
-- Indeks untuk tabel `detail_periksa`
--
ALTER TABLE `detail_periksa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_periksa` (`id_periksa`),
  ADD KEY `id_obat` (`id_obat`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `id_poli` (`id_poli`);

--
-- Indeks untuk tabel `jadwal_periksa`
--
ALTER TABLE `jadwal_periksa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dokter` (`id_dokter`);

--
-- Indeks untuk tabel `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indeks untuk tabel `periksa`
--
ALTER TABLE `periksa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_daftar_poli` (`id_daftar_poli`);

--
-- Indeks untuk tabel `poli`
--
ALTER TABLE `poli`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `daftar_poli`
--
ALTER TABLE `daftar_poli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT untuk tabel `detail_periksa`
--
ALTER TABLE `detail_periksa`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT untuk tabel `jadwal_periksa`
--
ALTER TABLE `jadwal_periksa`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `obat`
--
ALTER TABLE `obat`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `periksa`
--
ALTER TABLE `periksa`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `poli`
--
ALTER TABLE `poli`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `daftar_poli`
--
ALTER TABLE `daftar_poli`
  ADD CONSTRAINT `daftar_poli_ibfk_2` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_periksa` (`id`),
  ADD CONSTRAINT `daftar_poli_ibfk_3` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id`);

--
-- Ketidakleluasaan untuk tabel `detail_periksa`
--
ALTER TABLE `detail_periksa`
  ADD CONSTRAINT `detail_periksa_ibfk_1` FOREIGN KEY (`id_periksa`) REFERENCES `periksa` (`id`),
  ADD CONSTRAINT `detail_periksa_ibfk_2` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id`);

--
-- Ketidakleluasaan untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD CONSTRAINT `dokter_ibfk_1` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id`);

--
-- Ketidakleluasaan untuk tabel `jadwal_periksa`
--
ALTER TABLE `jadwal_periksa`
  ADD CONSTRAINT `jadwal_periksa_ibfk_1` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id`);

--
-- Ketidakleluasaan untuk tabel `periksa`
--
ALTER TABLE `periksa`
  ADD CONSTRAINT `periksa_ibfk_1` FOREIGN KEY (`id_daftar_poli`) REFERENCES `daftar_poli` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
