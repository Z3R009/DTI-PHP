<?php
include '../DBConnection.php';

if (!isset($connection) || !$connection) {
    $conn_error = "Database connection is not available. Please check the DBConnection.php file.";
    error_log($conn_error);
}

$success_message = '';
if(isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Payment has been recorded successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '2') {
    $success_message = 'DV has been returned to Chief Accountant successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '3') {
    $success_message = 'Batch ADA payment has been recorded successfully!';
    $lddap_ref = isset($_GET['lddap_ref']) ? $_GET['lddap_ref'] : '';
    $lddap_data = isset($_GET['lddap_data']) ? $_GET['lddap_data'] : '';
    $storage_key = isset($_GET['storage_key']) ? $_GET['storage_key'] : '';
} elseif(isset($_GET['success']) && $_GET['success'] == '4') {
    $success_message = 'Payees have been merged successfully!';
    $merge_id = isset($_GET['merge_id']) ? $_GET['merge_id'] : '';
} elseif(isset($_GET['success']) && $_GET['success'] == '5') {
    $success_message = 'Merged payee group has been deleted successfully!';
} elseif(isset($_GET['success']) && $_GET['success'] == '6') {
    $payment_count = isset($_GET['payment_count']) ? $_GET['payment_count'] : '';
    $total = isset($_GET['total']) ? $_GET['total'] : '';
    $success_message = "Merged payment has been processed successfully! $payment_count vouchers paid totaling ₱$total.";
} elseif(isset($_GET['success']) && $_GET['success'] == '7') {
    $success_message = 'Cash Advance payment has been successfully processed.';
}
$error_message = '';
if(isset($_GET['error']) && !empty($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}
require_once 'back_end/get_pending_vouchers.php';
$pending_result = getPendingVouchers();
try {
    if (file_exists(__DIR__ . '/back_end/get_merged_payees.php')) {
        require_once 'back_end/get_merged_payees.php';
        if (function_exists('getMergedPayees')) {
            $merged_payees = getMergedPayees();
            if (!is_array($merged_payees)) {
                $merged_payees = [];
                $display_merged_payees_error = true;
            }
        } else {
            $merged_payees = [];
            $display_merged_payees_error = true;
            error_log("getMergedPayees function does not exist");
        }
    } else {
        $merged_payees = [];
        $display_merged_payees_error = true;
        error_log("get_merged_payees.php file not found");
    }
} catch (Exception $e) {
    error_log("Error getting merged payees: " . $e->getMessage());
    $merged_payees = [];
    $display_merged_payees_error = true;
}

$merged_payees_error_message = "Database Connection Error: Could not connect to the database. The merged payees feature may not work properly.";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['return_to_chief'])) {
        include 'back_end/return_to_chief.php';
    } elseif (isset($_POST['submit_batch_ada'])) {
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


    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">

<link rel="stylesheet" href="css/table.css">

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
    
    <?php if (isset($conn_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-database-x me-1"></i>
        <strong>Database Connection Error:</strong> Could not connect to the database. The merged payees feature may not work properly.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <?php if (isset($conn_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-database-x me-1"></i>
                    <strong>Database Connection Error:</strong> Could not connect to the database. Some features may not work properly.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($display_merged_payees_error) && $display_merged_payees_error): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Merged Payees Feature Issue:</strong> <?php echo $merged_payees_error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
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
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mergePayeeModal" id="mergePayeesBtn" disabled>
                                    <i class="bi bi-people"></i> Merge Payees
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
                                            <?php 
                                            // Create an array of DV IDs that are already part of merged groups
                                            $merged_dv_ids = array();
                                            if (!empty($merged_payees)) {
                                                foreach ($merged_payees as $group) {
                                                    if (!empty($group['dvs'])) {
                                                        foreach ($group['dvs'] as $dv) {
                                                            if (isset($dv['dv_id'])) {
                                                                $merged_dv_ids[] = $dv['dv_id'];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            mysqli_data_seek($pending_result, 0);
                                            
                                            $displayed_count = 0;
                                            
                                            while ($row = mysqli_fetch_assoc($pending_result)): 
                                                if (in_array($row['dv_id'], $merged_dv_ids)) {
                                                    continue;
                                                }
                                                
                                                $displayed_count++;
                                            ?>
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
                                                                            <option value="Cash">Cash</option>
                                                                            <option value="Cash Advance">Cash Advance</option>
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
                                            
                                            <?php if ($displayed_count === 0 && $pending_count > 0): ?>
                                            <tr>
                                                <td colspan="9" class="text-center p-4">
                                                    <div class="empty-state">
                                                        <i class="bi bi-people-fill fs-3 text-primary"></i>
                                                        <h5 class="mt-3">All vouchers have been merged</h5>
                                                        <p class="text-muted">All pending vouchers have been added to merged payee groups. You can view them in the Merged Payee Groups section below.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
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
                            
                            <!-- Added section: Merged Payee Groups table -->
                            <?php if (!empty($merged_payees)): ?>
                            <div class="card border-primary shadow-sm mt-4 mb-4">
                                <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Merged Payee Groups</h5>
                                    <span class="badge bg-primary rounded-pill"><?php echo count($merged_payees); ?> groups</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%" class="text-center">
                                                        <div class="form-check">
                                                            <input type="checkbox" id="masterMergedCheckbox" class="form-check-input">
                                                        </div>
                                                    </th>
                                                    <th>Group Name</th>
                                                    <th>Type</th>
                                                    <th>Vouchers</th>
                                                    <th>Purpose</th>
                                                    <th class="text-end">Total Amount</th>
                                                    <th width="10%" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($merged_payees as $group): ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="selected_merged_groups[]" value="<?php echo $group['merge_id']; ?>" 
                                                                   class="form-check-input merged-group-checkbox"
                                                                   data-merge-id="<?php echo $group['merge_id']; ?>"
                                                                   data-merge-name="<?php echo htmlspecialchars($group['merge_name']); ?>"
                                                                   data-total-amount="<?php echo $group['total_amount']; ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="fw-medium text-primary"><?php echo $group['merge_name']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo ($group['payee_type'] == 'Internal') ? 'success' : 'primary'; ?>">
                                                            <?php echo $group['payee_type']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo $group['total_dvs']; ?> vouchers</span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($group['description'])): ?>
                                                        <span class="text-truncate d-inline-block" style="max-width: 250px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($group['description']); ?>">
                                                            <?php echo substr($group['description'], 0, 50) . (strlen($group['description']) > 50 ? '...' : ''); ?>
                                                        </span>
                                                        <?php else: ?>
                                                        <span class="text-muted fst-italic">No description</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end fw-medium text-success">₱<?php echo number_format($group['total_amount'], 2); ?></td>
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-info text-white rounded-circle me-1 view-merged-details-btn" 
                                                                    data-merge-id="<?php echo $group['merge_id']; ?>" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-primary rounded-circle process-merged-payment-btn" 
                                                                    data-merge-id="<?php echo $group['merge_id']; ?>" 
                                                                    data-merge-name="<?php echo htmlspecialchars($group['merge_name']); ?>" 
                                                                    data-total="<?php echo $group['total_amount']; ?>" title="Process Payment">
                                                                <i class="bi bi-cash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <!-- End of merged payee groups table -->
                            
                            <!-- Batch ADA Modal -->
                            <div class="modal fade" id="batchAdaModal" tabindex="-1" aria-labelledby="batchAdaModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <form method="POST" action="back_end/batch_ada_payment.php" id="batchAdaForm">
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
                                                                <label for="account_name" class="form-label fw-medium">Account Name <span class="text-danger">*</span></label>
                                                                <select class="form-select" id="account_name" name="account_name" required>
                                                                    <option value="">-- Select Account --</option>
                                                                    <?php
                                                                    // Get all accounts
                                                                    $account_query = "SELECT * FROM account_name ORDER BY account_name ASC";
                                                                    $account_result = $connection->query($account_query);
                                                                    
                                                                    if ($account_result->num_rows > 0) {
                                                                        while ($account = $account_result->fetch_assoc()) {
                                                                            // Strip any HTML and special characters
                                                                            $account_name = htmlspecialchars($account['account_name']);
                                                                            $account_number = htmlspecialchars($account['account_number']);
                                                                            
                                                                            echo '<option value="' . $account['account_id'] . '" 
                                                                                data-type="' . $account['type'] . '"
                                                                                data-fund-code="' . (isset($account['FUND_SOURCE']) ? $account['FUND_SOURCE'] : '') . '"
                                                                                data-account-name="' . $account['account_name'] . '"
                                                                                data-account-number="' . $account['account_number'] . '">
                                                                                ' . $account['account_name'] . ' (' . $account['account_number'] . ')
                                                                            </option>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                                <div class="alert alert-warning mt-2">
                                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                                    <strong>Important:</strong> The total payment amount will be deducted from the selected account's balance in the Chief Accountant's draft project.
                                                                </div>
                                                                <div class="form-text">Select the bank account for this ADA payment</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="fund_code" class="form-label fw-medium">Fund Code <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="fund_code" name="fund_code" value="" required>
                                                                <div class="form-text">This will be used in the LDDAP-ADA reference number format</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                            <label for="bank_info" class="form-label fw-medium">Bank Information <span class="text-danger">*</span></label>
                                                                <select class="form-select" id="bank_select" name="bank_select" >
                                                                    <option value="LBP-KORONADAL">LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- <span id="display_account_number">Select an account first</span></option>
                                                                </select>
                                                                <div id="custom_bank_container" class="mt-2 d-none">
                                                                    <input type="text" class="form-control" id="bank_info_custom" name="bank_info_custom" placeholder="Enter custom bank information">
                                                                </div>
                                                                <input type="hidden" id="bank_info" name="bank_info" value="LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH-">

                                                                <script>
                                                                // Update bank information when account is selected
                                                                document.getElementById('account_name').addEventListener('change', function() {
                                                                    const selectedOption = this.options[this.selectedIndex];
                                                                    const accountNumber = selectedOption.getAttribute('data-account-number');
                                                                    
                                                                    if (accountNumber) {
                                                                        // Update the display span with the account number
                                                                        document.getElementById('display_account_number').textContent = accountNumber;
                                                                        
                                                                        // Update the hidden bank_info input
                                                                        document.getElementById('bank_info').value = 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- ' + accountNumber;
                                                                    } else {
                                                                        document.getElementById('display_account_number').textContent = 'Select an account first';
                                                                        document.getElementById('bank_info').value = 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH-';
                                                                    }
                                                                });
                                                                </script>
                                                                <div id="custom_bank_container" class="mt-2 d-none">
                                                                    <input type="text" class="form-control" id="bank_info_custom" name="bank_info_custom" placeholder="Enter custom bank information">
                                                                </div>
                                                                <input type="hidden" id="bank_info" name="bank_info" value="LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81">
                                                                <div class="form-text">Format: BANK NAME- BRANCH- ACCOUNT NUMBER</div>
                                                                <!-- Hidden fields for additional account information -->
                                                                <input type="hidden" id="nca_no" name="nca_no" value="">
                                                                <input type="hidden" id="nca_date" name="nca_date" value="">
                                                                <input type="hidden" id="fund_source" name="fund_source" value="">
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="batch_remarks" class="form-label fw-medium">Payment Remarks</label>
                                                                <textarea class="form-control" id="batch_remarks" name="batch_remarks" rows="3" placeholder="Enter any additional notes or comments about this batch payment"></textarea>
                                                            </div>
                                                            
                                                            <!-- Hidden input for submission -->
                                                            <input type="hidden" name="submit_batch_ada" value="1">
                                                            
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
                                                            
                                                            <input type="hidden" name="use_common_ada" id="use_common_ada" value="1">
                                                            
                                                            <script>
                                                                // Update the use_common_ada field when radio buttons change
                                                                document.getElementById('useCommonAda').addEventListener('change', function() {
                                                                    document.getElementById('use_common_ada').value = '1';
                                                                    document.getElementById('commonAdaSection').style.display = 'block';
                                                                });
                                                                
                                                                document.getElementById('useMultipleAda').addEventListener('change', function() {
                                                                    document.getElementById('use_common_ada').value = '0';
                                                                    document.getElementById('commonAdaSection').style.display = 'none';
                                                                });
                                                            </script>
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
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Merge Payees Modal -->
                            <div class="modal fade" id="mergePayeeModal" tabindex="-1" aria-labelledby="mergePayeeModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title" id="mergePayeeModalLabel"><i class="bi bi-people me-2"></i>Merge Internal Payees</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="back_end/merge_payees.php" id="mergePayeeForm">
                                            <div class="modal-body">
                                                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                                    <div>
                                                        You are creating a consolidated internal payee group for multiple vouchers. This allows you to process them as a single payment.
                                                    </div>
                                                </div>
                                                
                                                <div class="card mb-4 border-0 shadow-sm">
                                                    <div class="card-header bg-light">
                                                        <h6 class="card-title mb-0">Merge Information</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-8">
                                                                <label for="merge_name" class="form-label fw-medium">Merged Payee Name <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control form-control-lg" id="merge_name" name="merge_name" 
                                                                       placeholder="Enter a name for this group (e.g. Department Utilities Bills)" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="payee_type" class="form-label fw-medium">Payee Type</label>
                                                                <select class="form-select form-select-lg" id="payee_type" name="payee_type">
                                                                    <option value="Internal" selected>Internal</option>
                                                                    <option value="External">External</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="merge_description" class="form-label fw-medium">Description</label>
                                                            <textarea class="form-control" id="merge_description" name="merge_description" rows="3" 
                                                                      placeholder="Enter a description for this merged payee group"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-header bg-light">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="card-title mb-0">Selected Vouchers</h6>
                                                            <span class="badge bg-success rounded-pill" id="selectedMergeCount">0 selected</span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover align-middle mb-0" id="selectedMergeTable">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>DV No.</th>
                                                                        <th>Payee</th>
                                                                        <th>Purpose</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="selectedMergeBody">
                                                                    <!-- Selected DVs will be populated here via JavaScript -->
                                                                </tbody>
                                                                <tfoot class="table-light">
                                                                    <tr>
                                                                        <td colspan="3" class="text-end fw-bold">Total Amount:</td>
                                                                        <td class="text-end fw-bold" id="totalMergeAmount">₱0.00</td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <input type="hidden" name="merge_payees" value="1">
                                                <!-- Selected DVs will be added here as hidden inputs via JavaScript -->
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                                </button>
                                                <button type="submit" class="btn btn-success" id="submitMergeBtn">
                                                    <i class="bi bi-people me-1"></i> Create Merged Payee
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Merged Payees List Modal -->
                            <div class="modal fade" id="viewMergedPayeesModal" tabindex="-1" aria-labelledby="viewMergedPayeesModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title" id="viewMergedPayeesModalLabel"><i class="bi bi-people me-2"></i>Merged Payee Groups</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if (isset($display_merged_payees_error) && $display_merged_payees_error): ?>
                                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                                <div>
                                                    <?php echo $merged_payees_error_message; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($merged_payees)): ?>
                                                <div class="accordion" id="mergedPayeesAccordion">
                                                    <?php foreach ($merged_payees as $index => $group): ?>
                                                    <div class="accordion-item border mb-3 rounded shadow-sm">
                                                        <h2 class="accordion-header" id="heading<?php echo $group['merge_id']; ?>">
                                                            <button class="accordion-button <?php echo ($index !== 0) ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" 
                                                                    data-bs-target="#collapse<?php echo $group['merge_id']; ?>" 
                                                                    aria-expanded="<?php echo ($index === 0) ? 'true' : 'false'; ?>" 
                                                                    aria-controls="collapse<?php echo $group['merge_id']; ?>">
                                                                <div class="d-flex align-items-center justify-content-between w-100">
                                                                    <div>
                                                                        <span class="fw-bold"><?php echo isset($group['merge_name']) ? $group['merge_name'] : 'Unnamed Group'; ?></span>
                                                                        <span class="badge bg-<?php echo (isset($group['payee_type']) && $group['payee_type'] == 'Internal') ? 'success' : 'primary'; ?> ms-2">
                                                                            <?php echo isset($group['payee_type']) ? $group['payee_type'] : 'Internal'; ?>
                                                                        </span>
                                                                    </div>
                                                                    <div class="text-end me-3">
                                                                        <span class="badge bg-secondary me-2"><?php echo isset($group['total_dvs']) ? $group['total_dvs'] : 0; ?> vouchers</span>
                                                                        <span class="text-success fw-bold">₱<?php echo isset($group['total_amount']) ? number_format($group['total_amount'], 2) : '0.00'; ?></span>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse<?php echo $group['merge_id']; ?>" class="accordion-collapse collapse <?php echo ($index === 0) ? 'show' : ''; ?>" 
                                                             aria-labelledby="heading<?php echo $group['merge_id']; ?>" data-bs-parent="#mergedPayeesAccordion">
                                                            <div class="accordion-body">
                                                                <?php if (!empty($group['description'])): ?>
                                                                <div class="alert alert-light mb-3">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    <?php echo $group['description']; ?>
                                                                </div>
                                                                <?php endif; ?>
                                                                
                                                                <div class="d-flex justify-content-between mb-3">
                                                                    <small class="text-muted">
                                                                        Created by <?php echo isset($group['created_by']) ? $group['created_by'] : 'Unknown'; ?> on 
                                                                        <?php echo isset($group['created_at']) ? date('M d, Y h:i A', strtotime($group['created_at'])) : 'Unknown date'; ?>
                                                                    </small>
                                                                    <form method="POST" action="back_end/merge_payees.php" class="delete-merge-form">
                                                                        <input type="hidden" name="delete_merge_id" value="<?php echo $group['merge_id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-outline-danger delete-merge-btn">
                                                                            <i class="bi bi-trash"></i> Delete this group
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                                
                                                                <?php if (!empty($group['dvs'])): ?>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-hover">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>DV No.</th>
                                                                                <th>Date</th>
                                                                                <th>Payee</th>
                                                                                <th>Purpose</th>
                                                                                <th class="text-end">Amount</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach ($group['dvs'] as $dv): ?>
                                                                            <tr>
                                                                                <td><?php echo isset($dv['dv_no']) ? $dv['dv_no'] : 'N/A'; ?></td>
                                                                                <td><?php echo isset($dv['date']) ? date('M d, Y', strtotime($dv['date'])) : 'N/A'; ?></td>
                                                                                <td><?php echo isset($dv['payee_name']) ? $dv['payee_name'] : 'N/A'; ?></td>
                                                                                <td>
                                                                                    <?php if (isset($dv['purpose'])): ?>
                                                                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" data-bs-toggle="tooltip" 
                                                                                          title="<?php echo htmlspecialchars($dv['purpose']); ?>">
                                                                                        <?php echo substr($dv['purpose'], 0, 50) . (strlen($dv['purpose']) > 50 ? '...' : ''); ?>
                                                                                    </span>
                                                                                    <?php else: ?>
                                                                                    <span>N/A</span>
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                                <td class="text-end">₱<?php echo isset($dv['net_amount']) ? number_format($dv['net_amount'], 2) : '0.00'; ?></td>
                                                                            </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                        <tfoot class="table-light">
                                                                            <tr>
                                                                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                                                                <td class="text-end fw-bold">₱<?php echo isset($group['total_amount']) ? number_format($group['total_amount'], 2) : '0.00'; ?></td>
                                                                            </tr>
                                                                        </tfoot>
                                                                    </table>
                                                                </div>
                                                                <?php else: ?>
                                                                <div class="alert alert-warning">
                                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                                    No vouchers found in this group.
                                                                </div>
                                                                <?php endif; ?>
                                                                
                                                                <div class="d-flex justify-content-center mt-3">
                                                                    <button type="button" class="btn btn-primary process-merged-payment-btn" data-merge-id="<?php echo $group['merge_id']; ?>" 
                                                                            data-merge-name="<?php echo isset($group['merge_name']) ? $group['merge_name'] : 'Unnamed Group'; ?>" 
                                                                            data-total="<?php echo isset($group['total_amount']) ? $group['total_amount'] : 0; ?>">
                                                                        <i class="bi bi-cash-coin me-1"></i> Process Merged Payment
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center p-4 empty-state">
                                                    <i class="bi bi-people fs-1 text-muted"></i>
                                                    <h5 class="mt-3">No Merged Payee Groups Found</h5>
                                                    <p class="text-muted">You haven't created any merged payee groups yet.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="bi bi-x-circle me-1"></i> Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Process Merged Payment Modal -->
                            <div class="modal fade" id="processMergedPaymentModal" tabindex="-1" aria-labelledby="processMergedPaymentModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="processMergedPaymentModalLabel"><i class="bi bi-cash-coin me-2"></i>Process Merged Payment</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="back_end/process_merged_payment.php" id="processMergedPaymentForm">
                                            <div class="modal-body">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <div class="border-start border-primary border-4 ps-3">
                                                            <h6 class="text-primary fw-bold mb-2">Merged Payee Information</h6>
                                                            <p class="mb-1 fs-5" id="mergedPayeeName"></p>
                                                            <p class="text-muted">Internal Consolidated Payment</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="border-start border-success border-4 ps-3">
                                                            <h6 class="text-success fw-bold mb-2">Amount Details</h6>
                                                            <p class="mb-1 fs-4 fw-bold" id="mergedPayeeAmount"></p>
                                                            <p class="text-muted" id="mergedPayeeVoucherCount"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <input type="hidden" name="merge_id" id="mergedPayeeId">
                                                
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label for="merged_payment_type" class="form-label fw-medium">Payment Type</label>
                                                        <select class="form-select form-select-lg" id="merged_payment_type" name="payment_type" required>
                                                            <option value="">Select Payment Type</option>
                                                            <option value="Check">Check</option>
                                                            <option value="ADA">ADA</option>
                                                            <option value="Cash">Cash</option>
                                                            <option value="Cash Advance">Cash Advance</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="merged_reference_no" class="form-label fw-medium">Reference No:</label>
                                                        <input type="text" class="form-control form-control-lg" id="merged_reference_no" name="reference_no" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label for="merged_payment_date" class="form-label fw-medium">Payment Date</label>
                                                        <input type="date" class="form-control form-control-lg" id="merged_payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="merged_amount" class="form-label fw-medium">Amount</label>
                                                        <div class="input-group input-group-lg">
                                                            <span class="input-group-text">₱</span>
                                                            <input type="number" step="0.01" class="form-control" id="merged_amount" name="amount" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="merged_remarks" class="form-label fw-medium">Remarks</label>
                                                    <textarea class="form-control" id="merged_remarks" name="remarks" rows="3" placeholder="Enter any additional information about this payment"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-save me-1"></i> Process Payment
                                                </button>
                                            </div>
                                        </form>
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

<!-- View Merged Payee Details Modal -->
<div class="modal fade" id="viewMergedDetailsModal" tabindex="-1" aria-labelledby="viewMergedDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewMergedDetailsModalLabel"><i class="bi bi-people-fill me-2"></i>Merged Payee Group Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <h5 class="text-primary fw-bold mb-1" id="groupDetailName"></h5>
                        <p class="text-muted mb-0" id="groupDetailType"></p>
                    </div>
                    <div class="text-end">
                        <h5 class="text-success fw-bold mb-1" id="groupDetailAmount"></h5>
                        <p class="text-muted mb-0" id="groupDetailCount"></p>
                    </div>
                </div>
                
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-dark mb-2">Description</h6>
                        <p class="card-text" id="groupDetailDescription">No description available</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">Included Vouchers</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>DV No.</th>
                                        <th>Date</th>
                                        <th>Payee</th>
                                        <th>Purpose</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="groupDetailVouchers">
                                    <!-- Vouchers will be populated via JavaScript -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold" id="groupDetailTotal"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="border-top pt-3 text-muted small">
                    <div class="row">
                        <div class="col-md-6">
                            Created by: <span id="groupDetailCreatedBy"></span>
                        </div>
                        <div class="col-md-6 text-end">
                            Created on: <span id="groupDetailCreatedAt"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="processFromDetailsBtn">
                    <i class="bi bi-cash me-1"></i> Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

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
        const mergedGroupCheckboxes = document.querySelectorAll('.merged-group-checkbox');
        if (!createBatchAdaBtn) return;
        
        // Check if any regular DVs or merged groups are selected
        const anyDvChecked = Array.from(dvCheckboxes).some(cb => cb.checked);
        const anyMergedChecked = Array.from(mergedGroupCheckboxes).some(cb => cb.checked);
        const anyChecked = anyDvChecked || anyMergedChecked;
        
        console.log('Any items checked:', anyChecked, '(DVs:', anyDvChecked, ', Merged:', anyMergedChecked, ')');
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
    
    // Handle merged payee group checkboxes
    function bindMergedGroupCheckboxes() {
        const masterMergedCheckbox = document.getElementById('masterMergedCheckbox');
        const mergedGroupCheckboxes = document.querySelectorAll('.merged-group-checkbox');
        
        console.log('Found merged group elements:', {
            masterMergedCheckbox: !!masterMergedCheckbox,
            mergedGroupCheckboxesCount: mergedGroupCheckboxes.length
        });
        
        if (!masterMergedCheckbox || mergedGroupCheckboxes.length === 0) return;
        
        // Clear previous event listeners by cloning and replacing elements
        if (masterMergedCheckbox._hasEventListener) {
            const newMasterMergedCheckbox = masterMergedCheckbox.cloneNode(true);
            masterMergedCheckbox.parentNode.replaceChild(newMasterMergedCheckbox, masterMergedCheckbox);
            masterMergedCheckbox = newMasterMergedCheckbox;
        }
        masterMergedCheckbox._hasEventListener = true;
        
        // Master checkbox functionality for merged groups
        masterMergedCheckbox.addEventListener('change', function() {
            console.log('Master merged checkbox changed:', this.checked);
            mergedGroupCheckboxes.forEach(checkbox => {
                checkbox.checked = masterMergedCheckbox.checked;
            });
            updateBatchButtonState();
        });
        
        // Individual merged group checkbox functionality
        mergedGroupCheckboxes.forEach((checkbox, index) => {
            if (!checkbox._hasEventListener) {
                checkbox.addEventListener('change', function() {
                    console.log('Merged group checkbox', index, 'changed:', this.checked);
                    updateMasterMergedCheckboxState();
                    updateBatchButtonState();
                });
                checkbox._hasEventListener = true;
            }
        });
        
        // Initial state update
        updateMasterMergedCheckboxState();
    }
    
    // Update master merged checkbox state
    function updateMasterMergedCheckboxState() {
        const masterMergedCheckbox = document.getElementById('masterMergedCheckbox');
        const mergedGroupCheckboxes = document.querySelectorAll('.merged-group-checkbox');
        if (!masterMergedCheckbox) return;
        
        const checkedCount = Array.from(mergedGroupCheckboxes).filter(cb => cb.checked).length;
        console.log('Checked merged groups count:', checkedCount, 'of', mergedGroupCheckboxes.length);
        masterMergedCheckbox.checked = checkedCount === mergedGroupCheckboxes.length && mergedGroupCheckboxes.length > 0;
        masterMergedCheckbox.indeterminate = checkedCount > 0 && checkedCount < mergedGroupCheckboxes.length;
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
            console.log('Selected regular DVs for modal:', currentCheckboxes.length);
            
            // Get all selected merged groups
            const selectedMergedGroups = document.querySelectorAll('.merged-group-checkbox:checked');
            console.log('Selected merged groups for modal:', selectedMergedGroups.length);
            
            // Calculate total count of all selected items
            const totalSelectedCount = currentCheckboxes.length + selectedMergedGroups.length;
            
            // Update selected count
            document.getElementById('selectedDVCount').textContent = `${totalSelectedCount} selected`;
            
            // First process regular DVs
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
                               data-item-type="dv"
                               value="${suggestedSeries}"
                               placeholder="Series number only (e.g. 001)" required>
                    </td>
                `;
                selectedDVsBody.appendChild(tr);
            });
            
            // Then process merged groups
            selectedMergedGroups.forEach((checkbox, idx) => {
                const row = checkbox.closest('tr');
                const mergeId = checkbox.dataset.mergeId;
                const mergeName = checkbox.dataset.mergeName;
                const amountText = row.cells[5].textContent.trim();
                
                console.log('Adding merged group to batch:', { mergeId, mergeName, amountText });
                
                // Extract amount (remove currency symbol and commas)
                const amount = parseFloat(amountText.replace(/[₱,]/g, ''));
                totalAmount += amount;
                
                // Generate a suggested series number (padded with leading zeros)
                // Continue from the last regular DV index
                const suggestedSeries = (currentCheckboxes.length + idx + 1).toString().padStart(3, '0');
                
                // Add to table with a different style to distinguish it
                const tr = document.createElement('tr');
                tr.classList.add('table-primary');
                tr.innerHTML = `
                    <td><i class="bi bi-people-fill me-1"></i> Group</td>
                    <td>${mergeName}</td>
                    <td>₱${amount.toFixed(2)}</td>
                    <td>
                        <input type="text" class="form-control ada-reference-input" 
                               data-merge-id="${mergeId}" 
                               data-item-type="merge"
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
            e.preventDefault();
            
            // Get form data
            const accountId = document.getElementById('account_name').value;
            const batchDate = document.getElementById('batch_date').value;
            
            // Validate required fields
            if (!accountId || !batchDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please select an account and payment date.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            // Check for ADA reference when using common ADA
            const useCommonAda = document.getElementById('useCommonAda').checked;
            const commonAdaRef = document.getElementById('common_ada_ref').value;
            
            if (useCommonAda && !commonAdaRef) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing ADA Reference',
                    text: 'Please enter an ADA reference number.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            // Get selected DVs and merged groups
            const selectedDVs = document.querySelectorAll('.dv-checkbox:checked');
            const selectedGroups = document.querySelectorAll('.merged-group-checkbox:checked');
            
            // Validate at least one item is selected
            if (selectedDVs.length === 0 && selectedGroups.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please select at least one payment to process.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            // Show confirmation
            Swal.fire({
                icon: 'question',
                title: 'Confirm Batch Payment',
                text: `Are you sure you want to process ${selectedDVs.length + selectedGroups.length} payment(s)?`,
                showCancelButton: true,
                confirmButtonText: 'Yes, Process Payment',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading modal
                    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
                    loadingModal.show();
                    
                    // Submit the form
                    document.getElementById('batchAdaForm').submit();
                }
            });
        });
    }
    
    // Initialize on page load
    bindCheckboxEvents();
    bindMergedGroupCheckboxes();
});

// JavaScript for Merge Payees functionality
document.addEventListener('DOMContentLoaded', function() {
    // Reference to the merge payees button
    const mergePayeesBtn = document.getElementById('mergePayeesBtn');
    
    // Make sure we have checkbox elements on the page
    function initializeCheckboxes() {
        // Find all checkboxes with the dv-checkbox class
        const checkboxes = document.querySelectorAll('.dv-checkbox');
        console.log('Initializing checkboxes for merge functionality. Found:', checkboxes.length);
        
        if (checkboxes.length === 0) {
            console.warn('No checkboxes found with class .dv-checkbox');
        }
        
        // Add event listener to each checkbox
        checkboxes.forEach(checkbox => {
            // Remove existing listeners first to avoid duplicates
            checkbox.removeEventListener('change', handleCheckboxChange);
            // Add the event listener
            checkbox.addEventListener('change', handleCheckboxChange);
            console.log('Added change listener to checkbox:', checkbox.value);
        });
        
        // Also initialize the master checkbox if present
        const masterCheckbox = document.getElementById('masterCheckbox');
        if (masterCheckbox) {
            masterCheckbox.removeEventListener('change', updateMergeButtonState);
            masterCheckbox.addEventListener('change', updateMergeButtonState);
            console.log('Added change listener to master checkbox');
        } else {
            console.warn('Master checkbox not found with ID masterCheckbox');
        }
        
        // Also initialize merged group checkboxes for ADA selection
        bindMergedGroupCheckboxes();
        
        // Initial update
        updateMergeButtonState();
        updateBatchButtonState(); // Make sure batch button state is also updated
    }
    
    // Handle checkbox change events
    function handleCheckboxChange() {
        console.log('Checkbox changed:', this.checked, 'Value:', this.value);
        updateMergeButtonState();
        updateBatchButtonState(); // Update both button states
    }
    
    // Update merge payees button state when checkboxes change
    function updateMergeButtonState() {
        // Get all checked checkboxes
        const dvCheckboxes = document.querySelectorAll('.dv-checkbox:checked');
        console.log('Selected DVs count for merge:', dvCheckboxes.length);
        
        // Find the merge button
        const mergePayeesBtn = document.getElementById('mergePayeesBtn');
        if (!mergePayeesBtn) {
            console.error('Merge payees button not found!');
            return;
        }
        
        // Enable button only if at least 2 DVs are selected
        const checkedCount = dvCheckboxes.length;
        mergePayeesBtn.disabled = checkedCount < 2;
        
        // Update button appearance
        if (checkedCount >= 2) {
            mergePayeesBtn.classList.remove('btn-secondary');
            mergePayeesBtn.classList.add('btn-success');
            console.log('Merge button enabled - enough DVs selected');
        } else {
            mergePayeesBtn.classList.remove('btn-success');
            mergePayeesBtn.classList.add('btn-secondary');
            console.log('Merge button disabled - need at least 2 DVs');
        }
    }
    
    // Initialize checkboxes when the page loads
    initializeCheckboxes();
    
    // Also handle any dynamically added checkboxes
    // This is useful if the table data gets refreshed via AJAX
    function setupMutationObserver() {
        // Create an observer instance linked to a callback function
        const observer = new MutationObserver(function(mutations) {
            // Check if any mutations might have added checkboxes
            let shouldReinitialize = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Check if any added nodes contain checkboxes
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && (
                            node.classList.contains('dv-checkbox') || 
                            node.classList.contains('merged-group-checkbox') ||
                            (node.querySelector && (
                                node.querySelector('.dv-checkbox') || 
                                node.querySelector('.merged-group-checkbox')
                            ))
                        )) {
                            shouldReinitialize = true;
                        }
                    });
                }
            });
            
            // If checkboxes were added, reinitialize
            if (shouldReinitialize) {
                console.log('New checkboxes detected, reinitializing...');
                initializeCheckboxes();
            }
        });
        
        // Start observing the target node for configured mutations
        const tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            observer.observe(tableContainer, { childList: true, subtree: true });
            console.log('Mutation observer set up for dynamic checkboxes in main table');
        }
        
        // Also observe the merged payees table if it exists
        const mergedTable = document.querySelector('.merged-payees-table');
        if (mergedTable) {
            observer.observe(mergedTable, { childList: true, subtree: true });
            console.log('Mutation observer set up for merged payees table');
        }
    }
    
    // Set up observer for dynamic content
    setupMutationObserver();
    
    // Handle merge payee form submission
    const mergePayeeForm = document.getElementById('mergePayeeForm');
    if (mergePayeeForm) {
        mergePayeeForm.addEventListener('submit', function(e) {
            // Validate at least 2 DVs are selected
            const selectedDvs = this.querySelectorAll('input[name="selected_dvs[]"]');
            console.log('Form submission - Selected DVs:', selectedDvs.length);
            
            if (selectedDvs.length < 2) {
                e.preventDefault();
                alert('Please select at least two vouchers to merge.');
                return false;
            }
            
            // Validate merge name is provided
            const mergeName = document.getElementById('merge_name').value.trim();
            if (!mergeName) {
                e.preventDefault();
                alert('Please enter a name for the merged payee group.');
                document.getElementById('merge_name').focus();
                return false;
            }
            
            console.log('Form is valid, submitting...');
            return true;
        });
    }
});

// Populate merge payee modal with selected DVs
const mergePayeeModal = document.getElementById('mergePayeeModal');
if (mergePayeeModal) {
    mergePayeeModal.addEventListener('show.bs.modal', function(event) {
        console.log('Merge Payee modal opening');
        
        // Get references to table elements
        const selectedMergeBody = document.getElementById('selectedMergeBody');
        const totalMergeAmount = document.getElementById('totalMergeAmount');
        const selectedMergeCount = document.getElementById('selectedMergeCount');
        const mergePayeeForm = document.getElementById('mergePayeeForm');
        
        if (!selectedMergeBody || !totalMergeAmount || !mergePayeeForm) {
            console.error('Could not find merge table elements');
            return;
        }
        
        // Clear previous data
        selectedMergeBody.innerHTML = '';
        
        // Get all selected DVs
        const selectedCheckboxes = document.querySelectorAll('.dv-checkbox:checked');
        console.log('Selected checkboxes for merge:', selectedCheckboxes.length);
        
        if (selectedCheckboxes.length < 2) {
            console.warn('Not enough DVs selected for merge - need at least 2');
            
            // Add a warning message to the modal
            selectedMergeBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-warning py-4">
                        <i class="bi bi-exclamation-triangle-fill fs-3 mb-2"></i>
                        <p>Please select at least two vouchers to merge.</p>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </td>
                </tr>
            `;
            selectedMergeCount.textContent = "0 selected";
            totalMergeAmount.textContent = "₱0.00";
            
            // Close the modal after a short delay
            setTimeout(() => {
                const modalInstance = bootstrap.Modal.getInstance(mergePayeeModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }, 3000);
            
            return;
        }
        
        // Update selected count
        selectedMergeCount.textContent = `${selectedCheckboxes.length} selected`;
        
        // Clear existing hidden inputs for selected DVs
        const existingHiddenInputs = mergePayeeForm.querySelectorAll('input[name="selected_dvs[]"]');
        existingHiddenInputs.forEach(input => {
            input.remove();
        });
        
        let totalAmount = 0;
        let successfullyAddedCount = 0;
        
        // Process each selected checkbox
        selectedCheckboxes.forEach((checkbox, index) => {
            try {
                // Find the parent row - this is critical
                const row = checkbox.closest('tr');
                if (!row) {
                    console.error(`Could not find parent row for checkbox ${index}`, checkbox);
                    return;
                }
                
                // Get the DV ID
                const dvId = checkbox.value;
                
                // Correctly get data from cells based on the actual table structure
                // The table columns are: checkbox(0), DV No(1), Date(2), ORS No(3), Payee(4), Purpose(5), Net Amount(6), Chief Account(7), Actions(8)
                let dvNo = row.cells[1].querySelector('.fw-medium') ? 
                          row.cells[1].querySelector('.fw-medium').textContent.trim() : 
                          row.cells[1].textContent.trim();
                
                let payee = row.cells[4].querySelector('.fw-medium') ? 
                          row.cells[4].querySelector('.fw-medium').textContent.trim() : 
                          row.cells[4].textContent.trim();
                
                let purpose = '';
                const purposeElement = row.cells[5].querySelector('[data-bs-toggle="tooltip"]');
                if (purposeElement && purposeElement.getAttribute('title')) {
                    purpose = purposeElement.getAttribute('title');
                } else {
                    purpose = row.cells[5].textContent.trim();
                }
                
                // Get amount (at index 6)
                const amountText = row.cells[6].textContent.trim();
                const amount = parseFloat(amountText.replace(/[₱,]/g, '')) || 0;
                totalAmount += amount;
                
                // Add to table
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${dvNo}</td>
                    <td>${payee}</td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 200px;" data-bs-toggle="tooltip" title="${purpose}">
                            ${purpose.length > 40 ? purpose.substring(0, 40) + '...' : purpose}
                        </span>
                    </td>
                    <td>₱${amount.toFixed(2)}</td>
                `;
                selectedMergeBody.appendChild(tr);
                
                // Add hidden input for DV ID
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_dvs[]';
                hiddenInput.value = dvId;
                mergePayeeForm.appendChild(hiddenInput);
                
                successfullyAddedCount++;
                
            } catch (error) {
                console.error(`Error processing checkbox ${index}:`, error);
            }
        });
        
        // Update UI with the results
        totalMergeAmount.textContent = `₱${totalAmount.toFixed(2)}`;
        selectedMergeCount.textContent = `${successfullyAddedCount} selected`;
        
        // Initialize tooltips in the modal
        var tooltipTriggerList = [].slice.call(mergePayeeModal.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // If no DVs were successfully added, show an error
        if (successfullyAddedCount === 0) {
            selectedMergeBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-circle-fill fs-3 mb-2"></i>
                        <p>Error processing selected vouchers. Please try again or contact support.</p>
                    </td>
                </tr>
            `;
        }
    });
}
</script>

<script>
// Additional validation for merge functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded - Checking critical elements:');
    
    // Check if merge modal exists
    const mergeModal = document.getElementById('mergePayeeModal');
    console.log('Merge Modal found:', !!mergeModal);
    
    // Check if merge table body exists
    const mergeTableBody = document.getElementById('selectedMergeBody');
    console.log('Merge Table Body found:', !!mergeTableBody);
    
    // Check for checkboxes
    const checkboxes = document.querySelectorAll('.dv-checkbox');
    console.log('DV Checkboxes found:', checkboxes.length);
    
    // Check merge button
    const mergeButton = document.getElementById('mergePayeesBtn');
    console.log('Merge Button found:', !!mergeButton);
    
    // Try to add another event listener to the merge modal to ensure it works
    if (mergeModal) {
        mergeModal.addEventListener('shown.bs.modal', function() {
            console.log('Merge modal has been shown - Secondary listener');
            const selectedCheckboxes = document.querySelectorAll('.dv-checkbox:checked');
            console.log('Selected checkboxes count (secondary check):', selectedCheckboxes.length);
        });
    }
    
    // Handle view merged payee details button click
    const viewMergedDetailsBtns = document.querySelectorAll('.view-merged-details-btn');
    if (viewMergedDetailsBtns.length > 0) {
        console.log('Found view merged details buttons:', viewMergedDetailsBtns.length);
        
        viewMergedDetailsBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const mergeId = this.getAttribute('data-merge-id');
                console.log('View details clicked for merge ID:', mergeId);
                showMergedGroupDetails(mergeId);
            });
        });
    }
    
    // Function to show merged group details in modal
    function showMergedGroupDetails(mergeId) {
        // Find the merged group in the data
        const mergedGroups = <?php echo json_encode($merged_payees); ?>;
        const group = mergedGroups.find(g => g.merge_id == mergeId);
        
        if (!group) {
            console.error('Merged group not found with ID:', mergeId);
            return;
        }
        
        console.log('Found group data:', group);
        
        // Populate modal with group data
        document.getElementById('groupDetailName').textContent = group.merge_name || 'Unnamed Group';
        document.getElementById('groupDetailType').textContent = group.payee_type || 'Internal';
        document.getElementById('groupDetailAmount').textContent = '₱' + parseFloat(group.total_amount || 0).toFixed(2);
        document.getElementById('groupDetailCount').textContent = (group.total_dvs || 0) + ' vouchers';
        document.getElementById('groupDetailDescription').textContent = group.description || 'No description available';
        document.getElementById('groupDetailCreatedBy').textContent = group.created_by || 'Unknown';
        document.getElementById('groupDetailCreatedAt').textContent = group.created_at ? new Date(group.created_at).toLocaleString() : 'Unknown date';
        document.getElementById('groupDetailTotal').textContent = '₱' + parseFloat(group.total_amount || 0).toFixed(2);
        
        // Clear and populate vouchers table
        const vouchersBody = document.getElementById('groupDetailVouchers');
        vouchersBody.innerHTML = '';
        
        if (group.dvs && group.dvs.length > 0) {
            group.dvs.forEach(dv => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${dv.dv_no || 'N/A'}</td>
                    <td>${dv.date ? new Date(dv.date).toLocaleDateString() : 'N/A'}</td>
                    <td>${dv.payee_name || 'N/A'}</td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 200px;" data-bs-toggle="tooltip" 
                              title="${dv.purpose || ''}">
                            ${dv.purpose ? (dv.purpose.length > 40 ? dv.purpose.substring(0, 40) + '...' : dv.purpose) : 'N/A'}
                        </span>
                    </td>
                    <td class="text-end">₱${parseFloat(dv.net_amount || 0).toFixed(2)}</td>
                `;
                vouchersBody.appendChild(tr);
            });
            
            // Initialize tooltips in the modal
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        } else {
            // No vouchers
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="5" class="text-center py-3">
                    <i class="bi bi-exclamation-circle text-warning me-2"></i>
                    No vouchers found in this group
                </td>
            `;
            vouchersBody.appendChild(tr);
        }
        
        // Set up payment button
        const processBtn = document.getElementById('processFromDetailsBtn');
        if (processBtn) {
            // Remove existing listeners to avoid duplicates
            const newProcessBtn = processBtn.cloneNode(true);
            processBtn.parentNode.replaceChild(newProcessBtn, processBtn);
            
            // Add event listener to process button
            newProcessBtn.addEventListener('click', function() {
                console.log('Process from details clicked for merge ID:', mergeId);
                
                // Get references to process merged payment modal elements
                const processMergedPaymentModal = document.getElementById('processMergedPaymentModal');
                const mergedPayeeId = document.getElementById('mergedPayeeId');
                const mergedPayeeName = document.getElementById('mergedPayeeName');
                const mergedPayeeAmount = document.getElementById('mergedPayeeAmount');
                const mergedAmount = document.getElementById('merged_amount');
                const mergedPayeeVoucherCount = document.getElementById('mergedPayeeVoucherCount');
                
                if (processMergedPaymentModal && mergedPayeeId && mergedPayeeName && mergedPayeeAmount && mergedAmount) {
                    // Populate data
                    mergedPayeeId.value = mergeId;
                    mergedPayeeName.textContent = group.merge_name || 'Unnamed Group';
                    mergedPayeeAmount.textContent = '₱' + parseFloat(group.total_amount || 0).toFixed(2);
                    mergedAmount.value = parseFloat(group.total_amount || 0).toFixed(2);
                    mergedPayeeVoucherCount.textContent = (group.total_dvs || 0) + ' vouchers';
                    
                    // Close details modal and open payment modal
                    const detailsModal = bootstrap.Modal.getInstance(document.getElementById('viewMergedDetailsModal'));
                    if (detailsModal) {
                        detailsModal.hide();
                    }
                    
                    // Show process modal
                    const processModal = new bootstrap.Modal(processMergedPaymentModal);
                    processModal.show();
                }
            });
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('viewMergedDetailsModal'));
        modal.show();
    }
});
</script>

<script>

    // Account name selection handling for batch ADA form
    const accountNameSelect = document.getElementById('account_name');
    if (accountNameSelect) {
        accountNameSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                // The updateAccountInfo function will handle everything
                updateAccountInfo();
            } else {
                // Reset fields
                document.getElementById('fund_code').value = '';
                document.getElementById('bank_info').value = '';
                
                // Reset warning message
                const warningDiv = document.querySelector('#account_name').nextElementSibling;
                if (warningDiv && warningDiv.classList.contains('alert-warning')) {
                    warningDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Important:</strong> The total payment amount will be deducted from the selected account's balance in the Chief Accountant's draft project.`;
                }
            }
        });
    }
    
   
</script>


<script>
// Helper function to get all selected DVs with their details
function getSelectedDVs() {
    const selectedDVs = [];
    
    // Get all checked DV checkboxes
    const selectedDvCheckboxes = document.querySelectorAll('.dv-checkbox:checked');
    
    selectedDvCheckboxes.forEach(checkbox => {
        const dvId = checkbox.value;
        const dvRow = checkbox.closest('tr');
        
        // Get the reference input for this DV if it exists
        let reference = '';
        const refInput = document.querySelector(`.ada-reference-input[data-dv-id="${dvId}"]`);
        if (refInput) {
            reference = refInput.value;
        }
        
        // Get amount from the data attribute or from the row
        let amount = 0;
        if (dvRow) {
            const amountText = dvRow.querySelector('.amount-column')?.textContent;
            if (amountText) {
                // Try to extract numeric value from string like "₱1,234.56"
                amount = parseFloat(amountText.replace(/[^\d.-]/g, ''));
            }
        }
        
        selectedDVs.push({
            id: dvId,
            reference: reference,
            amount: amount || 0
        });
    });
    
    return selectedDVs;
}

// Helper function to get all selected merged groups with their details
function getSelectedMergedGroups() {
    const selectedGroups = [];
    
    // Get all checked merged group checkboxes
    const selectedGroupCheckboxes = document.querySelectorAll('.merged-group-checkbox:checked');
    
    selectedGroupCheckboxes.forEach(checkbox => {
        const groupId = checkbox.value;
        const groupRow = checkbox.closest('tr');
        
        // Get the reference input for this group if it exists
        let reference = '';
        const refInput = document.querySelector(`.ada-reference-input[data-merge-id="${groupId}"]`);
        if (refInput) {
            reference = refInput.value;
        }
        
        // Get amount from the data attribute or from the row
        let amount = 0;
        if (groupRow) {
            const amountText = groupRow.querySelector('.amount-column')?.textContent;
            if (amountText) {
                // Try to extract numeric value from string like "₱1,234.56"
                amount = parseFloat(amountText.replace(/[^\d.-]/g, ''));
            }
        }
        
        selectedGroups.push({
            id: groupId,
            reference: reference,
            amount: amount || 0
        });
    });
    
    return selectedGroups;
}

// Handle the batch ADA form submission
const submitBatchAdaBtn = document.getElementById('submitBatchAdaBtn');
</script>

<!-- Modal with processing spinner for AJAX requests -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mt-4">Processing Payment</h5>
                <p class="mb-0 text-muted">Please wait while we process your payment request...</p>
            </div>
        </div>
    </div>
</div>

<!-- Script for batch payment functionality -->
<script src="js/batch_payment.js"></script>
</body>
</html>