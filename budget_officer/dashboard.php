<?php

include '../DBConnection.php';

// Fetch OOPAP categories
$query = "SELECT * FROM oopap";
$result = $connection->query($query);

$totalQuery = "SELECT SUM(allotment) AS total_allotment FROM project";
$totalResult = $connection->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalAllotment = $totalRow['total_allotment'] ?? 0;

// Add query for total remaining balance
$balanceQuery = "SELECT SUM(balances) AS total_balance FROM project";
$balanceResult = $connection->query($balanceQuery);
$balanceRow = $balanceResult->fetch_assoc();
$totalBalance = $balanceRow['total_balance'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - NiceAdmin Bootstrap Template</title>
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

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Dashboard</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">


                <!-- Total Allotment -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card sales-card">

                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Filter</h6>
                                </li>
                                <!-- Show All Option -->
                                <li>
                                    <a class="dropdown-item oopap-filter" href="#" data-id="">All Categories</a>
                                </li>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <li>
                                        <a class="dropdown-item oopap-filter" href="#" data-id="<?= $row['oopap_id'] ?>">
                                            <?= $row['oopap_name'] ?>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title">Total Allotment <span id="selected-oopap">| All Categories</span>
                            </h5>

                            <div class="d-flex align-items-center">
                                <div class="ps-3">
                                    <h6 id="total-allotment"><?php echo "₱" . number_format($totalAllotment, 2); ?></h6>
                                </div>
                            </div>


                        </div>


                    </div>
                </div><!-- End Sales Card -->

                <!-- Total Remaining Balance -->
                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Filter</h6>
                                </li>
                                <!-- Show All Option -->
                                <li>
                                    <a class="dropdown-item balance-filter" href="#" data-id="">All Categories</a>
                                </li>
                                <?php 
                                // Reset the result pointer for reuse
                                $result->data_seek(0);
                                while ($row = $result->fetch_assoc()): 
                                ?>
                                    <li>
                                        <a class="dropdown-item balance-filter" href="#" data-id="<?= $row['oopap_id'] ?>">
                                            <?= $row['oopap_name'] ?>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title">Total Remaining Balance <span id="selected-balance-oopap">| All Categories</span></h5>

                            <div class="d-flex align-items-center">
                                <div class="ps-3">
                                    <h6 id="total-balance"><?php echo "₱" . number_format($totalBalance, 2); ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Balance Card -->

            </div>
        </section>

    </main><!-- End #main -->


    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

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


    <!-- allotment -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".oopap-filter").forEach(item => {
                item.addEventListener("click", function (e) {
                    e.preventDefault();

                    let oopapId = this.getAttribute("data-id");
                    let oopapName = this.innerText;

                    fetch("fetch_allotment.php?oopap_id=" + oopapId)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Fetched Data:", data);
                            document.getElementById("total-allotment").innerText = "₱" + data.total_allotment;
                            document.getElementById("selected-oopap").innerText = "| " + oopapName;
                        })
                        .catch(error => console.error("Error fetching allotment:", error));
                });
            });

            // Add balance filter code
            document.querySelectorAll(".balance-filter").forEach(item => {
                item.addEventListener("click", function (e) {
                    e.preventDefault();

                    let oopapId = this.getAttribute("data-id");
                    let oopapName = this.innerText;

                    fetch("fetch_balance.php?oopap_id=" + oopapId)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Fetched Balance Data:", data);
                            document.getElementById("total-balance").innerText = "₱" + data.total_balance;
                            document.getElementById("selected-balance-oopap").innerText = "| " + oopapName;
                        })
                        .catch(error => console.error("Error fetching balance:", error));
                });
            });
        });
    </script>

</body>

</html>