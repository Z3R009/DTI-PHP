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
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="budget_report_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create Excel content as HTML table
    echo '<table border="1">';
    
    // Title and Report Info
    echo '<tr><th colspan="8">DEPARTMENT OF TRADE AND INDUSTRY - REGION XII</th></tr>';
    echo '<tr><th colspan="8">BUDGET REPORT</th></tr>';
    echo '<tr><th colspan="8">Period: ' . date('F d, Y', strtotime($from_date)) . ' to ' . date('F d, Y', strtotime($to_date)) . '</th></tr>';
    echo '<tr><th colspan="8">Generated on: ' . date('F d, Y') . '</th></tr>';
    echo '<tr><td colspan="8"></td></tr>'; // Empty row as separator
    
    // Account Details
    echo '<tr><th colspan="8">Account Details</th></tr>';
    echo '<tr>';
    echo '<td colspan="4"><strong>Account Name:</strong> ' . $account_details['account_name'] . '</td>';
    echo '<td colspan="4"><strong>Account Number:</strong> ' . $account_details['account_number'] . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="4"><strong>Type:</strong> ' . $account_details['type'] . '</td>';
    echo '<td colspan="4"></td>';
    echo '</tr>';
    echo '<tr><td colspan="8"></td></tr>'; // Empty row as separator
    
    // Budget Summary
    echo '<tr><th colspan="8">Budget Summary</th></tr>';
    echo '<tr>';
    echo '<td colspan="3"><strong>Starting Balance:</strong> PHP ' . number_format($starting_balance, 2) . '</td>';
    echo '<td colspan="2"><strong>Total Spent:</strong> PHP ' . number_format($total_amount, 2) . '</td>';
    echo '<td colspan="3"><strong>Ending Balance:</strong> PHP ' . number_format($ending_balance, 2) . '</td>';
    echo '</tr>';
    echo '<tr><td colspan="8"></td></tr>'; // Empty row as separator
    
    // Transaction Details Header
    echo '<tr><th colspan="8">Transaction Details</th></tr>';
    
    // Column headers
    echo '<tr>';
    echo '<th>Date</th>';
    echo '<th>DV No</th>';
    echo '<th>ORS No</th>';
    echo '<th>Payee</th>';
    echo '<th>Purpose</th>';
    echo '<th>Payment Type</th>';
    echo '<th>Reference No</th>';
    echo '<th>Amount</th>';
    echo '</tr>';
    
    // Data rows
    if ($transactions && mysqli_num_rows($transactions) > 0) {
        while ($row = mysqli_fetch_assoc($transactions)) {
            echo '<tr>';
            echo '<td>' . date('m/d/Y', strtotime($row['payment_date'])) . '</td>';
            echo '<td>' . $row['dv_no'] . '</td>';
            echo '<td>' . $row['ors_no'] . '</td>';
            echo '<td>' . $row['payee_name'] . '</td>';
            echo '<td>' . $row['purpose'] . '</td>';
            echo '<td>' . $row['payment_type'] . '</td>';
            echo '<td>' . $row['reference_no'] . '</td>';
            echo '<td>PHP ' . number_format($row['amount'], 2) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8">No transactions found for the selected period</td></tr>';
    }
    
    // Total row
    echo '<tr>';
    echo '<th colspan="7" style="text-align:right;">Total:</th>';
    echo '<th>PHP ' . number_format($total_amount, 2) . '</th>';
    echo '</tr>';
    
    // Footer - Signatures
    echo '<tr><td colspan="8"></td></tr>'; // Empty row as separator
    echo '<tr><td colspan="8"></td></tr>'; // Empty row as separator
    
    echo '<tr>';
    echo '<td colspan="2">Prepared by:</td>';
    echo '<td colspan="3">Verified by:</td>';
    echo '<td colspan="3">Approved by:</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td colspan="2">__________________</td>';
    echo '<td colspan="3">__________________</td>';
    echo '<td colspan="3">__________________</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td colspan="2">Cashier</td>';
    echo '<td colspan="3">Chief Accountant</td>';
    echo '<td colspan="3">Regional Director</td>';
    echo '</tr>';
    
    echo '</table>';
    exit();
} else {
    // If no account selected, redirect back to the report page
    header('Location: budget_report.php');
    exit();
}
?> 