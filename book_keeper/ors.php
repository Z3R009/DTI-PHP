<?php
include '../DBConnection.php';


// insert ors

if (isset($_POST['submit'])) {
    echo "Form submitted!";

    // Debugging: Print all POST data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";


    $fund_cluster_id = $_POST['fund_cluster_id'];
    $date = $_POST['date'];
    $ors_no = $_POST['ors_no'];
    $services_id = $_POST['services_id'];
    $payee_id = $_POST['payee_id'];
    $purpose = $_POST['purpose'];
    $notes = $_POST['notes'];
    $rc_id = $_POST['rc_id'];
    $account_ids = $_POST['account_id']; // Array of account IDs
    $amounts = $_POST['amount']; // Array of amounts
    $oopap_id = $_POST['oopap_id'];
    $total_amount = $_POST['total_amount'];
    $approver_id = $_POST['approver_id'];
    $budget_officer = $_POST['budget_officer'];

    // Start transaction
    $connection->begin_transaction();

    try {
        // Insert ORS record
        $sql = "INSERT INTO ors (fund_cluster_id, date, services_id, ors_no, payee_id, purpose, notes, rc_id, account_id, oopap_id, total_amount, approver_id, budget_officer) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $connection->error);
        }

        // Use the first account_id for the main ORS record
        $stmt->bind_param(
            "isssssssiidis",
            $fund_cluster_id,
            $date,
            $services_id,
            $ors_no,
            $payee_id,
            $purpose,
            $notes,
            $rc_id,
            $account_ids[0], // Use first account_id
            $oopap_id,
            $total_amount,
            $approver_id,
            $budget_officer
        );

        if (!$stmt->execute()) {
            throw new Exception("Error inserting ORS: " . $stmt->error);
        }

        // Get the inserted ORS ID
        $ors_id = $connection->insert_id;

        // Insert obligation history for each account entry
        $history_sql = "INSERT INTO obligation_history (ors_id, project_id, net) VALUES (?, ?, ?)";
        $history_stmt = $connection->prepare($history_sql);
        if (!$history_stmt) {
            throw new Exception('Prepare failed for obligation_history: ' . $connection->error);
        }

        // Process each account entry
        foreach ($account_ids as $index => $account_id) {
            $amount = $amounts[$index];

            // Get project information without balance check
            $check_sql = "SELECT project_id, balances FROM project 
                         WHERE account_id = ? AND oopap_id = ?";
            $check_stmt = $connection->prepare($check_sql);
            $check_stmt->bind_param("ii", $account_id, $oopap_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("No project found for account ID: " . $account_id);
            }

            $project = $result->fetch_assoc();
            $project_id = $project['project_id'];
            $new_balance = $project['balances'] - $amount;

            // Update project allotment (now allowing negative values)
            $update_sql = "UPDATE project SET balances = ? WHERE project_id = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("di", $new_balance, $project_id);
            $update_stmt->execute();

            // Insert obligation history record
            $history_stmt->bind_param("iid", $ors_id, $project_id, $amount);
            if (!$history_stmt->execute()) {
                throw new Exception("Error inserting obligation history: " . $history_stmt->error);
            }
        }

        $connection->commit();
        header("Location: ors_form.php?ors_no=$ors_no");
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        echo "Error: " . $e->getMessage();
        exit();
    } finally {
        if (isset($check_stmt))
            $check_stmt->close();
        if (isset($update_stmt))
            $update_stmt->close();
        if (isset($stmt))
            $stmt->close();
        if (isset($history_stmt))
            $history_stmt->close();
        $connection->close();
    }
}





// Query to fetch account titles and their corresponding UACS codes with OO/PAP
$sql_account = "SELECT DISTINCT at.account_id, at.account_title, at.account_code, p.oopap_id, o.oopap_name
                FROM account_title at
                INNER JOIN project p ON at.account_id = p.account_id
                INNER JOIN oopap o ON p.oopap_id = o.oopap_id
                ORDER BY o.oopap_name, at.account_code";

$result_account = $connection->query($sql_account);

// Store account data for JavaScript
$accountData = [];
while ($row = $result_account->fetch_assoc()) {
    $accountData[] = $row;
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


// Fetch Last Disbursement Voucher No.
$sql_last_dv = "SELECT ors_no FROM ors ORDER BY ors_id DESC LIMIT 1";
$result_last_dv = $connection->query($sql_last_dv);
$last_ors_no = $result_last_dv->fetch_assoc()['ors_no'] ?? null;

// Extract Last Sequence Number (if exists)
$last_sequence = 1;
if ($last_ors_no) {
    $parts = explode("-", $last_ors_no);
    if (count($parts) === 4) {
        $last_sequence = (int) $parts[3] + 1;
    }
}

// Format the new sequence number to 5 digits (e.g., 00001)
$new_sequence = str_pad($last_sequence, 5, '0', STR_PAD_LEFT);

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

    <title>Obligation Request and Status</title>
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
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #0077b6;
            color: white;
            font-weight: 500;
        }

        .signature-box {
            border: 1px dashed #ddd;
            height: 100px;
            border-radius: 8px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            cursor: pointer;
        }

        .signature-box:hover {
            background-color: #f8f9fa;
        }

        @media (max-width: 992px) {
            .form-group.half-width {
                flex: 0 0 100%;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

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
        }

        @media print {
            body {
                background-color: white;
            }

            .sidebar,
            .btn-container {
                display: none;
            }

            .dashboard-container {
                display: block;
            }

            .form-container {
                box-shadow: none;
                padding: 0;
            }
        }


        .calculation-field {
            background-color: #edf2f7;
            cursor: not-allowed;
        }

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

        .tax-fields {
            width: 100%;
            transition: all 0.3s ease;
        }

        .tax-fields.hidden {
            display: none;
        }
    </style>
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Obligation Request and Status</h1>
        </div><!-- End Page Title -->

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#orsForm">ORS Form</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#orsList">ORS List</a>
            </li>
        </ul>



        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Obligation Request And Status</h2>

                <!-- General Information Section -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="orsForm">
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
                                            <label class="form-label">Obligation Request No.</label>
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
                                            <input type="text" class="form-control" name="tin_no" id="tin_no" required
                                                autocomplete="off">

                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" id="address" required
                                            autocomplete="off">

                                    </div>
                                </div>

                                <!-- Payment Details Section -->
                                <div class="form-section">
                                    <h3></h3>


                                    <label class="form-label">Purpose</label>
                                    <div class="form-row">
                                        <select class="form-control" name="purpose">
                                            <option value="To Payment of">To Payment of</option>
                                            <option value="To Disburse">To Reimburse</option>
                                            <option value="To Cash Advance">To Cash Advance</option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group full-width">

                                            <textarea class="form-control" name="notes" placeholder="Enter Purpose"></textarea autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-row">
                                            <div class="form-group">
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

                                        <!-- Accounting Entry Section -->
                                        <div class="form-section">
                                            <h3>Particulars</h3>
                                            <div class="table-responsive">
                                                <table class="accounting-entry-table">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="3">Account Title</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="accounting-table-body">
                                                        <!-- First row -->
                                                        <tr class="entry-row">
                                                            <td colspan="3">
                                                                <select class="form-control" name="account_id[]" required>
                                                                    <option selected disabled>Select Account</option>
                                                                    <?php
                                                                    // Reset the result pointer
                                                                    $result_account->data_seek(0);
                                                                    while ($row = $result_account->fetch_assoc()) {
                                                                        echo "<option value='" . htmlspecialchars($row['account_id']) . "' 
                                                                            data-account_code='" . htmlspecialchars($row['account_code']) . "'
                                                                            data-oopap_id='" . htmlspecialchars($row['oopap_id']) . "'>"
                                                                            . htmlspecialchars($row['account_title']) . " - " . htmlspecialchars($row['account_code']) .
                                                                            "</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control amount-input" name="amount[]" step="0.01" required>
                                                            </td>
                                                        </tr>
                                                        <!-- Add Row button row -->
                                                        <tr id="add-row-container">
                                                            <td colspan="4" class="text-left">
                                                                <button type="button" id="addAccountRow" class="btn btn-secondary">
                                                                    <ion-icon name="add-outline"></ion-icon> Add Row
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <!-- Total Amount Row -->
                                                        <tr>
                                                            <td colspan="3" class="text-right font-weight-bold">Total Amount:</td>
                                                            <td><input type="text" id="total_amount" class="form-control" name="total_amount" readonly></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <input type="hidden" class="form-control" id="project_id" name="project_id" readonly placeholder="Project ID">


                                        <!-- Receipt Section -->
                                        <div class="form-section">
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
                                                    <label class="form-label">Budget Officer</label>
                                                    <select class="form-control" name="budget_officer">
                                                        <option>CONNIE M. BARNACHEA</option>

                                                    </select>
                                                </div>

                                            </div>

                                        </div>

                                        <!-- Form Buttons -->
                                        <div class="btn-container">
                                            <button type="button" class="btn btn-secondary">Clear Form</button>
                                            <button type="submit" class="btn btn-primary" name="submit">Submit Voucher</button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

        

                        <div class="tab-pane fade" id="orsList">
                            <div class="container">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ORS No.</th>
                                                <th>Date</th>
                                                <th>Payee</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch ORS records from database
                                            $ors_query = "SELECT * FROM ors ORDER BY date DESC";
                                            $ors_result = $connection->query($ors_query);

                                            while ($ors = $ors_result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($ors['ors_no']) . "</td>";
                                                echo "<td>" . htmlspecialchars($ors['date']) . "</td>";

                                                // Fetch payee name
                                                $payee_query = "SELECT payee_name FROM payee WHERE payee_id = " . $ors['payee_id'];
                                                $payee_result = $connection->query($payee_query);
                                                $payee = $payee_result->fetch_assoc();

                                                echo "<td>" . htmlspecialchars($payee['payee_name']) . "</td>";
                                                echo "<td>" . number_format($ors['total_amount'], 2) . "</td>";
                                                echo "<td>Processed</td>"; // You can add dynamic status logic
                                                echo "<td>
                                                    <a href='ors_form.php?ors_no=" . $ors['ors_no'] . "' class='btn btn-info btn-sm'>View</a>
                                                </td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
</div>
    </main><!-- End #main -->



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

    <script>

        function redirectToPage() {
            window.location.href = "ORSForm.html";
        }

        function toggleTaxFields() {
            const applyTaxes = document.getElementById('apply_taxes').checked;
            const taxFieldsContainer = document.getElementById('tax_fields_container');

            if (applyTaxes) {
                taxFieldsContainer.classList.remove('hidden');
                calculateTaxes();
            } else {
                taxFieldsContainer.classList.add('hidden');
                calculateWithoutTaxes();
            }
        }
        function calculateWithoutTaxes() {
            const grossAmount = parseFloat(document.getElementById('gross_amount').value) || 0;

            document.getElementById('net_amount').value = grossAmount.toFixed(2);

            if (document.getElementById('amount') && !document.getElementById('amount').value) {
                document.getElementById('amount').value = grossAmount.toFixed(2);
            }
        }

        function calculateTaxes() {
            const applyTaxes = document.getElementById('apply_taxes').checked;

            if (!applyTaxes) {
                calculateWithoutTaxes();
                return;
            }

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
            if (document.getElementById('amount') && !document.getElementById('amount').value) {
                document.getElementById('amount').value = grossAmount.toFixed(2);
            }
        }

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


            calculateTaxes();
        });

    </script>

    <!-- add row and total amount calculation -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tableBody = document.querySelector("#accounting-table-body");
            const addRowContainer = document.querySelector("#add-row-container");

            // Function to update total
            function updateTotal() {
                let total = 0;
                document.querySelectorAll(".amount-input").forEach(function (input) {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById("total_amount").value = total.toFixed(2);
            }

            // Add new row functionality
            document.getElementById("addAccountRow").addEventListener("click", function () {
                const newRow = document.createElement("tr");
                newRow.classList.add("entry-row");

                // Clone the account select options
                const accountSelect = document.querySelector('select[name="account_id[]"]').cloneNode(true);
                accountSelect.name = "account_id[]";
                accountSelect.value = ""; // Reset selection

                // Create amount input
                const amountInput = document.createElement("input");
                amountInput.type = "number";
                amountInput.className = "form-control amount-input";
                amountInput.name = "amount[]";
                amountInput.step = "0.01";
                amountInput.required = true;

                // Create cells
                const accountCell = document.createElement("td");
                accountCell.colSpan = 3;
                accountCell.appendChild(accountSelect);

                const amountCell = document.createElement("td");
                amountCell.appendChild(amountInput);

                // Add cells to row
                newRow.appendChild(accountCell);
                newRow.appendChild(amountCell);

                // Insert before the add row container
                tableBody.insertBefore(newRow, addRowContainer);

                // Add event listener for amount changes
                amountInput.addEventListener("input", updateTotal);
            });

            // Listen for input changes on all amount inputs
            document.querySelectorAll(".amount-input").forEach(input => {
                input.addEventListener("input", updateTotal);
            });
        });
    </script>

    <!-- approver -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const approverSelect = document.getElementById("approverSelect");
            const designationLabel = document.getElementById("designationLabel");

            approverSelect.addEventListener("change", function () {
                // Get the selected option
                const selectedOption = approverSelect.options[approverSelect.selectedIndex];
                const designation = selectedOption.getAttribute("data-designation") || "Designation";

                // Update the label text
                designationLabel.textContent = designation;
            });
        });
    </script>

    <!-- dv_number -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fundClusterSelect = document.getElementById("fundCluster");
            const dvDateInput = document.getElementById("dvDate");
            const orsNumberInput = document.getElementById("orsNumber");

            function generateorsNumber() {
                const selectedUACS = fundClusterSelect.value;
                const selectedDate = dvDateInput.value;

                if (!selectedUACS || !selectedDate) {
                    orsNumberInput.value = "";
                    return;
                }

                // Extract Year and Month from Date Input
                const dateObj = new Date(selectedDate);
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Ensure two digits

                // Retrieve the Last Sequence Number from PHP
                const lastSequence = "<?php echo $new_sequence; ?>";

                // Format Disbursement Voucher No.
                const orsNumber = `${selectedUACS}-${year}-${month}-${lastSequence}`;
                orsNumberInput.value = orsNumber;
            }

            fundClusterSelect.addEventListener("change", generateorsNumber);
            dvDateInput.addEventListener("change", generateorsNumber);
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

<!-- total_amount -->

<script>
    document.addEventListener("DOMContentLoaded", function () {
        function updateTotal() {
            let total = 0;
            document.querySelectorAll(".amount-input").forEach(function (input) {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById("total_amount").value = total.toFixed(2);
        }

        // Listen for input changes
        document.getElementById("accounting-table-body").addEventListener("input", function (event) {
            if (event.target.classList.contains("amount-input")) {
                updateTotal();
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const accountSelect = document.querySelector('select[name="account_id[]"]');
        const oopapSelect = document.querySelector('select[name="oopap_id"]');
        const amountInput = document.querySelector('.amount-input');
        const projectIdInput = document.getElementById('project_id');

        async function checkAllotment() {
            const accountId = accountSelect.value;
            const oopapId = oopapSelect.value;
            const amount = parseFloat(amountInput.value) || 0;

            if (!accountId || !oopapId || amount === 0) {
                projectIdInput.value = '';
                return;
            }

            try {
                const response = await fetch('check_allotment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `account_id=${accountId}&oopap_id=${oopapId}&amount=${amount}`
                });

                const data = await response.json();

                if (data.success) {
                    projectIdInput.value = data.project_id;
                    projectIdInput.style.backgroundColor = '#e8f5e9';
                } else {
                    projectIdInput.value = data.project_id;
                    projectIdInput.style.backgroundColor = '#fff3e0';
                    // Show warning instead of error
                    alert('Warning: This will result in a negative balance!');
                }
            } catch (error) {
                console.error('Error checking allotment:', error);
                projectIdInput.value = '';
                projectIdInput.style.backgroundColor = '#ffebee';
            }
        }

        accountSelect.addEventListener('change', checkAllotment);
        oopapSelect.addEventListener('change', checkAllotment);
        amountInput.addEventListener('input', checkAllotment);
    });
</script>

<!-- Add this before the closing </body> tag -->
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

<!-- Add this JavaScript before the closing </body> tag -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const oopapSelect = document.querySelector('select[name="oopap_id"]');
        const accountSelects = document.querySelectorAll('select[name="account_id[]"]');

        function updateAccountOptions() {
            const selectedOopapId = oopapSelect.value;

            accountSelects.forEach(select => {
                const currentValue = select.value;
                const options = select.options;

                // Show/hide options based on selected OO/PAP
                for (let i = 0; i < options.length; i++) {
                    const option = options[i];
                    if (option.value === "") continue; // Skip the default "Select Account" option

                    const optionOopapId = option.getAttribute('data-oopap_id');
                    if (optionOopapId === selectedOopapId) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                }

                // Reset selection if current value is not in selected OO/PAP
                if (currentValue && options[select.selectedIndex].style.display === 'none') {
                    select.value = "";
                }
            });
        }

        // Update account options when OO/PAP changes
        oopapSelect.addEventListener('change', updateAccountOptions);

        // Initial update
        updateAccountOptions();
    });
</script>

</body>

</html>