-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 09:47 PM
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
-- Database: `id_card_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$IIqWNSp0UiMyvYbjbBUTxe2zSqUJ.Br3NJTpGDWLU3esTlcDTW0bm', 'admin@example.com', '2025-09-28 13:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `job_type` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `id_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `date_of_birth`, `job_type`, `photo`, `id_number`, `created_at`, `updated_at`) VALUES
(3, 'noorawil@hotmail.com', '$2y$10$gP0y3Di9oj3mAloEEjw4ZOAbO.DWU8GMyX9WudJtC7QJs7BoohZ8S', 'Nora', 'Wail', '1971-06-10', 'Director', '68d9890d57d67.png', 'ID2025833114', '2025-09-28 18:51:08', '2025-09-28 19:14:21'),
(4, 'fatima54@yahoo.com', '$2y$10$ERkpvcerX9RoMJ3qpXa3guw2Y/N7MvAQ365r4htUsrLqKtYn3.VoO', 'Fatima', 'Agogo', '1967-11-12', 'Teacher', '68d9890184b0d.png', 'ID2025155391', '2025-09-28 18:52:43', '2025-09-28 19:14:09'),
(5, 'jack98@gmail.com', '$2y$10$0xIgEOFhwJNMEi61qhZKoO3i7zD/3q7ewnEtuPMm70trMTTmefLXa', 'Jack', 'Noah', '1978-09-19', 'Manager', '68d988f302f47.png', 'ID2025694009', '2025-09-28 18:54:02', '2025-09-28 19:13:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
