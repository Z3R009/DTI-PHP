<?php
include '../DBConnection.php';

// insert

// if (isset($_POST['submit'])) {
//     echo "Form submitted!";

//     // Debugging: Print all POST data
//     echo "<pre>";
//     print_r($_POST);
//     echo "</pre>";

//     $date = $_POST['date'];
//     $dv_no = $_POST['dv_no'];
//     $ors_id = $_POST['ors_id'];
//     $payment_mode = $_POST['payment_mode'];
//     $vat = $_POST['vat'];
//     $vat_amount = $_POST['vat_amount'];
//     $tax_base = $_POST['tax_base'];
//     $tax_1 = $_POST['tax_1'];
//     $tax_1_amount = $_POST['tax_1_amount'];
//     $tax_2 = $_POST['tax_2'];
//     $tax_2_amount = $_POST['tax_2_amount'];
//     $net_amount = $_POST['net_amount'];
//     $object_code_id = $_POST['object_code_id'];
//     $debit = $_POST['debit'];
//     $credit = $_POST['credit'];
//     $chief_accountant = $_POST['chief_accountant'];
//     $regional_director = $_POST['regional_director'];
//     $check_no = $_POST['check_no'];
//     $bank_acc_no = $_POST['bank_acc_no'];

//     $sql = "INSERT INTO dv (date, dv_no, ors_id, payment_mode, vat, vat_amount, tax_base, tax_1, tax_1_amount, tax_2, tax_2_amount, net_amount, object_code_id, debit, credit, chief_accountant, regional_director, check_no, bank_acc_no) 
//             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


//     $stmt = $connection->prepare($sql);
//     if ($stmt === false) {
//         die('Prepare failed: ' . htmlspecialchars($connection->error));
//     }

//     $stmt->bind_param("siisiiiiiiiiiiissss", $date, $dv_no, $ors_id, $payment_mode, $vat, $vat_amount, $tax_base, $tax_1, $tax_1_amount, $tax_2, $tax_2_amount, $net_amount, $object_code_id, $debit, $credit, $chief_accountant, $regional_director, $check_no, $bank_acc_no);
//     if ($stmt->execute()) {
//         header("Location: dv_form.php?dv_no=$dv_no");
//         exit();
//     } else {
//         echo "Error: " . $stmt->error;
//     }

//     $stmt->close();
//     $connection->close();
// }

// retrieve
$select = mysqli_query($connection, "
    SELECT dv.*, ors.*, 
    fund_cluster.fund_cluster_id,
    responsibility_center.rc_id,
    financial_object_code.object_code_id,
    oopap.oopap_id
    FROM dv
    INNER JOIN ors ON dv.ors_id = ors.ors_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
");

$sql_object_code = "SELECT object_code_id, object_name FROM financial_object_code";
$result_object_code = $connection->query($sql_object_code);

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

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="../img/DTI_short.png" alt="">
                <span class="d-none d-lg-block">Region 12</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->


        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">



                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <i class="ri-account-circle-fill fs-2"></i>
                        <span class="d-none d-md-block dropdown-toggle ps-2"></span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>Kevin Anderson</h6>
                            <span>Web Designer</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                                <i class="bi bi-gear"></i>
                                <span>Account Settings</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                                <i class="bi bi-question-circle"></i>
                                <span>Need Help?</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item">
                <a class="navbar-brand ps-3" href="">
                    <img src="../img/DTI_w12.png" alt="Logo" style="height: 100px; width: auto; max-width: 100%; ">
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="dashboard.php">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-bar-chart"></i><span>Forms</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="ors.php">
                            <i class="bi bi-circle"></i><span>Obligation Request and Status</span>
                        </a>
                    </li>
                    <li>
                        <a href="dv.php">
                            <i class="bi bi-circle"></i><span>Disbursement Voucher</span>
                        </a>
                    </li>
                    <li>
                        <a href="jev.php">
                            <i class="bi bi-circle"></i><span>JEV</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-menu-button-wide"></i><span>UACS</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="account_title.php">
                            <i class="bi bi-circle"></i><span>Account Title</span>
                        </a>
                    </li>
                    <li>
                        <a href="fund_cluster.php">
                            <i class="bi bi-circle"></i><span>Fund Cluster</span>
                        </a>
                    </li>
                    <li>
                        <a href="responsibility.php">
                            <i class="bi bi-circle"></i><span>Responsibility Center</span>
                        </a>
                    </li>
                    <li>
                        <a href="payee.php">
                            <i class="bi bi-circle"></i><span>Payee</span>
                        </a>
                    </li>

                    <li>
                        <a href="approver.php">
                            <i class="bi bi-circle"></i><span>Approver</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="reports_copy.php">
                    <i class="bi bi-journal-text"></i>
                    <span>Reports</span>
                </a>
            </li>



        </ul>

    </aside><!-- End Sidebar-->

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Journal Entry Voucher</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>DV No.</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['dv_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
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
                <h2 class="modal-title">Journal Entry Voucher</h2>
                <span class="close-modal" id="closeDvModal">&times;</span>
            </div>
            <div class="modal-body">

                <form action="" method="post">
                    <div class="form-container">
                        <div class="form-section">

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Entity Name</label>
                                    <input type="text" class="form-control" name="entity_name"
                                        value="DEPARTMENT OF TRADE AND INDUSTRY">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Fund Cluster</label>
                                    <input type="text" class="form-control" id="fund_cluster_id" name="fund_cluster_id">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Responsibility Center</label>
                                    <input type="text" class="form-control" id="rc_id" name="rc_id">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Payee Name</label>
                                    <input type="text" class="form-control" id="payee_name" name="payee_name">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="date">
                                </div>
                            </div>


                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">ORS No.</label>
                                    <input type="text" class="form-control" id="ors_id" name="ors_no">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">DV No.</label>
                                    <input type="text" class="form-control" id="dv_no" name="dv_no">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">JEV No.</label>
                                    <input type="text" class="form-control" name="jev_no">
                                </div>
                            </div>

                            <h3>Approver Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Administrative Aide VI</label>
                                    <select class="form-control" name="administrative_aide">
                                        <option>JINNARD B. LUBATON</option>

                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Accountant III</label>
                                    <select class="form-control" name="Accountant">
                                        <option>NEIL ANTHONY T. MORALA</option>

                                    </select>
                                </div>
                            </div>

                        </div>



                        <!-- Approver Section -->
                        <!-- <div class="form-section">
                                <h3>Approver Details</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Administrative Aide VI</label>
                                        <select class="form-control" name="administrative_aide">
                                            <option>JINNARD B. LUBATON</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Accountant III</label>
                                        <select class="form-control" name="accountant_iii">
                                            <option>NEIL ANTHONY T. MORALA</option>
                                        </select>
                                    </div>
                                </div>
                            </div> -->
                        <!-- Buttons -->
                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary" name="submit">Print</button>
                        </div>
                    </div>
                </form>
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
                        fetch(`get_jev_details.php?id=${orsId}`)
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('fund_cluster_id').value = data.fund_cluster_id;
                                document.getElementById('payee_name').value = data.payee_name;
                                document.getElementById('rc_id').value = data.rc_id;
                                document.getElementById('ors_id').value = data.ors_id;
                                document.getElementById('dv_no').value = data.dv_no;

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

</body>

</html>