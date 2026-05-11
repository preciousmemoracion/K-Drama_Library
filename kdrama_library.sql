-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2026 at 02:27 AM
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
-- Database: `kdrama_library`
--

-- --------------------------------------------------------

--
-- Table structure for table `dramas`
--

CREATE TABLE `dramas` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `episodes` int(11) DEFAULT NULL,
  `released_year` year(4) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dramas`
--

INSERT INTO `dramas` (`id`, `title`, `genre`, `episodes`, `released_year`, `rating`, `image`) VALUES
(4, 'Crash Landing On You', 'Romance, Drama', 16, '2019', 9.0, 'crash.jpg'),
(5, 'Goblin', 'Fantasy, Romance', 16, '2016', 8.8, 'goblin.jpg'),
(6, 'Descendants Of The Sun', 'Romance, Action', 16, '2016', 8.5, 'descendants.jpg'),
(7, 'The Heirs', 'Romance, School', 16, '2013', 7.8, 'heirs.jpg'),
(8, 'Boys Over Flowers', 'Romance, School', 25, '2009', 7.9, 'boys_over_flowers.jpg'),
(9, 'Hotel Del Luna', 'Fantasy, Romance', 16, '2019', 8.2, 'hotel.jpg'),
(10, 'Twinkling Watermelon', 'Fantasy, Romance, Youth, Music', 16, '2023', 8.9, 'twinkling.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dramas`
--
ALTER TABLE `dramas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dramas`
--
ALTER TABLE `dramas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
