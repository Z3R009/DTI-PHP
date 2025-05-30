<?php
include '../DBConnection.php';

if (isset($_POST['submit'])) {
    echo "Form submitted!";

    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $date = $_POST['date'];
    $dv_no = $_POST['dv_no'];
    $ors_no = $_POST['ors_id'];
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
    $account_titles = $_POST['account_titles'];
    $debit_amounts = $_POST['debit_amounts'];
    $credit_amounts = $_POST['credit_amounts'];
    $connection->begin_transaction();

    try {
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

        $account_id = 1;

        $account_query = "SELECT account_id FROM account_name WHERE account_id = ?";
        $account_stmt = $connection->prepare($account_query);
        if ($account_stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($connection->error));
        }
        $account_stmt->bind_param("i", $account_id);
        if (!$account_stmt->execute()) {
            throw new Exception("Error checking account ID: " . $account_stmt->error);
        }
        $account_result = $account_stmt->get_result();
        if ($account_result->num_rows === 0) {
            throw new Exception("Account ID not found in account_name table");
        }
        $account_stmt->close();

        $sql = "INSERT INTO dv (date, dv_no, ors_id, account_id, payment_mode, vat, vat_amount, tax_base, tax_1, tax_1_amount, tax_2, tax_2_amount, net_amount, chief_accountant, regional_director) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($connection->error));
        }

        $stmt->bind_param(
            "ssiisddddddddss",
            $date,
            $dv_no,
            $ors_id,
            $account_id,
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

        for ($i = 0; $i < count($account_titles); $i++) {
            if (empty($account_titles[$i]))
                continue;

            $account_id = $account_titles[$i];
            $debit = !empty($debit_amounts[$i]) ? $debit_amounts[$i] : 0;
            $credit = !empty($credit_amounts[$i]) ? $credit_amounts[$i] : 0;

            $type = ($debit > 0) ? 'debit' : 'credit';
            $amount = ($debit > 0) ? $debit : $credit;

            if ($amount == 0)
                continue;

            $history_sql = "INSERT INTO dv_history (dv_id, account_id, type, amount) VALUES (?, ?, ?, ?)";
            $history_stmt = $connection->prepare($history_sql);
            if ($history_stmt === false) {
                throw new Exception('Prepare failed: ' . htmlspecialchars($connection->error));
            }

            $history_stmt->bind_param("iisd", $dv_id, $account_id, $type, $amount);

            if (!$history_stmt->execute()) {
                throw new Exception("Error: " . $history_stmt->error);
            }

            $history_stmt->close();
        }

        $connection->commit();

        header("Location: dv_form.php?dv_no=$dv_no");
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }

    $connection->close();
}

$select_ors = mysqli_query($connection, "
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

$select_dv = mysqli_query($connection, "
SELECT 
    ors.*,
    ors.total_amount AS ors_total_amount,
    dv.*, 
    account_title.account_title, 
    approver.approver_name,
    CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
    responsibility_center.code,
    oopap.oopap_name,
    payee.payee_name,
    payee.tin_no,
    payee.address,
    services.services_name
FROM dv
LEFT JOIN ors ON dv.ors_id = ors.ors_id
LEFT JOIN account_title ON ors.account_id = account_title.account_id
LEFT JOIN approver ON ors.approver_id = approver.approver_id
LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
LEFT JOIN payee ON ors.payee_id = payee.payee_id
LEFT JOIN services ON ors.services_id = services.services_id
ORDER BY dv.date DESC, dv.dv_no DESC;
");

?>

<?php
// Fetch filter values
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : '';
$service = isset($_GET['service']) ? mysqli_real_escape_string($connection, $_GET['service']) : '';

// Build WHERE clause
$whereClauses = [];
$params = [];
$types = '';

// Always filter by year
$whereClauses[] = "YEAR(dv.date) = ?";
$params[] = $year;
$types .= 'i';

// Add month filter if selected
if (!empty($month)) {
    $whereClauses[] = "MONTH(dv.date) = ?";
    $params[] = $month;
    $types .= 'i';
}

// Add service filter if selected
if (!empty($service)) {
    $whereClauses[] = "services.services_name = ?";
    $params[] = $service;
    $types .= 's';
}

// Build final WHERE SQL
$whereSql = !empty($whereClauses) ? ' WHERE ' . implode(' AND ', $whereClauses) : '';

// Final query
$dv_query = "SELECT 
    ors.*,
    ors.total_amount AS ors_total_amount,
    dv.*, 
    account_title.account_title, 
    approver.approver_name,
    CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
    responsibility_center.code,
    oopap.oopap_name,
    payee.payee_name,
    payee.tin_no,
    payee.address,
    services.services_name
FROM dv
LEFT JOIN ors ON dv.ors_id = ors.ors_id
LEFT JOIN account_title ON ors.account_id = account_title.account_id
LEFT JOIN approver ON ors.approver_id = approver.approver_id
LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
LEFT JOIN payee ON ors.payee_id = payee.payee_id
LEFT JOIN services ON ors.services_id = services.services_id
$whereSql
ORDER BY dv.date DESC, dv.dv_no DESC";

// Prepare and bind
$stmt = $connection->prepare($dv_query);

if ($stmt === false) {
    die('Prepare failed: ' . $connection->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$select_dv = $stmt->get_result();

if ($select_dv->num_rows === 0) {
    echo "<p>No records found for the selected filters.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Bookkeeper</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="img/dti_logo.png" rel="icon">

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
    <link rel="stylesheet" href="css/processed_dv.css">
    <link rel="stylesheet" href="css/dv.css">
    <link rel="stylesheet" href="css/table.css">
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex align-items-center">
            <h1 class="mb-0">Disbursement Voucher</h1>
            <div class="ms-auto">
                <button class="btn btn-primary" onclick="window.location.href='dv.php'"> <i
                        class="bi bi-list-check me-1"></i>
                    DV Form
                </button>
                <button class="btn btn-primary" onclick="window.location.href='dv_w-out.php'"> <i
                        class="bi bi-file-earmark-plus me-1"></i>
                    DV Form without ORS
                </button>
            </div>
        </div>

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
                        $services_query = "SELECT DISTINCT services_name FROM services ORDER BY services_name ASC";
                        $services_result = $connection->query($services_query);
                        while ($row = $services_result->fetch_assoc()) {
                            $selected = ($row['services_name'] == $service) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['services_name']) . "' $selected>" . htmlspecialchars($row['services_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row mt-2">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" onclick="applyFilter()">
                        <i class="bi bi-funnel me-1"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="form-container">

                <div class="tab-content">
                    <div class="card">
                        <div class="card-body">
                            <!-- Table with stripped rows -->
                            <table class="datatable">
                                <thead>
                                    <tr>
                                        <th>ORS No.</th>
                                        <th>DV No.</th>
                                        <th>Date</th>
                                        <th>Payee Name</th>
                                        <th>Account Title</th>
                                        <th>Total Amount</th>
                                        <th>Approver Name</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select_dv)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['ors_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['dv_no']); ?></td>
                                            <td>
                                                <?php
                                                $date = new DateTime($row['date']);
                                                echo htmlspecialchars($date->format('F j, Y'));
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                            <td><?php echo htmlspecialchars($row['ors_total_amount']); ?></td>
                                            <td><?php echo htmlspecialchars($row['approver_name']); ?></td>
                                            <td>
                                                <a href="dv_form.php?dv_no=<?php echo urlencode($row['dv_no']); ?>"
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>


    <!-- filter -->
    <script>
        // JavaScript to handle filtering with visual feedback
        document.addEventListener('DOMContentLoaded', function () {
            // Add visual indicators for active filters
            highlightActiveFilters();
        });

        function applyFilter() {
            // Show loading indicator
            const tableBody = document.querySelector('tbody');
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><i class="bi bi-arrow-repeat spin me-2"></i> Loading records...</td></tr>';

            var year = document.getElementById('yearFilter').value;
            var month = document.getElementById('monthFilter').value;
            var service = document.getElementById('servicesFilter').value;

            // Build URL with only non-empty parameters
            var params = new URLSearchParams();
            if (year) params.append('year', year);
            if (month) params.append('month', month);
            if (service) params.append('service', service);

            // Get the current URL and append the filters
            var newUrl = window.location.origin + window.location.pathname;
            if (params.toString()) {
                newUrl += '?' + params.toString();
            }

            // Update the URL with the selected filters
            window.location.href = newUrl;
        }

        function resetFilters() {
            window.location.href = window.location.pathname;
        }

        function highlightActiveFilters() {
            // Get all filter dropdowns
            const filterElements = ['yearFilter', 'monthFilter', 'servicesFilter'];

            filterElements.forEach(id => {
                const select = document.getElementById(id);
                if (select && select.value) {
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
    </script>

</body>

</html>