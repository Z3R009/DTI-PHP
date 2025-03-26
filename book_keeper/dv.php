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
    $ors_id = $_POST['ors_id'];
    $payment_mode = $_POST['payment_mode'];
    $vat = $_POST['vat'];
    $vat_amount = $_POST['vat_amount'];
    $tax_base = $_POST['tax_base'];
    $tax_1 = $_POST['tax_1'];
    $tax_1_amount = $_POST['tax_1_amount'];
    $tax_2 = $_POST['tax_2'];
    $tax_2_amount = $_POST['tax_2_amount'];
    $net_amount = $_POST['net_amount'];
    $account_id = $_POST['account_id'];
    $debit = $_POST['debit'];
    $credit = $_POST['credit'];
    $chief_accountant = $_POST['chief_accountant'];
    $regional_director = $_POST['regional_director'];
    $check_no = $_POST['check_no'];
    $bank_acc_no = $_POST['bank_acc_no'];

    $sql = "INSERT INTO dv (date, dv_no, ors_id, payment_mode, vat, vat_amount, tax_base, tax_1, tax_1_amount, tax_2, tax_2_amount, net_amount, account_id, debit, credit, chief_accountant, regional_director, check_no, bank_acc_no) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($connection->error));
    }

    $stmt->bind_param("ssisiiiiiiiiiiissss", $date, $dv_no, $ors_id, $payment_mode, $vat, $vat_amount, $tax_base, $tax_1, $tax_1_amount, $tax_2, $tax_2_amount, $net_amount, $account_id, $debit, $credit, $chief_accountant, $regional_director, $check_no, $bank_acc_no);
    if ($stmt->execute()) {
        header("Location: dv_form.php?dv_no=$dv_no");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
}

// retrieve
$select = mysqli_query($connection, "
    SELECT 
        ors.*, 
        account_title.account_title, 
        approver.approver_name,
        CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
        responsibility_center.code,
        oopap.oopap_name,
        payee.payee_name,
        payee.tin_no,
        payee.address
    FROM ors
    LEFT JOIN account_title ON ors.account_id = account_title.account_id
    LEFT JOIN approver ON ors.approver_id = approver.approver_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
    LEFT JOIN payee ON ors.payee_id = payee.payee_id
");

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
            border-top: 2px solid #cbd5e1;
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
    </style>
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Disbursement</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Obligation Request No.</th>
                                <th>Payee Name</th>
                                <th>Account Title</th>
                                <th>Amount</th>
                                <th>Approver</th>
                                <th>Budget Officer</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ors_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                                    <td><?php echo htmlspecialchars($row['approver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['budget_officer']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary view-details"
                                            data-id="<?php echo $row['ors_id']; ?>">
                                            <i class="bi bi-eye" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="View Details"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal -->
    <div id="dvFormModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Disbursement Voucher</h2>
                <span class="close-modal" id="closeDvModal">&times;</span>
            </div>
            <!-- ORS Type Selection -->
            <div class="form-group">
                <label class="form-label">Select ORS Type</label>
                <select class="form-control" id="ors_type">
                    <option value="" selected disabled>Select ORS Type</option>
                    <option value="cash_advance">Cash Advance</option>
                    <option value="transfer_fund">Transfer of Fund</option>
                </select>
            </div>


            <div class="modal-body">

                <div id="dv_form" style="display: none;">
                    <form action="" method="post">
                        <div class="form-container">
                            <div class="form-section">
                                <h3>General Information</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Fund Cluster</label>
                                        <input type="text" class="form-control" id="fund_cluster" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" id="date" name="date">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">ORS No.</label>
                                        <input type="text" class="form-control" id="ors_no" name="ors_id" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Disbursement Voucher No.</label>
                                        <input type="text" class="form-control" id="dv_no" name="dv_no" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Mode of Payment</h3>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <div class="checkbox-group">
                                            <div class="checkbox-item">
                                                <input type="checkbox" id="mds" name="payment_mode" value="MDS Check">
                                                <label for="mds">MDS Check</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox" id="commercial" name="payment_mode"
                                                    value="Commercial Check">
                                                <label for="commercial">Commercial Check</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox" id="ada" name="payment_mode" value="ADA">
                                                <label for="ada">ADA</label>
                                            </div>
                                            <div class="checkbox-item">
                                                <input type="checkbox" id="others" name="payment_mode" value="Others">
                                                <label for="others">Others (Specify):</label>
                                                <input type="text" class="form-control" id="otherText"
                                                    name="other_specify" style="width: 200px;" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payee Details Section -->
                                <div class="form-section">
                                    <h3>Payee Details</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Payee Name</label>
                                            <input type="text" class="form-control" id="payee_name" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">TIN/Employee No.</label>
                                            <input type="text" class="form-control" id="tin_no" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" id="address" readonly>
                                    </div>
                                </div>
                                <!-- Payment Details Section -->
                                <div class="form-section">
                                    <h3>Purpose</h3>
                                    <div class="form-row">
                                        <div class="form-group full-width">
                                            <textarea class="form-control" id="notes" readonly></textarea>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Responsibility Center</label>
                                            <input type="text" class="form-control" id="code" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">OO/PAP</label>
                                            <input type="text" class="form-control" id="oopap_name" readonly>
                                        </div>
                                        <!-- <div class="form-group">
                                        <label class="form-label">Amount</label>
                                        <input type="number" class="form-control" id="amount" step="0.01">
                                    </div> -->
                                    </div>
                                </div>

                                <!-- tax -->
                                <div class="form-section">
                                    <h3>Breakdown of Expenses</h3>
                                    <div class="form-row">
                                        <div class="form-group half-width">
                                            <label class="form-label">Gross Amount</label>
                                            <input type="number" class="form-control" id="total_amount" step="0.01"
                                                readonly>
                                        </div>
                                        <div class="form-group half-width">
                                            <div class="checkbox-item">
                                                <input type="checkbox" class="apply_taxes" id="apply_taxes">
                                                <label for="apply_taxes">With VAT</label>
                                            </div>

                                        </div>
                                    </div>

                                    <div id="tax_fields_container" class="tax-fields">
                                        <div class="form-row">

                                        </div>

                                        <div class="form-group half-width">
                                            <label class="form-label">VAT <input type="number" class="tax-percentage"
                                                    id="vat_percentage" name="vat" value="12" min="0" max="100"
                                                    step="0.01" readonly>
                                                %</label>
                                            <input type="number" class="form-control calculation-field" id="vat_amount"
                                                name="vat_amount" step="0.01" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Tax Base</label>
                                            <input type="number" class="form-control calculation-field" id="tax_base"
                                                name="tax_base" step="0.01">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Less: <input type="number" class="tax-percentage"
                                                    id="tax1_percentage" name="tax_1" value="5" min="0" max="100"
                                                    step="0.01"> % Tax</label>
                                            <input type="number" class="form-control calculation-field" id="tax_1"
                                                name="tax_1_amount" step="0.01">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Less: <input type="number" class="tax-percentage"
                                                    id="tax2_percentage" name="tax_2" value="2" min="0" max="100"
                                                    step="0.01"> % Tax</label>
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
                            </div>

                            <div class="form-section">
                                <h3>Accounting Entry</h3>
                                <div class="table-responsive">
                                    <table class="accounting-entry-table">
                                        <thead>
                                            <tr>
                                                <th>Account Title</th>
                                                <th>UACS Code</th>
                                                <th>Debit Amount</th>
                                                <th>Credit Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="accountingTableBody">
                                            <tr>
                                                <td>
                                                    <select class="form-control">
                                                        <option>Select Account</option>
                                                        <option>Supplies Expense</option>
                                                        <option>Traveling Expenses - Local</option>
                                                        <option>Representation Expenses</option>
                                                        <option>Accounts Payable</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control"></td>
                                                <td><input type="number" class="form-control debit-amount" step="0.01"
                                                        onchange="calculateTotals()"></td>
                                                <td><input type="number" class="form-control credit-amount" step="0.01"
                                                        onchange="calculateTotals()"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <select class="form-control">
                                                        <option>Select Account</option>
                                                        <option>Supplies Expense</option>
                                                        <option>Traveling Expenses - Local</option>
                                                        <option>Representation Expenses</option>
                                                        <option>Accounts Payable</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control"></td>
                                                <td><input type="number" class="form-control debit-amount" step="0.01"
                                                        onchange="calculateTotals()"></td>
                                                <td><input type="number" class="form-control credit-amount" step="0.01"
                                                        onchange="calculateTotals()"></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" style="text-align: right;"><strong>Totals:</strong></td>
                                                <td><input type="number" id="total-debit"
                                                        class="form-control calculation-field" readonly></td>
                                                <td><input type="number" id="total-credit"
                                                        class="form-control calculation-field" readonly></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <button type="button" id="addAccountRow" class="btn btn-secondary"
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

                            <!-- Approver Section -->
                            <div class="form-section">
                                <h3>Approver Details</h3>
                                <div class="form-row">
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


                            <!-- Buttons -->
                            <div class="btn-container">
                                <button type="submit" class="btn btn-primary" name="submit">Print</button>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

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

    <!-- Custom Script for Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('dvFormModal');
            const closeModalBtn = document.getElementById('closeDvModal');
            const viewDetailsButtons = document.querySelectorAll('.view-details');

            // Open modal and populate data
            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const orsId = this.getAttribute('data-id');
                    fetch(`get_ors_details.php?id=${orsId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('ors_no').value = data.ors_no;
                            document.getElementById('fund_cluster').value = data.fund_cluster;
                            document.getElementById('payee_name').value = data.payee_name;
                            document.getElementById('tin_no').value = data.tin_no;
                            document.getElementById('address').value = data.address;
                            document.getElementById('notes').value = data.notes;
                            document.getElementById('code').value = data.code;
                            document.getElementById('oopap_name').value = data.oopap_name;
                            document.getElementById('total_amount').value = data.total_amount;

                            // Trigger calculations after populating data
                            calculate(); // Add this line to trigger calculation
                            generateDVNumber();
                            
                            modal.style.display = 'block';
                        })
                        .catch(error => console.error('Error fetching ORS details:', error));
                });
            });

            // Close modal
            closeModalBtn.addEventListener('click', function () {
                modal.style.display = 'none';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>

    <!-- mode of payment -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const checkboxes = document.querySelectorAll('input[name="payment_mode"]');
            const otherText = document.getElementById('otherText');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        checkboxes.forEach(cb => {
                            if (cb !== this) {
                                cb.checked = false;
                            }
                        });

                        // Enable/Disable text field based on "Others" selection
                        if (this.id === "others") {
                            otherText.disabled = false;
                        } else {
                            otherText.disabled = true;
                            otherText.value = ""; // Clear input if another option is selected
                        }
                    }
                });
            });
        });
    </script>

    <!-- tax calculation -->
    <script>
        let calculate; // Declare calculate in wider scope

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

            calculate = function() { // Assign calculate function to the wider scope variable
                const grossAmount = parseFloat(amountInput.value) || 0;

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
                    // Without VAT - use 3% and 1% tax rates
                    const tax1Amount = grossAmount * 0.03; // 3% without VAT
                    const tax2Amount = grossAmount * 0.01; // 1% without VAT
                    
                    // Update tax percentage displays
                    tax1PercentageInput.value = "3";
                    tax2PercentageInput.value = "1";

                    // Net amount is gross amount minus the sum of taxes
                    const totalTaxes = tax1Amount + tax2Amount;
                    const netAmount = grossAmount - totalTaxes;

                    // Update form fields
                    vatAmountInput.value = "0.00";
                    taxBaseInput.value = grossAmount.toFixed(2);
                    tax1Input.value = tax1Amount.toFixed(2);
                    tax2Input.value = tax2Amount.toFixed(2);
                    netAmountInput.value = netAmount.toFixed(2);

                    // Hide VAT fields but show tax fields
                    document.getElementById('tax_fields_container').style.display = 'none';
                }
            };

            // Event listeners
            amountInput.addEventListener("input", calculate);
            applyTaxesCheckbox.addEventListener("change", calculate);

            // Initial calculation - trigger calculation as soon as the page loads
            calculate();
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
        });

        function generateDVNumber() {
            let fundClusterInput = document.getElementById("fund_cluster");
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

    <!-- show form after selecting ors type  -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const orsTypeSelect = document.getElementById("ors_type");
            const orsForm = document.getElementById("dv_form");

            orsTypeSelect.addEventListener("change", function () {
                if (this.value) {
                    orsForm.style.display = "block";  // Show the form
                }
            });
        });
    </script>

    <!-- account_title -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const orsTypeSelect = document.getElementById("ors_type");
            const accountTitleSelect = document.getElementById("account_title");

            orsTypeSelect.addEventListener("change", function () {
                const selectedType = this.value;

                Array.from(accountTitleSelect.options).forEach(option => {
                    const uacsCode = option.getAttribute("data-uacs");
                    if (!uacsCode) return; // Skip the "Select Account" option

                    if ((selectedType === "cash_advance" && uacsCode.startsWith("50")) ||
                        (selectedType === "transfer_fund" && uacsCode.startsWith("10"))) {
                        option.hidden = false;
                    } else {
                        option.hidden = true;
                    }
                });

                accountTitleSelect.value = ""; // Reset selection
            });
        });
    </script>

    <!-- add row  -->
    <script>
        document.getElementById('addAccountRow').addEventListener('click', function () {
            const tableBody = document.getElementById('accountingTableBody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td>
                    <select class="form-control">
                        <option>Select Account</option>
                        <option>Supplies Expense</option>
                        <option>Traveling Expenses - Local</option>
                        <option>Representation Expenses</option>
                        <option>Accounts Payable</option>
                    </select>
                </td>
                <td><input type="text" class="form-control"></td>
                <td><input type="number" class="form-control debit-amount" step="0.01" onchange="calculateTotals()"></td>
                <td><input type="number" class="form-control credit-amount" step="0.01" onchange="calculateTotals()"></td>
            `;

            tableBody.appendChild(newRow);
        });
    </script>

</body>

</html>