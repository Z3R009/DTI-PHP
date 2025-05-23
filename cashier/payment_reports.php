<?php
include '../DBConnection.php';

// Initialize filter variables
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); // Default to first day of current month
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'); // Default to current date
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$payment_type = isset($_GET['payment_type']) ? $_GET['payment_type'] : 'all';

// Build the query with filters
$where_conditions = [];
$where_conditions[] = "p.payment_date BETWEEN '$from_date' AND '$to_date'";

if ($status != 'all') {
    $where_conditions[] = "p.status = '$status'";
}

if ($payment_type != 'all') {
    $where_conditions[] = "p.payment_type = '$payment_type'";
}

$where_clause = implode(' AND ', $where_conditions);

$report_query = "SELECT p.*, d.dv_no, o.ors_no, pa.payee_name
                FROM payment p
                JOIN dv d ON p.dv_id = d.dv_id
                JOIN ors o ON d.ors_id = o.ors_id
                JOIN payee pa ON o.payee_id = pa.payee_id
                WHERE $where_clause
                ORDER BY p.payment_date ASC";
$report_result = mysqli_query($connection, $report_query);

// Calculate totals
$total_query = "SELECT SUM(p.amount) as total_amount, 
                COUNT(CASE WHEN p.payment_type = 'Check' THEN 1 END) as check_count,
                COUNT(CASE WHEN p.payment_type = 'ADA' THEN 1 END) as ada_count,
                SUM(CASE WHEN p.payment_type = 'Check' THEN p.amount ELSE 0 END) as check_amount,
                SUM(CASE WHEN p.payment_type = 'ADA' THEN p.amount ELSE 0 END) as ada_amount
                FROM payment p
                WHERE $where_clause";
$total_result = mysqli_query($connection, $total_query);
$totals = mysqli_fetch_assoc($total_result);

// Get monthly data for trend chart
$monthly_query = "SELECT 
                    DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                    SUM(CASE WHEN p.payment_type = 'Check' THEN p.amount ELSE 0 END) as check_amount,
                    SUM(CASE WHEN p.payment_type = 'ADA' THEN p.amount ELSE 0 END) as ada_amount
                FROM payment p
                WHERE p.payment_date BETWEEN DATE_SUB('$from_date', INTERVAL 6 MONTH) AND '$to_date'
                GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
                ORDER BY month ASC";
$monthly_result = mysqli_query($connection, $monthly_query);

$months = [];
$check_amounts = [];
$ada_amounts = [];

while ($row = mysqli_fetch_assoc($monthly_result)) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $check_amounts[] = $row['check_amount'];
    $ada_amounts[] = $row['ada_amount'];
}

// Get status distribution data
$status_query = "SELECT 
                    p.status, 
                    COUNT(*) as count,
                    SUM(p.amount) as total_amount
                FROM payment p
                WHERE $where_clause
                GROUP BY p.status";
$status_result = mysqli_query($connection, $status_query);

$status_labels = [];
$status_data = [];
$status_colors = [
    'Completed' => '#41B883',
    'Pending' => '#E46651',
    'Returned' => '#00D8FF'
];

while ($row = mysqli_fetch_assoc($status_result)) {
    $status_labels[] = $row['status'];
    $status_data[] = $row['count'];
}

// Get top payees
$payee_query = "SELECT 
                    pa.payee_name, 
                    COUNT(*) as payment_count,
                    SUM(p.amount) as total_amount
                FROM payment p
                JOIN dv d ON p.dv_id = d.dv_id
                JOIN ors o ON d.ors_id = o.ors_id
                JOIN payee pa ON o.payee_id = pa.payee_id
                WHERE $where_clause
                GROUP BY pa.payee_name
                ORDER BY total_amount DESC
                LIMIT 5";
$payee_result = mysqli_query($connection, $payee_query);
?>

<!DOCTYPE html>
<html lang="en">
    
    <link href="../book_keeper/img/dti_logo.png" rel="icon">
<link rel="stylesheet" href="css/table.css">
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Payment Reports</h1>
       
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <!-- <h5 class="card-title">Generate Report</h5> -->
                        
                        <!-- Filter Form -->
                        <form method="GET" action="" class="mb-4 mt-5">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="from_date" id="from_date" value="<?php echo $from_date; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="to_date" id="to_date" value="<?php echo $to_date; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All</option>
                                        <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Returned" <?php echo $status == 'Returned' ? 'selected' : ''; ?>>Returned</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="payment_type" class="form-label">Payment Type</label>
                                    <select class="form-select" name="payment_type" id="payment_type">
                                        <option value="all" <?php echo $payment_type == 'all' ? 'selected' : ''; ?>>All</option>
                                        <option value="Check" <?php echo $payment_type == 'Check' ? 'selected' : ''; ?>>Check</option>
                                        <option value="ADA" <?php echo $payment_type == 'ADA' ? 'selected' : ''; ?>>ADA</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search me-1"></i> Generate
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Stats Cards Row -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Transactions</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-receipt"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?php echo mysqli_num_rows($report_result); ?></h6>
                                                <span class="text-muted small pt-2">transactions</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card info-card revenue-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Amount</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-currency-dollar"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>PHP <?php echo number_format($totals['total_amount'] ?? 0, 2); ?></h6>
                                                <span class="text-success small pt-1 fw-bold">100%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card info-card customers-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Check Payments</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-credit-card"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>PHP <?php echo number_format($totals['check_amount'] ?? 0, 2); ?></h6>
                                                <span class="text-success small pt-1 fw-bold">
                                                    <?php 
                                                        echo ($totals['total_amount'] > 0) 
                                                            ? round(($totals['check_amount'] / $totals['total_amount']) * 100, 1) . '%' 
                                                            : '0%'; 
                                                    ?>
                                                </span>
                                                <span class="text-muted small pt-2 ps-1">(<?php echo $totals['check_count'] ?? 0; ?> transactions)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="card info-card sales-card">
                                    <div class="filter">
                                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                            <li><a class="dropdown-item" href="export_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&status=<?php echo $status; ?>&payment_type=<?php echo $payment_type; ?>">Export to Excel</a></li>
                                            <li><a class="dropdown-item" href="generate_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&status=<?php echo $status; ?>&payment_type=<?php echo $payment_type; ?>" target="_blank">Print Report</a></li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">ADA Payments</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-bank"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>PHP <?php echo number_format($totals['ada_amount'] ?? 0, 2); ?></h6>
                                                <span class="text-success small pt-1 fw-bold">
                                                    <?php 
                                                        echo ($totals['total_amount'] > 0) 
                                                            ? round(($totals['ada_amount'] / $totals['total_amount']) * 100, 1) . '%' 
                                                            : '0%'; 
                                                    ?>
                                                </span>
                                                <span class="text-muted small pt-2 ps-1">(<?php echo $totals['ada_count'] ?? 0; ?> transactions)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Row -->
                        <div class="row mb-4">
                            <!-- Payment Trend Chart -->
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Payment Trends <span>| Last 6 Months</span></h5>
                                        <div id="paymentTrendsChart" style="min-height: 365px;">
                                            <canvas id="lineChart" style="max-height: 400px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Distribution Chart -->
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Status Distribution</h5>
                                        <div id="statusChart" style="min-height: 365px;">
                                            <canvas id="doughnutChart" style="max-height: 400px;"></canvas>
                                        </div>
                                        
                                        <?php if (empty($status_labels)): ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-exclamation-circle fs-1"></i>
                                            <p class="mt-3">No data available for the selected filters</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Payees -->
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Top Payees</h5>
                                        
                                        <?php if (mysqli_num_rows($payee_result) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Payee</th>
                                                        <th>Transactions</th>
                                                        <th>Total Amount</th>
                                                        <th>Percentage</th>
                                                        <th>Visualization</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($row = mysqli_fetch_assoc($payee_result)): 
                                                        $percentage = ($totals['total_amount'] > 0) 
                                                            ? ($row['total_amount'] / $totals['total_amount']) * 100 
                                                            : 0;
                                                    ?>
                                                    <tr>
                                                        <td><strong><?php echo $row['payee_name']; ?></strong></td>
                                                        <td><?php echo $row['payment_count']; ?></td>
                                                        <td>PHP <?php echo number_format($row['total_amount'], 2); ?></td>
                                                        <td><?php echo number_format($percentage, 1); ?>%</td>
                                                        <td width="30%">
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%" 
                                                                    aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-exclamation-circle fs-1"></i>
                                            <p class="mt-3">No payee data available for the selected filters</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Report Table -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Detailed Transactions</h5>
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="generate_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&status=<?php echo $status; ?>&payment_type=<?php echo $payment_type; ?>" class="btn btn-success btn-sm me-2" target="_blank">
                                        <i class="bi bi-printer"></i> Print Report
                                    </a>
                                    <a href="export_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&status=<?php echo $status; ?>&payment_type=<?php echo $payment_type; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-file-excel"></i> Export to Excel
                                    </a>
                                </div>
                                
                        <div class="table-responsive">
                                    <table class="datatable">
                                <thead>
                                            <tr class="bg-light">
                                        <th>Date</th>
                                        <th>DV No</th>
                                        <th>ORS No</th>
                                        <th>Payee</th>
                                        <th>Payment Type</th>
                                        <th>Reference No</th>
                                        <th>Amount</th>
                                        <!-- <th>Status</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                            <?php 
                                            mysqli_data_seek($report_result, 0);
                                            while ($row = mysqli_fetch_assoc($report_result)) : 
                                            ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                                <td><span class="badge bg-light text-dark"><?php echo $row['dv_no']; ?></span></td>
                                        <td><?php echo $row['ors_no']; ?></td>
                                        <td><?php echo $row['payee_name']; ?></td>
                                                <td>
                                                    <?php if ($row['payment_type'] == 'Check'): ?>
                                                        <span class="badge bg-info text-dark">
                                                            <i class="bi bi-credit-card me-1"></i> Check
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary">
                                                            <i class="bi bi-bank me-1"></i> ADA
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                        <td><?php echo $row['reference_no']; ?></td>
                                                <td class="fw-bold">PHP <?php echo number_format($row['amount'], 2); ?></td>
                                        <!-- <td>
                                            <?php if ($row['status'] == 'Completed') : ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php elseif ($row['status'] == 'Pending') : ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Returned</span>
                                            <?php endif; ?>
                                        </td> -->
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($report_result) == 0) : ?>
                                    <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="bi bi-search fs-1 text-muted"></i>
                                                    <p class="mt-3">No records found for the selected filters</p>
                                                </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<?php include 'includes/footer.php'; ?> 

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Line Chart - Payment Trends
    if (document.getElementById('lineChart')) {
        const months = <?php echo json_encode($months); ?>;
        const checkAmounts = <?php echo json_encode($check_amounts); ?>;
        const adaAmounts = <?php echo json_encode($ada_amounts); ?>;
        
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Check Payments',
                        data: checkAmounts,
                        fill: false,
                        borderColor: 'rgba(65, 105, 225, 1)',
                        tension: 0.1
                    },
                    {
                        label: 'ADA Payments',
                        data: adaAmounts,
                        fill: false,
                        borderColor: 'rgba(40, 167, 69, 1)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (PHP)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'PHP ' + new Intl.NumberFormat().format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': PHP ' + new Intl.NumberFormat().format(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
    }

    // Doughnut Chart - Status Distribution
    if (document.getElementById('doughnutChart')) {
        const statusLabels = <?php echo json_encode($status_labels); ?>;
        const statusData = <?php echo json_encode($status_data); ?>;
        
        if (statusLabels.length > 0) {
            const backgroundColors = statusLabels.map(status => {
                switch(status) {
                    case 'Completed': return '#41B883';
                    case 'Pending': return '#FFA500';
                    case 'Returned': return '#6C757D';
                    default: return '#36A2EB';
                }
            });
            
            new Chart(document.getElementById('doughnutChart'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: backgroundColors,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
});
</script> 