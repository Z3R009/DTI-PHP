<?php
include '../DBConnection.php';
session_start();

// Get counts for different parts of the system
$dv_count_query = "SELECT COUNT(*) as total FROM dv";
$dv_result = mysqli_query($connection, $dv_count_query);
$dv_count = mysqli_fetch_assoc($dv_result)['total'] ?? 0;

$payment_count_query = "SELECT COUNT(*) as total FROM payment";
$payment_result = mysqli_query($connection, $payment_count_query);
$payment_count = mysqli_fetch_assoc($payment_result)['total'] ?? 0;

$users_count_query = "SELECT COUNT(*) as total FROM users";
$users_result = mysqli_query($connection, $users_count_query);
$users_count = mysqli_fetch_assoc($users_result)['total'] ?? 0;

$modules = [
    [
        'name' => 'Budget Officer Module',
        'icon' => 'bi-cash-coin',
        'color' => 'primary',
        'description' => 'Manages budget allocations, ORS (Obligation Request and Status) creation, and budget monitoring reports.',
        'features' => [
            'Create and manage allotments',
            'Generate ORS for procurement',
            'Track budget balances and obligations',
            'Generate budget reports'
        ]
    ],
    [
        'name' => 'Chief Accountant Module',
        'icon' => 'bi-journal-check',
        'color' => 'success',
        'description' => 'Handles the review and approval of disbursement vouchers, endorsement to payment, and accounting reports.',
        'features' => [
            'Review and approve vouchers',
            'Endorse vouchers for payment',
            'Track endorsed transactions',
            'Generate accounting reports'
        ]
    ],
    [
        'name' => 'Cashier Module',
        'icon' => 'bi-credit-card',
        'color' => 'info',
        'description' => 'Processes payments, manages ADA records, generates payment reports, and tracks completed payments.',
        'features' => [
            'Process pending payments',
            'Create ADA payments',
            'Generate LDDAP-APA forms',
            'Track and manage payment records'
        ]
    ],
    [
        'name' => 'Bookkeeper Module',
        'icon' => 'bi-book',
        'color' => 'warning',
        'description' => 'Manages financial records, journal entries, and general ledger. Generates financial reports.',
        'features' => [
            'Record journal entries',
            'Manage account titles',
            'Track financial transactions',
            'Generate financial reports'
        ]
    ],
    [
        'name' => 'Administrator Module',
        'icon' => 'bi-gear',
        'color' => 'dark',
        'description' => 'Controls system settings, user management, and overall system monitoring. Has access to all modules.',
        'features' => [
            'Manage user accounts and permissions',
            'Configure system settings',
            'Monitor system activity',
            'Access all system functionality',
            'Perform system maintenance'
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>System Overview - Administrator</title>
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

    <!-- Template Main CSS File -->
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">

    <style>
        .module-card {
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .feature-list {
            margin-top: 15px;
        }
        
        .feature-list li {
            margin-bottom: 8px;
        }
        
        .system-stat {
            text-align: center;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .system-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php include "includes/header.php"; ?>
    <?php include "includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>System Overview</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">System Overview</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">DTI Financial Management System</h5>
                            <p>
                                The DTI Financial Management System is a comprehensive web-based application designed to streamline and automate the financial processes within the Department of Trade and Industry. The system integrates budget management, accounting, payment processing, and financial reporting to provide a complete solution for financial operations.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="system-stat bg-light-primary">
                        <div class="stat-icon text-primary">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($dv_count); ?></div>
                        <div class="stat-label">Total Vouchers Processed</div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="system-stat bg-light-success">
                        <div class="stat-icon text-success">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($payment_count); ?></div>
                        <div class="stat-label">Total Payments Made</div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="system-stat bg-light-info">
                        <div class="stat-icon text-info">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($users_count); ?></div>
                        <div class="stat-label">System Users</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">System Modules</h5>
                            
                            <div class="row">
                                <?php foreach($modules as $module): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card module-card">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class="bi <?php echo $module['icon']; ?> text-<?php echo $module['color']; ?> me-2"></i>
                                                    <?php echo $module['name']; ?>
                                                </h5>
                                                <p class="card-text"><?php echo $module['description']; ?></p>
                                                
                                                <div class="feature-list">
                                                    <p class="fw-bold mb-2">Key Features:</p>
                                                    <ul>
                                                        <?php foreach($module['features'] as $feature): ?>
                                                            <li><?php echo $feature; ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">System Workflow</h5>
                            
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <p>The DTI Financial Management System follows a structured workflow:</p>
                                    
                                    <ol>
                                        <li><strong>Budget Allocation</strong> - Budget Officer creates and manages budget allotments</li>
                                        <li><strong>ORS Creation</strong> - Budget Officer creates ORS for procurement</li>
                                        <li><strong>Voucher Creation</strong> - Accountant creates disbursement vouchers</li>
                                        <li><strong>Voucher Review</strong> - Chief Accountant reviews and approves vouchers</li>
                                        <li><strong>Payment Processing</strong> - Cashier processes payments for approved vouchers</li>
                                        <li><strong>Financial Recording</strong> - Bookkeeper records transactions in the general ledger</li>
                                        <li><strong>Reporting</strong> - Various reports are generated for financial monitoring</li>
                                    </ol>
                                    
                                    <p class="mt-3">Administrators have access to all functionalities and can manage user accounts, system settings, and monitor overall system activity.</p>
                                </div>
                                
                                <div class="col-lg-4 text-center">
                                    <img src="../img/workflow.png" alt="System Workflow" class="img-fluid" style="max-height: 300px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

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
</body>
</html> 