<?php
include '../DBConnection.php';

// Get filter parameters
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get oopap information
$oopap_id = 14; 
$oopap_query = "SELECT description FROM oopap WHERE oopap_id = $oopap_id";
$oopap_result = mysqli_query($connection, $oopap_query);
$oopap_data = mysqli_fetch_assoc($oopap_result);
$oopap_description = $oopap_data['description'];

// Base query for filtering
$where_clause = "WHERE oopap_id = $oopap_id AND YEAR(ors.date) = $selected_year";
if ($selected_month > 0) {
    $where_clause .= " AND MONTH(ors.date) = $selected_month";
}

$select = mysqli_query(
    $connection,
    "SELECT obligation_history.*, 
            ors.date, 
            ors.ors_no, 
            ors.payee_id, 
            ors.notes, 
            ors.total_amount,
            payee.payee_name 
     FROM obligation_history 
     LEFT JOIN ors ON obligation_history.ors_id = ors.ors_id 
     LEFT JOIN payee ON ors.payee_id = payee.payee_id
     $where_clause
     ORDER BY ors.date ASC"
);

// Calculate total amount for the filtered data
$total_filtered_amount = 0;
$filtered_data = [];
while ($row = mysqli_fetch_assoc($select)) {
    $filtered_data[] = $row;
    $total_filtered_amount += $row['total_amount'];
}
// Reset the pointer for later use
mysqli_data_seek($select, 0);

// Fetch total allotment
$total_allotment_query = "SELECT SUM(allotment) AS total_allotment FROM project WHERE oopap_id = $oopap_id";
$total_allotment_result = mysqli_query($connection, $total_allotment_query);
$total_allotment = mysqli_fetch_assoc($total_allotment_result)['total_allotment'];

// Fetch total balances
$total_balances_query = "SELECT SUM(balances) AS total_balances FROM project WHERE oopap_id = $oopap_id";
$total_balances_result = mysqli_query($connection, $total_balances_query);
$total_balances = mysqli_fetch_assoc($total_balances_result)['total_balances'];
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
</head>

<body>

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
        <h1>O1-RAPID RO 12(<?php echo date('Y'); ?>) - <?php echo htmlspecialchars($oopap_description); ?></h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <!-- Total Allotment Card -->
                <div class="col-md-6">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Allotment</h5>
                            <h3 class="card-text">₱<?php echo number_format($total_allotment, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <!-- Total Balances Card -->
                <div class="col-md-6">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Balances</h5>
                            <h3 class="card-text">₱<?php echo number_format($total_balances, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Filter by Month and Year</h5>
                    <form method="get" action="o1_rapidRO12Obligation.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="month" class="form-label">Month</label>
                            <select class="form-select" id="month" name="month">
                                <option value="0" <?php echo ($selected_month == 0) ? 'selected' : ''; ?>>All Months</option>
                                <?php
                                $months = [
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                ];
                                foreach ($months as $num => $name) {
                                    $selected = ($num == $selected_month) ? 'selected' : '';
                                    echo "<option value=\"$num\" $selected>$name</option>";
                                }
                                ?>
                            </select>
                            <small class="text-muted">Filter ORS by month</small>
                        </div>

                        <div class="col-md-4">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year">
                                <?php
                                $current_year = date('Y');
                                for ($year = $current_year; $year >= $current_year - 5; $year--) {
                                    $selected = ($year == $selected_year) ? 'selected' : '';
                                    echo "<option value=\"$year\" $selected>$year</option>";
                                }
                                ?>
                            </select>
                            <small class="text-muted">Filter ORS by month</small>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">

                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Obligation Number</th>
                                <th>Payee</th>
                                <th>Particulars</th>
                                <th>Obligations</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_data as $row) { ?>
                                <tr>
                                <td><?php echo date("F-d-Y", strtotime($row['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['ors_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['notes']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['total_amount'], 2)); ?></td>
                                    <td><button type="button" class="btn btn-primary view-details"
                                            onclick="window.location.href='ors_form.php?ors_no=<?php echo $row['ors_no']; ?>'">
                                            <i class="bi bi-eye" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="View Details"></i>
                                        </button></td>

                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>Total Obligations (<?php echo $selected_month > 0 ? $months[$selected_month] : 'All Months'; ?> <?php echo $selected_year; ?>):</strong></td>
                                <td><strong>₱<?php echo number_format($total_filtered_amount, 2); ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>

                    </table>
                </div>
            </div>

        </section>

    </main><!-- End #main -->

    <!-- update modal -->

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Project/Program/Activities</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_gas.php">
                        <input type="hidden" id="edit_project_id" name="project_id">
                        <input type="hidden" id="edit_account_id" name="edit_account_id">

                        <div class="mb-3">
                            <label for="edit_account_id" class="form-label">Project/Program/Activities</label>
                            <input type="text" class="form-control" id="edit_account_title" name="account_id" required
                                autocomplete="off" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="edit_allotment" class="form-label">Allotment</label>
                            <input type="number" class="form-control" id="edit_allotment" name="allotment" step="0.01"
                                required autocomplete="off">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="update" name="update" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


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

    <script>
        function filterData() {
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;
            let url = 'o1_rapidRO12Obligation.php?year=' + year;
            if (month) {
                url += '&month=' + month;
            }
            window.location.href = url;
        }
    </script>

</body>

</html>