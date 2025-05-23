<?php
include '../DBConnection.php';

// Get all merged payee groups with their DVs
$query = "SELECT mp.*, 
          COUNT(mpi.dv_id) as total_dvs,
          SUM(d.net_amount) as total_amount
          FROM merged_payees mp
          LEFT JOIN merged_payee_items mpi ON mp.merge_id = mpi.merge_id
          LEFT JOIN dv d ON mpi.dv_id = d.dv_id
          GROUP BY mp.merge_id
          ORDER BY mp.created_at DESC";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>View Merged Payees</title>
    <?php include 'includes/header.php'; ?>
    <style>
        .card {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 15px;
        }
        .card-body {
            padding: 2rem;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        .btn-info {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: #fff;
            transition: all 0.2s ease;
        }
        .btn-info:hover {
            background-color: #0bb6d9;
            border-color: #0bb6d9;
            transform: translateY(-1px);
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 15px 15px 0 0;
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 15px 15px;
        }
        .amount-cell {
            font-family: 'Roboto Mono', monospace;
            font-weight: 600;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        .group-name {
            font-weight: 600;
            color: #0d6efd;
        }
        .description-text {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .stats-card {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .stats-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .stats-card p {
            margin-bottom: 0;
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-people-fill me-2"></i>Merged Payee Groups</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Merged Payee Groups</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-diagram-3 me-2"></i>Merged Payee Groups
                                </h5>
                                <div class="stats-card">
                                    <h3><?php echo mysqli_num_rows($result); ?></h3>
                                    <p>Total Merged Groups</p>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-tag me-1"></i>Group Name</th>
                                            <th><i class="bi bi-info-circle me-1"></i>Description</th>
                                            <th><i class="bi bi-person-badge me-1"></i>Payee Type</th>
                                            <th><i class="bi bi-file-earmark-text me-1"></i>Total DVs</th>
                                            <th><i class="bi bi-currency-dollar me-1"></i>Total Amount</th>
                                            <th><i class="bi bi-person me-1"></i>Created By</th>
                                            <th><i class="bi bi-calendar me-1"></i>Created Date</th>
                                            <th><i class="bi bi-gear me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0) : ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                                <tr>
                                                    <td>
                                                        <span class="group-name"><?php echo htmlspecialchars($row['merge_name']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="description-text"><?php echo htmlspecialchars($row['description'] ?? 'No description'); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo ($row['payee_type'] == 'Internal') ? 'success' : 'primary'; ?>">
                                                            <i class="bi bi-<?php echo ($row['payee_type'] == 'Internal') ? 'building' : 'person'; ?> me-1"></i>
                                                            <?php echo $row['payee_type']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <i class="bi bi-file-text me-1"></i>
                                                            <?php echo $row['total_dvs']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="amount-cell text-success">
                                                        <i class="bi bi-currency-dollar me-1"></i>
                                                        <?php echo number_format($row['total_amount'], 2); ?>
                                                    </td>
                                                    <td>
                                                        <i class="bi bi-person-circle me-1"></i>
                                                        <?php echo htmlspecialchars($row['created_by']); ?>
                                                    </td>
                                                    <td>
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewDVsModal<?php echo $row['merge_id']; ?>">
                                                            <i class="bi bi-eye me-1"></i> View DVs
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="8">
                                                    <div class="empty-state">
                                                        <i class="bi bi-inbox"></i>
                                                        <h5>No Merged Payee Groups Found</h5>
                                                        <p>There are no merged payee groups available at this time.</p>
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

    <?php
    // Reset the result set to the beginning
    mysqli_data_seek($result, 0);
    
    // Create modals outside the main table
    while ($row = mysqli_fetch_assoc($result)) : ?>
        <!-- View DVs Modal -->
        <div class="modal fade" id="viewDVsModal<?php echo $row['merge_id']; ?>" tabindex="-1" aria-labelledby="viewDVsModalLabel<?php echo $row['merge_id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="viewMergedPayeesModalLabel">
                            <i class="bi bi-people me-2"></i>View Internal Merged Payees
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                            <div>
                                This view shows all internal payee groups that have been merged for consolidated payment processing.
                            </div>
                        </div>
                        <?php
                        // Get DVs for this merged group
                        $dvs_query = "SELECT d.*, p.payee_name, o.ors_no, o.purpose
                                    FROM merged_payee_items mpi
                                    JOIN dv d ON mpi.dv_id = d.dv_id
                                    JOIN ors o ON d.ors_id = o.ors_id
                                    JOIN payee p ON o.payee_id = p.payee_id
                                    WHERE mpi.merge_id = ?
                                    ORDER BY d.date DESC";
                        $dvs_stmt = mysqli_prepare($connection, $dvs_query);
                        mysqli_stmt_bind_param($dvs_stmt, "i", $row['merge_id']);
                        mysqli_stmt_execute($dvs_stmt);
                        $dvs_result = mysqli_stmt_get_result($dvs_stmt);
                        ?>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-file-text me-1"></i>DV No</th>
                                        <th><i class="bi bi-receipt me-1"></i>ORS No</th>
                                        <th><i class="bi bi-person me-1"></i>Payee</th>
                                        <th><i class="bi bi-info-circle me-1"></i>Purpose</th>
                                        <th><i class="bi bi-currency-dollar me-1"></i>Amount</th>
                                        <th><i class="bi bi-calendar me-1"></i>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($dvs_result) > 0) : ?>
                                        <?php while ($dv = mysqli_fetch_assoc($dvs_result)) : ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-light text-primary">
                                                        <?php echo $dv['dv_no']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo $dv['ors_no']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($dv['payee_name']); ?></td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($dv['purpose']); ?>
                                                    </small>
                                                </td>
                                                <td class="amount-cell text-success">
                                                    <i class="bi bi-currency-dollar me-1"></i>
                                                    <?php echo number_format($dv['net_amount'], 2); ?>
                                                </td>
                                                <td>
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($dv['date'])); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="bi bi-inbox"></i>
                                                    <h5>No DVs Found</h5>
                                                    <p>There are no DVs in this merged group.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>