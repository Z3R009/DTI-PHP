<?php
include '../DBConnection.php';

// Get all processed merged payees
$processed_query = "SELECT 
                        mp.merge_id,
                        mp.merge_name,
                        mp.description,
                        mp.payee_type,
                        mp.created_by,
                        mp.created_at,
                        COUNT(mpi.dv_id) AS total_dvs,
                        SUM(d.net_amount) AS total_amount,
                        (SELECT MIN(payment_date) FROM payment p 
                         JOIN dv ON p.dv_id = dv.dv_id 
                         JOIN merged_payee_items mpi2 ON mpi2.dv_id = dv.dv_id 
                         WHERE mpi2.merge_id = mp.merge_id) AS first_payment_date
                    FROM 
                        merged_payees mp
                    LEFT JOIN 
                        merged_payee_items mpi ON mp.merge_id = mpi.merge_id
                    LEFT JOIN 
                        dv d ON mpi.dv_id = d.dv_id
                    WHERE 
                        mp.processed = 1
                    GROUP BY 
                        mp.merge_id
                    ORDER BY 
                        first_payment_date DESC, mp.created_at DESC";
                        
$processed_result = mysqli_query($connection, $processed_query);

// Handle reset action
$success_message = '';
$error_message = '';

if (isset($_POST['reset_merge_id'])) {
    $merge_id = intval($_POST['reset_merge_id']);
    
    // Start transaction
    $connection->begin_transaction();
    
    try {
        // Check if the merged payee exists
        $check_query = "SELECT merge_id, merge_name FROM merged_payees WHERE merge_id = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("i", $merge_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Merged payee with ID $merge_id not found.");
        }
        
        $row = $result->fetch_assoc();
        $merge_name = $row['merge_name'];
        
        // Reset the processed status
        $update_query = "UPDATE merged_payees SET processed = 0 WHERE merge_id = ?";
        $update_stmt = $connection->prepare($update_query);
        $update_stmt->bind_param("i", $merge_id);
        $update_stmt->execute();
        
        // Commit the transaction
        $connection->commit();
        
        $success_message = "Merged payee '$merge_name' has been reset and is now visible in the pending payments list.";
        
        // Refresh the page to update the list
        header("Location: processed_merged_payees.php?success=" . urlencode($success_message));
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $connection->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}

// Set success message from URL parameter
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// Count the number of processed merged payees
$total_processed = mysqli_num_rows($processed_result);
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main id="main" class="main">
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <div>
            <h1>Processed Merged Payees</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="completed_payments.php">Completed Payments</a></li>
                    <li class="breadcrumb-item active">Processed Merged Payees</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-md-block">
            <a href="pending_payments.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-check me-1"></i> Pending Payments
            </a>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <i class="bi bi-people-fill me-2 text-primary"></i> Processed Merged Payee Groups
                                </h5>
                                <p class="text-muted mb-0">These merged payees have been processed in ADA payments</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="px-3 py-2 rounded-pill bg-light border me-2 text-center">
                                    <span class="fw-bold text-primary fs-5"><?php echo $total_processed; ?></span>
                                    <span class="d-block small text-muted">Total Groups</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#infoPanel">
                                    <i class="bi bi-info-circle me-1"></i> Info
                                </button>
                            </div>
                        </div>
                        
                        <div class="collapse mb-4" id="infoPanel">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-lightbulb me-1"></i> About Processed Merged Payees</h6>
                                    <p class="card-text mb-0">These merged payee groups have been processed in ADA payments and are no longer showing in the pending payments list. You can reset a processed group to make it available in the pending payments list again without affecting existing payments.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="datatable-main table table-hover align-middle">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Merge Name</th>
                                        <th>Type</th>
                                        <th>Total DVs</th>
                                        <th>Total Amount</th>
                                        <th>Payment Date</th>
                                        <th>Created By</th>
                                        <th width="12%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($total_processed > 0): ?>
                                    <?php mysqli_data_seek($processed_result, 0); // Reset result pointer ?>
                                    <?php while ($row = mysqli_fetch_assoc($processed_result)): ?>
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-2">
                                                    <div class="avatar avatar-sm bg-light rounded-circle p-1">
                                                        <i class="bi bi-people-fill text-primary"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="fw-medium text-primary"><?php echo htmlspecialchars($row['merge_name']); ?></span>
                                                    <?php if (!empty($row['description'])): ?>
                                                    <small class="d-block text-muted text-truncate" style="max-width: 250px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($row['description']); ?>"><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . (strlen($row['description']) > 50 ? '...' : ''); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['payee_type'] == 'Internal' ? 'info-subtle text-info' : 'warning-subtle text-warning'; ?> rounded-pill px-3 py-2">
                                                <?php echo $row['payee_type']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-text text-secondary me-1"></i>
                                                <span><?php echo $row['total_dvs']; ?> vouchers</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-currency-exchange text-success me-1"></i>
                                                <span class="fw-bold text-success">₱<?php echo number_format($row['total_amount'], 2); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar-event text-secondary me-1"></i>
                                                <?php echo !empty($row['first_payment_date']) ? date('M d, Y', strtotime($row['first_payment_date'])) : 'Not processed'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-person text-secondary me-1"></i>
                                                <?php echo htmlspecialchars($row['created_by']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['merge_id']; ?>" title="View Details">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal<?php echo $row['merge_id']; ?>" title="Reset to Pending">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Details Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $row['merge_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-people-fill me-2"></i>
                                                        Merged Payee Group Details
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                                        <h5 class="text-primary mb-0"><?php echo htmlspecialchars($row['merge_name']); ?></h5>
                                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                                            <i class="bi bi-check-circle me-1"></i> Processed
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="row g-4 mb-4">
                                                        <div class="col-md-6">
                                                            <div class="card bg-info bg-opacity-10 border-0 h-100">
                                                                <div class="card-body p-3">
                                                                    <h6 class="card-title text-info mb-3">
                                                                        <i class="bi bi-info-circle me-1"></i> Group Information
                                                                    </h6>
                                                                    <ul class="list-group list-group-flush bg-transparent">
                                                                        <li class="list-group-item bg-transparent px-0 py-2 border-secondary-subtle d-flex justify-content-between">
                                                                            <span class="text-muted">Type:</span>
                                                                            <span class="badge bg-<?php echo $row['payee_type'] == 'Internal' ? 'info' : 'warning'; ?> ms-2"><?php echo $row['payee_type']; ?></span>
                                                                        </li>
                                                                        <li class="list-group-item bg-transparent px-0 py-2 border-secondary-subtle d-flex justify-content-between">
                                                                            <span class="text-muted">Created By:</span>
                                                                            <span class="fw-medium"><?php echo htmlspecialchars($row['created_by']); ?></span>
                                                                        </li>
                                                                        <li class="list-group-item bg-transparent px-0 py-2 border-secondary-subtle d-flex justify-content-between">
                                                                            <span class="text-muted">Created On:</span>
                                                                            <span class="fw-medium"><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card bg-success bg-opacity-10 border-0 h-100">
                                                                <div class="card-body p-3">
                                                                    <h6 class="card-title text-success mb-3">
                                                                        <i class="bi bi-cash-coin me-1"></i> Payment Information
                                                                    </h6>
                                                                    <ul class="list-group list-group-flush bg-transparent">
                                                                        <li class="list-group-item bg-transparent px-0 py-2 border-secondary-subtle d-flex justify-content-between">
                                                                            <span class="text-muted">Total Amount:</span>
                                                                            <span class="fw-bold text-success fs-5">₱<?php echo number_format($row['total_amount'], 2); ?></span>
                                                                        </li>
                                                                        <li class="list-group-item bg-transparent px-0 py-2 border-secondary-subtle d-flex justify-content-between">
                                                                            <span class="text-muted">Vouchers:</span>
                                                                            <span class="fw-medium"><?php echo $row['total_dvs']; ?></span>
                                                                        </li>
                                                                        <li class="list-group-item bg-transparent px-0 py-2 border-secondary-subtle d-flex justify-content-between">
                                                                            <span class="text-muted">Payment Date:</span>
                                                                            <span class="fw-medium"><?php echo !empty($row['first_payment_date']) ? date('M d, Y', strtotime($row['first_payment_date'])) : 'Not processed'; ?></span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($row['description'])): ?>
                                                    <div class="card border-primary border-opacity-25 mb-4">
                                                        <div class="card-header bg-primary bg-opacity-10 py-3">
                                                            <h6 class="card-title mb-0 text-primary">
                                                                <i class="bi bi-journal-text me-1"></i> Description
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="card border-primary border-opacity-25 mb-0">
                                                        <div class="card-header bg-primary bg-opacity-10 py-3">
                                                            <h6 class="card-title mb-0 text-primary">
                                                                <i class="bi bi-file-earmark-text me-1"></i> Included Vouchers
                                                            </h6>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <?php
                                                            // Get the DVs for this merged group
                                                            $dvs_query = "SELECT 
                                                                        d.dv_id, d.dv_no, d.net_amount,
                                                                        p.payee_name, p.bank_acc_no,
                                                                        o.ors_no, o.purpose,
                                                                        pay.reference_no, pay.payment_date
                                                                    FROM 
                                                                        merged_payee_items mpi
                                                                    JOIN 
                                                                        dv d ON mpi.dv_id = d.dv_id
                                                                    JOIN
                                                                        ors o ON d.ors_id = o.ors_id
                                                                    JOIN
                                                                        payee p ON o.payee_id = p.payee_id
                                                                    LEFT JOIN
                                                                        payment pay ON pay.dv_id = d.dv_id AND pay.payment_type = 'ADA'
                                                                    WHERE 
                                                                        mpi.merge_id = ?
                                                                    ORDER BY 
                                                                        p.payee_name, pay.payment_date DESC";
                                                            
                                                            $dvs_stmt = $connection->prepare($dvs_query);
                                                            $dvs_stmt->bind_param("i", $row['merge_id']);
                                                            $dvs_stmt->execute();
                                                            $dvs_result = $dvs_stmt->get_result();
                                                            $dvs_count = $dvs_result->num_rows;
                                                            ?>
                                                            
                                                            <div class="table-responsive">
                                                                <table class="table table-hover mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>DV No</th>
                                                                            <th>Payee</th>
                                                                            <th>Reference No</th>
                                                                            <th>Payment Date</th>
                                                                            <th class="text-end">Amount</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if ($dvs_count > 0): ?>
                                                                        <?php while ($dv = $dvs_result->fetch_assoc()): ?>
                                                                        <tr>
                                                                            <td>
                                                                                <span class="badge bg-light text-dark border">
                                                                                    <i class="bi bi-file-earmark-text-fill me-1 text-primary"></i>
                                                                                    <?php echo $dv['dv_no']; ?>
                                                                                </span>
                                                                            </td>
                                                                            <td><?php echo htmlspecialchars($dv['payee_name']); ?></td>
                                                                            <td>
                                                                                <?php if (!empty($dv['reference_no'])): ?>
                                                                                <span class="badge bg-light text-primary border">
                                                                                    <i class="bi bi-hash me-1"></i>
                                                                                    <?php echo $dv['reference_no']; ?>
                                                                                </span>
                                                                                <?php else: ?>
                                                                                <span class="text-muted">N/A</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td>
                                                                                <?php if (!empty($dv['payment_date'])): ?>
                                                                                <span class="badge bg-success-subtle text-success rounded-pill">
                                                                                    <i class="bi bi-calendar-check me-1"></i>
                                                                                    <?php echo date('M d, Y', strtotime($dv['payment_date'])); ?>
                                                                                </span>
                                                                                <?php else: ?>
                                                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">Not processed</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td class="text-end text-success fw-medium">₱<?php echo number_format($dv['net_amount'], 2); ?></td>
                                                                        </tr>
                                                                        <?php endwhile; ?>
                                                                        <tr class="table-light fw-bold">
                                                                            <td colspan="4" class="text-end">Total Amount:</td>
                                                                            <td class="text-end text-success fs-5">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                                                        </tr>
                                                                        <?php else: ?>
                                                                        <tr>
                                                                            <td colspan="5" class="text-center py-4">
                                                                                <div class="d-flex flex-column align-items-center">
                                                                                    <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                                                                                    <p class="mb-0">No DVs found for this merged group.</p>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endif; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                        <i class="bi bi-x-circle me-1"></i> Close
                                                    </button>
                                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetModal<?php echo $row['merge_id']; ?>" onclick="$('#viewModal<?php echo $row['merge_id']; ?>').modal('hide')">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Pending
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Reset Confirmation Modal -->
                                    <div class="modal fade" id="resetModal<?php echo $row['merge_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning text-white">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                                        Reset Merged Payee
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body p-4">
                                                        <div class="alert alert-warning d-flex mb-4" role="alert">
                                                            <div class="me-3">
                                                                <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="alert-heading">Confirm Reset</h5>
                                                                <p class="mb-0">You are about to reset the processed status of <strong><?php echo htmlspecialchars($row['merge_name']); ?></strong>.</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="card bg-light border-0 mb-0">
                                                            <div class="card-body">
                                                                <h6 class="card-title fw-medium mb-3">
                                                                    <i class="bi bi-info-circle me-1"></i> What happens when you reset?
                                                                </h6>
                                                                <ul class="mb-0 ps-3">
                                                                    <li class="mb-2">This merged payee will become visible again in the pending payments list</li>
                                                                    <li class="mb-2">It can be used in new ADA payments</li>
                                                                    <li class="mb-0 fw-medium">Any existing ADA payments will remain intact</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        
                                                        <input type="hidden" name="reset_merge_id" value="<?php echo $row['merge_id']; ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                            <i class="bi bi-x-circle me-1"></i> Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-warning">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Pending
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state py-5 text-center">
                                                <div class="avatar avatar-lg bg-light rounded-circle p-3 mb-3 mx-auto">
                                                    <i class="bi bi-people fs-1 text-secondary"></i>
                                                </div>
                                                <h5 class="mb-2">No processed merged payees found</h5>
                                                <p class="text-muted mb-4">There are no merged payee groups that have been processed in ADA payments yet.</p>
                                                <a href="pending_payments.php" class="btn btn-primary">
                                                    <i class="bi bi-arrow-left me-1"></i> Go to Pending Payments
                                                </a>
                                            </div>
                                        </td>
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

</main><!-- End #main -->

<?php include 'includes/footer.php'; ?>

<style>
    /* Enhanced Table and Layout Styling */
    .pagetitle h1 {
        font-size: 1.8rem;
        font-weight: 600;
        color: #012970;
    }
    
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
    }
    
    .avatar-lg {
        width: 70px;
        height: 70px;
    }
    
    .avatar-sm {
        width: 32px;
        height: 32px;
    }
    
    .bg-info-subtle {
        background-color: rgba(13, 202, 240, 0.18) !important;
    }
    
    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.18) !important;
    }
    
    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.18) !important;
    }
</style>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        try {
            // Initialize DataTable
            const table = document.querySelector('.datatable');
            if (table) {
                const dataTable = new simpleDatatables.DataTable(table, {
                    perPageSelect: [10, 20, 50, 100],
                    columns: [
                        { select: 4, sort: "desc", type: "date", format: "MMM DD, YYYY" }
                    ]
                });
            }
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        } catch (error) {
            console.error('Error initializing components:', error);
        }
    });
</script> 
