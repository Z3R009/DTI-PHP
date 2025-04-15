<?php
include '../database/db_connection.php';

// Initialize filter variables
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

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
    $stmt = $connection->prepare($account_query);
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
    $stmt = $connection->prepare($draft_query);
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
        $stmt = $connection->prepare($previous_payments_query);
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
    $stmt = $connection->prepare($transactions_query);
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
    $stmt = $connection->prepare($total_query);
    $stmt->bind_param("iss", $account_id, $from_date, $to_date);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_amount = $total_row['total'];
    
    // Calculate ending balance
    $ending_balance = $starting_balance - $total_amount;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Report - <?php echo $account_details ? $account_details['account_name'] : 'Print'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 5px 0;
            font-size: 18px;
        }
        .report-header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .account-details {
            margin-bottom: 20px;
        }
        .budget-summary {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .summary-item {
            width: 30%;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            width: 30%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
            @page {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Print Report</button>
        <button onclick="window.close()">Close</button>
    </div>
    
    <?php if ($account_details): ?>
    <div class="report-header">
        <h1>DEPARTMENT OF TRADE AND INDUSTRY</h1>
        <p>Region XII</p>
        <h2>BUDGET REPORT</h2>
        <p>Period: <?php echo date('F d, Y', strtotime($from_date)); ?> to <?php echo date('F d, Y', strtotime($to_date)); ?></p>
    </div>
    
    <div class="account-details">
        <p><strong>Account Name:</strong> <?php echo $account_details['account_name']; ?></p>
        <p><strong>Account Number:</strong> <?php echo $account_details['account_number']; ?></p>
        <p><strong>Type:</strong> <?php echo $account_details['type']; ?></p>
        <p><strong>Date Generated:</strong> <?php echo date('F d, Y'); ?></p>
    </div>
    
    <div class="budget-summary">
        <div class="summary-item">
            <h3>Starting Balance</h3>
            <p>PHP <?php echo number_format($starting_balance, 2); ?></p>
        </div>
        <div class="summary-item">
            <h3>Total Spent</h3>
            <p>PHP <?php echo number_format($total_amount, 2); ?></p>
        </div>
        <div class="summary-item">
            <h3>Ending Balance</h3>
            <p>PHP <?php echo number_format($ending_balance, 2); ?></p>
        </div>
    </div>
    
    <h3>Transaction Details</h3>
    <table>
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
            if ($transactions && mysqli_num_rows($transactions) > 0): 
                while ($row = mysqli_fetch_assoc($transactions)): 
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
            else: 
            ?>
            <tr>
                <td colspan="8" style="text-align:center;">No transactions found for the selected period</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-end">Total:</th>
                <th class="text-end">PHP <?php echo number_format($total_amount, 2); ?></th>
            </tr>
        </tfoot>
    </table>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Prepared by</div>
            <p>Cashier</p>
        </div>
        <div class="signature-box">
            <div class="signature-line">Verified by</div>
            <p>Chief Accountant</p>
        </div>
        <div class="signature-box">
            <div class="signature-line">Approved by</div>
            <p>Regional Director</p>
        </div>
    </div>
    <?php else: ?>
    <div style="text-align:center; margin-top:50px;">
        <h3>No account selected or data not found</h3>
        <p>Please go back and select an account to generate the report.</p>
    </div>
    <?php endif; ?>
</body>
</html> 