<?php
include '../DBConnection.php';

// Get filter parameters
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// delete
if (isset($_GET['project_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $project_id = intval($_GET['project_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM project WHERE project_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $project_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: gas.php');
        exit();
    } else {
        // Handle error if either query fails
        echo "Error deleting user: " . $connection->error;
    }
} else {
    // Handle invalid request
    echo "Invalid request.";
}

//Add users
if (isset($_POST['submit'])) {
    // Generate a new project_id
    $project_id_query = "SELECT MAX(project_id) as max_id FROM project";
    $project_id_result = mysqli_query($connection, $project_id_query);
    $project_id_row = mysqli_fetch_assoc($project_id_result);
    $project_id = ($project_id_row['max_id'] ?? 0) + 1;  
    $oopap_id = $_POST['oopap_id'];
    $account_id = $_POST['account_id'];
    $allotment = $_POST['allotment'];
    $balances = $_POST['balances'];
    $created_at = $_POST['year']; // Use the selected date from the form

    // Validate required fields
    if (empty($account_id) || empty($allotment)) {
        $error_message = "Please fill in all required fields";
    } else {
        $sql = "INSERT INTO project (project_id, oopap_id, account_id, allotment, balances, created_at) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("iiisss", $project_id, $oopap_id, $account_id, $allotment, $balances, $created_at);

        if ($stmt->execute()) {
            header('Location: gas.php');
            exit();
        } else {
            $error_message = "Error saving data: " . $stmt->error;
        }
    }
}

// retrieve - Filter by year only for display
$select = mysqli_query(
    $connection,
    "SELECT project.*,
            account_title.account_title,
            account_title.account_code 
            FROM project
            LEFT JOIN account_title ON project.account_id = account_title.account_id
            WHERE oopap_id = 1 
            AND YEAR(project.created_at) = $selected_year
            ORDER BY account_title.account_title ASC"
);

// account name
$query_account = "SELECT account_id, account_title, account_code FROM account_title ORDER BY account_title ASC";
$result_account = $connection->query($query_account);

// Fetch total allotment for the selected year
$total_allotment_query = "SELECT SUM(allotment) AS total_allotment FROM project WHERE oopap_id = 1 AND YEAR(created_at) = $selected_year";
$total_allotment_result = mysqli_query($connection, $total_allotment_query);
$total_allotment = mysqli_fetch_assoc($total_allotment_result)['total_allotment'];

// Calculate balances based on allotment and ORS total_amount for the selected month
$update_balances_query = "UPDATE project p 
                         SET p.balances = p.allotment - (
                             SELECT COALESCE(SUM(ors.total_amount), 0) 
                             FROM obligation_history oh 
                             JOIN ors ON oh.ors_id = ors.ors_id 
                             WHERE oh.project_id = p.project_id
                             AND MONTH(ors.date) = $selected_month
                             AND YEAR(ors.date) = $selected_year
                         )
                         WHERE p.oopap_id = 1 
                         AND YEAR(p.created_at) = $selected_year";
mysqli_query($connection, $update_balances_query);

// Fetch total balances for the selected month and year
$total_balances_query = "SELECT SUM(balances) AS total_balances FROM project WHERE oopap_id = 1 AND YEAR(created_at) = $selected_year";
$total_balances_result = mysqli_query($connection, $total_balances_query);
$total_balances = mysqli_fetch_assoc($total_balances_result)['total_balances'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>GAS Management - Budget System</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

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
            border-left: 5px solid #4154f1;
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
            margin-bottom: 20px; /* Add bottom margin */
        }

        .datatable {
            margin-bottom: 0; /* Remove table bottom margin inside container */
        }
        .page-header {
            border-bottom: 2px solid #4154f1;
            margin-bottom: 25px;
            padding-bottom: 15px;
        }
        
        .amount-display {
            font-weight: 700;
            color: #012970;
        }
        
        /* Enhanced Table Styling */
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
            background-color: #4154f1 !important;
            border-color: #4154f1 !important;
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
            color: #4154f1;
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
            background: linear-gradient(135deg, #4154f1 0%, #5464fd 100%);
        }
        
        .table-meta-container {
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* .table-search {
            max-width: 300px;
        } */
    </style>
</head>

<body>

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle page-header d-flex justify-content-between align-items-center">
            <div>
                <h1>General Administrative Support (GAS) <?php echo date('Y'); ?></h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">GAS</li>
                    </ol>
                </nav>
            </div>
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle me-1"></i> Add Project
            </button>
        </div>

        <section class="section dashboard">
            <!-- Filter Form -->
            <div class="card mb-3">
            <div class="card-body">
                    <h5 class="card-title mb-3">Filter by Month and Year</h5> <!-- Added margin bottom -->
                    <form method="get" action="gas.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year">
                                <?php
                                $current_year = date('Y');
                                for ($year = $current_year; $year >= $current_year - 5; $year--) {
                                    $selected = ($year == $selected_year) ? 'selected' : '';
                                    echo "<option value=\"$year\" $selected>$year</option>";
                                }
                                ?>
                            </select>
                            <small class="text-muted">Shows total allotment for the selected year</small>
                        </div>
                        <div class="col-md-4">
                            <label for="month" class="form-label">Month (Remaining Balances)</label>
                            <select class="form-select" id="month" name="month">
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
                            <small class="text-muted">Shows remaining balances for the selected month</small>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>

             <!-- Cards Row -->
<div class="row mb-4">
    <!-- Total Allotment Card -->
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

            <div class="card">
                <div class="card-body p-4">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-octagon me-1"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <h5 class="card-title">Projects/Programs/Activities List</h5>

                    <!-- Enhanced Table with search and filters -->
                    <div class="table-meta-container mb-3">
                        <!-- <div class="table-search">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="tableSearch" class="form-control" placeholder="Search projects...">
                            </div>
                        </div> -->
                        <div>
                            <!-- <button class="btn btn-outline-primary btn-sm" id="refreshTable">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button> -->
                        </div>
                    </div>
                    
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

    </main><!-- End #main -->


    <!-- Modal for Add User Form -->
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

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <!-- clear -->
    <script>
        // Function to clear form
        function clearForm() {
            document.getElementById('addUserForm').reset();
            updateBalances();
        }
    </script>

    <!-- show update -->
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
            
            // Open the modal
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        });
    });
});
    </script>

    <!-- delete with confirmation -->
    <script>
        function deleteUser(gasID) {
            if (confirm("Are you sure you want to delete this GAS?")) {
                window.location.href = 'delete_gas.php?project_id=' + gasID + '&confirm=yes';
            }
        }
    </script>

    <!-- tooltip initialization -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <!-- balances calculation -->
    <script>
        function updateBalances() {
            var allotmentValue = document.getElementById('allotment').value;
            document.getElementById('balances').value = allotmentValue;
        }
    </script>

    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <?php if (isset($_SESSION['success_message'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo $_SESSION['success_message']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php unset($_SESSION['success_message']); ?>
    </script>
    <?php endif; ?>

    <!-- Enhanced table search functionality -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Initialize search functionality
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
            
            // Refresh button functionality
            const refreshButton = document.getElementById('refreshTable');
            if (refreshButton) {
                refreshButton.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    tableRows.forEach(row => {
                        row.style.display = '';
                    });
                    
                    // Show refresh animation
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
        
        /* Add Google Fonts for monospace font */
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500&display=swap');
    </style>

</body>

</html>