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

$report_query = "SELECT p.payment_date, d.dv_no, o.ors_no, pa.payee_name, p.payment_type, p.reference_no, p.amount, p.status, p.remarks
                FROM payment p
                JOIN dv d ON p.dv_id = d.dv_id
                JOIN ors o ON d.ors_id = o.ors_id
                JOIN payee pa ON o.payee_id = pa.payee_id
                WHERE $where_clause
                ORDER BY p.payment_date ASC";
$report_result = mysqli_query($connection, $report_query);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="payment_report_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Create Excel content
echo '<table border="1">';

// Title
echo '<tr><th colspan="9">DTI Payment Report ' . date('F d, Y', strtotime($from_date)) . ' to ' . date('F d, Y', strtotime($to_date)) . '</th></tr>';
echo '<tr><th colspan="9">Filters: Status - ' . (($status == 'all') ? 'All Statuses' : $status) . ', Payment Type - ' . (($payment_type == 'all') ? 'All Types' : $payment_type) . '</th></tr>';
echo '<tr><th colspan="9">Generated on: ' . date('F d, Y h:i A') . '</th></tr>';
echo '<tr><td colspan="9"></td></tr>'; // Empty row as separator

// Column headers
echo '<tr>';
echo '<th>No.</th>';
echo '<th>Date</th>';
echo '<th>DV No</th>';
echo '<th>ORS No</th>';
echo '<th>Payee</th>';
echo '<th>Payment Type</th>';
echo '<th>Reference No</th>';
echo '<th>Amount</th>';
echo '<th>Status</th>';
echo '</tr>';

// Data rows
$counter = 1;
$total_amount = 0;
while ($row = mysqli_fetch_assoc($report_result)) {
    $total_amount += $row['amount'];
    
    echo '<tr>';
    echo '<td>' . $counter++ . '</td>';
    echo '<td>' . date('m/d/Y', strtotime($row['payment_date'])) . '</td>';
    echo '<td>' . $row['dv_no'] . '</td>';
    echo '<td>' . $row['ors_no'] . '</td>';
    echo '<td>' . $row['payee_name'] . '</td>';
    echo '<td>' . $row['payment_type'] . '</td>';
    echo '<td>' . $row['reference_no'] . '</td>';
    echo '<td>' . number_format($row['amount'], 2) . '</td>';
    echo '<td>' . $row['status'] . '</td>';
    echo '</tr>';
}

// Total row
if (mysqli_num_rows($report_result) > 0) {
    echo '<tr>';
    echo '<td colspan="7" align="right"><strong>TOTAL:</strong></td>';
    echo '<td><strong>' . number_format($total_amount, 2) . '</strong></td>';
    echo '<td></td>';
    echo '</tr>';
} else {
    echo '<tr><td colspan="9" align="center">No records found for the selected filters</td></tr>';
}

echo '</table>';
exit; 