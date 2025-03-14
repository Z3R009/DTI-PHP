<?php
include '../DBConnection.php';

$select = mysqli_query($connection, "
    SELECT 
        ors.*, 
        financial_object_code.object_name, 
        approver.approver_name,
        CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
        responsibility_center.code,
        oopap.oopap_name
    FROM ors
    LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
    LEFT JOIN approver ON ors.approver_id = approver.approver_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
");
// Function to generate the next DV number1

// Function to generate the next DV number
function generateDVNumber($connection, $fund_cluster_id, $year, $month)
{
    // Fetch the uacs_code for the given fund_cluster_id
    $uacsQuery = "SELECT uacs_code FROM fund_cluster WHERE fund_cluster_id = ?";
    $uacsStmt = $connection->prepare($uacsQuery);
    $uacsStmt->bind_param("i", $fund_cluster_id);
    $uacsStmt->execute();
    $uacsResult = $uacsStmt->get_result();

    if ($uacsResult->num_rows > 0) {
        $uacsRow = $uacsResult->fetch_assoc();
        $uacs_code = $uacsRow['uacs_code']; // Get the uacs_code
    } else {
        // If no uacs_code is found, use a default value or handle the error
        throw new Exception("UACS code not found for fund_cluster_id: $fund_cluster_id");
    }

    // Fetch the latest DV number for the given fund cluster, year, and month
    $query = "SELECT dv_no FROM dv WHERE fund_cluster_id = ? AND YEAR(date) = ? AND MONTH(date) = ? ORDER BY dv_no DESC LIMIT 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iii", $fund_cluster_id, $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_dv_no = $row['dv_no'];
        // Extract the series number and increment it
        $series = intval(substr($last_dv_no, -3)) + 1;
    } else {
        // If no DV number exists, start with 001
        $series = 1;
    }

    // Format the series to 3 digits
    $series = str_pad($series, 3, '0', STR_PAD_LEFT);

    // Generate the new DV number using uacs_code instead of fund_cluster_id
    $dv_no = sprintf("%s-%02d-%02d-%s", $uacs_code, $month, $year % 100, $series);

    return $dv_no;
}

// Handle AJAX request to get the next DV number
if (isset($_GET['fund_cluster_id']) && isset($_GET['year']) && isset($_GET['month'])) {
    $fund_cluster_id = intval($_GET['fund_cluster_id']);
    $year = intval($_GET['year']);
    $month = intval($_GET['month']);

    $dv_no = generateDVNumber($connection, $fund_cluster_id, $year, $month);

    echo json_encode(['dv_no' => $dv_no]);
    exit;
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

    <!-- Additional custom styles for form validation -->

</head>
    <style>
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .required-field::after {
            content: " *";
            color: red;
        }

       
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

<body>

        <?php include "Includes/header.php";?>
        <?php include "Includes/sidebar.php";?>

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
                                    <td><?php echo htmlspecialchars($row['object_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                    <td><?php echo htmlspecialchars($row['approver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['budget_officer']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary view-details"
                                            data-id="<?php echo $row['ors_id']; ?>"
                                            data-fund-cluster-id="<?php echo $row['fund_cluster_id']; ?>">
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
            <div class="modal-body">
                <form id="dvForm" class="form-container">
                    <input type="hidden" id="ors_id" name="ors_id">
                    <input type="hidden" id="fund_cluster_id" name="fund_cluster_id">

                    <div class="form-section">
                        <h3>General Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required-field">Fund Cluster</label>
                                <input type="text" class="form-control" id="fund_cluster" name="fund_cluster" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">Disbursement Voucher No.</label>
                                <input type="text" class="form-control" id="dv_no" name="dv_no" required>
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
                                        <input type="checkbox" id="commercial" name="payment_mode" value="Commercial Check">
                                        <label for="commercial">Commercial Check</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="ada" name="payment_mode" value="ADA">
                                        <label for="ada">ADA</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="others" name="payment_mode" value="Others">
                                        <label for="others">Others (Specify):</label>
                                        <input type="text" class="form-control" id="other_payment_mode" style="width: 200px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payee Details Section -->
                    <div class="form-section">
                        <h3>Payee Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required-field">Payee Name</label>
                                <input type="text" class="form-control" id="payee_name" name="payee_name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">TIN/Employee No.</label>
                                <input type="text" class="form-control" id="tin_no" name="tin_no" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label required-field">Address</label>
                            <select class="form-control" id="address" name="address" required>
                                <option value="">Select Address</option>
                                <option value="Koronadal City">Koronadal City</option>
                                <!-- Add more options as needed -->
                            </select>
                        </div>
                    </div>
                    
                    <!-- Payment Details Section -->
                    <div class="form-section">
                        <h3>Particulars</h3>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label class="form-label required-field">NOTES</label>
                                <textarea class="form-control" id="notes" name="notes" required></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required-field">Responsibility Center</label>
                                <input type="text" class="form-control" id="code" name="rc_id" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">OO/PAP</label>
                                <input type="text" class="form-control" id="oopap_name" name="oopap_id" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <!-- tax -->
                    <div class="form-section">
                        <h3>Breakdown of Expenses</h3>
                        <div class="form-row">
                            <div class="form-group half-width">
                                <label class="form-label required-field">Gross Amount</label>
                                <input type="number" class="form-control" id="gross_amount" name="gross_amount" step="0.01"
                                    onchange="calculateTaxes()" required>
                            </div>
                            <div class="form-group half-width">
                                <div class="checkbox-item">
                                    <input type="checkbox" class="apply_taxes" id="apply_taxes" name="apply_taxes" checked
                                        onchange="toggleTaxFields()">
                                    <label for="apply_taxes">Apply Tax Calculations</label>
                                </div>
                            </div>
                        </div>

                        <div id="tax_fields_container" class="tax-fields">
                            <div class="form-row">
                                <div class="form-group half-width">
                                    <label class="form-label">VAT <input type="number" class="tax-percentage"
                                            id="vat_percentage" name="vat_percentage" value="12" min="0" max="100" step="0.01"
                                            onchange="calculateTaxes()"> %</label>
                                    <input type="number" class="form-control calculation-field" id="vat_amount" name="vat"
                                        step="0.01" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tax Base</label>
                                    <input type="number" class="form-control calculation-field" id="tax_base" name="tax_base"
                                        step="0.01" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Less: <input type="number" class="tax-percentage"
                                            id="tax1_percentage" name="tax1_percentage" value="5" min="0" max="100" step="0.01"
                                            onchange="calculateTaxes()"> % Tax</label>
                                    <input type="number" class="form-control calculation-field" id="tax_1" name="tax_1" step="0.01"
                                        readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Less: <input type="number" class="tax-percentage"
                                            id="tax2_percentage" name="tax2_percentage" value="2" min="0" max="100" step="0.01"
                                            onchange="calculateTaxes()"> % Tax</label>
                                    <input type="number" class="form-control calculation-field" id="tax_2" name="tax_2" step="0.01"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required-field">Net Amount</label>
                                <input type="number" class="form-control calculation-field" id="net_amount" name="net_amount" step="0.01"
                                    readonly required>
                            </div>
                        </div>
                    </div>

                    <!-- Approver Section -->
                    <div class="form-section">
                        <h3>Approver Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required-field">Approver</label>
                                <input type="text" class="form-control" id="approver_name" name="approver_id" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-field">Budget Officer</label>
                                <input type="text" class="form-control" id="budget_officer" name="budget_officer" required>
                            </div>
                        </div>
                        
                        <!-- Additional fields required by backend -->
                        <input type="hidden" id="chief_accountant" name="chief_accountant" value="Default Chief Accountant">
                        <input type="hidden" id="regional_director" name="regional_director" value="Default Regional Director">
                        <input type="hidden" id="check_no" name="check_no" value="">
                        <input type="hidden" id="bank_acc_no" name="bank_acc_no" value="">
                    </div>
                    
                    <!-- Buttons -->
                    <div class="btn-container">
                        <button type="button" class="btn btn-secondary" id="clearFormBtn">Clear Form</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Submit/Print</button>
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

    <!-- Custom Script for Modal and Form Submission -->
    <script>
        // Form Validation Function
        function validateForm() {
            const requiredFields = [
                'fund_cluster',
                'date',
                'dv_no',
                'payee_name',
                'tin_no',
                'address',
                'notes',
                'code',
                'oopap_name',
                'amount',
                'gross_amount',
                'net_amount',
                'approver_name',
                'budget_officer'
            ];
            
            let isValid = true;
            let missingFields = [];
            
            for (const field of requiredFields) {
                const element = document.getElementById(field);
                if (!element || !element.value.trim()) {
                    isValid = false;
                    missingFields.push(field.replace('_', ' '));
                    if (element) element.classList.add('is-invalid');
                } else if (element) {
                    element.classList.remove('is-invalid');
                }
            }
            
            // Check at least one payment mode is selected
            const paymentModes = document.querySelectorAll('input[name="payment_mode"]:checked');
            if (paymentModes.length === 0) {
                isValid = false;
                missingFields.push('payment mode');
                document.querySelectorAll('input[name="payment_mode"]').forEach(cb => {
                    cb.parentElement.classList.add('is-invalid');
                });
            } else {
                document.querySelectorAll('input[name="payment_mode"]').forEach(cb => {
                    cb.parentElement.classList.remove('is-invalid');
                });
            }
            
            if (!isValid) {
                alert('Please fill in all required fields: ' + missingFields.join(', '));
            }
            
            return isValid;
        }

        // Toggle Tax Fields
        function toggleTaxFields() {
            const applyTaxesCheckbox = document.getElementById('apply_taxes');
            const taxFieldsContainer = document.getElementById('tax_fields_container');
            
            if (applyTaxesCheckbox.checked) {
                taxFieldsContainer.style.display = 'block';
                calculateTaxes();
            } else {
                taxFieldsContainer.style.display = 'none';
                
                document.getElementById('vat_amount').value = '0.00';
                document.getElementById('tax_1').value = '0.00';
                document.getElementById('tax_2').value = '0.00';
                document.getElementById('tax_base').value = '0.00';
                
                const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
                document.getElementById('net_amount').value = grossAmount.toFixed(2);
                
                const amountField = document.getElementById('amount');
                if (!amountField.value) {
                    amountField.value = grossAmount.toFixed(2);
                }
            }
        }

        // Calculate Taxes
        function calculateTaxes() {
            const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
            const vatPercentage = parseFloat(document.getElementById('vat_percentage').value) || 0;
            const tax1Percentage = parseFloat(document.getElementById('tax1_percentage').value) || 0;
            const tax2Percentage = parseFloat(document.getElementById('tax2_percentage').value) || 0;
            
            const vatAmount = grossAmount * (vatPercentage / 100);
            const taxBase = grossAmount - vatAmount;
            const tax1 = taxBase * (tax1Percentage / 100);
            const tax2 = taxBase * (tax2Percentage / 100);
            const netAmount = grossAmount - vatAmount - tax1 - tax2;
            
            document.getElementById('vat_amount').value = vatAmount.toFixed(2);
            document.getElementById('tax_base').value = taxBase.toFixed(2);
            document.getElementById('tax_1').value = tax1.toFixed(2);
            document.getElementById('tax_2').value = tax2.toFixed(2);
            document.getElementById('net_amount').value = netAmount.toFixed(2);
            
            const amountField = document.getElementById('amount');
            if (!amountField.value) {
                amountField.value = grossAmount.toFixed(2);
            }
        }

        // Clear Form
        document.getElementById('clearFormBtn').addEventListener('click', function() {
            const formInputs = document.querySelectorAll('.form-control:not(.calculation-field)');
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            
            formInputs.forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid');
            });
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Reset tax percentages to defaults
            document.getElementById('vat_percentage').value = '12';
            document.getElementById('tax1_percentage').value = '5';
            document.getElementById('tax2_percentage').value = '2';
            document.getElementById('apply_taxes').checked = true;
            
            // Clear calculation fields
            document.getElementById('vat_amount').value = '';
            document.getElementById('tax_base').value = '';
            document.getElementById('tax_1').value = '';
            document.getElementById('tax_2').value = '';
            document.getElementById('net_amount').value = '';
            
            toggleTaxFields();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('dvFormModal');
            const closeModalBtn = document.getElementById('closeDvModal');
            const viewDetailsButtons = document.querySelectorAll('.view-details');
            const submitBtn = document.getElementById('submitBtn');
            
            // When view details button is clicked
            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const orsId = this.getAttribute('data-id');
                    fetch(`get_ors_details.php?id=${orsId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('fund_cluster').value = data.fund_cluster;
                            document.getElementById('payee_name').value = data.payee_name;
                            document.getElementById('tin_no').value = data.tin_no;
                            document.getElementById('address').value = data.address;
                            document.getElementById('notes').value = data.notes;
                            document.getElementById('code').value = data.code;
                            document.getElementById('oopap_name').value = data.oopap_name;
                            document.getElementById('amount').value = data.amount;
                            document.getElementById('approver_name').value = data.approver_name;
                            document.getElementById('budget_officer').value = data.budget_officer;

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
    
    <script>
             
             document.addEventListener('DOMContentLoaded', function() {
            // Get the form and submit button
            const form = document.querySelector('.form-container');
            const submitBtn = form.querySelector('button[type="submit"]');
            const modal = document.getElementById('dvFormModal');
            const closeModalBtn = document.getElementById('closeDvModal');
            const viewDetailsButtons = document.querySelectorAll('.view-details');
            
            // When view details button is clicked
            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const orsId = this.getAttribute('data-id');
                    const fundClusterId = this.getAttribute('data-fund-cluster-id');
                    
                    // Store these values in hidden fields
                    document.getElementById('ors_id').value = orsId;
                    document.getElementById('fund_cluster_id').value = fundClusterId;
                    
                    // Set current date by default
                    const today = new Date();
                    const formattedDate = today.toISOString().split('T')[0];
                    document.getElementById('date').value = formattedDate;
                    
                    fetch(`get_ors_details.php?id=${orsId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('fund_cluster').value = data.fund_cluster;
                            document.getElementById('payee_name').value = data.payee_name;
                            document.getElementById('tin_no').value = data.tin_no;
                            document.getElementById('address').value = data.address || "Koronadal City"; // Default value
                            document.getElementById('notes').value = data.notes || "";
                            document.getElementById('code').value = data.code;
                            document.getElementById('oopap_name').value = data.oopap_name;
                            document.getElementById('amount').value = data.amount;
                            document.getElementById('gross_amount').value = data.amount; // Set gross amount same as amount
                            document.getElementById('approver_name').value = data.approver_name;
                            document.getElementById('budget_officer').value = data.budget_officer;
                            
                            // Set MDS Check as default payment mode
                            document.getElementById('mds').checked = true;
                            
                            // Calculate taxes after setting the gross amount
                            calculateTaxes();
                            
                            // Generate DV number based on date and fund_cluster_id
                            generateDVNumber();
                            
                            modal.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Error fetching ORS details:', error);
                            alert('Error loading data. Please try again.');
                        });
                });
            });
            
            // Generate DV Number when date changes
            document.getElementById('date').addEventListener('change', generateDVNumber);
            
            function generateDVNumber() {
                const dateInput = document.getElementById('date');
                const fundClusterId = document.getElementById('fund_cluster_id').value;
                
                if (dateInput.value && fundClusterId) {
                    const date = new Date(dateInput.value);
                    const year = date.getFullYear();
                    const month = date.getMonth() + 1; // Months are 0-based in JavaScript
                    
                    fetch(`dv.php?fund_cluster_id=${fundClusterId}&year=${year}&month=${month}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.dv_no) {
                                document.getElementById('dv_no').value = data.dv_no;
                            } else {
                                console.error('No DV number returned from the server');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching DV number:', error);
                        });
                }
            }
            
            // Form validation function
            function validateForm() {
                const requiredFields = [
                    'fund_cluster',
                    'date',
                    'dv_no',
                    'payee_name',
                    'tin_no',
                    'address',
                    'notes',
                    'code',
                    'oopap_name',
                    'amount',
                    'gross_amount',
                    'net_amount',
                    'approver_name',
                    'budget_officer'
                ];
                
                let isValid = true;
                let missingFields = [];
                
                for (const field of requiredFields) {
                    const element = document.getElementById(field);
                    if (!element || !element.value.trim()) {
                        isValid = false;
                        missingFields.push(field.replace('_', ' '));
                        if (element) element.classList.add('is-invalid');
                    } else if (element) {
                        element.classList.remove('is-invalid');
                    }
                }
                
                // Check at least one payment mode is selected
                const paymentModes = document.querySelectorAll('input[name="payment_mode"]:checked');
                if (paymentModes.length === 0) {
                    isValid = false;
                    missingFields.push('payment mode');
                    document.querySelectorAll('input[name="payment_mode"]').forEach(cb => {
                        cb.parentElement.classList.add('is-invalid');
                    });
                } else {
                    document.querySelectorAll('input[name="payment_mode"]').forEach(cb => {
                        cb.parentElement.classList.remove('is-invalid');
                    });
                }
                
                if (!isValid) {
                    alert('Please fill in all required fields: ' + missingFields.join(', '));
                }
                
                return isValid;
            }
            
            // Form submission
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Validate form before submission
                if (!validateForm()) {
                    return; // Stop submission if validation fails
                }
                
                // Gather all form data
                const formData = new FormData();
                
                // Add all the form fields to the FormData object
                // Get fund_cluster_id safely
                const fundClusterValue = document.getElementById('fund_cluster').value;
                let fundClusterId;
                
                if (document.getElementById('fund_cluster_id').value) {
                    // Use the stored fund_cluster_id if available
                    fundClusterId = document.getElementById('fund_cluster_id').value;
                } else if (fundClusterValue.includes('-')) {
                    // Extract from the format "ID-Name"
                    fundClusterId = fundClusterValue.split('-')[0];
                } else {
                    // Fallback to the full value
                    fundClusterId = fundClusterValue;
                }
                
                formData.append('fund_cluster_id', fundClusterId);
                formData.append('date', document.getElementById('date').value);
                formData.append('dv_no', document.getElementById('dv_no').value);
                
                // ORS ID if available
                if (document.getElementById('ors_id').value) {
                    formData.append('ors_id', document.getElementById('ors_id').value);
                }
                
                // Mode of payment
                const paymentModes = document.querySelectorAll('input[name="payment_mode"]:checked');
                let modePayment = '';
                paymentModes.forEach(mode => {
                    if (mode.id === 'others') {
                        const otherValue = document.getElementById('other_payment_mode').value;
                        modePayment += (modePayment ? ', ' : '') + 'Others: ' + otherValue;
                    } else {
                        modePayment += (modePayment ? ', ' : '') + mode.value;
                    }
                });
                formData.append('mode_payment', modePayment);
                
                // Payee details
                formData.append('payee_name', document.getElementById('payee_name').value);
                formData.append('tin_no', document.getElementById('tin_no').value);
                formData.append('address', document.getElementById('address').value);
                
                // Payment details
                formData.append('notes', document.getElementById('notes').value);
                formData.append('rc_id', document.getElementById('code').value);
                formData.append('oopap_id', document.getElementById('oopap_name').value);
                formData.append('amount', document.getElementById('amount').value);
                
                // Tax calculations
                formData.append('gross_amount', document.getElementById('gross_amount').value);
                formData.append('vat', document.getElementById('vat_amount').value);
                formData.append('tax_1', document.getElementById('tax_1').value);
                formData.append('tax_2', document.getElementById('tax_2').value);
                formData.append('tax_base', document.getElementById('tax_base').value);
                formData.append('net_amount', document.getElementById('net_amount').value);
                
                // Approver details
                formData.append('approver_id', document.getElementById('approver_name').value);
                formData.append('budget_officer', document.getElementById('budget_officer').value);
                
                // Add default values for fields not in the form
                formData.append('chief_accountant', 'Default Chief Accountant');
                formData.append('regional_director', 'Default Regional Director');
                formData.append('check_no', '');
                formData.append('bank_acc_no', '');
                
                // Log all form data for debugging
                console.log("Form data being submitted:");
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                
                // Send the form data to the server
                fetch('save_dv.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server responded with an error status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Disbursement Voucher saved successfully!');
                        
                        // Open print window with the saved DV
                        const printWindow = window.open(`print_dv.php?id=${data.dv_id}`, '_blank');
                        
                        // After successful submission and printing, reset the form
                        document.getElementById('closeDvModal').click();
                        
                        // Refresh the datatable to show new entry
                        location.reload();
                    } else {
                        alert('Error saving Disbursement Voucher: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the Disbursement Voucher: ' + error.message);
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
            
            // Clear Form Button
            document.getElementById('clearFormBtn').addEventListener('click', function() {
                const formInputs = document.querySelectorAll('.form-control:not(.calculation-field)');
                const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                
                formInputs.forEach(input => {
                    input.value = '';
                    input.classList.remove('is-invalid');
                });
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Reset tax percentages to defaults
                document.getElementById('vat_percentage').value = '12';
                document.getElementById('tax1_percentage').value = '5';
                document.getElementById('tax2_percentage').value = '2';
                document.getElementById('apply_taxes').checked = true;
                
                // Clear calculation fields
                document.getElementById('vat_amount').value = '';
                document.getElementById('tax_base').value = '';
                document.getElementById('tax_1').value = '';
                document.getElementById('tax_2').value = '';
                document.getElementById('net_amount').value = '';
                
                toggleTaxFields();
            });
        });

    </script>


    <!-- tax -->

    <script>



        function redirectToPage() {
            window.location.href = "DVForm.html";
        }


        document.addEventListener('DOMContentLoaded', function () {
            const dateFilter = document.getElementById('date-filter');
            const statusFilter = document.getElementById('status-filter');
            const payeeFilter = document.getElementById('payee-filter');
            const clearFiltersBtn = document.getElementById('clear-filters');
            const filtersContainer = document.querySelector('.filters');

            dateFilter.addEventListener('change', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            payeeFilter.addEventListener('input', applyFilters);

            clearFiltersBtn.addEventListener('click', function () {
                dateFilter.value = '';
                statusFilter.value = '';
                payeeFilter.value = '';
                applyFilters();
            });

            function applyFilters() {
                const selectedDate = dateFilter.value;
                const selectedStatus = statusFilter.value.toLowerCase();
                const searchPayee = payeeFilter.value.toLowerCase();

                const hasActiveFilters = selectedDate || selectedStatus || searchPayee;

                if (hasActiveFilters) {
                    filtersContainer.classList.add('active-filters');
                } else {
                    filtersContainer.classList.remove('active-filters');
                }
                const tableRows = document.querySelectorAll('.assessments-table tbody tr');

                tableRows.forEach(row => {
                    let showRow = true;
                    if (selectedDate) {
                        const dateCell = row.querySelector('td:nth-child(5)').textContent.trim();
                        if (dateCell !== selectedDate) {
                            showRow = false;
                        }
                    }
                    if (showRow && selectedStatus) {
                        const statusCell = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();
                        if (!statusCell.includes(selectedStatus)) {
                            showRow = false;
                        }
                    }
                    if (showRow && searchPayee) {
                        const payeeCell = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase();
                        if (!payeeCell.includes(searchPayee)) {
                            showRow = false;
                        }
                    }

                    row.style.display = showRow ? '' : 'none';
                });

                updateResultsCount();
            }

            function updateResultsCount() {
                const visibleRows = document.querySelectorAll('.assessments-table tbody tr:not([style*="display: none"])');
                const totalRows = document.querySelectorAll('.assessments-table tbody tr');
                console.log(`Showing ${visibleRows.length} of ${totalRows.length} records`);
            }
        });

        function toggleTaxFields() {
            const applyTaxesCheckbox = document.getElementById('apply_taxes');
            const taxFieldsContainer = document.getElementById('tax_fields_container');
            if (applyTaxesCheckbox.checked) {
                taxFieldsContainer.style.display = 'block';

                calculateTaxes();
            } else {
                taxFieldsContainer.style.display = 'none';

                document.getElementById('vat_amount').value = '0.00';
                document.getElementById('tax_1').value = '0.00';
                document.getElementById('tax_2').value = '0.00';
                document.getElementById('tax_base').value = '0.00';


                const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
                document.getElementById('net_amount').value = grossAmount.toFixed(2);

                const amountField = document.getElementById('amount');
                if (!amountField.value) {
                    amountField.value = grossAmount.toFixed(2);
                }
            }
        }


        document.addEventListener('DOMContentLoaded', function () {
            toggleTaxFields();
        });


        function calculateTaxes() {
            const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
            const vatPercentage = parseFloat(document.getElementById('vat_percentage').value) || 0;
            const tax1Percentage = parseFloat(document.getElementById('tax1_percentage').value) || 0;
            const tax2Percentage = parseFloat(document.getElementById('tax2_percentage').value) || 0;
            const vatAmount = grossAmount * (vatPercentage / 100);

            const taxBase = grossAmount - vatAmount;
            const tax1 = taxBase * (tax1Percentage / 100);
            const tax2 = taxBase * (tax2Percentage / 100);
            const netAmount = grossAmount - vatAmount - tax1 - tax2;
            document.getElementById('vat_amount').value = vatAmount.toFixed(2);
            document.getElementById('tax_base').value = taxBase.toFixed(2);
            document.getElementById('tax_1').value = tax1.toFixed(2);
            document.getElementById('tax_2').value = tax2.toFixed(2);
            document.getElementById('net_amount').value = netAmount.toFixed(2);
            const amountField = document.getElementById('amount');
            if (!amountField.value) {
                amountField.value = grossAmount.toFixed(2);
            }
        }

        function toggleTaxFields() {
            const applyTaxesCheckbox = document.getElementById('apply_taxes');
            const taxFieldsContainer = document.getElementById('tax_fields_container');
            
            if (applyTaxesCheckbox.checked) {
                taxFieldsContainer.style.display = 'block';
                calculateTaxes();
            } else {
                taxFieldsContainer.style.display = 'none';
                
                document.getElementById('vat_amount').value = '0.00';
                document.getElementById('tax_1').value = '0.00';
                document.getElementById('tax_2').value = '0.00';
                document.getElementById('tax_base').value = '0.00';
                
                const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
                document.getElementById('net_amount').value = grossAmount.toFixed(2);
                
                const amountField = document.getElementById('amount');
                if (!amountField.value) {
                    amountField.value = grossAmount.toFixed(2);
                }
            }
        }

        // Calculate Taxes
        function calculateTaxes() {
            const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;
            const vatPercentage = parseFloat(document.getElementById('vat_percentage').value) || 0;
            const tax1Percentage = parseFloat(document.getElementById('tax1_percentage').value) || 0;
            const tax2Percentage = parseFloat(document.getElementById('tax2_percentage').value) || 0;
            
            // Make sure we have a valid gross amount
            if (grossAmount <= 0) {
                document.getElementById('vat_amount').value = '0.00';
                document.getElementById('tax_base').value = '0.00';
                document.getElementById('tax_1').value = '0.00';
                document.getElementById('tax_2').value = '0.00';
                document.getElementById('net_amount').value = '0.00';
                return;
            }
            
            const vatAmount = grossAmount * (vatPercentage / 100);
            const taxBase = grossAmount - vatAmount;
            const tax1 = taxBase * (tax1Percentage / 100);
            const tax2 = taxBase * (tax2Percentage / 100);
            const netAmount = grossAmount - vatAmount - tax1 - tax2;
            
            document.getElementById('vat_amount').value = vatAmount.toFixed(2);
            document.getElementById('tax_base').value = taxBase.toFixed(2);
            document.getElementById('tax_1').value = tax1.toFixed(2);
            document.getElementById('tax_2').value = tax2.toFixed(2);
            document.getElementById('net_amount').value = netAmount.toFixed(2);
            
            const amountField = document.getElementById('amount');
            if (!amountField.value) {
                amountField.value = grossAmount.toFixed(2);
            }
        }

        // Make sure these event listeners are set up
        document.addEventListener('DOMContentLoaded', function() {
            // Set up listeners for tax calculation fields
            document.getElementById('gross_amount').addEventListener('input', calculateTaxes);
            document.getElementById('vat_percentage').addEventListener('input', calculateTaxes);
            document.getElementById('tax1_percentage').addEventListener('input', calculateTaxes);
            document.getElementById('tax2_percentage').addEventListener('input', calculateTaxes);
            document.getElementById('apply_taxes').addEventListener('change', toggleTaxFields);
            
            // Initialize tax fields
            toggleTaxFields();
        });


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

        document.getElementById('clearFormBtn').addEventListener('click', function () {
            const formInputs = document.querySelectorAll('.form-control:not(.calculation-field)');
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');

            formInputs.forEach(input => {
                input.value = '';
            });

            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            document.getElementById('vat_percentage').value = '12';
            document.getElementById('tax1_percentage').value = '5';
            document.getElementById('tax2_percentage').value = '2';

            document.getElementById('vat_amount').value = '';
            document.getElementById('tax_base').value = '';
            document.getElementById('tax_1').value = '';
            document.getElementById('tax_2').value = '';
            document.getElementById('net_amount').value = '';
            document.getElementById('total-debit').value = '';
            document.getElementById('total-credit').value = '';
        });

        function openDVModal(dvNumber) {
            document.getElementById('dvFormModal').style.display = 'block';

            document.querySelector('.modal-title').textContent = `Disbursement Voucher: ${dvNumber}`;
        }

        document.getElementById('closeDvModal').addEventListener('click', function () {
            document.getElementById('dvFormModal').style.display = 'none';
        });

        window.addEventListener('click', function (event) {
            const modal = document.getElementById('dvFormModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        const dropdowns = document.querySelectorAll('.dropdown');

        dropdowns.forEach(dropdown => {
            const header = dropdown.querySelector('.dropdown-header');

            header.addEventListener('click', function () {
                dropdown.classList.toggle('active');

                const icon = this.querySelector('.dropdown-icon');
                if (dropdown.classList.contains('active')) {
                    icon.setAttribute('name', 'chevron-up-outline');
                } else {
                    icon.setAttribute('name', 'chevron-down-outline');
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            const dashboardContainer = document.querySelector('.dashboard-container');

            if (sidebar && dashboardContainer) {
                sidebar.addEventListener('click', function (e) {

                    if (e.target === sidebar || e.target.classList.contains('logo-container') ||
                        e.target.closest('.logo-container')) {
                        dashboardContainer.classList.toggle('collapsed');
                    }
                });
            }


            const dropdown = document.querySelector('.dropdown');

            if (dropdown) {
                dropdown.addEventListener('click', function (e) {

                    if (!e.target.closest('.dropdown-content')) {
                        e.stopPropagation();
                        this.classList.toggle('active');
                    }
                });
            }



        });
        document.addEventListener('DOMContentLoaded', function () {
            calculateTaxes();
            calculateTotals();
        });
    </script>


    <!-- dv_no -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.querySelector('input[name="date"]');
            const dvNoInput = document.querySelector('input[name="dv_no"]');
            const fundClusterInput = document.getElementById('fund_cluster_name');

            dateInput.addEventListener('change', function () {
                const date = new Date(this.value);
                const year = date.getFullYear();
                const month = date.getMonth() + 1; // Months are 0-based in JavaScript
                const fundClusterId = fundClusterInput.value.split('-')[0]; // Extract fund_cluster_id from fund_cluster_name

                if (fundClusterId && year && month) {
                    fetch(`dv.php?fund_cluster_id=${fundClusterId}&year=${year}&month=${month}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.dv_no) {
                                dvNoInput.value = data.dv_no;
                            } else {
                                console.error('No DV number returned from the server');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching DV number:', error);
                        });
                }
            });
        });
    </script>
</body>

</html>