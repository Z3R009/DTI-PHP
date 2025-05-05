<?php
include '../DBConnection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Disbursement Voucher - DTI Book Keeper</title>
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


</head>

<body>

    <div class="card shadow" style="max-width: 900px; margin: auto;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>Disbursement Voucher
            </h2>
            <button class="btn btn-primary" aria-label="Close" onclick="window.location.href='dv.php';">Back</button>


        </div>

        <div class="card-body">
            <form method="post">
                <div class="form-cntainer">
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
                                <input type="number" class="form-control" name="total_amount" id="total_amount"
                                    step="0.01" readonly>
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
                                        id="vat_percentage" name="vat" value="12" min="0" max="100" readonly>%</label>
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
                                        <select class="form-control account-select" name="account_titles[]" required>
                                            <option selected disabled value="">Select Account</option>
                                            <?php
                                            $account_query = "SELECT * FROM account_title ORDER BY account_title ASC";
                                            $account_result = $connection->query($account_query);
                                            while ($account = $account_result->fetch_assoc()) {
                                                echo "<option value='" . $account['account_id'] . "' data-uacs='" . $account['account_code'] . "' data-title='" . htmlspecialchars($account['account_title']) . "'>" . htmlspecialchars($account['account_title']) . " - " . $account['account_code'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control debit-amount" name="debit_amounts[]"
                                            step="0.01"></td>
                                    <td><input type="number" class="form-control credit-amount" name="credit_amounts[]"
                                            step="0.01"></td>
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

                <!-- Buttons -->
                <div class="btn-container">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" name="submit">
                        <i class="bi bi-printer me-1"></i> Print DV
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
</body>

</html>