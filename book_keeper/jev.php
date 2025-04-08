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
    $dv_no = $_POST['dv_no']; // changed from dv_id to dv_no
    $ors_no = $_POST['ors_no']; // clarified this is ORS number
    $jev_no = $_POST['jev_no'];
    $administrative_aide = $_POST['administrative_aide'];
    $accountant = $_POST['accountant'];

    // Start a transaction
    $connection->begin_transaction();

    try {
        // Get ors_id from ors_no
        $ors_query = "SELECT ors_id FROM ors WHERE ors_no = ?";
        $ors_stmt = $connection->prepare($ors_query);
        if ($ors_stmt === false) {
            throw new Exception('Prepare failed (ORS): ' . htmlspecialchars($connection->error));
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

        // Get dv_id from dv_no
        $dv_query = "SELECT dv_id FROM dv WHERE dv_no = ?";
        $dv_stmt = $connection->prepare($dv_query);
        if ($dv_stmt === false) {
            throw new Exception('Prepare failed (DV): ' . htmlspecialchars($connection->error));
        }
        $dv_stmt->bind_param("s", $dv_no);
        if (!$dv_stmt->execute()) {
            throw new Exception("Error getting DV ID: " . $dv_stmt->error);
        }
        $dv_result = $dv_stmt->get_result();
        if ($dv_result->num_rows === 0) {
            throw new Exception("DV number not found: " . $dv_no);
        }
        $dv_row = $dv_result->fetch_assoc();
        $dv_id = $dv_row['dv_id'];
        $dv_stmt->close();

        // Insert into jev table
        $sql = "INSERT INTO jev (date, dv_id, ors_id, jev_no, administrative_aide, accountant) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare failed (JEV insert): ' . htmlspecialchars($connection->error));
        }

        $stmt->bind_param(
            "siiiss",
            $date,
            $dv_id,
            $ors_id,
            $jev_no,
            $administrative_aide,
            $accountant
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert error: " . $stmt->error);
        }

        $stmt->close();

        // Update the dv status to 'Processed'
        $update_status_sql = "UPDATE dv SET status = 'Endorsed' WHERE dv_id = ?";
        $update_status_stmt = $connection->prepare($update_status_sql);
        if ($update_status_stmt === false) {
            throw new Exception('Prepare failed (ORS update): ' . htmlspecialchars($connection->error));
        }

        $update_status_stmt->bind_param("i", $dv_id);
        if (!$update_status_stmt->execute()) {
            throw new Exception("Error updating ORS status: " . $update_status_stmt->error);
        }
        $update_status_stmt->close();

        // Commit transaction
        $connection->commit();

        // Redirect
        header("Location: jev_form.php?jev_no=$jev_no");
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }

    $connection->close();
}


// retrieve_dv
$select_dv = mysqli_query($connection, "
SELECT 
    ors.*,
    ors.total_amount AS ors_total_amount,
    dv.*, 
    account_title.account_title, 
    account_title.account_code,
    approver.approver_name,
    CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
    responsibility_center.code,
    oopap.oopap_name,
    payee.payee_name,
    payee.tin_no,
    payee.address
FROM dv
LEFT JOIN ors ON dv.ors_id = ors.ors_id
LEFT JOIN account_title ON ors.account_id = account_title.account_id
LEFT JOIN approver ON ors.approver_id = approver.approver_id
LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
LEFT JOIN payee ON ors.payee_id = payee.payee_id

WHERE dv.status = 'Pending'
;


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
            <h1 class="mb-0">Journal Entry Voucher</h1>

            <!-- Buttons Container with right alignment -->
            <div class="ms-auto">
                <button class="btn btn-primary" onclick="window.location.href='processed_jev.php'">
                    View Processed JEV
                </button>
            </div>
        </div>



        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Journal Entry Voucher</h2>

                <div class="tab-content">
                    <!-- DV List Tab -->
                    <div>
                        <div class="card">
                            <div class="card-body">
                                <!-- Table with stripped rows -->
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th>DV No.</th>
                                            <th>Date</th>
                                            <th>Payee Name</th>
                                            <th>Account Title</th>
                                            <th>Amount</th>
                                            <th>Approver</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($select_dv)) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['dv_no']); ?></td>
                                                <td>
                                                    <?php
                                                    $date = new DateTime($row['date']);
                                                    echo htmlspecialchars($date->format('F j, Y')); // Example: "April 7, 2025"
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['ors_total_amount']); ?></td>
                                                <td><?php echo htmlspecialchars($row['approver_name']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-primary view-details"
                                                        data-id="<?php echo $row['dv_id']; ?>">
                                                        <i class="bi bi-eye" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title="View Details"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </main>

    <!-- Modal for DV Form -->
    <div id="dvFormModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Journal Entry Voucher</h2>
                <span class="close-modal" id="closeDvModal">&times;</span>
            </div>

            <div class="modal-body">

                <form action="" method="post">
                    <div class="form-container">
                        <div class="form-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">ORS No.</label>
                                    <input type="text" class="form-control" id="ors_no" name="ors_no" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">DV No.</label>
                                    <input type="text" class="form-control" id="dv_no" name="dv_no" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">JEV No.</label>
                                    <input type="text" class="form-control" name="jev_no" autocomplete="off">
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
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>Account Name</th>
                                    <th>UACS Object Code</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select_dv)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>


                        <div class="form-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Administrative Aide</label>
                                    <select class="form-control" name="administrative_aide">
                                        <option>JINNARD B. LUBATON</option>

                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Accountant III</label>
                                    <select class="form-control" name="accountant">
                                        <option>NEIL ANTHONY T. MORALA</option>

                                    </select>
                                </div>
                            </div>
                        </div>


                        <!-- Buttons -->
                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary" name="submit">Print</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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

        <!-- Custom Script for Modal -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('dvFormModal');
                const closeModalBtn = document.getElementById('closeDvModal');
                const viewDetailsButtons = document.querySelectorAll('.view-details');

                // Open modal and populate data
                viewDetailsButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const dvId = this.getAttribute('data-id');
                        fetch(`get_jev_details.php?id=${dvId}`)
                            .then(response => response.json())
                            .then(data => {
                                // Populate the form fields with the returned data
                                document.getElementById('ors_no').value = data.ors_no;
                                document.getElementById('dv_no').value = data.dv_no;
                                document.getElementById('payee_name').value = data.payee_name;

                                // Populate the accounts table dynamically
                                const tableBody = document.querySelector('#dvFormModal table tbody');
                                tableBody.innerHTML = '';  // Clear any existing rows

                                // Initialize totals
                                let totalDebit = 0;
                                let totalCredit = 0;

                                // Add account rows
                                data.accounts.forEach(account => {
                                    const row = document.createElement('tr');
                                    const amount = parseFloat(account.amount) || 0;

                                    // Determine if this should be debit or credit based on account type
                                    const isDebit = account.type === 'debit' || account.type === 'asset' || account.type === 'expense';
                                    const debitAmount = isDebit ? amount : 0;
                                    const creditAmount = !isDebit ? amount : 0;

                                    // Add to totals
                                    totalDebit += debitAmount;
                                    totalCredit += creditAmount;

                                    row.innerHTML = `
                            <td>${account.account_title}</td>
                            <td>${account.account_code}</td>
                            <td>${debitAmount.toFixed(2)}</td>
                            <td>${creditAmount.toFixed(2)}</td>
                        `;
                                    tableBody.appendChild(row);
                                });

                                // Add totals row
                                const totalsRow = document.createElement('tr');
                                totalsRow.className = 'table-active';
                                totalsRow.innerHTML = `
                        <td colspan="2"><strong>TOTAL</strong></td>
                        <td><strong>${totalDebit.toFixed(2)}</strong></td>
                        <td><strong>${totalCredit.toFixed(2)}</strong></td>
                    `;
                                tableBody.appendChild(totalsRow);

                                // Optional: Add balance check row
                                if (Math.abs(totalDebit - totalCredit) > 0.01) { // Use small threshold for floating point comparison
                                    const balanceRow = document.createElement('tr');
                                    balanceRow.className = 'table-danger';
                                    balanceRow.innerHTML = `
                            <td colspan="2"><strong>OUT OF BALANCE</strong></td>
                            <td colspan="2" class="text-center"><strong>${Math.abs(totalDebit - totalCredit).toFixed(2)}</strong></td>
                        `;
                                    tableBody.appendChild(balanceRow);
                                }

                                // Show modal
                                modal.style.display = 'block';

                                // Optionally trigger any additional calculations or setup here
                                setTimeout(() => {
                                    calculate(); // Trigger calculations if needed
                                    generateDVNumber(); // Generate DV number if needed
                                }, 100);
                            })
                            .catch(error => console.error('Error fetching DV details:', error));
                    });
                });

                // Close modal
                closeModalBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });

                // Close modal if clicked outside the modal content
                window.addEventListener('click', function (e) {
                    if (e.target === modal) {
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

                calculate = function () { // Assign calculate function to the wider scope variable
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

                        // Add BIR-related rows
                        addBIRRows(tax1Amount, tax2Amount);
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

                        // Add BIR-related rows
                        addBIRRows(tax1Amount, tax2Amount);
                    }
                };

                // Function to add BIR-related rows
                // function addBIRRows(tax1Amount, tax2Amount) {
                //     const tableBody = document.getElementById('accountingTableBody');

                //     // Clear existing BIR rows
                //     const existingRows = tableBody.querySelectorAll('tr');
                //     existingRows.forEach(row => {
                //         if (row.querySelector('.account-select')?.value === 'BIR') {
                //             row.remove();
                //         }
                //     });

                //     // Add rows only if there are tax amounts
                //     if (tax1Amount > 0 || tax2Amount > 0) {
                //         // Add first BIR row
                //         const row1 = document.createElement('tr');
                //         row1.innerHTML = `
                //             <td colspan="2">
                //                 <select class="form-control account-select" name="account_titles[]">
                //                     <option value="BIR" selected>Due to BIR - 2020101000</option>
                //                 </select>
                //             </td>
                //             <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01"></td>
                //             <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" value="${tax1Amount.toFixed(2)}" readonly></td>
                //         `;
                //         tableBody.appendChild(row1);

                //         // Add second BIR row
                //         const row2 = document.createElement('tr');
                //         row2.innerHTML = `
                //             <td colspan="2">
                //                 <select class="form-control account-select" name="account_titles[]">
                //                     <option value="BIR" selected>Due to BIR - 2020101000</option>
                //                 </select>
                //             </td>
                //             <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01"></td>
                //             <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" value="${tax2Amount.toFixed(2)}" readonly></td>
                //         `;
                //         tableBody.appendChild(row2);

                //         // Setup calculation listeners for new rows
                //         setupCalculationListeners(row1);
                //         setupCalculationListeners(row2);
                //     }
                // }

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

        <!-- show form after selecting ors type  -->
        <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            const orsTypeSelect = document.getElementById("ors_type");
            const orsForm = document.getElementById("dv_form");

            orsTypeSelect.addEventListener("change", function () {
                if (this.value) {
                    orsForm.style.display = "block";  // Show the form
                }
            });
        });
    </script> -->

        <!-- account_title -->
        <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            const orsTypeSelect = document.getElementById("ors_type");

            function filterAccountTitles() {
                const selectedType = orsTypeSelect.value;
                const accountSelects = document.querySelectorAll('.account-select');

                accountSelects.forEach(select => {
                    const currentValue = select.value;
                    const currentTitle = select.options[select.selectedIndex]?.getAttribute('data-title') || '';

                    Array.from(select.options).forEach(option => {
                        if (option.value === "") return;

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
                });
            }

            // Filter on initial load and when DV type changes
            orsTypeSelect.addEventListener("change", filterAccountTitles);

            // Also filter when new rows are added
            document.getElementById('addAccountRow').addEventListener('click', function () {
                setTimeout(filterAccountTitles, 0);
            });
        });
    </script> -->

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

</body>

</html>