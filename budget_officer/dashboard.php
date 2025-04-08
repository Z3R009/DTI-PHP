<?php
include '../DBConnection.php';

// Get current year and month
$current_year = date('Y');
$current_month = date('m');

// Get selected year and month from POST or use current values
$selected_year = isset($_POST['year']) ? $_POST['year'] : $current_year;
$selected_month = isset($_POST['month']) ? $_POST['month'] : $current_month;

// Get years for dropdown (last 5 years)
$years = range($current_year - 4, $current_year);

// Months array for dropdown
$months = array(
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
);

// Function to get total allotment and balances for a specific oopap_id
function getTotals($connection, $oopap_id, $year, $month) {
    // Get allotment based on year only
    $allotment_query = "SELECT SUM(allotment) AS total_allotment 
                       FROM project 
                       WHERE oopap_id = ? 
                       AND YEAR(created_at) = ?";
    
    $stmt = $connection->prepare($allotment_query);
    $stmt->bind_param("ii", $oopap_id, $year);
    $stmt->execute();
    $allotment_result = $stmt->get_result();
    $allotment_row = $allotment_result->fetch_assoc();
    
    // Get balances based on both year and month
    $balances_query = "SELECT SUM(balances) AS total_balances 
                      FROM project 
                      WHERE oopap_id = ? 
                      AND YEAR(created_at) = ?
                      AND MONTH(created_at) = ?";
    
    $stmt = $connection->prepare($balances_query);
    $stmt->bind_param("iii", $oopap_id, $year, $month);
    $stmt->execute();
    $balances_result = $stmt->get_result();
    $balances_row = $balances_result->fetch_assoc();
    
    return [
        'allotment' => $allotment_row['total_allotment'] ?? 0,
        'balances' => $balances_row['total_balances'] ?? 0
    ];
}

// Function to get recent transactions
function getRecentTransactions($connection, $limit = 10) {
    // Get both ORS and DV transactions
    $query = "
    (SELECT 
        'ors' AS type,
        o.ors_id AS id,
        o.ors_no AS document_no,
        o.date AS transaction_date,
        p.payee_name AS payee,
        o.total_amount AS amount,
        o.purpose AS description,
        oo.oopap_name AS category
    FROM ors o
    JOIN payee p ON o.payee_id = p.payee_id
    JOIN oopap oo ON o.oopap_id = oo.oopap_id
    ORDER BY o.date DESC
    LIMIT ?)
    UNION ALL
    (SELECT 
        'dv' AS type,
        d.dv_id AS id,
        d.dv_no AS document_no,
        d.date AS transaction_date,
        p.payee_name AS payee,
        d.net_amount AS amount,
        (SELECT o.purpose FROM ors o WHERE o.ors_id = d.ors_id) AS description,
        (SELECT oo.oopap_name FROM ors o JOIN oopap oo ON o.oopap_id = oo.oopap_id WHERE o.ors_id = d.ors_id) AS category
    FROM dv d
    JOIN ors o ON d.ors_id = o.ors_id
    JOIN payee p ON o.payee_id = p.payee_id
    ORDER BY d.date DESC
    LIMIT ?)
    ORDER BY transaction_date DESC
    LIMIT ?";
    
    $limit_half = ceil($limit / 2);
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iii", $limit_half, $limit_half, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

// Get totals for different sections
$gas_totals = getTotals($connection, 1, $selected_year, $selected_month);
$oo1_totals = getTotals($connection, 2, $selected_year, $selected_month);
$oo2_totals = getTotals($connection, 3, $selected_year, $selected_month);
$oo3_totals = getTotals($connection, 4, $selected_year, $selected_month);
$oo3_1_totals = getTotals($connection, 5, $selected_year, $selected_month);
$oo3_2_totals = getTotals($connection, 6, $selected_year, $selected_month);
$oo4_1_1_totals = getTotals($connection, 8, $selected_year, $selected_month);
$oo4_1_2_totals = getTotals($connection, 9, $selected_year, $selected_month);

// Calculate grand totals
$total_allotment = $gas_totals['allotment'] + $oo1_totals['allotment'] + $oo2_totals['allotment'] + 
                  $oo3_totals['allotment'] + $oo3_1_totals['allotment'] + $oo3_2_totals['allotment'] + 
                  $oo4_1_1_totals['allotment'] + $oo4_1_2_totals['allotment'];

$total_balances = $gas_totals['balances'] + $oo1_totals['balances'] + $oo2_totals['balances'] + 
                 $oo3_totals['balances'] + $oo3_1_totals['balances'] + $oo3_2_totals['balances'] + 
                 $oo4_1_1_totals['balances'] + $oo4_1_2_totals['balances'];

// Calculate utilization percentage
$utilization_percentage = $total_allotment > 0 ? (($total_allotment - $total_balances) / $total_allotment) * 100 : 0;

// Get recent transactions
$recent_transactions = getRecentTransactions($connection, 5);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Financial Reports - DTI Region 12</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/apexcharts/apexcharts.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">

    <style>
        .main {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        /* Enhanced financial card styles */
        #monthlyTrendChart {
        /* Remove the height: 100px; */
        flex-grow: 1;
        }
        .financial-card {
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
        }
        .financial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
        }
        .card-icon {
            width: 56px;
            height: 56px;
            background-color: rgba(13, 110, 253, 0.15);
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            transition: all 0.3s ease;
        }
        .card-icon i {
            transition: transform 0.3s ease;
        }
        .financial-card:hover .card-icon i {
            transform: scale(1.2);
        }
        .utilization-progress {
            height: 12px;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .utilization-progress .progress-bar {
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }
        .summary-card {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
        }
        .summary-card .card-icon {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .trend-indicator {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        .trend-up {
            color: #10b981;
        }
        .trend-down {
            color: #ef4444;
        }
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        .report-section {
            margin-bottom: 40px;
        }
        .report-title {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 25px;
            font-weight: 600;
            color: #344767;
            position: relative;
        }
        .report-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: #0d6efd;
        }
        
        /* Transaction list styles */
        .transaction-list {
            max-height: 500px;
            overflow-y: auto;
        }
        .transaction-item {
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        .transaction-item:hover {
            border-left-color: #0d6efd;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .transaction-item.type-ors {
            border-left-color: #10b981;
        }
        .transaction-item.type-dv {
            border-left-color: #6366f1;
        }
        .transaction-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .badge-ors {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .badge-dv {
            background-color: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }
        
        /* Animation styles */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
        .animate-delay-4 { animation-delay: 0.4s; }
        
        /* Enhanced card slider styles */
        .swipe-container {
            position: relative;
            overflow: hidden;
            padding: 15px 0;
            border-radius: 12px;
        }
        .transition-all {
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .budget-card-item {
            padding: 0 10px;
            position: relative;
        }
        .indicator-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .indicator-dot.active {
            width: 24px;
            border-radius: 4px;
            background-color: #0d6efd;
        }
        .swipe-container {
            touch-action: pan-y;
        }
        .category-tab {
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            margin-right: 8px;
            margin-bottom: 8px;
            border: 1px solid #e0e0e0;
        }
        .category-tab.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        .col-lg-5.report-section .card.financial-card {
        height: calc(100% - 40px); /* 40px accounts for the header space */
        }
        .col-lg-5.report-section .card-body {
   display: flex;
   flex-direction: column;
   height: 100%;
}
.report-section .card {
   margin-bottom: 0;
}
    </style>
</head>

<body>
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center">
            <div>
                <h1>Dashboard</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div>
                <div class="dropdown">
                    <!-- <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-1"></i> Export
                    </button> -->
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-pdf me-2"></i>PDF Report</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-excel me-2"></i>Excel Data</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-printer me-2"></i>Print Dashboard</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <section class="section dashboard">
            <!-- Filter Section -->
            <!-- <div class="filter-section mb-4 animate-fade-in">
                <form method="post" class="row g-3">
                    <div class="col-md-4">
                        <label for="year" class="form-label fw-semibold"><i class="bi bi-calendar-event me-1"></i>Fiscal Year</label>
                        <select class="form-select shadow-sm" id="year" name="year" required>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="month" class="form-label fw-semibold"><i class="bi bi-calendar-month me-1"></i>Month</label>
                        <select class="form-select shadow-sm" id="month" name="month" required>
                            <?php foreach ($months as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $selected_month == $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-funnel-fill me-2"></i>Apply Filter
                        </button>
                    </div>
                </form>
            </div> -->

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-12 col-md-6 col-lg-3 animate-fade-in animate-delay-1">
                    <div class="card summary-card financial-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0" style="color: #ffffff;">Total Budget</h6>
                                    <h2 class="mt-2 mb-0">₱<?php echo number_format($total_allotment, 2); ?></h2>
                                    <p class="text-white-50 mb-0">Year <?php echo $selected_year; ?></p>
                                </div>
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 animate-fade-in animate-delay-2">
                    <div class="card summary-card financial-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0" style="color: #ffffff;">Available Balance</h6>
                                    <h2 class="mt-2 mb-0">₱<?php echo number_format($total_balances, 2); ?></h2>
                                    <p class="text-white-50 mb-0">As of <?php echo $months[$selected_month]; ?></p>
                                </div>
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 animate-fade-in animate-delay-3">
                    <div class="card summary-card financial-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0" style="color: #ffffff;">Utilization Rate</h6>
                                    <h2 class="mt-2 mb-0"><?php echo number_format($utilization_percentage, 1); ?>%</h2>
                                    <div class="progress utilization-progress mt-2">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $utilization_percentage; ?>%"></div>
                                    </div>
                                </div>
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 animate-fade-in animate-delay-4">
                    <div class="card summary-card financial-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0" style="color: #ffffff;">Remaining Budget</h6>
                                    <h2 class="mt-2 mb-0">₱<?php echo number_format($total_allotment - $total_balances, 2); ?></h2>
                                    <p class="text-white-50 mb-0">For remaining months</p>
                                </div>
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-piggy-bank"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Budget Allocation by Category -->
                <div class="col-lg-7     report-section animate-fade-in">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="report-title">Budget Allocation by Category</h4>
                        <div class="d-flex">
                            <!-- <button type="button" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-grid me-1"></i> Grid View
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-list me-1"></i> List View
                            </button> -->
                        </div>
                    </div>
                    
                    <div class="card financial-card">
                        <div class="card-body">
                            <!-- Category tabs for navigation (optional)
                            <div class="d-flex flex-wrap mb-3">
                                <div class="category-tab active" data-type="all">All Categories</div>
                                <div class="category-tab" data-type="general">General Administration</div>
                                <div class="category-tab" data-type="operations">Operations</div>
                            </div> -->
                            
                            <!-- Swipeable navigation indicators -->
                            <div class="text-center mb-3">
                                <div class="d-flex justify-content-between align-items-center" style="margin-top: 20px;">
                                    <button id="prev-card" class="btn btn-sm btn-outline-secondary rounded-circle">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <div id="card-indicators" class="d-flex justify-content-center gap-2">
                                    </div>
                                    <button id="next-card" class="btn btn-sm btn-outline-secondary rounded-circle">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="swipe-container overflow-hidden">
                                <div id="budget-cards-wrapper" class="d-flex transition-all" style="transition: transform 0.3s ease;">
                                    
                                    <!-- GAS Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">GAS</h5>
                                            <span class="badge bg-primary">General Administration and Support</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($gas_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($gas_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?php echo $gas_totals['allotment'] > 0 ? (($gas_totals['allotment'] - $gas_totals['balances']) / $gas_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-primary">
                                                    <?php echo $gas_totals['allotment'] > 0 ? number_format((($gas_totals['allotment'] - $gas_totals['balances']) / $gas_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="gas.php" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- OO1 Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">OO1</h5>
                                            <span class="badge bg-info">Operations</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo1_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo1_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                style="width: <?php echo $oo1_totals['allotment'] > 0 ? (($oo1_totals['allotment'] - $oo1_totals['balances']) / $oo1_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-info">
                                                    <?php echo $oo1_totals['allotment'] > 0 ? number_format((($oo1_totals['allotment'] - $oo1_totals['balances']) / $oo1_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="oo1.php" class="btn btn-outline-info btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- OO2 Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">OO2</h5>
                                            <span class="badge bg-success">Operations</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo2_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo2_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: <?php echo $oo2_totals['allotment'] > 0 ? (($oo2_totals['allotment'] - $oo2_totals['balances']) / $oo2_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-success">
                                                    <?php echo $oo2_totals['allotment'] > 0 ? number_format((($oo2_totals['allotment'] - $oo2_totals['balances']) / $oo2_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="oo2.php" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- OO3 Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">OO3</h5>
                                            <span class="badge bg-warning">Operations</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo3_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo3_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                style="width: <?php echo $oo3_totals['allotment'] > 0 ? (($oo3_totals['allotment'] - $oo3_totals['balances']) / $oo3_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-warning">
                                                    <?php echo $oo3_totals['allotment'] > 0 ? number_format((($oo3_totals['allotment'] - $oo3_totals['balances']) / $oo3_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="oo3.php" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- OO3.1 Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">OO3.1</h5>
                                            <span class="badge bg-danger">Operations</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo3_1_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo3_1_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                style="width: <?php echo $oo3_1_totals['allotment'] > 0 ? (($oo3_1_totals['allotment'] - $oo3_1_totals['balances']) / $oo3_1_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-danger">
                                                    <?php echo $oo3_1_totals['allotment'] > 0 ? number_format((($oo3_1_totals['allotment'] - $oo3_1_totals['balances']) / $oo3_1_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="oo3_1.php" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- OO3.2 Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">OO3.2</h5>
                                            <span class="badge bg-secondary">Operations</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo3_2_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo3_2_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar bg-secondary" role="progressbar" 
                                                style="width: <?php echo $oo3_2_totals['allotment'] > 0 ? (($oo3_2_totals['allotment'] - $oo3_2_totals['balances']) / $oo3_2_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-secondary">
                                                    <?php echo $oo3_2_totals['allotment'] > 0 ? number_format((($oo3_2_totals['allotment'] - $oo3_2_totals['balances']) / $oo3_2_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="oo3_2.php" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- OO4.1.1 Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">OO4.1.1</h5>
                                            <span class="badge bg-dark">Operations</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo4_1_1_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo4_1_1_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar bg-dark" role="progressbar" 
                                                style="width: <?php echo $oo4_1_1_totals['allotment'] > 0 ? (($oo4_1_1_totals['allotment'] - $oo4_1_1_totals['balances']) / $oo4_1_1_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-dark">
                                                    <?php echo $oo4_1_1_totals['allotment'] > 0 ? number_format((($oo4_1_1_totals['allotment'] - $oo4_1_1_totals['balances']) / $oo4_1_1_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="oo4_1_1.php" class="btn btn-outline-dark btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- OO4.1.2 Card -->
                                    <div class="budget-card-item" style="min-width: 100%; flex-shrink: 0;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="card-title mb-0">OO4.1.2</h5>
                                            <span class="badge bg-primary">Operations</span>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Allotment</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo4_1_2_totals['allotment'], 2); ?></h4>
                                        </div>
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Balance</h6>
                                            <h4 class="mb-0">₱<?php echo number_format($oo4_1_2_totals['balances'], 2); ?></h4>
                                        </div>
                                        <div class="progress utilization-progress mb-2">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: <?php echo $oo4_1_2_totals['allotment'] > 0 ? (($oo4_1_2_totals['allotment'] - $oo4_1_2_totals['balances']) / $oo4_1_2_totals['allotment']) * 100 : 0; ?>%">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-muted">
                                                <span class="text-primary">
                                                    <?php echo $oo4_1_2_totals['allotment'] > 0 ? number_format((($oo4_1_2_totals['allotment'] - $oo4_1_2_totals['balances']) / $oo4_1_2_totals['allotment']) * 100, 1) : 0; ?>%
                                                </span> utilized
                                            </div>
                                            <a href="oo4_1_2.php" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Budget Chart -->
                    <div class="col-lg-5 report-section animate-fade-in animate-delay-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="report-title">Budget Utilization</h4>
                            <div class="dropdown">
                                <!-- <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartViewDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-bar-chart-fill me-1"></i> View As
                                </button> -->
                                <ul class="dropdown-menu" aria-labelledby="chartViewDropdown">
                                    <li><a class="dropdown-item" href="#" data-chart-type="bar">Bar Chart</a></li>
                                    <li><a class="dropdown-item" href="#" data-chart-type="pie">Pie Chart</a></li>
                                    <li><a class="dropdown-item" href="#" data-chart-type="line">Line Chart</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card financial-card h-100" >
                            <div class="card-body d-flex flex-column" >
                                <div id="monthlyTrendChart"   ></div>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- Recent Transactions Section -->
            <div class="row mt-4">
                <div class="col-12 report-section animate-fade-in">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="report-title">Recent Transactions</h4>
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye me-1"></i>View All Transactions
                        </a>
                    </div>
                    
                    <div class="card financial-card">
                        <div class="card-body">
                            <?php if (empty($recent_transactions)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">No Recent Transactions</h5>
                                    <p class="text-muted">Transactions will appear here once processed.</p>
                                </div>
                            <?php else: ?>
                                <div class="transaction-list" style="margin-top: 25px;">
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                        <div class="transaction-item type-<?php echo $transaction['type']; ?> animate-fade-in">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <?php if ($transaction['type'] == 'ors'): ?>
                                                                <div class="card-icon bg-light rounded-circle" style="width: 40px; height: 40px;">
                                                                    <i class="bi bi-file-earmark-text text-success"></i>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="card-icon bg-light rounded-circle" style="width: 40px; height: 40px;">
                                                                    <i class="bi bi-credit-card text-primary"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <div class="d-flex align-items-center">
                                                                <h6 class="mb-0"><?php echo htmlspecialchars($transaction['payee']); ?></h6>
                                                                <span class="ms-2 transaction-badge <?php echo $transaction['type'] == 'ors' ? 'badge-ors' : 'badge-dv'; ?>">
                                                                    <?php echo strtoupper($transaction['type']); ?>
                                                                </span>
                                                            </div>
                                                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($transaction['document_no']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="small text-muted">Category</div>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($transaction['category']); ?></div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="small text-muted">Amount</div>
                                                    <div class="fw-bold">₱<?php echo number_format($transaction['amount'], 2); ?></div>
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <?php 
                                                    $url = $transaction['type'] == 'ors' 
                                                        ? "ors_form.php?ors_no=" . urlencode($transaction['document_no']) 
                                                        : "dv_form.php?dv_no=" . urlencode($transaction['document_no']); 
                                                    ?>
                                                    <a href="<?php echo $url; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Budget cards swiper functionality
            const cardsWrapper = document.getElementById('budget-cards-wrapper');
            const cardItems = document.querySelectorAll('.budget-card-item');
            const prevBtn = document.getElementById('prev-card');
            const nextBtn = document.getElementById('next-card');
            const indicators = document.getElementById('card-indicators');
            
            let currentIndex = 0;
            const totalCards = cardItems.length;
            
            for (let i = 0; i < totalCards; i++) {
                const dot = document.createElement('div');
                dot.className = i === 0 ? 'indicator-dot active' : 'indicator-dot';
                dot.addEventListener('click', () => goToCard(i));
                indicators.appendChild(dot);
            }
            
            function updateIndicators() {
                const dots = document.querySelectorAll('.indicator-dot');
                dots.forEach((dot, i) => {
                    dot.className = i === currentIndex ? 'indicator-dot active' : 'indicator-dot';
                });
            }
            
            function goToCard(index) {
                if (index < 0) index = 0;
                if (index >= totalCards) index = totalCards - 1;
                
                currentIndex = index;
                cardsWrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
                updateIndicators();
            }
            
            prevBtn.addEventListener('click', () => goToCard(currentIndex - 1));
            nextBtn.addEventListener('click', () => goToCard(currentIndex + 1));

            let touchStartX = 0;
            let touchEndX = 0;
            
            const container = document.querySelector('.swipe-container');
            
            container.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, false);
            
            container.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, false);
            
            function handleSwipe() {
                const SWIPE_THRESHOLD = 50;
                if (touchEndX < touchStartX - SWIPE_THRESHOLD) {
                    // Swiped left
                    goToCard(currentIndex + 1);
                }
                if (touchEndX > touchStartX + SWIPE_THRESHOLD) {
                    // Swiped right
                    goToCard(currentIndex - 1);
                }
            }
            
            // Auto swipe
            let autoSwipeInterval;
            
            function startAutoSwipe() {
                autoSwipeInterval = setInterval(() => {
                    let nextIndex = currentIndex + 1;
                    if (nextIndex >= totalCards) nextIndex = 0;
                    goToCard(nextIndex);
                }, 5000); // Change slide every 5 seconds
            }
            
            function stopAutoSwipe() {
                clearInterval(autoSwipeInterval);
            }
            
            // Start auto swipe
            startAutoSwipe();
            
            // Stop auto swipe on interaction
            container.addEventListener('mouseenter', stopAutoSwipe);
            container.addEventListener('touchstart', stopAutoSwipe);
            
            // Resume auto swipe after interaction
            container.addEventListener('mouseleave', startAutoSwipe);
            
            // Category tabs functionality
            const categoryTabs = document.querySelectorAll('.category-tab');
            categoryTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    categoryTabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Filter logic would go here
                    const filterType = this.getAttribute('data-type');
                    // For now we'll just reset to the first card
                    goToCard(0);
                });
            });
            
            // Chart dropdown functionality
            const chartTypeLinks = document.querySelectorAll('[data-chart-type]');
            chartTypeLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const chartType = this.getAttribute('data-chart-type');
                    updateChartType(chartType);
                });
            });
            
            function updateChartType(type) {
                // Create new chart options based on type
                chart.updateOptions({
                    chart: {
                        type: type
                    }
                });
            }

            // Monthly Trend Chart
            var options = {
                series: [{
                    name: 'Allotment',
                    data: [<?php echo $gas_totals['allotment']; ?>, <?php echo $oo1_totals['allotment']; ?>, <?php echo $oo2_totals['allotment']; ?>, <?php echo $oo3_totals['allotment']; ?>, <?php echo $oo3_1_totals['allotment']; ?>, <?php echo $oo3_2_totals['allotment']; ?>, <?php echo $oo4_1_1_totals['allotment']; ?>, <?php echo $oo4_1_2_totals['allotment']; ?>]
                }, {
                    name: 'Balance',
                    data: [<?php echo $gas_totals['balances']; ?>, <?php echo $oo1_totals['balances']; ?>, <?php echo $oo2_totals['balances']; ?>, <?php echo $oo3_totals['balances']; ?>, <?php echo $oo3_1_totals['balances']; ?>, <?php echo $oo3_2_totals['balances']; ?>, <?php echo $oo4_1_1_totals['balances']; ?>, <?php echo $oo4_1_2_totals['balances']; ?>]
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded',
                        borderRadius: 4
                    },
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#0d6efd', '#6c757d'],
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: ['GAS', 'OO1', 'OO2', 'OO3', 'OO3.1', 'OO3.2', 'OO4.1.1', 'OO4.1.2'],
                    labels: {
                        style: {
                            fontFamily: 'Nunito, sans-serif',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Amount (₱)',
                        style: {
                            fontFamily: 'Nunito, sans-serif',
                            fontWeight: 600
                        }
                    },
                    labels: {
                        formatter: function (val) {
                            return "₱" + val.toLocaleString()
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "₱" + val.toLocaleString()
                        }
                    },
                    theme: 'light',
                    style: {
                        fontFamily: 'Nunito, sans-serif'
                    }
                },
                legend: {
                    position: 'top',
                    fontFamily: 'Nunito, sans-serif',
                    fontWeight: 600,
                    markers: {
                        width: 12,
                        height: 12,
                        radius: 12
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#monthlyTrendChart"), options);
            chart.render();
        });
    </script>
</body>

</html> 