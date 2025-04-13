<?php
include '../DBConnection.php';



$check_column_query = "SHOW COLUMNS FROM dv LIKE 'status'";
$column_result = mysqli_query($connection, $check_column_query);

if (!$column_result) {
    die("Query failed: " . mysqli_error($connection));
}

$column_exists = mysqli_num_rows($column_result) > 0;

if ($column_exists) {
    $where_clause = "WHERE dv.status = 'endorsed'";
} else {
    $where_clause = "WHERE dv.chief_accountant IS NOT NULL";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - NiceAdmin Bootstrap Template</title>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
</head>
<style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .summary-card {
            border-left: 5px solid #023e8a;
            background: linear-gradient(to right, #f6f9ff 0%, #ffffff 100%);
        }
        
        .card-title {
            color: #012970;
            font-weight: 600;
        }
        
        .action-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 3px;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px; 
        }

        .datatable {
            margin-bottom: 0; 
        }
        .page-header {
            border-bottom: 2px solid #023e8a;
            margin-bottom: 25px;
            padding-bottom: 15px;
        }
        
        .amount-display {
            font-weight: 700;
            color: #012970;
        }
        
        .datatable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .datatable thead th {
            background-color: #f6f9ff;
            color: #012970;
            font-weight: 600;
            padding: 15px;
            border-bottom: 2px solid #e0e7ff;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .datatable tbody tr {
            transition: all 0.2s;
        }
        
        .datatable tbody tr:hover {
            background-color: #f8f9fd;
            transform: scale(1.005);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            z-index: 5;
            position: relative;
        }
        
        .datatable tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .datatable-filter label, .datatable-info, .datatable-pagination {
            font-size: 0.9rem;
            color: #4b5563;
        }
        
        .datatable-pagination .active a {
            background-color: #023e8a !important;
            border-color: #023e8a !important;
        }
        
        .badge-code {
            font-size: 0.85rem;
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 30px;
            letter-spacing: 0.5px;
            display: inline-block;
            white-space: nowrap;
        }
        
        .amount-cell {
            font-family: 'Roboto Mono', monospace;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .amount-cell:hover {
            color: #023e8a;
        }
        
        .table-footer {
            background-color: #f6f9ff;
            padding: 15px;
            border-top: 2px solid #e0e7ff;
            margin-top: 5px;
            border-radius: 0 0 10px 10px;
        }
        
        .green-badge {
            background: linear-gradient(135deg, #2ec869 0%, #33d574 100%);
        }
        
        .blue-badge {
            background: linear-gradient(135deg, #023e8a 0%, #0077b6 100%);
        }
        
        .table-meta-container {
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
     
        .input-group-sm .input-group-text {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .input-group-sm .form-select {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .filter-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 8px 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .filter-form .input-group {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }
        
        .filter-form .input-group:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .filter-form .input-group-text {
            background-color: #f1f3f5;
            border: 1px solid #e9ecef;
            color: #6c757d;
        }
        
        .filter-form .form-select {
            border: 1px solid #e9ecef;
            background-color: #fff;
            cursor: pointer;
        }
        
        .filter-form .form-select:focus {
            border-color: #023e8a;
            box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.25);
        }
        
        .filter-form .btn-outline-primary {
            border-color: #023e8a;
            color: #023e8a;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .filter-form .btn-outline-primary:hover {
            background-color: #4154f1;
            color: #fff;
            box-shadow: 0 2px 5px rgba(65, 84, 241, 0.3);
        }
        
        .filter-active {
            position: relative;
        }
        
        .filter-active::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background-color: #4154f1;
            border-radius: 50%;
            border: 2px solid #fff;
        }
        .button{
            background-color: #023e8a !important;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
        button:hover{
            background-color: #0077b6 !important;
        }
        .btn-group-lg>.btn, .btn-lg {
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.3rem;
          
        }
        @media (max-width: 768px) {
            .pagetitle .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            .pagetitle .d-flex form {
                margin-bottom: 10px;
            }
            
            .pagetitle .btn-lg {
                width: 100%;
            }
            
            .filter-form {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
<body>

<?php include "Includes/header.php"; ?>
<?php include "Includes/sidebar.php"; ?>

 <main id="main" class="main">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Endorsed Disbursement Vouchers</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="endorsedDvTable">
                            <thead>
                                <tr>
                                    <th>DV No.</th>
                                    <th>Date</th>
                                    <th>Payee</th>
                                    <th>Amount</th>
                                    <th>Purpose</th>
                                    <th>Notes</th>
                                    <th>Endorsement Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT dv.*, payee.payee_name, ors.purpose, ors.notes 
                                         FROM dv 
                                         LEFT JOIN ors ON dv.ors_id = ors.ors_id
                                         LEFT JOIN payee ON ors.payee_id = payee.payee_id
                                         $where_clause
                                         ORDER BY dv.date DESC";
                                $result = mysqli_query($connection, $query);
                                
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['dv_no']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['payee_name']) . "</td>";
                                    echo "<td>₱" . number_format($row['net_amount'], 2) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['purpose']) .  "</td>";
                                    echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                                    echo "<td><span class='badge bg-success'>Endorsed</span></td>";
                                    echo "<td>
                                            <a href='view_endorsed_dv.php?id=" . $row['dv_id'] . "' class='btn btn-info btn-sm'>
                                                <i class='bi bi-eye'></i> View
                                            </a>
                                            <a href='print_dv.php?id=" . $row['dv_id'] . "' class='btn btn-secondary btn-sm' target='_blank'>
                                                <i class='bi bi-printer'></i> Print
                                            </a>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <script src="../NiceAdmin/assets/js/main.js"></script>

<script>
$(document).ready(function() {
    $('#endorsedDvTable').DataTable({
        "order": [[1, "desc"]],
        "pageLength": 10,
        "language": {
            "search": "Search DVs:"
        }
    });
});
</script>

<script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    </body>

</html>