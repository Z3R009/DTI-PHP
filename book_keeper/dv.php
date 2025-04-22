<?php
include '../DBConnection.php';

// insert

if (isset($_POST['submit'])) {
    echo "Form submitted!";

    // Debugging: Print all POST data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $date = $_POST['date'];
    $dv_no = $_POST['dv_no'];
    $ors_no = $_POST['ors_id']; // This is actually the ORS number
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
    $total_amount = $_POST['total_amount'];

    // Get the account titles and amounts arrays
    $account_titles = $_POST['account_titles'];
    $debit_amounts = $_POST['debit_amounts'];
    $credit_amounts = $_POST['credit_amounts'];

    // Start a transaction
    $connection->begin_transaction();

    try {
        // First, get the ors_id and account_id from the ors_no
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

        // Get a valid account_id from account_name table (using ID 1 as default - you can change this)
        $account_id = 1; // Using account ID 1 (DTI RO XI) as default

        // If you need to check if account_id exists
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

        // Insert the main DV record
        $sql = "INSERT INTO dv (date, dv_no, ors_id, account_id, vat, vat_amount, tax_base, tax_1, tax_1_amount, tax_2, tax_2_amount, net_amount, chief_accountant, regional_director, total_amount) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($connection->error));
        }

        $stmt->bind_param(
            "ssiiddddddddssd",
            $date,
            $dv_no,
            $ors_id,
            $account_id,
            $vat,
            $vat_amount,
            $tax_base,
            $tax_1,
            $tax_1_amount,
            $tax_2,
            $tax_2_amount,
            $net_amount,
            $chief_accountant,
            $regional_director,
            $total_amount
        );

        if (!$stmt->execute()) {
            throw new Exception("Error: " . $stmt->error);
        }

        $dv_id = $connection->insert_id;
        $stmt->close();

        // Update the ORS status to 'Processed'
        $update_status_sql = "UPDATE ors SET status = 'Endorsed' WHERE ors_id = ?";
        $update_status_stmt = $connection->prepare($update_status_sql);
        if ($update_status_stmt === false) {
            throw new Exception('Prepare failed (ORS update): ' . htmlspecialchars($connection->error));
        }

        $update_status_stmt->bind_param("i", $ors_id);
        if (!$update_status_stmt->execute()) {
            throw new Exception("Error updating ORS status: " . $update_status_stmt->error);
        }
        $update_status_stmt->close();


        // Loop through each account and save it in dv_history
        for ($i = 0; $i < count($account_titles); $i++) {
            if (empty($account_titles[$i]))
                continue; // Skip empty account selections

            $account_id = $account_titles[$i];
            $debit = !empty($debit_amounts[$i]) ? $debit_amounts[$i] : 0;
            $credit = !empty($credit_amounts[$i]) ? $credit_amounts[$i] : 0;

            // Determine the type (debit or credit)
            $type = ($debit > 0) ? 'debit' : 'credit';
            $amount = ($debit > 0) ? $debit : $credit;

            // Skip if amount is zero
            if ($amount == 0)
                continue;

            // Insert into dv_history
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

        // Commit the transaction
        $connection->commit();

        // Redirect after successful save
        header("Location: dv_form.php?dv_no=$dv_no");
        exit();

    } catch (Exception $e) {
        // Rollback the transaction on error
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }

    // $connection->close(); // Remove this line from the try-catch block
}

// retrieve ors
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

    WHERE ors.status = 'Pending';
");

// retrieve_dv
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
    payee.address
FROM dv
LEFT JOIN ors ON dv.ors_id = ors.ors_id
LEFT JOIN account_title ON ors.account_id = account_title.account_id
LEFT JOIN approver ON ors.approver_id = approver.approver_id
LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
LEFT JOIN payee ON ors.payee_id = payee.payee_id;


");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Disbursement Voucher - DTI Book Keeper</title>
    <meta content="Disbursement Voucher Management System for DTI" name="description">
    <meta content="disbursement, voucher, dti, finance, accounting" name="keywords">

    <!-- Favicons -->
    <link href="img/dti_logo.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i|Roboto+Mono:400,500,600,700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <!-- Template Main CSS File -->
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">

    <link rel="stylesheet" href="css/dv.css">
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex align-items-center">
            <h1 class="mb-0">Disbursement Voucher</h1>

            <!-- Buttons Container with right alignment -->
            <div class="ms-auto">
                <button class="btn btn-primary" onclick="window.location.href='processed_dv.php'">
                    <i class="bi bi-list-check me-1"></i> View Processed DV
                </button>
                <button class="btn btn-primary" onclick="window.location.href='dv_w-out.php'">
                    <i class="bi bi-file-earmark-plus me-1"></i> DV Form without ORS
                </button>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="form-container">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="form-title">Pending Disbursement Vouchers</h2>
                        <p class="text-muted text-center">Create disbursement vouchers from the available ORS documents
                            below</p>
                    </div>
                </div>

                <div class="tab-content">
                    <!-- DV List Tab -->
                    <div>
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-0">
                                <!-- Table with enhanced styling -->
                                <table class="enhanced-table datatable">
                                    <thead>
                                        <tr>
                                            <th>ORS No.</th>
                                            <th>Date</th>
                                            <th>Payee Name</th>
                                            <th>Account Title</th>
                                            <th class="amount-column">Amount</th>
                                            <th>Approver</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($select_ors) > 0) {
                                            while ($row = mysqli_fetch_assoc($select_ors)) {
                                                ?>
                                                <tr>
                                                    <td data-label="ORS No."><?php echo htmlspecialchars($row['ors_no']); ?>
                                                    </td>
                                                    <td data-label="Date">
                                                        <?php
                                                        $date = new DateTime($row['date']);
                                                        echo htmlspecialchars($date->format('F j, Y'));
                                                        ?>
                                                    </td>
                                                    <td data-label="Payee Name">
                                                        <?php echo htmlspecialchars($row['payee_name']); ?>
                                                    </td>
                                                    <td data-label="Account Title">
                                                        <?php echo htmlspecialchars($row['account_title']); ?>
                                                    </td>
                                                    <td data-label="Amount" class="amount-column">
                                                        ₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                                    <td data-label="Approver">
                                                        <?php echo htmlspecialchars($row['approver_name']); ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn-create-dv view-details"
                                                            data-id="<?php echo $row['ors_id']; ?>">
                                                            <i class="bi bi-file-earmark-plus"></i> Create DV
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="7" class="enhanced-table-empty">
                                                    <i class="bi bi-inbox"></i>
                                                    <p>No pending ORS records found</p>
                                                    <small>All available ORS documents have been processed</small>
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

    <!-- Modal for DV Form -->
    <div id="dvFormModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Disbursement Voucher</h2>
                <span class="close-modal" id="closeDvModal">&times;</span>
            </div>

            <div class="modal-body">
                <div id="dv_form">
                    <form action="" method="post">
                        <div class="form-container">
                            <div class="form-section">
                                <h3><i class="bi bi-info-circle me-2"></i>General Information</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Fund Cluster</label>
                                        <input type="text" class="form-control" id="fund_cluster" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" id="date" name="date">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">ORS No.</label>
                                        <input type="text" class="form-control" id="ors_no" name="ors_id" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Disbursement Voucher No.</label>
                                        <input type="text" class="form-control" id="dv_no" name="dv_no" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <!-- Payee Details Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-person me-2"></i>Payee Details</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Payee Name</label>
                                            <input type="text" class="form-control" id="payee_name" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">TIN/Employee No.</label>
                                            <input type="text" class="form-control" id="tin_no" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" id="address" readonly>
                                    </div>
                                </div>
                                <!-- Payment Details Section -->
                                <div class="form-section">
                                    <h3><i class="bi bi-file-text me-2"></i>Purpose</h3>
                                    <div class="form-row">
                                        <div class="form-group full-width">
                                            <textarea class="form-control" id="notes" readonly></textarea>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Responsibility Center</label>
                                            <input type="text" class="form-control" id="code" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">OO/PAP</label>
                                            <input type="text" class="form-control" id="oopap_name" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- tax -->
                                <div class="form-section">
                                    <h3><i class="bi bi-calculator me-2"></i>Breakdown of Expenses</h3>
                                    <div class="form-row">
                                        <div class="form-group half-width">
                                            <label class="form-label">Gross Amount</label>
                                            <input type="number" class="form-control" name="total_amount"
                                                id="total_amount" step="0.01" readonly>
                                        </div>
                                        <div class="form-group half-width">
                                            <div class="checkbox-item">
                                                <input type="checkbox" class="apply_taxes" id="apply_taxes">
                                                <label for="apply_taxes">With VAT</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="tax_fields_container" class="tax-fields">
                                        <div class="form-row"></div>

                                        <div class="form-group half-width">
                                            <label class="form-label">VAT <input type="number" class="tax-percentage"
                                                    id="vat_percentage" name="vat" value="12" min="0" max="100"
                                                    readonly>%</label>
                                            <input type="number" class="form-control calculation-field" id="vat_amount"
                                                name="vat_amount" step="0.01" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Tax Base</label>
                                            <input type="number" class="form-control calculation-field" id="tax_base"
                                                name="tax_base" step="0.01">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Less: <input type="number" class="tax-percentage"
                                                    id="tax1_percentage" name="tax_1" value="5" min="0" max="100"> %
                                                Tax</label>
                                            <input type="number" class="form-control calculation-field" id="tax_1"
                                                name="tax_1_amount" step="0.01">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Less: <input type="number" class="tax-percentage"
                                                    id="tax2_percentage" name="tax_2" value="2" min="0" max="100"> %
                                                Tax</label>
                                            <input type="number" class="form-control calculation-field" id="tax_2"
                                                name="tax_2_amount" step="0.01">
                                        </div>
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
                                            <tr>
                                                <td colspan="2">
                                                    <select class="form-control account-select" name="account_titles[]">
                                                        <option selected disabled>Select Account</option>
                                                        <?php
                                                        $account_query = "SELECT * FROM account_title ORDER BY account_title ASC";
                                                        $account_result = $connection->query($account_query);
                                                        while ($account = $account_result->fetch_assoc()) {
                                                            echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control debit-amount"
                                                        name="debit_amounts[]" step="0.01"></td>
                                                <td><input type="number" class="form-control credit-amount"
                                                        name="credit_amounts[]" step="0.01"></td>
                                                <td><button type="button" class="btn btn-danger btn-sm delete-row"><i
                                                            class="bi bi-trash"></i></button></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2">
                                                    <select class="form-control account-select" name="account_titles[]">
                                                        <option selected disabled>Select Cash Account</option>
                                                        <?php
                                                        // Define the specific account codes we want to show
                                                        $cashAccountCodes = ['1010404000', '1010405000', '1010406000'];

                                                        // Query only the specific cash accounts
                                                        $cash_account_query = "SELECT * FROM account_title WHERE account_code IN ('1010404000', '1010405000', '1010406000') ORDER BY account_title ASC";
                                                        $cash_account_result = $connection->query($cash_account_query);

                                                        while ($account = $cash_account_result->fetch_assoc()) {
                                                            echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td><input type="number" class="form-control debit-amount"
                                                        name="debit_amounts[]" step="0.01" readonly></td>
                                                <td><input type="number" class="form-control credit-amount"
                                                        name="credit_amounts[]" step="0.01" readonly></td>
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
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Approver Section -->
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

                            <!-- Buttons -->
                            <div class="btn-container">
                                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" name="submit">
                                    <i class="bi bi-printer me-1"></i> Print DV
                                </button>
                            </div>
                        </div>
                    </form>
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
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
                    const orsId = this.getAttribute('data-id');
                    // Show loader or loading animation
                    document.body.classList.add('loading');

                    fetch(`get_ors_details.php?id=${orsId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('ors_no').value = data.ors_no;
                            document.getElementById('fund_cluster').value = data.fund_cluster;
                            document.getElementById('payee_name').value = data.payee_name;
                            document.getElementById('tin_no').value = data.tin_no;
                            document.getElementById('address').value = data.address;
                            document.getElementById('notes').value = data.notes;
                            document.getElementById('code').value = data.code;
                            document.getElementById('oopap_name').value = data.oopap_name;
                            document.getElementById('total_amount').value = data.total_amount;

                            // Set current date if not already set
                            if (!document.getElementById('date').value) {
                                document.getElementById('date').valueAsDate = new Date();
                            }

                            // Show modal
                            openModal();

                            // Then trigger calculations and add BIR rows
                            setTimeout(() => {
                                calculate(); // Trigger calculation
                                generateDVNumber();
                                document.body.classList.remove('loading');
                            }, 100);
                        })
                        .catch(error => {
                            console.error('Error fetching ORS details:', error);
                            document.body.classList.remove('loading');
                            alert('An error occurred while fetching the ORS details. Please try again.');
                        });
                });
            });

            // Function to open modal with fade effect
            function openModal() {
                modal.style.display = 'block';
                setTimeout(() => {
                    modal.classList.add('show');
                }, 10);
            }

            // Function to close modal with fade effect
            window.closeModal = function () {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                    // Clear form fields if needed
                    resetForm();
                }, 300);
            }

            // Close modal button
            closeModalBtn.addEventListener('click', closeModal);

            // Close modal when clicking outside
            window.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            // Reset form function
            function resetForm() {
                // Clear any custom fields or state
                const tableBody = document.getElementById('accountingTableBody');
                const existingRows = tableBody.querySelectorAll('tr');
                existingRows.forEach(row => {
                    if (row.querySelector('.account-select')?.value === 'BIR') {
                        row.remove();
                    }
                });
            }
        });

        // Add loading spinner styles
        const style = document.createElement('style');
        style.textContent = `
            body.loading::after {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(3px);
                z-index: 9999;
            }
            
            body.loading::before {
                content: '';
                position: fixed;
                top: 50%;
                left: 50%;
                width: 50px;
                height: 50px;
                margin-top: -25px;
                margin-left: -25px;
                border-radius: 50%;
                border: 3px solid rgba(255, 255, 255, 0.3);
                border-top-color: #0077b6;
                animation: spin 1s ease-in-out infinite;
                z-index: 10000;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            .modal {
                transition: opacity 0.3s ease;
                opacity: 0;
            }
            
            .modal.show {
                opacity: 1;
            }
        `;
        document.head.appendChild(style);
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

            // Make tax fields editable or readonly based on VAT checkbox
            function setTaxFieldsEditability() {
                const isVatChecked = applyTaxesCheckbox.checked;
                console.log("Setting editability, VAT checked:", isVatChecked);

                // Explicitly set or remove readonly attribute
                if (isVatChecked) {
                    tax1Input.setAttribute("readonly", "readonly");
                    tax2Input.setAttribute("readonly", "readonly");
                } else {
                    tax1Input.removeAttribute("readonly");
                    tax2Input.removeAttribute("readonly");
                }

                // Update style to visually indicate if editable or not
                tax1PercentageInput.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax2PercentageInput.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax1Input.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";
                tax2Input.style.backgroundColor = isVatChecked ? "#f0f0f0" : "white";

                console.log("Tax1 input readonly:", tax1Input.readOnly);
                console.log("Tax1 percentage readonly:", tax1PercentageInput.readOnly);
            }

            // Recalculate tax amounts when tax percentages change
            function recalculateTaxAmounts() {
                const grossAmount = parseFloat(taxBaseInput.value) || 0;
                const tax1Percentage = parseFloat(tax1PercentageInput.value) || 0;
                const tax2Percentage = parseFloat(tax2PercentageInput.value) || 0;

                // Calculate tax amounts based on percentages
                const tax1Amount = grossAmount * (tax1Percentage / 100);
                const tax2Amount = grossAmount * (tax2Percentage / 100);

                // Update tax amount fields
                tax1Input.value = tax1Amount.toFixed(2);
                tax2Input.value = tax2Amount.toFixed(2);

                // Recalculate net amount
                recalculateNetAmount();
            }


            // Recalculate net amount when tax amounts are manually edited
            function recalculateNetAmount() {
                const grossAmount = parseFloat(amountInput.value) || 0;
                const tax1Amount = parseFloat(tax1Input.value) || 0;
                const tax2Amount = parseFloat(tax2Input.value) || 0;

                // Calculate net amount
                const totalTaxes = tax1Amount + tax2Amount;
                const netAmount = grossAmount - totalTaxes;

                // Update net amount field
                netAmountInput.value = netAmount.toFixed(2);

                // Debug output to help troubleshoot
                console.log("Net amount calculation:", {
                    grossAmount,
                    tax1Amount,
                    tax2Amount,
                    totalTaxes,
                    netAmount
                });
            }

            // Main calculation function
            window.calculate = function () {
                const grossAmount = parseFloat(amountInput.value) || 0;

                console.log("Running calculate() with gross amount:", grossAmount);

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
                } else {
                    // Without VAT - use 0% tax rates as default
                    if (tax1PercentageInput.value === "" || tax1PercentageInput.value === "5") {
                        tax1PercentageInput.value = "0";
                    }
                    if (tax2PercentageInput.value === "" || tax2PercentageInput.value === "2") {
                        tax2PercentageInput.value = "0";
                    }

                    // Calculate tax amounts based on percentages
                    const tax1Percentage = parseFloat(tax1PercentageInput.value) || 0;
                    const tax2Percentage = parseFloat(tax2PercentageInput.value) || 0;

                    const tax1Amount = grossAmount * (tax1Percentage / 100);
                    const tax2Amount = grossAmount * (tax2Percentage / 100);

                    // Net amount is gross amount minus the sum of taxes
                    const totalTaxes = tax1Amount + tax2Amount;
                    const netAmount = grossAmount - totalTaxes;

                    // Update form fields
                    vatAmountInput.value = "0.00";
                    taxBaseInput.value = grossAmount.toFixed(2);
                    tax1Input.value = tax1Amount.toFixed(2);
                    tax2Input.value = tax2Amount.toFixed(2);
                    netAmountInput.value = netAmount.toFixed(2);

                    // Hide VAT fields
                    document.getElementById('tax_fields_container').style.display = 'none';
                }

                // Set fields editability based on VAT checkbox
                setTaxFieldsEditability();
            };

            // Function to set tax percentages when the checkbox is checked and calculate
            function toggleTaxes() {
                if (applyTaxesCheckbox.checked) {
                    tax1PercentageInput.value = 5;
                    tax2PercentageInput.value = 2;
                }
                calculate();
            }

            // Add event listeners
            amountInput.addEventListener("input", calculate);

            // Special handling for checkbox to ensure it triggers editability changes and calculation
            applyTaxesCheckbox.addEventListener("change", function () {
                console.log("VAT checkbox changed to:", this.checked);
                toggleTaxes();
                setTaxFieldsEditability();
            });

            // Add event listeners for tax percentage fields
            tax1PercentageInput.addEventListener("input", function () {
                console.log("Tax1 percentage changed to:", this.value);
                recalculateTaxAmounts();
            });

            tax2PercentageInput.addEventListener("input", function () {
                console.log("Tax2 percentage changed to:", this.value);
                recalculateTaxAmounts();
            });


            // Add event listeners for tax amount fields (when editable)
            tax1Input.addEventListener("input", function () {
                console.log("Tax1 amount changed to:", this.value);
                if (!applyTaxesCheckbox.checked) {
                    recalculateNetAmount();
                }
            });

            tax2Input.addEventListener("input", function () {
                console.log("Tax2 amount changed to:", this.value);
                if (!applyTaxesCheckbox.checked) {
                    recalculateNetAmount();
                }
            });

            // Add event listener for tax base changes
            taxBaseInput.addEventListener("input", function () {
                console.log("Tax base changed to:", this.value);
                if (!applyTaxesCheckbox.checked) {
                    recalculateTaxAmounts();
                }
            });

            // Initial setup
            console.log("Initial setup - setting field editability");
            setTaxFieldsEditability();

            // Only call calculate() if this isn't a modal situation
            if (!document.getElementById('dvFormModal')) {
                console.log("Running initial calculation");
                calculate();
            }
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

            // Function to setup account select with Select2
            function setupAccountSelect(row) {
                const accountSelect = row.querySelector('.account-select');
                const uacsInput = row.querySelector('.uacs-code');

                // Initialize Select2
                $(accountSelect).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Select Account',
                    allowClear: true
                });

                // Update UACS code when selection changes
                $(accountSelect).on('change', function () {
                    const selectedOption = $(this).find('option:selected');
                    if (uacsInput) {
                        uacsInput.value = selectedOption.data('uacs') || '';
                    }
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
                const currentValue = $(select).val();

                // Get all options
                const options = $(select).find('option');

                // Filter options based on selected type
                options.each(function () {
                    if ($(this).val() === "") return; // Skip the "Select Account" option

                    const accountTitle = $(this).data('title')?.toLowerCase() || '';
                    const accountCode = $(this).data('uacs') || '';

                    if (selectedType === "cash_advance") {
                        $(this).toggle(accountTitle.includes('advance'));
                    } else if (selectedType === "transfer_fund") {
                        $(this).toggle(accountTitle.includes('cash') && accountCode.startsWith('10'));
                    } else {
                        $(this).show();
                    }
                });

                // Restore selection if it's still valid
                if (currentValue && $(select).find(`option[value="${currentValue}"]`).length) {
                    $(select).val(currentValue).trigger('change');
                } else {
                    $(select).val(null).trigger('change');
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
                <td><button type="button" class="btn btn-danger btn-sm delete-row"><i class="bi bi-trash"></i></button></td>
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

            // Initialize Select2 on existing account selects when the page loads
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize Select2 on all existing account selects
                $('.account-select').each(function () {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Select Account',
                        allowClear: true
                    });
                });

                // Setup calculation listeners for existing rows
                const existingRows = document.querySelectorAll('tbody tr');
                existingRows.forEach(row => {
                    setupCalculationListeners(row);
                });
            });
        });
    </script>


    <!-- set the main account in the first row -->

    <!-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get references to key elements
            const tableBody = document.getElementById('accountingTableBody');
            const applyTaxesCheckbox = document.getElementById('apply_taxes');
            const tax1PercentageInput = document.getElementById('tax1_percentage');
            const tax2PercentageInput = document.getElementById('tax2_percentage');
            const tax1Input = document.getElementById('tax_1');
            const tax2Input = document.getElementById('tax_2');

            // Add event delegation for delete row buttons
            tableBody.addEventListener('click', function (e) {
                if (e.target.closest('.delete-row')) {
                    const row = e.target.closest('tr');
                    // Don't delete if it's the only row in tbody
                    if (tableBody.querySelectorAll('tr').length > 1) {
                        row.remove();
                        calculateTotals();
                    } else {
                        alert("Cannot delete the last row. At least one account entry is required.");
                    }
                }
            });

            // Function to set the main account in the first row
            function setMainAccount(orsData) {
                if (!orsData || !orsData.account_id) return;

                // Get the first row's account select
                const firstRow = tableBody.querySelector('tr');
                if (!firstRow) return;

                const accountSelect = firstRow.querySelector('.account-select');
                const debitInput = firstRow.querySelector('.debit-amount');

                if (accountSelect && debitInput) {
                    // Set the account selection
                    $(accountSelect).val(orsData.account_id).trigger('change');

                    // Set the amount and make it readonly
                    debitInput.value = orsData.total_amount;
                    debitInput.readOnly = true;
                    debitInput.style.backgroundColor = "#f0f0f0";
                }
            }

            // Function to calculate totals - handles the tfoot values
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

                // Update the footer row's credit field with the difference if positive, 
                // or debit field if negative
                const footerDebitInput = document.querySelector('tfoot .debit-amount');
                const footerCreditInput = document.querySelector('tfoot .credit-amount');

                if (footerDebitInput && footerCreditInput) {
                    if (difference > 0) {
                        footerCreditInput.value = difference.toFixed(2);
                        footerDebitInput.value = "";
                    } else if (difference < 0) {
                        footerDebitInput.value = Math.abs(difference).toFixed(2);
                        footerCreditInput.value = "";
                    } else {
                        footerCreditInput.value = "";
                        footerDebitInput.value = "";
                    }
                }
            }

            // Add event listeners for tax changes
            applyTaxesCheckbox.addEventListener('change', function () {
                calculate(); // Assume this function exists in your main code
                setTimeout(calculateTotals, 100);
            });

            tax1PercentageInput.addEventListener('input', function () {
                calculate();
                setTimeout(calculateTotals, 100);
            });

            tax2PercentageInput.addEventListener('input', function () {
                calculate();
                setTimeout(calculateTotals, 100);
            });

            // Hook into existing view details event
            const viewDetailsButtons = document.querySelectorAll('.view-details');
            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const orsId = this.getAttribute('data-id');
                    fetch(`get_ors_details.php?id=${orsId}`)
                        .then(response => response.json())
                        .then(data => {
                            // Wait a moment to ensure the DOM and Select2 are ready
                            setTimeout(() => {
                                setMainAccount(data);
                                calculate(); // Trigger tax calculation
                                calculateTotals(); // Update totals
                            }, 300);
                        })
                        .catch(error => console.error('Error fetching ORS details:', error));
                });
            });

            // Override the global calculateTotals function
            window.calculateTotals = calculateTotals;
        });
    </script> -->

    <!-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const netAmountInput = document.getElementById('net_amount');
            const footerDebitInput = document.querySelector('tfoot .debit-amount');
            const footerCreditInput = document.querySelector('tfoot .credit-amount');

            form.addEventListener('submit', function (event) {
                const netAmount = parseFloat(netAmountInput.value) || 0;
                const footerDebit = parseFloat(footerDebitInput.value) || 0;
                const footerCredit = parseFloat(footerCreditInput.value) || 0;

                // Check if the net amount matches the footer value
                if (netAmount !== footerDebit && netAmount !== footerCredit) {
                    event.preventDefault(); // Prevent form submission
                    alert('The net amount does not match the total in the accounting entries. Please correct the values.');
                }
            });
        });
    </script> -->

</body>

</html>