<?php
include '../DBConnection.php';

$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// delete
if (isset($_GET['project_id']) && $_GET['confirm'] == 'yes') {
    $project_id = intval($_GET['project_id']);
    $deleteUserSql = "DELETE FROM project WHERE project_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $project_id);

    if ($stmtUser->execute()) {
           header('Location: oo1.php');
        exit();
    } else {
               echo "Error deleting user: " . $connection->error;
    }
} else {
     echo "Invalid request.";
}

//Add users
if (isset($_POST['submit'])) {
    $project_id_query = "SELECT MAX(project_id) as max_id FROM project";
    $project_id_result = mysqli_query($connection, $project_id_query);
    $project_id_row = mysqli_fetch_assoc($project_id_result);
    $project_id = ($project_id_row['max_id'] ?? 0) + 1;  
    $oopap_id = $_POST['oopap_id'];
    $account_id = $_POST['account_id'];
    $allotment = $_POST['allotment'];
    $balances = $_POST['balances'];
    $created_at = $_POST['year']; 
    $sql = "INSERT INTO project (project_id, oopap_id, account_id, allotment, balances, created_at) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iiisss", $project_id, $oopap_id, $account_id, $allotment, $balances, $created_at);

    if ($stmt->execute()) {
        header('Location: oo1.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

$select = mysqli_query(
    $connection,
    "SELECT project.*,
            account_title.account_title,
            account_title.account_code 
            FROM project
            LEFT JOIN account_title ON project.account_id = account_title.account_id
            WHERE oopap_id = 2
            AND YEAR(project.created_at) = $selected_year
            ORDER BY account_title.account_title ASC"
);
        
        $oopap_query = "SELECT description FROM oopap WHERE oopap_id = 2";
        $oopap_result = mysqli_query($connection, $oopap_query);
        $oopap_data = mysqli_fetch_assoc($oopap_result);
        $description = $oopap_data['description'];

        $query_account = "SELECT account_id, account_title, account_code FROM account_title ORDER BY account_title ASC";
        $result_account = $connection->query($query_account);

        $total_allotment_query = "SELECT SUM(allotment) AS total_allotment FROM project WHERE oopap_id = 2 AND YEAR(created_at) = $selected_year";
        $total_allotment_result = mysqli_query($connection, $total_allotment_query);
        $total_allotment = mysqli_fetch_assoc($total_allotment_result)['total_allotment'];

        $update_balances_query = "UPDATE project p 
                                SET p.balances = p.balances - (
                                    SELECT COALESCE(SUM(ors.total_amount), 0) 
                                    FROM obligation_history oh 
                                    JOIN ors ON oh.ors_id = ors.ors_id 
                                    WHERE oh.project_id = p.project_id
                                    AND MONTH(ors.date) = $selected_month
                                    AND YEAR(ors.date) = $selected_year
                                    )
                                    WHERE p.oopap_id = 2 
                                    AND YEAR(p.created_at) = $selected_year";
        mysqli_query($connection, $update_balances_query);

        $total_balances_query = "SELECT SUM(balances) AS total_balances FROM project WHERE oopap_id = 2 AND YEAR(created_at) = $selected_year";
        $total_balances_result = mysqli_query($connection, $total_balances_query);
        $total_balances = mysqli_fetch_assoc($total_balances_result)['total_balances'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>OO1-Exports and Investment Program</title>
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

    <div class="pagetitle page-header d-flex justify-content-between align-items-center">
            <div>
                <h1>OO1-Exports and Investment Program <?php echo date('Y'); ?></h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Status of Fund</a></li>
                        <li class="breadcrumb-item"><a href="index.php">MOOE</a></li>
                        <li class="breadcrumb-item active">OO1-Exports and Investment Program</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center">
            <form method="get" action="gas.php" class="d-flex align-items-center me-3 filter-form">
                    <div class="input-group input-group-sm me-2 <?php echo ($selected_year != date('Y')) ? 'filter-active' : ''; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Select year for total allotment">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <select class="form-select form-select-sm" id="year" name="year" style="width: 80px;">
                            <?php
                            $current_year = date('Y');
                            for ($year = $current_year; $year >= $current_year - 5; $year--) {
                                $selected = ($year == $selected_year) ? 'selected' : '';
                                echo "<option value=\"$year\" $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-group input-group-sm me-2 <?php echo ($selected_month != date('n')) ? 'filter-active' : ''; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Select month for remaining balances">
                  
                        <select class="form-select form-select-sm" id="month" name="month" style="width: 120px;">
                            <?php
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                            foreach ($months as $num => $name) {
                                $selected = ($num == $selected_month) ? 'selected' : '';
                                echo "<option value=\"$num\" $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Apply selected filters">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="gas.php" class="btn btn-sm btn-outline-secondary ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset to current month and year" id="resetFilter">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </form>
                
            </div>
        </div>
        <section class="section dashboard">
         <div class="row mb-4">
            <div class="col-md-6">
                <div class="card summary-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3" style="background-color: rgba(65, 84, 241, 0.1);">
                                <i class="bi bi-cash-stack text-primary fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Total Allotment (Year <?php echo $selected_year; ?>)</h5>
                                <h3 class="card-text">₱<?php echo number_format($total_allotment, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
                <!-- Total Balances Card -->
                <div class="col-md-6">
                <div class="card summary-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3" style="background-color: rgba(46, 202, 106, 0.1);">
                                <i class="bi bi-wallet2 text-success fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title">Remaining Balances (<?php echo $months[$selected_month]; ?> <?php echo $selected_year; ?>)</h5>
                                <h3 class="card-text">₱<?php echo number_format($total_balances, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
            </div>
            <div class="card">
                <div class="card-body p-4">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-octagon me-1"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addUserModal" style="width: 200px; background-color:#023e8a;" data-bs-toggle="tooltip" data-bs-placement="top" title="Add new project/program/activities">
                        <i class="bi bi-plus-circle me-1"></i> Add Project
                    </button>

                </div>
                <br>
                   
                    <div class="table-container">
                        <table class="table datatable table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="30%">
                                        <i class="bi bi-briefcase me-2 text-primary"></i>
                                        Project/Activities/Program
                                    </th>
                                    <th width="15%">
                                        <i class="bi bi-upc me-2 text-primary"></i>
                                        Code
                                    </th>
                                    <th class="text-end" width="15%">
                                        <i class="bi bi-cash me-2 text-primary"></i>
                                        Allotment
                                    </th>
                                    <th class="text-end" width="15%">
                                        <i class="bi bi-wallet2 me-2 text-primary"></i>
                                        Balances
                                    </th>
                                    <th width="15%">
                                        <i class="bi bi-calendar-date me-2 text-primary"></i>
                                        Date
                                    </th>
                                    <th class="text-center" width="10%">
                                        <i class="bi bi-sliders me-2 text-primary"></i>
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box rounded-circle bg-light me-3 p-2">
                                                    <i class="bi bi-folder text-primary"></i>
                                                </div>
                                                <span class="fw-medium"><?php echo htmlspecialchars($row['account_title']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-code blue-badge"><?php echo htmlspecialchars($row['account_code']); ?></span>
                                        </td>
                                        <td class="text-end amount-cell">₱<?php echo htmlspecialchars(number_format($row['allotment'], 2)); ?></td>
                                        <td class="text-end amount-cell">
                                            <span class="badge badge-code green-badge">
                                                ₱<?php echo htmlspecialchars(number_format($row['balances'], 2)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar3 me-2 text-muted"></i>
                                                <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-primary action-btn edit-btn" data-bs-toggle="tooltip" title="Edit project"
                                                data-bs-target="#editModal" data-id="<?php echo $row['project_id']; ?>"
                                                data-account_id="<?php echo htmlspecialchars($row['account_id']); ?>"
                                                data-account_title="<?php echo htmlspecialchars($row['account_title']); ?>"
                                                data-allotment="<?php echo htmlspecialchars($row['allotment']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button type="button" class="btn btn-danger action-btn" data-bs-toggle="tooltip" title="Delete project"
                                                onclick="deleteUser(<?php echo $row['project_id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            </table>
                            <div class="table-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        <i class="bi bi-info-circle me-1"></i> Showing all projects for current fiscal year
                                    </div>
                                    <div>
                                        <span class="fw-medium text-primary">Total Records: <?php echo mysqli_num_rows($select); ?></span>
                                    </div>
                                </div>
                            </div>
                              
                            </div>
                        </div>
                    </div>
                 
                </div>
            </div>

        </section>

    </main>
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Project/Program/Activities</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_oo1.php">
                        <input type="hidden" id="edit_project_id" name="project_id">
                        <input type="hidden" id="edit_account_id" name="edit_account_id">

                        <div class="mb-3">
                            <label for="edit_account_id" class="form-label">Project/Program/Activities</label>
                            <input type="text" class="form-control" id="edit_account_title" name="account_id" required
                                autocomplete="off" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="edit_allotment" class="form-label">Allotment</label>
                            <input type="number" class="form-control" id="edit_allotment" name="allotment" step="0.01"
                                required autocomplete="off">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="update" name="update" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add Project/Program/Activities
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body">
                    <form method="post" id="addUserForm">
                        <div class="mb-3">
                            <input type="hidden" class="form-control" id="oopap_id" name="oopap_id"
                                value="1" readonly required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="account_id" class="form-label">Account Title <span class="text-danger">*</span></label>
                            <select class="form-select" name="account_id" id="account_id" required>
                                <option value="">Select Account</option>
                                <?php
                                while ($row = $result_account->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['account_id']) . "' 
                                data-code='" . htmlspecialchars($row['account_code']) . "'>"
                                        . htmlspecialchars($row['account_title']) . " - " . htmlspecialchars($row['account_code']) . ""
                                        . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="allotment" class="form-label">Allotment <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="allotment" name="allotment"
                                    placeholder="Enter Allotment" required autocomplete="off"
                                    oninput="updateBalances()">
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" class="form-control" id="balances" name="balances"
                                placeholder="Balances" readonly>
                        </div>

                        <div class="form-group mb-3">
                            <label for="year" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="year" name="year" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                                <i class="bi bi-eraser me-1"></i>Clear
                            </button>
                            <button type="submit" id="submit" name="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Project/Program/Activities
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_gas.php">
                        <input type="hidden" id="edit_project_id" name="project_id">
                        <input type="hidden" id="edit_account_id" name="edit_account_id">

                        <div class="mb-3">
                            <label for="edit_account_title" class="form-label">Project/Program/Activities</label>
                            <input type="text" class="form-control" id="edit_account_title" name="account_id" required
                                autocomplete="off" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="edit_allotment" class="form-label">Allotment</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="edit_allotment" name="allotment" step="0.01"
                                    required autocomplete="off">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>
                            <button type="submit" id="update" name="update" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update
                            </button>
                        </div>
                    </form>
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
       
        function clearForm() {
            document.getElementById('addUserForm').reset();
            updateBalances();
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('year');
            const monthSelect = document.getElementById('month');
            const filterForm = yearSelect.closest('form');
            const filterButton = filterForm.querySelector('button[type="submit"]');
            const resetButton = document.getElementById('resetFilter');
            
            function submitFilter() {
                const originalButtonText = filterButton.innerHTML;
                filterButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Applying...';
                filterButton.disabled = true;
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'info',
                    title: 'Applying filters...'
                });
                
                filterForm.submit();
            }
       
            yearSelect.addEventListener('change', submitFilter);
            monthSelect.addEventListener('change', submitFilter);

            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            filterButton.addEventListener('click', function(e) {
                e.preventDefault();
                submitFilter();
            });
            
            if (resetButton) {
                resetButton.addEventListener('click', function(e) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    
                    Toast.fire({
                        icon: 'info',
                        title: 'Resetting filters...'
                    });
                });
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const editButtons = document.querySelectorAll(".edit-btn");

            editButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const id = this.getAttribute("data-id");
                    const account_id = this.getAttribute("data-account_id");
                    const account_title = this.getAttribute("data-account_title");
                    const allotment = this.getAttribute("data-allotment");

                    document.getElementById("edit_project_id").value = id;
                    document.getElementById("edit_account_id").value = account_id;
                    document.getElementById("edit_account_title").value = account_title;
                    document.getElementById("edit_allotment").value = allotment;
                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    
                    Toast.fire({
                        icon: 'info',
                        title: 'Editing project...'
                    });
                });
            });
        });
    </script>
    <script>
        function deleteUser(gasID) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we process your request',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    window.location.href = 'gas.php?project_id=' + gasID + '&confirm=yes';
                }
            });
        }

        function confirmUpdate() {
            event.preventDefault();
            
            Swal.fire({
                title: 'Update Project',
                text: "Are you sure you want to update this project?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait while we process your request',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('editUserForm').submit();
                }
            });
            return false;
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    <script>
        function updateBalances() {
            var allotmentValue = document.getElementById('allotment').value;
            document.getElementById('balances').value = allotmentValue;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        <?php if (isset($_SESSION['success_message'])): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                icon: 'success',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo $_SESSION['error_message']; ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById('tableSearch');
            const tableRows = document.querySelectorAll('.datatable tbody tr');
            
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
            
            const refreshButton = document.getElementById('refreshTable');
            if (refreshButton) {
                refreshButton.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    tableRows.forEach(row => {
                        row.style.display = '';
                    });
                    
                    this.querySelector('i').classList.add('spin-animation');
                    setTimeout(() => {
                        this.querySelector('i').classList.remove('spin-animation');
                    }, 1000);
                });
            }
        });

        
    </script>
    
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .spin-animation {
            animation: spin 1s linear;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500&display=swap');
    </style>

    <!-- Add jQuery and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 on the account_id dropdown
            $('#account_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Search for an account...',
                allowClear: true,
                dropdownParent: $('#addUserModal')
            });
        });
    </script>

</body>

</html>