<?php
include "../DBConnection.php";

// Function to get summary statistics
function getSummaryStats($connection, $startDate = null, $endDate = null) {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $dateFilter = "";
    if ($startDate && $endDate) {
        $dateFilter = " WHERE date BETWEEN '$startDate' AND '$endDate'";
    }

    // Get ORS stats with error handling
    $orsQuery = "SELECT 
        COUNT(*) as total_ors,
        COALESCE(SUM(total_amount), 0) as total_amount,
        COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_count
        FROM `ors`" . $dateFilter;
    $orsResult = $connection->query($orsQuery);
    if (!$orsResult) {
        die("Error in ORS query: " . $connection->error);
    }
    $orsStats = $orsResult->fetch_assoc();

    // Get DV stats with error handling
    $dvQuery = "SELECT 
        COUNT(*) as total_dv,
        COALESCE(SUM(total_amount), 0) as total_amount
        FROM `dv`" . $dateFilter;
    $dvResult = $connection->query($dvQuery);
    if (!$dvResult) {
        die("Error in DV query: " . $connection->error);
    }
    $dvStats = $dvResult->fetch_assoc();

    // Calculate budget utilization with error handling
    // Get total budget from project table instead of fund_cluster
    $budgetQuery = "SELECT 
        COALESCE(SUM(allotment), 0) as total_budget 
        FROM `project`";
    $budgetResult = $connection->query($budgetQuery);
    if (!$budgetResult) {
        die("Error in budget query: " . $connection->error);
    }
    $budgetData = $budgetResult->fetch_assoc();
    
    $totalBudget = $budgetData['total_budget'] ?? 0;
    $totalSpent = ($orsStats['total_amount'] ?? 0) + ($dvStats['total_amount'] ?? 0);
    $utilization = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;

    return [
        'ors' => $orsStats,
        'dv' => $dvStats,
        'utilization' => round($utilization, 2)
    ];
}

// Function to get monthly trends
function getMonthlyTrends($connection, $year) {
    $monthlyData = [];
    
    // Get ORS monthly totals
    $orsQuery = "SELECT 
        MONTH(date) as month,
        SUM(total_amount) as amount
        FROM ors
        WHERE YEAR(date) = ?
        GROUP BY MONTH(date)
        ORDER BY MONTH(date)";
    
    $stmt = $connection->prepare($orsQuery);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $monthlyData['ors'][$row['month']] = $row['amount'];
    }
    
    // Get DV monthly totals
    $dvQuery = "SELECT 
        MONTH(date) as month,
        SUM(total_amount) as amount
        FROM dv
        WHERE YEAR(date) = ?
        GROUP BY MONTH(date)
        ORDER BY MONTH(date)";
    
    $stmt = $connection->prepare($dvQuery);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $monthlyData['dv'][$row['month']] = $row['amount'];
    }
    
    return $monthlyData;
}

// Function to get fund cluster distribution
function getFundClusterDistribution($connection) {
    $query = "SELECT 
        fc.fund_cluster_name,
        COALESCE(SUM(p.allotment), 0) as budget,
        COALESCE(SUM(o.total_amount), 0) as actual_amount
        FROM fund_cluster fc
        LEFT JOIN ors o ON fc.fund_cluster_id = o.fund_cluster_id
        LEFT JOIN project p ON o.oopap_id = p.oopap_id
        GROUP BY fc.fund_cluster_id, fc.fund_cluster_name";
    
    $result = $connection->query($query);
    if (!$result) {
        die("Error in fund cluster distribution query: " . $connection->error);
    }
    
    $distribution = [];
    while ($row = $result->fetch_assoc()) {
        $distribution[] = $row;
    }
    
    return $distribution;
}

// Function to get top payees
function getTopPayees($connection, $limit = 5) {
    $query = "SELECT 
        p.payee_name,
        SUM(o.total_amount) as total_amount,
        COUNT(o.ors_id) as transaction_count
        FROM payee p
        JOIN ors o ON p.payee_id = o.payee_id
        GROUP BY p.payee_id
        ORDER BY total_amount DESC
        LIMIT ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payees = [];
    while ($row = $result->fetch_assoc()) {
        $payees[] = $row;
    }
    
    return $payees;
}

// Function to get recent transactions
function getRecentTransactions($connection, $limit = 5) {
    $query = "SELECT 
        'ORS' as type,
        date,
        ors_no as reference_no,
        total_amount,
        status
        FROM ors
        UNION ALL
        SELECT 
        'DV' as type,
        date,
        dv_no as reference_no,
        total_amount,
        status
        FROM dv
        ORDER BY date DESC
        LIMIT ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

// Get data for the page
$currentYear = date('Y');
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

$summaryStats = getSummaryStats($connection, $startDate, $endDate);
$monthlyTrends = getMonthlyTrends($connection, $currentYear);
$fundDistribution = getFundClusterDistribution($connection);
$topPayees = getTopPayees($connection);
$recentTransactions = getRecentTransactions($connection);

// Convert data to JSON for JavaScript
$jsData = [
    'monthlyTrends' => $monthlyTrends,
    'fundDistribution' => $fundDistribution
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Reports - Book Keeper</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
</head>
    
<body>
    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <!-- Floating Quick Navigation -->
    <div class="floating-nav">
        <button class="btn btn-primary rounded-circle" type="button" data-bs-toggle="offcanvas" data-bs-target="#quickNav" aria-controls="quickNav">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="quickNav" aria-labelledby="quickNavLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="quickNavLabel">Quick Navigation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="quick-nav-items">
                <a href="#summary-cards" class="quick-nav-item">
                    <i class="bi bi-card-text"></i> Summary Cards
                </a>
                <a href="#monthly-trends" class="quick-nav-item">
                    <i class="bi bi-graph-up"></i> Monthly Trends
                </a>
                <a href="#fund-distribution" class="quick-nav-item">
                    <i class="bi bi-pie-chart"></i> Fund Distribution
                </a>
                <a href="#top-payees" class="quick-nav-item">
                    <i class="bi bi-people"></i> Top Payees
                </a>
                <a href="#recent-transactions" class="quick-nav-item">
                    <i class="bi bi-clock-history"></i> Recent Transactions
                </a>
                <a href="#budget-analysis" class="quick-nav-item">
                    <i class="bi bi-bar-chart"></i> Budget Analysis
                </a>
            </div>
        </div>
    </div>

    <main id="main" class="main">
        <div class="pagetitle">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>Reports</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="page-actions">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" id="refreshData">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Data
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="exportData">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="printReport">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Date Range Filter -->
            <div class="date-filter mt-3">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="col-form-label">Date Range:</label>
                    </div>
                    <div class="col-auto">
                        <select class="form-select" id="dateRangePreset">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="last7days">Last 7 Days</option>
                            <option value="last30days">Last 30 Days</option>
                            <option value="thisMonth" selected>This Month</option>
                            <option value="lastMonth">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="col-auto date-inputs" style="display: none;">
                        <div class="input-group">
                            <input type="date" class="form-control" id="startDate">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" id="applyDateFilter">
                            <i class="bi bi-check2"></i> Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add loading overlay -->
        <div id="loadingOverlay" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading data...</p>
        </div>

       
            <!-- Analytical Dashboard Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Financial Analytics Dashboard</h5>
                            <p>Quick overview of key financial metrics and trends</p>
                            
                            <!-- Summary Cards -->
                            <div class="row">
                                <div class="col-md-3 mb-4">
                                    <div class="card info-card sales-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Total ORS Amount</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-cash-stack"></i>
                                                </div>
                                                <div class="ps-3">
                                                    <h6>₱ <?php echo number_format($summaryStats['ors']['total_amount'] ?? 0, 2); ?></h6>
                                                    <span class="text-success small pt-1 fw-bold">Total: <?php echo number_format($summaryStats['ors']['total_ors'] ?? 0); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-4">
                                    <div class="card info-card revenue-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Total DV Amount</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-currency-exchange"></i>
                                                </div>
                                                <div class="ps-3">
                                                    <h6>₱ <?php echo number_format($summaryStats['dv']['total_amount'] ?? 0, 2); ?></h6>
                                                    <span class="text-success small pt-1 fw-bold">Total: <?php echo number_format($summaryStats['dv']['total_dv'] ?? 0); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-4">
                                    <div class="card info-card customers-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Pending Approvals</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-hourglass-split"></i>
                                                </div>
                                                <div class="ps-3">
                                                    <h6><?php echo $summaryStats['ors']['pending_count'] ?? 0; ?></h6>
                                                    <span class="text-warning small pt-1 fw-bold">Pending ORS</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-4">
                                    <div class="card info-card sales-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Budget Utilization</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="bi bi-pie-chart"></i>
                                                </div>
                                                <div class="ps-3">
                                                    <h6><?php echo number_format($summaryStats['utilization'], 1); ?>%</h6>
                                                    <span class="text-info small pt-1 fw-bold">Of Total Budget</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Charts Row -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Monthly Financial Trends</h5>
                                            <div id="monthlyTrendChart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Fund Cluster Distribution</h5>
                                            <div id="fundClusterChart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tables Row -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Top 5 Payees</h5>
                                            <table class="table table-striped table-hover datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Payee Name</th>
                                                        <th>Total Amount</th>
                                                        <th>Transaction Count</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($topPayees as $payee): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($payee['payee_name']); ?></td>
                                                        <td>₱ <?php echo number_format($payee['total_amount'], 2); ?></td>
                                                        <td><?php echo $payee['transaction_count']; ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Recent Transactions</h5>
                                            <table class="table table-striped table-hover datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Reference No.</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recentTransactions as $transaction): ?>
                                                    <tr>
                                                        <td><?php echo date('Y-m-d', strtotime($transaction['date'])); ?></td>
                                                        <td><?php echo $transaction['type']; ?></td>
                                                        <td><?php echo htmlspecialchars($transaction['reference_no']); ?></td>
                                                        <td>₱ <?php echo number_format($transaction['total_amount'], 2); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $transaction['status'] === 'Approved' ? 'success' : 
                                                                    ($transaction['status'] === 'Pending' ? 'warning' : 'danger'); 
                                                            ?>">
                                                                <?php echo $transaction['status']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Budget Analysis Section -->
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Budget Analysis</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div id="budgetVsActualChart" style="height: 300px;"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Fund Cluster</th>
                                                                    <th>Budget</th>
                                                                    <th>Actual</th>
                                                                    <th>Remaining</th>
                                                                    <th>Utilization</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($fundDistribution as $fund): ?>
                                                                <?php 
                                                                    $remaining = $fund['budget'] - $fund['actual_amount'];
                                                                    $utilization = $fund['budget'] > 0 ? ($fund['actual_amount'] / $fund['budget']) * 100 : 0;
                                                                    $colorClass = $utilization >= 90 ? 'bg-danger' : 
                                                                                ($utilization >= 70 ? 'bg-warning' : 'bg-success');
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($fund['fund_cluster_name']); ?></td>
                                                                    <td>₱ <?php echo number_format($fund['budget'], 2); ?></td>
                                                                    <td>₱ <?php echo number_format($fund['actual_amount'], 2); ?></td>
                                                                    <td>₱ <?php echo number_format($remaining, 2); ?></td>
                                                                    <td>
                                                                        <div class="progress">
                                                                            <div class="progress-bar <?php echo $colorClass; ?>" 
                                                                                role="progressbar" 
                                                                                style="width: <?php echo $utilization; ?>%;" 
                                                                                aria-valuenow="<?php echo $utilization; ?>" 
                                                                                aria-valuemin="0" 
                                                                                aria-valuemax="100">
                                                                                <?php echo number_format($utilization, 1); ?>%
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- ORS Summary Modal -->
        <div class="modal fade" id="orsSummaryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ORS Summary Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="orsSummaryForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="orsSummaryStartDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="orsSummaryStartDate" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="orsSummaryEndDate" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="orsSummaryEndDate" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="orsSummaryFundCluster" class="form-label">Fund Cluster</label>
                                    <select class="form-select" id="orsSummaryFundCluster">
                                        <option value="">All Fund Clusters</option>
                                        <!-- PHP will populate this with fund clusters from the database -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="orsSummaryService" class="form-label">Service</label>
                                    <select class="form-select" id="orsSummaryService">
                                        <option value="">All Services</option>
                                        <!-- PHP will populate this with services from the database -->
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="orsSummaryStatus" class="form-label">Status</label>
                                    <select class="form-select" id="orsSummaryStatus">
                                        <option value="">All Statuses</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="generateOrsSummaryBtn">Generate Report</button>
                    </div>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Financial Reports</h5>
                            <p>Generate and view various financial reports for the Department of Trade & Industry.</p>
                            
                            <div class="row mt-4">
                                <!-- ORS Reports -->
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">ORS Reports</h5>
                                            <div class="list-group">
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#orsSummaryModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>ORS Summary Report
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#orsByPayeeModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>ORS by Payee
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#orsByFundClusterModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>ORS by Fund Cluster
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#orsByServiceModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>ORS by Service
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- DV Reports -->
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">DV Reports</h5>
                                            <div class="list-group">
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#dvSummaryModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>DV Summary Report
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#dvByPayeeModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>DV by Payee
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#dvByFundClusterModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>DV by Fund Cluster
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#dvByServiceModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>DV by Service
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- JEV Reports -->
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">JEV Reports</h5>
                                            <div class="list-group">
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#jevSummaryModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>JEV Summary Report
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#jevByAccountModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>JEV by Account Title
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#jevByFundClusterModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>JEV by Fund Cluster
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#jevByServiceModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>JEV by Service
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <!-- Consolidated Reports -->
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Consolidated Reports</h5>
                                            <div class="list-group">
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#monthlySummaryModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Monthly Summary
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#quarterlySummaryModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Quarterly Summary
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#annualSummaryModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Annual Summary
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Budget Reports -->
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Budget Reports</h5>
                                            <div class="list-group">
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#budgetUtilizationModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Budget Utilization
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#budgetVsActualModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Budget vs. Actual
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#remainingBudgetModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Remaining Budget
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Custom Reports -->
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Custom Reports</h5>
                                            <div class="list-group">
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#customReportModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Create Custom Report
                                                </a>
                                                <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#savedReportsModal">
                                                    <i class="bi bi-file-earmark-text me-2"></i>Saved Reports
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        
        <!-- Other modals would be similar in structure -->
        
    </main>


    
    <script>
        // Add PHP data to JavaScript
        const phpData = <?php echo json_encode($jsData); ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date inputs with current values from PHP
            document.querySelectorAll('input[type="date"]').forEach(input => {
                if (input.id.includes('StartDate')) {
                    input.value = '<?php echo $startDate; ?>';
                } else if (input.id.includes('EndDate')) {
                    input.value = '<?php echo $endDate; ?>';
                }
            });
            
            // Initialize charts with PHP data
            initCharts();
            
            // Initialize datatables
            initDataTables();
            
            // Initialize other features
            initQuickNav();
            initDateFilter();
            initActionButtons();
            initLoadingState();
            initCardAnimations();
            
            // Add AJAX functionality for data refresh
            setupAjaxRefresh();
        });
        
        // Setup AJAX refresh
        function setupAjaxRefresh() {
            document.getElementById('applyDateFilter').addEventListener('click', function() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                refreshData(startDate, endDate);
            });
        }
        
        // Refresh data via AJAX
        function refreshData(startDate, endDate) {
            showLoading();
            
            const params = new URLSearchParams({
                start_date: startDate,
                end_date: endDate
            });
            
            fetch(`reports.php?${params.toString()}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update summary cards
                    updateSummaryCards(doc);
                    
                    // Update tables
                    updateTables(doc);
                    
                    // Update charts
                    updateCharts(doc);
                    
                    hideLoading();
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                    hideLoading();
                    alert('Error refreshing data. Please try again.');
                });
        }
        
        // Update summary cards with new data
        function updateSummaryCards(doc) {
            const cards = doc.querySelectorAll('.info-card');
            cards.forEach(card => {
                const currentCard = document.querySelector(`#${card.id}`);
                if (currentCard) {
                    currentCard.innerHTML = card.innerHTML;
                }
            });
        }
        
        // Update tables with new data
        function updateTables(doc) {
            const tables = document.querySelectorAll('.datatable');
            tables.forEach(table => {
                const dt = simpleDatatables.DataTable.getInstance(table);
                if (dt) {
                    dt.destroy();
                }
            });
            
            const newTables = doc.querySelectorAll('.datatable');
            newTables.forEach((newTable, index) => {
                const currentTable = document.querySelectorAll('.datatable')[index];
                currentTable.innerHTML = newTable.innerHTML;
            });
            
            initDataTables();
        }
        
        // Initialize charts with PHP data
        function initCharts() {
            // Monthly Trend Chart
            const monthlyData = phpData.monthlyTrends;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            const orsData = months.map((_, index) => 
                monthlyData.ors[index + 1] ? monthlyData.ors[index + 1] / 1000 : 0
            );
            
            const dvData = months.map((_, index) => 
                monthlyData.dv[index + 1] ? monthlyData.dv[index + 1] / 1000 : 0
            );
            
            const monthlyTrendOptions = {
                series: [{
                    name: 'ORS',
                    data: orsData
                }, {
                    name: 'DV',
                    data: dvData
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    categories: months
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "₱ " + val.toFixed(2) + "K";
                        }
                    }
                }
            };
            
            const monthlyTrendChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyTrendOptions);
            monthlyTrendChart.render();
            
            // Fund Cluster Distribution Chart
            const fundData = phpData.fundDistribution;
            const fundClusterOptions = {
                series: fundData.map(fund => fund.actual_amount),
                chart: {
                    height: 300,
                    type: 'donut',
                },
                labels: fundData.map(fund => fund.fund_cluster_name),
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "₱ " + (val / 1000).toFixed(2) + "K";
                        }
                    }
                }
            };
            
            const fundClusterChart = new ApexCharts(document.querySelector("#fundClusterChart"), fundClusterOptions);
            fundClusterChart.render();
            
            // Budget vs Actual Chart
            const budgetVsActualOptions = {
                series: [{
                    name: 'Budget',
                    data: fundData.map(fund => fund.budget / 1000)
                }, {
                    name: 'Actual',
                    data: fundData.map(fund => fund.actual_amount / 1000)
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    stacked: false,
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: fundData.map(fund => fund.fund_cluster_name),
                },
                yaxis: {
                    title: {
                        text: 'Amount (₱)'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "₱ " + val.toFixed(2) + "K";
                        }
                    }
                }
            };
            
            const budgetVsActualChart = new ApexCharts(document.querySelector("#budgetVsActualChart"), budgetVsActualOptions);
            budgetVsActualChart.render();
        }
        
        // Initialize datatables
        function initDataTables() {
            const datatables = document.querySelectorAll('.datatable');
            datatables.forEach(datatable => {
                new simpleDatatables.DataTable(datatable);
            });
        }
    </script>

    <style>
        .floating-nav {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        .floating-nav .btn {
            width: 50px;
            height: 50px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .quick-nav-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .quick-nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--bs-dark);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .quick-nav-item:hover {
            background-color: var(--bs-light);
            color: var(--bs-primary);
        }

        .quick-nav-item i {
            margin-right: 0.5rem;
        }

        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .date-filter {
            background: #fff;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .page-actions .btn-group {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .date-filter .row {
                flex-direction: column;
            }
            
            .date-filter .col-auto {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .page-actions {
                margin-top: 1rem;
            }
            
            .page-actions .btn-group {
                display: flex;
                width: 100%;
            }
        }
    </style>
</body>

</html>