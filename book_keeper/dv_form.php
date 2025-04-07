<?php
// Include the database connection file
include '../DBConnection.php';

// Check if dv_no is set
if (!isset($_GET['dv_no'])) {
    die("Error: DV No. is missing.");
}

$dv_no = $_GET['dv_no'];

// Prepare SQL query to fetch DV record
$query = "SELECT dv.*, 
          ors.ors_no,
          ors.payee_id,
          payee.payee_name,
          payee.tin_no,
          payee.address,
          dv_history.account_id,
          dv_history.type,
          dv_history.amount,
          account_title.account_title,
          account_title.account_code
          FROM dv 
          LEFT JOIN ors ON dv.ors_id = ors.ors_id
          LEFT JOIN payee ON ors.payee_id = payee.payee_id
          LEFT JOIN dv_history ON dv.dv_id = dv_history.dv_id
          LEFT JOIN account_title ON dv_history.account_id = account_title.account_id
          WHERE dv.dv_no = ?";

$stmt = $connection->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $connection->error);
}
$stmt->bind_param("s", $dv_no);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data from 'dv' table
if ($result->num_rows > 0) {
    $dv_form = $result->fetch_assoc();
    $ors_id = $dv_form['ors_id']; // Get the related ORS ID
} else {
    die("No record found in 'dv' table for DV No.: " . htmlspecialchars($dv_no));
}
$stmt->close();

// Fetch all accounts for this DV
$accounts_query = "SELECT dv_history.*, 
                  account_title.account_title,
                  account_title.account_code
                  FROM dv_history 
                  LEFT JOIN account_title ON dv_history.account_id = account_title.account_id
                  WHERE dv_history.dv_id = ?";

$accounts_stmt = $connection->prepare($accounts_query);
if (!$accounts_stmt) {
    die("Query preparation failed: " . $connection->error);
}
$accounts_stmt->bind_param("i", $dv_form['dv_id']);
$accounts_stmt->execute();
$accounts_result = $accounts_stmt->get_result();

// Store all accounts in an array
$dv_accounts = [];
while ($account = $accounts_result->fetch_assoc()) {
    $dv_accounts[] = $account;
}
$accounts_stmt->close();

// Prepare SQL query to fetch ORS record using ors_id
// Prepare SQL query to fetch ORS record and join with the Approver table
$query2 = "
    SELECT ors.*, 
        account_title.account_title,
        account_title.account_code,
        approver.approver_name,
        approver.designation,
        CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
        responsibility_center.code AS code,
        oopap.oopap_name,
        payee.payee_name,
        payee.address,
        payee.bank_acc_no,
        payee.tin_no
    FROM ors 
    LEFT JOIN account_title ON ors.account_id = account_title.account_id
    LEFT JOIN approver ON ors.approver_id = approver.approver_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
    LEFT JOIN payee ON ors.payee_id = payee.payee_id
    WHERE ors.ors_id = ?";

$stmt2 = $connection->prepare($query2);
if (!$stmt2) {
    die("Query preparation failed: " . $connection->error);
}
$stmt2->bind_param("s", $ors_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

// Fetch data from 'ors' table
if ($result2->num_rows > 0) {
    $ors_form = $result2->fetch_assoc();
} else {
    $ors_form = []; // If no ORS record found
}
$stmt2->close();


// Close the database connection
$connection->close();

// Display results
echo "<pre>";
// print_r($dv_form);
// print_r($ors_form);
echo "</pre>";

function numberToWords($number)
{
    $ones = array(
        0 => "",
        1 => "ONE",
        2 => "TWO",
        3 => "THREE",
        4 => "FOUR",
        5 => "FIVE",
        6 => "SIX",
        7 => "SEVEN",
        8 => "EIGHT",
        9 => "NINE",
        10 => "TEN",
        11 => "ELEVEN",
        12 => "TWELVE",
        13 => "THIRTEEN",
        14 => "FOURTEEN",
        15 => "FIFTEEN",
        16 => "SIXTEEN",
        17 => "SEVENTEEN",
        18 => "EIGHTEEN",
        19 => "NINETEEN"
    );
    $tens = array(
        2 => "TWENTY",
        3 => "THIRTY",
        4 => "FORTY",
        5 => "FIFTY",
        6 => "SIXTY",
        7 => "SEVENTY",
        8 => "EIGHTY",
        9 => "NINETY"
    );
    $hundreds = array(
        "HUNDRED",
        "THOUSAND",
        "MILLION",
        "BILLION",
        "TRILLION"
    );

    $number = number_format($number, 2, '.', ',');
    $num_arr = explode(".", $number);
    $wholenum = $num_arr[0];
    $decnum = $num_arr[1];
    $whole_arr = array_reverse(explode(",", $wholenum));
    krsort($whole_arr, 1);
    $rettxt = "";

    foreach ($whole_arr as $key => $i) {
        while (substr($i, 0, 1) == "0")
            $i = substr($i, 1, 5);
        if ($i < 20) {
            $rettxt .= $ones[$i];
        } elseif ($i < 100) {
            if (substr($i, 0, 1) != "0")
                $rettxt .= $tens[substr($i, 0, 1)];
            if (substr($i, 1, 1) != "0")
                $rettxt .= " " . $ones[substr($i, 1, 1)];
        } else {
            if (substr($i, 0, 1) != "0")
                $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
            if (substr($i, 1, 1) != "0")
                $rettxt .= " " . $tens[substr($i, 1, 1)];
            if (substr($i, 2, 1) != "0")
                $rettxt .= " " . $ones[substr($i, 2, 1)];
        }
        if ($key > 0) {
            $rettxt .= " " . $hundreds[$key] . " ";
        }
    }

    if ($decnum > 0) {
        $rettxt .= " AND ";
        if ($decnum < 20) {
            $rettxt .= $ones[$decnum];
        } elseif ($decnum < 100) {
            $rettxt .= $tens[substr($decnum, 0, 1)];
            if (substr($decnum, 1, 1) != "0") {
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
        }
        $rettxt .= " CENTAVOS";
    }
    return $rettxt;
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disbursement Voucher Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            background: #f5f5f5;
        }

        .container {
            width: 700px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header {
            text-align: left;
        }

        .section {
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid black;
            background: white;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px 5px;
            /* Reduced padding */
            text-align: left;
            vertical-align: top;
            font-size: 10px;
            /* Smaller font size */
            line-height: 1.3;
        }

        .no-border {
            border: none !important;
        }

        .centered {
            text-align: center;
            vertical-align: middle;
        }

        .centered h3 {
            font-size: 12px;
            margin: 1px 0;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            display: block;
            line-height: 1.2;
        }

        .centered h5 {
            font-size: 11px;
            margin: 1px 0;
            font-weight: normal;
            text-align: center;
            display: block;
            line-height: 1.2;
        }

        .header-cell {
            padding: 2px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .res {
            vertical-align: text-top;
            text-align: left;
        }

        hr {
            border: none;
            border-top: 1px solid black;
            margin: 5px 0;
        }

        .signature-line {
            border-top: 1px solid black;
            width: 80%;
            margin: 20px auto 5px;
        }

        .signature-container {
            text-align: center;
            margin-top: 5px;
        }

        .signature-name {
            font-weight: bold;
            margin: 3px 0;
            font-size: 11px;
        }

        .signature-title {
            font-size: 10px;
            margin: 1px 0;
        }

        .checkbox-container {
            margin: 2px 0;
            font-size: 10px;
        }

        input[type="checkbox"] {
            margin-right: 3px;
            vertical-align: middle;
            transform: scale(0.8);
        }

        /* Specific cell adjustments */
        td.amount-cell {
            text-align: right;
            white-space: nowrap;
        }

        td.label-cell {
            white-space: nowrap;
            width: 1%;
        }

        /* Print specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
                min-height: auto;
            }

            .container {
                width: 650px !important;
                margin: 0 auto;
                padding: 10px 30px;
                box-shadow: none;
                border-radius: 0;
            }

            table {
                margin-bottom: 0;
            }

            /* Ensure borders print clearly */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: letter portrait;
                margin: 0.5cm;
            }

            /* Hide print buttons */
            .no-print {
                display: none !important;
            }
        }

        /* Modal footer adjustments */
        .modal-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            background: white;
        }

        /* Adjust button sizes */
        .btn {
            padding: 8px 16px;
            font-size: 14px;
        }

        /* Center the button group */
        .modal-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            background: white;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Print Preview Modal */
        .print-preview-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .print-preview-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            height: 80%;
            overflow: auto;
        }

        .print-preview-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .print-preview-close:hover {
            color: black;
        }

        .print-preview-actions {
            text-align: center;
            margin-top: 20px;
        }

        /* Add these styles */
        .amount-section {
            margin-left: 20px;
            width: 250px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            align-items: center;
        }

        .amount-label {
            flex: 1;
            text-align: left;
        }

        .amount-value {
            text-align: right;
            min-width: 100px;
            margin-right: 20px;
        }

        .amount-line {
            width: 150px;
            border-top: 1px solid black;
            margin: 3px 0 3px auto;
        }
    </style>
</head>

<body>
    <div class="container">

        <table>
            <tr>
                <th colspan="5" class="centered header-cell">
                    <h3>DEPARTMENT OF TRADE AND INDUSTRY 12</h3>
                    <h5>Entity Name</h5>
                    <h3>DISBURSEMENT VOUCHER</h3>
                </th>
                <td class="header-cell">
                    <p>Fund Cluster: <b><?php echo $ors_form['fund_cluster']; ?></b></p>
                    <p>Date: <b><?php echo $dv_form['date']; ?></b></p>
                    <p>DV No.: <b><?php echo $dv_form['dv_no']; ?></b></p>
                </td>
            </tr>

            <tr>
                <td><b>Mode of Payment</b></td>
                <td colspan="5"> <?php echo $dv_form['payment_mode']; ?></td>
            </tr>


            <tr>
                <td><strong>Payee</strong></td>
                <td>
                    <strong><?php echo $ors_form['payee_name']; ?></strong>
                </td>
                <td colspan="2">
                    <p>Tin Employee No.: <?php echo $ors_form['tin_no']; ?></p>
                </td>
                <td colspan="2">
                    <p>ORS/URS No.: <?php echo $ors_form['ors_no']; ?></p>
                </td>
            </tr>

            <tr>
                <td><strong>Address</strong></td>
                <td colspan="5"><b><?php echo $ors_form['address']; ?></b></td>
            </tr>
            <tr>
                <th colspan="3">Particulars</th>
                <th>Responsibility Center</th>
                <th>OO/PAP</th>
                <th>Amount</th>
            </tr>

            <tr>
                <td colspan="3">
                    <p style="text-align: center;"><b><?php echo $ors_form['notes']; ?></b></p>

                    <div class="amount-section">
                        <div class="amount-row">
                            <span class="amount-label">Total amount Billed:</span>
                            <span
                                class="amount-value"><?php echo number_format($dv_form['amount'], 2, '.', ','); ?></span>
                        </div>

                        <div class="amount-row">
                            <span class="amount-label">Gross Amount</span>
                            <span
                                class="amount-value"><?php echo number_format($dv_form['total_amount'], 2, '.', ','); ?></span>
                        </div>

                        <div class="amount-row">
                            <span class="amount-label">Less VAT <?php echo $dv_form['vat']; ?>%</span>
                            <span
                                class="amount-value"><?php echo number_format($dv_form['vat_amount'], 2, '.', ','); ?></span>
                        </div>
                        <div class="amount-line"></div>

                        <div class="amount-row">
                            <span class="amount-label">Tax Base</span>
                            <span
                                class="amount-value"><?php echo number_format($dv_form['tax_base'], 2, '.', ','); ?></span>
                        </div>
                        <div class="amount-line"></div>

                        <div class="amount-row">
                            <span class="amount-label">Less: <?php echo $dv_form['tax_1']; ?>%</span>
                            <span
                                class="amount-value"><?php echo number_format($dv_form['tax_1_amount'], 2, '.', ','); ?></span>
                        </div>

                        <div class="amount-row">
                            <span class="amount-label">Less: <?php echo $dv_form['tax_2']; ?>%</span>
                            <span
                                class="amount-value"><?php echo number_format($dv_form['tax_2_amount'], 2, '.', ','); ?></span>
                        </div>
                        <div class="amount-line"></div>

                        <div class="amount-row" style="justify-content: flex-end;">
                            <span class="amount-value"
                                style="margin-right: 20px;"><?php echo number_format(($dv_form['vat_amount'] + $dv_form['tax_1_amount'] + $dv_form['tax_2_amount']), 2, '.', ','); ?></span>
                        </div>
                        <div class="amount-line"></div>

                        <div class="amount-row">
                            <span class="amount-label">Net Amount</span>
                            <span
                                class="amount-value"><b><?php echo number_format($dv_form['net_amount'], 2, '.', ','); ?></b></span>
                        </div>
                        <div class="amount-line"></div>
                    </div>

                <td rowspan="2"><?php echo $ors_form['code']; ?></td>
                <td rowspan="2"><?php echo $ors_form['oopap_name']; ?></td>
                <td></td>
            </tr>

            <tr>
                <td colspan="3">Amount Due</td>
                <td><?php echo number_format($dv_form['net_amount'], 2, '.', ','); ?></td>
            </tr>
            <tr>
                <td colspan="6"><strong>A. Certified: Expenses/Cash Advance necessary, lawful and incurred under my
                        direct
                        supervision.</strong>
                    <div style="text-align: center; margin-top: 20px;">
                        <p style="margin-bottom: 0;"><?php echo $ors_form['approver_name']; ?></p>
                        <div style="width: 250px; border-top: 1px solid black; margin: 0 auto;"></div>
                        <p style="margin-top: 3px;"><?php echo $ors_form['designation']; ?></p>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="6"><strong>B. Accounting Entry</strong>
                </td>
            </tr>
            <tr>
                <td colspan="2">Account Title</td>
                <td>
                    <p>UACS Code</p>
                </td>
                <td colspan="2">
                    <p>Debit</p>
                </td>
                <td>
                    <p>Credit</p>
                </td>
            </tr>

            <?php foreach ($dv_accounts as $account): ?>
                <tr>
                    <td colspan="2"><?php echo $account['account_title']; ?></td>
                    <td><?php echo $account['account_code']; ?></td>
                    <td colspan="2">
                        <?php echo $account['type'] == 'debit' ? number_format($account['amount'], 2, '.', ',') : ''; ?>
                    </td>
                    <td>
                        <?php echo $account['type'] == 'credit' ? number_format($account['amount'], 2, '.', ',') : ''; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <td colspan="3">
                    <p><b>C. Certified:</b></p>
                    <div class="checkbox-container">
                        <input type="checkbox"> <span>Cash available</span>
                    </div>
                    <div class="checkbox-container">
                        <input type="checkbox"> <span>Subject to Authority to Debit Account (when applicable)</span>
                    </div>
                    <div class="checkbox-container">
                        <input type="checkbox"> <span>Supporting documents complete and amount claimed proper</span>
                    </div>
                </td>
                <td colspan="3">
                    <p><b>D. Approved for Payment</b></p>
                    <p style="text-align: center; margin-top: 20px;">
                        <b>***<?php
                        $last_account = end($dv_accounts);
                        echo numberToWords($last_account ? $last_account['amount'] : 0);
                        ?> PESOS ONLY***</b>
                    </p>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <p>Signature:</p>
                    <div class="signature-line"></div>
                    <div class="signature-container">
                        <p class="signature-name"><?php echo $dv_form['chief_accountant']; ?></p>
                        <p class="signature-title">Chief Accountant</p>
                        <p class="signature-title">Head, Accounting Unit/Authorized Representative</p>
                    </div>
                </td>
                <td colspan="3">
                    <p>Signature:</p>
                    <div class="signature-line"></div>
                    <div class="signature-container">
                        <p class="signature-name"><?php echo $dv_form['regional_director']; ?></p>
                        <p class="signature-title">Budget Officer</p>
                        <p class="signature-title">Agency Head/Authorized Representative</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>Date</strong></td>
                <td colspan="2"></td>
                <td><strong>Date</strong></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th colspan="6">E. Receipt of Payment</th>

            </tr>
            <tr>
                <td style="font-size: 12px;">Check/ADA No. :
                </td>
                <td>1232343</td>
                <td style="font-size: 12px;">Date :</td>
                <td style="font-size: 12px;">Bank Name & Account Number:</td>
                <td colspan="2"> <?php echo $ors_form['bank_acc_no']; ?></td>

            </tr>
            <tr>
                <td style="font-size: 12px;">Signature :</td>
                <td></td>
                <td style="font-size: 12px;">Date :</td>
                <td colspan="2" style="font-size: 12px;">Printed Name:</td>
                <td style="font-size: 12px;">JEV No.</td>
            </tr>
            <tr>
                <td style="font-size: 12px;" colspan="5">Official Receipt No. & Date/Other Documents</td>
                <td style="font-size: 12px;">Date: </td>
            </tr>
        </table>
        <div class="modal-footer no-print">
            <button type="button" class="btn btn-primary" onclick="window.print()">Print DV</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">
                Back
            </button>
        </div>

    </div>
    </div>
    </div>

    <script>
        // Function to show print preview
        function showPrintPreview() {
            // Clone the main content
            const content = document.querySelector('.container').cloneNode(true);

            // Remove the no-print elements
            const noPrintElements = content.querySelectorAll('.no-print');
            noPrintElements.forEach(element => {
                element.remove();
            });

            // Add the cloned content to the preview modal
            document.getElementById('printPreviewContent').innerHTML = '';
            document.getElementById('printPreviewContent').appendChild(content);

            // Show the modal
            document.getElementById('printPreviewModal').style.display = 'block';
        }

        // Function to close print preview
        function closePrintPreview() {
            document.getElementById('printPreviewModal').style.display = 'none';
        }

        // Function to print the document
        function printDocument() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');

            // Get the content to print
            const contentToPrint = document.querySelector('.container').cloneNode(true);

            // Remove the no-print elements
            const noPrintElements = contentToPrint.querySelectorAll('.no-print');
            noPrintElements.forEach(element => {
                element.remove();
            });
        }

    </script>
</body>

</html>