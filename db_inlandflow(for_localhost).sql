-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 02:13 AM
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
-- Database: `db_inlandflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `resortid` int(200) NOT NULL,
  `resort_room_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_description` varchar(1000) NOT NULL DEFAULT 'No Description',
  `uploaded_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('1','0') NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `resortid`, `resort_room_id`, `file_name`, `file_description`, `uploaded_on`, `status`) VALUES
(59, 3, NULL, 'jmaire-01.jpg', 'Pool', '2022-09-27 04:20:21', '1'),
(57, 3, NULL, 'jmaire-05.jpg', 'Pavilion', '2022-09-27 05:03:43', '1'),
(53, 1, NULL, 'doms-1.jpg', '', '2022-08-30 11:20:45', '1'),
(54, 1, NULL, 'doms-5.jpg', '', '2022-08-30 11:21:37', '1'),
(55, 1, NULL, 'doms-7.jpg', '', '2022-08-30 11:24:16', '1'),
(60, 2, NULL, 't-1.jpg', '', '2022-08-30 11:32:15', '1'),
(61, 2, NULL, 't-5.jpg', '', '2022-08-30 11:32:26', '1'),
(62, 4, NULL, 'r-1.jpg', '', '2022-08-30 11:33:17', '1'),
(63, 4, NULL, 'r-2.jpg', '', '2022-08-30 11:33:26', '1'),
(71, 1, NULL, 'doms-4.jpg', '', '2022-09-01 07:15:18', '1'),
(72, 1, NULL, 'doms-8.jpg', '', '2022-09-01 07:15:18', '1'),
(73, 12, NULL, '1669825818838.jpg', 'JEPAC Pool and Slides', '2022-11-21 04:05:42', '1'),
(74, 12, NULL, '1669825829403.jpg', 'JEPAC Activities', '2022-11-21 04:05:59', '1'),
(85, 11, NULL, '1669827088165.jpg', 'No Description', '2022-11-21 00:53:39', '1'),
(86, 13, NULL, '1670769762813.jpg', 'No Description', '2022-11-22 01:49:52', '1'),
(78, 5, NULL, '1669827130540.jpg', 'No Description', '2022-11-21 04:16:21', '1'),
(79, 5, NULL, '1669827142486.jpg', 'No Description', '2022-11-21 04:16:21', '1'),
(80, 5, NULL, '1669827148569.jpg', 'No Description', '2022-11-21 04:16:21', '1'),
(87, 13, NULL, '1670769768751.jpg', 'No Description', '2022-11-22 01:49:52', '1'),
(83, 3, NULL, 'jmaire-03.jpg', 'No Description', '2022-11-21 00:10:43', '1'),
(88, 13, NULL, '1670769773006.jpg', 'No Description', '2022-11-22 01:49:52', '1'),
(0, 1, NULL, 'doms-3.jpg', 'No Description', '2026-05-16 16:49:29', '1'),
(89, 1, NULL, 'doms-3.jpg', 'No Description', '2026-05-16 16:51:52', '1'),
(90, 1, NULL, 'doms-5.jpg', 'No Description', '2026-05-16 16:51:52', '1'),
(91, 1, NULL, 'doms-6.jpg', 'No Description', '2026-05-16 16:51:52', '1'),
(92, 1, NULL, 'doms-1.jpg', 'No Description', '2026-05-16 16:52:27', '1'),
(93, 1, 2, '1_room_2_1778983919_0.jpg', 'No Description', '2026-05-17 02:11:59', '1'),
(94, 1, 2, '1_room_2_1778983919_1.jpg', 'No Description', '2026-05-17 02:11:59', '1'),
(95, 1, 2, '1_room_2_1778983919_2.jpg', 'No Description', '2026-05-17 02:11:59', '1'),
(96, 1, 2, '1_room_2_1778983919_3.jpg', 'No Description', '2026-05-17 02:11:59', '1'),
(97, 1, 2, '1_room_2_1778983919_4.jpg', 'No Description', '2026-05-17 02:11:59', '1'),
(98, 1, 2, '1_room_2_1778983919_5.jpg', 'No Description', '2026-05-17 02:11:59', '1'),
(99, 17, 14, '17_room_14_1779046906_0.png', 'No Description', '2026-05-17 19:41:47', '1'),
(100, 17, 14, '17_room_14_1779046907_1.png', 'No Description', '2026-05-17 19:41:47', '1'),
(101, 17, 14, '17_room_14_1779046907_2.jpg', 'No Description', '2026-05-17 19:41:47', '1'),
(102, 17, 14, '17_room_14_1779046907_3.webp', 'No Description', '2026-05-17 19:41:47', '1'),
(103, 17, NULL, 'pod-1.png', 'No Description', '2026-05-17 19:53:34', '1'),
(104, 17, NULL, 'pod-2.png', 'No Description', '2026-05-17 19:53:34', '1'),
(105, 17, NULL, 'pod-4.jpg', 'No Description', '2026-05-17 19:53:34', '1'),
(106, 17, NULL, 'pod-5.webp', 'No Description', '2026-05-17 19:53:34', '1'),
(107, 17, NULL, 'solina.jpg', 'No Description', '2026-05-17 19:53:34', '1'),
(108, 17, NULL, 'solina-1.jpg', 'No Description', '2026-05-17 19:53:34', '1'),
(109, 17, NULL, 'solina-3.jpg', 'No Description', '2026-05-17 19:53:34', '1'),
(110, 17, NULL, 'solina-4.webp', 'No Description', '2026-05-17 19:53:34', '1'),
(111, 17, NULL, 'solina-5.webp', 'No Description', '2026-05-17 19:53:34', '1'),
(112, 17, NULL, 'solina-6.jpg', 'No Description', '2026-05-17 19:53:34', '1');

-- --------------------------------------------------------

--
-- Table structure for table `tb_add_on_amenities`
--

CREATE TABLE `tb_add_on_amenities` (
  `add_on_amenity_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_amenity_fee` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_add_on_amenities`
--

INSERT INTO `tb_add_on_amenities` (`add_on_amenity_id`, `po_id`, `amenity_id`, `quantity`, `total_amenity_fee`) VALUES
(0, 28, 2, 1, 90),
(4, 11, 2, 1, 90),
(5, 24, 1, 1, 50),
(6, 24, 2, 1, 90),
(7, 24, 4, 1, 30),
(8, 25, 1, 1, 50),
(9, 25, 2, 1, 90),
(10, 25, 4, 1, 30),
(11, 26, 1, 1, 50),
(12, 26, 2, 1, 90),
(13, 26, 4, 1, 30),
(16, 27, 4, 1, 30),
(17, 33, 5, 1, 1500),
(18, 34, 2, 5, 450),
(19, 34, 1, 5, 250);

-- --------------------------------------------------------

--
-- Table structure for table `tb_add_on_details`
--

CREATE TABLE `tb_add_on_details` (
  `add_on_details_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `resort_room_id` int(11) NOT NULL,
  `num_adults` int(11) NOT NULL,
  `adult_fee` int(11) NOT NULL,
  `num_kids` int(11) NOT NULL,
  `kids_fee` int(11) NOT NULL,
  `total_fee` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_add_on_details`
--

INSERT INTO `tb_add_on_details` (`add_on_details_id`, `po_id`, `resort_room_id`, `num_adults`, `adult_fee`, `num_kids`, `kids_fee`, `total_fee`) VALUES
(13, 9, 3, 4, 320, 3, 150, 869),
(16, 9, 2, 2, 160, 1, 50, 2109),
(21, 13, 5, 2, 160, 5, 250, 2609),
(23, 16, 2, 12, 960, 1, 50, 2909),
(24, 11, 2, 3, 240, 4, 200, 2339);

-- --------------------------------------------------------

--
-- Table structure for table `tb_analytics_bookings`
--

CREATE TABLE `tb_analytics_bookings` (
  `analytics_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `total_bookings` int(11) NOT NULL DEFAULT 0,
  `cancelled_bookings` int(11) NOT NULL DEFAULT 0,
  `confirmed_bookings` int(11) NOT NULL DEFAULT 0,
  `pending_bookings` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_analytics_resource`
--

CREATE TABLE `tb_analytics_resource` (
  `resource_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `occupancy_rate` double NOT NULL DEFAULT 0,
  `utilization_hours` double NOT NULL DEFAULT 0,
  `peak_hours` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_analytics_revenue`
--

CREATE TABLE `tb_analytics_revenue` (
  `revenue_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `total_revenue` double NOT NULL DEFAULT 0,
  `room_revenue` double NOT NULL DEFAULT 0,
  `amenity_revenue` double NOT NULL DEFAULT 0,
  `other_revenue` double NOT NULL DEFAULT 0,
  `expenses` double NOT NULL DEFAULT 0,
  `net_profit` double NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_cart`
--

CREATE TABLE `tb_cart` (
  `cart_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `resort_room_id` int(11) NOT NULL,
  `checkindate` date NOT NULL,
  `checkoutdate` date NOT NULL,
  `num_adults` int(11) NOT NULL,
  `num_kids` int(11) NOT NULL,
  `cart_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_cart`
--

INSERT INTO `tb_cart` (`cart_id`, `guest_id`, `resortid`, `resort_room_id`, `checkindate`, `checkoutdate`, `num_adults`, `num_kids`, `cart_status`) VALUES
(0, 15, 3, 6, '2026-04-28', '2026-04-30', 4, 3, 'Cancelled'),
(1, 1, 1, 2, '2022-12-31', '2022-12-31', 1, 2, 'Place Order'),
(2, 1, 2, 4, '2022-12-25', '2022-12-25', 2, 3, 'Place Order'),
(5, 1, 3, 6, '2022-12-28', '2022-12-28', 2, 3, 'Place Order'),
(6, 11, 1, 5, '2022-12-21', '2022-12-21', 3, 5, 'Place Order'),
(9, 1, 3, 8, '2022-12-22', '2022-12-22', 4, 2, 'Place Order'),
(10, 1, 3, 11, '2022-12-23', '2022-12-23', 5, 2, 'Place Order'),
(11, 1, 3, 8, '2022-12-24', '2022-12-24', 5, 1, 'Place Order'),
(12, 1, 3, 11, '2022-12-30', '2022-12-30', 2, 2, 'Place Order'),
(13, 1, 1, 1, '2022-12-27', '2022-12-27', 2, 3, 'Place Order'),
(14, 1, 1, 1, '2022-12-18', '2022-12-18', 5, 2, 'Place Order'),
(15, 1, 2, 4, '2022-11-18', '2022-11-18', 7, 4, 'Place Order'),
(17, 1, 3, 6, '2022-12-18', '2022-12-18', 5, 4, 'Place Order'),
(18, 11, 3, 8, '2022-12-17', '2022-12-18', 4, 2, ''),
(19, 11, 2, 4, '2022-12-26', '2022-12-27', 3, 1, ''),
(20, 1, 1, 1, '2022-12-19', '2022-12-19', 5, 2, 'Place Order'),
(21, 1, 3, 8, '2022-12-18', '2022-12-19', 6, 7, 'Place Order'),
(22, 1, 1, 1, '2022-12-22', '2022-12-23', 3, 4, 'Place Order'),
(23, 15, 1, 3, '2025-08-20', '2025-08-21', 5, 8, 'Place Order'),
(24, 15, 1, 1, '2026-04-22', '2026-04-25', 3, 2, 'Cancelled'),
(26, 15, 1, 1, '2026-04-23', '2026-04-25', 2, 0, 'Cancelled'),
(27, 15, 1, 1, '2026-04-28', '2026-04-30', 3, 0, 'Cancelled'),
(28, 15, 3, 6, '2026-04-28', '2026-04-30', 4, 3, 'Cancelled'),
(29, 15, 3, 6, '2026-04-28', '2026-04-30', 4, 3, 'Place Order'),
(30, 15, 1, 1, '2026-04-23', '2026-04-25', 2, 0, 'Place Order'),
(31, 15, 1, 2, '2026-04-28', '2026-04-29', 1, 0, 'Place Order'),
(32, 15, 1, 2, '2026-04-27', '2026-04-28', 2, 2, 'Place Order'),
(33, 15, 5, 0, '2026-05-05', '2026-05-06', 1, 2, 'Place Order'),
(34, 15, 3, 0, '2026-05-04', '2026-05-06', 1, 3, 'Place Order'),
(35, 15, 3, 6, '2026-05-04', '2026-05-06', 1, 0, 'Place Order'),
(36, 15, 1, 12, '2026-05-05', '2026-05-06', 4, 0, 'Place Order'),
(37, 15, 2, 4, '2026-05-05', '2026-05-06', 4, 0, 'Place Order'),
(38, 15, 1, 3, '2026-05-07', '2026-05-09', 4, 1, 'Place Order'),
(39, 15, 1, 2, '2026-05-05', '2026-05-06', 1, 0, 'Place Order'),
(40, 15, 3, 8, '2026-05-16', '2026-05-31', 6, 0, 'Place Order'),
(41, 15, 2, 4, '2026-05-16', '2026-05-31', 6, 0, 'Place Order'),
(42, 15, 1, 2, '2026-05-15', '2026-06-16', 5, 3, 'Place Order'),
(43, 15, 3, 8, '2026-05-15', '2026-06-15', 1, 0, 'Place Order'),
(44, 15, 3, 11, '2026-05-15', '2026-06-16', 1, 0, 'Place Order'),
(45, 15, 3, 8, '2026-05-15', '2026-05-31', 1, 0, 'Place Order'),
(46, 15, 1, 3, '2026-05-15', '2026-05-15', 1, 2, 'Place Order'),
(47, 15, 17, 14, '2026-05-19', '2026-05-31', 1, 0, 'Place Order');

-- --------------------------------------------------------

--
-- Table structure for table `tb_guest`
--

CREATE TABLE `tb_guest` (
  `guest_id` int(11) NOT NULL,
  `LastName` varchar(30) NOT NULL,
  `FirstName` varchar(30) NOT NULL,
  `MiddleName` varchar(30) NOT NULL,
  `ContactNo` varchar(15) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Username` varchar(30) NOT NULL,
  `Password` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_guest`
--

INSERT INTO `tb_guest` (`guest_id`, `LastName`, `FirstName`, `MiddleName`, `ContactNo`, `Address`, `Username`, `Password`, `status`) VALUES
(15, 'Guzman', 'Joeren Rey', 'Segura', '09669378933', 'Cayan Este', 'Joeren', 'Joeren123', 'Online now'),
(16, 'sdsfd', 'vdshgds', 'dfsfs', '09669378933', 'sdfsfs', 'joreren', '123123', 'Offline now'),
(17, 'ersrexs', 'resxg', 'treytd', '09669378933', 'qazesdx', 'rey', '123123', 'Online now');

-- --------------------------------------------------------

--
-- Table structure for table `tb_guest_records`
--

CREATE TABLE `tb_guest_records` (
  `record_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `num_visits` int(11) NOT NULL DEFAULT 1,
  `total_spent` double NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `preferences` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_invalid`
--

CREATE TABLE `tb_invalid` (
  `id` int(200) NOT NULL,
  `reportid` int(200) NOT NULL,
  `resortname` varchar(200) NOT NULL,
  `mun` varchar(200) NOT NULL,
  `district` varchar(200) NOT NULL,
  `remarks` varchar(1000) NOT NULL,
  `idate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `tb_invalid`
--

INSERT INTO `tb_invalid` (`id`, `reportid`, `resortname`, `mun`, `district`, `remarks`, `idate`) VALUES
(6, 12, 'J\'maire Farm', 'LAMBUNAO ', 'THIRD DISTRICT', 'Number of Customer not valid\n', '2022-11-19 13:51:30'),
(7, 14, 'J\'maire Farm', 'LAMBUNAO ', 'THIRD DISTRICT', 'Please Check your Records\n', '2022-11-01 06:15:09'),
(10, 15, 'J\'maire Farm', 'LAMBUNAO ', 'THIRD DISTRICT', 'Unable', '2022-11-01 10:41:14'),
(11, 15, 'J\'maire Farm', 'LAMBUNAO ', 'THIRD DISTRICT', 'Unable', '2022-11-01 10:42:13'),
(12, 15, 'J\'maire Farm', 'LAMBUNAO ', 'THIRD DISTRICT', 'Unable', '2022-11-01 10:43:08'),
(13, 15, 'J\'maire Farm', 'LAMBUNAO ', 'THIRD DISTRICT', 'Please Check', '2022-11-01 10:43:50'),

-- --------------------------------------------------------

--
-- Table structure for table `tb_location`
--

CREATE TABLE `tb_location` (
  `location_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lat` varchar(50) NOT NULL,
  `lon` varchar(50) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_location`
--

INSERT INTO `tb_location` (`location_id`, `resortid`, `name`, `lat`, `lon`, `address`) VALUES
(1, 5, 'Gines Garden Resort', '11.0891', '122.5446', 'Gines Garden Resort\r\nBrgy. Gines Lambunao Iloilo'),
(2, 4, 'Riverside Beach Resort', '10.6869', '122.4483', 'Riverside Beach Resort\r\nOton Iloilo\r\n'),
(3, 3, 'J\'maire Farm', '11.0743', '122.5238', 'J\'maire Farm\r\nSibaguan Lambunao, Iloilo'),
(5, 12, 'Jade Energetic Paradise Adventure Corp.', '10.9922', '122.5546', 'Jade Energetic Paradise Adventure Corp.\r\nCabayugan Badiangan, Iloilo'),
(6, 2, 'Turogban Inland Resort', '11.0285', '122.5454', 'Turogban Inland Resort\r\nBinaba-an Labayno Lambunao, Iloilo'),
(7, 1, 'Papa Doms Inland Resort', '11.0690', '122.5055', 'Papa Doms Inland Resort\r\nBrgy. Maite Grande Lambunao Iloilo');

-- --------------------------------------------------------

--
-- Table structure for table `tb_municipal`
--

CREATE TABLE `tb_municipal` (
  `adminid` int(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `tb_municipal`
--

INSERT INTO `tb_municipal` (`adminid`, `username`, `password`, `status`) VALUES
(1, 'admin', 'admin', 'Offline now');

-- --------------------------------------------------------

--
-- Table structure for table `tb_municipality`
--

CREATE TABLE `tb_municipality` (
  `id` int(200) NOT NULL,
  `mun` varchar(200) NOT NULL,
  `district` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT '',
  `last_activity` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `tb_municipality`
--

INSERT INTO `tb_municipality` (`id`, `mun`, `district`, `username`, `password`, `status`, `last_activity`) VALUES
(1, 'OTON', 'FIRST DISTRICT', 'OTON', 'OTON', 'Offline now', NULL),
(2, 'TIGBAUAN', 'FIRST DISTRICT', 'TIGBAUAN', 'TIGBAUAN', '', NULL),
(3, 'GUIMBAL', 'FIRST DISTRICT', 'GUIMBAL', 'GUIMBAL', 'Offline now', NULL),
(4, 'TUBUNGAN', 'FIRST DISTRICT', 'TUBUNGAN', 'TUBUNGAN', '', NULL),
(5, 'IGBARAS', 'FIRST DISTRICT', 'IGBARAS', 'IGBARAS', '', NULL),
(6, 'MIAGAO', 'FIRST DISTRICT', 'MIAGAO', 'MIAGAO', '', NULL),
(7, 'SAN JOAQUIN  ', 'FIRST DISTRICT', 'SANJOAQUIN', 'SANJOAQUIN', '', NULL),
(8, 'SAN MIGUEL', 'SECOND DISTRICT', 'SANMIGUEL', 'SANMIGUEL', '', NULL),
(9, 'ALIMODIAN', 'SECOND DISTRICT', 'ALIMODIAN', 'ALIMODIAN', '', NULL),
(10, 'LEON', 'SECOND DISTRICT', 'LEON', 'LEON', '', NULL),
(11, 'PAVIA', 'SECOND DISTRICT', 'PAVIA', 'PAVIA', '', NULL),
(12, 'SANTA BARBARA ', 'SECOND DISTRICT', 'SANTABARBARA ', 'SANTABARBARA ', '', NULL),
(13, 'NEW LUCENA ', 'SECOND DISTRICT', 'NEWLUCENA', 'NEWLUCENA', '', NULL),
(14, 'ZARRAGA', 'SECOND DISTRICT', 'ZARRAGA', 'ZARRAGA', '', NULL),
(15, 'LEGANES ', 'SECOND DISTRICT', 'LEGANES ', 'LEGANES ', '', NULL),
(16, 'CABATUAN', 'THIRD DISTRICT', 'CABATUAN', 'CABATUAN', '', NULL),
(17, 'MAASIN', 'THIRD DISTRICT', 'MAASIN', 'MAASIN', '', NULL),
(18, 'JANIUAY', 'THIRD DISTRICT', 'JANIUAY', 'JANIUAY', '', NULL),
(19, 'BADIANGAN', 'THIRD DISTRICT', 'BADIANGAN', 'BADIANGAN', '', NULL),
(20, 'LAMBUNAO ', 'THIRD DISTRICT', 'LAMBUNAO', 'LAMBUNAO', 'Online now', NULL),
(21, 'CALINOG', 'THIRD DISTRICT', 'CALINOG', 'CALINOG', '', NULL),
(22, 'BINGAWAN', 'THIRD DISTRICT', 'BINGAWAN', 'BINGAWAN', '', NULL),
(23, 'MINA', 'THIRD DISTRICT', 'MINA', 'MINA', '', NULL),
(24, 'POTOTAN', 'THIRD DISTRICT', 'POTOTAN', 'POTOTAN', '', NULL),
(25, 'DUMANGAS', 'FOURTH DISTRICT', 'DUMANGAS', 'DUMANGAS', '', NULL),
(26, 'BAROTAC NUEVO ', 'FOURTH DISTRICT', 'BAROTACNUEVO ', 'BAROTACNUEVO ', '', NULL),
(27, 'DINGLE', 'FOURTH DISTRICT', 'DINGLE', 'DINGLE', '', NULL),
(28, 'DUENAS ', 'FOURTH DISTRICT', 'DUENAS ', 'DUENAS ', '', NULL),
(29, 'PASSI CITY ', 'FOURTH DISTRICT', 'PASSI', 'PASSI', '', NULL),
(30, 'SAN ENRIQUE ', 'FOURTH DISTRICT', 'SANENRIQUE', 'SANENRIQUE', '', NULL),
(31, 'BANATE', 'FOURTH DISTRICT', '', '', '', NULL),
(32, 'BAROTAC VIEJO ', 'FIFTH DISTRICT', '', '', '', NULL),
(47, 'AJUY', 'FIFTH DISTRICT', 'AJUY', 'AJUY', '', NULL),
(34, 'CONCEPCION', 'FIFTH DISTRICT', '', '', '', NULL),
(35, 'SAN DIONISIO ', 'FIFTH DISTRICT', '', '', '', NULL),
(36, 'SARA', 'FIFTH DISTRICT', '', '', '', NULL),
(37, 'LEMERY', 'FIFTH DISTRICT', '', '', '', NULL),
(38, 'BATAD', 'FIFTH DISTRICT', '', '', '', NULL),
(39, 'ESTANCIA', 'FIFTH DISTRICT', '', '', '', NULL),
(40, 'BALASAN', 'FIFTH DISTRICT', 'BALASAN', 'BALASAN', '', NULL),
(41, 'CARLES', 'FIFTH DISTRICT', 'CARLES', 'CARLES', '', NULL),
(42, 'ANILAO', 'FIFTH DISTRICT', 'ANILAO', 'ANILAO', '', NULL),
(48, 'MANDURIAO', 'FIRST DISTRICT', 'MANDURIAO', 'MANDURIAO', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_placed_order`
--

CREATE TABLE `tb_placed_order` (
  `po_id` int(11) NOT NULL,
  `cart_id` int(20) NOT NULL,
  `adult_fee` int(20) NOT NULL,
  `kids_fee` int(20) NOT NULL,
  `total_fee` int(20) NOT NULL,
  `discount` double NOT NULL DEFAULT 0,
  `payment_method` varchar(100) NOT NULL,
  `message` varchar(2000) NOT NULL,
  `reservation_status` varchar(30) NOT NULL DEFAULT 'Pending',
  `reject_reason` varchar(1000) NOT NULL DEFAULT 'Contact Resort',
  `ratings` int(5) NOT NULL DEFAULT 0,
  `rating_comment` varchar(1000) NOT NULL DEFAULT 'Customer didn''t write anything.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_placed_order`
--

INSERT INTO `tb_placed_order` (`po_id`, `cart_id`, `adult_fee`, `kids_fee`, `total_fee`, `discount`, `payment_method`, `message`, `reservation_status`, `reject_reason`, `ratings`, `rating_comment`) VALUES
(18, 24, 240, 100, 1809, 0, 'Cash on Arrival', '', 'Reviewed', '', 5, 'gsdgsdg'),
(20, 26, 160, 0, 1629, 0, 'Cash on Arrival', '', 'Rejected', '', 0, 'Customer didn\'t write anything.'),
(21, 27, 240, 0, 1709, 0, 'Cash on Arrival', '', 'Cancelled', 'Contact Resort', 0, 'Customer didn\'t write anything.'),
(22, 33, 1, 2, 3, 0, 'Cash on Arrival', '', 'Pending', 'Contact Resort', 0, 'Customer didn\'t write anything.'),
(23, 35, 60, 0, 1260, 0, 'GCash', '', 'Rejected', 'sadad', 0, 'Customer didn\'t write anything.'),
(24, 34, 60, 105, 165, 0, 'Cash on Arrival', '', 'Rejected', 'asda', 0, 'Customer didn\'t write anything.'),
(25, 36, 360, 0, 1660, 0, 'Cash on Arrival', '', 'Reviewed', 'Contact Resort', 5, 'czxfsa'),
(26, 37, 480, 0, 779, 0, 'Cash on Arrival', '', 'Rejected', 'sorry\r\n', 0, 'Customer didn\'t write anything.'),
(27, 38, 360, 60, 849, 0, 'Cash on Arrival', '', 'Completed', 'Contact Resort', 0, 'Customer didn\'t write anything.'),
(28, 39, 90, 0, 2079, 0, 'Cash on Arrival', '', 'Completed', 'Contact Resort', 0, 'Customer didn\'t write anything.'),
(29, 41, 720, 0, 1019, 0, 'Cash on Arrival', '', 'PaymentApproval', 'Contact Resort', 0, 'Customer didn\'t write anything.'),
(30, 42, 450, 180, 2529, 0, 'Cash on Arrival', '', 'Rejected', 'sfdsaf', 0, 'Customer didn\'t write anything.'),
(31, 43, 60, 0, 27929, 0, 'Cash on Arrival', '', 'Rejected', 'sxzdsa', 0, 'Customer didn\'t write anything.'),
(32, 44, 60, 0, 1159, 0, 'Cash on Arrival', '', 'Reviewed', 'Contact Resort', 5, ''),
(33, 45, 60, 0, 15944, 0, 'Cash on Arrival', '', 'Completed', 'Contact Resort', 0, 'Customer didn\'t write anything.'),
(34, 46, 90, 120, 1309, 0, 'Cash on Arrival', '', 'Completed', 'Contact Resort', 0, 'Customer didn\'t write anything.'),
(35, 47, 0, 0, 56136, 0, 'Cash on Arrival', '', 'Reviewed', 'Contact Resort', 5, 'The Accomodations is OKay');

-- --------------------------------------------------------

--
-- Table structure for table `tb_provincial`
--

CREATE TABLE `tb_provincial` (
  `provid` int(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `tb_provincial`
--

INSERT INTO `tb_provincial` (`provid`, `username`, `password`, `status`) VALUES
(1, 'PROVINCE', 'PROVINCE', 'Online now');

-- --------------------------------------------------------

--
-- Table structure for table `tb_report`
--

CREATE TABLE `tb_report` (
  `id` int(200) NOT NULL,
  `reportid` int(11) NOT NULL DEFAULT 1000,
  `resortid` int(200) NOT NULL,
  `male_domestic` int(11) DEFAULT 0,
  `female_domestic` int(11) NOT NULL DEFAULT 0,
  `dcx_quan` int(200) NOT NULL,
  `male_foreign` int(11) NOT NULL DEFAULT 0,
  `female_foreign` int(11) NOT NULL DEFAULT 0,
  `fcx_quan` int(200) NOT NULL,
  `total_customer` int(11) NOT NULL,
  `rdate` varchar(12) NOT NULL,
  `rsales` float(10,2) NOT NULL,
  `rexpenses` float(10,2) NOT NULL,
  `rstatus` varchar(200) NOT NULL DEFAULT 'Pending',
  `date_validated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `tb_report`
--

INSERT INTO `tb_report` (`id`, `reportid`, `resortid`, `male_domestic`, `female_domestic`, `dcx_quan`, `male_foreign`, `female_foreign`, `fcx_quan`, `total_customer`, `rdate`, `rsales`, `rexpenses`, `rstatus`, `date_validated`) VALUES

(1, 1003, 1, 100, 70, 170, 0, 0, 0, 170, '2026-01-01', 10000.00, 25000.00, 'Validated', '2026-05-03 18:02:42'),
(2, 1004, 17, 1, 0, 1, 0, 0, 0, 1, '2026-05-01', 56136.00, 10000.00, 'Validated', '2026-05-17 19:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `tb_resort`
--

CREATE TABLE `tb_resort` (
  `resortid` int(200) NOT NULL,
  `resortname` varchar(200) NOT NULL,
  `resortaddress` varchar(200) NOT NULL,
  `mun` varchar(200) NOT NULL,
  `district` varchar(200) NOT NULL,
  `contact_no` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `status` varchar(15) NOT NULL,
  `isLocated` int(11) NOT NULL DEFAULT 0,
  `isFeatured` int(11) NOT NULL DEFAULT 0,
  `isTopItem` int(11) NOT NULL DEFAULT 0,
  `isBestSeller` int(11) NOT NULL DEFAULT 0,
  `isPromoDeals` int(11) NOT NULL DEFAULT 0,
  `isOnSale` int(11) NOT NULL DEFAULT 0,
  `adultEntranceFee` int(20) NOT NULL DEFAULT 0,
  `kidsEntranceFee` int(20) NOT NULL DEFAULT 0,
  `last_activity` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `tb_resort`
--

INSERT INTO `tb_resort` (`resortid`, `resortname`, `resortaddress`, `mun`, `district`, `contact_no`, `username`, `password`, `status`, `isLocated`, `isFeatured`, `isTopItem`, `isBestSeller`, `isPromoDeals`, `isOnSale`, `adultEntranceFee`, `kidsEntranceFee`, `last_activity`) VALUES
(1, 'Papa Dom\'\'s Resort', 'Maite Pequeno, Lambunao, Iloilo', 'LAMBUNAO', 'THIRD DISTRICT', '09123456789', 'PDOMS', 'PDOMS', 'Offline now', 1, 0, 0, 0, 0, 0, 90, 60, NULL),
(2, 'Turogban Inland Resort', 'Tuburan, Lambunao, Iloilo', 'LAMBUNAO', 'THIRD DISTRICT', '09462143875', 'TUROGBAN', 'TUROGBAN', 'Offline now', 1, 0, 0, 0, 0, 0, 120, 70, NULL),
(3, 'J\'maire Farm', 'Sibaguan, Lambunao, Iloilo', 'LAMBUNAO ', 'THIRD DISTRICT', '09124365879', 'JMAIRE', 'JMAIRE', 'Offline now', 1, 0, 0, 0, 0, 0, 60, 35, NULL),
(4, 'Riverside Beach Resort', 'Oton, Iloilo', 'OTON', 'FIRST DISTRICT', '09123456787', 'RIVERSIDE', 'RIVERSIDE', 'Offline now', 1, 0, 0, 0, 0, 0, 0, 0, NULL),
(5, 'Gines Garden Resort', 'Gines Lambunao, Iloilo', 'LAMBUNAO ', 'THIRD DISTRICT', '09866236224', 'GINES', 'GINES', 'Online now', 1, 0, 0, 0, 0, 0, 1, 1, NULL),
(11, 'Damires Hills', 'Janiuay, Iloilo', 'Janiuay', 'THIRD DISTRICT', '09981827333', 'DAMIRES', 'DAMIRES', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(12, 'Jade Energetic Paradise Adventure Corp.', 'Brgy. Cabayugan Badiangan, Iloilo', 'Badiangan', 'THIRD DISTRICT', '09097372637', 'JADE', 'JADE', 'Offline now', 1, 0, 0, 0, 0, 0, 0, 0, NULL),
(13, 'Shamrock Beach Resort', 'Guimbal, Iloilo', 'GUIMBAL', 'FIRST DISTRICT', '0907152558', 'SHAMROCK', 'SHAMROCK', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(14, 'La Conce Farm', 'Gines', 'LAMBUNAO ', 'THIRD DISTRICT', '09123456789', 'LACONCE', 'LACONCE', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(15, 'Tanie\'s Haven', 'Maribong, Lambunao', 'LAMBUNAO ', 'THIRD DISTRICT', '09123456789', 'TANIE', 'TANIE', '', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(16, 'Charles', 'Poblacion Ilawod', 'LAMBUNAO ', 'THIRD DISTRICT', '09123456789', 'CHARLES', 'CHARLES', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(17, 'Solina Beach and Nature Resort', 'Brgy. Guinticgan Carles', 'CARLES', 'FIFTH DISTRICT', '09123456789', 'SOLINA', 'SOLINA', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_resort_amenities`
--

CREATE TABLE `tb_resort_amenities` (
  `amenity_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `amenity_name` varchar(100) NOT NULL,
  `amenity_price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_resort_amenities`
--

INSERT INTO `tb_resort_amenities` (`amenity_id`, `resortid`, `amenity_name`, `amenity_price`) VALUES
(1, 1, 'Pillows', 50),
(2, 1, 'Blanket', 90),
(5, 3, 'Karaoke', 1500),
(6, 3, 'Karaoke', 1500),
(13, 1, 'Karaoke', 1500);

-- --------------------------------------------------------

--
-- Table structure for table `tb_resort_report`
--

CREATE TABLE `tb_resort_report` (
  `resort_report_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `report_file` varchar(1000) NOT NULL,
  `date_of_submission` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `report_status` varchar(20) NOT NULL DEFAULT 'For Validation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_resort_report`
--

INSERT INTO `tb_resort_report` (`resort_report_id`, `resortid`, `report_file`, `date_of_submission`, `report_status`) VALUES
(9, 3, 'IRJET-V7I4241 (1).pdf', '2022-09-14 07:58:53', 'Invalid'),
(10, 2, 'Upload.docx', '2022-09-14 21:19:44', 'Validated'),
(11, 2, 'pigs disease.docx', '2022-09-14 21:57:59', 'Validated'),
(12, 3, 'Design_and_Implementation_of_Poultry_Farming_Infor.pdf', '2022-10-29 06:33:05', 'Invalid'),
(13, 4, 'receipt.docx', '2022-10-01 06:33:11', 'For Validation');

-- --------------------------------------------------------

--
-- Table structure for table `tb_resort_room`
--

CREATE TABLE `tb_resort_room` (
  `resort_room_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `room_description` text DEFAULT NULL,
  `room_capacity` int(11) NOT NULL,
  `room_price` double NOT NULL,
  `room_status` varchar(20) NOT NULL,
  `resortid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_resort_room`
--

INSERT INTO `tb_resort_room` (`resort_room_id`, `room_name`, `room_description`, `room_capacity`, `room_price`, `room_status`, `resortid`) VALUES
(2, 'AC Room', 'HAHHA', 10, 1899, 'Available', 1),
(3, 'Cottage 1', NULL, 5, 399, 'Available', 1),
(4, 'Cottage Ilang-Ilang', NULL, 4, 299, 'Not Available', 2),
(7, 'Room 509', NULL, 10, 2999, 'Not Available', 4),
(8, 'Room 202', NULL, 3, 899, 'Available', 3),
(11, 'Room 204', NULL, 7, 1099, 'Available', 3),
(12, 'haha', NULL, 13, 1300, 'Available', 1),
(13, 'ROCKsolid', NULL, 5, 1000, 'Available', 13),
(14, 'Pod Dou', 'FREE WIFI\r\nFREE BREAKFAST', 2, 4678, 'Available', 17);

-- --------------------------------------------------------

--
-- Table structure for table `tb_staff`
--

CREATE TABLE `tb_staff` (
  `staff_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `position` varchar(50) NOT NULL,
  `hire_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_staff`
--

INSERT INTO `tb_staff` (`staff_id`, `resortid`, `first_name`, `last_name`, `email`, `phone`, `position`, `hire_date`, `status`, `created_at`) VALUES
(1, 17, 'Ryza Katren', 'Ann', 'ryzaannlebosada@gmail.com', '09669378933', 'Housekeeping', '2026-05-15', 'Active', '2026-05-17 19:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `tb_staff_schedule`
--

CREATE TABLE `tb_staff_schedule` (
  `schedule_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `shift_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_staff_schedule`
--

INSERT INTO `tb_staff_schedule` (`schedule_id`, `staff_id`, `resortid`, `shift_date`, `start_time`, `end_time`, `shift_type`, `status`, `created_at`) VALUES
(1, 1, 17, '2026-05-19', '03:52:00', '15:52:00', 'Day', 'Scheduled', '2026-05-17 19:52:54');

-- --------------------------------------------------------

--
-- Table structure for table `tb_task_assignment`
--

CREATE TABLE `tb_task_assignment` (
  `task_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `resortid` int(11) NOT NULL,
  `task_name` varchar(100) NOT NULL,
  `task_description` text DEFAULT NULL,
  `due_date` date NOT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'Medium',
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_task_assignment`
--

INSERT INTO `tb_task_assignment` (`task_id`, `staff_id`, `resortid`, `task_name`, `task_description`, `due_date`, `priority`, `status`, `created_at`, `completed_at`) VALUES
(1, 1, 17, 'Clean the Pod Dou Room', 'Clean all Mess created by a previous guest', '2026-05-19', 'High', 'Pending', '2026-05-17 19:52:33', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_add_on_amenities`
--
ALTER TABLE `tb_add_on_amenities`
  ADD PRIMARY KEY (`add_on_amenity_id`);

--
-- Indexes for table `tb_add_on_details`
--
ALTER TABLE `tb_add_on_details`
  ADD PRIMARY KEY (`add_on_details_id`);

--
-- Indexes for table `tb_analytics_bookings`
--
ALTER TABLE `tb_analytics_bookings`
  ADD PRIMARY KEY (`analytics_id`);

--
-- Indexes for table `tb_analytics_resource`
--
ALTER TABLE `tb_analytics_resource`
  ADD PRIMARY KEY (`resource_id`);

--
-- Indexes for table `tb_analytics_revenue`
--
ALTER TABLE `tb_analytics_revenue`
  ADD PRIMARY KEY (`revenue_id`);

--
-- Indexes for table `tb_cart`
--
ALTER TABLE `tb_cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `tb_guest`
--
ALTER TABLE `tb_guest`
  ADD PRIMARY KEY (`guest_id`);

--
-- Indexes for table `tb_guest_records`
--
ALTER TABLE `tb_guest_records`
  ADD PRIMARY KEY (`record_id`);

--
-- Indexes for table `tb_invalid`
--
ALTER TABLE `tb_invalid`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_location`
--
ALTER TABLE `tb_location`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `tb_resort_amenities`
--
ALTER TABLE `tb_resort_amenities`
  ADD PRIMARY KEY (`amenity_id`);

--
-- Indexes for table `tb_resort_room`
--
ALTER TABLE `tb_resort_room`
  ADD PRIMARY KEY (`resort_room_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_resort_amenities`
--
ALTER TABLE `tb_resort_amenities`
  MODIFY `amenity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tb_resort_room`
--
ALTER TABLE `tb_resort_room`
  MODIFY `resort_room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
