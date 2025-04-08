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
        // Try to find the ORS with ADMIN&POLICY prefix if not found
        if (strpos($ors_no, 'ADMIN&POLICY') === 0) {
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
            WHERE ors.ors_no LIKE ?
            ";

            $stmt = mysqli_prepare($connection, $query);
            $pattern = $ors_no . '%';
            mysqli_stmt_bind_param($stmt, "s", $pattern);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $ors_form = mysqli_fetch_assoc($result);
        }

        if (!$ors_form) {
            echo "ORS No. not found.";
            exit();
        }
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
            text-align: left;
            vertical-align: top;
            font-size: 10px;
            line-height: 1.3;
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

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: letter portrait;
                margin: 0.5cm;
            }

            .no-print {
                display: none !important;
            }
        }

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

        .amount-cell {
            text-align: right;
            white-space: nowrap;
        }

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

        .print-preview-actions {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <table>
            <tr>
                <th colspan="5" class="centered header-cell">
                    <h3>OBLIGATION REQUEST AND STATUS</h3>
                    <h3>DEPARTMENT OF TRADE AND INDUSTRY 12</h3>
                    <h5>Entity Name</h5>
                </th>
                <td colspan="3" class="header-cell">
                    <p>ORS No..: <b><?php echo $ors_form['ors_no']; ?></b></p>
                    <p>Date: <b><?php echo date('F d, Y'); ?></b></p>
                    <p>Fund Cluster: <b><?php echo $ors_form['fund_cluster']; ?></b></p>
                </td>
            </tr>

            <tr>
                <td><strong>Payee</strong></td>
                <td colspan="7"><strong><?php echo $ors_form['payee_name']; ?></strong></td>
            </tr>
            <tr>
                <td><strong>Office</strong></td>
                <td colspan="7">DTI-XII</td>
            </tr>
            <tr>
                <td><strong>Address</strong></td>
                <td colspan="7"><strong><?php echo $ors_form['address']; ?></strong></td>
            </tr>

            <tr>
                <th colspan="2">Responsibility Center</th>
                <th colspan="3">Particulars</th>
                <th>OO/PAP</th>
                <th>UACS Code</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td rowspan="3" colspan="2" class="res">
                    <p><?php echo $ors_form['parent_code']; ?></p>
                </td>
                <td colspan="3">
                    <p style="margin-bottom: 15px;"><?php echo $ors_form['purpose']; ?>:</p>
                    <?php foreach ($obligation_entries as $entry): ?>
                        <p style="padding-left: 50px; margin: 8px 0 ;">
                            <strong><?php echo $entry['account_title']; ?></strong>
                        </p>
                    <?php endforeach; ?>
                </td>
                <td rowspan="2" class="centered">
                    <p><?php echo $ors_form['oopap_name']; ?></p>
                </td>
                <td rowspan="2" class="centered">
                    <?php foreach ($obligation_entries as $entry): ?>
                        <p><?php echo $entry['account_code']; ?></p>
                    <?php endforeach; ?>
                </td>
                <td rowspan="2" class="centered">
                    <?php foreach ($obligation_entries as $entry): ?>
                        <p><?php echo number_format((float) $entry['net'], 2, '.', ','); ?></p>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <p style="padding-left: 35px;"><b><?php echo $ors_form['notes']; ?></b></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                <td></td>
                <td></td>
                <td class="amount-cell">
                    <strong>₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?></strong>
                </td>
            </tr>

            <tr>
                <td colspan="4">
                    <p style="height: 150px;"><strong>A. Certified:</strong> Charges to appropriation/allotment are
                        necessary, lawful, and under
                        my direct supervision.</p>
                    <div style="text-align: center; margin-top: 20px;">
                        <p style="margin-bottom: 0;"><?php echo $ors_form['approver_name']; ?></p>
                        <div style="width: 250px; border-top: 1px solid black; margin: 0 auto;"></div>
                        <p style="margin-top: 3px;"><?php echo $ors_form['designation']; ?></p>
                        <p style="margin-top: 3px;"><?php echo $ors_form['sub_title']; ?></p>
                    </div>
                </td>
                <td colspan="4">
                    <p style="height: 150px;"><strong>B. Certified:</strong> Allotment available and obligated for the
                        purpose/adjustment
                        necessary as indicated above.</p>
                    <div style="text-align: center; margin-top: 20px;">
                        <p style="margin-bottom: 0;"><?php echo $ors_form['budget_officer']; ?></p>
                        <div style="width: 250px; border-top: 1px solid black; margin: 0 auto;"></div>
                        <p style="margin-top: 3px;">Budget Officer</p>
                        <p style="margin-top: 3px;">Head, Budget Division/Unit/Authorized Representative</p>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="8" class="centered header-cell">
                    <h3>STATUS OF OBLIGATION</h3>
                </td>
            </tr>
            <tr>
                <th colspan="3" class="centered"><strong>Reference</strong></th>
                <th colspan="5" class="centered"><strong>Amount</strong></th>
            </tr>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Particulars</th>
                <th rowspan="2">ORS/JEV/Check/ADA/TRA No.</th>
                <th>Obligation (a)</th>
                <th>Payable (b)</th>
                <th>Payment (c)</th>
                <th>Not Yet Due (a-b)</th>
                <th>Due and Demandable (b-c)</th>
            </tr>
            <tr>
                <th>(a)</th>
                <th>(b)</th>
                <th>(c)</th>
                <th>(a-b)</th>
                <th>(b-c)</th>
            </tr>
            <tr>
                <td><strong><?php echo date('F d, Y'); ?></strong></td>
                <td><?php echo $ors_form['account_title']; ?></td>
                <td><?php echo $ors_form['services_name']; ?></td>
                <td class="amount-cell">₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?>
                </td>
                <td class="amount-cell">₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?>
                </td>
                <td class="amount-cell">₱<?php echo number_format((float) $ors_form['total_amount'], 2, '.', ','); ?>
                </td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <div class="modal-footer no-print">
            <button type="button" class="btn btn-primary" onclick="window.print()">Print ORS</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='processed_ors.php';">
                Back
            </button>
        </div>
    </div>

    <script>
        function showPrintPreview() {
            const content = document.querySelector('.container').cloneNode(true);
            const noPrintElements = content.querySelectorAll('.no-print');
            noPrintElements.forEach(element => {
                element.remove();
            });
            document.getElementById('printPreviewContent').innerHTML = '';
            document.getElementById('printPreviewContent').appendChild(content);
            document.getElementById('printPreviewModal').style.display = 'block';
        }

        function closePrintPreview() {
            document.getElementById('printPreviewModal').style.display = 'none';
        }

        function printDocument() {
            const printWindow = window.open('', '_blank');
            const contentToPrint = document.querySelector('.container').cloneNode(true);
            const noPrintElements = contentToPrint.querySelectorAll('.no-print');
            noPrintElements.forEach(element => {
                element.remove();
            });
        }
    </script>
</body>

</html>

</html>