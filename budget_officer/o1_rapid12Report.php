<?php
include '../DBConnection.php';

// Get filter parameters
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// Query to get projects with oopap_id = 1 for the selected year
$select = mysqli_query(
    $connection,
    "SELECT 
        project.project_id,
        project.account_id,
        project.allotment,
        project.created_at,
        account_title.account_title,
        account_title.account_code
     FROM project 
     LEFT JOIN account_title ON project.account_id = account_title.account_id 
     WHERE project.oopap_id = 14 
     AND YEAR(project.created_at) = $selected_year
     ORDER BY account_title.account_title ASC"
);

// Function to get obligations for a specific account_id and time period
function getObligations($connection, $account_id, $year, $start_month, $end_month) {
    $query = "SELECT COALESCE(SUM(ors.total_amount), 0) as total
              FROM obligation_history oh
              JOIN ors ON oh.ors_id = ors.ors_id
              WHERE oh.project_id IN (
                  SELECT project_id 
                  FROM project 
                  WHERE account_id = ? 
                  AND oopap_id = 14
              )
              AND YEAR(ors.date) = ?
              AND MONTH(ors.date) BETWEEN ? AND ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iiii", $account_id, $year, $start_month, $end_month);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Prepare data for the table
$filtered_data = [];
$total_allotment = 0;
$total_balances = 0;
$total_last_month = 0;
$total_this_month = 0;
$total_to_date = 0;

if ($select) {
    while ($row = mysqli_fetch_assoc($select)) {
        // Calculate obligations for each period
        $last_month_total = getObligations($connection, $row['account_id'], $selected_year, 1, $selected_month - 1);
        $this_month_total = getObligations($connection, $row['account_id'], $selected_year, $selected_month, $selected_month);
        $to_date_total = $last_month_total + $this_month_total;

        // Calculate balance (ALLOTMENT - TO DATE)
        $balance = $row['allotment'] - $to_date_total;

        $row['last_month'] = $last_month_total;
        $row['this_month'] = $this_month_total;
        $row['to_date'] = $to_date_total;
        $row['balances'] = $balance;

        $filtered_data[] = $row;
        
        // Update totals
        $total_allotment += $row['allotment'];
        $total_last_month += $last_month_total;
        $total_this_month += $this_month_total;
        $total_to_date += $to_date_total;
        $total_balances += $balance;
    }
}

// Get month name
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>O1-RAPID REPORTS</title>
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
</head>

<body>
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>O1-RAPID RO 12 REPORTS</h1>
        </div>

        <section class="section dashboard">
            <!-- Filter Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">SUMMARY OF OBLIGATION (O1-RAPID RO 12) <?php echo $months[$selected_month] . ' ' . $selected_year; ?></h5>
                    <form method="get" action="o1_rapid12Report.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year</label>
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
                            <label for="month" class="form-label">Month</label>
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
                            <button type="submit" class="btn btn-primary">Show Report</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($filtered_data)): ?>
            <!-- Report Card -->
            <div class="card">
                <div class="card-body">
                <h5 class="card-title"></h5>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="vertical-align: middle;">UACS CODE</th>
                                    <th rowspan="2" style="vertical-align: middle;">PROJECT/PROGRAM/ACTIVITIES</th>
                                    <th rowspan="2" style="vertical-align: middle;" class="text-end">ALLOTMENT</th>
                                    <th colspan="3" class="text-center">OBLIGATIONS</th>
                                    <th rowspan="2" style="vertical-align: middle;" class="text-end">BALANCES</th>
                                </tr>
                                <tr>
                                    <th class="text-end" style="background-color: #B8CCE4;">LAST MONTH</th>
                                    <th class="text-end" style="background-color: #FCD5B4;">THIS MONTH</th>
                                    <th class="text-end" style="background-color: #E6B8B7;">TO DATE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7"><strong>GAS</strong></td>
                                </tr>
                                <?php foreach ($filtered_data as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['account_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                        <td class="text-end">₱<?php echo number_format($row['allotment'], 2); ?></td>
                                        <td class="text-end" style="background-color: #B8CCE4;">₱<?php echo number_format($row['last_month'], 2); ?></td>
                                        <td class="text-end" style="background-color: #FCD5B4;">₱<?php echo number_format($row['this_month'], 2); ?></td>
                                        <td class="text-end" style="background-color: #E6B8B7;">₱<?php echo number_format($row['to_date'], 2); ?></td>
                                        <td class="text-end">₱<?php echo number_format($row['balances'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end"><strong>TOTAL</strong></td>
                                    <td class="text-end"><strong>₱<?php echo number_format($total_allotment, 2); ?></strong></td>
                                    <td class="text-end" style="background-color: #B8CCE4;"><strong>₱<?php echo number_format($total_last_month, 2); ?></strong></td>
                                    <td class="text-end" style="background-color: #FCD5B4;"><strong>₱<?php echo number_format($total_this_month, 2); ?></strong></td>
                                    <td class="text-end" style="background-color: #E6B8B7;"><strong>₱<?php echo number_format($total_to_date, 2); ?></strong></td>
                                    <td class="text-end"><strong>₱<?php echo number_format($total_balances, 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Print Button -->
                    <div class="text-end mt-3">
                        <button onclick="printReport()" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                    </div>

                    <!-- Print Styles -->
                    <style type="text/css" media="print">
                        @media print {
                            @page {
                                size: portrait;
                                margin: 3mm;
                            }
                            
                            body * {
                                visibility: hidden;
                            }
                            
                            .card-body, .card-body * {
                                visibility: visible;
                            }
                            
                            .card-body {
                                position: absolute;
                                left: 0;
                                top: 0;
                                width: 98%;
                                padding: 0;
                                margin: 0;
                            }
                            
                            .btn, .datatable-top, .datatable-bottom, .back-to-top, .pagetitle, header, .breadcrumb {
                                display: none !important;
                            }

                            table {
                                width: 100%;
                                border-collapse: collapse;
                                font-size: 7px;
                                line-height: 1;
                                margin: 0;
                            }

                            th, td {
                                border: 0.5px solid #000;
                                padding: 2px;
                                white-space: nowrap;
                            }

                            .card-title {
                                font-size: 9px;
                                margin: 10px 0;
                                text-align: center;
                            }

                            .table-responsive {
                                margin: 0;
                                padding: 0;
                            }

                            /* Optimize column widths */
                            td:first-child { /* UACS CODE */
                                width: 10%;
                            }
                            td:nth-child(2) { /* PROJECT/PROGRAM/ACTIVITIES */
                                width: 35%;
                            }
                            td:nth-child(3), /* ALLOTMENT */
                            td:nth-child(4), /* LAST MONTH */
                            td:nth-child(5), /* THIS MONTH */
                            td:nth-child(6), /* TO DATE */
                            td:nth-child(7) { /* BALANCES */
                                width: 15%;
                            }
                        }
                    </style>

                    <!-- Print Script -->
                    <script>
                        function printReport() {
                            window.print();
                        }
                    </script>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No projects found for the selected year.
                </div>
            <?php endif; ?>
        </section>
    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

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
</body>
</html>