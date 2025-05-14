<?php
include '../DBConnection.php';

// Get filter parameters
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get oopap information
$oopap_id = 1; // Set the oopap_id to 1 as requested
$oopap_query = "SELECT description FROM oopap WHERE oopap_id = $oopap_id";
$oopap_result = mysqli_query($connection, $oopap_query);
$oopap_data = mysqli_fetch_assoc($oopap_result);
$oopap_description = $oopap_data['description'];

// Base query for filtering obligations in the table
$table_where_clause = "WHERE oopap_id = $oopap_id AND YEAR(ors.date) = $selected_year";
if ($selected_month > 0) {
    $table_where_clause .= " AND MONTH(ors.date) = $selected_month";
}

// Query for total obligations up to selected month (for balance calculation)
$balance_where_clause = "WHERE oopap_id = $oopap_id AND YEAR(ors.date) = $selected_year";
if ($selected_month > 0) {
    $balance_where_clause .= " AND MONTH(ors.date) <= $selected_month";
}

// Get total obligations for balance calculation
$total_obligations_query = "SELECT COALESCE(SUM(ors.total_amount), 0) as total_amount 
                           FROM obligation_history 
                           LEFT JOIN ors ON obligation_history.ors_id = ors.ors_id 
                           $balance_where_clause";
$total_obligations_result = mysqli_query($connection, $total_obligations_query);
$total_filtered_amount = mysqli_fetch_assoc($total_obligations_result)['total_amount'];

// Get obligations for the table display
$select = mysqli_query(
    $connection,
    "SELECT 
        MAX(ors.date) as date,
        ors.ors_no,
        MAX(ors.payee_id) as payee_id,
        MAX(ors.notes) as notes,
        MAX(ors.oopap_id) as oopap_id,
        MAX(payee.payee_name) as payee_name,
        SUM(obligation_history.net) as total_net_amount,
        GROUP_CONCAT(DISTINCT ors.ors_id) as ors_ids
     FROM obligation_history 
     LEFT JOIN ors ON obligation_history.ors_id = ors.ors_id 
     LEFT JOIN payee ON ors.payee_id = payee.payee_id
     $table_where_clause
     GROUP BY ors.ors_no
     ORDER BY MAX(ors.date) ASC"
);

// Prepare filtered data for table display
$filtered_data = [];
while ($row = mysqli_fetch_assoc($select)) {
    $filtered_data[] = $row;
}

// Fetch total allotment
$total_allotment_query = "SELECT SUM(allotment) AS total_allotment FROM project WHERE oopap_id = $oopap_id AND YEAR(created_at) = $selected_year";
$total_allotment_result = mysqli_query($connection, $total_allotment_query);
$total_allotment = mysqli_fetch_assoc($total_allotment_result)['total_allotment'];

// Calculate total balances based on total allotment minus total amount from ORS
$total_balances = $total_allotment - $total_filtered_amount;
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
    <link href="img/dti_logo.png" rel="icon">
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
            <h1>GAS-OBLIGATION(<?php echo date('Y'); ?>) - <?php echo htmlspecialchars($oopap_description); ?></h1>
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
                    <form method="get" action="gas_obligation.php" class="row g-3">
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
                            <small class="text-muted">Shows all ors for the selected year</small>
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
                                    <td><?php echo htmlspecialchars(number_format($row['total_net_amount'], 2)); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary view-details" onclick="window.location.href='ors_form.php?ors_no=<?php echo $row['ors_no']; ?>'">
                                            <i class="bi bi-eye" data-bs-toggle="tooltip" data-bs-placement="top" title="View Details"></i>
                                        </button>
                                        <?php 
                                        // Get the first ORS ID from the grouped IDs
                                        $ors_ids = explode(',', $row['ors_ids']);
                                        $first_ors_id = $ors_ids[0];
                                        ?>
                                        <button type="button" class="btn btn-warning change-oopap" data-bs-toggle="modal" data-bs-target="#changeOopapModal" data-ors-id="<?php echo $first_ors_id; ?>" data-current-oopap="<?php echo $row['oopap_id']; ?>">
                                            <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top" title="Change OOPAP"></i>
                                        </button>
                                    </td>
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
            let url = 'gas_obligation.php?year=' + year;
            if (month) {
                url += '&month=' + month;
            }
            window.location.href = url;
        }

        // Handle OOPAP change modal
        document.addEventListener('DOMContentLoaded', function() {
            const changeOopapModal = document.getElementById('changeOopapModal');
            if (changeOopapModal) {
                changeOopapModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const orsId = button.getAttribute('data-ors-id');
                    const currentOopap = button.getAttribute('data-current-oopap');
                    
                    document.getElementById('orsId').value = orsId;
                    document.getElementById('oopapSelect').value = currentOopap;
                });
            }

            // Handle save button click
            document.getElementById('saveOopapChange').addEventListener('click', function() {
                const orsId = document.getElementById('orsId').value;
                const newOopapId = document.getElementById('oopapSelect').value;

                // Send AJAX request to update OOPAP
                fetch('update_oopap.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ors_id=${orsId}&oopap_id=${newOopapId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Updated successfully');
                        location.reload();
                    } else {
                        alert('Error Updating: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error updating OOPAP: ' + error);
                });
            });
        });
    </script>

    <!-- Change OOPAP Modal -->
    <div class="modal fade" id="changeOopapModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change OOPAP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeOopapForm">
                        <input type="hidden" id="orsId" name="ors_id">
                        <div class="mb-3">
                            <label for="oopapSelect" class="form-label">Select OOPAP</label>
                            <select class="form-select" id="oopapSelect" name="oopap_id" required>
                                <?php
                                $oopap_query = "SELECT oopap_id, oopap_name, description FROM oopap";
                                $oopap_result = mysqli_query($connection, $oopap_query);
                                while ($oopap = mysqli_fetch_assoc($oopap_result)) {
                                    echo "<option value='" . $oopap['oopap_id'] . "'>" . htmlspecialchars($oopap['oopap_name']) ." - " . htmlspecialchars($oopap['description']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveOopapChange">Save changes</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>