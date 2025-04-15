<?php
include '../DBConnection.php';

// Count all endorsed DVs (which are pending for payment)
$pending_query = "SELECT COUNT(*) as pending_count, SUM(net_amount) as pending_amount 
                 FROM dv 
                 WHERE status = 'Endorsed' 
                 AND dv_id NOT IN (SELECT dv_id FROM payment)";
$pending_result = mysqli_query($connection, $pending_query);
$pending_data = mysqli_fetch_assoc($pending_result);
$pending_count = $pending_data['pending_count'] ?? 0;
$pending_amount = $pending_data['pending_amount'] ?? 0;

// Count all completed payments
$completed_query = "SELECT COUNT(*) as completed_count, SUM(amount) as completed_amount 
                   FROM payment 
                   WHERE status = 'Completed'";
$completed_result = mysqli_query($connection, $completed_query);
$completed_data = mysqli_fetch_assoc($completed_result);
$completed_count = $completed_data['completed_count'] ?? 0;
$completed_amount = $completed_data['completed_amount'] ?? 0;

// Count payments by type
$payment_types_query = "SELECT payment_type, COUNT(*) as type_count 
                       FROM payment 
                       GROUP BY payment_type";
$payment_types_result = mysqli_query($connection, $payment_types_query);
$payment_types = array();
while ($row = mysqli_fetch_assoc($payment_types_result)) {
    $payment_types[$row['payment_type']] = $row['type_count'];
}
$check_count = $payment_types['Check'] ?? 0;
$ada_count = $payment_types['ADA'] ?? 0;

// Recent payments
$recent_payments_query = "SELECT p.*, d.dv_no 
                         FROM payment p
                         JOIN dv d ON p.dv_id = d.dv_id
                         ORDER BY p.created_at DESC
                         LIMIT 5";
$recent_payments_result = mysqli_query($connection, $recent_payments_query);
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Cashier Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <!-- Pending Payments Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Pending Payments</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-text text-primary"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $pending_count; ?></h6>
                                        <span class="text-muted small pt-2">PHP <?php echo number_format($pending_amount, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Payments Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Completed Payments</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-check-circle text-success"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $completed_count; ?></h6>
                                        <span class="text-muted small pt-2">PHP <?php echo number_format($completed_amount, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Types Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Payment Types</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-cash text-warning"></i>
                                    </div>
                                    <div class="ps-3">
                                        <div class="d-flex gap-3">
                                            <div>
                                                <h6><?php echo $check_count; ?></h6>
                                                <span class="text-muted small pt-2">Checks</span>
                                            </div>
                                            <div>
                                                <h6><?php echo $ada_count; ?></h6>
                                                <span class="text-muted small pt-2">ADA</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Payments</h5>
                        <table class="table table-borderless datatable">
                            <thead>
                                <tr>
                                    <th>DV No</th>
                                    <th>Payment Type</th>
                                    <th>Reference No</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($recent_payments_result)) : ?>
                                <tr>
                                    <td><?php echo $row['dv_no']; ?></td>
                                    <td><?php echo $row['payment_type']; ?></td>
                                    <td><?php echo $row['reference_no']; ?></td>
                                    <td>PHP <?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'Completed') : ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($row['status'] == 'Pending') : ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">Returned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if (mysqli_num_rows($recent_payments_result) == 0) : ?>
                                <tr>
                                    <td colspan="6" class="text-center">No recent payments found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<?php include 'includes/footer.php'; ?> 