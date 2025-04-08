<?php
include '../DBConnection.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_GET['id'])) {
    header('Location: endorsed_dv.php');
    exit();
}

$dv_id = $_GET['id'];

$check_column_query = "SHOW COLUMNS FROM dv LIKE 'status'";
$column_result = mysqli_query($connection, $check_column_query);
$column_exists = mysqli_num_rows($column_result) > 0;

if ($column_exists) {
    $where_clause = "AND dv.status = 'endorsed'";
} else {
    $where_clause = "AND dv.chief_accountant IS NOT NULL";
}

$query = "SELECT dv.*, payee.payee_name, payee.tin_no, payee.address,
                 fund_cluster.fund_cluster_name, responsibility_center.code as rc_code
          FROM dv 
          LEFT JOIN ors ON dv.ors_id = ors.ors_id
          LEFT JOIN payee ON ors.payee_id = payee.payee_id
          LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
          LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
          WHERE dv.dv_id = ? $where_clause";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $dv_id);
$stmt->execute();
$result = $stmt->get_result();
$dv = $result->fetch_assoc();

if (!$dv) {
    header('Location: endorsed_dv.php');
    exit();
}
?>
 <main id="main" class="main">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">View Endorsed Disbursement Voucher</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>DV Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>DV No.</th>
                                    <td><?php echo htmlspecialchars($dv['dv_no']); ?></td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td><?php echo date('M d, Y', strtotime($dv['date'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Payee</th>
                                    <td><?php echo htmlspecialchars($dv['payee_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>TIN/Employee No.</th>
                                    <td><?php echo htmlspecialchars($dv['tin_no']); ?></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td><?php echo htmlspecialchars($dv['address']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Fund Cluster</th>
                                    <td><?php echo htmlspecialchars($dv['fund_cluster_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Responsibility Center</th>
                                    <td><?php echo htmlspecialchars($dv['rc_code']); ?></td>
                                </tr>
                                <tr>
                                    <th>Gross Amount</th>
                                    <td>₱<?php echo number_format($dv['net_amount'] + $dv['vat_amount'] + $dv['tax_1_amount'] + $dv['tax_2_amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>VAT</th>
                                    <td>₱<?php echo number_format($dv['vat_amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Net Amount</th>
                                    <td>₱<?php echo number_format($dv['net_amount'], 2); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <h5>Endorsement Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Endorsement Date</th>
                                    <td><?php echo date('M d, Y', strtotime($dv['date'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Chief Accountant</th>
                                    <td><?php echo htmlspecialchars($dv['chief_accountant']); ?></td>
                                </tr>
                                <tr>
                                    <th>Remarks</th>
                                    <td><?php echo htmlspecialchars($dv['remarks'] ?? 'No remarks'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <a href="endorsed_dv.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <a href="print_dv.php?id=<?php echo $dv['dv_id']; ?>" class="btn btn-primary" target="_blank">
                                <i class="bi bi-printer"></i> Print DV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>