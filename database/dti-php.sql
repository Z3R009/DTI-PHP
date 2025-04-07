-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2025 at 09:27 AM
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
(357, 'Telephone Expenses-Mobile', '5020502001'),
(358, 'Other General Services', '5021299000'),
(359, 'Repairs and Maintenance - Machinery and Equipment', '5021305000'),
(360, 'Repair and Maintenance - ICT Equipments', '5021305003'),
(361, 'Repairs and Maintenance - Leased Assets, Buildings and Other Structures', '5021308001'),
(362, 'Rents-Buildings and Structures', '5029905001'),
(363, 'Rents-Motor Vehicles', '5029905003'),
(364, 'Rents-Equipment', '5029905004'),
(365, 'Rents-Living Quarters', '5029905005'),
(366, 'Subscription Expenses', '5029907000'),
(367, 'Postage and Courier Services', '5020501000'),
(368, 'Internet Subscription Expenses', '5020503000'),
(369, 'AWARDS/REWARDS EXPENSES', '5020601000'),
(370, 'EXTRAORDINARY AND MISCELLANEOUS EXPENSE', '5021030000'),
(371, 'Repair and Maintenance - Machinery ', '5021305001'),
(372, 'Repair and Maintenance - Office Equipments ', '5021305002'),
(373, 'Repair and Maintenance - BUILDINGS', '5021308001'),
(374, 'Printing and Publication Expenses', '5021202000'),
(375, 'Rents - Motor Vehicles', '5029990002'),
(376, 'Other Subscription Expenses', '5029907000'),
(379, 'test', '50000'),
(380, 'test2', '5001'),
(381, 'test3', '5000000000'),
(382, 'Cash -  Modified Disbursement System (MDS), Regular', '1010404000'),
(383, 'PERA', '5010201001'),
(384, 'BONUS', '5010214001'),
(385, 'MID - YEAR BONUS', '5010299036'),
(386, 'Productivity Enhancement Incentive (PEI)', '5010299012'),
(387, 'GSIS - ECIP', '5010304001'),
(388, 'Lump-Sum for Step Increments - Length of Service', '5010499010'),
(389, 'GSIS - RLIP', '5010301000'),
(390, 'ICT Equipment', '1060503000'),
(391, 'Office Expense', '5021103000'),
(392, 'Postage & Deliveries Expenses', '5029905001'),
(393, 'Other MOOE', '50299990'),
(394, 'RM - Motor Vehicles', '5021306001'),
(395, 'Other Supplies and Materials', '5020399000');

-- --------------------------------------------------------

--
-- Table structure for table `approver`
--

CREATE TABLE `approver` (
  `approver_id` int(11) NOT NULL,
  `approver_name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `sub_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approver`
--

INSERT INTO `approver` (`approver_id`, `approver_name`, `designation`, `sub_title`) VALUES
(1, 'HAZEL E. HAUTEA', 'Chief Administrative Officer', 'Head, Requesting Office/Authorized Representative'),
(2, 'EPIFANIA L. EALDAMA', 'OIC Division Chief, MSSD', ''),
(3, 'ROBERT A. ORFRECIO', 'Division Chief, SDD', ''),
(4, 'ELBERT G. CAPECIO', 'Division Chief, IDD', ''),
(5, 'MA. THERESA T. CHUA', 'Division Chief, CPD', '');

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
  `total_amount` double(40,2) NOT NULL,
  `chief_accountant` varchar(255) DEFAULT NULL,
  `regional_director` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dv`
--

INSERT INTO `dv` (`dv_id`, `date`, `ors_id`, `dv_no`, `payment_mode`, `vat`, `vat_amount`, `tax_base`, `tax_1`, `tax_1_amount`, `tax_2`, `tax_2_amount`, `net_amount`, `total_amount`, `chief_accountant`, `regional_director`) VALUES
(46, '2025-04-10', 61, '1-25-04-002', 'Commercial Check', 12.00, 0.00, 1000.00, 3.00, 30.00, 1.00, 10.00, 960.00, 0.00, 'NEIL ANTHONY T. MORALA', 'FLORA D. POLITUD-GABUNALES, CESO V');

-- --------------------------------------------------------

--
-- Table structure for table `dv_history`
--

CREATE TABLE `dv_history` (
  `dvhis_id` int(11) NOT NULL,
  `dv_id` int(255) NOT NULL,
  `account_id` int(255) NOT NULL,
  `type` enum('debit','credit') NOT NULL,
  `amount` double(40,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dv_history`
--

INSERT INTO `dv_history` (`dvhis_id`, `dv_id`, `account_id`, `type`, `amount`) VALUES
(4, 46, 318, 'debit', 1000.00),
(5, 46, 278, 'credit', 30.00),
(6, 46, 278, 'credit', 10.00),
(7, 46, 382, 'credit', 960.00);

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
  `ors_id` int(255) NOT NULL,
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
(26, 61, 63, 1000.00);

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
(1, 'MOOE-GAS', 'General Administration and Support'),
(2, 'OO1', 'Exports and Investment Program'),
(3, 'OO2', 'Industry Development Program'),
(4, 'OO3', 'MSME Development Program'),
(5, 'OO3.1', 'Negosyo Centers'),
(6, 'OO3.2', 'OTOP Next Gen'),
(7, 'OO3.3', 'Shared Service Facilities'),
(8, 'OO4.1.1', 'Monitoring and Enforcement'),
(9, 'OO4.1.2', 'Accreditation and Issuance of BN'),
(11, 'OO1', 'Personnel Services'),
(12, 'OO2', 'Tida Contractual'),
(13, 'OO3', 'Carp'),
(14, 'O1', 'Rapid LP 2024 Continuing RO 12'),
(15, 'O2', 'Rapid LP 2024 Continuing ');

-- --------------------------------------------------------

--
-- Table structure for table `ors`
--

CREATE TABLE `ors` (
  `ors_id` int(11) NOT NULL,
  `fund_cluster_id` int(255) NOT NULL,
  `services_id` int(255) NOT NULL,
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

INSERT INTO `ors` (`ors_id`, `fund_cluster_id`, `services_id`, `date`, `ors_no`, `payee_id`, `notes`, `purpose`, `rc_id`, `account_id`, `oopap_id`, `total_amount`, `approver_id`, `budget_officer`) VALUES
(61, 3, 7, '2025-04-04', 'RR-25-04-001', 17, 'Payment for water expense', 'To Payment of', 9, 318, 1, 1000.00, 1, 'CONNIE M. BARNACHEA');

-- --------------------------------------------------------

--
-- Table structure for table `payee`
--

CREATE TABLE `payee` (
  `payee_id` int(11) NOT NULL,
  `payee_name` varchar(255) NOT NULL,
  `tin_no` varchar(255) DEFAULT NULL,
  `bank_acc_no` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `nature` varchar(255) DEFAULT NULL,
  `contact_no` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payee`
--

INSERT INTO `payee` (`payee_id`, `payee_name`, `tin_no`, `bank_acc_no`, `address`, `nature`, `contact_no`) VALUES
(16, '3G Gensan Hotel', '', '', '', '', ''),
(17, '3D Advertising', '712-786-936-0000', '', '', '', ''),
(18, '8 OZ Prints', '', 'LBP - 0751-2051-55', '', '', ''),
(19, 'ACE CENTERPOINT', '', 'LBP - CA 0752-1040-93', '', '', ''),
(20, 'ACE HARDWARE PHIL. INC.', '200-035-311-0000', 'LBP - CA 0752-1040-93', '', '', ''),
(21, 'ADC AUTOMOTIVE SHOP BY ANNIE LIZA R CERALVO', '', 'LBP - SA 0751-1741-01', '', '', ''),
(22, 'ADWERKZ PRINTING SOLUTIONS/PAUL RYAN C. BARCELONA', '', 'LBP - SA 3416-0029-23', '', '', ''),
(23, 'AFL 168 CORPORATION', '', 'LBP - SA 0751-1769-37', '', '', ''),
(24, 'AHR WOODCRAFT CENTER BY ALLAN B. HIMALLA ', '', 'LBP - SA 0751-1759-90', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `oopap_id` int(11) NOT NULL,
  `account_id` int(15) NOT NULL,
  `allotment` double(40,2) NOT NULL,
  `balances` double(40,2) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`project_id`, `oopap_id`, `account_id`, `allotment`, `balances`, `created_at`) VALUES
(56, 1, 311, 1562000.00, 1562000.00, '2025-03-31'),
(57, 1, 313, 800000.00, 800000.00, '2025-03-31'),
(58, 1, 314, 196000.00, 196000.00, '2025-03-31'),
(59, 1, 315, 21000.00, 21000.00, '2025-03-31'),
(60, 1, 355, 130000.00, 130000.00, '2025-03-31'),
(61, 1, 317, 140000.00, 140000.00, '2025-03-31'),
(63, 1, 318, 200000.00, 199000.00, '2025-03-31'),
(64, 1, 319, 200000.00, 200000.00, '2025-03-31'),
(65, 1, 320, 50000.00, 50000.00, '2025-03-31'),
(66, 1, 357, 125000.00, 125000.00, '2025-03-31'),
(67, 1, 321, 155000.00, 155000.00, '2025-03-31'),
(68, 1, 322, 117000.00, 117000.00, '2025-03-31'),
(69, 1, 324, 136000.00, 136000.00, '2025-03-31'),
(70, 1, 325, 15000.00, 15000.00, '2025-03-31'),
(71, 1, 326, 60000.00, 60000.00, '2025-03-31'),
(72, 1, 327, 50000.00, 50000.00, '2025-03-31'),
(73, 1, 328, 250000.00, 250000.00, '2025-03-31'),
(74, 1, 329, 4571000.00, 4571000.00, '2025-03-31'),
(75, 1, 330, 2464000.00, 2464000.00, '2025-03-31'),
(76, 1, 358, 282000.00, 282000.00, '2025-03-31'),
(77, 1, 359, 17000.00, 17000.00, '2025-03-31'),
(78, 1, 332, 10000.00, 10000.00, '2025-03-31'),
(79, 1, 360, 5000.00, 5000.00, '2025-03-31'),
(80, 1, 361, 190000.00, 190000.00, '2025-03-31'),
(81, 1, 333, 400000.00, 400000.00, '2025-03-31'),
(82, 1, 337, 22000.00, 22000.00, '2025-03-31'),
(83, 1, 338, 150000.00, 150000.00, '2025-03-31'),
(84, 1, 339, 507000.00, 507000.00, '2025-03-31'),
(85, 1, 340, 15000.00, 15000.00, '2025-03-31'),
(86, 1, 341, 21000.00, 21000.00, '2025-03-31'),
(87, 1, 342, 500000.00, 500000.00, '2025-03-31'),
(88, 1, 343, 15000.00, 15000.00, '2025-03-31'),
(89, 1, 362, 2535000.00, 2535000.00, '2025-03-31'),
(90, 1, 363, 26000.00, 26000.00, '2025-03-31'),
(91, 1, 345, 68000.00, 68000.00, '2025-03-31'),
(92, 1, 346, 200000.00, 200000.00, '2025-03-31'),
(131, 3, 311, 421000.00, 421000.00, '2025-03-31'),
(132, 3, 313, 424000.00, 424000.00, '2025-03-31'),
(133, 3, 314, 393000.00, 393000.00, '2025-03-31'),
(134, 3, 315, 0.00, 0.00, '2025-03-31'),
(135, 3, 355, 211000.00, 211000.00, '2025-03-31'),
(137, 3, 318, 54000.00, 54000.00, '2025-03-31'),
(140, 3, 357, 66000.00, 66000.00, '2025-03-31'),
(141, 3, 321, 42000.00, 42000.00, '2025-03-31'),
(142, 3, 368, 10000.00, 10000.00, '2025-03-31'),
(143, 3, 369, 0.00, 0.00, '2025-03-31'),
(144, 3, 370, 0.00, 0.00, '2025-03-31'),
(145, 3, 325, 5000.00, 5000.00, '2025-03-31'),
(146, 3, 326, 0.00, 0.00, '2025-03-31'),
(147, 3, 327, 10000.00, 10000.00, '2025-03-31'),
(148, 3, 328, 0.00, 0.00, '2025-03-31'),
(149, 3, 329, 0.00, 0.00, '2025-03-31'),
(150, 3, 330, 0.00, 0.00, '2025-03-31'),
(153, 3, 372, 10000.00, 10000.00, '2025-03-31'),
(154, 3, 360, 5000.00, 5000.00, '2025-03-31'),
(155, 3, 359, 0.00, 0.00, '2025-03-31'),
(156, 3, 333, 0.00, 0.00, '2025-03-31'),
(157, 3, 337, 0.00, 0.00, '2025-03-31'),
(158, 3, 338, 0.00, 0.00, '2025-03-31'),
(159, 3, 339, 0.00, 0.00, '2025-03-31'),
(160, 3, 340, 22000.00, 22000.00, '2025-03-31'),
(161, 3, 374, 182000.00, 182000.00, '2025-03-31'),
(162, 3, 342, 312000.00, 312000.00, '2025-03-31'),
(163, 3, 343, 10000.00, 10000.00, '2025-03-31'),
(164, 3, 363, 0.00, 0.00, '2025-03-31'),
(165, 3, 364, 0.00, 0.00, '2025-03-31'),
(166, 3, 376, 30000.00, 30000.00, '2025-03-31'),
(167, 3, 346, 238000.00, 238000.00, '2025-03-31'),
(168, 3, 362, 0.00, 0.00, '2025-03-31'),
(169, 3, 317, 106000.00, 106000.00, '2025-03-31'),
(170, 3, 319, 319000.00, 319000.00, '2025-03-31'),
(171, 3, 367, 27000.00, 27000.00, '2025-03-31'),
(172, 3, 371, 4000.00, 4000.00, '2025-03-31'),
(173, 3, 358, 366000.00, 366000.00, '2025-03-31'),
(174, 2, 311, 424000.00, 424000.00, '2025-03-31'),
(175, 2, 313, 307000.00, 307000.00, '2025-03-31'),
(176, 2, 314, 396000.00, 396000.00, '2025-03-31'),
(177, 2, 315, 0.00, 0.00, '2025-03-31'),
(178, 2, 355, 79000.00, 79000.00, '2025-03-31'),
(179, 2, 317, 235000.00, 235000.00, '2025-03-31'),
(180, 2, 318, 22000.00, 22000.00, '2025-03-31'),
(181, 2, 319, 312000.00, 312000.00, '2025-03-31'),
(182, 2, 367, 22000.00, 22000.00, '2025-03-31'),
(183, 2, 357, 2000.00, 2000.00, '2025-03-31'),
(184, 2, 321, 47000.00, 47000.00, '2025-03-31'),
(185, 2, 368, 20000.00, 20000.00, '2025-03-31'),
(186, 2, 369, 0.00, 0.00, '2025-03-31'),
(187, 2, 370, 0.00, 0.00, '2025-03-31'),
(188, 2, 325, 10000.00, 10000.00, '2025-03-31'),
(189, 2, 326, 0.00, 0.00, '2025-03-31'),
(190, 2, 327, 36000.00, 36000.00, '2025-03-31'),
(191, 2, 328, 0.00, 0.00, '2025-03-31'),
(192, 2, 329, 0.00, 0.00, '2025-03-31'),
(193, 2, 330, 0.00, 0.00, '2025-03-31'),
(194, 2, 358, 433000.00, 433000.00, '2025-03-31'),
(195, 2, 371, 0.00, 0.00, '2025-03-31'),
(196, 2, 372, 15000.00, 15000.00, '2025-03-31'),
(197, 2, 372, 15000.00, 15000.00, '2025-03-31'),
(198, 2, 359, 0.00, 0.00, '2025-03-31'),
(199, 2, 333, 100000.00, 100000.00, '2025-03-31'),
(200, 2, 337, 0.00, 0.00, '2025-03-31'),
(201, 2, 338, 0.00, 0.00, '2025-03-31'),
(202, 2, 339, 0.00, 0.00, '2025-03-31'),
(203, 2, 340, 22000.00, 22000.00, '2025-03-31'),
(204, 2, 374, 100000.00, 100000.00, '2025-03-31'),
(205, 2, 342, 430000.00, 430000.00, '2025-03-31'),
(206, 2, 343, 57000.00, 57000.00, '2025-03-31'),
(207, 2, 362, 0.00, 0.00, '2025-03-31'),
(208, 2, 363, 52000.00, 52000.00, '2025-03-31'),
(209, 2, 364, 0.00, 0.00, '2025-03-31'),
(210, 2, 376, 13000.00, 13000.00, '2025-03-31'),
(211, 2, 346, 74000.00, 74000.00, '2025-03-31'),
(212, 4, 311, 670000.00, 670000.00, '2025-03-31'),
(213, 4, 313, 1133000.00, 1133000.00, '2025-03-31'),
(214, 4, 314, 462000.00, 462000.00, '2025-03-31'),
(215, 4, 315, 0.00, 0.00, '2025-03-31'),
(216, 4, 355, 361000.00, 361000.00, '2025-03-31'),
(217, 4, 314, 258000.00, 258000.00, '2025-03-31'),
(218, 4, 318, 54000.00, 54000.00, '2025-03-31'),
(219, 4, 319, 613000.00, 613000.00, '2025-03-31'),
(220, 4, 367, 211000.00, 211000.00, '2025-03-31'),
(221, 4, 357, 212000.00, 212000.00, '2025-03-31'),
(222, 4, 321, 158000.00, 158000.00, '2025-03-31'),
(223, 4, 368, 475000.00, 475000.00, '2025-03-31'),
(224, 4, 369, 155000.00, 155000.00, '2025-03-31'),
(225, 4, 370, 0.00, 0.00, '2025-03-31'),
(226, 4, 325, 5000.00, 5000.00, '2025-03-31'),
(227, 4, 326, 0.00, 0.00, '2025-03-31'),
(228, 4, 327, 758000.00, 758000.00, '2025-03-31'),
(229, 4, 328, 393000.00, 393000.00, '2025-03-31'),
(230, 4, 329, 0.00, 0.00, '2025-03-31'),
(231, 4, 330, 0.00, 0.00, '2025-03-31'),
(232, 4, 358, 670000.00, 670000.00, '2025-03-31'),
(233, 4, 371, 15000.00, 15000.00, '2025-03-31'),
(234, 4, 360, 52000.00, 52000.00, '2025-03-31'),
(235, 4, 372, 52000.00, 52000.00, '2025-03-31'),
(236, 4, 359, 0.00, 0.00, '2025-03-31'),
(237, 4, 333, 258000.00, 258000.00, '2025-03-31'),
(238, 4, 337, 0.00, 0.00, '2025-03-31'),
(239, 4, 338, 0.00, 0.00, '2025-03-31'),
(240, 4, 339, 0.00, 0.00, '2025-03-31'),
(241, 4, 340, 130000.00, 130000.00, '2025-03-31'),
(242, 4, 374, 464000.00, 464000.00, '2025-03-31'),
(243, 4, 342, 631000.00, 631000.00, '2025-03-31'),
(244, 4, 343, 361000.00, 361000.00, '2025-03-31'),
(245, 4, 362, 0.00, 0.00, '2025-03-31'),
(246, 4, 363, 103000.00, 103000.00, '2025-03-31'),
(247, 4, 364, 0.00, 0.00, '2025-03-31'),
(248, 4, 376, 29000.00, 29000.00, '2025-03-31'),
(249, 4, 346, 328000.00, 328000.00, '2025-03-31'),
(250, 5, 311, 1320000.00, 1320000.00, '2025-03-31'),
(251, 5, 313, 5642000.00, 5642000.00, '2025-03-31'),
(252, 5, 314, 1296000.00, 1296000.00, '2025-03-31'),
(253, 5, 355, 0.00, 0.00, '2025-03-31'),
(254, 5, 318, 324000.00, 324000.00, '2025-03-31'),
(255, 5, 319, 400000.00, 400000.00, '2025-03-31'),
(256, 5, 321, 300000.00, 300000.00, '2025-03-31'),
(257, 5, 357, 396000.00, 396000.00, '2025-03-31'),
(258, 5, 368, 330000.00, 330000.00, '2025-03-31'),
(259, 5, 328, 12225000.00, 12225000.00, '2025-03-31'),
(260, 5, 342, 972000.00, 972000.00, '2025-03-31'),
(261, 5, 325, 0.00, 0.00, '2025-03-31'),
(262, 6, 311, 350000.00, 350000.00, '2025-03-31'),
(263, 6, 313, 365000.00, 365000.00, '2025-03-31'),
(264, 6, 314, 369000.00, 369000.00, '2025-03-31'),
(265, 6, 357, 100000.00, 100000.00, '2025-03-31'),
(266, 6, 368, 100000.00, 100000.00, '2025-03-31'),
(267, 6, 327, 250000.00, 250000.00, '2025-03-31'),
(268, 6, 328, 1790000.00, 1790000.00, '2025-03-31'),
(269, 6, 372, 100000.00, 100000.00, '2025-03-31'),
(270, 6, 360, 100000.00, 100000.00, '2025-03-31'),
(271, 6, 340, 150000.00, 150000.00, '2025-03-31'),
(272, 6, 374, 200000.00, 200000.00, '2025-03-31'),
(273, 6, 342, 350000.00, 350000.00, '2025-03-31'),
(274, 6, 343, 100000.00, 100000.00, '2025-03-31'),
(275, 6, 362, 100000.00, 100000.00, '2025-03-31'),
(276, 6, 333, 150000.00, 150000.00, '2025-03-31'),
(277, 6, 364, 177000.00, 177000.00, '2025-03-31'),
(278, 6, 376, 200000.00, 200000.00, '2025-03-31'),
(279, 6, 346, 0.00, 0.00, '2025-03-31'),
(280, 7, 311, 310000.00, 310000.00, '2025-03-31'),
(281, 7, 313, 461000.00, 461000.00, '2025-03-31'),
(283, 7, 355, 180000.00, 180000.00, '2025-03-31'),
(284, 7, 317, 50000.00, 50000.00, '2025-03-31'),
(285, 7, 357, 30000.00, 30000.00, '2025-03-31'),
(286, 7, 368, 100000.00, 100000.00, '2025-03-31'),
(287, 7, 327, 300000.00, 300000.00, '2025-03-31'),
(288, 7, 328, 970000.00, 970000.00, '2025-03-31'),
(289, 7, 359, 650000.00, 650000.00, '2025-03-31'),
(290, 7, 360, 10000.00, 10000.00, '2025-03-31'),
(291, 7, 339, 400000.00, 400000.00, '2025-03-31'),
(292, 7, 374, 50000.00, 50000.00, '2025-03-31'),
(293, 7, 363, 50000.00, 50000.00, '2025-03-31'),
(294, 7, 346, 100000.00, 100000.00, '2025-03-31'),
(295, 7, 376, 50000.00, 50000.00, '2025-03-31'),
(296, 7, 317, 150000.00, 150000.00, '2025-03-31'),
(297, 7, 342, 311000.00, 311000.00, '2025-03-31'),
(298, 7, 343, 50000.00, 50000.00, '2025-03-31'),
(299, 7, 373, 50000.00, 50000.00, '2025-03-31'),
(300, 8, 311, 155000.00, 155000.00, '2025-03-31'),
(301, 8, 313, 258000.00, 258000.00, '2025-03-31'),
(302, 8, 314, 72000.00, 72000.00, '2025-03-31'),
(303, 8, 315, 0.00, 0.00, '2025-03-31'),
(304, 8, 355, 62000.00, 62000.00, '2025-03-31'),
(305, 8, 314, 27000.00, 27000.00, '2025-03-31'),
(306, 8, 318, 71000.00, 71000.00, '2025-03-31'),
(307, 8, 319, 224000.00, 224000.00, '2025-03-31'),
(308, 8, 367, 5000.00, 5000.00, '2025-03-31'),
(309, 8, 357, 15000.00, 15000.00, '2025-03-31'),
(310, 8, 321, 15000.00, 15000.00, '2025-03-31'),
(311, 8, 368, 5000.00, 5000.00, '2025-03-31'),
(312, 8, 369, 0.00, 0.00, '2025-03-31'),
(313, 8, 370, 0.00, 0.00, '2025-03-31'),
(314, 8, 325, 1000.00, 1000.00, '2025-03-31'),
(315, 8, 326, 0.00, 0.00, '2025-03-31'),
(316, 8, 327, 5000.00, 5000.00, '2025-03-31'),
(317, 8, 328, 103000.00, 103000.00, '2025-03-31'),
(318, 8, 329, 0.00, 0.00, '2025-03-31'),
(319, 8, 330, 0.00, 0.00, '2025-03-31'),
(320, 8, 358, 320000.00, 320000.00, '2025-03-31'),
(321, 8, 371, 1000.00, 1000.00, '2025-03-31'),
(322, 8, 332, 5000.00, 5000.00, '2025-03-31'),
(323, 8, 360, 2000.00, 2000.00, '2025-03-31'),
(324, 8, 359, 0.00, 0.00, '2025-03-31'),
(325, 8, 333, 155000.00, 155000.00, '2025-03-31'),
(326, 8, 337, 0.00, 0.00, '2025-03-31'),
(327, 8, 338, 0.00, 0.00, '2025-03-31'),
(328, 8, 339, 0.00, 0.00, '2025-03-31'),
(329, 8, 340, 5000.00, 5000.00, '2025-03-31'),
(330, 8, 374, 10000.00, 10000.00, '2025-03-31'),
(331, 8, 342, 103000.00, 103000.00, '2025-03-31'),
(332, 8, 343, 0.00, 0.00, '2025-03-31'),
(333, 8, 362, 0.00, 0.00, '2025-03-31'),
(334, 8, 375, 10000.00, 10000.00, '2025-03-31'),
(335, 8, 364, 0.00, 0.00, '2025-03-31'),
(336, 8, 376, 6000.00, 6000.00, '2025-03-31'),
(337, 8, 346, 21000.00, 21000.00, '2025-03-31'),
(338, 9, 311, 23000.00, 23000.00, '2025-03-31'),
(339, 9, 313, 55000.00, 55000.00, '2025-03-31'),
(340, 9, 317, 48000.00, 48000.00, '2025-03-31'),
(341, 9, 315, 0.00, 0.00, '2025-03-31'),
(343, 9, 355, 10000.00, 10000.00, '2025-03-31'),
(344, 9, 317, 20000.00, 20000.00, '2025-03-31'),
(345, 9, 318, 50000.00, 50000.00, '2025-03-31'),
(346, 9, 319, 87000.00, 87000.00, '2025-03-31'),
(347, 9, 367, 5000.00, 5000.00, '2025-03-31'),
(348, 9, 357, 5000.00, 5000.00, '2025-03-31'),
(349, 9, 321, 21000.00, 21000.00, '2025-03-31'),
(350, 9, 368, 15000.00, 15000.00, '2025-03-31'),
(351, 9, 369, 0.00, 0.00, '2025-03-31'),
(352, 9, 370, 0.00, 0.00, '2025-03-31'),
(353, 9, 325, 0.00, 0.00, '2025-03-31'),
(354, 9, 326, 0.00, 0.00, '2025-03-31'),
(355, 9, 327, 90000.00, 90000.00, '2025-03-31'),
(356, 9, 328, 0.00, 0.00, '2025-03-31'),
(357, 9, 329, 0.00, 0.00, '2025-03-31'),
(358, 9, 330, 0.00, 0.00, '2025-03-31'),
(359, 9, 358, 131000.00, 131000.00, '2025-03-31'),
(360, 9, 371, 0.00, 0.00, '2025-03-31'),
(361, 9, 372, 3000.00, 3000.00, '2025-03-31'),
(362, 9, 360, 1000.00, 1000.00, '2025-03-31'),
(363, 9, 359, 0.00, 0.00, '2025-03-31'),
(364, 9, 333, 15000.00, 15000.00, '2025-03-31'),
(365, 9, 337, 0.00, 0.00, '2025-03-31'),
(366, 9, 338, 0.00, 0.00, '2025-03-31'),
(367, 9, 339, 0.00, 0.00, '2025-03-31'),
(368, 9, 340, 5000.00, 5000.00, '2025-03-31'),
(369, 9, 374, 10000.00, 10000.00, '2025-03-31'),
(370, 9, 342, 56000.00, 56000.00, '2025-03-31'),
(371, 9, 343, 0.00, 0.00, '2025-03-31'),
(372, 9, 362, 0.00, 0.00, '2025-03-31'),
(373, 9, 363, 50000.00, 50000.00, '2025-03-31'),
(374, 9, 364, 0.00, 0.00, '2025-03-31'),
(375, 9, 314, 50000.00, 50000.00, '2025-03-31'),
(376, 9, 346, 50000.00, 50000.00, '2025-03-31'),
(378, 1, 379, 100000.00, 100000.00, '2025-04-02'),
(379, 1, 380, 100000.00, 100000.00, '2025-04-02'),
(380, 1, 381, 0.00, 0.00, '2025-04-02'),
(381, 11, 295, 3817000.00, 3817000.00, '2025-04-06'),
(382, 11, 383, 144000.00, 144000.00, '2025-04-06'),
(383, 11, 298, 120000.00, 120000.00, '2025-04-06'),
(384, 11, 299, 120000.00, 120000.00, '2025-04-06'),
(385, 11, 300, 42000.00, 42000.00, '2025-04-06'),
(386, 11, 384, 318000.00, 318000.00, '2025-04-06'),
(387, 11, 304, 30000.00, 30000.00, '2025-04-06'),
(388, 11, 385, 318000.00, 318000.00, '2025-04-06'),
(389, 11, 386, 30000.00, 30000.00, '2025-04-06'),
(390, 11, 306, 14000.00, 14000.00, '2025-04-06'),
(391, 11, 307, 81000.00, 81000.00, '2025-04-06'),
(392, 11, 387, 7000.00, 7000.00, '2025-04-06'),
(393, 11, 388, 10000.00, 10000.00, '2025-04-06'),
(394, 11, 389, 458000.00, 458000.00, '2025-04-06'),
(395, 12, 296, 2569849.24, 2569849.24, '2025-04-06'),
(396, 12, 383, 0.00, 0.00, '2025-04-06'),
(397, 12, 300, 0.00, 0.00, '2025-04-06'),
(398, 12, 384, 0.00, 0.00, '2025-04-06'),
(399, 12, 385, 0.00, 0.00, '2025-04-06'),
(400, 12, 306, 0.00, 0.00, '2025-04-06'),
(401, 12, 307, 0.00, 0.00, '2025-04-06'),
(402, 12, 387, 0.00, 0.00, '2025-04-06'),
(403, 12, 389, 0.00, 0.00, '2025-04-06'),
(404, 13, 296, 3884966.80, 3884966.80, '2025-04-06'),
(405, 13, 383, 0.00, 0.00, '2025-04-06'),
(406, 13, 300, 0.00, 0.00, '2025-04-06'),
(407, 13, 384, 0.00, 0.00, '2025-04-06'),
(408, 13, 385, 0.00, 0.00, '2025-04-06'),
(409, 13, 304, 0.00, 0.00, '2025-04-06'),
(410, 13, 386, 0.00, 0.00, '2025-04-06'),
(411, 13, 306, 0.00, 0.00, '2025-04-06'),
(412, 13, 307, 0.00, 0.00, '2025-04-06'),
(413, 13, 387, 0.00, 0.00, '2025-04-06'),
(414, 13, 389, 0.00, 0.00, '2025-04-06'),
(415, 14, 311, 123354.84, 123354.84, '2025-04-06'),
(416, 14, 313, 998767.19, 998767.19, '2025-04-06'),
(417, 14, 327, 532198.00, 532198.00, '2025-04-06'),
(418, 14, 374, 38921.51, 38921.51, '2025-04-06'),
(419, 14, 362, 119686.79, 119686.79, '2025-04-06'),
(420, 14, 328, 0.00, 0.00, '2025-04-06'),
(421, 14, 355, 0.00, 0.00, '2025-04-06'),
(422, 14, 333, 0.00, 0.00, '2025-04-06'),
(423, 14, 390, 11850.00, 11850.00, '2025-04-06'),
(424, 15, 311, 1082400.00, 1082400.00, '2025-04-06'),
(425, 15, 313, 9020000.00, 9020000.00, '2025-04-06'),
(426, 15, 391, 105600.00, 105600.00, '2025-04-06'),
(427, 15, 355, 411840.00, 411840.00, '2025-04-06'),
(428, 15, 392, 2200.00, 2200.00, '2025-04-06'),
(429, 15, 357, 71280.00, 71280.00, '2025-04-06'),
(430, 15, 327, 594000.00, 594000.00, '2025-04-06'),
(431, 15, 328, 2264581.60, 2264581.60, '2025-04-06'),
(432, 15, 342, 1795200.00, 1795200.00, '2025-04-06'),
(433, 15, 362, 844800.00, 844800.00, '2025-04-06'),
(434, 15, 393, 352000.00, 352000.00, '2025-04-06'),
(435, 15, 394, 0.00, 0.00, '2025-04-06'),
(436, 15, 325, 0.00, 0.00, '2025-04-06'),
(437, 15, 395, 0.00, 0.00, '2025-04-06'),
(438, 15, 337, 0.00, 0.00, '2025-04-06');

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
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `services_id` int(11) NOT NULL,
  `services_name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `oopap_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`services_id`, `services_name`, `code`, `oopap_id`) VALUES
(2, 'REGULAR MANDATORY', 'RM', 1),
(3, 'ONE TIME EXPENSES', 'ONETIME', 1),
(4, 'OUTSOURCED SERVICES', 'OS', 1),
(5, 'SECURITY SERVICES', 'SECURITY', 1),
(6, 'LEARNING AND DEVELOPMENT', 'LD', 1),
(7, 'REWARDS AND RECOGNITION', 'RR', 1),
(8, 'PERFORMANCE MANAGEMENT SYSTEM', 'PMS', 1),
(9, 'RECRUITMENT, SELECTION AND PLACEMENT', 'RSP', 1),
(10, 'SUPPLY & PROPERTY MANAGEMENT', 'SUPPLY', 1),
(11, 'ADMINISTRATION & POLICY', 'ADMINPOLICY', 1),
(12, 'SAFETY AND HEALTH', 'OSH', 1),
(13, 'RBAC MEETING', 'RBAC', 1),
(14, 'GADGET COST', 'GADGET', 1),
(15, 'MSSD ORD', 'MSSD', 1),
(16, 'MSSD ISO', 'MSSD-ISO', 1),
(17, 'MSSD PLANNING', 'MSSD-PLAN', 1),
(18, 'MSSD-RECORDS', 'MSSD-RECORDS', 1),
(19, 'MSSD-KM', 'MSSD-KM', 1),
(20, 'PROVINCIAL OPERATING FUND (POF) - MANDATORY', 'POF', 1);

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
  ADD KEY `ors_id` (`ors_id`);

--
-- Indexes for table `dv_history`
--
ALTER TABLE `dv_history`
  ADD PRIMARY KEY (`dvhis_id`),
  ADD KEY `dv_id` (`dv_id`),
  ADD KEY `account_id` (`account_id`);

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
  ADD KEY `dv_id` (`dv_id`),
  ADD KEY `ors_id` (`ors_id`);

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
  ADD KEY `payee_id` (`payee_id`),
  ADD KEY `services_id` (`services_id`);

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
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`services_id`),
  ADD KEY `oopap_id` (`oopap_id`);

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
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=396;

--
-- AUTO_INCREMENT for table `approver`
--
ALTER TABLE `approver`
  MODIFY `approver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dv`
--
ALTER TABLE `dv`
  MODIFY `dv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `dv_history`
--
ALTER TABLE `dv_history`
  MODIFY `dvhis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `oopap`
--
ALTER TABLE `oopap`
  MODIFY `oopap_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `ors`
--
ALTER TABLE `ors`
  MODIFY `ors_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `payee`
--
ALTER TABLE `payee`
  MODIFY `payee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1743926826;

--
-- AUTO_INCREMENT for table `responsibility_center`
--
ALTER TABLE `responsibility_center`
  MODIFY `rc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
  ADD CONSTRAINT `dv_ibfk_1` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dv_history`
--
ALTER TABLE `dv_history`
  ADD CONSTRAINT `dv_history_ibfk_1` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_history_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jev`
--
ALTER TABLE `jev`
  ADD CONSTRAINT `jev_ibfk_1` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jev_ibfk_2` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `ors_ibfk_8` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_9` FOREIGN KEY (`services_id`) REFERENCES `services` (`services_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`oopap_id`) REFERENCES `oopap` (`oopap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`oopap_id`) REFERENCES `oopap` (`oopap_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
