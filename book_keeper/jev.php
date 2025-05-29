<?php
include '../DBConnection.php';

// insert

if (isset($_POST['submit'])) {
    $date = $_POST['date'];
    $dv_no = $_POST['dv_no'];
    $jev_no = $_POST['jev_no'];
    $administrative_aide = $_POST['administrative_aide'];
    $accountant = $_POST['accountant'];

    $connection->begin_transaction();

    try {
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

        // Get all ORS IDs from dv_multiple_ors table
        $ors_ids_query = "SELECT ors_id FROM dv_multiple_ors WHERE dv_id = ?";
        $ors_ids_stmt = $connection->prepare($ors_ids_query);
        if ($ors_ids_stmt === false) {
            throw new Exception('Prepare failed (ORS IDs): ' . htmlspecialchars($connection->error));
        }
        $ors_ids_stmt->bind_param("i", $dv_id);
        if (!$ors_ids_stmt->execute()) {
            throw new Exception("Error getting ORS IDs: " . $ors_ids_stmt->error);
        }
        $ors_ids_result = $ors_ids_stmt->get_result();
        $ors_ids = [];
        while ($row = $ors_ids_result->fetch_assoc()) {
            $ors_ids[] = $row['ors_id'];
        }
        $ors_ids_stmt->close();

        // If no ORS IDs found in dv_multiple_ors, try getting from dv table
        if (empty($ors_ids)) {
            $main_ors_query = "SELECT ors_id FROM dv WHERE dv_id = ?";
            $main_ors_stmt = $connection->prepare($main_ors_query);
            if ($main_ors_stmt === false) {
                throw new Exception('Prepare failed (Main ORS): ' . htmlspecialchars($connection->error));
            }
            $main_ors_stmt->bind_param("i", $dv_id);
            if (!$main_ors_stmt->execute()) {
                throw new Exception("Error getting main ORS ID: " . $main_ors_stmt->error);
            }
            $main_ors_result = $main_ors_stmt->get_result();
            if ($main_ors_result->num_rows === 0) {
                throw new Exception("No ORS ID found for DV: " . $dv_no);
            }
            $main_ors_row = $main_ors_result->fetch_assoc();
            $ors_ids[] = $main_ors_row['ors_id'];
            $main_ors_stmt->close();
        }

        // Use the first ORS ID as the main ORS ID for the JEV
        $ors_id = $ors_ids[0];

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

        $jev_id = $connection->insert_id;
        $stmt->close();

        // Link JEV with all ORS entries using jev_multiple_ors table
        foreach ($ors_ids as $ors_id) {
            $link_sql = "INSERT INTO jev_multiple_ors (jev_id, ors_id) VALUES (?, ?)";
            $link_stmt = $connection->prepare($link_sql);
            if ($link_stmt === false) {
                throw new Exception('Prepare failed (JEV-ORS link): ' . htmlspecialchars($connection->error));
            }

            $link_stmt->bind_param("ii", $jev_id, $ors_id);
            if (!$link_stmt->execute()) {
                throw new Exception("Error linking JEV with ORS: " . $link_stmt->error);
            }
            $link_stmt->close();

            // Update ORS status to 'Endorsed'
            $update_ors_sql = "UPDATE ors SET status = 'Endorsed' WHERE ors_id = ?";
            $update_ors_stmt = $connection->prepare($update_ors_sql);
            if ($update_ors_stmt === false) {
                throw new Exception('Prepare failed (ORS update): ' . htmlspecialchars($connection->error));
            }
            $update_ors_stmt->bind_param("i", $ors_id);
            if (!$update_ors_stmt->execute()) {
                throw new Exception("Error updating ORS status: " . $update_ors_stmt->error);
            }
            $update_ors_stmt->close();
        }

        // Update the dv status to 'Endorsed'
        $update_status_sql = "UPDATE dv SET status = 'Endorsed' WHERE dv_id = ?";
        $update_status_stmt = $connection->prepare($update_status_sql);
        if ($update_status_stmt === false) {
            throw new Exception('Prepare failed (DV update): ' . htmlspecialchars($connection->error));
        }

        $update_status_stmt->bind_param("i", $dv_id);
        if (!$update_status_stmt->execute()) {
            throw new Exception("Error updating DV status: " . $update_status_stmt->error);
        }
        $update_status_stmt->close();

        // Commit transaction
        $connection->commit();

        // Return success response
        header("Location: jev_form.php?jev_no=$jev_no");
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
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


ORDER BY dv.date DESC, dv_no DESC
;


");

$select = mysqli_query($connection, "SELECT * FROM dv_multiple_ors");

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Book Keeper - Journal Entry Voucher</title>
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

    <style>
        td:nth-child(3),
        th:nth-child(3) {
            display: none;
        }
    </style>
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex align-items-center">
            <h1 class="mb-0">Journal Entry Voucher</h1>
            <div class="ms-auto">
                <button class="btn btn-primary" onclick="window.location.href='processed_jev.php'"> <i
                        class="bi bi-list-check me-1"></i>
                    View Processed JEV
                </button>
            </div>
        </div>



        <div class="content-wrapper">
            <div class="form-container">
                <h2></h2>

                <div class="tab-content">
                    <div>
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
                                                    <i class="bi bi-file-earmark-plus"></i> Create JEV
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
    </main>

    <!-- JEV Modal -->
    <div id="dvFormModal" class="modal fade" tabindex="-1" aria-labelledby="jevModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jevModalLabel">Journal Entry Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form Template -->
                    <div id="jevFormTemplate" style="display: none;">
                        <form action="jev.php" method="post" id="jevForm">
                            <input type="hidden" name="submit" value="1">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="fund_cluster" class="form-label">Fund Cluster</label>
                                    <input type="text" class="form-control" id="fund_cluster" name="fund_cluster"
                                        readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="jev_no" class="form-label">JEV No.</label>
                                    <input type="text" class="form-control" name="jev_no" id="jev_no" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ors_no" class="form-label">ORS No.</label>
                                    <input type="text" class="form-control" id="ors_no" name="ors_no" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="dv_no" class="form-label">DV No.</label>
                                    <input type="text" class="form-control" id="dv_no" name="dv_no" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="ors_notes" class="form-label">ORS Notes</label>
                                    <textarea class="form-control" id="ors_notes" name="ors_notes" rows="3"
                                        readonly></textarea>
                                </div>
                            </div>


                            <!-- Payee Details Section -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-3">Payee Details</h5>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="payee_name" class="form-label">Payee Name</label>
                                        <input type="text" class="form-control" id="payee_name" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Accounting Entries Table -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered" id="accountsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account Name</th>
                                            <th>UACS Object Code</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table content will be dynamically populated -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <td colspan="2" class="text-end fw-bold">TOTAL</td>
                                            <td class="text-end fw-bold" id="totalDebit">0.00</td>
                                            <td class="text-end fw-bold" id="totalCredit">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="administrative_aide" class="form-label">Administrative Aide</label>
                                    <select class="form-select" name="administrative_aide" id="administrative_aide"
                                        required>
                                        <option value="JINNARD B. LUBATON">JINNARD B. LUBATON</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="accountant" class="form-label">Accountant III</label>
                                    <select class="form-select" name="accountant" id="accountant" required>
                                        <option value="NEIL ANTHONY T. MORALA">NEIL ANTHONY T. MORALA</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="submit" form="jevForm">Submit</button>
                </div>
            </div>
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
    <script src="../NiceAdmin/assets/vendor/quill/quill.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <!-- Custom Script for Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize all tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize the modal
            var modalEl = document.getElementById('dvFormModal');
            if (!modalEl) {
                console.error('Modal element not found');
                return;
            }

            var modal = new bootstrap.Modal(modalEl);
            const formTemplate = document.getElementById('jevFormTemplate');

            // Add click event listeners to all view-details buttons
            document.querySelectorAll('.view-details').forEach(function (button) {
                button.addEventListener('click', function () {
                    const dvId = this.getAttribute('data-id');
                    if (!dvId) {
                        console.error('No DV ID found');
                        return;
                    }

                    // Show loading state
                    const modalBody = modalEl.querySelector('.modal-body');
                    if (!modalBody) {
                        console.error('Modal body not found');
                        return;
                    }

                    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

                    // Show the modal
                    modal.show();

                    // Fetch the data
                    fetch(`get_jev_details.php?id=${dvId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                throw new Error(data.error);
                            }

                            // Restore modal content using the template
                            modalBody.innerHTML = formTemplate.innerHTML;

                            // Populate form fields
                            const form = document.getElementById('jevForm');
                            if (!form) {
                                throw new Error('Form not found after template restoration');
                            }

                            form.querySelector('#ors_no').value = data.ors_details
                                ? data.ors_details.map(ors => ors.ors_no).join(', ')
                                : '';
                            const notesField = form.querySelector('#ors_notes');
                            if (notesField) {
                                notesField.value = data.ors_details
                                    ? data.ors_details
                                        .filter(ors => ors.notes && ors.notes.trim() !== '')
                                        .map(ors => `${ors.ors_no}: ${ors.notes}`)
                                        .join('\n')
                                    : '';
                            }
                            form.querySelector('#dv_no').value = data.dv_no || '';
                            form.querySelector('#payee_name').value = data.payee_name || '';
                            form.querySelector('#fund_cluster').value = data.fund_cluster || '';

                            // Set current date
                            const today = new Date().toISOString().split('T')[0];
                            form.querySelector('#date').value = today;

                            // Populate accounts table
                            const tableBody = form.querySelector('#accountsTable tbody');
                            const totalDebitElement = form.querySelector('#totalDebit');
                            const totalCreditElement = form.querySelector('#totalCredit');

                            if (tableBody && totalDebitElement && totalCreditElement) {
                                tableBody.innerHTML = '';

                                let totalDebit = 0;
                                let totalCredit = 0;

                                if (data.accounts && data.accounts.length > 0) {
                                    data.accounts.forEach(account => {
                                        const row = document.createElement('tr');
                                        const amount = parseFloat(account.amount) || 0;

                                        const isDebit = account.type === 'debit' || account.type === 'asset' || account.type === 'expense';
                                        const debitAmount = isDebit ? amount : 0;
                                        const creditAmount = !isDebit ? amount : 0;

                                        totalDebit += debitAmount;
                                        totalCredit += creditAmount;

                                        row.innerHTML = `
                                            <td>${account.account_title || ''}</td>
                                            <td>${account.account_code || ''}</td>
                                            <td class="text-end">${debitAmount.toFixed(2)}</td>
                                            <td class="text-end">${creditAmount.toFixed(2)}</td>
                                        `;
                                        tableBody.appendChild(row);
                                    });
                                }

                                // Update totals
                                totalDebitElement.textContent = totalDebit.toFixed(2);
                                totalCreditElement.textContent = totalCredit.toFixed(2);
                            }

                            // Generate JEV number
                            generateJEVNumber();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error.message}</div>`;
                        });
                });
            });

            // Form submission handler
            const form = document.getElementById('jevForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    // Show loading state
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        const originalButtonText = submitButton.innerHTML;
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
                    }

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.error || 'An error occurred while submitting the form');
                            }

                            // Close the modal first
                            modal.hide();

                            // Redirect immediately to jev_form.php
                            window.location.replace(`jev_form.php?jev_no=${data.jev_no}`);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Restore button state
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.innerHTML = originalButtonText;
                            }

                            // Show error message
                            const modalBody = modalEl.querySelector('.modal-body');
                            if (modalBody) {
                                modalBody.innerHTML = `
                                <div class="alert alert-danger">
                                    <h4 class="alert-heading">Error!</h4>
                                    <p>${error.message}</p>
                                    <hr>
                                    <p class="mb-0">Please try again.</p>
                                </div>
                            `;
                            }
                        });
                });
            }
        });

        // Function to generate JEV number
        function generateJEVNumber() {
            const dateInput = document.getElementById("date");
            const fundClusterInput = document.getElementById("fund_cluster");

            if (!dateInput || !dateInput.value || !fundClusterInput || !fundClusterInput.value) {
                console.log('Missing required fields for JEV number generation');
                return;
            }

            const date = new Date(dateInput.value);
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');

            // Extract fund cluster number
            const fundClusterValue = fundClusterInput.value.trim();
            const fundClusterNumber = fundClusterValue.match(/^\d+/);

            if (!fundClusterNumber) {
                console.log('Invalid fund cluster format');
                return;
            }

            const formData = new FormData();
            formData.append("fund_cluster_id", fundClusterNumber[0]);
            formData.append("date", dateInput.value);

            fetch('fetch_jev_no.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.jev_no) {
                        const jevNoInput = document.getElementById('jev_no');
                        if (jevNoInput) {
                            jevNoInput.value = data.jev_no;
                        }
                    } else {
                        console.error('Error fetching JEV number:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
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