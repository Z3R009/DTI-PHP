<?php
include '../DBConnection.php';

// Initialize filter variables
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report - <?php echo date('M d, Y', strtotime($from_date)); ?> to <?php echo date('M d, Y', strtotime($to_date)); ?></title>
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            padding: 0;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-header h2, .report-header h3 {
            margin: 5px 0;
        }
        .filter-info {
            margin-bottom: 15px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .summary-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            width: 30%;
            background-color: #f9f9f9;
        }
        .summary-card h4 {
            margin-top: 0;
            color: #333;
            font-size: 16px;
        }
        .summary-card p {
            margin: 5px 0;
            font-size: 14px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        .report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .report-footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-block {
            width: 30%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
        }
        .signature-title {
            font-size: 14px;
            color: #666;
        }
        @media print {
            body {
                margin: 0.5cm;
            }
            .print-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-buttons mb-3">
        <button class="btn btn-primary" onclick="window.print()">Print Report</button>
        <button class="btn btn-secondary" onclick="window.close()">Close</button>
    </div>
    
    <div class="report-header">
        <h2>Department of Trade and Industry</h2>
        <h3>Payment Report</h3>
        <p><?php echo date('F d, Y', strtotime($from_date)); ?> to <?php echo date('F d, Y', strtotime($to_date)); ?></p>
    </div>
    
    <div class="filter-info">
        <div>
            <p><strong>Filter:</strong> 
                Status: <?php echo ($status == 'all') ? 'All Statuses' : $status; ?> | 
                Payment Type: <?php echo ($payment_type == 'all') ? 'All Types' : $payment_type; ?>
            </p>
        </div>
        <div>
            <p><strong>Generated on:</strong> <?php echo date('F d, Y h:i A'); ?></p>
        </div>
    </div>
    
    <div class="summary-cards">
        <div class="summary-card">
            <h4>Total Payments</h4>
            <p><strong>Amount:</strong> PHP <?php echo number_format($totals['total_amount'] ?? 0, 2); ?></p>
            <p><strong>Count:</strong> <?php echo ($totals['check_count'] ?? 0) + ($totals['ada_count'] ?? 0); ?></p>
        </div>
        <div class="summary-card">
            <h4>Check Payments</h4>
            <p><strong>Amount:</strong> PHP <?php echo number_format($totals['check_amount'] ?? 0, 2); ?></p>
            <p><strong>Count:</strong> <?php echo $totals['check_count'] ?? 0; ?></p>
        </div>
        <div class="summary-card">
            <h4>ADA Payments</h4>
            <p><strong>Amount:</strong> PHP <?php echo number_format($totals['ada_amount'] ?? 0, 2); ?></p>
            <p><strong>Count:</strong> <?php echo $totals['ada_count'] ?? 0; ?></p>
        </div>
    </div>
    
    <table class="report-table">
        <thead>
            <tr>
                <th>No.</th>
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
            <?php 
            $counter = 1;
            $total_amount = 0;
            while ($row = mysqli_fetch_assoc($report_result)) : 
                $total_amount += $row['amount'];
            ?>
            <tr>
                <td><?php echo $counter++; ?></td>
                <td><?php echo date('m/d/Y', strtotime($row['payment_date'])); ?></td>
                <td><?php echo $row['dv_no']; ?></td>
                <td><?php echo $row['ors_no']; ?></td>
                <td><?php echo $row['payee_name']; ?></td>
                <td><?php echo $row['payment_type']; ?></td>
                <td><?php echo $row['reference_no']; ?></td>
                <td align="right"><?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo $row['status']; ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($report_result) == 0) : ?>
            <tr>
                <td colspan="9" align="center">No records found for the selected filters</td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="7" align="right"><strong>TOTAL:</strong></td>
                <td align="right"><strong><?php echo number_format($total_amount, 2); ?></strong></td>
                <td></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="report-footer">
        <div class="signature-block">
            <div class="signature-line">Prepared by</div>
            <div class="signature-title">Cashier</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">Checked by</div>
            <div class="signature-title">Chief Accountant</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">Approved by</div>
            <div class="signature-title">Regional Director</div>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            // Auto print when page loads
            //window.print();
        }
    </script>
</body>
</html> 