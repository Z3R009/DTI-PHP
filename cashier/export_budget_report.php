<?php
include '../DBConnection.php';

// Initialize filter variables
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : 'all';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get the selected account's current balance
$balance = 0;
$cash_allotment = 0;
$account_name = "All Accounts";
$account_number = "";

if ($account_id != 'all') {
    $draft_query = "SELECT dp.*, an.account_name, an.account_number 
                   FROM draft_project dp 
                   JOIN account_name an ON dp.account_id = an.account_id 
                   WHERE dp.account_id = '$account_id' 
                   ORDER BY dp.created_at DESC LIMIT 1";
    $draft_result = mysqli_query($connection, $draft_query);
    
    if (mysqli_num_rows($draft_result) > 0) {
        $draft_data = mysqli_fetch_assoc($draft_result);
        $balance = $draft_data['balances'];
        $cash_allotment = $draft_data['cash_allotment'];
        $account_name = $draft_data['account_name'];
        $account_number = $draft_data['account_number'];
    } else {
        // If no record in draft_project, get account name from account_name table
        $account_query = "SELECT account_name, account_number FROM account_name WHERE account_id = '$account_id'";
        $account_result = mysqli_query($connection, $account_query);
        if (mysqli_num_rows($account_result) > 0) {
            $account_data = mysqli_fetch_assoc($account_result);
            $account_name = $account_data['account_name'];
            $account_number = $account_data['account_number'];
        }
    }
}

// Get payment transactions for the selected account or all accounts
$where_conditions = [];
$where_conditions[] = "p.payment_date BETWEEN '$from_date' AND '$to_date'";

if ($account_id != 'all') {
    $where_conditions[] = "an.account_id = '$account_id'";
}

$where_clause = implode(' AND ', $where_conditions);

$payments_query = "SELECT p.payment_id, p.payment_date, p.payment_type, p.reference_no, p.amount, 
                  p.status, d.dv_no, o.ors_no, pa.payee_name, an.account_name
                  FROM payment p
                  JOIN dv d ON p.dv_id = d.dv_id
                  JOIN ors o ON d.ors_id = o.ors_id
                  JOIN payee pa ON o.payee_id = pa.payee_id
                  JOIN account_name an ON d.account_id = an.account_id
                  WHERE $where_clause
                  ORDER BY p.payment_date ASC";
$payments_result = mysqli_query($connection, $payments_query);

// Calculate starting balance
$starting_balance = $balance;
if (mysqli_num_rows($payments_result) > 0) {
    // Calculate total payments in the period
    $total_query = "SELECT SUM(p.amount) as total_amount 
                   FROM payment p
                   JOIN dv d ON p.dv_id = d.dv_id
                   JOIN account_name an ON d.account_id = an.account_id
                   WHERE $where_clause";
    $total_result = mysqli_query($connection, $total_query);
    $total_data = mysqli_fetch_assoc($total_result);
    $total_payments = $total_data['total_amount'] ?? 0;
    
    // Add total payments to current balance to get starting balance
    $starting_balance = $balance + $total_payments;
}

// Calculate totals
$check_total = 0;
$ada_total = 0;
mysqli_data_seek($payments_result, 0); // Reset the pointer
while ($row = mysqli_fetch_assoc($payments_result)) {
    if ($row['payment_type'] == 'Check') {
        $check_total += $row['amount'];
    } else if ($row['payment_type'] == 'ADA') {
        $ada_total += $row['amount'];
    }
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="cash_budget_report_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Start Excel content
echo '<table border="1">';

// Header
echo '<tr><th colspan="5">Department of Trade and Industry</th></tr>';
echo '<tr><th colspan="5">Cash Budget Report</th></tr>';
echo '<tr><th colspan="5">Period: ' . date('F d, Y', strtotime($from_date)) . ' to ' . date('F d, Y', strtotime($to_date)) . '</th></tr>';
echo '<tr><th colspan="5">Account: ' . $account_name . ' ' . (!empty($account_number) ? "($account_number)" : "") . '</th></tr>';
echo '<tr><th colspan="5">Cash Allotment: PHP ' . number_format($cash_allotment, 2) . '</th></tr>';
echo '<tr><th colspan="5">Current Balance: PHP ' . number_format($balance, 2) . '</th></tr>';
echo '<tr><th colspan="5">Generated on: ' . date('F d, Y h:i A') . '</th></tr>';
echo '<tr><td colspan="5"></td></tr>'; // Empty row as separator

// Column headers
echo '<tr>';
echo '<th rowspan="2" style="vertical-align: middle; width: 35%;">Nature of Payment</th>';
echo '<th colspan="4" style="text-align: center;">Amount</th>';
echo '</tr>';
echo '<tr>';
echo '<th style="text-align: center; width: 15%;">Beginning Balance</th>';
echo '<th style="text-align: center; width: 15%;">Check Issued</th>';
echo '<th style="text-align: center; width: 15%;">ADA Issued</th>';
echo '<th style="text-align: center; width: 20%;">NCA/Bank Balance</th>';
echo '</tr>';

// Beginning Balance Row
echo '<tr>';
echo '<td>Beginning Balance</td>';
echo '<td style="text-align: right;">' . number_format($starting_balance, 2) . '</td>';
echo '<td></td>';
echo '<td></td>';
echo '<td style="text-align: right;">' . number_format($starting_balance, 2) . '</td>';
echo '</tr>';

// Transaction rows
mysqli_data_seek($payments_result, 0); // Reset the pointer
$running_balance = $starting_balance;

while ($row = mysqli_fetch_assoc($payments_result)) {
    $payment_amount = $row['amount'];
    $running_balance -= $payment_amount;
    
    echo '<tr>';
    echo '<td>' . $row['payee_name'] . '</td>';
    echo '<td></td>';
    
    // Check column
    echo '<td style="text-align: right;">';
    if ($row['payment_type'] == 'Check') {
        echo number_format($payment_amount, 2);
    }
    echo '</td>';
    
    // ADA column
    echo '<td style="text-align: right;">';
    if ($row['payment_type'] == 'ADA') {
        echo number_format($payment_amount, 2);
    }
    echo '</td>';
    
    echo '<td style="text-align: right;">' . number_format($running_balance, 2) . '</td>';
    echo '</tr>';
}

// Show message if no transactions
if (mysqli_num_rows($payments_result) == 0) {
    echo '<tr>';
    echo '<td colspan="5" style="text-align: center;">No transactions found for the selected period</td>';
    echo '</tr>';
}

// Total row
echo '<tr style="font-weight: bold;">';
echo '<td>Total</td>';
echo '<td></td>';
echo '<td style="text-align: right;">' . number_format($check_total, 2) . '</td>';
echo '<td style="text-align: right;">' . number_format($ada_total, 2) . '</td>';
echo '<td></td>';
echo '</tr>';

// Ending Balance row
echo '<tr style="font-weight: bold;">';
echo '<td>Ending Balance</td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td style="text-align: right;">' . number_format($balance, 2) . '</td>';
echo '</tr>';

// Signature section
echo '<tr><td colspan="5"></td></tr>'; // Empty row as separator
echo '<tr><td colspan="5"></td></tr>'; // Empty row as separator

echo '<tr>';
echo '<td style="text-align: center;">Prepared by:</td>';
echo '<td style="text-align: center;">Checked by:</td>';
echo '<td colspan="3" style="text-align: center;">Approved by:</td>';
echo '</tr>';

echo '<tr>';
echo '<td style="text-align: center;">Cashier</td>';
echo '<td style="text-align: center;">Chief Accountant</td>';
echo '<td colspan="3" style="text-align: center;">Regional Director</td>';
echo '</tr>';

echo '</table>';
exit; 