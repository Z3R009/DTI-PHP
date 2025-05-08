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

// Initialize filter variables
$batch_date = isset($_GET['payment_date']) ? $_GET['payment_date'] : '';
$batch_number = isset($_GET['batch_number']) ? $_GET['batch_number'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build the query with filters
$query = "SELECT 
            a.payment_date,
            a.reference_no,
            a.status,
            SUBSTRING_INDEX(a.reference_no, '-', -1) as batch_number,
            (SELECT COUNT(*) FROM batch_ada_dvs WHERE batch_ada_dvs.batch_id = a.batch_id) as dv_count,
            (SELECT SUM(bad.net_amount) FROM batch_ada_dvs bad WHERE bad.batch_id = a.batch_id) as total_amount
          FROM batch_ada a
          WHERE 1=1";

if (!empty($batch_date)) {
    $query .= " AND DATE(a.payment_date) = '" . mysqli_real_escape_string($connection, $batch_date) . "'";
}

if (!empty($batch_number)) {
    $query .= " AND a.reference_no LIKE '%" . mysqli_real_escape_string($connection, $batch_number) . "%'";
}

if (!empty($status)) {
    $query .= " AND a.status = '" . mysqli_real_escape_string($connection, $status) . "'";
}

$query .= " ORDER BY a.payment_date DESC";

$ada_result = mysqli_query($connection, $query);

// Check for errors
if (!$ada_result) {
    die("Query failed: " . mysqli_error($connection));
}

// Build where clause for total query based on the same filters
$where_clause = "p.payment_type = 'ADA'";
if (!empty($batch_date)) {
    $where_clause .= " AND DATE(p.payment_date) = '" . mysqli_real_escape_string($connection, $batch_date) . "'";
}

if (!empty($batch_number)) {
    $where_clause .= " AND p.reference_no LIKE '%" . mysqli_real_escape_string($connection, $batch_number) . "%'";
}

$total_query = "SELECT COUNT(DISTINCT p.reference_no) as ada_count,
                SUM(p.amount) as ada_amount
                FROM payment p
                WHERE $where_clause";
$total_result = mysqli_query($connection, $total_query);
$totals = mysqli_fetch_assoc($total_result);

if (isset($_GET['regenerate_lddap']) && isset($_GET['reference'])) {
    $reference_no = mysqli_real_escape_string($connection, $_GET['reference']);
    
    // First get the batch ID
    $batch_query = "SELECT batch_id FROM batch_ada WHERE reference_no = ?";
    $batch_stmt = $connection->prepare($batch_query);
    $batch_stmt->bind_param('s', $reference_no);
    $batch_stmt->execute();
    $batch_result = $batch_stmt->get_result();
    
    if ($batch_result->num_rows > 0) {
        $batch_row = $batch_result->fetch_assoc();
        $batch_id = $batch_row['batch_id'];
        
        $payments_query = "SELECT bad.*, d.dv_no, d.net_amount as dv_net, d.vat_amount, d.tax_1_amount, d.tax_2_amount, 
                          bad.net_amount as amount, pa.payee_name, pa.bank_acc_no, o.ors_no, o.purpose, o.notes,
                          p.remarks, p.payment_date
                          FROM batch_ada_dvs bad
                          JOIN dv d ON bad.dv_id = d.dv_id
                          JOIN ors o ON d.ors_id = o.ors_id
                          JOIN payee pa ON o.payee_id = pa.payee_id
                          LEFT JOIN payment p ON p.dv_id = d.dv_id AND p.payment_type = 'ADA'
                          WHERE bad.batch_id = ?
                          ORDER BY pa.payee_name ASC";
        
        $stmt = $connection->prepare($payments_query);
        $stmt->bind_param('i', $batch_id);
        $stmt->execute();
        $payments_result = $stmt->get_result();
        
        if ($payments_result->num_rows > 0) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $ada_numbers = [];
            while ($row = $payments_result->fetch_assoc()) {
                if (!empty($row['ada_no'])) {
                    $ada_numbers[] = $row['ada_no'];
                }
            }
            sort($ada_numbers);
            $ada_no = $reference_no;
            if (!empty($ada_numbers)) {
                $first_ada = $ada_numbers[0];
                $last_ada = $ada_numbers[count($ada_numbers) - 1];
                
                if (count($ada_numbers) > 1 && $first_ada !== $last_ada) {
                    $parts = explode('-', $first_ada);
                    if (count($parts) >= 4) {
                        $fund_code = $parts[0];
                        $month = $parts[1];
                        $year = $parts[count($parts) - 1];
                        $first_series = $parts[2];
                        $last_series = explode('-', $last_ada)[2];
                        
                        $ada_no = "$fund_code-$month-$first_series-$last_series-$year";
                    }
                }else {
                    $ada_no = $first_ada;
                }
            } else {
                $parts = explode('-', $reference_no);
                if (count($parts) >= 4) {
                    $fund_code = $parts[0];
                    $month = $parts[1];
                    $series = $parts[2];
                    $year = $parts[3];
                    $ada_no = "$fund_code-$month-$series-$year";
                }
            }
            
            $_SESSION['lddap_data'] = [
                'reference_no' => $reference_no,
                'ada_no' => $ada_no,
                'payment_date' => '',
                'remarks' => '',
                'dvs' => [],
                'total_gross' => 0,
                'total_withholding' => 0,
                'total_net' => 0,
                'fundCode' => '01101101',
                'bankInfo' => 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81',
                'has_multiple_references' => count($ada_numbers) > 1
            ];
            
            $total_gross = 0;
            $total_withholding = 0;
            $total_net = 0;
            $first_payment = $payments_result->fetch_assoc();
            
            // Get payment date from batch_ada as a fallback
            if (empty($first_payment['payment_date'])) {
                $ada_date_query = "SELECT payment_date, remarks FROM batch_ada WHERE batch_id = ?";
                $ada_date_stmt = $connection->prepare($ada_date_query);
                $ada_date_stmt->bind_param('i', $batch_id);
                $ada_date_stmt->execute();
                $ada_date_result = $ada_date_stmt->get_result();
                $ada_date_row = $ada_date_result->fetch_assoc();
                
                $_SESSION['lddap_data']['payment_date'] = $ada_date_row['payment_date'] ?? '';
                $_SESSION['lddap_data']['remarks'] = $ada_date_row['remarks'] ?? '';
            } else {
                $_SESSION['lddap_data']['payment_date'] = $first_payment['payment_date'];
                $_SESSION['lddap_data']['remarks'] = $first_payment['remarks'] ?? '';
            }
            
            $payments_result->data_seek(0);
            
            while ($row = $payments_result->fetch_assoc()) {
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
                    'net_amount' => $net_amount,
                    'reference_no' => $reference_no
                ];
                
                $total_gross += $gross_amount;
                $total_withholding += $withholding_tax;
                $total_net += $net_amount;
            }
            
            $_SESSION['lddap_data']['total_gross'] = $total_gross;
            $_SESSION['lddap_data']['total_withholding'] = $total_withholding;
            $_SESSION['lddap_data']['total_net'] = $total_net;
            
            function numberToWords($number) {
                $ones = array(
                    0 => "Zero", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 
                    5 => "Five", 6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine", 
                    10 => "Ten", 11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen", 
                    15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen"
                );
                $tens = array(
                    2 => "Twenty", 3 => "Thirty", 4 => "Forty", 5 => "Fifty", 
                    6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
                );
                $hundreds = array(
                    "Hundred", "Thousand", "Million", "Billion", "Trillion", "Quadrillion"
                );
                
                $num = number_format($number, 2, '.', '');
                $num_arr = explode('.', $num);
                $wholenum = $num_arr[0];
                $decnum = $num_arr[1];
                $whole_arr = array_reverse(explode(',', $wholenum));
                krsort($whole_arr);
                $rettext = "";
                
                foreach($whole_arr as $key => $i) {
                    if($i < 20) {
                        $rettext .= $ones[$i];
                    } elseif($i < 100) {
                        $rettext .= $tens[substr($i, 0, 1)];
                        $rettext .= " ".$ones[substr($i, 1, 1)];
                    } else {
                        $rettext .= $ones[substr($i, 0, 1)]." ".$hundreds[0];
                        $tmp = substr($i, 1, 2);
                        if($tmp > 0) {
                            $rettext .= " and ".numberToWords($tmp);
                        }
                    }
                    if($key > 0) {
                        $rettext .= " ".$hundreds[$key]." ";
                    }
                }
                
                if($decnum > 0) {
                    $rettext .= " and ";
                    if($decnum < 20) {
                        $rettext .= $ones[$decnum];
                    } elseif($decnum < 100) {
                        $rettext .= $tens[substr($decnum, 0, 1)];
                        $rettext .= " ".$ones[substr($decnum, 1, 1)];
                    }
                    $rettext .= " Centavos";
                }
                
                return $rettext . " Pesos Only";
            }
            
            $_SESSION['lddap_data']['amountInWords'] = numberToWords($total_net);
            
            header('Location: generate_lddap.php?ref=' . urlencode($reference_no));
            exit();
        }
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
    
    $query = "SELECT * FROM batch_ada WHERE reference_no = '$reference_no'";
    $result = @mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $_SESSION['reference_no'] = $row['reference_no'];
        $_SESSION['payment_date'] = $row['payment_date'];
        $_SESSION['payment_remarks'] = $row['remarks'] ?? '';
        header("Location: generate_lddap.php?ref=" . urlencode($reference_no));
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
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
 <link rel="stylesheet" href="css/table.css">

    <style>
        .btn-group .btn {
            margin-right: 0.25rem;
            border-radius: 4px !important;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
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
        </div>
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
                <!-- Batch Filter Section -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Batch Filter</h5>
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="batch_date" class="form-label">Batch Date</label>
                                    <input type="date" class="form-control" id="batch_date" name="batch_date" value="<?php echo isset($_GET['batch_date']) ? $_GET['batch_date'] : ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="batch_number" class="form-label">Batch Number</label>
                                    <input type="text" class="form-control" id="batch_number" name="batch_number" value="<?php echo isset($_GET['batch_number']) ? $_GET['batch_number'] : ''; ?>" placeholder="Enter batch number">
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All</option>
                                        <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'processed') ? 'selected' : ''; ?>>Processed</option>
                                        <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-search me-1"></i> Search
                                    </button>
                                    <a href="ada_records.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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
                                            <th>Batch Date</th>
                                            <th>Batch No</th>
                                            <th>Reference No</th>
                                            <th>DV Count</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($ada_result)): 
                                            $reference_no = $row['reference_no'];
                                            $modal_id = md5($reference_no);
                                            $status_class = '';
                                            switch($row['status']) {
                                                case 'pending':
                                                    $status_class = 'bg-warning';
                                                    break;
                                                case 'processed':
                                                    $status_class = 'bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $status_class = 'bg-danger';
                                                    break;
                                                default:
                                                    $status_class = 'bg-secondary';
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($row['payment_date'])); ?></td>
                                            <td><span class="badge bg-primary"><?php echo isset($row['batch_number']) ? $row['batch_number'] : substr(strrchr($reference_no, '-'), 1); ?></span></td>
                                            <td><span class="badge bg-light text-primary">LDDAP-ADA No.: <?php echo $reference_no; ?></span></td>
                                            <td><?php echo $row['dv_count']; ?></td>
                                            <td class="text-success fw-bold">PHP <?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
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
                                                    <a href="javascript:void(0)" onclick="openLDDAPForm('<?php echo urlencode($reference_no); ?>')" class="btn btn-sm btn-primary rounded">
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
    mysqli_data_seek($ada_result, 0);
    while ($row = mysqli_fetch_assoc($ada_result)):
        $reference_no = $row['reference_no'];
        $modal_id = md5($reference_no);
        
        // First get the batch_id
        $batch_query = "SELECT batch_id FROM batch_ada WHERE reference_no = ?";
        $batch_stmt = $connection->prepare($batch_query);
        $batch_stmt->bind_param("s", $reference_no);
        $batch_stmt->execute();
        $batch_result = $batch_stmt->get_result();
        $batch_row = $batch_result->fetch_assoc();
        $batch_id = $batch_row['batch_id'];
        
        // Now use batch_id for joins to avoid collation issues
        $detail_query = "SELECT bad.*, d.dv_no, o.ors_no, pa.payee_name, o.purpose, o.notes, 
                          p.remarks, p.payment_date, p.amount
                       FROM batch_ada_dvs bad
                       JOIN dv d ON bad.dv_id = d.dv_id
                       LEFT JOIN payment p ON p.dv_id = d.dv_id 
                       JOIN ors o ON d.ors_id = o.ors_id
                       JOIN payee pa ON o.payee_id = pa.payee_id
                       WHERE bad.batch_id = ?
                       ORDER BY pa.payee_name";
        $detail_stmt = $connection->prepare($detail_query);
        $detail_stmt->bind_param("i", $batch_id);
        $detail_stmt->execute();
        $detail_result = $detail_stmt->get_result();
    ?>
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
                                            <span class="fw-medium ms-2"><?php echo date('F d, Y h:i A', strtotime($row['payment_date'])); ?></span>
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
                    <a href="javascript:void(0)" onclick="openLDDAPForm('<?php echo urlencode($reference_no); ?>')" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i> View LDDAP-ADA Form
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
        try {
            const table = document.querySelector('.datatable');
            if (table) {
                const dataTable = new simpleDatatables.DataTable(table, {
                    perPageSelect: [10, 20, 50, 100],
                    columns: [
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
    
    function openLDDAPForm(referenceNo) {
        window.open('LDDAP-APA.html?ref=' + referenceNo, '_blank');
    }
    </script>
</body>
</html> 