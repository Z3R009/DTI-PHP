<?php
include '../DBConnection.php';


// Check if there's a success message
$success_message = '';
if(isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Payment has been recorded successfully!';
}

// Get all endorsed DVs that don't have payments yet
$pending_query = "SELECT d.*, p.payee_name, o.ors_no, o.purpose  
                 FROM dv d
                 JOIN ors o ON d.ors_id = o.ors_id
                 JOIN payee p ON o.payee_id = p.payee_id
                 WHERE (d.status = 'Endorsed' OR d.chief_accountant IS NOT NULL)
                 AND d.dv_id NOT IN (SELECT dv_id FROM payment WHERE status != 'Rejected')
                 ORDER BY d.date DESC";
$pending_result = mysqli_query($connection, $pending_query);

// Process payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    $dv_id = $_POST['dv_id'];
    $payment_type = $_POST['payment_type'];
    $reference_no = $_POST['reference_no'];
    $payment_date = $_POST['payment_date'];
    $amount = $_POST['amount'];
    $remarks = $_POST['remarks'];
    
    // Insert payment record
    $insert_query = "INSERT INTO payment (dv_id, payment_date, payment_type, reference_no, amount, remarks, created_by, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Cashier', 'Pending')";
    
    $stmt = $connection->prepare($insert_query);
    $stmt->bind_param("isssds", $dv_id, $payment_date, $payment_type, $reference_no, $amount, $remarks);
    
    if ($stmt->execute()) {
        // Update DV status to 'Processing'
        $update_dv = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
        $update_stmt = $connection->prepare($update_dv);
        $update_stmt->bind_param("i", $dv_id);
        $update_stmt->execute();
        
        // Redirect with success message
        header('Location: pending_payments.php?success=1');
        exit();
    } else {
        $error_message = "Error recording payment: " . $conn->error;
    }
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
        <h1>Pending Payments</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Pending Payments</li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Vouchers Endorsed for Payment</h5>
                        <p>These disbursement vouchers have been endorsed by the Chief Accountant and are ready for payment processing.</p>
                        
                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>DV No</th>
                                        <th>Date</th>
                                        <th>ORS No</th>
                                        <th>Payee</th>
                                        <th>Purpose</th>
                                        <th>Net Amount</th>
                                        <th>Endorsed By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($pending_result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($pending_result)): ?>
                                        <tr>
                                            <td><?php echo $row['dv_no']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                            <td><?php echo $row['ors_no']; ?></td>
                                            <td><?php echo $row['payee_name']; ?></td>
                                            <td><?php echo substr($row['purpose'], 0, 50) . (strlen($row['purpose']) > 50 ? '...' : ''); ?></td>
                                            <td>₱<?php echo number_format($row['net_amount'], 2); ?></td>
                                            <td><?php echo $row['chief_accountant']; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $row['dv_id']; ?>">
                                                    <i class="bi bi-cash"></i> Process Payment
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Payment Modal -->
                                        <div class="modal fade" id="paymentModal<?php echo $row['dv_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Process Payment for DV #<?php echo $row['dv_no']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST" action="">
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-bold">Payee:</label>
                                                                    <p><?php echo $row['payee_name']; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-bold">Purpose:</label>
                                                                    <p><?php echo $row['purpose']; ?></p>
                                                                </div>
                                                            </div>
                                                            
                                                            <input type="hidden" name="dv_id" value="<?php echo $row['dv_id']; ?>">
                                                            
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label for="payment_type" class="form-label">Payment Type</label>
                                                                    <select class="form-select" name="payment_type" required>
                                                                        <option value="">Select Payment Type</option>
                                                                        <option value="Check">Check</option>
                                                                        <option value="ADA">ADA</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="reference_no" class="form-label">Reference/Check No</label>
                                                                    <input type="text" class="form-control" name="reference_no" required>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <label for="payment_date" class="form-label">Payment Date</label>
                                                                    <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="amount" class="form-label">Amount</label>
                                                                    <input type="number" step="0.01" class="form-control" name="amount" value="<?php echo $row['net_amount']; ?>" required>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="remarks" class="form-label">Remarks</label>
                                                                <textarea class="form-control" name="remarks" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="submit_payment" class="btn btn-primary">Save Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No pending payments found. All vouchers have been processed.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> 