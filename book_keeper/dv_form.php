<?php
// Include the database connection file
include '../DBConnection.php';

// Check if dv_no is set
if (!isset($_GET['dv_no'])) {
    die("Error: DV No. is missing.");
}

$dv_no = $_GET['dv_no'];

// Prepare SQL query to fetch DV record
$query1 = "SELECT * FROM dv WHERE dv_no = ?";
$stmt1 = $connection->prepare($query1);
if (!$stmt1) {
    die("Query preparation failed: " . $connection->error);
}
$stmt1->bind_param("s", $dv_no);
$stmt1->execute();
$result1 = $stmt1->get_result();

// Fetch data from 'dv' table
if ($result1->num_rows > 0) {
    $dv_form = $result1->fetch_assoc();
    $ors_id = $dv_form['ors_id']; // Get the related ORS ID
} else {
    die("No record found in 'dv' table for DV No.: " . htmlspecialchars($dv_no));
}
$stmt1->close();

// Prepare SQL query to fetch ORS record using ors_id
// Prepare SQL query to fetch ORS record and join with the Approver table
$query2 = "
    SELECT ors.*, 
    financial_object_code.object_name, 
    financial_object_code.uacs_code,
        approver.approver_name,
        approver.designation,
        CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
        responsibility_center.code AS code,
        oopap.oopap_name
    FROM ors 
    LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
    LEFT JOIN approver ON ors.approver_id = approver.approver_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
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
            margin: 20px;
        }

        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
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
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        #db {
            text-align: center;
        }

        #dti {
            text-align: center;
        }

        .split {
            display: flex-start;
            justify-content: center;
        }

        .no-border {
            border: none !important;
        }

        .centered h3,
        .centered h5 {
            margin: 5px 0;
            text-align: center;
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="container">

            <table>
                <tr>
                    <th colspan="3" class="centered">
                        <h3>DEPARTMENT OF TRADE AND INDUSTRY 12</h3>
                        <h5>Entity Name</h5>
                        <h3>DISBURSEMENT VOUCHER</h3>
                    </th>
                    <td rowspan="3" class="left-align">
                        <b>Fund Cluster:</b><br>
                        <span><?php echo $ors_form['fund_cluster']; ?></span><br><br>
                        <b>Date:</b> <?php echo $dv_form['date']; ?><br><br>
                        <b>DV No.:</b><br>
                        <?php echo $dv_form['dv_no']; ?>
                    </td>
                </tr>
            </table>


            <table>
                <tr>
                    <td><strong>Mode of Payment: </strong> <?php echo $dv_form['payment_mode']; ?></p>
                    </td>
                </tr>
            </table>


            <table>
                <tr>
                    <td><strong>Payee</strong></td>
                    <td>
                        <strong><?php echo $ors_form['payee_name']; ?></strong>
                    </td>
                    <td>
                        <p>Tin Employee No.: <?php echo $ors_form['tin_no']; ?></p>
                    </td>
                    <td>
                        <p>ORS/BURS No.: <?php echo $ors_form['ors_no']; ?></p>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td><strong>Address</strong></td>
                    <td><strong><?php echo $ors_form['address']; ?></strong></td>
                </tr>
            </table>

            <table>
                <tr>
                    <th>Particulars</th>
                    <th>Responsibility Center</th>
                    <th>OO/PAP</th>
                    <th>Amount</th>
                </tr>

                <tr>
                    <td><strong><?php echo $ors_form['notes']; ?></strong></td>
                    <td rowspan="5"><?php echo $ors_form['code']; ?></td>
                    <td rowspan="5"><?php echo $ors_form['oopap_name']; ?></td>
                    <td rowspan="4"></td>
                </tr>

                <tr>
                    <td><?php echo $ors_form['notes']; ?></td>
                </tr>

                <tr>
                </tr>

                <tr>
                    <td>
                        <strong>Total Amount Billed:</strong> <span
                            style=" padding-left: 180px;"><?php echo $ors_form['amount']; ?></span> <br>
                        <span style="padding-left: 100px;"><strong>Gross
                                Amount</strong></span><span
                            style="padding-left: 30px "><?php echo $ors_form['amount']; ?></span> <br>

                        <span style="padding-left: 100px;"><strong>Less VAT
                                <?php echo $dv_form['vat']; ?>%</strong></span><span
                            style="padding-left: 30px "><?php echo $dv_form['vat_amount']; ?>
                        </span> <br>

                        <span style="padding-left: 100px;"><strong>Tax Base</strong></span><span
                            style="padding-left: 30px "><?php echo $dv_form['tax_base']; ?></span> <br>

                        <span style="padding-left: 100px;"><strong>Less
                                <?php echo $dv_form['tax_1']; ?>%</strong></span><span
                            style="padding-left: 30px "><?php echo $dv_form['tax_1_amount']; ?></span> <br>

                        <span style="padding-left: 100px;"><strong>Less 2%</strong></span><span
                            style="padding-left: 30px ">937.50 </span> <br>

                        <span style="padding-left: 100px;"><strong>Net Amount</strong></span><span
                            style="padding-left: 30px "><?php echo $dv_form['net_amount']; ?></span> <br>
                    </td>
                </tr>

                <tr>
                    <td><strong style="padding-left: 200px;">Amount Due</strong></td>
                    <td><strong><?php echo $dv_form['net_amount']; ?></strong></td>
                </tr>
            </table>

            <table>
                <tr>
                    <td><strong>A. Certified: Expenses/Cash Advance necessary, lawful and incurred under my direct
                            supervision.</strong>
                        <p style="text-align: center;"><u><?php echo $ors_form['approver_name']; ?></u></p>
                        <p style="text-align: center;">Chief Administrative Officer</p>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td><strong>B. Accounting Entry</strong>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td>Account Title</td>
                    <td>
                        <p>UACS Code</p>
                    </td>
                    <td>
                        <p>Debit</p>
                    </td>
                    <td>
                        <p>Credit</p>
                    </td>
                </tr>

                <tr>
                    <td><?php echo $ors_form['object_name']; ?></td>
                    <td><?php echo $ors_form['uacs_code']; ?></td>
                    <td><?php echo $dv_form['debit']; ?></td>
                    <td><?php echo $dv_form['credit']; ?></td>
                </tr>


            </table>

            <table>
                <tr>
                    <td>
                        <div class="split">
                            <div class="column">
                                <b>C. Certified:</b><br>
                                <input type="checkbox"> <span style="font-size: 12px;">Cash available</span> <br>
                                <input type="checkbox"> <span style="font-size: 12px;">Subject to Authority to Debit
                                    Account (when applicable) </span><br>
                                <input type="checkbox"> <span style="font-size: 12px;">Supporting documents complete and
                                    amount claimed proper</span>

                            </div>
                            <div class="column">
                    <td>
                        <b>D. Approved for Payment</b><br>
                        <p style="text-align: center;">#NAME?</p>
                    </td>
        </div>
    </div>
    </td>
    </tr>
    </table>


    <table>
        <tr>
            <td style="font-size: 12px; " colspan="2">Signature</td>
            <td style="font-size: 12px; " colspan="2">Signature</td>
        </tr>
        <tr>
            <td style="text-align: center;" colspan="2" class="name"><strong
                    style="font-size:18px;"><?php echo $dv_form['chief_accountant']; ?></strong> <br>
                <p>Chief Accountant</p>
                <p>Head, Accounting Unit/Authorized Representative</p>
            </td>
            <td style="text-align: center;" colspan="2" class="name"><strong
                    style="font-size:18px;"><?php echo $dv_form['regional_director']; ?></strong>
                <br>
                <p>Regional Director</p>
                <p>Agency Head/Authorized Representative</p>
            </td>
        </tr>
        <tr>
            <td colspan=" 2"><strong>Date</strong></td>
            <td colspan="2"><strong>Date</strong></td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="4">E. Receipt of Payment</th>
            <td style="font-size: 12px;" rowspan="3">JEV No.</td>
        </tr>
        <tr>
            <td style="font-size: 12px;" rowspan="2" colspan="2">Check/ ADA No. : <?php echo $dv_form['check_no']; ?>
            </td>
            <td style="font-size: 12px;">Date :</td>
            <td style="font-size: 12px;">Bank Name & Account Number: <?php echo $dv_form['bank_acc_no']; ?></td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td style="font-size: 12px;" style="font-size: 12px;">Signature :</td>
            <td style="font-size: 12px;" colspan="2">Date :</td>
            <td style="font-size: 12px;">Printed Name:</td>
            <td style="font-size: 12px;" rowspan="3">Date: </td>
        </tr>
        <tr>
            <td style="font-size: 12px;" colspan="4  ">Official Receipt No. & Date/Other Documents</td>
        </tr>
    </table>
    <button class="btn-print" onclick="window.print()">Print DV</button>
    </div>
    </div>
</body>

</html>