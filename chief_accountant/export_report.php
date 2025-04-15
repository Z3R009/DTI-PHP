<?php
include '../DBConnection.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payment_report_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$fund_cluster = isset($_GET['fund_cluster']) ? $_GET['fund_cluster'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build WHERE clause based on filters
$where_clause = "p.payment_date BETWEEN '$from_date' AND '$to_date'";
if ($fund_cluster != 'all') {
    $where_clause .= " AND fc.fund_cluster_id = '$fund_cluster'";
}
if ($status != 'all') {
    $where_clause .= " AND p.status = '$status'";
}

// Get transactions
$query = "SELECT p.*, d.dv_no, o.purpose, fc.fund_cluster_name, 
            CASE 
                WHEN p.payment_type = 'Check' THEN 'Check'
                WHEN p.payment_type = 'ADA' THEN 'ADA'
                ELSE p.payment_type
            END as payment_method_name,
            pa.payee_name as payee
          FROM payment p
          JOIN dv d ON p.dv_id = d.dv_id
          JOIN ors o ON d.ors_id = o.ors_id
          JOIN payee pa ON o.payee_id = pa.payee_id
          LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
          WHERE $where_clause
          ORDER BY p.payment_date DESC";
$result = mysqli_query($connection, $query);

// Get total amount
$total_query = "SELECT SUM(p.amount) as grand_total 
                FROM payment p 
                JOIN dv d ON p.dv_id = d.dv_id
                JOIN ors o ON d.ors_id = o.ors_id
                LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                WHERE $where_clause";
$total_result = mysqli_query($connection, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$grand_total = $total_row['grand_total'] ?? 0;

// Get status summary
$status_summary_query = "SELECT p.status, COUNT(*) as count, SUM(p.amount) as total_amount
                         FROM payment p
                         JOIN dv d ON p.dv_id = d.dv_id
                         JOIN ors o ON d.ors_id = o.ors_id
                         LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                         WHERE $where_clause
                         GROUP BY p.status";
$status_summary_result = mysqli_query($connection, $status_summary_query);

// Get fund cluster summary
$fund_summary_query = "SELECT fc.fund_cluster_name, COUNT(*) as count, SUM(p.amount) as total_amount
                      FROM payment p
                      JOIN dv d ON p.dv_id = d.dv_id
                      JOIN ors o ON d.ors_id = o.ors_id
                      LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                      WHERE $where_clause
                      GROUP BY fc.fund_cluster_id";
$fund_summary_result = mysqli_query($connection, $fund_summary_query);

// Get payment method summary
$payment_method_query = "SELECT p.payment_type as payment_method_name, COUNT(*) as count, SUM(p.amount) as total_amount
                        FROM payment p
                        JOIN dv d ON p.dv_id = d.dv_id
                        JOIN ors o ON d.ors_id = o.ors_id
                        LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
                        WHERE $where_clause
                        GROUP BY p.payment_type";
$payment_method_result = mysqli_query($connection, $payment_method_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
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
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .summary-container {
            margin-bottom: 30px;
        }
        .summary-table {
            width: 80%;
            margin: 0 auto;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h2>Payment Transaction Report</h2>
        <h3>Period: <?= date('F d, Y', strtotime($from_date)) ?> to <?= date('F d, Y', strtotime($to_date)) ?></h3>
        <?php if ($status != 'all'): ?>
        <h4>Status: <?= ucfirst($status) ?></h4>
        <?php endif; ?>
        <?php if ($fund_cluster != 'all'): ?>
        <h4>Fund Cluster: <?php 
            $fc_query = "SELECT fund_cluster_name FROM fund_cluster WHERE fund_cluster_id = '$fund_cluster'";
            $fc_result = mysqli_query($connection, $fc_query);
            $fc_row = mysqli_fetch_assoc($fc_result);
            echo $fc_row['fund_cluster_name'];
        ?></h4>
        <?php endif; ?>
        <h4>Report Generated: <?= date('F d, Y h:i A') ?></h4>
    </div>

    <div class="summary-container">
        <h3>Summary Information</h3>
        
        <!-- Status Summary -->
        <h4>Status Summary</h4>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($status_summary_result, 0);
                while ($row = mysqli_fetch_assoc($status_summary_result)) : 
                ?>
                <tr>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['count'] ?></td>
                    <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td>Total</td>
                    <td><?= mysqli_num_rows($result) ?></td>
                    <td>₱<?= number_format($grand_total, 2) ?></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Fund Cluster Summary -->
        <h4>Fund Cluster Summary</h4>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Fund Cluster</th>
                    <th>Count</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($fund_summary_result, 0);
                while ($row = mysqli_fetch_assoc($fund_summary_result)) : 
                ?>
                <tr>
                    <td><?= $row['fund_cluster_name'] ?></td>
                    <td><?= $row['count'] ?></td>
                    <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td>Total</td>
                    <td><?= mysqli_num_rows($result) ?></td>
                    <td>₱<?= number_format($grand_total, 2) ?></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Payment Method Summary -->
        <h4>Payment Method Summary</h4>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Count</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($payment_method_result, 0);
                $method_total_count = 0;
                $method_total_amount = 0;
                while ($row = mysqli_fetch_assoc($payment_method_result)) : 
                    $method_total_count += $row['count'];
                    $method_total_amount += $row['total_amount'];
                ?>
                <tr>
                    <td><?= $row['payment_method_name'] ?></td>
                    <td><?= $row['count'] ?></td>
                    <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td>Total</td>
                    <td><?= $method_total_count ?></td>
                    <td>₱<?= number_format($method_total_amount, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>Detailed Transaction Report</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>DV No</th>
                <th>Date</th>
                <th>Payee</th>
                <th>Fund Cluster</th>
                <th>Method</th>
                <th>Reference No.</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Purpose</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)) : 
            ?>
            <tr>
                <td><?= $row['payment_id'] ?></td>
                <td><?= $row['dv_no'] ?></td>
                <td><?= date('m/d/Y', strtotime($row['payment_date'])) ?></td>
                <td><?= $row['payee'] ?></td>
                <td><?= $row['fund_cluster_name'] ?></td>
                <td><?= $row['payment_method_name'] ?></td>
                <td><?= $row['reference_no'] ?></td>
                <td>₱<?= number_format($row['amount'], 2) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td><?= $row['purpose'] ?></td>
            </tr>
            <?php endwhile; ?>
            <tr class="total-row">
                <td colspan="7" style="text-align: right;"><strong>Grand Total:</strong></td>
                <td><strong>₱<?= number_format($grand_total, 2) ?></strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>
</html> 