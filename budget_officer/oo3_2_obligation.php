<?php
include '../DBConnection.php';


// retrieve 

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
     WHERE oopap_id = 6" .
    (isset($_GET['month']) ? " AND MONTH(ors.date) = " . intval($_GET['month']) : "")
);


?>

<?php
// Fetch total allotment
$total_allotment_query = "SELECT SUM(allotment) AS total_allotment FROM project WHERE oopap_id = 6";
$total_allotment_result = mysqli_query($connection, $total_allotment_query);
$total_allotment = mysqli_fetch_assoc($total_allotment_result)['total_allotment'];

// Fetch total balances
$total_balances_query = "SELECT SUM(balances) AS total_balances FROM project WHERE oopap_id = 6";
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
            <h1>OO3.2</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <!-- Month Selection -->
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <div style="width: 200px;">
                        <select class="form-select" id="monthSelect" onchange="filterByMonth(this.value)">
                            <option value="">All Months</option>
                            <option value="1" <?php echo (isset($_GET['month']) && $_GET['month'] == '1') ? 'selected' : ''; ?>>January</option>
                            <option value="2" <?php echo (isset($_GET['month']) && $_GET['month'] == '2') ? 'selected' : ''; ?>>February</option>
                            <option value="3" <?php echo (isset($_GET['month']) && $_GET['month'] == '3') ? 'selected' : ''; ?>>March</option>
                            <option value="4" <?php echo (isset($_GET['month']) && $_GET['month'] == '4') ? 'selected' : ''; ?>>April</option>
                            <option value="5" <?php echo (isset($_GET['month']) && $_GET['month'] == '5') ? 'selected' : ''; ?>>May</option>
                            <option value="6" <?php echo (isset($_GET['month']) && $_GET['month'] == '6') ? 'selected' : ''; ?>>June</option>
                            <option value="7" <?php echo (isset($_GET['month']) && $_GET['month'] == '7') ? 'selected' : ''; ?>>July</option>
                            <option value="8" <?php echo (isset($_GET['month']) && $_GET['month'] == '8') ? 'selected' : ''; ?>>August</option>
                            <option value="9" <?php echo (isset($_GET['month']) && $_GET['month'] == '9') ? 'selected' : ''; ?>>September</option>
                            <option value="10" <?php echo (isset($_GET['month']) && $_GET['month'] == '10') ? 'selected' : ''; ?>>October</option>
                            <option value="11" <?php echo (isset($_GET['month']) && $_GET['month'] == '11') ? 'selected' : ''; ?>>November</option>
                            <option value="12" <?php echo (isset($_GET['month']) && $_GET['month'] == '12') ? 'selected' : ''; ?>>December</option>
                        </select>
                    </div>
                </div>
            </div>

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
                                <th>Obligation</th>
                                <th>Allotment</th>
                                <th>NET</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ors_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['notes']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['obligation'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['allotment'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['net'], 2)); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>

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
        function filterByMonth(month) {
            if (month) {
                window.location.href = 'oo3_2_obligation.php?month=' + month;
            } else {
                window.location.href = 'oo3_2_obligation.php';
            }
        }
    </script>

</body>

</html>