<?php
include '../DBConnection.php';
session_start();

// Function to safely create tables if they don't exist
function ensureAllotmentTables($connection) {
    try {
        // Check if allotment table exists
        $table_check = $connection->query("SHOW TABLES LIKE 'allotment'");
        $table_exists = $table_check && $table_check->num_rows > 0;
        
        if (!$table_exists) {
            // Create allotment table
            $create_allotment = "CREATE TABLE IF NOT EXISTS allotment (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fiscal_year INT NOT NULL,
                program_id INT,
                description VARCHAR(255) NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                date_created DATE NOT NULL,
                created_by VARCHAR(100) NOT NULL,
                status ENUM('Active', 'Inactive') DEFAULT 'Active',
                remarks TEXT,
                INDEX (fiscal_year),
                INDEX (program_id)
            )";
            
            // Create program table if reference is needed
            $create_program = "CREATE TABLE IF NOT EXISTS program (
                program_id INT AUTO_INCREMENT PRIMARY KEY,
                program_code VARCHAR(50) NOT NULL,
                program_name VARCHAR(255) NOT NULL,
                description TEXT,
                status ENUM('Active', 'Inactive') DEFAULT 'Active'
            )";
            
            // Execute the queries
            $connection->query($create_program);
            if ($connection->query($create_allotment)) {
                return true;
            }
            
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error setting up allotment tables: " . $e->getMessage());
        return false;
    }
}

// Initialize tables
$tables_initialized = ensureAllotmentTables($connection);

// Handle allotment addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_allotment'])) {
    if ($tables_initialized) {
        $fiscal_year = $_POST['fiscal_year'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $program_id = !empty($_POST['program_id']) ? $_POST['program_id'] : NULL;
        $date_created = date('Y-m-d');
        $created_by = 'Admin'; // In a real app, this would be the logged-in user
        $remarks = $_POST['remarks'];
        
        $insert_query = "INSERT INTO allotment (fiscal_year, program_id, description, amount, date_created, created_by, remarks) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $connection->prepare($insert_query);
        $stmt->bind_param("iisdsss", $fiscal_year, $program_id, $description, $amount, $date_created, $created_by, $remarks);
        
        if ($stmt->execute()) {
            $success_message = "Allotment added successfully.";
        } else {
            $error_message = "Error adding allotment: " . $stmt->error;
        }
    } else {
        $error_message = "Database tables not initialized.";
    }
}

// Handle allotment editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_allotment'])) {
    if ($tables_initialized) {
        $allotment_id = $_POST['allotment_id'];
        $fiscal_year = $_POST['fiscal_year'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $program_id = !empty($_POST['program_id']) ? $_POST['program_id'] : NULL;
        $status = $_POST['status'];
        $remarks = $_POST['remarks'];
        
        $update_query = "UPDATE allotment 
                         SET fiscal_year = ?, program_id = ?, description = ?, amount = ?, status = ?, remarks = ? 
                         WHERE id = ?";
        
        $stmt = $connection->prepare($update_query);
        $stmt->bind_param("iisdisi", $fiscal_year, $program_id, $description, $amount, $status, $remarks, $allotment_id);
        
        if ($stmt->execute()) {
            $success_message = "Allotment updated successfully.";
        } else {
            $error_message = "Error updating allotment: " . $stmt->error;
        }
    } else {
        $error_message = "Database tables not initialized.";
    }
}

// Handle allotment deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if ($tables_initialized) {
        $allotment_id = $_GET['delete'];
        
        // Check if the allotment is used in any obligations
        $check_query = "SELECT COUNT(*) as count FROM ors WHERE allotment_id = ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("i", $allotment_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_data = $check_result->fetch_assoc();
        
        if ($check_data['count'] > 0) {
            $error_message = "Cannot delete allotment: It is associated with one or more obligations.";
        } else {
            // Safe to delete
            $delete_query = "DELETE FROM allotment WHERE id = ?";
            $delete_stmt = $connection->prepare($delete_query);
            $delete_stmt->bind_param("i", $allotment_id);
            
            if ($delete_stmt->execute()) {
                $success_message = "Allotment deleted successfully.";
            } else {
                $error_message = "Error deleting allotment: " . $delete_stmt->error;
            }
        }
    } else {
        $error_message = "Database tables not initialized.";
    }
}

// Fetch programs for dropdown
$programs = [];
try {
    $program_query = "SELECT program_id, program_name FROM program WHERE status = 'Active' ORDER BY program_name";
    $program_result = $connection->query($program_query);
    
    if ($program_result && $program_result->num_rows > 0) {
        while ($row = $program_result->fetch_assoc()) {
            $programs[] = $row;
        }
    }
} catch (Exception $e) {
    // Silently handle program fetch errors
}

// Get current fiscal year
$current_year = date('Y');

// Fetch allotments
$allotments = [];
$total_allotment = 0;
try {
    $allotment_query = "SELECT a.*, p.program_name 
                       FROM allotment a 
                       LEFT JOIN program p ON a.program_id = p.program_id 
                       ORDER BY a.fiscal_year DESC, a.date_created DESC";
    $allotment_result = $connection->query($allotment_query);
    
    if ($allotment_result && $allotment_result->num_rows > 0) {
        while ($row = $allotment_result->fetch_assoc()) {
            $allotments[] = $row;
            if ($row['status'] == 'Active' && $row['fiscal_year'] == $current_year) {
                $total_allotment += $row['amount'];
            }
        }
    }
} catch (Exception $e) {
    // Silently handle allotment fetch errors
}

// Fetch allotment for editing if ID is provided
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $edit_id = $_GET['edit'];
        $edit_query = "SELECT * FROM allotment WHERE id = ?";
        $edit_stmt = $connection->prepare($edit_query);
        $edit_stmt->bind_param("i", $edit_id);
        $edit_stmt->execute();
        $edit_result = $edit_stmt->get_result();
        
        if ($edit_result && $edit_result->num_rows > 0) {
            $edit_data = $edit_result->fetch_assoc();
        }
    } catch (Exception $e) {
        // Silently handle edit fetch errors
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Allotment Management - Administrator</title>
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
</head>

<body>
    <?php include "includes/header.php"; ?>
    <?php include "includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Allotment Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Allotment Management</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-octagon me-1"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!$tables_initialized): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Database setup required</strong>
                    <p>The allotment tables could not be created automatically. Please check your database configuration and ensure the database user has sufficient privileges.</p>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $edit_data ? 'Edit Allotment' : 'Add New Allotment'; ?></h5>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <?php if ($edit_data): ?>
                                    <input type="hidden" name="allotment_id" value="<?php echo $edit_data['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row mb-3">
                                    <label for="fiscal_year" class="col-sm-4 col-form-label">Fiscal Year</label>
                                    <div class="col-sm-8">
                                        <select class="form-select" id="fiscal_year" name="fiscal_year" required>
                                            <?php for ($year = $current_year + 2; $year >= $current_year - 5; $year--): ?>
                                                <option value="<?php echo $year; ?>" <?php if (($edit_data && $edit_data['fiscal_year'] == $year) || (!$edit_data && $year == $current_year)) echo 'selected'; ?>>
                                                    <?php echo $year; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="program_id" class="col-sm-4 col-form-label">Program</label>
                                    <div class="col-sm-8">
                                        <select class="form-select" id="program_id" name="program_id">
                                            <option value="">-- Select Program --</option>
                                            <?php foreach ($programs as $program): ?>
                                                <option value="<?php echo $program['program_id']; ?>" <?php if ($edit_data && $edit_data['program_id'] == $program['program_id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="description" class="col-sm-4 col-form-label">Description</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="description" name="description" value="<?php echo $edit_data ? htmlspecialchars($edit_data['description']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="amount" class="col-sm-4 col-form-label">Amount</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" step="0.01" min="0" class="form-control" id="amount" name="amount" value="<?php echo $edit_data ? $edit_data['amount'] : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($edit_data): ?>
                                <div class="row mb-3">
                                    <label for="status" class="col-sm-4 col-form-label">Status</label>
                                    <div class="col-sm-8">
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Active" <?php if ($edit_data['status'] == 'Active') echo 'selected'; ?>>Active</option>
                                            <option value="Inactive" <?php if ($edit_data['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="row mb-3">
                                    <label for="remarks" class="col-sm-4 col-form-label">Remarks</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo $edit_data ? htmlspecialchars($edit_data['remarks']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <?php if ($edit_data): ?>
                                        <a href="allotment_management.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" name="edit_allotment" class="btn btn-primary">Update Allotment</button>
                                    <?php else: ?>
                                        <button type="reset" class="btn btn-secondary">Clear</button>
                                        <button type="submit" name="add_allotment" class="btn btn-primary">Add Allotment</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Budget Summary</h5>
                            
                            <div class="row mb-2">
                                <div class="col-sm-6 fw-bold">Current Fiscal Year:</div>
                                <div class="col-sm-6"><?php echo $current_year; ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-sm-6 fw-bold">Total Allotment:</div>
                                <div class="col-sm-6">₱<?php echo number_format($total_allotment, 2); ?></div>
                            </div>
                            
                            <!-- In a real app, we would show more budget details here -->
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Allotment Records</h5>
                            
                            <?php if (!empty($allotments)): ?>
                                <table class="table table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col">FY</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Program</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Date Created</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allotments as $allotment): ?>
                                            <tr>
                                                <td><?php echo $allotment['fiscal_year']; ?></td>
                                                <td><?php echo htmlspecialchars($allotment['description']); ?></td>
                                                <td><?php echo htmlspecialchars($allotment['program_name'] ?? 'N/A'); ?></td>
                                                <td class="text-end">₱<?php echo number_format($allotment['amount'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($allotment['date_created'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $allotment['status'] == 'Active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $allotment['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="allotment_management.php?edit=<?php echo $allotment['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $allotment['id']; ?>)" data-bs-toggle="tooltip" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-wallet2 text-secondary" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3">No allotment records found. Add a new allotment to get started.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this allotment record? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

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
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Delete confirmation
        function confirmDelete(id) {
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDeleteBtn').href = 'allotment_management.php?delete=' + id;
            deleteModal.show();
        }
    </script>
</body>
</html> 