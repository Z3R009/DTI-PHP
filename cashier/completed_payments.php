<?php
include '../DBConnection.php';

// Process status update (mark as completed or return to chief accountant)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $payment_id = $_POST['payment_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE payment SET status = '$status' WHERE payment_id = '$payment_id'";
    
    if (mysqli_query($connection, $update_query)) {
        // Success message
        echo "<script>alert('Payment status updated successfully!'); window.location.href='completed_payments.php';</script>";
    } else {
        // Error message
        echo "<script>alert('Error updating status: " . mysqli_error($connection) . "');</script>";
    }
}

// Get all payments that are pending or completed
$payments_query = "SELECT p.*, d.dv_no, o.ors_no, pa.payee_name
                  FROM payment p
                  JOIN dv d ON p.dv_id = d.dv_id
                  JOIN ors o ON d.ors_id = o.ors_id
                  JOIN payee pa ON o.payee_id = pa.payee_id
                  WHERE p.status IN ('Pending', 'Completed')
                  ORDER BY p.created_at DESC";
$payments_result = mysqli_query($connection, $payments_query);
?>

<!DOCTYPE html>
<html lang="en">

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
                        <h5 class="card-title">Payment Records</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>DV No</th>
                                        <th>ORS No</th>
                                        <th>Payee</th>
                                        <th>Payment Type</th>
                                        <th>Reference No</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($payments_result)) : ?>
                                    <tr>
                                        <td><?php echo $row['dv_no']; ?></td>
                                        <td><?php echo $row['ors_no']; ?></td>
                                        <td><?php echo $row['payee_name']; ?></td>
                                        <td><?php echo $row['payment_type']; ?></td>
                                        <td><?php echo $row['reference_no']; ?></td>
                                        <td>PHP <?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'Completed') : ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php else : ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if ($row['status'] == 'Pending') : ?>
                                                    <li>
                                                        <form method="POST" action="" style="display: inline;">
                                                            <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
                                                            <input type="hidden" name="status" value="Completed">
                                                            <button type="submit" name="update_status" class="dropdown-item">
                                                                <i class="bi bi-check-circle text-success"></i> Mark as Completed
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <form method="POST" action="" style="display: inline;">
                                                            <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
                                                            <input type="hidden" name="status" value="Returned">
                                                            <button type="submit" name="update_status" class="dropdown-item">
                                                                <i class="bi bi-arrow-return-left text-primary"></i> Return to Chief Accountant
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['payment_id']; ?>">
                                                            <i class="bi bi-eye text-info"></i> View Details
                                                        </button>
                                                    </li>
                                                </ul>
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
                                                            <p><strong>Status:</strong> 
                                                                <?php if ($row['status'] == 'Completed') : ?>
                                                                    <span class="badge bg-success">Completed</span>
                                                                <?php else : ?>
                                                                    <span class="badge bg-warning">Pending</span>
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
                                        <td colspan="9" class="text-center">No payment records found</td>
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