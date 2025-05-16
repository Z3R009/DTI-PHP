<!DOCTYPE html>
<?php
include '../DBConnection.php';

// Initialize filter variables
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$fund_cluster = isset($_GET['fund_cluster']) ? $_GET['fund_cluster'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get fund clusters for dropdown
$fund_clusters_query = "SELECT * FROM fund_cluster ORDER BY fund_cluster_name";
$fund_clusters_result = mysqli_query($connection, $fund_clusters_query);

// Build WHERE clause based on filters
$where_clause = "p.payment_date BETWEEN '$from_date' AND '$to_date'";
if ($fund_cluster != 'all') {
    $where_clause .= " AND fc.fund_cluster_id = '$fund_cluster'";
}
if ($status != 'all') {
    $where_clause .= " AND p.status = '$status'";
}

// Get transactions
$query = "SELECT p.*, d.dv_no, o.purpose, fc.fund_cluster_name, 
            CASE 
                WHEN p.payment_type = 'Check' THEN 'Check'
                WHEN p.payment_type = 'ADA' THEN 'ADA'
                ELSE p.payment_type
            END as payment_method_name,
            pa.payee_name as payee
          FROM payment p
          JOIN dv d ON p.dv_id = d.dv_id
          JOIN ors o ON d.ors_id = o.ors_id
          JOIN payee pa ON o.payee_id = pa.payee_id
          LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
          WHERE $where_clause
          ORDER BY p.payment_date DESC";
$result = mysqli_query($connection, $query);

// Get status summary
$status_summary_query = "SELECT p.status, COUNT(*) as count, SUM(p.amount) as total_amount
                         FROM payment p
                         JOIN dv d ON p.dv_id = d.dv_id
                         JOIN ors o ON d.ors_id = o.ors_id
                         LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                         WHERE $where_clause
                         GROUP BY p.status";
$status_summary_result = mysqli_query($connection, $status_summary_query);

// Get fund cluster summary
$fund_summary_query = "SELECT fc.fund_cluster_name, COUNT(*) as count, SUM(p.amount) as total_amount
                      FROM payment p
                      JOIN dv d ON p.dv_id = d.dv_id
                      JOIN ors o ON d.ors_id = o.ors_id
                      LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                      WHERE $where_clause
                      GROUP BY fc.fund_cluster_id";
$fund_summary_result = mysqli_query($connection, $fund_summary_query);

// Get payment method summary
$payment_method_query = "SELECT p.payment_type as payment_method_name, COUNT(*) as count, SUM(p.amount) as total_amount
                        FROM payment p
                        JOIN dv d ON p.dv_id = d.dv_id
                        JOIN ors o ON d.ors_id = o.ors_id
                        LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                        WHERE $where_clause
                        GROUP BY p.payment_type";
$payment_method_result = mysqli_query($connection, $payment_method_query);

// Total amount
$total_query = "SELECT SUM(p.amount) as grand_total 
                FROM payment p 
                JOIN dv d ON p.dv_id = d.dv_id
                JOIN ors o ON d.ors_id = o.ors_id
                LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                WHERE $where_clause";
$total_result = mysqli_query($connection, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$grand_total = $total_row['grand_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Chief Accountant - DTI PHP</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../book_keeper/img/dti_logo.png" rel="icon">
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

 <main id="main" class="main">
<div class="pagetitle">
    <h1>Reports</h1>

</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="from_date" name="from_date" value="<?= $from_date ?>">
            </div>
            <div class="col-md-4">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="to_date" name="to_date" value="<?= $to_date ?>">
            </div>
            <div class="col-md-4">
                <label for="fund_cluster" class="form-label">Fund Cluster</label>
                <select class="form-select" id="fund_cluster" name="fund_cluster">
                    <option value="all">All Fund Clusters</option>
                    <?php while ($fc = mysqli_fetch_assoc($fund_clusters_result)) : ?>
                        <option value="<?= $fc['fund_cluster_id'] ?>" <?= ($fund_cluster == $fc['fund_cluster_id']) ? 'selected' : '' ?>>
                            <?= $fc['fund_cluster_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" <?= ($status == 'all') ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Pending" <?= ($status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Endorsed" <?= ($status == 'Endorsed') ? 'selected' : '' ?>>Endorsed</option>
                    <option value="Processing" <?= ($status == 'Processing') ? 'selected' : '' ?>>Processing</option>
                    <option value="Completed" <?= ($status == 'Completed') ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <button type="button" class="btn btn-success me-2" onclick="window.print();">
                    <i class="bi bi-printer"></i> Print Report
                </button>
                <a href="export_report.php?from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&fund_cluster=<?= $fund_cluster ?>&status=<?= $status ?>" class="btn btn-warning">
                    <i class="bi bi-file-excel"></i> Export to Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Summary Cards -->
<div class="row">
    <div class="col-xxl-4 col-md-4">
        <div class="card info-card revenue-card">
            <div class="card-body">
                <h5 class="card-title">Total Amount</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="ps-3">
                        <h6>₱<?= number_format($grand_total, 2) ?></h6>
                        <span class="text-muted small pt-2">From <?= mysqli_num_rows($result) ?> transactions</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Get pending amount
    $pending_query = "SELECT SUM(p.amount) as pending_amount
                     FROM payment p
                     JOIN dv d ON p.dv_id = d.dv_id
                     JOIN ors o ON d.ors_id = o.ors_id
                     LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                     WHERE p.status = 'Pending' AND $where_clause";
    $pending_result = mysqli_query($connection, $pending_query);
    $pending_row = mysqli_fetch_assoc($pending_result);
    $pending_amount = $pending_row['pending_amount'] ?? 0;
    
    // Get completed amount
    $completed_query = "SELECT SUM(p.amount) as completed_amount
                     FROM payment p
                     JOIN dv d ON p.dv_id = d.dv_id
                     JOIN ors o ON d.ors_id = o.ors_id
                     LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                     WHERE p.status = 'Completed' AND $where_clause";
    $completed_result = mysqli_query($connection, $completed_query);
    $completed_row = mysqli_fetch_assoc($completed_result);
    $completed_amount = $completed_row['completed_amount'] ?? 0;
    ?>
    
    <div class="col-xxl-4 col-md-4">
        <div class="card info-card sales-card">
            <div class="card-body">
                <h5 class="card-title">Pending Amount</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="ps-3">
                        <h6>₱<?= number_format($pending_amount, 2) ?></h6>
                        <span class="text-warning small pt-2">Awaiting processing</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-4 col-md-4">
        <div class="card info-card customers-card">
            <div class="card-body">
                <h5 class="card-title">Completed Amount</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ps-3">
                        <h6>₱<?= number_format($completed_amount, 2) ?></h6>
                        <span class="text-success small pt-2">Successfully processed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Status Distribution</h5>
                
                <!-- Status Donut Chart -->
                <div id="statusDonutChart" style="min-height: 365px;"></div>
                
                <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const statusData = [];
                    const statusLabels = [];
                    
                    <?php 
                    mysqli_data_seek($status_summary_result, 0);
                    while ($row = mysqli_fetch_assoc($status_summary_result)) : 
                    ?>
                    statusData.push(<?= $row['count'] ?>);
                    statusLabels.push('<?= ucfirst($row['status']) ?>');
                    <?php endwhile; ?>
                    
                    new ApexCharts(document.querySelector("#statusDonutChart"), {
                        series: statusData,
                        chart: {
                            height: 350,
                            type: 'donut',
                            toolbar: {
                                show: true
                            }
                        },
                        labels: statusLabels,
                        colors: ['#ffc107', '#17a2b8', '#007bff', '#28a745', '#6c757d'],
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
                        }]
                    }).render();
                });
                </script>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Fund Cluster Distribution</h5>
                
                <!-- Fund Cluster Pie Chart -->
                <div id="fundClusterPieChart" style="min-height: 365px;"></div>
                
                <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const fundData = [];
                    const fundLabels = [];
                    
                    <?php 
                    mysqli_data_seek($fund_summary_result, 0);
                    while ($row = mysqli_fetch_assoc($fund_summary_result)) : 
                    ?>
                    fundData.push(<?= $row['total_amount'] ?>);
                    fundLabels.push('<?= $row['fund_cluster_name'] ?>');
                    <?php endwhile; ?>
                    
                    new ApexCharts(document.querySelector("#fundClusterPieChart"), {
                        series: fundData,
                        chart: {
                            height: 350,
                            type: 'pie',
                            toolbar: {
                                show: true
                            }
                        },
                        labels: fundLabels,
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
                        }]
                    }).render();
                });
                </script>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method and Trend Charts -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Method Comparison</h5>
                
                <!-- Payment Method Bar Chart -->
                <div id="paymentMethodBarChart" style="min-height: 365px;"></div>
                
                <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const methodData = [];
                    const methodLabels = [];
                    
                    <?php 
                    mysqli_data_seek($payment_method_result, 0);
                    while ($row = mysqli_fetch_assoc($payment_method_result)) : 
                    ?>
                    methodData.push(<?= $row['total_amount'] ?>);
                    methodLabels.push('<?= $row['payment_method_name'] ?>');
                    <?php endwhile; ?>
                    
                    new ApexCharts(document.querySelector("#paymentMethodBarChart"), {
                        series: [{
                            name: 'Amount',
                            data: methodData
                        }],
                        chart: {
                            type: 'bar',
                            height: 350,
                            toolbar: {
                                show: true
                            }
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
                            categories: methodLabels,
                        },
                        yaxis: {
                            title: {
                                text: '₱ (Amount)'
                            }
                        },
                        fill: {
                            opacity: 1
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return "₱" + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
                                }
                            }
                        }
                    }).render();
                });
                </script>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Monthly Payment Trends</h5>
                
                <?php
                // Get monthly trend data
                $monthly_trend_query = "SELECT DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                                       SUM(p.amount) as monthly_total
                                    FROM payment p
                                    JOIN dv d ON p.dv_id = d.dv_id
                                    JOIN ors o ON d.ors_id = o.ors_id
                                    LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                                    WHERE p.payment_date BETWEEN DATE_SUB('$to_date', INTERVAL 6 MONTH) AND '$to_date'
                                    GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
                                    ORDER BY month ASC";
                $monthly_trend_result = mysqli_query($connection, $monthly_trend_query);
                ?>
                
                <!-- Monthly Trend Line Chart -->
                <div id="monthlyTrendLineChart" style="min-height: 365px;"></div>
                
                <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const monthlyData = [];
                    const monthlyLabels = [];
                    
                    <?php 
                    while ($row = mysqli_fetch_assoc($monthly_trend_result)) : 
                        $month_year = date('M Y', strtotime($row['month'] . '-01'));
                    ?>
                    monthlyData.push(<?= $row['monthly_total'] ?>);
                    monthlyLabels.push('<?= $month_year ?>');
                    <?php endwhile; ?>
                    
                    new ApexCharts(document.querySelector("#monthlyTrendLineChart"), {
                        series: [{
                            name: 'Monthly Total',
                            data: monthlyData
                        }],
                        chart: {
                            height: 350,
                            type: 'line',
                            toolbar: {
                                show: true
                            }
                        },
                        markers: {
                            size: 4
                        },
                        xaxis: {
                            categories: monthlyLabels
                        },
                        yaxis: {
                            title: {
                                text: '₱ (Amount)'
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return "₱" + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
                                }
                            }
                        }
                    }).render();
                });
                </script>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Analytics Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Payment Analytics Overview</h5>
                
                <?php
                // Get transaction statistics
                $stats_query = "SELECT 
                                AVG(p.amount) as avg_amount,
                                MAX(p.amount) as max_amount,
                                MIN(p.amount) as min_amount,
                                COUNT(DISTINCT p.payment_type) as payment_methods_count,
                                COUNT(DISTINCT d.dv_id) as unique_dvs
                            FROM payment p
                            JOIN dv d ON p.dv_id = d.dv_id
                            JOIN ors o ON d.ors_id = o.ors_id
                            LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                            WHERE $where_clause";
                $stats_result = mysqli_query($connection, $stats_query);
                $stats = mysqli_fetch_assoc($stats_result);
                
                // Get top payees
                $top_payees_query = "SELECT 
                                    pa.payee_name,
                                    SUM(p.amount) as total_amount,
                                    COUNT(p.payment_id) as transaction_count
                                FROM payment p
                                JOIN dv d ON p.dv_id = d.dv_id
                                JOIN ors o ON d.ors_id = o.ors_id
                                JOIN payee pa ON o.payee_id = pa.payee_id
                                LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                                WHERE $where_clause
                                GROUP BY pa.payee_name
                                ORDER BY total_amount DESC
                                LIMIT 5";
                $top_payees_result = mysqli_query($connection, $top_payees_query);
                ?>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Payment Statistics</h5>
                                <table class="table">
                                    <tr>
                                        <td>Average Transaction Amount:</td>
                                        <td>₱<?= number_format($stats['avg_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Highest Transaction Amount:</td>
                                        <td>₱<?= number_format($stats['max_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Lowest Transaction Amount:</td>
                                        <td>₱<?= number_format($stats['min_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Payment Methods Used:</td>
                                        <td><?= $stats['payment_methods_count'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Unique DVs Processed:</td>
                                        <td><?= $stats['unique_dvs'] ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Top 5 Payees</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Payee</th>
                                            <th>Transactions</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($payee = mysqli_fetch_assoc($top_payees_result)): ?>
                                        <tr>
                                            <td><?= $payee['payee_name'] ?></td>
                                            <td><?= $payee['transaction_count'] ?></td>
                                            <td>₱<?= number_format($payee['total_amount'], 2) ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Weekly Distribution Heatmap -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Weekly Payment Distribution</h5>
                        
                        <?php
                        // Get day of week data
                        $day_of_week_query = "SELECT 
                                           DAYNAME(p.payment_date) as day_name,
                                           COUNT(*) as transaction_count
                                        FROM payment p
                                        JOIN dv d ON p.dv_id = d.dv_id
                                        JOIN ors o ON d.ors_id = o.ors_id
                                        LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                                        WHERE $where_clause
                                        GROUP BY DAYNAME(p.payment_date)
                                        ORDER BY FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
                        $day_of_week_result = mysqli_query($connection, $day_of_week_query);
                        ?>
                        
                        <div id="weeklyHeatmapChart" style="min-height: 200px;"></div>
                        
                        <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const dayData = [];
                            const dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            const dataValues = Array(7).fill(0); // Initialize with zeros
                            
                            <?php 
                            while ($row = mysqli_fetch_assoc($day_of_week_result)) : 
                                $day_index = "dayLabels.indexOf('{$row['day_name']}')";
                            ?>
                                dataValues[<?= $day_index ?>] = <?= $row['transaction_count'] ?>;
                            <?php endwhile; ?>
                            
                            dayData.push({
                                name: 'Transactions',
                                data: dataValues
                            });
                            
                            new ApexCharts(document.querySelector("#weeklyHeatmapChart"), {
                                series: dayData,
                                chart: {
                                    height: 200,
                                    type: 'heatmap',
                                    toolbar: {
                                        show: false
                                    }
                                },
                                dataLabels: {
                                    enabled: true
                                },
                                colors: ["#008FFB"],
                                xaxis: {
                                    categories: dayLabels
                                },
                                title: {
                                    text: 'Transaction Count by Day of Week'
                                }
                            }).render();
                        });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Original Summary Sections and Detailed Transactions follow -->

</main>

<!-- Vendor JS Files -->
<script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
<script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
<script src="../NiceAdmin/assets/vendor/quill/quill.min.js"></script>
<script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
<script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="../NiceAdmin/assets/js/main.js"></script>

<!-- Helper function for status badge colors -->
<?php
function getStatusBadgeColor($status) {
    switch ($status) {
        case 'Pending':
            return 'warning';
        case 'Endorsed':
            return 'info';
        case 'Processing':
            return 'primary';
        case 'Completed':
            return 'success';
        default:
            return 'secondary';
    }
}
?>

</body>
</html>