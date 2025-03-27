-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2025 at 02:03 AM
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
(11, 'Longevity Pay-Civilian', '5010212001');

-- --------------------------------------------------------

--
-- Table structure for table `approver`
--

CREATE TABLE `approver` (
  `approver_id` int(11) NOT NULL,
  `approver_name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approver`
--

INSERT INTO `approver` (`approver_id`, `approver_name`, `designation`) VALUES
(1, 'HAZEL E. HAUTEA', 'Chief Administrative Officer111'),
(2, 'EPIFANIA L. EALDAMA', 'OIC Division Chief, MSSD'),
(3, 'ROBERT A. ORFRECIO', 'Division Chief, SDD'),
(4, 'ELBERT G. CAPECIO', 'Division Chief, IDD'),
(5, 'MA. THERESA T. CHUA', 'Division Chief, CPD');

-- --------------------------------------------------------

--
-- Table structure for table `dv`
--

CREATE TABLE `dv` (
  `dv_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `ors_id` int(255) NOT NULL,
  `dv_no` varchar(50) DEFAULT NULL,
  `payment_mode` varchar(100) DEFAULT NULL,
  `vat` double(40,2) DEFAULT NULL,
  `vat_amount` double(40,2) NOT NULL,
  `tax_base` double(40,2) NOT NULL,
  `tax_1` double(40,2) DEFAULT NULL,
  `tax_1_amount` double(40,2) NOT NULL,
  `tax_2` double(40,2) DEFAULT NULL,
  `tax_2_amount` double(40,2) NOT NULL,
  `net_amount` double(40,2) DEFAULT NULL,
  `account_id` int(255) NOT NULL,
  `debit` double(40,2) NOT NULL,
  `credit` double(40,2) NOT NULL,
  `chief_accountant` varchar(255) DEFAULT NULL,
  `regional_director` varchar(255) DEFAULT NULL,
  `check_no` varchar(50) DEFAULT NULL,
  `bank_acc_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fund_cluster`
--

CREATE TABLE `fund_cluster` (
  `fund_cluster_id` int(11) NOT NULL,
  `fund_cluster_name` varchar(255) NOT NULL,
  `uacs_code` int(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fund_cluster`
--

INSERT INTO `fund_cluster` (`fund_cluster_id`, `fund_cluster_name`, `uacs_code`, `status`) VALUES
(3, 'Regular Agency Fund', 1, 'Active'),
(4, 'Foreign Assisted Projects Fund', 2, 'Active'),
(5, 'Special Account - Locally Funded/Domestic Grants Fund', 3, 'Active'),
(6, 'Special Account - Foreign Assisted/Foreign Grants Fund', 4, 'Active'),
(7, 'Internally Generated Funds', 5, 'Active'),
(8, 'Business Related Funds', 6, 'Active'),
(9, 'Trust Receipts ', 7, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `jev`
--

CREATE TABLE `jev` (
  `jev_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `dv_id` int(11) NOT NULL,
  `ors_no` varchar(255) NOT NULL,
  `dv_no` varchar(255) NOT NULL,
  `jev_no` varchar(255) NOT NULL,
  `administrative_aide` varchar(255) NOT NULL,
  `accountant` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `obligation_history`
--

CREATE TABLE `obligation_history` (
  `id` int(11) NOT NULL,
  `ors_id` int(255) NOT NULL,
  `project_id` int(255) NOT NULL,
  `net` double(40,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `obligation_history`
--

INSERT INTO `obligation_history` (`id`, `ors_id`, `project_id`, `net`) VALUES
(2, 41, 29, 1.00),
(3, 42, 29, 1.00),
(4, 43, 50, 1000.00),
(5, 44, 34, 1.00),
(6, 45, 51, 1000.00),
(7, 46, 50, 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `oopap`
--

CREATE TABLE `oopap` (
  `oopap_id` int(11) NOT NULL,
  `oopap_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `oopap`
--

INSERT INTO `oopap` (`oopap_id`, `oopap_name`, `description`) VALUES
(1, 'GAS', 'General Administration and Support'),
(2, 'OO1', 'Exports and Investment Program'),
(3, 'OO2', 'Industry Development Program'),
(4, 'OO3', 'MSME Development Program'),
(5, 'OO3.1', 'Negosyo Centers'),
(6, 'OO3.2', 'OTOP Next Gen'),
(7, 'OO3.3', 'Shared Service Facilities'),
(8, 'OO4.1.1', 'Monitoring and Enforcement'),
(9, 'OO4.1.2', 'Accreditation and Issuance of BN');

-- --------------------------------------------------------

--
-- Table structure for table `ors`
--

CREATE TABLE `ors` (
  `ors_id` int(11) NOT NULL,
  `fund_cluster_id` int(255) NOT NULL,
  `date` date NOT NULL,
  `ors_no` varchar(255) NOT NULL,
  `payee_id` int(255) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `rc_id` int(255) NOT NULL,
  `account_id` int(255) NOT NULL,
  `oopap_id` int(255) NOT NULL,
  `total_amount` double(40,2) NOT NULL,
  `approver_id` int(255) NOT NULL,
  `budget_officer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ors`
--

INSERT INTO `ors` (`ors_id`, `fund_cluster_id`, `date`, `ors_no`, `payee_id`, `notes`, `purpose`, `rc_id`, `account_id`, `oopap_id`, `total_amount`, `approver_id`, `budget_officer`) VALUES
(32, 3, '2025-03-12', 'RM-25-03-009', 6, 'Payment of water expense', 'To Payment of', 1, 1, 1, 10000.00, 5, 'CONNIE M. BARNACHEA'),
(38, 3, '2025-03-26', '123', 6, 's', 'To Payment of', 3, 1, 1, 111.00, 5, 'CONNIE M. BARNACHEA'),
(39, 3, '2025-03-26', '88888', 6, '888', 'To Payment of', 10, 1, 1, 88.00, 4, 'CONNIE M. BARNACHEA'),
(41, 3, '2025-03-06', '4444', 6, '44', 'To Payment of', 11, 1, 1, 1.00, 1, 'CONNIE M. BARNACHEA'),
(42, 3, '2025-03-19', '111', 6, '111', 'To Payment of', 5, 1, 1, 1.00, 3, 'CONNIE M. BARNACHEA'),
(43, 4, '2025-03-26', '789-097', 5, 'expense', 'To Payment of', 1, 5, 2, 1000.00, 5, 'CONNIE M. BARNACHEA'),
(44, 3, '2025-03-06', '999', 6, 'payment', 'To Payment of', 9, 1, 2, 1.00, 4, 'CONNIE M. BARNACHEA'),
(45, 3, '2025-03-26', 'Rm-25-03-001', 5, 'payment', 'To Payment of', 10, 9, 9, 1650.00, 2, 'CONNIE M. BARNACHEA'),
(46, 3, '2025-03-12', '123', 6, 'payment', 'To Payment of', 12, 5, 2, 15000.00, 3, 'CONNIE M. BARNACHEA');

-- --------------------------------------------------------

--
-- Table structure for table `payee`
--

CREATE TABLE `payee` (
  `payee_id` int(11) NOT NULL,
  `payee_name` varchar(255) NOT NULL,
  `tin_no` varchar(255) NOT NULL,
  `bank_acc_no` int(30) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payee`
--

INSERT INTO `payee` (`payee_id`, `payee_name`, `tin_no`, `bank_acc_no`, `address`) VALUES
(5, 'CINCO NIÑAS RESTO', '123-456', 123456789, 'CITY OF KORONADAL'),
(6, 'CITY OF KORONADAL WATER DISTRICT', '234-678', 34509684, 'CITY OF KORONADAL');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `oopap_id` int(11) NOT NULL,
  `account_id` int(15) NOT NULL,
  `allotment` double(40,2) NOT NULL,
  `balances` double(40,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`project_id`, `oopap_id`, `account_id`, `allotment`, `balances`) VALUES
(29, 1, 1, 12000.00, 11998.00),
(34, 2, 1, 123.00, 122.00),
(48, 1, 3, 1000.00, 1000.00),
(49, 3, 3, 111.00, 111.00),
(50, 2, 5, 100000.00, 84000.00),
(51, 9, 9, 100000.00, 99000.00);

-- --------------------------------------------------------

--
-- Table structure for table `responsibility_center`
--

CREATE TABLE `responsibility_center` (
  `rc_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `parent_code` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `acronym` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `responsibility_center`
--

INSERT INTO `responsibility_center` (`rc_id`, `code`, `parent_code`, `type`, `acronym`, `description`) VALUES
(1, '22-001-03-00012-03', '22-001-03-00012', 'Division', 'DTIAFMD', 'Administrative, Financial and Management Division'),
(2, '22-001-03-00012-04', '22-001-03-00012', 'Division', 'DTIIDD', 'Industry Development Division'),
(3, '22-001-03-00012-05', '22-001-03-00012', 'Division', 'DTISDD', 'SME Development Division'),
(4, '22-001-03-00012-06', '22-001-03-00012', 'Division', 'DTICPD', 'Consumer Protection Division'),
(5, '22-001-03-00012-09', '22-001-03-00012', 'Office', 'CARP', 'CARP'),
(6, '22-001-03-00012-10', '22-001-03-00012', 'Non Office', 'PPG', 'Regional Pangkabuhayan sa Pagbangon at Ginhawa'),
(7, '22-001-03-00012-11', '22-001-03-00012', 'Non Office ', 'GoNego', 'Regional GO Negosyo Center'),
(8, '22-001-03-00012-12', '22-001-03-00012', 'Non Office', 'LSP-NSB', 'Livelihood Seeding Program and Negosyo Serbisyo sa Barangay'),
(9, '22-001-03-00012-13', '22-001-03-00012', 'Local Projects', 'SSF', 'Shared Service Facility'),
(10, '22-001-03-00012-14', '22-001-03-00012', 'Non Office', 'RAPID-LP', 'RAPID Growth Project - Loan Proceeds'),
(11, '22-001-03-00012-15', '22-001-03-00012', 'Non Office ', 'RAPID-GoP', 'RAPID Growth Project -GoP Counterpart'),
(12, '22-001-03-00012-16', '22-001-03-00012', 'Local Projects', 'OTOP NEXT GEN', 'OTOP Next Generation'),
(13, '22-001-03-00012-17', '22-001-03-00012', 'Non Office', 'SAA', 'Sub-allotment');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Budget Officer','Chief Accountant','Bookkeeper','Guest') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(175710936, 'f', '', '$2y$10$A/PawVthwn7iKveGnxdNoOCuWZU4ZVUSD.8NNKPjc6rD0dsOA0NEy', '', NULL, NULL),
(194886581, '', '', '$2y$10$mfLfGhypXKIWT3EFzlYiaOBFVnMLQkJNYcGyEDLI8aCJM9rTCTzde', '', NULL, NULL),
(355348792, 'admin', 'admin', '$2y$10$9leHAHoC6pYrDImgzPc19uJJqHmYHfVE5OMLSjYmQDo9Jrg6ViOem', 'Admin', NULL, NULL),
(401635458, 'CA', 'CA', '$2y$10$skFllfT75nxiPhKzbP1B1OK1GXmSnv1EaZWN1HlYSJDqqKF2OuwUO', 'Chief Accountant', NULL, NULL),
(443457611, '', '', '$2y$10$XMMYeo9x57ObW5bJsr1i1.jdmOfEGfQM7.KpFST8vaK/jJ6841etq', '', NULL, NULL),
(484343948, '', '', '$2y$10$OCCPISPjKmpRVnPtZOTsiu.J1gBMPogfhGolD1gFwP3.rAwSsphXm', '', NULL, NULL),
(578675221, '', '', '$2y$10$bAhmU7HG5jvYv9rdLsUV.uXU.K31niTY5pSyLFFo5t9g6RtDAlDiK', '', NULL, NULL),
(653438109, 'BK', 'bk', '$2y$10$GUy2v3usvaqliVA8Q8TWeOs6DreFHb7zYjCxEa5jzY4VWnHRvf58W', 'Bookkeeper', NULL, NULL),
(669526780, 'G', 'g', '$2y$10$x/BokAg/bOHFwYnMLYpytue767.F6zAuGr9dsd.OXTx6eBjAKRMaS', 'Guest', NULL, NULL),
(770226996, 'BO', 'bo', '$2y$10$FaAVHcL8e0Vbh9txuHJ4KuDAf5thUtCmUJyFiSfy1fz5QuByisiT.', 'Budget Officer', NULL, NULL),
(771616702, '', '', '$2y$10$qVd8WxRZgO9G1hUEjPCDNOJ3HaIvz1HCOs3XxEsBlGEcj4rjAJl7q', '', NULL, NULL),
(877884626, '', '', '$2y$10$7SOSJw0SJ3p3Yb0lgRxwAO/O2EFMiDsFAHOBv3nx.xR.WnHI73l7K', '', NULL, NULL),
(941568985, 'Ritz Laraño', 'r', '$2y$10$ecM8TboeXRQGzC0Kgvf9guKopaZJA6egJtkqHbYCVwX5kQhTZ9lHO', 'Admin', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_title`
--
ALTER TABLE `account_title`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `approver`
--
ALTER TABLE `approver`
  ADD PRIMARY KEY (`approver_id`);

--
-- Indexes for table `dv`
--
ALTER TABLE `dv`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `ors_id` (`ors_id`),
  ADD KEY `object_code_id` (`account_id`);

--
-- Indexes for table `fund_cluster`
--
ALTER TABLE `fund_cluster`
  ADD PRIMARY KEY (`fund_cluster_id`);

--
-- Indexes for table `jev`
--
ALTER TABLE `jev`
  ADD PRIMARY KEY (`jev_id`),
  ADD KEY `dv_id` (`dv_id`);

--
-- Indexes for table `obligation_history`
--
ALTER TABLE `obligation_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ors_id` (`ors_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `oopap`
--
ALTER TABLE `oopap`
  ADD PRIMARY KEY (`oopap_id`);

--
-- Indexes for table `ors`
--
ALTER TABLE `ors`
  ADD PRIMARY KEY (`ors_id`),
  ADD KEY `fund_cluster_id` (`fund_cluster_id`),
  ADD KEY `rs_id` (`rc_id`),
  ADD KEY `object_code_id` (`account_id`),
  ADD KEY `approver_id` (`approver_id`),
  ADD KEY `oopap_id` (`oopap_id`),
  ADD KEY `payee_id` (`payee_id`);

--
-- Indexes for table `payee`
--
ALTER TABLE `payee`
  ADD PRIMARY KEY (`payee_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `oopap_id` (`oopap_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `responsibility_center`
--
ALTER TABLE `responsibility_center`
  ADD PRIMARY KEY (`rc_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_title`
--
ALTER TABLE `account_title`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `approver`
--
ALTER TABLE `approver`
  MODIFY `approver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dv`
--
ALTER TABLE `dv`
  MODIFY `dv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `fund_cluster`
--
ALTER TABLE `fund_cluster`
  MODIFY `fund_cluster_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jev`
--
ALTER TABLE `jev`
  MODIFY `jev_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `obligation_history`
--
ALTER TABLE `obligation_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `oopap`
--
ALTER TABLE `oopap`
  MODIFY `oopap_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ors`
--
ALTER TABLE `ors`
  MODIFY `ors_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `payee`
--
ALTER TABLE `payee`
  MODIFY `payee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `responsibility_center`
--
ALTER TABLE `responsibility_center`
  MODIFY `rc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=941568986;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dv`
--
ALTER TABLE `dv`
  ADD CONSTRAINT `dv_ibfk_1` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jev`
--
ALTER TABLE `jev`
  ADD CONSTRAINT `jev_ibfk_1` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `obligation_history`
--
ALTER TABLE `obligation_history`
  ADD CONSTRAINT `obligation_history_ibfk_1` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `obligation_history_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ors`
--
ALTER TABLE `ors`
  ADD CONSTRAINT `ors_ibfk_1` FOREIGN KEY (`fund_cluster_id`) REFERENCES `fund_cluster` (`fund_cluster_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_2` FOREIGN KEY (`rc_id`) REFERENCES `responsibility_center` (`rc_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_4` FOREIGN KEY (`approver_id`) REFERENCES `approver` (`approver_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_5` FOREIGN KEY (`oopap_id`) REFERENCES `oopap` (`oopap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_7` FOREIGN KEY (`payee_id`) REFERENCES `payee` (`payee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_8` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`oopap_id`) REFERENCES `oopap` (`oopap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
