-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 04:59 AM
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
-- Database: `dti-php`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_title`
--

CREATE TABLE `account_title` (
  `account_id` int(11) NOT NULL,
  `account_title` varchar(255) NOT NULL,
  `account_code` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_title`
--

INSERT INTO `account_title` (`account_id`, `account_title`, `account_code`) VALUES
(1, 'Water Expenses', '5020401000'),
(3, 'Cash - Collecting Officer', '1010101000'),
(4, 'Basic Salary-Civilian', '5010101001'),
(5, 'Salaries and Wages - Casual/Contractual', '5010102000'),
(6, 'PERA-Civilian', '5010201001'),
(7, 'Representation Allowance (RA)', '5010202000'),
(8, 'Transportation Allowance (TA)', '5010203001'),
(9, 'Clothing/Uniform Allowance-Civilian', '5010204001'),
(10, 'Productivity Incentive Allowance-Civilian', '5010208001'),
(11, 'Longevity Pay-Civilian', '5010212001'),
(13, 'Year-End Bonus-Civilian', '5010214001'),
(14, 'Cash Gift-Civilian', '5010215001'),
(15, 'Mid-Year Bonus', '5010216001'),
(16, 'Collective Negotiation Agreement Incentive-Civilian', '5010299011'),
(17, 'Productivity Enhancement Incentive-Civilian', '5010299012'),
(18, 'Performance Based Bonus-Civilian', '5010299014'),
(19, 'Advances for Operating Expenses', '1990101000'),
(20, 'Advances to Special Disbursing Officer', '1990103000'),
(21, 'Advances to Officers and Employees', '1990104000'),
(22, 'Advances to Contractors', '1990201000'),
(23, 'Petty Cash', '1010102000'),
(24, 'Cash in Bank - Local Currency, Current Account-Land Bank of the Philippines (LBP)', '1010202024'),
(25, 'Cash-Treasury/Agency Deposit, Regular', '1010401000'),
(26, 'Cash-Treasury/Agency Deposit, Special Account', '1010402000'),
(27, 'Cash-Treasury/Agency Deposit, Trust', '1010403000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_title`
--
ALTER TABLE `account_title`
  ADD PRIMARY KEY (`account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_title`
--
ALTER TABLE `account_title`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
