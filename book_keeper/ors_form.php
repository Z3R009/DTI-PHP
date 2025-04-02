<?php
include '../DBConnection.php';

if (isset($_GET['ors_no'])) {
    $ors_no = $_GET['ors_no'];

    // Prepared statement to prevent SQL injection
    $query = "
    SELECT 
        ors.*, 
        account_title.account_title,
        account_title.account_code,
        approver.approver_name,
        approver.designation,
        approver.sub_title,
        CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
        responsibility_center.code AS parent_code,
        oopap.oopap_name, 
        payee.payee_name,
        payee.address,
        services.services_name
    FROM ors
    LEFT JOIN account_title ON ors.account_id = account_title.account_id
    LEFT JOIN approver ON ors.approver_id = approver.approver_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
    LEFT JOIN payee ON ors.payee_id = payee.payee_id
    LEFT JOIN services ON ors.services_id = services.services_id
    WHERE ors.ors_no = ?
    ";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "s", $ors_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ors_form = mysqli_fetch_assoc($result);

    if (!$ors_form) {
        echo "ORS No. not found.";
        exit();
    }

    // Get obligation history entries
    $history_query = "
    SELECT 
        obligation_history.net,
        account_title.account_title,
        account_title.account_code
    FROM obligation_history
    LEFT JOIN project ON obligation_history.project_id = project.project_id
    LEFT JOIN account_title ON project.account_id = account_title.account_id
    WHERE obligation_history.ors_id = ?
    ";

    $history_stmt = mysqli_prepare($connection, $history_query);
    mysqli_stmt_bind_param($history_stmt, "i", $ors_form['ors_id']);
    mysqli_stmt_execute($history_stmt);
    $history_result = mysqli_stmt_get_result($history_stmt);
    $obligation_entries = [];
    while ($row = mysqli_fetch_assoc($history_result)) {
        $obligation_entries[] = $row;
    }
} else {
    echo "ORS No. not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obligation Request and Status</title>
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

        .no-border {
            border: none !important;
        }

        .centered h3,
        .centered h5 {
            margin: 5px 0;
            text-align: center;
            display: block;
        }

        .res {
            vertical-align: text-top;
            text-align: left;
        }

        /* Hide buttons when printing */
        @media print {
            .no-print {
                display: none !important;
            }
        }

        /* Center the button group */
        .modal-footer {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        /* Button styles */
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }

        /* Primary Button */
        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        /* Secondary Button */
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Show Form -->

        <table>
            <tr>
                <th colspan="5" class="centered">
                    <h3>OBLIGATION REQUEST AND STATUS</h3>
                    <h3>DEPARTMENT OF TRADE AND INDUSTRY 12</h3>
                    <h5>Entity Name</h5>
                </th>
                <td colspan="3">
                    <p>ORS No..: <b><?php echo $ors_form['ors_no']; ?></b></p>
                    <p>Date: <b><?php echo date('F d, Y'); ?></b></p>
                    <p>Fund Cluster: <b><?php echo $ors_form['fund_cluster']; ?></b></p>
                </td>
            </tr>

            <tr>
                <td>Payee</td>
                <td colspan="7">
                    <p><?php echo $ors_form['payee_name']; ?></p>
                </td>
            </tr>
            <tr>
                <td>Office</td>
                <td colspan="7">DTI-XII</td>
            </tr>
            <tr>
                <td>Address</td>
                <td colspan="7">
                    <p><?php echo $ors_form['address']; ?></p>
                </td>
            </tr>

            <tr>
                <td colspan="2">Responsibility Center</td>
                <td colspan="3">Particulars</td>
                <td>OO/PAP</td>
                <td>UACS Code</td>
                <td>Amount</td>
            </tr>
            <tr>
                <td rowspan="3" colspan="2" style="vertical-align: top;"><br><br>
                    <p><?php echo $ors_form['parent_code']; ?></p>
                </td>

                <td colspan="3" style="border: none;">
                    <p><?php echo $ors_form['purpose']; ?>:</p>
                    <?php foreach ($obligation_entries as $entry): ?>
                        <p style="padding-left: 50px;">
                            <strong><?php echo $entry['account_title']; ?></strong>
                        </p>
                    <?php endforeach; ?>
                </td>
                <td rowspan="2" style="vertical-align: top;"><br><br>
                    <p style="text-align: center;"><?php echo $ors_form['oopap_name']; ?></p>
                </td>
                <td rowspan="2" style="vertical-align: top;"><br><br>
                    <?php foreach ($obligation_entries as $entry): ?>
                        <p style="text-align: center;">
                            <?php echo $entry['account_code']; ?>
                        </p>
                    <?php endforeach; ?>
                </td>
                <td rowspan="2" style="vertical-align: top;"><br><br>
                    <?php foreach ($obligation_entries as $entry): ?>
                        <p style="text-align: right;">
                            <?php echo number_format((float) $entry['net'], 2, '.', ','); ?>
                        </p>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="border: none;">
                    <p style="padding-left: 35px;"><b><?php echo $ors_form['notes']; ?></b></p>
                </td>
            </tr>

            <tr>
                <td colspan="3" style="text-align: right;">Total</td>
                <td></td>
                <td></td>
                <td>
                    <p style="text-align: right;">
                        <b>₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?></b>
                    </p>
                </td>
            </tr>

            <tr>
                <td colspan="4">
                    <p style="height: 200px;"><b>A. Certified:</b> Charges to appropriation/allotment are necessary,
                        lawful, and under my
                        direct supervision.</p>

                    <p>Signature:</p>
                    <hr style="width: 500px; border: 1px solid black; margin: 5px 0 0 20;">

                    <p style="text-align: center;"><strong><?php echo $ors_form['approver_name']; ?></strong></p>
                    <p style="text-align: center; font-size: 15px;"><?php echo $ors_form['designation']; ?></p>
                    <p style="text-align: center; font-size: 15px;"><?php echo $ors_form['sub_title']; ?></p>
                </td>
                <td colspan="4">
                    <p style="height: 200px;"><b>B. Certified:</b> Allotment available and obligated for the
                        purpose/adjustment necessary as indicated above.</p>
                    <p>Signature:</p>
                    <hr style="width: 500px; border: 1px solid black; margin: 5px 0 0 20;">
                    <p style="text-align: center;"><strong><?php echo $ors_form['budget_officer']; ?></strong></p>
                    <p style="text-align: center; font-size: 15px;">Budget Officer</p>
                    <p style="text-align: center; font-size: 15px;">Head, Budget Division/Unit/Authorized Representative
                    </p>
                </td>
            </tr>
            <tr>
                <td colspan="8"></td>
            </tr>
            <tr>
                <td><b>C.</b></td>
                <td colspan="7" class="header" style="text-align: center;"><b>STATUS OF OBLIGATION</b></td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: center;"><b>Reference</b></th>
                <th colspan="5" style="text-align: center;"><b>Amount</b></th>
            </tr>
            <tr>
                <th rowspan="3">Date</th>
                <th rowspan="3">Particulars</th>
                <th rowspan="3">ORS/JEV/Check/ ADA/TRA No.</th>
                <th rowspan="2">Obligation (a)</th>
                <th rowspan="2">Payable (b)</th>
                <th rowspan="2">Payment (c)</th>
                <th colspan="2" style="text-align: center;">Balance</th>

            </tr>

            <tr>

                <th>Not Yet Due (a-b)</th>
                <th>Due and Demandable (b-c)</th>
            </tr>
            <tr></tr>
            <tr></tr>
            <tr></tr>
            <tr></tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th>(a)</th>
                <th>(b)</th>
                <th>(c)</th>
                <th>(a-b)</th>
                <th>(b-c)</th>
            </tr>
            <tr>
                <td>
                    <p><b><?php echo date('F d, Y'); ?></b></p>

                </td>
                <td><?php echo $ors_form['account_title']; ?></td>
                <td><?php echo $ors_form['services_name']; ?></td>
                <td>
                    <p>₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?></p>
                </td>
                <td>
                    <p>₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?></p>
                </td>
                <td>
                    <p>₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?></p>
                </td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <div class="modal-footer no-print text-center">
            <button type="button" class="btn btn-primary" onclick="window.print()">Print ORS</button>
            <a href="ors.php" class="btn btn-secondary">Submit Another</a>
        </div>

    </div>
</body>

</html>