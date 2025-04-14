<?php
include '../DBConnection.php';


$check_column_query = "SHOW COLUMNS FROM dv LIKE 'status'";
$column_result = mysqli_query($connection, $check_column_query);
$column_exists = mysqli_num_rows($column_result) > 0;

if ($column_exists) {
    $where_clause = "WHERE dv.status = 'pending'";
} else {
    $where_clause = "WHERE dv.chief_accountant IS NULL";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - NiceAdmin Bootstrap Template</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
</head>

<body>

    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Pending Disbursement Vouchers</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="pendingDvTable">
                                    <thead>
                                        <tr>
                                            <th>DV No.</th>
                                            <th>Date</th>
                                            <th>Payee</th>
                                            <th>Amount</th>
                                            <th>Purpose</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT dv.*, payee.payee_name 
                                         FROM dv 
                                         LEFT JOIN ors ON dv.ors_id = ors.ors_id
                                         LEFT JOIN payee ON ors.payee_id = payee.payee_id
                                         $where_clause
                                         ORDER BY dv.date DESC";
                                        $result = mysqli_query($connection, $query);

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['dv_no']) . "</td>";
                                            echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['payee_name']) . "</td>";
                                            echo "<td>₱" . number_format($row['net_amount'], 2) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
                                            echo "<td><span class='badge bg-warning'>Pending</span></td>";
                                            echo "<td>
                                            <a href='review_dv.php?id=" . $row['dv_id'] . "' class='btn btn-primary btn-sm'>
                                                <i class='bi bi-check-circle'></i> Review
                                            </a>
                                          </td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $('#pendingDvTable').DataTable({
                    "order": [[1, "desc"]],
                    "pageLength": 10,
                    "language": {
                        "search": "Search DVs:"
                    }
                });
            });
        </script>

        <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
        <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
        <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
        <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
        <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
        <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
        <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

</body>

</html>