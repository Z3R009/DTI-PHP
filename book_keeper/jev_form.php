<?php
include '../DBConnection.php';

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
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="2"></td>
            <td>JEV No.</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Entity Name:</strong> </td>
            <td>DEPARTMENT OF TRADE AND INDUSTRY</td>
            <td rowspan="3">Date:</td>
            <td rowspan="3">02-24-2025</td>
        </tr>
        <tr>
            <td><strong>Payee:</strong> </td>
            <td>CINCO NIÑAS RESTO</td>
        </tr>
        <tr>
            <td><strong>Fund Cluster:</strong></td>
            <td> GENERAL FUND (101)</td>
        </tr>
    </table>

    <table>
        <tr>
            <td colspan="2">Responsibility Center</td>
            <td colspan="5">ACCOUNTING ENTRIES</td>
        </tr>
        <tr>
            <th rowspan="6" colspan="2">0101010</th>
            <th>Account Name</th>
            <th>UACS Object Code</th>
            <th>P</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
        <tr>
            <td>Representation Expenses</td>
            <td>5029901000</td>
            <td></td>
            <td>15,000.00</td>
            <td></td>
        </tr>
        <tr>
            <td>Due to BIR 5%</td>
            <td>2020101000</td>
            <td></td>
            <td></td>
            <td>669.64</td>
        </tr>
        <tr>
            <td>Due to BIR 2%</td>
            <td>2020101000</td>
            <td></td>
            <td></td>
            <td>267.86</td>
        </tr>
        <tr>
            <td>Cash - Modified Disbursement System (MDS), Regular</td>
            <td>1010404000</td>
            <td></td>
            <td></td>
            <td>14,062.50</td>
        </tr>
        <tr>
            <td>To recognize Payment of Meals during DTI RO AFMD and FO AO QMS Strat Planning Feb 6, 2025
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>DV No.:</strong></td>
            <td> ADMIN&POLICY-25-02-008</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>O.R No.:</strong></td>
            <td> ADMIN&POLICY-25-02-008</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>15,000.00</td>
            <td>15,000.00</td>
        </tr>
    </table>

    <div class="signature">
        <p><strong>Prepared by:</strong> <br> JINNARD B. LUBATON <br> Administrative Aide VI</p>
        <p><strong>Certified Correct:</strong> <br> NEIL ANTHONY T. MORALA <br> Accountant III</p>
    </div>
</body>

</html>