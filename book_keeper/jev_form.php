<?php
include '../DBConnection.php';

// Ensure JEV No. is provided
$jev_no = isset($_GET['jev_no']) ? $_GET['jev_no'] : null;
if (!$jev_no) {
    die("JEV No. is required.");
}

// Fetch JEV and DV details, including ors_id
$query1 = "SELECT jev.*, dv.dv_id, dv.ors_id 
           FROM jev 
           LEFT JOIN dv ON jev.dv_id = dv.dv_id
           WHERE jev.jev_no = ?";
$stmt1 = $connection->prepare($query1);
if (!$stmt1) {
    die("Query preparation failed: " . $connection->error);
}
$stmt1->bind_param("s", $jev_no);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result1->num_rows > 0) {
    $jev_form = $result1->fetch_assoc();
    $ors_id = $jev_form['ors_id']; // Retrieve ors_id
    $dv_id = $jev_form['dv_id'];   // Retrieve dv_id
} else {
    die("No record found for JEV No.: " . htmlspecialchars($jev_no));
}
$stmt1->close();

// Ensure ors_id is available
if (!$ors_id) {
    die("No ORS ID found for the given JEV.");
}

// ============================
// Fetch data from 'dv' table
$query_dv = "SELECT * FROM dv WHERE dv_id = ?";
$stmt = $connection->prepare($query_dv);
if (!$stmt) {
    die("Query preparation failed: " . $connection->error);
}
$stmt->bind_param("i", $dv_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $dv_form = $result->fetch_assoc();
    $ors_id = $dv_form['ors_id']; // Get the related ORS ID (again, for safety)
} else {
    die("No record found in 'dv' table for DV ID: " . htmlspecialchars($dv_id));
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

// ============================
// Fetch ORS record and join with other tables
$query2 = "
    SELECT ors.*, dv.*, dv_history.*,
           approver.approver_name,
           payee.payee_name,
           account_title.account_title,
           account_title.account_code,
           approver.designation,
           CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
           responsibility_center.code AS code,
           oopap.oopap_name
    FROM ors 
    INNER JOIN dv ON ors.ors_id = dv.ors_id
    INNER JOIN dv_history ON dv_history.dv_id = dv.dv_id
    LEFT JOIN approver ON ors.approver_id = approver.approver_id
    LEFT JOIN payee ON payee.payee_id = payee.payee_id
    LEFT JOIN account_title ON account_title.account_id = account_title.account_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
    WHERE ors.ors_id = ? AND payee.payee_id = 25 ";

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
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Entries</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .floating-card {
            width: 1300px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .header-table td {
            border: none;
        }

        .signature {
            margin-top: 20px;
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
        <div class="floating-card">
            <table>
                <tr>
                    <td colspan="6"></td>
                    <td>JEV No.:</td>
                    <td><?php echo $jev_form['jev_no']; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Entity Name:</strong> </td>
                    <td colspan="4">DEPARTMENT OF TRADE AND INDUSTRY</td>
                    <td rowspan="3">Date:</td>
                    <td rowspan="3"><?php echo $jev_form['date']; ?></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Payee:</strong> </td>
                    <td colspan="6">
                        <?php echo !empty($ors_form['payee_name']) ? htmlspecialchars($ors_form['payee_name']) : "Not Available"; ?>

                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Fund Cluster:</strong></td>
                    <td colspan="6">
                        <?php echo !empty($ors_form['fund_cluster']) ? htmlspecialchars($ors_form['fund_cluster']) : "Not Available"; ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">Responsibility Center</td>
                    <td colspan="6">ACCOUNTING ENTRIES</td>
                </tr>
                <tr>
                    <td rowspan="6" colspan="2">
                        <?php echo !empty($ors_form['code']) ? htmlspecialchars($ors_form['code']) : "Not Available"; ?>
                    </td>
                    <td colspan="2">Account Title</td>
                    <td colspan="2">
                        <p>UACS Code</p>
                    </td>
                    <td>
                        <p>Debit</p>
                    </td>
                    <td>
                        <p>Credit</p>
                    </td>
                </tr>
                <?php
                $total_debit = 0;
                $total_credit = 0;

                foreach ($dv_accounts as $account):
                    if ($account['type'] == 'debit') {
                        $total_debit += $account['amount'];
                    } else {
                        $total_credit += $account['amount'];
                    }
                    ?>
                    <tr>
                        <td colspan="2"><?php echo $account['account_title']; ?></td>
                        <td colspan="2"><?php echo $account['account_code']; ?></td>
                        <td>
                            <?php echo $account['type'] == 'debit' ? number_format($account['amount'], 2, '.', ',') : ''; ?>
                        </td>
                        <td>
                            <?php echo $account['type'] == 'credit' ? number_format($account['amount'], 2, '.', ',') : ''; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>

                    <td colspan="2"><?php echo $ors_form['notes']; ?>
                    </td>
                    <td colspan="3"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>DV No.:</strong></td>
                    <td colspan="3"><?php echo $ors_form['dv_no']; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>O.R.S No.:</strong></td>
                    <td colspan="3"><?php echo $ors_form['ors_no']; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Check No.:</strong></td>
                    <td colspan="3"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>ADA No.:</strong></td>
                    <td colspan="3"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td colspan="3"></td>
                    <td></td>
                    <td><strong><?php echo number_format($total_debit, 2, '.', ','); ?></strong></td>
                    <td><strong><?php echo number_format($total_credit, 2, '.', ','); ?></strong></td>
                </tr>

                <td style="text-align: center;" colspan="3" class="name"><strong
                        style="font-size:18px;"><?php echo $jev_form['administrative_aide']; ?></strong> <br>
                    <p>Administrative Aide VI</p>
                </td>
                <td style="text-align: center;" colspan="5" class="name"><strong
                        style="font-size:18px;"><?php echo $jev_form['accountant']; ?></strong>
                    <br>
                    <p>Accountant III</p>
                </td>
            </table>
        </div>
    </div>

    <div class="modal-footer no-print text-center">
        <button type="button" class="btn btn-primary" onclick="window.print()">Print JEV</button>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='processed_jev.php';">
            Back
        </button>
    </div>




</body>

</html>