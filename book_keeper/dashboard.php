<?php
include "../DBConnection.php";

// if (isset($_SESSION['user_id'])) {
//     $user_id = $_SESSION['user_id'];
// } else {
//     // Redirect to login if user_id is not set
//     header('Location: ../index.php');
//     exit();
// }


$ors_query = "SELECT COUNT(*) as total_ors, SUM(total_amount) as total_amount FROM ors";
$ors_result = $connection->query($ors_query);
$ors_data = $ors_result->fetch_assoc();

$dv_query = "SELECT COUNT(*) as total_dv, SUM(total_amount) as total_amount FROM dv ";
$dv_result = $connection->query($dv_query);
$dv_data = $dv_result->fetch_assoc();

$jev_query = "SELECT COUNT(*) as total_jev FROM jev";
$jev_result = $connection->query($jev_query);
$jev_data = $jev_result->fetch_assoc();
$pending_ors_query = "SELECT COUNT(*) as pending_ors FROM ors WHERE status = 'Pending'";
$pending_ors_result = $connection->query($pending_ors_query);
$pending_ors_data = $pending_ors_result->fetch_assoc();
$recent_ors_query = "SELECT o.*, p.payee_name, fc.fund_cluster_name, s.services_name 
                    FROM ors o 
                    JOIN payee p ON o.payee_id = p.payee_id 
                    JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id 
                    JOIN services s ON o.services_id = s.services_id 
                    ORDER BY o.date DESC LIMIT 5";
$recent_ors_result = $connection->query($recent_ors_query);
$recent_dv_query = "SELECT d.*, o.ors_no, o.purpose 
                   FROM dv d 
                   JOIN ors o ON d.ors_id = o.ors_id 
                   ORDER BY d.date DESC LIMIT 5";
$recent_dv_result = $connection->query($recent_dv_query);
$recent_jev_query = "SELECT j.*, d.dv_no, o.ors_no 
                    FROM jev j 
                    JOIN dv d ON j.dv_id = d.dv_id 
                    JOIN ors o ON j.ors_id = o.ors_id 
                    ORDER BY j.date DESC LIMIT 5";
$recent_jev_result = $connection->query($recent_jev_query);
$monthly_data_query = "SELECT 
                        DATE_FORMAT(date, '%Y-%m') as month, 
                        COUNT(*) as count, 
                        SUM(total_amount) as amount 
                     FROM ors 
                     GROUP BY DATE_FORMAT(date, '%Y-%m') 
                     ORDER BY month DESC LIMIT 6";
$monthly_data_result = $connection->query($monthly_data_query);
$monthly_labels = [];
$monthly_counts = [];
$monthly_amounts = [];

while ($row = $monthly_data_result->fetch_assoc()) {
    $monthly_labels[] = date('M Y', strtotime($row['month']));
    $monthly_counts[] = $row['count'];
    $monthly_amounts[] = $row['amount'];
}
$monthly_labels = array_reverse($monthly_labels);
$monthly_counts = array_reverse($monthly_counts);
$monthly_amounts = array_reverse($monthly_amounts);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Book Keeper - Dashboard </title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <link href="img/dti_logo.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/table.css">
</head>

<body>
    <?php include "Includes/header.php"; ?>'


    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Dashboard</h1>

        </div><!-- End Page Title -->

        <section class="section dashboard">
            <!-- Quick Action Buttons -->


            <div class="row">
                <!-- ORS Card -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ORS <span>| Total</span></h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $ors_data['total_ors']; ?></h6>
                                    <span
                                        class="text-success small pt-1 fw-bold">₱<?php echo number_format($ors_data['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End ORS Card -->

                <!-- DV Card -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">DV <span>| Total</span></h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $dv_data['total_dv']; ?></h6>
                                    <span
                                        class="text-success small pt-1 fw-bold">₱<?php echo number_format($dv_data['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End DV Card -->

                <!-- JEV Card -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">JEV <span>| Total</span></h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $jev_data['total_jev']; ?></h6>
                                    <span class="text-muted small pt-1">Journal Entry Voucher</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End JEV Card -->

                <!-- Pending ORS Card -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Pending ORS <span>| For Processing</span></h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $pending_ors_data['pending_ors']; ?></h6>
                                    <span class="text-warning small pt-1 fw-bold">Awaiting Processing</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Pending ORS Card -->
            </div>

            <div class="row">
                <!-- Monthly ORS Chart -->
                <div class="col-12">
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Filter</h6>
                                </li>
                                <li><a class="dropdown-item" href="#">This Month</a></li>
                                <li><a class="dropdown-item" href="#">This Year</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Monthly ORS Overview</h5>
                            <div class="chart-container">
                                <div id="monthlyChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent ORS -->
                <div class="col-12">
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Filter</h6>
                                </li>
                                <li><a class="dropdown-item" href="ors.php">View All</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Recent ORS</h5>
                            <div class="datatable">
                                <table class="datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col">ORS No.</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Payee</th>
                                            <th scope="col">Fund Cluster</th>
                                            <th scope="col">Service</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($ors = $recent_ors_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><a
                                                        href="ors.php?ors_id=<?php echo $ors['ors_id']; ?>"><?php echo $ors['ors_no']; ?></a>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($ors['date'])); ?></td>
                                                <td><?php echo $ors['payee_name']; ?></td>
                                                <td><?php echo $ors['fund_cluster_name']; ?></td>
                                                <td><?php echo $ors['services_name']; ?></td>
                                                <td>₱<?php echo number_format($ors['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php if ($ors['status'] == 'Pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Endorsed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- End Recent ORS -->
            </div>

            <div class="row">
                <!-- Recent DV -->
                <div class="col-12">
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Filter</h6>
                                </li>
                                <li><a class="dropdown-item" href="dv.php">View All</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Recent DV</h5>
                            <div class="datatable">
                                <table class="datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col">DV No.</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">ORS No.</th>
                                            <th scope="col">Purpose</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($dv = $recent_dv_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><a
                                                        href="dv.php?dv_id=<?php echo $dv['dv_id']; ?>"><?php echo $dv['dv_no']; ?></a>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($dv['date'])); ?></td>
                                                <td><?php echo $dv['ors_no']; ?></td>
                                                <td><?php echo $dv['purpose']; ?></td>
                                                <td>₱<?php echo number_format($dv['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php if ($dv['status'] == 'Pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Endorsed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- End Recent DV -->
            </div>

            <div class="row">
                <!-- Recent JEV -->
                <div class="col-12">
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Filter</h6>
                                </li>
                                <li><a class="dropdown-item" href="jev.php">View All</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Recent JEV</h5>
                            <div class="datatable">
                                <table class="datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col">JEV No.</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">DV No.</th>
                                            <th scope="col">ORS No.</th>
                                            <th scope="col">Administrative Aide</th>
                                            <th scope="col">Accountant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($jev = $recent_jev_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><a
                                                        href="jev.php?jev_id=<?php echo $jev['jev_id']; ?>"><?php echo $jev['jev_no']; ?></a>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($jev['date'])); ?></td>
                                                <td><?php echo $jev['dv_no']; ?></td>
                                                <td><?php echo $jev['ors_no']; ?></td>
                                                <td><?php echo $jev['administrative_aide']; ?></td>
                                                <td><?php echo $jev['accountant']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- End Recent JEV -->
            </div>
        </section>

    </main><!-- End #main -->


    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <?php include "includes/common_scripts.php"; ?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Monthly Chart
            var options = {
                series: [{
                    name: 'Number of ORS',
                    data: <?php echo json_encode($monthly_counts); ?>
                }, {
                    name: 'Total Amount',
                    data: <?php echo json_encode($monthly_amounts); ?>
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Poppins, sans-serif',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    lineCap: 'round'
                },
                colors: ['#0d6efd', '#198754'],
                xaxis: {
                    categories: <?php echo json_encode($monthly_labels); ?>,
                    labels: {
                        style: {
                            fontFamily: 'Poppins, sans-serif',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: [{
                    title: {
                        text: 'Number of ORS',
                        style: {
                            fontFamily: 'Poppins, sans-serif',
                            fontSize: '12px'
                        }
                    },
                    labels: {
                        style: {
                            fontFamily: 'Poppins, sans-serif',
                            fontSize: '12px'
                        }
                    }
                }, {
                    opposite: true,
                    title: {
                        text: 'Total Amount (₱)',
                        style: {
                            fontFamily: 'Poppins, sans-serif',
                            fontSize: '12px'
                        }
                    },
                    labels: {
                        style: {
                            fontFamily: 'Poppins, sans-serif',
                            fontSize: '12px'
                        }
                    }
                }],
                tooltip: {
                    theme: 'light',
                    y: [{
                        formatter: function (val) {
                            return val
                        }
                    }, {
                        formatter: function (val) {
                            return "₱" + val.toLocaleString()
                        }
                    }]
                },
                grid: {
                    borderColor: '#f1f1f1',
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 10
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontFamily: 'Poppins, sans-serif',
                    fontSize: '12px'
                }
            };

            var chart = new ApexCharts(document.querySelector("#monthlyChart"), options);
            chart.render();
        });
    </script>
</body>

</html>