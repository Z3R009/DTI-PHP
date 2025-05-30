<?php
include '../DBConnection.php';

$sql_account = "SELECT DISTINCT at.account_id, at.account_title, at.account_code, p.oopap_id, o.oopap_name
                FROM account_title at
                INNER JOIN project p ON at.account_id = p.account_id
                INNER JOIN oopap o ON p.oopap_id = o.oopap_id
                ORDER BY o.oopap_name, at.account_code";

$result_account = $connection->query($sql_account);

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
              ORDER BY ors.date DESC, ors_no DESC";

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

    <title>Book Keeper - Obligation Request and Status</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/processed_ors.css">
    <link rel="stylesheet" href="css/table.css">
    <link href="img/dti_logo.png" rel="icon">

</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">Processed ORS Records</h1>
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
                <!-- <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="card-title mb-0">Obligation Request Records</h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="printTable()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                            <i class="bi bi-file-earmark-excel"></i> Export
                        </button>
                    </div>
                </div> -->

                <!-- ors list -->
                <div>
                    <div class="table-responsive">
                        <table class="datatable">
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
                                        echo "<td><span class='status-processed'><i class='bi bi-check-circle-fill'></i> Obligated</span></td>";
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




    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Filter -->
    <script>
        // JavaScript to handle filtering with visual feedback
        document.addEventListener('DOMContentLoaded', function () {
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
            printWindow.onload = function () {
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
            const filename = `ORS_Records_${today.getFullYear()}-${(today.getMonth() + 1).toString().padStart(2, '0')}-${today.getDate().toString().padStart(2, '0')}.csv`;
            link.setAttribute("download", filename);

            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    <?php include "includes/common_scripts.php"; ?>

</body>

</html>