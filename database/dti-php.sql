-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 04:35 AM
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
-- Table structure for table `account_name`
--

CREATE TABLE `account_name` (
  `account_id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `NCA_NO` int(123) NOT NULL,
  `NCA_DATE` timestamp NOT NULL DEFAULT current_timestamp(),
  `FUND_SOURCE` varchar(123) NOT NULL,
  `type` enum('EMDS','REGULAR LCCA') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_name`
--

INSERT INTO `account_name` (`account_id`, `account_name`, `account_number`, `NCA_NO`, `NCA_DATE`, `FUND_SOURCE`, `type`) VALUES
(1, 'DTI RO XI', '2075-9006-81', 878, '2025-04-30 01:11:42', '01101101', 'EMDS'),
(2, 'DTI XI RAPID GOT COUNTERPART', '2075-9020-61', 0, '2025-04-30 01:11:42', '02101151', 'EMDS'),
(3, 'DTI COCONUT LEVY FUND', '2075-9020-70', 0, '2025-04-30 01:11:42', '07308601', 'EMDS'),
(4, 'DTI XII MSMEDC', '2075-9020-88', 0, '2025-04-30 01:11:42', '03104362', 'EMDS'),
(5, 'DTI XII (TRUST)', '2075-9015-88', 0, '2025-04-30 01:11:42', '07308601', 'EMDS'),
(6, 'DTI YAMAN GENSAN', '2075-9021-93', 0, '2025-04-30 01:11:42', '07308601', 'EMDS'),
(7, 'DTI RAPID LOAN PROCEEDS', '0752-1120-45', 0, '2025-04-30 01:11:42', '', 'REGULAR LCCA'),
(8, 'DTI RAPID GRANT', '0752-0952-99', 0, '2025-04-30 01:11:42', '', 'REGULAR LCCA'),
(9, 'DTI BSMED_RTCXII_ADMIN EXPENSEFUND FOR CFIDP IMPLEMENTATION', '0752-2103-22', 0, '2025-04-30 01:11:42', '', 'REGULAR LCCA');

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
(395, 'Other Supplies and Materials', '5020399000'),
(396, 'Cash - Modified Disbursement System (MDS), Special Account', '1010405000'),
(397, 'Cash - Modified Disbursement System (MDS), Trust', '1010406000');

-- --------------------------------------------------------

--
-- Table structure for table `allotment`
--

CREATE TABLE `allotment` (
  `id` int(11) NOT NULL,
  `fiscal_year` int(11) NOT NULL,
  `program_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `date_created` date NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
-- Table structure for table `batch_ada`
--

CREATE TABLE `batch_ada` (
  `batch_id` int(11) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `fund_code` varchar(20) NOT NULL,
  `bank_info` varchar(255) NOT NULL,
  `total_gross` decimal(15,2) DEFAULT NULL,
  `total_withholding` decimal(15,2) DEFAULT NULL,
  `total_net` decimal(15,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batch_ada`
--

INSERT INTO `batch_ada` (`batch_id`, `reference_no`, `payment_date`, `fund_code`, `bank_info`, `total_gross`, `total_withholding`, `total_net`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`) VALUES
(36, '101-04-001-003-2025', '2025-04-24', '01101101', 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81', 300.00, NULL, 300.00, 'jafk', 'Pending', '2025-04-24 03:27:11', '2025-04-24 03:35:08', 'Cashier');

-- --------------------------------------------------------

--
-- Table structure for table `batch_ada_dvs`
--

CREATE TABLE `batch_ada_dvs` (
  `id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `dv_id` int(11) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `gross_amount` decimal(15,2) NOT NULL,
  `withholding_tax` decimal(15,2) NOT NULL,
  `net_amount` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `draft_project`
--

CREATE TABLE `draft_project` (
  `draft_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `payee` varchar(100) NOT NULL,
  `cash_allotment` double(40,2) NOT NULL,
  `balances` double(40,2) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `draft_project`
--

INSERT INTO `draft_project` (`draft_id`, `account_id`, `payee`, `cash_allotment`, `balances`, `created_at`) VALUES
(3, 2, 'thank that', 1212.00, 1212.00, '2025-04-14');

-- --------------------------------------------------------

--
-- Table structure for table `dv`
--

CREATE TABLE `dv` (
  `dv_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `ors_id` int(255) NOT NULL,
  `account_id` int(255) NOT NULL,
  `dv_no` varchar(255) NOT NULL,
  `vat` double(40,2) NOT NULL,
  `vat_amount` double(40,2) NOT NULL,
  `tax_base` double(40,2) NOT NULL,
  `tax_1` double(40,2) NOT NULL,
  `tax_1_amount` double(40,2) NOT NULL,
  `tax_2` double(40,2) NOT NULL,
  `tax_2_amount` double(40,2) NOT NULL,
  `net_amount` double(40,2) NOT NULL,
  `total_amount` double(40,2) NOT NULL,
  `chief_accountant` varchar(255) NOT NULL,
  `regional_director` varchar(255) NOT NULL,
  `status` enum('Pending','Endorsed') NOT NULL,
  `endorsement_date` datetime DEFAULT NULL,
  `endorsement_remarks` varchar(255) DEFAULT NULL,
  `check_no` varchar(255) DEFAULT NULL,
  `ada_no` varchar(255) DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `dv_ors`
--

CREATE TABLE `dv_ors` (
  `dv_ors_id` int(11) NOT NULL,
  `dv_id` int(11) NOT NULL,
  `ors_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dv`
--

INSERT INTO `dv` (`dv_id`, `date`, `ors_id`, `account_id`, `dv_no`, `vat`, `vat_amount`, `tax_base`, `tax_1`, `tax_1_amount`, `tax_2`, `tax_2_amount`, `net_amount`, `total_amount`, `chief_accountant`, `regional_director`, `status`, `endorsement_date`, `endorsement_remarks`, `check_no`, `ada_no`, `payment_type`) VALUES
(107, '2025-05-05', 157, 1, '1-25-05-001', 12.00, 0.00, 11548.65, 0.00, 0.00, 0.00, 0.00, 11548.65, 11548.65, 'NEIL ANTHONY T. MORALA', 'FLORA D. POLITUD-GABUNALES, CESO V', 'Endorsed', '2025-05-05 07:47:10', '', '', '', ''),
(108, '2025-02-04', 119, 1, '1-25-02-002', 12.00, 0.00, 1200.00, 0.00, 0.00, 0.00, 0.00, 1200.00, 1200.00, 'NEIL ANTHONY T. MORALA', 'FLORA D. POLITUD-GABUNALES, CESO V', 'Pending', '2025-05-05 09:28:33', '', '', '', '');

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
(111, 107, 328, 'debit', 11548.65),
(112, 107, 280, 'credit', 200.00),
(113, 107, 281, 'credit', 609.52),
(114, 107, 382, 'credit', 10739.13),
(115, 108, 311, 'debit', 1200.00),
(116, 108, 382, 'credit', 1200.00);

-- --------------------------------------------------------

--
-- Table structure for table `dv_non_ors`
--

CREATE TABLE `dv_non_ors` (
  `dv_non_ors_id` int(11) NOT NULL,
  `fund_cluster_id` int(255) NOT NULL,
  `oopap_id` int(255) NOT NULL,
  `date` date NOT NULL,
  `dv_no` varchar(255) NOT NULL,
  `payee_id` int(255) NOT NULL,
  `rc_id` int(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `total_amount` double(40,2) NOT NULL,
  `tax_base` double(40,2) NOT NULL,
  `vat` int(255) NOT NULL,
  `vat_amount` double(40,2) NOT NULL,
  `tax_1` double(40,2) NOT NULL,
  `tax_1_amount` double(40,2) NOT NULL,
  `tax_2` double(40,2) NOT NULL,
  `tax_2_amount` double(40,2) NOT NULL,
  `net_amount` double(40,2) NOT NULL,
  `approver_id` int(255) NOT NULL,
  `chief_accountant` varchar(255) NOT NULL,
  `regional_director` varchar(255) NOT NULL,
  `status` enum('Pending','Endorsed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dv_non_ors_entry`
--

CREATE TABLE `dv_non_ors_entry` (
  `dv_non_ors_entry_id` int(11) NOT NULL,
  `dv_non_ors_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `type` enum('debit','credit') NOT NULL,
  `amount` double(40,2) NOT NULL
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
  `ors_id` int(255) NOT NULL,
  `jev_no` varchar(255) NOT NULL,
  `administrative_aide` varchar(255) NOT NULL,
  `accountant` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jev`
--

INSERT INTO `jev` (`jev_id`, `date`, `dv_id`, `ors_id`, `jev_no`, `administrative_aide`, `accountant`) VALUES
(18, '2025-05-05', 107, 157, '1-25-05-001', 'JINNARD B. LUBATON', 'NEIL ANTHONY T. MORALA');

-- --------------------------------------------------------

--
-- Table structure for table `merged_payees`
--

CREATE TABLE `merged_payees` (
  `merge_id` int(11) NOT NULL,
  `merge_name` varchar(255) NOT NULL COMMENT 'Merged payee group name',
  `description` text DEFAULT NULL COMMENT 'Description of the merged group',
  `payee_type` enum('Internal','External') NOT NULL DEFAULT 'Internal' COMMENT 'Type of payees in this group',
  `created_by` varchar(100) DEFAULT NULL COMMENT 'User who created this merge group',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `processed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores merged payee groups';

--
-- Dumping data for table `merged_payees`
--

INSERT INTO `merged_payees` (`merge_id`, `merge_name`, `description`, `payee_type`, `created_by`, `created_at`, `updated_at`, `processed`) VALUES
(1, 'dti', 'asgag', 'Internal', 'Cashier', '2025-04-30 00:37:30', '2025-04-30 00:37:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `merged_payee_items`
--

CREATE TABLE `merged_payee_items` (
  `item_id` int(11) NOT NULL,
  `merge_id` int(11) NOT NULL COMMENT 'Reference to merged_payees.merge_id',
  `dv_id` int(11) NOT NULL COMMENT 'Reference to dv.dv_id',
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores individual DVs in a merged payee group';

-- --------------------------------------------------------

--
-- Stand-in structure for view `merged_payee_totals`
-- (See below for the actual view)
--
CREATE TABLE `merged_payee_totals` (
`merge_id` int(11)
,`merge_name` varchar(255)
,`payee_type` enum('Internal','External')
,`total_dvs` bigint(21)
,`total_amount` double(19,2)
);

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
(78, 113, 87, 2550.00),
(79, 114, 87, 25000.00),
(80, 115, 87, 3100.00),
(81, 116, 87, 3650.00),
(82, 117, 87, 1900.00),
(83, 118, 87, 15000.00),
(84, 119, 280, 1200.00),
(85, 120, 288, 19061.51),
(86, 121, 297, 912.00),
(87, 122, 280, 1200.00),
(88, 123, 288, 11714.89),
(89, 124, 280, 3000.00),
(90, 124, 281, 3000.00),
(91, 124, 283, 21000.00),
(92, 124, 288, 24000.00),
(93, 124, 297, 3000.00),
(99, 127, 280, 9750.00),
(100, 127, 1743926826, 2350.00),
(101, 127, 283, 5000.00),
(102, 127, 285, 600.00),
(103, 127, 286, 800.00),
(104, 127, 297, 13500.00),
(105, 128, 280, 4500.00),
(106, 128, 1743926826, 7500.00),
(107, 128, 283, 2348.00),
(108, 128, 285, 1600.00),
(109, 128, 297, 1000.00),
(110, 129, 280, 950.00),
(111, 129, 1743926826, 6000.00),
(112, 129, 283, 4000.00),
(113, 129, 285, 600.00),
(114, 129, 297, 11000.00),
(115, 130, 280, 1800.00),
(116, 132, 1743926828, 100.00),
(117, 133, 288, 11493.24),
(118, 134, 280, 14500.00),
(119, 134, 281, 30000.00),
(120, 134, 1743926826, 5000.00),
(121, 134, 283, 3000.00),
(122, 134, 285, 500.00),
(123, 134, 288, 10000.00),
(124, 134, 297, 10000.00),
(125, 134, 293, 15000.00),
(126, 134, 294, 11000.00),
(127, 135, 280, 3450.00),
(128, 136, 280, 450.00),
(129, 137, 280, 3450.00),
(130, 138, 297, 12950.00),
(131, 139, 288, 8623.39),
(132, 140, 280, 3450.00),
(133, 141, 283, 3000.00),
(134, 142, 280, 4600.00),
(135, 143, 280, 4600.00),
(136, 144, 288, 9927.87),
(137, 145, 280, 750.00),
(138, 146, 280, 750.00),
(139, 147, 288, 9662.36),
(140, 148, 280, 5000.00),
(141, 148, 281, -3000.00),
(142, 148, 1743926826, 12000.00),
(143, 148, 283, 19000.00),
(144, 148, 288, -24000.00),
(145, 148, 297, 5000.00),
(146, 149, 280, 9600.00),
(147, 149, 1743926826, 4175.00),
(148, 149, 283, 5000.00),
(149, 149, 285, 600.00),
(150, 149, 286, 1000.00),
(151, 149, 297, 27000.00),
(152, 150, 281, 20600.00),
(153, 150, 283, 1174.00),
(154, 150, 285, 400.00),
(155, 151, 280, 1500.00),
(156, 151, 281, 25000.00),
(157, 151, 1743926826, 1000.00),
(158, 151, 283, 1000.00),
(159, 151, 285, 600.00),
(160, 151, 288, 7000.00),
(161, 151, 297, 6000.00),
(162, 152, 280, 6500.00),
(163, 152, 281, 30300.00),
(164, 152, 1743926826, 1000.00),
(165, 152, 284, 15000.00),
(166, 152, 285, 100.00),
(167, 152, 288, 12500.00),
(168, 152, 294, 5500.00),
(169, 153, 280, 878.00),
(170, 154, 297, 94890.00),
(171, 155, 1743926826, 2095.00),
(172, 156, 297, 1942.50),
(173, 157, 288, 11548.65),
(174, 159, 65, 1213.00),
(175, 160, 66, 4400.00),
(176, 161, 64, 69029.88),
(177, 162, 63, 1791.60),
(178, 163, 66, 1200.00),
(179, 164, 66, 2998.00),
(180, 165, 67, 2763.60),
(181, 166, 68, 43948.08),
(182, 167, 81, 16994.59),
(183, 168, 66, 1499.00),
(184, 169, 56, 0.00),
(185, 170, 66, 1200.00),
(186, 171, 66, 4917.00),
(187, 172, 66, 4800.00),
(188, 173, 64, 75833.85),
(189, 175, 69, 33900.00),
(190, 176, 65, 450.00),
(191, 177, 59, 5000.00),
(192, 178, 63, 2997.35),
(193, 179, 81, 9450.00),
(194, 180, 66, 1299.00),
(195, 181, 66, 1499.00),
(196, 182, 63, 1650.00),
(197, 183, 60, 5294.09),
(198, 184, 81, 9825.00),
(199, 185, 68, 47936.00),
(200, 186, 70, 200.00),
(201, 186, 76, 2012.00),
(202, 186, 81, 600.00),
(203, 186, 82, 335.00),
(204, 186, 92, 1455.00),
(205, 187, 56, 8150.00),
(206, 188, 65, 1609.00),
(207, 189, 56, 14690.64),
(208, 190, 66, 1689.00),
(209, 191, 56, 3000.00),
(210, 192, 66, 4678.20),
(211, 193, 66, 2000.00),
(212, 194, 66, 1299.00),
(213, 195, 64, 78785.48),
(214, 196, 81, 9980.00),
(215, 197, 67, 2637.60),
(216, 198, 67, 2703.12),
(217, 199, 63, 3031.80),
(218, 200, 63, 1875.00),
(219, 201, 60, 10000.00),
(220, 202, 81, 5369.85),
(221, 203, 60, 19332.45),
(222, 203, 81, 808.00),
(223, 204, 56, 0.00),
(224, 205, 56, 390.00),
(225, 205, 65, 175.00),
(226, 205, 68, 199.00),
(227, 205, 70, 330.00),
(228, 205, 76, 1480.00),
(229, 206, 81, 3500.00),
(230, 207, 66, 1698.00),
(231, 208, 66, 1499.00),
(232, 209, 64, 65773.17),
(233, 210, 56, 0.00),
(234, 211, 56, 0.00),
(235, 212, 81, 21500.00),
(236, 213, 81, 3000.00),
(237, 214, 65, 2151.00),
(238, 215, 66, 2000.00),
(239, 216, 66, 1299.00),
(240, 217, 63, 1998.30),
(241, 218, 67, 2758.56),
(242, 219, 66, 4510.99),
(243, 220, 63, 2000.00),
(244, 221, 82, 2010.00),
(245, 222, 87, 4725.00),
(246, 223, 87, 14900.00),
(247, 224, 61, 2750.00),
(248, 225, 61, 34500.00),
(249, 226, 91, 14335.18),
(250, 227, 87, 33142.00),
(251, 228, 91, 997.00),
(252, 229, 87, 8500.00),
(253, 230, 87, 39900.00),
(254, 231, 87, 22950.00),
(255, 232, 56, 1000.00),
(256, 232, 58, 5000.00),
(257, 232, 60, 1000.00),
(258, 232, 63, 500.00),
(259, 232, 64, 3000.00),
(260, 232, 65, 500.00),
(261, 232, 66, 500.00),
(262, 232, 89, 16500.00),
(263, 233, 56, 4000.00),
(264, 233, 58, 7000.00),
(265, 233, 60, 10000.00),
(266, 233, 63, 2500.00),
(267, 233, 64, 26000.00),
(268, 233, 65, 500.00),
(269, 233, 66, 1800.00),
(270, 233, 67, 3000.00),
(271, 233, 68, 2500.00),
(272, 233, 81, 1000.00),
(273, 233, 87, 4000.00),
(274, 234, 56, 4000.00),
(275, 234, 57, 1000.00),
(276, 234, 58, 5000.00),
(277, 234, 60, 5000.00),
(278, 234, 63, 1500.00),
(279, 234, 64, 17000.00),
(280, 234, 65, 1000.00),
(281, 234, 66, 1800.00),
(282, 234, 67, 4000.00),
(283, 234, 81, 3000.00),
(284, 234, 87, 2000.00),
(285, 234, 89, 74725.00),
(286, 235, 56, 5000.00),
(287, 235, 58, 3000.00),
(288, 235, 60, 7500.00),
(289, 235, 63, 1200.00),
(290, 235, 64, 15000.00),
(291, 235, 65, 1000.00),
(292, 235, 66, 1800.00),
(293, 235, 67, 1800.00),
(294, 235, 81, 2000.00),
(295, 235, 87, 9700.00),
(296, 235, 89, 59000.00),
(297, 236, 56, 5000.00),
(298, 236, 57, 2000.00),
(299, 236, 58, 6000.00),
(300, 236, 60, 8000.00),
(301, 236, 63, 1500.00),
(302, 236, 64, 11000.00),
(303, 236, 65, 1000.00),
(304, 236, 66, 1800.00),
(305, 236, 67, 4000.00),
(306, 236, 81, 2000.00),
(307, 236, 87, 4000.00),
(308, 236, 89, 39893.00),
(309, 237, 56, 5000.00),
(310, 237, 57, 1500.00),
(311, 237, 58, 11500.00),
(312, 237, 60, 11000.00),
(313, 237, 63, 2126.00),
(314, 237, 65, 1065.00),
(315, 237, 66, 2000.00),
(316, 237, 67, 5125.00),
(317, 237, 76, 9500.00),
(318, 237, 81, 2500.00),
(319, 237, 87, 8000.00),
(320, 237, 89, 3500.00);

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
  `budget_officer` varchar(255) NOT NULL,
  `status` enum('Pending','Endorsed') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ors`
--

INSERT INTO `ors` (`ors_id`, `fund_cluster_id`, `services_id`, `date`, `ors_no`, `payee_id`, `notes`, `purpose`, `rc_id`, `account_id`, `oopap_id`, `total_amount`, `approver_id`, `budget_officer`, `status`) VALUES
(113, 3, 11, '2025-01-09', 'ADMINPOLICY-25-01-001', 26, 'Meals for Hinugyaw Festival Civic Military Parade on January 10, 2025', 'To Payment of', 1, 342, 1, 2550.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(114, 3, 11, '2025-01-14', 'ADMINPOLICY-25-01-002', 27, 'Meals during COA Entrance Conference', 'To Payment of', 1, 342, 1, 25000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(115, 3, 11, '2025-01-15', 'ADMINPOLICY-25-01-003', 28, 'Meals for DTI 12 Staff during Bloodletting Activity of BJMP 12.', 'To Payment of', 1, 342, 1, 3100.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(116, 3, 11, '2025-01-22', 'ADMINPOLICY-25-01-004', 29, 'Meals for free Vaccine Drive of IPHO South Cotabato', 'To Payment of', 1, 342, 1, 3650.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(117, 3, 11, '2025-01-22', 'ADMINPOLICY-25-01-005', 30, 'OTOP for Vaccine Drive of IPHO South Cotabato', 'To Payment of', 1, 342, 1, 1900.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(118, 3, 11, '2025-01-28', 'ADMINPOLICY-25-01-006', 29, 'Meals during AFMD QMS Strategic Planning on Jan 24, 2025', 'To Payment of', 1, 342, 1, 15000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(119, 3, 23, '2025-02-04', 'SSF-25-02-001', 39, 'Attendance to Choir Performance and SSF Monitoring on Jan 30-31, 2025', 'To Disburse', 9, 311, 7, 1200.00, 3, 'CONNIE M. BARNACHEA', 'Endorsed'),
(120, 3, 23, '2025-02-05', 'SSF-25-02-002', 39, 'Payment of Salary for January 6-31, 2025', 'To Payment of', 9, 328, 7, 19061.51, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(121, 3, 23, '2025-02-10', 'SSF-25-02-003', 39, 'Reimbursement of meals during 2025 WFP Review on Feb 7, 2025', 'To Disburse', 9, 342, 7, 912.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(122, 3, 23, '2025-02-17', 'SSF-25-02-004', 39, 'Attendance to SSF Project Monitoring in Sarangani and GenSan on Feb 12-13, 2025', 'To Disburse', 9, 311, 7, 1200.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(123, 3, 23, '2025-02-18', 'SSF-25-02-005', 39, 'Payment of Salary for February 1-15, 2025', 'To Payment of', 9, 328, 7, 11714.89, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(124, 3, 23, '2025-02-19', 'SSF-25-02-006', 40, 'Transfer of Fund for SSF Project for 1st quarter 2025', 'To Transfer', 9, 311, 7, 54000.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(127, 3, 23, '2025-02-19', 'SSF-25-02-007', 41, 'Transfer of Fund for SSF Project for 1st quarter 2025', 'To Transfer', 9, 311, 7, 32000.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(128, 3, 23, '2025-02-19', 'SSF-25-02-008', 42, 'Transfer of Fund for SSF Project for 1st quarter 2025', 'To Transfer', 9, 311, 7, 16948.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(129, 3, 23, '2025-02-19', 'SSF-25-02-009', 43, 'Transfer of Fund for SSF Project for 1st quarter 2025', 'To Transfer', 9, 311, 7, 22550.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(130, 3, 23, '2025-02-20', 'SSF-25-02-010', 44, 'Attendance to various SSF Monitoring', 'To Disburse', 9, 311, 7, 1800.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(132, 3, 23, '2025-02-27', 'SSF-25-02-011', 32, 'Replenishment of PCF - Notary of contract', 'To Disburse', 9, 325, 7, 100.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(133, 3, 23, '2025-03-04', 'SSF-25-03-012', 39, 'Payment of Salary for February 16-28, 2025', 'To Payment of', 9, 328, 7, 11493.24, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(134, 3, 23, '2025-03-07', 'SSF-25-03-013', 45, 'Transfer of Fund for SSF Project for 1st quarter 2025', 'To Transfer', 9, 311, 7, 99000.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(135, 3, 23, '2025-03-11', 'SSF-25-03-014', 39, 'Attendance to SSF Project Monitoring in Cotabato Mar 5-7, 2025', 'To Disburse', 9, 311, 7, 3450.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(136, 3, 23, '2025-03-11', 'SSF-25-03-015', 46, 'Traveling expenses during SSF Visit', 'To Disburse', 9, 311, 7, 450.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(137, 3, 23, '2025-03-11', 'SSF-25-03-016', 44, 'Attendance to SSF Project Monitoring in Cotabato Mar 5-7, 2025', 'To Disburse', 9, 311, 7, 3450.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(138, 3, 23, '2025-03-13', 'SSF-25-03-017', 29, 'Snacks for Reproductive Health Seminar on Mar 24, 2025', 'To Payment of', 9, 342, 7, 12950.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(139, 3, 23, '2025-03-13', 'SSF-25-03-018', 39, 'Payment of Salary for March 1-15, 2025', 'To Payment of', 9, 328, 7, 8623.39, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(140, 3, 23, '2025-03-24', 'SSF-25-03-019', 47, 'Attendance to SSF Project Monitoring in Cotabato Mar 5-7, 2025', 'To Disburse', 9, 311, 7, 3450.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(141, 3, 23, '2025-03-25', 'SSF-25-03-020', 32, 'Replenishment of PCF - SSF Monitoring', 'To Disburse', 9, 355, 7, 3000.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(142, 3, 23, '2025-03-28', 'SSF-25-03-021', 39, 'Attendance to SSF Project Monitoring in Sultan Kudarat on Mar 19-31, 2025', 'To Disburse', 9, 311, 7, 4600.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(143, 3, 23, '2025-03-28', 'SSF-25-03-022', 44, 'Attendance to SSF Project Monitoring in Sultan Kudarat on Mar 19-31, 2025', 'To Disburse', 9, 311, 7, 4600.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(144, 3, 23, '2025-04-04', 'SSF-25-04-023', 39, 'Payment of Salary for March 16-31, 2025', 'To Payment of', 9, 328, 7, 9927.87, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(145, 3, 23, '2025-04-14', 'SSF-25-04-024', 44, 'Attendance to DOST\'s SBTS Enterprises through Mulit-stakeholder Collaboration on Mar 25, 2025', 'To Disburse', 9, 311, 7, 750.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(146, 3, 23, '2025-04-21', 'SSF-25-04-025', 44, 'Attendance to DAR VISTA Regional Coordinating Committe Meeting on Apr 4, 2025', 'To Disburse', 9, 311, 7, 750.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(147, 3, 23, '2025-04-22', 'SSF-25-04-026', 39, 'Payment of Salary for April 1-15, 2025', 'To Payment of', 9, 328, 7, 9662.36, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(148, 3, 23, '2025-04-24', 'SSF-25-04-027', 40, 'Transfer of Fund for SSF Project for April 2025', 'To Transfer', 9, 311, 7, 14000.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(149, 3, 23, '2025-04-24', 'SSF-25-04-028', 41, 'Transfer of Fund for SSF Project for April 2025', 'To Transfer', 9, 311, 7, 47375.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(150, 3, 23, '2025-04-24', 'SSF-25-04-029', 42, 'Transfer of Fund for SSF Project for April 2025', 'To Transfer', 9, 313, 7, 22174.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(151, 3, 23, '2025-04-24', 'SSF-25-04-030', 43, 'Transfer of Fund for SSF Project for April 2025', 'To Transfer', 9, 311, 7, 42100.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(152, 3, 23, '2025-04-24', 'SSF-25-04-031', 30, 'Transfer of Fund for SSF Project for April 2025', 'To Transfer', 9, 311, 7, 70900.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(153, 3, 23, '2025-04-28', 'SSF-25-04-032', 44, 'Attendance to Mahintana Foundations ProPEACE 7th Joint PIT-PMT Meeting on Apr 22, 2025', 'To Disburse', 9, 311, 7, 878.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(154, 3, 23, '2025-04-29', 'SSF-25-04-033', 48, 'Lease of Venue for UPSCALE for SSF on May 6, 2025', 'To Payment of', 9, 342, 7, 94890.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(155, 3, 23, '2025-05-05', 'SSF-25-05-034', 49, 'Supplies for UPSCALE of SSF in Wearables and Homestyle on May 6, 2025', 'To Payment of', 9, 314, 7, 2095.00, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(156, 3, 23, '2025-05-05', 'SSF-25-05-035', 30, 'OTOP for UPSCALE of SSF in Wearables and Homestyle on May 6, 2025', 'To Payment of', 9, 342, 7, 1942.50, 3, 'CONNIE M. BARNACHEA', 'Pending'),
(157, 3, 23, '2025-05-05', 'SSF-25-05-036', 39, 'Payment of Salary for April 16-30, 2025', 'To Payment of', 9, 328, 7, 11548.65, 3, 'CONNIE M. BARNACHEA', 'Endorsed'),
(159, 3, 2, '2025-01-08', 'RM-25-01-001', 50, 'Payment of Postage Services for Dec 2024.', 'To Payment of', 1, 320, 1, 1213.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(160, 3, 2, '2025-01-13', 'RM-25-01-002', 51, 'Telephone Expenses - Mobile for Dec, 2024', 'To Payment of', 1, 357, 1, 4400.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(161, 3, 2, '2025-01-15', 'RM-25-01-003', 52, 'Payment of Electricity for the period of 12/12/24-1/11/25', 'To Payment of', 1, 319, 1, 69029.88, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(162, 3, 2, '2025-01-17', 'RM-25-01-004', 53, 'Payment of Water for the period of Dec 14, 2024 to January 16, 2025', 'To Payment of', 1, 318, 1, 1791.60, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(163, 3, 2, '2025-01-17', 'RM-25-01-005', 37, 'Reimbursement of Telephone Expenses - Mobile for 11/24/24 to 12/26/24', 'To Disburse', 1, 357, 1, 1200.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(164, 3, 2, '2025-01-20', 'RM-25-01-006', 54, 'Reimbursement of mobile expense for 10/16/24-12/15/24', 'To Disburse', 1, 357, 1, 2998.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(165, 3, 2, '2025-01-23', 'RM-25-01-007', 55, 'Telephone Expenses for the period of December 1-31, 2024', 'To Payment of', 1, 321, 1, 2763.60, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(166, 3, 2, '2025-01-28', 'RM-25-01-008', 56, 'Payment of Internet Expenses for January 2025', 'To Payment of', 1, 322, 1, 43948.08, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(167, 3, 2, '2025-02-04', 'RM-25-02-009', 57, 'Preventive maintenance of Mitsubishi Expander', 'To Payment of', 1, 333, 1, 16994.59, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(168, 3, 2, '2025-02-04', 'RM-25-02-010', 54, 'Reimbursement of mobile expense for 12/16/24 to 1/15/25', 'To Disburse', 1, 357, 1, 1499.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(169, 3, 2, '2025-02-05', 'RM-25-02-011', 58, 'Remittance for Ralph Luren Biñas', 'To Payment of', 1, 311, 1, 0.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(170, 3, 2, '2025-02-12', 'RM-25-02-012', 59, 'Reimbursement of Telephone Expense - Mobile for the month of JAN 2025', 'To Disburse', 1, 357, 1, 1200.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(171, 3, 2, '2025-02-12', 'RM-25-02-013', 51, 'Telephone Expenses - Mobile for JAN 2025', 'To Payment of', 1, 357, 1, 4917.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(172, 3, 2, '2025-02-12', 'RM-25-02-014', 60, 'Reimbursement of mobile expense for 8/16/24 to 12/15/24', 'To Disburse', 1, 357, 1, 4800.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(173, 3, 2, '2025-02-14', 'RM-25-02-015', 52, 'Payment of Electricity for the period of 1/11/25-2/11/25', 'To Payment of', 1, 319, 1, 75833.85, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(175, 3, 2, '2025-02-14', 'RM-25-02-016', 54, 'Payment of Extraordinary and Miscellaneous for 1st Quarter 2025', 'To Payment of', 1, 324, 1, 33900.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(176, 3, 2, '2025-02-14', 'RM-25-02-017', 50, 'Payment of Postage Services for Jan 1-31, 2025', 'To Payment of', 1, 320, 1, 450.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(177, 3, 2, '2025-02-14', 'RM-25-02-018', 61, 'Payment of Accountable Forms No. 51-C', 'To Payment of', 1, 315, 1, 5000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(178, 3, 2, '2025-02-17', 'RM-25-02-019', 53, 'Payment of Water for the period of Jan 17, 2025 to Feb 17, 2025', 'To Payment of', 1, 318, 1, 2997.35, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(179, 3, 2, '2025-02-18', 'RM-25-02-020', 21, 'Payment of preventive maintenance for Toyota Vios.', 'To Payment of', 1, 333, 1, 9450.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(180, 3, 2, '2025-02-18', 'RM-25-02-021', 37, 'Reimbursement of Telephone Expenses - Mobile for 12/27/24 to 1/26/25', 'To Disburse', 1, 357, 1, 1299.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(181, 3, 2, '2025-02-18', 'RM-25-02-022', 60, 'Reimbursement of mobile expense for 12/16/24 to 1/15/25', 'To Disburse', 1, 357, 1, 1499.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(182, 3, 2, '2025-02-20', 'RM-25-02-023', 63, 'Payment of Water for the period of Jan 2025', 'To Payment of', 1, 318, 1, 1650.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(183, 3, 2, '2025-02-21', 'RM-25-02-024', 32, 'Replenishment of PCF - Staff servicing', 'To Disburse', 1, 355, 1, 5294.09, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(184, 3, 2, '2025-02-21', 'RM-25-02-025', 64, 'Maintenance of Honda City', 'To Payment of', 1, 333, 1, 9825.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(185, 3, 2, '2025-02-26', 'RM-25-02-026', 56, 'Payment of Internet Expenses for February 2025', 'To Payment of', 1, 322, 1, 47936.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(186, 3, 2, '2025-02-27', 'RM-25-02-027', 32, 'Replenishment of PCF - Bank charges, taxes, laundry', 'To Disburse', 1, 325, 1, 4602.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(187, 3, 2, '2025-02-27', 'RM-25-02-028', 65, 'CA of travel to pick up Tamaraw Utility Van at DTI Head Office', 'To Cash Advance', 1, 311, 1, 8150.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(188, 3, 2, '2025-03-10', 'RM-25-03-029', 50, 'Payment of Postage Services for Feb 1-28, 2025', 'To Payment of', 1, 320, 1, 1609.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(189, 3, 2, '2025-03-11', 'RM-25-03-030', 66, 'GPC - Plane Ticket to pick-up utility vehicle (VILLAREAL, ROBLES)', 'To Payment of', 1, 311, 1, 14690.64, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(190, 3, 2, '2025-03-13', 'RM-25-03-031', 60, 'Reimbursement of mobile expense for 1/16/25 to 2/15/25', 'To Disburse', 1, 357, 1, 1689.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(191, 3, 2, '2025-03-13', 'RM-25-03-032', 67, 'To pick up Tamaraw Utility Van at DTI Head Office', 'To Disburse', 1, 311, 1, 3000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(192, 3, 2, '2025-03-17', 'RM-25-03-033', 51, 'Telephone Expenses - Mobile for FEB 2025', 'To Payment of', 1, 357, 1, 4678.20, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(193, 3, 2, '2025-03-17', 'RM-25-03-034', 59, 'Reimbursement of Telephone Expense - Mobile for the month of FEB 2025', 'To Disburse', 1, 357, 1, 2000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(194, 3, 2, '2025-03-18', 'RM-25-03-035', 37, 'Reimbursement of Telephone Expenses - Mobile for 1/27/25 to 2/26/25', 'To Disburse', 1, 357, 1, 1299.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(195, 3, 2, '2025-03-18', 'RM-25-03-036', 52, 'Payment of Electricity for the period of 2/11/25-3/14/25', 'To Payment of', 1, 319, 1, 78785.48, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(196, 3, 2, '2025-03-18', 'RM-25-03-037', 21, 'Periodic Maintenance of Nissan Urvan', 'To Payment of', 1, 333, 1, 9980.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(197, 3, 2, '2025-03-24', 'RM-25-03-038', 55, 'Telephone Expenses for the period of January 1-31 2025', 'To Payment of', 1, 321, 1, 2637.60, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(198, 3, 2, '2025-03-24', 'RM-25-03-039', 55, 'Telephone Expenses for the period of February 1-28 2025', 'To Payment of', 1, 321, 1, 2703.12, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(199, 3, 2, '2025-03-24', 'RM-25-03-040', 53, 'Payment of Water for the period of 2/15/25 to 3/17/25', 'To Payment of', 1, 318, 1, 3031.80, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(200, 3, 2, '2025-03-24', 'RM-25-03-041', 63, 'Payment of Water for the period of Feb 2025', 'To Payment of', 1, 318, 1, 1875.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(201, 3, 2, '2025-03-25', 'RM-25-03-042', 32, 'Replenishment of PCF - staff servicing', 'To Disburse', 1, 355, 1, 10000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(202, 3, 2, '2025-03-26', 'RM-25-03-043', 68, 'Periodic Maintenance of Toyota Hilux Tamaraw', 'To Payment of', 1, 333, 1, 5369.85, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(203, 3, 2, '2025-04-02', 'RM-25-04-044', 32, 'Replenishment of PCF - staff servicing', 'To Disburse', 1, 355, 1, 20140.45, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(204, 3, 2, '2025-04-03', 'RM-25-04-045', 29, 'CANCELLED OBLIGATIONS', 'To Payment of', 1, 311, 1, 0.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(205, 3, 2, '2025-04-03', 'RM-25-04-046', 32, 'Replenishment of PCF - tricycle fare, check up of aircon, delivery fee of OR', 'To Disburse', 1, 311, 1, 2574.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(206, 3, 2, '2025-04-08', 'RM-25-04-047', 69, 'Repair of Housing Evaporator of Nissan Urvan and Aircon Cleaning', 'To Payment of', 1, 333, 1, 3500.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(207, 3, 2, '2025-04-08', 'RM-25-04-048', 60, 'Reimbursement of mobile expense for 2/16/25 to 3/15/25', 'To Disburse', 1, 357, 1, 1698.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(208, 3, 2, '2025-04-08', 'RM-25-04-049', 54, 'Reimbursement of mobile expense for 2/16/25 to 3/15/25', 'To Disburse', 1, 357, 1, 1499.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(209, 3, 2, '2025-04-21', 'RM-25-04-050', 52, 'Payment of Electricity for the period of 3/14/25-4/11/25', 'To Payment of', 1, 319, 1, 65773.17, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(210, 3, 2, '2025-04-21', 'RM-25-04-051', 58, 'Payment of remittances of JOCOS for 1st quarter 2025', 'To Payment of', 1, 311, 1, 0.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(211, 3, 2, '2025-04-21', 'RM-25-04-052', 71, 'Payment of remittances of JOCOS for 1st quarter 2025', 'To Payment of', 1, 311, 1, 0.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(212, 3, 2, '2025-04-21', 'RM-25-04-053', 72, 'Replacement of tires and wheel alignment for Honda City', 'To Payment of', 1, 333, 1, 21500.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(213, 3, 2, '2025-04-22', 'RM-25-04-054', 73, 'Replacement of Bolt Hub for Honda City', 'To Payment of', 1, 333, 1, 3000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(214, 3, 2, '2025-04-22', 'RM-25-04-055', 50, 'Payment of Postage Services for March 1-31, 2025', 'To Payment of', 1, 320, 1, 2151.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(215, 3, 2, '2025-04-22', 'RM-25-04-056', 59, 'Reimbursement of Telephone Expense - Mobile for the month of MAR 2025', 'To Disburse', 1, 357, 1, 2000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(216, 3, 2, '2025-04-22', 'RM-25-04-057', 37, 'Reimbursement of Telephone Expenses - Mobile for 2/27/25 to 3/26/25', 'To Disburse', 1, 357, 1, 1299.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(217, 3, 2, '2025-04-22', 'RM-25-04-058', 53, 'Payment of Water for the period of 3/17/25 to 4/16/25', 'To Payment of', 1, 318, 1, 1998.30, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(218, 3, 2, '2025-04-25', 'RM-25-04-059', 55, 'Telephone Expenses for the period of March 1-31, 2025', 'To Payment of', 1, 321, 1, 2758.56, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(219, 3, 2, '2025-04-25', 'RM-25-04-060', 51, 'Telephone Expenses - Mobile for MARCH 2025', 'To Payment of', 1, 357, 1, 4510.99, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(220, 3, 2, '2025-04-28', 'RM-25-04-061', 63, 'Payment of Water for the period of Mar 2025', 'To Payment of', 1, 318, 1, 2000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(221, 3, 3, '2025-01-15', 'ONETIME-25-01-001', 74, 'Payment of license for Mitsubishi Expander.', 'To Payment of', 1, 337, 1, 2010.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(222, 3, 7, '2025-01-08', 'HR RR-25-01-001', 29, 'Snacks during send-off ceremony for Mr. Joven Quiriones for AAS Program', 'To Payment of', 1, 342, 1, 4725.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(223, 3, 9, '2025-01-13', 'HR RSP-25-01-001', 75, 'Meals for HRMPSB Assessment and Deliberation on Jan 3, 2025', 'To Payment of', 1, 342, 1, 14900.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(224, 3, 10, '2025-01-08', 'SUPPLY-25-01-001', 76, 'Supplies for Office use', 'To Payment of', 1, 317, 1, 2750.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(225, 3, 10, '2025-01-14', 'SUPPLY-25-01-002', 30, 'Supplies for Office use', 'To Payment of', 1, 317, 1, 34500.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(226, 3, 10, '2025-01-15', 'SUPPLY-25-01-003', 37, 'Reimbursement of subscription of Airtable for CY 2025.', 'To Disburse', 1, 345, 1, 14335.18, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(227, 3, 15, '2025-01-21', 'MSSD-25-01-001', 59, 'Cash Advance for the OCI of MSSD, IDD and CPD on Jan 30-31, 2025.', 'To Cash Advance', 15, 342, 1, 33142.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(228, 3, 15, '2025-01-31', 'MSSD-25-01-002', 78, 'Reimbursement of Adobe subscription for January 2025', 'To Disburse', 15, 345, 1, 997.00, 2, 'CONNIE M. BARNACHEA', 'Pending'),
(229, 3, 16, '2025-01-08', 'MSSD-ISO-25-01-001', 28, 'Meals for 2025 QMS Planning and Audit Preparation Meeting on Jan 7, 2025', 'To Payment of', 15, 342, 1, 8500.00, 2, 'CONNIE M. BARNACHEA', 'Pending'),
(230, 3, 17, '2025-01-09', 'MSSD-PLAN-25-01-001', 28, 'Meals for 2025 1st Quarter ManCom', 'To Payment of', 15, 342, 1, 39900.00, 2, 'CONNIE M. BARNACHEA', 'Pending'),
(231, 3, 17, '2025-01-09', 'MSSD-PLAN-25-01-002', 28, 'Meals for 2024 4th Quarter Management Review on January 15, 2025', 'To Payment of', 15, 342, 1, 22950.00, 2, 'CONNIE M. BARNACHEA', 'Pending'),
(232, 3, 20, '2025-01-20', 'POF-25-01-001', 41, 'POF to cover expenses for the month of January 2025', 'To Transfer', 1, 311, 1, 28000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(233, 3, 20, '2025-01-20', 'POF-25-01-002', 41, 'POF to cover expenses for the month of January 2025', 'To Transfer', 1, 311, 1, 62300.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(234, 3, 20, '2025-01-20', 'POF-25-01-003', 43, 'POF to cover expenses for the month of January 2025', 'To Disburse', 1, 311, 1, 120025.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(235, 3, 20, '2025-01-20', 'POF-25-01-004', 45, 'POF to cover expenses for the month of January 2025', 'To Disburse', 1, 311, 1, 107000.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(236, 3, 20, '2025-01-20', 'POF-25-01-005', 40, 'POF to cover expenses for the month of January 2025', 'To Transfer', 1, 311, 1, 86193.00, 1, 'CONNIE M. BARNACHEA', 'Pending'),
(237, 3, 20, '2025-01-20', 'POF-25-01-006', 42, 'POF to cover expenses for the month of January 2025', 'To Transfer', 1, 311, 1, 62816.00, 1, 'CONNIE M. BARNACHEA', 'Pending');

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
  `contact_no` varchar(15) DEFAULT NULL,
  `payee_type` varchar(123) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payee`
--

INSERT INTO `payee` (`payee_id`, `payee_name`, `tin_no`, `bank_acc_no`, `address`, `nature`, `contact_no`, `payee_type`) VALUES
(16, '3G Gensan Hotel', '', '', '', '', '', ''),
(17, '3D Advertising', '712-786-936-0000', '', '', '', '', ''),
(18, '8 OZ Prints', '', 'LBP - 0751-2051-55', '', '', '', ''),
(19, 'ACE CENTERPOINT', '', 'LBP - CA 0752-1040-93', 'Koronadal City, South Cotabato', '', '', 'External'),
(20, 'ACE HARDWARE PHIL. INC.', '200-035-311-0000', 'LBP - CA 0752-1040-93', '', '', '', ''),
(21, 'ADC AUTOMOTIVE SHOP BY ANNIE LIZA R CERALVO', '', 'LBP - SA 0751-1741-01', '', '', '', ''),
(22, 'ADWERKZ PRINTING SOLUTIONS/PAUL RYAN C. BARCELONA', '', 'LBP - SA 3416-0029-23', '', '', '', ''),
(23, 'AFL 168 CORPORATION', '', 'LBP - SA 0751-1769-37', '', '', '', ''),
(24, 'AHR WOODCRAFT CENTER BY ALLAN B. HIMALLA ', '', 'LBP - SA 0751-1759-90', '', '', '', ''),
(25, 'BENJO G. BASID', '', '', 'Koronadal City', '', '', ''),
(26, 'TRISHA\'S BURGER STATION/ PILAR F. PARDO', '', '', 'KORONADAL CITY', '', '', 'external'),
(27, 'TJ\'S TITO JUN SEAFOOD GRILL/BAR CATERING SERVICES', '', '', 'KORONADAL CITY', '', '', 'external'),
(28, 'WAN-BAN CATERING SERVICES', '', '', 'KORONADAL CITY', '', '', 'external'),
(29, 'GENARO\'S CATERING', '', '', 'KORONADAL CITY', '', '', 'external'),
(30, 'SOUTHRAYS VALLEY FOOD ASSOCIATION', '', '', 'KORONADAL CITY', '', '', 'external'),
(31, 'CINCO NIÑAS RESTO', '', '', 'KORONADAL CITY', '', '', 'external'),
(32, 'MILDRED T. SUCOL', '', '', 'KORONADAL CITY', '', '', 'internal'),
(33, 'THE FARM AT CARPENTER HILL, INC.', '', '', 'KORONADAL CITY', '', '', 'external'),
(34, 'MANG INASAL', '', '', 'KORONADAL CITY', '', '', 'external'),
(35, 'SARAH JANE T. TOLEDO', '', '', '', '', '', 'internal'),
(36, 'RYAN GAZO', '', '', '', '', '', 'external'),
(37, 'HAZEL E. HAUTEA', '', '', 'KORONADAL CITY', '', '', 'internal'),
(38, 'BABS RESTAURANT', '', '', 'KORONADAL CITY', '', '', 'external'),
(39, 'SERAFIN JAY S. BASIYA', '', '', '', '', '', 'internal'),
(40, 'DTI - SOUTH COTABATO', '', '', 'SOUTH COTABATO', '', '', 'internal'),
(41, 'DTI - COTABATO PROVINCE', '', '', 'COTABATO PROVINCE', '', '', 'internal'),
(42, 'DTI - SARANGANI PROVINCE', '', '', 'SARANGANI PROVINCE', '', '', 'internal'),
(43, 'DTI - GENERAL SANTOS CITY', '', '', 'GENERAL SANTOS CITY', '', '', 'internal'),
(44, 'ANGELITO B. VILLAR', '', '', '', '', '', 'internal'),
(45, 'DTI - SULTAN KUDARAT', '', '', 'SULTAN KUDARAT', '', '', 'internal'),
(46, 'RAMIL B. GOLORAN', '', '', '', '', '', 'internal'),
(47, 'JOEL S. JASPE', '', '', 'KORONADAL CITY', '', '', 'internal'),
(48, 'GENSAN GREENLEAF HOTEL AND MANAGEMENT', '', '', 'GENERAL SANTOS CITY', '', '', 'external'),
(49, 'PEOPLE\'S GENERAL MERCHANDISE', '', '', 'KORONADAL CITY', '', '', 'external'),
(50, 'JRS BUSINESS CORPORATION', '', '', '', '', '', 'external'),
(51, 'SMART COMMUNICATIONS, INC.', '', '', '', '', '', 'external'),
(52, 'SOUTH COTABATO I ELECTRIC COOPERATIVE, INC.', '', '', 'KORONADAL CITY', '', '', 'external'),
(53, 'CITY OF KORONADAL WATER DISTRICT', '', '', 'KORONADAL CITY', '', '', 'external'),
(54, 'FLORA P. GABUNALES', '', '', '', '', '', 'internal'),
(55, 'MARBEL TELEPHONE SYSTEM, INC.', '', '', 'KORONADAL CITY', '', '', 'external'),
(56, 'PLDT, INC.', '', '', '', '', '', 'external'),
(57, 'MINDANAO INTEGRATED COMMERCIAL ENTERPRISES, INC.', '', '', '', '', '', 'external'),
(58, 'PHILIPPINE HEALTH INSURANCE CORP.', '', '', 'KORONADAL CITY', '', '', 'external'),
(59, 'EPIFANIA L. EALDAMA', '', '', '', '', '', 'internal'),
(60, 'MA. THERESA T. CHUA', '', '', '', '', '', 'internal'),
(61, 'NATIONAL PRINTING PRESS', '', '', '', '', '', 'external'),
(62, 'ADC AUTOMOTIVE SHOP', '', '', '', '', '', 'external'),
(63, 'ESTER WATER MARKET FILTRATION & PURIFICATION SYSTEM ', '', '', 'KORONADAL CITY', '', '', 'external'),
(64, 'JAYVANBENJO CORPORATION', '', '', '', '', '', 'external'),
(65, 'NOEL P. VILLAREAL', '', '', '', '', '', 'internal'),
(66, 'LANDBANK OF THE PHILIPPINES', '', '', 'KORONADAL CITY', '', '', 'external'),
(67, 'RYAN D. ROBLES', '', '', '', '', '', 'internal'),
(68, 'TOYOTA KORONADAL SERIVCE CENTER', '', '', 'KORONADAL CITY', '', '', 'external'),
(69, 'DIGS CAR AIRCON SERVICES', '', '', '', '', '', 'external'),
(70, 'PHILIPPINE HEALTH INSURANCE CORP.', '', '', 'KORONADAL CITY', '', '', 'external'),
(71, 'PAG-IBIG FUND KORONADAL', '', '', 'KORONADAL CITY', '', '', 'external'),
(72, 'KWIK FIT TYRE DEPOT', '', '', '', '', '', 'external'),
(73, 'AP AUTOMOTIVE MECHANICAL SERVICES', '', '', '', '', '', 'external'),
(74, 'LAND TRANSPORTATION OFFICE', '', '', 'KORONADAL CITY', '', '', 'external'),
(75, 'SHEILA\'S PARK FAMILY RESTO & FASTFOOD', '', '', 'KORONADAL CITY', '', '', 'external'),
(76, 'SOX FOOD PRODUCTS MANUFACTURING', '', '', '', '', '', 'external'),
(77, 'TRISHA\'S BURGER STATION/ PILAR F. PARDO', '', '', 'KORONADAL CITY', '', '', 'external'),
(78, 'MA. ADA N. ALBURO', '', '', 'KORONADAL CITY', '', '', 'external');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `dv_id` int(11) NOT NULL,
  `ada_no` int(123) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_type` enum('Check','ADA') NOT NULL,
  `reference_no` varchar(100) NOT NULL,
  `amount` double(40,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` varchar(255) NOT NULL,
  `is_merged_payment` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Flag indicating if this is part of a merged payment',
  `merge_id` int(11) DEFAULT NULL COMMENT 'Reference to the merged_payees table',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Completed','Returned') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `program_id` int(11) NOT NULL,
  `program_code` varchar(50) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(63, 1, 318, 200000.00, 200000.00, '2025-03-31'),
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
(280, 7, 311, 310000.00, 228122.00, '2025-03-31'),
(281, 7, 313, 461000.00, 355100.00, '2025-03-31'),
(283, 7, 355, 180000.00, 115478.00, '2025-03-31'),
(284, 7, 317, 50000.00, 35000.00, '2025-03-31'),
(285, 7, 357, 30000.00, 25000.00, '2025-03-31'),
(286, 7, 368, 100000.00, 98200.00, '2025-03-31'),
(287, 7, 327, 300000.00, 300000.00, '2025-03-31'),
(288, 7, 328, 970000.00, 858468.09, '2025-03-31'),
(289, 7, 359, 650000.00, 650000.00, '2025-03-31'),
(290, 7, 360, 10000.00, 10000.00, '2025-03-31'),
(291, 7, 339, 400000.00, 400000.00, '2025-03-31'),
(292, 7, 374, 50000.00, 50000.00, '2025-03-31'),
(293, 7, 363, 50000.00, 35000.00, '2025-03-31'),
(294, 7, 346, 100000.00, 83500.00, '2025-03-31'),
(295, 7, 376, 50000.00, 50000.00, '2025-03-31'),
(297, 7, 342, 311000.00, 123805.50, '2025-03-31'),
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
(438, 15, 337, 0.00, 0.00, '2025-04-06'),
(440, 1, 314, 150000.00, 150000.00, '2025-05-05'),
(1743926826, 7, 314, 150000.00, 108880.00, '2025-05-05'),
(1743926828, 7, 325, 0.00, -100.00, '2025-05-05');

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
(13, '22-001-03-00012-17', '22-001-03-00012', 'Non Office', 'SAA', 'Sub-allotment'),
(15, '22-001-03-00012-01', '22-001-03-00012', 'Office', 'DTIMSSD', 'Management Support Services Division');

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
(4, 'OUTSOURCED SERVICES', 'OUTSOURCED', 1),
(5, 'SECURITY SERVICES', 'SECURITY', 1),
(6, 'LEARNING AND DEVELOPMENT', 'HR LD', 1),
(7, 'REWARDS AND RECOGNITION', 'HR RR', 1),
(8, 'PERFORMANCE MANAGEMENT SYSTEM', 'HR PMS', 1),
(9, 'RECRUITMENT, SELECTION AND PLACEMENT', 'HR RSP', 1),
(10, 'SUPPLY & PROPERTY MANAGEMENT', 'SUPPLY', 1),
(11, 'ADMINISTRATION & POLICY', 'ADMIN&POLICY', 1),
(12, 'SAFETY AND HEALTH', 'OSH', 1),
(13, 'RBAC MEETING', 'RBAC', 1),
(14, 'GADGET COST', 'GADGET', 1),
(15, 'MSSD ORD', 'MSSD', 1),
(16, 'MSSD ISO', 'MSSD-ISO', 1),
(17, 'MSSD PLANNING', 'MSSD-PLAN', 1),
(18, 'MSSD-RECORDS', 'MSSD-RECORDS', 1),
(19, 'MSSD-KM', 'MSSD-KM', 1),
(20, 'PROVINCIAL OPERATING FUND (POF) - MANDATORY', 'POF', 1),
(23, 'SHARED SERVICE FACILITY', 'SSF', 7);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting`, `value`, `description`) VALUES
(1, 'system_name', 'DTI Financial Management System', 'System Name'),
(2, 'system_logo', 'logo.jpg', 'System Logo'),
(3, 'fiscal_year', '2025', 'Current Fiscal Year'),
(4, 'email_host', 'smtp.example.com', 'Email SMTP Host'),
(5, 'email_username', 'notification@example.com', 'Email Username'),
(6, 'email_password', '', 'Email Password');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Budget Officer','Chief Accountant','Bookkeeper','Guest','Cashier') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(114537886, 'Cashier User', 'cashier', '$2y$10$ivzvPS6kkArxRq0iUc5bDOZ9wiZwdIeqsC8THY.2Ghl47AU4WhzN2', 'Cashier', '2025-04-15 03:36:17', '2025-04-15 03:36:17'),
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
(941568985, 'Ritz 1010101', 'r', '$2y$10$ecM8TboeXRQGzC0Kgvf9guKopaZJA6egJtkqHbYCVwX5kQhTZ9lHO', 'Admin', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure for view `merged_payee_totals`
--
DROP TABLE IF EXISTS `merged_payee_totals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `merged_payee_totals`  AS SELECT `mp`.`merge_id` AS `merge_id`, `mp`.`merge_name` AS `merge_name`, `mp`.`payee_type` AS `payee_type`, count(`mpi`.`dv_id`) AS `total_dvs`, sum(`dv`.`net_amount`) AS `total_amount` FROM ((`merged_payees` `mp` left join `merged_payee_items` `mpi` on(`mp`.`merge_id` = `mpi`.`merge_id`)) left join `dv` on(`mpi`.`dv_id` = `dv`.`dv_id`)) GROUP BY `mp`.`merge_id`, `mp`.`merge_name`, `mp`.`payee_type` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_name`
--
ALTER TABLE `account_name`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `account_title`
--
ALTER TABLE `account_title`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `allotment`
--
ALTER TABLE `allotment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fiscal_year` (`fiscal_year`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `approver`
--
ALTER TABLE `approver`
  ADD PRIMARY KEY (`approver_id`);

--
-- Indexes for table `batch_ada`
--
ALTER TABLE `batch_ada`
  ADD PRIMARY KEY (`batch_id`),
  ADD UNIQUE KEY `unique_reference` (`reference_no`);

--
-- Indexes for table `batch_ada_dvs`
--
ALTER TABLE `batch_ada_dvs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dv_batch` (`dv_id`,`batch_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Indexes for table `draft_project`
--
ALTER TABLE `draft_project`
  ADD PRIMARY KEY (`draft_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `dv`
--
ALTER TABLE `dv`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `ors_id` (`ors_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `dv_history`
--
ALTER TABLE `dv_history`
  ADD PRIMARY KEY (`dvhis_id`),
  ADD KEY `dv_id` (`dv_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `dv_non_ors`
--
ALTER TABLE `dv_non_ors`
  ADD PRIMARY KEY (`dv_non_ors_id`),
  ADD KEY `fund_cluster_id` (`fund_cluster_id`),
  ADD KEY `oopap_id` (`oopap_id`),
  ADD KEY `approver_id` (`approver_id`),
  ADD KEY `payee_id` (`payee_id`),
  ADD KEY `rc_id` (`rc_id`);

--
-- Indexes for table `dv_non_ors_entry`
--
ALTER TABLE `dv_non_ors_entry`
  ADD PRIMARY KEY (`dv_non_ors_entry_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `dv_non_ors_id` (`dv_non_ors_id`);

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
-- Indexes for table `merged_payees`
--
ALTER TABLE `merged_payees`
  ADD PRIMARY KEY (`merge_id`);

--
-- Indexes for table `merged_payee_items`
--
ALTER TABLE `merged_payee_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_merge_id` (`merge_id`),
  ADD KEY `idx_dv_id` (`dv_id`);

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
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `dv_id` (`dv_id`),
  ADD KEY `idx_merge_id` (`merge_id`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`program_id`);

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
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting` (`setting`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_name`
--
ALTER TABLE `account_name`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `account_title`
--
ALTER TABLE `account_title`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=399;

--
-- AUTO_INCREMENT for table `allotment`
--
ALTER TABLE `allotment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approver`
--
ALTER TABLE `approver`
  MODIFY `approver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `batch_ada`
--
ALTER TABLE `batch_ada`
  MODIFY `batch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `batch_ada_dvs`
--
ALTER TABLE `batch_ada_dvs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `draft_project`
--
ALTER TABLE `draft_project`
  MODIFY `draft_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dv`
--
ALTER TABLE `dv`
  MODIFY `dv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `dv_history`
--
ALTER TABLE `dv_history`
  MODIFY `dvhis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `dv_non_ors`
--
ALTER TABLE `dv_non_ors`
  MODIFY `dv_non_ors_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `dv_non_ors_entry`
--
ALTER TABLE `dv_non_ors_entry`
  MODIFY `dv_non_ors_entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `fund_cluster`
--
ALTER TABLE `fund_cluster`
  MODIFY `fund_cluster_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jev`
--
ALTER TABLE `jev`
  MODIFY `jev_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `merged_payees`
--
ALTER TABLE `merged_payees`
  MODIFY `merge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `merged_payee_items`
--
ALTER TABLE `merged_payee_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `obligation_history`
--
ALTER TABLE `obligation_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

--
-- AUTO_INCREMENT for table `oopap`
--
ALTER TABLE `oopap`
  MODIFY `oopap_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `ors`
--
ALTER TABLE `ors`
  MODIFY `ors_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=238;

--
-- AUTO_INCREMENT for table `payee`
--
ALTER TABLE `payee`
  MODIFY `payee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1743926830;

--
-- AUTO_INCREMENT for table `responsibility_center`
--
ALTER TABLE `responsibility_center`
  MODIFY `rc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=969649707;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `batch_ada_dvs`
--
ALTER TABLE `batch_ada_dvs`
  ADD CONSTRAINT `batch_ada_dvs_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batch_ada` (`batch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_ada_dvs_ibfk_2` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE;

--
-- Constraints for table `draft_project`
--
ALTER TABLE `draft_project`
  ADD CONSTRAINT `draft_project_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account_name` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dv`
--
ALTER TABLE `dv`
  ADD CONSTRAINT `dv_ibfk_1` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account_name` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dv_history`
--
ALTER TABLE `dv_history`
  ADD CONSTRAINT `dv_history_ibfk_1` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_history_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dv_non_ors`
--
ALTER TABLE `dv_non_ors`
  ADD CONSTRAINT `dv_non_ors_ibfk_1` FOREIGN KEY (`fund_cluster_id`) REFERENCES `fund_cluster` (`fund_cluster_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_non_ors_ibfk_2` FOREIGN KEY (`oopap_id`) REFERENCES `oopap` (`oopap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_non_ors_ibfk_4` FOREIGN KEY (`approver_id`) REFERENCES `approver` (`approver_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_non_ors_ibfk_5` FOREIGN KEY (`payee_id`) REFERENCES `payee` (`payee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_non_ors_ibfk_7` FOREIGN KEY (`rc_id`) REFERENCES `responsibility_center` (`rc_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dv_non_ors_entry`
--
ALTER TABLE `dv_non_ors_entry`
  ADD CONSTRAINT `dv_non_ors_entry_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account_title` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_non_ors_entry_ibfk_2` FOREIGN KEY (`dv_non_ors_id`) REFERENCES `dv_non_ors` (`dv_non_ors_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jev`
--
ALTER TABLE `jev`
  ADD CONSTRAINT `jev_ibfk_1` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jev_ibfk_2` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `merged_payee_items`
--
ALTER TABLE `merged_payee_items`
  ADD CONSTRAINT `fk_merged_payee_items_dv_id` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_merged_payee_items_merge_id` FOREIGN KEY (`merge_id`) REFERENCES `merged_payees` (`merge_id`) ON DELETE CASCADE;

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
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payments_merge_id` FOREIGN KEY (`merge_id`) REFERENCES `merged_payees` (`merge_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Table structure for table `dv_multiple_ors`
--

CREATE TABLE `dv_multiple_ors` (
  `id` int(11) NOT NULL,
  `dv_id` int(11) NOT NULL,
  `ors_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dv_multiple_ors`
--
ALTER TABLE `dv_multiple_ors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dv_id` (`dv_id`),
  ADD KEY `ors_id` (`ors_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dv_multiple_ors`
--
ALTER TABLE `dv_multiple_ors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dv_multiple_ors`
--
ALTER TABLE `dv_multiple_ors`
  ADD CONSTRAINT `dv_multiple_ors_ibfk_1` FOREIGN KEY (`dv_id`) REFERENCES `dv` (`dv_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dv_multiple_ors_ibfk_2` FOREIGN KEY (`ors_id`) REFERENCES `ors` (`ors_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
