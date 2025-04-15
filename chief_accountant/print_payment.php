<?php
include '../DBConnection.php';

// Get payment ID from URL parameter
$payment_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch payment details
$query = "SELECT p.*, d.dv_no, o.purpose, fc.fund_cluster_name, 
          pa.payee_name as payee,
          CASE 
            WHEN p.payment_type = 'Check' THEN 'Check'
            WHEN p.payment_type = 'ADA' THEN 'ADA'
            ELSE p.payment_type
          END as payment_method_name
          FROM payment p
          JOIN dv d ON p.dv_id = d.dv_id
          JOIN ors o ON d.ors_id = o.ors_id
          JOIN payee pa ON o.payee_id = pa.payee_id
          LEFT JOIN fund_cluster fc ON o.fund_cluster_id = fc.fund_cluster_id
          WHERE p.payment_id = $payment_id";
$result = mysqli_query($connection, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<div style='text-align:center; margin-top:50px;'>";
    echo "<h3>Payment record not found</h3>";
    echo "<a href='javascript:history.back()'>Go Back</a>";
    echo "</div>";
    exit;
}

$row = mysqli_fetch_assoc($result);

// Format status for better display
function getStatusBadge($status) {
    switch(strtolower($status)) {
        case 'pending':
            return "<span class='status-badge pending'>Pending</span>";
        case 'approved':
            return "<span class='status-badge approved'>Approved</span>";
        case 'rejected':
            return "<span class='status-badge rejected'>Rejected</span>";
        case 'cancelled':
            return "<span class='status-badge cancelled'>Cancelled</span>";
        default:
            return "<span class='status-badge'>$status</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - #<?php echo $payment_id; ?></title>
    <style>
        @media print {
            body {
                width: 21cm;
                height: 29.7cm;
                margin: 0;
                padding: 1cm;
                font-size: 12pt;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .print-header {
            text-align: right;
            margin-bottom: 10px;
        }
        
        .document-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 20px;
        }
        
        .logo {
            max-width: 80px;
            display: inline-block;
            vertical-align: middle;
        }
        
        .header-title {
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
        
        h1 {
            font-size: 24px;
            margin: 0;
            color: #444;
        }
        
        h2 {
            font-size: 18px;
            margin: 5px 0;
            font-weight: normal;
            color: #666;
        }
        
        .payment-id {
            font-size: 20px;
            color: #008758;
            font-weight: bold;
        }
        
        .details-container {
            margin-bottom: 30px;
        }
        
        .details-section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #555;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .details-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .details-label {
            width: 200px;
            font-weight: bold;
            color: #666;
        }
        
        .details-value {
            flex: 1;
        }
        
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #008758;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-badge.pending {
            background-color: #fff4de;
            color: #ffa800;
        }
        
        .status-badge.approved {
            background-color: #e8fff3;
            color: #1bc5bd;
        }
        
        .status-badge.rejected {
            background-color: #ffe2e5;
            color: #f64e60;
        }
        
        .status-badge.cancelled {
            background-color: #eee;
            color: #666;
        }
        
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-block {
            width: 45%;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .buttons {
            margin: 20px 0;
            text-align: center;
        }
        
        .print-btn {
            background-color: #008758;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .print-btn:hover {
            background-color: #006c46;
        }
        
        .back-btn {
            background-color: #ccc;
            color: #333;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            margin-left: 10px;
        }
        
        .back-btn:hover {
            background-color: #bbb;
        }
    </style>
</head>
<body>
    <div class="print-header no-print">
        <div class="buttons">
            <button class="print-btn" onclick="window.print()">Print Receipt</button>
            <button class="back-btn" onclick="window.history.back()">Go Back</button>
        </div>
    </div>
    
    <div class="document-header">
        <div class="header-title">
            <h1>Department of Trade and Industry</h1>
            <h2>Payment Receipt</h2>
            <div class="payment-id">Receipt #<?php echo $payment_id; ?> (DV #<?php echo $row['dv_no']; ?>)</div>
        </div>
    </div>
    
    <div class="details-container">
        <div class="details-section">
            <div class="section-title">Payment Information</div>
            <div class="details-row">
                <div class="details-label">Payment Date:</div>
                <div class="details-value"><?php echo date('F d, Y', strtotime($row['payment_date'])); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Status:</div>
                <div class="details-value"><?php echo getStatusBadge($row['status']); ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Amount:</div>
                <div class="details-value amount">₱<?php echo number_format($row['amount'], 2); ?></div>
            </div>
        </div>
        
        <div class="details-section">
            <div class="section-title">Payee Information</div>
            <div class="details-row">
                <div class="details-label">Payee Name:</div>
                <div class="details-value"><?php echo $row['payee']; ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Fund Cluster:</div>
                <div class="details-value"><?php echo $row['fund_cluster_name']; ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Purpose:</div>
                <div class="details-value"><?php echo $row['purpose']; ?></div>
            </div>
        </div>
        
        <div class="details-section">
            <div class="section-title">Payment Method</div>
            <div class="details-row">
                <div class="details-label">Method:</div>
                <div class="details-value"><?php echo $row['payment_method_name']; ?></div>
            </div>
            <div class="details-row">
                <div class="details-label">Reference Number:</div>
                <div class="details-value"><?php echo $row['reference_no']; ?></div>
            </div>
        </div>
        
        <?php if (!empty($row['remarks'])): ?>
        <div class="details-section">
            <div class="section-title">Remarks</div>
            <div class="details-row">
                <div class="details-value"><?php echo nl2br($row['remarks']); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="signature-section">
        <div class="signature-block">
            <div class="signature-line">Prepared By</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">Received By</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an official receipt from the Department of Trade and Industry.</p>
        <p>Receipt generated on: <?php echo date('F d, Y h:i A'); ?></p>
    </div>
</body>
</html> 