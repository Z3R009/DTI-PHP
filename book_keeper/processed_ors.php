<?php
include '../DBConnection.php';


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
$month = isset($_GET['month']) ? $_GET['month'] : ''; // Default to all months
$service = isset($_GET['service']) ? $_GET['service'] : ''; // Default to all services

// Build the WHERE clause based on filters
$whereClauses = [];
$params = [];
$types = '';

// Always filter by year
$whereClauses[] = "YEAR(ors.date) = ?";
$params[] = $year;
$types .= 'i'; // Assuming year is an integer

// Add month filter if selected
if ($month) {
    $whereClauses[] = "MONTH(ors.date) = ?";
    $params[] = $month;
    $types .= 'i'; // Assuming month is an integer
}

// Add service filter if selected
if ($service) {
    $whereClauses[] = "services.services_name = ?";
    $params[] = $service;
    $types .= 's'; // Assuming service name is a string
}

// Combine all the where clauses
$whereSql = ' WHERE ' . implode(' AND ', $whereClauses);

// Prepare the query
$ors_query = "SELECT * FROM ors
              LEFT JOIN services ON ors.services_id = services.services_id
              $whereSql
              ORDER BY ors.date DESC";

$stmt = $connection->prepare($ors_query);

// Bind parameters dynamically
if ($params) {
    $stmt->bind_param($types, ...$params);
}

// Execute the query
$stmt->execute();
$ors_result = $stmt->get_result();

// Debugging: Check if any records were found
if ($ors_result->num_rows === 0) {
    echo "<p>No records found for the selected filters.</p>";
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
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --secondary-color: #475569;
            --accent-color: #f59e0b;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --danger: #ef4444;
            --success: #10b981;
            --info: #0ea5e9;
            --warning: #f59e0b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Nunito', sans-serif;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }

        .form-container {
            max-width: 1200px;
            margin: 2rem auto;
            background-color: var(--bg-white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .form-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.75rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .form-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .form-section {
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
        }

        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 1.25rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .form-section h3:before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 18px;
            background-color: var(--primary-color);
            margin-right: 0.75rem;
            border-radius: 2px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
            padding: 0 0.75rem;
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            flex: 0 0 100%;
        }

        .form-group.half-width {
            flex: 0 0 50%;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background-color: var(--bg-white);
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%232563eb'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-container {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            letter-spacing: 0.01em;
        }

        .btn i {
            font-size: 1.1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #334155;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-info {
            background-color: var(--info);
            color: white;
        }

        .btn-info:hover {
            background-color: #0284c7;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            border: none;
        }

        .table th:first-child {
            border-top-left-radius: var(--radius-md);
        }

        .table th:last-child {
            border-top-right-radius: var(--radius-md);
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover td {
            background-color: var(--primary-light);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: var(--bg-light);
        }

        .table-striped tbody tr:hover {
            background-color: var(--primary-light);
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
        }

        .badge-success {
            background-color: var(--success);
            color: white;
        }

        .badge-warning {
            background-color: var(--warning);
            color: white;
        }

        .badge-info {
            background-color: var(--info);
            color: white;
        }

        /* Filter section */
        .filter-section {
            background-color: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary-color);
        }

        .filter-section h3 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
                padding: 1.5rem;
                margin: 1rem;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .btn-container {
                flex-direction: column;
            }

            .table th, 
            .table td {
                padding: 0.75rem;
            }
        }

        @media print {
            body {
                background-color: white;
            }

            .sidebar,
            .btn-container,
            .header,
            .filter-section,
            .back-to-top {
                display: none;
            }

            .dashboard-container {
                display: block;
            }

            .form-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
            }

            .table-responsive {
                overflow: visible;
                box-shadow: none;
            }

            .table th {
                background-color: #f1f5f9 !important;
                color: black !important;
            }
        }

        /* Animation styles */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Status indicator styles */
        .status-processed {
            background-color: #dcfce7;
            color: var(--success);
            padding: 0.3rem 0.6rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-processed i {
            font-size: 0.875rem;
        }

        .amount-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .active-filter {
            border-color: var(--primary-color);
            background-color: var(--primary-light);
        }
        
        .spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .highlight-row {
            animation: highlight 1.5s ease-in-out;
        }
        
        @keyframes highlight {
            0% { background-color: var(--primary-light); }
            100% { background-color: transparent; }
        }
    </style>
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">Processed ORS Records</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item">Book Keeper</li>
                        <li class="breadcrumb-item active">Processed ORS</li>
                    </ol>
                </nav>
            </div>
            <button class="btn btn-primary" onclick="window.location.href='ors.php'">
                <i class="bi bi-plus-circle"></i> Create New ORS
            </button>
        </div><!-- End Page Title -->


        <div class="content-wrapper fade-in">
            <div class="filter-section">
                <h3><i class="bi bi-funnel-fill"></i> Filter Records</h3>
                <div class="form-row">
                    <!-- Year Filter -->
                    <div class="form-group col-md-4">
                        <label for="yearFilter" class="form-label">Year</label>
                        <select class="form-control" id="yearFilter" name="year">
                            <option value="">Select Year</option>
                            <?php
                            for ($yearOption = 2010; $yearOption <= date('Y'); $yearOption++) {
                                $selected = ($yearOption == $year) ? 'selected' : '';
                                echo "<option value='" . $yearOption . "' $selected>" . $yearOption . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Month Filter -->
                    <div class="form-group col-md-4">
                        <label for="monthFilter" class="form-label">Month</label>
                        <select class="form-control" id="monthFilter" name="month">
                            <option value="">All Months</option>
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
                            foreach ($months as $index => $monthName) {
                                $monthNumber = $index + 1;
                                $selected = ($monthNumber == $month) ? 'selected' : '';
                                echo "<option value='" . $monthNumber . "' $selected>" . $monthName . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Services Filter -->
                    <div class="form-group col-md-4">
                        <label for="servicesFilter" class="form-label">Services</label>
                        <select class="form-control" id="servicesFilter" name="service">
                            <option value="">All Services</option>
                            <?php
                            $services_query = "SELECT * FROM services";
                            $services_result = $connection->query($services_query);
                            while ($row = $services_result->fetch_assoc()) {
                                $selected = ($row['services_name'] == $service) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['services_name']) . "' $selected>" . htmlspecialchars($row['services_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="card-title mb-0">Obligation Request Records</h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="printTable()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                            <i class="bi bi-file-earmark-excel"></i> Export
                        </button>
                    </div>
                </div>

                <!-- ors list -->
                <div>
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
                                if ($ors_result->num_rows === 0) {
                                    echo '<tr><td colspan="6" class="text-center py-4">No records found for the selected filters.</td></tr>';
                                } else {
                                    while ($ors = $ors_result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td><strong>" . htmlspecialchars($ors['ors_no']) . "</strong></td>";
                                        $date = new DateTime($ors['date']);
                                        echo "<td>" . htmlspecialchars($date->format('F j, Y')) . "</td>";

                                        // Fetch payee name
                                        $payee_query = "SELECT payee_name FROM payee WHERE payee_id = " . $ors['payee_id'];
                                        $payee_result = $connection->query($payee_query);
                                        $payee = $payee_result->fetch_assoc();

                                        echo "<td>" . htmlspecialchars($payee['payee_name']) . "</td>";
                                        echo "<td class='amount-cell'>₱ " . number_format($ors['total_amount'], 2) . "</td>";
                                        echo "<td><span class='status-processed'><i class='bi bi-check-circle-fill'></i> Processed</span></td>";
                                        echo "<td>
                                                <a href='ors_form.php?ors_no=" . $ors['ors_no'] . "' class='btn btn-info btn-sm'>
                                                    <i class='bi bi-eye'></i> View
                                                </a>
                                            </td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
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
        // JavaScript to handle filtering with visual feedback
        document.addEventListener('DOMContentLoaded', function() {
            // Filter change handlers
            document.getElementById('yearFilter').addEventListener('change', applyFilter);
            document.getElementById('monthFilter').addEventListener('change', applyFilter);
            document.getElementById('servicesFilter').addEventListener('change', applyFilter);
            
            // Add visual indicators for active filters
            highlightActiveFilters();
        });

        function applyFilter() {
            // Show loading indicator
            const tableBody = document.querySelector('tbody');
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><i class="bi bi-arrow-repeat spin me-2"></i> Loading records...</td></tr>';
            
            var year = document.getElementById('yearFilter').value;
            var month = document.getElementById('monthFilter').value;
            var service = document.getElementById('servicesFilter').value;

            // Get the current URL and append the filters
            var newUrl = window.location.origin + window.location.pathname + '?year=' + year + '&month=' + month + '&service=' + service;

            // Update the URL with the selected filters
            window.location.href = newUrl; // Redirect to the new URL with filters
        }
        
        function highlightActiveFilters() {
            // Get all filter dropdowns
            const filterElements = ['yearFilter', 'monthFilter', 'servicesFilter'];
            
            filterElements.forEach(id => {
                const select = document.getElementById(id);
                if (select.value) {
                    // Add a class to indicate active filter
                    select.classList.add('active-filter');
                    // Add a visual indicator to the label
                    const label = select.previousElementSibling;
                    if (label) {
                        label.innerHTML += ' <i class="bi bi-funnel-fill text-primary"></i>';
                    }
                }
            });
        }
        
        // Print table function
        function printTable() {
            const year = document.getElementById('yearFilter').value || 'All';
            const month = document.getElementById('monthFilter').options[document.getElementById('monthFilter').selectedIndex].text || 'All';
            const service = document.getElementById('servicesFilter').value || 'All Services';
            
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            
            // Generate print content
            let printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Processed ORS Records</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .header { margin-bottom: 20px; text-align: center; }
                        .filters { margin-bottom: 15px; font-size: 14px; }
                        .footer { margin-top: 30px; font-size: 12px; text-align: center; }
                        .amount { text-align: right; font-family: monospace; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Department of Trade and Industry</h2>
                        <h3>Processed Obligation Request and Status Records</h3>
                    </div>
                    <div class="filters">
                        <p><strong>Filters:</strong> Year: ${year} | Month: ${month} | Service: ${service}</p>
                        <p><strong>Date Printed:</strong> ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ORS No.</th>
                                <th>Date</th>
                                <th>Payee</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            // Get data from the current table
            const rows = document.querySelectorAll('.table tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length < 5) return; // Skip if it's a "no records found" row
                
                printContent += '<tr>';
                // Only include the first 5 columns (skip Actions column)
                for (let i = 0; i < 5; i++) {
                    const cellContent = cells[i].innerHTML;
                    // Special formatting for amount column
                    if (i === 3) {
                        printContent += `<td class="amount">${cellContent}</td>`;
                    } else {
                        printContent += `<td>${cellContent}</td>`;
                    }
                }
                printContent += '</tr>';
            });
            
            printContent += `
                        </tbody>
                    </table>
                    <div class="footer">
                        <p>Generated by DTI Book Keeping System</p>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.open();
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            // Wait for content to load before printing
            printWindow.onload = function() {
                printWindow.print();
            };
        }
        
        // Export to CSV function
        function exportToCSV() {
            const table = document.querySelector('.table');
            const rows = table.querySelectorAll('tr');
            
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Add CSV header - skip the Actions column
            const headerRow = rows[0];
            const headers = headerRow.querySelectorAll('th');
            const headerValues = [];
            
            // Include only the first 5 columns (skip the Actions column)
            for (let i = 0; i < 5; i++) {
                headerValues.push('"' + headers[i].innerText.trim() + '"');
            }
            csvContent += headerValues.join(",") + "\r\n";
            
            // Add data rows
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].querySelectorAll('td');
                if (cells.length < 5) continue; // Skip if it's a "no records found" row
                
                const rowValues = [];
                // Include only the first 5 columns (skip the Actions column)
                for (let j = 0; j < 5; j++) {
                    // Clean up the cell text - remove HTML tags and normalize text
                    const cellText = cells[j].innerText.trim();
                    rowValues.push('"' + cellText.replace(/"/g, '""') + '"');
                }
                csvContent += rowValues.join(",") + "\r\n";
            }
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            
            // Set filename with date
            const today = new Date();
            const filename = `ORS_Records_${today.getFullYear()}-${(today.getMonth()+1).toString().padStart(2, '0')}-${today.getDate().toString().padStart(2, '0')}.csv`;
            link.setAttribute("download", filename);
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

</body>

</html>