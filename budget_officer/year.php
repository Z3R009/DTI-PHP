<?php
include '../DBConnection.php';

// Get current year
$current_year = date('Y');

// Get selected year from POST or use current value
$selected_year = isset($_POST['year']) ? $_POST['year'] : $current_year;

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
function getTotals($connection, $oopap_id, $year) {
    $query = "SELECT SUM(allotment) AS total_allotment, SUM(balances) AS total_balances 
              FROM project 
              WHERE oopap_id = ? 
              AND YEAR(created_at) = ?";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $oopap_id, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return [
        'allotment' => $row['total_allotment'] ?? 0,
        'balances' => $row['total_balances'] ?? 0
    ];
}

// Get totals for different sections
$gas_totals = getTotals($connection, 1, $selected_year);
$oo1_totals = getTotals($connection, 2, $selected_year);
$oo2_totals = getTotals($connection, 3, $selected_year);
$oo3_totals = getTotals($connection, 4, $selected_year);
$oo3_1_totals = getTotals($connection, 5, $selected_year);
$oo3_2_totals = getTotals($connection, 6, $selected_year);
$oo4_1_1_totals = getTotals($connection, 8, $selected_year);
$oo4_1_2_totals = getTotals($connection, 9, $selected_year);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Yearly Overview - NiceAdmin Bootstrap Template</title>
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
            <h1>Yearly Overview</h1>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Select Year</h5>
                            <form method="post" class="row g-3">
                                <div class="col-md-8">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year" required>
                                        <?php foreach ($years as $year): ?>
                                            <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>>
                                                <?php echo $year; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block">Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- GAS Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">GAS</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($gas_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($gas_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OO1 Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">OO1</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo1_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo1_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OO2 Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">OO2</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo2_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo2_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OO3 Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">OO3</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo3_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo3_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OO3.1 Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">OO3.1</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo3_1_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo3_1_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OO3.2 Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">OO3.2</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo3_2_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo3_2_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OO4.1.1 Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">OO4.1.1</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo4_1_1_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo4_1_1_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OO4.1.2 Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">OO4.1.2</h5>
                            <div class="mb-3">
                                <h6 class="card-subtitle mb-2 text-muted">Total Allotment</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo4_1_2_totals['allotment'], 2); ?></h3>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balances</h6>
                                <h3 class="card-text">₱<?php echo number_format($oo4_1_2_totals['balances'], 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
