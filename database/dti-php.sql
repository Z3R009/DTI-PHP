-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2025 at 03:23 AM
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
  `fund_cluster_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `dv_no` varchar(50) DEFAULT NULL,
  `payment_mode` varchar(100) DEFAULT NULL,
  `vat` decimal(10,2) DEFAULT NULL,
  `tax_1` decimal(10,2) DEFAULT NULL,
  `tax_2` decimal(10,2) DEFAULT NULL,
  `net_amount` decimal(10,2) DEFAULT NULL,
  `object_code_id` int(255) NOT NULL,
  `debit` double(40,2) NOT NULL,
  `credit` double(40,2) NOT NULL,
  `chief_accountant` varchar(255) DEFAULT NULL,
  `regional_director` varchar(255) DEFAULT NULL,
  `check_no` varchar(50) DEFAULT NULL,
  `bank_acc_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_categories`
--

CREATE TABLE `financial_categories` (
  `category_id` int(255) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_categories`
--

INSERT INTO `financial_categories` (`category_id`, `category_name`) VALUES
(1, 'Asset'),
(2, 'Liabilities'),
(3, 'Equity'),
(4, 'Revenue/Income'),
(5, 'Expenses');

-- --------------------------------------------------------

--
-- Table structure for table `financial_object_code`
--

CREATE TABLE `financial_object_code` (
  `object_code_id` int(255) NOT NULL,
  `object_name` varchar(255) NOT NULL,
  `submodule_id` int(255) NOT NULL,
  `uacs_code` int(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_object_code`
--

INSERT INTO `financial_object_code` (`object_code_id`, `object_name`, `submodule_id`, `uacs_code`, `status`) VALUES
(3, 'Cash - Collecting Officers', 3, 10101010, 'Active'),
(4, 'Petty Cash', 3, 10101020, 'Active'),
(6, 'Cash in Bank - Local Currency, Bangko Sentral ng Pilipinas', 96, 10102010, 'Active'),
(7, 'Cash in Bank - Local Currency, Current Account', 96, 10102020, 'Active'),
(8, 'Cash in Bank - Local Currency, Savings Account', 96, 10102030, 'Active'),
(9, 'Cash in Bank - Local Currency, Time Deposits', 96, 10102040, 'Inactive'),
(10, 'Cash in Bank - Foreign Currency, Bangko Sentral ng Pilipinas ', 50, 10103010, 'Active'),
(15, 'Cash in Bank - Foreign Currency, Current Account', 50, 10103020, 'Active'),
(16, 'Cash in Bank - Foreign Currency, Savings Account', 50, 10103030, 'Active'),
(17, 'Cash in Bank - Foreign Currency, Time Deposits', 50, 10103040, 'Active'),
(18, 'Cash - Treasury/Agency Deposit, Regular', 51, 10104010, 'Active'),
(19, 'Cash - Treasury/Agency Deposit, Special Account', 51, 10104020, 'Active'),
(20, 'Cash - Treasury/Agency Deposit, Trust', 51, 10104030, 'Active'),
(21, 'Cash - Modified Disbursement System (MDS), Regular', 51, 10104040, 'Active'),
(22, 'Cash - Modified Disbursement System (MDS), Special Account', 51, 10104050, 'Active'),
(23, 'Cash - Modified Disbursement System (MDS), Trust', 51, 10104060, 'Active'),
(24, 'Cash - Tax Remittance Advice ', 51, 10104070, 'Active'),
(25, 'Cash - Constructive Income and Other Remittances', 51, 10104080, 'Active'),
(26, 'Cash - Constructive Disbursements', 51, 10104090, 'Active'),
(27, 'Treasury Bills', 52, 10105010, 'Active'),
(28, 'Time Deposits - Local Currency', 52, 10105020, 'Active'),
(29, 'Time Deposits - Foreign Currency', 52, 10105030, 'Active'),
(30, 'Financial Assets Held for Trading', 53, 10201010, 'Active'),
(31, 'Financial Assets Designated at Fair Value Through Surplus or Deficit', 53, 10201020, 'Active'),
(32, 'Derivative Financial Assets Held for Trading', 53, 10201030, 'Active'),
(33, 'Derivative Financial Assets Designated at Fair Value Through Surplus or Deficit', 53, 10201040, 'Active'),
(34, 'Investments in Treasury Bills - Local', 54, 10202010, 'Active'),
(35, 'Allowance for Impairment - Investments in Treasury Bills - Local', 54, 10202011, 'Inactive'),
(36, 'Allowance for Impairment - Investments in Treasury Bills - Local', 54, 10202012, 'Active'),
(37, 'Investments in Treasury Bills - Foreign', 54, 10202020, 'Active'),
(38, 'Allowance for Impairment - Investments in Treasury Bills - Foreign', 54, 10202021, 'Inactive'),
(39, 'Allowance for Impairment - Investments in Treasury Bills - Foreign', 54, 10202022, 'Active'),
(40, 'Investments in Treasury Bonds - Local', 54, 10202030, 'Active'),
(41, 'Allowance for Impairment - Investments in Bonds - Local', 54, 10202031, 'Inactive'),
(42, 'Allowance for Impairment - Investments in Treasury Bonds - Local', 54, 10202032, 'Active'),
(43, 'Investments in Treasury Bonds - Foreign', 54, 10202040, 'Active'),
(44, 'Allowance for Impairment - Investments in Treasury Bonds - Foreign', 54, 10202041, 'Inactive'),
(45, 'Allowance for Impairment - Investments in Treasury Bonds - Foreign', 54, 10202042, 'Active'),
(46, 'Investments in Stocks', 55, 10203010, 'Active'),
(47, 'Investments in Bonds', 55, 10203020, 'Active'),
(48, 'Other Investments', 55, 10203990, 'Active'),
(49, 'Investments in Government-Owned or Controlled Corporations', 55, 10204010, 'Active'),
(50, 'Allowance for Impairment - Investments in GOCCs', 56, 10204011, 'Inactive'),
(51, 'Allowance for Impairment - Investments in Government-Owned or Controlled Corporations', 56, 10204012, 'Active'),
(52, 'Investments in Joint Ventures', 56, 10205010, 'Active'),
(53, 'Allowance for Impairment - Investments in Joint Venture', 57, 10205011, 'Inactive'),
(54, 'Allowance for Impairment - Investments in Joint Ventures', 57, 10205012, 'Active'),
(55, 'Investments in Associates', 57, 10206010, 'Inactive'),
(56, 'Allowance for Impairment - Investments in Associates', 58, 10206011, 'Inactive'),
(57, 'Sinking Fund', 58, 10207010, 'Active'),
(58, 'Investments in Time Deposits - Local Currency', 59, 10211010, 'Active'),
(59, 'Investments in Time Deposits - Foreign Currency', 59, 10211020, 'Active'),
(60, 'Accounts Receivable', 60, 10301010, 'Active'),
(61, 'Allowance for Impairment - Accounts Receivable', 60, 10301011, 'Inactive'),
(62, 'Allowance for Impairment - Accounts Receivable', 60, 10301012, 'Active'),
(63, 'Notes Receivable', 60, 10301020, 'Active'),
(64, 'Allowance for Impairment - Notes Receivable', 60, 10301021, 'Inactive'),
(65, 'Allowance for Impairment - Notes Receivable', 60, 10301022, 'Active'),
(66, 'Loans Receivable - Government-Owned or Controlled Corporations', 60, 10301030, 'Active'),
(67, 'Allowance for Impairment - Loans Receivable - Government-Owned and/or Controlled Corporations', 60, 10301031, 'Inactive'),
(68, 'Allowance for Impairment - Loans Receivable - Government-Owned or Controlled Corporation', 60, 10301032, 'Active'),
(69, 'Loans Receivable - Local Government Units', 60, 10301040, 'Active'),
(70, 'Allowance for Impairment - Loans Receivable - Local Government Units', 60, 10301041, 'Inactive'),
(71, 'Allowance for Impairment - Loans Receivable - Local Government Units', 60, 10301042, 'Active'),
(72, 'Interests Receivable', 60, 10301050, 'Active'),
(73, 'Allowance for Impairment - Interests Receivable', 60, 10301051, 'Inactive'),
(74, 'Allowance for Impairment - Interests Receivable', 60, 10301052, 'Active'),
(75, 'Dividends Receivable', 60, 10301060, 'Active'),
(76, 'Tax Receivable', 60, 10301210, 'Active'),
(77, 'Allowance for Impairment - Tax Receivable', 60, 10301212, 'Active'),
(78, 'Receivables from Joint Ventures', 60, 10301220, 'Active'),
(79, 'Allowance for Impairment - Receivables from Joint Ventures ', 60, 10301222, 'Active'),
(80, 'Receivables from Joint Operators', 60, 10301230, 'Active'),
(81, 'Allowance for Impairment - Receivables from Joint Operators ', 60, 10301232, 'Active'),
(82, 'Service Concession Arrangements Receivable ', 60, 10301240, 'Active'),
(83, 'Allowance for Impairment - Service Concession Arrangements Receivable', 60, 10301242, 'Active'),
(84, 'Receivables from Authorized Agent Banks (AABs)/Agents ', 60, 10301250, 'Active'),
(85, 'Loans Receivable - Others', 60, 10301990, 'Active'),
(86, 'Allowance for Impairment - Loans Receivables - Others', 60, 10301991, 'Inactive'),
(87, 'Allowance for Impairment - Loans Receivable - Others', 60, 10301992, 'Inactive'),
(88, 'Operating Lease Receivable', 61, 10302010, 'Active'),
(89, 'Allowance for Impairment - Operating Lease Receivable', 61, 10302011, 'Inactive'),
(90, 'Allowance for Impairment - Operating Lease Receivable', 61, 10302012, 'Active'),
(91, 'Finance Lease Receivable', 61, 10302020, 'Active'),
(92, 'Allowance for Impairment - Finance Lease Receivable', 61, 10302021, 'Inactive'),
(93, 'Allowance for Impairment - Finance Lease Receivable', 61, 10302022, 'Active'),
(94, 'Due from National Government Agencies', 62, 10303010, 'Active'),
(95, 'Allowance for Impairment - Due from National Government Agencies', 62, 10303012, 'Active'),
(96, 'Due from Government-Owned or Controlled Corporations', 62, 10303020, 'Active'),
(97, 'Allowance for Impairment - Due from Government-Owned or Controlled Corporations', 62, 10303022, 'Active'),
(98, 'Due from Local Government Units', 62, 10303030, 'Active'),
(99, 'Allowance for Impairment - Due from Local Government Units', 62, 10303032, 'Active'),
(100, 'Due from Joint Venture', 62, 10303040, 'Active'),
(101, 'Due from Central Office', 63, 10304010, 'Active'),
(102, 'Due from Bureaus', 63, 10304020, 'Active'),
(103, 'Due from Regional Offices', 63, 10304030, 'Active'),
(104, 'Due from Operating/Field Units', 63, 10304040, 'Active'),
(105, 'Due from Other Funds', 63, 10304050, 'Active'),
(106, 'Receivables - Disallowances/Charges ', 64, 10305010, 'Inactive'),
(107, ' Due from Officers and Employees', 64, 10305020, 'Inactive'),
(108, 'Due from Non-Government Organizations/People\'s Organizations', 64, 10305030, 'Inactive'),
(109, 'Other Receivables', 64, 10305990, 'Inactive'),
(110, 'Allowance for Impairment - Other Receivables', 64, 10305991, 'Inactive'),
(111, 'Receivables - Disallowances/Charges', 64, 10399010, 'Active'),
(112, 'Due from Officers and Employees', 64, 10399020, 'Active'),
(113, 'Allowance for Impairment - Due from Officers and Employees', 64, 10399022, 'Active'),
(114, 'Due from Non-Government Organizations/Civil Society Organizations', 64, 10399030, 'Active'),
(115, 'Allowance for Impairment - Due from Non-Government Organizations/Civil Society Organizations', 64, 10399032, 'Active'),
(116, 'Other Receivables', 64, 10399990, 'Active'),
(117, 'Allowance for Impairment - Other Receivables ', 64, 10399992, 'Active'),
(118, 'Acquired/Forfeited Real Property', 65, 10401010, 'Active'),
(119, 'Aquaculture Produce ', 65, 10401010, 'Active'),
(120, 'Forest Products', 65, 10401010, 'Active'),
(121, 'Land/Reclaimed Land', 65, 10401010, 'Active'),
(122, 'Levied Real Property', 65, 10401010, 'Active'),
(123, 'Merchandise Inventory', 65, 10401010, 'Active'),
(124, 'Seized/Distrained Personal Property', 65, 10401010, 'Active'),
(125, 'Allowance for Impairment - Merchandise Inventory', 65, 10401012, 'Active'),
(126, 'Food Supplies for Distribution', 66, 10402010, 'Active'),
(127, 'Allowance for Impairment - Food Supplies for Distribution', 66, 10402012, 'Active'),
(128, ' Welfare Goods for Distribution', 66, 10402020, 'Active'),
(129, 'Allowance for Impairment - Welfare Goods for Distribution', 66, 10402022, 'Active'),
(130, 'Drugs and Medicines for Distribution', 66, 10402030, 'Active'),
(131, ' Allowance for Impairment - Drugs and Medicines for Distribution ', 66, 10402032, 'Active'),
(132, 'Medical, Dental and Laboratory Supplies for Distribution', 66, 10402040, 'Active'),
(133, 'Allowance for Impairment - Medical, Dental and Laboratory Supplies for Distribution', 66, 10402042, 'Active'),
(134, 'Agricultural and Marine Supplies for Distribution', 66, 10402050, 'Active'),
(135, 'Allowance for Impairment - Agricultural and Marine Supplies for Distribution ', 66, 10402052, 'Active'),
(136, 'Agricultural Produce for Distribution', 66, 10402060, 'Active'),
(137, 'Allowance for Impairment - Agricultural Produce for Distribution', 66, 10402062, 'Active'),
(138, 'Textbooks and Instructional Materials for Distribution', 66, 10402070, 'Active'),
(139, 'Allowance for Impairment - Textbooks and Instructional Materials for Distribution', 66, 10402072, 'Active'),
(140, 'Construction Materials for Distribution', 66, 10402080, 'Active'),
(141, 'Allowance for Impairment - Construction Materials for Distribution', 66, 10402082, 'Active'),
(142, 'Allowance for Impairment - Property and Equipment for Distribution', 66, 10402092, 'Active'),
(143, ' Aquaculture Produce for Distribution', 66, 10402100, 'Active'),
(144, 'Allowance for Impairment - Aquaculture Produce for Distribution', 66, 10402102, 'Active'),
(145, 'Other Supplies and Materials for Distribution', 66, 10402990, 'Active'),
(146, 'Allowance for Impairment - Other Supplies and Materials for Distribution', 66, 10402992, 'Active'),
(147, 'Raw Materials Inventory', 67, 10403010, 'Active'),
(148, ' Allowance for Impairment - Raw Materials Inventory', 67, 10403012, 'Active'),
(149, ' Finished Goods Inventory', 67, 10403030, 'Active'),
(150, 'Allowance for Impairment - Finished Goods Inventory ', 67, 10403032, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `financial_subcategories`
--

CREATE TABLE `financial_subcategories` (
  `subcategory_id` int(255) NOT NULL,
  `subcategory_name` varchar(255) NOT NULL,
  `category_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_subcategories`
--

INSERT INTO `financial_subcategories` (`subcategory_id`, `subcategory_name`, `category_id`) VALUES
(1, 'Cash and Cash Equivalents', 1),
(7, 'Investments', 1),
(8, 'Receivables', 1),
(9, 'Inventories', 1),
(10, 'Property, Plant and Equipment', 1),
(11, 'Investment Property', 1),
(12, 'Biological Assets', 1),
(13, 'Intangible Assets', 1),
(14, 'Other Assets', 1);

-- --------------------------------------------------------

--
-- Table structure for table `financial_submodules`
--

CREATE TABLE `financial_submodules` (
  `submodule_id` int(255) NOT NULL,
  `submodule_name` varchar(255) NOT NULL,
  `subcategory_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_submodules`
--

INSERT INTO `financial_submodules` (`submodule_id`, `submodule_name`, `subcategory_id`) VALUES
(3, 'Cash on Hand', 1),
(49, 'Semi-Expendable Furniture, Fixtures and Books', 9),
(50, 'Cash in Bank - Foreign Currency', 1),
(51, 'Treasury/Agency Cash Accounts', 1),
(52, 'Cash Equivalents', 1),
(53, 'Financial Assets at Fair Value Through Surplus or Deficit', 7),
(54, 'Financial Assets - Held to Maturity', 7),
(55, 'Financial Assets - Others', 7),
(56, 'Investments in Government-Owned or Controlled Corporations ', 7),
(57, 'Investments in Joint Ventures', 7),
(58, 'Investments in Associates', 7),
(59, 'Investments in Time Deposits', 7),
(60, 'Loans and Receivable Accounts', 8),
(61, 'Lease Receivable ', 8),
(62, 'Inter-Agency Receivables', 8),
(63, 'Intra-Agency Receivables', 8),
(64, 'Other Receivables ', 8),
(65, 'Inventory Held for Sale', 9),
(66, 'Inventory Held for Distribution', 9),
(67, 'Inventory Held for Manufacturing', 9),
(68, 'Inventory Held for Consumption', 9),
(69, 'Semi-Expendable Machinery and Equipment ', 9),
(71, 'Land and Buildings', 11),
(72, 'Construction in Progress', 11),
(73, 'Land', 10),
(74, 'Land Improvements', 10),
(75, 'Infrastructure Assets', 10),
(76, 'Buildings and Other Structures', 10),
(77, 'Machinery and Equipment', 10),
(78, 'Transportation Equipment', 10),
(79, 'Furniture, Fixtures and Books', 10),
(80, 'Leased Assets', 10),
(81, 'Leased Assets - Improvements', 10),
(82, 'Construction in Progress', 10),
(83, 'Heritage Assets', 10),
(84, 'Service Concession Tangible Assets', 10),
(85, 'Bearer Trees, Plants and Crops', 10),
(86, 'Construction in Progress', 10),
(87, 'Other Property, Plant and Equipment', 10),
(88, 'Bearer Biological Assets', 12),
(89, 'Consumable Biological Assets', 12),
(90, 'Service Concession - Intangible Assets', 13),
(91, 'Development in Progress', 13),
(92, 'Advances', 14),
(93, 'Prepayments', 14),
(94, 'Deposits', 14),
(95, 'Deferred Charges', 14),
(96, 'Cash in Bank - Local Currency', 1);

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
  `ors_no` varchar(255) NOT NULL,
  `dv_no` varchar(255) NOT NULL,
  `jev_no` varchar(255) NOT NULL,
  `prepared` varchar(255) NOT NULL,
  `certified` varchar(255) NOT NULL
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
  `payee_name` varchar(255) NOT NULL,
  `tin_no` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `rc_id` int(255) NOT NULL,
  `object_code_id` int(255) NOT NULL,
  `oopap_id` int(255) NOT NULL,
  `amount` double(40,2) NOT NULL,
  `approver_id` int(255) NOT NULL,
  `budget_officer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ors`
--

INSERT INTO `ors` (`ors_id`, `fund_cluster_id`, `date`, `ors_no`, `payee_name`, `tin_no`, `address`, `notes`, `rc_id`, `object_code_id`, `oopap_id`, `amount`, `approver_id`, `budget_officer`) VALUES
(17, 3, '2025-03-11', '111', '111', '111', 'Koronadal City', '111', 11, 19, 8, 1111.00, 1, 'CONNIE M. BARNACHEA'),
(18, 4, '0000-00-00', '12345', 'john', '54321', 'Koronadal City', 'asd', 11, 111, 6, 1000.00, 4, 'CONNIE M. BARNACHEA'),
(19, 9, '2025-03-14', '123', 'asd', '3445', 'Koronadal City', '535asdada', 9, 3, 3, 43434.00, 4, 'CONNIE M. BARNACHEA');

-- --------------------------------------------------------

--
-- Table structure for table `payee`
--

CREATE TABLE `payee` (
  `payee_name_id` int(11) NOT NULL,
  `payee_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(251897568, 'test', 'test', '$2y$10$GQCP8D/nSPG/Xl8CqaaTXuHI53eSmhxRXMVv6NDFFsP65jerOma6S', 'Admin', NULL, NULL),
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
-- Indexes for table `approver`
--
ALTER TABLE `approver`
  ADD PRIMARY KEY (`approver_id`);

--
-- Indexes for table `dv`
--
ALTER TABLE `dv`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `fund_cluster_id` (`fund_cluster_id`);

--
-- Indexes for table `financial_categories`
--
ALTER TABLE `financial_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `financial_object_code`
--
ALTER TABLE `financial_object_code`
  ADD PRIMARY KEY (`object_code_id`),
  ADD KEY `submodule_id` (`submodule_id`);

--
-- Indexes for table `financial_subcategories`
--
ALTER TABLE `financial_subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `financial_submodules`
--
ALTER TABLE `financial_submodules`
  ADD PRIMARY KEY (`submodule_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `fund_cluster`
--
ALTER TABLE `fund_cluster`
  ADD PRIMARY KEY (`fund_cluster_id`);

--
-- Indexes for table `jev`
--
ALTER TABLE `jev`
  ADD PRIMARY KEY (`jev_id`);

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
  ADD KEY `object_code_id` (`object_code_id`),
  ADD KEY `approver_id` (`approver_id`),
  ADD KEY `oopap_id` (`oopap_id`);

--
-- Indexes for table `payee`
--
ALTER TABLE `payee`
  ADD PRIMARY KEY (`payee_name_id`);

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
-- AUTO_INCREMENT for table `approver`
--
ALTER TABLE `approver`
  MODIFY `approver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dv`
--
ALTER TABLE `dv`
  MODIFY `dv_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_categories`
--
ALTER TABLE `financial_categories`
  MODIFY `category_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50606001;

--
-- AUTO_INCREMENT for table `financial_object_code`
--
ALTER TABLE `financial_object_code`
  MODIFY `object_code_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `financial_subcategories`
--
ALTER TABLE `financial_subcategories`
  MODIFY `subcategory_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `financial_submodules`
--
ALTER TABLE `financial_submodules`
  MODIFY `submodule_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `fund_cluster`
--
ALTER TABLE `fund_cluster`
  MODIFY `fund_cluster_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jev`
--
ALTER TABLE `jev`
  MODIFY `jev_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oopap`
--
ALTER TABLE `oopap`
  MODIFY `oopap_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ors`
--
ALTER TABLE `ors`
  MODIFY `ors_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payee`
--
ALTER TABLE `payee`
  MODIFY `payee_name_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `responsibility_center`
--
ALTER TABLE `responsibility_center`
  MODIFY `rc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=941568986;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `financial_object_code`
--
ALTER TABLE `financial_object_code`
  ADD CONSTRAINT `financial_object_code_ibfk_1` FOREIGN KEY (`submodule_id`) REFERENCES `financial_submodules` (`submodule_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `financial_subcategories`
--
ALTER TABLE `financial_subcategories`
  ADD CONSTRAINT `financial_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `financial_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `financial_submodules`
--
ALTER TABLE `financial_submodules`
  ADD CONSTRAINT `financial_submodules_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `financial_subcategories` (`subcategory_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ors`
--
ALTER TABLE `ors`
  ADD CONSTRAINT `ors_ibfk_1` FOREIGN KEY (`fund_cluster_id`) REFERENCES `fund_cluster` (`fund_cluster_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_2` FOREIGN KEY (`rc_id`) REFERENCES `responsibility_center` (`rc_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_4` FOREIGN KEY (`approver_id`) REFERENCES `approver` (`approver_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_5` FOREIGN KEY (`oopap_id`) REFERENCES `oopap` (`oopap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ors_ibfk_6` FOREIGN KEY (`object_code_id`) REFERENCES `financial_object_code` (`object_code_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
