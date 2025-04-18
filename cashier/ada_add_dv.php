<?php
include '../DBConnection.php';
session_start();

// Set success message if available
$success_message = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "DVs successfully added to ADA payment!";
}

// Get reference number from URL
$reference_no = isset($_GET['ref']) ? $_GET['ref'] : '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $ada_reference = $_POST['ada_reference'];
    $selected_dvs = isset($_POST['dv_ids']) ? $_POST['dv_ids'] : [];
    
    if (!empty($selected_dvs)) {
        try {
            // Start transaction
            $connection->begin_transaction();
            
            // Get the payment date for this ADA reference
            $payment_query = "SELECT DISTINCT payment_date, remarks FROM payment 
                             WHERE reference_no = ? AND payment_type = 'ADA' LIMIT 1";
            $stmt = $connection->prepare($payment_query);
            $stmt->bind_param('s', $ada_reference);
            $stmt->execute();
            $result = $stmt->get_result();
            $payment_data = $result->fetch_assoc();
            
            if ($payment_data) {
                $payment_date = $payment_data['payment_date'];
                $remarks = $payment_data['remarks'];
                
                // Insert payment for each DV and update DV status
                foreach ($selected_dvs as $dv_id) {
                    // Get DV information
                    $dv_query = "SELECT d.*, o.ors_no, p.payee_name 
                                FROM dv d 
                                JOIN ors o ON d.ors_id = o.ors_id 
                                JOIN payee p ON o.payee_id = p.payee_id 
                                WHERE d.dv_id = ?";
                    $dv_stmt = $connection->prepare($dv_query);
                    $dv_stmt->bind_param("i", $dv_id);
                    $dv_stmt->execute();
                    $dv_result = $dv_stmt->get_result();
                    $dv_data = $dv_result->fetch_assoc();
                    
                    $amount = $dv_data['net_amount'];
                    
                    // Insert payment record
                    $insert_query = "INSERT INTO payment (dv_id, payment_date, payment_type, reference_no, amount, remarks, created_by, status) 
                                    VALUES (?, ?, 'ADA', ?, ?, ?, 'Cashier', 'Pending')";
                    
                    $insert_stmt = $connection->prepare($insert_query);
                    $insert_stmt->bind_param("issds", $dv_id, $payment_date, $ada_reference, $amount, $remarks);
                    $insert_stmt->execute();
                    
                    // Update DV status
                    $update_dv = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
                    $update_stmt = $connection->prepare($update_dv);
                    $update_stmt->bind_param("i", $dv_id);
                    $update_stmt->execute();
                }
                
                $connection->commit();
                header("Location: ada_add_dv.php?ref=" . urlencode($ada_reference) . "&success=1");
                exit();
            } else {
                throw new Exception("ADA reference not found.");
            }
        } catch (Exception $e) {
            $connection->rollback();
            $error_message = "Error: " . $e->getMessage();
        }
    } else {
        $error_message = "Please select at least one DV to add.";
    }
}

// Get ADA details if reference number is provided
$ada_details = null;
if (!empty($reference_no)) {
    $ada_query = "SELECT p.reference_no, p.payment_date, p.remarks, 
                 COUNT(p.payment_id) AS dv_count, SUM(p.amount) AS total_amount
                 FROM payment p
                 WHERE p.reference_no = ? AND p.payment_type = 'ADA'
                 GROUP BY p.reference_no, p.payment_date, p.remarks";
    $ada_stmt = $connection->prepare($ada_query);
    $ada_stmt->bind_param('s', $reference_no);
    $ada_stmt->execute();
    $ada_result = $ada_stmt->get_result();
    $ada_details = $ada_result->fetch_assoc();
}

// Get list of ADA reference numbers
$ada_refs_query = "SELECT DISTINCT reference_no, MIN(payment_date) as payment_date 
                  FROM payment 
                  WHERE payment_type = 'ADA' 
                  GROUP BY reference_no 
                  ORDER BY MIN(payment_date) DESC";
$ada_refs_result = mysqli_query($connection, $ada_refs_query);

// Get pending DVs that can be added to an ADA
$pending_dvs_query = "SELECT d.dv_id, d.dv_no, p.payee_name, d.net_amount, d.date
                     FROM dv d
                     JOIN ors o ON d.ors_id = o.ors_id
                     JOIN payee p ON o.payee_id = p.payee_id
                     WHERE (d.status = 'Endorsed' OR d.chief_accountant IS NOT NULL)
                     AND d.dv_id NOT IN (SELECT dv_id FROM payment WHERE status != 'Rejected')
                     ORDER BY d.date DESC";
$pending_dvs_result = mysqli_query($connection, $pending_dvs_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Add DVs to ADA Payment - DTI PHP</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

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
    <link rel="stylesheet" href="css/table.css">

    <style>
        /* Additional Styling */
        .select-ada-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #4154f1;
            border-radius: 8px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .select-ada-card:hover {
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }

        .ada-summary-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .ada-summary-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .ada-summary-card .card-header {
            background-color: rgba(65, 84, 241, 0.05);
            border-bottom: none;
            padding: 1rem;
        }
        
        .ada-summary-card .card-title {
            margin-bottom: 0;
            font-size: 0.95rem;
            color: #012970;
        }
        
        .ada-summary-card .card-body {
            padding: 1.5rem;
        }
        
        .ada-summary-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #012970;
            margin-bottom: 0.5rem;
        }
        
        .ada-summary-label {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .ada-summary-icon {
            width: 45px;
            height: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .icon-primary {
            background-color: rgba(65, 84, 241, 0.1);
            color: #4154f1;
        }
        
        .icon-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .icon-info {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: #555;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #888;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .remarks-card {
            background-color: rgba(255, 193, 7, 0.05);
            border-left: 4px solid #ffc107;
            border-radius: 0.25rem;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(65, 84, 241, 0.04);
        }
    </style>
</head>

<body>
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Add DVs to ADA Payment</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="ada_records.php">ADA Records</a></li>
                    <li class="breadcrumb-item active">Add DVs</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <?php if (!empty($success_message)): ?>
                    <div class="col-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-1"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="col-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-bank me-2"></i>ADA Payment Management</h5>
                            <p class="text-muted">Add additional disbursement vouchers to an existing ADA payment record</p>
                            
                            <form method="POST" id="adaForm">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="select-ada-card p-4">
                                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-search me-2"></i>Select ADA Reference Number</h6>
                                            <div class="mb-3">
                                                <select class="form-select form-select-lg shadow-none" id="ada_reference" name="ada_reference" required>
                                                    <option value="">-- Select ADA Reference --</option>
                                                    <?php while ($ada_ref = mysqli_fetch_assoc($ada_refs_result)): ?>
                                                        <option value="<?php echo htmlspecialchars($ada_ref['reference_no']); ?>" 
                                                            <?php echo ($reference_no == $ada_ref['reference_no']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($ada_ref['reference_no']); ?> 
                                                            (<?php echo date('M d, Y', strtotime($ada_ref['payment_date'])); ?>)
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                                <div class="form-text mt-2">
                                                    <i class="bi bi-info-circle me-1"></i> Select an existing ADA reference number to add more DVs to it
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($ada_details): ?>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-4">
                                        <div class="ada-summary-card">
                                            <div class="card-body text-center">
                                                <div class="ada-summary-icon icon-primary mx-auto">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div class="ada-summary-number"><?php echo $ada_details['dv_count']; ?></div>
                                                <div class="ada-summary-label">Current DV Count</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ada-summary-card">
                                            <div class="card-body text-center">
                                                <div class="ada-summary-icon icon-success mx-auto">
                                                    <i class="bi bi-cash-stack"></i>
                                                </div>
                                                <div class="ada-summary-number">₱<?php echo number_format($ada_details['total_amount'], 2); ?></div>
                                                <div class="ada-summary-label">Current Total Amount</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="ada-summary-card">
                                            <div class="card-body text-center">
                                                <div class="ada-summary-icon icon-info mx-auto">
                                                    <i class="bi bi-calendar-date"></i>
                                                </div>
                                                <div class="ada-summary-number"><?php echo date('M d, Y', strtotime($ada_details['payment_date'])); ?></div>
                                                <div class="ada-summary-label">Payment Date</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($ada_details['remarks'])): ?>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card remarks-card border-0">
                                            <div class="card-body p-4">
                                                <h6 class="text-warning fw-semibold mb-3"><i class="bi bi-pencil-square me-2"></i>Cashier's Remarks</h6>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($ada_details['remarks'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                                                <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Available Disbursement Vouchers</h5>
                                                <div class="input-group" style="width: 250px;">
                                                    <span class="input-group-text bg-white border-end-0">
                                                        <i class="bi bi-search text-muted"></i>
                                                    </span>
                                                    <input type="text" id="searchDV" class="form-control border-start-0 ps-0" placeholder="Search DV...">
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <?php if (mysqli_num_rows($pending_dvs_result) > 0): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover datatable align-middle" id="pendingDVTable">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="5%" class="text-center">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                                                        </div>
                                                                    </th>
                                                                    <th>DV No.</th>
                                                                    <th>Payee</th>
                                                                    <th class="text-end">Amount</th>
                                                                    <th>Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php while ($dv = mysqli_fetch_assoc($pending_dvs_result)): ?>
                                                                    <tr>
                                                                        <td class="text-center">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input dv-checkbox" type="checkbox" name="dv_ids[]" value="<?php echo $dv['dv_id']; ?>">
                                                                            </div>
                                                                        </td>
                                                                        <td><span class="fw-medium text-primary"><?php echo htmlspecialchars($dv['dv_no']); ?></span></td>
                                                                        <td><div class="fw-medium"><?php echo htmlspecialchars($dv['payee_name']); ?></div></td>
                                                                        <td class="text-end fw-medium text-success">₱<?php echo number_format($dv['net_amount'], 2); ?></td>
                                                                        <td><?php echo date('M d, Y', strtotime($dv['date'])); ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="empty-state py-5">
                                                        <i class="bi bi-folder-x text-muted"></i>
                                                        <h5 class="mt-3">No pending vouchers available</h5>
                                                        <p class="text-muted">There are no pending disbursement vouchers available for payment at this time.</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer bg-white border-top-0 py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-muted small" id="selectedCount">0 DVs selected</div>
                                                    <div>
                                                        <button type="submit" name="submit" id="submitBtn" class="btn btn-primary px-4" disabled>
                                                            <i class="bi bi-plus-circle me-2"></i> Add Selected DVs to ADA Payment
                                                        </button>
                                                        <a href="ada_records.php" class="btn btn-light ms-2">
                                                            <i class="bi bi-arrow-left me-2"></i> Back to ADA Records
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            const dataTable = new simpleDatatables.DataTable("#pendingDVTable", {
                searchable: true,
                fixedHeight: true,
                perPage: 10,
                perPageSelect: [5, 10, 15, 20, 25],
                columns: [
                    { select: 0, sortable: false },
                    { select: 3, type: "number", format: "₱#,###.##" }
                ],
                labels: {
                    placeholder: "Search...",
                    perPage: "{select} entries per page",
                    noRows: "No DVs found",
                    info: "Showing {start} to {end} of {rows} entries"
                }
            });
            
            // Search functionality
            document.querySelector('#searchDV').addEventListener('keyup', function() {
                dataTable.search(this.value);
            });
            
            // Check/uncheck all checkboxes
            document.querySelector('#checkAll').addEventListener('change', function() {
                const isChecked = this.checked;
                const checkboxes = document.querySelectorAll('.dv-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                updateSubmitButton();
                updateSelectedCount();
            });
            
            // Update "Select All" checkbox state and submit button
            document.querySelectorAll('.dv-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateCheckAllState();
                    updateSubmitButton();
                    updateSelectedCount();
                });
            });
            
            // Function to update "Select All" checkbox state
            function updateCheckAllState() {
                const checkboxes = document.querySelectorAll('.dv-checkbox');
                const checkAll = document.querySelector('#checkAll');
                const checkedBoxes = document.querySelectorAll('.dv-checkbox:checked');
                
                checkAll.checked = checkedBoxes.length === checkboxes.length && checkboxes.length > 0;
                checkAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
            }
            
            // Update selected count display
            function updateSelectedCount() {
                const selectedCount = document.getElementById('selectedCount');
                const checkedBoxes = document.querySelectorAll('.dv-checkbox:checked');
                selectedCount.textContent = `${checkedBoxes.length} DVs selected`;
            }
            
            // Enable/disable submit button based on selections
            function updateSubmitButton() {
                const submitBtn = document.querySelector('#submitBtn');
                const checkedBoxes = document.querySelectorAll('.dv-checkbox:checked');
                const adaReference = document.querySelector('#ada_reference').value;
                
                submitBtn.disabled = checkedBoxes.length === 0 || adaReference === '';
                
                // Change button appearance based on state
                if (checkedBoxes.length === 0 || adaReference === '') {
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-secondary');
                } else {
                    submitBtn.classList.remove('btn-secondary');
                    submitBtn.classList.add('btn-primary');
                }
            }
            
            // Update button when ADA reference changes
            document.querySelector('#ada_reference').addEventListener('change', function() {
                updateSubmitButton();
                
                // If reference is selected, redirect to show current ADA details
                if (this.value !== '') {
                    window.location.href = 'ada_add_dv.php?ref=' + encodeURIComponent(this.value);
                }
            });
            
            // Form submission validation
            document.querySelector('#adaForm').addEventListener('submit', function(e) {
                const checkedBoxes = document.querySelectorAll('.dv-checkbox:checked');
                const adaReference = document.querySelector('#ada_reference').value;
                
                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one DV to add to the ADA payment.');
                    return false;
                }
                
                if (adaReference === '') {
                    e.preventDefault();
                    alert('Please select an ADA reference number.');
                    return false;
                }
                
                return true;
            });
            
            // Initial state update
            updateCheckAllState();
            updateSubmitButton();
            updateSelectedCount();
        });
    </script>
</body>
</html> 