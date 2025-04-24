<?php
// Include database connection
include '../DBConnection.php';

// Check if there's a success message
$success_message = '';
if(isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Payment has been recorded successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '2') {
    $success_message = 'DV has been returned to Chief Accountant successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '3') {
    $success_message = 'Batch ADA payment has been recorded successfully!';
    
    // Store LDDAP data in JavaScript if available
    $lddap_ref = isset($_GET['lddap_ref']) ? $_GET['lddap_ref'] : '';
    $lddap_data = isset($_GET['lddap_data']) ? $_GET['lddap_data'] : '';
    $storage_key = isset($_GET['storage_key']) ? $_GET['storage_key'] : '';
}

// Check if there's an error message
$error_message = '';
if(isset($_GET['error']) && !empty($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

// Get pending vouchers from backend
require_once 'back_end/get_pending_vouchers.php';
$pending_result = getPendingVouchers();

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['return_to_chief'])) {
        // Process return to chief accountant action
        include 'back_end/return_to_chief.php';
    } elseif (isset($_POST['submit_batch_ada'])) {
        // Process batch ADA payment
        include 'back_end/batch_ada_payment.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Cashier - DTI PHP</title>
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

<link rel="stylesheet" href="css/table.css">
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
        <?php if(isset($_GET['success']) && $_GET['success'] == '3' && !empty($lddap_ref)): ?>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-success" id="viewLddapBtn">
                <i class="bi bi-file-earmark-text me-1"></i> View LDDAP-ADA Form
            </button>
        </div>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title fs-4 text-primary mb-1">Vouchers Endorsed for Payment</h5>
                                <p class="text-muted">These disbursement vouchers have been endorsed by the Chief Accountant and are ready for payment processing.</p>
                            </div>
                            <?php $pending_count = mysqli_num_rows($pending_result); ?>
                            <?php if ($pending_count > 0): ?>
                            <div class="d-flex gap-2">
                                <button type="button" id="selectAllBtn" class="btn btn-outline-primary">
                                    <i class="bi bi-check-all"></i> Select All
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#batchAdaModal" id="createBatchAdaBtn" disabled>
                                    <i class="bi bi-bank"></i> Create ADA
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" action="back_end/batch_ada_payment.php" id="batchAdaForm">
                            <div class="table-responsive">
                                <table class="datatable">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">
                                                <div class="form-check">
                                                <input type="checkbox" id="masterCheckbox" class="form-check-input">
                                                </div>
                                            </th>
                                            <th>DV No</th>
                                            <th>Date</th>
                                            <th>ORS No</th>
                                            <th>Payee</th>
                                            <th>Purpose</th>
                                            <th class="text-end">Net Amount</th>
                                            <th>Endorsed By</th>
                                            <th width="10%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($pending_count > 0): ?>
                                            <?php mysqli_data_seek($pending_result, 0); // Reset result pointer ?>
                                            <?php while ($row = mysqli_fetch_assoc($pending_result)): ?>
                                            <tr class="border-bottom">
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="selected_dvs[]" value="<?php echo $row['dv_id']; ?>" class="form-check-input dv-checkbox">
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-medium text-primary"><?php echo $row['dv_no']; ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border"><?php echo $row['ors_no']; ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-medium"><?php echo $row['payee_name']; ?></div>
                                                </td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($row['purpose']); ?>">
                                                        <?php echo substr($row['purpose'], 0, 50) . (strlen($row['purpose']) > 50 ? '...' : ''); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end fw-medium text-success">₱<?php echo number_format($row['net_amount'], 2); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="ms-2"><?php echo $row['chief_accountant']; ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-primary rounded-circle me-1" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $row['dv_id']; ?>" title="Process Payment">
                                                            <i class="bi bi-cash"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info text-white rounded-circle me-1" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['dv_id']; ?>" title="View DV">
                                                            <i class="bi bi-eye"></i>
                                                                </button>
                                                        <button type="button" class="btn btn-sm btn-warning text-white rounded-circle" data-bs-toggle="modal" data-bs-target="#returnModal<?php echo $row['dv_id']; ?>" title="Return to Chief">
                                                            <i class="bi bi-arrow-return-left"></i>
                                                                </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <!-- Payment Modal -->
                                            <div class="modal fade" id="paymentModal<?php echo $row['dv_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title">Process Payment for DV #<?php echo $row['dv_no']; ?></h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST" action="submit_payment_direct.php">
                                                            <div class="modal-body">
                                                                <div class="row mb-4">
                                                                    <div class="col-md-6">
                                                                        <div class="border-start border-primary border-4 ps-3">
                                                                            <h6 class="text-primary fw-bold mb-2">Payee Information</h6>
                                                                            <p class="mb-1 fs-5"><?php echo $row['payee_name']; ?></p>
                                                                            <p class="text-muted">DV #: <?php echo $row['dv_no']; ?> | ORS #: <?php echo $row['ors_no']; ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="border-start border-success border-4 ps-3">
                                                                            <h6 class="text-success fw-bold mb-2">Amount Details</h6>
                                                                            <p class="mb-1 fs-4 fw-bold">₱<?php echo number_format($row['net_amount'], 2); ?></p>
                                                                            <p class="text-muted">Endorsed: <?php echo date('M d, Y', strtotime($row['endorsement_date'])); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="card bg-light mb-4">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-dark mb-2">Purpose</h6>
                                                                        <p class="card-text"><?php echo $row['purpose']; ?>
                                                                            <?php if (!empty($row['notes'])): ?>
                                                                                <br><?php echo $row['notes']; ?>
                                                                        <?php endif; ?></p>
                                                                    </div>
                                                                </div>
                                                                
                                                                <input type="hidden" name="dv_id" value="<?php echo $row['dv_id']; ?>">
                                                                
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <label for="payment_type_<?php echo $row['dv_id']; ?>" class="form-label fw-medium">Payment Type</label>
                                                                        <select class="form-select form-select-lg" id="payment_type_<?php echo $row['dv_id']; ?>" name="payment_type" required>
                                                                            <option value="">Select Payment Type</option>
                                                                            <option value="Check">Check</option>
                                                                            <option value="ADA">ADA</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label for="reference_no_<?php echo $row['dv_id']; ?>" class="form-label fw-medium">Check/ADA Reference No:</label>
                                                                        <input type="text" class="form-control form-control-lg" id="reference_no_<?php echo $row['dv_id']; ?>" name="reference_no" required>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <label for="payment_date_<?php echo $row['dv_id']; ?>" class="form-label fw-medium">Payment Date</label>
                                                                        <input type="date" class="form-control form-control-lg" id="payment_date_<?php echo $row['dv_id']; ?>" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label for="amount_<?php echo $row['dv_id']; ?>" class="form-label fw-medium">Amount</label>
                                                                        <div class="input-group input-group-lg">
                                                                            <span class="input-group-text">₱</span>
                                                                            <input type="number" step="0.01" class="form-control" id="amount_<?php echo $row['dv_id']; ?>" name="amount" value="<?php echo $row['net_amount']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="remarks_<?php echo $row['dv_id']; ?>" class="form-label fw-medium">Remarks</label>
                                                                    <textarea class="form-control" id="remarks_<?php echo $row['dv_id']; ?>" name="remarks" rows="3" placeholder="Enter any additional information or notes about this payment"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                                                </button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="bi bi-save me-1"></i> Save Payment
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- View DV Modal -->
                                            <div class="modal fade" id="viewModal<?php echo $row['dv_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-info text-white">
                                                            <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Disbursement Voucher Details</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="card mb-4 border-0 shadow-sm">
                                                                <div class="card-header bg-light">
                                                                    <h5 class="card-title mb-0">DV #<?php echo $row['dv_no']; ?></h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="row g-4 mb-4">
                                                                <div class="col-md-6">
                                                                            <div class="border rounded p-3 h-100 bg-light-subtle">
                                                                                <h6 class="text-info border-bottom pb-2 mb-3">
                                                                                    <i class="bi bi-info-circle me-1"></i> DV Information
                                                                                </h6>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">DV Number:</span>
                                                                                    <span class="fw-medium ms-2"><?php echo $row['dv_no']; ?></span>
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">Date:</span>
                                                                                    <span class="fw-medium ms-2"><?php echo date('F d, Y', strtotime($row['date'])); ?></span>
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">Payee:</span>
                                                                                    <span class="fw-medium ms-2"><?php echo $row['payee_name']; ?></span>
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">ORS Number:</span>
                                                                                    <span class="fw-medium ms-2"><?php echo $row['ors_no']; ?></span>
                                                                                </div>
                                                                            </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                            <div class="border rounded p-3 h-100 bg-light-subtle">
                                                                                <h6 class="text-success border-bottom pb-2 mb-3">
                                                                                    <i class="bi bi-cash-coin me-1"></i> Financial Details
                                                                                </h6>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">Gross Amount:</span>
                                                                                    <span class="fw-medium ms-2">₱<?php echo number_format($row['gross_amount'] ?? $row['net_amount'], 2); ?></span>
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">Net Amount:</span>
                                                                                    <span class="fw-bold ms-2 fs-5 text-success">₱<?php echo number_format($row['net_amount'], 2); ?></span>
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">Endorsed By:</span>
                                                                                    <span class="fw-medium ms-2"><?php echo $row['chief_accountant']; ?></span>
                                                                                </div>
                                                                                <div class="mb-2">
                                                                                    <span class="text-muted">Endorsed On:</span>
                                                                                    <span class="fw-medium ms-2"><?php echo date('F d, Y', strtotime($row['endorsement_date'])); ?></span>
                                                                                </div>
                                                                            </div>
                                                                </div>
                                                            </div>
                                                            
                                                                    <div class="border rounded p-3 mb-3 bg-light-subtle">
                                                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                                                            <i class="bi bi-chat-square-text me-1"></i> Purpose
                                                                        </h6>
                                                                        <p class="mb-0">
                                                                <?php echo $row['purpose']; ?>
                                                                <?php if (!empty($row['notes'])): ?>
                                                                                <br><br><strong>Notes:</strong><br><?php echo $row['notes']; ?>
                                                                <?php endif; ?>
                                                                        </p>
                                                            </div>
                                                            
                                                            <?php if (!empty($row['endorsement_remarks'])): ?>
                                                                    <div class="border rounded p-3 bg-light-subtle">
                                                                        <h6 class="text-warning border-bottom pb-2 mb-3">
                                                                            <i class="bi bi-pencil-square me-1"></i> Endorsement Remarks
                                                                        </h6>
                                                                        <p class="mb-0"><?php echo nl2br($row['endorsement_remarks']); ?></p>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="bi bi-x-circle me-1"></i> Close
                                                            </button>
                                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $row['dv_id']; ?>" onclick="$('#viewModal<?php echo $row['dv_id']; ?>').modal('hide')">
                                                                <i class="bi bi-cash me-1"></i> Process Payment
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Return to Chief Accountant Modal -->
                                            <div class="modal fade" id="returnModal<?php echo $row['dv_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-warning text-white">
                                                            <h5 class="modal-title"><i class="bi bi-arrow-return-left me-2"></i>Return DV to Chief Accountant</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST" action="back_end/return_to_chief.php">
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning mb-4">
                                                                    <div class="d-flex">
                                                                        <div class="me-3">
                                                                            <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                                                                        </div>
                                                                        <div>
                                                                            <h5 class="alert-heading">Confirm Return</h5>
                                                                            <p class="mb-0">You are returning DV <strong>#<?php echo $row['dv_no']; ?></strong> to the Chief Accountant. Please provide a reason for this return.</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="border rounded p-3 mb-4 bg-light">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><span class="text-muted">Payee:</span></p>
                                                                            <p class="fw-medium"><?php echo $row['payee_name']; ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><span class="text-muted">Amount:</span></p>
                                                                            <p class="fw-bold text-danger">₱<?php echo number_format($row['net_amount'], 2); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <input type="hidden" name="return_dv_id" value="<?php echo $row['dv_id']; ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <label for="return_reason" class="form-label fw-medium">Reason for Return <span class="text-danger">*</span></label>
                                                                    <textarea class="form-control" name="return_reason" rows="4" placeholder="Please specify why this DV is being returned to the Chief Accountant" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                                                </button>
                                                                <button type="submit" name="return_to_chief" class="btn btn-warning text-white">
                                                                    <i class="bi bi-arrow-return-left me-1"></i> Return DV
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center p-4">
                                                <div class="empty-state">
                                                    <i class="bi bi-inbox me-2"></i>
                                                    <h5 class="mt-3">No pending payments found</h5>
                                                    <p class="text-muted">All vouchers have been processed or none are available at this time.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Batch ADA Modal -->
                            <div class="modal fade" id="batchAdaModal" tabindex="-1" aria-labelledby="batchAdaModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="batchAdaModalLabel"><i class="bi bi-bank me-2"></i>Create ADA Payment</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                                <div>
                                                    You are creating a batch ADA payment for multiple disbursement vouchers. Please review the selected DVs and provide the required information.
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="card h-100 border-primary">
                                                        <div class="card-header bg-primary bg-opacity-10">
                                                            <h5 class="card-title mb-0 text-primary"><i class="bi bi-calendar-date me-2"></i>Payment Details</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-3">
                                                                <label for="batch_date" class="form-label fw-medium">Payment Date <span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control form-control-lg" id="batch_date" name="batch_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="fund_code" class="form-label fw-medium">Fund Code <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="fund_code" name="fund_code" value="01101101" required>
                                                                <div class="form-text">This will be used in the LDDAP-ADA reference number format</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="bank_info" class="form-label fw-medium">Bank Information <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="bank_info" name="bank_info" value="LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81" required>
                                                                <div class="form-text">Format: BANK NAME- BRANCH- ACCOUNT NUMBER</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="batch_remarks" class="form-label fw-medium">Payment Remarks</label>
                                                                <textarea class="form-control" id="batch_remarks" name="batch_remarks" rows="3" placeholder="Enter any additional notes or comments about this batch payment"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="card h-100 border-success">
                                                        <div class="card-header bg-success bg-opacity-10">
                                                            <h5 class="card-title mb-0 text-success"><i class="bi bi-bank2 me-2"></i>ADA Reference</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="alert alert-secondary">
                                                                <small>The LDDAP-ADA reference format will be: <strong>fund_code-month-series-year</strong><br>
                                                                Example: 01101101-07-001-2023</small>
                                                            </div>
                                                            
                                                            <div class="form-check mb-3">
                                                                <input class="form-check-input" type="radio" name="ada_option" id="useCommonAda" value="common" checked>
                                                                <label class="form-check-label fw-medium" for="useCommonAda">
                                                                    Use a common ADA reference number for all selected DVs
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="mb-3" id="commonAdaSection">
                                                                <label for="common_ada_ref" class="form-label fw-medium">ADA Series Number <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control form-control-lg" id="common_ada_ref" name="common_ada_ref" 
                                                                       placeholder="Enter series number only (e.g. 001)" 
                                                                       pattern="[0-9]+" 
                                                                       title="Please enter numbers only" required>
                                                                <div class="form-text">The complete reference will be generated as: fund_code-month-series-year</div>
                                                            </div>
                                                            
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="ada_option" id="useMultipleAda" value="multiple">
                                                                <label class="form-check-label fw-medium" for="useMultipleAda">
                                                                    Use different ADA reference numbers for each DV
                                                                </label>
                                                                <p class="text-muted small mt-1">You'll be able to specify an ADA reference number for each DV below.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-header bg-light">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Selected Disbursement Vouchers</h5>
                                                        <span class="badge bg-primary rounded-pill" id="selectedDVCount">0 selected</span>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle mb-0" id="selectedDVTable">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>DV No.</th>
                                                                    <th>Payee</th>
                                                                    <th>Amount</th>
                                                                    <th>ADA Reference</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="selectedDVsBody">
                                                                <!-- Selected DVs will be populated here via JavaScript -->
                                                            </tbody>
                                                            <tfoot class="table-light">
                                                                <tr>
                                                                    <td colspan="2" class="fw-bold text-end">Total Amount:</td>
                                                                    <td class="fw-bold" id="totalSelectedAmount">₱0.00</td>
                                                                    <td></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                <i class="bi bi-x-circle me-1"></i> Cancel
                                            </button>
                                            <button type="button" class="btn btn-primary" id="submitBatchAdaBtn">
                                                <i class="bi bi-save me-1"></i> Process Batch Payment
                                            </button>
                                        </div>
                                    </div>
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

<script>
// Custom initialization for DataTable to preserve checkbox functionality
$(document).ready(function() {
    // Handle LDDAP-ADA data from URL if available
    <?php if(isset($_GET['success']) && $_GET['success'] == '3' && !empty($lddap_data) && !empty($storage_key)): ?>
    try {
        // Store LDDAP data in localStorage for the form to access
        const lddapData = <?php echo $lddap_data; ?>;
        const storageKey = "<?php echo $storage_key; ?>";
        localStorage.setItem(storageKey, JSON.stringify(lddapData));
        
        // Set up click handler for viewing LDDAP form
        document.getElementById('viewLddapBtn').addEventListener('click', function() {
            const lddapRef = "<?php echo $lddap_ref; ?>";
            const lddapWindow = window.open('LDDAP-APA.html?ref=' + encodeURIComponent(lddapRef), '_blank');
            
            // If the window opens, we can also try to send the data directly
            if (lddapWindow) {
                // Wait for the window to load
                setTimeout(function() {
                    try {
                        lddapWindow.postMessage(lddapData, window.location.origin);
                    } catch (e) {
                        console.error('Error sending data to LDDAP window:', e);
                    }
                }, 1000);
            }
        });
    } catch (e) {
        console.error('Error processing LDDAP data:', e);
    }
    <?php endif; ?>
    
    // Initialize DataTable with specific options to preserve form controls
    var table = $('.datatable').DataTable({
        "columnDefs": [
            { "orderable": false, "targets": 0 } // Disable sorting on checkbox column
        ],
        "order": [[1, 'asc']], // Order by second column (DV No) by default
        // Preserve state of checkboxes and other form elements
        "drawCallback": function(settings) {
            console.log("DataTable draw callback fired");
            setTimeout(function() {
                // Make sure checkbox events are still bound after DataTable redraws
                bindCheckboxEvents();
                
                // Initialize tooltips after table redraws
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                // Reinitialize payment modal handlers after table redraws
                document.querySelectorAll('[id^="paymentModal"]').forEach(modal => {
                    const form = modal.querySelector('form');
                    if (form) {
                        const submitBtn = form.querySelector('button[type="submit"][name="submit_payment"]');
                        if (submitBtn) {
                            // Remove existing listeners to avoid duplicates
                            const newSubmitBtn = submitBtn.cloneNode(true);
                            submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
                            
                            // Add new listener
                            newSubmitBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                console.log('Payment form submit button clicked');
                                form.submit();
                            });
                        }
                    }
                });
            }, 100);
        },
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "language": {
            "search": "<i class='bi bi-search me-1'></i>Search:",
            "paginate": {
                "previous": "<i class='bi bi-chevron-left'></i>",
                "next": "<i class='bi bi-chevron-right'></i>"
            },
            "lengthMenu": "Show _MENU_ entries",
            "zeroRecords": "No matching records found",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)"
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');

    // Initialize toggleAdaReferenceInputs when the page loads
    if (document.getElementById('useCommonAda')) {
        // Call initially with the default state of the common ADA radio button
        const initialUseCommon = document.getElementById('useCommonAda').checked;
        toggleAdaReferenceInputs(initialUseCommon);
    }

    // Function to handle all checkbox functionality
    function bindCheckboxEvents() {
    const masterCheckbox = document.getElementById('masterCheckbox');
    const dvCheckboxes = document.querySelectorAll('.dv-checkbox');
    const createBatchAdaBtn = document.getElementById('createBatchAdaBtn');
    const selectAllBtn = document.getElementById('selectAllBtn');
    
        console.log('Found elements:', {
            masterCheckbox: !!masterCheckbox,
            dvCheckboxesCount: dvCheckboxes.length,
            createBatchAdaBtn: !!createBatchAdaBtn,
            selectAllBtn: !!selectAllBtn
        });
        
        if (!masterCheckbox || dvCheckboxes.length === 0) return;
        
        // Clear previous event listeners by cloning and replacing elements
        if (masterCheckbox._hasEventListener) {
            const newMasterCheckbox = masterCheckbox.cloneNode(true);
            masterCheckbox.parentNode.replaceChild(newMasterCheckbox, masterCheckbox);
            masterCheckbox = newMasterCheckbox;
        }
        masterCheckbox._hasEventListener = true;
        
        // Master checkbox functionality
        masterCheckbox.addEventListener('change', function() {
            console.log('Master checkbox changed:', this.checked);
            dvCheckboxes.forEach(checkbox => {
                checkbox.checked = masterCheckbox.checked;
            });
            updateBatchButtonState();
        });
        
        // Individual checkbox functionality
        dvCheckboxes.forEach((checkbox, index) => {
            if (!checkbox._hasEventListener) {
            checkbox.addEventListener('change', function() {
                    console.log('Checkbox', index, 'changed:', this.checked);
                updateMasterCheckboxState();
                updateBatchButtonState();
            });
                checkbox._hasEventListener = true;
            }
        });
        
        // Select All button functionality
        if (selectAllBtn && !selectAllBtn._hasEventListener) {
            selectAllBtn.addEventListener('click', function() {
                console.log('Select All button clicked');
                masterCheckbox.checked = true;
                dvCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateBatchButtonState();
            });
            selectAllBtn._hasEventListener = true;
        }
        
        // Initial state update
        updateMasterCheckboxState();
        updateBatchButtonState();
    }
    
    // Update master checkbox state based on individual checkboxes
    function updateMasterCheckboxState() {
        const masterCheckbox = document.getElementById('masterCheckbox');
        const dvCheckboxes = document.querySelectorAll('.dv-checkbox');
        if (!masterCheckbox) return;
        
        const checkedCount = Array.from(dvCheckboxes).filter(cb => cb.checked).length;
        console.log('Checked count:', checkedCount, 'of', dvCheckboxes.length);
        masterCheckbox.checked = checkedCount === dvCheckboxes.length && dvCheckboxes.length > 0;
        masterCheckbox.indeterminate = checkedCount > 0 && checkedCount < dvCheckboxes.length;
    }
    
    // Enable/disable batch ADA button based on checkbox selection
    function updateBatchButtonState() {
        const createBatchAdaBtn = document.getElementById('createBatchAdaBtn');
        const dvCheckboxes = document.querySelectorAll('.dv-checkbox');
        if (!createBatchAdaBtn) return;
        
        const anyChecked = Array.from(dvCheckboxes).filter(cb => cb.checked).length > 0;
        console.log('Any checkboxes checked:', anyChecked, 'count:', dvCheckboxes.length);
        createBatchAdaBtn.disabled = !anyChecked;
        
        // Fix: Add visual feedback by changing the button appearance
        if (anyChecked) {
            createBatchAdaBtn.classList.remove('btn-secondary');
            createBatchAdaBtn.classList.add('btn-primary');
        } else {
            createBatchAdaBtn.classList.remove('btn-primary');
            createBatchAdaBtn.classList.add('btn-secondary');
        }
    }
    
    // Populate batch ADA modal with selected DVs
    const batchAdaModal = document.getElementById('batchAdaModal');
    if (batchAdaModal) {
        batchAdaModal.addEventListener('show.bs.modal', function() {
            console.log('Batch ADA modal opening');
            
            // Get references to table elements
            const selectedDVsBody = document.getElementById('selectedDVsBody');
            const totalAmountElement = document.getElementById('totalSelectedAmount');
            
            if (!selectedDVsBody || !totalAmountElement) {
                console.error('Could not find table elements:', { 
                    selectedDVsBody: !!selectedDVsBody, 
                    totalAmountElement: !!totalAmountElement 
                });
                return;
            }
            
            // Clear previous data
            selectedDVsBody.innerHTML = '';
            
            // Get all selected DVs and populate table
            let totalAmount = 0;
            const currentCheckboxes = document.querySelectorAll('.dv-checkbox:checked');
            console.log('Selected checkboxes for modal:', currentCheckboxes.length);
            
            // Update selected count
            document.getElementById('selectedDVCount').textContent = `${currentCheckboxes.length} selected`;
            
            currentCheckboxes.forEach((checkbox, idx) => {
                const row = checkbox.closest('tr');
                const dvId = checkbox.value;
                const dvNo = row.cells[1].textContent.trim();
                const payee = row.cells[4].textContent.trim();
                const amountText = row.cells[6].textContent.trim();
            
                console.log('Adding DV to batch:', { dvNo, payee, amountText });
                    
                // Extract amount (remove currency symbol and commas)
                const amount = parseFloat(amountText.replace(/[₱,]/g, ''));
                totalAmount += amount;
                
                // Generate a suggested series number (padded with leading zeros)
                const suggestedSeries = (idx + 1).toString().padStart(3, '0');
                
                // Add to table
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${dvNo}</td>
                    <td>${payee}</td>
                    <td>₱${amount.toFixed(2)}</td>
                    <td>
                        <input type="text" class="form-control ada-reference-input" 
                               data-dv-id="${dvId}" 
                               value="${suggestedSeries}"
                               placeholder="Series number only (e.g. 001)" required>
                    </td>
                `;
                selectedDVsBody.appendChild(tr);
            });
            
            // Update total amount
            totalAmountElement.textContent = `₱${totalAmount.toFixed(2)}`;
            
            // Handle common ADA reference toggle
            const useCommonAda = document.getElementById('useCommonAda').checked;
            toggleAdaReferenceInputs(useCommonAda);
        });
    }
    
    // Handle the common ADA reference toggle
    const useCommonAdaRadio = document.getElementById('useCommonAda');
    const useMultipleAdaRadio = document.getElementById('useMultipleAda');
    
    if (useCommonAdaRadio && useMultipleAdaRadio) {
        useCommonAdaRadio.addEventListener('change', function() {
            if (this.checked) toggleAdaReferenceInputs(true);
        });
        
        useMultipleAdaRadio.addEventListener('change', function() {
            if (this.checked) toggleAdaReferenceInputs(false);
        });
    }
    
    function toggleAdaReferenceInputs(useCommon = true) {
        const commonAdaContainer = document.getElementById('commonAdaSection');
        const adaReferenceInputs = document.querySelectorAll('.ada-reference-input');
        
        // Show/hide the common ADA input
        commonAdaContainer.style.display = useCommon ? 'block' : 'none';
        
        // Enable/disable individual ADA inputs
        adaReferenceInputs.forEach(input => {
            input.disabled = useCommon;
            input.required = !useCommon;
        });
        
        // Update the batch_reference_no field requirement
        const batchReferenceNo = document.getElementById('common_ada_ref');
        if (batchReferenceNo) {
            batchReferenceNo.required = useCommon;
        }
    }
    
    // Handle the batch ADA form submission
    const submitBatchAdaBtn = document.getElementById('submitBatchAdaBtn');
    if (submitBatchAdaBtn) {
        submitBatchAdaBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default action
            console.log('Submit Batch ADA button clicked');
            
            // Get the form values
            const useCommonAda = document.getElementById('useCommonAda').checked;
            const commonReferenceNo = document.getElementById('common_ada_ref').value;
            const paymentDate = document.getElementById('batch_date').value;
            const remarks = document.getElementById('batch_remarks').value;
            const fundCode = document.getElementById('fund_code').value;
            const bankInfo = document.getElementById('bank_info').value;
            
            // Get all checkboxes and verify selection
            const selectedCheckboxes = document.querySelectorAll('.dv-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one DV for batch payment');
                return;
            }
            
            // Validate form
            if (useCommonAda && !commonReferenceNo) {
                alert('Please enter an ADA Series Number');
                return;
            }
            
            if (!paymentDate) {
                alert('Please enter a Payment Date');
                return;
            }
            
            if (!fundCode) {
                alert('Please enter a Fund Code');
                return;
            }
            
            if (!bankInfo) {
                alert('Please enter Bank Information');
                return;
            }
            
            // Validate individual ADA references if not using common reference
            if (!useCommonAda) {
                const adaReferenceInputs = document.querySelectorAll('.ada-reference-input');
                let allValid = true;
                
                adaReferenceInputs.forEach(input => {
                    if (!input.value.trim()) {
                        allValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                if (!allValid) {
                    alert('Please provide ADA reference numbers for all selected DVs');
                    return;
                }
            }
            
            // Get the form and create a FormData object for better data handling
            const form = document.getElementById('batchAdaForm');
            
            // First clear all hidden inputs to prevent duplication
            const existingHiddenInputs = form.querySelectorAll('input[type="hidden"]');
            existingHiddenInputs.forEach(input => {
                if (input.name === 'selected_dvs[]' || input.name.startsWith('ada_references') || 
                    input.name === 'fund_code' || input.name === 'bank_info') {
                    input.remove();
                }
            });
            
            const formData = new FormData();
            
            // Add necessary fields that might be missing
            formData.append('submit_batch_ada', '1');
            formData.append('use_common_ada', useCommonAda ? '1' : '0');
            
            // Add fund code and bank info
            formData.append('fund_code', fundCode);
            formData.append('bank_info', bankInfo);
            
            // Handle the reference number(s)
            if (useCommonAda) {
                formData.append('batch_reference_no', commonReferenceNo);
                formData.append('common_ada_ref', commonReferenceNo); // Add both possible names
            } else {
                // For individual references, add them to the form
                const adaReferenceInputs = document.querySelectorAll('.ada-reference-input');
                adaReferenceInputs.forEach(input => {
                    const dvId = input.getAttribute('data-dv-id');
                    const refNo = input.value.trim();
                    formData.append(`ada_references[${dvId}]`, refNo);
                });
            }
            
            // Add payment date with both possible names
            formData.append('batch_payment_date', paymentDate);
            formData.append('batch_date', paymentDate);
            
            // Add remarks
            formData.append('batch_remarks', remarks);
            
            // Add selected DVs
            selectedCheckboxes.forEach(checkbox => {
                formData.append('selected_dvs[]', checkbox.value);
            });
            
            // Log form data for debugging
            console.log('Form submission data:', {
                useCommonAda: useCommonAda,
                referenceNo: useCommonAda ? commonReferenceNo : 'Multiple',
                paymentDate: paymentDate,
                remarks: remarks,
                fundCode: fundCode,
                bankInfo: bankInfo,
                selectedDVs: Array.from(selectedCheckboxes).map(cb => cb.value)
            });
            
            // Transfer formData values to the form for standard submission
            const keys = Array.from(formData.keys());
            keys.forEach(key => {
                const values = formData.getAll(key);
                
                // Add new elements for each value
                values.forEach(value => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });
            });
            
            // Submit the form
            form.submit();
        });
    }
    
    // Initialize on page load
    bindCheckboxEvents();
});
</script>