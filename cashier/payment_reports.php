<?php
include '../DBConnection.php';

// Initialize filter variables
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); // Default to first day of current month
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'); // Default to current date
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$payment_type = isset($_GET['payment_type']) ? $_GET['payment_type'] : 'all';

// Build the query with filters
$where_conditions = [];
$where_conditions[] = "p.payment_date BETWEEN '$from_date' AND '$to_date'";

if ($status != 'all') {
    $where_conditions[] = "p.status = '$status'";
}

if ($payment_type != 'all') {
    $where_conditions[] = "p.payment_type = '$payment_type'";
}

$where_clause = implode(' AND ', $where_conditions);

$report_query = "SELECT p.*, d.dv_no, o.ors_no, pa.payee_name
                FROM payment p
                JOIN dv d ON p.dv_id = d.dv_id
                JOIN ors o ON d.ors_id = o.ors_id
                JOIN payee pa ON o.payee_id = pa.payee_id
                WHERE $where_clause
                ORDER BY p.payment_date ASC";
$report_result = mysqli_query($connection, $report_query);

// Calculate totals
$total_query = "SELECT SUM(p.amount) as total_amount, 
                COUNT(CASE WHEN p.payment_type = 'Check' THEN 1 END) as check_count,
                COUNT(CASE WHEN p.payment_type = 'ADA' THEN 1 END) as ada_count,
                SUM(CASE WHEN p.payment_type = 'Check' THEN p.amount ELSE 0 END) as check_amount,
                SUM(CASE WHEN p.payment_type = 'ADA' THEN p.amount ELSE 0 END) as ada_amount
                FROM payment p
                WHERE $where_clause";
$total_result = mysqli_query($connection, $total_query);
$totals = mysqli_fetch_assoc($total_result);
?>

<!DOCTYPE html>
<html lang="en">

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
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Generate Report</h5>
                        
                        <!-- Filter Form -->
                        <form method="GET" action="" class="mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="from_date" id="from_date" value="<?php echo $from_date; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="to_date" id="to_date" value="<?php echo $to_date; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All</option>
                                        <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Returned" <?php echo $status == 'Returned' ? 'selected' : ''; ?>>Returned</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="payment_type" class="form-label">Payment Type</label>
                                    <select class="form-select" name="payment_type" id="payment_type">
                                        <option value="all" <?php echo $payment_type == 'all' ? 'selected' : ''; ?>>All</option>
                                        <option value="Check" <?php echo $payment_type == 'Check' ? 'selected' : ''; ?>>Check</option>
                                        <option value="ADA" <?php echo $payment_type == 'ADA' ? 'selected' : ''; ?>>ADA</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Report Summary -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Amount</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-currency-dollar"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>PHP <?php echo number_format($totals['total_amount'] ?? 0, 2); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card info-card revenue-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Check Payments</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-credit-card"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?php echo $totals['check_count'] ?? 0; ?></h6>
                                                <span class="text-muted small pt-2">PHP <?php echo number_format($totals['check_amount'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card info-card customers-card">
                                    <div class="card-body">
                                        <h5 class="card-title">ADA Payments</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-bank"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6><?php echo $totals['ada_count'] ?? 0; ?></h6>
                                                <span class="text-muted small pt-2">PHP <?php echo number_format($totals['ada_amount'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-grid gap-2">
                                    <a href="generate_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&status=<?php echo $status; ?>&payment_type=<?php echo $payment_type; ?>" class="btn btn-success" target="_blank">
                                        <i class="bi bi-printer"></i> Print Report
                                    </a>
                                    <a href="export_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&status=<?php echo $status; ?>&payment_type=<?php echo $payment_type; ?>" class="btn btn-primary">
                                        <i class="bi bi-file-excel"></i> Export to Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Report Table -->
                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>DV No</th>
                                        <th>ORS No</th>
                                        <th>Payee</th>
                                        <th>Payment Type</th>
                                        <th>Reference No</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($report_result)) : ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                        <td><?php echo $row['dv_no']; ?></td>
                                        <td><?php echo $row['ors_no']; ?></td>
                                        <td><?php echo $row['payee_name']; ?></td>
                                        <td><?php echo $row['payment_type']; ?></td>
                                        <td><?php echo $row['reference_no']; ?></td>
                                        <td>PHP <?php echo number_format($row['amount'], 2); ?></td>
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
                                    <?php if (mysqli_num_rows($report_result) == 0) : ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No records found for the selected filters</td>
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