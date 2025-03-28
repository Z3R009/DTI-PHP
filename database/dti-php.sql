-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 02:44 AM
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
(245, 'Cash - Collecting Officers', '1010101000'),
(246, 'Petty Cash Fund', '1010102000'),
(247, 'Cash in Bank - Local Currency, Current Account', '1010202024'),
(248, 'Cash - National Treasury - MDS', '1010404000'),
(249, 'Loans Receivables-Others', '1030101000'),
(250, 'Due from NGAs', '1030301000'),
(251, 'Due from NGOs and POs', '1030303000'),
(252, 'Receivables - Disallowance Charges', '1030501000'),
(253, 'Due from Operating Units', '1030502000'),
(254, 'Other Receivables', '1030599000'),
(255, 'Office Supplies Inventory', '1040401000'),
(256, 'Other Supplies Inventory', '1040499000'),
(257, 'Machineries', '1060501000'),
(258, 'Accumulated Depreciation - Machineries', '1060501100'),
(259, 'Office Equipment', '1060502000'),
(260, 'Accumulated Depreciation - Office Equipment', '1060502100'),
(261, 'IT Equipment and Software', '1060503000'),
(262, 'Accumulated Depreciation - IT Equipment and Software', '1060503100'),
(263, 'Communication Equipment', '1060507000'),
(264, 'Accumulated Depreciation - Communication Equipment', '1060507100'),
(265, 'Other Machineries and Equipment', '1060599000'),
(266, 'Accumulated Depreciation - Other Machineries & Equipt', '1060599100'),
(267, 'Motor Vehicles', '1060601000'),
(268, 'Accumulated Depreciation - Motor Vehicles', '1060601100'),
(269, 'Furniture and Fixtures', '1060701000'),
(270, 'Accumulated Depreciation - Furniture and Fixtures', '1060701100'),
(271, 'Library Books', '1080103000'),
(272, 'Accumulated Depreciation - Library Books', '1080103100'),
(273, 'Advances to Officers and Employees', '1990101000'),
(274, 'Prepaid Rent', '1990202000'),
(275, 'Other Assets', '1999999000'),
(276, 'Accounts Payables', '2010101000'),
(277, 'Due to Officers & Employees', '2010102000'),
(278, 'Due to BIR', '2020101000'),
(279, 'Due to GSIS', '2020102000'),
(280, 'Due to PAG-IBIG', '2020103000'),
(281, 'Due to PHILHEALTH', '2020104000'),
(282, 'Due to Central Office', '2030101000'),
(283, 'Other Payables', '2999999000'),
(284, 'Government Equity', '3010101000'),
(285, 'Income and Expense Summary', '3030101000'),
(286, 'Permit Fees', '4020101099'),
(287, 'Registration Fees', '4020102000'),
(288, 'Clearance and Certification Fees', '4020104002'),
(289, 'Other Permits and Licenses', '4020107000'),
(290, 'Processing Fees', '4020111001'),
(291, 'Fines and Penalties - Service Income ', '4020114000'),
(292, 'Other Service Income ', '4020199099'),
(293, 'Subsidy Income from National Government', '4030101000'),
(294, 'Gain/Loss on Sale of Disposed Asset', '4050104000'),
(295, 'Salaries and Wages - Regular Pay', '5010101001'),
(296, 'Salaries and Wages - Contractual', '5010102000'),
(297, 'Personal Economic Relief Allowance (PERA)', '5010201001'),
(298, 'Representation Allowance (RA)', '5010202000'),
(299, 'Transportation Allowance (TA)', '5010203001'),
(300, 'Clothing/Uniform Allowance', '5010204001'),
(301, 'Productivity Incentive Benefits', '5010208001'),
(302, 'Longevity Pay', '5010212001'),
(303, 'Year-End Bonus', '5010214001'),
(304, 'Cash Gift', '5010215001'),
(305, 'Life and Retirement Insurance Contributions', '5010301000'),
(306, 'PAG-IBIG Contributions', '5010302001'),
(307, 'PHILHEALTH Contributions', '5010303001'),
(308, 'ECC Contributions', '5010304001'),
(309, 'Terminal Leave Credits', '5010403001'),
(310, 'Other Personal Benefits', '5010499099'),
(311, 'Travelling Expenses - Local', '5020101000'),
(312, 'Traveling Expenses - Foreign', '5020102000'),
(313, 'Training Expenses', '5020201000'),
(314, 'Office Supplies Expenses', '5020301000'),
(315, 'Accountable Forms Expenses', '5020302000'),
(316, 'Gasoline, Oil And Lubricants Expense', '5020309000'),
(317, 'Other Supplies Expenses', '5020399000'),
(318, 'Water Expenses', '5020401000'),
(319, 'Electricity Expenses', '5020402000'),
(320, 'Postage and Deliveries', '5020501000'),
(321, 'Telephone Expenses - Landline', '5020502001'),
(322, 'Internet Expenses', '5020503000'),
(323, 'Cable, Satellite, Telegraph and Radio Expenses', '5020504000'),
(324, 'Extraordinary Expenses', '5021003000'),
(325, 'Legal Services', '5021101000'),
(326, 'Auditing Services', '5021102000'),
(327, 'Consultancy Services', '5021103000'),
(328, 'Other Professional Services', '5021199000'),
(329, 'Janitorial Services', '5021202000'),
(330, 'Security Services', '5021203000'),
(331, 'General Services', '5021299000'),
(332, 'Repairs and Maintenance - Office Equipment', '5021305000'),
(333, 'Repairs and Maintenance - Motor Vehicles', '5021306001'),
(334, 'Repairs and Maintenance - Furniture and Fixtures', '5021307000'),
(335, 'Leasehold Improvements', '5021309002'),
(336, 'Other Subsidies', '5021403000'),
(337, 'Taxes, Duties and Licenses', '5021501001'),
(338, 'Fidelity Bond Premium', '5021502000'),
(339, 'Insurance Expenses', '5021503000'),
(340, 'Advertising Expenses', '5029901000'),
(341, 'Printing and Binding Expenses', '5029902000'),
(342, 'Representation Expenses', '5029903000'),
(343, 'Transportation and Delivery Expenses', '5029904000'),
(344, 'Rent Expenses', '5029905001'),
(345, 'Subsciptions Expenses', '5029907000'),
(346, 'Other Maintenance and Operating Expenses', '5029999002'),
(347, 'Bank Charges', '5030104000'),
(348, 'Depreciation - Office Equipment', '5050105000'),
(349, 'Depreciation - Motor Vehicles', '5050106001'),
(350, 'Depreciation - Furniture and Fixtures', '5050107000'),
(351, 'Depreciation-Other Asset', '5050199099'),
(352, 'Documentary Stamp Expenses', '5050399000'),
(353, 'Prior Years Adjustments', '5050409000'),
(355, 'Fuel, Oil and Lubricants Expenses', '5020309000'),
(356, 'Fuel, Oil and Lubricants Expenses', '5020309000'),
(357, 'Telephone Expenses-Mobile', '5020502001'),
(358, 'Other General Services', '5021299000'),
(359, 'Repairs and Maintenance - Machinery and Equipment', '5021305000'),
(360, 'Repair and Maintenance - ICT Equipments', '5021305003'),
(361, 'Repairs and Maintenance - Leased Assets, Buildings and Other Structures', '5021308001'),
(362, 'Rents-Buildings and Structures', '5029905001'),
(363, 'Rents-Motor Vehicles', '5029905003'),
(364, 'Rents-Equipment', '5029905004'),
(365, 'Rents-Living Quarters', '5029905005'),
(366, 'Subscription Expenses', '5029907000');

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
(1, 'HAZEL E. HAUTEA', 'Chief Administrative Officer'),
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
(6, 'CITY OF KORONADAL WATER DISTRICT', '234-678', 34509684, 'CITY OF KORONADAL'),
(8, 'MARBEL TELEPHONE SYSTEM, INC.', '11', 11, 'CITY OF KORONADAL'),
(9, 'SMART COMMUNICATIONS, INC.', '22', 22, 'CITY OF KORONADAL');

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
(56, 1, 311, 1562000.00, 1562000.00),
(57, 1, 313, 800000.00, 800000.00),
(58, 1, 314, 196000.00, 196000.00),
(59, 1, 315, 21000.00, 21000.00),
(60, 1, 355, 130000.00, 130000.00),
(61, 1, 317, 140000.00, 140000.00),
(63, 1, 318, 200000.00, 200000.00),
(64, 1, 319, 200000.00, 200000.00),
(65, 1, 320, 50000.00, 50000.00),
(66, 1, 357, 125000.00, 125000.00),
(67, 1, 321, 155000.00, 155000.00),
(68, 1, 322, 117000.00, 117000.00),
(69, 1, 324, 136000.00, 136000.00),
(70, 1, 325, 15000.00, 15000.00),
(71, 1, 326, 60000.00, 60000.00),
(72, 1, 327, 50000.00, 50000.00),
(73, 1, 328, 250000.00, 250000.00),
(74, 1, 329, 4571000.00, 4571000.00),
(75, 1, 330, 2464000.00, 2464000.00),
(76, 1, 358, 282000.00, 282000.00),
(77, 1, 359, 17000.00, 17000.00),
(78, 1, 332, 10000.00, 10000.00),
(79, 1, 360, 5000.00, 5000.00),
(80, 1, 361, 190000.00, 190000.00),
(81, 1, 333, 400000.00, 400000.00),
(82, 1, 337, 22000.00, 22000.00),
(83, 1, 338, 150000.00, 150000.00),
(84, 1, 339, 507000.00, 507000.00),
(85, 1, 340, 15000.00, 15000.00),
(86, 1, 341, 21000.00, 21000.00),
(87, 1, 342, 500000.00, 500000.00),
(88, 1, 343, 15000.00, 15000.00),
(89, 1, 362, 2535000.00, 2535000.00),
(90, 1, 363, 26000.00, 26000.00),
(91, 1, 345, 68000.00, 68000.00),
(92, 1, 346, 200000.00, 200000.00);

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
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=367;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `oopap`
--
ALTER TABLE `oopap`
  MODIFY `oopap_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ors`
--
ALTER TABLE `ors`
  MODIFY `ors_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `payee`
--
ALTER TABLE `payee`
  MODIFY `payee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

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
