<?php
include '../DBConnection.php';
session_start();

// Function to safely get query result or return 0 if error
function safeQueryResult($query, $connection) {
    try {
        $result = mysqli_query($connection, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row ? ($row[array_key_first($row)] ?? 0) : 0;
        }
    } catch (Exception $e) {
        // Table doesn't exist or other database error
        error_log("Database error: " . $e->getMessage());
    }
    return 0;
}

// Get total user count - less likely to error
$users_query = "SELECT COUNT(*) as total_users FROM users";
try {
    $users_result = mysqli_query($connection, $users_query);
    $total_users = mysqli_fetch_assoc($users_result)['total_users'] ?? 0;
} catch (Exception $e) {
    $total_users = 0;
}

// Get user count by role
$roles_data = [];
$roles_labels = [];
$roles_counts = [];

try {
    $roles_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $roles_result = mysqli_query($connection, $roles_query);
    
    if ($roles_result) {
        while ($row = mysqli_fetch_assoc($roles_result)) {
            $roles_data[$row['role']] = $row['count'];
            $roles_labels[] = $row['role'];
            $roles_counts[] = $row['count'];
        }
    }
} catch (Exception $e) {
    // Handle error silently
}

// Get total allotment - handle missing table
$total_allotment = safeQueryResult("SELECT SUM(amount) as total_allotment FROM allotment", $connection);

// Get total obligations - handle missing table
$total_obligations = safeQueryResult("SELECT SUM(amount) as total_obligations FROM ors", $connection);

// Calculate balance and percentages
$total_balance = $total_allotment - $total_obligations;
$obligation_percentage = $total_allotment > 0 ? ($total_obligations / $total_allotment) * 100 : 0;
$balance_percentage = 100 - $obligation_percentage;

// Get processed DVs
$total_dvs = safeQueryResult("SELECT COUNT(*) as total_dvs FROM dv", $connection);

// Get payment stats
$total_payments = safeQueryResult("SELECT COUNT(*) as total_payments FROM payment", $connection);
$total_payment_amount = safeQueryResult("SELECT SUM(amount) as total_payment_amount FROM payment", $connection);

// Get payment methods distribution
$payment_types = [];
$payment_type_labels = [];
$payment_type_counts = [];

try {
    $payment_types_query = "SELECT payment_type, COUNT(*) as count FROM payment GROUP BY payment_type";
    $payment_types_result = mysqli_query($connection, $payment_types_query);
    
    if ($payment_types_result) {
        while ($row = mysqli_fetch_assoc($payment_types_result)) {
            $payment_types[$row['payment_type']] = $row['count'];
            $payment_type_labels[] = $row['payment_type'];
            $payment_type_counts[] = $row['count'];
        }
    }
} catch (Exception $e) {
    // Handle error silently
}

// Get recent activity (from various tables)
$recent_activity = [];

try {
    $recent_activity_query = "
        (SELECT 'Payment' as type, p.reference_no as ref, d.dv_no as item, p.payment_date as date, p.amount as amount, p.status as status
        FROM payment p
        JOIN dv d ON p.dv_id = d.dv_id
        ORDER BY p.payment_date DESC
        LIMIT 5)
        
        UNION
        
        (SELECT 'Voucher' as type, d.dv_no as ref, p.payee_name as item, d.date as date, d.net_amount as amount, d.status as status
        FROM dv d
        JOIN ors o ON d.ors_id = o.ors_id
        JOIN payee p ON o.payee_id = p.payee_id
        ORDER BY d.date DESC
        LIMIT 5)
        
        UNION
        
        (SELECT 'ORS' as type, o.ors_no as ref, p.payee_name as item, o.date as date, o.amount as amount, o.status as status
        FROM ors o
        JOIN payee p ON o.payee_id = p.payee_id
        ORDER BY o.date DESC
        LIMIT 5)
        
        ORDER BY date DESC
        LIMIT 10
    ";
    
    $recent_activity_result = mysqli_query($connection, $recent_activity_query);
} catch (Exception $e) {
    // If the query fails, create an empty result set
    $recent_activity_result = false;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Admin Dashboard - DTI Financial Management System</title>
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

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

    <?php include "Includes/header.php";?>
    <?php include "Includes/sidebar.php";?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Administrator Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Left side columns -->
                <div class="col-lg-8">
                    <div class="row">

                        <!-- Total Allotment -->
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Allotment <span>| This Year</span></h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-currency-dollar"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>₱<?php echo number_format($total_allotment, 2); ?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Total Allotment Card -->

                        <!-- Total Obligations Card -->
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card revenue-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Obligations <span>| This Year</span></h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-credit-card"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>₱<?php echo number_format($total_obligations, 2); ?></h6>
                                            <span class="text-success small pt-1 fw-bold"><?php echo number_format($obligation_percentage, 1); ?>%</span>
                                            <span class="text-muted small pt-2 ps-1">of total allotment</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Total Obligations Card -->

                        <!-- Total Balance -->
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card customers-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Balance <span>| This Year</span></h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>₱<?php echo number_format($total_balance, 2); ?></h6>
                                            <span class="text-primary small pt-1 fw-bold"><?php echo number_format($balance_percentage, 1); ?>%</span>
                                            <span class="text-muted small pt-2 ps-1">remaining</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Total Balance Card -->

                        <!-- Users Card -->
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card customers-card">
                                <div class="card-body">
                                    <h5 class="card-title">System Users</h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?php echo $total_users; ?></h6>
                                            <span class="text-muted small pt-2 ps-1">active users</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Users Card -->

                        <!-- Document Count Card -->
                        <div class="col-xxl-4 col-xl-12">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">Document Processing</h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6><?php echo number_format($total_dvs); ?> Vouchers</h6>
                                            <span class="text-muted small pt-2 ps-1">processed through the system</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Document Count Card -->

                        <!-- Payments Card -->
                        <div class="col-xxl-4 col-xl-12">
                            <div class="card info-card revenue-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Payments</h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>₱<?php echo number_format($total_payment_amount, 2); ?></h6>
                                            <span class="text-success small pt-1 fw-bold"><?php echo number_format($total_payments); ?></span>
                                            <span class="text-muted small pt-2 ps-1">payments processed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End Payments Card -->

                        <!-- No tables notification -->
                        <?php if($total_allotment == 0 && $total_obligations == 0 && $total_dvs == 0 && $total_payments == 0): ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Database Tables Missing</h4>
                                <p>It appears that one or more required database tables are missing. This could be because:</p>
                                <ul>
                                    <li>The system has not been fully set up yet</li>
                                    <li>The database migration has not been completed</li>
                                    <li>There was an error during database initialization</li>
                                </ul>
                                <hr>
                                <p class="mb-0">Please check your database configuration or contact the system administrator.</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Recent Activity -->
                        <div class="col-12">
                            <div class="card recent-sales overflow-auto">
                                <div class="card-body">
                                    <h5 class="card-title">Recent Activity <span>| Last 10 Transactions</span></h5>

                                    <?php if($recent_activity_result && mysqli_num_rows($recent_activity_result) > 0): ?>
                                    <table class="table table-borderless datatable">
                                        <thead>
                                            <tr>
                                                <th scope="col">Type</th>
                                                <th scope="col">Reference</th>
                                                <th scope="col">Item</th>
                                                <th scope="col">Date</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($recent_activity_result)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                                    <th scope="row"><a href="#"><?php echo htmlspecialchars($row['ref']); ?></a></th>
                                                    <td><?php echo htmlspecialchars($row['item']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                    <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                                                    <td>
                                                        <?php 
                                                        $status_class = 'secondary';
                                                        switch(strtolower($row['status'])) {
                                                            case 'completed':
                                                            case 'approved':
                                                                $status_class = 'success';
                                                                break;
                                                            case 'pending':
                                                                $status_class = 'warning';
                                                                break;
                                                            case 'rejected':
                                                                $status_class = 'danger';
                                                                break;
                                                            case 'processing':
                                                                $status_class = 'primary';
                                                                break;
                                                            default:
                                                                $status_class = 'secondary';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                    <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox text-secondary" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">No recent activity found.</p>
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div><!-- End Recent Activity -->

                    </div>
                </div><!-- End Left side columns -->

                <!-- Right side columns -->
                <div class="col-lg-4">

                    <!-- System Status Card -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">System Status</h5>
                            
                            <div class="d-grid gap-3">
                                <a href="manage_users.php" class="btn btn-primary">
                                    <i class="bi bi-people me-2"></i> Manage Users
                                </a>
                                <a href="system_settings.php" class="btn btn-secondary">
                                    <i class="bi bi-gear me-2"></i> System Settings
                                </a>
                                <a href="system_overview.php" class="btn btn-info">
                                    <i class="bi bi-info-circle me-2"></i> System Overview
                                </a>
                            </div>
                            
                            <div class="mt-4">
                                <h6 class="fw-bold">Quick Setup</h6>
                                <div class="list-group mt-3">
                                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        Create Database Tables
                                        <span class="badge bg-primary rounded-pill"><i class="bi bi-database-add"></i></span>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        Import Sample Data
                                        <span class="badge bg-success rounded-pill"><i class="bi bi-file-earmark-arrow-up"></i></span>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        System Configuration
                                        <span class="badge bg-warning rounded-pill"><i class="bi bi-gear-wide-connected"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End System Status Card -->

                    <!-- User Roles -->
                    <?php if(!empty($roles_data)): ?>
                    <div class="card">
                        <div class="card-body pb-0">
                            <h5 class="card-title">User Roles Distribution</h5>

                            <div id="userRolesChart" style="min-height: 300px;"></div>

                        </div>
                    </div><!-- End User Roles -->
                    <?php endif; ?>

                    <!-- Payment Methods -->
                    <?php if(!empty($payment_types)): ?>
                    <div class="card">
                        <div class="card-body pb-0">
                            <h5 class="card-title">Payment Methods</h5>

                            <div id="paymentMethodsChart" style="min-height: 300px;"></div>

                        </div>
                    </div><!-- End Payment Methods -->
                    <?php endif; ?>

                    <!-- System Quick Links -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Module Access</h5>

                            <div class="activity">
                                <div class="d-grid gap-2">
                                    <a href="pending_payments.php" class="btn btn-success">
                                        <i class="bi bi-credit-card me-2"></i> Process Payments
                                    </a>
                                    <a href="review_dv.php" class="btn btn-warning">
                                        <i class="bi bi-file-earmark-check me-2"></i> Review Vouchers
                                    </a>
                                    <a href="ors_management.php" class="btn btn-info">
                                        <i class="bi bi-journal-text me-2"></i> ORS Management
                                    </a>
                                    <a href="account_title.php" class="btn btn-dark">
                                        <i class="bi bi-book me-2"></i> Account Titles
                                    </a>
                                </div>
                            </div><!-- End System Quick Links -->

                        </div>
                    </div><!-- End Quick Links Card -->

                </div><!-- End Right side columns -->

            </div>
        </section>

    </main><!-- End #main -->

    <?php include 'includes/footer.php'; ?>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php if(!empty($roles_data)): ?>
            // User Roles Chart
            var userRolesChart = echarts.init(document.querySelector("#userRolesChart"));
            userRolesChart.setOption({
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [{
                    name: 'User Roles',
                    type: 'pie',
                    radius: '60%',
                    data: [
                        <?php foreach ($roles_data as $role => $count): ?>
                        {
                            value: <?php echo $count; ?>,
                            name: '<?php echo $role; ?>'
                        },
                        <?php endforeach; ?>
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });
            <?php endif; ?>

            <?php if(!empty($payment_types)): ?>
            // Payment Methods Chart
            var paymentMethodsChart = echarts.init(document.querySelector("#paymentMethodsChart"));
            paymentMethodsChart.setOption({
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [{
                    name: 'Payment Methods',
                    type: 'pie',
                    radius: '60%',
                    data: [
                        <?php foreach ($payment_types as $type => $count): ?>
                        {
                            value: <?php echo $count; ?>,
                            name: '<?php echo $type; ?>'
                        },
                        <?php endforeach; ?>
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });
            <?php endif; ?>
        });
    </script>

</body>

</html>