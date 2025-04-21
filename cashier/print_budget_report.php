<?php
include '../database/db_connection.php';

$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

if (empty($account_id)) {
    echo "<script>alert('No account selected. Please select an account.'); window.close();</script>";
    exit;
}
$account_details_query = "SELECT a.*, d.cash_allotment, d.balances 
                        FROM account_name a 
                        LEFT JOIN draft_project d ON a.account_id = d.account_id 
                        WHERE a.account_id = '$account_id' 
                        ORDER BY d.created_at DESC LIMIT 1";
$account_details_result = mysqli_query($conn, $account_details_query);

$account_name = '';
$account_number = '';
$cash_allotment = 0;
$current_balance = 0;

if (mysqli_num_rows($account_details_result) > 0) {
    $account_details = mysqli_fetch_assoc($account_details_result);
    $account_name = $account_details['account_name'];
    $account_number = $account_details['account_number'];
    $cash_allotment = $account_details['cash_allotment'] ?? 0;
    $current_balance = $account_details['balances'] ?? 0;
}

$payment_query = "SELECT p.*, a.account_name, a.account_number, d.cash_allotment, d.balances 
                FROM payments p 
                INNER JOIN draft_project d ON p.draft_id = d.draft_id 
                INNER JOIN account_name a ON d.account_id = a.account_id 
                WHERE d.account_id = '$account_id'";

if (!empty($from_date)) {
    $payment_query .= " AND DATE(p.date_created) >= '$from_date'";
}
if (!empty($to_date)) {
    $payment_query .= " AND DATE(p.date_created) <= '$to_date'";
}

$payment_query .= " ORDER BY p.date_created ASC";
$payment_result = mysqli_query($conn, $payment_query);

$total_check = 0;
$total_ada = 0;
$total_payments = 0;

if (mysqli_num_rows($payment_result) > 0) {
    mysqli_data_seek($payment_result, 0);
    while ($row = mysqli_fetch_assoc($payment_result)) {
        if ($row['payment_type'] == 'Check') {
            $total_check += $row['amount'];
        } else if ($row['payment_type'] == 'ADA') {
            $total_ada += $row['amount'];
        }
        $total_payments += $row['amount'];
    }
    mysqli_data_seek($payment_result, 0);
}

$starting_balance = $cash_allotment;
if (!empty($from_date)) {
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

$ending_balance = $starting_balance - $total_payments;

$report_date = date('F d, Y');
$report_time = date('h:i A');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Budget Report - <?php echo $account_name; ?></title>
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
            font-size: 18px;
            margin: 0;
            padding: 0;
        }
        .report-header h2 {
            font-size: 16px;
            margin: 5px 0;
            padding: 0;
        }
        .report-header p {
            margin: 5px 0;
            padding: 0;
        }
        .report-details {
            margin-bottom: 20px;
        }
        .report-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-details td {
            padding: 5px;
        }
        .report-details .label {
            font-weight: bold;
            width: 150px;
        }
        .report-summary {
            margin-bottom: 20px;
            width: 100%;
        }
        .report-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-summary th, .report-summary td {
            border: 1px solid #000;
            padding: 5px;
        }
        .report-summary th {
            background-color: #f0f0f0;
            text-align: left;
        }
        .transactions table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .transactions th, .transactions td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .transactions th {
            background-color: #f0f0f0;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-section .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
            display: inline-block;
            text-align: center;
            margin-right: 50px;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()">Print Report</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="report-header">
        <h1>DEPARTMENT OF TRADE AND INDUSTRY</h1>
        <h2>CASH BUDGET REPORT</h2>
        <p>For the period of <?php echo date('F d, Y', strtotime($from_date)); ?> to <?php echo date('F d, Y', strtotime($to_date)); ?></p>
        <p>Generated on: <?php echo $report_date; ?> at <?php echo $report_time; ?></p>
    </div>

    <div class="report-details">
        <table>
            <tr>
                <td class="label">Account:</td>
                <td><?php echo $account_name; ?></td>
            </tr>
            <tr>
                <td class="label">Account Number:</td>
                <td><?php echo $account_number; ?></td>
            </tr>
            <tr>
                <td class="label">Cash Allotment:</td>
                <td>₱<?php echo number_format($cash_allotment, 2); ?></td>
            </tr>
            <tr>
                <td class="label">Current Balance:</td>
                <td>₱<?php echo number_format($current_balance, 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="report-summary">
        <table>
            <tr>
                <th colspan="2">Budget Summary</th>
            </tr>
            <tr>
                <td class="label">Starting Balance (<?php echo date('M d, Y', strtotime($from_date)); ?>):</td>
                <td class="text-end">₱<?php echo number_format($starting_balance, 2); ?></td>
            </tr>
            <tr>
                <td class="label">Total Check Payments:</td>
                <td class="text-end">₱<?php echo number_format($total_check, 2); ?></td>
            </tr>
            <tr>
                <td class="label">Total ADA Payments:</td>
                <td class="text-end">₱<?php echo number_format($total_ada, 2); ?></td>
            </tr>
            <tr>
                <td class="label">Total Payments:</td>
                <td class="text-end">₱<?php echo number_format($total_payments, 2); ?></td>
            </tr>
            <tr>
                <td class="label">Ending Balance (<?php echo date('M d, Y', strtotime($to_date)); ?>):</td>
                <td class="text-end">₱<?php echo number_format($ending_balance, 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="transactions">
        <h3>Transaction Details</h3>
        <table>
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
                            <td><?php echo date('m/d/Y', strtotime($payment['date_created'])); ?></td>
                            <td><?php echo $payment['dv_number']; ?></td>
                            <td><?php echo $payment['payee']; ?></td>
                            <td><?php echo $payment['particular']; ?></td>
                            <td><?php echo $payment['payment_type']; ?></td>
                            <td>
                                <?php 
                                if ($payment['status'] == 0) {
                                    echo 'Pending';
                                } elseif ($payment['status'] == 1) {
                                    echo 'Processed';
                                } elseif ($payment['status'] == 2) {
                                    echo 'Rejected';
                                }
                                ?>
                            </td>
                            <td class="text-end">₱<?php echo number_format($payment['amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center">No transactions found for the selected period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-end">Total:</th>
                    <th class="text-end">₱<?php echo number_format($total_payments, 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-line">
            <p>Prepared by:</p>
            <p>____________________</p>
            <p>Cashier</p>
        </div>
        
        <div class="signature-line">
            <p>Verified by:</p>
            <p>____________________</p>
            <p>Accounting Officer</p>
        </div>
        
        <div class="signature-line">
            <p>Approved by:</p>
            <p>____________________</p>
            <p>Department Head</p>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated report. No signature is required.</p>
    </div>

    <script>
        window.onload = function() {
            // Auto print when the page loads
            window.setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html> 