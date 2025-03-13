<?php
include '../DBConnection.php';

// Check if DV ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No Disbursement Voucher ID provided.";
    exit;
}

$dv_id = $_GET['id'];

// Fetch DV details from the database
$query = "SELECT dv.*, 
          CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
          responsibility_center.code AS responsibility_center,
          oopap.oopap_name,
          approver.approver_name
          FROM dv
          LEFT JOIN fund_cluster ON dv.fund_cluster_id = fund_cluster.fund_cluster_id
          LEFT JOIN responsibility_center ON dv.rc_id = responsibility_center.rc_id
          LEFT JOIN oopap ON dv.oopap_id = oopap.oopap_id
          LEFT JOIN approver ON dv.approver_id = approver.approver_id
          WHERE dv.dv_id = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $dv_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Disbursement Voucher not found.";
    exit;
}

$dv = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disbursement Voucher #<?php echo htmlspecialchars($dv['dv_no']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .dv-container {
            max-width: 8.5in;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            margin-bottom: 10px;
        }
        .form-group {
            flex: 1;
            padding: 0 10px;
        }
        .label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .value {
            border-bottom: 1px solid #000;
            padding: 5px 0;
        }
        .full-width {
            width: 100%;
        }
        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .signature-box {
            flex: 1;
            text-align: center;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .dv-container {
                border: none;
            }
            @page {
                size: letter;
                margin: 0.5in;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()">Print Disbursement Voucher</button>
        <button onclick="window.close()">Close</button>
    </div>
    
    <div class="dv-container">
        <div class="header">
            <h3>DEPARTMENT OF TRADE AND INDUSTRY - REGION 12</h3>
            <h2>DISBURSEMENT VOUCHER</h2>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <div class="label">Fund Cluster:</div>
                <div class="value"><?php echo htmlspecialchars($dv['fund_cluster']); ?></div>
            </div>
            <div class="form-group">
                <div class="label">Date:</div>
                <div class="value"><?php echo date('F d, Y', strtotime($dv['date'])); ?></div>
            </div>
            <div class="form-group">
                <div class="label">DV No.:</div>
                <div class="value"><?php echo htmlspecialchars($dv['dv_no']); ?></div>
            </div>
        </div>
        
        <div class="box">
            <div class="label">Mode of Payment:</div>
            <div class="value"><?php echo htmlspecialchars($dv['mode_payment']); ?></div>
        </div>
        
        <div class="box">
            <div class="form-row">
                <div class="form-group">
                    <div class="label">Payee:</div>
                    <div class="value"><?php echo htmlspecialchars($dv['payee_name']); ?></div>
                </div>
                <div class="form-group">
                    <div class="label">TIN/Employee No.:</div>
                    <div class="value"><?php echo htmlspecialchars($dv['tin_no']); ?></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group full-width">
                    <div class="label">Address:</div>
                    <div class="value"><?php echo htmlspecialchars($dv['address']); ?></div>
                </div>
            </div>
        </div>
        
        <div class="box">
            <div class="label">Particulars:</div>
            <div class="value" style="min-height: 100px;"><?php echo nl2br(htmlspecialchars($dv['notes'])); ?></div>
            
            <div class="form-row" style="margin-top: 20px;">
                <div class="form-group">
                    <div class="label">Responsibility Center:</div>
                    <div class="value"><?php echo htmlspecialchars($dv['responsibility_center']); ?></div>
                </div>
                <div class="form-group">
                    <div class="label">OO/PAP:</div>
                    <div class="value"><?php echo htmlspecialchars($dv['oopap_name']); ?></div>
                </div>
                <div class="form-group">
                    <div class="label">Amount:</div>
                    <div class="value">₱ <?php echo number_format($dv['amount'], 2); ?></div>
                </div>
            </div>
        </div>
        
        <div class="box">
            <h4>Tax Breakdown:</h4>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Gross Amount</td>
                    <td>₱ <?php echo number_format($dv['gross_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td>VAT</td>
                    <td>₱ <?php echo number_format($dv['vat'], 2); ?></td>
                </tr>
                <tr>
                    <td>Tax 1</td>
                    <td>₱ <?php echo number_format($dv['tax_1'], 2); ?></td>
                </tr>
                <tr>
                    <td>Tax 2</td>
                    <td>₱ <?php echo number_format($dv['tax_2'], 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Net Amount</strong></td>
                    <td><strong>₱ <?php echo number_format($dv['net_amount'], 2); ?></strong></td>
                </tr>
            </table>
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <div class="label">Certified by:</div>
                <div style="height: 50px;"></div>
                <div class="value"><?php echo htmlspecialchars($dv['chief_accountant']); ?></div>
                <div>Chief Accountant</div>
            </div>
            <div class="signature-box">
                <div class="label">Approved by:</div>
                <div style="height: 50px;"></div>
                <div class="value"><?php echo htmlspecialchars($dv['regional_director']); ?></div>
                <div>Regional Director</div>
            </div>
        </div>
        
        <div class="box" style="margin-top: 20px;">
            <div class="form-row"><b>
                <div class="form-group">
                    <div class="label">Check/ADA No.:</div>
                    <div class="value"><?php echo htmlspecialchars($dv['check_no']); ?></div>
                </div>
                <div class="form-group">
                    <div class="label">Bank Account No.:</div>
                    <div class="value"><?php echo htmlspecialchars($dv['bank_acc_no']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Automatically print when the page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>