<?php
include '../DBConnection.php';

if (isset($_GET['ors_no'])) {
    $ors_no = $_GET['ors_no'];

    // Prepared statement to prevent SQL injection
    $query = "
    SELECT 
        ors.*, 
        financial_object_code.object_name, 
        approver.approver_name,
        approver.designation,
        CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
        responsibility_center.code AS parent_code,
        oopap.oopap_name
    FROM ors
    LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
    LEFT JOIN approver ON ors.approver_id = approver.approver_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
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
    </style>
</head>

<body>
    <div class="container">
        <!-- Show Form -->

        <table>
            <tr>
                <th colspan="3" class="centered">
                    <h3>OBLIGATION REQUEST AND STATUS</h3>
                    <h3>DEPARTMENT OF TRADE AND INDUSTRY 12</h3>
                    <h5>Entity Name</h5>
                </th>
                <td rowspan="3">
                    <p>ORS No..: <b><?php echo $ors_form['ors_no']; ?></b></p>
                    <p>Date: <b><?php echo date('m-d-Y'); ?></b></p>
                    <p>Fund Cluster: <b><?php echo $ors_form['fund_cluster']; ?></b></p>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td>Payee</td>
                <td>
                    <p><?php echo $ors_form['payee_name']; ?></p>
                </td>
            </tr>
            <tr>
                <td>Office</td>
                <td>DTI-XII</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>
                    <p><?php echo $ors_form['address']; ?></p>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td>Responsibility Center</td>
                <td>Particulars</td>
                <td>OO/PAP</td>
                <td>UACS Object Code</td>
                <td>Amount</td>
            </tr>
            <tr>
                <td>
                    <p><?php echo $ors_form['parent_code']; ?></p>
                </td>
                <td>
                    <p><?php echo $ors_form['notes']; ?></p>
                </td>
                <td>
                    <p><?php echo $ors_form['oopap_name']; ?></p>
                </td>
                <td>
                    <p><?php echo $ors_form['object_name']; ?></p>
                </td>
                <td>
                    <p><?php echo $ors_form['amount']; ?></p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>Total</td>
                <td></td>
                <td></td>
                <td>
                    <p><?php echo $ors_form['amount']; ?></p>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td colspan="2">Certified by:</td>
                <td colspan="2">Certified by:</td>
            </tr>
            <tr>
                <td colspan="2">
                    <p><b>A. Certified:</b> Charges to appropriation/allotment are necessary, lawful, and under my
                        direct supervision.</p>
                    <p><strong><?php echo $ors_form['approver_name']; ?></strong></p>
                    <p><?php echo $ors_form['designation']; ?></p>
                </td>
                <td colspan="2">
                    <p><b>B. Certified:</b> Allotment available and obligated for the purpose.</p>
                    <p><strong><?php echo $ors_form['budget_officer']; ?></strong></p>
                    <p>Budget Officer</p>
                </td>
            </tr>
        </table>

        <button class="btn-print" onclick="window.print()">Print ORS</button>
        <br>
        <a href="ors.php">Submit Another</a>

    </div>
</body>

</html>