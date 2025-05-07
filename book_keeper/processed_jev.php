<?php
include '../DBConnection.php';


// retrieve ors
$select = mysqli_query($connection, "
    SELECT ors.*, jev.*,
    payee.payee_name,
    ors.total_amount

    FROM jev
    LEFT JOIN ors ON jev.ors_id = ors.ors_id
    LEFT JOIN payee ON ors.payee_id = payee.payee_id

");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Bookkeeper - Journal Entry Voucher </title>
    <meta content="" name="description">
    <meta content="" name="keywords">
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
    <link rel="stylesheet" href="css/table.css">

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
                <button class="btn btn-primary" onclick="window.location.href='jev.php'">
                    JEV Form
                </button>
            </div>
        </div>


        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Journal Entry Voucher</h2>

                <div class="tab-content">
                    <div class="card">
                        <div class="card-body">
                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>JEV No.</th>
                                        <th>Payee Name</th>
                                        <th>Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $date = new DateTime($row['date']);
                                                echo htmlspecialchars($date->format('F j, Y'));
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['jev_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                                            <td>
                                                <a href="jev_form.php?jev_no=<?php echo urlencode($row['jev_no']); ?>"
                                                    class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

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

</body>

</html>