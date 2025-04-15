<?php
include '../database/db_connection.php';
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/alert.php';

// Initialize filter variables
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Query accounts
$account_query = "SELECT * FROM account_name ORDER BY account_name ASC";
$account_result = mysqli_query($conn, $account_query);

// Base query for payments
$payment_query = "SELECT p.*, a.account_name, a.account_number, d.cash_allotment, d.balances 
                FROM payments p 
                INNER JOIN draft_project d ON p.draft_id = d.draft_id 
                INNER JOIN account_name a ON d.account_id = a.account_id 
                WHERE 1=1";

// Add filters
if (!empty($account_id)) {
    $payment_query .= " AND d.account_id = '$account_id'";
}
if (!empty($from_date)) {
    $payment_query .= " AND DATE(p.date_created) >= '$from_date'";
}
if (!empty($to_date)) {
    $payment_query .= " AND DATE(p.date_created) <= '$to_date'";
}

$payment_query .= " ORDER BY p.date_created DESC";
$payment_result = mysqli_query($conn, $payment_query);

// Get account details if an account is selected
$account_name = '';
$account_number = '';
$cash_allotment = 0;
$current_balance = 0;

if (!empty($account_id)) {
    $account_details_query = "SELECT a.*, d.cash_allotment, d.balances 
                            FROM account_name a 
                            LEFT JOIN draft_project d ON a.account_id = d.account_id 
                            WHERE a.account_id = '$account_id' 
                            ORDER BY d.created_at DESC LIMIT 1";
    $account_details_result = mysqli_query($conn, $account_details_query);
    
    if (mysqli_num_rows($account_details_result) > 0) {
        $account_details = mysqli_fetch_assoc($account_details_result);
        $account_name = $account_details['account_name'];
        $account_number = $account_details['account_number'];
        $cash_allotment = $account_details['cash_allotment'] ?? 0;
        $current_balance = $account_details['balances'] ?? 0;
    }
}

// Calculate transaction totals
$total_check = 0;
$total_ada = 0;
$total_payments = 0;

if (mysqli_num_rows($payment_result) > 0) {
    mysqli_data_seek($payment_result, 0); // Reset result pointer
    while ($row = mysqli_fetch_assoc($payment_result)) {
        if ($row['payment_type'] == 'Check') {
            $total_check += $row['amount'];
        } else if ($row['payment_type'] == 'ADA') {
            $total_ada += $row['amount'];
        }
        $total_payments += $row['amount'];
    }
    mysqli_data_seek($payment_result, 0); // Reset result pointer for display
}

// Calculate starting balance
$starting_balance = $cash_allotment;
if (!empty($account_id) && !empty($from_date)) {
    $prior_payments_query = "SELECT SUM(p.amount) as total_prior 
                            FROM payments p 
                            INNER JOIN draft_project d ON p.draft_id = d.draft_id 
                            WHERE d.account_id = '$account_id' 
                            AND DATE(p.date_created) < '$from_date'";
    $prior_payments_result = mysqli_query($conn, $prior_payments_query);
    $prior_payments = mysqli_fetch_assoc($prior_payments_result);
    $total_prior = $prior_payments['total_prior'] ?? 0;
    $starting_balance = $cash_allotment - $total_prior;
}

// Calculate ending balance
$ending_balance = $starting_balance - $total_payments;
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Cash Budget Report</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Cash Budget Report</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filter Cash Budget</h5>
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="account_id" class="form-label">Account</label>
                                <select name="account_id" id="account_id" class="form-select">
                                    <option value="">Select Account</option>
                                    <?php while ($account = mysqli_fetch_assoc($account_result)) : ?>
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
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($account_id)) : ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Budget Summary</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <strong>Account Name:</strong> <?php echo $account_name; ?><br>
                                <strong>Account Number:</strong> <?php echo $account_number; ?><br>
                                <strong>Cash Allotment:</strong> ₱<?php echo number_format($cash_allotment, 2); ?><br>
                                <strong>Current Balance:</strong> ₱<?php echo number_format($current_balance, 2); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Starting Balance (<?php echo date('M d, Y', strtotime($from_date)); ?>):</strong> ₱<?php echo number_format($starting_balance, 2); ?><br>
                                <strong>Total Check Payments:</strong> ₱<?php echo number_format($total_check, 2); ?><br>
                                <strong>Total ADA Payments:</strong> ₱<?php echo number_format($total_ada, 2); ?><br>
                                <strong>Ending Balance (<?php echo date('M d, Y', strtotime($to_date)); ?>):</strong> ₱<?php echo number_format($ending_balance, 2); ?>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="print_budget_report.php?account_id=<?php echo $account_id; ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" target="_blank" class="btn btn-secondary me-2">
                                <i class="bi bi-printer"></i> Print
                            </a>
                            <a href="export_budget_report.php?account_id=<?php echo $account_id; ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Export to Excel
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transaction Details</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>DV #</th>
                                        <th>Payee</th>
                                        <th>Particular</th>
                                        <th>Payment Type</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($payment_result) > 0) : ?>
                                        <?php while ($payment = mysqli_fetch_assoc($payment_result)) : ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($payment['date_created'])); ?></td>
                                                <td><?php echo $payment['dv_number']; ?></td>
                                                <td><?php echo $payment['payee']; ?></td>
                                                <td><?php echo $payment['particular']; ?></td>
                                                <td><?php echo $payment['payment_type']; ?></td>
                                                <td>
                                                    <?php 
                                                    if ($payment['status'] == 0) {
                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                    } elseif ($payment['status'] == 1) {
                                                        echo '<span class="badge bg-success">Processed</span>';
                                                    } elseif ($payment['status'] == 2) {
                                                        echo '<span class="badge bg-danger">Rejected</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-end">₱<?php echo number_format($payment['amount'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No transactions found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6" class="text-end">Total Transactions:</th>
                                        <th class="text-end">₱<?php echo number_format($total_payments, 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <?php else : ?>
                <div class="card">
                    <div class="card-body text-center p-5">
                        <h4>Please select an account to view the budget report</h4>
                        <p class="text-muted">Use the filter above to select an account and date range</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> 