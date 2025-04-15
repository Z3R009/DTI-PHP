<?php
include '../DBConnection.php';
$payments_query = "SELECT p.*, d.dv_no, o.ors_no, pa.payee_name
                  FROM payment p
                  JOIN dv d ON p.dv_id = d.dv_id
                  JOIN ors o ON d.ors_id = o.ors_id
                  JOIN payee pa ON o.payee_id = pa.payee_id
                  WHERE p.status = 'Returned'
                  ORDER BY p.created_at DESC";
$payments_result = mysqli_query($connection, $payments_query);
$completed_query = "SELECT p.*, d.dv_no, o.ors_no, pa.payee_name
                   FROM payment p
                   JOIN dv d ON p.dv_id = d.dv_id
                   JOIN ors o ON d.ors_id = o.ors_id
                   JOIN payee pa ON o.payee_id = pa.payee_id
                   WHERE p.status = 'Completed'
                   ORDER BY p.created_at DESC";
$completed_result = mysqli_query($connection, $completed_query);

// Calculate statistics
$stats_query = "SELECT 
                COUNT(CASE WHEN p.status = 'Completed' THEN 1 END) as completed_count,
                SUM(CASE WHEN p.status = 'Completed' THEN p.amount ELSE 0 END) as completed_amount,
                COUNT(CASE WHEN p.status = 'Returned' THEN 1 END) as returned_count,
                SUM(CASE WHEN p.status = 'Returned' THEN p.amount ELSE 0 END) as returned_amount,
                COUNT(CASE WHEN p.payment_type = 'Check' THEN 1 END) as check_count,
                COUNT(CASE WHEN p.payment_type = 'ADA' THEN 1 END) as ada_count
                FROM payment p";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<title>Chief Accountant - DTI PHP</title>
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

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Payment Reports</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Payment Reports</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <!-- Payment Statistics -->
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Completed Payments</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-check-circle text-success"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['completed_count'] ?? 0; ?></h6>
                                        <span class="text-muted small pt-2">PHP <?php echo number_format($stats['completed_amount'] ?? 0, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Returned Payments</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-arrow-return-left text-warning"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['returned_count'] ?? 0; ?></h6>
                                        <span class="text-muted small pt-2">PHP <?php echo number_format($stats['returned_amount'] ?? 0, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Check Payments</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-credit-card text-primary"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['check_count'] ?? 0; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">ADA Payments</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-bank text-info"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['ada_count'] ?? 0; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Returned Payments Table -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Returned Payments</h5>
                        
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
                                        <th>Payment Date</th>
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
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['payment_id']; ?>">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
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
                                                            <p><strong>Status:</strong> <span class="badge bg-secondary">Returned</span></p>
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
                                                    <a href="print_payment.php?id=<?php echo $row['payment_id']; ?>" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-printer"></i> Print Report
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($payments_result) == 0) : ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No returned payments found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Payments Table -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Completed Payments</h5>
                        
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
                                        <th>Payment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($completed_result)) : ?>
                                    <tr>
                                        <td><?php echo $row['dv_no']; ?></td>
                                        <td><?php echo $row['ors_no']; ?></td>
                                        <td><?php echo $row['payee_name']; ?></td>
                                        <td><?php echo $row['payment_type']; ?></td>
                                        <td><?php echo $row['reference_no']; ?></td>
                                        <td>PHP <?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                        <td>
                                            <a href="print_payment.php?id=<?php echo $row['payment_id']; ?>" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="bi bi-printer"></i> Print
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($completed_result) == 0) : ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No completed payments found</td>
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

