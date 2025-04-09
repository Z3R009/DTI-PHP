<?php
include '../DBConnection.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$check_column_query = "SHOW COLUMNS FROM dv LIKE 'status'";
$column_result = mysqli_query($connection, $check_column_query);
$column_exists = mysqli_num_rows($column_result) > 0;

if ($column_exists) {
    $pending_dv_query = "SELECT COUNT(*) as count FROM dv WHERE status = 'pending_endorsement'";
    $endorsed_dv_query = "SELECT COUNT(*) as count FROM dv WHERE status = 'endorsed'";
} else {
   
    $pending_dv_query = "SELECT COUNT(*) as count FROM dv WHERE chief_accountant IS NULL";
    $endorsed_dv_query = "SELECT COUNT(*) as count FROM dv WHERE chief_accountant IS NOT NULL";
}

$pending_dv_result = mysqli_query($connection, $pending_dv_query);
$pending_dv_count = mysqli_fetch_assoc($pending_dv_result)['count'];

$endorsed_dv_result = mysqli_query($connection, $endorsed_dv_query);
$endorsed_dv_count = mysqli_fetch_assoc($endorsed_dv_result)['count'];
?>


 <main id="main" class="main">
<div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <!-- Pending DVs Card -->
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">Pending DVs</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="ps-3">
                            <h6><?php echo $pending_dv_count; ?></h6>
                            <span class="text-muted small pt-2">Awaiting Endorsement</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Endorsed DVs Card -->
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title">Endorsed DVs</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ps-3">
                            <h6><?php echo $endorsed_dv_count; ?></h6>
                            <span class="text-muted small pt-2">Successfully Endorsed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total DVs Card -->
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card revenue-card">
                <div class="card-body">
                    <h5 class="card-title">Total DVs</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-files"></i>
                        </div>
                        <div class="ps-3">
                            <h6><?php echo $pending_dv_count + $endorsed_dv_count; ?></h6>
                            <span class="text-muted small pt-2">All Disbursement Vouchers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent DVs Table -->
        <div class="col-12">
            <div class="card recent-sales overflow-auto">
                <div class="card-body">
                    <h5 class="card-title">Recent Disbursement Vouchers</h5>
                    <table class="table table-borderless datatable">
                        <thead>
                            <tr>
                                <th scope="col">DV No.</th>
                                <th scope="col">Payee</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_dv_query = "SELECT d.*, p.payee_name, o.purpose 
                                               FROM dv d 
                                               JOIN ors o ON d.ors_id = o.ors_id 
                                               JOIN payee p ON o.payee_id = p.payee_id 
                                               ORDER BY d.date DESC LIMIT 10";
                            $recent_dv_result = mysqli_query($connection, $recent_dv_query);
                            
                            if (mysqli_num_rows($recent_dv_result) > 0) {
                                while ($dv = mysqli_fetch_assoc($recent_dv_result)) {
                                    $status = '';
                                    $status_class = '';
                                    
                                    if ($column_exists) {
                                        if ($dv['status'] == 'pending_endorsement') {
                                            $status = 'Pending';
                                            $status_class = 'warning';
                                        } else if ($dv['status'] == 'endorsed') {
                                            $status = 'Endorsed';
                                            $status_class = 'success';
                                        }
                                    } else {
                                        if ($dv['chief_accountant'] == NULL) {
                                            $status = 'Pending';
                                            $status_class = 'warning';
                                        } else {
                                            $status = 'Endorsed';
                                            $status_class = 'success';
                                        }
                                    }
                                    
                                    echo '<tr>';
                                    echo '<td>' . $dv['dv_no'] . '</td>';
                                    echo '<td>' . $dv['payee_name'] . '</td>';
                                    echo '<td>₱' . number_format($dv['net_amount'], 2) . '</td>';
                                    echo '<td>' . date('M d, Y', strtotime($dv['date'])) . '</td>';
                                    echo '<td><span class="badge bg-' . $status_class . '">' . $status . '</span></td>';
                                    echo '<td>';
                                    if ($status == 'Pending') {
                                        echo '<a href="review_dv.php?id=' . $dv['dv_id'] . '" class="btn btn-sm btn-primary">Review</a>';
                                    } else {
                                        echo '<a href="view_endorsed_dv.php?id=' . $dv['dv_id'] . '" class="btn btn-sm btn-info">View</a>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">No DVs found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

                        </main>
     <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>