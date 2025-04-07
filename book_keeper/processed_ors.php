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
<?php
// Fetch filter values from the URL, set the default year to current year if not provided
$year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // Default to current year
$month = isset($_GET['month']) ? $_GET['month'] : '';
$service = isset($_GET['service']) ? $_GET['service'] : '';

// Build the WHERE clause based on filters
$whereClauses = [];
if ($year) {
    $whereClauses[] = "YEAR(ors.date) = '$year'";
}
if ($month) {
    $whereClauses[] = "MONTH(ors.date) = '$month'";
}
if ($service) {
    $whereClauses[] = "services.services_name = '$service'";
}

// Combine all the where clauses
$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = " WHERE " . implode(' AND ', $whereClauses);
}

// Update your query with the filters
$ors_query = "SELECT * FROM ors
              LEFT JOIN services ON ors.services_id = services.services_id
              $whereSql
              ORDER BY ors.date DESC";

$ors_result = $connection->query($ors_query);
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

        <div class="pagetitle d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Obligation Request and Status</h1>
            <button class="btn btn-primary" onclick="window.location.href='ors.php'">
                ORS Form
            </button>

        </div><!-- End Page Title -->


        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Obligation Request And Status</h2>

                <!-- ors list -->
                <div>

                    <!-- ORS List Filter Section -->
                    <div class="container mt-4">
                        <div class="form-row">
                            <!-- Year Filter -->
                            <div class="form-group col-md-4">
                                <label for="yearFilter">Year</label>
                                <select class="form-control" id="yearFilter" name="yearFilter">
                                    <option value="">Select Year</option>
                                    <?php
                                    // Generate year options dynamically (for example from 2010 to the current year)
                                    for ($yearOption = 2010; $yearOption <= date('Y'); $yearOption++) {
                                        $selected = ($yearOption == $year) ? 'selected' : ''; // Keep the selected year
                                        echo "<option value='" . $yearOption . "' $selected>" . $yearOption . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Month Filter -->
                            <div class="form-group col-md-4">
                                <label for="monthFilter">Month</label>
                                <select class="form-control" id="monthFilter" name="monthFilter">
                                    <option value="">Select Month</option>
                                    <?php
                                    $months = [
                                        "January",
                                        "February",
                                        "March",
                                        "April",
                                        "May",
                                        "June",
                                        "July",
                                        "August",
                                        "September",
                                        "October",
                                        "November",
                                        "December"
                                    ];
                                    foreach ($months as $index => $month) {
                                        $monthNumber = $index + 1;
                                        $selected = ($monthNumber == $month) ? 'selected' : ''; // Keep the selected month
                                        echo "<option value='" . $monthNumber . "' $selected>" . $month . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Services Filter -->
                            <div class="form-group col-md-4">
                                <label for="servicesFilter">Services</label>
                                <select class="form-control" id="servicesFilter" name="servicesFilter">
                                    <option value="">Select Service</option>
                                    <?php
                                    // Fetch the services list dynamically
                                    $services_query = "SELECT * FROM services";
                                    $services_result = $connection->query($services_query);
                                    while ($row = $services_result->fetch_assoc()) {
                                        $selected = ($row['services_name'] == $service) ? 'selected' : ''; // Keep the selected service
                                        echo "<option value='" . htmlspecialchars($row['services_name']) . "' $selected>" . htmlspecialchars($row['services_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
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
                                        $date = new DateTime($ors['date']);
                                        echo "<td>" . htmlspecialchars($date->format('F j, Y')) . "</td>";

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


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Filter -->
    <script>
        // JavaScript to handle filtering without the "Apply Filters" button
        document.getElementById('yearFilter').addEventListener('change', applyFilter);
        document.getElementById('monthFilter').addEventListener('change', applyFilter);
        document.getElementById('servicesFilter').addEventListener('change', applyFilter);

        function applyFilter() {
            var year = document.getElementById('yearFilter').value;
            var month = document.getElementById('monthFilter').value;
            var service = document.getElementById('servicesFilter').value;

            // Get the current URL and append the filters
            var newUrl = window.location.origin + window.location.pathname + '?year=' + year + '&month=' + month + '&service=' + service;

            // Update the URL with the selected filters, keeping the #orsList tab in the URL
            window.location.href = newUrl + '#orsList'; // Keep the user in the orsList tab
        }
    </script>

</body>

</html>