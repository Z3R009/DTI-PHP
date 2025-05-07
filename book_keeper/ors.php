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
$sql_oopap = "SELECT oopap_id, oopap_name, description	 FROM oopap";
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
    <link rel="stylesheet" href="css/ors.css">
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Obligation Request and Status</h1>
            <button class="btn btn-primary" onclick="window.location.href='processed_ors.php'">
                <i class="bi bi-list-check"></i> View Processed ORS
            </button>

        </div><!-- End Page Title -->



        <div class="content-wrapper">
            <div class="form-container">
                <h2 class="form-title">Obligation Request And Status</h2>

                <!-- General Information Section -->
                <div class="tab-content">
                    <div>
                        <div id="ors_form">
                            <form method="post" id="orsForm">
                                <div class="form-section">
                                    <h3><i class="bi bi-info-circle me-2"></i>General Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Fund Cluster</label>
                                            <select class="form-control" name="fund_cluster_id" required>
                                                <option selected disabled value="">Select Fund Cluster</option>
                                                <?php
                                                while ($row = $result_fund_cluster->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['fund_cluster_id']) . "'>" . htmlspecialchars($row['fund_cluster_name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">OO/PAP</label>
                                            <select class="form-control" name="oopap_id" required>
                                                <option selected disabled value="">Select OO/PAP</option>
                                                <?php
                                                while ($row = $result_oopap->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['oopap_id']) . "'>" . htmlspecialchars($row['oopap_name']) . " - " . htmlspecialchars($row['description']) . "</option>";
                                                }
                                                ?>

                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Services</label>
                                            <select class="form-control" name="services_id" id="services" required>
                                                <option selected disabled value="">Select Services</option>
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
                                    <h3><i class="bi bi-person me-2"></i>Payee Details</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Payee Name</label>
                                            <select class="form-control" name="payee_id" id="payee_id" required>
                                                <option selected disabled value="">Select Payee</option>
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
                                            <input type="text" class="form-control" name="tin_no" id="tin_no"
                                                autocomplete="off">

                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" id="address"
                                            autocomplete="off">

                                    </div>
                                </div>

                                <!-- Payment Details Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-cash-coin me-2"></i>Payment Details</h3>

                                    <label class="form-label">Purpose</label>
                                    <div class="form-row">
                                        <select class="form-control" name="purpose">
                                            <option value="To Payment of">To Payment of</option>
                                            <option value="To Disburse">To Reimburse</option>
                                            <option value="To Cash Advance">To Cash Advance</option>
                                            <option value="To Transfer">To Transfer</option>

                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group full-width">

                                            <textarea class="form-control" name="notes" placeholder="Enter Purpose"
                                                required></textarea autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="form-row">
                                            <div class="form-group">
                                                    <label class="form-label">Responsibility Center</label>
                                                    <select class="form-control" name="rc_id" required>
                                                        <option selected disabled value="">Select Responsibility Center</option>
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
                                    <h3><i class="bi bi-journal-text me-2"></i>Particulars</h3>
                                            <div class="table-responsive">
                                               <!-- HTML Table Structure -->
<table class="accounting-entry-table">
    <thead>
        <tr>
            <th colspan="2">Account Title</th>
            <th>Code</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="accounting-table-body">
        <!-- First row -->
        <tr class="entry-row">
            <td colspan="2">
            <select class="form-control" name="account_id[]" required>
                                                                    <option selected disabled>Select Account</option>
                                                                    <?php
                                                                    // Reset the result pointer
                                                                    $result_account->data_seek(0);
                                                                    while ($row = $result_account->fetch_assoc()) {
                                                                        echo "<option value='" . htmlspecialchars($row['account_id']) . "' 
                              data-code='" . htmlspecialchars($row['account_code']) . "' 
                              data-oopap='" . htmlspecialchars($row['oopap_id']) . "'>"
                                                                            . htmlspecialchars($row['account_title']) . "</option>";
                                                                    }
                                                                    ?>
                </select>
            </td>
            <td class="account-code"></td>
            <td>
                <input type="number" step="0.01" class="form-control amount-input" name="amount[]" required>
            </td>
          
          
        </tr>
        <!-- Total row will be added dynamically -->
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="3" class="text-end fw-bold">Total Amount</td>
            <td>
                <input type="number" step="0.01" class="form-control calculation-field" id="total_amount" name="total_amount" readonly>
            </td>
            <td></td>
        </tr>
    </tfoot>
</table>
<button type="button" id="add-entry-button" class="btn btn-sm">
    <i class="bi bi-plus-circle"></i> Add Entry
</button>


                                            </div>
                                        </div>

                                        <input type="hidden" class="form-control" id="project_id" name="project_id" readonly placeholder="Project ID">


                                     

                                        </div>

                                <!-- Submit Button Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-check-circle me-2"></i>Confirmation</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Approver</label>
                                            <select class="form-control" name="approver_id" id="approver" required>
                                                <option selected disabled value="">Select Approver</option>
                                                <?php
                                                // Reset the result pointer
                                                $result_approvers->data_seek(0);
                                                while ($row = $result_approvers->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['approver_id']) . "'>"
                                                        . htmlspecialchars($row['approver_name']) . " - " . htmlspecialchars($row['designation']) .
                                                        "</option>";
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
                                    <div class="btn-container">
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                        </button>
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Submit
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" name="submit" id="realSubmitBtn" style="display: none;"></button>

                                    </form>
                        </div>
                    </div>
                                </div>
                            </div>
                        </div>
    </main><!-- End #main -->



    <!-- confirmation modal -->
    <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmSubmitLabel">Confirm Submission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to submit this form?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmBtn">Yes, Submit</button>
      </div>
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

    <!-- Custom Accounting Entry JS -->
    <script src="js/accounting-entry.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize form elements
            const dateInput = document.getElementById('dvDate');
            const orsNoInput = document.getElementById('ors_no');

            // Set default date to today if empty
            if (!dateInput.value) {
                const today = new Date();
                dateInput.value = today.toISOString().split('T')[0];
            }

            // Generate ORS Number based on date
            function generateORSNumber(dateStr) {
                if (!dateStr) return '';

                const date = new Date(dateStr);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');

                // Format: ORS-YEAR-MONTH-SEQUENCE
                return `ORS-${year}-${month}-<?php echo $new_sequence; ?>`;
            }

            // Initialize ORS number
            orsNoInput.value = generateORSNumber(dateInput.value);

            // Update ORS number when date changes
            dateInput.addEventListener('change', function () {
                orsNoInput.value = generateORSNumber(this.value);
            });

            // Fill in TIN and Address when Payee is selected
            const payeeSelect = document.getElementById('payee_id');
            const tinInput = document.getElementById('tin_no');
            const addressInput = document.getElementById('address');

            payeeSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                tinInput.value = selectedOption.getAttribute('data-tin');
                addressInput.value = selectedOption.getAttribute('data-address');

                // Add animation effect
                tinInput.classList.add('highlight-effect');
                addressInput.classList.add('highlight-effect');

                // Remove effect after animation completes
                setTimeout(() => {
                    tinInput.classList.remove('highlight-effect');
                    addressInput.classList.remove('highlight-effect');
                }, 1000);
            });

            // Handle dynamic accounting entries
            const tableBody = document.getElementById('accounting-table-body');
            const totalAmountInput = document.getElementById('total_amount');
            const addEntryButton = document.getElementById('add-entry-button');

            // Add a new entry row with animation
            addEntryButton.addEventListener('click', function () {
                const newRow = document.createElement('tr');
                newRow.className = 'entry-row';
                newRow.style.opacity = '0';
                newRow.style.transform = 'translateY(10px)';
                newRow.innerHTML = `
                    <td colspan="2">
                        <select class="form-control" name="account_id[]" required>
                            <option selected disabled>Select Account</option>
                            ${Array.from(document.querySelector('select[name="account_id[]"]').options)
                        .map(opt => opt.outerHTML)
                        .join('')}
                        </select>
                    </td>
                    <td class="account-code"></td>
                    <td>
                        <input type="number" step="0.01" class="form-control amount-input" name="amount[]" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-entry">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(newRow);

                // Animate row appearance
                setTimeout(() => {
                    newRow.style.transition = 'all 0.3s ease';
                    newRow.style.opacity = '1';
                    newRow.style.transform = 'translateY(0)';
                }, 10);

                // Add event listeners to the new row
                initializeRowEvents(newRow);

                // Filter accounts based on selected OOPAP
                filterAccountsByOOPAP();
            });

            // Initialize event listeners for existing rows
            function initializeRowEvents(row) {
                const accountSelect = row.querySelector('select[name="account_id[]"]');
                const accountCodeCell = row.querySelector('.account-code');
                const amountInput = row.querySelector('.amount-input');
                const removeButton = row.querySelector('.remove-entry');

                // Update account code when account changes
                accountSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value !== '') {
                        accountCodeCell.textContent = selectedOption.getAttribute('data-code');
                        // Highlight code cell with animation
                        accountCodeCell.classList.add('highlight-effect');
                        setTimeout(() => {
                            accountCodeCell.classList.remove('highlight-effect');
                        }, 1000);
                    } else {
                        accountCodeCell.textContent = '';
                    }

                    // Filter accounts based on selected OOPAP
                    filterAccountsByOOPAP();
                });

                // Remove row when delete button is clicked
                removeButton.addEventListener('click', function () {
                    if (tableBody.querySelectorAll('.entry-row').length > 1) {
                        // Animate removal
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateY(-10px)';

                        setTimeout(() => {
                            row.remove();
                            calculateTotal();
                        }, 300);
                    } else {
                        // Show error with animation
                        row.classList.add('shake-effect');
                        setTimeout(() => {
                            row.classList.remove('shake-effect');
                        }, 500);

                        // Show error message with tooltip
                        const tooltip = document.createElement('div');
                        tooltip.className = 'error-tooltip';
                        tooltip.textContent = 'At least one entry is required';
                        row.appendChild(tooltip);

                        setTimeout(() => {
                            tooltip.remove();
                        }, 3000);
                    }
                });

                // Calculate total when amount changes
                amountInput.addEventListener('input', function () {
                    calculateTotal();

                    // Highlight total amount with animation
                    totalAmountInput.classList.add('highlight-effect');
                    setTimeout(() => {
                        totalAmountInput.classList.remove('highlight-effect');
                    }, 1000);
                });
            }

            // Calculate total amount with currency formatting
            function calculateTotal() {
                const amountInputs = document.querySelectorAll('.amount-input');
                let total = 0;

                amountInputs.forEach(input => {
                    const value = parseFloat(input.value) || 0;
                    total += value;
                });

                totalAmountInput.value = total.toFixed(2);

                // Update total display with currency formatting
                const formattedTotal = new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP'
                }).format(total);

                // If there's a total display element, update it
                const totalDisplay = document.querySelector('.total-display');
                if (totalDisplay) {
                    totalDisplay.textContent = formattedTotal;
                }
            }

            // Filter accounts based on selected OOPAP
            function filterAccountsByOOPAP() {
                const oopapSelect = document.querySelector('select[name="oopap_id"]');
                if (!oopapSelect) return;

                const selectedOopapId = oopapSelect.value;
                if (!selectedOopapId || selectedOopapId === '') return;

                // Update account dropdowns
                const accountSelects = document.querySelectorAll('select[name="account_id[]"]');

                accountSelects.forEach(select => {
                    const options = select.querySelectorAll('option');

                    options.forEach(option => {
                        if (option.value === "") return;

                        const optionOopapId = option.getAttribute('data-oopap');
                        if (optionOopapId === selectedOopapId) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                });
            }

            // Handle OOPAP change
            const oopapSelect = document.querySelector('select[name="oopap_id"]');
            if (oopapSelect) {
                oopapSelect.addEventListener('change', filterAccountsByOOPAP);
            }

            // Initialize events for the first row
            const firstRow = tableBody.querySelector('.entry-row');
            if (firstRow) {
                initializeRowEvents(firstRow);
            }

            // Initialize Dropdown Services based on selected Fund Cluster and OOPAP
            const fundClusterSelect = document.querySelector('select[name="fund_cluster_id"]');
            const servicesSelect = document.getElementById('services');

            // Load services on page load
            loadServices();

            // Update services when fund cluster or OOPAP changes
            fundClusterSelect.addEventListener('change', loadServices);
            if (oopapSelect) {
                oopapSelect.addEventListener('change', loadServices);
            }

            function loadServices() {
                const fundClusterId = fundClusterSelect.value;
                const oopapId = oopapSelect ? oopapSelect.value : '';

                // Clear current options
                servicesSelect.innerHTML = '<option selected disabled>Select Services</option>';

                // Only continue if both values are selected
                if (!fundClusterId || !oopapId) return;

                // Add loading indicator
                servicesSelect.innerHTML = '<option>Loading services...</option>';

                // Fetch services from the server
                fetch(`get_services.php?fund_cluster_id=${fundClusterId}&oopap_id=${oopapId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Clear loading indicator
                        servicesSelect.innerHTML = '<option selected disabled>Select Services</option>';

                        if (data.length === 0) {
                            const option = document.createElement('option');
                            option.textContent = 'No services available';
                            option.disabled = true;
                            servicesSelect.appendChild(option);
                        } else {
                            data.forEach(service => {
                                const option = document.createElement('option');
                                option.value = service.services_id;
                                option.textContent = service.services_name + ' - ' + service.code;
                                servicesSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading services:', error);
                        servicesSelect.innerHTML = '<option disabled selected>Error loading services</option>';
                    });
            }

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function (e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');

                        // Create error message
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'This field is required';

                        // Only add if it doesn't exist already
                        if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                            field.parentNode.insertBefore(errorDiv, field.nextElementSibling);
                        }
                    } else {
                        field.classList.remove('is-invalid');
                        // Remove error message if exists
                        if (field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')) {
                            field.nextElementSibling.remove();
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    // Scroll to first error
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            // Clear invalid status when field changes
            form.querySelectorAll('[required]').forEach(field => {
                field.addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                    // Remove error message if exists
                    if (this.nextElementSibling && this.nextElementSibling.classList.contains('invalid-feedback')) {
                        this.nextElementSibling.remove();
                    }
                });
            });
        });
    </script>

    <!-- approver -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const approverSelect = document.getElementById("approver");

            if (approverSelect) {
                approverSelect.addEventListener("change", function () {
                    const selectedOption = approverSelect.options[approverSelect.selectedIndex];
                    const designation = selectedOption.textContent.split(' - ')[1] || '';

                    // You can display the designation somewhere if needed
                    console.log('Selected designation:', designation);
                });
            }
        });
    </script>

    <!-- dv_number -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fundClusterSelect = document.getElementById("fund_cluster_id");
            const dvDateInput = document.getElementById("dvDate");
            const orsNumberInput = document.getElementById("ors_no");

            function generateorsNumber() {
                const selectedUACS = fundClusterSelect.value;
                const selectedDate = dvDateInput.value;

                if (!selectedUACS || !selectedDate) {
                    orsNumberInput.value = "";
                    return;
                }

                const dateObj = new Date(selectedDate);
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');

                const lastSequence = "<?php echo $new_sequence; ?>";

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

            // Create warning message element
            const warningMessage = document.createElement('div');
            warningMessage.className = 'text-warning mt-1';
            warningMessage.style.display = 'none';
            warningMessage.innerHTML = '<small><i class="bi bi-exclamation-triangle"></i> Warning: This will result in a negative balance!</small>';
            amountInput.parentNode.appendChild(warningMessage);

            async function checkAllotment() {
                const accountId = accountSelect.value;
                const oopapId = oopapSelect.value;
                const amount = parseFloat(amountInput.value) || 0;

                if (!accountId || !oopapId || amount === 0) {
                    projectIdInput.value = '';
                    warningMessage.style.display = 'none';
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
                        warningMessage.style.display = 'none';
                    } else {
                        projectIdInput.value = data.project_id;
                        projectIdInput.style.backgroundColor = '#fff3e0';
                        warningMessage.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error checking allotment:', error);
                    projectIdInput.value = '';
                    projectIdInput.style.backgroundColor = '#ffebee';
                    warningMessage.style.display = 'none';
                }
            }

            accountSelect.addEventListener('change', checkAllotment);
            oopapSelect.addEventListener('change', checkAllotment);
            amountInput.addEventListener('input', checkAllotment);
        });
    </script>

    <!-- services -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const oopapSelect = document.querySelector('select[name="oopap_id"]');
            const servicesSelect = document.getElementById('services');
            const dateInput = document.getElementById('dvDate');
            const orsNoInput = document.getElementById('ors_no');

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

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const oopapSelect = document.querySelector('select[name="oopap_id"]');
            const accountSelects = document.querySelectorAll('select[name="account_id[]"]');

            function updateAccountOptions() {
                const selectedOopapId = oopapSelect.value;

                accountSelects.forEach(select => {
                    const currentValue = select.value;
                    const options = select.options;
                    for (let i = 0; i < options.length; i++) {
                        const option = options[i];
                        if (option.value === "") continue;

                        const optionOopapId = option.getAttribute('data-oopap');
                        if (optionOopapId === selectedOopapId) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    }

                    if (currentValue && options[select.selectedIndex].style.display === 'none') {
                        select.value = "";
                    }
                });
            }
            oopapSelect.addEventListener('change', updateAccountOptions);
            updateAccountOptions();
        });
    </script>
<script>
    document.getElementById('yearFilter').addEventListener('change', applyFilter);
    document.getElementById('monthFilter').addEventListener('change', applyFilter);
    document.getElementById('servicesFilter').addEventListener('change', applyFilter);

    function applyFilter() {
        var year = document.getElementById('yearFilter').value;
        var month = document.getElementById('monthFilter').value;
        var service = document.getElementById('servicesFilter').value;
        var newUrl = window.location.origin + window.location.pathname + '?year=' + year + '&month=' + month + '&service=' + service;
        window.location.href = newUrl + '#orsList';
    }
</script>

    <!-- Custom Searchable Dropdown Implementation -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const dropdownContainers = [];

            function convertToSearchableDropdown(selectElement) {
                const dropdownContainer = document.createElement('div');
                dropdownContainer.className = 'custom-dropdown';

                const dropdownToggle = document.createElement('div');
                dropdownToggle.className = 'dropdown-toggle';
                dropdownToggle.textContent = selectElement.options[selectElement.selectedIndex]?.text || 'Select Account';

                const dropdownMenu = document.createElement('div');
                dropdownMenu.className = 'dropdown-menu';

                const searchBox = document.createElement('div');
                searchBox.className = 'search-box';
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.placeholder = 'Search...';
                searchBox.appendChild(searchInput);
                dropdownMenu.appendChild(searchBox);

                const dropdownItems = document.createElement('div');
                dropdownItems.className = 'dropdown-items';

                Array.from(selectElement.options).forEach(option => {
                    if (option.value === '') return;
                    const dropdownItem = document.createElement('div');
                    dropdownItem.className = 'dropdown-item';
                    dropdownItem.dataset.value = option.value;
                    dropdownItem.dataset.oopapId = option.getAttribute('data-oopap');
                    dropdownItem.dataset.accountCode = option.getAttribute('data-account_code');

                    // Include account code in the display text
                    const accountCode = option.getAttribute('data-account_code') || '';
                    const displayText = accountCode ? `${option.text} (${accountCode})` : option.text;
                    dropdownItem.textContent = displayText;

                    dropdownItem.addEventListener('click', function () {
                        selectElement.value = this.dataset.value;
                        dropdownToggle.textContent = displayText;
                        dropdownMenu.classList.remove('show');

                        dropdownItems.querySelectorAll('.dropdown-item').forEach(item => {
                            item.classList.remove('selected');
                        });
                        this.classList.add('selected');

                        // Update the account code input
                        const row = selectElement.closest('tr');
                        const codeInput = row.querySelector('.account-code');
                        if (codeInput && this.dataset.accountCode) {
                            codeInput.value = this.dataset.accountCode;
                        }

                        const event = new Event('change', { bubbles: true });
                        selectElement.dispatchEvent(event);
                    });

                    dropdownItems.appendChild(dropdownItem);
                });

                dropdownMenu.appendChild(dropdownItems);
                dropdownToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                    if (dropdownMenu.classList.contains('show')) {
                        searchInput.focus();
                    }
                });

                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();
                    dropdownItems.querySelectorAll('.dropdown-item').forEach(item => {
                        const text = item.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });

                document.addEventListener('click', function (e) {
                    if (!dropdownContainer.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                selectElement.style.display = 'none';
                dropdownContainer.appendChild(dropdownToggle);
                dropdownContainer.appendChild(dropdownMenu);
                selectElement.parentNode.insertBefore(dropdownContainer, selectElement);

                return dropdownContainer;
            }

            const accountSelects = document.querySelectorAll('select[name="account_id[]"]');

            accountSelects.forEach(select => {
                if (!select.classList.contains('custom-dropdown-processed')) {
                    select.classList.add('custom-dropdown-processed');
                    const container = convertToSearchableDropdown(select);
                    dropdownContainers.push(container);
                }
            });

            const oopapSelect = document.querySelector('select[name="oopap_id"]');

            function updateAccountOptions() {
                const selectedOopapId = oopapSelect.value;

                dropdownContainers.forEach(container => {
                    const dropdownItems = container.querySelectorAll('.dropdown-item');
                    const selectElement = container.nextElementSibling;

                    dropdownItems.forEach(item => {
                        const itemOopapId = item.dataset.oopapId;
                        if (selectedOopapId && itemOopapId !== selectedOopapId) {
                            item.style.display = 'none';
                        } else {
                            item.style.display = '';
                        }
                    });

                    if (selectedOopapId) {
                        const selectedItem = container.querySelector(`.dropdown-item[data-value="${selectElement.value}"]`);
                        if (selectedItem && selectedItem.dataset.oopapId !== selectedOopapId) {
                            selectElement.value = '';
                            container.querySelector('.dropdown-toggle').textContent = 'Select Account';
                        }
                    }
                });
            }

            if (oopapSelect) {
                oopapSelect.addEventListener('change', updateAccountOptions);
                updateAccountOptions();
            }

            // Make the convertToSearchableDropdown function available globally
            window.convertToSearchableDropdown = convertToSearchableDropdown;
            window.dropdownContainers = dropdownContainers;
            window.updateAccountOptions = updateAccountOptions;
        });
    </script>


<!-- confirmation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('orsForm');
            const submitButton = document.querySelector('button[type="submit"][name="submit"]');
            const realSubmitBtn = document.getElementById('realSubmitBtn');
            const confirmBtn = document.getElementById('confirmBtn');

            submitButton.addEventListener('click', function (e) {
                e.preventDefault(); // Stop immediate form submission
                const modal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));
                modal.show(); // Show confirmation modal
            });

            confirmBtn.addEventListener('click', function () {
                // Submit form if user confirms
                realSubmitBtn.click();
            });
        });
    </script>


</body>

</html>