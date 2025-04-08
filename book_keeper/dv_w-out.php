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
                                            <input type="date" class="form-control" id="dvDate" name="date">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">DV No.</label>
                                            <input type="text" class="form-control" name="ors_no" id="ors_no" required
                                                readonly>
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

</body>

</html>