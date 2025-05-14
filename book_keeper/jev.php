<?php
include '../DBConnection.php';

// insert

if (isset($_POST['submit'])) {
    echo "Form submitted!";


    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $date = $_POST['date'];
    $dv_no = $_POST['dv_no'];
    $ors_no = $_POST['ors_no'];
    $jev_no = $_POST['jev_no'];
    $administrative_aide = $_POST['administrative_aide'];
    $accountant = $_POST['accountant'];


    $connection->begin_transaction();

    try {
        // Get ors_id from ors_no
        $ors_query = "SELECT ors_id FROM ors WHERE ors_no = ?";
        $ors_stmt = $connection->prepare($ors_query);
        if ($ors_stmt === false) {
            throw new Exception('Prepare failed (ORS): ' . htmlspecialchars($connection->error));
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

        // Get dv_id from dv_no
        $dv_query = "SELECT dv_id FROM dv WHERE dv_no = ?";
        $dv_stmt = $connection->prepare($dv_query);
        if ($dv_stmt === false) {
            throw new Exception('Prepare failed (DV): ' . htmlspecialchars($connection->error));
        }
        $dv_stmt->bind_param("s", $dv_no);
        if (!$dv_stmt->execute()) {
            throw new Exception("Error getting DV ID: " . $dv_stmt->error);
        }
        $dv_result = $dv_stmt->get_result();
        if ($dv_result->num_rows === 0) {
            throw new Exception("DV number not found: " . $dv_no);
        }
        $dv_row = $dv_result->fetch_assoc();
        $dv_id = $dv_row['dv_id'];
        $dv_stmt->close();

        // Insert into jev table
        $sql = "INSERT INTO jev (date, dv_id, ors_id, jev_no, administrative_aide, accountant) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare failed (JEV insert): ' . htmlspecialchars($connection->error));
        }

        $stmt->bind_param(
            "siisss",
            $date,
            $dv_id,
            $ors_id,
            $jev_no,
            $administrative_aide,
            $accountant
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert error: " . $stmt->error);
        }

        $stmt->close();

        // Update the dv status to 'Processed'
        $update_status_sql = "UPDATE dv SET status = 'Endorsed' WHERE dv_id = ?";
        $update_status_stmt = $connection->prepare($update_status_sql);
        if ($update_status_stmt === false) {
            throw new Exception('Prepare failed (ORS update): ' . htmlspecialchars($connection->error));
        }

        $update_status_stmt->bind_param("i", $dv_id);
        if (!$update_status_stmt->execute()) {
            throw new Exception("Error updating ORS status: " . $update_status_stmt->error);
        }
        $update_status_stmt->close();

        // Commit transaction
        $connection->commit();

        // Redirect
        header("Location: jev_form.php?jev_no=$jev_no");
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }

    $connection->close();
}


$select_dv = mysqli_query($connection, "
SELECT 
    ors.*,
    ors.total_amount AS ors_total_amount,
    dv.*, 
    account_title.account_title, 
    account_title.account_code,
    approver.approver_name,
    CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
    responsibility_center.code,
    oopap.oopap_name,
    payee.payee_name,
    payee.tin_no,
    payee.address
FROM dv
LEFT JOIN ors ON dv.ors_id = ors.ors_id
LEFT JOIN account_title ON ors.account_id = account_title.account_id
LEFT JOIN approver ON ors.approver_id = approver.approver_id
LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
LEFT JOIN payee ON ors.payee_id = payee.payee_id

WHERE dv.status = 'Pending'
;


");

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Bookkeeper - Journal Entry Voucher</title>
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
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/jev.css">
    <link rel="stylesheet" href="css/table.css">
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex align-items-center">
            <h1 class="mb-0">Journal Entry Voucher</h1>
            <div class="ms-auto">
                <button class="btn btn-primary" onclick="window.location.href='processed_jev.php'">
                    View Processed JEV
                </button>
            </div>
        </div>



        <div class="content-wrapper">
            <div class="form-container">
                <h2></h2>

                <div class="tab-content">
                    <div>
                        <div class="card">
                            <div class="card-body">
                                <table class="datatable">
                                    <thead>
                                        <tr>
                                            <th>DV No.</th>
                                            <th>Date</th>
                                            <th>Fund Cluster</th>
                                            <th>Payee Name</th>
                                            <th>Account Title</th>
                                            <th>Amount</th>
                                            <th>Approver</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($select_dv)) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['dv_no']); ?></td>
                                                <td>
                                                    <?php
                                                    $date = new DateTime($row['date']);
                                                    echo htmlspecialchars($date->format('F j, Y')); // Example: "April 7, 2025"
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['fund_cluster']); ?></td>
                                                <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['ors_total_amount']); ?></td>
                                                <td><?php echo htmlspecialchars($row['approver_name']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-primary view-details"
                                                        data-id="<?php echo $row['dv_id']; ?>">
                                                        <i class="bi bi-eye" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title="View Details"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </main>
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
                                    <label class="form-label">Fund Cluster</label>
                                    <input type="text" class="form-control" id="fund_cluster" name="fund_cluster">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date">
                                </div>

                                <!-- <script>
                                    // Set the current date as default
                                    document.addEventListener("DOMContentLoaded", function () {
                                        const dateInput = document.getElementById("date");
                                        if (!dateInput.value) {
                                            const today = new Date().toISOString().split('T')[0];
                                            dateInput.value = today;
                                        }
                                    });
                                </script> -->
                                <div class="form-group">
                                    <label class="form-label">ORS No.</label>
                                    <input type="text" class="form-control" id="ors_no" name="ors_no" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">DV No.</label>
                                    <input type="text" class="form-control" id="dv_no" name="dv_no" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">JEV No.</label>
                                    <input type="text" class="form-control" name="jev_no" id="jev_no"
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <!-- Payee Details Section -->
                        <div class="form-section">
                            <h3>Payee Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Payee Name</label>
                                    <input type="text" class="form-control" id="payee_name" readonly>
                                </div>
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>Account Name</th>
                                    <th>UACS Object Code</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select_dv)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                                        <td><?php echo number_format($row['amount'], 2); ?></td>

                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>


                        <div class="form-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Administrative Aide</label>
                                    <select class="form-control" name="administrative_aide">
                                        <option>JINNARD B. LUBATON</option>

                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Accountant III</label>
                                    <select class="form-control" name="accountant">
                                        <option>NEIL ANTHONY T. MORALA</option>

                                    </select>
                                </div>
                            </div>
                        </div>


                        <!-- Buttons -->
                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary" name="submit">Print</button>
                        </div>
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
                        const dvId = this.getAttribute('data-id');
                        fetch(`get_jev_details.php?id=${dvId}`)
                            .then(response => response.json())
                            .then(data => {
                                // Populate the form fields with the returned data
                                document.getElementById('ors_no').value = data.ors_no;
                                document.getElementById('dv_no').value = data.dv_no;
                                document.getElementById('payee_name').value = data.payee_name;
                                document.getElementById('fund_cluster').value = data.fund_cluster;

                                // Populate the accounts table dynamically
                                const tableBody = document.querySelector('#dvFormModal table tbody');
                                tableBody.innerHTML = '';  // Clear any existing rows

                                // Initialize totals
                                let totalDebit = 0;
                                let totalCredit = 0;

                                // Add account rows
                                data.accounts.forEach(account => {
                                    const row = document.createElement('tr');
                                    const amount = parseFloat(account.amount) || 0;

                                    // Determine if this should be debit or credit based on account type
                                    const isDebit = account.type === 'debit' || account.type === 'asset' || account.type === 'expense';
                                    const debitAmount = isDebit ? amount : 0;
                                    const creditAmount = !isDebit ? amount : 0;

                                    // Add to totals
                                    totalDebit += debitAmount;
                                    totalCredit += creditAmount;

                                    row.innerHTML = `
                            <td>${account.account_title}</td>
                            <td>${account.account_code}</td>
                            <td>${debitAmount.toFixed(2)}</td>
                            <td>${creditAmount.toFixed(2)}</td>
                        `;
                                    tableBody.appendChild(row);
                                });

                                // Add totals row
                                const totalsRow = document.createElement('tr');
                                totalsRow.className = 'table-active';
                                totalsRow.innerHTML = `
                        <td colspan="2"><strong>TOTAL</strong></td>
                        <td><strong>${totalDebit.toFixed(2)}</strong></td>
                        <td><strong>${totalCredit.toFixed(2)}</strong></td>
                    `;
                                tableBody.appendChild(totalsRow);

                                // Optional: Add balance check row
                                if (Math.abs(totalDebit - totalCredit) > 0.01) { // Use small threshold for floating point comparison
                                    const balanceRow = document.createElement('tr');
                                    balanceRow.className = 'table-danger';
                                    balanceRow.innerHTML = `
                            <td colspan="2"><strong>OUT OF BALANCE</strong></td>
                            <td colspan="2" class="text-center"><strong>${Math.abs(totalDebit - totalCredit).toFixed(2)}</strong></td>
                        `;
                                    tableBody.appendChild(balanceRow);
                                }

                                // Show modal
                                modal.style.display = 'block';

                                // Optionally trigger any additional calculations or setup here
                                setTimeout(() => {
                                    calculate(); // Trigger calculations if needed
                                    generateDVNumber(); // Generate DV number if needed
                                }, 100);
                            })
                            .catch(error => console.error('Error fetching DV details:', error));
                    });
                });

                // Close modal
                closeModalBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });

                // Close modal if clicked outside the modal content
                window.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        </script>

        <!-- mode of payment -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const checkboxes = document.querySelectorAll('input[name="payment_mode"]');
                const otherText = document.getElementById('otherText');

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        if (this.checked) {
                            checkboxes.forEach(cb => {
                                if (cb !== this) {
                                    cb.checked = false;
                                }
                            });

                            // Enable/Disable text field based on "Others" selection
                            if (this.id === "others") {
                                otherText.disabled = false;
                            } else {
                                otherText.disabled = true;
                                otherText.value = ""; // Clear input if another option is selected
                            }
                        }
                    });
                });
            });
        </script>

        <!-- tax calculation -->
        <script>
            let calculate; // Declare calculate in wider scope

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

                calculate = function () { // Assign calculate function to the wider scope variable
                    const grossAmount = parseFloat(amountInput.value) || 0;

                    if (applyTaxesCheckbox.checked) {
                        // With VAT calculation
                        // VAT calculation (12% of gross)
                        const vatPercentage = 12;
                        const vatAmount = (grossAmount * vatPercentage) / (100 + vatPercentage);

                        // Tax base is gross minus VAT
                        const taxBase = grossAmount - vatAmount;

                        // Calculate 5% and 2% from tax base
                        const tax1Amount = taxBase * 0.05; // 5% with VAT
                        const tax2Amount = taxBase * 0.02; // 2% with VAT

                        // Update tax percentage displays
                        tax1PercentageInput.value = "5";
                        tax2PercentageInput.value = "2";

                        // Net amount is gross amount minus the sum of taxes
                        const totalTaxes = tax1Amount + tax2Amount;
                        const netAmount = grossAmount - totalTaxes;

                        // Update form fields
                        vatAmountInput.value = vatAmount.toFixed(2);
                        taxBaseInput.value = taxBase.toFixed(2);
                        tax1Input.value = tax1Amount.toFixed(2);
                        tax2Input.value = tax2Amount.toFixed(2);
                        netAmountInput.value = netAmount.toFixed(2);

                        // Show tax fields
                        document.getElementById('tax_fields_container').style.display = 'block';

                        // Add BIR-related rows
                        addBIRRows(tax1Amount, tax2Amount);
                    } else {
                        // Without VAT - use 3% and 1% tax rates
                        const tax1Amount = grossAmount * 0.03; // 3% without VAT
                        const tax2Amount = grossAmount * 0.01; // 1% without VAT

                        // Update tax percentage displays
                        tax1PercentageInput.value = "3";
                        tax2PercentageInput.value = "1";

                        // Net amount is gross amount minus the sum of taxes
                        const totalTaxes = tax1Amount + tax2Amount;
                        const netAmount = grossAmount - totalTaxes;

                        // Update form fields
                        vatAmountInput.value = "0.00";
                        taxBaseInput.value = grossAmount.toFixed(2);
                        tax1Input.value = tax1Amount.toFixed(2);
                        tax2Input.value = tax2Amount.toFixed(2);
                        netAmountInput.value = netAmount.toFixed(2);

                        // Hide VAT fields but show tax fields
                        document.getElementById('tax_fields_container').style.display = 'none';

                        // Add BIR-related rows
                        addBIRRows(tax1Amount, tax2Amount);
                    }
                };

                // Function to add BIR-related rows
                // function addBIRRows(tax1Amount, tax2Amount) {
                //     const tableBody = document.getElementById('accountingTableBody');

                //     // Clear existing BIR rows
                //     const existingRows = tableBody.querySelectorAll('tr');
                //     existingRows.forEach(row => {
                //         if (row.querySelector('.account-select')?.value === 'BIR') {
                //             row.remove();
                //         }
                //     });

                //     // Add rows only if there are tax amounts
                //     if (tax1Amount > 0 || tax2Amount > 0) {
                //         // Add first BIR row
                //         const row1 = document.createElement('tr');
                //         row1.innerHTML = `
                //             <td colspan="2">
                //                 <select class="form-control account-select" name="account_titles[]">
                //                     <option value="BIR" selected>Due to BIR - 2020101000</option>
                //                 </select>
                //             </td>
                //             <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01"></td>
                //             <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" value="${tax1Amount.toFixed(2)}" readonly></td>
                //         `;
                //         tableBody.appendChild(row1);

                //         // Add second BIR row
                //         const row2 = document.createElement('tr');
                //         row2.innerHTML = `
                //             <td colspan="2">
                //                 <select class="form-control account-select" name="account_titles[]">
                //                     <option value="BIR" selected>Due to BIR - 2020101000</option>
                //                 </select>
                //             </td>
                //             <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01"></td>
                //             <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" value="${tax2Amount.toFixed(2)}" readonly></td>
                //         `;
                //         tableBody.appendChild(row2);

                //         // Setup calculation listeners for new rows
                //         setupCalculationListeners(row1);
                //         setupCalculationListeners(row2);
                //     }
                // }

                // Event listeners
                amountInput.addEventListener("input", calculate);
                applyTaxesCheckbox.addEventListener("change", calculate);

                // Initial calculation - trigger calculation as soon as the page loads
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

                fetch("fetch_jev_no.php", {
                    method: "POST",
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Fetched DV Data:", data); // Debugging
                        let dvNoInput = document.getElementById("jev_no");

                        if (dvNoInput) {
                            if (data.success) {
                                dvNoInput.value = data.jev_no;
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

        <!-- show form after selecting ors type  -->
        <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            const orsTypeSelect = document.getElementById("ors_type");
            const orsForm = document.getElementById("dv_form");

            orsTypeSelect.addEventListener("change", function () {
                if (this.value) {
                    orsForm.style.display = "block";  // Show the form
                }
            });
        });
    </script> -->

        <!-- account_title -->
        <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            const orsTypeSelect = document.getElementById("ors_type");

            function filterAccountTitles() {
                const selectedType = orsTypeSelect.value;
                const accountSelects = document.querySelectorAll('.account-select');

                accountSelects.forEach(select => {
                    const currentValue = select.value;
                    const currentTitle = select.options[select.selectedIndex]?.getAttribute('data-title') || '';

                    Array.from(select.options).forEach(option => {
                        if (option.value === "") return;

                        const accountTitle = option.getAttribute('data-title')?.toLowerCase() || '';
                        const accountCode = option.getAttribute('data-uacs') || '';
                        if (selectedType === "cash_advance") {
                            option.hidden = !accountTitle.includes('advance');
                        } else if (selectedType === "transfer_fund") {
                            option.hidden = !(accountTitle.includes('cash') && accountCode.startsWith('10'));
                        } else {
                            option.hidden = false;
                        }
                    });

                    // Restore selection if it's still valid
                    if (currentValue && select.querySelector(`option[value="${currentValue}"]`)) {
                        select.value = currentValue;
                    }
                });
            }

            // Filter on initial load and when DV type changes
            orsTypeSelect.addEventListener("change", filterAccountTitles);

            // Also filter when new rows are added
            document.getElementById('addAccountRow').addEventListener('click', function () {
                setTimeout(filterAccountTitles, 0);
            });
        });
    </script> -->

        <!-- add row and calculate totals -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tableBody = document.getElementById('accountingTableBody');

                // Function to update UACS code when account is selected
                function setupAccountSelect(row) {
                    const accountSelect = row.querySelector('.account-select');
                    const uacsInput = row.querySelector('.uacs-code');

                    accountSelect.addEventListener('change', function () {
                        const selectedOption = this.options[this.selectedIndex];
                        uacsInput.value = selectedOption.getAttribute('data-uacs') || '';
                    });
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

                // Function to filter account titles
                function filterAccountTitles(select, selectedType) {
                    const currentValue = select.value;
                    Array.from(select.options).forEach(option => {
                        if (option.value === "") return; // Skip the "Select Account" option

                        const accountTitle = option.getAttribute('data-title')?.toLowerCase() || '';
                        const accountCode = option.getAttribute('data-uacs') || '';
                        if (selectedType === "cash_advance") {
                            option.hidden = !accountTitle.includes('advance');
                        } else if (selectedType === "transfer_fund") {
                            option.hidden = !(accountTitle.includes('cash') && accountCode.startsWith('10'));
                        } else {
                            option.hidden = false;
                        }
                    });

                    // Restore selection if it's still valid
                    if (currentValue && select.querySelector(`option[value="${currentValue}"]`)) {
                        select.value = currentValue;
                    }
                }

                // Add event listener for the "Add Row" button
                document.getElementById('addAccountRow').addEventListener('click', function () {
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                    <td colspan="2">
                        <select class="form-control account-select" name="account_titles[]">
                            <option selected disabled>Select Account</option>
                            <?php
                            $account_result->data_seek(0);
                            while ($account = $account_result->fetch_assoc()) {
                                echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01"></td>
                    <td><input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01"></td>
                `;

                    tableBody.appendChild(newRow);
                    setupAccountSelect(newRow);
                    setupCalculationListeners(newRow);

                    // Filter account titles for the new row
                    const orsTypeSelect = document.getElementById("ors_type");
                    const accountSelect = newRow.querySelector('.account-select');
                    filterAccountTitles(accountSelect, orsTypeSelect.value);
                });

                // Function to setup calculation listeners for a row
                function setupCalculationListeners(row) {
                    const debitInput = row.querySelector('.debit-amount');
                    const creditInput = row.querySelector('.credit-amount');

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
                }

                // Setup initial row
                const initialRow = tableBody.querySelector('tr');
                setupAccountSelect(initialRow);
                setupCalculationListeners(initialRow);

                // Add event listener for DV type changes
                document.getElementById('ors_type').addEventListener('change', function () {
                    const selectedType = this.value;
                    const accountSelects = document.querySelectorAll('.account-select');
                    accountSelects.forEach(select => {
                        filterAccountTitles(select, selectedType);
                    });
                });
            });
        </script>

</body>

</html>