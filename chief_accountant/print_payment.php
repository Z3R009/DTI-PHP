<?php
include '../DBConnection.php';

// Get payment ID from URL
$payment_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get payment details
$payment_query = "SELECT p.*, d.dv_no, d.date as dv_date, o.ors_no, pa.payee_name, pa.payee_address
                 FROM payment p
                 JOIN dv d ON p.dv_id = d.dv_id
                 JOIN ors o ON d.ors_id = o.ors_id
                 JOIN payee pa ON o.payee_id = pa.payee_id
                 WHERE p.payment_id = '$payment_id'";
$payment_result = mysqli_query($connection, $payment_query);
$payment = mysqli_fetch_assoc($payment_result);

// If payment not found, redirect
if (!$payment) {
    echo "<script>alert('Payment not found!'); window.close();</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - NiceAdmin Bootstrap Template</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>
<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            padding: 0;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-header h2, .report-header h3 {
            margin: 5px 0;
        }
        .payment-details {
            margin-bottom: 30px;
        }
        .detail-section {
            margin-bottom: 20px;
        }
        .detail-section h4 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            width: 200px;
            font-weight: bold;
        }
        .detail-value {
            flex: 1;
        }
        .remarks-section {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .report-footer {
            margin-top: 50px;
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
<body>


<body>
    
    <div class="print-buttons mb-3">
        <button class="btn btn-primary" onclick="window.print()">Print Report</button>
        <button class="btn btn-secondary" onclick="window.close()">Close</button>
    </div>
    
    <div class="report-header">
        <h2>Department of Trade and Industry</h2>
        <h3>Payment Report</h3>
        <p>DV # <?php echo $payment['dv_no']; ?></p>
    </div>
    
    <div class="payment-details">
        <div class="detail-section">
            <h4>Disbursement Voucher Information</h4>
            <div class="detail-row">
                <div class="detail-label">DV Number:</div>
                <div class="detail-value"><?php echo $payment['dv_no']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">DV Date:</div>
                <div class="detail-value"><?php echo date('F d, Y', strtotime($payment['dv_date'])); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">ORS Number:</div>
                <div class="detail-value"><?php echo $payment['ors_no']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Payee:</div>
                <div class="detail-value"><?php echo $payment['payee_name']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Address:</div>
                <div class="detail-value"><?php echo $payment['payee_address']; ?></div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4>Payment Information</h4>
            <div class="detail-row">
                <div class="detail-label">Payment Type:</div>
                <div class="detail-value"><?php echo $payment['payment_type']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Reference Number:</div>
                <div class="detail-value"><?php echo $payment['reference_no']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Payment Date:</div>
                <div class="detail-value"><?php echo date('F d, Y', strtotime($payment['payment_date'])); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Amount:</div>
                <div class="detail-value">PHP <?php echo number_format($payment['amount'], 2); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value"><?php echo $payment['status']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Recorded By:</div>
                <div class="detail-value"><?php echo $payment['created_by']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Recorded On:</div>
                <div class="detail-value"><?php echo date('F d, Y h:i A', strtotime($payment['created_at'])); ?></div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4>Remarks</h4>
            <div class="remarks-section">
                <?php echo empty($payment['remarks']) ? 'No remarks added' : nl2br($payment['remarks']); ?>
            </div>
        </div>
    </div>
    
    <div class="report-footer">
        <div class="signature-block">
            <div class="signature-line">Prepared by</div>
            <div class="signature-title">Cashier</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">Verified by</div>
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