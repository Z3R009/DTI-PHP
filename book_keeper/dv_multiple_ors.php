<?php
include '../DBConnection.php';

// Retrieve ORS IDs from URL
$ors_ids = $_GET['ids'] ?? [];

if (empty($ors_ids)) {
    echo "No ORS selected.";
    exit;
}

// Prepare a query to get details for the selected ORS
$placeholders = implode(',', array_fill(0, count($ors_ids), '?'));
$query = "SELECT 
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
WHERE ors.ors_id IN ($placeholders)";

$stmt = $connection->prepare($query);
$stmt->bind_param(str_repeat('i', count($ors_ids)), ...$ors_ids);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all ORS details
$ors_details = $result->fetch_all(MYSQLI_ASSOC);

if (empty($ors_details)) {
    echo "No ORS details found.";
    exit;
}

// Fetch all account titles for dropdown
$account_query = "SELECT * FROM account_title ORDER BY account_title ASC";
$account_result = $connection->query($account_query);

// Calculate totals and get common information
$total_amount = 0;
$payee_name = $ors_details[0]['payee_name'];
$payee_tin = $ors_details[0]['tin_no'];
$payee_address = $ors_details[0]['address'];
$fund_cluster = $ors_details[0]['fund_cluster'];

foreach ($ors_details as $ors) {
    $total_amount += $ors['total_amount'];
}

// Generate DV number
$current_date = date('Y-m-d');
$dv_query = "SELECT MAX(CAST(SUBSTRING(dv_no, -4) AS UNSIGNED)) as max_number 
             FROM dv 
             WHERE dv_no LIKE ? 
             AND DATE(date) = ?";
$dv_stmt = $connection->prepare($dv_query);
$dv_pattern = $fund_cluster . '-' . date('Y') . '-%';
$dv_stmt->bind_param("ss", $dv_pattern, $current_date);
$dv_stmt->execute();
$dv_result = $dv_stmt->get_result();
$dv_row = $dv_result->fetch_assoc();
$next_number = ($dv_row['max_number'] ?? 0) + 1;
$dv_no = $fund_cluster . '-' . date('Y') . '-' . str_pad($next_number, 4, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Create Multiple ORS DV</title>
    <meta content="Disbursement Voucher Management System for DTI" name="description">
    <meta content="disbursement, voucher, dti, finance, accounting" name="keywords">
    <link href="img/dti_logo.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i|Roboto+Mono:400,500,600,700&display=swap"
        rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet" href="css/dv.css">
    <link rel="stylesheet" href="csst/table.css">

    <style>
        input[readonly] {
            background-color: #f5f5f5 !important;
            color: #333 !important;
        }

        /* General Checkbox Styling */
        .custom-checkbox {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
        }

        /* Hide the default checkbox */
        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Custom checkbox design */
        .custom-checkbox .checkmark {
            width: 20px;
            height: 20px;
            background-color: #fff;
            border: 2px solid #007bff;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        /* Add checkmark icon when checked */
        .custom-checkbox input:checked+.checkmark::after {
            content: '\2713';
            /* Unicode checkmark */
            font-size: 16px;
            font-weight: bold;
            color: #fff;
        }

        /* Background color when checked */
        .custom-checkbox input:checked+.checkmark {
            background-color: #007bff;
            border-color: #0056b3;
        }

        /* Hover Effect */
        .custom-checkbox:hover .checkmark {
            background-color: #e9f5ff;
        }

        /* Disabled checkbox */
        .custom-checkbox input:disabled+.checkmark {
            background-color: #ccc;
            border-color: #aaa;
            cursor: not-allowed;
        }

        .custom-check .form-check-input {
            width: 25px;
            height: 25px;
            border: 2px solid #0d6efd;
            background-color: #f0f8ff;
            cursor: pointer;
        }

        .custom-check .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>

</head>

<body>

    <div class="card shadow" style="max-width: 900px; margin: auto;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>Create Multiple ORS DV
            </h2>
            <button class="btn btn-primary" aria-label="Close" onclick="window.location.href='dv.php';">Back</button>


        </div>

        <div class="card-body">
            <form action="process_multiple_dv.php" method="post" id="dvForm">
                <input type="hidden" name="ors_ids" value="<?php echo htmlspecialchars(json_encode($ors_ids)); ?>">
                <div class="form-cntainer">
                    <div class="form-section">
                        <h3><i class="bi bi-info-circle me-2"></i>General Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Fund Cluster</label>
                                <input type="text" class="form-control" name="fund_cluster"
                                    value="<?php echo htmlspecialchars($fund_cluster); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">DV No.</label>
                                <input type="text" class="form-control" name="dv_no"
                                    value="<?php echo htmlspecialchars($dv_no); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="bi bi-person me-2"></i>Payee Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Payee Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($payee_name); ?>"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">TIN/Employee No.</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($payee_tin); ?>"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($payee_address); ?>"
                            readonly>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="bi bi-list-check me-2"></i>Selected ORS Details</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ORS No.</th>
                                    <th>Date</th>
                                    <th>Account Title</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ors_details as $ors): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ors['ors_no']); ?></td>
                                        <td><?php echo date('F j, Y', strtotime($ors['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($ors['account_title']); ?></td>
                                        <td class="text-end">₱<?php echo number_format($ors['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total Amount:</th>
                                    <th class="text-end">₱<?php echo number_format($total_amount, 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- tax -->
                <div class=" form-section">
                    <h3><i class="bi bi-calculator me-2"></i>Breakdown of Expenses</h3>
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label class="form-label">Gross Amount</label>
                            <input type="number" class="form-control" name="total_amount" id="total_amount"
                                value="<?php echo $total_amount; ?>" step="0.01" readonly>
                        </div>
                        <div class="form-group half-width">
                            <div class="checkbox-item">
                                <input type="checkbox" class="apply_taxes" id="apply_taxes">
                                <label for="apply_taxes">With VAT</label>
                            </div>
                        </div>
                    </div>

                    <div id="tax_fields_container" class="tax-fields d-flex">
                        <div class="form-group half-width">
                            <label class="form-label">VAT <input type="number" class="tax-percentage"
                                    id="vat_percentage" name="vat" value="12" min="0" max="100" readonly>%</label>
                            <input type="number" class="form-control calculation-field" id="vat_amount"
                                name="vat_amount" step="0.01" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tax Base</label>
                            <input type="number" class="form-control calculation-field" id="tax_base" name="tax_base"
                                step="0.01">
                        </div>
                    </div>



                    <div class="form-section">
                        <div class="form-group">
                            <label class="form-label">Less:
                                <input type="number" class="tax-percentage" id="tax1_percentage" name="tax_1" value="5"
                                    min="0" max="100"> % Tax
                            </label>
                            <input type="number" class="form-control calculation-field" id="tax_1" name="tax_1_amount"
                                step="0.01">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Less:
                                <input type="number" class="tax-percentage" id="tax2_percentage" name="tax_2" value="2"
                                    min="0" max="100"> % Tax
                            </label>
                            <input type="number" class="form-control calculation-field" id="tax_2" name="tax_2_amount"
                                step="0.01">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Net Amount</label>
                            <input type="number" class="form-control calculation-field" id="net_amount"
                                name="net_amount" step="0.01" readonly>
                        </div>
                    </div>
                </div>


                <div class="form-section">
                    <h3><i class="bi bi-journal-text me-2"></i>Accounting Entry</h3>
                    <div class="table-responsive">
                        <table class="accounting-entry-table">
                            <thead>
                                <tr>
                                    <th colspan="2">Account Title</th>
                                    <th>Debit Amount</th>
                                    <th>Credit Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="accountingTableBody">
                                <?php foreach ($ors_details as $ors): ?>
                                    <tr>
                                        <td colspan="2">
                                            <select class="form-control account-select" name="account_titles[]" required>
                                                <option value="<?php echo $ors['account_id']; ?>" selected>
                                                    <?php echo htmlspecialchars($ors['account_title']); ?>
                                                </option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control debit-amount" name="debit_amounts[]"
                                                value="<?php echo $ors['total_amount']; ?>" step="0.01" readonly></td>
                                        <td><input type="number" class="form-control credit-amount" name="credit_amounts[]"
                                                step="0.01"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm delete-row"><i
                                                    class="bi bi-trash"></i></button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <select class="form-control account-select" name="account_titles[]">
                                            <option selected disabled>Select Cash Account</option>
                                            <?php
                                            $cash_account_query = "SELECT * FROM account_title WHERE account_code IN ('1010404000', '1010405000', '1010406000') ORDER BY account_title ASC";
                                            $cash_account_result = $connection->query($cash_account_query);
                                            while ($account = $cash_account_result->fetch_assoc()) {
                                                echo "<option value='" . $account['account_id'] . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control debit-amount" name="debit_amounts[]"
                                            step="0.01" readonly></td>
                                    <td><input type="number" class="form-control credit-amount" name="credit_amounts[]"
                                            step="0.01" readonly></td>
                                    <td><button type="button" class="btn btn-danger btn-sm delete-row"><i
                                                class="bi bi-trash"></i></button></td>
                                </tr>
                                <tr>
                                    <td>
                                        <button type="button" id="addAccountRow" class="btn btn-secondary">
                                            <i class="bi bi-plus-lg"></i> Add Row
                                        </button>
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check custom-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll"> &nbsp
                                            <label class="form-check-label" for="selectAll">Include
                                                Tax</label>
                                        </div>

                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="bi bi-person-check me-2"></i>Approver Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Chief Accountant</label>
                            <select class="form-control" name="chief_accountant">
                                <option>NEIL ANTHONY T. MORALA</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Regional Director</label>
                            <select class="form-control" name="regional_director">
                                <option>FLORA D. POLITUD-GABUNALES, CESO V</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" name="submit">
                        <i class="bi bi-check-circle me-1"></i> Submit DV
                    </button>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <!-- Tax calculation script -->
    <script>
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

            function setTaxFieldsEditability() {
                const isVatChecked = applyTaxesCheckbox.checked;
                if (isVatChecked) {
                    tax1Input.setAttribute("readonly", "readonly");
                    tax2Input.setAttribute("readonly", "readonly");
                } else {
                    tax1Input.removeAttribute("readonly");
                    tax2Input.removeAttribute("readonly");
                }
                tax1PercentageInput.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax2PercentageInput.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax1Input.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax2Input.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
            }

            function recalculateTaxAmounts() {
                const grossAmount = parseFloat(taxBaseInput.value) || 0;
                const tax1Percentage = parseFloat(tax1PercentageInput.value) || 0;
                const tax2Percentage = parseFloat(tax2PercentageInput.value) || 0;
                const tax1Amount = grossAmount * (tax1Percentage / 100);
                const tax2Amount = grossAmount * (tax2Percentage / 100);
                tax1Input.value = tax1Amount.toFixed(2);
                tax2Input.value = tax2Amount.toFixed(2);
                recalculateNetAmount();
            }

            function recalculateNetAmount() {
                const grossAmount = parseFloat(amountInput.value) || 0;
                const tax1Amount = parseFloat(tax1Input.value) || 0;
                const tax2Amount = parseFloat(tax2Input.value) || 0;
                const totalTaxes = tax1Amount + tax2Amount;
                const netAmount = grossAmount - totalTaxes;
                netAmountInput.value = netAmount.toFixed(2);
            }

            function calculate() {
                const grossAmount = parseFloat(amountInput.value) || 0;
                if (applyTaxesCheckbox.checked) {
                    const vatPercentage = 12;
                    const vatAmount = (grossAmount * vatPercentage) / (100 + vatPercentage);
                    const taxBase = grossAmount - vatAmount;
                    const tax1Amount = taxBase * 0.05;
                    const tax2Amount = taxBase * 0.02;
                    tax1PercentageInput.value = "5";
                    tax2PercentageInput.value = "2";
                    vatAmountInput.value = vatAmount.toFixed(2);
                    taxBaseInput.value = taxBase.toFixed(2);
                    tax1Input.value = tax1Amount.toFixed(2);
                    tax2Input.value = tax2Amount.toFixed(2);
                    netAmountInput.value = (grossAmount - tax1Amount - tax2Amount).toFixed(2);
                } else {
                    if (tax1PercentageInput.value === "" || tax1PercentageInput.value === "5") {
                        tax1PercentageInput.value = "0";
                    }
                    if (tax2PercentageInput.value === "" || tax2PercentageInput.value === "2") {
                        tax2PercentageInput.value = "0";
                    }
                    const tax1Percentage = parseFloat(tax1PercentageInput.value) || 0;
                    const tax2Percentage = parseFloat(tax2PercentageInput.value) || 0;
                    const tax1Amount = grossAmount * (tax1Percentage / 100);
                    const tax2Amount = grossAmount * (tax2Percentage / 100);
                    vatAmountInput.value = "0.00";
                    taxBaseInput.value = grossAmount.toFixed(2);
                    tax1Input.value = tax1Amount.toFixed(2);
                    tax2Input.value = tax2Amount.toFixed(2);
                    netAmountInput.value = (grossAmount - tax1Amount - tax2Amount).toFixed(2);
                }
                setTaxFieldsEditability();
            }

            // Attach event listeners
            amountInput.addEventListener("input", calculate);
            applyTaxesCheckbox.addEventListener("change", calculate);
            tax1PercentageInput.addEventListener("input", function () {
                if (!applyTaxesCheckbox.checked) recalculateTaxAmounts();
            });
            tax2PercentageInput.addEventListener("input", function () {
                if (!applyTaxesCheckbox.checked) recalculateTaxAmounts();
            });
            tax1Input.addEventListener("input", function () {
                if (!applyTaxesCheckbox.checked) recalculateNetAmount();
            });
            tax2Input.addEventListener("input", function () {
                if (!applyTaxesCheckbox.checked) recalculateNetAmount();
            });
            taxBaseInput.addEventListener("input", function () {
                if (!applyTaxesCheckbox.checked) recalculateTaxAmounts();
            });

            // Force calculation on page load
            setTaxFieldsEditability();
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


    <!-- add row and calculate totals -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tax1Input = document.getElementById('tax_1');
            const tax2Input = document.getElementById('tax_2');
            const tableBody = document.getElementById('accountingTableBody');
            const checkbox = document.getElementById('selectAll');
            const applyTaxesCheckbox = document.getElementById('apply_taxes');
            const addRowButton = document.getElementById('addAccountRow');

            // Add event listener for the Add Row button
            addRowButton.addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td colspan="2">
                        <select class="form-control account-select" name="account_titles[]" required>
                            <option selected disabled value="">Select Account</option>
                            ${accountOptions}
                        </select>
                    </td>
                    <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01"></td>
                    <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01"></td>
                    <td><button type="button" class="btn btn-danger btn-sm delete-row"><i class="bi bi-trash"></i></button></td>
                `;
                
                tableBody.appendChild(newRow);
                setupAccountSelect(newRow);
                setupCalculationListeners(newRow);
            });

            // Utility: Create select options from PHP
            const accountOptions = `<?php
            $account_result->data_seek(0);
            while ($account = $account_result->fetch_assoc()) {
                echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
            }
            ?>`;

            // Helper to remove previously added tax rows
            function removeTaxRows() {
                const rows = tableBody.querySelectorAll('tr[data-tax="true"]');
                rows.forEach(row => row.remove());
            }

            // Function to add tax credit rows
            function addRowWithCredit(creditAmount, label = '', accountId = '') {
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-tax', 'true');

                newRow.innerHTML = `
                <td colspan="2">
                        <select class="form-control account-select" name="account_titles[]">
                        <option selected disabled value="">Select Account</option>
                        <?php
                            // Define the specific account codes we want to show
                            $accountCodes = ['2020101000'];

                            // Query only the specific cash accounts
                            $cash_account_query = "SELECT * FROM account_title WHERE account_code IN ('2020101000') ORDER BY account_title ASC";
                            $cash_account_result = $connection->query($cash_account_query);

                            while ($account = $cash_account_result->fetch_assoc()) {
                            echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                    <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01" readonly></td>
                    <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" value="${creditAmount.toFixed(2)}" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm delete-row"><i class="bi bi-trash"></i></button></td>
            `;

                tableBody.appendChild(newRow);
                setupAccountSelect(newRow);
                setupCalculationListeners(newRow);
            }

            // Function to setup account select with Select2
            function setupAccountSelect(row) {
                const accountSelect = row.querySelector('.account-select');
                
                // Initialize Select2
                $(accountSelect).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Select Account',
                    allowClear: true
                });
            }

            // Function to setup calculation listeners for a row
            function setupCalculationListeners(row) {
                const debitInput = row.querySelector('.debit-amount');
                const creditInput = row.querySelector('.credit-amount');
                const deleteButton = row.querySelector('.delete-row');

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

                if (deleteButton) {
                    deleteButton.addEventListener('click', function () {
                        // Don't delete if it's the only row in tbody
                        if (tableBody.querySelectorAll('tr').length > 1) {
                            row.remove();
                            calculateTotals();
                        } else {
                            alert("Cannot delete the last row. At least one account entry is required.");
                        }
                    });
                }
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

            // Checkbox handler
            checkbox.addEventListener('change', function () {
                removeTaxRows(); // Always clean before adding

                if (this.checked) {
                    const tax1Amount = parseFloat(tax1Input.value) || 0;
                    const tax2Amount = parseFloat(tax2Input.value) || 0;

                    if (tax1Amount > 0) {
                        addRowWithCredit(tax1Amount, 'Tax 1');
                    }
                    if (tax2Amount > 0) {
                        addRowWithCredit(tax2Amount, 'Tax 2');
                    }
                }
                calculateTotals();
            });

            // Add event listener for tax changes
            applyTaxesCheckbox.addEventListener('change', function() {
                setTimeout(() => {
                    if (checkbox.checked) {
                        checkbox.click(); // Uncheck
                        checkbox.click(); // Check again to refresh tax rows
                    }
                }, 100);
            });

            tax1Input.addEventListener('input', function() {
                if (checkbox.checked) {
                    checkbox.click(); // Uncheck
                    checkbox.click(); // Check again to refresh tax rows
                }
            });

            tax2Input.addEventListener('input', function() {
                if (checkbox.checked) {
                    checkbox.click(); // Uncheck
                    checkbox.click(); // Check again to refresh tax rows
                }
            });

            // Setup initial rows
            const initialRows = tableBody.querySelectorAll('tr');
            initialRows.forEach(row => {
                setupAccountSelect(row);
                setupCalculationListeners(row);
            });
        });
    </script>

    <!-- checkbox -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const messageContainer = document.getElementById('payeeMessage');
            const submitSelectedBtn = document.getElementById('submitSelected');
            let firstSelectedPayee = null;

            function updateCheckboxStates(clickedCheckbox) {
                const isChecked = clickedCheckbox.checked;
                const clickedPayee = clickedCheckbox.dataset.payee;

                if (isChecked) {
                    if (!firstSelectedPayee) {
                        firstSelectedPayee = clickedPayee;
                    }

                    rowCheckboxes.forEach(cb => {
                        if (cb.dataset.payee !== firstSelectedPayee) {
                            cb.checked = false;
                            cb.disabled = true;
                        } else {
                            cb.disabled = false;
                        }
                    });
                } else {
                    const remainingChecked = Array.from(rowCheckboxes).filter(cb => cb.checked);
                    if (remainingChecked.length === 0) {
                        firstSelectedPayee = null;
                        rowCheckboxes.forEach(cb => cb.disabled = false);
                        messageContainer.style.display = 'none';
                    }
                }

                updateBulkUI();
            }

            function updateBulkUI() {
                const checkedBoxes = Array.from(rowCheckboxes).filter(cb => cb.checked);
                const payees = checkedBoxes.map(cb => cb.dataset.payee);
                const allSamePayee = new Set(payees).size <= 1;

                if (checkedBoxes.length >= 2 && allSamePayee) {
                    messageContainer.style.display = 'none';
                    submitSelectedBtn.style.display = 'inline-block';
                } else if (checkedBoxes.length >= 1 && !allSamePayee) {
                    messageContainer.textContent = 'Cannot select multiple rows with different payees.';
                    messageContainer.style.display = 'block';
                    submitSelectedBtn.style.display = 'none';
                } else if (checkedBoxes.length === 1) {
                    messageContainer.textContent = 'Please select at least 2 ORS entries with the same payee.';
                    messageContainer.style.display = 'block';
                    submitSelectedBtn.style.display = 'none';
                } else {
                    messageContainer.style.display = 'none';
                    submitSelectedBtn.style.display = 'none';
                }
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    updateCheckboxStates(this);
                });
            });

            // Handle submit button click
            submitSelectedBtn.addEventListener('click', function () {
                const selected = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                    .map(cb => cb.value);

                if (selected.length >= 2) {
                    const params = new URLSearchParams();
                    selected.forEach(id => params.append('ids[]', id));
                    window.location.href = 'dv_multiple_ors.php?' + params.toString();
                } else {
                    alert('Please select at least 2 ORS entries with the same payee.');
                }
            });
        });
    </script>


</body>

</html>