<?php
include '../DBConnection.php';


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
                 fund_cluster.fund_cluster_name, responsibility_center.code as rc_code,
                 ors.purpose
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

// Calculate total amount
$gross_amount = $dv['net_amount'] + $dv['vat_amount'] + $dv['tax_1_amount'] + $dv['tax_2_amount'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['endorse'])) {
        $endorsement_date = date('Y-m-d H:i:s');
        $remarks = $_POST['remarks'];
        $chief_accountant = "NEIL ANTHONY T. MORALA"; // This should come from the logged-in user's session
        
        // Update DV based on whether status column exists
        if ($column_exists) {
            $update_query = "UPDATE dv SET 
                            status = 'Endorsed',
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
        } else {
            $error_message = "Error updating record: " . $conn->error;
        }
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
        <h1>Review Disbursement Voucher</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="pending_dv.php">Pending DVs</a></li>
                <li class="breadcrumb-item active">Review DV</li>
            </ol>
        </nav>
    </div>

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
                        <h5 class="card-title">Disbursement Voucher Details</h5>
                        <form method="POST" action="">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">DV Information</h5>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">DV Number:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo htmlspecialchars($dv['dv_no']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Date:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo date('F d, Y', strtotime($dv['date'])); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Payee:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo htmlspecialchars($dv['payee_name']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">TIN/Employee No:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo htmlspecialchars($dv['tin_no']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Address:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo htmlspecialchars($dv['address']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Financial Details</h5>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Fund Cluster:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo htmlspecialchars($dv['fund_cluster_name']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Responsibility Center:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo htmlspecialchars($dv['rc_code']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Purpose:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static"><?php echo htmlspecialchars($dv['purpose']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Gross Amount:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static">₱<?php echo number_format($gross_amount, 2); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label fw-bold">Net Amount:</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static fw-bold text-primary">₱<?php echo number_format($dv['net_amount'], 2); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5>Chief Accountant's Endorsement</h5>
                                    <div class="form-group">
                                        <label for="remarks" class="form-label">Remarks (if any)</label>
                                        <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Enter any remarks or notes for the cashier"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" name="endorse" class="btn btn-success" onclick="return confirm('Are you sure you want to endorse this DV to the cashier for payment?')">
                                        <i class="bi bi-check-circle me-1"></i> Endorse for Payment
                                    </button>
                                    <a href="pending_dv.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

