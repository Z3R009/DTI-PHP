<?php
include '../DBConnection.php';

// Get all payments that are pending or completed
$payments_query = "SELECT p.*, d.dv_id, d.dv_no, o.ors_no, pa.payee_name,
                   IF(mpi.item_id IS NOT NULL, 1, 0) AS is_merged,
                   mp.merge_id, mp.merge_name
                  FROM payment p
                  JOIN dv d ON p.dv_id = d.dv_id
                  JOIN ors o ON d.ors_id = o.ors_id
                  JOIN payee pa ON o.payee_id = pa.payee_id
                  LEFT JOIN merged_payee_items mpi ON mpi.dv_id = d.dv_id
                  LEFT JOIN merged_payees mp ON mp.merge_id = mpi.merge_id
                  WHERE p.status IN ('Pending', 'Completed')
                  ORDER BY p.created_at DESC";
$payments_result = mysqli_query($connection, $payments_query);
?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="css/table.css">

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Completed Payments</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Completed Payments</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Payment Records</h5>
                            <a href="processed_merged_payees.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-people me-1"></i> View Processed Merged Payees
                            </a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="datatable">
                                <thead>
                                    <tr class="bg-light text-dark">
                                        <th>Payee</th>
                                        <th>Payment Type</th>
                                        <th>Reference No</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Source</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($payments_result)) : ?>
                                    <tr <?php echo $row['is_merged'] == 1 ? 'class="table-info bg-opacity-25"' : ''; ?>>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div><?php echo $row['payee_name']; ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo $row['payment_type']; ?></td>
                                        <td><span class="badge bg-light text-primary"><?php echo $row['reference_no']; ?></span></td>
                                        <td class="text-success fw-bold">PHP <?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                        <td>
                                            <?php if ($row['is_merged'] == 1) : ?>
                                                <span class="badge bg-info" data-bs-toggle="tooltip" title="This DV is part of merged payee group<?php echo !empty($row['merge_name']) ? ': '.htmlspecialchars($row['merge_name']) : ''; ?>">Merged</span>
                                            <?php else : ?>
                                                <span class="badge bg-light text-dark border">Individual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-sm rounded" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['payment_id']; ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Details Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $row['payment_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Payment Details for DV #<?php echo $row['dv_no']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p><strong>DV No:</strong> <?php echo $row['dv_no']; ?></p>
                                                            <p><strong>ORS No:</strong> <?php echo $row['ors_no']; ?></p>
                                                            <p><strong>Payee:</strong> <?php echo $row['payee_name']; ?></p>
                                                            <p><strong>Payment Type:</strong> <?php echo $row['payment_type']; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Reference No:</strong> <?php echo $row['reference_no']; ?></p>
                                                            <p><strong>Amount:</strong> PHP <?php echo number_format($row['amount'], 2); ?></p>
                                                            <p><strong>Payment Date:</strong> <?php echo date('M d, Y', strtotime($row['payment_date'])); ?></p>
                                                            <p><strong>Source:</strong> 
                                                                <?php if ($row['is_merged'] == 1) : ?>
                                                                    <span class="badge bg-info">Merged</span>
                                                                    <?php if (!empty($row['merge_name'])) : ?>
                                                                        <small class="d-block mt-1 text-muted">Part of group: <?php echo htmlspecialchars($row['merge_name']); ?></small>
                                                                    <?php endif; ?>
                                                                <?php else : ?>
                                                                    <span class="badge bg-light text-dark border">Individual</span>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <p><strong>Remarks:</strong></p>
                                                        <div class="border p-3 rounded bg-light">
                                                            <?php echo empty($row['remarks']) ? 'No remarks added' : nl2br($row['remarks']); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <p><strong>Created By:</strong> <?php echo $row['created_by']; ?></p>
                                                        <p><strong>Created At:</strong> <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($payments_result) == 0) : ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <i class="bi bi-clock-history"></i>
                                                <h5>No payment records found</h5>
                                                <p>There are no completed payments available at this time.</p>
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
</style>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php include 'includes/footer.php'; ?> 