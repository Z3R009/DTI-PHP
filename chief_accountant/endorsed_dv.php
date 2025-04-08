<?php
include '../DBConnection.php';
include 'includes/header.php';
include 'includes/sidebar.php';


$check_column_query = "SHOW COLUMNS FROM dv LIKE 'status'";
$column_result = mysqli_query($connection, $check_column_query);

if (!$column_result) {
    die("Query failed: " . mysqli_error($connection));
}

$column_exists = mysqli_num_rows($column_result) > 0;

if ($column_exists) {
    $where_clause = "WHERE dv.status = 'endorsed'";
} else {
    $where_clause = "WHERE dv.chief_accountant IS NOT NULL";
}

?>
 <main id="main" class="main">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Endorsed Disbursement Vouchers</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="endorsedDvTable">
                            <thead>
                                <tr>
                                    <th>DV No.</th>
                                    <th>Date</th>
                                    <th>Payee</th>
                                    <th>Amount</th>
                                    <th>Purpose</th>
                                    <th>Notes</th>
                                    <th>Endorsement Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT dv.*, payee.payee_name, ors.purpose, ors.notes 
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
                                    echo "<td>" . htmlspecialchars($row['purpose']) .  "</td>";
                                    echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                                    echo "<td><span class='badge bg-success'>Endorsed</span></td>";
                                    echo "<td>
                                            <a href='view_endorsed_dv.php?id=" . $row['dv_id'] . "' class='btn btn-info btn-sm'>
                                                <i class='bi bi-eye'></i> View
                                            </a>
                                            <a href='print_dv.php?id=" . $row['dv_id'] . "' class='btn btn-secondary btn-sm' target='_blank'>
                                                <i class='bi bi-printer'></i> Print
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
$(document).ready(function() {
    $('#endorsedDvTable').DataTable({
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