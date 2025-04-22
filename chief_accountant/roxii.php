<?php
include '../DBConnection.php';

$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

//Delete
if (isset($_GET['draft_id']) && $_GET['confirm'] == 'yes') {
    $draft_id = intval($_GET['draft_id']);

    $deleteUserSql = "DELETE FROM draft_project WHERE draft_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $draft_id);

    if ($stmtUser->execute()) {
        $_SESSION['success_message'] = "Draft Project deleted successfully!";
        header('Location: roxii.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Error deleting Draft project: " . $connection->error;
        header('Location: roxii.php');
        exit();
    }
}

//Add
if (isset($_POST['submit'])) {
    $draft_id_query = "SELECT MAX(draft_id) as max_id FROM draft_project";
    $draft_id_result = mysqli_query($connection, $draft_id_query);
    $draft_id_row = mysqli_fetch_assoc($draft_id_result);
    $draft_id = ($draft_id_row['max_id'] ?? 0) + 1;
    $account_id = $_POST['account_id'];
    $payee = $_POST['reference'];
    $cash_allotment = $_POST['allotment'];
    $created_at = $_POST['year'];

    if (empty($payee) || empty($cash_allotment) || empty($created_at)) {
        $error_message = "Please fill in all required fields";
    } else {
        $sql = "INSERT INTO draft_project (draft_id, account_id, payee, cash_allotment, balances, created_at) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $balances = $cash_allotment; // Initial balance equals cash allotment
        $stmt->bind_param("iisdss", $draft_id, $account_id, $payee, $cash_allotment, $balances, $created_at);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Draft Project added successfully!";
            header('Location: roxii.php');
            exit();
        } else {
            $error_message = "Error saving data: " . $stmt->error;
        }
    }
}

// retrieve
$select = mysqli_query(
    $connection,
    "SELECT dp.*,
            an.account_name,
            an.account_number,
            an.type
     FROM draft_project dp
     LEFT JOIN account_name an ON dp.account_id = an.account_id
     WHERE dp.account_id = 1
     AND MONTH(dp.created_at) = $selected_month
     AND YEAR(dp.created_at) = $selected_year
     ORDER BY an.account_name ASC"
);

// Calculate beginning balance (previous month's remaining balance)
$prev_month = $selected_month - 1;
$prev_year = $selected_year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year--;
}

// Determine current quarter and previous quarter
$current_quarter = ceil($selected_month / 3);
$prev_quarter = ceil($prev_month / 3);

// Get beginning balance from previous month if in same quarter
$beginning_balance = 0;
if ($current_quarter == $prev_quarter) {
    $prev_balance_query = "SELECT SUM(balances) as prev_balance 
                          FROM draft_project 
                          WHERE account_id = 1 
                          AND MONTH(created_at) = $prev_month 
                          AND YEAR(created_at) = $prev_year";
    $prev_balance_result = mysqli_query($connection, $prev_balance_query);
    $prev_balance_row = mysqli_fetch_assoc($prev_balance_result);
    $beginning_balance = $prev_balance_row['prev_balance'] ?? 0;
}

// Get total cash allotment for current month
$total_Cashallotment_query = "SELECT SUM(cash_allotment) AS total_Cashallotment 
                             FROM draft_project 
                             WHERE account_id = 1
                             AND MONTH(created_at) = $selected_month 
                             AND YEAR(created_at) = $selected_year";
$total_Cashallotment_result = mysqli_query($connection, $total_Cashallotment_query);
$total_Cashallotment = mysqli_fetch_assoc($total_Cashallotment_result)['total_Cashallotment'] ?? 0;

// Add beginning balance to total cash allotment
$total_Cashallotment += $beginning_balance;


// Get total balances for current month
$total_balances_query = "SELECT SUM(balances) AS total_balances 
                        FROM draft_project 
                        WHERE account_id = 1 
                        AND MONTH(created_at) = $selected_month 
                        AND YEAR(created_at) = $selected_year";
$total_balances_result = mysqli_query($connection, $total_balances_query);
$total_balances = mysqli_fetch_assoc($total_balances_result)['total_balances'] ?? 0;

// Add beginning balance to total balances
$query_account = "SELECT account_id, account_name, account_number, type 
                 FROM account_name 
                 WHERE account_id = 1 
                 ORDER BY account_name ASC";
$result_account = $connection->query($query_account);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>DTI RO XI</title>
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

    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">

    <!-- Add Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

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

        .datatable-filter label,
        .datatable-info,
        .datatable-pagination {
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .filter-form .input-group {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .filter-form .input-group:hover {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        .button {
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

        button:hover {
            background-color: #0077b6 !important;
        }

        .btn-group-lg>.btn,
        .btn-lg {
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
</head>

<body>

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle page-header d-flex justify-content-between align-items-center">
            <div>
                <h1>DTI RO XI <?php echo date('Y'); ?></h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php">DTI RO XI</a></li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center">

                <form method="get" action="roxii.php" class="d-flex align-items-center me-3 filter-form">
                    <div class="input-group input-group-sm me-2 <?php echo ($selected_year != date('Y')) ? 'filter-active' : ''; ?>"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Select year for total allotment">
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
                    <div class="input-group input-group-sm me-2 <?php echo ($selected_month != date('n')) ? 'filter-active' : ''; ?>"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Select month for remaining balances">

                        <select class="form-select form-select-sm" id="month" name="month" style="width: 120px;">
                            <?php
                            $months = [
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December'
                            ];
                            foreach ($months as $num => $name) {
                                $selected = ($num == $selected_month) ? 'selected' : '';
                                echo "<option value=\"$num\" $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Apply selected filters">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="roxii.php" class="btn btn-sm btn-outline-secondary ms-2" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Reset to current month and year" id="resetFilter">
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
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="background-color: rgba(65, 84, 241, 0.1);">
                                    <i class="bi bi-cash-stack text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Beginning Balance 
                                        (<?php
                                        $previous_month = $selected_month - 1;

                                        if ($previous_month < 1) {
                                            $previous_month = 12;
                                        }

                                        echo $months[$previous_month];
                                        ?>)
                                        </h5>
                                    <h3 class="card-text">₱<?php echo number_format($beginning_balance, 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="background-color: rgba(65, 84, 241, 0.1);">
                                    <i class="bi bi-cash-stack text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Total Cash Allotment (<?php echo $months[$selected_month]; ?>
                                        <?php echo $selected_year; ?>)</h5>
                                    <h3 class="card-text">₱<?php echo number_format($total_Cashallotment, 2); ?></h3>
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
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="background-color: rgba(46, 202, 106, 0.1);">
                                    <i class="bi bi-wallet2 text-success fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Remaining Balances (<?php echo $months[$selected_month]; ?>
                                        <?php echo $selected_year; ?>)</h5>
                                    <h3 class="card-text">₱<?php echo number_format($total_balances + $beginning_balance, 2); ?></h3>
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
                        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal"
                            data-bs-target="#addUserModal" style="width: 200px; background-color:#023e8a;"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Add new project/program/activities">
                            <i class="bi bi-plus-circle me-1"></i> Add Cash Allotment
                        </button>
                    </div>

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
                                        Reference
                                    </th>
                                    <th width="30%">
                                        <i class="bi bi-briefcase me-2 text-primary"></i>
                                        Account Name
                                    </th>
                                    <th width="15%">
                                        <i class="bi bi-upc me-2 text-primary"></i>
                                        Account Number
                                    </th>
                                    <th class="text-end" width="15%">
                                        <i class="bi bi-cash me-2 text-primary"></i>
                                        Cash Allotment
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
                                                <span
                                                    class="fw-medium"><?php echo htmlspecialchars($row['payee']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box rounded-circle bg-light me-3 p-2">
                                                    <i class="bi bi-folder text-primary"></i>
                                                </div>
                                                <span
                                                    class="fw-medium"><?php echo htmlspecialchars($row['account_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-code blue-badge"><?php echo htmlspecialchars($row['account_number']); ?></span>
                                        </td>
                                        <td class="text-end amount-cell">
                                            ₱<?php echo htmlspecialchars(number_format($row['cash_allotment'], 2)); ?></td>
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
                                            <button type="button" class="btn btn-primary action-btn edit-btn"
                                                data-bs-toggle="tooltip" title="Edit project" data-bs-target="#editModal"
                                                data-id="<?php echo $row['draft_id']; ?>"
                                                data-payee="<?php echo htmlspecialchars($row['payee']); ?>"
                                                data-account_id="<?php echo htmlspecialchars($row['account_id']); ?>"
                                                data-account_title="<?php echo htmlspecialchars($row['account_name']); ?>"
                                                data-allotment="<?php echo htmlspecialchars($row['cash_allotment']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button type="button" class="btn btn-danger action-btn" data-bs-toggle="tooltip"
                                                title="Delete project"
                                                onclick="deleteUser(<?php echo $row['draft_id']; ?>)">
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
                                    <i class="bi bi-info-circle me-1"></i> Showing all allotment for current month
                                </div>
                                <div>
                                    <span class="fw-medium text-primary">Total Records:
                                        <?php echo mysqli_num_rows($select); ?></span>
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

    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addUserModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Acount Name
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="addUserForm">
                        <div class="mb-3">
                            <input type="hidden" class="form-control" id="account_id" name="account_id" value="1"
                                readonly required>
                        </div>

                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reference" name="reference" required>
                        </div>

                        <div class="mb-3">
                            <label for="account_display" class="form-label">Account Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="account_display"
                                value="DTI RO XI" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="allotment" class="form-label">Cash Allotment <span
                                    class="text-danger">*</span></label>
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
                            <input type="date" class="form-control" id="year" name="year" required
                                value="<?php echo date('Y-m-d'); ?>">
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
                        <i class="bi bi-pencil-square me-2"></i>Edit Draft
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_draft.php">
                        <input type="hidden" id="edit_draft_id" name="draft_id">
                        <input type="hidden" id="edit_account_id" name="account_id">

                        <!-- Add this hidden redirect field -->
                        <input type="hidden" name="redirect" value="roxii.php"> <!-- change value to current page -->

                        <div class="mb-3">
                            <label for="edit_payee" class="form-label">Reference</label>
                            <input type="text" class="form-control" id="edit_payee" name="payee" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_account_title" class="form-label">Account Name</label>
                            <input type="text" class="form-control" id="edit_account_title" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="edit_allotment" class="form-label">Cash Allotment</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="edit_allotment" name="cash_allotment"
                                    step="0.01" required autocomplete="off">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>
                            <button type="submit" name="update" class="btn btn-primary"
                                onclick="return confirmUpdate()">
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
        document.addEventListener('DOMContentLoaded', function () {
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

            filterButton.addEventListener('click', function (e) {
                e.preventDefault();
                submitFilter();
            });

            if (resetButton) {
                resetButton.addEventListener('click', function (e) {
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
                    const payee = this.getAttribute("data-payee");
                    const account_id = this.getAttribute("data-account_id");
                    const account_title = this.getAttribute("data-account_title");
                    const allotment = this.getAttribute("data-allotment");

                    document.getElementById("edit_draft_id").value = id;
                    document.getElementById("edit_payee").value = payee;
                    document.getElementById("edit_account_id").value = account_id;
                    document.getElementById("edit_account_title").value = account_title;
                    document.getElementById("edit_allotment").value = allotment;

                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                });
            });
        });
    </script>

    <script>
        function deleteUser(draft_id) {
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
                    window.location.href = 'roxii.php?draft_id=' + draft_id + '&confirm=yes';
                }
            });
        }

        function confirmUpdate() {
            event.preventDefault();

            Swal.fire({
                title: 'Update Draft',
                text: "Are you sure you want to update this draft?",
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
                searchInput.addEventListener('keyup', function () {
                    const searchTerm = this.value.toLowerCase();

                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            const refreshButton = document.getElementById('refreshTable');
            if (refreshButton) {
                refreshButton.addEventListener('click', function () {
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .spin-animation {
            animation: spin 1s linear;
        }

        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500&display=swap');
    </style>

    <!-- Add jQuery and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>

</html>