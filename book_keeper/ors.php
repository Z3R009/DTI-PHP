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
    $payee_name = $_POST['payee_name'];
    $tin_no = $_POST['tin_no'];
    $address = $_POST['address'];
    $notes = $_POST['notes'];
    $rc_id = $_POST['rc_id'];
    $object_code_id = $_POST['object_code_id'];
    $oopap_id = $_POST['oopap_id'];
    $amount = $_POST['amount'];
    $approver_id = $_POST['approver_id'];
    $budget_officer = $_POST['budget_officer'];

    $sql = "INSERT INTO ors (fund_cluster_id, date, ors_no, payee_name, tin_no, address, notes, rc_id, object_code_id, oopap_id, amount, approver_id, budget_officer) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $sql = "INSERT INTO ors (fund_cluster_id, date, ors_no, payee_name, tin_no, address, notes, rc_id, object_code_id, oopap_id, amount, approver_id, budget_officer) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($connection->error));
    }

    $stmt->bind_param("isssisssiidis", $fund_cluster_id, $date, $ors_no, $payee_name, $tin_no, $address, $notes, $rc_id, $object_code_id, $oopap_id, $amount, $approver_id, $budget_officer);
    if ($stmt->execute()) {
        header('Location: ors.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
}


// Query to fetch account titles and their corresponding UACS codes
$sql_object_code = "SELECT object_code_id, object_name FROM financial_object_code";
$result_object_code = $connection->query($sql_object_code);


// retrieve responsibility

$sql_responsibility_center = "SELECT rc_id, code FROM responsibility_center";
$result_responsibility_center = $connection->query($sql_responsibility_center);

// retrieve fund_cluster
$sql_fund_cluster = "SELECT fund_cluster_id, fund_cluster_name FROM fund_cluster";
$result_fund_cluster = $connection->query($sql_fund_cluster);


// retrieve oo/pap
$sql_oopap = "SELECT oopap_id, oopap_name FROM oopap";
$result_oopap = $connection->query($sql_oopap);


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
    <!-- <link rel="stylesheet" href="ors.css"> -->
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

    <?php include "Includes/header.php";?>

    <?php include "Includes/sidebar.php";?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Obligation Request and Status</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="content-wrapper">
                <div class="form-container">
                    <h2 class="form-title">Obligation Request And Status</h2>

                    <!-- General Information Section -->

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
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" id="dvDate" name="date">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Obligation Request No.</label>
                                    <input type="text" class="form-control" name="ors_no" required>
                                </div>
                            </div>
                        </div>


                        <!-- Payee Details Section -->
                        <div class="form-section">
                            <h3> Payee Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Payee Name</label>
                                    <input type="text" class="form-control" name="payee_name" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">TIN/Employee No.</label>
                                    <input type="text" class="form-control" name="tin_no" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <select class="form-control" name="address">
                                    <option>Koronadal City</option>

                                </select>
                            </div>
                        </div>

                        <!-- Payment Details Section -->
                        <div class="form-section">
                            <h3></h3>
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label class="form-label">NOTES</label>
                                    <textarea class="form-control" name="notes"></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Responsibility Center</label>
                                    <select class="form-control" name="rc_id">
                                        <option selected disabled>Select Responsibility Center</option>
                                        <?php
                                        while ($row = $result_responsibility_center->fetch_assoc()) {
                                            echo "<option value='" . htmlspecialchars($row['rc_id']) . "'>" . htmlspecialchars($row['code']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                            </div>
                        </div>

                        <!-- Accounting Entry Section -->
                        <div class="form-section">
                            <h3>Accounting Entry</h3>
                            <div class="table-responsive">
                                <table class="accounting-entry-table">
                                    <thead>
                                        <tr>
                                            <th>Account Title</th>
                                            <th>OO/PAP</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="accounting-table-body">
                                        <!-- First row -->
                                        <tr class="entry-row">
                                            <td>
                                                <select class="form-control" name="object_code_id">
                                                    <option selected disabled>Select Account</option>
                                                    <?php
                                                    while ($row = $result_object_code->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($row['object_code_id']) . "'>" . htmlspecialchars($row['object_name']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control" name="oopap_id">
                                                    <option selected disabled>Select OO/PAP</option>
                                                    <?php
                                                    while ($row = $result_oopap->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($row['oopap_id']) . "'>" . htmlspecialchars($row['oopap_name']) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control" name="amount" step="0.01">
                                            </td>
                                        </tr>

                                        <!-- Add Row button row (this will stay at the bottom) -->
                                        <tr id="add-row-container">
                                            <td colspan="4" class="text-left">
                                                <button type="button" name="submit" id="addAccountRow"
                                                    class="btn btn-secondary">
                                                    <ion-icon name="add-outline"></ion-icon> Add Row
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


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
        </section>

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


    <!-- add row  -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tableBody = document.querySelector("#accounting-table-body");
            const addRowContainer = document.querySelector("#add-row-container");

            // Fetch the options for object_code_id and oopap_id from the first row
            const objectCodeOptions = Array.from(tableBody.querySelector(".entry-row select[name='object_code_id']").options)
                .map(option => ({ value: option.value, text: option.text }));
            const oopapOptions = Array.from(tableBody.querySelector(".entry-row select[name='oopap_id']").options)
                .map(option => ({ value: option.value, text: option.text }));

            // Add new row functionality
            document.querySelector("#addAccountRow").addEventListener("click", function () {
                let newRow = document.createElement("tr");
                newRow.classList.add("entry-row");

                // Create the Account Title dropdown
                const objectCodeSelect = document.createElement("select");
                objectCodeSelect.className = "form-control";
                objectCodeSelect.name = "object_code_id";
                objectCodeSelect.innerHTML = '<option selected disabled>Select Account</option>';
                objectCodeOptions.forEach(option => {
                    const optionElement = document.createElement("option");
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    objectCodeSelect.appendChild(optionElement);
                });

                // Create the OO/PAP dropdown
                const oopapSelect = document.createElement("select");
                oopapSelect.className = "form-control";
                oopapSelect.name = "oopap_id";
                oopapSelect.innerHTML = '<option selected disabled>Select OO/PAP</option>';
                oopapOptions.forEach(option => {
                    const optionElement = document.createElement("option");
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    oopapSelect.appendChild(optionElement);
                });

                // Create the Amount input
                const amountInput = document.createElement("input");
                amountInput.type = "number";
                amountInput.className = "form-control";
                amountInput.name = "amount";
                amountInput.step = "0.01";

                // Append the elements to the new row
                const accountTitleCell = document.createElement("td");
                accountTitleCell.appendChild(objectCodeSelect);
                const oopapCell = document.createElement("td");
                oopapCell.appendChild(oopapSelect);
                const amountCell = document.createElement("td");
                amountCell.appendChild(amountInput);

                newRow.appendChild(accountTitleCell);
                newRow.appendChild(oopapCell);
                newRow.appendChild(amountCell);

                // Insert the new row before the "Add Row" button row
                tableBody.insertBefore(newRow, addRowContainer);
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
</body>

</html>