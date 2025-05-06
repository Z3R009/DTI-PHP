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
?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="css/table.css">

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Processed Merged Payees</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="completed_payments.php">Completed Payments</a></li>
                <li class="breadcrumb-item active">Processed Merged Payees</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-people me-1"></i> Processed Merged Payee Groups
                            <span class="text-muted ms-2 fs-6 fw-light">(These merged payees have been processed in ADA payments)</span>
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="datatable">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th>Merge Name</th>
                                        <th>Type</th>
                                        <th>Total DVs</th>
                                        <th>Total Amount</th>
                                        <th>Payment Date</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($processed_result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($processed_result)): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span class="fw-bold"><?php echo htmlspecialchars($row['merge_name']); ?></span>
                                                    <?php if (!empty($row['description'])): ?>
                                                    <small class="d-block text-muted"><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . (strlen($row['description']) > 50 ? '...' : ''); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-<?php echo $row['payee_type'] == 'Internal' ? 'info' : 'warning'; ?>"><?php echo $row['payee_type']; ?></span></td>
                                        <td><span class="badge bg-primary"><?php echo $row['total_dvs']; ?> vouchers</span></td>
                                        <td class="text-success fw-bold">PHP <?php echo number_format($row['total_amount'], 2); ?></td>
                                        <td><?php echo !empty($row['first_payment_date']) ? date('M d, Y', strtotime($row['first_payment_date'])) : 'Not processed'; ?></td>
                                        <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm rounded" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['merge_id']; ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm rounded" data-bs-toggle="modal" data-bs-target="#resetModal<?php echo $row['merge_id']; ?>">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Details Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $row['merge_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-people-fill me-2"></i>
                                                        Merged Payee Group: <?php echo htmlspecialchars($row['merge_name']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <div class="border rounded p-3 h-100 bg-light-subtle">
                                                                <h6 class="border-bottom pb-2 mb-3 text-info">Group Information</h6>
                                                                <p><strong>Group Name:</strong> <?php echo htmlspecialchars($row['merge_name']); ?></p>
                                                                <p><strong>Payee Type:</strong> <span class="badge bg-<?php echo $row['payee_type'] == 'Internal' ? 'info' : 'warning'; ?>"><?php echo $row['payee_type']; ?></span></p>
                                                                <p><strong>Total DVs:</strong> <?php echo $row['total_dvs']; ?></p>
                                                                <p><strong>Created By:</strong> <?php echo htmlspecialchars($row['created_by']); ?></p>
                                                                <p><strong>Created At:</strong> <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="border rounded p-3 h-100 bg-light-subtle">
                                                                <h6 class="border-bottom pb-2 mb-3 text-success">Payment Information</h6>
                                                                <p><strong>Total Amount:</strong> <span class="fw-bold text-success">PHP <?php echo number_format($row['total_amount'], 2); ?></span></p>
                                                                <p><strong>First Payment Date:</strong> <?php echo !empty($row['first_payment_date']) ? date('M d, Y', strtotime($row['first_payment_date'])) : 'Not processed'; ?></p>
                                                                <p><strong>Status:</strong> <span class="badge bg-success">Processed</span></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($row['description'])): ?>
                                                    <div class="mb-3">
                                                        <h6 class="border-bottom pb-2 mb-3 text-primary">Description</h6>
                                                        <div class="border p-3 rounded bg-light">
                                                            <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <h6 class="border-bottom pb-2 mb-3 text-primary">Included DVs</h6>
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
                                                    ?>
                                                    
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover">
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
                                                                <?php if ($dvs_result->num_rows > 0): ?>
                                                                <?php while ($dv = $dvs_result->fetch_assoc()): ?>
                                                                <tr>
                                                                    <td><span class="badge bg-light text-dark"><?php echo $dv['dv_no']; ?></span></td>
                                                                    <td><?php echo htmlspecialchars($dv['payee_name']); ?></td>
                                                                    <td><span class="badge bg-light text-primary"><?php echo $dv['reference_no'] ?? 'N/A'; ?></span></td>
                                                                    <td><?php echo !empty($dv['payment_date']) ? date('M d, Y', strtotime($dv['payment_date'])) : 'Not processed'; ?></td>
                                                                    <td class="text-end text-success">PHP <?php echo number_format($dv['net_amount'], 2); ?></td>
                                                                </tr>
                                                                <?php endwhile; ?>
                                                                <?php else: ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center">No DVs found for this merged group.</td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                                                            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                                            <div>
                                                                <strong>Warning:</strong> You are about to reset the processed status of <strong><?php echo htmlspecialchars($row['merge_name']); ?></strong> merged payee group.
                                                            </div>
                                                        </div>
                                                        <p>This will make the merged payee visible again in the pending payments list, allowing it to be used in new ADA payments. Any existing ADA payments will remain intact.</p>
                                                        <p class="mb-0">Are you sure you want to continue?</p>
                                                        <input type="hidden" name="reset_merge_id" value="<?php echo $row['merge_id']; ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning">Reset Merged Payee</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state my-5 text-center">
                                                <i class="bi bi-people fs-1 text-muted mb-3"></i>
                                                <h5>No processed merged payees found</h5>
                                                <p>There are no merged payee groups that have been processed in ADA payments yet.</p>
                                                <a href="pending_payments.php" class="btn btn-sm btn-primary mt-2">
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
    /* Enhanced Table Styling */
    .table-responsive {
        overflow-x: auto;
    }
    .empty-state {
        padding: 40px 0;
    }
    .empty-state i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 20px;
    }
    .empty-state h5 {
        color: #555;
        margin-bottom: 10px;
    }
    .empty-state p {
        color: #888;
        max-width: 500px;
        margin: 0 auto;
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