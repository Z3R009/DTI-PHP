<?php
include '../DBConnection.php';

if (!isset($connection) || !$connection) {
    $conn_error = "Database connection is not available. Please check the DBConnection.php file.";
    error_log($conn_error);
}

$success_message = '';
if(isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Report generated successfully!';
}

$error_message = '';
if(isset($_GET['error']) && !empty($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

$query = "SELECT
    ba.batch_id,
    ba.reference_no,
    ba.payment_date,
    ba.fund_code,
    ba.total_gross,
    ba.total_withholding,
    ba.total_net,
    ba.status,
    ba.created_at,
    ba.bank_info,
    an.account_name,
    an.account_number,
    dp.balances AS current_balance,
    dp.cash_allotment,
    dp.draft_id,
    (SELECT COUNT(*) FROM batch_ada_dvs WHERE batch_id = ba.batch_id) AS dv_count
FROM batch_ada ba
LEFT JOIN batch_ada_dvs bad ON bad.batch_id = ba.batch_id
LEFT JOIN dv ON dv.dv_id = bad.dv_id
LEFT JOIN account_name an ON an.account_id = dv.account_id
LEFT JOIN draft_project dp ON dp.account_id = an.account_id
    AND dp.draft_id = (
        SELECT MAX(dp2.draft_id) FROM draft_project dp2 
        WHERE dp2.account_id = an.account_id
    )
GROUP BY ba.batch_id
ORDER BY ba.created_at DESC";

$result = $connection->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>ADA Payments Report - DTI PHP</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/table.css">

</head>

<body>
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>
    
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>ADA Payments Report</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">ADA Payments Report</li>
                </ol>
            </nav>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($conn_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-database-x me-1"></i>
            <strong>Database Connection Error:</strong> Could not connect to the database. Some features may not work properly.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="card-title fs-4 text-primary mb-1">ADA Payments Summary</h5>
                                    <p class="text-muted">This report shows all ADA payments, account deductions, and remaining balances.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary" id="printReportBtn">
                                        <i class="bi bi-printer"></i> Print Report
                                    </button>
                                    <button type="button" class="btn btn-success" id="exportExcelBtn">
                                        <i class="bi bi-file-excel"></i> Export to Excel
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reference No</th>
                                            <th>Payment Date</th>
                                            <th>Account</th>
                                            <th>Vouchers</th>
                                            <th class="text-end">Total Amount</th>
                                            <th class="text-end">Previous Balance</th>
                                            <th class="text-end">Current Balance</th>
                                            <!-- <th>Status</th> -->
                                            <th width="10%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result && $result->num_rows > 0): ?>
                                            <?php 
                                            while ($row = $result->fetch_assoc()): 
                                                // Previous balance should be current_balance + total_net (balance before deduction)
                                                $previous_balance = ($row['current_balance'] ?? 0) + ($row['total_net'] ?? 0);
                                                $current_balance = $row['current_balance'] ?? 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-medium text-primary"><?php echo $row['reference_no']; ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                                <td>
                                                    <div class="fw-medium"><?php echo $row['account_name'] ?? 'Unknown Account'; ?></div>
                                                    <div class="small text-muted"><?php echo $row['account_number'] ?? ''; ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $row['dv_count']; ?> vouchers</span>
                                                </td>
                                                <td class="text-end fw-medium text-danger">₱<?php echo number_format($row['total_net'] ?? 0, 2); ?></td>
                                                <td class="text-end fw-medium">₱<?php echo number_format($previous_balance, 2); ?></td>
                                                <td class="text-end fw-medium text-success">₱<?php echo number_format($current_balance, 2); ?></td>
                                                <!-- <td>
                                                    <?php 
                                                    switch($row['status']) {
                                                        case 'Pending':
                                                            echo '<span class="badge bg-warning">Pending</span>';
                                                            break;
                                                        case 'Completed':
                                                            echo '<span class="badge bg-success">Completed</span>';
                                                            break;
                                                        case 'Cancelled':
                                                            echo '<span class="badge bg-danger">Cancelled</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="badge bg-secondary">Unknown</span>';
                                                    }
                                                    ?>
                                                </td> -->
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-info text-white rounded-circle me-1 view-details-btn" 
                                                                data-batch-id="<?php echo $row['batch_id']; ?>" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary rounded-circle me-1 print-ada-btn"
                                                                data-reference="<?php echo $row['reference_no']; ?>" title="Print ADA">
                                                            <i class="bi bi-printer"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="py-5">
                                                        <i class="bi bi-exclamation-circle text-muted fs-1"></i>
                                                        <h5 class="mt-3">No ADA Payments Found</h5>
                                                        <p class="text-muted">There are no ADA payments recorded in the system.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ADA Details Modal -->
    <div class="modal fade" id="adaDetailsModal" tabindex="-1" aria-labelledby="adaDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="adaDetailsModalLabel"><i class="bi bi-bank me-2"></i>ADA Payment Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="border-start border-primary border-4 ps-3">
                                <h6 class="text-primary fw-bold mb-2">ADA Information</h6>
                                <p class="mb-1 fs-5" id="adaReferenceNo"></p>
                                <p class="text-muted" id="adaDate"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-start border-success border-4 ps-3">
                                <h6 class="text-success fw-bold mb-2">Account Details</h6>
                                <p class="mb-1 fs-5" id="accountName"></p>
                                <p class="text-muted" id="accountNumber"></p>
                                <p class="text-muted" id="voucherCount"><strong>Vouchers:</strong> <span class="badge bg-secondary">0</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary">Gross Amount</h6>
                                    <h3 class="card-text" id="totalGross">₱0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-info">Withholding Tax</h6>
                                    <h3 class="card-text" id="totalWithholding">₱0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-success">Net Amount</h6>
                                    <h3 class="card-text" id="totalNet">₱0.00</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">Account Balance</h6>
                                    <p class="mb-1"><small>Previous: <span id="previousBalance" class="fw-medium">₱0.00</span></small></p>
                                    <p class="mb-0"><small>Current: <span id="currentBalance" class="fw-medium text-success">₱0.00</span></small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="text-primary fw-bold mb-3">Included Vouchers</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="adaVouchersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>DV No</th>
                                    <th>Payee</th>
                                    <th>Status</th>
                                    <th>Purpose</th>
                                    <th class="text-end">Gross Amount</th>
                                    <th class="text-end">Withholding</th>
                                    <th class="text-end">Net Amount</th>
                                </tr>
                            </thead>
                            <tbody id="adaVouchersBody">
                                <!-- Will be populated via JavaScript -->
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Totals:</td>
                                    <td class="text-end fw-bold" id="totalGrossFooter">₱0.00</td>
                                    <td class="text-end fw-bold" id="totalWithholdingFooter">₱0.00</td>
                                    <td class="text-end fw-bold" id="totalNetFooter">₱0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" id="printAdaBtn">
                        <i class="bi bi-printer me-1"></i> Print ADA
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('.datatable').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": 8 } // Disable sorting on actions column
                ],
                "order": [[1, 'desc']], // Order by payment date by default (newest first)
                "dom": '<"top"f>rt<"bottom"lip><"clear">',
                "language": {
                    "search": "<i class='bi bi-search me-1'></i>Search:",
                    "paginate": {
                        "previous": "<i class='bi bi-chevron-left'></i>",
                        "next": "<i class='bi bi-chevron-right'></i>"
                    },
                    "lengthMenu": "Show _MENU_ entries",
                    "zeroRecords": "No matching records found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)"
                }
            });
            
            // View ADA Details button click handler
            $('.view-details-btn').on('click', function() {
                const batchId = $(this).data('batch-id');
                loadAdaDetails(batchId);
            });
            
            // Print ADA button click handler
            $('.print-ada-btn').on('click', function() {
                const reference = $(this).data('reference');
                printAdaForm(reference);
            });
            
            // Print Report button click handler
            $('#printReportBtn').on('click', function() {
                window.print();
            });
            
            // Export to Excel button click handler
            $('#exportExcelBtn').on('click', function() {
                exportTableToExcel('ada_payments_report');
            });
            
            // Function to load ADA details into modal
            function loadAdaDetails(batchId) {
                // Show loading spinner
                $('#adaVouchersBody').html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
                
                console.log('Loading ADA details for batch ID:', batchId);
                
                // Fetch ADA details via AJAX
                $.ajax({
                    url: 'back_end/get_ada_details.php',
                    type: 'GET',
                    data: { batch_id: batchId },
                    dataType: 'json',
                    success: function(response) {
                        console.log('ADA details response:', response);
                        
                        if (response.success) {
                            const data = response.data;
                            
                            // Log debug info if available
                            if (data.debug) {
                                console.log('Debug info:', data.debug);
                            }
                            
                            // Log voucher data
                            console.log('Vouchers data:', data.vouchers);
                            
                            // Update header information
                            $('#adaReferenceNo').text(data.reference_no);
                            $('#adaDate').text(data.payment_date_formatted);
                            $('#accountName').text(data.account_name || 'Unknown Account');
                            $('#accountNumber').text(data.account_number || '');
                            
                            // Update voucher count
                            let voucherCount = data.vouchers ? data.vouchers.length : 0;
                            $('#voucherCount').html(`<strong>Vouchers:</strong> <span class="badge bg-secondary">${voucherCount}</span>`);
                            
                            // Update totals
                            $('#totalGross').text('₱' + parseFloat(data.total_gross).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#totalWithholding').text('₱' + parseFloat(data.total_withholding).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#totalNet').text('₱' + parseFloat(data.total_net).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            
                            // Update balance information
                            const previousBalance = parseFloat(data.previous_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            const currentBalance = parseFloat(data.current_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            $('#previousBalance').text('₱' + previousBalance);
                            $('#currentBalance').text('₱' + currentBalance);
                            
                            // Update footer totals
                            $('#totalGrossFooter').text('₱' + parseFloat(data.total_gross).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#totalWithholdingFooter').text('₱' + parseFloat(data.total_withholding).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#totalNetFooter').text('₱' + parseFloat(data.total_net).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            
                            // Populate vouchers table
                            let vouchersHtml = '';
                            let validVouchers = 0;
                            
                            if (data.vouchers && data.vouchers.length > 0) {
                                data.vouchers.forEach(function(voucher) {
                                    // Check if we have valid data for this voucher
                                    if (!voucher.dv_id) {
                                        console.warn('Missing DV ID for voucher:', voucher);
                                        return; // Skip this voucher
                                    }
                                    
                                    validVouchers++;
                                    
                                    // Check if this voucher is part of a merged group
                                    const isMerged = voucher.is_merged === "1" || voucher.is_merged === 1;
                                    const mergeStatus = isMerged 
                                        ? `<span class="badge bg-info" data-bs-toggle="tooltip" title="This DV is part of merged payee group${voucher.merge_name ? ': '+voucher.merge_name : ''}">Merged</span>` 
                                        : `<span class="badge bg-light text-dark border">Individual</span>`;
                                    
                                    // Ensure numeric values
                                    const grossAmount = parseFloat(voucher.gross_amount) || 0;
                                    const withholdingTax = parseFloat(voucher.withholding_tax) || 0;
                                    const netAmount = parseFloat(voucher.net_amount) || 0;
                                    
                                    // Get the DV number with fallback
                                    const dvNo = voucher.dv_no || ('DV ID: ' + voucher.dv_id);
                                    
                                    vouchersHtml += `
                                        <tr ${isMerged ? 'class="table-info bg-opacity-25"' : ''}>
                                            <td>${dvNo}</td>
                                            <td>${voucher.payee_name || 'Unknown'}</td>
                                            <td>${mergeStatus}</td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 250px;" data-bs-toggle="tooltip" title="${voucher.purpose || ''}">
                                                    ${voucher.purpose ? (voucher.purpose.length > 50 ? voucher.purpose.substring(0, 50) + '...' : voucher.purpose) : 'N/A'}
                                                </span>
                                            </td>
                                            <td class="text-end">₱${grossAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end">₱${withholdingTax.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                            <td class="text-end">₱${netAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        </tr>
                                    `;
                                });
                                
                                if (validVouchers === 0) {
                                    vouchersHtml = '<tr><td colspan="7" class="text-center">No valid voucher data found. Data may be incomplete.</td></tr>';
                                }
                            } else {
                                vouchersHtml = '<tr><td colspan="7" class="text-center">No vouchers found for this ADA payment.</td></tr>';
                            }
                            
                            $('#adaVouchersBody').html(vouchersHtml);
                            
                            // Update voucher count in the header
                            $('#voucherCount').html(`<strong>Vouchers:</strong> <span class="badge bg-secondary">${validVouchers}</span>`);
                            
                            // Initialize tooltips
                            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                            tooltipTriggerList.map(function(tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            });
                            
                            // Update Print button data
                            $('#printAdaBtn').data('reference', data.reference_no);
                            
                            // Show the modal
                            $('#adaDetailsModal').modal('show');
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to load ADA details'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load ADA details. Please try again later.'
                        });
                    }
                });
            }
            
            // Function to print ADA form
            function printAdaForm(reference) {
                window.open('LDDAP-APA.html?ref=' + encodeURIComponent(reference), '_blank');
            }
            
            // Print ADA button in modal click handler
            $('#printAdaBtn').on('click', function() {
                const reference = $(this).data('reference');
                printAdaForm(reference);
            });
            
            // Function to export table to Excel
            function exportTableToExcel(fileName) {
                const table = document.querySelector('.datatable');
                const wb = XLSX.utils.table_to_book(table, {sheet: "ADA Payments"});
                XLSX.writeFile(wb, fileName + '.xlsx');
            }
        });
    </script>

    <!-- Add XLSX library for Excel export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
</body>
</html> 