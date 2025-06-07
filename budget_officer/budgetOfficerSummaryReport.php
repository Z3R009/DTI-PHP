<?php
include '../DBConnection.php';


// Get filter parameters
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// Function to get obligations for a specific oopap and time period
function getObligations($connection, $oopap_id, $year, $start_month, $end_month)
{
    $query = "SELECT COALESCE(SUM(ors.total_amount), 0) as total
              FROM obligation_history oh
              JOIN ors ON oh.ors_id = ors.ors_id
              JOIN project ON oh.project_id = project.project_id
              WHERE project.oopap_id = ? 
              AND YEAR(ors.date) = ?
              AND MONTH(ors.date) BETWEEN ? AND ?";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("iiii", $oopap_id, $year, $start_month, $end_month);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Function to get total allotment for a specific oopap
function getAllotment($connection, $oopap_id, $year)
{
    $query = "SELECT COALESCE(SUM(allotment), 0) as total_allotment
              FROM project
              WHERE oopap_id = ?
              AND YEAR(created_at) = ?";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $oopap_id, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_allotment'];
}

// Get all OOPAP categories
$oopap_query = "SELECT * FROM oopap ORDER BY oopap_id";
$oopap_result = $connection->query($oopap_query);
$oopap_categories = [];
while ($row = $oopap_result->fetch_assoc()) {
    $oopap_categories[] = $row;
}

// Prepare data for the summary table
$summary_data = [];
$total_allotment = 0;
$total_last_month = 0;
$total_this_month = 0;
$total_to_date = 0;
$total_balances = 0;
$total_percent_utilized = 0;

foreach ($oopap_categories as $oopap) {
    $oopap_id = $oopap['oopap_id'];

    // Get allotment
    $allotment = getAllotment($connection, $oopap_id, $selected_year);

    // Get obligations
    $last_month_obligations = getObligations($connection, $oopap_id, $selected_year, 1, $selected_month - 1);
    $this_month_obligations = getObligations($connection, $oopap_id, $selected_year, $selected_month, $selected_month);
    $to_date_obligations = $last_month_obligations + $this_month_obligations;

    // Calculate balance and utilization percentage
    $balance = $allotment - $to_date_obligations;
    $percent_utilized = ($allotment > 0) ? ($to_date_obligations / $allotment) * 100 : 0;

    // Add to summary data
    $summary_data[] = [
        'oopap_id' => $oopap_id,
        'oopap_name' => $oopap['oopap_name'],
        'description' => $oopap['description'],
        'allotment' => $allotment,
        'last_month' => $last_month_obligations,
        'this_month' => $this_month_obligations,
        'to_date' => $to_date_obligations,
        'balance' => $balance,
        'percent_utilized' => $percent_utilized
    ];

    // Update totals
    $total_allotment += $allotment;
    $total_last_month += $last_month_obligations;
    $total_this_month += $this_month_obligations;
    $total_to_date += $to_date_obligations;
    $total_balances += $balance;
}

// Calculate overall utilization percentage
$total_percent_utilized = ($total_allotment > 0) ? ($total_to_date / $total_allotment) * 100 : 0;

// Get recent transactions
$recent_transactions_query = "SELECT 
    ors.ors_id,
    ors.ors_no,
    ors.date,
    ors.total_amount,
    ors.status,
    oopap.oopap_name,
    payee.payee_name,
    account_title.account_title
    FROM ors
    JOIN oopap ON ors.oopap_id = oopap.oopap_id
    JOIN payee ON ors.payee_id = payee.payee_id
    JOIN account_title ON ors.account_id = account_title.account_id
    ORDER BY ors.date DESC
    LIMIT 10";
$recent_transactions = $connection->query($recent_transactions_query);

// Get top expense categories
$top_expenses_query = "SELECT 
    account_title.account_title,
    account_title.account_code,
    SUM(ors.total_amount) as total_amount
    FROM ors
    JOIN account_title ON ors.account_id = account_title.account_id
    WHERE YEAR(ors.date) = ?
    GROUP BY ors.account_id
    ORDER BY total_amount DESC
    LIMIT 5";
$stmt = $connection->prepare($top_expenses_query);
$stmt->bind_param("i", $selected_year);
$stmt->execute();
$top_expenses = $stmt->get_result();

// Get month name
$months = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
];

// Get monthly expenditure data for charts
$monthly_data_query = "SELECT 
    MONTH(ors.date) as month,
    SUM(ors.total_amount) as amount
    FROM ors
    WHERE YEAR(ors.date) = ?
    GROUP BY MONTH(ors.date)
    ORDER BY MONTH(ors.date)";
$stmt = $connection->prepare($monthly_data_query);
$stmt->bind_param("i", $selected_year);
$stmt->execute();
$monthly_result = $stmt->get_result();

$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['month']] = $row['amount'];
}

// Get data for OOPAP distribution pie chart
$oopap_distribution_query = "SELECT 
    oopap.oopap_name,
    SUM(oh.net) as total_amount
    FROM obligation_history oh
    JOIN ors ON oh.ors_id = ors.ors_id
    JOIN project ON oh.project_id = project.project_id
    JOIN oopap ON project.oopap_id = oopap.oopap_id
    WHERE YEAR(ors.date) = ?
    GROUP BY oopap.oopap_id
    ORDER BY total_amount DESC";
$stmt = $connection->prepare($oopap_distribution_query);
$stmt->bind_param("i", $selected_year);
$stmt->execute();
$oopap_distribution = $stmt->get_result();

// Convert to JSON for charts
$chart_data = [
    'monthly' => $monthly_data,
    'oopap' => []
];

while ($row = $oopap_distribution->fetch_assoc()) {
    $chart_data['oopap'][] = [
        'name' => $row['oopap_name'],
        'value' => floatval($row['total_amount'])
    ];
}

$chart_json = json_encode($chart_data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Budget Officer Summary Report</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="img/dti_logo.png" rel="icon">
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

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        .report-header {
            background: #f6f9ff;
            color: #012970;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 5px solid #012970;
        }

        .report-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #012970;
        }

        .report-header p {
            margin: 5px 0 0;
            color: #899bbd;
        }

        .filter-card {
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(1, 41, 112, 0.1);
            margin-bottom: 20px;
        }

        .filter-card .card-body {
            padding: 15px 20px;
        }

        .filter-card .card-title {
            color: #012970;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .form-control,
        .form-select {
            border-radius: 4px;
            padding: 8px 12px;
            border-color: #ced4da;
            color: #212529;
        }

        .btn-primary {
            border-radius: 4px;
            padding: 8px 15px;
            background-color: #4154f1;
            border: none;
            font-weight: 500;
        }

        .btn-outline-primary {
            border-radius: 4px;
            padding: 8px 15px;
            color: #4154f1;
            border-color: #4154f1;
            font-weight: 500;
        }

        .stats-card {
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(1, 41, 112, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            border: none;
        }

        .stats-card .card-body {
            padding: 20px;
        }

        .stats-card .card-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .stats-card-primary {
            background: white;
            color: #012970;
            border-left: 5px solid #4154f1;
        }

        .stats-card-success {
            background: white;
            color: #012970;
            border-left: 5px solid #2eca6a;
        }

        .stats-card-warning {
            background: white;
            color: #012970;
            border-left: 5px solid #ff771d;
        }

        .stats-card .icon {
            float: right;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .stats-card-primary .icon {
            background: #f6f6fe;
            color: #4154f1;
        }

        .stats-card-success .icon {
            background: #e0f8e9;
            color: #2eca6a;
        }

        .stats-card-warning .icon {
            background: #ffecdf;
            color: #ff771d;
        }

        .stats-card h2 {
            font-size: 28px;
            font-weight: 700;
            color: #012970;
            margin-bottom: 5px;
        }

        .stats-card p {
            margin: 0;
            font-size: 14px;
            color: #899bbd;
        }

        .chart-card {
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(1, 41, 112, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .chart-card .card-body {
            padding: 20px;
        }

        .chart-card .card-title {
            color: #012970;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ebeef4;
        }

        .data-table {
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(1, 41, 112, 0.1);
            overflow: hidden;
        }

        .data-table .card-title {
            color: #012970;
            font-size: 16px;
            font-weight: 600;
            padding: 15px 20px;
            border-bottom: 1px solid #ebeef4;
            margin: 0;
        }

        .data-table .card-body {
            padding: 0;
        }

        .data-table .table {
            margin-bottom: 0;
        }

        .data-table .table th {
            background-color: #f6f9ff;
            font-weight: 600;
            border-top: none;
            color: #444444;
            white-space: nowrap;
            vertical-align: middle;
        }

        .data-table .table-header-blue {
            background-color: #B8CCE4 !important;
        }

        .data-table .table-header-orange {
            background-color: #FCD5B4 !important;
        }

        .data-table .table-header-red {
            background-color: #E6B8B7 !important;
        }

        .pagination-container {
            padding: 15px 20px;
            border-top: 1px solid #ebeef4;
            display: flex;
            justify-content: space-between;
            color: #899bbd;
            font-size: 14px;
        }

        .info-row {
            padding: 15px 20px 5px;
            background-color: #f6f9ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #899bbd;
            font-size: 14px;
        }

        /* Badge Styling */
        .badge-pill {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background-color: #e0f8e9;
            color: #2eca6a;
        }

        .badge-warning {
            background-color: #ffecdf;
            color: #ff771d;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #dc3545;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body * {
                visibility: hidden;
            }

            .main,
            .main * {
                visibility: visible;
            }

            .main {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .card {
                break-inside: avoid;
                border: 1px solid #dee2e6;
            }

            .btn,
            form button,
            .datatable-top,
            .datatable-bottom {
                display: none !important;
            }

            .sidebar,
            header,
            .back-to-top,
            .floating-nav {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Budget Summary Report</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item">Reports</li>
                    <li class="breadcrumb-item active">Budget Summary</li>
                </ol>
            </nav>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <!-- <h1>Comprehensive Budget Summary Report</h1> -->
            <p>Financial overview for <?php echo $months[$selected_month] . ' ' . $selected_year; ?></p>
        </div>

        <section class="section dashboard">
            <!-- Filter Card -->
            <div class="card filter-card">
                <div class="card-body">
                    <h5 class="card-title">Report Options</h5>
                    <form method="get" action="budgetOfficerSummaryReport.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="year" class="form-label">Fiscal Year</label>
                            <select class="form-select" id="year" name="year" required>
                                <?php
                                $current_year = date('Y');
                                for ($year = $current_year; $year >= $current_year - 5; $year--) {
                                    $selected = ($year == $selected_year) ? 'selected' : '';
                                    echo "<option value=\"$year\" $selected>$year</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="month" class="form-label">Report Month</label>
                            <select class="form-select" id="month" name="month" required>
                                <?php
                                foreach ($months as $num => $name) {
                                    $selected = ($num == $selected_month) ? 'selected' : '';
                                    echo "<option value=\"$num\" $selected>$name</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter"></i> Generate Report
                            </button>
                            <button type="button" onclick="printReport()" class="btn btn-outline-primary">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card stats-card stats-card-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Budget Allocation</h5>
                            <div class="icon">
                                <i class="bi bi-currency-exchange"></i>
                            </div>
                            <h2>₱<?php echo number_format($total_allotment, 2); ?></h2>
                            <p>Fiscal Year <?php echo $selected_year; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stats-card stats-card-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Obligations To Date</h5>
                            <div class="icon">
                                <i class="bi bi-credit-card"></i>
                            </div>
                            <h2>₱<?php echo number_format($total_to_date, 2); ?></h2>
                            <p>As of <?php echo $months[$selected_month] . ' ' . $selected_year; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card stats-card stats-card-warning">
                        <div class="card-body">
                            <h5 class="card-title">Budget Utilization Rate</h5>
                            <div class="icon">
                                <i class="bi bi-percent"></i>
                            </div>
                            <h2><?php echo number_format($total_percent_utilized, 2); ?>%</h2>
                            <p><?php echo $total_percent_utilized < 70 ? 'Below target' : 'On target'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Summary Table -->
            <div class="card data-table mb-4">
                <h5 class="card-title">
                    <i class="bi bi-table me-2"></i>
                    Budget Allocation & Utilization Summary
                </h5>
                <div class="info-row">
                    <div>Showing all OOPAP categories for <?php echo $months[$selected_month] . ' ' . $selected_year; ?>
                    </div>
                    <div><strong>Total Allotment:</strong> ₱<?php echo number_format($total_allotment, 2); ?></div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="vertical-align: middle; width: 10%;">OOPAP</th>
                                    <th rowspan="2" style="vertical-align: middle; width: 20%;">DESCRIPTION</th>
                                    <th rowspan="2" style="vertical-align: middle; width: 13%;" class="text-end">
                                        ALLOTMENT</th>
                                    <th colspan="3" class="text-center">OBLIGATIONS</th>
                                    <th rowspan="2" style="vertical-align: middle; width: 13%;" class="text-end">
                                        BALANCES</th>
                                    <th rowspan="2" style="vertical-align: middle; width: 10%;" class="text-end">%
                                        UTILIZED</th>
                                </tr>
                                <tr>
                                    <th class="text-end table-header-blue" style="width: 11%;">LAST MONTH</th>
                                    <th class="text-end table-header-orange" style="width: 11%;">THIS MONTH
                                        <br><?php echo $months[$selected_month] ?></th>
                                    <th class="text-end table-header-red" style="width: 12%;">TO DATE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($summary_data as $row): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['oopap_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="text-end">₱<?php echo number_format($row['allotment'], 2); ?></td>
                                        <td class="text-end" style="background-color: #B8CCE4;">
                                            ₱<?php echo number_format($row['last_month'], 2); ?></td>
                                        <td class="text-end" style="background-color: #FCD5B4;">
                                            ₱<?php echo number_format($row['this_month'], 2); ?></td>
                                        <td class="text-end" style="background-color: #E6B8B7;">
                                            ₱<?php echo number_format($row['to_date'], 2); ?></td>
                                        <td class="text-end">₱<?php echo number_format($row['balance'], 2); ?></td>
                                        <td class="text-end">
                                            <span
                                                class="badge-pill <?php echo $row['percent_utilized'] < 50 ? 'badge-danger' : ($row['percent_utilized'] < 70 ? 'badge-warning' : 'badge-success'); ?>">
                                                <?php echo number_format($row['percent_utilized'], 2); ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr class="fw-bold">
                                    <td colspan="2" class="text-end">GRAND TOTAL</td>
                                    <td class="text-end">₱<?php echo number_format($total_allotment, 2); ?></td>
                                    <td class="text-end" style="background-color: #B8CCE4;">
                                        ₱<?php echo number_format($total_last_month, 2); ?></td>
                                    <td class="text-end" style="background-color: #FCD5B4;">
                                        ₱<?php echo number_format($total_this_month, 2); ?></td>
                                    <td class="text-end" style="background-color: #E6B8B7;">
                                        ₱<?php echo number_format($total_to_date, 2); ?></td>
                                    <td class="text-end">₱<?php echo number_format($total_balances, 2); ?></td>
                                    <td class="text-end">
                                        <span
                                            class="badge-pill <?php echo $total_percent_utilized < 50 ? 'badge-danger' : ($total_percent_utilized < 70 ? 'badge-warning' : 'badge-success'); ?>">
                                            <?php echo number_format($total_percent_utilized, 2); ?>%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="pagination-container">
                    <div>Showing <?php echo count($summary_data); ?> entries</div>
                    <div>Report generated: <?php echo date('F d, Y h:i A'); ?></div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card chart-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-bar-chart-line me-2"></i>
                                Monthly Expenditure (<?php echo $selected_year; ?>)
                            </h5>
                            <div id="monthlyExpenditureChart" style="min-height: 365px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card chart-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-pie-chart-fill me-2"></i>
                                Budget Distribution by Program
                            </h5>
                            <div id="budgetDistributionChart" style="min-height: 365px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Row -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card data-table">
                        <h5 class="card-title">
                            <i class="bi bi-cash-stack me-2"></i>
                            Top Expense Categories
                        </h5>
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Account Code</th>
                                        <th style="width: 60%;">Account Title</th>
                                        <th style="width: 20%;" class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $top_expenses->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['account_code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                            <td class="text-end">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card data-table">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2"></i>
                            Recent Transactions
                        </h5>
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Date</th>
                                        <th style="width: 25%;">ORS No.</th>
                                        <th style="width: 35%;">Program</th>
                                        <th style="width: 20%;" class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $recent_transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['ors_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['oopap_name']); ?></td>
                                            <td class="text-end">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Chart data from PHP
        const chartData = <?php echo $chart_json; ?>;

        // Initialize monthly expenditure chart
        document.addEventListener('DOMContentLoaded', function () {
            // Monthly Expenditure Chart
            const monthlyData = [];
            const monthNames = [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];

            // Fill in data for all months, using 0 for months with no data
            for (let i = 1; i <= 12; i++) {
                monthlyData.push(chartData.monthly[i] || 0);
            }

            const monthlyOptions = {
                series: [{
                    name: 'Expenditure',
                    data: monthlyData
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Nunito, sans-serif',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                        borderRadius: 2,
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
                    categories: monthNames,
                },
                yaxis: {
                    title: {
                        text: '₱ (Philippine Peso)'
                    },
                    labels: {
                        formatter: function (value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                fill: {
                    opacity: 0.85,
                    colors: ['#4154f1']
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return '₱' + val.toLocaleString()
                        }
                    }
                },
                colors: ['#4154f1']
            };

            const monthlyChart = new ApexCharts(document.querySelector("#monthlyExpenditureChart"), monthlyOptions);
            monthlyChart.render();

            // Budget Distribution Chart (Pie)
            const programNames = chartData.oopap.map(item => item.name);
            const programValues = chartData.oopap.map(item => item.value);

            const distributionOptions = {
                series: programValues,
                chart: {
                    type: 'pie',
                    height: 350,
                    fontFamily: 'Nunito, sans-serif'
                },
                labels: programNames,
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
                plotOptions: {
                    pie: {
                        donut: {
                            size: '0%'
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return '₱' + val.toLocaleString()
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                colors: ['#4154f1', '#2eca6a', '#ff771d', '#ffbb55', '#6f42c1', '#dc3545', '#20c997', '#fd7e14', '#6c757d']
            };

            const distributionChart = new ApexCharts(document.querySelector("#budgetDistributionChart"), distributionOptions);
            distributionChart.render();
        });

        // Print functionality
        function printReport() {
            window.print();
        }
    </script>
</body>

</html>