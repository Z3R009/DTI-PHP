<?php
include '../DBConnection.php';

// insert

if (isset($_POST['submit'])) {
    echo "Form submitted!";

    // Debugging: Print all POST data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $date = $_POST['date'];
    $dv_no = $_POST['dv_no'];
    $ors_no = $_POST['ors_id']; // This is actually the ORS number
    $payment_mode = $_POST['payment_mode'];
    $vat = $_POST['vat'];
    $vat_amount = $_POST['vat_amount'];
    $tax_base = $_POST['tax_base'];
    $tax_1 = $_POST['tax_1'];
    $tax_1_amount = $_POST['tax_1_amount'];
    $tax_2 = $_POST['tax_2'];
    $tax_2_amount = $_POST['tax_2_amount'];
    $net_amount = $_POST['net_amount'];
    $chief_accountant = $_POST['chief_accountant'];
    $regional_director = $_POST['regional_director'];

    // Get the account titles and amounts arrays
    $account_titles = $_POST['account_titles'];
    $debit_amounts = $_POST['debit_amounts'];
    $credit_amounts = $_POST['credit_amounts'];

    // Start a transaction
    $connection->begin_transaction();

    try {
        // First, get the ors_id from the ors_no
        $ors_query = "SELECT ors_id FROM ors WHERE ors_no = ?";
        $ors_stmt = $connection->prepare($ors_query);
        if ($ors_stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($connection->error));
        }
        $ors_stmt->bind_param("s", $ors_no);
        if (!$ors_stmt->execute()) {
            throw new Exception("Error getting ORS ID: " . $ors_stmt->error);
        }
        $ors_result = $ors_stmt->get_result();
        if ($ors_result->num_rows === 0) {
            throw new Exception("ORS number not found: " . $ors_no);
        }
        $ors_row = $ors_result->fetch_assoc();
        $ors_id = $ors_row['ors_id'];
        $ors_stmt->close();

        // Insert the main DV record
        $sql = "INSERT INTO dv (date, dv_no, ors_id, payment_mode, vat, vat_amount, tax_base, tax_1, tax_1_amount, tax_2, tax_2_amount, net_amount, chief_accountant, regional_director) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($connection->error));
        }

        $stmt->bind_param(
            "ssisddddddddss",
            $date,
            $dv_no,
            $ors_id,
            $payment_mode,
            $vat,
            $vat_amount,
            $tax_base,
            $tax_1,
            $tax_1_amount,
            $tax_2,
            $tax_2_amount,
            $net_amount,
            $chief_accountant,
            $regional_director
        );

        if (!$stmt->execute()) {
            throw new Exception("Error: " . $stmt->error);
        }

        $dv_id = $connection->insert_id;
        $stmt->close();

        // Redirect after successful save
        header("Location: dv_form.php?dv_no=$dv_no");
        exit();

    } catch (Exception $e) {
        // Rollback the transaction on error
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }

    $connection->close();
}


// retrieve payee

$sql_payee = "SELECT payee_id, payee_name, tin_no, address  FROM payee";
$result_payee = $connection->query($sql_payee);

// retrieve responsibility

$sql_responsibility_center = "SELECT rc_id, code, description FROM responsibility_center";
$result_responsibility_center = $connection->query($sql_responsibility_center);

// retrieve fund_cluster
$sql_fund_cluster = "SELECT fund_cluster_id, fund_cluster_name FROM fund_cluster";
$result_fund_cluster = $connection->query($sql_fund_cluster);


// retrieve oo/pap
$sql_oopap = "SELECT oopap_id, oopap_name FROM oopap";
$result_oopap = $connection->query($sql_oopap);

// retrieve services
$sql_services = "SELECT services_id, services_name, code FROM services";
$result_services = $connection->query($sql_services);

// Fetch Approvers Data
$sql_approvers = "SELECT approver_id, approver_name, designation FROM approver";
$result_approvers = $connection->query($sql_approvers);

// Store Approver Data for JavaScript
$approverData = [];
while ($row = $result_approvers->fetch_assoc()) {
    $approverData[$row['approver_id']] = [
        'name' => $row['approver_name'],
        'designation' => $row['designation']
    ];
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

    <style>
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #03045e;
        }

        .form-section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .form-section h3 {
            color: #0077b6;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
            padding: 0 10px;
            margin-bottom: 15px;
        }

        .form-group.full-width {
            flex: 0 0 100%;
        }

        .form-group.half-width {
            flex: 0 0 50%;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2B2D42;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #0077b6;
            box-shadow: 0 0 0 2px rgba(0, 119, 182, 0.2);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #0077b6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #03045e;
        }

        .btn-secondary {
            background-color: #8d99ae;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #2B2D42;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        table th,
        table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e8e8e8;
            vertical-align: middle;
        }

        /* Table header */
        table th {
            background-color: #0077b6;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-top: none;
        }

        /* Zebra striping for better readability */
        table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table tbody tr:hover {
            background-color: #f0f9ff;
            transition: background-color 0.2s ease;
        }

        .assessments-table {
            font-size: 0.95rem;
        }

        .assessments-table th:first-child,
        .assessments-table td:first-child {
            padding-left: 20px;
        }

        .assessments-table th:last-child,
        .assessments-table td:last-child {
            padding-right: 20px;
        }

        /* Better styling for accounting entry table */
        .accounting-entry-table th {
            background-color: #0077b6;
        }

        .accounting-entry-table input,
        .accounting-entry-table select {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            border-radius: 6px;
            width: 100%;
            font-size: 0.9rem;
        }

        .accounting-entry-table input:focus,
        .accounting-entry-table select:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .accounting-entry-table tfoot tr:first-child {
            border-top: 5px solid #cbd5e1;
            font-weight: bold;
        }

        .accounting-entry-table tfoot input {
            font-weight: bold;
            background-color: #f1f5f9;
        }


        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .form-group.half-width {
                flex: 0 0 100%;
            }
        }

        @media (max-width: 768px) {


            .sidebar {
                display: none;
            }

            .form-container {
                padding: 20px;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            table th,
            table td {
                padding: 10px 12px;
            }

            .severity-badge {
                padding: 4px 8px;
            }


        }

        /* Timeline styles */
        .status-timeline {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .timeline-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            width: 100%;
            height: 2px;
            background-color: #ddd;
            left: 50%;
        }

        .timeline-point {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f8f9fa;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .timeline-point.completed {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .timeline-point.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        .timeline-label {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }

        .timeline-label.completed {
            color: #28a745;
            font-weight: 500;
        }

        .timeline-label.active {
            color: #007bff;
            font-weight: 500;
        }

        /* Badge styles */
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        /* Approval info styles */
        .approval-info {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            font-size: 14px;
        }

        .approval-info.approved {
            color: #28a745;
        }

        .approval-info.pending {
            color: #ffc107;
        }

        /* Form value styles */
        .form-value {
            padding: 8px 0;
            font-weight: 500;
        }

        @media print {
            body {
                background-color: white;
            }

            .sidebar,
            .btn-container {
                display: none;
            }


            .form-container {
                box-shadow: none;
                padding: 0;
            }
        }

        /* Custom styles for calculation fields */
        .calculation-field {
            background-color: #edf2f7;
            cursor: not-allowed;
        }

        /* Tax percentage input field */
        .tax-percentage {
            width: 50px;
            padding: 3px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        /* Accounting entry table */
        .accounting-entry-table th:nth-child(1),
        .accounting-entry-table td:nth-child(1) {
            width: 40%;
        }

        .accounting-entry-table th:nth-child(2),
        .accounting-entry-table td:nth-child(2) {
            width: 20%;
        }

        .accounting-entry-table th:nth-child(3),
        .accounting-entry-table th:nth-child(4),
        .accounting-entry-table td:nth-child(3),
        .accounting-entry-table td:nth-child(4) {
            width: 20%;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 900px;
            position: relative;
            animation: modalopen 0.4s;
        }

        @keyframes modalopen {
            from {
                opacity: 0;
                transform: translateY(-60px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #03045e;
        }

        .modal-header {
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-footer {
            padding-top: 15px;
            margin-top: 20px;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }

        .detail-label {
            flex: 0 0 200px;
            font-weight: 500;
            color: #666;
        }

        .detail-value {
            flex: 1;
        }

        /* Severity badges */
        .severity-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .severity-high {
            background-color: #ff4757;
            color: white;
        }

        .view-button {
            padding: 8px 14px;
            background-color: #0077b6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }





        /* Filter styles enhancement */
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .filter-item {
            flex: 1;
            min-width: 200px;
        }

        .filter-item label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #475569;
            font-size: 0.9rem;
        }

        .filter-item select,
        .filter-item input {
            width: 100%;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background-color: white;
            font-size: 0.9rem;
        }

        .filter-item select:focus,
        .filter-item input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        /* Add a filtering active indicator */
        .filters.active-filters {
            border-color: #93c5fd;
            background-color: #eff6ff;
        }

        /* Clear filters button */
        .clear-filters {
            padding: 8px 16px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            color: #475569;
            transition: all 0.2s ease;
            align-self: flex-end;
        }

        .clear-filters:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }

        /* Status indicator additions */
        .severity-with-balance {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                gap: 10px;
            }

            .filter-item {
                min-width: 100%;
            }
        }

        .form-title {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 30px;
            color: #03045e;
        }
    </style>
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">




        <div class="pagetitle d-flex align-items-center">
            <h1 class="mb-0">Disbursement Voucher</h1>

            <!-- Buttons Container with right alignment -->
            <div class="ms-auto">
                <button class="btn btn-primary" onclick="window.location.href='dv.php'">
                    DV Form
                </button>
                <button class="btn btn-primary" onclick="window.location.href='processed_dv.php'">
                    Processed DV
                </button>
            </div>
        </div>


        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Disbursement Voucher</h2>

                <div class="tab-content">
                    <div>
                        <div id="ors_form">
                            <form method="post">
                                <div class="form-section">
                                    <h3>General Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Fund Cluster</label>
                                            <select class="form-control" name="fund_cluster_id">
                                                <option selected disabled>Select Fund Cluster</option>
                                                <?php
                                                while ($row = $result_fund_cluster->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['fund_cluster_id']) . "'>" . htmlspecialchars($row['fund_cluster_name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">OO/PAP</label>
                                            <select class="form-control" name="oopap_id">
                                                <option selected disabled>Select OO/PAP</option>
                                                <?php
                                                while ($row = $result_oopap->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['oopap_id']) . "'>" . htmlspecialchars($row['oopap_name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Services</label>
                                            <select class="form-control" name="services_id" id="services">
                                                <option selected disabled>Select Services</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control" id="date" name="date">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Disbursement Voucher No.</label>
                                            <input type="text" class="form-control" id="dv_no" name="dv_no" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payee Details Section -->
                                <div class="form-section">
                                    <h3> Payee Details</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Payee Name</label>
                                            <select class="form-control" name="payee_id" id="payee_id">
                                                <option selected disabled>Select Payee</option>
                                                <?php
                                                while ($row = $result_payee->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['payee_id']) . "' 
                                                                        data-tin='" . htmlspecialchars($row['tin_no']) . "' 
                                                                        data-address='" . htmlspecialchars($row['address']) . "'>"
                                                        . htmlspecialchars($row['payee_name']) .
                                                        "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">TIN/Employee No.</label>
                                            <input type="text" class="form-control" name="tin_no" id="tin_no"
                                                autocomplete="off">

                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" id="address"
                                            autocomplete="off">

                                    </div>
                                </div>

                                <div class="form-section">


                                    <label class="form-label">Purpose</label>
                                    <div class="form-row">
                                        <select class="form-control" name="purpose">
                                            <option value="To Payment of">To Payment of</option>
                                            <option value="To Disburse">To Reimburse</option>
                                            <option value="To Cash Advance">To Cash Advance</option>
                                        </select>

                                    </div>

                                    <div class="form-group">

                                        <div class="form-row">
                                            <label class="form-label">Responsibility Center</label>
                                            <select class="form-control" name="rc_id">
                                                <option selected disabled>Select Responsibility Center</option>
                                                <?php
                                                while ($row = $result_responsibility_center->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['rc_id']) . "'>"
                                                        . htmlspecialchars($row['code']) . " - " . htmlspecialchars($row['description']) .
                                                        "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>


                                    </div>

                                    <div class="form-section">
                                        <h3>Accounting Entry</h3>
                                        <div class="table-responsive">
                                            <table class="accounting-entry-table">
                                                <thead>
                                                    <tr>
                                                        <th colspan="2">Account Title</th>
                                                        <th>Debit Amount</th>
                                                        <th>Credit Amount</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="accountingTableBody">
                                                    <tr>
                                                        <td colspan="2">
                                                            <select class="form-control account-select"
                                                                name="account_titles[]">
                                                                <option selected disabled>Select Account</option>
                                                                <?php
                                                                $account_query = "SELECT * FROM account_title ORDER BY account_title ASC";
                                                                $account_result = $connection->query($account_query);
                                                                while ($account = $account_result->fetch_assoc()) {
                                                                    echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="number" class="form-control debit-amount"
                                                                name="debit_amounts[]" step="0.01"></td>
                                                        <td><input type="number" class="form-control credit-amount"
                                                                name="credit_amounts[]" step="0.01"></td>
                                                        <td><button type="button"
                                                                class="btn btn-danger btn-sm delete-row"><i
                                                                    class="bi bi-trash"></i></button></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="2">
                                                            <select class="form-control account-select"
                                                                name="account_titles[]">
                                                                <option selected disabled>Select Cash Account</option>
                                                                <?php
                                                                // Define the specific account codes we want to show
                                                                $cashAccountCodes = ['1010404000', '1010405000', '1010406000'];

                                                                // Query only the specific cash accounts
                                                                $cash_account_query = "SELECT * FROM account_title WHERE account_code IN ('1010404000', '1010405000', '1010406000') ORDER BY account_title ASC";
                                                                $cash_account_result = $connection->query($cash_account_query);

                                                                while ($account = $cash_account_result->fetch_assoc()) {
                                                                    echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td><input type="number" class="form-control debit-amount"
                                                                name="debit_amounts[]" step="0.01" readonly></td>
                                                        <td><input type="number" class="form-control credit-amount"
                                                                name="credit_amounts[]" step="0.01" readonly></td>
                                                        <td><button type="button"
                                                                class="btn btn-danger btn-sm delete-row"><i
                                                                    class="bi bi-trash"></i></button></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <button type="button" id="addAccountRow"
                                                                class="btn btn-secondary"
                                                                style="padding: 5px 10px; font-size: 12px;">
                                                                <ion-icon name="add-outline"></ion-icon> Add Row
                                                            </button>
                                                        </td>
                                                        <td colspan="3"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- tax -->
                                    <div class="form-section">
                                        <h3>Breakdown of Expenses</h3>
                                        <div class="form-row">
                                            <div class="form-group half-width">
                                                <label class="form-label">Gross Amount</label>
                                                <input type="number" class="form-control" name="total_amount"
                                                    id="total_amount" step="0.01" readonly>
                                            </div>
                                            <div class="form-group half-width">
                                                <div class="checkbox-item">
                                                    <input type="checkbox" class="apply_taxes" id="apply_taxes">
                                                    <label for="apply_taxes">With VAT</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="tax_fields_container" class="tax-fields">
                                            <div class="form-row"></div>

                                            <div class="form-group half-width">
                                                <label class="form-label">VAT <input type="number"
                                                        class="tax-percentage" id="vat_percentage" name="vat" value="12"
                                                        min="0" max="100" readonly>%</label>
                                                <input type="number" class="form-control calculation-field"
                                                    id="vat_amount" name="vat_amount" step="0.01" readonly>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label class="form-label">Tax Base</label>
                                                <input type="number" class="form-control calculation-field"
                                                    id="tax_base" name="tax_base" step="0.01">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Less: <input type="number"
                                                        class="tax-percentage" id="tax1_percentage" name="tax_1"
                                                        value="5" min="0" max="100"> %
                                                    Tax</label>
                                                <input type="number" class="form-control calculation-field" id="tax_1"
                                                    name="tax_1_amount" step="0.01">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Less: <input type="number"
                                                        class="tax-percentage" id="tax2_percentage" name="tax_2"
                                                        value="2" min="0" max="100"> %
                                                    Tax</label>
                                                <input type="number" class="form-control calculation-field" id="tax_2"
                                                    name="tax_2_amount" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Net Amount</label>
                                            <input type="number" class="form-control calculation-field" id="net_amount"
                                                name="net_amount" step="0.01" readonly>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <h3>Approver Details</h3>
                                        <div class="form-row">

                                            <div class="form-group">
                                                <label class="form-label" id="designationLabel">Designation</label>
                                                <select class="form-control" id="approverSelect" name="approver_id">
                                                    <option value="">Select Approver</option>
                                                    <?php
                                                    foreach ($approverData as $approver_id => $data) {
                                                        echo "<option value='" . htmlspecialchars($approver_id) . "' data-designation='" . htmlspecialchars($data['designation']) . "'>" . htmlspecialchars($data['name']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Chief Accountant</label>
                                                <select class="form-control" name="chief_accountant">
                                                    <option>NEIL ANTHONY T. MORALA</option>

                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Regional Director</label>
                                                <select class="form-control" name="regional_director">
                                                    <option>FLORA D. POLITUD-GABUNALES, CESO V</option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <script>
        document.querySelectorAll('tfoot .credit-amount').forEach(function (input) {
            input.addEventListener('input', function () {
                document.getElementById('total_amount').value = this.value;
            });
        });

    </script>

    <!-- approver -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const approverSelect = document.getElementById("approverSelect");
            const designationLabel = document.getElementById("designationLabel");

            approverSelect.addEventListener("change", function () {

                const selectedOption = approverSelect.options[approverSelect.selectedIndex];
                const designation = selectedOption.getAttribute("data-designation") || "Designation";

                designationLabel.textContent = designation;
            });
        });
    </script>

    <!-- services -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const oopapSelect = document.querySelector('select[name="oopap_id"]');
            const servicesSelect = document.getElementById('services');
            const dateInput = document.getElementById('dvDate');
            const orsNoInput = document.getElementById('ors_no');

            // Function to update services dropdown
            function updateServices(oopapId) {
                if (!oopapId) {
                    servicesSelect.innerHTML = '<option selected disabled>Select Services</option>';
                    return;
                }

                fetch('get_filtered_services.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `oopap_id=${oopapId}`
                })
                    .then(response => response.json())
                    .then(services => {
                        servicesSelect.innerHTML = '<option selected disabled>Select Services</option>';
                        services.forEach(service => {
                            const option = document.createElement('option');
                            option.value = service.services_id;
                            option.textContent = service.services_name;
                            option.setAttribute('data-code', service.code);
                            servicesSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        servicesSelect.innerHTML = '<option selected disabled>Error loading services</option>';
                    });
            }

            // Listen for OO/PAP selection changes
            oopapSelect.addEventListener('change', function () {
                updateServices(this.value);
            });

            function generateORSNumber() {
                const selectedService = servicesSelect.options[servicesSelect.selectedIndex];
                const selectedDate = dateInput.value;

                if (!selectedService || selectedService.disabled || !selectedDate) {
                    return;
                }

                const serviceCode = selectedService.getAttribute('data-code');
                const date = new Date(selectedDate);
                const year = date.getFullYear().toString().substr(-2);
                const month = String(date.getMonth() + 1).padStart(2, '0');

                // Check if the service code is ADMIN&POLICY
                if (serviceCode === 'ADMIN&POLICY') {
                    fetch('get_next_ors_sequence.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `service_code=ADMIN&POLICY&year=${year}&month=${month}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            const sequence = String(data.next_sequence).padStart(3, '0');
                            orsNoInput.value = `ADMIN&POLICY-${year}-${month}-${sequence}`;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                } else {
                    fetch('get_next_ors_sequence.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `service_code=${serviceCode}&year=${year}&month=${month}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            const sequence = String(data.next_sequence).padStart(3, '0');
                            orsNoInput.value = `${serviceCode}-${year}-${month}-${sequence}`;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            }

            servicesSelect.addEventListener('change', generateORSNumber);
            dateInput.addEventListener('change', generateORSNumber);
        });
    </script>

    <!-- payee -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#payee_id').on('change', function () {
                var selectedOption = $(this).find('option:selected');
                var tinNo = selectedOption.data('tin');
                var address = selectedOption.data('address');

                $('#tin_no').val(tinNo);
                $('#address').val(address);
            });
        });
    </script>

    <!-- add row and calculate totals -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.getElementById('accountingTableBody');

            // Function to update UACS code when account is selected
            function setupAccountSelect(row) {
                const accountSelect = row.querySelector('.account-select');
                const uacsInput = row.querySelector('.uacs-code');

                accountSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    uacsInput.value = selectedOption.getAttribute('data-uacs') || '';
                });
            }

            // Function to calculate totals
            function calculateTotals() {
                let totalDebit = 0;
                let totalCredit = 0;

                // Get all debit and credit inputs except the footer row
                const debitInputs = document.querySelectorAll('tbody .debit-amount');
                const creditInputs = document.querySelectorAll('tbody .credit-amount');
                const totalAmountInput = document.getElementById('total_amount');
                const taxBaseInput = document.getElementById('tax_base');

                // Sum up debit amounts
                debitInputs.forEach(input => {
                    totalDebit += parseFloat(input.value || 0);
                });

                // Sum up credit amounts
                creditInputs.forEach(input => {
                    totalCredit += parseFloat(input.value || 0);
                });

                // Calculate the difference (total debit - total credit)
                const difference = totalDebit - totalCredit;

                // Update the footer row's credit field with the difference
                const footerCreditInput = document.querySelector('tfoot .credit-amount');
                if (footerCreditInput) {
                    footerCreditInput.value = difference.toFixed(2);
                }

                if (totalAmountInput) {
                    totalAmountInput.value = difference.toFixed(2); // Update the total_amount field
                }


                if (taxBaseInput) {
                    taxBaseInput.value = difference.toFixed(2); // Update the total_amount field
                }


            }

            // Function to filter account titles
            function filterAccountTitles(select, selectedType) {
                const currentValue = select.value;
                Array.from(select.options).forEach(option => {
                    if (option.value === "") return; // Skip the "Select Account" option

                    const accountTitle = option.getAttribute('data-title')?.toLowerCase() || '';
                    const accountCode = option.getAttribute('data-uacs') || '';
                    if (selectedType === "cash_advance") {
                        option.hidden = !accountTitle.includes('advance');
                    } else if (selectedType === "transfer_fund") {
                        option.hidden = !(accountTitle.includes('cash') && accountCode.startsWith('10'));
                    } else {
                        option.hidden = false;
                    }
                });

                // Restore selection if it's still valid
                if (currentValue && select.querySelector(`option[value="${currentValue}"]`)) {
                    select.value = currentValue;
                }
            }

            // Add event listener for the "Add Row" button
            document.getElementById('addAccountRow').addEventListener('click', function () {
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td colspan="2">
                        <select class="form-control account-select" name="account_titles[]">
                            <option selected disabled>Select Account</option>
                            <?php
                            $account_result->data_seek(0);
                            while ($account = $account_result->fetch_assoc()) {
                                echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01"></td>
                    <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01"></td>
                    <td><button type="button" class="btn btn-danger btn-sm delete-row"><i class="bi bi-trash"></i></button></td>
                `;

                tableBody.appendChild(newRow);
                setupAccountSelect(newRow);
                setupCalculationListeners(newRow);

                // Filter account titles for the new row
                const orsTypeSelect = document.getElementById("ors_type");
                const accountSelect = newRow.querySelector('.account-select');
                filterAccountTitles(accountSelect, orsTypeSelect.value);
            });

            // Function to setup calculation listeners for a row
            function setupCalculationListeners(row) {
                const debitInput = row.querySelector('.debit-amount');
                const creditInput = row.querySelector('.credit-amount');
                const deleteButton = row.querySelector('.delete-row');

                debitInput.addEventListener('input', function () {
                    if (this.value && parseFloat(this.value) > 0) {
                        creditInput.value = ''; // Clear credit when debit has value
                    }
                    calculateTotals();
                });

                creditInput.addEventListener('input', function () {
                    if (this.value && parseFloat(this.value) > 0) {
                        debitInput.value = ''; // Clear debit when credit has value
                    }
                    calculateTotals();
                });
            }

            // Setup initial row
            const initialRow = tableBody.querySelector('tr');
            setupAccountSelect(initialRow);
            setupCalculationListeners(initialRow);

            // Add event listener for DV type changes
            document.getElementById('ors_type').addEventListener('change', function () {
                const selectedType = this.value;
                const accountSelects = document.querySelectorAll('.account-select');
                accountSelects.forEach(select => {
                    filterAccountTitles(select, selectedType);
                });
            });
        });
    </script>

    <!-- dv number -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            generateDVNumber(); // Call function when page loads

            // Re-fetch DV number when fund cluster input changes
            let fundClusterInput = document.getElementById("fund_cluster");
            if (fundClusterInput) {
                fundClusterInput.addEventListener("input", generateDVNumber);
            } else {
                console.error("Fund cluster input field not found!");
            }

            // Re-fetch DV number when date input changes
            let dateInput = document.getElementById("date");
            if (dateInput) {
                dateInput.addEventListener("change", generateDVNumber);
            } else {
                console.error("Date input field not found!");
            }
        });

        function generateDVNumber() {
            let fundClusterInput = document.getElementById("fund_cluster");
            let dateInput = document.getElementById("date");

            if (!fundClusterInput) {
                console.error("Fund cluster input field not found!");
                return;
            }

            let fundClusterValue = fundClusterInput.value.trim();
            let fundClusterNumber = fundClusterValue.match(/^\d+/); // Extract only the leading number

            if (!fundClusterNumber) {
                console.error("Fund cluster ID is missing or invalid!");
                return;
            }

            let formData = new FormData();
            formData.append("fund_cluster_id", fundClusterNumber[0]); // Send only the number

            // Add date parameter if available
            if (dateInput && dateInput.value) {
                formData.append("date", dateInput.value);
            }

            fetch("fetch_dv_number.php", {
                method: "POST",
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched DV Data:", data); // Debugging
                    let dvNoInput = document.getElementById("dv_no");

                    if (dvNoInput) {
                        if (data.success) {
                            dvNoInput.value = data.dv_no;
                            console.log("DV No Set:", dvNoInput.value);
                        } else {
                            console.error("Error fetching DV number:", data.error);
                        }
                    } else {
                        console.error("DV Number input field not found!");
                    }
                })
                .catch(error => console.error("Fetch error:", error));
        }


    </script>

    <!-- tax calculation -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const amountInput = document.getElementById("total_amount");
            const applyTaxesCheckbox = document.getElementById("apply_taxes");
            const vatPercentageInput = document.getElementById("vat_percentage");
            const tax1PercentageInput = document.getElementById("tax1_percentage");
            const tax2PercentageInput = document.getElementById("tax2_percentage");

            const vatAmountInput = document.getElementById("vat_amount");
            const taxBaseInput = document.getElementById("tax_base");
            const tax1Input = document.getElementById("tax_1");
            const tax2Input = document.getElementById("tax_2");
            const netAmountInput = document.getElementById("net_amount");

            // Make tax fields editable or readonly based on VAT checkbox
            function setTaxFieldsEditability() {
                const isVatChecked = applyTaxesCheckbox.checked;
                console.log("Setting editability, VAT checked:", isVatChecked);

                // Explicitly set or remove readonly attribute
                if (isVatChecked) {
                    tax1PercentageInput.setAttribute("readonly", "readonly");
                    tax2PercentageInput.setAttribute("readonly", "readonly");
                    tax1Input.setAttribute("readonly", "readonly");
                    tax2Input.setAttribute("readonly", "readonly");
                } else {
                    tax1PercentageInput.removeAttribute("readonly");
                    tax2PercentageInput.removeAttribute("readonly");
                    tax1Input.removeAttribute("readonly");
                    tax2Input.removeAttribute("readonly");
                }

                // Update style to visually indicate if editable or not
                tax1PercentageInput.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax2PercentageInput.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax1Input.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax2Input.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";

                console.log("Tax1 input readonly:", tax1Input.readOnly);
                console.log("Tax1 percentage readonly:", tax1PercentageInput.readOnly);
            }

            // Recalculate tax amounts when tax percentages change
            function recalculateTaxAmounts() {
                if (applyTaxesCheckbox.checked) {
                    return; // Don't manually recalculate if VAT is checked
                }

                const grossAmount = parseFloat(taxBaseInput.value) || 0;
                const tax1Percentage = parseFloat(tax1PercentageInput.value) || 0;
                const tax2Percentage = parseFloat(tax2PercentageInput.value) || 0;

                // Calculate tax amounts based on percentages
                const tax1Amount = grossAmount * (tax1Percentage / 100);
                const tax2Amount = grossAmount * (tax2Percentage / 100);

                // Update tax amount fields
                tax1Input.value = tax1Amount.toFixed(2);
                tax2Input.value = tax2Amount.toFixed(2);

                // Recalculate net amount
                recalculateNetAmount();
            }

            // Recalculate net amount when tax amounts are manually edited
            function recalculateNetAmount() {
                const grossAmount = parseFloat(amountInput.value) || 0;
                const tax1Amount = parseFloat(tax1Input.value) || 0;
                const tax2Amount = parseFloat(tax2Input.value) || 0;

                // Calculate net amount
                const totalTaxes = tax1Amount + tax2Amount;
                const netAmount = grossAmount - totalTaxes;

                // Update net amount field
                netAmountInput.value = netAmount.toFixed(2);

                // Debug output to help troubleshoot
                console.log("Net amount calculation:", {
                    grossAmount,
                    tax1Amount,
                    tax2Amount,
                    totalTaxes,
                    netAmount
                });
            }

            // Main calculation function
            window.calculate = function () {
                const grossAmount = parseFloat(amountInput.value) || 0;

                console.log("Running calculate() with gross amount:", grossAmount);

                if (applyTaxesCheckbox.checked) {
                    // With VAT calculation
                    // VAT calculation (12% of gross)
                    const vatPercentage = 12;
                    const vatAmount = (grossAmount * vatPercentage) / (100 + vatPercentage);

                    // Tax base is gross minus VAT
                    const taxBase = grossAmount - vatAmount;

                    // Calculate 5% and 2% from tax base
                    const tax1Amount = taxBase * 0.05; // 5% with VAT
                    const tax2Amount = taxBase * 0.02; // 2% with VAT

                    // Update tax percentage displays
                    tax1PercentageInput.value = "5";
                    tax2PercentageInput.value = "2";

                    // Net amount is gross amount minus the sum of taxes
                    const totalTaxes = tax1Amount + tax2Amount;
                    const netAmount = grossAmount - totalTaxes;

                    // Update form fields
                    vatAmountInput.value = vatAmount.toFixed(2);
                    taxBaseInput.value = taxBase.toFixed(2);
                    tax1Input.value = tax1Amount.toFixed(2);
                    tax2Input.value = tax2Amount.toFixed(2);
                    netAmountInput.value = netAmount.toFixed(2);

                    // Show tax fields
                    document.getElementById('tax_fields_container').style.display = 'block';
                } else {
                    // Without VAT - use 0% tax rates as default
                    if (tax1PercentageInput.value === "" || tax1PercentageInput.value === "5") {
                        tax1PercentageInput.value = "0";
                    }
                    if (tax2PercentageInput.value === "" || tax2PercentageInput.value === "2") {
                        tax2PercentageInput.value = "0";
                    }

                    // Calculate tax amounts based on percentages
                    const tax1Percentage = parseFloat(tax1PercentageInput.value) || 0;
                    const tax2Percentage = parseFloat(tax2PercentageInput.value) || 0;

                    const tax1Amount = grossAmount * (tax1Percentage / 100);
                    const tax2Amount = grossAmount * (tax2Percentage / 100);

                    // Net amount is gross amount minus the sum of taxes
                    const totalTaxes = tax1Amount + tax2Amount;
                    const netAmount = grossAmount - totalTaxes;

                    // Update form fields
                    vatAmountInput.value = "0.00";
                    taxBaseInput.value = grossAmount.toFixed(2);
                    tax1Input.value = tax1Amount.toFixed(2);
                    tax2Input.value = tax2Amount.toFixed(2);
                    netAmountInput.value = netAmount.toFixed(2);

                    // Hide VAT fields
                    document.getElementById('tax_fields_container').style.display = 'none';
                }

                // Set fields editability based on VAT checkbox
                setTaxFieldsEditability();
            };

            // Add event listeners
            amountInput.addEventListener("input", calculate);

            // Special handling for checkbox to ensure it triggers editability changes
            applyTaxesCheckbox.addEventListener("change", function () {
                console.log("VAT checkbox changed to:", this.checked);
                setTaxFieldsEditability();
                calculate();
            });

            // Add event listeners for tax percentage fields
            tax1PercentageInput.addEventListener("input", function () {
                console.log("Tax1 percentage changed to:", this.value);
                if (!applyTaxesCheckbox.checked) {
                    recalculateTaxAmounts();
                }
            });

            tax2PercentageInput.addEventListener("input", function () {
                console.log("Tax2 percentage changed to:", this.value);
                if (!applyTaxesCheckbox.checked) {
                    recalculateTaxAmounts();
                }
            });

            // Add event listeners for tax amount fields (when editable)
            tax1Input.addEventListener("input", function () {
                console.log("Tax1 amount changed to:", this.value);
                if (!applyTaxesCheckbox.checked) {
                    recalculateNetAmount();
                }
            });

            tax2Input.addEventListener("input", function () {
                console.log("Tax2 amount changed to:", this.value);
                if (!applyTaxesCheckbox.checked) {
                    recalculateNetAmount();
                }
            });

            // Initial setup
            console.log("Initial setup - setting field editability");
            setTaxFieldsEditability();

            // Only call calculate() if this isn't a modal situation
            if (!document.getElementById('dvFormModal')) {
                console.log("Running initial calculation");
                calculate();
            }
        });
    </script>

    <!-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updateTotals() {
                let totalAmount = 0;

                // Include all debit and credit amounts (including those in tfoot)
                document.querySelectorAll('.debit-amount, .credit-amount').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    totalAmount += val;
                });

                // Update total_amount field
                document.getElementById('total_amount').value = totalAmount.toFixed(2);

                // If tax base is empty, set it to total_amount
                let taxBaseInput = document.getElementById('tax_base');
                if (!taxBaseInput.value) {
                    taxBaseInput.value = totalAmount.toFixed(2);
                }

                updateTaxes();
            }

            function updateTaxes() {
                let taxBase = parseFloat(document.getElementById('tax_base').value) || 0;

                // VAT calculation
                let vatPercentage = parseFloat(document.getElementById('vat_percentage').value) || 0;
                let vatAmount = document.getElementById('apply_taxes').checked ? taxBase * (vatPercentage / 100) : 0;
                document.getElementById('vat_amount').value = vatAmount.toFixed(2);

                // Tax 1
                let tax1Percentage = parseFloat(document.getElementById('tax1_percentage').value) || 0;
                let tax1Amount = taxBase * (tax1Percentage / 100);
                document.getElementById('tax_1').value = tax1Amount.toFixed(2);

                // Tax 2
                let tax2Percentage = parseFloat(document.getElementById('tax2_percentage').value) || 0;
                let tax2Amount = taxBase * (tax2Percentage / 100);
                document.getElementById('tax_2').value = tax2Amount.toFixed(2);

                // Net Amount
                let totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
                let netAmount = totalAmount + vatAmount - tax1Amount - tax2Amount;
                document.getElementById('net_amount').value = netAmount.toFixed(2);
            }

            // Listen to changes on debit, credit fields, tax base, tax percentages, and VAT toggle
            document.addEventListener('input', function (e) {
                if (
                    e.target.classList.contains('debit-amount') ||
                    e.target.classList.contains('credit-amount') ||
                    e.target.id === 'tax_base' ||
                    e.target.id === 'tax1_percentage' ||
                    e.target.id === 'tax2_percentage'
                ) {
                    if (e.target.classList.contains('debit-amount') || e.target.classList.contains('credit-amount')) {
                        updateTotals();
                    } else {
                        updateTaxes();
                    }
                }
            });

            // VAT toggle listener
            document.getElementById('apply_taxes').addEventListener('change', function () {
                updateTaxes();
            });
        });

    </script> -->


</body>

</html>