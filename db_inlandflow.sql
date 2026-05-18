-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 02:36 AM
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
  `file_name` varchar(255) NOT NULL,
  `file_description` varchar(1000) NOT NULL DEFAULT 'No Description',
  `uploaded_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('1','0') NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `resortid`, `file_name`, `file_description`, `uploaded_on`, `status`) VALUES
(59, 3, 'jmaire-01.jpg', 'Pool', '2022-09-27 04:20:21', '1'),
(57, 3, 'jmaire-05.jpg', 'Pavilion', '2022-09-27 05:03:43', '1'),
(53, 1, 'doms-1.jpg', '', '2022-08-30 11:20:45', '1'),
(54, 1, 'doms-5.jpg', '', '2022-08-30 11:21:37', '1'),
(55, 1, 'doms-7.jpg', '', '2022-08-30 11:24:16', '1'),
(60, 2, 't-1.jpg', '', '2022-08-30 11:32:15', '1'),
(61, 2, 't-5.jpg', '', '2022-08-30 11:32:26', '1'),
(62, 4, 'r-1.jpg', '', '2022-08-30 11:33:17', '1'),
(63, 4, 'r-2.jpg', '', '2022-08-30 11:33:26', '1'),
(71, 1, 'doms-4.jpg', '', '2022-09-01 07:15:18', '1'),
(72, 1, 'doms-8.jpg', '', '2022-09-01 07:15:18', '1'),
(73, 12, '1669825818838.jpg', 'JEPAC Pool and Slides', '2022-11-21 04:05:42', '1'),
(74, 12, '1669825829403.jpg', 'JEPAC Activities', '2022-11-21 04:05:59', '1'),
(85, 11, '1669827088165.jpg', 'No Description', '2022-11-21 00:53:39', '1'),
(86, 13, '1670769762813.jpg', 'No Description', '2022-11-22 01:49:52', '1'),
(78, 5, '1669827130540.jpg', 'No Description', '2022-11-21 04:16:21', '1'),
(79, 5, '1669827142486.jpg', 'No Description', '2022-11-21 04:16:21', '1'),
(80, 5, '1669827148569.jpg', 'No Description', '2022-11-21 04:16:21', '1'),
(87, 13, '1670769768751.jpg', 'No Description', '2022-11-22 01:49:52', '1'),
(83, 3, 'jmaire-03.jpg', 'No Description', '2022-11-21 00:10:43', '1'),
(88, 13, '1670769773006.jpg', 'No Description', '2022-11-22 01:49:52', '1');

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
(0, 0, 'Papa Dom\'\'s Resort', 'LAMBUNAO', 'THIRD DISTRICT', 'Sorry', '2026-05-03 17:31:53');

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
(40, 'BALASAN', 'FIFTH DISTRICT', '', '', '', NULL),
(41, 'CARLES', 'FIFTH DISTRICT', '', '', '', NULL),
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
(2, 'Turogban Inland Resort', 'Tuburan, Lambunao, Iloilo', 'LAMBUNAO', 'THIRD DISTRICT', '09462143875', 'TUROGBAN', 'TUROGBAN', 'Online now', 1, 0, 0, 0, 0, 0, 120, 70, NULL),
(3, 'J\'maire Farm', 'Sibaguan, Lambunao, Iloilo', 'LAMBUNAO ', 'THIRD DISTRICT', '09124365879', 'JMAIRE', 'JMAIRE', 'Offline now', 1, 0, 0, 0, 0, 0, 60, 35, NULL),
(4, 'Riverside Beach Resort', 'Oton, Iloilo', 'OTON', 'FIRST DISTRICT', '09123456787', 'RIVERSIDE', 'RIVERSIDE', 'Offline now', 1, 0, 0, 0, 0, 0, 0, 0, NULL),
(5, 'Gines Garden Resort', 'Gines Lambunao, Iloilo', 'LAMBUNAO ', 'THIRD DISTRICT', '09866236224', 'GINES', 'GINES', 'Online now', 1, 0, 0, 0, 0, 0, 1, 1, NULL),
(11, 'Damires Hills', 'Janiuay, Iloilo', 'Janiuay', 'THIRD DISTRICT', '09981827333', 'DAMIRES', 'DAMIRES', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(12, 'Jade Energetic Paradise Adventure Corp.', 'Brgy. Cabayugan Badiangan, Iloilo', 'Badiangan', 'THIRD DISTRICT', '09097372637', 'JADE', 'JADE', 'Offline now', 1, 0, 0, 0, 0, 0, 0, 0, NULL),
(13, 'Shamrock Beach Resort', 'Guimbal, Iloilo', 'GUIMBAL', 'FIRST DISTRICT', '0907152558', 'SHAMROCK', 'SHAMROCK', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(14, 'La Conce Farm', 'Gines', 'LAMBUNAO ', 'THIRD DISTRICT', '09123456789', 'LACONCE', 'LACONCE', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(15, 'Tanie\'s Haven', 'Maribong, Lambunao', 'LAMBUNAO ', 'THIRD DISTRICT', '09123456789', 'TANIE', 'TANIE', '', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(0, 'Tanies1', 'Maribong', 'LAMBUNAO ', 'THIRD DISTRICT', '09123456789', 'TANIES1', 'TANIES1', '', 0, 0, 0, 0, 0, 0, 0, 0, NULL),
(16, 'Charles', 'Poblacion Ilawod', 'LAMBUNAO ', 'THIRD DISTRICT', '09123456789', 'CHARLES', 'CHARLES', 'Offline now', 0, 0, 0, 0, 0, 0, 0, 0, NULL);

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
(6, 3, 'Karaoke', 1500);

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
(2, 'AC Room', NULL, 10, 1899, 'Not Available', 1),
(3, 'Cottage 1', NULL, 5, 399, 'Not Available', 1),
(4, 'Cottage Ilang-Ilang', NULL, 4, 299, 'Available', 2),
(7, 'Room 509', NULL, 10, 2999, 'Not Available', 4),
(8, 'Room 202', NULL, 3, 899, 'Available', 3),
(11, 'Room 204', NULL, 7, 1099, 'Available', 3),
(12, 'haha', NULL, 13, 1300, 'Available', 1);

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
  MODIFY `amenity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tb_resort_room`
--
ALTER TABLE `tb_resort_room`
  MODIFY `resort_room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
