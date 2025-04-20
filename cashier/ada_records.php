<?php
include '../DBConnection.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_ada'])) {
    $reference_no = $_POST['reference_no'];
    $new_reference_no = $_POST['new_reference_no'];
    $payment_date = $_POST['payment_date'];
    $remarks = $_POST['remarks'];
    $connection->begin_transaction();
    
    try {
        $update_query = "UPDATE payment 
                        SET reference_no = ?, payment_date = ?, remarks = ? 
                        WHERE reference_no = ? AND payment_type = 'ADA'";
        
        $stmt = $connection->prepare($update_query);
        $stmt->bind_param("ssss", $new_reference_no, $payment_date, $remarks, $reference_no);
        $stmt->execute();

        $connection->commit();
        
     header('Location: ada_records.php?edit_success=1&reference=' . urlencode($new_reference_no));
        exit();
    } catch (Exception $e) {
        $connection->rollback();
        $error_message = "Error updating ADA: " . $e->getMessage();
    }
}
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'); 
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_conditions = [];
$where_conditions[] = "p.payment_type = 'ADA'";
$where_conditions[] = "p.payment_date BETWEEN '$from_date' AND '$to_date'";

if (!empty($search)) {
    $where_conditions[] = "(p.reference_no LIKE '%$search%' OR pa.payee_name LIKE '%$search%' OR d.dv_no LIKE '%$search%' OR o.ors_no LIKE '%$search%')";
}

$where_clause = implode(' AND ', $where_conditions);

$ada_query = "SELECT p.reference_no, MIN(p.payment_date) as payment_date, 
             COUNT(p.payment_id) as dv_count, SUM(p.amount) as total_amount,
             GROUP_CONCAT(DISTINCT pa.payee_name SEPARATOR ', ') as payees
             FROM payment p
             JOIN dv d ON p.dv_id = d.dv_id
             JOIN ors o ON d.ors_id = o.ors_id
             JOIN payee pa ON o.payee_id = pa.payee_id
             WHERE $where_clause
             GROUP BY p.reference_no
             ORDER BY p.payment_date DESC";

$ada_result = mysqli_query($connection, $ada_query);

$total_query = "SELECT COUNT(DISTINCT p.reference_no) as ada_count,
                SUM(p.amount) as ada_amount
                FROM payment p
                WHERE $where_clause";
$total_result = mysqli_query($connection, $total_query);
$totals = mysqli_fetch_assoc($total_result);

if (isset($_GET['regenerate_lddap']) && isset($_GET['reference'])) {
    $reference_no = mysqli_real_escape_string($connection, $_GET['reference']);
    
    $payments_query = "SELECT p.*, d.dv_no, d.net_amount as dv_net, d.vat_amount, d.tax_1_amount, d.tax_2_amount, 
                      p.amount, pa.payee_name, pa.bank_acc_no, o.ors_no, o.purpose, o.notes
                      FROM payment p
                      JOIN dv d ON p.dv_id = d.dv_id
                      JOIN ors o ON d.ors_id = o.ors_id
                      JOIN payee pa ON o.payee_id = pa.payee_id
                      WHERE p.reference_no = ? AND p.payment_type = 'ADA'";
    
    $stmt = $connection->prepare($payments_query);
    $stmt->bind_param('s', $reference_no);
    $stmt->execute();
    $payments_result = $stmt->get_result();
    
    if ($payments_result->num_rows > 0) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['lddap_data'] = [
            'reference_no' => $reference_no,
            'payment_date' => '',
            'remarks' => '',
            'dvs' => [],
            'total_gross' => 0,
            'total_withholding' => 0,
            'total_net' => 0
        ];
        
        $total_gross = 0;
        $total_withholding = 0;
        $total_net = 0;
        
        while ($row = $payments_result->fetch_assoc()) {
            $_SESSION['lddap_data']['payment_date'] = $row['payment_date'];
            $_SESSION['lddap_data']['remarks'] = $row['remarks'];
            
            $gross_amount = $row['dv_net'] + $row['vat_amount'] + $row['tax_1_amount'] + $row['tax_2_amount'];
            $net_amount = $row['amount'];
            $withholding_tax = $gross_amount - $row['dv_net'];
            
            $_SESSION['lddap_data']['dvs'][] = [
                'dv_id' => $row['dv_id'],
                'dv_no' => $row['dv_no'],
                'payee_name' => $row['payee_name'],
                'bank_account' => $row['bank_acc_no'] ?? 'N/A',
                'ors_no' => $row['ors_no'],
                'purpose' => $row['purpose'],
                'notes' => $row['notes'],
                'gross_amount' => $gross_amount,
                'withholding_tax' => $withholding_tax,
                'net_amount' => $net_amount
            ];
            
            $total_gross += $gross_amount;
            $total_withholding += $withholding_tax;
            $total_net += $net_amount;
        }
        
        $_SESSION['lddap_data']['total_gross'] = $total_gross;
        $_SESSION['lddap_data']['total_withholding'] = $total_withholding;
        $_SESSION['lddap_data']['total_net'] = $total_net;
        
        header('Location: generate_lddap.php?ref=' . urlencode($reference_no));
        exit();
    }
}

$success_message = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "LDDAP-ADA Form has been generated successfully!";
}
if (isset($_GET['update_success']) && $_GET['update_success'] == 1) {
    $success_message = "ADA record has been updated successfully!";
}

if (isset($_GET['regenerate_lddap']) && isset($_GET['reference'])) {
    $reference_no = mysqli_real_escape_string($connection, $_GET['reference']);
    
    $query = "SELECT * FROM ada_payment WHERE reference_no = '$reference_no'";
    $result = @mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $_SESSION['reference_no'] = $row['reference_no'];
        $_SESSION['payment_date'] = $row['payment_date'];
        $_SESSION['payment_remarks'] = $row['remarks'];        
        header("Location: ../cashier/lddap_form.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>ADA Records - DTI PHP</title>
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
    
     /* Button Group Styling */
        .btn-group .btn {
            margin-right: 0.25rem;
            border-radius: 4px !important;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        /* Filter Section */
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .filter-section label {
            font-weight: 600;
            color: #555;
        }
        
        /* Summary Card */
        .summary-card {
            background: linear-gradient(135deg, #4154f1, #5969f3);
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,.1);
            margin-bottom: 20px;
        }
        
        .summary-card .card-body {
            padding: 20px;
        }
        
        .summary-card h5 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .summary-card p {
            opacity: 0.8;
            margin-bottom: 0;
        }
        
        /* Empty State */
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
    </style>
</head>

<body>
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>ADA Records</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">ADA Records</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <?php if (isset($_GET['edit_success']) && $_GET['edit_success'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            ADA record <?php echo htmlspecialchars($_GET['reference']); ?> has been updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <section class="section">
            <div class="row">
               

                <!-- ADA Records Table -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ADA Payment Records</h5>
                            <?php if (mysqli_num_rows($ada_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="datatable">
                                    <thead>
                                        <tr class="bg-light text-dark">
                                            <th>Date</th>
                                            <th>Reference No</th>
                                            <th>DV Count</th>
                                            <th>Total Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($ada_result)): 
                                            $reference_no = $row['reference_no'];
                                            $modal_id = md5($reference_no);
                                        ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                            <td><span class="badge bg-light text-primary"><?php echo $reference_no; ?></span></td>
                                            <td><?php echo $row['dv_count']; ?></td>
                                            <td class="text-success fw-bold">PHP <?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info rounded" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $modal_id; ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning rounded" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $modal_id; ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <a href="ada_add_dv.php?ref=<?php echo urlencode($reference_no); ?>" class="btn btn-sm btn-info rounded">
                                                        <i class="bi bi-plus-circle"></i>
                                                    </a>
                                                    <a href="ada_records.php?regenerate_lddap=1&reference=<?php echo urlencode($reference_no); ?>" class="btn btn-sm btn-primary rounded">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-folder-x"></i>
                                <h5>No ADA records found</h5>
                                <p>There are no ADA payment records matching your criteria.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    // Reset the result pointer
    mysqli_data_seek($ada_result, 0);
    
    // Generate detail modals for each ADA record
    while ($row = mysqli_fetch_assoc($ada_result)):
        $reference_no = $row['reference_no'];
        $modal_id = md5($reference_no);
        
        // Get details for this reference number
        $detail_query = "SELECT p.*, d.dv_no, o.ors_no, pa.payee_name, o.purpose, o.notes
                       FROM payment p
                       JOIN dv d ON p.dv_id = d.dv_id
                       JOIN ors o ON d.ors_id = o.ors_id
                       JOIN payee pa ON o.payee_id = pa.payee_id
                       WHERE p.reference_no = '$reference_no'
                       ORDER BY pa.payee_name";
        $detail_result = mysqli_query($connection, $detail_query);
    ?>
    <!-- Modal for ADA Details -->
    <div class="modal fade" id="detailsModal<?php echo $modal_id; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>ADA Details: <?php echo $reference_no; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">ADA Reference #<?php echo $reference_no; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                        <h6 class="text-info border-bottom pb-2 mb-3">
                                            <i class="bi bi-info-circle me-1"></i> ADA Information
                                        </h6>
                                        <div class="mb-2">
                                            <span class="text-muted">Reference Number:</span>
                                            <span class="fw-medium ms-2"><?php echo $reference_no; ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">Date:</span>
                                            <span class="fw-medium ms-2"><?php echo date('F d, Y', strtotime($row['payment_date'])); ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted">DV Count:</span>
                                            <span class="fw-medium ms-2"><?php echo $row['dv_count']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                        <h6 class="text-success border-bottom pb-2 mb-3">
                                            <i class="bi bi-cash-coin me-1"></i> Financial Details
                                        </h6>
                                        <div class="mb-2">
                                            <span class="text-muted">Total Amount:</span>
                                            <span class="fw-bold ms-2 fs-5 text-success">₱<?php echo number_format($row['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border rounded p-3 mb-3 bg-light-subtle">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-list-check me-1"></i> Included Disbursement Vouchers
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>DV No</th>
                                                <th>ORS No</th>
                                                <th>Payee</th>
                                                <th class="text-end">Amount</th>
                                                <th>Purpose</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            mysqli_data_seek($detail_result, 0);
                                            while ($detail = mysqli_fetch_assoc($detail_result)): 
                                            ?>
                                            <tr>
                                                <td><span class="fw-medium text-primary"><?php echo $detail['dv_no']; ?></span></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo $detail['ors_no']; ?></span></td>
                                                <td><div class="fw-medium"><?php echo $detail['payee_name']; ?></div></td>
                                                <td class="text-end fw-medium text-success">₱<?php echo number_format($detail['amount'], 2); ?></td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($detail['notes'] ?: $detail['purpose']); ?>">
                                                        <?php echo substr($detail['notes'] ?: $detail['purpose'], 0, 40) . (strlen($detail['notes'] ?: $detail['purpose']) > 40 ? '...' : ''); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <?php
                            // Reset pointer to get the first record for remarks
                            mysqli_data_seek($detail_result, 0);
                            $first_detail = mysqli_fetch_assoc($detail_result);
                            if (!empty($first_detail['remarks'])): 
                            ?>
                            <div class="border rounded p-3 bg-light-subtle">
                                <h6 class="text-warning border-bottom pb-2 mb-3">
                                    <i class="bi bi-pencil-square me-1"></i> Remarks
                                </h6>
                                <p class="mb-0"><?php echo nl2br($first_detail['remarks']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <a href="ada_records.php?regenerate_lddap=1&reference=<?php echo urlencode($reference_no); ?>" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i> Generate LDDAP-APA
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Edit ADA -->
    <div class="modal fade" id="editModal<?php echo $modal_id; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit ADA: <?php echo $reference_no; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="back_end/update_ada.php">
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                            <div>
                                You are editing the ADA record with reference number <strong><?php echo $reference_no; ?></strong>.
                            </div>
                        </div>
                        
                        <input type="hidden" name="original_reference" value="<?php echo $reference_no; ?>">
                        
                        <div class="mb-3">
                            <label for="reference_no_<?php echo $modal_id; ?>" class="form-label fw-medium">ADA Reference Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="reference_no_<?php echo $modal_id; ?>" name="reference_no" value="<?php echo $reference_no; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_date_<?php echo $modal_id; ?>" class="form-label fw-medium">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="payment_date_<?php echo $modal_id; ?>" name="payment_date" value="<?php echo date('Y-m-d', strtotime($row['payment_date'])); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks_<?php echo $modal_id; ?>" class="form-label fw-medium">Remarks</label>
                            <textarea class="form-control" id="remarks_<?php echo $modal_id; ?>" name="remarks" rows="3" placeholder="Enter any additional information or notes about this ADA payment"><?php 
                                mysqli_data_seek($detail_result, 0);
                                $first_detail = mysqli_fetch_assoc($detail_result);
                                echo $first_detail['remarks'] ?? '';
                            ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                        <button type="submit" name="update_ada" class="btn btn-warning text-white">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize datatables
        try {
            const table = document.querySelector('.datatable');
            if (table) {
                const dataTable = new simpleDatatables.DataTable(table, {
                    perPageSelect: [10, 20, 50, 100],
                    columns: [
                        // Sort date column properly
                        { select: 0, sort: "desc", type: "date", format: "MMM DD, YYYY" }
                    ]
                });
            } else {
                console.error('Table element not found');
            }
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }
    });
    </script>
</body>
</html> 