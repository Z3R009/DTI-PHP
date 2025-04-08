<?php
include '../DBConnection.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: pending_dv.php');
    exit();
}

$dv_id = $_GET['id'];

// Check if the status column exists
$check_column_query = "SHOW COLUMNS FROM dv LIKE 'status'";
$column_result = mysqli_query($connection, $check_column_query);
$column_exists = mysqli_num_rows($column_result) > 0;

// Fetch DV details
$query = "SELECT dv.*, payee.payee_name, payee.tin_no, payee.address,
                 fund_cluster.fund_cluster_name, responsibility_center.code as rc_code
          FROM dv 
          LEFT JOIN ors ON dv.ors_id = ors.ors_id
          LEFT JOIN payee ON ors.payee_id = payee.payee_id
          LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
          LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
          WHERE dv.dv_id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $dv_id);
$stmt->execute();
$result = $stmt->get_result();
$dv = $result->fetch_assoc();

if (!$dv) {
    header('Location: pending_dv.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['endorse'])) {
        $endorsement_date = date('Y-m-d H:i:s');
        $remarks = $_POST['remarks'];
        $chief_accountant = "NEIL ANTHONY T. MORALA"; // This should come from the logged-in user's session
        
        // Update DV based on whether status column exists
        if ($column_exists) {
            $update_query = "UPDATE dv SET 
                            status = 'endorsed',
                            endorsement_date = ?,
                            endorsement_remarks = ?,
                            chief_accountant = ?
                            WHERE dv_id = ?";
        } else {
            $update_query = "UPDATE dv SET 
                            endorsement_date = ?,
                            endorsement_remarks = ?,
                            chief_accountant = ?
                            WHERE dv_id = ?";
        }
        
        $update_stmt = $connection->prepare($update_query);
        $update_stmt->bind_param("sssi", $endorsement_date, $remarks, $chief_accountant, $dv_id);
        
        if ($update_stmt->execute()) {
            header('Location: pending_dv.php?success=1');
            exit();
        }
    }
}
?>
 <main id="main" class="main">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Review Disbursement Voucher</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
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
                                <h5>Certification Checklist</h5>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="cashAvailable" required>
                                    <label class="form-check-label" for="cashAvailable">
                                        Cash available
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="authorityToDebit" required>
                                    <label class="form-check-label" for="authorityToDebit">
                                        Subject to Authority to Debit Account (when applicable)
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="documentsComplete" required>
                                    <label class="form-check-label" for="documentsComplete">
                                        Supporting documents complete and amount claimed proper
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <h5>Remarks</h5>
                                <textarea class="form-control" name="remarks" rows="3" placeholder="Enter any remarks or notes"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" name="endorse" class="btn btn-primary" onclick="return confirm('Are you sure you want to endorse this DV?')">
                                    <i class="bi bi-check-circle"></i> Endorse for Payment
                                </button>
                                <a href="pending_dv.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

