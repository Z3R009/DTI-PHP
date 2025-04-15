<?php
include '../database/db_connection.php';
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/alert.php';

// Initialize filter variables
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get all accounts for dropdown
$accounts_query = "SELECT account_id, account_name, account_number, type FROM account_name ORDER BY account_name";
$accounts_result = mysqli_query($conn, $accounts_query);

// Initialize variables for results
$account_details = null;
$transactions = null;
$starting_balance = 0;
$total_amount = 0;
$ending_balance = 0;

// If account is selected, fetch its details and related transactions
if (!empty($account_id)) {
    // Fetch account details
    $account_query = "SELECT * FROM account_name WHERE account_id = ?";
    $stmt = $conn->prepare($account_query);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $account_result = $stmt->get_result();
    $account_details = $account_result->fetch_assoc();
    
    // Get draft project (budget allocation) for this account
    $draft_query = "SELECT * FROM draft_project 
                   WHERE account_id = ? 
                   AND created_at <= ?
                   ORDER BY created_at DESC
                   LIMIT 1";
    $stmt = $conn->prepare($draft_query);
    $stmt->bind_param("is", $account_id, $to_date);
    $stmt->execute();
    $draft_result = $stmt->get_result();
    $draft_project = $draft_result->fetch_assoc();
    
    // Calculate starting balance from the draft project
    if ($draft_project) {
        $starting_balance = $draft_project['cash_allotment'];
        
        // Get all payments before from_date (to calculate starting balance)
        $previous_payments_query = "SELECT COALESCE(SUM(p.amount), 0) as total
                                   FROM payment p
                                   JOIN dv d ON p.dv_id = d.dv_id
                                   WHERE d.account_id = ?
                                   AND p.payment_date < ?
                                   AND p.status = 'Completed'";
        $stmt = $conn->prepare($previous_payments_query);
        $stmt->bind_param("is", $account_id, $from_date);
        $stmt->execute();
        $previous_result = $stmt->get_result();
        $previous_payments = $previous_result->fetch_assoc();
        
        // Adjust starting balance by subtracting previous payments
        $starting_balance = $draft_project['cash_allotment'] - ($previous_payments['total'] ?? 0);
    }
    
    // Get all transactions within the date range
    $transactions_query = "SELECT p.payment_id, p.payment_date, p.payment_type, p.reference_no, 
                          p.amount, p.remarks, p.status, d.dv_no, 
                          o.ors_no, pa.payee_name, o.purpose
                          FROM payment p
                          JOIN dv d ON p.dv_id = d.dv_id
                          JOIN ors o ON d.ors_id = o.ors_id
                          JOIN payee pa ON o.payee_id = pa.payee_id
                          WHERE d.account_id = ?
                          AND p.payment_date BETWEEN ? AND ?
                          AND p.status = 'Completed'
                          ORDER BY p.payment_date ASC";
    $stmt = $conn->prepare($transactions_query);
    $stmt->bind_param("iss", $account_id, $from_date, $to_date);
    $stmt->execute();
    $transactions = $stmt->get_result();
    
    // Calculate total amount spent
    $total_query = "SELECT COALESCE(SUM(p.amount), 0) as total
                   FROM payment p
                   JOIN dv d ON p.dv_id = d.dv_id
                   WHERE d.account_id = ?
                   AND p.payment_date BETWEEN ? AND ?
                   AND p.status = 'Completed'";
    $stmt = $conn->prepare($total_query);
    $stmt->bind_param("iss", $account_id, $from_date, $to_date);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_amount = $total_row['total'];
    
    // Calculate ending balance
    $ending_balance = $starting_balance - $total_amount;
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Budget Report</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Budget Report</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Generate Budget Report</h5>
                        
                        <!-- Filter Form -->
                        <form method="GET" action="" class="mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="account_id" class="form-label">Account</label>
                                    <select class="form-select" name="account_id" id="account_id" required>
                                        <option value="">Select Account</option>
                                        <?php while ($account = mysqli_fetch_assoc($accounts_result)) : ?>
                                            <option value="<?php echo $account['account_id']; ?>" <?php echo ($account_id == $account['account_id']) ? 'selected' : ''; ?>>
                                                <?php echo $account['account_name'] . ' (' . $account['account_number'] . ')'; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="date" class="form-control" name="from_date" id="from_date" value="<?php echo $from_date; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="date" class="form-control" name="to_date" id="to_date" value="<?php echo $to_date; ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                                </div>
                            </div>
                        </form>
                        
                        <?php if ($account_details) : ?>
                        <!-- Budget Details -->
                        <div class="budget-details mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4><?php echo $account_details['account_name']; ?></h4>
                                    <p><strong>Account Number:</strong> <?php echo $account_details['account_number']; ?></p>
                                    <p><strong>Type:</strong> <?php echo $account_details['type']; ?></p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>Report Period:</strong> <?php echo date('M d, Y', strtotime($from_date)); ?> to <?php echo date('M d, Y', strtotime($to_date)); ?></p>
                                    <p><strong>Date Generated:</strong> <?php echo date('M d, Y'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Budget Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card info-card revenue-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Starting Balance</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-currency-dollar"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>PHP <?php echo number_format($starting_balance, 2); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Spent This Period</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-cart"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>PHP <?php echo number_format($total_amount, 2); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card info-card customers-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Ending Balance</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-bank"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>PHP <?php echo number_format($ending_balance, 2); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Export Buttons -->
                        <div class="d-flex justify-content-end mb-3">
                            <a href="print_budget_report.php?account_id=<?php echo $account_id; ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" class="btn btn-success me-2" target="_blank">
                                <i class="bi bi-printer"></i> Print Report
                            </a>
                            <a href="export_budget_report.php?account_id=<?php echo $account_id; ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" class="btn btn-primary">
                                <i class="bi bi-file-excel"></i> Export to Excel
                            </a>
                        </div>
                        
                        <!-- Transactions Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered datatable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>DV No</th>
                                        <th>ORS No</th>
                                        <th>Payee</th>
                                        <th>Purpose</th>
                                        <th>Payment Type</th>
                                        <th>Reference No</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($transactions && mysqli_num_rows($transactions) > 0) {
                                        while ($row = mysqli_fetch_assoc($transactions)) : 
                                    ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                        <td><?php echo $row['dv_no']; ?></td>
                                        <td><?php echo $row['ors_no']; ?></td>
                                        <td><?php echo $row['payee_name']; ?></td>
                                        <td><?php echo substr($row['purpose'], 0, 50) . (strlen($row['purpose']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo $row['payment_type']; ?></td>
                                        <td><?php echo $row['reference_no']; ?></td>
                                        <td class="text-end">PHP <?php echo number_format($row['amount'], 2); ?></td>
                                    </tr>
                                    <?php 
                                        endwhile; 
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No transactions found for the selected period</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-end">Total:</th>
                                        <th class="text-end">PHP <?php echo number_format($total_amount, 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php else: ?>
                            <?php if (isset($_GET['account_id'])): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    No budget data found for the selected account. Please check your selection or try a different account.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Please select an account to generate the budget report.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize datatable with custom options
    const dataTable = new simpleDatatables.DataTable(".datatable", {
        perPageSelect: [10, 20, 50, 100],
        perPage: 10,
        searchable: true,
        sortable: true,
        fixedHeight: false
    });
});
</script>

</body>
</html> 